    

<template>
    <div class="contenedor">
        <div class="col-sm-12 mt-2 sticky-botones">
            <button type="button" @click="cancelar()" class="btn btn-primary-modern">
                <i class="fa fa-arrow-left"></i> Volver
            </button>
            <div>
                <button type="button" @click="imprimir()" class="btn btn-warning-modern mr-2">
                    <i class="fa fa-print"></i> Imprimir PDF
                </button>
                <button v-if="edit==1" type="button" @click="actualizar()" class="btn btn-success-modern">
                    <i class="fa fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
        <div class="contenedor-header header-premium d-flex justify-content-between align-items-center">
            <div>
                <h2 v-if="factura.tipo === 'remision'">Remisión No. {{factura.num_comprobante || factura.id}}</h2>
                <h2 v-else-if="factura.tipo === 'cuentacobro'">Cuenta de Cobro No. {{factura.num_comprobante || factura.id}}</h2>
                <h2 v-else-if="edit==0">Factura No. {{factura.num_comprobante}}</h2>
                <h2 v-else>Editando Comprobante No. {{factura.num_comprobante}}</h2>
                <div v-if="factura.pedido_num || factura.pedido_id" class="font-weight-bold small text-white mt-1 opacity-9">
                    <i class="fa fa-shopping-cart mr-1"></i>Perteneciente al Pedido #{{ factura.pedido_num || factura.pedido_id }}
                </div>
            </div>
        </div>
       
    <div class="card-body-premium" id="factura">
        <div class="row">
            <div class="col-sm-3">
                <span class="seccion-label">Fecha del Comprobante</span>
                <input type="date" class="input-modern w-100" v-model="factura.fecha">
            </div>
            <div class="col-sm-3">
                <span class="seccion-label">Forma de Pago</span>
                <input type="text" class="input-modern w-100" v-model="factura.forma_pago" placeholder="Ej. Contado">
            </div>
            <div class="col-sm-3" v-if="factura.tipo === 'cuentacobro'">
                <span class="seccion-label">Estado de Validación</span>
                <select class="input-modern w-100 font-weight-bold" v-model="factura.estado" :class="{'text-success': ['Valida', 'Válida', 'Cerrado', '1'].includes(factura.estado), 'text-secondary': !['Valida', 'Válida', 'Cerrado', '1'].includes(factura.estado)}">
                    <option value="Valida">✓ Válida (Cuentas x Cobrar)</option>
                    <option value="Invalida">✕ Inválida (Sin Cartera)</option>
                </select>
            </div>
        </div>

        <div class="contenedor-seccion mt-4">
            <span class="seccion-label">Información del Cliente</span>
            <div class="seccion-body">
                <table class="table w-100 header-comprobante">
                    <thead>
                        <tr>
                            <th>Razón Social</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Dirección Empresa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="factura.razonsocial">
                            <td v-text="factura.razonsocial.razonsocial"></td>
                            <td>{{factura.razonsocial.tipo_documento}}: {{factura.razonsocial.numero}} {{factura.razonsocial.digito}}</td>
                            <td v-text="factura.razonsocial.telefono"></td>
                            <td>{{factura.razonsocial.direccion}}, {{factura.razonsocial.ciudad}}</td>
                        </tr>
                        <tr v-else-if="factura.cliente">
                            <td v-text="factura.cliente.razonsocial"></td>
                            <td>{{factura.cliente.tipo_documento}}: {{factura.cliente.numero}} {{factura.cliente.digito}}</td>
                            <td v-text="factura.cliente.telefono"></td>
                            <td>{{factura.cliente.direccion}}, {{factura.cliente.ciudad}}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="4" class="text-center text-muted">No hay datos de razón social disponibles</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="contenedor-seccion mt-4">
            <span class="seccion-label">Detalles del Comprobante</span>
            <div class="seccion-body" v-if="factura.lineas && factura.lineas.length">
                <table class="table table-modern w-100">
                    <thead>
                        <tr>
                            <th>Cantidad</th>
                            <th>Nombre del Producto</th>
                            <th>Detalles</th>
                            <th v-if="factura.tipo != 'remision'">V. Unitario</th>
                            <th v-if="factura.tipo != 'remision'">Subtotal</th>
                            <th v-if="factura.tipo != 'remision'">Descuento</th>
                            <th v-if="factura.tipo != 'remision'">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(linea,index) in factura.lineas" :key="index">
                            <td>
                                <input v-if="edit==1" type="number" class="input-modern w-100" @change="totales()" v-model="linea.cantidad">
                                <span v-else v-text="linea.cantidad"></span>
                            </td>
                            <td class="font-weight-bold">{{linea.articulo.nombre}}</td>
                            <td>
                                <ul class="detallesp">
                                    <li v-for="(detalle,index) in linea.detalles" :key="index">
                                        <strong>{{detalle.titulo}}:</strong>&nbsp;{{ detalle.valor}}
                                    </li>
                                </ul>
                            </td>
                            <td v-if="factura.tipo != 'remision'">
                                <div v-if="edit==1" class="d-flex align-items-center">
                                    <span class="mr-1">$</span>
                                    <input type="number" class="input-modern w-100" @change="totales()" v-model="linea.valor_unitario">
                                </div>
                                <span v-else>$ {{ formatoNumero(linea.valor_unitario) }}</span>
                            </td>
                            <td v-if="factura.tipo != 'remision'">$ {{ formatoNumero(linea.subtotal) }}</td>
                            <td v-if="factura.tipo != 'remision'">
                                <div v-if="edit==1" class="d-flex align-items-center">
                                    <span class="mr-1">$</span>
                                    <input type="number" class="input-modern w-100" @change="totales()" v-model="linea.descuento">
                                </div>
                                <span v-else>$ {{ formatoNumero(linea.descuento) }}</span>
                            </td>
                            <td v-if="factura.tipo != 'remision'" class="font-weight-bold text-success">$ {{ formatoNumero(linea.valor_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="alert alert-info border-0">Este comprobante no tiene líneas registradas.</div>
        </div>

        <div v-if="factura.tipo != 'remision'" class="footer-premium row mt-4 mx-0">
            <div class="col-md-3">
                <div class="summary-card">
                    <span class="seccion-label">Subtotal</span>
                    <h4 class="mb-0">$ {{ formatoNumero(factura.subtotal) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <span class="seccion-label">Descuento</span>
                    <div v-if="edit==1" class="d-flex align-items-center mt-1">
                        <span class="mr-1">$</span>
                        <input type="number" class="input-modern w-75" @change="totales()" v-model="factura.descuento">
                    </div>
                    <h4 v-else class="mb-0">$ {{ formatoNumero(factura.descuento) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <span class="seccion-label">Impuestos (IVA)</span>
                    <div v-if="edit==1" class="d-flex align-items-center mt-1">
                        <input type="number" step="0.01" class="input-modern w-50 mr-1" @change="totales()" v-model="factura.iva">
                        <span class="small text-muted">rate</span>
                    </div>
                    <h4 class="mb-0">$ {{ formatoNumero(factura.impuestos) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card total-card">
                    <span class="seccion-label text-white-50">Total General</span>
                    <h3 class="mb-0 font-weight-bold">$ {{ formatoNumero(total) }}</h3>
                </div>
            </div>
            
            <div class="col-md-6 mt-3">
                <div class="summary-card">
                    <span class="seccion-label">Abonos Realizados</span>
                    <div v-if="edit==1" class="d-flex align-items-center mt-1">
                        <span class="mr-1">$</span>
                        <input type="number" class="input-modern w-50" @change="totales()" v-model="factura.abono">
                    </div>
                    <h4 v-else class="mb-0 text-info">$ {{ formatoNumero(factura.abono) }}</h4>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="summary-card" :class="{'bg-light-danger': factura.saldo > 0}">
                    <span class="seccion-label">Saldo Pendiente</span>
                    <h4 class="mb-0 font-weight-bold" :class="{'text-danger': factura.saldo > 0}">$ {{ formatoNumero(factura.saldo) }}</h4>
                </div>
            </div>
        </div>
    </div>
    </div>
<!--Fin del modal-->
</template>

<script>
//funcion para cotizar cajas de carton con estilos css en vue.js?
 import lclientes from './ListaClientes'
 import lproducto from './ListaArticulo'
 import customproducto from './customProducto'
 import detalleorden from '../DetallesOrden'
 import html2pdf from "html2pdf.js"
 
 
 export default {
    props:['dato', 'edit','user','factura'],
    data(){
        return{
            borrarl:-1,
            borrarorden:0,
            opcionproduccion:-1,
            fixed:0,
            iva:19,
            newCliente:0,
            arrayClientes:[],
            arrayProductos:[],
            arrayOrdenes:[],
            buscar_cliente:'',
            buscar_producto:'',
            modalc:0,
            modala:0,
            modalp:0,
            clienteno:0,
            clienteSelect:{'nombre':''},
            productoSelect:{},
            cambios:'',
            scroll:0
            
        }
    },
    components: {
        lclientes,
        lproducto,
        customproducto,
        detalleorden
    },
    computed:{
        
        total(){
             var sumSubtotal=0;
             var sumDescuento=0;
             if (this.factura && this.factura.lineas) {
                 this.factura.lineas.forEach(e =>{
                     let cant = parseFloat(e.cantidad || 0);
                     let unit = parseFloat(e.valor_unitario || 0);
                     let desc = parseFloat(e.descuento || 0);
                     
                     e.subtotal = cant * unit;
                     e.valor_total = e.subtotal - desc;
                     
                     sumSubtotal += e.subtotal;
                     sumDescuento += desc;
                 })
             }
             this.factura.subtotal = sumSubtotal;
             this.factura.descuento = sumDescuento;
             this.factura.impuestos = this.factura.subtotal * (parseFloat(this.factura.iva) || 0);
             
             var valor_total = (this.factura.subtotal - this.factura.descuento) + parseFloat(this.factura.impuestos);
             this.factura.total = valor_total;
             this.factura.saldo = this.factura.total - parseFloat(this.factura.abono || 0);
             
             return this.factura.total;
         },
        
        
        },  
    methods:{
       
        totales(){
            return this.total();
        },
        subTotalProducto(){
            var valor=0;
            this.factura.lineas.forEach(e =>{
                e.subtotal=parseInt(e.cantidad)*parseFloat(e.valor_unitario)
                e.valor_total=e.subtotal-e.descuento
            })
            
           
        },
         
        impuesto(){
            var valor=0;
            this.factura.impuestos=this.factura.subtotal*0.19
        },
        subTotal(){
            var valor=0;
            this.factura.lineas.forEach(e =>{
                valor=valor+parseInt(e.subtotal)
            })
            var resultado=valor
            this.factura.subtotal=valor
        },
        descuento(){
             var descuento=0;
            this.factura.lineas.forEach(e =>{
                descuento=descuento+parseInt(e.descuento)
            })
            var resultado=descuento
            this.factura.descuento=descuento
        },
       
       
       
        formatoNumero(valor) {
            if (valor === null || valor === undefined || isNaN(valor)) return 0;
            return valor;
        },
        cancelar(){
            this.$emit('regresar', 1)
        },
        imprimir() {
            if (this.factura && this.factura.id) {
                var url = '/imprimirCuentaCobro?id=' + this.factura.id;
                window.open(url, '_blank');
            }
        },
        actualizar(){
            let me = this;
            const data = new FormData()
            data.set('data', JSON.stringify(this.factura))
            data.set('user_id', this.user.id)
            axios.post('/comprobante/actualizar', data)
            .then(function (response) {
                Swal.fire('Guardado', 'El comprobante ha sido actualizado correctamente', 'success');
                me.$emit('regresar', 1)
            }).catch(function (error) {
                console.log(error);
                Swal.fire('Error', 'No se pudo actualizar el comprobante', 'error');
            });
        },
            
        mounted() {
            
        }
       
        
    }
}

</script>

<style>
    .contenedor {
        background: #f8f9fa;
        padding: 20px;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    .header-premium {
        background: #20c997; /* Modern Teal */
        color: #fff;
        padding: 30px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(32, 201, 151, 0.2);
        margin-bottom: -10px;
        position: relative;
        z-index: 2;
    }
    .card-body-premium {
        background: #fff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #edf2f7;
    }
    .seccion-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
    }
    .header-comprobante th {
        border-top: none !important;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        background: #f1f5f9;
        padding: 12px !important;
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-modern tr {
        background: #fff;
    }
    .table-modern th {
        background: #f8fafc;
        border: none;
        padding: 15px;
        font-size: 12px;
        color: #475569;
    }
    .table-modern td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }
    .input-modern {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        transition: all 0.2s;
        background: #f8fafc;
    }
    .input-modern:focus {
        border-color: #20c997;
        box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.1);
        outline: none;
        background: #fff;
    }
    .btn-primary-modern {
        background: #475569;
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-success-modern {
        background: #20c997;
        color: #fff;
        padding: 12px 25px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(32, 201, 151, 0.3);
    }
    .btn-warning-modern {
        background: #f59e0b;
        color: #fff;
        padding: 12px 25px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    .footer-premium {
        background: #f8fafc;
        padding: 25px;
        border-radius: 12px;
        margin-top: 30px;
    }
    .summary-card {
        padding: 15px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .total-card {
        background: #20c997;
        color: #fff;
        border: none;
        box-shadow: 0 4px 12px rgba(32, 201, 151, 0.2);
    }
    .detallesp li strong {
        color: #64748b;
        font-size: 11px;
    }
    .sticky-botones {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
    }
</style>
