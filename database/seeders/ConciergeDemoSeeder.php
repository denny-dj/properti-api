<?php

namespace Database\Seeders;

use App\Enums\PartnerType;
use App\Models\AdBanner;
use App\Models\ArchitectPortfolio;
use App\Models\PortfolioImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Data demo untuk mencoba tampilan concierge (portofolio + banner). Aman dijalankan berulang.
 *   php artisan db:seed --class=ConciergeDemoSeeder
 */
class ConciergeDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Seeder demo tidak boleh dijalankan di produksi.');

            return;
        }

        $architect = User::where('email', 'architect@example.com')->first()
            ?? User::factory()->partner(PartnerType::Architect)->create();

        $canDraw = function_exists('imagecreatetruecolor');

        if (! $canDraw) {
            $this->command?->warn('Ekstensi GD tidak aktif: foto demo dilewati.');
        }

        $rows = [
            ['Rumah Japandi 2 Lantai 120 m²', 'full_house', 'japandi', 600_000_000, 120, true, 5_000_000, [58, 93, 120]],
            ['Fasad Modern Tropis Ruko 3 Lantai', 'facade', 'modern', 350_000_000, 90, false, null, [37, 99, 235]],
            ['Interior Ruang Keluarga Minimalis', 'interior', 'minimalist', 180_000_000, 36, true, 2_500_000, [100, 116, 139]],
            ['Kitchen Set Industrial Dapur Terbuka', 'kitchen_set', 'industrial', 95_000_000, 12, false, null, [87, 83, 78]],
            ['Rumah Modern 1 Lantai Tipe 90', 'full_house', 'modern', 420_000_000, 90, true, 4_000_000, [21, 128, 61]],
            ['Interior Kamar Utama Japandi', 'interior', 'japandi', 120_000_000, 24, false, null, [180, 83, 9]],
        ];

        foreach ($rows as [$title, $category, $style, $rab, $area, $ready, $price, $rgb]) {
            $portfolio = ArchitectPortfolio::updateOrCreate(
                ['user_id' => $architect->id, 'title' => $title],
                [
                    'category' => $category,
                    'architectural_style' => $style,
                    'estimated_rab' => $rab,
                    'area_sqm' => $area,
                    'cost_per_sqm' => (int) round($rab / $area),
                    'description' => "Desain {$title} dengan pendekatan fungsional dan material yang mudah dirawat.\nCocok untuk lahan perkotaan dan dapat disesuaikan dengan kebutuhan keluarga.",
                    'is_ready_to_build' => $ready,
                    'blueprint_price' => $price,
                    'blueprint_deliverables' => $ready ? ['DED (AutoCAD)', 'Model 3D (SketchUp)', 'RAB (Excel)', 'Gambar kerja (PDF)'] : null,
                    'status' => 'published',
                ],
            );

            if ($canDraw && PortfolioImage::where('portfolio_id', $portfolio->id)->doesntExist()) {
                foreach ([0, 1] as $i) {
                    $path = "portfolios/{$portfolio->id}/demo-{$i}.jpg";
                    Storage::disk('public')->put($path, $this->image($title, 1200, 800, $rgb, $i));
                    PortfolioImage::create(['portfolio_id' => $portfolio->id, 'image_path' => $path, 'is_cover' => $i === 0]);
                }
            }
        }

        $banners = [
            ['Promo Developer Mitra: cicilan ringan', 'top_hero', [14, 116, 144]],
            ['Sorotan Arsitek Bulan Ini', 'architect_spotlight', [126, 34, 206]],
            ['Developer Pilihan: Cluster Hijau', 'featured_developer', [21, 128, 61]],
        ];

        foreach ($banners as [$title, $placement, $rgb]) {
            $banner = AdBanner::firstOrNew(['title' => $title]);

            if (! $banner->exists) {
                $path = 'banners/demo-'.md5($title).'.jpg';
                if ($canDraw) {
                    Storage::disk('public')->put($path, $this->image($title, 1200, 300, $rgb, 0));
                }
                $banner->fill([
                    'placement' => $placement,
                    'banner_image' => $path,
                    'target_url' => 'https://example.com/promo',
                    'is_active' => true,
                    'start_date' => now()->subDay()->toDateString(),
                    'end_date' => now()->addMonths(2)->toDateString(),
                ])->save();
            }

            $banner->fill($this->creative($title))->save();
        }

        $this->command?->info('Demo concierge siap: 6 portofolio dan 3 banner.');
    }

    /** Teks promo untuk hero (hanya bila kolomnya sudah dimigrasi). */
    private function creative(string $title): array
    {
        if (! Schema::hasColumn('ad_banners', 'subtitle')) {
            return [];
        }

        return match (true) {
            str_contains($title, 'cicilan') => [
                'subtitle' => 'Cluster siap huni dengan DP ringan dan pendampingan KPR dari tim concierge kami.',
                'price_label' => 'Mulai Rp 850 Jt · Unit terbatas',
                'badge_label' => 'Featured Developer',
            ],
            default => [],
        };
    }

    private function image(string $label, int $w, int $h, array $rgb, int $variant): string
    {
        $img = imagecreatetruecolor($w, $h);
        $shade = $variant === 0 ? 1.0 : 0.7;
        $bg = imagecolorallocate($img, (int) ($rgb[0] * $shade), (int) ($rgb[1] * $shade), (int) ($rgb[2] * $shade));
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 24, 24, $label, $white);
        imagestring($img, 3, 24, $h - 36, 'Foto demo', $white);

        ob_start();
        imagejpeg($img, null, 82);
        $data = (string) ob_get_clean();
        imagedestroy($img);

        return $data;
    }
}
