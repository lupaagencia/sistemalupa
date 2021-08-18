import Vue from 'vue'
import Vuex from 'vuex'
import _ from 'lodash'
import axios from 'axios'

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {
    products: [],
    sorted:[],
    productsInCart: [],
    featuredProducts:[],
    min: 1,
    max: 1,

    stock: 1,
    stockMax: 1,

    sliderFilter: [],
    available: true,
    filterCat: null,
    categories: [],

    orderPrice:null,
    orderAvailable:null,
    orderQuantity: null,

    searchVisible:false,
    searchTerm:'',

    price:0,
    shipping:0
  },

  mutations: {
    increment(state) {
      state.count++
    },

    setUpProducts(state, products) {
      
      state.products = products
      state.sorted = products
      state.featuredProducts = state.products.filter((x) => {
    
        return x.featured ? true : false  
    })

      // state.max = _.maxBy(products, 'precio_venta').price,
      // state.min = _.minBy(products, 'precio_venta').price,
      state.max = 100000,
      state.min = 1,

      state.sliderFilter = [state.min, state.max]

      // state.stock = _.maxBy(products, 'stock').quantity
      state.stock = 10000
      state.stockMax = _.ceil(state.stock, -2)

      // state.productsInCart = 
    },
    setUpCategories(state, categories) {
      state.categories = categories
    },
    setUpCart(state) {
      // Check if the ID exists
      if (localStorage.hasOwnProperty('products')) {
        // Replace the state object with the stored item
        state.productsInCart = JSON.parse(localStorage.getItem('products'))
      }
    },


    updateRanges(state, value) {
      state.sliderFilter = [value[0], value[1]]
    },
    updateStock(state, value) {
      state.stock = value
    },
    updateAvailable(state, value) {
      state.available = value
    },
    updateFilterCat(state, value) {
      state.filterCat = value
    },

    updateorderAvaliable(state,value) {
      if(state.available) return
      state.orderAvailable = value

      state.sorted = _.orderBy(state.products,'condicion',value ? 'desc' : 'asc')

      state.orderQuantity = null // desactivar otros ordenados
      state.orderPrice = null
    },
    updateorderPrice(state,value) {
      state.orderPrice = value

      state.sorted = _.orderBy(state.products,'precio_venta',value ? 'desc' : 'asc')

      state.orderQuantity = null
      state.orderAvailable = null
    },
    updateorderQuantity(state,value) {
      state.orderQuantity = value

      state.sorted = _.orderBy(state.products,'stock',value ? 'desc' : 'asc')

      state.orderAvailable = null
      state.orderPrice = null
    },
    showSearch(state) {
      state.searchVisible = true
    },
    hideSearch(state) {
      state.searchVisible = false
    },
    search(state,value){
      state.sorted = state.products.filter((a) => {
        return a.name.includes(value)
        // return a.name.includes(value) && a.sublevel_id == state.filterCat // si se quisiera filtrar solo en la categoria activa

      })
    },


    saveToCart(state, value, wanted = 1) {
      if (!value.wanted) {
        value.wanted = 1
      }

      var item = state.productsInCart.filter((a) => {
        return a.id == value.id
      })[0]

      if (item) {
        if (item.wanted) {
          item.wanted += wanted
        } else {
          item.wanted = wanted
        }
        state.productsInCart.map((a) => {
          if (a.id == value) {
            a.wanted = item.wanted
          }
        })

      } else {
        value.wanted = 1
        state.productsInCart.push(value)
      }
      // state.$bus.$emit('addToCartNotice')
      this.commit('saveToLocalStorage')
    },
    removeFromCart(state, value) {
      state.productsInCart = state.productsInCart.filter((a) => {
        return !(a.id == value.id)
      })

      this.commit('saveToLocalStorage')
    },
    changeQuantity(state, value, wanted = 1) {

      if (value.wanted <= 1) return

      state.productsInCart.map((a) => {
        if (a.id == value.id) {
          a.wanted = wanted
        }
      })

      this.commit('saveToLocalStorage')

    },
    updatePrice(state,value) {
      state.price = value[0]
      state.shipping = value[1]
    },
    purchase(state) {
      state.productsInCart = []
      this.commit('saveToLocalStorage')
    },
    saveToLocalStorage(state) {
      localStorage.setItem('products', JSON.stringify(state.productsInCart));
    }


  },

  getters: {
    products: state => state.products,
    cart: state => state.cart,
  },

  actions: {
    setUpStore(context) {
      var url= 'http://127.0.0.1:8000/api/articulo';
      axios.get(url).then(function (response) {
          var respuesta= response.data.articulos;
          context.commit('setUpProducts', respuesta)
      })
     
      // fetch('/products.json')
      //   .then((res) => res.json())
      //   .then((json) => {
      //     var parsed = json.products.map((x) => {
      //       let random = Math.floor((Math.random() * 1000) + 1);
      //       x.src = 'https://loremflickr.com/320/340/product?random=' + random + '&lock=' + random 
      //       x.price = Number(x.price.substring(1).replace(',', '.'))
      //       return x
      //     })
      //     context.commit('setUpProducts', parsed)
      //   })
      var url2= 'http://127.0.0.1:8000/api/categoria/';
      axios.get(url2).then(function (response) {
          var respuesta= response.data.categorias.data;
          return context.commit('setUpCategories', respuesta)
      })
      
      // fetch('/categories.json')
      //   .then((res) => res.json())
      //   .then((json) => {

      //     context.commit('setUpCategories', json.categories)
      //   })

      context.commit('setUpCart')
    }
  },

})
// store.subscribe((mutation, state) => {
//   // Store the state object as a JSON string
//   console.log(state,mutation)
//   localStorage.setItem('store', JSON.stringify(state));
// });