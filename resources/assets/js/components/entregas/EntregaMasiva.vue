<template>
    <div class="card shadow-sm border-0">
        <!-- HEADER -->
        <!-- CABECERA PROFESIONAL -->
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white p-2 rounded mr-3 shadow-sm">
                    <i class="fa fa-truck fa-lg"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold text-dark">Entrega Consolidada de Productos</h5>
                    <div class="mt-1">
                        <span class="badge badge-warning text-dark px-3 py-2 shadow-sm uppercase font-weight-bold" style="font-size: 1.1rem; border: 1px solid #d39e00;">
                            <i class="fa fa-user mr-1"></i> {{ grupo.cliente.razonsocial }}
                        </span>
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm px-4 rounded-pill font-weight-bold shadow-sm" @click="$emit('cancelar')">
                <i class="fa fa-arrow-left mr-1"></i> Regresar al Listado
            </button>
        </div>

        <div class="card-body bg-light">
            <!-- FILTROS Y OPCIONES -->
            <div class="row mb-3 bg-white p-3 rounded shadow-sm mx-0">
                <div class="col-md-4">
                    <label class="small font-weight-bold">Tipo de Documento</label>
                    <select class="form-control form-control-sm" v-model="forma.tipo_documento">
                        <option value="Remision">Remisión de Entrega</option>
                        <option value="Cuenta de Cobro">Cuenta de Cobro</option>
                        <option value="Ninguno">Sin Documento (solo registro)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">Fecha</label>
                    <input type="date" class="form-control form-control-sm" v-model="forma.fecha">
                </div>
                <div class="col-md-5">
                    <label class="small font-weight-bold">Observaciones Generales</label>
                    <input type="text" class="form-control form-control-sm" v-model="forma.observaciones" placeholder="...">
                </div>
            </div>

            <!-- TABLA DE ITEMS AGRUPADOS POR PEDIDO -->
            <div v-if="cargandoData" class="text-center py-5 bg-white rounded shadow-sm">
                <i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <p class="text-muted">Cargando todos los pedidos del cliente...</p>
            </div>
            <div v-else class="table-responsive rounded shadow-sm bg-white">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-dark" style="font-size:0.8rem;">
                        <tr>
                            <th class="pl-3">Pedido / Producto</th>
                            <th style="width:100px" class="text-center">Total</th>
                            <th style="width:100px" class="text-center">Entregado</th>
                            <th style="width:100px" class="text-center">Pendiente</th>
                            <th style="width:100px" class="text-center bg-success text-white">A Entregar</th>
                            <th style="width:50px" class="text-center">Fin</th>
                        </tr>
                    </thead>
                    <tbody v-for="pedido in pedidosFiltrados" :key="'p-' + pedido.id">
                        <!-- Cabecera de Pedido -->
                        <tr class="table-secondary border-top-0">
                            <td colspan="5" class="py-2 pl-3 font-weight-bold text-dark border-top shadow-sm">
                                <span class="badge badge-dark mr-2">ORDEN #{{ pedido.id }}</span>
                                <span class="small text-muted font-italic"><i class="fa fa-calendar mr-1"></i> {{ pedido.fecha }}</span>
                                <span class="float-right mr-3 small">
                                    Estado: <span class="badge badge-info shadow-sm">{{ getEstadoLabel(pedido.estado) }}</span>
                                </span>
                            </td>
                        </tr>
                        <!-- Items de cada Pedido -->
                        <tr v-for="linea in pedido.lineas" :key="'item-' + pedido.id + '-' + linea.id" class="item-row">
                            <td class="pl-4 align-middle">
                                <div class="font-weight-bold small">{{ linea.articulo ? linea.articulo.nombre : 'Producto' }}</div>
                                <small class="text-muted">OT #{{ linea.ordentrabajo_id }}</small>
                            </td>
                            <td class="text-center align-middle small">{{ linea.cantidad }}</td>
                            <td class="text-center align-middle small text-success">{{ linea.cantidad_entregada }}</td>
                            <td class="text-center align-middle font-weight-bold text-primary small">{{ linea.pendiente }}</td>
                            <td class="p-1 align-middle">
                                <input type="number" 
                                    class="form-control form-control-sm text-center font-weight-bold" 
                                    v-model.number="linea.a_entregar"
                                    min="0"
                                    :max="linea.pendiente + 100"
                                    :placeholder="linea.pendiente">
                            </td>
                            <td class="text-center align-middle">
                                <input type="checkbox" v-model="linea.es_final">
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="pedidosFiltrados.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No se encontraron items pendientes para entregar en los estados permitidos (En producción, Completados, Para entregar).
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="totalAEntregar > 0" class="alert alert-info mt-3 mb-0 py-2 border-0 shadow-sm">
                <i class="fa fa-info-circle mr-2"></i>
                Se entregarán <strong>{{ totalAEntregar }}</strong> unidades en total.
            </div>
        </div>

        <!-- FOOTER -->
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
            <button class="btn btn-outline-secondary px-4" @click="$emit('cancelar')" :disabled="loading">
                <i class="fa fa-times mr-1"></i> Cancelar
            </button>
            <button class="btn btn-primary px-5 font-weight-bold shadow" @click="guardar()" :disabled="loading || totalAEntregar === 0">
                <span v-if="loading"><i class="fa fa-spinner fa-spin mr-1"></i> Procesando...</span>
                <span v-else><i class="fa fa-check-circle mr-1"></i> Confirmar Entrega Masiva</span>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: ['grupo'],
    data() {
        return {
            loading: false,
            cargandoData: false,
            forma: {
                tipo_documento: 'Remision',
                observaciones: '',
                fecha: new Date().toISOString().slice(0, 10)
            },
            pedidosFiltrados: []
        }
    },
    computed: {
        totalAEntregar() {
            let total = 0;
            this.pedidosFiltrados.forEach(p => {
                p.lineas.forEach(l => {
                    total += (parseInt(l.a_entregar) || 0);
                });
            });
            return total;
        }
    },
    created() {
        this.cargarPedidos();
    },
    methods: {
        cargarPedidos() {
            let me = this;
            me.cargandoData = true;
            me.pedidosFiltrados = [];

            // Consultar todos los pedidos del cliente (limite alto para no omitir ninguno)
            axios.get('/comprobante?buscar=' + this.grupo.cliente.id + '&criterio=cliente_id&per_page=500')
                .then(function(res) {
                    console.log('--- DEPURACIÓN ENTREGA MASIVA ---');
                    console.log('Payload recibido:', res.data);
                    
                    let data = [];
                    // Detectar estructura de respuesta del paginador
                    if (res.data.comprobantes && res.data.comprobantes.data) {
                        data = res.data.comprobantes.data;
                    } else if (res.data.comprobantes && Array.isArray(res.data.comprobantes)) {
                        data = res.data.comprobantes;
                    } else if (Array.isArray(res.data)) {
                        data = res.data;
                    }
                    
                    console.log('Total pedidos encontrados para ID ' + me.grupo.cliente.id + ':', data.length);
                    
                    // Solo pedidos en producción (2), completados (3) y por entregar (4)
                    const estadosPermitidos = [2, 3, 4];

                    me.pedidosFiltrados = data.filter(p => {
                        let est = parseInt(p.estado);
                        let match = estadosPermitidos.includes(est);
                        if (!match) console.log('Pedido ID ' + p.id + ' ignorado por estado (' + est + ')');
                        return match;
                    }).map(p => {
                        let pClone = JSON.parse(JSON.stringify(p));
                        if (!pClone.lineas) pClone.lineas = [];
                        
                        pClone.lineas.forEach(l => {
                            // Cálculo robusto de pediente
                            let yaEntregado = (l.orden && l.orden.cantidad_entregada !== undefined) ? parseInt(l.orden.cantidad_entregada) : 0;
                            let total = parseInt(l.cantidad) || 0;
                            l.cantidad_entregada = yaEntregado;
                            l.pendiente = Math.max(0, total - yaEntregado);
                            l.a_entregar = 0;
                            l.es_final = false;
                        });
                        
                        let lineasConPendiente = pClone.lineas.filter(l => l.pendiente > 0);
                        if (lineasConPendiente.length === 0 && pClone.lineas.length > 0) {
                            console.log('Pedido ID ' + p.id + ' filtrado: 0 items pendientes.');
                        }
                        pClone.lineas = lineasConPendiente;
                        return pClone;
                    }).filter(p => p.lineas.length > 0);

                    console.log('Pedidos finales a mostrar en tabla:', me.pedidosFiltrados.length);
                    me.cargandoData = false;
                })
                .catch(function(err) {
                    me.cargandoData = false;
                    console.error(err);
                    Swal.fire('Error', 'No se pudieron cargar los pedidos del cliente.', 'error');
                });
        },
        getEstadoLabel(e) {
            const labels = { 1: 'Pendiente', 2: 'En producción', 3: 'Completado', 4: 'Para entregar', 5: 'Entregado', 6: 'No recogido' };
            return labels[e] || e;
        },
        guardar() {
            let me = this;
            let itemsParaEnviar = [];

            this.pedidosFiltrados.forEach(p => {
                p.lineas.forEach(l => {
                    let cant = parseInt(l.a_entregar) || 0;
                    if (cant > 0) {
                        itemsParaEnviar.push({
                            ordentrabajo_id: l.ordentrabajo_id,
                            pedido_id: p.id,
                            cantidad: cant,
                            es_final: l.es_final
                        });
                    }
                });
            });

            if (itemsParaEnviar.length === 0) {
                Swal.fire('Error', 'No hay cantidades ingresadas para entregar.', 'warning');
                return;
            }

            if (this.forma.tipo_documento === 'Remision') {
                Swal.fire({
                    title: '¿Generar Cuenta de Cobro?',
                    text: '¿Desea generar automáticamente la Cuenta de Cobro para esta remisión masiva?',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, crear Cuenta de Cobro',
                    cancelButtonText: 'No, solo Remisión',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#17a2b8'
                }).then((result) => {
                    if (result.value) {
                        me.ejecutarGuardadoMasiva(itemsParaEnviar, true);
                    } else if (result.dismiss === 'cancel' || result.dismiss === Swal.DismissReason.cancel) {
                        me.ejecutarGuardadoMasiva(itemsParaEnviar, false);
                    }
                });
            } else {
                this.ejecutarGuardadoMasiva(itemsParaEnviar, false);
            }
        },
        ejecutarGuardadoMasiva(itemsParaEnviar, crearCuentaCobro) {
            let me = this;
            this.loading = true;
            let payload = {
                cliente_id: this.grupo.cliente.id,
                tipo_documento: this.forma.tipo_documento,
                fecha: this.forma.fecha,
                observaciones: this.forma.observaciones,
                crear_cuenta_cobro: crearCuentaCobro,
                items: itemsParaEnviar
            };

            axios.post('/entrega/registrarMasiva', payload)
                .then(function(res) {
                    me.loading = false;
                    if (res.data.success) {
                        if (me.forma.tipo_documento !== 'Ninguno' && res.data.comprobante_id) {
                            window.open('/entrega/pdfLote/' + res.data.comprobante_id, '_blank');
                        }
                        Swal.fire('¡Éxito!', res.data.message || 'Entrega masiva registrada.', 'success');
                        me.$emit('actualizar');
                    }
                })
                .catch(function(err) {
                    me.loading = false;
                    Swal.fire('Error', (err.response && err.response.data && err.response.data.error) || 'Error al procesar.', 'error');
                });
        }
    }
}
</script>

<style scoped>
.table th { border-top: none; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
.item-row:hover { background-color: #f8fbff; }
.tracking-wider { letter-spacing: 1px; }
.uppercase { text-transform: uppercase; }
.rounded-pill { border-radius: 50px; }
</style>
