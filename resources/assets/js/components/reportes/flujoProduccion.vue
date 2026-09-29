<template>
 
     <div class="contenedor">
        <div class="seccion">
           
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="col-md-2">
                        <label for="tiporeporte" class="form-label">Tipo proceso</label>
                        <select class="form-select" aria-label="Default select example" v-model="filtro.tipo">
                            <option  v-for="(proceso,index) in procesos" :value="proceso.proceso">{{ proceso.proceso }}</option>
                           
                        </select>
                    </div>
                    <div class="col-md-3" v-show="false">
                       
                        <div class="form-group" >
                            <label>Proveedor</label>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-inline">
                                        <input type="text" class="form-control" v-model="search" @keyup="selectProveedor('nuevo')" placeholder="Ingrese artículo">
                                    </div>  
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inline" v-if="proveedor_seleccionado!=null">
                                        <input type="text" readonly class="form-control ml-1" v-model="proveedor_seleccionado.nombre">
                                    </div>                                  
                                </div>
                            </div>
                        </div>
                    </div>
                    <template >
                        <div class="col-md-2 ml-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" v-model="filtro.iniciado" id="iniciado">
                                <label class="form-check-label" for="iniciado">
                                    Proceso iniciado
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" v-model="filtro.terminado" id="terminado">
                                <label class="form-check-label" for="terminado">
                                    Proceso terminado
                                </label>
                            </div>
                        </div>
                    </template>
                    
                    <div class="col-md-4">
                        <div>
                            <div class="inputGruop">
                                <label for="tiporeporte" class="form-label">Fecha inicio</label>
                                <input type="date" placeholder="Fecha Inicial" v-model="filtro.fechaI">
                            </div>
                            <div class="inputGruop">
                                <label for="tiporeporte" class="form-label">Fecha final</label>
                                <input type="date" placeholder="Fecha Final" v-model="filtro.fechaF">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary" @click="generarReporte()">Consultar</button>
                    </div>
                        
                </div>
                <template >
                    <reportetotal :reportes="reportes" :procesos="procesos" :filtro="filtro"></reportetotal>
                </template>
              
               
            </div>
        </div>
    </div>
</template>
<script>
   
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    import reportetotal from './ReporteTotal.vue'
    export default {
        data (){
            return {
                show: 'procesos',
                reporte:1,
                procesos:[],
                tipo:'Papel',
                proveedor:'',
                iniciado:0,
                terminado:0,
                pago:false,
                fechaI:'',
                fechaF:'',
                search:'',
                idproveedor:'',
                proveedor_seleccionado:{},
                arrayProveedor:[],
                ordenesProduccion:[],
                modalp:0,
                reportesOp:[],
                reportesPapel:[],
                reportesTiraje:[],
                reportesTroquelado:[],
                reportesOrdenes:[],
                reportes:[],
                pago_tro:false,
                impresa:false,
                filtro:{'tipo':'', 'iniciado': false,'terminado': false,'fechaI':'','fechaF':'',}

               
            }
        },
        components: {
            reportetotal,
        },
        computed:{
            
        },
        methods : {
            getProcesos(){
                let me=this;
                var url= '/statuspro/procesos';
                
                axios.get(url).then(function (response) {
                    console.log(response);
                    me.procesos = response.data;
                })
            },
            cantidadTamanos(){
                let cantidad=0
                this.ordenesProduccion.forEach(o => {
                    cantidad=(parseInt(o.cantidad)/parseInt(o.cabida))+50
                    o.tamano=cantidad
                });
            },
            
            listarOrdenes(){
                let me=this
                var url= ''
                axios.get('/orden/ordenesProducccion').then(function (response) {
                    let respuesta = response.data;
                    me.ordenesProduccion=respuesta
                    respuesta.forEach(o => {
                        let pliegos=0
                        let papel=''
                        let tamanos=0
                        o.costos.forEach(e => {
                            if(e.titulo=='Papel'){
                                pliegos=e.cantidad
                                papel=`${e.cantidad} Pliegos ${e.nombre}, Corte: ${o.medida_material}`
                                tamanos=parseInt(e.descripcion)-parseInt(o.carpeta_cliente)
                            }
                        });
                        o.pliegos=pliegos
                        o.tamanos=tamanos
                        o.papel=papel
                    });
                })
            },
            listareportesOp(){
                let me=this
                var url= ''
                axios.get('/orden/listaReportesOp').then(function (response) {
                    let respuesta = response.data;
                    me.reportesOp=respuesta;
                })
            },
            listareportesPapel(){
                let me=this
                var url= ''
                axios.get('/orden/listaReportesPapel').then(function (response) {
                    let respuesta = response.data;
                    me.reportesPapel=respuesta;
                })
            },
            listareportesTiraje(){
                let me=this
                var url= ''
                axios.get('/orden/listaReportesTiraje').then(function (response) {
                    let respuesta = response.data;
                    me.reportesTiraje=respuesta;
                })
            },
            listareportesTroquelado(){
                let me=this
                var url= ''
                axios.get('/orden/listaReportesTroquelado').then(function (response) {
                    let respuesta = response.data;
                    me.reportesTroquelado=respuesta;
                })
            },
            mostrarTab(show){
                this.show=show
            },
            selectProveedor(action){
                let me=this;
                if(action=='edit'){
                    me.topedit=1
                }
                var url= '/proveedor/selectProveedor?filtro='+this.search;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayProveedor=respuesta.proveedores;
                    me.modalp=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            eliminarPapel(i){
                let me=this
                me.reportes.splice(i,1)
            },
            eliminarTroquelado(index,i){
                let me=this
                me.reportes[index].filas.splice(i,1)
            },
            eliminarOrden(index){
                let me=this
                me.ordenesProduccion.splice(index,1)
            },
            seleccionarTodos(index){
                var filas =this.reportes[index].filas
                for(var i=0;i<filas.length;i++){
                    if(this.pago_tro==true){
                        filas[i].pago=true;
                    }else{
                        filas[i].pago=false;
                    }
                }
            },
            seleccionarTodasOp(){
                var filas =this.ordenesProduccion
                console.log(filas)
                for(var i=0;i<filas.length;i++){
                    if(this.impresa==true){
                        filas[i].impresa=true;
                    }else{
                        filas[i].impresa=false;
                    }
                }
            },
            generarReporte(){
                let me=this;
                
                var url= '/flujo/flujoProduccion'
                axios.post(url,{
                        'reporte':this.reporte,
                        'tipo':this.filtro.tipo,
                        'iniciado':this.filtro.iniciado,
                        'terminado':this.filtro.terminado,
                        'pago':this.pago,
                        'fechaI':this.filtro.fechaI,
                        'fechaF':this.filtro.fechaF,
                }).then(function (response) {
                    let respuesta = response.data;
                    console.log(response)
                    me.reportes=respuesta;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            generarExcelReporte(){
                let me=this;
                var url= '/orden/reporteFlujoProduccion'
                axios.post(url,{
                    'tipo':me.tipo,
                    'datos':JSON.stringify(me.reportes),
                }).then(function (response) {
                    let respuesta = response.data;
                    console.log(respuesta)
                    me.listareportesPapel()
                    me.listareportesTiraje()
                    me.listareportesTroquelado()
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            generarExcelOrdenes(){
                let me=this;
                var url= 'orden/generarReporteOrdenes'
                console.log(JSON.stringify(me.ordenesProduccion))
                axios.post(url,{
                    'datos':JSON.stringify(me.ordenesProduccion),
                }).then(function (response) {
                    let  respuesta = response.data;
                   me.listareportesOp()
                })
                .catch(function (error) {
                    console.log(error);
                });
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
            getDatosProveedor(val1, index){
                let me = this;
                me.loading = true;
                me.idproveedor = val1.id;
                me.proveedor_seleccionado=val1;
                me.search=val1.nombre
                this.seleccionado=true
                me.arrayProveedor=[];
                me.modalp=0
            },
            cerrarModalp(){
                this.modalp=0
                this.arrayProveedor=[]
            },
                     
        
        },
        created() {
            this.cantidadTamanos()
        },
        mounted() {
             this.getProcesos()
            this.listareportesOp()
            this.listareportesPapel()
            this.listareportesTiraje()
            this.listareportesTroquelado()
            this.listarOrdenes()
        }
    }
</script>
<style>  
    .reportes{
        display: flex;
        flex-direction: row;
    }
    .reportes div{
        margin-right: 10px;
    }
    .main{
        min-height:750px;
    }
    .insumos, .producto, .cliente{
        position: relative;
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
    .modal{
        top:10%;
        height:auto;
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

