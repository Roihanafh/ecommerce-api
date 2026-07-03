<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends BaseApiController
{
    public function __construct(
        protected ProductService $productService
    ) {}

    #[OA\Get(
        path: '/api/v1/products',
        summary: 'Get paginated list of products',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Search by name or description'),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filter by category'),
            new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), description: 'Filter by active status'),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, schema: new OA\Schema(type: 'number'), description: 'Minimum price'),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, schema: new OA\Schema(type: 'number'), description: 'Maximum price'),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'price', 'stock', 'created_at']), description: 'Sort field'),
            new OA\Parameter(name: 'sort_dir', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']), description: 'Sort direction'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getAll($request);

        return $this->successResponse(ProductResource::collection($products)->response()->getData(true));
    }

    #[OA\Post(
        path: '/api/v1/products',
        summary: 'Create a new product',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'category_id', 'price', 'stock'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Laptop Gaming'),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'price', type: 'number', example: 15000000),
                        new OA\Property(property: 'stock', type: 'integer', example: 10),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                    ]
                )
            )
        ),
        tags: ['Products'],
        responses: [
            new OA\Response(response: 201, description: 'Product created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request);
        $product->load('category');

        return $this->createdResponse(new ProductResource($product), 'Product created');
    }

    #[OA\Get(
        path: '/api/v1/products/{id}',
        summary: 'Get a single product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return $this->successResponse(new ProductResource($product));
    }

    #[OA\Put(
        path: '/api/v1/products/{id}',
        summary: 'Update a product',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'category_id', 'price', 'stock'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Laptop Gaming'),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'price', type: 'number', example: 15000000),
                        new OA\Property(property: 'stock', type: 'integer', example: 10),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                    ]
                )
            )
        ),
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $updated = $this->productService->update($request, $product);
        $updated->load('category');

        return $this->updatedResponse(new ProductResource($updated), 'Product updated');
    }

    #[OA\Delete(
        path: '/api/v1/products/{id}',
        summary: 'Delete a product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return $this->messageResponse('Product deleted');
    }
}
