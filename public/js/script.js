class TabManager {
    constructor() {
        this.tabs = [];
        this.tabStrip = document.getElementById('tab-strip');
        this.contentArea = document.getElementById('content-area');
        this.addBtn = document.getElementById('add-tab-btn');
        this.tabCounter = 0;
        this.MAX_TABS = 10;
        this.closingTabId = null;

        this.limitModal = new bootstrap.Modal(document.getElementById('tabLimitModal'));
        this.confirmModal = new bootstrap.Modal(document.getElementById('closeConfirmModal'));
        this.confirmBtn = document.getElementById('confirm-close-btn');
        this.docType = window.docType || 'invoice';
        this.docLabels = {
            invoice: 'Sale',
            estimate: 'Estimate',
            sale_order: 'Sale Order',
            proforma: 'Proforma',
            delivery_challan: 'Delivery Challan',
            sale_return: 'Sale Return',
            pos: 'POS',
        };

        this.init();
    }

    getTabPrefix() {
        return this.docLabels[this.docType] || 'Sale';
    }

    async init() {
        this.addBtn.addEventListener('click', () => this.createNewTab());

        // Get form template from DOM
        const template = document.getElementById('form-template');
        if (template) {
            this.formTemplate = template.innerHTML;
        } else {
            console.error("Form template not found!");
            this.formTemplate = `<div class="p-5 text-center text-danger">Form template not found.</div>`;
        }

        // Calculator functionality
        const calcIcon = document.getElementById('calc-icon');
        if (calcIcon) {
            calcIcon.addEventListener('click', () => {
                window.location.href = 'ms-calculator:';
            });
        }

        const saleToggle = document.getElementById('saleToggleSwitch');
        if (saleToggle) {
            saleToggle.addEventListener('change', (e) => {
                const isCash = e.target.checked;
                // Apply to all tabs
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    const partySelectorGroup = pane.querySelector('.party-selector-group');
                    if (partySelectorGroup) {
                        partySelectorGroup.classList.toggle('d-none', isCash);
                    }

                    const partyDetails = pane.querySelectorAll('.party-details');
                    partyDetails.forEach(field => {
                        if (isCash) {
                            field.classList.add('d-none');
                        }
                    });

                    if (isCash) {
                        const partyIdInput = pane.querySelector('.party-id');
                        const partySearchInput = pane.querySelector('.party-search-input');
                        if (partyIdInput) partyIdInput.value = '';
                        if (partySearchInput) partySearchInput.value = '';
                    }

                    const label = pane.querySelector('.party-label');
                    const cashFields = pane.querySelectorAll('.cash-fields');
                    if (label) {
                        label.textContent = isCash ? 'Billing Name (Optional)' : 'Party *';
                    }
                    cashFields.forEach(field => {
                        if (isCash) {
                            field.classList.remove('d-none');
                            field.classList.add('d-flex');
                        } else {
                            field.classList.add('d-none');
                            field.classList.remove('d-flex');
                        }
                    });

                    // Handle .cash-fields-two as well inside active tabs
                    const cashFieldsTwo = pane.querySelectorAll('.cash-fields-two');
                    cashFieldsTwo.forEach(field => {
                        if (isCash) {
                            field.classList.remove('d-none');
                            field.classList.add('d-flex');
                        } else {
                            field.classList.add('d-none');
                            field.classList.remove('d-flex');
                        }
                    });
                });

                // Update the template for new tabs
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = this.formTemplate;
                const tempPartySelectorGroup = tempDiv.querySelector('.party-selector-group');
                if (tempPartySelectorGroup) {
                    tempPartySelectorGroup.classList.toggle('d-none', isCash);
                }
                const tempPartyDetails = tempDiv.querySelectorAll('.party-details');
                tempPartyDetails.forEach(field => field.classList.add('d-none'));
                const tempLabel = tempDiv.querySelector('.party-label');
                const tempCashFields = tempDiv.querySelectorAll('.cash-fields');
                const tempCashFieldsTwo = tempDiv.querySelectorAll('.cash-fields-two');

                if (tempLabel) {
                    tempLabel.textContent = isCash ? 'Billing Name (Optional)' : 'Party *';
                }
                tempCashFields.forEach(field => {
                    if (isCash) {
                        field.classList.remove('d-none');
                        field.classList.add('d-flex');
                    } else {
                        field.classList.add('d-none');
                        field.classList.remove('d-flex');
                    }
                });
                tempCashFieldsTwo.forEach(field => {
                    if (isCash) {
                        field.classList.remove('d-none');
                        field.classList.add('d-flex');
                    } else {
                        field.classList.add('d-none');
                        field.classList.remove('d-flex');
                    }
                });
                this.formTemplate = tempDiv.innerHTML;
            });
            saleToggle.dispatchEvent(new Event('change'));
        }

        this.confirmBtn.addEventListener('click', () => {
            if (this.closingTabId) {
                this.executeClose(this.closingTabId);
                this.confirmModal.hide();
                this.closingTabId = null;
            }
        });

        // Initial Tab
        await this.createNewTab(`${this.getTabPrefix()} #1`);
    }

    getCloseFallbackUrl() {
        const referrer = document.referrer || '';
        if (referrer) {
            try {
                const referrerUrl = new URL(referrer);
                if (referrerUrl.origin === window.location.origin && referrerUrl.href !== window.location.href) {
                    return referrerUrl.href;
                }
            } catch (_) {}
        }

        return '/dashboard/sales';
    }

    closePageToPrevious() {
        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        window.location.href = this.getCloseFallbackUrl();
    }

   async createNewTab(title = null, content = "") {
        if (this.tabs.length >= this.MAX_TABS) {
            this.limitModal.show();
            return;
        }

        this.tabCounter++;
        const currentTitle = title || `${this.getTabPrefix()} #${this.tabCounter}`;
        const id = `tab-${Date.now()}-${this.tabCounter}`;

        // Use form template if no content provided
        const isFormTab = !content && !!this.formTemplate;
        if (isFormTab) {
            content = this.formTemplate;
        }

        const tabData = { id, title: currentTitle, content };
        this.tabs.push(tabData);

        this.renderTabElement(tabData);
        const paneEl = this.renderContentPane(tabData);
        this.activateTab(id);

        // Initialize form logic for this specific tab
        if (isFormTab && typeof initializeForm === 'function') {
            initializeForm(paneEl);

         const billInput = paneEl.querySelector('.bill-number');
if (billInput && window.nextInvoiceNumber) {
    const base = String(window.nextInvoiceNumber).trim();
    const match = base.match(/^(.*?)(\d+)$/);
    if (match) {
        const prefix = match[1]; // keep original prefix e.g. "CN-"
        const num = parseInt(match[2], 10);
        const tabOffset = this.tabs.length - 1;
        billInput.value = prefix + (num + tabOffset);
    } else {
        billInput.value = base + '-' + this.tabs.length;
    }
}
        }

        // Scroll tab strip to end
        this.tabStrip.scrollTo({ left: this.tabStrip.scrollWidth, behavior: 'smooth' });
    }

    renderTabElement(tab) {
        const tabEl = document.createElement('div');
        tabEl.className = 'tab-item';
        tabEl.id = `label-${tab.id}`;
        tabEl.innerHTML = `
            
            <span class="tab-title">${tab.title}</span>
            <span class="tab-close" title="Close Tab"><i class="bi bi-x-lg"></i></span>
        `;

        tabEl.addEventListener('click', (e) => {
            if (e.target.closest('.tab-close')) {
                this.closeTab(tab.id);
            } else {
                this.activateTab(tab.id);
            }
        });

        this.tabStrip.appendChild(tabEl);
    }

    renderContentPane(tab) {
        const paneEl = document.createElement('div');
        paneEl.className = 'tab-pane';
        paneEl.id = `content-${tab.id}`;
        paneEl.innerHTML = tab.content;
        this.contentArea.appendChild(paneEl);
        return paneEl;
    }

    activateTab(id) {
        // Remove active class from all tabs and panes
        document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));

        // Add active class to target
        const targetTab = document.getElementById(`label-${id}`);
        const targetPane = document.getElementById(`content-${id}`);

        if (targetTab && targetPane) {
            targetTab.classList.add('active');
            targetPane.classList.add('active');

            // Update address bar
            const tabData = this.tabs.find(t => t.id === id);
            const addressBar = document.getElementById('current-tab-title');
            if (tabData && addressBar) {
                addressBar.textContent = tabData.title;
            }
        }
    }

    closeTab(id) {
        if (this.tabs.length <= 1) {
            this.closePageToPrevious();
            return;
        }

        this.closingTabId = id;
        this.confirmModal.show();
    }

    executeClose(id) {
        const index = this.tabs.findIndex(t => t.id === id);
        const isActive = document.getElementById(`label-${id}`).classList.contains('active');

        // Remove from DOM
        document.getElementById(`label-${id}`).remove();
        document.getElementById(`content-${id}`).remove();

        // Remove from array
        this.tabs.splice(index, 1);

        // If active tab was closed, activate another
        if (isActive) {
            const nextIndex = index < this.tabs.length ? index : index - 1;
            this.activateTab(this.tabs[nextIndex].id);
        }

        // Renumber tabs
        this.renumberTabs();
    }

    renumberTabs() {
        this.tabs.forEach((tab, index) => {
            const newIndex = index + 1;
            tab.title = `${this.getTabPrefix()} #${newIndex}`;

            // Update the tab element title in UI
            const tabEl = document.getElementById(`label-${tab.id}`);
            if (tabEl) {
                const titleSpan = tabEl.querySelector('.tab-title');
                if (titleSpan) titleSpan.textContent = tab.title;
            }

            // Update content header if it exists
            const paneEl = document.getElementById(`content-${tab.id}`);
            if (paneEl) {
                const titleHeading = paneEl.querySelector('h2');
                if (titleHeading && titleHeading.textContent.startsWith('Content for Tab')) {
                    titleHeading.textContent = `Content for ${tab.title}`;
                }
            }
        });

        // Update the global counter to reflect the number of tabs
        this.tabCounter = this.tabs.length;

        // Update the address bar to show the renumbered title of the active tab
        const activeTab = this.tabs.find(t => {
            const el = document.getElementById(`label-${t.id}`);
            return el && el.classList.contains('active');
        });

        if (activeTab) {
            const addressBar = document.getElementById('current-tab-title');
            if (addressBar) addressBar.textContent = activeTab.title;
        }
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    new TabManager();
});
    
