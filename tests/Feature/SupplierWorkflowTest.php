<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupplierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_crud_search_and_validation_work(): void
    {
        [, $admin] = $this->createAdmin('Supplier company');
        $this->actingAs($admin);

        $this->get(route('suppliers.index'))->assertOk();
        $this->get(route('suppliers.create'))->assertOk();
        $this->post(route('suppliers.store'), [
            'name' => 'Supplier Initial',
            'email' => 'initial@supplier.test',
            'phone' => '+229 97000000',
            'address' => 'Cotonou',
            'notes' => 'Payment terms: 30 days',
        ])->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('email', 'initial@supplier.test')->firstOrFail();
        $this->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee('Payment terms: 30 days')
            ->assertSee('Supprimer');
        $this->get(route('suppliers.edit', $supplier))->assertOk();

        $this->put(route('suppliers.update', $supplier), [
            'name' => 'Supplier Updated',
            'email' => 'updated@supplier.test',
            'phone' => '+229 96000000',
            'address' => 'Porto-Novo',
            'notes' => 'Updated terms',
        ])->assertRedirect(route('suppliers.index'));

        $supplier->refresh();
        $this->assertSame('Supplier Updated', $supplier->name);
        $this->assertSame('updated@supplier.test', $supplier->email);
        $this->assertSame('Updated terms', $supplier->notes);

        $this->get(route('suppliers.index', ['search' => 'updated@supplier.test']))
            ->assertOk()
            ->assertSee('Supplier Updated');

        $this->from(route('suppliers.create'))
            ->post(route('suppliers.store'), [
                'name' => 'Invalid supplier',
                'email' => 'not-an-email',
            ])
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('suppliers', ['name' => 'Invalid supplier']);

        $this->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->getKey()]);
    }

    public function test_purchase_history_is_visible_and_prevents_supplier_deletion(): void
    {
        [, $adminA] = $this->createAdmin('Supplier company A');
        $this->actingAs($adminA);
        $supplierA = Supplier::create(['name' => 'Private supplier A']);
        $purchase = Purchase::create([
            'supplier_id' => $supplierA->getKey(),
            'purchase_number' => 'SUP-'.Str::upper(Str::random(10)),
            'purchase_date' => now()->toDateString(),
            'total_amount' => 75,
            'status' => 'received',
        ]);

        $this->get(route('suppliers.show', $supplierA))
            ->assertOk()
            ->assertSee($purchase->purchase_number)
            ->assertDontSee('Supprimer');
        $this->delete(route('suppliers.destroy', $supplierA))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplierA->getKey()]);
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->getKey(),
            'supplier_id' => $supplierA->getKey(),
        ]);

        [, $adminB] = $this->createAdmin('Supplier company B');
        $this->actingAs($adminB);
        Supplier::create(['name' => 'Private supplier B']);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Private supplier B')
            ->assertDontSee('Private supplier A');
        $this->get(route('suppliers.show', $supplierA))->assertNotFound();
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
