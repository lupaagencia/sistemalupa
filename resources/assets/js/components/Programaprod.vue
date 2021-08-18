<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <template v-if="listado===1">
                        <div class="card-body">
                            <h2>Procesos por realizar</h2>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="col-xl-12">
                                            <button class="btn btn-dark"  @click="listarProcesos(['Papel'],'=')">Papeles</button>
                                            <button class="btn btn-dark"  @click="listarProcesos(['Corte material'],'=')">Cortes material</button>
                                            <button class="btn btn-dark"  @click="listarProcesos(['Plancha','Clise'],'=')">Planchas, cireles, marcos, clises</button>
                                            <button class="btn btn-dark"  @click="listarProcesos(['Montaje'],'=')">Montajes</button>
                                            <button class="btn btn-dark"  @click="listarProcesos(['Impresionf'],'=')">Impresión gran formato</button>
                                            <button class="btn btn-warning"  @click="listarProcesos(['Tiraje'],'=')">Tirajes</button>
                                            <button class="btn btn-warning"  @click="listarProcesos(['Troquelado'],'=')">Troquelados</button>
                                            <button class="btn btn-warning"  @click="listarProcesos(['Refile'],'=')">Refiles y cortes</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Medio corte'],'=')">Medio cortes</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Numeración'],'=')">Numeraciones</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Terminado'],'=')">Terminados</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Acabado'],'=')">Acabados</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Laminado'],'=')">Laminados</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Estampado'],'=')">Estampados</button>
                                            <button class="btn btn-success"  @click="listarProcesos(['Otros','Corte laser','Transporte','Retablos','Camisetas','Telas'],'=')">Otros</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div> 
                                <h2 style="display:flex; flex-direction:row; justify-content:center" class="bg-primary pt-1 pb-1" ><span v-for="(busqueda, index) in busquedaActual" :key="index"  style="margin-right:10px">- {{busqueda}} </span> </h2>
                                <div class="table-responsive">
                                 <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                       
                                            <th >Opciones</th>
                                            <th @click="ordenar('fecha')">Fecha <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('cliente')">Cliente  <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('articulo')">Producto <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('nombre_insumo')">Proceso <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('descripcion_costo')">Descripción <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('cantidad')">Cantidad <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('completado')">Proceso iniciado <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('fecha')">Fecha que termina <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('terminado')">Proceso terminado <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayProcesos.length">
                                        <tr v-for="(proceso, index) in arrayProcesos" :key="index">
                                            <td>
                                                <button type="button" @click="listarOrdenes(1,proceso.idorden,'=','ordentrabajos.id')" class="btn btn-primary btn-sm">
                                                <i class="icon-eye"></i>
                                                </button> &nbsp;  
                                            </td>
                                            <td v-text="proceso.fecha"></td>
                                            <td v-text="proceso.cliente"></td>
                                            <td v-text="proceso.articulo"></td>
                                            <td >{{proceso.titulo}} {{proceso.nombre_insumo}}</td>
                                            <td v-text="proceso.descripcion_costo" ></td>
                                            <td>{{proceso.cantidad}} {{proceso.unidad_medida}} </td>
                                            <td>
                                               <select :class="proceso.completado==1 ? 'bg-success':'bg-warning'" v-model="proceso.completado" @change="cambiarProceso(proceso,'completado', busquedaActual)">
                                                   <option value="0" >No Iniciado</option>
                                                   <option value="1">Iniciado</option>
                                               </select>
                                              
                                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalft}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Fecha que termina el proceso</h5>
                                                            <button @click="cerrarModalft" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i></button>
                                                        </div>
                                                        
                                                        <div class="modal-body">
                                                            <div class="inputGruop">
                                                                {{calcularDias(index,proceso)}}
                                                                <input type="date" placeholder="Fecha que termian el proceso" v-model="procesoSeleccionado.fecha_termina" @change="cambiarProceso(procesoSeleccionado,'fechaterminado', busquedaActual)" >
                                                            </div>
                                                            <div class="alert alert-warning" v-if="mensajeft!=''" v-text="mensajeft"></div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button @click="cerrarModalft" type="button" class="btn btn-secondary">Cerrar</button>
                                                            <button @click="cambiarProceso(procesoSeleccionado,'completado', busquedaActual)" type="button" class="btn btn-primary">Cambiar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            </td>
                                            <td class="fechatermina-procesos" :style="`background:hsl(${proceso.diasfaltantes*30}deg 100% 44%)`">
                                                <input type="date" v-model="proceso.fecha_termina"  @change="cambiarProceso(proceso,'fechaterminado' , busquedaActual)" >
                                            </td>
                                            <td><select :class="proceso.terminado==1 ? 'bg-success':'bg-warning'" v-model="proceso.terminado" @change="cambiarProceso(proceso,'terminado', busquedaActual)">
                                                   <option value="0" >No terminado</option>
                                                   <option value="1">Terminado</option>
                                               </select>
                                            </td>
                                        </tr> 
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="7">
                                                No hay procesos
                                            </td>
                                        </tr>
                                    </tbody>    
                                </table>
                                </div>
                            </div>
                    </template>
                  
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
                                    <h5 v-text="`ORDEN DE TRABAJO NO. ${arrayOrdenes.idorden}`"></h5>
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
                                                <label>Contacto:</label> <div v-text="arrayOrdenes.contacto"></div> 
                                            </li>
                                            <li>
                                                <label>Correo:</label> <div v-text="arrayOrdenes.email_contacto"></div>
                                            </li>
                                            <li>
                                                <label>Razón Social:</label> <div v-text="arrayOrdenes.nombre"></div>
                                            </li>
                                            <li>
                                                <label v-text="tipo_documento"></label> <div v-text="arrayOrdenes.documento"></div>
                                            </li>
                                            <li>
                                                <label>Dirección: </label> <div v-text="arrayOrdenes.direccion"></div>
                                            </li>
                                            <li>
                                                <label>Teléfono empresa: </label> <div v-text="arrayOrdenes.telefono_contacto"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="datosorden">
                                <div class="card">
                                    <div class="card-body">
                                        <div><label for="">Producto:</label> <div v-text="arrayOrdenes.articulo"></div></div>
                                        <div><label for="">Cantidad trabajo:</label> <div v-text="arrayOrdenes.cantidad"></div></div>
                                        <div><label for="">Dimenciones trabajo:</label> <div v-text="`Arte: ${arrayOrdenes.ancho_material}`"></div><div v-text="`Material: ${largo_material}`"></div></div>
                                        <div><label for="">Carpeta cliente:</label> <div v-text="arrayOrdenes.carpeta_cliente"></div></div>
                                        <div><label for="">Fecha de entrega:</label> <div v-text="arrayOrdenes.fecha_entrega"></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="descripcion_orden">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Descripción del trabajo</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul>
                                            <li v-for="(detalle, index) in detalles" :key="index">
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                        <th scope="row" v-text="detalle.titulo_detalle"></th>
                                                        <td v-text="detalle.valor_detalle"></td>
                                                        <td v-text="detalle.descripcion_detalle"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Ejecución trabajo</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul>
                                            <li v-for="(costo, index) in costos" :key="index">
                                                <table class="table">
                                                    
                                                    <tbody>
                                                        <tr>
                                                        <th scope="row" v-text="costo.titulo_costo"></th>
                                                        <td v-text="costo.nombre_insumo"></td>
                                                        <td v-text="costo.descripcion_costo"></td>
                                                        <td v-text="costo.cantidad_costo"></td>
                                                        <td v-text="`$ ${costo.subtotal_costo}`"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header">
                                            <h4 for="">Detalles de diseño</h4> 
                                        </div>
                                        <div v-text="arrayOrdenes.detalle_diseno" class="card"></div>
                                    </div>
                                </div>
                                 <div class="card">
                                    <div class="card-body">
                                        <div class="card-header">
                                            <h4 for="">Descripción</h4>
                                        </div>
                                        <div v-text="arrayOrdenes.observaciones" class="card"></div>
                                    </div>
                                 </div>
                            </div>
                            <div class="card-footer">
                                <div class="card">
                                    <div class="card-body">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                <td v-text="`VALOR ORDEN: $${arrayOrdenes.valor}`"></td>
                                                <td v-text="`IVA: $${arrayOrdenes.totalImpuesto}`"></td>
                                                <td v-text="`TOTAL: $${arrayOrdenes.total}`"></td>
                                                <td v-text="`DESCUENTO: $${arrayOrdenes.descuento}`"></td>
                                                <td v-text="`ABONO: $${arrayOrdenes.abono}`"></td>
                                                <th scope="row" v-text="`SALDO: $${arrayOrdenes.saldo}`"></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </template>
                    
                    

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
                fechaI:'',
                fechaF:'',
                filtroFecha:'1',
                buscarFechai:'',
                buscarFechaf:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                action:'',
                idorden:0,
                estadocomercial:{AN:'Analizar', C:'Cotizar', CE:'Cotización Enviada', NR:'Negociación/Revisión', PA:'Pendiente Abono', PC:'Por Concretar',VC:'Venta Cerrada', P:'Perdida y Cerrada'},
                estadoproduccion:{D:'Diseño', A:'Aprobación',EP:'Enviar a producción',ENP:'En Producción', E:'Para Entrega',T:'Terminada'},
                unidad:'',
                fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                fechaorden:'',
                fecha_entrega:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                arrayOrdenes:{},
                arrayProcesos:[],
                estadoc:'C',
                estadop:'D',
                carpeta_cliente:'',
                detalle_diseno:'',
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
                documento:'',
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
                titulo_costo:'',
                procesoSeleccionado:'',
                modalft:0,
                modalc:0,
                modali:0,
                modalfecha:0,
                modalIntervalo:0,
                tituloModal : '',
                tipoAccion : 0,
                errorIngreso : 0,
                errorMostrarMsjIngreso : [],
                mensajeft:'',
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
                diasfaltantes:0,
                busquedaActual:'',
                ordenarFlecha:false
               
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
            calcularCosto2(){
                var resultado=0
                resultado=this.cantidad_costo*this.valor_insumo
                this.valor_costo=resultado
                return resultado
            },
            subtotalCosto(){
                var resultado=0
                for(var i=0;i<this.arrayCostos.length;i++){
                    this.arrayCostos[i].subtotal_costo=parseFloat(this.arrayCostos[i].valor_costo)*parseInt(this.arrayCostos[i].cantidad_costo)
                resultado=this.arrayCostos[i].subtotal_costo
                }
                return resultado
            },
            
            totalCostos(){
                var resultado=0
                for(var i=0;i<this.arrayCostos.length;i++){
                    resultado=resultado+parseFloat(this.arrayCostos[i].subtotal_costo)
                }
                return resultado
            },
            calcularValor(){
                var resultado=0
                resultado=this.precio*this.cantidad
                this.valor=resultado
                return resultado
                
            },
            calcularTotalParcial(){
                var resultado=0;
                resultado=this.valor-this.descuento
                this.totalParcial=resultado
                return resultado
            },
            calcularTotalImpuesto(){
                var resultado=0;
                resultado=this.totalParcial*this.impuesto
                this.totalImpuesto=resultado
                return resultado
            },
            calcularTotal(){
                var resultado=0
                resultado=this.totalParcial+this.totalImpuesto
                this.total=resultado
                return resultado
            },
            calcularSaldo(){
                var resultado=0
                resultado =this.total-this.abono
                this.saldo=resultado
                return resultado
            },
            detalles(){
                this.arrayDetalle=this.arrayOrdenes.detalles
                var respuesta=this.arrayDetalle
                return respuesta
            },
            costos(){
                this.arrayCostos=this.arrayOrdenes.costos
                var respuesta=this.arrayCostos
                return respuesta
            }
           

        },
        methods : {
            ordenar(opcion){
                this.ordenarFlecha=!this.ordenarFlecha
                this.arrayProcesos.sort(function(a, b) {
                    switch(opcion){
                        case 'fecha':
                            var textA = a.fecha;
                            var textB = b.fecha;
                            break;
                        case 'cliente':
                            var textA = a.cliente;
                            var textB = b.cliente;
                            break;
                        case 'articulo':
                            var textA = a.articulo;
                            var textB = b.articulo;
                            break;
                        case 'nombre_insumo':
                            var textA = a.nombre_insumo;
                            var textB = b.nombre_insumo;
                            break;
                        case 'descripcion_costo':
                            var textA = a.descripcion_costo;
                            var textB = b.descripcion_costo;
                            break;
                        case 'cantidad':
                            var textA = a.cantidad;
                            var textB = b.cantidad;
                            break;
                        case 'completado':
                            var textA = a.completado;
                            var textB = b.completado;
                            break;
                        case 'fecha':
                            var textA = a.fecha;
                            var textB = b.fecha;
                            break;
                        case 'terminado':
                            var textA = a.terminado;
                            var textB = b.terminado;
                            break;
                    }
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                    
                });
                if(!this.ordenarFlecha){
                    this.arrayProcesos.reverse(); 
                }
            },
             calcularDias(index,proceso){
                var fecha=new Date()
                var fechaini = new Date(`${fecha.getFullYear()}-${(fecha.getMonth()+1)}-${fecha.getDate()}`)
                var fechafin = new Date(proceso.fecha_termina)
                var diasdif= fechafin.getTime()-fechaini.getTime()
                var contdias = Math.round(diasdif/(1000*60*60*24))
                this.arrayProcesos[index].diasfaltantes=contdias
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
            
            listarProcesos(buscar,operador){
                let me=this;
                var url= me.dominio+'/orden/procesos?operador='+operador
                axios.post(url,{
                    'buscar':JSON.stringify(buscar),
                }).then(function (response) {
                    var respuesta= response.data;
                    me.arrayProcesos = respuesta.procesos;
                    me.busquedaActual=respuesta.buscar;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            listarOrdenes (page,buscar,operador,criterio){
                let me=this;
                var url= me.dominio+'/orden?page='+page+'&criterio='+ criterio+'&operador='+operador+'&buscar='+buscar;
                axios.get(url,{
                    
                }).then(function (response) {
                    var respuesta= response.data;
                   
                    me.arrayOrdenes = respuesta.ordenes.data[0];
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
                me.verOrden();
            },
            selectArticulobyid(idarticulo){
                let me=this;
               
                var url= me.dominio+'/articulo/selectArticulobyid?id='+idarticulo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data[0];
                    me.getDatosArticulo(respuesta,0)
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosArticulo(val1, index){
                let me = this;
                me.loading = true;
                me.idarticulo = val1.id;
                me.nombre_articulo=val1.nombre;
                me.precio=val1.precio_venta
                me.articulo_seleccionado=val1;
                me.buscar_articulo=val1.nombre
                me.aseleccionado=true
                me.arrayArticulo=[];
                me.modala=0
            },
            getClientebyid(idcliente){
                console.log(idcliente)
                let me=this;
                var url= me.dominio+'/cliente/selectCliente?id='+idcliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data[0];
                    
                    me.getDatosCliente(respuesta)
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosCliente(val1, index){
                let me = this;
                me.loading = true;
                me.idcliente = val1.id;
                me.nombre=val1.nombre;
                me.tipo_documento=val1.tipo_documento
                me.tipo_cliente=val1.tipo_cliente
                me.num_documento=val1.num_documento
                me.direccion=val1.direccion
                me.pais=val1.pais
                me.departamento=val1.departamento
                me.ciudad=val1.ciudad
                me.telefono=val1.telefono
                me.email=val1.email
                me.contacto=val1.contacto
                me.telefono_contacto=val1.telefono_contacto
                me.email_contacto=val1.email_contacto
                me.cliente_seleccionado=val1;
                me.buscar_cliente=val1.nombre
                me.cseleccionado=true
                me.arrayClientes=[];
                me.modalc=0
            },
            cambiarProceso(proceso,columna,buscar){
                let me=this;
                var dato
                switch(columna){
                     case 'completado':
                         if(proceso.completado){
                             me.abrirModalft(proceso)
                             if(this.procesoSeleccionado.fecha_termina=='0000-00-00' || this.procesoSeleccionado.fecha_termina==null){
                                 dato=false
                                 me.mensajeft='Debe asignar una fecha de terminacion del proceso'
                                 return
                             }else{
                                 dato=this.procesoSeleccionado.completado
                                 me.mensajeft=''
                                 me.modalft=0
                             }  
                         }else{
                             dato=proceso.completado
                         }
                        
                    break;
                    case 'fechaterminado':
                        dato=proceso.fecha_termina
                    break;
                    case 'terminado':
                        dato=proceso.terminado
                    break
                }
               
                var url= me.dominio+'/orden/cambiarProceso'
                axios.post(url,{
                    'id': proceso.idcosto,
                    'dato':dato,
                    'columna':columna
                    }).then(function (response) {
                        let respuesta = response.data;
                        me.listarProcesos(buscar,'=')
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            abrirModalft(proceso){
                this.procesoSeleccionado=proceso
                if(proceso.completado){
                    this.modalft = 1
                }
            },
          
            cerrarModalft(){
                this.procesoSeleccionado=''
                this.modalft=0;
                
            },
          
           
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarOrdenes(page,buscar,'like',criterio);
            },
         
            mostrarDetalle(action){
                this.action=action
                this.listado=0
            },
            ocultarDetalle(){
                this.listado=1;
            },
            filtrarFecha(){
                var me=this;
                if(this.modalIntervalo){
                    me.filtroFecha=`${this.fechaI},${this.fechaF}`
                }
                var url= me.dominio+'/orden/filtrarFecha?filtroFecha='+ me.filtroFecha;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    console.log(respuesta.ordenes.data)
                    me.arrayOrdenes = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination
                    me.modalIntervalo=0

                }).catch(function (error) {
                    console.log(error);
                });
            },
            
            filtrarOrdenes(buscar){
                let criterio='ordentrabajos.produccion'
                let operador='='
                this.listarOrdenes(1,buscar,operador,criterio)
            },
            imprimirOrden(){
                window.print()
            },
            verOrden(){
                this.idorden=this.arrayOrdenes.idorden
                this.estadoc=this.arrayOrdenes.estadoc
                this.estadop=this.arrayOrdenes.estadop
                this.id_cliente=this.arrayOrdenes.idcliente
                this.id_articulo=this.arrayOrdenes.idarticulo
                this.fecha_entrega=this.arrayOrdenes.fecha_entrega
                this.fecha=this.arrayOrdenes.fecha
                this.carpeta_cliente=this.arrayOrdenes.carpeta_cliente
                this.detalles_diseno=this.arrayOrdenes.detalle_diseno
                this.observaciones=this.arrayOrdenes.observaciones
                this.ancho_material= this.arrayOrdenes.ancho_material
                this.largo_material= this.arrayOrdenes.largo_material
                this.cantidad= this.arrayOrdenes.cantidad
                this.subtotal_orden=this.arrayOrdenes.totalParcial
                this.descuento=this.arrayOrdenes.descuento
                this.impuesto=this.arrayOrdenes.impuesto
                this.total=this.arrayOrdenes.total
                this.abono=this.arrayOrdenes.abono
                this.saldo=this.arrayOrdenes.saldo
                this.arrayDetalle=this.arrayOrdenes.detalles
                this.arrayCostos=this.arrayOrdenes.costos
                this.listado = 2
            },
          
        },
        mounted() {
            this.listarProcesos(['Papel'],'=');
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
