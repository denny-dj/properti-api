<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Menyimpan foto profil/logo ke disk public setelah diproses ulang dengan GD:
 *  - metadata EXIF (termasuk koordinat GPS ponsel) ikut terhapus,
 *  - orientasi foto dikoreksi, ukuran dikecilkan,
 *  - nama berkas diacak (UUID).
 * Bila ekstensi GD tidak tersedia, berkas disimpan apa adanya.
 */
class ImageUploader
{
    private const MAX_PIXELS = 40_000_000;

    private const ALLOWED_DIRECTORIES = ['avatars', 'logos'];

    /** Foto persegi (potong tengah) sebagai JPEG. Untuk avatar. */
    public function storeSquare(UploadedFile $file, string $directory, int $size = 512): string
    {
        $this->assertDirectory($directory);

        if (! $this->canProcess()) {
            return (string) $file->store($directory, 'public');
        }

        $source = $this->decode($file);
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);

        $canvas = imagecreatetruecolor($size, $size);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); // latar putih untuk PNG transparan
        imagecopyresampled($canvas, $source, 0, 0, intdiv($width - $side, 2), intdiv($height - $side, 2), $size, $size, $side, $side);

        return $this->put($directory, 'jpg', $this->encode(fn () => imagejpeg($canvas, null, 85)));
    }

    /** Gambar dengan rasio asli (maks. $maxWidth x $maxHeight) sebagai PNG transparan. Untuk logo. */
    public function storeContained(UploadedFile $file, string $directory, int $maxWidth = 600, int $maxHeight = 600): string
    {
        $this->assertDirectory($directory);

        if (! $this->canProcess()) {
            return (string) $file->store($directory, 'public');
        }

        $source = $this->decode($file);
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $this->put($directory, 'png', $this->encode(fn () => imagepng($canvas, null, 6)));
    }

    /** Hapus berkas lama. Hanya path di dalam folder yang dikelola uploader ini yang boleh dihapus. */
    public function delete(?string $path): void
    {
        if (blank($path) || str_starts_with($path, 'http')) {
            return;
        }

        foreach (self::ALLOWED_DIRECTORIES as $directory) {
            if (str_starts_with($path, $directory.'/') && ! str_contains($path, '..')) {
                Storage::disk('public')->delete($path);

                return;
            }
        }
    }

    // ------------------------------------------------------------------

    private function canProcess(): bool
    {
        return function_exists('imagecreatefromstring') && function_exists('imagejpeg') && function_exists('imagepng');
    }

    private function assertDirectory(string $directory): void
    {
        if (! in_array($directory, self::ALLOWED_DIRECTORIES, true)) {
            throw new InvalidArgumentException('Folder unggahan tidak diizinkan.');
        }
    }

    /** @return \GdImage */
    private function decode(UploadedFile $file)
    {
        $data = $file->get();
        $info = @getimagesizefromstring($data);

        if (! $info || ($info[0] * $info[1]) > self::MAX_PIXELS) {
            throw new InvalidArgumentException('Gambar tidak valid atau resolusinya terlalu besar.');
        }

        $image = @imagecreatefromstring($data);

        if ($image === false) {
            throw new InvalidArgumentException('Gambar tidak dapat dibaca.');
        }

        // Koreksi orientasi foto ponsel (EXIF) sebelum metadata dibuang.
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($data));
            $angle = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            if ($angle !== 0 && ($rotated = imagerotate($image, $angle, 0)) !== false) {
                $image = $rotated;
            }
        }

        return $image;
    }

    private function encode(callable $writer): string
    {
        ob_start();
        $writer();

        return (string) ob_get_clean();
    }

    private function put(string $directory, string $extension, string $bytes): string
    {
        $path = $directory.'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }
}
