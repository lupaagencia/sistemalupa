<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fa fa-address-book mr-2 text-primary"></i>Gestión de Prospectos (Leads)</h5>
            <button class="btn btn-primary btn-sm font-weight-bold" @click="abrirModalCrear">
                <i class="fa fa-plus-circle mr-1"></i>Nuevo Prospecto
            </button>
        </div>

        <div class="card-body p-3">
            <!-- Search & Filter bar -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Buscar prospecto..." v-model="buscar" @keyup.enter="listarProspectos(1)" />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" @click="listarProspectos(1)"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" v-model="estadoFiltro" @change="listarProspectos(1)">
                        <option value="">-- Todos los estados --</option>
                        <option value="Nuevo">Nuevo</option>
                        <option value="Contactado">Contactado</option>
                        <option value="Calificado">Calificado</option>
                        <option value="Convertido">Convertido</option>
                        <option value="Descartado">Descartado</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Nombre / Empresa</th>
                            <th>Contacto</th>
                            <th>Origen</th>
                            <th>Estado</th>
                            <th>Vendedor Asignado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!arrayProspectos.length">
                            <td colspan="6" class="text-center py-4 text-muted">No se encontraron prospectos registados.</td>
                        </tr>
                        <tr v-for="item in arrayProspectos" :key="item.id">
                            <td>
                                <strong>{{ item.nombre }}</strong>
                                <div v-if="item.empresa" class="small text-muted"><i class="fa fa-building-o mr-1"></i>{{ item.empresa }} ({{ item.cargo || 'Contacto' }})</div>
                            </td>
                            <td>
                                <div v-if="item.telefono || item.celular" class="small"><i class="fa fa-phone mr-1"></i>{{ item.telefono || item.celular }}</div>
                                <div v-if="item.email" class="small text-muted"><i class="fa fa-envelope-o mr-1"></i>{{ item.email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ item.origen }}</span>
                            </td>
                            <td>
                                <span class="badge" :class="badgeEstado(item.estado)">{{ item.estado }}</span>
                            </td>
                            <td class="small">
                                {{ item.vendedor ? item.vendedor.usuario : 'N/A' }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info text-white mr-1" title="Editar" @click="abrirModalEditar(item)">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button v-if="item.estado !== 'Convertido'" class="btn btn-sm btn-success text-white mr-1" title="Convertir a Cliente" @click="convertirACliente(item)">
                                    <i class="fa fa-user-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-danger text-white" title="Eliminar" @click="eliminarProspecto(item.id)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.last_page > 1">
                <span class="small text-muted">Mostrando página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <a class="page-link" href="#" @click.prevent="listarProspectos(pagination.current_page - 1)">Anterior</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="listarProspectos(pagination.current_page + 1)">Siguiente</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Prospecto Form -->
        <div class="modal fade" id="modalProspecto" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">{{ modoEditar ? 'Editar Prospecto' : 'Nuevo Prospecto' }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarProspecto">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Nombre Completo (*)</label>
                                    <input type="text" v-model="form.nombre" class="form-control" required />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Empresa</label>
                                    <input type="text" v-model="form.empresa" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Correo Electrónico</label>
                                    <input type="email" v-model="form.email" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Teléfono / Celular</label>
                                    <input type="text" v-model="form.telefono" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Origen del Lead</label>
                                    <select v-model="form.origen" class="form-control">
                                        <option value="Web">Web</option>
                                        <option value="Referido">Referido</option>
                                        <option value="Redes">Redes Sociales</option>
                                        <option value="Llamada">Llamada en Frío</option>
                                        <option value="Evento">Evento / Feria</option>
                                        <option value="Directo">Directo</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Estado</label>
                                    <select v-model="form.estado" class="form-control">
                                        <option value="Nuevo">Nuevo</option>
                                        <option value="Contactado">Contactado</option>
                                        <option value="Calificado">Calificado</option>
                                        <option value="Convertido">Convertido</option>
                                        <option value="Descartado">Descartado</option>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Dirección / Ciudad</label>
                                    <input type="text" v-model="form.direccion" class="form-control" placeholder="Dirección y ciudad" />
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
                                {{ cargando ? 'Guardando...' : 'Guardar Prospecto' }}
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
                arrayProspectos: [],
                pagination: {},
                modoEditar: false,
                cargando: false,
                form: {
                    id: 0,
                    nombre: '',
                    empresa: '',
                    cargo: '',
                    email: '',
                    telefono: '',
                    celular: '',
                    direccion: '',
                    ciudad: '',
                    origen: 'Web',
                    estado: 'Nuevo',
                    observaciones: ''
                }
            };
        },
        watch: {
            vendedorId() {
                this.listarProspectos(1);
            }
        },
        methods: {
            badgeEstado(est) {
                switch(est) {
                    case 'Nuevo': return 'badge-info';
                    case 'Contactado': return 'badge-primary';
                    case 'Calificado': return 'badge-warning';
                    case 'Convertido': return 'badge-success';
                    default: return 'badge-secondary';
                }
            },
            listarProspectos(page = 1) {
                axios.get('/crm/prospecto', {
                    params: {
                        page: page,
                        buscar: this.buscar,
                        estado: this.estadoFiltro,
                        vendedor_id: this.vendedorId
                    }
                })
                .then(response => {
                    this.arrayProspectos = response.data.prospectos.data;
                    this.pagination = response.data.pagination;
                })
                .catch(error => {
                    console.error('Error al listar prospectos:', error);
                });
            },
            limpiarForm() {
                this.form = {
                    id: 0,
                    nombre: '',
                    empresa: '',
                    cargo: '',
                    email: '',
                    telefono: '',
                    celular: '',
                    direccion: '',
                    ciudad: '',
                    origen: 'Web',
                    estado: 'Nuevo',
                    observaciones: ''
                };
            },
            abrirModalCrear() {
                this.modoEditar = false;
                this.limpiarForm();
                $('#modalProspecto').modal('show');
            },
            abrirModalEditar(item) {
                this.modoEditar = true;
                this.form = { ...item };
                $('#modalProspecto').modal('show');
            },
            guardarProspecto() {
                this.cargando = true;
                const url = this.modoEditar ? '/crm/prospecto/actualizar' : '/crm/prospecto/registrar';
                const method = this.modoEditar ? 'put' : 'post';

                axios[method](url, this.form)
                .then(response => {
                    this.cargando = false;
                    $('#modalProspecto').modal('hide');
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    this.listarProspectos(1);
                })
                .catch(error => {
                    this.cargando = false;
                    Swal.fire('Error', 'No se pudo guardar el prospecto', 'error');
                });
            },
            convertirACliente(item) {
                Swal.fire({
                    title: '¿Convertir a Cliente?',
                    text: `Se creará el cliente "${item.nombre}" en la base de datos general de clientes.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, convertir',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.post('/crm/prospecto/convertir-cliente', { id: item.id })
                        .then(response => {
                            Swal.fire('¡Convertido!', response.data.message, 'success');
                            this.listarProspectos(1);
                        })
                        .catch(error => {
                            Swal.fire('Error', 'No se pudo convertir el prospecto', 'error');
                        });
                    }
                });
            },
            eliminarProspecto(id) {
                Swal.fire({
                    title: '¿Eliminar prospecto?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.delete('/crm/prospecto/eliminar', { data: { id: id } })
                        .then(response => {
                            Swal.fire('Eliminado', response.data.message, 'success');
                            this.listarProspectos(1);
                        });
                    }
                });
            }
        },
        mounted() {
            this.listarProspectos(1);
        }
    };
</script>
