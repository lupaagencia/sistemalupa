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
                        <i class="fa fa-align-justify"></i> Usuarios
                        <button type="button" @click="abrirModal('persona','registrar')" class="btn btn-secondary">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                      <option value="nombre">Nombre</option>
                                      <option value="num_documento">Documento</option>
                                      <option value="email">Email</option>
                                      <option value="telefono">Teléfono</option>
                                    </select>
                                    <input type="text" v-model="buscar" @keyup.enter="listarPersona(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                                    <button type="submit" @click="listarPersona(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Opciones</th>
                                    <th>Nombre</th>
                                    <th>Tipo Documento</th>
                                    <th>Número</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Notificar Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="persona in arrayPersona" :key="persona.id">
                                    <td>
                                        <button type="button" @click="abrirModal('persona','actualizar',persona)" class="btn btn-warning btn-sm">
                                          <i class="icon-pencil"></i>
                                        </button>&nbsp;
                                        <template v-if="persona.condicion == 1">
                                            <button type="button" class="btn btn-danger btn-sm" @click="desactivarUsuario(persona.id)">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button type="button" class="btn btn-info btn-sm" @click="activarUsuario(persona.id)">
                                                <i class="icon-check"></i>
                                            </button>
                                        </template>
                                    </td>
                                     <td v-text="persona.nombre"></td>
                                     <td v-text="persona.tipo_documento"></td>
                                     <td v-text="persona.num_documento"></td>
                                     <td v-text="persona.direccion"></td>
                                     <td v-text="persona.telefono"></td>
                                     <td v-text="persona.email"></td>
                                     <td v-text="persona.usuario"></td>
                                     <td v-text="persona.rol ? persona.rol.nombre : (persona.idrol || '')"></td>
                                    <td>
                                        <span v-if="persona.notificar_ventas == 1" class="badge badge-success">Sí</span>
                                        <span v-else class="badge badge-secondary">No</span>
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
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
            <!--Inicio del modal agregar/actualizar-->
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
                                    <label class="col-md-3 form-control-label" for="text-input">Empleado (*)</label>
                                     <div class="form-inline">
                                        <input type="text" class="" v-model="buscar_empleado" @keyup="selectEmpleado(user)" placeholder="Ingrese nombre empleado">
                                    </div>
                                    
                                </div>
                               
                                <div class="form-group row" v-if="user && user.empleado && user.empleado.id">
                                   
                                    <div class="col-md-6">
                                        <div type="text" class="form-control">{{ user.empleado.nombre }} {{ user.empleado.apellido }}</div>                                        
                                    </div>
                                    <div class="col-md-6">
                                        <div type="text" class="form-control">{{ user.empleado.tipo_doc }} {{ user.empleado.num_doc }}</div>                                        
                                    </div>
                                   
                                </div>
                              
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="email-input">Rol (*)</label>
                                    <div class="col-md-9">
                                        <select class="form-control" v-model="user.rol.nombre">
                                            <option value="0">Seleccione un rol</option>
                                            <option v-for="rol in arrayRol" :key="rol.id" :value="rol.nombre" v-text="rol.nombre">

                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="email-input">Usuario (*)</label>
                                    <div class="col-md-9">
                                        <input type="text" v-model="user.usuario" class="form-control" placeholder="Nombre de usuario">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="email-input">Password (*)</label>
                                    <div class="col-md-9">
                                        <input type="password" v-model="user.password" class="form-control" placeholder="Password de acceso (dejar en blanco para conservar actual si actualiza)">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="notificar-input">Notificar Ventas</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" v-model="user.notificar_ventas" :true-value="1" :false-value="0">
                                        <span class="text-muted small ml-2">¿Recibir notificaciones por correo y WhatsApp al crear ventas/pedidos en producción?</span>
                                    </div>
                                </div>

                                <div v-show="errorPersona" class="form-group row div-error">
                                    <div class="text-center text-error">
                                        <div v-for="error in errorMostrarMsjPersona" :key="error" v-text="error">

                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                            <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarPersona(user)">Guardar</button>
                            <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarPersona(user)">Actualizar</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <lempleados :modal="modale" :user="user" :scroll="scroll" :empleados="this.arrayEmpleados" @empleadoSeleccionado="empleadoSeleccionado"></lempleados>
            <!--Fin del modal-->
        </main>
</template>

<script>
    import lempleados from './partes/ListaEmpleados'
    export default {
        data (){
            return {
                arrayEmpleados:[],
                empleado:{nombre:'',apellido:'', tipo_doc:'', num_doc:''},
                persona_id: 0,
                nombre : '',
                tipo_documento : 'DNI',
                num_documento : '',
                direccion : '',
                telefono : '',
                email : '',
                usuario : '',
                password : '',
                idrol : '',
                buscar_empleado:'',
                arrayPersona : [],
                arrayRol : [],
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                errorPersona : 0,
                user:{'empleado':{'id':null,'nombre':'','apellido':'','tipo_doc':'','num_doc':''},'usuario':'','password':'','rol':{'nombre':''},'notificar_ventas':0},
                errorMostrarMsjPersona : [],
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
        components: {
            lempleados,
        
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
            listarPersona (page,buscar,criterio){
                let me=this;
                var url= '/user?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    console.log(respuesta)
                    me.arrayPersona = respuesta.users.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
             selectEmpleado(user){
                let me=this;
                
                var url= '/empleado/selectEmpleados?filtro='+this.buscar_empleado;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    
                    me.arrayEmpleados=respuesta;
                    me.modale=1
                    me.user=user
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            empleadoSeleccionado(value){
                this.user.empleado=value
                
                this.modale=0
               
            },
            selectRol(){
                let me=this;
                var url= '/rol/selectRol';
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayRol = respuesta.roles;
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
            registrarPersona(user){
                let me = this;
                let idrol = (user && user.rol && user.rol.nombre) ? user.rol.nombre : user.idrol;
                let empleado_id = (user && user.empleado && user.empleado.id) ? user.empleado.id : null;

                axios.post('/user/registrar',{
                    'usuario': user.usuario,
                    'password': user.password,
                    'idrol' : idrol,
                    'empleado_id' : empleado_id,
                    'notificar_ventas': user.notificar_ventas,
                }).then(function (response) {
                    if (response.data && response.data.status === 'error') {
                        if (typeof swal === 'function') {
                            swal('Error', response.data.message, 'error');
                        } else {
                            alert(response.data.message);
                        }
                    } else {
                        me.cerrarModal();
                        me.listarPersona(1,'','nombre');
                    }
                }).catch(function (error) {
                    console.log(error);
                    var msg = 'Error al registrar usuario.';
                    if (error.response && error.response.data && error.response.data.message) {
                        msg = error.response.data.message;
                    }
                    if (typeof swal === 'function') {
                        swal('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                });
            },
            actualizarPersona(user){
                let me = this;
                let idrol = (user && user.rol && user.rol.nombre) ? user.rol.nombre : user.idrol;
                let empleado_id = (user && user.empleado && user.empleado.id) ? user.empleado.id : null;

                axios.put('/user/actualizar',{
                    'usuario': user.usuario,
                    'password': user.password,
                    'idrol' : idrol,
                    'empleado' : empleado_id,
                    'empleado_id' : empleado_id,
                    'id': user.id,
                    'notificar_ventas': user.notificar_ventas
                }).then(function (response) {
                    if (response.data && response.data.status === 'error') {
                        if (typeof swal === 'function') {
                            swal('Error', response.data.message, 'error');
                        } else {
                            alert(response.data.message);
                        }
                    } else {
                        me.cerrarModal();
                        me.listarPersona(1,'','nombre');
                    }
                }).catch(function (error) {
                    console.log(error);
                    var msg = 'Error al actualizar usuario.';
                    if (error.response && error.response.data && error.response.data.message) {
                        msg = error.response.data.message;
                    }
                    if (typeof swal === 'function') {
                        swal('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                }); 
            },            
            validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];

                if (!this.empleado.nombre) this.errorMostrarMsjPersona.push("Seleccione una persona");
                if (!this.persona.usuario) this.errorMostrarMsjPersona.push("El nombre de usuario no puede estar vacío.");
                if (!this.persona.password) this.errorMostrarMsjPersona.push("El password no puede estar vacío.");
                if (this.idrol=='') this.errorMostrarMsjPersona.push("Debes seleccionar un rol para el usuario.");

                if (this.errorMostrarMsjPersona.length) this.errorPersona = 1;

                return this.errorPersona;
            },
            cerrarModal(){
                this.modal=0;
                this.tituloModal='';
                this.nombre='';
                this.tipo_documento='DNI';
                this.num_documento='';
                this.direccion='';
                this.telefono='';
                this.email='';
                this.usuario='';
                this.password='';
                this.idrol='';
                this.errorPersona=0;

            },
            abrirModal(modelo, accion, data = []){
                this.selectRol();
                switch(modelo){
                    case "persona":
                    {
                        switch(accion){
                            case 'registrar':
                            {
                                this.modal = 1;
                                this.tituloModal = 'Registrar Usuario';
                                this.nombre= '';
                                this.tipo_documento='DNI';
                                this.num_documento='';
                                this.direccion='';
                                this.telefono='';
                                this.email='';
                                this.usuario='';
                                this.password='';
                                this.idrol='';
                                this.user = {
                                    empleado: { id: null, nombre:'', apellido:'', tipo_doc:'', num_doc:'' },
                                    usuario: '',
                                    password: '',
                                    rol: { nombre:'' },
                                    notificar_ventas: 0
                                };
                                this.tipoAccion = 1;
                                break;
                            }
                            case 'actualizar':
                            {
                                let userCopy = Object.assign({}, data);
                                if (!userCopy.rol) {
                                    userCopy.rol = { nombre: userCopy.idrol || '' };
                                }
                                if (!userCopy.empleado) {
                                    userCopy.empleado = { id: null, nombre:'', apellido:'', tipo_doc:'', num_doc:'' };
                                }
                                userCopy.password = '';
                                this.user = userCopy;
                                this.modal = 1;
                                this.tituloModal = 'Actualizar Usuario';
                                this.tipoAccion = 2;
                                this.persona_id = data['id'];
                                this.nombre = data['nombre'];
                                this.tipo_documento = data['tipo_documento'];
                                this.num_documento = data['num_documento'];
                                this.direccion = data['direccion'];
                                this.telefono = data['telefono'];
                                this.email = data['email'];
                                this.usuario = data['usuario'];
                                this.password = '';
                                this.idrol = data['idrol'];
                                break;
                            }
                        }
                    }
                }
            },
            desactivarUsuario(id){
               const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de desactivar este usuario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/desactivar',{
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1,'','nombre');
                        swal(
                        'Desactivado!',
                        'El registro ha sido desactivado con éxito.',
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
               const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de activar este usuario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
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
            }
        },
        mounted() {
            this.listarPersona(1,this.buscar,this.criterio);
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
</style>
