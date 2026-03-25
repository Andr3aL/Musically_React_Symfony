<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/login_check',
            openapi: new Model\Operation(
                summary: 'Obtenir un token JWT',
                description: 'Authentification avec email et mot de passe pour obtenir un token JWT',
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string', 'example' => 'user@example.com'],
                                    'password' => ['type' => 'string', 'example' => 'password123'],
                                ],
                                'required' => ['email', 'password'],
                            ],
                        ],
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Token JWT retourné',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Identifiants invalides',
                    ],
                ]
            )
        ),
    ]
)]
class AuthenticationToken
{
    public string $token;
}
