<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicTenant;

class ManifestController extends Controller
{
    use ResolvesPublicTenant;

    public function show()
    {
        $yayasan = $this->currentYayasan();

        $name = $yayasan?->nama ?? "Qinara App";

        $icons = [
            ["src" => "/icons/icon-192.png", "sizes" => "192x192", "type" => "image/png"],
            ["src" => "/icons/icon-512.png", "sizes" => "512x512", "type" => "image/png"],
        ];

        if ($yayasan?->logo) {
            $logoUrl = \Storage::disk('r2-public')->url($yayasan->logo);
            $icons = [
                ["src" => $logoUrl, "sizes" => "192x192", "type" => "image/png"],
                ["src" => $logoUrl, "sizes" => "512x512", "type" => "image/png"],
            ];
        }

        return response()->json([
            "name" => $name,
            "short_name" => $name,
            "start_url" => "/",
            "display" => "standalone",
            "background_color" => "#ffffff",
            "theme_color" => "#00A39D",
            "icons" => $icons,
        ])->header("Content-Type", "application/manifest+json");
    }
}
