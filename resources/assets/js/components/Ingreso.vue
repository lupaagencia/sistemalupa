<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="card">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" :class="{'active': show=='cot'}" @click="mostrarTab('cot')" id="procesos-tab" data-bs-toggle="tab" data-bs-target="#procesos" type="button" role="tab" aria-controls="procesos" aria-selected="true">Cotizaciones</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" :class="{'active': show=='pedidos'}" @click="mostrarTab('pedidos')" id="procesos-tab" data-bs-toggle="tab" data-bs-target="#procesos" type="button" role="tab" aria-controls="procesos" aria-selected="true">Pedidos</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" :class="{'active': show=='facturas'}" @click="mostrarTab('facturas')" id="facturas-tab" data-bs-toggle="tab" data-bs-target="#facturas" type="button" role="tab" aria-controls="pagos" aria-selected="false">Facturas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" :class="{'active': show=='remisiones'}" @click="mostrarTab('remisiones')" id="remisiones-tab" data-bs-toggle="tab" data-bs-target="#remisiones" type="button" role="tab" aria-controls="remisiones" aria-selected="false">Remisiones</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" :class="{'active': show=='cuentas'}" @click="mostrarTab('cuentas')" id="cuentas-tab" data-bs-toggle="tab" data-bs-target="#cuentas" type="button" role="tab" aria-controls="cuentas" aria-selected="false">Cuentas de Cobro</button>
                    </li>
                </ul>
               
                <div class="tab-content" id="myTabContent">
                    <div v-if="show=='cot'" id="cotizaciones" role="tabpanel" aria-labelledby="pedidos-tab">
                        <template>
                            <cotizaciones :show='show'></cotizaciones>
                        </template>
                    </div>
                    <div v-else-if="show=='pedidos'" id="pedidos" role="tabpanel" aria-labelledby="pedidos-tab">
                        <template>
                            <pedidos :user="user" :show='show'></pedidos>
                        </template>
                    </div>
                     <div v-else-if="show=='facturas'" id="facturas" role="tabpanel" aria-labelledby="facturas-tab">
                        <template>
                            <facturas :user="user" :show='show'></facturas>
                        </template>
                    </div>
                    <div v-else-if="show=='remisiones'" id="remisiones" role="tabpanel" aria-labelledby="remisiones-tab">
                        <template>
                            <remisiones :user="user"></remisiones>
                        </template>
                    </div>
                    <div v-else id="cuentas" role="tabpanel" aria-labelledby="cuentas-tab">
                        <template>
                            <cuentas-cobro :user="user"></cuentas-cobro>
                        </template>
                    </div>
                </div>
                    <!--Fin Listado-->
                   
            </div>
          
           
            
            <!--Fin del modal-->
        </main>
</template>

<script>
    import 'vue-select/dist/vue-select.css';
    import vSelect from 'vue-select';
    import pedidos from './Pedidos'
    import cotizaciones from './Cotizaciones'
    import facturas from './Facturas'
    import remisiones from './Remisiones'
    import cuentasCobro from './CuentasCobro'
    export default {
         props:['user'],
        data (){
            return {
                show: 'pedidos',
                ingreso_id: 0,
                idproveedor:0,
                nombre : '',
                tipo_comprobante : 'BOLETA',
                serie_comprobante : '',
                num_comprobante : '',
                impuesto: 0.18,
                total:0.0,
                totalImpuesto:0.0,
                totalParcial:0.0,
                arrayPedidos : [],
                arrayProveedor: [],
                arrayDetalle : [],
                arrayArticulo:[],
                idarticulo:0,
                articulo:0,
                codigo:0,
                precio:0,
                cantidad:0,
                listado:1,
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                errorPedido : 0,
                errorMostrarMsjPedido : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : 'num_comprobante',
                buscar : '',
                criterioA:'nombre',
                buscarA:''
               
            }
        },
        components: {
            vSelect,
            pedidos,
            cotizaciones,
            facturas,
            remisiones,
            cuentasCobro
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
            calcularTotal(){
                var resultado=0
                for(var i=0;i<this.arrayDetalle.length;i++){
                    resultado=resultado+(this.arrayDetalle[i].precio*this.arrayDetalle[i].cantidad)
                }
                return resultado
            }

        },
        methods : {
            mostrarTab(show){
                this.show=show
            },
            listarPedidos(page,buscar,criterio){
                let me=this;
                var url= '/comprobante?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPedidos = respuesta.comprobantes.data;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectProveedor(search,loading){
                let me=this;
                loading(true)

                var url= '/proveedor/selectProveedor?filtro='+search;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    q: search
                    me.arrayProveedor=respuesta.proveedores;
                    loading(false)
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosProveedor(val1){
                let me = this;
                me.loading = true;
                me.idproveedor = val1.id;
            },
            buscarArticulo(){
                let me=this;
                var url= '/articulo/buscarArticulo?filtro='+ me.codigo;
                axios.get(url).then(function (response) {
                    var respuesta=response.data
                    me.arrayArticulo=respuesta.articulos
                    if(me.arrayArticulo.length>0){
                        me.articulo=me.arrayArticulo[0]['nombre']
                        me.idarticulo=me.arrayArticulo[0]['id']
                        me.abrirModal()
                    }else{
                        me.articulo='No existe Artículo'
                        me.idarticulo=0
                       
                        
                    }
                })
            },
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarPedidos(page,buscar,criterio);
            },
            encuentra(id){
                var sw=0
                for(var i=0;i<this.arrayDetalle.length;i++){
                    if(this.arrayDetalle[i].idarticulo==id){
                        sw=true
                    }
                    return sw
                }
            },
            agregarDetalle(){
                 let me=this
                if(me.idarticulo==0 || me.cantidad==0 || me.precio==0){

                }else{
                    if(me.encuentra(me.idarticulo)){
                        Swal.fire({
                            icon:'error',
                            type:'error',
                            title:'Error...',
                            text:'Ese artículo ya se encuentra agregado'
                    })
                      
                    }else{
                        me.arrayDetalle.push({
                            idarticulo:me.idarticulo,
                            articulo:me.articulo,
                            cantidad:me.cantidad,
                            precio:me.precio
                        }) 
                        me.codigo=""
                        me.idarticulo=0
                        me.articulo=""
                        me.cantidad=0
                        me.precio=0
                    }
                }
               
                
            },
            agregarDetalleModal(data=[]){
                let me=this
                if(me.encuentra(data['id'])){
                        Swal.fire({
                            icon:'error',
                            type:'error',
                            title:'Error...',
                            text:'Ese artículo ya se encuentra agregado'
                    })
                      
                    }else{
                        me.arrayDetalle.push({
                            idarticulo:data['id'],
                            articulo:data['nombre'],
                            cantidad:1,
                            precio:1
                        }) 
                       
                    }
            },
            listarArticulo (page,buscar,criterio){
                let me=this;
                var url= '/articulo?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayArticulo = respuesta.articulos;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
             
            },
            eliminarDetalle(index){
                let me=this
                me.arrayDetalle.splice(index,1)
            },
            registrarPersona(){
                if (this.validarPersona()){
                    return;
                }
                
                let me = this;

                axios.post('/user/registrar',{
                    'nombre': this.nombre,
                    'tipo_documento': this.tipo_documento,
                    'num_documento' : this.num_documento,
                    'direccion' : this.direccion,
                    'telefono' : this.telefono,
                    'email' : this.email,
                    'idrol' : this.idrol,
                    'usuario': this.usuario,
                    'password': this.password

                }).then(function (response) {
                    me.cerrarModal();
                    me.listarPersona(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                });
            },
           
            validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];

                if (!this.nombre) this.errorMostrarMsjPersona.push("El nombre de la pesona no puede estar vacío.");
                if (!this.usuario) this.errorMostrarMsjPersona.push("El nombre de usuario no puede estar vacío.");
                if (!this.password) this.errorMostrarMsjPersona.push("La password del usuario no puede estar vacía.");
                if (this.idrol==0) this.errorMostrarMsjPersona.push("Seleccione una Role.");
                if (this.errorMostrarMsjPersona.length) this.errorPersona = 1;

                return this.errorPersona;
            },
            mostrarDetalle(){
                this.listado=0;
            },
            ocultarDetalle(){
                this.listado=1;
            },
            cerrarModal(){
                this.modal=0;
            },
            abrirModal(modelo, accion, data = []){
                this.arrayArticulo=[]
                this.modal = 1;
                this.tituloModal = 'Seleccione 1 o varios artículos';
            },
            desactivarUsuario(id){
               swal({
                title: 'Esta seguro de desactivar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/desactivar',{
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1,'','nombre');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Do you want to continue',
                            icon: 'error',
                            confirmButtonText: 'Cool'
                        })
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
               swal({
                title: 'Esta seguro de activar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
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
            },
        },
        mounted() {
            this.listarPedidos(1,this.buscar,this.criterio);
            this.listarArticulo(1,this.buscar,this.criterio);
        }
    }
</script>
<style>   
    .header-comprobante{
        font-size: 18px;
    }
    .header-comprobante td{
        padding:3px;
    }
    .header-comprobante th{
        color:#007910;
        padding:2px;
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
