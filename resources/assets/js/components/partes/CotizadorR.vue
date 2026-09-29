<template>
    <div class="modal fade" :class="{'mostrar' : modal}" role="dialog" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-20 overflow-hidden">
                <!-- Header Morado LupaPack -->
                <div class="modal-header text-white py-3 px-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #7d3c98 0%, #5b2c6f 100%);">
                    <h5 class="modal-title font-weight-900 mb-0">
                        <i class="fa fa-calculator mr-2" style="color: #a0ef6e;"></i> COTIZADOR R - REFERENCIAS PERSONALIZADAS
                    </h5>
                    <button type="button" class="close text-white" @click="cerrarModal()">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <div class="row">
                        <!-- Columna Izquierda: Configuración de Tipo, Medidas y Cantidad -->
                        <div class="col-lg-6 mb-3">
                            <!-- Nombre o Descripción del Trabajo -->
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-purple">
                                <label class="premium-label text-dark font-weight-900 mb-1">
                                    <i class="fa fa-tag mr-1 text-brand-purple"></i> Nombre o Descripción del Producto / Trabajo
                                </label>
                                <input type="text" class="form-control premium-input-sm font-weight-900 text-dark" v-model="nombreTrabajoCustom" placeholder="Ej: Caja SH1, Volante Promocional 20x30, Bolsa Kraft, etc.">
                            </div>

                            <!-- Modelo / Tipo de Producto -->
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-purple">
                                <label class="premium-label text-dark font-weight-900 mb-2">
                                    <i class="fa fa-cubes mr-1 text-brand-purple"></i> Modelo / Tipo de Producto
                                </label>
                                <select class="form-control premium-input-sm font-weight-800 text-dark mb-2" v-model="tipoSeleccionadoId" @change="alCambiarTipoSelect($event.target.value)">
                                    <option value="" disabled>Seleccione un Tipo de Producto...</option>
                                    <option v-for="tipo in tiposBD" :key="'bd_opt_' + tipo.id" :value="tipo.id">
                                        {{ tipo.nombre }} ({{ (tipo.atributosTienda ? tipo.atributosTienda.length : 0) + (tipo.atributos ? tipo.atributos.length : 0) }} Atributos Configurados)
                                    </option>
                                </select>

                                <!-- Botones de acceso rápido a tipos de producto -->
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <button v-for="t in tiposBD" :key="'btn_t_' + t.id"
                                            type="button" class="btn btn-sm mr-2 mb-2 font-weight-800 rounded-10 shadow-2xs"
                                            :class="tipoBDSeleccionado && tipoBDSeleccionado.id === t.id ? 'btn-purple text-white' : 'btn-outline-purple'"
                                            @click="seleccionarTipoBD(t)">
                                        {{ t.nombre }}
                                    </button>
                                </div>
                            </div>

                            <!-- Medidas 3D si aplica -->
                            <div v-if="tipoBDSeleccionado && (tipoBDSeleccionado.formula_ancho || tipoBDSeleccionado.formula_largo)" class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-purple">
                                <label class="premium-label text-dark font-weight-900 mb-2">Dimensiones 3D del Empaque (cm)</label>
                                <div class="row">
                                    <div class="col-4 mb-2">
                                        <label class="small font-weight-800 text-muted">Ancho (W)</label>
                                        <input type="number" step="0.1" class="form-control premium-input-sm font-weight-700" v-model.number="cajaData.ancho_box" @input="onInputCaja3D()">
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="small font-weight-800 text-muted">Largo (L)</label>
                                        <input type="number" step="0.1" class="form-control premium-input-sm font-weight-700" v-model.number="cajaData.largo_box" @input="onInputCaja3D()">
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="small font-weight-800 text-muted">Alto (H)</label>
                                        <input type="number" step="0.1" class="form-control premium-input-sm font-weight-700" v-model.number="cajaData.alto_box" @input="onInputCaja3D()">
                                    </div>
                                </div>
                            </div>

                            <!-- Selección Única de Pliego Base (Tabla Ajustes) -->
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-purple" v-if="pliegosBaseOpciones && pliegosBaseOpciones.length > 0">
                                <label class="premium-label text-dark font-weight-900 mb-2">
                                    <i class="fa fa-file-text-o mr-1 text-brand-purple"></i> Seleccionar Pliego Base (Ajustes)
                                </label>
                                <select class="form-control premium-input-sm font-weight-800 text-dark" v-model="pliegoBaseSeleccionado" @change="alCambiarPliegoBase()">
                                    <option value="" disabled>Seleccione un Pliego Base...</option>
                                    <option v-for="pb in pliegosBaseOpciones" :key="'pb_' + pb.valor" :value="pb.valor">
                                        {{ pb.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Dimensiones Planas (cm) y Cantidad -->
                            <div class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="premium-label mb-1">Ancho Plano (cm)</label>
                                        <input type="number" step="0.1" min="0" class="form-control premium-input-sm font-weight-900 text-dark" v-model.number="anchoPlano" @input="debouncedCalcular()" @change="calcularConWebLupa()">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="premium-label mb-1">Largo Plano (cm)</label>
                                        <input type="number" step="0.1" min="0" class="form-control premium-input-sm font-weight-900 text-dark" v-model.number="largoPlano" @input="debouncedCalcular()" @change="calcularConWebLupa()">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="premium-label mb-1">Cantidad a Cotizar (Unidades)</label>
                                    <div class="d-flex align-items-center flex-wrap mb-2">
                                        <button v-for="c in [100, 500, 1000, 2500, 5000]" :key="c" 
                                                type="button" class="btn btn-sm font-weight-800 mr-2 mb-2 rounded-10 shadow-2xs"
                                                :class="cantidad === c ? 'btn-dark text-white' : 'btn-outline-dark'"
                                                @click="cantidad = c; calcularConWebLupa();">
                                            {{ formatear(c) }} u.
                                        </button>
                                    </div>
                                    <input type="number" class="form-control premium-input-sm font-weight-900 text-primary" v-model.number="cantidad" min="1" placeholder="Ingresa otra cantidad..." @input="debouncedCalcular()" @change="calcularConWebLupa()">
                                </div>
                            </div>

                            <!-- Opciones de Cabida y Aprovechamiento por Tamaño de Pliego Base -->
                            <div v-if="opcionesCabidaList && opcionesCabidaList.length > 0" class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white border-left-success">
                                <label class="premium-label text-dark font-weight-900 mb-2 d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-th-large mr-1 text-success"></i> Opciones de Cabida y Aprovechamiento ({{ pliegoBaseSeleccionado || '70x100' }})</span>
                                    <span class="badge badge-success px-2 py-1 font-weight-800" style="font-size: 10px;">Optimizado WebLupa</span>
                                </label>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover border mb-0" style="font-size: 11.5px;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Formato / Tamaño</th>
                                                <th class="text-center">Cabida</th>
                                                <th class="text-center">Aprovechamiento</th>
                                                <th class="text-center">Pliegos Base</th>
                                                <th class="text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(opc, idx) in opcionesCabidaList" :key="idx" 
                                                :class="cabidaInput == opc.cabida ? 'table-warning font-weight-bold' : (opc.desperdicio_pct > 35 ? 'text-muted' : '')">
                                                <td class="align-middle">
                                                    <strong>{{ opc.nombre }}</strong>
                                                    <div class="text-muted small" style="font-size: 10px;">{{ opc.tamano }}</div>
                                                </td>
                                                <td class="text-center align-middle font-weight-900 text-purple" style="font-size: 12.5px;">
                                                    {{ opc.cabida }} pieza(s)
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge" 
                                                          :class="opc.desperdicio_pct <= 15 ? 'badge-success' : (opc.desperdicio_pct <= 30 ? 'badge-warning text-dark' : 'badge-danger')">
                                                        {{ (100 - opc.desperdicio_pct).toFixed(1) }}% útil ({{ opc.desperdicio_pct.toFixed(1) }}% desp.)
                                                    </span>
                                                </td>
                                                <td class="text-center align-middle font-weight-800">
                                                    {{ opc.pliegos_base }} u.
                                                </td>
                                                <td class="text-right align-middle">
                                                    <button type="button" 
                                                            class="btn btn-xs rounded-10 font-weight-800"
                                                            :class="cabidaInput == opc.cabida ? 'btn-success text-dark font-weight-900' : 'btn-outline-primary'"
                                                            @click="seleccionarOpcionCabida(opc)">
                                                        <i class="fa fa-check-circle mr-1"></i> {{ cabidaInput == opc.cabida ? 'Seleccionado' : 'Usar' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Atributos del Producto (Terminado, Tintas, Papel, Acabados) -->
                            <div v-if="tieneAtributosTienda" class="card border-0 shadow-sm rounded-15 p-3 mb-3 bg-white">
                                <label class="premium-label text-uppercase font-weight-900 text-brand-purple mb-2">
                                    <i class="fa fa-sliders mr-1"></i> Especificaciones del Producto
                                </label>

                                <div class="row">
                                    <div class="col-12 mb-3" v-for="(items, grupo) in atributosTiendaAgrupados" :key="'grp_' + grupo">
                                        <label class="small font-weight-900 text-dark mb-1 text-uppercase">{{ grupo }}</label>

                                        <!-- Selección múltiple (Checkboxes) como Terminado en Imagen 1 -->
                                        <template v-if="items[0] && items[0].seleccion_multiple">
                                            <div class="p-2 border rounded-10 bg-light" style="max-height: 140px; overflow-y: auto;">
                                                <div v-for="item in items" :key="item.id" class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox" class="custom-control-input" :id="'chk_attr_' + item.id" :value="item.nombre" v-model="opcionesMultiplesTienda[grupo]" @change="calcularConWebLupa()">
                                                    <label class="custom-control-label font-weight-700 text-dark small d-flex justify-content-between pr-2" :for="'chk_attr_' + item.id">
                                                        <span>{{ item.nombre }}</span>
                                                        <span v-if="parseFloat(item.valor_extra) > 0" class="text-success font-weight-800 float-right">(+${{ formatear(item.valor_extra) }})</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Select Único -->
                                        <template v-else>
                                            <select class="form-control premium-input-sm font-weight-700 text-dark"
                                                    :value="opcionesSeleccionadasTienda[grupo]"
                                                    @change="$set(opcionesSeleccionadasTienda, grupo, $event.target.value); calcularConWebLupa();">
                                                <option v-for="item in items" :key="'opt_t_' + item.id" :value="item.id">
                                                    {{ item.nombre }}
                                                    <template v-if="parseFloat(item.valor_extra) > 0">
                                                        (+$ {{ formatear(item.valor_extra) }})
                                                    </template>
                                                </option>
                                            </select>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Recuadro Amarillo de Cálculo de Producción WebLupa (Imagen 1) -->
                        <div class="col-lg-6 mb-3">
                            <!-- Recuadro Dorado/Amarillo idéntico a Imagen 1 -->
                            <div class="calc-gold-box p-3 rounded-15 shadow-sm mb-3">
                                <div class="d-flex align-items-center mb-2 font-weight-900 text-dark" style="font-size: 0.95rem;">
                                    <i class="fa fa-calculator text-warning mr-2 h5 mb-0"></i> Cálculo de producción aplicado (Costos + Gastos + Rentabilidad + IVA).
                                </div>

                                <div v-if="detallesCalculo && detallesCalculo.papel" class="bg-white p-3 rounded-12 shadow-2xs border border-warning">
                                    <!-- Medida Producto -->
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted small font-weight-700">Medida Producto:</span>
                                        <strong class="text-dark">{{ detallesCalculo.papel.medida_producto || (anchoPlano + 'x' + largoPlano) }}</strong>
                                    </div>

                                    <!-- Cantidad (Cliente) -->
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted small font-weight-700">Cantidad (Cliente):</span>
                                        <strong class="text-dark">{{ detallesCalculo.papel.cantidad || cantidad }} u.</strong>
                                    </div>

                                    <!-- Cabida editable -->
                                    <div class="d-flex justify-content-between py-1 border-bottom align-items-center">
                                        <span class="text-muted small font-weight-700" title="Cantidad de veces que se repite el arte en el tamaño">Cabida:</span>
                                        <div class="d-flex align-items-center">
                                            <input type="number" min="1" class="form-control form-control-sm text-center font-weight-900 text-purple border-warning" style="width: 75px; font-size: 1.1rem; height: 32px;" v-model.number="cabidaInput" placeholder="1" @input="debouncedCalcular()" @change="calcularConWebLupa()">
                                        </div>
                                    </div>

                                    <!-- Cantidad de Tamaños (Cantidad / Cabida) -->
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted small font-weight-700" title="Cantidad de piezas o tamaños a imprimir = Cantidad / Cabida">Cantidad de Tamaños:</span>
                                        <strong class="text-dark">{{ detallesCalculo.papel.tamanos_base || Math.ceil(cantidad / (cabidaInput || 1)) }}</strong>
                                    </div>

                                    <!-- Sobrante para cuadres -->
                                    <div class="d-flex justify-content-between py-1 border-bottom text-warning font-weight-700">
                                        <span class="small" title="Cantidad de tamaños sobrantes para cuadres de máquina">Sobrante (Cuadres):</span>
                                        <strong>+{{ (detallesCalculo.papel.sobrante !== undefined && detallesCalculo.papel.sobrante !== null) ? detallesCalculo.papel.sobrante : (detallesCalculo.sobrante || 50) }} tamaños</strong>
                                    </div>

                                    <!-- Tamaños Totales -->
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted small font-weight-700" title="Tamaños totales = (Cantidad / Cabida) + Sobrante">Tamaños Totales:</span>
                                        <strong class="text-dark">{{ detallesCalculo.papel.total_tamanos || detallesCalculo.hojas_totales }}</strong>
                                    </div>

                                    <!-- Tamaño (Piezas que salen del pliego) -->
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted small font-weight-700" title="Cantidad de piezas que salen del pliego">Tamaño (Piezas/Pliego):</span>
                                        <strong class="text-primary font-weight-900">{{ detallesCalculo.papel.tamano || detallesCalculo.tamano || (detallesCalculo.diagrama ? detallesCalculo.diagrama.pieces.length : 1) }} piezas</strong>
                                    </div>

                                    <!-- Pliegos Necesarios -->
                                    <div class="d-flex justify-content-between py-1 border-bottom bg-light p-2 rounded">
                                        <span class="text-purple small font-weight-900" title="Pliegos = ((Cantidad/Cabida) + Sobrante) / Tamaño">Pliegos Necesarios:</span>
                                        <strong class="text-purple font-weight-900" style="font-size: 1.15rem;">{{ detallesCalculo.papel.pliegos || detallesCalculo.pliegos_necesarios }} pliegos</strong>
                                    </div>

                                    <!-- Valor Pliego -->
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted small font-weight-700">Valor Pliego:</span>
                                        <strong class="text-dark">${{ formatear(detallesCalculo.papel.valor_unitario) }}</strong>
                                    </div>

                                    <!-- Valor Total Papel -->
                                    <div class="d-flex justify-content-between py-1 border-bottom text-success font-weight-900">
                                        <span>Valor total papel:</span>
                                        <strong>${{ formatear(detallesCalculo.papel.costo_total) }}</strong>
                                    </div>

                                    <!-- Planchas, Impresión y Tiraje -->
                                    <div v-if="detallesCalculo.impresion" class="mt-2 pt-2 border-top">
                                        <div class="d-flex justify-content-between py-1">
                                            <span class="text-muted small font-weight-700">Planchas ({{ detallesCalculo.impresion.num_tintas || 1 }} tintas):</span>
                                            <strong class="text-dark">${{ formatear(detallesCalculo.impresion.costo_planchas || 0) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between py-1">
                                            <span class="text-muted small font-weight-700">Costo Tiraje:</span>
                                            <strong class="text-dark">${{ formatear(detallesCalculo.impresion.costo_tiraje || 0) }}</strong>
                                        </div>
                                        <div class="small text-muted" style="font-size: 10.5px;">
                                            V.U. Plancha: ${{ formatear(detallesCalculo.impresion.valor_unitario_plancha || 0) }}
                                        </div>
                                    </div>

                                    <!-- Acabados y Terminados Especiales -->
                                    <div v-if="detallesCalculo.acabados && detallesCalculo.acabados.costo_total > 0" class="mt-2 p-2 border-top rounded-10" style="background: rgba(125, 60, 152, 0.05); border: 1px dashed #7d3c98;">
                                        <div class="d-flex justify-content-between mb-1 pb-1 border-bottom font-weight-800 text-brand-purple">
                                            <span><i class="fa fa-magic"></i> Acabados Especiales:</span>
                                            <strong>${{ formatear(detallesCalculo.acabados.costo_total) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>Área de impresión:</span>
                                            <span>{{ detallesCalculo.acabados.print_w }}x{{ detallesCalculo.acabados.print_h }} cm</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>Área Total Procesada:</span>
                                            <strong class="text-success">{{ (detallesCalculo.acabados.area_mt2 || 0).toFixed(2) }} m²</strong>
                                        </div>
                                        <div v-for="det in detallesCalculo.acabados.detalles" :key="det.nombre" class="mt-1 pl-2 border-left-purple small">
                                            <div class="d-flex justify-content-between">
                                                <span>{{ det.nombre }}:</span>
                                                <strong class="text-dark">${{ formatear(det.costo) }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Diagrama de Aprovechamiento Teórico -->
                                    <div v-if="detallesCalculo.diagrama && detallesCalculo.diagrama.pieces.length > 0" class="mt-3 p-2 bg-white border rounded-10 text-center shadow-2xs">
                                        <h6 class="text-brand-purple mb-2 text-left font-weight-900 text-uppercase" style="font-size: 11px;">
                                            <i class="fa fa-th"></i> APROVECHAMIENTO TEÓRICO ({{ detallesCalculo.diagrama.w }}x{{ detallesCalculo.diagrama.h }} CM):
                                        </h6>
                                        
                                        <div class="p-2 border rounded bg-light mb-2">
                                            <div v-if="detallesCalculo.diagrama.troquel_img" class="text-center">
                                                <img :src="'/storage/troqueles/' + detallesCalculo.diagrama.troquel_img" style="max-width: 100%; height: auto; border: 1px dashed #7d3c98; padding: 5px;" alt="Línea de troquel real">
                                                <div class="mt-1 text-brand-purple font-weight-bold" style="font-size: 10px;">
                                                    <i class="fa fa-check-circle"></i> Usando plantilla de troquel real
                                                </div>
                                            </div>
                                            <svg v-else :viewBox="`0 0 ${detallesCalculo.diagrama.w} ${detallesCalculo.diagrama.h}`" style="width: 100%; height: auto; display: block; max-height: 180px;">
                                                <rect x="0" y="0" :width="detallesCalculo.diagrama.w" :height="detallesCalculo.diagrama.h" fill="#f8f9fa" stroke="#ccc" stroke-width="0.5"/>
                                                <g v-for="(p, i) in detallesCalculo.diagrama.pieces" :key="i">
                                                    <rect :x="p.x" :y="p.y" :width="p.w" :height="p.h" :fill="i < detallesCalculo.cabida ? '#6ab04c' : '#dfe6e9'" fill-opacity="0.8" :stroke="i < detallesCalculo.cabida ? '#1b4d0e' : '#b2bec3'" stroke-width="0.8"/>
                                                    <text :x="p.x + p.w/2" :y="p.y + p.h/2" font-size="6" text-anchor="middle" dominant-baseline="middle" :fill="i < detallesCalculo.cabida ? '#ffffff' : '#636e72'" style="font-weight: 900;">{{ i + 1 }}</text>
                                                </g>
                                            </svg>
                                        </div>

                                        <!-- Análisis de Producción -->
                                        <div class="p-2 bg-light rounded text-left small" style="font-size: 11px; line-height: 1.5;">
                                            <strong class="text-brand-purple"><i class="fa fa-calculator mr-1"></i> Análisis de Producción (Fórmula WebLupa Pro):</strong><br>
                                            • <strong>Cantidad:</strong> {{ detallesCalculo.papel.cantidad || cantidad }} unidades pedidas por cliente.<br>
                                            • <strong>Cabida:</strong> {{ detallesCalculo.papel.cabida || 1 }} repetición(es) de arte.<br>
                                            • <strong>Cantidad de Tamaños:</strong> {{ detallesCalculo.papel.tamanos_base }} tamaños (impresiones).<br>
                                            • <strong>Sobrante (Cuadres):</strong> +{{ detallesCalculo.papel.sobrante }} tamaños sobrantes.<br>
                                            • <strong>Tamaño:</strong> {{ detallesCalculo.papel.tamano }} piezas que salen por pliego.<br>
                                            <div class="mt-1 p-2 bg-white border rounded font-weight-bold text-dark text-center shadow-2xs">
                                                Fórmula: (({{ detallesCalculo.papel.cantidad }} / {{ detallesCalculo.papel.cabida }}) + {{ detallesCalculo.papel.sobrante }}) / {{ detallesCalculo.papel.tamano }} = <span class="text-purple font-weight-900" style="font-size: 1.1rem;">{{ detallesCalculo.papel.pliegos }} Pliegos</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ajuste de Ganancia y Precio Sugerido -->
                            <div class="card border-0 shadow-lg rounded-20 bg-dark text-white p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-white-50 small font-weight-800 text-uppercase">Margen de Rentabilidad</span>
                                    <div class="btn-group btn-group-sm">
                                        <button v-for="g in [30, 40, 50, 70]" :key="g"
                                                type="button" class="btn btn-sm font-weight-800 px-2"
                                                :class="porcentajeGanancia === g ? 'btn-success text-dark' : 'btn-outline-light'"
                                                @click="porcentajeGanancia = g">
                                            {{ g }}%
                                        </button>
                                    </div>
                                </div>

                                <div class="text-center py-2">
                                    <span class="text-white-50 text-uppercase small font-weight-800">PRECIO SUGERIDO AL CLIENTE</span>
                                    <div class="display-4 font-weight-900 my-1" style="color: #a0ef6e;">$ {{ formatear(precioVentaTotal) }}</div>
                                    <span class="badge badge-light px-3 py-1 font-weight-800 text-dark">Valor Unitario: $ {{ formatear(valorUnitarioSugerido) }}</span>
                                </div>

                                <button class="btn btn-block btn-lg py-3 rounded-15 font-weight-900 shadow-lg mt-3" style="background-color: #a0ef6e; color: #111; border: none;" @click="agregarCotizacion()">
                                    <i class="fa fa-check-circle mr-2"></i> AGREGAR A LA COTIZACIÓN
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
        modal: { type: Number, default: 0 }
    },
    data() {
        return {
            calcTimer: null,
            lastCotizacionRequestId: 0,
            cantidad: 1000,
            porcentajeGanancia: 40,
            anchoPlano: 20,
            largoPlano: 30,
            nombreTrabajoCustom: '',

            cabidaInput: 1,

            resultadoWebLupa: null,
            detallesCalculo: null,
            pliegosWebLupa: {},
            impresionWebLupa: {},
            acabadosWebLupa: {},

            cajaData: {
                ancho_box: 0,
                largo_box: 0,
                alto_box: 0,
                p1: 2,
                p2: 2,
                pl: 2
            },

            tiposBD: [],
            tipoSeleccionadoId: '',
            tipoBDSeleccionado: null,
            atributosTiendaAgrupados: {},
            opcionesSeleccionadasTienda: {},
            opcionesMultiplesTienda: {},

            arrayMedidasPliego: [],
            pliegosBaseOpciones: [],
            pliegoBaseSeleccionado: ''
        }
    },
    watch: {
        modal(val) {
            if (val) {
                if (typeof $ !== 'undefined') $(document).off('focusin.bs.modal');
                this.listarTiposProducto();
                this.cargarMedidasPliego();
            }
        }
    },
    mounted() {
        if (typeof $ !== 'undefined') $(document).off('focusin.bs.modal');
        this.listarTiposProducto();
        this.cargarMedidasPliego();
    },
    computed: {
        tieneAtributosTienda() {
            return Object.keys(this.atributosTiendaAgrupados).length > 0;
        },
        costoProduccionTotal() {
            if (this.resultadoWebLupa && this.resultadoWebLupa.total) {
                return Math.round(this.resultadoWebLupa.total);
            }
            return 0;
        },
        montoGanancia() {
            return Math.round(this.costoProduccionTotal * (this.porcentajeGanancia / 100));
        },
        precioVentaTotal() {
            return this.costoProduccionTotal + this.montoGanancia;
        },
        valorUnitarioSugerido() {
            const cant = parseFloat(this.cantidad) || 1;
            return Math.round(this.precioVentaTotal / cant);
        },
        opcionesCabidaList() {
            if (this.detallesCalculo && Array.isArray(this.detallesCalculo.opciones_cabida)) {
                return this.detallesCalculo.opciones_cabida;
            }
            return [];
        }
    },
    methods: {
        async listarTiposProducto() {
            let me = this;
            if (me.tiposBD && me.tiposBD.length > 0) {
                return me.tiposBD;
            }
            try {
                const response = await axios.get('/tipoproducto');
                const data = response.data.tipoproducto || response.data;
                if (Array.isArray(data) && data.length > 0) {
                    me.tiposBD = data;
                } else if (data && typeof data === 'object') {
                    me.tiposBD = Object.values(data);
                }

                if (me.tiposBD.length > 0 && !me.tipoBDSeleccionado) {
                    await me.seleccionarTipoBD(me.tiposBD[0]);
                }
                return me.tiposBD;
            } catch (error) {
                console.error("Error cargando tipos de producto", error);
                return [];
            }
        },
        async cargarMedidasPliego() {
            let me = this;
            try {
                const response = await axios.get('/ajustes/listar?tipo=medidas_pliego');
                const data = response.data || [];
                me.arrayMedidasPliego = data;
                
                const defaults = ['70x100', '60x90'];
                const listaUnicas = [...defaults];

                data.forEach(item => {
                    if (item.categoria && item.categoria.trim() && !listaUnicas.includes(item.categoria.trim())) {
                        listaUnicas.push(item.categoria.trim());
                    }
                    if (item.valor && item.valor.trim() && !listaUnicas.includes(item.valor.trim())) {
                        listaUnicas.push(item.valor.trim());
                    }
                });

                const opciones = listaUnicas.map(val => {
                    const enBD = data.find(d => d.valor === val || d.categoria === val);
                    let label = `${val} cm`;
                    if (val === '70x100') {
                        label = 'Pliego Base 70x100 cm';
                    } else if (val === '60x90') {
                        label = 'Pliego Base 60x90 cm';
                    } else if (enBD && enBD.detalle) {
                        label = `${enBD.detalle}`;
                    }
                    return {
                        valor: val,
                        label: label
                    };
                });

                me.pliegosBaseOpciones = opciones;

                if (me.pliegosBaseOpciones.length > 0 && !me.pliegoBaseSeleccionado) {
                    me.pliegoBaseSeleccionado = me.pliegosBaseOpciones[0].valor;
                }
            } catch (err) {
                console.error("Error al cargar medidas de pliego desde Ajustes:", err);
            }
        },
        alCambiarPliegoBase() {
            this.cabidaInput = 1;
            this.calcularConWebLupa();
        },
        seleccionarOpcionCabida(opc) {
            if (!opc) return;
            this.cabidaInput = opc.cabida;
            this.calcularConWebLupa();
        },
        cerrarModal() {
            this.$emit('cerrarCotizador');
        },
        alCambiarTipoSelect(tipoId) {
            const tipo = this.tiposBD.find(t => t.id == tipoId);
            if (tipo) {
                this.seleccionarTipoBD(tipo);
            }
        },
        async seleccionarTipoBD(tipo) {
            this.tipoBDSeleccionado = tipo;
            this.tipoSeleccionadoId = tipo.id;

            if (!this.nombreTrabajoCustom || this.nombreTrabajoCustom.trim() === '') {
                this.nombreTrabajoCustom = tipo.nombre;
            }

            if (tipo.rentabilidad && parseFloat(tipo.rentabilidad) > 0) {
                this.porcentajeGanancia = parseFloat(tipo.rentabilidad);
            }

            this.cajaData.ancho_box = 0;
            this.cajaData.largo_box = 0;
            this.cajaData.alto_box = 0;

            if (tipo.formula_ancho || tipo.formula_largo) {
                this.calcularMedidas2D();
            }

            // Cargar atributos Tienda
            const attrsTienda = tipo.atributosTienda || tipo.atributos_tienda || [];
            const agrupadosVisibles = {};
            const multMap = {};

            attrsTienda.forEach(at => {
                const grupo = (at.tipo || 'General').trim();
                if (!agrupadosVisibles[grupo]) {
                    agrupadosVisibles[grupo] = [];
                    multMap[grupo] = [];
                }
                agrupadosVisibles[grupo].push(at);
            });
            this.atributosTiendaAgrupados = agrupadosVisibles;
            this.opcionesMultiplesTienda = multMap;

            const selecTiendaMap = {};
            Object.keys(agrupadosVisibles).forEach(grupo => {
                if (agrupadosVisibles[grupo] && agrupadosVisibles[grupo].length > 0) {
                    selecTiendaMap[grupo] = agrupadosVisibles[grupo][0].id;
                }
            });
            this.opcionesSeleccionadasTienda = selecTiendaMap;

            await this.calcularConWebLupa();
        },
        resetCotizador() {
            this.cantidad = 1000;
            this.anchoPlano = 20;
            this.largoPlano = 30;
            this.nombreTrabajoCustom = '';
            this.cabidaInput = 1;
            this.cajaData = { ancho_box: 0, largo_box: 0, alto_box: 0, p1: 2, p2: 2, pl: 2 };
            this.tipoBDSeleccionado = null;
            this.tipoSeleccionadoId = '';
            if (this.tiposBD && this.tiposBD.length > 0) {
                this.seleccionarTipoBD(this.tiposBD[0]);
            }
        },
        async cargarProductoExistente(producto) {
            if (!producto) return;
            await this.listarTiposProducto();

            this.nombreTrabajoCustom = producto.nombre || producto.concepto || '';
            if (parseFloat(producto.cantidad) > 0) {
                this.cantidad = parseFloat(producto.cantidad);
            }
            const tipoId = producto.tipo_producto_id || producto.tipo_id;
            if (tipoId && this.tiposBD.length > 0) {
                const tipoEncontrado = this.tiposBD.find(t => t.id == tipoId);
                if (tipoEncontrado) {
                    await this.seleccionarTipoBD(tipoEncontrado);
                }
            } else if ((producto.nombre || producto.concepto) && this.tiposBD.length > 0) {
                const nameSearch = (producto.nombre || producto.concepto).toLowerCase().trim();
                const tipoEncontrado = this.tiposBD.find(t => nameSearch.includes(t.nombre.toLowerCase().trim()) || t.nombre.toLowerCase().trim().includes(nameSearch));
                if (tipoEncontrado) {
                    await this.seleccionarTipoBD(tipoEncontrado);
                }
            }

            if (parseFloat(producto.ancho) > 0) this.anchoPlano = parseFloat(producto.ancho);
            if (parseFloat(producto.largo) > 0) this.largoPlano = parseFloat(producto.largo);

            if (!producto.ancho && (producto.descripcion || producto.concepto)) {
                const text = (producto.descripcion || '') + ' ' + (producto.concepto || '');
                const match = text.match(/Dimensiones Plano:\s*([\d.]+)\s*x\s*([\d.]+)/i) || text.match(/([\d.]+)\s*x\s*([\d.]+)\s*cm/i);
                if (match) {
                    this.anchoPlano = parseFloat(match[1]);
                    this.largoPlano = parseFloat(match[2]);
                }
            }

            await this.calcularConWebLupa();
        },
        onInputCaja3D() {
            this.calcularMedidas2D();
            this.debouncedCalcular();
        },
        debouncedCalcular() {
            if (this.calcTimer) clearTimeout(this.calcTimer);
            this.calcTimer = setTimeout(() => {
                this.calcularConWebLupa();
            }, 250);
        },
        async calcularConWebLupa() {
            const currentReqId = ++this.lastCotizacionRequestId;
            try {
                const cantVal = parseFloat(this.cantidad) || 1;
                const anchoVal = parseFloat(this.anchoPlano) || 0;
                const largoVal = parseFloat(this.largoPlano) || 0;
                
                if (anchoVal <= 0 || largoVal <= 0) {
                    return;
                }

                const especs = [];

                if (this.tieneAtributosTienda) {
                    Object.keys(this.atributosTiendaAgrupados).forEach(grupo => {
                        const items = this.atributosTiendaAgrupados[grupo];
                        if (items && items[0] && items[0].seleccion_multiple) {
                            const selMult = this.opcionesMultiplesTienda[grupo];
                            if (Array.isArray(selMult) && selMult.length > 0) {
                                especs.push({
                                    titulo: grupo,
                                    valor: selMult
                                });
                            }
                        } else {
                            const selId = this.opcionesSeleccionadasTienda[grupo];
                            if (selId && items) {
                                const item = items.find(i => i.id == selId);
                                if (item) {
                                    especs.push({
                                        titulo: grupo,
                                        valor: item.nombre
                                    });
                                }
                            }
                        }
                    });
                }

                const payload = {
                    articulo_id: (this.tipoBDSeleccionado && this.tipoBDSeleccionado.id) ? this.tipoBDSeleccionado.id : 0,
                    tipo_producto_id: (this.tipoBDSeleccionado && this.tipoBDSeleccionado.id) ? this.tipoBDSeleccionado.id : null,
                    cantidad: cantVal,
                    ancho: anchoVal,
                    largo: largoVal,
                    pliego_base: this.pliegoBaseSeleccionado || '70x100',
                    ancho_box: this.cajaData ? (parseFloat(this.cajaData.ancho_box) || 0) : 0,
                    largo_box: this.cajaData ? (parseFloat(this.cajaData.largo_box) || 0) : 0,
                    alto_box: this.cajaData ? (parseFloat(this.cajaData.alto_box) || 0) : 0,
                    cabida_troquel: (parseInt(this.cabidaInput) > 0 ? parseInt(this.cabidaInput) : 1),
                    especificaciones: especs
                };

                const response = await axios.post('/api/tienda/cotizar', payload);
                if (currentReqId !== this.lastCotizacionRequestId) {
                    return;
                }
                if (response.data) {
                    this.resultadoWebLupa = response.data;
                    if (response.data.detalles_calculo) {
                        this.detallesCalculo = response.data.detalles_calculo;
                        if (response.data.detalles_calculo.papel) this.pliegosWebLupa = response.data.detalles_calculo.papel;
                        if (response.data.detalles_calculo.impresion) this.impresionWebLupa = response.data.detalles_calculo.impresion;
                        if (response.data.detalles_calculo.acabados) this.acabadosWebLupa = response.data.detalles_calculo.acabados;
                    }
                }
            } catch (err) {
                console.error("Error al consultar el motor de cotización WebLupa:", err);
            }
        },
        calcularMedidas2D() {
            if (!this.tipoBDSeleccionado) return;
            const A = parseFloat(this.cajaData.alto_box) || 0;
            const L = parseFloat(this.cajaData.largo_box) || 0;
            const W = parseFloat(this.cajaData.ancho_box) || 0;

            // Si no se han ingresado dimensiones 3D del empaque, conservar las medidas planas manuales
            if (W <= 0 && L <= 0 && A <= 0) {
                return;
            }

            const H = A;
            const P1 = parseFloat(this.cajaData.p1) || 0;
            const P2 = parseFloat(this.cajaData.p2) || 0;
            const PL = parseFloat(this.cajaData.pl) || 0;

            const context = { A, L, W, H, P1, P2, PL };

            if (this.tipoBDSeleccionado.formula_ancho) {
                try {
                    let formula = this.tipoBDSeleccionado.formula_ancho;
                    Object.keys(context).forEach(k => {
                        formula = formula.replace(new RegExp('\\b' + k + '\\b', 'g'), context[k]);
                    });
                    const val = eval(formula);
                    if (!isNaN(val) && val > 0) {
                        this.anchoPlano = Math.max(1, Math.round(val * 10) / 10);
                    }
                } catch (e) {
                    console.log('Error calculando formula ancho:', e);
                }
            }

            if (this.tipoBDSeleccionado.formula_largo) {
                try {
                    let formula = this.tipoBDSeleccionado.formula_largo;
                    Object.keys(context).forEach(k => {
                        formula = formula.replace(new RegExp('\\b' + k + '\\b', 'g'), context[k]);
                    });
                    const val = eval(formula);
                    if (!isNaN(val) && val > 0) {
                        this.largoPlano = Math.max(1, Math.round(val * 10) / 10);
                    }
                } catch (e) {
                    console.log('Error calculando formula largo:', e);
                }
            }
            this.calcularConWebLupa();
        },
        formatear(valor) {
            return new Intl.NumberFormat('es-CO').format(Math.round(valor || 0));
        },
        agregarCotizacion() {
            const cant = parseFloat(this.cantidad) || 1;
            const atributosList = [];

            if (this.tipoBDSeleccionado) {
                atributosList.push({ nombre: 'Tipo de Producto', open: { labelOpAtributo: this.tipoBDSeleccionado.nombre } });
            }

            if (this.pliegosWebLupa && this.pliegosWebLupa.pliegos) {
                const p = this.pliegosWebLupa;
                const cantPedida = p.cantidad || cant;
                const cabidaVal = p.cabida || 1;
                const tamanoVal = p.tamano || 1;
                const sobranteVal = p.sobrante || 0;
                const pliegosVal = p.pliegos || 0;

                atributosList.push({
                    nombre: 'Cálculo de Pliegos (Motor WebLupa Pro)',
                    open: { 
                        labelOpAtributo: `${pliegosVal} pliegos | [Fórmula: ((${cantPedida} cant / ${cabidaVal} cabida) + ${sobranteVal} sobrante) / ${tamanoVal} piezas/pliego = ${pliegosVal} pliegos]` 
                    }
                });
            }

            if (this.tieneAtributosTienda) {
                Object.keys(this.atributosTiendaAgrupados).forEach(grupo => {
                    const items = this.atributosTiendaAgrupados[grupo];
                    if (items && items[0] && items[0].seleccion_multiple) {
                        const selMult = this.opcionesMultiplesTienda[grupo];
                        if (Array.isArray(selMult) && selMult.length > 0) {
                            atributosList.push({
                                nombre: grupo,
                                open: { labelOpAtributo: selMult.join(', ') }
                            });
                        }
                    } else {
                        const selId = this.opcionesSeleccionadasTienda[grupo];
                        if (selId && items) {
                            const found = items.find(i => i.id == selId);
                            if (found) {
                                atributosList.push({
                                    nombre: grupo,
                                    open: { labelOpAtributo: found.nombre }
                                });
                            }
                        }
                    }
                });
            }

            atributosList.push({ nombre: 'Dimensiones Plano', open: { labelOpAtributo: `${this.anchoPlano} x ${this.largoPlano} cm` } });

            const nombreFinal = (this.nombreTrabajoCustom && this.nombreTrabajoCustom.trim() !== '') 
                ? this.nombreTrabajoCustom 
                : `${this.tipoBDSeleccionado ? this.tipoBDSeleccionado.nombre : 'Producto'} (${this.anchoPlano}x${this.largoPlano} cm)`;

            const producto = {
                id: 0,
                tipo_producto_id: (this.tipoBDSeleccionado && this.tipoBDSeleccionado.id) ? this.tipoBDSeleccionado.id : null,
                nombre: nombreFinal,
                ancho: this.anchoPlano,
                largo: this.largoPlano,
                cantidad: cant,
                valor_unitario: this.valorUnitarioSugerido,
                iva: 0,
                descuento: 0,
                subtotal: this.precioVentaTotal,
                total: this.precioVentaTotal,
                atributos: atributosList,
                _cotizadorTipo: 'R'
            };

            this.$emit('productoCustom', producto);
            this.$emit('cerrarCotizador');
        },
        cargarProductoExistente(producto) {
            if (!producto) return;
            if (producto.nombre) this.nombreTrabajoCustom = producto.nombre;
            if (producto.concepto) this.nombreTrabajoCustom = producto.concepto;
            if (producto.cantidad) this.cantidad = parseFloat(producto.cantidad) || 1;
            if (producto.ancho) this.anchoPlano = parseFloat(producto.ancho) || 0;
            if (producto.largo) this.largoPlano = parseFloat(producto.largo) || 0;
            if (producto.tipo_producto_id) {
                this.tipoSeleccionadoId = producto.tipo_producto_id;
                this.alCambiarTipoSelect(producto.tipo_producto_id);
            }
        },
        resetCotizador() {
            this.nombreTrabajoCustom = '';
            this.tipoSeleccionadoId = '';
            this.tipoBDSeleccionado = null;
        }
    }
}
</script>

<style scoped>
.mostrar {
    display: block !important;
    opacity: 1 !important;
    position: fixed !important;
    background-color: rgba(0, 0, 0, 0.6) !important;
    z-index: 10600 !important;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow-y: auto;
}
.mostrar .modal-dialog {
    z-index: 10601 !important;
    margin-top: 2rem;
    margin-bottom: 2rem;
}

.rounded-20 { border-radius: 20px !important; }
.rounded-15 { border-radius: 15px !important; }
.rounded-12 { border-radius: 12px !important; }
.rounded-10 { border-radius: 10px !important; }
.font-weight-900 { font-weight: 900 !important; }
.font-weight-800 { font-weight: 800 !important; }
.font-weight-700 { font-weight: 700 !important; }

.calc-gold-box {
    background: #fffdf5 !important;
    border: 1px solid #ffe8a1 !important;
}

.text-brand-purple { color: #7d3c98 !important; }
.text-purple { color: #7d3c98 !important; }

.btn-purple {
    background-color: #7d3c98 !important;
    border-color: #7d3c98 !important;
    color: #fff !important;
}
.btn-outline-purple {
    border-color: #7d3c98 !important;
    color: #7d3c98 !important;
    background: transparent;
}
.btn-outline-purple:hover {
    background-color: #7d3c98 !important;
    color: #fff !important;
}

.border-left-purple {
    border-left: 4px solid #7d3c98 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.shadow-2xs {
    box-shadow: 0 2px 5px rgba(0,0,0,0.04) !important;
}
</style>
