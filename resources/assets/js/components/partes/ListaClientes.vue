<template>
     <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-20">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title font-weight-900"><i class="fas fa-users mr-2 text-primary-custom" style="color: #a0ef6e;"></i> Seleccione un cliente</h5>
                    <button type="button" class="close text-white" aria-label="Close" @click="cerrarModalc()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush overflow-auto" style="max-height: 400px;">
                        <a href="#" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3" 
                           v-for="(cliente, index) in clientes" 
                           :key="cliente.id" 
                           @click.prevent="getDatosCliente(cliente, index)">
                            <div>
                                <div class="font-weight-800 text-dark">{{ cliente.razonsocial || cliente.nombre }}</div>
                                <div class="small text-muted">{{ cliente.num_documento || 'No DOC' }}</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a> 
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
        clientes: { type: Array, default: () => [] },
        modal: { type: [Boolean, Number], default: 0 },
        scroll: { type: Number, default: 0 }
    },
    methods: {
        getDatosCliente(cliente, index) {
            this.$emit('clienteSeleccionado', cliente);
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
    }

    .list-group-item-action:hover {
        background-color: #f8fafc;
        border-left-color: #a0ef6e;
        padding-left: 20px !important;
    }
</style>