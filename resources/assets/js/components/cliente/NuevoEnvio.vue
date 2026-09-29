<template>
    <div class="contenedor">
        <div class="seccion" role="document">
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Datos Envio</div>
                    <div class="subseccion">
                        <button class="btn btn-danger" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary">Guardar y nuevo</button>
                        <button class="btn btn-success" @click="registrarEnvio()">Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Envio
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Persona que recibe</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.contacto" class="form-control" placeholder="Nombre contacto">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Nombre del negocio</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.empresa" class="form-control" placeholder="Nombre del negocioo empresa">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="text-input">Tipo documento</label>
                                <div class="col-md-9">
                                    <select v-model="envio.tipo_documento" class="form-control">
                                        <option value="NIT">NIT</option>
                                        <option value="CC">CC</option>
                                        <option value="PASAPORTE">PASAPORTE</option>
                                    </select>  
                                </div>  
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Número de documento</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.documento" class="form-control" placeholder="Número de documento">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">teléfono</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.telefono" class="form-control" placeholder="Teléfono">
                                </div>
                            </div>
                            
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Dirección destino</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.direccion" class="form-control" placeholder="Dirección destino">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Ciudad destino</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.ciudad" class="form-control" placeholder="Ciudad destino">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">País destino</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="envio.pais" class="form-control" placeholder="País destino">
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
                        <button class="btn btn-link" @click="nuevoEnvio('nuevo')">Nuevo</button>
                    </div>
                    <div v-if="empresaformulario==1">
                        <button @click="nuevoEnvio('')" class="btn btn-danger float-right">X</button>
                        <formempresa @valorFormulario="llenarformulario"></formempresa>
                    </div>
                    <div v-else-if="empresaformulario==2">
                        <div class="seccion-body">
                            <div class="form-group">
                                        
                                <label>buscar cuenta por nombre(*) <span style="color:red">(*Seleccione)</span>  </label>
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
                            <tbody v-if="envio.clientes.length>0">
                                <tr v-for="(cli,index) in envio.clientes" :key="index">
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
                    <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarCuenta()">Guardar</button>
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
        props:['envio'],
        data (){
            return {
                arrayCuentas:[],
                empresaformulario:2,
                show:'envios',
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
                this.envio.clientes.push(cuenta)
                this.modalc=0
            },
            nuevoCliente(action){
                if(action=='nuevo'){
                    this.clienteformulario=1;
                }else{
                    this.clienteformulario=2;
                }
            },
            registrarEnvio(){
                if (this.validarPersona()){
                    return;
                }
                
                let me = this;
                const envio = new FormData()
                if(this.envio.id){
                    envio.set('id',this.envio.id)
                }else{
                    envio.set('id',0)
                }
                envio.set('contacto',this.envio.contacto)
                envio.set('empresa',this.envio.empresa)
                envio.set('tipo_documento' , this.envio.tipo_documento)
                envio.set('documento', this.envio.documento)
                envio.set('telefono' , this.envio.telefono)
                envio.set('direccion' , this.envio.direccion)
                envio.set('ciudad',this.envio.ciudad)
                envio.set('pais',this.envio.pais)
                envio.set('clientes',JSON.stringify(this.envio.clientes))
                axios.post('/envio/registrar',envio).then(function (response) {
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
                if (!this.envio.contacto) this.errorMostrarMsjPersona.push("El nombre de la persona que recibe no puede estar vacío.");
                if (!this.envio.direccion) this.errorMostrarMsjPersona.push("La dirección de destino no puede estar vacía.");
                if (!this.envio.ciudad) this.errorMostrarMsjPersona.push("La ciudad de destino no puede estar vacía.");
                
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
