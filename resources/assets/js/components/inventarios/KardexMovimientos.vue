<template>
    <div class="kardex-movimientos-container p-4">
        <!-- Filtros del Kárdex -->
        <div class="card shadow-sm border-0 rounded mb-4 p-3 bg-white">
            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-2">
                    <label class="font-weight-bold text-muted small text-uppercase">Buscador Materia Prima</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" v-model="filters.buscar" @keyup.enter="loadKardexData(1)" class="form-control border-left-0" placeholder="Ej. Papel Kraft, Bond...">
                    </div>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="font-weight-bold text-muted small text-uppercase">Tipo Movimiento</label>
                    <select v-model="filters.tipo" @change="loadKardexData(1)" class="form-control">
                        <option value="">Todos</option>
                        <option value="entrada">Entradas</option>
                        <option value="salida">Salidas</option>
                    </select>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="font-weight-bold text-muted small text-uppercase">Fecha Inicio</label>
                    <input type="date" v-model="filters.fecha_inicio" @change="loadKardexData(1)" class="form-control">
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="font-weight-bold text-muted small text-uppercase">Fecha Fin</label>
                    <input type="date" v-model="filters.fecha_fin" @change="loadKardexData(1)" class="form-control">
                </div>

                <div class="col-md-3 form-group mb-2 d-flex justify-content-end">
                    <button class="btn btn-secondary mr-2" @click="resetFilters" title="Limpiar Filtros">
                        <i class="fa fa-refresh"></i> Limpiar
                    </button>
                    <button class="btn btn-success" @click="exportToExcel" title="Exportar Kárdex a Excel">
                        <i class="fa fa-file-excel-o"></i> Exportar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla Kárdex Premium -->
        <div class="card shadow-sm border-0 rounded bg-white overflow-hidden">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h6 class="font-weight-bold m-0"><i class="fa fa-list-ul mr-2"></i>Historial del Kárdex Contable</h6>
                <span class="badge badge-light px-3 py-2 text-dark font-weight-bold">{{ pagination.total }} Registros Encontrados</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="py-3">Fecha y Hora</th>
                            <th scope="col" class="py-3 text-left">Materia Prima / Insumo</th>
                            <th scope="col" class="py-3">Operación</th>
                            <th scope="col" class="py-3">Cantidad</th>
                            <th scope="col" class="py-3">Costo Unitario</th>
                            <th scope="col" class="py-3">Costo Total</th>
                            <th scope="col" class="py-3">Comprobante Contable</th>
                        </tr>
                    </thead>
                    <tbody v-if="arrayMovimientos.length > 0">
                        <tr v-for="mov in arrayMovimientos" :key="mov.id">
                            <td class="align-middle text-muted small">{{ formatDateTime(mov.created_at) }}</td>
                            <td class="align-middle text-left font-weight-bold text-dark">
                                {{ mov.inventario ? mov.inventario.referencia : 'N/A' }}
                                <span class="badge badge-secondary ml-1" v-if="mov.inventario && mov.inventario.costois">
                                    {{ mov.inventario.costois.unidad_medida }}
                                </span>
                            </td>
                            <td class="align-middle">
                                <span :class="mov.tipo === 'entrada' ? 'badge-entrada' : 'badge-salida'" class="badge-pill px-3 py-1 font-weight-bold text-uppercase small">
                                    <i :class="mov.tipo === 'entrada' ? 'fa fa-arrow-circle-down' : 'fa fa-arrow-circle-up'" class="mr-1"></i>
                                    {{ mov.tipo }}
                                </span>
                            </td>
                            <td class="align-middle font-weight-bold text-dark">{{ parseFloat(mov.cantidad).toLocaleString() }}</td>
                            <td class="align-middle text-muted">{{ formatCurrency(mov.costo_unitario) }}</td>
                            <td class="align-middle font-weight-bold" :class="mov.tipo === 'entrada' ? 'text-success' : 'text-danger'">
                                {{ formatCurrency(mov.costo_total) }}
                            </td>
                            <td class="align-middle">
                                <span v-if="mov.comprobante" class="badge badge-primary badge-voucher p-2 pointer" @click="copyVoucherToClipboard(mov.comprobante.tipo + ' - No. ' + mov.comprobante.numero)" title="Clic para copiar">
                                    <i class="fa fa-file-text-o mr-1"></i>
                                    {{ mov.comprobante.tipo }} #{{ mov.comprobante.numero }}
                                </span>
                                <span v-else class="text-muted small">No contabilizado</span>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="7" class="py-5 text-center text-muted">
                                <i class="fa fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                                <p class="mb-0 font-weight-bold">No se encontraron movimientos de inventario que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación Estilizada -->
            <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center py-3">
                <span class="text-muted small">
                    Mostrando {{ pagination.from || 0 }} al {{ pagination.to || 0 }} de {{ pagination.total }} registros.
                </span>
                
                <nav aria-label="Page navigation" v-if="pagination.last_page > 1">
                    <ul class="pagination mb-0 pagination-sm">
                        <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                            <a class="page-link rounded-left" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">
                                <i class="fa fa-angle-left"></i> Anterior
                            </a>
                        </li>
                        <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page === pagination.current_page }">
                            <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                            <a class="page-link rounded-right" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">
                                Siguiente <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<script>
import exportXlsJSON from 'export-from-json'

export default {
    data() {
        return {
            arrayMovimientos: [],
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 50,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 3,
            filters: {
                buscar: '',
                tipo: '',
                fecha_inicio: '',
                fecha_fin: ''
            }
        }
    },
    computed: {
        pagesNumber() {
            if (!this.pagination.to) return [];
            
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) to = this.pagination.last_page;
            
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        loadKardexData(page) {
            let me = this;
            let url = '/inventarios/kardex?page=' + page + 
                      '&buscar=' + encodeURIComponent(me.filters.buscar) + 
                      '&tipo=' + me.filters.tipo + 
                      '&fecha_inicio=' + me.filters.fecha_inicio + 
                      '&fecha_fin=' + me.filters.fecha_fin;
                      
            axios.get(url)
                .then(function(response) {
                    me.arrayMovimientos = response.data.movimientos;
                    me.pagination = response.data.pagination;
                })
                .catch(function(error) {
                    console.error('Error al cargar datos del Kárdex:', error);
                });
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.loadKardexData(page);
        },
        resetFilters() {
            this.filters.buscar = '';
            this.filters.tipo = '';
            this.filters.fecha_inicio = '';
            this.filters.fecha_fin = '';
            this.loadKardexData(1);
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '$0,00';
            return '$' + parseFloat(value).toLocaleString('es-CO', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        },
        formatDateTime(dateTimeStr) {
            if (!dateTimeStr) return '';
            const date = new Date(dateTimeStr);
            return date.toLocaleString('es-CO', { 
                year: 'numeric', 
                month: 'short', 
                day: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },
        copyVoucherToClipboard(text) {
            navigator.clipboard.writeText(text);
            swal('Copiado', 'Identificador del comprobante contable copiado al portapapeles: ' + text, 'info');
        },
        exportToExcel() {
            let me = this;
            // Fetch ALL filtered records without pagination for the export
            let url = '/inventarios/kardex?page=1&limit=5000' + 
                      '&buscar=' + encodeURIComponent(me.filters.buscar) + 
                      '&tipo=' + me.filters.tipo + 
                      '&fecha_inicio=' + me.filters.fecha_inicio + 
                      '&fecha_fin=' + me.filters.fecha_fin;
                      
            axios.get(url)
                .then(function(response) {
                    const allData = response.data.movimientos;
                    const itemsToExport = allData.map(mov => {
                        return {
                            'Fecha y Hora': me.formatDateTime(mov.created_at),
                            'Materia Prima / Insumo': mov.inventario ? mov.inventario.referencia : 'N/A',
                            'Tipo Operación': mov.tipo.toUpperCase(),
                            'Cantidad': parseFloat(mov.cantidad),
                            'Costo Unitario (COP)': parseFloat(mov.costo_unitario),
                            'Costo Total (COP)': parseFloat(mov.costo_total),
                            'Comprobante Contable': mov.comprobante ? (mov.comprobante.tipo + ' No. ' + mov.comprobante.numero) : 'No Contabilizado'
                        };
                    });
                    
                    const data = itemsToExport;
                    const filename = 'kardex_inventarios_' + new Date().toISOString().slice(0,10);
                    const exportType = exportXlsJSON.types.xls;
                    exportXlsJSON({ data, filename, exportType });
                    
                    swal('Exportado', 'Kárdex exportado a Excel correctamente.', 'success');
                })
                .catch(function(error) {
                    console.error('Error al exportar Kárdex:', error);
                    swal('Error', 'No se pudo realizar la exportación a Excel.', 'error');
                });
        }
    },
    mounted() {
        this.loadKardexData(1);
    }
}
</script>

<style scoped>
.kardex-movimientos-container {
    background-color: #fafbfc;
}

.table th {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #6c757d;
}

.table td {
    font-size: 13px;
}

.pointer {
    cursor: pointer;
}

.badge-entrada {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.badge-salida {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.badge-voucher {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.badge-voucher:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.pagination .page-item.active .page-link {
    background-color: #343a40;
    border-color: #343a40;
    color: #fff;
}

.pagination .page-link {
    color: #495057;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
}
</style>
