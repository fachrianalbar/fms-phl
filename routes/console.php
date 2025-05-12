<?php

use App\Helpers\GenerateCode;
use App\Helpers\GetTokenHelper;
use App\Models\Operational\Order;
use App\Models\Operational\OrderTracking;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Schedule::call(function () {
//     $orderData = Order::whereNotIn('status', [2, 3])->get();

//     foreach ($orderData as $item) {
//         try {
//             DB::beginTransaction();

//             $data = Order::where('id', $item->id)->with([
//                 'route',
//                 'route.originLocation',
//                 'route.destinationLocation',
//                 'fleet'
//             ])->first();

//             $response = Http::get(env('API_TOTAL_KILAT') . 'deviceHistory', [
//                 'grant_type' => 'totalkilatgps',
//                 'access_token' => GetTokenHelper::getToken(),
//                 'device_name' => $data->fleet->deviceName,
//                 'start_time' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
//                 'end_time' => Carbon::now()->format('Y-m-d H:i:s')
//             ]);

//             $dataApi = $response->json();

//             $latestData = end($dataApi[0]);

//             OrderTracking::create([
//                 'code' => GenerateCode::generateCode('FMSOT'),
//                 'latitude' => $latestData['lat'],
//                 'longitude' => $latestData['lon'],
//                 'orderCode' => $data->code
//             ]);

//             $truckLocation = OrderTracking::where('orderCode', $data->code)->orderByDesc('created_at')->first();

//             $originLatitude = $data->route->originLocation->latitude;
//             $originLongitude = $data->route->originLocation->longitude;

//             $destinationLatitude = $data->route->destinationLocation->latitude;
//             $destinationLongitude = $data->route->destinationLocation->longitude;

//             $origins = [
//                 "{$originLatitude},{$originLongitude}"
//             ];

//             $truckLatitude = $originLatitude;
//             $truckLongitude = $originLongitude;
//             if ($truckLocation) {
//                 $truckLatitude = $truckLocation->latitude;
//                 $truckLongitude = $truckLocation->longitude;

//                 // $truckLatitude = '-6.8582959';
//                 // $truckLongitude = '109.1472147';

//                 $radius = haversineDistanceInMeters(
//                     $truckLatitude,
//                     $truckLongitude,
//                     $destinationLatitude,
//                     $destinationLongitude
//                 );

//                 if ($radius <= 1000) {
//                     Order::where('id', $data->id)->update([
//                         'status' => 2,
//                         'distance' => null,
//                         'estimatedTime' => null
//                     ]);
//                 }

//                 $origins = [
//                     "{$originLatitude},{$originLongitude}",
//                     "{$truckLatitude},{$truckLongitude}",
//                 ];
//             }

//             $destinations = [
//                 "{$destinationLatitude},{$destinationLongitude}"
//             ];

//             $originsString = implode('|', $origins);
//             $destinationsString = implode('|', $destinations);

//             $order = Order::where('id', $item->id)->first();

//             if (in_array($order->status, [0, 1])) {
//                 $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
//                     'origins'      => $originsString,
//                     'destinations' => $destinationsString,
//                     'key'          => 'AIzaSyAqbjxyIJhHovu-x_Pn9dPlDilIKWTMYpE',
//                 ]);

//                 $dataMap = $response->json();

//                 if (isset($dataMap['rows'][1]['elements'][0])) {
//                     $distance = $dataMap['rows'][1]['elements'][0]['distance']['text'];
//                     $duration = $dataMap['rows'][1]['elements'][0]['duration']['text'];

//                     $order = Order::where('id', $item->id)->first();

//                     if (!in_array($order->status, [2, 3])) {
//                         Order::where('id', $data->id)->update([
//                             'distance' => $distance,
//                             'estimatedTime' => $duration,
//                             'status' => 1
//                         ]);
//                     }
//                 } else {
//                     $distance = $dataMap['rows'][0]['elements'][0]['distance']['text'];
//                     $duration = $dataMap['rows'][0]['elements'][0]['duration']['text'];
//                 }
//             }

//             DB::commit();
//         } catch (\Exception $e) {
//             Log::error("Gagal memproses Order ID {$item->id}: {$e->getMessage()}");
//             continue;
//         }
//     }
// })->everySecond();

// function haversineDistanceInMeters($lat1, $lon1, $lat2, $lon2)
// {
//     // Jari-jari bumi dalam meter
//     $earthRadius = 6371000;

//     // Konversi derajat ke radian
//     $dLat = deg2rad($lat2 - $lat1);
//     $dLon = deg2rad($lon2 - $lon1);

//     $a = sin($dLat / 2) * sin($dLat / 2)
//         + cos(deg2rad($lat1))
//         * cos(deg2rad($lat2))
//         * sin($dLon / 2)
//         * sin($dLon / 2);

//     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

//     // Jarak dalam meter
//     $distance = $earthRadius * $c;

//     return $distance;
// }
