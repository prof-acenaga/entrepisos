<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model;


class Department extends Model
{
    protected $collection = 'departments';
    protected $connection = 'mongodb';

    protected $fillable = [
        'type',
        'location',
        'floor',
        'department',
        'district',
        'flat_rooms',
        'removed',
    ];  

    //TODO: agregar section de amenities.
}
