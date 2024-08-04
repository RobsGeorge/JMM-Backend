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
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
        <style>
  @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
</style>
<link rel="icon" type="image/x-icon" href={{ asset('img/shamandora') }}>
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">
@if (session('status'))
<div class="alert alert-success" role="alert">
	<button type="button" class="close" data-dismiss="alert">×</button>
	{{ session('status') }}
</div>
@elseif(session('failed'))
<div class="alert alert-danger" role="alert">
	<button type="button" class="close" data-dismiss="alert">×</button>
	{{ session('failed') }}
</div>
@endif
    <div class="container">
<form action={{ url('/createperson') }} method="post">
@csrf
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-5 d-none d-lg-block">
                    <img src={{ asset('img/shamandora.png') }} style="width: 100%; height: 100%">
                    </div>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> ادخال بيانات ملتحق جديد</h1>
                            </div>
                            <form class="user">
                                <div class="form-group row" dir="rtl">
                                    <div class="col-sm-3 mb-3 mb-sm-0">
                                        <input type="text" name="first_name" class="form-control form-control-user" id="exampleFirstName" style="font-family: 'Cairo', sans-serif;"
                                            placeholder="الاسم الأول">
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" name="second_name" class="form-control form-control-user" id="exampleSecondName" style="font-family: 'Cairo', sans-serif;"
                                            placeholder="الاسم الثاني">
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" name="third_name" class="form-control form-control-user" id="exampleThirdName" style="font-family: 'Cairo', sans-serif;"
                                            placeholder="الاسم الثالث">
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" name="fourth_name" class="form-control form-control-user" id="exampleFourthName" style="font-family: 'Cairo', sans-serif;"
                                            placeholder="الاسم الرابع">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="person_email" class="form-control form-control-user" id="exampleInputEmail" style="font-family: 'Cairo', sans-serif;"
                                        placeholder="البريد الالكتروني">
                                </div>
                                <div class="form-group row" dir="rtl">
                                    <div class="col-sm-6 mb-3 mb-sm-0">    
                                        <label  for="bithdate" style="font-family: 'Cairo', sans-serif;">تاريخ الميلاد</label>
                                        <input name="person_dob" type="date" class="form-control form-control-user" id="birthdateInput" style="margin-left: 5px; font-family: 'Cairo', sans-serif;"
                                            placeholder="تاريخ الميلاد">
                                    </div>
                                </div>

                                <!--<div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleInputPassword" placeholder="Password">
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleRepeatPassword" placeholder="Repeat Password">
                                    </div>
                                </div>-->
                                <input type="submit" class="btn btn-primary btn-user btn-block" value="Register">
                                <hr>
                                <a href="index.html" class="btn btn-google btn-user btn-block">
                                    <i class="fab fa-google fa-fw"></i> Register with Google
                                </a>
                                <a href="index.html" class="btn btn-facebook btn-user btn-block">
                                    <i class="fab fa-facebook-f fa-fw"></i> Register with Facebook
                                </a>
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="forgot-password.html">Forgot Password?</a>
                            </div>
                            <div class="text-center">
                                <a class="small" href="login.html">Already have an account? Login!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </form>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>