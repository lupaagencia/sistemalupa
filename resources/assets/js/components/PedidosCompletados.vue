<template>
    <div class="contenedor-seccion shadow-lg rounded-xl overflow-hidden bg-white mt-3">
        <div class="seccion-header-premium py-2 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border-bottom: 2px solid #818cf8;">
            <div class="d-flex align-items-center">
                <div class="header-icon-box mr-2" style="background: rgba(129, 140, 248, 0.2); border: 1px solid rgba(129, 140, 248, 0.3); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                    <i class="fa fa-check-circle text-white small"></i>
                </div>
                <h5 class="text-white mb-0 font-weight-bold" style="font-size: 1rem;">PEDIDOS COMPLETADOS</h5>
            </div>
            <div class="header-summary d-none d-md-flex">
                <div class="summary-item mr-4">
                    <span class="text-white-50 small d-block text-uppercase">Total Valor</span>
                    <span class="text-white font-weight-bold h5 mb-0 font-premium">${{ forNum(totalValor) }}</span>
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
                         style="width: 100%; background: #9b5bb3; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        
                        <!-- Header de la tarjeta -->
                        <div class="px-4 pt-4 pb-2 d-flex justify-content-between align-items-start">
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Pedido #{{ pedido.id }}</span>
                                    <span class="badge badge-glass px-2 py-1 small text-white opacity-9">COMPLETADO</span>
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
                            <div class="valor-k-badge">
                                {{ formatK(pedido.total) }}
                            </div>
                        </div>

                        <!-- Inner Content Box -->
                        <div class="mx-3 mb-3 p-3 bg-white rounded-lg flex-grow-1 shadow-inner-premium d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center small mb-1 pb-1 border-bottom">
                                <span class="text-muted font-weight-bold">ENTREGA ESTIMADA</span>
                                <span class="badge badge-light-blue text-blue-800 font-weight-bold">{{ pedido.fecha }}</span>
                            </div>
                            
                            <div class="items-viewport flex-grow-1 custom-scrollbar" style="max-height: 200px; overflow-y: auto;">
                                <template v-if="pedido.lineas && pedido.lineas.length">
                                    <div v-for="linea in pedido.lineas" :key="linea.id" 
                                         class="d-flex align-items-center py-2 border-bottom-light last-no-border">
                                        <div class="qty-pill mr-2">{{ linea.cantidad }}</div>
                                        <div class="item-details text-dark flex-grow-1 d-flex justify-content-between align-items-center overflow-hidden">
                                            <div class="font-weight-bold text-truncate pr-2" style="font-size: 0.9rem;">
                                                {{ linea.articulo ? linea.articulo.nombre : 'Producto' }}
                                            </div>
                                            <span class="text-muted smallest font-weight-bold flex-shrink-0">OT #{{ linea.ordentrabajo_id }}</span>
                                        </div>
                                    </div>
                                </template>
                                <div v-else class="h-100 d-flex align-items-center justify-content-center flex-column text-muted opacity-5 py-4">
                                    <i class="fa fa-shopping-cart fa-2x mb-2"></i>
                                    <span class="small italic">Sin artículos</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column mt-auto">
                            <button class="btn-action-premium btn-green-glow w-100" @click="cambiarEstado(pedido, 4)">
                                <span>COLOCAR PARA DESPACHAR</span>
                                <i class="fa fa-arrow-right ml-2 mr-0"></i>
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
                    <i class="fa fa-check-circle fa-4x mb-3 opacity-2"></i>
                    <h5>No hay pedidos completados pendientes</h5>
                    <p>Los pedidos aparecerán aquí cuando hayan pasado todas las etapas de producción.</p>
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
                expandedGroups: []
            }
        },
        components: {
            vSelect,
            pedido
        },
        computed:{
            totalValor(){
                let resultado = 0;
                this.arrayPedidos.forEach(p => resultado += parseFloat(p.total || 0));
                return resultado;
            },
            pedidosAgrupados() {
                const grupos = {};
                this.arrayPedidos.forEach(pedido => {
                    const clienteId = pedido.cliente_id;
                    if (!grupos[clienteId]) {
                        grupos[clienteId] = {
                            cliente: pedido.cliente,
                            pedidos: []
                        };
                    }
                    grupos[clienteId].pedidos.push(pedido);
                });
                return Object.values(grupos);
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
                    text: `El pedido #${pedido.id} ${clienteNombre ? 'de "' + clienteNombre + '"' : ''} pasará a estado No Recogido y saldrá de producción. Se mantendrá activo en Cartera.`,
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
                var url= '/comprobante/completado';
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
            despacharTodo(grupo) {
                const me = this;
                Swal.fire({
                    title: '¿Despachar todos?',
                    text: `Se marcarán como DESPACHADOS todos los pedidos de ${grupo.cliente.razonsocial}.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, despachar todos'
                }).then((result) => {
                    if (result.value) {
                        let promises = grupo.pedidos.map(p => {
                            return axios.put('/comprobante/cambiarEstado', {
                                'id': p.id,
                                'user_id': me.user.id,
                                'estado': 4
                            });
                        });
                        Promise.all(promises).then(() => {
                            Swal.fire('Completado', 'Pedidos actualizados con éxito', 'success');
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
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .valor-k-badge {
        background: rgba(0, 0, 0, 0.2);
        color: #fff;
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .shadow-inner-premium {
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.03), 0 1px 2px rgba(0,0,0,0.05);
    }
    
    .qty-pill {
        background: #f1f5f9;
        color: #475569;
        font-weight: 800;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 6px;
        min-width: 60px;
        text-align: center;
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
    
    .btn-green-glow {
        background: linear-gradient(135deg, #7ad26f 0%, #33e034 100%);
        box-shadow: 0 -4px 15px rgba(92, 184, 92, 0.1);
    }
    
    .btn-green-glow:hover {
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
        border-radius: 4px;
    }
</style>

