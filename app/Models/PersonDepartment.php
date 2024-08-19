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

class PersonDepartment extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'DepartmentID',
        'UpdateTimestamp',
    ];

    protected $table = "PersonDepartment";
    protected $primaryKey = 'ID';

    public $timestamps = false;


    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonID');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID');
    } 

    public static function getByPersonID($personID)
    {
        return self::where('PersonID', $personID)->first();
    }
}
