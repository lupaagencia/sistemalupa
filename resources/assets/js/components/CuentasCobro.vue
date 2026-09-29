<template>
    <div class="contenedor">
        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div class="">
                <div class="contenedor-header mb-2">
                    <div v-if="listado==1">
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="btn-group shadow-sm w-100" role="group">
                                    <button type="button" class="btn font-weight-bold" :class="tipoDocFiltro === '' ? 'btn-primary' : 'btn-light border'" @click="listarCuentas(1, buscar, criterio, per_page, '')">
                                        💰 Todos los Saldos por Cobrar ({{ cantCuentas + cantProformas }})
                                    </button>
                                    <button type="button" class="btn font-weight-bold" :class="tipoDocFiltro === 'proforma' ? 'btn-warning text-dark' : 'btn-light border'" @click="listarCuentas(1, buscar, criterio, per_page, 'proforma')">
                                        📋 Proformas por Cobrar ({{ cantProformas }})
                                    </button>
                                    <button type="button" class="btn font-weight-bold" :class="tipoDocFiltro === 'cuentacobro' ? 'btn-info text-white' : 'btn-light border'" @click="listarCuentas(1, buscar, criterio, per_page, 'cuentacobro')">
                                        📄 Cuentas de Cobro ({{ cantCuentas }})
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Listado Cuentas de Cobro -->
                <template v-if="listado==1 && tabActiva==='cuentas'">
                    <div class="contenedor-seccion">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                        <option value="cliente_id">Nombre cliente</option>
                                        <option value="fecha">Fecha</option>
                                        <option value="num_comprobante">Número</option>
                                    </select>
                                     <input v-if="criterio=='fecha'" type="date" v-model="buscar" @change="listarCuentas(1,buscar,criterio,per_page)" class="form-control">
                                     <input v-else type="text" v-model="buscar" @input="buscarLive" @keyup.enter="listarCuentas(1,buscar,criterio,per_page)" class="form-control" placeholder="Escribe para buscar cliente o número...">
                                </div>
                            </div>
                        </div>
                        <nav>
                            <ul class="pagination">
                                <li>
                                    <select class="custom-select mr-sm-2" v-model="per_page" @change="listarCuentas(1,buscar,criterio,per_page)">
                                        <option value="10">10</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="500">500</option>
                                    </select>
                                </li>
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="(page,index) in pagesNumber" :key="index" :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right">IVA</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Abonos</th>
                                        <th class="text-right">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayCuentas.length>0">
                                    <tr v-for="cuenta in arrayCuentas" :key="cuenta.id" :class="{'table-success-light': esValida(cuenta)}">
                                        <td class="align-middle">
                                            <button type="button" @click="editarCuenta(cuenta)" class="btn btn-info btn-sm" title="Ver / Editar">
                                                <i class="icon-pencil"></i>
                                            </button>
                                            <button type="button" @click="imprimirCuenta(cuenta)" class="btn btn-warning btn-sm ml-1" title="Imprimir Cuenta de Cobro">
                                                <i class="fa fa-print"></i>
                                            </button>
                                            <button v-if="parseFloat(cuenta.saldo) > 0" type="button" @click="abrirModalAbono(cuenta)" class="btn btn-success btn-sm ml-1" title="Registrar Pago / Abono">
                                                <i class="fa fa-dollar"></i> Abonar
                                            </button>
                                            <button type="button" @click="cruzarAbonosPedido(cuenta)" class="btn btn-secondary btn-sm ml-1" title="Cruzar Abonos del Pedido">
                                                <i class="fa fa-exchange"></i> Cruzar
                                            </button>
                                            <button v-if="!esValida(cuenta)" type="button" @click="toggleEstado(cuenta, 'Valida')" class="btn btn-outline-success btn-sm font-weight-bold ml-1" title="Marcar como Válida">
                                                <i class="fa fa-check"></i> Válida
                                            </button>
                                            <button v-else type="button" @click="toggleEstado(cuenta, 'Invalida')" class="btn btn-outline-warning btn-sm font-weight-bold ml-1" title="Marcar como Inválida">
                                                <i class="fa fa-ban"></i> Inválida
                                            </button>
                                            <template v-if="user.idrol=='Administrador'">
                                                <button type="button" class="btn btn-danger btn-sm ml-1" @click="borrarCuenta(cuenta.id)" title="Eliminar">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </template>
                                        </td>
                                        <td class="align-middle font-weight-bold">
                                            <div v-if="cuenta.tipo === 'proforma'">
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mb-1" style="font-size: 0.85rem;">
                                                    📋 Proforma #{{ cuenta.num_comprobante || cuenta.id }}
                                                </span>
                                            </div>
                                            <div v-else>
                                                <span class="badge badge-info text-white font-weight-bold px-2 py-1 mb-1" style="font-size: 0.85rem;">
                                                    📄 CC #{{ cuenta.num_comprobante || cuenta.id }}
                                                </span>
                                            </div>
                                            <div v-if="cuenta.pedidos_texto" class="small text-primary font-weight-bold font-italic mt-1" style="font-size:0.78rem;">
                                                <i class="fa fa-shopping-cart mr-1"></i>{{ cuenta.pedidos_texto }}
                                            </div>
                                            <div v-else-if="cuenta.pedido_id_padre" class="small text-primary font-weight-bold font-italic mt-1" style="font-size:0.78rem;">
                                                <i class="fa fa-shopping-cart mr-1"></i>Pedido #{{ cuenta.pedido_id_padre }}
                                            </div>
                                            <div v-if="cuenta.remisiones_texto" class="small text-info font-weight-bold font-italic mt-1" style="font-size:0.78rem;">
                                                <i class="fa fa-truck mr-1"></i>{{ cuenta.remisiones_texto }}
                                            </div>
                                        </td>
                                        <td class="align-middle" v-text="cuenta.fecha"></td>
                                        <td class="align-middle">{{ cuenta.cliente ? cuenta.cliente.razonsocial : (cuenta.razonsocial ? cuenta.razonsocial.razonsocial : 'N/A') }}</td>
                                        <td class="align-middle text-center">
                                            <span v-if="parseFloat(cuenta.saldo) <= 0" class="badge badge-primary px-2 py-1">
                                                <i class="fa fa-check-circle mr-1"></i> Pagada
                                            </span>
                                            <span v-else-if="esValida(cuenta)" class="badge badge-success px-2 py-1">
                                                <i class="fa fa-check-circle mr-1"></i> Válida
                                            </span>
                                            <span v-else class="badge badge-secondary px-2 py-1">
                                                <i class="fa fa-times-circle mr-1"></i> Inválida
                                            </span>
                                        </td>
                                        <td class="align-middle text-right">${{ forNum(cuenta.subtotal) }}</td>
                                        <td class="align-middle text-right">${{ forNum(cuenta.impuestos) }}</td>
                                        <td class="align-middle text-right font-weight-bold text-primary">${{ forNum(cuenta.total) }}</td>
                                        <td class="align-middle text-right font-weight-bold text-success">${{ forNum(cuenta.abono) }}</td>
                                        <td class="align-middle text-right font-weight-bold text-danger">${{ forNum(cuenta.saldo) }}</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">No hay cuentas de cobro registradas</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Resumen y Tabla de Proformas -->
                <template v-if="listado==1 && tabActiva==='proformas'">
                    <div class="contenedor-seccion">
                        <!-- KPI Cards -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white p-3 shadow-sm rounded-lg">
                                    <span class="small font-weight-bold opacity-80">TOTAL PROFORMAS</span>
                                    <h4 class="font-weight-bold mb-0">${{ forNum(resumenProformasData.total_proformas) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white p-3 shadow-sm rounded-lg">
                                    <span class="small font-weight-bold opacity-80">NO ENVIADAS (Pendientes)</span>
                                    <h4 class="font-weight-bold mb-0">${{ forNum(resumenProformasData.total_no_enviadas) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white p-3 shadow-sm rounded-lg">
                                    <span class="small font-weight-bold opacity-80">ENVIADAS (Entregadas)</span>
                                    <h4 class="font-weight-bold mb-0">${{ forNum(resumenProformasData.total_enviadas) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white p-3 shadow-sm rounded-lg">
                                    <span class="small font-weight-bold opacity-80">SALDO POR COBRAR</span>
                                    <h4 class="font-weight-bold mb-0">${{ forNum(resumenProformasData.saldo_total_proformas) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros Estado Proformas -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group">
                                <button class="btn btn-sm" :class="filtroProformaEstado==='' ? 'btn-primary' : 'btn-outline-secondary'" @click="filtroProformaEstado=''; listarProformas(1);">Todas</button>
                                <button class="btn btn-sm" :class="filtroProformaEstado==='0' ? 'btn-warning text-white font-weight-bold' : 'btn-outline-secondary'" @click="filtroProformaEstado='0'; listarProformas(1);">No Enviada</button>
                                <button class="btn btn-sm" :class="filtroProformaEstado==='1' ? 'btn-success font-weight-bold' : 'btn-outline-secondary'" @click="filtroProformaEstado='1'; listarProformas(1);">Enviada</button>
                            </div>
                        </div>

                        <!-- Tabla Proformas -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Opciones</th>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Estado Proforma</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Abonos</th>
                                        <th class="text-right">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayProformas.length>0">
                                    <tr v-for="prof in arrayProformas" :key="prof.id">
                                        <td class="align-middle">
                                            <button type="button" @click="imprimirProforma(prof)" class="btn btn-warning btn-sm" title="Imprimir PDF Proforma">
                                                <i class="fa fa-print"></i> PDF
                                            </button>
                                            <button v-if="parseFloat(prof.saldo) > 0" type="button" @click="abrirModalAbono(prof)" class="btn btn-success btn-sm ml-1" title="Registrar Abono">
                                                <i class="fa fa-dollar"></i> Abonar
                                            </button>
                                        </td>
                                        <td class="font-weight-bold align-middle">#{{ prof.num_comprobante }}</td>
                                        <td class="align-middle">{{ prof.fecha }}</td>
                                        <td class="align-middle">{{ prof.cliente ? (prof.cliente.razonsocial || prof.cliente.nombre) : 'N/A' }}</td>
                                        <td class="align-middle text-center">
                                            <span v-if="prof.estado==0" class="badge badge-warning text-white p-2">📋 Proforma No Enviada</span>
                                            <span v-else class="badge badge-success p-2">📋 Proforma Enviada</span>
                                        </td>
                                        <td class="align-middle text-right font-weight-bold">${{ forNum(prof.total) }}</td>
                                        <td class="align-middle text-right font-weight-bold text-success">${{ forNum(prof.abono) }}</td>
                                        <td class="align-middle text-right font-weight-bold" :class="parseFloat(prof.saldo) > 0 ? 'text-danger' : 'text-muted'">${{ forNum(prof.saldo) }}</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No hay proformas registradas con los filtros seleccionados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
                <template v-else-if="listado==0">
                    <vista-factura :factura="cuentaSeleccionada" :edit="editMode" :user="user" @regresar="listado=1; listarCuentas(1, buscar, criterio, per_page)"></vista-factura>
                </template>
            </div>
        </div>

        <!-- Modal Registrar Abono a Cuenta de Cobro -->
        <div class="modal fade" :class="{'show d-block': modalAbono}" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);" v-if="modalAbono">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content border-0 shadow-lg rounded-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-dollar mr-1"></i> Registrar Abono a Cuenta de Cobro #{{ cuentaAbonar ? (cuentaAbonar.num_comprobante || cuentaAbonar.id) : '' }}
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalAbono">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" v-if="cuentaAbonar">
                        <div class="card bg-light border-0 mb-3 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Cliente:</span>
                                    <span class="font-weight-bold">{{ cuentaAbonar.cliente ? cuentaAbonar.cliente.razonsocial : 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Total CC:</span>
                                    <span class="font-weight-bold text-primary">${{ forNum(cuentaAbonar.total) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Abonos Previos:</span>
                                    <span class="font-weight-bold text-success">${{ forNum(cuentaAbonar.abono) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Saldo Pendiente:</span>
                                    <span class="font-weight-bold text-danger">${{ forNum(cuentaAbonar.saldo) }}</span>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="guardarAbonoCC">
                            <div class="form-group">
                                <label class="font-weight-bold">Monto a Abonar ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" max="1000000000" v-model="formAbono.monto" class="form-control form-control-lg font-weight-bold text-success" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha del Pago <span class="text-danger">*</span></label>
                                <input type="date" v-model="formAbono.fecha" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Forma de Pago <span class="text-danger">*</span></label>
                                <select v-model="formAbono.forma_pago" class="form-control" required>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia Bancaria</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Consignación">Consignación</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Observaciones / Notas</label>
                                <textarea v-model="formAbono.observaciones" class="form-control" rows="2" placeholder="Detalles adicionales del abono..."></textarea>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalAbono">Cancelar</button>
                                <button type="submit" class="btn btn-success font-weight-bold px-4" :disabled="loadingAbono">
                                    <i class="fa fa-check mr-1"></i> {{ loadingAbono ? 'Guardando...' : 'Registrar Abono' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VistaFactura from './partes/VistaFactura'
    export default {
        props: ['user'],
        components: {
            VistaFactura
        },
        data() {
            return {
                listado: 1,
                arrayCuentas: [],
                pagination: {
                    'total': 0,
                    'current_page': 0,
                    'per_page': 0,
                    'last_page': 0,
                    'from': 0,
                    'to': 0,
                },
                offset: 3,
                criterio: 'cliente_id',
                buscar: '',
                per_page: 100,
                cuentaSeleccionada: null,
                editMode: 0,
                modalAbono: 0,
                cuentaAbonar: null,
                loadingAbono: false,
                tabActiva: 'cuentas',
                arrayProformas: [],
                resumenProformasData: {
                    total_proformas: 0,
                    total_no_enviadas: 0,
                    total_enviadas: 0,
                    saldo_total_proformas: 0
                },
                filtroProformaEstado: '',
                tipoDocFiltro: '',
                cantCuentas: 0,
                cantProformas: 0,
                formAbono: {
                    cuentacobro_id: 0,
                    monto: 0,
                    fecha: '',
                    forma_pago: 'Efectivo',
                    observaciones: ''
                }
            }
        },
        computed: {
            isActived: function() {
                return this.pagination.current_page;
            },
            pagesNumber: function() {
                if (!this.pagination.to) {
                    return [];
                }
                var from = this.pagination.current_page - this.offset;
                if (from < 1) {
                    from = 1;
                }
                var to = from + (this.offset * 2);
                if (to >= this.pagination.last_page) {
                    to = this.pagination.last_page;
                }
                var pagesArray = [];
                while (from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;
            }
        },
        methods: {
            esValida(cuenta) {
                return ['Valida', 'Válida', 'Cerrado', '1'].includes(String(cuenta.estado || ''));
            },
            forNum(value) {
                if (!value) return '0';
                return parseFloat(value).toLocaleString('es-CO');
            },
            abrirModalAbono(cuenta) {
                this.cuentaAbonar = cuenta;
                this.formAbono = {
                    cuentacobro_id: cuenta.id,
                    monto: parseFloat(cuenta.saldo) || 0,
                    fecha: new Date().toISOString().slice(0, 10),
                    forma_pago: 'Efectivo',
                    observaciones: ''
                };
                this.modalAbono = 1;
            },
            cerrarModalAbono() {
                this.modalAbono = 0;
                this.cuentaAbonar = null;
                this.loadingAbono = false;
            },
            guardarAbonoCC() {
                let me = this;
                if (!me.formAbono.monto || me.formAbono.monto <= 0) {
                    Swal.fire('Error', 'Ingrese un monto válido mayor a cero.', 'error');
                    return;
                }
                me.loadingAbono = true;
                axios.post('/comprobante/registrarAbonoCuentaCobro', me.formAbono).then(function(response) {
                    me.loadingAbono = false;
                    Swal.fire('Éxito', response.data.message, 'success');
                    me.cerrarModalAbono();
                    me.listarCuentas(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                }).catch(function(error) {
                    me.loadingAbono = false;
                    let msg = error.response && error.response.data && error.response.data.message ? error.response.data.message : 'Error al registrar abono.';
                    Swal.fire('Error', msg, 'error');
                });
            },
            cruzarAbonosPedido(cuenta) {
                let me = this;
                Swal.fire({
                    title: '¿Cruzar abonos del pedido?',
                    text: 'Se buscarán los abonos o anticipos del pedido padre y se cruzarán con esta Cuenta de Cobro.',
                    type: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cruzar abonos',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.post('/comprobante/cruzarAbonosCuentaCobro', { cuentacobro_id: cuenta.id }).then(function(response) {
                            Swal.fire('Éxito', response.data.message, 'success');
                            me.listarCuentas(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                        }).catch(function(error) {
                            let msg = error.response && error.response.data && error.response.data.message ? error.response.data.message : 'Error al cruzar abonos.';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            },
            toggleEstado(cuenta, nuevoEstado) {
                let me = this;
                let texto = nuevoEstado === 'Valida' 
                    ? 'La Cuenta de Cobro pasará a ser VÁLIDA y se sumará en Cuentas por Cobrar.' 
                    : 'La Cuenta de Cobro pasará a ser INVÁLIDA y NO se sumará en Cuentas por Cobrar.';

                Swal.fire({
                    title: '¿Cambiar estado de Cuenta de Cobro?',
                    text: texto,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.post('/comprobante/cambiarEstadoCuentaCobro', {
                            id: cuenta.id,
                            estado: nuevoEstado
                        }).then(function(response) {
                            Swal.fire('Actualizado', response.data.message, 'success');
                            cuenta.estado = nuevoEstado;
                        }).catch(function(error) {
                            let msg = error.response && error.response.data && error.response.data.message ? error.response.data.message : 'Error al cambiar estado.';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            },
            buscarLive() {
                clearTimeout(this._searchTimer);
                let me = this;
                this._searchTimer = setTimeout(() => {
                    me.listarCuentas(1, me.buscar, me.criterio, me.per_page);
                }, 250);
            },
            listarCuentas(page, buscar, criterio, per_page, tipo_doc) {
                let me = this;
                if (tipo_doc !== undefined) {
                    me.tipoDocFiltro = tipo_doc;
                }
                var pBuscar = (buscar !== undefined) ? buscar : me.buscar;
                if (pBuscar === 0 || pBuscar === '0') {
                    pBuscar = '';
                }
                var pCriterio = (criterio !== undefined) ? criterio : me.criterio;
                var pPerPage = (per_page !== undefined) ? per_page : me.per_page;

                var url = '/comprobante/cuentasCobro?page=' + page + '&per_page=' + pPerPage + '&buscar=' + encodeURIComponent(pBuscar) + '&criterio=' + pCriterio + '&tipo_doc=' + me.tipoDocFiltro;
                axios.get(url).then(function(response) {
                    var respuesta = response.data;
                    me.arrayCuentas = respuesta.comprobantes.data;
                    me.pagination = respuesta.pagination;
                    me.cantCuentas = respuesta.cant_cuentas || 0;
                    me.cantProformas = respuesta.cant_proformas || 0;
                }).catch(function(error) {
                    console.log(error);
                });
            },
            cambiarPagina(page, buscar, criterio) {
                this.pagination.current_page = page;
                this.listarCuentas(page, buscar, criterio, this.per_page);
            },
            editarCuenta(cuenta) {
                this.cuentaSeleccionada = cuenta;
                this.editMode = 1;
                this.listado = 0;
            },
            imprimirCuenta(cuenta) {
                var url = '/imprimirCuentaCobro?id=' + cuenta.id;
                window.open(url, '_blank');
            },
            borrarCuenta(id) {
                let me = this;
                Swal.fire({
                    title: '¿Está seguro de eliminar esta cuenta de cobro?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.delete('/comprobante/borrar?id=' + id + '&user_id=' + me.user.id).then(function(response) {
                            me.listarCuentas(1, me.buscar, me.criterio, me.per_page);
                        }).catch(function(error) {
                            console.log(error);
                        });
                    }
                });
            },
            listarProformas(page = 1) {
                let me = this;
                var url = '/comprobante/resumen-proformas?page=' + page + '&per_page=' + me.per_page + '&buscar=' + me.filtroProformaEstado + '&criterio=estado';
                axios.get(url).then(function(response) {
                    let d = response.data;
                    me.arrayProformas = d.comprobantes.data || [];
                    me.resumenProformasData = {
                        total_proformas: d.total_proformas || 0,
                        total_no_enviadas: d.total_no_enviadas || 0,
                        total_enviadas: d.total_enviadas || 0,
                        saldo_total_proformas: d.saldo_total_proformas || 0
                    };
                }).catch(function(error) {
                    console.log(error);
                });
            },
            imprimirProforma(proforma) {
                var url = '/imprimirProforma?id=' + proforma.id;
                window.open(url, '_blank');
            }
        },
        mounted() {
            this.listarCuentas(1, this.buscar, this.criterio, this.per_page);
            this.listarProformas(1);
        }
    }
</script>
