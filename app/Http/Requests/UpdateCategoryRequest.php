<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\TenantValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
                    'categories',
                    'name',
                    $this->route('category')?->getKey()
                ),
            ],
            'description' => ['nullable', 'string'],
        ];
    }
}
