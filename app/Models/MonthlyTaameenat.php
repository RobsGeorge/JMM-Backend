<?php

namespace App\Models;
use App\Models\Person;
use App\Models\PersonDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyTaameenat extends Model
{
    use HasFactory;
    protected $fillable = [
        'PersonID', 
        'PersonTaameenValueAtThatMonth', 
        'TaameenValuePaidByPerson',
        'TaameenValuePaidByCorporate',
        'TaameenPersonPercentage',
        'TaameenCorporatePercentage',
        'Month',
        'Year',
        'UpdateTimestamp'

    ];
    protected $primaryKey = 'ID';
    protected $table = "MonthlyTaameenat";
    public $timestamps = false;

}
