<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ImportHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Fitness Foods API",
 *      description="Documentação da API do desafio Open Food Facts",
 *      @OA\Contact(email="seu-email@exemplo.com")
 * )
 */

class ProductController extends Controller
{
    /**
 * @OA\Get(
 *     path="/",
 *     operationId="getSystemStatus",
 *     tags={"System"},
 *     summary="Obter informações do sistema",
 *     @OA\Response(
 *         response=200,
 *         description="Status da API, conexão com DB, cron e uso de memória"
 *     )
 * )
 */

    public function status()
    {
        $latestImport = ImportHistory::orderBy('imported_at', 'desc')->first();
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);

        return response()->json([
            'status' => 'OK',
            'db_connection' => DB::connection()->getDatabaseName(),
            'last_cron' => optional($latestImport)->imported_at,
            'uptime' => now()->diffInMinutes(app()->startTime ?? now()) . ' min',
            'memory_usage_mb' => $memoryUsage,
        ]);
    }
/**
 * @OA\Get(
 *     path="/products",
 *     operationId="getProducts",
 *     tags={"Products"},
 *     summary="Lista todos os produtos com paginação",
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Número da página",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de produtos retornada com sucesso"
 *     )
 * )
 */


    public function index(Request $request)
    {
        $pageSize = $request->get('per_page', 10);
        return Product::where('status', '!=', 'trash')->paginate($pageSize);
    }
/**
 * @OA\Get(
 *     path="/products/{code}",
 *     operationId="getProductByCode",
 *     tags={"Products"},
 *     summary="Obter um produto pelo código",
 *     @OA\Parameter(
 *         name="code",
 *         in="path",
 *         description="Código do produto",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Produto encontrado"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Produto não encontrado"
 *     )
 * )
 */

    public function show($code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        return response()->json($product);
    }
/**
 * @OA\Put(
 *     path="/products/{code}",
 *     operationId="updateProduct",
 *     tags={"Products"},
 *     summary="Atualizar um produto",
 *     @OA\Parameter(
 *         name="code",
 *         in="path",
 *         description="Código do produto",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="product_name", type="string"),
 *             @OA\Property(property="brands", type="string"),
 *             @OA\Property(property="quantity", type="string"),
 *             example={"product_name": "Novo nome", "brands": "Marca X", "quantity": "500g"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Produto atualizado"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Produto não encontrado"
 *     )
 * )
 */

    public function update(Request $request, $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        $product->update($request->all());
        return response()->json(['message' => 'Produto atualizado com sucesso']);
    }
/**
 * @OA\Delete(
 *     path="/products/{code}",
 *     operationId="deleteProduct",
 *     tags={"Products"},
 *     summary="Marcar um produto como trash",
 *     @OA\Parameter(
 *         name="code",
 *         in="path",
 *         description="Código do produto",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Produto marcado como trash"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Produto não encontrado"
 *     )
 * )
 */

    public function destroy($code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        $product->status = 'trash';
        $product->save();

        return response()->json(['message' => 'Produto marcado como trash']);
    }
}
