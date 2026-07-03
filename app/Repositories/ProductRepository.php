<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Product::with('category');

        // search by name or description
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // filter by category
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // filter by is_active
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // filter by price range
        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // sort
        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'price', 'stock', 'created_at'])
            ? $filters['sort_by']
            : 'name';
        $sortDirection = in_array($filters['sort_dir'] ?? '', ['asc', 'desc'])
            ? $filters['sort_dir']
            : 'asc';

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 10);
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
