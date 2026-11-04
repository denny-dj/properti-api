<?php

namespace App\Services;

use App\Enums\LeadSource;
use App\Exceptions\ApiException;
use App\Models\ArchitectPortfolio;
use App\Models\ConciergeLead;
use App\Models\Property;
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

    /** Kunci untuk mencari judul item rujukan sebuah prospek: "property:12" atau "portfolio:7". */
    public static function referenceKey(ConciergeLead $lead): ?string
    {
        if ($lead->reference_id === null) {
            return null;
        }

        return ($lead->source === LeadSource::ArchitectPortfolio ? 'portfolio' : 'property').':'.$lead->reference_id;
    }

    /**
     * Judul + tautan publik item rujukan untuk sekumpulan prospek (2 query, bukan N+1).
     *
     * @param  iterable<ConciergeLead>  $leads
     * @return array<string, array{title: string, url: ?string, available: bool}>
     */
    public function referenceTitles(iterable $leads): array
    {
        $propertyIds = [];
        $portfolioIds = [];

        foreach ($leads as $lead) {
            $key = self::referenceKey($lead);

            if ($key === null) {
                continue;
            }

            if (str_starts_with($key, 'portfolio:')) {
                $portfolioIds[] = $lead->reference_id;
            } else {
                $propertyIds[] = $lead->reference_id;
            }
        }

        $titles = [];

        if ($propertyIds !== []) {
            foreach (Property::withTrashed()->whereIn('id', array_unique($propertyIds))->get(['id', 'title', 'slug', 'status', 'deleted_at']) as $p) {
                $titles["property:{$p->id}"] = [
                    'title' => $p->title,
                    'url' => $p->deleted_at ? null : route('properties.show', $p->slug),
                    'available' => $p->deleted_at === null,
                ];
            }
        }

        if ($portfolioIds !== []) {
            foreach (ArchitectPortfolio::withTrashed()->whereIn('id', array_unique($portfolioIds))->get(['id', 'title', 'slug', 'deleted_at']) as $p) {
                $titles["portfolio:{$p->id}"] = [
                    'title' => $p->title,
                    'url' => $p->deleted_at ? null : route('portfolios.show', $p->slug),
                    'available' => $p->deleted_at === null,
                ];
            }
        }

        return $titles;
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
