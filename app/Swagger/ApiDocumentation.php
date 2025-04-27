<?php

namespace App\Swagger;
/**
 * @OA\Info(
 *     title="Translation Management Service API",
 *     version="1.0.0",
 *     description="API for managing translations across multiple languages and contexts",
 *     @OA\Contact(
 *         email="admin@example.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication"
 * )
 * @OA\Tag(
 *     name="Languages",
 *     description="API endpoints for language management"
 * )
 * @OA\Tag(
 *     name="Tags",
 *     description="API endpoints for tag management"
 * )
 * @OA\Tag(
 *     name="Translations",
 *     description="API endpoints for translation management"
 * )
 */
