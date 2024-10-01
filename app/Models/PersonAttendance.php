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
use App\Models\PersonVacations;
use App\Models\WeekDays;
use Carbon\Carbon;

class PersonAttendance extends Model
{
    use HasFactory;

    protected $table = 'PersonAttendance';
    protected $primaryKey = 'AttendanceID';
    public $timestamps = false;
    
    protected $fillable = [
        'PersonID',
        'AttendanceDate',
        'WorkStartTime',
        'WorkEndTime',
        'IsAbsent',
        'IsWeeklyVacation',
        'IsCompanyOnVacation',
        'CompanyVacationID',
        'IsPersonalVacation',
        'PersonVacationID'
    ];

    // Relationships
    public function personInformation()
    {
        return $this->belongsTo(Person::class, 'PersonID')->where('IsDeleted', 0);
    }

    public function personalVacation()
    {
        return $this->belongsTo(PersonVacations::class, 'PersonVacationID');
    }

    // Relationship with WeekDaysTable to check if AttendanceDate matches a weekly vacation
    public function weeklyVacation()
    {
        return $this->hasOne(WeekDays::class, 'DayNameEnglish', 'DayOfWeek')
            ->where('IsWeeklyVacation', 1);
    }

    // Accessor to get the day of the week (e.g., Saturday, Sunday) from AttendanceDate
    public function getDayOfWeekAttribute()
    {
        // Using Carbon to get the day name in English from the AttendanceDate
        return Carbon::parse($this->AttendanceDate)->format('l'); // returns day like 'Saturday'
    }
}
