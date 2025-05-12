<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetAddress
{
    // Function to get address from latitude and longitude
    public static function getAddress($lat, $long)
    {
        $googleMapsApiKey = env('GOOGLE_MAPS_API_KEY'); // Make sure to add your API key in the .env file

        try {
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$long}&key={$googleMapsApiKey}&sensor=false";

            $response = Http::get($url);

            // Check if the request was successful and there are results
            if ($response->successful() && $response->json('status') !== 'ZERO_RESULTS' && $response->json('status') !== 'INVALID_REQUEST') {
                return $response->json('results.0.formatted_address', null);
            }

            return null;
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to fetch address from Google Maps API', ['error' => $e->getMessage()]);

            throw new \Exception('Failed to fetch address', 500);
        }
    }
}
