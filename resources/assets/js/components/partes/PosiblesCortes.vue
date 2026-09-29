<template>
  <div class="calculador-cortes">
    <h2>Calculador de Cortes</h2>

    <!-- Selección de tamaño del pliego -->
    <div>
      <label for="tamanoPliego">Selecciona el tamaño del pliego:</label>
      <select v-model="tamanoPliego" id="tamanoPliego">
        <option v-for="tamano in tamañosPliego" :key="tamano" :value="tamano">{{ tamano }}</option>
      </select>
    </div>

    <!-- Ingreso de cantidad de piezas a cortar -->
    <div>
      <label for="cantidad">Cantidad de piezas a cortar:</label>
      <input type="number" v-model="cantidad" id="cantidad" min="1" />
    </div>

    <!-- Botón para calcular el tamaño del corte -->
    <div>
      <button @click="calcularTamañosCorte()">Calcular Opciones de Corte</button>
    </div>

    <!-- Resultado del cálculo -->
    <div v-if="opcionesCorte.length">
      <h3>Opciones de Corte:</h3>
      <ul>
        <li v-for="(opcion, index) in opcionesCorte" :key="index">
          {{ opcion }}
        </li>
      </ul>
    </div>

    <!-- Mensaje de error si no es posible cortar las piezas -->
    <div v-if="error" class="error">
      <p>{{ error }}</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tamanoPliego: "70x100", // Valor predeterminado
      cantidad: 1,
      opcionesCorte: [], // Guardará todas las opciones de corte posibles
      error: null,
      tamañosPliego: ["70x100", "90x60", "125x157"], // Tamaños de pliego disponibles
    };
  },
  methods: {
    calcularTamañosCorte() {
      this.error = null; // Reiniciar error
      this.opcionesCorte = []; // Limpiar las opciones previas

      // Descomponer el tamaño del pliego
      const [anchoPliego, altoPliego] = this.tamanoPliego.split("x").map(Number);

      // Verificar la cantidad de piezas solicitada
      if (this.cantidad <= 0) {
        this.error = "La cantidad de piezas debe ser un número mayor que 0.";
        return;
      }

      // Buscar las opciones de corte posibles
      for (let anchoCorte = 30; anchoCorte <= anchoPliego; anchoCorte++) {
        for (let altoCorte = 30; altoCorte <= altoPliego; altoCorte++) {
          const piezasPorFila = Math.floor(anchoPliego / anchoCorte);
          const filasPorPliego = Math.floor(altoPliego / altoCorte);
          const piezasTotales = piezasPorFila * filasPorPliego;

          // Verificar si las piezas totales son suficientes para la cantidad solicitada
          if (piezasTotales >= this.cantidad) {
            // Si es una opción válida, la agregamos a la lista
            this.opcionesCorte.push(`${anchoCorte}x${altoCorte}`);
          }
        }
      }

    // Si no encontramos ninguna opción válida
      if (this.opcionesCorte.length === 0) {
        this.error = "No es posible obtener el número de piezas solicitado con el tamaño de pliego seleccionado.";
      }
    },
    cargarTamaniosPliego() {
      let me = this;
      axios.get('/ajustes/listar?tipo=medidas_pliego').then(response => {
        if (response.data && response.data.length > 0) {
          const categorias = [...new Set(response.data.map(item => item.categoria).filter(Boolean))];
          if (categorias.length > 0) {
            me.tamañosPliego = categorias;
          }
        }
      }).catch(err => {
        console.log(err);
      });
    }
  },
  mounted() {
    this.cargarTamaniosPliego();
  }
};
</script>

<style scoped>
.calculador-cortes {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

h2 {
  text-align: center;
}

label {
  display: block;
  margin: 10px 0 5px;
}

select,
input {
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

button {
  padding: 10px 20px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
}

button:hover {
  background-color: #45a049;
}

h3 {
  margin-top: 20px;
}

.error {
  color: red;
  margin-top: 20px;
}
</style>
