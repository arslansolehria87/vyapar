@extends('layouts.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    /* uper panel styling */
    .uper-panel {
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        height: 8vh;
        padding: 8px 16px;
        /* horizontal padding */
        background-color: white;
        display: flex;
        align-items: center;
        /* vertically center content */
    }

    .panel-main {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        /* vertically center all items */
        width: 100%;
    }

    .text {
        display: flex;
        align-items: center;
        /* vertically center h1 + arrow */
        gap: 6px;
    }

    .text h1 {
        font-size: 20px;
        margin: 0;
        /* remove default margin */
    }

    .header-dropdown {
        position: relative;
        display: flex;
        align-items: center;
        /* center h1 + arrow */
        gap: 4px;
        cursor: pointer;
    }

    .arrow-icon {
        color: #0f6fcc;
        font-size: 16px;
        transition: transform 0.3s;
    }

    .dropdown-container {
        position: relative;
        width: 200px;
    }

    .dropdown-input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 4px;
        /* spacing below your label */
    }

    .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 2px;
        display: none;
        z-index: 10;
    }

    .dropdown-option {
        padding: 8px;
        cursor: pointer;
        position: relative;
    }

    .dropdown-option:hover {
        background-color: #dbeafe;
        /* light blue */
        color: black;
    }

    .dropdown-option.selected::after {
        content: '✓';
        color: #3b82f6;
        /* light blue tick */
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }

    .header-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 4px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: none;
        z-index: 999;
        min-width: 120px;
    }

    input:focus {
        border-color: #3b82f6 !important;
        /* light blue border on focus */
        box-shadow: 0 0 4px rgba(96, 165, 250, 0.5);
        /* subtle blue glow */
        outline: none;
        /* remove default outline */
    }

    .header-dropdown-menu label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: #1f2937;
    }

    .header-dropdown-menu input[type="checkbox"] {
        accent-color: #0f6fcc;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-add-entity {
        background-color: #dc2626;
        color: white;
        border: none;
        min-width: 160px;
        padding: 0 14px;
        border-radius: 23px;
        height: 44px;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .dropdown-item {
        position: relative;
        display: block;
        padding: 6px 12px;
        font-size: 14px;
        color: #1f2937;
        cursor: default;
        width: 30px;
    }

    .tick-icon {
        position: absolute;
        top: 50%;
        right: 8px;
        /* top-right corner */
        transform: translateY(-50%);
        color: #0f6fcc;
        /* light blue */
        font-size: 14px;
    }

    .btn-settings,
    .btn-ellipsis {
        background: transparent;
        border: none;
        cursor: pointer;
        color: #9ca3af;
        font-size: 16px;
        padding: 4px;
    }

    .btn-settings:hover,
    .btn-ellipsis:hover {
        color: #374151;
    }

    .btn-settings {
        margin-left: 12px;
    }

    /* search box */
    .search-box {
        position: relative;
        width: 420px;
        /* width increase */
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
        z-index: 2;
    }

    .search-input {
        width: 100%;
        height: 42px;
        padding-left: 40px !important;
        /* IMPORTANT: icon ke liye space */
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #9ca3af;
    }

    .search-input::placeholder {
        color: #9ca3af !important;
    }

    .filter-wrapper {
        position: relative;
        /* zaroori */
        display: inline-flex;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .entity-name {
        color: #9ca3af;
    }

    .filter-icon {
        color: red;
        cursor: pointer;
        font-size: 16px;
    }


    .btn-icon {
        background: transparent;
        /* no full background */
        border: none;
        /* remove full border */
        border-bottom: 2px solid #0f6fcc;
        /* only bottom outline */
        padding: 6px 8px;
        cursor: pointer;
        color: #0f6fcc;
        /* icon color */
        border-radius: 4px 4px 0 0;
        /* optional rounded top corners */
        font-size: 16px;
        transition: background 0.2s, color 0.2s;
    }

    .btn-icon:hover {
        background: rgba(15, 111, 204, 0.1);
        /* slight blue background on hover */
        color: #074ea0;
        /* slightly darker icon on hover */
    }

    .btn-icon-right {
        background: transparent;
        /* no full background */
        border: none;
        /* remove all borders first */
        border-right: 2px solid #0f6fcc;
        /* vertical outline on right side */
        padding: 6px 8px;
        cursor: pointer;
        color: #0f6fcc;
        /* icon color */
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        /* vertically center icon */
        justify-content: center;
        /* horizontally center icon */
        border-radius: 4px 0 0 4px;
        /* optional rounded left corners */
        transition: background 0.2s, color 0.2s;
    }

    .btn-icon-right:hover {
        background: rgba(15, 111, 204, 0.1);
        /* subtle hover background */
        color: #074ea0;
        /* darker icon on hover */
    }

    .filter-dropdown label {
        display: block;
        color: black;
        margin-bottom: 6px;
        font-size: 14px;
    }

    .show {
        display: block;
    }

    .filter-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    .clear-btn {
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
    }

    .apply-btn {
        background-color: #dc2626;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 6px;
        cursor: pointer;
    }

    /* Parent row flex */
    .entity-row {
        display: flex;
        align-items: center;
        gap: 12px;
        /* space between left and right */
    }

    /* left section */
    /* SPLIT LAYOUT */
    .split-pane {
        display: flex;
        width: 100%;
    }

    /* LEFT PANEL */
    .split-left {
        width: 240px !important;
        flex: 0 0 240px;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    /* RIGHT PANEL */
    .split-right {
        flex: 1;
        padding: 0;
    }

    /* REMOVE UL DEFAULT SPACE */
    .entity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* LIST ITEMS */
    .entity-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 8px;
    }

    .entity-list li.active {
        background-color: rgba(15, 111, 204, 0.08);
    }

    /* HEADER AREA */
    .list-panel-header {
        padding: 8px;
    }

    /* FILTER WRAPPER */
    .filter-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 0;
        margin: 0;
    }

    /* PARTY NAME + ARROWS */
    .parent-arrows {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* SEPARATOR LINE */
    .separator {
        width: 1px;
        height: 18px;
        background: #e5e7eb;
        margin: 0 6px;
    }

    /* AMOUNT SECTION */
    .entity-balance {
        margin-left: auto;
    }

    /* Left side contains Party Name + Filter */
    .left-side {
        display: flex;
        align-items: center;
        position: relative;
        padding-right: 12px;
        border-right: 1px solid #d1d5db;
        /* vertical line */
    }

    /* filter dropdown */
    .filter-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        position: relative;
    }

    .filter-wrapper span {
        flex-shrink: 0;
    }

    .table-filter-icon {
        font-size: 16px;
        color: #6b7280;
        cursor: pointer;
        flex-shrink: 0;
        margin-left: 4px;
    }

    .filter-dropdown {
        position: absolute;
        top: 28px;
        margin-left: 12px;
        width: 200px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        display: none;
        flex-direction: column;
        z-index: 1000;
        padding: 10px;

    }

    .filter-options {
        overflow-y: auto;
        max-height: 160px;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;


    }

    .filter-options label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        color: #374151;
        cursor: pointer;
        font-weight: 400;
    }

    .filter-options::-webkit-scrollbar {
        width: 6px;
    }

    .filter-options::-webkit-scrollbar-thumb {
        background: #9ca3af;
        border-radius: 3px;
    }

    .filter-options::-webkit-scrollbar-track {
        background: #f3f4f6;
    }

    .filter-actions {
        display: flex;
        justify-content: space-between;
        padding: 8px;
        border-top: 1px solid #d1d5db;
        background: #f9fafb;
        border-radius: 0 0 8px 8px;
    }

    .clear-btn {
        background: whitesmoke;
        color: #6b7280;
        border: none;
        padding: 6px 12px;
        border-radius: 16px;
        transition: 0.2s;
        font-size: 14px !important;
        font-weight: 600;
    }

    .clear-btn:hover {
        background: #e5e7eb;
    }


    .apply-btn:hover {
        background: #8B0000 !important;
    }

    /* Right side (Amount) */
    .right-side {
        padding-left: 32px;
        /* space after line */
    }

    .entity-row {
        list-style: none;
        margin-bottom: 12px;
    }

    .row-content {
        display: flex;
        align-items: center;
    }


    /* transection table*/
    .table-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
    }

    .left-side {
        display: flex;
        align-items: center;
        gap: 8px;
        /* Party Name + icon spacing */
        position: relative;
        /* dropdown ke liye relative parent */
    }

    .txn-table th {
        border-right: 1px solid #d1d5db;
        ;
        padding: 12px 16px;
        background: #f9fafb;
    }

    .txn-table th {
        font-size: 12px !important;
        text-transform: capitalize !important;
    }

    .filter-icon {
        color: red;
        cursor: pointer;
        font-size: 16px;
    }

    .table-main {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        position: relative;
    }

    /* table layout equal columns */
    .txn-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .txn-table tbody tr.active-row {
        background-color: rgba(15, 111, 204, 0.08);
    }

    /* table heads */
    .txn-table th {
        padding: 10px 12px;
        text-align: left;
        position: relative;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center !important;
        margin-bottom: 8px;
    }

    .table-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }

    .header-icons {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-icons i {
        font-size: 16px;
        color: #9ca3af;
        cursor: pointer;
        transition: 0.2s;
        line-height: 1;
    }


    /* header content */
    .table-main {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        position: relative;
    }

    /* sort arrows */
    .sort-arrows {
        display: flex;
        flex-direction: column;
        position: absolute;
        right: 28px;
        /* filter icon se pehle fixed position */
        font-size: 9px;
        line-height: 7px;
        opacity: 0;
        transition: 0.2s;
    }

    /* arrows spacing */
    .sort-arrows i {
        margin: 0;
        padding: 0;
    }

    /* show arrows on click */
    .table-main.active .sort-arrows {
        opacity: 1;
    }

    /* filter icon */
    .table-filter-icon {
        margin-left: auto;
        font-size: 12px;
        color: #9ca3af;
    }

    .table-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* text left, arrows+filter right */
        gap: 4px;
        position: relative;
        width: 100%;
    }

    .table-main span {
        flex-shrink: 0;
    }

    /* arrows container */
    .sort-arrows {
        position: absolute;
        display: flex;
        flex-direction: column;
        line-height: 1px;
        font-size: 9px;
        opacity: 0;
        /* hidden but space reserved */
        transition: 0.2s;
        margin-right: 4px;
        /* gap from filter icon */
    }

    .sort-arrows i {
        margin: 0;
        padding: 0;
        line-height: 3px;
    }


    /* arrows style */
    .sort-arrows i {
        color: #6b7280;
    }

    /* click par show */
    .table-main.active .sort-arrows {
        opacity: 1;
    }

    /* Dropdown */

    .split-pane {
        margin-top: 10px;

    }

    .filter-dropdown label {
        display: block;
        color: black;
        margin-bottom: 6px;
        font-size: 14px;
    }

    .show {
        display: block;
    }

    /* Actions buttons */
    .filter-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }





    /* Vertical line */
    .separator {
        width: 1px;
        background-color: #d1d5db;
        height: 24px;
        margin: 0 16px;
    }

    .positive {
        color: #16a34a !important;
        /* green */
    }

    .negative {
        color: #dc2626 !important;
        /* red */
    }

    .entity-balance.positive {
        color: #9ca3af !important;
        /* override green */
    }

    /* Right side */
    .right-side {
        display: flex;
        align-items: center;
    }

    .left-side {
        display: flex;
        align-items: center;
        position: relative;
        /* dropdown ke liye */
    }

    .entity-name {
        color: #9ca3af;
        font-size: 14px;

    }

    /* parent wrapper */
    .parent-arrows {
        display: flex;
        align-items: center;
        gap: 4px;
        /* text aur arrows ke darmiyan gap */
        position: relative;
        cursor: pointer;
    }

    /* counter arrows (increment/decrement) */
    .counter-arrows {
        display: flex;
        flex-direction: column;
        line-height: 6px;
        /* arrows close ho */
        font-size: 9px;
        opacity: 0;
        /* default hidden */
        transition: 0.2s;
    }

    /* show arrows only when parent has 'active' class */
    .parent-arrows.active .counter-arrows {
        opacity: 1;
    }

    /* arrows styling */
    .counter-arrows i {
        margin: 0;
        padding: 0;
        line-height: 6px;
        color: #6b7280;
    }

    .counter-arrows {
        display: flex;
        flex-direction: column;
        /* vertical stacked arrows */
    }

    .counter-arrows i {
        font-size: 8px;
        color: #9ca3af;
        cursor: pointer;
        margin: 0;
        padding: 0;
    }

    .table-wrapper {
        position: relative;
    }


    /* Parent row */
    .entity-detail-meta-row {
        display: flex;
        gap: 40px;
        /* space between the 3 items */
        align-items: flex-start;
        margin-top: 16px;
        /* space from any content above */
    }

    /* Each meta block */
    .entity-detail-meta {
        display: flex;
        flex-direction: column;
    }

    /* Heading */
    .meta-heading {
        color: #9ca3af;
        /* gray color */
        font-weight: 400;
        font-size: 12px;
        margin-bottom: 4px;
        /* spacing between heading and value */
    }

    /* Value */
    .meta-value {
        font-size: 14px;
        color: #111827;
        /* darker text for value */
        display: flex;
        align-items: center;
        gap: 6px;
        /* space between icon and text */
    }

    /* Optional: responsive slight gap on smaller screens */
    @media (max-width: 768px) {
        .counter-arrows {
            margin-left: 4px;
            /* small space on mobile */
        }

    }

    .loan-details-panel {
        padding: 16px 20px;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    }

    .loan-details-panel h4 {
        margin: 0;
        font-weight: 600;
        font-size: 20px;
        margin-bottom: 10px;
    }

    @media (max-width:768px) {

        .txn-table th {
            padding: 10px 6px;
            font-size: 13px;
        }

        .table-main {
            gap: 4px;
        }

        .sort-arrows {
            right: 16px;
            font-size: 8px;
        }

        .table-filter-icon {
            font-size: 12px;
        }

    }

    /* Actions dropdown in table */
    .action-dropdown {
        position: relative;
        display: inline-block;
    }

    .action-toggle {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: #6b7280;
        font-size: 16px;
    }

    .action-toggle:hover {
        color: #111827;
    }

    .action-menu {
        position: fixed;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        min-width: 140px;
        display: none;
        z-index: 2000;
        padding: 4px 0;
    }

    .action-item {
        width: 100%;
        text-align: left;
        background: transparent;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 13px;
        color: #1f2937;
    }

    .action-item:hover {
        background: #f3f4f6;
    }

    .loan-th-inner {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        width: 100%;
    }

    .loan-filter-icon {
        color: #b8bec7;
        cursor: pointer;
        font-size: 10px;
        margin-left: auto;
    }

    .loan-filter-icon:hover,
    .loan-filter-icon.active {
        color: #e53e3e;
    }

    .loan-col-resize-handle {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 5px;
        cursor: col-resize;
        z-index: 2;
    }

    .loan-col-resize-handle:hover,
    .loan-col-resize-handle.resizing {
        background: #2563eb;
        opacity: .4;
    }

    .loan-col-filter-dd {
        display: none;
        position: fixed;
        z-index: 9999;
        min-width: 220px;
        padding: 16px 16px 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, .15);
    }

    .loan-col-filter-dd.open {
        display: block;
    }

    .lcfd-title {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
    }

    .lcfd-cb-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 7px 2px;
        font-size: 13px;
        color: #374151;
        cursor: pointer;
    }

    .lcfd-cb-row input[type=checkbox] {
        width: 15px;
        height: 15px;
        accent-color: #2563eb;
    }

    .lcfd-select,
    .lcfd-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 6px;
        padding: 9px 10px;
        font-size: 13px;
        color: #374151;
        background: #fff;
        outline: none;
        margin-bottom: 10px;
    }

    .lcfd-select:focus,
    .lcfd-input:focus {
        border-color: #2563eb;
    }

    .lcfd-actions {
        display: flex;
        gap: 8px;
        margin-top: 14px;
    }

    .lcfd-clear,
    .lcfd-apply {
        flex: 1;
        border-radius: 20px;
        padding: 8px 0;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .lcfd-clear {
        border: 1.5px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
    }

    .lcfd-apply {
        border: none;
        background: #e53e3e;
        color: #fff;
    }

    .lcfd-clear:hover {
        background: #f3f4f6;
    }

    .lcfd-apply:hover {
        background: #b91c1c;
    }

    .loan-no-results td {
        text-align: center;
        color: #9ca3af;
        padding: 48px 0 !important;
    }

    .loan-detail-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }

    .btn-loan-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 38px;
        padding: 0 16px;
        border: 1.5px solid #ef233c;
        border-radius: 999px;
        background: #fff;
        color: #ef233c;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-loan-action:hover {
        background: #fff5f7;
    }

    .loan-action-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        min-width: 190px;
        padding: 8px 0;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .16);
        display: none;
        z-index: 2200;
    }

    .loan-action-menu.open {
        display: block;
    }

    .loan-action-menu button {
        width: 100%;
        border: none;
        background: transparent;
        padding: 10px 18px;
        text-align: left;
        color: #374151;
        font-size: 13px;
        cursor: pointer;
    }

    .loan-action-menu button:hover {
        background: #f3f4f6;
    }

    .loan-mini-modal .modal-dialog {
        max-width: 380px;
    }

    .loan-mini-modal .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .25);
    }

    .loan-mini-modal .modal-header {
        padding: 16px 18px;
    }

    .loan-mini-modal .modal-title {
        font-size: 20px;
        color: #374151;
        font-weight: 700;
    }

    .loan-mini-modal .modal-body {
        padding: 14px 18px 18px;
    }

    .loan-mini-modal .form-label {
        color: #667085;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .loan-mini-modal .form-control,
    .loan-mini-modal .form-select {
        height: 38px;
        border-radius: 7px;
        border-color: #c7cce0;
        font-size: 14px;
    }

    .loan-mini-modal .modal-footer {
        border-top: none;
        padding: 0 18px 18px;
        gap: 10px;
    }

    .btn-loan-cancel {
        border: none;
        border-radius: 999px;
        background: #f3f4f6;
        color: #667085;
        padding: 10px 22px;
        font-weight: 700;
    }

    .btn-loan-save {
        border: none;
        border-radius: 999px;
        background: #ef233c;
        color: #fff;
        padding: 10px 24px;
        font-weight: 700;
    }

    .btn-loan-save:hover {
        background: #d90429;
    }

</style>
<script>
    function toggleFilter() {
        let dropdown = document.getElementById("filterDropdown");

        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const filterIcon = document.querySelector(".filter-icon");
        if (filterIcon) {
            filterIcon.onclick = function() {
                document.querySelector(".filter-dropdown")?.classList.toggle("show");
            }
        }
    });

    function toggleHeaderDropdown(element) {
        const dropdownMenu = element.nextElementSibling;
        const isVisible = dropdownMenu.style.display === 'block';
        dropdownMenu.style.display = isVisible ? 'none' : 'block';

        // Optional: rotate arrow
        element.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function toggleSort(el) {
        el.classList.toggle("active");
    }

    function toggleParentArrows(el) {
        el.classList.toggle('active');
    }

    // Toggle dropdown on icon click
    function toggleFilterDropdown(icon) {
        const dropdown = icon.nextElementSibling;
        dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
    }

    // Close dropdown if clicked outside
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.filter-dropdown').forEach(dd => {
            if (!dd.contains(e.target) && !dd.previousElementSibling.contains(e.target)) {
                dd.style.display = 'none';
            }
        });
    });

    // Clear button functionality
    document.querySelectorAll('.clear-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const checkboxes = this.closest('.filter-dropdown').querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
        });
    });

</script>

@section('title', 'Vyapar — Loan Accounts')
@section('description', 'Track and manage loan accounts with processing fee controls backed by bank balances.')
@section('page', 'loan-accounts')

@section('content')
<div class="uper-panel">
    <div class="panel-main">
        <div class="text">
            <h1>Loan Accounts</h1>
        </div>

        <div class="action-buttons">
            <button class="btn-add-entity" id="addLoanBtn" type="button">
                <i class="fa-solid fa-plus me-1"></i> Add Loan
            </button>
            <button class="btn-settings" title="Settings">
                <i class="fa-solid fa-gear"></i>
            </button>
            <button class="btn-ellipsis" title="More Options">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" role="alert">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="split-pane">
    <div class="split-left">
        <div class="list-panel-header">
            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control search-input" placeholder="Search loans" id="loanSearchInput">
            </div>
        </div>
        {{-- New Change --}}
        <div class="list-header d-flex justify-content-between px-3 py-2 border-bottom">
            <span class="sortable" data-sort="name">
                Account Name <i class="fa fa-sort"></i>
            </span>

            <span class="sortable" data-sort="amount">
                Amount <i class="fa fa-sort"></i>
            </span>
        </div>
        {{-- End --}}
        <ul class="entity-list" id="loanList">
            @foreach($loanAccounts as $loan)
            <li class="{{ $loop->first ? 'active' : '' }}" data-loan="{{ $loan->id }}" data-account-number="{{ $loan->account_number }}" data-lender-bank="{{ $loan->lenderBank?->display_with_account ?? '' }}" data-current-balance="{{ $loan->current_balance }}" data-interest-rate="{{ $loan->interest_rate }}">
                <span class="entity-name">{{ $loan->display_name }}</span>
                <span class="entity-balance {{ $loan->current_balance < 0 ? 'negative' : 'positive' }}">₹ {{ number_format($loan->current_balance, 2) }}</span>
            </li>
            @endforeach

            @if($loanAccounts->isEmpty())
            <li class="text-center text-muted py-4">No loans found. Add a new loan to get started.</li>
            @endif
        </ul>
    </div>

    <div class="split-right">
        <div class="detail-panel-header">
            <div>
                <div class="entity-detail-name" id="loanDetailName" style="font-weight: 400;">
                    <span id="loanDetailNameText">{{ optional($loanAccounts->first())->display_name ?? 'Select a loan' }}</span>
                    <button class="btn-icon" title="Edit" id="loanDetailEditBtn">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                </div>
                <div class="entity-detail-meta-row">
                    <div class="entity-detail-meta">
                        <div class="meta-heading">Account Number</div>
                        <div class="meta-value" id="loanDetailAccountNumber">{{ optional($loanAccounts->first())->account_number ?? '-' }}</div>
                    </div>
                    <div class="entity-detail-meta">
                        <div class="meta-heading">Lender Bank</div>
                        <div class="meta-value" id="loanDetailLenderBank">{{ optional($loanAccounts->first())->lenderBank?->display_with_account  }}</div>
                    </div>
                    <div class="entity-detail-meta">
                        <div class="meta-heading"> Balance Amount</div>
                        <div class="meta-value" id="loanDetailCurrentBalance">₹ {{ number_format(optional($loanAccounts->first())->current_balance ?? 0, 2) }}</div>
                    </div>
                    <div class="entity-detail-meta">
                        <div class="meta-heading">Interest Rate</div>
                        <div class="meta-value" id="loanDetailInterestRate">{{ optional($loanAccounts->first())->interest_rate ? number_format(optional($loanAccounts->first())->interest_rate, 2) . '%' : '-' }}</div>
                    </div>

                </div>
            </div>
            <div class="loan-detail-actions">
                <button type="button" class="btn-loan-action" id="loanTransactionMenuBtn">
                    Make Payment <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="loan-action-menu" id="loanTransactionMenu">
                    <button type="button" data-loan-transaction-open="emi_pay">Make Payment</button>
                    <button type="button" data-loan-transaction-open="loan_adjustment">Take more loan</button>
                    <button type="button" data-loan-transaction-open="loan_charge">Charges on Loan</button>
                </div>
            </div>
        </div>

        <div class="detail-panel-body">
            <div class="table-header">
                <h6 class="fw-600 mb-3" style="font-size: 14px !important;">Loan Accounts</h6>
                <div class="header-icons">
                    <div class="table-search-box">
                        <input type="text" class="form-control form-control-sm" id="tableSearchInput" placeholder="Search table">
                    </div>
                    <button type="button" class="btn btn-sm btn-light" id="focusSearchBtn" title="Search">
                        <i class="fa fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="exportExcelBtn" title="Export to Excel">
                        <i class="fa fa-file-excel"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="printTableBtn" title="Print">
                        <i class="fa fa-print"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="txn-table" id="loanTable">
                    <thead>
                        <tr>
                            <th data-col="type">
                                <span class="loan-th-inner">Type <i class="fa-solid fa-filter loan-filter-icon" data-filter-target="lcf-type"></i></span>
                                <div class="loan-col-resize-handle" data-col="type"></div>
                            </th>
                            <th data-col="date">
                                <span class="loan-th-inner">Date <i class="fa-solid fa-filter loan-filter-icon" data-filter-target="lcf-date"></i></span>
                                <div class="loan-col-resize-handle" data-col="date"></div>
                            </th>
                            <th data-col="principal">
                                <span class="loan-th-inner">Principal <i class="fa-solid fa-filter loan-filter-icon" data-filter-target="lcf-principal"></i></span>
                                <div class="loan-col-resize-handle" data-col="principal"></div>
                            </th>

                            <th data-col="charges">
                                <span class="loan-th-inner">Interest & Other Charges <i class="fa-solid fa-filter loan-filter-icon" data-filter-target="lcf-charges"></i></span>
                                <div class="loan-col-resize-handle" data-col="charges"></div>
                            </th>
                            <th data-col="total_amount">
                                <span class="loan-th-inner">Total Amount <i class="fa-solid fa-filter loan-filter-icon" data-filter-target="lcf-total-amount"></i></span>
                                <div class="loan-col-resize-handle" data-col="total_amount"></div>
                            </th>
                            <th data-col="loan_received_in">
                                <span class="loan-th-inner">Bank/Cash <i class="fa-solid fa-filter loan-filter-icon" data-filter-target="lcf-loan-received-in"></i></span>
                                <div class="loan-col-resize-handle" data-col="loan_received_in"></div>
                            </th>

                            <th data-col="actions">Actions<div class="loan-col-resize-handle" data-col="actions"></div></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loanAccounts as $loan)
                            @php
                                $rows = $loanTransactions[$loan->id] ?? collect();
                            @endphp
                            @forelse($rows as $txn)
                                @php
                                    $meta = $txn->meta ?? [];
                                    $isMore = in_array($txn->type, ['loan_more', 'loan_adjustment'], true);
                                    $isEmi = $txn->type === 'emi_pay';
                                    $principal = $isEmi ? (float) ($meta['principal'] ?? 0) : ($isMore ? (float) $txn->amount : 0);
                                    $charges = $isEmi ? (float) ($meta['charges'] ?? 0) : ($isMore ? 0 : (float) $txn->amount);
                                    $bankName = $txn->toBankAccount?->display_with_account ?? $txn->fromBankAccount?->display_with_account ?? '-';
                                    $typeLabel = match ($txn->type) {
                                        'loan_more', 'loan_adjustment' => 'Loan Adjustment',
                                        'emi_pay' => 'EMI Pay',
                                        default => 'Charges on Loan',
                                    };
                                @endphp
                                <tr data-loan-id="{{ $loan->id }}" data-transaction-id="{{ $txn->id }}" data-entry-type="{{ $txn->type }}" data-date="{{ optional($txn->transaction_date)->format('Y-m-d') }}" data-amount="{{ $txn->amount }}" data-principal="{{ $principal }}" data-interest="{{ $charges }}" data-bank-account-id="{{ $txn->to_bank_account_id ?? $txn->from_bank_account_id }}" data-details="{{ e(($meta['details'] ?? '') ?: $txn->description) }}">
                                    <td>{{ $typeLabel }}</td>
                                    <td>{{ optional($txn->transaction_date)->format('d/m/Y') ?? '-' }}</td>
                                    <td>Rs {{ number_format($principal, 2) }}</td>
                                    <td>Rs {{ number_format($charges, 2) }}</td>
                                    <td>Rs {{ number_format((float) $txn->amount, 2) }}</td>
                                    <td>{{ $bankName }}</td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button type="button" class="action-toggle" aria-label="Actions">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="action-menu">
                                                <button type="button" class="action-item" data-action="edit-transaction">View/Edit</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-loan-id="{{ $loan->id }}" class="loan-empty-row">
                                    <td colspan="7" class="text-center text-muted py-5">No loan transactions yet.</td>
                                </tr>
                            @endforelse
                        @endforeach
                        @if($loanAccounts->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No loans yet. Click Add Loan to create one.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach([
    'type' => ['type', 'Type', 'checkbox'],
    'date' => ['date', 'Date', 'date'],
    'principal' => ['principal', 'Principal', 'number'],
    'charges' => ['charges', 'Interest & Other Charges', 'number'],
    'total-amount' => ['total_amount', 'Total Amount', 'number'],
    'loan-received-in' => ['loan_received_in', 'Bank/Cash', 'checkbox'],
] as $filterId => [$filterCol, $filterLabel, $filterType])
<div class="loan-col-filter-dd" id="lcf-{{ $filterId }}" data-col="{{ $filterCol }}" data-filter-type="{{ $filterType }}">
    <div class="lcfd-title">{{ $filterLabel }}</div>
    @if($filterType === 'checkbox')
        <div class="loan-filter-options"></div>
    @elseif($filterType === 'date')
        <select class="lcfd-select" data-filter-op>
            <option value="equal">Equal To</option>
            <option value="before">Before</option>
            <option value="after">After</option>
        </select>
        <input type="date" class="lcfd-input" data-filter-value>
    @elseif($filterType === 'number')
        <select class="lcfd-select" data-filter-op>
            <option value="equal">Equal to</option>
            <option value="lt">Less Than</option>
            <option value="gt">Greater Than</option>
        </select>
        <input type="number" class="lcfd-input" data-filter-value placeholder="{{ strtoupper($filterLabel) }}" step="0.01">
    @else
        <select class="lcfd-select" data-filter-op>
            <option value="contains">Contains</option>
            <option value="exact">Exact match</option>
        </select>
        <input type="text" class="lcfd-input" data-filter-value placeholder="{{ strtoupper($filterLabel) }}">
    @endif
    <div class="lcfd-actions">
        <button type="button" class="lcfd-clear" data-clear-loan-filter="lcf-{{ $filterId }}">Clear</button>
        <button type="button" class="lcfd-apply" data-apply-loan-filter>Apply</button>
    </div>
</div>
@endforeach

<div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('loan-accounts.store') }}" id="loanForm">
                @csrf
                <input type="hidden" name="_method" id="loanFormMethod" value="POST">
                <input type="hidden" name="loan_id" id="loanIdField" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="loanModalLabel">Add Loan Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lender Bank</label>
                            <select name="lender_bank_id" class="form-select">
                                <option value="">Select bank...</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Balance <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="current_balance" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Balance As Of</label>
                            <input type="date" name="balance_as_of" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loan Received In <span class="text-danger">*</span></label>
                            <select name="received_in" class="form-select" required>
                                <option value="">Select bank...</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Processing Fee Paid From</label>
                            <select name="processing_fee_paid_from_id" class="form-select">
                                <option value="">Select bank...</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Processing Fee</label>
                            <input type="number" step="0.01" name="processing_fee" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" step="0.01" name="interest_rate" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Term Duration (months)</label>
                            <input type="number" name="term_months" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="loanFormSubmit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade loan-mini-modal" id="loanEmiPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="loanEmiPayForm">
                <div class="modal-header">
                    <h5 class="modal-title">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="transaction_id">
                    <div class="mb-3">
                        <label class="form-label">Principal Amount<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="principal_amount" value="0" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interest Amount<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="interest_amount" value="0" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="number" class="form-control" name="amount" value="0" min="0" step="0.01" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Paid From<span class="text-danger">*</span></label>
                        <select class="form-select" name="bank_account_id" required>
                            <option value="">Select bank/cash...</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-loan-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-loan-save">Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade loan-mini-modal" id="takeMoreLoanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="takeMoreLoanForm">
                <div class="modal-header">
                    <h5 class="modal-title">Take More Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="transaction_id">
                    <div class="mb-3">
                        <label class="form-label">Increase Loan By<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" value="0" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Loan Received In</label>
                        <select class="form-select" name="bank_account_id" required>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-loan-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-loan-save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade loan-mini-modal" id="loanChargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="loanChargeForm">
                <div class="modal-header">
                    <h5 class="modal-title">Charges On Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="transaction_id">
                    <div class="mb-3">
                        <label class="form-label">Transaction Type Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="details" placeholder="Example: Penalty on Missing EMI" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" value="0" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loan Received In</label>
                        <select class="form-select" name="bank_account_id">
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->display_with_account }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Total Amount</label>
                        <input type="number" class="form-control" name="total_amount" value="0" disabled>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-loan-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-loan-save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/loan.js') }}"></script>
@endpush
