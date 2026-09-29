<template>
    <main class="main">
        <div class="container-fluid">
            <div class="card mt-3 border-brand-purple">
                <div class="card-header brand-purple-bg text-white">
                    <i class="fa fa-align-justify"></i> Atributos de Tienda
                    <div class="float-right">
                        <button type="button" @click="abrirModalConfig()" class="btn btn-info btn-sm">
                            <i class="icon-settings"></i>&nbsp;Configuración
                        </button>
                        <button type="button" @click="abrirModal('registrar')" class="btn btn-brand-green btn-sm">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr class="brand-purple-bg text-white">
                                <th>Tipo</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Etiquetas (Búsqueda)</th>
                                <th>Valor Extra</th>
                                <th>Buscable</th>
                                <th>Múltiple</th>
                                <th>Visible</th>
                                <th>Estado</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="atributo in arrayAtributos" :key="atributo.id">
                                <td v-text="atributo.tipo"></td>
                                <td>
                                    <img v-if="atributo.imagen" :src="'img/atributos/' + atributo.imagen" style="width: 50px; height: 50px;" class="img-thumbnail">
                                </td>
                                <td v-text="atributo.nombre"></td>
                                <td v-text="atributo.etiquetas"></td>
                                <td v-text="atributo.valor_extra"></td>
                                <td>
                                    <span v-if="atributo.es_buscable" class="badge badge-info">Sí</span>
                                    <span v-else class="badge badge-secondary">No</span>
                                </td>
                                <td>
                                    <span v-if="atributo.seleccion_multiple" class="badge badge-warning">Sí</span>
                                    <span v-else class="badge badge-secondary">No</span>
                                </td>
                                <td>
                                    <button type="button" @click="toggleVisibilidad(atributo)" class="btn btn-sm" :class="atributo.mostrar_en_producto ? 'btn-success' : 'btn-danger'" :title="atributo.mostrar_en_producto ? 'Visible en Tienda' : 'No mostrable'">
                                        <i class="fa" :class="atributo.mostrar_en_producto ? 'fa-eye' : 'fa-eye-slash'"></i>
                                        {{ atributo.mostrar_en_producto ? 'Mostrable' : 'Oculto' }}
                                    </button>
                                </td>
                                <td>
                                    <div v-if="atributo.activo">
                                        <span class="badge badge-brand-green">Activo</span>
                                    </div>
                                    <div v-else>
                                        <span class="badge badge-danger">Desactivado</span>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" @click="abrirModal('actualizar', atributo)" class="btn btn-warning btn-sm">
                                        <i class="icon-pencil"></i>
                                    </button> &nbsp;
                                    <template v-if="atributo.activo">
                                        <button type="button" class="btn btn-danger btn-sm" @click="desactivarAtributo(atributo.id)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button type="button" class="btn btn-info btn-sm" @click="activarAtributo(atributo.id)">
                                            <i class="icon-check"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header brand-purple-bg text-white">
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close text-white" @click="cerrarModal()" aria-label="Close">
                          <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row mb-1">
                                <label class="col-md-3 form-control-label" for="text-input">Tipo</label>
                                <div class="col-md-9">
                                    <input type="text" list="tiposList" class="form-control form-control-sm" v-model="tipo" placeholder="Seleccione o escriba un nuevo tipo">
                                    <datalist id="tiposList">
                                        <option v-for="t in arrayTipos" :key="t" :value="t">{{ t }}</option>
                                    </datalist>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-md-3 form-control-label">Nombre</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombre" class="form-control form-control-sm" placeholder="Nombre del atributo">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-md-3 form-control-label">Etiquetas</label>
                                <div class="col-md-9">
                                    <textarea v-model="etiquetas" class="form-control form-control-sm" rows="2" placeholder="Etiqueta 1, Etiqueta 2, ..."></textarea>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-md-3 form-control-label">Imagen Guía</label>
                                <div class="col-md-9">
                                    <input type="file" @change="onImageChange" class="form-control form-control-sm">
                                    <div v-if="imagenPreview" class="mt-1">
                                        <img :src="imagenPreview" style="width: 60px; height: 60px;" class="img-thumbnail">
                                    </div>
                                    <div v-else-if="imagen" class="mt-1">
                                        <img :src="'/img/atributos/'+imagen" style="width: 60px; height: 60px;" class="img-thumbnail">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-md-3 form-control-label">Valor Extra</label>
                                <div class="col-md-9">
                                    <input type="number" v-model="valor_extra" class="form-control form-control-sm" step="0.01">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-md-3 form-control-label">Opciones</label>
                                <div class="col-md-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="buscable" v-model="es_buscable">
                                        <label class="form-check-label" for="buscable">¿Buscable?</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="multiple" v-model="seleccion_multiple">
                                        <label class="form-check-label" for="multiple">¿Múltiple?</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="mostrar_p" v-model="mostrar_en_producto" :true-value="0" :false-value="1">
                                        <label class="form-check-label text-danger font-weight-bold" for="mostrar_p">¿Ocultar en Producto? (No mostrable)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-brand-purple">Condición de Cálculo</label>
                                    <textarea v-model="condicion" class="form-control" placeholder="Ej: divisor >= 8" rows="2"></textarea>
                                    <small class="text-muted">Define cuándo aplica este valor (ej: rango de tamaño).</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-brand-purple">Fórmula</label>
                                    <textarea v-model="formula" class="form-control" placeholder="Ej: {valor} * {tintas}" rows="2"></textarea>
                                    <small class="text-muted">Variables: {valor}, {cantidad}, {divisor}, {tintas}, {dependencia}.</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-brand-purple">Depende de (Sección)</label>
                                    <select v-model="dependencia" class="form-control">
                                        <option value="">Ninguna</option>
                                        <option v-for="t in arrayTipos" :key="t" :value="t">{{ t }}</option>
                                    </select>
                                    <small class="text-muted">Si el cálculo depende de lo elegido en otra sección (ej: Tinta).</small>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-md-3 form-control-label font-weight-bold text-brand-purple" style="font-size: 0.8rem;">
                                    ¿En qué productos se muestra?
                                    <div class="mt-1">
                                        <button type="button" @click="seleccionarTodos()" class="btn btn-xs btn-outline-info p-0" style="font-size: 0.6rem;">Todos</button>
                                        <button type="button" @click="desmarcarTodos()" class="btn btn-xs btn-outline-secondary p-0" style="font-size: 0.6rem;">Ninguno</button>
                                    </div>
                                </label>
                                <div class="col-md-9">
                                    <div class="border rounded p-1 d-flex flex-wrap pl-2" style="max-height: 120px; overflow-y: auto; background: #fff;">
                                        <div v-for="tp in arrayTiposProducto" :key="tp.id" class="form-check mb-1 mr-3 ml-4">
                                            <input type="checkbox" class="form-check-input" :id="'tp'+tp.id" :value="tp.id" v-model="tipos_seleccionados" style="width: 15px; height: 15px; cursor: pointer;">
                                            <label class="form-check-label cursor-pointer ml-1" :for="'tp'+tp.id" style="font-size: 0.9rem;">{{ tp.nombre }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                        <button type="button" v-if="tipoAccion==1" class="btn btn-brand-purple" @click="registrarAtributo()">Guardar</button>
                        <button type="button" v-if="tipoAccion==2" class="btn btn-brand-purple" @click="actualizarAtributo()">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Configuración -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalConfig}" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-info" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Configuración de Precios Tienda</h4>
                        <button type="button" class="close" @click="modalConfig=0">
                          <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group border-bottom pb-3 mb-3">
                            <label class="font-weight-bold text-brand-purple">Modo de Visualización de Productos en la Web</label>
                            <select v-model="modo_vista_producto" class="form-control">
                                <option value="ambos">Mostrar Ambos (Personalizar Cotizador + Galería Zoom y Pedir por WhatsApp)</option>
                                <option value="personalizar">Solo Personalizar Producto (Cotizador dinámico)</option>
                                <option value="zoom">Solo Imágenes con Zoom y Pedir por WhatsApp</option>
                            </select>
                            <small class="text-muted">Define cómo verán los clientes el detalle de los productos en la web pública.</small>
                        </div>
                        <div class="form-group border-bottom pb-3 mb-3">
                            <label class="font-weight-bold text-brand-purple">Número de WhatsApp de Contacto / Pedidos</label>
                            <input type="text" v-model="numero_whatsapp" class="form-control" placeholder="Ej: 573101234567">
                            <small class="text-muted">Número con código de país para recibir los pedidos directos por WhatsApp.</small>
                        </div>
                        <div class="form-group">
                            <label>Incremento por Coincidencia (%)</label>
                            <input type="number" v-model="inc_match" class="form-control">
                            <small class="text-muted">Porcentaje que se suma al encontrar una orden previa idéntica.</small>
                        </div>
                        <div class="form-group">
                            <label>Incremento Anual (%)</label>
                            <input type="number" v-model="inc_anual" class="form-control">
                            <small class="text-muted">Porcentaje que se suma por cada año de antigüedad de la orden encontrada.</small>
                        </div>
                        <div class="form-group">
                            <label>IVA (%)</label>
                            <input type="number" v-model="iva" class="form-control">
                            <small class="text-muted">Impuesto al valor agregado aplicado al precio final.</small>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Octavo (>=)</label>
                                    <input type="number" v-model="threshold_octavo" class="form-control">
                                    <small class="text-muted">Ej: 8</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cuarto (>=)</label>
                                    <input type="number" v-model="threshold_cuarto" class="form-control">
                                    <small class="text-muted">Ej: 4</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Medio (>=)</label>
                                    <input type="number" v-model="threshold_medio" class="form-control">
                                    <small class="text-muted">Ej: 2</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modalConfig=0">Cerrar</button>
                        <button type="button" class="btn btn-primary" @click="guardarConfig()">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            atributo_id: 0,
            tipo: 'Tinta',
            nombre: '',
            etiquetas: '',
            imagen: null,
            imagenFile: null,
            imagenPreview: null,
            valor_extra: 0,
            formula: '',
            condicion: '',
            dependencia: '',
            es_buscable: true,
            seleccion_multiple: false,
            mostrar_en_producto: true,
            tipos_seleccionados: [],
            arrayAtributos: [],
            arrayTiposProducto: [],
            modal: 0,
            modalConfig: 0,
            modo_vista_producto: 'ambos',
            numero_whatsapp: '',
            inc_match: 20,
            inc_anual: 10,
            iva: 19,
            threshold_octavo: 8,
            threshold_cuarto: 4,
            threshold_medio: 2,
            tituloModal: '',
            tipoAccion: 0
        }
    },
    computed: {
        arrayTipos() {
            let tipos = [];
            this.arrayAtributos.forEach(item => {
                if (!tipos.includes(item.tipo)) {
                    tipos.push(item.tipo);
                }
            });
            if (tipos.length === 0) return ['Tinta', 'Papel', 'Acabado', 'Terminado'];
            return tipos;
        }
    },
    methods: {
        listarAtributos() {
            let me = this;
            axios.get('/api/tienda/atributos-todos').then(function (response) {
                me.arrayAtributos = response.data;
            });
        },
        listarTiposProducto() {
            let me = this;
            axios.get('/api/tienda/tipos-producto').then(function (response) {
                me.arrayTiposProducto = response.data;
            });
        },
        onImageChange(e) {
            this.imagenFile = e.target.files[0];
            if (this.imagenFile) {
                this.imagenPreview = URL.createObjectURL(this.imagenFile);
            }
        },
        seleccionarTodos() {
            this.tipos_seleccionados = this.arrayTiposProducto.map(tp => tp.id);
        },
        desmarcarTodos() {
            this.tipos_seleccionados = [];
        },
        abrirModalConfig() {
            let me = this;
            axios.get('/ajustes/listar?tipo=tienda').then(function (response) {
                let data = response.data;
                data.forEach(ajuste => {
                    if (ajuste.detalle === 'modo_vista_producto') me.modo_vista_producto = ajuste.valor;
                    if (ajuste.detalle === 'numero_whatsapp') me.numero_whatsapp = ajuste.valor;
                    if (ajuste.detalle === 'incremento_match') me.inc_match = ajuste.valor;
                    if (ajuste.detalle === 'incremento_anual') me.inc_anual = ajuste.valor;
                    if (ajuste.detalle === 'iva') me.iva = ajuste.valor;
                    if (ajuste.detalle === 'threshold_octavo') me.threshold_octavo = ajuste.valor;
                    if (ajuste.detalle === 'threshold_cuarto') me.threshold_cuarto = ajuste.valor;
                    if (ajuste.detalle === 'threshold_medio') me.threshold_medio = ajuste.valor;
                });
                me.modalConfig = 1;
            });
        },
        guardarConfig() {
            let me = this;
            axios.get('/ajustes/listar?tipo=tienda').then(function (response) {
                let data = response.data;
                let modoObj = data.find(a => a.detalle === 'modo_vista_producto');
                let wsObj = data.find(a => a.detalle === 'numero_whatsapp');
                let matchObj = data.find(a => a.detalle === 'incremento_match');
                let anualObj = data.find(a => a.detalle === 'incremento_anual');
                let ivaObj = data.find(a => a.detalle === 'iva');
                let octavoObj = data.find(a => a.detalle === 'threshold_octavo');
                let cuartoObj = data.find(a => a.detalle === 'threshold_cuarto');
                let medioObj = data.find(a => a.detalle === 'threshold_medio');

                if (modoObj) {
                    axios.put('/ajustes/actualizar', { id: modoObj.id, tipo: 'tienda', detalle: 'modo_vista_producto', valor: me.modo_vista_producto });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'modo_vista_producto', valor: me.modo_vista_producto });
                }

                if (wsObj) {
                    axios.put('/ajustes/actualizar', { id: wsObj.id, tipo: 'tienda', detalle: 'numero_whatsapp', valor: me.numero_whatsapp });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'numero_whatsapp', valor: me.numero_whatsapp });
                }

                if (matchObj) {
                    axios.put('/ajustes/actualizar', { id: matchObj.id, tipo: 'tienda', detalle: 'incremento_match', valor: me.inc_match });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'incremento_match', valor: me.inc_match });
                }
                
                if (anualObj) {
                    axios.put('/ajustes/actualizar', { id: anualObj.id, tipo: 'tienda', detalle: 'incremento_anual', valor: me.inc_anual });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'incremento_anual', valor: me.inc_anual });
                }

                if (ivaObj) {
                    axios.put('/ajustes/actualizar', { id: ivaObj.id, tipo: 'tienda', detalle: 'iva', valor: me.iva });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'iva', valor: me.iva });
                }

                if (octavoObj) {
                    axios.put('/ajustes/actualizar', { id: octavoObj.id, tipo: 'tienda', detalle: 'threshold_octavo', valor: me.threshold_octavo });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'threshold_octavo', valor: me.threshold_octavo });
                }

                if (cuartoObj) {
                    axios.put('/ajustes/actualizar', { id: cuartoObj.id, tipo: 'tienda', detalle: 'threshold_cuarto', valor: me.threshold_cuarto });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'threshold_cuarto', valor: me.threshold_cuarto });
                }

                if (medioObj) {
                    axios.put('/ajustes/actualizar', { id: medioObj.id, tipo: 'tienda', detalle: 'threshold_medio', valor: me.threshold_medio });
                } else {
                    axios.post('/ajustes/registrar', { tipo: 'tienda', detalle: 'threshold_medio', valor: me.threshold_medio });
                }
                
                me.modalConfig = 0;
                swal('Guardado', 'Configuración de la tienda web actualizada correctamente', 'success');
            });
        },
        registrarAtributo() {
            let me = this;
            let formData = new FormData();
            formData.append('tipo', me.tipo);
            formData.append('nombre', me.nombre);
            formData.append('etiquetas', me.etiquetas);
            formData.append('valor_extra', me.valor_extra);
            formData.append('formula', me.formula);
            formData.append('condicion', me.condicion);
            formData.append('dependencia', me.dependencia);
            formData.append('es_buscable', me.es_buscable ? 1 : 0);
            formData.append('seleccion_multiple', me.seleccion_multiple ? 1 : 0);
            formData.append('mostrar_en_producto', me.mostrar_en_producto ? 1 : 0);
            formData.append('tipos_producto', JSON.stringify(me.tipos_seleccionados));
            if (me.imagenFile) {
                formData.append('imagen', me.imagenFile);
            }

            axios.post('/api/tienda/atributos', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(function (response) {
                me.cerrarModal();
                me.listarAtributos();
            });
        },
        actualizarAtributo() {
            let me = this;
            let formData = new FormData();
            formData.append('id', me.atributo_id);
            formData.append('tipo', me.tipo);
            formData.append('nombre', me.nombre);
            formData.append('etiquetas', me.etiquetas);
            formData.append('valor_extra', me.valor_extra);
            formData.append('formula', me.formula);
            formData.append('condicion', me.condicion);
            formData.append('dependencia', me.dependencia);
            formData.append('es_buscable', me.es_buscable ? 1 : 0);
            formData.append('seleccion_multiple', me.seleccion_multiple ? 1 : 0);
            formData.append('mostrar_en_producto', me.mostrar_en_producto ? 1 : 0);
            formData.append('tipos_producto', JSON.stringify(me.tipos_seleccionados));
            if (me.imagenFile) {
                formData.append('imagen', me.imagenFile);
            }
            formData.append('_method', 'PUT');

            axios.post('/api/tienda/atributos/actualizar', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(function (response) {
                me.cerrarModal();
                me.listarAtributos();
            });
        },
        desactivarAtributo(id) {
            let me = this;
            axios.put('/api/tienda/atributos/desactivar', { 'id': id }).then(function (response) {
                me.listarAtributos();
            });
        },
        activarAtributo(id) {
            let me = this;
            axios.put('/api/tienda/atributos/activar', { 'id': id }).then(function (response) {
                me.listarAtributos();
            });
        },
        toggleVisibilidad(atributo) {
            let me = this;
            atributo.mostrar_en_producto = !atributo.mostrar_en_producto;
            let formData = new FormData();
            formData.append('id', atributo.id);
            formData.append('tipo', atributo.tipo);
            formData.append('nombre', atributo.nombre);
            formData.append('mostrar_en_producto', atributo.mostrar_en_producto ? 1 : 0);
            formData.append('_method', 'PUT');

            axios.post('/api/tienda/atributos/actualizar', formData).then(response => {
                // No es necesario recargar, el binding de Vue ya actualizó la UI
            }).catch(error => {
                atributo.mostrar_en_producto = !atributo.mostrar_en_producto; // Revertir en caso de error
                console.error(error);
            });
        },
        abrirModal(modelo, data = []) {
            this.modal = 1;
            if (modelo == 'registrar') {
                this.tituloModal = 'Registrar Atributo';
                this.tipoAccion = 1;
                this.tipo = 'Tinta';
                this.nombre = '';
                this.etiquetas = '';
                this.imagen = null;
                this.imagenFile = null;
                this.imagenPreview = null;
                this.valor_extra = 0;
                this.formula = '';
                this.condicion = '';
                this.dependencia = '';
                this.es_buscable = true;
                this.seleccion_multiple = false;
                this.mostrar_en_producto = true;
                this.tipos_seleccionados = [];
            } else {
                this.tituloModal = 'Actualizar Atributo';
                this.tipoAccion = 2;
                this.atributo_id = data['id'];
                this.tipo = data['tipo'];
                this.nombre = data['nombre'];
                this.etiquetas = data['etiquetas'];
                this.imagen = data['imagen'];
                this.imagenFile = null;
                this.imagenPreview = null;
                this.valor_extra = data['valor_extra'];
                this.formula = data['formula'];
                this.condicion = data['condicion'];
                this.dependencia = data['dependencia'];
                this.es_buscable = data['es_buscable'] == 1 || data['es_buscable'] === true;
                this.seleccion_multiple = data['seleccion_multiple'] == 1 || data['seleccion_multiple'] === true;
                this.mostrar_en_producto = data['mostrar_en_producto'] == 1 || data['mostrar_en_producto'] === true;
                this.tipos_seleccionados = data['tipos_producto'] ? data['tipos_producto'].map(tp => tp.id) : [];
            }
        },
        cerrarModal() {
            this.modal = 0;
            this.imagenPreview = null;
        }
    },
    mounted() {
        this.listarAtributos();
        this.listarTiposProducto();
    }
}
</script>

<style>
.mostrar {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    opacity: 1 !important;
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8) !important;
    z-index: 10000 !important;
}
.mostrar .modal-dialog {
    margin: auto !important;
    width: 95%;
    max-width: 800px;
    display: flex !important;
    align-items: center !important;
    top: 0 !important;
}
.mostrar .modal-content {
    max-height: 85vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}
.mostrar .modal-body {
    overflow-y: auto !important;
    flex: 1 1 auto !important;
}
.mostrar .modal-footer, .mostrar .modal-header {
    flex: 0 0 auto !important;
}
.brand-purple-bg { background-color: #7d3c98 !important; }
.border-brand-purple { border-color: #7d3c98 !important; }
.btn-brand-purple { background-color: #7d3c98 !important; color: white !important; }
.btn-brand-green { background-color: #6ab04c !important; color: white !important; }
.badge-brand-green { background-color: #6ab04c !important; color: white !important; }
.cursor-pointer { cursor: pointer; }
</style>
