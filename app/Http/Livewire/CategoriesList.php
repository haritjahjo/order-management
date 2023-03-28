<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Collection;

class CategoriesList extends Component
{
    use WithPagination;
    public Category $category;

    public bool $showModal = false;

    public array $active = [];
    public Collection $categories;

    public function render()
    {
        //$categories = Category::paginate(10);
        //$this->categories = Category::paginate(10);
        $cats = Category::orderBy('position')->paginate(10);
        $links = $cats->links();
        $this->categories = collect($cats->items());

        $this->active = $this->categories->mapWithKeys(
            fn (Category $item) => [$item['id'] => (bool) $item['is_active']]
        )->toArray();

        return view('livewire.categories-list', [
            //'categories' => $this->categories,
            'links' => $links,
        ]);
    }

    public function openModal()
    {
        $this->showModal = true;

        $this->category = new Category();
    }

    public function updatedCategoryName()
    {
        $this->category->slug = Str::slug($this->category->name);
    }

    protected function rules(): array
    {
        return [
            'category.name' => ['required', 'string', 'min:3'],
            'category.slug' => ['nullable', 'string'],
        ];
    }

    public function save()
    {
        $this->validate();

        $this->category->position = Category::max('position') + 1;

        $this->category->save();

        $this->reset('showModal');
    }

    public function toggleIsActive($categoryId)
    {
        Category::where('id', $categoryId)->update([
            'is_active' => $this->active[$categoryId],
        ]);
    }

    public function updateOrder($list)
    {
        foreach ($list as $item) {
            $cat = $this->categories->firstWhere('id', $item['value']);

            if ($cat['position'] != $item['order']) {
                Category::where('id', $item['value'])->update(['position' => $item['order']]);
            }
        }
    }
}
