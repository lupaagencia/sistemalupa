<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb shadow-sm border-0 mb-4 bg-white py-2 px-3 rounded">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active text-primary font-weight-bold">Control de Asistencia, Turnos & Código QR</li>
        </ol>

        <div class="container-fluid">
            <!-- Summary KPI Cards with Solid Rich Colors -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded text-white p-3" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); min-height: 110px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="mb-1 font-weight-bold text-white">{{ kpis.marcaciones_hoy }}</h2>
                                <p class="mb-0 text-white-50 small font-weight-bold">Marcaciones Registradas Hoy</p>
                            </div>
                            <div class="rounded-circle bg-white-20 p-3 text-white">
                                <i class="fa fa-qrcode fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded text-white p-3" style="background: linear-gradient(135deg, #991b1b 0%, #ef4444 100%); min-height: 110px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="mb-1 font-weight-bold text-white">{{ kpis.llegadas_tarde_mes }}</h2>
                                <p class="mb-0 text-white-50 small font-weight-bold">Llegadas Tarde este Mes ({{ kpis.minutos_tardanza_mes }} min)</p>
                            </div>
                            <div class="rounded-circle bg-white-20 p-3 text-white">
                                <i class="fa fa-clock-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded text-white p-3" style="background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); min-height: 110px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="mb-1 font-weight-bold text-white">{{ kpis.horas_extras_mes }} hrs</h2>
                                <p class="mb-0 text-white-50 small font-weight-bold">Horas Extras Acumuladas este Mes</p>
                            </div>
                            <div class="rounded-circle bg-white-20 p-3 text-white">
                                <i class="fa fa-battery-full fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded text-white p-3" style="background: linear-gradient(135deg, #334155 0%, #64748b 100%); min-height: 110px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="mb-1 font-weight-bold text-white">{{ empleados.length }}</h2>
                                <p class="mb-0 text-white-50 small font-weight-bold">Personal Activo Registrado</p>
                            </div>
                            <div class="rounded-circle bg-white-20 p-3 text-white">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Panel Container -->
            <div class="card border-0 shadow-sm rounded overflow-hidden">
                <!-- Navigation Tabs Bar -->
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <ul class="nav nav-tabs border-0 font-weight-bold" id="asistenciaTabs" v-if="!kioscoBloqueado">
                        <li class="nav-item">
                            <a class="nav-link py-2 px-3 border-0" :class="{'active text-primary border-bottom border-primary font-weight-bold': tabActiva === 'estadisticas'}" href="#" @click.prevent="tabActiva = 'estadisticas'">
                                <i class="fa fa-line-chart mr-2"></i> 📊 Estadísticas & Analítica
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 px-3 border-0" :class="{'active text-primary border-bottom border-primary font-weight-bold': tabActiva === 'kiosco'}" href="#" @click.prevent="tabActiva = 'kiosco'">
                                <i class="fa fa-camera mr-2"></i> 📷 Kiosco Marcador QR
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 px-3 border-0" :class="{'active text-primary border-bottom border-primary font-weight-bold': tabActiva === 'historial'}" href="#" @click.prevent="tabActiva = 'historial'">
                                <i class="fa fa-calendar mr-2"></i> 📅 Monitoreo Diario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 px-3 border-0" :class="{'active text-primary border-bottom border-primary font-weight-bold': tabActiva === 'reportes'}" href="#" @click.prevent="tabActiva = 'reportes'; cargarReportes();">
                                <i class="fa fa-clock-o mr-2"></i> ⏱️ Reporte Tardanzas & Extras
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 px-3 border-0" :class="{'active text-primary border-bottom border-primary font-weight-bold': tabActiva === 'config'}" href="#" @click.prevent="tabActiva = 'config'; cargarTurnos(); cargarConfigSeguridad();">
                                <i class="fa fa-cog mr-2"></i> ⚙️ Turnos & Seguridad GPS
                            </a>
                        </li>
                    </ul>
                    <div v-else class="font-weight-bold text-danger">
                        <i class="fa fa-lock mr-2"></i> ESTACIÓN KIOSCO BLOQUEADA EN PORTERÍA
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="/kiosco-asistencia" target="_blank" class="btn btn-success btn-sm font-weight-bold mr-2 shadow-sm">
                            <i class="fa fa-camera mr-1"></i> 📸 Abrir Estación Facial & QR
                        </a>
                        <button v-if="!kioscoBloqueado" class="btn btn-outline-danger btn-sm font-weight-bold" @click="bloquearKiosco()">
                            <i class="fa fa-lock mr-1"></i> Bloquear Pantalla
                        </button>
                        <button v-else class="btn btn-warning btn-sm font-weight-bold text-dark" @click="desbloquearKiosco()">
                            <i class="fa fa-unlock mr-1"></i> Desbloquear PIN
                        </button>
                    </div>
                </div>

                <div class="card-body p-4 bg-white">
                    <!-- TAB 0: ESTADÍSTICAS & ANALÍTICA -->
                    <div v-if="tabActiva === 'estadisticas'">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card border border-light-2 shadow-sm rounded p-4">
                                    <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-pie-chart text-primary mr-2"></i> Índice de Puntualidad del Mes</h5>
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3">
                                        <span class="font-weight-bold text-muted">Llegadas A Tiempo</span>
                                        <span class="h4 mb-0 font-weight-bold text-success">{{ Math.max(0, kpis.marcaciones_hoy - kpis.llegadas_tarde_mes) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                        <span class="font-weight-bold text-muted">Llegadas Tarde / Retardos</span>
                                        <span class="h4 mb-0 font-weight-bold text-danger">{{ kpis.llegadas_tarde_mes }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="card border border-light-2 shadow-sm rounded p-4">
                                    <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-bolt text-warning mr-2"></i> Resumen de Horas Extras</h5>
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3">
                                        <span class="font-weight-bold text-muted">Horas Extras Totales este Mes</span>
                                        <span class="h4 mb-0 font-weight-bold text-primary">{{ kpis.horas_extras_mes }} hrs</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                        <span class="font-weight-bold text-muted">Minutos de Tardanza Totales</span>
                                        <span class="h4 mb-0 font-weight-bold text-danger">{{ kpis.minutos_tardanza_mes }} min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 1: KIOSCO & PANTALLA DE MARCACIÓN QR EN VIVO -->
                    <div v-if="tabActiva === 'kiosco'">
                        <div class="mb-3 text-right">
                            <a href="/kiosco-asistencia" target="_blank" class="btn btn-dark font-weight-bold shadow-sm">
                                <i class="fa fa-tablet mr-2 text-warning"></i> 📺 Abrir Pantalla Completa para Tablet / Portería
                            </a>
                        </div>
                        <div class="row">
                            <!-- Columna 1: Lector y Código QR Dinámico de la Empresa -->
                            <div class="col-lg-6 mb-4">
                                <div class="card border border-light-2 shadow-sm rounded p-4 text-center bg-light h-100">
                                    <div class="mb-3">
                                        <span class="badge badge-primary px-3 py-2 text-uppercase font-weight-bold" style="font-size: 0.85rem;">
                                            Punto de Marcación en Vivo
                                        </span>
                                    </div>
                                    
                                    <h4 class="font-weight-bold text-dark mb-2">Escanee o Ingrese su Código QR</h4>
                                    <p class="text-muted small">El sistema cotejará su hora exacta de marcaje contra su <strong>Turno Asignado</strong> para determinar tardanzas o horas extras.</p>

                                    <!-- QR Dinámico en Pantalla -->
                                    <div class="my-3 p-3 bg-white d-inline-block rounded shadow-sm border mx-auto">
                                        <img :src="obtenerUrlQR()" 
                                             alt="QR Dinámico Pantalla" 
                                             class="img-fluid" style="width: 180px; height: 180px;">
                                        <small class="d-block text-muted mt-2 font-weight-bold" style="font-size: 0.75rem;">
                                            <i class="fa fa-refresh fa-spin text-primary mr-1"></i> Escanear con Celular (Refresca cada 15s)
                                        </small>
                                    </div>

                                    <form @submit.prevent="procesarMarcacionQR()" class="mt-2 text-left">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted">Escanear Carnet QR o Cédula:</label>
                                            <div class="input-group input-group-lg shadow-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0 text-primary">
                                                        <i class="fa fa-qrcode fa-lg"></i>
                                                    </span>
                                                </div>
                                                <input type="text" 
                                                       ref="inputQR"
                                                       class="form-control border-left-0 font-weight-bold text-primary" 
                                                       placeholder="Escanear Código QR..." 
                                                       v-model="inputCodigoQR" 
                                                       @keyup.enter.prevent="procesarMarcacionQR()"
                                                       @change="onInputChange()"
                                                       autofocus>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted d-block">Seleccionar Evento a Registrar:</label>
                                            <div class="btn-group-toggle d-flex flex-wrap gap-2 justify-content-center">
                                                <button type="button" 
                                                        class="btn btn-sm font-weight-bold m-1" 
                                                        :class="kioscoEvento === 'auto' ? 'btn-dark' : 'btn-outline-dark'" 
                                                        @click="kioscoEvento = 'auto'">
                                                    ✨ Auto-Detectar Siguiente Evento
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm font-weight-bold m-1" 
                                                        :class="kioscoEvento === 'entrada_manana' ? 'btn-success' : 'btn-outline-success'" 
                                                        @click="kioscoEvento = 'entrada_manana'">
                                                    🌅 Entrada Mañana
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm font-weight-bold m-1" 
                                                        :class="kioscoEvento === 'salida_almuerzo' ? 'btn-warning' : 'btn-outline-warning'" 
                                                        @click="kioscoEvento = 'salida_almuerzo'">
                                                    🍲 Salida Almuerzo
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm font-weight-bold m-1" 
                                                        :class="kioscoEvento === 'entrada_almuerzo' ? 'btn-info' : 'btn-outline-info'" 
                                                        @click="kioscoEvento = 'entrada_almuerzo'">
                                                    🥪 Entrada Almuerzo
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm font-weight-bold m-1" 
                                                        :class="kioscoEvento === 'salida_empresa' ? 'btn-danger' : 'btn-outline-danger'" 
                                                        @click="kioscoEvento = 'salida_empresa'">
                                                    🚪 Salida Empresa
                                                </button>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold shadow-sm" :disabled="loadingKiosco">
                                            <span v-if="loadingKiosco"><i class="fa fa-spinner fa-spin mr-2"></i> Procesando...</span>
                                            <span v-else><i class="fa fa-check-circle mr-2"></i> Registrar Marcación</span>
                                        </button>
                                    </form>

                                    <!-- Marcación manual rápida -->
                                    <div class="mt-4 pt-3 border-top text-left">
                                        <small class="font-weight-bold text-muted d-block mb-2">O seleccione directamente el empleado:</small>
                                        <div class="row">
                                            <div class="col-8">
                                                <select class="form-control form-control-sm" v-model="kioscoEmpleadoId">
                                                    <option value="">-- Seleccionar Empleado --</option>
                                                    <option v-for="emp in empleados" :key="emp.id" :value="emp.id">
                                                        {{ emp.nombre }} {{ emp.apellido }} (Doc: {{ emp.num_doc }})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-secondary btn-sm w-100 font-weight-bold" 
                                                        :disabled="!kioscoEmpleadoId" 
                                                        @click="procesarMarcacionManual()">
                                                    Marcar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 2: Resultado en Pantalla Gigante / Cotejo de Turno -->
                            <div class="col-lg-6 mb-4">
                                <div v-if="ultimoResultadoMarcaje" 
                                     class="card border-0 shadow-lg rounded p-4 text-center h-100 text-white" 
                                     :style="ultimoResultadoMarcaje.estado_llegada === 'llegada_tarde' ? 'background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);' : 'background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);'">
                                    
                                    <div class="avatar-container mb-3 mx-auto shadow rounded-circle bg-white p-1" style="width: 110px; height: 110px;">
                                        <img :src="ultimoResultadoMarcaje.foto ? ('/img/empleados/' + ultimoResultadoMarcaje.foto) : '/img/avatar.png'" 
                                             class="rounded-circle w-100 h-100" style="object-fit: cover;" 
                                             alt="Foto">
                                    </div>

                                    <h2 class="font-weight-bold mb-1 text-white">{{ ultimoResultadoMarcaje.empleado_nombre }}</h2>
                                    <p class="mb-2 text-white-50 font-weight-bold" style="font-size: 1.1rem;">{{ ultimoResultadoMarcaje.cargo || 'Empleado' }}</p>

                                    <div class="badge badge-light text-dark px-4 py-2 font-weight-bold mb-3 shadow-sm" style="font-size: 1.2rem;">
                                        {{ ultimoResultadoMarcaje.evento_etiqueta }}
                                    </div>

                                    <h1 class="font-weight-bold display-3 my-2 text-white">{{ ultimoResultadoMarcaje.hora_marcada }}</h1>
                                    <p class="small text-white-50 mb-3">{{ ultimoResultadoMarcaje.fecha }} | Turno: <strong>{{ ultimoResultadoMarcaje.turno_nombre }}</strong></p>

                                    <!-- Resultado del Cotejo con el Turno -->
                                    <div v-if="ultimoResultadoMarcaje.estado_llegada === 'llegada_tarde'" class="alert alert-light text-danger font-weight-bold mb-0 shadow-sm p-3">
                                        <h5 class="mb-1"><i class="fa fa-exclamation-triangle mr-2"></i> ¡LLEGADA TARDE DETECTADA!</h5>
                                        <p class="mb-0">El ingreso superó la hora del turno. Retardo registrado de <strong>{{ ultimoResultadoMarcaje.minutos_tardanza }} minutos</strong>.</p>
                                    </div>
                                    <div v-else-if="ultimoResultadoMarcaje.minutos_extras > 0" class="alert alert-light text-success font-weight-bold mb-0 shadow-sm p-3">
                                        <h5 class="mb-1"><i class="fa fa-battery-full mr-2"></i> ¡HORAS EXTRAS REGISTRADAS!</h5>
                                        <p class="mb-0">Tiempo adicional trabajado: <strong>{{ Math.floor(ultimoResultadoMarcaje.minutos_extras / 60) }}h {{ ultimoResultadoMarcaje.minutos_extras % 60 }}m extras</strong>.</p>
                                    </div>
                                    <div v-else class="alert alert-light text-success font-weight-bold mb-0 shadow-sm p-3">
                                        <h5 class="mb-1"><i class="fa fa-check-circle mr-2"></i> ENTRADA A TIEMPO</h5>
                                        <p class="mb-0">Registro dentro del horario oficial de su turno. ¡Excelente día!</p>
                                    </div>
                                </div>

                                <!-- Estado inicial / Esperando marcación -->
                                <div v-else class="card border border-light-2 shadow-sm rounded p-5 text-center bg-light h-100 d-flex align-items-center justify-content-center">
                                    <i class="fa fa-user-circle fa-4x text-muted mb-3"></i>
                                    <h4 class="font-weight-bold text-dark mb-2">Esperando Marcación...</h4>
                                    <p class="text-muted small max-w-400">Acerque su carnet al lector o escanee el código QR. El resultado del cotejo con su turno se mostrará en este panel.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: MONITOREO DIARIO DE ASISTENCIA Y COTEJO -->
                    <div v-if="tabActiva === 'historial'">
                        <!-- Filter Bar -->
                        <div class="row bg-light p-3 rounded border border-light-2 mb-3">
                            <div class="col-md-3 form-group">
                                <label class="small font-weight-bold text-muted">Fecha Desde</label>
                                <input type="date" class="form-control form-control-sm" v-model="filtroFechaInicio" @change="listarAsistencias(1)">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="small font-weight-bold text-muted">Fecha Hasta</label>
                                <input type="date" class="form-control form-control-sm" v-model="filtroFechaFin" @change="listarAsistencias(1)">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="small font-weight-bold text-muted">Estado Puntualidad</label>
                                <select class="form-control form-control-sm" v-model="filtroEstadoLlegada" @change="listarAsistencias(1)">
                                    <option value="">Todos los estados</option>
                                    <option value="a_tiempo">A Tiempo</option>
                                    <option value="llegada_tarde">Llegada Tarde</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="small font-weight-bold text-muted">Buscar Empleado</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" placeholder="Buscar..." v-model="buscar" @keyup.enter="listarAsistencias(1)">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" @click="listarAsistencias(1)"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Asistencias Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped border-light-2">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Fecha / Hora</th>
                                        <th>Empleado</th>
                                        <th>Tipo de Evento</th>
                                        <th>Turno Asignado</th>
                                        <th class="text-center">Cotejo Puntualidad</th>
                                        <th class="text-right">Retardo</th>
                                        <th class="text-right">Horas Extras</th>
                                        <th class="text-center">GPS Sede</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="asist in arrayAsistencias" :key="asist.id">
                                        <td>
                                            <strong class="text-dark d-block">{{ asist.hora_marcada }}</strong>
                                            <small class="text-muted">{{ asist.fecha }}</small>
                                        </td>
                                        <td>
                                            <strong v-if="asist.empleado" class="text-primary d-block">
                                                {{ asist.empleado.nombre }} {{ asist.empleado.apellido }}
                                            </strong>
                                            <small class="text-muted" v-if="asist.empleado">Doc: {{ asist.empleado.num_doc }} | {{ asist.empleado.cargo }}</small>
                                        </td>
                                        <td>
                                            <span class="badge p-2 font-weight-bold text-uppercase" :class="obtenerBadgeEvento(asist.tipo_evento)">
                                                {{ obtenerEtiquetaEvento(asist.tipo_evento) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span v-if="asist.turno" class="badge badge-light border text-dark font-weight-bold">
                                                <i class="fa fa-clock-o text-primary mr-1"></i> {{ asist.turno.nombre }}
                                            </span>
                                            <span v-else class="text-muted small">Sin turno</span>
                                        </td>
                                        <td class="text-center">
                                            <span v-if="asist.estado_llegada === 'llegada_tarde'" class="badge badge-danger px-2 py-1 font-weight-bold">
                                                <i class="fa fa-exclamation-triangle mr-1"></i> LLEGADA TARDE
                                            </span>
                                            <span v-else class="badge badge-success px-2 py-1 font-weight-bold">
                                                <i class="fa fa-check mr-1"></i> A Tiempo
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold" :class="asist.minutos_tardanza > 0 ? 'text-danger' : 'text-muted'">
                                            {{ asist.minutos_tardanza }} min
                                        </td>
                                        <td class="text-right font-weight-bold" :class="asist.minutos_extras > 0 ? 'text-success' : 'text-muted'">
                                            {{ asist.minutos_extras }} min ({{ roundHours(asist.minutos_extras) }}h)
                                        </td>
                                        <td class="text-center">
                                            <span v-if="asist.distancia_metros !== null" class="badge badge-light border text-muted" title="Distancia a la empresa">
                                                📍 {{ asist.distancia_metros }} m
                                            </span>
                                            <span v-else class="text-muted small">-</span>
                                        </td>
                                    </tr>
                                    <tr v-if="arrayAsistencias.length === 0">
                                        <td colspan="8" class="text-center py-4 text-muted">No se encontraron marcaciones de asistencia registradas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: REPORTES DE TARDANZAS Y HORAS EXTRAS -->
                    <div v-if="tabActiva === 'reportes'">
                        <div class="row">
                            <!-- Reporte Tardanzas -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm rounded">
                                    <div class="card-header bg-danger text-white font-weight-bold">
                                        <i class="fa fa-exclamation-triangle mr-2"></i> Reporte Acumulado de Llegadas Tarde
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Empleado</th>
                                                        <th class="text-center">Total Veces Tarde</th>
                                                        <th class="text-right">Minutos Acumulados</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="rep in reporteTardanzas" :key="'tarde-' + rep.empleado_id">
                                                        <td>
                                                            <strong v-if="rep.empleado">{{ rep.empleado.nombre }} {{ rep.empleado.apellido }}</strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-danger font-weight-bold">{{ rep.total_llegadas_tarde }}</span>
                                                        </td>
                                                        <td class="text-right font-weight-bold text-danger">
                                                            {{ rep.total_minutos }} min ({{ roundHours(rep.total_minutos) }}h)
                                                        </td>
                                                    </tr>
                                                    <tr v-if="reporteTardanzas.length === 0">
                                                        <td colspan="3" class="text-center text-muted py-3">No hay tardanzas registradas en este período.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte Horas Extras -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm rounded">
                                    <div class="card-header bg-success text-white font-weight-bold">
                                        <i class="fa fa-battery-full mr-2"></i> Reporte Acumulado de Horas Extras
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Empleado</th>
                                                        <th class="text-center">Días con Extras</th>
                                                        <th class="text-right">Total Horas Extras</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="rep in reporteExtras" :key="'extra-' + rep.empleado_id">
                                                        <td>
                                                            <strong v-if="rep.empleado">{{ rep.empleado.nombre }} {{ rep.empleado.apellido }}</strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success font-weight-bold">{{ rep.dias_con_extras }}</span>
                                                        </td>
                                                        <td class="text-right font-weight-bold text-success">
                                                            {{ roundHours(rep.total_minutos_extras) }} Horas ({{ rep.total_minutos_extras }} min)
                                                        </td>
                                                    </tr>
                                                    <tr v-if="reporteExtras.length === 0">
                                                        <td colspan="3" class="text-center text-muted py-3">No hay horas extras registradas en este período.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: TURNOS & SEGURIDAD GPS -->
                    <div v-if="tabActiva === 'config'">
                        <div class="row">
                            <!-- Definición de Turnos -->
                            <div class="col-md-7 mb-4">
                                <div class="card border-0 shadow-sm rounded">
                                    <div class="card-header bg-white border-bottom-light py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-clock-o text-primary mr-2"></i> Horarios y Turnos Laborales</h6>
                                        <button class="btn btn-primary btn-sm" @click="abrirModalTurno()">
                                            <i class="fa fa-plus mr-1"></i> Nuevo Turno
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Nombre Turno</th>
                                                        <th>Entrada</th>
                                                        <th>Tolerancia</th>
                                                        <th>Salida</th>
                                                        <th class="text-center">Empleados</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="tur in listadoTurnos" :key="tur.id">
                                                        <td><strong class="text-dark">{{ tur.nombre }}</strong></td>
                                                        <td><span class="badge badge-success">{{ tur.hora_entrada }}</span></td>
                                                        <td>{{ tur.tolerancia_minutos }} min</td>
                                                        <td><span class="badge badge-danger">{{ tur.hora_salida }}</span></td>
                                                        <td class="text-center"><span class="badge badge-info">{{ tur.empleados_count }}</span></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-info btn-sm text-white" @click="editarTurno(tur)">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="listadoTurnos.length === 0">
                                                        <td colspan="6" class="text-center text-muted py-3">No hay turnos creados.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Asignación de Turnos y QRs -->
                            <div class="col-md-5 mb-4">
                                <div class="card border-0 shadow-sm rounded">
                                    <div class="card-header bg-white border-bottom-light py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fa fa-users text-success mr-2"></i> Asignación de Turnos & QRs</h6>
                                    </div>
                                    <div class="card-body p-2" style="max-height: 420px; overflow-y: auto;">
                                        <ul class="list-group list-group-flush">
                                            <li v-for="emp in empleados" :key="emp.id" class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center py-2 px-2 border-bottom">
                                                <div class="mb-2 mb-sm-0 mr-2">
                                                    <strong class="text-dark d-block mb-0">{{ emp.nombre }} {{ emp.apellido }}</strong>
                                                    <small class="text-muted">Doc: {{ emp.num_doc }}</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <select class="form-control form-control-sm font-weight-bold border-primary text-primary" style="max-width: 160px;" v-model="emp.turno_id" @change="asignarTurnoEmpleado(emp.id, emp.turno_id)">
                                                        <option :value="null">-- Sin Turno --</option>
                                                        <option v-for="tur in listadoTurnos" :key="tur.id" :value="tur.id">
                                                            {{ tur.nombre }} ({{ tur.hora_entrada }})
                                                        </option>
                                                    </select>
                                                    <button class="btn btn-outline-primary btn-sm ml-1" title="Ver / Imprimir Carnet QR" @click="mostrarCarnetQR(emp)">
                                                        <i class="fa fa-qrcode"></i>
                                                    </button>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Perímetro GPS & PIN de Kiosco -->
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12 mb-3">
                                <h6 class="font-weight-bold text-dark"><i class="fa fa-shield text-danger mr-2"></i> Capa de Seguridad Anti-Fraude & Perímetro GPS</h6>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="card border-0 shadow-sm p-3 bg-light">
                                    <label class="font-weight-bold small text-muted">Latitud Oficial Empresa</label>
                                    <input type="text" class="form-control form-control-sm mb-2" v-model="configSeguridad.latitud_empresa" placeholder="Ej. 6.2442">
                                    
                                    <label class="font-weight-bold small text-muted">Longitud Oficial Empresa</label>
                                    <input type="text" class="form-control form-control-sm mb-2" v-model="configSeguridad.longitud_empresa" placeholder="Ej. -75.5812">
                                    
                                    <button class="btn btn-outline-secondary btn-sm mb-2 font-weight-bold" @click="capturarUbicacionEmpresa()">
                                        📍 Usar mi Ubicación GPS Actual como Empresa
                                    </button>

                                    <label class="font-weight-bold small text-muted">Radio Máximo Permitido (Metros)</label>
                                    <input type="number" class="form-control form-control-sm mb-2" v-model.number="configSeguridad.radio_maximo_metros" min="10" step="5">

                                    <div class="custom-control custom-checkbox my-2">
                                        <input type="checkbox" class="custom-control-input" id="checkGPS" v-model="configSeguridad.requerir_gps">
                                        <label class="custom-control-label font-weight-bold text-danger cursor-pointer" for="checkGPS">
                                            Requerir Validación GPS Obligatoria (Rechazar fuera de sede)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="card border-0 shadow-sm p-3 bg-light">
                                    <label class="font-weight-bold small text-muted">PIN de Bloqueo de Estación Kiosco (4 Dígitos)</label>
                                    <input type="password" class="form-control form-control-sm mb-3" v-model="configSeguridad.pin_kiosco" maxlength="6" placeholder="Ej. 1234">

                                    <p class="small text-muted mb-3">
                                        El PIN permite bloquear la pantalla de portería de modo inalterable para que nadie pueda cambiar de pestaña sin la clave del administrador.
                                    </p>

                                    <button class="btn btn-success btn-block font-weight-bold mt-2" @click="guardarConfigSeguridad()">
                                        <i class="fa fa-save mr-1"></i> Guardar Parámetros de Seguridad GPS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Crear/Editar Turno -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalTurno}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 3000 !important;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">{{ tipoAccionTurno === 1 ? 'Crear Nuevo Turno por Días' : 'Editar Programación Diaria del Turno' }}</h5>
                        <button type="button" class="close text-white" @click="cerrarModalTurno()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardarTurno()">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-dark">Nombre del Turno <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-weight-bold" v-model="formTurno.nombre" placeholder="Ej. Planta Producción (Lunes 7am / Martes-Viernes 8am)" required>
                            </div>

                            <h6 class="font-weight-bold text-primary mb-2 mt-4">
                                <i class="fa fa-calendar mr-1"></i> Horarios Diferenciados por Día de la Semana:
                            </h6>
                            <p class="small text-muted mb-3">Configure las horas de ingreso/salida para cada día o desmarque los días no laborables (ej. Sábados y Domingos).</p>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th>Día de Semana</th>
                                            <th>¿Es Laborable?</th>
                                            <th>Hora de Entrada</th>
                                            <th>Tolerancia (min)</th>
                                            <th>Hora de Salida</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(dia, idx) in formTurno.dias" :key="idx" :class="!dia.laborable ? 'bg-light text-muted' : ''">
                                            <td class="font-weight-bold text-dark align-middle">{{ dia.dia_nombre }}</td>
                                            <td class="align-middle">
                                                <div class="custom-control custom-checkbox d-inline-block">
                                                    <input type="checkbox" class="custom-control-input" :id="'checkDia_' + idx" v-model="dia.laborable">
                                                    <label class="custom-control-label font-weight-bold cursor-pointer" :for="'checkDia_' + idx" :class="dia.laborable ? 'text-success' : 'text-secondary'">
                                                        {{ dia.laborable ? 'SI (Trabaja)' : 'NO (Descanso)' }}
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm font-weight-bold" v-model="dia.hora_entrada" :disabled="!dia.laborable" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm text-center font-weight-bold" v-model.number="dia.tolerancia_minutos" min="0" :disabled="!dia.laborable" style="max-width: 80px; margin: 0 auto;" required>
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm font-weight-bold" v-model="dia.hora_salida" :disabled="!dia.laborable" required>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-right mt-4">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalTurno()">Cancelar</button>
                                <button type="submit" class="btn btn-success font-weight-bold"><i class="fa fa-save mr-1"></i> Guardar Programación del Turno</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ver Carnet QR Empleado -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalCarnet}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 3500 !important;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg text-center p-4">
                    <button type="button" class="close text-dark text-right" @click="modalCarnet = false">&times;</button>
                    <h5 class="font-weight-bold text-dark mt-2 mb-1">{{ carnetEmpleado.nombre }} {{ carnetEmpleado.apellido }}</h5>
                    <p class="text-muted small mb-3">Doc: {{ carnetEmpleado.num_doc }}</p>
                    
                    <div class="bg-light p-3 rounded border border-light-2 d-inline-block mx-auto mb-3">
                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + (carnetEmpleado.codigo_qr || carnetEmpleado.num_doc)" 
                             alt="Código QR" class="img-fluid">
                    </div>

                    <p class="font-weight-bold text-primary mb-3">{{ carnetEmpleado.codigo_qr || 'Sin QR' }}</p>
                    
                    <button class="btn btn-primary btn-sm font-weight-bold" @click="window.print()">
                        <i class="fa fa-print mr-1"></i> Imprimir Carnet
                    </button>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    props: ['user'],
    data() {
        return {
            tabActiva: 'estadisticas',
            inputCodigoQR: '',
            kioscoEvento: 'auto',
            kioscoEmpleadoId: '',
            loadingKiosco: false,
            ultimoResultadoMarcaje: null,
            tokenDinamico: Math.floor(Math.random() * 900000) + 100000,
            intervalToken: null,

            arrayAsistencias: [],
            empleados: [],
            turnos: [],
            kpis: {
                marcaciones_hoy: 0,
                llegadas_tarde_mes: 0,
                minutos_tardanza_mes: 0,
                horas_extras_mes: 0
            },
            pagination: { total: 0, current_page: 1, per_page: 15, last_page: 1 },
            filtroFechaInicio: new Date().toISOString().slice(0, 10),
            filtroFechaFin: new Date().toISOString().slice(0, 10),
            filtroEstadoLlegada: '',
            buscar: '',

            reporteTardanzas: [],
            reporteExtras: [],

            listadoTurnos: [],
            modalTurno: false,
            tipoAccionTurno: 1,
            formTurno: {
                id: 0,
                nombre: '',
                hora_entrada: '08:00',
                tolerancia_minutos: 10,
                hora_salida_almuerzo: '12:00',
                hora_entrada_almuerzo: '13:00',
                hora_salida: '17:00'
            },

            carnetEmpleado: {},

            configSeguridad: {
                latitud_empresa: '',
                longitud_empresa: '',
                radio_maximo_metros: 100,
                requerir_gps: false,
                pin_kiosco: '1234'
            },
            kioscoBloqueado: false,
            timerScan: null
        };
    },
    watch: {
        inputCodigoQR(val) {
            if (val && val.length >= 3 && !this.loadingKiosco) {
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
        obtenerUrlQR() {
            let baseUrl = window.location.origin;
            let targetUrl = baseUrl + '/marcar-asistencia?token=' + this.tokenDinamico;
            return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(targetUrl);
        },
        onInputChange() {
            if (this.inputCodigoQR && !this.loadingKiosco) {
                clearTimeout(this.timerScan);
                this.procesarMarcacionQR();
            }
        },
        listarAsistencias(page) {
            let me = this;
            let url = '/asistencia?page=' + page + '&fecha_inicio=' + me.filtroFechaInicio + '&fecha_fin=' + me.filtroFechaFin + '&estado_llegada=' + me.filtroEstadoLlegada + '&buscar=' + me.buscar;
            axios.get(url).then(function (response) {
                let resp = response.data;
                me.arrayAsistencias = resp.asistencias.data;
                me.pagination = resp.pagination;
                if (resp.kpis) me.kpis = resp.kpis;
                if (resp.empleados) me.empleados = resp.empleados;
                if (resp.turnos) me.turnos = resp.turnos;
            });
        },
        procesarMarcacionQR() {
            if (!this.inputCodigoQR || this.loadingKiosco) return;
            clearTimeout(this.timerScan);
            let code = this.inputCodigoQR.trim();
            this.enviarMarcacion({ codigo_qr: code, tipo_evento: this.kioscoEvento === 'auto' ? '' : this.kioscoEvento });
        },
        procesarMarcacionManual() {
            if (!this.kioscoEmpleadoId || this.loadingKiosco) return;
            this.enviarMarcacion({ empleado_id: this.kioscoEmpleadoId, tipo_evento: this.kioscoEvento === 'auto' ? '' : this.kioscoEvento });
        },
        enviarMarcacion(payload) {
            let me = this;
            if (me.loadingKiosco) return;
            me.loadingKiosco = true;
            clearTimeout(me.timerScan);

            let finalizar = () => {
                me.loadingKiosco = false;
                me.inputCodigoQR = '';
                me.kioscoEmpleadoId = '';
                me.$nextTick(() => {
                    if (me.$refs.inputQR) me.$refs.inputQR.focus();
                });
            };

            let enviarAXios = (coords) => {
                if (coords) {
                    payload.latitud = coords.latitude;
                    payload.longitud = coords.longitude;
                }
                axios.post('/asistencia/registrar-qr', payload).then(function (response) {
                    me.ultimoResultadoMarcaje = response.data.data;
                    swal('¡Marcaje OK!', response.data.message, 'success');
                    me.listarAsistencias(1);
                    finalizar();
                }).catch(function (error) {
                    let msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error al registrar marcación.';
                    swal('Error', msg, 'error');
                    finalizar();
                });
            };

            if (navigator.geolocation && me.configSeguridad.requerir_gps) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => enviarAXios(pos.coords),
                    (err) => enviarAXios(null),
                    { timeout: 5000, enableHighAccuracy: true }
                );
            } else {
                enviarAXios(null);
            }
        },
        cargarReportes() {
            let me = this;
            axios.get('/asistencia/reporte-llegadas-tarde?fecha_inicio=' + me.filtroFechaInicio + '&fecha_fin=' + me.filtroFechaFin).then(function (res) {
                me.reporteTardanzas = res.data.reporte;
            });
            axios.get('/asistencia/reporte-horas-extras?fecha_inicio=' + me.filtroFechaInicio + '&fecha_fin=' + me.filtroFechaFin).then(function (res) {
                me.reporteExtras = res.data.reporte;
            });
        },
        cargarTurnos() {
            let me = this;
            axios.get('/asistencia/turnos').then(function (res) {
                me.listadoTurnos = res.data.turnos;
            });
        },
        abrirModalTurno() {
            let diasDefecto = [
                { dia_num: 1, dia_nombre: 'Lunes', laborable: true, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '17:00' },
                { dia_num: 2, dia_nombre: 'Martes', laborable: true, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '17:00' },
                { dia_num: 3, dia_nombre: 'Miércoles', laborable: true, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '17:00' },
                { dia_num: 4, dia_nombre: 'Jueves', laborable: true, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '17:00' },
                { dia_num: 5, dia_nombre: 'Viernes', laborable: true, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '17:00' },
                { dia_num: 6, dia_nombre: 'Sábado', laborable: false, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '12:00' },
                { dia_num: 7, dia_nombre: 'Domingo', laborable: false, hora_entrada: '08:00', tolerancia_minutos: 10, hora_salida: '12:00' }
            ];
            this.tipoAccionTurno = 1;
            this.formTurno = { id: 0, nombre: '', dias: diasDefecto };
            this.modalTurno = true;
        },
        editarTurno(tur) {
            let nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            let diasForm = [];
            for (let i = 1; i <= 7; i++) {
                let dExistente = (tur.dias || []).find(d => d.dia_num === i);
                if (dExistente) {
                    diasForm.push({
                        dia_num: i,
                        dia_nombre: dExistente.dia_nombre || nombres[i-1],
                        laborable: !!dExistente.laborable,
                        hora_entrada: dExistente.hora_entrada ? dExistente.hora_entrada.substring(0,5) : '08:00',
                        tolerancia_minutos: dExistente.tolerancia_minutos !== undefined ? dExistente.tolerancia_minutos : 10,
                        hora_salida: dExistente.hora_salida ? dExistente.hora_salida.substring(0,5) : '17:00'
                    });
                } else {
                    diasForm.push({
                        dia_num: i,
                        dia_nombre: nombres[i-1],
                        laborable: i <= 5,
                        hora_entrada: tur.hora_entrada ? tur.hora_entrada.substring(0,5) : '08:00',
                        tolerancia_minutos: tur.tolerancia_minutos || 10,
                        hora_salida: tur.hora_salida ? tur.hora_salida.substring(0,5) : '17:00'
                    });
                }
            }
            this.tipoAccionTurno = 2;
            this.formTurno = { id: tur.id, nombre: tur.nombre, dias: diasForm };
            this.modalTurno = true;
        },
        cerrarModalTurno() {
            this.modalTurno = false;
        },
        guardarTurno() {
            let me = this;
            let url = me.tipoAccionTurno === 1 ? '/asistencia/turnos/registrar' : ('/asistencia/turnos/actualizar/' + me.formTurno.id);
            let method = me.tipoAccionTurno === 1 ? 'post' : 'put';
            axios({ method: method, url: url, data: me.formTurno }).then(function (response) {
                me.cerrarModalTurno();
                swal('¡Éxito!', response.data.message, 'success');
                me.cargarTurnos();
            });
        },
        asignarTurnoEmpleado(empleadoId, turnoId) {
            let me = this;
            axios.post('/asistencia/asignar-turno-empleado', {
                empleado_id: empleadoId,
                turno_id: turnoId
            }).then(function (response) {
                swal('Turno Asignado', response.data.message, 'success');
                me.cargarTurnos();
            }).catch(function (error) {
                swal('Error', 'No se pudo asignar el turno.', 'error');
            });
        },
        mostrarCarnetQR(emp) {
            let me = this;
            if (!emp.codigo_qr) {
                axios.post('/asistencia/generar-qr-empleado/' + emp.id).then(function (res) {
                    emp.codigo_qr = res.data.codigo_qr;
                    me.carnetEmpleado = emp;
                    me.modalCarnet = true;
                });
            } else {
                me.carnetEmpleado = emp;
                me.modalCarnet = true;
            }
        },
        obtenerEtiquetaEvento(tipo) {
            switch(tipo) {
                case 'entrada_manana': return '🌅 Ingreso / Entrada';
                case 'salida_receso': return '☕ Salida Receso';
                case 'entrada_receso': return '⏱️ Ingreso Receso';
                case 'salida_almuerzo': return '🍲 Salida Almuerzo';
                case 'entrada_almuerzo': return '🥪 Ingreso Almuerzo';
                case 'salida_empresa': return '🚪 Salida';
                default: return tipo;
            }
        },
        obtenerBadgeEvento(tipo) {
            switch(tipo) {
                case 'entrada_manana': return 'badge-success';
                case 'salida_receso': return 'badge-warning text-dark';
                case 'entrada_receso': return 'badge-info';
                case 'salida_almuerzo': return 'badge-warning text-dark';
                case 'entrada_almuerzo': return 'badge-info';
                case 'salida_empresa': return 'badge-danger';
                default: return 'badge-secondary';
            }
        },
        roundHours(min) {
            if (!min) return '0.0';
            return (min / 60).toFixed(1);
        },
        cargarConfigSeguridad() {
            let me = this;
            axios.get('/asistencia/configuracion-seguridad').then(function (res) {
                if (res.data.configuracion) {
                    me.configSeguridad = res.data.configuracion;
                }
            });
        },
        guardarConfigSeguridad() {
            let me = this;
            axios.post('/asistencia/configuracion-seguridad', me.configSeguridad).then(function (res) {
                swal('¡Éxito!', res.data.message, 'success');
            });
        },
        capturarUbicacionEmpresa() {
            let me = this;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    me.configSeguridad.latitud_empresa = pos.coords.latitude;
                    me.configSeguridad.longitud_empresa = pos.coords.longitude;
                    swal('Coordenadas Capturadas', 'Lat: ' + pos.coords.latitude + ', Lon: ' + pos.coords.longitude, 'success');
                }, function (err) {
                    swal('Error GPS', 'No se pudo obtener la ubicación actual del navegador.', 'error');
                });
            } else {
                swal('Error GPS', 'Su navegador no soporta geolocalización.', 'error');
            }
        },
        bloquearKiosco() {
            this.kioscoBloqueado = true;
            this.tabActiva = 'kiosco';
            swal('Estación Bloqueada', 'El kiosco de portería está bloqueado. Requiere PIN para salir.', 'info');
        },
        desbloquearKiosco() {
            let me = this;
            swal({
                title: 'Ingrese el PIN de Bloqueo',
                input: 'password',
                inputPlaceholder: 'PIN de 4 dígitos...',
                showCancelButton: true,
                confirmButtonText: 'Desbloquear',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value === (me.configSeguridad.pin_kiosco || '1234')) {
                    me.kioscoBloqueado = false;
                    swal('Desbloqueado', 'Acceso a paneles administrativos restaurado.', 'success');
                } else if (result.value) {
                    swal('Error', 'PIN Incorrecto.', 'error');
                }
            });
        }
    },
    mounted() {
        let me = this;
        me.listarAsistencias(1);
        me.cargarConfigSeguridad();
        
        // Refrescar el token QR dinámico en pantalla cada 15 segundos
        me.intervalToken = setInterval(() => {
            me.tokenDinamico = Math.floor(Math.random() * 900000) + 100000;
        }, 15000);
    },
    beforeDestroy() {
        if (this.intervalToken) clearInterval(this.intervalToken);
    }
};
</script>

<style scoped>
.gap-2 { gap: 0.5rem; }
.bg-white-20 { background-color: rgba(255, 255, 255, 0.2); }
</style>
