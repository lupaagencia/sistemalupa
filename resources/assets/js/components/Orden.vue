<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 1px solid #ccc; padding: 10px 15px;">
                        <i class="fa fa-align-justify"></i> <strong style="font-size: 16px;">Orden de trabajo</strong>
                    </div>
                    <!-- Listado-->

                    <template v-if="listado===1">
                        <div class="card-body" style="padding: 15px;">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <!-- Search inputs and filters row -->
                                    <div style="display: flex !important; flex-direction: row !important; flex-wrap: wrap !important; align-items: center !important; gap: 8px !important; width: 100% !important; margin-bottom: 15px !important;">
                                        <input type="text" class="form-control" style="width: 100px !important; max-width: 100px !important; display: inline-block !important; height: 35px !important; border: 1px solid #ccc !important; border-radius: 4px !important; padding: 6px 12px !important;" v-model="buscaro" @keyup="listarOrdenes(1,buscaro,'like','ordentrabajos.id')" placeholder="No. Orden">
                                        <input type="text" class="form-control" style="width: 180px !important; max-width: 180px !important; display: inline-block !important; height: 35px !important; border: 1px solid #ccc !important; border-radius: 4px !important; padding: 6px 12px !important;" v-model="buscare" @keyup="listarOrdenes(1,buscare,'like','personas.nombre')" placeholder="Empresa">
                                        <input type="text" class="form-control" style="width: 180px !important; max-width: 180px !important; display: inline-block !important; height: 35px !important; border: 1px solid #ccc !important; border-radius: 4px !important; padding: 6px 12px !important;" v-model="buscarp" @keyup="listarOrdenes(1,buscarp,'like','articulos.nombre')" placeholder="Producto">
                                        
                                        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0 !important; width: auto !important;">
                                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalIntervalo}">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Asignar Intervalo</h5>
                                                            <button @click="cerrarModalIntervalo" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div v-text="`${fechaI}-${fechaF}`"></div>
                                                            <div class="inputGroup" style="display: flex; gap: 5px; margin-top: 10px;">
                                                                <input type="date" class="form-control" placeholder="Fecha Inicial" v-model="fechaI">
                                                                <input type="date" class="form-control" placeholder="Fecha Final" v-model="fechaF">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button @click="cerrarModalIntervalo" type="button" class="btn btn-secondary">Cerrar</button>
                                                            <button @click="filtrarFecha()" type="button" class="btn btn-primary">Aplicar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <select class="form-control" style="width: 250px !important; max-width: 250px !important; display: inline-block !important; height: 35px !important; border: 1px solid #ccc !important; border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;" @change="filtrarFecha()" v-model="filtroFecha">
                                                <option value="1">Filtrar por fecha</option>   
                                                <option value="hoy">Hoy</option>   
                                                <option value="ayer">Ayer</option>  
                                                <option value="ultimos7">Ultimos 7 días</option>  
                                                <option value="ultimos30">Ultimos 30 días</option>  
                                                <option value="semana">Esta semana</option>  
                                                <option value="mes">Este mes</option>  
                                            </select>
                                            <button class="btn btn-primary" style="background-color: #6a1b9a !important; border-color: #6a1b9a !important; height: 35px !important; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; font-weight: bold !important; padding: 0 15px !important; white-space: nowrap !important; display: inline-block !important;" @click="asiganarIntervalo()">Intervalo fecha</button>
                                        </div>

                                        <button type="button" @click="mostrarDetalle('nuevo')" class="btn" style="background-color: #0df05b !important; color: white !important; border: none !important; border-radius: 4px !important; padding: 0 15px !important; font-weight: bold !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 5px !important; height: 35px !important; white-space: nowrap !important;">
                                            <i class="fa fa-plus-circle" style="font-size: 16px;"></i> Nuevo
                                        </button>
                                    </div>
                                    
                                    <!-- States Tab Bar -->
                                    <div style="display: flex; align-items: center; border: 1px solid #ccc; border-radius: 4px; overflow: hidden; width: fit-content; margin-bottom: 15px; background-color: #aec6d0;">
                                        <div style="background-color: #bde5f2; color: #136988; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;">
                                            Estado
                                        </div>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'todos')">Todos</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'D')">Diseño</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'A')">Aprobación</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'EP')">Enviar a producción</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'ENP')">En producción</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'EM')">Empacado</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold; border-right: 1px solid #ccc;" @click="filtrarOrdenes(1,'E')">Por entregar</button>
                                        <button class="btn" style="background-color: #aec6d0; color: #333; border: none; border-radius: 0; padding: 8px 15px; font-weight: bold;" @click="filtrarOrdenes(1,'T')">Terminada</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pagination Above the Table -->
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                                <select v-model="limite" @change="cambiarLimite()" class="form-control" style="width: 80px; height: 35px; border: 1px solid #ccc; border-radius: 4px; padding: 5px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                
                                <ul class="pagination" style="margin: 0; display: flex; list-style: none; padding-left: 0; gap: 5px;">
                                    <li class="page-item" v-if="pagination.current_page > 1">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)" style="color: #7b1fa2; border: 1px solid #ccc; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; background: white;">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" 
                                           :style="page == isActived ? 'background-color: #7b1fa2; border-color: #7b1fa2; color: white; font-weight: bold; border-radius: 4px; padding: 6px 12px; text-decoration: none;' : 'color: #7b1fa2; border: 1px solid #ccc; font-weight: bold; border-radius: 4px; padding: 6px 12px; text-decoration: none; background: white;'"
                                           v-text="page"></a>
                                    </li>
                                    <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)" style="color: #7b1fa2; border: 1px solid #ccc; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; background: white;">Sig</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                                                    <thead>
                                        <tr>
                                            <th>Opciones</th>
                                            <th @click="ordenar('idorden')">No. orden <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('fechaorden')">Fecha <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('fecha')">Fecha de cierre <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('articulo')">Trabajo <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('cliente')">Cliente <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('detalles_diseno')">Detalles del diseño <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('observaciones')">Observaciones <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th>Producción</th>
                                            <th @click="ordenar('estadop')">Estado Comercial y Producción <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th>Impresión orden de producción</th>
                                            <th @click="ordenar('total')">Valor orden <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('fecha_entrega')">Fecha de entrega <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayOrdenes.length">
                                        <tr v-for="(orden, index) in arrayOrdenes" :key="index">
                                            
                                            <td style="padding: 4px; vertical-align: middle;">
                                                <div style="display: flex; flex-direction: column; gap: 2px; align-items: center; justify-content: center; width: 35px; margin: 0 auto;">
                                                    <button type="button" @click="verOrden(orden)" class="btn btn-sm text-white" style="background-color: #6a1b9a; border: none; padding: 4px; width: 100%; border-radius: 2px;" title="Ver Orden">
                                                        <i class="icon-eye"></i>
                                                    </button>
                                                    <button type="button" @click="editarOrden(orden,'edit')" class="btn btn-sm text-white" style="background-color: #fbc02d; border: none; padding: 4px; width: 100%; border-radius: 2px;" title="Editar Orden">
                                                        <i class="icon-pencil"></i>
                                                    </button>
                                                    <button type="button" @click="copiarOrden(orden)" class="btn btn-sm text-white" style="background-color: #00e676; border: none; padding: 4px; width: 100%; border-radius: 2px;" title="Copiar Orden">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                    <button type="button" @click="eliminarOrden(orden.idorden || orden.id)" class="btn btn-sm text-white" style="background-color: #ff5252; border: none; padding: 4px; width: 100%; border-radius: 2px;" title="Eliminar Orden">
                                                        <i class="icon-trash"></i>
                                                    </button>
                                                    <button type="button" @click="generarOrdentrabajo(orden)" class="btn btn-sm text-white" style="background-color: #00b0ff; border: none; padding: 4px; width: 100%; border-radius: 2px;" title="Generar Orden de Trabajo">
                                                        <i class="icon-screen-desktop"></i>
                                                    </button>
                                                    <button type="button" @click="imprimirHojaRuta(orden)" class="btn btn-sm text-white" style="background-color: #5c2c74; border: none; padding: 4px; width: 100%; border-radius: 2px;" title="Hoja de Ruta (PDF)">
                                                        <i class="fa fa-list-alt"></i>
                                                    </button>
                                                    <button type="button" @click="gestionarHojaRutaEscaneada(orden)" class="btn btn-sm text-white mt-1" :style="{ backgroundColor: orden.hoja_ruta_escaneada ? '#16a34a' : '#0284c7', border: 'none', padding: '4px', width: '100%', borderRadius: '2px' }" :title="orden.hoja_ruta_escaneada ? 'Ver Hoja de Ruta Escaneada' : 'Subir Hoja de Ruta Escaneada / Foto'">
                                                        <i class="fa" :class="orden.hoja_ruta_escaneada ? 'fa-file-image-o' : 'fa-upload'"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td v-text="orden.idorden || orden.id"></td>
                                            <td v-text="orden.fechaorden || orden.fecha_orden || (orden.created_at ? orden.created_at.substring(0,10) : '')"></td>
                                            <td v-text="orden.fecha"></td>
                                            <td>
                                                {{orden.cantidad}} 
                                                {{ orden.articulo && typeof orden.articulo === 'object' ? orden.articulo.nombre : (orden.articulo || '') }} 
                                                {{orden.ancho_material || orden.tamano || ''}}
                                            </td>
                                            <td v-text="orden.rasonsocial || (orden.cliente && orden.cliente.razonsocial)"></td>
                                            <td style="color:red" v-text="orden.detalles_diseno"></td>
                                            <td style="color:red" v-text="orden.observaciones"></td>
                                            <td>
                                                 <div style="font-size: 11px;">Prioridad</div>
                                                 <div v-if="String(orden.prioridad).toLowerCase() === 'vip'" class="badge badge-vip-gold p-1 text-center" style="display: block; width: 100%;">
                                                     ⭐ VIP
                                                 </div>
                                                 <div v-else class="badge p-1 text-white text-center" :class="[ (orden.prioridad == 'normal') ? 'bg-success' : (orden.prioridad == 'media' ? 'bg-warning' : 'bg-danger') ]" style="display: block; width: 100%; border-radius: 4px; font-weight: bold;">
                                                     {{ orden.prioridad || 'normal' }}
                                                 </div>
                                            </td>
                                            <td>
                                                <select v-model="orden.produccion" class="bg-success text-white border-0 p-1" style="border-radius: 4px; font-weight: bold; width: 100%; height: 30px;" @change="cambiarEstado(orden)">
                                                    <option v-for="(estadop, index) in estadoproduccion" :key="index" :value="index" v-text="estadoproduccion[index]"></option>
                                                </select>
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <button v-if="orden.impresa == 0" class="btn btn-sm text-white" style="background-color: #7b1fa2; border-color: #7b1fa2; width: 100%; font-weight: bold;" @click="cambiarImpreso(orden, 1)">
                                                        No impresa
                                                    </button>
                                                    <button v-else class="btn btn-sm text-white" style="background-color: #e3b7e4; border-color: #e3b7e4; color: #522b53; width: 100%; font-weight: bold;" @click="cambiarImpreso(orden, 0)">
                                                        Impresa
                                                    </button>
                                                    
                                                    <button v-if="(orden.estadoc || orden.estado) == 'VC'" class="btn btn-sm text-white" style="background-color: #ffb300; border-color: #ffb300; width: 100%; font-weight: bold;" @click="cambiarEstado(orden, 'OSP')">
                                                        VC
                                                    </button>
                                                </div>
                                            </td>
                                            <td style="color: #007e33; font-weight: bold;" v-text="orden.total"></td>
                                            <td>
                                                <input type="date" v-model="orden.fecha_entrega" @change="cambiarFecha(orden)" :style="`background:hsl(${calcularDias(index,orden)*15}deg 100% 44%); border: 1px solid #ccc; border-radius: 4px; padding: 2px; color: black;`">
                                            </td>
                                                                        
                                        </tr> 
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="13" class="text-center">
                                                No hay ordenes creadas
                                            </td>
                                        </tr>
                                    </tbody>    
                                </table>
                            </div>   </nav>
                        </div>
                    </template>
                    <!--Fin Listado-->
                    <template v-else-if="listado===2">
                        <div class="botones_vieworden">
                            <button class="btn btn-primary" @click="ocultarDetalle">Volver al listado de ordenes</button>
                            <button class="btn btn-warning" @click="imprimirOrden">Imprimir</button>
                        </div>
                        <div class="card vieworden">
                            <div class="card-header header_vieworden">
                                <div class="logo_vieworden">

                                    <img src="img/logo.png" alt="">
                                </div>
                               <div class="fecha_norden">
                                    <h5 v-text="`ORDEN DE TRABAJO NO. ${idorden}`"></h5>
                                    <div class="card fecha_orden">
                                        <div class="card-body ">
                                            <strong> <div>Fecha:</div>   <div v-text="fecha"></div></strong>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                            <div class="datos_cliente">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Cliente</h4>
                                    </div>
                                    <div class="card-body">
                                       
                                        <ul>
                                            <li>
                                                <label>Contacto:</label> <div v-text="contacto"></div> 
                                            </li>
                                            <li>
                                                <label>Correo:</label> <div v-text="email_contacto"></div>
                                            </li>
                                            <li>
                                                <label>Razón Social:</label> <div v-text="nombre"></div>
                                            </li>
                                            <li>
                                                <label v-text="tipo_documento"></label> <div v-text="num_documento"></div>
                                            </li>
                                            <li>
                                                <label>Dirección: </label> <div v-text="direccion"></div>
                                            </li>
                                            <li>
                                                <label>Teléfono empresa: </label> <div v-text="telefono_contacto"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="datosorden">
                                <div class="card">
                                    <div class="card-body">
                                        <div><label for="">Cantidad trabajo:</label> <div v-text="cantidad"></div></div>
                                        <div><label for="">Dimenciones trabajo:</label> <div v-text="ancho_material"></div><div v-text="largo_material"></div></div>
                                        <div><label for="">Carpeta cliente:</label> <div v-text="carpeta_cliente"></div></div>
                                        <div><label for="">Fecha de entrega:</label> <div v-text="fecha_entrega"></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="descripcion_orden">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Descripción del trabajo</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul>
                                            <li v-for="(detalle, index) in arrayDetalle" :key="index">
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                        <th scope="row" v-text="detalle.titulo_detalle"></th>
                                                        <td v-text="detalle.valor_detalle"></td>
                                                        <td v-text="detalle.descripcion_detalle"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Ejecución trabajo</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul>
                                            <li v-for="(costo, index) in arrayCostos" :key="index">
                                                <table class="table">
                                                    
                                                    <tbody>
                                                        <tr>
                                                        <th scope="row" v-text="costo.tipo_costo"></th>
                                                        <td v-text="costo.nombre_insumo"></td>
                                                        <td v-text="costo.descripcion_costo"></td>
                                                        <td v-text="costo.cantidad_costo"></td>
                                                        <td v-text="`$ ${costo.subtotal_costo}`"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header">
                                            <h4 for="">Detalles de diseño</h4> 
                                        </div>
                                        <div style="color:red" v-text="detalles_diseno" class="card"></div>
                                    </div>
                                </div>
                                 <div class="card">
                                    <div class="card-body">
                                        <div class="card-header">
                                            <h4 for="">Descripción</h4>
                                        </div>
                                        <div style="color:red" v-text="observaciones" class="card"></div>
                                    </div>
                                 </div>
                            </div>
                            <div class="card-footer">
                                <div class="card">
                                    <div class="card-body">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                <td v-text="`VALOR ORDEN: $${valor}`"></td>
                                                <td v-text="`IVA: $${totalImpuesto}`"></td>
                                                <td v-text="`TOTAL: $${total}`"></td>
                                                <td v-text="`DESCUENTO: $${descuento}`"></td>
                                                <td v-text="`ABONO: $${abono}`"></td>
                                                <th scope="row" v-text="`SALDO: $${saldo}`"></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </template>
                    <!-- Detalle-->
                    <template v-else>
                    <div class="card-body">
                        <div class="form-group row border p-4 cliente">
                            <!--Inicio modal listado clientes-->
                            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalc}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seleccione un cliente</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModalc()">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <template v-if="arrayClientes">
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    :class="{'active' : cliente_seleccionado}" 
                                                    v-for="(cliente,index) in arrayClientes" 
                                                    :key="cliente.id" 
                                                    v-text="cliente.nombre"
                                                    @click="getDatosCliente(cliente,index)">
                                                    </a> 
                                                </div>
                                            </template>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" @click="cerrarModalc()" data-dismiss="modal">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- fin modal listado clientes -->
                            <div class="col-md-12">
                                <div class="headerOrden">
                                     <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalfecha}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Verificar Fecha</h5>
                                                    <button @click="cerrarModalFecha" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="date" v-model="fecha">
                                                </div>
                                                <div class="modal-footer">
                                                    <button @click="cerrarModalFecha" type="button" class="btn btn-primary">Terminar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>  
                                     <div class="form-group row">
                                        <div class="col-md-12">
                                            <button type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</button>
                                            <button v-if="action=='nuevo'" type="button" class="btn btn-primary" @click="registrarOrden()">Registrar</button>
                                            <button v-else type="button" class="btn btn-primary" @click="actualizarOrden()">Guardar</button>
                                        </div>
                                    </div>
                                    <div class="estadoOrden">
                                            <h3 class="btn btn-dark">Estado comercial</h3>
                                            <div class="form-check btn" :class="[estadoc=='C' ? 'btn-warning': 'btn-secondary']">
                                                <input type="radio" name="estadoc" id="C" v-model="estadoc" value="C">
                                                <label class="form-check-label"  for="C">Por Cotizar</label>                                
                                            </div>
                                            <div class="form-check btn" :class="[estadoc=='PC' ? 'btn-warning': 'btn-secondary']">
                                                <input type="radio" name="estadoc" id="PC"  v-model="estadoc" value="PC">
                                                <label class="form-check-label" for="PC"> Por Concretar</label>                                    
                                            </div>
                                           
                                            <div class="form-check btn" :class="[estadoc=='PA' ? 'btn-warning': 'btn-secondary']">
                                                <input type="radio" name="estadoc" id="PA"  v-model="estadoc" value="PA">
                                                <label class="form-check-label" for="PA"> Pendiente Abono</label>                                        
                                            </div>
                                            <div @click="asignarFecha()" class="form-check btn" :class="[estadoc=='VC' ? 'btn-success': 'btn-secondary']">
                                                <input type="radio" name="estadoc" id="VC"  v-model="estadoc" value="VC">
                                                <label class="form-check-label" for="VC"> Venta Cerrada</label> 
                                            </div>
                                            <div @click="asignarFecha()" class="form-check btn" :class="[estadoc=='P' ? 'btn-danger': 'btn-secondary']">
                                                <input type="radio" name="estadoc" id="P"  v-model="estadoc" value="P">
                                                <label class="form-check-label" for="P"> No Compra</label>                                  
                                            </div>
                                            <div class="form-check btn" :class="[estadoc=='AN' ? 'btn-warning': 'btn-secondary']">
                                                <input type="radio" name="estadoc" id="AN"  v-model="estadoc" value="AN">
                                                <label class="form-check-label" for="AN">Aplazada</label>
                                            </div>
                                    </div>
                                    <div class="estadoOrden">
                                            <h3 class="btn btn-primary">Estado produccón</h3>
                                            <div class="form-check btn" :class="[estadop=='D' ? 'btn-danger': 'btn-secondary']">
                                                <input type="radio" name="estadop" id="D"  v-model="estadop" value="D">
                                                <label class="form-check-label" for="D"> Diseño</label>                               
                                            </div>
                                            <div class="form-check btn" :class="[estadop=='A' ? 'btn-warning': 'btn-secondary']">
                                                <input type="radio" name="estadop" id="A"  v-model="estadop" value="A">
                                                <label class="form-check-label" for="A"> Aprobación</label>                                   
                                            </div>
                                            <div class="form-check btn" :class="[estadop=='EP' ? 'btn-danger': 'btn-secondary']">
                                                <input type="radio" name="estadop" id="EP"  v-model="estadop" value="EP">
                                                <label class="form-check-label" for="EP"> Enviar a producción</label>                                            
                                            </div>
                                            <div class="form-check btn" :class="[estadop=='ENP' ? 'btn-success': 'btn-secondary']">
                                                <input type="radio" name="estadop" id="ENP"  v-model="estadop" value="ENP">
                                                <label class="form-check-label" for="ENP"> En producción</label>                                      
                                            </div>
                                            <div class="form-check btn" :class="[estadop=='E' ? 'btn-success': 'btn-secondary']">
                                                <input type="radio" name="estadop" id="E"  v-model="estadop" value="E">
                                                <label class="form-check-label" for="E"> Para entrega</label>                                                          
                                            </div>
                                            <div class="form-check btn" :class="[estadop=='T' ? 'btn-primary': 'btn-secondary']">
                                                <input type="radio" name="estadop" id="T"  v-model="estadop" value="T">
                                                <label class="form-check-label" for="T"> Terminada</label>                                                          
                                            </div>
                                        
                                    </div>
                                    <h4>Datos del Cliente</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Fecha orden</label>
                                        <input type="date" class="form-control" v-model="fechaorden">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="">Fecha de entrega</label>
                                        <input type="date" class="form-control" v-model="fecha_entrega" style="color:#000" :style="`background:hsl(${diasfaltantes*15}deg 100% 44%)`" >
                                    </div>
                                    <div class="col-md-3">
                                        <label for="">Fecha de Cierre</label>
                                        <input type="date" class="form-control" v-model="fecha">
                                    </div>
                                </div>
                                <div class="form-group">
                                    
                                    <label>buscar cliente por nombre(*) <span style="color:red" v-show="idcliente==0">(*Seleccione)</span> </label>
                                    <div class="form-group row">
                                        <div class="">
                                            <div class="form-inline">
                                                <input type="text" class="form-control" v-model="buscar_cliente" @keyup="selectCliente('nuevo')" placeholder="Ingrese nombre del cliente">
                                            </div> 
                                        </div>
                                        <div class="col-md-9">
                                            <button @click="nuevoCliente()" class="btn btn-link">Nuevo cliente</button>
                                        </div> 
                                        <div class="col-md-12" v-if="cliente_seleccionado!=null">
                                            <div class="form-group row border p-4">
                                                <div class="col-md-3">
                                                    <label for="">Nombre o Razón social</label>
                                                    <input type="text" class="form-control" v-model="nombre">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class=" form-control-label" for="text-input">Tipo cliente</label>
                                                    <select v-model="tipo_cliente" class="form-control">
                                                        <option value="Natural">Natural</option>
                                                        <option value="Juridico">Juridico</option>
                                                    </select>                                    
                                                </div>
                                                <div class="col-md-3">
                                                    <label class=" form-control-label" for="text-input">Tipo Documento</label>
                                                    <select v-model="tipo_documento" class="form-control">
                                                        <option value="NIT">NIT</option>
                                                        <option value="CC">CC</option>
                                                        <option value="PASAPORTE">PASAPORTE</option>
                                                    </select>                                    
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Documento</label>
                                                    <input type="text" class="form-control" v-model="num_documento">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Dirección</label>
                                                    <input type="text" class="form-control" v-model="direccion">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">País</label>
                                                    <input type="text" class="form-control" v-model="pais">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Departamento</label>
                                                    <input type="text" class="form-control" v-model="departamento">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Ciudad</label>
                                                    <input type="text" class="form-control" v-model="ciudad">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Teléfono</label>
                                                    <input type="text" class="form-control" v-model="telefono">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Correo Electrónico</label>
                                                    <input type="email" class="form-control" v-model="email">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Nombre de contacto</label>
                                                    <input type="text" class="form-control" v-model="contacto">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Teléfono del contacto</label>
                                                    <input type="text" class="form-control" v-model="telefono_contacto">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Correo del contacto</label>
                                                    <input type="email" class="form-control" v-model="email_contacto">
                                                </div>

                                                <div class="col-md-9">
                                                    <label for=""></label>
                                                    <button class="btn btn-success" @click="guardarCliente()" style="margin-top: 25px;float: right;"><i class="fa fa-save"></i></button>
                                                </div>
                                                <div v-if="msjCliente.length>0" class="col-md-12 alert alert-success" v-text="msjCliente">

                                                </div>
                                            </div>                                  
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-12 producto">
                                 <!--Inicio modal listado articulos-->
                                <div class="modal fade" tabindex="-1" :class="{'mostrar' : modala}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                    <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Seleccione un producto</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModala()">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <template v-if="arrayArticulos">
                                                    <div class="list-group">
                                                        <a href="#" 
                                                        class="list-group-item list-group-item-action" 
                                                        :class="{'active' : articulo_seleccionado}" 
                                                        v-for="(articulo,index) in arrayArticulos" 
                                                        :key="articulo.id" 
                                                        v-text="articulo.nombre"
                                                        @click="getDatosArticulo(articulo,index), getPrecioUnitario(articulo)">
                                                        </a> 
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" @click="cerrarModala()" data-dismiss="modal">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- fin modal listado articulos -->
                                 <div class="col-md-12 mt-2">
                                    <h4>Datos del producto</h4>
                                </div>
                                <div class="form-group">
                                   
                                    <label>Buscar producto por su nombre <span style="color:red" v-show="idarticulo==0">(*Seleccione)</span> </label>
                                    <div class="form-group row">
                                        <div class="">
                                            <div class="form-inline">
                                                <input type="text" class="form-control" v-model="buscar_articulo" @keyup="selectArticulo('nuevo')" placeholder="Ingrese nombre del producto">
                                                
                                            </div>  
                                        </div>
                                        <div class="col-md-9">
                                            <button @click="nuevoArticulo()" class="btn btn-link">Nuevo Producto</button>
                                        </div> 
                                        <div class="col-md-12" v-if="articulo_seleccionado!=null">
                                            <div class="form-group row border p-4">
                                                <div class="col-md-3">
                                                    <label for="">Producto</label>
                                                    <input type="text" class="form-control" v-model="nombre_articulo">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Valor producto</label>
                                                    <input type="text" class="form-control" v-model="precio">
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label for="">IVA</label>
                                                    <input type="text" class="form-control" v-model="impuesto">
                                                </div>
                                                <div class="col-md-9">
                                                    <label for=""></label>
                                                    <button class="btn btn-success" @click="guardarArticulo()" style="margin-top: 25px;float: right;"><i class="fa fa-save"></i></button>
                                                </div>
                                                <div v-if="msjArticulo.length>0" class="col-md-12 alert alert-success" v-text="msjArticulo">

                                                </div>
                                            </div>                                  
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="">Cantidad</label>
                                <input type="text" class="form-control" v-model="cantidad">
                            </div>
                            <div class="col-md-2">
                                <label for="">Medida del trabajo</label>
                                <input type="text" class="form-control" v-model="ancho_material">
                            </div>
                            <div class="col-md-2">
                                <label for="">Medida material</label>
                                <input type="text" class="form-control" v-model="largo_material">
                            </div>
                            <div class="col-md-2">
                                <label for="">Unidad de medida</label>
                                <input type="text" class="form-control" v-model="unidad">
                            </div>
                           
                            <div class="col-md-2">
                                <label for="">Nombre Carpeta del cliente</label>
                                <input type="text" class="form-control" v-model="carpeta_cliente">
                            </div>
                            <div class="col-md-12">
                                <label for="">Detalles de diseño</label>
                                <textarea style="color:red" class="form-control" v-model="detalles_diseno">  </textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="">Observaciones</label>
                                <textarea style="color:red" class="form-control" v-model="observaciones">  </textarea>
                            </div>
                            <div class="col-md-12 mt-4">
                              <div class="form-group row border p-4">
                                    <div class="table-responsive col-md-12">
                                        <table class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Valor unitario</th>
                                                    <th>Cantidad</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                    <th>Impuesto</th>
                                                    <th>Total</th>
                                                    <th>Abono</th>
                                                    <th>Saldo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="background-color: #CEECF5;">
                                                    <td>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">$</span>
                                                            </div>
                                                            <input type="number" class="form-control" v-model="precio_producto">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" v-model="cantidad">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" v-model="descuento" placeholder="Descuento">
                                                    </td>
                                                    <td>$ {{calcularTotalParcial}}</td>
                                                    <td>$ {{calcularTotalImpuesto}}</td>
                                                    <td>$ {{calcularTotal.toFixed(0)}}</td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" class="form-control" v-model="abono" placeholder="Abono">
                                                        </div>
                                                    </td>
                                                    <td>$ {{calcularSaldo.toFixed(0)}}</td>
                                                </tr>
                                            </tbody>  
                                                                    
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- detalles de trabajo -->
                        <div class="form-group row border p-4 ">
                            
                            <div class="col-md-12 mt-2">
                                <h4>Detalles del trabajo</h4>
                            </div>
                           
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>Tipo Detalle <span style="color:red" v-show="titulo_detalle==0">(*Ingrese)</span></label>
                                    <input type="text" class="form-control" v-model="titulo_detalle" placeholder="Tinta, Papel, tamaño, etc">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>Detalle<span style="color:red" v-show="valor_detalle==0">(*Ingrese)</span></label>
                                    <input type="text" class="form-control" v-model="valor_detalle" placeholder="Cual es el detalle">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Descripción<span style="color:red" v-show="descripcion_detalle==0">(*Ingrese)</span></label>
                                    <input type="text"  class="form-control" v-model="descripcion_detalle" placeholder="Descripción o datos adicionales">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <button @click="agregarDetalle()" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></button>
                                </div>
                            </div>
                        
                            <!-- lista de detalles de trabajo agregados -->
                            <div class="form-group col-md-12 border p-4">
                                <div class="table-responsive col-md-12">
                                    <table class="table table-bordered table-striped table-sm" style="border-collapse: collapse; border: 1px solid #ccc; width: 100%;">
                                        <thead style="background-color: #d2e0e6; color: #2e3e4e; font-weight: bold; border-bottom: 2px solid #b2c0c6;">
                                            <tr>
                                                <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; text-align: left;">Opciones</th>
                                                <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Titulo Detalle</th>
                                                <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Detalle</th>
                                                <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Descripcion</th>
                                                <th style="padding: 12px; border: 1px solid #b8c7cc; font-size: 14px; font-style: italic; text-align: left;">Insumo o Maquina</th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="arrayDetalle.length">
                                            <tr v-for="(detalle,index) in arrayDetalle" :key="index" style="border-bottom: 1px solid #c8d4db;">
                                                <td style="background-color: #dce1e5; text-align: center; vertical-align: middle; padding: 6px 10px; width: 16%; border: 1px solid #c8d4db;">
                                                     <div style="display: flex; gap: 4px; align-items: center; justify-content: center;">
                                                         <button @click="moverArribaDetalle(index)" :disabled="index === 0" type="button" 
                                                                 class="btn btn-sm btn-secondary"
                                                                 style="padding: 4px 8px; font-size: 13px; font-weight: bold; cursor: pointer;"
                                                                 title="Mover arriba">
                                                             ▲
                                                         </button>
                                                         <button @click="moverAbajoDetalle(index)" :disabled="index === (arrayDetalle.length - 1)" type="button" 
                                                                 class="btn btn-sm btn-secondary"
                                                                 style="padding: 4px 8px; font-size: 13px; font-weight: bold; cursor: pointer;"
                                                                 title="Mover abajo">
                                                             ▼
                                                         </button>
                                                         <button @click="eliminarDetalle(index)" type="button" title="Eliminar"
                                                                 style="background-color: #ff6861; color: white; border: none; border-radius: 4px; padding: 4px 10px; height: 31px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; cursor: pointer; transition: background-color 0.2s;"
                                                                 onmouseover="this.style.backgroundColor='#e55750'"
                                                                 onmouseout="this.style.backgroundColor='#ff6861'">
                                                             ⊗
                                                         </button>
                                                     </div>
                                                 </td>
                                                <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 18%; border: 1px solid #c8d4db;">
                                                    <input type="text" v-model="detalle.titulo" class="form-control" 
                                                           style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                                                </td>
                                                <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 22%; border: 1px solid #c8d4db;">
                                                    <div style="width:100%" v-if="esColor(detalle.titulo, detalle.valor)">
                                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                            <selector-color :detalle="detalle" wi="100"></selector-color>
                                                            <div v-if="getColorsArray(detalle.valor).length > 0" style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                                                <div v-for="(val, idx) in getColorsArray(detalle.valor)" :key="idx" 
                                                                     class="text-white text-center d-flex align-items-center justify-content-center" 
                                                                     :style="'background:' + val.hex + '; padding: 8px 10px; border-radius: 4px; font-size: 12px; min-height: 52px; min-width: 75px; max-width: 95px; line-height: 1.2; font-family: inherit; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.15); word-break: break-word;'">
                                                                    {{ val.pantone }}
                                                                </div>
                                                            </div>
                                                            <div v-else-if="detalle.valor && typeof detalle.valor === 'object'" 
                                                                 class="text-white text-center d-flex align-items-center justify-content-center" 
                                                                 :style="'background:' + (detalle.valor.hex || '#000') + '; padding: 8px 10px; border-radius: 4px; font-size: 12px; min-height: 52px; min-width: 75px; max-width: 95px; line-height: 1.2; font-family: inherit; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.15); word-break: break-word;'">
                                                                {{ detalle.valor.pantone || detalle.valor }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-else>
                                                        <input type="text" v-model="detalle.valor" class="form-control" placeholder="Valor detalle" 
                                                               style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                                                    </div>
                                                </td>
                                                <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 22%; border: 1px solid #c8d4db;">
                                                    <input type="text" v-model="detalle.descripcion" class="form-control" 
                                                           style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%; margin-bottom: 4px;">
                                                    <div v-if="detalle.titulo && detalle.titulo.toLowerCase() === 'papel' && detalle.costo" 
                                                         style="background-color: #ffffff; border: 1px solid #b8c4cc; border-radius: 4px; padding: 8px 12px; font-size: 13px; color: #333; line-height: 1.4; margin-top: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-family: inherit; text-align: left;">
                                                        <div>Tamaños: {{ detalle.costo.descripcion }}</div>
                                                        <div>Pliegos: {{ Math.round(detalle.costo.cantidad) }}</div>
                                                    </div>
                                                </td>
                                                <td style="background-color: #dce1e5; padding: 10px; vertical-align: middle; width: 23%; border: 1px solid #c8d4db;">
                                                    <input v-if="!detalle.costo || !detalle.costo.costois || !detalle.costo.costois.nombre || detalle.costo.costois.nombre===''" 
                                                           type="text" 
                                                           :id="'modificar'+index" 
                                                           class="form-control" 
                                                           :value="(detalle.costo && detalle.costo.descripcion) ? detalle.costo.descripcion : ''"
                                                           @keyup="selectInsumos('modificar'+index,detalle)" 
                                                           placeholder="Asigne Insumo, Maquina" 
                                                           style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                                                    <input v-else 
                                                           type="text" 
                                                           v-model="detalle.costo.costois.nombre" 
                                                           :id="'modificar'+index"
                                                           @keyup="selectInsumos('modificar'+index,detalle)" 
                                                           class="form-control" 
                                                           style="background-color: #ffffff; color: #333; border: 1px solid #b8c4cc; border-radius: 4px; height: 36px; padding: 6px 12px; font-size: 14px; width: 100%;">
                                                </td>
                                            </tr>
                                        </tbody>                                    
                                        <tbody v-else>
                                            <tr>
                                                <td colspan="5"> 
                                                    No hay detalles agregados
                                                </td>
                                            </tr>  
                                        </tbody>                               
                                    </table>
                                    <div class="form-group row border p-4 insumos">
                                        <!--Inicio del modal insumos-->
                                        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modali}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                            <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Seleccione costo</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModali()">
                                                        <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <template v-if="arrayInsumos">
                                                            <div v-if="seccion=='costo'" class="list-group">
                                                                <a href="#" 
                                                                class="list-group-item list-group-item-action" 
                                                                :class="{'active' : insumo_seleccionado}" 
                                                                v-for="(insumo,index) in arrayInsumos" 
                                                                :key="insumo.id" 
                                                                v-text="insumo.nombre"
                                                                @click="getDatosInsumos(insumo,index)">
                                                                </a> 
                                                            </div>
                                                            <div v-else-if="seccion && seccion.includes('modificar')" class="list-group">
                                                                <a href="#" 
                                                                class="list-group-item list-group-item-action" 
                                                                v-for="(insumo,index) in arrayInsumos" 
                                                                :key="index" 
                                                                @click="agregarCostoDetalle(insumo,index,objeto,seccion)">
                                                                {{insumo.nombre}} - <small>({{insumo.proveedor ? insumo.proveedor.nombre : 'S/N'}})</small>
                                                                </a> 
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-primary" @click="cerrarModali()" data-dismiss="modal">Cancelar</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!--Fin del modal-->
                                        <div class="col-md-12 mt-2">
                                            <h4>Costos del trabajo</h4>
                                        </div>
                                    
                                        <div class="col-lg-7 col-xl-6">
                                            <div class="form-group">
                                                <label>Seleccione Insumo o servicio <span style="color:red" v-show="idcosto==0">(*Seleccione)</span> </label>
                                                <div class="form-group row">
                                                    <div class="col-lg-3">
                                                        <div class="form-inline">
                                                            <input type="text" class="form-control" v-model="buscar_insumo" @keyup="selectInsumos('costo')" placeholder="Ingrese costos del trabajo">
                                                        </div>  
                                                    </div>
                                                    <div class="col-lg-12" v-if="insumo_seleccionado!=null">
                                                        <div class="form-group row border p-4 subform">
                                                            <div class="col-md-3">
                                                                <label for="">Insumo</label>
                                                                <input type="text" class="form-control" v-model="nombre_insumo">
                                                            </div>
                                                            
                                                            <div class="col-md-3">
                                                                <label for="">Valor Insumo</label>
                                                                <input type="text" class="form-control" @keyup="calcularCosto()" v-model="valor_insumo">
                                                            </div>
                                                                <div class="col-md-3">
                                                                <label for="">Unidad de medida</label>
                                                                <input type="text" class="form-control" v-model="unidad_medida">
                                                            </div>
                                                        </div>                                  
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-xl-4" v-if="insumo_seleccionado!=null">
                                            <div class="form-group">
                                                <label>Titulo <span style="color:red" v-show="tipo_costo==0">(*Ingrese)</span></label>
                                                <div  class="form-control" v-text="tipo_costo" ></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-2">
                                            <div class="form-group">
                                                <label>Cantidad costo<span style="color:red"  v-show="cantidad_costo==0">(*Ingrese)</span></label>
                                                <input type="number" value="0" step="any" class="form-control" @keyup="calcularCosto()" v-model="cantidad_costo" placeholder="Cantidad del costo">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-3">
                                            <div class="form-group">
                                                <label>Orden de producción<span style="color:red"  v-show="orden==0">(*Ingrese)</span></label>
                                                <input type="text" class="form-control" v-model="orden_costo" placeholder="Orden de producción">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-3">
                                            <div class="form-group">
                                                <label>Valor costo<span style="color:red" v-show="valor_costo==0">(*Ingrese)</span></label>
                                                <input type="number" value="" step="any" class="form-control" v-model="valor_costo" placeholder="Valor del costo">
                                                <!-- {{calcularCosto2}} -->
                                            </div>
                                        </div>
                                        <div class="col-lg-10 col-xl-3">
                                            <div class="form-group">
                                                <label>Descripcion<span style="color:red" v-show="descripcion_costo==0">(*Ingrese)</span></label>
                                                <input type="text" value="0" step="any" class="form-control" v-model="descripcion_costo" placeholder="Descripción del costo">
                                            </div>
                                        </div>
                                    
                                        <div class="col-lg-2 col-xl-3">
                                            <div class="form-group">
                                                <button @click="agregarCosto()" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></button>
                                            </div>
                                        </div>

                                        <!-- lista de costos del trabajo -->
                                        <div class="form-group col-md-12 border p-4">
                                            <div class="table-responsive col-md-12">
                                                <table class="table table-bordered table-striped table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th></th>
                                                            <th>Costo</th>
                                                            <th>Insumo</th>
                                                            <th>Descripcion</th>
                                                            <th>Orden de producción</th>
                                                            <th>Valor</th>
                                                            <th>Cantidad</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody v-if="arrayCostos.length">
                                                        <tr v-for="(costo,index) in arrayCostos" :key="costo.id" :class="[ costo.terminado==1 ? 'bg-success': costo.completado==1 ? 'bg-warning': 'bg-danger']" >
                                                            <td>
                                                                <button @click="eliminarCosto(index)" type="button" class="btn btn-danger btn-sm">
                                                                    <i class="icon-close"></i>
                                                                </button>
                                                            </td>
                                                            <td >
                                                                <input type="text" v-model="costo.tipo_costo"  value="3" class="form-control">
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="costo.nombre_insumo"  value="3" class="form-control">
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="costo.descripcion_costo" class="form-control">
                                                            </td>
                                                            <td>
                                                                <input type="text"  v-model="costo.orden_costo" class="form-control">
                                                            </td>
                                                            <td>
                                                                <input type="text"  @change="subtotalCosto" v-model="costo.valor_costo" class="form-control">
                                                            </td>
                                                            <td>
                                                                <input type="number" @change="subtotalCosto" v-model="costo.cantidad_costo" class="form-control">
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="costo.subtotal_costo" class="form-control">
                                                            </td>
                                                        
                                                        </tr>
                                                    
                                                        <tr style="background-color: #CEECF5;">
                                                            <td colspan="4" align="right"><strong>Total Costos:</strong></td>
                                                            <td colspan="4">{{totalCostos}}</td>
                                                            <!-- <td>$ {{totalParcial=(total-totalImpuesto).toFixed(2)}}</td> -->
                                                        </tr>

                                                    </tbody>  
                                                    <tbody v-else>
                                                        <tr>
                                                            <td colspan="5"> 
                                                                No hay artíclos agregados
                                                            </td>
                                                        </tr>  
                                                    </tbody>                               
                                                </table>
                                            </div>
                                        </div>
                                        <!-- fin de listado de costos del trabajo -->
                                    </div>
                                </div>
                            </div>
                            <!-- fin lista de detalles de trabajo agregados -->

                        </div>
                        <!-- fin detalles de trabajo -->
                        <!-- costos del trabajos-->
                        <!--fin costos del trabajo-->
                        
                      
                      
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</button>
                                <button v-if="action=='nuevo'" type="button" class="btn btn-primary" @click="registrarOrden()">Registrar</button>
                                <button v-else type="button" class="btn btn-primary" @click="actualizarOrden()">Guardar</button>
                            </div>
                        </div>
                    </div>
                    </template>
                    <!-- Fin Detalle-->
                    

                </div>
                <!-- Fin ejemplo de tabla Listado -->
                 
            </div>
            
             
           
        </main>
</template>

<script>
    import SelectorColor from './partes/SelectorColor.vue'
   
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    export default {
        props: ['user'],
        data (){
            return {
                limite: 100,
                buscaro:'',
                buscare:'',
                buscarp:'',
                buscarc:'',
                buscarv:'',
                buscar:'todos',
                fechaI:'',
                fechaF:'',
                filtroFecha:'1',
                buscarFechai:'',
                buscarFechaf:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                action:'',
                idorden:0,
                estadocomercial:{C:'Por Cotizar', PC:'Por Concretar', PA:'Pendiente Abono', VC:'Venta Cerrada', P:'No Comprar', A:'Aplazada'},
                estadoproduccion:{D:'Diseño', A:'Aprobación', EP:'Enviar a producción', ENP:'En producción', EM:'Empacado', E:'Por entregar', T:'Terminada'},
                unidad:'',
                fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                fechaorden:`${new Date().getFullYear()}/${meses[new Date().getMonth()]}/${new Date().getDate()}`,
                fecha_entrega:new Date(),
                diasfaltantes:10,
                arrayOrdenes:[],
                estadoc:'C',
                estadop:'',
                carpeta_cliente:'',
                detalles_diseno:'',
                observaciones:'',
                largo_material:0,
                ancho_material:0,
                valor:0,
                idproveedor:0,
                nombre : '',
                tipo_comprobante : 'BOLETA',
                serie_comprobante : '',
                num_comprobante : '',
                impuesto: 0.19,
                descuento:0,
                total:0,
                valor_insumo:0,
                abono:0,
                saldo:0,
                totalImpuesto:0,
                totalParcial:0,
                arrayClientes: [],
                idcliente:0,
                cliente_seleccionado:null,
                cseleccionado:false,
                nombre:'',
                tipo_cliente:'Natural',
                tipo_documento:'',
                num_documento:'',
                direccion:'',
                pais:'Colombia',
                departamento:'Valle del Cauca',
                ciudad:'Cali',
                telefono:'',
                email:'',
                contacto:'',
                telefono_contacto:'',
                email_contacto:'',
                arrayDetalle : [],
                titulo_detalle:'',
                valor_detalle:'',
                descripcion_detalle:'',
                arrayArticulos:[],
                idarticulo:0,
                articulo_seleccionado:null,
                aseleccionado:false,
                nombre_articulo:'',
                precio:0,
                precio_producto:0,
                cantidad:1000,
                vieworden:0,
                listado:1,
                idcosto:0,
                nombre_insumo:'',
                valor_insumo:0,
                unidad_medida:'',
                insumo_seleccionado:null,
                iseleccionado:false,
                arrayInsumos:[],
                arrayCostos:[],
                descripcion_costo:'',
                valor_costo:0,
                cantidad_costo:1,
                tipo_costo:'',
                orden_costo:0,
                modala:0,
                modalc:0,
                modali:0,
                modalfecha:0,
                modalIntervalo:0,
                tituloModal : '',
                tipoAccion : 0,
                errorIngreso : 0,
                errorMostrarMsjIngreso : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : '',
                buscar_cliente: '',
                criterioA:'nombre',
                buscar_articulo:'',
                buscar_insumo:'',
                topedit:0,
                dominio:'',
                msjCliente:'',
                msjArticulo:'',
                seccion:'',
                ordenarFlecha:false,
                scroll:0,
                objeto:{}
            }
        },
        components: {
            'SelectorColor': SelectorColor,
            'selector-color': SelectorColor
        },
        computed:{
            isActived: function(){
                return this.pagination.current_page;
            },
            //Calcula los elementos de la paginación
            pagesNumber: function() {
                if(!this.pagination.to) {
                    return [];
                }
                
                var from = this.pagination.current_page - this.offset; 
                if(from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2); 
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }  

                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;             

            },
            calcularCosto2(){
                var resultado=0
                resultado=this.cantidad_costo*this.valor_insumo
                this.valor_costo=resultado
                return resultado
            },
            subtotalCosto(){
                var resultado=0
                for(var i=0;i<this.arrayCostos.length;i++){
                    this.arrayCostos[i].subtotal_costo=parseFloat(this.arrayCostos[i].valor_costo)*parseInt(this.arrayCostos[i].cantidad_costo)
                resultado=this.arrayCostos[i].subtotal_costo
                }
                return resultado
            },
            
            totalCostos(){
                var resultado=0
                for(var i=0;i<this.arrayCostos.length;i++){
                    resultado=resultado+parseFloat(this.arrayCostos[i].subtotal_costo)
                }
                return resultado
            },
           
            calcularTotalParcial(){
                var resultado=0
                resultado=(this.precio_producto*this.cantidad)-this.descuento
                this.totalParcial=resultado
                return resultado
            },
            calcularTotalImpuesto(){
                var resultado=0;
                resultado=this.totalParcial*this.impuesto
                this.totalImpuesto=resultado
                return resultado
            },
            calcularTotal(){
                var resultado=0
                resultado=this.totalParcial+this.totalImpuesto
                this.total=resultado
                return resultado
            },
            calcularSaldo(){
                var resultado=0
                resultado =this.total-this.abono
                this.saldo=resultado
                return resultado
            }
           

        },
        methods : {
           
            asignarFecha(){
                this.modalfecha=1
            },
            cerrarModalFecha(){
                this.modalfecha=0
            },
           
            asiganarIntervalo(){
                this.modalIntervalo=1
            },
            
            cerrarModalIntervalo(){
                this.modalIntervalo=0
            },
            calcularCosto(){
                let me=this
                if(me.valor_insumo!=0){
                    me.valor_costo=me.cantidad_costo*me.valor_insumo;
                }else{
                    alert('seleccione primero un Insumo');
                }
            },
             
            normalizarOrdenes(data) {
                if (!data || !Array.isArray(data)) return [];
                return data.map(orden => {
                    if (orden) {
                        if (typeof orden.idorden === 'undefined') orden.idorden = orden.id;
                        if (typeof orden.id === 'undefined') orden.id = orden.idorden;
                        if (typeof orden.produccion === 'undefined') orden.produccion = orden.estadop;
                        if (typeof orden.estadop === 'undefined') orden.estadop = orden.produccion;
                        if (typeof orden.estado === 'undefined') orden.estado = orden.estadoc;
                        if (typeof orden.estadoc === 'undefined') orden.estadoc = orden.estado;
                        if (typeof orden.rasonsocial === 'undefined' && orden.cliente) orden.rasonsocial = orden.cliente.razonsocial;
                        if (typeof orden.fechaorden === 'undefined') orden.fechaorden = orden.fecha_orden;
                        if (typeof orden.fecha_orden === 'undefined') orden.fecha_orden = orden.fechaorden;

                        if (orden.detalles && Array.isArray(orden.detalles)) {
                            orden.detalles.forEach(det => {
                                if (det) {
                                    if (typeof det.titulo_detalle === 'undefined' && typeof det.titulo !== 'undefined') det.titulo_detalle = det.titulo;
                                    if (typeof det.titulo === 'undefined' && typeof det.titulo_detalle !== 'undefined') det.titulo = det.titulo_detalle;
                                    if (typeof det.valor_detalle === 'undefined' && typeof det.valor !== 'undefined') det.valor_detalle = det.valor;
                                    if (typeof det.valor === 'undefined' && typeof det.valor_detalle !== 'undefined') det.valor = det.valor_detalle;
                                    if (typeof det.descripcion_detalle === 'undefined' && typeof det.descripcion !== 'undefined') det.descripcion_detalle = det.descripcion;
                                    if (typeof det.descripcion === 'undefined' && typeof det.descripcion_detalle !== 'undefined') det.descripcion = det.descripcion_detalle;
                                }
                            });
                        }
                    }
                    return orden;
                });
            },
            cambiarLimite() {
                this.pagination.per_page = this.limite;
                this.listarOrdenes(1, this.buscar, 'like', this.criterio);
            },
            cambiarImpreso(orden, valor){
                let me = this;
                axios.put(me.dominio + '/orden/cambiarImpresa', {
                    'id': orden.idorden || orden.id,
                    'impresa': valor
                }).then(function (response) {
                    orden.impresa = valor;
                }).catch(function (error) {
                    console.log(error);
                });
            },
            imprimirHojaRuta(orden) {
                if (window.abrirModalProcesosHojaRuta) {
                    window.abrirModalProcesosHojaRuta(orden);
                } else {
                    let id = (orden && (orden.idorden || orden.id)) ? (orden.idorden || orden.id) : orden;
                    if (id) window.open('/orden/hoja-ruta/' + id, '_blank');
                }
            },
            generarOrdentrabajo(orden) {
                var me = this;
                var url = '/generarOrden';
                var userj = me.user;
                var userId = userj ? userj.id : 1;
                
                let datosenvio = {
                    id: orden.idorden || orden.id,
                    fecha_orden: orden.fechaorden || orden.fecha_orden,
                    fecha: orden.fecha,
                    cantidad: orden.cantidad,
                    articulo: orden.articulo && typeof orden.articulo === 'object' ? orden.articulo.nombre : (orden.articulo || ''),
                    rasonsocial: orden.rasonsocial || (orden.cliente && orden.cliente.razonsocial),
                    detalles_diseno: orden.detalles_diseno,
                    observaciones: orden.observaciones,
                    total: orden.total,
                    fecha_entrega: orden.fecha_entrega,
                    prioridad: orden.prioridad,
                    produccion: orden.estadop || orden.produccion,
                    estado: orden.estadoc || orden.estado,
                    user_id: userId
                };
                
                axios({
                    url: me.dominio + '/generarOrden?orden=' + encodeURIComponent(JSON.stringify(datosenvio)),
                    method: 'GET',
                    responseType: 'blob'
                }).then(function (response) {
                    var url = window.URL.createObjectURL(new Blob([response.data]));
                    var link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'orden' + (datosenvio.rasonsocial || '') + '.pdf');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }).catch(function (error) {
                    console.log(error);
                });
            },
            listarOrdenes (page,buscar,operador,criterio){
                let me=this;
                me.buscar=buscar;
                me.criterio=criterio;
                var url= me.dominio+'/orden?page='+page+'&criterio='+ criterio+'&operador='+operador+'&buscar='+buscar+'&per_page='+me.limite;
                axios.get(url,{
                    
                }).then(function (response) {
                    console.log(response);
                    var respuesta= response.data;
                    me.arrayOrdenes = me.normalizarOrdenes(respuesta.ordenes.data);
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
               
            },
            getClientebyid(idcliente){
                let me=this;
                var url= me.dominio+'/cliente/selectCliente?id='+idcliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data[0];
                    me.getDatosCliente(respuesta)
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectCliente(){
                let me=this;
                
                var url= me.dominio+'/cliente/selectClientes?filtro='+this.buscar_cliente;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    
                    me.arrayClientes=respuesta.clientes;
                    me.modalc=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosCliente(val1, index){
                let me = this;
                me.loading = true;
                me.idcliente = val1.id;
                me.nombre=val1.nombre;
                me.tipo_documento=val1.tipo_documento
                me.tipo_cliente=val1.tipo_cliente
                me.num_documento=val1.num_documento
                me.direccion=val1.direccion
                me.pais=val1.pais
                me.departamento=val1.departamento
                me.ciudad=val1.ciudad
                me.telefono=val1.telefono
                me.email=val1.email
                me.contacto=val1.contacto
                me.telefono_contacto=val1.telefono_contacto
                me.email_contacto=val1.email_contacto
                me.cliente_seleccionado=val1;
                me.buscar_cliente=val1.nombre
                me.cseleccionado=true
                me.arrayClientes=[];
                me.modalc=0
            },
            nuevoCliente(){
                let me=this;
                me.cliente_seleccionado=0
                me.buscar_cliente=''
                me.nombre=''
                me.tipo_cliente=''
                me.tipo_documento=''
                me.direccion=''
                me.telefono=''
                me.telefono_contacto=''
                me.contacto=''
                me.pais=''
                me.departamento=''
                me.ciudad=''
                me.num_documento=''
                me.email=''
                me.email_contacto=''
                
            },
            nuevoArticulo(){
                 let me=this;
                me.articulo_seleccionado=0
                me.nombre_articulo=''
                me.precio=0
            },
            guardarCliente(){
                let me=this;
                var id=0
                if(me.cliente_seleccionado==0){
                   id=0
                }else{
                   id=me.cliente_seleccionado.id
                }
                var url= me.dominio+'/cliente/actualizar';
                axios.put(url,{
                    'tipo_cliente':me.tipo_cliente,
                    'nombre': me.nombre,
                    'tipo_documento': me.tipo_documento,
                    'num_documento' : me.num_documento,
                    'direccion' : me.direccion,
                    'ciudad' : me.ciudad,
                    'departamento' : me.departamento,
                    'pais' : me.pais,
                    'contacto' : me.contacto,
                    'telefono_contacto' : me.telefono_contacto,
                    'email_contacto' : me.email_contacto,
                    'telefono' : me.telefono,
                    'email' : me.email,
                    'id':id
                }).then(function (response) {
                    let respuesta = response.data;
                    if(id==0){
                        me.getDatosCliente(respuesta)
                    }
                    console.log(respuesta)
                    if(typeof respuesta.id !== 'undefined'){
                        me.msjCliente='Cliente guardado';
                    }

                })
                .catch(function (error) {
                    console.log(error);
                }); 
                   
                
            },
            guardarArticulo(){
                let me=this;
                var id=0
                if(me.articulo_seleccionado==0){
                   id=0
                }else{
                   id =me.articulo_seleccionado.id
                }
                var url= me.dominio+'/articulo/actualizar';
                axios.put(url,{
                    'idcategoria': 1,
                    'codigo': 0,
                    'nombre': this.nombre_articulo,
                    'stock': 1000,
                    'precio_venta': this.precio,
                    'descripcion': '',
                    'id':id
                }).then(function (response) {
                    let respuesta = response.data;
                    if(id==0){
                        me.getDatosArticulo(respuesta,0)
                    }
                    if(typeof respuesta.id !== 'undefined'){
                        me.msjArticulo='Producto guardado';
                    }

                })
                .catch(function (error) {
                    console.log(error);
                }); 
                this.precio_producto=this.precio
            },
            selectArticulobyid(idarticulo){
                let me=this;
               
                var url= me.dominio+'/articulo/selectArticulobyid?id='+idarticulo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data[0];
                    me.getDatosArticulo(respuesta,0)
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectArticulo(){
                let me=this;
               
                var url= me.dominio+'/articulo/selectArticulo?filtro='+this.buscar_articulo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayArticulos=respuesta.articulos;
                    me.modala=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
             getPrecioUnitario(val){
                let me=this
                me.precio_producto=val.precio_venta
            },
            getDatosArticulo(val1, index){
                let me = this;
                me.loading = true;
                me.idarticulo = val1.id;
                me.nombre_articulo=val1.nombre;
                me.precio=val1.precio_venta
                me.articulo_seleccionado=val1;
                me.buscar_articulo=val1.nombre
                me.aseleccionado=true
                me.arrayArticulo=[];
                me.modala=0
            },
            selectInsumos(seccion, objeto = null){
                let me=this;
                me.objeto=objeto;
                var filtro = '';
                if (seccion === 'costo') {
                    filtro = me.buscar_insumo;
                } else {
                    var campo = document.getElementById(seccion);
                    filtro = campo ? campo.value : '';
                }
                var base = window.location.pathname.replace(/\/main.*/i, '').replace(/\/+$/, '');
                var url= base + '/costop/selectInsumos?filtro='+encodeURIComponent(filtro);
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayInsumos=respuesta.insumos || [];
                    me.modali=1;
                    me.seccion=seccion;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            agregarCosto(){
                let me=this
                if(me.tipo_costo=='' || me.valor_costo==''){

                }else{
                    me.arrayCostos.push({
                        idcosto:0,
                        tipo_costo:me.tipo_costo,
                        nombre_insumo:me.nombre_insumo,
                        id_insumo:me.insumo_seleccionado.id,
                        valor_costo:me.valor_costo,
                        cantidad_costo:me.cantidad_costo,
                        orden_costo:me.orden_costo,
                        descripcion_costo:me.descripcion_costo,
                        subtotal_costo:parseFloat(me.valor_costo)*parseInt(me.cantidad_costo)
                    }) 
                    me.tipo_costo=''
                    me.nombre_insumo=''
                    me.id_insumo=''
                    me.valor_costo=''
                    me.cantidad_costo=1
                    me.orden_costo=0
                    me.descripcion_costo=''
                    me.subtotal_costo=''
                    me.insumo_seleccionado=null
                    me.buscar_insumo=''
                }
               
                
            },
            getDatosInsumos(val1, index){
                let me = this;
                me.loading = true;
                me.idcosto = val1.id;
                me.tipo_costo=val1.tipo_costo;
                me.nombre_insumo=val1.nombre;
                me.valor_insumo=val1.valor
                me.unidad_medida=val1.unidad_medida
                me.insumo_seleccionado=val1;
                me.buscar_insumo=val1.nombre
                me.iseleccionado=true
                me.arrayInsumos=[];
                me.modali=0
                me.calcularCosto()
            },
            getDatosDetalles(val1, index){
                let me = this;
                me.loading = true;
                me.idcosto = val1.id;
                me.titulo_costo=val1.tipo_costo;
                me.valor_detalle=val1.nombre;
                me.buscar_insumo=val1.nombre
                me.iseleccionado=true
                me.arrayInsumos=[];
                me.modali=0
            },
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarOrdenes(page,buscar,'like',criterio);
            },
           
            agregarDetalle(){
                 let me=this
                if(me.titulo_detalle=='' || me.valor_detalle==''){

                }else{
                    me.arrayDetalle.push({
                        id:0,
                        titulo_detalle:me.titulo_detalle,
                        valor_detalle:me.valor_detalle,
                        descripcion_detalle:me.descripcion_detalle
                    }) 
                    me.titulo_detalle=''
                    me.valor_detalle=''
                    me.descripcion_detalle=''
                }
               
                
            },
            
            listarArticulo (buscar,criterio){
                let me=this;
                var url= me.dominio+'/articulo/listarArticulo?buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayArticulos = respuesta.articulos.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            moverArribaDetalle(index) {
                if (index > 0 && this.arrayDetalle) {
                    const temp = this.arrayDetalle[index];
                    this.$set(this.arrayDetalle, index, this.arrayDetalle[index - 1]);
                    this.$set(this.arrayDetalle, index - 1, temp);
                    this.arrayDetalle.forEach((det, idx) => {
                        det.orden = idx + 1;
                    });
                }
            },
            moverAbajoDetalle(index) {
                if (this.arrayDetalle && index < this.arrayDetalle.length - 1) {
                    const temp = this.arrayDetalle[index];
                    this.$set(this.arrayDetalle, index, this.arrayDetalle[index + 1]);
                    this.$set(this.arrayDetalle, index + 1, temp);
                    this.arrayDetalle.forEach((det, idx) => {
                        det.orden = idx + 1;
                    });
                }
            },
            eliminarDetalle(index){
                 let me=this
                 if(me.arrayDetalle[index].id>0){
                    var url= '/detalle/borrar?id='+ me.arrayDetalle[index].id + '&_method=DELETE';
                    axios.delete(url).then(function (response) {
                        var respuesta= response.data;
                        me.arrayDetalle.splice(index,1)
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                   
                }else{
                    me.arrayDetalle.splice(index,1)
                }
               
                
            },
            eliminarCosto(index){
                let me=this
                if(me.arrayCostos[index].idcosto>0){
                    var url= me.dominio+'/costo/borrar?id='+ me.arrayCostos[index].idcosto + '&_method=DELETE';
                    axios.delete(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayCostos.splice(index,1)
                })
                .catch(function (error) {
                    console.log(error);
                });
                   
                }else{
                    me.arrayCostos.splice(index,1)
                }
            },
            registrarOrden(){
                let me = this;
                this.arrayDetalle.forEach(det => {
                    if (det) {
                        det.titulo_detalle = det.titulo;
                        det.valor_detalle = det.valor;
                        det.descripcion_detalle = det.descripcion;
                    }
                });
                const orden1 = new FormData()
                orden1.set('estadoc',this.estadoc)
                orden1.set('estadop',this.estadop)
                orden1.set('id_cliente', this.cliente_seleccionado.id)
                orden1.set('id_articulo' , this.articulo_seleccionado.id)
                orden1.set('fecha_entrega' , this.fecha_entrega)
                orden1.set('fecha' , this.fecha)
                orden1.set('fechaorden' , this.fechaorden)
                orden1.set('carpeta_cliente' , this.carpeta_cliente)
                orden1.set('detalles_diseno' , this.detalles_diseno)
                orden1.set('observaciones' , this.observaciones)
                orden1.set('unidad' , this.unidad)
                orden1.set('ancho_material', this.ancho_material)
                orden1.set('largo_material', this.largo_material)
                orden1.set('cantidad', this.cantidad)
                orden1.set('totalParcial',this.totalParcial)
                orden1.set('descuento',this.descuento)
                orden1.set('impuesto',this.impuesto)
                orden1.set('total',this.total)
                orden1.set('abono',this.abono)
                orden1.set('saldo',this.saldo)
                orden1.set('detalles',JSON.stringify(this.arrayDetalle))
                orden1.set('costos',JSON.stringify(this.arrayCostos))
                axios.post(me.dominio+'/orden/registrar',orden1)
                .then(function (response) {
                    me.listado=1
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
            },
           
            actualizarOrden(){
                let me = this;
                this.arrayDetalle.forEach(det => {
                    if (det) {
                        det.titulo_detalle = det.titulo;
                        det.valor_detalle = det.valor;
                        det.descripcion_detalle = det.descripcion;
                    }
                });
                const orden1 = new FormData()
                orden1.set('_method', 'PUT')
                orden1.set('id', parseInt(this.idorden))
                orden1.set('estadoc',this.estadoc)
                orden1.set('estadop',this.estadop)
                orden1.set('id_cliente', this.cliente_seleccionado.id)
                orden1.set('id_articulo' , this.articulo_seleccionado.id)
                orden1.set('fecha_entrega' , this.fecha_entrega)
                orden1.set('fecha' , this.fecha)
                orden1.set('fechaorden' , this.fechaorden)
                orden1.set('carpeta_cliente' , this.carpeta_cliente)
                orden1.set('detalles_diseno' , this.detalles_diseno)
                orden1.set('observaciones' , this.observaciones)
                orden1.set('unidad' , this.unidad)
                orden1.set('ancho_material', this.ancho_material)
                orden1.set('largo_material', this.largo_material)
                orden1.set('cantidad', this.cantidad)
                orden1.set('totalParcial',this.totalParcial)
                orden1.set('descuento',this.descuento)
                orden1.set('impuesto',this.impuesto)
                orden1.set('total',this.total)
                orden1.set('abono',this.abono)
                orden1.set('saldo',this.saldo)
                orden1.set('detalles',JSON.stringify(this.arrayDetalle))
                orden1.set('costos',JSON.stringify(this.arrayCostos))
                axios.post(me.dominio+'/orden/actualizar',orden1)
                .then(function (response) {
                    me.listado=1;
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
                
            },
            cambiarEstado(orden, nuevoEstadoComercial = null){
                var me=this
                let id = orden.idorden || orden.id;
                let estadoc = nuevoEstadoComercial !== null ? nuevoEstadoComercial : (orden.estado || orden.estadoc);
                let estadop = orden.produccion || orden.estadop;
                
                // Keep aliases in sync so subsequent changes work correctly
                if (nuevoEstadoComercial !== null) {
                    orden.estado = estadoc;
                    orden.estadoc = estadoc;
                }
                orden.estadop = estadop;

                axios.put(me.dominio+'/orden/cambiarEstado',{
                    'id': id,
                    'estado': estadoc,
                    'produccion': estadop,
                    'estadoc': estadoc,
                    'estadop': estadop,
                    'user_id': me.user ? me.user.id : null
                })
                .then(function (response) {
                    if (nuevoEstadoComercial !== null) {
                        if (typeof orden.estadoc !== 'undefined') orden.estadoc = nuevoEstadoComercial;
                        if (typeof orden.estado !== 'undefined') orden.estado = nuevoEstadoComercial;
                    }
                }).catch(function (error) {
                    console.log(error);
                });
                this.filtrarOrdenes(1,this.buscar);
            },
            cambiarFecha(orden){
                var me=this
                 axios.put(me.dominio+'/orden/cambiarFecha',{
                     'id':orden.idorden || orden.id,
                     'fecha_entrega':orden.fecha_entrega
                 })
                .then(function (response) {
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.filtrarOrdenes(1,this.buscar);
            },
           
            copiarOrden(orden){
                let me=this
                var url= me.dominio+'/orden/duplicar?id='+ orden.idorden
                axios.post(url).then(function (response) {
                    var respuesta= response.data;
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
               // this.editarOrden(this.arrayOrdenes[0],'edit')
            },
            eliminarOrden(id){
                let me=this
                var url= me.dominio+'/orden/borrar';
                axios.delete(url,{
                    data: {
                        'id': id,
                        'user_id': me.user ? me.user.id : null
                    }
                }).then(function (response) {

                    var respuesta= response.data;
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarOrdenes(1,this.buscar,'like',this.criterio);
            },
            imprimirOrden(){
                window.print()
            },
            mostrarDetalle(action){
                this.action=action
                this.listado=0
            },
            ocultarDetalle(){
                this.listado=1;
                this.articulo_seleccionado=''
                this.insumo_seleccionado=''
                this.cliente_seleccionado=''
                this.abono=0
                this.ancho_material=''
                this.largo_material=''
                this.detalles_diseno=''
                this.observaciones=''
                this.cantidad=1000
                this.unidad=''
                this.carpeta_cliente=''
                this.descuento=0
                this.saldo=0
                this.precio_producto=0
                this.precio=0
                this.idarticulo=0
                this.fecha_entrega=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.fechaorden=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.estadoc='C'
                this.estadop=''
            },
            cerrarModal(){
                this.modal=0;
            },
            abrirModal(orden, accion, data = []){
                this.arrayArticulos=[]
                this.modal = 1;
                this.tituloModal = 'Seleccione 1 o varios artículos';
            },
             ordenar(opcion){
                this.ordenarFlecha=!this.ordenarFlecha
                this.arrayOrdenes.sort(function(a, b) {
                    switch(opcion){
                        case 'idorden':
                            var textA = a.idorden;
                            var textB = b.idorden;
                            break;
                        case 'fechaorden':
                            var textA = a.fechaorden;
                            var textB = b.fechaorden;
                            break;
                        case 'fecha':
                            var textA = a.fecha;
                            var textB = b.fecha;
                            break;
                        case 'articulo':
                            var textA = a.articulo;
                            var textB = b.articulo;
                            break;
                        case 'rasonsocial':
                            var textA = a.rasonsocial;
                            var textB = b.rasonsocial;
                            break;
                        case 'contacto':
                            var textA = a.contacto;
                            var textB = b.contacto;
                            break;
                        case 'telefono_contacto':
                            var textA = a.telefono_contacto;
                            var textB = b.telefono_contacto;
                            break;
                        case 'carpeta_cliente':
                            var textA = a.carpeta_cliente;
                            var textB = b.carpeta_cliente;
                            break;
                        case 'estadoc':
                            var textA = a.estadoc;
                            var textB = b.estadoc;
                            break;
                        case 'estadop':
                            var textA = a.estadop;
                            var textB = b.estadop;
                            break;
                        case 'total':
                            var textA = a.total;
                            var textB = b.total;
                            break;
                        case 'fecha_entrega':
                            var textA = a.fecha_entrega;
                            var textB = b.fecha_entrega;
                            break;
                    }
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                    
                });
                if(!this.ordenarFlecha){
                    this.arrayOrdenes.reverse(); 
                }
            },
            filtrarFecha(){
                var me=this;
                if(this.modalIntervalo){
                    me.filtroFecha=`${this.fechaI},${this.fechaF}`
                }
                var url= me.dominio+'/orden/filtrarFecha?filtroFecha='+ me.filtroFecha;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayOrdenes = me.normalizarOrdenes(respuesta.ordenes.data);
                    me.pagination= respuesta.pagination
                    me.modalIntervalo=0

                }).catch(function (error) {
                    console.log(error);
                });
            },
            
            filtrarOrdenes(page,buscar){
                var me=this
                me.buscar=buscar
                var url= me.dominio+'/orden/filtrarOrdenes?page='+page+'&buscar='+buscar;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayOrdenes = me.normalizarOrdenes(respuesta.ordenes.data);
                    me.pagination= respuesta.pagination
                }).catch(function (error) {
                    console.log(error);
                });
            },
            verOrden(orden){
                this.listado = 2
                this.idorden=orden.idorden
                this.getClientebyid(orden.idcliente)
                this.selectArticulobyid(orden.idarticulo)
                this.estadoc=orden.estadoc
                this.estadop=orden.estadop
                this.id_cliente=orden.idcliente
                this.id_articulo=orden.idarticulo
                this.fecha_entrega=orden.fecha_entrega
                this.fecha=orden.fecha
                this.carpeta_cliente=orden.carpeta_cliente
                this.detalles_diseno=orden.detalles_diseno
                this.observaciones=orden.observaciones
                this.ancho_material= orden.ancho_material
                this.largo_material= orden.largo_material
                this.cantidad= orden.cantidad
                this.subtotal_orden=orden.totalParcial
                this.descuento=orden.descuento
                this.impuesto=orden.impuesto
                this.total=orden.total
                this.abono=orden.abono
                this.saldo=orden.saldo
                if (orden.detalles && Array.isArray(orden.detalles)) {
                    orden.detalles.forEach(det => {
                        if (det) {
                            if (typeof det.titulo_detalle === 'undefined' && typeof det.titulo !== 'undefined') det.titulo_detalle = det.titulo;
                            if (typeof det.titulo === 'undefined' && typeof det.titulo_detalle !== 'undefined') det.titulo = det.titulo_detalle;
                            if (typeof det.valor_detalle === 'undefined' && typeof det.valor !== 'undefined') det.valor_detalle = det.valor;
                            if (typeof det.valor === 'undefined' && typeof det.valor_detalle !== 'undefined') det.valor = det.valor_detalle;
                            if (typeof det.descripcion_detalle === 'undefined' && typeof det.descripcion !== 'undefined') det.descripcion_detalle = det.descripcion;
                            if (typeof det.descripcion === 'undefined' && typeof det.descripcion_detalle !== 'undefined') det.descripcion = det.descripcion_detalle;
                        }
                    });
                }
                this.arrayDetalle=orden.detalles
                this.arrayCostos=orden.costos
            },
            calcularDias(index,orden){
                var fecha=new Date()
                var fechaini = new Date(`${fecha.getFullYear()}-${(fecha.getMonth()+1)}-${fecha.getDate()}`)
                var fechafin = new Date(orden.fecha_entrega)
                var diasdif= fechafin.getTime()-fechaini.getTime()
                var contdias = Math.round(diasdif/(1000*60*60*24))
               return contdias
            },
            editarOrden(orden,action){
                this.listado = 0
                this.action=action
                this.idorden=orden.idorden || orden.id
                this.getClientebyid(orden.idcliente || orden.cliente_id)
                this.selectArticulobyid(orden.idarticulo || orden.articulo_id)
                this.estadoc=orden.estadoc
                this.estadop=orden.estadop
                this.diasfaltantes=this.calcularDias(0,orden)
                this.id_cliente=orden.idcliente || orden.cliente_id
                this.id_articulo=orden.idarticulo || orden.articulo_id
                this.fecha_entrega=orden.fecha_entrega
                this.fecha=orden.fecha
                this.fechaorden=orden.fechaorden
                this.carpeta_cliente=orden.carpeta_cliente
                this.detalles_diseno=orden.detalles_diseno
                this.observaciones=orden.observaciones
                this.ancho_material= orden.ancho_material
                this.largo_material= orden.largo_material
                this.cantidad= orden.cantidad
                this.subtotal_orden=orden.totalParcial
                this.precio_producto=orden.totalParcial/orden.cantidad
                this.descuento=orden.descuento
                this.impuesto=orden.impuesto
                this.total=orden.total
                this.abono=orden.abono
                this.saldo=orden.saldo
                if (orden.detalles && Array.isArray(orden.detalles)) {
                    orden.detalles.forEach(det => {
                        if (det) {
                            if (typeof det.titulo_detalle === 'undefined' && typeof det.titulo !== 'undefined') det.titulo_detalle = det.titulo;
                            if (typeof det.titulo === 'undefined' && typeof det.titulo_detalle !== 'undefined') det.titulo = det.titulo_detalle;
                            if (typeof det.valor_detalle === 'undefined' && typeof det.valor !== 'undefined') det.valor_detalle = det.valor;
                            if (typeof det.valor === 'undefined' && typeof det.valor_detalle !== 'undefined') det.valor = det.valor_detalle;
                            if (typeof det.descripcion_detalle === 'undefined' && typeof det.descripcion !== 'undefined') det.descripcion_detalle = det.descripcion;
                            if (typeof det.descripcion === 'undefined' && typeof det.descripcion_detalle !== 'undefined') det.descripcion = det.descripcion_detalle;
                        }
                    });
                }
                this.arrayDetalle=orden.detalles
                this.arrayCostos=orden.costos
            },
           
            cerrarModalc(){
                this.modalc=0
                this.arrayClientes=[]
            },
            cerrarModala(){
                this.modala=0
                this.arrayArticulos=[]
            },
            cerrarModali(){
                this.modali=0
                this.arrayInsumos=[]
            },
            cerrarModalo(){
                this.modalo=0
                this.ordenv=[]
            },
            getColorsArray(valor){
                if(Array.isArray(valor)) return valor;
                if(typeof valor === 'string' && valor.trim().startsWith('[')) {
                    try {
                        let parsed = JSON.parse(valor);
                        if(Array.isArray(parsed)) return parsed;
                    } catch(e){}
                }
                return [];
            },
            esColor(titulo, valor){
                if(this.getColorsArray(valor).length > 0) return true;
                if(!titulo) return false;
                let t = String(titulo).toLowerCase();
                return t.includes('tinta') || t.includes('impresion') || t.includes('impresión') || t.includes('color');
            },
            agregarCostoDetalle(costo,index,detalle,seccion){
                let me=this;
                var tamanos=parseFloat(me.cantidad/me.cabida)+parseInt(me.carpeta_cliente);
                var pliegos=tamanos/me.tamano;
                
                if(seccion.includes('modificar')){
                    if(detalle.costo){
                        detalle.costo.costois_id=costo.id;
                        detalle.costo.costois=costo;
                    }else{
                        var cost={
                            costos_id:0,
                            titulo:costo.tipo_costo,
                            costois:costo,
                            costois_id:costo.id,
                            valor:costo.valor,
                            cantidad:pliegos,
                            orden:1,
                            descripcion:tamanos,
                            completado:0,
                            terminado:0,
                        };
                        this.$set(detalle, 'costo', cost);
                        this.$set(detalle, 'costos_id', 0);
                    }
                    detalle.valor = costo.nombre;
                }
                
                // Keep suffix in sync
                detalle.valor_detalle = detalle.valor;
                
                me.modali=0;
            },
            handleScroll(evt) {
                this.scroll = window.scrollY;
            },
        },
        mounted() {
            this.listarOrdenes(1,this.buscar,'like',this.criterio);
        }
    }
</script>
<style>  
    .insumos, .producto, .cliente{
        position: relative;
    } 
    .mostrar{
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(0,0,0,0.5) !important;
        overflow-y: hidden !important;
        z-index: 10500 !important;
    }
    .mostrar .modal-dialog,
    .modal-bajo {
        margin: 10px auto !important;
        top: 0 !important;
        align-self: flex-start !important;
        max-height: calc(100vh - 20px) !important;
        height: calc(100vh - 20px) !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

</style>
