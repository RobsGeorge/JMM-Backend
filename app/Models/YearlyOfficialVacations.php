<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearlyOfficialVacations extends Model
{
    use HasFactory;

    protected $fillable = [
        'VacationID',
        'VacationDate',
        'VacationName',
        'Year'
    ];

    protected $primaryKey = 'VacationID';
    protected $table = 'YearlyOfficialVacations';
    public $timestamps = false;
}
