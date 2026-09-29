<template>
    <div class="contenedor-seccion">
        <div >
            <form action="" method="post" enctype="multipart/form-data" class="form-horizontal row">
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Razón social</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.razonsocial" class="form-control" placeholder="Nombre de la empresa">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="text-input">Tipo persona</label>
                    <div class="col-md-9">
                        <select v-model="empresa.tipo_persona" class="form-control">
                            <option value="Natural">Natural</option>
                            <option value="Juridica">Jurídica</option>
                        </select>   
                    </div> 
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="text-input">Tipo documento</label>
                    <div class="col-md-9">
                        <select v-model="empresa.tipo_documento" class="form-control">
                            <option value="NIT">NIT</option>
                            <option value="CC">CC</option>
                            <option value="PASAPORTE">PASAPORTE</option>
                        </select>  
                    </div>  
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Número de documento</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.numero" class="form-control" placeholder="Número de documento">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Dígito de verificación</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.digito" class="form-control" placeholder="Dígito de verificación">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Dirección</label>
                    <div class="col-md-9">
                        <input type="email" v-model="empresa.direccion" class="form-control" placeholder="Dirección">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Telefono de la empresa</label>
                    <div class="col-md-9">
                        <input type="email" v-model="empresa.telefono" class="form-control" placeholder="Telefono">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="email-input">Correo electrónico</label>
                    <div class="col-md-9">
                        <input type="email" v-model="empresa.correo" class="form-control" placeholder="Correo electrónico">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Ciudad</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.ciudad" class="form-control" placeholder="Ciudad">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Departamento</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.departamento" class="form-control" placeholder="Departamento">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">País</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.pais" class="form-control" placeholder="pais">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3 form-control-label" for="input">Número Actividad económica</label>
                    <div class="col-md-9">
                        <input type="text" v-model="empresa.actividad" class="form-control" placeholder="Número de la actividad económica">
                    </div>
                </div>
                <div class="form-group row col-md-6">
                    <label class="col-md-3  form-control-label" for="text-input">Respocable de IVA</label>
                    <div class="col-md-9">
                        <select v-model="empresa.responsable" class="form-control" placeholder="Selecione si es resposable de IVA">
                            <option value="Si">Si</option>
                            <option value="No">No</option>
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
            <button class="btn btn-success" @click="addEmpresa">+</button>
        </div>
    </div>
</template>
<script>
    export default {
        data (){
            return {
                empresa:{'razonsocial':'','tipo_persona':'','tipo_documento':'','numero':'','digito':'','direccion':'','telefono':'','ciudad':'Cali','departamento':'Valle del Cauca','pais':'Colombia','correo':'','actividad':'','responsable':''},
                errorMostrarMsjPersona:[],
                errorPersona:''
            }
        },
        computed:{
           
        },
        methods : {
            addEmpresa(){
                this.$emit('valorFormulario', [this.empresa, '2', 'empresa'])
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
