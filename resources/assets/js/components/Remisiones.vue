<template>
    <div class="contenedor">
        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div class="">
                <div class="contenedor-header d-flex justify-content-between align-items-center">
                    <div v-if="listado==1">
                        <i class="fa fa-truck"></i> Remisiones de Entrega
                    </div>
                    <div v-if="listado==1 && selectedRemisiones.length > 0">
                        <button type="button" @click="crearCuentaCobro" class="btn btn-success font-weight-bold shadow-sm">
                            <i class="fa fa-file-text-o mr-1"></i> Crear Cuenta de Cobro ({{ selectedRemisiones.length }})
                        </button>
                    </div>
                </div>
                <!-- Listado-->
                <template v-if="listado==1">
                    <div class="contenedor-seccion">
                        <div class="form-group row align-items-center">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="input-group">
                                    <select class="form-control col-md-4" v-model="criterio">
                                        <option value="cliente_id">Nombre cliente</option>
                                        <option value="fecha">Fecha</option>
                                        <option value="num_comprobante">Número</option>
                                    </select>
                                    <input v-if="criterio=='fecha'" type="date" v-model="buscar" @keyup.enter="listarRemisiones(1,buscar,criterio,per_page)" class="form-control">
                                    <input v-else type="text" v-model="buscar" @keyup.enter="listarRemisiones(1,buscar,criterio,per_page)" class="form-control" placeholder="Texto a buscar...">
                                </div>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <button type="button" @click="crearCuentaCobro" :disabled="selectedRemisiones.length === 0" class="btn btn-primary btn-round px-3">
                                    <i class="fa fa-plus-circle mr-1"></i> Generar Cuenta de Cobro
                                </button>
                            </div>
                        </div>
                        <nav>
                            <ul class="pagination">
                                <li>
                                    <select class="custom-select mr-sm-2" v-model="per_page" @change="listarRemisiones(1,buscar,criterio,per_page)">
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
                            <table class="table table-bordered table-striped table-hover table-sm align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                                        </th>
                                        <th>Opciones</th>
                                        <th>Número Remisión</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th class="text-right">Valor Total</th>
                                        <th>Estado Cuenta de Cobro</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayRemisiones.length>0">
                                    <tr v-for="remision in arrayRemisiones" :key="remision.id" :class="{'table-success-light': remision.cuentacobro_id}">
                                        <td class="text-center align-middle">
                                            <input type="checkbox" :value="remision.id" v-model="selectedRemisiones" :disabled="!!remision.cuentacobro_id">
                                        </td>
                                        <td class="align-middle">
                                            <button type="button" @click="editarRemision(remision)" class="btn btn-info btn-sm" title="Editar Remisión">
                                                <i class="icon-pencil"></i>
                                            </button>
                                            <template v-if="user && user.idrol=='Administrador'">
                                                <button type="button" class="btn btn-danger btn-sm" @click="borrarRemision(remision.id)" title="Eliminar Remisión">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </template>
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark">Remisión #{{ remision.num_comprobante || remision.id }}</div>
                                            <div v-if="remision.pedido_num" class="x-small font-weight-bold text-primary mt-1">
                                                <i class="fa fa-shopping-cart mr-1"></i>Pedido #{{ remision.pedido_num }}
                                            </div>
                                            <div v-else-if="remision.pedido_id" class="x-small font-weight-bold text-primary mt-1">
                                                <i class="fa fa-shopping-cart mr-1"></i>Pedido #{{ remision.pedido_id }}
                                            </div>
                                        </td>
                                        <td class="align-middle" v-text="remision.fecha"></td>
                                        <td class="align-middle">{{ remision.cliente ? remision.cliente.razonsocial : (remision.razonsocial ? remision.razonsocial.razonsocial : 'N/A') }}</td>
                                        <td class="align-middle text-right font-weight-bold text-primary">${{ forNum(remision.total) }}</td>
                                        <td class="align-middle">
                                            <span v-if="remision.cuentacobro_id" class="badge badge-success px-2 py-1">
                                                <i class="fa fa-check-circle mr-1"></i> Anexado a CC #{{ remision.cuentacobro_num }}
                                            </span>
                                            <span v-else class="badge badge-warning px-2 py-1">
                                                <i class="fa fa-clock-o mr-1"></i> Sin Cuenta de Cobro
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No hay remisiones registradas</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
                <template v-else-if="listado==0">
                    <vista-factura :factura="remisionSeleccionada" :edit="editMode" :user="user" @regresar="listado=1; listarRemisiones(1, buscar, criterio, per_page)"></vista-factura>
                </template>
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
                arrayRemisiones: [],
                selectedRemisiones: [],
                selectAll: false,
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
                remisionSeleccionada: null,
                editMode: 0
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
            forNum(value) {
                if (!value) return '0';
                return parseFloat(value).toLocaleString('es-CO');
            },
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedRemisiones = this.arrayRemisiones
                        .filter(r => !r.cuentacobro_id)
                        .map(r => r.id);
                } else {
                    this.selectedRemisiones = [];
                }
            },
            listarRemisiones(page, buscar, criterio, per_page) {
                let me = this;
                var url = '/comprobante/remisiones?page=' + page + '&per_page=' + per_page + '&buscar=' + buscar + '&criterio=' + criterio;
                axios.get(url).then(function(response) {
                    var respuesta = response.data;
                    me.arrayRemisiones = respuesta.comprobantes.data;
                    me.pagination = respuesta.pagination;
                    me.selectedRemisiones = [];
                    me.selectAll = false;
                }).catch(function(error) {
                    console.log(error);
                });
            },
            crearCuentaCobro() {
                if (this.selectedRemisiones.length === 0) {
                    Swal.fire('Atención', 'Seleccione al menos una remisión disponible para generar la Cuenta de Cobro.', 'warning');
                    return;
                }
                let me = this;
                Swal.fire({
                    title: '¿Generar Cuenta de Cobro?',
                    text: 'Se anexarán ' + this.selectedRemisiones.length + ' remisión(es) a la nueva Cuenta de Cobro.',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, generar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.post('/comprobante/crearCuentaCobroDesdeRemisiones', {
                            remision_ids: me.selectedRemisiones
                        }).then(function(response) {
                            Swal.fire('¡Cuenta de Cobro Creada!', response.data.message, 'success');
                            me.selectedRemisiones = [];
                            me.selectAll = false;
                            me.listarRemisiones(1, me.buscar, me.criterio, me.per_page);
                        }).catch(function(error) {
                            let msg = error.response && error.response.data && error.response.data.message ? error.response.data.message : 'Error al generar la Cuenta de Cobro.';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            },
            cambiarPagina(page, buscar, criterio) {
                this.pagination.current_page = page;
                this.listarRemisiones(page, buscar, criterio, this.per_page);
            },
            editarRemision(remision) {
                this.remisionSeleccionada = remision;
                this.editMode = 1;
                this.listado = 0;
            },
            borrarRemision(id) {
                let me = this;
                Swal.fire({
                    title: '¿Está seguro de eliminar esta remisión?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.delete('/comprobante/borrar?id=' + id + '&user_id=' + me.user.id).then(function(response) {
                            me.listarRemisiones(1, me.buscar, me.criterio, me.per_page);
                        }).catch(function(error) {
                            console.log(error);
                        });
                    }
                });
            }
        },
        mounted() {
            this.listarRemisiones(1, this.buscar, this.criterio, this.per_page);
        }
    }
</script>
