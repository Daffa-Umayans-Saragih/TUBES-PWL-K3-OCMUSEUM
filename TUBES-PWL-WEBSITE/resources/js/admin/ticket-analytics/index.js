/**
 * Ticket Analytics Dashboard JavaScript
 * Handles chart rendering and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all charts
    initRevenueTrendChart();
    initMonthlyRevenueChart();
    initPaymentStatusChart();
    initTicketSalesTrendChart();
    initTicketDistributionChart();
    initTicketStatusChart();
    
    // Add smooth scroll behavior
    enableSmoothScroll();
});

// ========== REVENUE TREND CHART ==========
function initRevenueTrendChart() {
    const canvas = document.getElementById('revenueTrendChart');
    if (!canvas) return;
    
    const data = JSON.parse(canvas.getAttribute('data-revenue'));
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Revenue',
                data: data.map(d => d.amount),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 600 },
                    bodyFont: { size: 13 },
                    borderColor: '#ddd',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ========== MONTHLY REVENUE CHART ==========
function initMonthlyRevenueChart() {
    const canvas = document.getElementById('monthlyRevenueChart');
    if (!canvas) return;
    
    const data = JSON.parse(canvas.getAttribute('data-revenue'));
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.month),
            datasets: [{
                label: 'Monthly Revenue',
                data: data.map(d => d.amount),
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(99, 102, 241, 0.6)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(99, 102, 241, 0.6)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(99, 102, 241, 0.6)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(99, 102, 241, 0.6)'
                ],
                borderColor: '#6366f1',
                borderWidth: 0,
                borderRadius: 8,
                hoverBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    borderColor: '#ddd',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ========== PAYMENT STATUS CHART ==========
function initPaymentStatusChart() {
    const canvas = document.getElementById('paymentStatusChart');
    if (!canvas) return;
    
    const data = JSON.parse(canvas.getAttribute('data-payment-status'));
    const ctx = canvas.getContext('2d');
    
    // Prepare data
    const labels = Object.keys(data);
    const counts = labels.map(label => data[label]?.count || 0);
    const colors = {
        'completed': '#10b981',
        'pending': '#f59e0b',
        'failed': '#ef4444'
    };
    const backgroundColor = labels.map(label => colors[label] || '#6366f1');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
            datasets: [{
                data: counts,
                backgroundColor: backgroundColor,
                borderColor: '#fff',
                borderWidth: 3,
                hoverBorderColor: '#ddd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12 },
                        usePointStyle: true
                    }
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' transactions';
                        }
                    }
                }
            }
        }
    });
}

// ========== TICKET SALES TREND CHART ==========
function initTicketSalesTrendChart() {
    const canvas = document.getElementById('ticketSalesTrendChart');
    if (!canvas) return;
    
    const data = JSON.parse(canvas.getAttribute('data-sales'));
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Tickets Sold',
                data: data.map(d => d.sales),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: '#3b82f6',
                borderWidth: 0,
                borderRadius: 6,
                hoverBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' tickets';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ========== TICKET DISTRIBUTION CHART ==========
function initTicketDistributionChart() {
    const canvas = document.getElementById('ticketDistributionChart');
    if (!canvas) return;
    
    const data = JSON.parse(canvas.getAttribute('data-distribution'));
    const ctx = canvas.getContext('2d');
    
    const colors = [
        '#6366f1',
        '#8b5cf6',
        '#d946ef',
        '#ec4899',
        '#f43f5e',
        '#f97316',
        '#eab308',
        '#84cc16',
        '#22c55e',
        '#10b981'
    ];
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(d => d.name),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: colors.slice(0, data.length),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        font: { size: 12 },
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// ========== TICKET STATUS CHART ==========
function initTicketStatusChart() {
    const canvas = document.getElementById('ticketStatusChart');
    if (!canvas) return;
    
    const data = JSON.parse(canvas.getAttribute('data-status'));
    const ctx = canvas.getContext('2d');
    
    const colors = {
        'used': '#10b981',
        'pending': '#f59e0b',
        'cancelled': '#ef4444',
        'expired': '#6b7280'
    };
    
    const labels = Object.keys(data);
    const backgroundColor = labels.map(label => colors[label] || '#6366f1');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
            datasets: [{
                data: labels.map(l => data[l]),
                backgroundColor: backgroundColor,
                borderWidth: 0,
                borderRadius: 6,
                hoverBackgroundColor: '#4b5563'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' tickets';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ========== UTILITY FUNCTIONS ==========
function enableSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

// Add data refresh capability
function refreshAnalyticsData(startDate, endDate) {
    fetch(`/admin/ticket-analytics/data?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            console.log('Analytics data refreshed:', data);
            // Could update charts here
        })
        .catch(error => console.error('Error refreshing data:', error));
}
