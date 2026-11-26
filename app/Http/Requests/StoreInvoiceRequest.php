<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
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
            'tax_rate' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:100',
            ],
            'due_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
        ];
    }
}
