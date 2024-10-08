<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class PersonSalary extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'Salary',
        'VariableSalary',
        'IsPerDay',
        'UpdateTimestamp',
    ];

    protected $table = "PersonSalary";
    protected $primaryKey = 'ID';

    public $timestamps = false;


    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonID');
    }

    public static function getByPersonID($personID)
    {
        return self::where('PersonID', $personID)->first();
    }
}
