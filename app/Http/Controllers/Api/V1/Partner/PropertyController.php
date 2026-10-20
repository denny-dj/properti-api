<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyIndexRequest;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Requests\Property\UploadPropertyImagesRequest;
use App\Http\Resources\PropertyImageResource;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\PropertyImageService;
use App\Services\PropertyService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Manajemen listing milik partner (seller) yang sedang login. */
class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $properties,
        private PropertyImageService $images,
    ) {
    }

    public function index(PropertyIndexRequest $request): JsonResponse
    {
        $paginator = $this->properties->listForPartner($request->user(), $request->validated());

        return ApiResponse::success(PropertyResource::collection($paginator), 'Daftar properti Anda.');
    }

    public function show(Property $property): JsonResponse
    {
        Gate::authorize('view', $property);

        return ApiResponse::success(
            PropertyResource::make($this->properties->loadDetail($property)),
            'Detail properti.',
        );
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->properties->create($request->user(), $request->validated());

        return ApiResponse::success(
            PropertyResource::make($property),
            'Properti dibuat sebagai draft. Unggah foto lalu ajukan untuk diverifikasi.',
            201,
        );
    }

    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        Gate::authorize('update', $property);

        return ApiResponse::success(
            PropertyResource::make($this->properties->update($property, $request->validated())),
            'Properti diperbarui.',
        );
    }

    public function destroy(Property $property): JsonResponse
    {
        Gate::authorize('delete', $property);

        $this->properties->delete($property);

        return ApiResponse::success(null, 'Properti dihapus.');
    }

    public function submit(Property $property): JsonResponse
    {
        Gate::authorize('update', $property);

        return ApiResponse::success(
            PropertyResource::make($this->properties->submit($property)),
            'Properti diajukan dan menunggu verifikasi administrator.',
        );
    }

    public function uploadImages(UploadPropertyImagesRequest $request, Property $property): JsonResponse
    {
        Gate::authorize('update', $property);

        $images = $this->images->upload($property, $request->file('images'));

        return ApiResponse::success(PropertyImageResource::collection($images), 'Foto berhasil diunggah.', 201);
    }

    public function destroyImage(Request $request, Property $property, PropertyImage $image): JsonResponse
    {
        Gate::authorize('update', $property);

        $this->images->delete($image);

        return ApiResponse::success(null, 'Foto dihapus.');
    }

    public function setCover(Property $property, PropertyImage $image): JsonResponse
    {
        Gate::authorize('update', $property);

        return ApiResponse::success(
            PropertyImageResource::make($this->images->setCover($image)),
            'Foto sampul diperbarui.',
        );
    }
}
