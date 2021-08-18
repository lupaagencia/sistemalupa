<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Cartera
                       
                    </div>
                    <!-- Listado-->

                    <template v-if="listado===1">
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="col-xl-12">
                                        <div class="input-group">
                                            <input type="text" v-model="buscare" @keyup="listarOrdenesCartera(1,`%${buscare}%`,'like','personas.nombre')"  placeholder="Empresa">
                                            <input type="text" v-model="buscarc" @keyup="listarOrdenesCartera(1,`%${buscarc}%`,'like','clientes.contacto')"  placeholder="Contacto">
                                            <input type="text" v-model="buscarv" @keyup="listarOrdenesCartera(1,`%${buscarv}%`,'like','ordentrabajos.total')"  placeholder="Valor orden">
                                            
                                            <div class="input-group">
                                                <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalIntervalo}">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Asiganr Intervalo</h5>
                                                                <button @click="cerrarModalIntervalo" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div v-text="`${fechaI}-${fechaF}`"></div>
                                                                <div class="inputGruop">
                                                                    <input type="date" placeholder="Fecha Inicial" v-model="fechaI">
                                                                    <input type="date" placeholder="Fecha Final" v-model="fechaF">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button @click="cerrarModalIntervalo" type="button" class="btn btn-secondary">Cerrar</button>
                                                                <button @click="filtrarFecha()" type="button" class="btn btn-primary">Aplicar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>  
                                                <select @change="filtrarFecha()" v-model="filtroFecha">
                                                    <option value="1">Filtrar por fecha</option>   
                                                    <option value="hoy">Hoy</option>   
                                                    <option value="ayer">Ayer</option>  
                                                    <option value="ultimos7">Ultimos 7 días</option>  
                                                    <option value="ultimos30">Ultimos 30 días</option>  
                                                    <option value="semana">Esta semana</option>  
                                                    <option value="mes">Este mes</option>  
                                                </select>
                                                <button class="btn btn-primary" @click="asiganarIntervalo()">Intervalo fecha</button>
                                                <button class="btn btn-warning" @click="imprimirOrden">Imprimir</button>
                                            </div>
                                            
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    
                                    <thead>
                                        <tr>
                                            <th>Opciones</th>
                                            <th @click="ordenar('idorden')">No. orden <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('fecha')">Fecha<i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('cliente')">Cliente <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('articulo')">Trabajo <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('contacto')">Valor Venta<i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('telefono_contacto')">Pago <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('carpeta_cliente')">Saldo <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayOrdenes.length">
                                        <tr v-for="(orden, index) in arrayOrdenes" :key="index">
                                            
                                            <td>
                                                <button type="button" @click="verOrden(orden)" class="btn btn-primary btn-sm">
                                                <i class="icon-eye"></i>
                                                </button> &nbsp;
                                            </td>
                                            <td v-text="orden.idorden"></td>
                                            <td v-text="orden.fecha"></td>
                                            <td v-text="orden.rasonsocial"></td>
                                            <td v-text="orden.articulo"></td>
                                            <td v-text="`$${forNum(orden.total)}`"></td>
                                            <td><input type="text" @change="cambiarAbono(orden)" v-model="orden.abono"> </td>
                                            <td v-text="`$${forNum(orden.saldo)}`"></td>
                                        </tr> 
                                        <tr><td colspan="5"></td><td><strong> Venta: ${{forNum(totalVenta)}}</strong></td><td><strong>Total Abonos ${{forNum(totalAbonos)}}</strong></td><td><strong>Total Cartera: ${{forNum(totalCartera)}}</strong></td></tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="5">
                                                No hay ordenes creadas
                                            </td>
                                        </tr>
                                    </tbody>    
                                </table>
                            </div>
                            <nav>
                                <ul class="pagination">
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
                    </template>
                    <!--Fin Listado-->
                    <template v-else-if="listado===2">
                        <div class="botones_vieworden">
                            <button class="btn btn-primary" @click="ocultarDetalle">Volver al listado de ordenes</button>
                            <button class="btn btn-warning" @click="imprimirOrden">Imprimir</button>
                        </div>
                        <div class="card vieworden">
                            <div class="card-header header_vieworden">
                                <div class="logo_vieworden">

                                    <img src="img/logo.png" alt="">
                                </div>
                               <div class="fecha_norden">
                                    <h5 v-text="`ORDEN DE TRABAJO NO. ${idorden}`"></h5>
                                    <div class="card fecha_orden">
                                        <div class="card-body ">
                                            <strong> <div>Fecha:</div>   <div v-text="fecha"></div></strong>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                            <div class="datos_cliente">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Cliente</h4>
                                    </div>
                                    <div class="card-body">
                                       
                                        <ul>
                                            <li>
                                                <label>Contacto:</label> <div v-text="contacto"></div> 
                                            </li>
                                            <li>
                                                <label>Correo:</label> <div v-text="email_contacto"></div>
                                            </li>
                                            <li>
                                                <label>Razón Social:</label> <div v-text="nombre"></div>
                                            </li>
                                            <li>
                                                <label v-text="tipo_documento"></label> <div v-text="num_documento"></div>
                                            </li>
                                            <li>
                                                <label>Dirección: </label> <div v-text="direccion"></div>
                                            </li>
                                            <li>
                                                <label>Teléfono empresa: </label> <div v-text="telefono_contacto"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="datosorden">
                                <div class="card">
                                    <div class="card-body">
                                        <div><label for="">Producto:</label> <div v-text="articulo"></div></div>
                                        <div><label for="">Cantidad trabajo:</label> <div v-text="cantidad"></div></div>
                                        <div><label for="">Dimenciones trabajo:</label> <div v-text="ancho_material"></div><div v-text="largo_material"></div></div>
                                        <div><label for="">Carpeta cliente:</label> <div v-text="carpeta_cliente"></div></div>
                                        <div><label for="">Fecha de entrega:</label> <div v-text="fecha_entrega"></div></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="card">
                                    <div class="card-body">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                <td v-text="`VALOR ORDEN: $${valor}`"></td>
                                                <td v-text="`IVA: $${totalImpuesto}`"></td>
                                                <td v-text="`TOTAL: $${total}`"></td>
                                                <td v-text="`DESCUENTO: $${descuento}`"></td>
                                                <td v-text="`ABONO: $${abono}`"></td>
                                                <th scope="row" v-text="`SALDO: $${saldo}`"></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </template>
                    <!-- Detalle-->
                  
                    <!-- Fin Detalle-->
                    

                </div>
                <!-- Fin ejemplo de tabla Listado -->
                 
            </div>
            
             
           
        </main>
</template>

<script>
   
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    export default {
        data (){
            return {
                
                buscare:'',
                buscarc:'',
                buscarv:'',
                buscar:'todos',
                fechaI:'',
                fechaF:'',
                filtroFecha:'1',
                buscarFechai:'',
                buscarFechaf:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                action:'',
                idorden:0,
                estadocomercial:{C:'Por Cotizar', PC:'Por Concretar', PA:'Pendiente Abono', VC:'Venta Cerrada', P:'No Comprar', A:'Aplazada'},
                estadoproduccion:{D:'Diseño', A:'Aprobación',EP:'Enviar a producción',ENP:'En Producción', E:'Para Entrega',T:'Terminada'},
                unidad:'',
                fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                fechaorden:`${new Date().getFullYear()}/${meses[new Date().getMonth()]}/${new Date().getDate()}`,
                fecha_entrega:new Date(),
                diasfaltantes:10,
                arrayOrdenes:[],
                estadoc:'C',
                estadop:'',
                carpeta_cliente:'',
                detalles_diseno:'',
                observaciones:'',
                largo_material:0,
                ancho_material:0,
                valor:0,
                idproveedor:0,
                nombre : '',
                tipo_comprobante : 'BOLETA',
                serie_comprobante : '',
                num_comprobante : '',
                impuesto: 0.19,
                descuento:0,
                total:0,
                valor_insumo:0,
                abono:0,
                saldo:0,
                totalImpuesto:0,
                totalParcial:0,
                arrayClientes: [],
                idcliente:0,
                cliente_seleccionado:null,
                cseleccionado:false,
                nombre:'',
                tipo_cliente:'Natural',
                tipo_documento:'',
                num_documento:'',
                direccion:'',
                pais:'Colombia',
                departamento:'Valle del Cauca',
                ciudad:'Cali',
                telefono:'',
                email:'',
                contacto:'',
                telefono_contacto:'',
                email_contacto:'',
                arrayDetalle : [],
                titulo_detalle:'',
                valor_detalle:'',
                descripcion_detalle:'',
                arrayArticulos:[],
                idarticulo:0,
                articulo_seleccionado:null,
                aseleccionado:false,
                nombre_articulo:'',
                precio:0,
                cantidad:1000,
                vieworden:0,
                listado:1,
                idcosto:0,
                nombre_insumo:'',
                valor_insumo:0,
                unidad_medida:'',
                insumo_seleccionado:null,
                iseleccionado:false,
                arrayInsumos:[],
                arrayCostos:[],
                descripcion_costo:'',
                valor_costo:0,
                cantidad_costo:1,
                tipo_costo:'',
                orden_costo:0,
                modala:0,
                modalc:0,
                modali:0,
                modalfecha:0,
                modalIntervalo:0,
                tituloModal : '',
                tipoAccion : 0,
                errorIngreso : 0,
                errorMostrarMsjIngreso : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : '',
                buscar_cliente: '',
                criterioA:'nombre',
                buscar_articulo:'',
                buscar_insumo:'',
                topedit:0,
                dominio:'',
                msjCliente:'',
                msjArticulo:'',
                seccion:'',
                ordenarFlecha:false,
            }
        },
        components: {
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
           
            totalCartera(){
                var resultado=0
                for(var i=0;i<this.arrayOrdenes.length;i++){
                    console.log(this.arrayOrdenes[i].saldo)
                    resultado=resultado+parseFloat(this.arrayOrdenes[i].saldo)
                }
                return resultado
            },
            totalAbonos(){
                var resultado=0
                this.arrayOrdenes.forEach((e)=>{
                    resultado=resultado+parseFloat(e.abono)
                })
                return resultado
            },
            totalVenta(){
                var resultado=0
                this.arrayOrdenes.forEach((e)=>{
                    resultado=resultado+parseFloat(e.total)
                })
                return resultado
            },

        },
        methods : {
            forNum(num){
                var numero=new Intl.NumberFormat("es-CO").format(num)
                return numero
            },
            asignarFecha(){
                this.modalfecha=1
            },
            cerrarModalFecha(){
                this.modalfecha=0
            },
           
            asiganarIntervalo(){
                this.modalIntervalo=1
            },
            
            cerrarModalIntervalo(){
                this.modalIntervalo=0
            },
            calcularCosto(){
                let me=this
                if(me.valor_insumo!=0){
                    me.valor_costo=me.cantidad_costo*me.valor_insumo;
                }else{
                    alert('seleccione primero un Insumo');
                }
            },
             
            listarOrdenesCartera (page,buscar,operador,criterio){
                let me=this;
                var url= me.dominio+'/orden/cartera?page='+page+'&criterio='+ criterio+'&operador='+operador+'&buscar='+buscar;
                axios.get(url,{
                    
                }).then(function (response) {
                    var respuesta= response.data;
                    console.log(respuesta)
                    me.arrayOrdenes = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination;
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
                me.listarOrdenesCartera(page,buscar,'like',criterio);
            },
           
          
         
            
            cambiarAbono(orden){
                var me=this
                var saldo=parseFloat(orden.total)-parseFloat(orden.abono)
                 axios.put(me.dominio+'/orden/cambiarAbono',{
                     'id':orden.idorden,
                     'abono':orden.abono,
                     'saldo':saldo
                 })
                .then(function (response) {
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenesCartera(1,this.buscar,'like',this.criterio);
            },
           
           
           
            imprimirOrden(){
                window.print()
            },
         
            cerrarModal(){
                this.modal=0;
            },
            abrirModal(orden, accion, data = []){
                this.arrayArticulos=[]
                this.modal = 1;
                this.tituloModal = 'Seleccione 1 o varios artículos';
            },
             ordenar(opcion){
                this.ordenarFlecha=!this.ordenarFlecha
                this.arrayOrdenes.sort(function(a, b) {
                    switch(opcion){
                        case 'idorden':
                            var textA = a.idorden;
                            var textB = b.idorden;
                            break;
                        case 'fechaorden':
                            var textA = a.fechaorden;
                            var textB = b.fechaorden;
                            break;
                        case 'fecha':
                            var textA = a.fecha;
                            var textB = b.fecha;
                            break;
                        case 'articulo':
                            var textA = a.articulo;
                            var textB = b.articulo;
                            break;
                        case 'rasonsocial':
                            var textA = a.rasonsocial;
                            var textB = b.rasonsocial;
                            break;
                        case 'contacto':
                            var textA = a.contacto;
                            var textB = b.contacto;
                            break;
                        case 'telefono_contacto':
                            var textA = a.telefono_contacto;
                            var textB = b.telefono_contacto;
                            break;
                        case 'carpeta_cliente':
                            var textA = a.carpeta_cliente;
                            var textB = b.carpeta_cliente;
                            break;
                        case 'estadoc':
                            var textA = a.estadoc;
                            var textB = b.estadoc;
                            break;
                        case 'estadop':
                            var textA = a.estadop;
                            var textB = b.estadop;
                            break;
                        case 'total':
                            var textA = a.total;
                            var textB = b.total;
                            break;
                        case 'fecha_entrega':
                            var textA = a.fecha_entrega;
                            var textB = b.fecha_entrega;
                            break;
                    }
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                    
                });
                if(!this.ordenarFlecha){
                    this.arrayOrdenes.reverse(); 
                }
            },
            filtrarFecha(){
                var me=this;
                if(this.modalIntervalo){
                    me.filtroFecha=`${this.fechaI},${this.fechaF}`
                }
                var url= me.dominio+'/orden/filtrarFecha?filtroFecha='+ me.filtroFecha;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayOrdenes = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination
                    me.modalIntervalo=0

                }).catch(function (error) {
                    console.log(error);
                });
            },
            
            filtrarOrdenes(buscar){
                var me=this
                me.buscar=buscar
                var url= me.dominio+'/orden/filtrarOrdenes?buscar='+buscar;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayOrdenes = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination
                }).catch(function (error) {
                    console.log(error);
                });
            },
           
            ocultarDetalle(){
                this.listado=1;
                this.articulo_seleccionado=''
                this.insumo_seleccionado=''
                this.cliente_seleccionado=''
                this.abono=0
                this.ancho_material=''
                this.largo_material=''
                this.detalles_diseno=''
                this.observaciones=''
                this.cantidad=1000
                this.unidad=''
                this.carpeta_cliente=''
                this.descuento=0
                this.saldo=0
                this.precio=0
                this.idarticulo=0
                this.fecha_entrega=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.fechaorden=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.estadoc='C'
                this.estadop=''
            },
         
         
            verOrden(orden){
                this.listado = 2
                this.idorden=orden.idorden
                this.estadoc=orden.estadoc
                this.estadop=orden.estadop
                this.id_cliente=orden.idcliente
                this.articulo=orden.articulo
                this.fecha_entrega=orden.fecha_entrega
                this.fecha=orden.fecha
                this.carpeta_cliente=orden.carpeta_cliente
                this.detalles_diseno=orden.detalles_diseno
                this.observaciones=orden.observaciones
                this.ancho_material= orden.ancho_material
                this.largo_material= orden.largo_material
                this.cantidad= orden.cantidad
                this.subtotal_orden=orden.totalParcial
                this.descuento=orden.descuento
                this.impuesto=orden.impuesto
                this.total=orden.total
                this.abono=orden.abono
                this.saldo=orden.saldo
                this.arrayDetalle=orden.detalles
                this.arrayCostos=orden.costos
            },
            calcularDias(index,orden){
                var fecha=new Date()
                var fechaini = new Date(`${fecha.getFullYear()}-${(fecha.getMonth()+1)}-${fecha.getDate()}`)
                var fechafin = new Date(orden.fecha_entrega)
                var diasdif= fechafin.getTime()-fechaini.getTime()
                var contdias = Math.round(diasdif/(1000*60*60*24))
               return contdias
            },
            cerrarModalo(){
                this.modalo=0
                this.ordenv=[]
            },
           
          
        },
        created() {
             this.listarOrdenesCartera(1,this.buscar,'like',this.criterio);
        },
        mounted() {
            
        }
    }
</script>
<style>  
    .insumos, .producto, .cliente{
        position: relative;
    } 
    .modal{
        height: 2015px !important
    }
   
    .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
        position: absolute !important;
    }
    .modal-bajo{
        top:30%;
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

</style>
