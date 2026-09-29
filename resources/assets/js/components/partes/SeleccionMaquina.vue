<template>
  <div>
    <h2>Optimización de impresión</h2>
    <div>
      <label for="anchoCaja">Ancho de la caja (cm):</label>
      <input v-model.number="anchoCaja" type="number" id="anchoCaja" />
    </div>
    <div>
      <label for="altoCaja">Alto de la caja (cm):</label>
      <input v-model.number="altoCaja" type="number" id="altoCaja" />
    </div>
    <div>
      <label for="cantidadProduccion">Cantidad a producir:</label>
      <input v-model.number="cantidadProduccion" type="number" id="cantidadProduccion" />
    </div>
    <div>
      <label for="colores">Número de colores adicionales:</label>
      <input v-model.number="colores" type="number" id="colores" />
    </div>
    <button @click="calcular">Calcular</button>
    <p><strong>Resultados:</strong></p>
    <p>Mejor máquina: {{ mejorMaquina }}</p>
    <p>Tamaño de impresión: {{ mejorTamano.ancho }}x{{ mejorTamano.alto }} cm</p>
    <p>Cabida (cajas/pliego): {{ mejorCabida }}</p>
    <p>Pliegos necesarios: {{ mejorPliegos }}</p>
    <p>Pliego utilizado: {{ mejorPliego.ancho }}x{{ mejorPliego.alto }} cm</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      anchoCaja: 25, // Ancho de la caja
      altoCaja: 23,  // Alto de la caja
      cantidadProduccion: 1000, // Cantidad de cajas a producir
      colores: 0, // Número de colores adicionales
      mejorMaquina: "", // Máquina seleccionada
      mejorTamano: {}, // Tamaño del área usada
      mejorPliego: {}, // Tamaño del pliego usado
      mejorCabida: 0, // Cabida (cajas por pliego)
      mejorPliegos: 0 // Pliegos necesarios
    };
  },
  methods: {
    calcular() {
      const calcularMejorOpcion = (anchoCaja, altoCaja, cantidadProduccion, colores) => {
        // Configuración de máquinas con límites exactos
        const maquinas = [
          { nombre: "GTO", max: { ancho: 50, alto: 35 }, min: { ancho: 22, alto: 14 } },
          { nombre: "SORM", max: { ancho: 70, alto: 52 }, min: { ancho: 35, alto: 27.5 } }
        ];

        const pliego = { ancho: 70, alto: 100 };
        const sobrante = 50 + colores * 25; // Suma el sobrante y los colores adicionales
        const produccionTotal = cantidadProduccion + sobrante;

        let mejorMaquina = "";
        let mejorTamano = {};
        let mejorCabida = 0;
        let mejorPliego = {};
        let mejorPliegos = Infinity;

        // Evaluar cada máquina
        maquinas.forEach(maquina => {
          for (let ancho = maquina.min.ancho; ancho <= maquina.max.ancho; ancho++) {
            for (let alto = maquina.min.alto; alto <= maquina.max.alto; alto++) {
              // Calcular cabida en orientación normal y rotada
              const cabidaNormal = Math.floor(ancho / anchoCaja) * Math.floor(alto / altoCaja);
              const cabidaRotada = Math.floor(ancho / altoCaja) * Math.floor(alto / anchoCaja);
              const cabidaMax = Math.max(cabidaNormal, cabidaRotada);

              if (cabidaMax > 0) {
                // Verificar cuántos tamaños caben en el pliego de 70x100
                const gruposHorizontales = Math.floor(pliego.ancho / ancho);
                const gruposVerticales = Math.floor(pliego.alto / alto);
                const totalGrupos = gruposHorizontales * gruposVerticales;

                if (totalGrupos > 0) {
                  // Calcular el número de pliegos necesarios
                  const pliegosNecesarios = Math.ceil(produccionTotal / (cabidaMax * totalGrupos));

                  // Actualizar la mejor opción
                  if (pliegosNecesarios < mejorPliegos) {
                    mejorMaquina = maquina.nombre;
                    mejorTamano = { ancho, alto };
                    mejorCabida = cabidaMax * totalGrupos;
                    mejorPliego = { ancho, alto };
                    mejorPliegos = pliegosNecesarios;
                  }
                }
              }
            }
          }
        });

        return { mejorMaquina, mejorTamano, mejorCabida, mejorPliego, mejorPliegos };
      };

      // Ejecutar el cálculo
      const { mejorMaquina, mejorTamano, mejorCabida, mejorPliego, mejorPliegos } =
        calcularMejorOpcion(this.anchoCaja, this.altoCaja, this.cantidadProduccion, this.colores);

      this.mejorMaquina = mejorMaquina;
      this.mejorTamano = mejorTamano;
      this.mejorCabida = mejorCabida;
      this.mejorPliego = mejorPliego;
      this.mejorPliegos = mejorPliegos;
    }
  }
};
</script>
