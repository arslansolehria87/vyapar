@php
    $modalId = $modalId ?? 'documentEmailModal';
    $toId = $toId ?? 'documentEmailTo';
    $subjectId = $subjectId ?? 'documentEmailSubject';
    $messageId = $messageId ?? 'documentEmailMessage';
    $viewPdfBtnId = $viewPdfBtnId ?? 'documentEmailViewPdfBtn';
    $sendBtnId = $sendBtnId ?? 'documentEmailSendBtn';
    $title = $title ?? 'Send Email';
    $toLabel = $toLabel ?? 'To:';
    $toPlaceholder = $toPlaceholder ?? 'Enter email';
    $subjectValue = $subjectValue ?? 'Your Vyapar PDF';
    $messageValue = $messageValue ?? "Dear Sir,\nPlease find the attached document below.\nThank you for doing business with us.\nThanks and regards.";
    $helperText = $helperText ?? 'The PDF will be attached automatically.';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true" data-email-url="">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="mb-3">
          <label for="{{ $toId }}" class="form-label small text-muted fw-semibold">{{ $toLabel }}<span class="text-danger">*</span></label>
          <input type="email" class="form-control rounded-4" id="{{ $toId }}" placeholder="{{ $toPlaceholder }}">
        </div>
        <div class="mb-3">
          <label for="{{ $subjectId }}" class="form-label small text-muted fw-semibold">Subject</label>
          <input type="text" class="form-control rounded-4" id="{{ $subjectId }}" value="{{ $subjectValue }}">
        </div>
        <div class="mb-2">
          <label for="{{ $messageId }}" class="form-label small text-muted fw-semibold">Message</label>
          <textarea class="form-control rounded-4" id="{{ $messageId }}" rows="5">{{ $messageValue }}</textarea>
          <div class="form-text mt-2">{{ $helperText }}</div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center gap-3">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="{{ $viewPdfBtnId }}">View PDF</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="{{ $sendBtnId }}">Send</button>
      </div>
    </div>
  </div>
</div>
