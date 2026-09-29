<template>
    <div>
        <div v-if="seccion=='listado'">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> Datos
                <button type="button" @click="abrirModal('nuevo')" class="btn btn-success boton-principal">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
            </div>
                
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-control col-md-3" v-model="criterio">
                            <option value="contacto">Contacto</option>
                            <option value="cuenta">Cuenta</option>
                            <option value="documento">Documento</option>
                            <option value="telefono">Teléfono</option>
                            <option value="ciudad">Ciudad</option>
                            </select>
                            <input type="text" v-model="buscar" @keyup.enter="listarEnvios(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                            <button type="submit" @click="listarEnvios(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Opciones</th>
                            <th>Destinatario</th>
                            <th>Cuentas</th>
                            <th>Documento</th>
                            <th>Telefono</th>
                            <th>Direccion</th>
                            <th>Ciudad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="persona in arrayEnvios" :key="persona.id">
                            <td>
                                <button type="button" @click="abrirModal('editar',persona)" class="btn btn-warning btn-sm">
                                <i class="icon-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" @click="eliminarRegEnvio(persona.id)">
                                <i class="icon-trash"></i>
                                </button>
                            </td>
                            <td >
                                {{persona.contacto}} - {{persona.empresa}}
                            </td>
                            <td ><ul>
                                    <li v-for="(cliente,index) in persona.clientes" :key="index">{{cliente.razonsocial}}</li>
                                </ul>
                            </td>
                            <td >{{persona.tipo_documento}} {{persona.documento}}</td>
                            <td v-text="persona.telefono"></td>
                            <td v-text="persona.direccion"></td>
                            <td v-text="persona.ciudad"></td>
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
            <nuevoenvio :envio="envio" @mostrarListado="mostrarEnvios" ></nuevoenvio>
         </div>   
         <div v-else>
            <nuevoenvio :envio="envio" @mostrarListado="mostrarEnvios" ></nuevoenvio>
         </div>   
    </div>
</template>

<script>
    import nuevoenvio from './NuevoEnvio'
    export default {
        data (){
            return {
                seccion:'listado',
                show:'envios',
                arrayEnvios : [],
                envio:{clientes:[]},
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
                criterio : 'contacto',
                buscar : ''
            }
        },
        components:{
            nuevoenvio
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
            listarEnvios (page,buscar,criterio){
                let me=this;
                var url= '/envio?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayEnvios = respuesta.envios.data;
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
                me.listarEnvios(page,buscar,criterio);
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
                        this.envio = {
                            id:0,
                            idcliente:0,
                            contacto :'',
                            empresa : '',
                            tipo_documento : 'CC',
                            documento : '',
                            telefono : '',
                            direccion : '',
                            ciudad : '',
                            pais : 'Colombia',
                            clientes: []
                        };
                        this.seccion='nuevo';
                        break;
                    }
                    case 'editar':
                    {
                        console.log(data)
                        this.seccion='editar'
                        this.envio=data
                        break;
                       
                    }
                }
            },
            mostrarEnvios(value){
                this.envio={clientes:[]};
                this.seccion=value;
                this.listarEnvios(1,this.buscar,this.criterio);
            },
            eliminarRegEnvio(id){
               Swal.fire({
                title: 'Esta seguro de eliminar este registro de envio?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.isConfirmed) {
                    let me =this
                    axios.delete('/envio/eliminar?id='+id)
                    .then(function (response) {
                        me.listarEnvios(me.pagination.current_page,me.buscar,me.criterio)
                        Swal.fire(
                            'Eliminado',
                            'El registro ha sido eliminado con exito',
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
                        } else if (error.response && error.response.status == 500) {
                            msg = '<b>Error interno del servidor (500)</b><br>No se pudo procesar la solicitud. Por favor, contacte al soporte técnico.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Atención',
                            html: msg
                        });
                        console.log(error);
                    }); 
                }
                })
            }
        },
        mounted() {
            this.listarEnvios(1,this.buscar,this.criterio);
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
