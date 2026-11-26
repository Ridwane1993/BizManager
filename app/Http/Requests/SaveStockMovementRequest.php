<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TenantValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveStockMovementRequest extends FormRequest
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
            'product_id' => [
                'required',
                $this->existsForCompany('products'),
            ],
            'type' => [
                'required',
                Rule::in([
                    'in',
                    'out',
                    'adjustment',
                    'loss',
                    'return',
                ]),
            ],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
