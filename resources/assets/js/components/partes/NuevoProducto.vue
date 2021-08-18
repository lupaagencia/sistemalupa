<template>
<div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" v-text="tituloModal"></h4>
                <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="text-input">Categoría</label>
                        <div class="col-md-9">
                            <select class="form-control" v-model="idcategoria">
                                <option value="0" disabled>Seleccione</option>
                                <option v-for="categoria in arrayCategoria" :key="categoria.id" :value="categoria.id" v-text="categoria.nombre"></option>
                            </select>                                        
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="text-input">Código</label>
                        <div class="col-md-9">
                            <input type="text" v-model="codigo" class="form-control" placeholder="Código de barras">                                        
                            <barcode :value="codigo" :options="{format:'EAN-13'}">
                                Generando código de barras
                            </barcode>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="text-input">Nombre</label>
                        <div class="col-md-9">
                            <input type="text" v-model="nombre" class="form-control" placeholder="Nombre de artículo">                                        
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="text-input">Precio Venta</label>
                        <div class="col-md-9">
                            <input type="number" v-model="precio_venta" class="form-control" placeholder="">                                        
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="text-input">Stock</label>
                        <div class="col-md-9">
                            <input type="number" v-model="stock" class="form-control" placeholder="">                                        
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="email-input">Descripción</label>
                        <div class="col-md-9">
                            <input type="email" v-model="descripcion" class="form-control" placeholder="Ingrese descripción">
                        </div>
                    </div>
                    <div  v-show="errorArticulo" class="form-group row div-error">
                        <div class="text-center text-error">
                            <div v-for="error in errorMostrarMsjArticulo" :key="error" v-text="error">

                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarArticulo()">Guardar</button>
                <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarArticulo()">Actualizar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--Fin del modal-->
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

   