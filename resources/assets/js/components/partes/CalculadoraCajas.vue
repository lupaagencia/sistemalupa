<template>
<div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-20 overflow-hidden">
            <!-- Header con Gradient -->
            <div class="modal-header bg-dark text-white p-4 border-0">
                <div class="header-section">
                    <div class="icon-box">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div>
                        <h2 class="mb-0 text-white">Cotizador de Cajas <span class="badge badge-primary-custom ml-2" style="background: #a0ef6e; color: #111; font-size: 0.8rem;">PRO</span></h2>
                        <p class="text-white-50 mb-0 font-weight-600">Calculador inteligente de pliegos y costos</p>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-100" aria-label="Close" @click="cerrarModal()">
                    <span aria-hidden="true" class="h3">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4 bg-f9">
                <div class="row">
                    <!-- Panel Izquierdo: Configuración -->
                    <div class="col-lg-7">
                        <div class="input-panel p-4 shadow-sm h-100 mb-0">
                            <div class="section-label">PARÁMETROS DE FABRICACIÓN</div>
                            
                            <!-- Tipo de Producto / Modelo -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="input-label">Modelo / Tipo de Caja</div>
                                    <select class="form-control check-input" v-model="caja.tipo">
                                        <option :value="null">-- Manual (Ingresar medidas 2D) --</option>
                                        <option v-for="tipo in tiposCaja" :key="tipo.id" :value="tipo">
                                            {{ tipo.nombre }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Medidas 3D Avanzadas -->
                            <div class="row" v-if="caja.tipo">
                                <template v-if="caja.tipo.nombre.includes('LS') || caja.tipo.nombre.toLowerCase().includes('trapez')">
                                    <div class="col-md-3 mb-3">
                                        <div class="input-label">Ancho Base (W1)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.ancho_box">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="input-label">Largo Base (L1)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.largo_box">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="input-label">Ancho Tapa (W2)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.ancho_box2">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="input-label">Largo Tapa (L2)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.largo_box2">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="input-label">Alto (H)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.alto_box">
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="col-md-4 mb-3">
                                        <div class="input-label">Ancho (W)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.ancho_box">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="input-label">Largo (L)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.largo_box">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="input-label">Alto (H)</div>
                                        <input type="number" step="0.1" class="form-control check-input" v-model.number="caja.alto_box">
                                    </div>
                                </template>
                            </div>

                            <!-- Pestañas Adicionales -->
                            <div class="row bg-light p-2 rounded-10 mb-3 mx-0" v-if="caja.tipo">
                                <div class="col-md-4">
                                    <div class="input-label small">Pestaña 1 (P1)</div>
                                    <input type="number" step="0.5" class="form-control form-control-sm check-input" v-model.number="caja.p1">
                                </div>
                                <div class="col-md-4">
                                    <div class="input-label small">Pestaña 2 (P2)</div>
                                    <input type="number" step="0.5" class="form-control form-control-sm check-input" v-model.number="caja.p2">
                                </div>
                                <div class="col-md-4">
                                    <div class="input-label small">Pest. Lat (PL)</div>
                                    <input type="number" step="0.5" class="form-control form-control-sm check-input" v-model.number="caja.pl">
                                </div>
                            </div>

                            <!-- Medidas 2D Planas (Calculadas o Manuales) -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="input-label">Ancho Final Papel <small>cm</small></div>
                                    <input type="number" step="0.1" class="form-control check-input" 
                                           v-model.number="caja.ancho">
                                    <small class="text-primary-custom" v-if="caja.tipo && caja.tipo.formula_ancho" style="color: #6c8052">Calculado por fórmula (editable)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="input-label">Largo Final Papel <small>cm</small></div>
                                    <input type="number" step="0.1" class="form-control check-input" 
                                           v-model.number="caja.largo">
                                    <small class="text-primary-custom" v-if="caja.tipo && caja.tipo.formula_largo" style="color: #6c8052">Calculado por fórmula (editable)</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="input-label">Cantidad</div>
                                    <div class="input-group">
                                        <input type="number" step="1000" class="form-control check-input" v-model.number="caja.cantidad">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white border-left-0 rounded-right-10">und</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Mínimo 1,000 unidades</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="input-label">Tintas</div>
                                    <select class="form-control check-input" v-model.number="caja.tintas">
                                        <option v-for="i in 6" :key="i" :value="i">{{ i }} {{ i === 1 ? 'Color' : 'Colores' }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Terminados -->
                            <div class="mt-4">
                                <div class="section-label">ACABADOS Y TERMINADOS</div>
                                <div class="row px-2">
                                    <div class="col-md-6 p-1" v-for="t in listaTerminados" :key="t.id">
                                        <div class="custom-control custom-checkbox custom-card p-3" :class="{'active': t.selected}" @click="t.selected = !t.selected">
                                            <input type="checkbox" class="custom-control-input" v-model="t.selected">
                                            <label class="custom-control-label font-weight-700 ml-2" @click.stop>{{ t.nombre }}</label>
                                            <div class="price-tag text-muted small ml-4">+ ${{ t.price }} millar</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Procesos del Tipo de Producto (Dinamicos) -->
                            <div class="mt-4" v-if="caja.tipo && caja.tipo.procesos && caja.tipo.procesos.length > 0">
                                <div class="section-label">PROCESOS DE PRODUCCIÓN (TIPO)</div>
                                <div class="row px-2">
                                    <div class="col-md-6 p-1" v-for="proc in caja.tipo.procesos" :key="proc.id">
                                        <div class="custom-control custom-checkbox custom-card p-3" :class="{'active': proc.selected !== false}" @click="toggleProceso(proc)">
                                            <input type="checkbox" class="custom-control-input" :checked="proc.selected !== false">
                                            <label class="custom-control-label font-weight-700 ml-2" @click.stop>{{ proc.nombre }}</label>
                                            <div class="price-tag text-muted small ml-4">+ ${{ proc.costo_unitario }} unidad</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Derecho: Resultados -->
                    <div class="col-lg-5">
                        <div class="sticky-top" style="top: 0;">
                            <!-- Card de Dimensiones -->
                            <div class="row mb-3 no-gutters">
                                <div class="col-md-6 p-1">
                                    <div class="result-card primary-card shadow-sm h-100 border-0">
                                        <div class="label">Cabida x Pliego</div>
                                        <div class="value">{{ calculos.cabida }} <span class="unit">pzas</span></div>
                                    </div>
                                </div>
                                <div class="col-md-6 p-1">
                                    <div class="result-card secondary-card shadow-sm h-100 border-0">
                                        <div class="label">Pliegos Totales (70x100)</div>
                                        <div class="value">{{ calculos.pliegosTotales }} <span class="unit">hojas</span></div>
                                    </div>
                                </div>
                                <div class="col-md-6 p-1 mt-1">
                                    <div class="result-card default-card shadow-sm h-100 border text-warning border-warning">
                                        <div class="label" style="color: #856404;">Sobrante p/ Impresión</div>
                                        <div class="value" style="color: #856404;">{{ calculos.sobrante }} <span class="unit">hojas</span></div>
                                    </div>
                                </div>
                                <div class="col-md-6 p-1 mt-1">
                                    <div class="result-card info-card shadow-sm h-100">
                                        <div class="label">Tamaños Totales</div>
                                        <div class="value">{{ calculos.tamaniosNecesarios }} <span class="unit">piezas</span></div>
                                    </div>
                                </div>
                                <div class="col-md-12 p-1 mt-1">
                                    <div class="result-card shadow-sm border text-white bg-dark">
                                        <div class="label" style="opacity: 0.7;">Capacidad Volumétrica</div>
                                        <div class="value" style="color: #a0ef6e;">{{ formatear(volumen) }} <span class="unit">cm³</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Financiero -->
                            <div class="summary-card flex-grow-1 p-4 bg-dark text-white rounded-20 shadow-lg mb-3 d-flex flex-column">
                                <div class="text-center mb-4">
                                    <div class="section-label text-white-50">SISTEMA DE COSTEO PROYECTADO</div>
                                    <h4 class="text-uppercase letter-spacing-1 font-weight-900 mb-0">Resumen Financiero</h4>
                                </div>
                                
                                <div class="summary-list px-2">
                                    <div class="summary-row d-flex justify-content-between mb-2">
                                        <span class="text-white-50">Materia Prima (Papel):</span>
                                        <span class="font-weight-600">$ {{ formatear(costos.material) }}</span>
                                    </div>
                                    <div class="summary-row d-flex justify-content-between mb-2">
                                        <span class="text-white-50">Planchas y Fotopolímeros:</span>
                                        <span class="font-weight-600">$ {{ formatear(costos.planchas) }}</span>
                                    </div>
                                    <div class="summary-row d-flex justify-content-between mb-2">
                                        <span class="text-white-50">Proceso de Impresión:</span>
                                        <span class="font-weight-600">$ {{ formatear(costos.impresion) }}</span>
                                    </div>
                                    <div class="summary-row d-flex justify-content-between mb-2">
                                        <span class="text-white-50">Corte y Troquelado:</span>
                                        <span class="font-weight-600">$ {{ formatear(costos.troquelado) }}</span>
                                    </div>
                                    <div class="summary-row d-flex justify-content-between mb-2">
                                        <span class="text-white-50">Terminados Especiales:</span>
                                        <span class="font-weight-600">$ {{ formatear(costos.terminados) }}</span>
                                    </div>
                                </div>

                                <div class="mt-auto pt-4 border-top border-white-10">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <div class="text-white-50 small font-weight-bold text-uppercase letter-spacing-1">Unitario</div>
                                            <div class="unit-price h2 mb-0 font-weight-900" style="color: #a0ef6e;">$ {{ formatear(totalUnidad) }}</div>
                                        </div>
                                        <div class="col-6 text-right">
                                            <div class="text-white-50 small font-weight-bold text-uppercase letter-spacing-1">Inversión Total</div>
                                            <div class="total-price h1 mb-0 text-white font-weight-900">$ {{ formatear(totalGeneral) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button @click="agregarCotizacion" class="btn btn-black btn-block btn-lg py-3 shadow-lg rounded-15" :disabled="!validarMedidas">
                                <i class="fas fa-plus-circle mr-2 text-primary-custom" style="color: #a0ef6e;"></i> AGREGAR A COTIZACIÓN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-f9 border-0 p-3">
                <button type="button" class="btn btn-link text-muted" @click="cerrarModal()">Cancelar operación</button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    props: {
        modal: { type: Number, default: 0 }
    },
    data() {
        return {
            caja: {
                tipo: null,
                ancho_box: 0,
                largo_box: 0,
                alto_box: 0,
                ancho_box2: 0,
                largo_box2: 0,
                p1: 2,
                p2: 2,
                pl: 2,
                ancho: 20,
                largo: 30,
                tintas: 1,
                cantidad: 1000
            },
            tiposCaja: [],
            listaTerminados: [
                { id: 1, nombre: 'Plastificado Mate', price: 50, selected: false },
                { id: 2, nombre: 'Plastificado Brillante', price: 40, selected: false },
                { id: 3, nombre: 'Barniz UV', price: 30, selected: false },
                { id: 4, nombre: 'Estampado al Calor', price: 180, selected: false },
                { id: 5, nombre: 'Repujado', price: 120, selected: false },
                { id: 6, nombre: 'Armado de Caja', price: 300, selected: false }
            ],
            preciosBase: {
                plancha: 15000,
                millar_impresion: 25000,
                millar_troquelado: 25000,
                empaque: 5000,
                papel_pliego: 2200
            }
        };
    },
    watch: {
        'caja.tipo'(val) {
            if (val && (val.formula_ancho || val.formula_largo)) {
                this.calcular2D();
            }
        },
        'caja.ancho_box'() { this.calcular2D(); },
        'caja.largo_box'() { this.calcular2D(); },
        'caja.alto_box'() { this.calcular2D(); },
        'caja.ancho_box2'() { this.calcular2D(); },
        'caja.largo_box2'() { this.calcular2D(); },
        'caja.p1'() { this.calcular2D(); },
        'caja.p2'() { this.calcular2D(); },
        'caja.pl'() { this.calcular2D(); }
    },
    computed: {
        validarMedidas() {
            return this.caja.ancho > 0 && this.caja.largo > 0 && this.caja.cantidad >= 1000;
        },
        calculos() {
            const WA = 70;
            const HA = 100;
            const w = this.caja.ancho || 1;
            const h = this.caja.largo || 1;

            let cabida = 1;

            // Si el tipo de producto ya tiene piezas por pliego definidas, usamos eso
            if (this.caja.tipo && this.caja.tipo.piezas_por_pliego > 0) {
                cabida = this.caja.tipo.piezas_por_pliego;
            } else {
                // Cálculo automático por área
                const c1_h = Math.floor(WA / w) * Math.floor(HA / h);
                const c2_h = Math.floor(WA / h) * Math.floor(HA / w);
                cabida = Math.max(c1_h, c2_h) || 1;
            }
            
            const tamaniosNecesarios = Math.ceil(this.caja.cantidad / cabida);
            
            let sobranteBase = 50;
            if (this.caja.tintas > 1) {
                sobranteBase = Math.round(50 * (1 + 0.33 * (this.caja.tintas - 1)));
            }

            const factorCantidad = Math.max(0, (tamaniosNecesarios / 1000) - 1);
            const sobrante = Math.round(sobranteBase + (factorCantidad * 10));

            return {
                cabida,
                tamaniosNecesarios,
                sobrante,
                pliegosTotales: Math.ceil(this.caja.cantidad / cabida + sobrante / cabida)
            };
        },
        costos() {
            const cal = this.calculos;
            const p = this.preciosBase;

            const costPlanchas = this.caja.tintas * p.plancha;
            const costMaterial = cal.pliegosTotales * p.papel_pliego;
            const millaresImp = Math.ceil(cal.tamaniosNecesarios / 1000);
            const costImpresion = millaresImp * p.millar_impresion * (1 + (this.caja.tintas - 1) * 0.5);
            const millaresTroq = Math.ceil(this.caja.cantidad / 1000);
            const costTroquelado = millaresTroq * p.millar_troquelado;

            let costTerminados = 0;
            // Terminados manuales
            this.listaTerminados.forEach(t => {
                if (t.selected) {
                    costTerminados += (t.price / 1000) * this.caja.cantidad;
                }
            });

            // Procesos del Tipo de Producto (Dinamicos)
            let costProcesosTP = 0;
            if (this.caja.tipo && this.caja.tipo.procesos) {
                this.caja.tipo.procesos.forEach(proc => {
                    // Si el proceso está seleccionado (agregaremos un checkbox en la UI)
                    if (proc.selected !== false) { // Por defecto true si no se especifica? O debamos dejar que el usuario elija
                         costProcesosTP += (parseFloat(proc.costo_unitario) || 0) * this.caja.cantidad;
                    }
                });
            }

            const costEmpaque = Math.ceil(this.caja.cantidad / 1000) * p.empaque;

            return {
                planchas: costPlanchas,
                material: costMaterial,
                impresion: costImpresion,
                troquelado: costTroquelado,
                terminados: costTerminados + costProcesosTP,
                empaque: costEmpaque
            };
        },
        totalGeneral() {
            const c = this.costos;
            return c.planchas + c.material + c.impresion + c.troquelado + c.terminados + c.empaque;
        },
        totalUnidad() {
            if (this.caja.cantidad === 0) return 0;
            return this.totalGeneral / this.caja.cantidad;
        },
        volumen() {
            const A = this.caja.alto_box || 0;
            const L = this.caja.largo_box || 0;
            const W = this.caja.ancho_box || 0;
            
            if (this.caja.tipo && (this.caja.tipo.nombre.includes('LS')) && (this.caja.ancho_box2 > 0)) {
                // Cálculo aproximado para bases trapezoidales
                const W2 = this.caja.ancho_box2;
                const L2 = this.caja.largo_box2 || L;
                return ((W+W2)/2) * ((L+L2)/2) * A;
            }
            return W * L * A;
        }
    },
    methods: {
        cerrarModal() {
            this.$emit('cerrarCalculadora');
        },
        formatear(valor) {
            return new Intl.NumberFormat('es-CO').format(Math.round(valor));
        },
        async listarTipos() {
            try {
                const response = await axios.get('/tipoproducto');
                this.tiposCaja = response.data.tipoproducto || response.data;
            } catch (error) {
                console.error("Error cargando tipos de caja", error);
            }
        },
        calcular2D() {
            if (!this.caja.tipo) return;
            
            const A = this.caja.alto_box || 0;
            const L = this.caja.largo_box || 0;
            const W = this.caja.ancho_box || 0;
            const H = A;
            const P1 = this.caja.p1 || 0;
            const P2 = this.caja.p2 || 0;
            const PL = this.caja.pl || 0;
            const W1 = W;
            const L1 = L;
            const W2 = this.caja.ancho_box2 || 0;
            const L2 = this.caja.largo_box2 || 0;

            const context = { A, L, W, H, P1, P2, PL, W1, L1, W2, L2 };

            if (this.caja.tipo.formula_ancho) {
                try {
                    let formula = this.caja.tipo.formula_ancho.toUpperCase();
                    Object.keys(context).forEach(key => {
                        const regex = new RegExp(`\\b${key}\\b`, 'g');
                        formula = formula.replace(regex, context[key]);
                    });
                    this.caja.ancho = eval(formula);
                } catch (e) { console.warn("Error en formula_ancho", e); }
            }

            if (this.caja.tipo.formula_largo) {
                try {
                    let formula = this.caja.tipo.formula_largo.toUpperCase();
                    Object.keys(context).forEach(key => {
                        const regex = new RegExp(`\\b${key}\\b`, 'g');
                        formula = formula.replace(regex, context[key]);
                    });
                    this.caja.largo = eval(formula);
                } catch (e) { console.warn("Error en formula_largo", e); }
            }
        },
        toggleProceso(proc) {
            if (proc.selected === undefined || proc.selected === true) {
                this.$set(proc, 'selected', false);
            } else {
                proc.selected = true;
            }
        },
        agregarCotizacion() {
            const terminadosStr = this.listaTerminados.filter(t => t.selected).map(t => t.nombre).join(', ');
            const dummyProducto = {
                id: 0,
                nombre: `${this.caja.tipo ? this.caja.tipo.nombre : 'Caja Personalizada'} (${this.caja.ancho_box}x${this.caja.largo_box}x${this.caja.alto_box}cm) - ${this.formatear(this.volumen)}cm³`,
                cantidad: this.caja.cantidad,
                total: this.totalGeneral,
                iva: 19,
                atributos: [
                    { nombre: 'Medidas Box', open: { labelOpAtributo: `${this.caja.ancho_box}x${this.caja.largo_box}x${this.caja.alto_box} cm` } },
                    { nombre: 'Medida Papel', open: { labelOpAtributo: `${this.caja.ancho}x${this.caja.largo} cm` } },
                    { nombre: 'Tintas', open: { labelOpAtributo: `${this.caja.tintas} Tinta(s)` } },
                    { nombre: 'Terminados', open: { labelOpAtributo: terminadosStr || 'Sin acabados' } },
                    { nombre: 'Cabida/Pliegos', open: { labelOpAtributo: `${this.calculos.cabida} / ${this.calculos.pliegosTotales}` } }
                ]
            };
            this.$emit('productoCustom', dummyProducto);
            this.cerrarModal();
        }
    },
    mounted() {
        this.listarTipos();
    }
};
</script>

<style scoped>
.modal.mostrar {
    display: block !important;
    opacity: 1 !important;
    position: fixed !important;
    background-color: rgba(0,0,0,0.7) !important;
    z-index: 10001;
    overflow-y: auto;
}

.rounded-20 { border-radius: 20px !important; }
.rounded-15 { border-radius: 15px !important; }
.bg-f9 { background-color: #f9fafb !important; }
.font-weight-900 { font-weight: 900 !important; }
.font-weight-800 { font-weight: 800 !important; }
.font-weight-700 { font-weight: 700 !important; }
.font-weight-600 { font-weight: 600 !important; }
.letter-spacing-1 { letter-spacing: 1px !important; }
.border-white-10 { border-color: rgba(255,255,255,0.1) !important; }

.header-section {
    display: flex;
    align-items: center;
}

.icon-box {
    width: 60px;
    height: 60px;
    background-color: #a0ef6e;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 1.8rem;
    color: #1a1a1a;
    box-shadow: 0 6px 12px rgba(160, 239, 110, 0.4);
}

.input-panel {
    background: #fff;
    border-radius: 16px;
}

.section-label {
    font-size: 0.65rem;
    color: #9ca3af;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 12px;
    letter-spacing: 1.2px;
}

.input-label {
    font-size: 0.85rem;
    color: #4b5563;
    margin-bottom: 6px;
    font-weight: 700;
}

.check-input {
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 10px 14px;
    height: auto;
    background-color: #f9fafb;
    font-size: 0.95rem;
    transition: all 0.2s;
    font-weight: 600;
    color: #111;
}

.check-input:focus {
    background-color: #fff;
    border-color: #a0ef6e;
    box-shadow: 0 0 0 3px rgba(160, 239, 110, 0.2);
    outline: none;
}

.custom-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.custom-card:hover {
    border-color: #a0ef6e;
    background-color: #f0fff0;
}

.custom-card.active {
    border-color: #a0ef6e;
    background-color: #f0fff0;
    box-shadow: 0 4px 6px rgba(160, 239, 110, 0.1);
}

.result-card {
    padding: 1.2rem;
    border-radius: 15px;
    color: #fff;
}

.primary-card { background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); }
.secondary-card { background: linear-gradient(135deg, #a0ef6e 0%, #89d658 100%); color: #111; }
.info-card { background-color: #fff; color: #111; border: 1px solid #e5e7eb; }

.result-card .label { font-size: 0.75rem; text-transform: uppercase; font-weight: 800; opacity: 0.8; margin-bottom: 5px; }
.result-card .value { font-size: 1.8rem; font-weight: 900; line-height: 1; }
.result-card .unit { font-size: 0.9rem; font-weight: 600; }

.summary-card {
    border-left: 5px solid #a0ef6e;
}

.btn-black {
    background-color: #111;
    color: #fff;
    border-radius: 15px;
    font-weight: 800;
    text-transform: uppercase;
    transition: all 0.3s;
}

.btn-black:hover {
    background-color: #000;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.btn-black:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
    transform: none;
}
</style>
