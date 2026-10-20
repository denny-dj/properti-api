<?php

namespace App\Repositories\Eloquent;

use App\Models\Property;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PropertyRepository extends BaseRepository implements PropertyRepositoryInterface
{
    /** Rumus Haversine (km). Binding: lat, lng, lat. least(1, ..) mencegah NaN akibat pembulatan. */
    private const HAVERSINE = '(6371 * acos(least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))';

    public function __construct(Property $model)
    {
        parent::__construct($model);
    }

    public function findPublishedBySlug(string $slug): ?Property
    {
        return $this->model->newQuery()
            ->with(['partner.partnerProfile', 'images', 'facilities'])
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    public function incrementViews(Property $property): void
    {
        // Query builder dasar agar updated_at tidak ikut berubah.
        DB::table($this->model->getTable())->where('id', $property->id)->increment('views_count');
    }

    public function paginatePublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['coverImage', 'facilities'])
            ->published();

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters, 'published_at');

        return $query->paginate($perPage)->withQueryString();
    }

    public function paginateByPartner(int $partnerId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['coverImage', 'facilities'])
            ->where('partner_id', $partnerId)
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status));

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters, 'created_at');

        return $query->paginate($perPage)->withQueryString();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['coverImage', 'partner.partnerProfile'])
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['partner_id'] ?? null, fn (Builder $q, $id) => $q->where('partner_id', $id));

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters, 'created_at');

        return $query->paginate($perPage)->withQueryString();
    }

    public function syncFacilities(Property $property, array $facilityIds): void
    {
        $property->facilities()->sync($facilityIds);
    }

    public function loadDetail(Property $property): Property
    {
        return $property->load(['partner.partnerProfile', 'images', 'facilities']);
    }

    // ------------------------------------------------------------------

    private function applyFilters(Builder $query, array $f): void
    {
        $query
            ->when($f['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('title', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('city', 'like', $like));
            })
            ->when($f['property_type'] ?? null, fn (Builder $q, $v) => $q->where('property_type', $v))
            ->when($f['listing_type'] ?? null, fn (Builder $q, $v) => $q->where('listing_type', $v))
            ->when($f['city'] ?? null, fn (Builder $q, $v) => $q->where('city', 'like', '%'.$v.'%'))
            ->when($f['province'] ?? null, fn (Builder $q, $v) => $q->where('province', 'like', '%'.$v.'%'))
            ->when($f['district'] ?? null, fn (Builder $q, $v) => $q->where('district', 'like', '%'.$v.'%'))
            ->when($f['certificate'] ?? null, fn (Builder $q, $v) => $q->where('certificate', $v))
            ->when($f['min_price'] ?? null, fn (Builder $q, $v) => $q->where('price', '>=', $v))
            ->when($f['max_price'] ?? null, fn (Builder $q, $v) => $q->where('price', '<=', $v))
            ->when($f['bedrooms'] ?? null, fn (Builder $q, $v) => $q->where('bedrooms', '>=', $v))
            ->when($f['bathrooms'] ?? null, fn (Builder $q, $v) => $q->where('bathrooms', '>=', $v))
            ->when($f['min_land_area'] ?? null, fn (Builder $q, $v) => $q->where('land_area', '>=', $v))
            ->when($f['max_land_area'] ?? null, fn (Builder $q, $v) => $q->where('land_area', '<=', $v));

        // Semua fasilitas yang dipilih harus dimiliki (AND).
        foreach ((array) ($f['facilities'] ?? []) as $slug) {
            $query->whereHas('facilities', fn (Builder $q) => $q->where('slug', $slug));
        }

        // Pencarian berdasarkan radius dari titik koordinat (Maps).
        if (isset($f['lat'], $f['lng'])) {
            $lat = (float) $f['lat'];
            $lng = (float) $f['lng'];
            $radius = (float) ($f['radius_km'] ?? 10);

            $query->select('properties.*')
                ->selectRaw(self::HAVERSINE.' as distance_km', [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw(self::HAVERSINE.' <= ?', [$lat, $lng, $lat, $radius]);
        }
    }

    private function applySort(Builder $query, array $f, string $defaultColumn): void
    {
        $hasGeo = isset($f['lat'], $f['lng']);

        match ($f['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'nearest' => $hasGeo
                ? $query->orderBy('distance_km')
                : $query->orderByDesc($defaultColumn)->orderByDesc('id'),
            default => $query->orderByDesc($defaultColumn)->orderByDesc('id'),
        };
    }
}
