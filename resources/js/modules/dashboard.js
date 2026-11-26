const periodTexts = {
    week: {
        button: "Cette semaine",
        subtitle: "Ventes des 7 derniers jours",
    },
    month: {
        button: "Ce mois",
        subtitle: "Ventes du mois",
    },
    quarter: {
        button: "Ce trimestre",
        subtitle: "Ventes des 3 derniers mois",
    },
};

export function initDashboardPeriodFilter() {
    const buttons = document.querySelectorAll(".period-option");

    if (buttons.length === 0) {
        return;
    }

    buttons.forEach((button) => {
        button.addEventListener("click", async function (event) {
            event.preventDefault();
            event.stopPropagation();

            const period = this.dataset.period;
            const periodButton = document.getElementById("salesPeriodButton");
            const periodButtonText = document.getElementById(
                "salesPeriodButtonText"
            );
            const chartSubtitle = document.getElementById(
                "salesChartSubtitle"
            );

            if (
                !window.salesChart ||
                !window.salesChart.data ||
                !periodButton ||
                !periodButtonText ||
                !chartSubtitle
            ) {
                console.error(
                    "Le graphique des ventes n'est pas disponible."
                );
                return;
            }

            window.bootstrap?.Dropdown?.getOrCreateInstance(
                periodButton
            )?.hide();

            periodButton.disabled = true;
            periodButtonText.textContent = "Chargement...";

            try {
                const response = await fetch(
                    `/dashboard/sales-chart?period=${encodeURIComponent(period)}`,
                    {
                        headers: {
                            Accept: "application/json",
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        "Erreur lors de la récupération des données."
                    );
                }

                const result = await response.json();

                window.salesChart.data.labels = result.data.map(
                    (item) => item.label
                );
                window.salesChart.data.datasets[0].data = result.data.map(
                    (item) => item.total
                );
                window.salesChart.update();

                const texts = periodTexts[period];

                if (texts) {
                    periodButtonText.textContent = texts.button;
                    chartSubtitle.textContent = texts.subtitle;
                }
            } catch (error) {
                console.error(error);
                periodButtonText.textContent = "Erreur de chargement";
            } finally {
                periodButton.disabled = false;
            }
        });
    });
}
