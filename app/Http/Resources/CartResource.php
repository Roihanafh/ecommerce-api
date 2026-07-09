<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CartItemResource')
        ),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'checked_out', 'abandoned'], example: 'active'),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 30000000),
        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2024-01-01T00:00:00.000000Z'),
    ]
)]
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'total' => $this->whenLoaded('items', function () {
                return $this->items->sum(fn ($item) => $item->quantity * ($item->product->price ?? 0));
            }),
            'created_at' => $this->created_at,
        ];
    }
}
