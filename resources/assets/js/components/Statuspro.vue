<template>

    <div class="card-body" id="contestatuspro" v-scroll="handleScroll">
        <div class="form-group mb-2">
            <div class="ordencards-wrapper" ref="toolbarWrapper" style="min-height: 50px;">
                <div id="ordencards" 
                     class="card shadow-sm border-0" 
                     :class="{ 'fixed-toolbar': isSticky }"
                     :style="stickyStyles">
                    <div class="card-body p-0 p-md-1">
                        <!-- Header with Search and Toggle (Mobile First) -->
                        <div class="d-flex align-items-center justify-content-between mb-0 pb-0">
                             <!-- Search Input (Primary on mobile) -->
                            <div class="search-container flex-grow-1" style="max-width: 450px;">
                                <div class="input-group shadow-sm-premium">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-primary" style="opacity: 0.7;"></i></span>
                                    </div>
                                    <input type="text" 
                                           v-model="buscar" 
                                           class="form-control border-left-0 bg-white" 
                                           placeholder="Buscar por cliente o No. Pedido..."
                                           style="font-size: 0.8rem; height: 30px;">
                                </div>
                            </div>
                            
                            <!-- Quick Nav Buttons for Desktop Sticky Toolbar -->
                            <div class="quick-nav-glass ml-2 d-none d-lg-flex">
                                <button class="btn btn-quick-nav-sm nav-completados" @click="$emit('nav', 'pedidoscompletados')">
                                    <i class="fa fa-check-circle mr-1"></i> <span>Completados</span>
                                </button>
                                <button class="btn btn-quick-nav-sm nav-entregar" @click="$emit('nav', 'pedidosentregar')">
                                    <i class="fa fa-truck mr-1"></i> <span>Para Entregar</span>
                                </button>
                            </div>
                            
                            <!-- Toggle Button for Mobile -->
                            <button class="btn btn-sm btn-outline-primary d-md-none ml-2" @click="showFilters = !showFilters" style="height: 34px; border-radius: 8px;">
                                <i class="fa" :class="showFilters ? 'fa-chevron-up' : 'fa-filter'"></i>
                                {{ showFilters ? 'Cerrar' : 'Filtros' }}
                            </button>
                            
                            <!-- Sync Indicator - Smarter UI -->
                            <div class="sync-badge ml-3 d-none d-md-flex align-items-center px-2 py-0 rounded-pill" 
                                 :class="syncing ? 'syncing-active' : 'syncing-idle'"
                                 style="height: 28px;">
                                <div :class="syncing ? 'sync-spinner' : 'pulse-dot'" class="mr-2"></div>
                                <div class="d-flex flex-column" style="line-height: 1.1;">
                                    <span class="badge-title font-weight-bold uppercase" style="letter-spacing: 0.5px; font-size: 0.6rem;">
                                        {{ syncing ? 'Sincronizando...' : 'Conexión en Vivo' }}
                                    </span>
                                    <span class="sync-time text-muted" style="font-size: 0.5rem;">Última: {{ lastSyncTime }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Collapsible Section for Filters and Processes -->
                        <div :class="{'d-none d-md-block': !showFilters}">
                            <!-- Horizontal Line for Mobile -->
                            <hr class="d-md-none my-2">
                            
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-0">
                                <!-- Status Legend -->
                                <div class="d-flex align-items-center flex-wrap mr-2 mt-0">
                                    <span class="font-weight-bold mr-1 text-dark small d-none d-sm-inline" style="font-size: 0.75rem;">Est:</span>
                                    <span class="badge-custom-sm diseno" title="Diseño">Dis.</span>
                                    <span class="badge-custom-sm enviarp" title="Enviar a producción">Env. P</span>
                                    <span class="badge-custom-sm enproduccion" title="En producción">Prod.</span>
                                    <span class="badge-custom-sm empacado" title="Empacado">Emp.</span>
                                    <span class="badge-custom-sm entregar" title="Para entregar">Ent.</span>
                                </div>

                                <!-- Sort Controls -->
                                <div class="sort-buttons d-flex align-items-center mt-2 mt-md-0">
                                    <span class="text-muted mr-2 small font-weight-bold text-uppercase d-none d-sm-inline">Ordenar:</span>
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-white border-light py-1 px-2" style="font-size: 0.758rem" @click="ordenarOrdenes(ordenes,orden=>orden.articulo.nombre,'asc')">Prod.</button>
                                        <button class="btn btn-white border-light py-1 px-2" style="font-size: 0.758rem" @click="ordenarOrdenes(ordenes,orden=>orden.cliente.razonsocial,'asc')">Cli.</button>
                                        <button class="btn btn-white border-light py-1 px-2" style="font-size: 0.758rem" @click="ordenarOrdenes(ordenes,orden=>orden.papeles,'asc')">Papel</button>
                                        <button class="btn btn-white border-light py-1 px-2" style="font-size: 0.758rem" @click="ordenarOrdenes(ordenes,orden=>orden.fecha_entrega,'asc')">Entreg.</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Process Buttons Row -->
                            <div class="d-flex flex-row flex-wrap justify-content-start align-items-center w-100 mt-0 pt-0">
                                <button v-for="(proceso, ind) in proc" :key="ind"  
                                        @click="bajarEstado(proceso.proceso)" 
                                        class="btn btn-process m-0-5 shadow-sm"
                                        style="width: auto; flex: 1; min-width: 70px; font-size: 0.65rem; padding: 1px 4px; line-height: 1.1;">
                                    {{ proceso.proceso }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div v-for="(proceso, ind) in proc" :key="ind" :id="proceso.proceso" class="ordenes elemento"  @dragover.prevent 
                @drop="handleDrop"  ref="elementos">
                    <h4 class="btn btn-primary font-xl d-flex justify-content-between align-items-center" 
                        style="margin-bottom: 0;">
                        <span @click="toggleSection(proceso.proceso)" style="cursor: pointer; flex-grow: 1; text-align: left;">{{proceso.proceso}}</span>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-light mr-2 text-primary shadow-sm" @click.stop="imprimirStatus(proceso.proceso)" title="Imprimir esta lista" style="font-size: 0.75rem; font-weight: bold; border-radius: 4px; padding: 2px 8px;">
                                <i class="fa fa-print"></i> <span class="d-none d-sm-inline ml-1">Imprimir</span>
                            </button>
                            <i class="fa d-md-none" @click="toggleSection(proceso.proceso)" :class="isCollapsed(proceso.proceso) ? 'fa-plus-circle' : 'fa-minus-circle'" style="cursor: pointer;"></i>
                        </div>
                    </h4>
                    <div v-show="!isCollapsed(proceso.proceso)" class="content-seccion">
                    <div v-if="proceso.proceso=='Impresión'" class="maquina " :id="proceso.proceso">
                        <div v-for="(uso,i) in agrupadoPorUso" :key="i" class="contenedor-seccion" >
                            <h5 v-if="i==''" class="btn boton-principal">Sin plancha</h5>
                            <h5 v-else class="btn boton-principal">{{ i }}</h5>
                            <div class="ordencard grid-container">
                                <template v-for="(orden, index) in ordenes">
                                    <div v-if="orden.status.estado==proceso.proceso && orden.maquina.uso==i && matchesSearch(orden)" 
                                         :key="orden.id" class="grid-item" draggable="true"
                                         @dragstart="dragStart(index)"
                                         @dragover.prevent 
                                         @drop.stop="onDrop(index)">
                                        <ordencard  :scroll="scroll" :operario="i" :status="proceso.proceso" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" @modal-opened="onModalOpened" @modal-closed="onModalClosed"> 
                                        </ordencard>
                                    </div>
                                </template>
                            </div>
                        
                        </div>
                    </div>
                    <div v-else-if="proceso.proceso=='Troquelado'" class="maquina ">
                       
                        <div v-for="(activo,i) in activos[proceso.proceso]" :key="i" class="contenedor-seccion"  >
                            <h5 v-if="activo.id==0" class="btn boton-principal">{{activo.activo}}</h5>
                            <h5 v-else class="btn boton-principal">{{ activo.activo }}</h5>
                            <div class="ordencard grid-container">
                                <template v-for="(orden, index) in ordenes">
                                    <div v-if="orden.status.estado==proceso.proceso && activo.id==orden.troquelado[0].costois_id && matchesSearch(orden)" 
                                         :key="orden.id" class="grid-item" draggable="true"
                                         @dragstart="dragStart(index)"
                                         @dragover.prevent 
                                         @drop.stop="onDrop(index)">
                                        <ordencard  :scroll="scroll" :operario="activo.activo" :status="proceso.proceso" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" @modal-opened="onModalOpened" @modal-closed="onModalClosed"> 
                                        </ordencard>
                                    </div>
                                </template>
                            </div>
                        
                        </div>
                    </div>
                    <div v-else-if="proceso.proceso=='Terminado'" class="maquina ">
                        
                        <!-- Sección especial de órdenes compartidas/divididas -->
                        <div class="contenedor-seccion w-100 mb-3 border border-warning rounded p-2" 
                             style="background-color: #fffdf5; border-width: 2px !important; flex: 1 1 100%;" 
                             v-if="ordenes.some(o => o.status.estado == proceso.proceso && o.terminado && o.terminado.length > 1)">
                            <h5 class="btn btn-warning text-dark font-weight-bold w-100 text-left mb-2 shadow-sm" style="cursor: default; background-color: #ffc107 !important; border-color: #ffc107 !important;">
                                <i class="fa fa-users"></i> Trabajos Compartidos (Órdenes Divididas)
                            </h5>
                            <div class="ordencard grid-container">
                                <template v-for="(orden, index) in ordenes">
                                    <div v-if="orden.status.estado==proceso.proceso && orden.terminado && orden.terminado.length > 1 && matchesSearch(orden)" 
                                         :key="orden.id" class="grid-item" draggable="true"
                                         @dragstart="dragStart(index)"
                                         @dragover.prevent 
                                         @drop.stop="onDrop(index)">
                                        <ordencard :scroll="scroll" :status="proceso.proceso" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" @modal-opened="onModalOpened" @modal-closed="onModalClosed"> 
                                        </ordencard>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Sección de operarias individuales -->
                        <div v-for="(activo,i) in activos[proceso.proceso]" :key="i" class="contenedor-seccion"  >
                            <h5 v-if="activo.id==0" class="btn boton-principal">{{activo.activo}}</h5>
                            <h5 v-else class="btn boto  n-principal">{{ activo.activo }}</h5>
                            <div class="ordencard grid-container">
                                <template v-for="(orden, index) in ordenes">
                                    <div v-if="orden.status.estado==proceso.proceso && orden.terminado && orden.terminado.some(t => t.costois_id == activo.id) && matchesSearch(orden)" 
                                         :key="orden.id" class="grid-item" draggable="true"
                                         @dragstart="dragStart(index)"
                                         @dragover.prevent 
                                         @drop.stop="onDrop(index)">
                                        <ordencard  :scroll="scroll" :operario="activo.activo" :status="proceso.proceso" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" @modal-opened="onModalOpened" @modal-closed="onModalClosed"> 
                                        </ordencard>
                                    </div>
                                </template>
                            </div>
                        
                        </div>
                    </div>
                        <div v-else class="ordencard grid-container" >
                            <template v-for="(orden, index) in ordenes">
                                <div v-if="orden.status.estado==proceso.proceso && matchesSearch(orden)" 
                                     :key="orden.id" class="grid-item" draggable="true"
                                     @dragstart="dragStart(index)"
                                     @dragover.prevent 
                                     @drop.stop="onDrop(index)">
                                    <ordencard :scroll="scroll" :status="proceso.proceso" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" @modal-opened="onModalOpened" @modal-closed="onModalClosed"> 
                                    </ordencard>
                                </div>
                            </template>
                        </div>
                    </div>
            </div>
        </div>
        <authmodal
            :mostrarModal="mostrarModal"
            @close="mostrarModal = null"
            @authorized="onAutorizado"/>
    </div>
</template>

<script>
    Vue.directive('scroll', {
    inserted: function (el, binding) {
        let f = function (evt) {
        if (binding.value(evt, el)) {
            window.removeEventListener('scroll', f)
        }
        }
        window.addEventListener('scroll', f)
    }
    })
    import ordencard from './partes/Ordencard.vue'
    import authmodal from './partes/AuthModal.vue'
    import { ref, onMounted, onUnmounted } from "vue";
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    let intervalo = null;
    export default {
        props:['user'],
        data (){
            return {
                contador: 50,   // segundos antes del próximo refresh
                intervalo: null,
                intervaloContador: null,
                items: ["Elemento 1", "Elemento 2", "Elemento 3"],
                droppedItems:[],
                draggedItem: null,
                ordenes:[],
                scroll:0,
                scroll2:0,
                scrollTop:0,
                interval:null,
                proc:[],
                agrupadoPorUso:[],
                activos:[],
                isSticky: false,
                toolbarWidth: 'auto',
                toolbarLeft: 0,
                lastUpdate: null,
                lastSyncTime: '---',
                editingMode: false,
                syncing: false,
                buscar: "",
                showFilters: false,
                collapsedSections: {}, // Tracks which status groups are collapsed
                mostrarModal: null,
                pendienteCambio: null,
                auth: false
            }
        },
        components: {
            ordencard,
            authmodal
        },
        computed:{
             stickyStyles() {
                if (this.isSticky) {
                    return {
                        position: 'fixed',
                        top: '55px', // Match navbar height
                        width: this.toolbarWidth + 'px',
                        left: this.toolbarLeft + 'px',
                        zIndex: 999
                    };
                }
                return {};
            }
        },
        watch: {
            buscar(val) {
                if (val && val.trim() !== '' && window.innerWidth < 768) {
                    // Expand sections that have matches
                    this.ordenes.forEach(orden => {
                        if (this.matchesSearch(orden)) {
                            this.$set(this.collapsedSections, orden.status.estado, false);
                        }
                    });
                }
            }
        },
        methods : {
            onModalOpened() {
                this.editingMode = true;
                // console.log('Editing mode ON');
            },
            onModalClosed() {
                this.editingMode = false;
                // console.log('Editing mode OFF');
                // Opt: Check for updates immediately after closing
                // this.checkForUpdates();
            },
            matchesSearch(orden) {
                if (!this.buscar) return true;
                const search = this.buscar.toLowerCase();
                const clientName = (orden.cliente && orden.cliente.razonsocial) ? orden.cliente.razonsocial.toLowerCase() : "";
                const orderId = orden.id ? orden.id.toString() : "";
                return clientName.includes(search) || orderId.includes(search);
            },
            handleToolbarScroll() {
                const wrapper = this.$refs.toolbarWrapper;
                if (!wrapper) return;
                
                const rect = wrapper.getBoundingClientRect();
                const triggerPoint = 55; // Stick when it hits the navbar
                
                if (window.innerWidth < 768) {
                    this.isSticky = false;
                    return;
                }

                if (rect.top < triggerPoint) {
                    this.isSticky = true;
                    this.toolbarWidth = rect.width;
                    this.toolbarLeft = rect.left;
                } else {
                    this.isSticky = false;
                }
            },
            toggleSection(proceso) {
                // Initialize if not exists. Defaults to true (collapsed) on mobile
                if (this.collapsedSections[proceso] === undefined) {
                    this.$set(this.collapsedSections, proceso, true);
                }
                this.collapsedSections[proceso] = !this.collapsedSections[proceso];
            },
            isCollapsed(proceso) {
                // On desktop, never collapse.
                if (window.innerWidth > 768) return false;
                
                // If there is an active search and the section has matches, keep it open
                if (this.buscar && this.buscar.trim() !== '') {
                    const hasMatch = this.ordenes.some(orden => 
                        orden.status.estado === proceso && this.matchesSearch(orden)
                    );
                    if (hasMatch) return false;
                }

                // On mobile, if not explicitly set, it's collapsed (true)
                if (this.collapsedSections[proceso] === undefined) return true;
                return this.collapsedSections[proceso] === true;
            },
            handleScroll: function (evt, el) {
                const myElement = document.getElementById('contestatuspro');
                // Clean up old scroll logic if needed, or leave it if it serves another purpose. 
                // The prompt suggests there was a syntax error due to truncated replace. 
                // Restoring valid syntax for handleScroll.
                if(!myElement) return;
                const rect = myElement.getBoundingClientRect();
                const vtop = rect.top + window.scrollY;
                if (window.scrollY < vtop) {
                    this.scroll=window.scrollY
                }else{
                    this.scroll=window.scrollY-100
                }
                
                // Call our new handler
                this.handleToolbarScroll();
            },
             handleScroll2: function (evt, el) {
                const encabezado = document.getElementById('ordencards');
                if (!encabezado) return;
                const rect = encabezado.getBoundingClientRect();
                const h = encabezado.clientHeight
                const vtop = rect.top + window.scrollY;
                // alert(window.scrollY+' - '+h+' - '+rect.top)
                if(window.scrollY>h){
                    this.scroll2=window.scrollY-90
                    encabezado.style.position = "absolute";
                    
                }else{
                    encabezado.style.position = "relative";
                    this.scroll2=0
                }
                // if (window.scrollY < vtop) {
                //     this.scroll=window.scrollY
                // }else{
                //     this.scroll=window.scrollY-100
                // }
            },
            bajarEstado(estado) {
                // Open the section if it was collapsed (mobile only)
                if (window.innerWidth < 768) {
                    this.$set(this.collapsedSections, estado, false);
                }
                
                const elemento = document.getElementById(estado);
                if (elemento) {
                    const y = elemento.getBoundingClientRect().top + window.pageYOffset - 170; // Adjusted offset for better view
                    window.scrollTo({
                        top: y,
                        behavior: 'smooth'
                    });
                }
            },
            checkVisibility() {
                const elementos = this.$refs.elementos;
                if (!elementos || !Array.isArray(elementos)) return;
                const viewportHeight = window.innerHeight;

                elementos.forEach(elemento => {
                    const rect = elemento.getBoundingClientRect();
                    // console.log(rect.top,viewportHeight, rect.bottom)
                    // // Verificamos si el elemento está centrado en la pantalla
                    // if (rect.top < viewportHeight / 50 || rect.bottom > viewportHeight) {
                    // elemento.style.transform = 'scale(0.8)'; // Reduce opacidad si no está centrado
                    // } else {
                    // elemento.style.transform = 'scale(1)'; // Restaura opacidad si está centrado
                    // }
                });
            },
            dragStart(item) {
            this.draggedItem = item;
            },
            async handleDrop(event) {
                event.preventDefault();
                // Get the container ID (the process name)
                const container = event.currentTarget;
                const processName = container.id;
                
                if (!processName || this.draggedItem === null) {
                    console.warn("Invalid drop attempt:", { processName, draggedItem: this.draggedItem });
                    return;
                }
                
                const orden = this.ordenes[this.draggedItem];
                if (!orden) return;
                
                const currentStatus = orden.status && orden.status.estado ? orden.status.estado : 'Sin Estado';
                
                if (currentStatus !== processName) {
                    console.log(`Dragging order #${orden.id} from ${currentStatus} to ${processName} (end of list)`);
                    const success = await this.actualizarEstadoOrden(orden, processName);
                    if (!success) {
                        this.draggedItem = null;
                        return;
                    }
                }
                
                // Move order to the very end of the target process column
                const droppedItem = this.ordenes.splice(this.draggedItem, 1)[0];
                
                let lastIndex = -1;
                for (let idx = this.ordenes.length - 1; idx >= 0; idx--) {
                    if (this.ordenes[idx].status && this.ordenes[idx].status.estado === processName) {
                        lastIndex = idx;
                        break;
                    }
                }
                
                if (lastIndex !== -1) {
                    this.ordenes.splice(lastIndex + 1, 0, droppedItem);
                } else {
                    this.ordenes.push(droppedItem);
                }
                
                // Re-assign priorities for processName
                let i = 1;
                this.ordenes.forEach(e => {
                    if (e.status && e.status.estado === processName) {
                        e.status.prioridad = i;
                        i++;
                    }
                });
                
                var me = this;
                var or = me.ordenes;
                axios.put('/statuspro/cambiarPrioridad', {
                    'ordenes': or,
                    'id': or.id,
                })
                .then(function (response) {
                    console.log("Prioridades actualizadas (al final)", response);
                })
                .catch(function (error) {
                    console.log(error);
                });
                
                this.draggedItem = null;
            },
            async actualizarEstadoOrden(orden, nuevoEstado, skipAuth = false) {
                const me = this;
                const estadoAnterior = orden.status.estado;
                const statusId = orden.status.id;
                
                // --- Security & Process Control ---
                let indexActual = this.getIndexProceso(estadoAnterior);
                let indexNuevo  = this.getIndexProceso(nuevoEstado);
                let indexControl = this.getIndexProceso('Corte material');

                // If moving from a restricted zone (after indexControl) to a prior zone (at/before indexControl), require auth
                if (!skipAuth && !this.auth && indexActual !== -1 && indexNuevo !== -1 && indexControl !== -1) {
                    if (indexActual > indexControl && indexNuevo <= indexControl) {
                        this.pendienteCambio = { orden, nuevoEstado };
                        this.mostrarModal = true;
                        return false;
                    }
                }

                // Determine movement type (entrada/salida) for inventory
                let tipo = ''; 
                if (indexActual !== -1 && indexNuevo !== -1 && indexControl !== -1) {
                    if (indexActual < indexNuevo && indexNuevo > indexControl) {
                        tipo = 'salida';
                    } else if (indexActual > indexNuevo && indexNuevo <= indexControl) {
                        tipo = 'entrada';
                    }
                }

                let costois_id = 0;
                let cantidad = 0;
                
                // Try to find inventory data if it's the 'Papel' cost
                if (Array.isArray(orden.detalles)) {
                    orden.detalles.forEach(e => {
                        if ((e.titulo === 'Papel' || e.titulo === 'papel') && e.costo && e.costo.cantidad !== undefined && e.costo.cantidad !== null) {
                            costois_id = e.costo.costois_id || 0;
                            let numCant = parseFloat(e.costo.cantidad);
                            cantidad = !isNaN(numCant) ? numCant.toFixed(0) : (e.costo.cantidad || 0);
                        }
                    });
                }

                // Optimistic UI update
                orden.status.estado = nuevoEstado;

                try {
                    await axios.put(me.getUrl('/statuspro/cambiarEstado'), {
                        id: statusId,
                        orden_id: orden.id,
                        estado: nuevoEstado,
                        costois_id: costois_id,
                        cantidad: cantidad,
                        cantidad_final: 0,
                        observaciones: 'Cambio vía Drag & Drop' + (this.auth ? ' (Autorizado)' : ''),
                        tipo: tipo,
                        operario: me.user ? me.user.id : null, 
                        estadoactual: estadoAnterior
                    });
                    
                    Swal.fire({
                        title: '¡Estado Actualizado!',
                        text: `Orden #${orden.id} movida a ${nuevoEstado}`,
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Reset auth for next operation
                    this.auth = false;
                    this.mostrarModal = null;
                    
                    // Trigger refresh but locally
                    me.refrescar('local');
                    return true;
                } catch (error) {
                    console.error('Error updating status:', error);
                    orden.status.estado = estadoAnterior; // Revert
                    this.auth = false;
                    Swal.fire('Error', 'No se pudo actualizar el estado de la orden', 'error');
                    return false;
                }
            },
            getIndexProceso(nombreProceso) {
                if (!nombreProceso || !this.proc) return -1;
                return this.proc.findIndex(p => p.proceso.trim() === nombreProceso.toString().trim());
            },
            onAutorizado() {
                this.auth = true;
                this.mostrarModal = null;
                if (this.pendienteCambio) {
                    const { orden, nuevoEstado } = this.pendienteCambio;
                    this.actualizarEstadoOrden(orden, nuevoEstado, true);
                    this.pendienteCambio = null;
                }
            },
            async onDrop(index) {
                if (this.draggedItem === null || this.draggedItem === index) return;

                const draggedCard = this.ordenes[this.draggedItem];
                const targetCard = this.ordenes[index];
                
                if (!draggedCard || !targetCard) return;
                
                const targetStatus = targetCard.status.estado;
                const sourceStatus = draggedCard.status.estado;
                
                if (sourceStatus !== targetStatus) {
                    const success = await this.actualizarEstadoOrden(draggedCard, targetStatus);
                    if (!success) {
                        this.draggedItem = null;
                        return;
                    }
                }
                
                this.droppedItems = this.ordenes.splice(this.draggedItem, 1)[0];
                let targetIndex = index;
                this.ordenes.splice(targetIndex, 0, this.droppedItems);
                this.droppedItems = null;
                
                let i = 1;
                this.ordenes.forEach(e => {
                    if (e.status && e.status.estado === targetStatus) {
                        e.status.prioridad = i;
                        i++;
                    }
                });
                
                var me = this;
                var or = me.ordenes;
                axios.put('/statuspro/cambiarPrioridad', {
                    'ordenes': or,
                    'id': or.id,
                })
                .then(function (response) {
                    console.log("Prioridades actualizadas", response);
                })
                .catch(function (error) {
                    console.log(error);
                });
                
                this.draggedItem = null;
            },              

            refrescar(origen = null){
                if (origen === 'local') {
                    // Update the timestamp immediately so the next poll doesn't reload
                    this.checkForUpdates(true); 
                } else {
                    this.listarordenes();
                }
            },
            matchesSearch(orden){
                if (!this.buscar || this.buscar.toString().trim() === '') return true;
                let term = this.buscar.toString().toLowerCase().trim();
                let cliente = (orden.cliente && orden.cliente.razonsocial) ? orden.cliente.razonsocial.toLowerCase() : '';
                let numComprobante = (orden.pedido) ? String(orden.pedido.num_comprobante || orden.pedido.id).toLowerCase() : '';
                let numOrden = orden.num_orden ? String(orden.num_orden).toLowerCase() : String(orden.id).toLowerCase();
                let producto = (orden.articulo && orden.articulo.nombre) ? orden.articulo.nombre.toLowerCase() : '';
                let papel = orden.papeles ? orden.papeles.toLowerCase() : '';

                return cliente.includes(term) || numComprobante.includes(term) || numOrden.includes(term) || producto.includes(term) || papel.includes(term);
            },
            ordenarOrdenes(array,getter,order){
                array.sort((a,b)=>{
                    const firts=getter(a)
                    const second=getter(b)
                    const compare =firts.localeCompare(second)
                    return order==='asc'? compare : -compare
                })
                return array
            },
            getActivos(){
                let me=this;
                var act={activo:'Sin Activo',id:0}
                var url= me.getUrl('/activo/activos');
                
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.activos=respuesta;
                    if(me.activos['Troquelado']) me.activos['Troquelado'].push(act)
                    if(me.activos['Terminado']) me.activos['Terminado'].push(act)
                    
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            getProcesos(){
                let me=this;
                var url= me.getUrl('/statuspro/procesos');
                
                axios.get(url).then(function (response) {
                    console.log(response);
                    me.proc = response.data;
                })
            },
            listarordenes(){
            let me=this;
            // Cache buster for mobile devices
            var url= me.getUrl('/statuspro?_=' + Date.now());
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                me.ordenes = respuesta.sort((a, b) => {
                    // 1. VIP Orders get top priority #1
                    let isVipA = (a.prioridad && String(a.prioridad).toLowerCase() === 'vip');
                    let isVipB = (b.prioridad && String(b.prioridad).toLowerCase() === 'vip');

                    if (isVipA && !isVipB) return -1;
                    if (!isVipA && isVipB) return 1;

                    // 2. Ordenar por prioridad de status
                    const statusA = (a.status && a.status.length > 0) ? a.status[0] : { prioridad: 999, fecha_entrega: '2099-12-31' };
                    const statusB = (b.status && b.status.length > 0) ? b.status[0] : { prioridad: 999, fecha_entrega: '2099-12-31' };

                    const prioridad = statusA.prioridad - statusB.prioridad;
                    if (prioridad !== 0) return prioridad;

                    // Si la prioridad es igual, ordenar por fechaEntrega
                    return new Date(b.fecha_entrega) - new Date(a.fecha_entrega);
                });
                me.ordenes.forEach(o => {
                    var nombrepapel=''
                    if(!o.maquina){
                        o.maquina={uso:''}
                    }
                    if(!Array.isArray(o.troquelado) || o.troquelado.length==0){
                        o.troquelado=[{costois_id:0}]
                    }
                    if(!Array.isArray(o.terminado) || o.terminado.length==0){
                        o.terminado=[{costois_id:0}]
                    }
                    if(!Array.isArray(o.costos)){
                        o.costos = []
                    }
                    if(!Array.isArray(o.detalles)){
                        o.detalles = []
                    }
                   
                    if(Array.isArray(o.papel)){
                        o.papel.forEach(p => {
                            if(p && p.costois!=null){
                                nombrepapel=p.costois.nombre
                            }
                        })
                    }else if(o.papel && typeof o.papel === 'object'){
                        if(o.papel.costois!=null){
                            nombrepapel=o.papel.costois.nombre
                            o.papel.nombre=nombrepapel
                        }
                    }
                    
                    if(o.status && Array.isArray(o.status) && o.status.length > 0) {
                        o.status = o.status[0]
                    } else if (!o.status || typeof o.status !== 'object') {
                        o.status = { estado: 'Sin Estado', prioridad: 999, id:0 }
                    }
                    let pliegos = 0
                    let tamanos = 0
                    let tinta = ''
                    let papelList = []
                    o.costos.forEach(e => {
                        if (e && (e.titulo == 'Papel' || e.titulo == 'papel')) {
                            let nPapel = (e.costois && e.costois.nombre) ? e.costois.nombre : (e.descripcion || nombrepapel || 'Papel')
                            let cMaterial = e.medida_material || o.medida_material || 'N/A'
                            let compLabel = e.componente ? ` [${e.componente}]` : ''
                            papelList.push(`${nPapel}${compLabel}: ${e.cantidad} Pliegos, Corte: ${cMaterial}`)
                            pliegos += parseInt(e.cantidad || 0)
                            tamanos += (parseInt(e.descripcion || 0) - parseInt(o.carpeta_cliente || 0))
                        }
                    });
                    o.detalles.forEach(e => {
                        if(e && (e.titulo=='tinta' || e.titulo =='impresion' || e.titulo=='Tinta' || e.titulo=='TINTA' || e.titulo=='Impresion')){
                            tinta = typeof e.valor === 'string' ? e.valor : (Array.isArray(e.valor) ? e.valor.map(v => v.pantone || v).join(', ') : '')
                        }
                    });
                    
                    if(o.papel && typeof o.papel === 'object') {
                        o.papel.costois_id = 0
                    }
                    o.tinta = tinta
                    o.pliegos = pliegos
                    o.tamanos = tamanos
                    o.papeles = papelList.length > 0 ? papelList.join(' | ') : `${nombrepapel || 'Papel'}, ${pliegos} Pliegos, Corte: ${o.medida_material || 'N/A'}`
                    
                });
                var papel={"id":0,"ordentrabajo_id":0,"costois_id":0,"titulo":"Papel","descripcion":0,"cantidad":0,"valor":"0","total":"0.00","orden":1,"completado":0,"pago":0,"fecha_termina":"","terminado":0,"costois":{"id":0,"idproveedor":0,"idpersona":0,"tipo_costo":"Papel","nombre":"","cabida":"pliego","valor":"","total":"0.00","estado":"1"}}
                me.agrupadoPorUso = me.ordenes.reduce((acc, orden) => {
                    const uso = orden.maquina.uso;

                    if (!acc[uso]) {
                        acc[uso] = [];
                    }
                    acc[uso].push(orden);
                    return acc;
                }, {});
            
                        
                
            })
            .catch(function (error) {
                console.log(error);
            });
          
        },
        flujo(){
             const data = new FormData()
            data.set('data',JSON.stringify(this.ordenes))
             axios.post('/statuspro/CrearFlujoInicial',data)
            .then(function (response) {
                console.log(response)
            }).catch(function (error) {
                console.log(error);
            });
        },
        actualizarColor() {
           const spinner = this.$refs.contador;

            const total = 50; // el máximo de segundos
            const restante = this.contador;

            // Calcular porcentaje (1 = verde, 0 = rojo)
            const p = restante / total;

            // Cálculo del color RGB de manera suave
            const r = Math.round(255 * (1 - p));  // aumenta hacia rojo
            const g = Math.round(255 * p);        // disminuye desde verde
            const b = 0;

            spinner.style.color = `rgb(${r}, ${g}, ${b})`;
        },
        checkForUpdates(skipReload = false) {
            let me = this;
            if (me.editingMode) return Promise.resolve();
            
            me.syncing = true;
            // Cache buster for mobile browsers (especially iOS/Safari)
            return axios.get('/statuspro/checkUpdates', { params: { _: Date.now() } }).then(response => {
                let currentUpdate = response.data.last_update;
                
                // Actualizamos la hora de última conexión para que el usuario sepa que funciona
                let now = new Date();
                me.lastSyncTime = now.getHours() + ":" + now.getMinutes().toString().padStart(2, '0') + ":" + now.getSeconds().toString().padStart(2, '0');

                if (me.lastUpdate === null) {
                    me.lastUpdate = currentUpdate;
                    return;
                }

                if (currentUpdate !== me.lastUpdate) {
                    me.lastUpdate = currentUpdate;
                    if (!skipReload) {
                        console.log('Change detected in DB! Refreshing orders...');
                        me.listarordenes();
                    }
                }
            }).catch(error => {
                console.error("Sync error:", error);
                if (error.response) {
                    me.lastSyncTime = "Error " + error.response.status;
                } else {
                    me.lastSyncTime = "Error Net";
                }
            }).finally(() => {
                setTimeout(() => { me.syncing = false; }, 800);
            });
        },
        iniciarRefresh() {
            this.programarSiguienteUpdate();
        },
        programarSiguienteUpdate() {
            if (this.intervalo) clearTimeout(this.intervalo);
            this.intervalo = setTimeout(() => {
                this.checkForUpdates().finally(() => {
                    this.programarSiguienteUpdate();
                });
            }, 5000); 
        },
        imprimirStatus(estado) {
            window.open('/statuspro/imprimir-status/' + encodeURIComponent(estado), '_blank');
        }
        },
        mounted() {
            this.listarordenes()
            this.getProcesos()
            this.getActivos()
            this.iniciarRefresh()
            
            window.addEventListener('scroll', this.checkVisibility); 
            window.addEventListener('resize', this.checkVisibility); 
            this.checkVisibility(); 
        },
        beforeDestroy() {
            if (this.intervalo) clearTimeout(this.intervalo);
            if (this.intervaloContador) clearInterval(this.intervaloContador);
            window.removeEventListener('scroll', this.checkVisibility);
            window.removeEventListener('resize', this.checkVisibility);
        }
    }
</script>
<style>  
.elemento{
    transition: transform 0.3s ease-out; /* Transición suave para el cambio de tamaño */
}

/* Pulsing Live Dot */
.pulse-dot {
    width: 8px;
    height: 8px;
    background-color: #28a745;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

/* Spinner for syncing state */
.sync-spinner {
    width: 8px;
    height: 8px;
    border: 2px solid #1985ac;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.noArea{
    width:30px;
}
.noArea .orden{
    transform: scale(0.3);
}
.noArea .orden .color{
    transform: scale(2);
}
.draggable {
  width: 150px;
  margin: 10px;
  padding: 10px;
  background-color: lightblue;
  cursor: grab;
  border: 1px solid #000;
  text-align: center;
}
.girador{
    display: inline-block;
    animation: girar 1s linear infinite;
    font-size: 20px;
    padding: 0 12px;
}
@keyframes girar {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.droppable {
  width: 200px;
  height: 200px;
  margin-top: 20px;
  background-color: lightgray;
  border: 2px dashed #000;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
    .ordencards{
        display:flex;
        flex-direction: column;
        justify-content: space-between;
        flex-wrap: wrap;
        position: relative;
       
        /* overflow-x: scroll; */

    }  
    #ordencards{
        background: #fff;
        width: 100%;
        box-shadow: 0px 5px 5px rgba(0, 0, 0, 0.3);
    } 
    .maquina{
        padding:1px 10px;
        cursor:pointer;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        
        
    }   
    
    .ordencard{
       
        padding:1px 10px;
        cursor:pointer;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }
    .maquina .ordencard{
        padding:5px 5px;
    }
    .maquina .contenedor-seccion{
        display: flex;
        flex-direction: column;
    }
    .ordenes{
        display:flex;
        flex-direction: row;
        flex:1;
        margin:0 5px;
        background: rgb(247, 247, 247);
        margin:0 0 3px 0;
        width:100%;
        min-height: 50px;
    }

    .content-seccion {
        width: 100%;
    }

    @media (min-width: 1025px) {
        .content-seccion {
            flex: 1;
            width: auto;
        }
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns */
        gap: 5px;
        width: 100%;
        padding: 5px;
    }

    .grid-item {
        width: 100%;
        min-width: 0; /* Allow shrinking */
    }

    @media (max-width: 1024px) {
        .ordenes {
            flex-direction: column;
        }
        .ordenes > h4 {
            width: 100% !important;
            padding: 12px !important;
            justify-content: flex-start !important;
            border-bottom: 2px solid #1985ac !important;
            border-top: none !important;
            margin-bottom: 0px !important;
        }
        .maquina, .ordencard {
            padding: 2px !important;
        }
    }

    @media (max-width: 500px) {
        .grid-container {
            grid-template-columns: repeat(2, 1fr); /* Stay 2 columns even on very small */
            gap: 3px;
            padding: 2px;
        }
    }

    @media (min-width: 1025px) {
        .grid-container {
            display: flex; /* Back to flex for desktop horizontal flow */
            flex-wrap: wrap;
        }
        .grid-item {
            width: auto;
        }
    }

    .btn-estados{
        background: #1985ac;
        color:#fff;
        font-size: 14px;
    }
    @media (min-width: 1025px) {
        .ordenes {
            display: flex !important;
            flex-direction: row !important;
            flex: 1 !important;
            margin: 0 0 5px 0 !important;
            background: #fdfdfd !important;
            width: 100% !important;
            min-height: 80px !important;
            align-items: stretch !important; /* Forces header and content to same height */
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .ordenes > h4 {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            width: 120px !important;
            flex-shrink: 0 !important;
            background: linear-gradient(180deg, #1985ac 0%, #136988 100%) !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            padding: 5px 10px !important;
            border: none !important;
            border-right: 2px solid rgba(0,0,0,0.1) !important;
            border-radius: 0 !important;
            text-align: center !important;
            line-height: 1.1 !important;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
            font-size: 13px !important;
            text-transform: uppercase;
            white-space: normal !important; /* Forces text to wrap - Fixes the overflow */
            word-break: break-word !important;
            hyphens: auto;
        }

        .ordenes > h4:hover {
            filter: brightness(1.1);
        }

        .ordenes > h4 span {
            width: 100%;
            display: block;
            padding: 0 5px;
        }

        .content-seccion {
            flex: 1 !important;
            width: auto !important;
            padding: 10px !important;
            background: #fff;
        }
    }

    @media (max-width: 768px) {
        #contestatuspro {
            padding: 5px !important;
        }
        #ordencards .card-body {
            padding: 10px 5px !important;
        }
        #ordencards .d-flex.flex-wrap {
            flex-direction: row !important; /* Allow some horizontal flow */
            justify-content: flex-start !important;
        }
        .badge-custom {
            padding: 3px 8px !important;
            font-size: 0.65rem !important;
            margin-bottom: 4px !important;
        }
        .refresh-timer, .search-container, .sort-buttons {
            width: 100% !important;
            margin: 4px 0 !important;
            justify-content: flex-start !important;
        }
        .sort-buttons .btn-group {
            width: 100%;
            display: flex;
        }
        .sort-buttons .btn-group button {
            flex: 1;
            font-size: 0.7rem !important;
            padding: 5px 2px !important;
        }
        .btn-process {
            flex: 1 1 45%; /* Two buttons per row roughly */
            margin: 2px !important;
            font-size: 0.75rem !important;
        }
    }

    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

    /* New Styles for Refactored Header */
    .sticky-toolbar {
        position: -webkit-sticky;
        position: sticky;
        top: 60px; /* Adjusted to clear navbar */
        z-index: 999;
        backdrop-filter: blur(5px);
        background: rgba(255, 255, 255, 0.98) !important;
        border-bottom: 1px solid #dee2e6; /* Reverted to standard */
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    
    .badge-custom {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-right: 5px;
        color: #333;
        display: inline-block;
        margin-bottom: 2px;
    }
    
    /* Use existing colors but with better styling */
    .diseno { background:#e4d6b7 !important; color: #5a4b2b; }
    .enviarp { background:#d9e4b7 !important; color: #4b522b; }
    .enproduccion { background:#b7e4c1 !important; color: #2b5235; }
    .empacado { background: #b7e4e2 !important; color: #2b4e52; }
    .entregar { background:#e3b7e4 !important; color: #522b53; }
    
    .btn-white {
        background: #fff;
        color: #555;
        font-weight: 500;
        border: 1px solid #ced4da;
    }
    
    .btn-white:hover {
        background: #f8f9fa;
        color: #333;
    }

    .sync-badge {
        transition: all 0.3s ease;
        border: 1px solid transparent;
        height: 28px;
    }
    .syncing-active {
        background: rgba(25, 133, 172, 0.1);
        border-color: rgba(25, 133, 172, 0.2);
    }
    .syncing-active .badge-title { color: #1985ac; }
    
    .syncing-idle {
        background: rgba(40, 167, 69, 0.1);
        border-color: rgba(40, 167, 69, 0.2);
    }
    .syncing-idle .badge-title { color: #28a745; }

    .shadow-sm-premium {
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-radius: 8px;
        overflow: hidden;
    }

    .badge-custom-sm {
        padding: 1px 5px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 700;
        margin-right: 3px;
        color: #333;
        display: inline-block;
        margin-bottom: 2px;
    }

    .m-0-5 {
        margin: 2px !important;
    }

    /* Fixed/Sticky Toolbar */
    .fixed-toolbar {
        position: fixed;
        z-index: 999;
        margin: 0 !important;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from { transform: translateY(-100%); }
        to { transform: translateY(0); }
    }

    .card-body {
        padding: 0.25rem;
    }

    /* Estilos para navegación rápida dentro del sticky toolbar */
    .quick-nav-glass {
        background: rgba(248, 250, 252, 0.8);
        backdrop-filter: blur(4px);
        padding: 2px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        display: flex;
        gap: 5px;
    }

    .btn-quick-nav-sm {
        border: none;
        padding: 4px 10px;
        border-radius: 7px;
        font-weight: 800;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        transition: all 0.2s ease;
        color: white;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .nav-completados {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    }

    .nav-entregar {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .btn-quick-nav-sm:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
        color: white;
    }
</style>

