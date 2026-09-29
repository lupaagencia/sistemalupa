<template>
    <div class="contenedor" v-scroll="handleScroll">
        <div class="seccion" role="document">
            
            <!-- Modal cliente -->
            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalc}" :style="'z-index:10000; top:'+scroll+'px; height:100vh; position:absolute !important; display: ' + (modalc ? 'block' : 'none') + ';'" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog" >
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Seleccione un cliente</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalc()">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <template v-if="arrayClientes">
                                <div class="list-group">
                                    <a href="#" 
                                    class="list-group-item list-group-item-action" 
                                    :class="{'active' : materiasPrimas.cliente.id == cliente.id}" 
                                    v-for="(cliente,index) in arrayClientes" 
                                    :key="index" 
                                    v-text="cliente.razonsocial"
                                    @click="getDatosCliente(cliente,index)">
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
            <!-- Cierre modal cliente -->

           
            
             <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Crear Plancha</div>
                    <div class="subseccion">
                        <button class="btn btn-danger boton-principal" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary boton-principal"  @click="registrarPlancha('nuevo')">Guardar y nuevo</button>
                        <button class="btn btn-success boton-principal" @click="registrarPlancha('cerrar')">Guardar</button>
                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Materia prima
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="contenedor-seccion">
                                    <div class="contenedor-header clientemateriasPrimas">
                                        <h4>Cliente: <font>{{materiasPrimas.cliente.razonsocial}}</font></h4>
                                        <div>
                                            <label>buscar cliente por nombre(*) <span style="color:red" v-show="materiasPrimas.cliente.id==0">(*Seleccione)</span> </label>
                                            <div class="form-group row">
                                                <div class="">
                                                    <div class="form-inline">
                                                        <input type="text" class="form-control" v-model="buscar_cliente" @keyup="selectCliente('nuevo')" placeholder="Ingrese nombre del cliente">
                                                    </div> 
                                                </div>
                                            
                                            </div>
                                        </div>
                                    </div>
                                   
                                </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="materiasPrimas.referencia" class="form-control" placeholder="Nombre del material">                                        
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Ubicación</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="materiasPrimas.ubicacion" class="form-control" placeholder="Ubicación">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Detalles</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="materiasPrimas.detalles" class="form-control" placeholder="Detalles">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Estado</label>
                               
                                <div class="col-md-9">
                                    <select v-model="materiasPrimas.estado" class="form-control">
                                        <option value="Buena">Buena</option>
                                        <option value="Mala">Mala</option>
                                        <option value="Cabiar proximamente">Cambiar proximamente</option>
                                        <option value="Incompleta">Incompleta</option>
                                    </select>  
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Maquina</label>
                                <div class="col-md-9">
                                    <select v-model="materiasPrimas.uso" class="form-control">
                                        <option value="Gto 52">Gto 52</option>
                                        <option value="Sorm">Sorm</option>
                                        <option value="Sorm">Otra</option>
                                     
                                    </select>  
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Cantidad</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="materiasPrimas.cantidad" class="form-control" placeholder="Cantidad">
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

                    
                <div class="contenedor-footer">
                    <div class="subseccion">
                        <button class="btn btn-danger boton-principal" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary boton-principal"  @click="registrarPlancha('nuevo')">Guardar y nuevo</button>
                        <button class="btn btn-success boton-principal" @click="registrarPlancha('cerrar')">Guardar</button>
                    </div>
                   
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>
<script>
     Vue.directive('scroll', {
    inserted: function (el, binding) {
        let f = function (evt) {
        if (binding.value(evt, el)) {
            window.removeEventListener('scroll', f)
        }
        }
        window.addEventListener('scroll', f)
    }
    })
    export default {
        props :['materiasPrimas'],
        data (){
            
            return {
                show:'materias',
                arraymateriasPrimas: [],
                errorPersona : 0,
                errorMostrarMsjPersona : [],
                buscar_cliente:'',
                buscar_material:'',
                modalc:0,
                modale:0,
                scroll:0,
                arrayClientes:[],
                arrayMateriales:[],
            }
        },
        components:{
           
        },
        computed:{
           
        },
        methods : {
            selectMaterial(){
                let me=this;
                var url= '/costop/selectInsumos?filtro='+this.buscar_material;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayMateriales=respuesta.insumos;
                    me.modale=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectCliente(){
                let me=this;
                
                var url= '/cliente/selectClientes?filtro='+this.buscar_cliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    
                    me.arrayClientes=respuesta.clientes;
                    me.modalc=1;
                    me.$nextTick(() => {
                        me.handleScroll(null, me.$el);
                    });
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
        
            cerrarModalc(){
                this.modalc=0
                this.arrayClientes=[]
            },
            getDatosCliente(val1, index){
                let me = this;
                me.materiasPrimas.cliente=val1;
                me.buscar_cliente=val1.nombre
                me.arrayClientes=[];
                me.modalc=0
            },
           
            handleScroll: function (evt, el) {
                const targetEl = el || this.$el;
                if (!targetEl) return;
                const rect = targetEl.getBoundingClientRect();
                const vtop = rect.top + window.scrollY;
                this.scroll = window.scrollY - vtop -150;
            },
            registrarPlancha(guardar){
               
                
                let me = this;
                if(me.validarPersona()){
                    return
                }
                const materiasPrimas = new FormData()
                if(this.materiasPrimas.id){
                    materiasPrimas.set('id',this.materiasPrimas.id)
                }else{
                    materiasPrimas.set('id',0)
                }
                materiasPrimas.set('referencia',this.materiasPrimas.referencia)
                materiasPrimas.set('tipo',this.materiasPrimas.tipo)
                materiasPrimas.set('ubicacion',this.materiasPrimas.ubicacion)
                materiasPrimas.set('asignado_id' , this.materiasPrimas.cliente.id)
                materiasPrimas.set('detalles', this.materiasPrimas.detalles)
                materiasPrimas.set('estado' , this.materiasPrimas.estado)
                materiasPrimas.set('uso' , this.materiasPrimas.uso)
                materiasPrimas.set('cantidad',this.materiasPrimas.cantidad)
                materiasPrimas.set('cambio',this.materiasPrimas.cambio)
                axios.post('/inventarios/registrarPlancha',materiasPrimas).then(function (response) {
                    if(guardar=='cerrar'){
                        me.cerrarModal();
                    }else{
                        me.seccion='nuevo';
                        me.materiasPrimas={
                            cliente:{razonsocial:'',id:0},
                            costois:{nombre:''},
                            referencia:'',
                            tipo:'Papel',
                            ubicacion:'Planta',
                            detalles:'',
                            estado:'Disponible',
                            uso:'',
                            cantidad:0,
                            unidad:'',
                            cambio:''
                        }

                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
             validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];
                if (!this.materiasPrimas.cliente.razonsocial) this.errorMostrarMsjPersona.push("El nombre de la persona no puede estar vacío.")
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
        position: relative !important;
        
    }
    .contenedor .modal-body {
        max-height: 350px;
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
