<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, function ($q, $categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== null, function ($q) use ($filters) {
                $q->where('is_active', (bool) $filters['is_active']);
            })
            ->when($filters['min_price'] ?? null, function ($q, $minPrice) {
                $q->where('price', '>=', $minPrice);
            })
            ->when($filters['max_price'] ?? null, function ($q, $maxPrice) {
                $q->where('price', '<=', $maxPrice);
            })
            ->orderBy(
                in_array($filters['sort_by'] ?? null, ['name', 'price', 'stock', 'created_at'])
                    ? $filters['sort_by']
                    : 'created_at',
                in_array($filters['sort_dir'] ?? null, ['asc', 'desc'])
                    ? $filters['sort_dir']
                    : 'desc'
            )
            ->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
