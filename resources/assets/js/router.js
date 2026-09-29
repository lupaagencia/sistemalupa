import Vue from 'vue'
import VueRouter from 'vue-router'
import Atributos from './components/Atributos'
import OpcionesAtributos from './components/OpcionesAtributos'

Vue.use(VueRouter)

export default new VueRouter({
    mode:'history',
    base: process.env.BASE_URL,
    routes:[
            {
                path:'/main#atributos',   
                name:'atributos',
                component:Atributos
            },
            {
                path:'/main#opciones-atributos',   
                name:'opciones-atributos',
                component:OpcionesAtributos
            }
        ]
    
})
