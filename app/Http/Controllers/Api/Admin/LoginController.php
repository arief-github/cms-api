<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        // gunakan validasi
        $validator = Validator::make($request->all(), [
           'email' => 'required|email',
           'password' => 'required'
        ]);

        // response jika validasi terjadi
        if($validator->fails()) {
          return response()->json($validator->errors(), 422);
        }

        // GET "email" dan "password" dari input
        $credentials = $request->only('email', 'password');

        // check jika email dan password tidak sesuai
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            // kembalikan response login gagal
            return response()->json([
                'success' => false,
                'message' => 'Email or Password Incorrect'
            ], 401);
        }

        // kembalikan repsonse login success
        return response()->json([
            'success' => true,
            'user' => auth()->guard('api')->user(),
            'token' => $token
        ], 200);
    }

    public function getUser()
    {
        // kembalikan response data user yang sedang login dengan status 200
        return response()->json([
            'success' => true,
            'user' => auth()->guard('api')->user()
        ], 200);
    }

    public function refreshToken(Request $request)
    {
        $refreshToken = JWTAuth::refresh(JWTAuth::getToken());

        // tambahkan user dengan token baru
        $user = JWTAuth::setToken($refreshToken)->toUser();

        // tambahkan header "Authorization" dengan type Bearer + Token Baru
        $request->headers->set('Authorization', 'Bearer '.$refreshToken);

        // kembalikan data user dengan token baru, dengan status 200
        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $refreshToken
        ], 200);
    }

    public function logout()
    {
        // hilangkan token jwt
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        // berikan response sukses logout
        return response()->json([
            'success' => true
        ], 200);
    }
}
