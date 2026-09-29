<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='proyeccion'}" @click="mostrarTab('proyeccion')" id="proyeccion-tab" data-bs-toggle="tab" data-bs-target="#proyeccion" type="button" role="tab" aria-controls="proyeccion" aria-selected="true">Proyección de papel</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='procesos'}" @click="mostrarTab('procesos')" id="procesos-tab" data-bs-toggle="tab" data-bs-target="#procesos" type="button" role="tab" aria-controls="procesos" aria-selected="false">Flujo de produccion</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='ordenes'}" @click="mostrarTab('ordenes')" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos" aria-selected="false">Ordenes de producción</button>
                        </li>
                    </ul>
                    
                    <!-- Listado-->

                    <div class="tab-content" id="myTabContent">
                        <div v-if="show=='proyeccion'" id="proyeccion" role="tabpanel" aria-labelledby="proyeccion-tab">
                            <proyeccion></proyeccion>
                        </div>
                        <div v-else-if="show=='procesos'" id="procesos" role="tabpanel" aria-labelledby="procesos-tab">
                           <flujoproduccion></flujoproduccion>
                        </div>
                       
                        <div v-else-if="show=='ordenes'" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
                             <div class="card-body">
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                         <div class="reportesArchivos">
                                      
                                            <div class="card" >
                                            
                                                <div class="card-body" style="background:#0d2bfd">
                                                    <h5 class="card-title" style="color:#fff">Ordenes</h5>
                                                    <ul v-if="reportesOp.length>0" class="list-group list-group-flush">
                                                        <li v-for="(reporte, index) in reportesOp" :key="index" class="list-group-item reportes">
                                                            <div><a :href="`/reportes/${reporte.archivo}`">{{reporte.archivo}}</a></div>
                                                            <div>

                                                            <button type="button" class="btn btn-danger btn-sm" @click="eliminarReporte(reporte.id,index, 'orden')">
                                                                <i class="icon-trash"></i>
                                                            </button>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <ul v-else class="list-group list-group-flush"><li class="list-group-item">No hay reportes de orden de corte</li></ul>
                                                </div>
                                            </div>
                                           
                                        </div>
                                    </div>
                                    <div class="col-md-9">

                                        <template>
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <button class="btn btn-primary" @click="listarOrdenes()">Listar Ordenes</button>
                                                </div>
                                                 <div class="col-md-2">
                                                    <button @click="generarExcelOrdenes()" class="btn btn-success">Generar Excel</button>
                                                </div>
                                            </div>
                                             <div v-if="ordenesProduccion.length>0" class="card p-2">
                                                <h3>Lista de ordenes de producción</h3>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped table-sm">
                                                               
                                                                <tr>
                                                                    <th><i class="icon-trash"></i></th><th>Impresa <input type="checkbox" v-model="impresa" @change="seleccionarTodasOp()"></th><th>Fecha</th><th>Pliegos</th><th>Cantidad Tamaños</th><th>Cabida</th><th>Material - Tamaño - Pliegos</th><th>Cliente</th><th>Producto</th><th>Especificaciones</th><th>Placha</th><th>Prioridad</th>
                                                                </tr>
                                                                <tr v-for="(orden,index) in ordenesProduccion" :key="index" >
                                                                    <td>
                                                                        <button @click="eliminarOrden(index)" type="button" class="btn btn-danger btn-sm">
                                                                            <i class="icon-close"></i>
                                                                        </button>
                                                                    </td>
                                                                     <td><input type="checkbox" v-model="orden.impresa"> </td>
                                                                    <td v-text="orden.fecha"></td>
                                                                    <td v-text="orden.pliegos"></td>
                                                                    <td>{{orden.tamanos}} + {{orden.carpeta_cliente}} </td>
                                                                    <td><input type="text" @keyup="cantidadTamanos()" v-model="orden.cabida"></td>
                                                                    <td v-text="orden.papel"></td>
                                                                    <td v-text="orden.rasonsocial"></td>
                                                                    <td>{{`${orden.cantidad} ${orden.articulo}`}}</td>
                                                                    <td>
                                                                        <ul>
                                                                            <li v-for="(detalle, index) in orden.detalles" :key="index"> {{detalle.titulo}}: {{detalle.valor}} {{detalle.descripcion}}</li>
                                                                        </ul>
                                                                    </td>
                                                                    <td ><input type="text" v-model="orden.plancha">{{orden.plancha ? 'Existe' : 'Nueva'}}</td> 
                                                                    <td v-text="orden.prioridad"></td> 
                                                                    
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <button @click="generarExcelOrdenes()" class="btn btn-success">Generar Excel</button>
                                            </div>
                                        </template>
                                       
                                    </div>
                                </div>
                               
                               
                            </div>
                        </div>
                        <div v-else>
                            <proyeccion></proyeccion>
                        </div>
                    </div>
                    <!--Fin Listado-->
                   
                </div>
                <!-- Fin ejemplo de tabla Listado -->
                 
            </div>
            
             
           
        </main>
</template>

<script>
   
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    import proyeccion from './partes/Proyeccion.vue'
    import flujoproduccion from './reportes/flujoProduccion.vue'
    export default {
        data (){
            return {
                show: 'proyeccion',
                reporte:1,
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
                impresa:false

               
            }
        },
       
        components: {
            proyeccion,
            flujoproduccion
        },
        computed:{
            
        },
        methods : {
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
                        let pliegos = 0
                        let tamanos = 0
                        let papelList = []
                        o.costos.forEach(e => {
                            if(e.titulo=='Papel' || e.titulo=='papel'){
                                let cMaterial = e.medida_material || o.medida_material || 'N/A'
                                let nPapel = e.nombre || (e.costois ? e.costois.nombre : 'Papel')
                                let compLabel = e.componente ? ` [${e.componente}]` : ''
                                papelList.push(`${e.cantidad} Pliegos ${nPapel}${compLabel}, Corte: ${cMaterial}`)
                                pliegos += parseInt(e.cantidad || 0)
                                tamanos += (parseInt(e.descripcion || 0) - parseInt(o.carpeta_cliente || 0))
                            }
                        });
                        o.pliegos = pliegos
                        o.tamanos = tamanos
                        o.papel = papelList.length > 0 ? papelList.join(' | ') : `${pliegos} Pliegos, Corte: ${o.medida_material}`
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
                var datos={
                    'reporte':this.reporte,
                    'tipo':this.tipo,
                    'iniciado':this.iniciado,
                    'terminado':this.terminado,
                    'pago':this.pago,
                    'fechaI':this.fechaI,
                    'fechaF':this.fechaF,
                    'idproveedor':this.idproveedor,
                }
                var url= '/orden/reporteProcesos'
                axios.post(url,{
                     'datos':JSON.stringify(datos),
                }).then(function (response) {
                    let respuesta = response.data;
                    console.log(respuesta)
                    me.reportes=respuesta;
                    me.reportes.forEach(e=>{
                        e.papel=e.papel[0]
                        e.completado=parseInt(e.completado)
                        e.terminado=parseInt(e.terminado)
                    })
                    me.listareportesPapel()
                    me.listareportesTiraje()
                    me.listareportesTroquelado()
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            generarExcelReporte(){
                let me=this;
                var url= '/orden/generarReporteProcesos'
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

