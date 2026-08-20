<?php

namespace App\Console\Commands;

use App\Models\Produk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateDummyProductImages extends Command
{
    protected $signature = 'products:generate-dummy-images {--force : Generate a new image even when the product already has one}';
    protected $description = 'Generate placeholder PNG images for products during development';

    public function handle(): int
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->error('PHP GD extension is required.');
            return self::FAILURE;
        }

        $dir = 'public/produk';
        Storage::makeDirectory($dir);
        $count = 0;

        foreach (Produk::withoutGlobalScopes()->get() as $product) {
            if (!$this->option('force') && $product->gambar && Storage::exists('public/' . $product->gambar)) continue;

            $image = imagecreatetruecolor(400, 400);
            $background = imagecolorallocate($image, random_int(50, 200), random_int(50, 200), random_int(50, 200));
            imagefill($image, 0, 0, $background);
            $text = imagecolorallocate($image, 255, 255, 255);
            $initials = strtoupper(substr(trim($product->nama_produk), 0, 2));
            $small = imagecreatetruecolor(40, 40);
            imagefill($small, 0, 0, $background);
            imagestring($small, 5, max(0, (40 - strlen($initials) * imagefontwidth(5)) / 2), 15, $initials, $text);
            imagecopyresized($image, $small, 0, 0, 0, 0, 400, 400, 40, 40);

            $filename = 'produk/' . Str::slug($product->nama_produk) . '-' . time() . '.png';
            imagepng($image, storage_path('app/public/' . $filename));
            imagedestroy($image);
            imagedestroy($small);
            $product->update(['gambar' => $filename]);
            $count++;
            $this->line("Generated image for: {$product->nama_produk}");
        }

        $this->info("Generated {$count} product image(s).");
        return self::SUCCESS;
    }
}
