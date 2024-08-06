<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\HTTPException;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Http\Resources\ProductDetailResource;
use App\Http\Requests\CreateProductDetailRequest;
use App\Http\Requests\UpdateProductDetailRequest;
use DB;
use Carbon\Carbon;

class ProductDetailController extends Controller
{   

    /**
        * @OA\Get(
        *     path="/api/products/{id}/detail",
        *     summary="Get product detail",
        *     tags={"Product Detail"},
        *     @OA\Parameter(
        *         ref="#/components/parameters/getById" 
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Get product detail successfully",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string"),
        *             @OA\Property(property="data", type="object")
        *         )
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Resource not found",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Resource not found")
        *         )
        *     )
        * )
    */
    public function getProductDetail($id) {
        $product = Product::find($id);

        if (!$product) {
            throw HTTPException::NOT_FOUND();
        }

        $detail = $product->detail;

        if (!$detail) {
            throw HTTPException::NOT_FOUND('Product not has any detail');
        }

        return response()->json([
            'message' => 'Get product detail successfully',
            'data' => new ProductDetailResource($detail)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/detail",
     *     tags={"Product Detail"},
     *     summary="Create a new product detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_detail_intro", type="string", example="Introduction to product"),
     *             @OA\Property(property="product_detail_desc", type="string", example="Detailed description of product"),
     *             @OA\Property(property="product_detail_weight", type="integer", example=500),
     *             @OA\Property(property="product_detail_mfg", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="product_detail_exp", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="product_detail_origin", type="string", example="Vietnam"),
     *             @OA\Property(property="product_detail_manual", type="string", example="Manual instructions")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product detail created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Created user successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Detail already exists",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Detail already exists"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Create product detail failed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Create product detail failed"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message details"
     *             )
     *         )
     *     )
     * )
     */
    public function createProductDetail($id, CreateProductDetailRequest $request) {
        $product = Product::find($id);

        if (!$product) {
            throw HTTPException::NOT_FOUND();
        }

        $detail = $product->detail;

        if ($detail) {
            throw HTTPException::CONSTRAINT_VIOLATION('Detail already exists');
        }

        $request->validated();

        try {
            DB::beginTransaction();

            $dateString = $request->product_detail_mfg;
            $dateOject = Carbon::parse($dateString); // chuyển về oject dạng date
            $date = $dateOject->format('Y-m-d');

            $detailCreated = ProductDetail::create([
                'product_detail_intro' => $request->product_detail_intro,
                'product_detail_desc' => $request->product_detail_desc,
                'product_detail_weight' => $request->product_detail_weight,
                'product_detail_mfg' => $date,
                'product_detail_exp' => $request->product_detail_exp,
                'product_detail_origin' => $request->product_detail_origin,
                'product_detail_manual' => $request->product_detail_manual,
                'product_id' => $product->id
            ]);

            DB::commit();
    
            return response()->json([
                'message' => 'Created user successfully',
                'data' => new ProductDetailResource($detailCreated)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Create product detail failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/products/{id}/detail",
     *     tags={"Product Detail"},
     *     summary="Update product detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product_detail_intro",
     *                 type="string",
     *                 example="New introduction text"
     *             ),
     *             @OA\Property(
     *                 property="product_detail_desc",
     *                 type="string",
     *                 example="New description text"
     *             ),
     *             @OA\Property(
     *                 property="product_detail_weight",
     *                 type="number",
     *                 format="float",
     *                 example=12.5
     *             ),
     *             @OA\Property(
     *                 property="product_detail_mfg",
     *                 type="string",
     *                 example="2023-08-05"
     *             ),
     *             @OA\Property(
     *                 property="product_detail_exp",
     *                 type="integer",
     *                 example=24
     *             ),
     *             @OA\Property(
     *                 property="product_detail_origin",
     *                 type="string",
     *                 example="Vietnam"
     *             ),
     *             @OA\Property(
     *                 property="product_detail_manual",
     *                 type="string",
     *                 example="User manual text"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated product detail successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Updated product detail successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product or detail not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product or detail not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Update product detail failed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Update product detail failed"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Detailed error message"
     *             )
     *         )
     *     )
     * )
     */
    public function updateProductDetail($id, UpdateProductDetailRequest $request) {
        $product = Product::find($id);
    
        if (!$product) {
            throw HTTPException::NOT_FOUND();
        }
    
        $detail = $product->detail;
    
        if (!$detail) {
            throw HTTPException::NOT_FOUND('Detail not found');
        }
    
        $request->validated();
    
        try {
            DB::beginTransaction();
    
            $dateString = $request->product_detail_mfg;
            $dateOject = Carbon::parse($dateString); // chuyển về object dạng date
            $date = $dateOject->format('Y-m-d');
    
            $detail->update([
                'product_detail_intro' => $request->product_detail_intro,
                'product_detail_desc' => $request->product_detail_desc,
                'product_detail_weight' => $request->product_detail_weight,
                'product_detail_mfg' => $date,
                'product_detail_exp' => $request->product_detail_exp,
                'product_detail_origin' => $request->product_detail_origin,
                'product_detail_manual' => $request->product_detail_manual,
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Updated product detail successfully',
                'data' => new ProductDetailResource($detail)
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Update product detail failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**
     * @OA\Delete(
     *     path="/api/products/{id}/detail",
     *     tags={"Product Detail"},
     *     summary="Delete a product detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product detail deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product detail deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product detail not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product detail not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Constraint violation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product detail cannot be deleted due to existing dependencies"
     *             )
     *         )
     *     )
     * )
     */
    public function deleteProductDetail($id) {
        $product = Product::find($id);

        if (!$product) {
            throw HTTPException::NOT_FOUND('Product not found');
        }

        $detail = $product->detail;

        if (!$detail) {
            throw HTTPException::NOT_FOUND('Product detail not found');
        }  

        $detail->delete();

        return response()->json([
            'message' => 'Product detail deleted successfully',
        ], 204);
    }
}
