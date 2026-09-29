<template>
     <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
        <div class="modal-dialog" >
            <div class="modal-content" :style="'top:'+scroll+'px'">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccione un empleado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModale()">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <template >
                        <div class="list-group">
                            <a href="#" 
                            class="list-group-item list-group-item-action" 
                            
                            v-for="(empleado,index) in empleados" 
                            :key="empleado.id" 
                            v-text="empleado.nombre+' '+empleado.apellido"
                            @click="getDatosempleado(empleado,index)">
                            </a> 
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="cerrarModale()" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
  var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
 export default {
    props:{
        empleados:null,
        modal:null,
        scroll:0
    },
    data(){
        return{
            empleadoSeleccionado:'',
            fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
        }
    },
   
    methods:{
        getempleadobyid(idempleado){
            let me=this;
            var url= me.dominio+'/empleado/selectempleado?id='+idempleado;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        getDatosempleado(val1, index){
            this.$emit('empleadoSeleccionado', val1)
            this.modal=0
        },
        cerrarModale(){
            this.modal=0
        }
    },
    mounted() {
    },
}

</script>

<style>    
     .mostrar{
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(0,0,0,0.5) !important;
        overflow-y: hidden !important;
        z-index: 10500 !important;
    }
    .mostrar .modal-dialog,
    .modal-bajo {
        margin: 10px auto !important;
        top: 0 !important;
        align-self: flex-start !important;
        max-height: calc(100vh - 20px) !important;
        height: calc(100vh - 20px) !important;
        display: flex !important;
        flex-direction: column !important;
    }
</style>