<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Insumos y Servicios
                        <button type="button" @click="mostrarDetalle()" class="btn btn-secondary">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                    </div>
                    <!-- Listado-->
                    <template v-if="listado">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12 title-secundario"><label for="">Buscar</label></div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" v-model="buscar_nombre" @keyup="listarInsumo(1,buscar_nombre,'nombre')" class="form-control" placeholder="Buscar por nombre">
                                    <input type="text" v-model="buscar_tipo" @keyup="listarInsumo(1,buscar_tipo,'tipo_costo')" class="form-control" placeholder="Buscar por tipo de insumo o servicio">
                                   
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Tipo</th>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th>Proveedor</th>
                                        <th>Fecha Actualizado</th>
                                        <th>Contacto</th>
                                        <th>Telefono proveedor</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="insumo in arrayInsumo" :key="insumo.id">
                                        <td>
                                            <button type="button" @click="abrirModal('costois','actualizar',insumo)" class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;
                                            <template>
                                                <button type="button" class="btn btn-danger btn-sm" @click="borrarInsumo(insumo.id)">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </template>
                                        </td>
                                        <td v-text="insumo.tipo_costo"></td>
                                        <td v-text="insumo.nombre"></td>
                                        <td v-text="insumo.valor"></td>
                                        <td v-text="insumo.nombre_proveedor"></td>
                                        <td v-text="insumo.updated_at"></td>
                                        <td v-text="insumo.contacto"></td>
                                        <td v-text="insumo.telefono_contacto"></td>
                                        <td v-text="insumo.estado"></td>
                                    </tr>                                
                                </tbody>
                            </table>
                        </div>
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
                    </template>
                    <!--Fin Listado-->
                    
                    <!-- Detalle-->
                    <template v-else>
                    <div class="card-body">
                        <div class="form-group row border p-3">
                            
                            <div class="col-md-5">
                                <label for="">Nombre(*)</label>
                                <input type="text" class="form-control" v-model="nombre" placeholder="Nombre del insumo o servicio">
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Valor(*)</label>
                                    <input type="number" class="form-control" v-model="valor" placeholder="$0.00">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Unidad de medida(*)</label>
                                    <input type="text" class="form-control" v-model="unidad" placeholder="cm, metro, grs, kilo, pliego, etc.">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo Insumo o Servicio(*)</label>
                                    <select class="form-control" v-model="tipo_costo">
                                        <option value="0">Seleccione</option>
                                        <option value="Papel">Papel</option>
                                        <option value="Corte material">Corte material</option>
                                        <option value="Placha">Plancha</option>
                                        <option value="Cirel">Cirel</option>
                                        <option value="Marco screen">Marco screen</option>
                                        <option value="Clise">Clise</option>
                                        <option value="Tiraje">Tiraje</option>
                                        <option value="Refile">Refile y corte</option>
                                        <option value="Corte laser">Corte laser</option>
                                        <option value="Transporte">Transporte y carreteo</option>
                                        <option value="Montaje">Montaje</option>
                                        <option value="Retablos">Retablos</option>
                                        <option value="Camisetas">Camiseta sublimación</option>
                                        <option value="Impresionf">Impresion gran formato</option>
                                        <option value="Telas">Telas</option>
                                        <option value="Troquelado">Troquelado</option>
                                        <option value="Terminado">Terminado</option>
                                        <option value="Acabado">Acabado</option>
                                        <option value="Numeración">Numeración</option>
                                        <option value="Medio corte">Medio corte</option>
                                        <option value="Estampado">Estampado</option>
                                        <option value="Otros">Otros costos</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                   
                                    <label>Proveedor(*) <span style="color:red" v-show="idproveedor==0">(*Seleccione)</span> </label>
                                    <div class="form-group row">
                                        <div class="col-md-3">
                                            <div class="form-inline">
                                                <input type="text" class="form-control" v-model="search" @keyup="selectProveedor('nuevo')" placeholder="Ingrese artículo">
                                                
                                            </div>  
                                        </div>
                                        <div class="col-md-6">

                                            <div class="form-inline" v-if="proveedor_seleccionado!=null">
                                                <label for="">Proovedor seleccionado </label>
                                                <input type="text" readonly class="form-control ml-1" v-model="proveedor_seleccionado.nombre">
                                            </div>                                  
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div v-show="errorInsumo" class="form-group row div-error">
                            <div class="text-center text-error">
                                <div v-for="error in errorMostrarMsjInsumo" :key="error" v-text="error">

                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</button>
                                <button type="button" class="btn btn-primary" @click="registrarInsumo()">Registrar Insumo o Servicio</button>
                            </div>
                        </div>
                    </div>
                    </template>
                    <!-- Fin Detalle-->
                    <!-- Listado de proveedores-->
                    <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalp}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                        <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Seleccione un proveedor</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalp()">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <template v-if="arrayProveedor">
                                            <div class="list-group">
                                                <a href="#" 
                                                class="list-group-item list-group-item-action" 
                                                :class="{'active' : seleccionado}" 
                                                v-for="(proveedor,index) in arrayProveedor" 
                                                :key="proveedor.id" 
                                                v-text="proveedor.nombre"
                                                @click="getDatosProveedor(proveedor,index)">
                                               </a> 
                                            </div>
                                        </template>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" @click="cerrarModalp()" data-dismiss="modal">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- fin listado proveedores -->
                    <!--Inicio del modal actualizar-->
                    <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog modal-primary modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" v-text="tituloModal"></h4>
                                    <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                                        <div class="form-group row">
                                            <label class="col-md-3 form-control-label" for="text-input">Proveedor(*)</label>
                                            <div class="col-md-9">
                                                <div class="form-group">
                                   
                                                    <label>Proveedor(*) <span style="color:red" v-show="idproveedor==0">(*Seleccione)</span> </label>
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <div class="form-inline">
                                                                <input type="text" class="form-control" v-model="search" @keyup="selectProveedor('edit')" placeholder="Ingrese artículo">
                                                                
                                                            </div>  
                                                        </div>
                                                        <div class="col-md-8">

                                                            <div class="form-inline" v-if="proveedor_seleccionado!=null">
                                                                <label for="">Proovedor seleccionado </label>
                                                                <input type="text" readonly class="form-control ml-1" v-model="proveedor_seleccionado.nombre">
                                                            </div>                                  
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                       
                                        <div class="form-group row">
                                            <label class="col-md-3 form-control-label" for="text-input">Nombre insumo o servicio(*)</label>
                                            <div class="col-md-9">
                                                <input type="text" v-model="nombre" class="form-control" placeholder="Nombre del insumo o servicio">                                        
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label class="col-md-3 form-control-label" for="text-input">Valor(*)</label>
                                            <div class="col-md-9">
                                                <input type="text" v-model="valor" class="form-control" placeholder="Valor del insumo o servicio">                                        
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label class="col-md-3 form-control-label" for="text-input">Unidad de medida(*)</label>
                                            <div class="col-md-9">
                                                <input type="text" v-model="unidad" class="form-control" placeholder="Unidad de medida">                                        
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-3 form-control-label" for="text-input">Tipo Insumo o Servicio(*)</label>
                                            <div class="col-md-9">
                                                <select class="form-control" v-model="tipo_costo">
                                                    <option value="0">Seleccione</option>
                                                    <option value="Papel">Papel</option>
                                                    <option value="placha">Plancha</option>
                                                    <option value="tiraje">Tiraje</option>
                                                    <option value="montaje">Montajes</option>
                                                    <option value="retablo">Retablo</option>
                                                    <option value="camisetas">Camiseta sublimación</option>
                                                    <option value="impresionf">Impresion gran formato</option>
                                                    <option value="telas">Tela para sublimación</option>
                                                </select>
                                                                          
                                            </div>
                                        </div>
                                       
                                        <div  v-show="errorInsumo" class="form-group row div-error">
                                            <div class="text-center text-error">
                                                <div v-for="error in errorMostrarMsjInsumo" :key="error" v-text="error">

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                                    <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarInsumo()">Guardar</button>
                                    <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarInsumo()">Actualizar</button>
                                </div>
                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                    </div>
                    <!--Fin del modal-->
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
           
        </main>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import vSelect from 'vue-select';
    export default {
        data (){
            return {
                insumo_id: 0,
                idproveedor:0,
                nombre : '',
                proveedor_seleccionado:{},
                modalp:0,
                tipo_costo : 0,
                valor:'',
                unidad:'',
                arrayInsumo : [],
                arrayProveedor: [],
                listado:1,
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                errorInsumo : 0,
                errorMostrarMsjInsumo : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 1,
                criterio : 'tipo_costo',
                buscar_nombre : '',
                buscar_tipo:'',
                buscar:'',
                search:'',
                seleccionado:false,
                topedit:0
               
            }
        },
        components: {
            vSelect
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

            },

        },
        methods : {
           
            listarInsumo (page,buscar,criterio){
                let me=this;
                var url= '/costop?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    console.log(respuesta);
                    me.arrayInsumo = respuesta.costop.data;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            obtenerProveedor(search){
                let me=this;
                var url= '/proveedor/selectProveedor?filtro='+search;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    q: search
                    me.proveedor_seleccionado=respuesta.proveedores[0];
                    
                })
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
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarInsumo(page,buscar,criterio);
            },
            encuentra(id){
                var sw=0
                for(var i=0;i<this.arrayDetalle.length;i++){
                    if(this.arrayDetalle[i].idarticulo==id){
                        sw=true
                    }
                    return sw
                }
            },
          
            registrarInsumo(){
                if (this.validarInsumo()){
                    return;
                }
                
                let me = this;

                axios.post('/costop/registrar',{
                    'nombre': this.nombre,
                    'tipo_costo': this.tipo_costo,
                    'valor' : this.valor,
                    'unidad' : this.unidad,
                    'idproveedor' : this.idproveedor,

                }).then(function (response) {
                    me.ocultarDetalle();
                    me.listarInsumo(1,'','tipo_costo');
                    me.proveedor_seleccionado={}
                    me.search=''
                }).catch(function (error) {
                    console.log(error);
                });
            },
            actualizarInsumo(){
               if (this.validarInsumo()){
                    return;
                }
                
                let me = this;

                axios.put('/costop/actualizar',{
                    'nombre': this.nombre,
                    'tipo_costo': this.tipo_costo,
                    'valor' : this.valor,
                    'unidad' : this.unidad,
                    'idproveedor' : this.idproveedor,
                    'id':this.insumo_id
                }).then(function (response) {
                    me.cerrarModal();
                    me.listarInsumo(1,'','tipo_costo');
                    me.proveedor_seleccionado={}
                    me.search=''
                }).catch(function (error) {
                    console.log(error);
                }); 
            },
            validarInsumo(){
                this.errorInsumo=0;
                this.errorMostrarMsjInsumo =[];

                if (!this.nombre) this.errorMostrarMsjInsumo.push("El nombre del insumo o servicio no puede estar vacío.");
                if (!this.valor) this.errorMostrarMsjInsumo.push("El valor del insumo o servicio no puede estar vacío.");
                if (!this.unidad) this.errorMostrarMsjInsumo.push("La unidad de medida del insumo o servicio no puede estar vacío.");
                if (this.tipo_costo==0) this.errorMostrarMsjInsumo.push("Seleccione una tipo de costo.");
                if (this.idproveedor==0) this.errorMostrarMsjInsumo.push("Seleccione un proveedor.");
                if (this.errorMostrarMsjInsumo.length) this.errorInsumo = 1;

                return this.errorInsumo;
            },
            mostrarDetalle(){
                this.listado=0;
            },
            ocultarDetalle(){
                this.listado=1;
                this.proveedor_seleccionado={}
                this.search=''
                this.nombre=''
                this.unidad=''
                this.valor=''
                this.tipo_costo='0'
            },
            cerrarModal(){
                this.modal=0;
                this.tituloModal='';
                this.nombre='';
                this.valor='';
                this.unidad='';
                this.tipo_costo='';
                this.proveedor_seleccionado={}
            },
            abrirModal(modelo, accion, data = []){
                switch(modelo){
                    case "costois":
                    {
                        switch(accion){
                            case 'registrar':
                            {
                                this.modal = 1;
                                this.tituloModal = 'Registrar Artículo';
                                this.idcategoria=0;
                                this.nombre_categoria='';
                                this.codigo='';
                                this.nombre= '';
                                this.precio_venta=0;
                                this.stock=0;
                                this.descripcion = '';
                                this.tipoAccion = 1;
                                break;
                            }
                            case 'actualizar':
                            {
                                
                                //console.log(data);
                                this.modal=1
                                this.tituloModal='Actualizar Insumo o Servicio'
                                this.tipoAccion=2
                                this.insumo_id=data['id']
                                this.idproveedor=data['idproveedor']
                                this.unidad=data['unidad_medida']
                                this.nombre = data['nombre']
                                this.valor=data['valor']
                                this.tipo_costo=data['tipo_costo']
                                this.obtenerProveedor(data['nombre_proveedor']);
                                break;
                            }
                        }
                    }
                }
            },
            borrarInsumo(id){
               const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de borrar el registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;
                    axios.delete('/costop/borrar?id='+id,{
                        'id': id
                    }).then(function (response) {
                        me.listarInsumo(1,'','nombre');
                        swal(
                        'Borrado!',
                        'El registro ha sido borrado con éxito.',
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
            activarUsuario(id){
               swal({
                title: 'Esta seguro de activar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/activar',{
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1,'','nombre');
                        swal(
                        'Activado!',
                        'El registro ha sido activado con éxito.',
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
        },
        mounted() {
            this.listarInsumo(1,this.buscar,this.criterio);
        }
    }
</script>
<style>    
    .modal-content{
        width: 100% !important;
        position: absolute !important;
        box-shadow:0px 0px 20px rgba(0,0,0,0.2);
    }
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
        position: absolute !important;
    }
    .modal-bajo{
        top:150px;
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
