<template>
    <div class="main-container">
        <!-- Listado de Cotizaciones -->
        <div v-if="listado" class="fade-in">
            <div class="card shadow-lg border-0 rounded-20">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="font-weight-900 text-dark mb-0">
                            <i class="fa fa-file-invoice-dollar mr-2 text-primary-custom" style="color: #a0ef6e;"></i> 
                            Gestión de Cotizaciones
                        </h2>
                        <p class="text-muted small mb-0">Visualiza, crea y convierte cotizaciones en pedidos reales del sistema.</p>
                    </div>
                    <button type="button" @click="mostrarDetalle()" class="btn btn-dark shadow-sm px-4 py-2 font-weight-800 rounded-10" style="border: 2px solid #a0ef6e;">
                        <i class="fa fa-plus-circle mr-2" style="color: #a0ef6e;"></i> NUEVA COTIZACIÓN
                    </button>
                </div>

                <div class="card-body px-4 pb-4">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="input-group search-group shadow-sm rounded-15 overflow-hidden border">
                                <div class="input-group-prepend">
                                    <select class="form-control border-0 bg-light font-weight-700 px-3" v-model="criterio" style="width: 160px; height: 50px;">
                                        <option value="cliente_id">Nombre Cliente</option>
                                        <option value="id">Número #</option>
                                        <option value="observaciones">Observaciones</option>
                                    </select>
                                </div>
                                <input type="text" v-model="buscar" @keyup.enter="listarCotizaciones(1,buscar,criterio)" class="form-control border-0 px-4" placeholder="¿Qué cotización estás buscando?" style="height: 50px;">
                                <div class="input-group-append">
                                    <button type="button" @click="listarCotizaciones(1,buscar,criterio)" class="btn btn-dark px-4" style="height: 50px;">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right d-flex align-items-center justify-content-end">
                            <span class="badge badge-light p-2 font-weight-700 text-muted">Mostrando {{ arrayCotizaciones.length }} resultados</span>
                        </div>
                    </div>

                    <!-- Tabla Premium -->
                    <div class="table-responsive">
                        <table class="table table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>OPCIONES</th>
                                    <th>ESTADO</th>
                                    <th>NÚMERO</th>
                                    <th>FECHA</th>
                                    <th>CLIENTE</th>
                                    <th>VALOR TOTAL</th>
                                    <th>ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody v-if="arrayCotizaciones.length > 0">
                                <tr v-for="cotizacion in arrayCotizaciones" :key="cotizacion.id">
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <button type="button" @click="abrirDetalle(cotizacion)" class="btn btn-outline-dark btn-sm rounded-circle mx-1 p-2 shadow-sm" title="Ver Detalles">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <a :href="'/imprimirPedido?id=' + cotizacion.id" target="_blank" class="btn btn-outline-danger btn-sm rounded-circle mx-1 p-2 shadow-sm" title="Ver PDF / Imprimir" style="color: #d9534f;">
                                                <i class="fa fa-file-pdf-o"></i>
                                            </a>
                                            <button type="button" @click="eliminarCotizacion(cotizacion.id)" class="btn btn-outline-danger btn-sm rounded-circle mx-1 p-2 shadow-sm" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-pill badge-primary-soft p-2 px-3 font-weight-800 text-uppercase">COTIZACIÓN</span>
                                    </td>
                                    <td class="align-middle text-center font-weight-900 text-dark"># {{ cotizacion.id }}</td>
                                    <td class="align-middle text-muted small font-weight-700">{{ cotizacion.fecha }}</td>
                                    <td class="align-middle font-weight-800 text-dark">
                                        {{ cotizacion.cliente ? cotizacion.cliente.razonsocial : 'N/A' }}
                                    </td>
                                    <td class="align-middle font-weight-900 text-success h5 mb-0">
                                        $ {{ formatear(cotizacion.total) }}
                                    </td>
                                    <td class="align-middle text-right">
                                        <button v-if="cotizacion.estado !== 'Convertida'" @click="convertirAPedido(cotizacion.id)" class="btn btn-success btn-sm font-weight-900 rounded-pill px-3 shadow-sm" style="font-size: 0.75rem;">
                                            <i class="fa fa-magic mr-1"></i> VOLVER PEDIDO
                                        </button>
                                        <button v-else @click="revertirConversion(cotizacion.id)" class="btn btn-warning btn-sm font-weight-900 rounded-pill px-3 shadow-sm text-white" style="font-size: 0.75rem;">
                                            <i class="fa fa-undo mr-1"></i> REVERTIR
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state py-4">
                                            <i class="fa fa-folder-open mb-3 text-muted display-4"></i>
                                            <h4 class="text-muted font-weight-700">No se encontraron cotizaciones</h4>
                                            <p class="text-secondary small">Intenta con otro criterio de búsqueda o crea una nueva.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación estilo Premium -->
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{'disabled': pagination.current_page == 1}">
                                <a class="page-link shadow-sm border-0 rounded-circle mx-1 font-weight-bold" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link shadow-sm border-0 rounded-circle mx-1 font-weight-bold" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                            </li>
                            <li class="page-item" :class="{'disabled': pagination.current_page == pagination.last_page}">
                                <a class="page-link shadow-sm border-0 rounded-circle mx-1 font-weight-bold" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Vista de Detalle / Nueva Cotización -->
        <div v-else class="fade-in">
            <div class="mb-3 d-flex align-items-center">
                <button @click="ocultarDetalle(1)" class="btn btn-link text-dark font-weight-800 p-0 mr-3 decoration-none">
                    <i class="fa fa-arrow-left mr-2"></i> Volver al listado
                </button>
                <div class="h3 font-weight-900 mb-0 shadow-text">
                    {{ edit ? 'Editando Cotización #' + cotizacionActual.id : 'Creando Nueva Cotización' }}
                </div>
            </div>
            <cotizacion :dato="1" @ocultarDetalle="ocultarDetalle" :edit="edit" :cotizacion_data="cotizacionActual"></cotizacion>
        </div>
    </div>
</template>

<script>
    import cotizacion from './partes/Cotizacion'

    export default {
        data (){
            return {
                listado: 1,
                edit: 0,
                arrayCotizaciones: [],
                pagination: {
                    total: 0,
                    current_page: 0,
                    per_page: 0,
                    last_page: 0,
                    from: 0,
                    to: 0,
                },
                offset: 3,
                criterio: 'cliente_id',
                buscar: '',
                cotizacionActual: null
            }
        },
        components: {
            cotizacion
        },
        computed:{
            isActived: function(){
                return this.pagination.current_page;
            },
            pagesNumber: function() {
                if(!this.pagination.to) return [];
                var from = this.pagination.current_page - this.offset; 
                if(from < 1) from = 1;
                var to = from + (this.offset * 2); 
                if(to >= this.pagination.last_page) to = this.pagination.last_page;
                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;
            }
        },
        methods : {
            listarCotizaciones(page, buscar, criterio){
                let me = this;
                var url = '/comprobante/cotizaciones?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayCotizaciones = respuesta.comprobantes.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            cambiarPagina(page){
                let me = this;
                me.pagination.current_page = page;
                me.listarCotizaciones(page, me.buscar, me.criterio);
            },
            mostrarDetalle(){
                this.listado = 0;
                this.edit = 0;
                this.cotizacionActual = null;
            },
            ocultarDetalle(val){
                this.listado = val;
                if(val == 1) this.listarCotizaciones(1, '', 'cliente_id');
            },
            abrirDetalle(data){
                this.cotizacionActual = data;
                this.edit = 1;
                this.listado = 0;
            },
            formatear(valor){
                return new Intl.NumberFormat('es-CO').format(valor);
            },
            convertirAPedido(id){
                Swal.fire({
                    title: '¿Convertir en Pedido?',
                    text: "Esta cotización pasará a ser un pedido activo en producción.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#a0ef6e',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, convertir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let me = this;
                        axios.put('/comprobante/convertirAPedido', {id: id})
                        .then(function (response) {
                            Swal.fire('¡Éxito!', 'La cotización se ha convertido en pedido.', 'success');
                            me.listarCotizaciones(1, '', 'cliente_id');
                        }).catch(function (error) {
                            console.log(error);
                            Swal.fire('Error', 'No se pudo convertir el documento.', 'error');
                        });
                    }
                })
            },
            revertirConversion(id){
                Swal.fire({
                    title: '¿Revertir Conversión?',
                    text: "Se eliminará el pedido generado y la cotización volverá a estar disponible para editar.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, revertir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let me = this;
                        axios.post('/crm/cotizacion/revertir-conversion', {id: id})
                        .then(function (response) {
                            Swal.fire('¡Éxito!', response.data.message || 'La conversión se ha revertido.', 'success');
                            me.listarCotizaciones(1, '', 'cliente_id');
                        }).catch(function (error) {
                            console.log(error);
                            const msg = error.response && error.response.data && error.response.data.message ? error.response.data.message : 'No se pudo revertir el documento.';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                })
            },
            eliminarCotizacion(id){
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Eliminar definitivamente'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let me = this;
                        axios.delete('/pedido/borrar', {data: {id: id}})
                        .then(function (response) {
                            Swal.fire('Eliminado', 'La cotización ha sido borrada.', 'success');
                            me.listarCotizaciones(1, '', 'cliente_id');
                        }).catch(function (error) {
                            console.log(error);
                        });
                    }
                })
            }
        },
        mounted() {
            this.listarCotizaciones(1, this.buscar, this.criterio);
        }
    }
</script>

<style scoped>
    .main-container { padding: 30px; background-color: #f4f7f6; min-height: 100vh; }
    .rounded-20 { border-radius: 20px !important; }
    .rounded-15 { border-radius: 15px !important; }
    .rounded-10 { border-radius: 10px !important; }
    .font-weight-900 { font-weight: 900 !important; }
    .font-weight-800 { font-weight: 800 !important; }
    .font-weight-700 { font-weight: 700 !important; }
    
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .badge-primary-soft {
        background-color: #e0f2fe;
        color: #0369a1;
        letter-spacing: 0.5px;
        font-size: 0.7rem;
    }

    .custom-table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .custom-table thead th {
        border: none;
        color: #94a3b8;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        padding: 15px;
    }

    .custom-table tbody tr {
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }

    .custom-table tbody tr:hover {
        background-color: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .custom-table td {
        border: none;
        padding: 20px 15px;
    }

    .pagination .page-link {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4b5563;
        font-size: 0.9rem;
    }

    .pagination .active .page-link {
        background-color: #1a1a1a !important;
        color: #a0ef6e !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2) !important;
    }

    .search-group {
        border-color: #e2e8f0;
    }

    .shadow-text {
        text-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>

