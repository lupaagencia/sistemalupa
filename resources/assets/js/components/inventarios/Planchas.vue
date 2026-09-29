<template>
    <div>
        <div v-if="seccion=='listado'">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> Planchas
                <button type="button" @click="abrirModal('nuevo')" class="btn btn-success boton-principal">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
            </div>
                
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">

                        <div class="input-group">
                            <button @click="listarPlanchas(1,'',criterio)">Todo</button>
                            <select class="form-control col-md-3" v-model="criterio">
                            <option value="asignado_id">Nombre cliente</option>
                            <option value="referencia">Referencia</option>
                            
                            </select>
                            <input type="text" v-model="buscar" @keyup.enter="listarPlanchas(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                            <button type="submit" @click="listarPlanchas(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Opciones</th>
                            <th>Cliente</th>
                            <th>Referencia</th>
                            <th>Detalles</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                           
                        </tr>
                    </thead>
                    <tbody v-if="arrayMateriasPrimas.length>0">
                        <tr v-for="materia in arrayMateriasPrimas" :key="materia.id">
                            <td class="btnOpciones">
                                <button type="button" class="btn btn-danger btn-sm" @click="eliminarPlancha(materia.id)">
                                    <i class="icon-trash"></i>
                                </button>
                                <button type="button" @click="abrirModal('editar',materia)" class="btn btn-warning btn-sm">
                                <i class="icon-pencil"></i>
                                </button>
                            </td>
                            <td ><button @click="listarPlanchas(1,materia.cliente.razonsocial,'asignado_id')">{{ materia.cliente.razonsocial }}</button> </td>
                            <td v-text="materia.referencia"></td>
                            <td v-text="materia.detalles"></td>
                            <td v-text="materia.cantidad"></td>
                            <td v-text="materia.estado"></td>
                        </tr>                                
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="5"> 
                                No hay registros
                            </td>
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
         <div v-else-if="seccion=='nuevo'">
            <nuevaPlancha :materiasPrimas="materiasPrimas" @mostrarListado="mostrarMateriaPrima" ></nuevaPlancha>
         </div>   
         <div v-else>
            <nuevaPlancha :materiasPrimas="materiasPrimas" @mostrarListado="mostrarMateriaPrima" ></nuevaPlancha>
         </div>   
    </div>
</template>

<script>
    import nuevaPlancha from './NuevaPlancha'
    export default {
        data (){
            return {
                seccion:'listado',
                show:'planchas',
                arrayMateriasPrimas : [],
                materiasPrimas:{},
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
                criterio : 'asignado_id',
                buscar : ''
            }
        },
        components:{
            nuevaPlancha
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
            eliminarPlancha(id){
                const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de borrar este item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;
                    var url= '/inventarios/eliminar?id='+ id+'&tipo=plancha';
                    axios.delete(url,{'_method': 'DELETE'})
                    .then(function (response) {
                        me.listarPlanchas(1,me.buscar,me.criterio);
                        swal(
                        'Eliminado!',
                        'El item ha sido eliminado con éxito.',
                        'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });
                    
                    
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                    
                }
                }) 
            },
            listarPlanchas (page,buscar,criterio){
                let me=this;
                var url= '/inventariosPlanchas?page='+page+'&buscar='+buscar+'&criterio='+criterio
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayMateriasPrimas = respuesta.materias.data;
                    me.pagination= respuesta.pagination;
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
                me.listarPlanchas(page,buscar,criterio);
            },
           
           
          
            abrirModal(accion, data){
                switch(accion){
                    case 'nuevo':
                    {
                        this.seccion='nuevo';
                        this.materiasPrimas={
                            cliente:{razonsocial:'',id:0},
                            costois:{nombre:''},
                            referencia:'',
                            tipo:'Plancha',
                            ubicacion:'Planta',
                            detalles:'',
                            estado:'Disponible',
                            uso:'',
                            cantidad:0,
                            unidad:'',
                            cambio:''
                            
                        }
                        break;
                    }
                    case 'editar':
                    {
                        this.seccion='editar'
                        this.materiasPrimas=data
                        break;
                       
                    }
                }
            },
            mostrarMateriaPrima(value){
                this.seccion=value
                this.listarPlanchas(1,this.buscar,this.criterio)

            }
        },
        mounted() {
            this.listarPlanchas(1,this.buscar,this.criterio);
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
