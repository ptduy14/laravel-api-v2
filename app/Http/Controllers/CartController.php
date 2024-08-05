<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\HTTPException;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddProductCartRequest;
use App\Http\Requests\UpdateProductsCartRequest;
use DB;

class CartController extends Controller
{   
    
    /**
    *     @OA\Get(
    *        path="/api/carts",
    *        summary="Get cart user",
    *        tags={"Carts"},
    *        security={{"bearerAuth":{}}},
    *        @OA\Response(
    *          response=200,
    *          description="Get cart successfully",
    *          @OA\JsonContent(
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="data", type="object")
    *          )
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized"
    *     )
    * )
    */
    public function getCurrentCarts() {
        $user = Auth::user();

        if (!$user->cart) {
           throw HTTPException::NOT_FOUND('Cart not found');
        }

        return response()->json([
            'message' => 'Get carts successfully',
            'data' => new CartResource($user->cart)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/carts/products",
     *     tags={"Carts"},
     *     summary="Add product cart",
     *     security={{"bearerAuth":{}}},  
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="number"),
     *             @OA\Property(property="quantity", type="number"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="New product has been successfully added to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="New product has been successfully added to cart"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function addProductCart(AddProductCartRequest $request) {
        $request->validated();
        $product = Product::find($request->product_id);

        if (!$product) {
            throw HTTPException::NOT_FOUND('Product not found');
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $quantity = $request->quantity;
            $productId = $product->id;

            $cart = $user->cart;

            if (!$cart) {
                // Nếu cart chưa tồn tại, tạo mới cart
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'total_quantity' => $quantity,
                    'total_price' => $product->product_price * $quantity,
                ]);

                $cart->products()->attach($productId, ['quantity' => $quantity]);
            } else {
                // Nếu cart đã tồn tại, cập nhật cart
                $cart->update([
                    'total_quantity' => $cart->total_quantity + $quantity,
                    'total_price' => $cart->total_price + $product->product_price * $quantity,
                ]);

                $productExists = $cart->products()->find($productId);

                if ($productExists) {
                    // Cập nhật số lượng sản phẩm nếu đã có trong cart
                    $cart->products()->updateExistingPivot($productId, ['quantity' => $productExists->pivot->quantity + $quantity]);
                } else {
                    // Thêm sản phẩm mới vào cart nếu chưa có
                    $cart->products()->attach($productId, ['quantity' => $quantity]);
                }
            }

                DB::commit();

                return response()->json([
                    'message' => 'New product has been successfully added to cart',
                    'data' => new CartResource($cart)
                ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'New product added failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/carts/products",
     *     tags={"Carts"},
     *     summary="Update product cart",
     *     security={{"bearerAuth":{}}},  
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 example={
     *                     {
     *                         "product_id": 1,
     *                         "quantity": 3,
     *                     }, 
     *                     {
     *                         "product_id": 3,
     *                         "quantity": 1,
     *                     }
     *                 },
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="product_id",
     *                         type="number",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="number",
     *                         example=1
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Update product cart has been successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Update product cart has been successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function updateProductsCart(UpdateProductsCartRequest $request) {
        $request->validated();

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $cart = $user->cart;
        
            if (!$cart) {
                throw HTTPException::NOT_FOUND('Cart not found');
            }

            $totalQuantityCart = $cart->total_quantity;
            $totalPriceCart = $cart->total_price;

            foreach ($request->products as $item) {
                $product = Product::find($item['product_id']);

                $updatedQuantity = $item['quantity'];
                $currentQuantityProduct = $cart->products()->where('product_id', $product->id)->first()->pivot->quantity;

                if ($updatedQuantity === $currentQuantityProduct) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Cart updated successfully',
                        'data' => new CartResource($cart)
                    ]);
                }

                $newQuantityProduct = 0;

                if ($updatedQuantity > $currentQuantityProduct) {
                    $newQuantityProduct = $updatedQuantity;
                    $totalQuantityCart += $updatedQuantity - $currentQuantityProduct;
                    $totalPriceCart += $product->product_price * ($updatedQuantity - $currentQuantityProduct);
                } else {
                    $newQuantityProduct = $updatedQuantity;
                    $totalQuantityCart -= $currentQuantityProduct - $updatedQuantity;
                    $totalPriceCart -= $product->product_price * ($currentQuantityProduct - $updatedQuantity);
                }

                $cart->products()->updateExistingPivot($product->id, ['quantity' => $newQuantityProduct]);
            }

            $cart->update([
                'total_quantity' => $totalQuantityCart,
                'total_price' => $totalPriceCart
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Cart updated successfully',
                'data' => new CartResource($cart)
            ], 200);

        } catch (\Exeption $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Update product cart failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }  

    /**
        * @OA\Delete(
        *     path="/api/carts/products/{id}",
        *     summary="Delete product cart",
        *     tags={"Carts"},
        *     security={{"bearerAuth":{}}},  
        *     @OA\Parameter(
        *         ref="#/components/parameters/getById" 
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Delete product successfully",
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
    public function deleteProductCart($id) {
        $user = Auth::user();
        $cart = $user->cart;
        
        if (!$user->cart) {
            throw HTTPException::NOT_FOUND('Cart not found');
        }

        $product = $cart->products()->find($id);

        if (!$product) {
            throw HTTPException::NOT_FOUND('Product not found');
        }

        try {
            DB::beginTransaction();

            $totalQuantityCart = $cart->total_quantity;
            $totalPriceCart = $cart->total_price;

            $quantityProductRemove = $cart->products()->where('product_id', $product->id)->first()->pivot->quantity;

            $totalQuantityCart -= $quantityProductRemove;
            $totalPriceCart -= $product->product_price * $quantityProductRemove;

            $cart->products()->detach($product->id);
            $cart->update([
                'total_quantity' => $totalQuantityCart,
                'total_price' => $totalPriceCart
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Product cart delete successfully',
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update product cart failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
