<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'VacationTypeID', 'VacationTypeName', 'VacationTypeDescription'
    ];

    protected $table = 'VacationTypesTable';
    protected $primaryKey = 'VacationTypeID';
    public $timestamps = false;
}
