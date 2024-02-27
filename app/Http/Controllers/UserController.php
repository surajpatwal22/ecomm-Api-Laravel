<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\product;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => 0
            ]);
            if ($user) {
                return response()->json([
                    'message' => 'User registered successfully',
                    'status' => 201,
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'message' => 'User not Registered',
                    'status' => 400,
                    'success' => false
                ], 500);
            }
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::where("email", $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Email Not found',
                    'status' => 404,
                    'success' => false
                ]);
            } else {
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'message' => 'Invalid Credentials',
                        'status' => 400,
                        'success' => false
                    ]);
                } else {
                    $token = $user->createToken('Personal Access Token', ['expires' => now()->addDays(7)])->plainTextToken;
                    return response()->json([
                        'message' => 'Login Successfully',
                        'token' => $token,
                        'status' => 200,
                        'success' => true
                    ]);
                }
            }
        }
    }
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $otp = rand(1000, 9999);
                $create = Otp::create([
                    'otp' => $otp,
                    'user_id' => $user->id,
                    'status' => 1
                ]);
                if ($create) {
                    try {
                        $sendmail = Mail::send('mail.verificationMail', ['otp' => $otp], function ($m) use ($user) {
                            $m->to($user->email, $user->name)->subject("Verification otp");
                        });
                        return response()->json([
                            'message' => 'Otp sent to mail',
                            'status' => 200,
                            'success' => true
                        ]);
                    } catch (Exception $e) {
                        //    return $e->getMessage();
                        return response()->json(['error' => $e->getMessage()], 500);
                    }

                } else {
                    return response()->json([
                        'message' => 'something went wrong',
                        'status' => 400,
                        'success' => false
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Email Not found',
                    'status' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $check = Otp::where(['user_id' => $user->id, 'otp' => $request->otp])->first();
                if ($check) {
                    $token = $user->createToken('Personal Access Token')->plainTextToken;
                    return response()->json([

                        'message' => 'otp verified',
                        'token' => $token,
                        'status' => 200,
                        'success' => true
                    ]);
                } else {
                    return response()->json([
                        'message' => 'credentials not matched',
                        'status' => 400,
                        'success' => false
                    ]);
                }

            } else {
                return response()->json([
                    'message' => 'Email Not found',
                    'status' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {

            // $user = User::find(Auth::user()->id);
            // if($user){
            //     $user->password = Hash::make($request->password);
            //     $user->save();
            //     return response()->json([
            //         'message'=> 'password updated successfully',
            //         'status' => 200 ,
            //         'success' => true
            //     ]);
            // }else{
            //     return response()->json([
            //         'message'=> 'User Token found',
            //         'status' => 404 ,
            //         'success' => false
            //     ]);
            // }
            $password = Hash::make($request->input('password'));
            $user = Auth::user();
            if (!empty($user)) {
                $user1 = User::find($user->id);
                $user1->update(['password' => $password]);
                return response()->json([
                    'message' => 'Password updated successfully',
                    'status' => 200,
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'message' => 'User not found',
                    'status' => 404,
                    'success' => false
                ]);
            }


        }
    }

    public function showAllProduct(Request $request)
    {

        $sortBy = $request->input('sort_by', 'price');
        $sortOrder = $request->input('sort_order', 'desc');
        $products = Product::orderBy($sortBy, $sortOrder)->get();
        return response()->json([
            'products' => $products,
            'status' => 200,
            'success' => true
        ]);
    }

    public function show($id)
    {
        $product = product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'something went wrong',
                'status' => 400,
                'success' => false
            ], 400);
        } else {
            return response()->json([
                'message' => 'Data is retrived successfully',
                'product' => $product,
                'status' => 200,
                'success' => true
            ], 200);
        }
    }

    public function getProfile()
    {
        return response()->json([
            'user' => Auth::user(),
            'status' => 200,
            'success' => true
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::user()->id);
        if ($user) {
            $validator = Validator::make($request->all(), [
                'email' => 'email',
                'contact' => 'min:10|max:10',
                'name' => 'string'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors(),
                    'status' => 400,
                    'success' => false
                ]);
            } else {
                if ($request->file) {
                    try {
                        $file = $request->file('file');
                        $imageName = time() . '.' . $file->extension();
                        $imagePath = public_path() . '/user_profile';

                        $file->move($imagePath, $imageName);

                        $user->profile = '/user_profile/' . $imageName;
                    } catch (Exception $e) {
                        return $e;
                    }
                }

                $user->email = $request->email;
                if ($request->contact) {
                    $user->contact = $request->contact;
                }
                $user->name = $request->name;
                if ($request->bio) {
                    $user->bio = $request->bio;
                }
                $user->save();

                return response()->json([
                    'message' => 'updated successfully',
                    'status' => 200,
                    'success' => true
                ]);
            }

        } else {
            return response()->json([
                'message' => 'user not found',
                'status' => 404,
                'success' => false
            ]);
        }
    }

    public function logout(){
        $user = Auth::user();
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json([
            'message' => 'Successfully logged out',
            'status' => 200,
            'success' => true
        ]);
    }
}


