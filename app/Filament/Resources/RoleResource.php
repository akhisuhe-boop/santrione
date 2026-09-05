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
     * Kelompokkan SEMUA baris di tabel permissions (hasil generate
     * Filament Shield) jadi "1 menu = 1 grup" -- alih-alih tampilan
     * asli Shield yang pecah jadi 12 checkbox terpisah (view, view_any,
     * create, update, delete, delete_any, restore, restore_any,
     * replicate, reorder, force_delete, force_delete_any) untuk SETIAP
     * menu. Dikerjakan dengan cara membaca permission yang SUDAH ADA
     * di database (bukan menebak API internal package Shield secara
     * langsung), supaya tidak gampang rusak kalau versi package
     * berubah -- ini murni pola nama string yang sudah stabil dipakai
     * Shield selama ini.
     *
     * Return: [
     *   'Resource' => ['Nama Menu' => ['permission1', 'permission2', ...], ...],
     *   'Halaman'  => [...],
     *   'Widget'   => [...],
     * ]
     */
    public static function groupPermissionsByMenu(): array
    {
        $semuaNama = \Spatie\Permission\Models\Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name');

        // Urutan PENTING -- prefix yang lebih spesifik/panjang harus
        // dicek LEBIH DULU (mis. "view_any" sebelum "view", kalau
        // tidak "view_any_announcement" akan salah kepotong jadi
        // subjek "any_announcement").
        $prefixAksi = [
            'view_any', 'view',
            'restore_any', 'restore',
            'force_delete_any', 'force_delete',
            'delete_any', 'delete',
            'replicate', 'reorder',
            'create', 'update',
        ];

        $grup = ['Resource' => [], 'Halaman' => [], 'Widget' => []];

        foreach ($semuaNama as $nama) {

            if (str_starts_with($nama, 'page_')) {
                $subjek = \Illuminate\Support\Str::of(substr($nama, 5))->headline()->toString();
                $grup['Halaman'][$subjek][] = $nama;

                continue;
            }

            if (str_starts_with($nama, 'widget_')) {
                $subjek = \Illuminate\Support\Str::of(substr($nama, 7))->headline()->toString();
                $grup['Widget'][$subjek][] = $nama;

                continue;
            }

            $subjekMentah = null;

            foreach ($prefixAksi as $prefix) {
                if (str_starts_with($nama, $prefix . '_')) {
                    $subjekMentah = substr($nama, strlen($prefix) + 1);

                    break;
                }
            }

            // Nama permission yang tidak cocok pola manapun (custom di
            // luar Shield) -- masukkan apa adanya sebagai grup sendiri
            // supaya tidak hilang/terlewat dari daftar.
            $subjek = \Illuminate\Support\Str::of($subjekMentah ?? $nama)->headline()->toString();

            $grup['Resource'][$subjek][] = $nama;
        }

        ksort($grup['Resource']);
        ksort($grup['Halaman']);
        ksort($grup['Widget']);

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

        $buatToggleSection = function (string $judul, array $menuList) {
            if (empty($menuList)) {
                return null;
            }

            $toggles = [];

            foreach ($menuList as $namaMenu => $daftarPermission) {
                $toggles[] = \Filament\Forms\Components\Toggle::make('menu_toggle.' . md5($namaMenu . implode(',', $daftarPermission)))
                    ->label($namaMenu)
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

            return \Filament\Forms\Components\Section::make($judul)
                ->collapsible()
                ->schema([
                    \Filament\Forms\Components\Grid::make(3)->schema($toggles),
                ]);
        };

        $sections = collect($grup)
            ->map(fn ($menuList, $judul) => $buatToggleSection($judul, $menuList))
            ->filter()
            ->values()
            ->all();

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

        // Bikin peta hash(nama menu + daftar permission) -> daftar
        // permission, PERSIS seperti cara key toggle dibuat di form()
        // di atas -- supaya bisa dicocokkan balik.
        $petaHash = [];

        foreach ($grup as $kategori) {
            foreach ($kategori as $namaMenu => $daftarPermission) {
                $hash = md5($namaMenu . implode(',', $daftarPermission));
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
