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
                        <i class="fa fa-align-justify"></i> Artículos
                        <div type="button" @click="abrirModal('articulo','registrar')" class="btn btn-secondary">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                      <option value="nombre">Nombre</option>
                                      <option value="descripcion">Descripción</option>
                                    </select>
                                    <input type="text" v-model="buscar" @keyup.enter="listarArticulo(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                                    <div type="submit" @click="listarArticulo(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</div>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Opciones</th>
                                    <th>Nombre</th>
                                    <th>Subproductos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="articulo in arrayArticulo" :key="articulo.id" ref="articulo2">
                                    <td>
                                        <div type="button" @click="abrirModal('articulo','actualizar',articulo)" class="btn btn-warning btn-sm">
                                          <i class="icon-pencil"></i>
                                        </div> &nbsp;
                                        <template v-if="articulo.condicion">
                                            <div type="button" class="btn btn-danger btn-sm" @click="eliminarArticulo(articulo.id)">
                                                <i class="icon-trash"></i>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div type="button" class="btn btn-info btn-sm" @click="activarArticulo(articulo.id)">
                                                <i class="icon-check"></i>
                                            </div>
                                        </template>
                                    </td>
                                    <td v-text="articulo.nombre"></td>
                                    <td>

                                        <table v-if="articulo.subproductos.length" class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Opciones</th>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>Categoría</th>
                                                    <th>Precio Venta</th>
                                                    <th>Stock</th>
                                                    <th>Descripción</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="sarticulo in articulo.subproductos" :key="sarticulo.id">
                                                    <td>
                                                        <div type="button" @click="abrirModal('articulo','actualizar',sarticulo)" class="btn btn-warning btn-sm">
                                                        <i class="icon-pencil"></i>
                                                        </div> &nbsp;
                                                        <template v-if="sarticulo.condicion">
                                                            <div type="button" class="btn btn-danger btn-sm" @click="eliminarArticulo(sarticulo.id)">
                                                                <i class="icon-trash"></i>
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            <div type="button" class="btn btn-info btn-sm" @click="activarArticulo(sarticulo.id)">
                                                                <i class="icon-check"></i>
                                                            </div>
                                                        </template>
                                                    </td>
                                                    <td v-text="sarticulo.codigo"></td>
                                                    <td v-text="sarticulo.nombre"></td>
                                                    <td v-text="sarticulo.nombre_categoria"></td>
                                                    <td v-text="sarticulo.precio_venta"></td>
                                                    <td v-text="sarticulo.stock"></td>
                                                    <td v-text="sarticulo.descripcion"></td>
                                                    <td>
                                                        <div v-if="sarticulo.condicion">
                                                            <span class="badge badge-success">Activo</span>
                                                        </div>
                                                        <div v-else>
                                                            <span class="badge badge-danger">Desactivado</span>
                                                        </div>
                                                        
                                                    </td>
                                                    
                                                </tr>  
                                                                          
                                            </tbody>
                                        </table>
                                    
                                    </td>
                                </tr>  
                            </tbody>
                        </table>
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
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
            <!--Inicio del modal agregar/actualizar-->
            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-primary modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" v-text="tituloModal"></h4>
                            <div type="button" class="close" @click="cerrarModal()" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </div>
                        </div>
                        <div class="modal-body" >
                            <form @submit.prevent="accion"  enctype="multipart/form-data" class="form-horizontal">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label class="form-control-label" for="text-input">Categoría</label>
                                        <select class="form-control" v-model="idcategoria">
                                            <option value="0" disabled>Seleccione</option>
                                            <option v-for="categoria in arrayCategoria" :key="categoria.id" :value="categoria.id" v-text="categoria.nombre"></option>
                                        </select>                                        
                                    </div>
                                    
                                    <div class="form-group col-md-3">
                                         <!--Inicio modal listado articulos-->
                                        <div class="modal modal-articulos fade" tabindex="-1" :class="{'mostrar' : modala}" role="dialog" aria-labelledby="myModalLabel" style="display: none; z-index:10000" aria-hidden="true">
                                            <div class="modal-dialog" :class="{'modal-bajo':topedit}">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Seleccione un producto</h5>
                                                        <div type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cerrarModala()">
                                                        <span aria-hidden="true">&times;</span>
                                                        </div>
                                                    </div>
                                                    <div class="modal-body">
                                                        <template v-if="arrayArticulosBuscados.length">
                                                            <div class="list-group">
                                                                <a href="#" 
                                                                class="list-group-item list-group-item-action" 
                                                                :class="{'active' : articulo_padre_seleccionado}" 
                                                                v-for="(articulo,index) in arrayArticulosBuscados" 
                                                                :key="articulo.id" 
                                                                v-text="articulo.nombre"
                                                                @click="getDatosArticulo(articulo,index)">
                                                                </a> 
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            <div class="list-group">
                                                                No se encontro ningun producto que coincida con la búsqueda
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div type="button" class="btn btn-primary" @click="cerrarModala()" data-dismiss="modal">Cancelar</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- fin modal listado articulos -->
                                        <label class="form-control-label" for="text-input">Producto padre</label>
                                        <input type="text" class="form-control" v-model="buscar_articulo" @keyup="selectArticulo('nuevo')" placeholder="Ingrese nombre del producto">
                                        <div v-if="articulo_padre_seleccionado" class="art_padre_select">
                                            Producto padre: <div v-text="nombre_articulo_padre"></div>  
                                        </div>
                                        <div v-else>
                                            Este producto no tiene padre
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tipo Producto(*)</label>
                                            <select class="form-control" v-model="tipo_producto" >
                                                <option value="1">Producto Simple</option>
                                                <option value="2">Producto Variable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                         <label class="form-control-label" for="text-input">Nombre</label>
                                        <input type="text" v-model="nombre" class="form-control" placeholder="Nombre de artículo"> 
                                    </div>
                                    <div class="form-group col-md-4 imgP">
                                        <div>
                                            <label class="form-control-label" for="text-input">Imagen</label>
                                            <input type="file"  @change="obtenerImagen" accept="image/*" class="form-control" :key="fileInputKey" placeholder="Seleccione una imagen">                                        
                                        </div>
                                          <figure>
                                            <img width="100" v-if="tipoAccion==2" :src="imagenMin" alt="">
                                        </figure>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="form-control-label" for="text-input">Precio Venta</label>
                                        <input type="number" v-model="precio_venta" class="form-control" placeholder="">                                        
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label class="form-control-label" for="text-input">Stock</label>
                                        <input type="number" v-model="stock" class="form-control" placeholder="">                                        
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="form-control-label" for="text-input">Rangos</label>
                                        <div class="art_padre_select">
                                            <input type="number" v-model="rango.de" class="form-control" placeholder="de"> 
                                            <input type="number" v-model="rango.hasta" class="form-control" placeholder="hasta"> 
                                            <input type="number" v-model="rango.descuento" class="form-control" placeholder="Descuento"> 
                                            <i class="icon-plus btn" @click="agregarRango()"  style="cursor:pointer"></i> 
                                        </div>                                      
                                    </div>
                                    <div class="form-group col-md-4">
                                        <div v-if="rangos">
                                            <ul class="list-atributos">
                                                <li  class="btn" v-for="(rango,index) in rangos" :key="index">
                                                    <div class="atributo-item">
                                                        <div class="botones-atributo">
                                                            <a @click="eliminarRango(index)" type="button" title="Eliminar rango" >
                                                                <i class="icon-close"></i>
                                                            </a>
                                                            
                                                            <a v-if="rango.rangoEdit==0" @click="editarRango(index, rango)" type="button" title="Editar rango" >
                                                                <i class="icon-arrow-down"></i>
                                                            </a>
                                                            <a v-else @click="cerrarRango(index)" type="button" title="Editar atributo" >
                                                                <i class="icon-arrow-up"></i>
                                                            </a>
                                                        </div>   
                                                        <div class="mr-1 rangos" v-if="rango.rangoEdit==0">
                                                            <div class="form-control" @click="editarRango(index,rango)" v-text="rango.de">
                                                            </div>
                                                            <div class="form-control" v-text="rango.hasta"></div>
                                                            <div class="form-control" v-text="rango.descuento"></div>
                                                        </div>
                                                        <div v-else class="rangos">
                                                                <input type="number" v-model="rango.de" class="form-control" placeholder="de"> 
                                                                <input type="number" v-model="rango.hasta" class="form-control" placeholder="hasta"> 
                                                                <input type="number" v-model="rango.descuento" class="form-control" placeholder="Descuento"> 
                                                        </div>
                                                                
                                                    </div>
                                                    
                                                </li>
                                            </ul>
                                        </div>    
                                    </div>  
                                    <div class="form-group col-md-12">
                                        <label class="form-control-label" for="text-input">Descripción</label>
                                        <input type="text" v-model="descripcion" class="form-control" placeholder="Ingrese descripción">
                                    </div>
                                    <div  v-show="errorArticulo" class="form-grou col-md-12 div-error">
                                        <div class="text-center text-error">
                                            <div v-for="error in errorMostrarMsjArticulo" :key="error" v-text="error">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                           
                            <template>
                                <div class="card-body">
                                    <div class="form-group row border p-3">
                                        <div class="col-md-3">
                                            <label for="">Atributo nombre(*)</label>
                                            <input type="text" class="form-control" v-model="atributo.nombreAtributo">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">Valor atributo</label>
                                            <input type="text" class="form-control" v-model="atributo.valor">
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Descripción Atributo</label>
                                                <input type="text" class="form-control" v-model="atributo.descripcionAtributo" >
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Alerta atributo</label>
                                                <input type="text" class="form-control" v-model="atributo.alertaAtributo">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Tipo Campo(*)</label>
                                                <select class="form-control" v-model="atributo.tipoCampo" >
                                                    <option value="1" >Selección</option>
                                                    <option value="2">Las opciones dependen del la cantidad</option>
                                                    <option value="3">Numero</option>
                                                    <option value="4">Campo</option>
                                                    <option value="5">Area</option>
                                                    <option value="6">Valor sumado al total</option>
                                                    <option value="7">Valor depende de la catidad </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>Tipo Valor(*)</label>
                                                <select class="form-control" v-model="atributo.tipoValor">
                                                    <option value="1" selected>Valor</option>
                                                    <option value="2">Porcentaje</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Unidad de medida(*)</label>
                                                <select class="form-control" v-model="atributo.unidadmedida">
                                                    <option value="Metros">Metros</option>
                                                    <option value="Cm">Cm</option>
                                                    <option value="mm">mm</option>
                                                    <option value="Metros cuadrados">Metros Cuadrados</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>Operación(*)</label>
                                                <input type="text" class="form-control" v-model="atributo.operacion" placeholder="+, -, *, /">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Orden Atributo(*)</label>
                                                <input type="text" class="form-control" v-model="atributo.ordenAtributo">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Mínimo valor</label>
                                                <input type="text" class="form-control" v-model="atributo.minimoValor">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Máximo valor</label>
                                                <input type="text" class="form-control" v-model="atributo.maximoValor">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <div @click="agregarAtributo()" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></div>
                                            </div>
                                        </div>
                                        <div  v-show="errorAtributo" class="form-grou col-md-12 div-error">
                                            <div class="text-center text-error">
                                                <div v-for="error in errorMostrarMsjAtributo" :key="error" v-text="error">

                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="table-responsive col-md-12">
                                            <div v-if="arrayAtributo">
                                                <ul class="list-atributos">
                                                    <li  class="btn" v-for="(atri,index) in arrayAtributo" :key="index">
                                                        <div class="atributo-item">
                                                            <div class="botones-atributo">
                                                                <div @click="eliminarAtributo(index)" type="button" title="Eliminar atributo" class="btn btn-danger btn-sm">
                                                                    <i class="icon-close"></i>
                                                                </div>
                                                                
                                                                <div v-if="atri.atributoEdit==0" @click="editarAtributo(index, atri)" type="button" title="Editar atributo" class="btn btn-warning btn-sm">
                                                                    <i class="icon-arrow-down"></i>
                                                                </div>
                                                                <div v-else @click="cerrarAtributo(index, atri)" type="button" title="Editar atributo" class="btn btn-warning btn-sm">
                                                                    <i class="icon-arrow-up"></i>
                                                                </div>
                                                            </div>   
                                                            <div class="mr-1" v-if="atri.atributoEdit==0">
                                                                <div class="form-control atributo-nombre" @click="editarAtributo(index,atri)" v-text="atri.nombreAtributo">
                                                                </div>
                                                            </div>
                                                            <div v-else>
                                                                <div  class="atributo-editado">

                                                                    <div>
                                                                        <label for="">Atributo nombre(*)</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.nombreAtributo" >
                                                                    </div>
                                                                     <div>
                                                                        <label for="">Valor atributo</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.valor" >
                                                                    </div>
                                                                    <div>
                                                                        <label>Tipo Campo(*)</label>
                                                                        <select class="form-control" v-model="atributoSelect.tipoCampo" >
                                                                            <option value="1" >Selección</option>
                                                                            <option value="2">Las opciones dependen del la cantidad</option>
                                                                            <option value="3">Numero</option>
                                                                            <option value="4">Campo</option>
                                                                            <option value="5">Area</option>
                                                                            <option value="6">Valor sumado al total</option>
                                                                            <option value="7">Valor depende de la catidad </option>
                                                                        </select>
                                                                    </div>
                                                                    <div>
                                                                        <label>Tipo Valor(*)</label>
                                                                        <select class="form-control" v-model="atributoSelect.tipoValor">
                                                                            <option value="1" >Valor</option>
                                                                            <option value="2">Porcentaje</option>
                                                                        </select>
                                                                    </div>
                                                                    <div>
                                                                        <label>Descripción Atributo</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.descripcionAtributo">
                                                                    </div>
                                                                    <div>
                                                                        <label>Alerta Atributo</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.alertaAtributo">
                                                                    </div>
                                                                    <div>
                                                                        <label>Orden Atributo(*)</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.ordenAtributo">
                                                                    </div>
                                                                    <div>
                                                                        <label>Operación(*)</label>
                                                                        <input type="text" class="form-control"  v-model="atributoSelect.operacion">
                                                                    </div>
                                                                    
                                                                    <div>
                                                                        <label>Unidad de medida(*)</label>
                                                                        <select class="form-control"  v-model="atributoSelect.unidadmedida">
                                                                            <option value="Metros">Metros</option>
                                                                            <option value="Cm">Cm</option>
                                                                            <option value="mm">mm</option>
                                                                            <option value="Metros cuadrados">Metros Cuadrados</option>
                                                                        </select>
                                                                    </div>
                                                                    <div>
                                                                        <label>Mínimo valor</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.minimoValor">
                                                                    </div>
                                                                    <div>
                                                                        <label>Máximo valor</label>
                                                                        <input type="text" class="form-control" v-model="atributoSelect.maximoValor">
                                                                    </div>

                                                                </div>
                                                                 <div  class="col-md-12 opciones-atributo">
                                                                    <div class="col-md-12">
                                                                        <h5 class="titulo-3"> Opciones de Atributos</h5>
                                                                    </div>
                                                                        <div class="card-body col-md-12">
                                                                            <div class="form-group row p-3">
                                                                                <div class="col-md-12 titulo-4">
                                                                                Atributo {{atributoSelect.nombreAtributo}}
                                                                                </div>
                                                                                
                                                                                <div class="col-md-2">
                                                                                    <label>Label opción(*)</label>
                                                                                    <input type="text" class="form-control" v-model="opcionesAtributo.labelOpAtributo">
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label>Valor opción(*)</label>
                                                                                    <input type="text" class="form-control" v-model="opcionesAtributo.valorOpAtributo">
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label>Alerta opción</label>
                                                                                    <input type="text" class="form-control" v-model="opcionesAtributo.alertaOpAtributo">
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label>Descripción opción</label>
                                                                                    <input type="text" class="form-control" v-model="opcionesAtributo.descripcionOpAtributo">
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label>Posición(*)</label>
                                                                                    <input type="text" class="form-control" v-model="opcionesAtributo.posicionOpAtributo">
                                                                                </div>
                                                                                <div class="col-md-2" >
                                                                                    <div class="form-group">
                                                                                        <div @click="agregarOpcion(index)" class="btn btn-success form-control btnagregar"><i class="icon-plus"></i></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div  v-show="errorOpcion" class="form-grou col-md-12 div-error">
                                                                                    <div class="text-center text-error">
                                                                                        <div v-for="error in errorMostrarMsjOpcion" :key="error" v-text="error">

                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="table-responsive col-md-12">
                                                                                    <div v-if="atri.opciones.length">
                                                                                        <ul class="list-atributos">
                                                                                            <li v-for="(opcion,i) in atri.opciones" :key="i">
                                                                                                <div class="atributo-item">
                                                                                                    <div class="botones-atributo">
                                                                                                        <div @click="eliminarOpAtributo(i,index)" type="button" title="Eliminar opción" class="btn btn-danger btn-sm">
                                                                                                            <i class="icon-close"></i>
                                                                                                        </div>
                                                                                                        
                                                                                                        <div v-if="opcion.editOpcion==0" @click="editarOpAtributo(index, opcion, i)" type="button" title="Editar opción" class="btn btn-warning btn-sm">
                                                                                                            <i class="icon-arrow-down"></i>
                                                                                                        </div>
                                                                                                        <div v-else @click="cerrarOpAtributo(index, i)" type="button" title="Cerrar opción" class="btn btn-success btn-sm">
                                                                                                            <i class="icon-check"></i>
                                                                                                        </div>
                                                                                                    </div>   
                                                                                                    <div class="mr-1" v-if="opcion.editOpcion==0">
                                                                                                        <div class="form-control atributo-nombre" @click="editarOpAtributo(index, opcion, i)" v-text="opcion.labelOpAtributo">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div v-else>
                                                                                                        <div  class="atributo-editado">

                                                                                                            <div class="col-md-2">
                                                                                                                <label>Label opción(*)</label>
                                                                                                                <input type="hidden"  class="form-control" v-model="opcionesAtributoSelect.idOpAtributo">
                                                                                                                <input type="text"  class="form-control" v-model="opcionesAtributoSelect.labelOpAtributo">
                                                                                                            </div>
                                                                                                            <div class="col-md-2">
                                                                                                                <label>Valor opción(*)</label>
                                                                                                                <input type="text" class="form-control" v-model="opcionesAtributoSelect.valorOpAtributo">
                                                                                                            </div>
                                                                                                            <div class="col-md-2">
                                                                                                                <label>Alerta opción</label>
                                                                                                                <input type="text" class="form-control" v-model="opcionesAtributoSelect.alertaOpAtributo">
                                                                                                            </div>
                                                                                                            <div class="col-md-4">
                                                                                                                <label>Descripción opción</label>
                                                                                                                <input type="text" class="form-control" v-model="opcionesAtributoSelect.descripcionOpAtributo">
                                                                                                            </div>
                                                                                                            <div class="col-md-2">
                                                                                                                <label>Posición(*)</label>
                                                                                                                <input type="text" class="form-control" v-model="opcionesAtributoSelect.posicionOpAtributo">
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                        
                                                                                                </div>
                                                                                                
                                                                                            </li>
                                                                                        </ul>
                                                                                    </div>
                                                                                    <div v-else>
                                                                                        <ul class="list-atributos">
                                                                                            <li> 
                                                                                                No hay opciones atributos agregados
                                                                                            </li>
                                                                                        </ul>  
                                                                                    </div>                                
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                            </div>
                                                                
                                                            </div>
                                                                 
                                                        </div>
                                                        
                                                    </li>
                                                </ul>
                                            </div>
                                            <div v-else>
                                                <ul class="list-atributos">
                                                    <li> 
                                                        No hay atributos agregados
                                                    </li>
                                                </ul>  
                                            </div>                                
                                        </div>
                                        <!-- v-if="arrayAtributo.length" -->
                                       
                                    </div>
                                    <!-- <div class="form-group row">
                                        <div class="col-md-12">
                                            <div type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</div>
                                            <div type="button" class="btn btn-primary" @click="registrarIngreso()">Registrar Compra</div>
                                        </div>
                                    </div> -->
                                </div>
                            </template>
                             
                             <div class="modal-footer">
                                <div type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</div>
                                <input type="submit" v-if="tipoAccion==1" class="btn btn-primary" value="Guardar"/>
                                <input type="submit" v-if="tipoAccion==2" class="btn btn-primary" value="Actualizar"/>
                            
                            </div>
                            </form>
                        </div>
                        
                       
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!--Fin del modal-->
            
            
        </main>
</template>

<script>
    import VueBarcode from 'vue-barcode';
   
    export default {
        data (){
            return {
                articulo_id: 0,
                idcategoria : 0,
                nombre_categoria : '',
                codigo : '',
                tipo_producto:1,
                nombre : '',
                imagen:'',
                imagenMin:'',
                precio_venta : 100,
                stock : 100,
                descripcion : '',
                rango:{'de':0,'hasta':0,'descuento':0},
                rangos:[],
                rangoSelect:{'de':0,'hasta':0,'descuento':0},
                arrayArticulo : [],
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                errorArticulo : 0,
                errorMostrarMsjArticulo : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : 'nombre',
                buscar : '',
                arrayCategoria :[],
                atributo:{id:'',nombreAtributo:'Papel','valor':0,tipoCampo:1, descripcionAtributo:'', tipoValor:1, operacion:'+', unidadmedida:'Cm', ordenAtributo:1,
                alertaAtributo:'', minimoValor:'',  maximoValor:''},
                atributoSelect:{id:'',nombreAtributo:'','valor':0,tipoCampo:1, descripcionAtributo:'', tipoValor:1, operacion:'+', unidadmedida:'Cm', ordenAtributo:'',
                alertaAtributo:'', minimoValor:'',  maximoValor:''},
                arrayAtributo:[],
                arrayOpAtributo:'',
                opcionesAtributo:{'id':'','labelOpAtributo':'Carta','valorOpAtributo':'','descripcionOpAtributo':'','alertaOpAtributo':'','posicionOpAtributo':1,'editOpcion':0},
                opcionesAtributoSelect:{'id':'','labelOpAtributo':'','valorOpAtributo':'','descripcionOpAtributo':'','alertaOpAtributo':'','posicionOpAtributo':1,'editOpcion':0},
                errorAtributo:0,
                errorMostrarMsjAtributo:[],
                errorOpcion:0,
                errorMostrarMsjOpcion:[],
                atributoIndex:0,
                topedit:0,
                buscar_articulo:'',
                modala:0,
                arrayArticulosBuscados:[],
                aseleccionado:false,
                idarticulo_padre:0,
                nombre_articulo_padre:'',
                articulo_padre_seleccionado:'',
                fileInputKey:0,
            }
        },
        components: {
            'barcode': VueBarcode
        },
        computed:{
            accion(){
                if(this.tipoAccion==1){
                    return this.registrarArticulo
                }else{
                    return this.actualizarArticulo
                }
            },
            imagenM(){
                return this.imagenMin
            },  
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

            }
        },
        methods : {
            anchoColum() {
                let me =this
                let ancholista=me.$refs.articulo2.clientWidth
                let w=ancholista/me.anchoCol
                    return { width: `${w}px` };
            }, 
            listarArticulo (page,buscar,criterio){
                let me=this;
                var url= '/articulo?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayArticulo = respuesta.articulos;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
             
            },
            obtenerImagen(e){
                let file=e.target.files[0]
                this.imagen=file
                this.cargarImagen(this.imagen)

            },
            cargarImagen(file){
                let reader=new FileReader()
                reader.onload=(e)=>{
                    this.imagenMin=e.target.result
                }
                reader.readAsDataURL(file)
            },
            selectCategoria(){
                let me=this;
                var url= '/categoria/selectCategoria';
                axios.get(url).then(function (response) {
                    //console.log(response);
                    var respuesta= response.data;
                    me.arrayCategoria = respuesta.categorias;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            selectArticulo(action){
                let me=this;
                if(action=='edit'){
                    me.topedit=1
                }
                var url= '/articulo/selectArticulo?filtro='+this.buscar_articulo;
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.arrayArticulosBuscados=respuesta.articulos;
                    me.modala=1
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getDatosArticulo(val1, index){
                let me = this;
                me.idarticulo_padre = val1.id;
                me.nombre_articulo_padre=val1.nombre;
                me.articulo_padre_seleccionado=val1;
                me.buscar_articulo=''
                me.aseleccionado=true
                me.arrayArticulosBuscados=[];
                me.modala=0
            },
            cerrarModala(){
                this.modala=0
                this.arrayArticulosBuscados=[]
            },
            agregarAtributo(){
                 if (this.validarAtributo()){
                    return;
                }
                let me=this
                if(me.idarticulo==0 || me.cantidad==0 || me.precio==0){

                }else{
                        me.arrayAtributo.push({
                            id:'',
                            nombreAtributo:me.atributo.nombreAtributo,
                            valor:me.atributo.valor,
                            tipoCampo:me.atributo.tipoCampo,
                            descripcionAtributo:me.atributo.descripcionAtributo,
                            tipoValor:me.atributo.tipoValor,
                            operacion:me.atributo.operacion,
                            unidadmedida:me.atributo.unidadmedida,
                            ordenAtributo:me.atributo.ordenAtributo,
                            alertaAtributo:me.atributo.alertaAtributo,
                            minimoValor:me.atributo.minimoValor,
                            maximoValor:me.atributo.maximoValor,
                            atributoEdit:0,
                            opciones:[]
                        }) 
                        me.atributo.nombreAtributo=''
                        me.atributo.valor=0
                        me.atributo.tipoCampo=0
                        me.atributo.descripcionAtributo=''
                        me.atributo.tipoValor=0
                        me.atributo.operacion=''
                        me.atributo.unidadmedida=''
                        me.atributo.ordenAtributo=0
                        me.atributo.alertaAtributo=''
                        me.atributo.minimoValor=''
                        me.atributo.maximoValor=''
                       
                }
               
                
            },
            eliminarAtributo(index){
                let me=this
                me.arrayAtributo.splice(index,1)
            },
            editarAtributo(index,atributo){
                let me=this
                console.log(atributo)
                me.atributoSelect=atributo
                me.atributoSelect.atributoEdit=1
                me.arrayAtributo.forEach((element,i) => {
                    if(index==i){
                        me.arrayAtributo.splice(index,1,me.atributoSelect) 
                    }else{
                        me.arrayAtributo[i].atributoEdit=0
                    }
                    
                });                         
                                                            
            },
            cerrarAtributo(index, atributo){
                let me=this
                let atributoSeleccionado={
                    id:me.atributoSelect.id,
                    nombreAtributo:me.atributoSelect.nombreAtributo,
                    valor:me.atributoSelect.valor,
                    tipoCampo:me.atributoSelect.tipoCampo,
                    descripcionAtributo:me.atributoSelect.descripcionAtributo,
                    tipoValor:me.atributoSelect.tipoValor,
                    operacion:me.atributoSelect.operacion,
                    unidadmedida:me.atributoSelect.unidadmedida,
                    ordenAtributo:me.atributoSelect.ordenAtributo,
                    alertaAtributo:me.atributoSelect.alertaAtributo,
                    minimoValor:me.atributoSelect.minimoValor,
                    maximoValor:me.atributoSelect.maximoValor,
                    opciones:me.atributoSelect.opciones,
                    atributoEdit:0
                }                             
                me.arrayAtributo.splice(index,1,atributoSeleccionado)          
            },
            agregarOpcion(index){
                if (this.validarOpcion()){
                     return;
                }
                let me=this
                if(me.idarticulo==0 || me.cantidad==0 || me.precio==0){

                }else{
                        me.arrayOpAtributo={
                            'id':me.opcionesAtributo.id,
                            'labelOpAtributo':me.opcionesAtributo.labelOpAtributo,
                            'valorOpAtributo':me.opcionesAtributo.valorOpAtributo,
                            'descripcionOpAtributo':me.opcionesAtributo.descripcionOpAtributo,
                            'alertaOpAtributo':me.opcionesAtributo.alertaOpAtributo,
                            'posicionOpAtributo':me.opcionesAtributo.posicionOpAtributo,
                            'editOpcion':0   
                        }
                        me.arrayAtributo[index].opciones.push(me.arrayOpAtributo) 
                        me.opcionesAtributo.labelOpAtributo=''
                        me.opcionesAtributo.valorOpAtributo=''
                        me.opcionesAtributo.idOpAtributo=''
                        me.opcionesAtributo.descripcionOpAtributo=''
                        me.opcionesAtributo.alertaOpAtributo=''
                        me.opcionesAtributo.posicionOpAtributo=''
                }
               
                
            },
            
            eliminarOpAtributo(i,index){
                let me=this
                me.arrayAtributo[index].opciones.splice(i,1)
            },
            editarOpAtributo(index,opcion,ind){
                let me=this
                me.opcionesAtributoSelect=opcion
                let op={
                    'id':me.opcionesAtributoSelect.id,
                    'labelOpAtributo':me.opcionesAtributoSelect.labelOpAtributo,
                    'valorOpAtributo':me.opcionesAtributoSelect.valorOpAtributo,
                    'descripcionOpAtributo':me.opcionesAtributoSelect.descripcionOpAtributo,
                    'alertaOpAtributo':me.opcionesAtributoSelect.alertaOpAtributo,
                    'posicionOpAtributo':me.opcionesAtributoSelect.posicionOpAtributo,
                    'editOpcion':1   
                }
                me.arrayAtributo[index].opciones.forEach((element,i) => {
                    if(ind==i){
                        me.arrayAtributo[index].opciones.splice(ind,1,op) 
                    }else{
                        me.arrayAtributo[index].opciones[i].editOpcion=0
                    }
                    
                });                         
                                                            
            },
            cerrarOpAtributo(index,i){
                let me=this
                let opcionSeleccionado={
                    'id':me.opcionesAtributoSelect.id,
                    'labelOpAtributo':me.opcionesAtributoSelect.labelOpAtributo,
                    'valorOpAtributo':me.opcionesAtributoSelect.valorOpAtributo,
                    'descripcionOpAtributo':me.opcionesAtributoSelect.descripcionOpAtributo,
                    'alertaOpAtributo':me.opcionesAtributoSelect.alertaOpAtributo,
                    'posicionOpAtributo':me.opcionesAtributoSelect.posicionOpAtributo,
                    'editOpcion':0  
                }                             
                me.arrayAtributo[index].opciones.splice(i,1,opcionSeleccionado)          
            },
            agregarRango(index){
                // if (this.validarRango()){
                //      return;
                // }
                let me=this
                if(me.idarticulo==0 || me.cantidad==0 || me.precio==0){

                }else{
                        me.rangos.push({
                            'de':me.rango.de,
                            'hasta':me.rango.hasta,
                            'descuento':me.rango.descuento,
                            'rangoEdit':0   
                        }) 
                        me.rango.de=''
                        me.rango.hasta=''
                        me.rango.descuento=''
                        
                }
               
                
            },
            eliminarRango(index){
                let me=this
                me.rangos.splice(index,1)
            },
            editarRango(index,rango){
                 let me=this
                me.rangoSelect=rango
                me.rangoSelect.rangoEdit=1
                me.rangos.forEach((element,i) => {
                    if(index==i){
                        me.rangos.splice(index,1,me.rangoSelect) 
                    }else{
                        me.rangos[i].rangoEdit=0
                    }
                    
                });   
            },
            cerrarRango(index){
                let me=this
                let rangoSeleccionado={
                    de:me.rangoSelect.de,
                    hasta:me.rangoSelect.hasta,
                    descuento:me.rangoSelect.descuento,
                    rangoEdit:0
                }                             
                me.rangos.splice(index,1,rangoSeleccionado)    
            },
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarArticulo(page,buscar,criterio);
            },
            
            
            registrarArticulo(){
                if (this.validarArticulo()){
                    return;
                }
                
                let me = this;
                // this.rangos=[{"de":"1","hasta":"10","descuento":"0","rangoEdit":0},{"de":"11","hasta":"20","descuento":"7","rangoEdit":0},{"de":"21","hasta":"30","descuento":"10","rangoEdit":0},{"de":"31","hasta":"40","descuento":"13","rangoEdit":0},{"de":"41","hasta":"50","descuento":"17","rangoEdit":0},{"de":"51","hasta":"60","descuento":"20","rangoEdit":0},{"de":"61","hasta":"70","descuento":"23","rangoEdit":0},{"de":"71","hasta":"80","descuento":"26","rangoEdit":0},{"de":"81","hasta":"90","descuento":"30","rangoEdit":0},{"de":"91","hasta":"100","descuento":"33","rangoEdit":0},{"de":"101","hasta":"200","descuento":"36","rangoEdit":0},{"de":"201","hasta":"10000","descuento":"46","rangoEdit":0}]
                const img = new FormData()
                img.set('imagen',this.imagen)
                const datos = new FormData()
                datos.set('idcategoria',this.idcategoria)
                datos.set('idarticulo_padre',this.idarticulo_padre)
                datos.set('codigo',this.codigo)
                datos.set('tipo_producto',this.tipo_producto)
                datos.set('nombre',this.nombre)
                datos.set('imagen',this.imagen)
                datos.set('rangos',JSON.stringify(this.rangos))
                datos.set('stock', this.stock)
                datos.set('precio_venta', this.precio_venta)
                datos.set('descripcion', this.descripcion)
                datos.set( 'atributos',JSON.stringify(this.arrayAtributo))
                axios.post('/articulo/registrar',datos)
                .then(function (response) {
                    console.log(response)
                    me.arrayAtributo=[];
                    me.arrayOpAtributo=[];
                    me.rangos=[];
                    me.articulo_padre_seleccionado={};
                    me.cerrarModal();
                    me.listarArticulo(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                });
            },
            actualizarArticulo(){
               if (this.validarArticulo()){
                    return;
                }
                let me = this;
                const datos = new FormData()
                datos.set('_method', 'PUT')
                datos.set('id',this.articulo_id)
                datos.set('idcategoria',this.idcategoria)
                datos.set('idarticulo_padre',this.idarticulo_padre)
                datos.set('codigo',this.codigo)
                datos.set('tipo_producto',this.tipo_producto)
                datos.set('nombre',this.nombre)
                datos.set('imagen',this.imagen)
                datos.set('rangos',JSON.stringify(this.rangos))
                datos.set('stock', this.stock)
                datos.set('precio_venta', this.precio_venta)
                datos.set('descripcion', this.descripcion)
                datos.set( 'atributos',JSON.stringify(this.arrayAtributo))
                axios.post('/articulo/actualizar',datos)
                .then(function (response) {
                    me.arrayAtributo=[]
                    me.arrayOpAtributo=[]
                    me.rangos=[]
                    me.articulo_padre_seleccionado={};
                    me.cerrarModal()
                    me.listarArticulo(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                })
            },
            eliminarArticulo(id){
               const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de borrar este artículo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;
                    var url= '/articulo/eliminar?id='+ id;
                    axios.delete(url,{'_method': 'DELETE'})
                    .then(function (response) {
                        me.listarArticulo(1,'','nombre');
                        console.log(response)
                        swal(
                        'Eliminado!',
                        'El registro ha sido eliminado con éxito.',
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
            activarArticulo(id){
               const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Esta seguro de activar este artículo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios.put('/articulo/activar',{
                        'id': id
                    }).then(function (response) {
                        me.listarArticulo(1,'','nombre');
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
            validarArticulo(){
                this.errorArticulo=0;
                this.errorMostrarMsjArticulo =[];

                if (this.idcategoria==0) this.errorMostrarMsjArticulo.push("Seleccione una categoría.");
                if (!this.nombre) this.errorMostrarMsjArticulo.push("El nombre del artículo no puede estar vacío.");
                if (!this.stock) this.errorMostrarMsjArticulo.push("El stock del artículo debe ser un número y no puede estar vacío.");
                if (!this.precio_venta) this.errorMostrarMsjArticulo.push("El precio venta del artículo debe ser un número y no puede estar vacío.");

                if (this.errorMostrarMsjArticulo.length) this.errorArticulo = 1;

                return this.errorArticulo;
            },
            validarOpcion(){
                this.errorOpcion=0;
                this.errorMostrarMsjOpcion =[];
                if (!this.opcionesAtributo.labelOpAtributo) this.errorMostrarMsjOpcion.push("La etiqueta de la opción no puede estar vacia.");
                if (!this.opcionesAtributo.valorOpAtributo) this.errorMostrarMsjOpcion.push("El valor del la opción no puede estar vacia");
                if (!this.opcionesAtributo.posicionOpAtributo) this.errorMostrarMsjOpcion.push("La posición de la opción no puede estar vacío.");

                if (this.errorMostrarMsjOpcion.length) this.errorOpcion = 1;

                return this.errorOpcion;
            },
             validarAtributo(){
                this.errorAtributo=0;
                this.errorMostrarMsjAtributo =[];
                if (!this.atributo.nombreAtributo) this.errorMostrarMsjAtributo.push("El nombre del atributo no puede estar vacío.");
                if (!this.atributo.operacion) this.errorMostrarMsjAtributo.push("El operador no puede estar vacio");
                if (!this.atributo.ordenAtributo) this.errorMostrarMsjAtributo.push("El orden del atributo no puede estar vacío.");

                if (this.errorMostrarMsjAtributo.length) this.errorAtributo = 1;

                return this.errorAtributo;
            },
            cerrarModal(){
                this.modal=0
                this.tituloModal=''
                this.idarticulo_padre=''
                this.articulo_padre_seleccionado=''
                this.nombre=''
                this.imagenMin=''
                this.fileInputKey++;
                this.descripcion=''
                this.arrayAtributo=[]
                this.arrayOpAtributo=[]
                this.rangos=[]
            },
            abrirModal(modelo, accion, data = []){
                switch(modelo){
                    case "articulo":
                    {
                        switch(accion){
                            case 'registrar':
                            {
                                this.modal = 1;
                                this.tituloModal = 'Registrar Artículo';
                                this.idcategoria=0;
                                this.nombre_categoria='';
                                this.codigo='';
                                this.nombre= '';
                                this.precio_venta=0;
                                this.stock=0;
                                this.descripcion = '';
                                this.tipoAccion = 1;
                                break;
                            }
                            case 'actualizar':
                            {
                                this.modal=1;
                                this.tituloModal='Actualizar Artículo';
                                this.tipoAccion=2;
                                this.articulo_id=data['id'];
                                this.idcategoria=data['idcategoria'];
                                this.idarticulo_padre=data['id_item_padre'];
                                this.codigo=data['codigo'];
                                this.nombre = data['nombre'];
                                this.stock=data['stock'];
                                this.precio_venta=data['precio_venta'];
                                this.descripcion= data['descripcion'];
                                if(data['rangos']==null){
                                    this.rangos=[]
                                }else{
                                    this.rangos=data['rangos'];
                                }
                                this.imagen=data['imagen']
                                this.imagenMin='img/productos/'+data['imagen']
                                if(data['atributos']){
                                    data['atributos'].forEach((e)=>{
                                        //console.log(e)
                                        var atr={
                                            id:e.id,
                                            nombreAtributo:e.nombre,
                                            valor:e.valor,
                                            tipoCampo:e.tipo_campo,
                                            descripcionAtributo:e.descripcion,
                                            tipoValor:e.tipo_valor,
                                            operacion:e.operacion,
                                            unidadmedida:e.unidad_medida,
                                            ordenAtributo:e.orden,
                                            alertaAtributo:e.alerta,
                                            minimoValor:e.minimo,
                                            maximoValor:e.maximo,
                                            atributoEdit:0,
                                            opciones:''
                                        }
                                        var opciones=[];
                                        e.opciones_atributo.forEach((o)=>{
                                            var op={
                                                'id':o.id,
                                                'labelOpAtributo':o.label,
                                                'valorOpAtributo':o.valor,
                                                'descripcionOpAtributo':o.descripcion,
                                                'alertaOpAtributo':o.alerta,
                                                'posicionOpAtributo':o.orden,
                                                'editOpcion':0  
                                            }
                                            opciones.push(op)
                                        })
                                        atr.opciones=opciones
                                        this.arrayAtributo.push(atr)
                                    })
                                }
                                break;
                            }
                        }
                    }
                }
                this.selectCategoria();
            }
        },
        mounted() {
            this.listarArticulo(1,this.buscar,this.criterio);
        }
    }
</script>
<style>    
     .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
    .mostrar{
        display: list-item !important;
        opacity: 1 !important;
        position: absolute !important;
        background-color: #3c29297a !important;
    }
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
        padding: 10px;
        border: solid 1px red;
        margin: 10px;
    }
     .modal-bajo{
        top:30%;
    }
    .art_padre_select{
        display: flex;
        justify-content: space-between;
    }
    .table-subproducto{
        position: absolute;
        left: 20px;
        top: 200px;
        z-index: 10;
        box-shadow: 5px 5px 5px rgb(0 0 0 / 30%);
        width: 97%;
    }
    .imgP{
        display:flex;
        justify-content: space-between;
    }
</style>

