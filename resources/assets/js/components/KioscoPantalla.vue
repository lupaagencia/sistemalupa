<template>
    <div class="kiosco-fullscreen p-2 p-md-4 d-flex flex-column justify-content-between">
        <!-- Header Top Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-2 pb-md-3 border-bottom border-secondary gap-2">
            <div class="d-flex align-items-center">
                <h3 class="font-weight-bold text-white mb-0 mr-2 mr-md-3" style="font-size: 1.4rem;">LUPACK</h3>
                <span class="badge badge-primary px-2 px-md-3 py-2 font-weight-bold text-uppercase" style="font-size: 0.8rem;">
                    <i class="fa fa-shield mr-1"></i> Control de Acceso & Biometría
                </span>
            </div>

            <!-- Access Method Mode Switcher (Facial / Huella / QR / Mixto) -->
            <div class="btn-group bg-dark p-1 rounded border border-secondary shadow-sm my-1 my-md-0">
                <button type="button" 
                        class="btn btn-sm font-weight-bold" 
                        :class="modoAcceso === 'facial' ? 'btn-primary shadow' : 'btn-outline-light'" 
                        @click="cambiarModoAcceso('facial')">
                    <i class="fa fa-user-circle mr-1"></i> Facial
                </button>
                <button type="button" 
                        class="btn btn-sm font-weight-bold" 
                        :class="modoAcceso === 'huella' ? 'btn-warning text-dark shadow' : 'btn-outline-light'" 
                        @click="cambiarModoAcceso('huella')">
                    <i class="fa fa-fingerprint mr-1"></i> Huella
                </button>
                <button type="button" 
                        class="btn btn-sm font-weight-bold" 
                        :class="modoAcceso === 'qr' ? 'btn-primary shadow' : 'btn-outline-light'" 
                        @click="cambiarModoAcceso('qr')">
                    <i class="fa fa-qrcode mr-1"></i> QR / Cédula
                </button>
                <button type="button" 
                        class="btn btn-sm font-weight-bold" 
                        :class="modoAcceso === 'ambos' ? 'btn-success shadow' : 'btn-outline-light'" 
                        @click="cambiarModoAcceso('ambos')">
                    <i class="fa fa-bolt mr-1"></i> Mixto (Todos)
                </button>
            </div>

            <div class="d-flex align-items-center">
                <h4 class="font-weight-bold text-warning mb-0 mr-3" style="font-size: 1.2rem;">{{ horaActual }}</h4>
                <button class="btn btn-outline-light btn-sm font-weight-bold d-none d-md-inline-block" @click="toggleFullscreen()">
                    <i class="fa fa-arrows-alt mr-1"></i> Fullscreen
                </button>
            </div>
        </div>

        <!-- Main Center Area -->
        <div class="row my-auto align-items-center">
            <!-- Left Side: Interactive Scanner (Facial Camera or QR) -->
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card border-0 shadow-lg rounded p-3 p-md-4 text-center text-dark bg-white">

                    <!-- MODO RECONOCIMIENTO FACIAL O MIXTO -->
                    <div v-if="modoAcceso === 'facial' || modoAcceso === 'ambos'">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="font-weight-bold mb-0 text-primary" style="font-size: 1.25rem;">
                                <i class="fa fa-camera mr-1"></i> Biometría Facial
                            </h4>
                            <button type="button" 
                                    class="btn btn-xs font-weight-bold" 
                                    :class="autoScanActivo ? 'btn-success' : 'btn-outline-secondary'" 
                                    @click="autoScanActivo = !autoScanActivo">
                                <i class="fa fa-bolt mr-1"></i> {{ autoScanActivo ? '⚡ Auto-Registro: ON' : '⚡ Auto-Registro: OFF' }}
                            </button>
                        </div>
                        <p class="text-muted small mb-2" style="font-size: 0.82rem;">Enfoque su rostro en la cámara. El registro es automático.</p>

                        <!-- Camera Container with Animated Biometric Target Frame -->
                        <div class="camera-wrapper position-relative mx-auto rounded shadow overflow-hidden bg-dark mb-2">
                            <video ref="videoElement" 
                                   autoplay 
                                   playsinline 
                                   class="w-100 h-100" 
                                   style="object-fit: cover; transform: scaleX(-1);">
                            </video>

                            <!-- Biometric Oval Overlay & Target Reticle -->
                            <div class="biometric-overlay position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="top: 0; left: 0; pointer-events: none;">
                                <div class="face-oval border border-success rounded-circle position-relative">
                                    <div class="scanner-line"></div>
                                </div>
                                
                                <!-- Status / Auto-Countdown Badge -->
                                <span v-if="loading" class="badge badge-warning px-3 py-1 font-weight-bold mt-2 shadow" style="font-size: 0.85rem; opacity: 0.95;">
                                    <i class="fa fa-spinner fa-spin mr-1"></i> Verificando Rostro...
                                </span>
                                <span v-else-if="autoScanActivo && !autoScanPausado" class="badge badge-success px-3 py-1 font-weight-bold mt-2 shadow" style="font-size: 0.85rem; opacity: 0.95;">
                                    <i class="fa fa-bolt mr-1"></i> Registrando en {{ autoCountdown }}s...
                                </span>
                                <span v-else class="badge badge-dark px-3 py-1 font-weight-bold mt-2 shadow" style="font-size: 0.85rem; opacity: 0.9;">
                                    <i class="fa fa-eye text-success mr-1"></i> Biometría Facial Lista
                                </span>
                            </div>

                            <!-- Offscreen Canvas for Snapshot -->
                            <canvas ref="canvasElement" style="display: none;"></canvas>
                        </div>

                        <!-- PROMINENT VISIBLE BUTTON DIRECTLY BELOW CAMERA -->
                        <button type="button" 
                                class="btn btn-success btn-block btn-lg font-weight-bold shadow my-2 py-3" 
                                @click="capturarYReconocerRostro(false)" 
                                :disabled="loading" 
                                style="font-size: 1.15rem; border-radius: 12px;">
                            <span v-if="loading"><i class="fa fa-spinner fa-spin mr-2"></i> REGISTRANDO...</span>
                            <span v-else><i class="fa fa-camera mr-2"></i> ESCANEAR ROSTRO & REGISTRAR AHORA</span>
                        </button>

                        <!-- Employee Optional Selector for Enrolment / Fast Verification -->
                        <div class="form-group mb-2 text-left">
                            <label class="small font-weight-bold text-muted mb-1" style="font-size: 0.8rem;">Trabajador (Opcional - Para Confirmación o Auto-detección):</label>
                            <select v-model="empleadoSeleccionadoId" class="form-control form-control-sm font-weight-bold text-primary border-primary">
                                <option value="">✨ Auto-Detectar Rostro / Logueado</option>
                                <option v-for="emp in empleados" :key="emp.id" :value="emp.id">
                                    {{ emp.nombre }} {{ emp.apellido }} {{ emp.foto ? '📷' : '⚠️ Sin Foto' }} {{ emp.cargo ? '(' + emp.cargo + ')' : '' }}
                                </option>
                            </select>
                            <div v-if="empleadoSeleccionadoObjeto && !empleadoSeleccionadoObjeto.foto" class="alert alert-warning p-2 mt-1 mb-0 rounded font-weight-bold text-left shadow-sm border-warning small" style="font-size: 0.8rem; background-color: #fffbeb;">
                                <i class="fa fa-exclamation-triangle text-warning mr-1"></i>
                                <strong>Aviso de Biometría:</strong> {{ empleadoSeleccionadoObjeto.nombre }} no tiene foto registrada. Registre su foto en el módulo de Empleados para activar la validación facial.
                            </div>
                        </div>
                    </div>

                    <!-- MODO HUELLA DACTILAR BIOMÉTRICA O MIXTO -->
                    <div v-if="modoAcceso === 'huella' || modoAcceso === 'ambos'" :class="{'mt-3 border-top pt-3': modoAcceso === 'ambos'}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="font-weight-bold mb-0 text-warning" style="font-size: 1.2rem; color: #d97706 !important;">
                                <i class="fa fa-fingerprint mr-1"></i> Biometría por Huella Dactilar
                            </h4>
                            <button type="button" 
                                    class="btn btn-xs btn-outline-warning font-weight-bold py-1 px-2 shadow-sm"
                                    @click="abrirModalRegistroHuella()"
                                    title="Registrar huella para cada empleado">
                                <i class="fa fa-user-plus mr-1"></i> Registrar Huellas
                            </button>
                        </div>

                        <!-- Hardware Sensor Status Notification -->
                        <div v-if="soporteHuella && !soporteHuella.disponible" class="alert alert-warning text-dark p-2 mb-2 rounded font-weight-bold text-left shadow-sm border-warning" style="font-size: 0.82rem; background-color: #fffbeb;">
                            <div class="d-flex align-items-start">
                                <i class="fa fa-exclamation-triangle text-danger mt-1 mr-2 fa-lg"></i>
                                <div>
                                    <strong class="text-danger d-block">Lector no disponible en este dispositivo:</strong>
                                    {{ soporteHuella.mensaje }}
                                </div>
                            </div>
                        </div>
                        <div v-else-if="soporteHuella && soporteHuella.disponible" class="alert alert-success text-dark p-2 mb-2 rounded font-weight-bold text-left shadow-sm border-success" style="font-size: 0.82rem; background-color: #f0fdf4;">
                            <i class="fa fa-check-circle text-success mr-1"></i>
                            <strong>Lector Biométrico Activo:</strong> El sensor de huella del equipo está listo para leer.
                        </div>

                        <p class="text-muted small mb-2" style="font-size: 0.82rem;">
                            Toque el botón inferior para activar el lector de huella dactilar de este equipo y registrar su asistencia.
                        </p>

                        <!-- Interactive Fingerprint Visual Target Frame -->
                        <div class="fingerprint-wrapper position-relative mx-auto rounded-15 shadow-sm p-3 mb-2 d-flex flex-column align-items-center justify-content-center text-white cursor-pointer"
                             @click="capturarYReconocerHuella()"
                             style="max-width: 320px; min-height: 170px; border: 2px dashed #f59e0b; background: radial-gradient(circle, #1e293b 0%, #0f172a 100%);">
                            
                            <div class="fingerprint-pulse-circle mb-2 position-relative d-flex align-items-center justify-content-center">
                                <i class="fa fa-fingerprint fa-4x text-warning" :class="{'animate-pulse-fast': loading}"></i>
                                <div class="fingerprint-scan-beam" v-if="loading"></div>
                            </div>

                            <span v-if="loading" class="badge badge-warning text-dark px-3 py-1 font-weight-bold shadow mt-2" style="font-size: 0.9rem;">
                                <i class="fa fa-spinner fa-spin mr-1"></i> Activando Sensor de Huella...
                            </span>
                            <span v-else class="badge badge-light text-dark px-3 py-1 font-weight-bold shadow mt-2" style="font-size: 0.85rem;">
                                <i class="fa fa-hand-pointer-o text-warning mr-1"></i> Toque para activar el lector
                            </span>
                        </div>

                        <!-- Button to Scan Fingerprint -->
                        <button type="button" 
                                class="btn btn-warning btn-block btn-lg font-weight-900 shadow my-2 py-3 text-dark" 
                                @click="capturarYReconocerHuella()" 
                                :disabled="loading" 
                                style="font-size: 1.15rem; border-radius: 12px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none;">
                            <span v-if="loading"><i class="fa fa-spinner fa-spin mr-2"></i> LEYENDO DEL SENSOR...</span>
                            <span v-else><i class="fa fa-fingerprint mr-2"></i> ESCANEAR HUELLA & REGISTRAR</span>
                        </button>

                        <div class="d-flex justify-content-between align-items-center mt-1 mb-2 px-1">
                            <small class="text-muted font-weight-bold">
                                <i class="fa fa-fingerprint text-warning mr-1"></i> {{ totalEmpleadosConHuella }} de {{ empleados.length }} con huella
                            </small>
                            <a href="#" class="small font-weight-bold text-primary" @click.prevent="abrirModalRegistroHuella()">
                                <i class="fa fa-cog mr-1"></i> Gestionar huellas
                            </a>
                        </div>

                        <!-- Employee Optional Selector -->
                        <div class="form-group mb-2 text-left">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="small font-weight-bold text-muted mb-0" style="font-size: 0.8rem;">Trabajador:</label>
                                <button v-if="empleadoSeleccionadoId" 
                                        type="button" 
                                        class="btn btn-xs btn-outline-warning py-0 px-2 font-weight-bold"
                                        @click="reVincularHuellaSeleccionado()"
                                        title="Activar el lector de este equipo para registrar su huella">
                                    <i class="fa fa-fingerprint mr-1"></i> Vincular a este Lector
                                </button>
                            </div>
                            <select v-model="empleadoSeleccionadoId" class="form-control form-control-sm font-weight-bold text-warning border-warning">
                                <option value="">✨ Auto-Identificar por Huella Registrada</option>
                                <option v-for="emp in empleados" :key="'fp_' + emp.id" :value="emp.id">
                                    {{ emp.nombre }} {{ emp.apellido }} {{ emp.huella_dactilar ? '✓' : '(Sin Huella)' }}
                                </option>
                            </select>
                            <button v-if="empleadoSeleccionadoId" 
                                    type="button" 
                                    class="btn btn-outline-warning btn-block btn-sm font-weight-bold mt-2 py-2 shadow-sm text-dark bg-warning"
                                    style="border-radius: 8px;"
                                    @click="reVincularHuellaSeleccionado()">
                                <i class="fa fa-fingerprint mr-1"></i> Grabar Huella de este Trabajador en este Lector
                            </button>
                        </div>
                    </div>

                    <!-- MODO CÓDIGO QR / CÉDULA -->
                    <div v-if="modoAcceso === 'qr' || modoAcceso === 'ambos'" :class="{'mt-3 border-top pt-2': modoAcceso === 'ambos'}">
                        <h4 class="font-weight-bold mb-2 text-dark" style="font-size: 1.1rem;">
                            <i class="fa fa-qrcode mr-1 text-primary"></i> Marcación por Código QR / Cédula
                        </h4>

                        <!-- QR Dinámico Rotatorio -->
                        <div v-if="modoAcceso === 'qr'" class="my-2 p-2 bg-light d-inline-block rounded border mx-auto shadow-sm">
                            <img :src="obtenerUrlQR()" 
                                 alt="QR Dinámico Empresa" 
                                 class="img-fluid" style="width: 160px; height: 160px;">
                            <small class="d-block text-muted mt-1 font-weight-bold">
                                <i class="fa fa-refresh fa-spin text-primary mr-1"></i> Escanear con Celular (Refresca 15s)
                            </small>
                        </div>

                        <form @submit.prevent="procesarMarcacionQR()" class="mt-2 text-left">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Escanear Código QR o Cédula:</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light text-primary border-right-0">
                                            <i class="fa fa-qrcode fa-lg"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           ref="inputQR"
                                           class="form-control border-left-0 font-weight-bold text-primary" 
                                           placeholder="Esperando escaneo..." 
                                           v-model="inputCodigoQR" 
                                           @keyup.enter.prevent="procesarMarcacionQR()"
                                           @change="onInputChange()">
                                </div>
                            </div>

                            <button v-if="modoAcceso === 'qr'" type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold shadow mt-2" :disabled="loading">
                                <span v-if="loading"><i class="fa fa-spinner fa-spin mr-2"></i> Procesando...</span>
                                <span v-else><i class="fa fa-check-circle mr-2"></i> Confirmar Marcación QR</span>
                            </button>
                        </form>
                    </div>

                    <!-- Event Selector Buttons (Ingreso / Salida / Auto) -->
                    <div class="form-group mt-2 text-left mb-0">
                        <label class="small font-weight-bold text-muted d-block mb-1" style="font-size: 0.78rem;">Seleccionar Evento:</label>
                        <div class="btn-group-toggle d-flex flex-wrap gap-1 justify-content-center">
                            <button type="button" class="btn btn-xs btn-sm font-weight-bold m-1" :class="kioscoEvento === 'auto' ? 'btn-dark' : 'btn-outline-dark'" @click="kioscoEvento = 'auto'">✨ Auto-Detectar</button>
                            <button type="button" class="btn btn-xs btn-sm font-weight-bold m-1" :class="kioscoEvento === 'entrada_manana' ? 'btn-success' : 'btn-outline-success'" @click="kioscoEvento = 'entrada_manana'">🌅 Entrada</button>
                            <button type="button" class="btn btn-xs btn-sm font-weight-bold m-1" :class="kioscoEvento === 'salida_almuerzo' ? 'btn-warning' : 'btn-outline-warning'" @click="kioscoEvento = 'salida_almuerzo'">🍲 Almuerzo Out</button>
                            <button type="button" class="btn btn-xs btn-sm font-weight-bold m-1" :class="kioscoEvento === 'entrada_almuerzo' ? 'btn-info' : 'btn-outline-info'" @click="kioscoEvento = 'entrada_almuerzo'">🥪 Almuerzo In</button>
                            <button type="button" class="btn btn-xs btn-sm font-weight-bold m-1" :class="kioscoEvento === 'salida_empresa' ? 'btn-danger' : 'btn-outline-danger'" @click="kioscoEvento = 'salida_empresa'">🚪 Salida</button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Side: Live Turn Result Screen -->
            <div class="col-lg-6">
                <div v-if="ultimoResultado" 
                     class="card border-0 shadow-lg rounded p-4 text-center text-white h-100" 
                     :style="ultimoResultado.estado_llegada === 'llegada_tarde' ? 'background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);' : 'background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);'">
                    
                    <div class="avatar-container mb-2 mx-auto shadow rounded-circle bg-white p-1" style="width: 110px; height: 110px;">
                        <img :src="ultimoResultado.foto ? ('/img/empleados/' + ultimoResultado.foto) : '/img/avatar.png'" 
                             class="rounded-circle w-100 h-100" style="object-fit: cover;" 
                             alt="Foto Trabajador">
                    </div>

                    <h2 class="font-weight-bold mb-1 text-white">{{ ultimoResultado.empleado_nombre }}</h2>
                    <p class="mb-2 text-white-50 font-weight-bold" style="font-size: 1.1rem;">{{ ultimoResultado.cargo || 'Empleado' }}</p>

                    <div class="d-flex justify-content-center gap-2 mb-2">
                        <span class="badge badge-light text-dark px-3 py-1 font-weight-bold shadow" style="font-size: 1rem;">
                            {{ ultimoResultado.evento_etiqueta }}
                        </span>
                        <span v-if="ultimoResultado.metodo_validacion" class="badge badge-warning text-dark px-3 py-1 font-weight-bold shadow" style="font-size: 1rem;">
                            <i class="fa fa-check-circle mr-1"></i> {{ ultimoResultado.metodo_validacion }}
                        </span>
                    </div>

                    <h1 class="font-weight-bold my-1 text-white display-3">{{ ultimoResultado.hora_marcada }}</h1>
                    <p class="small text-white-50 mb-3 font-weight-bold">Turno: {{ ultimoResultado.turno_nombre }}</p>

                    <!-- Feedback Result -->
                    <div v-if="ultimoResultado.estado_llegada === 'llegada_tarde'" class="alert alert-light text-danger font-weight-bold mb-0 shadow p-3">
                        <h4 class="font-weight-bold mb-1"><i class="fa fa-exclamation-triangle mr-2"></i> ¡LLEGADA TARDE DETECTADA!</h4>
                        <p class="mb-0">Retardo registrado de <strong>{{ ultimoResultado.minutos_tardanza }} minutos</strong>.</p>
                    </div>
                    <div v-else-if="ultimoResultado.minutos_extras > 0" class="alert alert-light text-success font-weight-bold mb-0 shadow p-3">
                        <h4 class="font-weight-bold mb-1"><i class="fa fa-battery-full mr-2"></i> ¡HORAS EXTRAS REGISTRADAS!</h4>
                        <p class="mb-0">Tiempo adicional: <strong>{{ Math.floor(ultimoResultado.minutos_extras / 60) }}h {{ ultimoResultado.minutos_extras % 60 }}m extras</strong>.</p>
                    </div>
                    <div v-else class="alert alert-light text-success font-weight-bold mb-0 shadow p-3">
                        <h4 class="font-weight-bold mb-1"><i class="fa fa-check-circle mr-2"></i> MARCAJE A TIEMPO</h4>
                        <p class="mb-0">Registro biométrico dentro del horario oficial de su turno.</p>
                    </div>
                </div>

                <!-- Initial Idle Screen -->
                <div v-else class="card border border-secondary shadow-lg rounded p-4 p-md-5 text-center bg-dark text-white h-100 d-flex align-items-center justify-content-center">
                    <i class="fa fa-user-circle fa-4x text-primary mb-3"></i>
                    <h3 class="font-weight-bold mb-2">Estación Biométrica Activa</h3>
                    <p class="text-muted" style="font-size: 1rem;">Enfoque su rostro en la cámara. El registro es automático al detectar el rostro o mediante el botón de escaneo.</p>
                </div>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary text-muted small">
            <span>LUPACK &copy; {{ new Date().getFullYear() }} - Sistema Biométrico Portería</span>
            <span>Perímetro GPS Protegido 📍</span>
        </div>

        <!-- VENTANA / MODAL DE ESCANEO EXITOSO (PREVENCIÓN DE MARCACIÓN DE SALIDA POR ERROR) -->
        <transition name="fade">
            <div v-if="mostrarModalExito && ultimoResultado" 
                 class="modal-exito-overlay d-flex align-items-center justify-content-center p-3">
                <div class="modal-exito-content card border-0 shadow-2xl rounded-20 p-4 p-md-5 text-center text-white position-relative overflow-hidden"
                     style="max-width: 580px; width: 100%; background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #047857 100%);">
                    
                    <!-- Celebratory Icon Badge -->
                    <div class="success-icon-badge mx-auto mb-3 shadow-lg rounded-circle bg-white text-success d-flex align-items-center justify-content-center">
                        <i class="fa fa-check text-success" style="font-size: 3.5rem;"></i>
                    </div>

                    <h2 class="font-weight-900 mb-1 text-white text-uppercase" style="font-size: 1.85rem; letter-spacing: 1px;">
                        ¡ESCANEO EXITOSO!
                    </h2>
                    <p class="font-weight-bold mb-3" style="font-size: 1.1rem; color: #a7f3d0;">
                        Su asistencia ha sido registrada correctamente en el sistema
                    </p>

                    <!-- Employee Details Card -->
                    <div class="card border-0 bg-white text-dark rounded-15 p-3 mb-3 shadow-sm text-left">
                        <div class="d-flex align-items-center justify-content-start">
                            <div class="avatar-wrap rounded-circle overflow-hidden shadow-sm mr-3" style="width: 72px; height: 72px; min-width: 72px; border: 3px solid #10b981;">
                                <img :src="ultimoResultado.foto ? ('/img/empleados/' + ultimoResultado.foto) : '/img/avatar.png'" 
                                     class="w-100 h-100" style="object-fit: cover;" 
                                     alt="Trabajador">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h4 class="font-weight-900 text-dark mb-0 text-truncate" style="font-size: 1.25rem;">
                                    {{ ultimoResultado.empleado_nombre }}
                                </h4>
                                <div class="text-muted font-weight-bold small mb-2">{{ ultimoResultado.cargo || 'Trabajador' }}</div>
                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.92rem;">
                                        <i class="fa fa-check-circle mr-1"></i> {{ ultimoResultado.evento_etiqueta }}
                                    </span>
                                    <span class="badge badge-dark px-2 py-1 font-weight-bold ml-1" style="font-size: 0.92rem;">
                                        <i class="fa fa-clock-o mr-1"></i> {{ ultimoResultado.hora_marcada }}
                                    </span>
                                    <span v-if="ultimoResultado.similitud_facial" class="badge badge-info px-2 py-1 font-weight-bold ml-1 text-white" style="font-size: 0.92rem; background-color: #0284c7;">
                                        <i class="fa fa-user-check mr-1"></i> Rostro Validado ({{ ultimoResultado.similitud_facial }}%)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Critical Warning / Step Away Notice -->
                    <div class="alert alert-warning text-dark font-weight-bold my-2 shadow-sm text-left p-3" 
                         style="background-color: #fffbeb; border: 2px solid #f59e0b; border-radius: 14px;">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-center" style="min-width: 40px;">
                                <i class="fa fa-exclamation-triangle fa-2x" style="color: #d97706 !important;"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-900 mb-1" style="font-size: 1.05rem; color: #92400e;">
                                    ⚠️ ¡POR FAVOR RETÍRESE DE LA CÁMARA!
                                </h6>
                                <p class="mb-0" style="font-size: 0.92rem; line-height: 1.35; color: #78350f;">
                                    Dé un paso atrás para <strong>no registrar la salida por error</strong> y permitir que su siguiente compañero pueda escanearse.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Auto reactivate timer bar -->
                    <div class="mt-3 mb-3">
                        <small class="text-white font-weight-bold d-block mb-1" style="opacity: 0.95; font-size: 0.9rem;">
                            <i class="fa fa-refresh fa-spin mr-1"></i> La cámara se reactivará en 
                            <span class="badge badge-warning text-dark font-weight-900 px-2 py-1 ml-1" style="font-size: 1.05rem;">
                                {{ segundosRestantesExito }}s
                            </span>
                        </small>
                        <div class="progress shadow-inner" style="height: 8px; background-color: rgba(255,255,255,0.25); border-radius: 10px;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 :style="{ width: ((segundosRestantesExito / 8) * 100) + '%' }">
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div>
                        <button type="button" 
                                class="btn btn-light btn-lg font-weight-900 text-success px-4 py-2 shadow-lg w-100"
                                style="border-radius: 14px; font-size: 1.15rem;" 
                                @click="cerrarVentanaExito()">
                            <i class="fa fa-check-circle mr-2"></i> Listo / Siguiente Persona
                        </button>
                    </div>

                </div>
            </div>
        </transition>

        <!-- MODAL REGISTRO / ENROLAMIENTO DE HUELLA POR EMPLEADO -->
        <div v-if="modalRegistroHuella" class="modal-kiosco-backdrop d-flex align-items-center justify-content-center" style="z-index: 99999;">
            <div class="card border-0 shadow-24 rounded-20 p-4 bg-white text-dark" style="max-width: 720px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h4 class="font-weight-900 text-warning mb-0" style="color: #d97706 !important;">
                        <i class="fa fa-fingerprint mr-2"></i> Registrar Huella Dactilar de Empleados
                    </h4>
                    <button type="button" class="close text-muted" @click="cerrarModalRegistroHuella()">&times;</button>
                </div>

                <p class="text-muted small mb-2">
                    Cada empleado debe registrar su huella dactilar una sola vez. Al presionar <strong>"Registrar Huella"</strong>, el dispositivo activará su lector biométrico para capturar el dedo del trabajador.
                </p>

                <!-- Sensor Status Alert -->
                <div v-if="soporteHuella && !soporteHuella.disponible" class="alert alert-danger p-2 mb-3 rounded small font-weight-bold">
                    <i class="fa fa-times-circle mr-1"></i> <strong>Aviso del Dispositivo:</strong> {{ soporteHuella.mensaje }}
                </div>
                <div v-else-if="soporteHuella && soporteHuella.disponible" class="alert alert-success p-2 mb-3 rounded small font-weight-bold">
                    <i class="fa fa-check-circle mr-1"></i> <strong>Lector Biométrico Activo:</strong> El hardware de huella de este equipo está listo para registrar huellas.
                </div>

                <!-- Search Input -->
                <div class="input-group input-group-sm mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" v-model="filtroEmpleadoModal" class="form-control" placeholder="Buscar por nombre o cédula...">
                </div>

                <!-- Employees Table List -->
                <div class="table-responsive flex-grow-1" style="overflow-y: auto; max-height: 380px;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Cédula</th>
                                <th>Estado</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="emp in empleadosFiltradosModal" :key="'modal_emp_' + emp.id">
                                <td class="font-weight-bold">
                                    <img :src="emp.foto ? ('/img/empleados/' + emp.foto) : '/img/avatar.png'" class="rounded-circle mr-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    {{ emp.nombre }} {{ emp.apellido }}
                                    <small class="d-block text-muted">{{ emp.cargo || 'Sin cargo' }}</small>
                                </td>
                                <td class="small font-weight-bold text-muted">{{ emp.num_doc || 'N/A' }}</td>
                                <td>
                                    <span v-if="emp.huella_dactilar" class="badge badge-success px-2 py-1">
                                        <i class="fa fa-check mr-1"></i> Registrada
                                    </span>
                                    <span v-else class="badge badge-secondary px-2 py-1 text-white">
                                        <i class="fa fa-minus-circle mr-1"></i> Sin Huella
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" 
                                                class="btn btn-warning font-weight-bold px-2 py-1 text-dark" 
                                                :disabled="enrolandoId === emp.id"
                                                @click="iniciarRegistroHuellaEmpleado(emp)">
                                            <span v-if="enrolandoId === emp.id"><i class="fa fa-spinner fa-spin mr-1"></i> Leyendo...</span>
                                            <span v-else><i class="fa fa-fingerprint mr-1"></i> {{ emp.huella_dactilar ? 'Re-Registrar' : 'Registrar Huella' }}</span>
                                        </button>
                                        <button v-if="emp.huella_dactilar" 
                                                type="button" 
                                                class="btn btn-outline-danger px-2 py-1" 
                                                @click="desvincularHuellaEmpleado(emp)" 
                                                title="Eliminar huella de este trabajador">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-2">
                    <small class="text-muted font-weight-bold">
                        Total: {{ totalEmpleadosConHuella }} de {{ empleados.length }} con huella registrada
                    </small>
                    <button type="button" class="btn btn-secondary font-weight-bold px-4" @click="cerrarModalRegistroHuella()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['empleadoLogueadoId'],
    data() {
        return {
            modoAcceso: 'facial', // 'facial', 'qr', 'ambos'
            inputCodigoQR: '',
            kioscoEvento: 'auto',
            empleadoSeleccionadoId: this.empleadoLogueadoId || '',
            empleados: [],
            loading: false,
            ultimoResultado: null,
            tokenDinamico: Math.floor(Math.random() * 900000) + 100000,
            intervalToken: null,
            horaActual: new Date().toLocaleTimeString(),
            timerScan: null,
            mediaStream: null,
            
            // Auto-Scan properties
            autoScanActivo: true,
            autoCountdown: 3,
            intervalAutoScan: null,
            autoScanPausado: false,

            // Success Modal Properties
            mostrarModalExito: false,
            segundosRestantesExito: 8,
            timerExito: null,

            // Biometric Fingerprint properties
            soporteHuella: null,
            modalRegistroHuella: false,
            filtroEmpleadoModal: '',
            enrolandoId: null
        };
    },
    computed: {
        empleadoSeleccionadoObjeto() {
            if (!this.empleadoSeleccionadoId) return null;
            return this.empleados.find(e => e.id == this.empleadoSeleccionadoId) || null;
        },
        totalEmpleadosConHuella() {
            return this.empleados.filter(e => e.huella_dactilar).length;
        },
        empleadosFiltradosModal() {
            if (!this.filtroEmpleadoModal) return this.empleados;
            let f = this.filtroEmpleadoModal.toLowerCase().trim();
            return this.empleados.filter(e => {
                let nombreCompleto = ((e.nombre || '') + ' ' + (e.apellido || '')).toLowerCase();
                let doc = (e.num_doc || '').toLowerCase();
                let cargo = (e.cargo || '').toLowerCase();
                return nombreCompleto.includes(f) || doc.includes(f) || cargo.includes(f);
            });
        }
    },
    watch: {
        inputCodigoQR(val) {
            if (val && val.length >= 3 && !this.loading) {
                clearTimeout(this.timerScan);
                this.timerScan = setTimeout(() => {
                    if (this.inputCodigoQR === val) {
                        this.procesarMarcacionQR();
                    }
                }, 350);
            }
        }
    },
    methods: {
        cambiarModoAcceso(nuevoModo) {
            this.modoAcceso = nuevoModo;
            if (nuevoModo === 'facial' || nuevoModo === 'ambos') {
                this.$nextTick(() => this.iniciarCamara());
            } else {
                this.detenerCamara();
                this.$nextTick(() => {
                    if (this.$refs.inputQR) this.$refs.inputQR.focus();
                });
            }
        },
        iniciarCamara() {
            let me = this;
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
                }).then(stream => {
                    me.mediaStream = stream;
                    if (me.$refs.videoElement) {
                        me.$refs.videoElement.srcObject = stream;
                    }
                }).catch(err => {
                    console.warn('No se pudo acceder a la cámara Web:', err);
                });
            }
        },
        detenerCamara() {
            if (this.mediaStream) {
                this.mediaStream.getTracks().forEach(track => track.stop());
                this.mediaStream = null;
            }
        },
        iniciarAutoScanTimer() {
            let me = this;
            if (me.intervalAutoScan) clearInterval(me.intervalAutoScan);
            me.intervalAutoScan = setInterval(() => {
                if ((me.modoAcceso === 'facial' || me.modoAcceso === 'ambos') && me.autoScanActivo && !me.loading && !me.autoScanPausado) {
                    if (me.autoCountdown > 1) {
                        me.autoCountdown--;
                    } else {
                        me.autoCountdown = 3;
                        me.capturarYReconocerRostro(true);
                    }
                }
            }, 1000);
        },
        capturarYReconocerRostro(esAutomatico = false) {
            let me = this;
            if (me.loading) return;

            if (me.empleadoSeleccionadoId) {
                let empSel = me.empleados.find(e => e.id == me.empleadoSeleccionadoId);
                if (empSel && !empSel.foto) {
                    if (!esAutomatico) {
                        swal('Sin Foto Registrada', empSel.nombre + ' ' + (empSel.apellido || '') + ' aún no tiene fotografía registrada en el sistema. Registre su foto en el módulo de Empleados para poder marcar por reconocimiento facial.', 'warning');
                    }
                    return;
                }
            }

            let video = me.$refs.videoElement;
            let canvas = me.$refs.canvasElement;
            if (!video || !canvas) {
                if (!esAutomatico) swal('Atención', 'Cámara no activa o disponible.', 'warning');
                return;
            }

            if (video.readyState < 2) {
                return;
            }

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            let ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            let fotoBase64 = canvas.toDataURL('image/jpeg', 0.85);

            me.loading = true;
            me.autoScanPausado = true;

            let enviarAXios = (coords) => {
                let payload = {
                    foto_base64: fotoBase64,
                    empleado_id: me.empleadoSeleccionadoId || '',
                    tipo_evento: me.kioscoEvento === 'auto' ? '' : me.kioscoEvento
                };
                if (coords) {
                    payload.latitud = coords.latitude;
                    payload.longitud = coords.longitude;
                }

                axios.post('/asistencia/reconocer-rostro', payload).then(function (response) {
                    me.loading = false;
                    me.mostrarVentanaExito(response.data.data);
                }).catch(function (error) {
                    me.loading = false;
                    let msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error en reconocimiento facial.';
                    swal('Atención Biometría', msg, (error.response && error.response.status === 422) ? 'info' : 'error');
                    setTimeout(() => {
                        me.autoScanPausado = false;
                        me.autoCountdown = 3;
                    }, 4000);
                });
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => enviarAXios(pos.coords),
                    (err) => enviarAXios(null),
                    { timeout: 3000, enableHighAccuracy: true }
                );
            } else {
                enviarAXios(null);
            }
        },
        obtenerUrlQR() {
            let baseUrl = window.location.origin;
            let targetUrl = baseUrl + '/marcar-asistencia?token=' + this.tokenDinamico;
            return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(targetUrl);
        },
        onInputChange() {
            if (this.inputCodigoQR && !this.loading) {
                clearTimeout(this.timerScan);
                this.procesarMarcacionQR();
            }
        },
        procesarMarcacionQR() {
            if (!this.inputCodigoQR) return;
            let me = this;
            if (me.loading) return;
            me.loading = true;
            clearTimeout(me.timerScan);

            let finalizar = () => {
                me.loading = false;
                me.inputCodigoQR = '';
                me.$nextTick(() => {
                    if (me.$refs.inputQR) me.$refs.inputQR.focus();
                });
            };

            let enviarAXios = (coords) => {
                let payload = {
                    codigo_qr: me.inputCodigoQR.trim(),
                    tipo_evento: me.kioscoEvento === 'auto' ? '' : me.kioscoEvento
                };
                if (coords) {
                    payload.latitud = coords.latitude;
                    payload.longitud = coords.longitude;
                }
                axios.post('/asistencia/registrar-qr', payload).then(function (response) {
                    me.mostrarVentanaExito(response.data.data);
                    finalizar();
                }).catch(function (error) {
                    let msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error al registrar marcación.';
                    swal('Atención Marcaje', msg, (error.response && error.response.status === 422) ? 'info' : 'error');
                    finalizar();
                });
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => enviarAXios(pos.coords),
                    (err) => enviarAXios(null),
                    { timeout: 4000, enableHighAccuracy: true }
                );
            } else {
                enviarAXios(null);
            }
        },
        mostrarVentanaExito(resultado) {
            let me = this;
            me.ultimoResultado = resultado;
            me.mostrarModalExito = true;
            me.autoScanPausado = true;
            me.segundosRestantesExito = 8;
            me.reproducirSonidoExito();

            if (me.timerExito) clearInterval(me.timerExito);
            me.timerExito = setInterval(() => {
                if (me.segundosRestantesExito > 1) {
                    me.segundosRestantesExito--;
                } else {
                    me.cerrarVentanaExito();
                }
            }, 1000);
        },
        cerrarVentanaExito() {
            let me = this;
            if (me.timerExito) clearInterval(me.timerExito);
            me.mostrarModalExito = false;
            // Cooldown de 3 segundos adicionales después de cerrar para dar tiempo a retirarse
            setTimeout(() => {
                me.autoScanPausado = false;
                me.autoCountdown = 3;
            }, 3000);
        },
        reproducirSonidoExito() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, ctx.currentTime);
                gain1.gain.setValueAtTime(0.15, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(ctx.currentTime);
                osc1.stop(ctx.currentTime + 0.15);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.15);
                gain2.gain.setValueAtTime(0.2, ctx.currentTime + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(ctx.currentTime + 0.15);
                osc2.stop(ctx.currentTime + 0.35);
            } catch (e) {}
        },
        bufferToBase64(buffer) {
            if (!buffer) return '';
            let binary = '';
            const bytes = new Uint8Array(buffer);
            for (let i = 0; i < bytes.byteLength; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return window.btoa(binary);
        },
        base64ToUint8Array(base64String) {
            if (!base64String) return null;
            try {
                let b64 = base64String.replace(/-/g, '+').replace(/_/g, '/');
                while (b64.length % 4) {
                    b64 += '=';
                }
                const binary = window.atob(b64);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) {
                    bytes[i] = binary.charCodeAt(i);
                }
                return bytes;
            } catch (e) {
                return null;
            }
        },
        async verificarSensorHuellaDispositivo() {
            if (!window.PublicKeyCredential) {
                return {
                    disponible: false,
                    mensaje: 'Este navegador o conexión no soporta la API de biometría WebAuthn / FIDO2 (se requiere HTTPS o localhost).'
                };
            }
            try {
                if (PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) {
                    const disponible = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
                    if (!disponible) {
                        return {
                            disponible: false,
                            mensaje: 'Este dispositivo NO cuenta con lector de huella dactilar disponible o no está configurado en el sistema operativo (Windows Hello / Android / Touch ID).'
                        };
                    }
                    return { disponible: true, mensaje: 'Lector biométrico de huella detectado y disponible en este dispositivo.' };
                }
                return { disponible: true, mensaje: 'Soporte biométrico disponible.' };
            } catch (err) {
                return {
                    disponible: false,
                    mensaje: 'No se pudo verificar el lector biométrico: ' + (err.message || err)
                };
            }
        },
        abrirModalRegistroHuella() {
            this.modalRegistroHuella = true;
            this.filtroEmpleadoModal = '';
            this.verificarSensorHuellaDispositivo().then(res => {
                this.soporteHuella = res;
            });
        },
        cerrarModalRegistroHuella() {
            this.modalRegistroHuella = false;
            this.enrolandoId = null;
        },
        async iniciarRegistroHuellaEmpleado(emp) {
            let me = this;
            if (me.enrolandoId) return;

            // Verificar si el navegador soporta WebAuthn
            if (!navigator.credentials || !navigator.credentials.create) {
                swal({
                    title: 'Lector no disponible',
                    text: 'Este navegador o conexión no permite activar el lector biométrico. Asegúrese de ingresar por HTTPS (https://' + window.location.hostname + ') o desde un navegador compatible (Chrome, Edge, Safari).',
                    icon: 'warning',
                    button: 'Entendido'
                });
                return;
            }

            me.enrolandoId = emp.id;

            try {
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);
                const userId = new Uint8Array(16);
                window.crypto.getRandomValues(userId);

                const empNombre = ((emp.nombre || '') + ' ' + (emp.apellido || '')).trim() || ('Empleado ' + emp.id);
                const empDoc = String(emp.num_doc || ('EMP_' + emp.id));

                const createOptions = {
                    publicKey: {
                        challenge: challenge,
                        rp: { 
                            name: "Sistema Empaques Lupa"
                        },
                        user: {
                            id: userId,
                            name: empDoc,
                            displayName: empNombre
                        },
                        pubKeyCredParams: [
                            { alg: -7, type: "public-key" },  // ES256
                            { alg: -257, type: "public-key" } // RS256
                        ],
                        authenticatorSelection: {
                            authenticatorAttachment: "platform", // Sensor nativo del dispositivo
                            userVerification: "preferred"        // 'preferred' es mucho más flexible y compatible
                        },
                        timeout: 60000
                    }
                };

                const host = window.location.hostname;
                if (host && !/^[0-9.]+$/.test(host) && host !== 'localhost') {
                    createOptions.publicKey.rp.id = host;
                }

                // Disparar el lector biométrico nativo del computador (Windows Hello)
                let cred;
                try {
                    cred = await navigator.credentials.create(createOptions);
                } catch (platErr) {
                    console.warn("Fallo con platform authenticator estricto, reintentando con fallback:", platErr);
                    if (createOptions.publicKey.authenticatorSelection) {
                        delete createOptions.publicKey.authenticatorSelection.authenticatorAttachment;
                    }
                    cred = await navigator.credentials.create(createOptions);
                }

                if (!cred) {
                    throw new Error('No se recibió lectura del sensor biométrico.');
                }

                let huellaData = me.bufferToBase64(cred.rawId) || cred.id;

                const response = await axios.post('/asistencia/enrolar-huella', {
                    empleado_id: emp.id,
                    huella_data: huellaData
                });

                // Actualizar localmente el empleado
                emp.huella_dactilar = huellaData;
                me.enrolandoId = null;

                me.reproducirSonidoExito();
                swal({
                    title: '¡Huella Vinculada!',
                    text: response.data.message || ('La huella dactilar de ' + emp.nombre + ' fue vinculada exitosamente en este lector.'),
                    icon: 'success',
                    button: 'Aceptar'
                });

            } catch (err) {
                me.enrolandoId = null;
                console.error('Error al registrar huella:', err);
                const detalle = (err.name ? err.name + ': ' : '') + (err.message || '');
                if (err.name === 'NotAllowedError') {
                    swal({
                        title: 'Sensor no activado / Cancelado',
                        text: 'Windows no completó la lectura de la huella (' + detalle + ').\n\nIMPORTANTE: Si es un lector integrado en la laptop, verifique tener configurada la huella en:\nConfiguración de Windows > Cuentas > Opciones de inicio de sesión > Reconocimiento de huellas digitales (Windows Hello).',
                        icon: 'warning'
                    });
                } else if (err.name === 'NotSupportedError') {
                    swal('Lector del Dispositivo', 'Este equipo o navegador no tiene activo el detector de huella nativo (Windows Hello).\n\nDetalle: ' + detalle, 'warning');
                } else if (err.name === 'InvalidStateError') {
                    swal('Atención', 'Esta huella ya fue registrada previamente en este dispositivo.', 'info');
                } else {
                    swal('Aviso Lector', 'No se pudo activar el lector biométrico: ' + detalle, 'warning');
                }
            }
        },
        reVincularHuellaSeleccionado() {
            let me = this;
            if (!me.empleadoSeleccionadoId) {
                swal('Atención', 'Seleccione un empleado en la lista para vincular su huella en este lector.', 'info');
                return;
            }
            let emp = me.empleados.find(e => e.id == me.empleadoSeleccionadoId);
            if (emp) {
                me.iniciarRegistroHuellaEmpleado(emp);
            }
        },
        desvincularHuellaEmpleado(emp) {
            let me = this;
            swal({
                title: '¿Desvincular Huella?',
                text: '¿Desea eliminar la huella registrada de ' + emp.nombre + ' ' + (emp.apellido || '') + '?',
                icon: 'warning',
                buttons: ['Cancelar', 'Sí, desvincular'],
                dangerMode: true
            }).then((willDelete) => {
                if (willDelete) {
                    axios.post('/asistencia/eliminar-huella', { empleado_id: emp.id }).then(() => {
                        emp.huella_dactilar = null;
                        swal('Huella Eliminada', 'La huella ha sido desvinculada exitosamente.', 'success');
                    }).catch(err => {
                        swal('Error', 'No se pudo eliminar la huella.', 'error');
                    });
                }
            });
        },
        async capturarYReconocerHuella() {
            let me = this;
            if (me.loading) return;

            // Verificar si el navegador soporta WebAuthn
            if (!navigator.credentials || !navigator.credentials.get) {
                swal({
                    title: 'Lector No Disponible',
                    text: 'Este navegador o conexión no permite activar el lector biométrico. Ingrese mediante HTTPS (https://' + window.location.hostname + ') o utilice Reconocimiento Facial o Código QR.',
                    icon: 'warning',
                    button: 'Entendido'
                });
                return;
            }

            const empleadosConHuella = me.empleados.filter(e => e.huella_dactilar);

            // Si hay un empleado seleccionado
            let empTarget = null;
            if (me.empleadoSeleccionadoId) {
                empTarget = me.empleados.find(e => e.id == me.empleadoSeleccionadoId);
                // Si el empleado seleccionado aún no tiene huella, alertar para que primero la grabe
                if (empTarget && !empTarget.huella_dactilar) {
                    swal({
                        title: 'Sin Huella Registrada',
                        text: empTarget.nombre + ' ' + (empTarget.apellido || '') + ' aún no tiene su huella vinculada en el sistema. Debe registrar su huella primero antes de poder marcar asistencia.',
                        icon: 'warning',
                        buttons: ['Cerrar', 'Grabar Huella Ahora']
                    }).then((confirm) => {
                        if (confirm) {
                            me.iniciarRegistroHuellaEmpleado(empTarget);
                        }
                    });
                    return;
                }
            } else if (empleadosConHuella.length === 0) {
                // Si no hay empleados con huella y no seleccionó ninguno, abrir modal de registro
                swal({
                    title: 'Registrar Huella Primero',
                    text: 'Aún no hay huellas registradas en este equipo. Seleccione su nombre en la lista o en "Registrar Huellas" para activar el lector del dispositivo.',
                    icon: 'info',
                    buttons: {
                        cancel: 'Cerrar',
                        enrolar: { text: 'Abrir Registro de Huellas', value: true }
                    }
                }).then((val) => {
                    if (val) me.abrirModalRegistroHuella();
                });
                return;
            }

            let allowedCredentials = [];
            if (empTarget && empTarget.huella_dactilar) {
                const rawArr = me.base64ToUint8Array(empTarget.huella_dactilar);
                if (rawArr) {
                    allowedCredentials.push({ 
                        type: 'public-key', 
                        id: rawArr,
                        transports: ['internal']
                    });
                }
            } else {
                for (let emp of empleadosConHuella) {
                    const rawArr = me.base64ToUint8Array(emp.huella_dactilar);
                    if (rawArr) {
                        allowedCredentials.push({ 
                            type: 'public-key', 
                            id: rawArr,
                            transports: ['internal']
                        });
                    }
                }
            }

            me.loading = true;

            try {
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);

                const getOptions = {
                    publicKey: {
                        challenge: challenge,
                        timeout: 60000,
                        userVerification: 'preferred'
                    }
                };

                const host = window.location.hostname;
                if (host && !/^[0-9.]+$/.test(host) && host !== 'localhost') {
                    getOptions.publicKey.rpId = host;
                }

                if (allowedCredentials.length > 0) {
                    getOptions.publicKey.allowCredentials = allowedCredentials;
                }

                // Disparar directamente el lector de huella del dispositivo (Windows Hello / Android Biometrics)
                let assertion;
                try {
                    assertion = await navigator.credentials.get(getOptions);
                } catch (getErr) {
                    if (allowedCredentials.length > 0) {
                        // Reintentar sin restricción estricta de transporte si el sensor del SO lo requiere
                        getOptions.publicKey.allowCredentials = allowedCredentials.map(c => ({
                            type: c.type,
                            id: c.id
                        }));
                        assertion = await navigator.credentials.get(getOptions);
                    } else {
                        throw getErr;
                    }
                }

                if (!assertion) {
                    throw new Error('No se detectó lectura del sensor de huella.');
                }

                const huellaToken = me.bufferToBase64(assertion.rawId) || assertion.id;

                let coords = null;
                if (navigator.geolocation) {
                    try {
                        const pos = await new Promise((resolve, reject) => {
                            navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 3000, enableHighAccuracy: true });
                        });
                        coords = pos.coords;
                    } catch (e) {}
                }

                let payload = {
                    huella_data: huellaToken,
                    empleado_id: me.empleadoSeleccionadoId || (empTarget ? empTarget.id : ''),
                    tipo_evento: me.kioscoEvento === 'auto' ? '' : me.kioscoEvento
                };
                if (coords) {
                    payload.latitud = coords.latitude;
                    payload.longitud = coords.longitude;
                }

                const response = await axios.post('/asistencia/reconocer-huella', payload);
                me.loading = false;
                me.mostrarVentanaExito(response.data.data);

            } catch (err) {
                me.loading = false;
                console.error('Error al escanear huella:', err);
                if (err.name === 'NotAllowedError') {
                    swal({
                        title: 'Huella no vinculada en este equipo',
                        text: 'El lector nativo de este computador no reconoció la huella de ' + (empTarget ? empTarget.nombre : 'este trabajador') + ' (o la credencial fue creada en otro equipo).\n\n¿Desea vincular la huella en el detector nativo de este computador ahora?',
                        icon: 'info',
                        buttons: ['Cerrar', 'Vincular a este Lector']
                    }).then((confirm) => {
                        if (confirm) {
                            if (empTarget) {
                                me.iniciarRegistroHuellaEmpleado(empTarget);
                            } else {
                                me.abrirModalRegistroHuella();
                            }
                        }
                    });
                } else if (err.name === 'NotSupportedError') {
                    swal('Lector del Dispositivo', 'Para activar el detector de huellas nativo en este equipo, configure Windows Hello en:\n\nInicio > Configuración > Cuentas > Opciones de inicio de sesión > Reconocimiento de huellas digitales (Windows Hello).', 'warning');
                } else if (err.response && err.response.data && err.response.data.message) {
                    swal('Atención Huella', err.response.data.message, err.response.status === 422 ? 'info' : 'error');
                } else {
                    swal('Aviso Lector', (err.message || 'No se pudo leer la huella dactilar desde el lector del dispositivo.'), 'warning');
                }
            }
        },
        cargarEmpleados() {
            let me = this;
            axios.get('/asistencia').then(res => {
                if (res.data && res.data.empleados) {
                    me.empleados = res.data.empleados;
                }
            }).catch(err => console.log(err));
        },
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }
    },
    mounted() {
        let me = this;
        // Garantizar contexto seguro HTTPS para habilitar el sensor de huella del equipo
        if (window.location.protocol === 'http:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            window.location.href = window.location.href.replace('http:', 'https:');
            return;
        }
        me.cargarEmpleados();
        me.iniciarCamara();
        me.iniciarAutoScanTimer();
        me.verificarSensorHuellaDispositivo().then(res => {
            me.soporteHuella = res;
        });
        me.intervalToken = setInterval(() => {
            me.tokenDinamico = Math.floor(Math.random() * 900000) + 100000;
            me.horaActual = new Date().toLocaleTimeString();
        }, 1000);
    },
    beforeDestroy() {
        if (this.intervalToken) clearInterval(this.intervalToken);
        if (this.intervalAutoScan) clearInterval(this.intervalAutoScan);
        if (this.timerExito) clearInterval(this.timerExito);
        this.detenerCamara();
    }
};
</script>

<style scoped>
.kiosco-fullscreen {
    min-height: 100vh;
    background-color: #0f172a;
}
.gap-1 { gap: 0.25rem; }
.gap-2 { gap: 0.5rem; }

.camera-wrapper {
    max-width: 480px;
    height: 250px;
}

.face-oval {
    width: 150px;
    height: 190px;
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.6);
    border-width: 3px !important;
}

@media (max-width: 576px) {
    .camera-wrapper {
        height: 210px;
    }
    .face-oval {
        width: 130px;
        height: 165px;
    }
}

.scanner-line {
    width: 100%;
    height: 3px;
    background: #22c55e;
    box-shadow: 0 0 12px #22c55e;
    position: absolute;
    top: 0;
    animation: scanMove 2.5s infinite ease-in-out;
}

@keyframes scanMove {
    0% { top: 0%; opacity: 0.8; }
    50% { top: 95%; opacity: 1; }
    100% { top: 0%; opacity: 0.8; }
}

/* Modal Éxito y Prevención de Doble Escaneo */
.modal-exito-overlay {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(8px);
    z-index: 99999 !important;
}

.modal-exito-content {
    animation: popIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.success-icon-badge {
    width: 85px;
    height: 85px;
}

.rounded-20 {
    border-radius: 20px !important;
}

.rounded-15 {
    border-radius: 15px !important;
}

.font-weight-900 {
    font-weight: 900 !important;
}

@keyframes popIn {
    0% { transform: scale(0.85); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}

/* Estilos Huella Dactilar */
.fingerprint-wrapper {
    transition: all 0.25s ease-in-out;
}

.fingerprint-wrapper:hover {
    border-color: #fbbf24 !important;
    background: radial-gradient(circle, #334155 0%, #0f172a 100%) !important;
    transform: translateY(-2px);
}

.fingerprint-scan-beam {
    width: 100%;
    height: 4px;
    background: #f59e0b;
    box-shadow: 0 0 14px #f59e0b;
    position: absolute;
    top: 0;
    left: 0;
    animation: scanMove 1.8s infinite ease-in-out;
}

.animate-pulse-fast {
    animation: pulseFast 1s infinite alternate;
}

@keyframes pulseFast {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(1.1); opacity: 1; }
}

.cursor-pointer {
    cursor: pointer !important;
}
</style>
