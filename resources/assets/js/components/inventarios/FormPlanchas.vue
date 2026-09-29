<template>
    <div class="contenedor-seccion">
        <div >
            <form action="" method="post" enctype="multipart/form-data" class="form-horizontal row">
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Nombre</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.nombre" class="form-control" placeholder="Nombre contacto">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Teléfono</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.telefono" class="form-control" placeholder="Teléfono">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Teléfono particular</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.telefono_particular" class="form-control" placeholder="Teléfono particular">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Correo electrónico</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.correo" class="form-control" placeholder="Correo electrónico">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Tipo de contacto</label>
                    <div class="col-md-9">
                        <input type="email" v-model="contacto.tipo_contacto " class="form-control" placeholder="Quien es en la organización">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Cargo</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.cargo" class="form-control" placeholder="Cargo">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Nombre asistente</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.nombre_asistente" class="form-control" placeholder="Nombre asistente">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Teléfono asistente</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.telefono_asistente" class="form-control" placeholder="Teléfono asistente">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Fecha nacimiento</label>
                    <div class="col-md-9">
                        <input type="text" v-model="contacto.fecha_nacimiento" class="form-control" placeholder="Teléfono de Contacto">
                    </div>
                </div>
                <div v-show="errorPersona" class="form-group row div-error">
                    <div class="text-center text-error">
                        <div v-for="error in errorMostrarMsjPersona" :key="error" v-text="error">

                        </div>
                    </div>
                </div>
            </form>
            <button class="btn btn-success" @click="addContacto">+</button>
        </div>
    </div>
</template>
<script>
    export default {
        data (){
            return {
                contacto:{'nombre':'','telefono':'','telefono_particular':'','correo':'','tipo_contacto':'','cargo':'','nombre_asistente':'','telefono_asistente':'','fecha_nacimiento':'2023-01-01'},
                errorMostrarMsjPersona:[],
                errorPersona:''
            }
        },
        computed:{
           
        },
        methods : {
            addContacto(){
                this.$emit('valorFormulario', [this.contacto, '2', 'contacto'])
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
