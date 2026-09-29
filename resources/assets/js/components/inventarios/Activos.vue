<template>
    <div>
        <div v-if="seccion=='listado'">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> Activos
                <button type="button" @click="abrirModal('nuevo')" class="btn btn-success boton-principal">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
            </div>
                
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-control col-md-3" v-model="criterio">
                            <option value="activo">Activo</option>
                           
                            </select>
                            <input type="text" v-model="buscar" @keyup.enter="listarActivos(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                            <button type="submit" @click="listarActivos(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Opciones</th>
                            <th>Tipo</th>
                            <th>Activo</th>
                            <th>Ubicación</th>
                            <th>Responsable</th>
                            <th>Clasificación</th>
                            <th>Estado</th>
                            <th>Datos Activo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="activo in arrayActivos" :key="activo.id">
                            <td class="btnOpciones">
                                <button type="button" @click="abrirModal('editar',activo)" class="btn btn-warning btn-sm">
                                <i class="icon-pencil"></i>
                                </button>
                            </td>
                            <td >
                                {{activo.tipo}} 
                            </td>
                            <td >{{ activo.activo }}
                            </td>
                            <td >{{activo.ubicacion}}</td>
                            <td v-text="activo.responsable"></td>
                            <td v-text="activo.clasificacion"></td>
                            <td v-text="activo.estado"></td>
                            <td v-text="activo.datos_activo"></td>
                        </tr>                                
                    </tbody>
                </table>
                <nav>
                    <ul class="pagination">
                        <li class="page-item" v-if="pagination.current_page > 1">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                        </li>
                        <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                        </li>
                        <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                        </li>
                    </ul>
                </nav>
            </div>
    <!-- Fin ejemplo de tabla Listado -->
        
         </div> 
         <div v-else>
            <nuevoactivo :activo="activo" @mostrarListado="mostrarActivos" ></nuevoactivo>
         </div>   
        
    </div>
</template>

<script>
 import nuevoactivo from './NuevoActivo'
    export default {
        data (){
            return {
                seccion:'listado',
                show:'activos',
                arrayActivos : [],
                activo:{},
                modal : 0,
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : 'activo',
                buscar : ''
            }
        },
        components:{
            nuevoactivo
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

            }
        },
        methods : {
            mostrarTab(value){
                console.log(value)
                this.$emit('cambiarTab', value)
            },
            listarActivos (page,buscar,criterio){
                let me=this;
                var url= '/activo'
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayActivos = respuesta.activos.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarPersona(page,buscar,criterio);
            },
           
            abrirModal(accion, data){
                switch(accion){
                    case 'nuevo':
                    case 'nuevo':
                    {
                        this.seccion='nuevo';
                        this.activo={
                            tipo:'',
                            activo:'',
                            descripcion:'',
                            ubicacion:'Planta',
                            responsable:0,
                            clasificacion:'',
                            estado:'bueno',
                            grupo:'',
                            datos_activo:'',
                           
                            
                        }
                        break;
                    }
                    case 'editar':
                    {
                        this.seccion='editar'
                        this.activo=data
                        break;
                       
                    }
                }
            },
            mostrarActivos(value){
                this.seccion=value
                this.listarActivos(1,this.buscar,this.criterio)

            }
            
        },
        mounted() {
            this.listarActivos(1,this.buscar,this.criterio);
        }
    }
</script>
<style>    
    .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
   
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
</style>
