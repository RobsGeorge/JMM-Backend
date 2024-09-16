<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taameen extends Model
{
    use HasFactory;

    protected $fillable = [
        'ID', 'TaameenMinValue', 'TaameenMaxValue', 'TaameenPersonPercentage', 'TaameenCorporatePercentage', 'UpdateTimestamp'
    ];
    protected $table = 'TaameenTable';
    protected $primaryKey = 'ID';
    public $timestamps = false;
}
