<template>
<div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog-custom-laravel">
        <div class="modal-content-custom-premium">
            <!-- Header -->
            <div class="modal-header-premium">
                <div class="header-logo-container">
                    <span class="header-title-text"><i class="fa fa-box mr-1"></i> LUPA Fábrica de Empaques</span>
                </div>
                <button type="button" class="close-btn-white" @click="cerrarModal()">×</button>
            </div>

            <div class="modal-body-premium">
                <div class="row m-0">
                    <!-- Left: Images -->
                    <div class="col-md-5 p-3 p-md-4 bg-light d-flex flex-column align-items-center justify-content-start border-right">
                        <div class="main-image-container mb-3 shadow-sm bg-white">
                            <img v-if="producto.imagenes && producto.imagenes.length > 0" 
                                 :src="`img/productos/${producto.imagenes[0].nombre}`" 
                                 class="img-fluid rounded-premium" alt="Producto">
                            <img v-else-if="producto.imagen" 
                                 :src="`img/productos/${producto.imagen}`" 
                                 class="img-fluid rounded-premium" alt="Producto">
                            <div v-else class="text-center p-4 text-muted">
                                <i class="fa fa-picture-o fa-3x mb-2" style="opacity: 0.4;"></i>
                                <p class="small m-0">Sin imagen disponible</p>
                            </div>
                        </div>
                        <div class="thumbnails-grid d-flex gap-2 overflow-auto pb-2 w-100 justify-content-center" v-if="producto.imagenes && producto.imagenes.length > 1">
                            <div v-for="(img, index) in producto.imagenes" :key="index" 
                                 class="thumb-item" :class="{'selected': index === 0}">
                                <img :src="`img/productos/${img.nombre}`" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    <!-- Right: Details & Config -->
                    <div class="col-md-7 p-3 p-md-4 bg-white">
                        <h1 class="product-title-premium text-brand-purple mb-1">{{producto.nombre || 'Configuración de Producto'}}</h1>
                        <p class="product-subtitle-premium text-muted mb-3">LUPA Fábrica de Empaques | Configuración de Trabajo</p>
                        
                        <div class="price-section mb-3" v-if="producto.valor_total">
                            <span class="price-from">Precio Total:</span>
                            <span class="price-main text-brand-green"> ${{ (producto.valor_total || 0).toLocaleString() }} USD</span>
                        </div>

                        <div class="info-badges mb-3">
                            <div class="info-badge-item">
                                <i class="fa fa-check text-success mr-1"></i> Mínimo: 1.000 u.
                            </div>
                            <div class="info-badge-item">
                                <i class="fa fa-clock-o text-primary mr-1"></i> Entrega: 10-15 días
                            </div>
                        </div>

                        <!-- 1. SELECCIÓN DE PAPEL (Blanco, Crema, Kraft) -->
                        <div class="option-group mb-3">
                            <label class="attr-label d-block text-dark font-weight-bold mb-2">
                                <i class="fa fa-file-text-o text-brand-purple mr-1"></i> Tipo de Papel:
                            </label>
                            <div class="d-flex flex-wrap">
                                <button type="button" 
                                        v-for="papelOpt in opcionesPapel" 
                                        :key="papelOpt.val" 
                                        class="btn btn-sm paper-chip d-flex align-items-center mr-2 mb-2" 
                                        :class="papelSeleccionado === papelOpt.val ? 'selected' : ''"
                                        @click="selectPapel(papelOpt.val)">
                                    <span class="color-swatch mr-1" :style="{ backgroundColor: papelOpt.color, border: papelOpt.border || '1px solid #ccc' }"></span>
                                    <span>{{ papelOpt.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. SELECCIÓN DE TINTAS -->
                        <div class="option-group mb-3">
                            <label class="attr-label d-block text-dark font-weight-bold mb-2">
                                <i class="fa fa-paint-brush text-brand-purple mr-1"></i> Tintas e Impresión:
                            </label>
                            <div class="d-flex flex-wrap">
                                <button type="button" 
                                        v-for="tintaOpt in opcionesTintas" 
                                        :key="tintaOpt" 
                                        class="btn btn-sm ink-chip mr-2 mb-2" 
                                        :class="tintaSeleccionada === tintaOpt ? 'selected' : ''"
                                        @click="selectTinta(tintaOpt)">
                                    {{ tintaOpt }}
                                </button>
                            </div>
                        </div>

                        <!-- 3. OTROS ACABADOS & ANOTACIONES -->
                        <div class="option-group mb-3">
                            <label class="attr-label d-block text-dark font-weight-bold mb-2">
                                <i class="fa fa-magic text-brand-purple mr-1"></i> Otros Acabados:
                            </label>
                            <div class="d-flex flex-wrap mb-2">
                                <button type="button" 
                                        v-for="acabadoOpt in opcionesAcabados" 
                                        :key="acabadoOpt" 
                                        class="btn btn-sm finish-chip mr-2 mb-2" 
                                        :class="acabadosSeleccionados.includes(acabadoOpt) ? 'selected' : ''"
                                        @click="toggleAcabado(acabadoOpt)">
                                    <i class="fa mr-1" :class="acabadosSeleccionados.includes(acabadoOpt) ? 'fa-check-square' : 'fa-square-o'"></i>
                                    {{ acabadoOpt }}
                                </button>
                            </div>

                            <label class="attr-label d-block text-dark font-weight-bold mb-1 mt-2">
                                <i class="fa fa-pencil-square-o text-brand-purple mr-1"></i> Especificaciones / Anotaciones adicionales:
                            </label>
                            <textarea class="form-control custom-textarea-notes" 
                                      v-model="notasEspeciales" 
                                      rows="2" 
                                      placeholder="Escriba observaciones o detalles específicos..."
                                      @change="cotizarPrecio"></textarea>
                        </div>

                        <!-- 4. CABIDAS Y MEDIDAS DE MATERIAL -->
                        <div class="option-group mb-3" v-if="cabidasDisponibles && cabidasDisponibles.length > 0">
                            <label class="attr-label d-block text-dark font-weight-bold mb-2">
                                <i class="fa fa-th text-brand-purple mr-1"></i> Cabida y Medida del Material:
                                <span v-if="cantidad > 1000" class="badge badge-warning text-dark ml-2" style="font-size: 11px;">
                                    <i class="fa fa-lightbulb-o"></i> Sugerida cabida más alta (>1.000 u)
                                </span>
                            </label>
                            <div class="d-flex flex-wrap">
                                <button type="button" 
                                        v-for="(cOpt, idx) in cabidasDisponibles" 
                                        :key="idx" 
                                        class="btn btn-sm finish-chip mr-2 mb-2 p-2" 
                                        :class="cabidaSeleccionada === cOpt ? 'selected btn-purple' : 'btn-outline-secondary'"
                                        @click="selectCabida(cOpt)">
                                    <strong>Cabida {{ cOpt.cabida }}</strong> - Mat: {{ cOpt.medida_material }} (Imp: {{ cOpt.tamano }})
                                </button>
                            </div>
                        </div>

                        <!-- Cantidad -->
                        <div class="quantity-group mb-3">
                            <label class="attr-label d-block text-dark font-weight-bold mb-2">
                                <i class="fa fa-cubes text-brand-purple mr-1"></i> Cantidad de Producto (De 1.000 en 1.000):
                            </label>
                            <div class="d-flex align-items-center mb-2">
                                <button type="button" class="btn btn-outline-purple font-weight-bold px-3 py-2" @click="disminuirCantidad" style="border-radius: 8px 0 0 8px;">
                                    - 1.000
                                </button>
                                <input type="number" 
                                       class="form-control text-center quantity-input-stepper font-weight-bold" 
                                       step="1000" 
                                       min="1000" 
                                       v-model.number="cantidad" 
                                       style="color: #000000 !important; background-color: #ffffff !important; border-radius: 0;"
                                       @change="validarYCotizar">
                                <button type="button" class="btn btn-outline-purple font-weight-bold px-3 py-2" @click="aumentarCantidad" style="border-radius: 0 8px 8px 0;">
                                    + 1.000
                                </button>
                            </div>
                        </div>

                        <div class="action-footer mt-4">
                            <div class="subtotal-box shadow-sm p-3 rounded mb-3 bg-light border" v-if="resumenConfiguracion">
                                <div class="config-summary-section mb-2">
                                    <h6 class="mb-1 text-brand-purple font-weight-bold" style="font-size: 13px; text-transform: uppercase;">
                                        <i class="fa fa-list-alt"></i> Detalles del trabajo:
                                    </h6>
                                    <p class="mb-0 text-dark" style="font-size: 13px; line-height: 1.4;">
                                        {{ resumenConfiguracion }}
                                    </p>
                                </div>
                                
                                <div class="subtotal-display text-right border-top pt-2 mt-2" v-if="producto.valor_total">
                                    <span class="subtotal-label font-weight-bold">Subtotal: </span>
                                    <span class="subtotal-amount text-brand-green font-weight-bold" style="font-size: 22px;">${{ (producto.valor_total || 0).toLocaleString() }} USD</span>
                                </div>
                            </div>

                            <button class="btn-add-to-cart-premium w-100 mb-2" @click="getDatosProducto">
                                <i class="fa fa-shopping-cart mr-1"></i> AGREGAR AL PEDIDO
                            </button>
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
    props: {
        modal: 0,
        producto: {}
    },
    data() {
        return {
            cantidad: 1000,
            papelSeleccionado: 'Blanco',
            opcionesPapel: [
                { label: 'Blanco', val: 'Blanco', color: '#ffffff', border: '2px solid #ddd' },
                { label: 'Crema', val: 'Crema', color: '#f5f2eb', border: '2px solid #e0d8c3' },
                { label: 'Kraft', val: 'Kraft', color: '#c49a6c', border: '2px solid #a67c46' }
            ],
            tintaSeleccionada: '1 Tinta',
            opcionesTintas: ['1 Tinta', '2 Tintas', '3 Tintas', '4 Tintas (CMYK)', 'Sin Tinta'],
            acabadosSeleccionados: [],
            opcionesAcabados: ['Plastificado Mate', 'Plastificado Brillante', 'Stamping', 'Barniz UV', 'Repujado'],
            notasEspeciales: '',
            cabidaSeleccionada: null,
            atributosTienda: {},
            specsSeleccionadas: {},
            matchFound: false,
            valorExtra: 0
        }
    },
    computed: {
        cabidasDisponibles() {
            if (!this.producto || !this.producto.cabidas_materiales) return [];
            let cabidas = typeof this.producto.cabidas_materiales === 'string'
                ? JSON.parse(this.producto.cabidas_materiales)
                : this.producto.cabidas_materiales;
            return Array.isArray(cabidas) ? cabidas : [];
        },
        resumenConfiguracion() {
            let parts = [];
            parts.push(`papel ${this.papelSeleccionado}`);
            parts.push(`tintas ${this.tintaSeleccionada}`);
            if (this.acabadosSeleccionados.length > 0) {
                parts.push(`acabados ${this.acabadosSeleccionados.join(', ')}`);
            }
            if (this.cabidaSeleccionada) {
                parts.push(`cabida ${this.cabidaSeleccionada.cabida} (${this.cabidaSeleccionada.medida_material})`);
            }
            if (this.notasEspeciales) {
                parts.push(`notas: "${this.notasEspeciales}"`);
            }
            return parts.join(' | ');
        }
    },
    watch: {
        modal(val) {
            if (val) {
                this.cantidad = 1000;
                this.papelSeleccionado = 'Blanco';
                this.tintaSeleccionada = '1 Tinta';
                this.acabadosSeleccionados = [];
                this.notasEspeciales = '';
                this.evaluarAutoseleccionCabida();
                this.fetchAtributosTienda();
                this.cotizarPrecio();
            }
        },
        producto: {
            handler() {
                this.evaluarAutoseleccionCabida();
            },
            immediate: true
        }
    },
    methods: {
        selectCabida(cOpt) {
            this.cabidaSeleccionada = cOpt;
        },
        evaluarAutoseleccionCabida() {
            let lista = this.cabidasDisponibles;
            if (!lista || lista.length === 0) {
                this.cabidaSeleccionada = null;
                return;
            }
            if (this.cantidad > 1000) {
                let maxCabida = lista.reduce((max, item) => (parseFloat(item.cabida) > parseFloat(max.cabida) ? item : max), lista[0]);
                this.cabidaSeleccionada = maxCabida;
            } else {
                if (!this.cabidaSeleccionada || !lista.includes(this.cabidaSeleccionada)) {
                    this.cabidaSeleccionada = lista[0];
                }
            }
        },
        selectPapel(val) {
            this.papelSeleccionado = val;
            this.cotizarPrecio();
        },
        selectTinta(val) {
            this.tintaSeleccionada = val;
            this.cotizarPrecio();
        },
        toggleAcabado(acabado) {
            let idx = this.acabadosSeleccionados.indexOf(acabado);
            if (idx > -1) {
                this.acabadosSeleccionados.splice(idx, 1);
            } else {
                this.acabadosSeleccionados.push(acabado);
            }
            this.cotizarPrecio();
        },
        aumentarCantidad() {
            this.cantidad = (parseInt(this.cantidad) || 0) + 1000;
            this.evaluarAutoseleccionCabida();
            this.cotizarPrecio();
        },
        disminuirCantidad() {
            if (this.cantidad > 1000) {
                this.cantidad = (parseInt(this.cantidad) || 1000) - 1000;
                this.evaluarAutoseleccionCabida();
                this.cotizarPrecio();
            }
        },
        validarYCotizar() {
            if (!this.cantidad || this.cantidad < 1000) {
                this.cantidad = 1000;
            }
            this.evaluarAutoseleccionCabida();
            this.cotizarPrecio();
        },
        fetchAtributosTienda() {
            let me = this;
            axios.get('/api/tienda/atributos').then(response => {
                me.atributosTienda = response.data;
            }).catch(() => {});
        },
        cotizarPrecio() {
            let me = this;
            if (!me.producto || !me.producto.id) return;
            axios.post('/api/tienda/cotizar', {
                articulo_id: this.producto.id,
                cantidad: this.cantidad,
                especificaciones: [
                    { titulo: 'Papel', valor: this.papelSeleccionado },
                    { titulo: 'Tintas', valor: this.tintaSeleccionada },
                    { titulo: 'Acabados', valor: this.acabadosSeleccionados.join(', ') },
                    { titulo: 'Notas Especiales', valor: this.notasEspeciales }
                ]
            }).then(response => {
                if (response.data) {
                    me.producto.valor_total = response.data.total || 0;
                }
            }).catch(() => {});
        },
        cerrarModal() {
            this.$emit('cerrarCustomProducto');
            this.$emit('cerrarModal');
        },
        getDatosProducto() {
            let cVal = this.cabidaSeleccionada ? (parseFloat(this.cabidaSeleccionada.cabida) || 1) : 1;
            let cMedida = this.cabidaSeleccionada ? (this.cabidaSeleccionada.medida_material || '') : '';
            let cTamano = this.cabidaSeleccionada ? (this.cabidaSeleccionada.tamano || '') : '';

            // Calculate automatic sobrante based on material quantity and ink count
            let tintasCount = 1;
            if (this.tintaSeleccionada) {
                if (this.tintaSeleccionada.includes('4')) tintasCount = 4;
                else if (this.tintaSeleccionada.includes('3')) tintasCount = 3;
                else if (this.tintaSeleccionada.includes('2')) tintasCount = 2;
                else if (this.tintaSeleccionada.includes('1')) tintasCount = 1;
                else if (this.tintaSeleccionada.toLowerCase().includes('sin')) tintasCount = 0;
            }

            let cantidadMaterial = cVal > 0 ? (this.cantidad / cVal) : this.cantidad;
            let sobranteBase = 50 + Math.max(0, tintasCount - 1) * 25;
            let sobranteTiraje = Math.round(cantidadMaterial * 0.01);
            let sobranteCalculado = Math.max(50, Math.round(sobranteBase + sobranteTiraje));

            let item = {
                ...this.producto,
                cantidad: this.cantidad,
                papel: this.papelSeleccionado,
                tinta: this.tintaSeleccionada,
                acabados: [...this.acabadosSeleccionados],
                notas: this.notasEspeciales,
                cabida: cVal,
                medida_material: cMedida,
                tamano_corte: cTamano,
                sobrante: sobranteCalculado,
                carpeta_cliente: sobranteCalculado,
                specs: {
                    'Papel': this.papelSeleccionado,
                    'Tintas': this.tintaSeleccionada,
                    'Acabados': this.acabadosSeleccionados.join(', '),
                    'Cabida / Material': cVal > 0 ? `Cabida ${cVal} (${cMedida})` : '',
                    'Sobrante (Cuadres)': `${sobranteCalculado} tamaños`,
                    'Notas Especiales': this.notasEspeciales
                }
            };
            this.$emit('productoCustom', item);
            this.cerrarModal();
        }
    }
}
</script>

<style scoped>
.mostrar {
    display: flex !important;
    align-items: center;
    justify-content: center;
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.65) !important;
    backdrop-filter: blur(4px);
    z-index: 2000 !important;
    overflow-y: auto !important;
    padding: 15px;
}

.modal-dialog-custom-laravel {
    width: 100%;
    max-width: 920px;
    margin: auto;
    position: relative;
    z-index: 2001;
}

.modal-content-custom-premium {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    overflow: hidden;
    border: none;
    display: flex;
    flex-direction: column;
    max-height: 92vh;
}

.modal-header-premium {
    background: linear-gradient(135deg, #7d3c98 0%, #5b2c6f 100%);
    color: #ffffff;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-logo-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-title-text {
    font-weight: 700;
    font-size: 15px;
    color: #ffffff;
    letter-spacing: 0.5px;
}

.close-btn-white {
    background: transparent;
    border: none;
    color: #ffffff;
    font-size: 28px;
    font-weight: 300;
    cursor: pointer;
    line-height: 1;
    opacity: 0.85;
    transition: opacity 0.2s;
}

.close-btn-white:hover {
    opacity: 1;
}

.modal-body-premium {
    padding: 0;
    overflow-y: auto;
    max-height: calc(92vh - 55px);
}

.main-image-container {
    width: 100%;
    max-height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    padding: 10px;
    overflow: hidden;
}

.main-image-container img {
    max-height: 300px;
    max-width: 100%;
    object-fit: contain;
}

.product-title-premium {
    font-size: 20px;
    font-weight: 800;
    color: #7d3c98;
    margin: 0;
}

.product-subtitle-premium {
    font-size: 12px;
    color: #6c757d;
}

.price-section {
    background: #f4ecf7;
    padding: 8px 14px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.price-from {
    font-size: 12px;
    color: #555;
    font-weight: 600;
}

.price-main {
    font-size: 18px;
    font-weight: 800;
    color: #27ae60;
}

.info-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.info-badge-item {
    font-size: 12px;
    font-weight: 600;
    color: #444;
    background: #eef2f7;
    padding: 4px 10px;
    border-radius: 6px;
}

.text-brand-purple {
    color: #7d3c98 !important;
}

.text-brand-green {
    color: #27ae60 !important;
}

.paper-chip, .ink-chip, .finish-chip {
    border: 1.5px solid #ddd;
    background: #f8f9fa;
    color: #333;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
}

.paper-chip.selected, .ink-chip.selected, .finish-chip.selected {
    background: #7d3c98 !important;
    color: #ffffff !important;
    border-color: #7d3c98 !important;
}

.color-swatch {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.custom-textarea-notes {
    border: 1.5px solid #7d3c98 !important;
    border-radius: 8px !important;
    font-size: 12px !important;
}

.quantity-input-stepper {
    border: 1.5px solid #7d3c98 !important;
    height: 40px;
    font-weight: 800;
    font-size: 15px;
    max-width: 120px;
}

.btn-outline-purple {
    border: 1.5px solid #7d3c98;
    color: #7d3c98;
    background: white;
}

.btn-outline-purple:hover {
    background: #7d3c98;
    color: white;
}

.btn-add-to-cart-premium {
    background: linear-gradient(135deg, #7d3c98 0%, #5b2c6f 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    border: none;
    border-radius: 10px;
    padding: 12px 20px;
    box-shadow: 0 4px 12px rgba(125, 60, 152, 0.3);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-add-to-cart-premium:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(125, 60, 152, 0.4);
}
</style>
