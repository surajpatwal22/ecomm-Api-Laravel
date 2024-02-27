<?php

namespace App\Http\Controllers;

use App\Models\product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\CountValidator\CountValidatorAbstract;

class AdminController extends Controller
{
    public function addProducts(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'file' => 'required|file',
            'price' => 'required|numeric',
            'category' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {

            try {
                $file = $request->file('file');
                $imageName = time() . '.' . $file->extension();
                $imagePath = public_path() . '/product_image';
                $file->move($imagePath, $imageName);

                $product = product::create([
                    'name' => $request->name,
                    'price' => $request->price,
                    'description' => $request->description,
                    'category' => $request->category,
                    'file' => '/product_image/' . $imageName
                ]);

                $product->save();

                return response()->json([
                    'message' => 'Product  added successfully!',
                    'data' => $product,
                    'status' => 201,
                    'success' => true
                ], 201);
            } catch (Exception $e) {
                return $e;
            }
        }

    }

    public function updateProduct(Request $request, $id)
    {
        $product = product::find($id);
        // dd($product);
        if ($product) {
            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'description' => 'string',
                'file' => 'file',
                'price' => 'numeric',
                'category' => 'string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors(),
                    'status' => 400,
                    'success' => false
                ]);
            } else {
                if (($request->hasFile('file')) ) {
                    try {
                        $file = $request->file('file');
                        $imageName = time() . '.' . $file->extension();
                        $imagePath = public_path() . '/product_image';
                        $file->move($imagePath, $imageName);
                        $product->file = '/product_image/' . $imageName;
                    } catch (Exception $e) {
                        return $e;
                    }
                }

                if ($request->filled('name')) {
                    $product->name = $request->name;
                }
            
                if ($request->filled('description')) {
                    $product->description = $request->description;
                }
            
                if ($request->filled('price')) {
                    $product->price = $request->price;
                }
            
                if ($request->filled('category')) {
                    $product->category = $request->category;
                }

                $product->save();
                // dd($product->toArray());
                return response()->json([
                    'message' => 'Product has been updated successfully',
                    'status' => 200,
                    'product' => $product,
                ], 200);
            }
        }else{
            return response()->json([
                'error' => 'Product not found',
                'status' => 404,
                'success' => false
            ],404);
        }
    }

    public function deleteProduct($id){
        $product = product::find($id);
        if(!$product){
            return response()->json([
               "message"=>"Product not found",
               "status"=>404,
               'success' => false
           ],404);
       }else{
           $product->delete();
           return response()->json([
            'message' => 'Product deleted successfully',
            'status' => 200,
            'success' => true
        ]);
       }
    }


}