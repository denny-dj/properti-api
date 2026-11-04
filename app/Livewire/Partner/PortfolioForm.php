<?php

namespace App\Livewire\Partner;

use App\Enums\ArchitecturalStyle;
use App\Enums\PortfolioCategory;
use App\Enums\PortfolioStatus;
use App\Exceptions\ApiException;
use App\Services\ArchitectPortfolioService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Formulir portofolio (baru/ubah) beserta foto. */
class PortfolioForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $portfolioId = null;

    public string $title = '';

    public string $category = 'full_house';

    public string $architectural_style = 'modern';

    public string $estimated_rab = '';

    public string $area_sqm = '';

    public string $description = '';

    public bool $is_ready_to_build = false;

    public string $blueprint_price = '';

    /** Satu berkas per baris. */
    public string $deliverables = '';

    public string $status = 'draft';

    public array $photos = [];

    public ?string $notice = null;

    public function mount(?int $portfolio = null): void
    {
        if ($portfolio === null) {
            return;
        }

        try {
            $model = app(ArchitectPortfolioService::class)->findOwnedOrFail(auth()->user(), $portfolio);
        } catch (ApiException) {
            abort(404);
        }

        $this->portfolioId = $model->id;
        $this->fill([
            'title' => $model->title,
            'category' => $model->category->value,
            'architectural_style' => $model->architectural_style->value,
            'estimated_rab' => (string) $model->estimated_rab,
            'area_sqm' => (string) (float) $model->area_sqm,
            'description' => (string) $model->description,
            'is_ready_to_build' => $model->is_ready_to_build,
            'blueprint_price' => $model->blueprint_price !== null ? (string) $model->blueprint_price : '',
            'deliverables' => implode("\n", $model->blueprint_deliverables ?? []),
            'status' => $model->status->value,
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::enum(PortfolioCategory::class)],
            'architectural_style' => ['required', Rule::enum(ArchitecturalStyle::class)],
            'estimated_rab' => ['required', 'integer', 'min:1000000', 'max:100000000000'],
            'area_sqm' => ['required', 'numeric', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_ready_to_build' => ['boolean'],
            'blueprint_price' => ['nullable', 'integer', 'min:100000', 'max:100000000000'],
            'deliverables' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(PortfolioStatus::class)],
            'photos' => ['array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    protected function messages(): array
    {
        return [
            'estimated_rab.min' => 'Estimasi RAB minimal Rp 1.000.000.',
            'blueprint_price.min' => 'Harga paket minimal Rp 100.000.',
            'photos.*.image' => 'Berkas harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 4 MB.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'judul',
            'estimated_rab' => 'estimasi RAB',
            'area_sqm' => 'luas bangunan',
            'blueprint_price' => 'harga paket',
            'deliverables' => 'daftar berkas',
            'architectural_style' => 'gaya desain',
        ];
    }

    public function save()
    {
        $this->resetErrorBag('form');

        $this->estimated_rab = preg_replace('/\D/', '', $this->estimated_rab) ?? '';
        $this->blueprint_price = preg_replace('/\D/', '', $this->blueprint_price) ?? '';
        $this->area_sqm = str_replace(',', '.', trim($this->area_sqm));

        $this->validate();

        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/', $this->deliverables) ?: []),
            fn (string $line) => $line !== '',
        ));

        if ($this->is_ready_to_build) {
            if ($this->blueprint_price === '') {
                $this->addError('blueprint_price', 'Harga paket wajib diisi untuk desain siap bangun.');

                return null;
            }

            if ($lines === [] || count($lines) > 10 || collect($lines)->contains(fn (string $l) => mb_strlen($l) > 100)) {
                $this->addError('deliverables', 'Tulis 1-10 berkas yang diserahkan, satu per baris (maks. 100 karakter).');

                return null;
            }
        }

        $data = [
            'title' => trim($this->title),
            'category' => $this->category,
            'architectural_style' => $this->architectural_style,
            'estimated_rab' => (int) $this->estimated_rab,
            'area_sqm' => (float) $this->area_sqm,
            'description' => trim($this->description) !== '' ? trim($this->description) : null,
            'is_ready_to_build' => $this->is_ready_to_build,
            'blueprint_price' => $this->is_ready_to_build ? (int) $this->blueprint_price : null,
            'blueprint_deliverables' => $this->is_ready_to_build ? $lines : null,
            'status' => $this->status,
        ];

        $service = app(ArchitectPortfolioService::class);

        try {
            if ($this->portfolioId === null) {
                $service->create(auth()->user(), $data, $this->photos);
                session()->flash('status', 'Portofolio dibuat.');

                return $this->redirect(route('partner.portfolios.index'));
            }

            $service->update(auth()->user(), $this->portfolioId, $data, $this->photos);
            $this->reset('photos');
            $this->notice = 'Perubahan disimpan.';
        } catch (ApiException $e) {
            $this->addError('form', $e->getMessage());
        }

        return null;
    }

    public function makeCover(int $imageId): void
    {
        $this->resetErrorBag('photos');

        if ($this->portfolioId === null) {
            return;
        }

        try {
            app(ArchitectPortfolioService::class)->setCover(auth()->user(), $this->portfolioId, $imageId);
        } catch (ApiException $e) {
            $this->addError('photos', $e->getMessage());
        }
    }

    public function deleteImage(int $imageId): void
    {
        $this->resetErrorBag('photos');

        if ($this->portfolioId === null) {
            return;
        }

        try {
            app(ArchitectPortfolioService::class)->update(auth()->user(), $this->portfolioId, [], [], [$imageId]);
        } catch (ApiException $e) {
            $this->addError('photos', $e->getMessage());
        }
    }

    public function removeNewPhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function render()
    {
        $images = $this->portfolioId
            ? app(ArchitectPortfolioService::class)->findOwnedOrFail(auth()->user(), $this->portfolioId)->images
            : collect();

        return view('livewire.partner.portfolio-form', [
            'images' => $images,
            'categories' => PortfolioCategory::cases(),
            'styles' => ArchitecturalStyle::cases(),
        ]);
    }
}
