/**
 * AI Executive Dashboard — Mock Data store.
 *
 * One dataset is shared by every module. It is loaded from LocalStorage when present,
 * otherwise from the default dataset embedded by PHP. Generate/Randomize fetch a new
 * dataset from api/mock-data.php; Reset removes the stored copy and returns to default.
 */
(function () {
    'use strict';

    var CONFIG = window.EXD_CONFIG || {};
    var IS_DEV = Boolean(CONFIG.debug);
    var STORAGE_KEY = 'exd.dataset';
    var listeners = [];
    var state = { data: null, source: 'default' };

    function log(fn, message, data) {
        if (!IS_DEV) return;
        if (data === undefined) console.log('[MockData][' + fn + '] ' + message);
        else console.log('[MockData][' + fn + '] ' + message, data);
    }

    function logError(fn, error, context) {
        console.error('[MockData][' + fn + '] ERROR', { error: error, context: context });
    }

    function readEmbedded() {
        var el = document.getElementById('default-dataset');
        if (!el) return null;
        try {
            return JSON.parse(el.textContent);
        } catch (error) {
            logError('readEmbedded', error);
            return null;
        }
    }

    function isValid(data) {
        return Boolean(data && data.meta && Array.isArray(data.students) && Array.isArray(data.teachers) && Array.isArray(data.classrooms));
    }

    function save(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            return true;
        } catch (error) {
            logError('save', error, { size: JSON.stringify(data).length });
            return false;
        }
    }

    function emit(reason) {
        log('emit', 'data changed', { reason: reason, scenario: state.data && state.data.meta.scenario });
        listeners.forEach(function (fn) {
            try { fn(state.data, reason); } catch (error) { logError('emit', error, { reason: reason }); }
        });
    }

    function load() {
        log('load', 'START');
        var stored = null;
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            stored = raw ? JSON.parse(raw) : null;
        } catch (error) {
            logError('load', error);
        }
        if (isValid(stored)) {
            state.data = stored;
            state.source = 'storage';
        } else {
            state.data = readEmbedded();
            state.source = 'default';
        }
        log('load', 'END', { source: state.source, valid: isValid(state.data) });
        return state.data;
    }

    /** Fetch a dataset for a scenario + seed from the PHP generator. */
    function generate(scenario, seed) {
        var url = CONFIG.api.mockData + '?scenario=' + encodeURIComponent(scenario) + '&seed=' + encodeURIComponent(seed);
        log('generate', 'REQUEST', { url: url });
        return fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (res) {
                return res.json().catch(function () { return { ok: false }; }).then(function (body) {
                    if (IS_DEV) {
                        console.group('[DEV] API Response');
                        console.log('URL:', url);
                        console.log('Status:', res.status);
                        console.log('Response:', body);
                        console.groupEnd();
                    }
                    if (!res.ok || !body.ok || !isValid(body.data)) {
                        var err = new Error('Generate failed');
                        err.userMessage = body && body.message ? body.message : 'ไม่สามารถสร้างข้อมูลได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง';
                        throw err;
                    }
                    state.data = body.data;
                    state.source = save(body.data) ? 'storage' : 'memory';
                    log('generate', 'RESPONSE', { scenario: scenario, seed: seed, students: body.data.students.length });
                    emit('generate');
                    return body.data;
                });
            });
    }

    function randomSeed() {
        return Math.floor(Math.random() * 899999) + 100000;
    }

    function reset() {
        log('reset', 'START');
        try { localStorage.removeItem(STORAGE_KEY); } catch (error) { logError('reset', error); }
        state.data = readEmbedded();
        state.source = 'default';
        emit('reset');
        log('reset', 'END');
        return state.data;
    }

    window.EXD_STORE = {
        load: load,
        generate: generate,
        reset: reset,
        randomSeed: randomSeed,
        isValid: isValid,
        get: function () { return state.data; },
        source: function () { return state.source; },
        onChange: function (fn) { listeners.push(fn); }
    };
})();
