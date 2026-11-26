<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'description',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'source_id' => 'integer',
    ];

    /**
     * Produit concerné par le mouvement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Source du mouvement.
     *
     * Peut être une Sale, un Purchase, etc.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Vérifier si le mouvement est manuel.
     */
    public function isManual(): bool
    {
        return $this->source_type === null
            && $this->source_id === null;
    }

    /**
     * Vérifier si le mouvement vient d'une vente.
     */
    public function isFromSale(): bool
    {
        return $this->source_type === Sale::class;
    }

    /**
     * Vérifier si le mouvement vient d'un achat.
     */
    public function isFromPurchase(): bool
    {
        return $this->source_type === Purchase::class;
    }

    /**
     * Entreprise à laquelle appartient cette donnée.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
