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

class PersonAbsence extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'AbsenceID',
        'PersonID',
        'AbsenceDate',
        'AbsenceReason'
    ];

    protected $table = "PersonAbsence";
    protected $primaryKey = 'AbsenceID';

    public $timestamps = false;

    public function personInformation()
    {
        return $this->belongsTo(Person::class, 'PersonID', 'PersonID');
    }
}
