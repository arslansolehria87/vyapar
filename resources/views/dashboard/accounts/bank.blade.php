@extends('layouts.app')

<style>

  

/* uper panel styling */
.uper-panel{
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  height: 8vh;
  padding: 8px 16px; /* horizontal padding */
  background-color: white;
  display: flex;
  align-items: center; /* vertically center content */
}

.panel-main{
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center; /* vertically center all items */
  width: 100%;
}

.text{
  display: flex;
  align-items: center; /* vertically center h1 + arrow */
  gap: 6px;
}

.text h1{
  font-size: 20px;
  margin: 0; /* remove default margin */
}

.header-dropdown {
  position: relative;
  display: flex;
  align-items: center; /* center h1 + arrow */
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
  margin-top: 4px; /* spacing below your label */
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
  background-color: #dbeafe; /* light blue */
  color: black;
}

.dropdown-option.selected::after {
  content: '✓';
  color: #3b82f6; /* light blue tick */
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
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  display: none;
  z-index: 999;
  min-width: 120px;
}
 input:focus {
  border-color: #3b82f6 !important; /* light blue border on focus */
  box-shadow: 0 0 4px rgba(96, 165, 250, 0.5); /* subtle blue glow */
  outline: none; /* remove default outline */
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

.btn-add-entity{
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
  right: 8px;           /* top-right corner */
  transform: translateY(-50%);
  color: #0f6fcc;       /* light blue */
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
.search-box{
  position: relative;
  width: 420px; /* width increase */
}

.search-box i{
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 14px;
  z-index: 2;
}

.search-input{
  width: 100%;
  height: 42px;
  padding-left: 40px !important; /* IMPORTANT: icon ke liye space */
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #9ca3af;
}

.search-input::placeholder{
  color: #9ca3af !important;
}

.filter-wrapper {
  position: relative; /* zaroori */
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
  background: transparent;        /* no full background */
  border: none;                   /* remove full border */
  border-bottom: 2px solid #0f6fcc; /* only bottom outline */
  padding: 6px 8px;
  cursor: pointer;
  color: #0f6fcc;                 /* icon color */
  border-radius: 4px 4px 0 0;     /* optional rounded top corners */
  font-size: 16px;
  transition: background 0.2s, color 0.2s;
}

.btn-icon:hover {
  background: rgba(15,111,204,0.1); /* slight blue background on hover */
  color: #074ea0;                     /* slightly darker icon on hover */
}
.btn-icon-right {
  background: transparent;          /* no full background */
  border: none;                     /* remove all borders first */
  border-right: 2px solid #0f6fcc; /* vertical outline on right side */
  padding: 6px 8px;
  cursor: pointer;
  color: #0f6fcc;                   /* icon color */
  font-size: 16px;
  display: inline-flex;
  align-items: center;              /* vertically center icon */
  justify-content: center;          /* horizontally center icon */
  border-radius: 4px 0 0 4px;       /* optional rounded left corners */
  transition: background 0.2s, color 0.2s;
}

.btn-icon-right:hover {
  background: rgba(15,111,204,0.1); /* subtle hover background */
  color: #074ea0;                   /* darker icon on hover */
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
  gap: 12px; /* space between left and right */
}
/* left section */
/* SPLIT LAYOUT */
.split-pane{
display:flex;
width:100%;
}

/* LEFT PANEL */
.split-left{
width:240px !important;
flex:0 0 240px;
padding:0;
margin:0;
box-sizing:border-box;
}

/* RIGHT PANEL */
.split-right{
flex:1;
padding:0;
}

/* REMOVE UL DEFAULT SPACE */
.entity-list{
list-style:none;
padding:0;
margin:0;
}

/* LIST ITEMS */
.entity-list li{
display:flex;
align-items:center;
justify-content:space-between;
padding:6px 8px;
}

.bank-list-info {
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.bank-list-right {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
  flex-shrink: 0;
}

.bank-list-right .entity-balance {
  margin-left: 0;
}

.bank-list-menu-wrap {
  position: relative;
  flex-shrink: 0;
}

.bank-list-menu-toggle {
  width: 26px;
  height: 26px;
  border: none;
  border-radius: 4px;
  background: transparent;
  color: #9ca3af;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 13px;
}

.bank-list-menu-toggle:hover {
  background: #f3f4f6;
  color: #374151;
}

.bank-list-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 124px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
  padding: 5px 0;
  display: none;
  z-index: 2200;
}

.bank-list-menu.open {
  display: block;
}

.bank-list-menu-item {
  width: 100%;
  border: none;
  background: transparent;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  font-size: 13px;
  text-align: left;
  cursor: pointer;
}

.bank-list-menu-item:hover {
  background: #f3f4f6;
}

.bank-list-menu-item.delete {
  color: #dc2626;
}

.bank-list-menu-item.delete:hover {
  background: #fee2e2;
}

.bank-list-sort-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 8px;
  background: #f9fafb;
  border-top: 1px solid #f3f4f6;
  border-bottom: 1px solid #f3f4f6;
  color: #9ca3af;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .06em;
}

.bank-list-sort {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: none;
  background: transparent;
  color: inherit;
  font: inherit;
  cursor: pointer;
  padding: 0;
}

.bank-list-sort:hover,
.bank-list-sort.active {
  color: #374151;
}

.bank-list-sort-arrow {
  display: inline-flex;
  width: 10px;
  color: #6b7280;
  opacity: 1;
}

.bank-list-sort-arrow::after {
  content: '↕';
  font-size: 10px;
  line-height: 1;
}

.bank-list-sort.asc .bank-list-sort-arrow::after {
  content: '↑';
}

.bank-list-sort.desc .bank-list-sort-arrow::after {
  content: '↓';
}

.bank-list-sort-payment {
  margin-left: auto;
}

/* HEADER AREA */
.list-panel-header{
padding:8px;
}

/* FILTER WRAPPER */
.filter-wrapper{
display:flex;
align-items:center;
gap:6px;
padding:0;
margin:0;
}

/* PARTY NAME + ARROWS */
.parent-arrows{
display:flex;
align-items:center;
gap:4px;
}

/* SEPARATOR LINE */
.separator{
width:1px;
height:18px;
background:#e5e7eb;
margin:0 6px;
}

/* AMOUNT SECTION */
.entity-balance{
margin-left:auto;
}

/* Left side contains Party Name + Filter */
.left-side {
  display: flex;
  align-items: center;
  position: relative;
  padding-right: 12px;
  border-right: 1px solid #d1d5db; /* vertical line */
}

/* filter dropdown */
.filter-wrapper{
  display:flex;
  align-items:center;
  gap:6px;
  position:relative;
}

.filter-wrapper span{
  flex-shrink:0;
}

.table-filter-icon{
  font-size:16px;
  color:#6b7280;
  cursor:pointer;
  flex-shrink:0;
  margin-left:4px;
}

.filter-dropdown{
  position:absolute;
  top:28px;
  margin-left: 12px;
  width:200px;
  background:#f3f4f6;
  border:1px solid #d1d5db;
  border-radius:8px;
  box-shadow:0 2px 8px rgba(0,0,0,0.25);
  display:none;
  flex-direction:column;
  z-index:1000;
  padding: 10px;

}

.filter-options{
  overflow-y:auto;
  max-height:160px;
  padding:8px;
  display:flex;
  flex-direction:column;
  gap:4px;


}

.filter-options label{
  display:flex;
  align-items:center;
  gap:6px;
  font-size:10px;
  color:#374151;
  cursor:pointer;
  font-weight: 400;
}

.filter-options::-webkit-scrollbar{
  width:6px;
}
.filter-options::-webkit-scrollbar-thumb{
  background:#9ca3af;
  border-radius:3px;
}
.filter-options::-webkit-scrollbar-track{
  background:#f3f4f6;
}

.filter-actions{
  display:flex;
  justify-content:space-between;
  padding:8px;
  border-top:1px solid #d1d5db;
  background:#f9fafb;
  border-radius:0 0 8px 8px;
}

.clear-btn{
  background:whitesmoke;
  color:#6b7280;
  border:none;
  padding:6px 12px;
  border-radius:16px;
  transition:0.2s;
  font-size: 14px !important;
  font-weight: 600;
}
.clear-btn:hover{
  background:#e5e7eb;
}


.apply-btn:hover{
   background: #8B0000 !important;
}
/* Right side (Amount) */
.right-side {
  padding-left: 32px; /* space after line */
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
.table-main{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:6px;
}
.left-side {
  display: flex;
  align-items: center;
  gap: 8px; /* Party Name + icon spacing */
  position: relative; /* dropdown ke liye relative parent */
}
.txn-table th{
  border-right:1px solid #d1d5db;;
  padding:12px 16px;
  background:#f9fafb;
}
.txn-table th{
  font-size:12px !important;
  text-transform: capitalize !important;
}
.filter-icon {
  color: red;
  cursor: pointer;
  font-size: 16px;
}

.table-main{
display:flex;
align-items:center;
gap:6px;
cursor:pointer;
position:relative;
}
/* table layout equal columns */
.txn-table{
width:100%;
border-collapse:collapse;
table-layout:fixed;
}

.txn-table tbody tr.active-row {
  background-color: rgba(255, 255, 255, 0.08);
}

/* table heads */
.txn-table th{
padding:10px 12px;
text-align:left;
position:relative;
}
.table-header{
display:flex;
justify-content:space-between;
align-items:center !important;
margin-bottom:8px;
}

.table-header h6{
margin:0;
font-weight:600;
font-size:16px;
}

.header-icons{
display:flex;
align-items:center;
gap:12px;
}

.header-icons i{
font-size:16px;
color: #9ca3af;
cursor:pointer;
transition:0.2s;
line-height:1;
}


/* header content */
.table-main{
display:flex;
align-items:center;
gap:6px;
cursor:pointer;
position:relative;
}

/* sort arrows */
.sort-arrows{
display:flex;
flex-direction:column;
position:absolute;
right:28px;   /* filter icon se pehle fixed position */
font-size:9px;
line-height:7px;
opacity:0;
transition:0.2s;
}

/* arrows spacing */
.sort-arrows i{
margin:0;
padding:0;
}

/* show arrows on click */
.table-main.active .sort-arrows{
opacity:1;
}

/* filter icon */
.table-filter-icon{
margin-left:auto;
font-size:12px;
color:#9ca3af;
}

.table-main{
display:flex;
align-items:center;
justify-content:space-between; /* text left, arrows+filter right */
gap:4px;
position:relative;
width:100%;
}
.table-main span{
flex-shrink:0;
}

/* arrows container */
.sort-arrows{
position:absolute;
display:flex;
flex-direction:column;
line-height:1px;
font-size:9px;
opacity:0;       /* hidden but space reserved */
transition:0.2s;
margin-right:4px; /* gap from filter icon */
}
.sort-arrows i{
margin:0;
padding:0;
line-height:3px;
}


/* arrows style */
.sort-arrows i{
color:#6b7280;
}

/* click par show */
.table-main.active .sort-arrows{
opacity:1;
}

/* Dropdown */

.split-pane{
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
  color: #16a34a !important; /* green */
}

.negative {
  color: #dc2626 !important; /* red */
}

.entity-balance.positive {
  color: #9ca3af !important; /* override green */
}
/* Right side */
.right-side {
  display: flex;
  align-items: center;
}
.left-side {
  display: flex;
  align-items: center;
  position: relative; /* dropdown ke liye */
}

.entity-name {
  color: #9ca3af;
  font-size: 14px;

}
/* parent wrapper */
.parent-arrows{
display:flex;
align-items:center;
gap:4px;           /* text aur arrows ke darmiyan gap */
position:relative;
cursor:pointer;
}

/* counter arrows (increment/decrement) */
.counter-arrows{
display:flex;
flex-direction:column;
line-height:6px;      /* arrows close ho */
font-size:9px;
opacity:0;             /* default hidden */
transition:0.2s;
}

/* show arrows only when parent has 'active' class */
.parent-arrows.active .counter-arrows{
opacity:1;
}

/* arrows styling */
.counter-arrows i{
margin:0;
padding:0;
line-height:6px;
color:#6b7280;
}

.counter-arrows {
  display: flex;
  flex-direction: column; /* vertical stacked arrows */
}

.counter-arrows i {
  font-size: 8px;
  color: #9ca3af;
  cursor: pointer;
  margin: 0;
  padding: 0;
}
.table-wrapper{
  position: relative;
}


/* Parent row */
.entity-detail-meta-row {
  display: flex;
  gap: 40px;             /* space between the 3 items */
  align-items: flex-start;
  margin-top: 16px;      /* space from any content above */
}

/* Each meta block */
.entity-detail-meta {
  display: flex;
  flex-direction: column;
}

/* Heading */
.meta-heading {
  color: #9ca3af;        /* gray color */
  font-weight: 400;
  font-size: 12px;
  margin-bottom: 4px;    /* spacing between heading and value */
}

/* Value */
.meta-value {
  font-size: 14px;
  color: #111827;        /* darker text for value */
  display: flex;
  align-items: center;
  gap: 6px;              /* space between icon and text */
}
/* Optional: responsive slight gap on smaller screens */
@media (max-width: 768px) {
  .counter-arrows {
    margin-left: 4px; /* small space on mobile */
  }

}

@media (max-width:768px){

.txn-table th{
padding:10px 6px;
font-size:13px;
}

.table-main{
gap:4px;
}

.sort-arrows{
right:16px;
font-size:8px;
}

.table-filter-icon{
font-size:12px;
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
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: white;
  border: 1px solid #edf0f5;
  border-radius: 14px;
  box-shadow: 0 16px 34px rgba(15, 23, 42, 0.16);
  min-width: 220px;
  display: none;
  z-index: 2000;
  padding: 10px 0;
}

.action-item {
  width: 100%;
  text-align: left;
  background: transparent;
  border: none;
  padding: 12px 22px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  color: #1f2937;
}

.action-item:hover {
  background: #f3f8ff;
}

.action-cell {
  width: 54px;
  text-align: center;
  position: relative;
  overflow: visible !important;
}

.bulk-menu-wrap {
  position: relative;
}

.bulk-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 180px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
  padding: 6px 0;
  display: none;
  z-index: 2100;
}

.bulk-menu.open {
  display: block;
}

.bulk-menu-item {
  width: 100%;
  border: none;
  background: transparent;
  text-align: left;
  padding: 10px 14px;
  font-size: 13px;
  color: #1f2937;
  cursor: pointer;
}

.bulk-menu-item:hover {
  background: #f3f4f6;
}

.bank-inactive {
  opacity: 0.65;
}

.bank-status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  margin-left: 8px;
  vertical-align: middle;
}

.bank-status-pill.active {
  background: #dcfce7;
  color: #166534;
}

.bank-status-pill.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.bulk-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  padding: 20px;
}

.bulk-overlay.open {
  display: flex;
}

.bulk-modal {
  width: min(720px, 100%);
  max-height: 90vh;
  overflow: hidden;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
  display: flex;
  flex-direction: column;
}

.bulk-modal-header,
.bulk-modal-footer {
  padding: 18px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.bulk-modal-footer {
  border-bottom: none;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.bulk-modal-body {
  padding: 18px 20px;
  overflow: auto;
}

.bulk-modal-title {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.bulk-modal-info {
  font-size: 13px;
  color: #6b7280;
  margin-top: 4px;
}

.bulk-search {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 14px;
  margin-bottom: 16px;
}

.bulk-table {
  width: 100%;
  border-collapse: collapse;
}

.bulk-table th,
.bulk-table td {
  padding: 12px 10px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  text-align: left;
}

.bulk-table th {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6b7280;
}

.bulk-password-box {
  margin-top: 14px;
  padding: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #f8fafc;
  display: none;
}

.bulk-password-box.open {
  display: block;
}

.bulk-password-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.bulk-password-input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 14px;
}

.bulk-password-error {
  color: #dc2626;
  font-size: 12px;
  margin-top: 8px;
  display: none;
}

.bulk-password-error.show {
  display: block;
}

.bulk-empty {
  text-align: center;
  color: #94a3b8;
  padding: 28px 12px;
}

.bulk-footer-note {
  font-size: 13px;
  color: #6b7280;
}

.bulk-footer-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.detail-panel-header {
  padding: 16px 20px 14px;
  border-bottom: 1px solid #f3f4f6;
  background: #fff;
}

.detail-panel-body {
  padding: 14px 20px 0;
}

.txn-table-wrap {
  width: 100%;
  overflow: visible;
  border-top: 1px solid #ebebeb;
}

.txn-table {
  min-width: 0;
  width: 100%;
  table-layout: fixed;
}

.txn-table th {
  padding: 12px 10px !important;
  font-size: 11px !important;
  font-weight: 600;
  text-transform: capitalize !important;
  color: #9ca3af;
  background: #f9fafb;
  border-bottom: 1px solid #ebebeb;
  border-right: 1px solid #d1d5db;
  white-space: nowrap;
  position: relative;
  user-select: none;
}

.txn-table th[data-col="actions"] {
  border-right: none;
}

.txn-table td {
  padding: 10px 8px;
  font-size: 12px;
  color: #374151;
  border-bottom: 1px solid #f3f4f6;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: middle;
}

.txn-table th[data-col="type"],
.txn-table th[data-col="invoice"],
.txn-table th[data-col="amount"] {
  width: 12% !important;
}

.txn-table th[data-col="party"],
.txn-table th[data-col="bank"] {
  width: 14% !important;
}

.txn-table th[data-col="payment"],
.txn-table th[data-col="date"] {
  width: 13% !important;
}

.txn-table th[data-col="actions"] {
  width: 44px !important;
}

.txn-table tbody tr:hover td {
  background: #fafafa;
}

.txn-table tbody tr.active-row td {
  background: #eff6ff;
}

.bank-th-inner {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  width: 100%;
  cursor: pointer;
}

.bank-th-label {
  overflow: hidden;
  text-overflow: ellipsis;
}

.bank-sort-arrow {
  display: inline-flex;
  align-items: center;
  color: #4b5563;
  font-size: 10px;
  line-height: 1;
  opacity: 0;
}

.txn-table th.sort-asc .bank-sort-arrow,
.txn-table th.sort-desc .bank-sort-arrow {
  opacity: 1;
}

.bank-sort-arrow::after {
  content: '↑';
}

.txn-table th.sort-desc .bank-sort-arrow::after {
  content: '↓';
}

.bank-filter-icon {
  color: #b8bec7;
  cursor: pointer;
  font-size: 10px;
  margin-left: auto;
}

.bank-filter-icon:hover,
.bank-filter-icon.active {
  color: #e53e3e;
}

.bank-col-resize-handle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 5px;
  cursor: col-resize;
  z-index: 2;
}

.bank-col-resize-handle:hover,
.bank-col-resize-handle.resizing {
  background: #2563eb;
  opacity: .4;
}

.bank-col-filter-dd {
  display: none;
  position: fixed;
  z-index: 9999;
  min-width: 220px;
  padding: 16px 16px 12px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 8px 30px rgba(0,0,0,.15);
}

.bank-col-filter-dd.open {
  display: block;
}

.bcfd-title {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
}

.bcfd-cb-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 2px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
}

.bcfd-cb-row input[type=checkbox] {
  width: 15px;
  height: 15px;
  accent-color: #2563eb;
}

.bcfd-select,
.bcfd-input {
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

.bcfd-input:focus,
.bcfd-select:focus {
  border-color: #2563eb;
}

.bcfd-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
}

.bcfd-clear,
.bcfd-apply {
  flex: 1;
  border-radius: 20px;
  padding: 8px 0;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.bcfd-clear {
  border: 1.5px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
}

.bcfd-apply {
  border: none;
  background: #e53e3e;
  color: #fff;
}

.bcfd-clear:hover {
  background: #f3f4f6;
}

.bcfd-apply:hover {
  background: #b91c1c;
}

.bank-no-results td {
  text-align: center;
  color: #9ca3af;
  padding: 48px 0 !important;
}

</style>

  <script>
    function toggleFilter(){
let dropdown = document.getElementById("filterDropdown");

if(dropdown.style.display === "block"){
dropdown.style.display = "none";
}else{
dropdown.style.display = "block";
}
}

document.addEventListener('DOMContentLoaded', function () {
  const filterIcon = document.querySelector(".filter-icon");
  if (filterIcon) {
    filterIcon.onclick = function(){
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

function toggleSort(el){
el.classList.toggle("active");
}

function toggleParentArrows(el){
  el.classList.toggle('active');
}

// Toggle dropdown on icon click
function toggleFilterDropdown(icon){
  const dropdown = icon.nextElementSibling;
  dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}

// Close dropdown if clicked outside
document.addEventListener('click', function(e){
  document.querySelectorAll('.filter-dropdown').forEach(dd=>{
    if(!dd.contains(e.target) && !dd.previousElementSibling.contains(e.target)){
      dd.style.display = 'none';
    }
  });
});

// Clear button functionality
document.querySelectorAll('.clear-btn').forEach(btn=>{
  btn.addEventListener('click', function(){
    const checkboxes = this.closest('.filter-dropdown').querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb=>cb.checked=false);
  });
});


  </script>

@section('title', 'Vyapar — Bank Account')
@section('description', 'Manage your business parties, customers, and suppliers in Vyapar accounting software.')
@section('page', 'bank-accounts')

@section('content')

<!-- uper panel -->
<div class="uper-panel">
  <div class="panel-main">

    <!-- Left: Header + Arrow -->
    <div class="text">
      <div class="header-dropdown">
        <h1>Bank Account</h1>
        <i class="fa fa-chevron-down arrow-icon" onclick="toggleHeaderDropdown(this)"></i>

      <div class="header-dropdown-menu">
  <label class="dropdown-item">
    Bank Account
    <i class="fa fa-check tick-icon"></i>
  </label>
</div>
      </div>
    </div>

    <!-- Right: Buttons -->
    <div class="action-buttons">
      <button class="btn-add-entity" data-bs-toggle="modal" data-bs-target="#addBankModal">
        <i class="fa-solid fa-plus me-1"></i> Add Bank Account
      </button>

      <button class="btn-settings" title="Settings">
        <i class="fa-solid fa-gear"></i>
      </button>

      <div class="bulk-menu-wrap">
        <button class="btn-ellipsis" title="More Options" id="bankBulkMenuBtn" type="button">
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <div class="bulk-menu" id="bankBulkMenu">
          <button type="button" class="bulk-menu-item" data-bulk-action="bulk-inactive">Bulk Inactive</button>
          <button type="button" class="bulk-menu-item" data-bulk-action="bulk-active">Bulk Active</button>
        </div>
      </div>
    </div>

  </div>
</div>

@if(session('success'))
  <div class="alert alert-success mt-3" role="alert" id="bankFlash">
    {{ session('success') }}
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger mt-3" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

  <div class="split-pane">


    <!-- Left: Bank Account List -->
    <div class="split-left">
      <div class="list-panel-header">
      <div class="search-box">
  <i class="fa fa-search"></i>
  <input type="text" class="form-control search-input" placeholder="Search Bank Account" id="bankSearchInput">
      </div>

      </div>
      <div class="bank-list-sort-header">
        <button type="button" class="bank-list-sort" data-sort="name">
          <span>BANK NAME</span>
          <span class="bank-list-sort-arrow"></span>
        </button>
        <button type="button" class="bank-list-sort bank-list-sort-payment" data-sort="amount">
          <span>PAYMENT</span>
          <span class="bank-list-sort-arrow"></span>
        </button>
      </div>
      <ul class="entity-list" id="bankList">
        @forelse($bankAccounts as $bank)
        <li class="{{ $loop->first ? 'active' : '' }}" data-bank="{{ $bank->id }}"
            data-account-number="{{ $bank->account_number }}"
            data-bank-name="{{ $bank->bank_name }}"
            data-opening-balance="{{ $bank->opening_balance }}"
            data-as-of-date="{{ optional($bank->as_of_date)->format('d/m/Y') }}"
            data-swift="{{ $bank->swift_code }}"
            data-iban="{{ $bank->iban }}"
            data-account-holder="{{ $bank->account_holder_name }}"
            data-print-on-invoice="{{ $bank->print_on_invoice ? '1' : '0' }}"
            data-is-active="{{ $bank->is_active ? '1' : '0' }}"
        >
          <span class="bank-list-info">
            <span class="entity-name">{{ $bank->display_with_account }}</span>
          </span>
          <span class="bank-list-right">
            <span class="entity-balance {{ (float) $bank->opening_balance < 0 ? 'negative' : 'positive' }}">₹ {{ number_format($bank->opening_balance, 2) }}</span>
            <span class="bank-list-menu-wrap">
              <button type="button" class="bank-list-menu-toggle" data-bank-list-menu-toggle title="More Options">
                <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <span class="bank-list-menu">
                <button type="button" class="bank-list-menu-item" data-bank-list-action="edit">
                  <i class="fa-solid fa-pen"></i>
                  <span>Edit</span>
                </button>
                <button type="button" class="bank-list-menu-item delete" data-bank-list-action="delete">
                  <i class="fa-solid fa-trash"></i>
                  <span>Delete</span>
                </button>
              </span>
            </span>
          </span>
        </li>
        @empty
        <li class="text-center text-muted py-5">
          No bank accounts found. Click <strong>Add Bank Account</strong> to get started.
        </li>
        @endforelse
      </ul>
    </div>
    <!-- Right: Bank Details -->
    <div class="split-right">
      <div class="detail-panel-header">
        <div>
          <div class="entity-detail-name" id="bankDetailName" style="font-weight: 400;">
            {{ optional($bankAccounts->first())->display_with_account ?? 'Select a bank account' }}
            <button class="btn-icon" title="Edit">
              <i class="fa-solid fa-pen"></i>
            </button>
          </div>
         <div class="entity-detail-meta-row">

  <div class="entity-detail-meta">
    <div class="meta-heading">Account Number</div>
    <div class="meta-value" id="bankDetailAccountNumber">{{ optional($bankAccounts->first())->account_number ?? '-' }}</div>
  </div>

  <div class="entity-detail-meta">
    <div class="meta-heading">Bank Name</div>
    <div class="meta-value" id="bankDetailBankName">{{ optional($bankAccounts->first())->bank_name ?? '-' }}</div>
  </div>

  <div class="entity-detail-meta">
    <div class="meta-heading"> Balance</div>
    <div class="meta-value" id="bankDetailOpeningBalance">₹ {{ number_format(optional($bankAccounts->first())->opening_balance ?? 0, 2) }}</div>
  </div>

</div>
        </div>
        <div class="action-buttons">

        </div>
      </div>
    <div class="detail-panel-body">
  <div class="table-header">
    <h6 class="fw-600 mb-3" style="font-size: 14px !important;">Bank Transactions</h6>
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


        <div class="txn-table-wrap">
        <table class="txn-table" id="bankTable">
          <thead>
            <tr>
              <th data-col="type" style="width:130px;">
                <span class="bank-th-inner" data-sort-col="type">
                  <span class="bank-th-label">Type</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-type"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="type"></div>
              </th>
              <th data-col="invoice" style="width:130px;">
                <span class="bank-th-inner" data-sort-col="invoice">
                  <span class="bank-th-label">Invoice No</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-invoice"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="invoice"></div>
              </th>
              <th data-col="party" style="width:150px;">
                <span class="bank-th-inner" data-sort-col="party">
                  <span class="bank-th-label">Party</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-party"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="party"></div>
              </th>
              <th data-col="bank" style="width:150px;">
                <span class="bank-th-inner" data-sort-col="bank">
                  <span class="bank-th-label">Bank Name</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-bank"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="bank"></div>
              </th>
              <th data-col="payment" style="width:140px;">
                <span class="bank-th-inner" data-sort-col="payment">
                  <span class="bank-th-label">Payment Type</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-payment"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="payment"></div>
              </th>
              <th data-col="date" style="width:150px;">
                <span class="bank-th-inner" data-sort-col="date">
                  <span class="bank-th-label">Date</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-date"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="date"></div>
              </th>
              <th data-col="amount" style="width:130px;">
                <span class="bank-th-inner" data-sort-col="amount">
                  <span class="bank-th-label">Amount</span><span class="bank-sort-arrow"></span>
                  <i class="fa-solid fa-filter bank-filter-icon" data-filter-target="bcf-amount"></i>
                </span>
                <div class="bank-col-resize-handle" data-col="amount"></div>
              </th>
              <th class="action-cell" data-col="actions" style="width:54px;"></th>
            </tr>
          </thead>
          <tbody>
            @forelse(($bankTransactions ?? collect()) as $transaction)
              @php
                $typeLabel = $transaction->type_label ?? '-';
                $amountClass = ($transaction->direction ?? 'in') === 'out' ? 'negative' : 'positive';
              @endphp
              <tr data-bank-id="{{ $transaction->bank_account_id }}"
                  data-transaction-url="{{ $transaction->source_url }}"
                  data-delete-url="{{ $transaction->delete_url }}"
                  data-history-url="{{ $transaction->history_url }}"
                  data-transaction-label="{{ $transaction->type_label ?? 'transaction' }}">
                <td>{{ $transaction->type_label ?? '-' }}</td>
                <td>{{ $transaction->invoice_no ?? '-' }}</td>
                <td>{{ $transaction->party_name ?? '-' }}</td>
                <td>{{ $transaction->bank_name ?? '-' }}</td>
                <td>{{ $transaction->payment_type ?? '-' }}</td>
                <td>{{ optional($transaction->created_at)->format('d/m/Y h:i A') }}</td>
                <td class="{{ $amountClass }}">₹ {{ number_format($transaction->amount, 2) }}</td>
                <td class="action-cell">
                  <div class="action-dropdown">
                    <button type="button" class="action-toggle" title="More Options" aria-label="More Options">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="action-menu">
                      <button type="button" class="action-item" data-action="edit">View/Edit</button>
                      <button type="button" class="action-item" data-action="delete">Delete</button>
                      <button type="button" class="action-item" data-action="history">View History</button>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">No payment transactions found yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

<div class="bank-col-filter-dd" id="bcf-type" data-col="type" data-filter-type="checkbox">
  <div class="bcfd-title">Select Type</div>
  <div class="bank-filter-options"></div>
  <div class="bcfd-actions">
    <button type="button" class="bcfd-clear" data-clear-filter="bcf-type">Clear</button>
    <button type="button" class="bcfd-apply" data-apply-filter>Apply</button>
  </div>
</div>

<div class="bank-col-filter-dd" id="bcf-payment" data-col="payment" data-filter-type="checkbox">
  <div class="bcfd-title">Select Payment Type</div>
  <div class="bank-filter-options"></div>
  <div class="bcfd-actions">
    <button type="button" class="bcfd-clear" data-clear-filter="bcf-payment">Clear</button>
    <button type="button" class="bcfd-apply" data-apply-filter>Apply</button>
  </div>
</div>

@foreach(['invoice' => 'Invoice No', 'party' => 'Party', 'bank' => 'Bank Name'] as $filterCol => $filterLabel)
<div class="bank-col-filter-dd" id="bcf-{{ $filterCol }}" data-col="{{ $filterCol }}" data-filter-type="text">
  <div class="bcfd-title">{{ $filterLabel }}</div>
  <select class="bcfd-select" data-filter-op>
    <option value="contains">Contains</option>
    <option value="exact">Exact match</option>
  </select>
  <input type="text" class="bcfd-input" data-filter-value placeholder="{{ strtoupper($filterLabel) }}">
  <div class="bcfd-actions">
    <button type="button" class="bcfd-clear" data-clear-filter="bcf-{{ $filterCol }}">Clear</button>
    <button type="button" class="bcfd-apply" data-apply-filter>Apply</button>
  </div>
</div>
@endforeach

<div class="bank-col-filter-dd" id="bcf-date" data-col="date" data-filter-type="date">
  <div class="bcfd-title">Date</div>
  <select class="bcfd-select" data-filter-op>
    <option value="equal">Equal To</option>
    <option value="before">Before</option>
    <option value="after">After</option>
  </select>
  <input type="date" class="bcfd-input" data-filter-value>
  <div class="bcfd-actions">
    <button type="button" class="bcfd-clear" data-clear-filter="bcf-date">Clear</button>
    <button type="button" class="bcfd-apply" data-apply-filter>Apply</button>
  </div>
</div>

<div class="bank-col-filter-dd" id="bcf-amount" data-col="amount" data-filter-type="number">
  <div class="bcfd-title">Amount</div>
  <select class="bcfd-select" data-filter-op>
    <option value="equal">Equal to</option>
    <option value="lt">Less Than</option>
    <option value="gt">Greater Than</option>
  </select>
  <input type="number" class="bcfd-input" data-filter-value placeholder="AMOUNT" step="0.01">
  <div class="bcfd-actions">
    <button type="button" class="bcfd-clear" data-clear-filter="bcf-amount">Clear</button>
    <button type="button" class="bcfd-apply" data-apply-filter>Apply</button>
  </div>
</div>

@endsection

@section('modals')
<!-- MODAL: ADD BANK ACCOUNT -->
<div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('bank-accounts.store') }}" id="bankForm">
        @csrf
        <input type="hidden" name="_method" value="POST" id="bankFormMethod">
        <input type="hidden" name="bank_id" id="bankIdField">
        <div class="modal-header">
          <h5 class="modal-title" id="addBankModalLabel"><i class="fa-solid fa-university me-2"></i>Add Bank Account</h5>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" title="Settings"><i class="fa-solid fa-gear"></i></button>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-600">Account Display Name <span class="text-danger">*</span></label>
              <input type="text" name="display_name" class="form-control" placeholder="Enter Account Display Name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Opening Balance</label>
              <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.01" name="opening_balance" class="form-control" placeholder="Enter Opening Balance">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">As of Date</label>
              <input type="date" name="as_of_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Account Number <span class="text-danger">*</span></label>
              <input type="text" name="account_number" class="form-control" placeholder="Enter Account Number" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">SWIFT Code</label>
              <input type="text" name="swift_code" class="form-control" placeholder="Enter SWIFT">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">IBAN</label>
              <input type="text" name="iban" class="form-control" placeholder="Enter IBAN">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Bank Name</label>
              <input type="text" name="bank_name" class="form-control" placeholder="Enter Bank Name">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Account Holder Name</label>
              <input type="text" name="account_holder_name" class="form-control" placeholder="Enter Account Holder Name">
            </div>
          </div>

          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="printOnInvoice" name="print_on_invoice" value="1">
            <label class="form-check-label" for="printOnInvoice">Print Bank Details on Invoices</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger me-auto d-none" id="bankDeleteBtn">Delete</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="bankFormSubmit">Save Details</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="bulk-overlay" id="bankBulkOverlay">
  <div class="bulk-modal" role="dialog" aria-modal="true" aria-labelledby="bankBulkModalTitle">
    <div class="bulk-modal-header">
      <h3 class="bulk-modal-title" id="bankBulkModalTitle">Bulk Inactive</h3>
      <div class="bulk-modal-info" id="bankBulkModalInfo">Select bank accounts to update.</div>
    </div>
    <div class="bulk-modal-body">
      <input type="text" id="bankBulkSearch" class="bulk-search" placeholder="Search bank account">
      <table class="bulk-table">
        <thead>
          <tr>
            <th style="width:44px;">
              <input type="checkbox" id="bankBulkCheckAll">
            </th>
            <th>Bank Account</th>
            <th>Account Number</th>
            <th style="width:140px;">Status</th>
          </tr>
        </thead>
        <tbody id="bankBulkTbody">
          <tr>
            <td colspan="4" class="bulk-empty">No bank accounts to show</td>
          </tr>
        </tbody>
      </table>
      <div class="bulk-password-box" id="bankBulkPasswordBox">
        <label for="bankBulkPasswordInput" class="bulk-password-label">Password required to activate bank accounts</label>
        <input type="password" id="bankBulkPasswordInput" class="bulk-password-input" placeholder="Enter password">
        <div class="bulk-password-error" id="bankBulkPasswordError">Incorrect password.</div>
      </div>
    </div>
    <div class="bulk-modal-footer">
      <div class="bulk-footer-note" id="bankBulkFooterNote">Choose one or more bank accounts.</div>
      <div class="bulk-footer-actions">
        <button type="button" class="btn btn-outline-secondary" id="bankBulkCancelBtn">Cancel</button>
        <button type="button" class="btn btn-primary" id="bankBulkApplyBtn">Apply</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/bank.js') }}"></script>
<script>
  document.querySelectorAll('.dropdown-container').forEach(container => {
    const input = container.querySelector('.dropdown-input');
    const optionsContainer = container.querySelector('.dropdown-options');
    const options = container.querySelectorAll('.dropdown-option');

    input.addEventListener('click', (e) => {
      e.stopPropagation();
      optionsContainer.style.display =
        optionsContainer.style.display === 'block' ? 'none' : 'block';
    });

    options.forEach(option => {
      option.addEventListener('click', () => {
        options.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        input.value = option.textContent;
        optionsContainer.style.display = 'none';
      });
    });
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-options').forEach(drop => {
      drop.style.display = 'none';
    });
  });
</script>
@endpush
