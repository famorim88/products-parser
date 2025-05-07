<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    public function test_can_get_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->get('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_can_get_single_product()
    {
        $product = Product::factory()->create(['code' => 999]);

        $response = $this->get("/api/products/999");

        $response->assertStatus(200)
                 ->assertJsonFragment(['code' => 999]);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create(['code' => 888]);

        $response = $this->putJson("/api/products/888", [
            'product_name' => 'Novo Nome'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Produto atualizado com sucesso']);

        $this->assertDatabaseHas('products', ['code' => 888, 'product_name' => 'Novo Nome']);
    }
}
