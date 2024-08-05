<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Exceptions\HTTPException;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductsCategoryResource;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use DB;

class CategoryController extends Controller
{
    /**
        * @OA\Get(
        *     path="/api/categories",
        *     summary="Get categories",
        *     tags={"Categories"},
        *     @OA\Response(
        *         response=200,
        *         description="Get categories successfully",
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
    public function getAll() {
        $categories = Category::all();

        if (!$categories) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'Get categories successfully',
            'data' => CategoryResource::collection($categories)
        ], 200);
    }

    /**
        * @OA\Get(
        *     path="/api/categories/{id}",
        *     summary="Get category",
        *     tags={"Categories"},
        *     @OA\Parameter(
        *         ref="#/components/parameters/getById" 
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Get category successfully",
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
    public function getById($id) {
        $category = Category::find($id);
        
        if (!$category) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'Get category successfully',
            'data' => new CategoryResource($category)
        ], 200);
    }   


    /**
        * @OA\Get(
        *     path="/api/categories/{id}/products",
        *     summary="Get products category",
        *     tags={"Categories"},
        *     @OA\Parameter(
        *         ref="#/components/parameters/getById" 
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Get products category successfully",
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
    public function getProductsCategory($id) {
        $category = Category::find($id);

        if (!$category) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'Get products category successfully',
            'data' => ProductsCategoryResource::collection($category->products)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Create a new category",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_name", "category_desc"},
     *             @OA\Property(
     *                 property="category_name",
     *                 type="string",
     *                 example="Electronics"
     *             ),
     *             @OA\Property(
     *                 property="category_desc",
     *                 type="string",
     *                 example="All kinds of electronic devices"
     *             ),
     *             @OA\Property(
     *                 property="category_status",
     *                 type="boolean",
     *                 example=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category created successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 additionalProperties=true
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=422
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation Error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation errors occurred"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties=true
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
    public function create(CreateCategoryRequest $request) {
        $request->validated();

        try {
            DB::beginTransaction();

            $category = Category::create([
                'category_name' => $request->input('category_name'),
                'category_desc' => $request->input('category_desc'),
                'category_status' => $request->input('category_status')
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Create category successfully',
                'data' => new CategoryResource($category)
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Update a category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_name", "category_desc", "category_status"},
     *             @OA\Property(
     *                 property="category_name",
     *                 type="string",
     *                 example="Updated Electronics"
     *             ),
     *             @OA\Property(
     *                 property="category_desc",
     *                 type="string",
     *                 example="Updated description of electronic devices"
     *             ),
     *             @OA\Property(
     *                 property="category_status",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category updated successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 additionalProperties=true
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
     *                 property="status",
     *                 type="integer",
     *                 example=422
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation Error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation errors occurred"
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
     *         description="Update failed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Update failed"
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
    public function update(UpdateCategoryRequest $request, $id) {
        $category = Category::find($id);

        if (!$category) {
            throw HTTPException::NOT_FOUND();
        }

        $request->validated();

        try {
            DB::beginTransaction();

            $category->update([
                'category_name' => $request->input('category_name'),
                'category_desc' => $request->input('category_desc'),
                'category_status' => $request->input('category_status')
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Create category successfully',
                'data' => new CategoryResource($category)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Delete a category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The action is done successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=204
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The action is done successfully"
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
    public function delete($id) {
        $category = Category::find($id);
        if (!$category) {
            throw HTTPException::NOT_FOUND();
        }

        $productsCategory = $category->products;
        if ($productsCategory) {
            throw HTTPException::CONSTRAINT_VIOLATION();
        }

        $category->delete();

        return response()->json([
            'message' => 'The action is done successfully',
        ], 204); 
    }
}
