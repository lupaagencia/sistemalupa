<template>
            <main class="main">
               

            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="">
                    <div class="contenedor-header">
                        <div>
                            <i class="fa fa-align-justify"></i> Artículos
                            <button type="button" @click="abrirModal('articulo','registrar')" class="btn btn-success boton-principal">
                                <i class="icon-plus"></i>&nbsp;Nuevo
                            </button>
                            <button type="button" @click="abrirModalImagenesMasivas()" class="btn btn-info text-white ml-2">
                                <i class="fa fa-file-image-o"></i>&nbsp;Imágenes Masivas
                            </button>
                            <button v-if="articulosSeleccionados.length > 0" type="button" @click="abrirModalAsignacionMasiva()" class="btn btn-warning text-dark font-weight-bold ml-2">
                                <i class="fa fa-tags"></i>&nbsp;Asignar Categorías ({{ articulosSeleccionados.length }} selec.)
                            </button>
                        </div>
                    </div>
                    <div class="contenedor-seccion">
                        <div class="form-group row align-items-end">
                            <div class="col-md-7">
                                <label>Búsqueda</label>
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                      <option value="nombre">Nombre</option>
                                      <option value="descripcion">Descripción</option>
                                    </select>
                                    <input type="text" v-model="buscar" @keyup.enter="listarArticulo(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                                    <input type="text" v-model="buscar_etiqueta" @keyup.enter="listarArticulo(1,buscar,criterio)" class="form-control ml-1" placeholder="Filtrar por etiqueta...">
                                    <button type="button" @click="listarArticulo(1,buscar,criterio)" class="btn btn-primary ml-1"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="excel">Importar Datos</label>
                                <input id="excel" type="file" @change="obtenerExcel()" class="form-control" accept=".xls, .xlsx" >
                            </div>
                            <div class="col-md-2 mt-2">
                                <button class="btn btn-success btn-block" @click="descargarExcel()"><i class="fa fa-download"></i> Descargar Excel</button>
                            </div>
                        </div>
                        <table v-if="tabla==1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 35px;" class="text-center">
                                        <input type="checkbox" v-model="seleccionarTodos" @change="toggleSeleccionarTodos" title="Seleccionar todos los artículos de esta página">
                                    </th>
                                    <th>ID</th>
                                    <th>Opciones</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Subproductos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="articulo in arrayArticulo" :key="articulo.id" ref="articulo2">
                                    <td class="text-center">
                                        <input type="checkbox" :value="articulo.id" v-model="articulosSeleccionados">
                                    </td>
                                    <td v-text="articulo.id"></td>
                                    <td class="btnOpciones">
                                        <div type="button" @click="abrirModal('articulo','actualizar',articulo)" class="btn btn-warning btn-sm" title="Editar Artículo">
                                          <i class="icon-pencil"></i>
                                        </div> &nbsp;
                                        <div type="button" @click="abrirModalAsignacionRapida(articulo)" class="btn btn-secondary btn-sm" title="Asignación Rápida de Categorías">
                                          <i class="fa fa-tags"></i>
                                        </div> &nbsp;
                                        <div type="button" @click="abrirModalTroquel(articulo)" class="btn btn-info btn-sm" title="Líneas de Troquel">
                                          <i class="fa fa-scissors"></i>
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
                                    <td>
                                        <div v-if="articulo.imagenes.length>0" >

                                            <figure v-for="img in articulo.imagenes" style="margin:0 0 0 0" :key="img.id">
                                                <img v-if="img.orden==1" :src="`img/productos/${img.nombre}`" width="100px" alt="">
                                            </figure> 
                                        </div>
                                    </td>
                                    <td v-text="articulo.nombre"></td>
                                    <td v-text="articulo.tamano"></td>
                                    <td>

                                        <table v-if="articulo.subproductos && articulo.subproductos.length > 0" class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Opciones</th>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>Tamaño</th>
                                                    <th>Categorías</th>
                                                    <th>Precio Venta</th>
                                                    <th>Stock</th>
                                                    <th>Descripción</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="sarticulo in (articulo.subproductos || [])" :key="sarticulo.id">
                                                    <td>
                                                        <div type="button" @click="abrirModal('articulo','actualizar',sarticulo)" class="btn btn-warning btn-sm" title="Editar Subproducto">
                                                          <i class="icon-pencil"></i>
                                                        </div> &nbsp;
                                                        <div type="button" @click="abrirModalTroquel(sarticulo)" class="btn btn-info btn-sm" title="Líneas de Troquel">
                                                          <i class="fa fa-scissors"></i>
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
                                                    <td v-text="sarticulo.tamano"></td>
                                                    <td>
                                                        <template v-if="sarticulo.categorias && sarticulo.categorias.length > 0">
                                                            <span v-for="cat in sarticulo.categorias" :key="cat.id" class="badge badge-primary mr-1 mb-1 d-inline-block">{{ cat.nombre }}</span>
                                                        </template>
                                                        <template v-else-if="sarticulo.nombre_categoria">
                                                            <span class="badge badge-secondary">{{ sarticulo.nombre_categoria }}</span>
                                                        </template>
                                                    </td>
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
                        <table v-else class="table table-bordered table-striped table-sm">
                            <tr>
                                <td colspan="4">
                                    <button @click="cancelarImportacion()" class="btn btn-warning btn-sm">Cerrar</button>
                                    <button @click="realizarCambio()" class="btn btn-primary btn-sm">Aplicar</button>
                                </td>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de producto</th>
                                <Th> Valor</Th>
                                <Th>  Descripción</Th>
                                <Th>  Tamaño</Th>
                            </tr>
                            <tr v-for="(dato,index) in datosExcel" :key="index">
                                <td>{{dato[0]}}</td>
                                <td>{{dato[1]}}</td>
                                <td>{{dato[2]}}</td>
                                <td>{{dato[3]}}</td>
                                <td>{{dato[4]}}</td>
                            </tr>
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
                                        <label class="form-control-label font-weight-bold" for="text-input">Categorías (Múltiple)</label>
                                        <div style="max-height: 120px; overflow-y: auto; border: 1px solid #ced4da; padding: 6px 10px; border-radius: 4px; background-color: #fff;">
                                            <div v-for="categoria in arrayCategoria" :key="categoria.id" class="form-check py-1">
                                                <input class="form-check-input" type="checkbox" :value="categoria.id" v-model="articulo.categorias_ids" :id="'cat_'+categoria.id">
                                                <label class="form-check-label cursor-pointer" :for="'cat_'+categoria.id">
                                                    {{ categoria.nombre }}
                                                </label>
                                            </div>
                                        </div>
                                        <small class="text-muted" v-if="articulo.categorias_ids && articulo.categorias_ids.length > 0">
                                            {{ articulo.categorias_ids.length }} categoría(s) seleccionada(s)
                                        </small>
                                    </div>
                                    
                                    <div class="form-group col-md-2">
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
                                        <div v-if="articulo.subarticulo" class="art_padre_select">
                                            Producto padre: <div v-text="articulo.subarticulo"></div>  
                                        </div>
                                        <div v-else>
                                            Este producto no tiene padre
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tipo Producto(*)</label>
                                            <select class="form-control" v-model="articulo.tipo_producto_id">
                                                <option value="1">Caja plegadiza</option>
                                                <option value="2">Lonchera</option>
                                                <option value="3">Caja pega lineal</option>
                                                <option value="4">Caja con pega laterales</option>
                                                <option value="5">Bolsa dimicilio fuelle</option>
                                                <option value="6">Bolsa sencilla</option>
                                                <option value="7">Bolsa boutique</option>
                                                <option value="8">Bolsillo antigrasa</option>
                                                <option value="9">Papel antigrasa</option>
                                                <option value="10">Producto de distribución</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                         <label class="form-control-label" for="text-input">Nombre</label>
                                        <input type="text" v-model="articulo.nombre" class="form-control" placeholder="Nombre de artículo"> 
                                    </div>
                                   
                                    <div  class="form-group col-md-4 imgP">
                                        <div>
                                            <label class="form-control-label" for="text-input">Imagen</label>
                                            <input type="file"  @change="obtenerImagen" accept="image/*" class="form-control" :key="fileInputKey" placeholder="Seleccione una imagen"> 
                                            <input type="text" v-model="ordenimg" placeholder="Orden de la imagen"/>                                       
                                        </div>
                                    </div>
                                    
                                   
                                    <div v-if="this.imagenMin.length>0" class="form-group col-md-3 imgM">
                                        <figure class="mini" v-for="(img,index) in imagenMin" :key="index">
                                            <i class="fa fa-trash" @click="eliminarImagenTemp(index)"></i>
                                            <img  width="100"  :src="img.file" alt="">
                                            <input type="text" v-model="img.orden"/>
                                        </figure>
                            
                                    </div>
                                    <div v-if="articulo.imagenes" class="form-group col-md-5 imgM">
                                        <figure class="mini-G" v-for="(img,index) in articulo.imagenes" :key="index">
                                            <i class="fa fa-trash text-danger cursor-pointer" @click="eliminarImagen(img, index)" title="Eliminar Imagen"></i>
                                            <img  width="100"  :src="`img/productos/${img.nombre}`" alt="">
                                            <input type="text" v-model="img.orden"/>
                                        </figure>
                                    </div>
                                    <!-- <div class="form-group col-md-2">
                                        <label class="form-control-label" for="text-input">Iva</label>
                                        <input type="number" v-model="articulo.iva" class="form-control" placeholder="">                                        
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="form-control-label" for="text-input">Precio Venta</label>
                                        <input type="number" v-model="articulo.precio_venta" class="form-control" placeholder="">                                        
                                    </div> -->
                                    <div class="form-group col-md-1">
                                        <label class="form-control-label" for="text-input">Stock</label>
                                        <input type="number" v-model="articulo.stock" class="form-control" placeholder="">                                        
                                    </div>
                                              <div class="form-group col-md-4">
                                         <label class="form-control-label" for="text-input">Medida Final</label>
                                         <input type="text" v-model="articulo.medida_final" class="form-control" placeholder="Ej. 23x22" readonly>
                                     </div>
                                     <div class="form-group col-md-4">
                                         <label class="form-control-label" for="text-input">M. Final Ancho</label>
                                         <input type="number" step="0.01" v-model.number="articulo.ancho_final" @input="onMedidaFinalInput" class="form-control" placeholder="0.00">
                                     </div>
                                     <div class="form-group col-md-4">
                                         <label class="form-control-label" for="text-input">M. Final Largo</label>
                                         <input type="number" step="0.01" v-model.number="articulo.largo_final" @input="onMedidaFinalInput" class="form-control" placeholder="0.00">
                                     </div>
                                     <!-- Cabidas y Medidas de Materiales -->
                                     <div class="form-group col-md-12">
                                         <hr>
                                         <strong>Posibles Cabidas y Medidas de Material (Calculadas desde Ajustes)</strong>
                                         <div class="table-responsive mt-2">
                                             <table class="table table-bordered table-sm align-middle">
                                                 <thead class="thead-dark">
                                                     <tr>
                                                         <th style="width: 120px;" class="text-center">Cabida (Pzas)</th>
                                                         <th>Tamaño Impresión / Corte</th>
                                                         <th>Medida del Material (Ajustes)</th>
                                                         <th style="width: 150px;" class="text-center">Tamaño</th>
                                                         <th style="width: 80px;" class="text-center">Acción</th>
                                                     </tr>
                                                 </thead>
                                                 <tbody>
                                                     <tr v-for="(item, index) in articulo.cabidas_materiales" :key="index">
                                                         <td>
                                                             <input type="number" v-model.number="item.cabida"  class="form-control form-control-sm text-center font-weight-bold" placeholder="Ej. 4">
                                                         </td>
                                                         <td>
                                                             <input type="text" list="listaMedidasMaterial" v-model="item.tamano" class="form-control form-control-sm" placeholder="Ej. 35x50">
                                                         </td>
                                                         <td>
                                                             <input type="text" list="listaMedidasMaterial" v-model="item.medida_material"  class="form-control form-control-sm" placeholder="Ej. 50x70">
                                                         </td>
                                                         <td class="text-center align-middle">
                                                             <span class="badge badge-info p-2 font-weight-bold" style="font-size: 13px;">Tamaño: {{ calcularTamanoMaterial(item) }}</span>
                                                         </td>
                                                         <td class="text-center align-middle">
                                                             <button type="button" @click="eliminarCabidaMaterial(index)" class="btn btn-danger btn-sm" title="Eliminar fila">
                                                                 <i class="icon-trash"></i>
                                                             </button>
                                                         </td>
                                                     </tr>
                                                     <tr v-if="!articulo.cabidas_materiales || articulo.cabidas_materiales.length === 0">
                                                         <td colspan="5" class="text-center text-muted py-3">No se han configurado cabidas. Haga clic en "Calcular / Generar Cabidas" para obtener las medidas reales de Ajustes.</td>
                                                     </tr>
                                                 </tbody>
                                             </table>
                                         </div>

                                        <datalist id="listaMedidasMaterial">
                                            <option v-for="corte in arrayCortesDB" :key="corte.id" :value="corte.valor">{{ corte.detalle }} ({{ corte.valor }})</option>
                                        </datalist>

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" @click="agregarCabidaMaterial()" class="btn btn-primary btn-sm mr-2">
                                                <i class="icon-plus"></i> Agregar Fila
                                            </button>
                                            <button type="button" @click="generarCabidasAutomaticas()" class="btn btn-info btn-sm" title="Calcular automáticamente las cabidas reales basadas en las medidas de Ajustes">
                                                <i class="icon-magic-wand"></i> Calcular / Generar Cabidas desde Ajustes
                                            </button>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-control-label" for="text-input">Descripción</label>
                                        <input type="text" v-model="articulo.descripcion" class="form-control" placeholder="Ingrese descripción">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-control-label">Etiquetas (Separadas por coma: uso del empaque, tipo, etc.)</label>
                                        <input type="text" v-model="articulo.etiquetas" class="form-control" placeholder="Ej: alimentos, biodegradable, boutique">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="form-control-label">Ancho (cm)</label>
                                        <input type="number" step="0.01" v-model.number="articulo.ancho" @input="onDimensionesInput" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="form-control-label">Largo (cm)</label>
                                        <input type="number" step="0.01" v-model.number="articulo.largo" @input="onDimensionesInput" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="form-control-label">Alto/Fuelle (cm)</label>
                                        <input type="number" step="0.01" v-model.number="articulo.alto" @input="onDimensionesInput" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="form-control-label">Volumen</label>
                                        <input type="number" step="0.01" v-model="articulo.volumen" class="form-control" placeholder="0.00">
                                    </div>
                                    <div  v-show="errorArticulo" class="form-grou col-md-12 div-error">
                                        <div class="text-center text-error">
                                            <div v-for="error in errorMostrarMsjArticulo" :key="error" v-text="error">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                           
                         
                             <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                                <button type="button" @click="registrarArticulo()" v-if="tipoAccion==1" class="btn btn-primary" :disabled="cargando==1" style="cursor:pointer">
                                    <i v-if="cargando==1" class="fa fa-spinner fa-spin"></i> Guardar
                                </button>
                                <button type="button" @click="actualizarArticulo()" v-if="tipoAccion==2" class="btn btn-primary" :disabled="cargando==1" style="cursor:pointer">
                                    <i v-if="cargando==1" class="fa fa-spinner fa-spin"></i> Actualizar
                                </button>
                            </div>
                            </form>
                        </div>
                        
                       
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- Modal para Líneas de Troquel -->
            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalTroquel}" role="dialog" style="display: none; z-index: 1050;" aria-hidden="true">
                <div class="modal-dialog modal-primary modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Líneas de Troquel: {{ articuloSeleccionado.nombre }}</h4>
                            <button type="button" class="close" @click="cerrarModalTroquel()">×</button>
                        </div>
                        <div class="modal-body">
                            <div class="card mb-3">
                                <div class="card-header bg-light"><b>Configurar Nueva Cabida</b></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Cabida</label>
                                            <input type="number" class="form-control" v-model="troquel.cabida" placeholder="Ej: 3">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Ancho Imp. (cm)</label>
                                            <input type="number" step="0.01" class="form-control" v-model="troquel.ancho_impresion">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Largo Imp. (cm)</label>
                                            <input type="number" step="0.01" class="form-control" v-model="troquel.largo_impresion">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Tamaño</label>
                                            <input type="text" class="form-control" v-model="troquel.tamano" placeholder="Ej: 1/4">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Imagen Troquel</label>
                                            <input type="file" class="form-control" @change="onFileTroquel">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="ocultarTroquel" v-model="troquel.mostrar" :true-value="0" :false-value="1">
                                                <label class="form-check-label text-danger font-weight-bold" for="ocultarTroquel">¿No mostrable?</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12 text-right">
                                            <button class="btn btn-success" @click="guardarTroquel()">
                                                <i class="fa fa-save"></i> Guardar Configuración
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-bordered table-sm table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center">Cabida</th>
                                        <th class="text-center">Dimensiones</th>
                                        <th class="text-center">Tamaño</th>
                                        <th class="text-center">Visible</th>
                                        <th class="text-center">Imagen</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="t in arrayTroqueles" :key="t.id">
                                        <td class="text-center" style="font-weight: bold; font-size: 1.2em;">{{ t.cabida }}</td>
                                        <td class="text-center">{{ t.ancho_impresion }} x {{ t.largo_impresion }} cm</td>
                                        <td class="text-center"><span class="badge badge-info">{{ t.tamano }}</span></td>
                                        <td class="text-center">
                                            <span v-if="t.mostrar" class="badge badge-success">Sí</span>
                                            <span v-else class="badge badge-danger">No</span>
                                        </td>
                                        <td class="text-center">
                                            <img v-if="t.imagen" :src="'/storage/troqueles/' + t.imagen" width="120" @click="abrirZoom('/storage/troqueles/' + t.imagen)" style="border: 1px solid #ddd; padding: 2px; cursor: pointer;" title="Click para ampliar">
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" @click="cargarTroquel(t)" title="Editar">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <a v-if="t.imagen" :href="'/storage/troqueles/' + t.imagen" :download="'Troquel_Cabida_' + t.cabida + '_' + articuloSeleccionado.nombre" class="btn btn-success btn-sm" title="Descargar Imagen">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm" @click="eliminarTroquel(t.id)" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="arrayTroqueles.length == 0">
                                        <td colspan="4" class="text-center">No hay líneas de troquel configuradas para este artículo.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--Fin del modal troquel-->
            
            <!-- Modal para Zoom de Imagen -->
            <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalZoom}" role="dialog" style="display: none; z-index: 1060;" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                    <div class="modal-header" style="justify-content: space-between; align-items: center;">
                        <h4 class="modal-title">Vista Ampliada</h4>
                        <button type="button" class="close" @click="cerrarZoom()" style="font-size: 2rem; color: #000; opacity: 1; border: none; background: transparent; padding: 0 10px; cursor: pointer;">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body text-center" style="padding: 20px;">
                        <img :src="imagenZoom" style="max-width: 100%; max-height: 70vh; object-fit: contain; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    </div>
                        <div class="modal-footer">
                            <a :href="imagenZoom" :download="imagenZoom.split('/').pop()" class="btn btn-success">
                                <i class="fa fa-download"></i> Descargar Imagen
                            </a>
                            <button type="button" class="btn btn-secondary" @click="cerrarZoom()">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Carga Masiva de Imágenes -->
            <div class="modal fade" :class="{'mostrar': modalImagenesMasivas}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-info modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><i class="fa fa-picture-o"></i> Carga Masiva de Imágenes para Artículos</h4>
                            <button type="button" class="close" @click="cerrarModalImagenesMasivas()" aria-label="Close">
                              <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                            <div class="alert alert-info py-2 small mb-3">
                                <strong><i class="fa fa-info-circle"></i> Instrucciones de Nombrado de Fotografía:</strong><br>
                                • El nombre del archivo se compara con el <strong>Código</strong> o <strong>Nombre</strong> del artículo.<br>
                                • La posición de la foto en el artículo viene dada por el último guión y número (ej. <code>-1</code>, <code>-2</code>).<br>
                                • <em>Ejemplo:</em> <code>REF100-1.jpg</code> &rarr; Foto <strong>Posición 1</strong> para el artículo <code>REF100</code>.<br>
                                • <em>Ejemplo:</em> <code>REF100-2.png</code> &rarr; Foto <strong>Posición 2</strong> para el artículo <code>REF100</code>.<br>
                                <span class="text-danger font-weight-bold">⚠️ Importante:</span> Al subir fotos nuevas para un artículo, se borrarán automáticamente las fotos antiguas de ese artículo.
                            </div>

                            <div class="form-group border p-3 rounded bg-light">
                                <label class="font-weight-bold d-block mb-2">Seleccionar Fotografías (.jpg, .png, .webp):</label>
                                <input type="file" ref="inputImagenesMasivas" @change="onArchivosMasivosChange" multiple accept="image/*" class="form-control-file" />
                                <small v-if="archivosMasivos.length > 0" class="text-success font-weight-bold d-block mt-2">
                                    <i class="fa fa-check-circle"></i> {{ archivosMasivos.length }} archivo(s) seleccionado(s).
                                </small>
                            </div>

                            <div v-if="cargandoMasivas" class="text-center py-4">
                                <i class="fa fa-spinner fa-spin fa-3x text-info mb-3"></i>
                                <h5 class="font-weight-bold text-info">Procesando y asignando imágenes por lotes...</h5>
                                <p class="text-dark font-weight-bold my-2">{{ textoProgresoMasivas }}</p>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info font-weight-bold" 
                                         role="progressbar" 
                                         :style="{ width: porcentajeMasivas + '%' }">
                                        {{ porcentajeMasivas }}%
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Optimizando y guardando fotos sin saturar el servidor...</small>
                            </div>

                            <!-- Resumen de Resultados -->
                            <div v-if="resultadoMasivas" class="mt-3">
                                <hr>
                                <h5 class="font-weight-bold text-success mb-3">
                                    <i class="fa fa-check-square-o"></i> Resumen: {{ resultadoMasivas.articulos_actualizados_count }} artículo(s) actualizado(s) ({{ resultadoMasivas.total_archivos }} fotos procesadas)
                                </h5>

                                <!-- Fotos Asignadas -->
                                <div v-if="resultadoMasivas.procesados && resultadoMasivas.procesados.length > 0" class="mb-3">
                                    <h6 class="text-success font-weight-bold"><i class="fa fa-check text-success"></i> Imágenes Asignadas ({{ resultadoMasivas.procesados.length }}):</h6>
                                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Archivo Subido</th>
                                                    <th>Artículo Coincidente</th>
                                                    <th>Posición</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(p, index) in resultadoMasivas.procesados" :key="index">
                                                    <td class="font-weight-bold text-primary">{{ p.archivo }}</td>
                                                    <td>{{ p.nombre_articulo }} <span v-if="p.codigo_articulo" class="badge badge-secondary">Ref: {{ p.codigo_articulo }}</span></td>
                                                    <td><span class="badge badge-info">Posición {{ p.posicion }}</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Archivos no encontrados -->
                                <div v-if="resultadoMasivas.no_encontrados && resultadoMasivas.no_encontrados.length > 0">
                                    <h6 class="text-danger font-weight-bold"><i class="fa fa-exclamation-triangle text-danger"></i> Archivos Sin Coincidencia ({{ resultadoMasivas.no_encontrados.length }}):</h6>
                                    <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-danger text-white">
                                                <tr>
                                                    <th>Archivo</th>
                                                    <th>Búsqueda Intentada</th>
                                                    <th>Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(n, index) in resultadoMasivas.no_encontrados" :key="index">
                                                    <td class="font-weight-bold text-danger">{{ n.archivo }}</td>
                                                    <td><code>{{ n.codigo_buscado }}</code></td>
                                                    <td class="small text-muted">{{ n.motivo }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModalImagenesMasivas()" :disabled="cargandoMasivas">Cerrar</button>
                            <button type="button" class="btn btn-info text-white font-weight-bold" @click="subirImagenesMasivas()" :disabled="archivosMasivos.length === 0 || cargandoMasivas">
                                <i class="fa fa-upload"></i> Subir y Asignar Fotos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Asignación de Categorías con Vista de Árbol e Imagen -->
            <div class="modal fade" :class="{'mostrar': modalAsignacionMasiva}" tabindex="-1" role="dialog" style="display: none; z-index: 1055;" aria-hidden="true">
                <div class="modal-dialog modal-info modal-lg" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white py-3">
                            <h4 class="modal-title font-weight-bold mb-0">
                                <i class="fa fa-tags mr-2"></i> {{ tituloModalCategorias }}
                            </h4>
                            <button type="button" class="close text-white opacity-1" @click="cerrarModalAsignacionMasiva()" aria-label="Close">
                                <span aria-hidden="true" style="font-size: 1.8rem;">×</span>
                            </button>
                        </div>
                        
                        <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                            
                            <!-- SECCIÓN SUPERIOR: VISTA PREVIA DEL PRODUCTO E IMAGEN -->
                            <div class="card mb-3 border-light shadow-sm bg-light" v-if="articuloSeleccionadoModal">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <!-- Columna Imagen -->
                                        <div class="col-md-3 text-center mb-2 mb-md-0">
                                            <div style="width: 110px; height: 110px; margin: 0 auto; background-color: #fff; border-radius: 8px; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px;">
                                                <img v-if="imagenArticuloModal" :src="imagenArticuloModal" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                <div v-else class="text-muted small">
                                                    <i class="fa fa-picture-o fa-2x d-block mb-1"></i> Sin imagen
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Columna Información Producto -->
                                        <div class="col-md-9">
                                            <h5 class="font-weight-bold text-dark mb-1">{{ articuloSeleccionadoModal.nombre }}</h5>
                                            <div class="text-muted small mb-2">
                                                <span class="mr-3" v-if="articuloSeleccionadoModal.codigo"><strong>Código:</strong> {{ articuloSeleccionadoModal.codigo }}</span>
                                                <span class="mr-3" v-if="articuloSeleccionadoModal.tamano"><strong>Tamaño:</strong> {{ articuloSeleccionadoModal.tamano }}</span>
                                                <span v-if="articulosSeleccionados.length > 1" class="badge badge-info ml-2">+{{ articulosSeleccionados.length - 1 }} artículo(s) más</span>
                                            </div>
                                            <div class="p-2 rounded bg-white border small">
                                                <span class="font-weight-bold text-primary mr-1"><i class="fa fa-info-circle"></i> Regla de jerarquía:</span>
                                                <span>Al seleccionar cualquier subcategoría, se asignarán automáticamente todas sus categorías superiores hasta la raíz.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN INFERIOR: SELECCIÓN DE CATEGORÍAS TIPO MENÚ WEB -->
                            <div class="card border shadow-sm">
                                <div class="card-header bg-white py-2 px-3">
                                    <div class="row align-items-center">
                                        <!-- Buscador -->
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i class="fa fa-search text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control border-left-0" placeholder="Buscar categoría por nombre..." v-model="buscarCategoriaModal">
                                                <div class="input-group-append" v-if="buscarCategoriaModal">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" @click="buscarCategoriaModal = ''">×</button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Botones Rápidos -->
                                        <div class="col-md-6 text-md-right">
                                            <button type="button" class="btn btn-sm btn-outline-success mr-1" @click="seleccionarTodasVisibles()">
                                                <i class="fa fa-check-square-o mr-1"></i> Seleccionar Visibles
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" @click="categoriasSeleccionadasMasivas = []">
                                                <i class="fa fa-trash-o mr-1"></i> Limpiar Todo
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- PESTAÑAS DE CATEGORÍAS PRINCIPALES (ESTILO MENÚ PÁGINA) -->
                                <div class="bg-light border-bottom px-2 pt-2" v-if="!buscarCategoriaModal">
                                    <ul class="nav nav-tabs border-bottom-0 flex-nowrap" style="overflow-x: auto; scrollbar-width: thin;">
                                        <li class="nav-item">
                                            <a class="nav-link py-2 px-3 cursor-pointer font-weight-bold small text-nowrap"
                                               :class="tabCategoriaActiva === 0 ? 'active bg-primary text-white border-primary' : 'text-dark bg-white'"
                                               @click="tabCategoriaActiva = 0">
                                                <i class="fa fa-th-large mr-1"></i> Todas ({{ categoriasSeleccionadasMasivas.length }})
                                            </a>
                                        </li>
                                        <li class="nav-item" v-for="root in treeCategorias" :key="root.id">
                                            <a class="nav-link py-2 px-3 cursor-pointer font-weight-bold small text-nowrap"
                                               :class="tabCategoriaActiva === root.id ? 'active bg-primary text-white border-primary' : 'text-dark bg-white'"
                                               @click="tabCategoriaActiva = root.id">
                                                <i class="fa fa-folder-o mr-1"></i> {{ root.nombreLimpio }}
                                                <span v-if="countSelectedInTree(root) > 0" class="badge badge-pill ml-1" :class="tabCategoriaActiva === root.id ? 'badge-light text-primary' : 'badge-info'">
                                                    {{ countSelectedInTree(root) }}
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- RESUMEN DE PÍLDORAS SELECCIONADAS -->
                                <div v-if="categoriasSeleccionadasMasivas.length > 0" class="px-3 py-2 bg-white border-bottom d-flex align-items-center flex-wrap gap-1" style="max-height: 80px; overflow-y: auto;">
                                    <span class="small font-weight-bold text-muted mr-2"><i class="fa fa-check-circle text-success"></i> Seleccionadas:</span>
                                    <span v-for="catId in categoriasSeleccionadasMasivas" :key="catId" class="badge badge-pill badge-primary py-1 px-2 mr-1 mb-1 d-inline-flex align-items-center">
                                        {{ getCategoriaNombre(catId) }}
                                        <i class="fa fa-times ml-1 cursor-pointer" @click="desmarcarCategoria(catId)" title="Quitar esta categoría"></i>
                                    </span>
                                </div>

                                <!-- CUERPO PRINCIPAL EN GRILLA DE COLUMNAS (ESTILO MEGA MENÚ) -->
                                <div class="card-body p-3" style="max-height: 420px; overflow-y: auto; background-color: #f4f6f9;">
                                    <div v-if="arrayCategoria.length === 0" class="text-center py-4 text-muted">
                                        <i class="fa fa-spinner fa-spin fa-2x mb-2"></i>
                                        <p>Cargando categorías...</p>
                                    </div>
                                    
                                    <div v-else-if="rootNodesVisibles.length === 0" class="text-center py-4 text-muted">
                                        <i class="fa fa-search fa-2x mb-2"></i>
                                        <p>No se encontraron categorías que coincidan con "{{ buscarCategoriaModal }}".</p>
                                    </div>

                                    <div v-else class="row">
                                        <!-- Cada bloque representa una sección principal/subsección -->
                                        <div v-for="rootNode in rootNodesVisibles" :key="rootNode.id" class="col-12 mb-3">
                                            
                                            <!-- Encabezado Sección Principal -->
                                            <div class="d-flex align-items-center bg-white p-2 border rounded shadow-sm mb-2">
                                                <div class="custom-control custom-checkbox mr-2">
                                                    <input type="checkbox" class="custom-control-input" :id="'tree_cat_' + rootNode.id" :value="rootNode.id" v-model="categoriasSeleccionadasMasivas" @change="onCategoryCheckboxChange(rootNode.id)">
                                                    <label class="custom-control-label font-weight-bold text-uppercase cursor-pointer text-primary" :for="'tree_cat_' + rootNode.id" style="font-size: 0.95rem;">
                                                        <i class="fa fa-folder text-warning mr-1"></i> {{ rootNode.nombreLimpio }}
                                                    </label>
                                                </div>
                                                <span class="badge badge-success ml-auto" v-if="categoriasSeleccionadasMasivas.includes(rootNode.id)">Categoría Principal</span>
                                            </div>

                                            <!-- Grilla de Subcategorías Nivel 1 en Columnas Card -->
                                            <div class="row" v-if="rootNode.sublevels && rootNode.sublevels.length > 0">
                                                <div v-for="sub1 in rootNode.sublevels" :key="sub1.id" class="col-md-4 col-sm-6 mb-3">
                                                    <div class="card h-100 border shadow-sm">
                                                        <!-- Header Columna (Nivel 1 Azul) -->
                                                        <div class="card-header py-1 px-2 d-flex align-items-center justify-content-between" style="background-color: #eaf2fd; border-bottom: 2px solid #0099cc;">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" :id="'tree_cat_' + sub1.id" :value="sub1.id" v-model="categoriasSeleccionadasMasivas" @change="onCategoryCheckboxChange(sub1.id)">
                                                                <label class="custom-control-label font-weight-bold cursor-pointer text-uppercase" :for="'tree_cat_' + sub1.id" style="color: #0080b3; font-size: 0.82rem;">
                                                                    {{ sub1.nombreLimpio }}
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <!-- Hijos Nivel 2 y Nivel 3 -->
                                                        <div class="card-body p-2" style="font-size: 0.80rem;">
                                                            <ul class="list-unstyled mb-0">
                                                                <li v-for="sub2 in sub1.sublevels" :key="sub2.id" class="mb-1">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" :id="'tree_cat_' + sub2.id" :value="sub2.id" v-model="categoriasSeleccionadasMasivas" @change="onCategoryCheckboxChange(sub2.id)">
                                                                        <label class="custom-control-label font-weight-bold text-dark cursor-pointer" :for="'tree_cat_' + sub2.id">
                                                                            {{ sub2.nombreLimpio }}
                                                                        </label>
                                                                    </div>

                                                                    <!-- Nivel 3 -->
                                                                    <ul class="list-unstyled ml-3 mt-1" v-if="sub2.sublevels && sub2.sublevels.length > 0">
                                                                        <li v-for="sub3 in sub2.sublevels" :key="sub3.id" class="mb-1">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" class="custom-control-input" :id="'tree_cat_' + sub3.id" :value="sub3.id" v-model="categoriasSeleccionadasMasivas" @change="onCategoryCheckboxChange(sub3.id)">
                                                                                <label class="custom-control-label text-secondary cursor-pointer" :for="'tree_cat_' + sub3.id">
                                                                                    <span class="text-muted">↳</span> {{ sub3.nombreLimpio }}
                                                                                </label>
                                                                            </div>

                                                                            <!-- Nivel 4 -->
                                                                            <ul class="list-unstyled ml-3 mt-1" v-if="sub3.sublevels && sub3.sublevels.length > 0">
                                                                                <li v-for="sub4 in sub3.sublevels" :key="sub4.id" class="mb-1">
                                                                                    <div class="custom-control custom-checkbox">
                                                                                        <input type="checkbox" class="custom-control-input" :id="'tree_cat_' + sub4.id" :value="sub4.id" v-model="categoriasSeleccionadasMasivas" @change="onCategoryCheckboxChange(sub4.id)">
                                                                                        <label class="custom-control-label text-muted cursor-pointer" :for="'tree_cat_' + sub4.id">
                                                                                            <span class="text-muted">↳</span> {{ sub4.nombreLimpio }}
                                                                                        </label>
                                                                                    </div>
                                                                                </li>
                                                                            </ul>
                                                                        </li>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODO DE ASIGNACIÓN SI HAY MÚLTIPLES SELECCIONADOS -->
                            <div class="form-group border p-3 rounded bg-light mt-3" v-if="articulosSeleccionados.length > 1">
                                <label class="font-weight-bold d-block mb-2">Modo de Asignación Masiva:</label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="modoReemplazar" value="reemplazar" class="custom-control-input" v-model="modoAsignacion">
                                    <label class="custom-control-label text-danger font-weight-bold" for="modoReemplazar">
                                        Reemplazar (Sustituye las categorías actuales por las elegidas)
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="modoAgregar" value="agregar" class="custom-control-input" v-model="modoAsignacion">
                                    <label class="custom-control-label text-success font-weight-bold" for="modoAgregar">
                                        Añadir / Agregar (Mantiene las categorías anteriores y añade las elegidas)
                                    </label>
                                </div>
                            </div>

                            <div v-if="cargandoMasivo" class="text-center py-3">
                                <i class="fa fa-spinner fa-spin fa-2x text-info mb-2"></i>
                                <p class="font-weight-bold text-info">Guardando categorías...</p>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary px-4" @click="cerrarModalAsignacionMasiva()" :disabled="cargandoMasivo">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-success px-4 font-weight-bold" @click="ejecutarAsignacionMasiva()" :disabled="categoriasSeleccionadasMasivas.length === 0 || cargandoMasivo">
                                <i class="fa fa-save mr-1"></i> Guardar Categorías ({{ categoriasSeleccionadasMasivas.length }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
</template>

<script>
    import VueBarcode from 'vue-barcode';
    import readXlsFile from 'read-excel-file'
    import exportXlsJSON from 'export-from-json'
    export default {
        data (){
            return {
                tabla:1,
                datosExcel:[],
                cabecera:[
                    {text:'Nombre producto',value:0},
                    {text:'Valor',value:1},
                    {text:'Descripción',value:2}
                ],
                articulo:{'codigo':1,'condicion':0,'descripcion':'','id':0,'precio_venta':0,'tipo_producto_id':1,'id_item_padre':0,'idcategoria':1,'categorias_ids':[],'imagen':'noimagen','imagenes':[],'iva':0,'nombre':'','stock':0, 'tamano': '', 'medida_final': '', 'ancho_final': 0, 'largo_final': 0, 'cabidas_materiales': []},
                arrayCortesDB: [],
                articulo_id: 0,
                idcategoria : 0,
                nombre_categoria : '',
                codigo : '',
                tipo_producto_id:1,
                nombre : '',
                imagen:[],
                imagentemp:[],
                imagenMin:[],
                ordenimg:1,
                precio_venta : 100,
                iva:0,
                stock : 1000,
                tamano:'4',
                descripcion : '',
                campo_cantidad:1,
                arrayArticulo : [],
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                cargando : 0,
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
                buscar_etiqueta: '',
                arrayCategoria :[],
                topedit:0,
                buscar_articulo:'',
                modala:0,
                arrayArticulosBuscados:[],
                aseleccionado:false,
                idarticulo_padre:0,
                nombre_articulo_padre:'',
                articulo_padre_seleccionado:'',
                fileInputKey:0,
                seccion:'',
                dominio:'',
                modalTroquel: 0,
                articuloSeleccionado: {},
                arrayTroqueles: [],
                troquel: {
                    cabida: 1,
                    ancho_impresion: 0,
                    largo_impresion: 0,
                    tamano: '',
                    mostrar: 1,
                    imagen: null
                },
                fileTroquel: null,
                modalZoom: false,
                imagenZoom: '',
                modalImagenesMasivas: 0,
                archivosMasivos: [],
                cargandoMasivas: false,
                resultadoMasivas: null,
                porcentajeMasivas: 0,
                textoProgresoMasivas: '',
                articulosSeleccionados: [],
                seleccionarTodos: false,
                modalAsignacionMasiva: 0,
                categoriasSeleccionadasMasivas: [],
                modoAsignacion: 'reemplazar',
                cargandoMasivo: false,
                tituloModalCategorias: 'Asignación Masiva de Categorías',
                articuloSeleccionadoModal: null,
                tabCategoriaActiva: 0,
                buscarCategoriaModal: ''
            }
        },
        components: {
            'barcode': VueBarcode
        },
        computed:{
            imagenArticuloModal() {
                if (!this.articuloSeleccionadoModal) return '';
                let art = this.articuloSeleccionadoModal;
                if (art.imagenes && art.imagenes.length > 0) {
                    let first = art.imagenes.find(i => i.orden == 1) || art.imagenes[0];
                    if (first && first.nombre) return 'img/productos/' + first.nombre;
                }
                if (art.imagen && art.imagen !== 'noimagen') {
                    return 'img/productos/' + art.imagen;
                }
                return '';
            },
            treeCategorias() {
                if (!this.arrayCategoria || this.arrayCategoria.length === 0) return [];
                
                let byParent = {};
                this.arrayCategoria.forEach(c => {
                    let pId = (c.padre_id && c.padre_id > 0) ? parseInt(c.padre_id) : 0;
                    if (!byParent[pId]) byParent[pId] = [];
                    let cleanName = (c.nombre || '').replace(/^[›\s\-]+/, '').trim();
                    byParent[pId].push({
                        id: c.id,
                        padre_id: pId,
                        nombre: c.nombre,
                        nombreLimpio: cleanName,
                        sublevels: []
                    });
                });

                function buildBranch(parentId) {
                    let items = byParent[parentId] || [];
                    items.forEach(item => {
                        item.sublevels = buildBranch(item.id);
                    });
                    return items;
                }

                return buildBranch(0);
            },
            rootNodesVisibles() {
                let tree = this.treeCategorias;
                if (!tree || tree.length === 0) return [];

                let query = (this.buscarCategoriaModal || '').trim().toLowerCase();

                if (query) {
                    function filterTree(nodes) {
                        let result = [];
                        nodes.forEach(node => {
                            let matches = node.nombreLimpio.toLowerCase().includes(query);
                            let filteredSublevels = filterTree(node.sublevels || []);
                            if (matches || filteredSublevels.length > 0) {
                                result.push({
                                    ...node,
                                    sublevels: filteredSublevels
                                });
                            }
                        });
                        return result;
                    }
                    return filterTree(tree);
                }

                if (this.tabCategoriaActiva !== 0) {
                    return tree.filter(r => r.id === this.tabCategoriaActiva);
                }

                return tree;
            },
            flatTreeCategorias() {
                if (!this.arrayCategoria || this.arrayCategoria.length === 0) return [];
                
                let map = {};
                let roots = [];
                
                this.arrayCategoria.forEach(c => {
                    let cleanName = (c.nombre || '').replace(/^[›\s\-]+/, '').trim();
                    map[c.id] = {
                        id: c.id,
                        padre_id: c.padre_id,
                        nombre: c.nombre,
                        nombreLimpio: cleanName,
                        children: []
                    };
                });

                this.arrayCategoria.forEach(c => {
                    if (c.padre_id && map[c.padre_id]) {
                        map[c.padre_id].children.push(map[c.id]);
                    } else {
                        roots.push(map[c.id]);
                    }
                });

                let result = [];
                function traverse(node, depth) {
                    result.push({
                        id: node.id,
                        padre_id: node.padre_id,
                        nombre: node.nombreLimpio,
                        level: depth,
                        hasChildren: node.children.length > 0
                    });
                    node.children.forEach(child => traverse(child, depth + 1));
                }

                roots.forEach(root => traverse(root, 0));
                return result;
            },
            
            accion(){
                if(this.tipoAccion==1){
                    return this.registrarArticulo
                }else{
                    return this.actualizarArticulo
                }
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
        watch: {
            'articulo.ancho_final': function(val) {
                this.articulo.medida_final = (val || 0) + 'x' + (this.articulo.largo_final || 0);
                this.actualizarTamano();
                this.recalcularTamanosCabidas();
            },
            'articulo.largo_final': function(val) {
                this.articulo.medida_final = (this.articulo.ancho_final || 0) + 'x' + (val || 0);
                this.actualizarTamano();
                this.recalcularTamanosCabidas();
            },
            'articulo.ancho': function(val) {
                this.onDimensionesInput();
            },
            'articulo.largo': function(val) {
                this.onDimensionesInput();
            },
            'articulo.alto': function(val) {
                this.onDimensionesInput();
            }
        },
        methods : {
            descargarExcel(){
                var itemsdescargar=[];
                this.arrayArticulo.forEach((e)=>{
                    var item={id:e.id, nombre:e.nombre,descripcion:e.descripcion,
                        valor:e.precio_venta,Tamaño:e.tamano
                    }
                    itemsdescargar.push(item);
                })
                const data=itemsdescargar
                const filename='productos'
                const exportType=exportXlsJSON.types.xls
                exportXlsJSON({data,filename,exportType})
            },
            obtenerExcel(){
                const input =document.getElementById('excel')
                readXlsFile(input.files[0]).then((rows)=>{
                    this.datosExcel=rows
                })
                this.tabla=2
            },
            cancelarImportacion(){
                this.tabla=1
                this.datosExcel=[]
                const input =document.getElementById('excel')
                input.value = ''
            },
            realizarCambio(){
                let me = this;
                const datos = new FormData()
                datos.set('datos',JSON.stringify(this.datosExcel))
                datos.set('_method', 'PUT')
                axios.post('/articulo/cambioximportacion',datos)
                .then(function (response) {
                    console.log(response)
                    me.tabla=1
                    me.datosExcel=[]
                    const input =document.getElementById('excel')
                    input.value = ''
                    me.listarArticulo(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                });
            },
          
            cerrarModali(){
                this.modali=0
                this.arrayInsumos=[]
            },
            anchoColum() {
                let me =this
                let ancholista=me.$refs.articulo2.clientWidth
                let w=ancholista/me.anchoCol
                    return { width: `${w}px` };
            }, 
            listarArticulo (page,buscar,criterio){
                let me=this;
                var url= '/articulo?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio + '&etiqueta=' + me.buscar_etiqueta;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    // console.log(respuesta);
                    me.arrayArticulo = respuesta.articulos.data;
                    me.pagination= respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
             
            },
            eliminarImagen(img, index){
                var me = this;
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });

                swalWithBootstrapButtons.fire({
                    title: '¿Está seguro de borrar esta imagen?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        axios.get('/borrarImagen?id=' + img.id + '&idtabla=' + img.id_tabla)
                        .then(function (response) {
                            if (me.articulo && me.articulo.imagenes) {
                                if (index !== undefined) {
                                    me.articulo.imagenes.splice(index, 1);
                                } else {
                                    let idx = me.articulo.imagenes.findIndex(i => i.id === img.id);
                                    if (idx !== -1) me.articulo.imagenes.splice(idx, 1);
                                }
                            }
                            swal('¡Eliminada!', 'La imagen ha sido eliminada con éxito.', 'success');
                        }).catch(function (error) {
                            console.log(error);
                            swal('Error', 'No se pudo eliminar la imagen.', 'error');
                        });
                    }
                });
            },
            eliminarImagenTemp(index){
                this.imagentemp.splice(index,1)
                this.imagenMin.splice(index,1)
            },
            obtenerImagen(e){
                let file=e.target.files[0]
                let img={
                    'file':URL.createObjectURL(file),
                    'orden':this.ordenimg
                }
                this.imagentemp.push(file)
                this.imagenMin.push(img)
                
                // this.imagen[]=imag
                // let imag={
                //     'id':0,
                //     'id_tabla':this.idarticulo,
                //     'nombre':file.name,
                //     'orden':img.indexOf()
                // }

            },
            cambiarOden(){
                let swap = (val1, val2, arr) => {
                if(!arr.includes(val1) || !arr.includes(val2)) return;
                    let val1_index = arr.indexOf(val1);
                    let val2_index = arr.indexOf(val2);
                    arr.splice(val1_index, 1, val2);
                    arr.splice(val2_index, 1, val1);
                }
                let estudiantes = ['Juan', 'Luis', 'Mario','Jessica', 'Marcos'];
                swap('Luis', 'Jessica', estudiantes);
                console.log(estudiantes)
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
          
      
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarArticulo(page,buscar,criterio);
            },
            
            
            registrarArticulo(){
                // if (this.validarArticulo()){
                //     return;
                // }
                
                let me = this;
                me.cargando = 1;
                // this.rangos=[{"de":"1","hasta":"10","descuento":"0","rangoEdit":0},{"de":"11","hasta":"20","descuento":"7","rangoEdit":0},{"de":"21","hasta":"30","descuento":"10","rangoEdit":0},{"de":"31","hasta":"40","descuento":"13","rangoEdit":0},{"de":"41","hasta":"50","descuento":"17","rangoEdit":0},{"de":"51","hasta":"60","descuento":"20","rangoEdit":0},{"de":"61","hasta":"70","descuento":"23","rangoEdit":0},{"de":"71","hasta":"80","descuento":"26","rangoEdit":0},{"de":"81","hasta":"90","descuento":"30","rangoEdit":0},{"de":"91","hasta":"100","descuento":"33","rangoEdit":0},{"de":"101","hasta":"200","descuento":"36","rangoEdit":0},{"de":"201","hasta":"10000","descuento":"46","rangoEdit":0}]
                
                const datos = new FormData()
                this.imagentemp.forEach(element => {
                    datos.append('imagen[]',element)
                });
                datos.set('orden',JSON.stringify(this.imagenMin))
                 datos.set('imagenes',JSON.stringify(this.articulo.imagenes))
                datos.set('id',this.articulo.id)
                datos.set('idcategoria', (this.articulo.categorias_ids && this.articulo.categorias_ids.length > 0) ? this.articulo.categorias_ids[0] : (this.articulo.idcategoria || 0))
                datos.set('categorias', JSON.stringify(this.articulo.categorias_ids || []))
                datos.set('id_item_padre',this.articulo.id_item_padre)
                datos.set('codigo',this.articulo.codigo)
                datos.set('tipo_producto_id',this.articulo.tipo_producto_id)
                datos.set('nombre',this.articulo.nombre)
                datos.set('stock', this.articulo.stock)
                datos.set('tamano', this.articulo.tamano)
                datos.set('medida_final', this.articulo.medida_final || '')
                datos.set('ancho_final', this.articulo.ancho_final || 0)
                datos.set('largo_final', this.articulo.largo_final || 0)
                datos.set('precio_venta', this.articulo.precio_venta)
                datos.set('descripcion', this.articulo.descripcion)
                datos.set('etiquetas', this.articulo.etiquetas)
                datos.set('ancho', this.articulo.ancho)
                datos.set('largo', this.articulo.largo)
                datos.set('alto', this.articulo.alto)
                datos.set('volumen', this.articulo.volumen)
                datos.set('cabidas_materiales', JSON.stringify(this.articulo.cabidas_materiales || []))
                axios.post('/articulo/registrar',datos)
                .then(function (response) {
                    console.log(response)
                    me.cargando = 0;
                    me.articulo_padre_seleccionado={};
                    me.cerrarModal();
                    me.listarArticulo(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                    me.cargando = 0;
                    swal('Error', 'No se pudo guardar el artículo', 'error');
                });
            },
            actualizarArticulo(){
               if (this.validarArticulo()){
                    return;
                }
                let me = this;
                me.cargando = 1;
                const datos = new FormData()
                let i=1
                this.imagentemp.forEach(element => {
                    datos.append('imagen[]',element)
                });
                datos.set('orden',JSON.stringify(this.imagenMin))
                datos.set('imagenes',JSON.stringify(this.articulo.imagenes))
                datos.set('_method', 'PUT')
                datos.set('id',this.articulo.id)
                datos.set('idcategoria', (this.articulo.categorias_ids && this.articulo.categorias_ids.length > 0) ? this.articulo.categorias_ids[0] : (this.articulo.idcategoria || 0))
                datos.set('categorias', JSON.stringify(this.articulo.categorias_ids || []))
                datos.set('id_item_padre',this.articulo.id_item_padre)
                datos.set('codigo',this.articulo.codigo)
                datos.set('tipo_producto_id',this.articulo.tipo_producto_id)
                datos.set('nombre',this.articulo.nombre)
                datos.set('stock', this.articulo.stock)
                datos.set('tamano', this.articulo.tamano)
                datos.set('medida_final', this.articulo.medida_final || '')
                datos.set('ancho_final', this.articulo.ancho_final || 0)
                datos.set('largo_final', this.articulo.largo_final || 0)
                datos.set('precio_venta', this.articulo.precio_venta)
                datos.set('descripcion', this.articulo.descripcion)
                datos.set('etiquetas', this.articulo.etiquetas)
                datos.set('ancho', this.articulo.ancho)
                datos.set('largo', this.articulo.largo)
                datos.set('alto', this.articulo.alto)
                datos.set('volumen', this.articulo.volumen)
                datos.set('cabidas_materiales', JSON.stringify(this.articulo.cabidas_materiales || []))
                axios.post('/articulo/actualizar',datos)
                .then(function (response) {
                    console.log(response)
                    me.cargando = 0;
                    me.articulo_padre_seleccionado={};
                    me.cerrarModal()
                    me.listarArticulo(1,'','nombre');
                    swal('Actualizado!', 'El artículo ha sido actualizado con éxito', 'success');
                }).catch(function (error) {
                    console.log(error);
                    me.cargando = 0;
                    swal('Error', 'No se pudo actualizar el artículo', 'error');
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
                let hasCats = (this.articulo.categorias_ids && this.articulo.categorias_ids.length > 0) || (this.articulo.idcategoria && this.articulo.idcategoria != 0);
                if (!hasCats) this.errorMostrarMsjArticulo.push("Seleccione al menos una categoría.");
                if (!this.articulo.nombre) this.errorMostrarMsjArticulo.push("El nombre del artículo no puede estar vacío.");
                if (!this.articulo.tamano) this.errorMostrarMsjArticulo.push("El tamaño del artículo no puede estar vacío.");
                if (this.articulo.stock === '' || this.articulo.stock === null || this.articulo.stock === undefined || isNaN(this.articulo.stock)) {
                    this.errorMostrarMsjArticulo.push("El stock del artículo debe ser un número.");
                }

                if (this.errorMostrarMsjArticulo.length) this.errorArticulo = 1;

                return this.errorArticulo;
            },
           
          
           
            cerrarModal(){
                this.modal=0
                this.tituloModal=''
                this.idarticulo_padre=''
                this.articulo_padre_seleccionado=''
                this.nombre=''
                this.imagenMin=[]
                this.imagen=[]
                this.imagentemp=[]
                this.fileInputKey++;
                this.descripcion=''
                this.arrayAtributo=[]
                this.arrayOpAtributo=[]
                this.arrayCostos=[]
                this.rangos=[]
                this.cantidades={'tipo_cantidad':1,'valores':[1,1000]}
                this.articulo.tamano = '';
                this.articulo.medida_final = '';
                this.articulo.cabidas_materiales = [];
            },
            abrirModal(modelo, accion, data){
                switch(modelo){
                    case "articulo":
                    {
                        switch(accion){
                            case 'registrar':
                            {
                                this.modal = 1;
                                this.tituloModal = 'Registrar Artículo';
                                this.tipoAccion = 1;
                                this.articulo = {
                                    'id': 0,
                                    'codigo': 1,
                                    'idcategoria': 1,
                                    'categorias_ids': [1],
                                    'id_item_padre': 0,
                                    'tipo_producto_id': 1,
                                    'nombre': '',
                                    'stock': 0,
                                    'tamano': '',
                                    'medida_final': '',
                                    'ancho_final': 0,
                                    'largo_final': 0,
                                    'precio_venta': 0,
                                    'descripcion': '',
                                    'etiquetas': '',
                                    'ancho': 0,
                                    'largo': 0,
                                    'alto': 0,
                                    'volumen': 0,
                                    'imagenes': [],
                                    'cabidas_materiales': []
                                };
                                break;
                            }
                            case 'actualizar':
                            {
                                this.modal=1;
                                this.tituloModal='Actualizar Artículo';
                                this.tipoAccion=2;
                                this.articulo=data;
                                let catIds = [];
                                if (data['categorias'] && data['categorias'].length > 0) {
                                    catIds = data['categorias'].map(c => c.id);
                                } else if (data['idcategoria']) {
                                    catIds = [data['idcategoria']];
                                }
                                this.$set(this.articulo, 'categorias_ids', catIds);
                                let cabidas = data['cabidas_materiales'];
                                if (typeof cabidas === 'string') {
                                    try {
                                        cabidas = JSON.parse(cabidas);
                                    } catch (e) {
                                        cabidas = [];
                                    }
                                }
                                this.$set(this.articulo, 'cabidas_materiales', cabidas || []);
                                this.articulo_id=data['id'];
                                this.idcategoria=data['idcategoria'];
                                this.idarticulo_padre=data['id_item_padre'];
                                this.codigo=data['codigo'];
                                this.nombre = data['nombre'];
                                this.stock=data['stock'];
                                this.precio_venta=data['precio_venta'];
                                this.iva=data['iva'];
                                this.descripcion= data['descripcion'];
                                this.tamano=data['tamano'];
                                if(data['rangos']==null){
                                    this.rangos=[]
                                }else{
                                    this.rangos=data['rangos'];
                                }
                                if(data['cantidades']==0){
                                    this.cantidades={'tipo_cantidad':1,'valores':[1,1000]}
                                }else{
                                    this.cantidades=data['cantidades'];
                                }
                                if(data['imagen'].length==0){
                                    this.imagen=[]
                                }else{
                                    this.imagen=data['imagen']
                                }
                                this.imagenMin=[]
                               
                                break;
                            }
                        }
                    }
                }
                this.selectCategoria();
            },

            // --- MÉTODOS PARA LÍNEAS DE TROQUEL ---
            abrirModalTroquel(articulo) {
                this.modalTroquel = 1;
                this.articuloSeleccionado = articulo;
                this.troquel.cabida = 1;
                this.troquel.ancho_impresion = 0;
                this.troquel.largo_impresion = 0;
                this.troquel.tamano = '';
                this.troquel.mostrar = 1;
                this.fileTroquel = null;
                this.listarTroqueles(articulo.id);
            },
            cerrarModalTroquel() {
                this.modalTroquel = 0;
                this.articuloSeleccionado = {};
                this.arrayTroqueles = [];
            },
            onFileTroquel(e) {
                this.fileTroquel = e.target.files[0];
            },
            listarTroqueles(id) {
                let me = this;
                axios.get('/articulo/troquel/listar?articulo_id=' + id).then(function (response) {
                    me.arrayTroqueles = response.data.troqueles;
                }).catch(function (error) {
                    console.log(error);
                });
            },
            guardarTroquel() {
                let me = this;
                let formData = new FormData();
                if (this.troquel.id) {
                    formData.append('id', this.troquel.id);
                }
                formData.append('articulo_id', this.articuloSeleccionado.id);
                formData.append('cabida', this.troquel.cabida);
                formData.append('ancho_impresion', this.troquel.ancho_impresion);
                formData.append('largo_impresion', this.troquel.largo_impresion);
                formData.append('tamano', this.troquel.tamano || '');
                formData.append('mostrar', this.troquel.mostrar);
                if (this.fileTroquel) {
                    formData.append('imagen', this.fileTroquel);
                }

                axios.post('/articulo/troquel/guardar', formData).then(function (response) {
                    Swal.fire("Éxito", "Configuración de troquel guardada", "success");
                    me.listarTroqueles(me.articuloSeleccionado.id);
                    // Reset fields
                    me.troquel.id = 0;
                    me.troquel.cabida = 0;
                    me.troquel.ancho_impresion = 0;
                    me.troquel.largo_impresion = 0;
                    me.troquel.tamano = '';
                    me.troquel.mostrar = 1;
                    me.fileTroquel = null;
                }).catch(function (error) {
                    Swal.fire("Error", "No se pudo guardar la configuración", "error");
                });
            },
            cargarTroquel(t) {
                this.troquel.id = t.id;
                this.troquel.cabida = t.cabida;
                this.troquel.ancho_impresion = t.ancho_impresion;
                this.troquel.largo_impresion = t.largo_impresion;
                this.troquel.tamano = t.tamano || '';
                this.troquel.mostrar = t.mostrar;
            },
            eliminarTroquel(id) {
                let me = this;
                Swal.fire({
                    title: '¿Eliminar esta configuración?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.delete('/articulo/troquel/eliminar?id=' + id).then(function (response) {
                            me.listarTroqueles(me.articuloSeleccionado.id);
                        });
                    }
                });
            },
            abrirZoom(url) {
                this.imagenZoom = url;
                this.modalZoom = true;
            },
            cerrarZoom() {
                this.modalZoom = false;
                this.imagenZoom = '';
            },
            onMedidaFinalInput() {
                let a = parseFloat(this.articulo.ancho_final) || 0;
                let l = parseFloat(this.articulo.largo_final) || 0;
                if (a > 0 && l > 0) {
                    this.articulo.medida_final = a + 'x' + l;
                }
                this.actualizarTamano();
            },
            onDimensionesInput() {
                let a = parseFloat(this.articulo.ancho) || 0;
                let l = parseFloat(this.articulo.largo) || 0;

                if (a > 0 && (!this.articulo.ancho_final || parseFloat(this.articulo.ancho_final) === 0)) {
                    this.articulo.ancho_final = a;
                }
                if (l > 0 && (!this.articulo.largo_final || parseFloat(this.articulo.largo_final) === 0)) {
                    this.articulo.largo_final = l;
                }

                this.actualizarTamano();
            },
            calcularTamanoMaterial(item) {
                if (!item) return 1;
                let mat = item.medida_material;

                let pliegoW = 70;
                let pliegoH = 100;
                if (this.arrayCortesDB && this.arrayCortesDB.length > 0) {
                    let maxArea = 0;
                    this.arrayCortesDB.forEach(c => {
                        if (c.valor && c.valor.includes('x')) {
                            let p = c.valor.split('x').map(Number);
                            if (p.length >= 2 && !isNaN(p[0]) && !isNaN(p[1])) {
                                let area = p[0] * p[1];
                                if (area > maxArea) {
                                    maxArea = area;
                                    pliegoW = p[0];
                                    pliegoH = p[1];
                                }
                            }
                        }
                    });
                }

                if (!mat || !mat.includes('x')) {
                    return 1;
                }

                let parts = mat.split('x').map(Number);
                if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) {
                    return 1;
                }

                let cortesPorPliego = this.getCabidaCorte(pliegoW, pliegoH, parts[0], parts[1]);
                return cortesPorPliego > 0 ? cortesPorPliego : 1;
            },
            actualizarTamano() {
                let a = parseFloat(this.articulo.ancho_final) || parseFloat(this.articulo.ancho) || 0;
                let l = parseFloat(this.articulo.largo_final) || parseFloat(this.articulo.largo) || 0;

                if (a <= 0 || l <= 0) return;

                if (this.articulo.cabidas_materiales && this.articulo.cabidas_materiales.length > 0) {
                    let t = this.calcularTamanoMaterial(this.articulo.cabidas_materiales[0]);
                    if (t > 0) {
                        this.articulo.tamano = t;
                        return;
                    }
                }

                let mejorCabida = 1;
                if (this.arrayCortesDB && this.arrayCortesDB.length > 0) {
                    this.arrayCortesDB.forEach(corte => {
                        if (!corte.valor || !corte.valor.includes('x')) return;
                        let parts = corte.valor.split('x').map(Number);
                        if (parts.length >= 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                            let cab = this.getCabidaCorte(parts[0], parts[1], a, l);
                            if (cab > mejorCabida) {
                                mejorCabida = cab;
                            }
                        }
                    });
                } else {
                    mejorCabida = this.getCabidaCorte(70, 100, a, l) || 1;
                }

                this.articulo.tamano = mejorCabida;
            },
            solveRowStacking(W, H, pieceW, pieceH) {
                if (pieceW <= 0 || pieceH <= 0 || W <= 0 || H <= 0) return 0;
                let count1 = Math.floor(W / pieceW);
                let count2 = Math.floor(W / pieceH);
                let best = 0;
                for (let n1 = 0; n1 * pieceH <= H; n1++) {
                    let remainingH = H - (n1 * pieceH);
                    let n2 = Math.floor(remainingH / pieceW);
                    let total = (n1 * count1) + (n2 * count2);
                    if (total > best) {
                        best = total;
                    }
                }
                return best;
            },
            getCabidaCorte(cutW, cutH, pieceW, pieceH) {
                let sol1 = this.solveRowStacking(cutW, cutH, pieceW, pieceH);
                let sol2 = this.solveRowStacking(cutH, cutW, pieceW, pieceH);
                return Math.max(sol1, sol2);
            },
            cargarCortesDB() {
                let me = this;
                axios.get('/ajustes/listar?tipo=medidas_pliego').then(res => {
                    me.arrayCortesDB = res.data || [];
                }).catch(err => {
                    console.log(err);
                });
            },
            calcularTamanoImpresion(c, a, l) {
                let cols = 1, rows = 1;
                if (c === 1) { cols = 1; rows = 1; }
                else if (c === 2) { cols = 2; rows = 1; }
                else if (c === 3) { cols = 3; rows = 1; }
                else if (c === 4) { cols = 2; rows = 2; }
                else if (c === 5) { cols = 2; rows = 3; }
                else if (c === 6) { cols = 3; rows = 2; }
                else if (c === 8) { cols = 4; rows = 2; }
                else if (c === 9) { cols = 3; rows = 3; }
                else if (c === 10) { cols = 5; rows = 2; }
                else if (c === 12) { cols = 4; rows = 3; }
                else if (c === 15) { cols = 5; rows = 3; }
                else if (c === 16) { cols = 4; rows = 4; }
                else {
                    let sq = Math.floor(Math.sqrt(c));
                    while (c % sq !== 0 && sq > 1) {
                        sq--;
                    }
                    cols = Math.max(sq, c / sq);
                    rows = Math.min(sq, c / sq);
                }

                let resW = Math.round((cols * a) * 10) / 10;
                let resH = Math.round((rows * l) * 10) / 10;
                return resW + 'x' + resH;
            },
            fitsInMaxMachine(impW, impH) {
                const maxW = 70;
                const maxH = 52;
                const normal = (impW <= maxW && impH <= maxH) || (impW <= maxH && impH <= maxW);
                const rotated = (impH <= maxW && impW <= maxH) || (impH <= maxH && impW <= maxW);
                return normal || rotated;
            },
            obtenerMejorMaterialParaLayout(impW, impH) {
                if (!this.arrayCortesDB || this.arrayCortesDB.length === 0) return impW + 'x' + impH;
                
                let candidatos = [];

                this.arrayCortesDB.forEach(corte => {
                    if (!corte.valor || !corte.valor.includes('x')) return;
                    let parts = corte.valor.split('x').map(Number);
                    if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return;

                    let matW = parts[0];
                    let matH = parts[1];

                    let fitsNormal = (impW <= matW && impH <= matH) || (impW <= matH && impH <= matW);
                    let fitsRotated = (impH <= matW && impW <= matH) || (impH <= matH && impW <= matW);

                    if (fitsNormal || fitsRotated) {
                        candidatos.push({
                            valor: corte.valor,
                            detalle: corte.detalle,
                            area: matW * matH
                        });
                    }
                });

                if (candidatos.length > 0) {
                    candidatos.sort((x, y) => x.area - y.area);
                    return candidatos[0].valor;
                }

                return impW + 'x' + impH;
            },
            onCabidaChange(index) {
                if (!this.articulo.cabidas_materiales || !this.articulo.cabidas_materiales[index]) return;
                let item = this.articulo.cabidas_materiales[index];
                let a = parseFloat(this.articulo.ancho_final) || 0;
                let l = parseFloat(this.articulo.largo_final) || 0;
                let c = parseFloat(item.cabida) || 1;

                let calcTamano = this.calcularTamanoImpresion(c, a, l);
                this.$set(item, 'tamano', calcTamano);

                if (index === 0 && c > 0) {
                    this.articulo.tamano = c;
                }

                if (a > 0 && l > 0) {
                    let parts = calcTamano.split('x').map(Number);
                    let mat = this.obtenerMejorMaterialParaLayout(parts[0], parts[1]);
                    if (mat) {
                        this.$set(item, 'medida_material', mat);
                    }
                }
            },
            agregarCabidaMaterial() {
                if (!this.articulo.cabidas_materiales) {
                    this.$set(this.articulo, 'cabidas_materiales', []);
                }
                let a = parseFloat(this.articulo.ancho_final) || 0;
                let l = parseFloat(this.articulo.largo_final) || 0;
                let nextCabida = 1;
                if (this.articulo.cabidas_materiales.length > 0) {
                    let last = this.articulo.cabidas_materiales[this.articulo.cabidas_materiales.length - 1];
                    nextCabida = (parseFloat(last.cabida) || 1) * 2;
                }

                let tamanoImp = this.calcularTamanoImpresion(nextCabida, a, l);
                let parts = tamanoImp.split('x').map(Number);
                let mat = this.obtenerMejorMaterialParaLayout(parts[0], parts[1]);

                this.articulo.cabidas_materiales.push({
                    cabida: nextCabida,
                    tamano: tamanoImp,
                    medida_material: mat || (this.arrayCortesDB[0] ? this.arrayCortesDB[0].valor : '50x70')
                });
            },
            generarCabidasAutomaticas() {
                let me = this;
                let a = parseFloat(this.articulo.ancho_final) || 0;
                let l = parseFloat(this.articulo.largo_final) || 0;

                if ((a <= 0 || l <= 0) && this.articulo.medida_final && this.articulo.medida_final.includes('x')) {
                    let parts = this.articulo.medida_final.split('x').map(Number);
                    if (parts.length >= 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                        a = parts[0];
                        l = parts[1];
                        this.articulo.ancho_final = a;
                        this.articulo.largo_final = l;
                    }
                }

                if (a <= 0 || l <= 0) {
                    Swal.fire("Atención", "Ingrese M. Final Ancho y Largo (o Medida Final) para calcular la Cabida 1 desde Ajustes.", "warning");
                    return;
                }

                if (!this.arrayCortesDB || this.arrayCortesDB.length === 0) {
                    axios.get('/ajustes/listar?tipo=medidas_pliego').then(res => {
                        me.arrayCortesDB = res.data || [];
                        me.procesarGenerarCabidas(a, l);
                    });
                } else {
                    this.procesarGenerarCabidas(a, l);
                }
            },
            evaluarCabidaEspecifica(c, a, l) {
                if (!this.arrayCortesDB || this.arrayCortesDB.length === 0) return null;

                let me = this;
                let areaPiezas = c * (a * l);
                let opcionesLayout = [];

                if (c === 1) {
                    opcionesLayout.push({ w: a, h: l });
                } else if (c === 2) {
                    opcionesLayout.push({ w: Math.round((2 * a) * 10) / 10, h: l });
                    opcionesLayout.push({ w: a, h: Math.round((2 * l) * 10) / 10 });
                }

                let mejorOpcion = null;
                let minDesperdicio = 999.0;

                opcionesLayout.forEach(layout => {
                    let impW = layout.w;
                    let impH = layout.h;

                    me.arrayCortesDB.forEach(corte => {
                        if (!corte.valor || !corte.valor.includes('x')) return;
                        let parts = corte.valor.split('x').map(Number);
                        if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return;

                        let cutW = parts[0];
                        let cutH = parts[1];

                        let fitsNormal = (impW <= cutW && impH <= cutH) || (impW <= cutH && impH <= cutW);
                        let fitsRotated = (impH <= cutW && impW <= cutH) || (impH <= cutH && impW <= cutW);

                        if (fitsNormal || fitsRotated) {
                            if (me.fitsInMaxMachine(cutW, cutH)) {
                                let areaCorte = cutW * cutH;
                                let desperdicioPct = ((areaCorte - areaPiezas) / areaCorte) * 100.0;

                                // Límite de desperdicio viable (máximo 55%)
                                if (desperdicioPct <= 55.0 && desperdicioPct < minDesperdicio) {
                                    minDesperdicio = desperdicioPct;
                                    mejorOpcion = {
                                        cabida: c,
                                        tamano: impW + 'x' + impH,
                                        medida_material: corte.valor,
                                        desperdicio: Math.round(desperdicioPct * 10) / 10
                                    };
                                }
                            }
                        }
                    });
                });

                return mejorOpcion;
            },
            procesarGenerarCabidas(a, l) {
                let me = this;
                let resultadoFinal = [];

                let cab1 = me.evaluarCabidaEspecifica(1, a, l);
                if (cab1) {
                    resultadoFinal.push(cab1);
                }

                let cab2 = me.evaluarCabidaEspecifica(2, a, l);
                if (cab2) {
                    resultadoFinal.push(cab2);
                }

                this.$set(this.articulo, 'cabidas_materiales', resultadoFinal);
                if (resultadoFinal && resultadoFinal.length > 0) {
                    this.articulo.tamano = this.calcularTamanoMaterial(resultadoFinal[0]);
                }
                Swal.fire("Cabidas Evaluadas", "Se evaluaron Cabida 1 y Cabida 2. Generadas: " + resultadoFinal.length + " cabida(s) viable(s).", "success");
            },
            eliminarCabidaMaterial(index) {
                this.articulo.cabidas_materiales.splice(index, 1);
            },
            abrirModalImagenesMasivas() {
                this.archivosMasivos = [];
                this.cargandoMasivas = false;
                this.resultadoMasivas = null;
                this.modalImagenesMasivas = 1;
                if (this.$refs.inputImagenesMasivas) {
                    this.$refs.inputImagenesMasivas.value = '';
                }
            },
            cerrarModalImagenesMasivas() {
                this.modalImagenesMasivas = 0;
                this.archivosMasivos = [];
                this.cargandoMasivas = false;
                this.resultadoMasivas = null;
            },
            onArchivosMasivosChange(event) {
                this.archivosMasivos = Array.from(event.target.files || []);
                this.resultadoMasivas = null;
            },
            async subirImagenesMasivas() {
                if (!this.archivosMasivos.length) return;
                let me = this;
                me.cargandoMasivas = true;
                me.resultadoMasivas = null;
                me.porcentajeMasivas = 0;
                me.textoProgresoMasivas = `Iniciando proceso para ${this.archivosMasivos.length} imágenes...`;

                let articulosYaLimpiados = [];
                let procesadosAcumulados = [];
                let noEncontradosAcumulados = [];
                let articulosActualizadosSet = new Set();

                const totalFiles = this.archivosMasivos.length;
                const BATCH_SIZE = 1;

                for (let i = 0; i < totalFiles; i += BATCH_SIZE) {
                    const chunk = this.archivosMasivos.slice(i, i + BATCH_SIZE);
                    const processedCount = Math.min(i + BATCH_SIZE, totalFiles);
                    me.porcentajeMasivas = Math.round((processedCount / totalFiles) * 100);
                    const currentName = chunk[0] ? chunk[0].name : '';
                    me.textoProgresoMasivas = `Subiendo foto ${i + 1} de ${totalFiles} (${currentName}) - ${me.porcentajeMasivas}%...`;

                    try {
                        const formData = new FormData();
                        const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');
                        if (tokenMeta) {
                            formData.append('_token', tokenMeta.content);
                        }
                        for (let j = 0; j < chunk.length; j++) {
                            formData.append('imagenes[]', chunk[j]);
                        }
                        formData.append('articulos_ya_limpiados', JSON.stringify(articulosYaLimpiados));

                        const response = await axios.post('/articulo/importarImagenesMasivas', formData);

                        const data = response.data;
                        if (data.articulos_limpiados) {
                            articulosYaLimpiados = data.articulos_limpiados;
                        }
                        if (data.procesados) {
                            data.procesados.forEach(p => {
                                procesadosAcumulados.push(p);
                                articulosActualizadosSet.add(p.articulo_id);
                            });
                        }
                        if (data.no_encontrados) {
                            data.no_encontrados.forEach(n => noEncontradosAcumulados.push(n));
                        }
                    } catch (errChunk) {
                        let errMsg = (errChunk.response && errChunk.response.data && errChunk.response.data.message)
                            ? errChunk.response.data.message
                            : (errChunk.message || 'Error de red o servidor al procesar la imagen');
                        noEncontradosAcumulados.push({
                            archivo: currentName,
                            codigo_buscado: '',
                            posicion: 1,
                            motivo: errMsg
                        });
                    }
                }

                me.cargandoMasivas = false;
                me.resultadoMasivas = {
                    total_archivos: totalFiles,
                    articulos_actualizados_count: articulosActualizadosSet.size,
                    procesados: procesadosAcumulados,
                    no_encontrados: noEncontradosAcumulados
                };
                me.listarArticulo(1, me.buscar, me.criterio);

                if (procesadosAcumulados.length > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Importación Masiva Finalizada!',
                        text: `Se procesaron ${procesadosAcumulados.length} de ${totalFiles} imágenes correctamente asignando ${articulosActualizadosSet.size} artículo(s).`
                    });
                } else {
                    Swal.fire('Atención', 'No se pudo asociar ninguna imagen. Verifique la tabla de resultados.', 'warning');
                }
            },
            toggleSeleccionarTodos() {
                if (this.seleccionarTodos) {
                    this.articulosSeleccionados = this.arrayArticulo.map(a => a.id);
                } else {
                    this.articulosSeleccionados = [];
                }
            },
            countSelectedInTree(node) {
                if (!node) return 0;
                let count = 0;
                if (this.categoriasSeleccionadasMasivas.includes(node.id)) {
                    count++;
                }
                if (node.sublevels && node.sublevels.length > 0) {
                    node.sublevels.forEach(child => {
                        count += this.countSelectedInTree(child);
                    });
                }
                return count;
            },
            getCategoriaNombre(catId) {
                let cat = this.arrayCategoria.find(c => c.id == catId);
                if (!cat) return 'Cat #' + catId;
                return (cat.nombre || '').replace(/^[›\s\-]+/, '').trim();
            },
            desmarcarCategoria(catId) {
                let idx = this.categoriasSeleccionadasMasivas.indexOf(catId);
                if (idx !== -1) {
                    this.categoriasSeleccionadasMasivas.splice(idx, 1);
                }
            },
            seleccionarTodasVisibles() {
                let me = this;
                function collectIds(nodes) {
                    nodes.forEach(n => {
                        if (!me.categoriasSeleccionadasMasivas.includes(n.id)) {
                            me.categoriasSeleccionadasMasivas.push(n.id);
                        }
                        if (n.sublevels && n.sublevels.length > 0) {
                            collectIds(n.sublevels);
                        }
                    });
                }
                collectIds(this.rootNodesVisibles);
            },
            onCategoryCheckboxChange(catId) {
                let isChecked = this.categoriasSeleccionadasMasivas.includes(catId);
                if (isChecked) {
                    this.seleccionarConPadres(catId);
                }
            },
            toggleCategoryBySpan(catId) {
                let idx = this.categoriasSeleccionadasMasivas.indexOf(catId);
                if (idx === -1) {
                    this.categoriasSeleccionadasMasivas.push(catId);
                    this.seleccionarConPadres(catId);
                } else {
                    this.categoriasSeleccionadasMasivas.splice(idx, 1);
                }
            },
            seleccionarConPadres(catId) {
                let currentId = catId;
                while (currentId) {
                    let catObj = this.arrayCategoria.find(c => c.id == currentId);
                    if (catObj && catObj.padre_id) {
                        let parentId = catObj.padre_id;
                        if (!this.categoriasSeleccionadasMasivas.includes(parentId)) {
                            this.categoriasSeleccionadasMasivas.push(parentId);
                        }
                        currentId = parentId;
                    } else {
                        currentId = null;
                    }
                }
            },
            abrirModalAsignacionMasiva() {
                if (this.articulosSeleccionados.length === 0) return;
                let firstArtId = this.articulosSeleccionados[0];
                this.articuloSeleccionadoModal = this.arrayArticulo.find(a => a.id === firstArtId) || null;
                this.tituloModalCategorias = 'Asignación Masiva de Categorías';
                this.categoriasSeleccionadasMasivas = [];
                this.modoAsignacion = 'reemplazar';
                this.modalAsignacionMasiva = 1;
                this.selectCategoria();
            },
            abrirModalAsignacionRapida(articulo) {
                this.articuloSeleccionadoModal = articulo;
                this.articulosSeleccionados = [articulo.id];
                this.tituloModalCategorias = 'Asignación de Categorías: ' + articulo.nombre;
                let cats = [];
                if (articulo.categorias && articulo.categorias.length > 0) {
                    cats = articulo.categorias.map(c => c.id);
                } else if (articulo.idcategoria) {
                    cats = [articulo.idcategoria];
                }
                this.categoriasSeleccionadasMasivas = cats;
                this.modoAsignacion = 'reemplazar';
                this.modalAsignacionMasiva = 1;
                this.selectCategoria();
            },
            cerrarModalAsignacionMasiva() {
                this.modalAsignacionMasiva = 0;
                this.cargandoMasivo = false;
            },
            ejecutarAsignacionMasiva() {
                if (this.articulosSeleccionados.length === 0 || this.categoriasSeleccionadasMasivas.length === 0) return;
                let me = this;
                me.cargandoMasivo = true;

                axios.post('/articulo/asignarCategoriasMasivas', {
                    articulo_ids: me.articulosSeleccionados,
                    categoria_ids: me.categoriasSeleccionadasMasivas,
                    modo: me.modoAsignacion
                })
                .then(function (response) {
                    me.cargandoMasivo = false;
                    me.cerrarModalAsignacionMasiva();
                    me.articulosSeleccionados = [];
                    me.seleccionarTodos = false;
                    me.listarArticulo(me.pagination.current_page || 1, me.buscar, me.criterio);

                    Swal.fire({
                        icon: 'success',
                        title: '¡Categorías Asignadas!',
                        text: (response.data && response.data.message) || 'Categorías actualizadas con éxito.'
                    });
                })
                .catch(function (error) {
                    me.cargandoMasivo = false;
                    let msg = (error.response && error.response.data && error.response.data.message) 
                        ? error.response.data.message 
                        : 'Error al asignar categorías';
                    Swal.fire('Error', msg, 'error');
                });
            }
        },
        mounted() {
            this.listarArticulo(1,this.buscar,this.criterio);
            this.cargarCortesDB();
        }
    }
</script>
<style>   
    .cantidades{
        display: flex;
        flex-direction: row;
        list-style: none;
        flex-wrap: wrap;
        padding:0;
        margin: 5px;
    } 
     .modal-content{
        width: 100% !important;
    }
    .mostrar{
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(0,0,0,0.5) !important;
        overflow-y: hidden !important;
        z-index: 10500 !important;
    }
    .mostrar .modal-dialog {
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
    .imgM{
        display:flex;
        flex-direction:row;
    }
    .mini-G input, .mini input{
        width:100px;
    }
    .mini-G{
        border:5px solid rgb(81, 158, 81);
        box-shadow: 0 0 5px rgba(0,0,0,0.5);
        margin-left:10px;
        display:flex;
        flex-direction:column;
    }
    .mini{
        border:5px solid rgb(158, 81, 81);
        box-shadow: 0 0 5px rgba(0,0,0,0.5);
        margin-left:10px;
        display:flex;
        flex-direction:column;
    }
</style>

