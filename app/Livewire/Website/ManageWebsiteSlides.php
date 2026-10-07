<?php

namespace App\Livewire\Website;

use App\Models\WebsiteSlide;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ManageWebsiteSlides extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $subtitle = '';

    public string $linkLabel = '';

    public string $linkUrl = '';

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
        return view('livewire.website.manage-website-slides', [
            'slides' => WebsiteSlide::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $slide = WebsiteSlide::findOrFail($id);
        $this->editingId = $slide->id;
        $this->title = (string) $slide->title;
        $this->subtitle = (string) $slide->subtitle;
        $this->linkLabel = (string) $slide->link_label;
        $this->linkUrl = (string) $slide->link_url;
        $this->isPublished = $slide->is_published;
        $this->sortOrder = (string) $slide->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'linkLabel' => ['nullable', 'string', 'max:100'],
            'linkUrl' => ['nullable', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'imageUpload' => [$this->editingId ? 'nullable' : 'required', 'image', 'max:5120'],
        ]);

        $slide = $this->editingId ? WebsiteSlide::findOrFail($this->editingId) : new WebsiteSlide();

        $data = [
            'title' => $this->title ?: null,
            'subtitle' => $this->subtitle ?: null,
            'link_label' => $this->linkLabel ?: null,
            'link_url' => $this->linkUrl ?: null,
            'is_published' => $this->isPublished,
            'sort_order' => (int) $this->sortOrder,
        ];

        if ($this->imageUpload) {
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $data['image_path'] = $this->imageUpload->store('website/slides', 'public');
        }

        if ($this->editingId) {
            $slide->update($data);
        } else {
            WebsiteSlide::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Slide tersimpan.');
    }

    public function delete(int $id): void
    {
        $slide = WebsiteSlide::findOrFail($id);
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();
        session()->flash('success', 'Slide dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'subtitle', 'linkLabel', 'linkUrl', 'imageUpload']);
        $this->isPublished = true;
        $this->sortOrder = '0';
        $this->resetErrorBag();
    }
}
