<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TenantValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
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
            'client_id' => [
                'required',
                $this->existsForCompany('clients'),
            ],
            'sale_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'completed']),
            ],
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
        ];
    }
}
