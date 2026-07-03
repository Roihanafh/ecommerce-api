<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Laptop Gaming'),
        new OA\Property(property: 'slug', type: 'string', example: 'laptop-gaming'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'High performance laptop'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 15000000),
        new OA\Property(property: 'stock', type: 'integer', example: 10),
        new OA\Property(property: 'image', type: 'string', nullable: true, example: 'products/laptop.jpg'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'category', ref: '#/components/schemas/CategoryResource', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2024-01-01T00:00:00.000000Z'),
    ]
)]
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
        ];
    }
}
