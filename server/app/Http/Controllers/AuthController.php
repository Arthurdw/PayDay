<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login(Request $request, string $lang): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateLoginRequest($request, $lang);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('name', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return Translation::UnauthorizedResponse($lang);
        }

        return $this->buildLoginResponse($token);
    }

    public function register(Request $request, string $lang): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateRegisterRequest($request, $lang);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->createUser($request->name, $request->password);
        $token = Auth::attempt(['name' => $request->name, 'password' => $request->password]);

        return $this->buildLoginResponse($token);
    }

    public function getMe(string $lang): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        $user = Auth::user();
        unset($user->password);

        return response()->json($user, 200);
    }

    private function validateLoginRequest(Request $request, string $lang): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'required|string',
            'password' => 'required|string',
        ], [
            'name.required' => Translation::template('required', $lang, 'name'),
            'password.required' => Translation::template('required', $lang, 'password'),
            'name.string' => Translation::template('type', $lang, 'name', 'string'),
            'password.string' => Translation::template('type', $lang, 'password', 'string'),
        ]);
    }

    private function validateRegisterRequest(Request $request, $lang): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'name.required' => Translation::template('required', $lang, 'name'),
            'password.required' => Translation::template('required', $lang, 'password'),
            'name.string' => Translation::template('type', $lang, 'name', 'string'),
            'password.string' => Translation::template('type', $lang, 'password', 'string'),
            'name.unique' => Translation::template('unique', $lang, 'name'),
            'password.min' => Translation::template('min', $lang, 'password', '8'),
        ]);
    }

    private function createUser($name, $password): User
    {
        return $this->user->create($name, bcrypt($password));
    }

    private function buildLoginResponse($token): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        return response()->json(['id' => $user->id])
            ->cookie('token', $token, 60 * 24 * 30, '/', env('APP_DOMAIN'), false, true);
    }

    public function logout(string $lang): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        Auth::logout();
        return response()->json(['message' => Translation::getT('success', $lang)])
            ->cookie('token', null, -1);
    }
}
