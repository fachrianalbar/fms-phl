<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\DecodedDirectionPolyline;
use App\Helpers\GenerateCode;
use App\Helpers\GetAddress;
use App\Helpers\GetTokenHelper;
use App\Helpers\SendNotif;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailTruckNotification;
use App\Jobs\SendTelegramNotification;
use App\Jobs\SendWaNotification;
use App\Models\Operational\Order;
use App\Models\Operational\OrderTracking;
use App\Services\Operational\OrderMonitoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Operational\TelegramUser;
use Illuminate\Support\Str;



class OrderMonitoringController extends Controller
{
    protected $service;
    protected $title;
    protected $view;

    public function __construct(OrderMonitoringService $orderMonitoringSvc)
    {
        $this->service = $orderMonitoringSvc;
        $this->title = "Order Monitoring";
        $this->view = "operational.monitoring-order.";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        // Ambil posisi terakhir truk
        $truckLocation = OrderTracking::where('orderCode', $data->code)->orderByDesc('created_at')->first();
        $historyPosition = OrderTracking::where('orderCode', $data->code)->orderBy('created_at')->get();

        $originLatitude = $data->route->originLocation->latitude;
        $originLongitude = $data->route->originLocation->longitude;
        $destinationLatitude = $data->route->destinationLocation->latitude;
        $destinationLongitude = $data->route->destinationLocation->longitude;

        $truckLatitude = $truckLocation ? $truckLocation->latitude : $originLatitude;
        $truckLongitude = $truckLocation ? $truckLocation->longitude : $originLongitude;

        // Panggil Directions API untuk total rute (jarak dan waktu awal)
        $directionApi = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
            'origin' => $originLatitude . ',' . $originLongitude,
            'destination' => $destinationLatitude . ',' . $destinationLongitude,
            'key' => env('GOOGLE_MAPS_KEY')
        ]);

        $direction = $directionApi->json();

        if (!isset($direction['routes'][0])) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data origin or destinatin latitude or longitude is invalid');
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

        return view($this->view . 'show')
            ->with('view', $this->view)
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
            ->with('data', $data);
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })
                ->editColumn('orderStatus.name', function ($row) {
                    $status = '';

                    if (isset($row->orderStatus->name)) {
                        $status = $row->orderStatus->name;
                    }

                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $finish_order = '';

                    if ($row->status == 2) {
                        $finish_order = '<li class="mx-2"> <a href="javascript:finishOrder(\'' . $row->id . '\')"><i class="icon-check-box"></i></a></li>';
                    }
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'show', $row->id) . '"><i class="icon-eye"></i></a></li>
                                        ' . $finish_order . '
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'route.originLocation.name', 'route.destinationLocation.name', 'orderDate', 'orderStatus.name', 'fleet.plateNumber'])
                ->toJson();
        }
    }

    public function orderTracking()
    {
        $orderData = Order::whereNotIn('status', [2, 3])->get();

        foreach ($orderData as $item) {
            try {

                DB::beginTransaction();

                $data = Order::where('id', $item->id)->with([
                    'route',
                    'route.originLocation',
                    'route.destinationLocation',
                    'fleet'
                ])->first();

                $response = Http::get(env('API_TOTAL_KILAT') . 'deviceHistory', [
                    'grant_type' => 'totalkilatgps',
                    'access_token' => GetTokenHelper::getToken(),
                    'device_name' => $data->fleet->deviceName,
                    'start_time' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::now()->format('Y-m-d H:i:s')
                ]);

                $dataApi = $response->json();

                if (!empty($dataApi) && isset($dataApi[0]) && is_array($dataApi[0]) && count($dataApi[0]) > 0) {
                    $latestData = end($dataApi[0]);

                    if (count($dataApi[0]) > 0) {
                        if ($latestData) {
                            OrderTracking::create([
                                'code' => GenerateCode::generateCode('FMSOT'),
                                'latitude' => $latestData['lat'],
                                'longitude' => $latestData['lon'],
                                'orderCode' => $data->code
                            ]);
                        }
                    }
                } else {
                    Log::warning("GPS data kosong untuk order ID {$data->id} ({$data->code})");
                    // continue;
                }


                $truckLocationCount = OrderTracking::where('orderCode', $data->code)->count();

                $truckLocation = OrderTracking::where('orderCode', $data->code)->orderByDesc('created_at')->first();

                $originLatitude = $data->route->originLocation->latitude;
                $originLongitude = $data->route->originLocation->longitude;

                $destinationLatitude = $data->route->destinationLocation->latitude;
                $destinationLongitude = $data->route->destinationLocation->longitude;

                $origins = [
                    "{$originLatitude},{$originLongitude}"
                ];

                $truckLatitude = $originLatitude;
                $truckLongitude = $originLongitude;
                if ($truckLocation) {
                    $truckLatitude = $truckLocation->latitude;
                    $truckLongitude = $truckLocation->longitude;

                    $radius = $this->haversineDistanceInMeters(
                        $truckLatitude,
                        $truckLongitude,
                        $destinationLatitude,
                        $destinationLongitude
                    );

                    if ($radius <= 100) {
                        Order::where('id', $data->id)->update([
                            'status' => 2,
                            'distance' => null,
                            'estimatedTime' => null
                        ]);
                    }

                    $origins = [
                        "{$originLatitude},{$originLongitude}",
                        "{$truckLatitude},{$truckLongitude}",
                    ];
                }

                $directionApi = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
                    'origin' => $originLatitude . ',' . $originLongitude,
                    'destination' => $destinationLatitude . ',' . $destinationLongitude,
                    'key' => 'AIzaSyAqbjxyIJhHovu-x_Pn9dPlDilIKWTMYpE'
                ]);

                $direction = $directionApi->json();

                if (isset($direction['routes'][0])) {
                    $encodedPolyline = $direction['routes'][0]['overview_polyline']['points'];

                    $onRoute = $this->isTruckOnRoute($truckLatitude, $truckLongitude, $encodedPolyline, 100);

                    if (!$onRoute) {
                        if ($truckLocationCount > 1) {
                            $truckData['email'] = $data->customer->email;
                            $truckData['name'] = $data->customer->name;
                            $truckData['plateNumber'] = $data->fleet->plateNumber;
                            $truckData['dateTime'] = $truckLocation->created_at->format('d-m-Y H:i');
                            $truckData['latitude'] = $truckLocation->latitude;
                            $truckData['longitude'] = $truckLocation->longitude;
                            $truckData['address'] = GetAddress::getAddress($truckLocation->latitude, $truckLocation->longitude);
                            $truckData['url'] = env('APP_URL') . '/' . 'operational/monitoring-order/' . $data->id;
                            if (isset($data->customer->email)) {
                                SendEmailTruckNotification::dispatch($truckData);
                            }

                            if (isset($data->customer->phone)) {
                                $messageWa = "⚠️ *Peringatan! Truk Keluar dari Jalur* ⚠️\n\n"
                                    . "Halo *{$data->customer->name}*,\n\n"
                                    . "Kami ingin memberitahu Anda bahwa kendaraan berikut telah keluar dari jalur yang ditentukan:\n\n"
                                    . "🚛 *Nomor Truk:* {$data->fleet->plateNumber}\n"
                                    . "📍 *Lokasi Terakhir:* {$truckData['address']}\n"
                                    . "🕒 *Waktu Terdeteksi:* {$truckData['dateTime']}\n"
                                    . "Silakan segera periksa atau hubungi pihak terkait untuk memastikan keadaan kendaraan.\n\n"
                                    . "🔍 Klik link berikut untuk melihat detail monitoring:\n"
                                    . "{$truckData['url']}\n"  // <-- Tambahkan spasi kosong sebelum dan sesudah link
                                    . "Best Regards,\n"
                                    . "TOTAL KILAT SOLUTION";

                                SendWaNotification::dispatch($data->customer->phone, $messageWa);

                                $telegramUser = TelegramUser::whereRaw('LOWER(username) = ?', [strtolower($data->customer->telegramUsername)])->first();

                                if ($telegramUser) {
                                    $messageTele = "⚠️ **Peringatan! Truk Keluar dari Jalur** ⚠️\n\n"
                                        . "Halo **{$data->customer->name}**,\n\n"
                                        . "Kami ingin memberitahu Anda bahwa kendaraan berikut telah keluar dari jalur yang ditentukan:\n\n"
                                        . "🚛 **Nomor Truk:** {$data->fleet->plateNumber}\n"
                                        . "📍 **Lokasi Terakhir:** {$truckData['address']}\n"
                                        . "🕒 **Waktu Terdeteksi:** {$truckData['dateTime']}\n"
                                        . "Silakan segera periksa atau hubungi pihak terkait untuk memastikan keadaan kendaraan.\n\n"
                                        . "🔍 Klik link berikut untuk melihat detail monitoring:\n"
                                        . "{$truckData['url']}\n"
                                        . "Best Regards,\n"
                                        . "TOTAL KILAT SOLUTION";

                                    SendTelegramNotification::dispatch($telegramUser->chatId, $messageTele);
                                }
                            }
                        }
                    }
                }


                $destinations = [
                    "{$destinationLatitude},{$destinationLongitude}"
                ];

                $originsString = implode('|', $origins);
                $destinationsString = implode('|', $destinations);

                $order = Order::where('id', $item->id)->first();

                if (in_array($order->status, [0, 1])) {
                    $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                        'origins'      => $originsString,
                        'destinations' => $destinationsString,
                        'key'          => 'AIzaSyAqbjxyIJhHovu-x_Pn9dPlDilIKWTMYpE',
                    ]);

                    $dataMap = $response->json();

                    if (isset($dataMap['rows'][1]['elements'][0])) {
                        $distance = $dataMap['rows'][1]['elements'][0]['distance']['text'];
                        $duration = $dataMap['rows'][1]['elements'][0]['duration']['text'];

                        $order = Order::where('id', $item->id)->first();

                        if (!in_array($order->status, [2, 3])) {
                            Order::where('id', $data->id)->update([
                                'distance' => $distance,
                                'estimatedTime' => $duration,
                                'status' => 1
                            ]);
                        }
                    } else {
                        $distance = $dataMap['rows'][0]['elements'][0]['distance']['text'];
                        $duration = $dataMap['rows'][0]['elements'][0]['duration']['text'];
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Gagal tracking order ID {$item->id} - Error: " . $e->getMessage());
                continue;
            }
        }

        return response()->json([
            "status" => true,
            "message" => "Fetch order tracking was successful"
        ]);
    }

    public function finishOrder(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $this->service->finishOrder($id);

        return redirect()->route($this->view . 'index')->with('success', 'Finish order was successfull');
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
}
