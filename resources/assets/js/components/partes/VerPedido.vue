    

<template>
    <div class="contenedor" v-scroll="handleScroll">
      
        <div class="card-body" id="pedido">
            <div class="mb-3 d-print-none">
                <button type="button" class="btn btn-warning btn-sm mr-2" @click="imprimir()"><i class="icon-printer"></i> Imprimir Pedido</button>
                <button type="button" class="btn btn-primary btn-sm font-weight-bold text-white shadow-sm" @click="crearOVerProforma()"><i class="fa fa-file-text-o"></i> 📋 Crear / Ver Proforma</button>
            </div>
            <div class="row">
                <div class="col-sm-12 linea" ></div>
                <div class="col-sm-12 encabezado">
                    <figure><img src="img/LOGO-LUPA.jpg" alt=""> </figure>
                    <div class="datosempresa">
                        <ul>
                            <li>EMPAQUES LUPA Y/O AGENCIA LUPA SAS</li>
                            <li>Nit. 901086443-7</li>
                            <li>Carrera 1 # 23-60 Cali - Colombia - Valle del Cauca</li>
                            <li>Cel. 57 + 316 5288931</li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-12 datos">
                    <div class="row">
                        <div class="col-sm-6 ">
                            <div>
                                <label for="">Fecha </label>
                                <div v-text="pedido.fecha"></div>
                            </div>
                            <div>
                                <label for="">Nombre Cliente </label>
                                <div v-text="pedido.cliente.razonsocial"></div>
                            </div>
                            <div>
                                <label for="">Dirección / Sede </label>
                                <div v-text="pedido.cliente.direccion"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                             <div>
                                <label for="">Pedido </label>
                                <div v-text="pedido.id"></div>
                            </div>
                            <div>
                                <label for="">Nit </label>
                                <div ></div>
                            </div>
                            <div>
                                <label for="">Celular </label>
                                <div ></div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-sm-12 detalles">
                        
                    <div class="seccion-body" v-if="pedido.lineas.length">
                        <table class="table">
                            
                            <tr class="titulodetalles">

                            <th>Cantidad</th><th >Detalles</th><th>V. Unit. </th><th>Valor</th>
                            </tr>
                            
                            <tr v-for="(linea,index) in pedido.lineas" :key="index">
                             
                                
                                <td v-text="linea.cantidad"> 
                                  
                                </td>
                                <td>
                                    {{linea.articulo.nombre}}
                                   
                                        - <span v-for="(detalle,index) in linea.detalles" :key="index"  >
                                          
                                           
                                                    - {{ detalle.valor}} {{detalle.descripcion}}  &nbsp; 
                                              
                                           
                                        </span>
                                   
                                </td>
                            
                                <td class="valores"> 
                                    $  {{linea.valor_unitario}}
                                </td>
                                <td class="valores"> 
                                    $ {{linea.valor_total}}
                                </td>
                            </tr>
                          
                        </table>
                    </div>
                </div>
                <div class="col-sm-12">
                    Nota: Por favor tener en cuenta que el saldo final por pagar puede variar de + o -, por lo cual te descontaremos 
el faltante o pagaras despues de 10 unidades o mas el excedente
                </div>
                <div class="col-sm-12 totales">
                      <table class="float-right">
                                
                                <tr>
                                    <th>SUBTOTAL $</th>
                                    <td v-text="pedido.subtotal"></td>
                                </tr>
                                 <tr> 
                                    <th>IVA 19% $</th> 
                                    <td v-text="pedido.impuestos"></td>
                                </tr>
                                <tr>
                                    <th>TOTAL $</th>
                                    <td v-text="total"></td>
                                </tr>
                                <tr>
                                    <th >ABONO $</th>
                                    <td v-text="pedido.abono"></td>
                                </tr>
                                <tr>
                                    <th >
                                        SALDO 
                                    </th>
                                    <td v-text="pedido.saldo"></td>
                                </tr>
                            </table>
                            <div>
                                Valor Letras:
                            </div>
                </div>
               
              
            </div>
            
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
            this.pedido.saldo=valor-parseInt(this.pedido.abono)
            this.pedido.total=valor
            var resultado=valor
            return valor
        },
        
        
        },  
    methods:{
        imprimir(){
            var element = document.getElementById('pedido');
                        var opt = {
            margin:       0.15,
            filename:     this.pedido.cliente.razonsocial+this.pedido.id+'.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { scale: 10},
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().from(element).set(opt).save();;
        },
        imprimirProforma(){
            window.open('/imprimirProforma?id=' + this.pedido.id, '_blank');
        },
        crearOVerProforma(){
            let me = this;
            if (!me.pedido || !me.pedido.id) return;
            Swal.fire({
                title: 'Creando Proforma...',
                text: 'Generando proforma oficial para el pedido #' + (me.pedido.num_comprobante || me.pedido.id),
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            axios.post('/comprobante/crearProformaDesdePedido', { pedido_id: me.pedido.id })
                .then(function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Proforma Creada!',
                        text: 'Proforma #' + response.data.num_comprobante + ' generada correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.open(response.data.pdf_url, '_blank');
                })
                .catch(function(error) {
                    Swal.fire('Error', 'No se pudo crear la proforma.', 'error');
                });
        },
        totales(){
            var valor=0;
            var descuento=0;
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
            if(me.pedido.cliente.id){
                var url= '/cliente/selectOrdenesCliente?cliente_id='+me.pedido.cliente.id;
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
            this.pedido.cliente=value
            var empresa =this.pedido.cliente.empresas.filter((e)=>e.favorito==1)
            var contacto =this.pedido.cliente.contactos.filter((e)=>e.favorito==1)
            var envio =this.pedido.cliente.envios.filter((e)=>e.favorito==1)
            this.pedido.cliente.empresas=empresa
            this.pedido.cliente.contactos=contacto
            this.pedido.cliente.envios=envio
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
                var lineaPedido={
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
                this.pedido.lineas.push(lineaPedido)
            }else{
                this.pedido.lineas.splice(index,1)
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
                if(me.pedido.lineas[index].id>0){
                    if(me.borrarorden==true || me.borrarorden==1){
                        me.cambios= ('Eliminado linea pedido No. '+me.pedido.id+', producto: '+me.pedido.lineas[index].articulo.nombre+', y se elimina orden No.: '+me.pedido.lineas[index].ordentrabajo_id)+me.cambios
                    }else{
                        me.cambios= ('Eliminado linea pedido No. '+me.pedido.id+', producto: '+me.pedido.lineas[index].articulo.nombre)+me.cambios

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
            value.medida_material=''
            value.tamano=0
            value.cabida=1
            value.plancha=1
            value.prioridad='normal'
            value.carpeta_cliente=30
            value.detalles_diseno=''
            value.observaciones=''
            this.pedido.lineas.push(value)
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
        crearPedido(){
            let me = this;
            var userj=me.user
            this.pedido.user_id=userj.id
            this.pedido.lineas.forEach(l => {
                if(!l.hasOwnProperty('detalles')){
                    l.detalles=0;
                }else{
                }
                
            });
            
            const data = new FormData()
            data.set('data',JSON.stringify(this.pedido))
            data.set('edit',this.edit)
            axios.post('/pedido/registrar',data)
            .then(function (response) {
                console.log(response)
                if (response.data && response.data.calculations_summary) {
                    swal({
                        title: "Pedido Guardado",
                        text: response.data.calculations_summary,
                        type: "success",
                        html: true
                    });
                } else {
                    swal("Pedido Guardado", "El pedido se ha registrado correctamente", "success");
                }
                me.$emit('listarPedidos', '1,'+me.pedido.estado+',"estado"')
                me.pedido={'fecha':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'forma_pago':'Contado','transportadora':' ','estado':2,'cliente':{'contactos':[],'empresas':[],'envios':[]},'productos':[], 'abono':0,'saldo':0,'subtotal':0, 'descuento':0, 'impuesto':0, 'total':0}
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
    .linea{
        background: #33e034;
        height: 20px;
    }
    .encabezado{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        padding: 10px 30px;
    }
    .encabezado ul{
        list-style: none;
        align-content: center;
    }
    .datos .col-sm-6 div{
        display: flex;
        flex-direction: row;
    }
    .titulodetalles{
        background: #33e034;
        color:#000;
    }
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
    .detallesi{
        list-style: none;
        padding:0;
    }
    .detallesi li{
        display:flex;
        height: 20px;
        flex-direction: row;
        border-bottom: 0px;
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



