<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTokenResource extends JsonResource
{
    /**
     * The token that should be included with the response.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $token
     * @return void
     */
    public function __construct($resource, $token)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource),
            'token' => $this->token,
        ];
    }
}
