<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="Translation Management API",
 *     version="1.0.0",
 *     description="API for managing translations",
 *     @OA\Contact(
 *         email="admin@example.com"
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
 *     bearerFormat="Token"
 * )
 */
class SwaggerConfig
{
    // This is just a placeholder class to hold Swagger annotations
}
