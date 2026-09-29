<template>
    <main class="main">
     <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
        </ol>
        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div v-if="this.menutipos==0" class="card">
                
                <h4>Tipos de producto</h4>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="newtipoproducto">
                            <form @submit.prevent="accion"  enctype="multipart/form-data" class="form-horizontal">
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Nombre</label>
                                        <input type="text" v-model="nombre" placeholder="Nombre del tipo de producto"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Impresiones</label>
                                        <input type="text" v-model="impresiones" placeholder="Cantidad de impresiones del tragajo"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Cabida</label>
                                        <input type="text" v-model="cabida" placeholder="Cabida para la impresión del trabajo"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Piezas por Pliego</label>
                                        <input type="number" v-model="piezas_por_pliego" placeholder="Cantidad de piezas por pliego"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Tamaño</label>
                                        <input type="text" v-model="tamano" placeholder="Tamaño del pliego"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Area en metros cuadrados</label>
                                        <input type="text" v-model="area" placeholder="Area en metros cuadrados"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Sobrante</label>
                                        <input type="text" v-model="sobrante" placeholder="Tamaños sobrantes del trabajo"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Fórmula Ancho (usar L, A, H)</label>
                                        <input type="text" v-model="formula_ancho" placeholder="ej: (L*2)+(A*2)+5"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Fórmula Largo (usar L, A, H)</label>
                                        <input type="text" v-model="formula_largo" placeholder="ej: H+L+3"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Descripción</label>
                                        <input type="text" v-model="descripcion" placeholder="Descripción del tipo de producto"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Orden</label>
                                        <input type="text" v-model="orden" placeholder="Orden del tipo de producto"/>                                       
                                    </div>
                                </div>
                                <div  class="form-group ">
                                    <div>
                                        <label class="form-control-label" for="text-input">Imagen</label>
                                        <!-- <input type="file"  @change="obtenerImagen" accept="image/*" class="form-control" :key="fileInputKey" placeholder="Seleccione una imagen">  -->
                                        <input type="text" v-model="imagen" placeholder="Orden de la imagen"/>        
                                    </div>                               
                                </div>
                                <div   class="float-right" >
                                    <button class="btn btn-success" @click="crearTipoproducto">Crear</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-9">
                    
                        <table v-if="tabla==1" class="table table-bordered table-stritipoped table-sm">
                            <thead>
                                <tr>
                                    <th>Opciones</th>
                                    <th>ID</th>
                                    <th>Orden</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tipo in arrayTipo" :key="tipo.id" ref="tipo2">
                                    <td>
                                        <div type="button" @click="abrirAtributos(tipo)" class="btn btn-warning btn-sm">
                                            <i class="icon-pencil"></i>
                                        </div> &nbsp;
                                        <template v-if="tipo.condicion">
                                            <div type="button" class="btn btn-danger btn-sm" @click="eliminartipo(tipo.id)">
                                                <i class="icon-trash"></i>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div type="button" class="btn btn-info btn-sm" @click="activartipo(tipo.id)">
                                                <i class="icon-check"></i>
                                            </div>
                                        </template>
                                    </td>
                                    <td v-text="tipo.id"></td>
                                    <td v-text="tipo.orden"></td>
                                    <td>
                                        <!-- <figure style="margin:0 0 0 0">
                                            <img v-if="img.orden==1" :src="`img/productos/${img.nombre}`" width="100px" alt="">
                                        </figure>  -->
                                    </td>
                                    <td ><button class="btn btn-link"  @click="abrirAtributos(tipo)" >{{ tipo.nombre }}</button></td>
                                    <td v-text="tipo.descripcion"></td>
                                
                                </tr>  
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Sección de Edición de Atributos y Costos -->
            <div v-if="menutipos == 1">
                <AtributosCostosTP 
                    @ocultarAtributos="ocultarAtributos" 
                    :tipo="tipoedit">
                </AtributosCostosTP>
            </div>
            <!-- Fin ejemplo de tabla Listado -->
        </div>
    <!--Inicio del modal agregar/actualizar-->
        
    </main>
</template>

<script>
import VueBarcode from 'vue-barcode';
import AtributosCostosTP from './AtributosCostosTP.vue';
export default {
data (){
    return {
        tabla:1,
        menutipos:0,
        tipoedit:[],
        nombre : '',
        orden:1,
        area:0,
        sobrante:100,
        tamano:1,
        impresiones:1,
        cabida:1,
        imagen:[],
        imagentemp:[],
        imagenMin:[],
        ordenimg:1,
        descripcion : '',
        arrayTipo : [],
        errorTipo : 0,
        errorMostrarMsjTipo : [],
        topedit:0,
        seccion:'',
        dominio:'',
        formula_ancho: '',
        formula_largo: '',
        piezas_por_pliego: 0,
    }
},
components: {
    'barcode': VueBarcode,
    AtributosCostosTP
},
computed:{
   
}, 
methods : {
    abrirAtributos(tipo){
        this.tipoedit=tipo
        this.menutipos=1
    },
    ocultarAtributos(val){
        this.menutipos=val;
        this.listarTipo();
    },
    crearTipoproducto(){
        if(this.validarTipo()){
            return;
        }
        let me =this
        axios.post('/tipoproducto/registrar', {
            'nombre': this.nombre,
            'descripcion': this.descripcion,
            'orden':this.orden,
            'tamano':this.tamano,
            'area':this.area,
            'cabida':this.cabida,
            'impresiones':this.impresiones,
            'sobrante':this.sobrante,
            'formula_ancho': this.formula_ancho,
            'formula_largo': this.formula_largo,
            'piezas_por_pliego': this.piezas_por_pliego
        })
        .then(function (response) {
            me.listarTipo()
            
        })
        .catch(function(error){
            console.log(error)
        });

    },
    validarTipo(){
        this.errorTipo=0
        this.errorMostrarMsjTipo=[]
        if(!this.nombre) this.errorMostrarMsjTipo.push('El nombre del tipo de producto')
        if(!this.orden) this.errorMostrarMsjTipo.push('El orden del tipo de producto')
        if(this.errorMostrarMsjTipo.length) this.errorTipo=1
        return this.errorTipo
    },
    
    cerrarModali(){
        this.modali=0
        this.arrayInsumos=[]
    },
    anchoColum() {
        let me =this
        let ancholista=me.$refs.articulo2.clientWidth
        let w=ancholista/me.anchoCol
            return { width: `${w}px` };
    }, 
    listarTipo (){
        let me=this;
        var url= '/tipoproducto'
        axios.get(url).then(function (response) {
            var respuesta= response.data;
            console.log(respuesta)
            me.arrayTipo = respuesta;
        })
        .catch(function (error) {
            console.log(error);
        });
        
        
    },
    eliminarImagen(img){
        console.log(img)
        var me=this
        axios.get('/borrarImagen?id='+img.id+'&idtabla='+img.id_tabla)
        .then(function (response) {
            console.log(response)
            if(response.data.length==0){
                me.imagen=[]
            }else{
                me.imagen=response.data
            }
            
        }).catch(function (error) {
            console.log(error);
        });
    },
    eliminarImagenTemp(index){
        this.imagentemp.splice(index,1)
        this.imagenMin.splice(index,1)
    },
    obtenerImagen(e){
        let file=e.target.files[0]
        let img={
            'file':URL.createObjectURL(file),
            'orden':this.ordenimg
        }
        this.imagentemp.push(file)
        this.imagenMin.push(img)
        
        // this.imagen[]=imag
        // let imag={
        //     'id':0,
        //     'id_tabla':this.idarticulo,
        //     'nombre':file.name,
        //     'orden':img.indexOf()
        // }

    },
    cambiarOden(){
        let swap = (val1, val2, arr) => {
        if(!arr.includes(val1) || !arr.includes(val2)) return;
            let val1_index = arr.indexOf(val1);
            let val2_index = arr.indexOf(val2);
            arr.splice(val1_index, 1, val2);
            arr.splice(val2_index, 1, val1);
        }
        let estudiantes = ['Juan', 'Luis', 'Mario','Jessica', 'Marcos'];
        swap('Luis', 'Jessica', estudiantes);
        console.log(estudiantes)
    },
    
},
mounted() {
    this.listarTipo();
}
}
</script>
<style>   
h4{
    padding: 20px;
}
input{
    width: 100%;
}
.newtipoproducto{
    padding:10px 20px;
}
.cantidades{
display: flex;
flex-direction: row;
list-style: none;
flex-wrap: wrap;
padding:0;
margin: 5px;
} 
.modal-content{
width: 100% !important;
position: absolute !important;
}
.mostrar{
display: list-item !important;
opacity: 1 !important;
position: absolute !important;
background-color: #3c29297a !important;
}
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
.modal-bajo{
top:30%;
}
.art_padre_select{
display: flex;
justify-content: space-between;
}
.table-subproducto{
position: absolute;
left: 20px;
top: 200px;
z-index: 10;
box-shadow: 5px 5px 5px rgb(0 0 0 / 30%);
width: 97%;
}
.imgP{
display:flex;
justify-content: space-between;
}
.imgM{
display:flex;
flex-direction:row;
}
.mini-G input, .mini input{
width:100px;
}
.mini-G{
border:5px solid rgb(81, 158, 81);
box-shadow: 0 0 5px rgba(0,0,0,0.5);
margin-left:10px;
display:flex;
flex-direction:column;
}
.mini{
border:5px solid rgb(158, 81, 81);
box-shadow: 0 0 5px rgba(0,0,0,0.5);
margin-left:10px;
display:flex;
flex-direction:column;
}
.card{
    padding:20px;
}
</style>

