<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>كشافة الشمندورة - ادخال بيانات</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
        <style>
  @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
</style>
    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href={{ asset('img/shamandora.png') }}>
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
</head>

<body class="bg-gradient-primary">
<div class="container mt-4">
    <div class="container">
    <form class="user" id="regForm" method="POST" action="{{route('person.entry-questions-submit')}}">
         @csrf
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="col">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> استكمال بيانات ملتحق جديد</h1>
                            </div>

                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="email_input" style="font-family: 'Cairo', sans-serif;">الكود</label>
                                <input dir="rtl" type="text" name="email_input" id="email_input" class="form-control form-control-user" style="font-family: 'Cairo', sans-serif; font-size: large" value="{{$person->ShamandoraCode}}" disabled>
                            </div>

                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="email_input" style="font-family: 'Cairo', sans-serif;" hidden>ID</label>
                                <input dir="rtl" type="texy" name="person_id" id="person_id" class="form-control form-control-user" style="font-family: 'Cairo', sans-serif; font-size: large" value="{{$person->PersonID}}" hidden>
                            </div>

                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="email_input" style="font-family: 'Cairo', sans-serif;">كلمة السر</label>
                                <input dir="rtl" type="text" name="email_input" id="email_input" class="form-control form-control-user" style="font-family: 'Cairo', sans-serif; font-size: large" value="{{$person->Password}}" disabled>
                            </div>

                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="email_input" style="font-family: 'Cairo', sans-serif;">القطاع</label>
                                <input dir="rtl" type="text" name="email_input" id="email_input" class="form-control form-control-user" style="font-family: 'Cairo', sans-serif; font-size: large" value="{{$person->QetaaName}}" disabled>
                            </div>
                            <br/>


                            <div class="form-group row" dir="rtl">
                                <div class="col-sm-3 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user" name="first_name" id="first_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="الاسم الأول" disabled value="{{$person->FirstName}}">
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-user" name="second_name" id="second_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="الاسم الثاني" disabled value="{{$person->SecondName}}">
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-user" name="third_name" id="third_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="الاسم الثالث" disabled value="{{$person->ThirdName}}">
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-user" name="fourth_name" id="fourth_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="الاسم الرابع" disabled value="{{$person->FourthName}}">
                                </div>
                            </div>
                            <br/>
                            <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown; text-align:center"> الجزء الأخير: الأسئلة الخاصة بكل قطاع</h2>
                            
                            @php
                                $noQuestionsFlag = true;
                            @endphp
                            @foreach($questions as $question)
                                @if($question->NotToBeShown==0)
                                    {{$noQuestionsFlag = false;}}
                                    @if($question->RequiredAnswerType=="MultipleChoice")
                                        <div class="form-group">
                                            <label style="font-family: 'Cairo', sans-serif;">{{$question->QuestionText}}</label>
                                            @if($question->IsRequired==1)
                                                <label style="font-family: 'Cairo', sans-serif; color:brown">**</label>
                                            @endif
                                            <br/>
                                            <select class="form-control col-sm-4" style="margin-right: 20px;" name="{{$question->QuestionID}}" id="{{$question->QuestionID}}">
                                            <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large" value="" disabled selected>اختر من الاجابات المتاحة</option>
                                            @foreach(explode('|',$question->MCAnswer) as $answer)
                                                <option style="font-family: 'Cairo', sans-serif; color: black;" value="{{$answer}}">{{$answer}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    @elseif($question->RequiredAnswerType=="OpenQuestion")
                                        <div class="form-group">
                                            <label style="font-family: 'Cairo', sans-serif;">{{$question->QuestionText}}</label>
                                            @if($question->IsRequired==1)
                                                <label style="font-family: 'Cairo', sans-serif; color:brown">**</label>
                                            @endif
                                            <br/>
                                            <input type="text" class="form-control form-control-user" name="{{$question->QuestionID}}" id="{{$question->QuestionID}}" 
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="أدخل اجابة السؤال  هنا" value="">
                                            <br/>
                                        </div>
                                    @elseif($question->RequiredAnswerType=="TrueOrFalse")
                                        <div class="form-group">
                                            <label for="joindate" style="font-family: 'Cairo', sans-serif;">{{$question->QuestionText}}</label>
                                            @if($question->IsRequired==1)
                                                <label style="font-family: 'Cairo', sans-serif; color:brown">**</label>
                                            @endif
                                            <br/>
                                            <select class="form-control col-sm-4" style="margin-right: 20px;" name="{{$question->QuestionID}}" id="{{$question->QuestionID}}">
                                            <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large" value="" disabled selected>اختر نعم أم لا</option>
                                            <option style="font-family: 'Cairo', sans-serif; color: black;" value="نعم">نعم</option>
                                            <option style="font-family: 'Cairo', sans-serif; color: black;" value="لا">لا</option>
                                            </select>
                                        </div>
                                    @endif
                                @endif
                                @if($noQuestionsFlag==true)
                                    <h2 class="text-center" style="font-family: 'Cairo', sans-serif; text-align: center;">لا يوجد أسئلة مختصة لهذا القطاع</h2>
                                @endif
                            <br/>
                            @endforeach
                        </div>
                        <div class="p-5">
                            <input type="submit" class="btn-google btn-user btn-block" style="background-color: brown; color: azure; font-weight: bolder;" id="submit-button" value="تأكيد"></input>
                        </div>
                        </div>

                    </div>
                </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Parsley Javascript Validation Form Library -->
    <script src="jquery.js"></script>
    <script src="parsley.min.js"></script>

</body>

</html>