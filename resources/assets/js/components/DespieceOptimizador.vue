<template>
    <div class="container-fluid py-4">
        <h2 class="font-weight-bold text-dark mb-4">
            <i class="fa fa-cut"></i> Optimización de Cortes 2D
        </h2>

        <div class="row">
            <!-- Configuración Lateral -->
            <div class="col-md-3">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white font-weight-bold">
                        Configuración del Tablero
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label class="form-label text-muted">Material a Optimizar</label>
                            <select v-model="materialSeleccionado" @change="optimizar()" class="form-control form-control-sm">
                                <option v-for="(piezas, material) in piezasPorMaterial" :key="material" :value="material">
                                    {{ material }} ({{ piezas.length }} piezas)
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-2">
                            <label class="form-label text-muted">Tamaño del Tablero (Pliego)</label>
                            <div class="d-flex">
                                <input type="number" v-model.number="config.tableroAncho" @change="optimizar()" class="form-control form-control-sm mr-1" placeholder="Largo">
                                <span class="align-self-center mx-1">x</span>
                                <input type="number" v-model.number="config.tableroAlto" @change="optimizar()" class="form-control form-control-sm ml-1" placeholder="Ancho">
                            </div>
                            <small class="text-muted">Ej: 2440 x 2150 mm</small>
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label text-muted">Espesor de Corte (Disco sierra)</label>
                            <input type="number" v-model.number="config.espesorCorte" @change="optimizar()" class="form-control form-control-sm" placeholder="3">
                        </div>

                        <div class="custom-control custom-switch mt-3">
                            <input type="checkbox" class="custom-control-input" id="switchRotacion" v-model="config.permitirRotacion" @change="optimizar()">
                            <label class="custom-control-label" for="switchRotacion">Permitir Rotar Piezas (Optimizar más)</label>
                        </div>
                        
                        <button class="btn btn-primary btn-block mt-4" @click="optimizar()">
                            <i class="fa fa-cogs"></i> Re-optimizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Área de Visualización (Resultados) -->
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">Planos de Corte Generados</span>
                        <span class="badge badge-primary">Tableros Usados: {{ tableros.length }}</span>
                    </div>
                    <div class="card-body bg-light" style="min-height: 500px; overflow-x: auto;">
                        <div v-if="!materialSeleccionado" class="text-center text-muted mt-5">
                            Seleccione un material para generar la optimización de cortes.
                        </div>
                        
                        <!-- Visualizador de Tableros -->
                        <div v-for="(tablero, index) in tableros" :key="index" class="mb-5">
                            <h5 class="text-dark">Tablero {{ index + 1 }}</h5>
                            <div class="board-container" :style="getBoardStyle()">
                                <!-- Piezas dentro del tablero -->
                                <div v-for="(pieza, i) in tablero.piezas" :key="i" class="piece-box" :style="getPieceStyle(pieza)">
                                    <span class="piece-label">{{ pieza.nombre }}<br>{{ pieza.w }}x{{ pieza.h }}</span>
                                </div>
                            </div>
                            <div class="mt-2 text-muted small">
                                Uso: {{ calculateUsage(tablero) }}% | Desperdicio: {{ 100 - calculateUsage(tablero) }}%
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
    props: ['proyectoId'],
    data() {
        return {
            piezasBrutas: [],
            piezasPorMaterial: {},
            materialSeleccionado: null,
            config: {
                tableroAncho: 2440,
                tableroAlto: 2150,
                espesorCorte: 3, // mm
                permitirRotacion: true
            },
            tableros: [] // Resultados de la optimización
        }
    },
    mounted() {
        if (this.proyectoId) {
            this.cargarPiezas();
        }
    },
    methods: {
        cargarPiezas() {
            axios.get(`/despiece/proyectos/${this.proyectoId}/piezas-globales`).then(response => {
                this.piezasBrutas = response.data;
                this.agruparPorMaterial();
            }).catch(error => {
                console.error(error);
            });
        },
        agruparPorMaterial() {
            let grupos = {};
            this.piezasBrutas.forEach(p => {
                if (!grupos[p.material]) {
                    grupos[p.material] = [];
                }
                // Multiplicamos la pieza por la cantidad requerida
                for(let i=0; i<p.cantidad; i++) {
                    grupos[p.material].push({
                        id: p.id + '_' + i,
                        nombre: p.nombre_pieza,
                        w: parseFloat(p.largo), // largo es el mayor
                        h: parseFloat(p.ancho)  // ancho es el menor
                    });
                }
            });
            this.piezasPorMaterial = grupos;
            if (Object.keys(grupos).length > 0) {
                this.materialSeleccionado = Object.keys(grupos)[0];
                this.optimizar();
            }
        },
        // ALGORITMO GUILLOTINE BIN PACKING (Simplificado)
        optimizar() {
            if (!this.materialSeleccionado || !this.piezasPorMaterial[this.materialSeleccionado]) return;
            
            let piezasList = JSON.parse(JSON.stringify(this.piezasPorMaterial[this.materialSeleccionado]));
            
            // Ordenar de mayor a menor área para el empaquetado (Heurística de First Fit Decreasing)
            piezasList.sort((a, b) => (b.w * b.h) - (a.w * a.h));

            const W = this.config.tableroAncho;
            const H = this.config.tableroAlto;
            const esp = this.config.espesorCorte;

            this.tableros = [];

            // Helper class para el árbol de Guillotine
            class Node {
                constructor(x, y, w, h) {
                    this.x = x;
                    this.y = y;
                    this.w = w;
                    this.h = h;
                    this.used = false;
                    this.right = null;
                    this.down = null;
                }
                
                insert(pw, ph, allowRot) {
                    if (this.used) {
                        return (this.right && this.right.insert(pw, ph, allowRot)) || (this.down && this.down.insert(pw, ph, allowRot));
                    }
                    
                    let fitsNormal = (pw <= this.w && ph <= this.h);
                    let fitsRotated = allowRot && (ph <= this.w && pw <= this.h);
                    
                    if (!fitsNormal && !fitsRotated) return null;
                    
                    let useRotated = false;
                    if (fitsNormal && fitsRotated) {
                        // Elegir la orientación que deje menos desperdicio en la dimensión más corta
                        if (Math.min(this.w - pw, this.h - ph) > Math.min(this.w - ph, this.h - pw)) {
                            useRotated = true;
                        }
                    } else if (fitsRotated) {
                        useRotated = true;
                    }
                    
                    let finalW = useRotated ? ph : pw;
                    let finalH = useRotated ? pw : ph;
                    
                    // Solo si cabe exacto o sobra
                    if (finalW > this.w || finalH > this.h) return null;
                    
                    this.used = true;
                    
                    // Split the remaining space: Vertical Guillotine Split
                    this.down = new Node(this.x, this.y + finalH + esp, this.w, this.h - finalH - esp);
                    this.right = new Node(this.x + finalW + esp, this.y, this.w - finalW - esp, finalH);
                    
                    return { x: this.x, y: this.y, w: finalW, h: finalH, rotated: useRotated };
                }
            }

            // Acomodar cada pieza
            piezasList.forEach(p => {
                let placed = false;
                
                // Intentar en los tableros existentes
                for (let t of this.tableros) {
                    let res = t.root.insert(p.w, p.h, this.config.permitirRotacion);
                    if (res) {
                        t.piezas.push({ ...p, x: res.x, y: res.y, w: res.w, h: res.h, rotated: res.rotated });
                        placed = true;
                        break;
                    }
                }
                
                // Si no cupo, crear un nuevo tablero
                if (!placed) {
                    let newBoard = { root: new Node(0, 0, W, H), piezas: [] };
                    let res = newBoard.root.insert(p.w, p.h, this.config.permitirRotacion);
                    if (res) {
                        newBoard.piezas.push({ ...p, x: res.x, y: res.y, w: res.w, h: res.h, rotated: res.rotated });
                    } else {
                        console.warn("Pieza demasiado grande para el tablero:", p);
                        // Force add to a new board with 0,0 anyway (will overflow visually)
                        newBoard.piezas.push({ ...p, x: 0, y: 0, rotated: false });
                    }
                    this.tableros.push(newBoard);
                }
            });
        },
        
        getBoardStyle() {
            // Factor de escala visual: ancho max 800px
            return {
                width: '800px',
                height: ((800 / this.config.tableroAncho) * this.config.tableroAlto) + 'px',
                position: 'relative',
                backgroundColor: '#d8b998', // Color madera claro
                border: '2px solid #8b5a2b',
                boxShadow: '0 4px 8px rgba(0,0,0,0.1)'
            };
        },
        
        getPieceStyle(pieza) {
            const scaleX = 800 / this.config.tableroAncho;
            const scaleY = scaleX; // Mantener proporción aspect ratio
            
            return {
                position: 'absolute',
                left: (pieza.x * scaleX) + 'px',
                top: (pieza.y * scaleY) + 'px',
                width: (pieza.w * scaleX) + 'px',
                height: (pieza.h * scaleY) + 'px',
                backgroundColor: '#f5deb3',
                border: '1px solid #a0522d',
                boxSizing: 'border-box',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                overflow: 'hidden'
            };
        },
        
        calculateUsage(tablero) {
            let totalArea = this.config.tableroAncho * this.config.tableroAlto;
            let usedArea = 0;
            tablero.piezas.forEach(p => {
                usedArea += (p.w * p.h);
            });
            return ((usedArea / totalArea) * 100).toFixed(1);
        }
    }
}
</script>

<style scoped>
.piece-box {
    transition: all 0.2s;
}
.piece-box:hover {
    background-color: #ffebcd !important;
    z-index: 10;
    box-shadow: 0 0 5px rgba(0,0,0,0.5);
}
.piece-label {
    font-size: 10px;
    font-weight: bold;
    color: #5c3a21;
    text-align: center;
    pointer-events: none;
    line-height: 1.1;
}
</style>
