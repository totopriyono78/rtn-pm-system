<?php

namespace App\Livewire\Website;

use App\Models\WebsiteService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageWebsiteServices extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $division = 'service_maintenance';

    public string $icon = 'check';

    public string $shortDescription = '';

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
        $services = WebsiteService::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('division')
            ->orderBy('sort_order')
            ->paginate(10);

        return view('livewire.website.manage-website-services', [
            'services' => $services,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $service = WebsiteService::findOrFail($id);
        $this->editingId = $service->id;
        $this->name = $service->name;
        $this->division = $service->division;
        $this->icon = (string) ($service->icon ?: 'check');
        $this->shortDescription = (string) $service->short_description;
        $this->description = (string) $service->description;
        $this->isPublished = $service->is_published;
        $this->sortOrder = (string) $service->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'division' => ['required', 'in:service_maintenance,sales_pompa'],
            'icon' => ['required', 'string', 'max:40'],
            'shortDescription' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'imageUpload' => ['nullable', 'image', 'max:4096'],
        ]);

        $service = $this->editingId ? WebsiteService::findOrFail($this->editingId) : new WebsiteService();

        $data = [
            'name' => $this->name,
            'slug' => $this->buildSlug($this->name, $this->editingId),
            'division' => $this->division,
            'icon' => $this->icon,
            'short_description' => $this->shortDescription ?: null,
            'description' => $this->description ?: null,
            'is_published' => $this->isPublished,
            'sort_order' => (int) $this->sortOrder,
        ];

        if ($this->imageUpload) {
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $data['image_path'] = $this->imageUpload->store('website/services', 'public');
        }

        if ($this->editingId) {
            $service->update($data);
        } else {
            WebsiteService::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Layanan tersimpan.');
    }

    public function delete(int $id): void
    {
        $service = WebsiteService::findOrFail($id);
        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }
        $service->delete();
        session()->flash('success', 'Layanan dihapus.');
    }

    private function buildSlug(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (WebsiteService::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'shortDescription', 'description', 'imageUpload']);
        $this->division = 'service_maintenance';
        $this->icon = 'check';
        $this->isPublished = true;
        $this->sortOrder = '0';
        $this->resetErrorBag();
    }
}
