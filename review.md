# Review Lengkap Project WorkHub

> Snapshot kode aktual: 22 Agustus 2026  
> Lokasi lokal: D:/Project/lab-buat-nganu  
> Tujuan dokumen: bahan review sebelum fitur dihapus, dipertahankan, atau disembunyikan dari GitHub.

## 1. Ringkasan Eksekutif

WorkHub adalah aplikasi kolaborasi proyek dan dokumen bergaya enterprise SaaS yang sengaja dibuat sebagai lab penetration testing. Aplikasi bukan untuk data produksi atau data nyata.

Stack aktual:

| Bagian | Implementasi |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Web interaktif | Blade, Livewire 4, Volt, Flux UI |
| Styling/build | Tailwind CSS 4, Vite 6 |
| Database | MySQL 8 |
| Web authentication | Session Laravel |
| API authentication | Laravel Sanctum personal access token |
| File storage | Local private disk melalui Storage facade |
| Session/cache/queue | Database driver |
| Testing | PHPUnit/Pest-style Laravel tests |
| Deployment reference | nginx + PHP-FPM |
| Bentuk aplikasi | Web Livewire dan JSON API /api/v1 |

Catatan penting:

- Composer memakai Livewire 4, walaupun dokumen awal masih menyebut Livewire 3.
- Aplikasi memiliki dua area profile yang tumpang tindih: /profile dan /settings/profile.
- Aplikasi membawa 13 kelemahan yang sengaja dipasang untuk pentest.
- File ini menjelaskan kelemahan tersebut. Jika repo GitHub ingin black-box/minimal, review.md sebaiknya ikut di-ignore setelah selesai direview.
- Saat snapshot dibuat, review.md belum tracked dan belum masuk .gitignore.
- Tidak ada file yang dipindahkan ke docs/secret. File lokal tetap berada di lokasi normal dan kategori non-core disembunyikan melalui .gitignore.

## 2. Status Publikasi Git Saat Ini

### 2.1 Saat ini ikut repository/tracked

Bagian yang saat ini menjadi source aplikasi utama:

- app/
- bootstrap/app.php dan bootstrap/providers.php
- config/
- database/migrations/
- public entry files
- resources/
- routes/
- artisan
- composer.json dan composer.lock
- package.json dan package-lock.json
- vite.config.js
- .editorconfig, .gitattributes, dan .gitignore
- placeholder .gitignore di storage dan bootstrap/cache

Ini adalah kombinasi frontend, backend, schema database, dan metadata build yang diperlukan agar source dapat direkonstruksi.

### 2.2 Saat ini lokal dan di-ignore

| Path | Isi | Alasan disembunyikan |
|---|---|---|
| .env | Key aplikasi, koneksi DB, mail, dan konfigurasi mesin | Rahasia lokal; wajib di-ignore |
| .env.example | Template environment tanpa nilai rahasia nyata | Sengaja disembunyikan sesuai keputusan repo minimal |
| .github/ | Workflow lint dan test | Non-core/repository automation |
| deploy/ | Contoh konfigurasi nginx | Infrastruktur internal dan mengungkap konfigurasi lab |
| docs/ | PRD, UI, schema, tech spec, vulnerability map, agent guide | Sangat mengungkap desain lab |
| README.md | Petunjuk setup dan akun demo | Mengungkap kredensial demo dan konteks lab |
| tests/ | Test auth, halaman, upload, komentar, query, share-link | Non-runtime dan dapat mengungkap behavior |
| phpunit.xml | Konfigurasi test | Non-runtime |
| database/factories/ | Pembuat data test | Non-runtime |
| database/seeders/ | Akun demo, skenario private/pending/expired/revoked | Mengungkap data dan skenario pentest |
| vendor/ | Dependency Composer hasil install | Generated, besar |
| node_modules/ | Dependency npm hasil install | Generated, besar |
| public/build/ | Hasil npm run build | Generated |
| public/storage | Symlink storage public | Runtime |
| storage/app/private/documents/ | Dokumen yang di-upload | Data pengguna lokal |
| storage/app/private/livewire-tmp/ | Upload sementara | Runtime dan data lokal |
| storage/framework/ | View/session/cache/testing runtime | Generated |
| storage/logs/ | Log aplikasi | Dapat mengandung data request/error |
| bootstrap/cache/packages.php | Cache discovery package | Generated |
| bootstrap/cache/services.php | Cache service container | Generated |
| .phpunit.result.cache | Cache hasil test | Generated |

Konsekuensi menyembunyikan .env.example:

- Repo lebih minim dan tidak memperlihatkan nama variabel konfigurasi.
- Orang yang clone tidak mendapat template setup.
- Workflow CI yang disembunyikan memakai cp .env.example .env; bila workflow kelak dipublikasikan lagi, .env.example juga perlu tersedia atau workflow diubah.
- Secara praktik umum, .env.example yang sudah disanitasi aman dipublikasikan. Namun kondisi sekarang mengikuti pilihan minimal/private.

### 2.3 Perubahan lokal yang belum menjadi commit pada snapshot

Terdapat perubahan performa/UX pada 17 file tracked, mencakup:

- query dashboard, project, document, admin, dan sidebar;
- navigasi wire:navigate.hover;
- upload otomatis dan progress;
- comment tanpa reload preview;
- pencegahan collision share-link;
- konfigurasi temporary upload Livewire.

review.md sendiri berstatus file baru/untracked. Menulis dokumen ini tidak otomatis menambahkannya ke commit.

## 3. Ukuran Folder Lokal

| Folder | Jumlah file lokal | Perkiraan ukuran |
|---|---:|---:|
| app | 50 | 0,06 MB |
| bootstrap | 5 | 0,02 MB |
| config | 11 | 0,04 MB |
| database | 24 | 0,03 MB |
| deploy | 1 | <0,01 MB |
| docs | 5 | 0,04 MB |
| node_modules | 5.398 | 45,11 MB |
| public | 7 | 0,20 MB |
| resources | 58 | 0,10 MB |
| routes | 4 | 0,01 MB |
| storage | 833 | 9,70 MB |
| tests | 13 | 0,02 MB |
| vendor | 9.874 | 84,06 MB |

vendor, node_modules, storage runtime, dan public/build tidak perlu dipush.

## 4. Struktur Project Lengkap

Bagian ini mencantumkan source first-party dan folder lokal penting. Isi ribuan file dependency di vendor/node_modules tidak dijabarkan satu per satu karena merupakan hasil package manager.

### 4.1 Root

~~~
D:/Project/lab-buat-nganu/
├── .env                         konfigurasi aktif lokal; rahasia
├── .env.example                 template konfigurasi; saat ini ignored
├── .editorconfig                aturan editor
├── .gitattributes               atribut Git
├── .gitignore                   aturan file yang tidak dipublikasikan
├── .github/                     workflow CI; ignored
├── app/                         seluruh backend/domain/Livewire
├── artisan                      CLI Laravel
├── bootstrap/                   bootstrap framework dan provider
├── composer.json                dependency dan script PHP
├── composer.lock                versi dependency PHP yang dikunci
├── config/                      konfigurasi aplikasi
├── database/                    migration, factory, seeder
├── deploy/                      konfigurasi nginx; ignored
├── docs/                        dokumen internal lab; ignored
├── node_modules/                dependency npm; ignored
├── package.json                 dependency dan script frontend
├── package-lock.json            versi dependency npm yang dikunci
├── phpunit.xml                  konfigurasi test; ignored
├── public/                      document root web
├── README.md                    setup dan akun demo; ignored
├── resources/                   CSS, JS, Blade, Livewire views
├── review.md                    dokumen ini; belum ignored
├── routes/                      route web, auth, API, console
├── storage/                     file runtime dan upload lokal
├── tests/                       automated tests; ignored
├── vendor/                      dependency Composer; ignored
└── vite.config.js               konfigurasi build asset
~~~

### 4.2 app/ — backend dan domain

~~~
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/
│   │   │   ├── AdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── CommentController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ProjectMemberController.php
│   │   │   ├── ShareController.php
│   │   │   └── UtilityController.php
│   │   ├── Auth/
│   │   │   └── VerifyEmailController.php
│   │   ├── Controller.php
│   │   ├── DocumentController.php
│   │   └── PublicShareController.php
│   └── Middleware/
│       ├── EnsureUserIsActive.php
│       └── EnsureUserIsAdmin.php
├── Livewire/
│   ├── Actions/
│   │   └── Logout.php
│   ├── Admin/
│   │   ├── AdminDashboard.php
│   │   ├── AuditLogTable.php
│   │   ├── DocumentTable.php
│   │   ├── ProjectTable.php
│   │   ├── SystemSettingsForm.php
│   │   └── UserTable.php
│   ├── Dashboard/
│   │   └── Dashboard.php
│   ├── Documents/
│   │   └── DocumentDetail.php
│   ├── Notifications/
│   │   └── NotificationList.php
│   ├── Profile/
│   │   └── ProfileForm.php
│   ├── Projects/
│   │   ├── ProjectDetail.php
│   │   ├── ProjectForm.php
│   │   └── ProjectList.php
│   └── Search/
│       └── GlobalSearch.php
├── Models/
│   ├── Activity.php
│   ├── AuditLog.php
│   ├── Comment.php
│   ├── Document.php
│   ├── DocumentShare.php
│   ├── DocumentShareLink.php
│   ├── Notification.php
│   ├── Project.php
│   ├── ProjectMember.php
│   ├── SystemSetting.php
│   └── User.php
├── Policies/
│   ├── CommentPolicy.php
│   ├── DocumentPolicy.php
│   ├── ProjectMemberPolicy.php
│   └── ProjectPolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── VoltServiceProvider.php
└── Services/
    ├── DocumentService.php
    ├── MarkdownLiteService.php
    ├── SearchService.php
    └── ShareLinkService.php
~~~

Fungsi tiap kelompok:

- Controllers/Api/V1: JSON API dan validasi request API.
- Controllers web: stream preview/download dan public share.
- Middleware: memblokir user suspended dan membatasi admin.
- Livewire: state, authorization, validasi, action, dan query untuk halaman web.
- Models: mapping tabel, relasi, cast, soft delete, dan helper.
- Policies: permission system role dan project role.
- Services: logic yang dipakai bersama web/API.
- Providers: boot service aplikasi dan lokasi komponen Volt.

Tidak terdapat app/Http/Requests. Walaupun tech spec awal menyebut Form Request, implementasi aktual memakai validasi inline di Livewire dan controller.

### 4.3 database/

~~~
database/
├── .gitignore
├── factories/                           ignored
│   ├── DocumentFactory.php
│   ├── ProjectFactory.php
│   └── UserFactory.php
├── migrations/                          tracked/core
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2026_08_21_000100_create_projects_table.php
│   ├── 2026_08_21_000200_create_project_members_table.php
│   ├── 2026_08_21_000300_create_documents_table.php
│   ├── 2026_08_21_000400_create_document_shares_table.php
│   ├── 2026_08_21_000500_create_document_share_links_table.php
│   ├── 2026_08_21_000600_create_comments_table.php
│   ├── 2026_08_21_000700_create_notifications_table.php
│   ├── 2026_08_21_000800_create_activities_table.php
│   ├── 2026_08_21_000900_create_audit_logs_table.php
│   ├── 2026_08_21_001000_create_system_settings_table.php
│   └── 2026_08_21_001100_create_personal_access_tokens_table.php
└── seeders/                             ignored
    ├── CommentSeeder.php
    ├── DatabaseSeeder.php
    ├── DocumentSeeder.php
    ├── ProjectSeeder.php
    ├── SystemSettingsSeeder.php
    └── UserSeeder.php
~~~

Migration adalah core karena menentukan schema aplikasi. Seeder/factory bukan runtime, tetapi dibutuhkan bila ingin clone baru langsung memiliki akun dan skenario demo.

### 4.4 resources/

~~~
resources/
├── css/
│   └── app.css
├── js/
│   └── app.js                           saat ini kosong
└── views/
    ├── components/
    │   ├── action-message.blade.php
    │   ├── app-logo.blade.php
    │   ├── app-logo-icon.blade.php
    │   ├── auth-header.blade.php
    │   ├── auth-session-status.blade.php
    │   ├── avatar.blade.php
    │   ├── empty-state.blade.php
    │   ├── page-header.blade.php
    │   ├── placeholder-pattern.blade.php
    │   ├── status-badge.blade.php
    │   ├── text-link.blade.php
    │   ├── layouts/
    │   │   ├── app.blade.php
    │   │   ├── app/header.blade.php
    │   │   ├── app/sidebar.blade.php
    │   │   ├── auth.blade.php
    │   │   ├── auth/card.blade.php
    │   │   ├── auth/simple.blade.php
    │   │   └── auth/split.blade.php
    │   └── settings/
    │       └── layout.blade.php
    ├── errors/
    │   ├── 403.blade.php
    │   └── 404.blade.php
    ├── flux/
    │   ├── icon/
    │   │   ├── book-open-text.blade.php
    │   │   ├── chevrons-up-down.blade.php
    │   │   ├── folder-git-2.blade.php
    │   │   └── layout-grid.blade.php
    │   └── navlist/
    │       └── group.blade.php
    ├── livewire/
    │   ├── admin/
    │   │   ├── admin-dashboard.blade.php
    │   │   ├── audit-log-table.blade.php
    │   │   ├── document-table.blade.php
    │   │   ├── project-table.blade.php
    │   │   ├── system-settings-form.blade.php
    │   │   └── user-table.blade.php
    │   ├── auth/
    │   │   ├── confirm-password.blade.php
    │   │   ├── forgot-password.blade.php
    │   │   ├── login.blade.php
    │   │   ├── register.blade.php
    │   │   ├── reset-password.blade.php
    │   │   └── verify-email.blade.php
    │   ├── dashboard/
    │   │   └── dashboard.blade.php
    │   ├── documents/
    │   │   └── document-detail.blade.php
    │   ├── notifications/
    │   │   └── notification-list.blade.php
    │   ├── profile/
    │   │   └── profile-form.blade.php
    │   ├── projects/
    │   │   ├── project-detail.blade.php
    │   │   ├── project-form.blade.php
    │   │   └── project-list.blade.php
    │   ├── search/
    │   │   └── global-search.blade.php
    │   └── settings/
    │       ├── appearance.blade.php
    │       ├── delete-user-form.blade.php
    │       ├── password.blade.php
    │       └── profile.blade.php
    ├── partials/
    │   ├── head.blade.php
    │   └── settings-heading.blade.php
    ├── share/
    │   ├── invalid.blade.php
    │   └── show.blade.php
    ├── dashboard.blade.php
    └── welcome.blade.php
~~~

app.css memuat Tailwind dan Flux, mendefinisikan theme zinc/dark mode, dan styling form focus. app.js kosong; Livewire/Flux menyediakan JavaScript melalui package/framework.

### 4.5 config/

~~~
config/
├── app.php             nama, environment, locale, timezone, key
├── auth.php            guard, provider user, password broker
├── cache.php           cache driver dan prefix
├── database.php        koneksi MySQL/SQLite/Redis
├── filesystems.php     local private dan public disk
├── livewire.php        layout dan temporary upload sampai 100 MB
├── logging.php         channel log
├── mail.php            mail transport
├── queue.php           queue driver
├── services.php        integrasi service eksternal
└── session.php         session driver/cookie/lifetime
~~~

Konfigurasi aktual lokal yang relevan:

- database: MySQL workhub di 127.0.0.1;
- session: database;
- cache: database;
- queue: database;
- filesystem default: local/private;
- mail lokal: log;
- upload aplikasi default: 20 MB dari system_settings;
- transport temporary Livewire: maksimal 100 MB agar dapat mengikuti range setting admin 1–100 MB.

Nilai rahasia aktual tetap hanya di .env dan tidak dicantumkan di dokumen ini.

### 4.6 routes/

~~~
routes/
├── web.php             halaman publik, user, admin, settings
├── auth.php            login/register/reset/verify/logout
├── api.php             seluruh /api/v1
└── console.php         command/route console
~~~

### 4.7 public/

~~~
public/
├── .htaccess           Apache rewrite
├── favicon.ico
├── index.php           front controller Laravel
├── robots.txt
├── build/              hasil Vite; ignored
└── storage             symlink ke storage/app/public; ignored
~~~

Web server harus diarahkan ke public/, bukan root repository.

### 4.8 bootstrap/

~~~
bootstrap/
├── app.php             routing, health endpoint, alias middleware
├── providers.php       daftar provider aplikasi
└── cache/
    ├── .gitignore
    ├── packages.php    generated/ignored
    └── services.php    generated/ignored
~~~

### 4.9 Folder internal/non-core

~~~
docs/
├── AGENTS.md           aturan implementasi lab
├── DB_SCHEMA.md        target schema dan seed
├── PRD.md              requirement produk
├── TECH_SPEC.md        arsitektur dan vulnerability map
└── UI_SPEC.md          desain UI dan sitemap

deploy/
└── nginx/
    └── workhub.conf    server block nginx yang sengaja minim hardening

.github/
└── workflows/
    ├── lint.yml        install dependency dan Laravel Pint
    └── tests.yml       build asset dan PHPUnit di Ubuntu

tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── PasswordResetTest.php
│   │   └── RegistrationTest.php
│   ├── Settings/
│   │   ├── PasswordUpdateTest.php
│   │   └── ProfileUpdateTest.php
│   ├── DashboardTest.php
│   ├── ExampleTest.php
│   └── WorkHubPageTest.php
├── Unit/
│   └── ExampleTest.php
├── Pest.php
└── TestCase.php
~~~

## 5. Arsitektur Aplikasi

### 5.1 Alur request web

~~~mermaid
flowchart LR
    Browser --> WebRoute[routes/web.php atau auth.php]
    WebRoute --> Middleware[auth + active + optional admin]
    Middleware --> Livewire[Livewire/Volt component]
    Middleware --> WebController[Document/PublicShare controller]
    Livewire --> Policy[Laravel Policies]
    WebController --> Policy
    Policy --> Model[Eloquent Models]
    Livewire --> Service[Shared Services]
    WebController --> Storage[Private File Storage]
    Service --> Model
    Service --> Storage
    Model --> MySQL[(MySQL)]
    Livewire --> Blade[Blade + Flux UI]
    Blade --> Browser
~~~

### 5.2 Alur request API

~~~mermaid
flowchart LR
    Client[Burp/Postman/API Client] --> API[/api/v1]
    API --> Sanctum[auth:sanctum]
    Sanctum --> Active[active middleware]
    Active --> APIController[Api V1 Controller]
    APIController --> Policy[Policies]
    APIController --> Service[Shared Services]
    Policy --> Model[Eloquent]
    Service --> Model
    Model --> MySQL[(MySQL)]
    APIController --> JSON[JSON response]
~~~

### 5.3 Boundary penyimpanan

- Dokumen: storage/app/private/documents/{project_id}/.
- Upload sementara: storage/app/private/livewire-tmp/.
- Avatar upload/import: storage/app/public/avatars/.
- Preview dan download dokumen privat selalu melalui controller Laravel.
- public/storage hanya untuk asset public seperti avatar.
- File temporary sukses dihapus setelah dokumen selesai disimpan.
- Soft-delete mempertahankan file fisik agar record dapat direstore; aksi admin Delete forever menghapus record dan file fisiknya.

## 6. Role dan Permission

### 6.1 System role

- admin: akses seluruh resource dan panel admin.
- user: akses mengikuti membership/share.

### 6.2 Project role

- owner: kelola proyek, member, dokumen, archive, transfer ownership, delete.
- editor: upload/ubah/hapus/share dokumen dan komentar; tidak mengelola member.
- viewer: baca dokumen yang terlihat dan komentar sesuai policy; tidak mengelola dokumen.

### 6.3 Matriks implementasi normal

| Aksi | Admin | Owner | Editor | Viewer |
|---|---:|---:|---:|---:|
| Lihat project aktif | Ya | Ya | Ya | Ya |
| Buat project | Ya | Ya | Ya | Ya |
| Edit/archive/delete project | Ya | Ya | Tidak | Tidak |
| Kelola member | Ya | Ya | Tidak | Tidak |
| Transfer ownership | Ya | Ya | Tidak | Tidak |
| Upload document | Ya | Ya | Ya | Tidak |
| Ubah/hapus/share document | Ya | Ya | Ya | Tidak |
| Editor share eksplisit mengubah document | Ya | N/A | Ya | Jika share=editor |
| Lihat project-visible document | Ya | Ya | Ya | Ya |
| Lihat private document dari daftar | Ya | owner/uploader/shared | uploader/shared | shared |
| Komentar pada document terlihat dan project aktif | Ya | Ya | Ya | Ya |
| Edit comment | Ya | Milik sendiri | Milik sendiri | Milik sendiri |
| Hapus comment | Ya | Milik sendiri | Milik sendiri | Milik sendiri |
| Panel admin | Ya | Tidak | Tidak | Tidak |

Pengecualian yang sengaja rentan dijelaskan pada bagian Vulnerability Map.

## 7. Fitur Aktual

### 7.1 Authentication dan account

Status: tersedia.

- Register user baru.
- Login dengan remember-me.
- Logout dan invalidasi session.
- Blok login user suspended.
- Middleware mengeluarkan user suspended yang masih memiliki session.
- Forgot password.
- Reset password.
- Confirm password.
- Halaman verify email dan kirim ulang link.
- Ubah password.
- Ubah nama/email.
- Hapus akun sendiri.
- API login/register/logout dengan Sanctum token.

Catatan:

- User model tidak mengimplementasikan MustVerifyEmail.
- Route aplikasi tidak memakai middleware verified.
- Artinya email verification UI tersedia tetapi verifikasi tidak diwajibkan untuk memakai aplikasi.
- Login web dan API tidak memakai rate limiter.
- Reset password sengaja tidak mengecek umur token.

### 7.2 Dashboard

Status: tersedia.

User biasa melihat:

- jumlah project aktif yang diikuti;
- pending invite;
- jumlah dokumen di project;
- unread notification;
- lima project terbaru;
- enam activity terbaru di project miliknya.

Admin melihat:

- jumlah user;
- jumlah project termasuk soft-deleted;
- jumlah document termasuk soft-deleted;
- jumlah user suspended;
- project dan activity terbaru.

### 7.3 Project

Status: tersedia.

- Buat project.
- Otomatis membuat membership owner aktif.
- List project dengan filter status.
- Admin melihat semua project.
- User melihat project dengan membership aktif.
- Detail memakai tab Documents, Members, Activity, Settings.
- Ubah nama dan deskripsi.
- Archive/restore.
- Soft-delete project.
- Activity project.created disimpan.

### 7.4 Project membership

Status: tersedia.

- Invite berdasarkan email user yang sudah ada.
- Role undangan editor atau viewer.
- Membership awal pending.
- Notification project.invited dibuat.
- Accept invite dari halaman notification.
- Accept invite lewat API.
- Ubah role.
- Hapus member non-owner.
- Transfer ownership dalam transaction.
- Owner lama menjadi editor.
- Owner baru harus membership aktif.

Catatan:

- Tidak ada email invitation eksternal; undangan hanya untuk akun existing.
- Livewire changeRole aman membatasi editor/viewer.
- API updateRole sengaja memakai mass assignment full body.
- Event activity/notification untuk setiap perubahan role/remove/transfer belum konsisten dibuat.

### 7.5 Documents

Status: tersedia.

- List dokumen yang terlihat.
- Upload document ke private local storage.
- Upload otomatis dimulai dan disimpan setelah file dipilih.
- Progress upload dan saving tampil.
- Batas upload mengikuti system_settings, default 20 MB.
- Nama tampilan opsional.
- Visibility project/private.
- Preview inline untuk image/PDF.
- Download file lain.
- Metadata owner, tipe, ukuran, dan visibility.
- Rename/edit description/edit visibility.
- Soft-delete document.
- Activity document.uploaded dibuat.
- Preview memakai wire:ignore agar komentar tidak me-reload PDF/image.

Catatan:

- File fisik dipertahankan ketika soft-delete dan dihapus oleh aksi admin Delete forever.
- Nama path berasal dari original filename yang di-slug + timestamp.
- MIME tersimpan berasal dari client.
- API upload dan web upload memakai DocumentService yang sama.
- Upload archived project sengaja hanya diblok di UI, tidak di handler server.

### 7.6 Document sharing ke user

Status: tersedia.

- Share berdasarkan email existing.
- Permission viewer/editor.
- Update share bila penerima sama.
- Revoke share.
- Editor share dapat mengubah dokumen selama project aktif.
- Private document muncul untuk uploader atau user yang menerima share.

Catatan:

- Live action share tidak membuat Notification document.shared; seed data memilikinya, tetapi runtime action belum lengkap.
- Tidak ada UI khusus penerimaan; share langsung aktif.

### 7.7 Public share-link

Status: tersedia.

- Generate link dengan expiry opsional.
- Token dicatat per document.
- Revoke link.
- Public view tanpa login.
- Public download tanpa login.
- Access count dan last accessed diperbarui ketika halaman show atau direct download dibuka.
- Link hanya menunjukkan satu document.

Catatan:

- Token sengaja predictable.
- Expiry sengaja tidak divalidasi di show maupun download.
- Direct download mencatat access_count dan last_accessed_at setelah file dipastikan tersedia.
- Revoked link ditolak.

### 7.8 Comments

Status: tersedia.

- Tambah comment.
- Edit comment milik sendiri.
- Hapus comment milik sendiri; admin dapat menghapus semua.
- Maksimum 5.000 karakter.
- Markdown-lite bold, italic, dan link.
- Comment ditampilkan tanpa reload halaman/preview.

Catatan:

- Renderer sengaja tidak melakukan sanitasi HTML.
- body_rendered dirender unescaped.
- Runtime tambah comment belum membuat notification comment.created maupun Activity.
- Comment pada archived project diblok untuk user biasa.

### 7.9 Search

Status: tersedia.

- Global search melalui query string q.
- Minimal dua karakter di web.
- Maksimum 200 karakter di API.
- Hasil dikelompokkan menjadi Projects, Documents, Users.
- Project dan document dibatasi berdasarkan akses user.
- Masing-masing grup maksimal delapan hasil.

Catatan:

- Pencarian user sengaja memakai raw SQL interpolation dan rentan SQL injection.
- User results bersifat global, bukan hanya anggota project yang sama.

### 7.10 Notifications

Status: tersedia.

- Polling setiap 30 detik pada halaman notification.
- List notification paginated.
- Unread state.
- Mark one as read.
- Mark all as read.
- Accept project invitation.

Catatan:

- Badge sidebar menghitung unread saat layout dirender.
- Tidak semua runtime action membuat notification yang disebut di PRD.
- Belum ada notification runtime untuk new comment dan role changed.

### 7.11 Profile dan session

Status: tersedia, tetapi ada duplikasi.

Area /profile:

- upload avatar maksimal 2 MB;
- import avatar dari URL;
- lihat session aktif;
- revoke session lain;
- lihat delapan activity user.

Area /settings/profile:

- ubah nama/email;
- resend verification;
- hapus akun.

Area /settings/password:

- ubah password dengan current password.

Area /settings/appearance:

- light/dark/system appearance.

Catatan:

- Avatar import memiliki SSRF weakness yang disengaja.
- Ada dua pengalaman profile yang sebaiknya digabung bila ingin menyederhanakan aplikasi.

### 7.12 Admin

Status: tersedia.

- Dashboard count dan audit event terbaru.
- User table: search, filter status, suspend/activate, admin/user role.
- Project table: search, filter status, menampilkan active/archived/deleted, soft-delete, restore, dan delete forever.
- Document table: search, filter visibility, menampilkan relasi project yang soft-deleted secara aman, soft-delete, restore, dan delete forever.
- Audit log table: search action dan pagination.
- System settings:
  - avatar_allowed_domains;
  - max_upload_size_mb antara 1–100.
- JSON admin endpoints untuk list/update yang sama.

Catatan:

- Audit logging belum menyelimuti seluruh aksi sensitif.
- UI toggle role tidak membuat AuditLog.
- UI/API update setting tidak membuat AuditLog.
- Admin delete, restore, dan delete forever project/document membuat AuditLog di Livewire UI.
- Admin API belum menyediakan delete project/document khusus karena admin dapat memakai resource endpoint biasa melalui policy before.

### 7.13 API

Status: tersedia.

- Auth token Sanctum.
- CRUD projects.
- Membership list/invite/update/delete/accept.
- Document list/upload/show/update/delete/download.
- User share dan public share-link management.
- Comment list/create/update/delete.
- Search.
- Notification list/read.
- Admin dashboard/users/projects/documents/audit/settings.

API penting untuk lab karena dapat dites langsung melalui Burp/Postman tanpa UI.

## 8. Workflow Utama

### 8.1 Register dan login

1. User mengisi register.
2. Data divalidasi dan password di-hash.
3. User dibuat dengan role user/status active.
4. User langsung login dan diarahkan ke dashboard.
5. Login berikutnya memvalidasi email, password, dan status active.
6. Session ID diregenerasi.
7. Middleware active terus memeriksa status pada route private.

### 8.2 Membuat project

1. User membuka /projects/create.
2. Policy Project.create memeriksa user aktif.
3. Name/description divalidasi.
4. Project dibuat dengan owner_id user.
5. Membership owner + active dibuat.
6. Activity project.created dibuat.
7. Livewire navigate menuju project detail tanpa full reload.

### 8.3 Invite dan accept member

1. Owner/admin membuka tab Members.
2. Memasukkan email existing dan role editor/viewer.
3. Membership dibuat/di-update menjadi pending.
4. Notification project.invited dibuat.
5. Pending user membuka /notifications.
6. User menekan Accept.
7. status menjadi active dan joined_at diisi.
8. User baru dapat membuka project melalui jalur normal.

### 8.4 Upload document

1. Owner/editor mengatur display name dan visibility.
2. User memilih file.
3. Browser mengirim file ke endpoint temporary upload Livewire.
4. Progress ditampilkan.
5. Setelah temporary upload selesai, UI otomatis memanggil action upload.
6. Policy Document.create memeriksa role.
7. Batas file divaca dari system_settings.
8. DocumentService menyimpan ke documents/{project_id}/.
9. Record documents dan activities dibuat.
10. File temporary dihapus.
11. Daftar document di-render ulang tanpa full page reload.

### 8.5 View, edit, dan download document

1. User membuka route scoped project/document.
2. DocumentPolicy.view dijalankan.
3. Relasi owner, project, share, link, comment dimuat.
4. PDF/image di-preview inline; file lain menawarkan download.
5. Owner/editor/editor-share dapat mengubah metadata atau soft-delete.
6. Download dan preview selalu melalui controller Laravel.

### 8.6 Share document

User share:

1. Pengelola document memasukkan email dan permission.
2. document_shares dibuat/di-update.
3. User penerima langsung mendapat akses sesuai permission.
4. Pengelola dapat revoke.

Public link:

1. Pengelola memilih optional expiry.
2. ShareLinkService membuat token predictable.
3. URL /share/{token} dapat dibuka tanpa login.
4. Revoked link ditolak.
5. Expired link sengaja tetap aktif untuk exercise.

### 8.7 Comment

1. User yang dapat melihat document mengirim text.
2. Policy memastikan project aktif.
3. MarkdownLiteService mengubah bold/italic/link.
4. body_raw dan body_rendered disimpan.
5. Thread di-render ulang tanpa reload preview.
6. Pemilik comment dapat edit/delete; admin override semua.

### 8.8 Notification

1. Event tertentu membuat row notifications.
2. Halaman melakukan polling 30 detik.
3. User membaca/mark all.
4. Invitation dapat diterima dari notification.
5. read_at menandai status baca.

### 8.9 Admin

1. Middleware auth, active, dan admin dijalankan.
2. Admin melihat global data.
3. Admin dapat suspend user atau mengubah system role.
4. Admin dapat soft-delete project/document.
5. Aksi tertentu menulis AuditLog.
6. Setting upload/avatar disimpan di system_settings.

### 8.10 API token

1. Client POST /api/v1/login atau /register.
2. Server mengembalikan Sanctum plain-text token sekali.
3. Client mengirim Authorization: Bearer.
4. auth:sanctum dan active middleware memeriksa request.
5. Logout menghapus current access token.

## 9. Route Web Aktual

### 9.1 Public/auth

| Method | Path | Fungsi |
|---|---|---|
| GET | / | Landing page |
| GET | /login | Login |
| GET | /register | Register |
| GET | /forgot-password | Request reset |
| GET | /reset-password/{token} | Reset password |
| GET | /share/{token} | Public document |
| GET | /share/{token}/download | Public download |
| POST | /logout | Logout |
| GET | /verify-email | Verification notice |
| GET | /verify-email/{id}/{hash} | Verify signed email |
| GET | /confirm-password | Confirm password |
| GET | /up | Laravel health endpoint |

### 9.2 Authenticated user

| Method | Path | Fungsi |
|---|---|---|
| GET | /dashboard | Dashboard |
| GET | /projects | Project list |
| GET | /projects/create | Create project |
| GET | /projects/{project} | Project detail/tabs/actions |
| GET | /projects/{project}/documents/{document} | Document detail |
| GET | /documents/{document}/download | Private download |
| GET | /documents/{document}/preview | Inline preview |
| GET | /search?q= | Global search |
| GET | /notifications | Notification list/accept |
| GET | /profile | Avatar/session/activity profile |
| ANY | /settings | Redirect ke profile settings |
| GET | /settings/profile | Name/email/delete account |
| GET | /settings/password | Change password |
| GET | /settings/appearance | Theme |

Livewire action seperti create/upload/comment/share berjalan melalui endpoint internal Livewire, sehingga tidak muncul sebagai route bisnis terpisah.

### 9.3 Admin web

| Method | Path | Fungsi |
|---|---|---|
| GET | /admin | Admin dashboard |
| GET | /admin/users | User management |
| GET | /admin/projects | Global projects |
| GET | /admin/documents | Global documents |
| GET | /admin/audit-logs | Audit viewer |
| GET | /admin/settings | System settings |

## 10. Route API Aktual

Prefix semua endpoint: /api/v1.

### 10.1 Auth

| Method | Endpoint |
|---|---|
| POST | /login |
| POST | /register |
| POST | /logout |

### 10.2 Projects

| Method | Endpoint |
|---|---|
| GET | /projects |
| POST | /projects |
| GET | /projects/{project} |
| PUT/PATCH | /projects/{project} |
| DELETE | /projects/{project} |

### 10.3 Members

| Method | Endpoint |
|---|---|
| GET | /projects/{project}/members |
| POST | /projects/{project}/members |
| PATCH | /projects/{project}/members/{user} |
| DELETE | /projects/{project}/members/{user} |
| POST | /projects/{project}/members/accept |

### 10.4 Documents

| Method | Endpoint |
|---|---|
| GET | /projects/{project}/documents |
| POST | /projects/{project}/documents |
| GET | /documents/{document} |
| PATCH | /documents/{document} |
| DELETE | /documents/{document} |
| GET | /documents/{document}/download |

### 10.5 Shares dan links

| Method | Endpoint |
|---|---|
| POST | /documents/{document}/shares |
| DELETE | /documents/{document}/shares |
| POST | /documents/{document}/share-links |
| DELETE | /share-links/{shareLink} |

### 10.6 Comments

| Method | Endpoint |
|---|---|
| GET | /documents/{document}/comments |
| POST | /documents/{document}/comments |
| PATCH | /comments/{comment} |
| DELETE | /comments/{comment} |

### 10.7 Utility

| Method | Endpoint |
|---|---|
| GET | /search?q= |
| GET | /notifications |
| POST | /notifications/{id}/read |

### 10.8 Admin API

| Method | Endpoint |
|---|---|
| GET | /admin |
| GET | /admin/users |
| PATCH | /admin/users/{user} |
| GET | /admin/projects |
| GET | /admin/documents |
| GET | /admin/audit-logs |
| GET | /admin/settings |
| PATCH | /admin/settings |

## 11. Desain Database

### 11.1 ERD

~~~mermaid
erDiagram
    USERS ||--o{ PROJECTS : owns
    USERS ||--o{ PROJECT_MEMBERS : belongs
    PROJECTS ||--o{ PROJECT_MEMBERS : has
    USERS ||--o{ DOCUMENTS : uploads
    PROJECTS ||--o{ DOCUMENTS : contains
    DOCUMENTS ||--o{ DOCUMENT_SHARES : grants
    USERS ||--o{ DOCUMENT_SHARES : receives
    DOCUMENTS ||--o{ DOCUMENT_SHARE_LINKS : exposes
    USERS ||--o{ DOCUMENT_SHARE_LINKS : creates
    DOCUMENTS ||--o{ COMMENTS : has
    USERS ||--o{ COMMENTS : writes
    USERS ||--o{ NOTIFICATIONS : receives
    PROJECTS ||--o{ ACTIVITIES : logs
    DOCUMENTS ||--o{ ACTIVITIES : logs
    USERS ||--o{ ACTIVITIES : acts
    USERS ||--o{ AUDIT_LOGS : acts
    USERS ||--o{ SYSTEM_SETTINGS : updates
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : tokenable
~~~

### 11.2 Tabel domain

#### users

- id bigint PK
- name
- email unique
- email_verified_at nullable
- password hashed
- avatar_url nullable
- system_role: admin/user
- status: active/suspended
- remember_token
- timestamps

Relasi: project owner, memberships, documents, shares, comments, notifications, API tokens.

#### projects

- id
- name
- description nullable
- owner_id FK users, restrict delete
- status: active/archived, indexed
- timestamps
- deleted_at soft delete

Relasi: owner, memberships, documents, activities.

#### project_members

- id
- project_id FK cascade
- user_id FK cascade
- role: owner/editor/viewer
- status: active/pending
- invited_by nullable, null on inviter delete
- invited_at
- joined_at
- timestamps
- unique project_id + user_id
- indexes project_id + status dan user_id

#### documents

- id
- project_id FK cascade
- owner_id FK users, restrict delete
- name
- description nullable
- file_path maksimal 2048
- original_filename
- mime_type maksimal 150
- size_bytes unsigned bigint
- visibility: project/private
- timestamps
- deleted_at soft delete
- index project_id + visibility
- index owner_id

#### document_shares

- id
- document_id FK cascade
- user_id penerima FK cascade
- permission: viewer/editor
- shared_by FK users, restrict delete
- timestamps
- unique document_id + user_id
- index user_id

#### document_share_links

- id
- document_id FK cascade
- token maksimal 64, unique
- created_by FK users, restrict delete
- expires_at nullable
- revoked_at nullable
- access_count default 0
- last_accessed_at nullable
- timestamps
- index document_id

#### comments

- id
- document_id FK cascade
- user_id FK cascade
- body_raw
- body_rendered
- timestamps
- deleted_at soft delete
- index document_id

#### notifications

- id
- user_id FK cascade
- type
- data JSON
- read_at nullable
- created_at
- index user_id + read_at
- tidak memiliki updated_at

#### activities

- id
- project_id nullable FK cascade
- document_id nullable FK cascade
- user_id FK restrict delete
- action
- description maksimal 500
- meta JSON nullable
- created_at
- index project_id dan document_id
- tidak memiliki updated_at

#### audit_logs

- id
- actor_id nullable FK users, null on delete
- action
- target_type
- target_id nullable
- meta JSON nullable
- ip_address nullable
- user_agent nullable
- created_at
- composite index target_type + target_id
- target_type/target_id adalah referensi polymorphic manual, bukan FK.

#### system_settings

- id
- key unique
- value text
- updated_by nullable FK users, null on delete
- updated_at
- tidak memiliki created_at

Setting aktif:

- avatar_allowed_domains
- max_upload_size_mb

### 11.3 Tabel framework

- password_reset_tokens
- sessions
- cache
- cache_locks
- jobs
- job_batches
- failed_jobs
- personal_access_tokens
- migrations

### 11.4 Soft delete dan efeknya

Soft delete dipakai oleh:

- projects
- documents
- comments

Dampak:

- Data masih ada di database.
- Query normal tidak menampilkan data tersebut.
- Admin count dapat memakai withTrashed.
- File fisik dokumen dipertahankan selama soft-delete dan dihapus saat Delete forever melalui admin table.
- Soft-delete project tidak menjalankan database cascade ke documents karena row project tidak benar-benar dihapus.

### 11.5 Data lokal saat snapshot

| Tabel | Row |
|---|---:|
| users | 14 |
| projects | 14 |
| project_members | 70 |
| documents | 140 |
| document_shares | 19 |
| document_share_links | 22 |
| comments | 160 |
| notifications | 72 |
| activities | 298 |
| audit_logs | 6 |
| system_settings | 2 |
| sessions | 8 |
| personal_access_tokens | 0 |
| jobs/failed_jobs | 0 |

Seluruh 14 migration berstatus Ran.

## 12. Seed Data Lokal

Seeder yang saat ini di-ignore membuat:

- 1 admin;
- 12 user aktif;
- 1 suspended user;
- seluruh akun demo menggunakan password lokal yang sama;
- 14 project, termasuk archived project;
- owner/editor/viewer dan pending memberships;
- 140 dokumen dengan visibility campuran;
- private document shares;
- valid, expired, dan revoked public links;
- comments markdown;
- notifications;
- activities;
- audit logs;
- system settings default.

Keuntungan mempertahankan seeder lokal:

- reset lab cepat;
- semua exercise langsung tersedia;
- QA role/access tidak perlu setup manual.

Konsekuensi menghapus seeder:

- database yang sudah ada tetap berjalan;
- clone/migrate baru akan kosong;
- akun admin pertama harus dibuat manual;
- skenario pentest harus dibuat ulang manual.

## 13. Intentional Vulnerability Map

Bagian ini sensitif dan menjadi alasan utama review.md/docs tidak dipublikasikan bila GitHub ditujukan untuk black-box lab.

| No | Kategori | Implementasi sengaja lemah | Lokasi utama |
|---:|---|---|---|
| 1 | Broken Access Control/IDOR | Direct show document tidak mengecek private visibility seperti list | DocumentPolicy, DocumentDetail |
| 2 | Broken Access Control | Archived upload diblok UI tetapi tidak dicek handler | ProjectDetail.upload, API DocumentController.store |
| 3 | Broken Access Control | Salah satu document access path mengecek role tetapi lupa active status | DocumentPolicy.viewAny |
| 4 | SQL Injection | Search user memakai raw SQL interpolation | SearchService.searchUsers |
| 5 | Stored XSS | Markdown-lite tidak sanitize dan output dirender unescaped | MarkdownLiteService, document-detail view |
| 6 | Mass Assignment | API role update menerapkan seluruh body request | ProjectMemberController.updateRole |
| 7 | Weak token | Public share token memakai predictable MD5 document/sequence | ShareLinkService |
| 8 | Expiry bypass | Public share hanya memeriksa revoked, bukan expires_at | PublicShareController |
| 9 | Brute force | Login tanpa rate limiting | Auth Livewire dan route/API |
| 10 | Old reset token | Hash token dicek tetapi created_at/expiry tidak diperiksa | reset-password Volt view |
| 11 | SSRF | Avatar allow-list memakai substring URL | ProfileForm.importAvatar |
| 12 | Security misconfiguration | nginx tanpa deny-dotfiles dan security headers | deploy/nginx/workhub.conf |
| 13 | File upload weakness | Path berasal dari filename asli dan MIME dari client | DocumentService |

### 13.1 Hal yang perlu direview karena dapat melebar dari vulnerability map

- API login juga tidak throttled, sedangkan dokumen map terutama menyebut login web.
- Upload tidak membatasi tipe file dengan daftar MIME; validasinya hanya memastikan objek file dan ukuran. Ini lebih luas daripada sekadar mempercayai MIME client.
- Email verification tidak enforced walaupun halaman verification tersedia. Ini bukan salah satu 13 item.
- Notification/activity/audit coverage belum lengkap. Ini bukan vulnerability sengaja, tetapi functional gap.
- File document soft-deleted tetap berada di disk. Ini dapat menjadi storage leak.
- Public direct download juga melewati expiry, tetapi sekarang mencatat access count dan last accessed.
- User search global menampilkan semua user match, tidak dibatasi hubungan project.

Jika targetnya “hanya 13 kelemahan”, poin-poin ini perlu diputuskan apakah dianggap desain lab atau harus dirapikan.

## 14. Gap Antara Dokumen dan Implementasi

| Area | Spec | Aktual |
|---|---|---|
| Livewire | Livewire 3 | Composer memakai Livewire 4 |
| Validation architecture | Form Request untuk state changes | Mayoritas inline validate di Livewire/controller |
| Email verification | Fitur auth lengkap | UI ada tetapi tidak enforced |
| Notification | invite/share/comment/role changed | Runtime terutama invite; sebagian hanya seed |
| Activity | project/member/document history lengkap | Runtime terutama project.created dan document.uploaded |
| Audit | seluruh aksi admin sensitif | Hanya beberapa aksi mencatat audit |
| Profile | satu alur profile/security | /profile dan /settings/profile overlap |
| Document deletion | hapus content | Soft-delete dapat direstore; Delete forever menghapus record dan file fisik |
| Queue | database worker | Belum ada job aplikasi yang benar-benar didispatch |
| Frontend JS | Vite JS entry | resources/js/app.js kosong |
| Admin deleted content | review termasuk deleted | Project/Document table memakai withTrashed dan menyediakan restore/delete forever |
| Public link expiry | field expiry | Sengaja tidak enforced |

## 15. Decision Map: Jika Fitur Dihapus

Jangan hanya menghapus view. Hapus satu kelompok secara lengkap: route, component/controller, service, model relation, migration baru untuk drop schema bila database sudah pernah dipakai, UI link, policy, seed, dan test.

### 15.1 Menghapus JSON API

Dapat dihapus bila lab hanya menguji browser:

- routes/api.php
- app/Http/Controllers/Api/V1/
- HasApiTokens dari User
- migration personal_access_tokens
- dependency laravel/sanctum
- config/auth terkait bila tidak lagi dipakai
- dokumentasi dan test API

Dampak: kehilangan permukaan pentest Burp/Postman dan mass-assignment API exercise.

### 15.2 Menghapus admin panel

Kelompok:

- app/Livewire/Admin/
- resources/views/livewire/admin/
- EnsureUserIsAdmin
- route /admin
- AdminController API
- AuditLog model/migration bila tidak dipakai
- system settings UI/API

Perhatian:

- max upload size memakai system_settings.
- avatar allow-list memakai system_settings.
- Jika admin dihapus, dua setting tersebut perlu dipindah ke config/env atau dibuat konstan.
- Policy before admin perlu diputuskan.

### 15.3 Menghapus public share-link

Kelompok:

- PublicShareController
- ShareLinkService
- DocumentShareLink model
- migration document_share_links
- route /share
- generate/revoke UI di document detail
- API share-link endpoints
- relasi Document.shareLinks
- seed/test terkait

Dampak: menghapus exercise weak token dan expiry bypass.

### 15.4 Menghapus direct user sharing

Kelompok:

- DocumentShare model
- migration document_shares
- share form/revoke di DocumentDetail
- ShareController store/destroy
- relasi Document.shares/sharedUsers dan User.documentShares
- visibility query yang memeriksa shares
- policy editor share
- seed/test terkait

Dampak: private document hanya dapat diakses project owner/uploader.

### 15.5 Menghapus comments

Kelompok:

- Comment model/policy/migration
- MarkdownLiteService
- action dan view thread pada DocumentDetail
- API CommentController/routes
- relasi Document.comments dan User.comments
- seed/test terkait

Dampak: menghapus stored-XSS exercise.

### 15.6 Menghapus notifications

Kelompok:

- Notification model/migration
- NotificationList dan view
- sidebar badge/link
- notification creation pada invite/share
- API notification routes
- dashboard unread count

Invite acceptance harus dipindahkan ke halaman khusus atau membership langsung dibuat active.

### 15.7 Menghapus project membership multi-role

Kelompok sangat core dan berdampak besar:

- ProjectMember model/policy/migration
- members tab dan invite workflow
- membership query pada semua policy/list/search/dashboard
- role badge
- notification invite
- API member endpoints

Alternatif lebih aman: pertahankan membership tetapi hilangkan pending/transfer/share yang tidak diperlukan.

### 15.8 Menghapus avatar URL import

Dapat dihapus tanpa menghapus profile:

- ProfileForm.importAvatar
- avatarUrl property/form
- Http outbound request
- avatar_allowed_domains setting
- related system setting UI

Upload avatar lokal tetap dapat dipakai. Ini sekaligus menghapus SSRF exercise.

### 15.9 Menghapus register/reset/verify untuk lab tertutup

Bila hanya memakai seeded accounts:

- route dan Volt view register;
- forgot/reset password;
- verify-email;
- password_reset_tokens bila seluruh reset dihapus.

Pertahankan:

- login;
- logout;
- change password bila masih dibutuhkan;
- session invalidation;
- active middleware.

Dampak: deployment lebih kecil dan akun baru harus dibuat admin/seeder/CLI.

### 15.10 Menghapus activities atau audit logs

Activities adalah feed user/project. Audit logs adalah log admin. Keduanya berbeda.

Menghapus activities:

- Activity model/migration;
- project Activity tab;
- profile activity;
- dashboard recent activity;
- create calls di ProjectForm/DocumentService.

Menghapus audit logs:

- AuditLog model/migration;
- admin audit page;
- logging di user/project/document admin action.

### 15.11 Menghapus settings profile duplikat

Pilihan yang paling rapi:

- Pertahankan /profile sebagai pusat avatar/session/activity.
- Pindahkan email/change-password/delete/appearance ke tab yang konsisten.
- Hapus salah satu navigation/layout yang overlap.

Jangan menghapus /settings/password sebelum fungsi change password dipindahkan.

## 16. Decision Map: Yang Sebaiknya Dipush atau Disembunyikan

### 16.1 Core minimal yang disarankan tetap dipush

- app/
- bootstrap/app.php
- bootstrap/providers.php
- config/
- database/migrations/
- public/.htaccess
- public/index.php
- public/robots.txt
- resources/
- routes/
- artisan
- composer.json
- composer.lock
- package.json
- package-lock.json
- vite.config.js
- .editorconfig
- .gitattributes
- .gitignore
- storage/bootstrap placeholder .gitignore

Alasan lock files tetap dipush: build reproducible dan dependency tidak berubah diam-diam.

### 16.2 Aman disembunyikan dan sudah ignored

- .env
- docs
- tests/phpunit
- seeders/factories
- deploy
- README
- .github
- vendor/node_modules
- build/cache/log/storage/upload

### 16.3 review.md

Pilihan:

1. Tetap lokal: tambahkan /review.md ke .gitignore setelah review selesai.
2. Push private repository: boleh, bila akses repo benar-benar terbatas.
3. Push public repository: tidak disarankan karena file ini mengungkap vulnerability map, workflow, schema, dan keputusan internal.

Saat ini belum di-ignore agar Anda dapat memutuskan sendiri.

### 16.4 .env.example

Pilihan:

1. Tetap hidden seperti sekarang untuk repo paling minimal.
2. Publish versi sanitized agar clone mudah dijalankan.
3. Buat template generik di luar GitHub/internal onboarding.

Jangan pernah mengganti .env.example dengan salinan .env aktual.

## 17. Hal yang Tidak Boleh Dipush

- APP_KEY aktif.
- DB password atau credential service.
- Token/API key.
- auth.json Composer private credential.
- Flux license credential.
- Session/cookie/database dump.
- File pengguna di storage/app/private.
- Avatar/dokumen pentest yang mungkin berisi data nyata.
- storage/logs.
- failed job payload.
- hasil proxy/Burp dan report pentest.
- database export tanpa sanitasi.

## 18. QA dan Health Check

Status QA terakhir sebelum dokumen ini dibuat:

- 33 automated tests lulus.
- 95 assertions lulus.
- Vite production build berhasil.
- Blade view cache berhasil.
- Upload/comment flow lulus tanpa redirect.
- Query document detail tidak tumbuh mengikuti jumlah comment.
- Multiple share-link generation tidak collision.
- Seluruh 14 migration aktif.

Command lokal:

~~~powershell
composer install
npm install
php artisan migrate
npm run build
php artisan test
php artisan serve
~~~

Untuk reset dengan data demo lokal, selama seeders tetap tersedia:

~~~powershell
php artisan migrate:fresh --seed
~~~

Perintah migrate:fresh menghapus seluruh tabel WorkHub. Jangan jalankan pada data yang ingin dipertahankan.

## 19. Prioritas Review yang Disarankan

### Prioritas A — keputusan publikasi

- Putuskan review.md di-ignore atau private.
- Pastikan .env tetap ignored.
- Putuskan apakah sanitized .env.example perlu dipublish.
- Pertahankan lock files.
- Jangan push docs/TECH_SPEC.md atau seeders bila lab ingin black-box.

### Prioritas B — keputusan fitur

- Apakah API tetap diperlukan?
- Apakah public share-link tetap diperlukan?
- Apakah comments/stored-XSS exercise tetap diperlukan?
- Apakah avatar URL/SSRF tetap diperlukan?
- Apakah user registration/reset perlu untuk lab tertutup?
- Gabungkan dua halaman profile.
- Tentukan apakah notification/activity/audit harus dilengkapi atau dihapus.

### Prioritas C — konsistensi security lab

- Pastikan hanya vulnerability yang dipilih tetap aktif.
- Review weakness tambahan di bagian 13.1.
- Setelah fitur dihapus, perbarui policies, routes, migrations, dan seed data bersama-sama.
- Jangan memperbaiki vulnerability sengaja tanpa juga memperbarui tujuan exercise.

## 20. Checklist Keputusan

Salin dan isi pilihan berikut saat review:

- [ ] Web Livewire dipertahankan
- [ ] JSON API dipertahankan
- [ ] Admin panel dipertahankan
- [ ] Registration dipertahankan
- [ ] Password reset dipertahankan
- [ ] Email verification dipertahankan/dienforce
- [ ] Project membership dan pending invite dipertahankan
- [ ] Document private visibility dipertahankan
- [ ] Direct user share dipertahankan
- [ ] Public share-link dipertahankan
- [ ] Comments dipertahankan
- [ ] Search users dipertahankan
- [ ] Notifications dipertahankan
- [ ] Activities dipertahankan
- [ ] Audit logs dipertahankan
- [ ] Avatar URL import dipertahankan
- [ ] Session management dipertahankan
- [ ] Seeders tetap lokal
- [ ] Tests tetap lokal
- [ ] docs tetap lokal
- [ ] deploy config tetap lokal
- [ ] README tetap lokal
- [ ] .env.example tetap lokal atau dibuat sanitized
- [ ] review.md ditambahkan ke .gitignore sebelum public push

---

Dokumen ini menjelaskan kondisi kode pada tanggal snapshot. Jika fitur diubah, route/schema/decision map perlu diperbarui agar tidak menjadi dokumentasi basi.
