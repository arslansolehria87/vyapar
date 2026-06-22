(function () {
  'use strict';

  if (window.__vyaparTransactionColumnDragLoaded) return;
  window.__vyaparTransactionColumnDragLoaded = true;

  var activeTable = null;
  var draggedColumnKey = '';

  function getHeaders(table) {
    return Array.from(table.querySelectorAll('thead tr:first-child > th[data-column-key]'));
  }

  function clearDragState(table, clearSource) {
    getHeaders(table).forEach(function (header) {
      header.classList.remove('transaction-column-drop-before', 'transaction-column-drop-after');
      if (clearSource) {
        header.classList.remove('transaction-column-dragging');
        header.setAttribute('aria-grabbed', 'false');
      }
    });
  }

  function moveColumn(table, sourceIndex, targetIndex, placeAfter) {
    if (sourceIndex < 0 || targetIndex < 0 || sourceIndex === targetIndex) return;

    var columnCount = getHeaders(table).length;
    table.querySelectorAll('thead tr:first-child, tbody > tr, tfoot > tr').forEach(function (row) {
      if (row.children.length !== columnCount) return;

      var sourceCell = row.children[sourceIndex];
      var targetCell = row.children[targetIndex];
      if (!sourceCell || !targetCell || sourceCell === targetCell) return;

      if (placeAfter) {
        targetCell.after(sourceCell);
      } else {
        targetCell.before(sourceCell);
      }
    });

    var colgroup = table.querySelector('colgroup');
    if (colgroup && colgroup.children.length === columnCount) {
      var sourceCol = colgroup.children[sourceIndex];
      var targetCol = colgroup.children[targetIndex];
      if (sourceCol && targetCol && sourceCol !== targetCol) {
        if (placeAfter) {
          targetCol.after(sourceCol);
        } else {
          targetCol.before(sourceCol);
        }
      }
    }
  }

  function alignKeyedRow(table, row) {
    var headers = getHeaders(table);
    if (row.children.length !== headers.length) return;

    var cellsByKey = {};
    Array.from(row.children).forEach(function (cell) {
      var key = cell.dataset.columnKey;
      if (key) cellsByKey[key] = cell;
    });

    if (Object.keys(cellsByKey).length === 0) {
      var defaultKeys = table._transactionColumnDefaultKeys || [];
      if (defaultKeys.length !== row.children.length) return;

      Array.from(row.children).forEach(function (cell, index) {
        cell.dataset.columnKey = defaultKeys[index];
        cellsByKey[defaultKeys[index]] = cell;
      });
    }

    if (Object.keys(cellsByKey).length !== headers.length) return;
    headers.forEach(function (header) {
      var cell = cellsByKey[header.dataset.columnKey];
      if (cell) row.appendChild(cell);
    });
  }

  function alignKeyedRows(table) {
    table.querySelectorAll('tbody > tr, tfoot > tr').forEach(function (row) {
      alignKeyedRow(table, row);
    });
  }

  function getStorageKey(table) {
    return table.dataset.columnDragStorage || ('vyapar.transaction-columns.' + (table.id || 'table'));
  }

  function saveOrder(table) {
    try {
      localStorage.setItem(getStorageKey(table), JSON.stringify(
        getHeaders(table).map(function (header) {
          return header.dataset.columnKey;
        })
      ));
    } catch (error) {
      // Reordering remains available when storage is blocked.
    }
  }

  function restoreOrder(table) {
    var savedOrder;
    try {
      savedOrder = JSON.parse(localStorage.getItem(getStorageKey(table)) || '[]');
    } catch (error) {
      return;
    }

    var currentKeys = getHeaders(table).map(function (header) {
      return header.dataset.columnKey;
    });
    var isValid = Array.isArray(savedOrder)
      && savedOrder.length === currentKeys.length
      && savedOrder.every(function (key) { return currentKeys.includes(key); });

    if (!isValid) return;

    savedOrder.forEach(function (key, desiredIndex) {
      var headers = getHeaders(table);
      var currentIndex = headers.findIndex(function (header) {
        return header.dataset.columnKey === key;
      });
      if (currentIndex >= 0 && currentIndex !== desiredIndex) {
        moveColumn(table, currentIndex, desiredIndex, false);
      }
    });
  }

  function isInteractiveTarget(target) {
    return Boolean(target.closest(
      'button, input, select, textarea, a, label, .dropdown-menu, .column-filter-dropdown, .filter-popover, .th-sort, .th-filter, .loan-filter-icon, .bank-filter-icon, .th-filter-icon, .col-rh, .col-resize-handle, .col-resize-handle-sales, .loan-col-resize-handle, .bank-col-resize-handle, .report-col-resizer, .resizer'
    ));
  }

  function initializeTable(table) {
    table._transactionColumnDefaultKeys = getHeaders(table).map(function (header) {
      return header.dataset.columnKey;
    });

    restoreOrder(table);
    alignKeyedRows(table);

    getHeaders(table).forEach(function (header) {
      header.draggable = true;
      header.setAttribute('aria-grabbed', 'false');
      header.title = header.title || 'Drag left or right to reorder this column';

      header.addEventListener('dragstart', function (event) {
        if (isInteractiveTarget(event.target)) {
          event.preventDefault();
          return;
        }

        activeTable = table;
        draggedColumnKey = header.dataset.columnKey || '';
        header.classList.add('transaction-column-dragging');
        header.setAttribute('aria-grabbed', 'true');

        if (event.dataTransfer) {
          event.dataTransfer.effectAllowed = 'move';
          event.dataTransfer.setData('text/plain', draggedColumnKey);
        }
      });

      header.addEventListener('dragover', function (event) {
        if (activeTable !== table || !draggedColumnKey || draggedColumnKey === header.dataset.columnKey) return;

        event.preventDefault();
        clearDragState(table, false);

        var rect = header.getBoundingClientRect();
        var placeAfter = event.clientX > rect.left + (rect.width / 2);
        header.classList.add(placeAfter ? 'transaction-column-drop-after' : 'transaction-column-drop-before');

        if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
      });

      header.addEventListener('drop', function (event) {
        if (activeTable !== table) return;
        event.preventDefault();

        var sourceKey = draggedColumnKey
          || (event.dataTransfer ? event.dataTransfer.getData('text/plain') : '');
        var headers = getHeaders(table);
        var sourceIndex = headers.findIndex(function (item) {
          return item.dataset.columnKey === sourceKey;
        });
        var targetIndex = headers.findIndex(function (item) {
          return item.dataset.columnKey === header.dataset.columnKey;
        });
        var rect = header.getBoundingClientRect();
        var placeAfter = event.clientX > rect.left + (rect.width / 2);

        moveColumn(table, sourceIndex, targetIndex, placeAfter);
        saveOrder(table);
        clearDragState(table, true);
        activeTable = null;
        draggedColumnKey = '';

        table.dispatchEvent(new CustomEvent('transaction-columns-reordered', {
          detail: {
            order: getHeaders(table).map(function (item) { return item.dataset.columnKey; })
          }
        }));
      });

      header.addEventListener('dragend', function () {
        clearDragState(table, true);
        activeTable = null;
        draggedColumnKey = '';
      });
    });

    var observedSections = Array.from(table.tBodies);
    if (table.tFoot) observedSections.push(table.tFoot);
    observedSections.forEach(function (section) {
      new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          mutation.addedNodes.forEach(function (node) {
            if (node.nodeType === 1 && node.matches('tr')) {
              alignKeyedRow(table, node);
            }
          });
        });
      }).observe(section, { childList: true });
    });
  }

  function injectStyles() {
    if (document.getElementById('transaction-column-drag-styles')) return;

    var style = document.createElement('style');
    style.id = 'transaction-column-drag-styles';
    style.textContent = [
      '[data-column-drag="native"] thead th[draggable="true"]{cursor:grab;user-select:none;}',
      '[data-column-drag="native"] thead th.transaction-column-dragging{opacity:.45;cursor:grabbing;}',
      '[data-column-drag="native"] thead th.transaction-column-drop-before{box-shadow:inset 3px 0 0 #2563eb;}',
      '[data-column-drag="native"] thead th.transaction-column-drop-after{box-shadow:inset -3px 0 0 #2563eb;}'
    ].join('');
    document.head.appendChild(style);
  }

  function initialize() {
    injectStyles();
    document.querySelectorAll('table[data-column-drag="native"]').forEach(initializeTable);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }
})();
