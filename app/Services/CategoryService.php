<?php

namespace App\Services;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description ?? $category->description,
            'is_active'   => $request->has('is_active')
                ? $request->boolean('is_active')
                : $category->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated',
            'data'    => new CategoryResource($category),
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted',
        ]);
    }
}
