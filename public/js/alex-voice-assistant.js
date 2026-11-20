/**
 * ═══════════════════════════════════════════════════════════
 *  ALEX AI — Urdu Voice Khata & Fast Billing Assistant (v2)
 *  With Live Audio Visualizer, Error Handling & Fallbacks
 *  Built for Hexasolv ERP / Vyapar
 * ═══════════════════════════════════════════════════════════
 */

(function () {
  'use strict';

  // State
  let isRecording = false;
  let recognition = null;
  let audioContext = null;
  let analyser = null;
  let micStream = null;
  let animFrameId = null;
  let currentParsedData = null;
  let recognitionLanguage = 'ur-PK';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.App?.csrfToken || '';
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;

  /**
   * Real-time Devanagari (Hindi) to authentic Urdu Nastaliq transliteration
   * Converts any Hindi transcript (phonetically spoken Urdu) into pure Urdu script in real-time.
   */
  function devanagariToUrdu(text) {
    if (!text || !/[\u0900-\u097F]/.test(text)) {
      return text;
    }

    // 1. Common words & phrases dictionary
    const wordMap = [
      // Greetings & Conversational
      ['हैलो', 'ہیلو'], ['हेलो', 'ہیلو'], ['हाउ', 'ہاؤ'], ['आर', 'آر'],
      ['यूआइ', 'یو آئی'], ['यूआई', 'یو آئی'], ['यू', 'یو'], ['आई', 'آئی'], ['एम', 'ایم'],
      ['यार', 'یار'], ['मुझे', 'مجھے'], ['उर्दू', 'اردو'], ['में', 'میں'],
      ['क्यों', 'کیوں'], ['नहीं', 'نہیں'], ['शो', 'شو'], ['हो', 'ہو'],
      ['रहा', 'رہا'], ['सब', 'سب'], ['कुछ', 'کچھ'], ['कैसे', 'کیسے'],
      ['और', 'اور'], ['क्या', 'کیا'], ['है', 'ہے'], ['हैं', 'ہیں'],
      ['था', 'تھا'], ['थी', 'تھی'], ['थे', 'تھے'],

      // Customer Names & Roles
      ['अरसलन', 'ارسلان'], ['अरसलान', 'ارسلان'], ['अर्सलान', 'ارسلان'],
      ['हमज़ा', 'حمزہ'], ['हमजा', 'حمزہ'], ['अली', 'علی'],
      ['उस्मान', 'عثمان'], ['अहमद', 'احمد'], ['बिलाल', 'بلال'],
      ['हसन', 'حسن'], ['हुसैन', 'حسین'], ['भाई', 'بھائی'],

      // Grocery & Shop Items
      ['चीनी', 'چینی'], ['घी', 'گھی'], ['तेल', 'تیل'], ['आटा', 'آٹا'],
      ['चावल', 'چاول'], ['दाल', 'دال'], ['दूध', 'دودھ'], ['पत्ती', 'پتی'],
      ['चाय', 'چائے'], ['साबुन', 'صابن'], ['सर्फ', 'سرف'], ['सामान', 'سامان'],
      ['दुकान', 'دکان'],

      // Units & Quantities
      ['किलो', 'کلو'], ['ग्राम', 'گرام'], ['लीटर', 'لیٹر'], ['पाव', 'پاؤ'], ['पाओ', 'پاؤ'],
      ['दर्जन', 'درجن'], ['पैकेट', 'پیکٹ'], ['बोरी', 'بوری'], ['डब्बा', 'ڈبہ'],
      ['عدد', 'عدد'], ['पीस', 'پیس'],

      // Numbers
      ['एक', 'ایک'], ['दो', 'دو'], ['तीन', 'تین'], ['चार', 'چار'], ['पांच', 'پانچ'],
      ['पाँच', 'پانچ'], ['छह', 'چھ'], ['सात', 'سات'], ['आठ', 'آٹھ'], ['नौ', 'نو'], ['दस', 'دس'],

      // Khata & Transactions
      ['ने', 'نے'], ['आज', 'آج'], ['लिया', 'لیا'], ['खाता', 'کھاتہ'], ['हिसाब', 'حساب'],
      ['दिखाओ', 'دکھاؤ'], ['बताओ', 'بتاؤ'], ['चेक', 'چیک'], ['रुपये', 'روپے'], ['रुपए', 'روپے'],
      ['पैसे', 'پیسے'], ['दिए', 'دیے'], ['दिया', 'دیا'], ['जमा', 'جمع'], ['बकाया', 'بقایا'],
      ['बाकी', 'باقی'], ['उधार', 'ادھار'], ['नकद', 'نقد'], ['स्टॉक', 'سٹاک'], ['कितना', 'کتنا'],
      ['कितने', 'کتنے'], ['कितनी', 'کتنی'], ['چاہیے', 'چاہیے'], ['करो', 'کرو'], ['करें', 'کریں']
    ];

    for (let i = 0; i < wordMap.length; i++) {
      const hi = wordMap[i][0];
      const ur = wordMap[i][1];
      text = text.split(hi).join(ur);
    }

    // 2. Character-level fallback for remaining letters
    const charMap = {
      'अ': 'ا', 'आ': 'آ', 'इ': 'اِ', 'ई': 'ای', 'उ': 'اُ', 'ऊ': 'او',
      'ए': 'اے', 'ऐ': 'اے', 'ओ': 'او', 'औ': 'او',
      'क': 'ک', 'ख': 'کھ', 'ग': 'گ', 'घ': 'گھ',
      'च': 'چ', 'छ': 'چھ', 'ज': 'ج', 'झ': 'جھ',
      'ट': 'ٹ', 'ठ': 'ٹھ', 'ड': 'ڈ', 'ढ': 'ڈھ',
      'त': 'ت', 'थ': 'تھ', 'द': 'د', 'ध': 'دھ', 'न': 'ن',
      'प': 'پ', 'फ': 'ف', 'ब': 'ب', 'भ': 'بھ', 'म': 'م',
      'य': 'ی', 'र': 'ر', 'ल': 'ل', 'व': 'و', 'श': 'ش', 'ष': 'ش', 'स': 'س', 'ह': 'ہ',
      'ा': 'ا', 'ि': 'ِ', 'ी': 'ی', 'ु': 'ُ', 'ू': 'و', 'े': 'ے', 'ै': 'ے', 'ो': 'و', 'ौ': 'و',
      '्': '', 'ं': 'ں', 'ँ': 'ں', 'ः': 'ہ', '़': '', 'ड़': 'ڑ', 'ढ़': 'ڑھ',
      'क़': 'ق', 'ख़': 'خ', 'ग़': 'غ', 'ज़': 'ز', 'फ़': 'ف'
    };

    let res = '';
    for (let j = 0; j < text.length; j++) {
      const two = text.substr(j, 2);
      if (charMap[two] !== undefined) {
        res += charMap[two];
        j++;
        continue;
      }
      const ch = text[j];
      res += charMap[ch] !== undefined ? charMap[ch] : ch;
    }

    return res;
  }

  function initUI() {
    // 1. Inject Floating Action Button
    if (!document.getElementById('alexVoiceFab')) {
      const fab = document.createElement('button');
      fab.id = 'alexVoiceFab';
      fab.className = 'alex-voice-fab';
      fab.setAttribute('title', 'Alex AI — وائس کھاتہ اور بل اسسٹنٹ');
      fab.innerHTML = `
        <div class="fab-mic-icon">
          <i class="fa-solid fa-microphone"></i>
        </div>
        <span>Alex AI کھاتہ</span>
      `;
      document.body.appendChild(fab);
      fab.addEventListener('click', openVoiceModal);
    }

    // 2. Inject Modal Overlay
    if (!document.getElementById('alexVoiceModal')) {
      const modal = document.createElement('div');
      modal.id = 'alexVoiceModal';
      modal.className = 'alex-voice-overlay';
      modal.innerHTML = `
        <div class="alex-voice-card">
          <!-- Header -->
          <div class="alex-card-header">
            <h5>
              <span class="alex-badge-ai">ALEX AI</span>
              <span>وائس کھاتہ و بل اسسٹنٹ</span>
            </h5>
            <div style="display:flex;align-items:center;gap:8px;">
              <a href="/dashboard/asan-dukan" style="color:#a7f3d0;text-decoration:none;font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:3px;" title="آسان دکان پورٹل کھولیں">
                <i class="fa-solid fa-store"></i> آسان دکان
              </a>
              <a href="/dashboard/udhar-register" style="color:#e9d5ff;text-decoration:none;font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:3px;" title="روزانہ ادھار رجسٹر دیکھیں">
                <i class="fa-solid fa-book-bookmark"></i> ادھار رجسٹر
              </a>
              <button type="button" class="alex-btn-close" id="alexCloseBtn" aria-label="Close">✕</button>
            </div>
          </div>

          <!-- Alert box for errors or guidance -->
          <div class="alex-status-alert" id="alexAlertBox"></div>

          <!-- Listening State Body -->
          <div id="alexListeningState" class="alex-listening-box">
            <div class="alex-wave-orb active" id="alexOrb" title="کلک کر کے ریکارڈنگ شروع یا بند کریں">
              <i class="fa-solid fa-microphone"></i>
            </div>

            <!-- Live Audio Visualizer Equalizer -->
            <div class="alex-equalizer" id="alexEqualizer">
              <div class="alex-bar" style="height: 6px;"></div>
              <div class="alex-bar" style="height: 12px;"></div>
              <div class="alex-bar" style="height: 20px;"></div>
              <div class="alex-bar" style="height: 14px;"></div>
              <div class="alex-bar" style="height: 8px;"></div>
            </div>

            <div class="alex-listening-label" id="alexStatusLabel">سن رہا ہوں... بولیں (Listening)</div>
            <div class="alex-listening-hint" id="alexHint">
              مائیک میں واضح آواز میں بولیں یا نیچے ٹیکسٹ باکس میں خود لکھیں۔
            </div>
          </div>

          <!-- Interactive & Editable Transcript Input -->
          <div class="alex-transcript-container" id="alexTranscriptContainer">
            <textarea
              class="alex-transcript-input"
              id="alexTranscriptInput"
              placeholder="آپ کی آواز یہاں لکھی جائے گی... آپ خود بھی ٹائپ کر سکتے ہیں"
              rows="3"
            ></textarea>
          </div>

          <!-- Quick 1-Click Test Chips -->
          <div class="alex-sample-chips" id="alexSampleChips">
            <span style="font-size:11px;color:#71717a;width:100%;margin-bottom:2px;">آزمائشی جملے (کلک کریں):</span>
            <button type="button" class="alex-chip" data-sample="علی اور حمزہ نے آج 5 کلو چینی، 5 کلو گھی لیا">
              ⚡ علی اور حمزہ: 5 کلو چینی، 5 کلو گھی
            </button>
            <button type="button" class="alex-chip" data-sample="ارسلان نے 2 پیکٹ سرف اور 3 صابن لیے">
              ⚡ ارسلان: 2 پیکٹ سرف، 3 صابن
            </button>
            <button type="button" class="alex-chip" data-sample="مجھے حمزہ کا کھاتہ دکھاؤ">
              📊 حمزہ کا کھاتہ دکھاؤ
            </button>
            <button type="button" class="alex-chip" data-sample="آج کا ادھار دکھاؤ">
              📖 آج کا ادھار دکھاؤ
            </button>
            <button type="button" class="alex-chip" data-sample="آج 20 بوری چینی اور 10 پیٹی گھی کا اسٹاک آیا ہے">
              📦 مال کی آمد: 20 بوری چینی، 10 پیٹی گھی
            </button>
          </div>

          <!-- Control Buttons Area -->
          <div class="alex-control-btns" id="alexControlBtns">
            <button type="button" class="alex-btn-mic-toggle recording" id="alexMicToggleBtn">
              <i class="fa-solid fa-microphone-slash"></i> مائیک بند کریں
            </button>
            <button type="button" class="alex-btn-submit" id="alexSubmitBtn">
              <i class="fa-solid fa-bolt"></i> بل / کھاتہ تیار کریں
            </button>
          </div>

          <!-- Dynamic Result Box (Hidden by default) -->
          <div id="alexResultBox" class="alex-result-box" style="display:none;"></div>
        </div>
      `;
      document.body.appendChild(modal);

      // Event listeners
      document.getElementById('alexCloseBtn').addEventListener('click', closeVoiceModal);
      document.getElementById('alexOrb').addEventListener('click', toggleRecording);
      document.getElementById('alexMicToggleBtn').addEventListener('click', toggleRecording);

      // Auto-transliterate any pasted or typed text to Urdu
      const transcriptInput = document.getElementById('alexTranscriptInput');
      if (transcriptInput) {
        transcriptInput.addEventListener('input', () => {
          const raw = transcriptInput.value;
          const conv = devanagariToUrdu(raw);
          if (conv !== raw) {
            transcriptInput.value = conv;
          }
        });
      }

      // Submit button
      document.getElementById('alexSubmitBtn').addEventListener('click', () => {
        const text = document.getElementById('alexTranscriptInput').value.trim();
        if (!text) {
          showAlert('براہ کرم کچھ بولیں یا ٹیکسٹ باکس میں لکھیں۔', 'warning');
          return;
        }
        stopRecording();
        processSpeech(text);
      });

      // Quick test sample chips
      document.querySelectorAll('.alex-chip').forEach((btn) => {
        btn.addEventListener('click', function () {
          const sample = this.getAttribute('data-sample');
          const input = document.getElementById('alexTranscriptInput');
          if (input) input.value = sample;
          stopRecording();
          processSpeech(sample);
        });
      });

      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeVoiceModal();
      });
    }
  }

  function openVoiceModal(prefillText) {
    const modal = document.getElementById('alexVoiceModal');
    if (!modal) return;
    modal.classList.add('open');
    resetListeningState();
    if (prefillText && typeof prefillText === 'string') {
      const input = document.getElementById('alexTranscriptInput');
      if (input) input.value = prefillText;
      stopRecording();
      processSpeech(prefillText);
    } else {
      startRecording();
    }
  }

  function closeVoiceModal() {
    stopRecording();
    const modal = document.getElementById('alexVoiceModal');
    if (modal) modal.classList.remove('open');
  }

  function resetListeningState() {
    const listeningState = document.getElementById('alexListeningState');
    const resultBox = document.getElementById('alexResultBox');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const input = document.getElementById('alexTranscriptInput');
    const alertBox = document.getElementById('alexAlertBox');

    if (listeningState) listeningState.style.display = 'flex';
    if (transcriptContainer) transcriptContainer.style.display = 'block';
    if (controlBtns) controlBtns.style.display = 'flex';
    if (sampleChips) sampleChips.style.display = 'flex';
    if (resultBox) {
      resultBox.style.display = 'none';
      resultBox.innerHTML = '';
    }
    if (input) input.value = '';
    if (alertBox) {
      alertBox.className = 'alex-status-alert';
      alertBox.style.display = 'none';
      alertBox.textContent = '';
    }
    currentParsedData = null;
  }

  function showAlert(msg, type = 'error') {
    const box = document.getElementById('alexAlertBox');
    if (box) {
      box.className = `alex-status-alert ${type}`;
      box.textContent = msg;
      box.style.display = 'block';
    }
  }

  function toggleRecording() {
    if (isRecording) {
      stopRecording();
    } else {
      startRecording();
    }
  }

  function startRecording() {
    isRecording = true;
    const orb = document.getElementById('alexOrb');
    const toggleBtn = document.getElementById('alexMicToggleBtn');
    const statusLabel = document.getElementById('alexStatusLabel');
    const alertBox = document.getElementById('alexAlertBox');

    if (orb) orb.classList.add('active');
    if (toggleBtn) {
      toggleBtn.className = 'alex-btn-mic-toggle recording';
      toggleBtn.innerHTML = '<i class="fa-solid fa-microphone-slash"></i> مائیک بند کریں';
    }
    if (statusLabel) statusLabel.textContent = 'سن رہا ہوں... بولیں (Listening)';
    if (alertBox) alertBox.style.display = 'none';

    // 1. Audio Level Meter (Visualizer)
    startAudioVisualizer();

    // 2. Web Speech Recognition
    startSpeechRecognition();
  }

  function startAudioVisualizer() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;

    navigator.mediaDevices.getUserMedia({ audio: true })
      .then((stream) => {
        micStream = stream;
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        audioContext = new AudioCtx();
        analyser = audioContext.createAnalyser();
        analyser.fftSize = 64;

        const source = audioContext.createMediaStreamSource(stream);
        source.connect(analyser);

        const dataArray = new Uint8Array(analyser.frequencyBinCount);
        const bars = document.querySelectorAll('#alexEqualizer .alex-bar');

        function updateVisualizer() {
          if (!isRecording) return;
          analyser.getByteFrequencyData(dataArray);

          // Calculate average volume
          let sum = 0;
          for (let i = 0; i < dataArray.length; i++) sum += dataArray[i];
          const avg = sum / dataArray.length;

          // Animate bars based on real mic volume
          bars.forEach((bar, idx) => {
            const h = Math.max(4, Math.min(28, (dataArray[idx * 2] || avg) / 4));
            bar.style.height = `${h}px`;
            bar.style.background = avg > 15 ? '#10b981' : '#AC22CB'; // green when speaking
          });

          animFrameId = requestAnimationFrame(updateVisualizer);
        }

        updateVisualizer();
      })
      .catch((err) => {
        console.warn('Microphone permission blocked or unavailable:', err);
        showAlert('⚠️ مائیک کی اجازت بلاک ہے۔ اوپر براؤزر کے ایڈریس بار میں لاک یا مائیک آئیکون پر کلک کر کے Allow کریں۔', 'error');
        const statusLabel = document.getElementById('alexStatusLabel');
        if (statusLabel) statusLabel.textContent = 'مائیک کی اجازت درکار ہے';
      });
  }

  function startSpeechRecognition() {
    if (!SpeechRecognition) {
      showAlert('⚠️ آپ کا براؤزر لائیو اسپیچ ریکگنیشن کو سپورٹ نہیں کرتا۔ آپ نیچے متبادل ٹیکسٹ لکھ سکتے ہیں۔', 'warning');
      return;
    }

    try {
      if (recognition) {
        recognition.abort();
      }
      recognition = new SpeechRecognition();
      recognition.lang = recognitionLanguage;
      recognition.continuous = true;
      recognition.interimResults = true;

      let transcriptAccum = '';

      recognition.onresult = (event) => {
        let interim = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
          if (event.results[i].isFinal) {
            transcriptAccum += event.results[i][0].transcript + ' ';
          } else {
            interim += event.results[i][0].transcript;
          }
        }

        const combined = (transcriptAccum + ' ' + interim).trim();
        const urduText = devanagariToUrdu(combined);
        const input = document.getElementById('alexTranscriptInput');
        if (input && urduText) {
          input.value = urduText;
        }
      };

      recognition.onerror = (e) => {
        console.warn('SpeechRecognition error:', e.error);
        if (e.error === 'not-allowed') {
          showAlert('مائیک کی اجازت نہیں ملی۔ براؤزر میں مائیک Allow کریں۔', 'error');
        } else if (e.error === 'network') {
          console.log('Network recognition glitch; keeping Urdu language ur-PK / ur-IN');
          // Never switch to Hindi script. Both ur-PK and ur-IN request Urdu script from Google Speech.
          recognitionLanguage = (recognitionLanguage === 'ur-PK') ? 'ur-IN' : 'ur-PK';
          try { recognition.start(); } catch (err) {}
        }
      };

      recognition.onend = () => {
        if (isRecording) {
          try { recognition.start(); } catch (e) {}
        }
      };

      recognition.start();
    } catch (e) {
      console.warn('Recognition start failed:', e);
    }
  }

  function stopRecording() {
    isRecording = false;
    const orb = document.getElementById('alexOrb');
    const toggleBtn = document.getElementById('alexMicToggleBtn');
    const statusLabel = document.getElementById('alexStatusLabel');

    if (orb) orb.classList.remove('active');
    if (toggleBtn) {
      toggleBtn.className = 'alex-btn-mic-toggle';
      toggleBtn.innerHTML = '<i class="fa-solid fa-microphone"></i> مائیک آن کریں';
    }
    if (statusLabel) statusLabel.textContent = 'مائیک بند ہے — اب بل بنائیں پر کلک کریں';

    if (recognition) {
      try { recognition.stop(); } catch (e) {}
    }
    if (micStream) {
      micStream.getTracks().forEach((track) => track.stop());
      micStream = null;
    }
    if (audioContext) {
      try { audioContext.close(); } catch (e) {}
      audioContext = null;
    }
    if (animFrameId) {
      cancelAnimationFrame(animFrameId);
    }

    // Reset equalizer bars
    document.querySelectorAll('#alexEqualizer .alex-bar').forEach((b) => {
      b.style.height = '6px';
      b.style.background = '#AC22CB';
    });
  }

  function processSpeech(transcriptText) {
    const statusLabel = document.getElementById('alexStatusLabel');
    if (statusLabel) statusLabel.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Alex AI حساب لگا رہا ہے...';

    const submitBtn = document.getElementById('alexSubmitBtn');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> پروسیسنگ...';
    }

    fetch('/dashboard/voice-assistant/process', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        transcript: transcriptText,
        _token: csrfToken
      })
    })
      .then((res) => res.json())
      .then((data) => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fa-solid fa-bolt"></i> بل / کھاتہ تیار کریں';
        }

        if (!data.success && !data.intent) {
          showAlert(data.message || 'سمجھ نہیں آیا، براہ کرم واضح الفاظ میں دوبارہ بولیں یا لکھیں۔', 'warning');
          const statusLabel = document.getElementById('alexStatusLabel');
          if (statusLabel) statusLabel.textContent = 'دوبارہ بولیں یا بل تیار کریں پر کلک کریں';
          return;
        }

        // Voice TTS Reply
        if (data.voice_reply) {
          speakUrdu(data.voice_reply);
        }

        if (data.intent === 'CONVERSATION') {
          renderConversationResult(data);
        } else if (data.intent === 'PAYMENT_RECEIVED') {
          renderPaymentResult(data);
        } else if (data.intent === 'SALE_ENTRY') {
          renderSaleConfirmation(data.data);
        } else if (data.intent === 'STOCK_INWARD') {
          renderStockInwardConfirmation(data);
        } else if (data.intent === 'KHATA_QUERY') {
          renderKhataResult(data);
        } else if (data.intent === 'STOCK_QUERY') {
          renderStockResult(data);
        } else if (data.intent === 'TODAY_UDHAR_QUERY') {
          renderTodayUdharResult(data);
        }
      })
      .catch((err) => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fa-solid fa-bolt"></i> بل / کھاتہ تیار کریں';
        }
        const statusLabel = document.getElementById('alexStatusLabel');
        if (statusLabel) statusLabel.textContent = 'رابطہ منقطع ہو گیا — دوبارہ کوشش کریں';
        console.error('Voice processing fetch error:', err);
        showAlert('سرور سے رابطہ نہ ہو سکا۔ انٹرنیٹ یا سرور چیک کریں۔', 'error');
      });
  }

  function renderConversationResult(data) {
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    resultBox.innerHTML = `
      <div class="alex-khata-card" style="border-color:#5813BC;background:#faf5ff;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
          <span class="alex-badge-ai">ALEX AI</span>
          <strong style="color:#5813BC;font-size:15px;">الیکس اے آئی کا جواب</strong>
        </div>
        <p style="font-size:15px;color:#18181b;line-height:1.8;margin:8px 0 16px;font-family:'Noto Nastaliq Urdu',Tahoma,sans-serif;">
          ${escapeHtml(data.message || data.voice_reply)}
        </p>
      </div>

      <div style="display:flex;gap:10px;margin-top:16px;">
        <button type="button" class="alex-btn-primary" style="flex:1;" onclick="window.AlexVoiceAssistant.resetListening()">
          <i class="fa-solid fa-microphone me-1"></i> اب گاہک کا بل یا کھاتہ بولیں
        </button>
      </div>
    `;

    resultBox.style.display = 'block';
  }

  function renderPaymentResult(data) {
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    resultBox.innerHTML = `
      <div class="alex-khata-card" style="border-color:#10b981;background:#f0fdf4;">
        <span style="font-size:11px;color:#15803d;font-weight:700;">ادائیگی وصولی (Payment Received)</span>
        <h4 style="margin:2px 0 10px;font-weight:800;color:#18181b;">${escapeHtml(data.customer_name)}</h4>
        <span style="font-size:12px;color:#52525b;display:block;">وصول شدہ رقم:</span>
        <div style="font-size:26px;font-weight:900;color:#10b981;margin:4px 0 10px;">
          Rs ${Number(data.amount).toLocaleString()}
        </div>
        <p style="font-size:13px;color:#374151;margin:0;">
          ${escapeHtml(data.voice_reply)}
        </p>
      </div>

      <button type="button" class="alex-btn-secondary" style="width:100%;margin-top:14px;" onclick="window.AlexVoiceAssistant.resetListening()">
        <i class="fa-solid fa-rotate-left me-1"></i> اگلا بل یا کھاتہ بولیں
      </button>
    `;

    resultBox.style.display = 'block';
  }

  function renderSaleConfirmation(data) {
    currentParsedData = JSON.parse(JSON.stringify(data)); // Deep clone
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    function buildRowsHtml() {
      let html = '';
      (currentParsedData.items || []).forEach((it, idx) => {
        const itemAmount = (it.quantity || 1) * (it.unit_price || 0);
        html += `
          <tr data-idx="${idx}">
            <td style="font-weight:700;">
              <span class="alex-item-title">${escapeHtml(it.name)}</span>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:3px;">
                <input type="number" step="any" min="0.1" class="alex-cell-input alex-qty-input" value="${it.quantity}">
                <span style="font-size:11px;color:#71717a;">${escapeHtml(it.unit || 'kg')}</span>
              </div>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:3px;">
                <span style="font-size:11px;color:#71717a;">Rs</span>
                <input type="number" step="any" min="0" class="alex-cell-input alex-rate-input" value="${Number(it.unit_price).toFixed(0)}">
              </div>
            </td>
            <td style="font-weight:800;color:#5813BC;white-space:nowrap;" class="alex-row-amount">
              Rs ${Number(itemAmount).toLocaleString()}
            </td>
            <td style="text-align:center;">
              <button type="button" class="alex-btn-del-row" data-idx="${idx}" title="یہ آئٹم بل سے ہٹائیں">✕</button>
            </td>
          </tr>
        `;
      });
      return html;
    }

    resultBox.innerHTML = `
      <div class="alex-party-chip">
        <div>
          <span style="font-size:11px;color:#71717a;display:block;">گاہک کا نام (کھاتہ)</span>
          <strong style="font-size:16px;color:#18181b;" id="alexCustomerDisplay">${escapeHtml(currentParsedData.customer_name)}</strong>
        </div>
        <span class="badge" style="background:#5813BC;color:#fff;padding:6px 12px;border-radius:6px;font-size:11px;">
          کھاتہ ادھار (Credit)
        </span>
      </div>

      <div style="margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:12px;font-weight:700;color:#52525b;">سامان کی لسٹ (ریٹ اور مقدار تبدیل کر سکتے ہیں):</span>
      </div>

      <table class="alex-items-table" id="alexItemsTable">
        <thead>
          <tr>
            <th>آئٹم</th>
            <th style="width:85px;">مقدار</th>
            <th style="width:95px;">ریٹ (فی اکائی)</th>
            <th style="width:85px;">ٹوٹل</th>
            <th style="width:30px;"></th>
          </tr>
        </thead>
        <tbody id="alexItemsTbody">
          ${buildRowsHtml()}
        </tbody>
      </table>

      <div class="alex-total-strip">
        <span>کل بل رقم (Grand Total):</span>
        <span style="font-size:20px;font-weight:900;" id="alexGrandTotalDisplay">Rs ${Number(currentParsedData.total_amount).toLocaleString()}</span>
      </div>

      <div style="display:flex;gap:10px;margin-top:16px;">
        <button type="button" class="alex-btn-secondary" style="flex:1;" onclick="window.AlexVoiceAssistant.resetListening()">
          ✕ کینسل / نیا بولیں
        </button>
        <button type="button" class="alex-btn-primary" style="flex:2;" id="alexConfirmSaleBtn">
          <i class="fa-solid fa-check"></i> بل محفوظ کریں اور کھاتہ اپ ڈیٹ کریں
        </button>
      </div>
    `;

    resultBox.style.display = 'block';

    function recalculateTotal() {
      let grand = 0;
      const rows = document.querySelectorAll('#alexItemsTbody tr');
      const updatedItems = [];

      rows.forEach((row) => {
        const idx = parseInt(row.getAttribute('data-idx'));
        const original = currentParsedData.items[idx];
        const qty = parseFloat(row.querySelector('.alex-qty-input')?.value) || 0;
        const rate = parseFloat(row.querySelector('.alex-rate-input')?.value) || 0;
        const amount = qty * rate;
        grand += amount;

        const amtCell = row.querySelector('.alex-row-amount');
        if (amtCell) amtCell.textContent = 'Rs ' + Math.round(amount).toLocaleString();

        if (original) {
          updatedItems.push({
            ...original,
            quantity: qty,
            unit_price: rate,
            amount: amount,
          });
        }
      });

      currentParsedData.items = updatedItems;
      currentParsedData.total_amount = grand;

      const grandEl = document.getElementById('alexGrandTotalDisplay');
      if (grandEl) grandEl.textContent = 'Rs ' + Math.round(grand).toLocaleString();
    }

    // Attach input listeners for live rate/qty edits
    resultBox.addEventListener('input', (e) => {
      if (e.target.classList.contains('alex-qty-input') || e.target.classList.contains('alex-rate-input')) {
        recalculateTotal();
      }
    });

    // Delete row listener
    resultBox.addEventListener('click', (e) => {
      const delBtn = e.target.closest('.alex-btn-del-row');
      if (delBtn) {
        const row = delBtn.closest('tr');
        if (row) {
          row.remove();
          recalculateTotal();
        }
      }
    });

    const confirmBtn = document.getElementById('alexConfirmSaleBtn');
    if (confirmBtn) {
      confirmBtn.onclick = function () {
        recalculateTotal();
        executeConfirmSale(currentParsedData);
      };
    }
  }

  function executeConfirmSale(data) {
    const confirmBtn = document.getElementById('alexConfirmSaleBtn');
    if (confirmBtn) {
      confirmBtn.disabled = true;
      confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> بل محفوظ ہو رہا ہے...';
    }

    fetch('/dashboard/voice-assistant/confirm-sale', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        party_name: data.customer_name,
        payment_type: data.payment_type || 'credit',
        items: data.items,
        _token: csrfToken
      })
    })
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) {
          showAlert(res.message || 'بل محفوظ نہیں ہو سکا', 'error');
          if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> بل محفوظ کریں';
          }
          return;
        }

        speakUrdu(res.voice_reply || 'بل محفوظ ہو گیا');

        const resultBox = document.getElementById('alexResultBox');
        resultBox.innerHTML = `
          <div style="text-align:center;padding:24px 12px;">
            <div style="width:60px;height:60px;background:#e8f5e9;color:#2e7d32;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;">
              <i class="fa-solid fa-check"></i>
            </div>
            <h4 style="font-weight:800;color:#18181b;margin-bottom:6px;">${res.message}</h4>
            <p style="font-size:13.5px;color:#52525b;margin-bottom:18px;">
              بل نمبر: <strong>#${res.bill_number}</strong> | کل بل: <strong>Rs ${Number(res.grand_total).toLocaleString()}</strong><br>
              گاہک کا کل نیا کھاتہ بیلنس: <strong style="color:#e53935;">Rs ${Number(res.customer_total_balance).toLocaleString()}</strong>
            </p>

            ${res.whatsapp_url ? `
            <a href="${res.whatsapp_url}" target="_blank" class="alex-btn-whatsapp" style="margin-bottom:12px;">
              <i class="fa-brands fa-whatsapp" style="font-size:20px;"></i> گاہک کو ڈیجیٹل واٹس ایپ رسید بھیجیں
            </a>
            ` : ''}

            <button type="button" class="alex-btn-secondary" style="width:100%;margin-top:10px;" onclick="window.AlexVoiceAssistant.openVoiceModal()">
              <i class="fa-solid fa-plus me-1"></i> ایک اور نیا بل بنائیں
            </button>
          </div>
        `;
      })
      .catch((err) => {
        console.error('Confirm sale error:', err);
        showAlert('بل محفوظ کرتے وقت ایرر آیا۔ براہ کرم دوبارہ کوشش کریں۔', 'error');
      });
  }

  function renderTodayUdharResult(data) {
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    let salesRows = '';
    if (data.sales && data.sales.length > 0) {
      salesRows = data.sales.map((s) => `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-bottom:1px solid #f4f4f5;font-size:13px;">
          <div>
            <strong style="color:#18181b;display:block;">${escapeHtml(s.party_name)}</strong>
            <span style="font-size:11px;color:#71717a;">${escapeHtml(s.items_summary || '')} ${s.time ? '• ' + escapeHtml(s.time) : ''}</span>
          </div>
          <div style="text-align:left;">
            <strong style="color:#dc2626;font-size:14px;display:block;">Rs ${Number(s.balance || 0).toLocaleString()}</strong>
            <span style="font-size:10px;color:#71717a;">کل بقایا: Rs ${Number(s.total_balance || 0).toLocaleString()}</span>
          </div>
        </div>
      `).join('');
    } else {
      salesRows = `
        <div style="text-align:center;padding:20px;color:#71717a;font-size:13px;">
          آج ابھی تک کوئی نیا ادھار درج نہیں ہوا۔
        </div>
      `;
    }

    resultBox.innerHTML = `
      <div class="alex-khata-card" style="border-color:#5813BC;padding:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
          <div>
            <span style="font-size:11px;color:#71717a;">تاریخ: ${escapeHtml(data.date || 'آج')}</span>
            <h4 style="margin:2px 0;font-weight:800;color:#18181b;">آج کا روزانہ ادھار خلاصہ</h4>
          </div>
          <a href="/dashboard/udhar-register" style="padding:6px 12px;background:#5813BC;color:#fff;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;">
            مکمل رجسٹر کھولیں ↗
          </a>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
          <div style="background:#fef2f2;border:1px solid #fecaca;padding:10px;border-radius:8px;text-align:center;">
            <span style="font-size:11px;color:#991b1b;display:block;font-weight:600;">آج کا کل ادھار</span>
            <span style="font-size:18px;font-weight:900;color:#dc2626;">Rs ${Number(data.total_udhar || 0).toLocaleString()}</span>
          </div>
          <div style="background:#f3e8ff;border:1px solid #e9d5ff;padding:10px;border-radius:8px;text-align:center;">
            <span style="font-size:11px;color:#6b21a8;display:block;font-weight:600;">ادھار گاہک</span>
            <span style="font-size:18px;font-weight:900;color:#5813BC;">${Number(data.customers_count || 0)} افراد</span>
          </div>
        </div>

        <div style="background:#fff;border:1px solid #e4e4e7;border-radius:8px;max-height:180px;overflow-y:auto;margin-bottom:12px;">
          ${salesRows}
        </div>

        <div style="display:flex;gap:8px;">
          <a href="/dashboard/udhar-register" class="alex-btn-confirm" style="flex:1;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;">
            <i class="fa-solid fa-book-bookmark"></i> روزانہ ادھار رجسٹر پر جائیں
          </a>
          <button type="button" class="alex-btn-secondary" style="flex:1;" onclick="window.AlexVoiceAssistant.resetListening()">
            <i class="fa-solid fa-rotate-left me-1"></i> اگلا سوال بولیں
          </button>
        </div>
      </div>
    `;

    resultBox.style.display = 'block';
  }

  function renderStockInwardConfirmation(data) {
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    let inwardItems = JSON.parse(JSON.stringify(data.items || []));

    function buildRows() {
      if (inwardItems.length === 0) {
        return `<tr><td colspan="6" style="text-align:center;padding:16px;color:#71717a;">کوئی آئٹم باقی نہیں رہا</td></tr>`;
      }
      return inwardItems.map((it, idx) => `
        <tr data-idx="${idx}" style="border-bottom: 1px solid #e4e4e7;">
          <td style="padding: 8px 6px;">
            <div style="font-weight: 800; color: #18181b; font-size: 14.5px;">${escapeHtml(it.name_ur)}</div>
            <div style="font-size: 11px; color: #0284c7; font-weight: 600; font-family: sans-serif;">${escapeHtml(it.name_en)}</div>
          </td>
          <td style="padding: 8px 6px; text-align: center;">
            <span class="badge bg-light text-dark border" style="font-size: 11.5px; padding: 4px 6px;">
              ${Number(it.current_stock || 0)} ${escapeHtml(it.unit || '')}
            </span>
          </td>
          <td style="padding: 8px 6px; text-align: center;">
            <input
              type="number"
              class="alex-cell-input alex-inward-qty-input"
              value="${it.added_qty}"
              min="0.1"
              step="any"
              data-idx="${idx}"
              style="width: 70px; text-align: center; font-weight: 800; color: #0284c7; border: 2px solid #38bdf8; border-radius: 6px; padding: 4px 2px;"
            >
          </td>
          <td style="padding: 8px 6px; text-align: center;">
            <strong class="alex-inward-total-text" data-idx="${idx}" style="color: #16a34a; font-size: 13.5px;">
              ${Number(it.new_stock || 0)} ${escapeHtml(it.unit || '')}
            </strong>
          </td>
          <td style="padding: 8px 6px; text-align: center;">
            <input
              type="number"
              class="alex-cell-input alex-inward-rate-input"
              value="${it.purchase_rate || 0}"
              min="0"
              step="any"
              data-idx="${idx}"
              style="width: 70px; text-align: center; font-weight: 700; color: #18181b; border: 1px solid #d4d4d8; border-radius: 6px; padding: 4px 2px;"
            >
          </td>
          <td style="padding: 8px 2px; text-align: center;">
            <button type="button" class="alex-btn-del-inward-row" data-idx="${idx}" title="یہ آئٹم ہٹائیں" style="border:none;background:#fee2e2;color:#dc2626;border-radius:6px;width:26px;height:26px;cursor:pointer;font-weight:bold;">
              ✕
            </button>
          </td>
        </tr>
      `).join('');
    }

    resultBox.innerHTML = `
      <div class="alex-sale-card" style="border-top-color: #0284c7;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
          <div>
            <span style="font-size:11px; color:#0284c7; font-weight:700;">📦 مال کی آمد (اسٹاک انٹری)</span>
            <h4 style="margin:2px 0 0; font-weight:800; color:#18181b; font-size:17px;">
              اسٹاک انوینٹری اندراج
            </h4>
          </div>
          <span class="badge" style="background:#0284c7; color:#fff; font-size:11px; padding:5px 8px;">
            ${inwardItems.length} آئٹمز
          </span>
        </div>

        <p style="font-size: 11.5px; color: #71717a; margin-bottom: 8px;">
          ایک طرف اردو اور ایک طرف انگلش میں تصدیق کریں۔ باکسز میں نئی مقدار یا خرید ریٹ تبدیل کر سکتے ہیں۔
        </p>

        <div style="max-height: 230px; overflow-y: auto; border: 1px solid #e4e4e7; border-radius: 8px; margin-bottom: 12px;">
          <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
            <thead style="background: #f8fafc; color: #64748b; font-size: 11px; position: sticky; top: 0;">
              <tr>
                <th style="padding: 6px; text-align: right;">آئٹم (اردو / English)</th>
                <th style="padding: 6px; text-align: center;">موجودہ</th>
                <th style="padding: 6px; text-align: center;">نیا مال (+)</th>
                <th style="padding: 6px; text-align: center;">نیا کل (=)</th>
                <th style="padding: 6px; text-align: center;">خرید ریٹ</th>
                <th style="padding: 6px; width: 30px;"></th>
              </tr>
            </thead>
            <tbody id="alexInwardTableBody">
              ${buildRows()}
            </tbody>
          </table>
        </div>

        <button type="button" class="alex-btn-confirm" id="alexConfirmStockInwardBtn" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 100%; margin-bottom: 6px;">
          <i class="fa-solid fa-boxes-stacked me-1"></i> ✓ مال اسٹاک انوینٹری میں جمع کریں
        </button>

        <button type="button" class="alex-btn-secondary" style="width: 100%;" onclick="window.AlexVoiceAssistant.resetListening()">
          <i class="fa-solid fa-xmark me-1"></i> کینسل / اگلا سوال بولیں
        </button>
      </div>
    `;

    resultBox.style.display = 'block';

    function attachListeners() {
      document.querySelectorAll('.alex-inward-qty-input').forEach(input => {
        input.addEventListener('input', function() {
          const idx = parseInt(this.getAttribute('data-idx'), 10);
          const newQty = parseFloat(this.value) || 0;
          inwardItems[idx].added_qty = newQty;
          inwardItems[idx].new_stock = (inwardItems[idx].current_stock || 0) + newQty;

          const totalEl = document.querySelector(`.alex-inward-total-text[data-idx="${idx}"]`);
          if (totalEl) {
            totalEl.textContent = `${inwardItems[idx].new_stock} ${inwardItems[idx].unit || ''}`;
          }
        });
      });

      document.querySelectorAll('.alex-inward-rate-input').forEach(input => {
        input.addEventListener('input', function() {
          const idx = parseInt(this.getAttribute('data-idx'), 10);
          inwardItems[idx].purchase_rate = parseFloat(this.value) || 0;
        });
      });

      document.querySelectorAll('.alex-btn-del-inward-row').forEach(btn => {
        btn.addEventListener('click', function() {
          const idx = parseInt(this.getAttribute('data-idx'), 10);
          inwardItems.splice(idx, 1);
          const tbody = document.getElementById('alexInwardTableBody');
          if (tbody) tbody.innerHTML = buildRows();
          attachListeners();
        });
      });
    }

    attachListeners();

    const confirmBtn = document.getElementById('alexConfirmStockInwardBtn');
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function() {
        if (inwardItems.length === 0) {
          showAlert('اسٹاک میں جمع کرنے کے لیے کم از کم ایک آئٹم ضروری ہے۔', 'warning');
          return;
        }
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> اسٹاک محفوظ ہو رہا ہے...';

        const payload = {
          items: inwardItems.map(it => ({
            item_id: it.item_id || null,
            name: it.name_ur,
            added_qty: it.added_qty,
            unit: it.unit,
            purchase_rate: it.purchase_rate,
          }))
        };

        fetch('/dashboard/voice-assistant/confirm-stock-inward', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
          if (!res.success) {
            showAlert(res.message || 'اسٹاک محفوظ نہیں ہو سکا۔', 'error');
            const btn = document.getElementById('alexConfirmStockInwardBtn');
            if (btn) {
              btn.disabled = false;
              btn.innerHTML = '<i class="fa-solid fa-boxes-stacked me-1"></i> ✓ مال اسٹاک انوینٹری میں جمع کریں';
            }
            return;
          }

          if (res.voice_reply) {
            speakUrdu(res.voice_reply);
          }

          resultBox.innerHTML = `
            <div class="alex-success-card" style="border-top-color:#0284c7;">
              <div class="alex-success-icon" style="background:#0284c7;font-size:32px;">📦</div>
              <h4 style="margin:4px 0 2px;font-weight:900;color:#18181b;">مال اسٹاک میں جمع ہو گیا!</h4>
              <p style="font-size:13px;color:#52525b;margin-bottom:12px;">
                ${escapeHtml(res.message)}
              </p>

              <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px;margin-bottom:12px;font-size:12.5px;">
                <strong style="color:#16a34a;display:block;margin-bottom:4px;">شامل شدہ اشیاء:</strong>
                ${inwardItems.map(it => `<div>• <strong>${escapeHtml(it.name_ur)}</strong>: +${it.added_qty} ${escapeHtml(it.unit || '')} (نیا کل: ${it.new_stock} ${escapeHtml(it.unit || '')})</div>`).join('')}
              </div>

              <div style="display:flex;gap:8px;">
                <a href="/dashboard/items" class="alex-btn-secondary" style="flex:1;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;">
                  <i class="fa-solid fa-box me-1"></i> اسٹاک انوینٹری دیکھیں
                </a>
                <button type="button" class="alex-btn-confirm" style="flex:1;" onclick="window.AlexVoiceAssistant.resetListening()">
                  <i class="fa-solid fa-rotate-left me-1"></i> اگلا بولیں
                </button>
              </div>
            </div>
          `;
        })
        .catch(err => {
          console.error('Confirm stock inward error:', err);
          showAlert('سرور سے رابطہ نہ ہو سکا۔ دوبارہ کوشش کریں۔', 'error');
          const btn = document.getElementById('alexConfirmStockInwardBtn');
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-boxes-stacked me-1"></i> ✓ مال اسٹاک انوینٹری میں جمع کریں';
          }
        });
      });
    }
  }

  function renderKhataResult(data) {
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    const p = data.party;
    const s = data.summary;

    resultBox.innerHTML = `
      <div class="alex-khata-card">
        <span style="font-size:11px;color:#71717a;">گاہک کا کھاتہ</span>
        <h4 style="margin:2px 0 10px;font-weight:800;color:#18181b;">${escapeHtml(p.name)}</h4>
        <span style="font-size:12px;color:#52525b;display:block;">موجودہ بقایا کھاتہ ادھار (Outstanding Balance):</span>
        <div class="alex-khata-balance">Rs ${Number(p.current_balance).toLocaleString()}</div>

        <div style="display:flex;justify-content:space-between;border-top:1px dashed #e4e4e7;padding-top:10px;font-size:12px;color:#71717a;">
          <span>کل خریداری: Rs ${Number(s.total_purchased).toLocaleString()}</span>
          <span>کل وصول شدہ: Rs ${Number(s.total_paid).toLocaleString()}</span>
        </div>
      </div>

      <button type="button" class="alex-btn-secondary" style="width:100%;" onclick="window.AlexVoiceAssistant.resetListening()">
        <i class="fa-solid fa-rotate-left me-1"></i> اگلا کھاتہ معلوم کریں
      </button>
    `;

    resultBox.style.display = 'block';
  }

  function renderStockResult(data) {
    const listeningState = document.getElementById('alexListeningState');
    const transcriptContainer = document.getElementById('alexTranscriptContainer');
    const controlBtns = document.getElementById('alexControlBtns');
    const sampleChips = document.getElementById('alexSampleChips');
    const resultBox = document.getElementById('alexResultBox');

    if (listeningState) listeningState.style.display = 'none';
    if (transcriptContainer) transcriptContainer.style.display = 'none';
    if (controlBtns) controlBtns.style.display = 'none';
    if (sampleChips) sampleChips.style.display = 'none';

    const it = data.item;

    resultBox.innerHTML = `
      <div class="alex-khata-card" style="border-color:#10b981;">
        <span style="font-size:11px;color:#71717a;">آئٹم کا نام</span>
        <h4 style="margin:2px 0 10px;font-weight:800;color:#18181b;">${escapeHtml(it.name)}</h4>
        <span style="font-size:12px;color:#52525b;display:block;">موجودہ اسٹاک (Available Stock):</span>
        <div style="font-size:26px;font-weight:900;color:#10b981;margin:4px 0 10px;">
          ${it.stock} ${escapeHtml(it.unit)}
        </div>
        <div style="font-size:13px;color:#71717a;">
          فروخت ریٹ: <strong>Rs ${Number(it.sale_price).toLocaleString()}</strong>
        </div>
      </div>

      <button type="button" class="alex-btn-secondary" style="width:100%;" onclick="window.AlexVoiceAssistant.resetListening()">
        <i class="fa-solid fa-rotate-left me-1"></i> اگلا سوال پوچھیں
      </button>
    `;

    resultBox.style.display = 'block';
  }

  function speakUrdu(text) {
    if (!('speechSynthesis' in window)) return;
    try {
      window.speechSynthesis.cancel();
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'ur-PK';
      utterance.rate = 0.95;
      utterance.pitch = 1.0;

      const voices = window.speechSynthesis.getVoices();
      const urduVoice = voices.find((v) => v.lang.includes('ur') || v.lang.includes('hi'));
      if (urduVoice) utterance.voice = urduVoice;

      window.speechSynthesis.speak(utterance);
    } catch (e) {
      console.warn('Speech synthesis error:', e);
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Expose global methods
  window.AlexVoiceAssistant = {
    openVoiceModal,
    closeVoiceModal,
    resetListening: resetListeningState,
  };

  // Auto-init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUI);
  } else {
    initUI();
  }
})();
