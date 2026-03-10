<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'connector_type_id', 
        'latitude', 
        'longitude', 
        'power_kw', 
        'status'
    ];

    public function connectorType()
    {
        return $this->belongsTo(ConnectorType::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
