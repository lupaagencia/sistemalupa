<template>
    <div class="contenedor">
        <div class="contenedor-seccion">
            <div>
                <div class="gantt">
                    <div class="gantt-left">
                        <div v-for="(proceso,index) in procesos" :key="index" class="row">{{ proceso.proceso }}</div>
                      
                    </div>

                    <div class="gantt-right">
                        <div class="timeline">
                            <div class="task"
                            :style="{ gridColumn: getGridColumn(filtro.fechaI, filtro.fechaF) }">
                            </div>
                            <div class="task" style="grid-column: 6 / 10;"></div>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>

    </div>
</template>
<script>
    export default {
        props:['reportes','procesos','filtro'],
        data (){
            return {
                fechaBase: '2026-01-01'
            }
        },
         components: {
        },
        computed:{
            
        },
        methods : {
             diffDias(fechaBase, fecha) {
                if (!fechaBase || !fecha) return 0

                const f1 = new Date(fechaBase)
                const f2 = new Date(fecha)

                // diferencia en milisegundos
                const diff = f2.getTime() - f1.getTime()

                // convertir a días
                return Math.floor(diff / (1000 * 60 * 60 * 24))
            },

            getGridColumn(inicio, fin) {
                const diaInicio = this.diffDias(this.fechaBase, inicio) + 1
                const diaFin = this.diffDias(this.fechaBase, fin) + 2

                return `${diaInicio} / ${diaFin}`
            }
        },
        created() {
        },
        mounted() {
           
        }
    }
</script>
<style>
    .gantt {
        display: grid;
        grid-template-columns: 300px 1fr;
        height: 600px;
    }
    .gantt-left .row {
        height: 40px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        padding-left: 10px;
        font-size: 13px;
        }
    .gantt-right {
    overflow-x: auto;
    }

    .timeline {
    display: grid;
    grid-template-columns: repeat(31, 40px); /* 31 días */
    position: relative;
    }
    .timeline-row {
    display: grid;
    grid-template-columns: repeat(31, 40px);
    height: 40px;
    border-bottom: 1px solid #e5e7eb;
    }
    .task {
    height: 28px;
    margin-top: 6px;
    border-radius: 6px;
    background: #3469d4;
    }
</style>