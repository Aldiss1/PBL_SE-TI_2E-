/**
 * ChartManager - Kelas untuk mengelola inisialisasi Chart.js
 */
class ChartManager {
    constructor(canvasId, chartData, options = {}) {
        this.canvasId = canvasId;
        this.chartData = chartData;
        this.options = options;
        this.chart = null;
        this.initAttempts = 0;
        this.maxAttempts = 50;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.startInit());
        } else {
            if (document.readyState === 'complete') {
                this.startInit();
            } else {
                window.addEventListener('load', () => this.startInit());
            }
        }
    }

    startInit() {
        // Tunggu sedikit untuk memastikan semua script sudah dimuat
        setTimeout(() => {
            this.initChart();
        }, 200);
    }

    initChart() {
        this.initAttempts++;
        
        // Cek apakah Chart.js sudah dimuat
        if (typeof Chart === 'undefined') {
            if (this.initAttempts < this.maxAttempts) {
                setTimeout(() => this.initChart(), 100);
            } else {
                console.error('Chart.js failed to load after maximum attempts');
            }
            return;
        }
        
        // Cek apakah canvas sudah ada
        const canvas = document.getElementById(this.canvasId);
        if (!canvas) {
            if (this.initAttempts < this.maxAttempts) {
                setTimeout(() => this.initChart(), 100);
            } else {
                console.error('Chart canvas not found after maximum attempts');
            }
            return;
        }
        
        // Pastikan chart belum diinisialisasi
        if (canvas.chartInstance) {
            return;
        }
        
        // Validasi struktur data
        if (!this.chartData || !this.chartData.labels || !this.chartData.data) {
            console.error('Invalid chart data structure');
            return;
        }
        
        try {
            const ctx = canvas.getContext('2d');
            
            // Destroy chart jika sudah ada
            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }
            
            // Default options
            const defaultOptions = {
                type: 'line',
                data: {
                    labels: this.chartData.labels || [],
                    datasets: [{
                        label: 'Jumlah Pendaftar',
                        data: this.chartData.data || [],
                        borderColor: '#2F80ED',
                        backgroundColor: 'rgba(47, 128, 237, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2F80ED',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#1E5FCC',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 2.5,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 14,
                                    weight: '500'
                                },
                                color: '#2c3e50'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            borderColor: '#2F80ED',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return 'Pendaftar: ' + context.parsed.y + ' siswa';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                },
                                color: '#6c757d'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    }
                }
            };
            
            // Merge dengan custom options
            const finalOptions = this.mergeOptions(defaultOptions, this.options);
            
            // Buat chart baru
            canvas.chartInstance = new Chart(ctx, finalOptions);
            this.chart = canvas.chartInstance;
        } catch (error) {
            // Silent fail
        }
    }

    mergeOptions(defaultOpts, customOpts) {
        if (!customOpts || Object.keys(customOpts).length === 0) {
            return defaultOpts;
        }
        
        // Deep merge
        const merged = JSON.parse(JSON.stringify(defaultOpts));
        
        for (let key in customOpts) {
            if (typeof customOpts[key] === 'object' && !Array.isArray(customOpts[key])) {
                merged[key] = this.mergeOptions(merged[key] || {}, customOpts[key]);
            } else {
                merged[key] = customOpts[key];
            }
        }
        
        return merged;
    }

    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }

    update() {
        if (this.chart) {
            this.chart.update();
        }
    }
}

