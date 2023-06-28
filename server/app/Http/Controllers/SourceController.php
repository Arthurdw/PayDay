<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SourceController extends Controller
{
    private Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    public function create(Request $request, string $lang): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateCreateRequest($request, $lang);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $source = $this->source->create(
            $request->name,
            $request->imageUrl,
            $request->type,
            $request->descriptionEnglish,
            $request->descriptionDutch
        );

        return response()->json($source, 201);
    }

    private function validateCreateRequest(Request $request, string $lang): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|unique:sources',
            'imageUrl' => 'required|string',
            'type' => 'required|string',
            'descriptionEnglish' => 'required|string',
            'descriptionDutch' => 'required|string',
        ], [
            'name.required' => Translation::template('required', $lang, 'name'),
            'name.string' => Translation::template('string', $lang, 'name'),
            'name.unique' => Translation::template('unique', $lang, 'name'),
            'imageUrl.required' => Translation::template('required', $lang, 'imageUrl'),
            'imageUrl.string' => Translation::template('string', $lang, 'imageUrl'),
            'type.required' => Translation::template('required', $lang, 'type'),
            'type.string' => Translation::template('string', $lang, 'type'),
            'descriptionEnglish.required' => Translation::template('required', $lang, 'descriptionEnglish'),
            'descriptionEnglish.string' => Translation::template('string', $lang, 'descriptionEnglish'),
            'descriptionDutch.required' => Translation::template('required', $lang, 'descriptionDutch'),
            'descriptionDutch.string' => Translation::template('string', $lang, 'descriptionDutch'),
        ]);
    }

    public function getAll(Request $request, string $lang): \Illuminate\Http\JsonResponse
    {
        $page = $request->input('page') ?? 1;
        $limit = $request->input('limit') ?? 10;
        $sources = $this->source->getAll($page, $limit, $lang);
        return response()->json($sources, 200);
    }

    public function getAllPairs(string $_): \Illuminate\Http\JsonResponse {
        $sources = $this->source->getAllPairs();
        return response()->json($sources, 200);
    }

    public function get(string $lang, int $id): \Illuminate\Http\JsonResponse
    {
        $source = $this->source->getOne($id, $lang);

        if (!$source) {
            return Translation::NotFoundResponse($lang);
        }

        return response()->json($source, 200);
    }

    public function update(Request $request, string $lang, int $id): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateUpdateRequest($request, $lang);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $source = $this->source->find($id);

        if (!$source) {
            return Translation::NotFoundResponse($lang);
        }

        $source->name = $request->name ?? $source->name;
        $source->imageUrl = $request->imageUrl ?? $source->imageUrl;
        $source->type = $request->type ?? $source->type;
        $source->descriptionEnglish = $request->descriptionEnglish ?? $source->descriptionEnglish;
        $source->descriptionDutch = $request->descriptionDutch ?? $source->descriptionDutch;
        $source->save();

        return response()->json($source, 200);
    }

    private function validateUpdateRequest(Request $request, $lang): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'string|unique:sources',
            'imageUrl' => 'string',
            'type' => 'string',
            'descriptionEnglish' => 'string',
            'descriptionDutch' => 'string',
        ], [
            'name.string' => Translation::template('string', $lang, 'name'),
            'name.unique' => Translation::template('unique', $lang, 'name'),
            'imageUrl.string' => Translation::template('string', $lang, 'imageUrl'),
            'type.string' => Translation::template('string', $lang, 'type'),
            'descriptionEnglish.string' => Translation::template('string', $lang, 'descriptionEnglish'),
            'descriptionDutch.string' => Translation::template('string', $lang, 'descriptionDutch'),
        ]);
    }

    public function delete(string $lang, int $id): \Illuminate\Http\JsonResponse
    {
        $source = $this->source->find($id);

        if (!$source) {
            return Translation::NotFoundResponse($lang);
        }

        $source->delete();
        return Translation::SuccessResponse($lang);
    }
}
