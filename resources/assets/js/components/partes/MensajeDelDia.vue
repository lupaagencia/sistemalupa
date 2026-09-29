<template>
    <div class="frase-wrapper">
        <!-- Toggle button for mobile -->
        <div class="text-right d-md-none">
            <button class="btn btn-sm btn-outline-secondary mb-1" style="font-size: 0.7rem; padding: 2px 8px;" @click="isCollapsed = !isCollapsed">
                <i class="fa" :class="isCollapsed ? 'fa-quote-left' : 'fa-chevron-up'"></i>
                {{ isCollapsed ? 'Ver frase diaria' : 'Cerrar' }}
            </button>
        </div>
        
        <!-- The phrase, hidden by default on mobile if collapsed -->
        <div v-show="!isCollapsed" class="frase-inspiradora">
             <p class="texto-frase">{{ mensajeDelDia }}</p>
        </div>
    </div>
</template>
  
  <script>
  const hoy = new Date();
  const diaDelAño = Math.floor(
    (hoy - new Date(hoy.getFullYear(), 0, 0)) / 86400000
  );
  export default {
    data() {
      return {
         mensajeDelDia: 'Cargando frase inspiradora...',
         isCollapsed: window.innerWidth < 768 // Starts collapsed on mobile
      };
    },
    created() {
      this.mensajedia()
    
    },
    methods: {
     async mensajedia() {
      var me = this;
       fetch("/frases.json")
      .then((response) => response.json())
      .then((data) => {
        
        console.log(indice)
        const indice = diaDelAño % data.length;
        me.mensajeDelDia = data[indice].contenido;
          })
      .catch((error) => {
        console.error("Error al cargar los colores:", error);
      });
    },
    }
  };

  </script>
  <style>
    .frase-inspiradora {
      max-width: 1300px;
      line-height: 1.4;
      margin: 5px auto;
      padding: 8px 12px;
      border-left: 4px solid #4f46e5;
      background: #f9fafb;
      font-family: 'Georgia', serif;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      border-radius: 8px;
    }
    .texto-frase {
      font-size: 1.1rem;
      color: #374151;
      margin-bottom: 0px;
      font-style: italic;
    }
    @media (max-width: 768px) {
        .texto-frase {
            font-size: 0.95rem;
        }
        .frase-inspiradora {
            padding: 6px 10px;
            border-left-width: 3px;
        }
    }
  </style>
  