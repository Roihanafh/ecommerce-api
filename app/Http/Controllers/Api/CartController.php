<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CartController extends BaseApiController
{
    public function __construct(
        protected CartService $cartService
    ) {}

    #[OA\Get(
        path: '/api/v1/cart',
        summary: 'Get current user active cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        $cart = $this->cartService->index();

        return $this->successResponse(new CartResource($cart));
    }

    #[OA\Post(
        path: '/api/v1/cart',
        summary: 'Add item to cart',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'quantity'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                ]
            )
        ),
        tags: ['Cart'],
        responses: [
            new OA\Response(response: 201, description: 'Item added to cart'),
            new OA\Response(response: 200, description: 'Stock warning'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(AddToCartRequest $request): JsonResponse
    {
        $result = $this->cartService->store($request);

        if (isset($result['warning'])) {
            return $this->errorResponse($result['message'], 422);
        }

        $result['cart']->load('items.product');

        return $this->createdResponse(new CartResource($result['cart']), 'Item added to cart');
    }

    #[OA\Put(
        path: '/api/v1/cart/{cartItem}',
        summary: 'Update cart item quantity',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', example: 3),
                ]
            )
        ),
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'cartItem', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cart item updated'),
            new OA\Response(response: 422, description: 'Stock warning or validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Cart item not found'),
        ]
    )]
    public function update(UpdateCartRequest $request, CartItem $cartItem): JsonResponse
    {
        $result = $this->cartService->update($request, $cartItem);

        if (isset($result['warning'])) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->updatedResponse(new CartItemResource($result['item']), 'Cart item updated');
    }

    #[OA\Delete(
        path: '/api/v1/cart/{cartItem}',
        summary: 'Remove item from cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'cartItem', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item removed from cart'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Cart item not found'),
        ]
    )]
    public function destroy(CartItem $cartItem): JsonResponse
    {
        $this->cartService->destroy($cartItem);

        return $this->messageResponse('Item removed from cart');
    }

    #[OA\Delete(
        path: '/api/v1/cart',
        summary: 'Clear all items from active cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(response: 200, description: 'Cart cleared'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function clear(): JsonResponse
    {
        $this->cartService->clear();

        return $this->messageResponse('Cart cleared');
    }
}
