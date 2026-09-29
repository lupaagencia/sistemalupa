<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fa fa-briefcase mr-2 text-primary"></i>Oportunidades de Negocio</h5>
            <button class="btn btn-primary btn-sm font-weight-bold" @click="abrirModalCrear">
                <i class="fa fa-plus-circle mr-1"></i>Nueva Oportunidad
            </button>
        </div>

        <div class="card-body p-3">
            <!-- Search & Filters -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Buscar por código o nombre..." v-model="buscar" @keyup.enter="listarOportunidades(1)" />
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" v-model="estadoFiltro" @change="listarOportunidades(1)">
                        <option value="">-- Todos los estados --</option>
                        <option value="Abierta">Abierta</option>
                        <option value="Ganada">Ganada</option>
                        <option value="Perdida">Perdida</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Código / Nombre</th>
                            <th>Prospecto / Cliente</th>
                            <th>Monto Estimado</th>
                            <th>Etapa Actual</th>
                            <th>Cierre Est.</th>
                            <th>Estado</th>
                            <th>Vendedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!arrayOportunidades.length">
                            <td colspan="8" class="text-center py-4 text-muted">No hay oportunidades registradas.</td>
                        </tr>
                        <tr v-for="item in arrayOportunidades" :key="item.id">
                            <td>
                                <strong>{{ item.nombre }}</strong>
                                <div class="small text-muted font-weight-bold">{{ item.codigo }}</div>
                            </td>
                            <td>
                                {{ item.cliente ? item.cliente.nombre : (item.prospecto ? item.prospecto.nombre : 'N/A') }}
                                <div v-if="item.prospecto && item.prospecto.empresa" class="small text-muted">{{ item.prospecto.empresa }}</div>
                            </td>
                            <td class="font-weight-bold text-success">
                                ${{ formatMonto(item.monto_estimado) }}
                            </td>
                            <td>
                                <span class="badge text-white" :style="{ backgroundColor: item.etapa ? item.etapa.color : '#64748b' }">
                                    {{ item.etapa ? item.etapa.nombre : 'Sin etapa' }}
                                </span>
                            </td>
                            <td class="small">
                                {{ item.fecha_cierre_estimada || 'Sin fecha' }}
                            </td>
                            <td>
                                <span class="badge" :class="badgeEstado(item.estado)">{{ item.estado }}</span>
                            </td>
                            <td class="small font-weight-semibold text-primary">
                                {{ item.vendedor ? item.vendedor.usuario : 'N/A' }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info text-white mr-1" title="Editar" @click="abrirModalEditar(item)">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger text-white" title="Eliminar" @click="eliminarOportunidad(item.id)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.last_page > 1">
                <span class="small text-muted">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <a class="page-link" href="#" @click.prevent="listarOportunidades(pagination.current_page - 1)">Anterior</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="listarOportunidades(pagination.current_page + 1)">Siguiente</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Oportunidad -->
        <div class="modal fade" id="modalOportunidad" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">{{ modoEditar ? 'Editar Oportunidad' : 'Nueva Oportunidad' }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarOportunidad">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Título del Negocio (*)</label>
                                    <input type="text" v-model="form.nombre" class="form-control" placeholder="Ej. Impresión de 50,000 Cajas" required />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold"><i class="fa fa-user text-primary mr-1"></i> Vendedor Asignado</label>
                                    <select v-model="form.user_id" class="form-control">
                                        <option :value="null">-- Seleccionar Vendedor --</option>
                                        <option v-for="v in arrayVendedores" :key="v.id" :value="v.id">
                                            {{ v.usuario }} ({{ v.idrol }})
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Prospecto / Lead</label>
                                    <select v-model="form.prospecto_id" class="form-control">
                                        <option :value="null">-- Seleccionar prospecto --</option>
                                        <option v-for="p in arraySelectProspectos" :key="p.id" :value="p.id">
                                            {{ p.nombre }} ({{ p.empresa || 'Individual' }})
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-semibold">Monto Estimado ($) (*)</label>
                                    <input type="number" step="0.01" v-model="form.monto_estimado" class="form-control" required />
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-semibold">Etapa Inicial (*)</label>
                                    <select v-model="form.etapa_id" class="form-control" required>
                                        <option v-for="e in arrayEtapas" :key="e.id" :value="e.id">
                                            {{ e.nombre }} ({{ e.probabilidad }}%)
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Fecha Estimada de Cierre</label>
                                    <input type="date" v-model="form.fecha_cierre_estimada" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Estado</label>
                                    <select v-model="form.estado" class="form-control">
                                        <option value="Abierta">Abierta</option>
                                        <option value="Ganada">Ganada</option>
                                        <option value="Perdida">Perdida</option>
                                    </select>
                                </div>
                                <div v-if="form.estado === 'Perdida'" class="col-md-6 form-group">
                                    <label>Motivo de Pérdida</label>
                                    <input type="text" v-model="form.motivo_perdida" class="form-control" placeholder="Ej. Precio alto, competencia..." />
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Observaciones</label>
                                    <textarea v-model="form.observaciones" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary font-weight-bold" :disabled="cargando">
                                {{ cargando ? 'Guardando...' : 'Guardar Oportunidad' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['vendedorId', 'userRole'],
        data() {
            return {
                buscar: '',
                estadoFiltro: '',
                arrayOportunidades: [],
                arraySelectProspectos: [],
                arrayVendedores: [],
                arrayEtapas: [
                    { id: 1, nombre: 'Prospecto / Calificación', probabilidad: 10 },
                    { id: 2, nombre: 'Contactado / Diagnóstico', probabilidad: 25 },
                    { id: 3, nombre: 'Propuesta / Cotización Enviada', probabilidad: 50 },
                    { id: 4, nombre: 'Negociación', probabilidad: 75 },
                    { id: 5, nombre: 'Ganado (Cierre)', probabilidad: 100 },
                    { id: 6, nombre: 'Perdido', probabilidad: 0 }
                ],
                pagination: {},
                modoEditar: false,
                cargando: false,
                form: {
                    id: 0,
                    nombre: '',
                    prospecto_id: null,
                    cliente_id: null,
                    user_id: null,
                    monto_estimado: 0,
                    etapa_id: 1,
                    probabilidad: 50,
                    fecha_cierre_estimada: '',
                    estado: 'Abierta',
                    motivo_perdida: '',
                    observaciones: ''
                }
            };
        },
        watch: {
            vendedorId() {
                this.listarOportunidades(1);
            }
        },
        methods: {
            formatMonto(val) {
                if (!val) return '0.00';
                return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            badgeEstado(est) {
                switch(est) {
                    case 'Abierta': return 'badge-warning';
                    case 'Ganada': return 'badge-success';
                    case 'Perdida': return 'badge-danger';
                    default: return 'badge-secondary';
                }
            },
            listarOportunidades(page = 1) {
                axios.get('/crm/oportunidad', {
                    params: {
                        page: page,
                        buscar: this.buscar,
                        estado: this.estadoFiltro,
                        vendedor_id: this.vendedorId
                    }
                })
                .then(response => {
                    this.arrayOportunidades = response.data.oportunidades.data;
                    this.pagination = response.data.pagination;
                });
            },
            cargarProspectosSelect() {
                axios.get('/crm/prospecto/select', { params: { vendedor_id: this.vendedorId } })
                .then(response => {
                    this.arraySelectProspectos = response.data.prospectos || [];
                });
            },
            cargarVendedoresSelect() {
                axios.get('/crm/vendedores/select')
                .then(response => {
                    this.arrayVendedores = response.data.vendedores || [];
                });
            },
            limpiarForm() {
                this.form = {
                    id: 0,
                    nombre: '',
                    prospecto_id: null,
                    cliente_id: null,
                    user_id: this.vendedorId || null,
                    monto_estimado: 0,
                    etapa_id: 1,
                    probabilidad: 50,
                    fecha_cierre_estimada: '',
                    estado: 'Abierta',
                    motivo_perdida: '',
                    observaciones: ''
                };
            },
            abrirModalCrear() {
                this.modoEditar = false;
                this.limpiarForm();
                this.cargarProspectosSelect();
                this.cargarVendedoresSelect();
                $('#modalOportunidad').modal('show');
            },
            abrirModalEditar(item) {
                this.modoEditar = true;
                this.cargarProspectosSelect();
                this.cargarVendedoresSelect();
                this.form = { ...item };
                if (!this.form.user_id && this.vendedorId) {
                    this.form.user_id = this.vendedorId;
                }
                $('#modalOportunidad').modal('show');
            },
            guardarOportunidad() {
                this.cargando = true;
                const url = this.modoEditar ? '/crm/oportunidad/actualizar' : '/crm/oportunidad/registrar';
                const method = this.modoEditar ? 'put' : 'post';

                axios[method](url, this.form)
                .then(response => {
                    this.cargando = false;
                    $('#modalOportunidad').modal('hide');
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    this.listarOportunidades(1);
                })
                .catch(error => {
                    this.cargando = false;
                    Swal.fire('Error', 'No se pudo guardar la oportunidad', 'error');
                });
            },
            eliminarOportunidad(id) {
                Swal.fire({
                    title: '¿Eliminar oportunidad?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.delete('/crm/oportunidad/eliminar', { data: { id: id } })
                        .then(response => {
                            Swal.fire('Eliminada', response.data.message, 'success');
                            this.listarOportunidades(1);
                        });
                    }
                });
            }
        },
        mounted() {
            this.listarOportunidades(1);
        }
    };
</script>
