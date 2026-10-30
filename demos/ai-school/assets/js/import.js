/**
 * AI School Intelligence — Data Import Center (client-side simulation).
 *
 * CSV files are parsed and validated in the browser; Excel/PDF files are queued as
 * "Pending" because server-side parsing arrives in a later phase. Nothing is sent to
 * the server and results live in browser storage only (cleared by Reset Demo Data).
 */
(function () {
    'use strict';

    var AIS = window.AIS;
    var STORE_KEY = 'imports';
    var MAX_SIZE = 5 * 1024 * 1024;
    var STATUS_LABEL = { imported: 'Imported', pending: 'Pending', failed: 'Failed' };
    var SOURCE_LABEL = { csv: 'CSV', excel: 'Excel', pdf: 'PDF', manual: 'Manual Entry' };

    function parseCsv(text) {
        var rows = [];
        var row = [];
        var field = '';
        var quoted = false;
        text = text.replace(/^﻿/, '');
        for (var i = 0; i < text.length; i++) {
            var ch = text[i];
            if (quoted) {
                if (ch === '"' && text[i + 1] === '"') { field += '"'; i++; }
                else if (ch === '"') quoted = false;
                else field += ch;
            } else if (ch === '"') {
                quoted = true;
            } else if (ch === ',') {
                row.push(field); field = '';
            } else if (ch === '\n' || ch === '\r') {
                if (ch === '\r' && text[i + 1] === '\n') i++;
                row.push(field); field = '';
                if (row.some(function (c) { return c.trim() !== ''; })) rows.push(row);
                row = [];
            } else {
                field += ch;
            }
        }
        row.push(field);
        if (row.some(function (c) { return c.trim() !== ''; })) rows.push(row);
        return rows;
    }

    function now() {
        var d = new Date();
        var pad = function (n) { return String(n).padStart(2, '0'); };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function thaiDate(value) {
        var months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        var p = value.split(/[- :]/);
        return parseInt(p[2], 10) + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + (parseInt(p[0], 10) + 543);
    }

    function init() {
        AIS.log('Import', 'init', 'START');
        var datasetSelect = document.querySelector('[data-import-dataset]');
        var fileInput = document.querySelector('[data-import-file]');
        var dropzone = document.querySelector('[data-dropzone]');
        var result = document.querySelector('[data-import-result]');
        var tbody = document.querySelector('[data-import-rows]');
        var steps = document.querySelectorAll('[data-import-steps] [data-step]');
        if (!datasetSelect || !fileInput || !tbody) return;

        var local = AIS.store.get(STORE_KEY, []);

        function datasetLabel() {
            return datasetSelect.options[datasetSelect.selectedIndex].text;
        }

        function requiredCols() {
            return datasetSelect.options[datasetSelect.selectedIndex].getAttribute('data-cols').split(',').map(function (c) { return c.trim(); });
        }

        function setStep(current, failed) {
            var order = ['upload', 'validate', 'preview', 'import'];
            var idx = order.indexOf(current);
            steps.forEach(function (s) {
                var i = order.indexOf(s.getAttribute('data-step'));
                s.classList.toggle('is-done', i < idx || (i === idx && current === 'import' && !failed));
                s.classList.toggle('is-current', i === idx && !(current === 'import' && !failed));
            });
        }

        function rowHtml(item) {
            return '<td>' + AIS.escapeHtml(item.id) + '</td>'
                + '<td><div class="cell-main"><div><strong>' + AIS.escapeHtml(item.file) + '</strong><small>' + AIS.escapeHtml(SOURCE_LABEL[item.source] || item.source) + ' · ทดลองในเบราว์เซอร์นี้</small></div></div></td>'
                + '<td>' + AIS.escapeHtml(item.dataset) + '</td>'
                + '<td class="num">' + Number(item.records).toLocaleString('th-TH') + '</td>'
                + '<td><span class="badge badge--' + item.status + '">' + STATUS_LABEL[item.status] + '</span></td>'
                + '<td data-value="' + AIS.escapeHtml(item.date) + '">' + thaiDate(item.date) + '</td>'
                + '<td><small class="muted">' + AIS.escapeHtml(item.note) + '</small></td>';
        }

        function insertRow(item) {
            var tr = document.createElement('tr');
            tr.setAttribute('data-status', item.status);
            tr.innerHTML = rowHtml(item);
            tbody.insertBefore(tr, tbody.firstChild);
        }

        function refreshCounts() {
            var counts = { imported: 0, pending: 0, failed: 0 };
            var total = 0;
            tbody.querySelectorAll('tr[data-status]').forEach(function (tr) {
                counts[tr.getAttribute('data-status')]++;
                total++;
            });
            Object.keys(counts).forEach(function (k) {
                var el = document.querySelector('[data-import-count="' + k + '"]');
                if (el) el.textContent = counts[k];
            });
            var t = document.querySelector('[data-import-total]');
            if (t) t.textContent = total;
            document.querySelectorAll('[data-table-count="import-table"]').forEach(function (el) { el.textContent = total; });
        }

        function addHistory(item) {
            item.id = 'IMP-L' + String(local.length + 1).padStart(3, '0');
            item.date = now();
            local.push(item);
            AIS.store.set(STORE_KEY, local);
            insertRow(item);
            refreshCounts();
            AIS.log('Import', 'addHistory', 'added', item);
        }

        function showValidation(lines, extra) {
            result.innerHTML = '<div class="validation">' + lines.map(function (l) {
                return '<p class="' + l[0] + '">' + AIS.icon(l[0] === 'ok' ? 'circle-check' : (l[0] === 'err' ? 'circle-x' : 'triangle-alert')) + AIS.escapeHtml(l[1]) + '</p>';
            }).join('') + '</div>' + (extra || '');
            AIS.renderIcons(result);
        }

        function handleCsv(file) {
            var reader = new FileReader();
            reader.onerror = function () {
                AIS.logError('Import', 'handleCsv', reader.error, { file: file.name });
                showValidation([['err', 'ไม่สามารถอ่านไฟล์ได้ กรุณาลองใหม่อีกครั้ง']]);
            };
            reader.onload = function () {
                setStep('validate');
                var rows = parseCsv(String(reader.result));
                var header = (rows[0] || []).map(function (h) { return h.trim().toLowerCase(); });
                var data = rows.slice(1);
                var required = requiredCols();
                var missing = required.filter(function (c) { return header.indexOf(c) === -1; });
                var emptyCells = 0;
                data.forEach(function (r) { r.forEach(function (c) { if (c.trim() === '') emptyCells++; }); });
                AIS.devGroup('CSV Validation', { File: file.name, Header: header, Rows: data.length, Missing: missing });

                if (!rows.length || missing.length) {
                    setStep('validate', true);
                    showValidation([
                        ['err', missing.length ? 'ไม่พบคอลัมน์ที่จำเป็น: ' + missing.join(', ') : 'ไม่พบข้อมูลในไฟล์'],
                        ['warn', 'กรุณาดาวน์โหลดเทมเพลต "' + datasetLabel() + '" แล้วลองใหม่อีกครั้ง']
                    ]);
                    addHistory({ file: file.name, source: 'csv', dataset: datasetLabel(), records: data.length, status: 'failed', note: 'คอลัมน์ไม่ตรงเทมเพลต' });
                    return;
                }

                setStep('preview');
                var preview = '<div class="table-wrap preview-box"><table class="table"><thead><tr>' + header.map(function (h) { return '<th>' + AIS.escapeHtml(h) + '</th>'; }).join('') + '</tr></thead><tbody>'
                    + data.slice(0, 5).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + AIS.escapeHtml(c) + '</td>'; }).join('') + '</tr>'; }).join('')
                    + '</tbody></table></div><p class="chart-note">แสดง 5 แถวแรกจาก ' + data.length.toLocaleString('th-TH') + ' แถว</p>'
                    + '<div class="toolbar" style="margin-top:12px"><button type="button" class="btn btn--primary" data-import-confirm>' + AIS.icon('database') + 'นำเข้า ' + data.length.toLocaleString('th-TH') + ' รายการ</button><button type="button" class="btn btn--ghost" data-import-cancel>ยกเลิก</button></div>';
                showValidation([
                    ['ok', 'คอลัมน์ครบตามเทมเพลต (' + required.length + ' คอลัมน์)'],
                    ['ok', 'พบข้อมูล ' + data.length.toLocaleString('th-TH') + ' แถว'],
                    [emptyCells ? 'warn' : 'ok', emptyCells ? 'พบช่องว่าง ' + emptyCells + ' ช่อง ระบบจะบันทึกเป็นค่าว่าง' : 'ไม่พบช่องข้อมูลว่าง']
                ], preview);

                result.querySelector('[data-import-confirm]').addEventListener('click', function (e) {
                    e.currentTarget.disabled = true;
                    e.currentTarget.textContent = 'กำลังนำเข้า…';
                    setTimeout(function () {
                        setStep('import');
                        addHistory({ file: file.name, source: 'csv', dataset: datasetLabel(), records: data.length, status: 'imported', note: 'นำเข้าสำเร็จ (จำลองในเบราว์เซอร์)' });
                        showValidation([['ok', 'นำเข้าข้อมูลสำเร็จ ' + data.length.toLocaleString('th-TH') + ' รายการ']]);
                        AIS.toast('นำเข้าข้อมูลสำเร็จ (Demo)', 'circle-check');
                    }, 900);
                });
                result.querySelector('[data-import-cancel]').addEventListener('click', function () {
                    result.innerHTML = '';
                    setStep('upload');
                });
            };
            reader.readAsText(file, 'utf-8');
        }

        function handleFile(file) {
            AIS.log('Import', 'handleFile', 'START', { name: file.name, size: file.size });
            setStep('upload');
            var ext = (file.name.split('.').pop() || '').toLowerCase();
            if (file.size > MAX_SIZE) {
                showValidation([['err', 'ไฟล์มีขนาดเกิน 5 MB กรุณาแบ่งไฟล์แล้วลองใหม่อีกครั้ง']]);
                return;
            }
            if (ext === 'csv') {
                handleCsv(file);
            } else if (ext === 'xlsx' || ext === 'xls' || ext === 'pdf') {
                setStep('validate');
                var isPdf = ext === 'pdf';
                showValidation([
                    ['ok', 'รับไฟล์ ' + file.name + ' แล้ว'],
                    ['warn', isPdf ? 'ไฟล์ PDF ต้องตรวจสอบตารางโดยเจ้าหน้าที่ก่อนนำเข้า (Pending)' : 'ไฟล์ Excel จะถูกแปลงและตรวจสอบโดยเซิร์ฟเวอร์ในเวอร์ชันใช้งานจริง — ในเดโมบันทึกเป็น Pending']
                ]);
                addHistory({ file: file.name, source: isPdf ? 'pdf' : 'excel', dataset: datasetLabel(), records: 0, status: 'pending', note: isPdf ? 'รอตรวจสอบตารางจาก PDF' : 'รอแปลงไฟล์ Excel (Demo)' });
            } else {
                showValidation([['err', 'ไม่รองรับไฟล์ประเภทนี้ กรุณาใช้ .csv .xlsx .xls หรือ .pdf']]);
            }
            fileInput.value = '';
        }

        function openManual() {
            AIS.openModal({
                eyebrow: 'Manual Entry',
                title: 'บันทึกการดูแลช่วยเหลือนักเรียน',
                html: '<form class="form-grid" id="manual-form">'
                    + '<div class="field"><label for="m-code">รหัสนักเรียน</label><input class="input" id="m-code" required placeholder="เช่น 6901015"></div>'
                    + '<div class="field"><label for="m-type">ประเภทบันทึก</label><select class="select" id="m-type"><option>พูดคุยกับนักเรียน</option><option>ติดต่อผู้ปกครอง</option><option>สอนเสริม</option><option>ส่งต่อครูแนะแนว</option></select></div>'
                    + '<div class="field full"><label for="m-note">รายละเอียด</label><textarea class="textarea" id="m-note" placeholder="สรุปการช่วยเหลือ (ไม่บันทึกถาวรในเดโม)"></textarea></div>'
                    + '</form>',
                foot: '<button type="button" class="btn btn--ghost" data-modal-close>ยกเลิก</button><button type="submit" form="manual-form" class="btn btn--primary">' + AIS.icon('save') + 'บันทึก</button>',
                onOpen: function (modal) {
                    modal.querySelector('#manual-form').addEventListener('submit', function (e) {
                        e.preventDefault();
                        var code = modal.querySelector('#m-code').value.trim();
                        var type = modal.querySelector('#m-type').value;
                        addHistory({ file: 'manual-entry', source: 'manual', dataset: 'บันทึกการดูแลช่วยเหลือ', records: 1, status: 'imported', note: type + ' · ' + code });
                        AIS.closeModal();
                        AIS.toast('บันทึกเรียบร้อย (Demo)', 'circle-check');
                    });
                }
            });
        }

        datasetSelect.addEventListener('change', function () {
            document.querySelector('[data-import-cols]').textContent = requiredCols().join(', ');
        });
        fileInput.addEventListener('change', function () {
            if (fileInput.files[0]) handleFile(fileInput.files[0]);
        });
        ['dragenter', 'dragover'].forEach(function (type) {
            dropzone.addEventListener(type, function (e) { e.preventDefault(); dropzone.classList.add('is-over'); });
        });
        ['dragleave', 'drop'].forEach(function (type) {
            dropzone.addEventListener(type, function (e) { e.preventDefault(); dropzone.classList.remove('is-over'); });
        });
        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
        });

        document.querySelectorAll('[data-source]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var source = btn.getAttribute('data-source');
                AIS.log('Import', 'selectSource', 'clicked', { source: source });
                if (source === 'manual') { openManual(); return; }
                fileInput.setAttribute('accept', source === 'excel' ? '.xlsx,.xls' : (source === 'pdf' ? '.pdf' : '.csv'));
                document.getElementById('upload').scrollIntoView({ behavior: 'smooth', block: 'start' });
                fileInput.click();
            });
        });

        local.forEach(insertRow);
        refreshCounts();
        setStep('upload');
        AIS.log('Import', 'init', 'END', { local: local.length });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
