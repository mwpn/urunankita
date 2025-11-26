# ======================================================
# ARCHITECTURE SPECIFICATION — STARTERKIT SAAS MODULAR CI4
# ======================================================
project:
  name: "StarterKit SaaS Modular"
  framework: "CodeIgniter 4"
  environment: "Laragon (Apache + PHP 8.x)"
  database:
    driver: "MySQL"
    hostname: "localhost"
    username: "root"
    password: "dodolgarut"
    central_db: "central_db"
  base_url: "http://localhost/starterkit-ci4/public"

purpose:
  description: >
    Membangun StarterKit SaaS berbasis CodeIgniter 4 yang sepenuhnya modular, multi-tenant,
    dan reusable untuk berbagai jenis aplikasi (HMS, Resto, Rental, Marketplace, Crowdfunding, dll).
    Setiap tenant memiliki database sendiri dengan kontrol user & role independen.

structure:
  root:
    - app/: "Hanya untuk Config & Bootstrap"
    - Modules/: "Berisi semua module"
    - public/: "Web root"
    - writable/: "Storage & logs"
    - .cursorcontext: "Context lock untuk Cursor"
  modules:
    Core: "Sistem inti: BaseController, BaseModel, Helpers, Services, Filters, CLI"
    Auth: "Login, Registrasi Tenant, Logout"
    Tenant: "Manajemen Tenant (central control)"
    Plan: "Paket langganan SaaS"
    Subscription: "Langganan Tenant terhadap Plan"
    Billing: "Invoice & Pembayaran"
    Dashboard: "Dashboard Superadmin & Tenant"
    ExampleModule: "Contoh modul tenant (HotelManager)"

modules_detail:
  Core:
    controllers:
      - BaseController
    models:
      - BaseModel
    helpers:
      - auth_helper
      - format_helper
      - string_helper
      - date_helper
      - system_helper
    filters:
      - AuthFilter
      - TenantFilter
    services:
      - AuthService
      - TenantService
      - ResponseService
    cli_commands:
      - make:module <Name>
      - tenant:create <Slug>
      - tenant:migrate <Slug>
  Auth:
    features:
      - Superadmin login
      - Tenant login
      - Tenant registration (auto-create DB)
      - Session-based auth (JWT optional)
      - Logout
  Tenant:
    features:
      - CRUD tenants (central DB)
      - Auto-create DB tenant_<slug>
      - Integration with TenantService
  Plan:
    features:
      - List plans (Free, Pro, Enterprise)
      - Central subscription mapping
  Subscription:
    features:
      - Track tenant plan status
      - Expiration & renewal
  Billing:
    features:
      - Dummy invoice generator
      - Payment status (paid/unpaid)
      - Revenue per plan
  Dashboard:
    ui_template: "TailAdmin (dark/light mode)"
    views:
      - layouts/main.php
      - admin_dashboard.php
      - tenant_dashboard.php
  ExampleModule:
    features:
      - Room CRUD
      - Reservation CRUD
      - Tenant-specific data

database_schema:
  central_db:
    tables:
      - users
      - tenants
      - plans
      - subscriptions
      - invoices
      - roles
      - permissions
  tenant_db:
    tables:
      - users
      - roles
      - permissions
      - audit_logs
      - custom_tables: ["rooms", "reservations"]

roles:
  - name: superadmin
    scope: global
    description: "Kelola seluruh tenant & plan"
  - name: tenant_owner
    scope: per_tenant
    description: "Pemilik tenant, akses penuh di tenant"
  - name: tenant_user
    scope: per_tenant
    description: "User internal tenant"

auth_flow:
  superadmin:
    login_path: "/admin/login"
    dashboard_path: "/admin/dashboard"
  tenant_owner:
    register_path: "/register"
    dashboard_path: "/tenant/<slug>/dashboard"
  tenant_user:
    login_path: "/tenant/<slug>/login"
    dashboard_path: "/tenant/<slug>/dashboard"

multi_tenant_logic:
  central:
    manages: "users, tenants, plans, billing"
  tenant:
    isolated_db: true
    db_naming: "tenant_<slug>"
    connection_via: "TenantService"
    connection_logic: "based on URL segment or X-Tenant-Key header"
  filters:
    - TenantFilter: "Autoload DB connection based on active tenant"
    - AuthFilter: "Ensure login session exists"

cli_commands:
  - "php spark make:module <Name>": "Generate new module structure"
  - "php spark tenant:create <Slug>": "Create tenant DB & record in central"
  - "php spark tenant:migrate <Slug>": "Run tenant-specific migrations"
  - "php spark db:seed": "Seed plans & superadmin data"

frontend:
  template: "TailAdmin"
  style: "TailwindCSS"
  layout:
    - Header
    - Sidebar
    - Content
    - Footer
  features:
    - Dark/Light Mode
    - Responsive Layout
    - Modular Views per Role

coding_rules:
  - "Jangan buat file di app/Controllers, Models, Views"
  - "Semua kode harus di dalam Modules/"
  - "Gunakan namespace Modules\<ModuleName>\Controllers, dsb"
  - "Semua module extend BaseController dari Modules/Core"
  - "Gunakan Service Pattern untuk logic bisnis"
  - "Gunakan TenantService untuk koneksi DB tenant"
  - "Ikuti PSR-4 dan Clean Architecture"
  - "Gunakan CLI Command untuk pembuatan modul & tenant"
  - "Setiap module harus bisa berdiri sendiri (plug & play)"

testing:
  steps:
    - "php spark migrate"
    - "php spark db:seed"
    - "php spark tenant:create nusaindah"
    - "Periksa DB tenant_nusaindah"
    - "Login ke dashboard superadmin"
    - "Daftarkan tenant baru via register form"

deployment:
  laragon_setup:
    mysql: "aktif"
    apache: "aktif"
  steps:
    - "Pastikan base_url sudah benar"
    - "Jalankan migrasi dan seed"
    - "Buat tenant via CLI"
    - "Akses tenant melalui path /tenant/<slug>"

summary: >
  StarterKit ini adalah fondasi SaaS modular multi-tenant untuk CodeIgniter 4.
  Dapat digunakan untuk HMS, POS, CMS, Marketplace, Crowdfunding, dan sistem SaaS lainnya.
  Semua modul harus mengikuti struktur dan aturan di file ini.
