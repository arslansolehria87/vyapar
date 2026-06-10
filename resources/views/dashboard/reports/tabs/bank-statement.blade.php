{{-- BANK STATEMENT TAB --}}
<div id="tab-bank statement" class="report-tab-content d-none">
    <div class="d-flex flex-column" style="min-height:100vh;padding:24px;background:#fff;border:1px solid #e5e7eb;">

        {{-- TOP BAR --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Bank Name</span>
                    <select id="bs-bank-select" style="font-size:13px;border:1px solid #d1d5db;border-radius:4px;padding:5px 10px;color:#374151;outline:none;background:#fff;min-width:160px;">
                        <option value="">-- Select Bank --</option>
                        @foreach(\App\Models\BankAccount::active()->orderBy('display_name')->get() as $bsBank)
                            <option value="{{ $bsBank->id }}">{{ $bsBank->display_with_account ?: ($bsBank->bank_name ?: 'Bank Account') }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                    <input type="checkbox" id="bs-date-toggle" style="width:15px;height:15px;cursor:pointer;">
                    <span style="font-size:14px;color:#6b7280;">Date filter</span>
                </label>
                <div id="bs-date-range" class="d-flex align-items-center gap-2 d-none">
                    <div style="border:1px solid #d1d5db;border-radius:4px;padding:4px 10px;background:#fff;display:flex;align-items:center;gap:6px;">
                        <span style="font-size:11px;color:#9ca3af;">From</span>
                        <input type="date" id="bs-from" style="border:none;outline:none;font-size:13px;color:#374151;" value="{{ date('Y-m-d', strtotime('first day of this month')) }}">
                    </div>
                    <div style="border:1px solid #d1d5db;border-radius:4px;padding:4px 10px;background:#fff;display:flex;align-items:center;gap:6px;">
                        <span style="font-size:11px;color:#9ca3af;">To</span>
                        <input type="date" id="bs-to" style="border:none;outline:none;font-size:13px;color:#374151;" value="{{ date('Y-m-d') }}">
                    </div>
                    <button id="bs-apply" style="font-size:12px;padding:5px 14px;background:#6366f1;color:#fff;border:none;border-radius:4px;cursor:pointer;">Apply</button>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button id="bs-excel-btn" title="Export Excel" style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                    <i class="fa-solid fa-file-excel" style="color:#10b981;font-size:17px;"></i>
                </button>
                <button id="bs-print-btn" title="Print" style="width:38px;height:38px;border-radius:50%;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                    <i class="fa-solid fa-print" style="color:#4b5563;font-size:17px;"></i>
                </button>
            </div>
        </div>

        <h2 style="font-weight:700;color:#1f2937;font-size:22px;margin:8px 0 20px;">Bank Statement</h2>
        <div id="bs-loading" class="d-none text-center py-5"><div class="spinner-border text-primary"><span class="visually-hidden">Loading…</span></div></div>

        <div id="bs-table-wrap" class="table-responsive">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#f3f4f6;">
                    <tr style="border-bottom:2px solid #e5e7eb;">
                        <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;border-right:1px solid #e5e7eb;width:130px;">Date</th>
                        <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:left;border-right:1px solid #e5e7eb;">Description</th>
                        <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #e5e7eb;width:170px;">Withdrawal Amount</th>
                        <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border-right:1px solid #e5e7eb;width:160px;">Deposit Amount</th>
                        <th style="padding:11px 16px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;width:160px;">Balance Amount</th>
                    </tr>
                </thead>
                <tbody id="bs-body">
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:40px;font-size:14px;">Please select a bank account to view the statement.</td></tr>
                </tbody>
                <tfoot id="bs-foot" style="display:none;background:#f9fafb;">
                    <tr style="border-top:2px solid #e5e7eb;">
                        <td colspan="2" style="padding:13px 16px;font-size:14px;font-weight:700;color:#1f2937;border-right:1px solid #e5e7eb;">Balance</td>
                        <td id="bs-total-wd"  style="padding:13px 16px;font-size:14px;font-weight:700;color:#ef4444;text-align:right;border-right:1px solid #e5e7eb;">Rs 0.00</td>
                        <td id="bs-total-dep" style="padding:13px 16px;font-size:14px;font-weight:700;color:#16a34a;text-align:right;border-right:1px solid #e5e7eb;">Rs 0.00</td>
                        <td id="bs-final-bal" style="padding:13px 16px;font-size:14px;font-weight:700;color:#1f2937;text-align:right;">Rs 0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- PRINT MODAL --}}
<div class="modal fade" id="bsPrintModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:10px;overflow:hidden;">
      <div class="modal-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-print me-2 text-secondary"></i>Print Preview — Bank Statement</h5>
        <div class="d-flex gap-2 align-items-center">
          <button id="bs-do-print" class="btn btn-sm btn-primary px-3"><i class="fa-solid fa-print me-1"></i>Print</button>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-0" style="background:#e5e7eb;">
        <div id="bs-print-area" style="background:#fff;margin:24px auto;padding:40px 48px;max-width:860px;box-shadow:0 4px 20px rgba(0,0,0,.10);border-radius:8px;font-family:Inter,sans-serif;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
            <div>
              <h2 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 4px;">Bank Statement</h2>
              <p id="bs-p-bank" style="font-size:13px;color:#6b7280;margin:0;"></p>
              <p id="bs-p-range" style="font-size:12px;color:#9ca3af;margin:2px 0 0;"></p>
            </div>
            <p style="font-size:12px;color:#9ca3af;margin:0;">Printed: <span id="bs-p-date"></span></p>
          </div>
          <hr style="border-color:#e5e7eb;margin-bottom:16px;">
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="background:#f3f4f6;">
                <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#6b7280;border:1px solid #e5e7eb;">Date</th>
                <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#6b7280;border:1px solid #e5e7eb;">Description</th>
                <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border:1px solid #e5e7eb;">Withdrawal</th>
                <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border:1px solid #e5e7eb;">Deposit</th>
                <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#6b7280;text-align:right;border:1px solid #e5e7eb;">Balance</th>
              </tr>
            </thead>
            <tbody id="bs-p-body"></tbody>
            <tfoot>
              <tr style="background:#f9fafb;">
                <td colspan="2" style="padding:9px 12px;font-size:13px;font-weight:700;border:1px solid #e5e7eb;">Balance</td>
                <td id="bs-p-wd"  style="padding:9px 12px;font-size:13px;font-weight:700;color:#ef4444;text-align:right;border:1px solid #e5e7eb;"></td>
                <td id="bs-p-dep" style="padding:9px 12px;font-size:13px;font-weight:700;color:#16a34a;text-align:right;border:1px solid #e5e7eb;"></td>
                <td id="bs-p-bal" style="padding:9px 12px;font-size:13px;font-weight:700;text-align:right;border:1px solid #e5e7eb;"></td>
              </tr>
            </tfoot>
          </table>
          <p style="font-size:11px;color:#d1d5db;text-align:center;margin-top:24px;">Computer-generated — no signature required.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var _bsData=[];
  var csrf=window.App?.csrfToken||document.querySelector('meta[name="csrf-token"]')?.content||'';

  function fmt(v,signed){var n=parseFloat(v||0);return isNaN(n)?'':((signed&&n<0)?'-':'')+'Rs '+Math.abs(n).toLocaleString('en-PK',{minimumFractionDigits:2,maximumFractionDigits:2});}
  function fmtD(s){if(!s)return'';var d=new Date(s);return isNaN(d)?s:d.toLocaleDateString('en-GB');}
  function esc(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}

  document.getElementById('bs-date-toggle').addEventListener('change',function(){
    document.getElementById('bs-date-range').classList.toggle('d-none',!this.checked);
    if(!this.checked)load();
  });
  document.getElementById('bs-apply').addEventListener('click',load);
  document.getElementById('bs-bank-select').addEventListener('change',load);

  function load(){
    var id=document.getElementById('bs-bank-select').value;
    if(!id){renderEmpty('Please select a bank account to view the statement.');return;}
    var p=new URLSearchParams({bank_id:id});
    if(document.getElementById('bs-date-toggle').checked){
      var f=document.getElementById('bs-from').value,t=document.getElementById('bs-to').value;
      if(f)p.append('from',f); if(t)p.append('to',t);
    }
    document.getElementById('bs-loading').classList.remove('d-none');
    document.getElementById('bs-table-wrap').classList.add('d-none');
    fetch('/dashboard/reports/bank-statement?'+p,{headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}})
      .then(function(r){if(!r.ok)throw new Error(r.status);return r.json();})
      .then(function(d){_bsData=d.transactions||d.rows||[];renderTable(_bsData,d);})
      .catch(function(e){console.error(e);renderEmpty('Failed to load. Please try again.');})
      .finally(function(){
        document.getElementById('bs-loading').classList.add('d-none');
        document.getElementById('bs-table-wrap').classList.remove('d-none');
      });
  }
  function renderEmpty(msg){
    document.getElementById('bs-body').innerHTML='<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:40px;font-size:14px;">'+msg+'</td></tr>';
    document.getElementById('bs-foot').style.display='none';_bsData=[];
  }
  function renderTable(rows,summary){
    if((!rows||!rows.length) && !parseFloat(summary.opening_balance||0)){renderEmpty('No transactions found.');return;}
    var opening=parseFloat(summary.opening_balance||0);
    var html='';
    if(opening){
      html+='<tr style="border-bottom:1px solid #f3f4f6;background:#fcfcfd;">'
        +'<td style="padding:12px 16px;font-size:13px;color:#6b7280;border-right:1px solid #e5e7eb;">'+fmtD(summary.period?.from||'')+'</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:#374151;border-right:1px solid #e5e7eb;font-weight:600;">Opening Balance</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:#9ca3af;text-align:right;border-right:1px solid #e5e7eb;">-</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:#9ca3af;text-align:right;border-right:1px solid #e5e7eb;">-</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:#1f2937;text-align:right;font-weight:600;">'+fmt(opening,true)+'</td>'
        +'</tr>';
    }
    html+=rows.map(function(r){
      var wd=parseFloat(r.withdrawal_amount||0),dep=parseFloat(r.deposit_amount||0);
      return'<tr style="border-bottom:1px solid #f3f4f6;">'
        +'<td style="padding:12px 16px;font-size:13px;color:#374151;border-right:1px solid #e5e7eb;">'+fmtD(r.date)+'</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:#374151;border-right:1px solid #e5e7eb;">'+(r.description||'—')+'</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:'+(wd>0?'#ef4444':'#9ca3af')+';text-align:right;border-right:1px solid #e5e7eb;">'+(wd>0?fmt(wd):'—')+'</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:'+(dep>0?'#16a34a':'#9ca3af')+';text-align:right;border-right:1px solid #e5e7eb;">'+(dep>0?fmt(dep):'—')+'</td>'
        +'<td style="padding:12px 16px;font-size:13px;color:#1f2937;text-align:right;">'+fmt(r.balance_amount)+'</td>'
        +'</tr>';
    }).join('');
    document.getElementById('bs-body').innerHTML=html;
    document.getElementById('bs-total-wd').textContent=fmt(summary.total_withdrawal||0);
    document.getElementById('bs-total-dep').textContent=fmt(summary.total_deposit||0);
    document.getElementById('bs-final-bal').textContent=fmt(summary.final_balance||0,true);
    document.getElementById('bs-foot').style.display='';
  }

  /* Print */
  document.getElementById('bs-print-btn').addEventListener('click',function(){
    var sel=document.getElementById('bs-bank-select');
    document.getElementById('bs-p-bank').textContent='Account: '+(sel.options[sel.selectedIndex]?.text||'—');
    document.getElementById('bs-p-date').textContent=new Date().toLocaleDateString('en-GB');
    var range='';
    if(document.getElementById('bs-date-toggle').checked){
      var f=document.getElementById('bs-from').value,t=document.getElementById('bs-to').value;
      if(f||t) range='Period: '+fmtD(f)+' — '+fmtD(t);
    }
    document.getElementById('bs-p-range').textContent=range;
    document.getElementById('bs-p-body').innerHTML=_bsData.length?_bsData.map(function(r){
      var wd=parseFloat(r.withdrawal_amount||0),dep=parseFloat(r.deposit_amount||0);
      return'<tr><td style="padding:7px 10px;font-size:12px;border:1px solid #e5e7eb;">'+fmtD(r.date)+'</td>'
        +'<td style="padding:7px 10px;font-size:12px;border:1px solid #e5e7eb;">'+(r.description||'—')+'</td>'
        +'<td style="padding:7px 10px;font-size:12px;color:'+(wd>0?'#ef4444':'#9ca3af')+';text-align:right;border:1px solid #e5e7eb;">'+(wd>0?fmt(wd):'—')+'</td>'
        +'<td style="padding:7px 10px;font-size:12px;color:'+(dep>0?'#16a34a':'#9ca3af')+';text-align:right;border:1px solid #e5e7eb;">'+(dep>0?fmt(dep):'—')+'</td>'
        +'<td style="padding:7px 10px;font-size:12px;text-align:right;border:1px solid #e5e7eb;">'+fmt(r.balance_amount)+'</td></tr>';
    }).join(''):'<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:16px;font-size:12px;border:1px solid #e5e7eb;">No data</td></tr>';
    document.getElementById('bs-p-wd').textContent=document.getElementById('bs-total-wd').textContent;
    document.getElementById('bs-p-dep').textContent=document.getElementById('bs-total-dep').textContent;
    document.getElementById('bs-p-bal').textContent=document.getElementById('bs-final-bal').textContent;
    new bootstrap.Modal(document.getElementById('bsPrintModal')).show();
  });
  document.getElementById('bs-do-print').addEventListener('click',function(){
    var w=window.open('','_blank','width=940,height=720');
    w.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Bank Statement</title><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,sans-serif;padding:30px 38px}table{width:100%;border-collapse:collapse}th,td{padding:8px 10px;font-size:12px;border:1px solid #e5e7eb}th{background:#f3f4f6;font-weight:600;color:#6b7280}h2{font-size:20px;margin-bottom:4px}p{font-size:12px;color:#6b7280;margin:2px 0}hr{border-color:#e5e7eb;margin:14px 0}@media print{@page{margin:14mm}}</style></head><body>'+document.getElementById('bs-print-area').innerHTML+'</body></html>');
    w.document.close();w.focus();setTimeout(function(){w.print();w.close();},400);
  });

  /* Excel */
  document.getElementById('bs-excel-btn').addEventListener('click',function(){
    var id=document.getElementById('bs-bank-select').value;
    if(!id){alert('Please select a bank account first.');return;}
    var p=new URLSearchParams({bank_id:id,export:'excel'});
    if(document.getElementById('bs-date-toggle').checked){
      var f=document.getElementById('bs-from').value,t=document.getElementById('bs-to').value;
      if(f)p.append('from',f);if(t)p.append('to',t);
    }
    window.location.href='/dashboard/reports/bank-statement/export?'+p;
  });
})();
</script>
