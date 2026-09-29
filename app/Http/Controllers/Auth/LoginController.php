<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    public function showLoginForm(){
        return view('auth.login');
    }
    public function tienda(){
        return Redirect::intended('http://localhost/sistema/web/dist');
    }

    public function login(Request $request){
        $this->validateLogin($request);        

        if (Auth::attempt(['usuario' => $request->usuario,'password' => $request->password], true)){
            return redirect('/main');
        }else{
            return back()
                ->withErrors(['usuario' => 'El usuario o la contraseña son incorrectos.'])
                ->withInput(request(['usuario']));
        }
    }

    protected function validateLogin(Request $request){
        $this->validate($request,[
            'usuario' => 'required|string',
            'password' => 'required|string'
        ]);

    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        return redirect('/');
    }
}