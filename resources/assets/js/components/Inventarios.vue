<template>
            <main class="main contenedor">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='materias'}" @click="mostrarTab('materias')" id="cuentas-tab" data-bs-toggle="tab" data-bs-target="#cuentas" type="button" role="tab" aria-controls="cuentas" aria-selected="true">Materias Primas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='planchas'}" @click="mostrarTab('planchas')" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">Planchas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='troqueles'}" @click="mostrarTab('troqueles')" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">Troqueles</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='activos'}" @click="mostrarTab('activos')" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">Activos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='dashboard_costos'}" @click="mostrarTab('dashboard_costos')" id="dashboard-costos-tab" data-bs-toggle="tab" data-bs-target="#dashboard-costos" type="button" role="tab" aria-controls="dashboard-costos" aria-selected="false">Dashboard de Costos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='kardex'}" @click="mostrarTab('kardex')" id="kardex-tab" data-bs-toggle="tab" data-bs-target="#kardex" type="button" role="tab" aria-controls="kardex" aria-selected="false">Kárdex (Movimientos)</button>
                        </li>
                       
                    </ul>
                     <template v-if="show=='materias'">
                        <materiasPrimas :user="user" @mostrarTab="mostrarTab" @cambiarTab="cambiarTab"></materiasPrimas>
                    </template>
                     <template v-if="show=='planchas'">
                        <planchas :user="user" @mostrarTab="mostrarTab" @cambiarTab="cambiarTab" ></planchas>
                    </template>
                     <template v-if="show=='troqueles'">
                        <troqueles :user="user" :cont="cont"></troqueles>
                    </template>
                     <template v-if="show=='activos'">
                        <activos :user="user" :cont="cont"></activos>
                    </template>
                     <template v-if="show=='dashboard_costos'">
                        <dashboard-costos :user="user"></dashboard-costos>
                    </template>
                     <template v-if="show=='kardex'">
                        <kardex-movimientos :user="user"></kardex-movimientos>
                    </template>
                   
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
           
        </main>
</template>

<script>
    import materiasPrimas from './inventarios/MateriasPrimas'
    import planchas from './inventarios/Planchas'
    import troqueles from './inventarios/Troqueles'
    import activos from './inventarios/Activos'
    import dashboardCostos from './inventarios/DashboardCostos'
    import kardexMovimientos from './inventarios/KardexMovimientos'
    export default {
        props:['user'],
        data (){
            return {
                show:'materias',
                cont:{invetario:[]},
            }
        },
        components:{
            materiasPrimas,
            planchas,
            troqueles,
            activos,
            dashboardCostos,
            kardexMovimientos
        },
        computed:{
            
           
        },
        methods : {
            mostrarTab(show){
                this.show=show
            },
            cambiarTab(value){
                this.mostrarTab(value.show)
                this.cont=value.cont
            },
            
            
           
        },
        mounted() {
        }
    }
</script>
<style>   
     
    .modal-content{
        width: 100% !important;
        position: absolute !important;
    }
    .modal-content{
        width: 100% !important;
        top: 0 !important;
    }
    .mostrar{
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(0,0,0,0.8) !important;
        overflow-y: auto;
        z-index: 10000 !important;
    }
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
</style>
