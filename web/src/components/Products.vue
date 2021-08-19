<template>
    
    <scale-transition group class="row ">
        <product :class="['col-sm-4 col-xs-12 col-lg-4 mx-4 mb-4 p-0 shadow up','product-'+ n]"   
        v-for="(product,n) in sorted" 
        :product="product" 
        :key="product.id" 
        />
    </scale-transition>
  
</template>

<script>
    import Product from './Product.vue'
    import { ScaleTransition } from 'vue2-transitions'

    import { mapState} from 'vuex'

    export default {
        props:['limit'],
        name: 'Products',
        components: {
            Product,
            ScaleTransition
        },
        data() {
            return {
                data: null,
                sortBy:null,
            }
        },
        computed : {
            ...mapState(['sorted','sliderFilter','filterCat','stock',
            'orderPrice','orderAvailable','orderQuantity'])
        },
        methods: {
            filterBy(product,n = 1) {
                var flag = true
                if(this.filterCat) {
                    flag = product.idcategoria == this.filterCat
                } 
                 if(!(product.precio_venta <= this.sliderFilter[1] && product.precio_venta >= this.sliderFilter[0])) {
                    flag = false
                }
                if(!product.condicion && this.available){
                    flag = false
                }
                if(product.stock > this.stock){
                    flag = false
                }
                
                if(this.limit <=  n ){
                    flag = false
                }

                return flag
            },
           
            
        },
        mounted() {
        }
    }
</script>

<style>
</style>