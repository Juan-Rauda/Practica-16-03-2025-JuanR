public function index(Request $request)
{
    $query = Order::query();

    // Filtrar por categoría
    if ($request->has('category_id')) {
        $query->whereHas('products', function($q) use ($request) {
            $q->where('category_id', $request->category_id);
        });
    }

    // Filtrar por producto
    if ($request->has('product_id')) {
        $query->whereHas('products', function($q) use ($request) {
            $q->where('products.id', $request->product_id);
        });
    }

    // Filtrar órdenes solo para el usuario conectado
    if ($request->has('my_orders')) {
        $query->where('user_id', auth()->id());
    }

    $orders = $query->with(['user', 'products.category'])
                    ->latest()
                    ->get()
                    ->map(function($order) {
                        return [
                            'id' => $order->id,
                            'fecha_orden' => $order->created_at->format('Y-m-d H:i'),
                            'cliente' => $order->user->name,
                            'productos' => $order->products->map(function($product) {
                                return [
                                    'nombre' => $product->name,
                                    'categoría' => $product->category->name,
                                    'cantidad' => $product->pivot->quantity,
                                    'precio' => number_format($product->price, 2)
                                ];
                            }),
                            'total' => number_format($order->total, 2)
                        ];
                    });

    return response()->json($orders);
}
