<?php

namespace App\Livewire\Partner;

use App\Enums\PropertyStatus;
use App\Exceptions\ApiException;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use App\Services\GeocodingService;
use App\Services\PropertyImageService;
use App\Services\PropertyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Formulir tambah/ubah properti + pengelolaan foto (mode ubah). */
class PropertyForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $propertyId = null;

    public string $title = '';
    public string $description = '';
    public string $property_type = 'house';
    public string $listing_type = 'sale';
    public string $price = '';
    public string $bedrooms = '';
    public string $bathrooms = '';
    public string $floors = '1';
    public string $land_area = '';
    public string $building_area = '';
    public string $certificate = '';
    public string $address = '';
    public string $district = '';
    public string $city = '';
    public string $province = '';
    public string $postal_code = '';
    public string $latitude = '';
    public string $longitude = '';

    /** @var array<int, string> */
    public array $facility_ids = [];

    public array $photos = [];

    public ?string $notice = null;

    public function mount(?int $property = null): void
    {
        if ($property === null) {
            return;
        }

        $model = Property::where('partner_id', auth()->id())->with('facilities')->findOrFail($property);
        Gate::authorize('update', $model);

        $this->propertyId = $model->id;

        $this->fill([
            'title' => $model->title,
            'description' => (string) $model->description,
            'property_type' => $model->property_type->value,
            'listing_type' => $model->listing_type->value,
            'price' => (string) (int) round((float) $model->price),
            'bedrooms' => (string) $model->bedrooms,
            'bathrooms' => (string) $model->bathrooms,
            'floors' => (string) $model->floors,
            'land_area' => $model->land_area !== null ? (string) (float) $model->land_area : '',
            'building_area' => $model->building_area !== null ? (string) (float) $model->building_area : '',
            'certificate' => (string) $model->certificate,
            'address' => $model->address,
            'district' => (string) $model->district,
            'city' => $model->city,
            'province' => $model->province,
            'postal_code' => (string) $model->postal_code,
            'latitude' => $model->latitude !== null ? (string) $model->latitude : '',
            'longitude' => $model->longitude !== null ? (string) $model->longitude : '',
            'facility_ids' => $model->facilities->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:10000'],
            'property_type' => ['required', Rule::in(['house', 'shop'])],
            'listing_type' => ['required', Rule::in(['sale', 'rent'])],
            'price' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'floors' => ['nullable', 'integer', 'min:1', 'max:50'],
            'land_area' => ['nullable', 'numeric', 'min:1', 'max:99999999'],
            'building_area' => ['nullable', 'numeric', 'min:1', 'max:99999999'],
            'certificate' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'facility_ids' => ['array', 'max:30'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'latitude.required_with' => 'Isi latitude dan longitude bersamaan, atau kosongkan keduanya.',
            'longitude.required_with' => 'Isi latitude dan longitude bersamaan, atau kosongkan keduanya.',
        ];
    }

    private function owned(): Property
    {
        return Property::where('partner_id', auth()->id())->findOrFail($this->propertyId);
    }

    /** Ubah string formulir menjadi tipe yang sesuai kolom database. */
    private function payload(): array
    {
        $text = fn (string $v) => trim($v) === '' ? null : trim($v);
        $int = fn (string $v, int $default) => trim($v) === '' ? $default : (int) $v;
        $float = fn (string $v) => trim($v) === '' ? null : (float) $v;

        return [
            'title' => trim($this->title),
            'description' => $text($this->description),
            'property_type' => $this->property_type,
            'listing_type' => $this->listing_type,
            'price' => (float) $this->price,
            'bedrooms' => $int($this->bedrooms, 0),
            'bathrooms' => $int($this->bathrooms, 0),
            'floors' => max(1, $int($this->floors, 1)),
            'land_area' => $float($this->land_area),
            'building_area' => $float($this->building_area),
            'certificate' => $text($this->certificate),
            'address' => trim($this->address),
            'district' => $text($this->district),
            'city' => trim($this->city),
            'province' => trim($this->province),
            'postal_code' => $text($this->postal_code),
            'latitude' => $float($this->latitude),
            'longitude' => $float($this->longitude),
            'facility_ids' => array_map('intval', $this->facility_ids),
        ];
    }

    public function save()
    {
        $this->validate();
        $this->resetErrorBag('form');

        try {
            if ($this->propertyId === null) {
                $property = app(PropertyService::class)->create(auth()->user(), $this->payload());

                session()->flash('status', 'Properti disimpan sebagai draft. Unggah foto, lalu ajukan untuk diverifikasi.');

                return redirect()->route('partner.properties.edit', $property->id);
            }

            $property = $this->owned();
            Gate::authorize('update', $property);

            $wasPublished = $property->status === PropertyStatus::Published;
            app(PropertyService::class)->update($property, $this->payload());

            $this->notice = $wasPublished
                ? 'Perubahan disimpan. Karena sudah tayang, properti diverifikasi ulang administrator sebelum tampil kembali.'
                : 'Perubahan disimpan.';
        } catch (ApiException $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    /** Alamat -> koordinat lewat Google Geocoding. */
    public function locate(): void
    {
        $this->resetErrorBag('geo');

        $query = trim(implode(', ', array_filter([trim($this->address), trim($this->district), trim($this->city), trim($this->province)])));

        if (strlen($query) < 3) {
            $this->addError('geo', 'Isi alamat dan kota terlebih dahulu.');

            return;
        }

        try {
            $result = app(GeocodingService::class)->geocode($query);
        } catch (ApiException $e) {
            $this->addError('geo', $e->getMessage());

            return;
        }

        $this->latitude = (string) $result['latitude'];
        $this->longitude = (string) $result['longitude'];

        foreach (['district' => 'district', 'city' => 'city', 'province' => 'province', 'postal_code' => 'postal_code'] as $field => $key) {
            if (trim($this->{$field}) === '' && ! empty($result[$key])) {
                $this->{$field} = (string) $result[$key];
            }
        }

        $this->notice = 'Koordinat ditemukan. Geser pin di peta bila perlu, lalu simpan.';
    }

    public function uploadPhotos(): void
    {
        $this->resetErrorBag('photos');

        if ($this->propertyId === null) {
            $this->addError('photos', 'Simpan properti terlebih dahulu sebelum mengunggah foto.');

            return;
        }

        $this->validate([
            'photos' => ['required', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'photos.*.image' => 'Berkas harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 4 MB.',
        ]);

        $property = $this->owned();
        Gate::authorize('update', $property);

        try {
            app(PropertyImageService::class)->upload($property, $this->photos);
        } catch (ApiException $e) {
            $this->addError('photos', $e->getMessage());

            return;
        }

        $this->reset('photos');
        $this->notice = 'Foto berhasil diunggah.';
    }

    public function makeCover(int $imageId): void
    {
        $property = $this->owned();
        Gate::authorize('update', $property);

        $image = PropertyImage::where('property_id', $property->id)->findOrFail($imageId);
        app(PropertyImageService::class)->setCover($image);
        $this->notice = 'Foto sampul diperbarui.';
    }

    public function deleteImage(int $imageId): void
    {
        $property = $this->owned();
        Gate::authorize('update', $property);

        $image = PropertyImage::where('property_id', $property->id)->findOrFail($imageId);
        app(PropertyImageService::class)->delete($image);
        $this->notice = 'Foto dihapus.';
    }

    public function submit(): void
    {
        $property = $this->owned();
        Gate::authorize('update', $property);

        $this->resetErrorBag('form');

        try {
            app(PropertyService::class)->submit($property);
            $this->notice = 'Properti diajukan dan menunggu verifikasi administrator.';
        } catch (ApiException $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function render()
    {
        $property = $this->propertyId ? $this->owned() : null;

        return view('livewire.partner.property-form', [
            'property' => $property,
            'images' => $property
                ? PropertyImage::where('property_id', $property->id)->orderBy('sort_order')->get()
                : collect(),
            'facilities' => app(FacilityRepositoryInterface::class)->all(),
        ]);
    }
}
