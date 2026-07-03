<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    #[OA\Get(
        path: '/api/v1/categories',
        summary: 'Get all categories',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CategoryResource')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index()
    {
        return $this->categoryService->index();
    }

    #[OA\Post(
        path: '/api/v1/categories',
        summary: 'Create a new category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic products'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Category created'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CategoryResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreCategoryRequest $request)
    {
        return $this->categoryService->store($request);
    }

    #[OA\Get(
        path: '/api/v1/categories/{id}',
        summary: 'Get a single category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CategoryResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function show(Category $category)
    {
        return $this->categoryService->show($category);
    }

    #[OA\Put(
        path: '/api/v1/categories/{id}',
        summary: 'Update a category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic products'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Category updated'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CategoryResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        return $this->categoryService->update($request, $category);
    }

    #[OA\Delete(
        path: '/api/v1/categories/{id}',
        summary: 'Delete a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Category deleted'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function destroy(Category $category)
    {
        return $this->categoryService->destroy($category);
    }
}
