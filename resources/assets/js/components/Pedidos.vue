<template>
    <div class="contenedor">
        <div class="container-fluid">
            
            <!-- Ejemplo de tabla Listado -->
            <div class="">
                <div  class="contenedor-header">
                    <div v-if="listado==1">
                        <i class="fa fa-align-justify"></i> Pedidos
                        <button type="button" @click="mostrarDetalle()" class="btn btn-success boton-principal">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                        <button type="button" @click="abrirModalConfigProforma()" class="btn btn-dark boton-principal ml-2" title="Configurar Factura Proforma (Consecutivo, Términos y Condiciones)">
                            <i class="fa fa-cog"></i>&nbsp;Configurar Proforma
                        </button>
                        <!-- <button type="button" @click="generar()" class="btn btn-secondary">
                            <i class="icon-plus"></i>&nbsp;Generar
                        </button> -->
                    </div>
                    <div v-else>
                        
                    </div>
                </div>
                <!-- Listado-->
                <template v-if="listado==1">
                    <div class="contenedor-seccion">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                    <option value="cliente_id" >Nombre cliente</option>
                                    <option value="id" >Nuemero Comprobante</option>
                                    <option value="fecha">Fecha</option>
                                    </select>
                                    <input v-if="criterio=='fecha'" type="date" v-model="buscar" @change="listarPedidos(1,buscar,criterio,per_page)" class="form-control">
                                    <input v-else type="text" v-model="buscar" @input="buscarLive" @keyup.enter="listarPedidos(1,buscar,criterio,per_page)" class="form-control" placeholder="Escribe para buscar cliente, ID o número...">
                                </div>
                            </div>
                            <div class="col-xl-12 mt-3">
                                <div class="btn-group w-100 shadow-sm rounded overflow-hidden">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary text-white border-0 px-3">Estado</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" :class="{'active btn-primary text-white': estadoActivo == ''}" @click="cambiarEstadoTab('')">Todos</button>
                                    <button class="btn btn-sm btn-outline-primary" :class="{'active btn-primary text-white': estadoActivo == '1'}" @click="cambiarEstadoTab('1')">Pendiente</button>
                                    <button class="btn btn-sm btn-outline-primary" :class="{'active btn-primary text-white': estadoActivo == '2'}" @click="cambiarEstadoTab('2')">En Produccion</button>
                                    <button class="btn btn-sm btn-outline-primary" :class="{'active btn-primary text-white': estadoActivo == '3'}" @click="cambiarEstadoTab('3')">Terminado</button>
                                    <button class="btn btn-sm btn-outline-primary" :class="{'active btn-primary text-white': estadoActivo == '4'}" @click="cambiarEstadoTab('4')">Para Entregar</button>
                                    <button class="btn btn-sm btn-outline-primary" :class="{'active btn-primary text-white': estadoActivo == '5'}" @click="cambiarEstadoTab('5')">Entregado</button>
                                    <button class="btn btn-sm btn-outline-danger" :class="{'active btn-danger text-white': estadoActivo == '6'}" @click="cambiarEstadoTab('6')">No Recogido</button>
                                </div>
                            </div>
                            <div class="col-xl-12 mt-2">
                                <div class="form-check form-switch ml-2 d-inline-block align-middle">
                                    <input class="form-check-input" type="checkbox" v-model="agruparPorCliente" id="groupClientSwitch">
                                    <label class="form-check-label" for="groupClientSwitch"><strong>Agrupar por Cliente</strong></label>
                                </div>
                            </div>
                        </div>
                         <nav>
                                <ul class="pagination">
                                    <li>
                                        <select class="custom-select mr-sm-2" id="inlineFormCustomSelect" v-model="per_page" @change="listarPedidos(1,buscar,criterio,per_page)">
                                            <option value="10" >10</option>
                                            <option value="50" >50</option>
                                            <option value="100" >100</option>
                                            <option value="150" >150</option>
                                            <option value="200" >200</option>
                                            <option value="500" >500</option>
                                            <option value="1000" >1000</option>
                                            
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
                            <table class="table table-modern table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Opciones</th>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th>Valor Total</th>
                                        <th>Abono</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                 <!-- Flat View -->
                                <tbody v-if="!agruparPorCliente">
                                    <tr v-for="(pedido, index) in pedidosOrdenados" :key="'f'+pedido.id" class="row-detail-glass">
                                        <td class="actions-cell">
                                            <div class="btn-group">
                                                <button type="button" @click="entrega(pedido)" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center" :title="pedido.num_remisiones ? `Entrega (${pedido.num_remisiones} remisión(es))` : 'Entrega'">
                                                     <i class="fa fa-truck"></i>
                                                     <span v-if="pedido.num_remisiones && pedido.num_remisiones > 0" class="badge badge-primary badge-pill ml-1 font-weight-bold" style="font-size: 0.68rem; padding: 0.2em 0.4em;">{{ pedido.num_remisiones }}</span>
                                                </button>
                                                <button type="button" @click="imprimirPedido(pedido)" class="btn btn-outline-warning btn-sm" title="Imprimir Pedido">
                                                    <i class="icon-printer"></i>
                                                </button>
                                                <button type="button" @click="crearOVerProforma(pedido)" class="btn btn-warning btn-sm font-weight-bold text-dark px-2" title="Crear y Sincronizar Proforma Oficial">
                                                     <i class="fa fa-file-text-o mr-1"></i> Proforma
                                                 </button>
                                                <button type="button" @click="verPedido(pedido)" class="btn btn-outline-success btn-sm" title="Editar">
                                                    <i class="icon-eye"></i>
                                                </button>
                                                <button type="button" @click="abrirModalPago(pedido)" class="btn btn-success btn-sm" title="Registrar Abono / Recibo de Pago">
                                                    <i class="fa fa-money"></i>
                                                </button>
                                                <button type="button" @click="borrarPedido(pedido.id)" class="btn btn-outline-danger btn-sm" title="Borrar">
                                                    <i class="icon-trash"></i>
                                                </button>
                                                <button v-if="parseFloat(pedido.abono) > 0 && parseFloat(pedido.total_recibos_registrados || 0) < parseFloat(pedido.abono)" type="button" @click="convertirAbonoEnRecibo(pedido)" class="btn btn-info btn-sm text-white" title="Migrar Abono a Recibo de Caja">
                                                    <i class="fa fa-exchange"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>{{ pedido.id }}</td>
                                        <td>{{ pedido.fecha }}</td>
                                        <td class="text-muted small italic">{{ pedido.cliente ? pedido.cliente.razonsocial : 'Sin Cliente' }}</td>
                                        <td>
                                            <select class="form-control form-control-sm border-0 bg-light rounded" @change="cambiarEstado(pedido)" v-model="pedido.estado">
                                                <option value="1">Pendiente</option>
                                                <option value="2">En produccion</option>
                                                <option value="3">Terminado</option>
                                                <option value="4">Para entregar</option>
                                                <option value="5">Entregado</option>
                                                <option value="6">No recogido</option>
                                            </select>
                                        </td>
                                        <td>${{ forNum(pedido.total) }}</td>
                                        <td>${{ forNum(pedido.abono) }}</td>
                                        <td class="font-weight-bold text-dark">${{ forNum(parseFloat(pedido.total || 0) - parseFloat(pedido.abono || 0)) }}</td>
                                    </tr>
                                </tbody>

                                <!-- Grouped View -->
                                <tbody v-if="agruparPorCliente" v-for="(grupo, gIdx) in pedidosAgrupados" :key="'g'+gIdx">
                                    <!-- Header del Cliente (Grupo) -->
                                    <tr class="group-row-premium" style="cursor: pointer;" @click="alternarGrupo(grupo.cliente ? grupo.cliente.id : 0)">
                                        <td class="d-flex align-items-center">
                                            <button class="btn btn-sm btn-dark mr-2">
                                                <i :class="estaExpandido(grupo.cliente ? grupo.cliente.id : 0) ? 'fa fa-minus' : 'fa fa-plus'"></i>
                                            </button>
                                            <div class="btn-group" @click.stop>
                                                <button @click="abrirModalPagoCliente(grupo)" class="btn btn-success btn-sm" title="Crear abono o recibo">
                                                    <i class="fa fa-money"></i> <span class="d-none d-md-inline ml-1">Abono Masivo</span>
                                                </button>
                                                <button @click="entregarTodo(grupo)" class="btn btn-primary btn-sm" title="Entregar pedidos">
                                                    <i class="fa fa-truck"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td colspan="2" class="text-center text-muted small">--- Group ---</td>
                                        <td><strong>{{ grupo.cliente ? grupo.cliente.razonsocial : 'Sin Cliente' }}</strong></td>
                                        <td class="text-center"><span class="badge badge-info shadow-sm">{{ grupo.pedidos.length }} Pedidos</span></td>
                                        <td><strong>${{ forNum(grupo.total_venta) }}</strong></td>
                                        <td><strong>${{ forNum(grupo.total_abono) }}</strong></td>
                                        <td class="text-danger font-weight-bold">${{ forNum(grupo.total_venta - grupo.total_abono) }}</td>
                                    </tr>

                                    <!-- Detalle de los Pedidos -->
                                    <template v-if="estaExpandido(grupo.cliente ? grupo.cliente.id : 0)">
                                        <tr v-for="(pedido, index) in grupo.pedidos" :key="'p'+pedido.id" class="row-detail-glass">
                                            <td class="actions-cell">
                                                <div class="btn-group">
                                                    <button type="button" @click="entrega(pedido)" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center" :title="pedido.num_remisiones ? `Entrega (${pedido.num_remisiones} remisión(es))` : 'Entrega'">
                                                        <i class="fa fa-truck"></i>
                                                        <span v-if="pedido.num_remisiones && pedido.num_remisiones > 0" class="badge badge-primary badge-pill ml-1 font-weight-bold" style="font-size: 0.68rem; padding: 0.2em 0.4em;">{{ pedido.num_remisiones }}</span>
                                                    </button>
                                                    <button type="button" @click="imprimirPedido(pedido)" class="btn btn-outline-warning btn-sm" title="Imprimir Pedido">
                                                        <i class="icon-printer"></i>
                                                    </button>
                                                    <button type="button" @click="crearOVerProforma(pedido)" class="btn btn-outline-info btn-sm font-weight-bold" title="📋 Crear / Ver Proforma">
                                                        <i class="fa fa-file-text-o"></i>
                                                    </button>
                                                    <button type="button" @click="verPedido(pedido)" class="btn btn-outline-success btn-sm" title="Editar">
                                                        <i class="icon-eye"></i>
                                                    </button>
                                                    <button type="button" @click="abrirModalPago(pedido)" class="btn btn-success btn-sm" title="Registrar Abono / Recibo de Pago">
                                                        <i class="fa fa-money"></i>
                                                    </button>
                                                    <button type="button" @click="borrarPedido(pedido.id)" class="btn btn-outline-danger btn-sm" title="Borrar">
                                                        <i class="icon-trash"></i>
                                                    </button>
                                                    <button v-if="parseFloat(pedido.abono) > 0 && parseFloat(pedido.total_recibos_registrados || 0) < parseFloat(pedido.abono)" type="button" @click="convertirAbonoEnRecibo(pedido)" class="btn btn-info btn-sm text-white" title="Migrar Abono a Recibo de Caja">
                                                        <i class="fa fa-exchange"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>{{ pedido.id }}</td>
                                            <td>{{ pedido.fecha }}</td>
                                            <td class="text-muted small italic">{{ pedido.cliente ? pedido.cliente.razonsocial : 'Sin Cliente' }}</td>
                                            <td>
                                                <select class="form-control form-control-sm border-0 bg-light rounded" @change="cambiarEstado(pedido)" v-model="pedido.estado">
                                                    <option value="1">Pendiente</option>
                                                    <option value="2">En produccion</option>
                                                    <option value="3">Completado</option>
                                                    <option value="4">Para entregar</option>
                                                    <option value="5">Entregado</option>
                                                    <option value="6">No recogido</option>
                                                </select>
                                            </td>
                                            <td>${{ forNum(pedido.total) }}</td>
                                            <td>${{ forNum(pedido.abono) }}</td>
                                            <td class="font-weight-bold text-dark">${{ forNum(parseFloat(pedido.total || 0) - parseFloat(pedido.abono || 0)) }}</td>
                                        </tr>
                                    </template>
                                </tbody>

                                <!-- No results message -->
                                <tbody v-if="arrayPedidos.length === 0">
                                    <tr>
                                        <th colspan="8" class="text-center py-5 text-muted">No hay pedidos disponibles con este filtro</th>
                                    </tr>
                                </tbody>

                                <!-- Totales -->
                                <tfoot v-if="arrayPedidos.length > 0">
                                    <tr class="summary-row-dark">
                                        <td colspan="5" class="text-right font-weight-bold">TOTALES GENERALES</td>
                                        <td class="font-weight-bold">${{ forNum(totalVenta) }}</td>
                                        <td class="font-weight-bold">${{ forNum(totalAbonos) }}</td>
                                        <td class="font-weight-bold text-warning">${{ forNum(totalCartera) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                         <nav>
                                <ul class="pagination">
                                   
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
                    </div>
                </template>
                <!--Fin Listado-->
                <!-- Detalle-->
                <template v-else-if="listado==2">
                    <verpedido :user="user" :pedido="pedido" ></verpedido>
                </template>
                <template v-else-if="listado==3">
                    <nuevaempresa  :empresa="empresa" @mostrarListado="mostrarListado"></nuevaempresa>
                </template>
                <template v-else-if="listado==4">
                    <entregaparcial :pedido="pedido" @actualizar="listarPedidos(pagination.current_page,buscar,criterio,per_page)" @cancelar="listado=1"></entregaparcial>
                </template>
                <template v-else-if="listado==5">
                    <entregamasiva :grupo="grupoSeleccionado" @actualizar="listado=1; listarPedidos(pagination.current_page,buscar,criterio,per_page)" @cancelar="listado=1"></entregamasiva>
                </template>
                <template v-else>
                    <pedido :user="user" :pedido="pedido" :dato="1" @ocultarDetalle="ocultarDetalle" @listarPedidos="listarPedidos" :edit="edit"></pedido>
                </template>
                <!-- Fin Detalle-->
            </div>
            <!-- Fin ejemplo de tabla Listado -->
        </div>
        <!-- Modal Registrar Pago -->
        <div class="modal fade" :class="{'mostrar' : modalPago}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg rounded-xl overflow-hidden">
                    <div class="modal-header bg-success text-white py-3">
                        <h4 class="modal-title font-weight-bold">
                            <i class="fa fa-money mr-2"></i> Registrar Abono o Recibo
                        </h4>
                        <button type="button" class="close text-white" @click="cerrarModalPago()" aria-label="Close">
                          <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <div class="p-2">
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Resumen del Pago</h6>
                                    <div class="bg-white rounded p-3 shadow-sm border mb-3">
                                        <div class="d-flex justify-content-between mb-2 small">
                                            <span>Documento:</span> <strong>#{{pagoData.num_comprobante}}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total:</span> <strong>${{forNum(pagoData.total)}}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-danger font-weight-bold">
                                            <span>Saldo Pendiente:</span> <span>${{forNum(pagoData.saldo)}}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">Monto a pagar</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                            <input type="number" class="form-control font-weight-bold text-success" v-model="pagoData.monto">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">Forma de Pago</label>
                                        <select class="form-control" v-model="pagoData.forma_pago">
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Banco">Banco / Transferencia</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-success btn-block btn-lg shadow-sm font-weight-bold mt-4" @click="registrarPago()" :disabled="loading">
                                        <i v-if="loading" class="fa fa-spinner fa-spin mr-2"></i>
                                        <i v-else class="fa fa-save mr-2"></i> GUARDAR PAGO
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 h-100 d-flex flex-column">
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-3 border-bottom pb-2">
                                        <i class="fa fa-history mr-1"></i> Historial de Pagos
                                    </h6>
                                    
                                    <div v-if="loading" class="text-center py-5">
                                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                                    </div>

                                    <div v-else-if="arrayPagos.length" class="table-responsive flex-grow-1" style="max-height: 350px;">
                                        <table class="table table-sm table-hover border">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="small py-1">Fecha</th>
                                                    <th class="small py-1">Recibo</th>
                                                    <th class="small py-1 text-right">Monto</th>
                                                    <th class="small py-1 text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="recibo in arrayPagos" :key="recibo.id">
                                                    <td class="small">{{ recibo.fecha }}</td>
                                                    <td class="small font-weight-bold">#{{ recibo.num_recibo }}</td>
                                                    <td class="small text-right text-success font-weight-bold">${{ forNum(recibo.monto) }}</td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-danger btn-xs py-0" @click="verReciboPdf(recibo.id)" title="Ver PDF">
                                                                <i class="fa fa-file-pdf-o"></i>
                                                            </button>
                                                            <button class="btn btn-outline-info btn-xs py-0" @click="abrirModalEditarRecibo(recibo)" title="Editar">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-xs py-0" @click="eliminarRecibo(recibo.id)" title="Eliminar">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div v-else class="text-center py-5 bg-white rounded border dash-border">
                                        <i class="fa fa-info-circle fa-2x text-muted mb-2 opacity-2"></i>
                                        <p class="small text-muted italic">No hay pagos registrados para este pedido.</p>
                                    </div>

                                    <div v-if="arrayPagos.length" class="mt-auto pt-3 border-top text-right pr-2">
                                        <span class="small text-muted font-weight-bold">TOTAL PAGADO: </span>
                                        <span class="text-success font-weight-bold h6 mb-0">${{ forNum(arrayPagos.reduce((a, b) => a + parseFloat(b.monto), 0)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Recibo -->
        <div class="modal fade" :class="{'mostrar' : modalEditarRecibo}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fa fa-edit mr-2"></i>Editar Recibo #{{reciboEditData.num_recibo}}</h5>
                        <button type="button" class="close text-white" @click="cerrarModalEditarRecibo()"><span>×</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Fecha</label>
                                <input type="date" class="form-control" v-model="reciboEditData.fecha">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">N° Recibo</label>
                                <input type="text" class="form-control" v-model="reciboEditData.num_recibo">
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="small font-weight-bold">Monto</label>
                                <input type="number" class="form-control" v-model="reciboEditData.monto">
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="small font-weight-bold">Forma de Pago</label>
                                <select class="form-control" v-model="reciboEditData.forma_pago">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Banco">Banco / Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="small font-weight-bold">Observaciones</label>
                                <textarea class="form-control" v-model="reciboEditData.observaciones" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalEditarRecibo()">Cancelar</button>
                        <button type="button" class="btn btn-info px-4 text-white" @click="actualizarRecibo()" :disabled="loading">
                            <i v-if="loading" class="fa fa-spinner fa-spin mr-1"></i>
                            <i v-else class="fa fa-save mr-1"></i> GUARDAR CAMBIOS
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Config Factura Proforma -->
        <div class="modal fade" :class="{'mostrar' : modalConfigProforma}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg rounded-xl">
                    <div class="modal-header bg-dark text-white py-3">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-file-text-o mr-2"></i> Configuración de Factura Proforma
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalConfigProforma()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold text-primary mb-2">
                                    <i class="fa fa-sort-numeric-asc mr-1"></i> Consecutivo de Proforma
                                </h6>
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold small">Número Inicial de Proforma</label>
                                    <input type="number" class="form-control form-control-lg font-weight-bold text-primary" v-model="proformaConfig.numero" placeholder="Ej. 5242" min="1">
                                </div>
                                <div v-if="proformaConfig.ultimo_generado > 0" class="alert alert-info py-2 small mb-0">
                                    <i class="fa fa-info-circle mr-1"></i> Último número proforma generado: <strong>#{{ proformaConfig.ultimo_generado }}</strong>. El siguiente disponible será: <strong>#{{ Math.max(proformaConfig.ultimo_generado + 1, parseInt(proformaConfig.numero || 0)) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-0 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold text-primary mb-2">
                                    <i class="fa fa-file-text mr-1"></i> Términos, Condiciones y Notas del PDF
                                </h6>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small">Términos y Condiciones (Visibles en la proforma PDF)</label>
                                    <textarea class="form-control" v-model="proformaConfig.terminos" rows="4" placeholder="Ingrese los términos y condiciones de la factura proforma..."></textarea>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Nota de Pie de Página (Aclaración legal/fiscal)</label>
                                    <input type="text" class="form-control" v-model="proformaConfig.nota_pie" placeholder="Este documento es un comprobante de cotización / factura proforma...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top-0">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalConfigProforma()">Cancelar</button>
                        <button type="button" class="btn btn-success btn-sm px-4" @click="guardarConfigProforma()"><i class="fa fa-save mr-1"></i> Guardar Configuración</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import vSelect from 'vue-select';
    import pedido from './partes/Pedido'
    import verpedido from './partes/VerPedido'
    import entregaparcial from './entregas/EntregaParcial'
    import entregamasiva from './entregas/EntregaMasiva'
    import nuevaempresa from './cliente/NuevaEmpresa'
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    var fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
    export default {
        props:['user','show'],
        data (){
            return {
                generarf:-1,
                edit:0,
                listado:1,
                arrayPedidos : [],
                offset : 3,
                criterio : 'cliente_id',
                buscar : '',
                estadoActivo: '', // pestaña de estado activa ('' = Todos),
                criterioA:'nombre',
                buscarA:'',
                empresa:{"id":0,"favorito":0,"razonsocial":"","tipo_persona":"Juridica","tipo_documento":"","numero":"","digito":"","direccion":"","telefono":"","correo":"","ciudad":"","departamento":"Valle del Cauca","pais":"Colombia","actividad":"","responsable":"","clientes":[]},
                pedido:{'fecha':fecha,'forma_pago':'Contado','transportadora':'','estado':2,'cliente':{'id':0,'contactos':[],'empresas':[],'envios':[]},'lineas':[],'iva':0.19,'subtotal':0, 'abono':0, 'saldo':0, 'descuento':0, 'impuestos':0, 'total':0},
                 pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                per_page:100,
                expandedGroups: [],
                agruparPorCliente: false,
                modalPago: 0,
                loading: false,
                pagoData: {
                    comprobante_id: 0,
                    cliente_id: 0,
                    es_masivo: false,
                    monto: 0,
                    forma_pago: 'Efectivo',
                    fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                    observaciones: '',
                    num_comprobante: '',
                    total: 0,
                    saldo: 0
                },
                arrayPagos: [],
                grupoSeleccionado: null,
                modalEditarRecibo: 0,
                reciboEditData: {
                    id: 0,
                    fecha: '',
                    monto: 0,
                    num_recibo: '',
                    forma_pago: '',
                    observaciones: ''
                },
                modalConfigProforma: 0,
                proformaConfig: {
                    numero: 5242,
                    ultimo_generado: 0,
                    terminos: '',
                    nota_pie: ''
                }
            }
        },
        components: {
            vSelect,
            pedido,
            verpedido,
            nuevaempresa,
            entregaparcial,
            entregamasiva
        },
        computed:{
            
            isActived: function(){
                return this.pagination.current_page;
            },
            //Calcula los elementos de la paginación
            pagesNumber: function() {
                if(!this.pagination.to) {
                    return [];
                }
                
                var from = this.pagination.current_page - this.offset; 
                if(from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2); 
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }  

                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;             

            },
            pedidosAgrupados() {
                if (!this.agruparPorCliente) return [];
                let grupos = {};
                if (!Array.isArray(this.arrayPedidos)) return [];
                
                // Asegurar orden descendente por ID (fecha)
                let sorted = [...this.arrayPedidos].sort((a,b) => b.id - a.id);

                sorted.forEach(p => {
                    if (!p) return;
                    let cid = (p.cliente && p.cliente.id) ? p.cliente.id : 0;
                    if (!grupos[cid]) {
                        grupos[cid] = {
                            cliente: p.cliente || { id: 0, razonsocial: 'Sin Cliente' },
                            total_venta: 0,
                            total_abono: 0,
                            total_saldo: 0,
                            pedidos: []
                        };
                    }
                    grupos[cid].total_venta += parseFloat(p.total || 0);
                    grupos[cid].total_abono += parseFloat(p.abono || 0);
                    grupos[cid].total_saldo += (parseFloat(p.total || 0) - parseFloat(p.abono || 0));
                    grupos[cid].pedidos.push(p);
                });
                return Object.values(grupos);
            },
            pedidosOrdenados() {
                if (!Array.isArray(this.arrayPedidos)) return [];
                return [...this.arrayPedidos].sort((a,b) => b.id - a.id);
            },
            totalCartera(){
                let resultado = 0;
                if (!Array.isArray(this.arrayPedidos)) return 0;
                this.arrayPedidos.forEach(p => resultado += (parseFloat(p.total || 0) - parseFloat(p.abono || 0)));
                return Math.max(0, resultado);
            },
            totalVenta(){
                let resultado = 0;
                if (!Array.isArray(this.arrayPedidos)) return 0;
                this.arrayPedidos.forEach(p => resultado += parseFloat(p.total || 0));
                return resultado;
            },
            totalAbonos(){
                let resultado = 0;
                if (!Array.isArray(this.arrayPedidos)) return 0;
                this.arrayPedidos.forEach(p => resultado += parseFloat(p.abono || 0));
                return resultado;
            }

        },
        methods: {
            generarfac(id,index){
                
                this.generarf=id
                this.arrayPedidos[index].empresa='Seleccione datos de facturación'
            },
            nogenerar(){
                this.generarf=-1
            },
            generarFactura(pedido)
            {
                let me = this;
                this.pedido.user_id=this.user.id
                this.pedido.lineas.forEach(l => {
                    if(!l.hasOwnProperty('detalles')){
                        l.detalles=0;
                    }else{
                    }
                    
                });
                
                const data = new FormData()
                data.set('data',JSON.stringify(pedido))
                axios.post('/factura/registrar',data)
                .then(function (response) {
                    console.log(response)
                    me.listarPedidos(1,'','',me.per_page);
                    me.$emit('listarFacturas', '1,'+me.pedido.estado+',"estado",50')
                    me.generarf=-1
                    me.pedido={'fecha':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'forma_pago':'Contado','transportadora':'','estado':2,'cliente':{'contactos':[],'empresas':[],'envios':[]},'productos':[], 'abono':0,'saldo':0,'subtotal':0, 'descuento':0, 'iva':0.19, 'total':0}
               }).catch(function (error) {
                    console.log(error);
                });
                me.$emit('ocultarDetalle', 1)
            },
            crearEmpresa(pedido){
                this.listado=3
                this.empresa.clientes.push(pedido.cliente)
                this.empresa.cliente=pedido.cliente
            },
            mostrarListado(value){
                this.listarPedidos(1,this.buscar,this.criterio,this.per_page);
                this.empresa={"id":0,"favorito":0,"razonsocial":"","tipo_persona":"Juridica","tipo_documento":"","numero":"","digito":"","direccion":"","telefono":"","correo":"","ciudad":"","departamento":"Valle del Cauca","pais":"Colombia","actividad":"","responsable":"","clientes":[]}
                
                this.seccion=value
                this.listado=1

            },
            imprimirPedido(pedido){
              let me=this;
              var ped=encodeURIComponent(JSON.stringify(pedido))
              console.log(ped)
              axios({
              url: '/imprimirPedido?id='+pedido.id,         
              method: 'GET',
              responseType: 'blob', // important
              }).then((response) => {
                console.log(response)
                  const url = window.URL.createObjectURL(new Blob([response.data]));
                  const link = document.createElement('a');
                  link.href = url;
                  link.setAttribute('download', 'pedido '+pedido.cliente.razonsocial+' '+pedido.id+'.pdf');
                  document.body.appendChild(link);
                  link.click();
              });
          },
          imprimirProforma(pedido) {
              window.open('/imprimirProforma?id=' + pedido.id, '_blank');
          },
          crearOVerProforma(pedido) {
              let me = this;
              Swal.fire({
                  title: 'Creando Proforma...',
                  text: 'Generando proforma oficial para el pedido #' + (pedido.num_comprobante || pedido.id),
                  allowOutsideClick: false,
                  didOpen: () => { Swal.showLoading(); }
              });
              axios.post('/comprobante/crearProformaDesdePedido', { pedido_id: pedido.id })
                  .then(function(response) {
                      Swal.fire({
                          icon: 'success',
                          title: '¡Proforma Creada!',
                          text: 'Proforma #' + response.data.num_comprobante + ' generada correctamente.',
                          timer: 2000,
                          showConfirmButton: false
                      });
                      window.open(response.data.pdf_url, '_blank');
                  })
                  .catch(function(error) {
                      Swal.fire('Error', 'No se pudo crear la proforma.', 'error');
                  });
          },
          abrirModalConfigProforma() {
              let me = this;
              const defaultTerminos = "• Documento generado como Factura Proforma para la solicitud y cobro de anticipo de producción.\n• Por favor tener en cuenta que el saldo final por pagar puede variar según ajustes de producción (+/- 10%).";
              const defaultNota = "Este documento es un comprobante de cotización / factura proforma sin efectos fiscales inmediatos.";

              axios.get('/proforma/consecutivo').then(response => {
                  me.proformaConfig.numero = response.data.numero_inicial;
                  me.proformaConfig.ultimo_generado = response.data.ultimo_generado;
                  me.proformaConfig.terminos = (response.data && response.data.terminos) ? response.data.terminos : defaultTerminos;
                  me.proformaConfig.nota_pie = (response.data && response.data.nota_pie) ? response.data.nota_pie : defaultNota;
                  me.modalConfigProforma = 1;
              }).catch(err => {
                  console.error(err);
                  if (!me.proformaConfig.terminos) me.proformaConfig.terminos = defaultTerminos;
                  if (!me.proformaConfig.nota_pie) me.proformaConfig.nota_pie = defaultNota;
                  me.modalConfigProforma = 1;
              });
          },
          cerrarModalConfigProforma() {
              this.modalConfigProforma = 0;
          },
          guardarConfigProforma() {
              let me = this;
              if (!me.proformaConfig.numero || me.proformaConfig.numero < 1) {
                  Swal.fire('Error', 'Ingrese un número consecutivo válido', 'warning');
                  return;
              }
              axios.post('/proforma/consecutivo', { 
                  numero: me.proformaConfig.numero,
                  terminos: me.proformaConfig.terminos,
                  nota_pie: me.proformaConfig.nota_pie
              }).then(response => {
                  Swal.fire('Guardado', 'Configuración de la factura proforma guardada correctamente', 'success');
                  me.cerrarModalConfigProforma();
              }).catch(err => {
                  Swal.fire('Error', 'No se pudo guardar la configuración', 'error');
              });
          },
             cambiarEstado(pedido){
                var me=this;
                if (String(pedido.estado) === '6') {
                    Swal.fire({
                        title: '¿Marcar como No Recogido?',
                        text: `El pedido #${pedido.id} pasará a estado No Recogido y saldrá de producción. Permanecerá activo en Cartera.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, marcar como No Recogido',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.value) {
                            me.ejecutarCambioEstado(pedido);
                        } else {
                            me.listarPedidos(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                        }
                    });
                    return;
                }
                me.ejecutarCambioEstado(pedido);
             },
             ejecutarCambioEstado(pedido){
                var me=this
                var userj=me.user
                var estadoAnterior = pedido.estado
                axios.put('/comprobante/cambiarEstado',{
                    'id':pedido.id,
                    'user_id':userj.id,
                    'estado':pedido.estado,
                })
                .then(function (response) {
                    if (me.criterio === 'estado' && me.buscar !== '' && String(pedido.estado) !== String(me.buscar)) {
                        var idx = me.arrayPedidos.findIndex(p => p.id === pedido.id);
                        if (idx > -1) {
                            me.arrayPedidos.splice(idx, 1);
                        }
                    }
                    var estados = {1:'Pendiente', 2:'En producción', 3:'Completado', 4:'Para entregar', 5:'Entregado', 6:'No recogido'};
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Pedido #' + pedido.id + ' → ' + (estados[pedido.estado] || pedido.estado),
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }).catch(function (error) {
                    console.log(error);
                    pedido.estado = estadoAnterior;
                    Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                });
             },
            generar(){
                 let me=this;
                var url= '/pedido/generar';
                axios.post(url).then(function (response) {
                    console.log(response)
                    
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
           
            buscarLive() {
                clearTimeout(this._searchTimer);
                let me = this;
                this._searchTimer = setTimeout(() => {
                    me.listarPedidos(1, me.buscar, me.criterio, me.per_page);
                }, 250);
            },
            cambiarEstadoTab(estado) {
                // Guarda la pestaña activa y limpia el buscador de texto
                this.estadoActivo = estado;
                this.buscar = '';
                // Pasa el estado como buscar para que el backend filtre correctamente
                this.listarPedidos(1, estado, 'estado', this.per_page);
            },
            listarPedidos(page, buscar, criterio, per_page){
                let me = this;
                let url;
                if (criterio === 'estado') {
                    // Filtro por pestaña: buscar contiene el valor de estado (o '' para Todos)
                    url = '/comprobante?page=' + page + '&per_page=' + per_page
                        + '&buscar=' + buscar + '&criterio=estado';
                } else {
                    // Búsqueda por cliente/fecha/id: combinar con pestaña activa
                    url = '/comprobante?page=' + page + '&per_page=' + per_page
                        + '&buscar=' + encodeURIComponent(buscar) + '&criterio=' + criterio
                        + '&estado_filtro=' + me.estadoActivo;
                }
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayPedidos = respuesta.comprobantes.data;
                    if (me.pedido && me.pedido.id) {
                        let updated = me.arrayPedidos.find(p => p.id === me.pedido.id);
                        if (updated) me.pedido = updated;
                    }
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
           
            
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarPedidos(page,buscar,criterio,me.per_page);
            },
            borrarPedido(id){
                const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de borrar este item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;
                    var userj=me.user
                    var url= '/pedido/borrar?id='+ id+'&user_id='+userj.id;
                    axios.delete(url,{'_method': 'DELETE'})
                    .then(function (response) {
                        me.listarPedidos(1,me.buscar,me.criterio,me.per_page);
                        swal(
                        'Eliminado!',
                        'El item ha sido eliminado con éxito.',
                        'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });
                    
                    
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                    
                }
                }) 
                
            },
            verPedido(pedido){
                this.pedido=pedido
                this.listado=0
                this.edit=1
            },
            entrega(pedido){
                this.pedido=pedido
                this.listado=4
            },
            mostrarDetalle(){
                let day = String(new Date().getDate()).padStart(2, '0');
                let month = String(new Date().getMonth() + 1).padStart(2, '0');
                let fechaActual = `${new Date().getFullYear()}-${month}-${day}`;
                this.edit = 0;
                this.pedido = {
                    'fecha': fechaActual,
                    'forma_pago': 'Contado',
                    'transportadora': '',
                    'estado': 2,
                    'cliente': { 'id': 0, 'razonsocial': '', 'contactos': [], 'empresas': [], 'envios': [] },
                    'lineas': [],
                    'iva': 0.19,
                    'subtotal': 0,
                    'abono': 0,
                    'saldo': 0,
                    'descuento': 0,
                    'impuestos': 0,
                    'total': 0
                };
                this.listado = 0;
            },
            ocultarDetalle(val,val2){
                this.listado = val;
                this.edit = 0;
                let day = String(new Date().getDate()).padStart(2, '0');
                let month = String(new Date().getMonth() + 1).padStart(2, '0');
                let fechaActual = `${new Date().getFullYear()}-${month}-${day}`;
                this.pedido = {
                    'fecha': fechaActual,
                    'forma_pago': 'Contado',
                    'transportadora': '',
                    'estado': 2,
                    'cliente': { 'id': 0, 'razonsocial': '', 'contactos': [], 'empresas': [], 'envios': [] },
                    'lineas': [],
                    'iva': 0.19,
                    'subtotal': 0,
                    'abono': 0,
                    'saldo': 0,
                    'descuento': 0,
                    'impuestos': 0,
                    'total': 0
                };
                if (val2) {
                    this.listarPedidos(1, val2, "estado", 50);
                } else {
                    this.listarPedidos(1, this.buscar, this.criterio, this.per_page);
                }
            },
           
            cerrarModal(){
                this.modal=0;
            },
            alternarGrupo(grupoId) {
                const index = this.expandedGroups.indexOf(grupoId);
                if (index > -1) {
                    this.expandedGroups.splice(index, 1);
                } else {
                    this.expandedGroups.push(grupoId);
                }
            },
            estaExpandido(grupoId) {
                return this.expandedGroups.indexOf(grupoId) > -1;
            },
            forNum(num) {
                let val = parseFloat(num);
                if (isNaN(val)) val = 0;
                return new Intl.NumberFormat("es-CO").format(val);
            },
            abrirModalPago(pedido) {
                this.modalPago = 1;
                this.pagoData.es_masivo = false;
                this.pagoData.comprobante_id = pedido.id;
                this.pagoData.cliente_id = pedido.cliente.id;
                this.pagoData.num_comprobante = pedido.num_comprobante || pedido.id;
                this.pagoData.total = pedido.total;
                this.pagoData.saldo = pedido.saldo;
                this.pagoData.monto = pedido.saldo;
                this.pagoData.fecha = `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`;
                this.pagoData.observaciones = '';
                this.listarPagos(pedido.id);
            },
            abrirModalPagoCliente(grupo) {
                this.modalPago = 1;
                this.pagoData.es_masivo = true;
                this.pagoData.cliente_id = grupo.cliente.id;
                this.pagoData.num_comprobante = `TOTAL ${grupo.cliente.razonsocial}`;
                this.pagoData.total = grupo.total_venta;
                this.pagoData.saldo = grupo.total_saldo;
                this.pagoData.monto = grupo.total_saldo;
                this.pagoData.fecha = `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`;
                this.pagoData.observaciones = 'Pago Masivo Carterizado';
            },
            cerrarModalPago() {
                this.modalPago = 0;
            },
            registrarPago() {
                let me = this;
                if (me.pagoData.monto <= 0) {
                    Swal.fire('Error', 'Ingrese un monto válido', 'error');
                    return;
                }
                const url = me.pagoData.es_masivo ? '/orden/registrarPagoMasivo' : '/orden/registrarPago';
                me.loading = true;
                axios.post(url, me.pagoData)
                    .then(function(response) {
                        me.loading = false;
                        Swal.fire('Éxito', 'Pago registrado correctamente', 'success');
                        me.cerrarModalPago();
                        me.listarPedidos(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                        if (response.data.recibo_id) {
                            me.verReciboPdf(response.data.recibo_id);
                        }
                    })
                    .catch(function(error) {
                        me.loading = false;
                        Swal.fire('Error', 'No se pudo registrar el pago', 'error');
                    });
            },
            verReciboPdf(id) {
                window.open('/orden/reciboPdf/' + id, '_blank');
            },
            entregarTodo(grupo) {
                this.grupoSeleccionado = grupo;
                this.listado = 5;
            },
            convertirAbonoEnRecibo(pedido) {
                if(pedido.abono <= 0) {
                    Swal.fire('Atención', 'Este pedido no tiene un abono válido superior a 0 para generar el recibo.', 'warning');
                    return;
                }
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: { confirmButton: 'btn btn-success ml-2', cancelButton: 'btn btn-danger' },
                    buttonsStyling: false
                });

                swalWithBootstrapButtons.fire({
                    title: '¿Migrar abono a un Recibo?',
                    text: 'Esta opción creará un Recibo de Pago físico por valor de $'+this.forNum(pedido.abono)+' en este pedido, legalizando el saldo como se hace en la nueva versión del sistema.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Crear Recibo',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        let me = this;
                        axios.post('/orden/convertirAbonoEnRecibo', { id: pedido.id, abono: pedido.abono })
                        .then(response => {
                            if (response.data.success) {
                                Swal.fire('¡Éxito!', 'Se ha creado el Recibo de Pago y cruzado matemáticamente.', 'success');
                                me.listarPedidos(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                                me.listarPagos(pedido.id);
                            } else {
                                Swal.fire('Error', response.data.error || 'Ocurrió un error', 'error');
                            }
                        }).catch(error => {
                            Swal.fire('Error', 'Error de comunicación', 'error');
                        });
                    }
                });
            },
            listarPagos(id) {
                let me = this;
                me.loading = true;
                axios.get('/orden/listarPagos/' + id).then(function(response) {
                    me.arrayPagos = response.data;
                    me.loading = false;
                }).catch(err => {
                    me.loading = false;
                    console.error(err);
                });
            },
            abrirModalEditarRecibo(recibo) {
                 this.modalEditarRecibo = 1;
                 this.reciboEditData = {
                     id: recibo.id,
                     fecha: recibo.fecha,
                     monto: recibo.monto,
                     num_recibo: recibo.num_recibo,
                     forma_pago: recibo.forma_pago || 'Efectivo',
                     observaciones: recibo.observaciones || ''
                 };
            },
            cerrarModalEditarRecibo() {
                this.modalEditarRecibo = 0;
            },
            actualizarRecibo() {
                let me = this;
                me.loading = true;
                axios.put('/orden/actualizarRecibo', me.reciboEditData)
                    .then(function(response) {
                        me.loading = false;
                        Swal.fire('Actualizado', 'Recibo actualizado correctamente', 'success');
                        me.modalEditarRecibo = 0;
                        me.listarPagos(me.pagoData.comprobante_id);
                        me.listarPedidos(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                    })
                    .catch(function(error) {
                        me.loading = false;
                        let msg = error.response && error.response.data && error.response.data.error ? error.response.data.error : 'No se pudo actualizar';
                        Swal.fire('Error', msg, 'error');
                    });
            },
            eliminarRecibo(id) {
                let me = this;
                Swal.fire({
                    title: '¿Eliminar Recibo?',
                    text: "Se revertirán todos los cruces y el saldo volverá a las facturas. Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        me.loading = true;
                        axios.delete('/orden/eliminarRecibo', {
                            data: { id: id }
                        }).then(function(response) {
                            me.loading = false;
                            Swal.fire('Eliminado', 'El recibo ha sido eliminado y los saldos revertidos.', 'success');
                            me.listarPagos(me.pagoData.comprobante_id);
                            me.listarPedidos(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                        }).catch(function(error) {
                            me.loading = false;
                            console.error(error);
                            Swal.fire('Error', 'No se pudo eliminar el recibo', 'error');
                        });
                    }
                });
            },
          
        },
        mounted() {
            this.listarPedidos(1,this.buscar,this.criterio,this.per_page);
        }
    }
</script>
<style>    
    .active{
        color:red;
    }
    .modal-content{
        width: 100% !important;
        position: relative !important;
        border-radius: 8px !important;
    }
    .mostrar{
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1050 !important;
        background-color: rgba(0,0,0,0.6) !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }
    .modal-dialog {
        width: 100%;
        margin: 1.75rem auto;
        pointer-events: auto !important;
    }
    @media (min-width: 576px) {
        .modal-dialog {
            max-width: 800px;
        }
        .modal-lg {
            max-width: 1100px !important;
        }
    }
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

    /* Modern Table Styles */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 8px;
        background: #f8fafc;
    }
    .table-modern thead th {
        border: none;
        background: white;
        color: #64748b;
        text-transform: uppercase;
        font-size: 0.70rem;
        letter-spacing: 0.05em;
        padding: 12px 15px;
        font-weight: 700;
    }
    .group-row-premium {
        background: #e0f2fe !important;
        border-left: 4px solid #0284c7;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .group-row-premium:hover {
        background: #d0eafb !important;
    }
    .row-detail-glass {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(5px);
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
    }
    .row-detail-glass:hover {
        background: white !important;
        transform: scale(1.002);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .summary-row-dark {
        background: #334155 !important;
        color: white;
        font-size: 0.9rem;
    }
    .rounded-xl { border-radius: 12px !important; }
    .dash-border { border: 2px dashed #e2e8f0 !important; }
    .actions-cell {
        white-space: nowrap;
        width: 1%;
    }
</style>
