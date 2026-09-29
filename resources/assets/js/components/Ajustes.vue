<template>
  <main class="main">
    <!-- Breadcrumb -->
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
      <li class="breadcrumb-item active">Tabla de Ajustes y Medidas de Pliegos con Máquinas</li>
    </ol>

    <div class="container-fluid">
      <!-- Configuración Global de Impresión de Hoja de Ruta -->
      <div class="card mb-4 border-primary shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
          <span><i class="fa fa-print"></i> <strong>Configuración Global de Papel para Hoja de Ruta</strong></span>
          <span class="badge badge-light text-dark font-weight-normal">Aplica automáticamente para todas las órdenes</span>
        </div>
        <div class="card-body bg-light">
          <div class="row align-items-end">
            <div class="col-md-4 mb-2">
              <label class="font-weight-bold">Tamaño de Papel Predeterminado:</label>
              <select class="form-control" v-model="configHojaRuta.formato_papel">
                <option value="letter">📄 Carta (Letter 21.6 x 27.9 cm)</option>
                <option value="legal">📄 Oficio (Legal 21.6 x 35.6 cm)</option>
                <option value="a4">📄 A4 (21.0 x 29.7 cm)</option>
                <option value="a5">📄 A5 (14.8 x 21.0 cm)</option>
                <option value="custom">📐 Medida Personalizada (Cualquier tamaño)</option>
              </select>
            </div>

            <div class="col-md-4 mb-2" v-if="configHojaRuta.formato_papel === 'custom'">
              <label class="font-weight-bold">Dimensiones Personalizadas (cm):</label>
              <div class="d-flex align-items-center">
                <input type="number" step="0.1" class="form-control mr-1" v-model="configHojaRuta.ancho_papel_cm" placeholder="Ancho (cm)">
                <span class="font-weight-bold mr-1">x</span>
                <input type="number" step="0.1" class="form-control" v-model="configHojaRuta.alto_papel_cm" placeholder="Alto (cm)">
              </div>
            </div>

            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">Orientación de Impresión:</label>
              <select class="form-control" v-model="configHojaRuta.orientacion_papel">
                <option value="portrait">📱 Vertical (Portrait)</option>
                <option value="landscape">🖥️ Horizontal (Landscape)</option>
              </select>
            </div>

            <div class="col-md-1 mb-2 text-right">
              <button type="button" class="btn btn-success font-weight-bold w-100" @click="guardarConfigHojaRuta()">
                <i class="fa fa-save"></i> Guardar
              </button>
            </div>
          </div>

          <!-- Selección Configurable de Procesos -->
          <div class="row border-top pt-3 mt-2" v-if="todos_procesos.length > 0">
            <div class="col-12 mb-2">
              <label class="font-weight-bold">📌 Seleccionar Procesos Incluidos en la Hoja de Ruta:</label>
              <small class="text-muted d-block">
                (Nota: <strong>Control de Calidad, Conteo, Empaque y Para Entregar</strong> se incluyen siempre obligatoriamente. Si selecciona <strong>Terminado</strong>, se incluirá automáticamente <strong>Espera Terminado</strong>).
              </small>
            </div>
            <div class="col-12">
              <div class="d-flex flex-wrap align-items-center">
                <div 
                  v-for="p in todos_procesos" 
                  :key="p.id" 
                  class="custom-control custom-checkbox mr-3 mb-2 p-2 border rounded"
                  :class="{'bg-light': isProcesoMandatory(p.proceso)}"
                >
                  <input 
                    type="checkbox" 
                    class="custom-control-input" 
                    :id="'proc_chk_' + p.id"
                    :checked="isProcesoSelected(p.proceso)"
                    :disabled="isProcesoMandatory(p.proceso)"
                    @change="toggleProceso(p.proceso)"
                  >
                  <label class="custom-control-label font-weight-bold" :for="'proc_chk_' + p.id" style="cursor: pointer;">
                    {{ p.proceso }}
                    <span v-if="isProcesoMandatory(p.proceso)" class="badge badge-info ml-1">Fijo</span>
                    <span v-else-if="p.proceso.toLowerCase() === 'espera terminado' && isProcesoSelected('Terminado')" class="badge badge-warning ml-1">Auto</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tarjetas informativas de Medidas de Pliego y Máquinas -->
      <div class="row mb-3">
        <div class="col-sm-6 col-md-4">
          <div class="card text-white bg-primary">
            <div class="card-body pb-2">
              <div class="h4 mb-0">{{ countPliego70x100 }}</div>
              <div>Pliego Base 70x100 cm</div>
              <small class="text-white-50">Cortes estándar configurados en Ajustes</small>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-4">
          <div class="card text-white bg-success">
            <div class="card-body pb-2">
              <div class="h4 mb-0">{{ countGTO }}</div>
              <div>Aptos Máquina GTO</div>
              <small class="text-white-50">Rango (22x14 cm a 50x35 cm)</small>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-4">
          <div class="card text-white bg-dark">
            <div class="card-body pb-2">
              <div class="h4 mb-0">{{ countSORM }}</div>
              <div>Aptos Máquina SORM</div>
              <small class="text-white-50">Rango (35x27.5 cm a 70x52 cm)</small>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span>
            <i class="fa fa-sliders"></i> <strong>Tabla de Ajustes y Compatibilidad de Máquina</strong>
          </span>
          <button type="button" @click="abrirModal('registrar')" class="btn btn-success btn-sm">
            <i class="icon-plus"></i>&nbsp;Nuevo Ajuste
          </button>
        </div>

        <div class="card-body">
          <!-- Filtros de búsqueda -->
          <div class="form-group row">
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">Tipo de Ajuste:</label>
              <select class="form-control" v-model="tipoFiltro" @change="filtrar()">
                <option value="medidas_pliego">Medidas de Pliego (70x100 cm)</option>
                <option value="todos">-- Todos los tipos --</option>
                <option value="tienda">Tienda / Precios</option>
                <option value="config_cat_produccion">Categorías Producción</option>
                <option value="actividad">Actividades</option>
                <option value="consecutivo">Consecutivos</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">Pliego / Categoría:</label>
              <select class="form-control" v-model="categoriaFiltro" @change="filtrar()">
                <option value="todas">-- Todas las categorías --</option>
                <option value="70x100">Pliego 70x100 cm</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">Máquina Impre.:</label>
              <select class="form-control" v-model="maquinaFiltro" @change="filtrar()">
                <option value="todas">-- Todas las Máquinas --</option>
                <option value="gto">GTO (22x14 a 50x35 cm)</option>
                <option value="sorm">SORM (35x27.5 a 70x52 cm)</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">Búsqueda:</label>
              <div class="input-group">
                <input type="text" v-model="buscar" @keyup.enter="listarAjustes()" class="form-control" placeholder="Buscar corte, valor...">
                <div class="input-group-append">
                  <button type="button" @click="listarAjustes()" class="btn btn-primary">
                    <i class="fa fa-search"></i>
                  </button>
                  <button type="button" class="btn btn-secondary" @click="limpiarFiltros()" title="Limpiar Filtros">
                    <i class="fa fa-refresh"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla de Ajustes -->
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm align-middle">
              <thead class="thead-dark">
                <tr>
                  <th style="width: 60px;">ID</th>
                  <th>Tipo</th>
                  <th>Detalle / Fracción</th>
                  <th>Dimensión (cm)</th>
                  <th>Pliego Base</th>
                  <th>Compatibilidad Máquina</th>
                  <th style="width: 100px;" class="text-center">Opciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ajuste in ajustesFiltrados" :key="ajuste.id">
                  <td v-text="ajuste.id"></td>
                  <td>
                    <span v-if="ajuste.tipo === 'medidas_pliego'" class="badge badge-success font-size-12">Medida de Pliego</span>
                    <span v-else-if="ajuste.tipo === 'tienda'" class="badge badge-info">Tienda</span>
                    <span v-else-if="ajuste.tipo === 'config_cat_produccion'" class="badge badge-warning">Categoría Prod.</span>
                    <span v-else class="badge badge-secondary" v-text="ajuste.tipo"></span>
                  </td>
                  <td>
                    <strong>{{ ajuste.detalle }}</strong>
                  </td>
                  <td>
                    <code style="font-size: 1.1em; color: #20a8d8;">{{ ajuste.valor }}</code>
                  </td>
                  <td>
                    <span v-if="ajuste.categoria === '70x100'" class="badge badge-primary">70x100 cm</span>
                    <span v-else-if="ajuste.categoria === '60x90'" class="badge badge-info">60x90 cm</span>
                    <span v-else v-text="ajuste.categoria || 'N/A'"></span>
                  </td>
                  <td>
                    <template v-if="ajuste.tipo === 'medidas_pliego'">
                      <span v-if="fitsInGTO(ajuste.valor)" class="badge badge-success mr-1" style="font-size: 0.85rem;" title="Mín 22x14 cm | Máx 50x35 cm">
                        <i class="fa fa-check-circle"></i> GTO (22x14 - 50x35)
                      </span>
                      <span v-if="fitsInSORM(ajuste.valor)" class="badge badge-primary mr-1" style="font-size: 0.85rem;" title="Mín 35x27.5 cm | Máx 70x52 cm">
                        <i class="fa fa-check-circle"></i> SORM (35x27.5 - 70x52)
                      </span>
                      <span v-if="!fitsInGTO(ajuste.valor) && !fitsInSORM(ajuste.valor)" class="badge badge-secondary" style="font-size: 0.85rem;">
                        <i class="fa fa-ban"></i> Fuera de Rango Máquina
                      </span>
                    </template>
                    <template v-else>
                      <span class="text-muted small">N/A</span>
                    </template>
                  </td>
                  <td class="text-center">
                    <button type="button" @click="abrirModal('actualizar', ajuste)" class="btn btn-warning btn-sm" title="Editar">
                      <i class="icon-pencil"></i>
                    </button>
                    &nbsp;
                    <button type="button" class="btn btn-danger btn-sm" @click="eliminarAjuste(ajuste.id)" title="Eliminar">
                      <i class="icon-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="ajustesFiltrados.length === 0">
                  <td colspan="7" class="text-center text-muted py-4">
                    No se encontraron registros de ajustes con los filtros seleccionados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Agregar / Actualizar -->
    <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-hidden="true" style="display: none;">
      <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" v-text="tituloModal"></h4>
            <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="post" class="form-horizontal" @submit.prevent>
              <div class="form-group row">
                <label class="col-md-3 form-control-label font-weight-bold">Tipo (*)</label>
                <div class="col-md-9">
                  <select v-model="tipo" class="form-control">
                    <option value="medidas_pliego">medidas_pliego (Medida de pliego)</option>
                    <option value="tienda">tienda (Ajustes de tienda)</option>
                    <option value="config_cat_produccion">config_cat_produccion (Categorías producción)</option>
                    <option value="actividad">actividad (Actividades producción)</option>
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-md-3 form-control-label font-weight-bold">Pliego / Categoría</label>
                <div class="col-md-9">
                  <select v-model="categoria" class="form-control">
                    <option value="70x100">70x100 (Pliego 70x100 cm)</option>
                    <option value="60x90">60x90 (Pliego 60x90 cm)</option>
                    <option value="">-- Sin Categoría --</option>
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-md-3 form-control-label font-weight-bold">Detalle / Nombre (*)</label>
                <div class="col-md-9">
                  <input type="text" v-model="detalle" class="form-control" placeholder="Ej. 1/4 Cuarto Pliego (35x50 cm)">
                </div>
              </div>

              <div class="form-group row">
                <label class="col-md-3 form-control-label font-weight-bold">Valor / Dimensión (*)</label>
                <div class="col-md-9">
                  <input type="text" v-model="valor" class="form-control" placeholder="Ej. 35x50">
                  <small class="form-text text-muted">Para medidas de pliego use el formato ANCHOxALTO en cm (ej. 50x70, 35x50).</small>
                  
                  <!-- Preview de Compatibilidad de Máquina -->
                  <div class="mt-2" v-if="tipo === 'medidas_pliego' && valor">
                    <span v-if="fitsInGTO(valor)" class="badge badge-success mr-1">Apto GTO (22x14 a 50x35 cm)</span>
                    <span v-if="fitsInSORM(valor)" class="badge badge-primary mr-1">Apto SORM (35x27.5 a 70x52 cm)</span>
                    <span v-if="!fitsInGTO(valor) && !fitsInSORM(valor)" class="badge badge-warning">Fuera de límites de máquinas conocidas</span>
                  </div>
                </div>
              </div>

              <div v-show="errorAjuste" class="form-group row div-error">
                <div class="text-center text-error col-md-12">
                  <div v-for="error in errorMostrarMsj" :key="error" v-text="error"></div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
            <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarAjuste()">Guardar</button>
            <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarAjuste()">Actualizar</button>
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
      ajuste_id: 0,
      tipo: 'medidas_pliego',
      detalle: '',
      valor: '',
      categoria: '70x100',
      arrayAjustes: [],
      tipoFiltro: 'medidas_pliego',
      categoriaFiltro: 'todas',
      maquinaFiltro: 'todas',
      buscar: '',
      modal: 0,
      tituloModal: '',
      tipoAccion: 0,
      errorAjuste: 0,
      errorMostrarMsj: [],
      configHojaRuta: {
        formato_papel: 'letter',
        orientacion_papel: 'portrait',
        ancho_papel_cm: '21.5',
        alto_papel_cm: '33',
        procesos_seleccionados: []
      },
      todos_procesos: []
    }
  },
  computed: {
    countPliego70x100() {
      return this.arrayAjustes.filter(a => a.tipo === 'medidas_pliego' && a.categoria === '70x100').length;
    },
    countPliego60x90() {
      return this.arrayAjustes.filter(a => a.tipo === 'medidas_pliego' && a.categoria === '60x90').length;
    },
    countGTO() {
      return this.arrayAjustes.filter(a => a.tipo === 'medidas_pliego' && this.fitsInGTO(a.valor)).length;
    },
    countSORM() {
      return this.arrayAjustes.filter(a => a.tipo === 'medidas_pliego' && this.fitsInSORM(a.valor)).length;
    },
    ajustesFiltrados() {
      if (this.maquinaFiltro === 'todas') {
        return this.arrayAjustes;
      }
      if (this.maquinaFiltro === 'gto') {
        return this.arrayAjustes.filter(a => a.tipo === 'medidas_pliego' && this.fitsInGTO(a.valor));
      }
      if (this.maquinaFiltro === 'sorm') {
        return this.arrayAjustes.filter(a => a.tipo === 'medidas_pliego' && this.fitsInSORM(a.valor));
      }
      return this.arrayAjustes;
    }
  },
  methods: {
    fitsInMachine(valor, minW, minH, maxW, maxH) {
      if (!valor || typeof valor !== 'string' || !valor.includes('x')) return false;
      const parts = valor.split('x').map(Number);
      if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return false;
      const cutW = parts[0];
      const cutH = parts[1];

      const normal = (cutW >= minW && cutH >= minH && cutW <= maxW && cutH <= maxH) ||
                     (cutW >= minH && cutH >= minW && cutW <= maxH && cutH <= maxW);

      const rotated = (cutH >= minW && cutW >= minH && cutH <= maxW && cutH <= maxH) ||
                      (cutH >= minH && cutW >= minW && cutH <= maxH && cutW <= maxW);

      return normal || rotated;
    },
    fitsInGTO(valor) {
      // GTO: Mínimo 22x14 cm | Máximo 50x35 cm
      return this.fitsInMachine(valor, 22, 14, 50, 35);
    },
    fitsInSORM(valor) {
      // SORM: Mínimo 35x27.5 cm | Máximo 70x52 cm
      return this.fitsInMachine(valor, 35, 27.5, 70, 52);
    },
    filtrar() {
      this.listarAjustes();
    },
    listarAjustes() {
      let me = this;
      let url = '/ajustes/listar?tipo=' + me.tipoFiltro + '&categoria=' + me.categoriaFiltro + '&buscar=' + encodeURIComponent(me.buscar);
      axios.get(url).then(function (response) {
        me.arrayAjustes = response.data;
      }).catch(function (error) {
        console.log(error);
      });
    },
    limpiarFiltros() {
      this.tipoFiltro = 'medidas_pliego';
      this.categoriaFiltro = 'todas';
      this.maquinaFiltro = 'todas';
      this.buscar = '';
      this.listarAjustes();
    },
    registrarAjuste() {
      if (this.validarAjuste()) {
        return;
      }
      let me = this;
      axios.post('/ajustes/registrar', {
        'tipo': this.tipo,
        'detalle': this.detalle,
        'valor': this.valor,
        'categoria': this.categoria
      }).then(function (response) {
        me.cerrarModal();
        me.listarAjustes();
        Swal.fire('¡Registrado!', 'El ajuste ha sido guardado exitosamente.', 'success');
      }).catch(function (error) {
        console.log(error);
      });
    },
    actualizarAjuste() {
      if (this.validarAjuste()) {
        return;
      }
      let me = this;
      axios.put('/ajustes/actualizar', {
        'id': this.ajuste_id,
        'tipo': this.tipo,
        'detalle': this.detalle,
        'valor': this.valor,
        'categoria': this.categoria
      }).then(function (response) {
        me.cerrarModal();
        me.listarAjustes();
        Swal.fire('¡Actualizado!', 'El ajuste ha sido actualizado exitosamente.', 'success');
      }).catch(function (error) {
        console.log(error);
      });
    },
    eliminarAjuste(id) {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-success mr-2',
          cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
      });

      swalWithBootstrapButtons.fire({
        title: '¿Está seguro de eliminar este ajuste?',
        text: 'Esta acción eliminará la medida/configuración seleccionada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.value) {
          let me = this;
          axios.delete('/ajustes/eliminar?id=' + id).then(function (response) {
            me.listarAjustes();
            Swal.fire('¡Eliminado!', 'El registro ha sido eliminado con éxito.', 'success');
          }).catch(function (error) {
            console.log(error);
          });
        }
      });
    },
    validarAjuste() {
      this.errorAjuste = 0;
      this.errorMostrarMsj = [];

      if (!this.tipo) this.errorMostrarMsj.push("El tipo de ajuste no puede estar vacío.");
      if (!this.detalle) this.errorMostrarMsj.push("El detalle/nombre del ajuste no puede estar vacío.");
      if (!this.valor) this.errorMostrarMsj.push("El valor no puede estar vacío.");

      if (this.errorMostrarMsj.length) this.errorAjuste = 1;

      return this.errorAjuste;
    },
    cerrarModal() {
      this.modal = 0;
      this.tituloModal = '';
      this.tipo = 'medidas_pliego';
      this.detalle = '';
      this.valor = '';
      this.categoria = '70x100';
      this.errorAjuste = 0;
      this.errorMostrarMsj = [];
    },
    abrirModal(accion, data = []) {
      switch (accion) {
        case 'registrar': {
          this.modal = 1;
          this.tituloModal = 'Registrar Medida / Ajuste';
          this.tipo = 'medidas_pliego';
          this.detalle = '';
          this.valor = '';
          this.categoria = '70x100';
          this.tipoAccion = 1;
          break;
        }
        case 'actualizar': {
          this.modal = 1;
          this.tituloModal = 'Actualizar Medida / Ajuste';
          this.tipoAccion = 2;
          this.ajuste_id = data['id'];
          this.tipo = data['tipo'];
          this.detalle = data['detalle'];
          this.valor = data['valor'];
          this.categoria = data['categoria'] || '';
          break;
        }
      }
    },
    cargarConfigHojaRuta() {
      let me = this;
      axios.get('/orden/hoja-ruta-config').then(response => {
        if (response.data) {
          if (response.data.formato_papel) me.configHojaRuta.formato_papel = response.data.formato_papel;
          if (response.data.orientacion_papel) me.configHojaRuta.orientacion_papel = response.data.orientacion_papel;
          if (response.data.ancho_papel_cm) me.configHojaRuta.ancho_papel_cm = response.data.ancho_papel_cm;
          if (response.data.alto_papel_cm) me.configHojaRuta.alto_papel_cm = response.data.alto_papel_cm;
          if (response.data.todos_procesos) me.todos_procesos = response.data.todos_procesos;
          
          if (response.data.procesos_seleccionados && Array.isArray(response.data.procesos_seleccionados)) {
            me.configHojaRuta.procesos_seleccionados = response.data.procesos_seleccionados;
          } else if (response.data.todos_procesos) {
            me.configHojaRuta.procesos_seleccionados = response.data.todos_procesos
              .map(p => p.proceso)
              .filter(p => !['espera', 'compra papel', 'repujado', 'estampado', 'colaminado', 'espera terminado'].includes(p.toLowerCase()));
          }
          me.enforceProcessRules();
        }
      }).catch(e => {});
    },
    enforceProcessRules() {
      let procs = (this.configHojaRuta.procesos_seleccionados || []).map(p => String(p).trim());
      let procsLower = procs.map(p => p.toLowerCase());
      
      const mandatoryMap = {
        'control de calidad': 'Control de Calidad',
        'conteo': 'Conteo',
        'empaque': 'Empaque',
        'empacado': 'Empaque',
        'para entregar': 'Para entregar',
        'entrega': 'Para entregar'
      };

      Object.keys(mandatoryMap).forEach(key => {
        if (!procsLower.includes(key)) {
          procs.push(mandatoryMap[key]);
          procsLower.push(key);
        }
      });

      const tieneTerminado = procsLower.some(p => p === 'terminado' || (p.includes('terminado') && p !== 'espera terminado'));
      if (tieneTerminado && !procsLower.includes('espera terminado')) {
        procs.push('Espera terminado');
      }

      this.configHojaRuta.procesos_seleccionados = Array.from(new Set(procs));
    },
    toggleProceso(nombreProceso) {
      const lower = nombreProceso.toLowerCase();
      if (['control de calidad', 'conteo', 'empaque', 'empacado', 'para entregar', 'entrega'].includes(lower)) {
        return;
      }
      let list = this.configHojaRuta.procesos_seleccionados || [];
      const idx = list.findIndex(p => p.toLowerCase() === lower);
      if (idx >= 0) {
        list.splice(idx, 1);
      } else {
        list.push(nombreProceso);
      }
      this.configHojaRuta.procesos_seleccionados = list;
      this.enforceProcessRules();
    },
    isProcesoSelected(nombreProceso) {
      const lower = nombreProceso.toLowerCase();
      return (this.configHojaRuta.procesos_seleccionados || []).some(p => p.toLowerCase() === lower);
    },
    isProcesoMandatory(nombreProceso) {
      const lower = nombreProceso.toLowerCase();
      return ['control de calidad', 'conteo', 'empaque', 'empacado', 'para entregar', 'entrega'].includes(lower);
    },
    guardarConfigHojaRuta() {
      let me = this;
      me.enforceProcessRules();
      axios.post('/orden/hoja-ruta-config', me.configHojaRuta).then(response => {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: '¡Configuración Guardada!',
            text: 'Los procesos seleccionados y el formato para la Hoja de Ruta se actualizaron correctamente.',
            icon: 'success',
            confirmButtonText: 'Aceptar'
          });
        } else {
          alert('Configuración guardada exitosamente.');
        }
      }).catch(e => {
        alert('Error al guardar la configuración.');
      });
    }
  },
  mounted() {
    this.listarAjustes();
    this.cargarConfigHojaRuta();
  }
}
</script>

<style scoped>
.modal-content {
  width: 100% !important;
  position: absolute !important;
}
.mostrar {
  display: list-item !important;
  opacity: 1 !important;
  position: absolute !important;
  background-color: #3c29297a !important;
}
.div-error {
  display: flex;
  justify-content: center;
}
.text-error {
  color: red !important;
  font-weight: bold;
}
.font-size-12 {
  font-size: 12px;
}
</style>
