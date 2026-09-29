<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Categorías
                        <button type="button" class="btn btn-secondary" @click="abrirModal('categoria', 'registrar')">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                        <button type="button" class="btn btn-info text-white ml-2" @click="abrirModalWord()">
                            <i class="fa fa-file-word-o"></i>&nbsp;Importar desde Word (.docx)
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                      <option value="nombre">Nombre</option>
                                      <option value="descripcion">Descripción</option>
                                    </select>
                                    <input type="text" v-model="buscar" @keyup.enter="listarCategorias(1,buscar, criterio)" class="form-control" placeholder="Texto a buscar">
                                    <button type="submit" @click="listarCategorias(1,buscar, criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                        <div class="modal" :class="{'mostrar': modala}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 v-if="arrayArticulos.length>0" class="modal-title">La Categoría tiene productos relacionados</h5>
                                        <h5 v-else class="modal-title">Eliminar</h5>
                                        <button type="button" class="close" @click="cerrarModala()" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div v-if="arrayArticulos.length>0" class="form-group">
                                            <label  class="form-control-label" for="text-input">Seleccione una nueva Categoría</label>
                                            <select class="form-control" v-model="updateid">
                                                <option v-for="categoria in arrayCategoria" :key="categoria.id" :value="categoria.id" v-text="categoria.nombre"></option>
                                            </select>    
                                        </div>                                    
                                        <div v-else class="form-group">
                                            <label class="form-control-label" for="text-input">Seguro quiere eliminar la categoría</label>
                                        </div>                                    
                                        <div v-show="errorCategoria" class="from-group row div-error">
                                            <div class="text-center text-error">
                                                <div v-for="error in errorMostrarMsjCategoria" :key="error" v-text="error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button v-if="arrayArticulos.length>0" type="button" @click="desactivarCategoria()" class="btn btn-danger">Cambiar y Eliminar</button>
                                        <button v-else type="button" @click="eliminarCategoria()" class="btn btn-danger">Eliminar</button>
                                        <button type="button" @click="cerrarModala(categoria_id)" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Opciones</th>
                                    <th>Imagen</th>
                                    <th>Nombre / Jerarquía</th>
                                    <th>Categoría Padre</th>
                                    <th>Descripción</th>
                                    <th>Posición</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="categoria in arrayCategoria" :key="categoria.id">
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm" @click="abrirModal('categoria', 'actualizar', categoria)">
                                          <i class="icon-pencil"></i>
                                        </button> &nbsp;
                                        <template v-if="categoria.condicion">
                                            <button type="button" class="btn btn-danger btn-sm" @click="articulosCategoria(categoria.id)">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button type="button" class="btn btn-success btn-sm" @click="activarCategoria(categoria.id)">
                                                <i class="icon-check"></i>
                                            </button>
                                        </template>
                                    </td>
                                    <td> <img v-if="categoria.imagen" :src="`img/categorias/${categoria.imagen}`" width="50px" alt=""></td>
                                    <td :style="{'padding-left': ((categoria.profundidad || 0) * 20 + 12) + 'px'}">
                                        <span v-if="categoria.padre_id" class="text-info font-weight-bold mr-1">↳</span>
                                        <strong :class="{'text-primary': !categoria.padre_id}">{{ categoria.nombre }}</strong>
                                    </td>
                                    <td>
                                        <span v-if="categoria.padre" class="badge badge-info"><i class="fa fa-folder-open-o"></i> {{ categoria.padre.nombre }}</span>
                                        <span v-else class="badge badge-secondary"><i class="fa fa-home"></i> Categoría Raíz</span>
                                    </td>
                                    <td v-text="categoria.descripcion"></td>
                                    <td v-text="categoria.condicion">                                   
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                         <nav>
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
            <!--Inicio del modal agregar/actualizar-->
            <div class="modal fade" :class="{'mostrar': modal}"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
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
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group row col-md-6">
                                        <label class="col-md-3 form-control-label" for="text-input">Nombre</label>
                                        <div class="col-md-9">
                                            <input type="text" v-model="category.nombre" class="form-control" placeholder="Nombre de categoría">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group row col-md-6">
                                        <label class="col-md-3 form-control-label" for="text-input">Posición</label>
                                        <div class="col-md-6">
                                            <input type="text" v-model="category.condicion" class="form-control" placeholder="Posición en el menú 1 para primera posición">
                                        </div>
                                    </div>
                                    <div class="form-group row col-md-6">
                                        <label class="col-md-3 form-control-label" for="padre-input">Categoría Padre</label>
                                        <div class="col-md-9">
                                            <select class="form-control" v-model="category.padre_id">
                                                <option :value="null">Ninguna (Es raíz)</option>
                                                <option v-for="padre in arrayPadres" :key="padre.id" :value="padre.id" v-text="padre.nombre"></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                <div class="form-group row">
                                    <label class="col-md-1 form-control-label" for="email-input">Descripción</label>
                                    <div class="col-md-9">
                                        <input type="email" v-model="category.descripcion" class="form-control" placeholder="Ingrese descripción">
                                    </div>
                                </div>
                                <div v-show="errorCategoria" class="from-group row div-error">
                                    <div class="text-center text-error">
                                        <div v-for="error in errorMostrarMsjCategoria" :key="error" v-text="error"></div>
                                    </div>
                                </div>
                                <div class="product-upload">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Imagen Cuadrada / Círculo</h6>
                                            <input type="file" @change="handleImageChange" accept="image/*" class="form-control-file mb-2" />
                                            <img v-if="category.imagen" :src="`img/categorias/${category.imagen}`" width="100px" alt="">
                                            <div v-if="preview" class="preview-container mt-2">
                                                <p class="mb-1 text-muted">Vista previa:</p>
                                                <img :src="preview" alt="Preview" class="preview-img border" width="100"/>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Banner Presentación (Página de Categoría)</h6>
                                            <input type="file" @change="handleBannerChange" accept="image/*" class="form-control-file mb-2" />
                                            <img v-if="category.banner" :src="`img/categorias/${category.banner}`" width="150px" alt="">
                                            <div v-if="previewBanner" class="preview-container mt-2">
                                                <p class="mb-1 text-muted">Vista previa Banner:</p>
                                                <img :src="previewBanner" alt="Preview Banner" class="preview-img border" width="150"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>                        
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                            <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarCategoria()">Guardar</button>
                            <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click='actualizarCategoria()'>Actualizar</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- Modal Importar Word / TXT -->
            <div class="modal fade" :class="{'mostrar': modalWord}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-info modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Importar Estructura de Categorías (.docx o .txt)</h4>
                            <button type="button" class="close" @click="cerrarModalWord()" aria-label="Close">
                              <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                Puedes subir un archivo de Word (<code>.docx</code>) o de texto plano (<code>.txt</code>). El sistema interpreta la jerarquía mediante cualquiera de estas 3 formas:
                            </p>
                            <ul class="small text-muted mb-3 pl-3">
                                <li><strong>1. Tabulaciones o Sangría:</strong> Tecla <kbd>Tab</kbd> o sangría al inicio de cada línea.</li>
                                <li><strong>2. Símbolos explícitos de nivel:</strong> Usa <code>></code> o <code>-></code> (Ej: <code>Categoría Raíz</code>, <code>> Subcategoría 1</code>, <code>>> Subcategoría 2</code>).</li>
                                <li><strong>3. Numeración decimal:</strong> Ej: <code>1. Categoría</code>, <code>1.1 Subcategoría</code>, <code>1.1.1 Sub-subcategoría</code>.</li>
                            </ul>
                            <div class="form-group border p-3 rounded bg-light">
                                <label class="font-weight-bold d-block mb-2">Seleccionar archivo (.docx o .txt):</label>
                                <input type="file" ref="fileWordInput" @change="onFileWordChange" accept=".docx,.txt" class="form-control-file" />
                                <small v-if="selectedWordFile" class="text-success font-weight-bold d-block mt-2">
                                    <i class="fa fa-check-circle"></i> Archivo seleccionado: {{ selectedWordFile.name }}
                                </small>
                            </div>
                            <div class="form-group border p-3 rounded bg-light mt-3">
                                <label class="font-weight-bold d-block mb-2"><i class="fa fa-question-circle"></i> Categorías que NO aparezcan en el archivo Word:</label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="optEliminar" value="eliminar" class="custom-control-input" v-model="accionNoIncluidas">
                                    <label class="custom-control-label text-danger font-weight-bold" for="optEliminar">
                                        <i class="fa fa-trash"></i> Eliminar categorías que no estén en el documento Word (Dejar exactamente como el archivo)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="optConservar" value="conservar" class="custom-control-input" v-model="accionNoIncluidas">
                                    <label class="custom-control-label text-success font-weight-bold" for="optConservar">
                                        <i class="fa fa-check-circle"></i> Conservar las categorías existentes
                                    </label>
                                </div>
                            </div>
                            <div v-if="cargandoWord" class="text-center py-3">
                                <i class="fa fa-spinner fa-spin fa-2x text-info mb-2"></i>
                                <p class="font-weight-bold text-info">Procesando y reconfigurando categorías...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModalWord()" :disabled="cargandoWord">Cancelar</button>
                            <button type="button" class="btn btn-info text-white font-weight-bold" @click="subirArchivoWord()" :disabled="!selectedWordFile || cargandoWord">
                                <i class="fa fa-upload"></i> Subir e Importar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--Fin del modal-->
            
        </main>
</template>

<script>
    export default {
        data(){
            return{
                category:{},
                selectedFile: null,
                preview: null,
                selectedBanner: null,
                previewBanner: null,
                categoria_id:0,
                updateid:0,
                nombre:'',
                posicion:1,
                descripcion:'',
                arrayCategoria:[],
                arrayPadres:[],
                arrayArticulos:[],
                modal:0,
                modala:0,
                modalWord:0,
                selectedWordFile: null,
                cargandoWord: false,
                accionNoIncluidas: 'eliminar',
                tituloModal:'',
                tipoAccion:0,
                errorCategoria:0,
                errorMostrarMsjCategoria:[],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : 'nombre',
                buscar : ''
            }
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
        methods: {
            handleImageChange(event) {
                const file = event.target.files[0]
                if (file) {
                    this.selectedFile = file
                    this.preview = URL.createObjectURL(file)
                }
            },
            handleBannerChange(event) {
                const file = event.target.files[0]
                if (file) {
                    this.selectedBanner = file
                    this.previewBanner = URL.createObjectURL(file)
                }
            },
            selectCategoria() {
                let me=this;
                var url= '/categoria/selectCategoria';
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPadres = respuesta.categorias;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            
            listarCategorias(page, buscar, criterio){
                let me=this
                var url= '/categoria?page=' + page +'&buscar='+buscar+'&criterio='+criterio;
                axios.get(url)
                .then(function (response) {
                    // handle success
                    var respuesta= response.data;
                    me.arrayCategoria = respuesta.categorias.data;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });
            },
            cambiarPagina(page, buscar, criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarCategorias(page, buscar, criterio);
            },
            async uploadImage() {
                const formData = new FormData()
                formData.append('imagen', this.selectedFile)

                try {
                    const response = axios.post('/categoria/registrar', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    })
                    alert('Imagen subida correctamente')
                    console.log(response.data)
                    // Opcional: resetear inputs
                    this.selectedFile = null
                    this.preview = null
                } catch (error) {
                    console.error('Error al subir imagen:', error)
                    alert('❌ Error al subir imagen')
                }
            },
            registrarCategoria(){
                if(this.validarCategoria()){
                    return;
                }

                let me =this
                const formData = new FormData()
                if (this.selectedFile) formData.append('imagen', this.selectedFile)
                if (this.selectedBanner) formData.append('banner', this.selectedBanner)
                formData.append('id', me.category.id)
                formData.append('nombre', me.category.nombre)
                if (me.category.imagen) formData.append('imagen2', me.category.imagen)
                if (me.category.banner) formData.append('banner2', me.category.banner)
                formData.append('descripcion', me.category.descripcion)
                formData.append('condicion', me.category.condicion)
                if (me.category.padre_id) {
                    formData.append('padre_id', me.category.padre_id)
                }
                axios.post('/categoria/registrar', formData)
                .then(function (response) {
                    me.cerrarModal()
                    me.listarCategorias(1,'','nombre')
                    
                })
                .catch(function(error){
                    console.log(error)
                });
            },
            actualizarCategoria(){
               if(this.validarCategoria()){
                    return;
                }
                let me =this
                const formData = new FormData()
                if (this.selectedFile) formData.append('imagen', this.selectedFile)
                if (this.selectedBanner) formData.append('banner', this.selectedBanner)
                formData.append('_method', 'PUT')
                formData.append('id', me.category.id)
                if (me.category.imagen) formData.append('imagen2', me.category.imagen)
                if (me.category.banner) formData.append('banner2', me.category.banner)
                formData.append('nombre', me.category.nombre)
                formData.append('descripcion', me.category.descripcion || '')
                formData.append('condicion', me.category.condicion)
                formData.append('padre_id', me.category.padre_id ? me.category.padre_id : 0)
                axios.post('/categoria/actualizar',  formData)
                .then(function (response) {
                    me.cerrarModal()
                    me.preview=null
                    me.listarCategorias(1,'','nombre')
                    me.selectCategoria();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Categoría actualizada!',
                        text: 'La categoría se ha actualizado correctamente.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                })
                .catch(function(error){
                    console.log(error);
                    Swal.fire('Error', 'No se pudo actualizar la categoría.', 'error');
                }); 
            },
            
            articulosCategoria(id){
                let me = this
                me.categoria_id=id
                var url= '/categoria/articulosCategoria?id='+ id;
                axios.get(url)
                .then(function (response) {
                    me.arrayArticulos=response.data.articulos
                    me.modala=1
                })
                .catch(function(error){
                    console.log(error)
                }); 
            },
            cerrarModala(){
                this.modala=0
                this.arrayArticulos=[]
                this.categoria_id=0
                this.updateid=0
            },
           
            eliminarCategoria(id){
                let me = this
                var url= '/categoria/eliminarC?id='+ me.categoria_id;
                axios.delete(url,{'_method': 'DELETE'})
                .then(function (response) {
                    me.listarCategorias(1,'','nombre')
                    me.cerrarModala()
                })
                .catch(function(error){
                    console.log(error)
                }); 
            },
            activarCategoria(id){
                const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de activar esta categoria?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.isConfirmed) {
                    let me =this
                    axios.put('/categoria/activar', {
                        'id':id
                    })
                    .then(function (response) {
                        me.listarCategorias(1,'','nombre')
                    })
                    .catch(function(error){
                        console.log(error)
                    }); 
                    swalWithBootstrapButtons.fire(
                    'Activada',
                    'El registro ha sido activo con exito',
                    'success'
                    )
                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                   
                }
                })
            },
            validarCategoria(){
                this.errorCategoria=0
                this.errorMostrarMsjCategoria=[]
                if(!this.category.nombre) this.errorMostrarMsjCategoria.push('El nombre de la categoría no puede estar vacío')
                if(this.errorMostrarMsjCategoria.length) this.errorCategoria=1
                return this.errorCategoria
            },
            cerrarModal(){
                this.modal=0
                this.tituloModal=''
                this.nombre=''
                this.descripcion=''
                this.posicion=1
            },
            abrirModal(modelo, accion, categoria){
                switch(modelo){
                    case "categoria":
                        {
                            switch(accion){
                                case 'registrar':
                                    {
                                        this.category={id:0,nombre:'',descripcion:'',condicion:1,padre_id:null}
                                        this.preview=null;
                                        this.previewBanner=null;
                                        this.selectedFile=null;
                                        this.selectedBanner=null;
                                        this.descripcion='';
                                        this.tituloModal='Registrar categoría',
                                        this.modal=1,
                                        this.tipoAccion=1
                                        break;
                                    }
                                case 'actualizar':
                                    {
                                        this.preview=null;
                                        this.previewBanner=null;
                                        this.selectedFile=null;
                                        this.selectedBanner=null;
                                        this.category = {
                                            id: categoria['id'],
                                            nombre: categoria['nombre'],
                                            descripcion: categoria['descripcion'],
                                            condicion: categoria['condicion'],
                                            padre_id: categoria['padre_id'],
                                            imagen: categoria['imagen'],
                                            banner: categoria['banner']
                                        };
                                        this.tituloModal='Actualizar categoría';
                                        this.modal=1;
                                        this.tipoAccion=2;
                                        break;
                                    }
                            }
                        }
                }
            },
            abrirModalWord() {
                this.selectedWordFile = null;
                this.cargandoWord = false;
                this.modalWord = 1;
            },
            cerrarModalWord() {
                this.modalWord = 0;
                this.selectedWordFile = null;
                this.cargandoWord = false;
            },
            onFileWordChange(event) {
                const file = event.target.files[0];
                if (file) {
                    const name = file.name.toLowerCase();
                    if (!name.endsWith('.docx') && !name.endsWith('.txt')) {
                        Swal.fire('Error', 'Por favor selecciona un archivo con extensión .docx o .txt', 'error');
                        return;
                    }
                    this.selectedWordFile = file;
                }
            },
            subirArchivoWord() {
                if (!this.selectedWordFile) return;

                let me = this;

                const ejecutarImportacion = () => {
                    me.cargandoWord = true;

                    const formData = new FormData();
                    formData.append('archivo_word', me.selectedWordFile);
                    formData.append('accion_no_incluidas', me.accionNoIncluidas);

                    axios.post('/categoria/importarWord', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    })
                    .then(function (response) {
                        me.cargandoWord = false;
                        me.cerrarModalWord();
                        me.listarCategorias(1, '', 'nombre');
                        me.selectCategoria();

                        Swal.fire({
                            icon: 'success',
                            title: '¡Estructura Importada!',
                            text: (response.data && response.data.message) || 'Se ha reconfigurado la jerarquía de categorías correctamente.'
                        });
                    })
                    .catch(function (error) {
                        me.cargandoWord = false;
                        let msg = (error.response && error.response.data && error.response.data.message) 
                            ? error.response.data.message 
                            : 'Ocurrió un error al procesar el archivo Word.';
                        Swal.fire('Error de Importación', msg, 'error');
                    });
                };

                if (this.accionNoIncluidas === 'eliminar') {
                    Swal.fire({
                        title: '¿Eliminar categorías no incluidas?',
                        text: 'Se ELIMINARÁN todas las categorías del sistema que NO aparezcan en este documento Word. ¿Deseas continuar?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar no incluidas',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            ejecutarImportacion();
                        }
                    });
                } else {
                    ejecutarImportacion();
                }
            }
        },  
        mounted() {
            this.listarCategorias(1,this.buscar,this.criterio);
            this.selectCategoria();
        }
    }
</script>
<style>
    .modal-content{
        width:100% !important;
        position:absolute !important;
    }
    .mostrar{
        display:list-item !important;
        opacity:1 !important;
        position:absolute !important;
        background-color:#3c29297a !important;
    }
    .div-error{
        display:flex;
        justify-content: center;
    }
    .text-error{
        color:red;
        font-weight: bold;
    }
</style>
