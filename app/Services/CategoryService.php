<?php

namespace App\Services;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->categoryRepository->getAllOrderedByName();
    }

    public function create(StoreCategoryRequest $request): Category
    {
        return $this->categoryRepository->create([
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): Category
    {
        return $this->categoryRepository->update($category, [
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'description' => $request->input('description') ?? $category->description,
            'is_active' => $request->has('is_active')
                ? $request->boolean('is_active')
                : $category->is_active,
        ]);
    }

    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }
}
