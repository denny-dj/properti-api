<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\AdBanner;
use App\Repositories\Contracts\AdBannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PublicContentService
{
    public function __construct(private AdBannerRepositoryInterface $banners)
    {
    }

    /** Halaman "Cara Kerja": 6 tahapan layanan arsitek/interior dengan pendampingan admin. */
    public function howItWorks(): array
    {
        return [
            'title' => 'Cara Kerja',
            'subtitle' => 'Dari konsultasi pertama sampai serah terima kunci, didampingi tim kami.',
            'steps' => [
                [
                    'step' => 1,
                    'key' => 'registration',
                    'title' => 'Registrasi',
                    'description' => 'Daftarkan akun klien dan verifikasi email Anda. Akun diperlukan untuk pembayaran dan memantau progres proyek.',
                    'options' => [],
                ],
                [
                    'step' => 2,
                    'key' => 'first_visit',
                    'title' => 'Kunjungan Pertama',
                    'description' => 'Pilih cara memulai. Tim admin kami mendampingi Anda lewat WhatsApp.',
                    'options' => [
                        ['code' => 'A', 'title' => 'Konsultasi Online', 'description' => 'Diskusi kebutuhan, anggaran, dan gaya desain lewat chat atau video.'],
                        ['code' => 'B', 'title' => 'Kunjungan Langsung / Survei Rumah', 'description' => 'Tim mengukur dan menilai kondisi lokasi sebelum penawaran.'],
                        ['code' => 'C', 'title' => 'Beli Desain Jadi Siap Bangun', 'description' => 'Paket gambar kerja (DED), model 3D, dan RAB Excel yang langsung bisa dipakai membangun.'],
                    ],
                ],
                [
                    'step' => 3,
                    'key' => 'design',
                    'title' => 'Desain',
                    'description' => 'Setelah Booking Fee/Tanda Jadi dibayar, arsitek mengerjakan gambar kerja dan 3D. Berkas final diserahkan lewat tautan cloud dan dana baru dilepas ke mitra setelah Anda mengonfirmasi penerimaan.',
                    'options' => [],
                ],
                [
                    'step' => 4,
                    'key' => 'production',
                    'title' => 'Produksi',
                    'description' => 'Fabrikasi material dan komponen di workshop mitra sesuai gambar kerja yang disetujui.',
                    'options' => [],
                ],
                [
                    'step' => 5,
                    'key' => 'installation',
                    'title' => 'Instalasi',
                    'description' => 'Pemasangan komponen di lokasi proyek oleh tim mitra dengan pengawasan.',
                    'options' => [],
                ],
                [
                    'step' => 6,
                    'key' => 'handover',
                    'title' => 'Serah Terima & Siap Huni',
                    'description' => 'Quality control akhir, perbaikan catatan, lalu serah terima kunci. Rumah siap huni.',
                    'options' => [],
                ],
            ],
            'payment_note' => 'Pembayaran Booking Fee/Tanda Jadi dilakukan lewat tautan resmi platform (Virtual Account, kartu kredit, atau e-wallet). Hindari transfer langsung ke rekening pribadi mitra agar transaksi Anda terlindungi.',
        ];
    }

    /** Banner aktif. Setiap penayangan menambah impression_count. */
    public function promotions(?string $placement = null): Collection
    {
        $banners = $this->banners->live($placement);
        $this->banners->incrementImpressions($banners->pluck('id')->all());

        return $banners;
    }

    /**
     * Satu banner untuk hero beranda (placement top_hero). Bila ada beberapa yang aktif, dipilih acak agar
     * tayangan adil antar pengiklan. Hanya banner yang benar-benar tampil yang dihitung impression-nya.
     */
    public function heroBanner(): ?AdBanner
    {
        $banners = $this->banners->live('top_hero');

        if ($banners->isEmpty()) {
            return null;
        }

        $banner = $banners->random();
        $this->banners->incrementImpressions([$banner->id]);

        return $banner;
    }

    /** Mencatat klik dan mengembalikan banner (untuk target_url). */
    public function trackClick(int $id): AdBanner
    {
        $banner = $this->banners->findLive($id) ?? throw new ApiException('Promosi tidak ditemukan atau sudah berakhir.', 404);
        $this->banners->incrementClicks($banner->id);

        return $banner;
    }
}
