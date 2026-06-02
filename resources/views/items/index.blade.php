@extends('layouts.app')

@section('title', 'Items')
@section('page', 'items')

@push('styles')
<style>
    
* { box-sizing: border-box; margin: 0; padding: 0; }

.il-page {
    display: flex; flex-direction: column;
    height: 100vh; max-height: 100vh;
    background: #fff; overflow: hidden;
}
/* ══════════════════════════════
   DELETE CONFIRMATION MODAL
══════════════════════════════ */
#delete-overlay {
    position: fixed; inset: 0; z-index: 2000;
    background: rgba(0,0,0,.45);
    display: none; align-items: center; justify-content: center;
}
#delete-overlay.open { display: flex; }
#delete-modal {
    background: #fff; border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,.25);
    width: 420px; max-width: 95vw;
    animation: popIn .15s ease-out;
}
.del-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 14px;
    background: #e8f0fb;
    border-radius: 8px 8px 0 0;
}
.del-header-title {
    font-size: 15px; font-weight: 700; color: #1a2a4a;
}
.del-header-close {
    background: none; border: none; cursor: pointer;
    font-size: 18px; color: #6b7280; line-height: 1;
    width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
    border-radius: 4px;
}
.del-header-close:hover { background: #d1d5db; color: #111; }
.del-body {
    padding: 22px 24px 20px;
}
.del-body p { font-size: 14px; font-weight: 600; color: #1a2a4a; }
.del-footer {
    display: flex; justify-content: flex-end; gap: 12px;
    padding: 14px 20px 18px;
}
.del-btn-yes {
    background: #5b9bd5; border: none; border-radius: 5px;
    padding: 9px 28px; font-size: 14px; font-weight: 600;
    color: #fff; cursor: pointer; transition: background .15s;
}
.del-btn-yes:hover { background: #3a7bbf; }
.del-btn-no {
    background: #5b9bd5; border: none; border-radius: 5px;
    padding: 9px 28px; font-size: 14px; font-weight: 600;
    color: #fff; cursor: pointer; transition: background .15s;
}
.del-btn-no:hover { background: #3a7bbf; }

/* BULK ACTION MODAL */
.bulk-overlay {
    position: fixed; inset: 0; z-index: 2100;
    background: rgba(0,0,0,.45);
    display: none; align-items: center; justify-content: center;
}
.bulk-overlay.open { display: flex; }
.bulk-modal {
    background: #fff; border-radius: 10px;
    box-shadow: 0 12px 42px rgba(0,0,0,.22);
    width: 760px; max-width: 96vw;
    animation: popIn .15s ease-out;
}
.bulk-modal-hdr {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px 14px;
    border-bottom: 1px solid #f3f4f6;
}
.bulk-modal-title {
    font-size: 17px; font-weight: 700; color: #111827;
}
.bulk-modal-close {
    background: none; border: none; cursor: pointer;
    color: #6b7280; font-size: 18px; line-height: 1;
}
.bulk-modal-search {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px;
    padding: 10px 12px; font-size: 13px; color: #374151; outline: none;
}
.bulk-modal-search:focus { border-color: #2563eb; }
.bulk-info-bar {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 24px; background: #eff6ff; color: #2563eb;
    font-size: 13px; font-weight: 500; border-top: 1px solid #dbeafe;
}
.bulk-empty {
    text-align: center; padding: 46px 20px; color: #9ca3af; font-size: 14px;
}
.bulk-table th, .bulk-table td { border-bottom: 1px solid #f3f4f6; }
.bulk-table tbody tr:last-child td { border-bottom: none; }

/* BULK UPDATE MODAL STYLES */
.bulk-edit-field {
    border: 1.5px solid #d1d5db; border-radius: 6px;
    padding: 8px 10px; font-size: 13px; color: #374151;
    outline: none; background: #fff; transition: border-color .15s;
    width: 100%;
}
.bulk-edit-field:focus { border-color: #2563eb; }
.bulk-edit-field::placeholder { color: #9ca3af; }
.bulk-row-editor {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-bottom: 1px solid #f3f4f6;
}
.u-overlay {
    position: fixed; inset: 0; z-index: 2200;
    background: rgba(0,0,0,.45);
    display: none; align-items: center; justify-content: center;
}
.u-overlay.open { display: flex; }
.u-mbox {
    width: 620px; max-width: 96vw;
    background: #fff; border-radius: 10px;
    box-shadow: 0 12px 42px rgba(0,0,0,.22);
    animation: popIn .15s ease-out;
}
.u-mhdr, .u-mfoot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px;
    border-bottom: 1px solid #f3f4f6;
}
.u-mfoot {
    border-bottom: none;
    border-top: 1px solid #f3f4f6;
    justify-content: flex-end;
    gap: 10px;
}
.u-mbody { padding: 18px 22px; }
.u-mclose {
    background: none; border: none; cursor: pointer;
    color: #6b7280; font-size: 18px; line-height: 1;
}
.u-mlabel {
    display: block; margin-bottom: 8px;
    color: #374151; font-size: 13px; font-weight: 700;
}
.conv-select, .conv-rate {
    width: 100%;
    border: 1.5px solid #d1d5db; border-radius: 8px;
    padding: 10px 12px; font-size: 13px; color: #374151;
    background: #fff;
}
.conv-eq {
    font-size: 18px; font-weight: 700; color: #374151;
}
.u-mbtn {
    border: none; border-radius: 7px;
    padding: 10px 22px; font-size: 13px; font-weight: 700;
    cursor: pointer;
}
.u-mbtn-new {
    background: #f3f4f6; color: #374151;
}
.u-mbtn-save {
    background: #e53e3e; color: #fff;
}
.bulk-row-editor input { flex: 1; }
.bulk-col-item { flex: 2; }
.bulk-col-price { flex: 1; }
.bulk-col-unit { flex: 1; }

/* ── TOP TABS ── */
.il-tabs {
    display: flex; border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0; background: #fff;
}
.il-tab {
    flex: 1; text-align: center; padding: 16px 0;
    font-size: 13px; font-weight: 600; letter-spacing: .06em;
    color: #9ca3af; cursor: pointer;
    border-bottom: 2px solid transparent; transition: all .15s;
    user-select: none;
}
.il-tab:hover { color: #4b5563; }
.il-tab.active { color: #e53e3e; border-bottom-color: #e53e3e; }

/* ── BODY ── */
.il-body { display: flex; flex: 1; min-height: 0; overflow: hidden; }

/* ── LEFT PANEL ── */
.il-left {
    width: 280px; flex-shrink: 0;
    border-right: 1px solid #e5e7eb;
    display: flex; flex-direction: column; background: #fff;
}
.il-left-toolbar {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 14px; border-bottom: 1px solid #f3f4f6; flex-shrink: 0;
}
.il-search-btn {
    width: 34px; height: 34px; border: 1.5px solid #e5e7eb;
    border-radius: 6px; background: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0; transition: border-color .15s;
}
.il-search-btn:hover { border-color: #93c5fd; }
.il-search-wrap { flex: 1; position: relative; display: none; }
.il-search-wrap.open { display: block; }
.il-search-input {
    width: 100%; border: 1.5px solid #2563eb; border-radius: 6px;
    padding: 7px 10px 7px 30px; font-size: 13px; outline: none; color: #374151;
}
.il-search-icon { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); }

.il-add-group { display: flex; align-items: center; margin-left: auto; position: relative; }
.il-add-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f59e0b; color: #fff; border: none;
    border-radius: 6px 0 0 6px; padding: 8px 14px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: background .15s; white-space: nowrap; height: 36px;
}
.il-add-btn:hover { background: #d97706; }
.il-add-chevron {
    background: #d97706; border: none;
    border-radius: 0 6px 6px 0; padding: 0 10px;
    cursor: pointer; color: #fff; display: flex; align-items: center; justify-content: center;
    transition: background .15s; height: 36px;
}
.il-add-chevron:hover { background: #b45309; }
.il-add-dd {
    position: absolute; top: calc(100% + 4px); right: 28px;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 6px; box-shadow: 0 6px 20px rgba(0,0,0,.12);
    z-index: 500; min-width: 150px; display: none;
}
.il-add-dd.open { display: block; }
.il-add-dd-item {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; cursor: pointer; font-size: 13px; color: #374151;
}
.il-add-dd-item:hover { background: #f9fafb; }

.il-more-btn {
    background: none; border: 1.5px solid #e5e7eb;
    border-radius: 6px; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #6b7280; flex-shrink: 0;
    transition: border-color .15s; position: relative;
}
.il-more-btn:hover { border-color: #93c5fd; }
.il-bulk-dd {
    position: absolute; top: calc(100% + 4px); right: 0;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 6px; box-shadow: 0 6px 20px rgba(0,0,0,.12);
    z-index: 500; min-width: 190px; display: none;
}
.il-bulk-dd.open { display: block; }
.il-bulk-dd-item {
    padding: 11px 16px; cursor: pointer; font-size: 13px; color: #374151;
}
.il-bulk-dd-item:hover { background: #f9fafb; }

.il-list { flex: 1; overflow-y: auto; }
.il-list::-webkit-scrollbar { width: 4px; }
.il-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

.il-list-header {
    display: flex; align-items: center;
    padding: 9px 14px; background: #f9fafb;
    border-bottom: 1px solid #f3f4f6;
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em; color: #9ca3af;
    position: relative;
}
.col-item { flex: 1; }
.col-filter {
    width: 20px; display: flex; align-items: center;
    justify-content: center; cursor: pointer; color: #e53e3e; position: relative;
    font-size: 12px;
}
.col-qty { width: 70px; text-align: right; }
.col-netw { width: 82px; text-align: right; }
.col-sort { width: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #6b7280; }

/* Left panel filter dropdown */
.il-filter-dd {
    position: absolute; top: calc(100% + 2px); left: 50%;
    transform: translateX(-50%);
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
    z-index: 500; min-width: 160px; display: none; padding: 14px 14px 10px;
}
.il-filter-dd.open { display: block; }
.il-filter-row {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 0; font-size: 13px; color: #374151; cursor: pointer;
}
.il-filter-row input[type=checkbox] { accent-color: #2563eb; width: 15px; height: 15px; }
.il-filter-actions { display: flex; gap: 8px; margin-top: 10px; }
.il-filter-clear {
    flex: 1; border: 1.5px solid #e5e7eb; background: #fff;
    border-radius: 20px; padding: 7px 0; font-size: 12px;
    color: #6b7280; cursor: pointer; font-weight: 500;
}
.il-filter-apply {
    flex: 1; border: none; background: #e53e3e;
    border-radius: 20px; padding: 7px 0; font-size: 12px;
    color: #fff; cursor: pointer; font-weight: 600;
}

.il-item-row {
    display: flex; align-items: center; padding: 12px 14px;
    border-bottom: 1px solid #f8f9fa; cursor: pointer; transition: background .12s;
    position: relative;
}
.il-item-row:hover { background: #f9fafb; }
.il-item-row.active { background: #eff6ff; }
.il-item-dot { width: 8px; height: 8px; border-radius: 50%; background: #9ca3af; margin-right: 8px; flex-shrink: 0; }
.il-item-name { flex: 1; min-width: 0; font-size: 14px; color: #111827; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.il-item-qty { width: 70px; flex-shrink: 0; text-align: right; font-size: 14px; color: #10b981; font-weight: 600; }
.il-item-qty.neg { color: #dc2626; }
.il-item-qty.pos { color: #16a34a; }
.il-item-netw { width: 82px; flex-shrink: 0; text-align: right; font-size: 14px; color: #6b7280; font-weight: 600; }
.il-item-netw.neg { color: #dc2626; }
.il-item-netw.pos { color: #dc2626; }

.il-item-more-wrap {
    position: relative; width: 24px; height: 24px; flex-shrink: 0;
}
.il-item-more-btn {
    width: 24px; height: 24px; display: flex; align-items: center;
    justify-content: center; color: #9ca3af; cursor: pointer;
    border-radius: 4px; background: none; border: none;
}
.il-item-more-btn:hover { background: #f3f4f6; color: #374151; }
.il-item-dd {
    position: absolute; right: 0; top: calc(100% + 2px);
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 6px; box-shadow: 0 6px 20px rgba(0,0,0,.12);
    z-index: 600; min-width: 140px; display: none;
}
.il-item-dd.open { display: block; }
.il-item-dd-item {
    padding: 11px 16px; cursor: pointer; font-size: 13px; color: #374151;
}
.il-item-dd-item:hover { background: #f9fafb; }
.il-item-dd-item.danger { color: #ef4444; }
.il-item-dd-item.danger:hover { background: #fef2f2; }

/* ── RIGHT PANEL ── */
.il-right { flex: 1; display: flex; flex-direction: column; background: #fff; min-width: 0; }

.il-no-selection {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center; color: #9ca3af; gap: 12px;
}
.il-no-sel-icon {
    width: 64px; height: 64px; background: #f3f4f6; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}

.il-detail-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 24px 12px; border-bottom: 1px solid #f3f4f6; flex-shrink: 0;
}
.il-detail-name-row { display: flex; align-items: center; gap: 10px; position: relative; }
.il-detail-name { font-size: 17px; font-weight: 700; color: #111827; }
.il-icon-btn {
    background: none; border: none; cursor: pointer;
    color: #6b7280; padding: 4px; border-radius: 4px; transition: color .12s;
}
.il-icon-btn:hover { color: #2563eb; }
.il-adjust-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #2563eb; color: #fff; border: none;
    border-radius: 6px; padding: 9px 16px; font-size: 13px;
    font-weight: 600; cursor: pointer; transition: background .15s;
}
.il-adjust-btn:hover { background: #1d4ed8; }

.il-stats {
    display: flex; align-items: stretch;
    border-bottom: 1px solid #f3f4f6; flex-shrink: 0;
}
.il-stat-left {
    display: flex; flex-direction: column;
    padding: 10px 24px; gap: 5px; flex: 1; justify-content: center;
}
.il-stat-right {
    display: flex; flex-direction: column;
    padding: 10px 24px; gap: 5px;
    align-items: flex-end; justify-content: center;
}
.il-stat-item { display: flex; align-items: center; gap: 6px; }
.il-stat-label { font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.il-stat-value { font-size: 13px; font-weight: 700; color: #16a34a; }
.il-stat-value.neutral { color: #16a34a; }
.il-stat-value.neg { color: #dc2626 !important; }
.il-stat-value.pos { color: #16a34a !important; }

.il-share-popup {
    position: absolute; top: calc(100% + 6px); left: 0;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,.15);
    z-index: 700; display: none; padding: 12px 8px; min-width: 260px;
}
.il-share-popup.open { display: flex; gap: 4px; }
.il-share-option {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 10px 14px; cursor: pointer; border-radius: 6px; flex: 1;
    font-size: 11px; color: #374151; font-weight: 500; transition: background .12s;
}
.il-share-option:hover { background: #f3f4f6; }
.il-share-option .share-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.share-email { background: #fff0f0; color: #e53e3e; }
.share-sms   { background: #f0fdf4; color: #16a34a; }
.share-wa    { background: #f0fdf4; color: #25d366; }
.share-copy  { background: #f5f3ff; color: #7c3aed; }

/* ── TRANSACTIONS SECTION ── */
.il-txn-section { flex: 1; display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
.il-txn-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px 10px; border-bottom: 1px solid #f0f0f0; flex-shrink: 0;
}
.il-txn-title { font-size: 12px; font-weight: 700; letter-spacing: .08em; color: #374151; text-transform: uppercase; }
.il-txn-right { display: flex; align-items: center; gap: 8px; }
.il-txn-search {
    border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 7px 10px 7px 34px; font-size: 13px; outline: none; width: 260px; color: #374151;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' viewBox='0 0 24 24' stroke='%23b0b8c4' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath stroke-linecap='round' d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 11px center;
}
.il-txn-search::placeholder { color: #b0b8c4; }
.il-txn-search:focus { border-color: #2563eb; outline: none; }

/* ── BLUE Excel icon button (matching Vyapar image) ── */
.il-export-btn {
    background: none; border: none; cursor: pointer; padding: 2px;
    display: flex; align-items: center; justify-content: center;
}
.il-export-btn .excel-icon {
    width: 26px; height: 26px; background: #ffffff; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 14px; font-weight: 800; letter-spacing: -1px;
    font-family: Arial, sans-serif;
}

/* ── RESIZABLE TABLE WRAPPER ── */
.il-tbl-wrap { flex: 1; overflow-y: auto; overflow-x: hidden; min-width: 0; }
.il-tbl-wrap::-webkit-scrollbar { width: 4px; height: 4px; }
.il-tbl-wrap::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

/* ── TABLE ── */
.il-tbl { width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 100%; }

.il-tbl th {
    padding: 12px 10px; font-size: 11px; font-weight: 600;
    text-transform: capitalize; color: #9ca3af;
    background: #f9fafb; border-bottom: 1px solid #ebebeb;
    border-right: 1px solid #d1d5db;
    text-align: left; white-space: nowrap;
    position: relative; overflow: hidden; user-select: none;
}
.il-tbl th[data-col="dot"] { padding: 0; border-bottom: 1px solid #ebebeb; }
.il-tbl th .th-inner {
    display: inline-flex; align-items: center; gap: 3px; cursor: pointer;
}
.th-sort-arrow {
    display: inline-flex; align-items: center;
    color: #4a4a4a; flex-shrink: 0; font-size: 10px; font-style: normal;
    opacity: 0; transition: opacity .1s; line-height: 1;
}
.il-tbl th.sort-asc  .th-sort-arrow,
.il-tbl th.sort-desc .th-sort-arrow { opacity: 1; }
.th-sort-arrow::after               { content: '↑'; }
.il-tbl th.sort-desc .th-sort-arrow::after { content: '↓'; }
.il-tbl th .th-filter-icon {
    color: #b8bec7; flex-shrink: 0; cursor: pointer; transition: color .15s;
    font-size: 10px;
}
.il-tbl th .th-filter-icon:hover  { color: #e53e3e; }
.il-tbl th .th-filter-icon.active { color: #e53e3e; }

/* ── COLUMN RESIZE HANDLE ── */
.col-resize-handle {
    position: absolute; right: 0; top: 0; bottom: 0;
    width: 5px; cursor: col-resize; z-index: 10;
}
.col-resize-handle:hover,
.col-resize-handle.resizing { background: #2563eb; opacity: .4; }

/* ── TABLE CELLS ── */
.il-tbl td {
    padding: 12px 10px; font-size: 12px; color: #374151; font-weight: 400;
    border-bottom: 1px solid #f3f4f6; vertical-align: middle;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.il-tbl td.td-dot { padding: 0 0 0 10px; width: 28px; vertical-align: middle; }
.il-tbl td.td-price, .il-tbl th.th-price-right { text-align: right; }
.il-tbl td.td-price { color: #16a34a; }
.il-tbl td.td-status { color: #9ca3af; }
.il-tbl td.td-actions { padding: 8px 4px; width: 50px; flex-shrink: 0; }
.il-tbl tbody tr:hover td { background: #fafafa; }
.il-tbl tbody tr.txn-selected td { background: #dbeafe; }
.il-tbl tbody tr:last-child td { border-bottom: none; }
.il-tbl td.td-date,
.il-tbl td.td-invoice,
.il-tbl td.td-type,
.il-tbl td.td-qty,
.il-tbl td.td-netw,
.il-tbl td.td-price,
.il-tbl td.td-status { white-space: nowrap; }
.il-tbl td.td-name,
.il-tbl td.td-broker {
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
    line-height: 1.35;
    word-break: break-word;
}
.il-tbl td.td-name { font-weight: 500; }
.il-tbl td.td-broker { color: #334155; }

/* Row action menu */
.il-row-menu-wrap { position: relative; }
.il-row-menu-btn {
    background: none; border: none; cursor: pointer; color: #9ca3af;
    padding: 6px 8px; border-radius: 4px; font-size: 16px; line-height: 1;
    min-width: 32px; min-height: 32px; display: flex; align-items: center; justify-content: center;
}
.il-row-menu-btn:hover { color: #374151; background: #f3f4f6; }
.il-row-menu {
    position: fixed;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 6px; box-shadow: 0 6px 20px rgba(0,0,0,.12);
    z-index: 9000; min-width: 150px; max-width: 240px; max-height: 280px; overflow-y: auto; display: none;
}
.il-row-menu.open { display: block; }
.il-row-menu-item {
    padding: 11px 16px; cursor: pointer; font-size: 13px; color: #374151;
    white-space: nowrap;
}
.il-row-menu-item:hover { background: #f9fafb; }
.il-row-menu-item.danger { color: #ef4444; }
.il-row-menu-item.danger:hover { background: #fef2f2; }

/* ══════════════════════════════
   COLUMN FILTER DROPDOWNS
══════════════════════════════ */
.col-filter-dd {
    display: none; position: fixed;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
    z-index: 9999; min-width: 220px; padding: 16px 16px 12px;
}
.col-filter-dd.open { display: block; }
.cfd-title { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 12px; }
.cfd-cb-row {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 2px; font-size: 13px; color: #374151; cursor: pointer;
}
.cfd-cb-row input[type=checkbox] { width: 15px; height: 15px; accent-color: #2563eb; flex-shrink: 0; }
.cfd-select {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 6px;
    padding: 9px 10px; font-size: 13px; color: #374151;
    background: #fff; outline: none; cursor: pointer; margin-bottom: 10px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}
.cfd-input {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 6px;
    padding: 9px 10px; font-size: 13px; color: #374151;
    outline: none; box-sizing: border-box;
}
.cfd-input:focus { border-color: #2563eb; }
.cfd-input::placeholder { color: #9ca3af; }
.cfd-date-lbl { font-size: 11px; color: #9ca3af; margin-bottom: 6px; }
.cfd-actions { display: flex; gap: 8px; margin-top: 14px; }
.cfd-clear {
    flex: 1; border: 1.5px solid #e5e7eb; background: #fff;
    border-radius: 20px; padding: 8px 0; font-size: 12px;
    color: #6b7280; cursor: pointer; font-weight: 500;
}
.cfd-apply {
    flex: 1; border: none; background: #e53e3e;
    border-radius: 20px; padding: 8px 0; font-size: 12px;
    color: #fff; cursor: pointer; font-weight: 600;
}
.cfd-clear:hover { background: #f3f4f6; }
.cfd-apply:hover { background: #4430c5; }

/* ══════════════════════════════
   STOCK ADJUSTMENT MODAL
══════════════════════════════ */
#adj-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,.45);
    display: none; align-items: center; justify-content: center;
}
#adj-overlay.open { display: flex; }
#adj-modal {
    background: #fff; border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,.2);
    width: 780px; max-width: 96vw;
    animation: popIn .15s ease-out;
}
@keyframes popIn {
    from { opacity:0; transform:scale(.96); }
    to   { opacity:1; transform:scale(1); }
}
.adj-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px 16px; border-bottom: 1px solid #f3f4f6;
}
.adj-title { font-size: 17px; font-weight: 700; color: #111827; }
.adj-toggle-row { display: flex; align-items: center; gap: 14px; }
.adj-toggle-lbl { font-size: 14px; font-weight: 600; color: #2563eb; cursor: pointer; }
.adj-toggle-lbl.inactive { color: #9ca3af; font-weight: 500; }
.adj-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
.adj-switch input { opacity:0; width:0; height:0; }
.adj-slider {
    position: absolute; cursor: pointer; inset: 0;
    background: #2563eb; border-radius: 24px; transition: .2s;
}
.adj-slider:before {
    content: ""; position: absolute;
    width: 18px; height: 18px; left: 3px; bottom: 3px;
    background: white; border-radius: 50%; transition: .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.3);
}
input:checked + .adj-slider:before { transform: translateX(20px); }
.adj-close { background: none; border: none; cursor: pointer; color: #6b7280; padding: 4px; }
.adj-close:hover { color: #111827; }
.adj-body { padding: 20px 28px; }
.adj-item-name { font-size: 14px; color: #374151; margin-bottom: 20px; }
.adj-fields { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.adj-input {
    border: 1.5px solid #d1d5db; border-radius: 6px;
    padding: 11px 14px; font-size: 14px; color: #374151;
    outline: none; background: #fff; transition: border-color .15s;
}
.adj-input:focus { border-color: #2563eb; }
.adj-input::placeholder { color: #9ca3af; }
.adj-qty-wrap { display: flex; align-items: center; position: relative; }
.adj-qty-input { width: 130px; border-radius: 6px 0 0 6px !important; }
.adj-unit-sel {
    border: 1.5px solid #d1d5db; border-left: none;
    border-radius: 0 6px 6px 0; padding: 11px 10px 11px 8px;
    font-size: 14px; color: #374151; background: #f9fafb;
    outline: none; cursor: pointer; min-width: 70px; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 8px center; padding-right: 26px;
}
.adj-price-input { width: 160px; }
.adj-details-input { flex: 1; min-width: 180px; }
.adj-date-wrap { position: relative; }
.adj-date-label {
    position: absolute; top: 4px; left: 14px;
    font-size: 10px; color: #9ca3af; pointer-events: none; z-index: 1;
}
.adj-date-input { width: 180px; padding-top: 20px !important; padding-bottom: 6px !important; }
.adj-footer {
    display: flex; justify-content: flex-end;
    padding: 16px 28px; border-top: 1px solid #f3f4f6; gap: 10px;
}
.adj-cancel {
    background: #fff; border: 1.5px solid #d1d5db; border-radius: 6px;
    padding: 10px 24px; font-size: 14px; color: #6b7280; cursor: pointer;
}
.adj-save {
    background: #2563eb; border: none; border-radius: 6px;
    padding: 10px 32px; font-size: 14px; font-weight: 700;
    color: #fff; cursor: pointer; transition: background .15s;
}
.adj-save:hover { background: #1d4ed8; }

#toast {
    position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(20px);
    background: #111827; color: #fff; padding: 10px 22px;
    border-radius: 8px; font-size: 13px; font-weight: 500;
    opacity: 0; transition: all .25s; z-index: 9999; pointer-events: none;
}
#toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
@endpush

@section('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="il-page">

    {{-- TOP TABS --}}
    <div class="il-tabs">
    <div class="il-tab {{ request()->routeIs('items') ? 'active' : '' }}" onclick="window.location.href='{{ route('items') }}'">PRODUCTS</div>
    <div class="il-tab {{ request()->routeIs('items.services') ? 'active' : '' }}" onclick="window.location.href='{{ route('items.services') }}'">SERVICES</div>
    <div class="il-tab {{ request()->routeIs('items.category') ? 'active' : '' }}" onclick="window.location.href='{{ route('items.category') }}'">CATEGORY</div>
    <div class="il-tab {{ request()->routeIs('items.units') ? 'active' : '' }}" onclick="window.location.href='{{ route('items.units') }}'">UNITS</div>
</div>

    @if(count($products) === 0)

    {{-- ══════════════════════════════
         EMPTY STATE
    ══════════════════════════════ --}}
    <div style="flex:1;display:flex;align-items:center;justify-content:center;background:#fff;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:18px;text-align:center;">
            <div style="width:220px;height:220px;background:#dbeafe;border-radius:50%;position:relative;">
                <div style="position:absolute;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.13);display:flex;align-items:center;justify-content:center;width:52px;height:52px;top:14px;left:50%;transform:translateX(-50%);font-size:26px;">🧺</div>
                <div style="position:absolute;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.13);display:flex;align-items:center;justify-content:center;width:48px;height:48px;top:50%;left:8px;transform:translateY(-50%);font-size:22px;">🖨️</div>
                <div style="position:absolute;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.13);display:flex;align-items:center;justify-content:center;width:48px;height:48px;top:50%;right:8px;transform:translateY(-50%);font-size:22px;">🫖</div>
                <div style="position:absolute;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.13);display:flex;align-items:center;justify-content:center;width:46px;height:46px;bottom:24px;left:26px;font-size:20px;">🧵</div>
                <div style="position:absolute;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.13);display:flex;align-items:center;justify-content:center;width:46px;height:46px;bottom:24px;right:26px;font-size:20px;">📦</div>
                <div style="position:absolute;background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.16);display:flex;align-items:center;justify-content:center;width:54px;height:54px;top:50%;left:50%;transform:translate(-50%,-50%);font-size:26px;z-index:2;">📋</div>
            </div>
            <p style="font-size:14px;color:#6b7280;max-width:420px;line-height:1.65;">
                Add products you sell to your customers and create Sale invoices for them faster.
            </p>
            <button onclick="window.location.href='{{ route('items.create') }}'" style="display:inline-flex;align-items:center;background:#f59e0b;color:#fff;border:none;border-radius:6px;padding:13px 32px;font-size:14px;font-weight:600;cursor:pointer;">
                Add Your First Product
            </button>
        </div>
    </div>

    @else

    {{-- ══════════════════════════════
         NORMAL LAYOUT (products exist)
    ══════════════════════════════ --}}
    <div class="il-body">

        {{-- LEFT PANEL --}}
        <div class="il-left">
            <div class="il-left-toolbar">
                <button class="il-search-btn" onclick="toggleSearch()" title="Search">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#6b7280" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                </button>
                <div class="il-search-wrap" id="search-wrap">
                    <svg class="il-search-icon" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" class="il-search-input" id="search-input" placeholder="Search items..." oninput="filterItems()"/>
                </div>

                <div class="il-add-group" id="add-group">
                    <button class="il-add-btn" onclick="goToAddItem()">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.8"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </button>
                    <button class="il-add-chevron" onclick="toggleAddDD(event)" title="More options">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="il-add-dd" id="add-dd">
                        <div class="il-add-dd-item" onclick="closeAddDD()">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#6b7280" stroke-width="2"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Import Items
                        </div>
                    </div>
                </div>

                <div class="il-more-btn" id="bulk-wrap" onclick="toggleBulkDD(event)">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    <div class="il-bulk-dd" id="bulk-dd">
                        <div class="il-bulk-dd-item" onclick="bulkAction('bulk-inactive')">Bulk Inactive</div>
                        <div class="il-bulk-dd-item" onclick="bulkAction('bulk-active')">Bulk Active</div>
                        <div class="il-bulk-dd-item" onclick="bulkAction('bulk-update')">Bulk Update Items</div>
                        <div class="il-bulk-dd-item" onclick="bulkAction('bulk-assign-unit')">Assign Unit</div>
                        <div class="il-bulk-dd-item" onclick="bulkAction('bulk-assign-code')">Bulk Assign Code</div>
                    </div>
                </div>
            </div>

            <div class="il-list-header">
                <span class="col-item">ITEM</span>
                <span class="col-filter" onclick="toggleFilterDD(event)" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                    <div class="il-filter-dd" id="filter-dd" onclick="event.stopPropagation()">
                        <label class="il-filter-row">
                            <input type="checkbox" id="filter-active" checked onchange="applyFilter()"> Active
                        </label>
                        <label class="il-filter-row">
                            <input type="checkbox" id="filter-inactive" onchange="applyFilter()"> InActive
                        </label>
                        <div class="il-filter-actions">
                            <button class="il-filter-clear" onclick="clearFilter()">Clear</button>
                            <button class="il-filter-apply" onclick="closeFilterDD()">Apply</button>
                        </div>
                    </div>
                </span>
                <span class="col-qty">QUANTITY</span>
                <span class="col-netw">NET W</span>
                <span class="col-sort" id="qty-sort-arrow" onclick="sortByQty()" title="Sort by quantity" style="font-size:12px;color:#6b7280;">↕</span>
                <span style="width:24px;"></span>
            </div>

            <div class="il-list" id="items-list"></div>
        </div>

        {{-- RIGHT PANEL --}}
        <div class="il-right">

            <div class="il-no-selection" id="no-selection">
                <div class="il-no-sel-icon">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.5"><path stroke-linecap="round" d="M20 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path stroke-linecap="round" d="M16 3H8l-2 4h12l-2-4z"/></svg>
                </div>
                <div style="font-size:15px;color:#6b7280;font-weight:500;">Select an item to view details</div>
                <div style="font-size:13px;color:#9ca3af;">or add a new item using the button above</div>
            </div>

            <div id="item-detail" style="display:none;flex-direction:column;flex:1;min-height:0;">

                <div class="il-detail-header">
                    <div class="il-detail-name-row">
                        <span class="il-detail-name" id="detail-name">—</span>
                        <button class="il-icon-btn" title="Share/Export" onclick="toggleSharePopup(event)">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-4.553M19.553 5.447L15 5m4.553.447V10M4 12v7a1 1 0 001 1h14a1 1 0 001-1v-3M4 12V5a1 1 0 011-1h7"/></svg>
                        </button>
                        <div class="il-share-popup" id="share-popup" onclick="event.stopPropagation()">
                            <div class="il-share-option" onclick="shareVia('email')">
                                <div class="share-icon share-email">✉️</div><span>EMAIL</span>
                            </div>
                            <div class="il-share-option" onclick="shareVia('sms')">
                                <div class="share-icon share-sms">💬</div><span>SMS</span>
                            </div>
                            <div class="il-share-option" onclick="shareVia('whatsapp')">
                                <div class="share-icon share-wa">
                                    <svg width="20" height="20" viewBox="0 0 32 32" fill="#25d366"><path d="M16 3C9 3 3 9 3 16c0 2.3.6 4.5 1.8 6.5L3 29l6.7-1.8C11.6 28.4 13.8 29 16 29c7 0 13-6 13-13S23 3 16 3zm6.5 18.2c-.3.8-1.5 1.5-2.1 1.6-.5.1-1.2.1-1.9-.1-.5-.1-1.1-.3-1.8-.6-3.2-1.4-5.3-4.6-5.5-4.8-.2-.2-1.4-1.9-1.4-3.6 0-1.7.9-2.5 1.2-2.8.3-.3.7-.4 1-.4h.7c.2 0 .5 0 .7.6l.9 2.3c.1.2.1.5 0 .7l-.5.6-.4.5c.2.4.9 1.5 1.8 2.3 1 .9 1.9 1.3 2.3 1.4.3-.4.7-.9.9-1.1.2-.2.4-.2.6-.1l2.1 1c.2.1.4.2.5.4.1.3.1 1-.2 1.8z"/></svg>
                                </div>
                                <span>WHATSAPP</span>
                            </div>
                            <div class="il-share-option" onclick="shareVia('copy')">
                                <div class="share-icon share-copy">🔗</div><span>COPY LINK</span>
                            </div>
                        </div>
                    </div>
                    <button class="il-adjust-btn" onclick="openAdjModal()">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        ADJUST ITEM
                    </button>
                </div>

                <div class="il-stats">
                    <div class="il-stat-left">
                        <div class="il-stat-item">
                            <span class="il-stat-label">SALE PRICE:</span>
                            <span class="il-stat-value" id="detail-sale">—</span>
                        </div>
                        <div class="il-stat-item">
                            <span class="il-stat-label">PURCHASE PRICE:</span>
                            <span class="il-stat-value" id="detail-purchase">—</span>
                        </div>
                    </div>
                    <div class="il-stat-right">
                        <div class="il-stat-item">
                            <span class="il-stat-label">STOCK QUANTITY:</span>
                            <span class="il-stat-value neutral" id="detail-stock-qty">0</span>
                        </div>
                        <div class="il-stat-item">
                            <span class="il-stat-label">STOCK VALUE:</span>
                            <span class="il-stat-value neutral" id="detail-stock-val">Rs 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="il-txn-section">
                    <div class="il-txn-header">
                        <span class="il-txn-title">TRANSACTIONS</span>
                        <div class="il-txn-right">
                            <input type="text" class="il-txn-search" placeholder="Search transactions..." oninput="filterTxns(this.value)"/>
                            <button class="il-export-btn" title="Export to Excel" onclick="exportToExcel()">
                                <div class="excel-icon">📊</div>
                            </button>
                        </div>
                    </div>

                    <div class="il-tbl-wrap">
                        <table class="il-tbl" id="txn-table">
                            <thead>
                                <tr id="txn-thead-row">
                                    <th style="width:28px;padding:0;" data-col="dot"></th>
                                    <th data-col="date" style="width:88px;">
                                        <span class="th-inner" onclick="sortTxnCol('date')">
                                            DATE <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-date')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="date"></div>
                                    </th>
                                    <th data-col="invoice" style="width:88px;">
                                        <span class="th-inner" onclick="sortTxnCol('invoice')">
                                            INVOICE NO <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-invoice')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="invoice"></div>
                                    </th>
                                    <th data-col="type" style="width:72px;">
                                        <span class="th-inner" onclick="sortTxnCol('type')">
                                            TYPE <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-type')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="type"></div>
                                    </th>
                                    <th data-col="name" style="width:118px;">
                                        <span class="th-inner" onclick="sortTxnCol('name')">
                                            PARTY NAME <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-name')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="name"></div>
                                    </th>
                                    <th data-col="broker" style="width:180px;">
                                        <span class="th-inner" onclick="sortTxnCol('broker')">
                                            BROKER <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-broker')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="broker"></div>
                                    </th>
                                    <th data-col="qty" style="width:68px;">
                                        <span class="th-inner" onclick="sortTxnCol('qty')">
                                            TADDAT <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-qty')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="qty"></div>
                                    </th>
                                    <th data-col="net_w" style="width:78px;">
                                        <span class="th-inner" onclick="sortTxnCol('net_w')">
                                            NET W <span class="th-sort-arrow"></span>
                                        </span>
                                        <div class="col-resize-handle" data-col="net_w"></div>
                                    </th>
                                    <th data-col="amount" style="width:110px;" class="th-price-right">
                                        <span class="th-inner" onclick="sortTxnCol('amount')" style="justify-content:flex-end;width:100%;">
                                            AMOUNT <span class="th-sort-arrow"></span>
                                        </span>
                                        <div class="col-resize-handle" data-col="amount"></div>
                                    </th>
                                    <th data-col="price" style="width:92px;" class="th-price-right">
                                        <span class="th-inner" onclick="sortTxnCol('price')" style="justify-content:flex-end;width:100%;">
                                            PRICE/UNIT <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-price')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="price"></div>
                                    </th>
                                    <th data-col="status" style="width:74px;">
                                        <span class="th-inner" onclick="sortTxnCol('status')">
                                            STATUS <span class="th-sort-arrow"></span>
                                            <i class="fa-solid fa-filter th-filter-icon" onclick="toggleColFilter(event,'cf-status')"></i>
                                        </span>
                                        <div class="col-resize-handle" data-col="status"></div>
                                    </th>
                                    <th style="width:40px;" data-col="actions"></th>
                                </tr>
                            </thead>
                            <tbody id="txn-tbody"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @endif

</div>

{{-- Toast --}}
<div id="toast"></div>

{{-- ══════════════════════════════
     COLUMN FILTER DROPDOWNS
══════════════════════════════ --}}
<div class="col-filter-dd" id="cf-type" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <label class="cfd-cb-row"><input type="checkbox" value="Sale" onchange="applyColFilters()"> Sale</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Sale (e-Invoice)" onchange="applyColFilters()"> Sale (e-Invoice)</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Purchase" onchange="applyColFilters()"> Purchase</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Add Adjustment" onchange="applyColFilters()"> Add Adjustment</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Reduce Adjustment" onchange="applyColFilters()"> Reduce Adjustment</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Opening Stock" onchange="applyColFilters()"> Opening Stock</label>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-type')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-invoice" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <select class="cfd-select" id="cf-invoice-op"><option value="contains">Contains</option><option value="exact">Exact match</option></select>
    <input type="text" class="cfd-input" id="cf-invoice-val" placeholder="INVOICE/REF. NO" oninput="applyColFilters()"/>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-invoice')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-name" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <select class="cfd-select" id="cf-name-op"><option value="contains">Contains</option><option value="exact">Exact match</option></select>
    <input type="text" class="cfd-input" id="cf-name-val" placeholder="NAME" oninput="applyColFilters()"/>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-name')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-broker" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <select class="cfd-select" id="cf-broker-op"><option value="contains">Contains</option><option value="exact">Exact match</option></select>
    <input type="text" class="cfd-input" id="cf-broker-val" placeholder="BROKER NAME" oninput="applyColFilters()"/>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-broker')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-date" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <select class="cfd-select" id="cf-date-op"><option value="equal">Equal To</option><option value="before">Before</option><option value="after">After</option></select>
    <div class="cfd-date-lbl">Select Date</div>
    <input type="date" class="cfd-input" id="cf-date-val" oninput="applyColFilters()"/>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-date')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-qty" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <select class="cfd-select" id="cf-qty-op">
        <option value="equal">Equal to</option>
        <option value="lt">Less Than</option>
        <option value="gt">Greater Than</option>
    </select>
    <input type="number" class="cfd-input" id="cf-qty-val" placeholder="QUANTITY" oninput="applyColFilters()"/>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-qty')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-price" onclick="event.stopPropagation()">
    <div class="cfd-title">Select Category</div>
    <select class="cfd-select" id="cf-price-op">
        <option value="equal">Equal to</option>
        <option value="lt">Less Than</option>
        <option value="gt">Greater Than</option>
    </select>
    <input type="number" class="cfd-input" id="cf-price-val" placeholder="PRICE/ UNIT" step="0.01" oninput="applyColFilters()"/>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-price')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="col-filter-dd" id="cf-status" onclick="event.stopPropagation()">
    <label class="cfd-cb-row"><input type="checkbox" value="Unpaid" onchange="applyColFilters()"> Unpaid</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Partial" onchange="applyColFilters()"> Partial</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Paid" onchange="applyColFilters()"> Paid</label>
    <label class="cfd-cb-row"><input type="checkbox" value="Cancelled" onchange="applyColFilters()"> Cancelled</label>
    <div class="cfd-actions">
        <button class="cfd-clear" onclick="clearColFilter('cf-status')">Clear</button>
        <button class="cfd-apply" onclick="applyColFilters();closeAllColFilters()">Apply</button>
    </div>
</div>

<div class="modal fade" id="itemTxnPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemTxnPreviewModalTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="min-height:70vh;">
                <iframe id="itemTxnPreviewFrame" title="Preview" style="width:100%; min-height:70vh; border:0;"></iframe>
            </div>
              <div class="modal-footer justify-content-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="itemTxnPreviewOpenPdf">Open PDF</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="itemTxnPreviewPrint">Print</button>
                <button type="button" class="btn btn-outline-success rounded-pill px-4" id="itemTxnPreviewSavePdf">Save PDF</button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="itemTxnPreviewEmailPdf">Email PDF</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
              </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemTxnHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemTxnHistoryModalTitle">Transaction History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemTxnHistoryModalBody" style="min-height:52vh;">
                <div class="text-center text-muted py-5">Loading...</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- BULK ACTION MODAL --}}
<div class="bulk-overlay" id="bulk-overlay" onclick="if(event.target===this)closeBulkModal()">
    <div class="bulk-modal" onclick="event.stopPropagation()">
        <div class="bulk-modal-hdr">
            <span class="bulk-modal-title" id="bulk-modal-title">Bulk Action</span>
            <button class="bulk-modal-close" onclick="closeBulkModal()">✕</button>
        </div>

        <!-- STATUS/INACTIVE MODAL VIEW -->
        <div id="bulk-status-view" style="display:none;">
            <div style="padding:14px 24px;">
                <input
                    class="bulk-modal-search"
                    id="bulk-search"
                    placeholder="Search items..."
                    oninput="renderBulkRows()"
                />
            </div>
            <div style="max-height:320px;overflow-y:auto;border-top:1px solid #f3f4f6;">
                <table class="bulk-table" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="width:44px;padding:10px 16px;">
                                <input type="checkbox" id="bulk-check-all" style="width:15px;height:15px;accent-color:#2563eb;" onchange="toggleAllBulk(this)">
                            </th>
                            <th style="padding:10px 16px;font-size:11px;color:#9ca3af;text-align:left;font-weight:700;letter-spacing:.06em;">ITEM</th>
                            <th style="width:120px;padding:10px 16px;font-size:11px;color:#9ca3af;text-align:right;font-weight:700;letter-spacing:.06em;">QUANTITY</th>
                        </tr>
                    </thead>
                    <tbody id="bulk-tbody"></tbody>
                </table>
            </div>
            <div class="bulk-info-bar">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <span id="bulk-info-text">Showing only active items</span>
            </div>
        </div>

        <!-- BULK UPDATE MODAL VIEW -->
        <div id="bulk-update-view" style="display:none;">
            <div style="padding:14px 24px;border-bottom:1px solid #f3f4f6;">
                <input
                    class="bulk-modal-search"
                    id="bulk-update-search"
                    placeholder="Search items..."
                    oninput="renderBulkEditRows()"
                />
            </div>
            <div style="max-height:400px;overflow-y:auto;">
                <div id="bulk-edit-tbody"></div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #f3f4f6;">
            <button onclick="closeBulkModal()" style="background:#f3f4f6;border:none;border-radius:7px;padding:10px 24px;font-size:13px;font-weight:600;cursor:pointer;color:#374151;">Cancel</button>
            <button id="bulk-action-btn" style="background:#e53e3e;color:#fff;border:none;border-radius:7px;padding:10px 24px;font-size:13px;font-weight:700;cursor:pointer;" onclick="applyBulkAction()">Apply</button>
        </div>
    </div>
</div>

<div class="bulk-overlay" id="assign-code-overlay" onclick="if(event.target===this)closeAssignCodeModal()">
    <div class="bulk-modal" onclick="event.stopPropagation()">
        <div class="bulk-modal-hdr">
            <span class="bulk-modal-title">Bulk Assign Code</span>
            <button class="bulk-modal-close" onclick="closeAssignCodeModal()">✕</button>
        </div>
        <div style="padding:14px 24px;border-bottom:1px solid #f3f4f6;">
            <input class="bulk-modal-search" id="assign-code-search" placeholder="Search selected items..." oninput="renderAssignCodeRows()"/>
        </div>
        <div style="max-height:420px;overflow-y:auto;">
            <div id="assign-code-body"></div>
        </div>
        <div class="bulk-info-bar">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
            <span>Showing items that don't have item code</span>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #f3f4f6;">
            <button onclick="closeAssignCodeModal()" style="background:#f3f4f6;border:none;border-radius:7px;padding:10px 24px;font-size:13px;font-weight:600;cursor:pointer;color:#374151;">Cancel</button>
            <button id="assign-code-save-btn" style="background:#e53e3e;color:#fff;border:none;border-radius:7px;padding:10px 24px;font-size:13px;font-weight:700;cursor:pointer;" onclick="saveAssignedCodes()">Assign Code</button>
        </div>
    </div>
</div>

<div class="u-overlay" id="add-conv-overlay" onclick="if(event.target===this)closeAddConversion()">
    <div class="u-mbox">
        <div class="u-mhdr">
            <span>Assign Unit</span>
            <button class="u-mclose" onclick="closeAddConversion()">✕</button>
        </div>
        <div class="u-mbody">
            <div class="bulk-info-bar" style="margin:-6px -6px 16px -6px;border-top:none;border-radius:8px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <span id="assign-unit-selected-text">Selected items: 0</span>
            </div>
            <div class="conv-fields" style="display:flex; align-items:flex-end; gap:10px; flex-wrap:nowrap;">
                <div style="flex:1;">
                    <label class="u-mlabel">Base Unit</label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;font-weight:700;color:#374151;">1</span>
                        <select class="conv-select" id="conv-base"></select>
                    </div>
                </div>
                <div style="display:flex; align-items:center; padding-bottom:11px;">
                    <span class="conv-eq">=</span>
                </div>
                <div style="width:80px;">
                    <label class="u-mlabel">Rate</label>
                    <input type="number" class="conv-rate" id="conv-rate-input" value="0" min="0" style="width:100%;"/>
                </div>
                <div style="flex:1;">
                    <label class="u-mlabel">Secondary Unit</label>
                    <select class="conv-select" id="conv-secondary"></select>
                </div>
            </div>
        </div>
        <div class="u-mfoot">
            <button class="u-mbtn u-mbtn-new" onclick="saveConversionAndNew()">SAVE & NEW</button>
            <button class="u-mbtn u-mbtn-save" onclick="saveConversion()">SAVE</button>
        </div>
    </div>
</div>

{{-- STOCK ADJUSTMENT MODAL --}}
<div id="adj-overlay" onclick="if(event.target.id==='adj-overlay')closeAdjModal()">
    <div id="adj-modal" onclick="event.stopPropagation()">
        <div class="adj-header">
            <span class="adj-title">Stock Adjustment</span>
            <div class="adj-toggle-row">
                <span class="adj-toggle-lbl" id="lbl-add-stock">Add Stock</span>
                <label class="adj-switch">
                    <input type="checkbox" id="adj-toggle" onchange="handleAdjToggle()">
                    <span class="adj-slider"></span>
                </label>
                <span class="adj-toggle-lbl inactive" id="lbl-reduce-stock">Reduce Stock</span>
            </div>
            <button class="adj-close" onclick="closeAdjModal()">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="adj-body">
            <div class="adj-item-name" id="adj-item-name">Item Name</div>
            <div class="adj-fields">
                <div class="adj-qty-wrap">
                    <input type="number" class="adj-input adj-qty-input" id="adj-qty" placeholder="Total Qty" min="0"/>
                    <select class="adj-unit-sel" id="adj-unit">
                        <option>Kg</option><option>Nos</option><option>Bag</option>
                        <option>Box</option><option>Ltr</option><option>Mtr</option><option>Pcs</option>
                    </select>
                </div>
                <input type="number" class="adj-input adj-price-input" id="adj-price" placeholder="At Price" min="0" step="0.01"/>
                <input type="text" class="adj-input adj-details-input" id="adj-details" placeholder="Details"/>
                <div class="adj-date-wrap">
                    <span class="adj-date-label">Adjustment Date</span>
                    <input type="date" class="adj-input adj-date-input" id="adj-date"/>
                </div>
            </div>
        </div>
        <div class="adj-footer">
            <button class="adj-cancel" onclick="closeAdjModal()">Cancel</button>
            <button class="adj-save" onclick="saveAdjustment()">Save</button>
        </div>
    </div>
</div>
{{-- DELETE CONFIRMATION MODAL --}}
<div id="delete-overlay">
    <div id="delete-modal" onclick="event.stopPropagation()">
        <div class="del-header">
            <span class="del-header-title">Are you sure you want to delete this Item?</span>
            <button class="del-header-close" onclick="closeDeleteModal()">✕</button>
        </div>
        <div class="del-body">
            <p>This Item will be Deleted.</p>
        </div>
        <div class="del-footer">
            <button class="del-btn-yes" onclick="confirmDelete()">YES</button>
            <button class="del-btn-no" onclick="closeDeleteModal()">NO</button>
        </div>
    </div>
</div>

@include('dashboard.partials.transaction-passcode-guard')

@endsection

@push('scripts')
<script>
/* ── State ── */
let allItems     = @json($products ?? []);
let storedUnits  = @json($units ?? []);
let transactions = {};
let selectedIdx  = null;
let sortAsc      = true;
let bulkModalType = null;
let pendingBulkSelection = [];

/* ── Sort state for transactions table ── */
let txnSortCol = null;
let txnSortAsc = true;

/* ══════════════════════════════════════════
   COLUMN RESIZE
══════════════════════════════════════════ */
(function initColResize() {
    let isResizing = false, startX = 0, startW = 0, th = null, handle = null;
    document.addEventListener('mousedown', function(e) {
        if (!e.target.classList.contains('col-resize-handle')) return;
        e.preventDefault();
        handle = e.target; th = handle.closest('th');
        isResizing = true; startX = e.clientX; startW = th.offsetWidth;
        handle.classList.add('resizing');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    });
    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        const newW = Math.max(60, startW + (e.clientX - startX));
        th.style.width = newW + 'px'; th.style.minWidth = newW + 'px';
    });
    document.addEventListener('mouseup', function() {
        if (!isResizing) return;
        isResizing = false;
        if (handle) handle.classList.remove('resizing');
        document.body.style.cursor = ''; document.body.style.userSelect = '';
        handle = null; th = null;
    });
})();

/* ── Sort arrow helper ── */
function updateSortArrows(col, asc) {
    document.querySelectorAll('#txn-thead-row th').forEach(th => th.classList.remove('sort-asc','sort-desc'));
    if (!col) return;
    const th = document.querySelector(`#txn-thead-row th[data-col="${col}"]`);
    if (th) th.classList.add(asc ? 'sort-asc' : 'sort-desc');
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', () => {
    renderList();
    ensureValidSelection();
    const d = new Date();
    const adjDate = document.getElementById('adj-date');
    if (adjDate) adjDate.value = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    document.addEventListener('click', () => {
        closeAddDD(); closeBulkDD(); closeFilterDD(); closeSharePopup();
        closeAllColFilters();
        document.querySelectorAll('.il-row-menu.open, .il-item-dd.open').forEach(m => m.classList.remove('open'));
    });
});

/* ── Toast ── */
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

/* ── Share popup ── */
function toggleSharePopup(e) { e.stopPropagation(); document.getElementById('share-popup').classList.toggle('open'); }
function closeSharePopup() { document.getElementById('share-popup')?.classList.remove('open'); }
function shareVia(method) {
    closeSharePopup();
    const name = allItems[selectedIdx]?.name || 'item';
    if (method === 'copy') { navigator.clipboard.writeText(window.location.href).then(() => showToast('Link copied!')); }
    else if (method === 'email') { window.open(`mailto:?subject=Item: ${name}&body=View item: ${window.location.href}`); }
    else if (method === 'whatsapp') { window.open(`https://wa.me/?text=Item: ${name} - ${window.location.href}`); }
    else if (method === 'sms') { window.open(`sms:?body=Item: ${name}`); }
}

/* ── Main tab switch (DEPRECATED - No longer used) ── */
// This function is no longer used. Tabs now use direct navigation with route helpers.
// Keeping this comment to avoid breaking any references, but the function is empty.

/* ── Search ── */
function toggleSearch() {
    const w = document.getElementById('search-wrap');
    w.classList.toggle('open');
    if (w.classList.contains('open')) document.getElementById('search-input').focus();
}
function filterItems() {
    renderList();
}

/* ── Render list ── */
function renderList(items = getFilteredItems()) {
    const c = document.getElementById('items-list');
    if (!c) return;
    if (!items.length) {
        c.innerHTML = `<div style="padding:32px 16px;text-align:center;color:#9ca3af;font-size:13px;">No items found</div>`;
        syncSelectionWithVisibleItems(items);
        return;
    }
    c.innerHTML = items.map(({ item, index }) => `
        <div class="il-item-row ${selectedIdx === index ? 'active' : ''}" onclick="selectItem(${index})">
            <span class="il-item-dot"></span>
            <span class="il-item-name">${esc(item.name)}</span>
            <span class="il-item-qty ${stockSignClass(getTotalQty(index))}">${formatSignedStock(getTotalQty(index))}</span>
            <span class="il-item-netw ${stockSignClass(parseFloat(item.total_net_w || 0))}">${parseFloat(item.total_net_w || 0).toFixed(2)}</span>
            <div class="il-item-more-wrap" onclick="event.stopPropagation()">
                <button class="il-item-more-btn" onclick="toggleItemDD(event,${index})" title="Options">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                    </svg>
                </button>
                <div class="il-item-dd" id="item-dd-${index}">
                    <div class="il-item-dd-item" onclick="editItemNav(${index})">View/Edit</div>
                <div class="il-item-dd-item danger" onclick="deleteItemWithPasscode(${index})">Delete</div>
                </div>
            </div>
        </div>
    `).join('');
    syncSelectionWithVisibleItems(items);
}
function toggleItemDD(e, i) {
    e.stopPropagation();
    document.querySelectorAll('.il-item-dd.open').forEach(d => d.classList.remove('open'));
    document.getElementById(`item-dd-${i}`).classList.toggle('open');
}
function editItemNav(i) {
    const url = '{{ url('dashboard/items') }}/' + (allItems[i].id || i) + '/edit';
    if (window.requestTransactionPasscode) {
        window.requestTransactionPasscode(() => { window.location.href = url; });
        return;
    }
    window.location.href = url;
}
/* ── Delete modal state ── */
let deleteTargetIdx = null;

function deleteItem(i) {
    deleteTargetIdx = i;
    document.getElementById('delete-overlay').classList.add('open');
    // Close the item dropdown
    document.querySelectorAll('.il-item-dd.open').forEach(d => d.classList.remove('open'));
}

function closeDeleteModal() {
    document.getElementById('delete-overlay').classList.remove('open');
    deleteTargetIdx = null;
}

function confirmDelete() {
    const i = deleteTargetIdx;
    if (i === null) return;
    closeDeleteModal();

    const item = allItems[i];
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken) { showToast('CSRF token missing.'); return; }

    // Use POST with _method=DELETE for broader server compatibility
    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', csrfToken);

    fetch(`{{ url('dashboard/items') }}/${item.id}`, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(async r => {
        if (r.ok) {
            allItems.splice(i, 1);
            delete transactions[i];
            selectedIdx = null;
            document.getElementById('no-selection').style.display = 'flex';
            document.getElementById('item-detail').style.display  = 'none';
            renderList();
            ensureValidSelection();
            showToast('Item deleted successfully');
        } else {
            // Show the actual error message from server if available
            let msg = 'Failed to delete item';
            try {
                const data = await r.json();
                if (data.message) msg = data.message;
            } catch(e) {}
            showToast(msg);
        }
    })
    .catch(() => showToast('Network error. Please try again.'));
}

function deleteItemWithPasscode(i) {
    if (window.requestTransactionPasscode) {
        window.requestTransactionPasscode(() => {
            deleteTargetIdx = i;
            document.getElementById('delete-overlay')?.classList.add('open');
            document.querySelectorAll('.il-item-dd.open').forEach(d => d.classList.remove('open'));
        });
        return;
    }
    deleteItem(i);
}

function getTotalQty(idx) {
    return parseFloat(allItems[idx]?.stock_qty ?? allItems[idx]?.opening_qty ?? 0);
}

function formatSignedStock(value) {
    const qty = parseFloat(value || 0) || 0;
    return qty > 0 ? `+${qty}` : `${qty}`;
}

function stockSignClass(value) {
    const qty = parseFloat(value || 0) || 0;
    return qty < 0 ? 'neg' : 'pos';
}

function getItemId(item, idx) {
    return String(item?.id ?? `idx-${idx}`);
}

function isItemInactive(item, idx) {
    return !(item?.is_active ?? true);
}

function setItemInactive(item, idx, inactive) {
    if (!allItems[idx]) return;
    allItems[idx].is_active = !inactive;
}

function getFilteredItems() {
    const q = (document.getElementById('search-input')?.value || '').toLowerCase();
    const showActive = document.getElementById('filter-active')?.checked ?? true;
    const showInactive = document.getElementById('filter-inactive')?.checked ?? false;

    return allItems
        .map((item, index) => ({ item, index }))
        .filter(({ item, index }) => {
            const inactive = isItemInactive(item, index);
            const nameMatch = (item.name || '').toLowerCase().includes(q);
            const statusMatch = (!inactive && showActive) || (inactive && showInactive);
            return nameMatch && statusMatch;
        });
}

function syncSelectionWithVisibleItems(visibleItems) {
    if (visibleItems.some(({ index }) => index === selectedIdx)) return;

    if (!visibleItems.length) {
        selectedIdx = null;
        document.getElementById('no-selection').style.display = 'flex';
        document.getElementById('item-detail').style.display  = 'none';
        return;
    }

    selectItem(visibleItems[0].index);
}

function ensureValidSelection() {
    const visibleItems = getFilteredItems();
    if (!visibleItems.length) {
        selectedIdx = null;
        document.getElementById('no-selection').style.display = 'flex';
        document.getElementById('item-detail').style.display  = 'none';
        return;
    }

    if (selectedIdx === null || !visibleItems.some(({ index }) => index === selectedIdx)) {
        selectItem(visibleItems[0].index);
    }
}

/* ── Select item ── */
function selectItem(idx) {
    selectedIdx = idx;
    const item = allItems[idx];
    renderList();
    document.getElementById('no-selection').style.display = 'none';
    const detail = document.getElementById('item-detail');
    detail.style.display = 'flex';
    document.getElementById('detail-name').textContent     = item.name;
    document.getElementById('detail-sale').textContent     = item.sale_price     ? 'Rs ' + parseFloat(item.sale_price).toFixed(2)     : '—';
    document.getElementById('detail-purchase').textContent = item.purchase_price ? 'Rs ' + parseFloat(item.purchase_price).toFixed(2) : '—';

    const stockQty = parseFloat(item.stock_qty ?? item.opening_qty ?? 0);
    document.getElementById('detail-stock-qty').textContent = formatSignedStock(stockQty);
    document.getElementById('detail-stock-qty').className = `il-stat-value ${stockSignClass(stockQty)}`;
    document.getElementById('detail-stock-val').textContent = 'Rs ' + (parseFloat(item.purchase_price || 0) * stockQty).toFixed(2);
    document.getElementById('detail-stock-val').className = `il-stat-value ${stockSignClass(stockQty)}`;

    // Show loading
    document.getElementById('txn-tbody').innerHTML = `
        <tr><td colspan="12" style="text-align:center;color:#9ca3af;padding:48px 0;font-size:13px;">Loading transactions...</td></tr>
    `;

    // Fetch fresh item data and transactions from server
    Promise.all([
        fetch(`/dashboard/items/${item.id}?details=true`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(r => r.json()),
        fetch(`/dashboard/items/${item.id}/transactions`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(r => r.json())
    ])
    .then(([freshItem, txnData]) => {
        // Update allItems with fresh data
        if (freshItem && freshItem.id) {
            allItems[idx] = { ...allItems[idx], ...freshItem };
            const updatedStockQty = parseFloat(freshItem.stock_qty ?? freshItem.opening_qty ?? 0);
            document.getElementById('detail-stock-qty').textContent = formatSignedStock(updatedStockQty);
            document.getElementById('detail-stock-qty').className = `il-stat-value ${stockSignClass(updatedStockQty)}`;
            document.getElementById('detail-stock-val').textContent = 'Rs ' + (parseFloat(freshItem.purchase_price || 0) * updatedStockQty).toFixed(2);
            document.getElementById('detail-stock-val').className = `il-stat-value ${stockSignClass(updatedStockQty)}`;
        }

        // Update transactions
        transactions[idx] = txnData;
        renderTxns(idx);
    })
    .catch(() => {
        document.getElementById('txn-tbody').innerHTML = `
            <tr><td colspan="12" style="text-align:center;color:#ef4444;padding:48px 0;font-size:13px;">Failed to load transactions.</td></tr>
        `;
    });
}

/* ── Transactions ── */
let selectedTxnIdx = null;

function renderTxns(idx) {
    const tbody = document.getElementById('txn-tbody');
    const txns  = transactions[idx] || [];
    if (!txns.length) {
        tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;color:#9ca3af;padding:48px 0;font-size:13px;">No transactions to show</td></tr>`;
        return;
    }
    const statusColor = { 'Paid': '#22c55e', 'Partial': '#f59e0b', 'Unpaid': '#ef4444' };
    tbody.innerHTML = txns.map((t, ti) => {
        const dotColor = t.isAdd ? '#22c55e' : '#ef4444';
        const status   = t.status || 'Unpaid';
        const color    = statusColor[status] || '#9ca3af';
        const amountColor = status === 'Paid' ? '#22c55e' : status === 'Unpaid' ? '#ef4444' : '#9ca3af';
        const unpaidColor = status === 'Unpaid' ? '#ef4444' : '#374151';
        return `
        <tr id="txn-row-${idx}-${ti}" onclick="openTxnAction(${idx},${ti},'edit')" style="cursor:pointer;user-select:none;">
            <td class="td-dot"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${dotColor};"></span></td>
            <td class="td-date">${esc(t.date)}</td>
            <td class="td-invoice">${esc(t.invoice ?? '')}</td>
            <td class="td-type">${esc(t.type)}</td>
            <td class="td-name">${esc(t.name ?? '')}</td>
            <td class="td-broker">${esc(t.broker ?? '')}</td>
            <td class="td-qty">${t.qty}</td>
            <td class="td-netw" style="color:${unpaidColor};">${parseFloat(t.net_w || 0).toFixed(2)} kg</td>
            <td class="td-price" style="color:${amountColor};">${t.amount !== undefined && t.amount !== null ? 'Rs ' + parseFloat(t.amount || 0).toFixed(2) : '—'}</td>
            <td class="td-price" style="color:${unpaidColor};">${t.price ? 'Rs ' + parseFloat(t.price).toFixed(2) : '—'}</td>
            <td class="td-status" style="font-weight:500;color:${color};">${esc(status)}</td>
            <td class="td-actions">
                <div class="il-row-menu-wrap">
                    <button class="il-row-menu-btn" onclick="toggleRowMenu(event,'row-menu-${idx}-${ti}')">⋮</button>
                    <div class="il-row-menu" id="row-menu-${idx}-${ti}">
                        ${buildTxnMenu(idx, ti, t)}
                    </div>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function selectTxnRow(idx, ti) {
    document.querySelectorAll('#txn-tbody tr').forEach(r => r.classList.remove('txn-selected'));
    const row = document.getElementById(`txn-row-${idx}-${ti}`);
    if (row) row.classList.add('txn-selected');
    selectedTxnIdx = ti;
}

function openAdjModalForTxn(idx, ti) {
    selectTxnRow(idx, ti);
    const t = transactions[idx][ti];
    document.getElementById('adj-item-name').textContent = allItems[idx].name;
    document.getElementById('adj-qty').value     = t.qty;
    document.getElementById('adj-price').value   = t.price || '';
    document.getElementById('adj-details').value = t.details || '';
    document.getElementById('adj-unit').value    = t.unit || 'Kg';
    document.getElementById('adj-toggle').checked = !t.isAdd;
    handleAdjToggle();
    if (t.date) {
        const parts = t.date.split('/');
        if (parts.length === 3) document.getElementById('adj-date').value = `${parts[2]}-${parts[1]}-${parts[0]}`;
    }
    const overlay = document.getElementById('adj-overlay');
    overlay.dataset.editIdx = idx; overlay.dataset.editTi = ti; overlay.dataset.isEdit = '1';
    overlay.classList.add('open');
}

function filterTxns(q) {
    const tbody = document.getElementById('txn-tbody');
    const txns  = (transactions[selectedIdx] || []).filter(t =>
        t.type.toLowerCase().includes(q.toLowerCase()) || (t.details || '').toLowerCase().includes(q.toLowerCase())
    );
    if (!txns.length) {
        tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;color:#9ca3af;padding:48px 0;font-size:13px;">No transactions found</td></tr>`;
        return;
    }
    const statusColor = { 'Paid': '#22c55e', 'Partial': '#f59e0b', 'Unpaid': '#ef4444' };
    tbody.innerHTML = txns.map((t, ti) => {
        const status   = t.status || 'Unpaid';
        const color    = statusColor[status] || '#9ca3af';
        const amountColor = status === 'Paid' ? '#22c55e' : status === 'Unpaid' ? '#ef4444' : '#9ca3af';
        const unpaidColor = status === 'Unpaid' ? '#ef4444' : '#374151';
        return `
        <tr onclick="openTxnAction(${selectedIdx},${ti},'edit')" style="cursor:pointer;user-select:none;">
            <td class="td-dot"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#111111;"></span></td>
            <td class="td-date">${esc(t.date)}</td><td class="td-invoice">${esc(t.invoice ?? '')}</td><td class="td-type">${esc(t.type)}</td><td class="td-name">${esc(t.name ?? '')}</td>
            <td class="td-broker">${esc(t.broker ?? '')}</td>
            <td class="td-qty">${t.qty}</td>
            <td class="td-netw" style="color:${unpaidColor};">${parseFloat(t.net_w || 0).toFixed(2)} kg</td>
            <td class="td-price" style="color:${amountColor};">${t.amount !== undefined && t.amount !== null ? 'Rs ' + parseFloat(t.amount || 0).toFixed(2) : '—'}</td>
            <td class="td-price" style="color:${unpaidColor};">${t.price ? 'Rs ' + parseFloat(t.price).toFixed(2) : '—'}</td>
            <td class="td-status" style="font-weight:500;color:${color};">${esc(status)}</td><td class="td-actions"></td>
        </tr>
    `; }).join('');
}

/* ── Column filter dropdowns ── */
function toggleColFilter(e, id) {
    e.stopPropagation();
    const rect = e.currentTarget.getBoundingClientRect();
    const dd   = document.getElementById(id);
    const isOpen = dd.classList.contains('open');
    closeAllColFilters();
    if (!isOpen) {
        dd.style.top = (rect.bottom + 6) + 'px'; dd.style.left = rect.left + 'px'; dd.style.right = 'auto';
        dd.classList.add('open');
        const ddRect = dd.getBoundingClientRect();
        if (ddRect.right > window.innerWidth - 8) dd.style.left = (window.innerWidth - ddRect.width - 8) + 'px';
    }
}
function closeAllColFilters() { document.querySelectorAll('.col-filter-dd.open').forEach(d => d.classList.remove('open')); }
function clearColFilter(id) {
    const dd = document.getElementById(id);
    dd.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
    dd.querySelectorAll('input[type=text], input[type=number], input[type=date]').forEach(i => i.value = '');
    dd.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    applyColFilters();
}
function applyColFilters() {
    if (selectedIdx === null) return;
    let txns = transactions[selectedIdx] || [];
    const typeChecked = [...document.querySelectorAll('#cf-type input[type=checkbox]:checked')].map(c => c.value);
    if (typeChecked.length) txns = txns.filter(t => typeChecked.includes(t.type));
    const invoiceOp = document.getElementById('cf-invoice-op')?.value;
    const invoiceVal = (document.getElementById('cf-invoice-val')?.value || '').toLowerCase();
    if (invoiceVal) txns = txns.filter(t => { const v = (t.invoice||'').toLowerCase(); return invoiceOp==='exact'?v===invoiceVal:v.includes(invoiceVal); });
    const nameOp = document.getElementById('cf-name-op')?.value;
    const nameVal = (document.getElementById('cf-name-val')?.value || '').toLowerCase();
    if (nameVal) txns = txns.filter(t => { const v = (t.details||'').toLowerCase(); return nameOp==='exact'?v===nameVal:v.includes(nameVal); });
    const dateOp = document.getElementById('cf-date-op')?.value;
    const dateVal = document.getElementById('cf-date-val')?.value;
    if (dateVal) txns = txns.filter(t => {
        if (!t.date) return false;
        const parts = t.date.split('/');
        const txnDate = parts.length===3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : t.date;
        if (dateOp==='before') return txnDate < dateVal;
        if (dateOp==='after')  return txnDate > dateVal;
        return txnDate === dateVal;
    });
    const qtyOp = document.getElementById('cf-qty-op')?.value;
    const qtyVal = document.getElementById('cf-qty-val')?.value;
    if (qtyVal !== '' && qtyVal !== undefined) { const n = parseFloat(qtyVal); if (!isNaN(n)) txns = txns.filter(t => { const v=parseFloat(t.qty); if(qtyOp==='lt')return v<n; if(qtyOp==='gt')return v>n; return v===n; }); }
    const priceOp = document.getElementById('cf-price-op')?.value;
    const priceVal = document.getElementById('cf-price-val')?.value;
    if (priceVal !== '' && priceVal !== undefined) { const n = parseFloat(priceVal); if (!isNaN(n)) txns = txns.filter(t => { const v=parseFloat(t.price||0); if(priceOp==='lt')return v<n; if(priceOp==='gt')return v>n; return v===n; }); }
    const brokerOp = document.getElementById('cf-broker-op')?.value;
    const brokerVal = (document.getElementById('cf-broker-val')?.value || '').toLowerCase();
    if (brokerVal) txns = txns.filter(t => { const v = (t.broker||'').toLowerCase(); return brokerOp==='exact'?v===brokerVal:v.includes(brokerVal); });
    const statusChecked = [...document.querySelectorAll('#cf-status input[type=checkbox]:checked')].map(c => c.value);
    if (statusChecked.length) txns = txns.filter(t => statusChecked.includes(t.status||''));
    renderFilteredTxns(txns);
}
function renderFilteredTxns(txns) {
    const idx = selectedIdx;
    const tbody = document.getElementById('txn-tbody');
    if (!txns.length) { tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;color:#9ca3af;padding:48px 0;font-size:13px;">No transactions found</td></tr>`; return; }
    tbody.innerHTML = txns.map((t, ti) => {
        const originalTi = getTransactionIndex(idx, t);
        return `
        <tr style="cursor:pointer;user-select:none;" onclick="openTxnAction(${idx},${originalTi},'edit')">
            <td class="td-dot"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#111111;"></span></td>
            <td class="td-date">${esc(t.date)}</td><td class="td-invoice">${esc(t.invoice ?? '')}</td><td class="td-type">${esc(t.type)}</td><td class="td-name">${esc(t.name ?? '')}</td>
            <td class="td-broker">${esc(t.broker ?? '')}</td>
            <td class="td-qty">${t.qty}</td>
            <td class="td-netw">${parseFloat(t.net_w || 0).toFixed(2)}</td>
            <td class="td-price">${t.amount !== undefined && t.amount !== null ? 'Rs ' + parseFloat(t.amount || 0).toFixed(2) : '—'}</td>
            <td class="td-price">${t.price ? 'Rs ' + parseFloat(t.price).toFixed(2) : '—'}</td>
            <td class="td-status">${esc(t.status || '')}</td>
            <td class="td-actions">
                <div class="il-row-menu-wrap">
                    <button class="il-row-menu-btn" onclick="toggleRowMenu(event,'row-menu-f-${ti}')">⋮</button>
                    <div class="il-row-menu" id="row-menu-f-${ti}">
                        ${buildTxnMenu(idx, originalTi, t)}
                    </div>
                </div>
            </td>
        </tr>
    `;
    }).join('');
}

/* ── Sort ── */
function sortTxnCol(col) {
    if (txnSortCol === col) { txnSortAsc = !txnSortAsc; } else { txnSortCol = col; txnSortAsc = true; }
    updateSortArrows(col, txnSortAsc);
    const txns = transactions[selectedIdx] || [];
    txns.sort((a, b) => {
        if (col==='qty')   return txnSortAsc ? parseFloat(a.qty)-parseFloat(b.qty) : parseFloat(b.qty)-parseFloat(a.qty);
        if (col==='net_w') return txnSortAsc ? parseFloat(a.net_w||0)-parseFloat(b.net_w||0) : parseFloat(b.net_w||0)-parseFloat(a.net_w||0);
        if (col==='price') return txnSortAsc ? parseFloat(a.price||0)-parseFloat(b.price||0) : parseFloat(b.price||0)-parseFloat(a.price||0);
        if (col==='amount') return txnSortAsc ? parseFloat(a.amount||0)-parseFloat(b.amount||0) : parseFloat(b.amount||0)-parseFloat(a.amount||0);
        const av = (col==='type'?a.type:col==='name'?a.details:col==='date'?a.date:col==='invoice'?a.invoice:col==='broker'?a.broker:a.status)||'';
        const bv = (col==='type'?b.type:col==='name'?b.details:col==='date'?b.date:col==='invoice'?b.invoice:col==='broker'?b.broker:b.status)||'';
        return txnSortAsc ? String(av).localeCompare(String(bv)) : String(bv).localeCompare(String(av));
    });
    renderTxns(selectedIdx);
}

/* ── Row menu ── */
function toggleRowMenu(e, id) {
    e.stopPropagation();
    const btn = e.currentTarget; const rect = btn.getBoundingClientRect();
    document.querySelectorAll('.il-row-menu.open').forEach(m => { if(m.id!==id) m.classList.remove('open'); });
    const menu = document.getElementById(id); const isOpen = menu.classList.contains('open');
    menu.classList.remove('open');
    if (!isOpen) {
        menu.style.visibility = 'hidden';
        menu.style.top = '0px';
        menu.style.left = '0px';
        menu.classList.add('open');
        requestAnimationFrame(() => {
            const mRect = menu.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
            const spaceBelow = viewportHeight - rect.bottom;
            const shouldOpenUp = spaceBelow < mRect.height + 12;
            const top = shouldOpenUp
                ? (rect.top + window.scrollY - mRect.height - 2)
                : (rect.bottom + window.scrollY + 2);
            let left = rect.right - mRect.width;
            if (left < 4) left = 4;
            menu.style.top = `${Math.max(4, top)}px`;
            menu.style.left = `${left}px`;
            menu.style.visibility = 'visible';
        });
    }
}
function deleteTxn(idx, ti) {
    const txn = getTxn(idx, ti);
    if (!txn) {
        showToast('Transaction not found.');
        return;
    }

    const links = getTxnActionLinks(txn);
    if (!links.delete) {
        showToast('Delete is not available for this transaction.');
        return;
    }

    const performDelete = () => {
        if (!confirm('Delete this transaction?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.getElementById('transactionPasscodeConfig')?.dataset?.csrfToken;
        if (!csrfToken) {
            showToast('CSRF token missing.');
            return;
        }

        const formData = new FormData();
        formData.append('_method', 'DELETE');

        fetch(links.delete, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Failed to delete transaction.');
            }

            transactions[idx].splice(ti, 1);
            selectItem(idx);
            renderList();
            showToast(data.message || 'Transaction deleted successfully.');
        })
        .catch(error => {
            showToast(error.message || 'Failed to delete transaction.');
        });
    };

    if (window.requestTransactionPasscode) {
        window.requestTransactionPasscode(performDelete);
    } else {
        performDelete();
    }
}
function getTransactionIndex(idx, txn) {
    return (transactions[idx] || []).findIndex(t =>
        String(t.id ?? '') === String(txn.id ?? '') &&
        String(t.invoice ?? '') === String(txn.invoice ?? '') &&
        String(t.date ?? '') === String(txn.date ?? '') &&
        String(t.qty ?? '') === String(txn.qty ?? '')
    );
}
function getTxn(idx, ti) {
    return transactions[idx]?.[ti] || null;
}
function getTxnActionLinks(txn) {
    if (!txn || !txn.id) return {};
    const base = `{{ url('dashboard') }}`;
    const type = String(txn.raw_type || '').toLowerCase();
    const id = encodeURIComponent(txn.id);
    const links = {
        edit: null,
        delete: null,
        cancel: null,
        duplicate: null,
        convert_return: null,
        pdf: null,
        preview: null,
        print: null,
        payment: null,
        payment_history: null,
        history: null
    };
    if (type === 'invoice' || type === 'pos') {
        links.edit = `${base}/sales/${id}/edit`;
        links.delete = `${base}/sales/${id}`;
        links.cancel = `${base}/sales/${id}/cancel`;
        links.duplicate = `${base}/sales/${id}/duplicate`;
        links.convert_return = `${base}/sale-return/create?sale_id=${id}`;
        links.pdf = `${base}/invoice/download-pdf?sale_id=${id}`;
        links.preview = `${base}/sales/${id}/invoice-preview`;
        links.print = `${base}/sales/${id}/invoice-preview?print=1`;
        links.payment = `${base}/payment-in?sale_id=${id}`;
        links.payment_history = `${base}/sales/${id}/payment-history`;
        links.history = `${base}/sales/${id}/bank-history`;
        return links;
    }
    if (type === 'estimate') {
        links.edit = `${base}/estimates/${id}/edit`;
        links.delete = `${base}/estimates/${id}`;
        links.duplicate = `${base}/estimates/${id}/convert-to-sale`;
        links.pdf = `${base}/invoice/download-pdf?sale_id=${id}`;
        links.preview = `${base}/sales/${id}/invoice-preview`;
        links.print = `${base}/sales/${id}/invoice-preview?print=1`;
        return links;
    }
    if (type === 'proforma') {
        links.edit = `${base}/proforma-invoice/${id}/edit`;
        links.delete = `${base}/proforma-invoice/${id}`;
        links.duplicate = `${base}/proforma-invoice/${id}/convert-to-sale`;
        links.pdf = `${base}/invoice/download-pdf?sale_id=${id}`;
        links.preview = `${base}/sales/${id}/invoice-preview`;
        links.print = `${base}/sales/${id}/invoice-preview?print=1`;
        return links;
    }
    if (type === 'sale_return') {
        links.edit = `${base}/sale-return/${id}/edit`;
        links.delete = `${base}/sale-return/${id}`;
        links.duplicate = `${base}/sale-return/${id}/duplicate`;
        links.pdf = `${base}/invoice/download-pdf?sale_id=${id}`;
        links.preview = `${base}/sales/${id}/invoice-preview`;
        links.print = `${base}/sales/${id}/invoice-preview?print=1`;
        return links;
    }
    if (type === 'delivery_challan') {
        links.edit = `${base}/delivery-challan/${id}/edit`;
        links.delete = `${base}/delivery-challan/${id}`;
        links.duplicate = `${base}/delivery-challans/${id}/convert-to-sale`;
        links.pdf = `${base}/invoice/download-pdf?sale_id=${id}&doc=delivery_challan`;
        links.preview = `${base}/sales/${id}/invoice-preview?doc=delivery_challan`;
        links.print = `${base}/sales/${id}/invoice-preview?doc=delivery_challan&print=1`;
        return links;
    }
    if (type === 'sale_order') {
        links.duplicate = `${base}/sale-orders/${id}/convert-to-sale`;
        links.pdf = `${base}/invoice/download-pdf?sale_id=${id}`;
        links.preview = `${base}/sales/${id}/invoice-preview`;
        links.print = `${base}/sales/${id}/invoice-preview?print=1`;
        return links;
    }
    return links;
}
function buildTxnMenu(idx, ti, txn) {
    const links = getTxnActionLinks(txn);
    const items = [];
    if (links.edit) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'edit')">View/Edit</div>`);
    if (links.cancel) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'cancel')">Cancel Invoice</div>`);
    if (links.delete) items.push(`<div class="il-row-menu-item danger" onclick="event.stopPropagation(); deleteTxn(${idx},${ti})">Delete</div>`);
    if (links.duplicate) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'duplicate')">Duplicate</div>`);
    if (links.pdf) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'pdf')">Open PDF</div>`);
    if (links.preview) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'preview')">Preview</div>`);
    if (links.print) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'print')">Print</div>`);
    if (links.convert_return) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'convert_return')">Convert To Return</div>`);
    if (links.payment_history) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'payment_history')">Payment History</div>`);
    if (links.history) items.push(`<div class="il-row-menu-item" onclick="event.stopPropagation(); openTxnAction(${idx},${ti},'history')">View History</div>`);
    return items.join('');
}
function openUrlInNewTab(url) {
    const win = window.open(url, '_blank', 'noopener');
    if (!win) showToast('Please allow popups for this action.');
}
const itemTxnPreviewModalEl = document.getElementById('itemTxnPreviewModal');
const itemTxnPreviewModal = itemTxnPreviewModalEl ? bootstrap.Modal.getOrCreateInstance(itemTxnPreviewModalEl) : null;
const itemTxnPreviewFrame = document.getElementById('itemTxnPreviewFrame');
const itemTxnPreviewModalTitle = document.getElementById('itemTxnPreviewModalTitle');
const itemTxnPreviewOpenPdfBtn = document.getElementById('itemTxnPreviewOpenPdf');
const itemTxnPreviewPrintBtn = document.getElementById('itemTxnPreviewPrint');
const itemTxnPreviewSavePdfBtn = document.getElementById('itemTxnPreviewSavePdf');
const itemTxnPreviewEmailPdfBtn = document.getElementById('itemTxnPreviewEmailPdf');
const itemTxnHistoryModalEl = document.getElementById('itemTxnHistoryModal');
const itemTxnHistoryModal = itemTxnHistoryModalEl ? bootstrap.Modal.getOrCreateInstance(itemTxnHistoryModalEl) : null;
const itemTxnHistoryModalTitle = document.getElementById('itemTxnHistoryModalTitle');
const itemTxnHistoryModalBody = document.getElementById('itemTxnHistoryModalBody');

function openItemTxnPreview(url, title, options = {}) {
    if (!url) {
        showToast('Preview is not available for this transaction.');
        return;
    }

    if (!itemTxnPreviewModal || !itemTxnPreviewFrame) {
        openUrlInNewTab(options.pdfUrl || url);
        return;
    }

    itemTxnPreviewModalTitle.textContent = title || 'Preview';
    itemTxnPreviewFrame.src = url;
    itemTxnPreviewFrame.dataset.pdfUrl = options.pdfUrl || url;
    itemTxnPreviewFrame.dataset.printUrl = options.printUrl || '';
    itemTxnPreviewFrame.dataset.downloadUrl = options.downloadUrl || options.pdfUrl || url;
    itemTxnPreviewModal.show();
}

itemTxnPreviewOpenPdfBtn?.addEventListener('click', function () {
    const pdfUrl = itemTxnPreviewFrame?.dataset?.pdfUrl || itemTxnPreviewFrame?.src;
    if (!pdfUrl) return showToast('PDF is not available for this transaction.');
    openUrlInNewTab(pdfUrl);
});

itemTxnPreviewPrintBtn?.addEventListener('click', function () {
    const printUrl = itemTxnPreviewFrame?.dataset?.printUrl || itemTxnPreviewFrame?.dataset?.pdfUrl || itemTxnPreviewFrame?.src;
    if (!printUrl) return showToast('Print is not available for this transaction.');
    openUrlInNewTab(printUrl);
});

itemTxnPreviewSavePdfBtn?.addEventListener('click', function () {
    const downloadUrl = itemTxnPreviewFrame?.dataset?.downloadUrl || itemTxnPreviewFrame?.dataset?.pdfUrl || itemTxnPreviewFrame?.src;
    if (!downloadUrl) return showToast('PDF is not available for this transaction.');
    const a = document.createElement('a');
    a.href = downloadUrl;
    a.target = '_blank';
    a.rel = 'noopener';
    document.body.appendChild(a);
    a.click();
    a.remove();
});

itemTxnPreviewEmailPdfBtn?.addEventListener('click', function () {
    const downloadUrl = itemTxnPreviewFrame?.dataset?.downloadUrl || itemTxnPreviewFrame?.dataset?.pdfUrl || itemTxnPreviewFrame?.src;
    if (!downloadUrl) return showToast('PDF is not available for this transaction.');
    const subject = document.getElementById('txnTitle')?.textContent?.trim() || 'Transaction PDF';
    const body = `Please find the PDF here: ${downloadUrl}`;
    const mailtoUrl = 'mailto:?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
    try {
      const opened = window.open(mailtoUrl, '_self');
      if (opened !== null) return;
    } catch (error) {
      // fallback to anchor click below
    }
    const link = document.createElement('a');
    link.href = mailtoUrl;
    link.target = '_self';
    link.rel = 'noopener';
    document.body.appendChild(link);
    link.click();
    link.remove();
});

itemTxnPreviewModalEl?.addEventListener('hidden.bs.modal', function () {
    if (itemTxnPreviewFrame) {
        itemTxnPreviewFrame.src = 'about:blank';
        delete itemTxnPreviewFrame.dataset.pdfUrl;
        delete itemTxnPreviewFrame.dataset.printUrl;
        delete itemTxnPreviewFrame.dataset.downloadUrl;
    }
});

function renderTxnHistoryPlaceholder(message) {
    return `
        <div class="d-flex flex-column align-items-center justify-content-center text-center py-5 text-muted" style="min-height:40vh;">
            <div style="font-size:56px;opacity:.15;line-height:1;">&#128196;</div>
            <div class="mt-3" style="font-size:26px;opacity:.18;">&#128196;&#128196;</div>
            <p class="mt-4 mb-0 fw-semibold" style="font-size:15px;color:#8b93a7;">${message}</p>
        </div>
    `;
}

function openTxnHistoryModal(url, title, mode = 'history') {
    if (!url || !itemTxnHistoryModal || !itemTxnHistoryModalBody) {
        return showToast('History is not available for this transaction.');
    }
    itemTxnHistoryModalTitle.textContent = title;
    itemTxnHistoryModalBody.innerHTML = `<div class="text-center text-muted py-5">Loading...</div>`;
    itemTxnHistoryModal.show();
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(async response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (mode === 'payment' && Array.isArray(data.payments)) {
                if (!data.payments.length) {
                    itemTxnHistoryModalBody.innerHTML = renderTxnHistoryPlaceholder('No payment history found for this transaction.');
                    return;
                }
                const paymentRows = data.payments.map((payment, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${esc(payment.date || '-')}</td>
                        <td>${esc(payment.payment_type || '-')}</td>
                        <td>${esc(payment.bank_name || '-')}</td>
                        <td>${esc(payment.reference || '-')}</td>
                        <td class="text-end">Rs ${parseFloat(payment.amount || 0).toFixed(2)}</td>
                    </tr>
                `).join('');
                itemTxnHistoryModalBody.innerHTML = `
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><div class="border rounded-3 p-3"><div class="text-muted small">Invoice No.</div><div class="fw-semibold">${esc(data.bill_number || '-')}</div></div></div>
                        <div class="col-md-4"><div class="border rounded-3 p-3"><div class="text-muted small">Grand Total</div><div class="fw-semibold">Rs ${parseFloat(data.grand_total || 0).toFixed(2)}</div></div></div>
                        <div class="col-md-4"><div class="border rounded-3 p-3"><div class="text-muted small">Received / Balance</div><div class="fw-semibold">Rs ${parseFloat(data.received_amount || 0).toFixed(2)} / Rs ${parseFloat(data.balance || 0).toFixed(2)}</div></div></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>#</th><th>Date</th><th>Payment Type</th><th>Bank</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>${paymentRows}</tbody>
                        </table>
                    </div>
                `;
                return;
            }
            if (Array.isArray(data.entries)) {
                if (!data.entries.length) {
                    itemTxnHistoryModalBody.innerHTML = renderTxnHistoryPlaceholder('No history found for this transaction.');
                    return;
                }
                const entryRows = data.entries.map((entry, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${esc(entry.date || '-')}</td>
                        <td>${esc(entry.type || '-')}</td>
                        <td>${esc(entry.bank_name || '-')}</td>
                        <td>${esc(entry.reference || '-')}</td>
                        <td class="text-end">Rs ${parseFloat(entry.amount || 0).toFixed(2)}</td>
                    </tr>
                `).join('');
                itemTxnHistoryModalBody.innerHTML = `
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Bank</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>${entryRows}</tbody>
                        </table>
                    </div>
                `;
                return;
            }
            itemTxnHistoryModalBody.innerHTML = renderTxnHistoryPlaceholder('No edits have been made to this transaction.');
        })
        .catch(() => {
            itemTxnHistoryModalBody.innerHTML = renderTxnHistoryPlaceholder('Unable to load history right now.');
        });
}

async function cancelTxn(url, label) {
    if (!url) return showToast('Cancel is not available for this transaction.');
    if (!confirm(`Cancel ${label || 'this invoice'}?`)) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) return showToast('CSRF token missing.');
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    }).catch(() => null);
    if (!response) return showToast('Network error while cancelling transaction.');
    if (response.ok) {
        showToast('Invoice cancelled successfully.');
        window.location.reload();
        return;
    }
    showToast('Unable to cancel this invoice.');
}

function openPrintView(url) {
    const existingFrame = document.getElementById('txn-print-frame');
    if (existingFrame) existingFrame.remove();

    const frame = document.createElement('iframe');
    frame.id = 'txn-print-frame';
    frame.style.position = 'fixed';
    frame.style.right = '0';
    frame.style.bottom = '0';
    frame.style.width = '0';
    frame.style.height = '0';
    frame.style.border = '0';
    frame.style.visibility = 'hidden';

    frame.onload = () => {
        const doPrint = () => {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (error) {
                showToast('Unable to open print dialog.');
            }
        };

        setTimeout(doPrint, 400);
        setTimeout(doPrint, 1200);
    };

    frame.src = url;
    document.body.appendChild(frame);
}
function openTxnAction(idx, ti, action) {
    const txn = getTxn(idx, ti);
    if (!txn) {
        showToast('Transaction not found.');
        return;
    }
    document.querySelectorAll('.il-row-menu.open').forEach(menu => menu.classList.remove('open'));
    const links = getTxnActionLinks(txn);
    const url = links[action];
    if (!url) {
        showToast('This action is not available for this transaction.');
        return;
    }
    if (action === 'cancel') {
        cancelTxn(url, txn.invoice || txn.id);
        return;
    }
    if (action === 'payment_history') {
        openTxnHistoryModal(url, `Payment History - ${txn.invoice || txn.id}`, 'payment');
        return;
    }
    if (action === 'history') {
        openTxnHistoryModal(url, `View History - ${txn.invoice || txn.id}`, 'history');
        return;
    }
    if (action === 'edit') {
        if (window.requestTransactionPasscode) {
            window.requestTransactionPasscode(() => { window.location.href = url; });
        } else {
            window.location.href = url;
        }
        return;
    }
    if (action === 'payment' || action === 'duplicate' || action === 'convert_return') {
        window.location.href = url;
        return;
    }
    if (action === 'pdf') {
        openUrlInNewTab(url);
        return;
    }
    if (action === 'preview') {
        openItemTxnPreview(url, `Preview - ${txn.invoice || txn.id}`, {
            pdfUrl: links.pdf,
            printUrl: links.print,
            downloadUrl: links.pdf ? links.pdf + (String(links.pdf).includes('?') ? '&' : '?') + 'download=1' : ''
        });
        return;
    }
    if (action === 'print') {
        openUrlInNewTab(url);
        return;
    }
    openUrlInNewTab(url);
}
function viewHistory(idx, ti) {
    openTxnAction(idx, ti, 'history');
}

/* ── Dropdowns ── */
function toggleAddDD(e) { e.stopPropagation(); document.getElementById('add-dd').classList.toggle('open'); closeBulkDD(); }
function closeAddDD()   { document.getElementById('add-dd')?.classList.remove('open'); }
function toggleBulkDD(e) { e.stopPropagation(); document.getElementById('bulk-dd').classList.toggle('open'); closeAddDD(); }
function closeBulkDD()   { document.getElementById('bulk-dd')?.classList.remove('open'); }
const bulkConfig = {
    'bulk-inactive': { title: 'Bulk Inactive', btnLabel: 'Mark as Inactive', info: 'Showing only active items' },
    'bulk-active': { title: 'Bulk Active', btnLabel: 'Mark as Active', info: 'Showing only inactive items' },
    'bulk-update': { title: 'Bulk Update Items', btnLabel: 'Save Changes', info: '' },
    'bulk-assign-unit': { title: 'Assign Unit', btnLabel: 'Next', info: 'Showing all items' },
    'bulk-assign-code': { title: 'Bulk Assign Code', btnLabel: 'Next', info: "Showing items that don't have item code" }
};
function bulkAction(action) {
    closeBulkDD();
    if (bulkConfig[action]) {
        openBulkModal(action);
        return;
    }
    alert('Bulk action: ' + action);
}
function toggleFilterDD(e) { e.stopPropagation(); document.getElementById('filter-dd').classList.toggle('open'); }
function closeFilterDD()   { document.getElementById('filter-dd')?.classList.remove('open'); }
function applyFilter()     { renderList(); }
function clearFilter()     {
    document.getElementById('filter-active').checked   = true;
    document.getElementById('filter-inactive').checked = false;
    renderList();
}
function sortByQty() {
    const arrow = document.getElementById('qty-sort-arrow');
    sortAsc = !sortAsc;
   allItems.sort((a, b) => sortAsc ? parseFloat(a.stock_qty||0)-parseFloat(b.stock_qty||0) : parseFloat(b.stock_qty||0)-parseFloat(a.stock_qty||0));
    if (arrow) arrow.textContent = sortAsc ? '↑' : '↓';
    renderList();
}
function goToAddItem() { window.location.href = '{{ route('items.create') }}'; }

function getBulkItems() {
    return allItems
        .map((item, index) => ({ item, index }))
        .filter(({ item, index }) => {
            if (bulkModalType === 'bulk-inactive') return !isItemInactive(item, index);
            if (bulkModalType === 'bulk-active') return isItemInactive(item, index);
            if (bulkModalType === 'bulk-assign-code') return !(item.item_code || '').trim();
            return true;
        });
}

function openBulkModal(type) {
    bulkModalType = type;
    const cfg = bulkConfig[type] || { title: 'Bulk Action', btnLabel: 'Apply', info: 'Showing all items' };
    document.getElementById('bulk-modal-title').textContent = cfg.title;
    document.getElementById('bulk-action-btn').textContent  = cfg.btnLabel;

    // Show appropriate view
    const statusView = document.getElementById('bulk-status-view');
    const updateView = document.getElementById('bulk-update-view');

    if (type === 'bulk-update') {
        statusView.style.display = 'none';
        updateView.style.display = 'block';
        renderBulkEditRows();
    } else {
        statusView.style.display = 'block';
        updateView.style.display = 'none';
        document.getElementById('bulk-info-text').textContent = cfg.info;
        document.getElementById('bulk-search').value = '';
        document.getElementById('bulk-check-all').checked = false;
        renderBulkRows();
    }

    document.getElementById('bulk-overlay').classList.add('open');
}

function closeBulkModal(resetSelection = true) {
    document.getElementById('bulk-overlay').classList.remove('open');
    bulkModalType = null;
    if (resetSelection) {
        pendingBulkSelection = [];
    }
}

function renderBulkRows() {
    const tbody = document.getElementById('bulk-tbody');
    if (!tbody) return;

    const search = (document.getElementById('bulk-search')?.value || '').toLowerCase();
    const rows = getBulkItems().filter(({ item }) => (item.name || '').toLowerCase().includes(search));

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="bulk-empty">No items to show</td></tr>`;
        document.getElementById('bulk-check-all').checked = false;
        return;
    }

    tbody.innerHTML = rows.map(({ item, index }) => `
        <tr>
            <td style="width:44px;padding:10px 16px;">
                <input type="checkbox" data-idx="${index}" style="width:15px;height:15px;accent-color:#2563eb;">
            </td>
            <td style="font-size:14px;color:#111827;padding:10px 16px;">${esc(item.name)}</td>
            <td style="width:120px;text-align:right;font-size:14px;color:#16a34a;padding:10px 16px;">${getTotalQty(index)}</td>
        </tr>
    `).join('');
}

function toggleAllBulk(el) {
    document.querySelectorAll('#bulk-tbody input[type=checkbox]').forEach(cb => cb.checked = el.checked);
}

function applyBulkAction() {
    if (bulkModalType === 'bulk-update') {
        applyBulkUpdate();
        return;
    }

    const selectedIndexes = [...document.querySelectorAll('#bulk-tbody input[type=checkbox]:checked')]
        .map(cb => Number(cb.dataset.idx))
        .filter(idx => !Number.isNaN(idx));

    if (!selectedIndexes.length) {
        showToast('Please select at least one item.');
        return;
    }

    if (bulkModalType === 'bulk-assign-unit' || bulkModalType === 'bulk-assign-code') {
        pendingBulkSelection = selectedIndexes;
        const nextType = bulkModalType;
        closeBulkModal(false);
        if (nextType === 'bulk-assign-unit') {
            openAssignUnitModal();
        } else {
            openAssignCodeModal();
        }
        return;
    }

    if (bulkModalType !== 'bulk-inactive' && bulkModalType !== 'bulk-active') {
        showToast('This bulk action is not available yet.');
        return;
    }

    const makeInactive = bulkModalType === 'bulk-inactive';
    const itemIds = selectedIndexes
        .map(idx => allItems[idx]?.id)
        .filter(id => !!id);

    if (!itemIds.length) {
        showToast('Selected items are missing IDs.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        showToast('CSRF token missing.');
        return;
    }

    fetch(`{{ route('items.bulk-status') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            item_ids: itemIds,
            is_active: !makeInactive
        })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Failed to update item status.');
        }

        selectedIndexes.forEach(idx => setItemInactive(allItems[idx], idx, makeInactive));
        renderBulkRows();
        renderList();
        ensureValidSelection();
        showToast(data.message || (makeInactive ? 'Selected items marked inactive.' : 'Selected items marked active.'));
    })
    .catch(error => {
        showToast(error.message || 'Failed to update item status.');
    });
}

function populateUnitSelectOptions() {
    const baseSelect = document.getElementById('conv-base');
    const secondarySelect = document.getElementById('conv-secondary');
    if (!baseSelect || !secondarySelect) return;

    const options = storedUnits.map(unit => {
        const value = esc(unit.short_name || unit.name || '');
        const label = esc(unit.name || unit.short_name || '');
        return `<option value="${value}">${label}</option>`;
    }).join('');

    baseSelect.innerHTML = `<option value="">Select unit</option>${options}`;
    secondarySelect.innerHTML = `<option value="">Select unit</option>${options}`;
}

function openAssignUnitModal() {
    if (!pendingBulkSelection.length) {
        showToast('Please select at least one item.');
        return;
    }
    populateUnitSelectOptions();
    document.getElementById('conv-rate-input').value = '0';
    document.getElementById('assign-unit-selected-text').textContent = `Selected items: ${pendingBulkSelection.length}`;
    document.getElementById('add-conv-overlay').classList.add('open');
}

function closeAddConversion() {
    document.getElementById('add-conv-overlay')?.classList.remove('open');
}

function saveConversionAndNew() {
    saveAssignedUnit(false);
}

function saveConversion() {
    saveAssignedUnit(true);
}

function saveAssignedUnit(closeAfterSave = true) {
    const baseUnit = document.getElementById('conv-base')?.value || '';
    const secondaryUnit = document.getElementById('conv-secondary')?.value || '';
    const rate = document.getElementById('conv-rate-input')?.value || '0';

    if (!baseUnit) {
        showToast('Please select base unit.');
        return;
    }

    const updates = {};
    pendingBulkSelection.forEach((idx) => {
        const item = allItems[idx];
        if (!item?.id) return;
        updates[item.id] = {
            unit: baseUnit,
            secondary_unit: secondaryUnit || null,
            unit_conversion_rate: rate || 0
        };
    });

    submitBulkItemUpdates(updates, 'Units assigned successfully!', () => {
        if (closeAfterSave) {
            closeAddConversion();
            pendingBulkSelection = [];
        } else {
            document.getElementById('conv-secondary').value = '';
            document.getElementById('conv-rate-input').value = '0';
        }
    });
}

function openAssignCodeModal() {
    if (!pendingBulkSelection.length) {
        showToast('Please select at least one item.');
        return;
    }
    document.getElementById('assign-code-search').value = '';
    renderAssignCodeRows();
    document.getElementById('assign-code-overlay').classList.add('open');
}

function closeAssignCodeModal() {
    document.getElementById('assign-code-overlay')?.classList.remove('open');
}

function renderAssignCodeRows() {
    const body = document.getElementById('assign-code-body');
    if (!body) return;

    const keyword = (document.getElementById('assign-code-search')?.value || '').toLowerCase();
    const rows = pendingBulkSelection
        .map(idx => ({ idx, item: allItems[idx] }))
        .filter(({ item }) => item && (item.name || '').toLowerCase().includes(keyword));

    if (!rows.length) {
        body.innerHTML = `<div style="text-align:center;padding:54px 20px;color:#9ca3af;font-size:14px;">No Items to Show</div>`;
        return;
    }

    body.innerHTML = rows.map(({ idx, item }) => `
        <div class="bulk-row-editor">
            <input type="text" class="bulk-edit-field bulk-col-item" value="${esc(item.name || '')}" disabled />
            <input type="text" class="bulk-edit-field" placeholder="Enter item code" data-assign-code-id="${item.id}" data-assign-code-idx="${idx}" value="${esc(item.item_code || '')}" />
        </div>
    `).join('');
}

function saveAssignedCodes() {
    const updates = {};
    const inputs = Array.from(document.querySelectorAll('[data-assign-code-id]'));

    inputs.forEach((input) => {
        const id = input.dataset.assignCodeId;
        const value = input.value.trim();
        if (!id || !value) return;
        updates[id] = { item_code: value };
    });

    if (!Object.keys(updates).length) {
        showToast('Please enter at least one item code.');
        return;
    }

    submitBulkItemUpdates(updates, 'Item codes assigned successfully!', () => {
        closeAssignCodeModal();
        pendingBulkSelection = [];
    });
}

function submitBulkItemUpdates(updates, successMessage, onDone) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) { showToast('CSRF token missing.'); return; }

    fetch(`{{ route('items.bulk-update') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ updates })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            throw new Error(data.message || 'Failed to update items.');
        }
        Object.entries(updates).forEach(([itemId, fields]) => {
            const idx = allItems.findIndex(entry => String(entry.id) === String(itemId));
            if (idx >= 0) {
                allItems[idx] = { ...allItems[idx], ...fields };
            }
        });
        renderList();
        ensureValidSelection();
        if (typeof onDone === 'function') onDone();
        showToast(successMessage);
    })
    .catch(error => showToast(error.message || 'Failed to update items.'));
}

function renderBulkEditRows() {
    const tbody = document.getElementById('bulk-edit-tbody');
    if (!tbody) return;

    const search = (document.getElementById('bulk-update-search')?.value || '').toLowerCase();
    const rows = allItems.map((item, index) => ({ item, index }))
        .filter(({ item }) => (item.name || '').toLowerCase().includes(search));

    if (!rows.length) {
        tbody.innerHTML = `<div style="text-align:center;padding:46px 20px;color:#9ca3af;font-size:14px;">No items to show</div>`;
        return;
    }

    tbody.innerHTML = rows.map(({ item, index }) => {
        const itemId = item.id || index;
        return `
        <div class="bulk-row-editor">
            <input type="text" class="bulk-edit-field bulk-col-item" placeholder="Item Name" value="${esc(item.name)}" data-item-id="${itemId}" data-field="name"/>
            <input type="text" class="bulk-edit-field" placeholder="Unit" value="${esc(item.unit || '')}" data-item-id="${itemId}" data-field="unit"/>
            <input type="text" class="bulk-edit-field" placeholder="Item Code" value="${esc(item.item_code || '')}" data-item-id="${itemId}" data-field="item_code"/>
            <input type="number" class="bulk-edit-field bulk-col-price" placeholder="Sale Price" value="${item.sale_price || ''}" data-item-id="${itemId}" data-field="sale_price" step="0.01" min="0"/>
            <input type="number" class="bulk-edit-field bulk-col-price" placeholder="Purchase Price" value="${item.purchase_price || ''}" data-item-id="${itemId}" data-field="purchase_price" step="0.01" min="0"/>
            <input type="number" class="bulk-edit-field bulk-col-price" placeholder="Opening Qty" value="${item.opening_qty || ''}" data-item-id="${itemId}" data-field="opening_qty" step="0.01" min="0"/>
            <input type="number" class="bulk-edit-field bulk-col-price" placeholder="Min Stock" value="${item.min_stock || ''}" data-item-id="${itemId}" data-field="min_stock" step="0.01" min="0"/>
            <input type="text" class="bulk-edit-field" placeholder="Location" value="${esc(item.location || '')}" data-item-id="${itemId}" data-field="location"/>
        </div>
    `;
    }).join('');
}

function applyBulkUpdate() {
    const updates = {};
    document.querySelectorAll('#bulk-edit-tbody input[data-field]').forEach(input => {
        const itemId = input.dataset.itemId;
        const field = input.dataset.field;
        const value = input.value;

        if (!itemId) return;
        if (!updates[itemId]) updates[itemId] = {};
        updates[itemId][field] = value === '' ? null : value;
    });

    if (!Object.keys(updates).length) {
        showToast('No changes to save.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) { showToast('CSRF token missing.'); return; }

    const saveBtn = document.getElementById('bulk-action-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const requests = Object.entries(updates).map(([itemId, fields]) =>
        fetch(`{{ url('dashboard/items') }}/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ ...fields, _method: 'PUT' })
        }).then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                throw new Error(data.message || `Failed to update item ${itemId}.`);
            }
            return { itemId, fields, item: data.item || null };
        })
    );

    Promise.all(requests)
    .then(results => {
        results.forEach(({ itemId, fields, item }) => {
            const idx = allItems.findIndex(entry => String(entry.id) === String(itemId));
            if (idx >= 0) {
                allItems[idx] = { ...allItems[idx], ...fields, ...(item || {}) };
            }
        });
        showToast('Items updated successfully!');
        closeBulkModal();
        renderList();
        ensureValidSelection();
    })
    .catch(error => showToast(error.message || 'Failed to update items.'))
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';
    });
}
/* ── Excel Export ── */
function exportToExcel() {
    if (selectedIdx === null) { showToast('Please select an item first.'); return; }
    const item = allItems[selectedIdx];
    const txns = transactions[selectedIdx] || [];
    const stockQty = getTotalQty(selectedIdx);
    const stockValue = parseFloat(item.purchase_price || 0) * parseFloat(stockQty || 0);
    const summaryData = [
        ['Item Summary'], [],
        ['Item Name', item.name || '—'],
        ['Item Type', item.type || 'product'],
        ['Category', item.category || item.category_name || '—'],
        ['Item Code', item.item_code || '—'],
        ['Unit', item.unit || '—'],
        ['Secondary Unit', item.secondary_unit || '—'],
        ['Conversion Rate', item.unit_conversion_rate || '—'],
        ['Sale Price', item.sale_price !== undefined && item.sale_price !== null ? 'Rs ' + parseFloat(item.sale_price || 0).toFixed(2) : '—'],
        ['Purchase Price', item.purchase_price !== undefined && item.purchase_price !== null ? 'Rs ' + parseFloat(item.purchase_price || 0).toFixed(2) : '—'],
        ['Opening Qty', item.opening_qty ?? '—'],
        ['Stock Quantity', stockQty],
        ['Stock Value', 'Rs ' + stockValue.toFixed(2)],
        ['Location', item.location || '—'],
        ['Description', item.description || '—'],
        ['Status', item.is_active === false || item.is_active === 0 ? 'Inactive' : 'Active'],
        ['Exported On', new Date().toLocaleDateString('en-GB')],
    ];
    const txnHeader = ['#', 'Date', 'Invoice/Ref.', 'Type', 'Party/Name', 'Broker', 'Quantity', 'Unit', 'Net W', 'Amount (Rs)', 'Price/Unit (Rs)', 'Status'];
    const txnRows = txns.length ? txns.map((t, i) => [
        i + 1,
        t.date || '—',
        t.invoice || '—',
        t.type || '—',
        t.name || t.details || '—',
        t.broker || '—',
        t.qty ?? '—',
        t.unit || '—',
        t.net_w !== undefined && t.net_w !== null ? parseFloat(t.net_w || 0).toFixed(2) : '—',
        t.amount !== undefined && t.amount !== null ? parseFloat(t.amount || 0).toFixed(2) : '—',
        t.price !== undefined && t.price !== null ? parseFloat(t.price || 0).toFixed(2) : '—',
        t.status || '—'
    ]) : [['No transactions recorded']];
    const wb = XLSX.utils.book_new();
    const wsSummary = XLSX.utils.aoa_to_sheet(summaryData); wsSummary['!cols']=[{wch:22},{wch:34}];
    XLSX.utils.book_append_sheet(wb, wsSummary, 'Summary');
    const wsTxn = XLSX.utils.aoa_to_sheet([txnHeader,...txnRows]); wsTxn['!cols']=[{wch:4},{wch:14},{wch:16},{wch:18},{wch:26},{wch:24},{wch:10},{wch:10},{wch:12},{wch:16},{wch:16},{wch:12}];
    XLSX.utils.book_append_sheet(wb, wsTxn, 'Transactions');
    const dateStr = new Date().toISOString().slice(0,10);
    const safeName = (item.name||'item').replace(/[^a-zA-Z0-9_\-]/g,'_');
    XLSX.writeFile(wb, `${safeName}_${dateStr}.xlsx`);
    showToast(`Downloaded: ${safeName}_${dateStr}.xlsx`);
}

/* ── Stock Adjustment Modal ── */
function openAdjModal() {
    if (selectedIdx === null) return;
    document.getElementById('adj-item-name').textContent = allItems[selectedIdx].name;
    document.getElementById('adj-qty').value = ''; document.getElementById('adj-price').value = '';
    document.getElementById('adj-details').value = ''; document.getElementById('adj-toggle').checked = false;
    handleAdjToggle();
    const d = new Date();
    document.getElementById('adj-date').value = d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
    const overlay = document.getElementById('adj-overlay');
    overlay.dataset.isEdit='0'; overlay.dataset.editIdx=''; overlay.dataset.editTi='';
    overlay.classList.add('open');
}
function closeAdjModal() { document.getElementById('adj-overlay').classList.remove('open'); }
function handleAdjToggle() {
    const isReduce = document.getElementById('adj-toggle').checked;
    document.getElementById('lbl-add-stock').classList.toggle('inactive', isReduce);
    document.getElementById('lbl-reduce-stock').classList.toggle('inactive', !isReduce);
}
function saveAdjustment() {
    const qty = parseFloat(document.getElementById('adj-qty').value);
    if (!qty || qty <= 0) { showToast('Please enter a valid quantity.'); return; }

    const isAdd   = !document.getElementById('adj-toggle').checked;
    const unit    = document.getElementById('adj-unit').value;
    const price   = document.getElementById('adj-price').value;
    const details = document.getElementById('adj-details').value;
    const date    = document.getElementById('adj-date').value;

   const item = allItems[selectedIdx];
    if (!item) return;

    const saveBtn = document.querySelector('.adj-save');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    if (!isAdd) {
        const currentStock = parseFloat(item.stock_qty ?? item.opening_qty ?? 0);
        if (qty > currentStock) {
            showToast(`Cannot reduce. Current stock is only ${currentStock}.`);
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
            return;
        }
    }

// ── Call the backend API ──
fetch(`/dashboard/items/${item.id}/adjust`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ qty, is_add: isAdd, unit, price, details, date })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // ── Update local item stock so UI reflects immediately ──
            item.stock_qty  = data.stock_qty;
            item.opening_qty = data.opening_qty;

            // ── Add to local transactions so it shows instantly ──
            if (!transactions[selectedIdx]) transactions[selectedIdx] = [];
            transactions[selectedIdx].push({
                type   : isAdd ? 'Add Adjustment' : 'Reduce Adjustment',
                qty    : qty,
                unit   : unit,
                price  : price,
                details: details,
                date   : formatDate(date),
                isAdd  : isAdd,
                invoice: '—',
                name   : '—',
                status : '—'
            });

            closeAdjModal();
            showToast('Stock adjustment saved!');

            // ── Refresh right panel stats and list ──
            document.getElementById('detail-stock-qty').textContent = formatSignedStock(data.stock_qty);
            document.getElementById('detail-stock-qty').className = `il-stat-value ${stockSignClass(data.stock_qty)}`;
            document.getElementById('detail-stock-val').textContent =
                'Rs ' + (parseFloat(item.purchase_price || 0) * parseFloat(data.stock_qty)).toFixed(2);
            document.getElementById('detail-stock-val').className = `il-stat-value ${stockSignClass(data.stock_qty)}`;

            renderTxns(selectedIdx);
            renderList();
        } else {
            showToast(data.message || 'Failed to save adjustment.');
        }
    })
    .catch(() => showToast('Network error. Please try again.'))
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';
    });
}
function formatDate(d) { if(!d)return''; const[y,m,day]=d.split('-'); return day+'/'+m+'/'+y; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
@endpush
