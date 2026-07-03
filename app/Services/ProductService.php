<?php

namespace App\Services;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAll(Request $request): LengthAwarePaginator
    {
        return $this->productRepository->paginate([
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'is_active' => $request->input('is_active'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort_by' => $request->input('sort_by'),
            'sort_dir' => $request->input('sort_dir'),
            'per_page' => $request->input('per_page', 15),
        ]);
    }

    public function create(StoreProductRequest $request): Product
    {
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        return $this->productRepository->create([
            'category_id' => $request->input('category_id'),
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'image' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): Product
    {
        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        return $this->productRepository->update($product, [
            'category_id' => $request->input('category_id'),
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'description' => $request->input('description') ?? $product->description,
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'image' => $imagePath,
            'is_active' => $request->has('is_active')
                ? $request->boolean('is_active')
                : $product->is_active,
        ]);
    }

    public function delete(Product $product): void
    {
        $this->productRepository->delete($product);
    }
}
