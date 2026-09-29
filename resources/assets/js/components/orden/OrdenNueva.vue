<template>
    <div class="form-group row cliente">
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
                    <div class="modal-body" style="max-height: 50vh; overflow-y: auto;">
                        <template v-if="arrayClientes">
                            <div class="list-group">
                                <a href="#" 
                                class="list-group-item list-group-item-action" 
                                :class="{'active' : orden.cliente}" 
                                v-for="(cliente,index) in arrayClientes" 
                                :key="index" 
                                v-text="cliente.razonsocial"
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
                    <div  v-show="errorOrden" class="form-grou col-md-12 div-error">
                        <div class="text-center text-error">
                            <div v-for="(error, index) in errorMostrarMsjOrden" :key="index" v-text="error">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="estadoOrden">
                        <h3 class="btn btn-dark">Estado comercial</h3>
                        <div class="form-check btn" :class="[orden.estado=='C' ? 'btn-warning': 'btn-secondary']">
                            <input type="radio" name="estado" id="C" v-model="orden.estado" value="C">
                            <label class="form-check-label"  for="C">Por Cotizar</label>                                
                        </div>
                        <div class="form-check btn" :class="[orden.estado=='PC' ? 'btn-warning': 'btn-secondary']">
                            <input type="radio" name="estado" id="PC"  v-model="orden.estado" value="PC">
                            <label class="form-check-label" for="PC"> Por Concretar</label>                                    
                        </div>
                        
                        <div class="form-check btn" :class="[orden.estado=='PA' ? 'btn-warning': 'btn-secondary']">
                            <input type="radio" name="estado" id="PA"  v-model="orden.estado" value="PA">
                            <label class="form-check-label" for="PA"> Pendiente Abono</label>                                        
                        </div>
                        <div @click="asignarFecha()" class="form-check btn" :class="[orden.estado=='VC' ? 'btn-success': 'btn-secondary']">
                            <input type="radio" name="estado" id="VC"  v-model="orden.estado" value="VC">
                            <label class="form-check-label" for="VC"> Venta Cerrada</label> 
                        </div>
                        <div @click="asignarFecha()" class="form-check btn" :class="[orden.estado=='P' ? 'btn-danger': 'btn-secondary']">
                            <input type="radio" name="estado" id="P"  v-model="orden.estado" value="P">
                            <label class="form-check-label" for="P"> No Compra</label>                                  
                        </div>
                        <div class="form-check btn" :class="[orden.estado=='AN' ? 'btn-warning': 'btn-secondary']">
                            <input type="radio" name="estado" id="AN"  v-model="orden.estado" value="AN">
                            <label class="form-check-label" for="AN">Aplazada</label>
                        </div>
                </div>
                <div class="estadoOrden">
                        <h3 class="btn btn-primary">Estado produccón</h3>
                        <div class="form-check btn" :class="[orden.estadop=='D' ? 'btn-danger': 'btn-secondary']">
                            <input type="radio" name="estadop" id="D"  v-model="orden.estadop" value="D">
                            <label class="form-check-label" for="D"> Diseño</label>                               
                        </div>
                        <div class="form-check btn" :class="[orden.estadop=='A' ? 'btn-warning': 'btn-secondary']">
                            <input type="radio" name="estadop" id="A"  v-model="orden.estadop" value="A">
                            <label class="form-check-label" for="A"> Aprobación</label>                                   
                        </div>
                        <div class="form-check btn" :class="[orden.estadop=='EP' ? 'btn-danger': 'btn-secondary']">
                            <input type="radio" name="estadop" id="EP"  v-model="orden.estadop" value="EP">
                            <label class="form-check-label" for="EP"> Enviar a producción</label>                                            
                        </div>
                        <div class="form-check btn" :class="[orden.estadop=='ENP' ? 'btn-success': 'btn-secondary']">
                            <input type="radio" name="estadop" id="ENP"  v-model="orden.estadop" value="ENP">
                            <label class="form-check-label" for="ENP"> En producción</label>                                      
                        </div>
                        <div class="form-check btn" :class="[orden.estadop=='EM' ? 'btn-success': 'btn-secondary']">
                            <input type="radio" name="estadop" id="EM"  v-model="orden.estadop" value="EM">
                            <label class="form-check-label" for="EM"> Empacado</label>                                      
                        </div>
                        <div class="form-check btn" :class="[orden.estadop=='E' ? 'btn-success': 'btn-secondary']">
                            <input type="radio" name="estadop" id="E"  v-model="orden.estadop" value="E">
                            <label class="form-check-label" for="E"> Para entrega</label>                                                          
                        </div>
                        <div class="form-check btn" :class="[orden.estadop=='T' ? 'btn-primary': 'btn-secondary']">
                            <input type="radio" name="estadop" id="T"  v-model="orden.estadop" value="T">
                            <label class="form-check-label" for="T"> Terminada</label>                                                          
                        </div>
                    
                </div>
                
            </div>
            <div class="contenedor-seccion">
                <div class="row">
                    <input type="hidden" v-model="orden.impresa">
                    <div class="col-md-3">
                        <label for="">Fecha orden</label>
                        <input type="date" class="form-control" v-model="orden.fechaorden">
                    </div>
                    <div class="col-md-3">
                        <label for="">Fecha de entrega</label>
                        <input type="date" class="form-control" v-model="orden.fecha_entrega" style="color:#000" :style="`background:hsl(${diasfaltantes*15}deg 100% 44%)`" >
                    </div>
                    <div class="col-md-3">
                        <label for="">Fecha de Cierre</label>
                        <input type="date" class="form-control" v-model="orden.fecha">
                    </div>
                    <div class="col-md-3">
                        <label for="">Plancha</label>
                        <select class="form-control" v-model="orden.plancha" >
                            <option value="1">Existe</option>
                            <option value="0">No Existe</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="">Prioridad</label>
                        <select class="form-control" v-model="orden.prioridad">
                            <option value="normal">Normal</option>
                            <option value="media">Media</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="contenedor-seccion">
                <div class="contenedor-header">
                    <h4>Cliente: <font>{{orden.cliente.razonsocial}}</font></h4>
                    <div>
                        <label>buscar cliente por nombre(*) <span style="color:red" v-show="orden.cliente.id==0">(*Seleccione)</span> </label>
                        <div class="form-group row">
                            <div class="">
                                <div class="form-inline">
                                    <input type="text" class="form-control" v-model="buscar_cliente" @keyup="selectCliente('nuevo')" placeholder="Ingrese nombre del cliente">
                                </div> 
                            </div>
                        
                        </div>
                    </div>
                </div>
                    <div class="contenedor-header">
                    <h4>Producto: <font>{{orden.articulo.nombre}}</font></h4>
                    <div>
                    
                        <label>Buscar producto por su nombre <span style="color:red" v-show="orden.articulo.id==0">(*Seleccione)</span> </label>
                        <div class="form-group row">
                            <div class="">
                                <div class="form-inline">
                                    <input type="text" class="form-control" v-model="buscar_articulo" @keyup="selectArticulo('nuevo')" placeholder="Ingrese nombre del producto">
                                    
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contenedor-seccion">
                <div class="row">
                    <div class="col-md-2">
                        <label for="">Cantidad</label>
                        <input type="text" class="form-control" v-model="orden.cantidad">
                    </div>
                    <div class="col-md-2">
                        <label for="">Medida del trabajo</label>
                        <input type="text" class="form-control" v-model="orden.tamano">
                    </div>
                    <div class="col-md-2">
                        <label for="">Medida material</label>
                        <input type="text" class="form-control" v-model="orden.medida_material">
                    </div>
                    <div class="col-md-2">
                        <label for="">Cabida</label>
                        <input type="text" class="form-control" v-model="orden.cabida">
                    </div>
                
                    <div class="col-md-2">
                        <label for="">Nombre Carpeta del cliente</label>
                        <input type="text" class="form-control" v-model="orden.carpeta_cliente">
                    </div>
                    <div class="col-md-12">
                        <label for="">Detalles de diseño</label>
                        <textarea style="color:red" class="form-control" v-model="orden.detalles_diseno">  </textarea>
                    </div>
                    <div class="col-md-12">
                        <label for="">Observaciones</label>
                        <textarea style="color:red" class="form-control" v-model="orden.observaciones">  </textarea>
                    </div>
                </div>
            </div> 
            <div class="contenedor-seccion">
                <div class="contenedor-header">
                    <div class="col-md-2">
                        <label for="">Valor unitario</label>
                        <input type="text" class="form-control" v-model="orden.articulo.precio_venta">
                    </div>
                    <div class="col-md-2">
                        <label for="">Cantidad</label>
                        <input type="text" class="form-control" v-model="orden.cantidad">
                    </div>
                    <div class="col-md-2">
                        <label for="">Impuesto</label>
                        <input type="text" class="form-control" v-model="orden.impuesto">
                    </div>
                    <div class="col-md-2">
                        <label for="">Descuento</label>
                        <input type="text" class="form-control" v-model="orden.descuento">
                    </div>
                </div>
        
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Subtotal</th>
                            <th>Valor Impuesto</th>
                            <th>Total</th>
                            <th>Abono</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #CEECF5;">
                            <td>$ {{calcularTotalParcial}}</td>
                            <td>$ {{calcularTotalImpuesto}}</td>
                            <td>$ {{calcularTotal}}</td>
                            <td>
                                <div class="form-group">
                                    <input type="number" class="form-control" v-model="orden.abono" placeholder="Abono">
                                </div>
                            </td>
                            <td>$ {{calcularSaldo}}</td>
                        </tr>
                    </tbody>  
                                            
                </table>
                    
            </div> 
            <div class="contenedor-seccion">
                <div class="contenedor-header">
                    <div class="row">

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
                    </div>

                </div>
                <div class="seccion-body">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Opciones</th>
                                <th>Detalle</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody v-if="orden.detalles.length">
                            <tr v-for="(detalle,index) in orden.detalles" :key="index">
                                <td>
                                    <button @click="eliminarDetalle(index)" type="button" class="btn btn-danger btn-sm">
                                        <i class="icon-close"></i>
                                    </button>
                                </td>
                                <td>
                                    <input type="text" v-model="detalle.titulo_detalle" class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model="detalle.valor_detalle" class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model="detalle.descripcion_detalle" class="form-control">
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
                </div>
            </div> 
            <div class="contenedor-seccion">
                    <div class="insumos">
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
                                <div class="modal-body" style="max-height: 50vh; overflow-y: auto;">
                                    <template v-if="arrayInsumos">
                                        <div v-if="seccion=='costo'" class="list-group">
                                            <a href="#" 
                                            class="list-group-item list-group-item-action" 
                                            v-for="(insumo,index) in arrayInsumos" 
                                            :key="index" 
                                            @click="agregarCosto(insumo,index)">
                                            {{insumo.nombre}} - <small>({{insumo.nombre_proveedor}})</small>
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
                    <div class="contenedor-header">
                        <h4>Costos del trabajo</h4>
                        <div>
                            <div class="form-group">
                                <label>Seleccione Insumo o servicio <span style="color:red" v-show="idcosto==0">(*Seleccione)</span> </label>
                                <div class="form-group row">
                                    <div class="col-lg-3">
                                        <div class="form-inline">
                                            <input type="text" class="form-control" v-model="buscar_insumo" @keyup="selectInsumos('costo')" placeholder="Ingrese costos del trabajo">
                                        </div>  
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Costo</th>
                                <th>Insumo</th>
                                <th>Descripcion</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody v-if="orden.costos.length">
                            <tr v-for="(costo,index) in orden.costos" :key="index" :class="[ costo.terminado==1 ? 'bg-success': costo.completado==1 ? 'bg-warning': 'bg-danger']" >
                                <td>
                                    <button @click="eliminarCosto(index)" type="button" class="btn btn-danger btn-sm">
                                        <i class="icon-close"></i>
                                    </button>
                                </td>
                                <td >
                                    <input type="text" v-model="costo.tipo_costo"   class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model="costo.nombre_insumo"  class="form-control">
                                </td>
                                <td>
                                    <input type="text" v-model="costo.descripcion_costo" class="form-control">
                                </td>
                                <td>
                                    <input type="text"  v-model="costo.cantidad" class="form-control">
                                </td>
                                
<!--                                                     
                                <td>
                                    <select :class="costo.completado==1 ? 'bg-success':'bg-warning'" v-model="costo.completado" @change="cambiarProceso(costo,'completado', busquedaActual)">
                                        <option value="0" >No Iniciado</option>
                                        <option value="1">Iniciado</option>
                                    </select>
                                </td>
                                <td>
                                    <select :class="costo.terminado==1 ? 'bg-success':'bg-warning'" v-model="costo.terminado" @change="cambiarProceso(costo,'terminado', busquedaActual)">
                                        <option value="0" >No terminado</option>
                                        <option value="1">Terminado</option>
                                    </select>
                                </td>
                                -->
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
                    <!-- fin de listado de costos del trabajo -->
                </div>
            </div> 
                
        </div>
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modala}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
            <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Seleccione un producto</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModala()">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 50vh; overflow-y: auto;">
                        <template v-if="arrayArticulos">
                            <div class="list-group">
                                <a href="#" 
                                class="list-group-item list-group-item-action" 
                                :class="{'active' : orden.articulo}" 
                                v-for="(articulo,index) in arrayArticulos" 
                                :key="index" 
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
        
        <div  v-show="errorOrden" class="form-grou col-md-12 div-error">
            <div class="text-center text-error">
                <div v-for="(error,index) in errorMostrarMsjOrden" :key="index" v-text="error">

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
    </div>
</template>
