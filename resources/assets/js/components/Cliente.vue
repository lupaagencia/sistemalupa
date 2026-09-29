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
                            <button class="nav-link" :class="{'active': show=='cuentas'}" @click="mostrarTab('cuentas')" id="cuentas-tab" data-bs-toggle="tab" data-bs-target="#cuentas" type="button" role="tab" aria-controls="cuentas" aria-selected="true">Cuentas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='contactos'}" @click="mostrarTab('contactos')" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">Contactos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='facturacion'}" @click="mostrarTab('facturacion')" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">Datos facturacion</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" :class="{'active': show=='envio'}" @click="mostrarTab('envio')" id="contactos-tab" data-bs-toggle="tab" data-bs-target="#contactos" type="button" role="tab" aria-controls="contactos" aria-selected="false">Datos envio</button>
                        </li>
                       
                    </ul>
                     <template v-if="show=='cuentas'">
                        <cuentas :user="user" @mostrarTab="mostrarTab" @cambiarTab="cambiarTab"></cuentas>
                    </template>
                     <template v-if="show=='contactos'">
                        <contactos :user="user" :cont="cont"></contactos>
                    </template>
                     <template v-if="show=='facturacion'">
                        <facturacion :user="user" :cont="cont"></facturacion>
                    </template>
                     <template v-if="show=='envio'">
                        <envio :user="user" :cont="cont"></envio>
                    </template>
                   
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
           
        </main>
</template>

<script>
    import cuentas from './cliente/Cuentas'
    import contactos from './cliente/Contactos'
    import facturacion from './cliente/Facturacion'
    import envio from './cliente/Envio'
    export default {
        props:['user'],
        data (){
            return {
                show:'cuentas',
                cont:{clientes:[]},
            }
        },
        components:{
            cuentas,
            contactos,
            facturacion,
            envio
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
        top:10vh;
    }
    .mostrar{
        display: block !important;
        opacity: 1 !important;
      
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
