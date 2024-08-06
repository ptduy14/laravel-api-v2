<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Exceptions\HTTPException;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
Use Carbon\Carbon;
use DB;

class OrderController extends Controller
{   
        /**
     * @OA\Get(
     *     path="/api/users/orders",
     *     tags={"Orders"},
     *     summary="Get all orders for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Get orders successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Get orders successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not have any orders",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User not have any orders"
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
    public function getUserOrders() {
        $user = Auth::user();
        $orders = $user->orders;
        
        if ($orders->isEmpty()) {
            throw HTTPException::NOT_FOUND('User not have any orders');
        }

        return response()->json([
            'message' => 'Get orders successfully',
            'data' => OrderResource::collection($orders)
        ], 200);
    }


        /**
     * @OA\Get(
     *     path="/api/users/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get order by user ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Get order successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Get order successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order not found"
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
    public function getUserOrder($id) {
        $user = Auth::user();
        $order = $user->orders->find($id);

        if (!$order) {
            throw HTTPException::NOT_FOUND('Order not found');
        }

        return response()->json([
            'message' => 'Get order successfully',
            'data' => new OrderResource($order)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"reciver", "phone", "address", "method_payment"},
     *                 @OA\Property(
     *                     property="reciver",
     *                     type="string",
     *                     example="John Doe",
     *                     description="Recipient's name"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="1234567890",
     *                     description="Recipient's phone number",
     *                     pattern="^[0-9]{10}$"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string",
     *                     example="123 Main St, Anytown, USA",
     *                     description="Delivery address"
     *                 ),
     *                 @OA\Property(
     *                     property="method_payment",
     *                     type="string",
     *                     example="Credit Card",
     *                     description="Payment method"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order created successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Order details",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid input data"
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
    public function createUserOrder(CreateOrderRequest $request) {
        $user = Auth::user();
        $cart = $user->cart;

        if(!$cart) {
            throw HTTPException::NOT_FOUND('User not have any products in cart');
        }   

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $user->id,
                'reciver' => $request->reciver,
                'phone' => $request->phone,
                'address' => $request->address,
                'total_money' => $cart->total_price,
                'order_date' => Carbon::now()->toDateString(),
                'order_status' => OrderStatus::PENDING->value,
                'order_status_desc' => OrderStatus::getOrderStatusDesc(OrderStatus::PENDING->value),
                'method_payment' => $request->method_payment,
                'total_quantity' => $cart->total_quantity
            ]);
    
            $cart->products->map(function ($product) use ($order) {
                $order->products()->attach($product->id, ['quantity' => $product->pivot->quantity]);
            });

            $cart->delete();
            $cart->products()->detach();

            DB::commit();

            return response()->json([
                'message' => 'Order was created successfully',
                'data' => new OrderResource($order)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Create failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders Admin"},
     *     summary="Retrieve all orders",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Get orders successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No orders found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No orders found"
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
    public function getAllOrders() {
        $orders = Order::all();

        if ($orders->isEmpty()) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'Get orders successfully',
            'data' => OrderResource::collection($orders),
        ], 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/orders/{id}",
     *     tags={"Orders Admin"},
     *     summary="Update order status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="order_status",
     *                 type="integer",
     *                 description="New order status",
     *                 example=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order status updated successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order has been cancelled so cannot update"
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
    public function updateOrderStatus($id, UpdateOrderStatus $request) {
        $request->validated();

        $user = Auth::user();
        $order = Order::find($id);

        if (!$order) {
            throw HTTPException::NOT_FOUND('Order not found');
        }

        $order_status = $order->order_status;

        switch ($order_status) {
            case OrderStatus::CANCELED->value:
                throw HTTPException::BAD_REQUEST('Order has been cancelled so cannot update');
                break;
            
            case OrderStatus::COMPLETED->value:
                throw HTTPException::BAD_REQUEST('Order has been completed so cannot update');
                break;

            default:
                $order->update([
                    'order_status' => $request->order_status,
                    'order_status_desc' => OrderStatus::getOrderStatusDesc($request->order_status),
                ]);

                return response()->json([
                    'message' => 'Order status update successfully',
                    'data' => new OrderResource($order),
                ], 200);
                break;
        }
    }
}
