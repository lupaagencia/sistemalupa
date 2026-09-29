<template>
    <main class="main">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
        </ol>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Bancos y Cajas
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body pb-0">
                                    <div class="text-value">$0</div>
                                    <div>Caja Mayor</div>
                                </div>
                                <div class="chart-wrapper mt-3" style="height:40px;"></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card text-white bg-info">
                                <div class="card-body pb-0">
                                    <div class="text-value">$0</div>
                                    <div>Caja Menor</div>
                                </div>
                                <div class="chart-wrapper mt-3" style="height:40px;"></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card text-white bg-success">
                                <div class="card-body pb-0">
                                    <div class="text-value">$0</div>
                                    <div>Bancos</div>
                                </div>
                                <div class="chart-wrapper mt-3" style="height:40px;"></div>
                            </div>
                        </div>
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
        }
    }
}
</script>
