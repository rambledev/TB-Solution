/**
 * AI School Intelligence — AI Chat Assistant (calls api/chat.php).
 * Chat history is kept in browser storage only; Reset Demo Data clears it.
 */
(function () {
    'use strict';

    var AIS = window.AIS;
    var HISTORY_KEY = 'chatHistory';
    var MAX_HISTORY = 30;

    function init() {
        AIS.log('Chat', 'init', 'START');
        var root = document.querySelector('[data-chat]');
        if (!root) return;

        var log = root.querySelector('[data-chat-log]');
        var form = root.querySelector('[data-chat-form]');
        var input = form.querySelector('input');
        var suggest = root.querySelector('[data-chat-suggest]');
        var history = AIS.store.get(HISTORY_KEY, []);
        var busy = false;

        function scroll() {
            log.scrollTop = log.scrollHeight;
        }

        function addMessage(role, text, meta) {
            var el = document.createElement('div');
            el.className = 'msg' + (role === 'user' ? ' msg--user' : '');
            var body = role === 'user' ? '<p>' + AIS.escapeHtml(text) + '</p>' : AIS.formatText(text);
            if (meta && meta.sources && meta.sources.length) {
                body += '<div class="msg__meta">แหล่งข้อมูล: ' + meta.sources.map(AIS.escapeHtml).join(' · ') + '</div>';
            }
            el.innerHTML = '<span class="msg__avatar">' + AIS.icon(role === 'user' ? 'user-round' : 'sparkles') + '</span><div class="msg__bubble">' + body + '</div>';
            log.appendChild(el);
            AIS.renderIcons(el);
            scroll();
            return el;
        }

        function showTyping() {
            var el = document.createElement('div');
            el.className = 'msg';
            el.innerHTML = '<span class="msg__avatar">' + AIS.icon('sparkles') + '</span><div class="msg__bubble"><span class="typing"><span></span><span></span><span></span></span></div>';
            log.appendChild(el);
            AIS.renderIcons(el);
            scroll();
            return el;
        }

        function setSuggestions(list) {
            if (!list || !list.length) return;
            suggest.innerHTML = list.map(function (q) {
                return '<button type="button" data-chat-ask="' + AIS.escapeHtml(q) + '">' + AIS.escapeHtml(q) + '</button>';
            }).join('');
        }

        function save() {
            history = history.slice(-MAX_HISTORY);
            AIS.store.set(HISTORY_KEY, history);
        }

        function ask(question) {
            question = String(question || '').trim();
            if (!question || busy) return;
            AIS.log('Chat', 'ask', 'START', { question: question });
            busy = true;
            input.value = '';
            addMessage('user', question);
            var typing = showTyping();
            var started = Date.now();
            var payload = { message: question, history: history.slice(-6) };

            AIS.postJson(AIS.config.api.chat, payload)
                .then(function (data) {
                    // Keep a short "thinking" delay so the simulation feels natural.
                    var wait = Math.max(0, 650 - (Date.now() - started));
                    setTimeout(function () {
                        typing.remove();
                        addMessage('assistant', data.text, data);
                        setSuggestions(data.suggestions);
                        history.push({ role: 'user', content: question });
                        history.push({ role: 'assistant', content: data.text, sources: data.sources || [] });
                        save();
                        busy = false;
                        AIS.log('Chat', 'ask', 'END', { provider: data.provider, intent: data.intent });
                    }, wait);
                })
                .catch(function (error) {
                    AIS.logError('Chat', 'ask', error, { question: question });
                    typing.remove();
                    addMessage('assistant', error.userMessage || AIS.messages.genericError);
                    busy = false;
                });
        }

        // Restore previous conversation (per-viewer convenience).
        history.forEach(function (turn) {
            addMessage(turn.role, turn.content, { sources: turn.sources || [] });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            ask(input.value);
        });

        root.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-chat-ask]');
            if (btn) ask(btn.getAttribute('data-chat-ask'));
        });

        var clear = root.querySelector('[data-chat-clear]');
        if (clear) {
            clear.addEventListener('click', function () {
                history = [];
                save();
                Array.prototype.slice.call(log.children, 1).forEach(function (el) { el.remove(); });
                AIS.toast('ล้างประวัติแชตแล้ว', 'trash-2');
            });
        }

        // Allow other pages to deep-link a question: ?page=ai-insights&ask=...
        var params = new URLSearchParams(window.location.search);
        if (params.get('ask')) ask(params.get('ask'));

        AIS.log('Chat', 'init', 'END', { restored: history.length });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
