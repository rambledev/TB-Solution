/**
 * EduCare — Shared Data Store.
 *
 * One store for every role, persisted in LocalStorage. The first visit copies the seed
 * embedded by PHP; later visits (and other tabs) read the same data, so what a teacher
 * records is what the parent and the executive see. Changes made in another tab are
 * picked up through the "storage" event.
 */
(function () {
    'use strict';

    var CONFIG = window.EDU_CONFIG || {};
    var IS_DEV = Boolean(CONFIG.debug);
    var KEY = 'educare.demo.v1';
    var state = null;
    var listeners = [];
    var counter = 0;

    function log(fn, message, data) {
        if (!IS_DEV) return;
        if (data === undefined) console.log('[Store][' + fn + '] ' + message);
        else console.log('[Store][' + fn + '] ' + message, data);
    }
    function logError(fn, error, context) {
        console.error('[Store][' + fn + '] ERROR', { error: error, context: context });
    }

    function readSeed() {
        var el = document.getElementById('edu-seed');
        if (!el) return null;
        try { return JSON.parse(el.textContent); } catch (error) { logError('readSeed', error); return null; }
    }

    function readStored() {
        try {
            var raw = localStorage.getItem(KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            logError('readStored', error);
            return null;
        }
    }

    function valid(data) {
        return Boolean(data && data.meta && Array.isArray(data.students) && data.attendance && Array.isArray(data.notifications));
    }

    /** Persist; returns false when the browser storage is full or unavailable. */
    function persist() {
        try {
            localStorage.setItem(KEY, JSON.stringify(state));
            return true;
        } catch (error) {
            logError('persist', error, { bytes: JSON.stringify(state).length });
            return false;
        }
    }

    function emit(reason, detail) {
        log('emit', 'changed', { reason: reason });
        listeners.forEach(function (fn) {
            try { fn(reason, detail); } catch (error) { logError('emit', error, { reason: reason }); }
        });
    }

    var store = {
        KEY: KEY,

        load: function () {
            log('load', 'START');
            var stored = readStored();
            if (valid(stored)) {
                state = stored;
            } else {
                var seed = readSeed();
                if (valid(seed)) {
                    state = seed;
                    state.meta.changes = 0;
                    persist();
                }
            }
            log('load', 'END', { source: valid(stored) ? 'storage' : 'seed', ok: valid(state) });
            return state;
        },

        get: function () { return state; },
        hasStored: function () { return valid(readStored()); },
        changes: function () { var s = readStored(); return s && s.meta ? (s.meta.changes || 0) : 0; },

        /** Apply a mutation, save once and notify listeners. Returns { ok, quota }. */
        update: function (reason, mutate, detail) {
            log('update', 'START', { reason: reason });
            var backup = JSON.stringify(state);
            try {
                mutate(state);
                state.meta.changes = (state.meta.changes || 0) + 1;
                state.meta.updatedAt = new Date().toISOString();
            } catch (error) {
                logError('update', error, { reason: reason });
                state = JSON.parse(backup);
                return { ok: false };
            }
            if (!persist()) {
                state = JSON.parse(backup);
                return { ok: false, quota: true };
            }
            emit(reason, detail);
            log('update', 'END', { reason: reason });
            return { ok: true };
        },

        reset: function () {
            log('reset', 'START');
            try { localStorage.removeItem(KEY); } catch (error) { logError('reset', error); }
            state = null;
            log('reset', 'END');
        },

        id: function (prefix) {
            counter += 1;
            return prefix + Date.now().toString(36).toUpperCase() + counter;
        },

        onChange: function (fn) { listeners.push(fn); }
    };

    // Cross-tab sync: another tab saved → reload and re-render.
    window.addEventListener('storage', function (event) {
        if (event.key !== KEY) return;
        log('storage', 'external change detected');
        if (event.newValue === null) {
            store.load();
            emit('external-reset');
            return;
        }
        var next = readStored();
        if (valid(next)) {
            state = next;
            emit('external');
        }
    });

    window.EDU_STORE = store;
})();
