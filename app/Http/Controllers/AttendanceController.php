<?php

namespace App\Http\Controllers;
use App\Http\Controller\GroupPersonController;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;

class AttendanceController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {   
            $khademAuthenticatedID = Auth::user()->PersonID;
            $directGroupsConnectedToKhadem = DB::select("SELECT PersonGroup.GroupID FROM PersonGroup WHERE PersonID = ?", [$khademAuthenticatedID]);

            if($directGroupsConnectedToKhadem != NULL)
            {
                $allGroupsIDsBelowKhadem = [];
                foreach($directGroupsConnectedToKhadem as $groupConnected)
                {
                    $allGroupsIDsBelowKhadem = array_merge($allGroupsIDsBelowKhadem, app('App\Http\Controllers\GroupPersonController')->getNodesBelow($groupConnected->GroupID, [$groupConnected->GroupID]));
                }
                

                foreach($allGroupsIDsBelowKhadem as $groupID)
                {              
                    //$groupPersons = array_merge($groupPersons, $tempPersons);
                }

                $rootsArray = [];
                foreach($directGroupsConnectedToKhadem as $directGroup)
                {
                    array_push($rootsArray, app('App\Http\Controllers\GroupPersonController')->getLatestParentBeforeRoot($directGroup->GroupID));
                }
                $rootsArray = array_unique($rootsArray);


                $events = [];
                foreach($rootsArray as $rootItem)
                {
                    $qetaa_id = DB::selectOne("SELECT QetaaID FROM GroupQetaa WHERE GroupQetaa.GroupID = $rootItem")->QetaaID;
                    $events_connected_to_qetaa = DB::selectOne("SELECT EventID FROM EventQetaa WHERE QetaaID = $qetaa_id")->EventID;
                    array_push($events, $events_connected_to_qetaa);
                }

                $result = [];
                foreach($events as $event)
                {
                    $a = DB::selectOne("   SELECT CONCAT(EventType.EventTypeName, ' - ', Event.EventName, ' من ' , Event.EventStartDate, ' إلى ', Event.EventEndDate) AS EventInfo, EventID
                                        FROM Event
                                        LEFT JOIN EventType ON Event.EventTypeID = EventType.EventTypeID
                                        WHERE Event.EventID = $event");
                    
                    array_push($result,$a);
                }
            }
            //return all events for those groups-Qetaat attached to the current khadem
            return view("attendance.index", array('events' => $result));
        }

        public function findAttendanceByEventID($eventID)
        {
            $khademAuthenticatedID = Auth::user()->PersonID;
            $directGroupsConnectedToKhadem = DB::select("SELECT PersonGroup.GroupID FROM PersonGroup WHERE PersonID = ?", [$khademAuthenticatedID]);
            $persons = [];

            if(DB::table("PersonEventAttendance")->where("PersonEventAttendance.EventID", "=", $eventID)->exists()) //Event is found in attendance and has records attached to it
            {
                //This means that the event is having records attached to it
                //So, we need to return all the persons result array joined with the attendance of each person at this given EventID
                //For those users who are not found in the attendance table, they are fetched in the result persons array to be returned to the view
                
                foreach($directGroupsConnectedToKhadem as $groupID)
                {
                    $personsForthisGroup = DB::select(" SELECT pg.*, p.PersonID, p.ShamandoraCode,
                                                        CASE WHEN pe.EventID IS NOT NULL THEN 'Yes' ELSE 'No' END AS ExistsInEvent,
                                                        CONCAT(PersonInformation.FirstName, ' ', 
                                                        PersonInformation.SecondName, ' ', PersonInformation.ThirdName) AS PersonFullName,
                                                        Group_CONCAT(PersonFullName) AS GroupMembers,
                                                        GroupRole.GroupRoleName
                                                FROM PersonInformation p
                                                INNER JOIN PersonGroup pg ON p.PersonID = pg.PersonID
                                                LEFT JOIN PersonEventAttendance pe ON p.PersonID = pe.PersonID AND pe.EventID = $eventID
                                                INNER JOIN GroupTable g ON pg.groupID = g.GroupID
                                                LEFT JOIN GroupRole ON GroupRole.GroupRoleID = PersonGroup.GroupRoleID
                                                WHERE pg.GroupID = $groupID AND pe.PersonID != $khademAuthenticatedID AND GroupRole.IsKhademRole = 0
                                                GROUP BY pg.GroupID, p.PersonID
                                                ");
                    $persons = array_merge($persons, $personsForthisGroup);
                }
            }
            else
            {
                //This is event is not attached to any persons attendance and deosn't have any records
                //Return the Persons array without the attendance array
                //Return the attendance result array containing (PersonID) and Empty Checkboxes
                foreach($directGroupsConnectedToKhadem as $groupID)
                {
                    $personsForthisGroup = DB::select(" SELECT pg.*, p.PersonID, p.ShamandoraCode,
                                                        CONCAT(PersonInformation.FirstName, ' ', 
                                                        PersonInformation.SecondName, ' ', PersonInformation.ThirdName) AS PersonFullName,
                                                        Group_CONCAT(PersonFullName) AS GroupMembers,
                                                        GroupRole.GroupRoleName
                                                FROM PersonInformation p
                                                INNER JOIN PersonGroup pg ON p.PersonID = pg.PersonID
                                                INNER JOIN GroupTable g ON pg.groupID = g.GroupID
                                                LEFT JOIN GroupRole ON GroupRole.GroupRoleID = PersonGroup.GroupRoleID
                                                WHERE pg.GroupID = $groupID AND pe.PersonID != $khademAuthenticatedID AND GroupRole.IsKhademRole = 0
                                                GROUP BY pg.GroupID, p.PersonID
                                                ");
                    $persons = array_merge($persons, $personsForthisGroup);
                }
            }
            return $persons;
        }

        public function insert(Request  $request)
        {
            $lastGroupID = DB::table('GroupTable')->orderBy('GroupID','desc')->first();
            
            if($lastGroupID==Null)
                $thisGroupID = 1;
            else
                $thisGroupID = $lastGroupID->GroupID + 1;

            DB::table('GroupTable')->insert(
                array(
                    'GroupID' => $thisGroupID,
                    'GroupName' => $request -> group_name,
                    'GroupTypeID' => $request -> group_type_id,
                    'IncludedUnderGroupID' => $request -> included_under_group_id
                )
            );
            return redirect()->route('group.index');
        }
    
        /**
            * Display the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function show($id)
        {
            //
        }
    
        /**
            * Show the form for editing the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function edit($id)
        {

            $groupSelected =   DB::selectOne("SELECT    g1.IncludedUnderGroupID, 
                                    g1.GroupID AS GroupID1,
                                    g3.GroupTypeID,
                                    g1.GroupName, 
                                    g3.GroupTypeName, 
                                    g2.GroupID AS GroupID2, 
                                    CONCAT(g4.GroupTypeName, ' ', g2.GroupName) AS GroupInfo
                        FROM GroupTable g1
                        LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
                        LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
                        LEFT JOIN GroupType g4 ON g2.GroupTypeID = g4.GroupTypeID
                        WHERE g1.GroupID = ?
                        ", [$id]);
            //return $groupSelected;
            $groupTypes = DB::table('GroupType')->get();

            $groups = DB::select("  SELECT  GroupTable.GroupID,  GroupTable.IncludedUnderGroupID,
                                            CONCAT(GroupType.GroupTypeName, ' ', GroupTable.GroupName) AS GroupInfo
                                    FROM GroupTable
                                    LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID
                                ");
    
            return view("group.edit", array('groupSelected' => $groupSelected, 'groupTypes' => $groupTypes, 'groups' => $groups));
        }
    
        public function updates(Request $request, $id)
        {
            //$qetaa = DB::table('Qetaa')->where('QetaaID', $id)->first();
            //return $request;
            $affected = DB::table('GroupTable')->where('GroupID', $id)->update(['GroupName' => $request->group_name, 
                                                                                'GroupTypeID'=> $request -> group_type_id, 
                                                                                'IncludedUnderGroupID' => $request -> included_under_group_id]);

            return redirect()->route('group.index');
        }
    
        public function deletes($id)
        {
            $group = DB::table('GroupTable')->where('GroupID', $id)->first();
            return view("group.delete", array('group' => $group));
        }

        public function destroy($id)
        {
            $deleted = DB::table('GroupTable')->where('GroupID',$id)->delete();
            
            return redirect()->route('group.index');
        }
}