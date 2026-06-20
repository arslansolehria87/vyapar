/**
 * VYAPAR — Bank Accounts Page Logic
 */

document.addEventListener("DOMContentLoaded", () => {
    const list = document.getElementById("bankList");
    const sidebarSearch = document.getElementById("bankSearchInput");
    const tableSearch = document.getElementById("tableSearchInput");
    const bulkSearch = document.getElementById("bankBulkSearch");

    function hardResetSearchInput(input) {
        if (!input) return;
        input.setAttribute("autocomplete", "new-password");
        input.setAttribute("autocapitalize", "off");
        input.setAttribute("spellcheck", "false");
        input.setAttribute("data-form-type", "other");
        input.setAttribute("readonly", "readonly");
        input.setAttribute("inputmode", "search");
        if (!input.dataset.userTyped) {
            input.value = "";
        }
        input.addEventListener("focus", () => {
            input.removeAttribute("readonly");
        });
        input.addEventListener("blur", () => {
            if (!input.value) {
                input.setAttribute("readonly", "readonly");
            }
        });
        input.addEventListener("input", () => {
            input.dataset.userTyped = "1";
        });
    }

    hardResetSearchInput(sidebarSearch);
    hardResetSearchInput(tableSearch);
    hardResetSearchInput(bulkSearch);

    const clearAutoFilledValues = () => {
        [sidebarSearch, tableSearch, bulkSearch].forEach((input) => {
            if (!input || input.dataset.userTyped === "1") return;
            if (input.value) input.value = "";
        });
    };

    clearAutoFilledValues();
    requestAnimationFrame(() => requestAnimationFrame(clearAutoFilledValues));
    window.addEventListener("pageshow", clearAutoFilledValues);

    const detailName = document.getElementById("bankDetailName");
    const detailNameText =
        document.getElementById("bankDetailNameText") || detailName;
    const detailAccountNumber = document.getElementById(
        "bankDetailAccountNumber",
    );
    const detailBankName = document.getElementById("bankDetailBankName");
    const detailOpeningBalance = document.getElementById(
        "bankDetailOpeningBalance",
    );

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ||
        window.App?.csrfToken ||
        "";
    const bulkMenuButton = document.getElementById("bankBulkMenuBtn");
    const bulkMenu = document.getElementById("bankBulkMenu");
    const bulkOverlay = document.getElementById("bankBulkOverlay");
    const bulkModalTitle = document.getElementById("bankBulkModalTitle");
    const bulkModalInfo = document.getElementById("bankBulkModalInfo");
    const bulkTbody = document.getElementById("bankBulkTbody");
    const bulkCheckAll = document.getElementById("bankBulkCheckAll");
    const bulkApplyBtn = document.getElementById("bankBulkApplyBtn");
    const bulkCancelBtn = document.getElementById("bankBulkCancelBtn");
    const bulkFooterNote = document.getElementById("bankBulkFooterNote");
    const bulkPasswordBox = document.getElementById("bankBulkPasswordBox");
    const bulkPasswordInput = document.getElementById("bankBulkPasswordInput");
    const bulkPasswordError = document.getElementById("bankBulkPasswordError");

    // Table element used for filtering & actions
    const bankTable = document.getElementById("bankTable");

    // Keep track of whether a date filter is currently active (clicking the date column)
    let activeFilterDate = null;
    let bulkModalType = null;

    function isBankInactive(bankId) {
        const item = list?.querySelector(`li[data-bank="${bankId}"]`);
        return item ? item.dataset.isActive === "0" : false;
    }

    function setBankInactive(bankId, inactive) {
        const item = list?.querySelector(`li[data-bank="${bankId}"]`);
        if (!item) return;
        item.dataset.isActive = inactive ? "0" : "1";
    }

    function ensureStatusPill(item) {
        if (!item) return null;
        let pill = item.querySelector(".bank-status-pill");
        if (!pill) {
            pill = document.createElement("span");
            pill.className = "bank-status-pill";
            item.querySelector(".entity-name")?.insertAdjacentElement(
                "afterend",
                pill,
            );
        }
        return pill;
    }

    function refreshBankStatusUI() {
        if (!list) return;

        list.querySelectorAll("li[data-bank]").forEach((item) => {
            const inactive = isBankInactive(item.dataset.bank);
            item.classList.toggle("bank-inactive", inactive);
            const pill = ensureStatusPill(item);
            if (!pill) return;
            pill.textContent = inactive ? "Inactive" : "Active";
            pill.classList.toggle("inactive", inactive);
            pill.classList.toggle("active", !inactive);
        });

        applySearchFilter();

        const activeVisibleItem = list.querySelector(
            'li.active[data-bank]:not([style*="display: none"])',
        );
        if (!activeVisibleItem) {
            const firstVisibleItem = Array.from(
                list.querySelectorAll("li[data-bank]"),
            ).find((item) => item.style.display !== "none");
            if (firstVisibleItem) {
                selectBankItem(firstVisibleItem);
            }
        }
    }

    function getBulkModalRows() {
        if (!list) return [];

        return Array.from(list.querySelectorAll("li[data-bank]"))
            .map((item) => ({
                id: item.dataset.bank || "",
                name:
                    item
                        .querySelector(".entity-name")
                        ?.childNodes[0]?.textContent?.trim() ||
                    item.querySelector(".entity-name")?.textContent?.trim() ||
                    "Bank Account",
                accountNumber: item.dataset.accountNumber || "-",
                inactive: isBankInactive(item.dataset.bank),
            }))
            .filter((row) => {
                if (bulkModalType === "bulk-inactive") return !row.inactive;
                if (bulkModalType === "bulk-active") return row.inactive;
                return true;
            });
    }

    function renderBulkRows() {
        if (!bulkTbody) return;

        const query = (bulkSearch?.value || "").trim().toLowerCase();
        const rows = getBulkModalRows().filter((row) => {
            return [row.name, row.accountNumber].some((value) =>
                String(value).toLowerCase().includes(query),
            );
        });

        if (!rows.length) {
            bulkTbody.innerHTML =
                '<tr><td colspan="4" class="bulk-empty">No bank accounts to show</td></tr>';
            if (bulkCheckAll) bulkCheckAll.checked = false;
            return;
        }

        bulkTbody.innerHTML = rows
            .map(
                (row) => `
      <tr>
        <td>
          <input type="checkbox" class="bank-bulk-check" value="${row.id}" style="width:15px;height:15px;accent-color:#2563eb;">
        </td>
        <td>${escapeHtml(row.name)}</td>
        <td>${escapeHtml(row.accountNumber)}</td>
        <td><span class="bank-status-pill ${row.inactive ? "inactive" : "active"}">${row.inactive ? "Inactive" : "Active"}</span></td>
      </tr>
    `,
            )
            .join("");
    }

    function openBulkModal(type) {
        bulkModalType = type;
        if (bulkModalTitle) {
            bulkModalTitle.textContent =
                type === "bulk-active" ? "Bulk Active" : "Bulk Inactive";
        }
        if (bulkModalInfo) {
            bulkModalInfo.textContent =
                type === "bulk-active"
                    ? "Showing inactive bank accounts only."
                    : "Showing active bank accounts only.";
        }
        if (bulkFooterNote) {
            bulkFooterNote.textContent =
                type === "bulk-active"
                    ? "Selected bank accounts will become active."
                    : "Selected bank accounts will become inactive.";
        }
        if (bulkApplyBtn) {
            bulkApplyBtn.textContent =
                type === "bulk-active" ? "Mark Active" : "Mark Inactive";
        }
        if (bulkPasswordBox) {
            bulkPasswordBox.classList.toggle("open", type === "bulk-active");
        }
        if (bulkPasswordInput) {
            bulkPasswordInput.value = "";
        }
        if (bulkPasswordError) {
            bulkPasswordError.classList.remove("show");
        }
        if (bulkSearch) bulkSearch.value = "";
        if (bulkCheckAll) bulkCheckAll.checked = false;
        renderBulkRows();
        bulkOverlay?.classList.add("open");
    }

    function closeBulkModal() {
        bulkOverlay?.classList.remove("open");
        bulkModalType = null;
        if (bulkPasswordInput) {
            bulkPasswordInput.value = "";
        }
        if (bulkPasswordError) {
            bulkPasswordError.classList.remove("show");
        }
    }

    function closeBulkMenu() {
        bulkMenu?.classList.remove("open");
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function selectBankItem(item) {
        if (!item) return;

        list.querySelectorAll("li").forEach((li) =>
            li.classList.remove("active"),
        );
        item.classList.add("active");

        const bankId = item.dataset.bank;
        const name =
            item.querySelector(".entity-name")?.textContent?.trim() ?? "";
        const accountNumber = item.dataset.accountNumber || "-";
        const bankName = item.dataset.bankName || "-";
        const openingBalance = item.dataset.openingBalance
            ? Number(item.dataset.openingBalance)
            : 0;

        detailNameText.textContent = name || "Select a bank account";
        detailAccountNumber.textContent = accountNumber || "-";
        detailBankName.textContent = bankName || "-";
        detailOpeningBalance.textContent = `₹ ${openingBalance.toFixed(2)}`;

        // Reset any date-based table filter when selecting a different bank
        activeFilterDate = null;

        // Filter the table to only show the selected bank account
        filterTableByBankId(bankId);
    }

    function getSelectedBankId() {
        return (
            document.querySelector("li.active[data-bank]")?.dataset.bank || ""
        );
    }

    function filterTableByBankId(bankId) {
        applyBankTableFilters();
    }

    const bankTableFilterState = {};
    let bankTableSortCol = null;
    let bankTableSortAsc = true;

    function getBankTableDataRows() {
        if (!bankTable?.tBodies?.[0]) return [];
        return Array.from(bankTable.tBodies[0].rows).filter(
            (row) => row.dataset.bankId,
        );
    }

    function getBankCellText(row, col) {
        const index = Array.from(
            bankTable?.tHead?.rows?.[0]?.cells || [],
        ).findIndex((header) => header.dataset.columnKey === col);
        return index < 0
            ? ""
            : row.cells[index]?.textContent.trim() || "";
    }

    function parseMoney(value) {
        return Number(String(value || "").replace(/[^0-9.-]/g, "")) || 0;
    }

    function normalizeTableDate(value) {
        const text = String(value || "").trim();
        const match = text.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/);
        if (match) {
            const [, d, m, y] = match;
            const year = y.length === 2 ? `20${y}` : y;
            return `${year}-${m.padStart(2, "0")}-${d.padStart(2, "0")}`;
        }
        return normalizeDate(text);
    }

    function setBankFilterIconState() {
        document.querySelectorAll(".bank-filter-icon").forEach((icon) => {
            const dd = document.getElementById(icon.dataset.filterTarget || "");
            const col = dd?.dataset.col;
            const state = col ? bankTableFilterState[col] : null;
            const active = !!(
                state &&
                ((Array.isArray(state.values) && state.values.length) ||
                    state.value)
            );
            icon.classList.toggle("active", active);
        });
    }

    function toggleBankNoResults(show) {
        const tbody = bankTable?.tBodies?.[0];
        if (!tbody) return;
        tbody.querySelector(".bank-no-results")?.remove();
        if (!show) return;
        const row = document.createElement("tr");
        row.className = "bank-no-results";
        row.innerHTML =
            '<td colspan="8">No transactions found for selected filters.</td>';
        tbody.appendChild(row);
    }

    function rowMatchesBankTableFilters(row) {
        const selectedBankId = getSelectedBankId();
        const q = (tableSearch?.value || "").trim().toLowerCase();
        const rowText = Array.from(row.cells)
            .filter((cell) => cell.dataset.columnKey !== "actions")
            .map((cell) => cell.textContent.trim().toLowerCase())
            .join(" ");

        if (selectedBankId && row.dataset.bankId !== selectedBankId) {
            return false;
        }
        if (q && !rowText.includes(q)) {
            return false;
        }
        if (
            activeFilterDate &&
            getBankCellText(row, "date") !== activeFilterDate
        ) {
            return false;
        }

        return Object.entries(bankTableFilterState).every(([col, state]) => {
            const text = getBankCellText(row, col);
            const normalizedText = text.toLowerCase();

            if (state.type === "checkbox") {
                return !state.values?.length || state.values.includes(text);
            }

            if (state.type === "text") {
                const value = String(state.value || "").trim().toLowerCase();
                if (!value) return true;
                return state.op === "exact"
                    ? normalizedText === value
                    : normalizedText.includes(value);
            }

            if (state.type === "date") {
                if (!state.value) return true;
                const rowDate = normalizeTableDate(text);
                if (state.op === "before") return rowDate < state.value;
                if (state.op === "after") return rowDate > state.value;
                return rowDate === state.value;
            }

            if (state.type === "number") {
                if (state.value === "" || state.value === undefined) return true;
                const filterNumber = Number(state.value);
                if (Number.isNaN(filterNumber)) return true;
                const rowNumber = parseMoney(text);
                if (state.op === "lt") return rowNumber < filterNumber;
                if (state.op === "gt") return rowNumber > filterNumber;
                return rowNumber === filterNumber;
            }

            return true;
        });
    }

    function applyBankTableFilters() {
        if (!bankTable) return;
        toggleBankNoResults(false);
        const rows = getBankTableDataRows();
        let visibleCount = 0;

        rows.forEach((row) => {
            const show = rowMatchesBankTableFilters(row);
            row.style.display = show ? "" : "none";
            row.classList.toggle(
                "active-row",
                !!getSelectedBankId() && row.dataset.bankId === getSelectedBankId(),
            );
            if (show) visibleCount += 1;
        });

        toggleBankNoResults(rows.length > 0 && visibleCount === 0);
        setBankFilterIconState();
    }

    function readBankDropdownFilter(dropdown) {
        const col = dropdown.dataset.col;
        const type = dropdown.dataset.filterType;
        if (!col || !type) return;

        if (type === "checkbox") {
            bankTableFilterState[col] = {
                type,
                values: Array.from(
                    dropdown.querySelectorAll("input[type=checkbox]:checked"),
                ).map((input) => input.value),
            };
            return;
        }

        bankTableFilterState[col] = {
            type,
            op: dropdown.querySelector("[data-filter-op]")?.value || "contains",
            value: dropdown.querySelector("[data-filter-value]")?.value || "",
        };
    }

    function closeBankColumnFilters() {
        document
            .querySelectorAll(".bank-col-filter-dd.open")
            .forEach((dd) => dd.classList.remove("open"));
    }

    function clearBankColumnFilter(id) {
        const dropdown = document.getElementById(id);
        if (!dropdown) return;
        dropdown
            .querySelectorAll("input[type=checkbox]")
            .forEach((input) => (input.checked = false));
        dropdown
            .querySelectorAll("[data-filter-value]")
            .forEach((input) => (input.value = ""));
        dropdown
            .querySelectorAll("[data-filter-op]")
            .forEach((select) => (select.selectedIndex = 0));
        delete bankTableFilterState[dropdown.dataset.col];
        applyBankTableFilters();
    }

    function populateBankCheckboxFilters() {
        document
            .querySelectorAll('.bank-col-filter-dd[data-filter-type="checkbox"]')
            .forEach((dropdown) => {
                const col = dropdown.dataset.col;
                const optionsWrap = dropdown.querySelector(".bank-filter-options");
                if (!col || !optionsWrap) return;
                const values = [
                    ...new Set(
                        getBankTableDataRows()
                            .map((row) => getBankCellText(row, col))
                            .filter(Boolean),
                    ),
                ].sort((a, b) => a.localeCompare(b));

                optionsWrap.innerHTML = values.length
                    ? values
                          .map(
                              (value) => `
                    <label class="bcfd-cb-row">
                      <input type="checkbox" value="${escapeHtml(value)}">
                      ${escapeHtml(value)}
                    </label>
                  `,
                          )
                          .join("")
                    : '<div class="bulk-empty">No options</div>';
            });
    }

    function sortBankTable(col) {
        if (!bankTable?.tBodies?.[0]) return;
        bankTableSortAsc =
            bankTableSortCol === col ? !bankTableSortAsc : true;
        bankTableSortCol = col;

        document
            .querySelectorAll("#bankTable th")
            .forEach((th) => th.classList.remove("sort-asc", "sort-desc"));
        bankTable
            .querySelector(`th[data-col="${col}"]`)
            ?.classList.add(bankTableSortAsc ? "sort-asc" : "sort-desc");

        const rows = getBankTableDataRows();
        rows.sort((a, b) => {
            if (col === "amount") {
                return bankTableSortAsc
                    ? parseMoney(getBankCellText(a, col)) -
                          parseMoney(getBankCellText(b, col))
                    : parseMoney(getBankCellText(b, col)) -
                          parseMoney(getBankCellText(a, col));
            }
            const av =
                col === "date"
                    ? normalizeTableDate(getBankCellText(a, col))
                    : getBankCellText(a, col).toLowerCase();
            const bv =
                col === "date"
                    ? normalizeTableDate(getBankCellText(b, col))
                    : getBankCellText(b, col).toLowerCase();
            return bankTableSortAsc
                ? String(av).localeCompare(String(bv))
                : String(bv).localeCompare(String(av));
        });

        rows.forEach((row) => bankTable.tBodies[0].appendChild(row));
        applyBankTableFilters();
    }

    function initBankColumnResize() {
        let isResizing = false;
        let startX = 0;
        let startW = 0;
        let th = null;
        let handle = null;

        document.addEventListener("mousedown", (event) => {
            if (!event.target.classList.contains("bank-col-resize-handle")) {
                return;
            }
            event.preventDefault();
            handle = event.target;
            th = handle.closest("th");
            if (!th) return;
            isResizing = true;
            startX = event.clientX;
            startW = th.offsetWidth;
            handle.classList.add("resizing");
            document.body.style.cursor = "col-resize";
            document.body.style.userSelect = "none";
        });

        document.addEventListener("mousemove", (event) => {
            if (!isResizing || !th) return;
            const newW = Math.max(60, startW + (event.clientX - startX));
            th.style.width = `${newW}px`;
            th.style.minWidth = `${newW}px`;
        });

        document.addEventListener("mouseup", () => {
            if (!isResizing) return;
            isResizing = false;
            handle?.classList.remove("resizing");
            document.body.style.cursor = "";
            document.body.style.userSelect = "";
            th = null;
            handle = null;
        });
    }

    function initBankTableFilters() {
        populateBankCheckboxFilters();
        initBankColumnResize();

        document.querySelectorAll(".bank-th-inner").forEach((inner) => {
            inner.addEventListener("click", () => {
                const col = inner.dataset.sortCol;
                if (col) sortBankTable(col);
            });
        });

        document.querySelectorAll(".bank-filter-icon").forEach((icon) => {
            icon.addEventListener("click", (event) => {
                event.stopPropagation();
                const dropdown = document.getElementById(
                    icon.dataset.filterTarget || "",
                );
                if (!dropdown) return;
                const isOpen = dropdown.classList.contains("open");
                closeBankColumnFilters();
                if (isOpen) return;

                const rect = icon.getBoundingClientRect();
                dropdown.style.top = `${rect.bottom + 6}px`;
                dropdown.style.left = `${rect.left}px`;
                dropdown.classList.add("open");

                const ddRect = dropdown.getBoundingClientRect();
                if (ddRect.right > window.innerWidth - 8) {
                    dropdown.style.left = `${window.innerWidth - ddRect.width - 8}px`;
                }
            });
        });

        document.querySelectorAll(".bank-col-filter-dd").forEach((dropdown) => {
            dropdown.addEventListener("click", (event) => event.stopPropagation());
            dropdown
                .querySelectorAll("input[type=checkbox], [data-filter-value], [data-filter-op]")
                .forEach((field) => {
                    field.addEventListener("input", () => {
                        readBankDropdownFilter(dropdown);
                        applyBankTableFilters();
                    });
                    field.addEventListener("change", () => {
                        readBankDropdownFilter(dropdown);
                        applyBankTableFilters();
                    });
                });
        });

        document.querySelectorAll("[data-clear-filter]").forEach((button) => {
            button.addEventListener("click", () =>
                clearBankColumnFilter(button.dataset.clearFilter),
            );
        });

        document.querySelectorAll("[data-apply-filter]").forEach((button) => {
            button.addEventListener("click", () => {
                const dropdown = button.closest(".bank-col-filter-dd");
                if (dropdown) readBankDropdownFilter(dropdown);
                applyBankTableFilters();
                closeBankColumnFilters();
            });
        });

        document.addEventListener("click", closeBankColumnFilters);
        applyBankTableFilters();
    }

    const addBankButton = document.querySelector(".btn-add-entity");
    const bankForm = document.getElementById("bankForm");
    const bankFormMethod = document.getElementById("bankFormMethod");
    const bankIdField = document.getElementById("bankIdField");
    const modalTitle = document.getElementById("addBankModalLabel");
    const bankDeleteBtn = document.getElementById("bankDeleteBtn");

    function showBankModal() {
        const modalEl = document.getElementById("addBankModal");
        if (modalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function hideBankModal() {
        const modalEl = document.getElementById("addBankModal");
        if (modalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.hide();
        }
    }

    function deleteBankAccount(bankId) {
        if (!bankId) return;

        fetch(`/dashboard/bank-accounts/${bankId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const message =
                        data?.message || "Could not delete bank account.";
                    throw new Error(message);
                }
                return data;
            })
            .then(() => {
                const listItem = document.querySelector(
                    `li[data-bank="${bankId}"]`,
                );
                if (listItem) listItem.remove();

                document
                    .querySelectorAll(`tr[data-bank-id="${bankId}"]`)
                    .forEach((row) => row.remove());

                hideBankModal();
                showToast("Bank account deleted successfully.");

                const remaining = document.querySelector("li[data-bank]");
                if (remaining) {
                    selectBankItem(remaining);
                } else {
                    detailNameText.textContent = "Select a bank account";
                    detailAccountNumber.textContent = "-";
                    detailBankName.textContent = "-";
                    detailOpeningBalance.textContent = "₹ 0.00";
                }
            })
            .catch((error) => {
                showToast(
                    error?.message || "Could not delete bank account.",
                    "danger",
                );
            });
    }

    if (list) {
        list.addEventListener("click", (event) => {
            const menuToggle = event.target.closest(
                "[data-bank-list-menu-toggle]",
            );
            const actionButton = event.target.closest(
                "[data-bank-list-action]",
            );
            const item = event.target.closest("li");
            if (!item || !item.dataset.bank) return;

            if (menuToggle) {
                event.stopPropagation();
                const menu = menuToggle
                    .closest(".bank-list-menu-wrap")
                    ?.querySelector(".bank-list-menu");
                list.querySelectorAll(".bank-list-menu.open").forEach((openMenu) => {
                    if (openMenu !== menu) openMenu.classList.remove("open");
                });
                menu?.classList.toggle("open");
                return;
            }

            if (actionButton) {
                event.stopPropagation();
                actionButton.closest(".bank-list-menu")?.classList.remove("open");
                selectBankItem(item);

                if (actionButton.dataset.bankListAction === "edit") {
                    openBankModal("edit", item.dataset.bank);
                    showBankModal();
                    return;
                }

                if (actionButton.dataset.bankListAction === "delete") {
                    if (
                        !confirm(
                            "Are you sure you want to delete this bank account?",
                        )
                    ) {
                        return;
                    }
                    deleteBankAccount(item.dataset.bank);
                    return;
                }
            }

            selectBankItem(item);
        });

        document.addEventListener("click", (event) => {
            if (!event.target.closest(".bank-list-menu-wrap")) {
                list.querySelectorAll(".bank-list-menu.open").forEach((menu) => {
                    menu.classList.remove("open");
                });
            }
        });

        // Auto-select first item on load
        const first =
            list.querySelector("li.active") || list.querySelector("li");
        if (first) {
            selectBankItem(first);
        }
    }

    refreshBankStatusUI();

    if (bulkMenuButton) {
        bulkMenuButton.addEventListener("click", (event) => {
            event.stopPropagation();
            bulkMenu?.classList.toggle("open");
        });
    }

    document.addEventListener("click", (event) => {
        if (!event.target.closest(".bulk-menu-wrap")) {
            closeBulkMenu();
        }
    });

    document.querySelectorAll("[data-bulk-action]").forEach((button) => {
        button.addEventListener("click", () => {
            closeBulkMenu();
            openBulkModal(button.dataset.bulkAction || "bulk-inactive");
        });
    });

    if (bulkCancelBtn) {
        bulkCancelBtn.addEventListener("click", closeBulkModal);
    }

    if (bulkOverlay) {
        bulkOverlay.addEventListener("click", (event) => {
            if (event.target === bulkOverlay) {
                closeBulkModal();
            }
        });
    }

    if (bulkSearch) {
        bulkSearch.addEventListener("input", renderBulkRows);
    }

    if (bulkCheckAll) {
        bulkCheckAll.addEventListener("change", () => {
            document
                .querySelectorAll(".bank-bulk-check")
                .forEach((checkbox) => {
                    checkbox.checked = bulkCheckAll.checked;
                });
        });
    }

    if (bulkApplyBtn) {
        bulkApplyBtn.addEventListener("click", () => {
            const selectedIds = Array.from(
                document.querySelectorAll(".bank-bulk-check:checked"),
            )
                .map((checkbox) => checkbox.value)
                .filter(Boolean);

            if (!selectedIds.length) {
                showToast(
                    "Please select at least one bank account.",
                    "warning",
                );
                return;
            }

            const makeInactive = bulkModalType === "bulk-inactive";

            if (!makeInactive) {
                const enteredPassword = bulkPasswordInput?.value || "";
                if (!enteredPassword) {
                    bulkPasswordError?.classList.add("show");
                    bulkPasswordInput?.focus();
                    showToast("Please enter bank account password.", "danger");
                    return;
                }
            }

            fetch("/dashboard/bank-accounts/bulk-status", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    bank_ids: selectedIds,
                    is_active: !makeInactive,
                    password: bulkPasswordInput?.value || "",
                }),
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        throw new Error(
                            data?.message || "Failed to update bank status.",
                        );
                    }
                    return data;
                })
                .then((data) => {
                    selectedIds.forEach((bankId) =>
                        setBankInactive(bankId, makeInactive),
                    );
                    refreshBankStatusUI();
                    renderBulkRows();
                    showToast(
                        data.message ||
                            (makeInactive
                                ? "Selected bank accounts marked inactive."
                                : "Selected bank accounts marked active."),
                    );
                    closeBulkModal();
                })
                .catch((error) => {
                    showToast(
                        error?.message || "Failed to update bank status.",
                        "danger",
                    );
                });
        });
    }

    if (addBankButton) {
        addBankButton.addEventListener("click", () => {
            openBankModal("add");
        });
    }

    function getBankOptions(selectedBankId = "") {
        if (!list) return "";
        return Array.from(list.querySelectorAll("li[data-bank]"))
            .map((item) => {
                const bankId = item.dataset.bank || "";
                const bankName =
                    item.querySelector(".entity-name")?.textContent?.trim() ||
                    "Bank";
                const selected =
                    String(bankId) === String(selectedBankId) ? "selected" : "";
                return `<option value="${bankId}" ${selected}>${bankName}</option>`;
            })
            .join("");
    }

    function injectTransferActions() {
        if (!addBankButton || document.getElementById("bankTransferActions"))
            return;

        const actionButtons = addBankButton.closest(".action-buttons");
        if (!actionButtons) return;

        const wrapper = document.createElement("div");
        wrapper.id = "bankTransferActions";
        wrapper.className = "dropdown";
        wrapper.innerHTML = `
      <button class="btn btn-outline-danger rounded-pill px-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Deposit / Withdraw <i class="fa-solid fa-chevron-down ms-2"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><button class="dropdown-item bank-transfer-option" type="button" data-mode="bank_to_cash">Bank to Cash Transfer</button></li>
        <li><button class="dropdown-item bank-transfer-option" type="button" data-mode="cash_to_bank">Cash to Bank Transfer</button></li>
        <li><button class="dropdown-item bank-transfer-option" type="button" data-mode="bank_to_bank">Bank to Bank Transfer</button></li>
        <li><button class="dropdown-item bank-transfer-option" type="button" data-mode="adjust_balance">Adjust Bank Balance</button></li>
      </ul>
    `;

        actionButtons.insertBefore(
            wrapper,
            actionButtons.querySelector(".btn-settings"),
        );
    }

    function injectTransferModal() {
        if (document.getElementById("bankTransferModal")) return;

        const modal = document.createElement("div");
        modal.className = "modal fade";
        modal.id = "bankTransferModal";
        modal.tabIndex = -1;
        modal.setAttribute("aria-hidden", "true");
        modal.innerHTML = `
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <form id="bankTransferForm">
            <div class="modal-header">
              <h5 class="modal-title" id="bankTransferModalTitle">Bank To Cash Transfer</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="bankTransferMode" value="bank_to_cash">
              <div class="row g-3" id="bankTransferDualFields">
                <div class="col-md-6">
                  <label class="form-label" id="transferFromLabel">From</label>
                  <select class="form-select" id="transferFromBank"></select>
                  <input type="text" class="form-control d-none" id="transferFromCash" value="Cash" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label" id="transferToLabel">To</label>
                  <select class="form-select" id="transferToBank"></select>
                  <input type="text" class="form-control d-none" id="transferToCash" value="Cash" readonly>
                </div>
              </div>
              <div class="row g-3 d-none" id="bankAdjustFields">
                <div class="col-md-6">
                  <label class="form-label">Account Name</label>
                  <select class="form-select" id="adjustAccountName"></select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Type</label>
                  <select class="form-select" id="adjustType">
                    <option value="increase">Increase balance</option>
                    <option value="decrease">Decrease balance</option>
                  </select>
                </div>
              </div>
              <div class="row g-3 mt-1">
                <div class="col-md-6">
                  <label class="form-label">Amount</label>
                  <input type="number" step="0.01" class="form-control" id="transferAmount" value="0">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Adjustment Date</label>
                  <input type="date" class="form-control" id="transferDate">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" id="transferDescription" rows="3" placeholder="Add description"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Image</label>
                  <input type="file" class="form-control" id="transferImage" accept="image/*">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger rounded-pill px-4">Save</button>
            </div>
          </form>
        </div>
      </div>
    `;

        document.body.appendChild(modal);
    }

    function setTransferModalMode(mode) {
        const modalTitleEl = document.getElementById("bankTransferModalTitle");
        const dualFields = document.getElementById("bankTransferDualFields");
        const adjustFields = document.getElementById("bankAdjustFields");
        const fromBank = document.getElementById("transferFromBank");
        const toBank = document.getElementById("transferToBank");
        const fromCash = document.getElementById("transferFromCash");
        const toCash = document.getElementById("transferToCash");
        const adjustAccountName = document.getElementById("adjustAccountName");
        const transferDate = document.getElementById("transferDate");
        const activeBankId =
            document.querySelector("li.active[data-bank]")?.dataset.bank || "";

        if (
            !modalTitleEl ||
            !dualFields ||
            !adjustFields ||
            !fromBank ||
            !toBank ||
            !fromCash ||
            !toCash ||
            !adjustAccountName
        ) {
            return;
        }

        document.getElementById("bankTransferMode").value = mode;
        transferDate.value = new Date().toISOString().slice(0, 10);
        fromBank.innerHTML = getBankOptions(activeBankId);
        toBank.innerHTML = getBankOptions(activeBankId);
        adjustAccountName.innerHTML = getBankOptions(activeBankId);

        dualFields.classList.remove("d-none");
        adjustFields.classList.add("d-none");
        fromBank.classList.remove("d-none");
        toBank.classList.remove("d-none");
        fromCash.classList.add("d-none");
        toCash.classList.add("d-none");

        if (mode === "bank_to_cash") {
            modalTitleEl.textContent = "Bank To Cash Transfer";
            toCash.classList.remove("d-none");
            toCash.readOnly = true;
            toBank.classList.add("d-none");
        } else if (mode === "cash_to_bank") {
            modalTitleEl.textContent = "Cash To Bank Transfer";
            fromCash.classList.remove("d-none");
            fromCash.readOnly = true;
            fromBank.classList.add("d-none");
        } else if (mode === "bank_to_bank") {
            modalTitleEl.textContent = "Bank To Bank Transfer";
        } else {
            modalTitleEl.textContent = "Bank Adjustment Entry";
            dualFields.classList.add("d-none");
            adjustFields.classList.remove("d-none");
        }
    }

    injectTransferActions();
    injectTransferModal();

    document.addEventListener("click", (event) => {
        const transferOption = event.target.closest(".bank-transfer-option");
        if (!transferOption) return;

        const mode = transferOption.dataset.mode || "bank_to_cash";
        setTransferModalMode(mode);

        const transferModalEl = document.getElementById("bankTransferModal");
        if (transferModalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(transferModalEl);
            modal.show();
        }
    });

    const bankTransferForm = document.getElementById("bankTransferForm");
    if (bankTransferForm) {
        bankTransferForm.addEventListener("submit", (event) => {
            event.preventDefault();

            const mode =
                document.getElementById("bankTransferMode")?.value ||
                "bank_to_bank";
            const amount =
                document.getElementById("transferAmount")?.value || 0;
            const fromBankId =
                document.getElementById("transferFromBank")?.value || "";
            const toBankId =
                document.getElementById("transferToBank")?.value || "";
            const adjustBankId =
                document.getElementById("adjustAccountName")?.value || "";
            const adjustType =
                document.getElementById("adjustType")?.value || "increase";
            const date = document.getElementById("transferDate")?.value || "";
            const description =
                document.getElementById("transferDescription")?.value || "";

            fetch("/dashboard/bank-accounts/transfer", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    mode,
                    from_bank_id:
                        mode === "cash_to_bank" || mode === "adjust_balance"
                            ? adjustBankId
                            : fromBankId,
                    to_bank_id:
                        mode === "bank_to_cash"
                            ? ""
                            : mode === "adjust_balance"
                              ? adjustBankId
                              : toBankId,
                    amount,
                    date,
                    description,
                    adjust_type: adjustType,
                }),
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        throw new Error(data?.message || "Transfer failed.");
                    }
                    return data;
                })
                .then((data) => {
                    showToast(
                        data.message || "Transfer completed successfully.",
                    );

                    const transferModalEl =
                        document.getElementById("bankTransferModal");
                    if (transferModalEl && window.bootstrap) {
                        const modal =
                            bootstrap.Modal.getOrCreateInstance(
                                transferModalEl,
                            );
                        modal.hide();
                    }

                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                })
                .catch((error) => {
                    showToast(error?.message || "Transfer failed.", "danger");
                });
        });
    }

    // Make the detail panel edit icon open the selected bank in edit mode
    const detailEditButton = document.getElementById("bankDetailEditBtn");
    if (detailEditButton) {
        detailEditButton.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            const activeItem = document.querySelector("li.active[data-bank]");
            if (!activeItem) return;
            const bankId = activeItem.dataset.bank;
            openBankModal("edit", bankId);
            showBankModal();
        });
    }

    // Prepare Add/Edit modal
    function openBankModal(mode, bankId = null) {
        if (!bankForm) return;

        // Restore modal defaults
        bankForm.reset();
        bankFormMethod.value = "POST";
        bankIdField.value = "";
        bankForm.action = "/dashboard/bank-accounts";
        if (bankDeleteBtn) {
            bankDeleteBtn.classList.add("d-none");
            bankDeleteBtn.dataset.bankId = "";
        }

        const submitButton = bankForm.querySelector("#bankFormSubmit");

        // Ensure the modal is in a predictable state
        const inputs = bankForm.querySelectorAll("input, select, textarea");
        inputs.forEach((input) => {
            input.disabled = false;
        });
        submitButton.style.display = "";

        if (mode === "view" && bankId) {
            modalTitle.textContent = "View Bank Account";
            submitButton.style.display = "none";
            bankFormMethod.value = "GET";
            bankForm.action = `/dashboard/bank-accounts/${bankId}`;

            // Load bank data via AJAX
            loadBankDetails(bankId);
            return;
        }

        if (mode === "edit" && bankId) {
            modalTitle.textContent = "Edit Bank Account";
            submitButton.textContent = "Update";
            bankFormMethod.value = "PUT";
            bankIdField.value = bankId;
            bankForm.action = `/dashboard/bank-accounts/${bankId}`;
            if (bankDeleteBtn) {
                bankDeleteBtn.classList.remove("d-none");
                bankDeleteBtn.dataset.bankId = bankId;
            }

            // Load bank data via AJAX
            loadBankDetails(bankId);
            return;
        }

        // Default: add new bank
        modalTitle.textContent = "Add Bank Account";
        submitButton.textContent = "Save Details";
    }

    if (bankDeleteBtn) {
        bankDeleteBtn.addEventListener("click", () => {
            const bankId = bankDeleteBtn.dataset.bankId || bankIdField.value;
            if (!bankId) return;

            if (
                !confirm("Are you sure you want to delete this bank account?")
            ) {
                return;
            }

            deleteBankAccount(bankId);
        });
    }

    function loadBankDetails(bankId) {
        if (!bankForm) return;

        fetch(`/dashboard/bank-accounts/${bankId}`, {
            headers: { Accept: "application/json" },
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const message =
                        data?.message || "Could not load bank account details.";
                    throw new Error(message);
                }
                return data;
            })
            .then((data) => {
                bankForm.querySelector('[name="display_name"]').value =
                    data.display_name || "";
                bankForm.querySelector('[name="opening_balance"]').value =
                    data.opening_balance ?? "";
                bankForm.querySelector('[name="as_of_date"]').value =
                    data.as_of_date ?? "";
                bankForm.querySelector('[name="account_number"]').value =
                    data.account_number ?? "";
                bankForm.querySelector('[name="swift_code"]').value =
                    data.swift_code ?? "";
                bankForm.querySelector('[name="iban"]').value = data.iban ?? "";
                bankForm.querySelector('[name="bank_name"]').value =
                    data.bank_name ?? "";
                bankForm.querySelector('[name="account_holder_name"]').value =
                    data.account_holder_name ?? "";
                bankForm.querySelector('[name="print_on_invoice"]').checked =
                    !!data.print_on_invoice;
            })
            .catch((error) => {
                showToast(
                    error?.message || "Could not load bank account details.",
                    "danger",
                );
            });
    }

    function normalizeDate(str) {
        // Support both dd/mm/yyyy and yyyy-mm-dd
        if (!str) return "";
        const trimmed = str.trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
            return trimmed;
        }
        const parts = trimmed.split("/");
        if (parts.length === 3) {
            const [d, m, y] = parts;
            const fullYear = y.length === 2 ? `20${y}` : y;
            return `${fullYear}-${m.padStart(2, "0")}-${d.padStart(2, "0")}`;
        }
        return trimmed.toLowerCase();
    }

    function applySearchFilter() {
        const q = sidebarSearch.value.trim().toLowerCase();
        const isDateSearch =
            /^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/.test(q) ||
            /^\d{4}-\d{2}-\d{2}$/.test(q);
        const normalizedDate = normalizeDate(q);

        list.querySelectorAll("li").forEach((li) => {
            if (!li.dataset.bank) return;

            if (isBankInactive(li.dataset.bank)) {
                li.style.display = "none";
                return;
            }

            const name =
                li.querySelector(".entity-name")?.textContent?.toLowerCase() ||
                "";
            const bankName = li.dataset.bankName?.toLowerCase() || "";
            const accountNumber = li.dataset.accountNumber?.toLowerCase() || "";
            const asOfDate = normalizeDate(li.dataset.asOfDate || "");

            if (q === "") {
                li.style.display = "";
                return;
            }

            if (isDateSearch) {
                li.style.display = asOfDate.includes(normalizedDate)
                    ? ""
                    : "none";
                return;
            }

            const matches = [name, bankName, accountNumber].some((val) =>
                val.includes(q),
            );
            li.style.display = matches ? "" : "none";
        });
    }

    if (sidebarSearch) {
        sidebarSearch.addEventListener("input", applySearchFilter);
    }

    function applyTableFilter() {
        applyBankTableFilters();
    }

    function getTransactionRowFromTarget(target) {
        return target.closest("tr[data-transaction-url]");
    }

    function openTransactionRow(row, target = "transaction") {
        const url =
            target === "history" ? row?.dataset.historyUrl : row?.dataset.transactionUrl;
        if (!url) {
            showToast("Transaction link is not available.", "warning");
            return;
        }
        window.location.href = url;
    }

    function deleteTransactionRow(row) {
        const url = row?.dataset.deleteUrl;
        const label = row?.dataset.transactionLabel || "transaction";

        if (!url) {
            showToast("Delete link is not available for this transaction.", "warning");
            return;
        }

        if (!confirm(`Are you sure you want to delete this ${label}?`)) {
            return;
        }

        fetch(url, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        })
            .then(async (res) => {
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    throw new Error(data?.message || "Could not delete transaction.");
                }
                return res;
            })
            .then(() => {
                showToast("Transaction deleted successfully.");
                setTimeout(() => window.location.reload(), 400);
            })
            .catch((error) => {
                showToast(error?.message || "Could not delete transaction.", "danger");
            });
    }

    if (tableSearch) {
        tableSearch.addEventListener("input", applyTableFilter);
    }

    initBankTableFilters();

    const bankSettingsBtn = document.getElementById("bankSettingsBtn");
    if (bankSettingsBtn) {
        bankSettingsBtn.addEventListener("click", () => {
            const settingsUrl = bankSettingsBtn.dataset.settingsUrl;
            if (settingsUrl) window.location.href = settingsUrl;
        });
    }

    const focusSearchBtn = document.getElementById("focusSearchBtn");
    if (focusSearchBtn) {
        focusSearchBtn.addEventListener("click", () => {
            if (tableSearch) {
                tableSearch.focus();
                return;
            }
            if (sidebarSearch) {
                sidebarSearch.focus();
            }
        });
    }

    // Clicking a date cell filters the table to only show rows for that date.
    if (bankTable) {
        bankTable.addEventListener("click", (event) => {
            if (event.target.closest(".action-dropdown")) return;

            const cell = event.target.closest("td");
            if (!cell) return;

            const row = cell.closest("tr");
            if (!row) return;

            // If clicked outside the date column, clear the date filter
            if (cell.dataset.columnKey !== "date") {
                if (activeFilterDate) {
                    activeFilterDate = null;
                    applyBankTableFilters();
                }
                return;
            }

            const clickedDate = cell.textContent.trim();
            if (!clickedDate) return;

            // Toggle the filter when clicking the same date again
            if (activeFilterDate === clickedDate) {
                activeFilterDate = null;
                applyBankTableFilters();
                return;
            }

            activeFilterDate = clickedDate;
            applyBankTableFilters();
        });

        bankTable.addEventListener("dblclick", (event) => {
            if (event.target.closest(".action-dropdown")) return;
            const row = getTransactionRowFromTarget(event.target);
            if (row) openTransactionRow(row);
        });
    }

    // Action dropdown handling for each table row
    document.addEventListener("click", (event) => {
        const toggle = event.target.closest(".action-toggle");
        const dropdown = event.target.closest(".action-dropdown");

        // Close any open menus if click is outside
        document
            .querySelectorAll(".action-dropdown .action-menu")
            .forEach((menu) => {
                if (
                    !menu.contains(event.target) &&
                    !menu.parentElement
                        .querySelector(".action-toggle")
                        ?.contains(event.target)
                ) {
                    menu.style.display = "none";
                }
            });

        if (!toggle) return;

        event.preventDefault();
        const menu = toggle.parentElement.querySelector(".action-menu");
        if (!menu) return;

        const isVisible = menu.style.display === "block";
        if (isVisible) {
            menu.style.display = "none";
            menu.style.left = "";
            menu.style.right = "";
            menu.style.transform = "";
            return;
        }

        menu.style.display = "block";
        menu.style.left = "";
        menu.style.right = "0";
        menu.style.transform = "none";
        const rect = menu.getBoundingClientRect();
        if (rect.right > window.innerWidth - 10) {
            menu.style.left = "auto";
            menu.style.right = "0";
            menu.style.transform = "translateX(-8px)";
        }
    });

    // Handle action item clicks (view/edit/delete)
    document.addEventListener("click", (event) => {
        const actionBtn = event.target.closest(".action-item");
        if (!actionBtn) return;

        const action = actionBtn.dataset.action;
        const row = actionBtn.closest("tr[data-transaction-url]");

        // Close open menus
        document
            .querySelectorAll(".action-dropdown .action-menu")
            .forEach((menu) => (menu.style.display = "none"));

        if (action === "edit") {
            openTransactionRow(row);
            return;
        }

        if (action === "delete") {
            deleteTransactionRow(row);
            return;
        }

        if (action === "history") {
            openTransactionRow(row, "history");
        }
    });

    // Handle form submission (create/update) via AJAX
    if (bankForm) {
        bankForm.addEventListener("submit", (event) => {
            event.preventDefault();

            const url = bankForm.action;
            const formData = new FormData(bankForm);

            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: formData,
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const message =
                            data?.message || "Could not save bank account.";
                        throw new Error(message);
                    }
                    return data;
                })
                .then((data) => {
                    showToast(data.message || "Saved successfully.");

                    // Reload the page to ensure table + sidebar stay in sync.
                    // (This keeps behavior simple and avoids edge cases.)
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                })
                .catch((error) => {
                    showToast(
                        error?.message || "Could not save bank account.",
                        "danger",
                    );
                });
        });
    }

    // Export table data to CSV (Excel-friendly)
    function exportTableToCsv(filename) {
        if (!bankTable) return;

        const rows = Array.from(bankTable.tBodies[0].rows).filter(
            (r) => r.dataset.bankId && r.style.display !== "none",
        );
        if (rows.length === 0) {
            showToast("No rows available to export.", "warning");
            return;
        }

        const headerCells = Array.from(bankTable.tHead.rows[0].cells).map(
            (th) => th.textContent.trim(),
        );
        const keepIndexes = Array.from(bankTable.tHead.rows[0].cells)
            .map((th, idx) =>
                th.dataset.col === "actions" ? -1 : idx,
            )
            .filter((idx) => idx !== -1);

        const csv = [keepIndexes.map((idx) => headerCells[idx]).join(",")];

        rows.forEach((row) => {
            const cols = keepIndexes.map((idx) => {
                const td = row.cells[idx];
                let text = td ? td.textContent.trim() : "";
                // Remove any extra whitespace/newlines
                text = text.replace(/\s+/g, " ");
                // Wrap values that contain comma/quote/newline
                if (/[",\n]/.test(text)) {
                    text = `"${text.replace(/"/g, '""')}"`;
                }
                return text;
            });
            csv.push(cols.join(","));
        });

        const blob = new Blob([csv.join("\n")], {
            type: "text/csv;charset=utf-8;",
        });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = "hidden";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    const exportExcelBtn = document.getElementById("exportExcelBtn");
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener("click", () => {
            const now = new Date();
            const filename = `bank-accounts-${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}.csv`;
            exportTableToCsv(filename);
        });
    }

    const printTableBtn = document.getElementById("printTableBtn");
    if (printTableBtn) {
        printTableBtn.addEventListener("click", () => {
            window.print();
        });
    }

    // Auto-hide flash messages after 4 seconds
    const flash = document.getElementById("bankFlash");
    if (flash) {
        setTimeout(() => {
            flash.style.transition = "opacity 0.3s";
            flash.style.opacity = "0";
            setTimeout(() => flash.remove(), 300);
        }, 4000);
    }

    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        toast.className = `alert alert-${type} mt-3`;
        toast.textContent = message;
        document
            .querySelector(".uper-panel")
            .insertAdjacentElement("afterend", toast);

        setTimeout(() => {
            toast.style.transition = "opacity 0.3s";
            toast.style.opacity = "0";
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
});
let sortDirection = {
    name: "desc",
    amount: "desc",
};

document.querySelectorAll(".bank-list-sort, .sortable").forEach((el) => {
    el.addEventListener("click", function () {
        let type = this.dataset.sort;

        sortDirection[type] = sortDirection[type] === "asc" ? "desc" : "asc";

        let list = document.getElementById("bankList");
        if (!list) return;
        let items = Array.from(list.querySelectorAll("li[data-bank]"));

        items.sort((a, b) => {
            if (type === "name") {
                let nameA = a
                    .querySelector(".entity-name")
                    .innerText.toLowerCase();
                let nameB = b
                    .querySelector(".entity-name")
                    .innerText.toLowerCase();

                return sortDirection[type] === "asc"
                    ? nameA.localeCompare(nameB)
                    : nameB.localeCompare(nameA);
            }

            if (type === "amount") {
                let amountA = parseFloat(a.dataset.openingBalance || "0");
                let amountB = parseFloat(b.dataset.openingBalance || "0");

                return sortDirection[type] === "asc"
                    ? amountA - amountB
                    : amountB - amountA;
            }

            return 0;
        });

        items.forEach((item) => list.appendChild(item));

        document
            .querySelectorAll(".bank-list-sort, .sortable")
            .forEach((button) => {
                button.classList.remove("active", "asc", "desc");
            });
        this.classList.add("active", sortDirection[type]);
    });
});
