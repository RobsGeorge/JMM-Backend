<?php

namespace App\Models;
use App\Models\Person;
use App\Models\PersonDepartment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;
    protected $fillable = [
        'ReasonID', 'Reason', 'ReasonDescription'
    ];
    protected $primaryKey = 'ReasonID';
    protected $table = "ReasonsTable";
    public $timestamps = false;

}
