/**
 * VYAPAR — Loan Accounts Page Logic
 *
 * This script mirrors the bank account page's behavior (bank.js) but operates
 * on loan accounts. It handles selecting loans from the sidebar, updating the
 * detail panel, filtering and exporting the table, and powering the Add/Edit
 * modal.
 */

(function () {
    const apiBase = "/dashboard/loan-accounts";

    function qs(selector) {
        return document.querySelector(selector);
    }

    function qsa(selector, root = document) {
        return Array.from(root.querySelectorAll(selector));
    }

    function formatCurrency(value) {
        const num = Number(value ?? 0);
        if (Number.isNaN(num)) return "-";
        return `₹ ${num.toFixed(2)}`;
    }

    function formatPercent(value) {
        if (value === null || value === undefined || value === "") return "-";
        const num = Number(value);
        if (Number.isNaN(num)) return "-";
        return `${num.toFixed(2)}%`;
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function getCsrfToken() {
        return (
            document.querySelector('meta[name="csrf-token"]')?.content ||
            window.App?.csrfToken ||
            ""
        );
    }

    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        toast.className = `alert alert-${type} mt-3`;
        toast.textContent = message;
        const panel = qs(".uper-panel");
        if (panel) {
            panel.insertAdjacentElement("afterend", toast);
            setTimeout(() => {
                toast.style.transition = "opacity 0.3s";
                toast.style.opacity = "0";
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }

    function setActiveLoanListItem(loanId) {
        qsa("#loanList li").forEach((li) => {
            const id = li.dataset.loan;
            li.classList.toggle("active", id === String(loanId));
        });
    }

    function setActiveTableRow(loanId) {
        applyLoanTableFilters();
    }

    const loanTableColumns = {
        type: 0,
        date: 1,
        principal: 2,
        charges: 3,
        total_amount: 4,
        loan_received_in: 5,
    };
    const loanTableFilterState = {};

    function getActiveLoanId() {
        return qs("#loanList li.active[data-loan]")?.dataset.loan || "";
    }

    function getLoanTable() {
        return qs("#loanTable");
    }

    function getLoanTableRows() {
        const loanTable = getLoanTable();
        if (!loanTable?.tBodies?.[0]) return [];
        return Array.from(loanTable.tBodies[0].rows).filter(
            (row) => row.dataset.loanId,
        );
    }

    function getLoanCellText(row, col) {
        const index = loanTableColumns[col];
        return index === undefined
            ? ""
            : row.cells[index]?.textContent.trim() || "";
    }

    function parseMoney(value) {
        return Number(String(value || "").replace(/[^0-9.-]/g, "")) || 0;
    }

    function normalizeLoanDate(value) {
        const text = String(value || "").trim();
        const match = text.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/);
        if (!match) return text.toLowerCase();
        const [, d, m, y] = match;
        const year = y.length === 2 ? `20${y}` : y;
        return `${year}-${m.padStart(2, "0")}-${d.padStart(2, "0")}`;
    }

    function setLoanFilterIconState() {
        qsa(".loan-filter-icon").forEach((icon) => {
            const dropdown = qs(`#${icon.dataset.filterTarget || ""}`);
            const col = dropdown?.dataset.col;
            const state = col ? loanTableFilterState[col] : null;
            const active = !!(
                state &&
                ((Array.isArray(state.values) && state.values.length) ||
                    state.value)
            );
            icon.classList.toggle("active", active);
        });
    }

    function toggleLoanNoResults(show) {
        const loanTable = getLoanTable();
        const tbody = loanTable?.tBodies?.[0];
        if (!tbody) return;
        tbody.querySelector(".loan-no-results")?.remove();
        if (!show) return;

        const row = document.createElement("tr");
        row.className = "loan-no-results";
        row.innerHTML =
            '<td colspan="7">No loan transactions found for selected filters.</td>';
        tbody.appendChild(row);
    }

    function rowMatchesLoanFilters(row) {
        const activeLoanId = getActiveLoanId();
        const tableSearch = qs("#tableSearchInput");
        const q = (tableSearch?.value || "").trim().toLowerCase();
        if (row.classList.contains("loan-empty-row")) {
            const hasActiveFilters = Object.values(loanTableFilterState).some(
                (state) => (Array.isArray(state.values) && state.values.length) || state.value,
            );
            return !!activeLoanId && row.dataset.loanId === activeLoanId && !q && !hasActiveFilters;
        }

        const rowText = Array.from(row.cells)
            .slice(0, -1)
            .map((cell) => cell.textContent.trim().toLowerCase())
            .join(" ");

        if (activeLoanId && row.dataset.loanId !== activeLoanId) return false;
        if (q && !rowText.includes(q)) return false;

        return Object.entries(loanTableFilterState).every(([col, state]) => {
            const text = getLoanCellText(row, col);
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
                const rowDate = normalizeLoanDate(text);
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

    function applyLoanTableFilters() {
        const rows = getLoanTableRows();
        toggleLoanNoResults(false);
        let visibleCount = 0;

        rows.forEach((row) => {
            const show = rowMatchesLoanFilters(row);
            row.style.display = show ? "" : "none";
            row.classList.toggle(
                "active-row",
                !!getActiveLoanId() && row.dataset.loanId === getActiveLoanId(),
            );
            if (show) visibleCount += 1;
        });

        toggleLoanNoResults(rows.length > 0 && visibleCount === 0);
        setLoanFilterIconState();
    }

    function readLoanDropdownFilter(dropdown) {
        const col = dropdown.dataset.col;
        const type = dropdown.dataset.filterType;
        if (!col || !type) return;

        if (type === "checkbox") {
            loanTableFilterState[col] = {
                type,
                values: qsa("input[type=checkbox]", dropdown).map(
                    (input) => input.checked ? input.value : null,
                ).filter(Boolean),
            };
            return;
        }

        loanTableFilterState[col] = {
            type,
            op: dropdown.querySelector("[data-filter-op]")?.value || "contains",
            value: dropdown.querySelector("[data-filter-value]")?.value || "",
        };
    }

    function closeLoanColumnFilters() {
        qsa(".loan-col-filter-dd.open").forEach((dd) =>
            dd.classList.remove("open"),
        );
    }

    function clearLoanColumnFilter(id) {
        const dropdown = qs(`#${id}`);
        if (!dropdown) return;
        qsa("input[type=checkbox]", dropdown).forEach(
            (input) => (input.checked = false),
        );
        qsa("[data-filter-value]", dropdown).forEach(
            (input) => (input.value = ""),
        );
        qsa("[data-filter-op]", dropdown).forEach(
            (select) => (select.selectedIndex = 0),
        );
        delete loanTableFilterState[dropdown.dataset.col];
        applyLoanTableFilters();
    }

    function populateLoanCheckboxFilters() {
        qsa('.loan-col-filter-dd[data-filter-type="checkbox"]').forEach(
            (dropdown) => {
                const col = dropdown.dataset.col;
                const optionsWrap = dropdown.querySelector(
                    ".loan-filter-options",
                );
                if (!col || !optionsWrap) return;

                const values = [
                    ...new Set(
                        getLoanTableRows()
                            .filter((row) => row.dataset.transactionId)
                            .map((row) => getLoanCellText(row, col))
                            .filter(Boolean),
                    ),
                ].sort((a, b) => a.localeCompare(b));

                optionsWrap.innerHTML = values.length
                    ? values
                          .map(
                              (value) => `
                    <label class="lcfd-cb-row">
                        <input type="checkbox" value="${escapeHtml(value)}">
                        ${escapeHtml(value)}
                    </label>
                `,
                          )
                          .join("")
                    : '<div class="text-center text-muted py-3">No options</div>';
            },
        );
    }

    function initLoanColumnResize() {
        let isResizing = false;
        let startX = 0;
        let startW = 0;
        let th = null;
        let handle = null;

        document.addEventListener("mousedown", (event) => {
            if (!event.target.classList.contains("loan-col-resize-handle")) {
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

    function initLoanTableFilters() {
        populateLoanCheckboxFilters();
        initLoanColumnResize();

        qsa(".loan-filter-icon").forEach((icon) => {
            icon.addEventListener("click", (event) => {
                event.stopPropagation();
                const dropdown = qs(`#${icon.dataset.filterTarget || ""}`);
                if (!dropdown) return;
                const isOpen = dropdown.classList.contains("open");
                closeLoanColumnFilters();
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

        qsa(".loan-col-filter-dd").forEach((dropdown) => {
            dropdown.addEventListener("click", (event) =>
                event.stopPropagation(),
            );
            qsa(
                "input[type=checkbox], [data-filter-value], [data-filter-op]",
                dropdown,
            ).forEach((field) => {
                field.addEventListener("input", () => {
                    readLoanDropdownFilter(dropdown);
                    applyLoanTableFilters();
                });
                field.addEventListener("change", () => {
                    readLoanDropdownFilter(dropdown);
                    applyLoanTableFilters();
                });
            });
        });

        qsa("[data-clear-loan-filter]").forEach((button) => {
            button.addEventListener("click", () =>
                clearLoanColumnFilter(button.dataset.clearLoanFilter),
            );
        });

        qsa("[data-apply-loan-filter]").forEach((button) => {
            button.addEventListener("click", () => {
                const dropdown = button.closest(".loan-col-filter-dd");
                if (dropdown) readLoanDropdownFilter(dropdown);
                applyLoanTableFilters();
                closeLoanColumnFilters();
            });
        });

        document.addEventListener("click", closeLoanColumnFilters);
    }

    function updateDetailPanel(data) {
        const nameEl = qs("#loanDetailNameText") || qs("#loanDetailName");
        const accountEl = qs("#loanDetailAccountNumber");
        const lenderBankEl = qs("#loanDetailLenderBank");
        const balanceEl = qs("#loanDetailCurrentBalance");
        const interestEl = qs("#loanDetailInterestRate");

        if (nameEl) nameEl.textContent = data.display_name || "Select a loan";
        if (accountEl) accountEl.textContent = data.account_number || "-";
        if (lenderBankEl)
            lenderBankEl.textContent = data.lender_bank?.display_name || "-";
        if (balanceEl)
            balanceEl.textContent = formatCurrency(data.current_balance);
        if (interestEl)
            interestEl.textContent = formatPercent(data.interest_rate);
    }

    function updateLoanBalanceUI(loan) {
        if (!loan?.id) return;
        updateDetailPanel(loan);
        const listItem = qs(`#loanList li[data-loan="${loan.id}"]`);
        if (listItem) {
            listItem.dataset.currentBalance = loan.current_balance ?? 0;
            const balanceEl = listItem.querySelector(".entity-balance");
            if (balanceEl) {
                balanceEl.textContent = formatCurrency(loan.current_balance);
                balanceEl.classList.toggle("negative", Number(loan.current_balance) < 0);
                balanceEl.classList.toggle("positive", Number(loan.current_balance) >= 0);
            }
        }
    }

    function loadLoanDetails(loanId) {
        return fetch(`${apiBase}/${loanId}`, {
            headers: { Accept: "application/json" },
        }).then((res) => {
            if (!res.ok) throw new Error("Unable to load loan details.");
            return res.json();
        });
    }

    function openLoanModal(mode, loanId = null) {
        const loanForm = qs("#loanForm");
        const loanFormMethod = qs("#loanFormMethod");
        const loanIdField = qs("#loanIdField");
        const loanModalLabel = qs("#loanModalLabel");
        if (!loanForm || !loanFormMethod || !loanIdField || !loanModalLabel)
            return;

        loanForm.reset();
        loanFormMethod.value = "POST";
        loanIdField.value = "";
        loanForm.action = apiBase;
        loanModalLabel.textContent = "Add Loan Account";

        if (mode === "edit" && loanId) {
            loanFormMethod.value = "PUT";
            loanIdField.value = loanId;
            loanForm.action = `${apiBase}/${loanId}`;
            loanModalLabel.textContent = "Edit Loan Account";

            loadLoanDetails(loanId)
                .then((data) => {
                    loanForm.querySelector('[name="display_name"]').value =
                        data.display_name || "";
                    loanForm.querySelector('[name="lender_bank_id"]').value =
                        data.lender_bank_id || "";
                    loanForm.querySelector('[name="account_number"]').value =
                        data.account_number || "";
                    loanForm.querySelector('[name="description"]').value =
                        data.description || "";
                    loanForm.querySelector('[name="current_balance"]').value =
                        data.current_balance ?? "";
                    loanForm.querySelector('[name="balance_as_of"]').value =
                        data.balance_as_of ?? "";
                    loanForm.querySelector('[name="received_in"]').value =
                        data.received_in || "";
                    loanForm.querySelector(
                        '[name="processing_fee_paid_from_id"]',
                    ).value = data.processing_fee_paid_from_id || "";
                    loanForm.querySelector('[name="processing_fee"]').value =
                        data.processing_fee ?? "";
                    loanForm.querySelector('[name="interest_rate"]').value =
                        data.interest_rate ?? "";
                    loanForm.querySelector('[name="term_months"]').value =
                        data.term_months ?? "";
                })
                .catch((err) =>
                    showToast(err.message || "Failed to load loan.", "danger"),
                );
        }

        const modalEl = document.getElementById("loanModal");
        if (modalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function deleteLoan(loanId) {
        if (!confirm("Are you sure you want to delete this loan account?"))
            return;

        fetch(`${apiBase}/${loanId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: "application/json",
            },
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(
                        data.message || "Could not delete loan account.",
                    );
                }
                return data;
            })
            .then(() => {
                const listItem = qs(`#loanList li[data-loan="${loanId}"]`);
                const tableRow = qs(
                    `#loanTable tbody tr[data-loan-id="${loanId}"]`,
                );
                if (listItem) listItem.remove();
                if (tableRow) tableRow.remove();
                showToast("Loan account deleted successfully.");

                // Select next available loan
                const next = qs("#loanList li[data-loan]");
                if (next) {
                    selectLoan(next.dataset.loan);
                } else {
                    updateDetailPanel({});
                }
            })
            .catch((err) =>
                showToast(
                    err.message || "Could not delete loan account.",
                    "danger",
                ),
            );
    }

    function exportTableToCsv(tableEl, filename) {
        if (!tableEl) return;

        const rows = Array.from(tableEl.tBodies[0].rows).filter(
            (r) => r.dataset.transactionId && r.style.display !== "none",
        );
        if (!rows.length) {
            showToast("No rows available to export.", "warning");
            return;
        }

        const headerCells = Array.from(tableEl.tHead.rows[0].cells).map((th) =>
            th.textContent.trim(),
        );
        const keepIndexes = headerCells
            .map((header, idx) =>
                header.toLowerCase() === "actions" ? -1 : idx,
            )
            .filter((idx) => idx !== -1);

        const csv = [keepIndexes.map((idx) => headerCells[idx]).join(",")];

        rows.forEach((row) => {
            const cols = keepIndexes.map((idx) => {
                const td = row.cells[idx];
                let text = td ? td.textContent.trim() : "";
                text = text.replace(/\s+/g, " ");
                if (/[,"\n]/.test(text)) {
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

    function applyTableSearch(tableEl, query) {
        applyLoanTableFilters();
    }

    function getActiveLoanRow() {
        return qs("#loanList li.active[data-loan]");
    }

    function closeLoanTransactionMenu() {
        qs("#loanTransactionMenu")?.classList.remove("open");
    }

    function hideActionMenus() {
        document.querySelectorAll(".action-menu").forEach((menu) => {
            menu.style.display = "none";
            menu.style.top = "";
            menu.style.left = "";
        });
    }

    function positionActionMenu(toggle, menu) {
        const rect = toggle.getBoundingClientRect();
        const menuWidth = Math.max(menu.offsetWidth || 140, 140);
        const menuHeight = menu.offsetHeight || 44;
        const gap = 6;
        let left = rect.right - menuWidth;
        let top = rect.bottom + gap;

        if (left < 8) left = 8;
        if (left + menuWidth > window.innerWidth - 8) {
            left = window.innerWidth - menuWidth - 8;
        }
        if (top + menuHeight > window.innerHeight - 8) {
            top = rect.top - menuHeight - gap;
        }
        if (top < 8) top = 8;

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
    }

    function setFormDate(form, value) {
        const field = form?.querySelector('[name="transaction_date"]');
        if (field) field.value = value || new Date().toISOString().slice(0, 10);
    }

    function openTakeMoreLoanModal(row = null) {
        const active = getActiveLoanRow();
        if (!active) return showToast("Please select a loan first.", "warning");
        const form = qs("#takeMoreLoanForm");
        const modalEl = qs("#takeMoreLoanModal");
        if (!form || !modalEl || !window.bootstrap) return;

        form.reset();
        form.dataset.mode = row ? "edit" : "add";
        form.dataset.loanId = active.dataset.loan;
        form.querySelector('[name="transaction_id"]').value = row?.dataset.transactionId || "";
        form.querySelector('[name="amount"]').value = row?.dataset.amount || "0";
        setFormDate(form, row?.dataset.date);
        const bankField = form.querySelector('[name="bank_account_id"]');
        if (bankField && row?.dataset.bankAccountId) bankField.value = row.dataset.bankAccountId;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function openLoanChargeModal(row = null) {
        const active = getActiveLoanRow();
        if (!active) return showToast("Please select a loan first.", "warning");
        const form = qs("#loanChargeForm");
        const modalEl = qs("#loanChargeModal");
        if (!form || !modalEl || !window.bootstrap) return;

        form.reset();
        form.dataset.mode = row ? "edit" : "add";
        form.dataset.loanId = active.dataset.loan;
        form.querySelector('[name="transaction_id"]').value = row?.dataset.transactionId || "";
        form.querySelector('[name="details"]').value = row?.dataset.details || "";
        form.querySelector('[name="amount"]').value = row?.dataset.amount || "0";
        form.querySelector('[name="total_amount"]').value = row?.dataset.amount || "0";
        setFormDate(form, row?.dataset.date);
        const bankField = form.querySelector('[name="bank_account_id"]');
        if (bankField && row?.dataset.bankAccountId) bankField.value = row.dataset.bankAccountId;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function calculateEmiTotal(form) {
        const principal = Number(form?.querySelector('[name="principal_amount"]')?.value || 0);
        const interest = Number(form?.querySelector('[name="interest_amount"]')?.value || 0);
        const total = form?.querySelector('[name="amount"]');
        if (total) total.value = (principal + interest).toFixed(2);
    }

    function openLoanEmiPayModal(row = null) {
        const active = getActiveLoanRow();
        if (!active) return showToast("Please select a loan first.", "warning");
        const form = qs("#loanEmiPayForm");
        const modalEl = qs("#loanEmiPayModal");
        if (!form || !modalEl || !window.bootstrap) return;

        form.reset();
        form.dataset.mode = row ? "edit" : "add";
        form.dataset.loanId = active.dataset.loan;
        form.querySelector('[name="transaction_id"]').value = row?.dataset.transactionId || "";
        form.querySelector('[name="principal_amount"]').value = row?.dataset.principal || "0";
        form.querySelector('[name="interest_amount"]').value = row?.dataset.interest || "0";
        form.querySelector('[name="amount"]').value = row?.dataset.amount || "0";
        setFormDate(form, row?.dataset.date);
        const bankField = form.querySelector('[name="bank_account_id"]');
        if (bankField && row?.dataset.bankAccountId) bankField.value = row.dataset.bankAccountId;
        calculateEmiTotal(form);

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function getLoanTransactionUrl(form) {
        const loanId = form.dataset.loanId;
        const transactionId = form.querySelector('[name="transaction_id"]')?.value;
        if (!loanId) return "";
        return transactionId
            ? `${apiBase}/${loanId}/transactions/${transactionId}`
            : `${apiBase}/${loanId}/transactions`;
    }

    function renderLoanTransactionRow(transaction) {
        const principal = Number(transaction.principal || 0);
        const charges = Number(transaction.charges || 0);
        const total = Number(transaction.total_amount || 0);
        return `
            <tr data-loan-id="${escapeHtml(transaction.loan_id)}" data-transaction-id="${escapeHtml(transaction.id)}" data-entry-type="${escapeHtml(transaction.type)}" data-date="${escapeHtml(transaction.date_value)}" data-amount="${escapeHtml(transaction.total_amount)}" data-principal="${escapeHtml(transaction.principal || 0)}" data-interest="${escapeHtml(transaction.charges || 0)}" data-bank-account-id="${escapeHtml(transaction.bank_account_id || "")}" data-details="${escapeHtml(transaction.details || "")}">
                <td>${escapeHtml(transaction.label)}</td>
                <td>${escapeHtml(transaction.date || "-")}</td>
                <td>${formatCurrency(principal)}</td>
                <td>${formatCurrency(charges)}</td>
                <td>${formatCurrency(total)}</td>
                <td>${escapeHtml(transaction.bank_name || "-")}</td>
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
        `;
    }

    function upsertLoanTransactionRow(transaction) {
        const tbody = getLoanTable()?.tBodies?.[0];
        if (!tbody) return;

        const selector = `tr[data-transaction-id="${transaction.id}"]`;
        const existing = tbody.querySelector(selector);
        if (existing) {
            existing.outerHTML = renderLoanTransactionRow(transaction);
        } else {
            tbody
                .querySelectorAll(`tr.loan-empty-row[data-loan-id="${transaction.loan_id}"]`)
                .forEach((row) => row.remove());
            tbody.insertAdjacentHTML("afterbegin", renderLoanTransactionRow(transaction));
        }

        populateLoanCheckboxFilters();
        applyLoanTableFilters();
    }

    function submitLoanTransaction(form, entryType) {
        const url = getLoanTransactionUrl(form);
        if (!url) return showToast("Please select a loan first.", "warning");
        const transactionId = form.querySelector('[name="transaction_id"]')?.value;
        const payload = {
            entry_type: entryType,
            amount: form.querySelector('[name="amount"]')?.value || 0,
            principal_amount: form.querySelector('[name="principal_amount"]')?.value || "",
            interest_amount: form.querySelector('[name="interest_amount"]')?.value || "",
            transaction_date: form.querySelector('[name="transaction_date"]')?.value || "",
            bank_account_id: form.querySelector('[name="bank_account_id"]')?.value || "",
            details: form.querySelector('[name="details"]')?.value || "",
        };

        fetch(url, {
            method: transactionId ? "PUT" : "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || "Could not save loan transaction.");
                return data;
            })
            .then((data) => {
                updateLoanBalanceUI(data.loan);
                upsertLoanTransactionRow(data.transaction);
                bootstrap.Modal.getInstance(form.closest(".modal"))?.hide();
                showToast(data.message || "Loan transaction saved successfully.");
            })
            .catch((err) => showToast(err.message || "Could not save loan transaction.", "danger"));
    }

    function openLoanTransactionEditor(row) {
        if (!row?.dataset.transactionId) return;
        setActiveLoanListItem(row.dataset.loanId);
        setActiveTableRow(row.dataset.loanId);
        if (row.dataset.entryType === "loan_more" || row.dataset.entryType === "loan_adjustment") {
            openTakeMoreLoanModal(row);
        } else if (row.dataset.entryType === "emi_pay") {
            openLoanEmiPayModal(row);
        } else {
            openLoanChargeModal(row);
        }
    }

    function applySidebarSearch(query) {
        const q = query.trim().toLowerCase();
        qsa("#loanList li").forEach((li) => {
            const name =
                li.querySelector(".entity-name")?.textContent?.toLowerCase() ||
                "";
            const matching = name.includes(q);
            li.style.display = q === "" || matching ? "" : "none";
        });
    }

    function selectLoan(loanId) {
        const listItem = qs(`#loanList li[data-loan="${loanId}"]`);
        if (!listItem) return;

        setActiveLoanListItem(loanId);
        setActiveTableRow(loanId);

        loadLoanDetails(loanId)
            .then((data) => updateDetailPanel(data))
            .catch((err) =>
                showToast(err.message || "Could not load loan.", "danger"),
            );
    }

    document.addEventListener("DOMContentLoaded", () => {
        const loanTable = qs("#loanTable");
        const tableSearch = qs("#tableSearchInput");
        const focusSearchBtn = qs("#focusSearchBtn");
        const exportExcelBtn = qs("#exportExcelBtn");
        const printTableBtn = qs("#printTableBtn");
        const addLoanBtn = qs("#addLoanBtn");
        const loanDetailEditBtn = qs("#loanDetailEditBtn");
        const loanTransactionMenuBtn = qs("#loanTransactionMenuBtn");
        const loanTransactionMenu = qs("#loanTransactionMenu");
        const takeMoreLoanForm = qs("#takeMoreLoanForm");
        const loanChargeForm = qs("#loanChargeForm");
        const loanEmiPayForm = qs("#loanEmiPayForm");

        initLoanTableFilters();

        // Initialize selection
        const firstLoan = qs("#loanList li[data-loan]");
        if (firstLoan) {
            selectLoan(firstLoan.dataset.loan);
        }

        // Sidebar search
        const loanSearch = qs("#loanSearchInput");
        if (loanSearch) {
            loanSearch.addEventListener("input", () =>
                applySidebarSearch(loanSearch.value),
            );
        }

        // Table search
        if (tableSearch && loanTable) {
            tableSearch.addEventListener("input", () =>
                applyTableSearch(loanTable, tableSearch.value),
            );
        }

        if (focusSearchBtn && tableSearch) {
            focusSearchBtn.addEventListener("click", () => tableSearch.focus());
        }

        if (exportExcelBtn && loanTable) {
            exportExcelBtn.addEventListener("click", () => {
                const now = new Date();
                const filename = `loan-accounts-${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}.csv`;
                exportTableToCsv(loanTable, filename);
            });
        }

        if (printTableBtn) {
            printTableBtn.addEventListener("click", () => window.print());
        }

        // Sidebar list click
        qsa("#loanList li[data-loan]").forEach((li) => {
            li.addEventListener("click", () => {
                selectLoan(li.dataset.loan);
            });
        });

        // Detail edit button
        if (loanDetailEditBtn) {
            loanDetailEditBtn.addEventListener("click", () => {
                const active = qs("#loanList li.active");
                if (active) {
                    openLoanModal("edit", active.dataset.loan);
                }
            });
        }

        // Action dropdowns (edit/delete)
        document.addEventListener("click", (event) => {
            const loanMenuAction = event.target.closest("[data-loan-transaction-open]");
            if (loanMenuAction) {
                closeLoanTransactionMenu();
                if (
                    loanMenuAction.dataset.loanTransactionOpen === "loan_more" ||
                    loanMenuAction.dataset.loanTransactionOpen === "loan_adjustment"
                ) {
                    openTakeMoreLoanModal();
                } else if (loanMenuAction.dataset.loanTransactionOpen === "emi_pay") {
                    openLoanEmiPayModal();
                } else {
                    openLoanChargeModal();
                }
                return;
            }

            if (loanTransactionMenuBtn?.contains(event.target)) {
                loanTransactionMenu?.classList.toggle("open");
                return;
            }

            if (loanTransactionMenu && !loanTransactionMenu.contains(event.target)) {
                closeLoanTransactionMenu();
            }

            const toggle = event.target.closest(".action-toggle");
            if (toggle) {
                const menu = toggle.parentElement.querySelector(".action-menu");
                if (!menu) return;
                const isVisible = menu.style.display === "block";
                hideActionMenus();
                if (!isVisible) {
                    menu.style.display = "block";
                    positionActionMenu(toggle, menu);
                }
                return;
            }

            const actionBtn = event.target.closest(".action-item");
            if (!actionBtn) return;

            const action = actionBtn.dataset.action;
            const loanId = actionBtn.dataset.loanId;

            hideActionMenus();

            if (action === "edit" && loanId) {
                setActiveLoanListItem(loanId);
                setActiveTableRow(loanId);
                openLoanModal("edit", loanId);
            }

            if (action === "delete" && loanId) {
                deleteLoan(loanId);
            }

            if (action === "edit-transaction") {
                openLoanTransactionEditor(actionBtn.closest("tr"));
            }
        });

        if (loanTable) {
            loanTable.addEventListener("dblclick", (event) => {
                const row = event.target.closest("tr[data-transaction-id]");
                if (row) openLoanTransactionEditor(row);
            });
        }

        window.addEventListener("resize", hideActionMenus);
        document.addEventListener("scroll", hideActionMenus, true);

        if (takeMoreLoanForm) {
            takeMoreLoanForm.addEventListener("submit", (event) => {
                event.preventDefault();
                submitLoanTransaction(takeMoreLoanForm, "loan_adjustment");
            });
        }

        if (loanChargeForm) {
            const amount = loanChargeForm.querySelector('[name="amount"]');
            const total = loanChargeForm.querySelector('[name="total_amount"]');
            amount?.addEventListener("input", () => {
                if (total) total.value = amount.value || "0";
            });
            loanChargeForm.addEventListener("submit", (event) => {
                event.preventDefault();
                submitLoanTransaction(loanChargeForm, "loan_charge");
            });
        }

        if (loanEmiPayForm) {
            const principal = loanEmiPayForm.querySelector('[name="principal_amount"]');
            const interest = loanEmiPayForm.querySelector('[name="interest_amount"]');
            principal?.addEventListener("input", () => calculateEmiTotal(loanEmiPayForm));
            interest?.addEventListener("input", () => calculateEmiTotal(loanEmiPayForm));
            loanEmiPayForm.addEventListener("submit", (event) => {
                event.preventDefault();
                calculateEmiTotal(loanEmiPayForm);
                submitLoanTransaction(loanEmiPayForm, "emi_pay");
            });
        }

        // Add Loan button
        if (addLoanBtn) {
            addLoanBtn.addEventListener("click", () => openLoanModal("add"));
        }
    });
})();

let sortDirection = {
    name: "asc",
    amount: "asc",
};

document.querySelectorAll(".sortable").forEach((el) => {
    el.addEventListener("click", function () {
        let type = this.dataset.sort;

        // toggle asc/desc
        sortDirection[type] = sortDirection[type] === "asc" ? "desc" : "asc";

        let list = document.getElementById("loanList");
        let items = Array.from(list.querySelectorAll("li[data-loan]"));

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
                let amountA = parseFloat(a.dataset.currentBalance);
                let amountB = parseFloat(b.dataset.currentBalance);

                return sortDirection[type] === "asc"
                    ? amountA - amountB
                    : amountB - amountA;
            }
        });

        // re-append sorted items
        items.forEach((item) => list.appendChild(item));
    });
});
