export function initInvoiceForm() {
    const summary = document.querySelector("[data-invoice-summary]");
    const taxRateInput = document.getElementById("tax_rate");
    const taxAmountOutput = document.getElementById("invoiceTaxAmount");
    const totalOutput = document.getElementById("invoiceTotal");

    if (!summary || !taxRateInput || !taxAmountOutput || !totalOutput) {
        return;
    }

    const subtotal = Number(summary.dataset.subtotal);
    const formatAmount = new Intl.NumberFormat("fr-FR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const updateTotals = () => {
        const enteredRate = Number(taxRateInput.value);
        const rate = Number.isFinite(enteredRate) ? enteredRate : 0;
        const taxAmount =
            Math.round((subtotal * (rate / 100) + Number.EPSILON) * 100) / 100;

        taxAmountOutput.textContent = `${formatAmount.format(taxAmount)} FCFA`;
        totalOutput.textContent = `${formatAmount.format(subtotal + taxAmount)} FCFA`;
    };

    taxRateInput.addEventListener("input", updateTotals);
    updateTotals();
}
