<template>
           
    <div class="detallesorden" id="contestatuspro" v-scroll="handleScroll">
        <div class="col-md-12 d-flex justify-content-between align-items-center mb-2">
            <h4 class="text-success m-0"><strong>No. Orden {{orden.id}} - {{articulo ? articulo.nombre : (orden.articulo ? orden.articulo.nombre : '')}}</strong></h4>
            <button type="button" @click="imprimirHojaRuta(orden)" class="btn btn-sm text-white shadow-sm" style="background-color: #5c2c74; font-weight: bold; padding: 6px 14px; border-radius: 4px; height: 32px;" title="Imprimir Hoja de Ruta de Producción">
                <i class="fa fa-print"></i> Hoja de Ruta (PDF)
            </button>
        </div>
        
        <div class="estadoOrden">
                <h3 class="btn btn-primary">Estado produccón</h3>
                <div class="form-check btn" :class="[orden.produccion=='D' ? 'btn-danger': 'btn-secondary']">
                    <input type="radio" name="produccion" id="D"  v-model="orden.produccion" value="D">
                    <label class="form-check-label" for="D"> Diseño</label>                               
                </div>
                <div class="form-check btn" :class="[orden.produccion=='A' ? 'btn-warning': 'btn-secondary']">
                    <input type="radio" name="produccion" id="A"  v-model="orden.produccion" value="A">
                    <label class="form-check-label" for="A"> Aprobación</label>                                   
                </div>
                <div class="form-check btn" :class="[orden.produccion=='EP' ? 'btn-danger': 'btn-secondary']">
                    <input type="radio" name="produccion" id="EP"  v-model="orden.produccion" value="EP">
                    <label class="form-check-label" for="EP"> Enviar a producción</label>                                            
                </div>
                <div class="form-check btn" :class="[orden.produccion=='ENP' ? 'btn-success': 'btn-secondary']">
                    <input type="radio" name="produccion" id="ENP"  v-model="orden.produccion" value="ENP">
                    <label class="form-check-label" for="ENP"> En producción</label>                                      
                </div>
                <div class="form-check btn" :class="[orden.produccion=='EM' ? 'btn-success': 'btn-secondary']">
                    <input type="radio" name="produccion" id="EM"  v-model="orden.produccion" value="EM">
                    <label class="form-check-label" for="EM"> Empacado</label>                                      
                </div>
                <div class="form-check btn" :class="[orden.produccion=='E' ? 'btn-success': 'btn-secondary']">
                    <input type="radio" name="produccion" id="E"  v-model="orden.produccion" value="E">
                    <label class="form-check-label" for="E"> Para entrega</label>                                                          
                </div>
                <div class="form-check btn" :class="[orden.produccion=='T' ? 'btn-primary': 'btn-secondary']">
                    <input type="radio" name="produccion" id="T"  v-model="orden.produccion" value="T">
                    <label class="form-check-label" for="T"> Terminada</label>                                                          
                </div>
            
        </div>
            
        <div class="contenedor-seccion datosgenerales">
                
            <div style="width: 100%; margin-bottom: 10px; margin-left: 0;" class="p-2 bg-light border rounded" v-if="cabidasArticulo && cabidasArticulo.length > 0">
                <label class="d-block font-weight-bold text-primary mb-1" style="font-size: 13px;">
                    <i class="fa fa-th mr-1"></i> Posibles Cabidas y Medidas de Material (desde el Artículo):
                    <span v-if="parseFloat(orden.cantidad) > 1000" class="badge badge-warning text-dark ml-2" style="font-size: 11px;">
                        <i class="fa fa-lightbulb-o"></i> Cantidad > 1.000: Sugerida cabida mayor
                    </span>
                </label>
                <div class="d-flex flex-wrap align-items-center">
                    <button type="button" 
                            v-for="(cOpt, cIdx) in cabidasArticulo" 
                            :key="cIdx"
                            class="btn btn-sm mr-2 mb-1"
                            :class="parseFloat(orden.cabida) === parseFloat(cOpt.cabida) ? 'btn-primary font-weight-bold' : 'btn-outline-secondary'"
                            @click="seleccionarCabidaMaterial(cOpt)">
                        <strong>Cabida {{ cOpt.cabida }}</strong> - Mat: {{ cOpt.medida_material }} (Corte: {{ cOpt.tamano }})
                    </button>
                </div>
            </div>

            <div >
                <label for="">Plancha</label>
                <select class="form-control" v-model="orden.plancha" @change=" calculopliegos(orden)" >
                    <option v-for="(plancha,index) in orden.planchas" :key="index" :value="plancha.id">{{plancha.referencia}} {{ plancha.uso }} {{plancha.detalles}}</option>
                    <option value="0">No Existe</option>
                </select>
            </div>
            <div >
                <label for="">Prioridad</label>
                <select class="form-control" v-model="orden.prioridad">
                    <option value="normal">Normal</option>
                    <option value="media">Media</option>
                    <option value="urgente">Urgente</option>
                    <option value="vip">⭐ VIP (Máxima Prioridad)</option>
                </select>
            </div>
      
                
            <div >
                <label for="">Medida material</label>
                <input type="text" class="form-control" v-model="orden.medida_material">
            </div>
                
            <div >
                <label for="">Medida Final</label>
                <input type="text" class="form-control" v-model="orden.medida_final">
            </div>
            <div >
                <label for="">Tamaño</label>
                <input type="text" class="form-control" @change="calculopliegos()" v-model="orden.tamano">
            </div>
            <div >
                <label for="">Cabida</label>
                <input type="text" class="form-control" @change="recalcularSobranteECabida()" @keyup="recalcularSobranteECabida()" v-model="orden.cabida">
            </div>
        
            <div >
                <label for="">Sobrante</label>
                <input type="text" class="form-control" @change="calculopliegos()" v-model="orden.carpeta_cliente">
            </div>
           
            <div >
                <label for="">Detalles de diseño</label>
                <textarea style="color:red" class="form-control" v-model="orden.detalles_diseno">  </textarea>
            </div>
            <div >
                <label for="">Observaciones</label>
                <textarea style="color:red" class="form-control" v-model="orden.observaciones">  </textarea>
            </div>
        </div> 
        <div class="contenedor-seccion detalles">
            <div class="contenedor-header">
                <div class="row">

                    <div class="col-md-12 mt-2">
                        <h4>Detalles del trabajo</h4>
                    </div>
                
                    
                    <div class="col-lg-4" v-if="titulo_detalle=='Papel'">
                        <label >Asigne Insumo, Maquina </label>
                        <div class="form-group ">
                                <div class="form-inline">
                                    <input type="text" id="detalle" class="form-control" @keyup="selectInsumos('detalle','')" placeholder="Asigne Insumo, Maquina">
                                </div>  
                            
                        </div>
                    </div>
                    <div class="col-lg-4" >
                        <div class="form-group">
                            <label>Tipo de detalle </label>
                            <select type="text" class="form-control" v-model="titulo_detalle" @change="cambiarTipoDetalle" placeholder="Cual es el detalle">
                                <option v-for="(titulo,index) in titulosDetalle" :key="index" :value="titulo.detalle">{{ titulo.detalle }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4" >
                    
                        <div class="form-group">
                            <label>Detalle <span style="color:red" v-show="valor_detalle==0"> (*Ingrese)</span></label>
                            <div v-show="esColor(titulo_detalle)">
                                <selector-color :detalle="detalle_temp" wi="100"></selector-color>
                                <div class="form-control-static border rounded p-1 bg-white mt-1" style="min-height: 30px;">
                                    <span v-for="(v, i) in detalle_temp.valor" :key="i" class="badge mr-1" :style="'color:#fff; background:'+v.hex">{{v.pantone}}</span>
                                </div>
                            </div>
                            <input v-show="!esColor(titulo_detalle)" type="text" class="form-control" v-model="valor_detalle" placeholder="Cual es el detalle">
                        </div>
                    </div>
                        <div class="col-lg-4">
                        <div class="form-group">
                            <label>Asignación Detalle</label>
                            <div type="text"  class="form-control" v-text="asignacion_detalle.nombre_insumo" style="min-height:25px"></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Descripción<span style="color:red" v-show="descripcion_detalle==0">(*Ingrese)</span></label>
                            <input type="text"  class="form-control" v-model="descripcion_detalle" placeholder="Descripción o datos adicionales">
                        </div>
                    </div>
                    
                    
                    <div class="col-lg-2">
                        <div class="form-group">
                            <button @click="agregarDetalle()" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></button>
                        </div>
                    </div>
                    
                </div>

            </div>
            <div class="seccion-body">
                <table class="table table-bordered table-striped table-sm" style="border-collapse: collapse; border: 1px solid #ccc; width: 100%;">
                    <thead style="background-color: #d2e0e6; color: #2e3e4e; font-weight: bold; border-bottom: 2px solid #b2c0c6;">
                        <tr>
                            <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; text-align: left;">Opciones</th>
                            <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Titulo Detalle</th>
                            <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Detalle</th>
                            <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Descripcion</th>
                            <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Insumo o Maquina</th>
                        </tr>
                    </thead>
                    <tbody v-if="orden && orden.detalles && orden.detalles.length">
                        <tr v-for="(detalle,index) in orden.detalles" :key="index" style="border-bottom: 1px solid #c8d4db;">
                            <td style="background-color: #dce1e5; text-align: center; vertical-align: middle; padding: 6px 10px; width: 16%; border: 1px solid #c8d4db;">
                                <div style="display: flex; gap: 4px; align-items: center; justify-content: center;">
                                    <button @click="moverArriba(index)" :disabled="index === 0" type="button" 
                                            class="btn btn-sm btn-secondary"
                                            style="padding: 4px 8px; font-size: 13px; font-weight: bold; cursor: pointer;"
                                            title="Mover arriba">
                                        ▲
                                    </button>
                                    <button @click="moverAbajo(index)" :disabled="index === (orden.detalles.length - 1)" type="button" 
                                            class="btn btn-sm btn-secondary"
                                            style="padding: 4px 8px; font-size: 13px; font-weight: bold; cursor: pointer;"
                                            title="Mover abajo">
                                        ▼
                                    </button>
                                    <button @click="eliminarDetalle(index)" type="button" title="Eliminar"
                                            style="background-color: #ff6861; color: white; border: none; border-radius: 4px; padding: 4px 10px; height: 31px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; cursor: pointer; transition: background-color 0.2s;"
                                            onmouseover="this.style.backgroundColor='#e55750'"
                                            onmouseout="this.style.backgroundColor='#ff6861'">
                                        ⊗
                                    </button>
                                </div>
                            </td>
                            <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 18%; border: 1px solid #c8d4db;">
                                <input type="text" v-model="detalle.titulo" class="form-control" 
                                       style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                            </td>
                            <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 22%; border: 1px solid #c8d4db;">
                                <div style="width:100%" v-if="esColor(detalle.titulo, detalle.valor)">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <selector-color :detalle="detalle" wi="100"></selector-color>
                                        <div v-if="getColorsArray(detalle.valor).length > 0" style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                            <div v-for="(val, idx) in getColorsArray(detalle.valor)" :key="idx" 
                                                 class="text-white text-center d-flex align-items-center justify-content-center" 
                                                 :style="'background:' + val.hex + '; padding: 8px 10px; border-radius: 4px; font-size: 12px; min-height: 52px; min-width: 75px; max-width: 95px; line-height: 1.2; font-family: inherit; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.15); word-break: break-word;'">
                                                {{ val.pantone }}
                                            </div>
                                        </div>
                                        <div v-else-if="detalle.valor && typeof detalle.valor === 'object'" 
                                             class="text-white text-center d-flex align-items-center justify-content-center" 
                                             :style="'background:' + (detalle.valor.hex || '#000') + '; padding: 8px 10px; border-radius: 4px; font-size: 12px; min-height: 52px; min-width: 75px; max-width: 95px; line-height: 1.2; font-family: inherit; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.15); word-break: break-word;'">
                                            {{ detalle.valor.pantone || detalle.valor }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <input type="text" v-model="detalle.valor" class="form-control" placeholder="Valor detalle" 
                                           style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                                </div>
                            </td>
                            <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 22%; border: 1px solid #c8d4db;">
                                <input type="text" v-model="detalle.descripcion" class="form-control" 
                                       style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%; margin-bottom: 4px;">
                                <div v-if="detalle.titulo && detalle.titulo.toLowerCase() === 'papel' && detalle.costo" 
                                     style="background-color: #ffffff; border: 1px solid #b8c4cc; border-radius: 4px; padding: 8px 12px; font-size: 13px; color: #333; line-height: 1.4; margin-top: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-family: inherit; text-align: left;">
                                    <div>Tamaños: {{ detalle.costo.descripcion }}</div>
                                    <div>Pliegos: {{ Math.round(detalle.costo.cantidad) }}</div>
                                </div>
                            </td>
                            <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 23%; border: 1px solid #c8d4db;">
                                <input v-if="!detalle.costo || !detalle.costo.costois || !detalle.costo.costois.nombre || detalle.costo.costois.nombre===''" 
                                       type="text" 
                                       :id="'modificar'+index" 
                                       class="form-control" 
                                       :value="(detalle.costo && detalle.costo.descripcion) ? detalle.costo.descripcion : ''"
                                       @keyup="selectInsumos('modificar'+index,detalle)" 
                                       placeholder="Asigne Insumo, Maquina" 
                                       style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                                <input v-else 
                                       type="text" 
                                       v-model="detalle.costo.costois.nombre" 
                                       :id="'modificar'+index"
                                       @keyup="selectInsumos('modificar'+index,detalle)" 
                                       class="form-control" 
                                       style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                            </td>
                        </tr>
                    </tbody>             
                    <tbody v-else>
                        <tr>
                            <td colspan="5"> 
                                No hay detalles agregados
                            </td>
                        </tr>  
                    </tbody>                               
                </table>
            </div>

        </div> 
        <div >
            <button class="btn btn-link" @click="cerrarOpcionePro()">Cerrar opciones de orden de produccion</button>
        </div>         
        
        <div  v-show="errorOrden" class="form-grou col-md-12 div-error">
            <div class="text-center text-error">
                <div v-for="(error,index) in errorMostrarMsjOrden" :key="index" v-text="error">

                </div>
            </div>
        </div>
        
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modali}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seleccione costo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModali()">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 50vh; overflow-y: auto;">
                        <template v-if="arrayInsumos">
                            
                            <div class="list-group">
                                <a href="#" 
                                class="list-group-item list-group-item-action" 
                                v-for="(insumo,index) in arrayInsumos" 
                                :key="index" 
                                @click="agregarCosto(insumo,index,objeto,seccion)">
                                {{insumo.nombre}} - <small>({{insumo.proveedor ? insumo.proveedor.nombre : 'S/N'}})</small>
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
    import SelectorColor from './partes/SelectorColor.vue'
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    export default {
        props:['user','orden', 'articulo'],
       
        data (){
            return {
                orden1:{
                    prioridad:'normal',
                    pago:0,
                    estado:'C',
                    produccion:'D',
                    impresa:0,
                    carpeta_cliente:null,
                    detalles_diseno:null,
                    observaciones:null,
                    medida_material:0,
                    tamano:0,
                    valor:0,
                    impuesto: 0,
                    valor_impuesto:0,
                    descuento:0,
                    cantidad:1000,
                    valor_unitario:0,
                    total:0,
                    abono:0,
                    saldo:0,
                    cabida:1,
                    cliente_id:0,
                    articulo_id:0,
                    cliente:{},
                    articulo:{},
                    costos:[],
                    detalles:[]
                },
                dominio:'',
                ordenIni:{},
                cambios:'',
                action:'',
                estadocomercial:{C:'Por Cotizar', PC:'Por Concretar', PA:'Pendiente Abono', VC:'Venta Cerrada', P:'No Comprar', A:'Aplazada'},
                estadoproduccion:{D:'Diseño', A:'Aprobación',EP:'Enviar a producción',ENP:'En Producción', EM:'Empacado', E:'Para Entrega',T:'Terminada'},
               
                arrayDetalle : [],
                titulo_detalle:'Tinta',
                valor_detalle:'',
                descripcion_detalle:'',
                arrayArticulos:[],
                asignacion_detalle:{},
                detalle_temp: { titulo: 'Tinta', valor: [] },
                tiposInsumo:[],
                titulosDetalle:[],
                idarticulo:0,
                idcosto:0,
                nombre_insumo:'',
                valor_insumo:0,
                cabida:'',
                insumo_seleccionado:null,
                iseleccionado:false,
                arrayInsumos:[],
                arrayCostos:[],
                descripcion_costo:'',
                valor_costo:0,
                cantidad_costo:1,
                tipo_costo:'',
                orden_costo:0,
                objeto:{},
                modali:0,
                errorIngreso : 0,
                errorMostrarMsjIngreso : [],
                buscar_insumo:'',
                seccion:'',
                errorMostrarMsjOrden:[],
                errorOrden:0,
                fixed:0,
                scroll:0,
                formatoPapelHojaRuta: 'letter',
                anchoPapelCm: '21.5',
                altoPapelCm: '33'
            }
        },
        components: {
            'selector-color': SelectorColor
        },
        mounted() {
            let me = this;
            axios.get('/orden/hoja-ruta-config').then(response => {
                if (response.data) {
                    if (response.data.formato_papel) me.formatoPapelHojaRuta = response.data.formato_papel;
                    if (response.data.ancho_papel_cm) me.anchoPapelCm = response.data.ancho_papel_cm;
                    if (response.data.alto_papel_cm) me.altoPapelCm = response.data.alto_papel_cm;
                }
            }).catch(e => {});
        },
        computed:{
            cabidasArticulo() {
                let art = this.articulo || (this.orden ? this.orden.articulo : null);
                if (!art && this.orden && this.orden.cabidas_materiales) {
                    art = { cabidas_materiales: this.orden.cabidas_materiales };
                }
                if (!art || !art.cabidas_materiales) return [];
                let list = typeof art.cabidas_materiales === 'string'
                    ? JSON.parse(art.cabidas_materiales)
                    : art.cabidas_materiales;
                return Array.isArray(list) ? list : [];
            }
        },
        methods : {
            calcularSobranteAuto(cantidadVal, cabidaVal) {
                const cant = parseFloat(cantidadVal) || (this.orden ? parseFloat(this.orden.cantidad) : 0) || 0;
                const cab = parseFloat(cabidaVal) || (this.orden ? parseFloat(this.orden.cabida) : 1) || 1;
                const cantidadMaterial = cab > 0 ? (cant / cab) : cant;

                let numTintas = 0;
                if (this.orden && Array.isArray(this.orden.detalles)) {
                    this.orden.detalles.forEach(d => {
                        let tit = (d.titulo || '').toLowerCase();
                        if (tit.includes('tinta') || tit.includes('impres') || tit.includes('color')) {
                            let val = d.valor;
                            if (Array.isArray(val)) {
                                numTintas += val.length;
                            } else if (typeof val === 'string' && val.trim().startsWith('[')) {
                                try {
                                    let parsed = JSON.parse(val);
                                    if (Array.isArray(parsed)) numTintas += parsed.length;
                                    else if (parsed) numTintas += 1;
                                } catch(e) {
                                    if (val.trim() && val !== '[]') numTintas += 1;
                                }
                            } else if (val && val !== '0' && val !== '[]') {
                                numTintas += 1;
                            }
                        }
                    });
                }
                if (numTintas < 1) numTintas = 1;

                const sobranteBase = 50 + (numTintas - 1) * 25;
                const sobranteTiraje = Math.round(cantidadMaterial * 0.01);
                return Math.max(50, Math.round(sobranteBase + sobranteTiraje));
            },
            seleccionarCabidaMaterial(cOpt) {
                if (!cOpt || !this.orden) return;
                this.$set(this.orden, 'cabida', cOpt.cabida);
                this.$set(this.orden, 'medida_material', cOpt.medida_material);
                let tam = this.calcularTamanoFromMedida(cOpt.medida_material);
                this.$set(this.orden, 'tamano', tam);

                const nuevoSobrante = this.calcularSobranteAuto(this.orden.cantidad, cOpt.cabida);
                this.$set(this.orden, 'carpeta_cliente', nuevoSobrante);

                this.calculopliegos();
            },
            recalcularSobranteECabida() {
                if (!this.orden) return;
                const autoSob = this.calcularSobranteAuto(this.orden.cantidad, this.orden.cabida);
                this.$set(this.orden, 'carpeta_cliente', autoSob);
                this.calculopliegos();
            },
            calcularTamanoFromMedida(medida) {
                if (!medida || !medida.includes('x')) return 1;
                let parts = medida.split('x').map(Number);
                if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return 1;
                let cutW = parts[0], cutH = parts[1];
                let pliegoW = 70, pliegoH = 100;
                let solve = (W, H, cW, cH) => {
                    let count1 = Math.floor(W / cW);
                    let count2 = Math.floor(W / cH);
                    let best = 0;
                    for (let n1 = 0; n1 * cH <= H; n1++) {
                        let n2 = Math.floor((H - n1 * cH) / cW);
                        best = Math.max(best, (n1 * count1) + (n2 * count2));
                    }
                    return best;
                };
                let c1 = solve(pliegoW, pliegoH, cutW, cutH);
                let c2 = solve(pliegoH, pliegoW, cutW, cutH);
                let res = Math.max(c1, c2);
                return res > 0 ? res : 1;
            },
            imprimirHojaRuta(orden) {
                if (window.abrirModalProcesosHojaRuta) {
                    window.abrirModalProcesosHojaRuta(orden);
                } else {
                    let id = (orden && (orden.idorden || orden.id)) ? (orden.idorden || orden.id) : orden;
                    if (id) window.open('/orden/hoja-ruta/' + id, '_blank');
                }
            },
            guardarConfigPapel() {
                let me = this;
                axios.post('/orden/hoja-ruta-config', {
                    formato_papel: me.formatoPapelHojaRuta,
                    orientacion_papel: 'portrait',
                    ancho_papel_cm: me.anchoPapelCm,
                    alto_papel_cm: me.altoPapelCm
                }).then(response => {
                    // Saved cleanly
                }).catch(e => {});
            },
            
            handleScroll: function (evt, el) {
                const myElement = document.getElementById('contestatuspro');
                if (!myElement) return;
                const rect = myElement.getBoundingClientRect();
                const vtop = rect.top + window.scrollY;
                if (window.scrollY < vtop) {
                    this.scroll=window.scrollY-300
                }else{
                    this.scroll=window.scrollY-400
                }
            },
            calculopliegos(or){
                let me=this;
                const targetOrden = me.orden;
                if (!targetOrden || !targetOrden.detalles) return;

                const articulo = me.articulo || (targetOrden.articulo || null);

                // Auto calculate sobrante based on material quantity and ink count
                const autoSob = me.calcularSobranteAuto(targetOrden.cantidad, targetOrden.cabida);
                if (!targetOrden.carpeta_cliente || parseFloat(targetOrden.carpeta_cliente) <= 50 || parseFloat(targetOrden.carpeta_cliente) === 0) {
                    me.$set(targetOrden, 'carpeta_cliente', autoSob);
                }

                // If 'or' is passed (meaning the plancha dropdown changed), we update cabida and medida_material from plancha
                if (or && targetOrden.planchas) {
                    const plancha = targetOrden.planchas.find(item => item.id === targetOrden.plancha);
                    let numero = 0;
                    if (plancha) {
                        let match = null;
                        if (plancha.detalles) {
                            match = plancha.detalles.match(/cabida\s*[:\-]?\s*([\d.]+)/i) || plancha.detalles.match(/[\d.]+/);
                        }
                        if (!match && plancha.referencia) {
                            match = plancha.referencia.match(/cabida\s*[:\-]?\s*([\d.]+)/i) || plancha.referencia.match(/[\d.]+/);
                        }
                        numero = match ? parseFloat(match[1] || match[0]) : 0;
                    }
                    
                    if (numero > 0) {
                        targetOrden.cabida = numero;

                        // Lookup matching material measure
                        let cabidas = [];
                        if (articulo && articulo.cabidas_materiales) {
                            cabidas = typeof articulo.cabidas_materiales === 'string'
                                ? JSON.parse(articulo.cabidas_materiales)
                                : articulo.cabidas_materiales;
                        }
                        if (Array.isArray(cabidas) && cabidas.length > 0) {
                            if (parseFloat(targetOrden.cantidad) > 1000) {
                                const maxCm = cabidas.reduce((max, c) => (parseFloat(c.cabida) > parseFloat(max.cabida) ? c : max), cabidas[0]);
                                targetOrden.cabida = parseFloat(maxCm.cabida) || targetOrden.cabida;
                                if (maxCm.medida_material) {
                                    targetOrden.medida_material = maxCm.medida_material;
                                }
                            } else {
                                const matchCm = cabidas.find(cm => parseFloat(cm.cabida) === numero);
                                if (matchCm && matchCm.medida_material) {
                                    targetOrden.medida_material = matchCm.medida_material;
                                }
                            }
                        }
                    }
                }

                // Sync final size from product if available
                if (articulo && articulo.medida_final) {
                    targetOrden.medida_final = articulo.medida_final;
                }
                
                if (targetOrden.detalles) {
                    targetOrden.detalles.forEach(o => {
                        if(o.titulo=='Papel' && o.costo){
                            let cabida = parseFloat(targetOrden.cabida) || 1;
                            let cantidad = parseFloat(targetOrden.cantidad) || 0;
                            let carpeta_cliente = parseFloat(targetOrden.carpeta_cliente) || 0;
                            let tamano = parseFloat(targetOrden.tamano) || 1;
                            
                            let descripcionVal = cantidad / cabida + carpeta_cliente;
                            let cantidadVal = descripcionVal / tamano;
                            
                            me.$set(o.costo, 'descripcion', descripcionVal);
                            me.$set(o.costo, 'cantidad', cantidadVal);
                        }
                    });
                }
                me.$forceUpdate();
            },
            getTitulosDetalle(){
                let me=this;
              
              var url= me.dominio+'/ajustes/titulosDetalle';
              axios.get(url).then(function (response) {
                console.log(response)
                  let respuesta = response.data;
                  me.titulosDetalle=respuesta;
              })
              .catch(function (error) {
                  console.log(error);
              });
            },
            getTiposInsumo(){
                 let me=this;
              
                var url= me.dominio+'/costop/agruparTipos';
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.tiposInsumo=respuesta;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectInsumos(seccion,objeto){
                let me=this;
                me.objeto=objeto;
                var campo = document.getElementById(seccion);
                let filtroVal = campo ? campo.value : '';
                var base = window.location.pathname.replace(/\/main.*/i, '').replace(/\/+$/, '');
                var url= base + '/costop/selectInsumos?filtro='+encodeURIComponent(filtroVal);
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayInsumos=respuesta.insumos || [];
                    me.modali=1;
                    me.seccion=seccion;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            agregarCosto(costo,index,detalle,seccion){
                let me=this
                var costoeliminar=''
                var ind=0;
                var costos_id=0;
                var tamanos=parseFloat(me.orden.cantidad/me.orden.cabida)+parseInt(me.orden.carpeta_cliente)
                var pliegos=tamanos/me.orden.tamano
                if(me.titulo_detalle == 'Papel'){
                    me.valor_detalle = costo.descripcion ? costo.descripcion : costo.nombre;
                }else{
                    me.valor_detalle = costo.nombre;
                }
                if(seccion.includes('modificar')){
                    if(detalle.costo){
                        detalle.costo.costois_id=costo.id
                        detalle.costo.costois=costo
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
                        this.$set(detalle, 'costo', cost)
                        this.$set(detalle, 'costos_id', 0)
                    }
                    detalle.valor = costo.nombre
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
            getColorsArray(valor){
                if(Array.isArray(valor)) return valor;
                if(typeof valor === 'string' && valor.trim().startsWith('[')) {
                    try {
                        let parsed = JSON.parse(valor);
                        if(Array.isArray(parsed)) return parsed;
                    } catch(e){}
                }
                return [];
            },
            esColor(titulo, valor){
                if(this.getColorsArray(valor).length > 0) return true;
                if(!titulo) return false;
                let t = String(titulo).toLowerCase();
                return t.includes('tinta') || t.includes('impresion') || t.includes('impresión') || t.includes('color');
            },
            cambiarTipoDetalle(){
                this.detalle_temp.titulo = this.titulo_detalle;
                this.detalle_temp.valor = [];
                this.valor_detalle = '';
            },
            modificarDetalle(costo,detalle){
                detalle.titulo=costo.titulo
                detalle.costo=costo
                detalle.costos_id=0

            },
            agregarDetalle(){
                let me=this
                if(me.esColor(me.titulo_detalle)){
                    if(!me.detalle_temp.valor || me.detalle_temp.valor.length == 0){
                        alert('Debe seleccionar al menos un color de la paleta');
                        return;
                    }
                    me.valor_detalle = [...me.detalle_temp.valor];
                }

                if(!me.valor_detalle || (typeof me.valor_detalle === 'string' && me.valor_detalle.trim() === '')){
                    alert('DEBUG_V1: El campo de DETALLE no debe estar vacio');
                    return;
                }
                if(true){
                    var costoid=0
                    var tipo=''
                    if(Object.keys(me.asignacion_detalle).length==0){
                        if(me.titulo_detalle=='Papel'){
                            alert('Si selecciono PAPEL debe asignar un insumo valido')
                            return
                        }else{
                            costoid=0
                            tipo=me.titulo_detalle
                        }
                        me.orden.detalles.push({
                            id:0,
                            titulo:me.titulo_detalle,
                            valor:me.valor_detalle,
                            descripcion:me.descripcion_detalle,
                            costos_id:0,
                            costo:0
                        })
                    }else{
                        costoid=me.asignacion_detalle.costois_id
                        tipo=me.asignacion_detalle.titulo
                        me.orden.detalles.push({
                            id:0,
                            titulo:tipo,
                            valor:me.valor_detalle,
                            descripcion:me.descripcion_detalle,
                            costos_id:0,
                            costo:me.asignacion_detalle
                        })
                    }
                    
                    me.titulo_detalle=''
                    me.valor_detalle=''
                    me.descripcion_detalle=''
                    me.asignacion_detalle={}
                    me.detalle_temp.valor = []
                    me.recalcularSobranteECabida()
                }
               
                
            },
            
           
            moverArriba(index) {
                if (index > 0 && this.orden && this.orden.detalles) {
                    const temp = this.orden.detalles[index];
                    this.$set(this.orden.detalles, index, this.orden.detalles[index - 1]);
                    this.$set(this.orden.detalles, index - 1, temp);
                    this.orden.detalles.forEach((det, idx) => {
                        det.orden = idx + 1;
                    });
                }
            },
            moverAbajo(index) {
                if (this.orden && this.orden.detalles && index < this.orden.detalles.length - 1) {
                    const temp = this.orden.detalles[index];
                    this.$set(this.orden.detalles, index, this.orden.detalles[index + 1]);
                    this.$set(this.orden.detalles, index + 1, temp);
                    this.orden.detalles.forEach((det, idx) => {
                        det.orden = idx + 1;
                    });
                }
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
                        me.recalcularSobranteECabida()
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                   
                }else{
                    me.orden.detalles.splice(index,1)
                    me.recalcularSobranteECabida()
                }
            },
            eliminarCosto(index){
                let me=this
                var userj=me.user
                if(me.orden.costos[index].idcosto>0){
                    me.cambios= ('Eliminado Costo: '+me.orden.costos[index].titulo+' - Nombre: '+me.orden.costos[index].nombre+' - Descripcion: '+me.orden.costos[index].descripcion+' - Cantidad: '+me.orden.costos[index].cantidad+ ', ')+me.cambios
                    var url= me.dominio+'/costo/borrar?id='+me.orden.costos[index].idcosto+'&user_id='+userj.id+'&_method=DELETE';
                    axios.delete(url).then(function (response) {
                        console.log(response)
                    var respuesta= response.data;
                    me.orden.costos.splice(index,1)
                })
                .catch(function (error) {
                    console.log(error);
                });
                   
                }else{
                    me.orden.costos.splice(index,1)
                }
            },
            registrarOrden(){
                let me = this;
                var userj=me.user
                 if (me.validarOrden()){
                    return;
                }
                const orden1 = new FormData()
                orden1.set('estado',this.orden.estado)
                orden1.set('user_id',userj.id)
                orden1.set('produccion',this.orden.produccion)
                orden1.set('cliente_id', this.orden.cliente_id)
                orden1.set('articulo_id' , this.orden.articulo_id)
                orden1.set('fecha_entrega' , this.orden.fecha_entrega)
                orden1.set('fecha' , this.orden.fecha)
                orden1.set('fecha_orden' , this.orden.fecha_orden)
                orden1.set('carpeta_cliente' , this.orden.carpeta_cliente)
                orden1.set('detalles_diseno' , this.orden.detalles_diseno)
                orden1.set('observaciones' , this.orden.observaciones)
                orden1.set('prioridad' , this.orden.prioridad)
                orden1.set('plancha' , this.orden.plancha)
                orden1.set('unidad' , this.orden.cabida)
                orden1.set('medida_final', this.orden.medida_final)
                orden1.set('tamano', this.orden.tamano)
                orden1.set('medida_material', this.orden.medida_material)
                orden1.set('cantidad', this.orden.cantidad)
                orden1.set('valor_unitario',this.orden.valor_unitario)
                orden1.set('totalParcial',this.orden.totalParcial)
                orden1.set('descuento',this.orden.descuento)
                orden1.set('impuesto',this.orden.impuesto)
                orden1.set('valor_impuesto',this.orden.valor_impuesto)
                orden1.set('total',this.orden.total)
                orden1.set('abono',this.orden.abono)
                orden1.set('saldo',this.orden.saldo)
                orden1.set('detalles',JSON.stringify(this.orden.detalles))
                orden1.set('costos',JSON.stringify(this.orden.costos))
                axios.post(me.dominio+'/orden/registrar',orden1)
                .then(function (response) {
                    console.log(response)
                    me.listado=1
                    me.cambios='';
                    me.orden={
                        fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                        fecha_orden:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                        fecha_entrega:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                        plancha:0,
                        prioridad:'normal',
                        pago:0,
                        estado:'C',
                        produccion:'D',
                        impresa:0,
                        carpeta_cliente:'',
                        detalles_diseno:'',
                        observaciones:'',
                        medida_material:0,
                        tamano:0,
                        valor:0,
                        impuesto: 0,
                        valor_impuesto:0,
                        descuento:0,
                        cantidad:1000,
                        valor_unitario:0,
                        total:0,
                        abono:0,
                        saldo:0,
                        cabida:1,
                        cliente_id:0,
                        articulo_id:0,
                        cliente:{},
                        articulo:{},
                        costos:[],
                        detalles:[],
                        dominio:''
                    }
                    me.listarOrdenes(1,me.per_page,me.buscar,'like',me.criterio);
                }).catch(function (error) {
                    console.log(error);
                });
            },
            comprobarCambios(){
                this.cambios= ((this.ordenIni.estado!=this.orden.estado) ? 'Estado comercial: '+this.orden.estado+', ' : '') +this.cambios
                this.cambios= ((this.ordenIni.produccion!=this.orden.produccion) ? 'Estado de produccion: '+this.orden.produccion+',' : '') +this.cambios
                this.cambios= ((this.ordenIni.cliente.id!=this.orden.cliente.id) ? 'Cliente: '+this.orden.cliente.id+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.articulo.id!=this.orden.articulo.id) ? 'Articulo: '+this.orden.articulo.id+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.fecha_entrega!=this.orden.fecha_entrega) ? 'Fecha entrega: '+this.orden.fecha+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.fecha!=this.orden.fecha) ? 'Fecha de cierre: '+this.orden.fecha+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.fecha_orden!=this.orden.fecha_orden) ? 'Fecha Orden: '+this.orden.fecha_orden+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.carpeta_cliente!=this.orden.carpeta_cliente) ? 'Carpeta Cliente: '+this.orden.carpeta_cliente+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.detalles_diseno!=this.orden.detalles_diseno) ? 'Detalles de diseño: '+this.orden.detalles_diseno+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.observaciones!=this.orden.observaciones) ? 'Observaciones: '+this.orden.observaciones+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.prioridad!=this.orden.prioridad) ? 'Prioridad: '+this.orden.prioridad+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.plancha!=this.orden.plancha) ? 'Plancha: '+this.orden.plancha+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.cabida!=this.orden.cabida) ? 'Cabida: '+this.orden.cabida+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.tamano!=this.orden.tamano) ? 'Medida del trabajo: '+this.orden.tamano+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.medida_material!=this.orden.medida_material) ? 'Medida material: '+this.orden.medida_material+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.cantidad!=this.orden.cantidad) ? 'Cantidad : '+this.orden.cantidad+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.valor_unitario!=this.orden.valor_unitario) ? 'Precio unitario: '+this.orden.valor_unitario+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.descuento!=this.orden.descuento) ? 'Descuento: '+this.orden.descuento+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.abono!=this.orden.abono) ? 'Abono: '+this.orden.abono+', ' : '')+this.cambios
                this.cambios= ((this.ordenIni.valor_impuesto!=this.orden.valor_impuesto) ? 'Impuesto: '+this.orden.valor_impuesto+', ' : '')+this.cambios
            },
            actualizarOrden(){
                let me = this;
                var userj=me.user
                if (me.validarOrden()){
                    return;
                }
                this.comprobarCambios();
                const orden1 = new FormData()
                orden1.set('_method', 'PUT')
                orden1.set('id', parseInt(this.orden.id))
                orden1.set('cambios',this.cambios,)
                orden1.set('user_id',userj.id,)
                orden1.set('estado',this.orden.estado)
                orden1.set('estadop',this.orden.produccion)
                orden1.set('impresa',this.orden.impresa)
                orden1.set('id_cliente', this.orden.cliente.id)
                orden1.set('id_articulo' , this.orden.articulo.id)
                orden1.set('fecha_entrega' , this.orden.fecha_entrega)
                orden1.set('fecha' , this.orden.fecha)
                orden1.set('fecha_orden' , this.orden.fecha_orden)
                orden1.set('carpeta_cliente' , this.orden.carpeta_cliente)
                orden1.set('detalles_diseno' , this.orden.detalles_diseno)
                orden1.set('observaciones' , this.orden.observaciones)
                orden1.set('prioridad' , this.orden.prioridad)
                orden1.set('plancha' , this.orden.plancha)
                orden1.set('unidad' , this.orden.cabida)
                orden1.set('medida_final', this.orden.medida_final)
                orden1.set('tamano', this.orden.tamano)
                orden1.set('medida_material', this.orden.medida_material)
                orden1.set('cantidad', this.orden.cantidad)
                orden1.set('valor_unitario',this.orden.valor_unitario)
                orden1.set('totalParcial',this.orden.totalParcial)
                orden1.set('descuento',this.orden.descuento)
                orden1.set('impuesto',this.orden.impuesto)
                orden1.set('valor_impuesto',this.orden.valor_impuesto)
                orden1.set('total',this.orden.total)
                orden1.set('abono',this.orden.abono)
                orden1.set('saldo',this.orden.saldo)
                orden1.set('detalles',JSON.stringify(this.orden.detalles))
                orden1.set('costos',JSON.stringify(this.orden.costos))
                axios.post(me.dominio+'/orden/actualizar',orden1)
                .then(function (response) {
                    console.log(response)
                    me.listado=1;
                    me.cambios='';
                    me.ordenIni={}
                    me.orden={
                        fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                        fecha_orden:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                        fecha_entrega:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                        plancha:0,
                        prioridad:'normal',
                        pago:0,
                        estado:'C',
                        produccion:'D',
                        impresa:0,
                        carpeta_cliente:'',
                        detalles_diseno:'',
                        observaciones:'',
                        medida_material:0,
                        tamano:0,
                        valor:0,
                        impuesto: 0,
                        valor_impuesto:0,
                        descuento:0,
                        cantidad:1000,
                        valor_unitario:0,
                        total:0,
                        abono:0,
                        saldo:0,
                        cabida:1,
                        cliente_id:0,
                        articulo_id:0,
                        cliente:{},
                        articulo:{},
                        costos:[],
                        detalles:[],
                        asignacion_detalle:{}
                    }
                    
                    me.listarOrdenes(1,me.per_page,me.buscar,'like',me.criterio);
                }).catch(function (error) {
                    console.log(error);
                });
                
            },
            cambiarEstado(orden){
                var me=this
                var userj=me.user
                axios.put(me.dominio+'/orden/cambiarEstado',{
                    'id':orden.id,
                    'user_id':userj.id,
                    'estado':orden.estado,
                    'produccion':orden.produccion
                })
                .then(function (response) {
                   console.log(response)
                    me.filtrarOrdenes(1,me.buscar);
                }).catch(function (error) {
                    console.log(error);
                });
            },
           
         
         
         
           
          
           
            cerrarModali(){
                this.modali=0
                this.arrayInsumos=[]
            },
            cerrarModalo(){
                this.modalo=0
                this.ordenv=[]
                 
            },
            cerrarOpcionePro(){
              
                this.orden = JSON.parse(JSON.stringify(this.orden));  
                this.$emit('cerrarOpcionePro', 1)
               
            },
           
          
        },
        mounted() {
            this.getTiposInsumo()
            this.getTitulosDetalle()
            this.calculopliegos(this.orden)
        }
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
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5) !important;
        z-index: 10000 !important;
        overflow-y: auto !important;
    }
    .mostrar .modal-dialog {
        margin-top: 12vh !important;
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
    .pago{
        color: green !important;
        font-weight: bold;
    }
    .detallesorden{
        display: flex;
        flex-direction: column;
    }
    .detalles{
        flex-direction: column;
    }
    .datosgenerales{
        flex-direction: row;
        flex-wrap: wrap;
    }
    .datosgenerales div{
        margin-left: 10px;

    }
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }
    /* .color{
    justify-content: center;
    height: 40px;
    vertical-align: middle;
    } */
</style>

