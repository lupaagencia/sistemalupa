<template>
    <div class="contenedor">
        <div class="seccion" role="document">
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Contacto</div>
                    <div class="subseccion">
                        <button class="btn btn-danger" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary">Guardar y nuevo</button>
                        <button class="btn btn-success" @click="registrarEmpresa()">Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos de facturacion
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Razón social</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.razonsocial" class="form-control" placeholder="Nombre de la empresa">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="text-input">Tipo persona</label>
                                <div class="col-md-9">
                                    <select v-model="empresa.tipo_persona" class="form-control">
                                        <option value="Natural">Natural</option>
                                        <option value="Juridica">Jurídica</option>
                                    </select>   
                                </div> 
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="text-input">Tipo documento</label>
                                <div class="col-md-9">
                                    <select v-model="empresa.tipo_documento" class="form-control">
                                        <option value="NIT">NIT</option>
                                        <option value="CC">CC</option>
                                        <option value="PASAPORTE">PASAPORTE</option>
                                    </select>  
                                </div>  
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Número de documento</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.numero" class="form-control" placeholder="Número de documento">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Dígito de verificación</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.digito" class="form-control" placeholder="Dígito de verificación">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Dirección</label>
                                <div class="col-md-9">
                                    <input type="email" v-model="empresa.direccion" class="form-control" placeholder="Dirección">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Telefono de la empresa</label>
                                <div class="col-md-9">
                                    <input type="email" v-model="empresa.telefono" class="form-control" placeholder="Telefono">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Correo electrónico</label>
                                <div class="col-md-9">
                                    <input type="email" v-model="empresa.correo" class="form-control" placeholder="Correo electrónico">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Ciudad</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.ciudad" class="form-control" placeholder="Ciudad">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Departamento</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.departamento" class="form-control" placeholder="Departamento">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">País</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.pais" class="form-control" placeholder="pais">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Número Actividad económica</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="empresa.actividad" class="form-control" placeholder="Número de la actividad económica">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3  form-control-label" for="text-input">Respocable de IVA</label>
                                <div class="col-md-9">
                                    <select v-model="empresa.responsable" class="form-control" placeholder="Selecione si es resposable de IVA">
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>    
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
                            <tbody v-if="empresa.clientes.length>0">
                                <tr v-for="(cli,index) in empresa.clientes" :key="index">
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
        props:['empresa'],
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
                this.empresa.clientes.push(cuenta)
                this.modalc=0
            },
            nuevoCliente(action){
                if(action=='nuevo'){
                    this.clienteformulario=1;
                }else{
                    this.clienteformulario=2;
                }
            },
            registrarEmpresa(){
                if (this.validarPersona()){
                    return;
                }
                
                let me = this;
                const empresa = new FormData()
                if(this.empresa.id){
                    empresa.set('id',this.empresa.id)
                }else{
                    empresa.set('id',0)
                }
                empresa.set('razonsocial',this.empresa.razonsocial)
                empresa.set('tipo_persona',this.empresa.tipo_persona)
                empresa.set('tipo_documento' , this.empresa.tipo_documento)
                empresa.set('numero', this.empresa.numero)
                empresa.set('digito' , this.empresa.digito)
                empresa.set('direccion' , this.empresa.direccion)
                empresa.set('telefono',this.empresa.telefono)
                empresa.set('correo',this.empresa.correo)
                empresa.set('ciudad',this.empresa.ciudad)
                empresa.set('departamento',this.empresa.departamento)
                empresa.set('pais',this.empresa.pais)
                empresa.set('actividad',this.empresa.actividad)
                empresa.set('responsable',this.empresa.responsable)
                empresa.set('clientes',JSON.stringify(this.empresa.clientes))
                axios.post('/facturacion/registrar',empresa).then(function (response) {
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
                if (!this.empresa.razonsocial) this.errorMostrarMsjPersona.push("El nombre de la persona no puede estar vacío.")
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
