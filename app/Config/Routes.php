<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// --- Rute API ---

// Rute Login
$routes->post('api/login', 'API\Auth::login');

// Rute Registrasi
$routes->post('api/register', 'API\User::create');


// Grup untuk endpoint API lainnya, sudah menggunakan namespace
$routes->group('api', ['namespace' => 'App\Controllers\API'], function (RouteCollection $routes) {
    
    // Rute untuk User
    $routes->resource('users', ['controller' => 'User']);

    // --- Rute untuk Sensor Data per Device ---
    // GET /api/devices/{deviceId}/sensor-data
    // Ini akan memanggil SensorData::index($deviceId)
    // $1 adalah placeholder untuk (:num) yang merupakan deviceId
    $routes->get('devices/(:num)/sensor-data', 'SensorData::index/$1');
    
    // Opsional: Jika Anda ingin POST data sensor ke device tertentu
    // POST /api/devices/{deviceId}/sensor-data
    // $routes->post('devices/(:num)/sensor-data', 'SensorDataController::create/$1');

    // Rute Resource untuk Device (setelah rute nested yang lebih spesifik)
    // Ini akan menangani:
    // GET    /api/devices -> DeviceController::index()
    // GET    /api/devices/{id} -> DeviceController::show($id)
    // POST   /api/devices -> DeviceController::create()
    // PUT    /api/devices/{id} -> DeviceController::update($id)
    // DELETE /api/devices/{id} -> DeviceController::delete($id)
    $routes->resource('devices', ['controller' => 'Device']);

    // Rute Resource untuk SensorData secara global (jika Anda memerlukannya)
    // Ini akan menangani /api/sensor-data, BUKAN yang nested di bawah device.
    // Jika Anda hanya mengakses sensor data melalui device, Anda mungkin tidak memerlukan ini.
    // $routes->resource('sensor-data', ['controller' => 'SensorData']);

});