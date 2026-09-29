<template>

    <div class="card-body" id="contestatuspro" v-scroll="handleScroll">
        <div class="form-group">
            <div class="ordencards"  v-scroll="handleScroll2">
                <div id="ordencards" :style="'top:'+scroll2+'px'">
                    <!-- <div
                        class="draggable"
                        v-for="(item, index) in items"
                        :key="index"
                        draggable="true"
                        @dragstart="dragStart(item)"
                        @dragover.prevent
                        >
                        {{ item }}
                        </div> -->

                        <!-- <div
                        class="droppable"
                        @dragover.prevent
                        @drop="onDrop"
                        >
                        Suelta aquí
                        <div v-for="(item, index) in droppedItems" :key="index">
                        {{ item}}
                        </div>
                        </div> -->
                    <label class="tituloinfoproduccion">Colores del estado de producción de las ordenes</label>  
                    
                    <ul class="infoproduccion">
                        <li class="diseno">Diseño</li>
                        <li class="enviarp">Enviar a producción</li>
                        <li class="enproduccion">En producción</li>
                        <li class="empacado">Empacado</li>
                        <li class="entregar">Para entregar</li>
                    </ul>
                    <button @click="ordenarOrdenes(ordenes,orden=>orden.articulo.nombre,'asc')">Ordenar x producto</button>
                    <button @click="ordenarOrdenes(ordenes,orden=>orden.cliente.razonsocial,'asc')">Ordenar x Cliente</button>
                    <button @click="ordenarOrdenes(ordenes,orden=>orden.papeles,'asc')">Ordenar x Papel</button>
                    <button @click="ordenarOrdenes(ordenes,orden=>orden.fecha_entrega,'asc')">Ordenar x Fecha de entrega</button>
                    <div style="font-size:20px; font-weight:bold;" id="contador">
                        <span class="girador" style="">⟳</span> Refrescando en {{ contador }} segundos
                    </div>
                </div>
                <div v-for="(proceso, ind) in proc" :key="ind" :id="ind" class="ordenes elemento" @dragover.prevent
                @drop="handleDrop"  ref="elementos">
                    <h4 class="btn btn-primary font-xl ">{{proceso.proceso}}</h4>
                    <div v-if="ind==2" class="maquina ">
                        <div v-for="(uso,i) in agrupadoPorUso" :key="i" class="contenedor-seccion" >
                            <h5 v-if="i==''" class="btn boton-principal">Sin plancha</h5>
                            <h5 v-else class="btn boton-principal">{{ i }}</h5>
                            <div class="ordencard">
                                <div v-for="(orden, index) in ordenes" :key="orden.id"  draggable="true"
                                    @dragstart="dragStart(index)"
                                    @dragover.prevent 
                                    @drop="onDrop(index)">
                                    <div  v-if="orden.status.estado==ind && orden.maquina.uso==i" >
                                        <ordencard  :scroll="scroll" :status="ind" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" 
                                        > </ordencard>
                                    </div>
                                    
                                </div>
                            </div>
                        
                        </div>
                    </div>
                    <div v-else-if="ind==5" class="maquina ">
                       
                        <div v-for="(activo,i) in activos[ind]" :key="i" class="contenedor-seccion"  >
                            <h5 v-if="activo.id==0" class="btn boton-principal">{{activo.activo}}</h5>
                            <h5 v-else class="btn boton-principal">{{ activo.activo }}</h5>
                            <div class="ordencard">
                                <div v-for="(orden, index) in ordenes" :key="orden.id"  draggable="true"
                                    @dragstart="dragStart(index)"
                                    @dragover.prevent 
                                    @drop="onDrop(index)">
                                    <div v-if="orden.status.estado==ind && activo.id==orden.troquelado[0].costois_id" >
                                        <ordencard  :scroll="scroll" :status="ind" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" 
                                        > </ordencard>
                                    </div>
                                    
                                </div>
                            </div>
                        
                        </div>
                    </div>
                    <div v-else-if="ind==6" class="maquina ">
                        
                        <!-- Sección especial de órdenes compartidas/divididas -->
                        <div class="contenedor-seccion w-100 mb-3 border border-warning rounded p-2" 
                             style="background-color: #fffdf5; border-width: 2px !important; flex: 1 1 100%;" 
                             v-if="ordenes.some(o => o.status.estado == ind && o.terminado && o.terminado.length > 1)">
                            <h5 class="btn btn-warning text-dark font-weight-bold w-100 text-left mb-2 shadow-sm" style="cursor: default; background-color: #ffc107 !important; border-color: #ffc107 !important;">
                                <i class="fa fa-users"></i> Trabajos Compartidos (Órdenes Divididas)
                            </h5>
                            <div class="ordencard">
                                <div v-for="(orden, index) in ordenes" :key="orden.id" draggable="true"
                                     @dragstart="dragStart(index)"
                                     @dragover.prevent 
                                     @drop="onDrop(index)">
                                    <div v-if="orden.status.estado==ind && orden.terminado && orden.terminado.length > 1">
                                        <ordencard :scroll="scroll" :status="ind" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar"> 
                                        </ordencard>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de operarias individuales -->
                        <div v-for="(activo,i) in activos[ind]" :key="i" class="contenedor-seccion"  >
                            <h5 v-if="activo.id==0" class="btn boton-principal">{{activo.activo}}</h5>
                            <h5 v-else class="btn boton-principal">{{ activo.activo }}</h5>
                            <div class="ordencard">
                                <div v-for="(orden, index) in ordenes" :key="orden.id"  draggable="true"
                                    @dragstart="dragStart(index)"
                                    @dragover.prevent 
                                    @drop="onDrop(index)">
                                    <div v-if="orden.status.estado==ind && orden.terminado && orden.terminado.some(t => t.costois_id == activo.id)" >
                                        <ordencard  :scroll="scroll" :status="ind" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" 
                                        > </ordencard>
                                    </div>
                                    
                                </div>
                            </div>
                        
                        </div>
                    </div>
                    <div v-else class="ordencard" >

                        <div v-for="(orden, index) in ordenes" :key="orden.id"  draggable="true"
                            @dragstart="dragStart(index)"
                            @dragover.prevent 
                            @drop="onDrop(index)">
                            <div v-if="orden.status.estado==ind">
                                <ordencard  :scroll="scroll" :status="ind" :orden="orden" :user="user" :procesos="proc" @refrescar="refrescar" 
                                > </ordencard>
                            </div>
                            
                        </div>
                    </div>
                      
                   
                </div>
                
                
            </div>
        </div>
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
                proc:[  
                        {'proceso': 'Espera', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso': 'Corte material', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso': 'Impresión', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso':'Transito', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso':'Acabado', 'cantidad':0, 'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso':'Troquelado', 'cantidad':0, 'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso':'Terminado', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso':'Empacado', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`},
                        {'proceso':'Para entragar', 'cantidad':0,'fechaTermina':`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,'hora':`${new Date().getHours()}-${new Date().getMinutes()}`}
                    ],
                    agrupadoPorUso:[],
                    activos:[]
            }
        },
        components: {
            ordencard
        },
        computed:{
            
           

        },
        methods : {
            handleScroll: function (evt, el) {
                const myElement = document.getElementById('contestatuspro');
                if (!myElement) return;
                const rect = myElement.getBoundingClientRect();
                const vtop = rect.top + window.scrollY;
                if (window.scrollY < vtop) {
                    this.scroll=window.scrollY
                }else{
                    this.scroll=window.scrollY-100
                }
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
            handleDrop(event) {
                const droppedOnContainer = event.target; // Contenedor donde se suelta el elemento
                console.log("Dropped on:", event); // Muestra el ID del contenedor
            },
            onDrop(index) {
                this.droppedItems =this.ordenes.splice(this.draggedItem,1)[0]
                this.ordenes.splice(index,0,this.droppedItems)
                this.droppedItems=null
                var i=1
                this.ordenes.forEach(e=>{
                    if(e.status.estado==this.ordenes[index].status.estado){
                        e.status.prioridad=i
                        i=i+1
                    }

                })
                var me=this
                var or=me.ordenes
                axios.put('/statuspro/cambiarPrioridad',{
                    'ordenes':or,
                    'id':or.id,
                })
                    .then(function (response) {
                        console.log(response)
                    
                }).catch(function (error) {
                    console.log(error);
                });
                

                    
            },
            refrescar(){
                this.listarordenes()
                // this.$refs.pedidosentregar.listarPedidos
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
                var url= '/activo/activos';
                
                axios.get(url).then(function (response) {
                    let respuesta = response.data;
                    me.activos=respuesta;
                    me.activos[5].push(act)
                    me.activos[6].push(act)
                    
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            
        listarordenes() {
            let me = this;
            var url = '/statuspro';

            axios.get(url)
                .then(function (response) {

                    let respuesta = response.data;

                    // ORDENAR: Primero por prioridad y luego por fecha de entrega
                   me.ordenes = respuesta.sort((a, b) => {

                        const prioridadA = a.status && a.status.length ? a.status[0].prioridad : 9999;
                        const prioridadB = b.status && b.status.length ? b.status[0].prioridad : 9999;

                        if (prioridadA !== prioridadB) {
                            return prioridadA - prioridadB;
                        }

                        const horaA = a.status && a.status.length ? a.status[0].hora : '99:99';
                        const horaB = b.status && b.status.length ? b.status[0].hora : '99:99';

                        return horaA.localeCompare(horaB);
                    });

                    // RECORRER ORDENES PARA AJUSTAR DATA
                    me.ordenes.forEach(o => {

                        // -----------------------------------
                        // Asegurar que existan propiedades
                        // -----------------------------------
                        if (!o.maquina) o.maquina = { uso: '' };
                        if (!o.troquelado || !o.troquelado.length) {
    o.troquelado = [{ costois_id: 0 }];
}

if (!o.terminado || !o.terminado.length) {
    o.terminado = [{ costois_id: 0 }];
}


                        // STATUS debe ser un solo objeto, no array
                        o.status = o.status[0];

                        // -----------------------------------
                        // PROCESAR PAPEL
                        // -----------------------------------
                        let nombrepapel = '';

                        if (o.papel && Array.isArray(o.papel)) {
    // Si papel viene como array, tomamos el primer elemento
    o.papel.forEach(function(p) {
        if (p.costois && p.costois.nombre) {
            nombrepapel = p.costois.nombre;
        }
    });

} else if (o.papel && o.papel.costois) {
    nombrepapel = o.papel.costois.nombre;
}

                        // -----------------------------------
                        // EXTRAER PLIEGOS, CORTE Y TAMAÑOS
                        // -----------------------------------
                        let pliegos = 0;
                        let tamanos = 0;

                        o.costos.forEach(e => {
                            if (e.titulo === 'Papel') {
                                pliegos = e.cantidad;
                                tamanos = parseInt(e.descripcion) - parseInt(o.carpeta_cliente);
                            }
                        });

                        // -----------------------------------
                        // EXTRAER TINTA
                        // -----------------------------------
                        let tinta = '';
                        o.detalles.forEach(e => {
                            if (
                                ['tinta', 'impresion', 'Tinta', 'TINTA', 'Impresion']
                                    .includes(e.titulo)
                            ) {
                                tinta = e.valor;
                            }
                        });

                        // -----------------------------------
                        // ARMAR TEXTO DE PAPEL PARA FILTROS
                        // -----------------------------------
                        o.papeles = `${nombrepapel}, ${pliegos} Pliegos, Corte: ${o.medida_material}`;

                        // -----------------------------------
                        // GUARDAR EN OBJETO FINAL
                        // -----------------------------------
                        o.pliegos = pliegos;
                        o.tamanos = tamanos;
                        o.tinta = tinta;
                        o.papel.costois_id = 0;
                    });

                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        actualizarColor() {
           const spinner = document.getElementById("contador");

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
        iniciarRefresh() {
        // Reiniciar contador

            // Intervalo del refresco
            this.intervalo = setInterval(() => {
                this.listarordenes();
               
                this.contador = 50; // reinicia el contador cuando refresca
            }, this.contador*1000);

            // Intervalo del contador visible
            this.intervaloContador = setInterval(() => {
                if (this.contador > 0) {
                    this.contador--;
                     this.actualizarColor();
                }
            }, 1000);
        },
            
         
          
        },
        mounted() {
            window.addEventListener('scroll', this.checkVisibility); // Escuchar el evento de desplazamiento
            window.addEventListener('resize', this.checkVisibility); // Escuchar cambios en el tamaño de la ventana
            this.checkVisibility(); // Verificar la visibilidad al montar el componente
            this.listarordenes()
            this.getActivos()
            this.iniciarRefresh()
            // this.intervalo = setInterval(() => {
            // this.listarordenes(); // Llamada periódica cada 5 segundos
            // }, 80000);
           
        }
    }
</script>
<style>  
.elemento{
    transition: transform 0.3s ease-out; /* Transición suave para el cambio de tamaño */
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
    
    .ordenes > h4{
        display: flex;
        align-items: center;
        justify-items:center ;
        justify-content: center;
        margin-bottom: -1px;
        width:10%;
        border-block-start: #1985ac;
        border-top: #1985ac solid 1px;
        border-bottom: #1985ac solid 1px;
        font-size: 16px !important;

    }
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

</style>

