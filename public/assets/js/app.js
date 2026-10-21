// Afriserva Properties: Core Client Application Scripts

document.addEventListener('DOMContentLoaded', () => {
    // 1. Search Tab Selector in Hero
    const searchTabs = document.querySelectorAll('.search-tab-btn');
    const transactionTypeInput = document.getElementById('search-transaction-type');

    if (searchTabs.length && transactionTypeInput) {
        searchTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                searchTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                transactionTypeInput.value = tab.getAttribute('data-type');
            });
        });
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

