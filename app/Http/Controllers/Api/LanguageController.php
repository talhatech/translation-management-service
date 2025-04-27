<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\TranslationServiceInterface;
use App\Models\Language;
use Illuminate\Http\Request;


class LanguageController extends Controller
{
    protected $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * @OA\Get(
     *     path="/languages",
     *     summary="Get all languages",
     *     description="Returns a list of all available languages",
     *     tags={"Languages"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Language")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $languages = $this->translationService->getAllLanguages();
        return response()->json($languages);
    }


    /**
     * @OA\Post(
     *     path="/languages",
     *     summary="Create a new language",
     *     description="Creates a new language entry",
     *     tags={"Languages"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "name"},
     *             @OA\Property(property="code", type="string", example="fr"),
     *             @OA\Property(property="name", type="string", example="French"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Language created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="code",
     *                     type="array",
     *                     @OA\Items(type="string", example="The code has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        // todo: create validation separate files + add resources

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $language = Language::create($validated);

        return response()->json($language, 201);
    }


    /**
     * @OA\Get(
     *     path="/languages/{id}",
     *     summary="Get a specific language",
     *     description="Returns details for a specific language",
     *     tags={"Languages"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Language ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Language not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show(Language $language)
    {
        return response()->json($language);
    }


    /**
     * @OA\Put(
     *     path="/languages/{id}",
     *     summary="Update a language",
     *     description="Updates an existing language entry",
     *     tags={"Languages"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Language ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="fr"),
     *             @OA\Property(property="name", type="string", example="French (Updated)"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Language not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="code",
     *                     type="array",
     *                     @OA\Items(type="string", example="The code has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Language $language)
    {
        // todo: create validation separate files + add resources

        $validated = $request->validate([
            'code' => 'string|max:10|unique:languages,code,' . $language->id,
            'name' => 'string|max:255',
            'is_active' => 'boolean',
        ]);

        $language->update($validated);

        return response()->json($language);
    }


    /**
     * @OA\Delete(
     *     path="/languages/{id}",
     *     summary="Delete a language",
     *     description="Deletes an existing language entry",
     *     tags={"Languages"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Language ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Language deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Language not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function destroy(Language $language)
    {
        $language->delete();

        return response()->json(null, 204);
    }
}
