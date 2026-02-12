(function() {
    'use strict';

    console.log('🎯 Dashboard.js loaded!');

    /* ========================================
       ✅ HELPER FUNCTIONS
       ======================================== */

    // const getLabels = (data) => {
    //     if (!data) {
    //         console.warn('⚠️ No data provided to getLabels');
    //         return [];
    //     }
        
    //     if (Array.isArray(data)) {
    //         console.log('📊 Data is array:', data);
    //         return data.map(item => item.name || item.product_status_name || 'Unknown');
    //     }
        
    //     console.log('📊 Data is object:', data);
    //     return Object.keys(data);
    // };

    // const getData = (data) => {
    //     if (!data) {
    //         console.warn('⚠️ No data provided to getData');
    //         return [];
    //     }
        
    //     if (Array.isArray(data)) {
    //         return data.map(item => parseInt(item.total) || 0);
    //     }
        
    //     return Object.values(data).map(val => parseInt(val) || 0);
    // };

    /* ========================================
       1️⃣ PURCHASE REQUEST STATUS CHART (Doughnut)
       ======================================== */

    // console.log('🔍 Checking Purchase Request Chart...');
    // const el1 = document.getElementById('monthlyProductReceived');
    
    // if (!el1) {
    //     console.error('❌ Element #monthlyProductReceived not found!');
    // } else {
    //     console.log('✅ Element found:', el1);
    // }

    // if (!window.purchase_request_status_counts) {
    //     console.error('❌ window.purchase_request_status_counts is undefined!');
    // } else {
    //     console.log('✅ Purchase Request Data:', window.purchase_request_status_counts);
    // }
    
    // if (el1 && window.purchase_request_status_counts) {
    //     const labels = getLabels(window.purchase_request_status_counts);
    //     const chartData = getData(window.purchase_request_status_counts);
        
    //     console.log('📈 Purchase Request Chart Data:');
    //     console.log('   Labels:', labels);
    //     console.log('   Data:', chartData);
        
    //     if (labels.length === 0 || chartData.length === 0) {
    //         console.error('❌ No data for Purchase Request chart!');
    //         el1.parentElement.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
    //     } else {
    //         try {
    //             new Chart(el1.getContext('2d'), {
    //                 type: 'doughnut',
    //                 data: {
    //                     labels: labels,
    //                     datasets: [{
    //                         data: chartData,
    //                         backgroundColor: [
    //                             '#FF6384', // Red
    //                             '#36A2EB', // Blue
    //                             '#FFCD56', // Yellow
    //                             '#4BC0C0', // Teal
    //                             '#9966FF', // Purple
    //                             '#FF9F40'  // Orange
    //                         ],
    //                     }]
    //                 },
    //                 options: { 
    //                     responsive: true, 
    //                     maintainAspectRatio: false,
    //                     plugins: {
    //                         legend: {
    //                             display: true,
    //                             position: 'right'
    //                         }
    //                     }
    //                 }
    //             });
    //             console.log('✅ Purchase Request chart created successfully!');
    //         } catch (error) {
    //             console.error('❌ Error creating Purchase Request chart:', error);
    //         }
    //     }
    // }

    /* ========================================
       2️⃣ PRODUCT STATUS CHART (Doughnut)
       ======================================== */

    // console.log('🔍 Checking Product Status Chart...');
    // const el2 = document.getElementById('monthlyProductRelease');
    
    // if (!el2) {
    //     console.error('❌ Element #monthlyProductRelease not found!');
    // } else {
    //     console.log('✅ Element found:', el2);
    // }

    // if (!window.product_status_counts) {
    //     console.error('❌ window.product_status_counts is undefined!');
    // } else {
    //     console.log('✅ Product Status Data:', window.product_status_counts);
    // }

    // if (el2 && window.product_status_counts) {
    //     const labels = getLabels(window.product_status_counts);
    //     const chartData = getData(window.product_status_counts);
        
    //     console.log('📈 Product Status Chart Data:');
    //     console.log('   Labels:', labels);
    //     console.log('   Data:', chartData);
        
    //     if (labels.length === 0 || chartData.length === 0) {
    //         console.error('❌ No data for Product Status chart!');
    //         el2.parentElement.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
    //     } else {
    //         try {
    //             new Chart(el2.getContext('2d'), {
    //                 type: 'doughnut',
    //                 data: {
    //                     labels: labels,
    //                     datasets: [{
    //                         data: chartData,
    //                         backgroundColor: [
    //                             '#4BC0C0', // Teal
    //                             '#FF6384', // Red
    //                             '#FFCD56', // Yellow
    //                             '#C9CBCF', // Gray
    //                             '#36A2EB'  // Blue
    //                         ],
    //                     }]
    //                 },
    //                 options: { 
    //                     responsive: true, 
    //                     maintainAspectRatio: false,
    //                     plugins: {
    //                         legend: {
    //                             display: true,
    //                             position: 'right'
    //                         }
    //                     }
    //                 }
    //             });
    //             console.log('✅ Product Status chart created successfully!');
    //         } catch (error) {
    //             console.error('❌ Error creating Product Status chart:', error);
    //         }
    //     }
    // }

    /* ========================================
       3️⃣ MONTHLY PRODUCTS SCANNED CHART (Bar)
       ======================================== */

    console.log('🔍 Checking Monthly Products Chart...');
    const el3 = document.getElementById('MonthlyProductsScanned');
    
    if (!el3) {
        console.error('❌ Element #MonthlyProductsScanned not found!');
    } else {
        console.log('✅ Element found:', el3);
    }

    if (!window.monthly_products_in) {
        console.error('❌ window.monthly_products_in is undefined!');
    } else {
        console.log('✅ Monthly Products Data:', window.monthly_products_in);
    }

    if (el3 && window.monthly_products_in) {
        const chartData = Array.isArray(window.monthly_products_in) 
            ? window.monthly_products_in.map(val => parseInt(val) || 0)
            : Object.values(window.monthly_products_in).map(val => parseInt(val) || 0);
        
        console.log('📈 Monthly Products Chart Data:', chartData);
        
        if (chartData.length === 0) {
            console.error('❌ No data for Monthly Products chart!');
            el3.parentElement.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
        } else {
            try {
                new Chart(el3.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Products Scanned-in',
                            backgroundColor: '#36A2EB',
                            borderColor: '#2E8BC0',
                            borderWidth: 1,
                            data: chartData
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
                console.log('✅ Monthly Products chart created successfully!');
            } catch (error) {
                console.error('❌ Error creating Monthly Products chart:', error);
            }
        }
    }

    console.log('✅ Dashboard.js initialization complete!');
})();

feb 11