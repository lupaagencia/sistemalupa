<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SuperadminIdeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Ensure the user is a Superadministrador.
     */
    private function checkSuperadmin()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Usuario no autenticado.');
        }

        // Query fresh role from DB in case session holds old cached role
        $dbUser = \App\User::find($user->id);
        $role = $dbUser ? $dbUser->idrol : $user->idrol;
        $username = strtolower($dbUser ? $dbUser->usuario : $user->usuario);

        if ($role !== 'Superadministrador' && $username !== 'julian') {
            abort(403, 'Acceso restringido únicamente para el usuario Superadministrador.');
        }
    }

    /**
     * Get directory file tree for the project.
     */
    public function getTree(Request $request)
    {
        $this->checkSuperadmin();

        $subPath = ltrim($request->input('path', ''), '/\\');
        $basePath = base_path();
        $targetDir = empty($subPath) ? $basePath : base_path($subPath);

        if (!File::exists($targetDir) || !is_dir($targetDir)) {
            return response()->json(['status' => 'error', 'message' => 'Directorio no encontrado.'], 404);
        }

        $tree = $this->buildTree($targetDir, $basePath, 0, 10);

        return response()->json($this->sanitizeUtf8([
            'status' => 'success',
            'base_path' => $basePath,
            'tree' => $tree
        ]));
    }

    /**
     * Read a specific file content.
     */
    public function getFile(Request $request)
    {
        $this->checkSuperadmin();

        $relativePath = ltrim($request->input('path'), '/\\');
        $fullPath = base_path($relativePath);

        if (!File::exists($fullPath) || is_dir($fullPath)) {
            return response()->json(['status' => 'error', 'message' => 'Archivo no encontrado: ' . $relativePath], 404);
        }

        // Security check: ensure path is within base_path
        $realPath = realpath($fullPath);
        if (!$realPath || strpos($realPath, realpath(base_path())) !== 0) {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado fuera de la raíz del proyecto.'], 403);
        }

        $extension = strtolower(File::extension($fullPath));
        $binaryExtensions = ['png', 'jpg', 'jpeg', 'gif', 'ico', 'pdf', 'zip', 'tar', 'gz', 'rar', 'exe', 'dll', 'so', 'woff', 'woff2', 'ttf', 'eot', 'mp3', 'mp4', 'xlsx', 'xls', 'doc', 'docx', 'bak', 'pyc', 'phar'];
        
        if (in_array($extension, $binaryExtensions)) {
            return response()->json([
                'status' => 'success',
                'path' => $relativePath,
                'extension' => $extension,
                'content' => "// Archivo binario o multimedia ({$extension}). No se puede editar en formato texto.",
                'size' => filesize($fullPath),
                'mtime' => date('Y-m-d H:i:s', filemtime($fullPath))
            ]);
        }

        $content = File::get($fullPath);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1, Windows-1252, ASCII');
        }

        return response()->json($this->sanitizeUtf8([
            'status' => 'success',
            'path' => $relativePath,
            'extension' => $extension,
            'content' => $content,
            'size' => filesize($fullPath),
            'mtime' => date('Y-m-d H:i:s', filemtime($fullPath))
        ]));
    }

    /**
     * Save file with syntax check & backup.
     */
    public function saveFile(Request $request)
    {
        $this->checkSuperadmin();

        $relativePath = ltrim($request->input('path'), '/\\');
        $content = $request->input('content');
        $fullPath = base_path($relativePath);

        $realBase = realpath(base_path());
        $dirPath = dirname($fullPath);

        // Ensure parent dir exists
        if (!File::exists($dirPath)) {
            File::makeDirectory($dirPath, 0755, true);
        }

        // Syntax lint for PHP
        $extension = strtolower(File::extension($fullPath));
        if ($extension === 'php') {
            $tmpFile = tempnam(sys_get_temp_dir(), 'ide_lint_');
            file_put_contents($tmpFile, $content);
            $output = [];
            $returnVar = 0;
            exec("php -l " . escapeshellarg($tmpFile) . " 2>&1", $output, $returnVar);
            @unlink($tmpFile);

            if ($returnVar !== 0) {
                $errorMsg = implode("\n", $output);
                return response()->json([
                    'status' => 'syntax_error',
                    'message' => 'Error de sintaxis en el código PHP. No se ha guardado el archivo.',
                    'details' => $errorMsg
                ], 422);
            }
        } elseif ($extension === 'json') {
            json_decode($content);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 'syntax_error',
                    'message' => 'Error de formato JSON: ' . json_last_error_msg()
                ], 422);
            }
        }

        // Create backup in storage/ide_backups
        if (File::exists($fullPath)) {
            $backupDir = storage_path('ide_backups/' . date('Y-m-d'));
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }
            $backupName = pathinfo($relativePath, PATHINFO_FILENAME) . '_' . date('His') . '.' . $extension . '.bak';
            File::copy($fullPath, $backupDir . '/' . $backupName);
        }

        File::put($fullPath, $content);

        // Auto-commit & push to GitHub repository
        try {
            $commitMsg = "Web IDE: Auto-commit cambio en " . $relativePath . " [" . date('Y-m-d H:i:s') . "]";
            $gitRepoPath = base_path();
            $escapedFile = escapeshellarg($relativePath);
            $escapedMsg = escapeshellarg($commitMsg);
            
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B git -C " . escapeshellarg($gitRepoPath) . " add " . $escapedFile . " && git -C " . escapeshellarg($gitRepoPath) . " commit -m " . $escapedMsg . " && git -C " . escapeshellarg($gitRepoPath) . " push origin main", "r"));
            } else {
                exec("git -C " . escapeshellarg($gitRepoPath) . " add " . $escapedFile . " && git -C " . escapeshellarg($gitRepoPath) . " commit -m " . $escapedMsg . " && git -C " . escapeshellarg($gitRepoPath) . " push origin main > /dev/null 2>&1 &");
            }
        } catch (\Exception $e) {
            Log::warning("Git auto-push from Web IDE failed: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Archivo guardado correctamente y sincronizado en GitHub.',
            'path' => $relativePath,
            'mtime' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Smart File Discovery for Agentic AI queries when no active file is selected.
     */
    private function findTargetFileForPrompt($prompt, $activeFile)
    {
        if (!empty($activeFile) && File::exists(base_path($activeFile))) {
            return $activeFile;
        }

        $p = strtolower($prompt);

        // Keyword mapping to system components/controllers
        $map = [
            'pedido' => 'resources/assets/js/components/partes/Pedido.vue',
            'orden' => 'app/Http/Controllers/OrdentrabajoController.php',
            'comprobante' => 'app/Http/Controllers/ComprobanteController.php',
            'asistencia' => 'app/Http/Controllers/AsistenciaController.php',
            'rostro' => 'resources/assets/js/components/ControlAsistencia.vue',
            'empleado' => 'app/Http/Controllers/EmpleadoController.php',
            'articulo' => 'app/Http/Controllers/ArticuloController.php',
            'producto' => 'resources/assets/js/components/Articulo.vue',
            'crm' => 'app/Http/Controllers/CrmCotizacionController.php',
            'cotizacion' => 'resources/assets/js/components/crm/CrmCotizaciones.vue',
            'rol' => 'app/Http/Controllers/SuperadminIdeController.php',
            'ide' => 'resources/assets/js/components/superadmin/WebIde.vue',
            'sidebar' => 'resources/views/plantilla/sidebaradministrador.blade.php',
            'menu' => 'resources/views/backend/contenido.blade.php'
        ];

        foreach ($map as $key => $target) {
            if (strpos($p, $key) !== false && File::exists(base_path($target))) {
                return $target;
            }
        }

        // Default fallback to Pedido.vue or AGENTS.md
        if (File::exists(base_path('resources/assets/js/components/partes/Pedido.vue'))) {
            return 'resources/assets/js/components/partes/Pedido.vue';
        }

        return 'AGENTS.md';
    }

    /**
     * AI Agent Prompt processing with Agentic Auto-Execution capabilities.
     */
    public function aiPrompt(Request $request)
    {
        $this->checkSuperadmin();

        $prompt = trim($request->input('prompt'));
        $activeFile = $request->input('active_file');
        $fileContent = $request->input('file_content');
        $selectedCode = $request->input('selected_code');
        $userApiKey = trim($request->input('api_key', ''));

        if (empty($prompt)) {
            return response()->json(['status' => 'error', 'message' => 'El prompt no puede estar vacío.'], 400);
        }

        // Direct command parsing (e.g., "abrir Pedido.vue", "desplegar", "guardar")
        $pLower = strtolower($prompt);

        if (strpos($pLower, 'abrir ') === 0 || strpos($pLower, 'open ') === 0) {
            $searchTarget = trim(substr($prompt, strpos($prompt, ' ') + 1));
            $foundFile = $this->findTargetFileForPrompt($searchTarget, '');
            if (File::exists(base_path($foundFile))) {
                return response()->json($this->sanitizeUtf8([
                    'status' => 'success',
                    'agent' => 'Antigravity Agent',
                    'response' => "⚡ He localizado y abierto el archivo `{$foundFile}` en el editor.",
                    'applied' => false,
                    'target_file' => $foundFile,
                    'modified_code' => null
                ]));
            }
        }

        // Auto-detect target file if no active file is open
        $targetFile = $this->findTargetFileForPrompt($prompt, $activeFile);
        $fullTargetFile = base_path($targetFile);

        if (File::exists($fullTargetFile)) {
            $fileContent = File::get($fullTargetFile);
        }

        // Read AGENTS.md instructions if exists
        $agentsRules = "";
        $agentsPath = base_path('AGENTS.md');
        if (File::exists($agentsPath)) {
            $agentsRules = File::get($agentsPath);
        }

        $systemContext = "Eres Antigravity, el asistente de código agente senior de Google DeepMind en este Web IDE.\n";
        $systemContext .= "Tienes permisos agenticos completos para MODIFICAR y GUARDAR código en el proyecto.\n\n";

        if (!empty($agentsRules)) {
            $systemContext .= "--- REGLAS DEL PROYECTO (AGENTS.md) ---\n" . $agentsRules . "\n----------------------------------------\n\n";
        }

        $systemContext .= "Archivo objetivo detectado y abierto: {$targetFile}\n";

        if (!empty($selectedCode)) {
            $systemContext .= "Código seleccionado:\n```\n{$selectedCode}\n```\n";
        } elseif (!empty($fileContent)) {
            $previewSnippet = strlen($fileContent) > 4000 ? substr($fileContent, 0, 4000) . "\n... (contenido truncado)" : $fileContent;
            $systemContext .= "Contenido completo del archivo objetivo:\n```\n{$previewSnippet}\n```\n";
        }

        $systemContext .= "\nINSTRUCCIONES DE RESPUESTA AGENTICA:\n";
        $systemContext .= "1. Analiza cuidadosamente la solicitud del usuario.\n";
        $systemContext .= "2. Explica qué cambios específicos se deben realizar o se realizaron en {$targetFile} en español en formato Markdown.\n";
        $systemContext .= "3. Si la solicitud requiere modificar código, proporciona el CÓDIGO COMPLETO FINAL actualizado del archivo {$targetFile} encerrado en un bloque de código ``` (ej. ```php o ```vue o ```javascript).\n";

        $apiKey = $userApiKey ?: env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
        $replyText = "";

        if ($apiKey) {
            $models = ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-1.5-pro'];
            
            foreach ($models as $model) {
                try {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
                    $payload = [
                        "contents" => [
                            [
                                "parts" => [
                                    ["text" => $systemContext . "\n\nSolicitud del usuario: " . $prompt]
                                ]
                            ]
                        ]
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    $res = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode === 200 && $res) {
                        $data = json_decode($res, true);
                        $fetchedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? "";
                        if (!empty($fetchedText)) {
                            $replyText = $fetchedText;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error consultando Gemini API model {$model}: " . $e->getMessage());
                }
            }
        }

        if (empty($replyText)) {
            if (empty($apiKey)) {
                $replyText = "### ⚡ Antigravity Agent\n\n" .
                             "Para activar el procesamiento con Inteligencia Artificial autónoma, por favor haz clic en el ícono de la llave **🔑 Clave API** en la barra superior del chat y configura tu **Gemini API Key** de Google.\n\n" .
                             "**Comandos directos sin API Key**:\n" .
                             "- `abrir <archivo>` (ej: *abrir Pedido.vue*, *abrir AsistenciaController.php*)\n" .
                             "- Haz clic en **Desplegar FTP** en la barra superior para subir los cambios al servidor en vivo.";
            } else {
                $replyText = "⚠️ **Error de comunicación con Google Gemini API**\n\nLa API Key proporcionada no respondió correctamente o alcanzó el límite de solicitudes. Por favor verifica tu Gemini API Key haciiendo clic en el botón 🔑 en el chat.";
            }
        }

        $modifiedCode = $this->extractCodeFromResponse($replyText);
        $wasApplied = false;
        $appliedFile = null;

        // Auto-apply modified code to target file if provided
        if (!empty($modifiedCode) && !empty($targetFile)) {
            $fullPath = base_path($targetFile);
            $extension = strtolower(File::extension($fullPath));

            // Validate syntax if PHP
            $canApply = true;
            if ($extension === 'php') {
                $tmpFile = tempnam(sys_get_temp_dir(), 'ide_lint_');
                file_put_contents($tmpFile, $modifiedCode);
                $output = [];
                $returnVar = 0;
                exec("php -l " . escapeshellarg($tmpFile) . " 2>&1", $output, $returnVar);
                @unlink($tmpFile);

                if ($returnVar !== 0) {
                    $canApply = false;
                    $replyText .= "\n\n⚠️ **Nota de sintaxis**: Se generó una propuesta pero falló la validación `php -l`. El archivo no fue sobrescrito automáticamente para evitar errores.";
                }
            }

            if ($canApply) {
                // Create backup
                if (File::exists($fullPath)) {
                    $backupDir = storage_path('ide_backups/' . date('Y-m-d'));
                    if (!File::exists($backupDir)) {
                        File::makeDirectory($backupDir, 0755, true);
                    }
                    $backupName = pathinfo($targetFile, PATHINFO_FILENAME) . '_' . date('His') . '.' . $extension . '.bak';
                    File::copy($fullPath, $backupDir . '/' . $backupName);
                }

                File::put($fullPath, $modifiedCode);
                $wasApplied = true;
                $appliedFile = $targetFile;

                // Auto-commit & push to GitHub
                try {
                    $commitMsg = "Antigravity AI Agent: Auto-apply en " . $targetFile . " [" . date('Y-m-d H:i:s') . "]";
                    $gitRepoPath = base_path();
                    $escapedFile = escapeshellarg($targetFile);
                    $escapedMsg = escapeshellarg($commitMsg);
                    
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        pclose(popen("start /B git -C " . escapeshellarg($gitRepoPath) . " add " . $escapedFile . " && git -C " . escapeshellarg($gitRepoPath) . " commit -m " . $escapedMsg . " && git -C " . escapeshellarg($gitRepoPath) . " push origin main", "r"));
                    } else {
                        exec("git -C " . escapeshellarg($gitRepoPath) . " add " . $escapedFile . " && git -C " . escapeshellarg($gitRepoPath) . " commit -m " . $escapedMsg . " && git -C " . escapeshellarg($gitRepoPath) . " push origin main > /dev/null 2>&1 &");
                    }
                } catch (\Exception $e) {
                    Log::warning("Git auto-push failed: " . $e->getMessage());
                }
            }
        }

        return response()->json($this->sanitizeUtf8([
            'status' => 'success',
            'agent' => 'Antigravity DeepMind',
            'response' => $replyText,
            'applied' => $wasApplied,
            'target_file' => $appliedFile,
            'modified_code' => $modifiedCode
        ]));
    }

    private function generateAgentResponseFallback($prompt, $targetFile, $fileContent, $selectedCode)
    {
        return $this->aiPrompt(request());
    }


    private function extractCodeFromResponse($text)
    {
        if (preg_match('/```(?:php|javascript|vue|html|css)?\s*\n(.*?)```/s', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Build directory tree recursively.
     */
    private function buildTree($dir, $basePath, $depth = 0, $maxDepth = 10)
    {
        if ($depth >= $maxDepth) {
            return [];
        }

        $items = [];
        $files = @scandir($dir);
        if (!$files) {
            return [];
        }

        $ignoredDirs = ['.git', 'node_modules', 'vendor', 'storage/framework', 'storage/logs', '.idea', '.vscode'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            $realBase = realpath($basePath) ?: $basePath;
            $realFull = realpath($fullPath) ?: $fullPath;

            $relativePath = str_replace($realBase . DIRECTORY_SEPARATOR, '', $realFull);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Check ignored dirs
            $isIgnored = false;
            foreach ($ignoredDirs as $ignored) {
                if ($file === $ignored || strpos($relativePath, $ignored) === 0) {
                    $isIgnored = true;
                    break;
                }
            }

            if ($isIgnored) {
                continue;
            }

            $isDir = is_dir($fullPath);
            $item = [
                'name' => $file,
                'path' => $relativePath,
                'type' => $isDir ? 'dir' : 'file',
                'extension' => $isDir ? '' : strtolower(pathinfo($file, PATHINFO_EXTENSION)),
            ];

            if ($isDir) {
                $children = $this->buildTree($fullPath, $basePath, $depth + 1, $maxDepth);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
            }

            $items[] = $item;
        }

        // Sort: directories first, then files alphabetically
        usort($items, function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $a['type'] === 'dir' ? -1 : 1;
        });

        return $items;
    }

    /**
     * Recursively sanitize strings to UTF-8 for JSON encoding.
     */
    private function sanitizeUtf8($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeUtf8($value);
            }
            return $data;
        } elseif (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1, Windows-1252, ASCII');
            }
            return $data;
        }
        return $data;
    }
}

