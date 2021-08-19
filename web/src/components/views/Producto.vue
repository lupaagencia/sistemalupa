<template>
  <div class="container">
    <div class="row">
      <div class="col-sm-12 padding-right">
					<div class="product-details"><!--product-details-->
						<div class="col-sm-5">
							<div class="view-product">
								<img :src="`${dominio}/img/${producto.imagen}`" alt="" />
							</div>
							<div id="similar-product" class="carousel slide" data-ride="carousel">
								
								  <!-- Wrapper for slides -->
                  <!-- <div class="carousel-inner">
										<div class="item active">
										  <a href=""><img src="images/product-details/similar1.jpg" alt=""></a>
										  <a href=""><img src="images/product-details/similar2.jpg" alt=""></a>
										  <a href=""><img src="images/product-details/similar3.jpg" alt=""></a>
										</div>
										<div class="item">
										  <a href=""><img src="images/product-details/similar1.jpg" alt=""></a>
										  <a href=""><img src="images/product-details/similar2.jpg" alt=""></a>
										  <a href=""><img src="images/product-details/similar3.jpg" alt=""></a>
										</div>
										<div class="item">
										  <a href=""><img src="images/product-details/similar1.jpg" alt=""></a>
										  <a href=""><img src="images/product-details/similar2.jpg" alt=""></a>
										  <a href=""><img src="images/product-details/similar3.jpg" alt=""></a>
										</div>
										
									</div> -->

								  <!-- Controls -->
								  <a class="left item-control" href="#similar-product" data-slide="prev">
									<i class="fa fa-angle-left"></i>
								  </a>
								  <a class="right item-control" href="#similar-product" data-slide="next">
									<i class="fa fa-angle-right"></i>
								  </a>
							</div>

						</div>
						<div class="col-sm-7">
							<div class="product-information"><!--/product-information-->
							  <!-- <img src="images/product-details/new.jpg" class="newarrival" alt="" /> -->
								<h2>{{producto.nombre}}</h2>
								<p v-if="producto.descripcion!='null'">{{producto.descripcion}}</p>
								<!-- <img src="images/product-details/rating.png" alt="" /> -->
                <span v-if="producto.tipo_producto==2" class="left-sidebar flex">
                    <div >
                      <ul class="radio">
                        <li v-for="sub in subproductos" :key="sub.id">
                          <label>
                            <input class="" type="radio" name="subproducto" @change="listaAtributosOpciones(sub.id)" :value="sub.nombre" v-model="idsub">
                            {{sub.nombre}}
                          </label>
                        </li>
                      </ul>
                    </div>
                    <div v-if="atributosOpciones.length">
                      <ul class="radio ul-atributos">
                        <li  v-for="atr in atributosOpciones" :key="atr.id"  >
                          <div v-if="atr.tipo_campo==1">
                            <h4 for="">
                               <label>
                                <input class="" type="radio" name="atributo" @change="mostrarOpciones(atr.id)" :value="atr.nombre" >
                                  {{atr.nombre}}
                                 
                                </label>
                                
                            </h4>
                            <ul v-show="atr.nota" class="opcioneAtri">
                              <li v-for="op in atr.opciones_atributo" :key="op.id" @click="obtenerOpcion(op.id)" styles="display:inline-block" :class="op.open ? 'opactive' : '' " class="opciones" >
                                  <p>{{op.label}}</p> <span>${{op.valor}}</span>
                              </li>
                            </ul>
                          </div>
                          <div v-else-if="atr.tipo_campo==2">
                            <div v-if="cantidadCom>=atr.minimo && cantidadCom<=atr.maximo">
                              <h4 for="">{{`${atr.nombre} ${idsub}:`}} 
                                <div v-if="atr.valor>0" >
                                  <span v-if="atr.tipo_campo==6" style="font-size:18px !important">$ {{atr.valor}}</span>
                                  <span v-else-if="atr.tipo_campo==7" style="font-size:18px !important">$ {{atr.valor}}</span>
                                </div>
                              </h4>
                              <ul class="opcioneAtri">
                                <li v-for="op in atr.opciones_atributo" :key="op.id" @click="obtenerOpcion(op.id)" styles="display:inline-block" :class="op.open ? 'opactive' : '' " class="opciones" >
                                    <p>{{op.label}}</p> <span>${{op.valor}}</span>
                                </li>
                              </ul>
                            </div>
                          </div>
                          <div v-else>
                            <h4 for="">{{`${atr.nombre} ${idsub}:`}} 
                              <div v-if="atr.valor>0" >
                                <span v-if="atr.tipo_campo==6" style="font-size:18px !important">$ {{atr.valor}}</span>
                                <span v-else-if="atr.tipo_campo==7" style="font-size:18px !important">$ {{atr.valor}}</span>
                              </div>
                            </h4>
                            <ul class="opcioneAtri">
                              <li v-for="op in atr.opciones_atributo" :key="op.id" @click="obtenerOpcion(op.id)" styles="display:inline-block" :class="op.open ? 'opactive' : '' " class="opciones" >
                                  <p>{{op.label}}</p> <span>${{op.valor}}</span>
                              </li>
                            </ul>
                          </div>
                          
                        </li>
                      </ul>  
                    </div>
                    
                </span>
                <span v-else class="left-sidebar flex">
                    <div v-if="producto.atributos.length">
                      <ul class="radio ul-atributos">
                        <li  v-for="atributo in producto.atributos" :key="atributo.id"  >
                          <div v-if="atributo.tipo_campo==1">
                            <h4 for="">
                               <label>
                                <input class="" type="radio" name="atributo" @change="mostrarOpciones(atributo.id)" :value="atributo.nombre" >
                                  {{atributo.nombre}}
                                   hola
                                </label>
                                
                            </h4>
                            <ul v-show="atributo.nota" class="opcioneAtri">
                              <li v-for="op in atributo.opciones_atributo" :key="op.id" @click="obtenerOpcion(op.id)" styles="display:inline-block" :class="op.open ? 'opactive' : '' " class="opciones" >
                                  <p>{{op.label}}</p> <span>${{op.valor}}</span>
                              </li>
                            </ul>
                          </div>
                          <div v-else-if="atributo.tipo_campo==2">
                            <div v-if="cantidadCom>=atributo.minimo && cantidadCom<=atributo.maximo">
                              <h4 for="">{{`${atributo.nombre}:`}} 
                                <div v-if="atributo.valor>0" >
                                  <span v-if="atributo.tipo_campo==6" style="font-size:18px !important">$ {{atributo.valor}}</span>
                                  <span v-else-if="atributo.tipo_campo==7" style="font-size:18px !important">$ {{atributo.valor}}</span>
                                </div>
                              </h4>
                              <ul class="opcioneAtri">
                                <li v-for="op in atributo.opciones_atributo" :key="op.id" @click="obtenerOpcion(op.id)" styles="display:inline-block" :class="op.open ? 'opactive' : '' " class="opciones" >
                                    <p>{{op.label}}</p> <span>${{op.valor}}</span>
                                </li>
                              </ul>
                            </div>
                          </div>
                          <div v-else>
                            <h4 for="">{{`${atributo.nombre}:`}} 
                              <div v-if="atributo.valor>0" >
                                <span v-if="atributo.tipo_campo==6" style="font-size:18px !important">$ {{atributo.valor}}</span>
                                <span v-else-if="atributo.tipo_campo==7" style="font-size:18px !important">$ {{atributo.valor}}</span>
                              </div>
                            </h4>
                            <ul class="opcioneAtri">
                              <li v-for="op in atributo.opciones_atributo" :key="op.id" @click="obtenerOpcion(op.id)" styles="display:inline-block" :class="op.open ? 'opactive' : '' " class="opciones" >
                                  <p>{{op.label}}</p> <span>${{op.valor}}</span>
                              </li>
                            </ul>
                          </div>
                          
                        </li>
                      </ul>  
                    </div>
                    
                </span>
                
								<span>
                  <div class="flex-row totales float-right">
                    <p class="descuento"><label > Descuento:</label> <span>$ {{descuento}}</span></p>
                    <p><label>Valor a pagar:</label> <span>$ {{valorTotal}}</span></p>
                  </div>
                  <label>Cantidad:</label>
                  <input type="text" value="1" @change="calcularRango, validarCantidad" v-model="cantidad" :min="minCantidadcom" :max="maxCantidadcom"/>
                  <button type="button" class="btn btn-fefault cart">
                  <i class="fa fa-shopping-cart"></i>
									Añadir al carrito
									</button>
                  <div  v-show="errorCantidad" class="form-grou col-md-12 div-error">
                      <div class="text-center text-error">
                          <div v-for="error in errorMostrarMsjCantidad" :key="error" v-text="error">
                          </div>
                      </div>
                  </div>
								</span>
								
								<!-- <p><b>Availability:</b> In Stock</p>
								<p><b>Condition:</b> New</p>
								<p><b>Brand:</b> E-SHOPPER</p> -->
								<a href=""><img src="images/product-details/share.png" class="share img-responsive"  alt="" /></a>
							</div><!--/product-information-->
						</div>
					</div><!--/product-details-->
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Emenu from '../Emenu.vue'
export default {
  components: { Emenu },
  
  data() {
    return { 
      tipocampo:{6:'atr.valor', 7:'atr.valor*cantidad'},
      id: '',
      producto:'',
      idsub:'',
      cantidad:1,
      valorUnit:0,
      productos:this.$store.state.sorted,
      porcentajedes:0,
      subproductos:[],
      atributosOpciones:[],
      rangos:[],
      opcion:{},
      minCantidad:1,
      maxCantidad:10000,
      errorCantidad:0,
      errorMostrarMsjCantidad:[],
      dominio:this.$store.state.dominio
    }
  },
  mounted(){
     this.id=this.$route.params.id
      this.productos=this.$store.state.sorted
     this.obtenerProducto(this.id)
     this.validarCantidad()
  },
  created() {
      // this.viewProduct(this.id)
      this.id=this.$route.params.id
      this.productos=this.$store.state.sorted
      this.obtenerProducto(this.id)
      this.validarCantidad()
    },
    computed:{
        // descuento(){
        //   const numberFormat = new Intl.NumberFormat('en-US', this.$store.state.formato);
        //   var des=(this.valorUnit*this.cantidad)*(this.porcentajedes/100)
          
        //   return des.toFixed()
        // },
        descuento(){
          var des=0
          if(this.errorCantidad) return des
          this.atributosOpciones.forEach((e,index)=>{
            switch(parseInt(e.tipo_campo)){
              case 1:
                des=des+(parseFloat(this.atributosOpciones[index].valor)*this.cantidad)*(this.porcentajedes/100)
                break;
              case 2:
                break;
              case 3:
                break;
              case 4:
                break;
              case 5:
                break;
              case 6:
                break;
              case 7:
                des=des+(parseFloat(this.atributosOpciones[index].valor)*this.cantidad)*(this.porcentajedes/100)
                break;
            }
          })
          return des.toFixed()
        },
        cantidadCom(){
          this.validarCantidad()
          var cant=parseInt(this.cantidad)
          return cant
        },
        minCantidadcom(){
            return this.minCantidad
        },
        maxCantidadcom(){
            return this.maxCantidad
        },
        valorTotal(){
          this.calcularRango()
          var valor=0;
          if(this.errorCantidad){
            return valor
          } 
          this.atributosOpciones.forEach((e,index)=>{
            switch(parseInt(e.tipo_campo)){
              case 1:
                if(e.nota==true){
                  valor=valor+(parseFloat(this.atributosOpciones[index].valor)*this.cantidad)-((this.atributosOpciones[index].valor*this.cantidad)*(this.porcentajedes/100))
                }
                break;
              case 2:
                valor=valor+(parseFloat(this.atributosOpciones[index].valor)*this.cantidad)-((this.atributosOpciones[index].valor*this.cantidad)*(this.porcentajedes/100))
                break;
              case 3:
                break;
              case 4:
                break;
              case 5:
                break;
              case 6:
                valor=valor+parseFloat(this.atributosOpciones[index].valor)-((this.atributosOpciones[index].valor*this.cantidad)*(this.porcentajedes/100));
                break;
              case 7:
                valor=valor+(parseFloat(this.atributosOpciones[index].valor)*this.cantidad)-((this.atributosOpciones[index].valor*this.cantidad)*(this.porcentajedes/100))
                break;
            }
          })
          // if(this.valorUnit==0){
          //   var valor=(0*this.cantidad)-((0*this.cantidad)*(this.porcentajedes/100))
          // }else{
          //   var valor=(this.valorUnit*this.cantidad)-((this.valorUnit*this.cantidad)*(this.porcentajedes/100))
          //   var valor=this.atributosOpciones[1].valor*this.cantidad
          // }
          var valortofix=valor.toFixed();
          return valortofix
        }
    },
    methods:{
      validarCantidad(){
        var atributo=this.atributosOpciones
        atributo.forEach(a => {
          if(a.tipo_campo==2){
            if(this.cantidad>=a.minimo && this.cantidad<=a.maximo){
              a.operacion=true
              a.valor=a.opciones_atributo[0].valor
              a.opciones_atributo[0].open=true
            }else{
              a.valor=0
              a.opciones_atributo[0].open=false
              a.operacion=null
            }
          }
        })
        this.errorCantidad=0;
        this.errorMostrarMsjCantidad =[];
        if (parseInt(this.cantidad)<parseInt(this.minCantidad)) this.errorMostrarMsjCantidad.push("La cantidad no puede ser menor de "+this.minCantidad);
        if (parseInt(this.cantidad)>parseInt(this.maxCantidad)) this.errorMostrarMsjCantidad.push(`La cantidad no puede ser mayor de ${this.maxCantidad}`);
        if (this.errorMostrarMsjCantidad.length) this.errorCantidad = 1;
        return this.errorCantidad;
      },
      viewProduct(id){
        this.$store.commit('updateProduct',id)
      },
      obtenerSubproducto(id){
        var url= this.dominio+'/api/subarticulo'
        axios.get(url,{
          'id':id
        }).then(function (response) {
            var respuesta= response.data
            this.subproductos=respuesta.subproductos
            
        })
        .catch(function (error) {
            console.log(error);
        })
      },
      calcularRango(){
        this.rangos.forEach((e)=>{
          if(parseInt(this.cantidad) >= parseInt(e.de) && parseInt(this.cantidad) <= parseInt(e.hasta)){
            this.porcentajedes=e.descuento
          }
        })
      },
      obtenerProducto(id){
        let arrayp=this.productos.filter(p => p.id==id)
        console.log(arrayp);
        this.producto=arrayp[0]
        this.subproductos=this.producto.subproductos
      },
      obtenerOpcion(id){
        var atributo=this.atributosOpciones
        atributo.filter((a)=>{
          a.opciones_atributo.forEach(e => {
            e.open=false
            if(e.id==id){
                e.open=true
                this.opcion=e
                a.valor=e.valor
                this.valorUnit=e.valor
            }
          })
        })
      },
      mostrarOpciones(id){
        this.atributosOpciones.forEach((i)=>{
          if(i.id==id){
            i.nota=true
          }else{
            i.nota=false
          }
          
        })
      },
      listaAtributosOpciones(id){
        this.opcion={}
        this.valorUnit=0
        var sub=this.subproductos.filter(a=>a.id==id)
        this.atributosOpciones=sub[0].atributos
        this.atributosOpciones.forEach((i,index)=>{
          if(i.tipo_campo==2 || i.minimo=='' || i.minimo==null || i.minimo==0){this.minCantidad=1}else{
            this.minCantidad=i.minimo
          }
          if(i.tipo_campo==2 || i.maximo=='' || i.maximo==null || i.maximo==0) {this.maxCantidad=10000}else{
            this.maxCantidad=i.maximo
          }
          if(i.tipo_campo==6){
              this.valorUnit=i.valor;
          }
          this.atributosOpciones[index].opciones_atributo.forEach((e)=>{
            e.open=false;
          })

        })
        this.rangos=sub[0].rangos
      }

    },
    
}
</script>

<style >
.div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
        padding: 10px;
        border: solid 1px red;
        margin: 10px;
    }
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
