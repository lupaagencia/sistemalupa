<template>
    <div class="contenedor-seccion">
        <div >
            <form action="" method="post" enctype="multipart/form-data" class="form-horizontal row">
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
                            <option value="impresora offset">Impresora Offset</option>
                            <option value="impresora flexo">Impresora Flexo</option>
                            <option value="troqueladora">Troqueladora</option>
                            <option value="plastificadora">Plastificadora</option>
                            <option value="guillotina">Guillotina</option>
                            <option value="mesas">Mesas</option>
                            <option value="repisas">Repisas</option>
                            <option value="muebles">Muebles y Enseres</option>
                            <option value="computo">Equipo de computo</option>
                            <option value="electronicos">Equipos Electronicos</option>
                            <option value="herramientas">Herramientas</option>
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
                        <select v-model="activo.tipo" class="form-control">
                            <option value="Maquinaria">Impresora Offset</option>
                            <option value="Muebles y Enseres">Impresora Flexo</option>
                            <option value="Herramienta">Troqueladora</option>
                            <option value="Complemento">Plastificadora</option>
                            <option value="Oficina">Guillotina</option>
                            
                        </select>  
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Estado</label>
                    <div class="col-md-9">
                        <select v-model="activo.tipo" class="form-control">
                            <option value="Bueno">Bueno</option>
                            <option value="Reparacion">Reparación</option>
                            <option value="Mantenimiento">En matenimiento</option>
                            <option value="Desuso">En desuso</option>
                            <option value="Malo">Malo</option>
                            
                            
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
</template>
<script>
    export default {
        data (){
            return {
                activo:{},
                errorMostrarMsjPersona:[],
                errorPersona:''
            }
        },
        computed:{
           
        },
        methods : {
            addEnvio(){
                this.$emit('valorFormulario', [this.activo, '2', 'activo'])
            }

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
