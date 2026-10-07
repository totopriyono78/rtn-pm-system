<?php

namespace App\Livewire\Admin;

use App\Models\Region;
use App\Models\Site;
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

    // Site form — lokasi fisik GPS untuk validasi radius presensi teknisi.
    public bool $showSiteModal = false;

    public ?int $editingSiteId = null;

    public string $siteUnitId = '';

    public string $siteCode = '';

    public string $siteName = '';

    public string $siteAddress = '';

    public string $siteLatitude = '';

    public string $siteLongitude = '';

    public string $siteRadiusMeters = '200';

    // Keputusan client 2026-10-07: radius bisa dimatikan total per site
    // (presensi diterima dari mana saja), terpisah dari sekadar menaikkan
    // angka radius_meters -- lihat Site::isWithinRadius().
    public bool $siteRadiusCheckEnabled = true;

    public bool $siteIsActive = true;

    public function render()
    {
        return view('livewire.admin.manage-locations', [
            'regions' => Region::withCount('units')->orderBy('name')->get(),
            'units' => Unit::with('region')->orderBy('name')->get(),
            'sites' => Site::with('unit.region')->orderBy('name')->get(),
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

    // ===== Site (lokasi fisik + GPS) =====

    public function openCreateSite(): void
    {
        $this->reset(['editingSiteId', 'siteUnitId', 'siteCode', 'siteName', 'siteAddress', 'siteLatitude', 'siteLongitude']);
        $this->siteRadiusMeters = '200';
        $this->siteRadiusCheckEnabled = true;
        $this->siteIsActive = true;
        $this->showSiteModal = true;
    }

    public function openEditSite(int $id): void
    {
        $site = Site::findOrFail($id);
        $this->editingSiteId = $site->id;
        $this->siteUnitId = (string) $site->unit_id;
        $this->siteCode = (string) $site->code;
        $this->siteName = $site->name;
        $this->siteAddress = (string) $site->address;
        $this->siteLatitude = (string) $site->latitude;
        $this->siteLongitude = (string) $site->longitude;
        $this->siteRadiusMeters = (string) $site->radius_meters;
        $this->siteRadiusCheckEnabled = $site->radius_check_enabled;
        $this->siteIsActive = $site->is_active;
        $this->showSiteModal = true;
    }

    public function saveSite(): void
    {
        $this->validate([
            'siteUnitId' => ['nullable', Rule::exists('units', 'id')],
            'siteCode' => ['nullable', 'string', 'max:50'],
            'siteName' => ['required', 'string', 'max:255'],
            'siteAddress' => ['nullable', 'string', 'max:1000'],
            'siteLatitude' => ['required', 'numeric', 'between:-90,90'],
            'siteLongitude' => ['required', 'numeric', 'between:-180,180'],
            // Batas atas dinaikkan dari 5.000m jadi 100.000m (100km) --
            // keputusan client 2026-10-07: admin bisa set radius "sangat
            // luas" kalau ingin presensi longgar tanpa mematikan validasi
            // sepenuhnya. Mematikan total pakai siteRadiusCheckEnabled.
            'siteRadiusMeters' => ['required', 'integer', 'min:10', 'max:100000'],
        ]);

        Site::updateOrCreate(['id' => $this->editingSiteId], [
            'unit_id' => $this->siteUnitId ?: null,
            'code' => $this->siteCode ?: null,
            'name' => $this->siteName,
            'address' => $this->siteAddress ?: null,
            'latitude' => $this->siteLatitude,
            'longitude' => $this->siteLongitude,
            'radius_meters' => $this->siteRadiusMeters,
            'radius_check_enabled' => $this->siteRadiusCheckEnabled,
            'is_active' => $this->siteIsActive,
        ]);

        $this->showSiteModal = false;
        session()->flash('success', 'Site tersimpan.');
    }

    public function deleteSite(int $id): void
    {
        Site::findOrFail($id)->delete();
        session()->flash('success', 'Site dihapus.');
    }
}
