<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\UserCompanyScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs.
     */
    public function index(Request $request)
    {
        $company = UserCompanyScope::companyFor($request->user());

        $query = UserCompanyScope::queryFor($company)
            ->latest();

        // ==================================================
        // RECHERCHE PAR NOM OU EMAIL
        // ==================================================

        if ($request->filled('search')) {
            $search = trim($request->search);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
        }

        // ==================================================
        // PAGINATION
        // ==================================================

        $users = $query
            ->paginate(15)
            ->withQueryString();

        return view(
            'users.index',
            compact('users')
        );
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create()
    {
        return view('users.create');
    }

    public function issueJoinCode(Request $request)
    {
        $company = UserCompanyScope::companyFor($request->user());
        $joinCode = Str::upper(Str::random(10));

        $company->forceFill([
            'join_code_hash' => hash('sha256', $joinCode),
            'join_code_expires_at' => now()->addHours(24),
        ])->save();

        return redirect()
            ->route('users.index')
            ->with('company_join_code', $joinCode);
    }

    /**
     * Enregistrer un nouvel utilisateur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'manager',
                    'employee',
                ]),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $company = UserCompanyScope::companyFor($request->user());

        $company->users()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Utilisateur créé avec succès.'
            );
    }

    /**
     * Afficher un utilisateur.
     */
    public function show(Request $request, User $user)
    {
        UserCompanyScope::ensureBelongsTo(
            $user,
            UserCompanyScope::companyFor($request->user())
        );

        return view(
            'users.show',
            compact('user')
        );
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(Request $request, User $user)
    {
        UserCompanyScope::ensureBelongsTo(
            $user,
            UserCompanyScope::companyFor($request->user())
        );

        return view(
            'users.edit',
            compact('user')
        );
    }

    /**
     * Mettre à jour un utilisateur.
     */
    public function update(
        Request $request,
        User $user
    ) {
        $company = UserCompanyScope::companyFor($request->user());
        UserCompanyScope::ensureBelongsTo($user, $company);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->getKey()),
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'manager',
                    'employee',
                ]),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $newRole = $validated['role'];

        // ==================================================
        // DONNÉES À METTRE À JOUR
        // ==================================================

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $newRole,
        ];

        // ==================================================
        // MOT DE PASSE
        // ==================================================

        if (
            isset($validated['password'])
            && $validated['password'] !== null
            && $validated['password'] !== ''
        ) {
            $data['password'] = Hash::make(
                $validated['password']
            );
        }

        // ==================================================
        // MISE À JOUR ATOMIQUE
        // ==================================================

        $error = DB::transaction(function () use (
            $user,
            $data,
            $newRole,
            $company
        ) {

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER LES ADMINISTRATEURS
            |--------------------------------------------------------------------------
            |
            | Toutes les opérations sensibles sur les utilisateurs
            | suivent le même ordre de verrouillage.
            |
            | Cela évite qu'une modification simultanée puisse
            | supprimer ou rétrograder plusieurs derniers admins.
            |
            */

            $adminUsers = UserCompanyScope::queryFor($company)
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER L'UTILISATEUR CIBLE
            |--------------------------------------------------------------------------
            */

            $target = UserCompanyScope::queryFor($company)
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | PROTECTION DU PROPRE COMPTE
            |--------------------------------------------------------------------------
            */

            if (
                $target->getKey() === auth()->id()
                && $newRole !== 'admin'
            ) {
                return 'Vous ne pouvez pas retirer votre propre rôle administrateur.';
            }

            /*
            |--------------------------------------------------------------------------
            | PROTECTION DU DERNIER ADMINISTRATEUR
            |--------------------------------------------------------------------------
            */

            if (
                $target->role === 'admin'
                && $newRole !== 'admin'
            ) {
                if ($adminUsers->count() <= 1) {
                    return 'Impossible de retirer le rôle administrateur du dernier administrateur.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | MISE À JOUR
            |--------------------------------------------------------------------------
            */

            $target->update($data);

            return null;
        });

        // ==================================================
        // GESTION DES ERREURS MÉTIER
        // ==================================================

        if ($error !== null) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $error);
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Utilisateur modifié avec succès.'
            );
    }

    /**
     * Supprimer un utilisateur.
     */
    public function destroy(Request $request, User $user)
    {
        $company = UserCompanyScope::companyFor($request->user());
        UserCompanyScope::ensureBelongsTo($user, $company);

        // ==================================================
        // PROTECTION DU PROPRE COMPTE
        // ==================================================

        if ($user->getKey() === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Vous ne pouvez pas supprimer votre propre compte.'
                );
        }

        // ==================================================
        // SUPPRESSION ATOMIQUE
        // ==================================================

        $error = DB::transaction(function () use (
            $user,
            $company
        ) {

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER LES ADMINISTRATEURS
            |--------------------------------------------------------------------------
            */

            $adminUsers = UserCompanyScope::queryFor($company)
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER L'UTILISATEUR CIBLE
            |--------------------------------------------------------------------------
            */

            $target = UserCompanyScope::queryFor($company)
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | PROTECTION DU DERNIER ADMINISTRATEUR
            |--------------------------------------------------------------------------
            */

            if ($target->role === 'admin') {

                if ($adminUsers->count() <= 1) {
                    return 'Impossible de supprimer le dernier administrateur.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SUPPRESSION
            |--------------------------------------------------------------------------
            */

            $target->delete();

            return null;
        });

        // ==================================================
        // GESTION DES ERREURS MÉTIER
        // ==================================================

        if ($error !== null) {
            return redirect()
                ->route('users.index')
                ->with('error', $error);
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Utilisateur supprimé avec succès.'
            );
    }
}
