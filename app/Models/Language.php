<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Language",
 *     required={"code", "name"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="code", type="string", example="en"),
 *     @OA\Property(property="name", type="string", example="English"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class Language extends BaseModel
{

    protected $fillable = ['code', 'name', 'is_active'];

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }
}
