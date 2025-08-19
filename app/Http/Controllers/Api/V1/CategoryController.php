<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    public function index(Request $request)
    {
        $items = Category::where('user_id', $request->user()->id)
            ->orderBy('name')->get()
            ->map(fn($c) => $this->transform($c));

        return $this->success($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'type' => ['nullable','in:income,expense'], // optional if you use typed categories
            'color'=> ['nullable','string','max:20'],
        ]);

        $cat = Category::create($data + ['user_id' => $request->user()->id]);
        return $this->created($this->transform($cat), 'Category created');
    }

    public function show(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) return $this->forbidden();
        return $this->success($this->transform($category));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) return $this->forbidden();

        $data = $request->validate([
            'name' => ['sometimes','string','max:100'],
            'type' => ['sometimes','nullable','in:income,expense'],
            'color'=> ['sometimes','nullable','string','max:20'],
        ]);

        $category->update($data);
        return $this->success($this->transform($category), 'Category updated');
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) return $this->forbidden();
        $category->delete();
        return $this->success(null, 'Category deleted');
    }

    private function transform(Category $c): array
    {
        return [
            'id'    => $c->id,
            'name'  => $c->name,
            'type'  => $c->type,
            'color' => $c->color,
        ];
    }
}
