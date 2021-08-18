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
                        <i class="fa fa-align-justify"></i> Seguimiento Ventas
                        <button type="button" @click="mostrarDetalle('nuevo')" class="btn btn-success">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                    </div>
                    <!-- Listado-->

                    <template v-if="listado===1">
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
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('todos')">Todos</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('C')">Por Cotizar</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('PC')">Por Concretar</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('PA')">Pendiente de Abono</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('VC')">Venta Cerrada</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('P')">No Compra</button>
                                            <button class="btn btn-secondary" @click="filtrarOrdenes('A')">Aplazada</button>
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
                                            <th @click="ordenar('fechaorden')">Fecha <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('fecha')">Fecha de cierre <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('articulo')">Trabajo <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('cliente')">Cliente <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('contacto')">Nombre contacto <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('telefono_contacto')">Teléfono <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('carpeta_cliente')">Carpeta <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('estadoc')">Estado Comerical <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('total')">Valor orden <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayOrdenes.length">
                                        <tr v-for="(orden, index) in arrayOrdenes" :key="index">
                                            
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
                                            <td v-text="orden.idorden"></td>
                                            <td v-text="orden.fechaorden"></td>
                                            <td v-text="orden.fecha"></td>
                                            <td v-text="orden.articulo"></td>
                                            <td v-text="orden.rasonsocial"></td>
                                            <td v-text="orden.contacto"></td>
                                            <td v-text="orden.telefono_contacto"></td>
                                            <td v-text="orden.carpeta_cliente"></td>
                                            <td>
                                                <select v-model="orden.estadoc" id="" class="bg-primary" @change="cambiarEstado(orden)" >
                                                    <option v-for="(estadoc, key) in estadocomercial" :key="key" :value="key" v-text="estadocomercial[key]"></option>
                                                </select>
                                            </td>
                                            
                                            <td v-text="orden.total"></td>
                                            
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
                                                        <th scope="row" v-text="costo.tipo_costo"></th>
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
                                        <div v-text="detalles_diseno" class="card"></div>
                                    </div>
                                </div>
                                 <div class="card">
                                    <div class="card-body">
                                        <div class="card-header">
                                            <h4 for="">Descripción</h4>
                                        </div>
                                        <div v-text="observaciones" class="card"></div>
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
            }
           

        },
        methods : {
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
            selectCliente(){
                let me=this;
                
                var url= me.dominio+'/cliente/selectClientes?filtro='+this.buscar_cliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    
                    me.arrayClientes=respuesta.clientes;
                    me.modalc=1
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
            nuevoCliente(){
                let me=this;
                me.cliente_seleccionado=0
                me.buscar_cliente=''
                me.nombre=''
                me.tipo_cliente=''
                me.tipo_documento=''
                me.direccion=''
                me.telefono=''
                me.telefono_contacto=''
                me.contacto=''
                me.pais=''
                me.departamento=''
                me.ciudad=''
                me.num_documento=''
                me.email=''
                me.email_contacto=''
                
            },
            nuevoArticulo(){
                 let me=this;
                me.articulo_seleccionado=0
                me.nombre_articulo=''
                me.precio=0
            },
            guardarCliente(){
                let me=this;
                var id=0
                if(me.cliente_seleccionado==0){
                   id=0
                }else{
                   id=me.cliente_seleccionado.id
                }
                var url= me.dominio+'/cliente/actualizar';
                axios.put(url,{
                    'tipo_cliente':me.tipo_cliente,
                    'nombre': me.nombre,
                    'tipo_documento': me.tipo_documento,
                    'num_documento' : me.num_documento,
                    'direccion' : me.direccion,
                    'ciudad' : me.ciudad,
                    'departamento' : me.departamento,
                    'pais' : me.pais,
                    'contacto' : me.contacto,
                    'telefono_contacto' : me.telefono_contacto,
                    'email_contacto' : me.email_contacto,
                    'telefono' : me.telefono,
                    'email' : me.email,
                    'id':id
                }).then(function (response) {
                    let respuesta = response.data;
                    if(id==0){
                        me.getDatosCliente(respuesta)
                    }
                    console.log(respuesta)
                    if(typeof respuesta.id !== 'undefined'){
                        me.msjCliente='Cliente guardado';
                    }

                })
                .catch(function (error) {
                    console.log(error);
                }); 
                   
                
            },
            guardarArticulo(){
                let me=this;
                var id=0
                if(me.articulo_seleccionado==0){
                   id=0
                }else{
                   id =me.articulo_seleccionado.id
                }
                var url= me.dominio+'/articulo/actualizar';
                axios.put(url,{
                    'idcategoria': 1,
                    'codigo': 0,
                    'nombre': this.nombre_articulo,
                    'stock': 1000,
                    'precio_venta': this.precio,
                    'descripcion': '',
                    'id':id
                }).then(function (response) {
                    let respuesta = response.data;
                    if(id==0){
                        me.getDatosArticulo(respuesta,0)
                    }
                    if(typeof respuesta.id !== 'undefined'){
                        me.msjArticulo='Producto guardado';
                    }

                })
                .catch(function (error) {
                    console.log(error);
                }); 
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
            selectArticulo(){
                let me=this;
               
                var url= me.dominio+'/articulo/selectArticulo?filtro='+this.buscar_articulo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayArticulos=respuesta.articulos;
                    me.modala=1
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
            selectInsumos(seccion){
                let me=this;
              
                var url= me.dominio+'/costop/selectInsumos?filtro='+this.buscar_insumo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayInsumos=respuesta.insumos;
                    me.modali=1
                    me.seccion=seccion
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            agregarCosto(){
                let me=this
                if(me.tipo_costo=='' || me.valor_costo==''){

                }else{
                    me.arrayCostos.push({
                        idcosto:0,
                        tipo_costo:me.tipo_costo,
                        nombre_insumo:me.nombre_insumo,
                        id_insumo:me.insumo_seleccionado.id,
                        valor_costo:me.valor_costo,
                        cantidad_costo:me.cantidad_costo,
                        orden_costo:me.orden_costo,
                        descripcion_costo:me.descripcion_costo,
                        subtotal_costo:parseFloat(me.valor_costo)*parseInt(me.cantidad_costo)
                    }) 
                    me.tipo_costo=''
                    me.nombre_insumo=''
                    me.id_insumo=''
                    me.valor_costo=''
                    me.cantidad_costo=1
                    me.orden_costo=0
                    me.descripcion_costo=''
                    me.subtotal_costo=''
                    me.insumo_seleccionado=null
                    me.buscar_insumo=''
                }
               
                
            },
            getDatosInsumos(val1, index){
                let me = this;
                me.loading = true;
                me.idcosto = val1.id;
                me.tipo_costo=val1.tipo_costo;
                me.nombre_insumo=val1.nombre;
                me.valor_insumo=val1.valor
                me.unidad_medida=val1.unidad_medida
                me.insumo_seleccionado=val1;
                me.buscar_insumo=val1.nombre
                me.iseleccionado=true
                me.arrayInsumos=[];
                me.modali=0
                me.calcularCosto()
            },
            getDatosDetalles(val1, index){
                let me = this;
                me.loading = true;
                me.idcosto = val1.id;
                me.titulo_costo=val1.tipo_costo;
                me.valor_detalle=val1.nombre;
                me.buscar_insumo=val1.nombre
                me.iseleccionado=true
                me.arrayInsumos=[];
                me.modali=0
            },
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarOrdenes(page,buscar,'like',criterio);
            },
           
            agregarDetalle(){
                 let me=this
                if(me.titulo_detalle=='' || me.valor_detalle==''){

                }else{
                    me.arrayDetalle.push({
                        id:0,
                        titulo_detalle:me.titulo_detalle,
                        valor_detalle:me.valor_detalle,
                        descripcion_detalle:me.descripcion_detalle
                    }) 
                    me.titulo_detalle=''
                    me.valor_detalle=''
                    me.descripcion_detalle=''
                }
               
                
            },
            
            listarArticulo (buscar,criterio){
                let me=this;
                var url= me.dominio+'/articulo/listarArticulo?buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayArticulos = respuesta.articulos.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            eliminarDetalle(index){
                 let me=this
                 if(me.arrayDetalle[index].id>0){
                    var url= '/detalle/borrar?id='+ me.arrayDetalle[index].id + '&_method=DELETE';
                    axios.delete(url).then(function (response) {
                        var respuesta= response.data;
                        me.arrayDetalle.splice(index,1)
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                   
                }else{
                    me.arrayDetalle.splice(index,1)
                }
               
                
            },
            eliminarCosto(index){
                let me=this
                if(me.arrayCostos[index].idcosto>0){
                    var url= me.dominio+'/costo/borrar?id='+ me.arrayCostos[index].idcosto + '&_method=DELETE';
                    axios.delete(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayCostos.splice(index,1)
                })
                .catch(function (error) {
                    console.log(error);
                });
                   
                }else{
                    me.arrayCostos.splice(index,1)
                }
            },
            registrarOrden(){
                let me = this;
                const orden1 = new FormData()
                orden1.set('estadoc',this.estadoc)
                orden1.set('estadop',this.estadop)
                orden1.set('id_cliente', this.cliente_seleccionado.id)
                orden1.set('id_articulo' , this.articulo_seleccionado.id)
                orden1.set('fecha_entrega' , this.fecha_entrega)
                orden1.set('fecha' , this.fecha)
                orden1.set('fechaorden' , this.fechaorden)
                orden1.set('carpeta_cliente' , this.carpeta_cliente)
                orden1.set('detalles_diseno' , this.detalles_diseno)
                orden1.set('observaciones' , this.observaciones)
                orden1.set('unidad' , this.unidad)
                orden1.set('ancho_material', this.ancho_material)
                orden1.set('largo_material', this.largo_material)
                orden1.set('cantidad', this.cantidad)
                orden1.set('totalParcial',this.totalParcial)
                orden1.set('descuento',this.descuento)
                orden1.set('impuesto',this.impuesto)
                orden1.set('total',this.total)
                orden1.set('abono',this.abono)
                orden1.set('saldo',this.saldo)
                orden1.set('detalles',JSON.stringify(this.arrayDetalle))
                orden1.set('costos',JSON.stringify(this.arrayCostos))
                axios.post(me.dominio+'/orden/registrar',orden1)
                .then(function (response) {
                    me.listado=1
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
            },
           
            actualizarOrden(){
                let me = this;
                const orden1 = new FormData()
                orden1.set('_method', 'PUT')
                orden1.set('id', parseInt(this.idorden))
                orden1.set('estadoc',this.estadoc)
                orden1.set('estadop',this.estadop)
                orden1.set('id_cliente', this.cliente_seleccionado.id)
                orden1.set('id_articulo' , this.articulo_seleccionado.id)
                orden1.set('fecha_entrega' , this.fecha_entrega)
                orden1.set('fecha' , this.fecha)
                orden1.set('fechaorden' , this.fechaorden)
                orden1.set('carpeta_cliente' , this.carpeta_cliente)
                orden1.set('detalles_diseno' , this.detalles_diseno)
                orden1.set('observaciones' , this.observaciones)
                orden1.set('unidad' , this.unidad)
                orden1.set('ancho_material', this.ancho_material)
                orden1.set('largo_material', this.largo_material)
                orden1.set('cantidad', this.cantidad)
                orden1.set('subtotal_orden',this.totalParcial)
                orden1.set('descuento',this.descuento)
                orden1.set('impuesto',this.impuesto)
                orden1.set('total',this.total)
                orden1.set('abono',this.abono)
                orden1.set('saldo',this.saldo)
                orden1.set('detalles',JSON.stringify(this.arrayDetalle))
                orden1.set('costos',JSON.stringify(this.arrayCostos))
                axios.post(me.dominio+'/orden/actualizar',orden1)
                .then(function (response) {
                    me.listado=1;
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
                
            },
            cambiarEstado(orden){
                var me=this
                axios.put(me.dominio+'/orden/cambiarEstado',{
                    'id':orden.idorden,
                    'estadoc':orden.estadoc,
                    'estadop':orden.estadop
                })
                .then(function (response) {
                   
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.filtrarOrdenes(this.buscar);
            },
            cambiarFecha(orden){
                var me=this
                 axios.put(me.dominio+'/orden/cambiarFecha',{
                     'id':orden.idorden,
                     'fecha_entrega':orden.fecha_entrega
                 })
                .then(function (response) {
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.filtrarOrdenes(this.buscar);
            },
           
            copiarOrden(orden){
                let me=this
                var url= me.dominio+'/orden/duplicar?id='+ orden.idorden
                axios.post(url).then(function (response) {
                    var respuesta= response.data;
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
               // this.editarOrden(this.arrayOrdenes[0],'edit')
            },
            eliminarOrden(id){
                let me=this
                var url= me.dominio+'/orden/borrar?id='+ id;
                axios.delete(url,{'_method': 'DELETE'}).then(function (response) {

                    var respuesta= response.data;
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
            },
            imprimirOrden(){
                window.print()
            },
            mostrarDetalle(action){
                this.action=action
                this.listado=0
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
                var url= me.dominio+'/orden/filtrarEstadoc?buscar='+buscar;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayOrdenes = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination
                }).catch(function (error) {
                    console.log(error);
                });
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
            editarOrden(orden,action){
                this.listado = 0
                this.action=action
                this.idorden=orden.idorden
                this.getClientebyid(orden.idcliente)
                this.selectArticulobyid(orden.idarticulo)
                this.estadoc=orden.estadoc
                this.estadop=orden.estadop
                this.diasfaltantes=this.calcularDias(0,orden)
                this.id_cliente=orden.idcliente
                this.id_articulo=orden.idarticulo
                this.fecha_entrega=orden.fecha_entrega
                this.fecha=orden.fecha
                this.fechaorden=orden.fechaorden
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
           
            cerrarModalc(){
                this.modalc=0
                this.arrayClientes=[]
            },
            cerrarModala(){
                this.modala=0
                this.arrayArticulos=[]
            },
            cerrarModali(){
                this.modali=0
                this.arrayInsumos=[]
            },
            cerrarModalo(){
                this.modalo=0
                this.ordenv=[]
            },
           
          
        },
        mounted() {
            this.listarOrdenes(1,this.buscar,'like',this.criterio);
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
