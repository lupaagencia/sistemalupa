<template>
    <div class="contenedor">
        <div class="seccion" role="document">
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Crear Cuenta</div>
                    <div class="subseccion">
                        <button class="btn btn-danger" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary">Guardar y nuevo</button>
                        <button class="btn btn-success" @click="registrarCuenta()">Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Cliente
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre de la cuenta</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.razonsocial" class="form-control" placeholder="Nombre de la cuenta">                                        
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Teléfono</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.telefono" class="form-control" placeholder="Teléfono">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Direccion</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.direccionf" class="form-control" placeholder="Direccion">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Ciudad</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.ciudad" class="form-control" placeholder="Ciudad">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Departamento</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.departamento" class="form-control" placeholder="Departamento">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="input">Pais</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.pais" class="form-control" placeholder="Pais">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Sitio Web</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.sitio_web" class="form-control" placeholder="Sitio Web">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Redes Sociales</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="cuenta.redes_sociales" class="form-control" placeholder="Redes sociales">
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
                        <div class="titulo">Contactos</div>
                        <button class="btn btn-link" @click="nuevoContacto('nuevo')">Nuevo</button>
                    </div>
                    <div v-if="contactoformulario==1">
                        <button @click="nuevoContacto('')" class="btn btn-danger float-right">X</button>
                        <formcontacto @valorFormulario="llenarformulario"></formcontacto>
                    </div>
                    <div v-else-if="contactoformulario==2">
                        <div class="seccion-body">
                            <div class="form-group">
                                        
                                <label>buscar contacto por nombre(*) <span style="color:red">(*Seleccione)</span>  </label>
                                <div class="form-group">
                                    <div class="">
                                        <div class="form-inline">
                                            <input type="text" class="form-control" v-model="buscar_cliente" @keyup="selectContacto()" placeholder="Ingrese nombre del cliente">
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalc}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                <div class="modal-dialog" >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seleccione un contacto</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalc()">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <template v-if="arrayContactos">
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    v-for="(contacto,index) in arrayContactos" 
                                                    :key="index" 
                                                    v-text="contacto.nombre"
                                                    @click="getDatosContacto(contacto,index)">
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
                                <th scope="col">Nombre de contacto</th>
                                <th scope="col">Telefono</th>
                                <th scope="col">Correo electronico</th>
                                <th scope="col">Cargo</th>
                                <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="cuenta.contactos.length>0">
                                <tr v-for="(cont,index) in cuenta.contactos" :key="index">
                                    <th> <button type="button" class="btn btn-sm"  @click="favoritoAsignar(cont,cuenta.id,'contacto')">
                                            <i class="icon-star" :class="{'favorito' : cont.favorito}"></i>
                                        </button>
                                    </th>
                                    <th scope="row" ><button v-text="cont.nombre" @click="viewContacto(cont)"></button></th>
                                    <td v-text="cont.telefono"></td>
                                    <td v-text="cont.correo"></td>
                                    <td v-text="cont.cargo"></td>
                                    <td> <button type="button" class="btn btn-danger btn-sm" @click="eliminarContacto(cont,index)">
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
                <div class="contenedor-seccion">
                    <div class="contenedor-header">
                        <div class="titulo">Empresa</div>
                        <button class="btn btn-link" @click="nuevoEmpresa('nuevo')">Nuevo</button>
                    </div>
                    <div v-if="empresaformulario==1">
                        <button @click="nuevoEmpresa('')" class="btn btn-danger float-right">X</button>
                        <formempresa @valorFormulario="llenarformulario"></formempresa>
                    </div>
                    <div v-else-if="empresaformulario==2">
                        <div class="seccion-body">
                            <div class="form-group">
                                        
                                <label>buscar empresa por nombre(*) <span style="color:red">(*Seleccione)</span>  </label>
                                <div class="form-group">
                                    <div class="">
                                        <div class="form-inline">
                                            <input type="text" class="form-control" v-model="buscar_empresa" @keyup="selectEmpresa()" placeholder="Ingrese nombre de la empresa">
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modale}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                <div class="modal-dialog" >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seleccione un empresa</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModale()">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <template v-if="arrayEmpresas">
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    v-for="(empresa,index) in arrayEmpresas" 
                                                    :key="index" 
                                                    v-text="empresa.razonsocial"
                                                    @click="getDatosEmpresa(empresa,index)">
                                                    </a> 
                                                </div>
                                            </template>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" @click="cerrarModale()" data-dismiss="modal">Cancelar</button>
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
                                <th scope="col">Razon social</th>
                                <th scope="col">Persona juridica</th>
                                <th scope="col">Documento</th>
                                <th scope="col">Direccion</th>
                                <th scope="col">Telefono</th>
                                <th scope="col">Correo</th>
                                <th scope="col">Responsable</th>
                                <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="cuenta.empresas.length>0">
                                <tr v-for="(cont,index) in cuenta.empresas" :key="index">
                                    <th> <button type="button" class="btn btn-sm"  @click="favoritoAsignar(cont,cuenta.id,'empresa')">
                                            <i class="icon-star" :class="{'favorito' : cont.favorito}"></i>
                                        </button>
                                    </th>
                                    <th scope="row" v-text="cont.razonsocial"></th>
                                    <td v-text="cont.tipo_persona"></td>
                                    <td>{{cont.tipodocumento}}  {{cont.numero}}-{{cont.digito}}</td>
                                    <td >{{cont.direccion}},{{cont.pais}},{{cont.departamento}},{{cont.ciudad}}</td>
                                    <td v-text="cont.telefono"></td>
                                    <td v-text="cont.correo"></td>
                                    <td v-text="cont.responsable"></td>
                                    <td> <button type="button" class="btn btn-danger btn-sm" @click="eliminarEmpresa(cont,index)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                            </tbody>
                            <tbody v-else>
                                <tr><td colspan="9" style="color:rgb(145, 145, 145); font-weight: bold;">No se encontraron registros</td></tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="contenedor-seccion">
                    <div class="contenedor-header">
                        <div class="titulo">Datos envio</div>
                        <button class="btn btn-link" @click="nuevoEnvio('nuevo')">Nuevo</button>
                    </div>
                    <div v-if="envioformulario==1">
                        <button @click="nuevoEnvio('')" class="btn btn-danger float-right">X</button>
                        <formenvio @valorFormulario="llenarformulario"></formenvio>
                    </div>
                    <div v-else-if="envioformulario==2">
                        <div class="seccion-body">
                            <div class="form-group">
                                        
                                <label>buscar envio por nombre contacto(*) <span style="color:red">(*Seleccione)</span>  </label>
                                <div class="form-group">
                                    <div class="">
                                        <div class="form-inline">
                                            <input type="text" class="form-control" v-model="buscar_envio" @keyup="selectEnvio()" placeholder="Ingrese nombre contacto de envio">
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modali}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                <div class="modal-dialog" >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seleccione un envio</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModali()">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <template v-if="arrayEnvios">
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    v-for="(envio,index) in arrayEnvios" 
                                                    :key="index" 
                                                    v-text="envio.contacto"
                                                    @click="getDatosEnvio(envio,index)">
                                                    </a> 
                                                </div>
                                            </template>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" @click="cerrarModali()" data-dismiss="modal">Cancelar</button>
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
                                <th scope="col">Empresa</th>
                                <th scope="col">Contacto</th>
                                <th scope="col">Documento</th>
                                <th scope="col">Direccion</th>
                                <th scope="col">Telefono</th>
                                <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="cuenta.envios.length>0">
                                <tr v-for="(cont,index) in cuenta.envios" :key="index">
                                    <th> <button type="button" class="btn btn-sm"  @click="favoritoAsignar(cont,cuenta.id,'envio')">
                                            <i class="icon-star" :class="{'favorito' : cont.favorito}"></i>
                                        </button>
                                    </th>
                                    <th scope="row" v-text="cont.empresa"></th>
                                    <td v-text="cont.contacto"></td>
                                    <td v-text="cont.tipo_persona"></td>
                                    <td>{{cont.tipodocumento}}  {{cont.numero}}</td>
                                    <td >{{cont.direccion}},{{cont.ciudad}}</td>
                                    <td v-text="cont.telefono"></td>
                                    <td> <button type="button" class="btn btn-danger btn-sm" @click="eliminarEnvio(cont,index)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                            </tbody>
                            <tbody v-else>
                                <tr><td colspan="7" style="color:rgb(145, 145, 145); font-weight: bold;">No se encontraron registros</td></tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
   
                    
                <div class="contenedor-footer">
                    <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                   
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>
<script>
    import formcontacto from './FormContacto'
    import formempresa from './FormEmpresa'
    import formenvio from './FormEnvio'
    export default {
        props :['cuenta'],
        data (){
            
            return {
                contactoformulario:2,
                empresaformulario:2,
                envioformulario:2,
                arrayContactos:[],
                arrayEmpresas:[],
                arrayEnvios:[],
                show:'cuentas',
                persona_id: 0,
                contactos:[],
                arrayCuentas : [],
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
                buscar_cliente:'',
                buscar_empresa:'',
                buscar_envio:'',
                modalc:0,
                modale:0,
                modali:0
            }
        },
        components:{
            formcontacto,
            formempresa,
            formenvio,
        },
        computed:{
           
        },
        methods : {
            llenarformulario(array){
               
                if(Object.entries(array[0]).length!=0){
                    switch(array[2]){
                        case 'contacto':
                        this.cuenta.contactos.push(array[0])
                        this.contactoformulario=array[1]
                        break;
                        case 'empresa':
                            this.cuenta.empresas.push(array[0])
                            this.empresaformulario=array[1]
                        break;
                        case 'envio':
                            this.cuenta.envios.push(array[0])
                            this.envioformulario=array[1]
                        break;
                    }
                }
            },
            cerrarModalc(){
                this.modalc=0
                this.arrayContactos=[]
            },
            cerrarModale(){
                this.modale=0
                this.arrayEmpresas=[]
            },
            cerrarModali(){
                this.modali=0
                this.arrayEnvios=[]
            },
            selectContacto(){
                let me=this;
                
                var url= '/contacto/selectContactos?filtro='+this.buscar_cliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayContactos=respuesta;
                    me.modalc=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectEmpresa(){
                let me=this;
                
                var url= '/empresa/selectEmpresas?filtro='+this.buscar_empresa;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayEmpresas=respuesta;
                    me.modale=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectEnvio(){
                let me=this;
                
                var url= '/envio/selectEnvios?filtro='+this.buscar_envio;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayEnvios=respuesta;
                    me.modali=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosContacto(contacto,index){
                this.cuenta.contactos.push(contacto)
                this.modalc=0
            },
            getDatosEmpresa(empresa,index){
                this.cuenta.empresas.push(empresa)
                this.modale=0
            },
            getDatosEnvio(envio,index){
                this.cuenta.envios.push(envio)
                this.modali=0
            },
            
            favoritoAsignar(data,id,tabla){
                let me=this;
                var url= '/asignarFavorito?id='+id+'&id2='+data.id+'&tabla='+tabla;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    switch(tabla){
                        case 'contacto':
                        me.cuenta.contactos=respuesta
                        break
                        case 'empresa':
                        me.cuenta.empresas=respuesta
                        break
                        case 'envio':
                        me.cuenta.envios=respuesta
                        break

                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            nuevoContacto(action){
                if(action=='nuevo'){
                    this.contactoformulario=1
                }else{
                    this.contactoformulario=2
                }
            },
            nuevoEmpresa(action){
                if(action=='nuevo'){
                    this.empresaformulario=1
                }else{
                    this.empresaformulario=2
                }
            },
            nuevoEnvio(action){
                if(action=='nuevo'){
                    this.envioformulario=1
                }else{
                    this.envioformulario=2
                }
            },
            eliminarContacto(contacto,index){
                let me=this
                if(contacto.id){
                    let me=this
                    var url= '/contacto/desvincular?id='+ me.cuenta.id+'&contacto_id='+contacto.id;
                    axios.delete(url,{'_method': 'DELETE'}).then(function (response) {
                        console.log(response.data)
                    }).catch(function (error) {
                        console.log(error);
                    });
                    me.cuenta.contactos.splice(index,1)
                }else{
                    me.cuenta.contactos.splice(index,1)
                }
              
            },
            
            eliminarEmpresa(empresa,index){
                let me=this
                if(empresa.id){
                    let me=this
                    var url= '/empresa/desvincular?id='+ me.cuenta.id+'&empresa_id='+empresa.id;
                    axios.delete(url,{'_method': 'DELETE'}).then(function (response) {
                        console.log(response.data)
                    }).catch(function (error) {
                        console.log(error);
                    });
                    me.cuenta.empresas.splice(index,1)
                }else{
                    me.cuenta.empresas.splice(index,1)
                }
                
              
            },
            eliminarEnvio(envio,index){
                let me=this
                if(envio.id){
                    let me=this
                    var url= '/envio/desvincular?id='+ me.cuenta.id+'&envio_id='+envio.id;
                    axios.delete(url,{'_method': 'DELETE'}).then(function (response) {
                        console.log(response.data)
                    }).catch(function (error) {
                        console.log(error);
                    });
                    me.cuenta.envios.splice(index,1)
                }else{
                    me.cuenta.envios.splice(index,1)
                }
              
            },
           
            registrarCuenta(){
                if (this.validarPersona()){
                    return;
                }
                
                let me = this;
                const cuenta = new FormData()
                if(this.cuenta.id){
                    cuenta.set('id',this.cuenta.id)
                }else{
                    cuenta.set('id',0)
                }
                cuenta.set('razonsocial',this.cuenta.razonsocial)
                cuenta.set('telefono',this.cuenta.telefono)
                cuenta.set('direccionf',this.cuenta.direccionf)
                cuenta.set('ciudad' , this.cuenta.ciudad)
                cuenta.set('departamento', this.cuenta.departamento)
                cuenta.set('pais' , this.cuenta.pais)
                cuenta.set('sitio_web' , this.cuenta.sitio_web)
                cuenta.set('redes_sociles',this.cuenta.redes_sociales)
                cuenta.set('contactos',JSON.stringify(this.cuenta.contactos))
                cuenta.set('empresas',JSON.stringify(this.cuenta.empresas))
                cuenta.set('envios',JSON.stringify(this.cuenta.envios))
                axios.post('/cliente/registrar',cuenta).then(function (response) {
                    console.log(response)
                    me.cerrarModal();
                }).catch(function (error) {
                    console.log(error);
                });
            },
             validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];
                if (!this.cuenta.razonsocial) this.errorMostrarMsjPersona.push("La razón social/nombre de la cuenta no puede estar vacío.");
                if (!this.cuenta.telefono) this.errorMostrarMsjPersona.push("El número de teléfono no puede estar vacío.");
                if (!this.cuenta.direccionf) this.errorMostrarMsjPersona.push("La dirección no puede estar vacía.");
                
                if (this.errorMostrarMsjPersona.length) this.errorPersona = 1;
                

                return this.errorPersona;
            },           
            viewContacto(value){
                this.$emit('verContacto',{'show':'contactos', 'cont':value })         
            },
            cerrarModal(){
                this.$emit('mostrarListado', 'listado')
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
   
  
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
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
</style>
