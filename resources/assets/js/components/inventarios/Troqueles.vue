<template>
    <main class="main">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Inventario de Líneas de Troquel
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" v-model="buscar" @keyup.enter="listarTroqueles(1,buscar)" class="form-control" placeholder="Buscar por nombre de producto...">
                                <button type="submit" @click="listarTroqueles(1,buscar)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cabida</th>
                                    <th>Dimensiones (cm)</th>
                                    <th>Tamaño</th>
                                    <th>Visible</th>
                                    <th>Imagen</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="troquel in arrayTroqueles" :key="troquel.id">
                                    <td v-text="troquel.nombre_articulo"></td>
                                    <td class="text-center" style="font-weight: bold;">{{ troquel.cabida }}</td>
                                    <td>{{ troquel.ancho_impresion }} x {{ troquel.largo_impresion }} cm</td>
                                    <td><span class="badge badge-info">{{ troquel.tamano }}</span></td>
                                    <td>
                                        <span v-if="troquel.mostrar" class="badge badge-success">Sí</span>
                                        <span v-else class="badge badge-danger">No</span>
                                    </td>
                                    <td class="text-center">
                                        <img v-if="troquel.imagen" :src="'/storage/troqueles/' + troquel.imagen" width="100" @click="abrirZoom('/storage/troqueles/' + troquel.imagen)" style="border: 1px solid #ddd; padding: 2px; cursor: pointer;" title="Click para ampliar">
                                        <span v-else>Sin imagen</span>
                                    </td>
                                    <td>
                                        <a v-if="troquel.imagen" :href="'/storage/troqueles/' + troquel.imagen" :download="'Troquel_' + troquel.nombre_articulo + '_Cabida_' + troquel.cabida" class="btn btn-success btn-sm" title="Descargar">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="arrayTroqueles.length == 0">
                                    <td colspan="7" class="text-center">No se encontraron líneas de troquel.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar)" v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal para Zoom de Imagen -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalZoom}" role="dialog" style="display: none; z-index: 1060;" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="justify-content: space-between; align-items: center;">
                        <h4 class="modal-title">Vista Ampliada</h4>
                        <button type="button" class="close" @click="cerrarZoom()" style="font-size: 2rem; color: #000; opacity: 1; border: none; background: transparent; padding: 0 10px; cursor: pointer;">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body text-center" style="padding: 20px;">
                        <img :src="imagenZoom" style="max-width: 100%; max-height: 70vh; object-fit: contain; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    </div>
                    <div class="modal-footer">
                        <a :href="imagenZoom" :download="imagenZoom.split('/').pop()" class="btn btn-success">
                            <i class="fa fa-download"></i> Descargar Imagen
                        </a>
                        <button type="button" class="btn btn-secondary" @click="cerrarZoom()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            arrayTroqueles: [],
            buscar: '',
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            modalZoom: false,
            imagenZoom: ''
        }
    },
    computed: {
        isActived: function() {
            return this.pagination.current_page;
        },
        pagesNumber: function() {
            if (!this.pagination.to) {
                return [];
            }
            var from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        listarTroqueles(page, buscar) {
            let me = this;
            var url = '/articulo/troquel/listarTodos?page=' + page + '&buscar=' + buscar;
            axios.get(url).then(function(response) {
                var respuesta = response.data;
                me.arrayTroqueles = respuesta.troqueles.data;
                me.pagination = respuesta.pagination;
            })
            .catch(function(error) {
                console.log(error);
            });
        },
        cambiarPagina(page, buscar) {
            this.pagination.current_page = page;
            this.listarTroqueles(page, buscar);
        },
        abrirZoom(url) {
            this.imagenZoom = url;
            this.modalZoom = true;
        },
        cerrarZoom() {
            this.modalZoom = false;
            this.imagenZoom = '';
        }
    },
    mounted() {
        this.listarTroqueles(1, this.buscar);
    }
}
</script>

<style scoped>
.mostrar {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    opacity: 1 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.8) !important;
    overflow-y: auto;
    z-index: 10000 !important;
}
.mostrar .modal-dialog {
    margin: 0 !important;
    top: 0 !important;
}
.modal-content {
    top: 0 !important;
}
</style>
