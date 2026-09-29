<template>
    <main class="main p-3">
        <!-- Header Banner & Salesperson Selector -->
        <div class="card shadow-sm border-0 mb-3 bg-white" style="border-radius: 10px;">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <h4 class="mb-1 text-primary font-weight-bold">
                            <i class="fa fa-handshake-o mr-2"></i>CRM & Cotizaciones
                        </h4>
                        <p class="text-muted small mb-0">
                            Gestión integral de prospectos, embudo de ventas, cotizaciones y seguimiento comercial.
                        </p>
                    </div>

                    <!-- Role-based Vendedor Selector & Company Config Button -->
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                        <div v-if="userRole !== 'Vendedor'" class="form-inline">
                            <label class="mr-2 font-weight-bold text-secondary small">
                                <i class="fa fa-filter mr-1"></i>Ámbito de Vista:
                            </label>
                            <select class="form-control form-control-sm border-primary mr-2" v-model="selectedVendedor">
                                <option v-if="userRole === 'Gerente Comercial'" value="equipo">-- Todo Mi Equipo --</option>
                                <option v-if="userRole === 'Administrador' || userRole === 'Coordinador'" value="all">-- Todos los Usuarios --</option>
                                <option v-for="userItem in equipo" :key="userItem.id" :value="userItem.id">
                                    {{ userItem.usuario }} ({{ userItem.idrol }})
                                </option>
                            </select>

                            <button class="btn btn-sm btn-outline-secondary font-weight-bold" @click="abrirModalEmpresa" title="Configurar Datos de Empresa para Cotizaciones">
                                <i class="fa fa-cog mr-1"></i>Datos Empresa
                            </button>
                        </div>
                        <div v-else class="badge badge-primary px-3 py-2 font-weight-normal">
                            <i class="fa fa-user mr-1"></i>Vendedor: {{ user ? user.usuario : 'Yo' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs nav-tabs-modern mb-3" style="border-bottom: 2px solid #e2e8f0;">
            <li class="nav-item">
                <a class="nav-link font-weight-bold" :class="{ active: tab === 'dashboard' }" href="#" @click.prevent="tab = 'dashboard'">
                    <i class="fa fa-bar-chart mr-1"></i> Embudo & KPIs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" :class="{ active: tab === 'bases' }" href="#" @click.prevent="tab = 'bases'">
                    <i class="fa fa-database mr-1"></i> Bases & Nichos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" :class="{ active: tab === 'prospectos' }" href="#" @click.prevent="tab = 'prospectos'">
                    <i class="fa fa-users mr-1"></i> Prospectos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" :class="{ active: tab === 'oportunidades' }" href="#" @click.prevent="tab = 'oportunidades'">
                    <i class="fa fa-briefcase mr-1"></i> Oportunidades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" :class="{ active: tab === 'cotizaciones' }" href="#" @click.prevent="tab = 'cotizaciones'">
                    <i class="fa fa-file-text-o mr-1"></i> Cotizaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" :class="{ active: tab === 'actividades' }" href="#" @click.prevent="tab = 'actividades'">
                    <i class="fa fa-calendar-check-o mr-1"></i> Agenda & Tareas
                </a>
            </li>
        </ul>

        <!-- Tab Components -->
        <keep-alive>
            <component 
                :is="activeComponent" 
                :user="user" 
                :user-role="userRole"
                :vendedor-id="selectedVendedor" 
                :equipo="equipo"
            ></component>
        </keep-alive>

        <!-- Modal Configuración Datos de Empresa -->
        <div class="modal fade" id="modalEmpresaConfig" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-building mr-2"></i>Configuración de Datos de Empresa (Cotizaciones)</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarEmpresaConfig">
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                Estos datos aparecerán en los encabezados e impresiones de las cotizaciones en PDF.
                            </p>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Nombre de la Empresa (*)</label>
                                    <input type="text" v-model="empresaForm.nombre" class="form-control" required />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">NIT / Registro Fiscal (*)</label>
                                    <input type="text" v-model="empresaForm.nit" class="form-control" required />
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Eslogan / Subtítulo Comercial</label>
                                    <input type="text" v-model="empresaForm.slogan" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Teléfonos de Contacto</label>
                                    <input type="text" v-model="empresaForm.telefono" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Correo Electrónico Institucional</label>
                                    <input type="email" v-model="empresaForm.email" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Dirección</label>
                                    <input type="text" v-model="empresaForm.direccion" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Ruta del Logo Institucional</label>
                                    <input type="text" v-model="empresaForm.logo" class="form-control" placeholder="img/LOGO-LUPA.jpg" />
                                    <small class="text-muted">Ruta relativa dentro de la carpeta public (ej. img/LOGO-LUPA.jpg)</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary font-weight-bold" :disabled="cargandoEmpresa">
                                {{ cargandoEmpresa ? 'Guardando...' : 'Guardar Configuración' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    import CrmDashboard from './CrmDashboard.vue';
    import CrmBasesDatos from './CrmBasesDatos.vue';
    import CrmProspectos from './CrmProspectos.vue';
    import CrmOportunidades from './CrmOportunidades.vue';
    import CrmCotizaciones from './CrmCotizaciones.vue';
    import CrmActividades from './CrmActividades.vue';

    export default {
        props: ['user'],
        components: {
            CrmDashboard,
            CrmBasesDatos,
            CrmProspectos,
            CrmOportunidades,
            CrmCotizaciones,
            CrmActividades
        },
        data() {
            return {
                tab: 'dashboard',
                userRole: (this.user && this.user.idrol) ? this.user.idrol : 'Vendedor',
                selectedVendedor: 'equipo',
                equipo: [],
                cargandoEmpresa: false,
                empresaForm: {
                    nombre: 'EMPAQUES LUPA S.A.S.',
                    slogan: 'Soluciones Integrales en Empaques e Impresión',
                    nit: '900.123.456-7',
                    telefono: '(601) 123 4567 / 310 123 4567',
                    email: 'contacto@empaqueslupa.com',
                    direccion: 'Calle Principal # 12-34, Bogotá',
                    logo: 'img/LOGO-LUPA.jpg'
                }
            };
        },
        computed: {
            activeComponent() {
                switch(this.tab) {
                    case 'bases': return 'CrmBasesDatos';
                    case 'prospectos': return 'CrmProspectos';
                    case 'oportunidades': return 'CrmOportunidades';
                    case 'cotizaciones': return 'CrmCotizaciones';
                    case 'actividades': return 'CrmActividades';
                    default: return 'CrmDashboard';
                }
            }
        },
        methods: {
            cargarEquipo() {
                axios.get('/crm/equipo')
                .then(response => {
                    this.userRole = response.data.user_role;
                    this.equipo = response.data.equipo || [];

                    if (this.userRole === 'Vendedor') {
                        this.selectedVendedor = response.data.current_user_id;
                    } else if (this.userRole === 'Gerente Comercial') {
                        this.selectedVendedor = 'equipo';
                    } else {
                        this.selectedVendedor = 'all';
                    }
                });
            },
            abrirModalEmpresa() {
                axios.get('/crm/empresa-config')
                .then(response => {
                    if (response.data.config) {
                        this.empresaForm = { ...this.empresaForm, ...response.data.config };
                    }
                    $('#modalEmpresaConfig').modal('show');
                });
            },
            guardarEmpresaConfig() {
                this.cargandoEmpresa = true;
                axios.post('/crm/empresa-config', this.empresaForm)
                .then(response => {
                    this.cargandoEmpresa = false;
                    $('#modalEmpresaConfig').modal('hide');
                    Swal.fire('¡Guardado!', response.data.message, 'success');
                })
                .catch(error => {
                    this.cargandoEmpresa = false;
                    Swal.fire('Error', 'No se pudo guardar la configuración', 'error');
                });
            }
        },
        mounted() {
            this.cargarEquipo();
        }
    };
</script>

<style scoped>
    .nav-tabs-modern .nav-link {
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 18px;
    }
    .nav-tabs-modern .nav-link:hover {
        color: #2563eb;
    }
    .nav-tabs-modern .nav-link.active {
        color: #2563eb;
        background-color: transparent;
        border-bottom: 3px solid #2563eb;
    }
</style>
