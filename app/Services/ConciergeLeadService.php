<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\ConciergeLead;
use App\Models\User;
use App\Repositories\Contracts\ConciergeLeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConciergeLeadService
{
    public function __construct(private ConciergeLeadRepositoryInterface $leads)
    {
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->leads->paginateFiltered($filters, (int) ($filters['per_page'] ?? 15));
    }

    public function updateStatus(int $id, User $admin, string $status, ?string $note = null): ConciergeLead
    {
        $lead = $this->leads->find($id) ?? throw new ApiException('Prospek tidak ditemukan.', 404);

        $notes = $lead->notes;

        if ($note !== null && trim($note) !== '') {
            $line = '['.now()->format('d/m/Y H:i').' '.$admin->name.'] '.trim($note);
            $notes = $notes ? $notes."\n".$line : $line;
        }

        /** @var ConciergeLead $lead */
        $lead = $this->leads->update($lead, [
            'status' => $status,
            'notes' => $notes,
            'handled_by' => $lead->handled_by ?? $admin->id,
        ]);

        return $lead->load('handler');
    }
}
