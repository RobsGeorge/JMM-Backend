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

class MarhalaEntryQuestionsController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $qetaat = DB::table('Qetaa')->get();
            $entryQuestions = DB::table('MarhalaEntryQuestions')
            ->Join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
            ->Join('QuestionsTypes', 'MarhalaEntryQuestions.RequiredAnswerType', '=','QuestionsTypes.QuestionType')
            ->select('MarhalaEntryQuestions.*', 'Qetaa.QetaaName', 'QuestionsTypes.QuestionTypeInArabicWords')
            ->get();

            return view("entry-questions.entry-questions-index", array('entryQuestions' => $entryQuestions, 'title'=> "الأسئلة"));
        }

        public function create()
        {
            $qetaat = DB::table('Qetaa')->get();
            $questionTypes = DB::table('QuestionsTypes')->get();
            return view("entry-questions.entry-questions-create", array('qetaat'=>$qetaat, 'questionTypes' => $questionTypes));
        }

        public function insert(Request  $request)
        {
            $lastQuestionID = DB::table('MarhalaEntryQuestions')->orderBy('QuestionID','desc')->first();
            
            if($lastQuestionID==Null)
                $thisQuestionID = 1;
            else
                $thisQuestionID = $lastQuestionID->QuestionID + 1;
            
            
            $numberOfChoices =  $request->memberA;
            $stringOfChoices = "";
            for ($i=1; $i<=$numberOfChoices; $i++)
            {
                $choice = "choice".$i;
                $stringOfChoices = $stringOfChoices.$request->$choice;

                if($i<$numberOfChoices)
                    $stringOfChoices = $stringOfChoices.'|';
            }

            if($request->has('questionIsRequired'))
                $isRequired = 1;
            else
                $isRequired = 0;
            
            DB::table('MarhalaEntryQuestions')->insert(
                    array(
                        'QuestionID' => $thisQuestionID,
                        'QetaaID' => $request -> qetaa_id,
                        'QuestionText' => $request -> question_text,
                        'RequiredAnswerType' => $request -> required_answer_type,
                        'MCAnswer' => $stringOfChoices,
                        'NotToBeShown' => 0,
                        'IsRequired' => $isRequired,
                    )
                );
            
            return redirect()->route('entry-questions.index')->with('status',' :تم ادخال بنجاح السؤال' .$thisQuestionID);
            
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
            $qetaat = DB::table('Qetaa')->get();
            $qetaaSelected = DB::table('MarhalaEntryQuestions')
                            ->where('QuestionID', '=', $id)
                            ->Join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
                            ->select('Qetaa.QetaaID', 'Qetaa.QetaaName')
                            ->first();
            $questionTypes = DB::table('QuestionsTypes')->get();
            //$entryQuestions = DB::table('MarhalaEntryQuestions')->where('QuestionID', $id)->first();
            $entryQuestion = DB::table('MarhalaEntryQuestions')
                            ->where('QuestionID', $id)
                            ->Join('Qetaa', 'MarhalaEntryQuestions.QetaaID', '=', 'Qetaa.QetaaID')
                            ->Join('QuestionsTypes', 'MarhalaEntryQuestions.RequiredAnswerType', '=','QuestionsTypes.QuestionType')
                            ->select(   'MarhalaEntryQuestions.QuestionID', 
                                        'MarhalaEntryQuestions.QuestionText', 
                                        'Qetaa.QetaaName', 
                                        'QuestionsTypes.QuestionTypeInArabicWords', 
                                        'MarhalaEntryQuestions.RequiredAnswerType', 
                                        'MarhalaEntryQuestions.MCAnswer',
                                        'MarhalaEntryQuestions.NotToBeShown',
                                        'MarhalaEntryQuestions.IsRequired')
                            ->first();
            $arrayOfMCAnswers = explode('|', $entryQuestion->MCAnswer); 
            //return $arrayOfMCAnswers;
            return view("entry-questions.entry-questions-edit", array('entryQuestion' => $entryQuestion, 'qetaat'=>$qetaat, 'questionTypes'=> $questionTypes, 'qetaaSelected'=>$qetaaSelected, 'arrayOfMCAnswers'=>$arrayOfMCAnswers));
        }
    
        public function updates(Request $request, $id)
        {

            if($request->has('questionNotToBeShown'))
                $notToBeShown = 1;
            else
                $notToBeShown = 0;

            if($request->has('questionIsRequired'))
                $isRequired = 1;
            else
                $isRequired = 0;

                
                $numberOfChoices =  $request->answers;
                
                $stringOfChoices = "";
                for ($i=1; $i<=$numberOfChoices; $i++)
                {
                    $answer = "answer".$i;
                    $stringOfChoices = $stringOfChoices.$request->$answer;
    
                    if($i<$numberOfChoices)
                        $stringOfChoices = $stringOfChoices.'|';
                }
                

            DB::table('MarhalaEntryQuestions')
                            ->where('QuestionID', $id)
                            ->update(['QuestionText' => $request->question_text, 
                                      'QetaaID' => $request->qetaa_id, 
                                      'NotToBeShown' => $notToBeShown,
                                      'MCAnswer' => $stringOfChoices,
                                      'IsRequired' => $isRequired,
                                    ]);
            
            return redirect()->route('entry-questions.index')->with('status','تم تعديل بنجاح السؤال');

        }
    
        public function deletes($id)
        {
            $qetaat = DB::table('Qetaa')->get();
            $entryQuestions = DB::table('MarhalaEntryQuestions')->where('QuestionID', $id)->first();
            return view("entry-questions.entry-questions-delete", array('qetaat' => $qetaat, 'entryQuestions' => $entryQuestions, 'title'=> "حذف رتبة كشفية"));
        }

        public function destroy($id)
        {
            $deleted = DB::table('MarhalaEntryQuestions')->where('QuestionID',$id)->delete();

            return redirect()->route('entry-questions.index')->with('status','تم الغاء السؤال بنجاح');
        }
}