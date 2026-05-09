<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use App\Http\Requests\Sma\Product\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Product/Category/Index', [
            'parents' => Category::onlyParent()->get(['id as value', 'name as label', 'category_id']),

            'pagination' => new Collection(
                Category::with('category:id,name')->filter($filters)->latest()->orderBy('name')->paginate()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Category'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Category'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->{$category->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Category'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}. The record is being used for relationships.', [
            'model'  => __('Category'),
            'action' => __('deleted'),
        ]));
    }
}
