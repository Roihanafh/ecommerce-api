<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItemResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'product', ref: '#/components/schemas/ProductResource', nullable: true),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 30000000),
    ]
)]
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'subtotal' => $this->whenLoaded('product', fn () => $this->quantity * $this->product->price),
        ];
    }
}
