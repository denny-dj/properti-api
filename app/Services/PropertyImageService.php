<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Repositories\Contracts\PropertyImageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PropertyImageService
{
    public const MAX_IMAGES = 10;

    public function __construct(private PropertyImageRepositoryInterface $images)
    {
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, PropertyImage>
     */
    public function upload(Property $property, array $files): Collection
    {
        if ($this->images->countForProperty($property->id) + count($files) > self::MAX_IMAGES) {
            throw new ApiException('Maksimal '.self::MAX_IMAGES.' foto per properti.', 422);
        }

        $hasCover = $this->images->hasCover($property->id);
        $order = $this->images->maxSortOrder($property->id);
        $storedPaths = [];

        try {
            DB::transaction(function () use ($property, $files, $hasCover, &$order, &$storedPaths) {
                foreach (array_values($files) as $index => $file) {
                    $path = $file->store("properties/{$property->id}", 'public');
                    $storedPaths[] = $path;

                    $this->images->create([
                        'property_id' => $property->id,
                        'path' => $path,
                        'is_cover' => ! $hasCover && $index === 0,
                        'sort_order' => ++$order,
                    ]);
                }
            });
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return $property->images()->get();
    }

    public function delete(PropertyImage $image): void
    {
        DB::transaction(function () use ($image) {
            $propertyId = $image->property_id;
            $wasCover = $image->is_cover;
            $path = $image->path;

            $this->images->delete($image);

            if ($wasCover && ($next = $this->images->firstForProperty($propertyId))) {
                $this->images->update($next, ['is_cover' => true]);
            }

            Storage::disk('public')->delete($path);
        });
    }

    public function setCover(PropertyImage $image): PropertyImage
    {
        return DB::transaction(function () use ($image) {
            $this->images->resetCover($image->property_id);

            return $this->images->update($image, ['is_cover' => true]);
        });
    }
}
