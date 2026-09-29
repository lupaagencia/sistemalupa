<template>
    <main class="main d-flex align-items-center justify-content-center py-4">
        <div class="card shadow-lg border-0" style="width: 100%; max-width: 440px; border-radius: 12px;">
            <div class="card-header text-white bg-primary text-center py-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h4 class="mb-0 font-weight-bold"><i class="fa fa-key mr-2"></i>Cambiar Contraseña</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted text-center small mb-4">
                    Ingresa tu contraseña actual y la nueva contraseña que deseas utilizar.
                </p>

                <div v-if="errorMsgs.length" class="alert alert-danger py-2 px-3 mb-3">
                    <ul class="mb-0 pl-3">
                        <li v-for="(err, idx) in errorMsgs" :key="idx" class="small">{{ err }}</li>
                    </ul>
                </div>

                <form @submit.prevent="cambiarPassword">
                    <div class="form-group mb-3">
                        <label for="password_actual" class="font-weight-semibold small text-secondary">Contraseña Anterior (*)</label>
                        <div class="input-group">
                            <input 
                                :type="mostrarPassActual ? 'text' : 'password'" 
                                id="password_actual" 
                                v-model="password_actual" 
                                class="form-control" 
                                placeholder="Ingresa tu contraseña actual"
                                required
                            />
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" @click="mostrarPassActual = !mostrarPassActual">
                                    <i :class="mostrarPassActual ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="font-weight-semibold small text-secondary">Nueva Contraseña (*)</label>
                        <div class="input-group">
                            <input 
                                :type="mostrarPassNueva ? 'text' : 'password'" 
                                id="password" 
                                v-model="password" 
                                class="form-control" 
                                placeholder="Mínimo 6 caracteres"
                                required
                            />
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" @click="mostrarPassNueva = !mostrarPassNueva">
                                    <i :class="mostrarPassNueva ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password_confirmation" class="font-weight-semibold small text-secondary">Confirmar Nueva Contraseña (*)</label>
                        <div class="input-group">
                            <input 
                                :type="mostrarPassNueva ? 'text' : 'password'" 
                                id="password_confirmation" 
                                v-model="password_confirmation" 
                                class="form-control" 
                                placeholder="Repite la nueva contraseña"
                                required
                            />
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" 
                        :disabled="cargando"
                    >
                        <i v-if="cargando" class="fa fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fa fa-check-circle mr-2"></i>
                        {{ cargando ? 'Guardando...' : 'Cambiar Contraseña' }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>

<script>
    export default {
        props: ['user', 'pass'],
        data() {
            return {
                password_actual: '',
                password: '',
                password_confirmation: '',
                mostrarPassActual: false,
                mostrarPassNueva: false,
                cargando: false,
                errorMsgs: []
            };
        },
        methods: {
            validarFormulario() {
                this.errorMsgs = [];
                if (!this.password_actual) {
                    this.errorMsgs.push('Ingresa tu contraseña actual.');
                }
                if (!this.password) {
                    this.errorMsgs.push('Ingresa la nueva contraseña.');
                } else if (this.password.length < 6) {
                    this.errorMsgs.push('La nueva contraseña debe tener al menos 6 caracteres.');
                }
                if (this.password !== this.password_confirmation) {
                    this.errorMsgs.push('La confirmación de la contraseña no coincide.');
                }
                return this.errorMsgs.length === 0;
            },
            cambiarPassword() {
                if (!this.validarFormulario()) {
                    return;
                }

                this.cargando = true;
                axios.post('/user/cambiar-password', {
                    password_actual: this.password_actual,
                    password: this.password,
                    password_confirmation: this.password_confirmation
                })
                .then(response => {
                    this.cargando = false;
                    Swal.fire(
                        '¡Éxito!',
                        response.data.message || 'La contraseña ha sido actualizada correctamente.',
                        'success'
                    );
                    this.password_actual = '';
                    this.password = '';
                    this.password_confirmation = '';
                    this.errorMsgs = [];
                })
                .catch(error => {
                    this.cargando = false;
                    let msg = 'Ocurrió un error al intentar cambiar la contraseña.';
                    if (error.response && error.response.data) {
                        if (error.response.data.message) {
                            msg = error.response.data.message;
                        } else if (error.response.data.errors) {
                            const errs = error.response.data.errors;
                            msg = Object.values(errs).flatMap(e => e).join(' ');
                        }
                    }
                    Swal.fire('Error', msg, 'error');
                });
            }
        }
    };
</script>

<style scoped>
    .main {
        min-height: 80vh;
    }
</style>
