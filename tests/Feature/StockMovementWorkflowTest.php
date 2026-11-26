<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockMovementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_movement_create_update_and_delete_reconcile_product_stock(): void
    {
        [, $admin] = $this->createAdmin('Movement company');
        $this->actingAs($admin);
        $productA = $this->createProduct('Product A', 10);
        $productB = $this->createProduct('Product B', 5);

        $this->get(route('stock-movements.index'))->assertOk();
        $this->get(route('stock-movements.create'))->assertOk();
        $this->post(route('stock-movements.store'), [
            'product_id' => $productA->getKey(),
            'type' => 'out',
            'quantity' => 3,
            'description' => 'Manual issue',
        ])->assertRedirect(route('stock-movements.index'));

        $movement = StockMovement::where('description', 'Manual issue')->firstOrFail();
        $this->assertTrue($movement->isManual());
        $this->assertSame(7, $productA->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->getKey(),
            'product_id' => $productA->getKey(),
            'type' => 'out',
            'quantity' => 3,
            'source_type' => null,
            'source_id' => null,
        ]);
        $this->get(route('stock-movements.show', $movement))
            ->assertOk()
            ->assertSee('Manual issue');
        $this->get(route('stock-movements.edit', $movement))->assertOk();

        $this->put(route('stock-movements.update', $movement), [
            'product_id' => $productB->getKey(),
            'type' => 'loss',
            'quantity' => 2,
            'description' => 'Damaged units',
        ])->assertRedirect(route('stock-movements.show', $movement));

        $movement->refresh();
        $this->assertSame(10, $productA->fresh()->stock);
        $this->assertSame(3, $productB->fresh()->stock);
        $this->assertSame((int) $productB->getKey(), (int) $movement->product_id);
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->getKey(),
            'type' => 'loss',
            'quantity' => 2,
            'description' => 'Damaged units',
        ]);

        $this->get(route('stock-movements.index', ['type' => 'loss']))
            ->assertOk()
            ->assertSee('Damaged units')
            ->assertSee('-2');

        $this->delete(route('stock-movements.destroy', $movement))
            ->assertRedirect(route('stock-movements.index'))
            ->assertSessionHas('success');

        $this->assertSame(5, $productB->fresh()->stock);
        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->getKey()]);
    }

    public function test_invalid_manual_movements_do_not_change_stock_or_persist(): void
    {
        [, $admin] = $this->createAdmin('Movement validation company');
        $this->actingAs($admin);
        $product = $this->createProduct('Limited product', 2);

        $invalidMovements = [
            ['type' => 'out', 'quantity' => 3],
            ['type' => 'in', 'quantity' => -1],
            ['type' => 'adjustment', 'quantity' => -3],
        ];

        foreach ($invalidMovements as $invalidMovement) {
            $this->from(route('stock-movements.create'))
                ->post(route('stock-movements.store'), [
                    'product_id' => $product->getKey(),
                    'description' => 'Rejected movement',
                ] + $invalidMovement)
                ->assertRedirect(route('stock-movements.create'))
                ->assertSessionHasErrors('quantity');
        }

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_automatic_movements_cannot_be_edited_or_deleted(): void
    {
        [, $admin] = $this->createAdmin('Automatic movement company');
        $this->actingAs($admin);
        $client = Client::create(['name' => 'Movement client']);
        $product = $this->createProduct('Automatic product', 4);
        $sale = Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => 'MOV-'.Str::upper(Str::random(10)),
            'sale_date' => now()->toDateString(),
            'total_amount' => 20,
            'status' => 'completed',
        ]);
        $movement = StockMovement::create([
            'product_id' => $product->getKey(),
            'type' => 'out',
            'quantity' => 1,
            'source_type' => Sale::class,
            'source_id' => $sale->getKey(),
        ]);
        $product->update(['stock' => 3]);

        $this->get(route('stock-movements.edit', $movement))->assertStatus(403);
        $this->put(route('stock-movements.update', $movement), [
            'product_id' => $product->getKey(),
            'type' => 'out',
            'quantity' => 2,
        ])->assertStatus(403);
        $this->delete(route('stock-movements.destroy', $movement))->assertStatus(403);

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->getKey()]);
    }

    public function test_company_cannot_view_or_move_stock_for_another_company_product(): void
    {
        [, $adminA] = $this->createAdmin('Movement company A');
        $this->actingAs($adminA);
        $productA = $this->createProduct('Private product A', 5);
        $movement = StockMovement::create([
            'product_id' => $productA->getKey(),
            'type' => 'in',
            'quantity' => 1,
            'description' => 'Private movement',
        ]);

        [, $adminB] = $this->createAdmin('Movement company B');
        $this->actingAs($adminB);

        $this->get(route('stock-movements.show', $movement))->assertNotFound();
        $this->from(route('stock-movements.create'))
            ->post(route('stock-movements.store'), [
                'product_id' => $productA->getKey(),
                'type' => 'in',
                'quantity' => 1,
            ])
            ->assertRedirect(route('stock-movements.create'))
            ->assertSessionHasErrors('product_id');

        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertSame(5, $productA->fresh()->stock);
    }

    private function createProduct(string $name, int $stock): Product
    {
        $category = Category::create(['name' => $name.' category']);

        return Product::create([
            'category_id' => $category->getKey(),
            'name' => $name,
            'reference' => Str::upper(Str::random(12)),
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => $stock,
            'stock_alert_threshold' => 1,
        ]);
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
