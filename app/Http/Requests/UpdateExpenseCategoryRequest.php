<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TenantValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                $this->uniqueForCompany(
                    'expense_categories',
                    'name',
                    $this->route('expense_category')?->getKey()
                ),
            ],
        ];
    }
}
