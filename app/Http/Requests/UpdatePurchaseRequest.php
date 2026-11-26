<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TenantValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
{
    use TenantValidation;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => [
                'required',
                $this->existsForCompany('suppliers'),
            ],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => [
                'required',
                $this->existsForCompany('products'),
                'distinct',
            ],
            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'products.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}
