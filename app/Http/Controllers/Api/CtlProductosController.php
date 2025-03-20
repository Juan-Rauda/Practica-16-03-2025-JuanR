<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\CtlInventerio;
use App\Models\CtlProductos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CtlProductosController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categoriaId = $request->query('categoria_id');
            $nombre = $request->query('nombre');

            $query = CtlProductos::with([
                "categoria" => function ($query) {
                    $query->select(['id', 'nombre']);
                },
                "inventario"
            ]);

            if ($categoriaId) {
                $query->where('categoria_id', $categoriaId);
            }

            if ($nombre) {
                $query->where('nombre', 'like', "%$nombre%");
            }

            $products = $query->paginate(10);

            return ApiResponse::success('Lista de productos', 200, $products);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $message = [
                "nombre.required" => "Nombre es requerido",
                "nombre.max" => "El nombre no debe pasar de 255 caracteres",
                "nombre.unique" => "El nombre ya existe",
                "precio.required" => "Precio es requerido",
                "image.required" => "Imagen es requerida",
                "cantidad.required" => "Cantidad es requerida",
                "cantidad.numeric" => "Cantidad debe ser un número"
            ];

            $validators = Validator::make($request->all(), [
                "nombre" => "required|max:255|unique:ctl_productos,nombre",
                "precio" => "required|numeric",
                "image" => "required",
                "cantidad" => "required|numeric"
            ]);

            if ($validators->fails()) {
                return response()->json([
                    'errors' => $validators->errors()
                ], 422);
            }

            DB::beginTransaction();
            $producto = new CtlProductos();
            $producto->fill($request->all());
            if ($producto->save()) {
                $inventario = new CtlInventerio();
                $inventario->cantidad = $request->cantidad;
                $inventario->product_id = $producto->id;
                DB::commit();
                if ($inventario->save()) {
                    return ApiResponse::success('Se creó el producto', 200, $producto);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage());
        }
    }

    public function updateInventario(Request $request, $id)
    {
        try {
            $inventario = CtlInventerio::find($id);
            if (!$inventario) {
                return ApiResponse::error('Inventario no encontrado', 404);
            }

            $inventario->cantidad += $request->cantidad;
            if ($inventario->save()) {
                return ApiResponse::success('Inventario actualizado', 200);
            }
        } catch (\Exception $th) {
            return ApiResponse::error($th->getMessage(), 422);
        }
    }

    public function getProductosFiltrados(Request $request)
    {
        try {
            $query = CtlProductos::query();

            if ($request->has('categoria')) {
                $query->where('categoria_id', $request->categoria);
            }

            if ($request->has('min_precio') && $request->has('max_precio')) {
                $query->whereBetween('precio', [$request->min_precio, $request->max_precio]);
            }

            if ($request->has('nombre')) {
                $query->where('nombre', 'LIKE', '%' . $request->nombre . '%');
            }

            if ($request->has('orden')) {
                $query->orderBy('precio', $request->orden);
            }

            $productos = $query->paginate(10);

            return ApiResponse::success('Productos filtrados', 200, $productos);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function deleteProducto($id)
    {
        try {
            $producto = CtlProductos::find($id);
            if (!$producto) {
                return ApiResponse::error('Producto no encontrado', 404);
            }

            $producto->activo = !$producto->activo;
            if ($producto->save()) {
                return ApiResponse::success('Se actualizó el producto', 200, $producto);
            }
        } catch (\Throwable $th) {
            return ApiResponse::error($th->getMessage(), 422);
        }
    }
}
