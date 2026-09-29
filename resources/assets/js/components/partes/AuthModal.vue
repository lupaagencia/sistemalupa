<template>
     <div class="modal-overlay">
        <div class="auth" v-if="mostrarModal">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">
            🔒 Autorización requerida
            </h2>
            <p v-if="error==null" class="text-sm text-gray-600 mb-3">
            Ingrese la clave para continuar
            </p>
            <p
              v-else
              class="text-red-600 text-sm mb-3 bg-red-50 p-2 rounded-lg"
            >
              {{ error }}
            </p>
            <div class="input">

              <input
               type="password"
               v-model="clave"
               placeholder="Clave de autorización"
               class="border rounded-lg w-full p-2 mb-3 text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
               @keyup.enter="validarClave"
             />
   
          
               <button class="btn btn-danger" @click="$emit('close')">Cancelar</button>
            </div>
        </div>
    </div>
</template>
<script>
import { ref } from 'vue'
export default {
  name: 'AuthModal',

  props: ['mostrarModal'],
   

  data() {
    return {
      clave: '',
      error: null,
    }
  },

  methods: {
    async validarClave() {
      this.error = null
      if (!this.clave) {
        this.error = 'Por favor ingrese una clave'
        return
      }
      try {
        const res = await axios.post('/verificar-clave', { clave: this.clave })
        console.log(res)
        if (res.data) {
          this.$emit('authorized', true)
        } else {
          this.error = '❌ Clave incorrecta o sin permisos'
        }
      } catch (e) {
        this.error = 'Error al verificar la clave'
      }
    },
  },
}
</script>
<style>
.auth{
  position: absolute;
    top: 50%;
    background: #fff;
    padding: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
    border: 10px solid #147d00;
}
.auth p{
    font-size: 24px;
    color: #ab0558;
    margin:auto;
}
.auth .input{
  display: flex;
  justify-content: space-between;
}
.auth button{
  font-size: 16px;
  height:40px;
  padding: 0 20px;
}
</style>