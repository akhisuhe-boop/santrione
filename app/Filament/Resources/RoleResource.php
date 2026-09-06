<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Resources\RoleResource as BaseRoleResource;
use Illuminate\Database\Eloquent\Builder;

class RoleResource extends BaseRoleResource
{
    public static function getNavigationGroup(): ?string
    {
        return 'Master Setting';
    }

    /**
     * Kelompokkan permission PERSIS mengikuti struktur menu & sub-menu
     * yang tenant lihat sendiri di sidebar -- BUKAN lagi dikelompokkan
     * dari nama tabel/model database (revisi setelah masukan user: itu
     * tidak mencerminkan menu yang mereka kenal sehari-hari).
     *
     * "Menu" = navigationGroup tiap Resource/Page (mis. "Akademik"),
     * "Sub-menu" = navigationLabel Resource/Page itu sendiri (mis.
     * "Jadwal Pelajaran"). Permission yang cocok untuk 1 Resource/Page
     * dicari lewat pola nama standar Shield ({aksi}_{model_snake} untuk
     * Resource, page_{NamaClass} untuk Page) terhadap permission yang
     * SUDAH ADA di database -- supaya tetap tidak bergantung menebak
     * detail internal Shield secara langsung.
     *
     * Return: [
     *   'Akademik' => ['Jadwal Pelajaran' => [...permission], 'Nilai' => [...]],
     *   'Keuangan' => [...],
     *   'Lainnya'  => [...],  // permission yang tidak match Resource/Page manapun (mis. Widget)
     * ]
     */
    public static function groupPermissionsByMenu(): array
    {
        $semuaNama = \Spatie\Permission\Models\Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        $adaNama = array_flip($semuaNama);
        $sudahDipakai = [];

        $prefixAksi = [
            'view_any', 'view',
            'restore_any', 'restore',
            'force_delete_any', 'force_delete',
            'delete_any', 'delete',
            'replicate', 'reorder',
            'create', 'update',
        ];

        $panel = \Filament\Facades\Filament::getPanel('admin');
        $grup = [];
        $sortResource = []; // navGroup|navLabel -> nilai navigationSort, untuk urutan dalam 1 grup

        // RESOURCE (mis. LembagaResource, SiswaResource, dst)
        foreach ($panel->getResources() as $resourceClass) {
            if (! class_exists($resourceClass)) {
                continue;
            }

            // Resource yang sengaja disembunyikan dari sidebar
            // (shouldRegisterNavigation() false -- biasanya diakses
            // lewat relasi/halaman lain, bukan link langsung, mis.
            // KantinResource/KantinTransaksiResource) TIDAK ditampilkan
            // di sini juga, supaya form ini PERSIS mencerminkan menu
            // yang tenant benar-benar lihat -- bukan menu "hantu" yang
            // sebenarnya tidak pernah muncul di sidebar mereka.
            if (! $resourceClass::shouldRegisterNavigation()) {

                // Tetap tandai permission-nya "sudah ditangani" (walau
                // tidak dibuatkan toggle) -- supaya tidak malah nyasar
                // muncul lagi di grup "Lainnya" di bawah.
                $subjekTersembunyi = \Illuminate\Support\Str::snake(
                    \Illuminate\Support\Str::of(class_basename($resourceClass))->beforeLast('Resource')->toString(),
                    '::'
                );

                foreach ($prefixAksi as $prefix) {
                    $sudahDipakai[$prefix . '_' . $subjekTersembunyi] = true;
                }

                continue;
            }

            $navGroup = $resourceClass::getNavigationGroup() ?? 'Lainnya';
            $navLabel = $resourceClass::getNavigationLabel();

            // PENTING: subjek permission Shield diambil dari nama
            // CLASS RESOURCE itu sendiri (dikurangi akhiran "Resource"),
            // BUKAN dari nama model belakangnya -- keduanya sering beda
            // (mis. LaporanPerizinanResource modelnya Siswa, bukan
            // LaporanPerizinan). Salah ambil dari getModel() sebelumnya
            // bikin HAMPIR SEMUA resource gagal cocok, numpuk semua di
            // "Lainnya" (ditemukan 6 Sep 2026). Shield juga pakai "::"
            // sebagai pemisah kata, BUKAN underscore biasa.
            $namaResource = \Illuminate\Support\Str::of(class_basename($resourceClass))
                ->beforeLast('Resource')
                ->toString();
            $subjek = \Illuminate\Support\Str::snake($namaResource, '::');

            $daftarPermission = [];

            foreach ($prefixAksi as $prefix) {
                $nama = $prefix . '_' . $subjek;

                if (isset($adaNama[$nama])) {
                    $daftarPermission[] = $nama;
                    $sudahDipakai[$nama] = true;
                }
            }

            if (! empty($daftarPermission)) {
                $grup[$navGroup][$navLabel] = $daftarPermission;
                $sortResource[$navGroup . '|' . $navLabel] = $resourceClass::getNavigationSort();
            }
        }

        // PAGE MANDIRI (mis. Dashboard, Scan Produk, Langganan, dst)
        foreach ($panel->getPages() as $pageClass) {
            if (! class_exists($pageClass)) {
                continue;
            }

            if (! $pageClass::shouldRegisterNavigation()) {
                $sudahDipakai['page_' . class_basename($pageClass)] = true;

                continue;
            }

            $navGroup = $pageClass::getNavigationGroup() ?? 'Lainnya';
            $navLabel = $pageClass::getNavigationLabel();
            $nama = 'page_' . class_basename($pageClass);

            if (isset($adaNama[$nama])) {
                $grup[$navGroup][$navLabel] = [$nama];
                $sudahDipakai[$nama] = true;
                $sortResource[$navGroup . '|' . $navLabel] = $pageClass::getNavigationSort();
            }
        }

        // WIDGET & permission lain yang tidak cocok Resource/Page
        // manapun -- ditampung supaya tidak hilang dari daftar, bukan
        // dibuang.
        foreach ($semuaNama as $nama) {
            if (isset($sudahDipakai[$nama])) {
                continue;
            }

            if (str_starts_with($nama, 'widget_')) {
                $label = \Illuminate\Support\Str::of(substr($nama, 7))->headline()->toString();
                $grup['Widget'][$label] = [$nama];

                continue;
            }

            $subjekMentah = $nama;

            foreach ($prefixAksi as $prefix) {
                if (str_starts_with($nama, $prefix . '_')) {
                    $subjekMentah = substr($nama, strlen($prefix) + 1);

                    break;
                }
            }

            $label = \Illuminate\Support\Str::of($subjekMentah)->replace('::', ' ')->headline()->toString();
            $grup['Lainnya'][$label][] = $nama;
        }

        // Urutan section (menu) IKUT PERSIS urutan resmi yang
        // didefinisikan di AdminPanelProvider::navigationGroups() --
        // bukan diurutkan alfabetis -- supaya otomatis konsisten sama
        // sidebar dan otomatis ikut berubah kalau urutan itu diubah,
        // tanpa perlu ubah kode di sini juga. Grup yang TIDAK ada di
        // daftar resmi itu (mis. "Lainnya", "Widget") ditaruh PALING
        // BAWAH.
        $urutanResmi = collect($panel->getNavigationGroups())
            ->map(fn ($g) => $g->getLabel())
            ->values()
            ->all();

        uksort($grup, function ($a, $b) use ($urutanResmi) {
            $posA = array_search($a, $urutanResmi);
            $posB = array_search($b, $urutanResmi);
            $posA = $posA === false ? 9999 : $posA;
            $posB = $posB === false ? 9999 : $posB;

            return $posA <=> $posB ?: $a <=> $b;
        });

        // Urutan sub-menu DALAM 1 grup ikut getNavigationSort() resmi
        // tiap Resource/Page (angka lebih kecil duluan, null di
        // belakang) -- sama seperti cara Filament sendiri mengurutkan
        // sidebar.
        foreach ($grup as $namaMenu => &$subMenu) {
            uksort($subMenu, function ($a, $b) use ($namaMenu, $sortResource) {
                $sortA = $sortResource[$namaMenu . '|' . $a] ?? null;
                $sortB = $sortResource[$namaMenu . '|' . $b] ?? null;

                if ($sortA === $sortB) {
                    return $a <=> $b;
                }

                if ($sortA === null) {
                    return 1;
                }

                if ($sortB === null) {
                    return -1;
                }

                return $sortA <=> $sortB;
            });
        }
        unset($subMenu);

        return $grup;
    }

    /**
     * Form Create/Edit Role yang DISEDERHANAKAN total -- 1 toggle per
     * menu (bukan 12 checkbox per menu seperti bawaan Shield).
     * Menghidupkan 1 toggle otomatis berarti SEMUA hak akses (lihat,
     * tambah, ubah, hapus, dst) untuk menu itu diberikan -- tidak ada
     * lagi pilihan granular per-aksi, sesuai permintaan supaya lebih
     * simpel.
     */
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        $grup = static::groupPermissionsByMenu();

        $sections = [];

        foreach ($grup as $namaMenu => $subMenuList) {

            $toggles = [];

            foreach ($subMenuList as $namaSubMenu => $daftarPermission) {
                $toggles[] = \Filament\Forms\Components\Toggle::make('menu_toggle.' . md5($namaMenu . '|' . $namaSubMenu . '|' . implode(',', $daftarPermission)))
                    ->label($namaSubMenu)
                    ->inline(false)
                    ->afterStateHydrated(function (\Filament\Forms\Components\Toggle $component, $record) use ($daftarPermission) {
                        if (! $record) {
                            return;
                        }

                        $adaSatuAja = $record->permissions()
                            ->whereIn('name', $daftarPermission)
                            ->exists();

                        $component->state($adaSatuAja);
                    });
            }

            $sections[] = \Filament\Forms\Components\Section::make($namaMenu)
                ->collapsible()
                ->schema([
                    \Filament\Forms\Components\Grid::make(3)->schema($toggles),
                ]);
        }

        return $form->schema([
            \Filament\Forms\Components\Section::make('Info Peran')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nama Peran')
                        ->required()
                        ->maxLength(255),

                    \Filament\Forms\Components\Hidden::make('guard_name')
                        ->default('web'),
                ])
                ->columns(2),

            ...$sections,
        ]);
    }

    /**
     * Konversi state toggle yang barusan diisi user (form()) jadi
     * daftar nama permission mentah yang perlu di-sync ke Role --
     * dipakai bersama oleh CreateRole & EditRole, supaya logikanya
     * cuma ditulis 1 kali.
     */
    public static function extractPermissionsFromToggleState(array $toggleState): array
    {
        $grup = static::groupPermissionsByMenu();

        // Bikin peta hash -> daftar permission, PERSIS seperti cara
        // key toggle dibuat di form() di atas -- supaya bisa
        // dicocokkan balik.
        $petaHash = [];

        foreach ($grup as $namaMenu => $subMenuList) {
            foreach ($subMenuList as $namaSubMenu => $daftarPermission) {
                $hash = md5($namaMenu . '|' . $namaSubMenu . '|' . implode(',', $daftarPermission));
                $petaHash[$hash] = $daftarPermission;
            }
        }

        $permissions = [];

        foreach ($toggleState as $hash => $aktif) {
            if ($aktif && isset($petaHash[$hash])) {
                array_push($permissions, ...$petaHash[$hash]);
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Peran (Role) sekarang campuran: sebagian GLOBAL (mis. "Admin
     * Yayasan", "super_admin" — yayasan_id NULL, dipakai bersama
     * semua tenant), sebagian CUSTOM milik 1 yayasan saja.
     *
     * Tenant biasa cuma boleh lihat: role global + role custom
     * miliknya sendiri (TIDAK lihat role custom yayasan lain).
     * Platform Admin tetap lihat semuanya (untuk keperluan support).
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->is_platform_admin) {
            return $query;
        }

        $tenant = \Filament\Facades\Filament::getTenant();

        return $query->where(function ($q) use ($tenant) {
            $q->whereNull('yayasan_id');

            if ($tenant) {
                $q->orWhere('yayasan_id', $tenant->id);
            }
        });
    }

    /**
     * Role GLOBAL (yayasan_id NULL, mis. "Admin Yayasan") cuma boleh
     * di-edit/dihapus oleh Platform Admin — supaya tenant biasa tidak
     * bisa ubah/hapus role yang dipakai bersama semua yayasan lain.
     */
    public static function canEdit($record): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        return $record->yayasan_id !== null;
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    /**
     * Bulk "Delete selected" dihapus total — checkbox pilih-banyak itu
     * gampang kepencet tanpa sadar dan sebelumnya tetap kelihatan
     * aktif untuk role global (Admin Yayasan/Super Admin) walau
     * secara policy sebenarnya sudah ditolak. Daripada mengandalkan
     * itu, tombolnya langsung dihilangkan dari halaman ini.
     */
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return parent::table($table)
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        $pages = parent::getPages();

        // 'index' WAJIB di-override juga -- Pages\ListRoles bawaan Shield
        // hardcode $resource ke class RoleResource Shield sendiri (bukan
        // late-static-bound), jadi query tabelnya lolos dari scoping tenant
        // di getEloquentQuery() atas kalau tidak diganti (lihat penjelasan
        // lengkap di App\Filament\Resources\RoleResource\Pages\ListRoles).
        $pages['index'] = \App\Filament\Resources\RoleResource\Pages\ListRoles::route('/');
        $pages['create'] = \App\Filament\Resources\RoleResource\Pages\CreateRole::route('/create');
        $pages['edit'] = \App\Filament\Resources\RoleResource\Pages\EditRole::route('/{record}/edit');

        return $pages;
    }
}
