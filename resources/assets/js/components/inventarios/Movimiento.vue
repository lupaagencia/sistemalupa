<template>
    <div class="contenedor" v-scroll="handleScroll">
        <div class="seccion" role="document">
           
             <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Movimiento de {{movimiento.costois.nombre}}</div>
                    <div class="subseccion">
                        <button class="btn btn-danger boton-principal" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary boton-principal" @click="registrarMateriaPrima('nuevo')">Guardar y nuevo</button>
                        <button class="btn btn-success boton-principal" @click="registrarMateriaPrima('cerrar')">Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Materia prima
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="contenedor-seccion">
                                <div class="contenedor-header productomateriasPrimas">
                                    <h4>Producto: <font>{{movimiento.costois.nombre}}</font></h4>
                                </div>
                            </div>
                           
                            <div class="form-group row">
                                <div class="col-md-3">
                                    <label class="col-md-12 form-control-label" for="email-input">Tipo de movimiento</label>
                                
                                    <div class="col-md-12">
                                        <select v-model="movimiento.tipo" class="form-control">
                                            <option value="entrada">Entrada</option>
                                            <option value="salida">Salida</option>
                                        </select>  
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="col-md-12 form-control-label" for="email-input">Cantidad</label>
                                    <div class="col-md-12">
                                        <input type="text" v-model="movimiento.cantidad" class="form-control" placeholder="Ubicación">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="col-md-12 form-control-label" for="email-input">Costo Unitario</label>
                                    <div class="col-md-12">
                                        <input type="text" v-model="movimiento.costo_unitario" class="form-control" placeholder="Detalles">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="col-md-12 form-control-label" for="input">Costo total</label>
                                    <div class="col-md-12">
                                        <input type="text" v-model="movimiento.costo_total" class="form-control" placeholder="Uso">
                                    </div>
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
                    <button class="btn btn-danger boton-principal" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary boton-principal"  @click="registrarMateriaPrima('nuevo')">Guardar y nuevo</button>
                        <button class="btn btn-success boton-principal" @click="registrarMateriaPrima('cerrar')">Guardar</button>
                   
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
        props :['movimiento'],
        data (){
            
            return {
                show:'materias',
                scroll:0,
            }
        },
        components:{
           
        },
        computed:{
           
        },
        methods : {
           
            handleScroll: function (evt, el) {
                const targetEl = el || this.$el;
                if (!targetEl) return;
                const rect = targetEl.getBoundingClientRect();
                const vtop = rect.top + window.scrollY;
                this.scroll = window.scrollY - vtop;
            },
            registrarMateriaPrima(guardar){
                let me = this;
                const movimiento = new FormData()
                if(this.movimiento.id){
                    movimiento.set('id',this.movimiento.id)
                }else{
                    movimiento.set('id',0)
                }
                movimiento.set('id',this.movimiento.id)
                movimiento.set('inventarios_materia_prima_id',this.movimiento.inventarios_materia_prima_id)
                movimiento.set('proveedores_id', this.movimiento.costois.idproveedor)
                movimiento.set('costois_id' , this.movimiento.costois.id)
                movimiento.set('tipo',this.movimiento.tipo)
                movimiento.set('cantidad',this.movimiento.cantidad)
                movimiento.set('costo_unitario' , this.movimiento.costo_unitario)
                movimiento.set('costo_total' , this.movimiento.costo_total)
                axios.post('/movimientos/registrar',movimiento).then(function (response) {
                    console.log(response)
                    if(guardar=='cerrar'){
                        me.cerrarModal();
                    }else{
                        me.seccion='movimiento';
                        me.movimiento.cantidad=0
                        me.movimiento.tipo='entrada'
                        me.movimiento.costo_unitario=0
                        me.movimiento.costo_total=0
                    }
                }).catch(function (error) {
                    console.log(error);
                });
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
