<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Challan</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Form Styles -->
    <link rel="stylesheet" href="{{ asset('css/saleform_style.css') }}">

</head>

<style>
    /* Dropdown with two columns and scrollbar */
    #partyDropdownMenu {
    min-width: 280px;
    max-width: 100%;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0 !important;
    margin: 0 !important;
    padding-top: 0 !important;
}

#partyDropdownMenu::before {
    content: none !important;
}

.dropdown-menu.show {
    padding-top: 0 !important;
}

#partyDropdownMenu.show {
    padding-top: 0 !important;
}

ul#partyDropdownMenu {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

#partyDropdownMenu li.p-2 {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
}

#partyDropdownMenu .form-control-sm {
    font-size: 13px;
}

/* Scrollbar styling for responsive dropdown */
#partyDropdownMenu::-webkit-scrollbar {
    width: 8px;
}

#partyDropdownMenu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#partyDropdownMenu::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#partyDropdownMenu::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Firefox scrollbar */
#partyDropdownMenu {
    scrollbar-width: thin;
    scrollbar-color: #888 #f1f1f1;
}

.party-option span {
    display: inline-block;
    width: 100%;
}
.party-option .party-option-main {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 60%;
}
.party-option .party-option-name {
    width: 100%;
}
.party-option .party-option-phone {
    width: 100%;
    font-size: 11px;
    color: #475569;
    line-height: 1.2;
    margin-top: 2px;
}
.party-option > span:first-child {
    width: 60%; /* Party name */
}
.party-option > span:last-child {
    width: 40%; /* Opening balance */
    text-align: right;
}

.item-picker {
    position: relative;
    min-width: 260px;
    flex: 1;
    overflow: visible;
}

.item-picker-input {
    width: 100%;
    border: 1px solid #cfd8e3;
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 14px;
    background: #fff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.item-picker-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.floating-input-wrapper {
    position: relative;
}

.floating-input-wrapper .meta-control {
    width: 70%;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 18px 14px 10px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: #fff;
}

.compact-header-field {
    width: 100%;
    max-width: 180px;
}
.compact-header-field .meta-control,
.compact-header-field .party-search-input {
    width: 100% !important;
    max-width: 100% !important;
}

.header-aux-fields .header-mini-fields-grid {
    max-width: 240px;
}

.floating-input-wrapper .meta-control:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.floating-input-wrapper textarea.meta-control {
    min-height: 78px;
    padding-top: 24px;
}

.floating-input-wrapper label {
    position: absolute;
    top: 16px;
    left: 14px;
    padding: 0 8px;
    background: #fff;
    font-size: 0.9rem;
    color: #6b7280;
    transition: top 0.2s ease, transform 0.2s ease, font-size 0.2s ease, color 0.2s ease;
    pointer-events: none;
}

.floating-input-wrapper .meta-control:focus + label,
.floating-input-wrapper .meta-control:not(:placeholder-shown) + label {
    top: 0;
    transform: translateY(-50%);
    font-size: 0.78rem;
    color: #2563eb;
}

.party-details .meta-control,
.billing-name-field .meta-control {
    min-height: 42px;

}

.cash-party-link-wrap {
    margin-top: 4px;
}

.cash-party-link-btn {
    border: 0;
    background: transparent;
    padding: 0;
    color: #2563eb;
    font-size: 12px;
    font-weight: 600;
    text-decoration: underline;
}

.cash-party-link-btn:hover {
    color: #1d4ed8;
}

.browser-toolbar {
    gap: 10px;
}

.toolbar-spacer {
    flex: 1 1 auto;
}

.toolbar-warehouse-block {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

.toolbar-warehouse-label {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    margin: 0;
}

.toolbar-warehouse-select {
    min-width: 120px;
    height: 32px;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #fff;
    font-size: 12px;
}

.toolbar-user-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 6px;
}

.toolbar-user-avatar {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    background: #e2e8f0;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.toolbar-user-name {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    white-space: nowrap;
}

.party-meta-field.address-field {
    width: 100%;
    max-width: 100%;
}

.party-meta-field.address-field textarea.meta-control {
    min-height: 120px;
    height: 120px;
    padding-top: 26px;
    padding-bottom: 10px;
}

.party-meta-field.address-field .floating-input-wrapper label {
    left: 16px;
    top: 8px;
    padding: 0 6px;
}

.description-content-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
}

.description-pane {
    flex: 1 1 100%;
    width: auto !important;
    margin-top: -2px;
}

.description-pane .floating-input-wrapper .meta-control,
.description-side-fields .floating-input-wrapper .meta-control,
.billing-name-field .floating-input-wrapper .meta-control,
.party-details .floating-input-wrapper .meta-control {
    width: 100%;
    max-width:280px;
}

.description-side-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 150px));
    gap: 8px 10px;
    align-content: start;
}

.description-side-fields .party-meta-field {
    min-width: 0;
}

.action-fields-layout {
    display: grid;
    grid-template-columns: minmax(180px, 220px) minmax(0, 1fr);
    gap: 16px;
    width: 100%;
    align-items: start;
}

.action-buttons-column {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.compact-side-fields {
    justify-content: start;
    padding-top: 2px;
}

.cash-party-selector-group {
    grid-column: 1;
    grid-row: 1;
}

.billing-name-group {
    grid-column: 1 / span 1;
    justify-self: start;
}

.party-details {
    display: contents;
}

.party-details .phone-field {
    grid-column: 3;
    grid-row: 1;
}

.party-details .billing-address-field {
    grid-column: 1;
    grid-row: 2;
}

.party-details .shipping-address-field {
    grid-column: 2;
    grid-row: 2;
}

.header-aux-fields {
    grid-column: 3;
    grid-row: 1;
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
    align-content: start;
    justify-items: stretch;
    margin-top: -2px;
}

.header-mini-fields-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
    width: 100%;
    max-width: none;
}

.header-mini-field .meta-control {
    width: 100%;
    min-height: 32px;
    height: 32px;
    padding: 6px 8px;
    border: 1px solid #d7e0ea;
    border-radius: 6px;
    background: #fbfdff;
    font-size: 12px;
}

.header-mini-field input[type="date"].meta-control {
    padding-right: 8px;
}

.po-fields-group.is-hidden {
    display: none;
}

.party-details .address-field {
    width: 100%;
}

.party-details .address-field .floating-input-wrapper label {
    top: 10px;
}

/* =========================
   HEADER LAYOUT COMPACT
========================= */

.header-section{
    display:grid;
    grid-template-columns:1fr;
    gap:6px;
}

.header-left{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap:6px;
    align-items:start;
}

/* =========================
   COMMON FIELD SIZE
========================= */

.party-meta-field,
.input-group,
.floating-input-wrapper{
    margin:0 !important;
}

.floating-input-wrapper .meta-control,
.party-dropdown-wrapper .party-search-input,
.party-dropdown-wrapper .btn.dropdown-toggle{
    width:100%;
    min-height:34px !important;
    height:34px !important;
    padding:10px 8px !important;
    font-size:12px !important;
    border-radius:6px !important;
    border:1px solid #d7e0ea !important;
    background:#fff !important;
    box-shadow:none !important;
}

/* =========================
   TEXTAREA COMPACT
========================= */
textarea.meta-control,
.party-details .address-field textarea.meta-control{
    min-height: 80px !important;
    height: 80px !important;
    max-height: 80px !important;
    width: 200px !important;
    max-width: 200px !important;
    resize: none;
    overflow-y: auto !important;
    padding: 8px 10px !important;
    scrollbar-width: thin;
    scrollbar-color: #888 #f1f1f1;
}

/* =========================
   LABELS SMALL
========================= */

.floating-input-wrapper label{
    font-size:10px !important;
    top:8px !important;
    left:8px !important;
    color:#64748b;
    font-weight:600;
}

.floating-input-wrapper .meta-control:focus + label,
.floating-input-wrapper .meta-control:not(:placeholder-shown) + label{
    top:0 !important;
    font-size:9px !important;
}

/* =========================
   BALANCE TEXT
========================= */

#partyBalanceDisplay{
    margin-top:2px !important;
    font-size:11px !important;
    line-height:1;
}

/* =========================
   RIGHT SIDE SMALL FIELDS
========================= */

.header-aux-fields{
    display:flex;
    flex-direction:column;
    gap:6px;
    margin-top:0 !important;
    align-items: stretch;
}

.header-mini-fields-grid{
    display:flex;
    flex-direction:column;
    gap:6px;
    width:100%;
    max-width:none;
}

/* =========================
   REMOVE EXTRA WIDTHS
========================= */

.description-pane .floating-input-wrapper .meta-control,
.description-side-fields .floating-input-wrapper .meta-control,
.billing-name-field .floating-input-wrapper .meta-control,
.party-details .floating-input-wrapper .meta-control{
    max-width:100% !important;
}

/* =========================
   ADDRESS WIDTH FIX
========================= */

.billing-address-field{
    width: 200px !important;
    max-width: 200px !important;
}
.shipping-address-field{
    width: 100%;
}

.cash-mode .party-details .phone-field {
    display: none;
}

@media (max-width: 768px) {
    .description-side-fields .party-meta-field,
    .description-pane,
    .description-side-fields,
    .action-fields-layout,
    .header-mini-fields-grid {
        grid-template-columns: 1fr;
    }

    .header-left {
        grid-template-columns: 1fr;
    }

    .header-aux-fields {
        justify-items: stretch;
    }
}


.item-picker-panel {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    width: 100%;
    min-width: 520px;
    max-width: 100%;
    background: white;
    border: 1px solid #e1e8ed;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1055;
    display: none;
    overflow: hidden;
    box-sizing: border-box;
}

.item-picker-panel.open {
    display: block !important;
}

.item-picker-head > span:first-child,
.item-picker-row > .item-picker-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-picker-list {
    max-height: 320px;
    overflow-y: auto;
}

.item-picker-list::-webkit-scrollbar {
    width: 8px;
}

.item-picker-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.item-picker-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.item-picker-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.item-picker-add {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    color: #2563eb;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.item-picker-add:hover {
    background: #f8fbff;
}

.item-picker-head,
.item-picker-row {
    display: grid;
    grid-template-columns: minmax(0, 2.4fr) 110px 120px 90px;
    gap: 12px;
    align-items: center;
}

@media (max-width: 768px) {
    .item-picker-head,
    .item-picker-row {
        grid-template-columns: minmax(0, 2fr) 90px 100px 90px;
        gap: 8px;
    }

    .item-picker-panel {
        max-width: 400px;
    }
}

@media (max-width: 576px) {
    .item-picker {
        min-width: 200px;
    }

    .item-picker-head,
    .item-picker-row {
        grid-template-columns: 1fr;
        gap: 4px;
    }

    .item-picker-head span:nth-child(2),
    .item-picker-head span:nth-child(3),
    .item-picker-head span:nth-child(4),
    .item-picker-row > div:nth-child(2),
    .item-picker-row > div:nth-child(3),
    .item-picker-row > div:nth-child(4) {
        display: none;
    }

    .item-picker-panel {
        max-width: 300px;
    }
}

.item-picker-head {
    padding: 10px 18px;
    font-size: 12px;
    font-weight: 700;
    color: #97a3b6;
    text-transform: uppercase;
}

.item-picker-list {
    max-height: 280px;
    overflow-y: auto;
}

.item-picker-row {
    padding: 12px 18px;
    cursor: pointer;
    border-top: 1px solid #f4f7fb;
}

.item-picker-row:hover {
    background: #f8fbff;
}

.item-picker-name small {
    color: #8a94a6;
    margin-left: 6px;
}

.item-picker-stock.neg {
    color: #dc3545;
}

.item-picker-empty {
    padding: 14px 18px;
    color: #8a94a6;
    font-size: 13px;
}

.table-container {
    position: relative;
    overflow-x: hidden;
    overflow-y: visible;
    width: 100%;
    box-sizing: border-box;
}

.item-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
}

.item-table th {
    position: relative;
    cursor: pointer;
    user-select: none;
    padding: 8px 4px !important;
}

.item-table th:hover {
    background-color: #f0f4f8;
}

.item-table th.editable-header::after {
    content: '✎';
    margin-left: 4px;
    opacity: 0.5;
    font-size: 0.85em;
}

.item-table th.editable-header:hover::after {
    opacity: 1;
}

.item-table th,
.item-table td {
    white-space: normal;
    word-break: break-word;
    overflow-wrap: break-word;
    padding: 6px 4px;
}

.item-table td {
    overflow: visible;
}

.item-table td:nth-child(1) {
    width: 40px;
    text-align: center;
}

.item-table td:nth-child(2) {
    width: 180px;
    min-width: 180px;
}

.item-table td:nth-child(3),
.item-table td:nth-child(4),
.item-table td:nth-child(5),
.item-table td:nth-child(6),
.item-table td:nth-child(7),
.item-table td:nth-child(8),
.item-table td:nth-child(9),
.item-table td:nth-child(10) {
    width: 90px;
}

.item-table td input,
.item-table td select {
    width: 100%;
    min-width: auto;
    padding: 4px 6px;
    font-size: 0.9em;
}

.item-picker-input {
    width: 100%;
    min-width: auto;
}

.modal-stack-top {
    z-index: 1085;
}

.unit-menu-scroll {
    max-height: 260px;
    overflow-y: auto;
}

/* Header style */
.dropdown-header {
    font-weight: 600;
    font-size: 0.9rem;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    position: sticky;
    top: 0;
    z-index: 10;
    margin: 0 !important;
    padding: 8px 12px !important;
}

#partyDropdownMenu .dropdown-header {
    margin: 0 !important;
    padding: 8px 12px !important;
    margin-top: 0 !important;
}

#partyDropdownMenu li:first-child {
    margin: 0 !important;
    padding: 0 !important;
}

#partyDropdownMenu li:first-child .dropdown-header {
    margin: 0 !important;
}

/* Hover effect */
.dropdown-item.party-option:hover {
    background-color: #e2f0ff;
}

/* Party dropdown styling */
.party-dropdown-wrapper .dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 6px;
}

.party-dropdown-wrapper .dropdown-toggle span {
    flex: 1;
    display: flex;
    align-items: center;
}

/* Search input styling */
.party-dropdown-wrapper .party-search-input {
    border: 1px solid #cbd5e1 !important;
    font-size: 13px;
    background: #fff !important;
    width: 100%;
    min-height: 34px;
    height: 34px;
    padding: 6px 12px !important;
    border-radius: 6px !important;
    margin-bottom: 0 !important;
}

.party-dropdown-wrapper .dropdown-menu {
    margin-top: 0 !important;
    top: calc(100% + 0px) !important;
}

.party-dropdown-wrapper .party-search-input:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25) !important;
    outline: none;
}

.party-dropdown-wrapper,
.broker-dropdown-wrapper {
    width: 100%;
}

.broker-dropdown-wrapper .dropdown-menu {
    max-height: 280px;
    overflow-y: auto;
}

.additional-charge-live-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 8px 0 12px;
}

.additional-charge-live-row {
    display: grid;
    grid-template-columns: 90px 1fr 86px 54px;
    gap: 8px;
    align-items: center;
}

.party-dropdown-wrapper {
    display: block;
    width: 100%;
    min-width: 0;
}

/* Hide element utility */
.is-hidden {
    display: none !important;
}

/* Party Group Dropdown Styles */
.party-group-dropdown {
    position: relative;
}

.party-group-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    border: 1px solid #ced4da;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.party-group-trigger:hover {
    border-color: #adb5bd;
    background: #f8f9fa;
}

.party-group-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 6px;
    margin-top: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 100;
    display: none;
}

.party-group-menu.show {
    display: block;
}

.party-group-add-btn {
    width: 100%;
    padding: 10px 12px;
    background: white;
    border: none;
    border-bottom: 1px solid #ced4da;
    text-align: left;
    cursor: pointer;
    color: #007bff;
    font-weight: 500;
    font-size: 13px;
}

.party-group-add-btn:hover {
    background: #f8f9fa;
}

.party-group-options {
    max-height: 200px;
    overflow-y: auto;
}

.party-group-option {
    padding: 8px 12px;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 13px;
    display: block;
}

.party-group-option:hover {
    background: #e2f0ff;
}

/* Modal for Party Group */
.txn-option-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1050;
}

.txn-option-modal.show {
    display: flex;
}

.txn-option-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.txn-option-dialog {
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    margin: auto;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    z-index: 1051;
}

.txn-option-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 16px;
}

.txn-option-actions {
    display: flex;
    gap: 8px;
    margin-top: 20px;
    justify-content: flex-end;
}

.txn-option-btn {
    padding: 8px 16px;
    border: 1px solid #ced4da;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.txn-option-btn.cancel {
    color: #6c757d;
}

.txn-option-btn.cancel:hover {
    background: #f8f9fa;
}

.txn-option-btn.ok {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.txn-option-btn.ok:hover {
    background: #0056b3;
}

.header-section {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 6px;
    align-items: start;
}

.header-left {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 160px);
    grid-template-rows: auto auto auto;
    gap: 6px;
    align-items: start;
}
.billing-name-group {
    grid-column: 2;
    grid-row: 1;
    width: 100%;
}

.party-selector-group {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

.party-selector-panel {
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
    width: 100%;
    margin-right: 0 !important;
}

.party-dropdown-wrapper {
    width: 100%;
    display: block;
    min-width: 0;
}

.party-dropdown-wrapper .party-search-input {
    width: 100%;
    min-width: 0;
}

.party-dropdown-wrapper .btn.dropdown-toggle,
.broker-dropdown-wrapper .btn.dropdown-toggle {
    width: 100%;
    min-height: 34px;
    height: 34px;
    padding: 6px 8px;
    border-radius: 6px;
    border-color: #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 500;
    background: #fff;
    font-size: 12px;
}

.broker-dropdown-wrapper #brokerDropdownBtn {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #495057;
}
.broker-dropdown-wrapper .broker-selected-info {
    display: none;
    margin-top: 4px;
    font-size: 12px;
    line-height: 1.2;
    color: #495057;
}
.broker-dropdown-wrapper .broker-selected-info.visible {
    display: block;
}
.broker-dropdown-wrapper .broker-selected-name {
    font-weight: 600;
}
.broker-dropdown-wrapper .broker-selected-phone {
    color: #6c757d;
}
.broker-dropdown-wrapper .broker-option {
    min-height: 50px;
    padding-top: 8px;
    padding-bottom: 8px;
}
.broker-dropdown-wrapper .broker-option-name {
    font-weight: 600;
}
.broker-dropdown-wrapper .broker-option-phone {
    display: block;
}
.broker-dropdown-wrapper .broker-option-city {
    margin-top: 2px;
}

.broker-inline-add-btn {
    border: 0;
    background: transparent;
    color: #2563eb;
    font-size: 14px;
    font-weight: 700;
    white-space: nowrap;
    padding: 0 4px;
}

.broker-inline-add-btn:hover {
    color: #1d4ed8;
}

#partyBalanceDisplay {
    margin-top: 2px !important;
    font-size: 11px;
    line-height: 1.2;
}

.party-meta-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.party-meta-field label {
    color: #334155;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.party-meta-field .meta-control {
    width: 100%;
    min-height: 32px;
    height: 32px;
    padding: 5px 8px;
    border: 1px solid #d7e0ea;
    border-radius: 6px;
    background: #fbfdff;
    color: #111827;
    resize: none;
    font-size: 12px;
}

.party-meta-grid {
    display: contents;
}

.party-meta-field.address-field {
    order: 4;
}

.billing-name-field,
.phone-field,
.billing-address-field,
.shipping-address-field {
    margin-top: 0 !important;
}

.billing-name-field {
    width: 100% !important;
    margin-right: 0 !important;
}

.billing-address-field,
.shipping-address-field {
    margin-top: -3px !important;
}

.description-pane .description-input {
    min-height: 140px !important;
    padding: 14px 10px !important;
    font-size: 14px !important;
}

.description-content-row {
    width: 100%;
    max-width: 100%;
}

.description-side-fields {
    margin-left: 0 !important;
}

.terms-condition-pane {
    width: 100%;
}

.meta-right-stack {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex: 1 1 auto;
    min-width: 0;
}

.terms-condition-card {
    border: 1px solid #dfe5ee;
    border-radius: 10px;
    background: #fff;
    padding: 18px 16px 16px;
    box-shadow: none;
}

.terms-condition-card-title {
    margin: 0 0 14px;
    font-size: 17px;
    font-weight: 700;
    color: #4b5563;
}

.terms-condition-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 14px;
}

.terms-condition-field {
    flex: 1 1 auto;
}

.terms-condition-field-label {
    display: block;
    margin: 0 0 6px;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
}

.terms-condition-select,
.terms-condition-text {
    width: 100%;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-size: 14px;
}

.terms-condition-select {
    height: 40px;
}

.terms-condition-text {
    min-height: 240px;
    resize: vertical;
    padding: 14px 16px;
    line-height: 1.55;
}

.action-fields-layout.meta-stack-layout {
    display: grid;
    grid-template-columns: minmax(220px, 250px) minmax(0, 360px);
    gap: 18px;
    align-items: start;
}

.action-fields-layout.meta-stack-layout .description-side-fields {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 360px;
    margin-left: 0 !important;
    margin-top: 0 !important;
    padding-top: 0;
}

.action-fields-layout.meta-stack-layout .party-meta-field {
    width: 100%;
}

.action-fields-layout.meta-stack-layout .floating-input-wrapper .meta-control {
    width: 100%;
    max-width: none;
    min-height: 50px;
    padding: 14px 16px 8px;
}

.additional-charge-live-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 8px 0 12px;
}

.additional-charge-live-row {
    display: grid;
    grid-template-columns: 90px 1fr 86px 54px;
    gap: 8px;
    align-items: center;
}

.additional-charge-live-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 600;
}

.additional-charge-live-input,
.additional-charge-live-tax {
    width: 100%;
    min-height: 38px;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    background: #fff;
    padding: 8px 12px;
}

.item-inline-input {
    width: 100%;
    min-width: 88px;
    height: 34px;
    padding: 6px 8px;
    border: 1px solid #d7e0ea;
    border-radius: 6px;
    background: #fff;
    font-size: 12px;
}

  border-radius: 6px;
  padding: 4px 8px;
}

.bottom-right .broker-calc-row {
    display: grid;
    grid-template-columns: 74px minmax(0, 1fr);
    gap: 8px;
    align-items: center;
    padding: 8px 10px;
    border: 1px solid #dbe4f0;
    border-radius: 10px;
    background: #fbfdff;
}

.bottom-right .market-calc-row {
    align-items: flex-start;
}

.bottom-right .broker-calc-inputs {
    display: grid;
    grid-template-columns: minmax(0, 220px) minmax(0, 260px) minmax(0, 180px);
    gap: 8px;
    width: 100%;
    align-items: center;
}

.bottom-right .broker-dropdown-wrapper {
    width: auto;
    max-width: 220px;
}

.bottom-right .broker-dropdown-wrapper .btn.dropdown-toggle {
    width: 100%;
    min-width: 0;
}

.bottom-right .broker-phone-input {
    width: 100%;
    max-width: 180px;
}

.bottom-right .brokerage-inputs {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px;
    width: 100%;
}

.bottom-right .market-calc-inputs {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    width: 100%;
}

.bottom-right .market-calc-inputs.single-column {
    grid-template-columns: 1fr;
}

.bottom-right .summary-expense-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    width: 100%;
    align-items: start;
}

.bottom-right .summary-expense-grid .calc-row {
    margin-bottom: 0;
}

.bottom-right .editable-expense-label {
    display: inline-block;
    min-width: 72px;
    padding: 3px 6px;
    border: 1px dashed transparent;
    border-radius: 6px;
    cursor: text;
    line-height: 1.2;
}

.bottom-right .editable-expense-label:focus {
    outline: none;
    border-color: #93c5fd;
    background: #f8fbff;
}

.bottom-right .custom-expense-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 8px;
}

.bottom-right .custom-expense-rows {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bottom-right .custom-expense-row {
    padding: 8px 10px;
    border: 1px solid #dbe4f0;
    border-radius: 10px;
    background: #fbfdff;
}

.bottom-right .custom-expense-row.no-heading .calc-label {
    display: none !important;
}

.bottom-right .custom-expense-inputs {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.bottom-right .custom-expense-mode-group {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px;
    border: 1px solid #d7e0ea;
    border-radius: 999px;
    background: #fff;
}

.bottom-right .custom-mode-btn {
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

.bottom-right .custom-mode-btn.is-active {
    background: #2563eb;
    color: #fff;
}

.bottom-right .custom-expense-account-wrap {
    min-width: 190px;
    flex: 1 1 210px;
}

.bottom-right .custom-expense-account-input,
.bottom-right .custom-expense-details,
.bottom-right .custom-expense-pct,
.bottom-right .custom-expense-value {
    min-height: 30px;
    height: 30px;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #d7e0ea;
    background: #fff;
    font-size: 11px;
}

.bottom-right .custom-expense-pct {
    width: 56px !important;
    min-width: 56px !important;
    max-width: 56px !important;
    text-align: right;
    flex: 0 0 56px !important;
}

.bottom-right .custom-expense-details {
    width: 180px !important;
    min-width: 180px !important;
    max-width: 180px !important;
    flex: 0 0 180px !important;
}

.bottom-right .custom-expense-value {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
    text-align: right;
    flex: 0 0 80px !important;
}

.bottom-right .ledger-account-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.bottom-right .ledger-account-option small {
    color: #64748b;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.bottom-right .ledger-account-group-label {
    padding: 6px 14px 4px;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.bottom-right .remove-custom-expense-row {
    width: 30px;
    height: 30px;
    border: 1px solid #fecaca;
    border-radius: 6px;
    background: #fff5f5;
    color: #dc2626;
}

.bottom-right .add-custom-expense-row {
    min-width: 120px;
    max-width: 140px;
    margin-left: 242px;
}

@media (max-width: 1200px) {
    .bottom-right .summary-expense-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .bottom-right .summary-expense-grid {
        grid-template-columns: 1fr;
    }
}

.item-discount-fields {
    display: grid;
    grid-template-columns: 58px minmax(88px, 1fr);
    gap: 6px;
    align-items: center;
}

.item-discount-fields input {
    width: 100%;
}

.bottom-right .broker-dropdown-wrapper {
    width: auto;
    max-width: 240px;
}

.bottom-right .broker-dropdown-wrapper .btn.dropdown-toggle {
    min-width: 0;
}

.bottom-right .broker-phone-input {
    max-width: 240px;
}

.bottom-right .market-mini-input {
    min-height: 34px;
    height: 34px;
    padding: 6px 8px;
    border-radius: 6px;
    border: 1px solid #d7e0ea;
    background: #fff;
    font-size: 12px;
    width: 100%;
}

.bottom-right .broker-dropdown-wrapper .btn.dropdown-toggle,
.bottom-right .broker-phone-input,
.bottom-right .brokerage-type,
.bottom-right .brokerage-rate,
.bottom-right .brokerage-amount {
    min-height: 30px;
    height: 30px;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #d7e0ea;
    background: #fff;
    font-size: 11px;
    width: 100%;
}

.bottom-right .broker-phone-input,
.bottom-right .brokerage-type,
.bottom-right .brokerage-rate,
.bottom-right .brokerage-amount {
    max-width: 100%;
}

.bottom-right .broker-calc-row .calc-label {
    margin-bottom: 0;
    font-weight: 600;
    font-size: 12px;
    line-height: 1.2;
}

.bottom-right .brokerage-amount {
    background: #f8fafc;
}

.header-right.w-25 {
    width: 420px !important;
    min-width: 420px;
    justify-content: flex-end;
    background: #ffffff;
    border: 1px solid #dbe4f0;
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px 16px;
    align-content: start;
}

.header-right.w-25 > .d-flex {
    display: none !important;
}

.header-right.w-25 .input-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
    margin: 0;
    min-width: 0;
}

.header-right.w-25 .input-group span {
    color: #1e293b;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
}

.header-right.w-25 .input-control {
    width: 100%;
    min-width: 0;
    height: 34px;
    padding: 6px 8px;
    border: 1px solid #d7e0ea;
    border-radius: 6px;
    background: #fbfdff;
    font-size: 12px;
}

.header-right.w-25 .invoice-number-group {
    grid-column: 1 / -1;
}

.header-right.w-25 .invoice-date-group {
    grid-column: 1;
}

.header-right.w-25 .order-date-group {
    grid-column: 2;
}

.header-right.w-25 .deal-days-group {
    grid-column: 1;
}

.header-right.w-25 .final-due-date-group {
    grid-column: 2;
}

.broker-option span:first-child {
    width: 70%;
}

.broker-option span:last-child {
    width: 30%;
    text-align: right;
    color: #64748b;
}

@media (max-width: 991px) {
    .header-section {
        grid-template-columns: 1fr;
    }

    .header-left {
        grid-template-columns: 1fr;
    }

    .header-right.w-25 {
        width: 100% !important;
        min-width: 0;
        grid-template-columns: 1fr;
    }

    .bottom-right .broker-dropdown-wrapper,
    .bottom-right .broker-phone-input,
    .bottom-right .brokerage-type,
    .bottom-right .brokerage-rate,
    .bottom-right .brokerage-amount {
        max-width: 100%;
    }

    .bottom-right .broker-calc-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .bottom-right .broker-calc-row:first-of-type .broker-calc-inputs,
    .bottom-right .broker-calc-row:last-of-type .broker-calc-inputs {
        grid-template-columns: 1fr;
    }

    .bottom-right .brokerage-inputs {
        grid-template-columns: 1fr;
    }

    .bottom-right .market-calc-inputs {
        grid-template-columns: 1fr;
    }

    .header-right.w-25 .invoice-date-group,
    .header-right.w-25 .order-date-group,
    .header-right.w-25 .deal-days-group,
    .header-right.w-25 .final-due-date-group {
        grid-column: auto;
    }
}

/* Warehouse Modal Gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.item-table tfoot td {
    background: #fbfdff;
    border-top: 1px solid var(--border-color);
    border-bottom: 0;
    padding: 12px 10px;
}

.item-table .column-total-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    margin-bottom: 2px;
}

.item-table .column-total-value {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-main);
}

.item-table .tfoot-add-row-cell {
    text-align: left;
}
/* ===== PARTY SELECTED CARD ===== */
.party-selected-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    background: #f0f7ff;
    border: 1.5px solid #2563eb;
    border-radius: 8px;
    padding: 8px 10px;
    min-height: 34px;
    width: 100%;
    cursor: default;
    box-sizing: border-box;
}

.party-selected-card .party-card-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 12px;
    line-height: 1.4;
    flex: 1;
    min-width: 0;
}

.party-selected-card .party-card-name {
    font-weight: 700;
    font-size: 13px;
    color: #1e293b;
}

.party-selected-card .party-card-line {
    color: #475569;
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.party-selected-card .party-card-balance {
    font-weight: 700;
    font-size: 11px;
}

.party-selected-card .party-card-clear {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 16px;
    cursor: pointer;
    padding: 0 2px;
    line-height: 1;
    flex-shrink: 0;
}

.party-selected-card .party-card-clear:hover {
    color: #dc2626;
}.header-left {
    display: grid;
    grid-template-columns: minmax(0, 200px) minmax(0, 160px) minmax(130px, 0.5fr);
    grid-template-rows: auto auto auto;
    gap: 4px 6px;
    min-width: 0;
    align-items: start;
    justify-items: start;
}

.cash-party-selector-group {
    grid-column: 1;
    grid-row: 1;
}

.billing-name-group {
    grid-column: 2;
    grid-row: 1;
    width: 100%;
}

.party-details .billing-address-field {
    grid-column: 1;
    grid-row: 2;
}

.party-details .shipping-address-field {
    grid-column: ;
    grid-row: 3;
}

.header-aux-fields {
    grid-column: 2;
    grid-row: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 0 !important;
    align-items: stretch;
}.cash-party-selector-group {
    grid-column: 1;
    grid-row: 1;
}

.billing-name-group {
    grid-column: 2;
    grid-row: 1;
    width: 100%;
}

.party-details .billing-address-field {
    grid-column: 1;
    grid-row: 2;
}

.party-details .shipping-address-field {
    grid-column: 2;   /* ← was 1, now moves to RIGHT column */
    grid-row: 2;      /* ← was 3, now same row as billing address */
}

.header-left {
    display: grid;
    grid-template-columns: minmax(0, 200px) minmax(0, 160px) minmax(130px, 0.5fr);
    grid-template-rows: auto auto auto;
    gap: 4px 6px;
    min-width: 0;
    align-items: start;
    justify-items: start;
}

.party-details .shipping-address-field {
    grid-column: 2;
    grid-row: 2;
}

.header-aux-fields {
    grid-column: 3;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 0 !important;
    margin-left: 35px;   /* ← adds gap between shipping address and delivery person */
    align-items: stretch;
}

.additional-charge-live-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 8px 0 12px;
}

.additional-charge-live-row {
    display: grid;
    grid-template-columns: 90px 1fr 86px 54px;
    gap: 8px;
    align-items: center;
}

.additional-charge-live-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 600;
}

.additional-charge-live-input,
.additional-charge-live-tax {
    width: 100%;
    min-height: 38px;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    background: #fff;
    padding: 8px 12px;
}

.transportation-details-live-section {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-start;
    padding: 8px 0;
}

.transportation-details-live-section .form-group {
    flex: 1 1 calc(50% - 4px);
    margin-bottom: 0;
    min-width: 150px;
}

.transportation-details-live-section input,
.transportation-details-live-section select,
.transportation-details-live-section textarea {
    font-size: 12px;
    padding: 6px 8px;
    min-height: auto;
    height: 32px;
}

.transportation-details-live-section textarea {
    height: 60px;
    resize: vertical;
}

.transportation-details-live-section label {
    font-size: 11px;
    margin-bottom: 3px;
}

/* Match sales/create.blade.php terms + meta layout */
.terms-condition-group {
    flex: 0 0 305px;
    max-width: 305px;
    width: 305px;
}

.terms-condition-pane {
    width: 100%;
}

.meta-right-stack {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex: 1 1 auto;
    min-width: 0;
}

.action-fields-layout.meta-stack-layout {
    display: flex;
    align-items: flex-start;
    gap: 22px;
}

.action-fields-layout.meta-stack-layout .action-buttons-column {
    flex: 0 0 270px;
    max-width: 270px;
}

.action-fields-layout.meta-stack-layout .description-side-fields {
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 0 0 315px;
    max-width: 315px;
    width: 315px;
    margin-left: 0 !important;
    margin-top: 0 !important;
    padding-top: 0;
}

.action-fields-layout.meta-stack-layout .party-meta-field {
    width: 100%;
}

.action-fields-layout.meta-stack-layout .floating-input-wrapper .meta-control {
    width: 100%;
    max-width: none;
}

.action-fields-layout.meta-stack-layout.no-terms-layout {
    gap: 0 !important;
}

.action-fields-layout.meta-stack-layout.no-terms-layout .meta-right-stack {
    width: 100% !important;
    max-width: 100% !important;
}

.action-fields-layout.meta-stack-layout.no-terms-layout .action-buttons-column {
    flex: 0 0 270px;
    max-width: 270px;
}

.action-fields-layout.meta-stack-layout.no-terms-layout .description-side-fields {
    flex: 0 0 315px;
    max-width: 315px;
    width: 315px;
}

.terms-condition-card {
    border: 1px solid #dfe5ee;
    border-radius: 10px;
    background: #fff;
    padding: 18px 16px 16px;
    box-shadow: none;
}

.terms-condition-card-title {
    margin: 0 0 14px;
    font-size: 17px;
    font-weight: 700;
    color: #4b5563;
}

.terms-condition-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 14px;
}

.terms-condition-field {
    flex: 1 1 auto;
}

.terms-condition-field-label {
    display: block;
    margin: 0 0 6px;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
}

.terms-condition-select,
.terms-condition-text {
    width: 100%;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-size: 14px;
}

.terms-condition-select {
    height: 40px;
    padding: 8px 40px 8px 12px;
}

.terms-condition-add-btn {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: 1px solid #cfd8e3;
    background: #fff;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 19px;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
}

.terms-condition-text {
    min-height: 250px !important;
    resize: vertical;
    padding: 14px 12px !important;
    line-height: 1.45;
}

    </style>

@php
    $itemFormSettings = $itemFormSettings ?? json_decode(\App\Models\AppSetting::getValue('item_form_settings', '{}'), true) ?: [];
    $itemEnable = (bool) data_get($itemFormSettings, 'enable_item', true);
    $sellType = (string) data_get($itemFormSettings, 'sell_type', 'both');
    $showProducts = $itemEnable && in_array($sellType, ['product', 'both'], true);
    $showServices = $itemEnable && in_array($sellType, ['service', 'both'], true);

    $saleItemsSource = $showProducts ? collect($items ?? []) : collect();
    if ($saleItemsSource->isEmpty() && $showProducts) {
        $saleItemsSource = \App\Models\Item::with('category')
            ->where(function ($query) {
                $query->where('type', 'product')
                    ->orWhereNull('type');
            })
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->orderBy('name')
            ->get();
    }

    $serviceItemsSource = $showServices ? collect($serviceItemsSource ?? collect()) : collect();
    if ($serviceItemsSource->isEmpty() && $showServices) {
        $serviceItemsSource = \App\Models\Item::with('category')
            ->where('type', 'service')
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->orderBy('name')
            ->get();
    }

    $saleCategoryOptions = collect($saleItemsSource)
        ->concat($serviceItemsSource)
        ->map(function ($item) {
            return $item->category->name ?? $item->category_name ?? $item->category_id ?? null;
        })
        ->filter()
        ->map(fn ($value) => trim((string) $value))
        ->filter()
        ->unique()
        ->sort()
        ->values();
@endphp

<body>

    <div class="container-fluid min-vh-100 d-flex flex-column p-0">
        <!-- Explorer / Tab Bar Area -->
        <header class="tab-system-header">
            <div class="tab-strip-wrapper justify-content-between">
                <div class="d-flex align-items-end flex-grow-1 overflow-hidden">
                    <div id="tab-strip" class="tab-strip d-flex align-items-end">
                        <!-- Tabs will be dynamically inserted here -->
                    </div>
                    <button id="add-tab-btn" class="btn add-tab-btn" title="New Tab">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>

                <div class="window-controls d-flex align-items-center px-2 gap-3">
                    <i id="calc-icon" class="fa-solid fa-calculator" title="Calculator"></i>
                            <button type="button" class="sale-settings-trigger-btn text-reset border-0 bg-transparent p-0" title="Settings" data-bs-toggle="offcanvas" data-bs-target="#saleSettingsSidebar" aria-controls="saleSettingsSidebar">
                                <i class="fa-solid fa-gear"></i>
                            </button>
                    <i class="fa-solid fa-xmark close-app-icon" title="Close Window"></i>
                </div>
            </div>
            <!-- Browser Toolbar / Heading Area -->
            <div class="browser-toolbar d-flex align-items-center px-3">
                <p class="mt-3 ms-3 mb-0 me-3 mb-2"> Delivery Challan</p>
                <div class="toolbar-spacer"></div>
                <div class="toolbar-warehouse-block">
                    <p class="toolbar-warehouse-label">Warehouse</p>
                    <select class="toolbar-warehouse-select warehouse-select" name="warehouse_id">
                        @forelse(($warehouses ?? []) as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                data-handler-name="{{ $warehouse->handler_name }}"
                                data-handler-phone="{{ $warehouse->handler_phone }}">
                                {{ $warehouse->name }}
                            </option>
                        @empty
                            <option value="">Main Store</option>
                        @endforelse
                        <option value="add_new_warehouse">+ Add New Warehouse</option>
                    </select>
                </div>
                <div class="toolbar-user-chip">
                    <span class="toolbar-user-avatar">{{ strtoupper(substr(trim((string) (auth()->user()->name ?? 'U')), 0, 1)) }}</span>
                    <span class="toolbar-user-name">{{ auth()->user()->name ?? 'User' }}</span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main id="content-area" class="">
            <!-- Tab contents will be dynamically inserted here
            <button id="global-save-btn" class="btn btn-primary position-absolute bottom-0 end-0 m-4 shadow-lg z-3">
                <i class="bi bi-save me-2"></i>Save
            </button> -->
            <!-- Form Template -->
            <template id="form-template">
                <div class="invoice-container">
                    <div class="invoice-form invoice-card">

                        <!-- Header Section -->
                        <div class="header-section">
                            <div class="header-left">
                                <div class="input-group party-selector-group cash-party-selector-group">
                                <div class="party-selector-panel">
                                <!-- Party dropdown button -->
<div class="dropdown party-dropdown-wrapper compact-header-field" data-bs-auto-close="outside" style="position: relative;">
    <input type="text" class="form-control party-search-input w-100" placeholder="Search party..." id="partyDropdownBtn" data-bs-toggle="dropdown" style="font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 8px; min-height: 32px;">

    <!-- Balance display -->
    <div id="partyBalanceDisplay" style="color: #007bff; font-weight: 600; margin-top: 4px;">
        <!-- JS will populate balance here -->
    </div>

    <!-- Dropdown menu (existing) -->
    <ul class="dropdown-menu w-100" aria-labelledby="partyDropdownBtn" id="partyDropdownMenu">
        <li><a class="dropdown-item text-primary" href="#" id="addNewPartyBtn">+ Add New Party</a></li>
        <li class="dropdown-header d-flex justify-content-between px-3">

            <span>Party Name</span>
            <span>Opening Balance</span>
        </li>
          @foreach($parties as $party)
          <li>
            <a class="dropdown-item d-flex justify-content-between align-items-start party-option" href="#"
               data-id="{{ $party->id }}"
               data-name="{{ $party->name }}"
               data-phone="{{ $party->phone }}"
               data-phone-number-2="{{ $party->phone_number_2 }}"
               data-city="{{ $party->city }}"
               data-ptcl="{{ $party->ptcl_number }}"
               data-email="{{ $party->email }}"
               data-address="{{ addslashes($party->address ?? '') }}"
               data-billing="{{ addslashes($party->billing_address ?? '') }}"
               data-shipping="{{ addslashes($party->shipping_address ?? '') }}"
               data-party-group="{{ $party->party_group }}"
               data-due-days="{{ $party->due_days ?? '' }}"
               data-opening="{{ $party->opening_balance ?? 0 }}"
               data-type="{{ $party->transaction_type }}"
               data-party-type="{{ is_array($party->party_type) ? implode(',', $party->party_type) : ($party->party_type ?? '') }}"
               data-credit-limit-enabled="{{ $party->credit_limit_enabled ?? 0 }}"
               data-credit-limit-amount="{{ $party->credit_limit_amount ?? '' }}"
               data-custom-fields="{{ e(json_encode($party->custom_fields ?? [])) }}">
                <span class="party-option-main">
                  <span class="party-option-name">{{ $party->name }}</span>
                  <span class="party-option-phone">{{ $party->phone ?: '-' }}</span>
                </span>
                <span
                  @if($party->transaction_type == 'pay')
                      class="text-danger"
                  @elseif($party->transaction_type == 'receive')
                      class="text-success"
                  @endif
                >
                  @if($party->transaction_type == 'pay')
                      <i class="fa-solid fa-arrow-up me-1"></i>
                  @elseif($party->transaction_type == 'receive')
                      <i class="fa-solid fa-arrow-down me-1"></i>
                  @endif
                  ₹{{ number_format($party->opening_balance ?? 0, 2) }}
                </span>
            </a>
          </li>
          @endforeach

    </ul>
</div>
<input type="hidden" class="party-id" name="party_id">
                                </div>
                                </div>
                               <div class="party-meta-grid billing-name-group">
<div class="party-meta-field billing-name-field compact-header-field">
    <div class="floating-input-wrapper">
            <input type="text" id="billingNameInput" name="billing_name" class="meta-control billing-name-input" placeholder=" ">
            <label>Billing Name (Optional)</label>
        </div>
        <div class="cash-party-link-wrap d-none">
            <button type="button" class="cash-party-link-btn show-party-selector-btn">Show Party</button>
        </div>
    </div>
</div>
                                <div class="party-meta-grid party-details d-none">

                                    <div class="party-meta-field address-field billing-address-field">
    <div class="floating-input-wrapper" style="position:relative;">
        <textarea name="billing_address" class="meta-control billing-address" rows="2" placeholder=" "
            style="min-height:90px !important; height:90px !important; max-height:90px !important;
                   overflow-y:auto !important; resize:none; font-size:12px; line-height:1.5;
                   scrollbar-width:thin; padding:8px 10px !important;"></textarea>
        <label>Billing Adddress</label>
        <span class="party-save-indicator" style="position:absolute; top:4px; right:6px; font-size:10px; font-weight:600; opacity:0; transition:opacity 0.3s;"></span>
    </div>
</div>
                                    <div class="party-meta-field address-field shipping-address-field">
                                        <div class="floating-input-wrapper">
                                            <textarea name="shipping_address" class="meta-control shipping-address" rows="2" placeholder=" "></textarea>
                                            <label>Shipping Address</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="header-aux-fields">
                                    <div class="header-mini-fields-grid">
                                        <div class="party-meta-field header-mini-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="delivery_person" class="meta-control delivery-person-input" placeholder=" ">
                                                <label>Delivery Person</label>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="header-mini-fields-grid po-fields-group {{ !empty($customerPoDetailsEnabled) ? '' : 'is-hidden' }}">
                                        <div class="party-meta-field header-mini-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="po_no" class="meta-control po-no-input" placeholder=" ">
                                                <label>PO No.</label>
                                            </div>
                                        </div>
                                        <div class="party-meta-field header-mini-field">
                                            <div class="floating-input-wrapper">
                                                <input type="date" name="po_date" class="meta-control po-date-input" placeholder=" ">
                                                <label>PO Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="header-mini-fields-grid dynamic-invoice-fields-row"></div>
                                </div>
                            </div>

                            <div class="header-right w-25">
                                <div class="d-flex justify-content-end mb-2">

                                </div>
                                <div class="input-group invoice-number-group">
                                    <span>Invoice No.</span>
                                    <div class="invoice-prefix-stack">
                                        <select class="input-control underline-input sale-prefix-select"></select>
                                        <input type="text" class="input-control underline-input bill-number" value="{{ $nextInvoiceNumber ?? 'Auto' }}" readonly>
                                    </div>
                                </div>
                                <div class="input-group date-wrapper invoice-date-group">
                                    <span>Invoice Date</span>
                                    <input type="text" class="input-control underline-input invoice-date" placeholder="dd/mm/yyyy" readonly>
                                </div>
                                <div class="input-group date-wrapper transaction-time-group d-none">
                                    <span>Invoice Time</span>
                                    <input type="text" class="input-control underline-input transaction-time-display" placeholder="03:45 PM" readonly>
                                </div>

                                <div class="input-group date-wrapper deal-days-group">
                                    <span>Deal Days</span>
                                    <select class="input-control underline-input due-days-select">
                                        <option value="0">0 Days</option>
                                        <option value="5">5 Days</option>
                                        <option value="10">10 Days</option>
                                        <option value="15">15 Days</option>
                                        <option value="30">30 Days</option>
                                        <option value="45">45 Days</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                    <input type="number" class="input-control underline-input due-days-custom d-none" placeholder="Custom deal days" min="0">
                                </div>
                                <div class="input-group date-wrapper final-due-date-group">
                                    <span>Due Date</span>
                                    <input type="text" class="input-control underline-input due-date" placeholder="dd/mm/yyyy" readonly>
                                </div>

                            </div>
                        </div>

                        <div class="alert alert-success d-none sale-success-msg"></div>

                        <!-- Table Section -->
                        @include('dashboard.sales.partials._create_item_table')

                        <!-- Bottom Split Section -->
                        <div class="bottom-section">
                            <!-- Left Column -->
                            <div class="bottom-left">
                                <div class="payment-section">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control default-payment-direction d-none" style="max-width: 140px;">
                                            <option value="payment_in" selected>Payment In</option>
                                            <option value="payment_out">Payment Out</option>
                                        </select>
                                        <select class="input-control default-payment-type">
                                            <option value="">Select Payment Type</option>
                                            <option value="cash" selected>Cash</option>
                                            <option value="cheques">Cheques</option>
                                            @foreach($bankAccounts as $bank)
                                                <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                            @endforeach
                                            <option value="add_new_bank">+ Add Bank Account</option>
                                        </select>
                                                                                <input type="text" class="input-control default-payment-reference d-none" placeholder="Reference">

                                        <input type="number" class="input-control default-payment-amount d-none" placeholder="Amount" min="0" step="0.01">
                                    </div>

                                    <div class="payment-entries">
                                        <!-- Payment rows will be added here when "Add Payment type" is clicked -->
                                    </div>

                                    <div class="payment-total d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-muted">Total payment:</span>
                                        <span class="fw-bold payment-total-amount">0</span>
                                    </div>

                                    <a href="#" class="link-text add-payment-entry">+ Add Payment type</a>
                                    <div class="transportation-details-live-section d-none mt-3"></div>
                                </div>

                                <template id="payment-entry-template">
                                    <div class="payment-entry d-flex align-items-center gap-2 mb-2">
                                        <select class="input-control payment-direction-entry d-none" style="max-width: 140px;">
                                            <option value="payment_in" selected>Payment In</option>
                                            <option value="payment_out">Payment Out</option>
                                        </select>
                                         <select class="input-control payment-type-entry">
                                              <option value="">Select Bank Account</option>
                                              <option value="cash" selected>Cash</option>
                                              <option value="cheques">Cheques</option>
                                              @foreach($bankAccounts as $bank)
                                                  <option value="bank-{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                              @endforeach
                                              <option value="add_new_bank">+ Add Bank Account</option>
                                          </select>
                                                                                  <input type="text" class="input-control payment-reference" placeholder="Reference">

                                        <input type="number" class="input-control payment-amount" placeholder="Amount" min="0" step="0.01">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-payment-entry" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </template>

<div class="d-flex flex-column align-items-start w-100">

                                <div class="action-fields-layout meta-stack-layout w-100">
                                    <div class="terms-condition-group mb-2">

                                        <div class="terms-condition-pane mt-2">
                                            <div class="terms-condition-card">
                                                <h6 class="terms-condition-card-title">Terms &amp; Conditions</h6>
                                                <div class="terms-condition-row">
                                                    <div class="terms-condition-field">
                                                        <label class="terms-condition-field-label">Terms &amp; Conditions</label>
                                                        <select class="form-select terms-condition-select">
                                                            <option value="">Select Terms</option>
                                                            <option value="__add_new__">+ Add Terms &amp; Conditions</option>
                                                        </select>
                                                    </div>
                                                    <button type="button" class="terms-condition-add-btn open-terms-condition-modal" title="Add Terms & Conditions">
                                                        <i class="fa-solid fa-plus"></i>
                                                    </button>
                                                </div>
                                                <textarea class="form-control terms-condition-text" rows="5" placeholder="Thanks for doing business with us!"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="meta-right-stack d-flex gap-3">
                                    <div class="action-buttons-column">
                                        <div class="description-action-group mb-2 w-100 d-flex">
                                            <button type="button" class="btn-action-light action-btn add-description">
                                                <i class="fa-solid fa-align-left"></i>
                                                ADD DESCRIPTION
                                            </button>
                                            <div class="description-content-row">
                                                <div class="description-pane d-none">
                                                    <div class="floating-input-wrapper">
                                                        <textarea class="form-control description-input meta-control" rows="3" placeholder=" "></textarea>
                                                        <label>Description</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="action-buttons d-flex flex-wrap gap-2 mb-2 w-100">
                                            <button type="button" class="btn-action-light action-btn add-image">
                                                <i class="fa-solid fa-camera"></i>
                                                ADD IMAGE
                                            </button>

                                            <button type="button" class="btn-action-light action-btn add-document">
                                                <i class="fa-solid fa-align-left"></i>
                                                ADD DOCUMENT
                                            </button>
                                        </div>
                                    </div>

                                    <div class="description-side-fields compact-side-fields d-flex flex-column gap-2">
                                        <div class="party-meta-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="goods_name" class="meta-control goods-name-input" placeholder=" ">
                                                <label>Goodz / Name</label>
                                            </div>
                                        </div>
                                        <div class="party-meta-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="bilti_gari_no" class="meta-control bilti-gari-input" placeholder=" ">
                                                <label>Bilti No / Gari No</label>
                                            </div>
                                        </div>
                                        <div class="party-meta-field">
                                            <div class="floating-input-wrapper">
                                                <input type="text" name="details_extra" class="meta-control details-extra-input" placeholder=" ">
                                                <label>Details Extra</label>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>

                               <div class="image-upload-section mt-2">
                                    <div class="image-placeholder text-center p-3 border border-dashed rounded" style="cursor:pointer;">
                                        <div class="text-muted">Click to select image(s)</div>
                                        <div class="small text-muted">(PNG/JPG, up to 5MB each)</div>
                                    </div>
                                    <div class="image-files-list d-flex flex-wrap gap-2 mt-2"></div>
                                    <div class="document-files-list list-group mt-2"></div>
                                </div>

                                <input type="file" class="d-none image-input" accept="image/*" multiple />
                                <input type="file" class="d-none document-input" accept=".pdf,.doc,.docx" multiple />
                            </div>

                        </div>

                            <!-- Right Column -->
                            <div class="bottom-right">
                                <div class="calc-row broker-calc-row d-none legacy-broker-calc-row">
                                    <div class="calc-label">Broker</div>
                                    <div class="calc-inputs broker-calc-inputs">
                                        <div class="broker-dropdown-wrapper dropdown" data-bs-auto-close="outside" style="position: relative; display: inline-block; width: 260px; max-width: 100%;">
                                            <input type="text" class="form-control broker-search-input w-100" placeholder="Broker" id="brokerDropdownBtn" data-bs-toggle="dropdown" autocomplete="off">
                                            <div class="broker-selected-info">
                                                <div class="broker-selected-name"></div>
                                                <div class="broker-selected-phone"></div>
                                            </div>

                                            <ul class="dropdown-menu w-100" aria-labelledby="brokerDropdownBtn" id="brokerDropdownMenu">
                                                @foreach($brokers as $broker)
                                                <li>
                                                    <a class="dropdown-item d-flex justify-content-between align-items-center broker-option" href="#"
                                                       data-id="{{ $broker->id }}"
                                                       data-phone="{{ $broker->phone }}"
                                                       data-name="{{ $broker->name }}"
                                                       data-commission-rate="{{ $broker->commission_rate ?? 0 }}">
                                                        <div class="broker-option-name">{{ $broker->name }}</div>
                                                        <div class="broker-option-city text-muted small">{{ $broker->city ?: '-' }}</div>
                                                    </a>
                                                </li>
                                                @endforeach
                                               </ul>
                                        </div>
                                        <button type="button" class="broker-inline-add-btn open-broker-modal-btn">+ Broker</button>
                                        <div class="brokerage-inputs">
                                        <select class="brokerage-type">
                                            <option value="">Condition</option>
                                            <option value="broker_rate">Broker Rate</option>
                                            <option value="full">Poori Brokerage (0.45%)</option>
                                            <option value="half">Aadhi Brokerage (0.225%)</option>
                                            <option value="custom_pct">Custom %</option>
                                            <option value="fixed_rs">Rs</option>
                                            <option value="per_kg">Per KG (Safi Wazan)</option>
                                        </select>
                                        <input type="number" class="brokerage-rate" min="0" step="0.01" placeholder="Value">
                                        <input type="hidden" class="brokerage-base-amount" value="0">
                                        <input type="number" class="brokerage-amount" min="0" step="0.01" value="0" readonly>
                                    </div>
                                        <input type="hidden" class="broker-id" name="broker_id">
                                        <input type="hidden" class="broker-phone-input" name="broker_phone">
                                    </div>
                                </div>



                                <!-- Discount -->
                                <div class="calc-row transaction-discount-row">
                                    <div class="calc-label">Discount</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input discount-pct" placeholder="%">
                                        <span>-</span>
                                        <input type="number" class="mini-input discount-rs" placeholder="Rs">
                                    </div>
                                </div>

                                <!-- Tax -->
                                <div class="calc-row transaction-tax-row">
                                    <div class="calc-label">Tax</div>
                                    <div class="calc-inputs">
                                        <select class="mini-input tax-select" style="width: 100px;">
                                            <option value="0">NONE</option>
                                            <option value="5">GST@5%</option>
                                            <option value="12">GST@12%</option>
                                            <option value="18">GST@18%</option>
                                        </select>
                                        <span class="tax-amount-display">0</span>
                                    </div>
                                </div>

                                <div class="additional-charge-live-section d-none"></div>

                                <!-- Summary Expense Grid -->

                                <div class="custom-expense-section">
                                    <div class="custom-expense-rows"></div>
                                    <button type="button" class="btn-action-light action-btn add-custom-expense-row">ADD ROW</button>
                                </div>

                                <!-- Round Off -->
                                <div class="calc-row transaction-round-total-row">
                                    <div class="checkbox-group">
                                        <input type="checkbox" class="custom-checkbox round-off-check" checked>
                                        <label class="link-text">Round Off</label>
                                    </div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input round-off-val" value="0" readonly>
                                    </div>
                                </div>

                                <!-- Final Total -->
                                <div class="final-total-group">
                                    <div class="calc-row" style="margin-bottom: 5px;">
                                        <div class="calc-label" style="font-weight: 700;">Total</div>
                                    </div>
                                    <input type="text" class="total-input-large grand-total" value="0" readonly>
                                </div>

                                <div class="calc-row">
                                    <div class="calc-label">Paid Amount</div>
                                    <div class="calc-inputs">
                                        <input type="number" class="mini-input received-amount" value="0" readonly>
                                    </div>
                                </div>

                                <div class="calc-row">
                                    <div class="calc-label">Remaining Amount</div>
                                    <div class="calc-inputs">
                                        <span class="fw-bold balance-amount">0</span>
                                    </div>
                                </div>
                            </div>

                            <template id="custom-expense-row-template">
                                <div class="calc-row custom-expense-row">
                                    <div class="calc-label">
                                        <span class="editable-expense-label custom-expense-heading" contenteditable="true" spellcheck="false">New Row</span>
                                    </div>
                                    <div class="calc-inputs custom-expense-inputs">
                                        <div class="custom-expense-mode-group" role="group" aria-label="Adjustment mode">
                                            <button type="button" class="custom-mode-btn" data-mode="-">-</button>
                                            <button type="button" class="custom-mode-btn is-active" data-mode="+">+</button>
                                            <button type="button" class="custom-mode-btn" data-mode="S">S</button>
                                        </div>
                                        <div class="broker-dropdown-wrapper dropdown custom-expense-account-wrap" data-bs-auto-close="outside" style="position: relative; display: inline-block; width: 190px; max-width: 100%;">
                                            <input type="text" class="form-control custom-expense-account-input w-100" placeholder="Party / Broker / Item" data-bs-toggle="dropdown" autocomplete="off">
                                            <ul class="dropdown-menu w-100 ledger-account-menu"></ul>
                                        </div>
                                        <input type="text" class="mini-input custom-expense-details" value="" placeholder="Tafseel">
                                        <input type="number" class="mini-input custom-expense-pct" value="" min="0" step="0.01" placeholder="%">
                                        <span class="text-muted small">-</span>
                                        <input type="number" class="mini-input custom-expense-value" value="0" min="0" step="0.01" placeholder="Amt">
                                        <input type="hidden" class="custom-expense-mode" value="+">
                                        <input type="hidden" class="custom-expense-account-type" value="">
                                        <input type="hidden" class="custom-expense-account-id" value="">
                                        <input type="hidden" class="custom-expense-account-phone" value="">
                                        <button type="button" class="remove-custom-expense-row" title="Remove">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Fixed Action Bar -->
                    <div class="sticky-actions">
                        <div class="btn-share">
                            <button class="btn-share-main" type="button">Save &amp; Share</button>
                            <button class="btn-share-arrow"><i class="fa-solid fa-chevron-down"></i></button>
                        </div>
                        <button class="btn-save" type="button">Save</button>
                    </div>
                </div>
            </template>
        </main>
    </div>

    <!-- Item Column Settings Modal -->
    <div class="modal fade" id="itemColumnModal" tabindex="-1" aria-labelledby="itemColumnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="itemColumnModalLabel">Add fields to items</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input check-category" type="checkbox" id="colCategoryCheck">
                        <label class="form-check-label" for="colCategoryCheck">Item Category</label>
                    </div>
                    <div class="mb-3">
                        <select class="form-select form-select-sm item-filter-category" disabled>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input check-item-code" type="checkbox" id="colItemCodeCheck">
                        <label class="form-check-label" for="colItemCodeCheck">Item Code</label>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm item-filter-code" placeholder="Filter by code" disabled>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input check-description" type="checkbox" id="colDescriptionCheck">
                        <label class="form-check-label" for="colDescriptionCheck">Description</label>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm item-filter-description" placeholder="Filter by description" disabled>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input check-discount" type="checkbox" id="colDiscountCheck">
                        <label class="form-check-label" for="colDiscountCheck">Discount</label>
                    </div>
                    <div class="mb-2">
                        <select class="form-select form-select-sm item-filter-discount" disabled>
                            <option value="">Any Discount</option>
                            <option value="has">Has Discount</option>
                            <option value="none">No Discount</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100 item-filter-apply" data-bs-dismiss="modal">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Settings Sidebar -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="saleSettingsSidebar" aria-labelledby="saleSettingsSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="saleSettingsSidebarLabel">Sale Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="fw-semibold">Sale Prefix</div>
                    <div class="text-muted small">INV- <a href="#" class="text-decoration-none">Edit</a></div>
                </div>
                <input class="form-check-input" type="checkbox" checked>
            </div>
            <div class="list-group mb-3">
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#itemColumnModal">
                    <div>
                        <div class="fw-semibold">Add fields to invoice</div>
                        <div class="text-muted small">Select columns to show</div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <label class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Quick Entry</div>
                        <div class="text-muted small">Speed up data entry</div>
                    </div>
                    <input class="form-check-input" type="checkbox">
                </label>
                <label class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Link payment to invoices</div>
                        <div class="text-muted small">Keep payment history linked</div>
                    </div>
                    <input class="form-check-input" type="checkbox" checked>
                </label>
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Due dates &amp; payment terms</div>
                        <div class="text-muted small">Set payment terms</div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#additionalChargesModal">
                    <div>
                        <div class="fw-semibold">Additional charges</div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <a href="{{ route('settings.print-layout') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Print Settings</div>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>

            <div class="mb-3">
                <div class="fw-semibold mb-2">Billing Type</div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="billingType" id="billingLite" value="lite">
                    <label class="form-check-label" for="billingLite">Lite Sale</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="billingType" id="billingFull" value="full" checked>
                    <label class="form-check-label" for="billingFull">Full Sale</label>
                </div>
            </div>

            <button class="btn btn-link text-decoration-none p-0"><i class="fa-solid fa-gear me-1"></i> More Settings</button>
        </div>
    </div>

    <!-- Additional Charges Modal -->
    <div class="modal fade" id="additionalChargesModal" tabindex="-1" aria-labelledby="additionalChargesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="additionalChargesModalLabel">Additional Charges</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-semibold">Enable Additional Charges</span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="additionalChargesToggle" checked>
                        </div>
                    </div>
                    <div class="additional-charge-block">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input class="form-check-input additional-charge-check" type="checkbox" checked>
                            <input type="text" class="form-control form-control-sm additional-charge-input" value="Shipping">
                            <select class="form-select form-select-sm additional-charge-tax">
                                <option>NONE</option>
                                <option>GST 5%</option>
                                <option>GST 12%</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3 ms-4">
                            <input class="form-check-input additional-charge-tax-check" type="checkbox">
                            <label class="form-check-label small">Enable tax for Shipping</label>
                        </div>
                    </div>
                    <div class="additional-charge-block">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input class="form-check-input additional-charge-check" type="checkbox" checked>
                            <input type="text" class="form-control form-control-sm additional-charge-input" value="Packaging">
                            <select class="form-select form-select-sm additional-charge-tax">
                                <option>NONE</option>
                                <option>GST 5%</option>
                                <option>GST 12%</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3 ms-4">
                            <input class="form-check-input additional-charge-tax-check" type="checkbox">
                            <label class="form-check-label small">Enable tax for Packaging</label>
                        </div>
                    </div>
                    <div class="additional-charge-block">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input class="form-check-input additional-charge-check" type="checkbox" checked>
                            <input type="text" class="form-control form-control-sm additional-charge-input" value="Adjustment">
                            <select class="form-select form-select-sm additional-charge-tax">
                                <option>NONE</option>
                                <option>GST 5%</option>
                                <option>GST 12%</option>
                            </select>
                        </div>
                        <div class="form-check form-switch ms-4">
                            <input class="form-check-input additional-charge-tax-check" type="checkbox">
                            <label class="form-check-label small">Enable tax for Adjustment</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger w-100">Save Details</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Tab Limit Modal -->
    <div class="modal fade" id="tabLimitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-dark border-secondary">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
                    <h5>Maximum Limit Reached</h5>
                    <p>You can open a maximum of 10 transactions at a time.</p>
                    <button type="button" class="btn btn-primary px-4 mt-2" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Confirmation Modal -->
    <div class="modal fade" id="closeConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Close Tab?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to close this tab? Your purchase will not be saved. Use the Save button on
                        the bottom right of the screen to save.</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-close-btn" class="btn btn-danger">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    window.items = @json(($saleItemsSource ?? collect())->concat($serviceItemsSource ?? collect())->values());
    window.parties = @json($parties ?? []);
    window.brokers = @json($brokers ?? []);
    window.bankAccounts = @json($bankAccounts ?? []);
    window.bankAccountRoutes = {
        store: "{{ route('bank-accounts.store') }}"
    };
    window.transactionSettings = {
        countEnabled: @json(\App\Models\AppSetting::getValue('transaction_items_count_enabled', '0') === '1'),
        poDetailsEnabled: @json(!empty($customerPoDetailsEnabled)),
        countLabel: 'Count'
    };
    window.itemRoutes = {
        index: "{{ url('dashboard/items') }}",
        servicesIndex: "{{ route('items.services') }}",
        store: "{{ url('dashboard/items') }}",
        categoryStore: "{{ url('dashboard/items/category') }}",
        unitsIndex: "{{ url('dashboard/items/units') }}",
        unitsStore: "{{ url('dashboard/items/units') }}"
    };

    window.saleStoreUrl = "{{ route('delivery-challan.store') }}";
    window.saleMethod = 'POST';

    // Default values
    window.editSaleData = null;
    window.sourceEstimateId = null;
    window.sourceSaleOrderId = null;
    window.sourceChallanId = null;
    window.sourceProformaId = null;

    // Optional doc type (avoid JS error)
   window.docType = "delivery_challan";

    @if(isset($challan))
        // Edit mode
        window.saleStoreUrl = "{{ route('delivery-challan.update', $challan->id) }}";
        window.saleMethod = 'PUT';
        window.editSaleData = @json($challan);

    @elseif(isset($duplicateChallan))
        // Duplicate challan
        window.editSaleData = @json($duplicateChallan);
    @endif
</script>

    <!-- Toast container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <div id="sale-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @include('components.bank-account-modal')
    <div class="modal fade" id="brokerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content broker-modal-card">
          <form id="brokerForm" action="{{ route('brokers.store') }}">
            @csrf
            <div class="modal-header broker-modal-header">
              <div>
                <h5 class="modal-title">Add Broker</h5>
                <p class="broker-modal-subtitle mb-0">Save broker details and commission rate.</p>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Broker Name</label>
                  <input type="text" class="form-control" name="name" id="brokerName" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input type="text" class="form-control" name="phone" id="brokerPhone">
                </div>
                <div class="col-md-6">
                  <label class="form-label">City</label>
                  <input type="text" class="form-control" name="city" id="brokerCity">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Commission Type</label>
                  <select class="form-select" name="commission_type" id="brokerCommissionType">
                    <option value="fixed">Fixed</option>
                    <option value="percent">Percent</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Commission Rate</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="commission_rate" id="brokerCommissionRate" value="0">
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="status" id="brokerStatus" checked>
                        <label class="form-check-label" for="brokerStatus">Keep broker active</label>
                    </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Total Brokerage</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="total_brokerage" id="brokerTotalBrokerage" value="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Paid Brokerage</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="paid_brokerage" id="brokerPaidBrokerage" value="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Remaining</label>
                  <input type="text" class="form-control" id="brokerRemainingBrokerage" value="0.00" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Address</label>
                  <input type="text" class="form-control" name="address" id="brokerAddress">
                </div>
                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea class="form-control" name="notes" id="brokerNotes" rows="3"></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer broker-modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn brokers-submit-btn">Save Broker</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="warehouseModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content broker-modal-card shadow-lg">
          <form id="warehouseForm" action="{{ route('warehouses.store') }}">
            @csrf
            <div class="modal-header broker-modal-header bg-gradient-primary text-white">
              <div class="d-flex align-items-center">
                <i class="fa-solid fa-warehouse me-3 fs-4"></i>
                <div>
                  <h5 class="modal-title mb-0">Add New Warehouse</h5>
                  <p class="broker-modal-subtitle mb-0 opacity-75">Configure warehouse details and management information</p>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
              <div class="row g-4">
                <!-- Basic Information -->
                <div class="col-12">
                  <h6 class="text-primary mb-3">
                    <i class="fa-solid fa-info-circle me-2"></i>Basic Information
                  </h6>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-building me-1"></i>Warehouse Name <span class="text-danger">*</span>
                  </label>
                  <input type="text" class="form-control form-control-lg" name="name" id="warehouseName" required placeholder="Enter warehouse name">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-phone me-1"></i>Phone
                  </label>
                  <input type="text" class="form-control form-control-lg" name="phone" id="warehousePhone" placeholder="Contact number">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-envelope me-1"></i>Email
                  </label>
                  <input type="email" class="form-control form-control-lg" name="email" id="warehouseEmail" placeholder="warehouse@example.com">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-city me-1"></i>City
                  </label>
                  <input type="text" class="form-control form-control-lg" name="city" id="warehouseCity" placeholder="City location">
                </div>

                <!-- Type and Capacity -->
                <div class="col-12">
                  <h6 class="text-primary mb-3">
                    <i class="fa-solid fa-cogs me-2"></i>Type & Capacity
                  </h6>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-tags me-1"></i>Warehouse Type
                  </label>
                  <select class="form-select form-select-lg" name="type" id="warehouseType">
                    <option value="storage">Storage</option>
                    <option value="main">Main Warehouse</option>
                    <option value="branch">Branch</option>
                    <option value="distribution">Distribution Center</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-weight-hanging me-1"></i>Capacity (Tons)
                  </label>
                  <input type="number" step="0.01" min="0" class="form-control form-control-lg" name="capacity" id="warehouseCapacity" placeholder="Storage capacity">
                </div>

                <!-- Handler Information -->
                <div class="col-12">
                  <h6 class="text-primary mb-3">
                    <i class="fa-solid fa-user-tie me-2"></i>Handler Information
                  </h6>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-user me-1"></i>Handler Name
                  </label>
                  <input type="text" class="form-control form-control-lg" name="handler_name" id="warehouseHandlerName" placeholder="Person in charge">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-phone-alt me-1"></i>Handler Phone
                  </label>
                  <input type="text" class="form-control form-control-lg" name="handler_phone" id="warehouseHandlerPhone" placeholder="Handler contact">
                </div>

                <!-- Address -->
                <div class="col-12">
                  <h6 class="text-primary mb-3">
                    <i class="fa-solid fa-map-marker-alt me-2"></i>Location & Notes
                  </h6>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-address-card me-1"></i>Address
                  </label>
                  <textarea class="form-control form-control-lg" name="address" id="warehouseAddress" rows="3" placeholder="Full address"></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">
                    <i class="fa-solid fa-sticky-note me-1"></i>Notes
                  </label>
                  <textarea class="form-control form-control-lg" name="notes" id="warehouseNotes" rows="2" placeholder="Additional notes"></textarea>
                </div>

                <!-- Status -->
                <div class="col-12">
                  <h6 class="text-primary mb-3">
                    <i class="fa-solid fa-toggle-on me-2"></i>Status
                  </h6>
                </div>
                <div class="col-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="warehouseIsActive" checked>
                    <label class="form-check-label fw-semibold" for="warehouseIsActive">
                      <i class="fa-solid fa-check-circle text-success me-2"></i>Active Warehouse
                    </label>
                  </div>
                  <small class="text-muted">Inactive warehouses won't be available for selection</small>
                </div>

                <input type="hidden" name="responsible_user_id" value="{{ auth()->id() ?? 0 }}">
              </div>
            </div>
            <div class="modal-footer broker-modal-footer bg-light">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fa-solid fa-times me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-save me-2"></i>Save Warehouse
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="termsConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Terms &amp; Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="terms-modal-field">
                        <label class="terms-modal-label">Terms and Conditions</label>
                        <input type="text" class="form-control" id="termsConditionNameInput" placeholder="">
                        <div class="terms-modal-help">You can select the term based on the header you select here</div>
                    </div>
                    <div class="terms-modal-field">
                        <label class="terms-modal-label">Description</label>
                        <textarea class="form-control terms-modal-textarea" id="termsConditionDescriptionInput" placeholder="Paste/Write your terms and conditions here"></textarea>
                    </div>
                    <div class="terms-modal-field mb-0">
                        <label class="terms-modal-label">Applicable for:</label>
                        <div class="terms-modal-grid">
                            <label class="terms-modal-check"><input type="checkbox" value="invoice" class="terms-applicable-check"> Sale Invoice</label>
                            <label class="terms-modal-check"><input type="checkbox" value="sale_order" class="terms-applicable-check"> Sale Order</label>
                            <label class="terms-modal-check"><input type="checkbox" value="delivery_challan" class="terms-applicable-check"> Delivery Challan</label>
                            <label class="terms-modal-check"><input type="checkbox" value="estimate" class="terms-applicable-check"> Estimation/Quotation</label>
                            <label class="terms-modal-check"><input type="checkbox" value="purchase_bill" class="terms-applicable-check"> Purchase Bill</label>
                            <label class="terms-modal-check"><input type="checkbox" value="purchase_order" class="terms-applicable-check"> Purchase Order</label>
                            <label class="terms-modal-check"><input type="checkbox" value="proforma" class="terms-applicable-check"> Proforma Invoice</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer terms-modal-actions">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTermsConditionBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Logic -->
    <script>
        window.transactionTermsTemplates = @json($termsConditionTemplates ?? []);
        window.saleFormSettings = @json($saleFormSettings ?? (json_decode(\App\Models\AppSetting::getValue('sale_form_settings', '{}'), true) ?: []));
        window.itemFormSettings = @json($itemFormSettings ?? (json_decode(\App\Models\AppSetting::getValue('item_form_settings', '{}'), true) ?: []));
        window.termsConditionTemplates = @json($termsConditionTemplates ?? []);
        window.saleSettingsUpdateUrl = @json(route('sale.settings.update'));
        window.termsConditionStoreUrl = @json(route('sale.terms-conditions.store'));
        window.saleNextNumberUrl = @json(route('sale.next-number'));
    </script>
    <script src="{{ asset('js/saleform_script.js') }}"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/bank-account-modal.js') }}"></script>
    <script src="{{ asset('js/transaction-count-column.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const unitButtons = document.querySelectorAll('.unit-option');
            const unitInput = document.getElementById('newItemUnit');
            const unitBtn = document.getElementById('newItemUnitBtn');
            const assignCodeBtn = document.getElementById('assignItemCodeBtn');
            const itemNameInput = document.getElementById('newItemName');
            const wholesaleToggle = document.getElementById('toggleWholesalePricing');
            const wholesaleSection = document.querySelector('.wholesale-pricing');
            const imagePickerCard = document.querySelector('.open-item-image-picker');
            let currentImageObjectUrl = null;

            if (unitButtons && unitInput && unitBtn) {
                unitButtons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const unit = this.dataset.unit || '';
                        unitInput.value = unit;
                        unitBtn.textContent = unit || 'Select Unit';
                    });
                });
            }

            if (assignCodeBtn && itemNameInput) {
                assignCodeBtn.addEventListener('click', function () {
                    const name = itemNameInput.value.trim();
                    const slug = name ? name.toUpperCase().replace(/[^A-Z0-9]+/g, '') : 'ITEM';
                    const suffix = Math.floor(Math.random() * 9000) + 1000;
                    document.getElementById('newItemCode').value = `${slug ? slug.substring(0, 6) : 'ITEM'}-${suffix}`;
                });
            }

            const itemTypeToggle = document.getElementById('newItemTypeToggle');
            const itemTypeHidden = document.getElementById('newItemType');
            const productLabel = document.getElementById('newItemProductLabel');
            const itemNameLabel = document.getElementById('newItemNameLabel');
            const stockTabButton = document.getElementById('stock-tab');
            const stockTabPane = document.getElementById('stock-tab-pane');
            const purchaseSection = document.getElementById('purchase-sec');

            if (itemTypeToggle && itemTypeHidden) {
                itemTypeToggle.addEventListener('change', function () {
                    const isService = this.checked;
                    itemTypeHidden.value = isService ? 'service' : 'product';
                    productLabel.textContent = isService ? 'Service' : 'Product';
                    itemNameLabel.textContent = isService ? 'Service Name *' : 'Item Name *';
                    if (stockTabButton && stockTabPane) {
                        stockTabButton.style.display = isService ? 'none' : '';
                        stockTabPane.style.display = isService ? 'none' : '';
                    }
                    if (purchaseSection) {
                        purchaseSection.style.display = isService ? 'none' : '';
                    }
                    const pricingTabEl = document.getElementById('pricing-tab');
                    if (pricingTabEl) {
                        const pricingTab = bootstrap.Tab.getOrCreateInstance(pricingTabEl);
                        pricingTab.show();
                    }
                });
            }

            const newItemImageInput = document.getElementById('newItemImage');
            const newItemImageThumb = document.getElementById('newItemImageThumb');
            const newItemImageLabel = document.getElementById('newItemImageLabel');

            if (imagePickerCard && newItemImageInput) {
                imagePickerCard.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    newItemImageInput.click();
                });
            }

            if (newItemImageInput) {
                newItemImageInput.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                newItemImageInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (currentImageObjectUrl) {
                        URL.revokeObjectURL(currentImageObjectUrl);
                        currentImageObjectUrl = null;
                    }
                    if (!file) {
                        newItemImageThumb.innerHTML = '<i class="fa-regular fa-image fa-2x text-secondary"></i>';
                        newItemImageThumb.style.border = '1.5px solid #93c5fd';
                        newItemImageLabel.textContent = 'Click to choose image';
                        return;
                    }
                    currentImageObjectUrl = URL.createObjectURL(file);
                    newItemImageThumb.innerHTML = `<img src="${currentImageObjectUrl}" style="width:100%;height:100%;object-fit:cover;"/>`;
                    newItemImageThumb.style.border = '1.5px solid #2563eb';
                    newItemImageLabel.textContent = file.name;
                });
            }

            if (wholesaleToggle && wholesaleSection) {
                wholesaleToggle.addEventListener('click', function () {
                    wholesaleSection.classList.toggle('d-none');
                    this.textContent = wholesaleSection.classList.contains('d-none') ? '+ Add Wholesale Price' : '- Remove Wholesale Price';
                });
            }

            // Initialize editable table headers
            initializeEditableHeaders();
        });

        function initializeEditableHeaders() {
            const tableHeaders = document.querySelectorAll('.item-table th');
            const storageKey = 'itemTableHeaders';
            const defaultHeaders = {};

            // Build default headers and add editable class
            tableHeaders.forEach((th, index) => {
                const text = th.textContent.trim();
                if (text && text !== '+' && !th.classList.contains('add-col')) {
                    th.classList.add('editable-header');
                    defaultHeaders[index] = text;

                    th.addEventListener('click', function(e) {
                        if (e.target.tagName === 'BUTTON') return;
                        editHeader(this, index);
                    });
                }
            });

            // Load saved headers from localStorage
            loadSavedHeaders();
        }

        function editHeader(headerCell, index) {
            const currentText = headerCell.textContent.trim();
            const newText = prompt('Edit column name:', currentText);

            if (newText !== null && newText.trim() !== '') {
                const trimmedText = newText.trim();
                headerCell.textContent = trimmedText;

                // Save to localStorage
                saveHeaderToStorage(index, trimmedText);
            }
        }

        function saveHeaderToStorage(index, text) {
            const storageKey = 'itemTableHeaders';
            let savedHeaders = JSON.parse(localStorage.getItem(storageKey) || '{}');
            savedHeaders[index] = text;
            localStorage.setItem(storageKey, JSON.stringify(savedHeaders));
        }

        function loadSavedHeaders() {
            const storageKey = 'itemTableHeaders';
            const savedHeaders = JSON.parse(localStorage.getItem(storageKey) || '{}');

            const tableHeaders = document.querySelectorAll('.item-table th.editable-header');
            tableHeaders.forEach((th, index) => {
                // Find the actual index considering hidden headers
                let actualIndex = 0;
                let realIndex = 0;
                th.parentElement.querySelectorAll('th').forEach((h, i) => {
                    if (h === th) {
                        actualIndex = i;
                    }
                });

                if (savedHeaders[actualIndex]) {
                    th.textContent = savedHeaders[actualIndex];
                }
            });
        }
    </script>
     <div class="container">
        @yield('content')
    </div>

@section('modals')
<!-- MODAL: ADD PARTY -->
 <div class="modal fade" id="addPartyModal" tabindex="-1" aria-labelledby="addPartyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPartyModalLabel"><i class="fa-solid fa-user-plus me-2"></i>Add Party</h5>
        <div class="d-flex align-items-center gap-2" style="margin-left:79%;">
          <button class="btn btn-sm btn-outline-secondary" type="button" id="partyModalSettingsTrigger" title="Settings"><i class="fa-solid fa-gear"></i></button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body">
        <form id="addPartyForm">
          @csrf
          <div class="row g-3 mb-4">
            <div class="col-md-4" data-party-setting="name">
              <div class="floating-input-wrapper">
                <input type="text" name="name" class="meta-control" placeholder=" " id="partyNameInput" required>
                <label>Party Name <span class="text-danger">*</span></label>
              </div>
            </div>
            <div class="col-md-4" data-party-setting="phone">
              <div class="floating-input-wrapper">
                <input type="tel" name="phone" class="meta-control" placeholder=" " id="partyPhoneInput">
                <label>Phone Number</label>
              </div>
            </div>
            <div class="col-md-4" data-party-setting="phone_2">
              <div class="floating-input-wrapper">
                <input type="tel" name="phone_number_2" class="meta-control" placeholder=" " id="partyPhone2Input">
                <label>Phone Number 2</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="floating-input-wrapper">
                <input type="text" name="ptcl_number" class="meta-control" placeholder=" " id="partyPtclInput">
                <label>PTCL Number</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="floating-input-wrapper">
                <input type="text" name="city" class="meta-control" placeholder=" " id="partyCityInput">
                <label>City</label>
              </div>
            </div>


            <div class="col-md-4">
  <label class="form-label fw-600">Party Group</label>

  <div class="position-relative">
    <button type="button" class="form-control text-start" id="partyGroupTrigger">
      <span id="partyGroupText">Select group</span>
      <i class="fa fa-chevron-down float-end mt-1"></i>
    </button>

    <input type="hidden" name="party_group" id="partyGroupInput">

      <div id="partyGroupMenu" class="border bg-white position-absolute w-100 mt-1 d-none" style="z-index:999;">
      <button type="button" class="dropdown-item text-primary" id="addNewGroupBtn">+ New Group</button>
      <div id="partyGroupList">
        @foreach($partyGroups as $partyGroup)
          <button type="button" class="dropdown-item" data-group="{{ $partyGroup->name }}">{{ $partyGroup->name }}</button>
        @endforeach
      </div>
      </div>
    </div>
  </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs" id="partyModalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="party-address-tab" data-bs-toggle="tab" data-bs-target="#partyAddressPane" type="button" role="tab" aria-controls="partyAddressPane" aria-selected="true">
                <i class="fa-solid fa-location-dot me-1"></i> Address
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-credit-tab" data-bs-toggle="tab" data-bs-target="#partyCreditPane" type="button" role="tab" aria-controls="partyCreditPane" aria-selected="false">
                <i class="fa-solid fa-credit-card me-1"></i> Credit & Balance
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="party-additional-tab" data-bs-toggle="tab" data-bs-target="#partyAdditionalPane" type="button" role="tab" aria-controls="partyAdditionalPane" aria-selected="false">
                <i class="fa-solid fa-sliders me-1"></i> Additional Fields
              </button>
            </li>
          </ul>

          <div class="tab-content pt-3" id="partyModalTabContent">
            <!-- Address Tab -->
            <div class="tab-pane fade show active" id="partyAddressPane" role="tabpanel" aria-labelledby="party-address-tab">
              <div class="row g-3">
                <div class="col-md-6" data-party-setting="email">
                  <div class="floating-input-wrapper">
                    <input type="email" name="email" class="meta-control" placeholder=" " value="">
                    <label>Email ID</label>
                  </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                  <div class="floating-input-wrapper">
                    <textarea id="partyAddressInput" class="meta-control" name="address" rows="3" placeholder=" "></textarea>
                    <label>Address</label>
                  </div>
                </div>
                <div class="col-md-6" data-party-setting="billing_address">
                  <div class="floating-input-wrapper">
                    <textarea id="billingAddress" class="meta-control" name="billing_address" rows="3" placeholder=" "></textarea>
                    <label>Billing Address</label>
                  </div>
                </div>
                <div class="col-md-6" data-party-setting="shipping_address">
                  <div class="floating-input-wrapper">
                    <textarea id="shippingAddress" class="meta-control" name="shipping_address" rows="3" placeholder=" "></textarea>
                    <label>Shipping Address</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Credit & Balance Tab -->
          <div class="tab-pane fade" id="partyCreditPane" role="tabpanel" aria-labelledby="party-credit-tab">
            <div class="row g-3">
              <div class="col-md-4" data-party-setting="opening_balance">
                <label class="form-label">Opening Balance</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" name="opening_balance" class="form-control" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-4" data-party-setting="as_of_date">
                <label class="form-label">As Of Date</label>
                <input type="date" name="as_of_date" class="form-control" value="{{ date('Y-m-d') }}">
              </div>
              <div class="col-md-4" data-party-setting="credit_limit">
                <label class="form-label d-block">Credit Limit</label>
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" name="credit_limit_enabled" type="checkbox" id="creditLimitSwitch">
                  <label class="form-check-label" for="creditLimitSwitch">Enable</label>
                </div>
                <div class="input-group mt-2 is-hidden" id="creditLimitAmountWrap">
                  <span class="input-group-text">Rs</span>
                  <input type="number" name="credit_limit_amount" class="form-control" placeholder="Enter credit limit" id="creditLimitAmountInput" min="0" step="0.01">
                </div>
              </div>
              <div class="col-md-4" data-party-setting="due_days">
                <label class="form-label">Due Days</label>
                <input type="number" name="due_days" class="form-control" placeholder="e.g. 5, 10, 30" min="1" max="100" id="partyDueDaysInput">
              </div>
            </div>

            <!-- To Receive / To Pay Options at the bottom -->
            <div class="mt-4" data-party-setting="transaction_type">
              <label class="form-label d-block">Transaction Type</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="toReceive" value="receive">
                <label class="form-check-label" for="toReceive">To Receive</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="toPay" value="pay">
                <label class="form-check-label" for="toPay">To Pay</label>
              </div>
            </div>

            <div class="row g-3 mt-3" data-party-setting="party_type">
              <div class="col-md-6">
                <label class="form-label fw-600">Party Type</label>

                <div class="form-check">
                  <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="customerParty" value="customer">
                  <label class="form-check-label" for="customerParty">Customer</label>
                </div>

                  <div class="form-check">
                    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="supplierParty" value="supplier">
                    <label class="form-check-label" for="supplierParty">Supplier</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input party-type-checkbox" type="checkbox" name="party_type[]" id="brokerParty" value="broker">
                    <label class="form-check-label" for="brokerParty">Broker</label>
                  </div>
                </div>
              </div>
          </div>

            <!-- Additional Fields Tab -->
            <div class="tab-pane fade" id="partyAdditionalPane" role="tabpanel" aria-labelledby="party-additional-tab" data-party-setting="additional_fields">
              <p class="text-muted mb-3" style="font-size:13px;">Add custom fields to track additional information.</p>
              <div class="row g-3">
                @for($i=1; $i<=4; $i++)
                <div class="col-md-6">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="customField{{$i}}Check">
                    <label class="form-check-label" for="customField{{$i}}Check">Custom Field {{$i}}</label>
                  </div>
                  <input type="text" name="custom_fields[]" class="form-control form-control-sm" placeholder="Field name">
                </div>

                <input type="hidden" id="transactionTypeValue" name="transaction_type">
                @endfor

              </div>
            </div>
          </div>


          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" id="btnSaveNewParty">
              <i class="fa-solid fa-plus me-1"></i> Save & New
            </button>
            <button type="button" class="btn btn-primary" id="btnSaveParty">
              <i class="fa-solid fa-check me-1"></i> Save
            </button>
 <button type="button" class="btn btn-primary" id="btnUpdateParty" style="display:none;">Update</button>
    <button type="button" class="btn btn-danger" id="btnDeleteParty" style="display:none;">Delete</button>
          </div>
        </form>

      </div>
    </div>
</div>

</div>

<div class="modal fade" id="partyGroupModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">New Party Group</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="newGroupName" class="form-control" placeholder="Enter group name">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="saveGroupBtn">Save</button>
      </div>
    </div>
  </div>
</div>

@php
    $salesModalUnits = [];

    if (\Illuminate\Support\Facades\Schema::hasTable('item_units')) {
        $salesModalUnits = \App\Models\ItemUnit::query()
            ->where('is_active', true)
            ->orderBy('short_name')
            ->get(['name', 'short_name']);
    }

@endphp
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header align-items-start justify-content-between">
        <div>
          <h5 class="modal-title">Add Item</h5>
          <p class="text-muted small mb-0">Create item details, pricing, stock and description.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span id="newItemProductLabel" class="text-primary fw-semibold">Product</span>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="newItemTypeToggle">
            <label class="form-check-label" for="newItemTypeToggle">Service</label>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addItemForm">
          <input type="hidden" id="newItemType" name="item_type" value="product">
          <div class="row g-3">
            <div class="col-md-5">
              <label for="newItemName" class="form-label" id="newItemNameLabel">Item Name *</label>
              <input type="text" class="form-control" id="newItemName" required>
            </div>
            <div class="col-md-4">
              <label for="newItemCategory" class="form-label">Category</label>
              <select class="form-select" id="newItemCategory">
                <option value="">Select Category</option>
                @foreach($categories ?? [] as $category)
                  <option value="{{ $category->id ?? '' }}">{{ $category->name ?? '' }}</option>
                @endforeach
                <option value="__add_new__">+ Add Category</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Unit</label>
              <div class="w-100">
                <button class="btn btn-outline-primary w-100 text-start" type="button" id="newItemUnitBtn">
                  Select Unit
                </button>
                <input type="hidden" id="newItemUnit" name="unit">
                <input type="hidden" id="newItemSecondaryUnit" name="secondary_unit">
                <input type="hidden" id="newItemUnitConversionRate" name="unit_conversion_rate">
              </div>
            </div>
            <div class="col-md-6">
              <label for="newItemCode" class="form-label">Item Code</label>
              <div class="input-group">
                <input type="text" class="form-control" id="newItemCode" placeholder="Enter item code">
                <button type="button" class="btn btn-outline-secondary" id="assignItemCodeBtn">Assign</button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Item Image</label>
              <div class="border rounded-3 p-3 text-center h-100 d-flex flex-column justify-content-center align-items-center open-item-image-picker" style="cursor:pointer;">
                <div id="newItemImageThumb" style="width:68px; height:68px; border:1.5px solid #93c5fd; border-radius:12px; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%); overflow:hidden;">
                  <i class="fa-regular fa-image fa-2x text-secondary"></i>
                </div>
                <div class="text-secondary mt-2" id="newItemImageLabel">Click to choose image</div>
                <input type="file" class="form-control d-none" id="newItemImage" accept="image/*">
              </div>
            </div>
          </div>

          <ul class="nav nav-tabs mt-4" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing-tab-pane" type="button" role="tab" aria-controls="pricing-tab-pane" aria-selected="true">Pricing</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock-tab-pane" type="button" role="tab" aria-controls="stock-tab-pane" aria-selected="false">Stock</button>
            </li>
          </ul>

          <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="pricing-tab-pane" role="tabpanel" aria-labelledby="pricing-tab">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="newItemSalePrice" class="form-label">Sale Price</label>
                  <input type="number" class="form-control" id="newItemSalePrice" min="0" step="0.01" placeholder="Sale Price">
                </div>
                <div class="col-md-6">
                  <label for="newItemPurchasePrice" class="form-label">Purchase Price</label>
                  <input type="number" class="form-control" id="newItemPurchasePrice" min="0" step="0.01" placeholder="Purchase Price">
                </div>
                <div class="col-12">
                  <div class="border rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div class="fw-semibold">Wholesale Pricing</div>
                      <button type="button" class="btn btn-link btn-sm p-0" id="toggleWholesalePricing">+ Add Wholesale Price</button>
                    </div>
                    <div class="row g-2 wholesale-pricing d-none">
                      <div class="col-md-6">
                        <label for="newItemWholesalePrice" class="form-label">Wholesale Price</label>
                        <input type="number" class="form-control" id="newItemWholesalePrice" min="0" step="0.01" placeholder="Wholesale Price">
                      </div>
                      <div class="col-md-6">
                        <label for="newItemWholesaleMinQty" class="form-label">Minimum Wholesale Qty</label>
                        <input type="number" class="form-control" id="newItemWholesaleMinQty" min="0" step="1" placeholder="Minimum Qty">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="stock-tab-pane" role="tabpanel" aria-labelledby="stock-tab">
              <div class="row g-3">
                <div class="col-md-4">
                  <label for="newItemStock" class="form-label">Opening Quantity</label>
                  <input type="number" class="form-control" id="newItemStock" min="0" step="1" placeholder="Opening Qty">
                </div>
                <div class="col-md-4">
                  <label for="newItemAtPrice" class="form-label">At Price</label>
                  <input type="number" class="form-control" id="newItemAtPrice" min="0" step="0.01" placeholder="At Price">
                </div>
                <div class="col-md-4">
                  <label for="newItemAsOfDate" class="form-label">As Of Date</label>
                  <input type="date" class="form-control" id="newItemAsOfDate" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                  <label for="newItemBagWeight" class="form-label">Bag Weight</label>
                  <input type="number" class="form-control" id="newItemBagWeight" min="0" step="0.01" placeholder="Enter Bag Weight (KG)">
                </div>
                <div class="col-md-6">
                  <label for="newItemMinStock" class="form-label">Min Stock To Maintain</label>
                  <input type="number" class="form-control" id="newItemMinStock" min="0" step="1" placeholder="Min Stock">
                </div>
                <div class="col-md-6">
                  <label for="newItemLocation" class="form-label">Location</label>
                  <input type="text" class="form-control" id="newItemLocation" placeholder="Location">
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Item Images</label>
                  <div class="item-stock-images-trigger open-item-stock-images-picker">
                    <span><i class="fa-regular fa-camera me-2"></i>Add Item Images</span>
                  </div>
                  <input type="file" class="d-none" id="newItemStockImages" accept="image/*" multiple>
                  <div id="newItemStockImagesList" class="item-stock-images-list"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-4">
            <div class="col-12">
              <label for="newItemDescription" class="form-label">Description</label>
              <textarea class="form-control" id="newItemDescription" rows="4" placeholder="Item description"></textarea>
            </div>
          </div>

          <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveNewItemBtn">Save Item</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-stack-top" id="selectItemUnitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="newItemBaseUnitSelect" class="form-label text-uppercase small fw-bold">Base Unit</label>
            <select class="form-select" id="newItemBaseUnitSelect">
              <option value="">Select Base Unit</option>
              @foreach($salesModalUnits as $unit)
                @php
                  $unitShortName = strtoupper($unit['short_name'] ?? $unit->short_name ?? '');
                  $unitName = strtoupper($unit['name'] ?? $unit->name ?? '');
                  $unitLabel = $unitName && $unitName !== $unitShortName ? $unitName . ' (' . $unitShortName . ')' : $unitShortName;
                @endphp
                <option value="{{ $unitShortName }}">{{ $unitLabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label for="newItemSecondaryUnitSelect" class="form-label text-uppercase small fw-bold">Secondary Unit</label>
            <select class="form-select" id="newItemSecondaryUnitSelect">
              <option value="">Select Secondary Unit</option>
              @foreach($salesModalUnits as $unit)
                @php
                  $unitShortName = strtoupper($unit['short_name'] ?? $unit->short_name ?? '');
                  $unitName = strtoupper($unit['name'] ?? $unit->name ?? '');
                  $unitLabel = $unitName && $unitName !== $unitShortName ? $unitName . ' (' . $unitShortName . ')' : $unitShortName;
                @endphp
                <option value="{{ $unitShortName }}">{{ $unitLabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-link text-primary p-0 open-add-unit-from-selector">+ Add Unit</button>
          </div>
          <div class="col-12">
            <label for="newItemUnitConversionInput" class="form-label fw-semibold">Conversion Rate</label>
            <div class="item-unit-conversion-row">
              <span class="base-unit-preview">1 Base Unit</span>
              <span>=</span>
              <input type="number" class="form-control" id="newItemUnitConversionInput" min="0" step="0.0001" value="0">
              <span class="secondary-unit-preview">Secondary Unit</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="saveSelectedUnitsBtn">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-stack-top" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="quickCategoryName" class="form-label">Category Name</label>
          <input type="text" class="form-control" id="quickCategoryName" placeholder="Enter category name">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveQuickCategoryBtn">Save Category</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-stack-top" id="addUnitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label for="quickUnitName" class="form-label">Unit Name</label>
            <input type="text" class="form-control" id="quickUnitName" placeholder="e.g. KILOGRAMS">
          </div>
          <div class="col-md-4">
            <label for="quickUnitShortName" class="form-label">Short Name</label>
            <input type="text" class="form-control" id="quickUnitShortName" placeholder="e.g. KG">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveQuickUnitBtn">Save Unit</button>
      </div>
    </div>
  </div>
</div>
@endsection
    @yield('modals')

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const partySelect = document.querySelector(".party-select");
    const addModalEl = document.getElementById('addPartyModal');
    const addModal = new bootstrap.Modal(addModalEl);

    if (partySelect) {
        partySelect.addEventListener("change", function () {
            if (this.value === "__new") {
                addModal.show();

                // Optional: Reset modal har bar open hone pe
                document.getElementById("addPartyForm").reset();
            }
        });
    }

    if (addModalEl) {
        addModalEl.addEventListener('shown.bs.modal', function () {
            const addressTabEl = document.getElementById('party-address-tab');
            const addressPaneEl = document.getElementById('partyAddressPane');
            const creditTabEl = document.getElementById('party-credit-tab');
            const creditPaneEl = document.getElementById('partyCreditPane');
            const additionalTabEl = document.getElementById('party-additional-tab');
            const additionalPaneEl = document.getElementById('partyAdditionalPane');

            [addressTabEl, creditTabEl, additionalTabEl].forEach(tab => {
                if (tab) {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                }
            });

            [addressPaneEl, creditPaneEl, additionalPaneEl].forEach(pane => {
                if (pane) {
                    pane.classList.remove('show', 'active');
                }
            });

            if (addressTabEl) {
                addressTabEl.classList.add('active');
                addressTabEl.setAttribute('aria-selected', 'true');
            }
            if (addressPaneEl) {
                addressPaneEl.classList.add('show', 'active');
                addressPaneEl.style.display = '';
            }

            if (addressTabEl && bootstrap.Tab) {
                const addressTab = bootstrap.Tab.getOrCreateInstance(addressTabEl);
                addressTab.show();
            }
        });
    }

    // Party search functionality handled by saleform_script.js
});

document.addEventListener("DOMContentLoaded", function () {

    const trigger = document.getElementById("partyGroupTrigger");
    const menu = document.getElementById("partyGroupMenu");
    const list = document.getElementById("partyGroupList");
    const input = document.getElementById("partyGroupInput");
    const text = document.getElementById("partyGroupText");

    const groupModal = new bootstrap.Modal(document.getElementById('partyGroupModal'));
    window.salePartyGroups = window.salePartyGroups || Array.from((list?.querySelectorAll('.dropdown-item') || []))
        .filter((btn) => btn.id !== 'addNewGroupBtn')
        .map((btn) => btn.textContent.trim())
        .filter(Boolean);

    if (!trigger || !menu || !list || !input || !text) {
        return;
    }

    // Toggle dropdown
    trigger.addEventListener("click", () => {
        menu.classList.toggle("d-none");
    });

    // Close outside
    document.addEventListener("click", (e) => {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add("d-none");
        }
    });

    // Render groups
    function renderGroups() {
        list.innerHTML = "";
        window.salePartyGroups.forEach(g => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "dropdown-item";
            btn.dataset.group = g;
            btn.textContent = g;

            btn.onclick = () => {
                input.value = g;
                text.textContent = g;
                menu.classList.add("d-none");
            };

            list.appendChild(btn);
        });
    }

    renderGroups();

    list.addEventListener("click", function (event) {
        const btn = event.target.closest("button.dropdown-item");
        if (!btn || btn.id === "addNewGroupBtn") return;

        input.value = btn.dataset.group || btn.textContent.trim();
        text.textContent = btn.dataset.group || btn.textContent.trim();
        menu.classList.add("d-none");
    });

    // Open modal
    const addNewGroupBtn = document.getElementById("addNewGroupBtn");
    if (addNewGroupBtn) {
        addNewGroupBtn.onclick = () => {
            groupModal.show();
        };
    }

    const partyGroupsStoreUrl = '{{ route("party-groups.store") }}';

    // Save group
    const saveGroupBtn = document.getElementById("saveGroupBtn");
    if (saveGroupBtn) {
      saveGroupBtn.onclick = async () => {
        const nameEl = document.getElementById("newGroupName");
        const name = nameEl.value.trim();

        if (!name) return alert("Enter group name");

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(partyGroupsStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name }),
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Unable to save party group');
            }

            const groupName = result.partyGroup?.name || name;
            if (!window.salePartyGroups.includes(groupName)) {
                window.salePartyGroups.push(groupName);
            }
            renderGroups();

            input.value = groupName;
            text.textContent = groupName;

            nameEl.value = "";
            groupModal.hide();
        } catch (error) {
            console.error(error);
            alert('Could not save party group. Please try again.');
        }
      };
    }

});

document.addEventListener("DOMContentLoaded", function () {
    const addModalEl = document.getElementById('addPartyModal');
    const addModal = new bootstrap.Modal(addModalEl);
    const saveBtn = document.getElementById("btnSaveParty");
    const saveNewBtn = document.getElementById("btnSaveNewParty");

    // Handle modal close - clean up backdrops to prevent black screen
    addModalEl.addEventListener('hidden.bs.modal', function () {
        // Remove any remaining backdrops
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        // Reset overflow if it was set
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    // Handle modal show - ensure clean state
    addModalEl.addEventListener('show.bs.modal', function () {
        // Remove any orphaned backdrops before opening
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    });




    function getPartyData() {
        const form = document.getElementById("addPartyForm");
        return new FormData(form);
    }

   function applyPartyDueDays(partyRecord = {}) {
    const dealDaysSelect = document.querySelector(".due-days-select");
    const dealDaysCustomInput = document.querySelector(".due-days-custom");
    const dueDateInput = document.querySelector(".due-date");
    const baseDateInput = document.querySelector(".invoice-date") || document.querySelector(".order-date");
    if (!dealDaysSelect || !dueDateInput || !baseDateInput) {
        return;
    }

    const dueDays = Number(partyRecord.due_days || 0);
    const baseDateValue = baseDateInput.value;
    const allowedDays = ['0', '5', '10', '15', '30', '45'];

    if (dueDays > 0) {
        if (allowedDays.includes(String(dueDays))) {
            dealDaysSelect.value = String(dueDays);
            dealDaysCustomInput?.classList.add('d-none');
            if (dealDaysCustomInput) dealDaysCustomInput.value = '';
        } else {
            dealDaysSelect.value = 'custom';
            dealDaysCustomInput?.classList.remove('d-none');
            if (dealDaysCustomInput) dealDaysCustomInput.value = dueDays;
        }
    } else {
        dealDaysSelect.value = '0';
        dealDaysCustomInput?.classList.add('d-none');
        if (dealDaysCustomInput) dealDaysCustomInput.value = '';
    }

    if (!baseDateValue) {
        return;
    }

    const dueDate = new Date(baseDateValue);
    if (Number.isNaN(dueDate.getTime())) {
        return;
    }

    if (dueDays > 0) {
        dueDate.setDate(dueDate.getDate() + dueDays);
    }
    const yyyy = dueDate.getFullYear();
    const mm = String(dueDate.getMonth() + 1).padStart(2, '0');
    const dd = String(dueDate.getDate()).padStart(2, '0');
    dueDateInput.value = `${dd}/${mm}/${yyyy}`;
   }

   function saveParty(closeAfterSave = true) {
    const form = document.getElementById("addPartyForm");
    const data = new FormData(form);

    // Transaction type fix
    const toReceive = document.getElementById("toReceive").checked;
    const toPay = document.getElementById("toPay").checked;
    if(toReceive) data.set("transaction_type", "receive");
    else if(toPay) data.set("transaction_type", "pay");

    // Credit limit fix
    const creditSwitch = document.getElementById("creditLimitSwitch");
    data.set("credit_limit_enabled", creditSwitch.checked ? 1 : 0);

    fetch("{{ route('parties.store') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Accept": "application/json"   // important!
        },
        body: data
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            const party = res.party || {};
         const partyRecord = {
    name:             selectedParty.name             ?? option.dataset.name         ?? name,
    phone:            selectedParty.phone            ?? option.dataset.phone        ?? "",
    phone_number_2:   selectedParty.phone_number_2   ?? option.dataset.phoneNumber2 ?? "",
    ptcl_number:      selectedParty.ptcl_number      ?? option.dataset.ptcl         ?? "",
    email:            selectedParty.email            ?? option.dataset.email        ?? "",
    city:             selectedParty.city             ?? option.dataset.city         ?? "",
    party_group:      selectedParty.party_group      ?? option.dataset.partyGroup   ?? "",
    address:          selectedParty.address          ?? option.dataset.address      ?? "",
    billing_address:  selectedParty.billing_address  ?? option.dataset.billing      ?? "",
    shipping_address: selectedParty.shipping_address ?? option.dataset.shipping     ?? "",
    due_days:         selectedParty.due_days         ?? option.dataset.dueDays      ?? "",
};

            if (partyRecord.party_group) {
                window.salePartyGroups = window.salePartyGroups || [];
                if (!window.salePartyGroups.includes(partyRecord.party_group)) {
                    window.salePartyGroups.push(partyRecord.party_group);
                }

                const partyGroupList = document.getElementById('partyGroupList');
                if (partyGroupList && !partyGroupList.querySelector(`[data-group="${partyRecord.party_group}"]`)) {
                    const groupBtn = document.createElement('button');
                    groupBtn.type = 'button';
                    groupBtn.className = 'dropdown-item';
                    groupBtn.dataset.group = partyRecord.party_group;
                    groupBtn.textContent = partyRecord.party_group;
                    groupBtn.onclick = () => {
                        const groupInput = document.getElementById('partyGroupInput');
                        const groupText = document.getElementById('partyGroupText');
                        if (groupInput) groupInput.value = partyRecord.party_group;
                        if (groupText) groupText.textContent = partyRecord.party_group;
                    };
                    partyGroupList.appendChild(groupBtn);
                }
            }

            if (partyRecord.id) {
                window.parties = Array.isArray(window.parties) ? window.parties.filter(p => String(p.id) !== String(partyRecord.id)) : [];
                window.parties.push(partyRecord);

                const dropdownMenu = document.getElementById("partyDropdownMenu");
                const partyIdInput = document.querySelector(".party-id");
                const dropdownBtn = document.getElementById("partyDropdownBtn");
                const balanceDisplay = document.getElementById("partyBalanceDisplay");

                if (dropdownMenu) {
                    const existing = dropdownMenu.querySelector(`.party-option[data-id="${partyRecord.id}"]`);
                    if (existing) {
                        existing.closest('li')?.remove();
                    }

                    const optionHtml = `
                      <li>
                        <a class="dropdown-item d-flex justify-content-between align-items-start party-option"
                           href="#"
                           data-id="${partyRecord.id}"
                           data-name="${partyRecord.name}"
                           data-phone="${partyRecord.phone}"
                           data-phone-number-2="${partyRecord.phone_number_2 || ''}"
                           data-city="${partyRecord.city}"
                           data-ptcl="${partyRecord.ptcl_number}"
                           data-email="${partyRecord.email || ''}"
                           data-address="${partyRecord.address.replace(/"/g, '&quot;')}"
                           data-billing="${partyRecord.billing_address.replace(/"/g, '&quot;')}"
                           data-shipping="${partyRecord.shipping_address.replace(/"/g, '&quot;') }"
                           data-party-group="${partyRecord.party_group || ''}"
                           data-due-days="${partyRecord.due_days}"
                           data-opening="${partyRecord.opening_balance}"
                           data-type="${partyRecord.transaction_type}"
                           data-party-type="${Array.isArray(partyRecord.party_type) ? partyRecord.party_type.join(',') : partyRecord.party_type || ''}"
                           data-credit-limit-enabled="${partyRecord.credit_limit_enabled || 0}"
                           data-credit-limit-amount="${partyRecord.credit_limit_amount || ''}"
                           data-custom-fields="${String(JSON.stringify(partyRecord.custom_fields || [])).replace(/"/g, '&quot;')}">
                            <span class="party-option-main">
                                <span class="party-option-name">${partyRecord.name}</span>
                                <span class="party-option-phone">${partyRecord.phone || '-'}</span>
                            </span>
                            <span class="text-success">0</span>
                        </a>
                      </li>
                    `;

                    const divider = dropdownMenu.querySelector('li > hr.dropdown-divider');
                    if (divider) {
                        divider.closest('li')?.insertAdjacentHTML('beforebegin', optionHtml);
                    } else {
                        dropdownMenu.insertAdjacentHTML('beforeend', optionHtml);
                    }
                }

                if (partyIdInput) partyIdInput.value = partyRecord.id;
                if (dropdownBtn) {
                    if (dropdownBtn.tagName === 'INPUT' || dropdownBtn.tagName === 'TEXTAREA') {
                        dropdownBtn.value = partyRecord.name || 'Select Party';
                    } else {
                        dropdownBtn.textContent = partyRecord.name || 'Select Party';
                    }
                }
                if (balanceDisplay) {
                    balanceDisplay.textContent = partyRecord.transaction_type === 'pay'
                        ? `To Pay Rs ${partyRecord.opening_balance || 0}`
                        : `To Receive Rs ${partyRecord.opening_balance || 0}`;
                    balanceDisplay.className = partyRecord.transaction_type === 'pay' ? 'text-danger small' : 'text-success small';
                }

              const qs = (sel, val) => { const el = document.querySelector(sel); if (el) el.value = val; };

qs(".phone-input", partyRecord.phone || "");
qs(".billing-address", partyRecord.billing_address || "");
qs(".shipping-address", partyRecord.shipping_address || "");
                applyPartyDueDays(partyRecord);
            }

            // Close modal first, then show success message
            if(closeAfterSave) {
                bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
                // Wait for modal to close, then reset
                setTimeout(() => {
                    form.reset();
                    // Clean up any leftover backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                }, 300);
            } else {
                form.reset();
            }

            // Show success message without blocking UI
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.innerHTML = `
                <div style="background: #28a745; color: white; padding: 12px 20px; border-radius: 4px; margin: 10px; position: fixed; top: 20px; right: 20px; z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <i class="fa-solid fa-check me-2"></i> Party saved successfully!
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        } else {
            const errorToast = document.createElement('div');
            errorToast.innerHTML = `
                <div style="background: #dc3545; color: white; padding: 12px 20px; border-radius: 4px; margin: 10px; position: fixed; top: 20px; right: 20px; z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <i class="fa-solid fa-exclamation me-2"></i> Error saving party
                </div>
            `;
            document.body.appendChild(errorToast);
            setTimeout(() => errorToast.remove(), 3000);
        }
    })
    .catch(err => {
        console.error(err);
        const errorToast = document.createElement('div');
        errorToast.innerHTML = `
            <div style="background: #dc3545; color: white; padding: 12px 20px; border-radius: 4px; margin: 10px; position: fixed; top: 20px; right: 20px; z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <i class="fa-solid fa-exclamation me-2"></i> Something went wrong! Check console.
                </div>
            `;
        document.body.appendChild(errorToast);
        setTimeout(() => errorToast.remove(), 3000);
    });
}
    saveBtn.addEventListener('click', function () {
        saveParty(true); // close modal after save
    });

    saveNewBtn.addEventListener('click', function () {
        saveParty(false); // reset modal for new entry
    });
});

</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const dropdownBtn = document.getElementById("partyDropdownBtn");
    const dropdownMenu = document.getElementById("partyDropdownMenu");
    const partyIdInput = document.querySelector(".party-id");
    const balanceDisplay = document.getElementById("partyBalanceDisplay");
    const billingNameInput = document.getElementById("billingNameInput");
    const partySelectorGroup = document.querySelector(".cash-party-selector-group");
    const partyDetails = document.querySelector(".party-details");
    const showPartyWrap = document.querySelector(".cash-party-link-wrap");
    const showPartyButton = document.querySelector(".show-party-selector-btn");
    const brokerDropdownBtn = document.getElementById("brokerDropdownBtn");
    const brokerDropdownMenu = document.getElementById("brokerDropdownMenu");
    const brokerSearchInput = document.querySelector('.broker-search-input');
    const brokerIdInput = document.querySelector(".broker-id");
    const brokerSelectedName = document.querySelector('.broker-selected-name');
    const brokerSelectedPhone = document.querySelector('.broker-selected-phone');
    const brokerForm = document.getElementById("brokerForm");
    const brokerModalEl = document.getElementById("brokerModal");
    const brokerPhoneInput = document.querySelector(".broker-phone-input");
    const brokerageTypeInput = document.querySelector(".brokerage-type");
    const brokerageRateInput = document.querySelector(".brokerage-rate");
    const addModalEl = document.getElementById('addPartyModal');

    const addModal = new bootstrap.Modal(addModalEl);
    const brokerModal = brokerModalEl ? new bootstrap.Modal(brokerModalEl) : null;

    const partySearchInput = dropdownBtn;
    if (partySearchInput) {
        refreshPartyDropdownMenu();
        partySearchInput.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') {
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            const searchTerm = String(this.value || '').trim();
            if (!searchTerm) {
                return;
            }

            const options = Array.from(dropdownMenu.querySelectorAll('.party-option'));
            const exactOption = options.find(opt => {
                const name = String(opt.dataset.name || opt.querySelector('.party-option-name')?.textContent || '').trim().toLowerCase();
                return name === searchTerm.toLowerCase();
            });

            if (exactOption) {
                exactOption.click();
                return;
            }

            addModal.show();
            const nameInput = document.getElementById('partyNameInput');
            if (nameInput) {
                nameInput.value = searchTerm;
                nameInput.focus();
            }
        });
    }

    if (brokerSearchInput && brokerDropdownMenu) {
        refreshBrokerDropdownMenus();
        let brokerNoResultsItem = brokerDropdownMenu.querySelector('.broker-no-results');
        if (!brokerNoResultsItem) {
            brokerNoResultsItem = document.createElement('li');
            brokerNoResultsItem.className = 'broker-no-results d-none';
            brokerNoResultsItem.innerHTML = '<span class="dropdown-item text-muted">No brokers found</span>';
            const addNewBrokerItem = brokerDropdownMenu.querySelector('#addNewBrokerBtn')?.closest('li');
            if (addNewBrokerItem) {
                addNewBrokerItem.insertAdjacentElement('beforebegin', brokerNoResultsItem);
            } else {
                brokerDropdownMenu.appendChild(brokerNoResultsItem);
            }
        }

        const filterBrokerOptions = (value) => {
            const searchText = String(value || '').trim().toLowerCase();
            const options = Array.from(brokerDropdownMenu.querySelectorAll('li > .broker-option'));
            let anyVisible = false;

            options.forEach(option => {
                const brokerName = String(option.dataset.name || option.querySelector('.broker-option-name')?.textContent || '').trim().toLowerCase();
                const brokerCity = String(option.dataset.city || option.querySelector('.broker-option-city')?.textContent || '').trim().toLowerCase();
                const brokerPhone = String(option.dataset.phone || option.querySelector('.broker-option-phone')?.textContent || '').trim().toLowerCase();
                const optionText = [brokerName, brokerCity, brokerPhone].filter(Boolean).join(' ');
                const shouldShow = !searchText || optionText.includes(searchText);
                const listItem = option.closest('li');
                if (listItem) {
                    listItem.style.display = shouldShow ? '' : 'none';
                }
                if (shouldShow) {
                    anyVisible = true;
                }
            });

            brokerNoResultsItem.classList.toggle('d-none', anyVisible);
        };

        brokerSearchInput.addEventListener('input', function () {
            filterBrokerOptions(this.value);
        });

        brokerSearchInput.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') {
                return;
            }
            const searchTerm = String(this.value || '').trim().toLowerCase();
            if (!searchTerm) {
                return;
            }
            const matchingOption = Array.from(brokerDropdownMenu.querySelectorAll('li > .broker-option')).find(option => {
                const brokerName = String(option.dataset.name || option.querySelector('.broker-option-name')?.textContent || '').trim().toLowerCase();
                const brokerCity = String(option.dataset.city || option.querySelector('.broker-option-city')?.textContent || '').trim().toLowerCase();
                const brokerPhone = String(option.dataset.phone || option.querySelector('.broker-option-phone')?.textContent || '').trim().toLowerCase();
                const optionText = [brokerName, brokerCity, brokerPhone].filter(Boolean).join(' ');
                return optionText.includes(searchTerm);
            });
            if (matchingOption) {
                matchingOption.click();
            }
        });
    }

    const setFieldValue = (selector, value = "") => {
        const field = document.querySelector(selector);
        if (field) {
            field.value = value;
        }
    };

    function buildBrokerOptionMarkup(broker) {
        return `
        <li>
            <a class="dropdown-item d-flex justify-content-between align-items-center broker-option" href="#"
               data-id="${broker.id || ''}"
               data-phone="${broker.phone || ''}"
               data-name="${broker.name || ''}"
               data-commission-rate="${broker.commission_rate || 0}">
                <div class="broker-option-name">${broker.name || ''}</div>
                <div class="broker-option-city text-muted small">${broker.city || '-'}</div>
            </a>
        </li>
    `;
    }

    function refreshBrokerDropdownMenus() {
        const brokers = Array.isArray(window.brokers) ? window.brokers : [];
        const parties = Array.isArray(window.parties) ? window.parties : [];
        document.querySelectorAll('#brokerDropdownMenu, .broker-dropdown-menu').forEach((menu) => {
            const brokersMarkup = brokers.map(buildBrokerOptionMarkup).join('');
            const partiesMarkup = parties.map((party) => `
                <li>
                    <a class="dropdown-item d-flex justify-content-between align-items-center broker-option broker-party-option" href="#"
                       data-id=""
                       data-phone="${party.phone || ''}"
                       data-name="${party.name || ''}"
                       data-commission-rate="0">
                        <div class="broker-option-name">${party.name || ''}</div>
                        <div class="broker-option-city text-muted small">Party</div>
                    </a>
                </li>
            `).join('');
            const addNewMarkup = `
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-primary add-new-broker-option" href="#">+ Add New Broker</a></li>
            `;
            const partySection = partiesMarkup ? `<li><hr class="dropdown-divider"></li>${partiesMarkup}` : '';
            menu.innerHTML = `${brokersMarkup}${partySection}${addNewMarkup}`;
        });
    }

    function refreshPartyDropdownMenu() {
        const menu = document.getElementById('partyDropdownMenu');
        if (!menu) return;

        const parties = Array.isArray(window.parties) ? window.parties : [];
        const partyOptions = parties.map((party) => `
            <li>
                <a class="dropdown-item d-flex justify-content-between align-items-start party-option"
                   href="#"
                   data-id="${party.id || ''}"
                   data-name="${party.name || ''}"
                   data-phone="${party.phone || ''}"
                   data-phone-number-2="${party.phone_number_2 || ''}"
                   data-city="${party.city || ''}"
                   data-ptcl="${party.ptcl_number || ''}"
                   data-email="${party.email || ''}"
                   data-address="${String(party.address || '').replace(/"/g, '&quot;')}"
                   data-billing="${String(party.billing_address || '').replace(/"/g, '&quot;')}"
                   data-shipping="${String(party.shipping_address || '').replace(/"/g, '&quot;')}"
                   data-party-group="${party.party_group || ''}"
                   data-due-days="${party.due_days || ''}"
                   data-opening="${party.opening_balance || 0}"
                   data-type="${party.transaction_type || ''}">
                    <span class="party-option-main">
                        <span class="party-option-name">${party.name || ''}</span>
                        <span class="party-option-phone">${party.phone || ''}</span>
                    </span>
                    <span class="${party.transaction_type === 'pay' ? 'text-danger' : 'text-success'}">
                        ${party.transaction_type === 'pay' ? '<i class="fa-solid fa-arrow-up me-1"></i>' : '<i class="fa-solid fa-arrow-down me-1"></i>'}
                        Rs ${Number(party.opening_balance || 0).toFixed(2)}
                    </span>
                </a>
            </li>
        `).join('');

        const footer = `
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-primary" href="#" id="addNewPartyBtn">+ Add New Party</a></li>
        `;

        menu.innerHTML = `${partyOptions}${footer}`;
    }

    function openBrokerModalForm() {
        brokerForm?.reset();
        const brokerStatusField = document.getElementById('brokerStatus');
        if (brokerStatusField) brokerStatusField.checked = true;
        const remainingField = document.getElementById('brokerRemainingBrokerage');
        if (remainingField) remainingField.value = '0.00';
        if (brokerModal) brokerModal.show();
    }

    const warehouseSelect = document.querySelector('.warehouse-select');
    const deliveryPersonInput = document.querySelector('.delivery-person-input');
    const deliveryPhoneInput = document.querySelector('.delivery-person-phone-input');

    const fillWarehouseHandler = () => {
        if (!warehouseSelect) return;
        const selectedOption = warehouseSelect.selectedOptions[0];
        if (!selectedOption) return;

        const handlerName = selectedOption.dataset.handlerName || '';
        const handlerPhone = selectedOption.dataset.handlerPhone || '';

        if (deliveryPersonInput && handlerName) {
            deliveryPersonInput.value = handlerName;
        }
        if (deliveryPhoneInput) {
            deliveryPhoneInput.value = handlerPhone;
        }
    };

    const warehouseModalEl = document.getElementById('warehouseModal');
    const warehouseForm = document.getElementById('warehouseForm');
    const warehouseModal = warehouseModalEl ? new bootstrap.Modal(warehouseModalEl) : null;
    let lastWarehouseValue = warehouseSelect?.value || '';

    const openWarehouseModal = () => {
        if (!warehouseModal) return;
        warehouseForm?.reset();
        // Reset the switch to checked (active) by default
        const isActiveSwitch = document.getElementById('warehouseIsActive');
        if (isActiveSwitch) {
            isActiveSwitch.checked = true;
        }
        warehouseModal.show();
    };

    if (warehouseSelect) {
        warehouseSelect.addEventListener('focus', function () {
            lastWarehouseValue = this.value;
        });

        warehouseSelect.addEventListener('change', function () {
            if (this.value === 'add_new_warehouse') {
                this.value = lastWarehouseValue || '';
                openWarehouseModal();
                return;
            }
            fillWarehouseHandler();
            lastWarehouseValue = this.value;
        });
        fillWarehouseHandler();
    }

    warehouseForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!warehouseForm) return;

        const formData = new FormData(warehouseForm);
        formData.set('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        formData.set('is_active', document.getElementById('warehouseIsActive')?.checked ? '1' : '0');

        fetch("{{ route('warehouses.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || '',
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: formData
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success || !data.warehouse) {
                throw new Error(data.message || 'Failed to save warehouse');
            }
            return data.warehouse;
        })
        .then((warehouse) => {
            if (!warehouse || !warehouse.id) {
                throw new Error('Invalid warehouse data returned');
            }

            const select = document.querySelector('.warehouse-select');
            if (select) {
                const option = document.createElement('option');
                option.value = warehouse.id;
                option.dataset.handlerName = warehouse.handler_name || '';
                option.dataset.handlerPhone = warehouse.handler_phone || '';
                option.textContent = warehouse.name || 'New Warehouse';

                const addNewOption = select.querySelector('option[value="add_new_warehouse"]');
                if (addNewOption) {
                    addNewOption.insertAdjacentElement('beforebegin', option);
                } else {
                    select.appendChild(option);
                }
                select.value = warehouse.id;
                fillWarehouseHandler();
            }

            warehouseModal?.hide();
            warehouseForm.reset();
        })
        .catch((error) => {
            alert(error.message || 'Unable to save warehouse.');
        });
    });

    partySelectorGroup?.setAttribute('data-cash-party-visible', 'false');
    showPartyWrap?.setAttribute('data-cash-link-armed', 'false');

    const syncCashPartyLayout = () => {
        const isCash = document.getElementById("saleToggleSwitch")?.checked;
        const hasParty = Boolean((partyIdInput?.value || '').trim());
        const cashPartySelectorVisible = partySelectorGroup?.getAttribute('data-cash-party-visible') === 'true';
        const cashLinkArmed = showPartyWrap?.getAttribute('data-cash-link-armed') === 'true';

        if (partyDetails) {
            partyDetails.classList.toggle('d-none', !(isCash || hasParty));
        }

        if (partySelectorGroup) {
            partySelectorGroup.classList.toggle('d-none', Boolean(isCash && !cashPartySelectorVisible));
        }

        if (showPartyWrap) {
            const shouldShowLink = Boolean(isCash && !cashPartySelectorVisible && cashLinkArmed);
            showPartyWrap.classList.toggle('d-none', !shouldShowLink);
        }
    };
const setPartyFieldValues = (partyRecord = {}) => {
    // Show party details section (keep for form data)
    const partyDetailsSection = document.querySelector(".party-details");
    if (partyDetailsSection) partyDetailsSection.classList.remove("d-none");

    const phoneField = document.querySelector(".phone-field");
    if (phoneField) phoneField.style.display = "";

    const shippingField = document.querySelector(".shipping-address-field");
    if (shippingField) shippingField.style.display = "";

    setFieldValue(".phone-input", partyRecord.phone || "");

    let billingContent = "";
    if (partyRecord.name) billingContent += partyRecord.name.toUpperCase() + "\n";
    const mobiles = [partyRecord.phone, partyRecord.phone_number_2].filter(Boolean);
    if (mobiles.length) billingContent += "M: " + mobiles.join(", ") + "\n";
    if (partyRecord.ptcl_number) billingContent += "T: " + partyRecord.ptcl_number + "\n";
    if (partyRecord.email) billingContent += "Em: " + partyRecord.email + "\n";
    const addrParts = [partyRecord.city, partyRecord.billing_address || partyRecord.address].filter(Boolean);
    if (addrParts.length) billingContent += "📍 " + addrParts.join(", ");
    setFieldValue(".billing-address", partyRecord.billing_address || partyRecord.address || "");
    setFieldValue(".shipping-address", partyRecord.shipping_address || "");

    // ===== SHOW PARTY CARD IN SEARCH BAR =====
    renderPartyCard(partyRecord);
};

const renderPartyCard = (partyRecord = {}) => {
    const wrapper = document.querySelector('.party-dropdown-wrapper');
    const searchInput = document.getElementById('partyDropdownBtn');
    if (!wrapper || !searchInput) return;

    // Remove old card if any
    const oldCard = wrapper.querySelector('.party-selected-card');
    if (oldCard) oldCard.remove();

    if (!partyRecord.name) {
        // No party — show input again
        searchInput.style.display = '';
        searchInput.value = '';
        const balanceDisplay = document.getElementById('partyBalanceDisplay');
        if (balanceDisplay) balanceDisplay.innerHTML = '';
        return;
    }

    // Hide search input
    searchInput.style.display = 'none';

    // Build balance display
    const opening = parseFloat(partyRecord.opening_balance || 0);
    const type = partyRecord.transaction_type;
    let balanceHtml = '';
    if (type === 'pay') {
        balanceHtml = `<span class="party-card-balance text-danger"><i class="fa-solid fa-arrow-up me-1"></i>₹${opening.toFixed(2)}</span>`;
    } else if (type === 'receive') {
        balanceHtml = `<span class="party-card-balance text-success"><i class="fa-solid fa-arrow-down me-1"></i>₹${opening.toFixed(2)}</span>`;
    } else if (opening) {
        balanceHtml = `<span class="party-card-balance text-muted">₹${opening.toFixed(2)}</span>`;
    }

    // Build lines
    const mobiles = [partyRecord.phone, partyRecord.phone_number_2].filter(Boolean);
    const lines = [];
    if (mobiles.length) lines.push(`M: ${mobiles.join(', ')}`);
    if (partyRecord.ptcl_number) lines.push(`T: ${partyRecord.ptcl_number}`);
    if (partyRecord.email) lines.push(`Em: ${partyRecord.email}`);
    const addrParts = [partyRecord.city, partyRecord.billing_address || partyRecord.address].filter(Boolean);
    if (addrParts.length) lines.push(`📍 ${addrParts.join(', ')}`);

    const linesHtml = lines.map(l => `<span class="party-card-line">${l}</span>`).join('');

    const card = document.createElement('div');
    card.className = 'party-selected-card';
    card.innerHTML = `
        <div class="party-card-info">
            <span class="party-card-name">${partyRecord.name}</span>
            ${linesHtml}
            ${balanceHtml}
        </div>
        <button type="button" class="party-card-clear" title="Change Party">✕</button>
    `;

    // Clear button — reset to search
    card.querySelector('.party-card-clear').addEventListener('click', function (e) {
        e.stopPropagation();
        card.remove();
        searchInput.style.display = '';
        searchInput.value = '';
        searchInput.focus();

        const partyIdInput = document.querySelector('.party-id');
        if (partyIdInput) partyIdInput.value = '';

        const balanceDisplay = document.getElementById('partyBalanceDisplay');
        if (balanceDisplay) balanceDisplay.innerHTML = '';

        // Hide party details
        const partyDetailsSection = document.querySelector('.party-details');
        if (partyDetailsSection) partyDetailsSection.classList.add('d-none');

        setFieldValue('.phone-input', '');
        setFieldValue('.billing-address', '');
        setFieldValue('.shipping-address', '');
    });

    // Insert card right before the search input (same position)
    searchInput.insertAdjacentElement('beforebegin', card);

    // Also hide balance display below (already shown in card)
    const balanceDisplay = document.getElementById('partyBalanceDisplay');
    if (balanceDisplay) balanceDisplay.innerHTML = '';
};

    const setDueDateFromParty = (partyRecord = {}) => {
        const dealDaysSelect = document.querySelector(".due-days-select");
        const dealDaysCustomInput = document.querySelector(".due-days-custom");
        const dueDateInput = document.querySelector(".due-date");
        const baseDateInput = document.querySelector(".invoice-date") || document.querySelector(".order-date");
        if (!dueDateInput || !baseDateInput || !dealDaysSelect) return;

        const existingSale = window.editSaleData || {};
        const hasSavedDueDate = Boolean(existingSale.due_date);
        const dueDays = hasSavedDueDate
            ? Number(existingSale.deal_days ?? 0)
            : Number(partyRecord.due_days || partyRecord.dueDays || 0);
        const baseDateValue = baseDateInput.value;

        if (dueDays > 0) {
            const allowedDays = ['0', '5', '10', '15', '30', '45'];
            if (allowedDays.includes(String(dueDays))) {
                dealDaysSelect.value = String(dueDays);
                dealDaysCustomInput?.classList.add('d-none');
                if (dealDaysCustomInput) dealDaysCustomInput.value = '';
            } else {
                dealDaysSelect.value = 'custom';
                dealDaysCustomInput?.classList.remove('d-none');
                if (dealDaysCustomInput) dealDaysCustomInput.value = dueDays;
            }
        } else {
            dealDaysSelect.value = '0';
            dealDaysCustomInput?.classList.add('d-none');
            if (dealDaysCustomInput) dealDaysCustomInput.value = '';
        }

        if (hasSavedDueDate) {
            const savedDueDate = String(existingSale.due_date || '');
            const parsedSavedDueDate = savedDueDate.includes('/')
                ? null
                : new Date(savedDueDate);

            dueDateInput.value = parsedSavedDueDate && !Number.isNaN(parsedSavedDueDate.getTime())
                ? `${String(parsedSavedDueDate.getDate()).padStart(2, '0')}/${String(parsedSavedDueDate.getMonth() + 1).padStart(2, '0')}/${parsedSavedDueDate.getFullYear()}`
                : savedDueDate;
            return;
        }

        if (!baseDateValue) {
            return;
        }

        const dueDate = new Date(baseDateValue);
        if (Number.isNaN(dueDate.getTime())) return;

        if (dueDays > 0) {
            dueDate.setDate(dueDate.getDate() + dueDays);
        }
        const yyyy = dueDate.getFullYear();
        const mm = String(dueDate.getMonth() + 1).padStart(2, '0');
        const dd = String(dueDate.getDate()).padStart(2, '0');
        dueDateInput.value = `${yyyy}-${mm}-${dd}`;
    };

    window.renderPartyCard = renderPartyCard;
    window.setPartyFieldValues = setPartyFieldValues;
    window.setDueDateFromParty = setDueDateFromParty;
    window.syncCashPartyLayout = syncCashPartyLayout;
    window.initializeSelectedPartyCard = function (partyOverride = null) {
        const existingPartyId = partyIdInput?.value?.trim();
        const sale = window.editSaleData || {};
        const saleParty = sale.party || {};
        const partyRecord = partyOverride
            || (window.parties || []).find((party) => String(party.id) === String(existingPartyId))
            || (existingPartyId ? {
                id: existingPartyId,
                name: sale.party_name || saleParty.name || '',
                phone: sale.phone || saleParty.phone || '',
                phone_number_2: saleParty.phone_number_2 || '',
                ptcl_number: saleParty.ptcl_number || '',
                email: saleParty.email || '',
                city: saleParty.city || '',
                address: saleParty.address || '',
                billing_address: sale.billing_address || saleParty.billing_address || '',
                shipping_address: sale.shipping_address || saleParty.shipping_address || '',
                due_days: saleParty.due_days || 0,
                opening_balance: saleParty.opening_balance || 0,
                transaction_type: saleParty.transaction_type || '',
            } : null);

        if (!partyRecord || !partyRecord.name) {
            return;
        }

        renderPartyCard(partyRecord);
        setPartyFieldValues(partyRecord);
        setDueDateFromParty(partyRecord);
        partySelectorGroup?.setAttribute('data-cash-party-visible', 'true');
        showPartyWrap?.setAttribute('data-cash-link-armed', 'false');
        syncCashPartyLayout();
    };

    window.initializeSelectedPartyCard();
    setTimeout(() => window.initializeSelectedPartyCard(), 50);


    dropdownMenu.addEventListener("click", function(e) {
        if(e.target.closest(".party-option")) {
            e.preventDefault();
            const option = e.target.closest(".party-option");
            const name = option.dataset.name || option.querySelector(".party-option-name")?.textContent || '';
            let opening = parseFloat(option.dataset.opening) || 0;
            const type = option.dataset.type;
            const id = option.dataset.id;
            const selectedParty = (window.parties || []).find((party) => String(party.id) === String(id)) || {};
         const partyRecord = {
    name:             selectedParty.name             ?? option.dataset.name        ?? name,
    phone:            selectedParty.phone            ?? option.dataset.phone       ?? "",
    phone_number_2:   selectedParty.phone_number_2   ?? option.dataset.phoneNumber2 ?? "",
    ptcl_number:      selectedParty.ptcl_number      ?? option.dataset.ptcl        ?? "",
    email:            selectedParty.email            ?? option.dataset.email       ?? "",
    city:             selectedParty.city             ?? option.dataset.city        ?? "",
    party_group:      selectedParty.party_group      ?? option.dataset.partyGroup  ?? "",
    address:          selectedParty.address          ?? option.dataset.address     ?? "",
    billing_address:  selectedParty.billing_address  ?? option.dataset.billing     ?? "",
    shipping_address: selectedParty.shipping_address ?? option.dataset.shipping    ?? "",
    due_days:         selectedParty.due_days         ?? option.dataset.dueDays     ?? "",
    opening_balance:      selectedParty.opening_balance      ?? parseFloat(option.dataset.opening || 0),
    transaction_type:     selectedParty.transaction_type     ?? option.dataset.type ?? type,
};

            // Button pe sirf party name
            dropdownBtn.value = name;
            // Clear billing name input when party is selected
const billingNameInp = document.querySelector('.billing-name-input');
if (billingNameInp) billingNameInp.value = '';

            // Show balance below button with color
          if(type === "pay"){
    balanceDisplay.innerHTML = `
        <i class="fa-solid fa-arrow-up text-danger me-1"></i>
        ₹${opening.toFixed(2)}
    `;
}
else if(type === "receive"){
    balanceDisplay.innerHTML = `
        <i class="fa-solid fa-arrow-down text-success me-1"></i>
        ₹${opening.toFixed(2)}
    `;
}
else {
    balanceDisplay.innerHTML = `₹${opening.toFixed(2)}`;
}

            // Save selected party id
            partyIdInput.value = id;

            // Populate detail fields
            partySelectorGroup?.setAttribute('data-cash-party-visible', 'true');
showPartyWrap?.setAttribute('data-cash-link-armed', 'false');
syncCashPartyLayout();
setPartyFieldValues(partyRecord);
setDueDateFromParty(partyRecord);
        }
        else if(e.target.id === "addNewPartyBtn") {
            const partySearchValue = dropdownBtn?.value?.toString().trim() || '';
            addModal.show();
            const addPartyForm = document.getElementById("addPartyForm");
            if (addPartyForm) addPartyForm.reset();
            const partyNameInput = document.getElementById('partyNameInput');
            if (partyNameInput && partySearchValue) {
                partyNameInput.value = partySearchValue;
            }
            balanceDisplay.textContent = "";
            setPartyFieldValues({});
            syncCashPartyLayout();
        }
    });

    billingNameInput?.addEventListener('click', function () {
        if (document.getElementById("saleToggleSwitch")?.checked) {
            showPartyWrap?.setAttribute('data-cash-link-armed', 'true');
            syncCashPartyLayout();
        }
    });

    billingNameInput?.addEventListener('focus', function () {
        if (document.getElementById("saleToggleSwitch")?.checked) {
            showPartyWrap?.setAttribute('data-cash-link-armed', 'true');
            syncCashPartyLayout();
        }
    });

    showPartyButton?.addEventListener('click', function () {
        partySelectorGroup?.setAttribute('data-cash-party-visible', 'true');
        showPartyWrap?.setAttribute('data-cash-link-armed', 'false');
        syncCashPartyLayout();
        dropdownBtn?.focus();
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.add-new-broker-option')) {
            event.preventDefault();
            openBrokerModalForm();
        }
    });

    brokerDropdownMenu?.addEventListener("click", function(e) {
        if (!e.target.closest(".broker-option")) return;

        e.preventDefault();
        const option = e.target.closest(".broker-option");
        const name = option.dataset.name || option.querySelector(".party-option-name")?.textContent || '';
        const phone = option.dataset.phone || "";
        const id = option.dataset.id || "";
        const commissionRate = parseFloat(option.dataset.commissionRate || 0) || 0;

        if (brokerDropdownBtn) {
            brokerDropdownBtn.value = name;
        }
        if (brokerSelectedName) {
            brokerSelectedName.textContent = name || '';
        }
        if (brokerSelectedPhone) {
            brokerSelectedPhone.textContent = phone || '';
            brokerSelectedPhone.closest('.broker-selected-info')?.classList.toggle('visible', !!phone);
        }
        brokerIdInput.value = id;
        if (brokerPhoneInput) {
            brokerPhoneInput.value = phone;
        }
        if (brokerageTypeInput && brokerageRateInput) {
            brokerageTypeInput.value = 'broker_rate';
            brokerageRateInput.value = commissionRate ? commissionRate.toFixed(2) : '';
            brokerageRateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    brokerForm?.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(brokerForm);
        formData.set('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.set('status', document.getElementById('brokerStatus')?.checked ? '1' : '0');

        fetch("{{ route('brokers.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: formData
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success || !data.broker) {
                throw new Error(data.message || 'Failed to save broker');
            }
            return data.broker;
        })
        .then((broker) => {
            window.brokers = Array.isArray(window.brokers) ? window.brokers : [];
            window.brokers = window.brokers.filter(entry => String(entry.id) !== String(broker.id));
            window.brokers.unshift(broker);
            refreshBrokerDropdownMenus();
            refreshPartyDropdownMenu();

            if (brokerDropdownBtn) {
                brokerDropdownBtn.value = broker.name || 'Broker';
            }
            if (brokerSelectedName) {
                brokerSelectedName.textContent = broker.name || '';
            }
            if (brokerSelectedPhone) {
                brokerSelectedPhone.textContent = broker.phone || '';
                brokerSelectedPhone.closest('.broker-selected-info')?.classList.toggle('visible', !!broker.phone);
            }
            brokerIdInput.value = broker.id || '';
            if (brokerPhoneInput) {
                brokerPhoneInput.value = broker.phone || '';
            }
            if (brokerageTypeInput && brokerageRateInput) {
                brokerageTypeInput.value = 'broker_rate';
                brokerageRateInput.value = broker.commission_rate ? Number(broker.commission_rate).toFixed(2) : '';
                brokerageRateInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            brokerModal?.hide();
            brokerForm.reset();
        })
        .catch((error) => {
            alert(error.message || 'Unable to save broker.');
        });
    });

});

// Credit Limit Toggle
document.addEventListener("DOMContentLoaded", function() {
    const creditLimitSwitch = document.getElementById("creditLimitSwitch");
    const creditLimitAmountWrap = document.getElementById("creditLimitAmountWrap");

    if (creditLimitSwitch) {
        creditLimitSwitch.addEventListener("change", function() {
            if (this.checked) {
                creditLimitAmountWrap.classList.remove("is-hidden");
            } else {
                creditLimitAmountWrap.classList.add("is-hidden");
            }
        });
    }
});

// Payment type switch: show/hide deal days and due date for Cash/Credit
document.addEventListener("DOMContentLoaded", function() {
    const saleToggleSwitch = document.getElementById("saleToggleSwitch");
    const dealDaysGroup = document.querySelector('.deal-days-group');
    const dueDateGroup = document.querySelector('.final-due-date-group');
    const partySelectorGroup = document.querySelector('.cash-party-selector-group');
    const partyDetails = document.querySelector('.party-details');
    const invoiceContainer = document.querySelector('.invoice-container');
    const showPartyWrap = document.querySelector('.cash-party-link-wrap');
    const partyIdInput = document.querySelector('.party-id');

    function updatePaymentMode() {
        const isCash = saleToggleSwitch?.checked;
        let cashPartySelectorVisible = partySelectorGroup?.getAttribute('data-cash-party-visible') === 'true';
        let cashLinkArmed = showPartyWrap?.getAttribute('data-cash-link-armed') === 'true';
        if (dealDaysGroup) {
            dealDaysGroup.style.display = isCash ? 'none' : '';
        }
        if (dueDateGroup) {
            dueDateGroup.style.display = isCash ? 'none' : '';
        }
        invoiceContainer?.classList.toggle('cash-mode', Boolean(isCash));
        if (!isCash) {
            cashPartySelectorVisible = true;
            cashLinkArmed = false;
            partySelectorGroup?.setAttribute('data-cash-party-visible', 'true');
            showPartyWrap?.setAttribute('data-cash-link-armed', 'false');
        } else if (!cashPartySelectorVisible) {
            partySelectorGroup?.setAttribute('data-cash-party-visible', 'false');
        }
        if (partySelectorGroup) {
            partySelectorGroup.classList.toggle('d-none', Boolean(isCash && !cashPartySelectorVisible));
        }
        if (showPartyWrap) {
            showPartyWrap.classList.toggle('d-none', !isCash || cashPartySelectorVisible || !cashLinkArmed);
        }
        if (partyDetails) {
            const hasParty = Boolean((partyIdInput?.value || '').trim());
            partyDetails.classList.toggle('d-none', !(isCash || hasParty));
        }
    }

    if (saleToggleSwitch) {
        saleToggleSwitch.addEventListener('change', function () {
            if (this.checked) {
                partySelectorGroup?.setAttribute('data-cash-party-visible', 'false');
                showPartyWrap?.setAttribute('data-cash-link-armed', 'false');
            }
            updatePaymentMode();
        });
        updatePaymentMode();
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const poFieldsGroup = document.querySelector('.po-fields-group');
    const poDetailsEnabled = Boolean(window.transactionSettings?.poDetailsEnabled);
    if (poFieldsGroup) {
        poFieldsGroup.classList.toggle('is-hidden', !poDetailsEnabled);
    }
});

// Add New Party button in dropdown
document.addEventListener("DOMContentLoaded", function() {
    const addNewPartyBtn = document.getElementById("addNewPartyBtn");
    const addPartyModal = document.getElementById("addPartyModal");

    if (addNewPartyBtn && addPartyModal) {
        addNewPartyBtn.addEventListener("click", function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(addPartyModal);

            const partySearchInput = document.getElementById('partyDropdownBtn');
            const partyNameInput = document.getElementById('partyNameInput');
            if (partyNameInput) {
                partyNameInput.value = (partySearchInput?.value || '').trim();
            }

            modal.show();
        });
    }
});
// Auto-save party details when edited
document.addEventListener('input', function(e) {
    const billingAddr = e.target.closest('.billing-address');
    if (!billingAddr) return;

    const partyIdInput = document.querySelector('.party-id');
    const partyId = partyIdInput?.value?.trim();
    if (!partyId) return;

    clearTimeout(window._partyAutoSaveTimer);
    window._partyAutoSaveTimer = setTimeout(() => {
        const rawText = billingAddr.value || '';

        // Parse fields from the textarea content
        const lines = rawText.split('\n');
        const parsed = {};
        lines.forEach(line => {
            const colonIdx = line.indexOf(':');
            if (colonIdx === -1) return;
            const key = line.substring(0, colonIdx).trim().toLowerCase();
            const val = line.substring(colonIdx + 1).trim();
            if (key === 'name') parsed.name = val;
            else if (key === 'mob' || key === 'mob 1') parsed.phone = val;
            else if (key === 'mob 2') parsed.phone_number_2 = val;
            else if (key === 'ptcl') parsed.ptcl_number = val;
            else if (key === 'email') parsed.email = val;
            else if (key === 'city') parsed.city = val;
            else if (key === 'group') parsed.party_group = val;
        });

        // Extract address block (after "Address:" line)
        const addrMatch = rawText.match(/Address:\n([\s\S]*)/i);
        if (addrMatch) parsed.billing_address = addrMatch[1].trim();

        if (!parsed.name) return; // don't save if name missing

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/dashboard/parties/${partyId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(parsed)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success || data.party) {
                // Update window.parties cache
                const idx = (window.parties || []).findIndex(p => String(p.id) === String(partyId));
                if (idx !== -1) {
                    window.parties[idx] = { ...window.parties[idx], ...parsed };
                }
                // Show subtle save indicator
                billingAddr.style.borderColor = '#28a745';
                setTimeout(() => { billingAddr.style.borderColor = ''; }, 1200);
            }
        })
        .catch(() => {
            billingAddr.style.borderColor = '#dc3545';
            setTimeout(() => { billingAddr.style.borderColor = ''; }, 1200);
        });
    }, 800); // debounce 800ms
});
// ===== AUTO-SAVE PARTY DETAILS ON EDIT =====
(function() {
    function getActivePane() {
        return document.querySelector('.tab-pane.active') || document;
    }

    // Delegate input event on document to catch all tabs
    document.addEventListener('input', function(e) {
        const textarea = e.target;
        if (!textarea.classList.contains('billing-address')) return;

        const pane = textarea.closest('.tab-pane') || document;
        const partyId = pane.querySelector('.party-id')?.value?.trim();
        if (!partyId) return;

        // Show "saving..." indicator
        const indicator = textarea.closest('.floating-input-wrapper')?.querySelector('.party-save-indicator');
        if (indicator) {
            indicator.textContent = '● saving...';
            indicator.style.color = '#f59e0b';
            indicator.style.opacity = '1';
        }

        clearTimeout(textarea._saveTimer);
        textarea._saveTimer = setTimeout(() => {
            const rawText = textarea.value || '';

            // Parse the structured text back into fields
            const parsed = { billing_address: '' };
            const lines = rawText.split('\n');
            let addressLines = [];
            let inAddress = false;

            lines.forEach(line => {
                if (inAddress) {
                    addressLines.push(line);
                    return;
                }
                if (/^address\s*:/i.test(line)) {
                    inAddress = true;
                    const afterColon = line.replace(/^address\s*:/i, '').trim();
                    if (afterColon) addressLines.push(afterColon);
                    return;
                }
                const colonIdx = line.indexOf(':');
                if (colonIdx === -1) return;
                const key = line.substring(0, colonIdx).trim().toLowerCase().replace(/\s+/g, '_');
                const val = line.substring(colonIdx + 1).trim();
                if (!val) return;

                if (key === 'name') parsed.name = val;
                else if (key === 'mob' || key === 'mob_1') parsed.phone = val;
                else if (key === 'mob_2') parsed.phone_number_2 = val;
                else if (key === 'ptcl') parsed.ptcl_number = val;
                else if (key === 'email') parsed.email = val;
                else if (key === 'city') parsed.city = val;
                else if (key === 'group') parsed.party_group = val;
            });

            if (addressLines.length) {
                parsed.billing_address = addressLines.join('\n').trim();
            }

            // Also sync phone input field if name changed
            if (parsed.name) {
                const dropdownBtn = pane.querySelector('#partyDropdownBtn');
                if (dropdownBtn && dropdownBtn.tagName === 'INPUT') {
                    dropdownBtn.value = parsed.name;
                }
            }
            if (parsed.phone) {
                const phoneInput = pane.querySelector('.phone-input');
                if (phoneInput) phoneInput.value = parsed.phone;
            }

            // Update window.parties cache immediately
            const partyIdx = (window.parties || []).findIndex(p => String(p.id) === String(partyId));
            if (partyIdx !== -1) {
                window.parties[partyIdx] = { ...window.parties[partyIdx], ...parsed };
            }

            // Save to server
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`/dashboard/parties/${partyId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(parsed)
            })
            .then(res => res.json())
            .then(data => {
                if (indicator) {
                    if (data.success || data.party) {
                        indicator.textContent = '✓ saved';
                        indicator.style.color = '#22c55e';
                        setTimeout(() => { indicator.style.opacity = '0'; }, 2000);

                        // Update dropdown option data attributes too
                        const option = document.querySelector(`.party-option[data-id="${partyId}"]`);
                        if (option) {
                            if (parsed.name) option.dataset.name = parsed.name;
                            if (parsed.phone) option.dataset.phone = parsed.phone;
                            if (parsed.city) option.dataset.city = parsed.city;
                            if (parsed.billing_address) option.dataset.billing = parsed.billing_address;
                            const nameEl = option.querySelector('.party-option-name');
                            if (nameEl && parsed.name) nameEl.textContent = parsed.name;
                            const phoneEl = option.querySelector('.party-option-phone');
                            if (phoneEl && parsed.phone) phoneEl.textContent = parsed.phone;
                        }
                    } else {
                        indicator.textContent = '✗ error';
                        indicator.style.color = '#ef4444';
                        setTimeout(() => { indicator.style.opacity = '0'; }, 3000);
                    }
                }
            })
            .catch(() => {
                if (indicator) {
                    indicator.textContent = '✗ failed';
                    indicator.style.color = '#ef4444';
                    setTimeout(() => { indicator.style.opacity = '0'; }, 3000);
                }
            });
        }, 900);
    });
})();

</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize invoice date with today's date in DD/MM/YYYY format
    const invoiceDateInput = document.querySelector(".invoice-date");
    const dueDateInput = document.querySelector(".due-date");
    const dueDaysSelect = document.querySelector(".due-days-select");
    const dueDaysCustomInput = document.querySelector(".due-days-custom");

    // Helper function to parse DD/MM/YYYY to Date object
    function parseDate(dateString) {
        if (!dateString) return null;
        const parts = dateString.split('/');
        if (parts.length !== 3) return null;
        const day = parseInt(parts[0]);
        const month = parseInt(parts[1]) - 1; // Month is 0-indexed
        const year = parseInt(parts[2]);
        const date = new Date(year, month, day);
        if (Number.isNaN(date.getTime())) return null;
        return date;
    }

    // Helper function to parse YYYY-MM-DD format (from date input) safely without timezone issues
    function parseYYYYMMDD(dateString) {
        if (!dateString) return null;
        const parts = dateString.split('-');
        if (parts.length !== 3) return null;
        const year = parseInt(parts[0]);
        const month = parseInt(parts[1]) - 1; // Month is 0-indexed
        const day = parseInt(parts[2]);
        const date = new Date(year, month, day);
        if (Number.isNaN(date.getTime())) return null;
        return date;
    }

    // Helper function to format date as DD/MM/YYYY
    function formatDate(date) {
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const yyyy = date.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
    }

    // Set today's date in DD/MM/YYYY format
    function setTodayDate() {
        if (invoiceDateInput) {
            if (window.editSaleData?.due_date && dueDateInput) {
                const savedDueDate = String(window.editSaleData.due_date || '');
                const parsedSavedDueDate = savedDueDate.includes('/')
                    ? null
                    : new Date(savedDueDate);
                dueDateInput.value = parsedSavedDueDate && !Number.isNaN(parsedSavedDueDate.getTime())
                    ? `${String(parsedSavedDueDate.getDate()).padStart(2, '0')}/${String(parsedSavedDueDate.getMonth() + 1).padStart(2, '0')}/${parsedSavedDueDate.getFullYear()}`
                    : savedDueDate;
            }

            let dateValue = invoiceDateInput.value;
            let dateToSet;

            if (dateValue) {
                // If there's already a value, check if it's in YYYY-MM-DD format and convert it
                if (dateValue.includes('-')) {
                    // Format is YYYY-MM-DD
                    dateToSet = parseYYYYMMDD(dateValue);
                } else if (dateValue.includes('/')) {
                    // Format is already DD/MM/YYYY
                    dateToSet = parseDate(dateValue);
                }
            } else {
                // No value, use today's date
                dateToSet = new Date();
            }

            if (dateToSet) {
                invoiceDateInput.value = formatDate(dateToSet);
                if (!window.editSaleData?.due_date) {
                    calculateDueDate();
                }
            }
        }
    }

    // Calculate and update due date based on invoice date and deal days
    function calculateDueDate() {
        if (!invoiceDateInput || !dueDateInput) return;

        const baseDateValue = invoiceDateInput.value;
        if (!baseDateValue) {
            dueDateInput.value = '';
            return;
        }

        const baseDate = parseDate(baseDateValue);
        if (!baseDate) {
            return;
        }

        const dueDate = new Date(baseDate);

        let dueDays = 0;
        if (dueDaysSelect?.value === 'custom') {
            dueDays = parseInt(dueDaysCustomInput?.value) || 0;
        } else {
            dueDays = parseInt(dueDaysSelect?.value) || 0;
        }

        if (dueDays > 0) {
            dueDate.setDate(dueDate.getDate() + dueDays);
        }

        dueDateInput.value = formatDate(dueDate);
    }

    // Set today's date on page load
    setTodayDate();

    // Make invoice date clickable to allow date selection
    if (invoiceDateInput) {
        invoiceDateInput.style.cursor = 'pointer';
        invoiceDateInput.addEventListener('click', function() {
            // Create a hidden date input for the date picker
            const hiddenDateInput = document.createElement('input');
            hiddenDateInput.type = 'date';
            hiddenDateInput.style.display = 'none';

            // Set current date if exists
            if (invoiceDateInput.value) {
                const currentDate = parseDate(invoiceDateInput.value);
                if (currentDate) {
                    const yyyy = currentDate.getFullYear();
                    const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(currentDate.getDate()).padStart(2, '0');
                    hiddenDateInput.value = `${yyyy}-${mm}-${dd}`;
                }
            }

            document.body.appendChild(hiddenDateInput);

            hiddenDateInput.addEventListener('change', function() {
                if (hiddenDateInput.value) {
                    // Parse YYYY-MM-DD format safely
                    const selectedDate = parseYYYYMMDD(hiddenDateInput.value);
                    if (selectedDate) {
                        invoiceDateInput.value = formatDate(selectedDate);
                        calculateDueDate();
                    }
                }
                document.body.removeChild(hiddenDateInput);
            });

            // Open the date picker
            hiddenDateInput.click();
        });
    }

    // Listen for direct input on invoice date
    if (invoiceDateInput) {
        invoiceDateInput.addEventListener('change', calculateDueDate);
        invoiceDateInput.addEventListener('blur', function() {
            // Validate and reformat the date if user typed it manually
            if (invoiceDateInput.value) {
                const parsedDate = parseDate(invoiceDateInput.value);
                if (parsedDate) {
                    invoiceDateInput.value = formatDate(parsedDate);
                    calculateDueDate();
                }
            }
        });
    }

    // Listen for changes on deal days select
    if (dueDaysSelect) {
        dueDaysSelect.addEventListener('change', function() {
            if (dueDaysSelect.value === 'custom') {
                dueDaysCustomInput?.classList.remove('d-none');
                dueDaysCustomInput?.focus();
            } else {
                dueDaysCustomInput?.classList.add('d-none');
                calculateDueDate();
            }
        });
    }

    // Listen for changes on custom deal days input
    if (dueDaysCustomInput) {
        dueDaysCustomInput.addEventListener('change', calculateDueDate);
    }
});
</script>

</body>

</html>
