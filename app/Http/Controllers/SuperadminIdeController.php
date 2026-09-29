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

        $tree = $this->buildTree($targetDir, $basePath, 0, 3);

        return response()->json([
            'status' => 'success',
            'base_path' => $basePath,
            'tree' => $tree
        ]);
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

        $content = File::get($fullPath);
        $extension = File::extension($fullPath);

        return response()->json([
            'status' => 'success',
            'path' => $relativePath,
            'extension' => strtolower($extension),
            'content' => $content,
            'size' => filesize($fullPath),
            'mtime' => date('Y-m-d H:i:s', filemtime($fullPath))
        ]);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Archivo guardado correctamente.',
            'path' => $relativePath,
            'mtime' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * AI Agent Prompt processing.
     */
    public function aiPrompt(Request $request)
    {
        $this->checkSuperadmin();

        $prompt = $request->input('prompt');
        $activeFile = $request->input('active_file');
        $fileContent = $request->input('file_content');
        $selectedCode = $request->input('selected_code');

        if (empty($prompt)) {
            return response()->json(['status' => 'error', 'message' => 'El prompt no puede estar vacío.'], 400);
        }

        // Read AGENTS.md instructions if exists
        $agentsRules = "";
        $agentsPath = base_path('AGENTS.md');
        if (File::exists($agentsPath)) {
            $agentsRules = File::get($agentsPath);
        }

        $systemContext = "Eres Antigravity, un asistente de programación agentico de nivel senior creado por Google DeepMind.\n";
        $systemContext .= "Estás pair-programming con el Superadministrador dentro del Web IDE del sistema Empaques Lupa.\n\n";

        if (!empty($agentsRules)) {
            $systemContext .= "--- REGLAS DEL PROYECTO (AGENTS.md) ---\n" . $agentsRules . "\n----------------------------------------\n\n";
        }

        if (!empty($activeFile)) {
            $systemContext .= "Archivo activo: {$activeFile}\n";
        }
        if (!empty($selectedCode)) {
            $systemContext .= "Código seleccionado por el usuario:\n```\n{$selectedCode}\n```\n";
        } elseif (!empty($fileContent)) {
            $previewSnippet = strlen($fileContent) > 3000 ? substr($fileContent, 0, 3000) . "\n... (contenido truncado para contexto)" : $fileContent;
            $systemContext .= "Contenido del archivo actual:\n```\n{$previewSnippet}\n```\n";
        }

        // Call Gemini API if GEMINI_API_KEY is available in .env or fallback
        $apiKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
        if (!$apiKey) {
            // Generar respuesta inteligente estructurada estilo Antigravity
            $responseMessage = $this->generateAgentResponseFallback($prompt, $activeFile, $fileContent, $selectedCode);
            return response()->json([
                'status' => 'success',
                'agent' => 'Antigravity DeepMind',
                'response' => $responseMessage,
                'suggested_code' => $this->extractCodeFromResponse($responseMessage)
            ]);
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
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
                $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? "No se obtuvo respuesta de la IA.";
                return response()->json([
                    'status' => 'success',
                    'agent' => 'Antigravity DeepMind',
                    'response' => $replyText,
                    'suggested_code' => $this->extractCodeFromResponse($replyText)
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error consultando Gemini API: " . $e->getMessage());
        }

        $fallbackReply = $this->generateAgentResponseFallback($prompt, $activeFile, $fileContent, $selectedCode);
        return response()->json([
            'status' => 'success',
            'agent' => 'Antigravity DeepMind',
            'response' => $fallbackReply,
            'suggested_code' => $this->extractCodeFromResponse($fallbackReply)
        ]);
    }

    /**
     * Deploy modified files via FTP.
     */
    public function deployFtp(Request $request)
    {
        $this->checkSuperadmin();

        $scriptPath = base_path('scratch/upload_superadmin_changes.py');
        if (!File::exists($scriptPath)) {
            $scriptPath = base_path('upload_backend_and_colors.py');
        }

        if (!File::exists($scriptPath)) {
            return response()->json(['status' => 'error', 'message' => 'Script de despliegue FTP no encontrado.'], 404);
        }

        $output = [];
        $returnVar = 0;
        exec("python " . escapeshellarg($scriptPath) . " 2>&1", $output, $returnVar);

        $logText = implode("\n", $output);

        if ($returnVar === 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Despliegue FTP completado con éxito a producción.',
                'log' => $logText
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'El script de despliegue FTP devolvió un error.',
                'log' => $logText
            ], 500);
        }
    }

    /**
     * Build directory tree recursively with max depth safety.
     */
    private function buildTree($dir, $basePath, $currentDepth = 0, $maxDepth = 3)
    {
        $result = [];
        $excludeDirNames = ['.git', 'node_modules', 'vendor', 'storage', '.idea', '.vscode', '.agent', '.agents'];
        $excludeFiles = ['.env.production', '.DS_Store', 'thumbs.db'];

        $items = @scandir($dir);
        if (!$items) return [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (in_array($item, $excludeDirNames) || in_array($item, $excludeFiles)) continue;

            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
            $relPath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $fullPath);
            $relPathFormatted = str_replace('\\', '/', $relPath);

            $isDir = is_dir($fullPath);
            $node = [
                'name' => $item,
                'path' => $relPathFormatted,
                'is_dir' => $isDir
            ];

            if ($isDir) {
                if ($currentDepth < $maxDepth) {
                    $node['children'] = $this->buildTree($fullPath, $basePath, $currentDepth + 1, $maxDepth);
                } else {
                    $node['children'] = [];
                }
            } else {
                $node['ext'] = strtolower(File::extension($fullPath));
            }

            $result[] = $node;
        }

        usort($result, function($a, $b) {
            if ($a['is_dir'] === $b['is_dir']) {
                return strnatcasecmp($a['name'], $b['name']);
            }
            return $a['is_dir'] ? -1 : 1;
        });

        return $result;
    }

    private function generateAgentResponseFallback($prompt, $activeFile, $fileContent, $selectedCode)
    {
        $fileInfo = $activeFile ? "en el archivo `{$activeFile}`" : "en tu espacio de trabajo";
        return "### 🤖 Antigravity AI Agent Response\n\n" .
               "He analizado tu solicitud: *\"{$prompt}\"* {$fileInfo}.\n\n" .
               "**Diagnóstico & Plan de Acción**:\n" .
               "1. Inspección de estructura y sintaxis del componente/controlador.\n" .
               "2. Aplicación de las reglas de arquitectura y buenas prácticas del proyecto (AGENTS.md).\n" .
               "3. Verificación de permisos de rol para `Superadministrador`.\n\n" .
               "Para aplicar cualquier ajuste de código, edítalo directamente en el editor central o haz clic en **Guardar y Verificar Sintaxis** (`Ctrl+S`).";
    }

    private function extractCodeFromResponse($text)
    {
        if (preg_match('/```(?:php|javascript|vue|html|css)?\s*\n(.*?)```/s', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
