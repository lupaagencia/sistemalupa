<template>
    <div class="card-body" id="contestatuspro" v-scroll="handleScroll">
        <div class="form-group row">
            <!-- <div class="col-sm-3">
                    <div class="reportesArchivos">
                
                    <div class="card" >
                    
                        <div class="card-body" style="background:#0d2bfd">
                            <h5 class="card-title" style="color:#fff">Reportes de corte</h5>
                            <ul v-if="reportesPapel.length>0" class="list-group list-group-flush">
                                <li v-for="(reporte, index) in reportesPapel" :key="index" class="list-group-item reportes">
                                    <div><a :href="`/reportes/${reporte.archivo}`">{{reporte.archivo}}</a></div>
                                    <div>

                                    <button type="button" class="btn btn-danger btn-sm" @click="eliminarReporte(reporte.id,index, 'corte')">
                                        <i class="icon-trash"></i>
                                    </button>
                                    </div>
                                </li>
                            </ul>
                            <ul v-else class="list-group list-group-flush"><li class="list-group-item">No hay reportes de orden de corte</li></ul>
                        </div>
                    </div>
                 
                </div>
            </div> -->
            <div class="col-md-12">
                <div class="row card p-2 filtrop">
                    <div class="col-md-2">
                        <label for="tiporeporte" class="form-label">Tipo proceso</label>
                        <select class="form-select" aria-label="Default select example" v-model="statusorden" @change="generarReporte()">
                            <option v-for="(st, index) in status"  :key="index" :value="st.proceso || st.estado">{{ st.proceso || st.estado }}</option>
                        </select>
                    </div>
                  
                  
                    <div class="col-md-1">
                        <button class="btn btn-primary" @click="generarReporte()">Consultar</button>
                    </div>
                        
                </div>
                <template >
                    <div class="card p-2">
                        <h3>Lista Papel <button @click="actualizar()" class="btn btn-success boton-principal">Guardar</button></h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm">
                                        <tr>
                                            <th><i class="icon-trash"></i> Orden</th>
                                            <th class="inputnom">Cliente</th>
                                            <th class="inputref">Cantidad Ref.</th>
                                            <th class="inputnom">Material</th>
                                            <th>Corte Material</th>
                                            <th>Medida Final</th>
                                            <th class="inputs">Tamaño / Cabida / Sobrante</th>
                                            <th>Cantidad de tamaños</th>
                                            <th>Cantidad pliegos</th>
                                        </tr>
                                        <tr v-for="(papel,i) in arrayPapel" :key="i">
                                            <td>
                                                <button @click="eliminarPapel(i)" type="button" class="btn btn-danger btn-sm">
                                                    <i class="icon-close"></i>
                                                </button>
                                                {{papel.id}}
                                            </td>
                                            <td ><input type="text" v-model="papel.cliente.razonsocial"></td>
                                            <td class="inputref"><input type="text" v-model="papel.cantidad"> <input type="text" v-model="papel.articulo.nombre"></td>
                                            <td style="min-width: 200px;">
                                                <input type="text" v-model="papel.detalle.valor" class="form-control form-control-sm mb-1" placeholder="Nombre papel (Detalle)">
                                                
                                                <div v-if="papel.costos.costo_nombre && papel.costos.costo_nombre != ''" class="mb-1 d-flex align-items-center justify-content-between bg-light p-1 rounded border">
                                                    <span class="small font-weight-bold text-truncate" :title="papel.costos.costo_nombre">
                                                        <i class="fa fa-tag text-info"></i> {{ papel.costos.costo_nombre }}
                                                    </span>
                                                    <button type="button" class="btn btn-outline-danger btn-xs py-0 px-1 ml-1" @click="limpiarInsumo(papel.costos)" title="Quitar/Cambiar insumo actual">
                                                        <i class="icon-close"></i>
                                                    </button>
                                                </div>

                                                <div class="input-group input-group-sm">
                                                    <input :id="'modificar'+i" type="text" class="form-control form-control-sm" @keyup="selectInsumos(papel.costos, 'modificar'+i, papel)" placeholder="Buscar / Cambiar Insumo...">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary btn-sm" type="button" @click="buscarInsumo(papel, 'modificar'+i)" title="Buscar Insumos">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td >
                                                <input type="text" v-model="papel.medida_material">
                                                <!-- <select name="" id="" v-model="papel.medida_material">
                                                    <option v-for="(corte,i) in papel.posiblesCortes" :key="i" :value="corte.largo+'x'+corte.ancho">{{corte.largo+'x'+corte.ancho}}</option>
                                                </select> -->
                                            </td>
                                            <td >
                                                <input type="text" v-model="papel.medida_final">
                                            </td>
                                            <td class="inputs">
                                                <input @change="cantidades(papel),generarPosiblesCortes(papel)" type="text" v-model="papel.tamano">
                                                <input @change="cantidades(papel)" type="text" v-model="papel.cabida">
                                                <input @change="cantidades(papel)" type="text" v-model="papel.carpeta_cliente"> 
                                            </td>
                                            <td><input type="text" v-model="papel.costos.descripcion"></td>
                                            <td><input  type="text" v-model="papel.costos.cantidad"></td>
                                           
                                            
                                        </tr>
                                    </table>
                                    <button @click="actualizar()" class="btn btn-success boton-principal">Guardar</button>
                                </div>
                            </li>
                        </ul>
                       
                        <button @click="generarExcelReporte" class="btn btn-success boton-principal">Generar Excel</button>
                        <ul v-if="papelesTotales.length>0">
                            <li v-for="(papel, index) in papelesTotales" :key="index">{{ papel.costo_nombre }} - {{ papel.cantidad_total }}</li>
                        </ul>
                    </div>
                </template>
                               
              
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
                                @click="agregarCosto(insumo,objeto)">
                                {{insumo.nombre}} - <small v-if="insumo.proveedor">({{insumo.proveedor.nombre}})</small>
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
import { forEach } from 'lodash';

    
    export default {
        data (){
            return {
                reportesPapel:[],
                statusnombres:['Espera', 'Corte material', 'Impresión', 'Transito', 'Acabado','Troquelado', 'Terminado', 'Empacado', 'Para entregar'],
                arrayPapel : [],
                modal : 0,
                modali:0,
                scroll:0,
                tituloModal : '',
                tipoAccion : 0,
                status:[],
                statusorden:'Compra papel',
                arrayInsumos:[],
                objeto:{},
                papelSeleccionado: null,
                papelesTotales:[]
               
            }
        },
        computed:{
            filteredItems() {
            return this.arrayPapel.filter(item =>
                item.costos.costo_nombre.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
    },

           
            
        },
        methods : {
            generarExcelReporte(){
                this.papelesTotales=this.procesarCostos(this.arrayPapel)
            },
            procesarCostos(papeles) {
                // 1. Crear un objeto para almacenar las sumas por nombre_costo
                const costosSumados = {};

                // 2. Iterar sobre el array de papeles y sumar las cantidades
                papeles.forEach(papel => {
                    const nombreCosto = papel.costos.costo_nombre;
                    const cantidad = papel.costos.cantidad;

                    if (costosSumados[nombreCosto]) {
                    costosSumados[nombreCosto] += cantidad;
                    } else {
                    costosSumados[nombreCosto] = cantidad;
                    }
                });

                // 3. Convertir el objeto a un array de objetos para mejor manejo
                const resultado = Object.keys(costosSumados).map(nombreCosto => ({
                    costo_nombre: nombreCosto,
                    cantidad_total: costosSumados[nombreCosto]
                }));

                    // 4. Ordenar el array por nombre_costo (opcional)
                    resultado.sort((a, b) => a.costo_nombre.localeCompare(b.costo_nombre));

                return resultado;
            },


            handleScroll: function (evt, el) {
                const myElement = document.getElementById('contestatuspro');
                if (!myElement) return;
                const rect = myElement.getBoundingClientRect();
                const vtop = rect.top + window.scrollY;
                if (window.scrollY < vtop) {
                    this.scroll=window.scrollY
                }else{
                    this.scroll=window.scrollY-100
                }
            },
            generarPosiblesCortes(orden) {
                orden.posiblesCortes = [];
                const cantidad = orden.tamano;

                // Revisar todas las combinaciones posibles de cortes
                for (let i = 1; i <= cantidad; i++) {
                    const ancho = Math.floor(70 / i);
                    const largo = Math.floor(100 / (cantidad / i));

                    // Asegurar que las dimensiones son válidas y que se ajustan al pliego
                    if (ancho > 0 && largo > 0) {
                    orden.posiblesCortes.push({ ancho, largo });
                    }
                }
            },
           
            cantidades(orden){
                var tamanos=orden.cantidad/parseFloat(orden.cabida)+parseFloat(orden.carpeta_cliente)
                orden.costos.descripcion=tamanos
                var pliegos=(orden.cantidad/parseFloat(orden.cabida)+parseFloat(orden.carpeta_cliente))/parseFloat(orden.tamano)
                orden.costos.cantidad=pliegos
            },
            cantidadesAll(){
                this.arrayPapel.forEach(e => {
                    this.cantidades(e)
                });
            },
            selectInsumos(objeto, id, papel = null){
                let me=this;
                me.objeto=objeto;
                if (papel) {
                    me.papelSeleccionado = papel;
                }
                var campo = document.getElementById(id);
                var filtro = campo ? campo.value : '';
                var url= '/costop/selectInsumos?filtro='+encodeURIComponent(filtro);
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayInsumos=respuesta.insumos;
                    me.modali=1;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            buscarInsumo(papel, id) {
                let me = this;
                me.objeto = papel.costos;
                me.papelSeleccionado = papel;
                var campo = document.getElementById(id);
                var filtro = campo ? campo.value : '';
                var url = '/costop/selectInsumos?filtro=' + encodeURIComponent(filtro);
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayInsumos = respuesta.insumos;
                    me.modali = 1;
                }).catch(function (error) {
                    console.log(error);
                });
            },
            limpiarInsumo(costo) {
                costo.costo_nombre = '';
                costo.costois_id = 0;
            },
            agregarCosto(insumo,costo){
                let me=this;
                costo.costo_nombre=insumo.nombre;
                costo.costois_id=insumo.id;
                if (me.papelSeleccionado && me.papelSeleccionado.detalle) {
                    me.papelSeleccionado.detalle.valor = insumo.nombre;
                }
                me.modali=0;
            },
            eliminarReporte(id,index,proceso){
                let me=this
                var url= '/orden/borrarReportes?id='+ id + '&_method=DELETE';
                axios.delete(url).then(function (response) {
                    var respuesta= response.data;
                    switch(proceso){
                        case 'orden': me.reportesOp.splice(index,1)
                        break;
                        case 'corte': me.reportesPapel.splice(index,1)
                        break;
                        case 'tiraje': me.reportesTiraje.splice(index,1)
                        break;
                        case 'troquelado': me.reportesTroquelado.splice(index,1)
                        break;
                    }
                  
                })
                    .catch(function (error) {
                        console.log(error);
                });
            },
            listareportesPapel(){
                let me=this
                var url= ''
                axios.get('/orden/listaReportesPapel').then(function (response) {
                    let respuesta = response.data;
                    me.reportesPapel=respuesta;
                })
            },
            estadosdestatus(){
                let me=this;
                axios.get('/statuspro/estadosdestatus').then(function (response) {
                    let respuesta = response.data;
                    me.status = respuesta;
                    const compra = me.status.find(s => {
                        const val = (s.proceso || s.estado || '').toLowerCase();
                        return val.includes('compra');
                    });
                    if (compra) {
                        me.statusorden = compra.proceso || compra.estado;
                    } else if (!me.statusorden) {
                        me.statusorden = 'Compra papel';
                    }
                    me.generarReporte();
                });
            },
           
            generarReporte(){
                let me=this;
               
                var url= '/orden/reporteProyeccionPapel?statusorden='+me.statusorden
                axios.get(url).then(function (response) {
                    console.log(response)
                    let respuesta = response.data;
                    me.arrayPapel=respuesta;
                  
                    me.listareportesPapel()
                    me.cantidadesAll()
                   
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            actualizar(){
                let me = this;
               
                const data = new FormData()
                data.set('data',JSON.stringify(this.arrayPapel))
                axios.post('orden/actualizarPapel',data)
                .then(function (response) {
                    console.log(response)
                    Swal.fire({
                        title: '¡Cambios Guardados!',
                        text: 'La proyección de papel se actualizó correctamente.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    me.generarReporte()
                    
                }).catch(function (error) {
                    console.log(error);
                    Swal.fire('Error', 'No se pudieron guardar los cambios de papel.', 'error');
                });
            },
            cerrarModali(){
                this.modali=0
                this.arrayInsumos=[]
            },
           
        },
        mounted() {
            this.estadosdestatus()
            this.cantidadesAll()
        }
    }
</script>
<style>    
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
    table input{
        width:100%;
    }
    .inputs{
        width:200px !important;
    }
    .inputref{
        width:170px !important;
    }
    .inputnom{
        width:180px !important;
    }
    .inputref input:first-child{
        width:45%;
    }
    .inputref input:last-child{
        width:50%;
    }
    .inputs input{
        width:30%;
    }
</style>

