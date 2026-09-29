<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado Actualizado - Orden #{{ $orden->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #1e0c29 0%, #3b164c 50%, #5c2c74 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            color: #1e293b;
        }
        .card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            text-align: center;
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            padding: 28px 20px;
            color: #ffffff;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
            backdrop-filter: blur(4px);
        }
        .checkmark {
            font-size: 38px;
            font-weight: bold;
            line-height: 1;
        }
        .card-header h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        .card-header p {
            font-size: 13px;
            opacity: 0.95;
        }
        .card-body {
            padding: 24px 20px;
            text-align: left;
        }
        .order-badge {
            display: inline-block;
            background: #f1f0f5;
            color: #5c2c74;
            font-weight: 700;
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-val {
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }
        .status-highlight {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 14px;
            margin-top: 16px;
            text-align: center;
        }
        .status-title {
            font-size: 11px;
            color: #166534;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .status-name {
            font-size: 18px;
            color: #15803d;
            font-weight: 700;
        }
        .card-footer {
            padding: 16px 20px 24px 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background: #5c2c74;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(92, 44, 116, 0.3);
            transition: transform 0.2s, background-color 0.2s;
        }
        .btn:active {
            transform: scale(0.98);
            background: #4a235a;
        }
        .brand-footer {
            margin-top: 14px;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <div class="icon-circle">
                <span class="checkmark">✓</span>
            </div>
            <h2>¡Estado Actualizado!</h2>
            <p>Escaneo registrado exitosamente en el sistema</p>
        </div>

        <div class="card-body">
            <div style="text-align: center;">
                <span class="order-badge">ORDEN DE TRABAJO #{{ $orden->idorden ?? $orden->id }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Cliente</span>
                <span class="info-val">{{ $orden->cliente->razonsocial ?? $orden->rasonsocial ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Trabajo</span>
                <span class="info-val">{{ $orden->articulo->nombre ?? $orden->articulo ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Cantidad</span>
                <span class="info-val">{{ number_format($orden->cantidad) }} Uds</span>
            </div>

            <div class="info-row">
                <span class="info-label">Fecha / Hora</span>
                <span class="info-val">{{ date('d/m/Y h:i A') }}</span>
            </div>

            <div class="status-highlight">
                <div class="status-title">Nuevo Estado del Proceso</div>
                <div class="status-name">{{ strtoupper($proceso) }}</div>
            </div>

            <!-- DIGITAL PHOTO UPLOAD SECTION FOR PHYSICAL HOJA DE RUTA -->
            <div style="margin-top: 18px; padding: 16px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; text-align: center;">
                <div style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">📷 Digitalizar Hoja de Ruta Física</div>
                <p style="font-size: 11.5px; color: #64748b; margin-bottom: 12px;">Tome una foto o adjunte el documento firmado por los operarios para guardarlo digitalmente en la orden.</p>
                
                @if(!empty($orden->hoja_ruta_escaneada))
                    <div style="margin-bottom: 10px; background: #e0f2fe; border: 1px solid #bae6fd; padding: 8px 12px; border-radius: 8px; font-size: 12px; color: #0369a1; font-weight: 600;">
                        ✔ Documento Digitalizado Registrado
                        <div style="margin-top: 4px;">
                            <a href="{{ asset($orden->hoja_ruta_escaneada) }}" target="_blank" style="color: #0284c7; text-decoration: underline;">Ver / Descargar Documento</a>
                        </div>
                    </div>
                @endif

                <form id="uploadForm" style="display: block;">
                    <input type="file" id="fileInput" name="file" accept="image/*,application/pdf" capture="environment" style="display: none;" onchange="uploadHojaRuta(this)">
                    <button type="button" onclick="document.getElementById('fileInput').click()" id="uploadBtn" style="background: #2563eb; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; width: 100%; cursor: pointer;">
                        📷 Tomar Foto / Subir Hoja de Ruta
                    </button>
                </form>
                <div id="uploadMsg" style="font-size: 12px; font-weight: 600; margin-top: 8px; display: none;"></div>
            </div>
        </div>

        <script style="display:none;">
            function uploadHojaRuta(input) {
                if (!input.files || !input.files[0]) return;
                var file = input.files[0];
                var formData = new FormData();
                formData.append('id', '{{ $orden->idorden ?? $orden->id }}');
                formData.append('file', file);
                
                var btn = document.getElementById('uploadBtn');
                var msg = document.getElementById('uploadMsg');
                btn.disabled = true;
                btn.innerText = '⏳ Subiendo documento...';
                msg.style.display = 'block';
                msg.style.color = '#0284c7';
                msg.innerText = 'Subiendo archivo al sistema...';

                fetch('/orden/subir-hoja-ruta-escaneada', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        btn.innerText = '✔ Documento Guardado';
                        btn.style.background = '#16a34a';
                        msg.style.color = '#15803d';
                        msg.innerText = '¡Hoja de Ruta guardada digitalmente con éxito!';
                    } else {
                        btn.disabled = false;
                        btn.innerText = '📷 Reintentar Foto / Subida';
                        msg.style.color = '#dc2626';
                        msg.innerText = data.message || 'Error al subir el archivo.';
                    }
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.innerText = '📷 Reintentar Foto / Subida';
                    msg.style.color = '#dc2626';
                    msg.innerText = 'Error de conexión al subir.';
                });
            }
        </script>

        <div class="card-footer">
            <a href="{{ url('/') }}" class="btn">Ir al Sistema Principal</a>
            <div class="brand-footer">Agencia Lupa S.A.S. &bull; Control Digital de Producción</div>
        </div>
    </div>

</body>
</html>
