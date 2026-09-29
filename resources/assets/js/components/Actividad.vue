<template>
    <main class="main contenedor">
        <div class="contenedor" >
            <div class="contenedor-header" >
            <!-- Breadcrumb -->
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><h5>Actividad de los usuarios</h5></li>
                </ol>
            </div>
            <div class="card-body">
                <div class="contenedor-seccion ">
                    <div class="seccion-header">
                    
                    
                    </div>
                    <div class="seccion-body userac">
                        <div class="subseccion">
                            <div @click="viewActividad(user,index)"  class="userbtn" :class="{'activo': index==viewUser}" v-for="(user,index) in arrayUsuarios" :key="index">{{user.nombre}}</div>
                        </div>
                        <div class="subseccion">
                            <div v-if="actividadUser" class="actUser">
                                <div class="list-group">
                                    <a v-for="(actividad,index) in actividadUser" :key="index" href="#" class="list-group-item list-group-item-action" aria-current="true">
                                        <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{actividad.actividad}}</h5>
                                        </div>
                                        <div class="d-flex flex-row justify-content-between">
                                            <p class="mb-1">{{actividad.fecha}} - {{actividad.hora}}</p>
                                            <button type="button" class="btn btn-danger btn-sm" @click="borrarActividad(actividad,index)">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </div>
                                    </a>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    export default {
        
        data (){
            return {
               arrayUsuarios:[],
               actividadUser:[],
               viewUser:-1,
            }
        },
        computed:{
           
                     

            
        },
        methods : {
            borrarActividad(actividad,index){
                var me=this
                var h=confirm('Esta seguro de borrar la actividad?');
                if(h){
                    var url= '/user/borrarActividad?id='+ actividad.id+'&_method=DELETE';
                    axios.delete(url).then(function (response) {
                        var respuesta= response.data;
                        console.log(respuesta);
                        me.actividadUser.splice(index,1)
                        me.actividadUser.push.respuesta;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                }
                

            },
            viewActividad(user,index){
                let me=this;
                var url= '/user/actividad?id='+user.id;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.actividadUser = respuesta
                    me.viewUser=index;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            listarUsuarios (){
                let me=this;
                var url= '/user';
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayUsuarios = respuesta.personas.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
          
         
        },
        mounted() {
            this.listarUsuarios();
        }
    }
</script>
<style>  
    .subseccion{
        display:flex;
        flex-direction: row;
    }
    .userac{
        display:flex;
        flex-direction:column;
    }  
    .userbtn{
        background:#f5f5f5;
        border:solid 1px #bdbdbd;
        border-radius:5px;
        margin-right:2px;
        padding:10px;
        color:#3daf6d;
        cursor:pointer;
    }
    .actUser{
        width:100%;
    }
    .activo{
        background:#000;
        font-size: 16px;
        font-weight: bold;
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
