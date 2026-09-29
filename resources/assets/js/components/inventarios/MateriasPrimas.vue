<template>
    <div>
        <div v-if="seccion=='listado'">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> Materias Primas
                <button type="button" @click="abrirModal('nuevo')" class="btn btn-success boton-principal">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
               
            </div>
                
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <button type="submit" @click="listarMateriaPrimas(1,'',criterio)" class="btn btn-success">Ver Todo</button>
                            <select class="form-control col-md-3" v-model="criterio">
                                <option value="referencia">Nombre papel</option>
                                <option value="ubicacion">Ubicación</option>
                                
                            </select>
                            <input type="text" v-model="buscar" @keyup.enter="listarMateriaPrimas(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                            <button type="submit" @click="listarMateriaPrimas(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                            
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Opciones</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th>Cantidad</th>
                            <th>Detalles</th>
                            <th>Estado</th>
                           
                        </tr>
                    </thead>
                    <tbody v-if="arrayMateriasPrimas.length>0">
                        <tr v-for="materia in arrayMateriasPrimas" :key="materia.id">
                            <td class="btnOpciones">
                                <button type="button" title="Eliminar materia prima" @click="eliminar(materia.id)" class="btn btn-danger btn-sm">
                                <i class="icon-trash"></i>
                                </button>
                                <button type="button" title="Editar Materia Prima" @click="abrirModal('editar',materia)" class="btn btn-warning btn-sm">
                                <i class="icon-pencil"></i>
                                </button>
                                <button type="button" title="Realizar Movimiento de materia prima" @click="abrirModal('movimiento',materia)" class="btn btn-info btn-sm">
                                <i class="fa fa-edit"></i>
                                </button>
                            </td>
                            <td v-text="materia.referencia"></td>
                            <td v-text="materia.tipo"></td>
                            <td v-text="materia.ubicacion"></td>
                            <td v-text="materia.cantidad"></td>
                            <td v-text="materia.unidad"></td>
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
         <div v-else-if="seccion=='nuevo' || seccion=='editar'">
            <nuevaMateriaPrima :seccion="seccion" :materiasPrimas="materiasPrimas" @mostrarListado="mostrarMateriaPrima" ></nuevaMateriaPrima>
         </div>   
         <div v-else>
            <movimientomaterial :movimiento="movimiento" @mostrarListado="mostrarMateriaPrima" ></movimientomaterial>
         </div>   
    </div>
</template>

<script>
    import nuevaMateriaPrima from './NuevaMateriaPrima'
    import movimientomaterial from './Movimiento'
    export default {
        data (){
            return {
                seccion:'listado',
                show:'materias',
                arrayMateriasPrimas : [],
                materiasPrimas:{},
                movimiento:{},
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
                criterio : 'referencia',
                buscar : ''
            }
        },
        components:{
            nuevaMateriaPrima,
            movimientomaterial
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
            eliminar(id){
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
                    var url= '/inventarios/eliminar?id='+ id+'&tipo=materia';
                    axios.delete(url,{'_method': 'DELETE'})
                    .then(function (response) {
                        me.listarMateriaPrimas(1,me.buscar,me.criterio);
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
            mostrarTab(value){
                this.$emit('cambiarTab', value)
            },
            listarMateriaPrimas (page,buscar,criterio){
                let me=this;
                var url= '/inventariosMateriasPrimas?page='+page+'&buscar='+buscar+'&criterio='+criterio
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayMateriasPrimas = respuesta.materias.data;
                    me.arrayMateriasPrimas.forEach(e => {
                        e.cliente={razonsocial:'',id:0}

                    });
                    me.pagination= respuesta.pagination;
                    console.log(me.arrayMateriasPrimas)
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
                me.listarMateriaPrimas(page,buscar,criterio);
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
                            tipo:'Papel',
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
                    case 'movimiento':
                    {
                        this.seccion='movimiento'
                        this.movimiento.id=0
                        this.movimiento.costois=data.costois
                        this.movimiento.inventarios_materia_prima_id=data.id
                        this.movimiento.tipo='entrada'
                        this.movimiento.cantidad=0
                        this.movimiento.costo_unitario=0
                        this.movimiento.costo_total=0
                        break;
                       
                    }
                }
            },
            mostrarMateriaPrima(value){
                this.seccion=value
                this.listarMateriaPrimas(1,this.buscar,this.criterio);

            }
        },
        mounted() {
            this.listarMateriaPrimas(1,this.buscar,this.criterio);
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
