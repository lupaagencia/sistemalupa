<template>
    <div class="contenedor">
        <div class="seccion" role="document">
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Datos Envio</div>
                    <div class="subseccion">
                        <button class="btn btn-danger boton-principal" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary boton-principal"  @click="registrarActivo('nuevo')">Guardar y nuevo</button>
                        <button class="btn btn-success boton-principal" @click="registrarActivo('cerrar')">Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Activo
                    </div>

                    <div class="seccion-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="email-input">Activo</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="activo.activo" class="form-control" placeholder="Escriba el activo">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Datos del activo</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="activo.datos_activo" class="form-control" placeholder="Serial, Modelo">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="text-input">Tipo</label>
                                <div class="col-md-9">
                                     <select v-model="activo.tipo" class="form-control">
                                         <option value="Impresora Offset">Impresora Offset</option>
                                         <option value="Impresora Flexo">Impresora Flexo</option>
                                         <option value="Troquelado">Troqueladora</option>
                                         <option value="Terminado">Terminado</option>
                                         <option value="Plastificadora">Plastificadora</option>
                                         <option value="Guillotina">Guillotina</option>
                                         <option value="Mesas">Mesas</option>
                                         <option value="Repisas">Repisas</option>
                                         <option value="Muebles y Enseres">Muebles y Enseres</option>
                                         <option value="Equipo de computo">Equipo de computo</option>
                                         <option value="Equipos Electronicos">Equipos Electronicos</option>
                                         <option value="Herramientas">Herramientas</option>
                                     </select>  
                                </div>  
                            </div>
                        
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Descripción</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="activo.descripcion" class="form-control" placeholder="Descripción">
                                </div>
                            </div>
                            
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Ubicación</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="activo.ubicacion" class="form-control" placeholder="Ubicación">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Responsable</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="activo.responsable" class="form-control" placeholder="Responsable">
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Clasificación</label>
                                <div class="col-md-9">
                                    <select v-model="activo.clasificacion" class="form-control">
                                        <option value="Maquinaria">Maquinaria</option>
                                        <option value="Muebles y Enseres">Muebles y Enseres</option>
                                        <option value="Herramienta">Herramienta</option>
                                        <option value="Complemento">Complemento</option>
                                        <option value="Oficina">Oficina</option>
                                        
                                    </select>  
                                </div>
                            </div>
                            <div class="form-group row col-md-6">
                                <label class="col-md-3 form-control-label" for="input">Estado</label>
                                <div class="col-md-9">
                                    <select v-model="activo.estado" class="form-control">
                                        <option value="Bueno">Bueno</option>
                                        <option value="Reparacion">Reparación</option>
                                        <option value="Mantenimiento">En matenimiento</option>
                                        <option value="Desuso">En desuso</option>
                                        <option value="Malo">Malo</option>
                                        
                                        
                                    </select>  
                                </div>
                            </div>
                           
                        </form>
                    </div>
                </div>
                    
                <div class="contenedor-footer">
                    <div class="subseccion">
                        <button class="btn btn-danger boton-principal" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary boton-principal"  @click="registrarActivo('nuevo')">Guardar y nuevo</button>
                        <button class="btn btn-success boton-principal" @click="registrarActivo('cerrar')">Guardar</button>
                    </div>
                   
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>
<script>
    export default {
        props:['activo'],
        data (){
            return {
                arrayActivos:[],
                show:'activos',
                tituloModal : '',
                errorPersona : 0,
                errorMostrarMsjPersona : [],
                
            }
        },
        computed:{
           
        },
        methods : {
         
          
            registrarActivo(guardar){
               
                
               let me = this;
               const activo = new FormData()
               if(this.activo.id){
                   activo.set('id',this.activo.id)
               }else{
                   activo.set('id',0)
               }
               activo.set('activo',this.activo.activo)
               activo.set('tipo',this.activo.tipo)
               activo.set('ubicacion',this.activo.ubicacion)
               activo.set('descripcion' , this.activo.descripcion)
               activo.set('responsable', this.activo.responsable)
               activo.set('estado' , this.activo.estado)
               activo.set('clasificacion' , this.activo.clasificacion)
               activo.set('grupo',this.activo.grupo)
               activo.set('datos_activo',this.activo.datos_activo)
               axios.post('/activo/registrarActivo',activo).then(function (response) {
                   if(guardar=='cerrar'){
                       me.cerrarModal();
                   }else{
                       me.seccion='nuevo';
                       me.activo={
                            tipo:'',
                            activo:'',
                            descripcion:'',
                            ubicacion:'Planta',
                            responsable:0,
                            clasificacion:'',
                            estado:'bueno',
                            grupo:'',
                            datos_activo:'',
                       }

                   }
               }).catch(function (error) {
                   console.log(error);
               });
           },
           
            cerrarModal(){
                this.$emit('mostrarListado', 'listado')
            },
           
           
             validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];
                if (!this.envio.contacto) this.errorMostrarMsjPersona.push("El nombre de la persona no puede estar vacío.")
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
