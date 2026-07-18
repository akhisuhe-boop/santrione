<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pasang trait ini di CLASS LIST PAGE Resource (bukan di Resource class-nya
 * sendiri), misal app/Filament/Resources/SiswaResource/Pages/ListSiswas.php.
 *
 * Cara pakai:
 *   1. use HidesAlumniByDefault;
 *   2. Override method alumniRelation() kalau perlu:
 *      - return null kalau model List ini SENDIRI adalah Siswa
 *        (mis. ListSiswas)
 *      - return 'siswa' (default, tidak perlu override) kalau model
 *        punya relasi langsung ke Siswa (mis. ListTagihans)
 *      - return 'wallet.siswa' dst kalau relasinya nested
 *   3. Panggil $this->alumniToggleAction() di getHeaderActions()
 *
 * Data alumni TIDAK PERNAH dihapus -- cuma disembunyikan dari query
 * default. Toggle-nya per-menu (session key beda tiap Resource), jadi
 * nyalain di 1 menu tidak mempengaruhi menu lain.
 *
 * CATATAN: sengaja pakai METHOD (bukan property class) untuk menentukan
 * nama relasi -- PHP tidak mengizinkan class & trait yang dipakainya
 * sama-sama deklarasi property dengan default value berbeda (fatal
 * error "incompatible"). Method override tidak punya batasan itu.
 */
trait HidesAlumniByDefault
{
    /**
     * Nama relasi ke Siswa. Override di class yang pakai trait ini
     * kalau bukan 'siswa' langsung (mis. null, atau 'wallet.siswa').
     */
    protected function alumniRelation(): ?string
    {
        return 'siswa';
    }

    protected function alumniSessionKey(): string
    {
        return 'tampilkan_alumni_' . static::getResource();
    }

    protected function isAlumniShown(): bool
    {
        return (bool) session($this->alumniSessionKey(), false);
    }

    protected function alumniToggleAction(): Action
    {
        return Action::make('toggleAlumni')
            ->label(fn () => $this->isAlumniShown() ? 'Sembunyikan Alumni' : 'Tampilkan Alumni')
            ->icon(fn () => $this->isAlumniShown() ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
            ->color(fn () => $this->isAlumniShown() ? 'gray' : 'warning')
            ->action(function () {
                session([$this->alumniSessionKey() => ! $this->isAlumniShown()]);
            });
    }

    protected function applyAlumniScope(Builder $query): Builder
    {
        if ($this->isAlumniShown()) {
            return $query;
        }

        $relation = $this->alumniRelation();

        // Model List ini sendiri adalah Siswa
        if ($relation === null) {
            return $query->where('status_siswa', 'Aktif');
        }

        return $query->where(function (Builder $q) use ($relation) {
            $q->whereDoesntHave($relation)
              ->orWhereHas($relation, fn (Builder $s) =>
                  $s->where('status_siswa', 'Aktif'));
        });
    }

    protected function getTableQuery(): Builder
    {
        return $this->applyAlumniScope(parent::getTableQuery());
    }
}
