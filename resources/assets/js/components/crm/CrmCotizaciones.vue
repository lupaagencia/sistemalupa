<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fa fa-file-text-o mr-2 text-primary"></i>Cotizaciones Comerciales</h5>
            <button class="btn btn-primary btn-sm font-weight-bold" @click="abrirModalCrear">
                <i class="fa fa-plus-circle mr-1"></i>Nueva Cotización
            </button>
        </div>

        <div class="card-body p-3">
            <!-- Search & Filters -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Buscar por número o cliente..." v-model="buscar" @keyup.enter="listarCotizaciones(1)" />
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" v-model="estadoFiltro" @change="listarCotizaciones(1)">
                        <option value="">-- Todos los estados --</option>
                        <option value="Borrador">Borrador</option>
                        <option value="Enviada">Enviada</option>
                        <option value="Aprobada">Aprobada</option>
                        <option value="Rechazada">Rechazada</option>
                        <option value="Convertida">Convertida</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>N° Cotización</th>
                            <th>Cliente / Prospecto</th>
                            <th>Emisión</th>
                            <th>Vencimiento</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Vendedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!arrayCotizaciones.length">
                            <td colspan="8" class="text-center py-4 text-muted">No hay cotizaciones registradas.</td>
                        </tr>
                        <tr v-for="item in arrayCotizaciones" :key="item.id">
                            <td class="font-weight-bold text-primary">
                                {{ item.numero_cotizacion }}
                            </td>
                            <td>
                                <strong>{{ item.cliente ? (item.cliente.razonsocial || item.cliente.nombre) : (item.prospecto ? item.prospecto.nombre : 'N/A') }}</strong>
                                <div v-if="item.cliente && item.cliente.num_documento" class="small text-muted font-weight-bold text-success">Doc: {{ item.cliente.num_documento }}</div>
                                <div v-else-if="item.prospecto && item.prospecto.empresa" class="small text-muted">{{ item.prospecto.empresa }}</div>
                            </td>
                            <td class="small">{{ item.fecha_emision }}</td>
                            <td class="small">{{ item.fecha_vencimiento || 'Sin fecha' }}</td>
                            <td class="font-weight-bold text-dark">${{ formatMonto(item.total) }}</td>
                            <td>
                                <span class="badge" :class="badgeEstado(item.estado)">{{ item.estado }}</span>
                            </td>
                            <td class="small">{{ item.vendedor ? item.vendedor.usuario : 'N/A' }}</td>
                            <td>
                                <!-- Download PDF -->
                                <a :href="'/crm/cotizacion/pdf/' + item.id" target="_blank" class="btn btn-sm btn-outline-danger mr-1" title="Descargar PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                </a>

                                <!-- Edit -->
                                <button v-if="item.estado !== 'Convertida'" class="btn btn-sm btn-info text-white mr-1" title="Editar" @click="abrirModalEditar(item.id)">
                                    <i class="fa fa-edit"></i>
                                </button>

                                <!-- Convert to Pedido -->
                                <button v-if="item.estado !== 'Convertida'" class="btn btn-sm btn-success text-white mr-1" title="Convertir en Pedido" @click="convertirAPedido(item)">
                                    <i class="fa fa-shopping-cart"></i>
                                </button>

                                <!-- Revert Conversion -->
                                <button v-if="item.estado === 'Convertida'" class="btn btn-sm btn-warning text-white mr-1" title="Revertir Conversión" @click="revertirConversion(item)">
                                    <i class="fa fa-undo"></i>
                                </button>

                                <!-- Delete -->
                                <button class="btn btn-sm btn-danger text-white" title="Eliminar" @click="eliminarCotizacion(item.id)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.last_page > 1">
                <span class="small text-muted">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <a class="page-link" href="#" @click.prevent="listarCotizaciones(pagination.current_page - 1)">Anterior</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="listarCotizaciones(pagination.current_page + 1)">Siguiente</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Formulario Cotizacion -->
        <div class="modal fade" id="modalCotizacion" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width: 95% !important; width: 95% !important;" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">{{ modoEditar ? 'Editar Cotización #' + form.numero_cotizacion : 'Nueva Cotización Comercial' }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarCotizacion">
                        <div class="modal-body">
                            <!-- Header Form -->
                            <div class="row bg-light p-3 rounded mb-3">
                                <!-- Destinatario Selection Type -->
                                <div class="col-md-4 form-group mb-2">
                                    <label class="font-weight-semibold">Dirigido a (*)</label>
                                    <div class="d-flex align-items-center mt-1">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="tipoProspecto" value="prospecto" v-model="tipo_entidad" class="custom-control-input" @change="cambiarTipoEntidad">
                                            <label class="custom-control-label cursor-pointer font-weight-bold text-primary" for="tipoProspecto">
                                                <i class="fa fa-user-plus mr-1"></i>Prospecto / Lead
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="tipoCliente" value="cliente" v-model="tipo_entidad" class="custom-control-input" @change="cambiarTipoEntidad">
                                            <label class="custom-control-label cursor-pointer font-weight-bold text-success" for="tipoCliente">
                                                <i class="fa fa-building mr-1"></i>Cliente Registrado
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Live Search / Selection Component -->
                                <div class="col-md-8 form-group mb-2 position-relative">
                                    <label class="font-weight-semibold" :class="tipo_entidad === 'cliente' ? 'text-success' : 'text-primary'">
                                        <i class="fa mr-1" :class="tipo_entidad === 'cliente' ? 'fa-building-o' : 'fa-user-circle'"></i>
                                        {{ tipo_entidad === 'cliente' ? 'Buscar Cliente en el Sistema (*)' : 'Buscar Prospecto en CRM (*)' }}
                                    </label>

                                    <!-- Selected Card -->
                                    <div v-if="entidadSeleccionadaNombre" class="d-flex align-items-center justify-content-between p-2 rounded border" :class="tipo_entidad === 'cliente' ? 'bg-success-light border-success' : 'bg-primary-light border-primary'">
                                        <div class="font-weight-bold" :class="tipo_entidad === 'cliente' ? 'text-success' : 'text-primary'">
                                            <i class="fa mr-1" :class="tipo_entidad === 'cliente' ? 'fa-check-circle' : 'fa-user'"></i>
                                            {{ entidadSeleccionadaNombre }}
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold py-0 px-2" @click="deseleccionarEntidad">
                                            <i class="fa fa-times mr-1"></i>Cambiar
                                        </button>
                                    </div>

                                    <!-- Search Input -->
                                    <div v-else class="position-relative">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm" :placeholder="tipo_entidad === 'cliente' ? 'Escriba nombre de la empresa, razón social o NIT...' : 'Escriba nombre del prospecto o empresa...'" v-model="filtroBuscarEntidad" @input="buscarEntidades" @focus="buscarEntidades" />
                                            <div class="input-group-append" v-if="filtroBuscarEntidad">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="filtroBuscarEntidad = ''; buscarEntidades();">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Results Dropdown -->
                                        <div v-if="mostrarResultadosBusqueda && resultadosEntidad.length" class="list-group position-absolute w-100 shadow-lg border-0 rounded-bottom" style="z-index: 1050; max-height: 220px; overflow-y: auto; top: 100%; left: 0;">
                                            <button type="button" v-for="item in resultadosEntidad" :key="item.id" class="list-group-item list-group-item-action py-2 px-3 d-flex justify-content-between align-items-center" @click="seleccionarEntidadItem(item)">
                                                <div>
                                                    <strong class="d-block text-dark">{{ item.nombre || item.razonsocial }}</strong>
                                                    <small class="text-muted" v-if="item.empresa || item.num_documento || item.email">
                                                        <span v-if="item.empresa">Empresa: {{ item.empresa }} </span>
                                                        <span v-if="item.num_documento">NIT/CC: {{ item.num_documento }} </span>
                                                        <span v-if="item.email">({{ item.email }})</span>
                                                    </small>
                                                </div>
                                                <span class="badge" :class="tipo_entidad === 'cliente' ? 'badge-success' : 'badge-primary'">Seleccionar</span>
                                            </button>
                                        </div>
                                        <div v-if="mostrarResultadosBusqueda && !resultadosEntidad.length && filtroBuscarEntidad.length >= 2" class="position-absolute w-100 bg-white p-2 text-center text-muted small border shadow-sm" style="z-index: 1050; top: 100%; left: 0;">
                                            No se encontraron {{ tipo_entidad === 'cliente' ? 'clientes' : 'prospectos' }} para "{{ filtroBuscarEntidad }}"
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label class="font-weight-bold text-primary d-flex justify-content-between align-items-center">
                                        <span><i class="fa fa-hashtag mr-1"></i>N° Cotización (*)</span>
                                        <button v-if="!modoEditar" type="button" class="btn btn-xs btn-link p-0 text-primary text-decoration-none" @click="obtenerSiguienteNumero" title="Recalcular consecutivo sugerido">
                                            <i class="fa fa-refresh"></i> Auto
                                        </button>
                                    </label>
                                    <input type="text" v-model="form.numero_cotizacion" class="form-control form-control-sm font-weight-bold text-primary" placeholder="Ej. COT-2026-0006" required />
                                    <small class="form-text text-muted" style="font-size: 10px;">Editable: sigue consecutivo y salta si se ocupa</small>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="font-weight-semibold">Fecha Emisión (*)</label>
                                    <input type="date" v-model="form.fecha_emision" class="form-control form-control-sm" required />
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>Fecha Vencimiento</label>
                                    <input type="date" v-model="form.fecha_vencimiento" class="form-control form-control-sm" />
                                </div>

                                <div class="col-md-2 form-group">
                                    <label class="font-weight-semibold"><i class="fa fa-user text-primary mr-1"></i> Vendedor</label>
                                    <select v-model="form.user_id" class="form-control form-control-sm">
                                        <option :value="null">-- Vendedor --</option>
                                        <option v-for="v in arrayVendedores" :key="v.id" :value="v.id">
                                            {{ v.usuario }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>Estado</label>
                                    <select v-model="form.estado" class="form-control form-control-sm">
                                        <option value="Borrador">Borrador</option>
                                        <option value="Enviada">Enviada</option>
                                        <option value="Aprobada">Aprobada</option>
                                        <option value="Rechazada">Rechazada</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Condiciones de Pago</label>
                                    <input type="text" v-model="form.condiciones_pago" class="form-control form-control-sm" placeholder="Ej. 50% anticipo, 50% contra entrega" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Observaciones</label>
                                    <input type="text" v-model="form.observaciones" class="form-control form-control-sm" placeholder="Comentarios comerciales" />
                                </div>
                            </div>

                            <!-- Horizontal Cotizadores Toolbar -->
                            <div class="cotizadores-toolbar-card mb-3 p-2 bg-white rounded-15 border shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center">
                                        <h6 class="font-weight-900 text-dark mb-0 py-1 px-2">
                                            <i class="fa fa-list mr-1 text-primary"></i> Ítems de Cotización:
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold ml-2 py-1 px-2" @click="agregarFilaDetalle" title="Agregar renglón en blanco manual">
                                            <i class="fa fa-plus mr-1"></i> + Fila en blanco
                                        </button>
                                    </div>
                                    
                                    <div class="cotizadores-btn-group flex-grow-1 justify-content-end">
                                        <button type="button" class="btn-cotizador-pill pill-0" @click="abrirCotizadorModal('0')" title="Cotizador0: Productos Fabricados Desde Cero">
                                            <i class="fa fa-calculator mr-1 text-lime"></i> Cotizador0 (Desde Cero)
                                        </button>
                                        <button type="button" class="btn-cotizador-pill pill-r" @click="abrirCotizadorModal('R')" title="CotizadorR: Referencias Personalizadas del Catálogo">
                                            <i class="fa fa-bookmark mr-1 text-cyan"></i> CotizadorR (Referencias)
                                        </button>
                                        <button type="button" class="btn-cotizador-pill pill-g" @click="abrirCotizadorModal('G')" title="CotizadorG: Productos Generales Comercializados">
                                            <i class="fa fa-boxes mr-1 text-emerald"></i> CotizadorG (Generales)
                                        </button>
                                        <button type="button" class="btn-cotizador-pill pill-m" @click="abrirCotizadorModal('M')" title="CotizadorM: Cotización Manual Asistida">
                                            <i class="fa fa-sliders mr-1 text-amber"></i> CotizadorM (Manual)
                                        </button>
                                        <button type="button" class="btn-cotizador-pill pill-s" @click="abrirCotizadorModal('S')" title="CotizadorS: Cotizador Sencillo Directo">
                                            <i class="fa fa-pencil-square-o mr-1 text-light"></i> CotizadorS (Sencillo)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mb-3" style="overflow-x: auto;">
                                <table class="table table-bordered align-middle mb-0" style="min-width: 980px;">
                                    <thead class="bg-secondary text-white small">
                                        <tr>
                                            <th style="width: 22%;">Concepto / Producto (*)</th>
                                            <th style="width: 25%;">Descripción Adicional</th>
                                            <th style="width: 7%; min-width: 65px;" class="text-center">Cant.</th>
                                            <th style="width: 10%; min-width: 95px;" class="text-right">Precio Unit. ($)</th>
                                            <th style="width: 7%; min-width: 65px;" class="text-center">IVA (%)</th>
                                            <th style="width: 9%; min-width: 95px;" class="text-right">Total ($)</th>
                                            <th style="width: 20%; min-width: 230px;" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(det, index) in form.detalles" :key="index">
                                            <td>
                                                <div class="input-group input-group-sm mb-1">
                                                    <input type="text" v-model="det.concepto" class="form-control form-control-sm font-weight-bold" placeholder="Nombre del producto o servicio" required />
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-sm font-weight-bold px-2 text-white" :class="getColorBotonCotizador(det._cotizadorTipo)" @click="abrirMotorEnFila(index)" :title="'Editar en ' + getNombreCotizador(det._cotizadorTipo)">
                                                            <i class="fa fa-sliders mr-1"></i>{{ getNombreCortoCotizador(det._cotizadorTipo) }}
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-1">
                                                    <span class="badge" :class="getColorBadgeCotizador(det._cotizadorTipo)" style="font-size: 10px;">
                                                        <i class="fa fa-calculator mr-1"></i>{{ getNombreCotizador(det._cotizadorTipo) }}
                                                    </span>
                                                    <select v-model="det._cotizadorTipo" class="custom-select custom-select-sm py-0 px-1 font-weight-bold" style="height: 22px; font-size: 11px; width: auto; max-width: 145px;" title="Cambiar motor asignado para este ítem">
                                                        <option value="M">Cotizador M (Manual)</option>
                                                        <option value="R">Cotizador R (Referencias)</option>
                                                        <option value="0">Cotizador 0 (Desde Cero)</option>
                                                        <option value="G">Cotizador G (Generales)</option>
                                                        <option value="S">Cotizador S (Sencillo)</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <textarea v-model="det.descripcion" class="form-control form-control-sm" rows="3" style="min-height: 65px;" placeholder="Detalles de impresión, medidas, acabados, etc."></textarea>
                                            </td>
                                            <td>
                                                <input type="number" step="1" min="1" v-model.number="det.cantidad" class="form-control form-control-sm text-center font-weight-bold" @input="calcularTotales" required />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" v-model.number="det.precio_unitario" class="form-control form-control-sm text-right font-weight-bold text-dark" @input="calcularTotales" required />
                                            </td>
                                            <td class="text-center">
                                                <select v-model.number="det.iva_porcentaje" class="form-control form-control-sm font-weight-bold text-center" @change="calcularTotales">
                                                    <option :value="0">0%</option>
                                                    <option :value="19">19%</option>
                                                </select>
                                            </td>
                                            <td class="font-weight-bold text-right align-middle text-purple" style="white-space: nowrap;">
                                                ${{ formatMonto(det.total) }}
                                            </td>
                                            <td class="text-center align-middle" style="white-space: nowrap; width: 20%; min-width: 230px;">
                                                <div class="d-flex justify-content-center align-items-center" style="gap: 5px;">
                                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 font-weight-bold shadow-2xs" @click="abrirMotorEnFila(index)" :title="'Editar en ' + getNombreCotizador(det._cotizadorTipo)">
                                                        <i class="fa fa-edit mr-1"></i>Editar
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info py-1 px-2 font-weight-bold text-white shadow-2xs" @click="duplicarFilaDetalle(index)" title="Duplicar renglón">
                                                        <i class="fa fa-copy mr-1"></i>Duplicar
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger py-1 px-2 font-weight-bold shadow-2xs" @click="eliminarFilaDetalle(index)" title="Eliminar renglón">
                                                        <i class="fa fa-trash mr-1"></i>Borrar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals Box & Opciones de Totalización -->
                            <div class="row align-items-center mb-2">
                                <div class="col-md-7 mb-2 mb-md-0">
                                    <div class="card border-info p-2 rounded-10 bg-white shadow-2xs">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="checkMostrarTotales" v-model="form.mostrar_totales">
                                            <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="checkMostrarTotales">
                                                <i class="fa fa-calculator text-primary mr-1"></i> Mostrar Cuadro de Totales en el PDF / Vista del Cliente
                                            </label>
                                        </div>
                                        <div class="text-muted small ml-4 mt-1">
                                            El CRM siempre mantendrá la suma interna para control de ventas, pero si desmarcas esta opción se ocultará la casilla final de suma total en el PDF del cliente.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-0 p-3 transition-all" :class="form.mostrar_totales ? 'bg-light' : 'bg-light border border-warning'">
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                            <span class="small font-weight-bold text-muted text-uppercase">Resumen Comercial</span>
                                            <span class="badge" :class="form.mostrar_totales ? 'badge-success' : 'badge-warning'">
                                                <i class="fa mr-1" :class="form.mostrar_totales ? 'fa-eye' : 'fa-eye-slash'"></i>
                                                {{ form.mostrar_totales ? 'Totales Visibles en PDF' : 'Totales Ocultos en PDF' }}
                                            </span>
                                        </div>

                                        <div v-if="!form.mostrar_totales" class="alert alert-warning py-1 px-2 mb-2 small text-center font-weight-bold">
                                            <i class="fa fa-eye-slash mr-1"></i> El PDF se imprimirá SIN cuadro de totales
                                        </div>

                                        <div :style="!form.mostrar_totales ? 'text-decoration: line-through; opacity: 0.45;' : ''">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Subtotal Neto:</span>
                                                <span class="font-weight-bold">${{ formatMonto(form.subtotal) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>IVA Acumulado:</span>
                                                <span>${{ formatMonto(form.iva) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between pt-2 border-top h5 font-weight-bold text-primary mb-0">
                                                <span>TOTAL CRM:</span>
                                                <span>${{ formatMonto(form.total) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary font-weight-bold" :disabled="cargando">
                                {{ cargando ? 'Guardando...' : 'Guardar Cotización' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modales de los 5 Cotizadores -->
        <cotizador-0 ref="cotizador0" :modal="modalCotizador0" @productoCustom="recibirProductoCotizador" @cerrarCotizador="modalCotizador0 = 0"></cotizador-0>
        <cotizador-r ref="cotizadorR" :modal="modalCotizadorR" @productoCustom="recibirProductoCotizador" @cerrarCotizador="modalCotizadorR = 0"></cotizador-r>
        <cotizador-g ref="cotizadorG" :modal="modalCotizadorG" @productoCustom="recibirProductoCotizador" @cerrarCotizador="modalCotizadorG = 0"></cotizador-g>
        <cotizador-m ref="cotizadorM" :modal="modalCotizadorM" @productoCustom="recibirProductoCotizador" @cerrarCotizador="modalCotizadorM = 0"></cotizador-m>
        <cotizador-s ref="cotizadorS" :modal="modalCotizadorS" @productoCustom="recibirProductoCotizador" @cerrarCotizador="modalCotizadorS = 0"></cotizador-s>
    </div>
</template>

<script>
    import Cotizador0 from '../partes/Cotizador0.vue';
    import CotizadorR from '../partes/CotizadorR.vue';
    import CotizadorG from '../partes/CotizadorG.vue';
    import CotizadorM from '../partes/CotizadorM.vue';
    import CotizadorS from '../partes/CotizadorS.vue';

    export default {
        components: {
            Cotizador0,
            CotizadorR,
            CotizadorG,
            CotizadorM,
            CotizadorS
        },
        props: ['vendedorId', 'userRole'],
        data() {
            return {
                buscar: '',
                estadoFiltro: '',
                arrayCotizaciones: [],
                arrayProspectos: [],
                arrayClientes: [],
                arrayVendedores: [],
                tipo_entidad: 'prospecto',
                filtroBuscarEntidad: '',
                resultadosEntidad: [],
                mostrarResultadosBusqueda: false,
                entidadSeleccionadaNombre: '',
                pagination: {},
                modoEditar: false,
                cargando: false,
                modalCotizador0: 0,
                modalCotizadorR: 0,
                modalCotizadorG: 0,
                modalCotizadorM: 0,
                modalCotizadorS: 0,
                itemIndexEditando: null,
                ultimoCotizadorAbierto: 'R',
                form: {
                    id: 0,
                    numero_cotizacion: '',
                    prospecto_id: null,
                    cliente_id: null,
                    fecha_emision: new Date().toISOString().slice(0, 10),
                    fecha_vencimiento: '',
                    condiciones_pago: '50% Anticipo, 50% Contra Entrega',
                    observaciones: '',
                    estado: 'Borrador',
                    subtotal: 0,
                    descuento: 0,
                    iva: 0,
                    total: 0,
                    detalles: [
                        { concepto: '', descripcion: '', cantidad: 1, precio_unitario: 0, descuento_porcentaje: 0, subtotal: 0, iva: 0, total: 0 }
                    ]
                }
            };
        },
        watch: {
            vendedorId() {
                this.listarCotizaciones(1);
            }
        },
        methods: {
            formatMonto(val) {
                if (!val) return '0.00';
                return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            badgeEstado(est) {
                switch(est) {
                    case 'Borrador': return 'badge-secondary';
                    case 'Enviada': return 'badge-info';
                    case 'Aprobada': return 'badge-success';
                    case 'Rechazada': return 'badge-danger';
                    case 'Convertida': return 'badge-primary';
                    default: return 'badge-light';
                }
            },
            listarCotizaciones(page = 1) {
                axios.get('/crm/cotizacion', {
                    params: {
                        page: page,
                        buscar: this.buscar,
                        estado: this.estadoFiltro,
                        vendedor_id: this.vendedorId
                    }
                })
                .then(response => {
                    this.arrayCotizaciones = response.data.cotizaciones.data;
                    this.pagination = response.data.pagination;
                });
            },
            cargarVendedoresSelect() {
                axios.get('/crm/vendedores/select')
                .then(response => {
                    this.arrayVendedores = response.data.vendedores || [];
                });
            },
            buscarEntidades() {
                this.mostrarResultadosBusqueda = true;
                const q = (this.filtroBuscarEntidad || '').trim();

                if (this.tipo_entidad === 'prospecto') {
                    axios.get('/crm/prospecto/select', {
                        params: { filtro: q, vendedor_id: this.vendedorId }
                    })
                    .then(response => {
                        this.resultadosEntidad = response.data.prospectos || [];
                    });
                } else {
                    axios.get('/cliente/selectClientes', {
                        params: { filtro: q }
                    })
                    .then(response => {
                        this.resultadosEntidad = response.data.clientes || [];
                    });
                }
            },
            seleccionarEntidadItem(item) {
                if (this.tipo_entidad === 'prospecto') {
                    this.form.prospecto_id = item.id;
                    this.form.cliente_id = null;
                    let emp = item.empresa ? ` (${item.empresa})` : '';
                    this.entidadSeleccionadaNombre = `${item.nombre}${emp}`;
                } else {
                    this.form.cliente_id = item.id;
                    this.form.prospecto_id = null;
                    let doc = item.num_documento ? ` (Doc: ${item.num_documento})` : '';
                    let nom = item.nombre || item.razonsocial;
                    this.entidadSeleccionadaNombre = `${nom}${doc}`;
                }
                this.mostrarResultadosBusqueda = false;
                this.resultadosEntidad = [];
                this.filtroBuscarEntidad = '';
            },
            deseleccionarEntidad() {
                this.form.prospecto_id = null;
                this.form.cliente_id = null;
                this.entidadSeleccionadaNombre = '';
                this.filtroBuscarEntidad = '';
                this.resultadosEntidad = [];
                this.mostrarResultadosBusqueda = true;
                this.$nextTick(() => {
                    this.buscarEntidades();
                });
            },
            cambiarTipoEntidad() {
                this.deseleccionarEntidad();
            },
            limpiarForm() {
                this.tipo_entidad = 'prospecto';
                this.entidadSeleccionadaNombre = '';
                this.filtroBuscarEntidad = '';
                this.resultadosEntidad = [];
                this.mostrarResultadosBusqueda = false;
                this.form = {
                    id: 0,
                    numero_cotizacion: '',
                    prospecto_id: null,
                    cliente_id: null,
                    user_id: this.vendedorId || null,
                    fecha_emision: new Date().toISOString().slice(0, 10),
                    fecha_vencimiento: '',
                    condiciones_pago: '50% Anticipo, 50% Contra Entrega',
                    observaciones: '',
                    estado: 'Borrador',
                    subtotal: 0,
                    descuento: 0,
                    iva: 0,
                    total: 0,
                    mostrar_totales: true,
                    detalles: [
                        { concepto: '', descripcion: '', cantidad: 1, precio_unitario: 0, iva_porcentaje: 19, subtotal: 0, iva: 0, total: 0, _cotizadorTipo: 'M' }
                    ]
                };
            },
            agregarFilaDetalle() {
                this.form.detalles.push({
                    concepto: '',
                    descripcion: '',
                    cantidad: 1,
                    precio_unitario: 0,
                    iva_porcentaje: 19,
                    subtotal: 0,
                    iva: 0,
                    total: 0,
                    _cotizadorTipo: 'M'
                });
            },
            abrirCotizadorModal(tipo, index = null) {
                if (typeof $ !== 'undefined') $(document).off('focusin.bs.modal');
                this.itemIndexEditando = index;
                this.ultimoCotizadorAbierto = tipo;

                let det = null;
                if (index !== null && this.form.detalles[index]) {
                    det = this.form.detalles[index];
                }

                const payload = det ? (det._productoRaw || det) : null;

                if (tipo === '0') {
                    this.modalCotizador0 = 1;
                    this.$nextTick(() => {
                        if (this.$refs.cotizador0) {
                            if (payload && typeof this.$refs.cotizador0.cargarProductoExistente === 'function') {
                                this.$refs.cotizador0.cargarProductoExistente(payload);
                            } else if (typeof this.$refs.cotizador0.resetCotizador === 'function') {
                                this.$refs.cotizador0.resetCotizador();
                            }
                        }
                    });
                } else if (tipo === 'R') {
                    this.modalCotizadorR = 1;
                    this.$nextTick(() => {
                        if (this.$refs.cotizadorR) {
                            if (payload && typeof this.$refs.cotizadorR.cargarProductoExistente === 'function') {
                                this.$refs.cotizadorR.cargarProductoExistente(payload);
                            } else if (typeof this.$refs.cotizadorR.resetCotizador === 'function') {
                                this.$refs.cotizadorR.resetCotizador();
                            }
                        }
                    });
                } else if (tipo === 'G') {
                    this.modalCotizadorG = 1;
                    this.$nextTick(() => {
                        if (this.$refs.cotizadorG && payload && typeof this.$refs.cotizadorG.cargarProductoExistente === 'function') {
                            this.$refs.cotizadorG.cargarProductoExistente(payload);
                        }
                    });
                } else if (tipo === 'S') {
                    this.modalCotizadorS = 1;
                    this.$nextTick(() => {
                        if (this.$refs.cotizadorS && payload && typeof this.$refs.cotizadorS.cargarProductoExistente === 'function') {
                            this.$refs.cotizadorS.cargarProductoExistente(payload);
                        }
                    });
                } else {
                    // Default 'M' (CotizadorM / Manual)
                    this.modalCotizadorM = 1;
                    this.$nextTick(() => {
                        if (this.$refs.cotizadorM) {
                            if (payload && typeof this.$refs.cotizadorM.cargarProductoExistente === 'function') {
                                this.$refs.cotizadorM.cargarProductoExistente(payload);
                            } else if (typeof this.$refs.cotizadorM.resetCotizador === 'function') {
                                this.$refs.cotizadorM.resetCotizador();
                            }
                        }
                    });
                }
            },
            abrirMotorNuevoItem() {
                this.abrirCotizadorModal('M', null);
            },
            abrirMotorEnFila(index) {
                const det = this.form.detalles[index];
                if (!det) return;
                let tipo = det._cotizadorTipo || (det.cotizador_tipo ? det.cotizador_tipo : 'M');
                this.abrirCotizadorModal(tipo, index);
            },
            recibirProductoCotizador(producto) {
                if (!producto) return;
                const cant = parseFloat(producto.cantidad) || 1;
                const totalMonto = parseFloat(producto.total) || 0;
                const precioUnit = parseFloat(producto.precio_unitario) || (totalMonto / cant);
                const conceptoNombre = producto.concepto || producto.nombre || 'Producto Cotizado';
                let especificaciones = producto.descripcion || '';
                if (!especificaciones && producto.atributos && Array.isArray(producto.atributos)) {
                    especificaciones = producto.atributos.map(a => {
                        let val = '';
                        if (a.open && a.open.labelOpAtributo) val = a.open.labelOpAtributo;
                        else if (a.valor) val = a.valor;
                        return a.nombre ? `${a.nombre}: ${val}` : val;
                    }).filter(Boolean).join(', ');
                }
                if (especificaciones) {
                    especificaciones = especificaciones.replace(/\|\s*Desglose:.*$/gi, '')
                                                       .replace(/-\s*Desglose:.*$/gi, '')
                                                       .replace(/Desglose:.*$/gi, '')
                                                       .trim();
                }
                const ivaPct = producto.iva !== undefined ? parseFloat(producto.iva) : 19;

                const detData = {
                    articulo_id: producto.articulo_id || (producto.articulo ? producto.articulo.id : null),
                    concepto: conceptoNombre,
                    descripcion: especificaciones,
                    cantidad: cant,
                    precio_unitario: Math.round(precioUnit * 100) / 100,
                    iva_porcentaje: ivaPct,
                    subtotal: Math.round((cant * precioUnit) * 100) / 100,
                    iva: Math.round((cant * precioUnit * (ivaPct / 100)) * 100) / 100,
                    total: Math.round(totalMonto * 100) / 100,
                    _cotizadorTipo: producto._cotizadorTipo || this.ultimoCotizadorAbierto || 'M',
                    _productoRaw: JSON.parse(JSON.stringify(producto))
                };

                if (this.itemIndexEditando !== null && this.form.detalles[this.itemIndexEditando]) {
                    this.$set(this.form.detalles, this.itemIndexEditando, detData);
                } else {
                    if (this.form.detalles.length === 1 && !this.form.detalles[0].concepto) {
                        this.form.detalles = [];
                    }
                    this.form.detalles.push(detData);
                }

                this.calcularTotales();
                this.modalCotizador0 = 0;
                this.modalCotizadorR = 0;
                this.modalCotizadorG = 0;
                this.modalCotizadorM = 0;
                this.modalCotizadorS = 0;
                this.itemIndexEditando = null;
            },

            getNombreCotizador(tipo) {
                if (tipo === '0') return 'Cotizador 0 (Desde Cero)';
                if (tipo === 'R') return 'Cotizador R (Referencias)';
                if (tipo === 'G') return 'Cotizador G (Generales)';
                if (tipo === 'S') return 'Cotizador S (Sencillo)';
                return 'Cotizador M (Manual)';
            },
            getNombreCortoCotizador(tipo) {
                if (tipo === '0') return 'Cotiz. 0';
                if (tipo === 'R') return 'Cotiz. R';
                if (tipo === 'G') return 'Cotiz. G';
                if (tipo === 'S') return 'Cotiz. S';
                return 'Cotiz. M';
            },
            getColorBadgeCotizador(tipo) {
                if (tipo === '0') return 'badge-success';
                if (tipo === 'R') return 'badge-info text-white';
                if (tipo === 'G') return 'badge-success text-white';
                if (tipo === 'S') return 'badge-secondary text-white';
                return 'badge-warning text-dark font-weight-bold';
            },
            getColorBotonCotizador(tipo) {
                if (tipo === '0') return 'btn-success';
                if (tipo === 'R') return 'btn-info';
                if (tipo === 'G') return 'btn-success';
                if (tipo === 'S') return 'btn-secondary';
                return 'btn-warning text-dark';
            },

            moverFilaArriba(index) {
                if (index > 0) {
                    const item = this.form.detalles.splice(index, 1)[0];
                    this.form.detalles.splice(index - 1, 0, item);
                    this.calcularTotales();
                }
            },
            moverFilaAbajo(index) {
                if (index < this.form.detalles.length - 1) {
                    const item = this.form.detalles.splice(index, 1)[0];
                    this.form.detalles.splice(index + 1, 0, item);
                    this.calcularTotales();
                }
            },
            duplicarFilaDetalle(index) {
                if (!this.form.detalles || !this.form.detalles[index]) return;
                const itemOriginal = this.form.detalles[index];
                const itemCopia = JSON.parse(JSON.stringify(itemOriginal));
                this.form.detalles.splice(index + 1, 0, itemCopia);
                this.calcularTotales();
            },
            eliminarFilaDetalle(index) {
                this.form.detalles.splice(index, 1);
                if (this.form.detalles.length === 0) {
                    this.form.detalles.push({
                        concepto: '',
                        descripcion: '',
                        cantidad: 1,
                        precio_unitario: 0,
                        iva_porcentaje: 19,
                        subtotal: 0,
                        iva: 0,
                        total: 0,
                        _cotizadorTipo: 'M'
                    });
                }
                this.calcularTotales();
            },
            calcularTotales() {
                let subtotalAcum = 0;
                let ivaAcum = 0;

                this.form.detalles.forEach(det => {
                    const cant = parseFloat(det.cantidad) || 0;
                    const precio = parseFloat(det.precio_unitario) || 0;
                    const ivaPct = parseFloat(det.iva_porcentaje !== undefined ? det.iva_porcentaje : (det.iva || 0)) || 0;

                    const bruto = cant * precio;
                    const montoIva = bruto * (ivaPct / 100);
                    const totalRenglon = bruto + montoIva;

                    det.iva_porcentaje = ivaPct;
                    det.subtotal = Math.round(bruto * 100) / 100;
                    det.iva = Math.round(montoIva * 100) / 100;
                    det.total = Math.round(totalRenglon * 100) / 100;

                    subtotalAcum += bruto;
                    ivaAcum += montoIva;
                });

                this.form.subtotal = Math.round(subtotalAcum * 100) / 100;
                this.form.descuento = 0;
                this.form.iva = Math.round(ivaAcum * 100) / 100;
                this.form.total = Math.round((subtotalAcum + ivaAcum) * 100) / 100;
            },
            abrirModalCrear() {
                this.modoEditar = false;
                this.limpiarForm();
                this.cargarVendedoresSelect();
                this.obtenerSiguienteNumero();
                $('#modalCotizacion').modal('show');
            },
            obtenerSiguienteNumero() {
                axios.get('/crm/cotizacion/siguiente-numero')
                .then(response => {
                    if (response.data && response.data.siguiente) {
                        this.form.numero_cotizacion = response.data.siguiente;
                    }
                })
                .catch(err => {
                    console.error('Error al obtener siguiente número:', err);
                });
            },
            abrirModalEditar(id) {
                this.modoEditar = true;
                this.limpiarForm();
                this.cargarVendedoresSelect();
                axios.get('/crm/cotizacion/' + id)
                .then(response => {
                    this.form = response.data.cotizacion;
                    if (this.form.cliente) {
                        this.tipo_entidad = 'cliente';
                        let doc = this.form.cliente.num_documento ? ` (Doc: ${this.form.cliente.num_documento})` : '';
                        let nom = this.form.cliente.nombre || this.form.cliente.razonsocial;
                        this.entidadSeleccionadaNombre = `${nom}${doc}`;
                    } else if (this.form.prospecto) {
                        this.tipo_entidad = 'prospecto';
                        let emp = this.form.prospecto.empresa ? ` (${this.form.prospecto.empresa})` : '';
                        this.entidadSeleccionadaNombre = `${this.form.prospecto.nombre}${emp}`;
                    } else {
                        this.tipo_entidad = 'prospecto';
                        this.entidadSeleccionadaNombre = '';
                    }
                    if (!this.form.detalles || !this.form.detalles.length) {
                        this.form.detalles = [{ concepto: '', descripcion: '', cantidad: 1, precio_unitario: 0, iva_porcentaje: 19, subtotal: 0, iva: 0, total: 0, _cotizadorTipo: 'M' }];
                    } else {
                        this.form.detalles.forEach(d => {
                            if (d.iva_porcentaje === undefined) {
                                d.iva_porcentaje = d.iva > 0 ? 19 : 0;
                            }
                            if (!d._cotizadorTipo) {
                                d._cotizadorTipo = d.cotizador_tipo || 'M';
                            }
                        });
                    }
                    const rawObs = this.form.observaciones || '';
                    this.form.mostrar_totales = !rawObs.includes('[NO_TOTALIZAR]');
                    this.form.observaciones = rawObs.replace(/\s*\[NO_TOTALIZAR\]/gi, '').trim();
                    this.calcularTotales();
                    $('#modalCotizacion').modal('show');
                });
            },
            guardarCotizacion() {
                if (this.tipo_entidad === 'prospecto' && !this.form.prospecto_id) {
                    Swal.fire('Atención', 'Por favor selecciona un prospecto de la lista.', 'warning');
                    return;
                }
                if (this.tipo_entidad === 'cliente' && !this.form.cliente_id) {
                    Swal.fire('Atención', 'Por favor selecciona un cliente de la lista.', 'warning');
                    return;
                }
                if (!this.form.detalles.length || !this.form.detalles[0].concepto) {
                    Swal.fire('Atención', 'Ingresa al menos un ítem con concepto en la cotización.', 'warning');
                    return;
                }

                let obsLimpia = (this.form.observaciones || '').replace(/\s*\[NO_TOTALIZAR\]/gi, '').trim();
                let obsParaGuardar = obsLimpia;
                if (!this.form.mostrar_totales) {
                    obsParaGuardar = (obsLimpia + ' [NO_TOTALIZAR]').trim();
                }

                let prospectoId = this.tipo_entidad === 'prospecto' ? this.form.prospecto_id : null;
                let clienteId = this.tipo_entidad === 'cliente' ? this.form.cliente_id : null;

                const payload = {
                    ...this.form,
                    prospecto_id: prospectoId,
                    cliente_id: clienteId,
                    observaciones: obsParaGuardar
                };

                this.cargando = true;
                const url = this.modoEditar ? '/crm/cotizacion/actualizar' : '/crm/cotizacion/registrar';
                const method = this.modoEditar ? 'put' : 'post';

                axios[method](url, payload)
                .then(response => {
                    this.cargando = false;
                    $('#modalCotizacion').modal('hide');
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    this.listarCotizaciones(1);
                })
                .catch(error => {
                    this.cargando = false;
                    let msg = 'No se pudo guardar la cotización';
                    if (error.response && error.response.data) {
                        if (error.response.data.message) {
                            msg = error.response.data.message;
                        } else if (error.response.data.errors) {
                            msg = Object.values(error.response.data.errors).flat().join('<br>');
                        }
                    }
                    Swal.fire('Error', msg, 'error');
                });
            },
            convertirAPedido(item) {
                Swal.fire({
                    title: '¿Convertir en Pedido?',
                    text: `La cotización ${item.numero_cotizacion} pasará a estado Convertida y se creará un Pedido oficial en el sistema de ventas.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, convertir en Pedido',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.post('/crm/cotizacion/convertir-pedido', { id: item.id })
                        .then(response => {
                            Swal.fire('¡Convertida!', response.data.message, 'success');
                            this.listarCotizaciones(1);
                        })
                        .catch(error => {
                            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error al convertir cotización';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            },
            revertirConversion(item) {
                Swal.fire({
                    title: '¿Revertir conversión?',
                    text: `Se eliminará el Pedido #${item.pedido_id || ''} y sus órdenes asociadas. La cotización volverá a estar editable.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, revertir',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.post('/crm/cotizacion/revertir-conversion', { id: item.id })
                        .then(response => {
                            Swal.fire('¡Revertida!', response.data.message, 'success');
                            this.listarCotizaciones(1);
                        })
                        .catch(error => {
                            const msg = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Error al revertir conversión';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            },
            eliminarCotizacion(id) {
                Swal.fire({
                    title: '¿Eliminar cotización?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.delete('/crm/cotizacion/eliminar', { data: { id: id } })
                        .then(response => {
                            Swal.fire('Eliminada', response.data.message, 'success');
                            this.listarCotizaciones(1);
                        });
                    }
                });
            }
        },
        mounted() {
            this.listarCotizaciones(1);
        }
    };
</script>

<style scoped>
.btn-purple {
    background-color: #7d3c98 !important;
    border-color: #7d3c98 !important;
    color: #ffffff !important;
}
.btn-purple:hover {
    background-color: #5b2c6f !important;
    border-color: #5b2c6f !important;
    color: #ffffff !important;
}
.text-purple {
    color: #7d3c98 !important;
}

.cotizadores-toolbar-card {
    border-radius: 12px !important;
    background: #ffffff !important;
}

.cotizadores-btn-group {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 8px !important;
    row-gap: 8px !important;
}

.btn-cotizador-pill {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 7px 14px !important;
    font-size: 0.8rem !important;
    font-weight: 800 !important;
    border-radius: 10px !important;
    border: none !important;
    color: #ffffff !important;
    cursor: pointer !important;
    white-space: nowrap !important;
    transition: all 0.2s ease-in-out !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1) !important;
    text-decoration: none !important;
}

.btn-cotizador-pill:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 14px rgba(0, 0, 0, 0.2) !important;
    color: #ffffff !important;
    text-decoration: none !important;
}

.pill-0 { background: linear-gradient(135deg, #7d3c98 0%, #5b2c6f 100%) !important; }
.pill-r { background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%) !important; }
.pill-g { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important; }
.pill-m { background: linear-gradient(135deg, #fd7e14 0%, #d35400 100%) !important; }
.pill-s { background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important; }

.text-lime { color: #a0ef6e !important; }
.text-cyan { color: #80deea !important; }
.text-emerald { color: #a5d6a7 !important; }
.text-amber { color: #ffe082 !important; }

.bg-primary-light {
    background-color: #f0f9ff !important;
}
.bg-success-light {
    background-color: #f0fdf4 !important;
}
</style>
