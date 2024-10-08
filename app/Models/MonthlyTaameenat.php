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
        'DepartmentID', 'DepartmentName', 'DepartmentDescription'
    ];
    protected $primaryKey = 'DepartmentID';
    protected $table = "DepartmentsTable";
    public $timestamps = false;

    public function people()
    {
        return $this->hasMany(Person::class, 'DepartmentID');
    }

    public function personDepartments()
    {
        return $this->hasMany(PersonDepartment::class, 'DepartmentID');
    }

    public static function getByID($id)
    {
        return self::where('DepartmentID', $id)->first();
    }
}
