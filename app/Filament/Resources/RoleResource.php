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

        // RESOURCE (mis. LembagaResource, SiswaResource, dst)
        foreach ($panel->getResources() as $resourceClass) {
            if (! class_exists($resourceClass)) {
                continue;
            }

            $navGroup = $resourceClass::getNavigationGroup() ?? 'Lainnya';
            $navLabel = $resourceClass::getNavigationLabel();
            $subjek = \Illuminate\Support\Str::snake(class_basename($resourceClass::getModel()));

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
            }
        }

        // PAGE MANDIRI (mis. Dashboard, Scan Produk, Langganan, dst)
        foreach ($panel->getPages() as $pageClass) {
            if (! class_exists($pageClass)) {
                continue;
            }

            $navGroup = $pageClass::getNavigationGroup() ?? 'Lainnya';
            $navLabel = $pageClass::getNavigationLabel();
            $nama = 'page_' . class_basename($pageClass);

            if (isset($adaNama[$nama])) {
                $grup[$navGroup][$navLabel] = [$nama];
                $sudahDipakai[$nama] = true;
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

            $label = \Illuminate\Support\Str::of($subjekMentah)->headline()->toString();
            $grup['Lainnya'][$label][] = $nama;
        }

        ksort($grup);

        foreach ($grup as &$subMenu) {
            ksort($subMenu);
        }

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

        $pages['create'] = \App\Filament\Resources\RoleResource\Pages\CreateRole::route('/create');
        $pages['edit'] = \App\Filament\Resources\RoleResource\Pages\EditRole::route('/{record}/edit');

        return $pages;
    }
}
