<?php

namespace App\Http\Controllers;

use App\Models\order;
use App\Models\product;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function getallOrder()
    {
        $orders = order::orderBy("created_at", "desc")->paginate(10);
        return response()->json([
            'status' => 200,
            "success" => true,
            "orders" => $orders
        ]);
    }

    public function neworder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'count' => 'required|integer|min:1',
            'address' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'phoneno' => 'required',
            'state' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {

            $product = product::find($request->input('product_id'));
            if (!$product) {
                return response()->json([
                    'message' => 'product not found',
                    'status' => 404,
                    'success' => false
                ]);
            } else {
                $subtotal = $product->price * $request->input('count');
                $price = $product->price;
                $order = order::create([
                    'user_id' => Auth::user()->id,
                    'product_id' => $request->input('product_id'),
                    'price' => $price,
                    'count' => $request->input('count'),
                    "subtotal" => $subtotal,
                    'address' => $request->input('address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'pincode' => $request->input('pincode'),
                    'phoneno' => $request->input('phoneno'),
                ]);
                if ($order) {
                    return response()->json([
                        'message' => 'order created successfully',
                        'status' => 200,
                        'success' => true
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'something went wrong',
                        'status' => 400,
                        'success' => false
                    ], 400);
                }
            }

        }
    }

    public function myOrder(Request $request, )
    {
        $orders = order::where('user_id', Auth::user()->id)->get();

        return response()->json([
            'orders' => $orders,
            'status' => 200,
            'success' => true
        ]);
    }

    public function updateOrder(Request $request, $id)
    {
        $order = order::where('user_id', Auth::user()->id)->where('id', $id)->first();
        if ($order) {
            $validator = Validator::make($request->all(), [
                'count' => 'integer|min:1',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors(),
                    'status' => 400,
                    'success' => false
                ]);
            } else {
                if ($request->filled('count')) {
                    $order->count = $request->input('count');
                    $subtotal = $order->price * $request->input('count');
                    $order->subtotal = $subtotal;
                }
                if ($request->filled('address')) {
                    $order->address = $request->input('address');
                }

                if ($request->filled('city')) {
                    $order->city = $request->input('city');

                }

                if ($request->filled('pincode')) {
                    $order->pincode = $request->input('pincode');

                }

                if ($request->filled('phoneno')) {
                    $order->phoneno = $request->input('phoneno');
                }
                if ($request->filled('state')) {
                    $order->state = $request->input('state');
                }
                $order->save();
                
                return response()->json([
                    'message' => 'order has been updated successfully',
                    'status' => 200,
                    'product' => $order,
                ], 200);
            }
        } else {
            return response()->json([
                'error' => 'order not found',
                'status' => 404,
                'success' => false
            ], 404);
        }
    }
    public function deleteOrder(Request $request,$id){
        $order = order::where('user_id', Auth::user()->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json([
                'message' => 'order not found',
                'status' => 404,
                'success' => false
            ], 404);
        }

        $order->delete();

        return response()->json([
            'message' => 'order cancelled successfully',
            'status' => 200,
            'success' => true
        ], 200);
    }




}
