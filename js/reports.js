// reports.js - Handle all reporting and data analysis functionality
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const reportSelector = document.getElementById('report-type');
    const dateRangeStart = document.getElementById('date-range-start');
    const dateRangeEnd = document.getElementById('date-range-end');
    const propertyFilter = document.getElementById('property-filter');
    const generateReportBtn = document.getElementById('generate-report');
    const reportContainer = document.getElementById('report-container');
    const reportChartCanvas = document.getElementById('report-chart');
    
    // Set default date range (last 30 days)
    if (dateRangeStart && dateRangeEnd) {
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        dateRangeEnd.value = formatDateForInput(today);
        dateRangeStart.value = formatDateForInput(thirtyDaysAgo);
    }
    
    // Populate property filter if it exists
    if (propertyFilter && window.propertyUtils) {
        const properties = window.propertyUtils.getProperties();
        propertyFilter.innerHTML = '<option value="all">All Properties</option>';
        
        properties.forEach(property => {
            const option = document.createElement('option');
            option.value = property.id;
            option.textContent = property.name;
            propertyFilter.appendChild(option);
        });
    }
    
    // Event listener for report generation
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            const reportType = reportSelector.value;
            const startDate = dateRangeStart.value;
            const endDate = dateRangeEnd.value;
            const propertyId = propertyFilter.value === 'all' ? null : parseInt(propertyFilter.value);
            
            generateReport(reportType, startDate, endDate, propertyId);
        });
    }
    
    // Generate report based on selected options
    function generateReport(reportType, startDate, endDate, propertyId) {
        if (!reportContainer) return;
        
        // Convert dates to Date objects for comparison
        const start = new Date(startDate);
        const end = new Date(endDate);
        end.setHours(23, 59, 59); // Include the entire end day
        
        // Get data
        const payments = window.paymentUtils ? window.paymentUtils.getPayments() : [];
        const properties = window.propertyUtils ? window.propertyUtils.getProperties() : [];
        const tenants = window.tenantUtils ? window.tenantUtils.getTenants() : [];
        
        // Filter data by date range and property (if specified)
        const filteredPayments = payments.filter(payment => {
            const paymentDate = new Date(payment.date);
            const matchesDateRange = paymentDate >= start && paymentDate <= end;
            const matchesProperty = propertyId ? payment.propertyId === propertyId : true;
            return matchesDateRange && matchesProperty;
        });
        
        // Generate the appropriate report
        switch (reportType) {
            case 'income':
                generateIncomeReport(filteredPayments, start, end, propertyId, properties);
                break;
            case 'occupancy':
                generateOccupancyReport(properties, tenants, propertyId);
                break;
            case 'tenant-status':
                generateTenantStatusReport(tenants, propertyId, properties);
                break;
            case 'payment-status':
                generatePaymentStatusReport(filteredPayments, tenants, properties);
                break;
            case 'property-performance':
                generatePropertyPerformanceReport(filteredPayments, properties, start, end);
                break;
            default:
                reportContainer.innerHTML = '<div class="alert alert-warning">Please select a report type</div>';
        }
    }
    
    // Generate income report
    function generateIncomeReport(payments, start, end, propertyId, properties) {
        // Calculate total income
        const totalIncome = payments
            .filter(p => p.status === 'Paid')
            .reduce((sum, payment) => sum + payment.amount, 0);
        
        // Calculate income by property
        const incomeByProperty = {};
        payments.filter(p => p.status === 'Paid').forEach(payment => {
            if (!incomeByProperty[payment.propertyId]) {
                incomeByProperty[payment.propertyId] = 0;
            }
            incomeByProperty[payment.propertyId] += payment.amount;
        });
        
        // Calculate income by month
        const incomeByMonth = {};
        payments.filter(p => p.status === 'Paid').forEach(payment => {
            const date = new Date(payment.date);
            const monthYear = `${date.getMonth() + 1}/${date.getFullYear()}`;
            
            if (!incomeByMonth[monthYear]) {
                incomeByMonth[monthYear] = 0;
            }
            incomeByMonth[monthYear] += payment.amount;
        });
        
        // Sort months chronologically
        const sortedMonths = Object.keys(incomeByMonth).sort((a, b) => {
            const [monthA, yearA] = a.split('/').map(Number);
            const [monthB, yearB] = b.split('/').map(Number);
            
            return yearA === yearB ? monthA - monthB : yearA - yearB;
        });
        
        // Prepare data for chart
        const chartLabels = sortedMonths.map(monthYear => {
            const [month, year] = monthYear.split('/');
            return `${getMonthName(parseInt(month) - 1)} ${year}`;
        });
        
        const chartData = sortedMonths.map(monthYear => incomeByMonth[monthYear]);
        
        // Generate the report HTML
        let reportHTML = `
            <h2>Income Report</h2>
            <p>Period: ${formatDate(start)} - ${formatDate(end)}</p>
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Income</h3>
                    <p class="amount">$${totalIncome.toFixed(2)}</p>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="income-chart"></canvas>
            </div>
            
            <h3>Income by Property</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Income</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        // Add rows for each property
        Object.keys(incomeByProperty).forEach(propId => {
            const property = properties.find(p => p.id === parseInt(propId)) || { name: 'Unknown' };
            const propertyIncome = incomeByProperty[propId];
            const percentage = (propertyIncome / totalIncome * 100).toFixed(1);
            
            reportHTML += `
                <tr>
                    <td>${property.name}</td>
                    <td>$${propertyIncome.toFixed(2)}</td>
                    <td>${percentage}%</td>
                </tr>
            `;
        });
        
        reportHTML += `
                </tbody>
            </table>
        `;
        
        reportContainer.innerHTML = reportHTML;
        
        // Create chart
        if (document.getElementById('income-chart')) {
            const ctx = document.getElementById('income-chart').getContext('2d');
            
            // Destroy previous chart if it exists
            if (window.incomeChart) {
                window.incomeChart.destroy();
            }
            
            window.incomeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Monthly Income',
                        data: chartData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Generate occupancy report
    function generateOccupancyReport(properties, tenants, propertyId) {
        // Filter properties if a specific one is selected
        const relevantProperties = propertyId ? 
            properties.filter(p => p.id === propertyId) : 
            properties;
        
        // Calculate occupancy stats
        let totalUnits = 0;
        let occupiedUnits = 0;
        const occupancyByProperty = [];
        
        relevantProperties.forEach(property => {
            const propertyTenants = tenants.filter(t => t.propertyId === property.id && t.status === 'Active');
            const occupied = propertyTenants.length;
            const total = property.units;
            const occupancyRate = total > 0 ? (occupied / total * 100).toFixed(1) : 0;
            
            occupancyByProperty.push({
                property: property,
                occupied: occupied,
                total: total,
                rate: occupancyRate
            });
            
            totalUnits += total;
            occupiedUnits += occupied;
        });
        
        const overallOccupancyRate = totalUnits > 0 ? 
            (occupiedUnits / totalUnits * 100).toFixed(1) : 0;
        
        // Generate the report HTML
        let reportHTML = `
            <h2>Occupancy Report</h2>
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Overall Occupancy Rate</h3>
                    <p class="amount">${overallOccupancyRate}%</p>
                </div>
                <div class="summary-card">
                    <h3>Occupied Units</h3>
                    <p class="amount">${occupiedUnits} / ${totalUnits}</p>
                </div>
                <div class="summary-card">
                    <h3>Vacant Units</h3>
                    <p class="amount">${totalUnits - occupiedUnits}</p>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="occupancy-chart"></canvas>
            </div>
            
            <h3>Occupancy by Property</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Occupied Units</th>
                        <th>Total Units</th>
                        <th>Occupancy Rate</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        // Add rows for each property
        occupancyByProperty.forEach(item => {
            reportHTML += `
                <tr>
                    <td>${item.property.name}</td>
                    <td>${item.occupied}</td>
                    <td>${item.total}</td>
                    <td>${item.rate}%</td>
                </tr>
            `;
        });
        
        reportHTML += `
                </tbody>
            </table>
        `;
        
        reportContainer.innerHTML = reportHTML;
        
        // Create chart
        if (document.getElementById('occupancy-chart')) {
            const ctx = document.getElementById('occupancy-chart').getContext('2d');
            
            // Destroy previous chart if it exists
            if (window.occupancyChart) {
                window.occupancyChart.destroy();
            }
            
            // Prepare data for chart
            const labels = occupancyByProperty.map(item => item.property.name);
            const data = occupancyByProperty.map(item => parseFloat(item.rate));
            
            window.occupancyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Occupancy Rate (%)',
                        data: data,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Generate tenant status report
    function generateTenantStatusReport(tenants, propertyId, properties) {
        // Filter tenants if a specific property is selected
        const relevantTenants = propertyId ? 
            tenants.filter(t => t.propertyId === propertyId) : 
            tenants;
        
        // Count tenants by status
        const statusCounts = {
            'Active': 0,
            'Pending': 0,
            'Past Due': 0,
            'Eviction': 0,
            'Inactive': 0
        };
        
        relevantTenants.forEach(tenant => {
            if (statusCounts[tenant.status] !== undefined) {
                statusCounts[tenant.status]++;
            }
        });
        
        // Calculate lease expirations
        const now = new Date();
        const thirtyDaysLater = new Date();
        thirtyDaysLater.setDate(now.getDate() + 30);
        const ninetyDaysLater = new Date();
        ninetyDaysLater.setDate(now.getDate() + 90);
        
        const expiringIn30Days = relevantTenants.filter(tenant => {
            const leaseEnd = new Date(tenant.leaseEnd);
            return leaseEnd >= now && leaseEnd <= thirtyDaysLater && tenant.status === 'Active';
        });
        
        const expiringIn90Days = relevantTenants.filter(tenant => {
            const leaseEnd = new Date(tenant.leaseEnd);
            return leaseEnd >= thirtyDaysLater && leaseEnd <= ninetyDaysLater && tenant.status === 'Active';
        });
        
        // Generate the report HTML
        let reportHTML = `
            <h2>Tenant Status Report</h2>
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Tenants</h3>
                    <p class="amount">${relevantTenants.length}</p>
                </div>
                <div class="summary-card">
                    <h3>Active Tenants</h3>
                    <p class="amount">${statusCounts['Active']}</p>
                </div>
                <div class="summary-card">
                    <h3>Leases Expiring Soon</h3>
                    <p class="amount">${expiringIn30Days.length}</p>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="tenant-status-chart"></canvas>
            </div>
            
            <h3>Tenant Status Distribution</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        // Add rows for each status
        Object.keys(statusCounts).forEach(status => {
            const count = statusCounts[status];
            const percentage = relevantTenants.length > 0 ? 
                (count / relevantTenants.length * 100).toFixed(1) : 0;
            
            reportHTML += `
                <tr>
                    <td>${status}</td>
                    <td>${count}</td>
                    <td>${percentage}%</td>
                </tr>
            `;
        });
        
        reportHTML += `
                </tbody>
            </table>
            
            <h3>Leases Expiring in Next 30 Days</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Lease End Date</th>
                        <th>Days Remaining</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (expiringIn30Days.length === 0) {
            reportHTML += `<tr><td colspan="4" class="text-center">No leases expiring in the next 30 days</td></tr>`;
        } else {
            expiringIn30Days.forEach(tenant => {
                const property = properties.find(p => p.id === tenant.propertyId) || { name: 'Unknown' };
                const leaseEnd = new Date(tenant.leaseEnd);
                const daysRemaining = Math.ceil((leaseEnd - now) / (1000 * 60 * 60 * 24));
                
                reportHTML += `
                    <tr>
                        <td>${tenant.name}</td>
                        <td>${property.name}</td>
                        <td>${formatDate(leaseEnd)}</td>
                        <td>${daysRemaining}</td>
                    </tr>
                `;
            });
        }
        
        reportHTML += `
                </tbody>
            </table>
        `;
        
        reportContainer.innerHTML = reportHTML;
        
        // Create chart
        if (document.getElementById('tenant-status-chart')) {
            const ctx = document.getElementById('tenant-status-chart').getContext('2d');
            
            // Destroy previous chart if it exists
            if (window.tenantStatusChart) {
                window.tenantStatusChart.destroy();
            }
            
            // Prepare data for chart
            const labels = Object.keys(statusCounts);
            const data = Object.values(statusCounts);
            const backgroundColors = [
                'rgba(75, 192, 192, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(255, 99, 132, 0.5)',
                'rgba(153, 102, 255, 0.5)'
            ];
            
            window.tenantStatusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(color => color.replace('0.5', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
    }
    
    // Generate payment status report
    function generatePaymentStatusReport(payments, tenants, properties) {
        // Count payments by status
        const statusCounts = {
            'Paid': 0,
            'Pending': 0,
            'Late': 0,
            'Partial': 0,
            'Failed': 0
        };
        
        payments.forEach(payment => {
            if (statusCounts[payment.status] !== undefined) {
                statusCounts[payment.status]++;
            }
        });
        
        // Calculate total and average payments
        const totalPaid = payments
            .filter(p => p.status === 'Paid')
            .reduce((sum, payment) => sum + payment.amount, 0);
        
        const avgPayment = payments.length > 0 ? 
            (totalPaid / payments.filter(p => p.status === 'Paid').length).toFixed(2) : 0;
        
        // Find overdue payments (more than 5 days with 'Pending' or 'Late' status)
        const now = new Date();
        const overduePayments = payments.filter(payment => {
            const paymentDate = new Date(payment.date);
            const daysDiff = Math.floor((now - paymentDate) / (1000 * 60 * 60 * 24));
            return (payment.status === 'Pending' || payment.status === 'Late') && daysDiff > 5;
        });
        
        // Generate the report HTML
        let reportHTML = `
            <h2>Payment Status Report</h2>
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Payments</h3>
                    <p class="amount">${payments.length}</p>
                </div>
                <div class="summary-card">
                    <h3>Total Amount Collected</h3>
                    <p class="amount">$${totalPaid.toFixed(2)}</p>
                </div>
                <div class="summary-card">
                    <h3>Average Payment</h3>
                    <p class="amount">$${avgPayment}</p>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="payment-status-chart"></canvas>
            </div>
            
            <h3>Payment Status Distribution</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        // Add rows for each status
        Object.keys(statusCounts).forEach(status => {
            const count = statusCounts[status];
            const percentage = payments.length > 0 ? 
                (count / payments.length * 100).toFixed(1) : 0;
            
            reportHTML += `
                <tr>
                    <td>${status}</td>
                    <td>${count}</td>
                    <td>${percentage}%</td>
                </tr>
            `;
        });
        
        reportHTML += `
                </tbody>
            </table>
            
            <h3>Overdue Payments</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Amount</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (overduePayments.length === 0) {
            reportHTML += `<tr><td colspan="5" class="text-center">No overdue payments found</td></tr>`;
        } else {
            overduePayments.forEach(payment => {
                const tenant = tenants.find(t => t.id === payment.tenantId) || { name: 'Unknown' };
                const property = properties.find(p => p.id === payment.propertyId) || { name: 'Unknown' };
                const paymentDate = new Date(payment.date);
                const daysOverdue = Math.floor((now - paymentDate) / (1000 * 60 * 60 * 24));
                
                reportHTML += `
                    <tr>
                        <td>${formatDate(paymentDate)}</td>
                        <td>${tenant.name}</td>
                        <td>${property.name}</td>
                        <td>$${payment.amount.toFixed(2)}</td>
                        <td>${daysOverdue}</td>
                    </tr>
                `;
            });
        }
        
        reportHTML += `
                </tbody>
            </table>
        `;
        
        reportContainer.innerHTML = reportHTML;
        
        // Create chart
        if (document.getElementById('payment-status-chart')) {
            const ctx = document.getElementById('payment-status-chart').getContext('2d');
            
            // Destroy previous chart if it exists
            if (window.paymentStatusChart) {
                window.paymentStatusChart.destroy();
            }
            
            // Prepare data for chart
            const labels = Object.keys(statusCounts);
            const data = Object.values(statusCounts);
            const backgroundColors = [
                'rgba(75, 192, 192, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(255, 99, 132, 0.5)',
                'rgba(153, 102, 255, 0.5)'
            ];
            
            window.paymentStatusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(color => color.replace('0.5', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
    }
    
    // Generate property performance report
    function generatePropertyPerformanceReport(payments, properties, start, end) {
        // Calculate performance metrics for each property
        const propertyPerformance = [];
        
        properties.forEach(property => {
            // Filter payments for this property
            const propertyPayments = payments.filter(p => p.propertyId === property.id);
            
            // Calculate revenue
            const revenue = propertyPayments
                .filter(p => p.status === 'Paid')
                .reduce((sum, payment) => sum + payment.amount, 0);
            
            // Get occupancy (from tenant data)
            const tenants = window.tenantUtils ? window.tenantUtils.getTenants() : [];
            const propertyTenants = tenants.filter(t => t.propertyId === property.id && t.status === 'Active');
            const occupancyRate = property.units > 0 ? (propertyTenants.length / property.units * 100).toFixed(1) : 0;
            
            // Calculate revenue per unit
            const revenuePerUnit = property.units > 0 ? (revenue / property.units).toFixed(2) : 0;
            
            // Calculate payment reliability (percentage of paid vs total payments)
            const paidPayments = propertyPayments.filter(p => p.status === 'Paid').length;
            const paymentReliability = propertyPayments.length > 0 ? 
                (paidPayments / propertyPayments.length * 100).toFixed(1) : 0;
            
            propertyPerformance.push({
                property: property,
                revenue: revenue,
                occupancyRate: occupancyRate,
                revenuePerUnit: revenuePerUnit,
                paymentReliability: paymentReliability
            });
        });
        
        // Sort properties by revenue (highest first)
        propertyPerformance.sort((a, b) => b.revenue - a.revenue);
        
        // Generate the report HTML
        let reportHTML = `
            <h2>Property Performance Report</h2>
            <p>Period: ${formatDate(start)} - ${formatDate(end)}</p>
            
            <div class="chart-container">
                <canvas id="property-revenue-chart"></canvas>
            </div>
            
            <h3>Property Performance Metrics</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Revenue</th>
                        <th>Occupancy Rate</th>
                        <th>Revenue Per Unit</th>
                        <th>Payment Reliability</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        // Add rows for each property
        propertyPerformance.forEach(item => {
            reportHTML += `
                <tr>
                    <td>${item.property.name}</td>
                    <td>$${item.revenue.toFixed(2)}</td>
                    <td>${item.occupancyRate}%</td>
                    <td>$${item.revenuePerUnit}</td>
                    <td>${item.paymentReliability}%</td>
                </tr>
            `;
        });
        
        reportHTML += `
                </tbody>
            </table>
        `;
        
        reportContainer.innerHTML = reportHTML;
        
        // Create chart
        if (document.getElementById('property-revenue-chart')) {
            const ctx = document.getElementById('property-revenue-chart').getContext('2d');
            
            // Destroy previous chart if it exists
            if (window.propertyRevenueChart) {
                window.propertyRevenueChart.destroy();
            }
            
            // Prepare data for chart
            const labels = propertyPerformance.map(item => item.property.name);
            const revenueData = propertyPerformance.map(item => item.revenue);
            const occupancyData = propertyPerformance.map(item => parseFloat(item.occupancyRate));
            
            window.propertyRevenueChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue ($)',
                            data: revenueData,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Occupancy Rate (%)',
                            data: occupancyData,
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            type: 'line',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            max: 100,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
}
)