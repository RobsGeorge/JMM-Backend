<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;



class ClosedMonthsController extends Controller
{
        public function getClosedMonths()
        {
            $data = DB::table('ClosedMonths')->get();
            return response()->json(['data'=>$data, 'message'=>'All Closed Months Returned Successfully!'],200);
        }
}