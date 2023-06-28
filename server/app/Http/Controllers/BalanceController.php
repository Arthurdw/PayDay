<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BalanceController extends Controller
{
    private Balance $balance;

    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }

    public function add(Request $request, string $lang): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        $validator = $this->validateAddRequest($request, $lang);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $balance = $this->balance->create(
            $request->source_id,
            $user->id,
            $request->in,
            $request->out,
            $request->subscription
        );

        return response()->json($balance);
    }

    public function getBalance(Request $request, string $lang): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        $page = $request->input('page') ?? 1;
        $limit = $request->input('limit') ?? 10;

        $user = Auth::user();
        $data = $this->balance->getAllFrom($user->id, $page, $limit);
        return response()->json($data);
    }

    private function validateAddRequest(Request $request, string $lang): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'source_id' => 'required|integer|exists:sources,id',
            'in' => 'required|numeric',
            'out' => 'required|numeric',
            'subscription' => 'required|boolean',
        ], [
            "source_id.required" => Translation::template('required', $lang, 'source_id'),
            "source_id.integer" => Translation::template('integer', $lang, 'source_id'),
            "source_id.exists" => Translation::template('exists', $lang, 'source_id'),
            "in.required" => Translation::template('required', $lang, 'in'),
            "in.numeric" => Translation::template('numeric', $lang, 'in'),
            "out.required" => Translation::template('required', $lang, 'out'),
            "out.numeric" => Translation::template('numeric', $lang, 'out'),
            "subscription.required" => Translation::template('required', $lang, 'subscription'),
            "subscription.boolean" => Translation::template('boolean', $lang, 'subscription'),
        ]);
    }

    public function delete(string $lang, int $id): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        $balance = $this->balance->find($id);

        if (!$balance) {
            return Translation::NotFoundResponse($lang);
        }

        $user = Auth::user();

        if ($balance->user_id !== $user->id) {
            return Translation::UnauthorizedResponse($lang);
        }

        $balance->delete();
        return Translation::SuccessResponse($lang);
    }
}
