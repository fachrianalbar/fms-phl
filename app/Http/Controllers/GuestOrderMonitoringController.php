<?php

namespace App\Http\Controllers;

use App\Helpers\DecodedDirectionPolyline;
use App\Helpers\GenerateCode;
use App\Helpers\GetTokenHelper;
use App\Models\Operational\Order;
use App\Models\Operational\OrderTracking;
use App\Services\Master\MenuService;
use App\Services\Operational\OrderMonitoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class GuestOrderMonitoringController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(OrderMonitoringService $orderMonitoringSvc, MenuService $menuSvc)
    {
        $this->service = $orderMonitoringSvc;
        $this->title = "Guest Order Monitoring";
        $this->view = "guest.monitoring-order.";
    }
    public function index(Request $request)
    {

        $data = $this->service->getByShipmentNumber($request->shipmentNumber);

        if (!$data) {
            return redirect()->route('guest.home');
        }

        $truckLocation = OrderTracking::where('orderCode', $data->code)->orderByDesc('created_at')->first();

        $historyPosition = OrderTracking::where('orderCode', $data->code)->orderBy('created_at')->get();

        $originLatitude = $data->route->originLocation->latitude;
        $originLongitude = $data->route->originLocation->longitude;

        $destinationLatitude = $data->route->destinationLocation->latitude;
        $destinationLongitude = $data->route->destinationLocation->longitude;

        $truckLatitude = $originLatitude;
        $truckLongitude = $originLongitude;


        // $truckLatitude = '-6.1606';
        // $truckLongitude = '106.78092';
        if ($truckLocation) {
            $truckLatitude = $truckLocation->latitude;
            $truckLongitude = $truckLocation->longitude;

            // $truckLatitude = '-6.8582959';
            // $truckLongitude = '109.1472147';
        }

        $directionApi = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
            'origin' => $originLatitude . ',' . $originLongitude,
            'destination' => $destinationLatitude . ',' . $destinationLongitude,
            'key' => 'AIzaSyAqbjxyIJhHovu-x_Pn9dPlDilIKWTMYpE'
        ]);

        $direction = $directionApi->json();

        if (!isset($direction['routes'][0])) {
            return redirect()->route('guest.home')->with('fail', 'Data origin or destinatin latitude or longitude is invalid');
        }

        // Ambil data dari Directions API
        $totalDistanceKm = $direction['routes'][0]['legs'][0]['distance']['value'] / 1000;


        // Ambil sisa perjalanan dari DB (dengan tipe varchar + teks)
        $currentDistanceKm = $data->distance
            ? floatval(str_replace(['km', ' '], '', $data->distance))
            : $totalDistanceKm;

        // Hitung progress
        $distanceCovered = max(0, $totalDistanceKm - $currentDistanceKm);
        $distancePercentage = $totalDistanceKm > 0
            ? round(($distanceCovered / $totalDistanceKm) * 100)
            : 0;

        // Decode polyline
        $encodedPolyline = $direction['routes'][0]['overview_polyline']['points'];
        $decodedPolyline = DecodedDirectionPolyline::decoded($encodedPolyline);
        $formattedPolyline = array_map(fn($point) => ['lat' => $point[0], 'lng' => $point[1]], $decodedPolyline);

        if (in_array($data->status, [2, 3])) {
            $distancePercentage = 100;
        }

        return view($this->view . 'index')
            ->with('title', $this->title)
            ->with('truckLatitude', $truckLatitude)
            ->with('truckLongitude', $truckLongitude)
            ->with('originLatitude', $originLatitude)
            ->with('originLongitude', $originLongitude)
            ->with('destinationLatitude', $destinationLatitude)
            ->with('destinationLongitude', $destinationLongitude)
            ->with('historyPosition', $historyPosition)
            ->with('encodedPolyline', $encodedPolyline)
            ->with('decodedPolyline', $formattedPolyline)
            ->with('distance', $data->distance)
            ->with('duration', $data->estimatedTime)
            ->with('distancePercentage', $distancePercentage)
            ->with('data', $data);;
    }

    function isTruckOnRoute($lat, $lng, $encodedPolyline, $maxDistance = 100)
    {
        $decodedPolyline = DecodedDirectionPolyline::decoded($encodedPolyline);

        foreach ($decodedPolyline as $point) {
            $distance = $this->haversineDistanceInMeters($lat, $lng, $point[0], $point[1]);

            if ($distance < $maxDistance) {
                return true;
            }
        }

        return false;
    }


    function haversineDistanceInMeters($lat1, $lon1, $lat2, $lon2)
    {
        // Jari-jari bumi dalam meter
        $earthRadius = 6371000;

        // Konversi derajat ke radian
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($dLon / 2)
            * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Jarak dalam meter
        $distance = $earthRadius * $c;

        return $distance;
    }

    public function guestorderShipment($shipment)
    {
        return $this->service->getByShipmentNumber($shipment);
    }

    public function guestOrderShipmentSuggestion(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = Order::where('shipmentNumber', 'LIKE', '%' . $query . '%')->limit(10)->whereIn('status', [0, 1, 2])
            ->pluck('shipmentNumber');

        return response()->json($results);
    }
}
