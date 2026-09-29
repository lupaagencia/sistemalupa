<template >
<div class="orden" >
  
  <div v-if="orden.status.estado==status" class="estadoPago rounded"
  :class="[orden.produccion=='EP'? 'enviarp' : [orden.produccion=='ENP' ? 'enproduccion' : [orden.produccion=='D'?'diseno': [orden.produccion=='EM'? 'empacado':'entregar']]]]"  >
 
  <div class="card-body"  @click="abrirmodal" >
        
          <ul>
            <li :style="{ background: getEntregaColor(orden.fecha_entrega) }">No. {{orden.id}}
              
              <div v-if="orden.pedido.transportadora!='' && orden.pedido.transportadora!='e'" class="btn" style="background:#fff;color:#00458d; border:3px solid #00458d;">{{ orden.pedido.transportadora }}</div>
              <div v-else-if="orden.pedido.transportadora=='e'" class="btn " style="background:#fff;color:#9300c3; border:3px solid #9300c3;">Entrega cliente</div>
              <div v-else class="btn" style="background:#fff;color:#168d00; border:3px solid #168d00;">Cali</div>
              <div v-if="orden.prioridad=='urgente'" class="badge badge-danger float-right">P</div>
              <div v-else-if="orden.prioridad=='media'" class="badge badge-warning float-right">P</div>
              <div v-else class="badge badge-primary float-right">P</div>
            </li>
            <li class="rasonsocial">{{orden.cliente.razonsocial}}</li>
            <li>{{orden.articulo.nombre}} 
                <div v-for="(plancha,index) in orden.planchas" :key="index">
                    <div v-if="plancha.id==orden.plancha">Plancha: {{ plancha.referencia}} {{ plancha.uso }}</div>
                </div>
            </li>
            <li><div v-for="(detalle,index) in orden.detalles" :key="index" v-if="detalle.titulo=='Papel'">{{ detalle.valor }}</div></li>
            <li>F. {{orden.fecha_entrega}}  </li>
            <li class="colores" v-for="(detalle,index) in orden.detalles" :key="index"   v-if="detalle.titulo=='Tinta' || detalle.titulo=='tinta'"> 
              <div v-for="(v,i) in detalle.valor" :key="i" v-if="Array.isArray(detalle.valor)" >
                <div class="color"
                :style="{ background: v.hex, width: '20px', height: '20px' }" 
              ></div> 
              </div> 
            </li>
          </ul>
         
          
            
      </div>
      
  </div>
  <div class="modal" tabindex="-1" role="dialog" :class="{'mostrar' : modal}" :style="{top:`${scroll}px`}">
      <div class="modal-dialog ordenescritorio" role="document">
          <div id="zoomDiv" class="modal-content" :style="{transform: `scale(${escala})`}">

            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarmodal()">
                
               <span  >&times;</span>
               <h4>
                <strong>Orden No. {{orden.id}} </strong>
              </h4> 
              </button> 
              <div class="contenedor-seccion flex-row-bet">
                <i class="fa fa-print btn btn-warning boton-principal" @click="generarExcelOrdenes(orden)">
                  <a v-if="archivo!=''" class="fa fa-download btn btn-dark  ml-2" :href="`/reportes/${archivo}`">  {{archivo}}</a>
                </i>

                <div class="btn btn-info boton-principal btn-zoom">
                  <i class="fa fa-sort"></i>
                  <div class="btn btn-dark " @click="ajustarTop(-100)">↑</div>
                  <div class="btn btn-dark " @click="ajustarTop(100)">↓</div>
                </div>
                <div class="btn btn-info boton-principal btn-zoom">
                  <i class="fa fa-search"></i>
                  <div class="btn btn-dark " @click="ajustarZoom(-0.02)">–</div>
                  <div class="btn btn-dark " @click="ajustarZoom(0.02)">+</div>
                </div>
              </div>
              
              
              
              <div class="modal-header">
                  <div v-for="(envio, i) in orden.cliente.envios" :key="i" >
                    
                      <button  class="btn btn-warning" id="guia" v-if="envio.favorito" @click="generarGuia(envio, orden.cliente.razonsocial)"> imprimir Guia </button>
                  </div>
                  <div class="datosorden">
                    <div> <strong> Plancha:</strong>

                      <select class="form-control" v-model="orden.plancha" @change="cambiarPlancha()" >
                          <option v-for="(plancha,index) in orden.planchas" :key="index" :value="plancha.id">{{plancha.referencia}} {{ plancha.uso }} {{ plancha.detalles }}</option>
                          <option value="0">No Existe</option>
                      </select>  
                    </div>
                    <div> <strong> Asignar:</strong>
                      <select v-model="tipoactivo" @change="abrirOpciones()" id="">
                        <option value="5">Troquelado</option>
                        <option value="6">Terminado</option>
                      </select>
                      <div v-if="arrayactivos.length>0">
                        <select v-model="activoasignado" id="" @change="asignarActivo(orden)">
                          <option v-for="(activo,i) in arrayactivos" :key="i" :value="activo">{{activo.activo}}</option>
                        </select>
                      </div>
                    </div>
                   
                    <div> <strong>Prioridad:</strong> {{orden.prioridad}} {{ orden.status.prioridad }} </div>
                    <div> <strong>Fecha entrega:</strong> {{orden.fecha_entrega}} </div>
                    <div> <strong> Cliente:</strong> {{orden.cliente.razonsocial}} </div>
                    <div> <strong>Articulo:</strong> {{orden.articulo.nombre}} </div>
                    <div> <strong>Cantidad:</strong> {{orden.cantidad}} </div>
                    <div class="row">
                      <div> <strong>Pliegos:</strong> {{cantidadPliegos.toFixed(0)}} </div>
                      <div v-for="(detalle,index) in orden.detalles" :key="index" v-if="detalle.titulo=='Papel'"><strong>Papel:</strong> {{ detalle.costo.costois.nombre }}</div>
                      <div> <strong>Medida tamaño:</strong> {{orden.medida_material}}   </div>
                      <div> <strong>Medida Final:</strong> {{orden.medida_final}}   </div>
                      <div> <strong>Cabida:</strong> {{orden.cabida}}   </div>
                      <div> <strong>Tamaños + Sobrante:</strong> {{cantidadTamanos.toFixed(0)}}   </div>
                    </div>
                   
                  </div>
                  <div class="detalles">
                    <h4>Detalles orden</h4>
                    <ul >
                      <li v-for="(detalle, ind) in orden.detalles" :key="ind">
                        <div>{{detalle.titulo}}</div>
                        <div v-if="Array.isArray(detalle.valor)">
                          <span v-for="(valor,index) in detalle.valor" :key="index" :style="'color:#fff; padding:2px 5px ; background:'+valor.hex" >{{valor.pantone}}</span>
                        </div>
                        <div v-else>
                          {{detalle.valor}}
                        </div>
                        <div>{{detalle.descripcion}}</div>
                      </li>
                      <li>{{orden.observaciones}}</li>
                    </ul>
                     <div v-if="editardetalles" class="contenedor-seccion">
                         
                          <div class="seccion-body">
                                  
                                  <ul v-if="orden.detalles.length">
                                      <li v-for="(detalle,index) in orden.detalles" :key="index">
                                          <div>
                                              <button @click="eliminarDetalle(index)" type="button" class="btn btn-danger btn-sm">
                                                  <i class="icon-close"></i>
                                              </button>
                                          </div>
                                          <div>
                                              <input type="text" v-model="detalle.titulo" class="form-control">
                                          </div>
                                          <div style="width:20%" v-if="Array.isArray(detalle.valor)">
                                              <div v-for="(valor,index) in detalle.valor" :key="index" type="text" class="form-control" :style="'color:#fff; background:'+valor.hex">{{valor.pantone}}</div>
                                          </div>
                                          <div v-else>
                                            <input type="text" v-model="detalle.valor" class="form-control" placeholder="Valor detalle">
                                          </div>
                                          <div>
                                              <input type="text" v-model="detalle.descripcion" class="form-control" placeholder="Descripción">
                                          </div>
                                          <div  v-if="detalle.titulo=='Papel'">
                                              <input  v-if="detalle.costo==null || detalle.costo.costois.nombre==''" type="text" :id="'modificar'+index" class="form-control" @keyup="selectInsumos('modificar'+index,detalle)" placeholder="Asigne Insumo, Maquina">
                                              <input type="text" v-else v-model="detalle.costo.costois.nombre" class="form-control">
                                          </div>
                                          <div v-else-if="detalle.titulo=='Tinta' || detalle.titulo=='tinta'" >
                                              <selectorcolor wi="140" ma="105" :detalle="detalle"></selectorcolor>
                                          </div>
                                      </li>
                                    
                                      <li>
                                        <textarea class="observaciones" v-model="orden.observaciones"></textarea>
                                      </li>
                                      <button @click="guardarOpciones(orden,'detalles')" type="button" class="btn btn-success" ><i class="fa fa-save"></i></button>
                                      <div class="col-md-4">
                                          <button class="btn btn-link" @click="cerrarEditarDetalles()">Cerrar</button>
                                      </div>
                                  </ul>  
                                                             
                          </div>
                      </div> 
                      <div v-else>
                          <button class="btn btn-link" @click="abrirEditarDetalles()">Editar detalles</button>
                      </div>
                    
                  </div>
              </div>
              <div class="model-body">
                 <div v-if="opcionproduccion" class="row contenedor-seccion detmateriales mt-3" style="width:150%; margin-left: -25%;">
                      
                        <div v-for="(detalle,index) in orden.detalles" :key="index" v-if="detalle.titulo=='Papel'" class="col-md-3" >
                          <div class="col-md-12 mt-3 mr-1">
                            <button @click="guardarMaterial(orden,detalle,'opciones')" type="button" class="btn btn-success" ><i class="fa fa-save"></i></button>
                        </div>  
                            <label for="">Material</label>
                              <input  v-if="detalle.costo!=null" type="text" v-model="detalle.costo.costois.nombre" class="form-control">
                              <small v-else >No se ha asignado </small>
                        </div>
                        <div class="col-md-2">
                            <label for="">Medida material</label>
                            <input type="text" class="form-control" v-model="orden.medida_material">
                        </div>
                        <div class="col-md-2">
                            <label for="">Medida Final</label>
                            <input type="text" class="form-control" v-model="orden.medida_final">
                        </div>
                        <div class="col-md-2">
                            <label for="">Tamaño</label>
                            <input type="text" class="form-control" v-model="orden.tamano">
                        </div>
                        <div class="col-md-2">
                            <label for="">Cabida</label>
                            <input type="text" class="form-control" v-model="orden.cabida">
                        </div>
                        <div class="col-md-2">
                            <label for="">Sobrante</label>
                            <input type="text" class="form-control" v-model="orden.carpeta_cliente">
                        </div>
                        <div class="col-md-3">
                            <label for="">Cantidad Tamaños</label>
                            <div type="text" class="form-control"> {{cantidadTamanos}}</div>
                        </div>
                        <div class="col-md-2">
                            <label for="">Pliegos</label>
                            <div type="text" class="form-control"> {{cantidadPliegos}}</div>
                        </div>
                       
                       
                        <div class="col-md-2">
                            <button class="btn btn-link" @click="cerrarOpcionePro()">Cerrar opciones de orden de produccion</button>
                        </div>
                    
                </div>
                <div v-else>
                    <button class="btn btn-link" @click="abrirOpcionePro()">Opciones de orden de produccion</button>
                </div>
              </div>
              
              <div class="modal-footer">
                
                

                    <div v-if="orden.status.estado==8" class="row actualizar">
                      
                    
                      <div  class="col-sm-8">
                          <label for="">Cantidad resultante</label>
                          <input type="text" class="form-control" v-model="orden.status.observaciones">
                      </div>
                      <div class="col-sm-4">
                       
                       
                        <button  @click="cambiarDatosEstado(orden)" class="float-right btn btn-primary boton-principal">Actualizar</button>
                      </div>
                    </div>
                 
                <div class="estado">
                  <button v-for="(proceso, index) in procesos" :key="index" class="btn" :class="{'btn-success': orden.status.estado==index}" @click="preguntarInventario(orden.status.id, orden.status.estado, index)">
                    {{proceso.proceso}}
                  </button>
                  <div v-if="mostrarModal" class="modal-overlay">
                    <div>
                      <h3>Confirmar Movimiento</h3>
                      <p>{{ mensajeModal }}</p>
                      <button @click="afectarInventario(mostrarModal.id,mostrarModal.estado)">Sí</button>
                      <button @click="cancelarMovimiento(mostrarModal.id,mostrarModal.estado)">No</button>
                    </div>
                  </div>
                 
                  
                </div>
              </div>
          </div>
      </div>
  </div>
  <div class="modal fade" tabindex="-1" :class="{'mostrar' : modali}" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" >
        <div class="modal-content" :style="'top:'+scroll+'px'">
            <div class="modal-header">
                <h5 class="modal-title">Seleccione costo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModali()">
                <span >&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <template v-if="arrayInsumos">
                    <div class="list-group">
                        <a href="#" 
                        class="list-group-item list-group-item-action" 
                        v-for="(insumo,index) in arrayInsumos" 
                        :key="index" 
                        @click="agregarCosto(insumo,index,objeto,seccion)">
                        {{insumo.nombre}} - <small>({{insumo.personas.nombre}})</small>
                        </a> 
                    </div>
                    
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" @click="cerrarModali()" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
    <!-- /.modal-dialog -->
</div>
</div>
</template>

<script>
import { forEach } from 'lodash';
import { Integer } from 'read-excel-file'
import selectorcolor from './SelectorColor.vue'
 var meses=['01','02','03','04','05','06','07','08','09','10','11','12']


  
export default {
  name: 'ordencard',
  props: 
        {orden:0,
        scroll:0,
        status:0,
        procesos:{type:Array},
        user:{}
    },
  data(){
    return{
      escala:1,
      modal:0,
      modali:0,
      mensajeModal:'',
      mostrarModal:false,
      accionPendiente: null,
      inventario:0,
      opcionproduccion:0,
      editardetalles:0,
      arrayInsumos:[],
      archivo:'',
      seccion:'costo',
      buscar_insumo:'',
      objeto:{},
      seccion:'',
      asignacion_detalle:{},
      tintas:'',
      tipoactivo:'',
      activoasignado:{},
      arrayactivos:[],
      fondo:'255,255,255',
      dias:0,
     
      
        }
  },
  components: {
            selectorcolor
        },
  computed:{
    

    
    cantidadTamanos(){

      var tamanos= this.orden.cantidad/parseFloat(this.orden.cabida)+parseFloat(this.orden.carpeta_cliente)
      this.orden.detalles.forEach(e => {
        if(e.titulo=='Papel'){
          if(e.costo!=null){
            e.costo.descripcion=tamanos
          }
        }
        
      });
      return tamanos;
    },
    cantidadPliegos(){
      var pliegos=(this.orden.cantidad/parseFloat(this.orden.cabida)+parseFloat(this.orden.carpeta_cliente))/parseFloat(this.orden.tamano)
      this.orden.detalles.forEach(e => {
        if(e.titulo=='Papel'){
          if(e.costo!=null){
            e.costo.cantidad=pliegos
          }
        }
        
      });
      return pliegos;
    }
  },
  methods: {
    getEntregaColor(fechaEntrega) {
        const hoy = new Date();
        const entrega = new Date(fechaEntrega);

        // Normalizar horas
        hoy.setHours(0, 0, 0, 0);
        entrega.setHours(0, 0, 0, 0);

        const diffMs = entrega - hoy;
        const diasRestantes = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        const diasMax = 12; // cantidad de días desde verde hasta rojo

        // Progreso de 0 (fecha vencida o hoy) a 1 (muy anticipado)
        const progreso = Math.max(0, Math.min(1, diasRestantes / diasMax));
        // Hue va de 0 (rojo) a 120 (verde), así que lo invertimos
        var hue = Math.round(170 * progreso); // 120 = verde, 0 = rojo
      
        if(hue>120){
          var d=hue-120
          hue=120-d/2
          
        }

        
        const color = `hsl(${hue}, 100%, 50%)`;

        return color;
      },


     ajustarTop(cambio) {
      let newTop = this.scroll + cambio;
      if (newTop < -50) newTop = 0.1;
      if (newTop > 1000) newTop = 3;
      this.scroll = newTop;
    },
    ajustarZoom(cambio) {
      let newScale = this.escala + cambio;
      if (newScale < 0.1) newScale = 0.1;
      if (newScale > 3) newScale = 3;
      this.escala = newScale;
    },
    preguntarInventario(id,estadoactual,estado) {
      if(estadoactual==1 && estado>1 || estadoactual==2 && estado==1){
        this.mostrarModal = {'id':id,
          'estado':estado
        };
        if (estado === 1 || estado === 0) {
          // Preguntar si se hará una entrada de material
          this.mensajeModal = "¿Desea sumar papel en el inventario?";
          this.accionPendiente = () => {
            this.inventario = 2; // Cambiar inventario a 2
          };
          
        } else if (estado > 1) {
          // Preguntar si se hará una salida de material
          this.mensajeModal = "¿Desea descontar papel en el inventario?";
          this.accionPendiente = () => {
            this.inventario = 1; // Cambiar inventario a 1
          };
        }
      }else{
        this.cerrarModal();
        this.cambiarEstado(id,estado)
      }
    },
    cambiarEstado(id,estado){
      var me=this
      var costois_id=0
      var cantidad=0
      var tipo=''
      if(me.inventario!=0){
        if(me.inventario==1){
          tipo='salida'
        }else{
          tipo='entrada'
        }
      }
      me.orden.detalles.forEach(e => {
        if(e.titulo=='Papel'){
          costois_id=e.costo.costois_id
          cantidad=e.costo.cantidad.toFixed(0)
        }
      });
      me.orden.status.estado=estado
      axios.put('/statuspro/cambiarEstado',{
          'id':id,
          'estado':estado,
          'costois_id':costois_id,
          'cantidad':cantidad,
          'tipo':tipo
      })
      .then(function (response) {
        console.log(response)
          me.modal=0    
          me.archivo=''  
        }).catch(function (error) {
          console.log(error);
      });
      
    },
    afectarInventario(id,estado) {
      // Ejecutar la acción pendiente y cerrar el modal
      if (this.accionPendiente) this.accionPendiente();
      this.cerrarModal();
      this.cambiarEstado(id,estado)
    },
    cancelarMovimiento(id,estado) {
      // Revertir el estado y cerrar el modal
      this.cerrarModal();
      this.cambiarEstado(id,estado)
    },
    cerrarModal() {
      this.mostrarModal = false;
      this.accionPendiente = null;
    },
    generarExcelOrdenes(orden){
      orden.idorden=orden.id
      orden.rasonsocial=orden.cliente.razonsocial
      var ordenes = new Array()
      ordenes.push(orden)
          let me=this;
          var url= 'orden/generarReporteOrdenes'
          axios.post(url,{
              'datos':JSON.stringify(ordenes),
          }).then(function (response) {
              let  respuesta = response.data;
              me.archivo=response.data
          })
          .catch(function (error) {
              console.log(error);
          });
      },
      generarGuia(datosenvio,cliente){
              let me=this;
              var url= '/generarGuia'
              if(datosenvio.empresa=='' || datosenvio.empresa==null){
                  datosenvio.empresa=cliente;
              }
              var datos=encodeURIComponent(JSON.stringify(datosenvio))
              axios({
              url: '/generarGuia?guia='+datos,         
              method: 'GET',
              responseType: 'blob', // important
              }).then((response) => {
                  const url = window.URL.createObjectURL(new Blob([response.data]));
                  const link = document.createElement('a');
                  link.href = url;
                  link.setAttribute('download', 'guia '+datosenvio.empresa+'.pdf');
                  document.body.appendChild(link);
                  link.click();
              });
          },
    
        
          agregarCosto(costo,index,detalle,seccion){
                let me=this
                var costoeliminar=''
                var ind=0;
                var costos_id=0;
                var tamanos=parseFloat(me.orden.cantidad/me.orden.cabida)+parseInt(me.orden.carpeta_cliente)
                var pliegos=tamanos/me.orden.tamano
                if(seccion.includes('modificar')){
                  if(detalle.costos_id==null || detalle.costos_id==0){
                    detalle.costos_id=costos_id
                  }
                  var cost={
                        titulo:costo.tipo_costo,
                        costois:costo,
                        costois_id:costo.id,
                        valor:costo.valor,
                        cantidad:pliegos,
                        orden:1,
                        descripcion:tamanos,
                        completado:0,
                        terminado:0,
                    }
                  detalle.costo=cost
                  detalle.titulo=costo.tipo_costo
                  detalle.costo.costois_id=costo.id

                  
                }else{
                    
                    var cost={
                        costos_id:0,
                        titulo:costo.tipo_costo,
                        costois:costo,
                        costois_id:costo.id,
                        valor:costo.valor,
                        cantidad:pliegos,
                        orden:1,
                        descripcion:tamanos,
                        completado:0,
                        terminado:0,
                    }
                    me.asignacion_detalle=cost
                }
                    
                me.modali=0
                
               
                
            },
     eliminarCosto(index,costo){
        let me=this
        var userj=me.user
        if(costo){
          me.orden.costos.splice(index,1)
          var url= '/costo/borrar?id='+costo.idcosto+'&user_id='+userj.id+'&_method=DELETE';
            axios.delete(url).then(function (response) {
                var respuesta= response.data;
            })
            .catch(function (error) {
                console.log(error);
            });
            
        }else{
            me.orden.costos.splice(index,1)
        }
    },
     modificarDetalle(insumo,detalle){
          detalle.titulo=insumo.tipo_costo
          detalle.costo_id=insumo.id
          detalle.costo=insumo

      },
    guardarMaterial(orden,detalle){
      var me=this
      const orden1 = new FormData()
      orden1.set('id',orden.id)
      orden1.set('medida_final',orden.medida_final)
      orden1.set('medida_material',orden.medida_material)
      orden1.set('tamano',orden.tamano)
      orden1.set('cabida',orden.cabida)
      orden1.set('carpeta_cliente',orden.carpeta_cliente)
      orden1.set('detalle',JSON.stringify(detalle))
      axios.post('/statuspro/guardarMaterial',orden1)
      .then(function (response) {
        console.log(response)
          me.opcionproduccion=0   
          me.editardetalles=0   
          me.modal=0
          me.archivo='' 
          me.buscar_insumo=''
          me.$emit('refrescar')  
        }).catch(function (error) {
          console.log(error);
      });
    },
    guardarOpciones(orden,seccion){
      var me=this
      const orden1 = new FormData()
      orden1.set('id',orden.id)
      orden1.set('medida_material',orden.medida_material)
      orden1.set('tamano',orden.tamano)
      orden1.set('cabida',orden.cabida)
      orden1.set('carpeta_cliente',orden.carpeta_cliente)
      orden1.set('observaciones',orden.observaciones)
      orden1.set('seccion',seccion)
      orden1.set('costo',JSON.stringify(this.orden.costos))
      orden1.set('detalles',JSON.stringify(this.orden.detalles))
      axios.post('/statuspro/guardarOpciones',orden1)
      .then(function (response) {
        console.log(response)
          me.opcionproduccion=0   
          me.editardetalles=0   
          me.modal=0
          me.archivo='' 
          me.buscar_insumo=''
          me.$emit('refrescar')  
        }).catch(function (error) {
          console.log(error);
      });
    },
    abrirOpcionePro(){
        this.opcionproduccion=1
    },
    abrirEditarDetalles(){
        this.editardetalles=1
    },
    cerrarOpcionePro(){
        this.opcionproduccion=0
    },
    cerrarEditarDetalles(){
        this.editardetalles=0
    },
     cerrarModali(){
          this.modali=0
          this.arrayInsumos=[]
      },
    abrirmodal(){
      this.modal=1
    },
    cerrarmodal(){
      this.modal=0
    },
    eliminarDetalle(index){
        let me=this
        var userj=me.user
          if(me.orden.detalles[index].id>0){
            me.cambios= ('Eliminado detalle Titulo: '+me.orden.detalles[index].titulo_detalle+' - Detalle: '+me.orden.detalles[index].valor+' - Descripcion: '+me.orden.detalles[index].descripcion+', ')+me.cambios
            var url= '/detalle/borrar?id='+ me.orden.detalles[index].id + '&user_id='+userj.id+'&_method=DELETE';
            axios.delete(url).then(function (response) {
                var respuesta= response.data;
                me.orden.detalles.splice(index,1)
            })
            .catch(function (error) {
                console.log(error);
            });
            
        }else{
            me.orden.detalles.splice(index,1)
        }
        
        
    },
    selectInsumos(seccion,objeto){
        let me=this;
        me.objeto=objeto
        var campo = document.getElementById(seccion);
        var url= '/costop/selectInsumos?filtro='+campo.value;
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
    asignarActivo(orden){
      var me=this
      const activo = new FormData()
      activo.set('ordenid',orden.id)
      activo.set('activo',JSON.stringify(me.activoasignado))
      axios.post('/statuspro/guardaractivo',activo)
      .then(function (response) {
          me.$emit('refrescar')  
          me.activoasignado={}
        }).catch(function (error) {
          console.log(error);
      });
    },
    abrirOpciones(){
      let me=this;
      var url= '/activo/portipo?tipo='+me.tipoactivo
      axios.get(url).then(function (response) {
        console.log(response)
          var respuesta= response.data
          me.arrayactivos = respuesta
         
      })
      .catch(function (error) {
          console.log(error);
      });
    },
    cambiarPlancha(){
      var me=this
      axios.put('/statuspro/cambiarPlancha',{
          'id':me.orden.plancha,
          'id_orden':me.orden.id,
      })
      .then(function (response) {
        me.$emit('refrescar')  
        }).catch(function (error) {
          console.log(error);
      });
      
    },
    
    cambiarDatosEstado(or){
        var me=this
        me.orden.status.estado=or.status.estado
        axios.put('/statuspro/cambiarDatosEstado',{
            'orden':or,
            'id':or.id,
            'observaciones':me.orden.observaciones,
        })
        .then(function (response) {
          me.modal=0
          me.archivo=''      
          }).catch(function (error) {
            console.log(error);
        });
        
      },
      verificarEnvio(){
        var me=this
        var idorden=this.orden.idorden
        axios.get('/statuspro/verificarEnvio',{
            'id':idorden,
        })
        .then(function (response) {
          me.orden.envio=response.data+'456'
                  
          }).catch(function (error) {
            console.log(error);
        });
        
      }
  },
  beforeMount() {
    this.getEntregaColor()
  }
    
    
}
</script>
<style lang="">
    .rasonsocial{
      text-transform: uppercase;
    } 
    .pago{
      background:rgb(238, 154, 84) !important;
      color: black;
    }
   
    .modal-footer h4{
      font-size: 16px;
    }
    
    .actualizar{
      margin-bottom: 7px;
    }
    .modal-header{
      display: flex;
      flex-direction: column;
      place-items: normal;
    }
    .ordencard ul{
      list-style: none;
      padding: 0;
      margin-bottom: 5px;
      font-size: 11px;
      font-weight: 700;
    }
    .ordencard .modal-footer{
      flex-wrap: wrap;
      justify-content: center;
    }
    .datosorden{
      display:flex;
      flex-direction: row;
      font-size: 14px;
      flex-wrap: wrap;
    }
    .datosorden div{
      padding: 3px 10px;
      border: solid 1px rgb(228, 228, 228)

    }
    .detalles h4{
      font-size: 16px;
      padding:5px;
      width:100%;
      background: #ccc;
      margin:5px 0 0 0  
    }
    .detalles{
       display:flex;
      flex-direction: column;
    }
    .detalles ul{
      display:flex;
      flex-direction: row;
      font-size:14px;
      justify-content: flex-start;
    }
    .detalles ul li{
      display:flex;
      flex-direction: row;
      border-bottom:1px solid #ccc;
      border-right: 1px solid #ccc;
    }
    .detalles  ul li div{
      padding:5px;
    }
    .card-body{
      padding:2px 5px;
    }
    .card .estadoPago{
        background: rgb(183, 228, 193);
        margin-bottom: 1px;
        
    }
    
    .modal-content{
        width: 100% !important;
    }
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
       
    }
    .fa-download{
      font-size: 12px;

    }
    .fa-download a{
      color:#fff;
      padding:0 10px;
    }
    .detalles .contenedor-seccion{
      padding:0;
    }
    .detalles .seccion-body{
      padding:0;
    }
    .observaciones{
      width:100%;
    }
    .estado button{
      width:auto;
    }
    .ordenescritorio{
      max-width:700px;
    }
    .colores{
      display:flex;
      flex-direction:row;
    }
    .detmateriales{
      display:flex;
      flex-direction:row !important;
    }
    .close{
      display:flex;
      padding: 5px 20px !important;
      justify-content:space-between;
      background: #fff;
    }
    .btn-zoom .btn-dark{
      padding:0px 10px;
      font-size:16px;
    }
    .btn-info{
      padding:2px !important;
      font-size:20px;
    }
    i{
      margin-right: 3px;
    }
 
    /* Animación para el modal */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
