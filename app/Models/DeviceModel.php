<?php

namespace App\Models;
use CodeIgniter\Model;

class DeviceModel extends Model
{
    protected $table = 'devices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'device_name', 'location', 'created_at'];
}
