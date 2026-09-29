<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb shadow-sm border-0">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Pagos Programados</li>
        </ol>

        <div class="container-fluid">
            <!-- Premium Summary KPI Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-primary text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ kpis.total_estimado.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Total Activo Estimado</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-danger text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ kpis.vencidos_monto.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Vencidos / Mora ({{ kpis.vencidos_count }})</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-warning text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ kpis.por_vencer_monto.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Vence en 7 días ({{ kpis.por_vencer_count }})</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-clock-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-success text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">{{ kpis.activos_count }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Obligaciones Programadas</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-calendar-check-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Panel -->
            <div class="card border-0 shadow-sm-premium rounded">
                <div class="card-header bg-white border-bottom-light py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-calendar text-primary mr-2 fa-lg"></i>
                        <h5 class="mb-0 font-weight-bold">Agenda de Obligaciones y Pagos Recurrentes</h5>
                    </div>
                    <button class="btn btn-primary btn-sm-premium mt-2 mt-sm-0" @click="abrirModalCrear()">
                        <i class="fa fa-plus mr-1"></i> Programar Nuevo Pago
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filters Grid -->
                    <div class="row mb-3 bg-light p-3 rounded border border-light-2 select-container">
                        <div class="col-md-3 col-sm-6 form-group">
                            <label class="small font-weight-bold text-muted">Categoría</label>
                            <select class="form-control form-control-sm border-light-2" v-model="filtroCategoria" @change="listar(1)">
                                <option value="">Todas las categorías</option>
                                <option value="Préstamo / Crédito">Préstamo / Crédito</option>
                                <option value="Impuesto / Tributario">Impuesto / Tributario</option>
                                <option value="Vehicular (SOAT/Tecno)">Vehicular (SOAT/Tecno)</option>
                                <option value="Arriendo / Servicios">Arriendo / Servicios</option>
                                <option value="Seguros / Licencias">Seguros / Licencias</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label class="small font-weight-bold text-muted">Frecuencia</label>
                            <select class="form-control form-control-sm border-light-2" v-model="filtroFrecuencia" @change="listar(1)">
                                <option value="">Todas las frecuencias</option>
                                <option value="Mensual">Mensual</option>
                                <option value="Bimensual">Bimensual (2 meses)</option>
                                <option value="Cuatrimestral">Cuatrimestral (4 meses)</option>
                                <option value="Semestral">Semestral (6 meses)</option>
                                <option value="Anual">Anual (1 año)</option>
                                <option value="Única vez">Única vez</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12 form-group">
                            <label class="small font-weight-bold text-muted">Buscar por Concepto o Beneficiario</label>
                            <div class="input-group input-group-sm">
                                <input type="text" 
                                       class="form-control border-light-2" 
                                       placeholder="Buscar..." 
                                       v-model="buscar" 
                                       @keyup.enter="listar(1)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" @click="listar(1)">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12 form-group d-flex align-items-end justify-content-end">
                            <button class="btn btn-outline-secondary btn-sm-premium w-100" @click="limpiarFiltros()">
                                <i class="fa fa-refresh mr-1"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-custom border-light-2">
                            <thead>
                                <tr>
                                    <th>Concepto / Obligación</th>
                                    <th>Categoría</th>
                                    <th>Frecuencia</th>
                                    <th>Beneficiario</th>
                                    <th class="text-right">Monto Estimado</th>
                                    <th class="text-center">Próximo Pago</th>
                                    <th class="text-center">Estado Alerta</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pago in arrayPagos" :key="pago.id" class="align-middle">
                                    <td>
                                        <strong class="text-dark d-block">{{ pago.concepto }}</strong>
                                        <small class="text-muted" v-if="pago.observaciones">{{ pago.observaciones }}</small>
                                        <div v-if="pago.cuenta" class="mt-1">
                                            <span class="badge badge-light border text-muted" style="font-size: 0.7rem; padding: 2px 4px;">
                                                <i class="fa fa-book mr-1"></i> {{ pago.cuenta.codigo }} - {{ pago.cuenta.nombre }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge font-weight-bold" :class="obtenerBadgeCategoria(pago.categoria)">
                                            {{ pago.categoria }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-weight-bold">
                                            <i class="fa fa-repeat mr-1 text-primary"></i> {{ pago.frecuencia }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="pago.proveedor" class="badge badge-light border p-2 font-weight-bold text-dark">
                                            <i class="fa fa-building text-secondary mr-1"></i> {{ pago.proveedor.nombre }}
                                        </span>
                                        <span v-else class="text-muted small">Sin vincular</span>
                                    </td>
                                    <td class="text-right font-weight-bold h6 mb-0 text-primary">
                                        ${{ parseFloat(pago.monto_estimado).toLocaleString() }}
                                    </td>
                                    <td class="text-center">
                                        <strong class="d-block">{{ pago.proxima_fecha_pago }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="pago.estado !== 'Activo'" class="badge badge-secondary px-2 py-1">
                                            {{ pago.estado }}
                                        </span>
                                        <span v-else :class="['badge-vencimiento', obtenerClaseVencimiento(pago.proxima_fecha_pago)]">
                                            {{ obtenerTextoVencimiento(pago.proxima_fecha_pago) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-success btn-action mr-1" 
                                                    title="Generar Cuenta por Pagar (Avanza fecha automáticamente)"
                                                    :disabled="pago.estado !== 'Activo'"
                                                    @click="abrirModalGenerarCuenta(pago)">
                                                <i class="fa fa-calendar-check-o"></i> Generar Cta
                                            </button>
                                            <button class="btn btn-info btn-action text-white mr-1" 
                                                    title="Editar" 
                                                    @click="abrirModalEditar(pago)">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-secondary btn-action mr-1" 
                                                    :title="pago.estado === 'Activo' ? 'Pausar' : 'Activar'" 
                                                    @click="toggleEstado(pago)">
                                                <i :class="pago.estado === 'Activo' ? 'fa fa-pause' : 'fa fa-play'"></i>
                                            </button>
                                            <button class="btn btn-danger btn-action" 
                                                    title="Eliminar" 
                                                    @click="eliminar(pago)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="arrayPagos.length === 0">
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No se encontraron pagos programados registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                        <span class="small text-muted">Mostrando del {{ pagination.from || 0 }} al {{ pagination.to || 0 }} de {{ pagination.total || 0 }} registros</span>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">
                                    <i class="fa fa-angle-left"></i>
                                </a>
                            </li>
                            <li class="page-item" 
                                v-for="page in paginasCalculadas" 
                                :key="page" 
                                :class="{ active: page === pagination.current_page }">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                            </li>
                            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal Crear/Editar Pago Programado -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalForm}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 3000 !important;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header" :class="tipoAccion === 1 ? 'bg-primary' : 'bg-info'">
                        <h5 class="modal-title text-white font-weight-bold">
                            {{ tipoAccion === 1 ? 'Programar Nuevo Pago / Obligación' : 'Editar Pago Programado' }}
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalForm()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardar()">
                            <div class="row">
                                <div class="col-md-8 form-group">
                                    <label class="font-weight-bold small">Concepto / Nombre de la Obligación <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" v-model="form.concepto" placeholder="Ej. Cuota Préstamo Bancolombia / SOAT Camión" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold small">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="form.categoria" required>
                                        <option value="Préstamo / Crédito">Préstamo / Crédito</option>
                                        <option value="Impuesto / Tributario">Impuesto / Tributario</option>
                                        <option value="Vehicular (SOAT/Tecno)">Vehicular (SOAT/Tecno)</option>
                                        <option value="Arriendo / Servicios">Arriendo / Servicios</option>
                                        <option value="Seguros / Licencias">Seguros / Licencias</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Frecuencia de Pago <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="form.frecuencia" required>
                                        <option value="Mensual">Mensual (Cada mes)</option>
                                        <option value="Bimensual">Bimensual (Cada 2 meses)</option>
                                        <option value="Cuatrimestral">Cuatrimestral (Cada 4 meses)</option>
                                        <option value="Semestral">Semestral (Cada 6 meses)</option>
                                        <option value="Anual">Anual (Cada año)</option>
                                        <option value="Única vez">Única vez (Sin repetición)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Monto Estimado Cuota ($) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control text-right font-weight-bold text-primary" v-model.number="form.monto_estimado" min="0.01" step="any" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold small">Próxima Fecha de Pago <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="form.proxima_fecha_pago" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold small">Proveedor Registrado (Opcional)</label>
                                    <select class="form-control" v-model="form.proveedor_id">
                                        <option value="">-- Ninguno (Usar Beneficiario) --</option>
                                        <option v-for="prov in proveedores" :key="prov.id" :value="prov.id">{{ prov.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold small">Beneficiario / Entidad (Texto libre)</label>
                                    <input type="text" class="form-control" v-model="form.beneficiario" placeholder="Ej. DIAN, Banco, Persona, etc.">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Cuenta Contable PUC (Opcional)</label>
                                    <select class="form-control" v-model="form.cuenta_id">
                                        <option value="">-- Sin asignar --</option>
                                        <option v-for="cta in cuentas" :key="cta.id" :value="cta.id">{{ cta.codigo }} - {{ cta.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Estado</label>
                                    <select class="form-control" v-model="form.estado">
                                        <option value="Activo">Activo</option>
                                        <option value="Pausado">Pausado</option>
                                        <option value="Finalizado">Finalizado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold small">Observaciones / Notas Adicionales</label>
                                <textarea class="form-control" rows="2" v-model="form.observaciones" placeholder="Detalles de la entidad bancaria, número de póliza, placa del vehículo, etc."></textarea>
                            </div>

                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-secondary btn-sm-premium mr-2" @click="cerrarModalForm()">Cancelar</button>
                                <button type="submit" class="btn btn-success btn-sm-premium" :disabled="loadingForm">
                                    <span v-if="loadingForm"><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                                    <span v-else>Guardar</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Generar Cuenta por Pagar -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalGenerar}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 3500 !important;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-calendar-check-o mr-1"></i> Generar Cuenta por Pagar
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalGenerar()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            Se registrará la obligación en **Cuentas por Pagar** y la fecha del pago programado avanzará automáticamente al siguiente ciclo de <strong>{{ pagoGenerar.frecuencia }}</strong>.
                        </p>
                        <form @submit.prevent="confirmarGenerarCuenta()">
                            <div class="form-group">
                                <label class="font-weight-bold small">Concepto / Obligación</label>
                                <input type="text" class="form-control" :value="pagoGenerar.concepto" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Número Factura / Ref</label>
                                    <input type="text" class="form-control" v-model="generarForm.numero_factura" placeholder="Ej. CUOTA-01">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Monto Total a Cobrar <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control text-right font-weight-bold text-primary" v-model.number="generarForm.monto" min="0.01" step="any" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small">Fecha Vencimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" v-model="generarForm.fecha_vencimiento" required>
                            </div>
                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-secondary btn-sm-premium mr-2" @click="cerrarModalGenerar()">Cancelar</button>
                                <button type="submit" class="btn btn-success btn-sm-premium" :disabled="loadingGenerar">
                                    <span v-if="loadingGenerar"><i class="fa fa-spinner fa-spin mr-1"></i> Generando...</span>
                                    <span v-else><i class="fa fa-check mr-1"></i> Confirmar y Avanzar Fecha</span>
                                </button>
                            </div>
                        </form>
                    </div>
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
            arrayPagos: [],
            proveedores: [],
            cuentas: [],
            kpis: {
                total_estimado: 0,
                vencidos_count: 0,
                vencidos_monto: 0,
                por_vencer_count: 0,
                por_vencer_monto: 0,
                activos_count: 0
            },
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1,
                from: 1,
                to: 1
            },
            offset: 3,
            buscar: '',
            filtroCategoria: '',
            filtroFrecuencia: '',
            modalForm: false,
            tipoAccion: 1,
            loadingForm: false,
            form: {
                id: 0,
                concepto: '',
                categoria: 'Préstamo / Crédito',
                proveedor_id: '',
                beneficiario: '',
                monto_estimado: 0,
                frecuencia: 'Mensual',
                proxima_fecha_pago: '',
                recordatorio_dias: 5,
                cuenta_id: '',
                estado: 'Activo',
                observaciones: ''
            },
            modalGenerar: false,
            loadingGenerar: false,
            pagoGenerar: {},
            generarForm: {
                monto: 0,
                numero_factura: '',
                fecha_vencimiento: ''
            }
        };
    },
    computed: {
        paginasCalculadas() {
            if (!this.pagination.to) return [];
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) to = this.pagination.last_page;
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        listar(page) {
            let me = this;
            let url = '/pagos-programados?page=' + page + '&buscar=' + me.buscar + '&categoria=' + me.filtroCategoria + '&frecuencia=' + me.filtroFrecuencia;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.arrayPagos = respuesta.pagos.data;
                me.pagination = respuesta.pagination;
                if (respuesta.kpis) me.kpis = respuesta.kpis;
                if (respuesta.proveedores) me.proveedores = respuesta.proveedores;
                if (respuesta.cuentas) me.cuentas = respuesta.cuentas;
            }).catch(function (error) {
                console.log(error);
            });
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.listar(page);
        },
        limpiarFiltros() {
            this.buscar = '';
            this.filtroCategoria = '';
            this.filtroFrecuencia = '';
            this.listar(1);
        },
        obtenerBadgeCategoria(cat) {
            switch(cat) {
                case 'Préstamo / Crédito': return 'badge-primary';
                case 'Impuesto / Tributario': return 'badge-danger';
                case 'Vehicular (SOAT/Tecno)': return 'badge-warning text-dark';
                case 'Arriendo / Servicios': return 'badge-info';
                case 'Seguros / Licencias': return 'badge-dark';
                default: return 'badge-secondary';
            }
        },
        obtenerClaseVencimiento(fecha) {
            if (!fecha) return 'status-normal';
            let today = new Date();
            today.setHours(0,0,0,0);
            let fVenc = new Date(fecha + 'T00:00:00');
            let diffDays = Math.ceil((fVenc - today) / (1000 * 60 * 60 * 24));

            if (diffDays < 0) return 'status-mora';
            if (diffDays <= 7) return 'status-proximo';
            return 'status-normal';
        },
        obtenerTextoVencimiento(fecha) {
            if (!fecha) return '';
            let today = new Date();
            today.setHours(0,0,0,0);
            let fVenc = new Date(fecha + 'T00:00:00');
            let diffDays = Math.ceil((fVenc - today) / (1000 * 60 * 60 * 24));

            if (diffDays < 0) return '¡VENCIDO! (' + Math.abs(diffDays) + ' días)';
            if (diffDays === 0) return '¡VENCE HOY!';
            if (diffDays === 1) return 'Vence mañana';
            if (diffDays <= 7) return 'Vence en ' + diffDays + ' días';
            return 'En ' + diffDays + ' días';
        },
        abrirModalCrear() {
            this.tipoAccion = 1;
            let todayStr = new Date().toISOString().split('T')[0];
            this.form = {
                id: 0,
                concepto: '',
                categoria: 'Préstamo / Crédito',
                proveedor_id: '',
                beneficiario: '',
                monto_estimado: 0,
                frecuencia: 'Mensual',
                proxima_fecha_pago: todayStr,
                recordatorio_dias: 5,
                cuenta_id: '',
                estado: 'Activo',
                observaciones: ''
            };
            this.modalForm = true;
        },
        abrirModalEditar(data) {
            this.tipoAccion = 2;
            this.form = {
                id: data.id,
                concepto: data.concepto,
                categoria: data.categoria,
                proveedor_id: data.proveedor_id || '',
                beneficiario: data.beneficiario || '',
                monto_estimado: parseFloat(data.monto_estimado),
                frecuencia: data.frecuencia,
                proxima_fecha_pago: data.proxima_fecha_pago,
                recordatorio_dias: data.recordatorio_dias || 5,
                cuenta_id: data.cuenta_id || '',
                estado: data.estado,
                observaciones: data.observaciones || ''
            };
            this.modalForm = true;
        },
        cerrarModalForm() {
            this.modalForm = false;
        },
        guardar() {
            let me = this;
            me.loadingForm = true;
            let url = me.tipoAccion === 1 ? '/pagos-programados/registrar' : ('/pagos-programados/actualizar/' + me.form.id);
            let method = me.tipoAccion === 1 ? 'post' : 'put';

            axios({ method: method, url: url, data: me.form }).then(function (response) {
                me.loadingForm = false;
                me.cerrarModalForm();
                swal('¡Éxito!', response.data.message || 'Operación realizada correctamente.', 'success');
                me.listar(me.pagination.current_page);
            }).catch(function (error) {
                me.loadingForm = false;
                swal('Error', 'No se pudo guardar la información.', 'error');
            });
        },
        toggleEstado(data) {
            let me = this;
            let nuevoEstado = data.estado === 'Activo' ? 'Pausado' : 'Activo';
            axios.put('/pagos-programados/cambiar-estado/' + data.id, { estado: nuevoEstado }).then(function (response) {
                me.listar(me.pagination.current_page);
            });
        },
        eliminar(data) {
            let me = this;
            swal({
                title: '¿Eliminar Pago Programado?',
                text: 'Esta acción no eliminará las Cuentas por Pagar generadas anteriormente.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.delete('/pagos-programados/eliminar/' + data.id).then(function (response) {
                        swal('Eliminado', 'Pago programado eliminado.', 'success');
                        me.listar(me.pagination.current_page);
                    });
                }
            });
        },
        abrirModalGenerarCuenta(data) {
            this.pagoGenerar = data;
            this.generarForm = {
                monto: parseFloat(data.monto_estimado),
                numero_factura: 'PROG-' + data.id + '-' + new Date().toISOString().slice(0,10).replace(/-/g,''),
                fecha_vencimiento: data.proxima_fecha_pago
            };
            this.modalGenerar = true;
        },
        cerrarModalGenerar() {
            this.modalGenerar = false;
        },
        confirmarGenerarCuenta() {
            let me = this;
            me.loadingGenerar = true;
            axios.post('/pagos-programados/generar-cuenta/' + me.pagoGenerar.id, me.generarForm).then(function (response) {
                me.loadingGenerar = false;
                me.cerrarModalGenerar();
                swal('¡Cuenta Generada!', response.data.message, 'success');
                me.listar(me.pagination.current_page);
            }).catch(function (error) {
                me.loadingGenerar = false;
                let msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error al generar cuenta.';
                swal('Error', msg, 'error');
            });
        }
    },
    mounted() {
        this.listar(1);
    }
};
</script>

<style scoped>
.badge-vencimiento {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
}
.status-mora {
    background-color: #fee2e2;
    color: #dc2626;
    border: 1px solid rgba(220, 38, 38, 0.2);
}
.status-proximo {
    background-color: #fef3c7;
    color: #d97706;
    border: 1px solid rgba(217, 119, 6, 0.2);
}
.status-normal {
    background-color: #e0f2fe;
    color: #0284c7;
    border: 1px solid rgba(2, 132, 199, 0.2);
}
</style>
