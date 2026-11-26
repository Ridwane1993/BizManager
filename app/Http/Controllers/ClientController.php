<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Afficher la liste des clients.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Client::query();
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $filters = ['search' => $search];

        return view(
            'clients.index',
            compact('clients', 'filters')
        );
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Enregistrer un nouveau client.
     */
    public function store(SaveClientRequest $request)
    {
        $validated = $request->validated();

        Client::create($validated);

        return redirect()
            ->route('clients.index')
            ->with(
                'success',
                'Client créé avec succès.'
            );
    }

    /**
     * Afficher les détails d'un client.
     */
    public function show(Client $client)
    {
        return view(
            'clients.show',
            compact('client')
        );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(Client $client)
    {
        return view(
            'clients.edit',
            compact('client')
        );
    }

    /**
     * Modifier un client.
     */
    public function update(
        SaveClientRequest $request,
        Client $client
    ) {
        $validated = $request->validated();

        $client->update($validated);

        return redirect()
            ->route('clients.index')
            ->with(
                'success',
                'Client modifié avec succès.'
            );
    }

    /**
     * Supprimer un client.
     */
    public function destroy(Client $client)
    {
        return DB::transaction(function () use ($client) {

            /*
            |--------------------------------------------------------------------------
            | Verrouiller le client
            |--------------------------------------------------------------------------
            */
            $client = Client::whereKey($client->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Vérifier si le client possède des ventes
            |--------------------------------------------------------------------------
            */
            if ($client->sales()->exists()) {
                return redirect()
                    ->route('clients.index')
                    ->with(
                        'error',
                        'Impossible de supprimer ce client car il possède déjà des ventes.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Supprimer le client
            |--------------------------------------------------------------------------
            */
            $client->delete();

            return redirect()
                ->route('clients.index')
                ->with(
                    'success',
                    'Client supprimé avec succès.'
                );
        });
    }
}
