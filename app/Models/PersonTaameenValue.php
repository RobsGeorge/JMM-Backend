<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class PersonTaameenValue extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'Taameenvalue',
        'UpdateTimestamp',
    ];

    protected $table = "PersonTaameenValue";
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
