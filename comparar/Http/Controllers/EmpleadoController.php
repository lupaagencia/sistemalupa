<?php

namespace App\Http\Controllers;
use App\Empleado;
use App\User;
use App\Ajustes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $per_page=$request->per_page;
        $id=array();
        if ($buscar==''){
            $empleados=Empleado::select('*')->orderBy('id','desc')->paginate($per_page);
          
            // $empleados = Comprobante::select()->orderBy('id', 'desc');
        }
        else{
           
            $empleados = Empleado::select('*')->where($criterio,$buscar)->orderBy('id', 'desc')->paginate($per_page);
           
        }
        
        return [
            'pagination' => [
                'total'        => $empleados->total(),
                'current_page' => $empleados->currentPage(),
                'per_page'     => $empleados->perPage(),
                'last_page'    => $empleados->lastPage(),
                'from'         => $empleados->firstItem(),
                'to'           => $empleados->lastItem(),
            ],
            'empleados' => $empleados,
            
        ];
    }
    
   
    public function crearEmpleado(request $request){
        // return $request->data;
        $empleadoData = json_decode($request->data,true);
        // $empleadoArray=['empleado' => get_object_vars($empleadoData)];
        // $empleadoData2 = json_decode($empleadoData, false);

        
        // Validación
        // $request->validate([
        //     'img_doc' => 'nullable|image|mimes:jpeg,png,jpg|max:112048',
        //     'foto' => 'nimage|mimes:jpeg,png,jpg|max:112048',
        //     'pdf_hoja' => 'mimes:pdf|max:2048',
        //     'pdf_contrato' => 'mimes:pdf|max:2048',
        // ]);
        $rutaimg=public_path('fotos'); 
        $rutapdf=public_path('documentos');
        if($request->img_doc){
            $doc=$request->img_doc;
            $nameDoc=$request->img_doc->getclientoriginalname();
            $doc->move($rutaimg, $nameDoc);
        }else{
            $nameDoc='';
        }
        if($request->foto){
            $foto=$request->foto;
            $nameFoto=$request->foto->getclientoriginalname();
            $foto->move($rutaimg, $nameFoto);
        }else{
            $nameFoto='';
        }
        if($request->pdf_contrato){
            $contrato=$request->pdf_contrato;
            $nameContrato=$request->pdf_contrato->getclientoriginalname();
            $contrato->move($rutapdf, $nameContrato);
        }else{
            $nameContrato='';
        }
        if($request->pdf_hoja){
            $hoja=$request->pdf_hoja;
            $nameHoja=$request->pdf_hoja->getclientoriginalname();
            $hoja->move($rutapdf, $nameHoja);
        }else{
            $nameHoja='';
        }
        // Crear el empleado con los datos JSON y archivos
       
        // return $empleadoArray;
        foreach (['fecha_nacimiento', 'fecha_ingreso', 'fecha_finalizacion'] as $campo) {
            if (empty($empleadoData[$campo])) {
                $empleadoData[$campo] = null;
            }
        }

        foreach (['salario', 'horas_semanales', 'num_hijos', 'num_cuenta_banco', 'num_afiliacion_social'] as $campo) {
            if (empty($empleadoData[$campo])) {
                $empleadoData[$campo] = null;
            }
        }
        if($empleadoData['id']==0){
            $empleado = new Empleado();
        }else{
            $empleado=Empleado::find($empleadoData['id']);
        }
        $empleado->nombre = $empleadoData['nombre'];
        $empleado->apellido = $empleadoData['apellido'];
        $empleado->lugar_nacimiento = $empleadoData['lugar_nacimiento'];
        $empleado->telefono = $empleadoData['telefono'];
        $empleado->direccion = $empleadoData['direccion'];
        $empleado->correo = $empleadoData['correo'];
        $empleado->cargo = $empleadoData['cargo'];
        $empleado->area = $empleadoData['area'];
        $empleado->banco = $empleadoData['banco'];
        $empleado->pension = $empleadoData['pension'];
        $empleado->eps = $empleadoData['eps'];
        $empleado->arl = $empleadoData['arl'];
        $empleado->nivel_estudio = $empleadoData['nivel_estudio'];
        $empleado->titulos = $empleadoData['titulos'];
        $empleado->certificaciones = $empleadoData['certificaciones'];
        $empleado->idiomas = $empleadoData['idiomas'];
        $empleado->habilidades = $empleadoData['habilidades'];
        $empleado->experiencia = $empleadoData['experiencia'];
        $empleado->contacto_esposa = $empleadoData['contacto_esposa'];
        $empleado->contacto_padres = $empleadoData['contacto_padres'];
        $empleado->contacto_emergencia = $empleadoData['contacto_emergencia'];
        $empleado->info_medica = $empleadoData['info_medica'];
        $empleado->talla_dotacion = $empleadoData['talla_dotacion'];
        $empleado->tipo_doc = $empleadoData['tipo_doc'];
        $empleado->estado_civil = $empleadoData['estado_civil'];
        $empleado->tipo_contrato = $empleadoData['tipo_contrato'];
        $empleado->tipo_jornada = $empleadoData['tipo_jornada'];
        $empleado->tipo_cuenta = $empleadoData['tipo_cuenta'];
        $empleado->turno = $empleadoData['turno'];
        $empleado->fecha_nacimiento = $empleadoData['fecha_nacimiento'];
        $empleado->fecha_ingreso = $empleadoData['fecha_ingreso'];
        $empleado->fecha_finalizacion = $empleadoData['fecha_finalizacion'];
        $empleado->num_doc = $empleadoData['num_doc'];
        $empleado->num_hijos = $empleadoData['num_hijos'];
        $empleado->salario = $empleadoData['salario'];
        $empleado->horas_semanales = $empleadoData['horas_semanales'];
        $empleado->num_cuenta_banco = $empleadoData['num_cuenta_banco'];
        $empleado->num_afiliacion_social = $empleadoData['num_afiliacion_social'];
        $empleado->img_doc=$nameDoc;
        $empleado->foto =$nameFoto;
        $empleado->pdf_hoja = $nameHoja;
        $empleado->pdf_contrato = $nameContrato;
        $empleado->save();
      
        return $empleado;
    }
    
    public function getEmpleados(request $request){
        $empleados = Empleado::whereHas('user.rol', function($q) {
            $q->where('produccion', 1);
        })->with('user.rol')->get();
                
        return $empleados;
    }
    public function selectEmpleado(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $clientes = Empleado::find($filtro);

        return ['empleados' => $empleados];
    }
    public function selectEmpleados(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $empleados = Empleado::where('nombre', 'like', '%'.$filtro.'%')->get();
       
        return $empleados;

    }
    public function borrar(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $empleado= Empleado::find($request->id);
        $user=User::where('empleado_id',$request->id)->first();
        if($user){
            $user->empleado_id=0;
            $user->save();
        }
        $empleado->delete();

    }
}
