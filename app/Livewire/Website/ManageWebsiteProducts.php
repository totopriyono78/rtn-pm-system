<?php

namespace App\Livewire\Website;

use App\Models\WebsiteProduct;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageWebsiteProducts extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $category = '';

    public string $shortDescription = '';

    public string $description = '';

    public bool $isPublished = true;

    public string $sortOrder = '0';

    /** @var mixed */
    public $imageUpload;

    public ?string $currentImagePath = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-website'), 403);
    }

    public function render()
    {
        $products = WebsiteProduct::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.website.manage-website-products', [
            'products' => $products,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $product = WebsiteProduct::findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->category = (string) $product->category;
        $this->shortDescription = (string) $product->short_description;
        $this->description = (string) $product->description;
        $this->isPublished = $product->is_published;
        $this->sortOrder = (string) $product->sort_order;
        $this->currentImagePath = $product->image_path;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'shortDescription' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'imageUpload' => ['nullable', 'image', 'max:4096'],
        ]);

        $product = $this->editingId ? WebsiteProduct::findOrFail($this->editingId) : new WebsiteProduct();

        $data = [
            'name' => $this->name,
            'slug' => $this->buildSlug($this->name, $this->editingId),
            'category' => $this->category ?: null,
            'short_description' => $this->shortDescription ?: null,
            'description' => $this->description ?: null,
            'is_published' => $this->isPublished,
            'sort_order' => (int) $this->sortOrder,
        ];

        if ($this->imageUpload) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $this->imageUpload->store('website/products', 'public');
        }

        if ($this->editingId) {
            $product->update($data);
        } else {
            WebsiteProduct::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Produk tersimpan.');
    }

    public function delete(int $id): void
    {
        $product = WebsiteProduct::findOrFail($id);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        session()->flash('success', 'Produk dihapus.');
    }

    private function buildSlug(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (WebsiteProduct::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'category', 'shortDescription', 'description', 'imageUpload', 'currentImagePath']);
        $this->isPublished = true;
        $this->sortOrder = '0';
        $this->resetErrorBag();
    }
}
