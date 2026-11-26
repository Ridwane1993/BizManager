import "./bootstrap";
import Chart from "chart.js/auto";
import { Passkeys } from "@laravel/passkeys";

document.addEventListener("DOMContentLoaded", function () {
    // ==================================================
    // CONFIGURATION PASSKEYS
    // ==================================================

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    const headers = {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    };

    if (csrfToken) {
        headers["X-CSRF-TOKEN"] = csrfToken;
    }

    Passkeys.configure({
        fetch: {
            credentials: "same-origin",
            headers,
        },
    });

    // ==================================================
    // GRAPHIQUE DES VENTES — CHART.JS
    // ==================================================

    const salesCanvas = document.getElementById("salesChart");

    if (salesCanvas && Array.isArray(window.salesChartData)) {
        window.salesChart = new Chart(salesCanvas, {
            type: "line",

            data: {
                labels: window.salesChartData.map((item) => item.label),

                datasets: [
                    {
                        label: "Ventes",
                        data: window.salesChartData.map((item) => item.total),
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 4,
                    },
                ],
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false,
                    },

                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.raw ?? 0;
                                return value.toLocaleString("fr-FR") + " FCFA";
                            },
                        },
                    },
                },

                scales: {
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10,
                        },
                    },

                    y: {
                        beginAtZero: true,

                        ticks: {
                            callback: function (value) {
                                return value.toLocaleString("fr-FR") + " FCFA";
                            },
                        },
                    },
                },
            },
        });
    }

    const expenseCanvas = document.getElementById("expenseChart");
    const expenseChartData = window.expenseChartData;

    if (
        expenseCanvas &&
        Array.isArray(expenseChartData) &&
        expenseChartData.length > 0
    ) {
        const expenseColors = [
            "#2563eb",
            "#16a34a",
            "#f59e0b",
            "#ef4444",
            "#8b5cf6",
            "#14b8a6",
            "#f97316",
        ];

        window.expenseChart = new Chart(expenseCanvas, {
            type: "doughnut",

            data: {
                labels: expenseChartData.map((item) => item.label),
                datasets: [
                    {
                        data: expenseChartData.map((item) => item.total),
                        backgroundColor: expenseChartData.map(
                            (_, index) =>
                                expenseColors[index % expenseColors.length]
                        ),
                        borderWidth: 0,
                    },
                ],
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        position: "bottom",
                    },

                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.raw ?? 0;
                                return (
                                    context.label +
                                    ": " +
                                    value.toLocaleString("fr-FR") +
                                    " FCFA"
                                );
                            },
                        },
                    },
                },
            },
        });
    }

    // ==================================================
    // MESSAGES PASSKEYS
    // ==================================================

    function showStatus(element, type, message) {
        if (!element) {
            return;
        }

        element.className = `alert alert-${type}`;
        element.textContent = message;
    }

    // ==================================================
    // ENREGISTRER UNE PASSKEY DEPUIS LE PROFIL
    // ==================================================

    const registerButton = document.getElementById(
        "registerPasskeyButton"
    );

    const passkeyNameInput = document.getElementById("passkeyName");

    const registerStatus = document.getElementById("passkeyStatus");

    if (registerButton && passkeyNameInput && registerStatus) {
        registerButton.addEventListener("click", async function () {
            const name = passkeyNameInput.value.trim();

            if (!name) {
                showStatus(
                    registerStatus,
                    "warning",
                    "Veuillez donner un nom à votre passkey."
                );

                passkeyNameInput.focus();
                return;
            }

            if (!Passkeys.isSupported()) {
                showStatus(
                    registerStatus,
                    "danger",
                    "Votre navigateur ne prend pas en charge les passkeys dans cet environnement."
                );

                return;
            }

            registerButton.disabled = true;
            registerButton.textContent = "Enregistrement en cours...";

            showStatus(
                registerStatus,
                "info",
                "Suivez les instructions affichées par votre navigateur."
            );

            try {
                await Passkeys.register({ name });

                showStatus(
                    registerStatus,
                    "success",
                    "Passkey enregistrée avec succès."
                );

                passkeyNameInput.value = "";
            } catch (error) {
                console.error("Erreur d'enregistrement de la passkey :", error);

                showStatus(
                    registerStatus,
                    "danger",
                    error?.message ||
                        "Échec de l'enregistrement. Veuillez réessayer."
                );
            } finally {
                registerButton.disabled = false;

                registerButton.innerHTML =
                    '<i class="bi bi-plus-circle me-1"></i> Ajouter une passkey';
            }
        });
    }

    // ==================================================
    // SE CONNECTER AVEC UNE PASSKEY
    // ==================================================

    const loginButton = document.getElementById("loginPasskeyButton");

    const loginStatus = document.getElementById("loginPasskeyStatus");

    const rememberCheckbox = document.getElementById("remember");

    if (loginButton && loginStatus) {
        loginButton.addEventListener("click", async function () {
            if (!Passkeys.isSupported()) {
                showStatus(
                    loginStatus,
                    "danger",
                    "Votre navigateur ne prend pas en charge les passkeys dans cet environnement."
                );

                return;
            }

            loginButton.disabled = true;

            loginButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                "Vérification en cours...";

            showStatus(
                loginStatus,
                "info",
                "Suivez les instructions de votre navigateur pour vous authentifier."
            );

            try {
                const response = await Passkeys.verify({
                    remember: Boolean(rememberCheckbox?.checked),
                });

                if (response?.redirect) {
                    window.location.assign(response.redirect);
                } else {
                    window.location.assign("/dashboard");
                }
            } catch (error) {
                console.error("Erreur de connexion par passkey :", error);

                showStatus(
                    loginStatus,
                    "danger",
                    error?.message ||
                        "La connexion par passkey a échoué. Réessayez ou utilisez votre mot de passe."
                );
            } finally {
                loginButton.disabled = false;

                loginButton.innerHTML =
                    '<i class="bi bi-fingerprint me-1"></i> Se connecter avec une passkey';
            }
        });
    }
});
