<?php

namespace App\Http\Controllers;
use App\User;

use App\Actividad;
use App\Empleado;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $users = User::select('*')->orderBy('id', 'desc')->paginate(100);
            foreach ($users as $user) {
                $user->rol;
                if($user->empleado){
                    $user->empleado;
                }else{
                    $user->empleado= new \stdClass(); 
                    $user->empleado->nombre='';
                    $user->empleado->apellido='';
                    $user->empleado->tipo_doc='';
                    $user->empleado->num_doc='';
                    

                }
            }
        }
        else {
            $users = User::where($criterio, 'like', '%' . $buscar . '%')
                ->orderBy('id', 'desc')->paginate(100);
            foreach ($users as $user) {
                $user->rol;
                if ($user->empleado) {
                    $user->empleado;
                } else {
                    $user->empleado = new \stdClass();
                    $user->empleado->nombre = '';
                    $user->empleado->apellido = '';
                    $user->empleado->tipo_doc = '';
                    $user->empleado->num_doc = '';
                }
            }
        }
        
        return [
            'pagination' => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'per_page'     => $users->perPage(),
                'last_page'    => $users->lastPage(),
                'from'         => $users->firstItem(),
                'to'           => $users->lastItem(),
            ],
            'users' => $users
        ];
    }
    public function login(Request $request){
        $user=User::where('usuario','=',$request->usuario)->first();
        if(!is_null($user) && Hash::check($request->password, $user->password))  {
            $user->api_token=Str::random(100);
            $user->save();
            return response()->json([
                    'res'=>true,
                    'token'=>$user->api_token,
                    'message'=>'ok'
            ], 200);
        }
    }
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        try {
            DB::beginTransaction();
            $user = new User();
            $user->usuario = $request->usuario;
            $user->empleado_id = $request->empleado_id;
            $user->password = bcrypt($request->password);
            $user->condicion = '1';
            $user->idrol = $request->idrol;

            $user->save();

            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
        }

        
        
    }
    public function actividadUser(Request $request){
        $id=$request->id;
        $actividad=Actividad::where('user_id',$id)->orderBy('id', 'desc')->get();
        return $actividad;

    }
    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);

            $user->usuario = $request->usuario;
            $user->password = bcrypt($request->password);
            $user->condicion = '1';
            $user->empleado_id = $request->empleado;
            $user->idrol = $request->idrol;
            $user->save();

            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
        }

    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $user = User::findOrFail($request->id);
        $user->delete();
    }

    public function activar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $user = User::findOrFail($request->id);
        $user->condicion = '1';
        $user->save();
    }


}
