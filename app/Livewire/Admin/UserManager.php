<?php

namespace App\Livewire\Admin;

use App\Exceptions\ApiException;
use App\Livewire\Admin\Concerns\RequiresPermission;
use App\Models\User;
use App\Services\UserService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use RequiresPermission, WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $role = '';

    #[Url(except: '')]
    public string $status = '';

    public ?string $notice = null;

    public function mount(): void
    {
        $this->requirePermission('users.manage');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['q', 'role', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function suspend(int $id): void
    {
        $this->change($id, 'suspended', 'Akun disuspend dan seluruh sesinya diakhiri.');
    }

    public function activate(int $id): void
    {
        $this->change($id, 'active', 'Akun diaktifkan kembali.');
    }

    private function change(int $id, string $status, string $success): void
    {
        $this->requirePermission('users.manage');
        $this->resetErrorBag('list');

        $target = User::findOrFail($id);

        try {
            app(UserService::class)->updateStatus($target, auth()->user(), $status);
            $this->notice = $success;
        } catch (ApiException $e) {
            $this->notice = null;
            $this->addError('list', $e->getMessage());
        }
    }

    public function render()
    {
        $filters = ['per_page' => 15];

        if (trim($this->q) !== '') {
            $filters['q'] = trim($this->q);
        }

        if (in_array($this->role, ['admin', 'partner', 'user'], true)) {
            $filters['role'] = $this->role;
        }

        if (in_array($this->status, ['active', 'suspended'], true)) {
            $filters['status'] = $this->status;
        }

        return view('livewire.admin.user-manager', [
            'users' => app(UserService::class)->list($filters),
        ]);
    }
}
