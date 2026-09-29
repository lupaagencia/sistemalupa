<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Categoria;
use App\Articulo;
use Illuminate\Support\Facades\File;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //if(!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $all = Categoria::with('padre')->orderBy('condicion', 'asc')->get();
            $categoriasList = $this->ordenarJerarquicamente($all);
        }
        else{
            $categoriasList = Categoria::with('padre')->where($criterio, 'like', '%'. $buscar . '%')->orderBy('condicion', 'asc')->get();
        }
        
        return [
            'pagination' => [
                'total'        => count($categoriasList),
                'current_page' => 1,
                'per_page'     => 1000,
                'last_page'    => 1,
                'from'         => 1,
                'to'           => count($categoriasList),
            ],
            'categorias' => [
                'data' => $categoriasList
            ]
        ];
    }

    private function ordenarJerarquicamente($categorias, $padreId = null, $depth = 0)
    {
        $result = [];
        foreach ($categorias as $cat) {
            $catPadreId = ($cat->padre_id && $cat->padre_id > 0) ? (int)$cat->padre_id : null;
            if ($catPadreId === $padreId) {
                $cat->profundidad = $depth;
                $result[] = $cat;
                $hijos = $this->ordenarJerarquicamente($categorias, (int)$cat->id, $depth + 1);
                foreach ($hijos as $hijo) {
                    $result[] = $hijo;
                }
            }
        }
        if ($padreId === null) {
            $addedIds = array_map(function($c) { return $c->id; }, $result);
            foreach ($categorias as $cat) {
                if (!in_array($cat->id, $addedIds)) {
                    $cat->profundidad = 0;
                    $result[] = $cat;
                }
            }
        }
        return $result;
    }

    private function parseLineHierarchy($cleanText, $xmlLevel)
    {
        // 1. Check numeric dot hierarchy (e.g. 1. Empaques, 1.1 Cajas, 1.1.1 Mesa)
        if (preg_match('/^(\d+(?:\.\d+)*)[\.\)]?\s+(.*)$/u', $cleanText, $mNum)) {
            $dots = substr_count($mNum[1], '.');
            return [
                'text' => trim($mNum[2]),
                'level' => $dots
            ];
        }

        // 2. Check arrow prefix (-> Subcat, --> Sub-subcat, ---> Sub-sub-subcat)
        if (preg_match('/^(-+>)\s*(.*)$/u', $cleanText, $mArrow)) {
            $dashes = strlen($mArrow[1]) - 1;
            return [
                'text' => trim($mArrow[2]),
                'level' => max(1, $dashes)
            ];
        }

        // 3. Check greater-than prefix (> Subcat, >> Sub-subcat, >>> Sub-sub-subcat)
        if (preg_match('/^(>+)\s*(.*)$/u', $cleanText, $mGt)) {
            $gts = strlen($mGt[1]);
            return [
                'text' => trim($mGt[2]),
                'level' => $gts
            ];
        }

        // 4. Check dash prefix (- Subcat, -- Sub-subcat, --- Sub-sub-subcat)
        if (preg_match('/^(-{1,5})\s+(.*)$/u', $cleanText, $mDash)) {
            $dashes = strlen($mDash[1]);
            return [
                'text' => trim($mDash[2]),
                'level' => $dashes
            ];
        }

        // 5. Clean leading bullets
        $cleanText = preg_replace('/^[\-\*\•\–\—\>\·\§\+]\s*/u', '', $cleanText);
        $cleanText = preg_replace('/^\d+[\.\)]\s*/', '', $cleanText);
        $cleanText = trim($cleanText);

        return [
            'text' => $cleanText,
            'level' => $xmlLevel
        ];
    }
    public function categoriaMenu(Request $request)
    {
        // Traer categorias raiz con sus subniveles
        $categoriasMenu = Categoria::where(function($q) {
            $q->whereNull('padre_id')->orWhere('padre_id', 0);
        })->with('sublevels')->orderBy('condicion', 'asc')->get();
        return $categoriasMenu;
    }
    public function categorias(Request $request)
    {
        //if(!$request->ajax()) return redirect('/');
        $categoriasMenu=[];
        $categorias = Categoria::all();
       
        
        return  $categorias;
       
    }

    public function selectCategoria(Request $request){
        if(!$request->ajax()) return redirect('/');
        $categorias=Categoria::select('id', 'padre_id', 'nombre', 'descripcion')->orderBy('condicion', 'asc')->get();
        return['categorias'=>$categorias]; 

    }
    public function articulosCategoria(Request $request){
        if(!$request->ajax()) return redirect('/');
        $catId = $request->id;
        $articulos = Articulo::whereHas('categorias', function($q) use ($catId) {
            $q->where('categorias.id', $catId);
        })->orWhere('idcategoria', $catId)->get();
        return['articulos'=>$articulos]; 

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        if($request->id>0){
            $categoria=Categoria::find($request->id);
        }else{

            $categoria = new Categoria();
        }
        $categoria->nombre = $request->nombre;
        $categoria->descripcion = $request->descripcion;
        $categoria->condicion = $request->condicion;
        $categoria->padre_id = ($request->padre_id && $request->padre_id > 0) ? $request->padre_id : null;
        if ($request->hasFile('imagen')) {
            $rutaImagenAnterior = public_path('img/categorias/' . $request->imagen2);

            if ($request->imagen2 && File::exists($rutaImagenAnterior)) {
                File::delete($rutaImagenAnterior);
            }
            $file = $request->file('imagen');
            $nombreArchivo = $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->imagen = $nombreArchivo;
        }else{
            if($request->imagen2) $categoria->imagen=$request->imagen2;
        }
        if ($request->hasFile('banner')) {
            $rutaBannerAnterior = public_path('img/categorias/' . $request->banner2);

            if ($request->banner2 && File::exists($rutaBannerAnterior)) {
                File::delete($rutaBannerAnterior);
            }
            $file = $request->file('banner');
            $nombreArchivo = 'banner_' . time() . '_' . $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->banner = $nombreArchivo;
        }else{
            if($request->banner2) $categoria->banner=$request->banner2;
        }
        $categoria->save();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        $categoria = Categoria::findOrFail($request->id);
        $categoria->nombre = $request->nombre;
        $categoria->descripcion = $request->descripcion ? $request->descripcion : '';
        if ($request->has('condicion') && $request->condicion !== null) {
            $categoria->condicion = $request->condicion;
        }
        if ($request->has('padre_id')) {
            $categoria->padre_id = ($request->padre_id && $request->padre_id > 0) ? $request->padre_id : null;
        }
        if ($request->hasFile('imagen')) {
            $rutaImagenAnterior = public_path('img/categorias/' . $request->imagen2);

            if ($request->imagen2 && File::exists($rutaImagenAnterior)) {
                File::delete($rutaImagenAnterior);
            }
            $file = $request->file('imagen');
            $nombreArchivo = $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->imagen = $nombreArchivo;
        }
        if ($request->hasFile('banner')) {
            $rutaBannerAnterior = public_path('img/categorias/' . $request->banner2);

            if ($request->banner2 && File::exists($rutaBannerAnterior)) {
                File::delete($rutaBannerAnterior);
            }
            $file = $request->file('banner');
            $nombreArchivo = 'banner_' . time() . '_' . $file->getClientOriginalName();
            $ruta=public_path('img/categorias'); 
            $file->move($ruta, $nombreArchivo);
            $categoria->banner = $nombreArchivo;
        }
        $categoria->save();
        return response()->json(['status' => 'ok', 'categoria' => $categoria]);
    }
    
    public function eliminar(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar categorías.'], 403);
        }
        if (!$request->ajax()) return redirect('/');
        $articulos=Articulo::select('*')->where('idcategoria', $request->id)->get();
        foreach($articulos as $a){
            $a->idcategoria=$request->updateid;
            $a->save();
        }
        $categoria = Categoria::findOrFail($request->id);
        $categoria->delete();
       
    }
    public function eliminarC(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar categorías.'], 403);
        }
        if (!$request->ajax()) return redirect('/');
        $categoria = Categoria::findOrFail($request->id);
        $categoria->delete();
       
    }

    public function activar(Request $request)
    {
        if(!$request->ajax()) return redirect('/');
        $categoria = Categoria::findOrFail($request->id);
        $categoria->condicion = '1';
        $categoria->save();
    }

    public function importarWord(Request $request)
    {
        if (!$request->hasFile('file') && !$request->hasFile('archivo_word')) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se ha seleccionado ningún archivo (.docx o .txt)'
            ], 400);
        }

        $file = $request->file('file') ? $request->file('file') : $request->file('archivo_word');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['docx', 'txt'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'El archivo debe estar en formato Word (.docx) o Texto (.txt)'
            ], 422);
        }

        $paragraphs = [];

        if ($extension === 'txt') {
            $rawText = file_get_contents($file->getRealPath());
            // UTF-8 conversion if needed
            if (!mb_check_encoding($rawText, 'UTF-8')) {
                $rawText = mb_convert_encoding($rawText, 'UTF-8', 'ISO-8859-1, WINDOWS-1252');
            }
            $lines = explode("\n", $rawText);
            foreach ($lines as $line) {
                $lineContent = rtrim($line, "\r\n");
                if (trim($lineContent) === '') continue;

                $leadingTabs = 0;
                $leadingSpaces = 0;
                $len = strlen($lineContent);
                $cleanText = '';

                for ($i = 0; $i < $len; $i++) {
                    $ch = $lineContent[$i];
                    if ($ch === "\t") {
                        $leadingTabs++;
                    } else if ($ch === ' ') {
                        $leadingSpaces++;
                    } else {
                        $cleanText = substr($lineContent, $i);
                        break;
                    }
                }

                $cleanText = trim($cleanText);
                if (empty($cleanText)) continue;

                $spaceLevel = (int)floor($leadingSpaces / 2);
                $tabLevel = max($leadingTabs, $spaceLevel);

                $parsed = $this->parseLineHierarchy($cleanText, $tabLevel);
                $paragraphs[] = $parsed;
            }
        } else {
            // Processing Word .docx
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo abrir el archivo Word .docx'
                ], 422);
            }

            $documentXml = $zip->getFromName('word/document.xml');
            $zip->close();

            if (!$documentXml) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El archivo .docx no contiene un formato de texto válido'
                ], 422);
            }

            preg_match_all('/<w:p\b[^>]*>(.*?)<\/w:p>/s', $documentXml, $matches);

            foreach ($matches[0] as $pXml) {
                $pDom = new \DOMDocument();
                @$pDom->loadXML('<root xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . $pXml . '</root>');
                $xpath = new \DOMXPath($pDom);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                // 1. List Level
                $listLevel = null;
                $ilvlNodes = $xpath->query('//w:pPr/w:numPr/w:ilvl/@w:val');
                if ($ilvlNodes->length > 0) {
                    $listLevel = (int)$ilvlNodes->item(0)->nodeValue;
                }

                // 2. Indent twips
                $indLevel = null;
                $indTwipsNodes = $xpath->query('//w:pPr/w:ind/@w:left | //w:pPr/w:ind/@w:firstLine | //w:pPr/w:ind/@w:hanging');
                if ($indTwipsNodes->length > 0) {
                    $maxTwips = 0;
                    foreach ($indTwipsNodes as $node) {
                        $val = (int)$node->nodeValue;
                        if ($val > $maxTwips) $maxTwips = $val;
                    }
                    if ($maxTwips >= 180) {
                        $indLevel = (int)max(1, round($maxTwips / 720.0));
                    }
                }
                $indCharsNodes = $xpath->query('//w:pPr/w:ind/@w:leftChars | //w:pPr/w:ind/@w:firstLineChars');
                if ($indCharsNodes->length > 0) {
                    $maxChars = 0;
                    foreach ($indCharsNodes as $node) {
                        $val = (int)$node->nodeValue;
                        if ($val > $maxChars) $maxChars = $val;
                    }
                    if ($maxChars >= 50) {
                        $charLevel = (int)max(1, round($maxChars / 100.0));
                        $indLevel = ($indLevel !== null) ? max($indLevel, $charLevel) : $charLevel;
                    }
                }

                // 3. Style
                $styleLevel = null;
                $styleNodes = $xpath->query('//w:pPr/w:pStyle/@w:val');
                if ($styleNodes->length > 0) {
                    $styleVal = $styleNodes->item(0)->nodeValue;
                    if (preg_match('/(?:Heading|T[ií]tulo)\s*(\d+)/i', $styleVal, $mStyle)) {
                        $styleLevel = max(0, (int)$mStyle[1] - 1);
                    }
                }

                // 4. Runs for typed tabs and leading text spaces/tabs
                $pText = '';
                $leadingTabsCount = 0;
                $leadingSpacesCount = 0;
                $hasEncounteredText = false;

                $runNodes = $xpath->query('//w:r');
                if ($runNodes->length > 0) {
                    foreach ($runNodes as $runNode) {
                        foreach ($runNode->childNodes as $child) {
                            if ($child->localName === 'tab') {
                                if (!$hasEncounteredText) {
                                    $leadingTabsCount++;
                                }
                                $pText .= "\t";
                            } else if ($child->localName === 't') {
                                $txt = str_replace(["\xc2\xa0", "\xa0"], ' ', $child->nodeValue);
                                if (!$hasEncounteredText) {
                                    $len = strlen($txt);
                                    for ($i = 0; $i < $len; $i++) {
                                        $ch = $txt[$i];
                                        if ($ch === "\t") {
                                            $leadingTabsCount++;
                                        } else if ($ch === ' ') {
                                            $leadingSpacesCount++;
                                        } else {
                                            $hasEncounteredText = true;
                                            $pText .= substr($txt, $i);
                                            break;
                                        }
                                    }
                                } else {
                                    $pText .= $txt;
                                }
                            }
                        }
                    }
                } else {
                    $pText = $pDom->textContent;
                }

                $cleanText = trim($pText);
                if (empty($cleanText)) continue;

                $spaceLevel = (int)floor($leadingSpacesCount / 2);

                $xmlLevel = 0;
                if ($listLevel !== null) {
                    $xmlLevel = max($xmlLevel, $listLevel);
                }
                if ($indLevel !== null) {
                    $xmlLevel = max($xmlLevel, $indLevel);
                }
                if ($styleLevel !== null) {
                    $xmlLevel = max($xmlLevel, $styleLevel);
                }
                $xmlLevel = max($xmlLevel, $leadingTabsCount, $spaceLevel);

                $parsed = $this->parseLineHierarchy($cleanText, $xmlLevel);
                $paragraphs[] = $parsed;
            }
        }

        if (empty($paragraphs)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontraron líneas de categorías en el archivo'
            ], 422);
        }

        // Normalize levels sequentially (relative indent mapping)
        $rawToNorm = [0 => 0];
        $lastRaw = 0;
        $lastNorm = 0;

        foreach ($paragraphs as &$p) {
            $raw = $p['level'];
            if ($raw === 0) {
                $norm = 0;
                $rawToNorm = [0 => 0];
            } else if ($raw > $lastRaw) {
                $norm = $lastNorm + 1;
                $rawToNorm[$raw] = $norm;
            } else if (isset($rawToNorm[$raw])) {
                $norm = $rawToNorm[$raw];
            } else {
                $found = 0;
                foreach ($rawToNorm as $rKey => $nVal) {
                    if ($rKey <= $raw && $nVal > $found) {
                        $found = $nVal;
                    }
                }
                $norm = $found;
                $rawToNorm[$raw] = $norm;
            }
            $p['level'] = $norm;
            $lastRaw = $raw;
            $lastNorm = $norm;
        }
        unset($p);

        $levelMap = [];
        $creadas = 0;
        $actualizadas = 0;
        $processedCategoryIds = [];

        foreach ($paragraphs as $pData) {
            $nombre = trim($pData['text']);
            if (empty($nombre)) continue;

            $level = $pData['level'];

            $padreId = null;
            if ($level > 0) {
                for ($l = $level - 1; $l >= 0; $l--) {
                    if (isset($levelMap[$l])) {
                        $padreId = $levelMap[$l];
                        break;
                    }
                }
            }

            $categoria = Categoria::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])->first();

            if (!$categoria) {
                $categoria = new Categoria();
                $categoria->nombre = $nombre;
                $categoria->descripcion = $nombre;
                $categoria->condicion = 1;
                $categoria->padre_id = $padreId;
                $categoria->save();
                $creadas++;
            } else {
                $categoria->padre_id = $padreId;
                $categoria->save();
                $actualizadas++;
            }

            $processedCategoryIds[] = $categoria->id;

            $levelMap[$level] = $categoria->id;
            foreach (array_keys($levelMap) as $l) {
                if ($l > $level) {
                    unset($levelMap[$l]);
                }
            }
        }

        $eliminadas = 0;
        $accionNoIncluidas = $request->input('accion_no_incluidas', 'eliminar');

        if ($accionNoIncluidas === 'eliminar' && !empty($processedCategoryIds)) {
            $fallbackCatId = $processedCategoryIds[0];

            Categoria::whereNotIn('id', $processedCategoryIds)->update(['padre_id' => null]);

            $categoriasAEliminar = Categoria::whereNotIn('id', $processedCategoryIds)->get();
            foreach ($categoriasAEliminar as $catElim) {
                Articulo::where('idcategoria', $catElim->id)->update(['idcategoria' => $fallbackCatId]);
                \DB::table('articulo_categoria')->where('categoria_id', $catElim->id)->delete();
                $catElim->delete();
                $eliminadas++;
            }
        }

        $msg = "Estructura de categorías importada con éxito. Creadas: {$creadas}, Actualizadas: {$actualizadas}.";
        if ($eliminadas > 0) {
            $msg .= " Se eliminaron {$eliminadas} categoría(s) no presentes en el documento Word.";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'creadas' => $creadas,
            'actualizadas' => $actualizadas,
            'eliminadas' => $eliminadas
        ]);
    }
}
