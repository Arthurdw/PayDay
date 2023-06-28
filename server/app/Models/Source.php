<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    protected $table = 'sources';
    protected $primaryKey = 'id';

    public function create(
        string $name,
        string $imageUrl,
        string $type,
        string $descriptionEnglish,
        string $descriptionDutch
    ): Source
    {
        $this->name = $name;
        $this->imageUrl = $imageUrl;
        $this->type = $type;
        $this->descriptionEnglish = $descriptionEnglish;
        $this->descriptionDutch = $descriptionDutch;
        $this->save();
        return $this;
    }

    public function getAll(int $page, int $limit, string $language)
    {
        return $this
            ->select(
                'id',
                'name',
                'imageUrl',
                'type',
            )
            ->selectRaw(
                match ($language) {
                    'be' => 'descriptionDutch',
                    default => 'descriptionEnglish'
                } . " AS description",
            )
            ->orderBy('name', 'ASC')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getOne(int $id, string $language)
    {
        return $this
            ->select(
                'id',
                'name',
                'imageUrl',
                'type',
            )
            ->selectRaw(
                match ($language) {
                    'be' => 'descriptionDutch',
                    default => 'descriptionEnglish'
                } . " AS description",
            )
            ->find($id);
    }

    public function getAllPairs()
    {
        return $this
            ->select("id", "name")
            ->orderBy("name", "ASC")
            ->get();
    }
}
