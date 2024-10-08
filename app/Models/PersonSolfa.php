<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class PersonSolfa extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'SolfaValue',
        'SolfaDate',
        'SolfaReason'
    ];

    protected $table = "PersonSolaf";
    protected $primaryKey = 'SolfaID';

    public $timestamps = false;


    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonID');
    }
}
