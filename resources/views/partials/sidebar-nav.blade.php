<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home" label="Dashboard" />

    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" icon="briefcase" label="Proyek" />

    @can('submit-report')
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Teknisi</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        <x-nav-link :href="route('teknisi.checkin')" :active="request()->routeIs('teknisi.checkin')" icon="map-pin" label="Presensi" />
        <x-nav-link :href="route('teknisi.schedule')" :active="request()->routeIs('teknisi.schedule')" icon="calendar" label="Jadwal Saya" />
        <x-nav-link :href="route('teknisi.report.create')" :active="request()->routeIs('teknisi.report.create')" icon="doc-plus" label="Submit Laporan" />
        <x-nav-link :href="route('teknisi.report.index')" :active="request()->routeIs('teknisi.report.index')" icon="clipboard-list" label="Riwayat Laporan" />
        <x-nav-link :href="route('teknisi.safety-talk')" :active="request()->routeIs('teknisi.safety-talk')" icon="shield" label="Safety Talk" />
        @can('request-cash-advance')
            <x-nav-link :href="route('teknisi.cash-advances')" :active="request()->routeIs('teknisi.cash-advances')" icon="wallet" label="Kasbon & Expense" />
        @endcan
    @endcan

    @can('manage-pump-assets')
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Master Data</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        <x-nav-link :href="route('assets.pump-units')" :active="request()->routeIs('assets.pump-units')" icon="cube" label="Unit Pompa" />
    @endcan

    @canany(['manage-customers', 'manage-contracts', 'view-contract-value', 'approve-quotation'])
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Kontrak</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        @can('manage-customers')
            <x-nav-link :href="route('contracts.customers')" :active="request()->routeIs('contracts.customers')" icon="building" label="Customer" />
        @endcan
        <x-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*')" icon="doc-text" label="Contract" />
        @canany(['manage-invoices', 'view-contract-value'])
            <x-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" icon="wallet" label="Invoice" />
        @endcanany
    @endcanany

    @canany(['manage-prospects', 'manage-sales-orders', 'view-sales-dashboard'])
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Marketing &amp; Sales</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        @can('view-sales-dashboard')
            <x-nav-link :href="route('sales.dashboard')" :active="request()->routeIs('sales.dashboard')" icon="chart-bar" label="Dashboard Sales" />
        @endcan
        @can('manage-prospects')
            <x-nav-link :href="route('sales.prospects.index')" :active="request()->routeIs('sales.prospects.*')" icon="user-plus" label="Prospect" />
        @endcan
        @can('manage-sales-orders')
            <x-nav-link :href="route('sales.orders.index')" :active="request()->routeIs('sales.orders.*')" icon="clipboard-list" label="Sales Order" />
        @endcan
    @endcanany

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

    @can('manage-cost-control')
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Cost Control</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        <x-nav-link :href="route('cost-control.index')" :active="request()->routeIs('cost-control.*')" icon="chart-bar" label="Budget & Actual Cost" />
    @endcan

    @canany(['manage-users', 'manage-roles-permissions', 'manage-projects', 'manage-kpi-settings', 'manage-attendance-overrides', 'manage-payroll', 'manage-cash-bank', 'view-financial-reports', 'manage-general-ledger', 'manage-other-receivables', 'manage-fixed-assets', 'manage-budgets', 'approve-budgets'])
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Administrasi</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        @can('manage-users')
            <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')" icon="users" label="Kelola User" />
        @endcan
        @can('manage-roles-permissions')
            <x-nav-link :href="route('admin.role-permissions')" :active="request()->routeIs('admin.role-permissions')" icon="shield" label="Kelola Role & Permission" />
        @endcan
        @can('manage-projects')
            <x-nav-link :href="route('admin.locations')" :active="request()->routeIs('admin.locations')" icon="map-pin" label="Region & Unit" />
        @endcan
        @can('manage-attendance-overrides')
            <x-nav-link :href="route('admin.attendance-overrides')" :active="request()->routeIs('admin.attendance-overrides')" icon="map-pin" label="Override Presensi" />
        @endcan
        @can('manage-kpi-settings')
            <x-nav-link :href="route('admin.kpi-settings')" :active="request()->routeIs('admin.kpi-settings')" icon="sliders" label="Pengaturan KPI" />
        @endcan
        @can('manage-payroll')
            <x-nav-link :href="route('admin.employee-salaries')" :active="request()->routeIs('admin.employee-salaries')" icon="wallet" label="Komponen Gaji" />
            <x-nav-link :href="route('admin.payroll.index')" :active="request()->routeIs('admin.payroll.*')" icon="wallet" label="Payroll" />
        @endcan
        @can('manage-cash-bank')
            <x-nav-link :href="route('admin.cash-bank-accounts')" :active="request()->routeIs('admin.cash-bank-accounts')" icon="wallet" label="Akun Kas/Bank" />
            <x-nav-link :href="route('admin.cash-bank.index')" :active="request()->routeIs('admin.cash-bank.*')" icon="clipboard-list" label="Buku Kas/Bank" />
        @endcan
        @can('view-financial-reports')
            <x-nav-link :href="route('admin.financial-reports')" :active="request()->routeIs('admin.financial-reports')" icon="chart-bar" label="Laporan Keuangan" />
            <x-nav-link :href="route('admin.audit-trail')" :active="request()->routeIs('admin.audit-trail')" icon="history" label="Audit Trail" />
        @endcan
        @can('manage-general-ledger')
            <x-nav-link :href="route('admin.chart-of-accounts')" :active="request()->routeIs('admin.chart-of-accounts')" icon="clipboard-list" label="Chart of Account" />
        @endcan
        @canany(['manage-general-ledger', 'view-financial-reports'])
            <x-nav-link :href="route('admin.journal-entries')" :active="request()->routeIs('admin.journal-entries')" icon="doc-text" label="Jurnal Umum" />
            <x-nav-link :href="route('admin.general-ledger')" :active="request()->routeIs('admin.general-ledger')" icon="chart-bar" label="Buku Besar" />
            <x-nav-link :href="route('admin.trial-balance')" :active="request()->routeIs('admin.trial-balance')" icon="clipboard-list" label="Neraca Saldo" />
        @endcanany
        @can('manage-other-receivables')
            <x-nav-link :href="route('finance.other-receivables')" :active="request()->routeIs('finance.other-receivables')" icon="wallet" label="Piutang Lain-lain" />
        @endcan
        @can('manage-fixed-assets')
            <x-nav-link :href="route('finance.fixed-assets')" :active="request()->routeIs('finance.fixed-assets')" icon="cube" label="Asset Management" />
        @endcan
        @canany(['manage-budgets', 'approve-budgets'])
            <x-nav-link :href="route('finance.budgets')" :active="request()->routeIs('finance.budgets')" icon="chart-bar" label="Anggaran (Budgeting)" />
        @endcanany
    @endcanany

    @canany(['view-weekly-recap', 'manage-delivery-gatepass', 'manage-cash-advances'])
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Operasional</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        @can('view-weekly-recap')
            <x-nav-link :href="route('admin.weekly-recap')" :active="request()->routeIs('admin.weekly-recap')" icon="clipboard-list" label="Rekap Laporan Mingguan" />
        @endcan
        @can('manage-delivery-gatepass')
            <x-nav-link :href="route('admin.gatepasses')" :active="request()->routeIs('admin.gatepasses')" icon="truck" label="Surat Jalan & Gatepass" />
        @endcan
        @can('manage-cash-advances')
            <x-nav-link :href="route('admin.cash-advances')" :active="request()->routeIs('admin.cash-advances')" icon="wallet" label="Kasbon & Expense" />
        @endcan
    @endcanany

    @can('manage-website')
        <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Company Website</div>
        <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
        <x-nav-link :href="route('admin.website.profile')" :active="request()->routeIs('admin.website.profile')" icon="building" label="Profil Perusahaan" />
        <x-nav-link :href="route('admin.website.products')" :active="request()->routeIs('admin.website.products')" icon="cube" label="Produk" />
        <x-nav-link :href="route('admin.website.services')" :active="request()->routeIs('admin.website.services')" icon="briefcase" label="Layanan" />
        <x-nav-link :href="route('admin.website.portfolio')" :active="request()->routeIs('admin.website.portfolio')" icon="doc-text" label="Portofolio" />
        <x-nav-link :href="route('admin.website.slides')" :active="request()->routeIs('admin.website.slides')" icon="sliders" label="Slide Beranda" />
        <x-nav-link :href="route('admin.website.messages')" :active="request()->routeIs('admin.website.messages')" icon="mail" label="Pesan Masuk" />
    @endcan

    <div x-show="!collapsed" x-transition.opacity class="mt-5 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Akun</div>
    <div x-show="collapsed" class="my-3 border-t border-slate-800"></div>
    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.index')" icon="bell" label="Notifikasi" />
    <x-nav-link :href="route('profile.signature')" :active="request()->routeIs('profile.signature')" icon="edit" label="Tanda Tangan Saya" />
</nav>
