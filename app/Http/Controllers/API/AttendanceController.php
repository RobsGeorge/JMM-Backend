<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;


class PersonController extends Controller
{

    //Given a certain EventID and PersonID (Authenticated Session) -> get all the recorded attendance for persons under this user authority 
    //as well as the recorded attendance for each of them in the event defined bt the given EventID

    public function getAttendanceByEventID($eventID)
    {
        $event = DB::selectOne("Event")->where("Event.EventID", "=", $eventID)->get();

        $groupControlledByAuthUser = DB::select("   SELECT  PersonGroup.GroupID, 
                                                            PersonGroup.GroupRoleID, 
                                                            GroupRole.GroupRoleName, 
                                                            CONCAT(GroupType.GroupTypeName, ' ', GroupTable.GroupName) AS GroupInfo
                                                    FROM PersonGroup
                                                    WHERE PersonGroup.PersonID = ?
                                                    LEFT JOIN GroupRole ON GroupRole.GroupRoleID = PersonGroup.GroupRoleID
                                                    LEFT JOIN GroupTable ON GroupTable.GroupID = PersonGroup.GroupID
                                                    LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID 
                                                ", [Auth::user()->PersonID]);

        $data = DB::select("SELECT DISTINCT  
                                                    pi.ShamandoraCode,
                                                    pi.FirstName, 
                                                    pi.SecondName, 
                                                    pi.ThirdName, 
                                                    pi.FourthName, 
                                                    q.QetaaName,
                                                    pi.ScoutJoiningYear,
                                                    sm.SanaMarhalaName, 
                                                    pi.RaqamQawmy,
                                                    ppn.PersonPersonalMobileNumber
                                                FROM PersonInformation pi
                                                LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID 
                                                LEFT JOIN PersonSanaMarhala psm ON psm.PersonID = pi.PersonID
                                                LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
                                                LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
                                                LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
                                                LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
                                                WHERE q.QetaaID = ?;", [$qetaa_id]);
        
        
        return response()->json(['groupControlledByAuthUser'=>$groupControlledByAuthUser, 'status'=>200]);
    }
}

?>