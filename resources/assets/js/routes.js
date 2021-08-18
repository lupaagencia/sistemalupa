import ProductModal from './components/ProductModal.vue'
import Checkout from './components/views/Checkout.vue'
//import Home from './components/views/Home.vue'
import Store from './components/views/Store.vue'
import Payment from './components/views/Payment.vue'
import Producto from './components/views/Producto.vue'


    const routes = [
        { path: '/', 
        component: Store,
        name: 'store',
        children:[
            {
                path: '/product/:id',
                component: ProductModal,
                name: 'product',
            }]
       },
       {
           path: '/checkout',
           component: Checkout,
           name: 'checkout',
       },
    //    {
    //     path: '/tienda',
    //     component: Store,
    //     name: 'store',
    //     children:[
    //        ]
    //     },
        {
            path: '/payment',
            component: Payment,
            name: 'payment',
        },
        {
            path: '/producto/:id',
            component: Producto,
            name: 'producto',
        },
       
        { path: '*', redirect: '/' }
        // { path: '/bar',  component: Bar }
    
        // {
        //   path: '/coins/:id',
        //   name: 'Coins',
        //   component: Coins
        // }
      ]

export default routes