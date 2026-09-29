    

<template>
    <div v-if="user.idrol=='Administrador'" class="contenedor" v-scroll="handleScroll">
        <div class="contenedor-header" :class="{'stick':fixed}">
             
                <div class="col-sm-9">
                    <h2 v-if="edit==0">Nuevo factura</h2>
                    <h2 v-else>Factura {{factura.num_comprobante}}</h2>
                </div>
                <div class="col-sm- mt-2 botones">
                    
                    <button type="button"  @click="cancelar()" class="btn btn-primary">
                        Cancelar
                    </button>
                    <button v-if="edit==0" type="button" @click="crearfactura()" class="btn btn-success">
                        Guardar
                    </button>
          
                    <button v-else type="button" @click="crearfactura()" class="btn btn-success">
                        Actualizar
                    </button>
                    
                </div>
            
        </div>
        <div class="card-body" id="factura">
            <div class="row">
                <div class="col-sm-3">
                    <label for="">Fecha</label>
                    <input type="date" class="form-control" v-model="factura.fecha">
                </div>
                <div class="col-sm-3">
                    <label for="">Forma de pago</label>
                    <input type="text" class="form-control" v-model="factura.forma_pago">
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
                           
                            <tr><th>Razon social</th><th>Documento</th><th>Telefono</th><th>Direccion empresa</th>
                            </tr>

                            <tr >
                                        <td v-text="factura.razonsocial.razonsocial"></td><td >{{factura.razonsocial.tipo_documento}}: {{factura.razonsocial.numero}} {{factura.razonsocial.digito}}</td>
                                        <td v-text="factura.razonsocial.telefono"></td><td >{{factura.razonsocial.direccion}}, {{factura.razonsocial.ciudad}}</td>
                            </tr>
                            
                           
                        </table>
                        
                    </div>
                </div>
                 <div class="contenedor-seccion ">
                   
                    <div class="seccion-body" v-if="factura.lineas.length">
                        <table class="table">
                            
                            <tr>

                            <th>Opciones orden</th><th>Cantidad</th><th>Nombre</th><th >Detalles</th><th>V. Unit. </th><th>Subtotal</th><th>Descuento</th><th>Total</th>
                            </tr>
                            
                            <tr v-for="(linea,index) in factura.lineas" :key="index">
                                 <td> <div v-if="opcionproduccion==index" class="row contenedor-seccion oproduccion">
                                         <detalleorden :orden="linea.orden" :user="user" @cerrarOpcionePro="cerrarOpcionePro"></detalleorden>
                                            <div >
                                                <button class="btn btn-link" @click="cerrarOpcionePro()">Cerrar opciones de orden de produccion</button>
                                            </div> 
                                    </div>
                                    <button class="btn btn-link" @click="abrirOpcionePro(linea,index)"><i class="fa fa-cog" aria-hidden="true"></i></button>
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
                                </td> 
                                
                                <td> 
                                    <div class="valores">
                                        <input type="text" class="form-control" @keyup="subTotalProducto()" v-model="linea.cantidad">
                                    </div>
                                </td>
                                <td>{{linea.articulo.nombre}}</td>
                                <td>
                                    <ul class="detallesp">
                                        <li v-for="(detalle,index) in linea.detalles" :key="index"  >
                                            <div><strong >{{detalle.titulo}}: &nbsp;</strong> </div>
                                            <div class="atributo">
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
                                    <div>

                                        $ <div type="text" class="form-control" v-text="linea.valor_total"></div>
                                    </div>
                                </td>
                            </tr>
                            <tfoot>
                                <tr>
                                    <td class="col-sm-3">
                                        <label for="">Abono</label>
                                        <input  type="text" class="form-control" v-model="factura.abono" @change="total">
                                        <input type="text" class="form-control" v-model="factura.abono" @change="total">
                                    </td>
                                    <td class="col-sm-3">
                                        <label for="">IVA</label>
                                        <input type="text" class="form-control" v-model="factura.iva" @change="total">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Descuento</strong> $ {{factura.descuento}}</td>
                                    <td colspan="2"><strong>Subtotal</strong> $ {{factura.subtotal}}</td>
                                    <td colspan="2"><strong>Iva</strong> $ {{factura.impuestos}}</td>
                                     <td colspan="2"><strong>Total</strong> $ {{total}}</td>
                                </tr>
                                 <tr>
                                    <th colspan="4"></th>
                                    <th colspan="2">
                                        Abono: $ {{factura.abono}}
                                    </th>
                                    <th colspan="2">
                                        Saldo: $ {{factura.saldo}}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-sm- mt-2 botones">
                        
                        <button type="button"  @click="cancelar()" class="btn btn-primary">
                            Cancelar
                        </button>
                        <button v-if="edit==0" type="button" @click="crearfactura()" class="btn btn-success">
                            Guardar
                        </button>
            
                        <button v-else type="button" @click="crearfactura()" class="btn btn-success">
                            Actualizar
                        </button>

                    </div>
                </div>
        
        </div>
    </div>
<!--Fin del modal-->
    <div v-else >
        <vistafactura :factura="factura" @cancelar="cancelar"></vistafactura>
    </div>
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
 import vistafactura from './VistaFactura'
 import html2pdf from "html2pdf.js"
 
 
 export default {
    props:['dato', 'edit','user','factura'],
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
        detalleorden,
        vistafactura
    },
    computed:{
        
       total(){
            var valor=0;
            var descuento=0;
            this.factura.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
                valor=valor+parseInt(e.valor_total)
                descuento=descuento+parseInt(e.descuento)
            })
            this.factura.subtotal=valor
            this.factura.descuento=descuento
            this.factura.impuestos=this.factura.subtotal*this.factura.iva
            var valor=parseFloat(this.factura.impuestos)+parseFloat(this.factura.subtotal)
            this.factura.saldo=valor-parseInt(this.factura.abono)
            this.factura.total=valor
            var resultado=valor
            return valor
        },
        
        
        },  
    methods:{
        imprimir(){
            var element = document.getElementById('factura');
            html2pdf(element);
        },
        totales(){
            var valor=0;
            var descuento=0;
            this.factura.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
                valor=valor+parseInt(e.subtotal)
                descuento=descuento+parseInt(e.descuento)
            })
            this.factura.subtotal=valor
            this.factura.descuento=descuento
            this.factura.impuestos=this.factura.subtotal*this.factura.iva
            var valor=parseFloat(this.factura.impuestos)+parseFloat(this.factura.subtotal)
            this.factura.saldo=valor-parseInt(this.factura.abono)
            this.factura.total=valor
            var resultado=valor
            return valor
        },
        subTotalProducto(){
            var valor=0;
            this.factura.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
            })
            
           
        },
         
        impuesto(){
            var valor=0;
            this.factura.impuestos=this.factura.subtotal*0.19
        },
        subTotal(){
            var valor=0;
            this.factura.lineas.forEach(e =>{
                valor=valor+parseInt(e.subtotal)
            })
            var resultado=valor
            this.factura.subtotal=valor
        },
        descuento(){
             var descuento=0;
            this.factura.lineas.forEach(e =>{
                descuento=descuento+parseInt(e.descuento)
            })
            var resultado=descuento
            this.factura.descuento=descuento
        },
        abrirOpcionePro(producto,index){
            this.opcionproduccion=index
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
            this.$emit('ocultarDetalle', 1)
        },
      
        selectOrden(){
            let me=this;
            if(me.factura.cliente.id){
                var url= '/cliente/selectOrdenesCliente?cliente_id='+me.factura.cliente.id;
                axios.get(url).then(function (response) {
                let respuesta = response.data;
                // fechas=array()
                // me.arrayOrdenes=Object.keys(respuesta)
                // fechas.array.forEach(e => {
                //     me.arrayOrdenes[e]=Object.values(respuesta)
                    
                // });
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
                
                me.arrayClientes=respuesta;
                me.modalc=1
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
            this.factura.cliente=value
            var empresa =this.factura.cliente.empresas.filter((e)=>e.favorito==1)
            var contacto =this.factura.cliente.contactos.filter((e)=>e.favorito==1)
            var envio =this.factura.cliente.envios.filter((e)=>e.favorito==1)
            this.factura.cliente.empresas=empresa
            this.factura.cliente.contactos=contacto
            this.factura.cliente.envios=envio
            this.modalc=0
            this.newCliente=0
        },
        agregarOrden(value,index,event){
            console.log(event.target.checked)
            if(event.target.checked){
                var impuesto=0
                if(value.impuesto=19){
                    var impuesto=0.19;
                }
                var lineafactura={
                    'id':0,
                    'ordentrabajo_id':value.id,
                    'articulo_id':value.articulo_id,
                    'articulo':value.articulo,
                    'cantidad':value.cantidad,
                    'descuento':value.descuento,
                    'valor_unitario':value.valor_unitario,
                    'orden':value,
                    'valor_total':value.total,
                    'subtotal':value.totalParcial,
                    'carpeta_cliente':value.carpeta_cliente,
                    'detalles_diseno':value.detalles_diseno,
                    'cabida':value.cabida,
                    'observaciones':value.observaciones,
                    'abono':value.abono,
                    'saldo':value.saldo,
                    'detalles':value.detalles,
                }
                this.factura.lineas.push(lineafactura)
            }else{
                this.factura.lineas.splice(index,1)
            }
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
                if(me.factura.lineas[index].id>0){
                    if(me.borrarorden==true || me.borrarorden==1){
                        me.cambios= ('Eliminado linea factura No. '+me.factura.id+', producto: '+me.factura.lineas[index].articulo.nombre+', y se elimina orden No.: '+me.factura.lineas[index].ordentrabajo_id)+me.cambios
                    }else{
                        me.cambios= ('Eliminado linea factura No. '+me.factura.id+', producto: '+me.factura.lineas[index].articulo.nombre)+me.cambios

                    }
                var url= '/factura/eliminarLinea?id='+ me.factura.lineas[index].id+ '&idorden='+me.factura.lineas[index].ordentrabajo_id+'&borrarorden='+me.borrarorden+'&user_id='+userj.id+'&cambios='+me.cambios+'&_method=DELETE';
                axios.delete(url).then(function (response) {
                    var respuesta= response.data;
                    me.noeliminar()
                    me.factura.lineas.splice(index,1)
                })
                .catch(function (error) {
                    console.log(error);
                });
                
            }else{
                me.factura.lineas.splice(index,1)
            }

        },
        
        productoCustom(value){
            value.medida_material=''
            value.tamano=0
            value.cabida=1
            value.plancha=1
            value.prioridad='normal'
            value.carpeta_cliente=30
            value.detalles_diseno=''
            value.observaciones=''
            this.factura.lineas.push(value)
            // this.subTotal()
            // this.descuento()
            // this.impuesto()
            // this.total()
            this.modalp=0
        },
        cerrarCustomProducto(value){
            this.modalp=value
        },
        productoSeleccionado(value){
            this.productoSelect=value
            this.productoSelect.produccion='ENP'
            this.productoSelect.medida_material=1
            this.productoSelect.tamano=1
            this.productoSelect.cabida=1
            this.productoSelect.plancha=1
            this.productoSelect.prioridad='normal'
            this.productoSelect.carpeta_cliente=1
            this.productoSelect.detalles_diseno=''
            this.productoSelect.observaciones=''
            this.productoSelect.cantidad=1000
            this.modala=0
            this.modalp=1
        },
        editarLinea(linea){
            this.modalp=1
            this.productoSelect=linea
        },
        crearfactura(){
            let me = this;
            var userj=me.user
            this.factura.user_id=userj.id
            this.factura.lineas.forEach(l => {
                if(!l.hasOwnProperty('detalles')){
                    l.detalles=0;
                }else{
                }
                
            });
            
            const data = new FormData()
            data.set('data',JSON.stringify(this.factura))
            data.set('edit',this.edit)
            axios.post('/factura/registrar',data)
            .then(function (response) {
                console.log(response)
                me.$emit('listarfacturas', '1,'+this.factura.estado+',"estado",50')
                me.factura={'fecha':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'forma_pago':'Contado','transportadora':' ','estado':2,'cliente':{'contactos':[],'empresas':[],'envios':[]},'productos':[], 'abono':0,'saldo':0,'subtotal':0, 'descuento':0, 'impuesto':0, 'total':0}
            }).catch(function (error) {
                console.log(error);
            });
            me.$emit('ocultarDetalle', 1)
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
    .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
        position: absolute !important;
        background-color: #3c29297a !important;
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

