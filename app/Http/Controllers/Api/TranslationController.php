<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Translation\ExportTranslationRequest;
use App\Http\Requests\Api\Translation\StoreTranslationRequest;
use App\Http\Requests\Api\Translation\UpdateTranslationRequest;
use App\Http\Resources\Api\TranslationResource;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * @OA\Get(
     *     path="/translations",
     *     summary="Get translations with filters",
     *     description="Returns a paginated list of translations with optional filters",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter by key (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="welcome")
     *     ),
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         description="Filter by value (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Welcome")
     *     ),
     *     @OA\Parameter(
     *         name="language_id",
     *         in="query",
     *         description="Filter by language ID",
     *         required=false,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="language_code",
     *         in="query",
     *         description="Filter by language code",
     *         required=false,
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter by tag name",
     *         required=false,
     *         @OA\Schema(type="string", example="mobile")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, example=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Translation")),
     *             @OA\Property(property="first_page_url", type="string", example="http://localhost/api/translations?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=10),
     *             @OA\Property(property="last_page_url", type="string", example="http://localhost/api/translations?page=10"),
     *             @OA\Property(property="links", type="array", @OA\Items(
     *                 @OA\Property(property="url", type="string", nullable=true),
     *                 @OA\Property(property="label", type="string", example="Next &raquo;"),
     *                 @OA\Property(property="active", type="boolean")
     *             )),
     *             @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost/api/translations?page=2"),
     *             @OA\Property(property="path", type="string", example="http://localhost/api/translations"),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *             @OA\Property(property="to", type="integer", example=15),
     *             @OA\Property(property="total", type="integer", example=150)
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
    public function index(Request $request)
    {
        $filters = $request->only(['key', 'value', 'language_id', 'language_code', 'tag', 'per_page']);
        $translations = $this->translationService->searchTranslations($filters);

        return TranslationResource::collection($translations);
    }

    /**
     * @OA\Post(
     *     path="/translations",
     *     summary="Create a new translation",
     *     description="Creates a new translation entry",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "value", "language_id"},
     *             @OA\Property(property="key", type="string", example="welcome_message"),
     *             @OA\Property(property="value", type="string", example="Welcome to our application"),
     *             @OA\Property(property="language_id", type="integer", format="int64", example=1),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="mobile"),
     *                 description="Optional array of tag names"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
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
     *                     property="key",
     *                     type="array",
     *                     @OA\Items(type="string", example="The key field is required.")
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
    public function store(StoreTranslationRequest $request)
    {
        $validated = $request->validated();
        $translation = $this->translationService->createTranslation($validated);

        return  TranslationResource::make($translation->load(['language', 'tags']))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/translations/{id}",
     *     summary="Get a specific translation",
     *     description="Returns details for a specific translation",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation not found.")
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
    public function show(Translation $translation)
    {
        return TranslationResource::make($translation->load(['language', 'tags']));
    }

    /**
     * @OA\Put(
     *     path="/translations/{id}",
     *     summary="Update a translation",
     *     description="Updates an existing translation entry",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="key", type="string", example="welcome_message_updated"),
     *             @OA\Property(property="value", type="string", example="Welcome to our updated application"),
     *             @OA\Property(property="language_id", type="integer", format="int64", example=1),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="web"),
     *                 description="Optional array of tag names"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation not found.")
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
     *                     property="language_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected language id is invalid.")
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
    public function update(UpdateTranslationRequest $request, Translation $translation)
    {
        $validated = $request->validated();
        $translation = $this->translationService->updateTranslation($translation, $validated);

        return TranslationResource::make($translation->load(['language', 'tags']));
    }

    /**
     * @OA\Delete(
     *     path="/translations/{id}",
     *     summary="Delete a translation",
     *     description="Deletes an existing translation entry",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Translation deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation not found.")
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
    public function destroy(Translation $translation)
    {
        $this->translationService->deleteTranslation($translation);

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/export",
     *     summary="Export translations as JSON",
     *     description="Exports translations for a specific language and optional tags as JSON for frontend use",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Language code",
     *         required=true,
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Parameter(
     *         name="tags[]",
     *         in="query",
     *         description="Filter by tag names (can specify multiple)",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", example="mobile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "welcome_message": "Welcome to our application",
     *                 "login_button": "Sign In",
     *                 "register_button": "Create Account"
     *             }
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
     *                     property="language",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected language is invalid.")
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
    public function export(ExportTranslationRequest $request)
    {
        $validated = $request->validated();
        $translations = $this->translationService->getTranslationsForLanguage(
            $validated['language'],
            $validated['tags'] ?? []
        );

        return response()->json($translations);
    }
}
