<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TenantValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id' => [
                'required',
                $this->existsForCompany('categories'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'reference' => [
                'required',
                'string',
                'max:255',
                $this->uniqueForCompany('products', 'reference'),
            ],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_alert_threshold' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
