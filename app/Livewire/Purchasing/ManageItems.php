<?php

namespace App\Livewire\Purchasing;

use App\Models\Item;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageItems extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $category = 'material';

    public string $unitOfMeasure = '';

    public string $unitPrice = '0';

    public bool $isActive = true;

    public function render()
    {
        $items = Item::when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")
            ->orWhere('code', 'like', "%{$this->search}%")))
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.purchasing.manage-items', [
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
        $item = Item::findOrFail($id);
        $this->editingId = $item->id;
        $this->code = $item->code;
        $this->name = $item->name;
        $this->category = $item->category;
        $this->unitOfMeasure = $item->unit_of_measure;
        $this->unitPrice = (string) $item->unit_price;
        $this->isActive = $item->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('items', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(Item::CATEGORIES))],
            'unitOfMeasure' => ['required', 'string', 'max:20'],
            'unitPrice' => ['required', 'numeric', 'min:0'],
        ]);

        Item::updateOrCreate(['id' => $this->editingId], [
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'unit_of_measure' => $this->unitOfMeasure,
            'unit_price' => $this->unitPrice,
            'is_active' => $this->isActive,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Item tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'unitOfMeasure']);
        $this->category = 'material';
        $this->unitPrice = '0';
        $this->isActive = true;
        $this->resetErrorBag();
    }
}
