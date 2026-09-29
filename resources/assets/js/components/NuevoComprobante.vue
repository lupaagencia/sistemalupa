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
                        <i class="fa fa-align-justify"></i> Seleccione un comprobante 
                        <select type="button" v-model="comprobante" @change="mostrarDetalle('nuevo')" class="btn btn-success">
                            <option value="pedido">Pedido</option>
                            <option value="factura">Factura</option>
                            <option value="recibo">Recibo</option>
                            <option value="remisión">Remisión</option>
                        </select>
                    </div>
                    <!-- Listado-->

                    <template>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <h3>Nuevo {{comprobante}}</h3>
                            </div>
                            <div class="col-md-2">
                                <div class="headerOrden">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" @click="registrarComprobante()">Registrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row border p-4 cliente">
                            <div class="col-md-12">
                                 <div class="col-md-3">
                                    <label for="">Fecha {{comprobante}}</label>
                                    <input type="date" class="form-control" v-model="fecha_comprobante">
                                </div>
                                <div class="col-md-3">
                                    <label for="">Forma de pago</label>
                                    <input type="text" class="form-control" v-model="forma_pago">
                                </div>
                                    <div class="col-md-3">
                                    <label class=" form-control-label" for="text-input">Estado</label>
                                    <select v-model="tipo_cliente" class="form-control">
                                        <option value="Natural">Pendiente</option>
                                        <option value="Juridico">En proceso</option>
                                        <option value="Juridico">Entregado</option>
                                    </select>                                    
                                </div>
                                <div class="col-md-3">
                                    <label for="">Número {{comprobante}}</label>
                                    <input type="text" class="form-control" v-model="num_comprobante">
                                </div>

                            </div>
                        </div>
                        <div class="form-group row border p-4 cliente">
                            <!--Inicio modal listado clientes-->
                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalc}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seleccione un cliente</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalc()">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <template v-if="arrayClientes">
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    :class="{'active' : cliente_seleccionado}" 
                                                    v-for="(cliente,index) in arrayClientes" 
                                                    :key="cliente.id" 
                                                    v-text="cliente.nombre"
                                                    @click="getDatosCliente(cliente,index)">
                                                    </a> 
                                                </div>
                                            </template>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" @click="cerrarModalc()" data-dismiss="modal">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- fin modal listado clientes -->
                            <div class="col-md-12">
                               
                                <h4>Datos del Cliente</h4>
                                
                                <div class="form-group">
                                    
                                    <label>Seleccione cliente por nombre(*) <span style="color:red" v-show="idcliente==0">(*Seleccione)</span> </label>
                                    <div class="form-group row">
                                        <div class="">
                                            <div class="form-inline">
                                                <input type="text" class="form-control" v-model="buscar_cliente" @keyup="selectCliente('nuevo')" placeholder="Ingrese nombre del cliente">
                                            </div> 
                                        </div>
                                       
                                        <div class="col-md-12" v-if="cliente_seleccionado!=null">
                                            <div class="form-group row border p-4">
                                                <div class="col-md-3">
                                                    <label for="">Nombre o Razón social</label>
                                                    <input type="text" class="form-control" v-model="nombre">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class=" form-control-label" for="text-input">Tipo cliente</label>
                                                    <select v-model="tipo_cliente" class="form-control">
                                                        <option value="Natural">Natural</option>
                                                        <option value="Juridico">Juridico</option>
                                                    </select>                                    
                                                </div>
                                                <div class="col-md-3">
                                                    <label class=" form-control-label" for="text-input">Tipo Documento</label>
                                                    <select v-model="tipo_documento" class="form-control">
                                                        <option value="NIT">NIT</option>
                                                        <option value="CC">CC</option>
                                                        <option value="PASAPORTE">PASAPORTE</option>
                                                    </select>                                    
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Documento</label>
                                                    <input type="text" class="form-control" v-model="num_documento">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Dirección</label>
                                                    <input type="text" class="form-control" v-model="direccion">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Dirección</label>
                                                    <input type="text" class="form-control" v-model="direccion_envio">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Ciudad</label>
                                                    <input type="text" class="form-control" v-model="ciudad">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Teléfono</label>
                                                    <input type="text" class="form-control" v-model="telefono">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Correo Electrónico</label>
                                                    <input type="email" class="form-control" v-model="email">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Nombre de contacto</label>
                                                    <input type="text" class="form-control" v-model="contacto">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Teléfono del contacto</label>
                                                    <input type="text" class="form-control" v-model="telefono_contacto">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Correo del contacto</label>
                                                    <input type="email" class="form-control" v-model="email_contacto">
                                                </div>
                                                
                                            </div>                                  
                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                           

                        </div>
                        <!-- detalles de trabajo -->
                        <div class="form-group row border p-4 ">
                            <div class="form-group row border p-4 insumos">
                                <!--Inicio del modal insumos-->
                                 <div class="modal fade" tabindex="-1" :class="{'mostrar' : modala}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                    <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Seleccione un producto</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModala()">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <template v-if="arrayArticulos">
                                                    <div class="list-group">
                                                        <a href="#" 
                                                        class="list-group-item list-group-item-action" 
                                                        :class="{'active' : articulo_seleccionado}" 
                                                        v-for="(articulo,index) in arrayArticulos" 
                                                        :key="articulo.id" 
                                                        v-text="articulo.nombre"
                                                        @click="getDatosArticulo(articulo,index), getPrecioUnitario(articulo)">
                                                        </a> 
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" @click="cerrarModala()" data-dismiss="modal">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Fin del modal-->
                                <div class="col-md-12 mt-2">
                                    <h4>Lineas de {{comprobante}}</h4>
                                </div>
                            
                                <div class="col-lg-7 col-xl-3">
                                    <div class="form-group">
                                        <label>Seleccione un producto<span style="color:red" v-show="idarticulo==0">(*Seleccione)</span> </label>
                                        <div class="form-group row">
                                            <div class="col-lg-3">
                                                <div class="form-inline">
                                                    <input type="text" class="form-control" v-model="buscar_articulo" @keyup="selectArticulo('nuevo')" placeholder="Ingrese nombre del producto">
                                                </div>  
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="">Fecha</label>
                                    <input type="date" class="form-control" v-model="fecha" >
                                </div>
                                <div class="col-md-3">
                                    <label for="">Fecha de entrega</label>
                                    <input type="date" class="form-control" v-model="fecha_entrega" >
                                </div>
                                <div class="col-md-2">
                                    <label for="">Cantidad</label>
                                    <input type="text" class="form-control" v-model="cantidad_linea">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Detalle</label>
                                    <input type="text" class="form-control" v-model="nombre_articulo">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Valor unitario</label>
                                    <input type="text" class="form-control" v-model="precio">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Descuento</label>
                                    <input type="text" class="form-control" v-model="descuento_linea">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Subtotal</label>
                                    <div class="form-control">
                                    {{calcularSubtotalLinea}}
                                    </div>
                                </div>
                            
                                <div class="col-md-2">
                                    <label for="">Impuesto</label>
                                     <input type="text" class="form-control" v-model="impuesto_linea">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Total</label>
                                    <div class="form-control">
                                    {{calcularTotalLinea}}
                                    </div>
                                </div>
                                
                                <div class="col-lg-2 col-xl-3">
                                    <div class="form-group">
                                        <button @click="agregarLinea()" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></button>
                                    </div>
                                </div>

                                <!-- lista de costos del trabajo -->
                                <div class="form-group col-md-12 border p-4">
                                    <div class="table-responsive col-md-12">
                                        <table class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Fecha</th>
                                                    <th>Fecha de entrega</th>
                                                    <th>Cantidad</th>
                                                    <th>Detalle</th>
                                                    <th>Valor unitario</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                    <th>Impuesto</th>
                                                    <th>Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody v-if="arrayLineas.length">
                                                <tr v-for="(linea,index) in arrayLineas" :key="linea.id">
                                                    <td>
                                                        <button @click="eliminarLinea(index)" type="button" class="btn btn-danger btn-sm">
                                                            <i class="icon-close"></i>
                                                        </button>
                                                    </td>
                                                    <td >
                                                        <input type="text" v-model="linea.fecha"  value="3" class="form-control">
                                                    </td>
                                                    <td >
                                                        <input type="text" v-model="linea.fecha_entrega"  value="3" class="form-control">
                                                    </td>
                                                    <td >
                                                        <input type="text" v-model="linea.cantidad"  value="3" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text" v-model="linea.articulo"  value="3" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text" v-model="linea.valor" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.descuento" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.subtotal" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.impuesto" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.total" class="form-control">
                                                    </td>
                                                   
                                                </tr>

                                            </tbody>  
                                            <tbody v-else>
                                                <tr>
                                                    <td colspan="5"> 
                                                       No hay lineas de {{comprobante}}
                                                    </td>
                                                </tr>  
                                            </tbody>                               
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4">
                                <div class="form-group row border p-4">
                                    <div class="table-responsive col-md-12">
                                        <table class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Subtotal</th>
                                                    <th>Descuento</th>
                                                    <th>Impuesto</th>
                                                    <th>Total</th>
                                                    <th>Abono</th>
                                                    <th>Saldo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="background-color: #CEECF5;">
                                                    <td>$ {{calcularSubtotal}}</td>
                                                    <td>$ {{calcularTotalDescuento}}</td>
                                                    <td>$ {{calcularTotalImpuesto}}</td>
                                                    <td>$ {{calcularTotalParcial}}</td>
                                                    <td>$ <input type="text" v-model="abono"></td>
                                                    <td>$ {{calcularSaldo}}</td>
                                                   
                                                </tr>
                                            </tbody>  
                                                                    
                                        </table>
                                    </div>
                                </div>
                            </div>
                       
                      
                      
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" @click="registrarPedido()">Registrar</button>
                            </div>
                        </div>
                    </div>
                    </template>
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
                listado:0,
                fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                comprobante : 'pedido',
                num_comprobante : '',
                forma_pago:'Contado',
                abono:0,
                saldo:0,
                impuesto: 0,
                descuento:0,
                subtotal:0,
                total:0,
                cliente_seleccionado:null,
                buscar_cliente:'',
                modalc:0,
                idcliente:0,
                nombre:'',
                tipo_documento:'',
                num_documento:'',
                direccion:'',
                direccion_envio:'',
                telefono:'',
                email:'',
                contacto:'',
                telefono_contacto:'',
                email_contacto:'',
                valor_articulo:0,
                totalImpuesto:0,
                totalParcial:0,
                arrayClientes: [],
                cseleccionado:false,
                pais:'Colombia',
                departamento:'Valle del Cauca',
                ciudad:'Cali',
                contacto:'',
                telefono_contacto:'',
                email_contacto:'',
                modala:0,
                arrayArticulos:[],
                articulo_seleccionado:null,
                aseleccionado:false,
                idarticulo:0,
                nombre_articulo:'',
                precio:0,
                impuesto_artiuclo:0,
                descuento_artiuclo:0,
                fecha_entrega:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                cantidad_linea:1000,
                detalle_linea:'',
                precio:0,
                descuento_linea:0,
                impuesto_linea:0,
                iva_linea:0,
                subtotal_linea:0,
                total_linea:0,
                tamano_linea:'',
                medida_final_linea:'',
                arrayLineas:[],
                tituloModal : '',
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                buscar_cliente: '',
                buscar_articulo:'',
                topedit:0,
                dominio:'',
               
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
            calcularImpuestoLinea(){
                var resultado=0
                this.iva_linea=parseFloat(this.subtotal_linea)*parseFloat(this.impuesto_linea);
                for(var i=0;i<this.arrayLineas.length;i++){
                    this.arrayLineas[i].iva=parseFloat(this.arrayLineas[i].subtotal)*parsefloat(this.arrayLineas[i].impuesto)
                }
                return resultado
            },
            calcularSubtotalLinea(){
                var resultado=0
                this.subtotal_linea=parseFloat(this.precio)*parseFloat(this.cantidad_linea)-parseFloat(this.descuento_linea);
                resultado=this.subtotal_linea
                for(var i=0;i<this.arrayLineas.length;i++){
                    this.arrayLineas[i].subtotal=parseFloat(this.arrayLineas[i].valor)*parseInt(this.arrayLineas[i].cantidad)-parseInt(this.arrayLineas[i].descuento)
                }
                return resultado
            },
            
            calcularTotalLinea(){
                var resultado=0
                if(this.impuesto_linea==''){
                    this.impuesto_linea=0
                }
                this.total_linea=parseFloat(this.subtotal_linea)*(1+parseFloat(this.impuesto_linea))
                resultado=this.total_linea
                for(var i=0;i<this.arrayLineas.length;i++){
                    if(this.arrayLineas[i].impuesto==''){
                        this.arrayLineas[i].impuesto=0
                    }
                    this.arrayLineas[i].total=parseFloat(this.arrayLineas[i].subtotal)*(1+parseFloat(this.arrayLineas[i].impuesto))
                }
                return resultado
            },
          
           
           
            calcularTotalDescuento(){
                var resultado=0
                this.arrayLineas.forEach(e => {
                    resultado=resultado+parseFloat(e.descuento)
                });
                return resultado
            },
           
            calcularTotalImpuesto(){
                var resultado=0
                var iva=0
                this.arrayLineas.forEach(e => {
                    e.iva=e.subtotal*(e.impuesto)
                    iva=iva+parseFloat(e.iva)
                })
                resultado=iva
                return resultado
            },
            
            calcularSubtotal(){
                var resultado=0
                var subtotal=0
                this.arrayLineas.forEach(e => {
                    var sub=parseFloat(e.subtotal)+parseFloat(e.descuento)
                    subtotal=subtotal+sub
                })
                resultado=subtotal
                return resultado
            },
            calcularTotalParcial(){
                var resultado=0
                var iva=0
                var subtotal=0
                var descuento=0
                this.arrayLineas.forEach(e => {
                    subtotal=subtotal+parseFloat(e.subtotal)
                    iva=iva+parseFloat(e.iva)
                    descuento=descuento+parseFloat(e.descuento)
                })
                resultado=subtotal+iva
                return resultado
            },
            calcularSaldo(){
                var resultado=0
                var iva=0
                var subtotal=0
                var descuento=0
                this.arrayLineas.forEach(e => {
                    subtotal=subtotal+parseFloat(e.subtotal)
                    iva=iva+parseFloat(e.iva)
                    descuento=descuento+parseFloat(e.descuento)
                })
                resultado=(subtotal+iva)-this.abono
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
             getPrecioUnitario(val){
                let me=this
                me.precio_producto=val.precio_venta
            },
            getDatosArticulo(val1, index){
                let me = this;
                me.loading = true;
                me.idarticulo = val1.id;
                me.nombre_articulo = val1.nombre;
                me.precio = val1.precio_venta;
                me.tamano_linea = val1.tamano;
                me.medida_final_linea = val1.medida_final;
                me.impuesto_linea = 0;
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
            agregarLinea(){
                let me=this
                
                if(me.tipo_costo=='' || me.valor_costo==''){

                }else{
                    me.arrayLineas.push({
                        idarticulo:0,
                        impuesto:me.impuesto_linea,
                        descuento_artiuclo:me.descuento_linea,
                        fecha:me.fecha,
                        fecha_entrega:me.fecha_entrega,
                        cantidad:me.cantidad_linea,
                        articulo:me.nombre_articulo,
                        valor:me.precio,
                        iva:parseFloat(this.subtotal_linea)*parseFloat(this.impuesto_linea),
                        descuento:me.descuento_linea,
                        valor_total:me.total_linea,
                        subtotal:me.subtotal_linea,
                        tamano: me.tamano_linea,
                        medida_final: me.medida_final_linea,
                        tipo_cantidad:me.articulo_seleccionado.tipo_cantidad
                    }) 
                    me.nombre_articulo=''
                    me.iva_linea=0,
                    me.impuesto_linea=0
                    me.descuento_linea=0
                    me.fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                    me.fecha_entrega=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                    me.cantidad_linea=1000
                    me.detalle_linea=''
                    me.precio=0
                    me.descuento_linea=0
                    me.subtotal_linea=0
                    me.total_linea=0
                }
               
                
            },
            getDatosInsumos(val1, index){
                let me = this;
                me.loading = true;
                me.idcosto = val1.id;
                me.tipo_costo=val1.tipo_costo;
                me.nombre_insumo=val1.nombre;
                me.valor_insumo=val1.valor
                me.cabida=val1.cabida
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
                const pedido = new FormData()
                pedido.set('estadoc',this.estadoc)
                pedido.set('estadop',this.estadop)
                pedido.set('id_cliente', this.cliente_seleccionado.id)
                pedido.set('fecha' , this.fecha)
                pedido.set('carpeta_cliente' , this.carpeta_cliente)
                pedido.set('detalles_diseno' , this.detalles_diseno)
                pedido.set('observaciones' , this.observaciones)
                pedido.set('unidad' , this.unidad)
                pedido.set('tamano', this.tamano)
                pedido.set('medida_material', this.medida_material)
                pedido.set('cantidad', this.cantidad)
                pedido.set('totalParcial',this.totalParcial)
                pedido.set('descuento',this.descuento)
                pedido.set('impuesto',this.impuesto)
                pedido.set('total',this.total)
                pedido.set('abono',this.abono)
                pedido.set('saldo',this.saldo)
                pedido.set('detalles',JSON.stringify(this.arrayLineas))
                axios.post(me.dominio+'/orden/registrar',orden1)
                .then(function (response) {
                    me.listado=1
                }).catch(function (error) {
                    console.log(error);
                });
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
                orden1.set('tamano', this.tamano)
                orden1.set('medida_material', this.medida_material)
                orden1.set('cantidad', this.cantidad)
                orden1.set('totalParcial',this.totalParcial)
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
                this.filtrarOrdenes(1,this.buscar);
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
                this.filtrarOrdenes(1,this.buscar);
            },
           
            copiarOrden(orden){
                let me=this
                var url= me.dominio+'/orden/duplicar?id='+ orden.idorden
                axios.post(url).then(function (response) {
                    var respuesta= response.data;
                }).catch(function (error) {
                    console.log(error);
                });
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
                this.tamano=''
                this.medida_material=''
                this.detalles_diseno=''
                this.observaciones=''
                this.cantidad=1000
                this.unidad=''
                this.carpeta_cliente=''
                this.descuento=0
                this.saldo=0
                this.precio_producto=0
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
            
            filtrarOrdenes(page,buscar){
                var me=this
                me.buscar=buscar
                var url= me.dominio+'/orden/filtrarOrdenes?page='+page+'&buscar='+buscar;
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
                this.tamano= orden.tamano
                this.medida_material= orden.medida_material
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
                this.tamano= orden.tamano
                this.medida_material= orden.medida_material
                this.cantidad= orden.cantidad
                this.subtotal_orden=orden.totalParcial
                this.precio_producto=orden.totalParcial/orden.cantidad
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
       
    }
</script>
<style>  
    .insumos, .producto, .cliente{
        position: relative;
    } 
    .mostrar{
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 10500 !important;
        background-color: rgba(0,0,0,0.5) !important;
        overflow: hidden !important;
    }
    .mostrar .modal-dialog,
    .modal-bajo {
        margin: 10px auto !important;
        top: 0 !important;
        align-self: flex-start !important;
        max-height: calc(100vh - 20px) !important;
        height: calc(100vh - 20px) !important;
        display: flex !important;
        flex-direction: column !important;
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

</style>

