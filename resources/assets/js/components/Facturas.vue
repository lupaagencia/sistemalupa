<template>
    <div class="contenedor">
        <div class="container-fluid">
            
            <!-- Ejemplo de tabla Listado -->
            <div class="">
                <div  class="contenedor-header">
                    <div v-if="listado==1">
                        <i class="fa fa-align-justify"></i> Facturas
                        <button v-if="user.idrol=='Administrador'" type="button" @click="mostrarDetalle()" class="btn btn-success boton-principal">
                            <i class="icon-plus"></i>&nbsp;Nueva
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
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group shadow-sm w-100" role="group">
                                    <button type="button" class="btn font-weight-bold" :class="tipoFiltro === 'proforma' && estadoFiltro === '' ? 'btn-warning text-dark' : 'btn-light border'" @click="filtrarFacturas('proforma', '')">
                                        📋 Proformas - Todas ({{ cantProformas }})
                                    </button>
                                    <button type="button" class="btn font-weight-bold" :class="tipoFiltro === 'proforma' && estadoFiltro === '1' ? 'btn-success' : 'btn-light border'" @click="filtrarFacturas('proforma', '1')">
                                        📤 Proformas Enviadas ({{ cantEnviadas }})
                                    </button>
                                    <button type="button" class="btn font-weight-bold" :class="tipoFiltro === 'proforma' && estadoFiltro === '0' ? 'btn-secondary text-white' : 'btn-light border'" @click="filtrarFacturas('proforma', '0')">
                                        📥 Proformas No Enviadas ({{ cantNoEnviadas }})
                                    </button>
                                    <button type="button" class="btn font-weight-bold" :class="tipoFiltro === '' ? 'btn-primary' : 'btn-light border'" @click="filtrarFacturas('', '')">
                                        📑 Todos los Comprobantes ({{ cantFacturas + cantProformas }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-4 font-weight-bold" v-model="criterio">
                                        <option value="cliente_id">Nombre cliente</option>
                                        <option value="fecha">Fecha</option>
                                        <option value="num_comprobante">Número</option>
                                    </select>
                                    <input v-if="criterio=='fecha'" type="date" v-model="buscar" @change="ListarFacturas(1,buscar,criterio,per_page)" class="form-control">
                                    <input v-else type="text" v-model="buscar" @input="buscarLive" @keyup.enter="ListarFacturas(1,buscar,criterio,per_page)" class="form-control" placeholder="Buscar por cliente o número...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" @click="ListarFacturas(1,buscar,criterio,per_page)"><i class="fa fa-search"></i> Buscar</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                         <nav>
                                <ul class="pagination">
                                    <li>
                                        <select class="custom-select mr-sm-2" id="inlineFormCustomSelect" v-model="per_page" @change="ListarFacturas(1,buscar,criterio,per_page)">
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
                                        <th>Opciones</th>
                                        <th>Tipo</th>
                                        <th>Estado DIAN</th>
                                        <th>Estado Físico</th>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Razón Social</th>
                                        <th>Productos</th>
                                        <th>Forma de pago</th>
                                        <th>Subtotal</th>
                                        <th>Impuesto</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayfacturas.length>0">
                                    <tr v-for="(factura,index) in arrayfacturas" :key="factura.id">
                                        <td>
                                            <button type="button" @click="imprimirfactura(factura)" class="btn btn-warning btn-sm" title="Imprimir">
                                                <i class="icon-printer"></i>
                                            </button> 
                                            <button type="button" @click="verfactura(factura)" class="btn btn-info btn-sm" title="Editar">
                                                <i class="icon-pencil"></i>
                                            </button> 
                                            <template v-if="user.idrol=='Administrador'">
                                                <button type="button" class="btn btn-danger btn-sm" @click="borrarfactura(factura.id)" title="Eliminar">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </template>

                                            <!-- Transmitir DIAN -->
                                            <button v-if="!factura.factura_electronica || factura.factura_electronica.estado_dian === 'Rechazado'" 
                                                    type="button" 
                                                    @click="transmitirFactura(factura)" 
                                                    class="btn btn-outline-warning btn-sm" 
                                                    title="Transmitir a DIAN"
                                                    :disabled="transmitiendoId === factura.id">
                                                <i class="fa" :class="transmitiendoId === factura.id ? 'fa-spinner fa-spin' : 'fa-cloud-upload'"></i>
                                            </button>

                                            <!-- Descargar XML -->
                                            <button v-if="factura.factura_electronica && factura.factura_electronica.estado_dian === 'Aceptado'" 
                                                    type="button" 
                                                    @click="descargarArchivo('xml', factura.factura_electronica.id)" 
                                                    class="btn btn-outline-info btn-sm" 
                                                    title="Descargar XML">
                                                <i class="fa fa-file-code-o"></i>
                                            </button>

                                            <!-- Descargar PDF -->
                                            <button v-if="factura.factura_electronica && factura.factura_electronica.estado_dian === 'Aceptado'" 
                                                    type="button" 
                                                    @click="descargarArchivo('pdf', factura.factura_electronica.id)" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Descargar PDF">
                                                <i class="fa fa-file-pdf-o"></i>
                                            </button>
                                        <td>
                                            <span v-if="factura.tipo === 'proforma'" class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 0.9em;">
                                                📋 Proforma #{{ factura.num_comprobante }}
                                            </span>
                                            <span v-else class="badge badge-primary font-weight-bold px-2 py-1" style="font-size: 0.9em;">
                                                📄 Factura #{{ factura.num_comprobante }}
                                            </span>
                                        </td>
                                        <td>
                                            <!-- DIAN Status Badge -->
                                            <span v-if="!factura.factura_electronica" class="badge badge-secondary px-2 py-1" style="font-size: 0.85em;">
                                                <i class="fa fa-cloud"></i> No Emitido
                                            </span>
                                            <span v-else-if="factura.factura_electronica.estado_dian === 'Aceptado'" class="badge badge-success px-2 py-1" style="font-size: 0.85em; cursor: pointer;" @click="verDetallesDIAN(factura)">
                                                <i class="fa fa-check-circle"></i> Aceptado
                                            </span>
                                            <span v-else-if="factura.factura_electronica.estado_dian === 'Pendiente'" class="badge badge-warning px-2 py-1 text-white" style="font-size: 0.85em;">
                                                <i class="fa fa-refresh fa-spin"></i> Pendiente
                                            </span>
                                            <span v-else-if="factura.factura_electronica.estado_dian === 'Rechazado'" class="badge badge-danger px-2 py-1" style="font-size: 0.85em; cursor: pointer;" @click="verDetallesDIAN(factura)">
                                                <i class="fa fa-times-circle"></i> Rechazado
                                            </span>
                                        </td>
                                        <td>
                                            <template v-if="factura.tipo === 'proforma'">
                                                <button v-if="factura.estado==0" class="btn btn-warning btn-sm font-weight-bold shadow-sm" @click="cambiarEstado(factura, 1)" title="Marcar como Enviada">📋 Proforma No Enviada</button>
                                                <button v-else class="btn btn-success btn-sm font-weight-bold shadow-sm" @click="cambiarEstado(factura, 0)" title="Marcar como No Enviada">📋 Proforma Enviada</button>
                                            </template>
                                            <template v-else>
                                                <button v-if="factura.estado==0" class="btn btn-danger btn-sm" @click="cambiarEstado(factura, 1)">No enviada</button>
                                                <button v-else class="btn btn-success btn-sm" @click="cambiarEstado(factura, 0)">Enviada</button>
                                            </template>
                                        </td>
                                        <td v-text="factura.num_comprobante"></td>
                                        <td v-text="factura.fecha"></td>
                                        <td><button @click="ListarFacturas(1,factura.razonsocial.razonsocial,'cliente_id',per_page)" class="btn btn-link boton-principal">{{factura.razonsocial.razonsocial}}</button></td>
                                        <td>
                                            <div v-for="linea in factura.lineas" :key="linea.id">{{linea.articulo.nombre}}</div>
                                        </td>
                                        <td v-text="factura.forma_pago"></td>
                                        <td v-text="factura.subtotal"></td>
                                        <td v-text="factura.impuestos"></td>
                                        <td v-text="factura.total"></td>
                                    </tr>                                
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <th colspan="11">No hay facturas </th>
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
                <template v-else-if="listado==2">
                    <verfactura :user="user" :factura="factura" ></verfactura>
                </template>
                <template v-else-if="listado==3">
                    <nuevaempresa  :empresa="empresa" @mostrarListado="mostrarListado"></nuevaempresa>
                </template>
                <template v-else>
                    <factura :user="user" :factura="factura" :dato="1" @ocultarDetalle="ocultarDetalle" @ListarFacturasp="ListarFacturas" :edit="edit"></factura>
                </template>
                <!-- Fin Detalle-->
            </div>
            <!-- Fin ejemplo de tabla Listado -->
        </div>
        
        <!-- Modal Auditoría DIAN -->
        <div class="modal fade" :class="{'mostrar' : modalDIAN}" tabindex="-1" style="overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0 rounded-xl overflow-hidden">
                    <div class="modal-header bg-dark text-white py-3">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-shield mr-2 text-primary"></i> Detalles de Facturación Electrónica DIAN
                        </h5>
                        <button @click="cerrarModalDIAN" type="button" class="close text-white"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4 bg-light" v-if="dianDetalle">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card border-0 shadow-sm rounded-lg mb-4">
                                    <div class="card-body">
                                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Información General</h6>
                                        <div class="row mb-3">
                                            <div class="col-sm-4 text-muted">ID Comprobante:</div>
                                            <div class="col-sm-8 font-weight-bold">#{{ dianDetalle.comprobante_id }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4 text-muted">Estado DIAN:</div>
                                            <div class="col-sm-8">
                                                <span class="badge" :class="dianDetalle.estado_dian === 'Aceptado' ? 'badge-success' : 'badge-danger'">
                                                    {{ dianDetalle.estado_dian }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row mb-3" v-if="dianDetalle.fecha_transmision">
                                            <div class="col-sm-4 text-muted">Transmisión:</div>
                                            <div class="col-sm-8">{{ formatDate(dianDetalle.fecha_transmision) }}</div>
                                        </div>
                                        <div class="row mb-3" v-if="dianDetalle.uuid_proveedor">
                                            <div class="col-sm-4 text-muted">UUID Proveedor:</div>
                                            <div class="col-sm-8"><code class="small">{{ dianDetalle.uuid_proveedor }}</code></div>
                                        </div>
                                        <div class="row" v-if="dianDetalle.cufe">
                                            <div class="col-12 text-muted mb-1">CUFE (Código Único de Facturación Electrónica):</div>
                                            <div class="col-12">
                                                <div class="p-3 bg-dark text-success rounded-lg font-weight-bold small text-monospace" style="word-break: break-all;">
                                                    {{ dianDetalle.cufe }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card border-0 shadow-sm rounded-lg mb-4 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Código QR de Control</h6>
                                    <div v-if="dianDetalle.qr_code" class="p-2 bg-white rounded border shadow-xs mb-3">
                                        <div class="d-flex align-items-center justify-content-center bg-light font-weight-bold" style="width: 120px; height: 120px; border: 2px dashed #ccc; font-size: 10px; color: #666; flex-direction: column;">
                                            <i class="fa fa-qrcode fa-3x mb-2 text-dark"></i>
                                            <span>MOCK DIAN QR</span>
                                        </div>
                                    </div>
                                    <a v-if="dianDetalle.qr_code" :href="dianDetalle.qr_code" target="_blank" class="btn btn-outline-primary btn-sm btn-block">
                                        <i class="fa fa-external-link"></i> Consultar DIAN
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Respuesta DIAN -->
                        <div class="card border-0 shadow-sm rounded-lg" v-if="dianDetalle.dian_response">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Respuesta Oficial DIAN / PTA</h6>
                                <pre class="p-3 bg-dark text-white rounded-lg small mb-0" style="max-height: 200px; overflow-y: auto;">{{ formatJSON(dianDetalle.dian_response) }}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button @click="cerrarModalDIAN" type="button" class="btn btn-secondary px-4">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import vSelect from 'vue-select';
    import factura from './partes/factura'
    import nuevaempresa from './cliente/NuevaEmpresa'
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    var fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
    export default {
        props:['user','show'],
        data (){
            return {
                generarf:-1,
                edit:0,
                listado:1,
                arrayfacturas : [],
                offset : 3,
                criterio : 'cliente_id',
                buscar : '',
                criterioA:'nombre',
                buscarA:'',
                empresa:{"id":0,"favorito":0,"razonsocial":"","tipo_persona":"Juridica","tipo_documento":"","numero":"","digito":"","direccion":"","telefono":"","correo":"","ciudad":"","departamento":"Valle del Cauca","pais":"Colombia","actividad":"","responsable":"","clientes":[]},
                factura:{'fecha':fecha,'forma_pago':'Contado','transportadora':' ','estado':2,'cliente':{'contactos':[],'empresas':[],'envios':[]},'lineas':[],'iva':0,'subtotal':0, 'abono':0, 'saldo':0, 'descuento':0, 'impuestos':0, 'total':0},
                 pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                per_page:100,
                modalDIAN: 0,
                dianDetalle: null,
                transmitiendoId: null,
                dominio: '',
                tipoFiltro: 'proforma',
                estadoFiltro: '',
                cantProformas: 0,
                cantFacturas: 0,
                cantEnviadas: 0,
                cantNoEnviadas: 0
            }
        },
        components: {
            vSelect,
            factura,
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

            },

        },
        methods: {
            generarfac(id,index){
                
                this.generarf=id
                this.arrayfacturas[index].empresa='Seleccione datos de facturación'
            },
            nogenerar(){
                this.generarf=-1
            },
            generarFactura(factura)
            {
                let me = this;
                var userj=me.user
                this.factura.user_id=userj.id
                this.factura.lineas.forEach(l => {
                    if(!l.hasOwnProperty('detalles')){
                        l.detalles=0;
                    }else{
                    }
                    
                });
                
                const data = new FormData()
                data.set('data',JSON.stringify(factura))
                axios.post('/factura/registrar',data)
                .then(function (response) {
                    console.log(response)
                    me.$emit('listarFacturas', '1,'+this.factura.estado+',"estado",50')
                    me.factura={'fecha':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'forma_pago':'Contado','transportadora':' ','estado':2,'cliente':{'contactos':[],'empresas':[],'envios':[]},'productos':[], 'abono':0,'saldo':0,'subtotal':0, 'descuento':0, 'impuesto':0, 'total':0}
                    me.generarf=-1
               }).catch(function (error) {
                    console.log(error);
                });
                me.$emit('ocultarDetalle', 1)
            },
           
            mostrarListado(value){
                this.ListarFacturas(1,this.buscar,this.criterio,this.per_page);
                this.empresa={"id":0,"favorito":0,"razonsocial":"","tipo_persona":"Juridica","tipo_documento":"","numero":"","digito":"","direccion":"","telefono":"","correo":"","ciudad":"","departamento":"Valle del Cauca","pais":"Colombia","actividad":"","responsable":"","clientes":[]}
                
                this.seccion=value
                this.listado=1

            },
            imprimirfactura(factura){
              let me=this;
              var ped=encodeURIComponent(JSON.stringify(factura))
              console.log(ped)
              axios({
              url: '/imprimirfactura?id='+factura.id,         
              method: 'GET',
              responseType: 'blob', // important
              }).then((response) => {
                console.log(response)
                  const url = window.URL.createObjectURL(new Blob([response.data]));
                  const link = document.createElement('a');
                  link.href = url;
                  link.setAttribute('download', 'factura '+factura.cliente.razonsocial+' '+factura.id+'.pdf');
                  document.body.appendChild(link);
                  link.click();
              });
          },
             cambiarEstado(factura,estado){
                var me=this
                var userj=me.user
                axios.put('/comprobante/cambiarEstadoFactura',{
                    'id':factura.id,
                    'user_id':userj.id,
                    'estado':estado,
                })
                .then(function (response) {
                    console.log(response)
                    me.ListarFacturas(1,response.data.estado,'estado',me.per_page);
                }).catch(function (error) {
                    console.log(error);
                });
            },
            cambiarPagina(buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.ListarFacturas(1,buscar,criterio,me.per_page);
            },
            generar(){
                 let me=this;
                var url= '/factura/generar';
                axios.post(url).then(function (response) {
                    console.log(response)
                    
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            buscarLive() {
                 clearTimeout(this._searchTimer);
                 let me = this;
                 this._searchTimer = setTimeout(() => {
                     me.ListarFacturas(1, me.buscar, me.criterio, me.per_page);
                 }, 250);
             },
             ListarFacturasp(value){
                console.log(value)
                this.ListarFacturas(value)
            },
             filtrarFacturas(tipo, estado) {
                 this.tipoFiltro = tipo !== undefined ? tipo : this.tipoFiltro;
                 this.estadoFiltro = estado !== undefined ? estado : this.estadoFiltro;
                 this.ListarFacturas(1, this.buscar, this.criterio, this.per_page);
             },
             ListarFacturas(page,buscar,criterio,per_page,tipo,estado){
                 let me=this;
                 if (tipo !== undefined) {
                     me.tipoFiltro = tipo;
                 }
                 if (estado !== undefined) {
                     me.estadoFiltro = estado;
                 }
                 var pBuscar = (buscar !== undefined) ? buscar : me.buscar;
                 if (pBuscar === 0 || pBuscar === '0') {
                     pBuscar = '';
                 }
                 var pCriterio = (criterio !== undefined) ? criterio : me.criterio;
                 var pPerPage = (per_page !== undefined) ? per_page : me.per_page;

                 var url= '/comprobante/facturas?page=' + page +'&per_page='+ pPerPage +'&buscar='+ encodeURIComponent(pBuscar) + '&criterio='+ pCriterio + '&tipo=' + me.tipoFiltro + '&estado_filtro=' + me.estadoFiltro;
                 axios.get(url).then(function (response) {
                     var respuesta= response.data;
                     me.arrayfacturas = respuesta.comprobantes.data;
                     me.pagination= respuesta.pagination;
                     me.cantProformas = respuesta.cant_proformas || 0;
                     me.cantFacturas = respuesta.cant_facturas || 0;
                     me.cantEnviadas = respuesta.cant_enviadas || 0;
                     me.cantNoEnviadas = respuesta.cant_no_enviadas || 0;
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
                me.ListarFacturas(page,buscar,criterio,me.per_page);
            },
            borrarfactura(id){
                let me=this
                var userj=me.user
                var url= '/factura/borrar?id='+ id+'&user_id='+userj.id;
                axios.delete(url,{'_method': 'DELETE'}).then(function (response) {

                    var respuesta= response.data;
                    console.log(respuesta);
                    me.ListarFacturas(1,'','',me.per_page);
                }).catch(function (error) {
                    console.log(error);
                });
            },
            verfactura(factura){
                this.factura=factura
                this.listado=0
                this.edit=1
            },
            
            mostrarDetalle(){
                this.listado=0;
            },
            ocultarDetalle(val){
                this.listado=val
            },
           
            cerrarModal(){
                this.modal=0;
            },
            verDetallesDIAN(factura) {
                if (factura.factura_electronica) {
                    this.dianDetalle = factura.factura_electronica;
                    this.modalDIAN = 1;
                }
            },
            cerrarModalDIAN() {
                this.modalDIAN = 0;
                this.dianDetalle = null;
            },
            formatJSON(jsonStr) {
                try {
                    return JSON.stringify(JSON.parse(jsonStr), null, 2);
                } catch(e) {
                    return jsonStr;
                }
            },
            formatDate(dateStr) {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleString('es-CO');
            },
            descargarArchivo(tipo, id) {
                window.open(this.dominio + '/factura-electronica/descargar/' + tipo + '/' + id, '_blank');
            },
            transmitirFactura(factura) {
                let me = this;
                me.transmitiendoId = factura.id;
                
                axios.post(me.dominio + '/factura-electronica/transmitir', {
                    'comprobante_id': factura.id
                })
                .then(function (response) {
                    me.transmitiendoId = null;
                    if (response.data.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'La factura ha sido transmitida y aprobada por la DIAN.',
                            icon: 'success',
                            confirmButtonClass: 'btn btn-success'
                        });
                        // Update the invoice in local state
                        factura.factura_electronica = response.data.factura_electronica;
                        me.ListarFacturas(me.pagination.current_page, me.buscar, me.criterio, me.per_page);
                    } else {
                        Swal.fire({
                            title: 'Error de validación',
                            text: response.data.error || 'Ocurrió un error en la transmisión.',
                            icon: 'error',
                            confirmButtonClass: 'btn btn-danger'
                        });
                    }
                })
                .catch(function (error) {
                    me.transmitiendoId = null;
                    let errorMsg = 'Error en el servidor al transmitir a la DIAN.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMsg = error.response.data.error;
                    }
                    Swal.fire({
                        title: 'Error DIAN',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonClass: 'btn btn-danger'
                    });
                });
            }
        },
        watch: {
            show: function(newVal) {
                if (newVal) {
                    this.ListarFacturas(1, this.buscar, this.criterio, this.per_page, this.tipoFiltro, this.estadoFiltro);
                }
            }
        },
        mounted() {
            this.ListarFacturas(1, this.buscar, this.criterio, this.per_page, this.tipoFiltro, this.estadoFiltro);
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
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

</style>
