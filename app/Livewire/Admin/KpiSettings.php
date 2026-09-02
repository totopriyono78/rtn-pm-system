<?php

namespace App\Livewire\Admin;

use App\Models\KpiSetting;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class KpiSettings extends Component
{
    public string $mode = KpiSetting::MODE_AVERAGE;

    public string $minHoursDay = '8';

    public string $minHoursWeek = '40';

    public string $minHoursMonth = '160';

    public string $averageMarginPercent = '100';

    public bool $includeZeroHourEmployees = false;

    public bool $showThresholdBadges = true;

    public function mount(): void
    {
        $setting = KpiSetting::current();

        $this->mode = $setting->mode;
        $this->minHoursDay = (string) $setting->min_hours_day;
        $this->minHoursWeek = (string) $setting->min_hours_week;
        $this->minHoursMonth = (string) $setting->min_hours_month;
        $this->averageMarginPercent = (string) $setting->average_margin_percent;
        $this->includeZeroHourEmployees = $setting->include_zero_hour_employees;
        $this->showThresholdBadges = $setting->show_threshold_badges;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'mode' => ['required', Rule::in(array_keys(KpiSetting::MODES))],
            'minHoursDay' => ['required', 'numeric', 'min:0', 'max:24'],
            'minHoursWeek' => ['required', 'numeric', 'min:0', 'max:168'],
            'minHoursMonth' => ['required', 'numeric', 'min:0', 'max:744'],
            'averageMarginPercent' => ['required', 'integer', 'min:1', 'max:100'],
        ], [], [
            'minHoursDay' => 'Min. jam per hari',
            'minHoursWeek' => 'Min. jam per minggu',
            'minHoursMonth' => 'Min. jam per bulan',
            'averageMarginPercent' => 'Ambang persentase rata-rata',
        ]);

        $setting = KpiSetting::current();
        $setting->update([
            'mode' => $validated['mode'],
            'min_hours_day' => $validated['minHoursDay'],
            'min_hours_week' => $validated['minHoursWeek'],
            'min_hours_month' => $validated['minHoursMonth'],
            'average_margin_percent' => $validated['averageMarginPercent'],
            'include_zero_hour_employees' => $this->includeZeroHourEmployees,
            'show_threshold_badges' => $this->showThresholdBadges,
            'updated_by_user_id' => auth()->id(),
        ]);

        session()->flash('success', 'Pengaturan KPI tersimpan.');
    }

    public function render()
    {
        return view('livewire.admin.kpi-settings', [
            'modes' => KpiSetting::MODES,
            'setting' => KpiSetting::current()->load('updatedBy'),
        ]);
    }
}
