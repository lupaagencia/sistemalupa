<template>
    <div class="modal fade" :class="{'mostrar' : modal}" role="dialog" style="display: none; z-index: 10600 !important;" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-20 overflow-hidden">
                <!-- Header Gris / Sencillo -->
                <div class="modal-header text-white py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #4a6572 0%, #34495e 100%);">
                    <h5 class="modal-title font-weight-900 mb-0">
                        <i class="fa fa-pencil-square-o mr-2" style="color: #f0f4c3;"></i> COTIZADORS - COTIZADOR SENCILLO
                    </h5>
                    <button type="button" class="close text-white" @click="cerrarModal()">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-secondary">
                        <label class="premium-label text-dark font-weight-900 mb-1">Nombre del Producto / Servicio (*)</label>
                        <input type="text" class="form-control premium-input-sm font-weight-900" v-model="concepto" placeholder="Ej. Servicio de Diseño, Folleto Simple, etc.">

                        <label class="premium-label text-dark font-weight-900 mb-1 mt-3">Detalles / Descripción</label>
                        <textarea class="form-control premium-input-sm" rows="2" v-model="descripcion" placeholder="Descripción breve del producto o servicio..."></textarea>
                    </div>

                    <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-secondary">
                        <div class="row">
                            <div class="col-6">
                                <label class="premium-label text-dark font-weight-900 mb-1">Cantidad (*)</label>
                                <input type="number" min="1" step="1" class="form-control form-control-lg font-weight-900 text-center text-dark" v-model.number="cantidad">
                            </div>
                            <div class="col-6">
                                <label class="premium-label text-dark font-weight-900 mb-1">Valor Unitario ($) (*)</label>
                                <input type="number" min="0" step="0.01" class="form-control form-control-lg font-weight-900 text-center text-dark" v-model.number="precioUnitario">
                            </div>
                        </div>
                    </div>

                    <!-- Cuadro Total -->
                    <div class="p-3 rounded-15 bg-white border text-center mb-3 shadow-2xs">
                        <div class="text-muted small mb-1">Valor Total (Cantidad × Valor Unitario)</div>
                        <div class="display-4 font-weight-900 text-dark">${{ formatMonto(totalCalculado) }}</div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-block btn-lg font-weight-900 rounded-12 shadow-sm text-white" @click="confirmarCotizacion" :disabled="!concepto || cantidad <= 0 || precioUnitario <= 0">
                        <i class="fa fa-plus-circle mr-1"></i> Agregar a Cotización
                    </button>
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
    methods: {
        cargarProductoExistente(producto) {
            if (!producto) return;
            this.concepto = producto.concepto || producto.nombre || '';
            this.descripcion = producto.descripcion || '';
            this.cantidad = parseFloat(producto.cantidad) || 1;
            this.precioUnitario = parseFloat(producto.precio_unitario) || 0;
        },
        confirmarCotizacion() {
            this.$emit('productoCustom', {
                concepto: this.concepto,
                descripcion: this.descripcion || `Cotización Sencilla - Cantidad: ${this.cantidad} u.`,
                cantidad: this.cantidad,
                precio_unitario: this.precioUnitario,
                total: this.totalCalculado,
                _cotizadorTipo: 'S'
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
.border-left-secondary { border-left: 4px solid #6c757d !important; }
.premium-label { font-size: 0.85rem; }
.premium-input-sm { border-radius: 10px; border: 1px solid #ced4da; }
.shadow-2xs { box-shadow: 0 2px 5px rgba(0,0,0,0.04) !important; }
</style>
