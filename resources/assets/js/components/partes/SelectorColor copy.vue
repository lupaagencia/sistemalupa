<template>
    <div>
      <!-- Botón para abrir el selector de color -->
      
  
      <!-- Selector de color oculto -->
      <input
        id="color-picker"
        type="color"
        v-model="detalle.descripcion"
        @input="updateColor"
        ref="colorPicker"
        style="display: none;"
      />
  
      <!-- Muestra el color seleccionado -->
      <div>
        <div @click.prevent="openColorPicker"
          :style="{ backgroundColor: selectedColor, width: '20px', height: '20px' }"
        ></div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: 
        {
        user:{},
        detalle:{}
    },
    data() {
      return {
        selectedColor: "#ff0000", // Color inicial (rojo)
      };
    },
    methods: {
      openColorPicker() {
        // Abre el input de selección de color
        this.$refs.colorPicker.click();
      },
      updateColor() {
        console.log("Color seleccionado:", this.selectedColor);
      },
    },
  };
  </script>
  
  <style>
  
 
  </style>
  