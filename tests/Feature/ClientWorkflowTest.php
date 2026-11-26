<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_crud_search_and_validation_persist_company_data(): void
    {
        [$company, $admin] = $this->createAdmin('Client company');
        $this->actingAs($admin);

        $this->get(route('clients.index'))->assertOk();
        $this->get(route('clients.create'))->assertOk();
        $this->post(route('clients.store'), [
            'name' => 'Client Initial',
            'email' => 'initial@example.test',
            'phone' => '+229 97000000',
            'address' => 'Cotonou',
            'notes' => 'Premier contact',
        ])->assertRedirect(route('clients.index'));

        $client = Client::where('email', 'initial@example.test')->firstOrFail();
        $this->assertSame((int) $company->getKey(), (int) $client->company_id);
        $this->assertDatabaseHas('clients', [
            'id' => $client->getKey(),
            'company_id' => $company->getKey(),
            'name' => 'Client Initial',
            'phone' => '+229 97000000',
        ]);
        $this->get(route('clients.show', $client))->assertOk()->assertSee('Premier contact');
        $this->get(route('clients.edit', $client))->assertOk();

        $this->put(route('clients.update', $client), [
            'name' => 'Client Modifié',
            'email' => 'updated@example.test',
            'phone' => '+229 96000000',
            'address' => 'Porto-Novo',
            'notes' => 'Informations actualisées',
        ])->assertRedirect(route('clients.index'));

        $client->refresh();
        $this->assertSame('Client Modifié', $client->name);
        $this->assertSame('updated@example.test', $client->email);
        $this->assertSame('Porto-Novo', $client->address);

        $this->get(route('clients.index', ['search' => 'updated@example.test']))
            ->assertOk()
            ->assertSee('Client Modifié');
        $this->get(route('clients.index', ['search' => '+229 96000000']))
            ->assertOk()
            ->assertSee('Client Modifié');

        $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Invalid client',
                'email' => 'not-an-email',
            ])
            ->assertRedirect(route('clients.create'))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('clients', ['name' => 'Invalid client']);

        $this->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('clients', ['id' => $client->getKey()]);
    }

    public function test_client_isolation_and_sales_relation_prevent_unsafe_deletion(): void
    {
        [, $adminA] = $this->createAdmin('Client company A');
        $this->actingAs($adminA);
        $clientA = Client::create(['name' => 'Private client A']);

        [, $adminB] = $this->createAdmin('Client company B');
        $this->actingAs($adminB);
        $clientB = Client::create(['name' => 'Private client B']);

        $this->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Private client B')
            ->assertDontSee('Private client A');
        $this->get(route('clients.show', $clientA))->assertNotFound();

        $this->actingAs($adminA);
        $sale = Sale::create([
            'client_id' => $clientA->getKey(),
            'sale_number' => 'CLIENT-'.Str::upper(Str::random(10)),
            'sale_date' => now()->toDateString(),
            'total_amount' => 100,
            'status' => 'completed',
        ]);

        $this->delete(route('clients.destroy', $clientA))
            ->assertRedirect(route('clients.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('clients', ['id' => $clientA->getKey()]);
        $this->assertDatabaseHas('sales', [
            'id' => $sale->getKey(),
            'client_id' => $clientA->getKey(),
        ]);
    }

    public function test_client_index_validates_search_length(): void
    {
        [, $admin] = $this->createAdmin('Client search validation company');
        $this->actingAs($admin);

        $this->from(route('clients.index'))
            ->get(route('clients.index', [
                'search' => str_repeat('x', 101),
            ]))
            ->assertRedirect(route('clients.index'))
            ->assertSessionHasErrors('search');
    }

    public function test_client_index_paginates_and_preserves_search(): void
    {
        [, $admin] = $this->createAdmin('Client pagination company');
        $this->actingAs($admin);

        for ($index = 1; $index <= 16; $index++) {
            Client::create([
                'name' => sprintf('Pagination Client %02d', $index),
                'email' => sprintf('pagination-%02d@example.test', $index),
            ]);
        }

        $this->get(route('clients.index', ['search' => 'Pagination Client']))
            ->assertOk()
            ->assertSee('page=2')
            ->assertViewHas('clients', static fn ($clients): bool => $clients->total() === 16
                && $clients->count() === 15
            );

        $this->get(route('clients.index', [
            'search' => 'Pagination Client',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('clients', static fn ($clients): bool => $clients->currentPage() === 2
                && $clients->total() === 16
                && $clients->count() === 1
            );
    }

    private function createAdmin(string $companyName): array
    {
        $company = Company::create([
            'name' => $companyName,
            'slug' => Str::slug($companyName).'-'.Str::uuid(),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        return [$company, $admin];
    }
}
