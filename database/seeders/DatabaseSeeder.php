<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;


    public function run(): void
    {
        // Usuario admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Usuario cliente
        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'role' => 'cliente',
        ]);

        // Categorías
        $electronica = Category::create(['name' => 'Electrónica', 'slug' => 'electronica']);
        $ropa = Category::create(['name' => 'Ropa', 'slug' => 'ropa']);

        // Productos
        Product::create([
            'name' => 'Laptop HP',
            'description' => 'Laptop 15" 8GB RAM',
            'price' => 1500.00,
            'stock' => 10,
            'category_id' => $electronica->id,
        ]);

        Product::create([
            'name' => 'Camiseta Negra',
            'description' => 'Camiseta algodón',
            'price' => 25.00,
            'stock' => 50,
            'category_id' => $ropa->id,
        ]);
    }

    // /**
    //  * Seed the application's database.
    //  */
    // public function run(): void
    // {
    //     // User::factory(10)->create();

    //     User::factory()->create([
    //         'name' => 'Test User',
    //         'email' => 'test@example.com',
    //     ]);
    // }
}
