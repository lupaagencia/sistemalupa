<template>
      <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalp}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
            <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seleccione un proveedor</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalp()">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <template v-if="arrayProveedor">
                                <div class="list-group">
                                    <a href="#" 
                                    class="list-group-item list-group-item-action" 
                                    :class="{'active' : seleccionado}" 
                                    v-for="(proveedor,index) in arrayProveedor" 
                                    :key="proveedor.id" 
                                    v-text="proveedor.nombre"
                                    @click="getDatosProveedor(proveedor,index)">
                                    </a> 
                                </div>
                            </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="cerrarModalp()" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
</template>
<script>
 props:['modal']

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

   