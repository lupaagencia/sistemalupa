<template>
    <main class="main contenedor bg-light p-4">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Simulador y Centro de Aprendizaje Contable</li>
        </ol>

        <div class="container-fluid">
            <!-- Selector de Pestaña Principal (Guía vs. Simulador) -->
            <div class="card shadow-sm border-0 rounded-lg mb-4">
                <div class="card-header bg-dark text-white p-0">
                    <ul class="nav nav-tabs border-bottom-0" id="tourTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 text-white" :class="{'active bg-white text-dark': activeTab == 'guias'}" @click="activeTab = 'guias'">
                                <i class="fa fa-book mr-2"></i>Guía y Conceptos Contables
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 text-white" :class="{'active bg-white text-dark': activeTab == 'simulador'}" @click="activeTab = 'simulador'">
                                <i class="fa fa-calculator mr-2"></i>Simulador de Partida Doble
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- PESTAÑA 1: GUÍA DE CONCEPTOS CONTABLES -->
                <div v-if="activeTab == 'guias'" class="card-body p-4 bg-white">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-8">
                            <h4 class="font-weight-bold text-dark mb-2">Entendiendo la Lógica Contable de Lupa Agencia</h4>
                            <p class="text-muted leading-relaxed">
                                El sistema contable está estructurado en base a los principios internacionales de **Partida Doble (NIIF)**. 
                                Cada transacción en el software (crear un pedido, pagar una nómina, ajustar existencias) genera automáticamente un registro contable balanceado en el Libro Diario.
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-warning text-white font-weight-bold" @click="activeTab = 'simulador'">
                                <i class="fa fa-play mr-2"></i>Ir al Simulador en Vivo
                            </button>
                        </div>
                    </div>

                    <!-- Carrusel de Conceptos Clave -->
                    <div class="row">
                        <div v-for="(concept, index) in conceptsList" :key="index" class="col-md-4 mb-4">
                            <div class="concept-box p-3 rounded border h-100 hover-shadow transition">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="concept-icon-circle mr-3" :class="concept.bgColorClass">
                                        <i class="fa text-white" :class="concept.icon"></i>
                                    </div>
                                    <h6 class="font-weight-bold text-dark m-0">{{ concept.title }}</h6>
                                </div>
                                <p class="text-muted small mb-0 leading-relaxed">{{ concept.description }}</p>
                                <div class="mt-3 p-2 bg-light rounded text-muted font-weight-bold small text-center border-top">
                                    Cuenta clave: <span class="text-primary">{{ concept.keyAccount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 2: SIMULADOR DE PARTIDA DOBLE EN VIVO -->
                <div v-if="activeTab == 'simulador'" class="card-body p-4 bg-white">
                    <div class="row">
                        <!-- Columna Izquierda: Parámetros del Simulador -->
                        <div class="col-md-5">
                            <div class="bg-light p-4 rounded border mb-4">
                                <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-cog mr-2"></i>1. Configurar Transacción</h5>
                                
                                <!-- Selector de Transacción -->
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-muted small text-uppercase">Tipo de Operación</label>
                                    <select v-model="sim.tipo" @change="recalculateSimulation" class="form-control font-weight-bold text-primary">
                                        <option value="venta">Venta de Pedido con IVA</option>
                                        <option value="recaudo">Recibo de Caja / Abono Cliente</option>
                                        <option value="compra">Compra de Materia Prima (A Crédito)</option>
                                        <option value="consumo">Consumo de Materia Prima (Kárdex)</option>
                                        <option value="nomina">Liquidación de Nómina Quincenal</option>
                                        <option value="gasto">Gasto General (Pago Caja Menor)</option>
                                    </select>
                                </div>

                                <hr>

                                <!-- Campos Dinámicos según Transacción -->
                                <div class="dynamic-fields mt-3">
                                    <!-- Campos para Venta -->
                                    <div v-if="sim.tipo == 'venta'">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Subtotal de Venta (COP)</label>
                                            <input type="number" v-model.number="sim.venta.subtotal" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Tasa de IVA (%)</label>
                                            <input type="number" v-model.number="sim.venta.iva_tasa" @input="recalculateSimulation" class="form-control" min="0" max="100">
                                        </div>
                                    </div>

                                    <!-- Campos para Recaudo -->
                                    <div v-if="sim.tipo == 'recaudo'">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Monto Recibido (COP)</label>
                                            <input type="number" v-model.number="sim.recaudo.monto" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Método de Pago</label>
                                            <select v-model="sim.recaudo.metodo" @change="recalculateSimulation" class="form-control">
                                                <option value="efectivo">Efectivo (Caja General)</option>
                                                <option value="transferencia">Transferencia (Bancos)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Campos para Compra -->
                                    <div v-if="sim.tipo == 'compra'">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Subtotal de Compra (COP)</label>
                                            <input type="number" v-model.number="sim.compra.subtotal" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Tasa de IVA (%)</label>
                                            <input type="number" v-model.number="sim.compra.iva_tasa" @input="recalculateSimulation" class="form-control" min="0" max="100">
                                        </div>
                                    </div>

                                    <!-- Campos para Consumo -->
                                    <div v-if="sim.tipo == 'consumo'">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Cantidad Consumida</label>
                                            <input type="number" v-model.number="sim.consumo.cantidad" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Costo Unitario del Insumo (COP)</label>
                                            <input type="number" v-model.number="sim.consumo.costo_unitario" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                    </div>

                                    <!-- Campos para Nómina -->
                                    <div v-if="sim.tipo == 'nomina'">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Salario Básico (COP)</label>
                                            <input type="number" v-model.number="sim.nomina.basico" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Auxilio de Transporte (COP)</label>
                                            <input type="number" v-model.number="sim.nomina.transporte" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Deducción de Salud (4% - COP)</label>
                                            <input type="number" v-model.number="sim.nomina.salud" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Deducción de Pensión (4% - COP)</label>
                                            <input type="number" v-model.number="sim.nomina.pension" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                    </div>

                                    <!-- Campos para Gasto -->
                                    <div v-if="sim.tipo == 'gasto'">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Monto Total del Gasto (COP)</label>
                                            <input type="number" v-model.number="sim.gasto.monto" @input="recalculateSimulation" class="form-control" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted small">Tipo de Gasto</label>
                                            <select v-model="sim.gasto.categoria" @change="recalculateSimulation" class="form-control">
                                                <option value="servicios">Servicios Públicos (Cuenta Gasto 5135)</option>
                                                <option value="diversos">Diversos / Insumos (Cuenta Gasto 5195)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Visualización del Asiento Contable -->
                        <div class="col-md-7">
                            <div class="card shadow-sm border rounded h-100 overflow-hidden bg-white">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                                    <h6 class="font-weight-bold m-0"><i class="fa fa-balance-scale mr-2"></i>2. Asiento en Partida Doble (Simulado)</h6>
                                    <span class="badge badge-success p-2 font-weight-bold" v-if="isBalanced">
                                        <i class="fa fa-check-circle mr-1"></i>Cuadrado (Balanced)
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col" class="text-left">Cuenta PUC</th>
                                                <th scope="col" class="text-left">Nombre de Cuenta</th>
                                                <th scope="col" class="text-right">Debe (Débito)</th>
                                                <th scope="col" class="text-right">Haber (Crédito)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(row, idx) in simRows" :key="idx" class="sim-row">
                                                <td class="text-left font-weight-bold text-primary">{{ row.codigo }}</td>
                                                <td class="text-left text-dark small font-weight-bold">{{ row.nombre }}</td>
                                                <td class="text-right align-middle text-success font-weight-bold">
                                                    {{ row.debe > 0 ? formatCurrency(row.debe) : '-' }}
                                                </td>
                                                <td class="text-right align-middle text-danger font-weight-bold">
                                                    {{ row.haber > 0 ? formatCurrency(row.haber) : '-' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold text-dark border-top-2">
                                            <tr>
                                                <td colspan="2" class="text-left">TOTALES BALANCEADOS</td>
                                                <td class="text-right text-success font-weight-bold">{{ formatCurrency(totalDebe) }}</td>
                                                <td class="text-right text-danger font-weight-bold">{{ formatCurrency(totalHaber) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Explicación Didáctica -->
                                <div class="p-3 bg-light border-top">
                                    <h6 class="font-weight-bold text-muted small text-uppercase mb-2"><i class="fa fa-info-circle mr-1"></i>Lógica Contable detrás de esta transacción:</h6>
                                    <p class="text-dark small mb-0 leading-relaxed text-justify">{{ glosaExplicativa }}</p>
                                </div>
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
    data() {
        return {
            activeTab: 'guias',
            sim: {
                tipo: 'venta',
                venta: {
                    subtotal: 1000000,
                    iva_tasa: 19
                },
                recaudo: {
                    monto: 500000,
                    metodo: 'efectivo'
                },
                compra: {
                    subtotal: 2000000,
                    iva_tasa: 19
                },
                consumo: {
                    cantidad: 50,
                    costo_unitario: 500
                },
                nomina: {
                    basico: 1300000,
                    transporte: 170000,
                    salud: 52000,
                    pension: 52000
                },
                gasto: {
                    monto: 150000,
                    categoria: 'servicios'
                }
            },
            simRows: [],
            glosaExplicativa: '',
            conceptsList: [
                {
                    title: '1. Partida Doble (Debe vs. Haber)',
                    description: 'La contabilidad profesional exige que por cada registro, la suma de los valores debitados sea exactamente igual a los valores acreditados. No existen transacciones huérfanas o sin cuadre.',
                    bgColorClass: 'bg-primary',
                    icon: 'fa-balance-scale',
                    keyAccount: 'Ecuación: Debe = Haber'
                },
                {
                    title: '2. Cuentas por Cobrar (Clientes)',
                    description: 'Al registrar una venta a crédito (Pedido), nace un derecho de cobro a favor del negocio. Este se debita en el activo y se cancela acreditándolo una vez el cliente realiza abonos (Recibo de Caja).',
                    bgColorClass: 'bg-info',
                    icon: 'fa-user-circle-o',
                    keyAccount: '130505 - Clientes Nacionales'
                },
                {
                    title: '3. Impuestos Separados (IVA)',
                    description: 'El IVA en ventas es un pasivo a favor del estado y se acredita. En compras de materias primas, el IVA cobrado por el proveedor es descontable y se debita como un beneficio tributario.',
                    bgColorClass: 'bg-warning',
                    icon: 'fa-percent',
                    keyAccount: '240805 / 240810 - IVA Generado / Descontable'
                },
                {
                    title: '4. Inventario de Materias Primas',
                    description: 'Las compras de materias primas no se consideran un gasto directo inmediato. Se debitan en el activo (Inventarios) y solo pasan al costo real cuando se registran salidas por despacho a producción.',
                    bgColorClass: 'bg-success',
                    icon: 'fa-archive',
                    keyAccount: '140505 - Inventario de Materias Primas'
                },
                {
                    title: '5. Costo de Ventas (COGS)',
                    description: 'Al consumir materiales en las órdenes de producción, se reduce el activo del inventario y se debita la cuenta de costos. Esto permite calcular el margen de contribución real por pedido.',
                    bgColorClass: 'bg-danger',
                    icon: 'fa-line-chart',
                    keyAccount: '613505 - Costo de Materia Prima Directa'
                },
                {
                    title: '6. Nómina y Provisiones',
                    description: 'La nómina debita los sueldos básicos y auxilios de transporte como gastos de personal. Acredita las deducciones por salud y pensión, dejando el saldo neto como pasivo por pagar al empleado.',
                    bgColorClass: 'bg-dark',
                    icon: 'fa-users',
                    keyAccount: '250505 - Salarios por Pagar'
                }
            ]
        }
    },
    computed: {
        totalDebe() {
            return this.simRows.reduce((sum, row) => sum + (row.debe || 0), 0);
        },
        totalHaber() {
            return this.simRows.reduce((sum, row) => sum + (row.haber || 0), 0);
        },
        isBalanced() {
            return Math.abs(this.totalDebe - this.totalHaber) < 0.01;
        }
    },
    methods: {
        formatCurrency(value) {
            if (value === null || value === undefined) return '$0,00';
            return '$' + parseFloat(value).toLocaleString('es-CO', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        },
        recalculateSimulation() {
            let rows = [];
            let glosa = '';

            switch (this.sim.tipo) {
                case 'venta': {
                    let sub = parseFloat(this.sim.venta.subtotal) || 0;
                    let tasa = parseFloat(this.sim.venta.iva_tasa) || 0;
                    let iva = Math.round(sub * (tasa / 100));
                    let total = sub + iva;

                    rows.push({ codigo: '130505', nombre: 'Clientes Nacionales', debe: total, haber: 0 });
                    rows.push({ codigo: '413505', nombre: 'Ventas de Productos / Empaques', debe: 0, haber: sub });
                    if (iva > 0) {
                        rows.push({ codigo: '240805', nombre: 'IVA Generado por Pagar (19% / Ventas)', debe: 0, haber: iva });
                    }

                    glosa = `Al registrar el pedido, se genera una cuenta por cobrar (Débito en Clientes 130505) por el total facturado. El ingreso neto (base gravable) se registra como un beneficio en Ventas (Crédito en 413505), mientras que el IVA se separa como una obligación fiscal por pagar al Estado (Crédito en 240805).`;
                    break;
                }
                case 'recaudo': {
                    let monto = parseFloat(this.sim.recaudo.monto) || 0;
                    let metodo = this.sim.recaudo.metodo;
                    let cuentaCajaBanco = metodo === 'efectivo' ? '110505' : '111005';
                    let nombreCajaBanco = metodo === 'efectivo' ? 'Caja General' : 'Bancos Nacionales';

                    rows.push({ codigo: cuentaCajaBanco, nombre: nombreCajaBanco, debe: monto, haber: 0 });
                    rows.push({ codigo: '130505', nombre: 'Clientes Nacionales', debe: 0, haber: monto });

                    glosa = `Cuando el cliente paga o abona, ingresa dinero real a la empresa (Débito en Caja General o Bancos). Al mismo tiempo, se reduce la cuenta por cobrar que teníamos pendiente con ese tercero (Crédito en Clientes 130505) cancelando la deuda del cliente en esa proporción.`;
                    break;
                }
                case 'compra': {
                    let sub = parseFloat(this.sim.compra.subtotal) || 0;
                    let tasa = parseFloat(this.sim.compra.iva_tasa) || 0;
                    let iva = Math.round(sub * (tasa / 100));
                    let total = sub + iva;

                    rows.push({ codigo: '140505', nombre: 'Inventario de Materias Primas', debe: sub, haber: 0 });
                    if (iva > 0) {
                        rows.push({ codigo: '240810', nombre: 'IVA Descontable (Compras / Activo)', debe: iva, haber: 0 });
                    }
                    rows.push({ codigo: '220505', nombre: 'Proveedores Nacionales', debe: 0, haber: total });

                    glosa = `Al ingresar materias primas compradas a crédito, incrementamos nuestro activo (Débito en Inventarios 140505 por el valor neto). El IVA cobrado por el proveedor se registra como un derecho tributario que podremos descontar en el futuro (Débito en IVA Descontable 240810). Finalmente, asumimos la obligación de pago total ante el tercero (Crédito en Proveedores 220505).`;
                    break;
                }
                case 'consumo': {
                    let cant = parseFloat(this.sim.consumo.cantidad) || 0;
                    let unit = parseFloat(this.sim.consumo.costo_unitario) || 0;
                    let total = roundToTwo(cant * unit);

                    rows.push({ codigo: '613505', nombre: 'Costo de Materia Prima e Insumos Directos', debe: total, haber: 0 });
                    rows.push({ codigo: '140505', nombre: 'Inventario de Materias Primas', debe: 0, haber: total });

                    glosa = `Al despachar materiales para una Orden de Trabajo activa, el stock físico de almacén disminuye (Crédito en Inventario de Materias Primas 140505). Ese valor del consumo no se pierde, sino que se traslada como un costo directamente atribuible al producto fabricado (Débito en Costo de Ventas/Producción 613505), permitiendo calcular la rentabilidad del pedido.`;
                    break;
                }
                case 'nomina': {
                    let basico = parseFloat(this.sim.nomina.basico) || 0;
                    let trans = parseFloat(this.sim.nomina.transporte) || 0;
                    let salud = parseFloat(this.sim.nomina.salud) || 0;
                    let pension = parseFloat(this.sim.nomina.pension) || 0;
                    let totalPagar = basico + trans - salud - pension;

                    rows.push({ codigo: '510506', nombre: 'Gasto Sueldos Básicos', debe: basico, haber: 0 });
                    rows.push({ codigo: '510527', nombre: 'Gasto Auxilio de Transporte', debe: trans, haber: 0 });
                    rows.push({ codigo: '237005', nombre: 'Retenciones y Aportes de Salud (Trabajador)', debe: 0, haber: salud });
                    rows.push({ codigo: '238030', nombre: 'Aportes a Fondos de Pensión (Trabajador)', debe: 0, haber: pension });
                    rows.push({ codigo: '250505', nombre: 'Salarios por Pagar (Neto a pagar)', debe: 0, haber: totalPagar });

                    glosa = `La liquidación quincenal registra los devengos del empleado como un gasto para la empresa (Débito en Gasto Sueldos y Gasto Auxilio de Transporte). Se deducen los aportes obligatorios de ley (Crédito en Pasivos por Retenciones de Salud 237005 y Pensión 238030). El excedente neto calculado se asienta como un pasivo por pagar a favor del operario (Crédito en Salarios por Pagar 250505).`;
                    break;
                }
                case 'gasto': {
                    let monto = parseFloat(this.sim.gasto.monto) || 0;
                    let cat = this.sim.gasto.categoria;
                    let cuentaGasto = cat === 'servicios' ? '513505' : '519595';
                    let nombreGasto = cat === 'servicios' ? 'Gasto Servicios Públicos (Acueducto/Energía)' : 'Gastos Diversos / Insumos Oficina';

                    rows.push({ codigo: cuentaGasto, nombre: nombreGasto, debe: monto, haber: 0 });
                    rows.push({ codigo: '110510', nombre: 'Caja Menor (Egreso Directo)', debe: 0, haber: monto });

                    glosa = `Al pagar un gasto general menor de forma inmediata (por ejemplo, el recibo de energía de la oficina), se asienta una pérdida o gasto directamente en la cuenta de resultados del PUC (Débito en la cuenta del Gasto correspondiente clase 5) y se disminuye la disponibilidad de efectivo asignada a la caja menor de administración (Crédito en Caja Menor 110510).`;
                    break;
                }
            }

            this.simRows = rows;
            this.glosaExplicativa = glosa;
        }
    },
    mounted() {
        this.recalculateSimulation();
    }
}

function roundToTwo(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}
</script>

<style scoped>
.concept-box {
    background-color: #fcfdfe;
    border-color: #e3e8ee !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.concept-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.05);
    border-color: #d1d9e2 !important;
}

.concept-icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.sim-row {
    transition: background-color 0.2s ease;
}

.sim-row:hover {
    background-color: #f8f9fa;
}

.border-top-2 {
    border-top: 2px solid #343a40 !important;
}

.hover-shadow {
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.leading-relaxed {
    line-height: 1.6;
}
</style>
