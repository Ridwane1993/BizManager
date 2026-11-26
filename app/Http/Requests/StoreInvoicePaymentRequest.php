<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoicePaymentRequest extends FormRequest
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
        $invoice = $this->route('invoice');

        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:'.$invoice?->remainingAmount(),
            ],
            'paid_at' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
