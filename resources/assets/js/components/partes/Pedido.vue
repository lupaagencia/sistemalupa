    

<template>
    <div class="contenedor" v-scroll="handleScroll">
        <div class="contenedor-header" :class="{'stick':fixed}">
             
                <div class="col-sm-9">
                    <h2 v-if="edit==0">Nuevo Pedido</h2>
                    <h2 v-else>{{ pedido.tipo === 'cuentacobro' ? 'Cuenta de Cobro' : 'Pedido' }} {{pedido.id}}</h2>
                </div>
                <div class="col-sm- mt-2 botones">
                    
                    <button type="button"  @click="cancelar()" class="btn btn-primary ">
                        Cancelar
                    </button>
                    <button v-if="edit==0" type="button" @click="crearPedido()" class="btn btn-success">
                        Guardar
                    </button>
          
                    <button v-else type="button" @click="crearPedido()" class="btn btn-success">
                        Actualizar
                    </button>
                    
                </div>
            
        </div>
        <div class="card-body" id="pedido">
            <button @click="imprimir()">imprimir</button>
            <div class="row">
                <div class="col-sm-3">
                    <label for="">Fecha</label>
                    <input type="date" class="form-control" v-model="pedido.fecha">
                </div>
                <div class="col-sm-3">
                    <label for="">Forma de pago</label>
                    <input type="text" class="form-control" v-model="pedido.forma_pago">
                </div>
               <div class="col-sm-3">
                    <label for="">Estado</label>
                    <select name="estado" class="form-control" v-model="pedido.estado">
                        <option value="1">Pendiente</option>
                        <option value="2">En produccion</option>
                        <option value="3">Completado</option>
                        <option value="4">Para entregar</option>
                        <option value="5">Entregado</option>
                        <option value="6">No recogido</option>
                    </select>
               </div>
               
                 
                <div class="col-sm-3">
                    <label for="">Transportadora</label>
                    <input type="text" class="form-control" v-model="pedido.transportadora">
                </div>
            </div>
                <div class="contenedor-seccion ">
                    <div class="seccion-header dcliente">
                        <div class="form-group row">
                            <div class="">
                              
                                <div class="form-inline">
                                    <input type="text" class="" v-model="buscar_cliente" @keyup="selectCliente()" placeholder="Ingrese nombre del cliente">
                                </div> 
                            </div>
                            
                        </div>
                    </div>
                    <div class="seccion-body">
                         <table class="table w-100 header-comprobante">
                            <tr><th>Nombre de la Cuenta</th><td><h4>{{pedido.cliente.razonsocial}}</h4></td></tr>
                            <tr>
                                <th >Contacto</th><th>Telefono contacto</th><th>Correo</th>
                            </tr>
                            <tr v-for="(cont, index) in pedido.cliente.contactos" :key="index">
                                <td v-text="cont ? cont.nombre : ''"></td>
                                <td v-text="cont ? cont.telefono : ''"></td>
                                <td v-text="cont ? cont.correo : ''"></td></tr>
                           
                            <tr><th>Razon social</th><th>Documento</th><th>Telefono</th><th>Direccion empresa</th>
                            </tr>

                            <tr v-for="(em,index) in pedido.cliente.empresas" :key="index">
                                        <td v-text="em.razonsocial"></td><td >{{em.tipo_documento}}: {{em.num_documento}} {{em.digito}}</td>
                                        <td v-text="em.telefono"></td><td >{{em.direccion}}, {{em.ciudad}}</td>
                            </tr>
                            
                            <tr><th>Destinatario envio</th><th>Documento</th><th>Telefono destinatario</th><th>Direccion de envio</th>
                            </tr>
                            <tr v-for="(en,index) in pedido.cliente.envios" :key="index">
                                        <td>{{en.contacto}} - {{en.empresa}}</td><td >{{en.tipo_documento}}: {{en.documento}} </td>
                                        <td v-text="en.telefono"></td><td >{{en.direccion}}, {{en.ciudad}}</td>
                            </tr>
                           
                        </table>
                        
                    </div>
                </div>
                 <div class="contenedor-seccion ">
                    
                    <div class="seccion-header dcliente">
                        
                        <h4>Detalles productos</h4>
                        <div class="form-group row">
                            <div class="">
                                <div class="form-inline">
                                    <input type="text" class="from-control" v-model="buscar_producto" @keyup="selectArticulo('nuevo')" placeholder="Ingrese nombre del producto">
                                </div> 
                            </div>
                        </div>
                        
                    </div>
                    <div class="contenedor-seccion">
                            <!-- <div class="form-group row seccion-header">
                                <div class="">
                                    <div class="form-inline">
                                        <button class="btn btn-link" @click="selectOrden()">Listar ordenes</button>
                                    </div> 
                                </div>
                            </div> -->
                            <div class="seccion-body ordenes-cliente">
                                <div class="form-check" v-for="(orden,index) in arrayOrdenes" :key="index">
                                    <input class="form-check-input" type="checkbox" :value="orden.id" id="flexCheckDefault" @change="agregarOrden(orden,index,$event)">
                                    <label class="form-check-label" for="flexCheckDefault" >
                                        {{orden.articulo ? orden.articulo.nombre : 'Cargando...'}} 
                                    </label>
                                </div>
                            </div>
                            <div v-if="clienteno==1">
                                <div class="seccion-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label>Seleccione un cliente</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="clienteno==2">
                                <div class="seccion-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label>No se encontraron registros</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="seccion-body" v-if="pedido && pedido.lineas && pedido.lineas.length">
                        <table class="table">
                            
                            <tr>

                            <th>Opciones orden</th><th>Cantidad</th><th>Nombre</th><th >Detalles</th><th>V. Unit. </th><th>Subtotal</th><th>Descuento</th><th>Total</th>
                            </tr>
                            
                            <tr v-for="(linea,index) in pedido.lineas" :key="index">
                                 <td> <div v-if="opcionproduccion==index" class="row contenedor-seccion oproduccion">
                                        <detalleorden :orden="linea.orden" :articulo="linea.articulo" :user="user" @cerrarOpcionePro="cerrarOpcionePro"></detalleorden>
                                            
                                    </div>
                                    <button v-else class="btn btn-link" @click="abrirOpcionePro(linea,index)"><i class="fa fa-cog" aria-hidden="true"></i></button>
                                    <button class="btn btn-link" @click="editarLinea(linea)"><i class="fa fa-edit" aria-hidden="true"></i></button>
                                    <button class="btn btn-danger" @click="eliminar(index)" ><i class="fa fa-trash" aria-hidden="true"></i></button>
                                    <div v-if="borrarl==index" class="alert alert-danger adeliminar" role="alert">
                                        <div class="card" style="width: 18rem;">
                                            <div class="card-header">
                                                <h5 class="card-title">Seguro quiere eliminar?</h5>

                                            </div>
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input type="checkbox" v-model="borrarorden"  class="form-check-input" id="exampleCheck1">
                                                    <label class="form-check-label" for="exampleCheck1">Eliminar orden de trabajo</label>
                                                </div>
                                                
                                            </div>
                                            <aside class="card-body">
                                                <a class="btn btn-success" href="#" @click="noeliminar()" >Cancelar</a>
                                                <a  class="btn btn-danger" href="#" @click=eliminarLinea(index) >Eliminar</a>
                                            </aside>
                                        </div>
                                    </div>
                                    {{linea.ordentrabajo_id}}
                                </td> 
                                
                                <td> 
                                    <div>
                                        <input type="text" class="form-control" @keyup="totales(linea)"  v-model="linea.cantidad">
                                    </div>
                                </td>
                                <td>
                                    <strong>{{linea.articulo ? linea.articulo.nombre : 'N/A'}}</strong>
                                    <div v-if="linea.ordentrabajo_id" class="mt-1">
                                        <span class="badge badge-success">Orden #{{linea.ordentrabajo_id}}</span>
                                    </div>
                                </td>
                                <td>
                                    <ul class="detallesp">
                                        <li v-for="(detalle,index) in linea.detalles" :key="index"  >
                                            <div><strong >{{detalle.titulo}}: &nbsp;</strong> </div>
                                            <div class="atributo d-flex flex-wrap" v-if="esColor(detalle)">
                                                <div v-for="(valor,i) in parseColorValores(detalle.valor)" :key="i" :style="'padding:2px 6px; color:#fff; background:'+valor.hex+'; border-radius:4px; font-weight:bold; font-size:11px; margin-right:4px; margin-bottom:4px; display:inline-block;'">
                                                    {{ valor.pantone || valor.nombre || '' }} 
                                                </div> &nbsp;
                                            </div> 
                                            <div v-else class="atributo" >
                                                <div>
                                                    {{ detalle.valor}} - {{detalle.descripcion}}  &nbsp; 
                                                </div>
                                            </div> 
                                        </li>
                                    </ul>
                                </td>
                            
                                <td class="valores"> 
                                    <div>
                                        $ <input type="text" class="form-control" @keyup="totales()" v-model="linea.valor_unitario">
                                    </div>
                                </td>
                                <td class="valores"> 
                                    <div>
                                        $ <div type="text" class="form-control" v-text="linea.subtotal"></div>
                                    </div>
                                </td>
                                <td class="valores"> 
                                    <div>
                                        $ <input type="text" class="form-control" @keyup="totales()" v-model="linea.descuento">
                                    </div>
                                </td>
                                <td class="valores"> 
                                        $ <div type="text" class="form-control" v-text="linea.valor_total"></div>
                                </td>
                            </tr>
                            <tfoot>
                                <tr v-if="pedido.tipo !== 'cuentacobro'">
                                    <td class="col-sm-3">
                                        <label for="">Abono</label>
                                        <input type="text" class="form-control" v-model="pedido.abono" @change="total" readonly>
                                    </td>
                                    <td class="col-sm-3">
                                        <label for="">IVA</label>
                                        <input type="text" class="form-control" v-model="pedido.iva" @change="total">
                                    </td>
                                </tr>
                                <tr v-else>
                                    <td class="col-sm-3">
                                        <label for="">IVA</label>
                                        <input type="text" class="form-control" v-model="pedido.iva" @change="total">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Descuento</strong> $ {{pedido.descuento}}</td>
                                    <td colspan="2"><strong>Subtotal</strong> $ {{pedido.subtotal}}</td>
                                    <td colspan="2"><strong>Iva</strong> $ {{pedido.impuestos}}</td>
                                     <td colspan="2"><strong>Total</strong> $ {{total}}</td>
                                </tr>
                                 <tr>
                                    <th colspan="4"></th>
                                    <th colspan="2" v-if="pedido.tipo !== 'cuentacobro'">
                                        Abono: $ {{pedido.abono}}
                                    </th>
                                    <th :colspan="pedido.tipo === 'cuentacobro' ? 4 : 2">
                                        Saldo: $ {{pedido.saldo}}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-sm- mt-2 botones">
                        
                        <button type="button"  @click="cancelar()" class="btn btn-primary boton-principal">
                            Cancelar
                        </button>
                        <button v-if="edit==0" type="button" @click="crearPedido()" class="btn btn-success boton-principal">
                            Guardar
                        </button>
            
                        <button v-else type="button" @click="crearPedido()" class="btn btn-success boton-principal">
                            Actualizar
                        </button>

                    </div>
                    <customproducto :modal="modalp" :producto="productoSelect" @productoCustom="productoCustom" @cerrarCustomProducto="cerrarCustomProducto"></customproducto>
                </div>
               
                <lproducto :modal="modala" :scroll="scroll"  :productos="this.arrayProductos" @productoCustom="productoCustom" @productoSeleccionado="productoSeleccionado" @cerrarModal="modala=0"></lproducto>
                <lclientes :modal="modalc" :scroll="scroll" :clientes="this.arrayClientes" @clienteSeleccionado="clienteSeleccionado" @cerrarModal="modalc=0"></lclientes>
        
        </div>
    </div>
<!--Fin del modal-->
</template>

<script>
Vue.directive('scroll', {
  inserted: function (el, binding) {
    let f = function (evt) {
      if (binding.value(evt, el)) {
        window.removeEventListener('scroll', f)
      }
    }
    window.addEventListener('scroll', f)
  }
})
//funcion para cotizar cajas de carton con estilos css en vue.js?
 import lclientes from './ListaClientes'
 import lproducto from './ListaArticulo'
 import customproducto from './customProducto'
 import detalleorden from '../DetallesOrden'
 import html2pdf from "html2pdf.js"
 var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
 
 export default {
    props:['dato', 'edit','user','pedido'],
    data(){
        return{
            borrarl:-1,
            borrarorden:0,
            opcionproduccion:-1,
            fixed:0,
            iva:19,
            newCliente:0,
            arrayClientes:[],
            arrayProductos:[],
            arrayOrdenes:[],
            buscar_cliente:'',
            buscar_producto:'',
            modalc:0,
            modala:0,
            modalp:0,
            clienteno:0,
            clienteSelect:{'nombre':''},
            productoSelect:{},
            cambios:'',
            scroll:0
            
        }
    },
    components: {
        lclientes,
        lproducto,
        customproducto,
        detalleorden
    },
    computed:{
        
       total(){
            var valor=0;
            var descuento=0;
            this.pedido.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
                valor=valor+parseInt(e.valor_total)
                descuento=descuento+parseInt(e.descuento)
            })
            this.pedido.subtotal=valor
            this.pedido.descuento=descuento
            this.pedido.impuestos=this.pedido.subtotal*this.pedido.iva
            var valor=parseFloat(this.pedido.impuestos)+parseFloat(this.pedido.subtotal)
            this.pedido.saldo = Math.max(0, valor - (parseInt(this.pedido.abono) || 0))
            this.pedido.total=valor
            var resultado=valor
            return valor
        },
        
        
        },  
    methods:{
        imprimir(){
            var element = document.getElementById('pedido');
            html2pdf(element);
        },
        totales(line){
           
                
                // me.orden.detalles.forEach(o => {
                //     if(o.titulo=='Papel'){
                //         if(o.costo){
                //             if(numero!=0){
                //                 o.costo.descripcion=parseFloat(me.orden.cantidad/numero)+parseInt(me.orden.carpeta_cliente)
                //             }else{
                //                 o.costo.descripcion=parseFloat(me.orden.cantidad/me.orden.cabida)+parseInt(me.orden.carpeta_cliente)
                //             }
                //             o.costo.cantidad =o.costo.descripcion/me.orden.tamano
                //         }
                //     }
                // });
           
            var valor=0;
            var descuento=0;
            if (line.orden && line.orden.detalles) {
                line.orden.detalles.forEach(d=>{
                    if(d.titulo=='Papel' && d.costo){
                        line.orden.cantidad=parseInt(line.cantidad)
                        console.log(line.orden.cantidad)
                        d.costo.descripcion=parseFloat(line.orden.cantidad)/parseFloat(line.orden.cabida)+parseFloat(line.orden.carpeta_cliente)
                        d.costo.cantidad=d.costo.descripcion/parseFloat(line.orden.tamano)
                    }
                })
            }
            this.pedido.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
                valor=valor+parseInt(e.subtotal)
                descuento=descuento+parseInt(e.descuento)
                
            })
            this.pedido.subtotal=valor
            this.pedido.descuento=descuento
            this.pedido.impuestos=this.pedido.subtotal*this.pedido.iva
            var valor=parseFloat(this.pedido.impuestos)+parseFloat(this.pedido.subtotal)
            this.pedido.saldo=valor-parseInt(this.pedido.abono)
            this.pedido.total=valor
            var resultado=valor
            return valor
        },
        subTotalProducto(){
            var valor=0;
            this.pedido.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
            })
            
           
        },
         
        impuesto(){
            var valor=0;
            this.pedido.impuestos=this.pedido.subtotal*0.19
        },
        subTotal(){
            var valor=0;
            this.pedido.lineas.forEach(e =>{
                valor=valor+parseInt(e.subtotal)
            })
            var resultado=valor
            this.pedido.subtotal=valor
        },
        descuento(){
             var descuento=0;
            this.pedido.lineas.forEach(e =>{
                descuento=descuento+parseInt(e.descuento)
            })
            var resultado=descuento
            this.pedido.descuento=descuento
        },
        abrirOpcionePro(producto,index){
            this.opcionproduccion=index
            if (producto.orden && producto.orden.detalles) {
                producto.orden.detalles.forEach(e => {
                    if(e.costo){
                        if(e.costo.costois==null){
                            e.costo.costois={'nombre':''}
                        }
                    }
                });
            }
        },
        cerrarOpcionePro(){
            this.opcionproduccion=-1
        },
        handleScroll: function (evt, el) {
            this.scroll=window.scrollY
            if (window.scrollY > 80) {
                this.fixed=1
            }else{
                this.fixed=0
            }
        },
        cancelar(){
            this.buscar_cliente = '';
            this.buscar_producto = '';
            this.arrayOrdenes = [];
            this.$emit('ocultarDetalle', 1, this.pedido.estado);
        },
      
        selectOrden(){
            let me=this;
            if(me.pedido.cliente.id){
                var url= '/cliente/selectOrdenesCliente?cliente_id='+me.pedido.cliente.id;
                axios.get(url).then(function (response) {
                let respuesta = response.data;
                
                console.log(respuesta);
                if(respuesta.length){
                    me.arrayOrdenes=respuesta
                    me.clienteno=0
                }else{
                    me.clienteno=2
                }
                })
                .catch(function (error) {
                    console.log(error);
                });
            }else{
                me.clienteno=1
            }
           
        },
         
        selectCliente(){
            let me=this;
            
            var url= '/cliente/selectClientes?filtro='+this.buscar_cliente;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                
                me.arrayClientes = respuesta.clientes || [];
                me.modalc = 1;
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        selectArticulo(){
            let me=this;
            
            var url= '/articulo/selectArticulo?filtro='+this.buscar_producto;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.arrayProductos=respuesta.articulos;
                me.modala=1
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        
        clienteSeleccionado(value){
            this.pedido.cliente=value
            var empresa =this.pedido.cliente.empresas.filter((e)=>e.favorito==1)
            var contacto =this.pedido.cliente.contactos.filter((e)=>e.favorito==1)
            var envio =this.pedido.cliente.envios.filter((e)=>e.favorito==1)
            this.pedido.cliente.empresas=empresa
            this.pedido.cliente.contactos=contacto
            this.pedido.cliente.envios=envio
            this.modalc=0
            this.newCliente=0
            this.selectOrden()
        },
      
        noeliminar(){
            this.borrarl=-1
        },
        eliminar(index){
            this.borrarl=index
        },
        eliminarLinea(index){
            
            let me=this
            var userj=me.user
                if(me.pedido.lineas[index].id>0){
                    if(me.borrarorden==true || me.borrarorden==1){
                        me.cambios= ('Eliminado linea pedido No. '+me.pedido.id+', producto: '+(me.pedido.lineas[index].articulo ? me.pedido.lineas[index].articulo.nombre : 'N/A')+', y se elimina orden No.: '+me.pedido.lineas[index].ordentrabajo_id)+me.cambios
                    }else{
                        me.cambios= ('Eliminado linea pedido No. '+me.pedido.id+', producto: '+(me.pedido.lineas[index].articulo ? me.pedido.lineas[index].articulo.nombre : 'N/A'))+me.cambios

                    }
                var url= '/pedido/eliminarLinea?id='+ me.pedido.lineas[index].id+ '&idorden='+me.pedido.lineas[index].ordentrabajo_id+'&borrarorden='+me.borrarorden+'&user_id='+userj.id+'&cambios='+me.cambios+'&_method=DELETE';
                axios.delete(url).then(function (response) {
                    var respuesta= response.data;
                    me.noeliminar()
                    me.pedido.lineas.splice(index,1)
                })
                .catch(function (error) {
                    console.log(error);
                });
                
            }else{
                me.pedido.lineas.splice(index,1)
            }

        },
        
        productoCustom(value){
            console.log(value)
            if (!value.orden) {
                this.$set(value, 'orden', {
                    id: 0,
                    medida_final: value.medida_final || '',
                    medida_material: 1,
                    tamano: value.tamano || 1,
                    cabida: 1,
                    plancha: 1,
                    prioridad: 'normal',
                    carpeta_cliente: 30,
                    detalles_diseno: null,
                    observaciones: null,
                    cantidad: value.cantidad || 1000,
                    detalles: [],
                    articulo: value
                })
            } else {
                if (!value.orden.tamano) value.orden.tamano = value.tamano || 1;
                if (!value.orden.medida_final) value.orden.medida_final = value.medida_final || '';
                if (!value.orden.cabida) value.orden.cabida = 1;
                if (!value.orden.plancha) value.orden.plancha = 1;
            }
            this.pedido.lineas.push(value)
            
            this.modalp=0
            this.modala=0
        },
       
        agregarOrden(value,index,event){
            console.log(event.target.checked)
          if(event.target.checked){
            value.id=0
            value.ordentrabajo_id=0
            value.detalles=value.orden.detalles
            value.orden.produccion='ENP'
            value.orden.fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
            value.medida_final=value.orden.medida_final
            value.medida_material=value.orden.medida_material
            value.tamano=value.orden.tamano
            value.cabida=value.orden.undad_medida
            value.plancha=value.orden.plancha
            value.prioridad=value.orden.prioridad
            value.carpeta_cliente=value.orden.carpeta_cliente
            value.detalles_diseno=value.orden.detalles_diseno
            value.observaciones=value.orden.observaciones
              
            this.pedido.lineas.push(value)
          }else{
              this.pedido.lineas.splice(index,1)
          }
         
        },
         cerrarCustomProducto(value){
            this.modalp=value
        },
        productoSeleccionado(value){
            this.productoSelect=value
            this.$set(this.productoSelect, 'orden', {
                id: 0,
                produccion: 'ENP',
                medida_material: 1,
                tamano: value.tamano || 1,
                medida_final: value.medida_final || '',
                cabida: 1,
                plancha: 1,
                prioridad: 'normal',
                carpeta_cliente: 1,
                detalles_diseno: null,
                observaciones: null,
                cantidad: 1000,
                detalles: [],
                articulo: value
            })
            this.modala=0
            this.modalp=1
        },
        editarLinea(linea){
            this.modalp=1
            this.productoSelect=linea
        },
        crearPedido(){
            let me = this;
            var userj=me.user
            this.pedido.user_id=userj.id
            this.pedido.lineas.forEach(l => {
                if(!l.hasOwnProperty('detalles')){
                    l.detalles=0;
                }
                if (l.orden) {
                    if (l.orden.medida_final) {
                        l.medida_final = l.orden.medida_final;
                    } else if (l.medida_final) {
                        l.orden.medida_final = l.medida_final;
                    }
                }
            });
            const data = new FormData()
            data.set('data',JSON.stringify(this.pedido))
            data.set('edit',this.edit)
            axios.post('pedido/registrar',data)
            .then(function (response) {
                if (response.data && response.data.calculations_summary) {
                    Swal.fire({
                        title: "Pedido Guardado",
                        html: response.data.calculations_summary,
                        icon: "success"
                    });
                } else {
                    Swal.fire("Pedido Guardado", "El pedido se ha registrado correctamente", "success");
                }
                me.$emit('ocultarDetalle', 1, me.pedido.estado)
                
                let day = String(new Date().getDate()).padStart(2, '0');
                let month = String(new Date().getMonth() + 1).padStart(2, '0');
                me.buscar_cliente = '';
                me.buscar_producto = '';
                me.arrayOrdenes = [];
                me.pedido = {
                    'fecha': `${new Date().getFullYear()}-${month}-${day}`,
                    'forma_pago': 'Contado',
                    'transportadora': '',
                    'estado': 2,
                    'cliente': { 'id': 0, 'razonsocial': '', 'contactos': [], 'empresas': [], 'envios': [] },
                    'lineas': [],
                    'abono': 0,
                    'saldo': 0,
                    'subtotal': 0,
                    'descuento': 0,
                    'iva': 0.19,
                    'total': 0
                };
                
            }).catch(function (error) {
                console.log(error);
            });
        },
        esColor(detalle) {
            if (!detalle) return false;
            if (Array.isArray(detalle.valor) && detalle.valor.length > 0 && typeof detalle.valor[0] === 'object' && detalle.valor[0].hex) return true;
            let t = (detalle.titulo || '').toLowerCase();
            if (t.includes('tinta') || t.includes('impresion') || t.includes('impresión') || t.includes('color')) return true;
            return false;
        },
        parseColorValores(valor) {
            if (Array.isArray(valor)) return valor;
            if (typeof valor === 'string' && valor.trim().startsWith('[')) {
                try { return JSON.parse(valor); } catch(e) { return []; }
            }
            return [];
        },
        mounted() {
            
        }
    }
}

</script>

<style> 
   
    .ordenes-cliente{
        display:flex;
    }
    .form-check{
        margin-left:20px;
        margin-right:20px;
        margin-bottom: 0;
    }
    .form-control{
        min-height: 30px;
        width:auto;
    }
    .dcliente{
        display:flex;
        flex-direction: row;
    } 
    .dcliente h4{
        flex:2
    }
    .dcliente .form-group{
        flex:4;
    }
    .mostrar{
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5) !important;
        z-index: 10000 !important;
        overflow-y: auto !important;
    }
    .mostrar .modal-dialog {
        margin-top: 10vh !important;
        margin-bottom: 10vh !important;
    }
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
    .detallesp{
        list-style: none;
        padding:0;
    }
    .detallesp li{
        display:flex;
    }
    .atributo{
        display:flex;
    }
    td div{
        display: flex;
        flex-direction: row;
        align-items: center;
    }
    td div input{
        margin-left:5px;
    }
    .adeliminar{
        position: absolute;
        z-index: 100;
        box-shadow: 2px 2px 15px rgba(0,0,0,0.5);
    }
    .adeliminar div{
        flex-direction: column;
    }
</style>

