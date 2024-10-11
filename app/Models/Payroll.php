<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class Payroll extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'PersonID',
        'MainSalary',
        'VariableSalary',
        'DayValue',
        'NumberOfAbsentDays',
        'NumberOfAttendedDays',
        'NumberOfPersonalVacations',
        'NumberOfOfficialvacations',
        'NumberOfWeeklyVacations',
        'HawafezValue',
        'KhosoomatValue',
        'SolafValue',
        'TaameenValue',
        'TaameenPercentage',
        'TaameenFinalValue',
        'TaxesPercentage',
        'TaxesValue',
        'PayrollClosingDate',
        'PayrollMonth',
        'PayrollYear',
        'TotalBeforeTaxesAndTaameen',
        'TotalAfterTaxesAndTaameen'
    ];

    protected $table = "Payroll";
    protected $primaryKey = 'ID';

    public $timestamps = false;

    public function person()
    {
        return $this->belongsTo(Person::class, 'PersonID', 'PersonID');
    }
}
