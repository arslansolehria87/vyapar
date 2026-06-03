(function () {
  const registry = new Map();

  function getEl(id) {
    return id ? document.getElementById(id) : null;
  }

  function resolveValue(value, context) {
    if (typeof value === 'function') {
      return value(context);
    }
    return value;
  }

  function readContextFromElement(el) {
    if (!el) return {};
    return {
      previewUrl: el.dataset?.previewUrl || '',
      pdfUrl: el.dataset?.pdfUrl || '',
      printUrl: el.dataset?.printUrl || '',
      partyEmail: el.dataset?.partyEmail || '',
      partyName: el.dataset?.partyName || '',
      saleNumber: el.dataset?.saleNumber || '',
      emailUrl: el.dataset?.emailUrl || '',
      documentLabel: el.dataset?.documentLabel || '',
    };
  }

  function createToast(message, isError, toastId) {
    const targetId = toastId || 'documentEmailToast';
    let toastEl = document.getElementById(targetId);
    if (!toastEl) {
      const wrap = document.createElement('div');
      wrap.className = 'position-fixed bottom-0 end-0 p-3';
      wrap.style.zIndex = '1085';
      wrap.innerHTML = `
        <div id="${targetId}" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      `;
      document.body.appendChild(wrap);
      toastEl = document.getElementById(targetId);
    }

    const body = toastEl.querySelector('.toast-body');
    if (body) body.textContent = message;
    toastEl.classList.toggle('text-bg-success', !isError);
    toastEl.classList.toggle('text-bg-danger', isError);
    bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4500 }).show();
  }

  function releaseModalFocus(modalEl) {
    if (!modalEl) return;
    const active = document.activeElement;
    if (active && modalEl.contains(active) && typeof active.blur === 'function') {
      active.blur();
    }

    if (typeof document.body?.focus === 'function') {
      document.body.setAttribute('tabindex', '-1');
      document.body.focus({ preventScroll: true });
    }
  }

  function init(userConfig) {
    const cfg = Object.assign({
      name: 'document-email',
      previewModalId: null,
      previewFrameId: null,
      emailModalId: null,
      emailToId: null,
      emailSubjectId: null,
      emailMessageId: null,
      viewPdfBtnId: null,
      sendBtnId: null,
      openButtonId: null,
      toastId: null,
      defaultSubject: 'Your Vyapar PDF',
      defaultMessage: "Dear Sir,\nPlease find the attached document below.\nThank you for doing business with us.\nThanks and regards.",
      bindOpenButton: true,
      getContext: null,
    }, userConfig || {});

    const previewModalEl = getEl(cfg.previewModalId);
    const previewModal = previewModalEl ? bootstrap.Modal.getOrCreateInstance(previewModalEl) : null;
    const previewFrameEl = getEl(cfg.previewFrameId);
    const emailModalEl = getEl(cfg.emailModalId);
    const emailModal = emailModalEl ? bootstrap.Modal.getOrCreateInstance(emailModalEl) : null;
    const emailToEl = getEl(cfg.emailToId);
    const emailSubjectEl = getEl(cfg.emailSubjectId);
    const emailMessageEl = getEl(cfg.emailMessageId);
    const viewPdfBtn = getEl(cfg.viewPdfBtnId);
    const sendBtn = getEl(cfg.sendBtnId);
    const openBtn = getEl(cfg.openButtonId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function getContext(trigger) {
      const frameContext = readContextFromElement(previewFrameEl);
      const triggerContext = readContextFromElement(trigger?.closest?.('.action-menu') || trigger);
      const merged = Object.assign({}, frameContext, triggerContext);

      if (typeof cfg.getContext === 'function') {
        const extra = cfg.getContext({
          trigger,
          previewModalEl,
          previewFrameEl,
          emailModalEl,
          context: merged,
        }) || {};
        return Object.assign(merged, extra);
      }

      return merged;
    }

    function updateFields(context, emailUrl) {
      if (!emailModalEl || !emailToEl || !emailSubjectEl || !emailMessageEl) return;
      emailModalEl.dataset.emailUrl = emailUrl || context.emailUrl || '';
      emailToEl.value = context.partyEmail || '';
      emailSubjectEl.value = resolveValue(cfg.defaultSubject, context) || 'Your Vyapar PDF';
      emailMessageEl.value = resolveValue(cfg.defaultMessage, context) || '';
    }

    function open(trigger) {
      const context = getContext(trigger);
      const emailUrl = context.emailUrl || emailModalEl?.dataset?.emailUrl || '';
      const pdfUrl = context.pdfUrl || context.previewUrl || previewFrameEl?.src || '';

      if (!emailModal || !emailModalEl || !emailToEl || !emailSubjectEl || !emailMessageEl) {
        if (pdfUrl) {
          window.open(pdfUrl, '_blank', 'noopener');
        }
        return;
      }

      if (!emailUrl) {
        createToast('Email URL missing for this document.', true, cfg.toastId);
        return;
      }

      const launch = () => {
        updateFields(context, emailUrl);
        emailModal.show();
      };

      if (previewModalEl && previewModalEl.classList.contains('show')) {
        releaseModalFocus(previewModalEl);
        previewModal.hide();
        window.setTimeout(launch, 350);
        return;
      }

      launch();
    }

    function send() {
      if (!emailModalEl || !emailToEl || !emailSubjectEl || !emailMessageEl) return;

      const emailUrl = emailModalEl.dataset?.emailUrl || previewFrameEl?.dataset?.emailUrl || '';
      if (!emailUrl) {
        createToast('Email URL missing for this document.', true, cfg.toastId);
        return;
      }

      const to = (emailToEl.value || '').trim();
      if (!to) {
        createToast('Please enter an email address.', true, cfg.toastId);
        emailToEl.focus();
        return;
      }

      const subject = (emailSubjectEl.value || '').trim() || resolveValue(cfg.defaultSubject, getContext()) || 'Your Vyapar PDF';
      const message = (emailMessageEl.value || '').trim();

      if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';
      }

      fetch(emailUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: to, subject, message }),
      })
        .then(async (res) => {
          const data = await res.json().catch(() => ({}));
          if (!res.ok) {
            throw new Error(data.message || 'Unable to send email.');
          }
          return data;
        })
        .then(() => {
          createToast('Email sent successfully.', false, cfg.toastId);
          emailModal.hide();
        })
        .catch((err) => {
          createToast(err.message || 'Unable to send email.', true, cfg.toastId);
        })
        .finally(() => {
          if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
          }
        });
    }

    function viewPdf() {
      const context = getContext();
      const pdfUrl = context.pdfUrl || context.previewUrl || previewFrameEl?.src || '';
      if (pdfUrl) {
        window.open(pdfUrl, '_blank', 'noopener');
      }
    }

    if (openBtn && cfg.bindOpenButton !== false) {
      openBtn.addEventListener('click', function () {
        open();
      });
    }

    if (viewPdfBtn) {
      viewPdfBtn.addEventListener('click', viewPdf);
    }

    if (sendBtn) {
      sendBtn.addEventListener('click', send);
    }

    const api = { open, send, getContext };
    registry.set(cfg.name || cfg.emailModalId || 'document-email', api);
    return api;
  }

  window.DocumentEmailPreview = {
    init,
    get(name) {
      return registry.get(name) || null;
    },
  };
})();
