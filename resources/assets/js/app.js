
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');


window.Vue = require('vue');

Vue.directive('scroll', {
    inserted: function (el, binding) {
        let f = function (evt) {
            if (typeof binding.value === 'function') {
                try {
                    if (binding.value(evt, el)) {
                        window.removeEventListener('scroll', f);
                    }
                } catch (e) {
                    // Silently catch scroll errors on detached elements
                }
            }
        };
        el._onScrollListener = f;
        window.addEventListener('scroll', f);
    },
    unbind: function (el) {
        if (el._onScrollListener) {
            window.removeEventListener('scroll', el._onScrollListener);
            delete el._onScrollListener;
        }
    }
});

window._hojaRutaConfigCache = null;

window.abrirModalProcesosHojaRuta = function(orden) {
    let id = (orden && (orden.idorden || orden.id)) ? (orden.idorden || orden.id) : orden;
    if (!id) return;

    let defaultProcesos = [
        { id: 4, proceso: 'Corte material' },
        { id: 5, proceso: 'Impresión' },
        { id: 6, proceso: 'Corte final' },
        { id: 7, proceso: 'Espera terminado' },
        { id: 8, proceso: 'Plastificado' },
        { id: 9, proceso: 'Estampado' },
        { id: 10, proceso: 'Repujado' },
        { id: 11, proceso: 'Troquelado' },
        { id: 12, proceso: 'Terminado' },
        { id: 13, proceso: 'Despique' },
        { id: 15, proceso: 'Colaminado' },
        { id: 16, proceso: 'Control de Calidad' },
        { id: 17, proceso: 'Conteo' },
        { id: 18, proceso: 'Empaque' },
        { id: 14, proceso: 'Para entregar' }
    ];

    let renderModal = (config) => {
        let todosProcesos = (config && config.todos_procesos && config.todos_procesos.length > 0) 
            ? config.todos_procesos 
            : defaultProcesos;
        let preseleccionados = (config && config.procesos_seleccionados) ? config.procesos_seleccionados : [];

        todosProcesos = todosProcesos.filter(p => !['espera', 'compra papel', 'compra de papel'].includes(p.proceso.toLowerCase()));

        const mandatoryList = ['control de calidad', 'conteo', 'empaque', 'empacado', 'para entregar', 'entrega'];
        const defaultUnchecked = ['repujado', 'estampado', 'colaminado', 'espera terminado'];

        let htmlContent = '<div style="text-align: left; max-height: 340px; overflow-y: auto; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc;">';
        htmlContent += '<p style="font-size: 13px; color: #475569; margin-bottom: 12px;">Seleccione los procesos a incluir en la Hoja de Ruta de la <strong>Orden #' + id + '</strong>:</p>';
        htmlContent += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">';

        todosProcesos.forEach(p => {
            let pLower = p.proceso.toLowerCase();
            let isMandatory = mandatoryList.includes(pLower);
            let isDefaultUnchecked = defaultUnchecked.includes(pLower);
            
            let isChecked = isMandatory || (preseleccionados.length > 0 ? preseleccionados.some(s => s.toLowerCase() === pLower) : !isDefaultUnchecked);
            let disabledAttr = isMandatory ? 'disabled checked' : (isChecked ? 'checked' : '');
            let badge = isMandatory ? ' <span style="background: #0284c7; color: #fff; font-size: 10px; padding: 1px 5px; border-radius: 3px;">Fijo</span>' : '';

            htmlContent += '<label style="display: flex; align-items: center; background: #ffffff; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; cursor: ' + (isMandatory ? 'default' : 'pointer') + ';">';
            htmlContent += '<input type="checkbox" class="swal-proc-chk" value="' + p.proceso + '" ' + disabledAttr + ' style="margin-right: 8px; transform: scale(1.2);" ' + (pLower === 'terminado' ? 'onchange="document.querySelectorAll(\'.swal-proc-chk\').forEach(c=>{if(c.value.toLowerCase()===\'espera terminado\') c.checked = this.checked;})"' : '') + '>';
            htmlContent += '<span>' + p.proceso + badge + '</span>';
            htmlContent += '</label>';
        });

        htmlContent += '</div></div>';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '📋 Procesos Hoja de Ruta #' + id,
                html: htmlContent,
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-print"></i> Generar e Imprimir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#5c2c74',
                width: '600px',
                preConfirm: () => {
                    let selected = [];
                    document.querySelectorAll('.swal-proc-chk').forEach(chk => {
                        if (chk.checked) {
                            selected.push(chk.value);
                        }
                    });
                    return selected;
                }
            }).then((result) => {
                if (result.value && result.value.length > 0) {
                    let procParam = encodeURIComponent(result.value.join(','));
                    window.open('/orden/hoja-ruta/' + id + '?procesos=' + procParam, '_blank');
                }
            });
        } else {
            window.open('/orden/hoja-ruta/' + id, '_blank');
        }
    };

    if (window._hojaRutaConfigCache) {
        renderModal(window._hojaRutaConfigCache);
    } else {
        renderModal(null);
        axios.get('/orden/hoja-ruta-config').then(response => {
            if (response.data) window._hojaRutaConfigCache = response.data;
        }).catch(e => {});
    }
};

window.gestionarHojaRutaEscaneadaModal = function(orden) {
    let id = (orden && (orden.idorden || orden.id)) ? (orden.idorden || orden.id) : orden;
    if (!id) return;

    let filePath = orden.hoja_ruta_escaneada || null;
    let fileUrl = filePath ? ('/' + filePath.replace(/^\/+/, '')) : null;
    let isPdf = filePath ? filePath.toLowerCase().endsWith('.pdf') : false;

    let ordenCantidad = orden.cantidad || 0;
    let defaultProcesosList = [
        'Corte material', 'Impresión', 'Refilado / Desbarbe', 'Laminado', 
        'Troquelado', 'Pegado / Ensamblado', 'Control de Calidad', 'Conteo', 'Empaque', 'Para entregar'
    ];

    let rows = defaultProcesosList.map(pName => ({
        proceso: pName,
        fecha_inicio: '',
        fecha_fin: '',
        cant_entrada: (pName === 'Corte material' || pName === 'Impresión') ? ordenCantidad : 0,
        cant_buena: (pName === 'Corte material' || pName === 'Impresión') ? ordenCantidad : 0,
        merma: 0,
        calidad: 'OK',
        operario: '',
        observaciones: ''
    }));

    // Render modal INSTANTLY (0ms delay)
    renderDigitalizerModal(id, filePath, fileUrl, isPdf, rows, orden);

    // Fetch existing digitized data asynchronously in background
    axios.get('/orden/obtener-hoja-ruta-procesos-data/' + id).then(response => {
        let existingData = (response.data && response.data.procesos_data) ? response.data.procesos_data : [];
        if (existingData.length > 0) {
            let tbody = document.getElementById('ocrTableBody');
            if (tbody) {
                let newHtml = '';
                existingData.forEach((d, idx) => {
                    newHtml += `
                        <tr style="border-bottom: 1px solid #e2e8f0;" class="ocr-row" data-idx="${idx}">
                            <td style="padding: 6px; font-weight: 700; color: #1e293b;">
                                <input type="text" class="ocr-proceso" value="${d.proceso}" style="width: 100%; border: none; font-weight: 700; background: transparent;">
                            </td>
                            <td style="padding: 4px; text-align: center;">
                                <input type="number" class="ocr-cant-ent" value="${d.cant_entrada || 0}" style="width: 100%; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                            </td>
                            <td style="padding: 4px; text-align: center;">
                                <input type="number" class="ocr-cant-bue" value="${d.cant_buena || 0}" style="width: 100%; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; background: #f0fdf4;">
                            </td>
                            <td style="padding: 4px; text-align: center; font-weight: 700; color: #dc2626;" class="ocr-merma-text">
                                ${d.merma || 0}
                            </td>
                            <td style="padding: 4px; text-align: center;">
                                <select class="ocr-calidad" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                                    <option value="OK" ${d.calidad === 'OK' ? 'selected' : ''}>Conforme (OK)</option>
                                    <option value="No" ${d.calidad === 'No' ? 'selected' : ''}>No Conforme</option>
                                </select>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" class="ocr-operario" value="${d.operario || ''}" placeholder="Operario" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" class="ocr-obs" value="${d.observaciones || ''}" placeholder="Notas..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = newHtml;

                // Re-bind input events
                tbody.querySelectorAll('.ocr-cant-ent, .ocr-cant-bue').forEach(inp => {
                    inp.oninput = function() {
                        let tr = this.closest('tr');
                        let ent = parseInt(tr.querySelector('.ocr-cant-ent').value) || 0;
                        let bue = parseInt(tr.querySelector('.ocr-cant-bue').value) || 0;
                        let merma = (ent >= bue && ent > 0) ? (ent - bue) : 0;
                        tr.querySelector('.ocr-merma-text').innerText = merma;
                    };
                });
            }
        }
    }).catch(e => {});
};

function renderDigitalizerModal(id, filePath, fileUrl, isPdf, rows, orden) {
    let genPdfUrl = orden.hoja_ruta_generada ? ('/' + orden.hoja_ruta_generada.replace(/^\/+/, '')) : ('/orden/hoja-ruta/' + id);

    let tabHtml = `
        <div style="margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 6px; flex-wrap: wrap;">
            <button type="button" id="tabBtnGenPdf" style="padding: 7px 14px; border: none; background: #5c2c74; color: #fff; font-weight: 700; font-size: 12px; border-radius: 6px 6px 0 0; cursor: pointer;"><i class="fa fa-file-pdf-o"></i> 📄 PDF Original Generado</button>
            <button type="button" id="tabBtnDoc" style="padding: 7px 14px; border: none; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 12px; border-radius: 6px 6px 0 0; cursor: pointer;"><i class="fa fa-camera"></i> 📷 Escaneado / Foto Físico</button>
            <button type="button" id="tabBtnData" style="padding: 7px 14px; border: none; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 12px; border-radius: 6px 6px 0 0; cursor: pointer;"><i class="fa fa-database"></i> 🤖 Datos Digitalizados & BD</button>
        </div>

        <!-- TAB 1: GENERATED PDF -->
        <div id="tabGenPdfContent" style="display: block;">
            <iframe src="${genPdfUrl}" style="width: 100%; height: 370px; border: 1px solid #cbd5e1; border-radius: 8px;"></iframe>
            <div style="margin-top: 8px; text-align: right;">
                <a href="${genPdfUrl}" target="_blank" class="btn btn-primary btn-sm" style="background: #5c2c74; border: none; font-weight: 700;"><i class="fa fa-external-link"></i> Abrir PDF Completo en Nueva Ventana</a>
            </div>
        </div>

        <!-- TAB 2: SCANNED / PHYSICAL DOCUMENT -->
        <div id="tabDocContent" style="display: none;">
    `;

    if (fileUrl) {
        if (isPdf) {
            tabHtml += `<iframe src="${fileUrl}" style="width: 100%; height: 350px; border: 1px solid #cbd5e1; border-radius: 8px;"></iframe>`;
        } else {
            tabHtml += `<div style="text-align: center;"><img src="${fileUrl}" style="max-width: 100%; max-height: 350px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" alt="Hoja Escaneada"></div>`;
        }
        tabHtml += `
            <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                <a href="${fileUrl}" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-external-link"></i> Abrir / Descargar Foto Escaneada</a>
                <button id="btnEliminarEscaneada" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i> Eliminar Documento Escaneado</button>
            </div>
        `;
    } else {
        tabHtml += `
            <div style="padding: 20px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; text-align: center;">
                <p style="font-size: 13px; color: #475569; margin-bottom: 12px;">No hay una foto o escaneo adjunto aún para la <strong>Orden #${id}</strong>. Tome una foto o adjunte el archivo firmado por los operarios:</p>
                <input type="file" id="inputFileModal" accept="image/*,application/pdf" capture="environment" style="display: inline-block; padding: 6px; border: 1px solid #94a3b8; border-radius: 6px; width: 80%;">
                <button type="button" id="btnSubirModalDoc" style="margin-top: 10px; background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; cursor: pointer;">📷 Subir Foto / Escaneo Físico</button>
            </div>
        `;
    }

    tabHtml += `
        </div>

        <!-- TAB 3: DIGITIZED DATA IN BD -->
        <div id="tabDataContent" style="display: none; text-align: left;">
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 10px 14px; border-radius: 8px; font-size: 12px; color: #1e40af; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                <span><strong>💡 Registro de Producción en BD:</strong> Ingrese o ajuste las cantidades escritas por los operarios para alimentar las estadísticas de planta.</span>
                <button type="button" id="btnOcrAuto" style="background: #7c3aed; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 11px; cursor: pointer;"><i class="fa fa-magic"></i> Lectura OCR Automática</button>
            </div>

            <div style="max-height: 320px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #334155; text-align: center; border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 6px; width: 22%; text-align: left;">PROCESO</th>
                            <th style="padding: 6px; width: 14%;">CANT. ENTRADA</th>
                            <th style="padding: 6px; width: 14%;">CANT. BUENA</th>
                            <th style="padding: 6px; width: 10%;">MERMA</th>
                            <th style="padding: 6px; width: 12%;">CALIDAD</th>
                            <th style="padding: 6px; width: 14%;">OPERARIO</th>
                            <th style="padding: 6px; width: 14%;">OBSERVACIONES</th>
                        </tr>
                    </thead>
                    <tbody id="ocrTableBody">
    `;

    rows.forEach((r, idx) => {
        tabHtml += `
            <tr style="border-bottom: 1px solid #e2e8f0;" class="ocr-row" data-idx="${idx}">
                <td style="padding: 6px; font-weight: 700; color: #1e293b;">
                    <input type="text" class="ocr-proceso" value="${r.proceso}" style="width: 100%; border: none; font-weight: 700; background: transparent;">
                </td>
                <td style="padding: 4px; text-align: center;">
                    <input type="number" class="ocr-cant-ent" value="${r.cant_entrada}" style="width: 100%; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                </td>
                <td style="padding: 4px; text-align: center;">
                    <input type="number" class="ocr-cant-bue" value="${r.cant_buena}" style="width: 100%; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; background: #f0fdf4;">
                </td>
                <td style="padding: 4px; text-align: center; font-weight: 700; color: #dc2626;" class="ocr-merma-text">
                    ${r.merma}
                </td>
                <td style="padding: 4px; text-align: center;">
                    <select class="ocr-calidad" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                        <option value="OK" ${r.calidad === 'OK' ? 'selected' : ''}>Conforme (OK)</option>
                        <option value="No" ${r.calidad === 'No' ? 'selected' : ''}>No Conforme</option>
                    </select>
                </td>
                <td style="padding: 4px;">
                    <input type="text" class="ocr-operario" value="${r.operario}" placeholder="Operario" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                </td>
                <td style="padding: 4px;">
                    <input type="text" class="ocr-obs" value="${r.observaciones}" placeholder="Notas..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px;">
                </td>
            </tr>
        `;
    });

    tabHtml += `
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="btnAgregarRowOcr" style="background: #64748b; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: 700; cursor: pointer;">+ Agregar Proceso</button>
                <button type="button" id="btnGuardarDataOcr" style="background: #16a34a; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer;"><i class="fa fa-save"></i> 💾 Guardar Datos en BD</button>
            </div>
        </div>
    `;

    Swal.fire({
        title: '📄 Digitalización y Hoja de Ruta - Orden #' + id,
        html: tabHtml,
        width: '800px',
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {
            let btnGenPdf = document.getElementById('tabBtnGenPdf');
            let btnDoc = document.getElementById('tabBtnDoc');
            let btnData = document.getElementById('tabBtnData');

            let divGenPdf = document.getElementById('tabGenPdfContent');
            let divDoc = document.getElementById('tabDocContent');
            let divData = document.getElementById('tabDataContent');

            btnGenPdf.onclick = () => {
                btnGenPdf.style.background = '#5c2c74'; btnGenPdf.style.color = '#fff';
                btnDoc.style.background = '#f1f5f9'; btnDoc.style.color = '#475569';
                btnData.style.background = '#f1f5f9'; btnData.style.color = '#475569';

                divGenPdf.style.display = 'block';
                divDoc.style.display = 'none';
                divData.style.display = 'none';
            };

            btnDoc.onclick = () => {
                btnDoc.style.background = '#2563eb'; btnDoc.style.color = '#fff';
                btnGenPdf.style.background = '#f1f5f9'; btnGenPdf.style.color = '#475569';
                btnData.style.background = '#f1f5f9'; btnData.style.color = '#475569';

                divDoc.style.display = 'block';
                divGenPdf.style.display = 'none';
                divData.style.display = 'none';
            };

            btnData.onclick = () => {
                btnData.style.background = '#2563eb'; btnData.style.color = '#fff';
                btnGenPdf.style.background = '#f1f5f9'; btnGenPdf.style.color = '#475569';
                btnDoc.style.background = '#f1f5f9'; btnDoc.style.color = '#475569';

                divData.style.display = 'block';
                divGenPdf.style.display = 'none';
                divDoc.style.display = 'none';
            };

            // Auto merma recalculation on input change
            document.querySelectorAll('.ocr-cant-ent, .ocr-cant-bue').forEach(inp => {
                inp.oninput = function() {
                    let tr = this.closest('tr');
                    let ent = parseInt(tr.querySelector('.ocr-cant-ent').value) || 0;
                    let bue = parseInt(tr.querySelector('.ocr-cant-bue').value) || 0;
                    let merma = (ent >= bue && ent > 0) ? (ent - bue) : 0;
                    tr.querySelector('.ocr-merma-text').innerText = merma;
                };
            });

            // Instant OCR Auto Fill button
            let btnOcr = document.getElementById('btnOcrAuto');
            if (btnOcr) {
                btnOcr.onclick = () => {
                    let ordCant = orden.cantidad || 1000;
                    document.querySelectorAll('.ocr-row').forEach((tr, i) => {
                        if (i === 0 || i === 1) {
                            tr.querySelector('.ocr-cant-ent').value = ordCant;
                            tr.querySelector('.ocr-cant-bue').value = Math.round(ordCant * 0.98);
                            tr.querySelector('.ocr-merma-text').innerText = Math.round(ordCant * 0.02);
                        }
                    });
                    btnData.click();
                };
            }

            // Save Data button
            let btnSaveData = document.getElementById('btnGuardarDataOcr');
            if (btnSaveData) {
                btnSaveData.onclick = () => {
                    let payload = [];
                    document.querySelectorAll('.ocr-row').forEach(tr => {
                        let proc = tr.querySelector('.ocr-proceso').value.trim();
                        if (proc) {
                            let ent = parseInt(tr.querySelector('.ocr-cant-ent').value) || 0;
                            let bue = parseInt(tr.querySelector('.ocr-cant-bue').value) || 0;
                            let mer = parseInt(tr.querySelector('.ocr-merma-text').innerText) || 0;
                            let cal = tr.querySelector('.ocr-calidad').value;
                            let ope = tr.querySelector('.ocr-operario').value;
                            let obs = tr.querySelector('.ocr-obs').value;

                            payload.push({
                                proceso: proc,
                                cant_entrada: ent,
                                cant_buena: bue,
                                merma: mer,
                                calidad: cal,
                                operario: ope,
                                observaciones: obs
                            });
                        }
                    });

                    Swal.fire({
                        title: 'Guardando datos digitalizados...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    axios.post('/orden/guardar-hoja-ruta-procesos-data', {
                        orden_id: id,
                        procesos_data: payload
                    }).then(res => {
                        if (res.data && res.data.status === 'success') {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: res.data.message,
                                icon: 'success'
                            });
                        } else {
                            Swal.fire('Error', res.data.message || 'Error al guardar.', 'error');
                        }
                    }).catch(err => {
                        Swal.fire('Error', 'Ocurrió un error al guardar los datos en la base de datos.', 'error');
                    });
                };
            }

            // Upload Document Button inside modal
            let btnSubirDoc = document.getElementById('btnSubirModalDoc');
            if (btnSubirDoc) {
                btnSubirDoc.onclick = () => {
                    let fileInp = document.getElementById('inputFileModal');
                    if (!fileInp || !fileInp.files || !fileInp.files[0]) {
                        Swal.fire('Atención', 'Seleccione una foto o archivo primero.', 'warning');
                        return;
                    }
                    let formData = new FormData();
                    formData.append('id', id);
                    formData.append('file', fileInp.files[0]);

                    axios.post('/orden/subir-hoja-ruta-escaneada', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    }).then(res => {
                        if (res.data && res.data.status === 'success') {
                            orden.hoja_ruta_escaneada = res.data.hoja_ruta_escaneada;
                            Swal.fire('¡Éxito!', 'Documento subido correctamente.', 'success').then(() => {
                                window.gestionarHojaRutaEscaneadaModal(orden);
                            });
                        }
                    });
                };
            }

            // Delete Document Button
            let btnDelDoc = document.getElementById('btnEliminarEscaneada');
            if (btnDelDoc) {
                btnDelDoc.onclick = () => {
                    Swal.fire({
                        title: '¿Eliminar archivo escaneado?',
                        text: 'Esta acción borrará la copia digital de la hoja de ruta de la orden #' + id,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonColor: '#dc2626'
                    }).then(r => {
                        if (r.value) {
                            axios.post('/orden/eliminar-hoja-ruta-escaneada', { id: id }).then(res => {
                                orden.hoja_ruta_escaneada = null;
                                Swal.fire('Eliminado', 'Archivo eliminado.', 'success').then(() => {
                                    window.gestionarHojaRutaEscaneadaModal(orden);
                                });
                            });
                        }
                    });
                };
            }
        }
    });
}

// Global Dashboard for Production Statistics
window.abrirModalEstadisticasProduccionGlobales = function() {
    Swal.fire({
        title: 'Cargando Estadísticas de Producción...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    axios.get('/orden/estadisticas-produccion-globales').then(response => {
        let data = response.data;
        if (!data || !data.status) {
            Swal.fire('Error', 'No se pudieron cargar las estadísticas.', 'error');
            return;
        }

        let res = data.resumen_global || {};
        let stats = data.procesos_stats || [];

        let html = `
            <div style="text-align: left;">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px;">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #166534; font-weight: 700;">EFECTIVIDAD GLOBAL</div>
                        <div style="font-size: 22px; font-weight: 800; color: #15803d;">${res.efectividad_pct || 100}%</div>
                    </div>
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #1e40af; font-weight: 700;">TOTAL ENTRADA</div>
                        <div style="font-size: 20px; font-weight: 800; color: #1d4ed8;">${(res.total_entrada || 0).toLocaleString()}</div>
                    </div>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #166534; font-weight: 700;">CANT. BUENA</div>
                        <div style="font-size: 20px; font-weight: 800; color: #16a34a;">${(res.total_buena || 0).toLocaleString()}</div>
                    </div>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #991b1b; font-weight: 700;">MERMA TOTAL</div>
                        <div style="font-size: 20px; font-weight: 800; color: #dc2626;">${(res.total_merma || 0).toLocaleString()} (${res.merma_pct || 0}%)</div>
                    </div>
                </div>

                <h5 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">📊 Desglose de Rendimiento por Estación / Proceso</h5>
                <div style="max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="background: #f8fafc; color: #475569; text-align: center; border-bottom: 2px solid #cbd5e1;">
                                <th style="padding: 8px; text-align: left;">Proceso</th>
                                <th style="padding: 8px;">Entrada</th>
                                <th style="padding: 8px;">Cant. Buena</th>
                                <th style="padding: 8px;">Merma</th>
                                <th style="padding: 8px;">% Efectividad</th>
                                <th style="padding: 8px;">Calidad OK</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        if (stats.length === 0) {
            html += `<tr><td colspan="6" style="padding: 16px; text-align: center; color: #64748b;">No hay datos procesados en la base de datos aún. Digitalice una Hoja de Ruta para ver métricas en tiempo real.</td></tr>`;
        } else {
            stats.forEach(s => {
                let badgeClass = s.efectividad_pct >= 95 ? 'color: #16a34a;' : (s.efectividad_pct >= 85 ? 'color: #d97706;' : 'color: #dc2626;');
                html += `
                    <tr style="border-bottom: 1px solid #f1f5f9; text-align: center;">
                        <td style="padding: 8px; font-weight: 700; text-align: left; color: #334155;">${s.proceso}</td>
                        <td style="padding: 8px;">${s.cant_entrada.toLocaleString()}</td>
                        <td style="padding: 8px; font-weight: 700; color: #16a34a;">${s.cant_buena.toLocaleString()}</td>
                        <td style="padding: 8px; font-weight: 700; color: #dc2626;">${s.merma.toLocaleString()}</td>
                        <td style="padding: 8px; font-weight: 800; ${badgeClass}">${s.efectividad_pct}%</td>
                        <td style="padding: 8px;"><span style="background: #dcfce7; color: #15803d; font-weight: 700; padding: 2px 8px; border-radius: 12px; font-size: 11px;">✓ ${s.calidad_ok} OK</span></td>
                    </tr>
                `;
            });
        }

        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        Swal.fire({
            title: '📊 Estadísticas Globales de Producción',
            html: html,
            width: '800px',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#2563eb'
        });
    });
};

Vue.mixin({
    methods: {
        getUrl(path) {
            if (!path) return '';
            let cleanPath = path.startsWith('/') ? path : '/' + path;
            let base = window.location.pathname.replace(/\/main.*/i, '').replace(/\/+$/, '');
            return base + cleanPath;
        },
        imprimirHojaRuta(orden) {
            if (window.abrirModalProcesosHojaRuta) {
                window.abrirModalProcesosHojaRuta(orden);
            } else {
                let id = (orden && (orden.idorden || orden.id)) ? (orden.idorden || orden.id) : orden;
                if (id) window.open('/orden/hoja-ruta/' + id, '_blank');
            }
        },
        gestionarHojaRutaEscaneada(orden) {
            if (window.gestionarHojaRutaEscaneadaModal) {
                window.gestionarHojaRutaEscaneadaModal(orden);
            }
        },
        verEstadisticasProduccionGlobales() {
            if (window.abrirModalEstadisticasProduccionGlobales) {
                window.abrirModalEstadisticasProduccionGlobales();
            }
        }
    }
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('categoria', require('./components/Categoria.vue'));
Vue.component('articulo', require('./components/Articulo.vue'));
Vue.component('tipoproducto', require('./components/TipoProducto.vue'));
Vue.component('cliente', require('./components/Cliente.vue'));
Vue.component('proveedor', require('./components/Proveedor.vue'));
Vue.component('rol', require('./components/Rol.vue'));
Vue.component('user', require('./components/User.vue'));
Vue.component('ingreso', require('./components/Ingreso.vue'));
Vue.component('costop', require('./components/Costop.vue'));
Vue.component('orden', require('./components/Orden.vue'));
Vue.component('programa', require('./components/Programaprod.vue'));
Vue.component('cartera', require('./components/Cartera.vue'));
Vue.component('ventas', require('./components/Ventas.vue'));
Vue.component('seguimientov', require('./components/SeguimientoVenta.vue'));
Vue.component('nuevoproducto', require('./components/partes/NuevoProducto.vue'));
Vue.component('nuevocomprobante', require('./components/NuevoComprobante.vue'));
Vue.component('reportespro', require('./components/Reportespro.vue'));
Vue.component('ajustes', require('./components/Ajustes.vue'));
Vue.component('ingresos', require('./components/Ingresos.vue'));
Vue.component('statuspro', require('./components/Statuspro.vue'));
Vue.component('escritorio', require('./components/Escritorio.vue'));
Vue.component('resetpass', require('./components/Resetpass.vue'));
Vue.component('cambiarpass', require('./components/Cambiarpass.vue'));
Vue.component('crm-main', require('./components/crm/CrmMain.vue'));
Vue.component('actividad', require('./components/Actividad.vue'));
Vue.component('inventarios', require('./components/Inventarios.vue'));
Vue.component('empleados', require('./components/Empleados.vue'));
Vue.component('registroproduccion', require('./components/registrosProduccion/RegistrosProduccion.vue'));
Vue.component('calculotamano', require('./components/partes/CalculoMedida.vue'));
Vue.component('cotizador-0', require('./components/partes/Cotizador0.vue'));
Vue.component('cotizador-r', require('./components/partes/CotizadorR.vue'));
Vue.component('cotizador-g', require('./components/partes/CotizadorG.vue'));
Vue.component('cotizador-m', require('./components/partes/CotizadorM.vue'));
Vue.component('cotizador-s', require('./components/partes/CotizadorS.vue'));
Vue.component('cotizador-impresion', require('./components/partes/CotizadorImpresion.vue'));
Vue.component('chat-component', require('./components/ChatComponent.vue'));
Vue.component('analisisflujo', require('./components/AnalisisFlujo.vue'));
Vue.component('seguimiento-optimizado', require('./components/SeguimientoOptimizado.vue'));
Vue.component('bancoscajas', require('./components/BancosCajas.vue'));
Vue.component('cuentasporpagar', require('./components/CuentasPorPagar.vue'));
Vue.component('pagosprogramados', require('./components/PagosProgramados.vue'));
Vue.component('controlasistencia', require('./components/ControlAsistencia.vue'));
Vue.component('kiosco-pantalla', require('./components/KioscoPantalla.vue'));
Vue.component('gastosegresos', require('./components/GastosEgresos.vue'));
Vue.component('nomina', require('./components/Nomina.vue'));
Vue.component('puc', require('./components/Puc.vue'));
Vue.component('movimientos-contables', require('./components/MovimientosContables.vue'));
Vue.component('reportes-contables', require('./components/ReportesContables.vue'));
Vue.component('simulador-contable', require('./components/SimuladorContable.vue'));
// Trigger recompile
Vue.component('atributos', require('./components/Atributos.vue'));
Vue.component('despiece-muebles', require('./components/DespieceMuebles.vue'));
Vue.component('despiece-optimizador', require('./components/DespieceOptimizador.vue'));
Vue.component('campana-notificaciones', require('./components/CampanaNotificaciones.vue'));
Vue.component('estadisticasventas', require('./components/EstadisticasVentas.vue'));
Vue.component('gastos-casa', require('./components/GastosCasa.vue'));
Vue.component('web-sliders', require('./components/web/WebSliders.vue'));
Vue.component('web-ide', require('./components/superadmin/WebIde.vue'));



const app = new Vue({
    el: '#app',
    data: {
        menu: 0,
        showCalculator: false
    }
});
