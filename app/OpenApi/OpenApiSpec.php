<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'E-Commerce API',
    version: '1.0.0',
    description: 'REST API for E-Commerce Portfolio'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class OpenApiSpec
{
}
