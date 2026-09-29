
 
          
<template>
  <div class="contenedor">
    <div class="container-fluid">
      
      <div>
        <div class="contenedor-header d-flex justify-content-between align-items-center">
            <h5>Registrar Nueva Actividad</h5>
            <button v-if="user && user.idrol == 'Administrador'" 
                    class="btn btn-sm btn-dark" 
                    @click="abrirConfigModal()">
                <i class="fa fa-gear"></i> Configurar Botones
            </button>
        </div>
        <div class="contenedor-seccion">

          <div class="category-grid">
              <!-- Categorías Dinámicas -->
              <button v-for="cat in arrayCategorias" :key="cat.id" 
                      class="btn-category" 
                      :style="{ backgroundColor: cat.categoria }"
                      @click="selectCategoryAction(cat)">
                  <div class="icon-part"><i :class="['fa', cat.valor]"></i></div>
                  <div class="text-part">{{ cat.detalle }}</div>
              </button>

              <!-- Categoría de respaldo para actividades no asignadas -->
              <button v-if="unassignedCodigos.length > 0" 
                      class="btn-category btn-unassigned" 
                      @click="selectedCategory = { detalle: 'Sin Clasificar', categoria: '#7f8c8d' }">
                  <div class="icon-part"><i class="fa fa-question-circle"></i></div>
                  <div class="text-part">Otras / Sin Clasificar</div>
              </button>

              <!-- Botón de Administración (Movido arriba para mejor visibilidad, pero dejamos respaldo aquí) -->
              <button v-if="user && user.idrol == 'Administrador'" 
                      class="btn-category btn-config" 
                      @click="abrirConfigModal()">
                  <div class="icon-part"><i class="fa fa-gear"></i></div>
                  <div class="text-part">🔧 Administración</div>
              </button>
          </div>

          <div v-if="selectedCategory" class="sub-activities-container mt-4 animate__animated animate__fadeIn">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Seleccione la actividad de <strong>{{ selectedCategory.detalle }}</strong>:</h6>
                <button class="btn btn-sm btn-link" @click="selectedCategory = null">Cerrar</button>
              </div>
              <div class="sub-activities-grid">
                  <button v-for="(cod, index) in filteredCodigos" :key="index"
                          class="btn-sub"
                          :style="{ borderColor: selectedCategory.categoria || '#ccc', color: selectedCategory.categoria || '#333' }"
                          @click="quickRegister(cod)">
                      {{ cod.valor }}
                  </button>
                  <div v-if="filteredCodigos.length === 0" class="text-muted p-3">
                    No se encontraron actividades asignadas a esta categoría.
                  </div>
              </div>
          </div>
          
          <div v-if="searchText.length > 2" class="manual-search mt-3">
             <div class="form-group">
                <label>Buscando: <strong>{{ searchText }}</strong></label>
                <div class="search-results">
                   <button v-for="(cod, index) in searchedCodigos" :key="'s-'+index"
                           class="btn-sub btn-sub-search"
                           @click="quickRegister(cod)">
                       {{ cod.valor }}
                   </button>
                </div>
             </div>
          </div>
        </div>

        <!-- Modal de Configuración (Administrable) -->
        <div class="modal fade" :class="{'mostrar': configModal}" role="dialog" style="z-index: 10000;">
            <div class="modal-dialog modal-xl shadow-lg">
                <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                    <!-- HEADER FIJO -->
                    <div class="modal-header d-flex justify-content-between align-items-center" style="background: #2c3e50; color: white; padding: 15px 25px;">
                        <h4 class="modal-title mb-0" style="font-weight: 700;"><i class="fa fa-cogs mr-2"></i> Administración de Categorías y Actividades</h4>
                        <button type="button" class="btn btn-outline-light btn-sm rounded-circle" @click="configModal = 0" style="width: 32px; height: 32px; line-height: 1;">&times;</button>
                    </div>

                    <div class="modal-body p-0" style="background: #f8f9fa;">
                        <div class="container-fluid py-4" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                            <!-- SECCIÓN 1: CREAR CATEGORÍAS -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-white font-weight-bold text-primary border-bottom">
                                    1. Crear e Identificar Categorías (Botones)
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="small font-weight-bold">Nombre Categoría</label>
                                            <input type="text" class="form-control" v-model="nuevaCatNombre" placeholder="Ej: Impresión">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small font-weight-bold">Icono (Selecciona uno)</label>
                                            <div class="d-flex flex-wrap bg-white p-2 rounded border" style="max-height: 100px; overflow-y: auto; gap: 8px;">
                                                <button v-for="icon in commonIcons" :key="icon" 
                                                        type="button"
                                                        class="btn btn-sm"
                                                        :class="nuevaCatIcono === icon ? 'btn-primary' : 'btn-outline-secondary'"
                                                        @click="nuevaCatIcono = icon"
                                                        style="width: 35px; height: 35px; padding: 0;">
                                                    <i :class="['fa', icon]"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" v-model="nuevaCatIcono">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small font-weight-bold">Color del Botón</label>
                                            <input type="color" class="form-control" v-model="nuevaCatColor" style="height: 38px; padding: 2px;">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary btn-block font-weight-bold" @click="guardarCategoria()">AÑADIR</button>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th width="50">Icono</th>
                                                    <th>Nombre</th>
                                                    <th>Color</th>
                                                    <th width="100">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="cat in arrayCategorias" :key="'catlist-'+cat.id">
                                                    <td class="text-center"><i :class="['fa', cat.valor, 'text-muted']"></i></td>
                                                    <td class="font-weight-bold">{{ cat.detalle }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div :style="{ backgroundColor: cat.categoria, width: '30px', height: '15px', borderRadius: '10px', marginRight: '10px', border: '1px solid #ddd' }"></div>
                                                            <small class="text-muted">{{ cat.categoria }}</small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-outline-danger btn-sm border-0" @click="eliminarCategoria(cat.id)" title="Eliminar"><i class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                                <tr v-if="arrayCategorias.length === 0">
                                                    <td colspan="4" class="text-center p-4 text-muted">No has creado categorías. Crea una arriba para empezar.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 2: ASIGNAR ACTIVIDADES -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-white font-weight-bold text-success border-bottom">
                                    2. Clasificar Actividades bajo Botones
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 350px;">
                                        <table class="table table-striped table-hover table-sm mb-0">
                                            <thead class="bg-light" style="position: sticky; top: 0; z-index: 5;">
                                                <tr>
                                                    <th width="100">Código</th>
                                                    <th>Actividad</th>
                                                    <th width="250">Seleccionar Botón / Categoría</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="cod in codigos" :key="'asig-'+cod.id">
                                                    <td class="font-italic text-muted">{{ cod.detalle }}</td>
                                                    <td>{{ cod.valor }}</td>
                                                    <td>
                                                        <select class="form-control form-control-sm" v-model="cod.categoria" @change="actualizarAsignacion(cod)"
                                                                :style="{ borderLeft: '4px solid ' + (getCategoryColor(cod.categoria)) }">
                                                            <option value="">-- Sin Clasificar --</option>
                                                            <option v-for="cat in arrayCategorias" :key="cat.id" :value="cat.detalle">{{ cat.detalle }}</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 3: CREAR ACTIVIDAD -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white font-weight-bold text-info border-bottom">
                                    3. Dar de Alta Nueva Actividad Maestra
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-3">
                                            <label class="small font-weight-bold">ID / Código</label>
                                            <input type="text" class="form-control" v-model="nuevoCodID" placeholder="Ej: 50">
                                        </div>
                                        <div class="col-md-7">
                                            <label class="small font-weight-bold">Nombre Completo</label>
                                            <input type="text" class="form-control" v-model="nuevoCodNombre" placeholder="Ej: Control de Tintas Especial">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-success btn-block font-weight-bold" @click="guardarActividadMaestra()">CREAR</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER FIJO -->
                    <div class="modal-footer shadow-sm" style="background: white; padding: 15px 25px;">
                        <button type="button" class="btn btn-secondary px-5 font-weight-bold" @click="configModal = 0">CERRAR CONFIGURACIÓN</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="contenedor-seccion">

          <ol class="header-registro">
            <li class="dato-registro"><div class="titulo-r">Nombre:</div> <div>{{ empleado.nombre }} {{ empleado.apellido }}</div></li>
            <li class="dato-registro d-flex align-items-center">
                <div class="titulo-r mr-2">Fecha: </div> 
                <template v-if="user && (user.idrol == 'Administrador' || user.idrol == 'Auxiliar producción' || user.idrol == 'Auxiliar de producción')">
                    <input type="date" class="form-control form-control-sm" v-model="fecha" @change="obtenerRegistros()" style="width: 150px; font-weight: bold;">
                </template>
                <template v-else>
                    <div class="font-weight-bold" style="font-size: 1.1em; color: #2c3e50;">{{ fecha }}</div>
                </template>
            </li>
          </ol>
          <table class="table w-100 border border-collapse border-gray-300 shadow-sm" style="background: white; table-layout: fixed;">
            <thead>
              <tr class="bg-dark text-white text-center" style="font-size: 11px;">
                <th style="width: 40px; padding: 5px;">#</th>
                <th style="width: 85px; padding: 5px;">Fecha</th>
                <th style="width: 70px; padding: 5px;">Cant.</th>
                <th style="width: 25%; padding: 5px;">Actividad / Código</th>
                <th style="width: 70px; padding: 5px;">OT</th>
                <th style="width: 18%; padding: 5px;">Cliente / Producto</th>
                <th style="width: 100px; padding: 5px;">H. Inicio</th>
                <th style="width: 100px; padding: 5px;">H. Fin</th>
                <th style="width: 50px; padding: 5px;">Min.</th>
                <th style="padding: 5px;">Observaciones</th>
              </tr>
            </thead>
            <tbody>
              
              <tr v-for="(registro,index) in registros" :key="'reg-'+index" style="font-size: 12px;">
                <td class="border text-center" style="padding: 3px;" >
                  <button class="btn btn-sm btn-link text-danger p-0" @click="borrarRegistro(registro,index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
                <td class="border text-center font-weight-bold" style="padding: 3px; font-size: 10px; color: #7f8c8d">
                  {{ registro.fecha }}
                </td>
                <td class="border" style="padding: 3px;">
                  <input type="number" class="form-control form-control-sm text-center px-1" v-model="registro.cantidad" @change="agregarRegistro('b')" style="font-size: 11px; height: 26px;">
                </td>
                <td class="border" style="padding: 3px; line-height: 1.1;">
                  <div class="font-weight-bold" style="color: #2c3e50">{{ registro.actividad }}</div>
                  <small class="text-muted" style="font-size: 9px">{{ registro.codigo }}</small>
                </td>
                <td class="border" style="padding: 3px;">
                  <input type="text" class="form-control form-control-sm text-center px-1" v-model="registro.orden_trabajo_id" @change="editar(registro)" style="font-size: 11px; height: 26px;">
                </td>
                <td class="border" style="padding: 3px; line-height: 1.1;">
                  <div class="text-truncate" style="max-width: 150px; font-weight: 500;">{{ registro.orden_trabajo ? registro.orden_trabajo.cliente.razonsocial : '—' }}</div>
                  <div class="text-truncate text-muted" style="max-width: 150px; font-size: 10px;">{{ registro.orden_trabajo ? registro.orden_trabajo.articulo.nombre : '—' }}</div>
                </td>
                <td class="border text-center" style="padding: 3px;">
                  <input type="time" class="form-control form-control-sm px-1" v-model="registro.hora_inicio" @change="calcularDiferencia(registro),agregarRegistro('b')" style="font-size: 11px; height: 26px; min-width: 100px;">
                </td>
                <td class="border text-center" style="padding: 3px;">
                  <input type="time" class="form-control form-control-sm px-1" v-model="registro.hora_fin" @change="calcularDiferencia(registro),agregarRegistro('b')" style="font-size: 11px; height: 26px; min-width: 100px;">
                </td>
                <td class="border text-center font-weight-bold" style="padding: 3px; color: #e67e22;">
                  {{ registro.minutos }}
                </td>
                <td class="border" style="padding: 3px;">
                  <input type="text" class="form-control form-control-sm px-1" v-model="registro.observaciones" @change="agregarRegistro('b')" style="font-size: 11px; height: 26px;">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
            

<script>
import axios from 'axios';
var meses=['01','02','03','04','05','06','07','08','09','10','11','12']

export default {
  props:['empleado', 'registros','codigos','user'],
  data() {
    return {
      fecha: new Date().toLocaleDateString('en-CA'), // YYYY-MM-DD local
      loading: true,
      codigo:'',
      actividad:'',
      orden_trabajo_id:null,
      orden:{},
      orden_id:'',
      elemento:'',
      hora_inicio:null,
      hora_fin:null,
      minutos:0,
      cantidad:0,
      unidad:'',
      observaciones:'',
      selectedCategory: null,
      showManualSearch: false,
      searchText: '',
      arrayCategorias: [],
      configModal: 0,
      nuevaCatNombre: '',
      nuevaCatIcono: 'fa-tag',
      nuevaCatColor: '#3498db',
      nuevoCodID: '',
      nuevoCodNombre: '',
      commonIcons: [
        'fa-tag', 'fa-print', 'fa-archive', 'fa-wrench', 'fa-scissors', 
        'fa-layer-group', 'fa-check-double', 'fa-truck', 'fa-cogs', 
        'fa-industry', 'fa-pallet', 'fa-box', 'fa-recycle', 
        'fa-paint-brush', 'fa-drafting-compass', 'fa-cog', 'fa-tools',
        'fa-clipboard-check', 'fa-barcode', 'fa-cube', 'fa-cubes',
        'fa-anchor', 'fa-balance-scale', 'fa-beer', 'fa-bell', 'fa-bicycle', 
        'fa-book', 'fa-briefcase', 'fa-bug', 'fa-bullhorn', 'fa-calculator', 
        'fa-calendar', 'fa-camera', 'fa-car', 'fa-chart-area', 'fa-chart-bar', 
        'fa-chart-line', 'fa-chart-pie', 'fa-check', 'fa-chevron-up', 'fa-clock', 
        'fa-cloud', 'fa-code', 'fa-coffee', 'fa-comment', 'fa-compass', 
        'fa-credit-card', 'fa-cut', 'fa-database', 'fa-desktop', 'fa-download', 
        'fa-edit', 'fa-envelope', 'fa-exclamation-triangle', 'fa-eye', 'fa-file', 
        'fa-filter', 'fa-flag', 'fa-folder', 'fa-gift', 'fa-globe', 
        'fa-graduation-cap', 'fa-heart', 'fa-home', 'fa-image', 'fa-inbox', 
        'fa-info-circle', 'fa-key', 'fa-laptop', 'fa-leaf', 'fa-lightbulb-o', 
        'fa-list', 'fa-lock', 'fa-magic', 'fa-map', 'fa-microphone', 
        'fa-music', 'fa-newspaper-o', 'fa-paper-plane', 'fa-paperclip', 'fa-pencil', 
        'fa-phone', 'fa-plane', 'fa-plus', 'fa-power-off', 'fa-puzzle-piece', 
        'fa-question-circle', 'fa-quote-left', 'fa-redo', 'fa-reply', 'fa-rocket', 
        'fa-save', 'fa-search', 'fa-server', 'fa-share-alt', 'fa-shopping-cart', 
        'fa-smile-o', 'fa-star', 'fa-sticky-note-o', 'fa-suitcase', 'fa-tablet', 
        'fa-tasks', 'fa-terminal', 'fa-thumbs-up', 'fa-ticket', 'fa-trash', 
        'fa-trophy', 'fa-tv', 'fa-umbrella', 'fa-university', 'fa-unlock', 
        'fa-user', 'fa-users', 'fa-video-camera', 'fa-volume-up', 'fa-wifi', 
        'fa-flask', 'fa-stethoscope', 'fa-medkit', 'fa-ambulance', 'fa-h-square', 
        'fa-user-md', 'fa-wheelchair', 'fa-heartbeat', 'fa-plus-square', 
        'fa-circle-o', 'fa-dot-circle-o', 'fa-check-square-o', 'fa-square-o', 
        'fa-envelope-open', 'fa-handshake-o', 'fa-vcard', 'fa-id-card', 'fa-address-book', 
        'fa-window-maximize', 'fa-window-minimize', 'fa-window-restore', 'fa-times-circle', 
        'fa-times-rectangle', 'fa-thermometer-half', 'fa-shower', 'fa-bath', 'fa-podcast', 
        'fa-window-close', 'fa-snowflake-o', 'fa-superpowers', 'fa-wpexplorer', 'fa-meetup', 
        'fa-free-code-camp', 'fa-grav', 'fa-quora', 'fa-bandcamp', 'fa-eercast'
      ]
    };
  },

  computed: {
    filteredCodigos() {
      if (!this.selectedCategory) return [];
      if (this.selectedCategory.detalle === 'Sin Clasificar') return this.unassignedCodigos;
      return this.codigos.filter(c => c.categoria === this.selectedCategory.detalle);
    },
    unassignedCodigos() {
      return this.codigos.filter(c => !c.categoria || c.categoria === '');
    },
    searchedCodigos() {
      if (this.searchText.length < 2) return [];
      const query = this.searchText.toLowerCase();
      return this.codigos.filter(c => c.valor.toLowerCase().includes(query) || (c.detalle && c.detalle.toLowerCase().includes(query)));
    }
  },

  methods: {
    selectCategoryAction(cat) {
      this.selectedCategory = cat;
      this.showManualSearch = false;
    },
    abrirConfigModal() {
        this.configModal = 1;
        this.listarCategorias();
    },
    async listarCategorias() {
        try {
            const res = await axios.get('/ajustes/listar?tipo=config_cat_produccion');
            this.arrayCategorias = res.data;
        } catch (e) { console.error(e); }
    },
    async guardarCategoria() {
        if (!this.nuevaCatNombre) return;
        try {
            await axios.post('/ajustes/registrar', {
                tipo: 'config_cat_produccion',
                detalle: this.nuevaCatNombre,
                valor: this.nuevaCatIcono,
                categoria: this.nuevaCatColor // Usamos el campo categoria para el color de la propia cat
            });
            this.nuevaCatNombre = '';
            this.listarCategorias();
        } catch (e) { console.error(e); }
    },
    async eliminarCategoria(id) {
        if (!confirm('¿Desea eliminar esta categoría?')) return;
        try {
            await axios.delete('/ajustes/eliminar?id='+id);
            this.listarCategorias();
        } catch (e) { console.error(e); }
    },
    async guardarActividadMaestra() {
        if (!this.nuevoCodID || !this.nuevoCodNombre) return;
        try {
            await axios.post('/ajustes/registrar', {
                tipo: 'actividad',
                detalle: this.nuevoCodID,
                valor: this.nuevoCodNombre
            });
            this.nuevoCodID = '';
            this.nuevoCodNombre = '';
            this.$emit('actualizarCodigos');
            Swal.fire('Creado', 'Actividad maestra creada con éxito', 'success');
        } catch (e) { console.error(e); }
    },
    async actualizarAsignacion(cod) {
        try {
            await axios.put('/ajustes/actualizar', {
                id: cod.id,
                tipo: 'actividad',
                detalle: cod.detalle,
                valor: cod.valor,
                categoria: cod.categoria
            });
        } catch (e) { console.error(e); }
    },
    quickRegister(cod) {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      this.hora_inicio = `${hours}:${minutes}`;
      
      this.codigo = cod.detalle;
      this.actividad = cod.valor;
      
      this.agregarRegistro('a');
      
      this.selectedCategory = null;
      this.showManualSearch = false;
      this.searchText = '';
    },
    selectActividad(cod,set,registro){
      console.log(cod)
      if(set=='a'){
        this.codigo=cod.detalle
        this.actividad=cod.valor
      }else{
        registro.codigo=cod.detalle
        registro.actividad=cod.valor
      }
    },
    llenarActividad(cod,a){
      let me=this
      const encontrado = this.codigos.find(c => c.detalle === me.codigo)
      console.log(this.codigos)
      this.actividad = encontrado ? encontrado.valor : ''
        
    },
    EditarActividad(registro){
        const encontrado = this.codigos.find(c => c.detalle === registro.codigo)
        registro.actividad = encontrado ? encontrado.valor : ''
    },
    async editar(registro){
      let me=this
      try {
        const response = await axios.get('/orden/buscarorden?id='+registro.orden_trabajo_id);
        registro.orden_trabajo = response.data;
        
        // Priorizar la descripción detallada en el papel (donde se guarda Tamaños + Sobrante)
        if (registro.orden_trabajo.papel && registro.orden_trabajo.papel.length > 0) {
            const papel = registro.orden_trabajo.papel[0];
            if (papel.descripcion) {
                registro.cantidad = papel.descripcion;
            }
        } else if (registro.orden_trabajo && registro.orden_trabajo.tamano) {
            registro.cantidad = registro.orden_trabajo.tamano;
        }

        // GUARDADO EXPLÍCITO para asegurar que la cantidad se persista
        this.agregarRegistro('b');

      } catch (error) {
        console.error('Error al cargar registros:', error);
      } finally {
        this.loading = false;
      }
      
    },
    calcularDiferencia(registro) {
      console.log(registro)
      
     
      if(typeof registro == 'undefined'){
        if (!this.hora_inicio || !this.hora_fin) return;
        var [h1, m1] = this.hora_inicio.split(':').map(Number);
        var [h2, m2] = this.hora_fin.split(':').map(Number);
       
      }else{
        if (!registro.hora_inicio || !registro.hora_fin) return;
        var [h1, m1] = registro.hora_inicio.split(':').map(Number);
        var [h2, m2] = registro.hora_fin.split(':').map(Number);
        
      }

      const inicio = new Date(0, 0, 0, h1, m1);
      const fin = new Date(0, 0, 0, h2, m2);

      let minutosDiferencia = (fin - inicio) / (1000 * 60);

      if (minutosDiferencia < 0) {
        minutosDiferencia += 24 * 60; // Caso: pasó la medianoche
      }

      const horas = Math.floor(minutosDiferencia / 60);
      const minutos = Math.floor(minutosDiferencia % 60);
      console.log(minutosDiferencia)
      if(isNaN(minutosDiferencia)){
        this.minutos=0
        registro.minutos=0
      }else{

        this.minutos = minutosDiferencia;
        registro.minutos=minutosDiferencia
      }
    },
   
   borrarRegistro(registro,index){
    var id=registro.id
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
        })

        swalWithBootstrapButtons.fire({
        title: 'Esta seguro de borrar este registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
        }).then((result) => {
        if (result.value) {
            let me = this;
            var userj=me.user
            var url= '/registros/borrar?id='+ id+'&user_id='+me.user.id;
            axios.delete(url,{'_method': 'DELETE'})
            .then(function (response) {
              // me.registros.splice(index, 1)
              me.$emit('actualizarRegistros',me.empleado)
                Swal.fire(
                'Eliminado!',
                'El registro ha sido eliminado con éxito.',
                'success'
                )
                // emit('obtenerRegistros')
            }).catch(function (error) {
                console.log(error);
            });
            
            
        } else if (
            // Read more about handling dismissals
            result.dismiss === Swal.DismissReason.cancel
        ) {
            
        }
        }) 
        
    },
    agregarRegistro(set){
        let me=this
        if(set=='a'){
          me.registros.push({
              id:0,
              empleado_id:me.empleado.id,
              orden_trabajo_id:me.orden_trabajo_id,
              actividad:me.actividad,
              elemento:me.elemento,
              fecha:me.fecha,
              hora_inicio:me.hora_inicio,
              hora_fin:null,
              minutos:0,
              unidad:me.unidad,
              cantidad:me.cantidad,
              observaciones:me.observaciones
          })
         
          me.actividad = '';
          me.elemento = '';
          me.hora_inicio = '';
          me.hora_fin = '';
          me.tiempo_total = 0;
          me.cantidad = 0;
          me.observaciones = '';
          me.orden_trabajo_id = '';
          me.orden = {};
          me.codigo = '';
        }
        console.log(me.registros)
        var userj=me.user
        const regis = new FormData()
        regis.set('registros',JSON.stringify(me.registros))
        axios.post('/registros/registrar',regis)
        .then(function (response) {
            console.log(response)
             me.$emit('actualizarRegistros',me.empleado)
        }).catch(function (error) {
            console.log(error);
        });
    },
      registrarOrden(){
                let me = this;
               
            },
    async buscarclipro() {
        let me=this
        try {
          const response = await axios.get('/orden/buscarorden?id='+me.orden_trabajo_id);
          me.orden = response.data;
          if (me.orden && me.orden.tamano) {
            me.cantidad = me.orden.tamano;
          }
        } catch (error) {
          console.error('Error al cargar registros:', error);
        } finally {
          this.loading = false;
        }
    },
    async obtenerCodigos() {
      try {
        const response = await axios.get('/ajustes/codigos');
        this.codigos = response.data;
      } catch (error) {
        console.error('Error al cargar registros:', error);
      } finally {
        this.loading = false;
      }
    },
    getCategoryColor(catName) {
        const cat = this.arrayCategorias.find(c => c.detalle === catName);
        return cat ? cat.categoria : '#ccc';
    },
    obtenerRegistros() {
      let me=this
      axios.get('/registros/registroEmpleado?ide='+me.empleado.id + '&fecha=' + me.fecha)
      .then(function (response) {
        me.registros = response.data;
      }).catch(function (error) {
          console.log(error);
      });
     
    }
  },
  created(){
    this.listarCategorias();
  },
   mounted() {
   }
};
</script>

<style scoped>
.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.btn-category {
  display: flex;
  align-items: center;
  border: none;
  border-radius: 12px;
  color: white;
  padding: 0;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  cursor: pointer;
  height: 60px;
}

.btn-category:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 15px rgba(0,0,0,0.2);
  filter: brightness(1.1);
}

.btn-category .icon-part {
  background: rgba(0,0,0,0.15);
  height: 100%;
  width: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.btn-category .text-part {
  padding: 10px 15px;
  font-weight: 700;
  flex-grow: 1;
  text-align: left;
  font-size: 0.95rem;
  letter-spacing: 0.5px;
}

.sub-activities-container {
  background: #fdfdfd;
  border-radius: 15px;
  padding: 20px;
  border: 1px solid #edf2f7;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.sub-activities-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.btn-sub {
  background: white;
  border: 2px solid;
  border-radius: 10px;
  padding: 8px 18px;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-sub:hover {
  background: rgba(0,0,0,0.03);
  transform: scale(1.05);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.btn-sub-search {
  border-color: #546e7a;
  color: #546e7a;
}

.manual-search {
  background: #f8fafc;
  padding: 15px;
  border-radius: 12px;
  border: 1px dashed #cbd5e1;
}

.search-results {
  max-height: 200px;
  overflow-y: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 10px;
}

table {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  font-size: 13px;
}

.thead-dark th {
  background-color: #2d3748;
  color: white;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
}

.titulo-r {
  font-weight: 700;
  color: #4a5568;
}

.header-registro {
  display: flex;
  flex-direction: row;
  list-style: none;
  padding: 10px;
  background: #f7fafc;
  border-radius: 8px;
  margin-bottom: 15px;
}

.dato-registro {
  margin-right: 30px;
}

.btn-config {
    background-color: #2c3e50 !important;
}

.btn-unassigned {
    background-color: #7f8c8d !important;
}

.mostrar {
    display: list-item !important;
    opacity: 1 !important;
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5) !important;
    z-index: 9999;
    overflow-x: hidden;
    overflow-y: auto;
}

.modal-dialog {
    margin: 1.75rem auto;
    z-index: 10000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.titulo-r {
  font-weight: bold;
  color: #2d3748;
}
</style>
