<?php

namespace App\Actions\Fortify;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['company_name'] = trim(
            (string) ($input['company_name'] ?? '')
        );
        $input['join_code'] = trim(
            (string) ($input['join_code'] ?? '')
        );

        if ($input['company_name'] === '') {
            $input['company_name'] = null;
        }

        if ($input['join_code'] === '') {
            $input['join_code'] = null;
        } else {
            $input['join_code'] = Str::upper($input['join_code']);
        }

        Validator::make($input, [
            'company_name' => [
                'nullable',
                'required_without:join_code',
                'string',
                'max:255',
            ],

            'join_code' => [
                'nullable',
                'required_without:company_name',
                'string',
                'size:10',
                'alpha_num',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            'password' => $this->passwordRules(),
        ])->validate();

        $joinCode = $input['join_code'] ?? '';

        if ($joinCode !== '') {
            $throttleKey = 'join-code:'.request()->ip();

            if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
                throw ValidationException::withMessages([
                    'join_code' => 'Trop de tentatives. Réessayez dans quelques minutes.',
                ]);
            }

            RateLimiter::hit($throttleKey, 60);
        }

        return DB::transaction(function () use ($input, $joinCode): User {
            if ($joinCode !== '') {
                $company = Company::where('join_code_hash', hash('sha256', $joinCode))
                    ->where(static function ($query): void {
                        $query->whereNull('join_code_expires_at')
                            ->orWhere('join_code_expires_at', '>', now());
                    })
                    ->lockForUpdate()
                    ->first();

                if ($company === null) {
                    throw ValidationException::withMessages([
                        'join_code' => 'Le code entreprise est invalide ou expiré.',
                    ]);
                }

                $user = $company->users()->create([
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'password' => Hash::make($input['password']),
                    'role' => 'employee',
                ]);

                // Le code est à usage unique : il est consommé dès la première inscription.
                $company->forceFill([
                    'join_code_hash' => null,
                    'join_code_expires_at' => null,
                ])->save();

                return $user;
            }

            $baseSlug = Str::slug($input['company_name']) ?: 'entreprise';
            $slug = $baseSlug;

            while (Company::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.Str::lower(Str::random(6));
            }

            $company = Company::create([
                'name' => $input['company_name'],
                'slug' => $slug,
            ]);

            return $company->users()->create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => 'admin',
            ]);
        });
    }
}
