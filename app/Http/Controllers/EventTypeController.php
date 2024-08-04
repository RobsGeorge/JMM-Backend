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

class EventTypeController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $eventTypes = DB::table('EventType')->get();
            return view("event-type.index", array('eventTypes' => $eventTypes));
        }

        public function create()
        {
            return view("event-type.create");
        }

        public function insert(Request  $request)
        {
 
            $lastEventTypeID = DB::table('EventType')->orderBy('EventTypeID','desc')->first();
            
            if($lastEventTypeID==Null)
                $thisEventTypeID = 1;
            else
                $thisEventTypeID = $lastEventTypeID->EventTypeID + 1;

            DB::table('EventType')->insert(
                array(
                    'EventTypeID' => $thisEventTypeID,
                    'EventTypeName' => $request -> event_type_name
                )
            );
            return redirect()->route('event-type.index');
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
            $eventType = DB::table('EventType')->where('EventTypeID', $id)->first();
            return view("event-type.edit", array('eventType' => $eventType));
        }
    
        public function updates(Request $request, $id)
        {
            $affected = DB::table('EventType')->where('EventTypeID', $id)->update(['EventTypeName' => $request->event_type_name]);
            return redirect()->route('event-type.index');
        }
    
        public function deletes($id)
        {
            $eventType = DB::table('EventType')->where('EventTypeID', $id)->first();
            return view("event-type.delete", array('eventType' => $eventType));
        }

        public function destroy($id)
        {
            $deleted = DB::table('EventType')->where('EventTypeID',$id)->delete();
            return redirect()->route('event-type.index');
        }
}