<template>
    <div class="crm-bases-datos">
        <!-- Sub-Navigation Header -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="btn-group shadow-sm">
                    <button class="btn btn-sm" :class="subTab === 'listas' ? 'btn-primary font-weight-bold' : 'btn-white text-secondary'" @click="subTab = 'listas'">
                        <i class="fa fa-database mr-1"></i>Bases & Nichos ({{ paginationBases.total || 0 }})
                    </button>
                    <button class="btn btn-sm" :class="subTab === 'contactos' ? 'btn-primary font-weight-bold' : 'btn-white text-secondary'" @click="subTab = 'contactos'">
                        <i class="fa fa-address-book-o mr-1"></i>Todos los Contactos ({{ paginationContactos.total || 0 }})
                    </button>
                    <button class="btn btn-sm" :class="subTab === 'metricas' ? 'btn-primary font-weight-bold' : 'btn-white text-secondary'" @click="cargarEstadisticas(); subTab = 'metricas'">
                        <i class="fa fa-pie-chart mr-1"></i>Métricas de Prospección
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-md-right">
                <button class="btn btn-sm btn-outline-dark font-weight-bold mr-2 shadow-sm" @click="abrirModalCatalogos">
                    <i class="fa fa-tags mr-1"></i>Gestionar Nichos & Orígenes
                </button>
                <button class="btn btn-sm btn-success font-weight-bold mr-2 shadow-sm" @click="abrirModalImportar">
                    <i class="fa fa-file-excel-o mr-1"></i>Importar CSV / Excel
                </button>
                <button class="btn btn-sm btn-primary font-weight-bold shadow-sm" @click="abrirModalBase('crear')">
                    <i class="fa fa-plus-circle mr-1"></i>Nueva Base de Datos
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 1: BASES DE DATOS & NICHOS -->
        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <div v-if="subTab === 'listas'">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" v-model="buscarBase" @keyup.enter="cargarBases(1)" class="form-control border-left-0" placeholder="Buscar base por nombre, sector u origen..." />
                            </div>
                        </div>
                        <div class="col-md-8 text-md-right">
                            <span class="badge badge-light p-2 mr-2 border">
                                <i class="fa fa-filter text-primary mr-1"></i>Sectores Populares: 
                                <span v-for="sec in sectoresList.slice(0, 5)" :key="sec.id" class="badge badge-pill badge-primary ml-1" @click="buscarBase = sec.nombre; cargarBases(1)" style="cursor:pointer">
                                    {{ sec.nombre }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Databases Table -->
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-secondary font-weight-bold">
                            <tr>
                                <th>Nombre de la Base</th>
                                <th>Sector / Nicho</th>
                                <th>Origen de Datos</th>
                                <th>Vendedor Propietario</th>
                                <th class="text-center">Contactos</th>
                                <th class="text-center">Portafolio Enviado</th>
                                <th class="text-center">Interesados</th>
                                <th class="text-center">Convertidos</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="base in bases" :key="base.id">
                                <td>
                                    <div class="font-weight-bold text-dark">{{ base.nombre }}</div>
                                    <div class="small text-muted" v-if="base.descripcion">{{ base.descripcion }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-primary px-2 py-1">{{ base.sector || 'General' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light border"><i class="fa fa-globe mr-1 text-muted"></i>{{ base.origen || 'Cámara de Comercio' }}</span>
                                </td>
                                <td>
                                    <span class="small font-weight-bold text-secondary">
                                        <i class="fa fa-user-circle-o mr-1"></i>{{ base.user ? base.user.usuario : 'Sin Asignar' }}
                                    </span>
                                </td>
                                <td class="text-center font-weight-bold">{{ base.total_contactos || 0 }}</td>
                                <td class="text-center">
                                    <span class="badge badge-pill" :class="(base.portafolios_enviados || 0) > 0 ? 'badge-success' : 'badge-light'">
                                        {{ base.portafolios_enviados || 0 }}
                                    </span>
                                </td>
                                <td class="text-center font-weight-bold text-info">{{ base.interesados || 0 }}</td>
                                <td class="text-center font-weight-bold text-success">{{ base.convertidos || 0 }}</td>
                                <td class="text-right">
                                    <button class="btn btn-xs btn-outline-primary mr-1" @click="verContactosBase(base)" title="Ver contactos de esta base">
                                        <i class="fa fa-eye mr-1"></i>Contactos
                                    </button>
                                    <button class="btn btn-xs btn-outline-success mr-1" @click="abrirModalContacto('crear', base)" title="Agregar contacto manual">
                                        <i class="fa fa-user-plus"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-secondary mr-1" @click="abrirModalBase('editar', base)">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-danger" @click="eliminarBase(base)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="bases.length === 0">
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa fa-database fa-2x mb-2 text-muted"></i>
                                    <p class="mb-0">No hay bases de datos creadas aún. ¡Crea una nueva base o importa un archivo CSV!</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 2: TODOS LOS CONTACTOS & SEGUIMIENTO OUTBOUND -->
        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <div v-if="subTab === 'contactos'">
            <!-- Filters Bar -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <input type="text" v-model="buscarContacto" @keyup.enter="cargarContactos(1)" class="form-control form-control-sm" placeholder="Empresa, contacto, email, teléfono..." />
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <select v-model="filtroBaseId" @change="cargarContactos(1)" class="form-control form-control-sm">
                                <option value="">-- Filtrar por Base de Datos --</option>
                                <option v-for="b in basesList" :key="b.id" :value="b.id">{{ b.nombre }} ({{ b.sector }})</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select v-model="filtroEstadoGestion" @change="cargarContactos(1)" class="form-control form-control-sm">
                                <option value="">-- Estado Gestión --</option>
                                <option value="Sin Contactar">Sin Contactar</option>
                                <option value="Contactado">Contactado</option>
                                <option value="Portafolio Enviado">Portafolio Enviado</option>
                                <option value="Información Enviada">Información Enviada</option>
                                <option value="Interesado">Interesado</option>
                                <option value="Convertido a Prospecto">Convertido a Prospecto</option>
                                <option value="Descartado">Descartado</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select v-model="filtroPortafolioEnviado" @change="cargarContactos(1)" class="form-control form-control-sm">
                                <option value="">-- Portafolio Enviado? --</option>
                                <option value="true">Sí Enviado</option>
                                <option value="false">No Enviado</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-md-right">
                            <button class="btn btn-sm btn-success font-weight-bold mr-1" @click="abrirModalContacto('crear')">
                                <i class="fa fa-plus mr-1"></i>Contacto
                            </button>
                            <button class="btn btn-sm btn-info text-white font-weight-bold" :disabled="selectedContactoIds.length === 0" @click="abrirModalRegistrarPortafolioMasivo">
                                <i class="fa fa-send mr-1"></i>Portafolio ({{ selectedContactoIds.length }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contacts Table -->
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-secondary font-weight-bold">
                            <tr>
                                <th width="3%" class="text-center">
                                    <input type="checkbox" @change="toggleSelectAllContactos" :checked="isAllSelected" />
                                </th>
                                <th>Empresa & Contacto</th>
                                <th>Base de Datos / Nicho</th>
                                <th>Teléfono / Email</th>
                                <th>Ciudad</th>
                                <th class="text-center">Portafolio</th>
                                <th class="text-center">Estado Gestión</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="contacto in contactos" :key="contacto.id">
                                <td class="text-center">
                                    <input type="checkbox" :value="contacto.id" v-model="selectedContactoIds" />
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ contacto.empresa }}</div>
                                    <div class="small text-muted" v-if="contacto.contacto_nombre">
                                        <i class="fa fa-user mr-1"></i>{{ contacto.contacto_nombre }} <span v-if="contacto.cargo">({{ contacto.cargo }})</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small font-weight-bold text-primary">{{ contacto.base_datos ? contacto.base_datos.nombre : 'General' }}</div>
                                    <span class="badge badge-light border small">{{ contacto.sector || 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="small"><i class="fa fa-phone mr-1 text-success"></i>{{ contacto.telefono || 'N/A' }}</div>
                                    <div class="small text-muted" v-if="contacto.email"><i class="fa fa-envelope-o mr-1"></i>{{ contacto.email }}</div>
                                </td>
                                <td>{{ contacto.ciudad || 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge px-2 py-1" :class="contacto.portafolio_enviado ? 'badge-success' : 'badge-secondary'">
                                        <i class="fa" :class="contacto.portafolio_enviado ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                        {{ contacto.portafolio_enviado ? 'Enviado' : 'Pendiente' }}
                                    </span>
                                    <div class="small text-muted" v-if="contacto.fecha_envio_portafolio">
                                        {{ formatDate(contacto.fecha_envio_portafolio) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge px-2 py-1" :class="getBadgeGestionClass(contacto.estado_gestion)">
                                        {{ contacto.estado_gestion }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-xs btn-outline-info mr-1" @click="abrirModalRegistrarPortafolioIndividual(contacto)" title="Registrar envío de portafolio">
                                        <i class="fa fa-send"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-success mr-1" v-if="!contacto.prospecto_id" @click="convertirAProspecto(contacto)" title="Convertir a Prospecto CRM">
                                        <i class="fa fa-user-plus"></i> Prospecto
                                    </button>
                                    <span v-else class="badge badge-success small mr-1"><i class="fa fa-check"></i> Convertido</span>
                                    <button class="btn btn-xs btn-outline-secondary" @click="abrirModalContacto('editar', null, contacto)">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="contactos.length === 0">
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No se encontraron contactos con los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 3: MÉTRICAS DE PROSPECTACIÓN DE BASES -->
        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <div v-if="subTab === 'metricas'">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm bg-white p-3 text-center" style="border-radius:10px;">
                        <span class="text-muted small font-weight-bold text-uppercase">Total Contactos Cargados</span>
                        <h3 class="font-weight-bold text-primary mb-0 mt-1">{{ metricasKpis.totalContactos || 0 }}</h3>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm bg-white p-3 text-center" style="border-radius:10px;">
                        <span class="text-muted small font-weight-bold text-uppercase">Portafolios Enviados</span>
                        <h3 class="font-weight-bold text-success mb-0 mt-1">{{ metricasKpis.portafoliosEnviados || 0 }}</h3>
                        <small class="text-success font-weight-bold">{{ metricasKpis.tasaPortafolio || 0 }}% Cobertura</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm bg-white p-3 text-center" style="border-radius:10px;">
                        <span class="text-muted small font-weight-bold text-uppercase">Contactos Interesados</span>
                        <h3 class="font-weight-bold text-info mb-0 mt-1">{{ metricasKpis.interesados || 0 }}</h3>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm bg-white p-3 text-center" style="border-radius:10px;">
                        <span class="text-muted small font-weight-bold text-uppercase">Convertidos a Prospectos</span>
                        <h3 class="font-weight-bold text-purple mb-0 mt-1" style="color: #933B8F;">{{ metricasKpis.convertidos || 0 }}</h3>
                        <small class="font-weight-bold" style="color: #933B8F;">{{ metricasKpis.tasaConversion || 0 }}% Efectividad</small>
                    </div>
                </div>
            </div>

            <!-- Metrics By Sector -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">
                    <i class="fa fa-bar-chart text-primary mr-2"></i>Desempeño de Prospección por Sector / Nicho
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small font-weight-bold">
                                <tr>
                                    <th>Sector / Nicho</th>
                                    <th class="text-center">Total Contactos</th>
                                    <th class="text-center">Portafolios Enviados</th>
                                    <th>Porcentaje de Cobertura</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sec in metricasPorSector" :key="sec.sector">
                                    <td class="font-weight-bold">{{ sec.sector || 'Sin Especificar' }}</td>
                                    <td class="text-center font-weight-bold">{{ sec.total }}</td>
                                    <td class="text-center font-weight-bold text-success">{{ sec.portafolios }}</td>
                                    <td>
                                        <div class="progress" style="height: 18px;">
                                            <div class="progress-bar bg-success font-weight-bold" role="progressbar" :style="{ width: getPercent(sec.portafolios, sec.total) + '%' }">
                                                {{ getPercent(sec.portafolios, sec.total) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════════ -->
        <!-- MODALES -->
        <!-- ═══════════════════════════════════════════════════════════════════════ -->

        <!-- Modal Nueva/Editar Base -->
        <div class="modal fade" id="modalBaseDatos" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">{{ modoBase === 'crear' ? 'Nueva Base de Datos' : 'Editar Base de Datos' }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarBase">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="font-weight-semibold">Nombre de la Base (*)</label>
                                <input type="text" v-model="formBase.nombre" class="form-control" placeholder="Ej: Restaurantes Z-Norte Cámara Comercio" required />
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Sector / Nicho (*)</label>
                                    <select v-model="formBase.sector" class="form-control" required>
                                        <option v-for="sec in sectoresList" :key="sec.id" :value="sec.nombre">{{ sec.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Origen de Datos</label>
                                    <select v-model="formBase.origen" class="form-control">
                                        <option v-for="ori in origenesList" :key="ori.id" :value="ori.nombre">{{ ori.nombre }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" v-if="userRole !== 'Vendedor'">
                                <label class="font-weight-semibold">Vendedor Propietario</label>
                                <select v-model="formBase.user_id" class="form-control">
                                    <option v-for="u in equipo" :key="u.id" :value="u.id">{{ u.usuario }} ({{ u.idrol }})</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Descripción / Observaciones</label>
                                <textarea v-model="formBase.descripcion" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary font-weight-bold">Guardar Base</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Crear/Editar Contacto Manual -->
        <div class="modal fade" id="modalContacto" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">{{ modoContacto === 'crear' ? 'Nuevo Contacto en Base de Datos' : 'Editar Contacto' }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarContacto">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="font-weight-semibold">Base de Datos de Destino (*)</label>
                                <select v-model="formContacto.base_datos_id" class="form-control" required>
                                    <option value="">-- Seleccionar Base de Datos --</option>
                                    <option v-for="b in basesList" :key="b.id" :value="b.id">{{ b.nombre }} ({{ b.sector }})</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Nombre de la Empresa (*)</label>
                                    <input type="text" v-model="formContacto.empresa" class="form-control" required />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Persona de Contacto</label>
                                    <input type="text" v-model="formContacto.contacto_nombre" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Cargo del Contacto</label>
                                    <input type="text" v-model="formContacto.cargo" class="form-control" placeholder="Ej: Gerente de Compras" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Sector / Nicho</label>
                                    <select v-model="formContacto.sector" class="form-control">
                                        <option v-for="sec in sectoresList" :key="sec.id" :value="sec.nombre">{{ sec.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Teléfono / Celular</label>
                                    <input type="text" v-model="formContacto.telefono" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Correo Electrónico</label>
                                    <input type="email" v-model="formContacto.email" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Ciudad</label>
                                    <input type="text" v-model="formContacto.ciudad" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Dirección</label>
                                    <input type="text" v-model="formContacto.direccion" class="form-control" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Estado de Gestión</label>
                                    <select v-model="formContacto.estado_gestion" class="form-control">
                                        <option value="Sin Contactar">Sin Contactar</option>
                                        <option value="Contactado">Contactado</option>
                                        <option value="Portafolio Enviado">Portafolio Enviado</option>
                                        <option value="Información Enviada">Información Enviada</option>
                                        <option value="Interesado">Interesado</option>
                                        <option value="Descartado">Descartado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group d-flex align-items-center mt-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" v-model="formContacto.portafolio_enviado" class="custom-control-input" id="checkPortafolioEnv" />
                                        <label class="custom-control-label font-weight-bold" for="checkPortafolioEnv">¿Portafolio Ya Enviado?</label>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Resultado / Notas de la Gestión</label>
                                    <textarea v-model="formContacto.resultado_gestion" class="form-control" rows="2" placeholder="Notas de llamadas, correos o acuerdos previos..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success font-weight-bold">Guardar Contacto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Importar CSV / Excel -->
        <div class="modal fade" id="modalImportar" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-file-excel-o mr-2"></i>Importación Masiva de Contactos (CSV / Texto)</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">1. Seleccionar Base de Destino (*)</label>
                            <select v-model="importarBaseId" class="form-control" required>
                                <option value="">-- Seleccionar Base de Datos --</option>
                                <option v-for="b in basesList" :key="b.id" :value="b.id">{{ b.nombre }} ({{ b.sector }})</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">2. Cargar Archivo CSV o Pegar Datos Separados por Comas / Punto y Coma</label>
                            <input type="file" ref="fileCsv" @change="leerArchivoCsv" accept=".csv, .txt" class="form-control-file mb-2" />
                            <textarea v-model="importarTexto" @input="procesarTextoCsv" class="form-control" rows="6" placeholder="Formatos soportados: Empresa, Contacto, Sector, Teléfono, Email, Ciudad&#10;Ejemplo:&#10;Restaurante El Sabor, Juan Pérez, Restaurantes, 3101234567, contacto@elsabor.com, Bogotá"></textarea>
                            <small class="text-muted">Primera línea puede incluir encabezados: empresa, contacto_nombre, sector, telefono, email, ciudad, direccion</small>
                        </div>

                        <!-- Preview Table -->
                        <div v-if="contactosPrevisualizados.length > 0" class="mt-3">
                            <h6 class="font-weight-bold text-success"><i class="fa fa-check-circle mr-1"></i>Vista Previa de los Primeros 5 Registros (Total: {{ contactosPrevisualizados.length }})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Empresa</th>
                                            <th>Contacto</th>
                                            <th>Sector</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Ciudad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(c, idx) in contactosPrevisualizados.slice(0, 5)" :key="idx">
                                            <td>{{ c.empresa }}</td>
                                            <td>{{ c.contacto_nombre }}</td>
                                            <td>{{ c.sector }}</td>
                                            <td>{{ c.telefono }}</td>
                                            <td>{{ c.email }}</td>
                                            <td>{{ c.ciudad }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success font-weight-bold" :disabled="contactosPrevisualizados.length === 0 || !importarBaseId || cargandoImportacion" @click="ejecutarImportacion">
                            {{ cargandoImportacion ? 'Importando...' : 'Importar ' + contactosPrevisualizados.length + ' Contactos' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Registrar Portafolio -->
        <div class="modal fade" id="modalPortafolio" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-send mr-2"></i>Registrar Envío de Portafolio</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <form @submit.prevent="guardarPortafolio">
                        <div class="modal-body">
                            <p class="small text-muted mb-2">
                                Se registrará el envío de portafolio/catálogo para <strong>{{ targetContactoIds.length }}</strong> contacto(s).
                            </p>
                            <div class="form-group">
                                <label class="font-weight-semibold">Método / Canal de Envío (*)</label>
                                <select v-model="formPortafolio.metodo_envio" class="form-control" required>
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="Email / Correo Electrónico">Email / Correo Electrónico</option>
                                    <option value="Presencial / Visita Comercial">Presencial / Visita Comercial</option>
                                    <option value="Llamada Telefónica">Llamada Telefónica</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-semibold">Notas / Observaciones de la Gestión</label>
                                <textarea v-model="formPortafolio.notas" class="form-control" rows="3" placeholder="Ej: Se envió el portafolio digital en PDF por WhatsApp. Quedamos de llamar el viernes."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-info text-white font-weight-bold">Guardar Envío</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Administrar Nichos & Orígenes -->
        <div class="modal fade" id="modalCatalogos" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-tags mr-2"></i>Administrar Nichos (Sectores) y Orígenes de Datos</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Column 1: Sectores / Nichos -->
                            <div class="col-md-6 border-right">
                                <h6 class="font-weight-bold text-primary mb-2"><i class="fa fa-folder-open mr-1"></i>Nichos / Sectores Comerciales</h6>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" v-model="nuevoNichoNombre" class="form-control" placeholder="Ej: Farmacias, Hoteles..." />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary font-weight-bold" @click="crearCatalogItem('crm_nicho', nuevoNichoNombre)">
                                            <i class="fa fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                                <ul class="list-group list-group-flush border rounded" style="max-height: 250px; overflow-y: auto;">
                                    <li v-for="sec in sectoresList" :key="sec.id" class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span>{{ sec.nombre }}</span>
                                        <div>
                                            <button class="btn btn-xs btn-outline-secondary mr-1" @click="editarCatalogItem(sec)">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger" @click="eliminarCatalogItem(sec.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Column 2: Orígenes de Datos -->
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-info mb-2"><i class="fa fa-globe mr-1"></i>Orígenes de Datos / Canales</h6>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" v-model="nuevoOrigenNombre" class="form-control" placeholder="Ej: LinkedIn, Feria Empresarial..." />
                                    <div class="input-group-append">
                                        <button class="btn btn-info text-white font-weight-bold" @click="crearCatalogItem('crm_origen', nuevoOrigenNombre)">
                                            <i class="fa fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                                <ul class="list-group list-group-flush border rounded" style="max-height: 250px; overflow-y: auto;">
                                    <li v-for="ori in origenesList" :key="ori.id" class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span>{{ ori.nombre }}</span>
                                        <div>
                                            <button class="btn btn-xs btn-outline-secondary mr-1" @click="editarCatalogItem(ori)">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger" @click="eliminarCatalogItem(ori.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['user', 'userRole', 'vendedorId', 'equipo'],
        data() {
            return {
                subTab: 'listas',
                bases: [],
                basesList: [],
                contactos: [],
                sectoresList: [],
                origenesList: [],
                paginationBases: {},
                paginationContactos: {},
                buscarBase: '',
                buscarContacto: '',
                filtroBaseId: '',
                filtroEstadoGestion: '',
                filtroPortafolioEnviado: '',
                selectedContactoIds: [],
                
                // Form Base
                modoBase: 'crear',
                formBase: {
                    id: null,
                    nombre: '',
                    sector: 'Restaurantes',
                    origen: 'Cámara de Comercio',
                    descripcion: '',
                    user_id: this.user ? this.user.id : null
                },

                // Form Contacto
                modoContacto: 'crear',
                formContacto: {
                    id: null,
                    base_datos_id: '',
                    empresa: '',
                    contacto_nombre: '',
                    cargo: '',
                    sector: '',
                    telefono: '',
                    email: '',
                    ciudad: '',
                    direccion: '',
                    origen_detalle: '',
                    estado_gestion: 'Sin Contactar',
                    portafolio_enviado: false,
                    resultado_gestion: ''
                },

                // Catalog Management
                nuevoNichoNombre: '',
                nuevoOrigenNombre: '',

                // Import CSV
                importarBaseId: '',
                importarTexto: '',
                contactosPrevisualizados: [],
                cargandoImportacion: false,

                // Portafolio Modal
                targetContactoIds: [],
                formPortafolio: {
                    metodo_envio: 'WhatsApp',
                    notas: ''
                },

                // Metricas
                metricasKpis: {},
                metricasPorSector: []
            };
        },
        computed: {
            isAllSelected() {
                return this.contactos.length > 0 && this.selectedContactoIds.length === this.contactos.length;
            }
        },
        methods: {
            cargarCatalogos() {
                axios.get('/crm/base-datos/catalogos')
                .then(response => {
                    this.sectoresList = response.data.sectores || [];
                    this.origenesList = response.data.origenes || [];
                    if (this.sectoresList.length > 0 && !this.formBase.sector) {
                        this.formBase.sector = this.sectoresList[0].nombre;
                    }
                    if (this.origenesList.length > 0 && !this.formBase.origen) {
                        this.formBase.origen = this.origenesList[0].nombre;
                    }
                });
            },
            abrirModalCatalogos() {
                this.nuevoNichoNombre = '';
                this.nuevoOrigenNombre = '';
                $('#modalCatalogos').modal('show');
            },
            crearCatalogItem(tipo, nombre) {
                if (!nombre || !nombre.trim()) return;
                axios.post('/crm/base-datos/catalogos/registrar', { tipo, nombre: nombre.trim() })
                .then(response => {
                    if (tipo === 'crm_nicho') this.nuevoNichoNombre = '';
                    if (tipo === 'crm_origen') this.nuevoOrigenNombre = '';
                    this.cargarCatalogos();
                });
            },
            editarCatalogItem(item) {
                Swal.fire({
                    title: 'Editar Nombre',
                    input: 'text',
                    inputValue: item.nombre,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar'
                }).then(result => {
                    if (result.value && result.value.trim()) {
                        axios.put('/crm/base-datos/catalogos/actualizar', { id: item.id, nombre: result.value.trim() })
                        .then(() => {
                            this.cargarCatalogos();
                        });
                    }
                });
            },
            eliminarCatalogItem(id) {
                Swal.fire({
                    title: '¿Eliminar elemento?',
                    text: 'Se eliminará de la lista de opciones.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar'
                }).then(result => {
                    if (result.value) {
                        axios.delete('/crm/base-datos/catalogos/eliminar', { data: { id } })
                        .then(() => {
                            this.cargarCatalogos();
                        });
                    }
                });
            },
            cargarBases(page = 1) {
                axios.get('/crm/base-datos', {
                    params: {
                        page: page,
                        vendedor_id: this.vendedorId,
                        buscar: this.buscarBase
                    }
                }).then(response => {
                    this.bases = response.data.bases.data || [];
                    this.paginationBases = response.data.bases;
                });
            },
            cargarBasesSelect() {
                axios.get('/crm/base-datos/select', {
                    params: { vendedor_id: this.vendedorId }
                }).then(response => {
                    this.basesList = response.data.bases || [];
                });
            },
            cargarContactos(page = 1) {
                axios.get('/crm/base-datos/contactos', {
                    params: {
                        page: page,
                        vendedor_id: this.vendedorId,
                        base_datos_id: this.filtroBaseId,
                        estado_gestion: this.filtroEstadoGestion,
                        portafolio_enviado: this.filtroPortafolioEnviado,
                        buscar: this.buscarContacto
                    }
                }).then(response => {
                    this.contactos = response.data.contactos.data || [];
                    this.paginationContactos = response.data.contactos;
                    this.selectedContactoIds = [];
                });
            },
            cargarEstadisticas() {
                axios.get('/crm/base-datos/estadisticas', {
                    params: { vendedor_id: this.vendedorId }
                }).then(response => {
                    this.metricasKpis = response.data.kpis || {};
                    this.metricasPorSector = response.data.porSector || [];
                });
            },
            abrirModalBase(modo, base = null) {
                this.modoBase = modo;
                if (modo === 'editar' && base) {
                    this.formBase = { ...base };
                } else {
                    this.formBase = {
                        id: null,
                        nombre: '',
                        sector: this.sectoresList.length > 0 ? this.sectoresList[0].nombre : 'Restaurantes',
                        origen: this.origenesList.length > 0 ? this.origenesList[0].nombre : 'Cámara de Comercio',
                        descripcion: '',
                        user_id: this.user ? this.user.id : null
                    };
                }
                $('#modalBaseDatos').modal('show');
            },
            guardarBase() {
                const url = this.modoBase === 'crear' ? '/crm/base-datos/registrar' : '/crm/base-datos/actualizar';
                const method = this.modoBase === 'crear' ? 'post' : 'put';

                axios[method](url, this.formBase)
                .then(response => {
                    $('#modalBaseDatos').modal('hide');
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    this.cargarBases(1);
                    this.cargarBasesSelect();
                });
            },
            eliminarBase(base) {
                Swal.fire({
                    title: '¿Eliminar base de datos?',
                    text: 'Se eliminarán todos los contactos asociados a esta base.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar'
                }).then(result => {
                    if (result.value) {
                        axios.delete('/crm/base-datos/eliminar', { data: { id: base.id } })
                        .then(response => {
                            Swal.fire('Eliminada', response.data.message, 'success');
                            this.cargarBases(1);
                        });
                    }
                });
            },
            verContactosBase(base) {
                this.filtroBaseId = base.id;
                this.subTab = 'contactos';
                this.cargarContactos(1);
            },

            // Contacto Modal
            abrirModalContacto(modo, base = null, contacto = null) {
                this.modoContacto = modo;
                if (modo === 'editar' && contacto) {
                    this.formContacto = { ...contacto };
                } else {
                    const defaultBaseId = base ? base.id : (this.filtroBaseId || (this.basesList.length > 0 ? this.basesList[0].id : ''));
                    const defaultSector = base ? base.sector : (this.sectoresList.length > 0 ? this.sectoresList[0].nombre : '');
                    this.formContacto = {
                        id: null,
                        base_datos_id: defaultBaseId,
                        empresa: '',
                        contacto_nombre: '',
                        cargo: '',
                        sector: defaultSector,
                        telefono: '',
                        email: '',
                        ciudad: '',
                        direccion: '',
                        origen_detalle: '',
                        estado_gestion: 'Sin Contactar',
                        portafolio_enviado: false,
                        resultado_gestion: ''
                    };
                }
                $('#modalContacto').modal('show');
            },
            guardarContacto() {
                const url = this.modoContacto === 'crear' ? '/crm/base-datos/contacto/registrar' : '/crm/base-datos/contacto/actualizar';
                const method = this.modoContacto === 'crear' ? 'post' : 'put';

                axios[method](url, this.formContacto)
                .then(response => {
                    $('#modalContacto').modal('hide');
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    this.cargarContactos(1);
                    this.cargarBases(1);
                }).catch(error => {
                    Swal.fire('Error', 'Por favor verifica los campos obligatorios.', 'error');
                });
            },

            // CSV import
            abrirModalImportar() {
                this.importarBaseId = this.filtroBaseId || (this.basesList.length > 0 ? this.basesList[0].id : '');
                this.importarTexto = '';
                this.contactosPrevisualizados = [];
                $('#modalImportar').modal('show');
            },
            leerArchivoCsv(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.importarTexto = e.target.result;
                    this.procesarTextoCsv();
                };
                reader.readAsText(file);
            },
            procesarTextoCsv() {
                if (!this.importarTexto.trim()) {
                    this.contactosPrevisualizados = [];
                    return;
                }

                const lines = this.importarTexto.trim().split('\n');
                const list = [];

                lines.forEach((line, index) => {
                    if (!line.trim()) return;
                    const parts = line.split(/[,;\t]/).map(p => p.trim());

                    // Ignore header line if matches keywords
                    if (index === 0 && (parts[0].toLowerCase().includes('empresa') || parts[0].toLowerCase().includes('nombre'))) {
                        return;
                    }

                    if (parts[0]) {
                        list.push({
                            empresa: parts[0] || '',
                            contacto_nombre: parts[1] || '',
                            sector: parts[2] || '',
                            telefono: parts[3] || '',
                            email: parts[4] || '',
                            ciudad: parts[5] || '',
                            direccion: parts[6] || ''
                        });
                    }
                });

                this.contactosPrevisualizados = list;
            },
            ejecutarImportacion() {
                this.cargandoImportacion = true;
                axios.post('/crm/base-datos/importar', {
                    base_datos_id: this.importarBaseId,
                    contactos: this.contactosPrevisualizados
                }).then(response => {
                    this.cargandoImportacion = false;
                    $('#modalImportar').modal('hide');
                    Swal.fire('¡Importación Exitosa!', response.data.message, 'success');
                    this.cargarBases(1);
                    this.cargarContactos(1);
                }).catch(error => {
                    this.cargandoImportacion = false;
                    Swal.fire('Error', 'Hubo un fallo al importar contactos.', 'error');
                });
            },
            toggleSelectAllContactos(e) {
                if (e.target.checked) {
                    this.selectedContactoIds = this.contactos.map(c => c.id);
                } else {
                    this.selectedContactoIds = [];
                }
            },
            abrirModalRegistrarPortafolioIndividual(contacto) {
                this.targetContactoIds = [contacto.id];
                this.formPortafolio.notas = '';
                $('#modalPortafolio').modal('show');
            },
            abrirModalRegistrarPortafolioMasivo() {
                this.targetContactoIds = [...this.selectedContactoIds];
                this.formPortafolio.notas = '';
                $('#modalPortafolio').modal('show');
            },
            guardarPortafolio() {
                axios.post('/crm/base-datos/registrar-portafolio', {
                    ids: this.targetContactoIds,
                    metodo_envio: this.formPortafolio.metodo_envio,
                    notas: this.formPortafolio.notas
                }).then(response => {
                    $('#modalPortafolio').modal('hide');
                    Swal.fire('¡Registrado!', response.data.message, 'success');
                    this.cargarContactos(1);
                    this.cargarBases(1);
                });
            },
            convertirAProspecto(contacto) {
                Swal.fire({
                    title: '¿Convertir en Prospecto CRM?',
                    text: `Se creará el prospecto "${contacto.empresa}" en la tubería de ventas.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, convertir'
                }).then(result => {
                    if (result.value) {
                        axios.post('/crm/base-datos/convertir-prospecto', { id: contacto.id })
                        .then(response => {
                            Swal.fire('¡Convertido!', response.data.message, 'success');
                            this.cargarContactos(1);
                            this.cargarBases(1);
                        });
                    }
                });
            },
            getBadgeGestionClass(estado) {
                switch(estado) {
                    case 'Portafolio Enviado': return 'badge-info';
                    case 'Información Enviada': return 'badge-primary';
                    case 'Interesado': return 'badge-warning text-dark';
                    case 'Convertido a Prospecto': return 'badge-success';
                    case 'Descartado': return 'badge-danger';
                    default: return 'badge-secondary';
                }
            },
            formatDate(val) {
                if (!val) return '';
                return val.toString().substring(0, 10);
            },
            getPercent(part, total) {
                if (!total || total === 0) return 0;
                return Math.round((part / total) * 100);
            }
        },
        watch: {
            vendedorId() {
                this.cargarBases(1);
                this.cargarBasesSelect();
                this.cargarContactos(1);
                if (this.subTab === 'metricas') {
                    this.cargarEstadisticas();
                }
            }
        },
        mounted() {
            this.cargarCatalogos();
            this.cargarBases(1);
            this.cargarBasesSelect();
            this.cargarContactos(1);
        }
    };
</script>

<style scoped>
    .badge-soft-primary {
        background-color: #e0f2fe;
        color: #0369a1;
    }
    .btn-white {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
    }
</style>
