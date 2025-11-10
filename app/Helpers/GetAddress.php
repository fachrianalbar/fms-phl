<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetAddress
{
    // Function to get address from latitude and longitude using OpenStreetMap Nominatim
    public static function getAddress($lat, $long)
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$long}";

            $response = Http::withHeaders([
                'User-Agent' => 'YourAppName/1.0', // Nominatim requires a valid User-Agent
            ])->get($url);

            if ($response->successful()) {
                return $response->json('display_name', null);
            }

            return null;
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to fetch address from Nominatim API', ['error' => $e->getMessage()]);

            throw new \Exception('Failed to fetch address', 500);
        }
    }
}
