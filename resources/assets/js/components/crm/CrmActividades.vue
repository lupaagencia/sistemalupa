<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar-check-o mr-2 text-primary"></i>Agenda y Seguimiento Comercial</h5>
            <button class="btn btn-primary btn-sm font-weight-bold" @click="abrirModalCrear">
                <i class="fa fa-plus-circle mr-1"></i>Nueva Tarea / Actividad
            </button>
        </div>

        <div class="card-body p-3">
            <!-- Filter Bar -->
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" v-model="filtroCompletada" @change="listarActividades(1)">
                        <option value="">-- Estado de Tareas --</option>
                        <option value="false">Pendientes</option>
                        <option value="true">Completadas</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" v-model="filtroTipo" @change="listarActividades(1)">
                        <option value="">-- Todos los tipos --</option>
                        <option value="Llamada">Llamada</option>
                        <option value="Reunión">Reunión</option>
                        <option value="Correo">Correo</option>
                        <option value="Tarea">Tarea</option>
                        <option value="Nota">Nota</option>
                    </select>
                </div>
            </div>

            <!-- List -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Tipo / Asunto</th>
                            <th>Relacionado con</th>
                            <th>Fecha Vencimiento</th>
                            <th>Vendedor</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!arrayActividades.length">
                            <td colspan="7" class="text-center py-4 text-muted">No hay actividades de seguimiento pendientes.</td>
                        </tr>
                        <tr v-for="item in arrayActividades" :key="item.id" :class="{ 'bg-light text-muted': item.completada }">
                            <td class="text-center">
                                <input type="checkbox" :checked="item.completada" @change="marcarCompletada(item)" style="transform: scale(1.2); cursor: pointer;" />
                            </td>
                            <td>
                                <strong>
                                    <i :class="iconTipo(item.tipo)" class="mr-1 text-primary"></i>
                                    {{ item.asunto }}
                                </strong>
                                <div v-if="item.descripcion" class="small text-muted">{{ item.descripcion }}</div>
                            </td>
                            <td class="small">
                                <div v-if="item.prospecto"><i class="fa fa-user-o mr-1"></i>{{ item.prospecto.nombre }} (Prospecto)</div>
                                <div v-if="item.oportunidad"><i class="fa fa-briefcase mr-1"></i>{{ item.oportunidad.nombre }}</div>
                                <div v-if="item.cotizacion"><i class="fa fa-file-text-o mr-1"></i>{{ item.cotizacion.numero_cotizacion }}</div>
                            </td>
                            <td class="small font-weight-bold">
                                {{ item.fecha_vencimiento || 'Sin fecha' }}
                            </td>
                            <td class="small">
                                {{ item.vendedor ? item.vendedor.usuario : 'N/A' }}
                            </td>
                            <td>
                                <span class="badge" :class="item.completada ? 'badge-success' : 'badge-warning'">
                                    {{ item.completada ? 'Completada' : 'Pendiente' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger border-0" title="Eliminar" @click="eliminarActividad(item.id)">
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
                        <a class="page-link" href="#" @click.prevent="listarActividades(pagination.current_page - 1)">Anterior</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="listarActividades(pagination.current_page + 1)">Siguiente</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Actividad -->
        <div class="modal fade" id="modalActividad" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">Nueva Actividad de Seguimiento</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarActividad">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-semibold">Tipo de Actividad (*)</label>
                                <select v-model="form.tipo" class="form-control" required>
                                    <option value="Llamada">Llamada Telefónica</option>
                                    <option value="Reunión">Reunión / Cita</option>
                                    <option value="Correo">Envío de Correo</option>
                                    <option value="Tarea">Tarea Comercial</option>
                                    <option value="Nota">Nota / Recordatorio</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-semibold">Asunto / Título (*)</label>
                                <input type="text" v-model="form.asunto" class="form-control" placeholder="Ej. Llamada de seguimiento a cotización" required />
                            </div>
                            <div class="form-group mb-3">
                                <label>Prospecto / Lead Asociado</label>
                                <select v-model="form.prospecto_id" class="form-control">
                                    <option :value="null">-- Ninguno --</option>
                                    <option v-for="p in arrayProspectos" :key="p.id" :value="p.id">
                                        {{ p.nombre }} ({{ p.empresa || 'Individual' }})
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Fecha y Hora de Vencimiento (*)</label>
                                <input type="datetime-local" v-model="form.fecha_vencimiento" class="form-control" required />
                            </div>
                            <div class="form-group mb-3">
                                <label>Descripción / Comentarios</label>
                                <textarea v-model="form.descripcion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary font-weight-bold" :disabled="cargando">
                                {{ cargando ? 'Guardando...' : 'Guardar Actividad' }}
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
                filtroCompletada: 'false',
                filtroTipo: '',
                arrayActividades: [],
                arrayProspectos: [],
                pagination: {},
                cargando: false,
                form: {
                    tipo: 'Llamada',
                    asunto: '',
                    descripcion: '',
                    prospecto_id: null,
                    oportunidad_id: null,
                    cotizacion_id: null,
                    fecha_vencimiento: ''
                }
            };
        },
        watch: {
            vendedorId() {
                this.listarActividades(1);
            }
        },
        methods: {
            iconTipo(t) {
                switch(t) {
                    case 'Llamada': return 'fa fa-phone';
                    case 'Reunión': return 'fa fa-users';
                    case 'Correo': return 'fa fa-envelope';
                    case 'Tarea': return 'fa fa-check-square-o';
                    default: return 'fa fa-sticky-note-o';
                }
            },
            listarActividades(page = 1) {
                axios.get('/crm/actividad', {
                    params: {
                        page: page,
                        completada: this.filtroCompletada,
                        tipo: this.filtroTipo,
                        vendedor_id: this.vendedorId
                    }
                })
                .then(response => {
                    this.arrayActividades = response.data.actividades.data;
                    this.pagination = response.data.pagination;
                });
            },
            cargarProspectosSelect() {
                axios.get('/crm/prospecto/select', { params: { vendedor_id: this.vendedorId } })
                .then(response => {
                    this.arrayProspectos = response.data.prospectos || [];
                });
            },
            limpiarForm() {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                const nowIso = now.toISOString().slice(0, 16);

                this.form = {
                    tipo: 'Llamada',
                    asunto: '',
                    descripcion: '',
                    prospecto_id: null,
                    oportunidad_id: null,
                    cotizacion_id: null,
                    fecha_vencimiento: nowIso
                };
            },
            abrirModalCrear() {
                this.limpiarForm();
                this.cargarProspectosSelect();
                $('#modalActividad').modal('show');
            },
            guardarActividad() {
                this.cargando = true;
                axios.post('/crm/actividad/registrar', this.form)
                .then(response => {
                    this.cargando = false;
                    $('#modalActividad').modal('hide');
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    this.listarActividades(1);
                })
                .catch(error => {
                    this.cargando = false;
                    Swal.fire('Error', 'No se pudo registrar la actividad', 'error');
                });
            },
            marcarCompletada(item) {
                axios.put('/crm/actividad/completar', { id: item.id })
                .then(response => {
                    this.listarActividades(1);
                });
            },
            eliminarActividad(id) {
                axios.delete('/crm/actividad/eliminar', { data: { id: id } })
                .then(response => {
                    this.listarActividades(1);
                });
            }
        },
        mounted() {
            this.listarActividades(1);
        }
    };
</script>
