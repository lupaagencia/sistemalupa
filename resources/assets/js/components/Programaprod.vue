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
                                 <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Opciones</th>
                                            <th>Cliente</th>
                                            <th>Producto</th>
                                            <th>Proceso</th>
                                            <th>Cantidad</th>
                                            <th>Proceso iniciado</th>
                                            <th>Fecha que termina</th>
                                            <th>Proceso terminado</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayProcesos.length">
                                        <tr v-for="(proceso, index) in arrayProcesos" :key="index">
                                            <td>
                                                <button type="button"  class="btn btn-primary btn-sm">
                                                <i class="icon-eye"></i>
                                                </button> &nbsp;  
                                            </td>
                                            <td v-text="proceso.cliente"></td>
                                            <td v-text="proceso.articulo"></td>
                                            <td >{{proceso.titulo}} {{proceso.nombre_insumo}}</td>
                                            <td>{{proceso.cantidad}} {{proceso.unidad_medida}}</td>
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
                    </template>
                    <template v-else-if="listado==3">
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="col-xl-12">
                                        <div class="input-group">
                                            <input type="text" v-model="buscare" @keyup="listarOrdenes(1,`%${buscare}%`,'like','personas.nombre')"  placeholder="Empresa">
                                            <input type="text" v-model="buscarc" @keyup="listarOrdenes(1,`%${buscarc}%`,'like','clientes.contacto')"  placeholder="Contacto">
                                            <input type="text" v-model="buscarv" @keyup="listarOrdenes(1,`%${buscarv}%`,'like','ordentrabajos.total')"  placeholder="Valor orden">
                                            
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
                                            </div>
                                            
                                        
                                        </div>
                                    </div>
                                        <div class="col-xl-12">
                                        <div class="input-group">
                                            <div class="alert alert-primary mb-0 p-1" ><strong>Estado</strong> </div>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('')">Todos</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('D')">Diseño</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('A')">Aprobación</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('EP')">Enviar a producción</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('ENP')">En producción</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('E')">Por entregar</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('T')">Terminada</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    
                                    <thead>
                                        <tr>
                                            <th>Opciones</th>
                                            <th>Fecha</th>
                                            <th>Fecha de cierre</th>
                                            <th>Trabajo</th>
                                            <th>Cliente</th>
                                            <th>Nombre contacto</th>
                                            <th>Teléfono</th>
                                            <th>Carpeta</th>
                                            <th>Estado Comerical</th>
                                            <th>Estado Producción</th>
                                            <th>Valor orden</th>
                                            <th>Fecha de entrega</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayOrdenes.length">
                                        <tr v-for="orden in arrayOrdenes" :key="orden.idorden">
                                            <td>
                                            
                                                <button type="button" @click="verOrden(orden)" class="btn btn-primary btn-sm">
                                                <i class="icon-eye"></i>
                                                </button> &nbsp;
                                                <button type="button" @click="editarOrden(orden,'edit')" class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                                </button> &nbsp;
                                                <button type="button" @click="copiarOrden(orden)" class="btn btn-success btn-sm">
                                                <i class="fa fa-copy"></i>
                                                </button> &nbsp;
                                                <button type="button" class="btn btn-danger btn-sm" @click="eliminarOrden(orden.idorden)">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </td>
                                            <td v-text="orden.fechaorden"></td>
                                            <td v-text="orden.fecha"></td>
                                            <td v-text="orden.articulo"></td>
                                            <td v-text="orden.rasonsocial"></td>
                                            <td v-text="orden.contacto"></td>
                                            <td v-text="orden.telefono_contacto"></td>
                                            <td v-text="orden.carpeta_cliente"></td>
                                            <td v-text="estadocomercial[orden.estadoc]"></td>
                                            <td v-text="estadoproduccion[orden.estadop]"></td>
                                            <td v-text="orden.total"></td>
                                            <td v-text="orden.fecha_entrega"></td>
                                            <table>

                                            <tr  v-if="vieworden==orden.idorden">
                                                <td >
                                                    Orden de trabajo
                                                </td>
                                            </tr>
                                            </table>
                                                                        
                                        </tr> 
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
                                                <label v-text="tipo_documento"></label> <div v-text="documento"></div>
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
                                        <div><label for="">Cantidad trabajo:</label> <div v-text="cantidad"></div></div>
                                        <div><label for="">Dimenciones trabajo:</label> <div v-text="ancho_material"></div><div v-text="largo_material"></div></div>
                                        <div><label for="">Carpeta cliente:</label> <div v-text="carpeta_cliente"></div></div>
                                        <div><label for="">Fecha de entrega:</label> <div v-text="fecha_entrega"></div></div>
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
                                            <li v-for="(detalle, index) in arrayDetalle" :key="index">
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
                                            <li v-for="(costo, index) in arrayCostos" :key="index">
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
                                        <div v-text="detalle_diseno" class="card"></div>
                                    </div>
                                </div>
                                 <div class="card">
                                    <div class="card-body">
                                        <div class="card-header">
                                            <h4 for="">Descripción</h4>
                                        </div>
                                        <div v-text="descripcion_orden" class="card"></div>
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
                arrayOrdenes:[],
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
                busquedaActual:''
               
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
           

        },
        methods : {
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
                    console.log(respuesta)
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
                    me.arrayOrdenes = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getClientebyid(idcliente){
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
            verOrden(orden){
                this.listado = 2
                this.idorden=orden.idorden
                this.getClientebyid(orden.idcliente)
                this.selectArticulobyid(orden.idarticulo)
                this.estadoc=orden.estadoc
                this.estadop=orden.estadop
                this.id_cliente=orden.idcliente
                this.id_articulo=orden.idarticulo
                this.fecha_entrega=orden.fecha_entrega
                this.fecha=orden.fecha
                this.carpeta_cliente=orden.carpeta_cliente
                this.detalles_diseno=orden.detalle_diseno
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
          
        },
        mounted() {
            this.listarOrdenes(1,this.buscar,'like',this.criterio);
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
