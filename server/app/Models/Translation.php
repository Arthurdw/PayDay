<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $table = 'translations';
    protected $primaryKey = 'key';

    public static function getT(string $key, string $language, bool $hasTried = false): string
    {
        $translationKey = $language . ':' . $key;
        $translation = Translation::where('key', $translationKey)->first();

        if ($translation === null) {
            if ($hasTried) {
                return $key;
            }

            return self::getT($key, 'en', true);
        }

        return $translation->value;
    }

    public static function UnauthorizedResponse(string $language): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            ['error' => Translation::getT("unauthorized", $language)],
            401
        );
    }

    public static function NotFoundResponse($language): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            ['error' => Translation::getT("not-found", $language)],
            404
        );
    }

    public static function SuccessResponse($language): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            ['success' => Translation::getT("success", $language)],
            201
        );
    }

    public static function template(string $key, string $language, string ...$values): string
    {
        $translation = Translation::getT($key, $language);

        foreach ($values as $value) {
            $translation = str_replace("{}", $value, $translation);
        }
        return $translation;
    }

}
