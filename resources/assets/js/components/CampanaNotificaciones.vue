<template>
    <li class="nav-item d-md-down-none">
        <a class="nav-link" href="#" data-toggle="dropdown" @click.prevent="cargarAlertas">
            <i class="icon-bell"></i>
            <span class="badge badge-pill badge-danger" v-if="total > 0">{{ total }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow">
            <div class="dropdown-header text-center">
                <strong>Alertas de Cartera</strong>
            </div>
            <a class="dropdown-item" href="#" @click.prevent="irACartera('Completados')">
                <i class="fa fa-flag-checkered text-warning"></i> Completados
                <span class="badge badge-warning text-white">{{ completadosCount }}</span>
            </a>
            <a class="dropdown-item" href="#" @click.prevent="irACartera('Entregados')">
                <i class="fa fa-check-circle text-primary"></i> Entregados
                <span class="badge badge-primary text-white">{{ entregadosCount }}</span>
            </a>
        </div>
    </li>
</template>

<script>
export default {
    data() {
        return {
            completadosCount: 0,
            entregadosCount: 0
        }
    },
    computed: {
        total() {
            return this.completadosCount + this.entregadosCount;
        }
    },
    mounted() {
        this.cargarAlertas();
        // Recargar cada 5 minutos
        setInterval(this.cargarAlertas, 300000);
    },
    methods: {
        cargarAlertas() {
            let me = this;
            axios.get('/orden/alertaCartera').then(function(response) {
                me.completadosCount = response.data.completados.count;
                me.entregadosCount = response.data.entregados.count;
            }).catch(function (error) {
                console.log(error);
            });
        },
        irACartera(tabFiltro) {
            localStorage.setItem('filtroCarteraInicial', tabFiltro);
            if (this.$root && typeof this.$root.menu !== 'undefined') {
                this.$root.menu = 16;
            } else {
                window.location.reload();
            }
        }
    }
}
</script>
