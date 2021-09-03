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
                                            <button v-if="action=='nuevo'" type="button" class="btn btn-primary" @click="registrarComprobante()">Registrar</button>
                                            <button v-else type="button" class="btn btn-primary" @click="actualizarComprobante()">Guardar</button>
                                        </div>
                                    </div>
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
                            
                                <div class="col-lg-7 col-xl-6">
                                    <div class="form-group">
                                        <label>Seleccione un producto<span style="color:red" v-show="idarticulo==0">(*Seleccione)</span> </label>
                                        <div class="form-group row">
                                            <div class="col-lg-3">
                                                <div class="form-inline">
                                                    <input type="text" class="form-control" v-model="buscar_articulo" @keyup="selectArticulo('nuevo')" placeholder="Ingrese nombre del producto">
                                                </div>  
                                            </div>
                                            <div class="col-lg-12" v-if="articulo_seleccionado!=null">
                                                    <div class="form-group row border p-4 subform">
                                                    <div class="col-md-3">
                                                        <label for="">Producto</label>
                                                        <input type="text" class="form-control" v-model="nombre_articulo">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="">Valor producto</label>
                                                        <input type="text" class="form-control" v-model="precio">
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        <label for="">IVA</label>
                                                        <input type="text" class="form-control" v-model="descuento_articulo">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="">IVA</label>
                                                        <input type="text" class="form-control" v-model="impuesto_articulo">
                                                    </div>
                                                </div>                                  
                                            </div>
                                        </div>
                                    </div>
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
                                    <input type="text" class="form-control" v-model="detalle_linea">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Valor unitario</label>
                                    <input type="text" class="form-control" v-model="valor_unitario">
                                </div>
                                <div class="col-md-2">
                                    <label for="">Descuento</label>
                                    <input type="text" class="form-control" v-model="descuento_linea">
                                </div>
                            
                                <div class="col-md-2">
                                    <label for="">Impuesto</label>
                                    <input type="text" class="form-control" v-model="impuesto_linea">
                                </div>
                                <div class="col-md-12">
                                    <label for="">Total</label>
                                    <textarea style="color:red" class="form-control" v-model="total_linea">  </textarea>
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
                                                        <input type="text" v-model="linea.artiuclo"  value="3" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text" v-model="linea.valor_unitario" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.descuento" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.impuesto" class="form-control">
                                                    </td>
                                                    <td>
                                                        <input type="text"  v-model="linea.valor_total" class="form-control">
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
                                                    <th>Valor unitario</th>
                                                    <th>Cantidad</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                    <th>Impuesto</th>
                                                    <th>Total</th>
                                                    <th>Abono</th>
                                                    <th>Saldo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="background-color: #CEECF5;">
                                                    <td>$ <input type="text" v-model="precio_producto"> </td>
                                                    <td><input type="text" v-model="cantidad"> </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" class="form-control" v-model="descuento" placeholder="Descuento">
                                                        </div>
                                                    </td>
                                                    <td>$ {{calcularTotalParcial}}</td>
                                                    <td>$ {{calcularTotalImpuesto}}</td>
                                                    <td>$ {{calcularTotal.toFixed(0)}}</td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" class="form-control" v-model="abono" placeholder="Abono">
                                                        </div>
                                                    </td>
                                                    <td>$ {{calcularSaldo.toFixed(0)}}</td>
                                                </tr>
                                            </tbody>  
                                                                    
                                        </table>
                                    </div>
                                </div>
                            </div>
                       
                      
                      
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</button>
                                <button v-if="action=='nuevo'" type="button" class="btn btn-primary" @click="registrarOrden()">Registrar</button>
                                <button v-else type="button" class="btn btn-primary" @click="actualizarOrden()">Guardar</button>
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
                fecha_entrega:new Date(),
                cantidad_linea:1000,
                detalle_linea:'',
                valor_unitario:'',
                descuento_linea:'',
                impuesto_linea:'',
                total_linea:'',
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
           
            calcularTotalParcial(){
                var resultado=0
                resultado=(this.precio_producto*this.cantidad)-this.descuento
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
