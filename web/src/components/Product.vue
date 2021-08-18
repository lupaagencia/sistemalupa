<template>
  <div class="product-image-wrapper"  @mouseover="details = true"  @mouseout="details = false" >
    <router-link :to="{name: 'producto' , params: {id:product.id}}">
      <div class="single-products">
        <div class="productinfo text-center">
          <img :class="['card-img-top',product.condicion ? '' : 'product-disabled']" :src="`${dominio}/img/${product.imagen}`" alt="Card image cap" >

          <h2>$ {{product.precio_venta }}</h2>

          <slide-x-right-transition>
              <p >{{product.nombre}}</p>
          </slide-x-right-transition>
          <scale-transition >
              <a class="btn btn-default add-to-cart" @click="saveToCart(product)" href="#">Agregar al Carrito</a>
          </scale-transition>
        </div>
      </div>
    </router-link>
  </div>

</template>

<script>
  import { SlideXRightTransition,ScaleTransition } from 'vue2-transitions'
    import { mapMutations } from 'vuex'


export default {
  name: 'product',
  props: ['product'],
  components: {
    SlideXRightTransition,ScaleTransition
  },
  data() {
    return { details: false,store:false, dominio:this.$store.state.dominio}
  },
    methods : {
       ...mapMutations(['saveToCart'])
    },
    created() {
      this.$route.name == 'store' ? this.store = true : ''
    }
}
</script>

<style >
.product-disabled{
  opacity:0.7;
}
.flying-badge{
      position: absolute;
    right: -27px;
    border-radius: 40px;
    padding: 9px;
}
.card-title,.card-text{
  text-shadow:1px 2px 8px black;
}
</style>
