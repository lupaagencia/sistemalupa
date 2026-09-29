<template>
  <div class="selector-color-wrapper">
    <!-- Backdrop Overlay -->
    <div v-if="paleta" class="paleta-backdrop" @click="cerrarPaleta()"></div>

    <!-- Color Grid Modal -->
    <div v-if="paleta" :class="['paleta-modal-container', wi==='100' ? 'cont-paleta' : 'cont-paleta140']">
      <!-- Modal Header -->
      <div class="paleta-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="m-0 font-weight-bold text-primary header-title">
            <i class="fa fa-paint-brush"></i> {{ detalle.titulo || 'Seleccionar Color' }}
          </h5>
          <button @click="cerrarPaleta()" class="btn-close-modal">
            <i class="fa fa-times"></i>
          </button>
        </div>
        
        <!-- Search Bar in Header -->
        <div class="search-box">
          <div class="input-group input-group-sm">
            <div class="input-group-prepend">
              <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
            </div>
            <input v-model="buscarC" @keyup="buscarColor()" type="text" class="form-control border-left-0" placeholder="Buscar color...">
          </div>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="paleta-body">
        <!-- Selected Colors Section -->
        <div v-if="selectedColors.length > 0" class="selected-section">
          <label class="section-label">Colores Seleccionados:</label>
          <div class="selected-colors-list">
            <div v-for="(valor, index) in selectedColors" :key="index" class="selected-color-badge" :style="{ background: valor.hex || '#000' }">
              <span class="pantone-name">{{ valor.pantone || valor.nombre || '' }}</span>
              <button class="btn-remove-color" @click.stop="eliminar(index)">
                <i class="fa fa-times-circle"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Add Custom Color Section -->
        <div class="custom-color-add">
          <label class="section-label">Añadir Pantone Personalizado:</label>
          <div class="d-flex align-items-center">
            <input v-model="nuevoPantone" class="form-control form-control-sm mr-2" placeholder="Nombre Pantone" />
            <div class="color-picker-box mr-2">
              <input type="color" v-model="nuevoHex" class="inner-picker">
            </div>
            <button @click="agregarColor()" class="btn btn-sm btn-primary">
              <i class="fa fa-plus"></i>
            </button>
          </div>
        </div>

        <!-- Color Grid -->
        <div v-if="colors.length > 0" class="color-grid">
          <div
            v-for="(color, idx) in colors"
            :key="idx"
            class="color-item position-relative"
            :title="color.pantone"
            @click="selectColor(color)"
          >
            <!-- Botón para eliminar el color de la paleta -->
            <button 
              class="btn-delete-palette-color" 
              @click.stop="eliminarColorPaleta(idx)"
              title="Eliminar de la paleta"
            >
              <i class="fa fa-trash-o"></i>
            </button>

            <div class="color-swatch-box" :style="{ background: color.hex }"></div>
            <span class="color-tag">{{ color.pantone }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4">
          <i class="fa fa-spinner fa-spin text-muted fa-2x mb-2"></i>
          <p class="text-muted small">Cargando...</p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="paleta-footer">
        <div class="d-flex justify-content-between align-items-center">
          <div class="paleta-tabs">
            <button type="button" class="tab-btn" :class="{active: tipopaleta === 'p'}" @click="abrirPaleta('p')">P</button>
            <button type="button" class="tab-btn" :class="{active: tipopaleta === 'c'}" @click="abrirPaleta('c')">B</button>
            <button type="button" class="tab-btn" :class="{active: tipopaleta === 'u'}" @click="abrirPaleta('u')">M</button>
          </div>
          <button @click="cerrarPaleta()" class="btn btn-sm btn-dark px-4">Listo</button>
        </div>
      </div>
    </div>

    <!-- Toggle Buttons -->
    <div v-else class="toggle-buttons-row">
      <button type="button" class="color-btn pantone" title="Pantone" @click="abrirPaleta('p')">P</button>
      <button type="button" class="color-btn cmyk" title="CMYK" @click="abrirPaleta('c')">B</button>
      <button type="button" class="color-btn ral" title="RAL" @click="abrirPaleta('u')">M</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    detalle: { type: Object, default: () => ({ valor: [] }) },
    wi: { type: String, default: '140' },
    ma: { type: String, default: '105' },
    scroll: { type: Number, default: 0 }
  },
  data() {
    return {
      colors: [],
      selectedColor: null,
      paleta: 0,
      tipopaleta: 'u',
      buscarC: '',
      nuevoPantone: '',
      nuevoHex: '#000000',
    };
  },
  computed: {
    selectedColors() {
      if (!this.detalle) return [];
      let val = this.detalle.valor;
      if (typeof val === 'string') {
        if (val.trim().startsWith('[')) {
          try {
            val = JSON.parse(val);
            this.$set(this.detalle, 'valor', val);
          } catch(e) {
            val = [];
            this.$set(this.detalle, 'valor', val);
          }
        } else {
          val = [];
          this.$set(this.detalle, 'valor', val);
        }
      } else if (!Array.isArray(val)) {
        val = [];
        this.$set(this.detalle, 'valor', val);
      }
      return val;
    }
  },
  mounted() {
    this.cargarColores();
  },
  methods: {
    normalizarDetalleValor() {
      if (!this.detalle) return;
      let val = this.detalle.valor;
      if (typeof val === 'string') {
        if (val.trim().startsWith('[')) {
          try {
            val = JSON.parse(val);
          } catch(e) {
            val = [];
          }
        } else {
          val = [];
        }
        this.$set(this.detalle, 'valor', val);
      } else if (!Array.isArray(val)) {
        this.$set(this.detalle, 'valor', []);
      }
    },
    cargarColores() {
      if (!this.tipopaleta) return;
      fetch("/colores/" + this.tipopaleta)
        .then((response) => {
          if (!response.ok) return [];
          return response.json();
        })
        .then((data) => {
          if (Array.isArray(data) || typeof data === 'object') {
            this.colors = data;
          }
        })
        .catch((error) => {
          console.error("Error al cargar los colores:", error);
        });
    },
    eliminar(index) {
      this.normalizarDetalleValor();
      if (Array.isArray(this.detalle.valor)) {
        this.detalle.valor.splice(index, 1);
      }
    },
    selectColor(color) {
      this.normalizarDetalleValor();
      const exists = this.detalle.valor.some(v => v.pantone === color.pantone);
      if (!exists) {
        this.detalle.valor.push({...color});
      }
    },
    abrirPaleta(t) {
      this.paleta = 1;
      this.tipopaleta = t;
      this.buscarC = '';
      this.cargarColores();
      this.normalizarDetalleValor();
    },
    agregarColor() {
      if (!this.nuevoPantone || !this.nuevoHex) return;

      const nuevo = {
        pantone: this.nuevoPantone,
        hex: this.nuevoHex
      };
      
      this.colors.unshift(nuevo);
      this.guardarColorServidor('agregar', nuevo);
      
      this.nuevoPantone = '';
      this.nuevoHex = '#000000';
    },
    eliminarColorPaleta(idx) {
      if (confirm('¿Deseas eliminar este color de la paleta permanentemente?')) {
        const colorAEliminar = this.colors[idx];
        this.colors.splice(idx, 1);
        if (colorAEliminar) {
          this.guardarColorServidor('eliminar', colorAEliminar);
        }
      }
    },
    guardarColorServidor(accion, colorObj) {
      axios.post('/guardar-colores', { 
        'accion': accion, 
        'color': colorObj, 
        'paleta': this.tipopaleta 
      })
      .then(function (response) {
        console.log("Color procesado correctamente en la paleta");
      }).catch(function (error) {
        console.error("Error al procesar el color en la paleta:", error);
      });
    },
    cerrarPaleta() {
      this.paleta = 0;
    },
    buscarColor() {
      if (!this.buscarC) {
        this.cargarColores();
        return;
      }
      const regex = new RegExp(this.buscarC, "i");
      fetch("/colores/" + this.tipopaleta)
        .then((response) => response.json())
        .then((data) => {
          this.colors = data.filter(item => regex.test(item.pantone));
        })
        .catch((error) => {
          console.error("Error al buscar colores:", error);
        });
    }
  }
};
</script>

<style scoped>
.selector-color-wrapper {
  display: block;
}

.paleta-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9998;
}

.paleta-modal-container {
  position: fixed;
  top: 50%;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  background: #ffffff;
  border: none;
  border-radius: 12px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
  z-index: 9999;
  display: flex !important;
  flex-direction: column !important;
  max-height: 90vh;
  width: 95%;
  max-width: 480px;
  overflow: hidden;
}

.paleta-header {
  padding: 15px 20px;
  background: #fff;
  border-bottom: 1px solid #eee;
  flex-shrink: 0;
}

.header-title {
  font-size: 1.1rem !important;
  display: inline-block;
}

.header-title i {
  margin-right: 8px;
}

.search-box {
  margin-top: 5px;
}

.paleta-body {
  padding: 15px 20px !important;
  overflow-y: auto !important;
  flex-grow: 1 !important;
  display: flex !important;
  flex-direction: column !important;
  text-align: left !important;
}

.section-label {
  display: block !important;
  width: 100% !important;
  font-size: 11px !important;
  font-weight: bold !important;
  color: #888 !important;
  text-transform: uppercase !important;
  margin-bottom: 8px !important;
}

.selected-section {
  margin-bottom: 15px !important;
  display: block !important;
  width: 100% !important;
}

.selected-colors-list {
  display: flex !important;
  flex-wrap: wrap !important;
}

.selected-color-badge {
  padding: 3px 10px;
  border-radius: 20px;
  color: #fff;
  font-size: 11px;
  font-weight: bold;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  margin-right: 5px;
  margin-bottom: 5px;
}

.btn-remove-color {
  background: none;
  border: none;
  color: rgba(255,255,255,0.8);
  margin-left: 6px;
  padding: 0;
  cursor: pointer;
  font-size: 14px;
}

.custom-color-add {
  background: #f8f9fa !important;
  padding: 12px !important;
  border-radius: 8px !important;
  border: 1px solid #eee !important;
  margin-bottom: 15px !important;
  display: block !important;
  width: 100% !important;
}

.color-picker-box {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  overflow: hidden;
  flex-shrink: 0;
  border: 1px solid #ddd;
}

.inner-picker {
  width: 50px;
  height: 50px;
  margin: -10px;
  cursor: pointer;
}

.color-grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(65px, 1fr)) !important;
  gap: 12px !important;
  width: 100% !important;
  justify-content: center !important;
}

.color-item {
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  cursor: pointer;
  transition: transform 0.1s;
}

.color-item:hover {
  transform: scale(1.05);
}

.color-swatch-box {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  border: 1px solid rgba(0,0,0,0.1);
  position: relative;
}

.btn-delete-palette-color {
  position: absolute;
  top: -5px;
  right: 5px;
  background: #ff4d4d;
  color: white;
  border: none;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  opacity: 0;
  transition: opacity 0.2s, transform 0.2s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.color-item:hover .btn-delete-palette-color {
  opacity: 1;
}

.btn-delete-palette-color:hover {
  transform: scale(1.2);
  background: #cc0000;
}

.color-tag {
  font-size: 9px !important;
  margin-top: 3px !important;
  max-width: 50px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: center;
  display: block;
}

.paleta-footer {
  padding: 12px 20px;
  background: #fafafa;
  border-top: 1px solid #eee;
  flex-shrink: 0;
}

.paleta-tabs {
  display: flex !important;
}

.tab-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #ddd;
  background: #fff;
  margin-right: 6px;
  font-weight: bold;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tab-btn.active {
  background: #1985ac;
  color: #fff;
  border-color: #1985ac;
}

.btn-close-modal {
  background: #eee;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-close-modal:hover {
  background: #ddd;
}

.toggle-buttons-row {
  display: flex !important;
  align-items: center !important;
}

.color-btn {
  width: 30px;
  height: 30px;
  border: none;
  border-radius: 6px;
  color: #fff;
  font-weight: bold;
  margin-right: 5px;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.color-btn.pantone { background: #008031; }
.color-btn.cmyk { background: #00c503; }
.color-btn.ral { background: #608929; }

/* Scrollbar styling */
.paleta-body::-webkit-scrollbar { width: 4px; }
.paleta-body::-webkit-scrollbar-track { background: #f1f1f1; }
.paleta-body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
</style>
