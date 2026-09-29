<template >
<div class="orden" >
  
  <div v-if="orden.status.estado==status" class="estadoPago rounded"
  :class="[
    orden.produccion=='EP'? 'enviarp' : [orden.produccion=='ENP' ? 'enproduccion' : [orden.produccion=='D'?'diseno': [orden.produccion=='EM'? 'empacado':'entregar']]],
    (orden.terminado && orden.terminado.length > 1 && (status === 'Terminado' || status === 6)) ? 'shared-card' : ''
  ]"  >
 
  <div class="card-body" @click="abrirmodal" style="position: relative; overflow: visible;">
        
          <ul>
            <li :style="{ background: getEntregaColor(orden.fecha_entrega) }" class="d-flex align-items-center justify-content-between">
              <span class="font-weight-bold d-flex align-items-center flex-wrap">
                No. {{orden.id}}
                <span v-if="orden.pedido" class="badge badge-warning text-dark ml-1 font-weight-bold" style="font-size: 9px; padding: 2px 4px; border-radius: 3px; line-height: 1.1;" :title="'Pedido #' + (orden.pedido.num_comprobante || orden.pedido.id)">
                  Ped. #{{ orden.pedido.num_comprobante || orden.pedido.id }}
                </span>
                <span v-if="orden.terminado && orden.terminado.length > 1 && (status === 'Terminado' || status === 6)" 
                      class="badge badge-warning text-dark ml-1 font-weight-bold" 
                      style="font-size: 8px; padding: 2px 4px; border-radius: 3px; line-height: 1.1;" 
                      title="Dividido entre varias operarias">
                  <i class="fa fa-users"></i> Dividido
                </span>
              </span>
              
              <div class="d-flex align-items-center">
                <div v-if="orden.pedido && orden.pedido.transportadora!='' && orden.pedido.transportadora!='e'" 
                     class="btn btn-sm-mobile mr-1" 
                     style="background:#fff;color:#00458d; border:2px solid #00458d;">{{ orden.pedido.transportadora }}</div>
                <div v-else-if="orden.pedido && orden.pedido.transportadora=='e'" 
                     class="btn btn-sm-mobile mr-1" 
                     style="background:#fff;color:#9300c3; border:2px solid #9300c3;">E.Cliente</div>
                <div v-else 
                     class="btn btn-sm-mobile mr-1" 
                     style="background:#fff;color:#168d00; border:2px solid #168d00;">{{ orden.pedido && orden.pedido.transportadora == '' ? 'Cali' : 'Cali' }}</div>

                <div v-if="orden.prioridad=='urgente'" class="badge badge-danger badge-p-mobile">P</div>
                <div v-else-if="orden.prioridad=='media'" class="badge badge-warning badge-p-mobile">P</div>
                <div v-else-if="String(orden.prioridad).toLowerCase()!='vip'" class="badge badge-primary badge-p-mobile">P</div>
              </div>
            </li>
            <li class="rasonsocial">{{orden.cliente ? orden.cliente.razonsocial : (orden.rasonsocial || 'Cliente')}}</li>
            <li>{{orden.articulo ? orden.articulo.nombre : (orden.articulo_nombre || 'N/A')}} 
                <div v-for="(plancha,index) in orden.planchas" :key="index">
                    <div v-if="plancha.id==orden.plancha">Plancha: {{ plancha.referencia}} {{ plancha.uso }}</div>
                </div>
            </li>
            <li><div v-for="(detalle,index) in orden.detalles" :key="index" v-if="detalle.titulo=='Papel'">{{ detalle.valor }}</div></li>
            <li class="d-flex align-items-center justify-content-between">
                <span>F. {{orden.fecha_entrega}}</span>
                <span v-if="orden.es_cliente_nuevo" class="nuevo-inline-badge" title="Primer Pedido de este Cliente">
                    🌱 NUEVO
                </span>
            </li>
            <li class="colores d-flex flex-wrap" v-for="(detalle,index) in orden.detalles" :key="index" v-if="esColor(detalle)"> 
              <div v-for="(v,i) in parseColorValores(detalle.valor)" :key="i" class="mr-1 mb-1">
                <div class="color"
                :style="{ background: v.hex, width: '20px', height: '20px', borderRadius: '3px' }" 
                :title="v.pantone || v.nombre || ''"
              ></div> 
              </div> 
            </li>
            <li v-if="orden.status.estado == 'Terminado'" class="mt-2 pt-2 border-top" @click.stop>
                <div v-if="orden.terminado && orden.terminado.length > 0">
                    <div v-if="orden.terminado.length === 1 && orden.terminado[0].costois_id > 0">
                        <div class="text-center font-weight-bold text-dark mb-1" style="font-size: 11px;">
                            {{ orden.terminado[0].activo ? orden.terminado[0].activo.activo : (orden.terminado[0].costois ? orden.terminado[0].costois.nombre : 'Operaria') }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size: 11px; font-weight: bold; color: #333;">V. Unit:</span>
                            <div class="input-group input-group-sm" style="width: 80px;">
                                <input type="number" 
                                       class="form-control text-right py-0 px-1" 
                                       style="height: 20px; font-size: 11px;" 
                                       v-model.number="valorTerminado" 
                                       @change="actualizarValorTerminado"
                                       @keyup.enter="actualizarValorTerminado"
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size: 11px; font-weight: bold; color: #333;">Entregada:</span>
                            <div class="input-group input-group-sm" style="width: 80px;">
                                <input type="number" 
                                       class="form-control text-right py-0 px-1" 
                                       style="height: 20px; font-size: 11px;" 
                                       v-model.number="cantidadEntregada" 
                                       @change="actualizarCantidadEntregada"
                                       @keyup.enter="actualizarCantidadEntregada"
                                       placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div v-else-if="orden.terminado.length > 1">
                        <div class="text-primary font-weight-bold mb-1" style="font-size: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 2px;">
                            <i class="fa fa-users"></i> Operarias:
                        </div>
                        <div v-for="(term, idx) in orden.terminado" :key="idx" class="d-flex justify-content-between small text-muted mb-1" style="font-size: 10px; line-height: 1.2;">
                            <span class="font-weight-bold text-dark">{{ term.activo ? term.activo.activo : (term.costois ? term.costois.nombre : 'Operaria') }}</span>
                            <span>{{ term.cuenta_cobro ? term.cuenta_cobro.cantidad_entregada : 0 }}/{{ term.cantidad }} u</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-danger small font-weight-bold text-center">
                    Sin operaria asignada
                </div>
            </li>
          </ul>
         
          
            
      </div>
      
      <!-- Floating 3D Diagonal Capsule VIP Sticker (Identical to user screenshot) -->
      <div v-if="String(orden.prioridad).toLowerCase() === 'vip'" class="vip-sticker-badge" title="Órden VIP con Máxima Prioridad">
          <span class="vip-star">★</span>
          <span class="vip-text">VIP</span>
      </div>
  </div>
  <div class="modal-backdrop-custom" v-if="modal">
      <div class="modal-dialog-custom ordenescritorio" role="document">
          <div id="zoomDiv" class="modal-content-custom shadow-lg" :style="{transform: `scale(${escala})`}">
            <div class="contenedor-header">
               <div class="d-flex align-items-center justify-content-between w-100">
                  <div class="d-flex align-items-center" @click="cerrarmodal()" style="cursor: pointer;" title="Cerrar ventana">
                     <h4 class="mb-0 text-white mr-3 d-flex align-items-center">
                       <strong>Orden No. {{orden.id}}</strong>
                       <span v-if="orden.pedido" class="badge badge-warning text-dark ml-2 font-weight-bold" style="font-size: 13px; vertical-align: middle;">
                         Ped. #{{ orden.pedido.num_comprobante || orden.pedido.id }}
                       </span>
                     </h4>
                    <span class="estado-pill">{{orden.status.estado}}</span>
                  </div>
                   <button type="button" class="close-custom" @click="cerrarmodal()">
                     <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               
               <div class="d-flex mt-2 align-items-center">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2 d-inline-flex align-items-center" @click="abrirModalEntrega()" :title="orden.num_remisiones ? `Entrega / Remisión (${orden.num_remisiones} remisión(es))` : 'Entrega / Remisión'">
                        <i class="fa fa-truck"></i>
                        <span v-if="orden.num_remisiones && orden.num_remisiones > 0" class="badge badge-primary badge-pill ml-1 font-weight-bold" style="font-size: 0.68rem; padding: 0.2em 0.4em;">{{ orden.num_remisiones }}</span>
                    </button>
                   <button type="button" class="btn btn-outline-warning btn-sm mr-2" @click="generarExcelOrdenes(orden)" title="Imprimir/Generar Excel">
                       <i class="fa fa-print"></i>
                   </button>
                  <a v-if="archivo!=''" class="btn btn-sm btn-light text-dark mr-2" :href="`/reportes/${archivo}`" target="_blank">
                      <i class="fa fa-download"></i> {{archivo}}
                  </a>
                  
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-dark" @click="ajustarZoom(-0.02)"><i class="fa fa-minus"></i></button>
                    <button class="btn btn-light disabled"><i class="fa fa-search"></i></button>
                    <button class="btn btn-dark" @click="ajustarZoom(0.02)"><i class="fa fa-plus"></i></button>
                  </div>
               </div>
            </div>
            
             
              <div class="contenedor-seccion flex-row-bet bg-white p-2 border-bottom">
               
                <div class="btn-cambio-estado w-100 justify-content-center">

                  <button v-for="(proceso, index) in procesos" :key="index" 
                          class="btn btn-sm m-1 shadow-sm" 
                          :class="orden.status.estado==proceso.proceso ? 'btn-success' : 'btn-outline-secondary'" 
                          @click="cambiarEstado(orden, orden.status.id, orden.status.estado, proceso.proceso)">
                     {{proceso.proceso}}
                   </button>
                </div>
               
              </div>
              
              
              
              <div class="modal-body-custom">
                  <div class="d-flex justify-content-end align-items-center mb-2">
                        <button type="button" class="btn btn-sm text-white shadow-sm mr-2" style="background-color: #5c2c74; border: none; font-weight: bold; padding: 6px 14px; border-radius: 4px;" @click="imprimirHojaRuta(orden)" title="Imprimir Hoja de Ruta de Producción">
                            <i class="fa fa-print"></i> Hoja de Ruta (PDF)
                        </button>
                        <button type="button" class="btn btn-sm text-white shadow-sm mr-2" :class="orden.hoja_ruta_escaneada ? 'btn-success' : 'btn-info'" style="font-weight: bold; padding: 6px 14px; border-radius: 4px;" @click="gestionarHojaRutaEscaneada(orden)" :title="orden.hoja_ruta_escaneada ? 'Ver Hoja de Ruta Digitalizada' : 'Subir Hoja de Ruta Escaneada / Foto'">
                            <i class="fa" :class="orden.hoja_ruta_escaneada ? 'fa-file-image-o' : 'fa-camera'"></i> {{ orden.hoja_ruta_escaneada ? 'Ver Hoja Escaneada' : 'Adjuntar Hoja Física' }}
                        </button>
                        <button v-for="(envio, i) in orden.cliente.envios" :key="i" class="btn btn-warning btn-sm shadow-sm" v-if="envio.favorito" @click="generarGuia(envio, orden.cliente.razonsocial)"> 
                            <i class="fa fa-truck"></i> Imprimir Guia 
                        </button>
                   </div>

                  <div class="datosorden row">
                    <div class="col-md-4 mb-2"> 
                         <strong><i class="fa fa-layer-group"></i> Plancha:</strong>
                      <select class="form-control form-control-sm mt-1" v-model="orden.plancha" @change="cambiarPlancha()" >
                          <option v-for="(plancha,index) in orden.planchas" :key="index" :value="plancha.id">{{plancha.referencia}} {{ plancha.uso }} {{ plancha.detalles }}</option>
                          <option value="0">No Existe</option>
                      </select>  
                    </div>
                    <div class="col-md-4 mb-2"> <strong><i class="fa fa-user-tag"></i> Asignar:</strong>
                      <div class="input-group input-group-sm mt-1">
                          <select class="form-control" v-model="tipoactivo" @change="abrirOpciones()">
                            <option value="Troquelado">Troquelado</option>
                            <option value="Terminado">Terminado</option>
                          </select>
                      </div>
                      <div v-if="arrayactivos.length>0 && (tipoactivo !== 'Terminado' || !dividirOrden)" class="mt-1">
                        <select class="form-control form-control-sm" v-model="activoasignado" @change="asignarActivo(orden)">
                          <option v-for="(activo,i) in arrayactivos" :key="i" :value="activo">{{activo.activo}}</option>
                        </select>
                        <div v-if="tipoactivo === 'Terminado' && !dividirOrden && orden.terminado && orden.terminado.length === 1 && orden.terminado[0].costois_id > 0" class="mt-1 small text-muted">
                          <strong>Asignada:</strong> {{ orden.terminado[0].activo ? orden.terminado[0].activo.activo : (orden.terminado[0].costois ? orden.terminado[0].costois.nombre : 'Operaria') }}
                        </div>
                        <div v-if="tipoactivo === 'Troquelado' && orden.troquelado && orden.troquelado.length > 0" class="mt-1 small text-muted">
                          <strong>Asignado:</strong> {{ orden.troquelado[0].activo ? orden.troquelado[0].activo.activo : (orden.troquelado[0].costois ? orden.troquelado[0].costois.nombre : 'Troquelado') }}
                        </div>
                      </div>
                      <div v-if="tipoactivo === 'Terminado'" class="mt-2">
                        <div class="custom-control custom-switch custom-control-inline">
                          <input type="checkbox" class="custom-control-input" id="switchDividir" v-model="dividirOrden">
                          <label class="custom-control-label small font-weight-bold text-dark" for="switchDividir" style="cursor: pointer; user-select: none;">Dividir entre varias operarias</label>
                        </div>
                      </div>
                    </div>
                   
                    <div class="col-md-4 mb-2"> 
                          <div class="p-2 border rounded bg-light">
                               <strong>Prioridad:</strong> 
                               <span v-if="String(orden.prioridad).toLowerCase()=='vip'" class="badge badge-vip-gold ml-1">⭐ VIP</span>
                               <span v-else :class="{'text-danger': orden.prioridad=='urgente', 'text-warning': orden.prioridad=='media'}">{{orden.prioridad}} {{ orden.status ? orden.status.prioridad : '' }}</span>
                          </div>
                      </div>

                      <!-- Single Operaria finished stage section -->
                      <div v-if="orden.status.estado == 'Terminado' && !dividirOrden && orden.terminado && orden.terminado.length > 0 && orden.terminado[0].costois_id > 0" class="col-md-4 mb-2">
                        <div class="p-2 border rounded bg-light">
                          <strong><i class="fa fa-calculator text-primary"></i> Pago Terminado:</strong>
                          <div class="d-flex align-items-center mt-1">
                            <span class="mr-1 small font-weight-bold" style="min-width: 60px;">V. Unit:</span>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" 
                                       class="form-control text-right" 
                                       v-model.number="valorTerminado" 
                                       @change="actualizarValorTerminado"
                                       @keyup.enter="actualizarValorTerminado"
                                       placeholder="0">
                            </div>
                          </div>
                          <div class="d-flex align-items-center mt-1">
                            <span class="mr-1 small font-weight-bold" style="min-width: 60px;">Entregada:</span>
                            <div class="input-group input-group-sm">
                                <input type="number" 
                                       class="form-control text-right" 
                                       v-model.number="cantidadEntregada" 
                                       @change="actualizarCantidadEntregada"
                                       @keyup.enter="actualizarCantidadEntregada"
                                       placeholder="0">
                            </div>
                          </div>
                          <div class="d-flex justify-content-between mt-1 small">
                              <span>Total:</span>
                              <span class="font-weight-bold text-success">${{ (valorTerminado * orden.cantidad).toLocaleString() }}</span>
                          </div>
                          <div class="d-flex justify-content-between mt-1 small border-top pt-1">
                              <span>Por Pagar:</span>
                              <span class="font-weight-bold text-info">${{ (valorTerminado * cantidadEntregada).toLocaleString() }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Multiple Operarias finished stage section -->
                      <div v-if="orden.status.estado == 'Terminado' && dividirOrden" class="col-md-12 mb-3">
                        <div class="card border-primary">
                          <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center" style="background-color: #007bff !important;">
                            <strong style="font-size: 13px;"><i class="fa fa-users"></i> Distribución de Trabajo: Operarias (Terminado)</strong>
                            <span class="badge badge-light" style="font-size: 11px;">Total Orden: {{ orden.cantidad }} uds</span>
                          </div>
                          <div class="card-body p-2">
                            <!-- Alert if total assigned does not match order quantity -->
                            <div v-if="totalAsignadoTerminado !== orden.cantidad" class="alert alert-warning py-1 px-2 mb-2 text-dark font-weight-bold animate__animated animate__pulse animate__infinite" style="font-size: 11px; background-color: #ffeeba; border-color: #ffeeba;">
                              <i class="fa fa-exclamation-triangle"></i> Suma Asignada: {{ totalAsignadoTerminado }} de {{ orden.cantidad }}. 
                              <span v-if="totalAsignadoTerminado < orden.cantidad">Faltan {{ orden.cantidad - totalAsignadoTerminado }} unidades por asignar.</span>
                              <span v-else>Exceso de {{ totalAsignadoTerminado - orden.cantidad }} unidades asignadas.</span>
                            </div>
                            <div v-else class="alert alert-success py-1 px-2 mb-2 font-weight-bold text-white" style="font-size: 11px; background-color: #28a745; border-color: #28a745;">
                              <i class="fa fa-check-circle"></i> Cantidades asignadas correctamente ({{ totalAsignadoTerminado }} de {{ orden.cantidad }}).
                            </div>

                            <!-- Assigned operarias table -->
                            <div class="table-responsive">
                              <table class="table table-bordered table-sm mb-2" style="font-size: 11px;">
                                <thead class="thead-light">
                                  <tr>
                                    <th>Operaria</th>
                                    <th style="width: 100px;">Cant. Asignada</th>
                                    <th style="width: 90px;">V. Unitario</th>
                                    <th style="width: 100px;">Cant. Entregada</th>
                                    <th>Total Payout</th>
                                    <th>Para Pago</th>
                                    <th style="width: 70px; text-align: center;">Acciones</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(term, idx) in orden.terminado" :key="idx">
                                    <td class="align-middle font-weight-bold">
                                      {{ term.activo ? term.activo.activo : (term.costois ? term.costois.nombre : 'Operaria') }}
                                    </td>
                                    <td>
                                      <input type="number" class="form-control form-control-sm text-right p-1" style="height: 25px; font-size: 11px;" v-model.number="term.cantidad">
                                    </td>
                                    <td>
                                      <div class="input-group input-group-sm" style="height: 25px;">
                                        <div class="input-group-prepend"><span class="input-group-text p-1" style="font-size: 10px;">$</span></div>
                                        <input type="number" class="form-control form-control-sm text-right p-1" style="height: 25px; font-size: 11px;" v-model.number="term.valor">
                                      </div>
                                    </td>
                                    <td>
                                      <input type="number" class="form-control form-control-sm text-right p-1" style="height: 25px; font-size: 11px;" v-model.number="term.cuenta_cobro.cantidad_entregada" v-if="term.cuenta_cobro">
                                      <span v-else class="text-muted text-center d-block">-</span>
                                    </td>
                                    <td class="align-middle text-right text-success font-weight-bold">
                                      ${{ ((term.cantidad || 0) * (term.valor || 0)).toLocaleString() }}
                                    </td>
                                    <td class="align-middle text-right text-info font-weight-bold">
                                      ${{ term.cuenta_cobro ? ((term.cuenta_cobro.cantidad_entregada || 0) * (term.valor || 0)).toLocaleString() : '$0' }}
                                    </td>
                                    <td class="align-middle text-center">
                                      <button type="button" class="btn btn-success btn-xs p-1" @click="actualizarOperaria(term)" title="Guardar cambios" style="padding: 1px 5px !important; font-size: 10px;">
                                        <i class="fa fa-check"></i>
                                      </button>
                                      <button type="button" class="btn btn-danger btn-xs p-1" @click="eliminarOperaria(term)" title="Eliminar asignación" style="padding: 1px 5px !important; font-size: 10px;">
                                        <i class="fa fa-trash"></i>
                                      </button>
                                    </td>
                                  </tr>
                                  <tr v-if="!orden.terminado || orden.terminado.length === 0">
                                    <td colspan="7" class="text-center text-muted py-2">Ninguna operaria asignada a esta orden.</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>

                            <!-- Add new operaria form -->
                            <div class="p-2 border rounded bg-light mt-2">
                              <h6 class="mb-2" style="font-size: 12px; font-weight: bold;"><i class="fa fa-user-plus text-primary"></i> Agregar Nueva Operaria</h6>
                              <div class="row align-items-end">
                                <div class="col-md-4">
                                  <label class="mb-1 small font-weight-bold">Seleccionar Operaria:</label>
                                  <select class="form-control form-control-sm" v-model="nuevaOperariaSelected">
                                    <option :value="null" disabled>Seleccionar...</option>
                                    <option v-for="(activo, i) in operariasList" :key="i" :value="activo">{{ activo.activo }}</option>
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <label class="mb-1 small font-weight-bold">Cantidad:</label>
                                  <input type="number" class="form-control form-control-sm text-right" v-model.number="nuevaOperariaCantidad" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                  <label class="mb-1 small font-weight-bold">Valor Unitario:</label>
                                  <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text py-0 px-2" style="font-size: 10px;">$</span></div>
                                    <input type="number" class="form-control form-control-sm text-right" v-model.number="nuevaOperariaValor" placeholder="0">
                                   </div>
                                </div>
                                <div class="col-md-2 text-right">
                                  <button type="button" class="btn btn-primary btn-sm btn-block" @click="guardarNuevaOperaria()" style="font-size: 11px;">
                                    <i class="fa fa-plus-circle"></i> Asignar
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                     
                     <div class="col-md-12"><hr class="my-2"></div>

                    <div class="col-md-3"> <strong>Fecha entrega:</strong> {{orden.fecha_entrega}} </div>
                    <div class="col-md-5"> <strong>Cliente:</strong> {{orden.cliente ? orden.cliente.razonsocial : (orden.rasonsocial || 'N/A')}} </div>
                    <div class="col-md-4"> <strong>Articulo:</strong> {{orden.articulo ? orden.articulo.nombre : (orden.articulo_nombre || 'N/A')}} </div>
                    
                    <div class="col-md-12"><hr class="my-2"></div>

                    <div class="col-md-3"> <strong>Cantidad final:</strong> {{orden.cantidad}} </div>
                    <div class="col-md-3"> <strong>Medida Final:</strong> {{orden.medida_final || 'N/A'}} </div>

                    <!-- Desglose organizado por Papel / Material -->
                    <div class="col-md-12 mt-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 bg-white" style="font-size: 11px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Papel / Material</th>
                                        <th class="text-center">Pliegos</th>
                                        <th class="text-center">Corte Material</th>
                                        <th class="text-center">Tamaño</th>
                                        <th class="text-center">Cabida</th>
                                        <th class="text-center">Tamaños + Sobrante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(pItem, pIdx) in listaPapeles" :key="pIdx">
                                        <td class="font-weight-bold text-primary">
                                            {{ pItem.nombre }} <span v-if="pItem.componente" class="badge badge-info ml-1">[{{ pItem.componente }}]</span>
                                        </td>
                                        <td class="text-center font-weight-bold text-success">{{ typeof pItem.pliegos === 'number' ? pItem.pliegos.toFixed(0) : pItem.pliegos }}</td>
                                        <td class="text-center">{{ pItem.corte }}</td>
                                        <td class="text-center">{{ pItem.tamano }}</td>
                                        <td class="text-center">{{ pItem.cabida }}</td>
                                        <td class="text-center">{{ typeof pItem.tamanosSobrante === 'number' ? pItem.tamanosSobrante.toFixed(0) : pItem.tamanosSobrante }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Reasignar Pedido Section -->
                    <div class="col-md-12 mt-3">
                        <div class="p-2 border rounded bg-light-blue" style="background-color: #f0f7ff;">
                            <strong><i class="fa fa-exchange"></i> Reasignar a otro Pedido:</strong>
                            <div class="input-group input-group-sm mt-1">
                                <input type="text" class="form-control" placeholder="Buscar Pedido # o Cliente..." v-model="buscarPedidoText" @keyup.enter="buscarPedidos()">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" @click="buscarPedidos()"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                            <div v-if="arrayPedidosAsignar.length > 0" class="mt-1">
                                <select class="form-control form-control-sm" v-model="pedidoDestinoId">
                                    <option v-for="(p,i) in arrayPedidosAsignar" :key="i" :value="p.id">#{{p.num_comprobante}} - {{p.cliente.razonsocial}} (${{p.total}})</option>
                                </select>
                                <button class="btn btn-warning btn-sm btn-block mt-1" v-if="pedidoDestinoId" @click="confirmarReasignacion()">
                                    Confirmar Reasignación
                                </button>
                            </div>
                        </div>
                    </div>
                  </div>
                  
                  <div class="detalles mt-3">
                    <h5 class="bg-light p-2 border rounded text-primary">Detalles orden</h5>
                    <ul class="items-detalles list-group list-group-flush">
                      <li v-for="(detalle, ind) in orden.detalles" :key="ind" class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <div style="flex:1; font-weight:bold;">{{detalle.titulo}}</div>
                        <div style="flex:2;">
                            <div v-if="Array.isArray(detalle.valor)">
                              <span v-for="(valor,index) in detalle.valor" :key="index" class="badge mr-1 border" :style="'background:'+valor.hex + (valor.hex.toUpperCase() === '#FFFFFF' ? '; color:#000' : '; color:#fff')">{{valor.pantone}}</span>
                            </div>
                            <div v-else>
                              {{detalle.valor}}
                            </div>
                        </div>
                        <div style="flex:2;" class="text-muted text-right">{{detalle.descripcion}}</div>
                      </li>
                      <li class="list-group-item bg-light text-dark font-italic p-2">{{orden.observaciones}}</li>
                    </ul>
                     
                     <div v-if="editardetalles" class="contenedor-seccion border p-3 rounded mt-2">
                         
                          <div class="seccion-body">
                                  
                              <ul v-if="orden.detalles.length" class="list-unstyled">
                                  <li v-for="(detalle,index) in orden.detalles" :key="index" class="row mb-2 align-items-center">
                                          <div class="col-1">
                                              <button @click="eliminarDetalle(index)" type="button" class="btn btn-danger btn-sm rounded-circle">
                                                  <i class="fa fa-times"></i>
                                              </button>
                                          </div>
                                          <div class="col-3">
                                              <input type="text" v-model="detalle.titulo" class="form-control form-control-sm">
                                          </div>
                                          <div class="col-3" v-if="Array.isArray(detalle.valor)">
                                              <div v-for="(valor,index) in detalle.valor" :key="index" type="text" class="form-control form-control-sm" :style="'color:#fff; background:'+valor.hex">{{valor.pantone}}</div>
                                          </div>
                                          <div class="col-3" v-else>
                                            <input type="text" v-model="detalle.valor" class="form-control form-control-sm" placeholder="Valor detalle">
                                          </div>
                                          <div class="col-5">
                                              <input type="text" v-model="detalle.descripcion" class="form-control form-control-sm" placeholder="Descripción">
                                          </div>
                                          <div class="col-12 mt-1" v-if="detalle.titulo=='Papel'">
                                              <input  v-if="detalle.costo==null || !detalle.costo.costois || (detalle.costo && detalle.costo.costois && detalle.costo.costois.nombre=='')" type="text" class="form-control form-control-sm" @keyup="selectInsumos($event,detalle)" placeholder="Asigne Insumo, Maquina">
                                              <input type="text" v-else v-model="detalle.costo.costois.nombre" class="form-control form-control-sm">
                                          </div>
                                          <div class="col-12 mt-1" v-else-if="esColor(detalle)" >
                                              <selectorcolor wi="140" ma="105" :detalle="detalle"></selectorcolor>
                                          </div>
                                      </li>
                                    </ul>
                                    
                                    <!-- Moved outside ul to be a standalone field -->
                                    <div class="form-group mt-3">
                                        <textarea class="observaciones form-control" v-model="orden.observaciones" placeholder="Observaciones de la orden..." rows="3"></textarea>
                                    </div>

                                      <div class="btn-detalles mt-2 d-flex justify-content-end">
                                        <button class="btn btn-secondary btn-sm mr-2" @click="cerrarEditarDetalles()">Cancelar</button>
                                        <button @click="guardarOpciones(orden,'detalles')" type="button" class="btn btn-success btn-sm" ><i class="fa fa-save"></i> Guardar Cambios</button>
                                      </div>  
                                                             
                          </div>
                      </div> 
                      <div v-else class="mt-2 text-right">
                          <button class="btn btn-outline-info btn-sm" @click="abrirEditarDetalles()"><i class="fa fa-edit"></i> Editar detalles</button>
                      </div>
                    
                  </div>
              </div>
              
              <div class="px-3 pb-3">
                 <div v-if="opcionproduccion" class="row contenedor-seccion detmateriales mt-3 border p-3 rounded bg-white mx-0 shadow-sm">
                      <div class="col-md-12 mb-2">
                          <h6 class="text-primary font-weight-bold"><i class="fa fa-cogs"></i> Opciones de Orden de Producción</h6>
                      </div>

                      <!-- Medida Final General de la Orden -->
                      <div class="col-md-3 form-group">
                          <label class="small font-weight-bold">Medida Final del Trabajo</label>
                          <input type="text" class="form-control form-control-sm" v-model="orden.medida_final">
                      </div>

                      <!-- Especificaciones Individuales por Material / Papel -->
                      <div class="col-md-12">
                          <label class="small font-weight-bold text-dark">Especificaciones por Material / Papel:</label>
                          <div v-for="(pItem, idx) in listaPapeles" :key="idx" class="border rounded p-2 mb-2 bg-light">
                              <div class="row align-items-center">
                                  <div class="col-md-12 mb-2" v-if="cabidasArticulo && cabidasArticulo.length > 0">
                                      <div class="p-2 bg-white border rounded d-flex align-items-center flex-wrap">
                                          <span class="mr-2 font-weight-bold text-purple" style="font-size: 11px;">
                                              <i class="fa fa-th"></i> Posibles Cabidas del Artículo:
                                          </span>
                                          <button type="button" 
                                                  v-for="(cOpt, cIdx) in cabidasArticulo" 
                                                  :key="cIdx"
                                                  class="btn btn-xs mr-2 mb-1"
                                                  :class="parseFloat(pItem.cabida) === parseFloat(cOpt.cabida) ? 'btn-primary font-weight-bold' : 'btn-outline-secondary'"
                                                  @click="onSeleccionarCabidaMaterial(pItem, cOpt)">
                                              Cabida {{ cOpt.cabida }} (Mat: {{ cOpt.medida_material }})
                                          </button>
                                          <span v-if="parseFloat(orden.cantidad) > 1000" class="badge badge-warning text-dark ml-auto" style="font-size: 11px;">
                                              <i class="fa fa-lightbulb-o"></i> Cantidad > 1.000: Cabida mayor sugerida
                                          </span>
                                      </div>
                                  </div>
                                  <div class="col-md-3 form-group mb-1">
                                      <label class="small font-weight-bold mb-0 text-primary">
                                          Papel / Insumo {{ pItem.componente ? '[' + pItem.componente + ']' : (listaPapeles.length > 1 ? '#' + (idx+1) : '') }}
                                      </label>
                                      <input type="text" class="form-control form-control-sm" v-model="pItem.nombre" @keyup="selectInsumos($event, pItem)" placeholder="Buscar o asignar papel...">
                                  </div>
                                  <div class="col-md-2 form-group mb-1">
                                      <label class="small font-weight-bold mb-0">Medida mat.</label>
                                      <input type="text" class="form-control form-control-sm" v-model="pItem.corte" placeholder="Ej: 70x100">
                                  </div>
                                  <div class="col-md-1 form-group mb-1">
                                      <label class="small font-weight-bold mb-0">Tamaño</label>
                                      <input type="text" class="form-control form-control-sm" v-model="pItem.tamano" @input="recalcularPapelItem(pItem)" @keyup="recalcularPapelItem(pItem)" @change="recalcularPapelItem(pItem)" placeholder="Ej: 4">
                                  </div>
                                  <div class="col-md-1 form-group mb-1">
                                      <label class="small font-weight-bold mb-0">Cabida</label>
                                      <input type="text" class="form-control form-control-sm" v-model="pItem.cabida" @input="recalcularPapelItem(pItem)" @keyup="recalcularPapelItem(pItem)" @change="recalcularPapelItem(pItem)" placeholder="1.00">
                                  </div>
                                  <div class="col-md-1 form-group mb-1">
                                      <label class="small font-weight-bold mb-0">Sobrante</label>
                                      <input type="text" class="form-control form-control-sm" v-model="pItem.sobrante" @input="recalcularPapelItem(pItem)" @keyup="recalcularPapelItem(pItem)" @change="recalcularPapelItem(pItem)" placeholder="50">
                                  </div>
                                  <div class="col-md-2 form-group mb-1">
                                      <label class="small font-weight-bold mb-0">Pliegos</label>
                                      <input type="number" step="any" class="form-control form-control-sm" v-model.number="pItem.pliegos" @input="recalcularPorPliegos(pItem)" @keyup="recalcularPorPliegos(pItem)" @change="recalcularPorPliegos(pItem)">
                                  </div>
                                  <div class="col-md-2 form-group mb-1">
                                      <label class="small font-weight-bold mb-0">Cant. Tamaños</label>
                                      <input type="number" step="any" class="form-control form-control-sm bg-white" v-model.number="pItem.tamanosSobrante" @input="recalcularPorTamanos(pItem)" @keyup="recalcularPorTamanos(pItem)" @change="recalcularPorTamanos(pItem)">
                                  </div>
                              </div>
                          </div>
                      </div>

                      <!-- Botón ÚNICO de Guardar y Cancelar -->
                      <div class="col-md-12 d-flex justify-content-end mt-2">
                          <button class="btn btn-secondary btn-sm mr-2" @click="cerrarOpcionePro()">Cancelar</button>
                          <button @click="guardarOpcionesMateriales(orden)" type="button" class="btn btn-success btn-sm font-weight-bold">
                              <i class="fa fa-save"></i> Guardar Cambios
                          </button>
                      </div>
                </div>
                <div v-else class="mt-2">
                    <button class="btn btn-link btn-sm" @click="abrirOpcionePro()">+ Opciones de orden de produccion</button>
                </div>
                
                  <div v-if="orden.status.estado=='Para entregar' || orden.status.estado=='Empacado'" class="row actualizar mt-3 border-top pt-2">
                      <div class="col-sm-12 mb-2">
                        <div class="progress" style="height: 20px;">
                          <div class="progress-bar bg-success" role="progressbar" :style="'width: ' + ((orden.cantidad_entregada || 0) / orden.cantidad * 100) + '%'" :aria-valuenow="orden.cantidad_entregada" aria-valuemin="0" :aria-valuemax="orden.cantidad">
                            {{ orden.cantidad_entregada || 0 }} / {{ orden.cantidad }} entregados
                          </div>
                      </div>
                    </div>
                      <div  class="col-sm-8">
                          <label for="">Cantidad resultante</label>
                          <input type="text" class="form-control" v-model="orden.status.observaciones">
                      </div>
                      <div class="col-sm-4">
                       
                       
                        <button  @click="cambiarDatosEstado(orden)" class="float-right btn btn-primary boton-principal">Actualizar</button>
                      </div>
                    </div>

                 
                <div class="estado">
                 
                  <authmodal
                  :mostrarModal="mostrarModal"
                  @close="mostrarModal = null"
                  @authorized="onAutorizado"/>
                  
                  
                </div>
              </div>

          </div>
      </div>
  </div>
  <div class="modal fade" tabindex="-1" :class="{'mostrar' : modali}" role="dialog" aria-labelledby="myModalLabel"  style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog" >
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Seleccione costo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" @click="cerrarModali()">
                <span >&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <template v-if="arrayInsumos">
                    <div class="list-group list-group-flush">
                        <a href="#" 
                        class="list-group-item list-group-item-action" 
                        v-for="(insumo,index) in arrayInsumos" 
                        :key="index" 
                        @click="agregarCosto(insumo,index,objeto,seccion)">
                        {{insumo.nombre}} - <small class="text-muted">({{insumo.proveedor ? insumo.proveedor.nombre : 'S/P'}})</small>
                        </a> 
                    </div>
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="cerrarModali()" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
  </div>

  <!-- Modal Entrega Parcial -->
  <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalEntrega}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Registrar Entrega - Orden #{{ orden.id }}</h5>
          <button type="button" class="close text-white" @click="cerrarModalEntrega()">&times;</button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info py-2">
            <strong>Pendiente:</strong> {{ orden.cantidad - (orden.cantidad_entregada || 0) }} unidades
          </div>
          
          <div class="form-group">
            <label class="font-weight-bold">Cantidad a Entregar</label>
            <input type="number" class="form-control" v-model.number="cantidadEntrega" :max="orden.cantidad - (orden.cantidad_entregada || 0)" min="1">
          </div>
          
          <div class="form-group">
            <label class="font-weight-bold">Documento a Generar</label>
            <select class="form-control" v-model="tipoDocumentoEntrega">
              <option value="Remision">Remisión</option>
              <option value="Cuenta de Cobro">Cuenta de Cobro</option>
              <option value="Ninguno">Sin Documento (Solo Registro)</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="font-weight-bold">Observaciones del Despacho</label>
            <textarea class="form-control" v-model="observacionesEntrega" rows="2" placeholder="Ej: Entregado en portería, Caja 1 de 3..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="cerrarModalEntrega()" :disabled="loadingEntrega">Cancelar</button>
          <button type="button" class="btn btn-success" @click="registrarEntrega()" :disabled="loadingEntrega">
            <span v-if="loadingEntrega"><i class="fa fa-spinner fa-spin"></i> Procesando...</span>
            <span v-else>Confirmar Entrega</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import { forEach } from 'lodash';
import { Integer } from 'read-excel-file'
import selectorcolor from './SelectorColor.vue'
import authmodal from './AuthModal.vue'
 var meses=['01','02','03','04','05','06','07','08','09','10','11','12']


  
export default {
  name: 'ordencard',
  props: 
        {orden:0,
        scroll:0,
        status:0,
        operario:'',
        procesos:{type:Array},
        user:{},
    },
  data(){
    return{
      cantidadEntregadaLocal: 0,
      auth:false,
      pendienteCambio:{},
      escala:1,
      modal:0,
      modali:0,
      mensajeModal:'',
      mostrarModal:null,
      accionPendiente: null,
      inventario:0,
      opcionproduccion:0,

      editardetalles:0,
      arrayInsumos:[],
      archivo:'',
      seccion:'costo',
      buscar_insumo:'',
      objeto:{},
      asignacion_detalle:{},
      tintas:'',
      tipoactivo:'',
      activoasignado:{},
      arrayactivos:[],
      fondo:'255,255,255',
      dias:0,
      modalEntrega: 0,
      cantidadEntrega: 0,
      tipoDocumentoEntrega: 'Remision',
      observacionesEntrega: '',
      loadingEntrega: false,
      
      buscarPedidoText: '',
      arrayPedidosAsignar: [],
      pedidoDestinoId: null,
      nuevaOperariaSelected: null,
      nuevaOperariaCantidad: 0,
      nuevaOperariaValor: 0,
      dividirOrden: false,
      listaPapeles: [],
      formatoPapelHojaRuta: 'letter',
      anchoPapelCm: '21.5',
      altoPapelCm: '33'
    }
  },
  components: {
            selectorcolor,
            authmodal
        },
  computed: {
    cabidasArticulo() {
      if (!this.orden) return [];
      let art = this.orden.articulo;
      if (!art && this.orden.cabidas_materiales) {
        art = { cabidas_materiales: this.orden.cabidas_materiales };
      }
      if (!art || !art.cabidas_materiales) return [];
      let list = typeof art.cabidas_materiales === 'string'
        ? JSON.parse(art.cabidas_materiales)
        : art.cabidas_materiales;
      return Array.isArray(list) ? list : [];
    },
    cantidadTamanos() {
      const cantidad = parseFloat(this.orden.cantidad) || 0;
      const unidadMedida = parseFloat(this.orden.cabida) || 1;
      const carpetaCliente = parseFloat(this.orden.carpeta_cliente) || 0;

      var tamanos = (unidadMedida !== 0) ? (cantidad / unidadMedida) + carpetaCliente : carpetaCliente;
      
      this.orden.detalles.forEach(e => {
        if (e.titulo === 'Papel' && e.costo != null) {
          e.costo.descripcion = tamanos;
        }
      });
      return tamanos;
    },
    cantidadPliegos() {
      const cantidad = parseFloat(this.orden.cantidad) || 0;
      const unidadMedida = parseFloat(this.orden.cabida) || 1;
      const carpetaCliente = parseFloat(this.orden.carpeta_cliente) || 0;
      const anchoMaterial = parseFloat(this.orden.tamano) || 1;

      var pliegos = (unidadMedida !== 0 && anchoMaterial !== 0) 
                    ? ((cantidad / unidadMedida) + carpetaCliente) / anchoMaterial 
                    : 0;

      this.orden.detalles.forEach(e => {
        if (e.titulo === 'Papel' && e.costo != null) {
          e.costo.cantidad = pliegos;
        }
      });
      return pliegos;
    },
    valorTerminado: {
      get() {
        return (this.orden.terminado && this.orden.terminado.length > 0) ? parseFloat(this.orden.terminado[0].valor) || 0 : 0;
      },
      set(val) {
        if (this.orden.terminado && this.orden.terminado.length > 0) {
          this.$set(this.orden.terminado[0], 'valor', val);
          this.$set(this.orden.terminado[0], 'total', val * this.orden.cantidad);
        }
      }
    },
    cantidadEntregada: {
      get() {
        if (this.orden.cuenta_cobro) {
          return parseInt(this.orden.cuenta_cobro.cantidad_entregada) || 0;
        }
        return this.cantidadEntregadaLocal || 0;
      },
      set(val) {
        if (this.orden.cuenta_cobro) {
          this.$set(this.orden.cuenta_cobro, 'cantidad_entregada', val);
        } else {
          this.cantidadEntregadaLocal = val;
        }
      }
    },
    totalAsignadoTerminado() {
      if (!this.orden.terminado) return 0;
      return this.orden.terminado.reduce((acc, item) => acc + (parseFloat(item.cantidad) || 0), 0);
    },
    operariasList() {
      if (!this.arrayactivos) return [];
      const assignedIds = this.orden.terminado ? this.orden.terminado.map(t => t.costois_id) : [];
      return this.arrayactivos.filter(a => !assignedIds.includes(a.id));
    }
  },
  watch: {
    totalAsignadoTerminado(newVal) {
      this.nuevaOperariaCantidad = Math.max(0, parseFloat(this.orden.cantidad) - newVal);
    },
    dividirOrden(newVal) {
      if (!newVal && this.orden.terminado && this.orden.terminado.length > 1) {
        Swal.fire({
          title: 'Atención',
          text: 'Hay más de una operaria asignada. Para volver a asignación simple, debe eliminar las operarias sobrantes primero.',
          icon: 'warning',
          confirmButtonText: 'Entendido'
        });
        this.dividirOrden = true;
      }
    }
  },
  mounted() {
    let me = this;
    axios.get('/orden/hoja-ruta-config').then(response => {
      if (response.data) {
        if (response.data.formato_papel) me.formatoPapelHojaRuta = response.data.formato_papel;
        if (response.data.ancho_papel_cm) me.anchoPapelCm = response.data.ancho_papel_cm;
        if (response.data.alto_papel_cm) me.altoPapelCm = response.data.alto_papel_cm;
      }
    }).catch(e => {});
  },
  methods: {
    esColor(detalle) {
      if (!detalle) return false;
      if (Array.isArray(detalle.valor) && detalle.valor.length > 0 && typeof detalle.valor[0] === 'object' && detalle.valor[0].hex) return true;
      let t = (detalle.titulo || '').toLowerCase();
      if (t.includes('tinta') || t.includes('impresion') || t.includes('impresión') || t.includes('color')) return true;
      return false;
    },
    parseColorValores(valor) {
      if (Array.isArray(valor)) return valor;
      if (typeof valor === 'string' && valor.trim().startsWith('[')) {
        try { return JSON.parse(valor); } catch(e) { return []; }
      }
      return [];
    },
    imprimirHojaRuta(orden) {
      if (window.abrirModalProcesosHojaRuta) {
        window.abrirModalProcesosHojaRuta(orden);
      } else {
        let id = (orden && (orden.idorden || orden.id)) ? (orden.idorden || orden.id) : orden;
        if (id) window.open('/orden/hoja-ruta/' + id, '_blank');
      }
    },
    actualizarTerminadoLocal(terminado) {
      this.orden.terminado = terminado;
      if (terminado && terminado.length === 1) {
        this.orden.cuenta_cobro = terminado[0].cuenta_cobro;
      } else {
        this.orden.cuenta_cobro = null;
      }
    },
    actualizarValorTerminado() {
      const me = this;
      const val = me.valorTerminado;
      axios.post('/statuspro/actualizarValorTerminado', {
        orden_id: me.orden.id,
        valor: val
      })
      .then(response => {
        if (response.data.success) {
          me.actualizarTerminadoLocal(response.data.terminado);
          Swal.fire({
            title: '¡Valor Guardado!',
            text: `Valor unitario de terminado para Orden #${me.orden.id} actualizado a $${val}`,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
          });
          me.$emit('refrescar', 'local');
        }
      })
      .catch(error => {
        console.error(error);
        const msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo guardar el valor de terminado.';
        Swal.fire('Error', msg, 'error');
      });
    },
    actualizarCantidadEntregada() {
      const me = this;
      const cant = me.cantidadEntregada;
      axios.post('/statuspro/actualizarCantidadEntregada', {
        orden_id: me.orden.id,
        cantidad_entregada: cant
      })
      .then(response => {
        if (response.data.success) {
          me.actualizarTerminadoLocal(response.data.terminado);
          Swal.fire({
            title: '¡Cantidad Guardada!',
            text: `Cantidad entregada de terminado para Orden #${me.orden.id} actualizada a ${cant}`,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
          });
          me.$emit('refrescar', 'local');
        }
      })
      .catch(error => {
        console.error(error);
        const msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo guardar la cantidad entregada.';
        Swal.fire('Error', msg, 'error');
      });
    },
    getEntregaColor(fechaEntrega) {
        const hoy = new Date();
        const entrega = new Date(fechaEntrega);

        // Normalizar horas
        hoy.setHours(0, 0, 0, 0);
        entrega.setHours(0, 0, 0, 0);

        const diffMs = entrega - hoy;
        const diasRestantes = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        const diasMax = 12; // cantidad de días desde verde hasta rojo

        // Progreso de 0 (fecha vencida o hoy) a 1 (muy anticipado)
        const progreso = Math.max(0, Math.min(1, diasRestantes / diasMax));
        // Hue va de 0 (rojo) a 120 (verde), así que lo invertimos
        var hue = Math.round(170 * progreso); // 120 = verde, 0 = rojo
      
        if(hue>120){
          var d=hue-120
          hue=120-d/2
          
        }

        
        const color = `hsl(${hue}, 100%, 50%)`;

        return color;
      },


     ajustarTop(cambio) {
      let newTop = this.scroll + cambio;
      if (newTop < -50) newTop = 0.1;
      if (newTop > 1000) newTop = 3;
      this.scroll = newTop;
    },
    ajustarZoom(cambio) {
      let newScale = this.escala + cambio;
      if (newScale < 0.1) newScale = 0.1;
      if (newScale > 3) newScale = 3;
      this.escala = newScale;
    },
    preguntarInventario(id,estadoactual,estado) {
      if(estadoactual==1 && estado>1 || estadoactual==2 && estado==1){
        this.mostrarModal = {'id':id,
          'estado':estado
        };
        if (estado === 1 || estado === 0) {
          // Preguntar si se hará una entrada de material
          this.mensajeModal = "¿Desea sumar papel en el inventario?";
          this.accionPendiente = () => {
            this.inventario = 2; // Cambiar inventario a 2
          };
          
        } else if (estado > 1) {
          // Preguntar si se hará una salida de material
          this.mensajeModal = "¿Desea descontar papel en el inventario?";
          this.accionPendiente = () => {
            this.inventario = 1; // Cambiar inventario a 1
          };
        }
      }else{
        this.cerrarModal();
        this.cambiarEstado(id,estado)
      }
    },
  async CambiarPrioridad(orden){
    orden.status.prioridad=1
    var me=this
    var or=me.orden
    const response = await axios.put('/statuspro/cambiarPrioridad',{
        'orden':or,
        'id':or.id,
    })
    console.log(response)
  },
   getIndexProceso(nombreProceso) {
    if (!nombreProceso) return -1;
    return this.procesos.findIndex(p => p.proceso.trim() === nombreProceso.toString().trim());
  },
   async cambiarEstado(orden,id, estadoactual, estado) {
      const me = this
      
      // If moving to Terminado and has assigned operaria, prompt for value first!
      if (estado === 'Terminado' && orden.terminado && orden.terminado.length > 0 && orden.terminado[0].costois_id > 0) {
        const nombreOperaria = me.operario || (orden.terminado[0].costois ? orden.terminado[0].costois.nombre : 'la operaria');
        const result = await Swal.fire({
          title: 'Valor Unitario de Terminado',
          text: `La orden pasará a Terminado. Ingrese el valor unitario para ${nombreOperaria}:`,
          input: 'number',
          inputValue: orden.terminado[0].valor || '',
          inputAttributes: {
            min: 0,
            step: 'any'
          },
          showCancelButton: true,
          confirmButtonText: 'Confirmar',
          cancelButtonText: 'Cancelar',
          inputValidator: (value) => {
            if (!value || value < 0) {
              return 'Debe ingresar un valor válido igual o mayor a 0'
            }
          }
        });
        
        if (!result.value) {
          return; // Cancelled
        }
        
        me.valorTerminadoTemp = parseFloat(result.value);
      }

      // If moving away from Terminado and has assigned operaria, set final quantity directly to order quantity without prompting
      let cantidad_final = 0;
      if (estadoactual === 'Terminado' && estado !== 'Terminado' && orden.terminado && orden.terminado.length > 0 && orden.terminado[0].costois_id > 0) {
        cantidad_final = orden.cantidad;
      }

      let costois_id = 0
      let cantidad = 0
      let tipo = ''
      let observaciones=''
      let operario=this.operario

      let indexActual = this.getIndexProceso(estadoactual)
      let indexNuevo  = this.getIndexProceso(estado)
      let indexControl = this.getIndexProceso('Corte material')

      // If we can't find the indices, we might have a name mismatch.
      // We should try to proceed if we have valid strings, but the auth logic depends on indices.
      if (indexActual === -1 || indexNuevo === -1) {
        console.warn('Proceso no encontrado en la lista de procesos:', { estadoactual, estado });
        // Fallback: If simply not found, allow change but warn (unless critical logic depends on it)
        // Check if we are blocking because of this
      }
     
      // Only apply auth restriction if we successfully resolved all indices
      if (indexActual !== -1 && indexNuevo !== -1 && indexControl !== -1) {
          if (indexActual > indexControl && indexNuevo<=indexControl && !this.auth) {
            this.pendienteCambio = { id, estadoactual, estado }
            this.mostrarModal = { id, estado }
            return
          }
      }

      // 🔄 Determinar tipo
      if (indexActual !== -1 && indexNuevo !== -1 && indexControl !== -1) {
        if (indexActual < indexNuevo && indexNuevo > indexControl) {
            tipo = 'salida'
        } 
        else if (indexActual > indexNuevo && indexNuevo <=indexControl) {
            tipo = 'entrada'
        }
      }

      if (Array.isArray(me.orden.detalles)) {
        me.orden.detalles.forEach(e => {
          if ((e.titulo === 'Papel' || e.titulo === 'papel') && e.costo && e.costo.cantidad !== undefined && e.costo.cantidad !== null) {
            costois_id = e.costo.costois_id || 0;
            let numCant = parseFloat(e.costo.cantidad);
            cantidad = !isNaN(numCant) ? numCant.toFixed(0) : (e.costo.cantidad || 0);
          }
        });
      }
      
      // Update locally immediately for responsiveness
      const estadoAnterior = me.orden.status.estado;
      me.orden.status.estado = estado

      try {
        const response = await axios.put(me.getUrl('/statuspro/cambiarEstado'), {
          id,
          orden_id: orden.id, // Needed for backend fallback if status doesn't exist
          estado,
          costois_id,
          cantidad,
          cantidad_final,
          observaciones,
          tipo,
          indexControl,
          estadoactual,
          operario,
        })
        if (estado === 'Terminado' && orden.terminado && orden.terminado.length > 0 && orden.terminado[0].costois_id > 0 && me.valorTerminadoTemp !== undefined) {
           await axios.post(me.getUrl('/statuspro/actualizarValorTerminado'), {
             orden_id: orden.id,
             valor: me.valorTerminadoTemp
           });
           me.valorTerminadoTemp = undefined;
        }
        me.CambiarPrioridad(orden)
        console.log( response.data)
        me.cerrarmodal()
        me.auth=false
        me.archivo = ''
      } catch (error) {
        console.error('❌ Error al cambiar estado:', error)
        // Revert on error
        me.orden.status.estado = estadoAnterior;
        let msg = "Error al cambiar de estado.";
        if(error.response && error.response.data && error.response.data.error){
            msg += " " + error.response.data.error;
        } else if (error.response && error.response.data && error.response.data.message){
             msg += " " + error.response.data.message;
        }
        alert(msg);
      }
      // Emit refresh to parent but signal it's a local update
      me.$emit('refrescar', 'local')
    },
      
    
    onAutorizado(value) {
      this.mostrarModal = null
      this.auth = value

      if (value && this.pendienteCambio) {
        const { id, estadoactual, estado } = this.pendienteCambio
        this.pendienteCambio = null
        // 👇 Aquí ya auth=true, así que no reabrirá el modal
        this.cambiarEstado(this.orden,id, estadoactual, estado)
      }
    },

   async validarClave(clave) {
      if(clave){
        return true
      }else{
        return false
      }
      try {
        const res = await axios.post('/api/validar-clave', { clave })
        return res.data.valida
      } catch (e) {
        console.error('Error validando clave', e)
        return false
      }
    },
    cancelarMovimiento() {
      // Revertir el estado y cerrar el modal
      this.cerrarModal();
    },
    cerrarModal() {
      this.mostrarModal = false;
      this.accionPendiente = null;
    },
    generarExcelOrdenes(orden){
      orden.idorden=orden.id
      orden.rasonsocial=orden.cliente.razonsocial
      var ordenes = new Array()
      ordenes.push(orden)
          let me=this;
          var url= 'orden/generarReporteOrdenes'
          axios.post(url,{
              'datos':JSON.stringify(ordenes),
          }).then(function (response) {
              let  respuesta = response.data;
              me.archivo=response.data
          })
          .catch(function (error) {
              console.log(error);
          });
      },
      generarGuia(datosenvio,cliente){
              let me=this;
              var url= '/generarGuia'
              if(datosenvio.empresa=='' || datosenvio.empresa==null){
                  datosenvio.empresa=cliente;
              }
              var datos=encodeURIComponent(JSON.stringify(datosenvio))
              axios({
              url: '/generarGuia?guia='+datos,         
              method: 'GET',
              responseType: 'blob', // important
              }).then((response) => {
                  const url = window.URL.createObjectURL(new Blob([response.data]));
                  const link = document.createElement('a');
                  link.href = url;
                  link.setAttribute('download', 'guia '+datosenvio.empresa+'.pdf');
                  document.body.appendChild(link);
                  link.click();
              });
          },
    
        
          agregarCosto(costo,index,detalle,seccion){
                let me=this
                if (!detalle) detalle = me.objeto || {};
                var tamanos=parseFloat(me.orden.cantidad / (me.orden.cabida || 1)) + parseInt(me.orden.carpeta_cliente || 0)
                var pliegos= (me.orden.tamano && me.orden.tamano != 0) ? tamanos / me.orden.tamano : tamanos

                if (detalle) {
                    this.$set(detalle, 'nombre', costo.nombre);
                    this.$set(detalle, 'costois_id', costo.id);
                    if (detalle.costo) {
                        this.$set(detalle.costo, 'costois', costo);
                        this.$set(detalle.costo, 'costois_id', costo.id);
                        this.$set(detalle.costo, 'descripcion', costo.nombre);
                    }
                    if (detalle.id && Array.isArray(me.orden.costos)) {
                        let cMatch = me.orden.costos.find(c => c.id === detalle.id);
                        if (cMatch) {
                            cMatch.costois_id = costo.id;
                            cMatch.costois = costo;
                            cMatch.descripcion = costo.nombre;
                        }
                    }
                }

                if (detalle && (detalle.titulo == 'Papel' || (costo && costo.tipo_costo == 'Papel'))){
                    detalle.valor = costo.descripcion ? costo.descripcion : costo.nombre;
                } else if (detalle) {
                    detalle.valor = costo.nombre;
                }

                if (seccion && seccion.includes('modificar')){
                  var cost={
                        titulo:costo.tipo_costo,
                        costois:costo,
                        costois_id:costo.id,
                        valor:costo.valor,
                        cantidad:pliegos,
                        orden:1,
                        descripcion:tamanos,
                        completado:0,
                        terminado:0,
                    }
                  this.$set(detalle, 'costo', cost)
                  detalle.titulo=costo.tipo_costo
                }
                    
                me.modali=0
          },
     eliminarCosto(index,costo){
        let me=this
        var userj=me.user
        if(costo){
          me.orden.costos.splice(index,1)
          var url= '/costo/borrar?id='+costo.idcosto+'&user_id='+userj.id+'&_method=DELETE';
            axios.delete(url).then(function (response) {
                var respuesta= response.data;
                me.$emit('refrescar', 'local')
            })
            .catch(function (error) {
                console.log(error);
            });
            
        }else{
            me.orden.costos.splice(index,1)
            me.$emit('refrescar', 'local')
        }
    },
     modificarDetalle(insumo,detalle){
          detalle.titulo=insumo.tipo_costo
          detalle.costo_id=insumo.id
          detalle.costo=insumo

      },
    getUrl(path) {
      let cleanPath = path.startsWith('/') ? path : '/' + path;
      let base = window.location.pathname.replace(/\/main.*/i, '').replace(/\/+$/, '');
      return base + cleanPath;
    },
    guardarMaterial(orden,detalle){
      var me=this
      const orden1 = new FormData()
      orden1.set('id',orden.id)
      orden1.set('medida_final',orden.medida_final)
      orden1.set('medida_material',orden.medida_material)
      orden1.set('tamano',orden.tamano)
      orden1.set('cabida',orden.cabida)
      orden1.set('carpeta_cliente',orden.carpeta_cliente)
      orden1.set('detalle',JSON.stringify(detalle))
      axios.post(me.getUrl('/statuspro/guardarMaterial'),orden1)
      .then(function (response) {
        console.log(response)
          me.opcionproduccion=0   
          me.editardetalles=0   
          me.cerrarmodal()
          me.archivo='' 
          me.buscar_insumo=''
          Swal.fire({
            title: '¡Material Guardado!',
            text: 'Las especificaciones del material se guardaron correctamente.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
          });
          me.$emit('refrescar', 'local')  
        }).catch(function (error) {
          console.log(error);
          Swal.fire('Error', 'No se pudo guardar el material.', 'error');
      });
    },
    guardarOpciones(orden,seccion){
      var me=this
      const orden1 = new FormData()
      orden1.set('id',orden.id)
      orden1.set('medida_material',orden.medida_material)
      orden1.set('tamano',orden.tamano)
      orden1.set('cabida',orden.cabida)
      orden1.set('carpeta_cliente',orden.carpeta_cliente)
      orden1.set('observaciones',orden.observaciones)
      orden1.set('seccion',seccion)
      orden1.set('costo',JSON.stringify(this.orden.costos))
      orden1.set('detalles',JSON.stringify(this.orden.detalles))
      axios.post(me.getUrl('/statuspro/guardarOpciones'),orden1)
      .then(function (response) {
        console.log(response)
          me.opcionproduccion=0   
          me.editardetalles=0   
          me.cerrarmodal()
          me.archivo='' 
          me.buscar_insumo=''
          Swal.fire({
            title: '¡Cambios Guardados!',
            text: 'Se guardaron las especificaciones y detalles correctamente.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
          });
          me.$emit('refrescar', 'local')  
        }).catch(function (error) {
          console.log(error);
          Swal.fire('Error', 'No se pudieron guardar los detalles.', 'error');
      });
    },
    abrirOpcionePro(){
        this.cargarListaPapeles();
        this.opcionproduccion=1;
    },
    cargarListaPapeles() {
      if (!this.orden) {
        this.listaPapeles = [];
        return;
      }
      let result = [];
      let detallesPapel = Array.isArray(this.orden.detalles) 
        ? this.orden.detalles.filter(d => d.titulo && d.titulo.toLowerCase() === 'papel')
        : [];
      let costosPapel = Array.isArray(this.orden.costos) 
        ? this.orden.costos.filter(c => c.titulo && c.titulo.toLowerCase() === 'papel')
        : [];

      if (detallesPapel.length > 0) {
        detallesPapel.forEach((d, idx) => {
          let costo = d.costo || (costosPapel[idx] ? costosPapel[idx] : null);
          let nombre = (costo && costo.costois && costo.costois.nombre && costo.costois.nombre !== '') 
            ? costo.costois.nombre 
            : (d.valor && d.valor !== '' ? d.valor : (costo && costo.descripcion ? costo.descripcion : 'Papel'));
          
          let pliegos = costo ? (parseFloat(costo.cantidad) || this.cantidadPliegos) : this.cantidadPliegos;
          let corte = (costo && costo.medida_material) ? costo.medida_material : (this.orden.medida_material || 'N/A');
          let tamano = (costo && costo.tamano) ? costo.tamano : (this.orden.tamano || 'N/A');
          let cabida = (costo && costo.cabida) ? costo.cabida : (this.orden.cabida || '1');
          let sobrante = (costo && costo.sobrante) ? costo.sobrante : (this.orden.carpeta_cliente || '0');
          let componente = (costo && costo.componente) ? costo.componente : '';
          let medidaFinal = (costo && costo.medida_final) ? costo.medida_final : (this.orden.medida_final || 'N/A');
          let tamanosSobrante = costo ? (parseFloat(costo.descripcion) || this.cantidadTamanos) : this.cantidadTamanos;

          result.push({
            id: costo ? costo.id : d.id,
            costos_id: costo ? costo.id : 0,
            detalle_id: d.id,
            costois_id: costo ? costo.costois_id : 0,
            nombre: nombre,
            componente: componente,
            pliegos: Math.round(pliegos * 100) / 100,
            corte: corte,
            tamano: tamano,
            cabida: cabida,
            sobrante: sobrante,
            medidaFinal: medidaFinal,
            tamanosSobrante: Math.round(tamanosSobrante * 100) / 100
          });
        });
      } else if (costosPapel.length > 0) {
        costosPapel.forEach(c => {
          let nombre = (c.costois && c.costois.nombre) ? c.costois.nombre : (c.descripcion || 'Papel');
          let pliegos = parseFloat(c.cantidad) || this.cantidadPliegos;
          let corte = c.medida_material || this.orden.medida_material || 'N/A';
          let tamano = c.tamano || this.orden.tamano || 'N/A';
          let cabida = c.cabida || this.orden.cabida || '1';
          let sobrante = c.sobrante || this.orden.carpeta_cliente || '0';
          let componente = c.componente || '';
          let medidaFinal = c.medida_final || this.orden.medida_final || 'N/A';
          let tamanosSobrante = parseFloat(c.descripcion) || this.cantidadTamanos;

          result.push({
            id: c.id,
            costos_id: c.id,
            detalle_id: 0,
            costois_id: c.costois_id || 0,
            nombre: nombre,
            componente: componente,
            pliegos: Math.round(pliegos * 100) / 100,
            corte: corte,
            tamano: tamano,
            cabida: cabida,
            sobrante: sobrante,
            medidaFinal: medidaFinal,
            tamanosSobrante: Math.round(tamanosSobrante * 100) / 100
          });
        });
      } else {
        result.push({
          id: 0,
          nombre: 'No asignado',
          componente: '',
          pliegos: Math.round(this.cantidadPliegos * 100) / 100,
          corte: this.orden.medida_material || 'N/A',
          tamano: this.orden.tamano || 'N/A',
          cabida: this.orden.cabida || '1',
          sobrante: this.orden.carpeta_cliente || '0',
          medidaFinal: this.orden.medida_final || 'N/A',
          tamanosSobrante: Math.round(this.cantidadTamanos * 100) / 100
        });
      }

      // Autoselect highest cabida if quantity > 1000 and cabidas exist
      if (parseFloat(this.orden.cantidad) > 1000 && this.cabidasArticulo.length > 0) {
        let maxCab = this.cabidasArticulo.reduce((max, c) => (parseFloat(c.cabida) > parseFloat(max.cabida) ? c : max), this.cabidasArticulo[0]);
        result.forEach(pItem => {
          if (!pItem.corte || pItem.corte === 'N/A' || parseFloat(pItem.cabida) === 1) {
            this.onSeleccionarCabidaMaterial(pItem, maxCab);
          }
        });
      }

      this.listaPapeles = result;
    },
    calcularSobranteAuto(cantidad, cabida) {
      const cant = parseFloat(cantidad) || 0;
      const cab = parseFloat(cabida) || 1;
      const cantidadMaterial = cab > 0 ? (cant / cab) : cant;
      
      let numTintas = 0;
      if (this.orden && Array.isArray(this.orden.detalles)) {
        this.orden.detalles.forEach(d => {
          let tit = (d.titulo || '').toLowerCase();
          if (tit.includes('tinta') || tit.includes('impres') || tit.includes('color')) {
            let val = d.valor;
            if (Array.isArray(val)) {
              numTintas += val.length;
            } else if (typeof val === 'string' && val.trim().startsWith('[')) {
              try {
                let parsed = JSON.parse(val);
                if (Array.isArray(parsed)) numTintas += parsed.length;
                else if (parsed) numTintas += 1;
              } catch(e) {
                if (val.trim() && val !== '[]') numTintas += 1;
              }
            } else if (val && val !== '0' && val !== '[]') {
              numTintas += 1;
            }
          }
        });
      }
      if (numTintas < 1) numTintas = 1;

      const sobranteBase = 50 + (numTintas - 1) * 25;
      const sobranteTiraje = Math.round(cantidadMaterial * 0.01);
      return Math.max(50, Math.round(sobranteBase + sobranteTiraje));
    },
    onSeleccionarCabidaMaterial(pItem, cabidaObj) {
      if (!pItem || !cabidaObj) return;
      this.$set(pItem, 'cabida', cabidaObj.cabida);
      this.$set(pItem, 'corte', cabidaObj.medida_material);
      let tam = this.calcularTamanoFromMedida(cabidaObj.medida_material);
      this.$set(pItem, 'tamano', tam);

      const nuevoSobrante = this.calcularSobranteAuto(this.orden.cantidad, cabidaObj.cabida);
      this.$set(pItem, 'sobrante', nuevoSobrante);
      this.orden.carpeta_cliente = nuevoSobrante;

      this.recalcularPapelItem(pItem);
    },
    calcularTamanoFromMedida(medida) {
      if (!medida || !medida.includes('x')) return 1;
      let parts = medida.split('x').map(Number);
      if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return 1;
      let cutW = parts[0], cutH = parts[1];
      let pliegoW = 70, pliegoH = 100;
      let solve = (W, H, cW, cH) => {
        let count1 = Math.floor(W / cW);
        let count2 = Math.floor(W / cH);
        let best = 0;
        for (let n1 = 0; n1 * cH <= H; n1++) {
          let n2 = Math.floor((H - n1 * cH) / cW);
          best = Math.max(best, (n1 * count1) + (n2 * count2));
        }
        return best;
      };
      let c1 = solve(pliegoW, pliegoH, cutW, cutH);
      let c2 = solve(pliegoH, pliegoW, cutW, cutH);
      let res = Math.max(c1, c2);
      return res > 0 ? res : 1;
    },
    abrirEditarDetalles(){
        this.editardetalles=1
    },
    cerrarOpcionePro(){
        this.opcionproduccion=0
    },

    cerrarEditarDetalles(){
        this.editardetalles=0
    },
    recalcularPapelItem(pItem) {
      if (!pItem) return;
      const cantidad = parseFloat(this.orden.cantidad) || 0;
      const cabida = parseFloat(pItem.cabida) || 1;
      
      if (!pItem.sobrante || parseFloat(pItem.sobrante) === 0) {
        const autoSob = this.calcularSobranteAuto(cantidad, cabida);
        this.$set(pItem, 'sobrante', autoSob);
        this.orden.carpeta_cliente = autoSob;
      }

      const sobrante = parseFloat(pItem.sobrante) || 0;
      const tamano = parseFloat(pItem.tamano) || 1;

      const cantTamanos = (cabida !== 0) ? (cantidad / cabida) + sobrante : sobrante;
      const pliegos = (tamano !== 0) ? cantTamanos / tamano : 0;

      this.$set(pItem, 'tamanosSobrante', Math.round(cantTamanos * 100) / 100);
      this.$set(pItem, 'pliegos', Math.round(pliegos * 100) / 100);
    },
    recalcularPorTamanos(pItem) {
      if (!pItem) return;
      const cantTamanos = parseFloat(pItem.tamanosSobrante) || 0;
      const tamano = parseFloat(pItem.tamano) || 1;
      const pliegos = tamano !== 0 ? cantTamanos / tamano : 0;
      this.$set(pItem, 'pliegos', Math.round(pliegos * 100) / 100);
    },
    recalcularPorPliegos(pItem) {
      if (!pItem) return;
      const pliegos = parseFloat(pItem.pliegos) || 0;
      const tamano = parseFloat(pItem.tamano) || 1;
      const cantTamanos = pliegos * tamano;
      this.$set(pItem, 'tamanosSobrante', Math.round(cantTamanos * 100) / 100);
    },
     cerrarModali(){
          this.modali=0
          this.arrayInsumos=[]
      },
    guardarOpcionesMateriales(orden) {
      var me = this;
      const data = new FormData();
      data.set('id', orden.id);
      data.set('medida_final', orden.medida_final || '');

      if (me.listaPapeles && me.listaPapeles.length > 0) {
        data.set('medida_material', me.listaPapeles[0].corte || '');
        data.set('tamano', me.listaPapeles[0].tamano || '');
        data.set('cabida', me.listaPapeles[0].cabida || '');
        data.set('carpeta_cliente', me.listaPapeles[0].sobrante || '');

        orden.medida_material = me.listaPapeles[0].corte;
        orden.tamano = me.listaPapeles[0].tamano;
        orden.cabida = me.listaPapeles[0].cabida;
        orden.carpeta_cliente = me.listaPapeles[0].sobrante;
      }

      data.set('papeles', JSON.stringify(me.listaPapeles));

      axios.post(me.getUrl('/statuspro/guardarMaterial'), data)
        .then(function (response) {
          me.opcionproduccion = 0;
          me.editardetalles = 0;
          Swal.fire({
            title: '¡Materiales Actualizados!',
            text: 'Se guardaron las especificaciones de los materiales.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
          });
          me.$emit('refrescar', 'local');
        }).catch(function (error) {
          console.error(error);
          Swal.fire('Error', 'No se pudieron guardar los materiales', 'error');
        });
    },
    abrirmodal(){
      let me = this;
      me.modal = 1;
      me.cargarListaPapeles();
      me.$emit('modal-opened', me.orden.id);
      
      // Auto load operarias list when modal is opened
      me.tipoactivo = 'Terminado';
      me.abrirOpciones();
      
      // Initialize suggest quantity for new operaria
      me.nuevaOperariaCantidad = Math.max(0, parseFloat(me.orden.cantidad) - me.totalAsignadoTerminado);

      // Initialize dividing order switch
      if (me.orden.terminado && me.orden.terminado.length > 1) {
        me.dividirOrden = true;
      } else {
        me.dividirOrden = false;
      }
    },
    cerrarmodal(){
      this.modal = 0;
      this.$emit('modal-closed', this.orden.id);
      this.nuevaOperariaSelected = null;
      this.nuevaOperariaCantidad = 0;
      this.nuevaOperariaValor = 0;
      this.dividirOrden = false;
    },
    eliminarDetalle(index){
        let me=this
        var userj=me.user
          if(me.orden.detalles[index].id>0){
            me.cambios= ('Eliminado detalle Titulo: '+me.orden.detalles[index].titulo_detalle+' - Detalle: '+me.orden.detalles[index].valor+' - Descripcion: '+me.orden.detalles[index].descripcion+', ')+me.cambios
            var url= '/detalle/borrar?id='+ me.orden.detalles[index].id + '&user_id='+userj.id+'&_method=DELETE';
            axios.delete(url).then(function (response) {
                var respuesta= response.data;
                me.orden.detalles.splice(index,1)
                me.$emit('refrescar', 'local')
            })
            .catch(function (error) {
                console.log(error);
            });
            
        }else{
            me.orden.detalles.splice(index,1)
            me.$emit('refrescar', 'local')
        }
        
        
    },
    selectInsumos(event,objeto){
        let me=this;
        me.objeto=objeto;
        me.seccion='modificar';
        let valor = event && event.target ? event.target.value : (typeof event === 'string' ? event : '');
        if(!valor || valor.length < 2) {
            me.modali = 0;
            return;
        }
        if (me.searchInsumoTimer) clearTimeout(me.searchInsumoTimer);
        me.searchInsumoTimer = setTimeout(() => {
            var url= me.getUrl('/costop/selectInsumos?filtro='+encodeURIComponent(valor));
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.arrayInsumos=respuesta.insumos || [];
                if(me.arrayInsumos.length > 0) {
                   me.modali=1;
                } else {
                   me.modali=0;
                }
            })
            .catch(function (error) {
                console.log(error);
            });
        }, 250);
    },
    asignarActivo(orden){
      var me=this
      
      if (me.tipoactivo === 'Terminado') {
        let promise;
        if (orden.cuenta_cobro && orden.cuenta_cobro.id) {
          promise = Swal.fire({
            title: 'Actualizar Cuenta de Cobro',
            text: `Esta orden ya tiene una cuenta de cobro asignada. ¿Desea actualizar la cuenta de cobro con la nueva operaria (${me.activoasignado.activo})?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Sí, actualizar cuenta',
            cancelButtonText: 'No, mantener cuenta anterior'
          }).then((res) => {
            if (res.value) {
              return { confirmed: true, actualizar: true };
            } else if (res.dismiss === 'cancel') {
              return { confirmed: true, actualizar: false };
            } else {
              return { confirmed: false };
            }
          });
        } else {
          promise = Promise.resolve({ confirmed: true, actualizar: true });
        }

        promise.then((decision) => {
          if (!decision.confirmed) {
            me.activoasignado = {};
            return;
          }
          
          let actualizarCuenta = decision.actualizar;
          
          Swal.fire({
            title: 'Valor Unitario de Terminado',
            text: `Ingrese el valor unitario para ${me.activoasignado.activo}:`,
            input: 'number',
            inputValue: (orden.terminado && orden.terminado.length > 0) ? orden.terminado[0].valor : '',
            inputAttributes: {
              min: 0,
              step: 'any'
            },
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
              if (!value || value < 0) {
                return 'Debe ingresar un valor válido igual o mayor a 0'
              }
            }
          }).then((result) => {
            if (result.value) {
              let valorUnitario = parseFloat(result.value);
              me.ejecutarAsignarActivo(orden, valorUnitario, actualizarCuenta);
            } else {
              me.activoasignado = {};
            }
          });
        });
      } else {
        me.ejecutarAsignarActivo(orden, null, true);
      }
    },
    ejecutarAsignarActivo(orden, valorUnitario, actualizarCuenta) {
      var me=this
      const activo = new FormData()
      activo.set('ordenid',orden.id)
      activo.set('activo',JSON.stringify(me.activoasignado))
      axios.post('/statuspro/guardaractivo',activo)
      .then(function (response) {
          if (valorUnitario !== null) {
              axios.post('/statuspro/actualizarValorTerminado', {
                orden_id: orden.id,
                valor: valorUnitario,
                actualizar_cuenta: actualizarCuenta
              })
              .then((resp) => {
                if (resp.data && resp.data.success) {
                  me.actualizarTerminadoLocal(resp.data.terminado);
                }
                me.$emit('refrescar', 'local')
              });
          } else {
              if (response.data && response.data.success) {
                if (response.data.tipo === 'Troquelado') {
                  me.orden.troquelado = response.data.troquelado;
                } else if (response.data.tipo === 'Terminado') {
                  me.actualizarTerminadoLocal(response.data.terminado);
                }
              }
              me.$emit('refrescar', 'local')  
          }
          me.activoasignado={}
        }).catch(function (error) {
          console.log(error);
      });
    },
    abrirOpciones(){
      let me=this;
      var url= '/activo/portipo?tipo='+me.tipoactivo
      axios.get(url).then(function (response) {
        console.log(response)
          var respuesta= response.data
          me.arrayactivos = respuesta
         
      })
      .catch(function (error) {
          console.log(error);
      });
    },
    cambiarPlancha(){
      var me=this;
      
      // Local calculation (Optimistic UI update)
      const plancha = me.orden.planchas.find(item => item.id === me.orden.plancha);
      let numero = 0;
      if (plancha && plancha.detalles) {
          let match = plancha.detalles.match(/[\d.]+/);
          numero = match ? parseFloat(match[0]) : 0;
      }
      if (numero > 0) {
          me.orden.cabida = numero;
          me.orden.detalles.forEach(e => {
            if (e.titulo === 'Papel' && e.costo != null) {
              const cantidad = parseFloat(me.orden.cantidad) || 0;
              const carpetaCliente = parseFloat(me.orden.carpeta_cliente) || 0;
              const anchoMaterial = parseFloat(me.orden.tamano) || 1;
              
              let desc = (numero !== 0) ? (cantidad / numero) + carpetaCliente : carpetaCliente;
              let cant = (numero !== 0 && anchoMaterial !== 0) ? desc / anchoMaterial : 0;
              
              me.$set(e.costo, 'descripcion', desc);
              me.$set(e.costo, 'cantidad', cant);
            }
          });
      }
      
      axios.put('/statuspro/cambiarPlancha',{
          'id':me.orden.plancha,
          'id_orden':me.orden.id,
      })
      .then(function (response) {
        me.$emit('refrescar', 'local')  
        }).catch(function (error) {
          console.log(error);
      });
      
    },
    

      verificarEnvio(){
        var me=this
        var idorden=this.orden.idorden
        axios.get('/statuspro/verificarEnvio',{
            'id':idorden,
        })
        .then(function (response) {
          me.orden.envio=response.data+'456'
                  
        }).catch(function (error) {
            console.log(error);
        });
        
      },
      cambiarDatosEstado(or){
        var me=this
        me.orden.status.estado=or.status.estado
        axios.put('/statuspro/cambiarDatosEstado',{
            'orden':or,
            'id':or.id,
            'observaciones':me.orden.observaciones,
        })
        .then(function (response) {
          me.modal=0
          me.archivo=''
          me.$emit('refrescar', 'local')      
          }).catch(function (error) {
            console.log(error);
        });
        
      },
      abrirModalEntrega() {
            this.modalEntrega = 1;
            this.cantidadEntrega = this.orden.cantidad - (this.orden.cantidad_entregada || 0);
            this.tipoDocumentoEntrega = 'Remision';
            this.observacionesEntrega = '';
      },
      cerrarModalEntrega() {
            this.modalEntrega = 0;
      },
      registrarEntrega() {
            let me = this;
            if (this.cantidadEntrega <= 0) {
            alert("La cantidad debe ser mayor a 0");
            return;
            }
            
            // Se permite que la cantidad a entregar supere el saldo pendiente. El backend ajustará el pedido automáticamente.

            this.loadingEntrega = true;
            axios.post('/statuspro/registrarEntrega', {
                ordentrabajo_id: this.orden.id,
                cantidad: this.cantidadEntrega,
                tipo_documento: this.tipoDocumentoEntrega,
                observaciones: this.observacionesEntrega
            })
            .then(function (response) {
                me.loadingEntrega = false;
                me.modalEntrega = 0;
                
                if (response.data.success) {
                    // Si hay documento, abrirlo
                    if (me.tipoDocumentoEntrega !== 'Ninguno' && response.data.entrega_id) {
                    window.open('/statuspro/entregaPdf/' + response.data.entrega_id, '_blank');
                    }
                    
                    me.$emit('refrescar', 'local');
                    alert(response.data.message);
                }
            })
            .catch(function (error) {
                me.loadingEntrega = false;
                console.error(error);
                alert((error.response && error.response.data && error.response.data.error) || "Error al registrar la entrega");
        });
      },
      buscarPedidos() {
          let me = this;
          if (me.buscarPedidoText.length < 2) return;
          
          let url = '/comprobante?buscar=' + me.buscarPedidoText + '&criterio=num_comprobante&per_page=10';
          axios.get(url).then(response => {
              me.arrayPedidosAsignar = response.data.data ? response.data.data : response.data.comprobantes.data;
          }).catch(err => console.error(err));
      },
      confirmarReasignacion() {
          if (!this.pedidoDestinoId) return;
          
          Swal.fire({
              title: '¿Reasignar esta orden?',
              text: "La orden cambiará de cliente al del pedido destino y se recalcularán los totales.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Sí, reasignar'
          }).then((result) => {
              if (result.value) {
                  this.ejecutarReasignacion();
              }
          });
      },
      ejecutarReasignacion() {
          let me = this;
          let url = '/orden/reasignarPedido';
          axios.post(url, {
              orden_id: me.orden.id,
              pedido_id: me.pedidoDestinoId
          }).then(response => {
              Swal.fire('¡Éxito!', response.data.message, 'success');
              me.cerrarmodal();
              me.$emit('refrescar');
          }).catch(error => {
              Swal.fire('Error', error.response.data.error || 'Ocurrió un error', 'error');
          });
      },
      guardarNuevaOperaria() {
        const me = this;
        if (!me.nuevaOperariaSelected) {
          Swal.fire('Error', 'Debe seleccionar una operaria.', 'error');
          return;
        }
        if (me.nuevaOperariaCantidad <= 0) {
          Swal.fire('Error', 'La cantidad debe ser mayor a 0.', 'error');
          return;
        }
        if (me.nuevaOperariaValor <= 0) {
          Swal.fire('Error', 'El valor unitario debe ser mayor a 0.', 'error');
          return;
        }

        axios.post('/statuspro/asignarOperariaTerminado', {
          orden_id: me.orden.id,
          activo_id: me.nuevaOperariaSelected.id,
          cantidad: me.nuevaOperariaCantidad,
          valor: me.nuevaOperariaValor
        })
        .then(response => {
          if (response.data.success) {
            me.actualizarTerminadoLocal(response.data.terminado);
            Swal.fire({
              title: 'Asignado',
              text: 'Operaria asignada con éxito.',
              icon: 'success',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 2000
            });
            me.nuevaOperariaSelected = null;
            me.nuevaOperariaCantidad = 0;
            me.nuevaOperariaValor = 0;
            me.$emit('refrescar', 'local');
          }
        })
        .catch(error => {
          console.error(error);
          const msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo asignar la operaria.';
          Swal.fire('Error', msg, 'error');
        });
      },
      actualizarOperaria(term) {
        const me = this;
        const cant = term.cantidad;
        const val = term.valor;
        const cantEntregada = term.cuenta_cobro ? term.cuenta_cobro.cantidad_entregada : 0;

        if (cant <= 0 || val <= 0) {
          Swal.fire('Error', 'La cantidad y el valor unitario deben ser mayores a 0.', 'error');
          return;
        }
        if (cantEntregada > cant) {
          Swal.fire('Error', 'La cantidad entregada no puede ser mayor a la cantidad asignada.', 'error');
          return;
        }

        axios.post('/statuspro/actualizarOperariaTerminado', {
          costo_id: term.id,
          cantidad: cant,
          valor: val,
          cantidad_entregada: cantEntregada
        })
        .then(response => {
          if (response.data.success) {
            me.actualizarTerminadoLocal(response.data.terminado);
            Swal.fire({
              title: 'Actualizado',
              text: 'Asignación actualizada con éxito.',
              icon: 'success',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 2000
            });
            me.$emit('refrescar', 'local');
          }
        })
        .catch(error => {
          console.error(error);
          const msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo actualizar la asignación.';
          Swal.fire('Error', msg, 'error');
        });
      },
      eliminarOperaria(term) {
        const me = this;
        Swal.fire({
          title: '¿Eliminar Asignación?',
          text: `¿Está seguro de eliminar a ${term.activo ? term.activo.activo : 'la operaria'} de esta orden?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.value) {
            axios.post('/statuspro/eliminarOperariaTerminado', {
              costo_id: term.id
            })
            .then(response => {
              if (response.data.success) {
                me.actualizarTerminadoLocal(response.data.terminado);
                Swal.fire({
                  title: 'Eliminado',
                  text: 'Asignación eliminada con éxito.',
                  icon: 'success',
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: 2000
                });
                me.$emit('refrescar', 'local');
              }
            })
            .catch(error => {
              console.error(error);
              const msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo eliminar la asignación.';
              Swal.fire('Error', msg, 'error');
            });
          }
        });
      }

  },
  beforeMount() {
    this.getEntregaColor()
  }
    
    
}
</script>
<style scoped>
    .shared-card {
      border: 2px dashed #ffc107 !important;
      background-color: #fffdf0 !important;
    }
    .rasonsocial{
      text-transform: uppercase;
      font-size: 13px;
    } 
    .pago{
      background:rgb(238, 154, 84) !important;
      color: black;
    }
   
     .card .estadoPago, .estadoPago {
         background: rgb(183, 228, 193);
         margin-bottom: 1px;
         position: relative !important;
         overflow: visible !important;
     }

     /* Floating 3D Diagonal Capsule VIP Sticker (50% smaller) */
     .vip-sticker-badge {
         position: absolute !important;
         bottom: -3px !important;
         right: -6px !important;
         background: linear-gradient(135deg, #FFE066 0%, #FFB703 45%, #FB8500 90%) !important;
         color: #111111 !important;
         border: 2px solid #FFFFFF !important;
         outline: 1.5px solid #E3A008 !important;
         border-radius: 50px !important;
         padding: 1px 8px 1px 5px !important;
         font-family: 'Arial Black', Impact, sans-serif !important;
         font-weight: 900 !important;
         font-size: 11px !important;
         letter-spacing: 0.8px !important;
         box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35), 0 1px 3px rgba(0, 0, 0, 0.2) !important;
         transform: rotate(-25deg) scale(1) !important;
         transform-origin: center center !important;
         z-index: 100 !important;
         display: flex !important;
         align-items: center !important;
         justify-content: center !important;
         gap: 2px !important;
         pointer-events: none !important;
         user-select: none !important;
         animation: vipStickerGlow 2.5s infinite ease-in-out !important;
     }

     /* Compact NUEVO CLIENTE Badge (Aligned right on date line where purple line was drawn) */
     .nuevo-inline-badge {
         background: linear-gradient(135deg, #00E676 0%, #00B0FF 100%) !important;
         color: #002B19 !important;
         border: 1.5px solid #FFFFFF !important;
         outline: 1px solid #00B0FF !important;
         border-radius: 12px !important;
         padding: 1px 7px !important;
         font-family: 'Arial Black', Impact, sans-serif !important;
         font-weight: 900 !important;
         font-size: 10px !important;
         letter-spacing: 0.5px !important;
         box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25) !important;
         display: inline-flex !important;
         align-items: center !important;
         gap: 2px !important;
         line-height: 1.3 !important;
         user-select: none !important;
     }
    
    /* MODAL CUSTOM STYLES */
    .modal-backdrop-custom {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1050;
        overflow: hidden;
    }

    .modal-dialog-custom {
        width: 95%;
        max-width: 1200px;
        margin: auto;
        padding: 20px;
        display: flex;
        align-items: center; 
        justify-content: center;
    }
    
    .modal-content-custom {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        display: flex;
        flex-direction: column;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        /* Default scale is 1, handled by Vue style binding */
        transition: transform 0.2s ease; 
        border: 1px solid rgba(0,0,0,.2);
        font-size: 14px; /* Base font size standardization */
    }

    .contenedor-header{
      background: #1985ac; /* Modern Blue */
      color: white;
      padding: 15px;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
    }

    .contenedor-header h4 {
        font-size: 18px; /* Slightly larger for main title */
    }

    .contenedor-seccion .btn-cambio-estado {
      display: flex;
      flex-wrap: wrap;
      background: transparent;
    }

    .close-custom {
        background: transparent;
        border: none;
        color: white;
        font-size: 1.5rem;
        line-height: 1;
        opacity: 0.8;
        cursor: pointer;
    }
    .close-custom:hover {
        opacity: 1;
    }

    .modal-body-custom {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    /* Override list styles from previous implementation */
    .card-body {
        padding: 0.25rem !important;
    }
    .ordencard ul{
      list-style: none;
      padding: 0;
      margin-bottom: 5px;
      font-size: 12px; /* Small text for card list */
      font-weight: 700;
    }

    .datosorden div{
       /* Clean up old borders */
       border: none;
       font-size: 14px; /* Standardize data display */
    }

    .detalles h5 {
        font-size: 16px; /* Section headers */
    }

    .list-group-item {
        font-size: 14px;
    }

    /* Status-specific button colors if needed, otherwise rely on Bootstrap */
    
    /* Mobile Responsive Enhancements */
    @media (max-width: 768px) {
        .ordencard {
            width: 100% !important; /* Force cards to take full width or at least more space */
            padding: 2px !important;
        }
        
        .estadoPago {
            margin: 4px 0 !important;
            border-radius: 6px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }

        .ordencard ul {
            font-size: 13px !important; /* Slightly larger for mobile */
            line-height: 1.3 !important;
        }

        .ordencard ul li {
            padding: 4px 8px !important;
            border-radius: 4px;
            margin-bottom: 2px;
        }

        .rasonsocial {
            font-size: 14px !important;
            font-weight: 800 !important;
            color: #111 !important;
            display: block;
            margin-top: 2px;
            white-space: normal !important; /* Ensure name wraps */
        }

        /* Adjusting the badges on mobile to be smaller but visible */
        .btn-sm-mobile {
            padding: 1px 4px !important;
            font-size: 10px !important;
            border-width: 1.5px !important;
        }

        .badge-p-mobile {
            font-size: 10px !important;
            padding: 3px 6px !important;
            border-radius: 50% !important;
            margin-left: 5px;
        }

        /* Modal specific mobile adjustments */
        .modal-body-custom {
            padding: 12px !important;
        }
        
        .datosorden div {
            font-size: 13px !important;
        }
        
        .items-detalles .list-group-item {
            padding: 6px 8px !important;
            font-size: 12px !important;
        }

        .contenedor-header h4 {
            font-size: 16px !important;
        }
        
        .btn-cambio-estado button {
            flex: 1 1 30%;
            font-size: 0.7rem !important;
            padding: 5px 2px !important;
        }
    }

    .mostrar {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        z-index: 2000 !important;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .mostrar .modal-dialog {
        margin-top: 12vh !important;
    }
    .badge-vip-gold {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #B8860B 100%) !important;
        color: #1a1a1a !important;
        font-weight: 900 !important;
        font-size: 11px !important;
        padding: 3px 8px !important;
        border-radius: 12px !important;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.8), 0 0 4px rgba(255, 165, 0, 0.6) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        border: 1px solid #FFF8DC !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 3px !important;
    }
</style>
