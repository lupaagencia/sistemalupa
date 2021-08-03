<template>
            <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Orden de trabajo
                        <button type="button" @click="mostrarDetalle()" class="btn btn-success">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                    </div>
                    <!-- Listado-->
                    <template v-if="listado">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" v-model="buscar" @keyup="listarOrden(1,buscar,'nombre')" class="form-control" placeholder="Cliente">
                                            <input type="text" v-model="buscar" @keyup="listarOrden(1,buscar,'fecha')" class="form-control" placeholder="Fecha">
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Usuario</th>
                                        <th>Proveedor</th>
                                        <th>Tipo Comprobante</th>
                                        <th>Serie Comprobante</th>
                                        <th>Número Comprobante</th>
                                        <th>Fecha Hora</th>
                                        <th>Total</th>
                                        <th>Impuesto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayOrdenes.length">
                                    <tr v-for="orden in arrayOrdenes" :key="orden.id">
                                        <td>
                                            <button type="button" @click="abrirModal('ingreso','actualizar',ingreso)" class="btn btn-success btn-sm">
                                            <i class="icon-eye"></i>
                                            </button> &nbsp;
                                            <template v-if="orden.estado=='Registrado'">
                                                <button type="button" class="btn btn-danger btn-sm" @click="desactivarIngreso(ingreso.id)">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </template>
                                        </td>
                                        <td v-text="orden.usuario"></td>
                                        <td v-text="orden.nombre"></td>
                                        <td v-text="orden.tipo_comprobante"></td>
                                        <td v-text="orden.serie_comprobante"></td>
                                        <td v-text="orden.num_comprobante"></td>
                                        <td v-text="orden.fecha_hora"></td>
                                        <td v-text="orden.total"></td>
                                        <td v-text="orden.impuesto"></td>
                                        <td v-text="orden.estado"></td>
                                    </tr>                                
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="5">
                                            No hay ordenes creadas
                                        </td>
                                    </tr>
                                </tbody>    
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    </template>
                    <!--Fin Listado-->
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
                                    <h4>Datos del Cliente</h4>
                                    <div class="estadoOrden">
                                        <label class=" form-control-label" for="text-input">Estado</label>
                                        <select v-model="estado" class="form-control">
                                            <option value="d">DISEÑO</option>
                                            <option value="a">APROBACION</option>
                                            <option value="ep">ENVIAR A PRODUCCION</option>
                                            <option value="enp">EN PRODUCCION</option>
                                            <option value="e">POR ENTREGAR</option>
                                        </select>                                    
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
                                        <div class="col-md-12" v-if="cliente_seleccionado!=null">
                                            <div class="form-group row border p-4">
                                                <div class="col-md-3">
                                                    <label for="">Nombre o Razón social</label>
                                                    <input type="text" class="form-control" v-model="nombre">
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
                                                    <input type="text" class="form-control" v-model="documento">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="">Dirección</label>
                                                    <input type="text" class="form-control" v-model="direccion">
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
                                                        @click="getDatosArticulo(articulo,index)">
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
                                            </div>                                  
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <label for="">Cantidad</label>
                                <input type="text" class="form-control" v-model="cantidad">
                            </div>
                            <div class="col-md-1">
                                <label for="">Ancho material</label>
                                <input type="text" class="form-control" v-model="ancho_material">
                            </div>
                            <div class="col-md-1">
                                <label for="">Largo material</label>
                                <input type="text" class="form-control" v-model="largo_material">
                            </div>
                            <div class="col-md-2">
                                <label for="">Unidad de medida</label>
                                <input type="text" class="form-control" v-model="unidad">
                            </div>
                           <div class="col-md-3">
                                <label for="">Fecha de entrega</label>
                                <input type="date" class="form-control" v-model="fecha_entrega">
                            </div>
                            <div class="col-md-2">
                                <label for="">Nombre Carpeta del cliente</label>
                                <input type="text" class="form-control" v-model="carpeta_cliente">
                            </div>
                            <div class="col-md-12">
                                <label for="">Detalles de diseño</label>
                                <textarea class="form-control" v-model="detalle_diseno">  </textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="">Observaciones</label>
                                <textarea class="form-control" v-model="observaciones">  </textarea>
                            </div>
                            <div class="col-md-12 mt-4">
                              <div class="form-group row border p-4">
                                    <div class="table-responsive col-md-12">
                                        <table class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Valor</th>
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
                                                    <td>$ {{this.calcularValor}}</td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="number" class="form-control" v-model="descuento" placeholder="Descuento">
                                                        </div>
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
                            <div class="col-md-2">
                                
                                <div class="form-group">
                                    <label>Tipo Detalle <span style="color:red" v-show="titulo_detalle==0">(*Ingrese)</span></label>
                                    <input type="text" class="form-control" v-model="titulo_detalle" placeholder="Tinta, Papel, tamaño, etc">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Detalle<span style="color:red" v-show="valor_detalle==0">(*Ingrese)</span></label>
                                    <input type="text" class="form-control" v-model="valor_detalle" placeholder="Cual es el detalle">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Descripción<span style="color:red" v-show="descripcion_detalle==0">(*Ingrese)</span></label>
                                    <input type="text"  class="form-control" v-model="descripcion_detalle" placeholder="Descripción o datos adicionales">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <button @click="agregarDetalle()" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></button>
                                </div>
                            </div>
                        
                            <!-- lista de detalles de trabajo agregados -->
                            <div class="form-group col-md-12 border p-4">
                                <div class="table-responsive col-md-12">
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Opciones</th>
                                                <th>Detalle</th>
                                                <th>Datos adicionales</th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="arrayDetalle.length">
                                            <tr v-for="(detalle,index) in arrayDetalle" :key="index">
                                                <td>
                                                    <button @click="eliminarDetalle(index)" type="button" class="btn btn-danger btn-sm">
                                                        <i class="icon-close"></i>
                                                    </button>
                                                </td>
                                                <td v-text="detalle.titulo_detalle">
                                                </td>
                                                <td v-text="detalle.valor_detalle">
                                                </td>
                                                <td v-text="detalle.descripcion_detalle">
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
                            <!-- fin lista de detalles de trabajo agregados -->

                        </div>
                        <!-- fin detalles de trabajo -->
                        <!-- costos del trabajos-->
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
                                                <div class="list-group">
                                                    <a href="#" 
                                                    class="list-group-item list-group-item-action" 
                                                    :class="{'active' : insumo_seleccionado}" 
                                                    v-for="(insumo,index) in arrayInsumos" 
                                                    :key="insumo.id" 
                                                    v-text="insumo.nombre"
                                                    @click="getDatosInsumos(insumo,index)">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Titulo <span style="color:red" v-show="titulo_costo==0">(*Ingrese)</span></label>
                                    <input type="text" class="form-control" v-model="titulo_costo" placeholder="Plancha, Papel, Refile, etc">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Seleccione Insumo o servicio <span style="color:red" v-show="idcosto==0">(*Seleccione)</span> </label>
                                    <div class="form-group row">
                                        <div class="col-md-3">
                                            <div class="form-inline">
                                                <input type="text" class="form-control" v-model="buscar_insumo" @keyup="selectInsumos('nuevo')" placeholder="Ingrese costos del trabajo">
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" v-if="insumo_seleccionado!=null">
                                <div class="form-group row border p-4">
                                    <div class="col-md-3">
                                        <label for="">Insumo</label>
                                        <input type="text" class="form-control" v-model="nombre_insumo">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="">Valor Insumo</label>
                                        <input type="text" class="form-control" v-model="valor_insumo">
                                    </div>
                                        <div class="col-md-3">
                                        <label for="">Unidad de medida</label>
                                        <input type="text" class="form-control" v-model="unidad_medida">
                                    </div>
                                </div>                                  
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Descripcion<span style="color:red" v-show="costo_detalle==0">(*Ingrese)</span></label>
                                    <input type="number" value="0" step="any" class="form-control" v-model="costo_detalle" placeholder="Descripción del costo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cantidad costo<span style="color:red"  v-show="cantidad_costo==0">(*Ingrese)</span></label>
                                    <input type="number" value="0" step="any" class="form-control" @keyup="calcularCosto()" v-model="cantidad_costo" placeholder="Cantidad del costo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valor costo<span style="color:red" v-show="valor_costo==0">(*Ingrese)</span></label>
                                    <input type="number" value="" step="any" class="form-control" v-model="valor_costo" placeholder="Valor del costo">
                                    <!-- {{calcularCosto2}} -->
                                </div>
                            </div>
                          
                            <div class="col-md-2">
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
                                                <th>Costo</th>
                                                <th></th>
                                                <th>Precio</th>
                                                <th>Cantidad</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="arrayDetalle.length">
                                            <tr v-for="(detalle,index) in arrayDetalle" :key="detalle.id">
                                                <td>
                                                    <button @click="eliminarDetalle(index)" type="button" class="btn btn-danger btn-sm">
                                                        <i class="icon-close"></i>
                                                    </button>
                                                </td>
                                                <td v-text="detalle.articulo">
                                                </td>
                                                <td>
                                                    <input type="number" v-model="detalle.precio"  value="3" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="number" value="2" v-model="detalle.cantidad" class="form-control">
                                                </td>
                                                <td>
                                                    {{detalle.precio*detalle.cantidad}}
                                                </td>
                                            </tr>
                                        
                                            <tr style="background-color: #CEECF5;">
                                                <td colspan="4" align="right"><strong>Total Parcial:</strong></td>
                                                <!-- <td>$ {{totalParcial=(total-totalImpuesto).toFixed(2)}}</td> -->
                                            </tr>
                                            <tr style="background-color: #CEECF5;">
                                                <td colspan="4" align="right"><strong>Total Impuesto:</strong></td>
                                                <!-- <td>$ {{totalImpuesto=((total*impuesto)/(1+impuesto)).toFixed(2)}}</td> -->
                                            </tr>
                                            <tr style="background-color: #CEECF5;">
                                                <td colspan="4" align="right"><strong>Total Neto:</strong></td>
                                                <!-- <td>$ {{total=calcularTotal}}</td> -->
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
                        <!--fin costos del trabajo-->
                        
                      
                      
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</button>
                                <button type="button" class="btn btn-primary" @click="registrarIngreso()">Registrar Compra</button>
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
  
    export default {
        data (){
            return {
                buscar:'',
                unidad:'',
                fecha_entrega:'',
                arrayOrdenes:[],
                estado:'d',
                carpeta_cliente:'',
                detalle_diseno:'',
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
                tipo_documento:'',
                documento:'',
                direccion:'',
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
                cantidad:1,
                listado:1,
                arrayInsumos:[],
                idcosto:0,
                nombre_insumo:'',
                valor_insumo:0,
                unidad_medida:'',
                insumo_seleccionado:null,
                iseleccionado:false,
                arrayCostos:[],
                costo_detalle:'',
                valor_costo:0,
                cantidad_costo:1,
                titulo_costo:'',
                modala:0,
                modalc:0,
                modali:0,
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
                criterio : 'num_comprobante',
                buscar_cliente: '',
                criterioA:'nombre',
                buscar_articulo:'',
                buscar_insumo:'',
                topedit:0
               
            }
        },
        components: {
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
            calcularCostos(){
                var resultado=0
                // for(var i=0;i<this.arrayCostos.length;i++){
                //     resultado=resultado+(this.arrayCostos[i].precio*this.arrayDetalle[i].cantidad)
                // }
                return resultado
            },
            calcularValor(){
                var resultado=0
                resultado=this.precio*this.cantidad
                this.valor=resultado
                return resultado
                
            },
            calcularTotalParcial(){
                var resultado=0;
                resultado=this.valor-this.descuento
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
            calcularCosto(){
                let me=this
                if(me.valor_insumo!=0){
                    me.valor_costo=me.cantidad_costo*me.valor_insumo;
                }else{
                    alert('seleccione primero un Insumo');
                }
            },
            listarOrdenes (page,buscar,criterio){
                let me=this;
                var url= '/ordenes?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayOrdenes = respuesta.ingresos.data;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectCliente(action){
                let me=this;
                if(action=='edit'){
                    me.topedit=1
                }
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
            getDatosCliente(val1, index){
                let me = this;
                me.loading = true;
                me.idcliente = val1.id;
                me.nombre=val1.nombre;
                me.tipo_documento=val1.tipo_documento
                me.documento=val1.documento
                me.direccion=val1.direccion
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
            selectArticulo(action){
                let me=this;
                if(action=='edit'){
                    me.topedit=1
                }
                var url= '/articulo/selectArticulo?filtro='+this.buscar_articulo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayArticulos=respuesta.articulos;
                    me.modala=1
                })
                .catch(function (error) {
                    console.log(error);
                });
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
            selectInsumos(action){
                let me=this;
                if(action=='edit'){
                    me.topedit=1
                }
                var url= '/costop/selectInsumos?filtro='+this.buscar_insumo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    console.log(respuesta);
                    me.arrayInsumos=respuesta.insumos;
                    me.modali=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosInsumos(val1, index){
                let me = this;
                me.loading = true;
                me.idcosto = val1.id;
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
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarIngreso(page,buscar,criterio);
            },
            encuentra(id){
                var sw=0
                for(var i=0;i<this.arrayDetalle.length;i++){
                    if(this.arrayDetalle[i].idarticulo==id){
                        sw=true
                    }
                    return sw
                }
            },
            agregarDetalle(){
                 let me=this
                if(me.titulo_detalle=='' || me.valor_detalle==''){

                }else{
                    me.arrayDetalle.push({
                        titulo_detalle:me.titulo_detalle,
                        valor_detalle:me.valor_detalle,
                        descripcion_detalle:me.descripcion_detalle
                    }) 
                }
               
                
            },
            agregarDetalleModal(data=[]){
                let me=this
                if(me.encuentra(data['id'])){
                        Swal.fire({
                            icon:'error',
                            type:'error',
                            title:'Error...',
                            text:'Ese artículo ya se encuentra agregado'
                    })
                      
                    }else{
                        me.arrayDetalle.push({
                            idarticulo:data['id'],
                            articulo:data['nombre'],
                            cantidad:1,
                            precio:1
                        }) 
                       
                    }
            },
            listarArticulo (buscar,criterio){
                let me=this;
                var url= '/articulo/listarArticulo?buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayArticulos = respuesta.articulos.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            eliminarDetalle(index){
                let me=this
                me.arrayDetalle.splice(index,1)
            },
            registrarPersona(){
                if (this.validarPersona()){
                    return;
                }
                
                let me = this;

                axios.post('/user/registrar',{
                    'nombre': this.nombre,
                    'tipo_documento': this.tipo_documento,
                    'num_documento' : this.num_documento,
                    'direccion' : this.direccion,
                    'telefono' : this.telefono,
                    'email' : this.email,
                    'idrol' : this.idrol,
                    'usuario': this.usuario,
                    'password': this.password

                }).then(function (response) {
                    me.cerrarModal();
                    me.listarPersona(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                });
            },
           
            validarPersona(){
                this.errorPersona=0;
                this.errorMostrarMsjPersona =[];

                if (!this.nombre) this.errorMostrarMsjPersona.push("El nombre de la pesona no puede estar vacío.");
                if (!this.usuario) this.errorMostrarMsjPersona.push("El nombre de usuario no puede estar vacío.");
                if (!this.password) this.errorMostrarMsjPersona.push("La password del usuario no puede estar vacía.");
                if (this.idrol==0) this.errorMostrarMsjPersona.push("Seleccione una Role.");
                if (this.errorMostrarMsjPersona.length) this.errorPersona = 1;

                return this.errorPersona;
            },
            mostrarDetalle(){
                this.listado=0;
            },
            ocultarDetalle(){
                this.listado=1;
            },
            cerrarModal(){
                this.modal=0;
            },
            abrirModal(modelo, accion, data = []){
                this.arrayArticulos=[]
                this.modal = 1;
                this.tituloModal = 'Seleccione 1 o varios artículos';
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
            desactivarUsuario(id){
               swal({
                title: 'Esta seguro de desactivar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/desactivar',{
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1,'','nombre');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Do you want to continue',
                            icon: 'error',
                            confirmButtonText: 'Cool'
                        })
                    }).catch(function (error) {
                        console.log(error);
                    });
                    
                    
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                    
                }
                }) 
            },
            activarUsuario(id){
               swal({
                title: 'Esta seguro de activar este usuario?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar!',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/user/activar',{
                        'id': id
                    }).then(function (response) {
                        me.listarPersona(1,'','nombre');
                        swal(
                        'Activado!',
                        'El registro ha sido activado con éxito.',
                        'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });
                    
                    
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                    
                }
                }) 
            },
        },
        mounted() {
            this.listarOrdenes(1,this.buscar,this.criterio);
        }
    }
</script>
<style>  
    .insumos, .producto, .cliente{
        position: relative;
    } 
    .modal{
        height: 2015px !important
    }
   
    .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
        position: absolute !important;
    }
    .modal-bajo{
        top:30%;
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
