<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'sale_id',
        'invoice_number',
        'issued_at',
        'due_date',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'payment_status',
        'paid_at',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'due_date' => 'date:Y-m-d',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)
            ->latest('paid_at');
    }

    /**
     * Total déjà réglé, calculé à partir des paiements enregistrés.
     */
    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Montant restant dû (jamais négatif).
     */
    public function remainingAmount(): float
    {
        return round(
            max(0, (float) $this->total_amount - $this->paidAmount()),
            2
        );
    }

    /**
     * Recalcule le statut de paiement après l'enregistrement d'un paiement.
     */
    public function refreshPaymentStatus(): void
    {
        $remaining = $this->remainingAmount();
        $paidAmount = $this->paidAmount();

        $status = match (true) {
            $remaining <= 0.001 => 'paid',
            $paidAmount > 0 => 'partial',
            default => 'unpaid',
        };

        $this->update([
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }
}
