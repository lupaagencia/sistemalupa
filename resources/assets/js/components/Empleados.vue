<template>
    <main class="main">
            <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
                    </ol>  
            <div class="container-fluid">
            
            <!-- Ejemplo de tabla Listado -->
            <div class="contenedor">
                <div  class="contenedor-header">
                    <div v-if="listado==1">
                        <i class="fa fa-align-justify"></i> Empleados
                        <button type="button" @click="mostrarDetalle()" class="btn btn-success boton-principal">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                        <!-- <button type="button" @click="generar()" class="btn btn-secondary">
                            <i class="icon-plus"></i>&nbsp;Generar
                        </button> -->
                    </div>
                    <div v-else>
                        
                    </div>
                </div>
                <!-- Listado-->
                <template v-if="listado==1">
                    <div class="contenedor-seccion">
                        <!-- Métricas Rápidas de Biometría & Personal -->
                        <div class="row mb-3">
                            <div class="col-xl-3 col-md-6 mb-2">
                                <div class="card border-0 shadow-sm p-3 rounded text-white" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-white-50 small font-weight-bold text-uppercase">Total Empleados</span>
                                            <h3 class="font-weight-bold mb-0 text-white">{{ pagination.total || arrayEmpleados.length }}</h3>
                                        </div>
                                        <div class="rounded-circle p-2 text-white" style="background: rgba(255,255,255,0.15);">
                                            <i class="fa fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-2">
                                <div class="card border-0 shadow-sm p-3 rounded text-white" style="background: linear-gradient(135deg, #065f46 0%, #10b981 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-white-50 small font-weight-bold text-uppercase">Foto Registrada</span>
                                            <h3 class="font-weight-bold mb-0 text-white">{{ totalConFoto }} <small class="text-white-50" style="font-size: 1rem;">/ {{ arrayEmpleados.length }}</small></h3>
                                        </div>
                                        <div class="rounded-circle p-2 text-white" style="background: rgba(255,255,255,0.15);">
                                            <i class="fa fa-camera fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-2">
                                <div class="card border-0 shadow-sm p-3 rounded text-white" style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-white-50 small font-weight-bold text-uppercase">Huella Vinculada</span>
                                            <h3 class="font-weight-bold mb-0 text-white">{{ totalConHuella }} <small class="text-white-50" style="font-size: 1rem;">/ {{ arrayEmpleados.length }}</small></h3>
                                        </div>
                                        <div class="rounded-circle p-2 text-white" style="background: rgba(255,255,255,0.15);">
                                            <i class="fa fa-fingerprint fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-2">
                                <div class="card border-0 shadow-sm p-3 rounded text-white" style="background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-white-50 small font-weight-bold text-uppercase">Biometría Completa</span>
                                            <h3 class="font-weight-bold mb-0 text-white">{{ totalBiometriaCompleta }} <small class="text-white-50" style="font-size: 1rem;">(Foto + Huella)</small></h3>
                                        </div>
                                        <div class="rounded-circle p-2 text-white" style="background: rgba(255,255,255,0.15);">
                                            <i class="fa fa-id-badge fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                    <option value="nombre" >Nombre Empleado</option>
                                    <option value="id" >Numero de Documento</option>
                                    <option value="fecha">Cargo</option>
                                    </select>
                                    <input v-if="criterio=='fecha'" type="date" v-model="buscar" @keyup.enter="listarEmpleados(1,buscar,criterio,per_page)" class="form-control" placeholder="Texto a buscar">
                                    <input v-else type="text" v-model="buscar" @keyup.enter="listarEmpleados(1,buscar,criterio,per_page)" class="form-control" placeholder="Texto a buscar">
                                </div>
                            </div>
                            
                        </div>
                        <nav>
                                <ul class="pagination">
                                    <li>
                                        <select class="custom-select mr-sm-2" id="inlineFormCustomSelect" v-model="per_page" @change="listarEmpleados(1,buscar,criterio,per_page)">
                                            <option value="10" >10</option>
                                            <option value="50" >50</option>
                                            <option value="100" >100</option>
                                            <option value="150" >150</option>
                                            <option value="200" >200</option>
                                            <option value="500" >500</option>
                                            <option value="1000" >1000</option>
                                            
                                        </select>
                                    </li>
                                    <li class="page-item" v-if="pagination.current_page > 1">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="(page,index) in pagesNumber" :key="index" :class="[page == isActived ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                                    </li>
                                    <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 110px;">Opciones</th>
                                        <th class="text-center" style="width: 100px;">Fotografía</th>
                                        <th class="text-center" style="width: 120px;">Huella Dactilar</th>
                                        <th>Nombre</th>
                                        <th>Cargo</th>
                                        <th>Numero Documento</th>
                                        <th>Teléfono</th>
                                        <th>Cumpleaños</th>
                                        <th>Contacto Emergencia</th>
                                    
                                    </tr>
                                </thead>
                                <tbody v-if="arrayEmpleados.length>0">
                                    <tr v-for="(empleado,index) in arrayEmpleados" :key="empleado.id">
                                        <td class="btnOpciones align-middle">
                                            <button type="button" title="Imprimir Registro" @click="imprimirEmpleado(empleado)" class="btn btn-warning btn-sm">
                                            <i class="icon-printer"></i>
                                            </button> 
                                            <button type="button" title="Editar Empleado" @click="verEmpleado(empleado)" class="btn btn-success btn-sm">
                                            <i class="icon-eye"></i>
                                            </button> 
                                            <template>
                                                <button type="button" title="Borrar Empleado" class="btn btn-danger btn-sm" @click="borrarEmpleado(empleado.id)">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </template>
                                        </td>
                                        <td class="text-center align-middle" style="min-width: 95px;">
                                            <div class="d-flex flex-column align-items-center justify-content-center py-1">
                                                <img v-if="empleado.foto" 
                                                     :src="getFotoUrl(empleado.foto)" 
                                                     @error="onFotoError($event, empleado.foto)"
                                                     class="rounded-circle shadow-sm mb-1 border border-success" 
                                                     style="width: 44px; height: 44px; object-fit: cover; cursor: pointer;"
                                                     @click="verFotoGrande(empleado)"
                                                     title="Clic para ver foto grande"
                                                     alt="Foto">
                                                <div v-else class="rounded-circle bg-light d-flex align-items-center justify-content-center mb-1 border text-muted shadow-sm" 
                                                     style="width: 44px; height: 44px;">
                                                    <i class="fa fa-user fa-lg text-secondary"></i>
                                                </div>
                                                <span v-if="empleado.foto" class="badge badge-success px-2 py-0 font-weight-bold" style="font-size: 0.72rem;">
                                                    <i class="fa fa-check mr-1"></i> Foto OK
                                                </span>
                                                <span v-else class="badge badge-secondary px-2 py-0 text-muted bg-light border" style="font-size: 0.72rem;">
                                                    <i class="fa fa-times mr-1 text-danger"></i> Sin Foto
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle" style="min-width: 120px;">
                                            <div class="d-flex flex-column align-items-center justify-content-center py-1">
                                                <span v-if="empleado.huella_dactilar" class="badge badge-success px-2 py-1 font-weight-bold shadow-sm" style="font-size: 0.8rem;">
                                                    <i class="fa fa-fingerprint mr-1"></i> Vinculada
                                                </span>
                                                <span v-else class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 0.78rem;">
                                                    <i class="fa fa-exclamation-triangle mr-1"></i> Sin Huella
                                                </span>
                                                <button v-if="empleado.huella_dactilar" 
                                                        type="button" 
                                                        class="btn btn-link btn-xs text-danger p-0 mt-1" 
                                                        @click="desvincularHuella(empleado)" 
                                                        title="Desvincular huella de este empleado"
                                                        style="font-size: 0.72rem;">
                                                    <i class="fa fa-trash-o mr-1"></i> Desvincular
                                                </button>
                                            </div>
                                        </td>
                                        <td class="align-middle font-weight-bold">
                                            {{empleado.nombre}} {{ empleado.apellido }}
                                        </td>
                                        <td class="align-middle" v-text="empleado.cargo"></td>
                                        <td class="align-middle">{{ empleado.tipo_doc }} {{ empleado.num_doc }}</td>
                                        <td class="align-middle" v-text="empleado.telefono"></td>
                                        <td class="align-middle" v-text="empleado.fecha_nacimiento"></td>
                                        <td class="align-middle" v-text="empleado.contacto_emergencia"></td>
                                    
                                    </tr>                                
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <th colspan="11">No hay empleados </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                                <ul class="pagination">
                                
                                    <li class="page-item" v-if="pagination.current_page > 1">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="(page,index) in pagesNumber" :key="index" :class="[page == isActived ? 'active' : '']">
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
                    <nuevoempleado :user="user" :empleado="empleado" @ocultarDetalle="ocultarDetalle" @listarEmpleados="listarEmpleados" :edit="edit"></nuevoempleado>
                </template>
                <!-- Fin Detalle-->
            </div>
            <!-- Fin ejemplo de tabla Listado -->
        </div>
        <!--Inicio del modal agregar/actualizar-->
            
    </main>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import nuevoempleado from './empleado/NuevoEmpleado'
  
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    var fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
    export default {
        props:['user','show'],
        data (){
            return {
                edit:0,
                listado:1,
                arrayEmpleados : [],
                offset : 3,
                criterio : 'nombre',
                buscar : '',
                empleado: {
          // Campos de texto
                    id:0,
                    nombre: '',
                    apellido: '',
                    lugar_nacimiento: '',
                    telefono: '',
                    direccion: '',
                    correo: '',
                    cargo: '',
                    area: '',
                    banco: '',
                    pension: '',
                    eps: '',
                    arl: '',
                    nivel_estudio: '',
                    titulos: '',
                    certificaciones: '',
                    idiomas: '',
                    habilidades: '',
                    experiencia: '',
                    contacto_esposa: '',
                    contacto_padres: '',
                    contacto_emergencia: '',
                    info_medica: '',
                    talla_dotacion: '',
                    // Campos select
                    tipo_doc: '',
                    estado_civil: '',
                    tipo_contrato: '',
                    tipo_jornada: '',
                    tipo_cuenta: '',
                    turno: '',
                    // Campos fecha
                    fecha_nacimiento: '',
                    fecha_ingreso: '',
                    fecha_finalizacion: '',
                    // Campos numéricos
                    num_doc: '',
                    num_hijos: '',
                    salario: '',
                    auxilio_transporte: 0,
                    horas_semanales: '',
                    num_cuenta_banco: '',
                    num_afiliacion_social: '',
                    // Campos archivos
                    foto: null,
                    img_doc: null,
                    pdf_hoja: null,
                    pdf_contrato: null,
                },
                per_page:100,
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
               
            }
        },
        components: {
            nuevoempleado,
            // verempleado,
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
            totalConFoto() {
                if (!this.arrayEmpleados) return 0;
                return this.arrayEmpleados.filter(e => e.foto && String(e.foto).trim() !== '').length;
            },
            totalConHuella() {
                if (!this.arrayEmpleados) return 0;
                return this.arrayEmpleados.filter(e => e.huella_dactilar && String(e.huella_dactilar).trim() !== '').length;
            },
            totalBiometriaCompleta() {
                if (!this.arrayEmpleados) return 0;
                return this.arrayEmpleados.filter(e => e.foto && String(e.foto).trim() !== '' && e.huella_dactilar && String(e.huella_dactilar).trim() !== '').length;
            },

        },
        methods: {
      
          
            mostrarListado(value){
                this.listarEmpleados(1,this.buscar,this.criterio,this.per_page);
                this.empresa={"id":0,"favorito":0,"razonsocial":"","tipo_persona":"Juridica","tipo_documento":"","numero":"","digito":"","direccion":"","telefono":"","correo":"","ciudad":"","departamento":"Valle del Cauca","pais":"Colombia","actividad":"","responsable":"","clientes":[]}
                
                this.seccion=value
                this.listado=1

            },
            imprimirEmpleado(pedido){
              let me=this;
              var ped=encodeURIComponent(JSON.stringify(pedido))
              console.log(ped)
              axios({
              url: '/imprimirEmpleado?id='+pedido.id,         
              method: 'GET',
              responseType: 'blob', // important
              }).then((response) => {
                console.log(response)
                  const url = window.URL.createObjectURL(new Blob([response.data]));
                  const link = document.createElement('a');
                  link.href = url;
                  link.setAttribute('download', 'pedido '+pedido.cliente.razonsocial+' '+pedido.id+'.pdf');
                  document.body.appendChild(link);
                  link.click();
              });
          },
             cambiarEstado(pedido){
                var me=this
                var userj=me.user
                axios.put('/comprobante/cambiarEstado',{
                    'id':pedido.id,
                    'user_id':userj.id,
                    'estado':pedido.estado,
                })
                .then(function (response) {
                    console.log(response)
                    me.listarEmpleados(1,response.data.estado,'estado',this.per_page);
                }).catch(function (error) {
                    console.log(error);
                });
            },
            cambiarPagina(buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarEmpleados(1,buscar,criterio,me.per_page);
            },
          
            listarEmpleados(page,buscar,criterio,per_page){
                let me=this;
                var url= '/empleado?page=' + page +'&per_page='+ per_page +'&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    console.log(response)
                    var respuesta= response.data;
                    me.arrayEmpleados = respuesta.empleados.data;
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
                me.listarEmpleados(page,buscar,criterio,me.per_page);
            },
            borrarEmpleado(id){
                const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de borrar este item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;
                    var userj=me.user
                    var url= '/empleado/borrar?id='+ id+'&user_id='+userj.id;
                    axios.delete(url,{'_method': 'DELETE'})
                    .then(function (response) {
                        console.log(response)
                        me.listarEmpleados(1,me.buscar,me.criterio,me.per_page);
                        swal(
                        'Eliminado!',
                        'El item ha sido eliminado con éxito.',
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
            verEmpleado(empleado){
                this.empleado=empleado
                this.listado=0
                this.edit=1
            },
            
            mostrarDetalle(){
                this.listado=0;
            },
            verPedido(pedido){
                this.pedido=pedido
                this.listado=0
                this.edit=1
            },
            ocultarDetalle(val,val2){
                this.listado=val
                this.listarEmpleados(1,'',"nombre",50)
            },
           
            cerrarModal(){
                this.modal=0;
            },
            getFotoUrl(foto) {
                if (!foto) return '/img/avatar.png';
                if (foto.startsWith('http://') || foto.startsWith('https://') || foto.startsWith('data:')) return foto;
                if (foto.startsWith('/')) return foto;
                return '/img/empleados/' + foto;
            },
            onFotoError(event, foto) {
                if (foto && !event.target.getAttribute('data-tried-fotos')) {
                    event.target.setAttribute('data-tried-fotos', '1');
                    event.target.src = '/fotos/' + foto;
                } else if (foto && !event.target.getAttribute('data-tried-productos')) {
                    event.target.setAttribute('data-tried-productos', '1');
                    event.target.src = '/img/productos/' + foto;
                } else {
                    event.target.src = '/img/avatar.png';
                }
            },
            verFotoGrande(empleado) {
                if (!empleado.foto) return;
                const url = this.getFotoUrl(empleado.foto);
                Swal.fire({
                    title: empleado.nombre + ' ' + (empleado.apellido || ''),
                    text: 'Fotografía de Identificación Facial',
                    imageUrl: url,
                    imageWidth: 280,
                    imageHeight: 280,
                    imageAlt: 'Foto de ' + empleado.nombre,
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#10b981'
                });
            },
            desvincularHuella(empleado) {
                let me = this;
                Swal.fire({
                    title: '¿Desvincular Huella?',
                    text: '¿Desea eliminar la huella dactilar registrada de ' + empleado.nombre + ' ' + (empleado.apellido || '') + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, desvincular',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.post('/asistencia/eliminar-huella', { empleado_id: empleado.id }).then(() => {
                            empleado.huella_dactilar = null;
                            Swal.fire('Huella Eliminada', 'La huella ha sido desvinculada exitosamente.', 'success');
                        }).catch(err => {
                            Swal.fire('Error', 'No se pudo eliminar la huella.', 'error');
                        });
                    }
                });
            }
          
        },
        mounted() {
            this.listarEmpleados(1,this.buscar,this.criterio,this.per_page);
        }
    }
</script>
<style>    
    .active{
        color:red;
    }
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
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

</style>
