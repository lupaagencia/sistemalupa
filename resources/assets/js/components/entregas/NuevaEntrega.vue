<template>
    <div class="card shadow-sm">
        <!-- HEADER -->
        <div class="card-header d-flex justify-content-between align-items-center py-2"
             :style="esEdicion ? 'background:#f6a00c;' : 'background:#1a73e8;'">
            <h6 class="mb-0 text-white">
                <i class="fa mr-2" :class="esEdicion ? 'fa-edit' : 'fa-plus-circle'"></i>
                <span v-if="esEdicion">
                    Editar Remisión {{ remisionEdit.tipo_documento !== 'Ninguno' ? remisionEdit.tipo_documento + ' #' + remisionEdit.numero_remision : '' }}
                </span>
                <span v-else>Nueva Remisión — Pedido #{{ pedido.id }}</span>
                <span v-if="clienteNombre" class="ml-1" style="opacity: 0.85; font-weight: normal;">
                    — <i class="fa fa-user-circle mr-1 ml-1"></i> {{ clienteNombre }}
                </span>
            </h6>
            <button class="btn btn-outline-light btn-sm" @click="$emit('cancelar')">
                <i class="fa fa-arrow-left mr-1"></i> Volver
            </button>
        </div>

        <div class="card-body">
            <!-- TIPO DOCUMENTO + OBSERVACIONES -->
            <div class="row mb-3">
                <div class="col-md-5">
                    <label class="small font-weight-bold">Tipo de Documento</label>
                    <select class="form-control form-control-sm" v-model="forma.tipo_documento" :disabled="esEdicion">
                        <option value="Remision">Remisión de Entrega</option>
                        <option value="Cuenta de Cobro">Cuenta de Cobro</option>
                        <option value="Ninguno">Sin Documento (solo registro)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small font-weight-bold">Fecha</label>
                    <input type="date" class="form-control form-control-sm" v-model="forma.fecha">
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">&nbsp;</label>
                    <div class="text-muted small pt-1" v-if="esEdicion">
                        <i class="fa fa-info-circle"></i> Editando remisión existente
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="small font-weight-bold">Observaciones generales</label>
                <textarea class="form-control form-control-sm" v-model="forma.observaciones"
                    rows="2" placeholder="Ej: Entregado a mensajería, recibe portería..."></textarea>
            </div>

            <!-- TABLA DE ITEMS DEL PEDIDO -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light text-center" style="font-size:0.8rem;">
                        <tr>
                            <th class="text-left pl-3">Producto</th>
                            <th style="width:110px">Cantidad Pedido</th>
                            <th style="width:110px">Ent. Previas</th>
                            <th style="width:110px">Cantidad Pendiente</th>
                            <th style="width:110px" class="text-success">Cantidad Entregada</th>
                            <th style="width:70px">¿Finalizar?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in items" :key="i">
                            <td class="pl-3 align-middle">
                                <div class="font-weight-bold" style="font-size:0.9rem">{{ item.nombre }}</div>
                                <small class="text-muted">OT #{{ item.ordentrabajo_id }}</small>
                            </td>
                            <td class="text-center align-middle">{{ item.cantidad_total }}</td>
                            <td class="text-center align-middle text-success">{{ item.cantidad_entregada }}</td>
                            <td class="text-center align-middle font-weight-bold text-primary">{{ item.pendiente }}</td>
                            <td class="p-1 align-middle">
                                <input type="number"
                                    class="form-control form-control-sm text-center font-weight-bold"
                                    v-model.number="item.a_entregar"
                                    min="0"
                                    :class="{'text-info': item.a_entregar > item.pendiente}"
                                    :placeholder="item.pendiente > 0 ? '0' : '✓'">
                                <div v-if="item.a_entregar > item.pendiente" class="text-info text-center" style="font-size:0.7rem; font-weight:bold;">
                                    Excede pedido
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <input type="checkbox" v-model="item.es_final">
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                Todo entregado — no hay pendientes
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Resumen -->
            <div v-if="totalAEntregar > 0" class="alert alert-success mt-3 py-2 mb-0">
                <i class="fa fa-check mr-1"></i>
                <strong>{{ totalAEntregar }}</strong> unidades a entregar en esta remisión.
            </div>
        </div>

        <!-- FOOTER -->
        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2">
            <button class="btn btn-outline-secondary btn-sm px-4" @click="$emit('cancelar')" :disabled="loading">
                Cancelar
            </button>
            <button class="btn btn-success btn-sm px-5 shadow-sm font-weight-bold" @click="guardar()" :disabled="loading || totalAEntregar === 0">
                <span v-if="loading"><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                <span v-else>
                    <i class="fa fa-check-circle mr-1"></i>
                    {{ esEdicion ? 'Guardar Cambios' : 'Registrar Remisión' }}
                </span>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        pedido: { type: Object, required: true },
        remisionEdit: { type: Object, default: null }   // null = nueva, Object = editar
    },
    data() {
        return {
            loading: false,
            items: [],
            forma: {
                tipo_documento: 'Remision',
                observaciones: '',
                fecha: new Date().toISOString().slice(0, 10)
            }
        }
    },
    computed: {
        esEdicion() {
            return !!this.remisionEdit && !!this.remisionEdit.comprobante_id;
        },
        clienteNombre() {
            if (this.pedido && this.pedido.cliente) {
                return this.pedido.cliente.razonsocial || this.pedido.cliente.nombre || '';
            }
            return '';
        },
        totalAEntregar() {
            return this.items.reduce((s, i) => s + (parseInt(i.a_entregar) || 0), 0);
        }
    },
    created() {
        this.inicializar();
    },
    methods: {
        inicializar() {
            // Construir lista de items desde las lineas del pedido
            this.items = (this.pedido.lineas || []).map(linea => {
                let entregadoTotal = (linea.orden && linea.orden.cantidad_entregada) ? parseInt(linea.orden.cantidad_entregada) : 0;
                let totalPedido = parseInt(linea.cantidad) || 0;
                let aEntregar = 0;

                // Si estamos editando, extraemos lo que ya tiene esta remisión para mostrar lo "entregado previo"
                if (this.esEdicion) {
                    let itemExistente = this.remisionEdit.items.find(i =>
                        i.ordentrabajo_id == linea.ordentrabajo_id
                    );
                    if (itemExistente) {
                        aEntregar = parseInt(itemExistente.cantidad) || 0;
                    }
                }

                let entregadoOtros = entregadoTotal - aEntregar;
                let pendiente = totalPedido - entregadoOtros;

                return {
                    ordentrabajo_id: linea.ordentrabajo_id,
                    nombre: (linea.articulo && linea.articulo.nombre) ? linea.articulo.nombre : 'Producto sin nombre',
                    cantidad_total: totalPedido,
                    cantidad_entregada: entregadoOtros, // Solo lo de otras remisiones
                    pendiente: pendiente, // Saldo antes de esta entrega
                    a_entregar: aEntregar,
                    es_final: false
                };
            }).filter(i => i.pendiente > 0 || i.a_entregar > 0);

            // Pre-llenar forma si es edición
            if (this.esEdicion) {
                this.forma.tipo_documento = this.remisionEdit.tipo_documento || 'Remision';
                this.forma.observaciones = this.remisionEdit.observaciones || '';
                this.forma.fecha = this.remisionEdit.fecha || this.forma.fecha;
            }
        },
        guardar() {
            let me = this;
            let itemsValidos = this.items.filter(i => (parseInt(i.a_entregar) || 0) > 0);

            if (itemsValidos.length === 0) {
                Swal.fire('Atención', 'Debe ingresar al menos una cantidad mayor a 0.', 'warning');
                return;
            }

            if (this.forma.tipo_documento === 'Remision' && !this.esEdicion) {
                Swal.fire({
                    title: '¿Generar Cuenta de Cobro?',
                    text: '¿Desea generar automáticamente la Cuenta de Cobro para esta remisión?',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, crear Cuenta de Cobro',
                    cancelButtonText: 'No, solo Remisión',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#17a2b8'
                }).then((result) => {
                    if (result.value) {
                        me.ejecutarGuardado(itemsValidos, true);
                    } else if (result.dismiss === 'cancel' || result.dismiss === Swal.DismissReason.cancel) {
                        me.ejecutarGuardado(itemsValidos, false);
                    }
                });
            } else {
                this.ejecutarGuardado(itemsValidos, false);
            }
        },
        ejecutarGuardado(itemsValidos, crearCuentaCobro) {
            let me = this;
            this.loading = true;

            let payload = {
                pedido_id: this.pedido.id,
                tipo_documento: this.forma.tipo_documento,
                observaciones: this.forma.observaciones,
                fecha: this.forma.fecha,
                crear_cuenta_cobro: crearCuentaCobro,
                items: itemsValidos.map(i => ({
                    ordentrabajo_id: i.ordentrabajo_id,
                    cantidad: i.a_entregar,
                    es_final: i.es_final
                }))
            };

            if (this.esEdicion) {
                payload.comprobante_id_destino = this.remisionEdit.comprobante_id;
                payload.es_edicion = true;
            }

            axios.post('/entrega/registrarLote', payload)
                .then(function(response) {
                    me.loading = false;
                    if (response.data.success) {
                        if (me.forma.tipo_documento !== 'Ninguno' && response.data.comprobante_id) {
                            window.open('/entrega/pdfLote/' + response.data.comprobante_id, '_blank');
                        }
                        Swal.fire('¡Listo!', response.data.message || 'Remisión guardada correctamente.', 'success');
                        me.$emit('guardado');
                    }
                })
                .catch(function(error) {
                    me.loading = false;
                    let msg = (error.response && error.response.data && error.response.data.error) || 'Error al guardar la remisión.';
                    Swal.fire('Error', msg, 'error');
                });
        }
    }
}
</script>

<style scoped>
.table th, .table td { vertical-align: middle; }
.border-danger { border-color: #dc3545 !important; }
</style>