<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Slider;
use Illuminate\Support\Facades\File;

class SliderController extends Controller
{
    /**
     * Obtener listado de sliders
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $ubicacion = $request->ubicacion ? $request->ubicacion : 'principal';

        if ($buscar == '') {
            $sliders = Slider::where('ubicacion', $ubicacion)
                ->orderBy('orden', 'asc')
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $sliders = Slider::where('ubicacion', $ubicacion)
                ->where($criterio, 'like', '%' . $buscar . '%')
                ->orderBy('orden', 'asc')
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return [
            'pagination' => [
                'total'        => $sliders->total(),
                'current_page' => $sliders->currentPage(),
                'per_page'     => $sliders->perPage(),
                'last_page'    => $sliders->lastPage(),
                'from'         => $sliders->firstItem(),
                'to'           => $sliders->lastItem(),
            ],
            'sliders' => $sliders
        ];
    }

    /**
     * Obtener sliders activos para la página web pública
     */
    /**
     * Obtener sliders activos para la página web pública
     */
    public function publicos(Request $request)
    {
        $this->verificarEsquema();

        $ubicacion = $request->ubicacion ? $request->ubicacion : 'principal';

        $sliders = Slider::where(function ($query) use ($ubicacion) {
                $query->where('ubicacion', $ubicacion)
                      ->orWhereNull('ubicacion')
                      ->orWhere('ubicacion', '');
            })
            ->where('estado', 1)
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Fallback: traer todos los activos si no se especificó ubicación
        if ($sliders->isEmpty()) {
            $sliders = Slider::where('estado', 1)
                ->orderBy('orden', 'asc')
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json($sliders);
    }

    /**
     * Verificar que las columnas existan en la base de datos
     */
    private function verificarEsquema()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('sliders', 'imagen_movil')) {
                \Illuminate\Support\Facades\Schema::table('sliders', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('imagen_movil', 255)->nullable()->after('imagen');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('sliders', 'alto_slider')) {
                \Illuminate\Support\Facades\Schema::table('sliders', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->integer('alto_slider')->nullable()->default(450)->after('orden');
                });
            }
            // Asegurar que sliders sin ubicación tengan 'principal'
            Slider::whereNull('ubicacion')->orWhere('ubicacion', '')->update(['ubicacion' => 'principal']);
        } catch (\Exception $e) {
            // Silencioso si hay error
        }
    }

    /**
     * Guardar nuevo slider
     */
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $this->verificarEsquema();

        $request->validate([
            'imagen' => 'required'
        ]);

        $slider = new Slider();
        $slider->titulo = $request->titulo;
        $slider->subtitulo = $request->subtitulo;
        $slider->texto_boton = $request->texto_boton;
        $slider->url_boton = $request->url_boton;
        $slider->ubicacion = $request->ubicacion ? $request->ubicacion : 'principal';
        $slider->orden = $request->orden ? intval($request->orden) : 0;
        $slider->alto_slider = $request->alto_slider ? intval($request->alto_slider) : 450;
        $slider->estado = 1;

        // Procesar imagen principal (desktop)
        if ($request->hasFile('imagen')) {
            $imagenFile = $request->file('imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagenFile->getClientOriginalExtension();
            $path = public_path('img/sliders');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $imagenFile->move($path, $nombreImagen);
            $slider->imagen = 'img/sliders/' . $nombreImagen;
        }

        // Procesar imagen móvil (opcional)
        if ($request->hasFile('imagen_movil')) {
            $imagenMovilFile = $request->file('imagen_movil');
            $nombreImagenMovil = time() . '_movil_' . uniqid() . '.' . $imagenMovilFile->getClientOriginalExtension();
            $path = public_path('img/sliders');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $imagenMovilFile->move($path, $nombreImagenMovil);
            $slider->imagen_movil = 'img/sliders/' . $nombreImagenMovil;
        }

        $slider->save();

        return ['status' => 'success', 'message' => 'Slider registrado exitosamente.'];
    }

    /**
     * Actualizar slider existente
     */
    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $this->verificarEsquema();

        $slider = Slider::findOrFail($request->id);
        $slider->titulo = $request->titulo;
        $slider->subtitulo = $request->subtitulo;
        $slider->texto_boton = $request->texto_boton;
        $slider->url_boton = $request->url_boton;
        $slider->ubicacion = $request->ubicacion ? $request->ubicacion : 'principal';
        $slider->orden = $request->orden ? intval($request->orden) : 0;
        $slider->alto_slider = $request->alto_slider ? intval($request->alto_slider) : 450;

        // Si se subió una nueva imagen principal
        if ($request->hasFile('imagen')) {
            if ($slider->imagen && File::exists(public_path($slider->imagen))) {
                File::delete(public_path($slider->imagen));
            }

            $imagenFile = $request->file('imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagenFile->getClientOriginalExtension();
            $path = public_path('img/sliders');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $imagenFile->move($path, $nombreImagen);
            $slider->imagen = 'img/sliders/' . $nombreImagen;
        }

        // Si se subió una nueva imagen móvil
        if ($request->hasFile('imagen_movil')) {
            if ($slider->imagen_movil && File::exists(public_path($slider->imagen_movil))) {
                File::delete(public_path($slider->imagen_movil));
            }

            $imagenMovilFile = $request->file('imagen_movil');
            $nombreImagenMovil = time() . '_movil_' . uniqid() . '.' . $imagenMovilFile->getClientOriginalExtension();
            $path = public_path('img/sliders');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $imagenMovilFile->move($path, $nombreImagenMovil);
            $slider->imagen_movil = 'img/sliders/' . $nombreImagenMovil;
        }

        $slider->save();

        return ['status' => 'success', 'message' => 'Slider actualizado exitosamente.'];
    }

    /**
     * Desactivar slider
     */
    public function desactivar(Request $request)
    {
        $slider = Slider::findOrFail($request->id);
        $slider->estado = 0;
        $slider->save();
        return response()->json(['status' => 'success', 'message' => 'Slider desactivado.']);
    }

    /**
     * Activar slider
     */
    public function activar(Request $request)
    {
        $slider = Slider::findOrFail($request->id);
        $slider->estado = 1;
        $slider->save();
        return response()->json(['status' => 'success', 'message' => 'Slider activado.']);
    }

    /**
     * Eliminar slider
     */
    public function destroy(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $slider = Slider::findOrFail($request->id);
        if ($slider->imagen && File::exists(public_path($slider->imagen))) {
            File::delete(public_path($slider->imagen));
        }
        if ($slider->imagen_movil && File::exists(public_path($slider->imagen_movil))) {
            File::delete(public_path($slider->imagen_movil));
        }
        $slider->delete();
        return ['status' => 'success', 'message' => 'Slider eliminado.'];
    }
}
