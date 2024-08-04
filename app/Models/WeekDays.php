<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekDays extends Model
{
    use HasFactory;
    protected $fillable = ['DayID', 'DayNameArabic', 'DayNameEnglish', 'IsWeeklyVacation'];
    protected $primaryKey = 'DayID';
    protected $table = 'WeekDaysTable';
    public $timestamps = false;
}
