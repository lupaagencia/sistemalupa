<template>
  <div class="calculator-container">
    <!-- Header -->
    <div class="header-section">
      <span class="icon-box"><i class="fas fa-boxes"></i></span>
      <div>
        <h2 class="mb-0">Calculador de Cabidas y Máquinas</h2>
        <small class="text-muted font-weight-bold">Optimización automática con Medidas de Ajuste (Pliego 70x100 cm) y Máquinas GTO / SORM</small>
      </div>
    </div>

    <div class="row">
      <!-- Panel Izquierdo: Parámetros de Entrada -->
      <div class="col-lg-4 col-md-5">
        <div class="card p-4 shadow-sm border-0 input-panel">
          <h4 class="mb-3 text-dark font-weight-bold">Pieza y Configuración</h4>

          <!-- Medidas de la Pieza (Producto) -->
          <div class="section-label">1. MEDIDAS DE LA PIEZA (PRODUCTO)</div>
          <div class="row mb-3">
            <div class="col-6">
              <label class="input-label">Ancho Pieza (cm)</label>
              <input v-model.number="tamanoAncho" type="number" step="0.1" class="form-control check-input" placeholder="Ej. 40" @input="calcular">
            </div>
            <div class="col-6">
              <label class="input-label">Largo Pieza (cm)</label>
              <input v-model.number="tamanoAlto" type="number" step="0.1" class="form-control check-input" placeholder="Ej. 30" @input="calcular">
            </div>
          </div>

          <!-- Cantidad Solicitada -->
          <div class="mb-3">
            <label class="input-label">Cantidad Requerida (Unidades)</label>
            <input v-model.number="cantidadSolicitada" type="number" class="form-control check-input" placeholder="Ej. 1000" @input="calcular">
          </div>

          <!-- Filtros de Máquina y Pliego -->
          <div class="section-label">2. RESTRICCIONES Y PREFERENCIAS</div>
          <div class="mb-3">
            <label class="input-label">Máquina Impresora</label>
            <select v-model="filtroMaquina" class="form-control check-input" @change="calcular">
              <option value="TODAS">-- Evaluar Todas (GTO y SORM) --</option>
              <option value="GTO">Solo GTO (Mín: 22x14 cm | Máx: 50x35 cm)</option>
              <option value="SORM">Solo SORM (Mín: 35x27.5 cm | Máx: 70x52 cm)</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="input-label">Pliego Base (Papel)</label>
            <select v-model="filtroPliego" class="form-control check-input" @change="calcular">
              <option value="70x100">Pliego 70x100 cm</option>
            </select>
          </div>

          <!-- Botón de Cálculo -->
          <button @click="calcular" class="btn btn-black btn-block btn-lg shadow-sm">
            <i class="fas fa-calculator mr-1"></i> Calcular Opciones de Cabida
          </button>
        </div>
      </div>

      <!-- Panel Derecho: Resultados y Evaluación de Opciones de Ajuste -->
      <div class="col-lg-8 col-md-7">
        <!-- Estado Vacío -->
        <div v-if="!tamanoAncho || !tamanoAlto || tamanoAncho <= 0 || tamanoAlto <= 0" 
             class="text-center p-5 text-muted bg-white shadow-sm rounded h-100 d-flex flex-column justify-content-center align-items-center">
          <i class="fas fa-border-all fa-4x mb-3 text-secondary" style="opacity: 0.2"></i>
          <h4 class="text-secondary font-weight-bold">Ingrese las medidas de la pieza</h4>
          <p class="mb-0">Escriba el ancho y largo de la pieza para evaluar automáticamente <br>todas las medidas de pliego configuradas en Ajustes y sus máquinas (GTO / SORM).</p>
        </div>

        <!-- Sin Opciones Válidas -->
        <div v-else-if="opcionesEvaluadas.length === 0" class="alert alert-warning shadow-sm p-4 text-center">
          <i class="fas fa-exclamation-circle fa-2x mb-2 text-warning"></i>
          <h5>No se encontraron cortes de pliego compatibles</h5>
          <p class="mb-0">La pieza de {{ tamanoAncho }}x{{ tamanoAlto }} cm es demasiado grande para caber en las medidas de ajuste o en los límites de las máquinas seleccionadas.</p>
        </div>

        <!-- Resultados Con Opciones Válidas -->
        <div v-else>
          <!-- Tarjeta de la Mejor Opción Recomendada -->
          <div class="card border-0 shadow-sm mb-4 bg-gradient-recommendation text-white" v-if="mejorOp">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge badge-warning text-dark font-weight-bold px-3 py-1" style="font-size: 0.85rem;">
                  <i class="fas fa-star mr-1"></i> MEJOR OPCIÓN RECOMENDADA
                </span>
                <span class="font-weight-bold badge badge-light text-dark px-2 py-1">
                  Pliego Base: {{ mejorOp.pliego_base }} cm
                </span>
              </div>
              <div class="row align-items-center mt-3">
                <div class="col-md-7">
                  <h3 class="font-weight-extrabold mb-1 text-white">{{ mejorOp.corte_detalle }}</h3>
                  <div class="d-flex align-items-center mt-2">
                    <span v-if="mejorOp.maquina === 'GTO'" class="badge badge-success px-3 py-1 mr-2" style="font-size: 0.9rem;">
                      <i class="fas fa-print mr-1"></i> Máquina GTO (22x14 - 50x35)
                    </span>
                    <span v-else class="badge badge-primary px-3 py-1 mr-2" style="font-size: 0.9rem;">
                      <i class="fas fa-print mr-1"></i> Máquina SORM (35x27.5 - 70x52)
                    </span>
                    <span class="text-white-50 small">Dimensión Corte: <strong>{{ mejorOp.corte_valor }} cm</strong></span>
                  </div>
                </div>
                <div class="col-md-5 text-md-right mt-3 mt-md-0">
                  <div class="d-inline-block text-left mr-3">
                    <div class="small text-white-50 text-uppercase font-weight-bold">Cabida por Pliego</div>
                    <div class="h2 font-weight-extrabold mb-0">{{ mejorOp.total_piezas_pliego }} <small style="font-size: 1rem;">pzas</small></div>
                  </div>
                  <div class="d-inline-block text-left" v-if="cantidadSolicitada > 0">
                    <div class="small text-white-50 text-uppercase font-weight-bold">Pliegos Enteros</div>
                    <div class="h2 font-weight-extrabold mb-0 text-warning">{{ mejorOp.pliegos_necesarios }} <small style="font-size: 1rem;">pliegos</small></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla Comparativa de Opciones -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-list-ol mr-1 text-primary"></i> Opciones de Corte Calculadas ({{ opcionesEvaluadas.length }})</span>
              <small class="text-muted">Ordenadas por menor cantidad de pliegos y desperdicio</small>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 table-sm align-middle">
                <thead class="thead-dark">
                  <tr>
                    <th>Máquina</th>
                    <th>Pliego Base</th>
                    <th>Fracción / Corte (Ajustes)</th>
                    <th class="text-center">Cabida / Corte</th>
                    <th class="text-center">Piezas / Pliego</th>
                    <th class="text-center" v-if="cantidadSolicitada > 0">Pliegos Req.</th>
                    <th class="text-center">Desperdicio</th>
                    <th class="text-center">Ver</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(op, idx) in opcionesEvaluadas" :key="idx" :class="{'table-success-highlight': selectedOp === op}">
                    <td>
                      <span v-if="op.maquina === 'GTO'" class="badge badge-success font-weight-bold">GTO</span>
                      <span v-else class="badge badge-primary font-weight-bold">SORM</span>
                    </td>
                    <td><span class="badge badge-secondary">{{ op.pliego_base }}</span></td>
                    <td><strong>{{ op.corte_detalle }}</strong></td>
                    <td class="text-center font-weight-bold">{{ op.cabida_por_corte }} pzas</td>
                    <td class="text-center text-success font-weight-bold" style="font-size: 1.05rem;">
                      {{ op.total_piezas_pliego }} pzas
                    </td>
                    <td class="text-center font-weight-bold text-dark" v-if="cantidadSolicitada > 0">
                      {{ op.pliegos_necesarios }}
                    </td>
                    <td class="text-center">
                      <span :class="op.desperdicio_pct > 30 ? 'text-danger' : 'text-success'" class="font-weight-bold">
                        {{ op.desperdicio_pct }}%
                      </span>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm" :class="selectedOp === op ? 'btn-success' : 'btn-outline-secondary'" @click="selectOp(op)">
                        <i class="fas fa-eye"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Visualización del Corte Seleccionado -->
          <div class="visualization-container shadow-sm" v-if="selectedOp">
            <div class="d-flex justify-content-between w-100 align-items-center mb-3">
              <h5 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-th mr-1 text-success"></i>
                Esquema de Corte: {{ selectedOp.corte_detalle }} ({{ selectedOp.corte_valor }} cm en {{ selectedOp.maquina }})
              </h5>
              <span class="badge badge-info px-3 py-1 font-size-13">
                Cabida en Hoja de Corte: {{ selectedOp.cabida_por_corte }} piezas
              </span>
            </div>

            <!-- SVG Layout -->
            <div class="dim-top text-center text-muted mb-1 font-weight-bold">{{ layoutWidth }} cm</div>
            <div class="d-flex justify-content-center align-items-center">
              <div class="dim-left text-muted mr-2 font-weight-bold" style="writing-mode: vertical-rl; transform: rotate(180deg);">{{ layoutHeight }} cm</div>
              <div class="svg-wrapper">
                <svg :viewBox="`0 0 ${layoutWidth} ${layoutHeight}`" class="layout-svg" preserveAspectRatio="xMidYMid meet">
                  <!-- Hoja de corte -->
                  <rect x="0" y="0" :width="layoutWidth" :height="layoutHeight" fill="#f8f9fa" stroke="#adb5bd" stroke-width="0.5"/>
                  
                  <!-- Piezas acomodadas -->
                  <g v-for="(piece, index) in pieces" :key="index">
                    <rect 
                      :x="piece.x" 
                      :y="piece.y" 
                      :width="piece.w" 
                      :height="piece.h" 
                      fill="#a0ef6e" 
                      stroke="#4ea61b" 
                      stroke-width="0.4"
                      class="piece-rect"
                    />
                    <text 
                      :x="piece.x + piece.w/2" 
                      :y="piece.y + piece.h/2" 
                      dominant-baseline="middle" 
                      text-anchor="middle" 
                      fill="#264e10" 
                      :font-size="Math.min(piece.w, piece.h) * 0.35"
                      style="pointer-events: none; font-weight: bold;"
                    >{{ index + 1 }}</text>
                  </g>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tamanoAncho: null,
      tamanoAlto: null,
      cantidadSolicitada: 1000,
      filtroMaquina: 'TODAS',
      filtroPliego: 'TODOS',

      arrayCortesDB: [],
      opcionesEvaluadas: [],
      mejorOp: null,
      selectedOp: null,

      pieces: [],
      layoutWidth: 50,
      layoutHeight: 35,

      // Especificaciones de Máquinas
      maquinasSpec: {
        'GTO': { nombre: 'GTO', minW: 22, minH: 14, maxW: 50, maxH: 35 },
        'SORM': { nombre: 'SORM', minW: 35, minH: 27.5, maxW: 70, maxH: 52 }
      }
    };
  },
  methods: {
    cargarCortesDB() {
      let me = this;
      axios.get('/ajustes/listar?tipo=medidas_pliego').then(res => {
        me.arrayCortesDB = res.data || [];
        me.calcular();
      }).catch(err => {
        console.log(err);
      });
    },
    fitsInBounds(w, h, minW, minH, maxW, maxH) {
      const normal = (w >= minW && h >= minH && w <= maxW && h <= maxH) ||
                     (w >= minH && h >= minW && w <= maxH && h <= maxW);

      const rotated = (h >= minW && w >= minH && h <= maxW && h <= maxH) ||
                      (h >= minH && w >= minW && h <= maxH && h <= maxW);

      return normal || rotated;
    },
    solveRowStacking(W, H, pieceW, pieceH) {
      if (pieceW <= 0 || pieceH <= 0 || W <= 0 || H <= 0) return { total: 0 };
      const count1 = Math.floor(W / pieceW);
      const count2 = Math.floor(W / pieceH);

      let best = { total: 0, n1: 0, n2: 0, count1, count2, rowH1: pieceH, rowH2: pieceW, w: pieceW, h: pieceH };

      for (let n1 = 0; n1 * pieceH <= H; n1++) {
        const remainingH = H - (n1 * pieceH);
        const n2 = Math.floor(remainingH / pieceW);

        const total = (n1 * count1) + (n2 * count2);
        if (total > best.total) {
          best = { total, n1, n2, count1, count2, rowH1: pieceH, rowH2: pieceW, w: pieceW, h: pieceH };
        }
      }
      return best;
    },
    getCabidaLayout(cutW, cutH, pieceW, pieceH) {
      const sol1 = this.solveRowStacking(cutW, cutH, pieceW, pieceH);
      const sol2 = this.solveRowStacking(cutH, cutW, pieceW, pieceH);

      if (sol2.total > sol1.total) {
        return { layout: sol2, isRotated: true, total: sol2.total };
      } else {
        return { layout: sol1, isRotated: false, total: sol1.total };
      }
    },
    calcular() {
      this.opcionesEvaluadas = [];
      this.mejorOp = null;
      this.selectedOp = null;
      this.pieces = [];

      if (!this.tamanoAncho || !this.tamanoAlto || this.tamanoAncho <= 0 || this.tamanoAlto <= 0) {
        return;
      }

      const pW = Number(this.tamanoAncho);
      const pH = Number(this.tamanoAlto);
      const qty = this.cantidadSolicitada && this.cantidadSolicitada > 0 ? Number(this.cantidadSolicitada) : 0;

      let evaluadas = [];

      this.arrayCortesDB.forEach(corte => {
        // Filtrar por Pliego Base si se seleccionó uno específico
        if (this.filtroPliego !== 'TODOS' && corte.categoria !== this.filtroPliego) {
          return;
        }

        if (!corte.valor || !corte.valor.includes('x')) return;
        const parts = corte.valor.split('x').map(Number);
        if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return;

        const cutW = parts[0];
        const cutH = parts[1];

        // Extraer número de cortes de la fracción (ej. 1/4 -> 4)
        const match = corte.detalle.match(/1\/(\d+)/);
        const cortesPorPliego = match ? Number(match[1]) : 1;

        // Evaluar cada máquina (GTO y SORM)
        Object.keys(this.maquinasSpec).forEach(mKey => {
          if (this.filtroMaquina !== 'TODAS' && this.filtroMaquina !== mKey) {
            return;
          }

          const spec = this.maquinasSpec[mKey];
          if (this.fitsInBounds(cutW, cutH, spec.minW, spec.minH, spec.maxW, spec.maxH)) {
            const result = this.getCabidaLayout(cutW, cutH, pW, pH);
            if (result.total > 0) {
              const totalPiezasPliego = result.total * cortesPorPliego;
              const pliegosReq = qty > 0 ? Math.ceil(qty / totalPiezasPliego) : 0;

              // Calcular % desperdicio
              const pliegoW = (corte.categoria === '60x90') ? 90 : 100;
              const pliegoH = (corte.categoria === '60x90') ? 60 : 70;
              const areaPliego = pliegoW * pliegoH;
              const areaUtil = totalPiezasPliego * (pW * pH);
              const desperdicioPct = Math.max(0, Math.round(((areaPliego - areaUtil) / areaPliego) * 1000) / 10);

              evaluadas.push({
                maquina: mKey,
                corte_detalle: corte.detalle,
                corte_valor: corte.valor,
                pliego_base: corte.categoria || '70x100',
                cutW: cutW,
                cutH: cutH,
                cortes_por_pliego: cortesPorPliego,
                cabida_por_corte: result.total,
                total_piezas_pliego: totalPiezasPliego,
                pliegos_necesarios: pliegosReq,
                desperdicio_pct: desperdicioPct,
                layoutResult: result
              });
            }
          }
        });
      });

      // Ordenar mejores opciones: primero menor número de pliegos, luego menor desperdicio
      evaluadas.sort((a, b) => {
        if (qty > 0) {
          if (a.pliegos_necesarios !== b.pliegos_necesarios) {
            return a.pliegos_necesarios - b.pliegos_necesarios;
          }
        } else {
          if (a.total_piezas_pliego !== b.total_piezas_pliego) {
            return b.total_piezas_pliego - a.total_piezas_pliego;
          }
        }
        return a.desperdicio_pct - b.desperdicio_pct;
      });

      this.opcionesEvaluadas = evaluadas;

      if (evaluadas.length > 0) {
        this.mejorOp = evaluadas[0];
        this.selectOp(evaluadas[0]);
      }
    },
    selectOp(op) {
      this.selectedOp = op;
      if (op && op.layoutResult) {
        this.layoutWidth = op.cutW;
        this.layoutHeight = op.cutH;
        this.generatePieces(op.layoutResult.layout, op.layoutResult.isRotated);
      }
    },
    generatePieces(layout, isRotated) {
      this.pieces = [];
      const { n1, n2, count1, count2, rowH1, rowH2, w, h } = layout;

      let currentAlgoY = 0;

      const addPiece = (algoX, algoY, pieceDimInRow, pieceDimInCol) => {
        let finalX, finalY, finalW, finalH;

        if (isRotated) {
          finalX = algoY;
          finalY = algoX;
          finalW = pieceDimInCol;
          finalH = pieceDimInRow;
        } else {
          finalX = algoX;
          finalY = algoY;
          finalW = pieceDimInRow;
          finalH = pieceDimInCol;
        }

        this.pieces.push({
          x: finalX,
          y: finalY,
          w: finalW,
          h: finalH
        });
      };

      for (let r = 0; r < n1; r++) {
        for (let c = 0; c < count1; c++) {
          addPiece(c * w, currentAlgoY, w, rowH1);
        }
        currentAlgoY += rowH1;
      }

      for (let r = 0; r < n2; r++) {
        for (let c = 0; c < count2; c++) {
          addPiece(c * h, currentAlgoY, h, rowH2);
        }
        currentAlgoY += rowH2;
      }
    }
  },
  mounted() {
    this.cargarCortesDB();
  }
};
</script>

<style scoped>
.calculator-container {
  padding: 25px;
  background-color: #f8f9fa;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  border-radius: 16px;
}

.header-section {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  background-color: #ffffff;
  padding: 16px 25px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.icon-box {
  width: 55px;
  height: 55px;
  background-color: #a0ef6e;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  font-size: 1.8rem;
  color: #1a1a1a;
}

.header-section h2 {
  font-weight: 800;
  color: #1f2937;
  font-size: 1.7rem;
}

.input-panel {
  background: #fff;
  border-radius: 16px;
}

.section-label {
  font-size: 0.72rem;
  color: #6c757d;
  font-weight: 800;
  text-transform: uppercase;
  margin-bottom: 8px;
  letter-spacing: 0.8px;
}

.input-label {
  font-size: 0.85rem;
  color: #495057;
  margin-bottom: 4px;
  font-weight: 600;
}

.check-input {
  border-radius: 8px;
  border: 1px solid #ced4da;
  padding: 10px;
  height: auto;
  background-color: #fff;
  font-size: 0.95rem;
  transition: all 0.2s;
  font-weight: 600;
  color: #212529;
}

.check-input:focus {
  border-color: #a0ef6e;
  box-shadow: 0 0 0 3px rgba(160, 239, 110, 0.25);
  outline: none;
}

.btn-black {
  background-color: #111;
  color: #fff;
  border-radius: 10px;
  font-weight: 700;
  padding: 12px;
  border: none;
  transition: all 0.2s;
}

.btn-black:hover {
  background-color: #000;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.bg-gradient-recommendation {
  background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
  border-radius: 16px;
}

.font-weight-extrabold {
  font-weight: 900;
}

.table-success-highlight {
  background-color: #f0fdf4 !important;
  font-weight: bold;
}

.visualization-container {
  background-color: #fff;
  border-radius: 14px;
  padding: 20px;
  border: 1px solid #e9ecef;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.svg-wrapper {
  width: 100%;
  max-width: 480px;
  height: auto;
  border: 1px solid #ced4da;
  background-color: #fff;
  border-radius: 4px;
  padding: 1px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.layout-svg {
  width: 100%;
  height: auto;
  display: block;
}

.piece-rect {
  transition: all 0.2s ease;
}

.piece-rect:hover {
  fill: #bbf7d0;
  stroke: #16a34a;
}

.font-size-13 {
  font-size: 13px;
}
</style>