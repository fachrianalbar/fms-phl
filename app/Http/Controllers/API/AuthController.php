<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *     title="Authentication API",
 *     version="1.0.0",
 *     description="API for user authentication including login, logout, registration, and token refresh",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="API Endpoints for Authentication"
 * )
 */
class AuthController extends Controller
{
    use ResponseJson;

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="User login",
     *     description="Login with username and password and receive an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="access-token", type="string", example="your-jwt-token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Username or password is wrong")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'     => 'required',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $credentials = $request->only('username', 'password');

        $token = auth()->guard('api')->attempt($credentials);

        if (!$token) {
            return $this->respondError(null, 'Username or password is wrong', 401);
        }

        return response()->json([
            "status" => true,
            "access-token" => $token
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="User logout",
     *     description="Logout the authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        auth()->guard('api')->logout();
        return $this->respondSuccess(null, 'Successfully logged out');
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="User registration",
     *     description="Register a new user with name, email, and password.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User successfully registered")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                "role_id" => "USER"
            ]
        ));

        return $this->respondSuccess(null, 'User successfully registered', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     tags={"Auth"},
     *     summary="Token refresh",
     *     description="Refresh the JWT token.",
     *     @OA\Response(
     *         response=200,
     *         description="Token successfully refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="refresh-token", type="string", example="your-new-jwt-token")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        $token = JWTAuth::parseToken()->refresh();
        return response()->json([
            'status' => true,
            'refresh-token' => $token
        ]);
    }
}
