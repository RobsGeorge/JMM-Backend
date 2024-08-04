<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use App\Models\User;
use App\Models\Person;
use Session;

class LoginController extends Controller
{
    /**
     * Display login page.
     * 
     * @return Renderable
     */
    public function show()
    {
        return view('login');
    }

    /**
     * Handle account login request
     * 
     * @param LoginRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        
        $user = User::find($request->person_id);

        if(!$user)
            return "No User Found!";
        if(!$user->Password == $request->person_password)
            return "Wrong Password";
            //return redirect()->to('login')->withErrors(trans('auth.failed'));


        
        //return ($user);
        /*$user = DB::select("SELECT              pi.PersonID,
                                                r.RoleName,
                                                psp.Password as Password
                                            FROM PersonInformation pi
                                            LEFT JOIN PersonSystemPassword psp ON pi.PersonID = psp.PersonID
                                            LEFT JOIN PersonRole pr ON pi.PersonID = pr.PersonID
                                            LEFT JOIN Roles r ON r.RoleID = pr.RoleID
                                            WHERE pi.PersonID = ?", [$request->person_id])[0];*/
        


        Auth::login($user);

        //$person = Person::find($request->person_id);

        //return $user->roles[0]->RoleName;
        //return $person->FirstName;
        //return redirect('/')->with('user', $user)->with('person', $person);
        return $this->authenticated($request, $user);
    }

    /**
     * Handle response after user authenticated
     * 
     * @param Request $request
     * @param Auth $user
     * 
     * @return \Illuminate\Http\Response
     */
    protected function authenticated(Request $request, $user) 
    {
        return redirect()->intended();
    }
}