<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Compras - Ingresos</li>
        </ol>
        <div class="container-fluid">
            <!-- Listado de Ingresos -->
            <div class="card" v-if="listado==1">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fa fa-align-justify"></i> <strong>Ingresos de Almacén (Compras)</strong>
                    </div>
                    <button type="button" @click="mostrarDetalle()" class="btn btn-success text-white">
                        <i class="icon-plus"></i>&nbsp;Nuevo Registro
                    </button>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="form-group row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select class="form-control col-md-3" v-model="criterio">
                                    <option value="num_comprobante">Número Comprobante</option>
                                    <option value="tipo_comprobante">Tipo Comprobante</option>
                                    <option value="fecha_hora">Fecha</option>
                                </select>
                                <input type="text" v-model="buscar" @keyup.enter="listarIngreso(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                                <button type="submit" @click="listarIngreso(1,buscar,criterio)" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Ingresos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr class="bg-light text-center">
                                    <th style="width: 120px;">Opciones</th>
                                    <th>Usuario</th>
                                    <th>Proveedor</th>
                                    <th>Comprobante</th>
                                    <th>Fecha Hora</th>
                                    <th>Total</th>
                                    <th>Impuesto</th>
                                    <th>Forma de Pago</th>
                                    <th>Días Crédito</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ingreso in arrayIngreso" :key="ingreso.id">
                                    <td class="text-center">
                                        <button type="button" @click="verIngreso(ingreso.id)" class="btn btn-success btn-sm" title="Ver detalles">
                                            <i class="icon-eye"></i>
                                        </button> &nbsp;
                                        <template v-if="ingreso.estado=='Registrado'">
                                            <button type="button" class="btn btn-danger btn-sm" @click="desactivarIngreso(ingreso.id)" title="Anular compra">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </template>
                                    </td>
                                    <td v-text="ingreso.usuario"></td>
                                    <td v-text="ingreso.proveedor_nombre"></td>
                                    <td>{{ ingreso.tipo_comprobante }} {{ ingreso.serie_comprobante }}-{{ ingreso.num_comprobante }}</td>
                                    <td class="text-center">{{ ingreso.fecha_hora }}</td>
                                    <td class="text-right text-dark font-weight-bold">${{ formatNumber(ingreso.total) }}</td>
                                    <td class="text-center">{{ (ingreso.impuesto * 100) }}%</td>
                                    <td class="text-center">
                                        <span class="badge text-white" :class="ingreso.forma_pago == 'Crédito' ? 'bg-warning' : 'bg-info'">
                                            {{ ingreso.forma_pago }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ ingreso.forma_pago == 'Crédito' ? ingreso.dias_credito + ' días' : '-' }}</td>
                                    <td class="text-center">
                                        <span v-if="ingreso.estado=='Registrado'" class="badge bg-success text-white">Registrado</span>
                                        <span v-else class="badge bg-danger text-white">Anulado</span>
                                    </td>
                                </tr>
                                <tr v-if="arrayIngreso.length == 0">
                                    <td colspan="10" class="text-center text-muted py-4">No se encontraron registros de ingresos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <nav class="mt-3" v-if="pagination.last_page > 1">
                        <ul class="pagination justify-content-end">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Registro de Ingreso (Nuevo) -->
            <div class="card" v-if="listado==0">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-align-justify"></i> <strong>Registrar Factura de Compra / Ingreso</strong>
                </div>
                <div class="card-body">
                    <!-- Cabecera del Ingreso -->
                    <div class="form-group row border-bottom pb-3 mb-4">
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-control-label"><strong>Proveedor (*)</strong></label>
                                    <v-select
                                        :on-search="selectProveedor"
                                        label="nombre"
                                        :options="arrayProveedor"
                                        placeholder="Buscar Proveedores..."
                                        :onChange="getDatosProveedor"
                                    ></v-select>
                                    <span v-if="proveedor" class="text-muted d-block mt-1">
                                        NIT: {{ proveedor.num_documento }} | Tel: {{ proveedor.telefono }}
                                        <template v-if="cupo_credito > 0">
                                            <br>
                                            <span class="badge bg-secondary text-white mr-1">Cupo Autorizado: ${{ formatNumber(cupo_credito) }}</span>
                                            <span class="badge bg-danger text-white mr-1">Saldo Pendiente: ${{ formatNumber(saldo_pendiente) }}</span>
                                            <span class="badge" :class="cupo_disponible >= calcularTotal ? 'bg-success text-white' : 'bg-warning text-dark'">
                                                Cupo Disponible: ${{ formatNumber(cupo_disponible) }}
                                            </span>
                                        </template>
                                    </span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-control-label"><strong>Impuesto (*)</strong></label>
                                    <input type="number" class="form-control text-center" v-model="impuesto" min="0" max="1" step="0.01">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-control-label"><strong>Forma de Pago (*)</strong></label>
                                    <select class="form-control" v-model="forma_pago">
                                        <option value="Contado">Contado</option>
                                        <option value="Crédito">Crédito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-control-label"><strong>Tipo Comprobante (*)</strong></label>
                                    <select class="form-control" v-model="tipo_comprobante">
                                        <option value="FACTURA">Factura</option>
                                        <option value="BOLETA">Boleta</option>
                                        <option value="REMISION">Remisión</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-control-label"><strong>Serie Comprobante</strong></label>
                                    <input type="text" class="form-control" v-model="serie_comprobante" placeholder="001">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-control-label"><strong>Número Comprobante (*)</strong></label>
                                    <input type="text" class="form-control" v-model="num_comprobante" placeholder="0001">
                                </div>
                                <div class="col-md-3 mb-3" v-if="forma_pago == 'Crédito'">
                                    <label class="form-control-label"><strong>Días de Crédito (*)</strong></label>
                                    <input type="number" class="form-control text-center" v-model="dias_credito" min="1" step="1">
                                </div>
                            </div>
                            <!-- Alerta de cupo de crédito excedido -->
                            <div v-if="forma_pago == 'Crédito' && cupo_credito > 0 && calcularTotal > cupo_disponible" class="alert alert-danger mt-2 py-2 text-center" style="width:100%;" role="alert">
                                <i class="fa fa-warning"></i> <strong>¡Advertencia!</strong> Esta compra a crédito supera el cupo de crédito disponible del proveedor (${{ formatNumber(cupo_disponible) }}).
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="aceptarSobrecupoIngreso" v-model="aceptar_sobrecupo">
                                    <label class="custom-control-label font-weight-bold cursor-pointer" for="aceptarSobrecupoIngreso">Aceptar sobrecupo (Permitir registrar compra superando el límite de crédito)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 bg-light p-3 border rounded text-center d-flex flex-column justify-content-center">
                            <span class="text-uppercase text-muted" style="font-size: 11px;">Total de Compra</span>
                            <h2 class="text-primary font-weight-bold my-1">${{ formatNumber(calcularTotal) }}</h2>
                            <span class="text-muted" style="font-size: 11px;">(Subtotal: ${{ formatNumber(calcularSubtotal) }} + IVA: ${{ formatNumber(calcularImpuesto) }})</span>
                        </div>
                    </div>

                    <!-- Detalle / Selección de Artículos -->
                    <div class="row border-bottom pb-4 mb-4">
                        <div class="col-md-12 mb-3">
                            <h5>Añadir Artículos</h5>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" v-model="codigo" @keyup.enter="buscarArticulo()" placeholder="Buscar por código de barra/referencia">
                                <button class="btn btn-secondary" @click="abrirModalArticulos()"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control bg-light" v-model="articulo" readonly placeholder="Artículo seleccionado">
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" v-model="precio" step="0.01" placeholder="Precio">
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <input type="number" class="form-control" v-model="cantidad" placeholder="Cant.">
                        </div>
                        <div class="col-md-1 mb-2">
                            <button class="btn btn-success btn-block" @click="agregarDetalle()"><i class="fa fa-plus-circle"></i></button>
                        </div>
                    </div>

                    <!-- Listado de Artículos Agregados -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr class="bg-light text-center">
                                    <th style="width: 80px;">Eliminar</th>
                                    <th>Artículo</th>
                                    <th>Precio Compra</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(detalle, index) in arrayDetalle" :key="index">
                                    <td class="text-center">
                                        <button @click="eliminarDetalle(index)" type="button" class="btn btn-danger btn-sm">
                                            <i class="icon-close"></i>
                                        </button>
                                    </td>
                                    <td v-text="detalle.articulo"></td>
                                    <td class="text-right">
                                        <input type="number" step="any" v-model="detalle.precio" class="form-control text-right py-0" style="height: auto;">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="1" v-model="detalle.cantidad" class="form-control text-center py-0" style="height: auto;">
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        ${{ formatNumber(detalle.precio * detalle.cantidad) }}
                                    </td>
                                </tr>
                                <tr v-if="arrayDetalle.length == 0">
                                    <td colspan="5" class="text-center text-muted py-4">Debe agregar al menos un artículo para registrar la compra.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensajes de Error -->
                    <div v-show="errorIngreso" class="form-group row div-error justify-content-center">
                        <div class="text-center text-error col-md-6">
                            <div class="alert alert-danger" role="alert">
                                <ul class="text-left mb-0">
                                    <li v-for="error in errorMostrarMsjIngreso" :key="error" v-text="error"></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="form-group row mt-4">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-secondary" @click="ocultarDetalle()">Regresar</button>
                            <button type="button" class="btn btn-primary" :disabled="arrayDetalle.length == 0" @click="registrarIngreso()">Guardar Compra</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE BÚSQUEDA DE ARTÍCULOS -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h4 class="modal-title">Seleccione uno o varios Artículos</h4>
                        <button type="button" class="close text-white" @click="cerrarModalArticulos()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Buscador de Artículos -->
                        <div class="form-group row mb-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <select class="form-control col-md-4" v-model="criterioA">
                                        <option value="nombre">Nombre</option>
                                        <option value="codigo">Código</option>
                                        <option value="descripcion">Descripción</option>
                                    </select>
                                    <input type="text" v-model="buscarA" @keyup.enter="listarArticulo(1,buscarA,criterioA)" class="form-control" placeholder="Buscar artículo...">
                                    <button type="submit" @click="listarArticulo(1,buscarA,criterioA)" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Artículos -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr class="bg-light text-center">
                                        <th style="width: 80px;">Selección</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Stock</th>
                                        <th>Precio Venta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="articulo in arrayArticulo" :key="articulo.id">
                                        <td class="text-center">
                                            <button type="button" @click="agregarDetalleModal(articulo)" class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                        </td>
                                        <td v-text="articulo.codigo"></td>
                                        <td v-text="articulo.nombre"></td>
                                        <td v-text="articulo.nombre_categoria"></td>
                                        <td class="text-center" v-text="articulo.stock"></td>
                                        <td class="text-right font-weight-bold" v-text="'$' + formatNumber(articulo.precio_venta)"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalArticulos()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE VISUALIZACIÓN DE DETALLE DE COMPRA (INGRESOS) -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalDetalle}" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content" v-if="detalleCabecera">
                    <div class="modal-header bg-primary text-white">
                        <h4 class="modal-title">Detalle de Factura de Compra</h4>
                        <button type="button" class="close text-white" @click="cerrarModalDetalle()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row mb-4 border-bottom pb-3">
                            <div class="col-md-6 mb-2">
                                <strong>Proveedor:</strong> {{ detalleCabecera.proveedor_nombre }} <br>
                                <strong>Registrado por:</strong> {{ detalleCabecera.usuario }} <br>
                                <strong>Fecha de Registro:</strong> {{ detalleCabecera.fecha_hora }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Tipo de Comprobante:</strong> {{ detalleCabecera.tipo_comprobante }} <br>
                                <strong>Número:</strong> {{ detalleCabecera.serie_comprobante }} - {{ detalleCabecera.num_comprobante }} <br>
                                <strong>Término de Pago:</strong> 
                                <span class="badge text-white" :class="detalleCabecera.forma_pago == 'Crédito' ? 'bg-warning' : 'bg-info'">
                                    {{ detalleCabecera.forma_pago }}
                                </span>
                                <span v-if="detalleCabecera.forma_pago == 'Crédito'"> ({{ detalleCabecera.dias_credito }} días)</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr class="bg-light text-center">
                                        <th>Artículo</th>
                                        <th>Precio Compra</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(det, index) in detalleCuerpo" :key="index">
                                        <td v-text="det.articulo"></td>
                                        <td class="text-right">${{ formatNumber(det.precio) }}</td>
                                        <td class="text-center" v-text="det.cantidad"></td>
                                        <td class="text-right font-weight-bold text-dark">${{ formatNumber(det.precio * det.cantidad) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total Factura:</th>
                                        <th class="text-right text-primary font-weight-bold text-lg">${{ formatNumber(detalleCabecera.total) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalDetalle()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import vSelect from 'vue-select';

    export default {
        props: ['user'],
        components: {
            vSelect
        },
        data() {
            return {
                ingreso_id: 0,
                idproveedor: 0,
                proveedor: null,
                nombre: '',
                tipo_comprobante: 'FACTURA',
                serie_comprobante: '',
                num_comprobante: '',
                impuesto: 0.19,
                total: 0.0,
                forma_pago: 'Contado',
                dias_credito: 30,
                
                cupo_credito: 0.00,
                saldo_pendiente: 0.00,
                cupo_disponible: 0.00,
                aceptar_sobrecupo: false,
                
                arrayIngreso: [],
                arrayDetalle: [],
                arrayProveedor: [],
                listado: 1,
                
                // Modal para buscar artículos
                modal: 0,
                arrayArticulo: [],
                criterioA: 'nombre',
                buscarA: '',
                
                // Formulario de agregar artículo
                idarticulo: 0,
                articulo: '',
                codigo: '',
                precio: 0,
                cantidad: 1,

                // Modal detalle de un ingreso
                modalDetalle: 0,
                detalleCabecera: null,
                detalleCuerpo: [],
                
                errorIngreso: 0,
                errorMostrarMsjIngreso: [],
                
                pagination: {
                    'total': 0,
                    'current_page': 0,
                    'per_page': 0,
                    'last_page': 0,
                    'from': 0,
                    'to': 0,
                },
                offset: 3,
                criterio: 'num_comprobante',
                buscar: ''
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
            },
            calcularTotal() {
                var resultado = 0.0;
                for (var i = 0; i < this.arrayDetalle.length; i++) {
                    resultado = resultado + (parseFloat(this.arrayDetalle[i].precio) * parseInt(this.arrayDetalle[i].cantidad));
                }
                return resultado;
            },
            calcularSubtotal() {
                return this.calcularTotal / (1 + parseFloat(this.impuesto));
            },
            calcularImpuesto() {
                return this.calcularTotal - this.calcularSubtotal;
            }
        },
        methods: {
            listarIngreso(page, buscar, criterio) {
                let me = this;
                var url = '/ingreso?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayIngreso = respuesta.ingresos.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectProveedor(search, loading) {
                let me = this;
                loading(true);
                var url = '/proveedor/selectProveedor?filtro=' + search;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayProveedor = respuesta.proveedores;
                    loading(false);
                })
                .catch(function (error) {
                    console.log(error);
                    loading(false);
                });
            },
            getDatosProveedor(val1) {
                let me = this;
                if (val1) {
                    me.proveedor = val1;
                    me.idproveedor = val1.id;
                    
                    // Fetch credit limit details
                    axios.get('/proveedor/obtenerEstadoCredito?id=' + val1.id)
                        .then(function(response) {
                            let data = response.data;
                            me.cupo_credito = parseFloat(data.cupo_credito);
                            me.saldo_pendiente = parseFloat(data.saldo_pendiente);
                            me.cupo_disponible = parseFloat(data.cupo_disponible);
                        })
                        .catch(function(error) {
                            console.log(error);
                        });
                } else {
                    me.proveedor = null;
                    me.idproveedor = 0;
                    me.cupo_credito = 0.00;
                    me.saldo_pendiente = 0.00;
                    me.cupo_disponible = 0.00;
                }
            },
            buscarArticulo() {
                let me = this;
                if (!me.codigo) return;
                var url = '/articulo/buscarArticulo?filtro=' + me.codigo;
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    var articulos = respuesta.articulos;
                    if (articulos.length > 0) {
                        me.articulo = articulos[0]['nombre'];
                        me.idarticulo = articulos[0]['id'];
                        me.precio = parseFloat(articulos[0]['precio_venta']) * 0.7; // default purchase price estimate 70% of sale
                        me.cantidad = 1;
                    } else {
                        me.articulo = 'No existe Artículo';
                        me.idarticulo = 0;
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            formatNumber(value) {
                if (!value) return '0.00';
                return parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            },
            cambiarPagina(page, buscar, criterio) {
                let me = this;
                me.pagination.current_page = page;
                me.listarIngreso(page, buscar, criterio);
            },
            encuentra(id) {
                var sw = false;
                for (var i = 0; i < this.arrayDetalle.length; i++) {
                    if (this.arrayDetalle[i].idarticulo == id) {
                        sw = true;
                        break;
                    }
                }
                return sw;
            },
            agregarDetalle() {
                let me = this;
                if (me.idarticulo == 0 || me.cantidad <= 0 || me.precio <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        text: 'Verifique los valores de artículo, precio y cantidad.'
                    });
                    return;
                }
                if (me.encuentra(me.idarticulo)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ese artículo ya se encuentra agregado.'
                    });
                } else {
                    me.arrayDetalle.push({
                        idarticulo: me.idarticulo,
                        articulo: me.articulo,
                        cantidad: parseInt(me.cantidad),
                        precio: parseFloat(me.precio)
                    });
                    // Limpiar campos
                    me.codigo = "";
                    me.idarticulo = 0;
                    me.articulo = "";
                    me.cantidad = 1;
                    me.precio = 0;
                }
            },
            agregarDetalleModal(articuloObj) {
                let me = this;
                if (me.encuentra(articuloObj.id)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ese artículo ya se encuentra agregado.'
                    });
                } else {
                    me.arrayDetalle.push({
                        idarticulo: articuloObj.id,
                        articulo: articuloObj.nombre,
                        cantidad: 1,
                        precio: parseFloat(articuloObj.precio_venta) * 0.7 // Estimar precio de compra en 70% del de venta
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        title: 'Agregado: ' + articuloObj.nombre
                    });
                }
            },
            eliminarDetalle(index) {
                this.arrayDetalle.splice(index, 1);
            },
            listarArticulo(page, buscar, criterio) {
                let me = this;
                var url = '/articulo?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayArticulo = respuesta.articulos.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            abrirModalArticulos() {
                this.modal = 1;
                this.listarArticulo(1, this.buscarA, this.criterioA);
            },
            cerrarModalArticulos() {
                this.modal = 0;
                this.arrayArticulo = [];
            },
            mostrarDetalle() {
                this.listado = 0;
                this.idproveedor = 0;
                this.proveedor = null;
                this.tipo_comprobante = 'FACTURA';
                this.serie_comprobante = '';
                this.num_comprobante = '';
                this.impuesto = 0.19;
                this.total = 0.0;
                this.forma_pago = 'Contado';
                this.dias_credito = 30;
                this.cupo_credito = 0.00;
                this.saldo_pendiente = 0.00;
                this.cupo_disponible = 0.00;
                this.aceptar_sobrecupo = false;
                this.arrayDetalle = [];
                this.codigo = '';
                this.idarticulo = 0;
                this.articulo = '';
                this.cantidad = 1;
                this.precio = 0;
            },
            ocultarDetalle() {
                this.listado = 1;
                this.listarIngreso(1, this.buscar, this.criterio);
            },
            validarIngreso() {
                this.errorIngreso = 0;
                this.errorMostrarMsjIngreso = [];

                if (this.idproveedor == 0) this.errorMostrarMsjIngreso.push("Seleccione un Proveedor.");
                if (!this.tipo_comprobante) this.errorMostrarMsjIngreso.push("Seleccione el Tipo de Comprobante.");
                if (!this.num_comprobante) this.errorMostrarMsjIngreso.push("Ingrese el Número del Comprobante.");
                if (this.forma_pago == 'Crédito' && (!this.dias_credito || this.dias_credito <= 0)) {
                    this.errorMostrarMsjIngreso.push("Ingrese un número válido de Días de Crédito.");
                }
                if (this.forma_pago == 'Crédito' && this.cupo_credito > 0 && this.calcularTotal > this.cupo_disponible && !this.aceptar_sobrecupo) {
                    this.errorMostrarMsjIngreso.push("La compra a crédito excede el cupo disponible de crédito del proveedor (Cupo disponible: $" + this.formatNumber(this.cupo_disponible) + ").");
                }
                if (this.arrayDetalle.length <= 0) this.errorMostrarMsjIngreso.push("Debe ingresar al menos un Artículo al detalle.");

                if (this.errorMostrarMsjIngreso.length) this.errorIngreso = 1;

                return this.errorIngreso;
            },
            registrarIngreso() {
                if (this.validarIngreso()) {
                    return;
                }
                let me = this;
                axios.post('/ingreso/registrar', {
                    'idproveedor': this.idproveedor,
                    'tipo_comprobante': this.tipo_comprobante,
                    'serie_comprobante': this.serie_comprobante,
                    'num_comprobante': this.num_comprobante,
                    'impuesto': this.impuesto,
                    'total': this.calcularTotal,
                    'forma_pago': this.forma_pago,
                    'dias_credito': this.dias_credito,
                    'detalles': this.arrayDetalle,
                    'aceptar_sobrecupo': this.aceptar_sobrecupo ? 1 : 0
                }).then(function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'La compra se ha registrado correctamente.'
                    });
                    me.ocultarDetalle();
                }).catch(function (error) {
                    let errorMsg = 'Error en el servidor al registrar el ingreso.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMsg = error.response.data.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                });
            },
            desactivarIngreso(id) {
                let me = this;
                Swal.fire({
                    title: '¿Está seguro de anular esta factura de compra?',
                    text: "Esta acción descontará los artículos ingresados del stock.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.put('/ingreso/desactivar', {
                            'id': id
                        }).then(function (response) {
                            me.listarIngreso(1, me.buscar, me.criterio);
                            Swal.fire(
                                'Anulado!',
                                'El ingreso ha sido anulado con éxito.',
                                'success'
                            )
                        }).catch(function (error) {
                            let errorMsg = 'Error al anular el ingreso.';
                            if (error.response && error.response.data && error.response.data.error) {
                                errorMsg = error.response.data.error;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        });
                    }
                });
            },
            verIngreso(id) {
                let me = this;
                me.modalDetalle = 1;
                
                // Obtener cabecera
                axios.get('/ingreso/obtenerCabecera?id=' + id).then(function (response) {
                    me.detalleCabecera = response.data.ingreso;
                }).catch(function (error) {
                    console.log(error);
                });

                // Obtener detalles
                axios.get('/ingreso/obtenerDetalles?id=' + id).then(function (response) {
                    me.detalleCuerpo = response.data.detalles;
                }).catch(function (error) {
                    console.log(error);
                });
            },
            cerrarModalDetalle() {
                this.modalDetalle = 0;
                this.detalleCabecera = null;
                this.detalleCuerpo = [];
            }
        },
        mounted() {
            this.listarIngreso(1, this.buscar, this.criterio);
        }
    }
</script>

<style scoped>
    .mostrar {
        display: block !important;
        opacity: 1 !important;
        position: fixed !important;
        background-color: rgba(0,0,0,0.5) !important;
        z-index: 1050 !important;
        overflow-y: auto !important;
    }
    .div-error {
        display: flex;
        justify-content: center;
    }
    .text-error {
        color: red !important;
        font-weight: bold;
    }
</style>
