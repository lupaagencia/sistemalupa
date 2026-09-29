<template>
    <div>
        <div v-if="seccion=='listado'">
            <div class="card-header">
                <i class="fa fa-align-justify"></i> FActuraci
                <button type="button" @click="abrirModal('nuevo')" class="btn btn-success boton-principal">
                    <i class="icon-plus"></i>&nbsp;Nuevo
                </button>
            </div>
                
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-control col-md-3" v-model="criterio">
                            <option value="razonsocial">Razon Social</option>
                            <option value="cuenta">Cuenta</option>
                            <option value="numero">Número</option>
                            <option value="correo">Email</option>
                            <option value="telefono">Teléfono</option>
                            </select>
                            <input type="text" v-model="buscar" @keyup.enter="listarEmpresa(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                            <button type="submit" @click="listarEmpresa(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Opciones</th>
                            <th>Razon social</th>
                            <th>Cuentas</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Direccion</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="persona in arrayEmpresas" :key="persona.id">
                            <td>
                                <button type="button" @click="abrirModal('editar',persona)" class="btn btn-warning btn-sm">
                                <i class="icon-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" @click="eliminarEmpresa(persona.id)">
                                <i class="icon-trash"></i>
                                </button>
                            </td>
                            <td v-text="persona.razonsocial"></td>
                            <td ><ul>
                                <li v-for="(cliente,index) in persona.clientes" :key="index">{{cliente.razonsocial}}</li>
                                </ul></td>
                           
                            <td v-text="persona.correo"></td>
                            <td v-text="persona.telefono"></td>
                            <td v-text="persona.direccion"></td>
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
            <nuevaempresa :empresa="empresa" @mostrarListado="mostrarEmpresas" @verCuenta="mostrarTab"></nuevaempresa>
         </div>   
         <div v-else>
            <nuevaempresa :empresa="empresa" @mostrarListado="mostrarEmpresas" @verCuneta="mostrarTab"></nuevaempresa>
         </div> 
    </div>
</template>

<script>
    import nuevaempresa from './NuevaEmpresa'
    export default {
        props: ['cont'],
        data (){
            return {
                seccion:'listado',
                show:'contactos',
                empresa:{clientes:[]},
                arrayEmpresas : [],
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
            nuevaempresa
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
            listarEmpresa (page,buscar,criterio){
                let me=this;
                var url= '/facturacion?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    console.log(respuesta)
                    me.arrayEmpresas = respuesta.empresas.data;
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
                me.listarEmpresa(page,buscar,criterio);
            },
            abrirModal(accion, data){ 
                console.log(data)
                switch(accion){
                    case 'nuevo':
                    {
                        this.empresa = {
                            id:0,
                            razonsocial : '',
                            tipo_persona : 'Persona Natural',
                            tipo_documento : 'NIT',
                            numero : '',
                            digito : '',
                            direccion : '',
                            telefono : '',
                            correo : '',
                            ciudad : '',
                            departamento : '',
                            pais : 'Colombia',
                            actividad : '',
                            responsable : '',
                            clientes: []
                        };
                        this.seccion='nuevo';
                        break;
                    }
                    case 'editar':
                    {
                        this.seccion='editar'
                        this.empresa=data
                        break;
                       
                    }
                }
            },
            mostrarEmpresas(value){
                this.empresa={clientes:[]};
                this.seccion=value;
                this.listarEmpresa(1,this.buscar,this.criterio);
            },
            eliminarEmpresa(id){
               Swal.fire({
                title: 'Esta seguro de eliminar este registro de facturacion?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.isConfirmed) {
                    let me =this
                    axios.delete('/facturacion/eliminar?id='+id)
                    .then(function (response) {
                        me.listarEmpresa(me.pagination.current_page,me.buscar,me.criterio)
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
                    title: 'Seleccione el nuevo registro de facturación',
                    text: 'Todos los comprobantes se moverán al nuevo registro seleccionado.',
                    input: 'text',
                    inputPlaceholder: 'Razón social o NIT...',
                    showCancelButton: true,
                    confirmButtonText: 'Buscar',
                    cancelButtonText: 'Cancelar'
                }).then((res) => {
                    if (res.isConfirmed && res.value) {
                        axios.get('/facturacion/selectFacturacion?filtro=' + res.value)
                        .then(function(response){
                            let items = response.data.facturaciones;
                            let options = {};
                            items.forEach(i => {
                                if(i.id != id_origen) options[i.id] = i.razonsocial + ' (' + i.nit + ')';
                            });

                            if(Object.keys(options).length == 0){
                                Swal.fire('Error', 'No se encontraron otros registros con ese nombre.', 'error');
                                return;
                            }

                            Swal.fire({
                                title: 'Seleccione el destino final',
                                input: 'select',
                                inputOptions: options,
                                inputPlaceholder: 'Seleccione un registro',
                                showCancelButton: true,
                                inputValidator: (value) => {
                                    return new Promise((resolve) => {
                                        if (value) resolve();
                                        else resolve('Debe seleccionar un registro');
                                    });
                                }
                            }).then((selection) => {
                                if (selection.isConfirmed) {
                                    axios.post('/facturacion/reasignar', {
                                        'id_origen': id_origen,
                                        'id_destino': selection.value
                                    }).then(function(resp){
                                        me.listarEmpresa(1, me.buscar, me.criterio);
                                        Swal.fire('Éxito', 'Registros reasignados y eliminado correctamente.', 'success');
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
            },
            
        },
        mounted() {
            this.listarEmpresa(1,this.buscar,this.criterio);
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
