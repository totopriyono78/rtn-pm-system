<?php

namespace App\Livewire\Admin;

use App\Models\Region;
use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ManageLocations extends Component
{
    public string $activeTab = 'region';

    // Region form
    public bool $showRegionModal = false;

    public ?int $editingRegionId = null;

    public string $regionCode = '';

    public string $regionName = '';

    // Unit form
    public bool $showUnitModal = false;

    public ?int $editingUnitId = null;

    public string $unitRegionId = '';

    public string $unitCode = '';

    public string $unitName = '';

    public function render()
    {
        return view('livewire.admin.manage-locations', [
            'regions' => Region::withCount('units')->orderBy('name')->get(),
            'units' => Unit::with('region')->orderBy('name')->get(),
        ]);
    }

    public function openCreateRegion(): void
    {
        $this->reset(['editingRegionId', 'regionCode', 'regionName']);
        $this->showRegionModal = true;
    }

    public function openEditRegion(int $id): void
    {
        $region = Region::findOrFail($id);
        $this->editingRegionId = $region->id;
        $this->regionCode = $region->code;
        $this->regionName = $region->name;
        $this->showRegionModal = true;
    }

    public function saveRegion(): void
    {
        $this->validate([
            'regionCode' => ['required', 'string', 'max:20', Rule::unique('regions', 'code')->ignore($this->editingRegionId)],
            'regionName' => ['required', 'string', 'max:255'],
        ]);

        Region::updateOrCreate(['id' => $this->editingRegionId], [
            'code' => $this->regionCode,
            'name' => $this->regionName,
        ]);

        $this->showRegionModal = false;
        session()->flash('success', 'Region tersimpan.');
    }

    public function deleteRegion(int $id): void
    {
        Region::findOrFail($id)->delete();
        session()->flash('success', 'Region dihapus.');
    }

    public function openCreateUnit(): void
    {
        $this->reset(['editingUnitId', 'unitRegionId', 'unitCode', 'unitName']);
        $this->showUnitModal = true;
    }

    public function openEditUnit(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->editingUnitId = $unit->id;
        $this->unitRegionId = (string) $unit->region_id;
        $this->unitCode = (string) $unit->code;
        $this->unitName = $unit->name;
        $this->showUnitModal = true;
    }

    public function saveUnit(): void
    {
        $this->validate([
            'unitRegionId' => ['required', Rule::exists('regions', 'id')],
            'unitCode' => ['nullable', 'string', 'max:20'],
            'unitName' => ['required', 'string', 'max:255'],
        ]);

        Unit::updateOrCreate(['id' => $this->editingUnitId], [
            'region_id' => $this->unitRegionId,
            'code' => $this->unitCode,
            'name' => $this->unitName,
        ]);

        $this->showUnitModal = false;
        session()->flash('success', 'Unit tersimpan.');
    }

    public function deleteUnit(int $id): void
    {
        Unit::findOrFail($id)->delete();
        session()->flash('success', 'Unit dihapus.');
    }
}
