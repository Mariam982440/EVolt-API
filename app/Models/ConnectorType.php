<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Station;



class ConnectorType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function stations()
    {
        return $this->hasMany(Station::class);
    }
}