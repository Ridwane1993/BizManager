export function initStockMovementForm() {
    const typeSelect = document.getElementById("type");
    const quantityInput = document.getElementById("quantity");
    const quantityHelp = document.getElementById("quantityHelp");

    if (!typeSelect || !quantityInput || !quantityHelp) {
        return;
    }

    const positiveHelp =
        "La quantité doit être positive pour une entrée, sortie, perte ou retour.";
    const adjustmentHelp =
        quantityHelp.dataset.adjustmentHelp ||
        "Pour une correction, utilisez un nombre positif pour augmenter le stock ou négatif pour le diminuer.";

    function updateQuantityRules() {
        if (typeSelect.value === "adjustment") {
            quantityInput.removeAttribute("min");
            quantityHelp.textContent = adjustmentHelp;
            return;
        }

        quantityInput.setAttribute("min", "1");
        quantityHelp.textContent = positiveHelp;

        if (quantityInput.value !== "" && Number(quantityInput.value) < 1) {
            quantityInput.value = 1;
        }
    }

    typeSelect.addEventListener("change", updateQuantityRules);
    updateQuantityRules();
}
