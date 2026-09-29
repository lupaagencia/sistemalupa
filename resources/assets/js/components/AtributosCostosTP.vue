<template>
    <div class="card mt-4 shadow rounded-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3" style="border-radius: 8px 8px 0 0;">
            <h5 class="mb-0 font-weight-bold"><i class="fa fa-sliders"></i> CONFIGURACIÓN: {{ tipo_edit.nombre }}</h5>
            <button class="btn btn-sm btn-outline-light" @click="salir()"><i class="fa fa-arrow-left"></i> VOLVER AL LISTADO</button>
        </div>
        <div class="card-body p-4 bg-white" style="max-height: 80vh; overflow-y: auto;">
            <div class="row">
                <!-- SECCIÓN 1: DATOS FINANCIEROS -->
                <div class="col-md-12 mb-3">
                    <h6 class="text-primary font-weight-bold border-bottom pb-2">1. Márgenes y Costos</h6>
                </div>
                <div class="col-md-6 form-group">
                    <label>Nombre del Producto</label>
                    <input type="text" class="form-control" v-model="tipo_edit.nombre">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-danger font-weight-bold">Gastos Fijos (%)</label>
                    <input type="number" step="0.01" class="form-control" v-model="tipo_edit.gastos_fijos">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-success font-weight-bold">Rentabilidad (%)</label>
                    <input type="number" step="0.01" class="form-control" v-model="tipo_edit.rentabilidad">
                </div>
                <div class="col-md-12 mt-2">
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; margin-bottom: 0;">
                            <input type="checkbox" v-model="tipo_edit.permite_troquelado" style="width: 20px; height: 20px;">
                            <span class="font-weight-bold" style="color: #713083;">Permitir Cálculo de Troquelado</span>
                        </label>
                    </div>
                </div>

                <!-- SECCIÓN 2: ATRIBUTOS DE TIENDA -->
                <div class="col-md-12 mt-4 mb-3">
                    <h6 class="text-primary font-weight-bold border-bottom pb-2">2. Atributos de Tienda Relacionados</h6>
                    <p class="small text-muted">Marque los atributos que el cliente podrá elegir para este producto en la web.</p>
                </div>
                <div class="col-md-12">
                    <div class="row bg-light p-3 rounded border mx-0">
                        <div v-for="attr in listaAtributosTienda" :key="attr.id" class="col-md-4 mb-2">
                            <div class="custom-control custom-checkbox p-2 border bg-white rounded shadow-sm hover-shadow">
                                <input type="checkbox" class="custom-control-input" 
                                       :id="'attr'+attr.id" 
                                       :value="attr.id" 
                                       v-model="atributosSeleccionados">
                                 <label class="custom-control-label font-weight-bold ml-4" :for="'attr'+attr.id" style="cursor:pointer">
                                    {{ attr.nombre }} 
                                    <span class="badge badge-secondary ml-1">{{ attr.tipo }}</span>
                                    <div class="mt-2 p-2 border-top bg-light" style="border-radius: 0 0 5px 5px; margin-left: -25px;">
                                        <label class="mb-0" style="cursor:pointer; display: flex; align-items: center; gap: 8px;">
                                            <input type="checkbox" v-model="attr.mostrar_en_producto" @change="cambiarVisibilidad(attr)" :true-value="0" :false-value="1" style="width: 16px; height: 16px;">
                                            <span :class="!attr.mostrar_en_producto ? 'text-danger' : 'text-success'" class="small font-weight-bold">
                                                {{ !attr.mostrar_en_producto ? 'OCULTO (No mostrable)' : 'MOSTRABLE EN TIENDA' }}
                                            </span>
                                        </label>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div v-if="listaAtributosTienda.length == 0" class="col-12 text-center py-3 text-muted">
                            <i class="fa fa-info-circle"></i> No hay atributos de tienda configurados.
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: PRODUCCIÓN Y FÓRMULAS -->
                <div class="col-md-12 mt-4 mb-3">
                    <h6 class="text-primary font-weight-bold border-bottom pb-2">3. Datos de Producción y Fórmulas</h6>
                </div>
                <div class="col-md-3 form-group">
                    <label>Sobrante (Pliegos)</label>
                    <input type="number" class="form-control" v-model="tipo_edit.sobrante">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tintas / Impresiones</label>
                    <input type="number" class="form-control" v-model="tipo_edit.impresiones">
                </div>
                <div class="col-md-6 form-group">
                    <label>Fórmula Ancho</label>
                    <input type="text" class="form-control" v-model="tipo_edit.formula_ancho" placeholder="ej: (L*2)+(A*2)">
                </div>
                <div class="col-md-6 form-group">
                    <label>Fórmula Largo</label>
                    <input type="text" class="form-control" v-model="tipo_edit.formula_largo" placeholder="ej: H+L+5">
                </div>
                <div class="col-md-3 form-group">
                    <label>Piezas por Pliego</label>
                    <input type="number" class="form-control" v-model="tipo_edit.piezas_por_pliego">
                </div>
            </div>
            
            <div class="mt-4 p-3 border-top d-flex justify-content-end bg-light mx-n4 mb-n4" style="border-radius: 0 0 8px 8px;">
                <button class="btn btn-secondary mr-2" @click="salir()">CANCELAR</button>
                <button class="btn btn-primary px-5 font-weight-bold shadow-sm" @click="guardar()" :disabled="cargando">
                    <span v-if="cargando"><i class="fa fa-spinner fa-spin mr-1"></i> GUARDANDO...</span>
                    <span v-else><i class="fa fa-save mr-1"></i> GUARDAR CONFIGURACIÓN</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AtributosCostosTP',
    props: ['tipo'],
    data() {
        return {
            tipo_edit: {
                id: 0,
                nombre: '',
                descripcion: '',
                orden: 1,
                gastos_fijos: 0,
                rentabilidad: 0,
                sobrante: 0,
                impresiones: 0,
                formula_ancho: '',
                formula_largo: '',
                piezas_por_pliego: 0
            },
            listaAtributosTienda: [],
            atributosSeleccionados: [],
            cargando: false
        }
    },
    methods: {
        cargar() {
            let me = this;
            if (me.tipo) {
                me.tipo_edit = Object.assign({}, me.tipo);
                
                // Cargar TODOS los atributos de tienda disponibles
                axios.get('/api/tienda/atributos-todos').then(response => {
                    me.listaAtributosTienda = response.data;
                });

                // Cargar los atributos ya vinculados a este tipo de producto
                axios.get('/api/tipo-producto/' + me.tipo_edit.id + '/atributos').then(response => {
                    me.atributosSeleccionados = response.data.map(a => a.id);
                });
            }
        },
        guardar() {
            let me = this;
            me.cargando = true;

            const datosGuardar = {
                id: me.tipo_edit.id,
                nombre: me.tipo_edit.nombre,
                descripcion: me.tipo_edit.descripcion || '',
                orden: me.tipo_edit.orden || 1,
                gastos_fijos: me.tipo_edit.gastos_fijos,
                rentabilidad: me.tipo_edit.rentabilidad,
                sobrante: me.tipo_edit.sobrante,
                impresiones: me.tipo_edit.impresiones,
                formula_ancho: me.tipo_edit.formula_ancho,
                formula_largo: me.tipo_edit.formula_largo,
                piezas_por_pliego: me.tipo_edit.piezas_por_pliego,
                permite_troquelado: me.tipo_edit.permite_troquelado,
                atributos: me.atributosSeleccionados
            };
            
            axios.put('/tipoproducto/actualizar', datosGuardar)
            .then(function (response) {
                me.cargando = false;
                if (response.data.status == 'success') {
                    console.log("¡Configuración guardada!");
                    me.salir();
                } else {
                    alert("Error al guardar: " + response.data.message);
                    me.cargando = false;
                }
            })
            .catch(function (error) {
                me.cargando = false;
                console.error("DETALLE DEL ERROR:", error.response);
                let msg = error.response && error.response.data && error.response.data.message ? error.response.data.message : "No se pudo guardar la configuración.";
                if (typeof Swal !== 'undefined') {
                    Swal.fire("Error", msg, "error");
                } else {
                    alert(msg);
                }
            });
        },
        salir() {
            this.$emit('ocultarAtributos', 0);
        },
        cambiarVisibilidad(attr) {
            let me = this;
            let formData = new FormData();
            formData.append('id', attr.id);
            formData.append('tipo', attr.tipo);
            formData.append('nombre', attr.nombre);
            formData.append('mostrar_en_producto', attr.mostrar_en_producto ? 1 : 0);
            formData.append('_method', 'PUT');

            axios.post('/api/tienda/atributos/actualizar', formData).then(response => {
                console.log("Visibilidad actualizada");
            }).catch(error => {
                console.error("Error al actualizar visibilidad", error);
            });
        }
    },
    mounted() {
        this.cargar();
    }
}
</script>

<style scoped>
.hover-shadow:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important; transition: all 0.3s ease; }
.custom-control-label { padding-top: 2px; }
.bg-light-blue { background-color: #f0f7ff; }
</style>
