<?php

namespace App\Http\Controllers;
use App\User;

use App\Actividad;
use App\Empleado;
use App\Persona;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        $query = User::leftJoin('personas', 'users.id', '=', 'personas.id')
            ->select(
                'users.id',
                'users.usuario',
                'users.condicion',
                'users.idrol',
                'users.email as user_email',
                'users.notificar_ventas',
                'users.empleado_id',
                'personas.nombre',
                'personas.tipo_documento',
                'personas.num_documento',
                'personas.direccion',
                'personas.telefono',
                'personas.email'
            );

        if ($buscar != '') {
            $searchColumn = 'personas.nombre';
            if ($criterio == 'email') {
                $searchColumn = 'personas.email';
            } elseif ($criterio == 'num_documento') {
                $searchColumn = 'personas.num_documento';
            } elseif ($criterio == 'telefono') {
                $searchColumn = 'personas.telefono';
            }
            $query->where($searchColumn, 'like', '%' . $buscar . '%');
        }

        $users = $query->orderBy('users.id', 'desc')->paginate(100);

        foreach ($users as $user) {
            $user->rol;
            $user->empleado;
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

        $request->validate([
            'usuario' => 'required|string|max:100|unique:users,usuario',
            'password' => 'required|string|min:4',
            'idrol' => 'required|string'
        ], [
            'usuario.required' => 'El nombre de usuario es obligatorio.',
            'usuario.unique' => 'El nombre de usuario ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'idrol.required' => 'Debe seleccionar un rol.'
        ]);

        try {
            DB::beginTransaction();

            $empleadoId = $request->empleado_id ?: $request->empleado;
            $empleado = $empleadoId ? Empleado::find($empleadoId) : null;
            $personaNombre = $empleado ? trim($empleado->nombre . ' ' . $empleado->apellido) : $request->usuario;

            $persona = Persona::where('nombre', $personaNombre)->first();
            if (!$persona) {
                $persona = new Persona();
                $persona->nombre = $personaNombre;
            }

            if ($empleado) {
                $persona->tipo_documento = $empleado->tipo_doc;
                $persona->num_documento = $empleado->num_doc;
                $persona->direccion = $empleado->direccion;
                $persona->telefono = $empleado->telefono;
                $persona->email = $empleado->correo;
            }
            $persona->save();

            $user = User::find($persona->id);
            if (!$user) {
                $user = new User();
                $user->id = $persona->id;
            }

            $user->usuario = $request->usuario;
            $user->empleado_id = $empleado ? $empleado->id : null;
            $user->password = bcrypt($request->password);
            $user->condicion = '1';
            $user->idrol = $request->idrol;
            $user->notificar_ventas = $request->input('notificar_ventas', 0);
            if ($empleado) {
                $user->email = $empleado->correo;
            }

            $user->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Usuario registrado con éxito']);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al registrar usuario: ' . $e->getMessage()], 500);
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

        $request->validate([
            'id' => 'required|integer',
            'usuario' => 'required|string|max:100|unique:users,usuario,' . $request->id,
            'idrol' => 'required|string'
        ], [
            'usuario.required' => 'El nombre de usuario es obligatorio.',
            'usuario.unique' => 'El nombre de usuario ya está registrado por otro usuario.',
            'idrol.required' => 'Debe seleccionar un rol.'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);

            $user->usuario = $request->usuario;
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }
            $user->condicion = '1';
            
            $empleadoId = $request->input('empleado') ?: $request->input('empleado_id');
            $user->empleado_id = $empleadoId;
            $user->idrol = $request->idrol;
            $user->notificar_ventas = $request->input('notificar_ventas', 0);

            $empleado = $empleadoId ? Empleado::find($empleadoId) : null;
            $personaNombre = $empleado ? trim($empleado->nombre . ' ' . $empleado->apellido) : $request->usuario;
            
            $persona = Persona::find($user->id);
            if (!$persona) {
                $persona = Persona::where('nombre', $personaNombre)->first();
                if (!$persona) {
                    $persona = new Persona();
                    $persona->id = $user->id;
                }
            }

            $persona->nombre = $personaNombre;
            if ($empleado) {
                $user->email = $empleado->correo;
                $persona->tipo_documento = $empleado->tipo_doc;
                $persona->num_documento = $empleado->num_doc;
                $persona->direccion = $empleado->direccion;
                $persona->telefono = $empleado->telefono;
                $persona->email = $empleado->correo;
            }
            $persona->save();

            $user->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Usuario actualizado con éxito']);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
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

    public function cambiarPassword(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $this->validate($request, [
            'password_actual' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password_actual.required' => 'La contraseña actual es requerida.',
            'password.required' => 'La nueva contraseña es requerida.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada con éxito.'
        ], 200);
    }
}
