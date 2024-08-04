<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingTimes extends Model
{
    use HasFactory;
    protected $fillable = ['ID', 'StartTimeHour', 'StartTimeMinute', 'EndTimeHour', 'EndTimeMinute', 'UpdateTimestamp'];
    protected $table = 'WorkingTimesTable';
    protected $primaryKey = 'ID';
    public $timestamps = false;
}
