/**
 * BarcodeScannerManager
 * Fixed version:
 * - Removed unnecessary loadPOItems() API call (data already in blade)
 * - Fixed scanner gun detection timing (was too strict, causing misses)
 * - Fixed input focus re-grab after scan
 * - Updated selectors to match new UI elements
 */
class BarcodeScannerManager {
    constructor(poId) {
        this.poId = poId;
        this.scannedItems = [];
        this.isScanning = false;
        this.totalOrdered = 0;
        this.totalScanned = 0;
        this.scanTimeout = null;
        this.lastInputTime = 0;
        this.isProcessing = false; // ✅ FIX: prevent duplicate submission
        this.init();
    }

    init() {
        console.log('🔍 Initializing Barcode Scanner...', { poId: this.poId });
        this.setupEventListeners();
        // ✅ FIX: Calculate totals directly from DOM (no API call needed - data is already in blade)
        this.calculateTotals();
    }

    setupEventListeners() {
        // ✅ BEGIN SCAN
        const beginBtn = document.getElementById('beginScanBtn');
        if (beginBtn) {
            beginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.beginScanning();
            });
        }

        // ✅ END SCAN
        const endBtn = document.getElementById('endScanBtn');
        if (endBtn) {
            endBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.endScanning();
            });
        }

        // ✅ BARCODE INPUT
        const barcodeInput = document.getElementById('barcodeInput');
        if (barcodeInput) {

            // ENTER key = manual entry
            barcodeInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!this.isScanning) return;

                    const barcode = barcodeInput.value.trim();
                    if (barcode.length === 0) return;

                    // ✅ FIX: Cancel any pending scanner-gun timeout
                    clearTimeout(this.scanTimeout);
                    this.scanTimeout = null;

                    console.log('⌨️ Manual ENTER:', barcode);
                    this.processBarcode(barcode, barcodeInput);
                }
            });

            // ✅ FIX: Scanner gun detection - improved timing
            barcodeInput.addEventListener('input', (e) => {
                if (!this.isScanning) return;

                const currentTime = Date.now();
                const timeDiff = currentTime - this.lastInputTime;
                this.lastInputTime = currentTime;

                clearTimeout(this.scanTimeout);

                // Scanner guns typically send chars < 30ms apart AND send all at once
                // We use a slightly longer window (100ms) to catch the full scan
                const isScannerGun = timeDiff < 50 && barcodeInput.value.length > 1;

                if (isScannerGun) {
                    console.log('🔫 Scanner gun input detected, waiting for complete scan...');
                    this.scanTimeout = setTimeout(() => {
                        const barcode = barcodeInput.value.trim();
                        if (barcode.length > 0) {
                            console.log('🔫 Scanner gun final value:', barcode);
                            this.processBarcode(barcode, barcodeInput);
                        }
                    }, 100); // ✅ FIX: reduced from 250ms → 100ms for faster response
                }
                // Manual typing: wait for ENTER key (no auto-trigger)
            });

            // ✅ Keep input focused while scanning
            barcodeInput.addEventListener('blur', () => {
                if (this.isScanning) {
                    setTimeout(() => {
                        if (this.isScanning) {
                            barcodeInput.focus();
                        }
                    }, 100);
                }
            });
        }

        // ✅ COMPLETE SCAN
        const completeBtn = document.getElementById('completeScanBtn');
        if (completeBtn) {
            completeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.completeScanning();
            });
        }

        // ✅ CANCEL / BACK
        const cancelBtn = document.getElementById('cancelScanBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.totalScanned > 0) {
                    if (confirm('Return to purchase orders? Any unsaved scan progress will be lost.')) {
                        window.location.href = '/purchase-order';
                    }
                } else {
                    window.location.href = '/purchase-order';
                }
            });
        }

        console.log('✅ Event listeners ready');
    }

    /**
     * ✅ FIX: Central barcode processor - used by both manual & scanner gun paths
     */
    async processBarcode(barcode, inputEl) {
        // Prevent double-processing
        if (this.isProcessing) {
            console.warn('⏳ Already processing a scan, skipping:', barcode);
            return;
        }

        if (!barcode || barcode.length === 0) return;

        // ✅ FIX: Allow any length barcode (removed 8-char minimum that was blocking valid scans)
        // The server validates the barcode anyway
        if (barcode.length < 3) {
            this.showToast('warning', 'Barcode too short. Please scan again.');
            if (inputEl) inputEl.value = '';
            return;
        }

        await this.scanBarcode(barcode);

        if (inputEl) {
            inputEl.value = '';
            if (this.isScanning) {
                inputEl.focus();
            }
        }
    }

    calculateTotals() {
        const rows = document.querySelectorAll('#poItemsBody tr[data-quantity-ordered]');
        this.totalOrdered = 0;

        rows.forEach(row => {
            const qty = parseInt(row.dataset.quantityOrdered) || 0;
            this.totalOrdered += qty;
        });

        console.log('📊 Total ordered:', this.totalOrdered);
        this.updateStats();
    }

    beginScanning() {
        console.log('🎬 BEGIN SCANNING');
        this.isScanning = true;

        // Toggle buttons
        const beginBtn = document.getElementById('beginScanBtn');
        const endBtn   = document.getElementById('endScanBtn');
        if (beginBtn) beginBtn.style.display = 'none';
        if (endBtn)   endBtn.style.display = 'block';

        // Enable and focus input
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.disabled = false;
            input.focus();
        }

        // Update UI state
        const wrap = document.getElementById('barcodeInputWrap');
        if (wrap) wrap.classList.add('active');

        const card = document.querySelector('.scanner-card');
        if (card) card.classList.add('scanning');

        const badge = document.getElementById('scannerStatusBadge');
        if (badge) badge.classList.add('active');

        const badgeText = document.getElementById('statusBadgeText');
        if (badgeText) badgeText.textContent = 'SCANNING';

        const dot = document.getElementById('statusDotAnim');
        if (dot) dot.classList.add('pulse');

        const statusText = document.getElementById('scannerStatusText');
        if (statusText) statusText.textContent = 'Scanner active — scan or type a barcode';

        this.showToast('success', 'Scanner activated! Ready to scan barcodes.');
    }

    endScanning() {
        console.log('🛑 END SCANNING');
        this.isScanning = false;
        clearTimeout(this.scanTimeout);

        // Toggle buttons
        const beginBtn = document.getElementById('beginScanBtn');
        const endBtn   = document.getElementById('endScanBtn');
        if (beginBtn) beginBtn.style.display = 'block';
        if (endBtn)   endBtn.style.display = 'none';

        // Disable input
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.disabled = true;
            input.value = '';
            input.blur();
        }

        // Reset UI state
        const wrap = document.getElementById('barcodeInputWrap');
        if (wrap) wrap.classList.remove('active');

        const card = document.querySelector('.scanner-card');
        if (card) card.classList.remove('scanning');

        const badge = document.getElementById('scannerStatusBadge');
        if (badge) badge.classList.remove('active');

        const badgeText = document.getElementById('statusBadgeText');
        if (badgeText) badgeText.textContent = 'IDLE';

        const dot = document.getElementById('statusDotAnim');
        if (dot) dot.classList.remove('pulse');

        const statusText = document.getElementById('scannerStatusText');
        if (statusText) statusText.textContent = 'Press BEGIN SCAN to activate';

        this.showToast('info', 'Scanner deactivated.');
    }

    async scanBarcode(barcode) {
        if (!this.isScanning) {
            this.showToast('warning', 'Please click "BEGIN SCAN" first');
            return;
        }
        if (!barcode || barcode.trim() === '') return;

        this.isProcessing = true;

        try {
            console.log('📡 Scanning barcode:', barcode);

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('❌ CSRF token not found!');
                this.showToast('error', 'CSRF token missing. Please refresh the page.');
                this.isProcessing = false;
                return;
            }

            const response = await fetch(`/purchase-order/${this.poId}/scan-item`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ barcode: barcode.trim() })
            });

            const data = await response.json();
            console.log('📡 Response:', data);

            if (data.success) {
                this.addScannedItem(data.product);
                this.updatePOItemProgress(data.product);
                this.playSuccessSound();
                this.flashScan('success');
                this.showToast('success', `✓ ${data.product.name} scanned! (${data.product.quantity_scanned}/${data.product.quantity_ordered})`);
            } else {
                this.playErrorSound();
                this.flashScan('error');
                this.showToast('error', data.message || 'Scan failed');
            }
        } catch (error) {
            console.error('❌ Scan error:', error);
            this.playErrorSound();
            this.flashScan('error');
            this.showToast('error', 'Network error. Please try again.');
        } finally {
            this.isProcessing = false;
        }
    }

    addScannedItem(product) {
        const tbody = document.getElementById('scannedItemsBody');
        if (!tbody) return;

        // Remove empty state row
        const emptyRow = tbody.querySelector('.empty-state-row');
        if (emptyRow) emptyRow.remove();

        let existingRow = tbody.querySelector(`tr[data-product-id="${product.id}"]`);

        if (existingRow) {
            // Update quantity and total for existing row
            const qtyCell   = existingRow.querySelector('.qty-cell');
            const totalCell = existingRow.querySelector('.total-cell');

            const currentQty = parseInt(qtyCell.textContent) || 0;
            const newQty     = currentQty + 1;
            const newTotal   = newQty * parseFloat(product.unit_cost);

            qtyCell.textContent   = newQty;
            totalCell.textContent = `₱${newTotal.toFixed(2)}`;

            // Flash highlight
            existingRow.style.background = 'rgba(34,197,94,0.15)';
            setTimeout(() => { existingRow.style.background = ''; }, 600);

        } else {
            // New row
            const row = document.createElement('tr');
            row.setAttribute('data-product-id', product.id);
            row.classList.add('new-row-anim');
            row.innerHTML = `
                <td><code>${product.barcode || '—'}</code></td>
                <td><strong>${product.name}</strong></td>
                <td class="text-center"><span class="qty-cell"
                    style="background:rgba(61,142,248,0.15);color:#3d8ef8;font-family:'IBM Plex Mono',monospace;
                           font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;">1</span></td>
                <td class="text-right total-cell"
                    style="font-family:'IBM Plex Mono',monospace;font-size:12px;color:#7c8499;">
                    ₱${parseFloat(product.unit_cost).toFixed(2)}
                </td>
            `;
            tbody.insertBefore(row, tbody.firstChild);
        }

        this.totalScanned++;
        this.updateGrandTotal();
        this.updateStats();
        this.updateScannedCount();
    }

    updatePOItemProgress(product) {
        const progress   = parseFloat(product.progress) || 0;
        const scanned    = parseInt(product.quantity_scanned) || 0;
        const ordered    = parseInt(product.quantity_ordered) || 0;
        const productId  = product.id;

        const progressBar = document.getElementById(`progress-${productId}`);
        const countLabel  = document.getElementById(`pcount-${productId}`);
        const pctLabel    = document.getElementById(`ppct-${productId}`);
        const statusIcon  = document.getElementById(`icon-${productId}`);
        const row         = document.querySelector(`#poItemsBody tr[data-product-id="${productId}"]`);

        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
        if (countLabel) {
            countLabel.textContent = `${scanned}/${ordered}`;
        }
        if (pctLabel) {
            pctLabel.textContent = `${Math.round(progress)}%`;
        }

        if (progress >= 100) {
            if (progressBar) progressBar.classList.add('done');
            if (statusIcon) {
                statusIcon.classList.remove('pending');
                statusIcon.classList.add('completed');
                statusIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
            }
            if (row) row.classList.add('completed-row');
        }
    }

    updateGrandTotal() {
        const rows = document.querySelectorAll('#scannedItemsBody tr[data-product-id]');
        let total = 0;

        rows.forEach(row => {
            const totalText = row.querySelector('.total-cell')?.textContent || '0';
            const amount = parseFloat(totalText.replace('₱', '').replace(/,/g, '')) || 0;
            total += amount;
        });

        const el = document.getElementById('scanGrandTotal');
        if (el) el.textContent = `₱${total.toFixed(2)}`;
    }

    updateStats() {
        const scannedEl  = document.getElementById('totalScanned');
        const orderedEl  = document.getElementById('totalOrderedDisplay');
        const progressEl = document.getElementById('scanProgress');
        const pctEl      = document.getElementById('progressPercent');
        const barEl      = document.getElementById('overallProgressBar');

        if (scannedEl) scannedEl.textContent = this.totalScanned;
        if (orderedEl) orderedEl.textContent = this.totalOrdered;

        const pct = this.totalOrdered > 0
            ? Math.round((this.totalScanned / this.totalOrdered) * 100)
            : 0;

        if (progressEl) progressEl.textContent = `${pct}%`;
        if (pctEl)      pctEl.textContent = `${pct}%`;
        if (barEl)      barEl.style.width = `${pct}%`;
    }

    updateScannedCount() {
        const count  = document.querySelectorAll('#scannedItemsBody tr[data-product-id]').length;
        const badge  = document.getElementById('scannedCount');
        if (badge) badge.textContent = `${count} item${count !== 1 ? 's' : ''}`;
    }

    async completeScanning() {
        if (this.totalScanned === 0) {
            this.showToast('warning', 'No items have been scanned yet.');
            return;
        }

        if (!confirm('Complete scanning? This will finalize the purchase order.')) return;

        try {
            const response = await fetch(`/purchase-order/${this.poId}/complete-scan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', '✅ Scanning completed! Redirecting...');
                setTimeout(() => { window.location.href = '/purchase-order'; }, 1500);
            } else {
                this.showToast('error', data.message || 'Could not complete scan');
            }
        } catch (error) {
            console.error('Complete scan error:', error);
            this.showToast('error', 'Network error. Please try again.');
        }
    }

    flashScan(type) {
        const overlay = document.getElementById('scanFlash');
        if (!overlay) return;

        overlay.style.display = 'block';
        overlay.classList.remove('flash-success', 'flash-error');
        void overlay.offsetWidth; // force reflow
        overlay.classList.add(type === 'success' ? 'flash-success' : 'flash-error');

        setTimeout(() => {
            overlay.style.display = 'none';
            overlay.classList.remove('flash-success', 'flash-error');
        }, 350);
    }

    showToast(type, message) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const icons = {
            success: 'fa-check-circle',
            error:   'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info:    'fa-info-circle',
        };

        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || 'fa-info-circle'} toast-icon"></i>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity    = '0';
            toast.style.transform  = 'translateX(20px)';
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    playSuccessSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.25);
        } catch (e) { /* silent */ }
    }

    playErrorSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(200, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.2);
        } catch (e) { /* silent */ }
    }
}

// ✅ INITIALIZE
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('scanContainer');
    const poId = container?.dataset.poId;

    if (poId) {
        console.log('🎯 Scanner ready for PO:', poId);
        window.scannerManager = new BarcodeScannerManager(poId);
    } else {
        console.error('❌ PO ID not found in #scanContainer[data-po-id]');
    }
});