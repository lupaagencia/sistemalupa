<template>
    <main class="main d-flex align-items-center justify-content-center py-4">
        <div class="card shadow-lg border-0" style="width: 100%; max-width: 440px; border-radius: 12px;">
            <div class="card-header text-white bg-primary text-center py-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h4 class="mb-0 font-weight-bold"><i class="fa fa-envelope mr-2"></i>Resetear Contraseña</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted text-center small mb-4">
                    Ingresa tu correo electrónico registrado y te enviaremos un enlace para restablecer tu contraseña.
                </p>

                <form @submit.prevent="resetear">
                    <div class="form-group mb-4">
                        <label for="email" class="font-weight-semibold small text-secondary">Correo Electrónico (*)</label>
                        <input 
                            type="email" 
                            id="email" 
                            v-model="email" 
                            class="form-control" 
                            placeholder="ejemplo@correo.com"
                            required
                        />
                    </div>

                    <button 
                        type="submit" 
                        class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" 
                        :disabled="cargando"
                    >
                        <i v-if="cargando" class="fa fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fa fa-paper-plane mr-2"></i>
                        {{ cargando ? 'Enviando...' : 'Enviar enlace de restablecimiento' }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>

<script>
    export default {
        props: ['user'],
        data() {
            return {
                email: (this.user && (this.user.email || this.user.user_email)) ? (this.user.email || this.user.user_email) : '',
                cargando: false
            };
        },
        methods: {
            resetear() {
                if (!this.email) {
                    Swal.fire('Atención', 'Por favor ingresa un correo electrónico válido.', 'warning');
                    return;
                }

                this.cargando = true;
                axios.post('password/email', { email: this.email })
                .then(response => {
                    this.cargando = false;
                    Swal.fire(
                        '¡Enviado!',
                        response.data.status || 'Hemos enviado por correo el enlace de restablecimiento de contraseña.',
                        'success'
                    );
                })
                .catch(error => {
                    this.cargando = false;
                    let msg = 'No se pudo enviar el correo de restablecimiento.';
                    if (error.response && error.response.data) {
                        if (error.response.data.email) {
                            msg = Array.isArray(error.response.data.email) ? error.response.data.email.join(' ') : error.response.data.email;
                        } else if (error.response.data.message) {
                            msg = error.response.data.message;
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
