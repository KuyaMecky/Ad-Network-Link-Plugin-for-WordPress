// Analytics Charts Script
jQuery(document).ready(function($) {
    
    // Click Trend Chart
    if (typeof clickTrendData !== 'undefined' && clickTrendData.length > 0) {
        var trendLabels = clickTrendData.map(function(item) {
            return item.date;
        });
        var trendCounts = clickTrendData.map(function(item) {
            return item.count;
        });
        
        var ctx1 = document.getElementById('clickTrendChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Clicks',
                        data: trendCounts,
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Device Distribution Chart
    if (typeof deviceData !== 'undefined' && deviceData.length > 0) {
        var deviceLabels = deviceData.map(function(item) {
            return item.device_type;
        });
        var deviceCounts = deviceData.map(function(item) {
            return item.count;
        });
        
        var ctx2 = document.getElementById('deviceChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: deviceLabels,
                    datasets: [{
                        data: deviceCounts,
                        backgroundColor: [
                            '#2271b1',
                            '#00a32a',
                            '#d63638',
                            '#f6a306',
                            '#9b51e0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
    
    // Browser Distribution Chart
    if (typeof browserData !== 'undefined' && browserData.length > 0) {
        var browserLabels = browserData.map(function(item) {
            return item.browser;
        });
        var browserCounts = browserData.map(function(item) {
            return item.count;
        });
        
        var ctx3 = document.getElementById('browserChart');
        if (ctx3) {
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: browserLabels,
                    datasets: [{
                        label: 'Clicks',
                        data: browserCounts,
                        backgroundColor: '#2271b1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }
});
