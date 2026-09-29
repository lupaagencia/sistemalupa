<template>
    <div>
        <cotizador-impresion 
            ref="cotizadorImpl"
            :modal="modal" 
            @productoCustom="agregarProductoCustom" 
            @cerrarCotizador="cerrarModal">
        </cotizador-impresion>
    </div>
</template>

<script>
import CotizadorImpresion from './CotizadorImpresion';

export default {
    components: {
        CotizadorImpresion
    },
    props: {
        modal: { type: Number, default: 0 },
        producto: { type: Object, default: () => ({}) }
    },
    watch: {
        modal(val) {
            if (val && this.producto) {
                this.$nextTick(() => {
                    if (this.$refs.cotizadorImpl) {
                        this.$refs.cotizadorImpl.cargarProductoExistente(this.producto);
                    }
                });
            }
        }
    },
    methods: {
        cerrarModal() {
            this.$emit('cerrarModal');
        },
        agregarProductoCustom(producto) {
            this.$emit('productoCustom', producto);
            this.$emit('cerrarModal');
        }
    }
}
</script>
