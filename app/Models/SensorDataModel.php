<?php

namespace App\Models;
use CodeIgniter\Model;

class SensorDataModel extends Model
{
    protected $table = 'sensor_data';
    protected $primaryKey = 'id';
    protected $allowedFields = ['device_id', 'temperature', 'humidity', 'rack_status', 'recorded_at'];
}
