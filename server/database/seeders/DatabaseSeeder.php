<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $translations = [
            'en:unauthorized' => 'Unauthorized',
            'be:unauthorized' => 'Onvoldoende rechten',
            'en:success' => 'Success',
            'be:success' => 'Succes',
            'en:not-found' => 'Could not find the requested resource.',
            'be:not-found' => 'Kon de gevraagde bron niet vinden.',
            'en:required' => 'The \'{}\' field is required.',
            'be:required' => 'Het \'{}\' veld is verplicht.',
            'en:type' => 'The \'{}\' field must be of type \'{}\'.',
            'be:type' => 'Het \'{}\' veld moet van het type \'{}\' zijn.',
            'en:unique' => 'A record with the \'{}\' field already exists.',
            'be:unique' => 'Een record met het \'{}\' veld bestaat al.',
            'en:exists' => 'The \'{}\' field must exist. (no record with that property exists)',
            'be:exists' => 'Het \'{}\' veld moet bestaan. (geen record met die eigenschap bestaat)',
            'en:integer' => 'The \'{}\' field must be an integer.',
            'be:integer' => 'Het \'{}\' veld moet een geheel getal zijn.',
            'en:numeric' => 'The \'{}\' field must be numeric.',
            'be:numeric' => 'Het \'{}\' veld moet numeriek zijn.',
            'en:boolean' => 'The \'{}\' field must be a boolean.',
            'be:boolean' => 'Het \'{}\' veld moet een boolean zijn.',
            'en:min' => 'The \'{}\' field must be at least {}. (for strings in length, for numbers in value)',
            'be:min' => 'Het \'{}\' veld moet minstens {} zijn. (voor strings in lengte, voor getallen in waarde)',
        ];

        $translationsFormatted = [];

        foreach ($translations as $key => $value) {
            $translationsFormatted[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        $table = DB::table('translations');
        $table->truncate();
        $table->insert($translationsFormatted);
    }
}
