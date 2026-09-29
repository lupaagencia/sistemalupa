<template>
    <div>
        <div v-if="seccion=='listado'">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> Cuentas
                <button type="button" @click="abrirModal('nuevo')" class="btn btn-success boton-principal">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
            </div>
                
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-control col-md-3" v-model="criterio">
                            <option value="razonsocial">Nombre</option>
                            <option value="telefono">Teléfono</option>
                            <option value="ciudad">Ciudad</option>
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
                            <th>Contacto</th>
                            <th>Facturación</th>
                            <th>Envio</th>
                            <th>Ciudad</th>
                            <th>Sitio Web</th>
                            <th>Redes Sociales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="persona in arrayCuentas" :key="persona.id">
                            <td>
                                <button type="button" @click="abrirModal('editar',persona)" class="btn btn-warning btn-sm">
                                <i class="icon-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" @click="eliminarCuenta(persona.id)">
                                <i class="icon-trash"></i>
                                </button>
                            </td>
                            <td v-text="persona.razonsocial"></td>
                            <td ><ul>
                                <li v-for="(contacto,index) in persona.contactos" :key="index">{{contacto.nombre}}</li>
                                </ul></td>
                            <td ><ul>
                                <li v-for="(empresa,index) in persona.empresas" :key="index">{{empresa.razonsocial}}</li>
                                </ul></td>
                            <td ><ul>
                                <li v-for="(envio,index) in persona.envios" :key="index">{{envio.contacto}}</li>
                                </ul></td>
                            <td v-text="persona.ciudad"></td>
                            <td v-text="persona.sitio_web"></td>
                            <td v-text="persona.redes_sociales"></td>
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
            <nuevacuenta :cuenta="cuenta" @mostrarListado="mostrarCuentas" @verContacto="mostrarTab"></nuevacuenta>
         </div>   
         <div v-else>
            <nuevacuenta :cuenta="cuenta" @mostrarListado="mostrarCuentas" @verContacto="mostrarTab"></nuevacuenta>
         </div>   
    </div>
</template>

<script>
    import nuevacuenta from './NuevaCuenta'
    export default {
        data (){
            return {
                seccion:'listado',
                show:'cuentas',
                arrayCuentas : [],
                cuenta:{contactos:[],empresas:[],envios:[],'telefono':0,'departamento':'Valle del Cauca','pais':'Colombia'},
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
                criterio : 'razonsocial',
                buscar : ''
            }
        },
        components:{
            nuevacuenta
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
            listarPersona (page,buscar,criterio){
                let me=this;
                var url= '/cliente?page='+page+'&buscar='+buscar+'&criterio='+criterio
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayCuentas = respuesta.personas.data;
                    me.pagination = respuesta.pagination;
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
           
            eliminarDatose(val){
                var me=this
                 var url= '/datosenvio/borrar?id='+ val+ '&_method=DELETE';
                    axios.delete(url).then(function (response) {
                    var respuesta= response.data;
                    me.datosenvio=[{'id':0,'idcliente':0,'contacto':'','empresa':'','tipo_documento':'CC','documento':0,'direccion':'','telefono':'','ciudad':'','pais':'Colombia'}]
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
           
          
            abrirModal(accion, data){
                switch(accion){
                    case 'nuevo':
                    {
                        this.cuenta = {contactos:[],empresas:[],envios:[],'telefono':0,'departamento':'Valle del Cauca','pais':'Colombia'};
                        this.seccion='nuevo';
                        break;
                    }
                    case 'editar':
                    {
                        this.seccion='editar'
                        this.cuenta=data
                        break;
                       
                    }
                }
            },
            mostrarCuentas(value){
                this.cuenta={contactos:[],empresas:[],envios:[],'departamento':'Valle del Cauca','pais':'Colombia'};
                this.seccion=value;
                this.listarPersona(1,this.buscar,this.criterio);
            },
            eliminarCuenta(id){
               Swal.fire({
                title: 'Esta seguro de eliminar esta cuenta? Esto podria afectar otros registros vinculados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.isConfirmed) {
                    let me =this
                    axios.delete('/cliente/eliminar?id='+id)
                    .then(function (response) {
                        me.listarPersona(me.pagination.current_page,me.buscar,me.criterio)
                        Swal.fire(
                            'Eliminado',
                            'La cuenta ha sido eliminada con exito',
                            'success'
                        )
                    })
                    .catch(function(error){
                        let msg = 'Ocurrió un error al intentar eliminar el registro.';
                        if (error.response && error.response.data && error.response.data.error) {
                            let data = error.response.data;
                            msg = `<b>${data.error}</b><br><br>`;
                            if (data.detalles) msg += `<small>${data.detalles}</small><br><br>`;
                            if (data.solucion) msg += `<p style="color: #87189D;">${data.solucion}</p>`;
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Atención',
                                html: msg,
                                showCancelButton: true,
                                confirmButtonText: 'Reasignar y Eliminar',
                                cancelButtonText: 'Cerrar',
                                confirmButtonColor: '#87189D'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    me.dialogoReasignar(id);
                                }
                            });
                        } else if (error.response && error.response.status == 500) {
                            msg = '<b>Error interno del servidor (500)</b><br>No se pudo procesar la solicitud. Por favor, contacte al soporte técnico.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Atención',
                                html: msg
                            });
                        }
                        console.log(error);
                    }); 
                }
                })
            },
            dialogoReasignar(id_origen){
                let me = this;
                Swal.fire({
                    title: 'Seleccione el cliente destino',
                    text: 'Todos los registros se moverán al nuevo cliente seleccionado.',
                    input: 'text',
                    inputPlaceholder: 'Nombre del cliente...',
                    showCancelButton: true,
                    confirmButtonText: 'Buscar',
                    cancelButtonText: 'Cancelar'
                }).then((res) => {
                    if (res.isConfirmed && res.value) {
                        axios.get('/cliente/selectCliente?filtro=' + res.value)
                        .then(function(response){
                            let clientes = response.data.clientes;
                            let options = {};
                            clientes.forEach(c => {
                                if(c.id != id_origen) options[c.id] = c.nombre;
                            });

                            if(Object.keys(options).length == 0){
                                Swal.fire('Error', 'No se encontraron otros clientes con ese nombre.', 'error');
                                return;
                            }

                            Swal.fire({
                                title: 'Seleccione el destino final',
                                input: 'select',
                                inputOptions: options,
                                inputPlaceholder: 'Seleccione un cliente',
                                showCancelButton: true,
                                inputValidator: (value) => {
                                    return new Promise((resolve) => {
                                        if (value) resolve();
                                        else resolve('Debe seleccionar un cliente');
                                    });
                                }
                            }).then((selection) => {
                                if (selection.isConfirmed) {
                                    axios.post('/cliente/reasignar', {
                                        'id_origen': id_origen,
                                        'id_destino': selection.value
                                    }).then(function(resp){
                                        me.listarPersona(1, me.buscar, me.criterio);
                                        Swal.fire('Éxito', 'Registros reasignados y cuenta eliminada correctamente.', 'success');
                                    }).catch(function(err){
                                        let msg = 'No se pudo completar la reasignación.';
                                        if (err.response && err.response.data && err.response.data.error) {
                                            msg = err.response.data.error;
                                        }
                                        Swal.fire('Error', msg, 'error');
                                    });
                                }
                            });
                        });
                    }
                });
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
   
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
</style>
