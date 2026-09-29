<template>
  <div>
    <h2>Cotizador de cajas de carton</h2> 
    <div class="form-group">
      <label for="tipo">Tipo de caja:</label>
      <select v-model="tipo" id="tipo" class="form-control">
        <option value="caja">Caja</option>
        <option value="paleta">Paleta</option>
      </select>
    </div>
    <div class="form-group">
      <label for="ancho">Ancho (cm):</label>
      <input v-model="ancho" type="number" id="ancho" class="form-control" />
    </div> 
    <div class="form-group">
      <label for="largo">Largo (cm):</label>
      <input v-model="largo" type="number" id="largo" class="form-control" />
    </div>
    <div class="form-group">
      <label for="alto">Alto (cm):</label>
      <input v-model="alto" type="number" id="alto" class="form-control" />
    </div>
    <div class="form-group">
      <label for="cantidad">Cantidad:</label>
      <input v-model="cantidad" type="number" id="cantidad" class="form-control" />
    </div>
    <button @click="cotizar" class="btn btn-primary">Cotizar</button>
    <div v-if="resultado">
      <h3>Resultado:</h3>
      <p>Tipo: {{ tipo }}</p>
      <p>Ancho: {{ ancho }} cm</p>
      <p>Largo: {{ largo }} cm</p>
      <p>Alto: {{ alto }} cm</p>
      <p>Cantidad: {{ cantidad }}</p>
      <p>Precio total: {{ precioTotal }}</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tipo: "caja",
      ancho: 10,
      largo: 10,
      alto: 10,
      cantidad: 1,
      resultado: null,
      precioTotal: 0,
    };
  },
  methods: {
    cotizar() {
      const area = this.ancho * this.largo;
      let precio = 0;
      if (this.tipo === "caja") {
        precio = area * 0.5;
      } else if (this.tipo === "paleta") {
        precio = area * 0.3;
      }
      this.precioTotal = precio * this.cantidad;
      this.resultado = {
        tipo: this.tipo,
        ancho: this.ancho,
        largo: this.largo,
        alto: this.alto,
        cantidad: this.cantidad,
        precioTotal: this.precioTotal,
      };
    },
  },
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}
</style>