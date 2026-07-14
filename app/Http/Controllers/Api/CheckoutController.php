<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CheckoutController extends BaseApiController
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected PaymentService $paymentService,
    ) {}

    #[OA\Post(
        path: '/api/v1/checkout',
        summary: 'Checkout active cart and create order',
        security: [['bearerAuth' => []]],
        tags: ['Checkout'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Checkout berhasil.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrderResource'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Cart kosong atau stok tidak cukup'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $order = $this->checkoutService->checkout();

        return $this->createdResponse(new OrderResource($order), 'Checkout berhasil.');
    }

    #[OA\Get(
        path: '/api/v1/orders',
        summary: 'Get order history for authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Checkout'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
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
                            items: new OA\Items(ref: '#/components/schemas/OrderResource')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function history(): JsonResponse
    {
        $orders = $this->checkoutService->history();

        return $this->successResponse(OrderResource::collection($orders)->response()->getData(true));
    }

    #[OA\Get(
        path: '/api/v1/orders/{id}',
        summary: 'Get a single order detail',
        security: [['bearerAuth' => []]],
        tags: ['Checkout'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrderResource'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $order = $this->checkoutService->show($id);

        return $this->successResponse(new OrderResource($order));
    }

    #[OA\Post(
        path: '/api/v1/payments/{order}',
        summary: 'Create Midtrans Snap payment token for an order',
        security: [['bearerAuth' => []]],
        tags: ['Payment'],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Snap token created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payment token created.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'token', type: 'string', example: 'xxxxx'),
                                new OA\Property(property: 'redirect_url', type: 'string', example: 'https://app.sandbox.midtrans.com/...'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function pay(Order $order): JsonResponse
    {
        $payment = $this->paymentService->createPayment($order);

        return $this->createdResponse([
            'token'        => $payment->snap_token,
            'redirect_url' => $payment->redirect_url,
        ], 'Payment token created.');
    }
}
