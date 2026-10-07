<?php

namespace App\Livewire\Website;

use App\Models\WebsitePortfolioItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageWebsitePortfolio extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $clientName = '';

    public string $category = '';

    public string $year = '';

    public string $description = '';

    public bool $isPublished = true;

    public string $sortOrder = '0';

    /** @var mixed */
    public $imageUpload;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-website'), 403);
    }

    public function render()
    {
        $items = WebsitePortfolioItem::when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->paginate(10);

        return view('livewire.website.manage-website-portfolio', [
            'items' => $items,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = WebsitePortfolioItem::findOrFail($id);
        $this->editingId = $item->id;
        $this->title = $item->title;
        $this->clientName = (string) $item->client_name;
        $this->category = (string) $item->category;
        $this->year = (string) ($item->year ?? '');
        $this->description = (string) $item->description;
        $this->isPublished = $item->is_published;
        $this->sortOrder = (string) $item->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'clientName' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'imageUpload' => ['nullable', 'image', 'max:4096'],
        ]);

        $item = $this->editingId ? WebsitePortfolioItem::findOrFail($this->editingId) : new WebsitePortfolioItem();

        $data = [
            'title' => $this->title,
            'slug' => $this->buildSlug($this->title, $this->editingId),
            'client_name' => $this->clientName ?: null,
            'category' => $this->category ?: null,
            'year' => $this->year !== '' ? (int) $this->year : null,
            'description' => $this->description ?: null,
            'is_published' => $this->isPublished,
            'sort_order' => (int) $this->sortOrder,
        ];

        if ($this->imageUpload) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $this->imageUpload->store('website/portfolio', 'public');
        }

        if ($this->editingId) {
            $item->update($data);
        } else {
            WebsitePortfolioItem::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Portofolio tersimpan.');
    }

    public function delete(int $id): void
    {
        $item = WebsitePortfolioItem::findOrFail($id);
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();
        session()->flash('success', 'Portofolio dihapus.');
    }

    private function buildSlug(string $title, ?int $ignoreId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (WebsitePortfolioItem::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'clientName', 'category', 'year', 'description', 'imageUpload']);
        $this->isPublished = true;
        $this->sortOrder = '0';
        $this->resetErrorBag();
    }
}
