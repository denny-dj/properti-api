<?php

namespace App\Livewire\Admin\Concerns;

trait RequiresPermission
{
    /** Pertahanan berlapis: rute sudah dijaga, aksi komponen diperiksa lagi di server. */
    protected function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
