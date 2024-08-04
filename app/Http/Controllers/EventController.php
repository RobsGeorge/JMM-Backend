<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;

class EventController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {

            $events = DB::select("
                                    SELECT e.*, et.EventTypeName, GROUP_CONCAT(q.QetaaName SEPARATOR ' | ') AS EventQetaat
                                    FROM Event e
                                    LEFT JOIN EventType et ON e.EventTypeID = et.EventTypeID
                                    LEFT JOIN EventQetaa eq ON e.EventID = eq.EventID
                                    LEFT JOIN Qetaa q ON eq.QetaaID = q.QetaaID
                                    GROUP BY e.EventID, e.EventName;            
                                    ");

            
            return view("event.index", array('events' => $events));
        }

        public function create()
        {
            $eventTypes = DB::table('EventType')->get();
            $qetaat = DB::table('Qetaa')->get();
            return view("event.create", array('qetaat'=>$qetaat, 'eventTypes'=>$eventTypes));
        }

        public function createRecursive()
        {
            $eventTypes = DB::table('EventType')->get();
            $qetaat = DB::table('Qetaa')->get();
            return view("event.create-recursive", array('qetaat'=>$qetaat, 'eventTypes'=>$eventTypes));
        }

        public function insert(Request  $request)
        {
            $validator = Validator::make($request->all(), [
                'event_type_id' => 'required',
                'event_start_date' => 'required',
                'event_end_date' => 'required',
                'qetaa_id' => 'required'
            ]);
     
            if ($validator->fails()) {
                return view('person.entry-error-repeat-trial');
            }


            if(date_create($request->event_start_date)>date_create($request->event_end_date))
            {
                return view('event.check-dates');
            }   

 
            $lastEventID = DB::table('Event')->orderBy('EventID','desc')->first();
            
            if($lastEventID==Null)
                $thisEventID = 1;
            else
                $thisEventID = $lastEventID->EventID + 1;

            
            try{

                DB::beginTransaction();
                DB::table('Event')->insert(
                    array(
                        'EventID' => $thisEventID,
                        'EventTypeID' => $request -> event_type_id,
                        'EventName' => $request -> event_name,
                        'EventStartDate' => $request -> event_start_date,
                        'EventEndDate' => $request -> event_end_date,
                    )
                );
                
                foreach($request->qetaa_id as $qetaa){
                    DB::table('EventQetaa')->insert(
                        array(
                            'EventID' => $thisEventID,
                            'QetaaID' => $qetaa 
                        )
                    );
                }
            }
            catch(Exception $e)
            {
                dd($e->getMessage());
                DB::rollBack();
                return view('person.entry-error');
            }

            DB::commit();

            return redirect()->route('event.index');
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
            $eventTypes = DB::table('EventType')->get();
            $qetaat = DB::table('Qetaa')->get();
            $event = DB::selectOne("
                                SELECT e.*, et.EventTypeName, GROUP_CONCAT(q.QetaaName SEPARATOR ' | ') AS EventQetaat
                                FROM Event e
                                LEFT JOIN EventQetaa eq ON e.EventID = eq.EventID
                                LEFT JOIN Qetaa q ON eq.QetaaID = q.QetaaID
                                LEFT JOIN EventType et ON e.EventTypeID = et.EventTypeID
                                WHERE e.EventID = ? 
                                GROUP BY e.EventID, e.EventName
                                LIMIT 1 
                                ", [$id]);

            return view("event.edit", array('event' => $event, 'eventTypes' => $eventTypes, 'qetaat' => $qetaat));
        }
    
        public function updates(Request $request, $id)
        {

            $validator = Validator::make($request->all(), [
                'event_type_id' => 'required',
                'event_start_date' => 'required',
                'event_end_date' => 'required',
                'qetaa_id' => 'required'
            ]);
     
            if ($validator->fails()) {
                return view('person.entry-error-repeat-trial');
            }

            try{

                DB::beginTransaction();
                
                DB::table('Event')  ->where('EventID', $id)
                                    ->update([  
                                                'EventName' => $request->event_name, 
                                                'EventTypeID'=>$request -> event_type_id,
                                                'EventName' => $request -> event_name,
                                                'EventStartDate' => $request -> event_start_date,
                                                'EventEndDate' => $request -> event_end_date,
                                            ]);
                DB::table('EventQetaa')->where('EventID',$id)->delete();

                foreach($request->qetaa_id as $qetaa){
                    DB::table('EventQetaa')->insert(
                        array(
                            'EventID' => $id,
                            'QetaaID' => $qetaa 
                        )
                    );
                }
            }
            catch(Exception $e)
            {
                dd($e->getMessage());
                DB::rollBack();
                return view('person.entry-error-repeat-trial');
            }

            DB::commit();

            return redirect()->route('event.index');
        }
    
        public function deletes($id)
        {
            $event = DB::table('Event')->where('EventID', $id)->first();
            return view("event.delete", array('event' => $event));
        }

        public function destroy($id)
        {
            try{
                DB::beginTransaction();
                DB::table('Event')->where('EventID',$id)->delete();
                DB::table('EventQetaa')->where('EventID',$id)->delete();
            }
            catch(Exception $e)
            {
                dd($e->getMessage());
                DB::rollBack();
                return view('person.entry-error-repeat-trial');
            }
            DB::commit();
            return redirect()->route('event.index');
        }
}