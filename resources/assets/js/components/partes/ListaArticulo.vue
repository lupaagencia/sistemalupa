<template>
     <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-20">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title font-weight-900"><i class="fas fa-boxes mr-2 text-primary-custom" style="color: #a0ef6e;"></i> Seleccione un producto</h5>
                    <button type="button" class="close text-white" aria-label="Close" @click="cerrarModalc()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush overflow-auto" style="max-height: 400px;">
                        <div 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3" 
                           v-for="(producto) in productos" 
                           :key="producto.id" 
                           @click="selectArticulobyid(producto.id)">
                            <div>
                                <div class="font-weight-800 text-dark">{{ producto.nombre }}</div>
                                <div class="small text-muted">{{ producto.categoria ? producto.categoria.nombre : 'General' }}</div>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success rounded-pill font-weight-900 px-3 shadow-sm mr-2" @click.stop="agregarDirecto(producto)">
                                    <i class="fas fa-plus mr-1"></i> Directo
                                </button>
                                <i class="fas fa-magic text-muted small"></i>
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-10 px-4" @click="cerrarModalc()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        productos: { type: Array, default: () => [] },
        modal: { type: [Boolean, Number], default: 0 },
        scroll: { type: Number, default: 0 }
    },
    methods: {
        agregarDirecto(articulo) {
            let p = {
                cantidad: 1000,
                articulo_id: articulo.id,
                articulo: { nombre: articulo.nombre },
                id: 0,
                ordentrabajo_id: 0,
                porcentajes: 0,
                valor_unitario: 0,
                descuento: 0,
                subtotal: 0,
                valor_total: 0,
                detalles: [],
                orden: {
                    id: 0,
                    articulo_id: articulo.id,
                    cantidad: 1000,
                    produccion: 'ENP',
                    prioridad: 1,
                    tamano: articulo.tamano || 1,
                    medida_final: articulo.medida_final || '',
                    medida_material: 1,
                    cabida: 1,
                    plancha: 1,
                    detalles: [],
                    costos: []
                }
            };
            this.$emit('productoCustom', p);
        },
        selectArticulobyid(idproducto) {
            let me = this;
            axios.get(`/articulo/selectArticulobyid?id=${idproducto}`).then(response => {
                me.$emit('productoSeleccionado', response.data);
            });
        },
        cerrarModalc() {
            this.$emit('cerrarModal');
        }
    }
}
</script>

<style scoped>    
    .modal {
        background-color: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
    }
   
    .mostrar {
        display: block !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        z-index: 10001;
    }

    .rounded-20 { border-radius: 20px !important; }
    .font-weight-900 { font-weight: 900 !important; }
    .font-weight-800 { font-weight: 800 !important; }

    .list-group-item-action {
        transition: all 0.2s;
        border-left: 4px solid transparent;
        cursor: pointer;
    }

    .list-group-item-action:hover {
        background-color: #f8fafc;
        border-left-color: #a0ef6e;
    }
</style>