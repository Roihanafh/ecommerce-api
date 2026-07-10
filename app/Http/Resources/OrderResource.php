<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'invoice', type: 'string', example: 'INV-20260708-001'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'paid', 'cancelled'], example: 'pending'),
        new OA\Property(property: 'payment_status', type: 'string', enum: ['unpaid', 'paid', 'failed'], example: 'unpaid'),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 25000000),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OrderItemResource')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-07-08T00:00:00.000000Z'),
    ]
)]
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice' => $this->invoice_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total' => $this->total_amount,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
