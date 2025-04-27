<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tag\StoreTagRequest;
use App\Http\Requests\Api\Tag\UpdateTagRequest;
use App\Http\Resources\Api\TagResource;
use App\Models\Tag;
use App\Services\TranslationService;

class TagController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * @OA\Get(
     *     path="/tags",
     *     summary="Get all tags",
     *     description="Returns a list of all available tags",
     *     tags={"Tags"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Tag")
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
        $tags = $this->translationService->getAllTags();
        return TagResource::collection($tags);
    }

    /**
     * @OA\Post(
     *     path="/tags",
     *     summary="Create a new tag",
     *     description="Creates a new tag entry",
     *     tags={"Tags"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="mobile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name has already been taken.")
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
    public function store(StoreTagRequest $request)
    {
        $validated = $request->validated();
        $tag = Tag::create($validated);

        return  TagResource::make($tag)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/tags/{id}",
     *     summary="Get a specific tag",
     *     description="Returns details for a specific tag",
     *     tags={"Tags"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tag ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag not found.")
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
    public function show(Tag $tag)
    {
        return TagResource::make($tag);
    }

    /**
     * @OA\Put(
     *     path="/tags/{id}",
     *     summary="Update a tag",
     *     description="Updates an existing tag entry",
     *     tags={"Tags"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tag ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="desktop")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag not found.")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name has already been taken.")
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
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $validated = $request->validated();
        $tag->update($validated);

        return TagResource::make($tag);
    }

    /**
     * @OA\Delete(
     *     path="/tags/{id}",
     *     summary="Delete a tag",
     *     description="Deletes an existing tag entry",
     *     tags={"Tags"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tag ID",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Tag deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag not found.")
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
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}
