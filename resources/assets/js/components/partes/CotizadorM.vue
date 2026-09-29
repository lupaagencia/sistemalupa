<template>
    <div class="modal fade" :class="{'mostrar' : modal}" role="dialog" style="display: none; z-index: 10600 !important;" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-20 overflow-hidden">
                <!-- Header Naranja / Manual -->
                <div class="modal-header text-white py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #d35400 0%, #a04000 100%);">
                    <h5 class="modal-title font-weight-900 mb-0">
                        <i class="fa fa-sliders mr-2" style="color: #f5b041;"></i> COTIZADORM - COTIZACIÓN MANUAL PASO A PASO
                    </h5>
                    <button type="button" class="close text-white" @click="cerrarModal()">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <div class="row">
                        <!-- Formulario de Entrada Manual -->
                        <div class="col-md-7 mb-3">
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-warning">
                                <label class="premium-label text-dark font-weight-900 mb-1">Nombre / Concepto (*)</label>
                                <input type="text" class="form-control premium-input-sm font-weight-900" v-model="concepto" placeholder="Ej. Impresión de Afiches 50x70, Estuche Especial, etc.">

                                <label class="premium-label text-dark font-weight-900 mb-1 mt-2">Detalles / Especificaciones Manuales</label>
                                <textarea class="form-control premium-input-sm" rows="2" v-model="descripcion" placeholder="Especificaciones de tintas, acabados, troquelado, etc."></textarea>
                            </div>

                            <!-- Desglose de Costos Manuales Dinámico -->
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-warning">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-900 text-warning mb-0"><i class="fa fa-calculator mr-1"></i> Desglose Manual de Costos ($)</h6>
                                    <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold rounded-10" @click="agregarItem" title="Agregar nuevo costo al desglose">
                                        <i class="fa fa-plus-circle mr-1"></i> Agregar Ítem
                                    </button>
                                </div>
                                
                                <div v-for="(item, index) in itemsCosto" :key="index" class="item-costo-row mb-2 p-2 rounded bg-light border d-flex align-items-center gap-2">
                                    <!-- Botones Reordenar Arriba / Abajo -->
                                    <div class="btn-group-vertical mr-2">
                                        <button type="button" class="btn btn-xs btn-light border text-muted py-0 px-1" :disabled="index === 0" @click="subirItem(index)" title="Subir posición">
                                            <i class="fa fa-chevron-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-light border text-muted py-0 px-1" :disabled="index === itemsCosto.length - 1" @click="bajarItem(index)" title="Bajar posición">
                                            <i class="fa fa-chevron-down"></i>
                                        </button>
                                    </div>

                                    <!-- Nombre / Nombre de Costo -->
                                    <div class="flex-grow-1 mr-2">
                                        <input type="text" class="form-control form-control-sm font-weight-bold" v-model="item.nombre" placeholder="Concepto de costo">
                                    </div>

                                    <!-- Valor del Costo -->
                                    <div style="width: 110px;" class="mr-2">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right font-weight-bold" v-model.number="item.valor" placeholder="0">
                                    </div>

                                    <!-- Botón Eliminar -->
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0" :disabled="itemsCosto.length <= 1" @click="eliminarItem(index)" title="Eliminar ítem">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group row mb-0 mt-3 pt-2 border-top">
                                    <label class="col-6 col-form-label small font-weight-bold text-dark">Margen / Utilidad (%):</label>
                                    <div class="col-6">
                                        <input type="number" step="1" min="0" class="form-control form-control-sm text-center font-weight-bold text-danger" v-model.number="margenPorcentaje">
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-15 p-3 bg-white border-left-warning">
                                <label class="premium-label text-dark font-weight-900 mb-1">Cantidad de Unidades Requeridas (*)</label>
                                <input type="number" min="1" step="1" class="form-control form-control-lg font-weight-900 text-center text-warning" v-model.number="cantidad">
                            </div>
                        </div>

                        <!-- Resumen y Total -->
                        <div class="col-md-5 mb-3">
                            <div class="card border-0 shadow-sm rounded-15 p-3 bg-white text-center h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="font-weight-900 text-dark mb-3">RESUMEN COTIZACIÓN MANUAL</h6>
                                    
                                    <div class="p-3 mb-3 rounded-15 bg-light border">
                                        <div class="text-muted small mb-1">Costo Neto Directo: <strong>${{ formatMonto(costoNeto) }}</strong></div>
                                        <div class="display-4 font-weight-900 text-warning mb-1">${{ formatMonto(totalCalculado) }}</div>
                                        <div class="text-muted small">Precio Unitario: <strong>${{ formatMonto(precioUnitarioCalculado) }}</strong></div>
                                    </div>

                                    <div class="text-left small bg-white p-3 rounded border" style="max-height: 220px; overflow-y: auto;">
                                        <div v-for="(item, idx) in itemsCosto" :key="idx" class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">{{ item.nombre || 'Ítem ' + (idx + 1) }}:</span>
                                            <strong>${{ formatMonto(item.valor) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between py-1 border-bottom text-danger font-weight-bold mt-1">
                                            <span>Utilidad ({{ margenPorcentaje }}%):</span>
                                            <span>+${{ formatMonto(montoMargen) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-warning btn-block btn-lg font-weight-900 rounded-12 shadow-sm text-dark" @click="confirmarCotizacion" :disabled="!concepto || cantidad <= 0 || totalCalculado <= 0">
                                        <i class="fa fa-check-circle mr-1"></i> Usar Cotización Manual
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
            concepto: '',
            descripcion: '',
            itemsCosto: [
                { nombre: 'Papel / Sustrato', valor: 0 },
                { nombre: 'Impresión / Prensa', valor: 0 },
                { nombre: 'Terminados / Acabados', valor: 0 }
            ],
            margenPorcentaje: 30,
            cantidad: 1000
        };
    },
    computed: {
        costoNeto() {
            return this.itemsCosto.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);
        },
        montoMargen() {
            return this.costoNeto * ((parseFloat(this.margenPorcentaje) || 0) / 100);
        },
        totalCalculado() {
            return Math.round((this.costoNeto + this.montoMargen) * 100) / 100;
        },
        precioUnitarioCalculado() {
            return (this.cantidad > 0) ? Math.round((this.totalCalculado / this.cantidad) * 100) / 100 : 0;
        }
    },
    methods: {
        agregarItem() {
            this.itemsCosto.push({ nombre: '', valor: 0 });
        },
        eliminarItem(index) {
            if (this.itemsCosto.length > 1) {
                this.itemsCosto.splice(index, 1);
            }
        },
        subirItem(index) {
            if (index > 0) {
                const item = this.itemsCosto.splice(index, 1)[0];
                this.itemsCosto.splice(index - 1, 0, item);
            }
        },
        bajarItem(index) {
            if (index < this.itemsCosto.length - 1) {
                const item = this.itemsCosto.splice(index, 1)[0];
                this.itemsCosto.splice(index + 1, 0, item);
            }
        },
        resetCotizador() {
            this.concepto = '';
            this.descripcion = '';
            this.itemsCosto = [
                { nombre: 'Papel / Sustrato', valor: 0 },
                { nombre: 'Impresión / Prensa', valor: 0 },
                { nombre: 'Terminados / Acabados', valor: 0 }
            ];
            this.margenPorcentaje = 30;
            this.cantidad = 1000;
        },
        cargarProductoExistente(producto) {
            if (!producto) return;
            this.concepto = producto.concepto || producto.nombre || '';
            this.descripcion = producto.descripcion || '';
            this.cantidad = parseFloat(producto.cantidad) || 1;
            if (producto.itemsCosto && Array.isArray(producto.itemsCosto) && producto.itemsCosto.length > 0) {
                this.itemsCosto = JSON.parse(JSON.stringify(producto.itemsCosto));
            } else if (producto.precio_unitario) {
                this.itemsCosto = [
                    { nombre: 'Costo base', valor: Math.round((parseFloat(producto.precio_unitario) * parseFloat(producto.cantidad || 1)) * 100) / 100 }
                ];
                this.margenPorcentaje = 0;
            }
            if (producto.margenPorcentaje !== undefined) {
                this.margenPorcentaje = parseFloat(producto.margenPorcentaje) || 0;
            }
        },
        confirmarCotizacion() {
            let detalleCostos = this.itemsCosto
                .filter(i => (parseFloat(i.valor) || 0) > 0 || i.nombre)
                .map(i => `${i.nombre || 'Costo'}: $${this.formatMonto(i.valor)}`)
                .join(', ');
            let desc = this.descripcion ? this.descripcion : "Cotización Manual";
            
            this.$emit('productoCustom', {
                concepto: this.concepto,
                descripcion: desc,
                cantidad: this.cantidad,
                precio_unitario: this.precioUnitarioCalculado,
                total: this.totalCalculado,
                itemsCosto: JSON.parse(JSON.stringify(this.itemsCosto)),
                margenPorcentaje: this.margenPorcentaje,
                _cotizadorTipo: 'M'
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
.rounded-10 { border-radius: 10px !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }
.premium-label { font-size: 0.85rem; }
.premium-input-sm { border-radius: 10px; border: 1px solid #ced4da; }
.btn-xs { padding: 1px 5px; font-size: 0.75rem; line-height: 1.2; }
.item-costo-row { transition: all 0.2s ease; }
.item-costo-row:hover { background-color: #f1f3f5 !important; }
</style>
