<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * Lista las órdenes del usuario autenticado.
     *
     * 🚨 IMPORTANTE ENTENDERLO 🚨
     * Un usuario SOLO ve SUS propias órdenes, nunca las de otros.
     * Esto se logra con $request->user()->orders().
     * Si usaramos Order::all() tendríamos una fuga de datos grave.
     */
    public function index(Request $request)
    {
        //
        // with('items.product') hace EAGER LOADING:
        // carga la orden + sus items + el producto de cada item
        // en 3 consultas totales, no en N+1.
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json($orders, 200);
    }

     /**
     * POST /api/orders
     * Crea una nueva orden desde el carrito.
     *
     * 🚨 IMPORTANTE ENTENDERLO 🚨
     * Este es el endpoint más crítico del e-commerce.
     * Todas las operaciones van dentro de una TRANSACCIÓN:
     * si algo falla, se revierte TODO.
     */
    public function store(Request $request)
    {
        // 1. Validación de estructura. NO confiamos en precios,
        //    solo en product_id y quantity.
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            // 🚨 IMPORTANTE ENTENDERLO 🚨
            // DB::transaction() recibe una función. Todo lo que
            // se ejecute dentro es "atómico": o pasa TODO o no pasa NADA.
            //
            // Si en cualquier línea se lanza una excepción, Laravel
            // hace ROLLBACK automáticamente y nada se guarda.
            // Esta es la garantía que evita inconsistencias graves
            // (ej: cliente paga pero no hay orden).
            $order = DB::transaction(function () use ($validated, $request) {

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total' => 0, // Se calcula después
                    'status' => 'pending',
                ]);

                $total = 0;

                foreach ($validated['items'] as $item) {
                    // 🚨 Bloqueo de la fila del producto (lockForUpdate).
                    // Esto evita CONDICIONES DE CARRERA: si dos clientes
                    // compran el mismo producto al mismo tiempo, sin el lock
                    // ambos podrían ver stock=1, ambos comprar, y quedar en -1.
                    //
                    // Con el lock, el segundo cliente espera a que el primero
                    // termine. Es crítico en e-commerce real.
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => ["Producto con ID {$item['product_id']} no encontrado."],
                        ]);
                    }

                    // Verificar stock suficiente
                    if ($product->stock < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => ["Stock insuficiente para '{$product->name}'. Disponible: {$product->stock}, solicitado: {$item['quantity']}."],
                        ]);
                    }

                    // 🚨 IMPORTANTE ENTENDERLO 🚨
                    // Usamos $product->price (precio REAL de la BD),
                    // NO el precio del frontend. Esta es la protección
                    // contra manipulación de precios.
                    $itemTotal = $product->price * $item['quantity'];

                    // Crear el item de la orden
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price, // ← Precio al momento de la compra
                    ]);

                    // Descontar stock
                    $product->decrement('stock', $item['quantity']);

                    // Acumular total
                    $total += $itemTotal;
                }

                // Actualizar el total de la orden
                $order->update(['total' => $total]);

                return $order;
            });

            // Cargamos relaciones para devolver datos completos a React
            $order->load('items.product');

            return response()->json([
                'message' => 'Orden creada exitosamente',
                'order' => $order,
            ], 201);

        } catch (ValidationException $e) {
            // 🚨 IMPORTANTE ENTENDERLO 🚨
            // Si la transacción falló por validación (stock insuficiente,
            // producto no encontrado), el rollback ya se hizo.
            // Solo devolvemos el error al cliente.
            throw $e;
        } catch (\Exception $e) {
            //\Log::error('Error al crear orden: ' . $e->getMessage()); // ← Log interno
            // Errores inesperados (BD caída, etc.)
            return response()->json([
                'message' => 'Error al procesar la orden. Intenta de nuevo.',
                //'error' => $e->getMessage(), // ← Quitar en producción
            ], 500);
        }
    }

    /**
     * GET /api/orders/{order}
     * Muestra una orden específica.
     */
    public function show(Request $request, Order $order)
    {
        // 🚨 IMPORTANTE ENTENDERLO 🚨
        // Verificamos que la orden pertenezca al usuario autenticado.
        // Sin esto, cualquier usuario logueado podría ver las órdenes
        // de otros solo cambiando el ID en la URL.
        //
        // Esto se llama "authorization" (autorización) y es distinto
        // de "authentication" (autenticación).
        //   - Autenticación: ¿quién eres?
        //   - Autorización: ¿qué puedes hacer?
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para ver esta orden.',
            ], 403);
        }

            $order->load('items.product');

            return response()->json($order, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
