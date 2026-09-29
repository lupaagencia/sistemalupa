<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Plan de Cuentas (PUC)</li>
        </ol>

        <div class="container-fluid">
            <!-- Main Card -->
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white p-2 rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fa fa-book fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-dark font-weight-bold">Plan Único de Cuentas (PUC)</h5>
                            <small class="text-muted">Gestión de la estructura contable de la empresa</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary px-4 shadow-sm font-weight-bold" @click="abrirModal('registrar')">
                        <i class="fa fa-plus-circle mr-2"></i>Nueva Cuenta
                    </button>
                </div>

                <div class="card-body bg-light-gray">
                    <!-- Filters Row -->
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <div class="input-group shadow-sm bg-white rounded">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" v-model="buscar" @keyup.enter="listarCuentas()" class="form-control border-0" placeholder="Buscar por código o nombre...">
                                <div class="input-group-append">
                                    <button type="button" @click="listarCuentas()" class="btn btn-primary px-4 font-weight-bold">Buscar</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control shadow-sm border-0 bg-white" v-model="filtroTipo" @change="listarCuentas()">
                                <option value="">Todos los tipos</option>
                                <option value="Activo">Activos</option>
                                <option value="Pasivo">Pasivos</option>
                                <option value="Patrimonio">Patrimonio</option>
                                <option value="Ingreso">Ingresos</option>
                                <option value="Gasto">Gastos</option>
                                <option value="Costo">Costos</option>
                            </select>
                        </div>
                        <div class="col-md-2" v-if="buscar || filtroTipo">
                            <button type="button" @click="limpiarFiltros()" class="btn btn-outline-secondary w-100 font-weight-bold border-0 bg-white shadow-sm">
                                <i class="fa fa-times-circle mr-2"></i>Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Accounts List -->
                    <div class="table-responsive shadow-sm bg-white rounded-lg">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="py-3 px-4 text-secondary font-weight-bold" style="width: 250px;">Código</th>
                                    <th class="py-3 text-secondary font-weight-bold">Nombre</th>
                                    <th class="py-3 text-secondary font-weight-bold" style="width: 150px;">Tipo</th>
                                    <th class="py-3 text-secondary font-weight-bold" style="width: 150px;">Naturaleza</th>
                                    <th class="py-3 text-secondary font-weight-bold text-center" style="width: 150px;">¿Detalle?</th>
                                    <th class="py-3 text-secondary font-weight-bold text-center" style="width: 180px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="arrayCuentas.length === 0">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa fa-info-circle fa-2x mb-3 text-secondary"></i>
                                        <p class="mb-0 font-weight-bold">No se encontraron cuentas contables</p>
                                        <small>Intenta buscando con otro término o crea una nueva cuenta</small>
                                    </td>
                                </tr>
                                <tr v-for="cuenta in arrayCuentas" :key="cuenta.id" :class="getRowClass(cuenta)">
                                    <!-- Código with hierarchy indentation -->
                                    <td class="py-3 px-4 font-weight-bold" :style="{ 'padding-left': getPadding(cuenta.codigo) }">
                                        <span class="code-span" :class="getCodeBadgeClass(cuenta)">
                                            {{ cuenta.codigo }}
                                        </span>
                                    </td>
                                    <!-- Nombre -->
                                    <td class="py-3" :class="{'font-weight-bold': getDepth(cuenta.codigo) < 3}">
                                        {{ cuenta.nombre }}
                                    </td>
                                    <!-- Tipo -->
                                    <td class="py-3">
                                        <span class="badge badge-pill px-3 py-1 font-weight-bold" :class="getTipoBadgeClass(cuenta.tipo)">
                                            {{ cuenta.tipo }}
                                        </span>
                                    </td>
                                    <!-- Naturaleza -->
                                    <td class="py-3">
                                        <span class="badge badge-pill px-3 py-1 font-weight-bold" :class="getNaturalezaBadgeClass(cuenta.naturaleza)">
                                            {{ cuenta.naturaleza }}
                                        </span>
                                    </td>
                                    <!-- Es Detalle -->
                                    <td class="py-3 text-center">
                                        <span v-if="cuenta.es_detalle" class="badge badge-success px-2 py-1 shadow-sm font-weight-bold">
                                            <i class="fa fa-check-circle mr-1"></i>Acepta Asientos
                                        </span>
                                        <span v-else class="badge badge-light text-muted px-2 py-1 font-weight-bold border">
                                            <i class="fa fa-folder-open mr-1"></i>Acumuladora
                                        </span>
                                    </td>
                                    <!-- Acciones -->
                                    <td class="py-3 text-center">
                                        <button type="button" class="btn btn-warning btn-sm shadow-sm text-dark mr-1 font-weight-bold" @click="abrirModal('actualizar', cuenta)">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm shadow-sm font-weight-bold" @click="eliminarCuenta(cuenta)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal fade" :class="{'mostrar': modal}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg shadow-lg border-0" role="document">
                <div class="modal-content border-0 rounded-lg">
                    <div class="modal-header bg-primary text-white py-3 border-bottom-0">
                        <h5 class="modal-title font-weight-bold" v-text="tituloModal"></h5>
                        <button type="button" class="close text-white opacity-10" @click="cerrarModal()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="row">
                                <!-- Cuenta Padre -->
                                <div class="form-group col-md-12">
                                    <label class="form-control-label font-weight-bold text-dark" for="padre-input">Cuenta Padre Contable</label>
                                    <select class="form-control select-flat" v-model="cuentaPadreId" @change="onParentChange()">
                                        <option :value="null">Ninguna (Es una cuenta raíz o clase)</option>
                                        <option v-for="padre in arrayPadres" :key="padre.id" :value="padre.id">
                                            {{ padre.codigo }} - {{ padre.nombre }}
                                        </option>
                                    </select>
                                    <small class="text-muted">Si la cuenta que vas a registrar depende de otra (ej. Caja Menor depende de Caja), elígela aquí.</small>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <!-- Código -->
                                <div class="form-group col-md-6">
                                    <label class="form-control-label font-weight-bold text-dark" for="codigo-input">Código Contable <span class="text-danger">*</span></label>
                                    <input type="text" v-model="codigo" class="form-control text-uppercase" placeholder="Ej. 110505" maxlength="20">
                                    <small class="text-muted">Código único asignado en el PUC.</small>
                                </div>

                                <!-- Nombre -->
                                <div class="form-group col-md-6">
                                    <label class="form-control-label font-weight-bold text-dark" for="nombre-input">Nombre de la Cuenta <span class="text-danger">*</span></label>
                                    <input type="text" v-model="nombre" class="form-control" placeholder="Ej. Caja General">
                                    <small class="text-muted">Nombre descriptivo de la cuenta contable.</small>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <!-- Tipo -->
                                <div class="form-group col-md-6">
                                    <label class="form-control-label font-weight-bold text-dark" for="tipo-input">Tipo de Cuenta <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="tipo">
                                        <option value="Activo">Activo</option>
                                        <option value="Pasivo">Pasivo</option>
                                        <option value="Patrimonio">Patrimonio</option>
                                        <option value="Ingreso">Ingreso</option>
                                        <option value="Gasto">Gasto</option>
                                        <option value="Costo">Costo</option>
                                    </select>
                                </div>

                                <!-- Naturaleza -->
                                <div class="form-group col-md-6">
                                    <label class="form-control-label font-weight-bold text-dark" for="naturaleza-input">Naturaleza <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="naturaleza">
                                        <option value="Debito">Débito</option>
                                        <option value="Credito">Crédito</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Es Detalle switch -->
                                <div class="form-group col-md-12">
                                    <div class="custom-control custom-switch custom-control-lg bg-light p-3 rounded border">
                                        <input type="checkbox" class="custom-control-input" id="esDetalleSwitch" v-model="esDetalle">
                                        <label class="custom-control-label font-weight-bold text-dark ml-2" for="esDetalleSwitch" style="cursor: pointer;">
                                            ¿Es una cuenta de detalle?
                                        </label>
                                        <p class="mb-0 text-muted mt-1 small pl-4">
                                            Las **cuentas de detalle** son las que pueden recibir directamente los movimientos o asientos en comprobantes (ej. 110505 Caja General). 
                                            Las cuentas que no son de detalle actúan solo como acumuladoras del saldo de sus cuentas hijas (ej. 1105 Caja).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert inside modal for Validation Errors -->
                            <div v-show="errorCuenta" class="alert alert-danger shadow-sm mt-3 border-0">
                                <div class="font-weight-bold mb-1"><i class="fa fa-exclamation-triangle mr-2"></i>Por favor corrige los siguientes errores:</div>
                                <ul class="mb-0 pl-4">
                                    <li v-for="error in errorMostrarMsjCuenta" :key="error" v-text="error"></li>
                                </ul>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer bg-light py-3 border-top-0 rounded-bottom-lg">
                        <button type="button" class="btn btn-secondary font-weight-bold px-4" @click="cerrarModal()">Cerrar</button>
                        <button type="button" v-if="tipoAccion==1" class="btn btn-primary font-weight-bold px-4 shadow-sm" @click="registrarCuenta()">Guardar Cuenta</button>
                        <button type="button" v-if="tipoAccion==2" class="btn btn-success font-weight-bold px-4 shadow-sm" @click="actualizarCuenta()">Actualizar Cuenta</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    props: ['user'],
    data() {
        return {
            cuentaId: 0,
            codigo: '',
            nombre: '',
            tipo: 'Activo',
            naturaleza: 'Debito',
            esDetalle: true,
            cuentaPadreId: null,

            arrayCuentas: [],
            arrayPadres: [],
            modal: 0,
            tituloModal: '',
            tipoAccion: 0, // 1: Registrar, 2: Actualizar
            errorCuenta: 0,
            errorMostrarMsjCuenta: [],
            buscar: '',
            filtroTipo: ''
        }
    },
    methods: {
        listarCuentas() {
            let me = this;
            let url = '/cuentas-contables?buscar=' + me.buscar + '&tipo=' + me.filtroTipo;
            axios.get(url).then(function (response) {
                me.arrayCuentas = response.data.cuentas;
            })
            .catch(function (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar las cuentas contables.', 'error');
            });
        },
        cargarPadres() {
            let me = this;
            let url = '/cuentas-contables';
            axios.get(url).then(function (response) {
                // Only allow accounts that are NOT detail accounts to be parents
                me.arrayPadres = response.data.cuentas.filter(c => !c.es_detalle && c.id !== me.cuentaId);
            })
            .catch(function (error) {
                console.error(error);
            });
        },
        limpiarFiltros() {
            this.buscar = '';
            this.filtroTipo = '';
            this.listarCuentas();
        },
        onParentChange() {
            if (this.cuentaPadreId) {
                let parent = this.arrayPadres.find(p => p.id === this.cuentaPadreId);
                if (parent) {
                    this.tipo = parent.tipo;
                    this.naturaleza = parent.naturaleza;
                    // Auto prepend code if helpful
                    if (this.codigo.length === 0 || this.codigo.startsWith(parent.codigo)) {
                        this.codigo = parent.codigo;
                    }
                }
            }
        },
        getDepth(code) {
            if (!code) return 0;
            let len = code.trim().length;
            if (len <= 1) return 0; // Clase (1, 2)
            if (len === 2) return 1; // Grupo (11, 22)
            if (len === 4) return 2; // Cuenta (1105, 2205)
            return 3; // Subcuenta / Auxiliar (110505)
        },
        getPadding(code) {
            let depth = this.getDepth(code);
            return (depth * 22) + 'px';
        },
        getRowClass(cuenta) {
            let depth = this.getDepth(cuenta.codigo);
            if (depth === 0) return 'table-secondary border-bottom font-weight-bold';
            if (depth === 1) return 'bg-white font-weight-bold text-dark';
            if (depth === 2) return 'bg-white text-muted';
            return 'bg-white text-dark-gray';
        },
        getCodeBadgeClass(cuenta) {
            let depth = this.getDepth(cuenta.codigo);
            if (depth === 0) return 'badge badge-dark px-3 py-1';
            if (depth === 1) return 'badge badge-secondary px-2 py-1';
            if (depth === 2) return 'badge badge-light px-2 py-1 border';
            return 'text-primary font-weight-bold';
        },
        getTipoBadgeClass(tipo) {
            const classes = {
                'Activo': 'badge-success text-white',
                'Pasivo': 'badge-danger text-white',
                'Patrimonio': 'badge-warning text-dark',
                'Ingreso': 'badge-info text-white',
                'Gasto': 'badge-primary text-white',
                'Costo': 'badge-dark text-white'
            };
            return classes[tipo] || 'badge-light';
        },
        getNaturalezaBadgeClass(nat) {
            return nat === 'Debito' ? 'badge-light text-primary border' : 'badge-light text-success border';
        },
        registrarCuenta() {
            if (this.validarCuenta()) {
                return;
            }

            let me = this;
            axios.post('/cuentas-contables/registrar', {
                'codigo': me.codigo.trim(),
                'nombre': me.nombre.trim(),
                'tipo': me.tipo,
                'naturaleza': me.naturaleza,
                'es_detalle': me.esDetalle ? 1 : 0,
                'padre_id': me.cuentaPadreId
            })
            .then(function (response) {
                me.cerrarModal();
                me.listarCuentas();
                Swal.fire('Guardado', 'La cuenta contable se registró con éxito.', 'success');
            })
            .catch(function (error) {
                if (error.response && error.response.status === 422) {
                    me.errorCuenta = 1;
                    me.errorMostrarMsjCuenta = Object.values(error.response.data.errors).flat();
                } else {
                    console.error(error);
                    Swal.fire('Error', 'Hubo un problema al guardar la cuenta.', 'error');
                }
            });
        },
        actualizarCuenta() {
            if (this.validarCuenta()) {
                return;
            }

            let me = this;
            axios.put('/cuentas-contables/actualizar/' + me.cuentaId, {
                'codigo': me.codigo.trim(),
                'nombre': me.nombre.trim(),
                'tipo': me.tipo,
                'naturaleza': me.naturaleza,
                'es_detalle': me.esDetalle ? 1 : 0,
                'padre_id': me.cuentaPadreId
            })
            .then(function (response) {
                me.cerrarModal();
                me.listarCuentas();
                Swal.fire('Actualizado', 'La cuenta contable se actualizó con éxito.', 'success');
            })
            .catch(function (error) {
                if (error.response && error.response.status === 422) {
                    me.errorCuenta = 1;
                    me.errorMostrarMsjCuenta = Object.values(error.response.data.errors).flat();
                } else {
                    console.error(error);
                    Swal.fire('Error', 'Hubo un problema al actualizar la cuenta.', 'error');
                }
            });
        },
        eliminarCuenta(cuenta) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-danger mx-2 px-4 font-weight-bold',
                    cancelButton: 'btn btn-secondary mx-2 px-4 font-weight-bold'
                },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: '¿Estás seguro de eliminar esta cuenta?',
                text: `Se eliminará la cuenta "${cuenta.codigo} - ${cuenta.nombre}". Esta acción no se puede deshacer si tiene registros asociados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let me = this;
                    axios.delete('/cuentas-contables/eliminar/' + cuenta.id)
                    .then(function (response) {
                        me.listarCuentas();
                        Swal.fire('Eliminado', 'La cuenta ha sido eliminada del PUC.', 'success');
                    })
                    .catch(function (error) {
                        if (error.response && error.response.status === 422) {
                            Swal.fire('No se puede eliminar', error.response.data.error, 'warning');
                        } else {
                            console.error(error);
                            Swal.fire('Error', 'No se pudo completar la eliminación.', 'error');
                        }
                    });
                }
            });
        },
        validarCuenta() {
            this.errorCuenta = 0;
            this.errorMostrarMsjCuenta = [];

            if (!this.codigo || this.codigo.trim() === '') {
                this.errorMostrarMsjCuenta.push('El código de la cuenta es obligatorio.');
            }
            if (!this.nombre || this.nombre.trim() === '') {
                this.errorMostrarMsjCuenta.push('El nombre de la cuenta es obligatorio.');
            }
            if (!this.tipo) {
                this.errorMostrarMsjCuenta.push('Debes seleccionar el tipo de cuenta.');
            }
            if (!this.naturaleza) {
                this.errorMostrarMsjCuenta.push('Debes seleccionar la naturaleza de la cuenta.');
            }

            if (this.errorMostrarMsjCuenta.length) {
                this.errorCuenta = 1;
            }
            return this.errorCuenta;
        },
        cerrarModal() {
            this.modal = 0;
            this.tituloModal = '';
            this.cuentaId = 0;
            this.codigo = '';
            this.nombre = '';
            this.tipo = 'Activo';
            this.naturaleza = 'Debito';
            this.esDetalle = true;
            this.cuentaPadreId = null;
            this.errorCuenta = 0;
            this.errorMostrarMsjCuenta = [];
        },
        abrirModal(accion, cuenta = null) {
            this.cargarPadres();
            this.errorCuenta = 0;
            this.errorMostrarMsjCuenta = [];

            if (accion === 'registrar') {
                this.cuentaId = 0;
                this.codigo = '';
                this.nombre = '';
                this.tipo = 'Activo';
                this.naturaleza = 'Debito';
                this.esDetalle = true;
                this.cuentaPadreId = null;

                this.tituloModal = 'Registrar Cuenta Contable';
                this.tipoAccion = 1;
                this.modal = 1;
            } else if (accion === 'actualizar') {
                this.cuentaId = cuenta.id;
                this.codigo = cuenta.codigo;
                this.nombre = cuenta.nombre;
                this.tipo = cuenta.tipo;
                this.naturaleza = cuenta.naturaleza;
                this.esDetalle = !!cuenta.es_detalle;
                this.cuentaPadreId = cuenta.padre_id;

                this.tituloModal = 'Actualizar Cuenta Contable';
                this.tipoAccion = 2;
                this.modal = 1;
            }
        }
    },
    mounted() {
        this.listarCuentas();
    }
}
</script>

<style scoped>
.modal-content {
    width: 100% !important;
    position: absolute !important;
}
.mostrar {
    display: list-item !important;
    opacity: 1 !important;
    position: fixed !important;
    background-color: rgba(60, 41, 41, 0.48) !important;
    overflow-y: auto;
}
.bg-light-gray {
    background-color: #f8f9fa;
}
.code-span {
    letter-spacing: 0.5px;
}
.text-dark-gray {
    color: #495057;
}
.select-flat {
    background-color: #ffffff;
}
.custom-control-lg .custom-control-label::before, 
.custom-control-lg .custom-control-label::after {
    top: 0.1rem;
    left: -2rem;
    width: 1.5rem;
    height: 1.5rem;
}
.custom-switch.custom-control-lg {
    padding-left: 2.75rem;
}
</style>
