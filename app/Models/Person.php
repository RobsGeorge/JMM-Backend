<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\PersonDepartment;

class Person extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'FirstName',
        'SecondName',
        'ThirdName',
        'FourthName',
        'Religion',
        'Gender',
        'RaqamQawmy',
        'TaameenNumber',
        'DateOfBirth',
        'WorkStartDate',
        'DateOfBirthCertificatePhotoURL',
        'PersonalPhotoURL',
        'PersonalIDPhotoURL',
        'MobileNumber',
        'LandlineNumber',
        'StreetName',
        'Manteqa',
        'District',
        'MohafzaID',
        'MaxNumberOfVacationDays',
        'MaxValueOfSolfaPerMonth',
        'MaxPercentOfSalaryForSolfaPerMonth',
        'WorkEmail',
        'PersonalEmail',
        'WorkContractPhotoURL'
    ];

    protected $guarded = [
        'IsDeleted',
        'DeletedAt'
    ];

    protected $table = "PersonInformation";
    protected $primaryKey = 'PersonID';

    public $timestamps = false;


    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID');
    }

    public function personDepartment()
    {
        return $this->hasOne(PersonDepartment::class, 'PersonID');
    }

    public static function getByRaqamQawmy($raqamQawmy)
    {
        return self::where('RaqamQawmy', $raqamQawmy)->first();
    }

    public static function getByID($id)
    {
        return self::where('PersonID', $id)->first();
    }
    
    public function absences()
    {
        return $this->hasMany(PersonAbsence::class, 'PersonID', 'PersonID');
    }

    public function attendances()
    {
        return $this->hasMany(PersonAttendance::class, 'PersonID', 'PersonID');
    }


}
