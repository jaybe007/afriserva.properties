// Afriserva Properties: Core Client Application Scripts

document.addEventListener('DOMContentLoaded', () => {
    // 1. Search Tab Selector & Dynamic Filter Matrix (Nigeria Property Centre Style)
    const searchTabs = document.querySelectorAll('.search-tab-btn');
    const transactionTypeInput = document.getElementById('search-transaction-type');
    const bedroomsCol = document.getElementById('search-bedrooms-col');
    const bedroomsSelect = document.getElementById('search-beds-select');
    const minPriceSelect = document.getElementById('search-min-price-select');
    const maxPriceSelect = document.getElementById('search-max-price-select');

    const saleMinPrices = [
        { val: '', text: 'Min Price (Any)' },
        { val: '10000000', text: '₦10,000,000' },
        { val: '25000000', text: '₦25,000,000' },
        { val: '50000000', text: '₦50,000,000' },
        { val: '100000000', text: '₦100,000,000' },
        { val: '250000000', text: '₦250,000,000' },
        { val: '500000000', text: '₦500,000,000' },
        { val: '1000000000', text: '₦1,000,000,000' },
        { val: '2000000000', text: '₦2,000,000,000' }
    ];

    const saleMaxPrices = [
        { val: '', text: 'Max Price (Any)' },
        { val: '30000000', text: '₦30,000,000' },
        { val: '50000000', text: '₦50,000,000' },
        { val: '100000000', text: '₦100,000,000' },
        { val: '250000000', text: '₦250,000,000' },
        { val: '500000000', text: '₦500,000,000' },
        { val: '1000000000', text: '₦1,000,000,000' },
        { val: '2500000000', text: '₦2,500,000,000' },
        { val: '5000000000', text: '₦5,000,000,000+' }
    ];

    const rentMinPrices = [
        { val: '', text: 'Min Price (Any)' },
        { val: '300000', text: '₦300,000' },
        { val: '500000', text: '₦500,000' },
        { val: '1000000', text: '₦1,000,000' },
        { val: '2500000', text: '₦2,500,000' },
        { val: '5000000', text: '₦5,000,000' },
        { val: '10000000', text: '₦10,000,000' },
        { val: '20000000', text: '₦20,000,000' },
        { val: '50000000', text: '₦50,000,000' }
    ];

    const rentMaxPrices = [
        { val: '', text: 'Max Price (Any)' },
        { val: '1000000', text: '₦1,000,000' },
        { val: '2500000', text: '₦2,500,000' },
        { val: '5000000', text: '₦5,000,000' },
        { val: '10000000', text: '₦10,000,000' },
        { val: '20000000', text: '₦20,000,000' },
        { val: '35000000', text: '₦35,000,000' },
        { val: '50000000', text: '₦50,000,000' },
        { val: '100000000', text: '₦100,000,000+' }
    ];

    function populateSelect(selectEl, options) {
        if (!selectEl) return;
        const currentVal = selectEl.value;
        selectEl.innerHTML = '';
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.val;
            option.textContent = opt.text;
            if (opt.val === currentVal) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
    }

    function syncSearchFieldsForType(type) {
        const isRental = (type === 'rent' || type === 'short_let');
        const isNonResidential = (type === 'land' || type === 'commercial');

        // Bedrooms visibility
        if (bedroomsCol) {
            if (isNonResidential) {
                bedroomsCol.style.display = 'none';
                if (bedroomsSelect) bedroomsSelect.value = '';
            } else {
                bedroomsCol.style.display = '';
            }
        }

        // Price presets
        if (isRental) {
            populateSelect(minPriceSelect, rentMinPrices);
            populateSelect(maxPriceSelect, rentMaxPrices);
        } else {
            populateSelect(minPriceSelect, saleMinPrices);
            populateSelect(maxPriceSelect, saleMaxPrices);
        }
    }

    if (searchTabs.length && transactionTypeInput) {
        searchTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                searchTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const type = tab.getAttribute('data-type');
                transactionTypeInput.value = type;
                syncSearchFieldsForType(type);
            });
        });

        // Initialize active tab on load
        const activeTab = document.querySelector('.search-tab-btn.active');
        if (activeTab) {
            syncSearchFieldsForType(activeTab.getAttribute('data-type') || 'sale');
        }
    }

    // 2. Inspection Modal Logic
    const openViewingModalBtns = document.querySelectorAll('.js-open-viewing-modal');
    const viewingModal = document.getElementById('viewing-modal');
    const closeModalBtns = document.querySelectorAll('.js-close-modal');

    if (viewingModal) {
        openViewingModalBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                viewingModal.style.display = 'flex';
            });
        });

        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewingModal.style.display = 'none';
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target === viewingModal) {
                viewingModal.style.display = 'none';
            }
        });
    }

    // 3. Loan / Mortgage Calculator (on detail page)
    const calcBtn = document.getElementById('btn-calculate-mortgage');
    if (calcBtn) {
        calcBtn.addEventListener('click', () => {
            const price = parseFloat(document.getElementById('mortgage-price')?.value || 0);
            const downPercent = parseFloat(document.getElementById('mortgage-downpayment')?.value || 20);
            const ratePercent = parseFloat(document.getElementById('mortgage-rate')?.value || 16);
            const years = parseFloat(document.getElementById('mortgage-years')?.value || 20);

            const principal = price * (1 - (downPercent / 100));
            const monthlyRate = (ratePercent / 100) / 12;
            const totalMonths = years * 12;

            if (monthlyRate > 0 && totalMonths > 0) {
                const monthlyPayment = (principal * monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / 
                                       (Math.pow(1 + monthlyRate, totalMonths) - 1);

                const resultElem = document.getElementById('mortgage-monthly-result');
                if (resultElem) {
                    resultElem.innerText = '₦' + Math.round(monthlyPayment).toLocaleString() + ' / month';
                }
            }
        });
    }

    // 4. Mobile Navigation Drawer Toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileDrawer = document.getElementById('mobile-nav-drawer');
    const mobileDrawerClose = document.getElementById('mobile-drawer-close');

    if (mobileMenuToggle && mobileDrawer) {
        mobileMenuToggle.addEventListener('click', () => {
            mobileDrawer.style.display = 'block';
        });

        if (mobileDrawerClose) {
            mobileDrawerClose.addEventListener('click', () => {
                mobileDrawer.style.display = 'none';
            });
        }

        mobileDrawer.addEventListener('click', (e) => {
            if (e.target === mobileDrawer) {
                mobileDrawer.style.display = 'none';
            }
        });
    }

    // 5. Flash Message Auto-dismiss
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length) {
        setTimeout(() => {
            alerts.forEach(a => {
                a.style.transition = 'opacity 0.5s ease';
                a.style.opacity = '0';
                setTimeout(() => a.remove(), 500);
            });
        }, 5000);
    }
});

