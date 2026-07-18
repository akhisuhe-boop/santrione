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
 *   2. Set $alumniRelation:
 *      - null kalau model List ini SENDIRI adalah Siswa (mis. ListSiswas)
 *      - 'siswa' (atau nama relasi lain) kalau model punya relasi ke Siswa
 *        (mis. ListTagihans -> relasi 'siswa')
 *   3. Panggil $this->alumniToggleAction() di getHeaderActions()
 *
 * Data alumni TIDAK PERNAH dihapus -- cuma disembunyikan dari query
 * default. Toggle-nya per-menu (session key beda tiap Resource), jadi
 * nyalain di 1 menu tidak mempengaruhi menu lain.
 */
trait HidesAlumniByDefault
{
    protected ?string $alumniRelation = 'siswa';

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

        // Model List ini sendiri adalah Siswa
        if ($this->alumniRelation === null) {
            return $query->where('status_siswa', 'Aktif');
        }

        $relation = $this->alumniRelation;

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
