<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Roles;
use App\Models\User;
use App\Models\Department;
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
        'VacationStartDate',
        'VacatioEndDate',
        'VacationTypeID',
        'NumberOfVacationDays'
    ];

    protected $table = "PersonVacations";
    protected $primaryKey = 'PersonvacationID';

    public $timestamps = false;


    public function personAttendance()
    {
        return $this->hasMany(PersonAttendance::class, 'PersonID', 'PersonID');
    }

}
