<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Página Web</a></li>
            <li class="breadcrumb-item active">Gestión de Sliders</li>
        </ol>

        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-sliders fa-2x text-primary mr-3"></i>
                        <div>
                            <h4 class="mb-0 font-weight-bold text-dark">Sliders y Banners Web</h4>
                            <small class="text-muted">Administra las imágenes destacadas y anuncios principales de tu sitio web</small>
                        </div>
                    </div>
                    <button type="button" @click="abrirModal('slider', 'registrar')" class="btn btn-primary font-weight-bold px-3">
                        <i class="fa fa-plus-circle"></i> Nuevo Slider
                    </button>
                </div>

                <div class="card-body">
                    <!-- Nav Tabs por Ubicación -->
                    <ul class="nav nav-pills mb-4 border-bottom pb-2">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: ubicacionFiltro === 'principal' }" href="#" @click.prevent="cambiarUbicacion('principal')">
                                <i class="fa fa-desktop"></i> Slider Principal (Home)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: ubicacionFiltro === 'promociones' }" href="#" @click.prevent="cambiarUbicacion('promociones')">
                                <i class="fa fa-tags"></i> Banners Promocionales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: ubicacionFiltro === 'secundario' }" href="#" @click.prevent="cambiarUbicacion('secundario')">
                                <i class="fa fa-th-large"></i> Banners Secundarios
                            </a>
                        </li>
                    </ul>

                    <!-- Filtros y Búsqueda -->
                    <div class="form-group row">
                        <div class="col-md-6 col-lg-5">
                            <div class="input-group">
                                <select class="form-control col-md-4" v-model="criterio">
                                    <option value="titulo">Título</option>
                                    <option value="subtitulo">Subtítulo</option>
                                </select>
                                <input type="text" v-model="buscar" @keyup.enter="listarSlider(1, buscar, criterio)" class="form-control" placeholder="Buscar texto...">
                                <span class="input-group-btn">
                                    <button type="button" @click="listarSlider(1, buscar, criterio)" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Buscar
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Sliders -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 120px;" class="text-center">Vista Previa</th>
                                    <th>Título & Subtítulo</th>
                                    <th>Botón / Enlace</th>
                                    <th class="text-center">Orden</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center" style="width: 160px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="slider in arraySlider" :key="slider.id">
                                    <!-- Imagen Vista Previa -->
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="mr-2" title="Imagen Computador / Desktop">
                                                <img :src="getUrl(slider.imagen)" class="rounded shadow-sm border" style="width: 80px; height: 48px; object-fit: cover;" alt="Slider Desktop" />
                                                <small class="d-block text-muted" style="font-size: 9px;"><i class="fa fa-desktop"></i> Desktop</small>
                                            </div>
                                            <div v-if="slider.imagen_movil" title="Imagen Celular / Móvil">
                                                <img :src="getUrl(slider.imagen_movil)" class="rounded shadow-sm border" style="width: 32px; height: 48px; object-fit: cover;" alt="Slider Móvil" />
                                                <small class="d-block text-primary" style="font-size: 9px;"><i class="fa fa-mobile"></i> Móvil</small>
                                            </div>
                                            <div v-else title="Sin imagen móvil personalizada (usa desktop)">
                                                <span class="badge badge-light border text-muted" style="font-size: 9px;"><i class="fa fa-mobile"></i> Auto</span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Título / Subtítulo -->
                                    <td>
                                        <div class="font-weight-bold text-dark mb-1">{{ slider.titulo || '(Sin Título)' }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 280px;">{{ slider.subtitulo || '-' }}</div>
                                    </td>
                                    <!-- Botón -->
                                    <td>
                                        <div v-if="slider.texto_boton" class="mb-1">
                                            <span class="badge badge-info px-2 py-1"><i class="fa fa-mouse-pointer"></i> {{ slider.texto_boton }}</span>
                                        </div>
                                        <small v-if="slider.url_boton" class="text-primary d-block text-truncate" style="max-width: 220px;">
                                            <i class="fa fa-link"></i> {{ slider.url_boton }}
                                        </small>
                                        <span v-else class="text-muted small">Sin botón</span>
                                    </td>
                                    <!-- Orden -->
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-secondary px-2 py-1">{{ slider.orden }}</span>
                                    </td>
                                    <!-- Estado -->
                                    <td class="text-center">
                                        <span v-if="slider.estado == 1" class="badge badge-success px-2 py-1">
                                            <i class="fa fa-check-circle"></i> Activo
                                        </span>
                                        <span v-else class="badge badge-danger px-2 py-1">
                                            <i class="fa fa-times-circle"></i> Inactivo
                                        </span>
                                    </td>
                                    <!-- Acciones -->
                                    <td class="text-center">
                                        <button type="button" @click="abrirModal('slider', 'actualizar', slider)" class="btn btn-warning btn-sm text-white" title="Editar">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        
                                        <template v-if="slider.estado == 1">
                                            <button type="button" class="btn btn-danger btn-sm" @click="desactivarSlider(slider.id)" title="Desactivar">
                                                <i class="fa fa-eye-slash"></i>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button type="button" class="btn btn-info btn-sm text-white" @click="activarSlider(slider.id)" title="Activar">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </template>

                                        <button type="button" class="btn btn-outline-danger btn-sm" @click="eliminarSlider(slider.id)" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="arraySlider.length === 0">
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa fa-info-circle fa-2x mb-2 d-block"></i>
                                        No hay sliders registrados para esta ubicación. Haz clic en <strong>Nuevo Slider</strong> para agregar uno.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)" v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- MODAL CREAR / EDITAR SLIDER -->
            <div class="modal fade" tabindex="-1" :class="{ 'show': modal }" role="dialog" :style="modal ? 'display: block; background: rgba(0,0,0,0.5); overflow-y: auto;' : 'display: none;'">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title font-weight-bold" v-text="tituloModal"></h5>
                            <button type="button" class="close text-white" @click="cerrarModal()" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Errores de validación -->
                            <div v-if="errorSlider" class="alert alert-danger mb-3">
                                <ul class="mb-0 pl-3">
                                    <li v-for="error in errorMostrarMsjSlider" :key="error" v-text="error"></li>
                                </ul>
                            </div>

                            <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                                <div class="row">
                                    <!-- Campos Formulario -->
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Título Encabezado</label>
                                            <input type="text" v-model="titulo" class="form-control" placeholder="Ej: Las Mejores Soluciones en Empaques">
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">Subtítulo / Descripción</label>
                                            <textarea v-model="subtitulo" rows="2" class="form-control" placeholder="Ej: Calidad garantizada para tu negocio al mejor precio."></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Texto del Botón</label>
                                                    <input type="text" v-model="texto_boton" class="form-control" placeholder="Ej: Ver Catálogo">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">URL / Enlace del Botón</label>
                                                    <input type="text" v-model="url_boton" class="form-control" placeholder="Ej: /catalogo o https://...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Ubicación del Banner</label>
                                                    <select v-model="ubicacion" class="form-control">
                                                        <option value="principal">Slider Principal (Home)</option>
                                                        <option value="promociones">Banners Promocionales</option>
                                                        <option value="secundario">Banners Secundarios</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Orden Aparición</label>
                                                    <input type="number" v-model="orden" class="form-control" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Alto Slider (px)</label>
                                                    <select v-model="alto_slider" class="form-control">
                                                        <option :value="300">300 px (Muy Bajo)</option>
                                                        <option :value="380">380 px (Compacto)</option>
                                                        <option :value="450">450 px (Recomendado)</option>
                                                        <option :value="500">500 px (Medio Alto)</option>
                                                        <option :value="600">600 px (Alto)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">Imagen Computador / Desktop <span class="text-danger">*</span></label>
                                            <input type="file" ref="imagenInput" @change="obtenerImagen" accept="image/*" class="form-control-file border p-2 rounded bg-light">
                                            <small class="text-muted d-block mt-1">Recomendado: 1920x600 px (Horizontal)</small>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">Imagen Celular / Móvil <span class="badge badge-info font-weight-normal">Opcional</span></label>
                                            <input type="file" ref="imagenMovilInput" @change="obtenerImagenMovil" accept="image/*" class="form-control-file border p-2 rounded bg-light">
                                            <small class="text-muted d-block mt-1">Si la subes, se mostrará en teléfonos móviles. Si la dejas vacía, usará la imagen principal.</small>
                                        </div>
                                    </div>

                                    <!-- Previsualización en Vivo (Live Card Preview) -->
                                    <div class="col-md-5">
                                        <label class="font-weight-bold text-secondary">Vista Previa en Vivo:</label>
                                        <div class="card border-0 shadow rounded overflow-hidden position-relative mb-2" style="min-height: 180px; background: #1e293b; color: #fff;">
                                            <!-- Background Image -->
                                            <div v-if="imagenMiniautura" :style="`background-image: url(${imagenMiniautura}); background-size: cover; background-position: center; position: absolute; top:0; left:0; width:100%; height:100%; opacity: 0.65;`"></div>
                                            
                                            <!-- Overlay Content -->
                                            <div class="p-3 position-relative d-flex flex-column justify-content-center h-100" style="z-index: 2; background: rgba(0,0,0,0.35);">
                                                <small class="text-uppercase font-weight-bold text-warning mb-1" style="font-size: 9px;"><i class="fa fa-desktop"></i> Modo Pantalla Desktop</small>
                                                <h6 class="font-weight-bold mb-1 text-white text-truncate">{{ titulo || 'Título del Banner' }}</h6>
                                                <p class="small text-light mb-2 text-truncate" style="font-size: 11px;">{{ subtitulo || 'Subtítulo descriptivo...' }}</p>
                                                <div v-if="texto_boton" class="mt-1">
                                                    <span class="btn btn-primary btn-sm px-2 py-0 font-weight-bold shadow-sm" style="pointer-events: none; font-size: 10px;">
                                                        {{ texto_boton }} <i class="fa fa-arrow-right ml-1"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Vista Previa Celular -->
                                        <div v-if="imagenMovilMiniatura" class="card border-0 shadow rounded overflow-hidden position-relative" style="min-height: 120px; background: #0f172a; color: #fff;">
                                            <div :style="`background-image: url(${imagenMovilMiniatura}); background-size: cover; background-position: center; position: absolute; top:0; left:0; width:100%; height:100%; opacity: 0.7;`"></div>
                                            <div class="p-2 position-relative d-flex flex-column justify-content-center h-100" style="z-index: 2; background: rgba(0,0,0,0.4);">
                                                <small class="text-uppercase font-weight-bold text-info mb-1" style="font-size: 9px;"><i class="fa fa-mobile"></i> Modo Celular (Móvil)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary font-weight-bold" @click="cerrarModal()">Cancelar</button>
                            <button type="button" v-if="tipoAccion == 1" class="btn btn-primary font-weight-bold" @click="registrarSlider()">Guardar Slider</button>
                            <button type="button" v-if="tipoAccion == 2" class="btn btn-warning text-white font-weight-bold" @click="actualizarSlider()">Actualizar Slider</button>
                        </div>
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
            slider_id: 0,
            titulo: '',
            subtitulo: '',
            imagen: null,
            imagenMiniautura: null,
            imagen_movil: null,
            imagenMovilMiniatura: null,
            texto_boton: '',
            url_boton: '',
            ubicacion: 'principal',
            ubicacionFiltro: 'principal',
            orden: 0,
            alto_slider: 450,
            estado: 1,

            arraySlider: [],
            modal: 0,
            tituloModal: '',
            tipoAccion: 0,

            errorSlider: 0,
            errorMostrarMsjSlider: [],

            pagination: {
                total: 0,
                current_page: 0,
                per_page: 0,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 3,
            criterio: 'titulo',
            buscar: ''
        };
    },
    computed: {
        isActived() {
            return this.pagination.current_page;
        },
        pagesNumber() {
            if (!this.pagination.to) return [];
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) to = this.pagination.last_page;

            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        getUrl(path) {
            if (!path) return '';
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
            return path.startsWith('/') ? path : '/' + path;
        },

        listarSlider(page, buscar, criterio) {
            let me = this;
            let url = '/slider?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio + '&ubicacion=' + me.ubicacionFiltro;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.arraySlider = respuesta.sliders.data;
                me.pagination = respuesta.pagination;
            }).catch(function (error) {
                console.log(error);
            });
        },

        cambiarUbicacion(ubicacion) {
            this.ubicacionFiltro = ubicacion;
            this.ubicacion = ubicacion;
            this.listarSlider(1, this.buscar, this.criterio);
        },

        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarSlider(page, buscar, criterio);
        },

        obtenerImagen(e) {
            let file = e.target.files[0];
            if (file) {
                this.imagen = file;
                let reader = new FileReader();
                reader.onload = (e) => {
                    this.imagenMiniautura = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        obtenerImagenMovil(e) {
            let file = e.target.files[0];
            if (file) {
                this.imagen_movil = file;
                let reader = new FileReader();
                reader.onload = (e) => {
                    this.imagenMovilMiniatura = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        registrarSlider() {
            if (this.validarSlider()) {
                return;
            }

            let me = this;
            let formData = new FormData();
            formData.append('titulo', me.titulo || '');
            formData.append('subtitulo', me.subtitulo || '');
            formData.append('texto_boton', me.texto_boton || '');
            formData.append('url_boton', me.url_boton || '');
            formData.append('ubicacion', me.ubicacion || 'principal');
            formData.append('orden', me.orden || 0);
            formData.append('alto_slider', me.alto_slider || 450);
            if (me.imagen) {
                formData.append('imagen', me.imagen);
            }
            if (me.imagen_movil) {
                formData.append('imagen_movil', me.imagen_movil);
            }

            axios.post('/slider/registrar', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(function (response) {
                me.cerrarModal();
                me.listarSlider(1, '', 'titulo');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('¡Registrado!', 'El slider se ha guardado con éxito.', 'success');
                }
            }).catch(function (error) {
                console.log(error);
            });
        },

        actualizarSlider() {
            if (this.validarSliderUpdate()) {
                return;
            }

            let me = this;
            let formData = new FormData();
            formData.append('id', me.slider_id);
            formData.append('titulo', me.titulo || '');
            formData.append('subtitulo', me.subtitulo || '');
            formData.append('texto_boton', me.texto_boton || '');
            formData.append('url_boton', me.url_boton || '');
            formData.append('ubicacion', me.ubicacion || 'principal');
            formData.append('orden', me.orden || 0);
            formData.append('alto_slider', me.alto_slider || 450);
            if (me.imagen && typeof me.imagen === 'object') {
                formData.append('imagen', me.imagen);
            }
            if (me.imagen_movil && typeof me.imagen_movil === 'object') {
                formData.append('imagen_movil', me.imagen_movil);
            }

            axios.post('/slider/actualizar', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(function (response) {
                me.cerrarModal();
                me.listarSlider(1, '', 'titulo');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('¡Actualizado!', 'El slider se ha actualizado con éxito.', 'success');
                }
            }).catch(function (error) {
                console.log(error);
            });
        },

        desactivarSlider(id) {
            let me = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Desactivar Slider?',
                    text: 'El slider dejará de mostrarse en la página web.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.put('/slider/desactivar', { 'id': id }).then(function () {
                            me.listarSlider(me.pagination.current_page || 1, me.buscar, me.criterio);
                            Swal.fire('Desactivado', 'El slider fue desactivado.', 'success');
                        }).catch(function (error) {
                            console.error(error);
                            Swal.fire('Error', 'No se pudo desactivar el slider.', 'error');
                        });
                    }
                });
            } else {
                axios.put('/slider/desactivar', { 'id': id }).then(function () {
                    me.listarSlider(me.pagination.current_page || 1, me.buscar, me.criterio);
                }).catch(function (error) {
                    console.error(error);
                });
            }
        },

        activarSlider(id) {
            let me = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Activar Slider?',
                    text: 'El slider volverá a estar visible en la página web.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, activar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        axios.put('/slider/activar', { 'id': id }).then(function () {
                            me.listarSlider(me.pagination.current_page || 1, me.buscar, me.criterio);
                            Swal.fire('Activado', 'El slider ahora está visible.', 'success');
                        }).catch(function (error) {
                            console.error(error);
                            Swal.fire('Error', 'No se pudo activar el slider.', 'error');
                        });
                    }
                });
            } else {
                axios.put('/slider/activar', { 'id': id }).then(function () {
                    me.listarSlider(me.pagination.current_page || 1, me.buscar, me.criterio);
                }).catch(function (error) {
                    console.error(error);
                });
            }
        },

        eliminarSlider(id) {
            let me = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar Slider?',
                    text: 'Esta acción borrará permanentemente la imagen y datos del banner.',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626'
                }).then((result) => {
                    if (result.value) {
                        axios.post('/slider/eliminar', { 'id': id }).then(function () {
                            me.listarSlider(me.pagination.current_page, me.buscar, me.criterio);
                            Swal.fire('Eliminado', 'El slider ha sido eliminado.', 'success');
                        });
                    }
                });
            }
        },

        validarSlider() {
            this.errorSlider = 0;
            this.errorMostrarMsjSlider = [];
            if (!this.imagen) this.errorMostrarMsjSlider.push("Debes seleccionar una imagen principal para el slider.");
            if (this.errorMostrarMsjSlider.length) this.errorSlider = 1;
            return this.errorSlider;
        },

        validarSliderUpdate() {
            this.errorSlider = 0;
            this.errorMostrarMsjSlider = [];
            if (this.errorMostrarMsjSlider.length) this.errorSlider = 1;
            return this.errorSlider;
        },

        cerrarModal() {
            this.modal = 0;
            this.tituloModal = '';
            this.titulo = '';
            this.subtitulo = '';
            this.imagen = null;
            this.imagenMiniautura = null;
            this.imagen_movil = null;
            this.imagenMovilMiniatura = null;
            this.texto_boton = '';
            this.url_boton = '';
            this.orden = 0;
            this.alto_slider = 450;
            this.errorSlider = 0;
            if (this.$refs.imagenInput) this.$refs.imagenInput.value = '';
            if (this.$refs.imagenMovilInput) this.$refs.imagenMovilInput.value = '';
        },

        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "slider": {
                    switch (accion) {
                        case 'registrar': {
                            this.modal = 1;
                            this.tituloModal = 'Registrar Nuevo Slider / Banner';
                            this.titulo = '';
                            this.subtitulo = '';
                            this.imagen = null;
                            this.imagenMiniautura = null;
                            this.imagen_movil = null;
                            this.imagenMovilMiniatura = null;
                            this.texto_boton = '';
                            this.url_boton = '';
                            this.ubicacion = this.ubicacionFiltro;
                            this.orden = 0;
                            this.alto_slider = 450;
                            this.tipoAccion = 1;
                            break;
                        }
                        case 'actualizar': {
                            this.modal = 1;
                            this.tituloModal = 'Actualizar Slider';
                            this.tipoAccion = 2;
                            this.slider_id = data['id'];
                            this.titulo = data['titulo'];
                            this.subtitulo = data['subtitulo'];
                            this.texto_boton = data['texto_boton'];
                            this.url_boton = data['url_boton'];
                            this.ubicacion = data['ubicacion'];
                            this.orden = data['orden'];
                            this.alto_slider = data['alto_slider'] || 450;
                            this.imagen = data['imagen'];
                            this.imagenMiniautura = this.getUrl(data['imagen']);
                            this.imagen_movil = data['imagen_movil'];
                            this.imagenMovilMiniatura = data['imagen_movil'] ? this.getUrl(data['imagen_movil']) : null;
                            break;
                        }
                    }
                }
            }
        }
    },
    mounted() {
        this.listarSlider(1, this.buscar, this.criterio);
    }
}
</script>

<style scoped>
.modal-content {
    border-radius: 8px;
}
.nav-pills .nav-link {
    color: #475569;
    font-weight: 600;
    border-radius: 6px;
    margin-right: 6px;
}
.nav-pills .nav-link.active {
    background-color: #2563eb;
    color: #ffffff;
}
</style>
