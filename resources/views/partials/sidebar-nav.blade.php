<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home" label="Dashboard" />

    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" icon="briefcase" label="Proyek" />

    @can('submit-report')
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Teknisi</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        <x-nav-link :href="route('teknisi.schedule')" :active="request()->routeIs('teknisi.schedule')" icon="calendar" label="Jadwal Saya" />
        <x-nav-link :href="route('teknisi.report.create')" :active="request()->routeIs('teknisi.report.create')" icon="doc-plus" label="Submit Laporan" />
        <x-nav-link :href="route('teknisi.report.index')" :active="request()->routeIs('teknisi.report.index')" icon="clipboard-list" label="Riwayat Laporan" />
    @endcan

    @can('view-kpi-team')
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">KPI</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        <x-nav-link :href="route('kpi.dashboard')" :active="request()->routeIs('kpi.*')" icon="chart-bar" label="Dashboard KPI" />
    @endcan

    @canany(['manage-purchasing', 'view-purchasing'])
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Purchasing</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        @can('manage-purchasing')
            <x-nav-link :href="route('purchasing.items')" :active="request()->routeIs('purchasing.items')" icon="cube" label="Master Item" />
        @endcan
        <x-nav-link :href="route('purchasing.vendors')" :active="request()->routeIs('purchasing.vendors*')" icon="store" label="Vendor" />
        <x-nav-link :href="route('purchasing.rfq')" :active="request()->routeIs('purchasing.rfq*')" icon="doc-text" label="Request for Quotation" :badge="$pendingApprovalCount > 0 ? $pendingApprovalCount : null" />
        <x-nav-link :href="route('purchasing.po')" :active="request()->routeIs('purchasing.po*')" icon="truck" label="Purchase Order" />
        <x-nav-link :href="route('purchasing.tracking')" :active="request()->routeIs('purchasing.tracking')" icon="package" label="Material Tracking" />
    @endcanany

    @canany(['manage-users', 'manage-projects', 'manage-kpi-settings'])
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Administrasi</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        @can('manage-users')
            <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')" icon="users" label="Kelola User" />
        @endcan
        @can('manage-projects')
            <x-nav-link :href="route('admin.locations')" :active="request()->routeIs('admin.locations')" icon="map-pin" label="Region & Unit" />
        @endcan
        @can('manage-kpi-settings')
            <x-nav-link :href="route('admin.kpi-settings')" :active="request()->routeIs('admin.kpi-settings')" icon="sliders" label="Pengaturan KPI" />
        @endcan
    @endcanany
</nav>
