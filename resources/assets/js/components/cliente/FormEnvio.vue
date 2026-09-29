<template>
    <div class="contenedor-seccion">
        <div >
            <form action="" method="post" enctype="multipart/form-data" class="form-horizontal row">
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Persona que recibe</label>
                    <div class="col-md-9">
                        <input type="text" v-model="envio.contacto" class="form-control" placeholder="Nombre contacto">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="text-input">Tipo documento</label>
                    <div class="col-md-9">
                        <select v-model="envio.tipo_documento" class="form-control">
                            <option value="NIT">NIT</option>
                            <option value="CC">CC</option>
                            <option value="PASAPORTE">PASAPORTE</option>
                        </select>  
                    </div>  
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Número de documento</label>
                    <div class="col-md-9">
                        <input type="text" v-model="envio.documento" class="form-control" placeholder="Número de documento">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">teléfono</label>
                    <div class="col-md-9">
                        <input type="text" v-model="envio.telefono" class="form-control" placeholder="Teléfono">
                    </div>
                </div>
                
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Dirección destino</label>
                    <div class="col-md-9">
                        <input type="text" v-model="envio.direccion" class="form-control" placeholder="Dirección destino">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Ciudad destino</label>
                    <div class="col-md-9">
                        <input type="text" v-model="envio.ciudad" class="form-control" placeholder="Ciudad destino">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">País destino</label>
                    <div class="col-md-9">
                        <input type="text" v-model="envio.pais" class="form-control" placeholder="País destino">
                    </div>
                </div>
               
                <div v-show="errorPersona" class="form-group row div-error">
                    <div class="text-center text-error">
                        <div v-for="error in errorMostrarMsjPersona" :key="error" v-text="error">

                        </div>
                    </div>
                </div>
            </form>
            <button class="btn btn-success" @click="addEnvio">+</button>
        </div>
    </div>
</template>
<script>
    export default {
        data (){
            return {
                envio:{'contacto':'','tipo_documento':'CC','documento':'','telefono':'','direccion':'','ciudad':'','pais':'Colombia'},
                errorMostrarMsjPersona:[],
                errorPersona:''
            }
        },
        computed:{
           
        },
        methods : {
            addEnvio(){
                this.$emit('valorFormulario', [this.envio, '2', 'envio'])
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
        display: block !important;
        opacity: 1 !important;
        position: fixed !important;
        background-color: #3c29297a !important;
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
