<template>
    <div>
        <!-- ===== LISTADO DE REMISIONES ===== -->
        <div v-if="vista === 'lista'">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#1a73e8;">
                    <h6 class="mb-0 text-white">
                        <i class="fa fa-truck mr-2"></i>Remisiones — Pedido #{{ pedido.id }} 
                        <span v-if="clienteNombre" class="ml-2" style="opacity: 0.85; font-weight: normal;">
                            — <i class="fa fa-user-circle mr-1 ml-1"></i> {{ clienteNombre }}
                        </span>
                    </h6>
                    <div>
                        <button class="btn btn-outline-light btn-sm mr-1" @click="cargarRemisiones()" title="Refrescar">
                            <i class="fa fa-refresh"></i>
                        </button>
                        <button class="btn btn-light btn-sm font-weight-bold shadow-sm" @click="crearNuevaRemision()">
                            <i class="fa fa-plus-circle mr-1"></i> Nueva Remisión
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div v-if="remisiones.length === 0" class="text-center py-5 text-muted">
                        <i class="fa fa-inbox fa-3x mb-3 d-block" style="opacity:0.3"></i>
                        <p class="mb-0">No hay remisiones para este pedido.</p>
                        <small>Haz clic en <strong>Nueva Remisión</strong> para comenzar.</small>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light text-center" style="font-size:0.8rem;">
                                <tr>
                                    <th class="text-left pl-3" style="width:35%">Documento</th>
                                    <th>Fecha</th>
                                    <th>Items</th>
                                    <th>Cant.</th>
                                    <th style="width:130px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Vue 2: key va en el tr, no en template -->
                                <template v-for="rem in remisiones">
                                    <tr :key="rem.key" class="align-middle" style="cursor:pointer" @click="toggleRem(rem.key)">
                                        <td class="pl-3">
                                            <span class="badge mr-2" :class="badgeColor(rem.tipo_documento)">
                                                {{ rem.tipo_documento === 'Ninguno' ? 'Sin Doc.' : rem.tipo_documento }}
                                            </span>
                                            <strong v-if="rem.numero_remision">#{{ rem.numero_remision }}</strong>
                                        </td>
                                        <td class="text-center small">{{ rem.fecha }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ rem.items.length }} ítem(s)</span>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ rem.total_cantidad }}</td>
                                        <td class="text-center" @click.stop>
                                            <div class="btn-group btn-group-sm">
                                                <button v-if="rem.comprobante_id" @click="verPdf(rem.comprobante_id)"
                                                    class="btn btn-outline-danger" title="Ver Remisión PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </button>
                                                <button v-if="rem.cuentacobro_id" @click="verPdf(rem.cuentacobro_id)"
                                                    class="btn btn-outline-success" title="Ver Cuenta de Cobro PDF">
                                                    <i class="fa fa-money"></i>
                                                </button>
                                                <button @click="editarRemision(rem)"
                                                    class="btn btn-outline-warning" title="Editar Remisión">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button v-if="rem.comprobante_id" @click="borrarRemision(rem.comprobante_id)"
                                                    class="btn btn-outline-danger" title="Eliminar Remisión">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Filas detalle — Vue 2: v-if + v-for juntos en el tr, key en tr -->
                                    <tr v-if="expandidas.includes(rem.key)"
                                        v-for="item in rem.items"
                                        :key="'det-' + item.id"
                                        class="bg-light"
                                        style="font-size:0.85rem;">
                                        <td colspan="2" class="pl-5 text-muted border-0">
                                            <i class="fa fa-angle-right mr-1"></i>
                                            {{ item.ordentrabajo && item.ordentrabajo.articulo ? item.ordentrabajo.articulo.nombre : 'Producto' }}
                                            <small v-if="item.observaciones" class="ml-2 font-italic">— {{ item.observaciones }}</small>
                                        </td>
                                        <td class="text-center border-0 small">{{ item.fecha }}</td>
                                        <td class="text-center border-0"></td>
                                        <td class="text-center border-0 font-weight-bold">{{ item.cantidad }}</td>
                                        <td class="text-center border-0 small text-muted">
                                            {{ item.usuario ? item.usuario.usuario : '' }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light text-right py-2">
                    <button class="btn btn-outline-secondary btn-sm px-4" @click="$emit('cancelar')">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== FORMULARIO CREAR / EDITAR REMISIÓN ===== -->
        <div v-if="vista === 'form'">
            <nueva-entrega
                :pedido="pedido"
                :remision-edit="remisionEdit"
                @guardado="onGuardado"
                @cancelar="vista = 'lista'"
            />
        </div>
    </div>
</template>

<script>
import NuevaEntrega from './NuevaEntrega'

export default {
    props: ['pedido'],
    components: { NuevaEntrega },
    data() {
        return {
            vista: 'lista',
            remisiones: [],
            expandidas: [],
            remisionEdit: null,
        }
    },
    computed: {
        clienteNombre() {
            if (this.remisiones.length > 0 && this.remisiones[0].cliente_nombre) {
                return this.remisiones[0].cliente_nombre;
            }
            if (this.pedido && this.pedido.cliente) {
                return this.pedido.cliente.razonsocial || this.pedido.cliente.nombre || '';
            }
            return '';
        }
    },
    created() {
        this.cargarRemisiones();
    },
    methods: {
        cargarRemisiones() {
            let me = this;
            axios.get('/entrega/pedido/' + this.pedido.id)
                .then(function(response) {
                    me.remisiones = me.buildGroups(response.data);
                })
                .catch(function(err) {
                    console.error('Error cargando entregas', err);
                });
        },
        buildGroups(entregas) {
            let map = {};
            let result = [];
            entregas.sort(function(a, b) { return b.id - a.id; }).forEach(function(e) {
                let key = e.comprobante_id ? 'c_' + e.comprobante_id : 'e_' + e.id;
                if (!map[key]) {
                    map[key] = {
                        key: key,
                        comprobante_id: e.comprobante_id,
                        cuentacobro_id: e.cuentacobro_id,
                        tipo_documento: e.tipo_documento || 'Ninguno',
                        numero_remision: e.numero_remision,
                        fecha: e.fecha,
                        cliente_nombre: (e.ordentrabajo && e.ordentrabajo.cliente) ? (e.ordentrabajo.cliente.razonsocial || e.ordentrabajo.cliente.nombre) : '',
                        observaciones: (e.comprobante && e.comprobante.observaciones) ? e.comprobante.observaciones : '',
                        items: [],
                        total_cantidad: 0
                    };
                    result.push(map[key]);
                }
                map[key].items.push(e);
                map[key].total_cantidad += parseInt(e.cantidad) || 0;
            });
            return result;
        },
        toggleRem(key) {
            var idx = this.expandidas.indexOf(key);
            if (idx >= 0) {
                this.expandidas.splice(idx, 1);
            } else {
                this.expandidas.push(key);
            }
        },
        crearNuevaRemision() {
            this.remisionEdit = null;
            this.vista = 'form';
        },
        editarRemision(rem) {
            this.remisionEdit = rem;
            this.vista = 'form';
        },
        onGuardado() {
            this.vista = 'lista';
            this.cargarRemisiones();
            this.$emit('actualizar');
        },
        verPdf(comprobanteId) {
            window.open('/entrega/pdfLote/' + comprobanteId, '_blank');
        },
        borrarRemision(id) {
            let me = this;
            Swal.fire({
                title: '¿Eliminar esta Remisión?',
                text: 'Se eliminarán todas las entregas asociadas y se revierten las cantidades.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.value) {
                    axios.delete('/entrega/comprobante/' + id)
                        .then(function(res) {
                            Swal.fire('Eliminada', res.data.message || 'Remisión eliminada', 'success');
                            me.cargarRemisiones();
                            me.$emit('actualizar');
                        })
                        .catch(function(err) {
                            Swal.fire('Error', (err.response && err.response.data && err.response.data.error) || 'No se pudo eliminar', 'error');
                        });
                }
            });
        },
        badgeColor: function(tipo) {
            if (tipo === 'Remision') return 'badge-primary';
            if (tipo === 'Cuenta de Cobro') return 'badge-success';
            return 'badge-secondary';
        }
    }
}
</script>

<style scoped>
.table td, .table th { vertical-align: middle; }
.badge { font-size: 0.75rem; text-transform: uppercase; }
</style>
