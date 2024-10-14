<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class PersonYearlyVacationsLimits extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'Year',
        'VacationTypeID',
        'VacationLimit'
    ];

    protected $table = "PersonYearlyVacationLimits";
    protected $primaryKey = 'ID';

    public $timestamps = false;


    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonID');
    }

    public function vacationType()
    {
        return $this->belongsTo(VacationType::class, 'VacationTypeID', 'VacationTypeID');
    }
}
