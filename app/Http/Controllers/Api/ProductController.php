<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET >Publico >
     */
    public function index()
    {
         // 🚨 IMPORTANTE ENTENDERLO 🚨
        // 'with('category')' hace EAGER LOADING: en lugar de hacer
        // 1 consulta para productos + N consultas para cada categoría,
        // hace 2 consultas totales. Esto es crucial cuando hay muchos
        // productos. Sin esto, tendrías el problema "N+1 queries".
        $products = Product::with('category')-> latest()->get();
        return response()->json($products,200);
    }

    /**
     * Store a newly created resource in storage.
     * POST > solo admin
     */
    public function store(Request $request)
    {
        // 🚨 IMPORTANTE ENTENDERLO 🚨
        // La validación ocurre ANTES de tocar la base de datos.
        // Si falla, Laravel devuelve automáticamente un 422 con
        // los errores en JSON. React luego los muestra al usuario.
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id', // Verifica que exista la categoría
            'image_url'   => 'nullable|url|max:500',
        ]);

        $product = Product::create($validated);

        // Devolvemos 201 (Created) y el producto con su categoría
        // para que React pueda mostrarlo sin otra petición.
        return response()->json($product->load('category'), 201);
    }

    /**
     * Display the specified resource.
     *GET /api/products/{id}
     * Muestra un producto individual (público).
     */
    public function show(Product $product)
    {
        // 🚨 IMPORTANTE ENTENDERLO 🚨
        // Aquí ocurre "Route Model Binding". Laravel ve el tipo
        // 'Product $product' y automáticamente busca el producto
        // por el ID de la URL. Si no existe, devuelve 404 solo.
        // Es mágico y muy útil, pero debes saber que pasa.
        return response()->json($product->load('category'), 200);
    }

    /**
     * Update the specified resource in storage.
     *  PUT /api/products/{id}
     * Actualiza un producto (solo admin).
     */
    public function update(Request $request, Product $product)
    {
        // 🚨 IMPORTANTE ENTENDERLO 🚨
        // Usamos 'sometimes' en lugar de 'required' porque en un PUT
        // parcial, el usuario puede enviar solo algunos campos.
        // Si no envía 'name', no lo validamos, pero si lo envía, debe cumplir.
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'image_url'   => 'nullable|url|max:500',
        ]);

        $product->update($validated);

        return response()->json($product->load('category'), 200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/products/{id}
     * Elimina un producto (solo admin).
     */
    public function destroy(Product $product)
    {
        $product->delete();

        // 204 = No Content. Significa "todo salió bien pero no
        // hay nada que devolver". Es el estándar para DELETE.
        return response()->json(null, 204);
    }
}
