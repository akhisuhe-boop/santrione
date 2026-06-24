<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PrestasiSiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrestasiSiswaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_prestasi::siswa');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PrestasiSiswa $prestasiSiswa): bool
    {
        return $user->can('view_prestasi::siswa');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_prestasi::siswa');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PrestasiSiswa $prestasiSiswa): bool
    {
        return $user->can('update_prestasi::siswa');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PrestasiSiswa $prestasiSiswa): bool
    {
        return $user->can('delete_prestasi::siswa');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_prestasi::siswa');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PrestasiSiswa $prestasiSiswa): bool
    {
        return $user->can('force_delete_prestasi::siswa');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_prestasi::siswa');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PrestasiSiswa $prestasiSiswa): bool
    {
        return $user->can('restore_prestasi::siswa');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_prestasi::siswa');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PrestasiSiswa $prestasiSiswa): bool
    {
        return $user->can('replicate_prestasi::siswa');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_prestasi::siswa');
    }
}
