<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class PersonVacations extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonVacationID',
        'PersonID',
        'VacationDate',
        'VacationTypeID',
        'IsBeyondLimit'
    ];

    protected $table = "PersonVacations";
    protected $primaryKey = 'PersonVacationID';

    public $timestamps = false;


    public function personAttendance()
    {
        return $this->hasMany(PersonAttendance::class, 'PersonID', 'PersonID');
    }

    public function personInformation()
    {
        return $this->hasOne(Person::class, 'PersonID', 'PersonID');
    }

    public function vacationType()
    {
        return $this->hasOne(VacationType::class, 'VacationTypeID', 'VacationTypeID');
    }

}
