<?php

namespace App\Http\Controllers;

use App\Models\review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function  review(Request $request) {
        $validator = Validator::make($request->all(),[
            'product_id'=>'required',
            'review'=>'required',
            'star' => 'required|numeric|min:1|max:5',
            'name' => ['nullable','string']
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{

            $create_review = review::create([
                'user_id'=> Auth::user()->id,
                'product_id'=>$request->product_id,
                'review'=>$request->review,
                'star'=>$request->star,
                'name'=>Auth::user()->name ? Auth::user()->name :'Anonymous'
            ]);
            if($create_review){
                return response()->json([
                    'message' => 'Review created successfully',
                    'status' =>200,
                    'success' =>true
                ],200);
            }else{
                return response()->json([
                    'message' => 'something went wrong',
                    'status' =>400,
                    'success' =>false
                ],400);
            }
        }
    } 

    public function getAllReviews(Request $request) {
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $reviews = review::orderBy($sortBy, $sortOrder)->get();
    
        if ($reviews->isEmpty()) {
            return response()->json([
                'message' => 'No reviews found',
                'status' => 404,
                'success' => false
            ], 404);
        }
    
        return response()->json([
            'reviews' => $reviews,
            'status' => 200,
            'success' => true
        ], 200);
    }

    public function deleteReview($id){
        $review = review::find($id);
        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
                'status' => 404,
                'success' => false
            ], 404);
        }

        if ($review->user_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this review',
                'status' => 403,
                'success' => false
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
            'status' => 200,
            'success' => true
        ], 200);


    }
}
