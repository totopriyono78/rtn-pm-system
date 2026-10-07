<?php

namespace App\Livewire\CostControl;

use App\Models\Project;
use App\Models\ProjectBudgetLine;
use App\Support\Notifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman Cost Control (SRS 4.18) -- dibangun 2026-10-07 sesuai keputusan
 * client: role ini MENGELOLA (input/edit), bukan cuma melihat, budget &
 * actual cost per proyek secara real-time. Perubahan yang diinput di sini
 * TIDAK butuh approval dari pihak lain -- langsung tersimpan, sistem cuma
 * mengirim notifikasi ke PM (Project.pic) terkait (keputusan client,
 * 2026-10-07, dikonfirmasi lewat chat terpisah dari keputusan role itu
 * sendiri).
 *
 * Planned amount dipakai dari ProjectBudgetLine.planned_amount yang sudah
 * ada sebelumnya (tapi sebelum halaman ini, TIDAK ADA UI manapun yang
 * benar-benar membuat/mengisi baris ProjectBudgetLine -- field ini baru
 * terisi pertama kali lewat halaman ini). Actual amount adalah kolom baru
 * (lihat migrasi add_actual_amount_to_project_budget_lines_table) yang
 * diisi manual Cost Control -- terpisah dari Project::used_budget yang
 * dihitung otomatis dari total PO issued, karena tidak semua pengeluaran
 * riil di lapangan tercatat lewat PO.
 */
#[Layout('layouts.app')]
class ManageProjectCost extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedProjectId = null;

    /** @var array<string, array{planned_amount: string, actual_amount: string, notes: string}> */
    public array $lines = [];

    public function mount(): void
    {
        abort_unless(Auth::user()->hasPermissionTo('manage-cost-control'), 403);
    }

    public function render()
    {
        $projects = Project::query()
            ->visibleTo(Auth::user())
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with('pic')
            ->orderBy('name')
            ->paginate(10);

        $selectedProject = null;
        $summary = null;

        if ($this->selectedProjectId) {
            $selectedProject = Project::with('pic', 'budgetLines')->find($this->selectedProjectId);

            if ($selectedProject) {
                $totalPlanned = array_sum(array_column($this->lines, 'planned_amount'));
                $totalActual = array_sum(array_column($this->lines, 'actual_amount'));

                $summary = [
                    'total_planned' => $totalPlanned,
                    'total_actual' => $totalActual,
                    'variance' => $totalPlanned - $totalActual,
                    'estimated_profit' => $selectedProject->project_value !== null
                        ? (float) $selectedProject->project_value - $totalActual
                        : null,
                ];
            }
        }

        return view('livewire.cost-control.manage-project-cost', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'summary' => $summary,
            'categories' => ProjectBudgetLine::CATEGORIES,
        ]);
    }

    public function selectProject(int $projectId): void
    {
        $project = Project::visibleTo(Auth::user())->findOrFail($projectId);

        $this->selectedProjectId = $project->id;

        $existing = ProjectBudgetLine::where('project_id', $project->id)
            ->get()
            ->keyBy('category');

        $this->lines = [];
        foreach (ProjectBudgetLine::CATEGORIES as $key => $label) {
            $row = $existing->get($key);
            $this->lines[$key] = [
                'planned_amount' => $row ? (string) $row->planned_amount : '0',
                'actual_amount' => $row ? (string) $row->actual_amount : '0',
                'notes' => $row ? (string) $row->notes : '',
            ];
        }
    }

    public function backToList(): void
    {
        $this->reset(['selectedProjectId', 'lines']);
    }

    public function save(): void
    {
        abort_unless(Auth::user()->hasPermissionTo('manage-cost-control'), 403);

        $project = Project::findOrFail($this->selectedProjectId);

        $this->validate([
            'lines.*.planned_amount' => ['required', 'numeric', 'min:0'],
            'lines.*.actual_amount' => ['required', 'numeric', 'min:0'],
            'lines.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($project) {
            foreach ($this->lines as $category => $line) {
                ProjectBudgetLine::updateOrCreate(
                    ['project_id' => $project->id, 'category' => $category],
                    [
                        'planned_amount' => $line['planned_amount'],
                        'actual_amount' => $line['actual_amount'],
                        'notes' => $line['notes'] ?: null,
                    ]
                );
            }
        });

        // Keputusan client 2026-10-07: TIDAK ada approval untuk perubahan
        // ini -- cukup notifikasi ke PM proyek terkait.
        if ($project->pic) {
            $totalPlanned = array_sum(array_column($this->lines, 'planned_amount'));
            $totalActual = array_sum(array_column($this->lines, 'actual_amount'));

            Notifier::user(
                $project->pic,
                'Update Budget & Actual Cost Proyek',
                sprintf(
                    'Cost Control (%s) memperbarui budget/actual cost proyek "%s". Total rencana: Rp %s, total aktual: Rp %s.',
                    Auth::user()->name,
                    $project->name,
                    number_format($totalPlanned, 0, ',', '.'),
                    number_format($totalActual, 0, ',', '.')
                ),
                route('cost-control.index')
            );
        }

        session()->flash('success', 'Budget & actual cost proyek tersimpan. PM proyek ini sudah diberi notifikasi.');
    }
}
