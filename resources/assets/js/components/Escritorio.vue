<template>
    <div class="main">
        <div class="content">
            <div class="row">
                
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card card-stats">
                    
                        <div class="card-header spg">
                            <div class="stats">
                                <h3>
                                    <p>Estado de producción 1</p>
                                </h3>  
                            </div>
                            
                            <div class="header-actions d-flex align-items-center">
                                <mensajedeldia></mensajedeldia>
                            </div>
                        </div>
                        <div class="card-body">
                            <statuspro :user="user" @nav="irA"></statuspro>
                        </div>
                        <div class="card-header">
                                <pedidoscompletados :user='user' :show='show' ref="pedidoscompletados"></pedidoscompletados>
                        </div>
                        <div class="card-header">
                                <pedidosentregar :user='user' :show='show' ref="pedidosentregar"></pedidosentregar>
                        </div>
                       
                        <!-- <div class="card-header">
                                <seleccionmaquina></seleccionmaquina>
                        </div> -->
                        <!-- <div class="card-header">
                                <calculotamano></calculotamano>
                        </div> -->
                        <!-- <div class="card-header">
                                <posiblescortes></posiblescortes>
                        </div> -->
                    </div>
                </div>
            
            </div>
           
          
        </div>
      
    </div>
</template>

<script>
    import statuspro from './Statuspro.vue'
    import cotizador from './partes/Cotizador.vue'
    import calculotamano from './partes/CalculoMedida.vue'
    import seleccionmaquina from './partes/SeleccionMaquina.vue'
    import posiblescortes from './partes/PosiblesCortes.vue'
    import pedidosentregar from './PedidosEntregar'
    import pedidoscompletados from './PedidosCompletados'
    import mensajedeldia from './partes/MensajeDelDia'
    
    export default {
         props:['user'],
        data (){
            return {
                arrayGuias:[],
                show: 'pedidos',
                alertaCompletadosCount: 0,
                alertaEntregadosCount: 0,
            }
        },
        
        components: {
            statuspro,
            cotizador,
            pedidosentregar,
            pedidoscompletados,
            calculotamano,
            seleccionmaquina,
            posiblescortes,
            mensajedeldia
        },
        computed:{
           
        },
        methods : {
            irA(refName) {
                const element = this.$refs[refName];
                if (element && element.$el) {
                    element.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            },
            irACartera(tab) {
                // Guarda la pestaña en localStorage para que Cartera.vue la lea
                localStorage.setItem('filtroCarteraInicial', tab);
                // Cambia el menú a Cartera
                if (this.$root && typeof this.$root.menu !== 'undefined') {
                    this.$root.menu = 16;
                } else {
                    window.location.reload(); // fallback
                }
            },
            cargarAlertaCartera() {
                let me = this;
                axios.get('/orden/alertaCartera').then(function(response) {
                    me.alertaCompletadosCount = response.data.completados.count;
                    me.alertaEntregadosCount = response.data.entregados.count;
                }).catch(function (error) {
                    console.log(error);
                });
            },
            formatoMoneda(value) {
                if(!value) return 0;
                let val = (value/1).toFixed(0).replace('.', ',')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            }
        },
        mounted() {
            this.cargarAlertaCartera();
        }
    }
</script>
<style>   

    .card-header{
        padding: 5px 20px;
    }
    .spg{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: end;
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
    .guias{
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        list-style: none;
       
        margin:0;
        
    }

    .guias li{
        cursor:pointer;
        margin-right: 5px;
        margin-bottom: 0px;
    }
    .btn-warning{
        font-size: 12px;
    }
    .btn-warning:hover{
        color:#fff;
    }
    .stats{
        font-size:16px !important;
    }
    .stats h3{
        margin-bottom: 0;
    }
     .enproduccion{
      background:#b7e4c1 !important;
    }
    .empacado{
      background: #b7e4e2 !important;
    }
    .entregar{
      background:#e3b7e4 !important;
    }
    .diseno{
    background:#e4d6b7 !important;
    }
    .enviarp{
      background:#d9e4b7 !important;
    }
    .infoproduccion{
        display: flex;
        flex-direction: row;
        padding: 0;
        list-style: none;
    }
    .infoproduccion li{
        width: auto;
        padding: 0 10px;
        font-weight: bold;
    }
    .tituloinfoproduccion{
        font-weight: bold;
        font-size: 16px;
    }
    @media (max-width: 768px) {
        .spg {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
            justify-content: space-between;
            margin-top: 10px;
        }
    }
</style>
