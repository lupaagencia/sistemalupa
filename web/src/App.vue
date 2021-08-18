<template>
  <div id="app">
    <eheader @showCart="showCart = !showCart"> </eheader>

    <zoom-center-transition>
    <div class="container">

    <router-view></router-view>
    </div>
    </zoom-center-transition>

    <slide-x-right-transition :duration='800'>
    </slide-x-right-transition>
    <efooter></efooter>
  </div>
</template>

<script>

  import Eheader from '@/components/layout/Eheader.vue'
  import Efooter from '@/components/layout/Efooter.vue'
  import SingleProduct from '@/components/SingleProduct.vue'
  import ProductModal from '@/components/ProductModal.vue'
  import { SlideXRightTransition,ZoomCenterTransition } from 'vue2-transitions'
  

  export default {
    name: 'app',
    components: {
      Eheader,
      Efooter,
      ProductModal,
      SingleProduct,
      SlideXRightTransition,
      ZoomCenterTransition,
    },
    data() {
      return {
        loading: false,
      }
    },
  }
</script>

<style lang="scss">
  //@import './assets/styles/custom-boostrap.scss';
  @import './assets/styles/bootstrap.min.scss';  
  @import './assets/styles/font-awesome.min.scss';
  @import './assets/styles/responsive.scss';
  @import './assets/styles/price-range.scss';
  @import './assets/styles/animate.scss';
  @import './assets/styles/styles.scss';
</style>