<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Exceptions\HTTPException;
use App\Http\Resources\ProductResource;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use DB;

class ProductController extends Controller
{
    /**
        * @OA\Get(
        *     path="/api/products",
        *     summary="Get products",
        *     tags={"Products"},
        *     @OA\Response(
        *         response=200,
        *         description="Get products successfully",
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
    public function getAllProducts() {
        $products = Product::all();

        if (!$products) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'The action is done successfully',
            'data' => ProductResource::collection($products)
        ], 200);
    }

    /**
        * @OA\Get(
        *     path="/api/products/{id}",
        *     summary="Get product",
        *     tags={"Products"},
        *     @OA\Parameter(
        *         ref="#/components/parameters/getById" 
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Get product successfully",
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
    public function getProduct($id) {
        $product = Product::find($id);
        
        if (!$product) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'Get product successfully',
            'data' => new ProductResource($product)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Create a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_name", "product_price", "product_status", "category_id"},
     *             @OA\Property(
     *                 property="product_name",
     *                 type="string",
     *                 example="Sample Product"
     *             ),
     *             @OA\Property(
     *                 property="product_price",
     *                 type="number",
     *                 format="integer",
     *                 example=19999
     *             ),
     *                 @OA\Property(
     *                 property="product_status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="category_id",
     *                 type="integer",
     *                 example=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The item was created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The item was created successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation Error"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Created failed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Created failed"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error details"
     *             )
     *         )
     *     )
     * )
     */
    public function createProduct(CreateProductRequest $request) {
        $request->validated();

        $category = Category::find($request->input('category_id'));

        if (!$category) {
            throw HTTPException::NOT_FOUND('Category not found');
        }

        try {
            DB::beginTransaction();

            $product = Product::create([
                'product_name' => $request->input('product_name'),
                'product_price' => $request->input('product_price'),
                'product_status' => $request->input('product_status'),
                'category_id' => $request->input('category_id')
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Product was created successfully',
                'data' => new ProductResource($product)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Created failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**
     * @OA\Patch(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Update a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_name", "product_price", "product_status", "category_id"},
     *             @OA\Property(
     *                 property="product_name",
     *                 type="string",
     *                 example="Updated Product"
     *             ),
     *             @OA\Property(
     *                 property="product_price",
     *                 type="number",
     *                 format="float",
     *                 example=29.99
     *             ),
     *             @OA\Property(
     *                 property="product_status",
     *                 type="string",
     *                 example="available"
     *             ),
     *             @OA\Property(
     *                 property="category_id",
     *                 type="integer",
     *                 example=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product was updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product was updated successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product or Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product or Category not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation Error"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Updated failed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Updated failed"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error details"
     *             )
     *         )
     *     )
     * )
     */
    public function updateProduct(UpdateProductRequest $request, $id) {
        $product = Product::find($id);
        
        if (!$product) {
            throw HTTPException::NOT_FOUND();
        }

        $category = Category::find($request->input('category_id'));

        if (!$category) {
            throw HTTPException::NOT_FOUND("Category not found");
        }

        $request->validated();

        try {
            DB::beginTransaction();

            $product->update([
                'product_name' => $request->input('product_name'),
                'product_price' => $request->input('product_price'),
                'product_status' => $request->input('product_status'),
                'category_id' => $request->input('category_id'),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Product was updated successfully',
                'data' => new ProductResource($product)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Updated failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Delete a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product was deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product was deleted successfully"
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
     *         description="Constraint violation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Constraint violation"
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
     *     )
     * )
     */
    public function deleteProduct($id) {
        $product = Product::find($id);

        if (!$product) {
            throw HTTPException::NOT_FOUND();
        }

        $productDetail = $product->detail;

        if ($productDetail) {
            throw HTTPException::CONSTRAINT_VIOLATION();
        }

        $product->delete();

        return response()->json([
            'message' => 'Product was deleted successfully',
        ], 204);
    }
}
