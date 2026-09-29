function calcularSobrante(cantidad, cabida, numTintas) {
    const cant = parseFloat(cantidad) || 0;
    const cab = parseFloat(cabida) || 1;
    const tintas = Math.max(1, parseInt(numTintas) || 1);
    
    // Cantidad de material a imprimir (tamaños/pliegos base)
    const cantidadMaterial = cab > 0 ? (cant / cab) : cant;
    
    // Base por cuadre de máquina (50 hojas base + 25 por cada tinta adicional)
    const sobranteBase = 50 + (tintas - 1) * 25;
    
    // Adicional por tiraje de impresión (1% del material a imprimir)
    const sobranteTiraje = Math.round(cantidadMaterial * 0.01);
    
    // Sobrante total
    const sobranteCalculado = Math.max(50, Math.round(sobranteBase + sobranteTiraje));
    const pliegosConSobrante = cantidadMaterial + sobranteCalculado;
    
    return {
        cantidadPedido: cant,
        cabida: cab,
        numTintas: tintas,
        cantidadMaterialImprimir: cantidadMaterial,
        sobranteBase: sobranteBase,
        sobranteTiraje: sobranteTiraje,
        sobranteCalculadoTotal: sobranteCalculado,
        totalMaterialConSobrante: pliegosConSobrante
    };
}

console.log("=== PRUEBAS DE CÁLCULO DE SOBRANTE ===");
console.table([
    calcularSobrante(1000, 1, 1),
    calcularSobrante(1000, 2, 4),
    calcularSobrante(5000, 4, 2),
    calcularSobrante(10000, 2, 4),
    calcularSobrante(500, 1, 1)
]);
