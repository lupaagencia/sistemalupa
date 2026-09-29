<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro de Asistencia Móvil - LUPACK</title>
    <link rel="stylesheet" href="{{ asset('css/plantilla.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .card-mobile {
            background: #ffffff;
            color: #1e293b;
            border-radius: 20px;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.4);
        }
        .btn-register {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            padding: 18px;
            font-size: 1.2rem;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.4);
        }
        .btn-register:active {
            transform: scale(0.97);
        }
        .avatar-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2563eb;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    @php
        $empActual = isset($empleado) ? $empleado : (Auth::check() && Auth::user()->empleado_id ? \App\Empleado::with('turno')->find(Auth::user()->empleado_id) : null);
        $listaEmp = isset($empleados) && count($empleados) ? $empleados : \App\Empleado::select('id', 'nombre', 'apellido', 'num_doc', 'cargo')->orderBy('nombre', 'asc')->get();
    @endphp

    <div class="container" style="max-width: 450px;">
        <div class="text-center mb-4">
            <h2 class="font-weight-bold text-white mb-1">LUPACK</h2>
            <p class="text-white-50 small font-weight-bold mb-0">Registro Móvil de Asistencia & Puntualidad</p>
        </div>

        <div class="card card-mobile p-4">
            <div id="section-form">
                @if($empActual)
                    <!-- Caso 1: Empleado autenticado/identificado automáticamente (¡Sin digitar cédula!) -->
                    <div class="text-center mb-4">
                        <img src="{{ $empActual->foto ? asset('img/empleados/'.$empActual->foto) : asset('img/avatar.png') }}" 
                             alt="Foto" class="avatar-circle mb-2 shadow-sm">
                        <h4 class="font-weight-bold text-dark mb-1">{{ $empActual->nombre }} {{ $empActual->apellido }}</h4>
                        <span class="badge badge-primary px-3 py-1 text-uppercase font-weight-bold" style="font-size: 0.85rem;">
                            {{ $empActual->cargo ?: 'Empleado' }}
                        </span>
                        <p class="text-muted small mt-2 mb-0">
                            Turno Asignado: <strong>{{ $empActual->turno ? $empActual->turno->nombre : 'Ordinario General' }}</strong>
                        </p>
                    </div>
                    <input type="hidden" id="empleado_id" value="{{ $empActual->id }}">
                @else
                    <!-- Caso 2: Selección rápida de trabajador en lista (¡Sin digitar números!) -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small mb-2">Seleccione su Nombre:</label>
                        <select id="empleado_id" class="form-control form-control-lg font-weight-bold border-primary text-dark">
                            <option value="">-- Seleccione Trabajador --</option>
                            @foreach($listaEmp as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->nombre }} {{ $emp->apellido }} ({{ $emp->cargo }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group mb-4">
                    <label class="font-weight-bold text-dark small d-block">Tipo de Evento (Opcional):</label>
                    <select id="tipo_evento" class="form-control font-weight-bold text-dark">
                        <option value="">✨ Auto-Detectar (Ingreso / Salida)</option>
                        <option value="entrada_manana">🌅 Ingreso / Entrada</option>
                        <option value="salida_empresa">🚪 Salida</option>
                        <option value="salida_almuerzo">🍲 Salida Almuerzo</option>
                        <option value="entrada_almuerzo">🥪 Ingreso Almuerzo</option>
                    </select>
                </div>

                <button id="btn-submit" class="btn-register shadow mb-2" onclick="enviarRegistro()">
                    📍 MARCAR MI INGRESO / SALIDA
                </button>
            </div>

            <div id="section-result" style="display: none;" class="text-center py-3">
                <div id="result-icon" class="mb-3"></div>
                <h3 id="result-title" class="font-weight-bold mb-2"></h3>
                <h4 id="result-name" class="font-weight-bold text-primary mb-1"></h4>
                <p id="result-detail" class="text-muted mb-3"></p>
                <div id="result-alert" class="alert font-weight-bold mb-3"></div>

                <button class="btn btn-outline-dark btn-block font-weight-bold py-2" onclick="resetForm()">
                    <i class="fa fa-arrow-left mr-1"></i> Volver a Intentar
                </button>
            </div>
        </div>

        <div class="text-center mt-3 text-white-50 small">
            <span>LUPACK &copy; {{ date('Y') }} - Perímetro GPS Verificado 📍</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function enviarRegistro() {
            var empEl = document.getElementById('empleado_id');
            var empId = empEl ? empEl.value : '';
            var evento = document.getElementById('tipo_evento').value;
            var btn = document.getElementById('btn-submit');

            if (!empId) {
                Swal.fire('Atención', 'Por favor seleccione su nombre de la lista.', 'warning');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '⌛ Validando ubicación GPS y registrando...';

            function send(coords) {
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                var payload = {
                    empleado_id: empId,
                    tipo_evento: evento
                };
                if (coords) {
                    payload.latitud = coords.latitude;
                    payload.longitud = coords.longitude;
                }

                fetch('/asistencia/registrar-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    btn.innerHTML = '📍 MARCAR MI INGRESO / SALIDA';

                    if (data.status) {
                        var resData = data.data;
                        document.getElementById('section-form').style.display = 'none';
                        document.getElementById('section-result').style.display = 'block';

                        document.getElementById('result-icon').innerHTML = '<div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mx-auto" style="width:75px; height:75px;"><i class="fa fa-check fa-3x"></i></div>';
                        document.getElementById('result-title').innerText = resData.evento_etiqueta + ' Registrado';
                        document.getElementById('result-name').innerText = resData.empleado_nombre;
                        document.getElementById('result-detail').innerText = 'Hora: ' + resData.hora_marcada + ' | Turno: ' + resData.turno_nombre;

                        var alertEl = document.getElementById('result-alert');
                        if (resData.estado_llegada === 'llegada_tarde') {
                            alertEl.className = 'alert alert-danger font-weight-bold';
                            alertEl.innerText = '⚠️ Retardo detectado de ' + resData.minutos_tardanza + ' minutos.';
                        } else if (resData.minutos_extras > 0) {
                            alertEl.className = 'alert alert-success font-weight-bold';
                            alertEl.innerText = '⚡ Horas extras acumuladas: ' + Math.floor(resData.minutos_extras / 60) + 'h ' + (resData.minutos_extras % 60) + 'm.';
                        } else {
                            alertEl.className = 'alert alert-success font-weight-bold';
                            alertEl.innerText = '✅ Marcaje registrado a tiempo.';
                        }
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo registrar la asistencia.', 'error');
                    }
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.innerHTML = '📍 MARCAR MI INGRESO / SALIDA';
                    Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
                });
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) { send(pos.coords); },
                    function(err) { send(null); },
                    { timeout: 5000, enableHighAccuracy: true }
                );
            } else {
                send(null);
            }
        }

        function resetForm() {
            document.getElementById('section-result').style.display = 'none';
            document.getElementById('section-form').style.display = 'block';
        }
    </script>
</body>
</html>
