<template>
    <div class="contenedor">
        <div class="seccion" role="document">
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Contacto</div>
                    <div class="subseccion">
                        <button class="btn btn-danger" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary">Guardar y nuevo</button>
                        <button class="btn btn-success" @click="registrarContacto()">Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Cliente
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre del contacto</label>
                                <div class="col-md-9">
                                    <input type="text"  v-model="contacto.nombre" class="form-control" placeholder="Nombre de la contacto">                                        
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Teléfono</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.telefono" class="form-control" placeholder="Teléfono">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Teléfono Personal</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.telefono_particular" class="form-control" placeholder="Número de telefono personal">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="input">Correo</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.correo" class="form-control" placeholder="Correo electrónico">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Cargo</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.cargo" class="form-control" placeholder="Cargo">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Nombre de asistente</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.nombre_asistente" class="form-control" placeholder="Nombre asistente">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">teléfono asistente</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.telefono_asistente" class="form-control" placeholder="Número de teléfono asistente">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Fecha de nacimiento</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto.fecha_nacimiento" class="form-control" placeholder="Fecha de nacimiento">
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
                </div>
                <div class="contenedor-seccion">
                    <div class="contenedor-header">
                        <div class="titulo">Cuentas</div>
                        <button class="btn btn-link" @click="nuevaCuenta('nuevo')">Nuevo</button>
                    </div>
                    <div v-if="empresaformulario==1">
                        <button @click="nuevaCuenta('')" class="btn btn-danger float-right">X</button>
                        <formempresa @valorFormulario="llenarformulario"></formempresa>
                    </div>
                    <div v-else-if="empresaformulario==2">
                        <div class="seccion-body">
                            <div class="form-group">
                                        
                                <label>buscar contacto por nombre(*) <span style="color:red">(*Seleccione)</span>  </label>
                                <div class="form-group">
                                    <div class="">
                                        <div class="form-inline">
                                            <input type="text" class="form-control" v-model="buscar_cliente" @keyup="selectCuenta()" placeholder="Ingrese nombre del cliente">
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalc}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                <div class="modal-dialog" >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seleccione un Cuenta</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalc()">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <template v-if="arrayCuentas">
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    v-for="(cuenta,index) in arrayCuentas" 
                                                    :key="index" 
                                                    v-text="cuenta.razonsocial"
                                                    @click="getDatosCuenta(cuenta,index)">
                                                    </a> 
                                                </div>
                                            </template>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" @click="cerrarModalc()" data-dismiss="modal">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                        </div>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table class="table">
                            <thead>
                                <tr>
                                <th scope="col">Cuenta</th>
                                <th scope="col">Ciudad</th>
                                <th scope="col">Sitio Web</th>
                                <th scope="col">Redes sociales</th>
                                <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="contacto.clientes.length>0">
                                <tr v-for="(cli,index) in contacto.clientes" :key="index">
                                    <th scope="row" ><button v-text="cli.razonsocial" @click="viewCuenta(cli)"></button></th>
                                    <td v-text="cli.ciudad"></td>
                                    <td v-text="cli.sitio_web"></td>
                                    <td v-text="cli.redes_sociales"></td>
                                    <td> <button type="button" class="btn btn-danger btn-sm" @click="eliminarCuenta(cli,index)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                            </tbody>
                            <tbody v-else>
                                <tr><td colspan="4" style="color:rgb(145, 145, 145); font-weight: bold;">No se encontraron registros</td></tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
   
                    
                <div class="contenedor-footer">
                    <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                    <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarPersona()">Guardar</button>
                    <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarPersona()">Actualizar</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>
<script>
    export default {
        props:['contacto'],
        data (){
            return {
                arrayCuentas:[],
                empresaformulario:2,
                show:'contactos',
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                errorPersona : 0,
                errorMostrarMsjPersona : [],
                verificarnom:0,
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
                buscar_cliente:'',
                modalc:0,
                buscar : '',
            }
        },
        computed:{
           
        },
        methods : {
            selectCuenta(){
                let me=this;
                
                var url= '/cliente/selectClientes?filtro='+this.buscar_cliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    
                    console.log(respuesta)
                    me.arrayCuentas=respuesta.clientes;
                    me.modalc=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosCuenta(cuenta,index){
                this.contacto.clientes.push(cuenta)
                this.modalc=0
            },
            nuevoCliente(action){
                if(action=='nuevo'){
                    this.clienteformulario=1;
                }else{
                    this.clienteformulario=2;
                }
            },
            registrarContacto(){
                if (this.validarPersona()){
                    return;
                }
                
                let me = this;
                const contacto = new FormData()
                if(this.contacto.id){
                    contacto.set('id',this.contacto.id)
                }else{
                    contacto.set('id',0)
                }
                contacto.set('nombre',this.contacto.nombre)
                contacto.set('telefono',this.contacto.telefono)
                contacto.set('telefono_particular' , this.contacto.telefono_particular)
                contacto.set('correo', this.contacto.correo)
                contacto.set('cargo' , this.contacto.cargo)
                contacto.set('nombre_asistente' , this.contacto.nombre_asistente)
                contacto.set('telefono_asistente',this.contacto.telefono_asistente)
                contacto.set('fecha_nacimiento',this.contacto.telefono_asistente)
                contacto.set('clientes',JSON.stringify(this.contacto.clientes))
                axios.post('/contacto/registrar',contacto).then(function (response) {
                    console.log(response)
                    me.cerrarModal();
                }).catch(function (error) {
                    console.log(error);
                });
            },
            cerrarModalc(){
                this.modalc=0
                this.arrayCuentas=[]
            },
            viewCuenta(value){
                this.$emit('verCuenta',{'show':'cuentas', 'cli':value })         
            },
            cerrarModal(){
                this.$emit('mostrarListado', 'listado')
            },
            eliminarCuenta(cliente,index){
                let me=this
                if(cliente.id){
                    let me=this
                    var url= '/cuenta/desvincular?id='+ me.contacto.id+'&cliente_id='+cliente.id;
                    axios.delete(url,{'_method': 'DELETE'}).then(function (response) {
                        console.log(response.data)
                    }).catch(function (error) {
                        console.log(error);
                    });
                    me.contacto.clientes.splice(index,1)
                }else{
                    me.contacto.clientes.splice(index,1)
                }
              
            },
           
           
           
             validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];
                if (!this.contacto.nombre) this.errorMostrarMsjPersona.push("El nombre de la persona no puede estar vacío.");
                if (!this.contacto.telefono) this.errorMostrarMsjPersona.push("El número de teléfono no puede estar vacío.");
                
                if (this.errorMostrarMsjPersona.length) this.errorPersona = 1;
                

                return this.errorPersona;
            },           
          
           
            
        },
        mounted() {
        }
    }
</script>
<style>    
    .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
    .mostrar{
        display: block !important;
        opacity: 1 !important;
        position: fixed !important;
        background-color: #3c29297a !important;
        overflow-y: auto;
    }
    .modal-body {
        max-height: 400px;
        overflow-y: auto;
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
