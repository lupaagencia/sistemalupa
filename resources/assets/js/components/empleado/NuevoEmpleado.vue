<template>
      <div class="contenedor">
        <div class="seccion" role="document">
            <div class="contenedor-div">
                <div class="contenedor-header">
                    <div class="titulo">Contacto</div>
                    <div class="subseccion">
                        <button class="btn btn-danger" @click="cerrarModal()">Cancelar</button>
                        <button class="btn btn-primary">Guardar y nuevo</button>
                        <button class="btn btn-success" @click="enviarFormulario()" type="submit" >Guardar</button>

                    </div>
                    
                </div>
                <div class="contenedor-seccion">
                    <div class="seccion-header" id="headingOne">
                        Datos Cliente
                    </div>

                    <div class="seccion-body">
                      <!-- Banner Informativo de Biometría & Identificación -->
                      <div v-if="edit && empleado.id" class="row mb-4">
                          <!-- Tarjeta Fotografía -->
                          <div class="col-md-6 mb-2">
                              <div class="card border shadow-sm p-3 rounded" :class="empleado.foto ? 'border-success' : 'border-warning'" style="background: #f8fafc;">
                                  <div class="d-flex align-items-center">
                                      <div class="mr-3">
                                          <img v-if="empleado.foto" 
                                               :src="getFotoUrl(empleado.foto)" 
                                               @error="onFotoError($event, empleado.foto)"
                                               class="rounded-circle shadow border border-success" 
                                               style="width: 65px; height: 65px; object-fit: cover;"
                                               alt="Foto">
                                          <div v-else class="rounded-circle bg-white d-flex align-items-center justify-content-center border text-muted shadow-sm" 
                                               style="width: 65px; height: 65px;">
                                              <i class="fa fa-user fa-2x text-secondary"></i>
                                          </div>
                                      </div>
                                      <div>
                                          <h6 class="font-weight-bold mb-1 text-dark">
                                              <i class="fa fa-camera mr-1 text-primary"></i> Fotografía Facial
                                          </h6>
                                          <div v-if="empleado.foto">
                                              <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i> Fotografía Registrada</span>
                                              <small class="d-block text-muted mt-1">Habilitada para reconocimiento facial biométrico.</small>
                                          </div>
                                          <div v-else>
                                              <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i> Sin Fotografía</span>
                                              <small class="d-block text-muted mt-1">Puede subirla en el campo 'Foto' abajo o en el kiosco.</small>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <!-- Tarjeta Huella Dactilar -->
                          <div class="col-md-6 mb-2">
                              <div class="card border shadow-sm p-3 rounded" :class="empleado.huella_dactilar ? 'border-success' : 'border-warning'" style="background: #f8fafc;">
                                  <div class="d-flex align-items-center justify-content-between">
                                      <div class="d-flex align-items-center">
                                          <div class="mr-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                               :style="empleado.huella_dactilar ? 'background: #dcfce7; color: #15803d; width: 65px; height: 65px;' : 'background: #fef3c7; color: #b45309; width: 65px; height: 65px;'">
                                              <i class="fa fa-fingerprint fa-2x"></i>
                                          </div>
                                          <div>
                                              <h6 class="font-weight-bold mb-1 text-dark">
                                                  <i class="fa fa-id-card-o mr-1 text-warning"></i> Huella Dactilar
                                              </h6>
                                              <div v-if="empleado.huella_dactilar">
                                                  <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i> Huella Vinculada</span>
                                                  <small class="d-block text-muted mt-1">Registrada en el lector de este sistema.</small>
                                              </div>
                                              <div v-else>
                                                  <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i> Sin Huella Vinculada</span>
                                                  <small class="d-block text-muted mt-1">Vincúlela desde la pantalla del Kiosco de Asistencia.</small>
                                              </div>
                                          </div>
                                      </div>
                                      <div v-if="empleado.huella_dactilar">
                                          <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm" @click="desvincularHuella()">
                                              <i class="fa fa-trash mr-1"></i> Desvincular
                                          </button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <form @submit.prevent="enviarFormulario" class="grid-form">
                          <!-- Campos de texto simples -->
                          <div class="row"> 
                            <div v-for="(field, index) in textFields" :key="index" class="form-group col-sm-3">
                                
                                <label class="form-control-label" :for="field">{{ formatLabel(field) }}</label>
                                <select  v-if="typeof field === 'object' && field !== null" class="" v-model="empleado[Object.keys(field)[0]]" type="text"  >
                                  <option value="" disabled>Seleccione...</option>
                                  <option v-for="option in field.tipo_doc" :key="option" :value="option">{{ option }}</option>
                                </select>
                                <input v-else  class="" v-model="empleado[field]" type="text" :id="field" :name="field" />
                            </div>

                            <!-- Campos de tipo select -->
                            <div v-for="(field, index) in selectFields" :key="index" class="form-group  col-sm-3">
                                <label :for="field">{{ formatLabel(field) }}</label>
                                <select v-model="empleado[field]" :id="field" :name="field">
                                <option value="" disabled>Seleccione...</option>
                                <option v-for="option in options[field]" :key="option" :value="option">{{ option }}</option>
                                </select>
                            </div>

                            <!-- Campos de fecha -->
                            <div v-for="(field, index) in dateFields" :key="index" class="form-group  col-sm-3">
                                <label :for="field">{{ formatLabel(field) }}</label>
                                <input v-model="empleado[field]" type="date" :id="field" :name="field" />
                            </div>

                            <!-- Campos numéricos -->
                            <div v-for="(field, index) in numberFields" :key="index" class="form-group  col-sm-3">
                                <label :for="field">{{ formatLabel(field) }}</label>
                                <input v-model="empleado[field]" type="text" :id="field" :name="field" />
                            </div>

                            <!-- Salario Base (calculado) -->
                            <div class="form-group col-sm-3">
                                <label for="salario_base">Salario Base</label>
                                <input type="text" id="salario_base" :value="salarioBase" class="form-control" readonly style="background-color: #e9ecef; font-weight: bold; color: #28a745;" />
                                <small class="text-muted">Salario - Auxilio de Transporte</small>
                            </div>

                            <!-- Campos de archivos -->
                            <div v-for="(field, index) in fileFields" :key="index" class="form-group  col-sm-3">
                                <label :for="field">{{ formatLabel(field) }}</label>
                                <input type="file" @change="handleFileUpload" :id="field" :name="field" />
                            </div>
                          </div>
                          <!-- Botón de Enviar -->
                          <div class="form-group full-width">
                              <button class="btn btn-success boton-principal" type="submit">Guardar</button>
                          </div>
                      </form>
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </template>
  
  <script>
  export default {
    props:['edit','user','empleado'],
    data() {
      return {
        
        textFields: [
          'nombre', 'apellido', {'tipo_doc':['CC', 'TI', 'CE', 'PA']}, 'num_doc', 'lugar_nacimiento', 'telefono', 'direccion', 'correo', 
          'cargo', 'area', 'banco', 'pension', 'eps', 'arl', 'nivel_estudio', 
          'titulos', 'certificaciones', 'idiomas', 'habilidades', 'experiencia', 
          'contacto_esposa', 'contacto_padres', 'contacto_emergencia', 'info_medica', 
          'talla_dotacion'
        ],
        selectFields: [
          'tipo_doc', 'estado_civil', 'tipo_contrato', 'tipo_jornada', 'tipo_cuenta', 'turno'
        ],
        dateFields: ['fecha_nacimiento', 'fecha_ingreso', 'fecha_finalizacion'],
        numberFields: [
           'num_hijos', 'salario', 'auxilio_transporte', 'horas_semanales', 
          'num_cuenta_banco', 'num_afiliacion_social'
        ],
        fileFields: ['foto', 'img_doc', 'pdf_hoja', 'pdf_contrato'],
       
        options: {
          estado_civil: ['Soltero', 'Casado', 'Divorciado', 'Viudo'],
          tipo_contrato: ['Indefinido', 'Término fijo', 'Prestación de servicios'],
          tipo_jornada: ['Completa', 'Parcial'],
          tipo_cuenta: ['Ahorros', 'Corriente'],
          turno: ['Diurno', 'Nocturno', 'Rotativo'],
        },
      };
    },
    computed: {
      salarioBase() {
        const salario = parseFloat(this.empleado.salario) || 0;
        const auxilio = parseFloat(this.empleado.auxilio_transporte) || 0;
        return salario - auxilio;
      }
    },
    methods: {
      handleFileUpload(event) {
        const fieldName = event.target.name;
        this.empleado[fieldName] = event.target.files[0];
      },
      cerrarModal(){
                this.$emit('ocultarDetalle', 1,this.empleado.nombre)
            },
        enviarFormulario() {
            var me=this;
            const data = new FormData()

            if (me.empleado.img_doc) {
              data.append("img_doc", me.empleado.img_doc);
            }
            
            if (me.empleado.foto) {
              data.append("foto", me.empleado.foto);
            }
            if (me.empleado.pdf_hoja) {
              data.append("pdf_hoja", me.empleado.pdf_hoja);
            }
            if (me.empleado.pdf_contrato) {
              data.append("pdf_contrato", me.empleado.pdf_contrato);
            }
            data.append('data',JSON.stringify(me.empleado))
            axios.post('/empleado/registrar', data,{
                headers: {
                            'Content-Type': 'application/json', // Especificar formato JSON
                        },
            })
            .then(function (response) {
              console.log(response)
              me.cerrarModal()
            }).catch(function (error) {
                console.log(error);
            });
           

               
                
        },
      
      formatLabel(field) {
        if(typeof field === "object" && field !== null){
          var tipo=Object.keys(field)[0]
          return tipo.replace(/_/g, " ").replace(/\b\w/g, (letra) => letra.toUpperCase());
        }else{
          return field.replace(/_/g, " ").replace(/\b\w/g, (letra) => letra.toUpperCase());
        }
      },
      getFotoUrl(foto) {
        if (!foto) return '/img/avatar.png';
        if (typeof foto !== 'string') return '/img/avatar.png';
        if (foto.startsWith('http://') || foto.startsWith('https://') || foto.startsWith('data:')) return foto;
        if (foto.startsWith('/')) return foto;
        return '/img/empleados/' + foto;
      },
      onFotoError(event, foto) {
        if (foto && !event.target.getAttribute('data-tried-fotos')) {
          event.target.setAttribute('data-tried-fotos', '1');
          event.target.src = '/fotos/' + foto;
        } else if (foto && !event.target.getAttribute('data-tried-productos')) {
          event.target.setAttribute('data-tried-productos', '1');
          event.target.src = '/img/productos/' + foto;
        } else {
          event.target.src = '/img/avatar.png';
        }
      },
      desvincularHuella() {
        let me = this;
        if (!me.empleado.id) return;
        swal({
          title: '¿Desvincular Huella?',
          text: '¿Desea eliminar la huella dactilar de ' + me.empleado.nombre + ' ' + (me.empleado.apellido || '') + '?',
          icon: 'warning',
          buttons: ['Cancelar', 'Sí, desvincular'],
          dangerMode: true
        }).then((willDelete) => {
          if (willDelete) {
            axios.post('/asistencia/eliminar-huella', { empleado_id: me.empleado.id }).then(() => {
              me.empleado.huella_dactilar = null;
              swal('Huella Eliminada', 'La huella ha sido desvinculada exitosamente.', 'success');
            }).catch(err => {
              swal('Error', 'No se pudo eliminar la huella.', 'error');
            });
          }
        });
      },
    },
    mounted() {
        if (!this.edit && (!this.empleado.auxilio_transporte || parseFloat(this.empleado.auxilio_transporte) === 0)) {
            axios.get('/ajustes/nomina-config').then(response => {
                if (response.data && response.data.auxilio_transporte) {
                    this.empleado.auxilio_transporte = parseFloat(response.data.auxilio_transporte);
                }
            }).catch(e => {});
        }
    }
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .grid-form {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 20px;
  }
  
  .form-group {
    display: flex;
    flex-direction: column;
  }
  
  .form-group label {
    font-weight: bold;
    margin-bottom: 5px;
  }
  
  .form-group input,
  .form-group select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  
  .full-width {
    grid-column: span 3;
  }
  
  
  
 
  </style>
  