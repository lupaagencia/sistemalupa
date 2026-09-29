  <template>
      <div class="contenedor">
        <div class="seccion">
            <div class="contenedor-header botones_vieworden">
                <button class="btn btn-primary" @click="ocultarDetalle">Volver al listado de ordenes</button>
                <button class="btn btn-warning" @click="imprimirOrden">Imprimir</button>
            </div>
            <div class="contenedor-seccion vieworden">
                <div class="card-header header_vieworden">
                    <div class="logo_vieworden">

                        <img src="img/LOGO-LUPA.jpg" alt="">
                    </div>
                    <div class="fecha_norden">
                        <h5 v-text="`ORDEN DE TRABAJO NO. ${orden.id}`"></h5>
                        <div class="card fecha_orden">
                            <div class="card-body ">
                                <strong> <div>Fecha:</div>   <div v-text="orden.fecha"></div></strong>
                            </div>
                        </div>prioridad
                    </div> 
                </div>
                
                <div class="datos_cliente">
                    <div class="card">
                        <div class="card-body">
                            <ul>
                                <li>
                                    <label>Prioridad:</label> <div :class="[ orden.prioridad=='normal' ? 'bg-success': orden.prioridad=='media' ? 'bg-warning': 'bg-danger']" class="p-1" v-text="orden.prioridad"></div>
                                </li>
                                <li>
                                    <label>Pago:</label> 
                                    <div v-if="orden.pago==1" class="bg-success p-1">Pago realizado</div>
                                    <div v-else class="bg-danger p-1">Pago pendiente</div>
                                </li>
                                <li class="ml-1">
                                    <label>Plancha:</label> 
                                    <div v-if="orden.plancha==0">No existe</div>
                                    <div v-else>Existe</div>
                                </li>
                            </ul>
                        </div>
                        <div class="card-header">
                            <h4>Cliente</h4>
                        </div>
                        <div class="card-body">
                            
                            <ul>
                                <li>
                                    <label>Cliente:</label> <div v-text="orden.cliente.razonsocial"></div>
                                </li>
                                <li>
                                    <label>Contacto:</label> <div v-text="orden.cliente.contacto"></div> 
                                </li>
                                
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="datosorden">
                    <div class="card">
                        <div class="card-body">

                            <!-- <div><label for="">Producto:</label> <div v-text="orden.articulo.nombre_articulo"></div></div> -->
                            <div><label for="">Cantidad trabajo:</label> <div v-text="orden.cantidad"></div></div>
                            <div><label for="">Dimenciones trabajo:</label> <div v-text="orden.tamano"></div><div v-text="orden.medida_material"></div></div>
                            <div><label for="">Carpeta cliente:</label> <div v-text="orden.carpeta_cliente"></div></div>
                            <div><label for="">Fecha de entrega:</label> <div v-text="orden.fecha_entrega"></div></div>
                        </div>
                    </div>
                </div>
                <div class="descripcion_orden">
                    <div class="card">
                        <div class="card-header">
                            <h4>Descripción del trabajo</h4>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li v-for="(detalle, index) in orden.detalles" :key="index">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                            <th scope="row" v-text="detalle.titulo_detalle"></th>
                                            <td v-text="detalle.valor_detalle"></td>
                                            <td v-text="detalle.descripcion_detalle"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4>Ejecución trabajo</h4>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li v-for="(costo, index) in orden.costos" :key="index">
                                    <table class="table">
                                        
                                        <tbody>
                                            <tr>
                                            <th scope="row" v-text="costo.tipo_costo"></th>
                                            <td v-text="costo.nombre_insumo"></td>
                                            <td v-text="costo.descripcion_costo"></td>
                                            <td v-text="costo.cantidad_costo"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-header">
                                <h4 for="">Detalles de diseño</h4> 
                            </div>
                            <div style="color:red" v-text="orden.detalles_diseno" class="card"></div>
                        </div>
                    </div>
                        <div class="card">
                        <div class="card-body">
                            <div class="card-header">
                                <h4 for="">Descripción</h4>
                            </div>
                            <div style="color:red" v-text="orden.observaciones" class="card"></div>
                        </div>
                        </div>
                </div>

            </div>
        </div>
    </div>
</template>
 <script>                    
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    export default {
        props:['orden','user'],
        data (){
            return {
             cliente:'uno'
            }
           
        },
        methods : {
            ocultarDetalle(){
                this.$emit('ocultarDetalle' )
            },
            imprimirOrden(){
                window.print()
            },
            
           
        },
        mounted() {
        }
    }
</script>

