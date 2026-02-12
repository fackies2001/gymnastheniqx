// class BarcodeScannerManager {
    /*
    constructor(poId) {
        this.poId = poId;
        this.scannedItems = [];
        this.isScanning = false;
        this.totalOrdered = 0;
        this.totalScanned = 0;
        this.scanBuffer = '';
        this.scanTimeout = null;
        this.lastInputTime = 0;
        this.init();
    }

    init() {    
        console.log('🔍 Initializing Barcode Scanner...', { poId: this.poId });
        this.setupEventListeners();
        this.loadPOItems();
        this.calculateTotals();
    }

    setupEventListeners() {
        console.log('🔧 Setting up event listeners...');
        
        // ✅ BEGIN SCAN BUTTON
        const beginBtn = document.getElementById('beginScanBtn');
        if (beginBtn) {
            beginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.beginScanning();
            });
        }

        // ✅ END SCAN BUTTON
        const endBtn = document.getElementById('endScanBtn');
        if (endBtn) {
            endBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.endScanning();
            });
        }

        // ✅ BARCODE INPUT - Support both manual typing and scanner gun
        const barcodeInput = document.getElementById('barcodeInput');
        if (barcodeInput) {
            // For manual entry (keyboard) - Press ENTER
            barcodeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const barcode = barcodeInput.value.trim();
                    if (barcode && barcode.length >= 8) {
                        console.log('⌨️ Manual entry:', barcode);
                        this.scanBarcode(barcode);
                        barcodeInput.value = '';
                    } else if (barcode.length > 0) {
                        this.showToast('warning', 'Please enter a complete barcode (minimum 8 characters)');
                    }
                }
            });

            // For barcode scanner gun (fast input detection)
            barcodeInput.addEventListener('input', (e) => {
                if (!this.isScanning) return;
                
                const currentTime = Date.now();
                const timeDiff = currentTime - this.lastInputTime;
                this.lastInputTime = currentTime;
                
                clearTimeout(this.scanTimeout);
                
                // ✅ Detect scanner gun: very fast typing (< 50ms between characters)
                const isScannerGun = timeDiff < 50 && barcodeInput.value.length > 1;
                
                if (isScannerGun) {
                    console.log('🔫 Scanner gun detected!');
                    // Wait for scanner to finish (100ms)
                    this.scanTimeout = setTimeout(() => {
                        const barcode = barcodeInput.value.trim();
                        if (barcode.length >= 8) {
                            console.log('🔫 Scanner gun scan:', barcode);
                            this.scanBarcode(barcode);
                            barcodeInput.value = '';
                        }
                    }, 250);
                } else {
                    // Manual typing - wait longer (500ms)
                    this.scanTimeout = setTimeout(() => {
                        const barcode = barcodeInput.value.trim();
                        // Don't auto-trigger for manual typing, wait for ENTER key
                        if (barcode.length >= 13) {
                            // Only if really long barcode is typed manually
                            console.log('⌨️ Long manual entry detected:', barcode);
                        }
                    }, 500);
                }
            });
        }

        // ✅ COMPLETE SCANNING BUTTON
        const completeBtn = document.getElementById('completeScanBtn');
        if (completeBtn) {
            completeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.completeScanning();
            });
        }

        // ✅ CANCEL BUTTON
        const cancelBtn = document.getElementById('cancelScanBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Return to purchase orders? Any unsaved progress will be lost.')) {
                    window.location.href = '/purchase-order';
                }
            });
        }

        console.log('✅ Event listeners setup complete');
    }

    async loadPOItems() {
        try {
            console.log('📥 Loading PO items...');
            const response = await fetch(`/purchase-order/${this.poId}`);
            const data = await response.json();
            
            if (data) {
                console.log('✅ PO data loaded:', data);
                this.displayPOItems(data.items || []);
            }
        } catch (error) {
            console.error('❌ Error loading PO items:', error);
            this.showToast('error', 'Error loading purchase order items');
        }
    }

    displayPOItems(items) {
        console.log('📋 Displaying PO items:', items);
        const tbody = document.getElementById('poItemsBody');
        if (!tbody || !items.length) return;

        this.totalOrdered = items.reduce((sum, item) => sum + (item.quantity_ordered || 0), 0);
        console.log('📊 Total ordered:', this.totalOrdered);
        this.updateStats();
    }

    calculateTotals() {
        const rows = document.querySelectorAll('#poItemsBody tr[data-quantity-ordered]');
        this.totalOrdered = 0;
        
        rows.forEach(row => {
            const qty = parseInt(row.dataset.quantityOrdered) || 0;
            this.totalOrdered += qty;
        });
        
        console.log('📊 Calculated totals:', { totalOrdered: this.totalOrdered });
        this.updateStats();
    }

    beginScanning() {
        console.log('🎬 BEGIN SCANNING!');
        this.isScanning = true;
        
        // Toggle buttons
        const beginBtn = document.getElementById('beginScanBtn');
        const endBtn = document.getElementById('endScanBtn');
        
        if (beginBtn) beginBtn.style.display = 'none';
        if (endBtn) endBtn.style.display = 'inline-block';
        
        // Enable and focus input
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.disabled = false;
            input.focus();
            console.log('✅ Input enabled and focused');
        }
        
        // Update status
        const statusEl = document.getElementById('scanStatus');
        if (statusEl) {
            statusEl.textContent = 'Scanning Active';
            statusEl.parentElement.parentElement.classList.remove('alert-warning');
            statusEl.parentElement.parentElement.classList.add('alert-success');
        }
        
        // Show status indicator
        const statusDot = document.getElementById('statusDot');
        if (statusDot) {
            statusDot.classList.add('active');
        }
        
        // Show alert
        const alert = document.getElementById('scanStatusAlert');
        if (alert) {
            alert.style.display = 'block';
        }
        
        this.showToast('success', '✅ Scanner activated! Start scanning barcodes.');
    }

    endScanning() {
        console.log('🛑 END SCANNING!');
        this.isScanning = false;
        
        // Toggle buttons
        const beginBtn = document.getElementById('beginScanBtn');
        const endBtn = document.getElementById('endScanBtn');
        
        if (beginBtn) beginBtn.style.display = 'inline-block';
        if (endBtn) endBtn.style.display = 'none';
        
        // Disable input
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.disabled = true;
            input.value = '';
        }
        
        // Update status
        const statusEl = document.getElementById('scanStatus');
        if (statusEl) {
            statusEl.textContent = 'Ready to Scan';
            statusEl.parentElement.parentElement.classList.remove('alert-success');
            statusEl.parentElement.parentElement.classList.add('alert-warning');
        }
        
        // Hide status indicator
        const statusDot = document.getElementById('statusDot');
        if (statusDot) {
            statusDot.classList.remove('active');
        }
        
        this.showToast('info', 'Scanner deactivated');
    }

    async scanBarcode(barcode) {
        console.log('🔍 Scanning:', { barcode, isScanning: this.isScanning });
        
        if (!this.isScanning) {
            this.showToast('warning', 'Please click "BEGIN SCAN" first');
            return;
        }

        if (!barcode || barcode.trim() === '') {
            return;
        }

        try {
            console.log('📡 Sending scan request...');
            const response = await fetch(`/purchase-order/${this.poId}/scan-item`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ barcode: barcode.trim() })
            });

            const data = await response.json();
            console.log('📡 Scan response:', data);
            
            if (data.success) {
                console.log('✅ Scan successful!');
                this.addScannedItem(data.product);
                this.updateProgress(data.product);
                this.playSuccessSound();
                this.showToast('success', `✓ ${data.product.name} scanned!`);
                this.flashScanSuccess();
            } else {
                console.error('❌ Scan failed:', data.message);
                this.playErrorSound();
                this.showToast('error', data.message);
            }
        } catch (error) {
            console.error('❌ Error scanning barcode:', error);
            this.playErrorSound();
            this.showToast('error', 'Error scanning item. Please try again.');
        }
    }

    addScannedItem(product) {
        console.log('➕ Adding scanned item:', product);
        const tbody = document.getElementById('scannedItemsBody');
        
        // Remove empty state
        const emptyState = tbody.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }
        
        // Check if product already in list
        let existingRow = tbody.querySelector(`tr[data-product-id="${product.id}"]`);
        
        if (existingRow) {
            // Update quantity
            const qtyCell = existingRow.querySelector('.qty-cell');
            const totalCell = existingRow.querySelector('.total-cell');
            const currentQty = parseInt(qtyCell.textContent);
            const newQty = currentQty + 1;
            const total = newQty * product.unit_cost;
            
            qtyCell.textContent = newQty;
            totalCell.textContent = `₱${total.toFixed(2)}`;
            
            // Highlight
            existingRow.classList.add('table-success');
            setTimeout(() => existingRow.classList.remove('table-success'), 1000);
        } else {
            // Add new row
            const row = document.createElement('tr');
            row.dataset.productId = product.id;
            row.style.opacity = '0';
            row.innerHTML = `
                <td><code class="text-primary">${product.barcode}</code></td>
                <td><strong>${product.name}</strong></td>
                <td class="text-center"><span class="badge badge-primary qty-cell">1</span></td>
                <td class="text-right font-weight-bold total-cell">₱${parseFloat(product.unit_cost).toFixed(2)}</td>
            `;
            tbody.insertBefore(row, tbody.firstChild);
            
            // Fade in animation
            setTimeout(() => {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '1';
            }, 10);
        }
        
        this.totalScanned++;
        this.updateGrandTotal();
        this.updateStats();
        this.updateScannedCount();
    }

    updateProgress(product) {
        const row = document.querySelector(`#poItemsBody tr[data-product-id="${product.id}"]`);
        if (!row) return;
        
        const progressBar = row.querySelector('.progress-bar');
        if (!progressBar) return;
        
        const progress = product.progress || 0;
        
        progressBar.style.width = `${progress}%`;
        const progressText = progressBar.querySelector('.progress-text');
        if (progressText) {
            progressText.textContent = `${product.quantity_scanned}/${product.quantity_ordered}`;
        }
        
        // Update percentage
        const progressDesc = progressBar.parentElement.nextElementSibling;
        if (progressDesc) {
            progressDesc.textContent = `${Math.round(progress)}% complete`;
        }
        
        // Change color if complete
        if (progress === 100) {
            progressBar.classList.remove('bg-primary', 'progress-bar-animated');
            progressBar.classList.add('bg-success');
            row.classList.add('completed');
            
            const icon = row.querySelector('i');
            if (icon) {
                icon.classList.remove('far', 'fa-circle', 'text-muted');
                icon.classList.add('fas', 'fa-check-circle', 'text-success');
            }
        }
    }

    updateGrandTotal() {
        const rows = document.querySelectorAll('#scannedItemsBody tr[data-product-id]');
        let total = 0;
        
        rows.forEach(row => {
            const totalText = row.querySelector('.total-cell').textContent;
            const subtotal = parseFloat(totalText.replace('₱', '').replace(',', ''));
            total += subtotal;
        });
        
        const grandTotalEl = document.getElementById('scanGrandTotal');
        if (grandTotalEl) {
            grandTotalEl.textContent = `₱${total.toFixed(2)}`;
        }
    }

    updateStats() {
        const scannedEl = document.getElementById('totalScanned');
        const orderedEl = document.getElementById('totalOrderedDisplay');
        const progressEl = document.getElementById('scanProgress');
        
        if (scannedEl) scannedEl.textContent = this.totalScanned;
        if (orderedEl) orderedEl.textContent = this.totalOrdered;
        
        const progress = this.totalOrdered > 0 
            ? Math.round((this.totalScanned / this.totalOrdered) * 100) 
            : 0;
        
        if (progressEl) progressEl.textContent = `${progress}%`;
    }

    updateScannedCount() {
        const count = document.querySelectorAll('#scannedItemsBody tr[data-product-id]').length;
        const badge = document.getElementById('scannedCount');
        if (badge) {
            badge.textContent = `${count} item${count !== 1 ? 's' : ''}`;
        }
    }

    async completeScanning() {
        if (!confirm('Complete scanning? This will finalize the purchase order and create serialized products.')) {
            return;
        }

        try {
            const response = await fetch(`/purchase-order/${this.poId}/complete-scan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showToast('success', 'Scanning completed successfully! Redirecting...');
                setTimeout(() => {
                    window.location.href = '/purchase-order';
                }, 1500);
            } else {
                this.showToast('error', data.message);
            }
        } catch (error) {
            console.error('Error completing scan:', error);
            this.showToast('error', 'Error completing scanning. Please try again.');
        }
    }

    flashScanSuccess() {
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.style.backgroundColor = '#d4edda';
            setTimeout(() => {
                input.style.backgroundColor = '';
            }, 300);
        }
    }

    showToast(type, message) {
        const typeMap = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const toast = document.createElement('div');
        toast.className = `alert ${typeMap[type]} position-fixed shadow-lg`;
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
            ${message}
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    playSuccessSound() {
        try {
            const beep = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBStxy/LcfjMGH2S36ueXTQwNU6Xj8LdlHAdBnNryw3kvBSh+zPHaizsIGm276OGVUAwLUaLk8LVoHgg+ktfzzHwwBSh6y/LZiToIGGm57eWaTAwNUKLl8LJrHgg9ldfzzH0yBSh5zPDYizkIGWm77OWaTQwNU6Li8rVpHQc+ltjzw30yBSl3y/LaiToIG2q87eOaTgwNU6Ph8LNoHQg8ktjzw38xBSp3y/LXizsIGWm67eWaTgwNU6Li8LRqHgc8ktjzw38xBSp2y/DXizsIGWq87OSbTgwNU6Ph8LRqHQc9ktfzw38xBSl3y/DXizsIGWm77OSbTgwNUqPh8LNqHQc8ktjzw38xBSp3y/DYizsIGWm77OWaTgwNU6Ph8LNoHQc+ltfzw38xBSl3y/DXizsIGWq87eOaTQwNUqPh8LNqHQc8ktjzw38xBSp3y/DXizsIGWm77eWaTgwNU6Ph8LNoHQc9ktfzw38xBSl4y/DXizsIGWm77OSbTgwNU6Ph8LNqHQc9ktfzw38xBSl3y/DXizsIGWm77OSbTgwNU6Ph8LNqHQc9ktfzw38xBSl3y/DXiz');
            beep.play().catch(() => {});
        } catch (e) {
            // Silent fail
        }
    }

    playErrorSound() {
        try {
            const beep = new Audio('data:audio/wav;base64,UklGRhwCAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YfgBAAD/AP///wAA//8AAAEA//8BAAAA/wD//wEAAAD/AAEA/wAAAP//AAABAP///////wAAAQD//wEA/wD+/wEAAQAAAAEA/v8AAP//AAABAP7/AAD//wAAAAEA/v8AAAEA/v////8BAP7/AAD//wAAAQD+/wAAAQD+////AAEA/v8AAAEA/v////8BAP7/AAABAP7////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3////+AAEA/v8AAAEA/v////8AAP7/AAABAP3/');
            beep.play().catch(() => {});
        } catch (e) {
            // Silent fail
        }
    }
}

// ✅ INITIALIZE SCANNER
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ DOM ready - Initializing scanner...');
    
    const container = document.getElementById('scanContainer');
    const poId = container?.dataset.poId;
    
    if (poId) {
        console.log('🎯 Creating scanner instance for PO:', poId);
        window.scannerManager = new BarcodeScannerManager(poId);
    } else {
        console.error('❌ PO ID not found in scanContainer!');
    }
});

feb 12