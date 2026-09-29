<template>
    <div class="modal fade" :class="{'mostrar' : modal}" role="dialog" style="display: none; z-index: 10600 !important;" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-20 overflow-hidden">
                <!-- Header Verde / Generales -->
                <div class="modal-header text-white py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e8449 0%, #145a32 100%);">
                    <h5 class="modal-title font-weight-900 mb-0">
                        <i class="fa fa-boxes mr-2" style="color: #82e0aa;"></i> COTIZADORG - PRODUCTOS GENERALES (COMERCIALIZADOS)
                    </h5>
                    <button type="button" class="close text-white" @click="cerrarModal()">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <div class="row">
                        <!-- Formulario de Producto General -->
                        <div class="col-md-7 mb-3">
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-success">
                                <label class="premium-label text-dark font-weight-900 mb-2">
                                    <i class="fa fa-shopping-bag mr-1 text-success"></i> Seleccionar de Inventario General o Escribir Producto
                                </label>
                                <select class="form-control premium-input-sm font-weight-800 text-dark mb-2" v-model="articuloId" @change="alSeleccionarProducto">
                                    <option value="0">-- Escribir Producto Manualmente --</option>
                                    <option v-for="art in listaProductos" :key="'g_' + art.id" :value="art.id">
                                        {{ art.nombre }} - ${{ formatMonto(art.precio_venta || 0) }}
                                    </option>
                                </select>

                                <label class="premium-label text-dark font-weight-900 mb-1 mt-2">Nombre / Concepto del Producto (*)</label>
                                <input type="text" class="form-control premium-input-sm font-weight-900" v-model="concepto" placeholder="Ej. Bolígrafo Publicitario, Cinta de Embalaje, etc.">

                                <label class="premium-label text-dark font-weight-900 mb-1 mt-3">Detalle / Especificación Adicional</label>
                                <textarea class="form-control premium-input-sm" rows="2" v-model="descripcion" placeholder="Color, presentación, paquete x 100 u, etc."></textarea>
                            </div>

                            <div class="card border-0 shadow-sm rounded-15 p-3 bg-white border-left-success">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="premium-label text-dark font-weight-900 mb-1">Cantidad (*)</label>
                                        <input type="number" min="1" step="1" class="form-control form-control-lg font-weight-900 text-center text-success" v-model.number="cantidad">
                                    </div>
                                    <div class="col-6">
                                        <label class="premium-label text-dark font-weight-900 mb-1">Precio Unitario ($) (*)</label>
                                        <input type="number" min="0" step="0.01" class="form-control form-control-lg font-weight-900 text-center text-success" v-model.number="precioUnitario">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen y Total -->
                        <div class="col-md-5 mb-3">
                            <div class="card border-0 shadow-sm rounded-15 p-3 bg-white text-center h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="font-weight-900 text-dark mb-3">RESUMEN PRODUCTO GENERAL</h6>
                                    
                                    <div class="p-3 mb-3 rounded-15 bg-light border">
                                        <div class="text-muted small mb-1">Valor Total del Ítem</div>
                                        <div class="display-4 font-weight-900 text-success mb-1">${{ formatMonto(totalCalculado) }}</div>
                                        <div class="text-muted small">Cálculo: {{ cantidad }} u. × ${{ formatMonto(precioUnitario) }}</div>
                                    </div>

                                    <div class="text-left small bg-white p-3 rounded border">
                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Producto:</span>
                                            <strong>{{ concepto || 'Sin definir' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Tipo:</span>
                                            <span class="badge badge-success">Comercializado / Stock</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-success btn-block btn-lg font-weight-900 rounded-12 shadow-sm text-white" @click="confirmarCotizacion" :disabled="!concepto || cantidad <= 0 || precioUnitario <= 0">
                                        <i class="fa fa-plus-circle mr-1"></i> Agregar a Cotización
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['modal'],
    data() {
        return {
            listaProductos: [],
            articuloId: 0,
            concepto: '',
            descripcion: '',
            cantidad: 1,
            precioUnitario: 0
        };
    },
    computed: {
        totalCalculado() {
            return Math.round((this.cantidad * this.precioUnitario) * 100) / 100;
        }
    },
    watch: {
        modal(newVal) {
            if (newVal) {
                this.cargarProductos();
            }
        }
    },
    methods: {
        cargarProductos() {
            let me = this;
            axios.get('/articulo/selectArticulo').then(function(response) {
                me.listaProductos = response.data.articulo || response.data || [];
            }).catch(function(error) {
                console.log(error);
            });
        },
        alSeleccionarProducto() {
            let prod = this.listaProductos.find(p => p.id === this.articuloId);
            if (prod) {
                this.concepto = prod.nombre;
                this.precioUnitario = parseFloat(prod.precio_venta) || 0;
            }
        },
        cargarProductoExistente(producto) {
            if (!producto) return;
            this.concepto = producto.concepto || producto.nombre || '';
            this.descripcion = producto.descripcion || '';
            this.cantidad = parseFloat(producto.cantidad) || 1;
            this.precioUnitario = parseFloat(producto.precio_unitario) || 0;
            if (producto.articulo_id) this.articuloId = producto.articulo_id;
        },
        confirmarCotizacion() {
            this.$emit('productoCustom', {
                articulo_id: this.articuloId > 0 ? this.articuloId : null,
                concepto: this.concepto,
                descripcion: this.descripcion || `Producto Comercializado - Cantidad: ${this.cantidad} u.`,
                cantidad: this.cantidad,
                precio_unitario: this.precioUnitario,
                total: this.totalCalculado,
                _cotizadorTipo: 'G'
            });
            this.cerrarModal();
        },
        cerrarModal() {
            this.$emit('cerrarCotizador');
        },
        formatMonto(val) {
            return (parseFloat(val) || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
};
</script>

<style scoped>
.mostrar { display: block !important; background-color: rgba(0, 0, 0, 0.6) !important; }
.rounded-20 { border-radius: 20px !important; }
.rounded-15 { border-radius: 15px !important; }
.rounded-12 { border-radius: 12px !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.premium-label { font-size: 0.85rem; }
.premium-input-sm { border-radius: 10px; border: 1px solid #ced4da; }
</style>
