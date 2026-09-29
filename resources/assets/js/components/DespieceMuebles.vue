<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Despiece Muebles Cocina</li>
        </ol>

        <div class="container-fluid">
            <!-- VISTA 1: LISTADO DE PROYECTOS -->
            <div v-if="vista === 'listado'" class="card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="h5 font-weight-bold text-dark mb-0">
                        <i class="fa fa-folder text-warning"></i> Proyectos de Despiece y Cortes
                    </span>
                    <button type="button" @click="nuevoProyecto()" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Nuevo Proyecto
                    </button>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select class="form-control col-md-3" v-model="criterio">
                                    <option value="cliente">Cliente</option>
                                    <option value="descripcion">Descripción</option>
                                </select>
                                <input type="text" v-model="buscar" @keyup.enter="listarProyectos(1)" class="form-control" placeholder="Texto a buscar">
                                <button type="button" @click="listarProyectos(1)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Proyectos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm text-center">
                            <thead>
                                <tr>
                                    <th>Opciones</th>
                                    <th>Proyecto ID</th>
                                    <th>Cliente</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="proyecto in arrayProyectos" :key="proyecto.id">
                                    <td>
                                        <button type="button" @click="abrirProyecto(proyecto)" class="btn btn-success btn-xs" title="Ver Detalles y Muebles">
                                            <i class="fa fa-eye"></i> Entrar
                                        </button>
                                        <button type="button" @click="editarProyecto(proyecto)" class="btn btn-warning btn-xs text-dark" title="Editar Información">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" @click="eliminarProyecto(proyecto.id)" class="btn btn-danger btn-xs" title="Eliminar Proyecto">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                    <td>#{{ String(proyecto.id).padStart(5, '0') }}</td>
                                    <td class="text-left font-weight-bold">{{ proyecto.cliente || 'N/A' }}</td>
                                    <td class="text-left">{{ proyecto.descripcion }}</td>
                                    <td>{{ formatFecha(proyecto.fecha) }}</td>
                                </tr>
                                <tr v-if="arrayProyectos.length === 0">
                                    <td colspan="5">No se encontraron proyectos registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <nav>
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- VISTA 2: DETALLES DEL PROYECTO (DASHBOARD) -->
            <div v-if="vista === 'proyecto'" class="card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" @click="volverAlListado()" class="btn btn-outline-secondary btn-sm mr-2">
                            <i class="fa fa-arrow-left"></i> Volver
                        </button>
                        <span class="h5 font-weight-bold text-dark mb-0">
                            Proyecto: {{ proyectoSeleccionado.cliente || 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <button type="button" @click="vista = 'optimizador'" class="btn btn-success btn-sm mr-2">
                            <i class="fa fa-cut"></i> Optimizar Cortes
                        </button>
                        <a :href="`/despiece/pdf/${proyectoSeleccionado.id}`" target="_blank" class="btn btn-danger btn-sm mr-2">
                            <i class="fa fa-file-pdf-o"></i> Descargar PDF
                        </a>
                        <button type="button" @click="nuevoMueble()" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Agregar Mueble
                        </button>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <!-- Resumen del Proyecto -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-primary mb-2"><i class="fa fa-info-circle"></i> Datos Generales</h6>
                                    <p class="mb-1"><strong>Descripción:</strong> {{ proyectoSeleccionado.descripcion }}</p>
                                    <p class="mb-0"><strong>Fecha de Creación:</strong> {{ formatFecha(proyectoSeleccionado.fecha) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-success text-white">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-white mb-2"><i class="fa fa-calculator"></i> Consumo Total Estimado</h6>
                                    <p class="mb-1 h5"><strong>Melamina:</strong> {{ totalMelaminaProyecto.toFixed(2) }} m²</p>
                                    <p class="mb-0 h5"><strong>Canto:</strong> {{ totalCantoProyecto.toFixed(1) }} m</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Listado de Muebles -->
                    <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-cubes"></i> Módulos y Muebles del Proyecto</h5>
                    <div class="row">
                        <div class="col-md-12" v-for="mueble in arrayMuebles" :key="mueble.id">
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fa fa-cube text-primary"></i> {{ mueble.nombre }} 
                                        <span class="badge badge-info ml-2">Módulo {{ mueble.tipo_mueble }}</span>
                                    </span>
                                    <div>
                                        <button @click="editarMueble(mueble)" class="btn btn-warning btn-xs text-dark mr-1" title="Editar Mueble">
                                            <i class="fa fa-pencil"></i> Editar
                                        </button>
                                        <button @click="eliminarMueble(mueble.id)" class="btn btn-danger btn-xs" title="Eliminar Mueble">
                                            <i class="fa fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Dimensiones:</strong> {{ mueble.ancho }} x {{ mueble.alto }} x {{ mueble.profundidad }} mm</p>
                                            <p class="mb-1"><strong>Espesor:</strong> {{ mueble.espesor_material }} mm</p>
                                            <p class="mb-1"><strong>Mat. Casco:</strong> {{ mueble.material_interno || mueble.material || 'Blanco' }}</p>
                                            <p class="mb-1"><strong>Mat. Vistas:</strong> {{ mueble.material_externo || mueble.material || 'N/A' }}</p>
                                            <p class="mb-1" v-if="mueble.tipo_meson"><strong>Tapa/Mesón:</strong> {{ mueble.tipo_meson }} &nbsp;|&nbsp; <strong>Costados Vistos:</strong> {{ mueble.costados_vistos || 'Ninguno' }}</p>
                                            <p class="mb-1" v-if="mueble.sistema_apertura"><strong>Apertura:</strong> {{ mueble.sistema_apertura }} <span v-if="mueble.tipo_tirador && mueble.tipo_tirador !== 'Sin Tirador'">({{ mueble.tipo_tirador }})</span></p>
                                            <p class="mb-0" v-if="mueble.notas"><strong>Notas:</strong> {{ mueble.notas }}</p>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm text-center mb-0" style="font-size: 11px;">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Pieza</th>
                                                            <th>Material</th>
                                                            <th>Cant.</th>
                                                            <th>Largo (mm)</th>
                                                            <th>Ancho (mm)</th>
                                                            <th>Cantos</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="p in mueble.piezas" :key="p.id">
                                                            <td class="text-left font-weight-bold">{{ p.nombre_pieza }}</td>
                                                            <td class="text-left text-muted" style="font-size: 10px;">{{ p.material || mueble.material_interno || mueble.material }}</td>
                                                            <td>{{ p.cantidad }}</td>
                                                            <td>{{ formatNumber(p.largo) }}</td>
                                                            <td>{{ formatNumber(p.ancho) }}</td>
                                                            <td>
                                                                <span class="badge badge-secondary mr-1" :class="{'badge-success': p.canto_l1}">L1</span>
                                                                <span class="badge badge-secondary mr-1" :class="{'badge-success': p.canto_l2}">L2</span>
                                                                <span class="badge badge-secondary mr-1" :class="{'badge-success': p.canto_a1}">A1</span>
                                                                <span class="badge badge-secondary" :class="{'badge-success': p.canto_a2}">A2</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-center py-4 text-muted bg-white border rounded" v-if="arrayMuebles.length === 0">
                            <i class="fa fa-cubes fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0">No se han agregado muebles a este proyecto todavía. ¡Haz clic en <strong>Agregar Mueble</strong> para comenzar!</p>
                        </div>
                    </div>

                    <!-- Cálculo de Tableros Requeridos por Material -->
                    <div class="card mt-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="font-weight-bold text-dark mb-0">
                                <i class="fa fa-th-large text-success"></i> Cálculo de Tableros Requeridos
                            </h5>
                            <small class="text-muted">Ajusta las medidas de tu tablero y desperdicio para estimar la compra.</small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" v-if="resumenMateriales.length > 0">
                                <table class="table table-bordered table-striped text-center mb-0" style="font-size: 13px;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-left" width="30%">Material / Tablero</th>
                                            <th width="15%">Largo Tablero (mm)</th>
                                            <th width="15%">Ancho Tablero (mm)</th>
                                            <th width="12%">Desperdicio (%)</th>
                                            <th width="10%">Área Útil (m²)</th>
                                            <th width="10%">Tableros Est.</th>
                                            <th width="8%">Comprar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="mat in resumenMateriales" :key="mat.material">
                                            <td class="text-left font-weight-bold align-middle">
                                                <i class="fa fa-tag text-info mr-1"></i> {{ mat.material }}
                                                <br><small class="text-muted font-weight-normal">{{ mat.cantPiezas }} piezas calculadas</small>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" v-model.number="materialSettings[mat.material].largo" class="form-control form-control-sm text-center mx-auto" style="max-width: 100px;">
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" v-model.number="materialSettings[mat.material].ancho" class="form-control form-control-sm text-center mx-auto" style="max-width: 100px;">
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" v-model.number="materialSettings[mat.material].desperdicio" class="form-control form-control-sm text-center mx-auto" style="max-width: 80px;">
                                            </td>
                                            <td class="align-middle font-weight-bold">
                                                {{ mat.totalAreaM2.toFixed(2) }} m²
                                            </td>
                                            <td class="align-middle text-muted">
                                                {{ mat.tablerosEstimados.toFixed(2) }}
                                            </td>
                                            <td class="align-middle font-weight-bold text-success h5 mb-0">
                                                {{ mat.tablerosComprar }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center py-4 text-muted" v-else>
                                <i class="fa fa-calculator fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0">Agrega módulos a este proyecto para calcular la cantidad de tableros requeridos.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Plano de Distribución y Corte de Tableros -->
                    <div class="card mt-4 border-0 shadow-sm" v-if="arrayMuebles.length > 0">
                        <div class="card-header bg-white py-3">
                            <h5 class="font-weight-bold text-dark mb-0">
                                <i class="fa fa-map text-warning"></i> Plano de Distribución y Corte de Tableros
                            </h5>
                            <small class="text-muted">Acomodo óptimo de piezas sobre tableros de melamina (Desperdicio y hoja de sierra de 4mm considerados).</small>
                        </div>
                        <div class="card-body">
                            <!-- Selector de Material -->
                            <div class="d-flex mb-3 align-items-center">
                                <span class="mr-2 font-weight-bold" style="font-size: 13px;">Selecciona Material a Visualizar:</span>
                                <div class="btn-group">
                                    <button type="button" 
                                            v-for="res in resumenMateriales" 
                                            :key="res.material"
                                            @click="materialOptimizarActivo = res.material"
                                            class="btn btn-sm btn-outline-primary py-1 px-3"
                                            :class="{'active text-white btn-primary': materialOptimizarActivo === res.material || (!materialOptimizarActivo && resumenMateriales[0] && resumenMateriales[0].material === res.material)}">
                                        {{ res.material }}
                                    </button>
                                </div>
                            </div>

                            <!-- Planos de los tableros del material activo -->
                            <div v-if="getOptimizadoActivo()">
                                <div class="row">
                                    <div class="col-md-12 mb-4" v-for="sheet in getOptimizadoActivo().sheets" :key="sheet.id">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-light p-2 font-weight-bold d-flex justify-content-between align-items-center" style="font-size: 12px; border-bottom: 1px solid #e3e6f0;">
                                                <span><i class="fa fa-th-large text-secondary"></i> Tablero #{{ sheet.id }} ({{ getOptimizadoActivo().sheetW }} x {{ getOptimizadoActivo().sheetH }} mm)</span>
                                                <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">Eficiencia: {{ sheet.efficiency.toFixed(1) }}% Utilizado</span>
                                            </div>
                                            <div class="card-body p-3 text-center bg-light">
                                                <!-- SVG Viewport representativo -->
                                                <div class="d-inline-block border bg-white shadow-sm p-1" style="width: 100%; max-width: 900px; overflow-x: auto;">
                                                    <svg :viewBox="`0 0 ${getOptimizadoActivo().sheetW} ${getOptimizadoActivo().sheetH}`" width="100%" height="auto" style="min-width: 500px; display: block;">
                                                        <!-- Fondo de Tablero en Melamina (Textura símil madera o blanco con borde) -->
                                                        <rect x="0" y="0" :width="getOptimizadoActivo().sheetW" :height="getOptimizadoActivo().sheetH" fill="#fdf2e9" stroke="#d35400" stroke-width="8" />
                                                        
                                                        <!-- Piezas cortadas -->
                                                        <g v-for="(p, pIndex) in sheet.packedPieces" :key="pIndex">
                                                            <!-- Rectángulo de la pieza con un degradado o color moderno azul/madera -->
                                                            <rect :x="p.x" :y="p.y" :width="p.w" :height="p.h" 
                                                                  fill="#3498db" stroke="#2c3e50" stroke-width="2.5" opacity="0.85" rx="3" ry="3" />
                                                            <!-- Nombre de la pieza -->
                                                            <text :x="p.x + p.w/2" :y="p.y + p.h/2 - 10" 
                                                                  text-anchor="middle" font-size="28" font-weight="bold" fill="#ffffff"
                                                                  v-if="p.w > 160 && p.h > 110">
                                                                {{ p.nombre }}
                                                            </text>
                                                            <!-- Medida de la pieza -->
                                                            <text :x="p.x + p.w/2" :y="p.y + p.h/2 + 20" 
                                                                  text-anchor="middle" font-size="22" font-weight="bold" fill="#eaeded"
                                                                  v-if="p.w > 160 && p.h > 110">
                                                                {{ p.w }} x {{ p.h }} mm
                                                            </text>
                                                            <!-- Título Hover (Nativo SVG tooltip) -->
                                                            <title>{{ p.nombre }} ({{ p.w }}x{{ p.h }} mm)&#10;Módulo: {{ p.mueble }}</title>
                                                        </g>
                                                    </svg>
                                                </div>
                                                <div class="mt-2 text-muted" style="font-size: 11px;">
                                                    <span class="mr-3"><i class="fa fa-info-circle text-info"></i> Pasa el cursor por encima de una pieza para ver los detalles y el módulo.</span>
                                                    <span>Total piezas en este tablero: <strong>{{ sheet.packedPieces.length }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISTA 3: CALCULADORA Y EDITOR DE MUEBLE -->
            <div v-if="vista === 'mueble'" class="card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" @click="volverAlProyecto()" class="btn btn-outline-secondary btn-sm mr-2">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </button>
                        <span class="h5 font-weight-bold text-dark mb-0">
                            {{ formMueble.id ? 'Editar' : 'Agregar' }} Módulo de Cocina
                        </span>
                    </div>
                    <button type="button" @click="guardarMueble()" class="btn btn-success btn-sm">
                        <i class="fa fa-save"></i> Guardar en Proyecto
                    </button>
                </div>
                <div class="card-body bg-light">
                    <div class="row">
                        <!-- Columna Izquierda: Parámetros del Formulario -->
                        <div class="col-lg-5 col-md-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-cogs"></i> Parámetros de Diseño (mm)</h6>
                                    
                                    <!-- Tabs Navigation -->
                                    <ul class="nav nav-tabs nav-sm mb-3" style="font-size: 13px;">
                                        <li class="nav-item">
                                            <a class="nav-link font-weight-bold" :class="{'active': activeTab === 'general'}" href="#" @click.prevent="activeTab = 'general'">
                                                <i class="fa fa-info-circle text-primary"></i> General
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link font-weight-bold" :class="{'active': activeTab === 'dimensiones'}" href="#" @click.prevent="activeTab = 'dimensiones'">
                                                <i class="fa fa-arrows text-success"></i> Dimensiones
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link font-weight-bold" :class="{'active': activeTab === 'estructura'}" href="#" @click.prevent="activeTab = 'estructura'">
                                                <i class="fa fa-cogs text-warning"></i> Estructura
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link font-weight-bold" :class="{'active': activeTab === 'divisiones'}" href="#" @click.prevent="activeTab = 'divisiones'">
                                                <i class="fa fa-th-list text-danger"></i> Divisiones
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- TAB 1: GENERAL -->
                                    <div v-show="activeTab === 'general'">
                                        <div class="form-group mb-2">
                                            <label class="form-label text-muted">Nombre del Módulo</label>
                                            <input type="text" v-model="formMueble.nombre" class="form-control form-control-sm" placeholder="Ej: Fregadero Bajo, Alacena Alta">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Tipo de Mueble</label>
                                                <select v-model="formMueble.tipo_mueble" @change="recalcularDespiece()" class="form-control form-control-sm">
                                                    <option value="Bajo">Mueble Bajo (Puertas)</option>
                                                    <option value="Alto">Mueble Alto (Pared)</option>
                                                    <option value="Cajonera">Mueble Cajonera (Bajo)</option>
                                                    <option value="Esquinero L">Esquinero en L (Bajo)</option>
                                                    <option value="Esquinero Recto">Esquinero Recto (Ciego)</option>
                                                    <option value="Columna Nevera">Torre / Columna (Nevera/Horno)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Espesor Material</label>
                                                <select v-model.number="formMueble.espesor_material" @change="recalcularDespiece()" class="form-control form-control-sm">
                                                    <option :value="15">15 mm</option>
                                                    <option :value="18">18 mm</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Material Casco (Interno)</label>
                                                <input type="text" v-model="formMueble.material_interno" @input="recalcularDespiece()" class="form-control form-control-sm" placeholder="Ej: Melamina Blanco 15mm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Material Vistas (Externo)</label>
                                                <input type="text" v-model="formMueble.material_externo" @input="recalcularDespiece()" class="form-control form-control-sm" placeholder="Ej: Melamina Cedro 15mm">
                                            </div>
                                        </div>
                                        <div class="form-group mb-0 mt-2">
                                            <label class="form-label text-muted">Notas Internas</label>
                                            <textarea v-model="formMueble.notas" class="form-control form-control-sm" rows="2" placeholder="Detalles de instalación o de cantos..."></textarea>
                                        </div>
                                    </div>

                                    <!-- TAB 2: DIMENSIONES -->
                                    <div v-show="activeTab === 'dimensiones'">
                                        <div class="row" v-if="formMueble.tipo_mueble === 'Esquinero L'">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Ancho Izquierdo (W1)</label>
                                                <input type="number" v-model.number="formMueble.ancho" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Ancho Derecho (W2)</label>
                                                <input type="number" v-model.number="formMueble.ancho_derecho" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Alto (H)</label>
                                                <input type="number" v-model.number="formMueble.alto" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Profundidad (D)</label>
                                                <input type="number" v-model.number="formMueble.profundidad" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                        </div>
                                        <div class="row" v-else-if="formMueble.tipo_mueble === 'Columna Nevera'">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Ancho Total (W)</label>
                                                <input type="number" v-model.number="formMueble.ancho" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Alto Total (H)</label>
                                                <input type="number" v-model.number="formMueble.alto" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Profundidad (D)</label>
                                                <input type="number" v-model.number="formMueble.profundidad" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Alto Hueco (Nevera)</label>
                                                <input type="number" v-model.number="formMueble.hueco_alto" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                        </div>
                                        <div class="row" v-else-if="formMueble.tipo_mueble === 'Esquinero Recto'">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Ancho Total (W)</label>
                                                <input type="number" v-model.number="formMueble.ancho" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Alto (H)</label>
                                                <input type="number" v-model.number="formMueble.alto" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Profundidad (D)</label>
                                                <input type="number" v-model.number="formMueble.profundidad" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Espacio Ciego (Oculto)</label>
                                                <input type="number" v-model.number="formMueble.espacio_ciego" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold" placeholder="Ej: 600">
                                            </div>
                                        </div>
                                        <div class="row" v-else>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Ancho (W)</label>
                                                <input type="number" v-model.number="formMueble.ancho" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Alto (H)</label>
                                                <input type="number" v-model.number="formMueble.alto" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Profundidad (D)</label>
                                                <input type="number" v-model.number="formMueble.profundidad" @input="recalcularDespiece()" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TAB 3: ESTRUCTURA -->
                                    <div v-show="activeTab === 'estructura'">
                                        <div class="row" v-if="formMueble.tipo_mueble !== 'Alto'">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Tipo de Mesón / Tapa</label>
                                                <select v-model="formMueble.tipo_meson" @change="recalcularDespiece()" class="form-control form-control-sm">
                                                    <option value="Sin Tapa">Sin Tapa (Piedra/Mármol)</option>
                                                    <option value="Mesón Melamina">Mesón de Melamina</option>
                                                    <option value="Cerrado">Tapa Superior Cerrada</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2" v-if="formMueble.tipo_meson === 'Sin Tapa'">
                                                <label class="form-label text-muted">Alto del Mesón (M)</label>
                                                <input type="number" v-model.number="formMueble.alto_meson" @input="recalcularDespiece()" class="form-control form-control-sm" placeholder="Ej: 20">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2" v-if="formMueble.tipo_mueble !== 'Alto'">
                                                <label class="form-label text-muted">Altura de Zócalo (Z)</label>
                                                <input type="number" v-model.number="formMueble.zocalo" @input="recalcularDespiece()" class="form-control form-control-sm" placeholder="100">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Costados Vistos</label>
                                                <select v-model="formMueble.costados_vistos" @change="recalcularDespiece()" class="form-control form-control-sm">
                                                    <option value="Ambos">Ambos Costados Vistos</option>
                                                    <option value="Izquierdo">Solo Costado Izquierdo</option>
                                                    <option value="Derecho">Solo Costado Derecho</option>
                                                    <option value="Ninguno">Ninguno (Interno)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Fondo (Espalda)</label>
                                                <div class="custom-control custom-switch mt-1">
                                                    <input type="checkbox" class="custom-control-input" id="switchFondo" v-model="formMueble.tiene_fondo" @change="recalcularDespiece()">
                                                    <label class="custom-control-label" for="switchFondo">Incluir Fondo</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2" v-if="formMueble.tiene_fondo">
                                                <label class="form-label text-muted">Material del Fondo</label>
                                                <input type="text" v-model="formMueble.material_fondo" @input="recalcularDespiece()" class="form-control form-control-sm" placeholder="Ej. MDF Blanco 3mm">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TAB 4: DIVISIONES -->
                                    <div v-show="activeTab === 'divisiones'">
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Sistema de Apertura</label>
                                                <select v-model="formMueble.sistema_apertura" @change="recalcularDespiece()" class="form-control form-control-sm">
                                                    <option value="Manija">Manija / Tirador tradicional</option>
                                                    <option value="Push">Push to Open (Presionar)</option>
                                                    <option value="Gola">Perfil Gola (Sin manija)</option>
                                                    <option value="Bisel">Uñero Biselado (45°)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Tipo de Tirador</label>
                                                <select v-model="formMueble.tipo_tirador" :disabled="formMueble.sistema_apertura !== 'Manija'" class="form-control form-control-sm">
                                                    <option value="Estándar">Estándar / De Sobreponer</option>
                                                    <option value="Embutido">Embutido / Perfil C / J</option>
                                                    <option value="Sin Tirador" v-if="formMueble.sistema_apertura !== 'Manija'">N/A</option>
                                                </select>
                                            </div>
                                        </div>
                                        <h6 class="font-weight-bold text-secondary border-bottom pb-2 mt-3">Configuración de Divisiones</h6>
                                        <div v-for="(div, index) in formMueble.divisiones" :key="index" class="d-flex align-items-end mb-2 p-2 border rounded bg-white">
                                            <div class="mr-2 flex-grow-1">
                                                <label class="form-label text-muted mb-0" style="font-size: 11px;">Contenido</label>
                                                <select v-model="div.tipo" @change="recalcularDespiece()" class="form-control form-control-sm">
                                                    <option value="Cajones">Cajones</option>
                                                    <option value="Puertas">Puertas</option>
                                                    <option value="Abierto">Espacio Abierto</option>
                                                </select>
                                            </div>
                                            <div class="mr-2" style="width: 100px;">
                                                <label class="form-label text-muted mb-0" style="font-size: 11px;">Cantidad</label>
                                                <input type="number" v-model.number="div.cantidad" @input="recalcularDespiece()" class="form-control form-control-sm" min="1">
                                            </div>
                                            <div>
                                                <button type="button" @click="eliminarDivision(index)" class="btn btn-danger btn-sm" title="Eliminar División">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" @click="agregarDivision()" class="btn btn-outline-primary btn-sm mt-1 w-100">
                                            <i class="fa fa-plus"></i> Agregar División Vertical
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráfico de Previsualización (SVG) -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="font-weight-bold text-primary mb-0 text-left"><i class="fa fa-eye"></i> Plano del Mueble</h6>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" @click="tipo_vista_grafico = 'Frontal'" class="btn btn-outline-primary py-0 px-2" :class="{'active text-white': tipo_vista_grafico === 'Frontal'}" style="font-size: 11px;">Frontal</button>
                                            <button type="button" @click="tipo_vista_grafico = 'Lateral'" class="btn btn-outline-primary py-0 px-2" :class="{'active text-white': tipo_vista_grafico === 'Lateral'}" style="font-size: 11px;">Lateral</button>
                                            <button type="button" @click="tipo_vista_grafico = 'Perspectiva'" class="btn btn-outline-primary py-0 px-2" :class="{'active text-white': tipo_vista_grafico === 'Perspectiva'}" style="font-size: 11px;">Perspectiva 3D</button>
                                        </div>
                                    </div>
                                    <div class="bg-white p-2 border rounded d-flex justify-content-center align-items-center" style="height: 240px;">
                                        <svg width="100%" height="100%" viewBox="0 0 450 320" style="background-color: #fcfcfc;">
                                            <defs>
                                                <!-- Gradients to make the 3D cabinet look premium -->
                                                <linearGradient id="meson-stone" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#34495e" />
                                                    <stop offset="100%" stop-color="#2c3e50" />
                                                </linearGradient>
                                                <linearGradient id="meson-wood" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#b97a57" />
                                                    <stop offset="100%" stop-color="#8a5229" />
                                                </linearGradient>
                                                <linearGradient id="door-front" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#34495e" />
                                                    <stop offset="100%" stop-color="#2c3e50" />
                                                </linearGradient>
                                                <linearGradient id="drawer-front" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#e74c3c" />
                                                    <stop offset="100%" stop-color="#c0392b" />
                                                </linearGradient>
                                                <linearGradient id="carcass-side" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#f8f9f9" />
                                                    <stop offset="100%" stop-color="#ebf5fb" />
                                                </linearGradient>
                                            </defs>

                                            <!-- Sombra de Base (Floor shadow) -->
                                            <polygon :points="`${pt(-10, 0, -10)} ${pt(formMueble.ancho + 10, 0, -10)} ${pt(formMueble.ancho + 10, 0, formMueble.profundidad + 10)} ${pt(-10, 0, formMueble.profundidad + 10)}`" 
                                                     fill="rgba(0, 0, 0, 0.05)" />

                                            <template v-if="formMueble.tipo_mueble === 'Esquinero L'">
                                                <!-- L-shape approximation -->
                                                <polygon :points="`${pt(0, formMueble.zocalo, 0)} ${pt(0, formMueble.zocalo, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, 0)}`" fill="url(#meson-wood)" stroke="#95a5a6" />
                                                <polygon :points="`${pt(formMueble.ancho, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.zocalo, formMueble.profundidad)} ${pt(formMueble.ancho, formMueble.alto, formMueble.profundidad)} ${pt(formMueble.ancho, formMueble.alto, 0)}`" fill="url(#meson-wood)" stroke="#95a5a6" />
                                                
                                                <!-- Piso L -->
                                                <polygon :points="`${pt(0, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.zocalo, formMueble.profundidad)} ${pt(0, formMueble.zocalo, formMueble.profundidad)}`" fill="#eaeded" stroke="#bdc3c7" />

                                                <text :x="pt(formMueble.ancho/2, formMueble.alto/2, formMueble.profundidad/2).split(',')[0]" :y="pt(formMueble.ancho/2, formMueble.alto/2, formMueble.profundidad/2).split(',')[1]" text-anchor="middle" font-size="14" font-weight="bold" fill="#34495e">Vista Simplificada L</text>
                                            </template>

                                            <template v-else-if="formMueble.tipo_mueble === 'Esquinero Recto'">
                                                <polygon :points="`${pt(0, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(0, formMueble.alto, 0)}`" fill="#eaeded" stroke="#bdc3c7" />
                                                <!-- Panel Ciego -->
                                                <polygon :points="`${pt(formMueble.ancho - (formMueble.espacio_ciego || 600), formMueble.zocalo, -2)} ${pt(formMueble.ancho, formMueble.zocalo, -2)} ${pt(formMueble.ancho, formMueble.alto, -2)} ${pt(formMueble.ancho - (formMueble.espacio_ciego || 600), formMueble.alto, -2)}`" fill="url(#carcass-side)" stroke="#bdc3c7" />
                                                <text :x="pt(formMueble.ancho - (formMueble.espacio_ciego || 600)/2, formMueble.alto/2, -2).split(',')[0]" :y="pt(formMueble.ancho - (formMueble.espacio_ciego || 600)/2, formMueble.alto/2, -2).split(',')[1]" text-anchor="middle" font-size="10" font-weight="bold" fill="#7f8c8d">Ciego</text>
                                                <!-- Puerta -->
                                                <polygon :points="`${pt(0, formMueble.zocalo, -2)} ${pt(formMueble.ancho - (formMueble.espacio_ciego || 600), formMueble.zocalo, -2)} ${pt(formMueble.ancho - (formMueble.espacio_ciego || 600), formMueble.alto, -2)} ${pt(0, formMueble.alto, -2)}`" fill="url(#door-front)" stroke="#1b2631" />
                                            </template>

                                            <template v-else-if="formMueble.tipo_mueble === 'Columna Nevera'">
                                                <!-- Hueco Central -->
                                                <polygon :points="`${pt(15, formMueble.zocalo, 0)} ${pt(formMueble.ancho-15, formMueble.zocalo, 0)} ${pt(formMueble.ancho-15, formMueble.zocalo + (formMueble.hueco_alto || 1800), 0)} ${pt(15, formMueble.zocalo + (formMueble.hueco_alto || 1800), 0)}`" fill="#e5e8e8" />
                                                <text :x="pt(formMueble.ancho/2, formMueble.zocalo + (formMueble.hueco_alto || 1800)/2, 0).split(',')[0]" :y="pt(formMueble.ancho/2, formMueble.zocalo + (formMueble.hueco_alto || 1800)/2, 0).split(',')[1]" text-anchor="middle" font-size="12" font-weight="bold" fill="#7f8c8d">Hueco Libre</text>
                                                <!-- Puerta Superior -->
                                                <polygon :points="`${pt(15, formMueble.zocalo + (formMueble.hueco_alto || 1800), -2)} ${pt(formMueble.ancho-15, formMueble.zocalo + (formMueble.hueco_alto || 1800), -2)} ${pt(formMueble.ancho-15, formMueble.alto, -2)} ${pt(15, formMueble.alto, -2)}`" fill="url(#door-front)" stroke="#1b2631" />
                                                <polygon :points="`${pt(0, formMueble.zocalo, 0)} ${pt(15, formMueble.zocalo, 0)} ${pt(15, formMueble.alto, 0)} ${pt(0, formMueble.alto, 0)}`" fill="url(#meson-wood)" stroke="#95a5a6" />
                                                <polygon :points="`${pt(formMueble.ancho-15, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(formMueble.ancho-15, formMueble.alto, 0)}`" fill="url(#meson-wood)" stroke="#95a5a6" />
                                                <polygon :points="`${pt(0, formMueble.alto, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(formMueble.ancho, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)}`" fill="url(#meson-wood)" stroke="#95a5a6" />
                                            </template>

                                            <template v-else>
                                                <!-- 1. ZÓCALO (Si no es mueble alto) -->
                                            <template v-if="formMueble.tipo_mueble !== 'Alto'">
                                                <!-- Zócalo Lateral Izquierdo -->
                                                <polygon :points="`${pt(30, 0, 50)} ${pt(30, 0, formMueble.profundidad - 50)} ${pt(30, formMueble.zocalo, formMueble.profundidad - 50)} ${pt(30, formMueble.zocalo, 50)}`" 
                                                         fill="#566573" stroke="#2c3e50" stroke-width="0.5" />
                                                <!-- Zócalo Frontal -->
                                                <polygon :points="`${pt(30, 0, 50)} ${pt(formMueble.ancho - 30, 0, 50)} ${pt(formMueble.ancho - 30, formMueble.zocalo, 50)} ${pt(30, formMueble.zocalo, 50)}`" 
                                                         fill="#7f8c8d" stroke="#34495e" stroke-width="0.5" />
                                            </template>

                                            <!-- 2. FONDO TRASERO (Carcass Back Panel) -->
                                            <polygon :points="`${pt(0, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, formMueble.profundidad)} ${pt(formMueble.ancho, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, formMueble.profundidad)} ${pt(formMueble.ancho, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)}`" 
                                                     fill="#d5dbdb" stroke="#bdc3c7" stroke-width="0.5" />

                                            <!-- 3. INTERIOR: PISO (Cabinet floor) -->
                                            <polygon :points="`${pt(formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo + formMueble.espesor_material, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo + formMueble.espesor_material, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo + formMueble.espesor_material, formMueble.profundidad)} ${pt(formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo + formMueble.espesor_material, formMueble.profundidad)}`" 
                                                     fill="#eaeded" stroke="#bdc3c7" stroke-width="0.5" />
                                            <polygon :points="`${pt(formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo + formMueble.espesor_material, 0)} ${pt(formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo + formMueble.espesor_material, 0)}`" 
                                                     fill="#bdc3c7" stroke="#95a5a6" stroke-width="0.5" />

                                            <!-- 4. ESTANTES INTERNOS (Translucent Blue Glass/MDF) -->
                                            <template v-if="formMueble.estantes > 0">
                                                <g v-for="(yHeight, idx) in get3DShelves()" :key="'est3d-'+idx">
                                                    <!-- Top Face of Shelf -->
                                                    <polygon :points="`${pt(formMueble.espesor_material, yHeight, 20)} ${pt(formMueble.ancho - formMueble.espesor_material, yHeight, 20)} ${pt(formMueble.ancho - formMueble.espesor_material, yHeight, formMueble.profundidad - 10)} ${pt(formMueble.espesor_material, yHeight, formMueble.profundidad - 10)}`" 
                                                             fill="rgba(52, 152, 219, 0.15)" stroke="#3498db" stroke-width="0.8" />
                                                    <!-- Front Edge of Shelf -->
                                                    <polygon :points="`${pt(formMueble.espesor_material, yHeight - 15, 20)} ${pt(formMueble.ancho - formMueble.espesor_material, yHeight - 15, 20)} ${pt(formMueble.ancho - formMueble.espesor_material, yHeight, 20)} ${pt(formMueble.espesor_material, yHeight, 20)}`" 
                                                             fill="rgba(52, 152, 219, 0.3)" stroke="#2980b9" stroke-width="0.8" />
                                                </g>
                                            </template>

                                            <!-- 5. COSTADO LATERAL IZQUIERDO (3D solid block) -->
                                            <!-- Cara Exterior Izquierda (Visible o no según costados_vistos) -->
                                            <polygon :points="`${pt(0, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(0, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, 0)}`" 
                                                     :fill="(formMueble.costados_vistos === 'Ambos' || formMueble.costados_vistos === 'Izquierdo') ? 'url(#meson-wood)' : '#eaecee'" 
                                                     stroke="#95a5a6" stroke-width="0.5" />
                                            <!-- Canto Delantero -->
                                            <polygon :points="`${pt(0, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(formMueble.espesor_material, formMueble.alto, 0)} ${pt(0, formMueble.alto, 0)}`" 
                                                     fill="#bdc3c7" stroke="#7f8c8d" stroke-width="0.5" />
                                            <!-- Canto Superior (Si no lleva mesón cerrado/melamina) -->
                                            <polygon v-if="formMueble.tipo_meson !== 'Cerrado'" :points="`${pt(0, formMueble.alto, 0)} ${pt(formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.espesor_material, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)}`" 
                                                     fill="#bdc3c7" stroke="#7f8c8d" stroke-width="0.5" />

                                            <!-- 6. COSTADO LATERAL DERECHO (Solo canto y cara superior visibles) -->
                                            <!-- Canto Delantero -->
                                            <polygon :points="`${pt(formMueble.ancho - formMueble.espesor_material, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.tipo_mueble === 'Alto' ? 0 : formMueble.zocalo, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)}`" 
                                                     fill="#bdc3c7" stroke="#7f8c8d" stroke-width="0.5" />
                                            <!-- Canto Superior -->
                                            <polygon v-if="formMueble.tipo_meson !== 'Cerrado'" :points="`${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(formMueble.ancho, formMueble.alto, formMueble.profundidad)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, formMueble.profundidad)}`" 
                                                     fill="#bdc3c7" stroke="#7f8c8d" stroke-width="0.5" />

                                            <!-- 7. TAPA SUPERIOR / MESÓN / AMARRES -->
                                            <template v-if="formMueble.tipo_meson === 'Mesón Melamina'">
                                                <!-- Cara Superior del Mesón -->
                                                <polygon :points="`${pt(0, formMueble.alto, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(formMueble.ancho, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)}`" 
                                                         fill="url(#meson-wood)" stroke="#8a5229" stroke-width="0.8" />
                                                <!-- Frente del Mesón -->
                                                <polygon :points="`${pt(0, formMueble.alto - formMueble.espesor_material, 0)} ${pt(formMueble.ancho, formMueble.alto - formMueble.espesor_material, 0)} ${pt(formMueble.ancho, formMueble.alto, 0)} ${pt(0, formMueble.alto, 0)}`" 
                                                         fill="url(#meson-wood)" stroke="#8a5229" stroke-width="0.8" />
                                                <!-- Lateral Izquierdo del Mesón -->
                                                <polygon :points="`${pt(0, formMueble.alto - formMueble.espesor_material, 0)} ${pt(0, formMueble.alto - formMueble.espesor_material, formMueble.profundidad)} ${pt(0, formMueble.alto, formMueble.profundidad)} ${pt(0, formMueble.alto, 0)}`" 
                                                         fill="#8a5229" stroke="#683d1c" stroke-width="0.8" />
                                            </template>
                                            <template v-else-if="formMueble.tipo_meson === 'Cerrado'">
                                                <!-- Tapa Estructura Cerrada (Interna) -->
                                                <polygon :points="`${pt(formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, formMueble.profundidad)} ${pt(formMueble.espesor_material, formMueble.alto, formMueble.profundidad)}`" 
                                                         fill="#eaeded" stroke="#bdc3c7" stroke-width="0.5" />
                                                <!-- Canto Frontal Tapa -->
                                                <polygon :points="`${pt(formMueble.espesor_material, formMueble.alto - formMueble.espesor_material, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto - formMueble.espesor_material, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.espesor_material, formMueble.alto, 0)}`" 
                                                         fill="#bdc3c7" stroke="#95a5a6" stroke-width="0.5" />
                                            </template>
                                            <template v-else>
                                                <!-- Amarres Superiores (Sin tapa, va mesón de piedra) -->
                                                <!-- Amarre Frontal -->
                                                <polygon :points="`${pt(formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 80)} ${pt(formMueble.espesor_material, formMueble.alto, 80)}`" 
                                                         fill="#f2f4f4" stroke="#bdc3c7" stroke-width="0.5" />
                                                <polygon :points="`${pt(formMueble.espesor_material, formMueble.alto - formMueble.espesor_material, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto - formMueble.espesor_material, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.espesor_material, formMueble.alto, 0)}`" 
                                                         fill="#bdc3c7" stroke="#95a5a6" stroke-width="0.5" />
                                                <!-- Amarre Trasero -->
                                                <polygon :points="`${pt(formMueble.espesor_material, formMueble.alto, formMueble.profundidad - 80)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, formMueble.profundidad - 80)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, formMueble.profundidad)} ${pt(formMueble.espesor_material, formMueble.alto, formMueble.profundidad)}`" 
                                                         fill="#f2f4f4" stroke="#bdc3c7" stroke-width="0.5" />
                                            </template>

                                            <!-- 8. CANAL GOLA (Ranura de aluminio) -->
                                            <polygon v-if="formMueble.sistema_apertura === 'Gola' && formMueble.tipo_mueble !== 'Alto'" 
                                                     :points="`${pt(formMueble.espesor_material, formMueble.alto - 35, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto - 35, 0)} ${pt(formMueble.ancho - formMueble.espesor_material, formMueble.alto, 0)} ${pt(formMueble.espesor_material, formMueble.alto, 0)}`" 
                                                     fill="#7f8c8d" stroke="#2c3e50" opacity="0.9" stroke-width="0.5" />

                                                    <!-- Bisel Indicator -->
                                                    <line v-if="formMueble.sistema_apertura === 'Bisel'" 
                                                          :x1="pt(2, draw.yTop - 1, -2).split(',')[0]" 
                                                          :y1="pt(2, draw.yTop - 1, -2).split(',')[1]" 
                                                          :x2="pt(formMueble.ancho - 2, draw.yTop - 1, -2).split(',')[0]" 
                                                          :y2="pt(formMueble.ancho - 2, draw.yTop - 1, -2).split(',')[1]" 
                                                          stroke="#e67e22" stroke-width="1.5" stroke-dasharray="2,2" />

                                                    <!-- Push Indicator -->
                                                    <g v-if="formMueble.sistema_apertura === 'Push'">
                                                        <circle :cx="pt(formMueble.ancho/2, draw.yBottom + (draw.yTop - draw.yBottom)/2, -2).split(',')[0]" 
                                                                :cy="pt(formMueble.ancho/2, draw.yBottom + (draw.yTop - draw.yBottom)/2, -2).split(',')[1]" 
                                                                r="4" fill="#e74c3c" stroke="#c0392b" stroke-width="0.8" />
                                                        <circle :cx="pt(formMueble.ancho/2, draw.yBottom + (draw.yTop - draw.yBottom)/2, -2).split(',')[0]" 
                                                                :cy="pt(formMueble.ancho/2, draw.yBottom + (draw.yTop - draw.yBottom)/2, -2).split(',')[1]" 
                                                                r="1.5" fill="#ffffff" />
                                                    </g>
                                                </g>
                                            </template>


                                            <!-- 11. COTAS TÉCNICAS 3D (Planos de Taller) -->
                                            <!-- Cota Ancho (Superior) -->
                                            <g>
                                                <line :x1="pt(0, formMueble.alto + 25, 0).split(',')[0]" 
                                                      :y1="pt(0, formMueble.alto + 25, 0).split(',')[1]" 
                                                      :x2="pt(formMueble.ancho, formMueble.alto + 25, 0).split(',')[0]" 
                                                      :y2="pt(formMueble.ancho, formMueble.alto + 25, 0).split(',')[1]" 
                                                      stroke="#5d6d7e" stroke-width="1" />
                                                <polygon :points="`${pt(0, formMueble.alto + 25, 0)} ${pt(12, formMueble.alto + 27, 0)} ${pt(12, formMueble.alto + 23, 0)}`" fill="#5d6d7e" />
                                                <polygon :points="`${pt(formMueble.ancho, formMueble.alto + 25, 0)} ${pt(formMueble.ancho - 12, formMueble.alto + 27, 0)} ${pt(formMueble.ancho - 12, formMueble.alto + 23, 0)}`" fill="#5d6d7e" />
                                                <text :x="pt(formMueble.ancho/2, formMueble.alto + 38, 0).split(',')[0]" 
                                                      :y="pt(formMueble.ancho/2, formMueble.alto + 38, 0).split(',')[1]" 
                                                      text-anchor="middle" font-size="9.5" font-weight="bold" fill="#2c3e50">{{ formMueble.ancho }} mm</text>
                                            </g>

                                            <!-- Cota Alto (Lateral Derecho) -->
                                            <g>
                                                <line :x1="pt(formMueble.ancho + 25, 0, 0).split(',')[0]" 
                                                      :y1="pt(formMueble.ancho + 25, 0, 0).split(',')[1]" 
                                                      :x2="pt(formMueble.ancho + 25, formMueble.alto, 0).split(',')[0]" 
                                                      :y2="pt(formMueble.ancho + 25, formMueble.alto, 0).split(',')[1]" 
                                                      stroke="#5d6d7e" stroke-width="1" />
                                                <polygon :points="`${pt(formMueble.ancho + 25, 0, 0)} ${pt(formMueble.ancho + 27, 12, 0)} ${pt(formMueble.ancho + 23, 12, 0)}`" fill="#5d6d7e" />
                                                <polygon :points="`${pt(formMueble.ancho + 25, formMueble.alto, 0)} ${pt(formMueble.ancho + 27, formMueble.alto - 12, 0)} ${pt(formMueble.ancho + 23, formMueble.alto - 12, 0)}`" fill="#5d6d7e" />
                                                <text :x="pt(formMueble.ancho + 35, formMueble.alto/2, 0).split(',')[0]" 
                                                      :y="pt(formMueble.ancho + 35, formMueble.alto/2, 0).split(',')[1]" 
                                                      text-anchor="start" font-size="9.5" font-weight="bold" fill="#2c3e50">{{ formMueble.alto }} mm</text>
                                            </g>

                                            <!-- Cota Profundidad (Lateral de Planta) -->
                                            <g>
                                                <line :x1="pt(formMueble.ancho + 25, 0, 0).split(',')[0]" 
                                                      :y1="pt(formMueble.ancho + 25, 0, 0).split(',')[1]" 
                                                      :x2="pt(formMueble.ancho + 25, 0, formMueble.profundidad).split(',')[0]" 
                                                      :y2="pt(formMueble.ancho + 25, 0, formMueble.profundidad).split(',')[1]" 
                                                      stroke="#5d6d7e" stroke-width="1" />
                                                <polygon :points="`${pt(formMueble.ancho + 25, 0, 0)} ${pt(formMueble.ancho + 23, 0, 10)} ${pt(formMueble.ancho + 27, 0, 10)}`" fill="#5d6d7e" />
                                                <polygon :points="`${pt(formMueble.ancho + 25, 0, formMueble.profundidad)} ${pt(formMueble.ancho + 23, 0, formMueble.profundidad - 10)} ${pt(formMueble.ancho + 27, 0, formMueble.profundidad - 10)}`" fill="#5d6d7e" />
                                                <text :x="pt(formMueble.ancho + 35, 0, formMueble.profundidad/2).split(',')[0]" 
                                                      :y="pt(formMueble.ancho + 35, 0, formMueble.profundidad/2).split(',')[1]" 
                                                      text-anchor="start" font-size="9.5" font-weight="bold" fill="#2c3e50">{{ formMueble.profundidad }} mm</text>
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="mt-2 text-center" style="font-size: 10px;">
                                        <span class="mr-2"><i class="fa fa-square" style="color: #3498db;"></i> Estante</span>
                                        <span class="mr-2"><i class="fa fa-square" style="color: #e67e22;"></i> Puerta</span>
                                        <span><i class="fa fa-square" style="color: #e74c3c;"></i> Frente Cajón</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Estimación de Materiales para este Mueble -->
                            <div class="card border-0 shadow-sm mt-3">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-dark mb-2 text-left">
                                        <i class="fa fa-pie-chart text-warning"></i> Tableros requeridos para este mueble
                                    </h6>
                                    <div class="row text-center my-2">
                                        <div class="col-6 border-right">
                                            <small class="text-muted d-block" style="font-size: 11px;">Área de Melamina</small>
                                            <span class="h5 font-weight-bold text-dark">{{ areaMelaminaMueble.toFixed(2) }} m²</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 11px;">Metros de Canto</small>
                                            <span class="h5 font-weight-bold text-dark">{{ metrosCantoMueble.toFixed(1) }} m</span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="p-2 rounded bg-light" style="font-size: 11px;">
                                        <span class="font-weight-bold d-block mb-1 text-muted text-left">Ajusta el tamaño del Tablero de Melamina:</span>
                                        <div class="row no-gutters text-left">
                                            <div class="col-4 pr-1">
                                                <label class="mb-0 text-muted" style="font-size: 9px;">Largo Tablero (mm)</label>
                                                <input type="number" v-model.number="tableroEstLargo" class="form-control form-control-xs py-0 text-center" style="height: 22px; font-size: 10px;">
                                            </div>
                                            <div class="col-4 pr-1">
                                                <label class="mb-0 text-muted" style="font-size: 9px;">Ancho Tablero (mm)</label>
                                                <input type="number" v-model.number="tableroEstAncho" class="form-control form-control-xs py-0 text-center" style="height: 22px; font-size: 10px;">
                                            </div>
                                            <div class="col-4">
                                                <label class="mb-0 text-muted" style="font-size: 9px;">Desperdicio (%)</label>
                                                <input type="number" v-model.number="tableroEstDesperdicio" class="form-control form-control-xs py-0 text-center" style="height: 22px; font-size: 10px;">
                                            </div>
                                        </div>
                                        <div class="mt-2 text-center border-top pt-2">
                                            <span class="font-weight-bold">Estimación: </span>
                                            <span class="text-success font-weight-bold" style="font-size: 13px;">
                                                {{ Math.ceil((areaMelaminaMueble * (1 + (tableroEstDesperdicio/100))) / (((tableroEstLargo || 2440) * (tableroEstAncho || 1220))/1000000)) }} tablero(s)
                                            </span>
                                            <span class="text-muted">
                                                ({{ ((areaMelaminaMueble * (1 + (tableroEstDesperdicio/100))) / (((tableroEstLargo || 2440) * (tableroEstAncho || 1220))/1000000)).toFixed(2) }} del área)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Planos de Corte para este Mueble -->
                            <div class="card border-0 shadow-sm mt-3" v-if="formMueble.piezas && formMueble.piezas.length > 0">
                                <div class="card-body p-3">
                                    <h6 class="font-weight-bold text-dark mb-2 text-left">
                                        <i class="fa fa-map text-warning"></i> Plano de Corte de este Módulo
                                    </h6>
                                    <div v-for="(opt, mat) in tablerosOptimizadosMueble" :key="mat" class="mb-3 text-left">
                                        <span class="badge badge-info mb-2" style="font-size: 11px;">{{ mat }}</span>
                                        <div v-for="sheet in opt.sheets" :key="sheet.id" class="border rounded bg-white p-2 text-center mb-2">
                                            <small class="d-block text-muted font-weight-bold mb-1" style="font-size: 11px;">Tablero #{{ sheet.id }} - Eficiencia: {{ sheet.efficiency.toFixed(1) }}%</small>
                                            <div class="d-inline-block border bg-white p-1" style="width: 100%; max-width: 100%; overflow-x: auto;">
                                                <svg :viewBox="`0 0 ${opt.sheetW} ${opt.sheetH}`" width="100%" height="auto" style="min-width: 300px; display: block;">
                                                    <rect x="0" y="0" :width="opt.sheetW" :height="opt.sheetH" fill="#fdf2e9" stroke="#d35400" stroke-width="8" />
                                                    <g v-for="(p, pIndex) in sheet.packedPieces" :key="pIndex">
                                                        <rect :x="p.x" :y="p.y" :width="p.w" :height="p.h" 
                                                              fill="#3498db" stroke="#2c3e50" stroke-width="2.5" opacity="0.85" rx="3" ry="3" />
                                                        <text :x="p.x + p.w/2" :y="p.y + p.h/2 - 10" 
                                                              text-anchor="middle" font-size="28" font-weight="bold" fill="#ffffff"
                                                              v-if="p.w > 160 && p.h > 110">
                                                            {{ p.nombre }}
                                                        </text>
                                                        <text :x="p.x + p.w/2" :y="p.y + p.h/2 + 20" 
                                                              text-anchor="middle" font-size="22" font-weight="bold" fill="#eaeded"
                                                              v-if="p.w > 160 && p.h > 110">
                                                            {{ p.w }} x {{ p.h }} mm
                                                        </text>
                                                        <title>{{ p.nombre }} ({{ p.w }}x{{ p.h }} mm)</title>
                                                    </g>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Detalle de la Lista de Cortes -->
                        <div class="col-lg-7 col-md-12">
                            <div class="card border-0 shadow-sm rounded h-100">
                                <div class="card-header bg-white font-weight-bold py-3 text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-list text-success"></i> Lista de Cortes Calculada</span>
                                    <button type="button" @click="agregarFilaManual()" class="btn btn-outline-primary btn-xs">
                                        <i class="fa fa-plus"></i> Añadir Pieza Libre
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center" style="font-size: 11px;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Pieza</th>
                                                    <th width="18%">Material / Tablero</th>
                                                    <th width="8%">Cant.</th>
                                                    <th width="12%">Largo (mm)</th>
                                                    <th width="12%">Ancho (mm)</th>
                                                    <th width="5%">L1</th>
                                                    <th width="5%">L2</th>
                                                    <th width="5%">A1</th>
                                                    <th width="5%">A2</th>
                                                    <th width="5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(p, index) in formMueble.piezas" :key="index">
                                                    <td>
                                                        <input type="text" v-model="p.nombre_pieza" class="form-control form-control-sm text-left border-0 bg-transparent py-0">
                                                    </td>
                                                    <td>
                                                        <input type="text" v-model="p.material" class="form-control form-control-sm text-left border-0 bg-transparent py-0 font-weight-normal text-muted" style="font-size: 10px;">
                                                    </td>
                                                    <td>
                                                        <input type="number" v-model.number="p.cantidad" class="form-control form-control-sm text-center border-0 bg-transparent p-0 font-weight-bold">
                                                    </td>
                                                    <td>
                                                        <input type="number" v-model.number="p.largo" class="form-control form-control-sm text-center border-0 bg-transparent p-0">
                                                    </td>
                                                    <td>
                                                        <input type="number" v-model.number="p.ancho" class="form-control form-control-sm text-center border-0 bg-transparent p-0">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" v-model="p.canto_l1">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" v-model="p.canto_l2">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" v-model="p.canto_a1">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" v-model="p.canto_a2">
                                                    </td>
                                                    <td>
                                                        <button type="button" @click="eliminarFilaManual(index)" class="btn btn-outline-danger btn-xs border-0 py-0" title="Remover pieza">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4 p-3 bg-light border rounded">
                                        <h6 class="font-weight-bold text-dark mb-2">Consumo Estimado Módulo:</h6>
                                        <div class="row text-center">
                                            <div class="col-md-6 border-right">
                                                <span class="text-muted d-block font-weight-normal">Melamina requerida:</span>
                                                <span class="h4 font-weight-bold text-success">{{ areaMelaminaMueble.toFixed(2) }} m²</span>
                                            </div>
                                            <div class="col-md-6">
                                                <span class="text-muted d-block font-weight-normal">Metros de Canto:</span>
                                                <span class="h4 font-weight-bold text-success">{{ metrosCantoMueble.toFixed(1) }} m</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CREAR / EDITAR PROYECTO -->
        <div class="modal fade" :class="{'show d-block': modalProyecto}" tabindex="-1" role="dialog" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">{{ formProyecto.id ? 'Editar' : 'Nuevo' }} Proyecto</h5>
                        <button type="button" class="close" @click="cerrarModalProyecto()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">Cliente / Nombre del Proyecto</label>
                            <input type="text" v-model="formProyecto.cliente" class="form-control" placeholder="Ej: Juan Pérez - Cocina Integral">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">Descripción</label>
                            <textarea v-model="formProyecto.descripcion" class="form-control" rows="3" placeholder="Detalles de la instalación, materiales principales..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">Fecha</label>
                            <input type="date" v-model="formProyecto.fecha" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalProyecto()">Cerrar</button>
                        <button type="button" class="btn btn-primary btn-sm" @click="guardarProyecto()">Confirmar</button>
                    </div>
                </div>
            </div>
            
            <!-- VISTA 4: OPTIMIZADOR DE CORTES -->
            <div v-if="vista === 'optimizador'">
                <div class="mb-3">
                    <button type="button" @click="volverAlProyecto()" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Volver al Proyecto
                    </button>
                </div>
                <despiece-optimizador :proyecto-id="proyectoSeleccionado.id"></despiece-optimizador>
            </div>

        </div>
    </main>
</template>

<script>
export default {
    props: ['user'],
    data() {
        return {
            vista: 'listado', // listado, proyecto, mueble
            activeTab: 'general',
            buscar: '',
            criterio: 'cliente',
            arrayProyectos: [],
            arrayMuebles: [],
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0,
            },
            offset: 3,

            // Selección de Proyecto
            proyectoSeleccionado: {},

            // Formularios
            modalProyecto: 0,
            formProyecto: {
                id: null,
                cliente: '',
                descripcion: '',
                fecha: ''
            },

            formMueble: {
                id: null,
                proyecto_id: null,
                nombre: '',
                tipo_mueble: 'Bajo',
                ancho: 800,
                alto: 850,
                profundidad: 600,
                espesor_material: 15,
                material: 'Melamina Blanco',
                material_interno: 'Melamina Blanco 15mm',
                material_externo: 'Melamina Color 15mm',
                tipo_meson: 'Sin Tapa',
                costados_vistos: 'Ambos',
                sistema_apertura: 'Manija',
                tipo_tirador: 'Estándar',
                zocalo: 100,
                puertas: 2,
                cajones: 3,
                estantes: 1,
                notas: '',
                piezas: []
            },
            materialSettings: {},
            tipo_vista_grafico: 'Perspectiva',
            tableroEstLargo: 2440,
            tableroEstAncho: 1220,
            tableroEstDesperdicio: 12,
            materialOptimizarActivo: ''
        }
    },
    computed: {
        mueblesDeCalculo() {
            if (this.vista === 'mueble' && this.formMueble) {
                let tempMuebles = JSON.parse(JSON.stringify(this.arrayMuebles));
                if (this.formMueble.id) {
                    let index = tempMuebles.findIndex(m => m.id === this.formMueble.id);
                    if (index !== -1) {
                        tempMuebles[index] = this.formMueble;
                    } else {
                        tempMuebles.push(this.formMueble);
                    }
                } else {
                    tempMuebles.push(this.formMueble);
                }
                return tempMuebles;
            }
            return this.arrayMuebles;
        },
        isActived() {
            return this.pagination.current_page;
        },
        pagesNumber() {
            if (!this.pagination.to) {
                return [];
            }
            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },

        // Resumen de consumo del mueble actual
        areaMelaminaMueble() {
            let total = 0;
            this.formMueble.piezas.forEach(p => {
                total += (p.largo * p.ancho * p.cantidad) / 1000000;
            });
            return total;
        },
        metrosCantoMueble() {
            let total = 0;
            this.formMueble.piezas.forEach(p => {
                let pEnchapado = 0;
                if (p.canto_l1) pEnchapado += p.largo;
                if (p.canto_l2) pEnchapado += p.largo;
                if (p.canto_a1) pEnchapado += p.ancho;
                if (p.canto_a2) pEnchapado += p.ancho;
                total += (pEnchapado * p.cantidad) / 1000;
            });
            return total;
        },

        // Resumen total del proyecto seleccionado
        totalMelaminaProyecto() {
            let total = 0;
            this.mueblesDeCalculo.forEach(m => {
                m.piezas.forEach(p => {
                    total += (parseFloat(p.largo) * parseFloat(p.ancho) * parseInt(p.cantidad)) / 1000000;
                });
            });
            return total;
        },
        totalCantoProyecto() {
            let total = 0;
            this.mueblesDeCalculo.forEach(m => {
                m.piezas.forEach(p => {
                    let pEnchapado = 0;
                    if (p.canto_l1) pEnchapado += parseFloat(p.largo);
                    if (p.canto_l2) pEnchapado += parseFloat(p.largo);
                    if (p.canto_a1) pEnchapado += parseFloat(p.ancho);
                    if (p.canto_a2) pEnchapado += parseFloat(p.ancho);
                    total += (pEnchapado * parseInt(p.cantidad)) / 1000;
                });
            });
            return total;
        },
        resumenMateriales() {
            let resumen = {};
            this.mueblesDeCalculo.forEach(mueble => {
                if (mueble.piezas) {
                    mueble.piezas.forEach(pieza => {
                        const matName = (pieza.material || mueble.material_interno || mueble.material || 'Melamina Blanco 15mm').trim();
                        if (!resumen[matName]) {
                            if (!this.materialSettings[matName]) {
                                this.$set(this.materialSettings, matName, {
                                    largo: 2440,
                                    ancho: 1220,
                                    desperdicio: 12
                                });
                            }
                            resumen[matName] = {
                                material: matName,
                                totalArea: 0,
                                cantPiezas: 0
                            };
                        }
                        const areaPieza = parseFloat(pieza.largo) * parseFloat(pieza.ancho) * parseInt(pieza.cantidad);
                        resumen[matName].totalArea += areaPieza;
                        resumen[matName].cantPiezas += parseInt(pieza.cantidad);
                    });
                }
            });
            let arrayResumen = [];
            for (let mat in resumen) {
                const settings = this.materialSettings[mat];
                const totalAreaM2 = resumen[mat].totalArea / 1000000;
                const sheetAreaM2 = (settings.largo * settings.ancho) / 1000000;
                const areaWithWaste = totalAreaM2 * (1 + (settings.desperdicio / 100));
                const tablerosEstimados = areaWithWaste / (sheetAreaM2 || 1);
                const tablerosComprar = Math.ceil(tablerosEstimados);
                
                arrayResumen.push({
                    material: mat,
                    totalAreaM2: totalAreaM2,
                    sheetAreaM2: sheetAreaM2,
                    cantPiezas: resumen[mat].cantPiezas,
                    largo: settings.largo,
                    ancho: settings.ancho,
                    desperdicio: settings.desperdicio,
                    tablerosEstimados: tablerosEstimados,
                    tablerosComprar: tablerosComprar
                });
            }
            return arrayResumen;
        },
        tablerosOptimizados() {
            let resultado = {};
            const kerf = 4; // saw blade kerf in mm
            
            // 1. Group all pieces by material
            let piezasPorMaterial = {};
            this.arrayMuebles.forEach(mueble => {
                if (mueble.piezas) {
                    mueble.piezas.forEach(pieza => {
                        const matName = (pieza.material || mueble.material_interno || mueble.material || 'Melamina Blanco 15mm').trim();
                        if (!piezasPorMaterial[matName]) {
                            piezasPorMaterial[matName] = [];
                        }
                        for (let q = 0; q < parseInt(pieza.cantidad || 1); q++) {
                            piezasPorMaterial[matName].push({
                                nombre: pieza.nombre_pieza || pieza.nombre || 'Pieza',
                                w: parseFloat(pieza.largo),
                                h: parseFloat(pieza.ancho),
                                muebleNombre: mueble.nombre
                            });
                        }
                    });
                }
            });

            // 2. Pack pieces for each material
            for (let mat in piezasPorMaterial) {
                const settings = this.materialSettings[mat] || { largo: 2440, ancho: 1220 };
                const sheetW = parseFloat(settings.largo || 2440);
                const sheetH = parseFloat(settings.ancho || 1220);
                
                let piezas = piezasPorMaterial[mat];
                
                // sort descending by area to place largest pieces first
                piezas.sort((a, b) => (b.w * b.h) - (a.w * a.h));
                
                let sheets = [];
                let currentSheet = {
                    id: 1,
                    w: sheetW,
                    h: sheetH,
                    packedPieces: [],
                    shelves: []
                };
                sheets.push(currentSheet);
                
                piezas.forEach(p => {
                    let pw = p.w;
                    let ph = p.h;
                    
                    // clamp pieces to sheet size if too large (fallback)
                    if (pw > sheetW && pw > sheetH) pw = Math.max(sheetW, sheetH);
                    if (ph > sheetH && ph > sheetW) ph = Math.max(sheetW, sheetH);
                    
                    let packed = false;
                    
                    for (let sIndex = 0; sIndex < sheets.length; sIndex++) {
                        let sheet = sheets[sIndex];
                        // Try to pack in existing shelves
                        for (let shIndex = 0; shIndex < sheet.shelves.length; shIndex++) {
                            let shelf = sheet.shelves[shIndex];
                            
                            // Try normal orientation
                            if (shelf.currentX + pw <= sheetW && ph <= shelf.height) {
                                sheet.packedPieces.push({
                                    x: shelf.currentX, y: shelf.y, w: pw, h: ph,
                                    nombre: p.nombre, mueble: p.muebleNombre
                                });
                                shelf.currentX += pw + kerf;
                                packed = true;
                                break;
                            }
                            
                            // Try rotated orientation
                            if (shelf.currentX + ph <= sheetW && pw <= shelf.height) {
                                sheet.packedPieces.push({
                                    x: shelf.currentX, y: shelf.y, w: ph, h: pw,
                                    nombre: p.nombre, mueble: p.muebleNombre
                                });
                                shelf.currentX += ph + kerf;
                                packed = true;
                                break;
                            }
                        }
                        
                        if (packed) break;
                        
                        // If it doesn't fit in any shelf, try creating a new shelf on this sheet
                        let lastShelfY = 0;
                        let lastShelfH = 0;
                        if (sheet.shelves.length > 0) {
                            let lastShelf = sheet.shelves[sheet.shelves.length - 1];
                            lastShelfY = lastShelf.y;
                            lastShelfH = lastShelf.height;
                        }
                        
                        let newShelfY = lastShelfY + lastShelfH + (sheet.shelves.length > 0 ? kerf : 0);
                        
                        let canNormal = (newShelfY + ph <= sheetH && pw <= sheetW);
                        let canRotated = (newShelfY + pw <= sheetH && ph <= sheetW);
                        
                        if (canNormal || canRotated) {
                            // Determine best orientation (prefer the one that uses less height to save vertical space)
                            let useRotated = false;
                            if (canNormal && canRotated) {
                                useRotated = (pw < ph);
                            } else if (canRotated) {
                                useRotated = true;
                            }
                            
                            let shelfH = useRotated ? pw : ph;
                            let shelfW = useRotated ? ph : pw;
                            
                            let newShelf = {
                                y: newShelfY,
                                height: shelfH,
                                currentX: shelfW + kerf
                            };
                            sheet.shelves.push(newShelf);
                            sheet.packedPieces.push({
                                x: 0, y: newShelfY, w: shelfW, h: shelfH,
                                nombre: p.nombre, mueble: p.muebleNombre
                            });
                            packed = true;
                            break;
                        }
                    }
                    
                    if (!packed) {
                        // Create a new sheet
                        let newSheet = {
                            id: sheets.length + 1,
                            w: sheetW,
                            h: sheetH,
                            packedPieces: [],
                            shelves: []
                        };
                        
                        // Decide initial orientation for the new sheet
                        let useRotated = (pw < ph);
                        if (useRotated && ph > sheetW) useRotated = false;
                        if (!useRotated && pw > sheetW) useRotated = true;
                        
                        let shelfH = useRotated ? pw : ph;
                        let shelfW = useRotated ? ph : pw;
                        
                        let newShelf = {
                            y: 0,
                            height: shelfH,
                            currentX: shelfW + kerf
                        };
                        newSheet.shelves.push(newShelf);
                        newSheet.packedPieces.push({
                            x: 0, y: 0, w: shelfW, h: shelfH,
                            nombre: p.nombre, mueble: p.muebleNombre
                        });
                        sheets.push(newSheet);
                    }
                });
                
                sheets.forEach(sheet => {
                    let usedArea = 0;
                    sheet.packedPieces.forEach(p => {
                        usedArea += p.w * p.h;
                    });
                    sheet.efficiency = (usedArea / (sheetW * sheetH)) * 100;
                });
                
                resultado[mat] = {
                    material: mat,
                    sheetW: sheetW,
                    sheetH: sheetH,
                    sheets: sheets
                };
            }
            return resultado;
        },
        tablerosOptimizadosMueble() {
            let resultado = {};
            const kerf = 4; // saw blade kerf in mm
            
            // 1. Group all pieces of formMueble by material
            let piezasPorMaterial = {};
            if (this.formMueble && this.formMueble.piezas) {
                this.formMueble.piezas.forEach(pieza => {
                    const matName = (pieza.material || this.formMueble.material_interno || this.formMueble.material || 'Melamina Blanco 15mm').trim();
                    if (!piezasPorMaterial[matName]) {
                        piezasPorMaterial[matName] = [];
                    }
                    for (let q = 0; q < parseInt(pieza.cantidad || 1); q++) {
                        piezasPorMaterial[matName].push({
                            nombre: pieza.nombre_pieza || pieza.nombre || 'Pieza',
                            w: parseFloat(pieza.largo),
                            h: parseFloat(pieza.ancho)
                        });
                    }
                });
            }

            // 2. Pack pieces for each material using single cabinet custom board sizes
            for (let mat in piezasPorMaterial) {
                const sheetW = parseFloat(this.tableroEstLargo || 2440);
                const sheetH = parseFloat(this.tableroEstAncho || 1220);
                
                let piezas = piezasPorMaterial[mat];
                
                piezas.forEach(p => {
                    if (p.h > p.w) {
                        const temp = p.w;
                        p.w = p.h;
                        p.h = temp;
                    }
                });
                
                piezas.sort((a, b) => b.h - a.h || b.w - a.w);
                
                let sheets = [];
                let currentSheet = {
                    id: 1,
                    w: sheetW,
                    h: sheetH,
                    packedPieces: [],
                    shelves: []
                };
                sheets.push(currentSheet);
                
                piezas.forEach(p => {
                    let pw = p.w;
                    let ph = p.h;
                    if (pw > sheetW) pw = sheetW;
                    if (ph > sheetH) ph = sheetH;
                    
                    let packed = false;
                    
                    for (let sIndex = 0; sIndex < sheets.length; sIndex++) {
                        let sheet = sheets[sIndex];
                        for (let shIndex = 0; shIndex < sheet.shelves.length; shIndex++) {
                            let shelf = sheet.shelves[shIndex];
                            if (shelf.currentX + pw <= sheetW && ph <= shelf.height) {
                                sheet.packedPieces.push({
                                    x: shelf.currentX,
                                    y: shelf.y,
                                    w: pw,
                                    h: ph,
                                    nombre: p.nombre
                                });
                                shelf.currentX += pw + kerf;
                                packed = true;
                                break;
                            }
                        }
                        
                        if (packed) break;
                        
                        let lastShelfY = 0;
                        let lastShelfH = 0;
                        if (sheet.shelves.length > 0) {
                            let lastShelf = sheet.shelves[sheet.shelves.length - 1];
                            lastShelfY = lastShelf.y;
                            lastShelfH = lastShelf.height;
                        }
                        
                        let newShelfY = lastShelfY + lastShelfH + (sheet.shelves.length > 0 ? kerf : 0);
                        if (newShelfY + ph <= sheetH) {
                            let newShelf = {
                                y: newShelfY,
                                height: ph,
                                currentX: pw + kerf
                            };
                            sheet.shelves.push(newShelf);
                            sheet.packedPieces.push({
                                x: 0,
                                y: newShelfY,
                                w: pw,
                                h: ph,
                                nombre: p.nombre
                            });
                            packed = true;
                            break;
                        }
                    }
                    
                    if (!packed) {
                        let newSheet = {
                            id: sheets.length + 1,
                            w: sheetW,
                            h: sheetH,
                            packedPieces: [],
                            shelves: []
                        };
                        let newShelf = {
                            y: 0,
                            height: ph,
                            currentX: pw + kerf
                        };
                        newSheet.shelves.push(newShelf);
                        newSheet.packedPieces.push({
                            x: 0,
                            y: 0,
                            w: pw,
                            h: ph,
                            nombre: p.nombre
                        });
                        sheets.push(newSheet);
                    }
                });
                
                sheets.forEach(sheet => {
                    let usedArea = 0;
                    sheet.packedPieces.forEach(p => {
                        usedArea += p.w * p.h;
                    });
                    sheet.efficiency = (usedArea / (sheetW * sheetH)) * 100;
                });
                
                resultado[mat] = {
                    material: mat,
                    sheetW: sheetW,
                    sheetH: sheetH,
                    sheets: sheets
                };
            }
            return resultado;
        }
    },
    methods: {
        getOptimizadoActivo() {
            const opts = this.tablerosOptimizados;
            let active = this.materialOptimizarActivo;
            const exists = this.resumenMateriales.some(r => r.material === active);
            if (!active || !exists) {
                if (this.resumenMateriales.length > 0) {
                    active = this.resumenMateriales[0].material;
                    this.materialOptimizarActivo = active;
                } else {
                    return null;
                }
            }
            return opts[active] || null;
        },
        listarProyectos(page) {
            let me = this;
            let url = `/despiece/proyectos?page=${page}&buscar=${me.buscar}&criterio=${me.criterio}`;
            axios.get(url).then(response => {
                let respuesta = response.data;
                me.arrayProyectos = respuesta.proyectos;
                me.pagination = respuesta.pagination;
            }).catch(error => {
                console.error(error);
            });
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.listarProyectos(page);
        },

        // Métodos de Proyecto
        nuevoProyecto() {
            let today = new Date().toISOString().split('T')[0];
            this.formProyecto = {
                id: null,
                cliente: '',
                descripcion: '',
                fecha: today
            };
            this.modalProyecto = 1;
        },
        editarProyecto(proyecto) {
            this.formProyecto = {
                id: proyecto.id,
                cliente: proyecto.cliente,
                descripcion: proyecto.descripcion,
                fecha: proyecto.fecha
            };
            this.modalProyecto = 1;
        },
        cerrarModalProyecto() {
            this.modalProyecto = 0;
        },
        guardarProyecto() {
            let me = this;
            if (!me.formProyecto.cliente || !me.formProyecto.fecha) {
                Swal.fire('Error', 'El cliente y la fecha son obligatorios', 'error');
                return;
            }
            axios.post('/despiece/proyectos/registrar', me.formProyecto).then(response => {
                me.cerrarModalProyecto();
                Swal.fire('Guardado', 'Proyecto registrado correctamente.', 'success');
                me.listarProyectos(me.pagination.current_page);
            }).catch(error => {
                console.error(error);
            });
        },
        eliminarProyecto(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: 'Se eliminarán todos los muebles y despieces asociados a este proyecto.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/despiece/proyectos/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'El proyecto ha sido eliminado.', 'success');
                        me.listarProyectos(1);
                    }).catch(error => {
                        console.error(error);
                    });
                }
            });
        },

        abrirProyecto(proyecto) {
            this.proyectoSeleccionado = proyecto;
            this.listarMuebles(proyecto.id);
            this.vista = 'proyecto';
        },
        volverAlListado() {
            this.vista = 'listado';
            this.proyectoSeleccionado = {};
            this.arrayMuebles = [];
        },

        // Métodos de Muebles
        listarMuebles(proyecto_id) {
            let me = this;
            axios.get(`/despiece/muebles/${proyecto_id}`).then(response => {
                me.arrayMuebles = response.data;
            }).catch(error => {
                console.error(error);
            });
        },

        nuevoMueble() {
            this.formMueble = {
                id: null,
                proyecto_id: this.proyectoSeleccionado.id,
                nombre: '',
                tipo_mueble: 'Bajo',
                ancho: 800,
                alto: 850,
                profundidad: 600,
                espesor_material: 15,
                material: 'Melamina Blanco',
                material_interno: 'Melamina Blanco 15mm',
                material_externo: 'Melamina Color 15mm',
                tipo_meson: 'Sin Tapa',
                costados_vistos: 'Ambos',
                sistema_apertura: 'Manija',
                tipo_tirador: 'Estándar',
                zocalo: 100,
                alto_meson: 0,
                tiene_fondo: true,
                material_fondo: 'Durolac Blanco / MDF 3mm',
                divisiones: [
                    { tipo: 'Puertas', cantidad: 2 }
                ],
                notas: '',
                piezas: []
            };
            this.recalcularDespiece();
            this.tipo_vista_grafico = 'Perspectiva';
            this.vista = 'mueble';
        },

        editarMueble(mueble) {
            // Restore form properties or guess them based on DB record
            this.formMueble = {
                id: mueble.id,
                proyecto_id: mueble.proyecto_id,
                nombre: mueble.nombre,
                tipo_mueble: mueble.tipo_mueble,
                ancho: parseInt(mueble.ancho),
                alto: parseInt(mueble.alto),
                profundidad: parseInt(mueble.profundidad),
                espesor_material: parseInt(mueble.espesor_material),
                material: mueble.material,
                material_interno: mueble.material_interno || 'Melamina Blanco 15mm',
                material_externo: mueble.material_externo || 'Melamina Color 15mm',
                tipo_meson: mueble.tipo_meson || 'Sin Tapa',
                costados_vistos: mueble.costados_vistos || 'Ambos',
                sistema_apertura: mueble.sistema_apertura || 'Manija',
                tipo_tirador: mueble.tipo_tirador || 'Estándar',
                notas: mueble.notas,
                // defaults to guess zocalo/puertas/estantes for GUI recalculation if needed
                zocalo: mueble.tipo_mueble === 'Alto' ? 0 : 100,
                alto_meson: mueble.alto_meson || 0,
                tiene_fondo: mueble.tiene_fondo !== undefined ? !!mueble.tiene_fondo : true,
                material_fondo: mueble.material_fondo || 'Durolac Blanco / MDF 3mm',
                divisiones: mueble.divisiones ? JSON.parse(JSON.stringify(mueble.divisiones)) : [],
                piezas: JSON.parse(JSON.stringify(mueble.piezas)) // Clone pieces
            };
            this.tipo_vista_grafico = 'Perspectiva';
            this.vista = 'mueble';
        },

        eliminarMueble(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar este mueble?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/despiece/muebles/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'Mueble eliminado del proyecto.', 'success');
                        me.listarMuebles(me.proyectoSeleccionado.id);
                    }).catch(error => {
                        console.error(error);
                    });
                }
            });
        },

        volverAlProyecto() {
            this.vista = 'proyecto';
        },

        agregarDivision() {
            if (!this.formMueble.divisiones) {
                this.$set(this.formMueble, 'divisiones', []);
            }
            this.formMueble.divisiones.push({ tipo: 'Cajones', cantidad: 1 });
            this.recalcularDespiece();
        },

        eliminarDivision(index) {
            this.formMueble.divisiones.splice(index, 1);
            this.recalcularDespiece();
        },

        guardarMueble() {
            let me = this;
            if (!me.formMueble.nombre || !me.formMueble.ancho || !me.formMueble.alto || !me.formMueble.profundidad) {
                Swal.fire('Error', 'Complete los datos obligatorios del mueble (Nombre, Ancho, Alto, Fondo)', 'error');
                return;
            }
            axios.post('/despiece/muebles/registrar', me.formMueble).then(response => {
                Swal.fire('Guardado', 'Módulo guardado correctamente en el proyecto.', 'success');
                me.listarMuebles(me.proyectoSeleccionado.id);
                me.vista = 'proyecto';
            }).catch(error => {
                console.error(error);
            });
        },

        // Algoritmo de cálculo de Despiece
        recalcularDespiece() {
            const H = parseFloat(this.formMueble.alto || 0);
            const W = parseFloat(this.formMueble.ancho || 0);
            const D = parseFloat(this.formMueble.profundidad || 0);
            const t = parseFloat(this.formMueble.espesor_material || 15);
            const Z = this.formMueble.tipo_mueble === 'Alto' ? 0 : parseFloat(this.formMueble.zocalo || 100);
            let M = 0;
            if (this.formMueble.tipo_mueble !== 'Alto') {
                if (this.formMueble.tipo_meson === 'Sin Tapa') {
                    M = parseFloat(this.formMueble.alto_meson || 0);
                } else if (this.formMueble.tipo_meson === 'Mesón Melamina') {
                    M = t; // Si es melamina, el espesor del material es el alto del mesón
                }
            }
            const numEstantes = parseInt(this.formMueble.estantes || 0);
            const numPuertas = parseInt(this.formMueble.puertas || 0);
            const numCajones = parseInt(this.formMueble.cajones || 3);
            const sistemaApertura = this.formMueble.sistema_apertura || 'Manija';

            const matInt = this.formMueble.material_interno || this.formMueble.material || 'Melamina Blanco 15mm';
            const matExt = this.formMueble.material_externo || this.formMueble.material || 'Melamina Color 15mm';
            const meson = this.formMueble.tipo_meson || 'Sin Tapa';
            const costadosVistos = this.formMueble.costados_vistos || 'Ambos';

            // Sincronizar tipo_tirador
            if (sistemaApertura !== 'Manija') {
                this.formMueble.tipo_tirador = 'Sin Tirador';
            } else if (this.formMueble.tipo_tirador === 'Sin Tirador') {
                this.formMueble.tipo_tirador = 'Estándar';
            }

            let arrayP = [];

            if (this.formMueble.tipo_mueble === 'Bajo') {
                const H_cuerpo = H - Z - M;

                let costado_nota = '';
                if (sistemaApertura === 'Gola') {
                    costado_nota = ' (Ranura Gola)';
                } else if (sistemaApertura === 'Push') {
                    costado_nota = ' (Push)';
                }

                // Costado Izquierdo
                const matLeft = (costadosVistos === 'Ambos' || costadosVistos === 'Izquierdo') ? matExt : matInt;
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Izquierdo' + costado_nota,
                    material: matLeft,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Costado Derecho
                const matRight = (costadosVistos === 'Ambos' || costadosVistos === 'Derecho') ? matExt : matInt;
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Derecho' + costado_nota,
                    material: matRight,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Piso
                arrayP.push({
                    nombre_pieza: 'Piso',
                    material: matInt,
                    cantidad: 1,
                    largo: W - (2 * t),
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Tapa Superior / Mesón (Si aplica)
                if (meson === 'Mesón Melamina') {
                    arrayP.push({
                        nombre_pieza: 'Tapa / Mesón Melamina',
                        material: matExt,
                        cantidad: 1,
                        largo: W,
                        ancho: D,
                        canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                    });
                } else if (meson === 'Cerrado') {
                    arrayP.push({
                        nombre_pieza: 'Tapa Superior Estructura',
                        material: matInt,
                        cantidad: 1,
                        largo: W - (2 * t),
                        ancho: D,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Amarres superiores
                if (meson !== 'Cerrado') {
                    arrayP.push({
                        nombre_pieza: 'Amarre Superior',
                        material: matInt,
                        cantidad: 2,
                        largo: W - (2 * t),
                        ancho: 80,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Estantes (eliminado)

                // Fondo
                if (this.formMueble.tiene_fondo) {
                    arrayP.push({
                        nombre_pieza: 'Fondo (Espalda)',
                        material: this.formMueble.material_fondo || 'Durolac Blanco / MDF 3mm',
                        cantidad: 1,
                        largo: H_cuerpo - 4,
                        ancho: W - 4,
                        canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Frontales por divisiones
                let divisiones = this.formMueble.divisiones || [];
                if (divisiones.length === 0) divisiones = [{tipo: 'Puertas', cantidad: 1}];
                
                let num_divs = divisiones.length;
                if (num_divs > 1) {
                    // Agregar divisorias verticales
                    arrayP.push({
                        nombre_pieza: 'División Vertical',
                        material: matInt,
                        cantidad: num_divs - 1,
                        largo: H_cuerpo - 4,
                        ancho: D - 20,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }
                
                let W_inner = W - 2*t;
                let W_div = (W_inner - (num_divs - 1)*t) / num_divs;
                
                divisiones.forEach((div, idx) => {
                    let num = div.cantidad || 1;
                    if (div.tipo === 'Puertas') {
                        let H_puerta = H_cuerpo - 4;
                        let puerta_nota = ` (Div ${idx+1})`;
                        if (sistemaApertura === 'Gola') {
                            H_puerta = H_cuerpo - 35 - 4;
                            puerta_nota += ' (Gola)';
                        } else if (sistemaApertura === 'Bisel') {
                            puerta_nota += ' (Bisel)';
                        }
                        
                        let W_puerta = (W_div + t - 4) / num; // sumamos 1/2 espesor de cada lado que cubre
                        if (num_divs === 1) W_puerta = (W - 4) / num;

                        arrayP.push({
                            nombre_pieza: 'Puerta' + puerta_nota,
                            material: matExt,
                            cantidad: num,
                            largo: H_puerta,
                            ancho: W_puerta,
                            canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                        });
                    } else if (div.tipo === 'Cajones') {
                        let H_frente = (H_cuerpo - (num * 4) - 4) / num;
                        let frente_nota = ` (Div ${idx+1})`;
                        
                        if (sistemaApertura === 'Gola') {
                            arrayP.push({
                                nombre_pieza: 'Frente Cajón Sup' + frente_nota + ' (-35mm)',
                                material: matExt,
                                cantidad: 1,
                                largo: H_frente - 35,
                                ancho: (num_divs === 1 ? W - 4 : W_div + t - 4),
                                canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                            });
                            if (num > 1) {
                                arrayP.push({
                                    nombre_pieza: 'Frente Cajón Inf' + frente_nota,
                                    material: matExt,
                                    cantidad: num - 1,
                                    largo: H_frente,
                                    ancho: (num_divs === 1 ? W - 4 : W_div + t - 4),
                                    canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                                });
                            }
                        } else {
                            arrayP.push({
                                nombre_pieza: 'Frente Cajón' + frente_nota,
                                material: matExt,
                                cantidad: num,
                                largo: H_frente,
                                ancho: (num_divs === 1 ? W - 4 : W_div + t - 4),
                                canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                            });
                        }
                        
                        // Drawer boxes
                        let W_caja = (num_divs === 1 ? W - 2*t : W_div) - 26;
                        arrayP.push({
                            nombre_pieza: 'Lateral Cajón (Caja)',
                            material: matInt,
                            cantidad: num * 2,
                            largo: D - 50,
                            ancho: 120,
                            canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                        });
                        arrayP.push({
                            nombre_pieza: 'Frente/Trasero (Caja)',
                            material: matInt,
                            cantidad: num * 2,
                            largo: W_caja - 30 > 0 ? W_caja - 30 : 100,
                            ancho: 120,
                            canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                        });
                        arrayP.push({
                            nombre_pieza: 'Fondo Cajón (3mm)',
                            material: 'Durolac Blanco / MDF 3mm',
                            cantidad: num,
                            largo: D - 50,
                            ancho: W_caja > 0 ? W_caja : 100,
                            canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                        });
                    } else if (div.tipo === 'Abierto') {
                        if (num > 0) {
                            arrayP.push({
                                nombre_pieza: `Estante (Div ${idx+1})`,
                                material: matInt,
                                cantidad: num,
                                largo: (num_divs === 1 ? W - 2*t : W_div) - 2,
                                ancho: D - 20,
                                canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                            });
                        }
                    }
                });

            } else if (this.formMueble.tipo_mueble === 'Alto') {
                let costado_nota = '';
                if (sistemaApertura === 'Push') {
                    costado_nota = ' (Push)';
                }

                // Costado Izquierdo
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Izquierdo' + costado_nota,
                    material: matExt,
                    cantidad: 1,
                    largo: H,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Costado Derecho
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Derecho' + costado_nota,
                    material: matExt,
                    cantidad: 1,
                    largo: H,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Piso / Techo
                arrayP.push({
                    nombre_pieza: 'Piso / Techo',
                    material: matInt,
                    cantidad: 2,
                    largo: W - (2 * t),
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Estantes globales eliminados

                // Fondo
                if (this.formMueble.tiene_fondo) {
                    arrayP.push({
                        nombre_pieza: 'Fondo (Espalda)',
                        material: this.formMueble.material_fondo || 'Durolac Blanco / MDF 3mm',
                        cantidad: 1,
                        largo: H - 4,
                        ancho: W - 4,
                        canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Frontales por divisiones
                let divisiones = this.formMueble.divisiones || [];
                if (divisiones.length === 0) divisiones = [{tipo: 'Puertas', cantidad: 1}];
                
                let num_divs = divisiones.length;
                if (num_divs > 1) {
                    // Agregar divisorias verticales
                    arrayP.push({
                        nombre_pieza: 'División Vertical',
                        material: matInt,
                        cantidad: num_divs - 1,
                        largo: H,
                        ancho: D - 20,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }
                
                let W_inner = W - 2*t;
                let W_div = (W_inner - (num_divs - 1)*t) / num_divs;
                
                divisiones.forEach((div, idx) => {
                    let num = div.cantidad || 1;
                    if (div.tipo === 'Puertas') {
                        let H_puerta = H - 4;
                        let puerta_nota = ` (Div ${idx+1})`;
                        if (sistemaApertura === 'Bisel') {
                            puerta_nota += ' (Bisel)';
                        } else if (sistemaApertura === 'Push') {
                            puerta_nota += ' (Push)';
                        }
                        
                        let W_puerta = (W_div + t - 4) / num;
                        if (num_divs === 1) W_puerta = (W - 4) / num;

                        arrayP.push({
                            nombre_pieza: 'Puerta' + puerta_nota,
                            material: matExt,
                            cantidad: num,
                            largo: H_puerta,
                            ancho: W_puerta,
                            canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                        });
                    } else if (div.tipo === 'Abierto') {
                        if (num > 0) {
                            arrayP.push({
                                nombre_pieza: `Estante (Div ${idx+1})`,
                                material: matInt,
                                cantidad: num,
                                largo: (num_divs === 1 ? W - 2*t : W_div) - 2,
                                ancho: D - 10,
                                canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                            });
                        }
                    }
                });

            } else if (this.formMueble.tipo_mueble === 'Cajonera') {
                const H_cuerpo = H - Z - M;

                let costado_nota = '';
                if (sistemaApertura === 'Gola') {
                    costado_nota = ' (Ranura Gola)';
                } else if (sistemaApertura === 'Push') {
                    costado_nota = ' (Push)';
                }

                // Costado Izquierdo
                const matLeft = (costadosVistos === 'Ambos' || costadosVistos === 'Izquierdo') ? matExt : matInt;
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Izquierdo' + costado_nota,
                    material: matLeft,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Costado Derecho
                const matRight = (costadosVistos === 'Ambos' || costadosVistos === 'Derecho') ? matExt : matInt;
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Derecho' + costado_nota,
                    material: matRight,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Piso
                arrayP.push({
                    nombre_pieza: 'Piso',
                    material: matInt,
                    cantidad: 1,
                    largo: W - (2 * t),
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });

                // Tapa Superior / Mesón (Si aplica)
                if (meson === 'Mesón Melamina') {
                    arrayP.push({
                        nombre_pieza: 'Tapa / Mesón Melamina',
                        material: matExt,
                        cantidad: 1,
                        largo: W,
                        ancho: D,
                        canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                    });
                } else if (meson === 'Cerrado') {
                    arrayP.push({
                        nombre_pieza: 'Tapa Superior Estructura',
                        material: matInt,
                        cantidad: 1,
                        largo: W - (2 * t),
                        ancho: D,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Amarres superiores
                if (meson !== 'Cerrado') {
                    arrayP.push({
                        nombre_pieza: 'Amarre Superior',
                        material: matInt,
                        cantidad: 2,
                        largo: W - (2 * t),
                        ancho: 80,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Fondo
                if (this.formMueble.tiene_fondo) {
                    arrayP.push({
                        nombre_pieza: 'Fondo (Espalda)',
                        material: this.formMueble.material_fondo || 'Durolac Blanco / MDF 3mm',
                        cantidad: 1,
                        largo: H_cuerpo - 4,
                        ancho: W - 4,
                        canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }

                // Frontales por divisiones (para Cajonera)
                let divisiones = this.formMueble.divisiones || [];
                if (divisiones.length === 0) divisiones = [{tipo: 'Cajones', cantidad: 3}];
                
                let num_divs = divisiones.length;
                if (num_divs > 1) {
                    arrayP.push({
                        nombre_pieza: 'División Vertical',
                        material: matInt,
                        cantidad: num_divs - 1,
                        largo: H_cuerpo - 4,
                        ancho: D - 20,
                        canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }
                
                let W_inner = W - 2*t;
                let W_div = (W_inner - (num_divs - 1)*t) / num_divs;
                
                divisiones.forEach((div, idx) => {
                    let num = div.cantidad || 1;
                    if (div.tipo === 'Cajones') {
                        let H_frente = (H_cuerpo - (num * 4) - 4) / num;
                        let frente_nota = ` (Div ${idx+1})`;
                        
                        if (sistemaApertura === 'Gola') {
                            arrayP.push({
                                nombre_pieza: 'Frente Cajón Sup' + frente_nota + ' (-35mm)',
                                material: matExt,
                                cantidad: 1,
                                largo: H_frente - 35,
                                ancho: (num_divs === 1 ? W - 4 : W_div + t - 4),
                                canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                            });
                            if (num > 1) {
                                arrayP.push({
                                    nombre_pieza: 'Frente Cajón Inf' + frente_nota,
                                    material: matExt,
                                    cantidad: num - 1,
                                    largo: H_frente,
                                    ancho: (num_divs === 1 ? W - 4 : W_div + t - 4),
                                    canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                                });
                            }
                        } else {
                            arrayP.push({
                                nombre_pieza: 'Frente Cajón' + frente_nota,
                                material: matExt,
                                cantidad: num,
                                largo: H_frente,
                                ancho: (num_divs === 1 ? W - 4 : W_div + t - 4),
                                canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                            });
                        }
                        
                        // Drawer boxes
                        let W_caja = (num_divs === 1 ? W - 2*t : W_div) - 26;
                        arrayP.push({
                            nombre_pieza: 'Lateral Cajón (Caja)',
                            material: matInt,
                            cantidad: num * 2,
                            largo: D - 50,
                            ancho: 120,
                            canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                        });
                        arrayP.push({
                            nombre_pieza: 'Frente/Trasero (Caja)',
                            material: matInt,
                            cantidad: num * 2,
                            largo: W_caja - 30 > 0 ? W_caja - 30 : 100,
                            ancho: 120,
                            canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                        });
                        arrayP.push({
                            nombre_pieza: 'Fondo Cajón (3mm)',
                            material: 'Durolac Blanco / MDF 3mm',
                            cantidad: num,
                            largo: D - 50,
                            ancho: W_caja > 0 ? W_caja : 100,
                            canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                        });
                    }
                });
            } else if (this.formMueble.tipo_mueble === 'Esquinero L') {
                const H_cuerpo = H - Z - M;
                const W1 = W; // Ancho Izquierdo
                const W2 = parseFloat(this.formMueble.ancho_derecho || 0);
                
                // Base structure (approximate logic for L shape corner base)
                arrayP.push({
                    nombre_pieza: 'Piso Esquinero (L)',
                    material: matInt,
                    cantidad: 1,
                    largo: W1 - t,
                    ancho: W2 - t,
                    canto_l1: true, canto_l2: false, canto_a1: true, canto_a2: false
                });
                
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Izquierdo',
                    material: matExt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Derecho',
                    material: matExt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                // Fondos
                if (this.formMueble.tiene_fondo) {
                    arrayP.push({
                        nombre_pieza: 'Fondo Izquierdo (Espalda)',
                        material: this.formMueble.material_fondo || 'Durolac Blanco / MDF 3mm',
                        cantidad: 1,
                        largo: H_cuerpo - 4,
                        ancho: W1 - t - 4,
                        canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                    arrayP.push({
                        nombre_pieza: 'Fondo Derecho (Espalda)',
                        material: this.formMueble.material_fondo || 'Durolac Blanco / MDF 3mm',
                        cantidad: 1,
                        largo: H_cuerpo - 4,
                        ancho: W2 - D - 4,
                        canto_l1: false, canto_l2: false, canto_a1: false, canto_a2: false
                    });
                }
                
                // Puertas en L
                const pLargo = H_cuerpo - (sistemaApertura === 'Gola' ? 35 : 4);
                const pAncho1 = W1 - D - 2;
                const pAncho2 = W2 - D - 2 - t; // Aproximado
                
                arrayP.push({
                    nombre_pieza: 'Puerta Esquinera (Izq)',
                    material: matExt,
                    cantidad: 1,
                    largo: pLargo,
                    ancho: pAncho1,
                    canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                });
                arrayP.push({
                    nombre_pieza: 'Puerta Esquinera (Der)',
                    material: matExt,
                    cantidad: 1,
                    largo: pLargo,
                    ancho: pAncho2,
                    canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                });

            } else if (this.formMueble.tipo_mueble === 'Esquinero Recto') {
                const H_cuerpo = H - Z - M;
                const espacioCiego = parseFloat(this.formMueble.espacio_ciego || 600);
                
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Ciego',
                    material: matInt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                arrayP.push({
                    nombre_pieza: 'Costado Lateral Visto',
                    material: matExt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                arrayP.push({
                    nombre_pieza: 'Piso',
                    material: matInt,
                    cantidad: 1,
                    largo: W - (2 * t),
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                // Panel Ciego Frontal
                arrayP.push({
                    nombre_pieza: 'Panel Ciego Frontal',
                    material: matExt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: espacioCiego,
                    canto_l1: true, canto_l2: false, canto_a1: true, canto_a2: true
                });
                
                // Puerta
                const pLargo = H_cuerpo - (sistemaApertura === 'Gola' ? 35 : 4);
                const pAncho = W - espacioCiego - t - 4; // Aproximado
                arrayP.push({
                    nombre_pieza: 'Puerta',
                    material: matExt,
                    cantidad: 1,
                    largo: pLargo,
                    ancho: pAncho,
                    canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                });

            } else if (this.formMueble.tipo_mueble === 'Columna Nevera') {
                const H_cuerpo = H - Z; // Todo el alto desde el zocalo
                const H_hueco = parseFloat(this.formMueble.hueco_alto || 1800);
                
                arrayP.push({
                    nombre_pieza: 'Costado Torre Izquierdo',
                    material: matExt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: true, canto_a2: true
                });
                
                arrayP.push({
                    nombre_pieza: 'Costado Torre Derecho',
                    material: matExt,
                    cantidad: 1,
                    largo: H_cuerpo,
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: true, canto_a2: true
                });
                
                // Piso superior del hueco (techo de la nevera)
                arrayP.push({
                    nombre_pieza: 'Repisa sobre Nevera',
                    material: matInt,
                    cantidad: 1,
                    largo: W - (2 * t),
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                // Techo de la columna
                arrayP.push({
                    nombre_pieza: 'Techo Columna',
                    material: matInt,
                    cantidad: 1,
                    largo: W - (2 * t),
                    ancho: D,
                    canto_l1: true, canto_l2: false, canto_a1: false, canto_a2: false
                });
                
                // Puerta superior
                const pLargo = H_cuerpo - H_hueco - t - 4;
                const pAncho = W - 4;
                if (pLargo > 0) {
                    arrayP.push({
                        nombre_pieza: 'Puerta Superior (Sobre Nevera)',
                        material: matExt,
                        cantidad: 1,
                        largo: pLargo,
                        ancho: pAncho,
                        canto_l1: true, canto_l2: true, canto_a1: true, canto_a2: true
                    });
                }
            }

            this.formMueble.piezas = arrayP;
        },

        // Métodos de Fila Manual en la tabla de cortes
        agregarFilaManual() {
            this.formMueble.piezas.push({
                nombre_pieza: 'Pieza Nueva',
                cantidad: 1,
                largo: 100,
                ancho: 100,
                canto_l1: false,
                canto_l2: false,
                canto_a1: false,
                canto_a2: false
            });
        },
        eliminarFilaManual(index) {
            this.formMueble.piezas.splice(index, 1);
        },

        // Helper calculations for visual cabinet preview Y coordinate positions
        getEstanteY(index) {
            // Distribute shelves vertically
            const totalH = this.formMueble.tipo_mueble === 'Alto' ? 190 : 170;
            const step = totalH / (this.formMueble.estantes + 1);
            return 20 + (step * index);
        },
        getCajonY(index) {
            const bodyH = 170;
            const drawH = bodyH / this.formMueble.cajones;
            return 20 + (drawH * (index - 1)) + 2;
        },
        getCajonHeight() {
            const bodyH = 170;
            const drawH = bodyH / this.formMueble.cajones;
            return drawH - 4;
        },
        getCajonYDim(index) {
            const H_total = 170;
            const numCajones = this.formMueble.cajones || 3;
            if (this.formMueble.sistema_apertura === 'Gola') {
                const golaTop = 12;
                const golaInter = 8;
                const totalGolaH = golaTop + (numCajones - 1) * golaInter;
                const drawH = (H_total - totalGolaH) / numCajones;
                
                let y = 20 + golaTop;
                for (let i = 1; i < index; i++) {
                    y += drawH + golaInter;
                }
                return y;
            } else {
                const drawH = H_total / numCajones;
                return 20 + (drawH * (index - 1)) + 2;
            }
        },
        getCajonHeightDim(index) {
            const H_total = 170;
            const numCajones = this.formMueble.cajones || 3;
            if (this.formMueble.sistema_apertura === 'Gola') {
                const golaTop = 12;
                const golaInter = 8;
                const totalGolaH = golaTop + (numCajones - 1) * golaInter;
                const drawH = (H_total - totalGolaH) / numCajones;
                return drawH;
            } else {
                const drawH = H_total / numCajones;
                return drawH - 4;
            }
        },

        // Format helpers
        formatFecha(dateString) {
            if (!dateString) return '';
            let parts = dateString.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        },
        formatMonto(value) {
            return this.formatNumber(value);
        },
        formatNumber(value) {
            return parseFloat(value).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        get3DShelves() {
            // Keep this for backward compatibility if needed, but we'll use get3DDivisiones now
            const H = parseFloat(this.formMueble.alto || 850);
            const Z = this.formMueble.tipo_mueble === 'Alto' ? 0 : parseFloat(this.formMueble.zocalo || 100);
            const t = parseFloat(this.formMueble.espesor_material || 15);
            const numEstantes = parseInt(this.formMueble.estantes || 0);
            const H_cuerpo = H - Z;
            let heights = [];
            for (let i = 1; i <= numEstantes; i++) {
                heights.push(Z + t + (i * (H_cuerpo - 2 * t) / (numEstantes + 1)));
            }
            return heights;
        },
        get3DDivisiones() {
            const H = parseFloat(this.formMueble.alto || 850);
            const W = parseFloat(this.formMueble.ancho || 800);
            const D = parseFloat(this.formMueble.profundidad || 600);
            const t = parseFloat(this.formMueble.espesor_material || 15);
            const Z = this.formMueble.tipo_mueble === 'Alto' ? 0 : parseFloat(this.formMueble.zocalo || 100);
            let M = 0;
            if (this.formMueble.tipo_mueble !== 'Alto' && this.formMueble.tipo_meson === 'Sin Tapa') M = parseFloat(this.formMueble.alto_meson || 0);
            if (this.formMueble.tipo_mueble !== 'Alto' && this.formMueble.tipo_meson === 'Mesón Melamina') M = t;
            
            const H_cuerpo = H - Z - M;
            const sistemaApertura = this.formMueble.sistema_apertura || 'Manija';
            
            let divisiones = this.formMueble.divisiones || [];
            if (divisiones.length === 0) {
                // Si no hay divisiones, asumimos 1 división completa con puertas (por retrocompatibilidad visual)
                divisiones = [{tipo: 'Puertas', cantidad: 1}];
            }
            
            let num_divs = divisiones.length;
            let W_inner = W - 2*t;
            let W_div = (W_inner - (num_divs - 1)*t) / num_divs;
            
            let result = [];
            
            // Dibujar divisores verticales internos
            for (let i = 1; i < num_divs; i++) {
                let xDiv = t + i * W_div + (i - 1) * t;
                result.push({
                    type: 'divisor',
                    x1: xDiv, x2: xDiv + t, y1: Z + t, y2: Z + H_cuerpo - t
                });
            }
            
            divisiones.forEach((div, idx) => {
                let xStart = t + idx * (W_div + t);
                let xEnd = xStart + W_div;
                
                // Las puertas/frentes de cajón cubren la mitad del divisor
                let xStartFront = idx === 0 ? 2 : xStart - (t/2);
                let xEndFront = idx === num_divs - 1 ? W - 2 : xEnd + (t/2);
                
                let num = parseInt(div.cantidad) || 1;
                
                if (div.tipo === 'Puertas') {
                    // Divide into multiple doors if num > 1
                    let w_puerta = (xEndFront - xStartFront) / num;
                    for (let i = 0; i < num; i++) {
                        let pxStart = xStartFront + (i * w_puerta);
                        let pxEnd = pxStart + w_puerta;
                        
                        let yBottom = this.formMueble.tipo_mueble === 'Alto' ? 2 : Z + 2;
                        let yTop = Z + H_cuerpo - 2;
                        if (sistemaApertura === 'Gola') yTop -= 35;
                        
                        result.push({
                            type: 'puerta',
                            x1: pxStart, x2: pxEnd - 1, y1: yBottom, y2: yTop,
                            cx: pxStart + (pxEnd - pxStart)/2 // centro para la manija
                        });
                    }
                } else if (div.tipo === 'Cajones') {
                    let h_cajon = (H_cuerpo - (num * 4) - 4) / num;
                    for (let i = 0; i < num; i++) {
                        let yBottom = Z + 2 + i * (h_cajon + 4);
                        let yTop = yBottom + h_cajon;
                        let isTop = (i === num - 1);
                        if (sistemaApertura === 'Gola' && isTop) yTop -= 35;
                        
                        result.push({
                            type: 'cajon',
                            x1: xStartFront, x2: xEndFront - 1, y1: yBottom, y2: yTop,
                            cx: xStartFront + (xEndFront - xStartFront)/2
                        });
                    }
                } else if (div.tipo === 'Abierto') {
                    // Just draw horizontal shelves
                    let h_estante = H_cuerpo / (num + 1);
                    for (let i = 1; i <= num; i++) {
                        let y = Z + i * h_estante;
                        result.push({
                            type: 'estante',
                            x1: xStart, x2: xEnd, y1: y, y2: y + t
                        });
                    }
                }
            });
            return result;
        },
        pt(x, y, z) {
            const W = parseFloat(this.formMueble.ancho || 800);
            const H = parseFloat(this.formMueble.alto || 850);
            const D = parseFloat(this.formMueble.profundidad || 600);
            
            if (this.tipo_vista_grafico === 'Frontal') {
                const minX = 0;
                const maxX = W;
                const minY = -H;
                const maxY = 0;
                
                const projWidth = maxX - minX;
                const projHeight = maxY - minY;
                const scale = Math.min(300 / (projWidth || 1), 220 / (projHeight || 1));
                
                const midX = W / 2;
                const midY = -H / 2;
                
                const cx = 225 - midX * scale;
                const cy = 150 - midY * scale;
                
                const X = cx + x * scale;
                const Y = cy - y * scale;
                return `${X.toFixed(1)},${Y.toFixed(1)}`;
            } else if (this.tipo_vista_grafico === 'Lateral') {
                // Lateral: Depth (Z) as horizontal, Height (Y) as vertical
                // z = 0 is Front (right of screen), z = D is Back (left of screen)
                const minX = 0;
                const maxX = D;
                const minY = -H;
                const maxY = 0;
                
                const projWidth = maxX - minX;
                const projHeight = maxY - minY;
                const scale = Math.min(300 / (projWidth || 1), 220 / (projHeight || 1));
                
                const midX = D / 2;
                const midY = -H / 2;
                
                const cx = 225 - midX * scale;
                const cy = 150 - midY * scale;
                
                const X = cx + (D - z) * scale;
                const Y = cy - y * scale;
                return `${X.toFixed(1)},${Y.toFixed(1)}`;
            } else {
                // Perspectiva Isométrica 3D
                const minX = -D * 0.866;
                const maxX = W * 0.866;
                const minY = -H;
                const maxY = (W + D) * 0.5;
                
                const projWidth = maxX - minX;
                const projHeight = maxY - minY;
                const scale = Math.min(300 / (projWidth || 1), 220 / (projHeight || 1));
                
                const midX = (minX + maxX) / 2;
                const midY = (minY + maxY) / 2;
                
                const cx = 225 - midX * scale;
                const cy = 150 - midY * scale;
                
                const X = cx + (x - z) * 0.866 * scale;
                const Y = cy - y * scale + (x + z) * 0.5 * scale;
                return `${X.toFixed(1)},${Y.toFixed(1)}`;
            }
        }
    },
    mounted() {
        this.listarProyectos(1);
    }
}
</script>

<style scoped>
.badge-secondary {
    opacity: 0.4;
}
.badge-success {
    opacity: 1 !important;
}
.btn-xs {
    padding: 1px 5px;
    font-size: 11px;
}
.bg-light {
    background-color: #f8f9fa !important;
}
.table-responsive {
    margin-top: 10px;
}
.form-label {
    font-size: 11px;
    margin-bottom: 2px;
}
</style>
