<template>
    <div class="cotizacion-premium-container">
        <!-- Dashboard Header -->
        <div class="header-card mb-4">
            <div class="row align-items-center">
                <div class="col-sm-8">
                    <h2 class="display-title"><i class="fa fa-file-text-o mr-2" style="color: #a0ef6e;"></i> Nueva Cotización</h2>
                    <p class="text-muted small mb-0">Configura los detalles del cliente y productos para generar una propuesta profesional.</p>
                </div>
                <div class="col-sm-4 text-right">
                    <div class="btn-group-premium">
                        <button type="button" @click="cancelar()" class="btn btn-outline-secondary px-4 rounded-10 mr-2">
                             Cancelar
                        </button>
                        <button type="button" @click="registrar()" class="btn btn-dark px-4 rounded-10 shadow-sm" style="border: 2px solid #a0ef6e;">
                            <i class="fa fa-save mr-2" style="color: #a0ef6e;"></i> Guardar
                        </button>
                        <button v-if="edit==1" type="button" @click="imprimir()" class="btn btn-warning px-4 rounded-10 ml-2 shadow-sm">
                            <i class="fa fa-print mr-2"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-3">
                <div class="premium-input-group shadow-sm">
                    <label class="premium-label">Fecha de Emisión</label>
                    <input type="date" class="form-control premium-input" v-model="fecha">
                </div>
            </div>
        </div>

        <!-- Seccion Cliente -->
        <div class="card border-0 shadow-sm rounded-20 mb-4 overflow-hidden">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3 px-4">
                <h4 class="mb-0 font-weight-800" style="font-size: 1.1rem;"><i class="fa fa-user mr-2 text-primary-custom" style="color: #a0ef6e;"></i> Datos del Cliente</h4>
                <button class="btn btn-link text-primary-custom p-0 font-weight-700" style="color: #a0ef6e;" @click="nuevoCliente()">
                    <i class="fa fa-user-plus mr-1"></i> Crear nuevo cliente
                </button>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row align-items-end mb-4" v-if="newCliente != 1">
                    <div class="col-md-6">
                        <div class="input-group search-premium shadow-sm border rounded-15 overflow-hidden">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0 px-3"><i class="fa fa-search text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control border-0 px-2" v-model="buscar_cliente" @keyup="selectCliente()" placeholder="Escribe el nombre del cliente para buscar..." style="height: 50px;">
                        </div>
                    </div>
                </div>

                <!-- Formulario Nuevo Cliente / Prospecto -->
                <div v-if="newCliente==1" class="form-nuevo-cliente fade-in p-4 rounded-15 bg-light border shadow-sm mb-3">
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="font-weight-900 mb-0 text-dark">
                            <i class="fa fa-user-plus mr-2 text-primary-custom" style="color: #a0ef6e;"></i> Registrar Nuevo Prospecto / Cliente
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-danger font-weight-700 mr-2 rounded-10 px-3" @click="cancelarNuevoCliente()">Cancelar</button>
                            <button class="btn btn-sm btn-success font-weight-900 rounded-10 px-3 shadow-sm" @click="registrarCliente()">
                                <i class="fa fa-save mr-1"></i> Guardar Prospecto
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="premium-label">Razón Social / Nombre del Prospecto <span class="text-danger">*</span></label>
                            <input class="form-control premium-input-sm" type="text" v-model="empresa" placeholder="Nombre de la empresa o cliente">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="premium-label">NIT / CC / Documento</label>
                            <input class="form-control premium-input-sm" type="text" v-model="nit_emp" placeholder="900.000.000-1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="premium-label">Teléfono Fijo / Empresa</label>
                            <input class="form-control premium-input-sm" type="text" v-model="telefono_emp" placeholder="601 234 5678">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="premium-label">Nombre del Contacto Principal <span class="text-danger">*</span></label>
                            <input class="form-control premium-input-sm" type="text" v-model="nombre_cont" placeholder="Nombre de la persona de contacto">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="premium-label">Cargo del Contacto</label>
                            <input class="form-control premium-input-sm" type="text" v-model="cargo_cont" placeholder="Ej: Gerente, Compras, Diseño">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="premium-label">Celular / WhatsApp <span class="text-danger">*</span></label>
                            <input class="form-control premium-input-sm" type="text" v-model="telefono_cont" placeholder="+57 300 123 4567">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="premium-label">Correo Electrónico</label>
                            <input class="form-control premium-input-sm" type="email" v-model="correo_cont" placeholder="contacto@empresa.com">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="premium-label">Ciudad / Sede</label>
                            <input class="form-control premium-input-sm" type="text" v-model="ciudad_emp" placeholder="Bogotá, Medellín, Cali...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="premium-label">Dirección Fiscal / Entrega</label>
                            <input class="form-control premium-input-sm" type="text" v-model="direccion_emp" placeholder="Calle 123 #45-67">
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="premium-label">Redes Sociales / Sitio Web / Notas</label>
                            <input class="form-control premium-input-sm" type="text" v-model="redes_sociales" placeholder="@instagram, www.sitio.com o notas del prospecto">
                        </div>
                    </div>
                </div>

                <!-- Visualización Cliente Seleccionado -->
                <div v-else-if="clienteSelect.id" class="row selected-client-card p-3 rounded-15 border mx-1">
                    <div class="col-sm-3 border-right">
                        <label class="premium-label text-muted mb-1">CLIENTE</label>
                        <div class="font-weight-900 text-dark">{{clienteSelect.nombre || clienteSelect.razonsocial}}</div>
                    </div>
                    <div class="col-sm-3 border-right">
                        <label class="premium-label text-muted mb-1">NIT / CC</label>
                        <div class="font-weight-800 text-dark">{{clienteSelect.num_documento || '---'}}</div>
                    </div>
                    <div class="col-sm-3 border-right">
                        <label class="premium-label text-muted mb-1">TELÉFONO</label>
                        <div class="font-weight-800 text-dark">{{clienteSelect.telefono || '---'}}</div>
                    </div>
                    <div class="col-sm-3">
                        <label class="premium-label text-muted mb-1">DIRECCIÓN</label>
                        <div class="font-weight-800 text-dark small">{{clienteSelect.direccion || '---'}}</div>
                    </div>
                </div>
                <div v-else class="text-center py-3 text-muted border border-dashed rounded-15">
                    <i class="fa fa-info-circle mr-2"></i> Seleccione un cliente para continuar con la cotización.
                </div>
            </div>
        </div>

        <!-- Seccion Productos -->
        <div class="card border-0 shadow-sm rounded-20 mb-4 overflow-hidden">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3 px-4">
                <h4 class="mb-0 font-weight-800" style="font-size: 1.1rem;"><i class="fa fa-th-list mr-2 text-primary-custom" style="color: #a0ef6e;"></i> Detalle de Productos</h4>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row mb-4 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group search-premium shadow-sm border rounded-15 overflow-hidden">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0 px-3"><i class="fa fa-cube text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control border-0 px-2" v-model="buscar_producto" @keyup="selectArticulo('nuevo')" placeholder="Buscar producto en catalogo..." style="height: 50px;">
                        </div>
                    </div>
                    <div class="col-md-5 text-right">
                        <button class="btn btn-dark font-weight-900 px-3 shadow-sm mr-2 mb-2 mb-md-0" @click="modalImpresion=1" style="height: 50px; border-radius: 15px; border: 2px solid #a0ef6e;">
                            <i class="fa fa-calculator mr-2" style="color: #a0ef6e;"></i> ⚡ MOTOR WEBLUPA PRO
                        </button>
                        <button class="btn btn-primary font-weight-900 px-3 shadow-sm mr-2 mb-2 mb-md-0" @click="abrirModalManual()" style="height: 50px; border-radius: 15px;">
                            <i class="fa fa-plus-circle mr-2"></i> TRABAJO MANUAL
                        </button>
                        <button class="btn btn-dark font-weight-900 px-3 shadow-sm" @click="modalImpresion=1" style="height: 50px; border-radius: 15px; border: 1px solid #64748b;">
                            <i class="fa fa-cube mr-2"></i> CAJAS (MOTOR WEBLUPA)
                        </button>
                    </div>
                </div>

                <div class="table-responsive" v-if="pedido.productos.length > 0">
                    <table class="table table-hover premium-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="90">CANT.</th>
                                <th>DESCRIPCIÓN DEL PRODUCTO / TRABAJO</th>
                                <th>ESPECIFICACIONES TÉCNICAS</th>
                                <th width="130">VALOR UNIT.</th>
                                <th width="100">IVA (%)</th>
                                <th width="140">TOTAL LÍNEA</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(producto,index) in pedido.productos" :key="index" class="product-row">
                                <td class="align-middle text-center">
                                    <input type="number" class="form-control form-control-sm text-center font-weight-900" v-model.number="producto.cantidad" @input="recalcularLinea(producto)" min="1">
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="font-weight-900 text-dark">{{producto.nombre}}</div>
                                            <span v-if="producto.id == 0" class="badge badge-success-soft px-2 py-1 small">Trabajo Personalizado</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-purple font-weight-bold ml-2 px-2" @click="recotizarConMotor(index)" title="Recotizar / Rellenar este ítem con Motor WebLupa Pro">
                                            <i class="fa fa-calculator mr-1"></i> Motor Pro
                                        </button>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <ul class="list-unstyled mb-0 small">
                                        <template v-for="(atributo,i) in producto.atributos">
                                            <li v-if="getAtributoTexto(atributo)" :key="i" class="mb-1 text-muted">
                                                <b class="text-dark">{{atributo.nombre || atributo.titulo}}:</b> {{ getAtributoTexto(atributo) }}
                                            </li>
                                        </template>
                                    </ul>
                                </td>
                                <td class="align-middle">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control text-right font-weight-700" v-model.number="producto.valor_unitario" @input="recalcularLinea(producto)">
                                    </div>
                                </td>
                                <td class="align-middle text-center">
                                    <select class="form-control form-control-sm font-weight-700" v-model.number="producto.iva">
                                        <option :value="0">0%</option>
                                        <option :value="19">19%</option>
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control text-right font-weight-900 text-success" v-model.number="producto.total">
                                    </div>
                                </td>
                                <td class="align-middle text-right" style="white-space: nowrap;">
                                    <button type="button" class="btn btn-outline-primary btn-sm border-0 p-1 mr-1" @click="recotizarConMotor(index)" title="Editar / Recotizar renglón con Motor Pro">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm border-0 p-1 mr-1" @click="duplicarProducto(index)" title="Duplicar renglón">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm border-0 p-1 mr-1" @click="moverProductoArriba(index)" :disabled="index === 0" title="Subir renglón">
                                        <i class="fa fa-arrow-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm border-0 p-1 mr-1" @click="moverProductoAbajo(index)" :disabled="index === pedido.productos.length - 1" title="Bajar renglón">
                                        <i class="fa fa-arrow-down"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm border-0 p-1" @click="eliminarProducto(index)" title="Eliminar ítem">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-5 bg-f9 rounded-20 border border-dashed">
                    <img src="https://cdn-icons-png.flaticon.com/512/10531/10531128.png" width="80" class="mb-3 grayscaleOpacity">
                    <h5 class="text-muted font-weight-700">Tu cotización está vacía</h5>
                    <p class="text-secondary small">Busca un producto o usa la calculadora de cajas para empezar.</p>
                </div>
            </div>
        </div>

        <!-- Resumen de Totales y Observaciones -->
        <div class="row">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-20 p-4 h-100">
                    <h5 class="font-weight-900 mb-3"><i class="fa fa-comment mr-2" style="color: #a0ef6e;"></i> Observaciones Adicionales</h5>
                    <textarea class="form-control premium-textarea border-0 bg-light p-3" rows="6" v-model="pedido.observaciones" placeholder="Escribe aquí aclaraciones sobre tiempos de entrega, formas de pago o especificaciones técnicas que el cliente debe conocer..."></textarea>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card border-0 shadow-lg rounded-20 bg-dark text-white p-4 h-100">
                    <h5 class="font-weight-900 mb-4 border-bottom border-secondary pb-3 text-uppercase letter-spacing-1 d-flex justify-content-between align-items-center">
                        <span>Resumen de Inversión</span>
                    </h5>
                    
                    <div class="custom-control custom-checkbox mb-3 p-2 rounded border" :class="pedido.mostrar_totales ? 'bg-secondary border-secondary' : 'bg-warning text-dark border-warning'">
                        <input type="checkbox" class="custom-control-input" id="checkMostrarTotalesWeb" v-model="pedido.mostrar_totales">
                        <label class="custom-control-label font-weight-bold small cursor-pointer" :class="pedido.mostrar_totales ? 'text-white' : 'text-dark'" for="checkMostrarTotalesWeb">
                            <i class="fa mr-1" :class="pedido.mostrar_totales ? 'fa-eye text-success' : 'fa-eye-slash text-dark'"></i>
                            {{ pedido.mostrar_totales ? 'Mostrar Cuadro de Totales en el PDF' : 'Ocultar Cuadro de Totales en el PDF' }}
                        </label>
                    </div>

                    <div v-if="!pedido.mostrar_totales" class="alert alert-warning py-2 px-3 mb-3 small text-dark font-weight-bold text-center">
                        <i class="fa fa-eye-slash mr-1"></i> El PDF se imprimirá SIN cuadro de totales
                    </div>

                    <div :style="!pedido.mostrar_totales ? 'text-decoration: line-through; opacity: 0.45;' : ''">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-white-50">Subtotal Neto:</span>
                            <span class="h5 mb-0 font-weight-700 text-white">$ {{formatear(subTotal)}}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-white-50">IVA Acumulado:</span>
                            <span class="h5 mb-0 font-weight-700 text-white">$ {{formatear(impuesto)}}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 mt-2 pt-3 border-top border-secondary">
                            <span class="h4 mb-0 font-weight-900">VALOR TOTAL:</span>
                            <span class="h3 mb-0 font-weight-900 text-primary-custom" style="color: #a0ef6e;">$ {{formatear(total)}}</span>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <button class="btn btn-primary-custom btn-block btn-lg py-3 rounded-15 font-weight-900 shadow-lg" @click="registrar()" style="background-color: #a0ef6e; color: #111; border: none;">
                            <i class="fa fa-check-circle mr-2"></i> GENERAR COTIZACIÓN AHORA
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modales -->
        <cotizacionproducto :modal="modalp" :producto="productoSelect" @productoCustom="productoCustom" @cerrarModal="modalp=0"></cotizacionproducto>
        <calculadora-cajas :modal="modalCajas" @productoCustom="productoCustom" @cerrarCalculadora="modalCajas=0"></calculadora-cajas>
        <cotizador-r ref="cotizadorWebLupa" :modal="modalImpresion" @productoCustom="productoCustom" @cerrarCotizador="modalImpresion=0"></cotizador-r>
        <lproducto :modal="modala" :productos="this.arrayProductos" @productoSeleccionado="productoSeleccionado" @cerrarModal="modala=0"></lproducto>
        <lclientes :modal="modalc" :clientes="this.arrayClientes" @clienteSeleccionado="clienteSeleccionado" @cerrarModal="modalc=0"></lclientes>

        <!-- Modal Trabajo Manual / Personalizado -->
        <div class="modal fade" :class="{'mostrar' : modalManual}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg rounded-20 overflow-hidden">
                    <div class="modal-header bg-dark text-white py-3 px-4">
                        <h5 class="modal-title font-weight-900 mb-0">
                            <i class="fa fa-edit mr-2" style="color: #a0ef6e;"></i> AGREGAR TRABAJO MANUAL / PERSONALIZADO
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalManual()">&times;</button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="premium-label">Nombre / Descripción del Trabajo</label>
                                <input type="text" class="form-control premium-input" v-model="manualItem.nombre" placeholder="Ej: Volantes 1/2 Carta, Plegables 3 Cuerpos, Cajas Impresas...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="premium-label">Cantidad</label>
                                <input type="number" class="form-control premium-input" v-model.number="manualItem.cantidad" min="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="premium-label">Valor Unitario ($)</label>
                                <input type="number" class="form-control premium-input" v-model.number="manualItem.valor_unitario" min="0">
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-center pt-4">
                                <div class="form-check form-switch ml-2">
                                    <input class="form-check-input" type="checkbox" id="checkIvaManual" v-model="manualItem.con_iva">
                                    <label class="form-check-label font-weight-700 ml-2" for="checkIvaManual">Aplicar IVA (19%)</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-900 text-dark mb-0"><i class="fa fa-list-alt mr-2 text-primary" style="color: #a0ef6e;"></i> Especificaciones del Trabajo (Línea por Línea)</h6>
                            <button type="button" class="btn btn-sm btn-outline-success font-weight-800 rounded-10" @click="agregarEspecificacionManual()">
                                <i class="fa fa-plus mr-1"></i> Agregar otra especificación
                            </button>
                        </div>
                        
                        <div class="specs-container bg-light p-3 rounded-15 mb-3 border">
                            <div v-for="(spec, index) in manualItem.especificaciones" :key="index" class="row align-items-center mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control form-control-sm font-weight-700" v-model="spec.nombre" placeholder="Especificación (ej. Papel, Tintas...)">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm" v-model="spec.valor" placeholder="Detalle (ej. Propalcote 150g, 4x4...)">
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="eliminarEspecificacionManual(index)" title="Eliminar renglón">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="premium-label">Observaciones Adicionales del Ítem</label>
                            <input type="text" class="form-control premium-input-sm" v-model="manualItem.observaciones" placeholder="Aclaraciones particulares para este producto...">
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary font-weight-700 px-4 rounded-10" @click="cerrarModalManual()">Cancelar</button>
                        <button type="button" class="btn btn-dark font-weight-900 px-4 rounded-10 shadow-sm" @click="agregarTrabajoManual()" style="border: 2px solid #a0ef6e;">
                            <i class="fa fa-plus-circle mr-2" style="color: #a0ef6e;"></i> AGREGAR A COTIZACIÓN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
 import lclientes from './ListaClientes'
 import lproducto from './ListaArticulo'
 import cotizacionproducto from './cotizacionProducto'
 import calculadoraCajas from './CalculadoraCajas'
 import CotizadorR from './CotizadorR'
 
 export default {
    props:{
        dato:{type: Number, default: 0},
        edit:{type: Number, default: 0},
        cotizacion_data:{type: Object, default: null}
    },
    data(){
        return{
            newCliente:0,
            fecha: new Date().toISOString().slice(0, 10),
            arrayClientes:[],
            arrayProductos:[],
            pedido:{'productos':[], 'subtotal':0, 'descuento':0, 'impuesto':0, 'total':0, 'tipo': 'cotizacion', 'observaciones': '', 'mostrar_totales': true},
            buscar_cliente:'',
            buscar_producto:'',
            modalc:0,
            modala:0,
            modalp:0,
            clienteSelect:{'nombre':''},
            productoSelect:{},
            modalCajas:0,
            modalImpresion:0,
            modalManual:0,
            itemIndexEditando: null,
            manualItem: {
                nombre: '',
                cantidad: 1000,
                valor_unitario: 0,
                con_iva: false,
                especificaciones: [
                    { nombre: 'Papel / Material', valor: '' },
                    { nombre: 'Tintas / Impresión', valor: '' },
                    { nombre: 'Medidas', valor: '' },
                    { nombre: 'Acabados', valor: '' }
                ],
                observaciones: ''
            },
            // Campos para nuevo prospecto (Cuenta + Contacto)
            empresa:'',
            nit_emp:'',
            telefono_emp:'',
            ciudad_emp:'',
            direccion_emp:'',
            nombre_cont:'',
            cargo_cont:'',
            correo_cont:'',
            telefono_cont:'',
            redes_sociales:''
        }
    },
    watch: {
        cotizacion_data: {
          handler(val) {
            if (val) {
              this.clienteSelect = val.cliente || { nombre: '' };
              this.fecha = val.fecha;
              this.pedido = {
                ...val,
                mostrar_totales: !(val.observaciones && val.observaciones.includes('[NO_TOTALIZAR]')),
                productos: val.lineas ? val.lineas.map(l => ({
                   id: l.articulo_id,
                   nombre: (l.orden && l.orden.detalles_diseno) ? l.orden.detalles_diseno : (l.articulo ? l.articulo.nombre : 'Producto'),
                   cantidad: l.cantidad,
                   valor_unitario: l.valor_unitario || 0,
                   subtotal: l.subtotal || l.valor_total,
                   total: l.valor_total,
                   iva: l.impuesto || 0,
                   atributos: (l.orden && l.orden.detalles && l.orden.detalles.length > 0) ? l.orden.detalles : (l.atributos || l.detalles || []),
                   descuento: 0
                })) : []
              };
            }
          },
          immediate: true
        }
    },
    components: {
        lclientes,
        lproducto,
        cotizacionproducto,
        calculadoraCajas,
        CotizadorR
    },
    computed:{
        impuesto(){
            var valor=0;
            this.pedido.productos.forEach(e =>{
                const cant = parseFloat(e.cantidad) || 0;
                const vUnit = parseFloat(e.valor_unitario) || 0;
                const ivaPct = parseFloat(e.iva) || 0;
                valor += (cant * vUnit * (ivaPct / 100));
            });
            var resultado=Math.round(valor);
            this.pedido.impuesto=resultado;
            return resultado;
        },
        subTotal(){
            var valor=0;
            this.pedido.productos.forEach(e =>{
                const cant = parseFloat(e.cantidad) || 0;
                const vUnit = parseFloat(e.valor_unitario) || 0;
                valor += (cant * vUnit);
            });
            var resultado=Math.round(valor);
            this.pedido.subtotal=resultado;
            return resultado;
        },
        total(){
            var valor=parseFloat(this.pedido.impuesto)+parseFloat(this.pedido.subtotal);
            this.pedido.total=Math.round(valor);
            return Math.round(valor);
        },
    },
    methods:{
        cancelar(){
            this.$emit('ocultarDetalle', 1)
        },
        cancelarNuevoCliente(){
            this.newCliente=0
        },
        nuevoCliente(){
            this.newCliente=1
        },
        selectCliente(){
            let me=this;
            var url= '/cliente/selectClientes?filtro='+this.buscar_cliente;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.arrayClientes=respuesta.clientes;
                me.modalc=1
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        selectArticulo(){
            let me=this;
            var url= '/articulo/selectArticulo?filtro='+this.buscar_producto;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.arrayProductos=respuesta.articulos;
                me.modala=1
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        clienteSeleccionado(value){
            this.clienteSelect=value
            this.modalc=0
            this.newCliente=0
        },
        recotizarConMotor(index) {
            this.itemIndexEditando = index;
            this.modalImpresion = 1;
        },
        productoCustom(value){
            if (this.itemIndexEditando !== null && this.pedido.productos[this.itemIndexEditando]) {
                this.$set(this.pedido.productos, this.itemIndexEditando, value);
                this.itemIndexEditando = null;
            } else {
                this.pedido.productos.push(value);
            }
            this.modalp = 0;
            this.modalCajas = 0;
            this.modalImpresion = 0;
        },
        productoSeleccionado(value){
            this.productoSelect = value;
            this.modala = 0;
            this.modalImpresion = 1;
            this.$nextTick(() => {
                if (this.$refs.cotizadorWebLupa) {
                    this.$refs.cotizadorWebLupa.cargarProductoExistente(value);
                }
            });
        },
        cerrarCalculadora() {
            this.modalCajas = 0;
        },
        moverProductoArriba(index) {
            if (index > 0) {
                const item = this.pedido.productos.splice(index, 1)[0];
                this.pedido.productos.splice(index - 1, 0, item);
            }
        },
        moverProductoAbajo(index) {
            if (index < this.pedido.productos.length - 1) {
                const item = this.pedido.productos.splice(index, 1)[0];
                this.pedido.productos.splice(index + 1, 0, item);
            }
        },
        duplicarProducto(index) {
            if (!this.pedido.productos || !this.pedido.productos[index]) return;
            const itemOriginal = this.pedido.productos[index];
            const itemCopia = JSON.parse(JSON.stringify(itemOriginal));
            this.pedido.productos.splice(index + 1, 0, itemCopia);
        },
        eliminarProducto(index) {
            this.pedido.productos.splice(index, 1);
        },
        formatear(valor){
            return new Intl.NumberFormat('es-CO').format(valor || 0);
        },
        registrar(){
            let me = this;
            if(!this.clienteSelect.id) {
                Swal.fire('Atención', 'Por favor selecciona un cliente antes de continuar.', 'info');
                return;
            }
            if(this.pedido.productos.length == 0) {
                Swal.fire('Atención', 'Debes agregar al menos un producto a la cotización.', 'info');
                return;
            }

            this.pedido.tipo = 'cotizacion';
            let obsLimpia = (this.pedido.observaciones || '').replace(/\s*\[NO_TOTALIZAR\]/gi, '').trim();
            let obsParaGuardar = obsLimpia;
            if (!this.pedido.mostrar_totales) {
                obsParaGuardar = (obsLimpia + ' [NO_TOTALIZAR]').trim();
            }

            const payloadPedido = {
                ...this.pedido,
                observaciones: obsParaGuardar
            };

            const datos = new FormData();
            datos.set('cliente', JSON.stringify(this.clienteSelect));
            datos.set('pedido', JSON.stringify(payloadPedido));
            
            axios.post('/comprobante/registrar', datos)
            .then(function (response) {
                const pdfUrl = response.data.pdf_url || ('/imprimirPedido?id=' + (response.data.comprobante ? response.data.comprobante.id : ''));
                
                Swal.fire({
                    title: '¡Cotización Guardada!',
                    html: 'La cotización ha sido registrada con éxito.<br><br>' +
                          '<a href="' + pdfUrl + '" target="_blank" class="btn btn-success btn-lg" style="text-decoration:none; padding: 10px 30px; font-weight:bold;">' +
                          '📄 Abrir PDF para Imprimir</a>',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonColor: '#a0ef6e',
                    confirmButtonText: 'Cerrar'
                });

                me.clienteSelect = { nombre: '' };
                me.pedido = { productos: [], total:0, impuesto:0, subtotal:0, tipo: 'cotizacion', observaciones: '', mostrar_totales: true };
                me.$emit('ocultarDetalle', 1);
            }).catch(function (error) {
                console.log(error);
                var msg = 'Hubo un problema al guardar la cotización.';
                if (error.response && error.response.data && error.response.data.message) {
                    msg += '\n\nDetalle: ' + error.response.data.message;
                }
                Swal.fire('Error', msg, 'error');
            });
        },
        abrirModalManual() {
            this.manualItem = {
                nombre: '',
                cantidad: 1000,
                valor_unitario: 0,
                con_iva: false,
                especificaciones: [
                    { nombre: 'Papel / Material', valor: '' },
                    { nombre: 'Tintas / Impresión', valor: '' },
                    { nombre: 'Medidas', valor: '' },
                    { nombre: 'Acabados', valor: '' }
                ],
                observaciones: ''
            };
            this.modalManual = 1;
        },
        cerrarModalManual() {
            this.modalManual = 0;
        },
        agregarEspecificacionManual() {
            this.manualItem.especificaciones.push({ nombre: '', valor: '' });
        },
        eliminarEspecificacionManual(index) {
            this.manualItem.especificaciones.splice(index, 1);
        },
        agregarTrabajoManual() {
            if (!this.manualItem.nombre) {
                Swal.fire('Atención', 'Por favor ingresa un nombre para el trabajo.', 'warning');
                return;
            }
            const cant = parseFloat(this.manualItem.cantidad) || 1;
            const vUnit = parseFloat(this.manualItem.valor_unitario) || 0;
            const subtotal = Math.round(cant * vUnit);

            const atributosList = this.manualItem.especificaciones
                .filter(e => e.valor && String(e.valor).trim() !== '')
                .map(e => ({
                    nombre: e.nombre || 'Especificación',
                    open: { labelOpAtributo: e.valor }
                }));

            if (this.manualItem.observaciones) {
                atributosList.push({
                    nombre: 'Observaciones',
                    open: { labelOpAtributo: this.manualItem.observaciones }
                });
            }

            const producto = {
                id: 0,
                nombre: this.manualItem.nombre,
                cantidad: cant,
                valor_unitario: vUnit,
                iva: this.manualItem.con_iva ? 19 : 0,
                descuento: 0,
                subtotal: subtotal,
                total: Math.round(subtotal * (this.manualItem.con_iva ? 1.19 : 1)),
                atributos: atributosList
            };
            this.pedido.productos.push(producto);
            this.modalManual = 0;
        },
        recalcularLinea(producto) {
            const cant = parseFloat(producto.cantidad) || 0;
            const vUnit = parseFloat(producto.valor_unitario) || 0;
            const ivaPct = parseFloat(producto.iva) || 0;
            const bruto = cant * vUnit;
            const totalLinea = bruto * (1 + (ivaPct / 100));
            producto.subtotal = Math.round(bruto);
            producto.total = Math.round(totalLinea);
        },
        getAtributoTexto(atributo) {
            if (!atributo) return '';
            if (atributo.valor && typeof atributo.valor === 'string' && !atributo.open) {
                return atributo.valor;
            }
            if (Array.isArray(atributo.open)) {
                return atributo.open.map(op => (typeof op === 'object' && op !== null ? (op.labelOpAtributo || op.label || op.nombre || op.valor || '') : String(op))).filter(Boolean).join(', ');
            }
            if (typeof atributo.open === 'object' && atributo.open !== null) {
                return atributo.open.labelOpAtributo || atributo.open.label || atributo.open.nombre || atributo.open.valor || '';
            }
            if (atributo.open !== undefined && atributo.open !== null && atributo.open !== false) {
                return String(atributo.open);
            }
            if (atributo.labelOpAtributo) {
                return String(atributo.labelOpAtributo);
            }
            if (atributo.valor && typeof atributo.valor !== 'object') {
                return String(atributo.valor);
            }
            return '';
        },
        registrarCliente() {
            let me = this;
            if(!this.empresa || (!this.nombre_cont && !this.telefono_cont)) {
                Swal.fire('Atención', 'Razón Social y Teléfono o Contacto son obligatorios.', 'warning');
                return;
            }

            let data = {
                id: 0,
                razonsocial: this.empresa,
                nit: this.nit_emp,
                tipo_documento: this.nit_emp ? 'NIT' : 'CC',
                num_documento: this.nit_emp,
                telefono: this.telefono_emp || this.telefono_cont,
                direccionf: this.direccion_emp,
                ciudad: this.ciudad_emp,
                pais: 'Colombia',
                redes_sociales: this.redes_sociales,
                email: this.correo_cont,
                es_prospecto: 1,
                contactos: JSON.stringify([{
                    nombre: this.nombre_cont,
                    cargo: this.cargo_cont,
                    correo: this.correo_cont,
                    telefono: this.telefono_cont
                }]),
                empresas: JSON.stringify([]),
                envios: JSON.stringify([])
            };

            axios.post('/cliente/crearCuenta', data).then(function (response) {
                Swal.fire('¡Éxito!', 'Prospecto creado y seleccionado correctamente.', 'success');
                me.newCliente = 0;
                if (response.data && response.data.id) {
                    me.clienteSelect = response.data;
                } else {
                    me.buscar_cliente = me.empresa;
                    me.selectCliente();
                }
            }).catch(function (error) {
                console.log(error);
                Swal.fire('Error', 'Hubo un problema al crear el prospecto.', 'error');
            });
        }
    }
}
</script>

<style scoped>
    .cotizacion-premium-container { padding: 5px; font-family: 'Inter', 'Segoe UI', sans-serif; }
    .rounded-20 { border-radius: 20px !important; }
    .rounded-15 { border-radius: 15px !important; }
    .rounded-10 { border-radius: 10px !important; }
    .font-weight-900 { font-weight: 900 !important; }
    .font-weight-800 { font-weight: 800 !important; }
    .bg-f9 { background-color: #f8fafc !important; }
    .text-primary-custom { color: #a0ef6e !important; }
    .letter-spacing-1 { letter-spacing: 1px !important; }

    .header-card {
        background: #fff;
        padding: 25px 30px;
        border-radius: 20px;
        border: 1px solid #eef2f6;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    .display-title {
        font-weight: 950;
        font-size: 2.2rem;
        letter-spacing: -1.5px;
        color: #1a202c;
        margin-bottom: 5px;
    }

    .premium-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
        display: block;
    }

    .premium-input {
        background-color: #fff;
        border: 2px solid #eef2f6;
        border-radius: 12px;
        height: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .premium-input:focus {
        border-color: #a0ef6e;
        box-shadow: 0 0 0 4px rgba(160, 239, 110, 0.15);
    }

    .premium-input-sm {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        height: 40px;
    }

    .search-premium input::placeholder { color: #cbd5e1; }
    
    .selected-client-card {
        background-color: #fdfdfd;
        border-color: #e2e8f0;
        border-left: 4px solid #a0ef6e !important;
    }

    .product-row:hover {
        background-color: #f8fafc;
    }

    .premium-table thead th {
        border: none;
        padding: 15px;
        font-size: 0.75rem;
        font-weight: 850;
        color: #64748b;
        letter-spacing: 0.5px;
    }

    .premium-table td {
        border-top: 1px solid #f1f5f9;
        padding: 15px;
    }

    .badge-success-soft {
        background-color: #ecfdf5;
        color: #059669;
        font-weight: 700;
    }

    .premium-textarea {
        border-radius: 15px;
        resize: none;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .premium-textarea:focus {
        background-color: #fff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .grayscaleOpacity {
        filter: grayscale(1);
        opacity: 0.3;
    }

    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

