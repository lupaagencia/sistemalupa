<template>
    <div class="contenedor-seccion shadow-lg rounded-xl overflow-hidden bg-white mt-3">
        <div class="seccion-header-premium py-2 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); border-bottom: 2px solid #10b981;">
            <div class="d-flex align-items-center">
                <div class="header-icon-box mr-2" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.3); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                    <i class="fa fa-truck text-white small"></i>
                </div>
                <h5 class="text-white mb-0 font-weight-bold" style="font-size: 1rem;">PEDIDOS PARA ENTREGAR</h5>
            </div>
            <div class="header-summary d-none d-md-flex">
                <div class="summary-item mr-4">
                    <span class="text-white-50 small d-block text-uppercase">Total Pendiente</span>
                    <span class="text-white font-weight-bold h5 mb-0 font-premium">${{ forNum(totalCartera) }}</span>
                </div>
                <div class="summary-item">
                    <span class="text-white-50 small d-block text-uppercase">Clientes</span>
                    <span class="text-white font-weight-bold h5 mb-0 font-premium">{{ pedidosAgrupados.length }}</span>
                </div>
            </div>
        </div>

        <div class="card-body p-2 bg-light">
            <div v-if="arrayPedidos.length > 0" class="d-flex flex-wrap justify-content-start" style="width: 100%;">
                <div v-for="pedido in arrayPedidos" :key="pedido.id" class="pedido-card-container" style="flex: 0 0 32.3%; margin: 0.5%; min-width: 250px;">
                    <div class="pedido-card border-0 shadow-lg rounded-xl overflow-hidden h-100 d-flex flex-column" 
                         style="width: 100%; background: #33e034; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        
                        <!-- Header de la tarjeta -->
                        <div class="px-4 pt-4 pb-2 d-flex justify-content-between align-items-start">
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Pedido #{{ pedido.id }}</span>
                                    <span class="badge badge-glass px-2 py-1 small text-white opacity-9">LISTO PARA ENTREGA</span>
                                </div>
                                <h4 class="text-white font-weight-extra-bold mb-0 text-truncate" style="font-size: 1.4rem; letter-spacing: -0.5px;">
                                    {{ pedido.cliente ? pedido.cliente.razonsocial : '---' }}
                                </h4>
                            </div>
                        </div>
                        
                        <!-- Middle section: Stats Bar -->
                        <div class="px-4 pb-3 d-flex align-items-center justify-content-between mt-2">
                            <div class="d-flex align-items-center">
                                <span v-if="pedido.transportadora && pedido.transportadora!='e' && pedido.transportadora!=' '" 
                                      class="badge-premium-status bg-white text-primary mr-2">
                                    <i class="fa fa-truck mr-1 small"></i>{{ pedido.transportadora }}
                                </span>
                                <span v-else-if="pedido.transportadora=='e'" class="badge-premium-status bg-white text-purple mr-2">
                                    <i class="fa fa-user mr-1 small"></i>E. CLIENTE
                                </span>
                                <span v-else class="badge-premium-status bg-white text-success mr-2">
                                    <i class="fa fa-building mr-1 small"></i>RECOGEN
                                </span>
                            </div>
                            <div class="valor-k-badge-orange">
                                {{ formatK(pedido.total) }}
                            </div>
                        </div>

                        <!-- Inner Content Box -->
                        <div class="mx-3 mb-3 p-3 bg-white rounded-lg flex-grow-1 shadow-inner-premium d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center small mb-1 pb-1 border-bottom">
                                <span class="text-muted font-weight-bold uppercase-xs">Fecha Estimada</span>
                                <span class="badge badge-light-blue text-blue-800 font-weight-bold">{{ pedido.fecha }}</span>
                            </div>
                            
                            <div class="items-viewport flex-grow-1 custom-scrollbar" style="max-height: 200px; overflow-y: auto;">
                                <template v-if="pedido.lineas && pedido.lineas.length">
                                    <div v-for="linea in pedido.lineas" :key="linea.id" 
                                         class="d-flex align-items-center py-2 border-bottom-light last-no-border">
                                        <div class="qty-pill-green mr-2">{{ linea.cantidad }}</div>
                                        <div class="item-details text-dark flex-grow-1 d-flex justify-content-between align-items-center overflow-hidden">
                                            <div class="font-weight-bold text-truncate pr-2" style="font-size: 0.9rem;">
                                                {{ linea.articulo ? linea.articulo.nombre : 'Producto' }}
                                            </div>
                                            <span class="text-muted smallest font-weight-bold flex-shrink-0">OT #{{ linea.ordentrabajo_id }}</span>
                                        </div>
                                    </div>
                                </template>
                                <div v-else class="h-100 d-flex align-items-center justify-content-center flex-column text-muted opacity-5 py-4">
                                    <i class="fa fa-box-open fa-2x mb-2"></i>
                                    <span class="small italic">Sin ítems</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column">
                            <button class="btn-action-premium btn-orange-glow w-100" @click="cambiarEstado(pedido, 5)">
                                <span>MARCAR ENTREGADO</span>
                                <i class="fa fa-check-circle ml-2"></i>
                            </button>
                            <div class="text-center mt-2 mb-1">
                                <a href="#" class="text-danger small font-weight-bold text-decoration-none opacity-8 hover-opacity-100" @click.prevent="marcarNoRecogido(pedido)">
                                    <i class="fa fa-exclamation-triangle mr-1"></i>Marcar como No Recogido
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No results -->
            <div v-else class="text-center py-5">
                <div class="empty-state text-muted">
                    <i class="fa fa-truck fa-4x mb-3 opacity-2"></i>
                    <h5>No hay pedidos listos para entregar</h5>
                    <p>Los pedidos aparecerán aquí cuando su estado sea "Empacado".</p>
                </div>
            </div>
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
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-3 text-center">Resumen del Pago</h6>
                                    <div class="bg-white rounded p-3 shadow-sm border mb-3">
                                        <div class="d-flex justify-content-between mb-2 small">
                                            <span>Documento:</span> <strong>#{{pagoData.num_comprobante}}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total:</span> <strong>${{ forNum(pagoData.total) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-danger font-weight-bold">
                                            <span>Saldo Pendiente:</span> <span>${{ forNum(pagoData.saldo) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">Monto a pagar</label>
                                        <div class="input-group shadow-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-light border-right-0">$</span></div>
                                            <input type="number" class="form-control font-weight-bold text-success border-left-0" v-model="pagoData.monto">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">Forma de Pago</label>
                                        <select class="form-control shadow-sm" v-model="pagoData.forma_pago">
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Banco">Banco / Transferencia</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-success btn-block btn-lg shadow font-weight-bold mt-4" @click="registrarPago()" :disabled="loading">
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

                                    <div v-else class="text-center py-5 bg-white rounded border dash-border text-muted small italic">
                                        No hay pagos previos para este pedido.
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
    </div>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import vSelect from 'vue-select';
    import pedido from './partes/Pedido'
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    var fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
    
    export default {
        props:['user','show'],
        data (){
            return {
                edit:0,
                listado:1,
                arrayPedidos : [],
                offset : 3,
                criterio : 'cliente_id',
                buscar : '',
                criterioA:'nombre',
                buscarA:'',
                pedido:{'fecha':fecha,'forma_pago':'Contado','transportadora':' ','cliente':{'contactos':[],'empresas':[],'envios':[]},'lineas':[],'iva':0,'subtotal':0, 'abono':0, 'saldo':0, 'descuento':0, 'impuestos':0, 'total':0},
                expandedGroups: [],
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
                    total: 0,
                    saldo: 0
                },
                arrayPagos: [],
                modalEditarRecibo: 0,
                reciboEditData: {
                    id: 0,
                    fecha: '',
                    monto: 0,
                    num_recibo: '',
                    forma_pago: '',
                    observaciones: ''
                },
            }
        },
        components: {
            vSelect,
            pedido
        },
        computed:{
             pedidosAgrupados() {
                 let grupos = {};
                 if (!Array.isArray(this.arrayPedidos)) return [];
                 this.arrayPedidos.forEach(p => {
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
                     grupos[cid].total_saldo += parseFloat(p.saldo || 0);
                     grupos[cid].pedidos.push(p);
                 });
                 return Object.values(grupos);
             },
            totalCartera(){
                let resultado = 0;
                this.arrayPedidos.forEach(p => resultado += parseFloat(p.saldo || 0));
                return resultado;
            }
        },
        methods : {
            formatK(num) {
                let val = parseFloat(num);
                if (isNaN(val)) return '0K';
                // Redondear hacia arriba al siguiente múltiplo de 50 (para que 675 -> 700)
                let kValue = Math.ceil(val / 50000) * 50;
                return kValue + 'K';
            },
            generarGuia(datosenvio,cliente){
                let me=this;
                if(datosenvio.empresa=='' || datosenvio.empresa==null){
                    datosenvio.empresa=cliente;
                }
                axios({
                url: '/generarGuia?guia='+encodeURIComponent(JSON.stringify(datosenvio)),         
                method: 'GET',
                responseType: 'blob',
                }).then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'guia '+datosenvio.empresa+'.pdf');
                    document.body.appendChild(link);
                    link.click();
                });
            },
            cambiarEstado(pedido, estado){
                var me=this
                axios.put('/comprobante/cambiarEstado',{
                    'id':pedido.id,
                    'user_id':me.user.id,
                    'estado':estado,
                })
                .then(function (response) {
                    me.listarPedidos();
                }).catch(function (error) {
                    console.log(error);
                });
            },
            marcarNoRecogido(pedido) {
                const me = this;
                const clienteNombre = pedido.cliente ? (pedido.cliente.razonsocial || pedido.cliente.nombre || '') : '';
                Swal.fire({
                    title: '¿Marcar como No Recogido?',
                    text: `El pedido #${pedido.id} ${clienteNombre ? 'de "' + clienteNombre + '"' : ''} pasará a estado No Recogido y saldrá de entregas. Se mantendrá activo en Cartera.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, marcar como No Recogido',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        me.cambiarEstado(pedido, 6);
                    }
                });
            },
            listarPedidos(){
                let me=this;
                var url= '/comprobante/entregar';
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPedidos = respuesta.comprobantes.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
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
                this.pagoData.num_comprobante = pedido.id;
                this.pagoData.total = pedido.total;
                this.pagoData.saldo = pedido.saldo;
                this.pagoData.monto = pedido.saldo;
                this.pagoData.fecha = `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                this.pagoData.observaciones = '';
                this.listarPagos(pedido.id);
            },
            abrirModalPagoCliente(grupo) {
                this.modalPago = 1;
                this.pagoData.es_masivo = true;
                this.pagoData.cliente_id = grupo.cliente.id;
                this.pagoData.num_comprobante = `MASIVO ${grupo.cliente.razonsocial}`;
                this.pagoData.total = grupo.total_venta;
                this.pagoData.saldo = grupo.total_saldo;
                this.pagoData.monto = grupo.total_saldo;
                this.pagoData.fecha = `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                this.pagoData.observaciones = 'Pago Masivo de Pedidos';
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
                        me.listarPedidos();
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
                        me.listarPedidos();
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
                            me.listarPedidos();
                        }).catch(function(error) {
                            me.loading = false;
                            console.error(error);
                            Swal.fire('Error', 'No se pudo eliminar el recibo', 'error');
                        });
                    }
                });
            },
            entregarTodo(grupo) {
                const me = this;
                Swal.fire({
                    title: '¿Entregar todos?',
                    text: `Se marcarán como ENTREGADOS todos los pedidos de ${grupo.cliente.razonsocial}.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, entregar todos'
                }).then((result) => {
                    if (result.value) {
                        let promises = grupo.pedidos.map(p => {
                            return axios.put('/comprobante/cambiarEstado', {
                                'id': p.id,
                                'user_id': me.user.id,
                                'estado': 5
                            });
                        });
                        Promise.all(promises).then(() => {
                            Swal.fire('Entregado', 'Pedidos actualizados con éxito', 'success');
                            me.listarPedidos();
                        });
                    }
                });
            }
        },
        mounted() {
            this.listarPedidos();
            this.intervalo = setInterval(() => {
                this.listarPedidos();
            }, 5000);
        },
        beforeDestroy() {
            if (this.intervalo) clearInterval(this.intervalo);
        }
    }
</script>

<style>   
    .font-weight-extra-bold { font-weight: 800; }
    .tracking-wider { letter-spacing: 0.05em; }
    .opacity-9 { opacity: 0.9; }
    .opacity-5 { opacity: 0.5; }
    .uppercase-xs { text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px; }
    
    .pedido-card {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    
    .pedido-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 35px -7px rgba(0, 0, 0, 0.2);
    }
    
    .badge-glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 6px;
    }
    
    .badge-premium-status {
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .valor-k-badge-orange {
        background: #fef3c7;
        color: #92400e;
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .shadow-inner-premium {
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.03), 0 1px 2px rgba(0,0,0,0.05);
    }
    
    .qty-pill-green {
        background: #f0fdf4;
        color: #166534;
        font-weight: 800;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 6px;
        min-width: 60px;
        text-align: center;
        border: 1px solid #dcfce7;
    }
    
    .border-bottom-light {
        border-bottom: 1px solid #f8fafc;
    }
    
    .last-no-border:last-child {
        border-bottom: none !important;
    }
    
    .btn-action-premium {
        width: 100%;
        border: none;
        padding: 20px;
        font-weight: 800;
        font-size: 0.85rem;
        color: white;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .btn-orange-glow {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        box-shadow: 0 -4px 15px rgba(245, 158, 11, 0.15);
        color: #fff;
    }
    
    .btn-orange-glow:hover {
        filter: brightness(1.05);
        padding-top: 22px;
        padding-bottom: 22px;
    }
    
    .rounded-xl { border-radius: 20px !important; }
    .rounded-lg { border-radius: 12px !important; }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    .line-height-1 { line-height: 1.2; }
    .smallest { font-size: 0.7rem; }
    
    .badge-light-blue {
        background: #e0f2fe;
        color: #0369a1;
        padding: 2px 8px;
        border-radius: 8px;
    }
</style>

