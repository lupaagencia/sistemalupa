<template>
   <main class="main">
     <ol class="breadcrumb">
       <li class="breadcrumb-item">Registros de Producción</li>
       <li class="breadcrumb-item"><mensajedeldia></mensajedeldia></li>
      </ol>
      <div class="card">
        <ul   class="nav nav-tabs" id="myTab" role="tablist">
         
          <li v-for="(empleado, index) in empleados" :key="index" class="nav-item" role="presentation"> 
              <button class="nav-link" :class="{'active': show==empleado.id}" @click="mostrarTab(empleado)" :id="empleado.id+'-tab'" data-bs-toggle="tab" :data-bs-target="'#'+empleado.id" type="button" role="tab" :aria-controls="empleado.id" aria-selected="true">{{ empleado.nombre }}</button>
          </li>
           <li v-if="user && (user.idrol == 'Administrador' || user.idrol == 'Auxiliar producción' || user.idrol == 'Auxiliar de producción')" class="nav-item" role="presentation">
               <button class="nav-link" :class="{'active': show=='estadisticas'}" @click="mostrarTab('estadisticas')" id="0-tab" data-bs-toggle="tab" data-bs-target="#0" type="button" role="tab" aria-controls="0" aria-selected="true">Estadisticas</button>
          </li>
        </ul>
         <div  class="tab-content" id="myTabContent">
            <div v-if="show=='estadisticas'"   id="0" role="tabpanel" aria-labelledby="0-tab">
                <template>
                    <estadisticas :user="user"></estadisticas>
                </template>
            </div>
            <div v-else
            role="tabpanel"
            >
            <!-- quite el for por que no me funciona tengo que dejar el componente y enviarle el empleado -->
             <template>
                    <rempleados :empleado="empleado" :registros="registros" :codigos="codigos" :user="user" @actualizarRegistros="obtenerRegistros" @actualizarCodigos="obtenerCodigos"></rempleados>
              </template>
            </div>
              
        </div>
      
    </div>
  </main>
</template>

<script>
import axios from 'axios';
import rempleados from './RegistrosEmpleados';
import estadisticas from './EstadisticasProduccion';
import mensajedeldia from '../partes/MensajeDelDia';

export default {
   props:['user'],
  data() {
    return {
      show: 'estadisticas',
      empleados:[],
      registros: [],
      empleado:{},
      loading: true,
      codigos:[],
    };
  },
  components: {
    rempleados,
    estadisticas,
    mensajedeldia
  },
  created() {
    this.getempleados();
    this.obtenerCodigos();
    if(this.user && (this.user.idrol != 'Administrador' && this.user.idrol != 'Auxiliar producción' && this.user.idrol != 'Auxiliar de producción')){
      this.show = 0; 
    }
  },
  methods: {
    mostrarTab(empleado){
      if(empleado=='estadisticas'){
        this.show=empleado
      }else{
        this.empleado=empleado
        this.show=empleado.id
        this.obtenerCodigos()
        this.obtenerRegistros(empleado)

      }
    },
     async obtenerCodigos() {
      try {
        const response = await axios.get('/ajustes/codigos');
        this.codigos = response.data;
      } catch (error) {
        console.error('Error al cargar registros:', error);
      } finally {
        this.loading = false;
      }
    },
    obtenerRegistros(empleado) {
      let me=this;
      const localDate = new Date().toLocaleDateString('en-CA');
      axios.get('/registros/registroEmpleado?ide='+empleado.id + '&fecha=' + localDate)
      .then(function (response) {
        me.registros = response.data;
        me.registros.forEach(e => {
            if(e.actividad==''){
              e.codigo=''
            }
        });
      }).catch(function (error) {
          console.log(error);
      });
     
    },
    async getempleados() {
      try {
        const response = await axios.get('/empleado/getEmpleados');
        this.empleados = response.data;
        
        // Auto-seleccionar empleado si no es admin
        if (this.user && (this.user.idrol != 'Administrador' && this.user.idrol != 'Auxiliar producción' && this.user.idrol != 'Auxiliar de producción')) {
            const currentEmp = this.empleados.find(e => e.id == this.user.empleado_id);
            if (currentEmp) {
                this.mostrarTab(currentEmp);
            } else if (this.empleados.length > 0) {
                this.mostrarTab(this.empleados[0]);
            }
        }
      } catch (error) {
        console.error('Error al cargar registros:', error);
      } finally {
        this.loading = false;
      }
    },
   
  }
};
</script>

<style scoped>
ol{
  margin:0;
}
table {
  font-family: Arial, sans-serif;
  font-size: 14px;
}
</style>