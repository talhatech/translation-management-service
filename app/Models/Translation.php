<?php
// app/Models/Translation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Translation",
 *     required={"key", "value", "language_id"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="key", type="string", example="welcome_message"),
 *     @OA\Property(property="value", type="string", example="Welcome to our application"),
 *     @OA\Property(property="language_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="language", ref="#/components/schemas/Language"),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Tag")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class Translation extends BaseModel
{

    protected $fillable = ['key', 'value', 'language_id'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'translation_tag');
    }

}
