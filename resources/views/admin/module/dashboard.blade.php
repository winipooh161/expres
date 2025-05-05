<div class="container-fluid">
    <!-- Заголовок страницы -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Панель управления</h1>
        <div>
            <button class="btn btn-sm btn-primary" id="refreshDashboard">
                <i class="fas fa-sync-alt"></i> Обновить данные
            </button>
        </div>
    </div>

    <!-- Фильтры периодов и вкладки -->
    <div class="dashboard-filters">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="filter-label mr-2">Период:</div>
                <div class="period-selector">
                    <button class="period-button" data-period="7days">7 дней</button>
                    <button class="period-button active" data-period="30days">30 дней</button>
                    <button class="period-button" data-period="90days">90 дней</button>
                    <button class="period-button" data-period="year">Год</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Система вкладок -->
    <div class="tab-navigation mt-3">
        <ul class="nav-tabs">
            <li class="tab-item active" data-tab="analytics">
                <i class="fas fa-chart-line"></i> Аналитика
            </li>
            <li class="tab-item" data-tab="tables">
                <i class="fas fa-table"></i> Таблицы
            </li>
            <li class="tab-item" data-tab="summary">
                <i class="fas fa-info-circle"></i> Общие сведения
            </li>
        </ul>
    </div>

    <!-- Содержимое вкладок -->
    <div class="tab-content">
        <!-- Вкладка: Аналитика -->
        <div class="tab-pane active" id="analytics-tab">
            <!-- KPI карточки -->
            <div class="kpi-cards">
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="kpi-value">{{ $usersCount }}</div>
                    <div class="kpi-label">Пользователей</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up mr-1"></i> {{ $newUsersLast30Days }} за 30 дней
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="kpi-value">{{ $dealsCount }}</div>
                    <div class="kpi-label">Всего сделок</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up mr-1"></i> {{ $newDealsLast30Days }} за 30 дней
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-ruble-sign"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($totalDealAmount, 0, '.', ' ') }}</div>
                    <div class="kpi-label">Общая сумма</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up mr-1"></i> 12% с прошлого месяца
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($avgRating, 1) }}</div>
                    <div class="kpi-label">Средний рейтинг</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up mr-1"></i> 0.2 с прошлого месяца
                    </div>
                </div>
            </div>

            <!-- Компактная сетка графиков - 4 в одну строку -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Динамика пользователей</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="usersGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Динамика сделок</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="dealsGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Пользователи</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="userRolesChart"></canvas>
                            </div>
                            <div class="chart-legend" id="userRolesLegend"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Сделки</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="dealStatusChart"></canvas>
                            </div>
                            <div class="chart-legend" id="dealStatusLegend"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка: Таблицы - размещение в одну строку -->
        <div class="tab-pane" id="tables-tab">
            <div class="row">
                <!-- Недавние пользователи -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Недавно зарегистрированные пользователи</h2>
                            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-primary">Все пользователи</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Пользователь</th>
                                            <th>Email</th>
                                            <th>Роль</th>
                                            <th>Дата регистрации</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentUsers as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->status }}</td>
                                            <td>{{ $user->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Недавние сделки -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Недавно созданные сделки</h2>
                            <a href="{{ route('deal.cardinator') }}" class="btn btn-sm btn-primary">Все сделки</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Статус</th>
                                            <th>Сумма</th>
                                            <th>Дата создания</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentDeals as $deal)
                                        <tr>
                                            <td>{{ $deal->name }}</td>
                                            <td>
                                                <span class="status-badge 
                                                    {{ $deal->status == 'Проект завершен' ? 'status-completed' : 
                                                    ($deal->status == 'Проект на паузе' ? 'status-pending' : 'status-active') }}">
                                                    {{ $deal->status }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($deal->total_sum, 0, '.', ' ') }} ₽</td>
                                            <td>{{ $deal->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка: Общие сведения - в одну строку -->
        <div class="tab-pane" id="summary-tab">
            <div class="row">
                <!-- Статистика брифов -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Статистика брифов</h2>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="chart-container" style="height: 200px;">
                                        <canvas id="briefsChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stats-list">
                                        <div class="mb-3">
                                            <h5>Общие брифы</h5>
                                            <h3>{{ $commonsCount }}</h3>
                                        </div>
                                        <div class="mb-3">
                                            <h5>Коммерческие брифы</h5>
                                            <h3>{{ $commercialsCount }}</h3>
                                        </div>
                                        <div>
                                            <h5>Конверсия в сделки</h5>
                                            <h3>
                                                @php
                                                    $totalBriefs = $commonsCount + $commercialsCount;
                                                    $conversionRate = $totalBriefs > 0 ? round(($dealsCount / $totalBriefs) * 100) : 0;
                                                @endphp
                                                {{ $conversionRate }}%
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Прогноз роста -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2>Прогноз роста</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="growthForecastChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript для инициализации графиков -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Хранилище для объектов графиков
    const charts = {};
    
    // Система вкладок
    document.querySelectorAll('.tab-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.tab-item').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(panel => panel.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(this.dataset.tab + '-tab').classList.add('active');
        });
    });

    // Фильтры периода
    document.querySelectorAll('.period-button').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.period-button').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            updateDashboardData(this.dataset.period);
        });
    });

    // Обновление данных дашборда
    function updateDashboardData(period = '30days') {
        // Анимация кнопки обновления
        const refreshBtn = document.getElementById('refreshDashboard');
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обновление...';
        refreshBtn.disabled = true;
        
        // AJAX запрос к серверу для получения актуальных данных
        fetch(`/admin/analytics-data?period=${period}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка при получении данных');
                }
                return response.json();
            })
            .then(data => {
                // Обновляем графики с новыми данными
                updateCharts(data);
                
                // Восстанавливаем кнопку
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Обновить данные';
                refreshBtn.disabled = false;
            })
            .catch(error => {
                console.error('Ошибка при обновлении данных:', error);
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Обновить данные';
                refreshBtn.disabled = false;
                
                // Показываем уведомление пользователю
                showNotification('error', 'Не удалось получить актуальные данные');
            });
    }

    // Обработчик кнопки обновления
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        const activePeriod = document.querySelector('.period-button.active').dataset.period;
        updateDashboardData(activePeriod);
    });

    // Инициализация графиков
    initializeCharts();
    
    // Функция для обновления графиков после получения новых данных
    function updateCharts(data) {
        // Обновление KPI карточек
        if (data.kpi) {
            updateKPICards(data.kpi);
        }
        
        // Обновление графика роста пользователей
        if (data.userGrowth && charts.usersGrowthChart) {
            charts.usersGrowthChart.data.labels = data.userGrowth.labels;
            charts.usersGrowthChart.data.datasets[0].data = data.userGrowth.data;
            charts.usersGrowthChart.update();
        }
        
        // Обновление графика роста сделок
        if (data.dealGrowth && charts.dealsGrowthChart) {
            charts.dealsGrowthChart.data.labels = data.dealGrowth.labels;
            charts.dealsGrowthChart.data.datasets[0].data = data.dealGrowth.data;
            charts.dealsGrowthChart.update();
        }
        
        // Обновление прогноза роста
        if (data.forecast && charts.forecastChart) {
            updateForecastChart(charts.forecastChart, data.forecast);
        }
        
        // Если есть данные для статусов сделок - обновляем круговую диаграмму
        if (data.dealStatus && charts.dealStatusChart) {
            charts.dealStatusChart.data.labels = data.dealStatus.labels;
            charts.dealStatusChart.data.datasets[0].data = data.dealStatus.data;
            charts.dealStatusChart.update();
            
            // Обновляем легенду
            createChartLegend('dealStatusLegend', data.dealStatus.labels, charts.dealStatusChart.data.datasets[0].backgroundColor);
        }
        
        // Если есть данные о пользователях - обновляем круговую диаграмму ролей
        if (data.userRoles && charts.userRolesChart) {
            charts.userRolesChart.data.datasets[0].data = data.userRoles.data;
            charts.userRolesChart.update();
        }
    }
    
    // Обновление KPI карточек
    function updateKPICards(kpiData) {
        // Пользователи
        if (kpiData.users) {
            document.querySelector('.kpi-card:nth-child(1) .kpi-value').textContent = kpiData.users.total;
            const usersTrend = document.querySelector('.kpi-card:nth-child(1) .kpi-trend');
            usersTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.users.trend_direction}"></i> ${kpiData.users.trend_value} за ${kpiData.period_text}`;
            usersTrend.className = `kpi-trend trend-${kpiData.users.trend_direction}`;
        }
        
        // Сделки
        if (kpiData.deals) {
            document.querySelector('.kpi-card:nth-child(2) .kpi-value').textContent = kpiData.deals.total;
            const dealsTrend = document.querySelector('.kpi-card:nth-child(2) .kpi-trend');
            dealsTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.deals.trend_direction}"></i> ${kpiData.deals.trend_value} за ${kpiData.period_text}`;
            dealsTrend.className = `kpi-trend trend-${kpiData.deals.trend_direction}`;
        }
        
        // Общая сумма
        if (kpiData.amount) {
            document.querySelector('.kpi-card:nth-child(3) .kpi-value').textContent = kpiData.amount.formatted;
            const amountTrend = document.querySelector('.kpi-card:nth-child(3) .kpi-trend');
            amountTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.amount.trend_direction}"></i> ${kpiData.amount.trend_percent}% с прошлого месяца`;
            amountTrend.className = `kpi-trend trend-${kpiData.amount.trend_direction}`;
        }
        
        // Средний рейтинг
        if (kpiData.rating) {
            document.querySelector('.kpi-card:nth-child(4) .kpi-value').textContent = kpiData.rating.value;
            const ratingTrend = document.querySelector('.kpi-card:nth-child(4) .kpi-trend');
            ratingTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.rating.trend_direction}"></i> ${kpiData.rating.trend_value} с прошлого месяца`;
            ratingTrend.className = `kpi-trend trend-${kpiData.rating.trend_direction}`;
        }
    }
    
    // Обновление графика прогноза с корректными историческими данными
    function updateForecastChart(chart, forecastData) {
        // Обновление данных для исторического периода
        chart.data.labels = forecastData.labels;
        
        // Обновление данных для прогноза пользователей
        chart.data.datasets[0].data = forecastData.users.historical.concat(forecastData.users.forecast);
        
        // Обновление данных для прогноза сделок
        chart.data.datasets[1].data = forecastData.deals.historical.concat(forecastData.deals.forecast);
        
        // Обновляем конфигурацию для визуального разделения исторических и прогнозных данных
        chart.update();
    }
    
    // Функция показа уведомлений
    function showNotification(type, message) {
        const notificationEl = document.createElement('div');
        notificationEl.className = `notification notification-${type}`;
        notificationEl.textContent = message;
        
        document.body.appendChild(notificationEl);
        
        setTimeout(() => {
            notificationEl.style.opacity = '0';
            setTimeout(() => {
                notificationEl.remove();
            }, 500);
        }, 3000);
    }
});

function initializeCharts() {
    // Получаем контексты всех графиков
    const charts = {};
    
    // График роста пользователей
    const usersCtx = document.getElementById('usersGrowthChart').getContext('2d');
    charts.usersGrowthChart = new Chart(usersCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($userGrowthData['labels']) !!},
            datasets: [{
                label: 'Новые пользователи',
                data: {!! json_encode($userGrowthData['data']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                tension: 0.3,
                borderWidth: 3,
                fill: true,
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#fff',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#6e707e',
                    titleMarginBottom: 10,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false
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

    // График роста сделок
    const dealsCtx = document.getElementById('dealsGrowthChart').getContext('2d');
    charts.dealsGrowthChart = new Chart(dealsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dealGrowthData['labels']) !!},
            datasets: [{
                label: 'Новые сделки',
                data: {!! json_encode($dealGrowthData['data']) !!},
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.05)',
                tension: 0.3,
                borderWidth: 3,
                fill: true,
                pointBackgroundColor: '#1cc88a',
                pointBorderColor: '#fff',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#6e707e',
                    titleMarginBottom: 10,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false
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

    // Круговая диаграмма пользователей по ролям
    const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
    const userRoles = {
        labels: ['Клиенты', 'Координаторы', 'Партнеры', 'Архитекторы', 'Дизайнеры', 'Визуализаторы', 'Администраторы'],
        datasets: [{
            data: [
                {{ $userRoles['client'] ?? $userRoles['user'] ?? 0 }}, 
                {{ $userRoles['coordinator'] ?? 0 }}, 
                {{ $userRoles['partner'] ?? 0 }}, 
                {{ $userRoles['architect'] ?? 0 }}, 
                {{ $userRoles['designer'] ?? 0 }}, 
                {{ $userRoles['visualizer'] ?? 0 }}, 
                {{ $userRoles['admin'] ?? 0 }}
            ],
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b', '#5a32a3', '#fd6500'],
            hoverOffset: 5
        }]
    };

    charts.userRolesChart = new Chart(userRolesCtx, {
        type: 'doughnut',
        data: userRoles,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#6e707e',
                    titleMarginBottom: 10,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false
                }
            }
        }
    });

    // Создание легенды для диаграммы пользователей
    createChartLegend('userRolesLegend', userRoles.labels, userRoles.datasets[0].backgroundColor);

    // Круговая диаграмма статусов сделок
    const dealStatusCtx = document.getElementById('dealStatusChart').getContext('2d');
    const dealStatusLabels = [];
    const dealStatusData = [];
    const dealStatusColors = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
        '#6f42c1', '#fd7e14', '#20c9a6', '#3a3b45', '#5a5c69'
    ];
    
    @foreach($dealsByStatus as $index => $status)
        dealStatusLabels.push("{{ $status->status }}");
        dealStatusData.push({{ $status->count }});
    @endforeach

    const dealStatus = {
        labels: dealStatusLabels,
        datasets: [{
            data: dealStatusData,
            backgroundColor: dealStatusColors.slice(0, dealStatusLabels.length),
            hoverBackgroundColor: dealStatusColors.slice(0, dealStatusLabels.length).map(color => color),
            hoverOffset: 5
        }]
    };

    charts.dealStatusChart = new Chart(dealStatusCtx, {
        type: 'doughnut',
        data: dealStatus,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#6e707e',
                    titleMarginBottom: 10,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false
                }
            }
        }
    });

    // Создание легенды для статусов сделок
    createChartLegend('dealStatusLegend', dealStatus.labels, dealStatus.datasets[0].backgroundColor);

    // Прогноз роста с улучшенным алгоритмом
    const forecastCtx = document.getElementById('growthForecastChart').getContext('2d');
    
    // Создаем массивы с историческими данными 
    const historicalMonths = {!! json_encode($userGrowthData['labels']) !!};
    const historicalUsers = {!! json_encode($userGrowthData['data']) !!};
    const historicalDeals = {!! json_encode($dealGrowthData['data']) !!};
    
    // Добавляем прогноз на основе скользящего среднего и сезонного коэффициента
    const forecastMonths = ['Прогноз 1 мес', 'Прогноз 2 мес', 'Прогноз 3 мес'];
    
    // Улучшенный алгоритм прогнозирования
    function calculateForecast(historicalData) {
        // Если исторических данных меньше 3, используем простое среднее
        if (historicalData.length < 3) {
            const avg = historicalData.reduce((sum, val) => sum + val, 0) / historicalData.length;
            return [
                Math.max(1, Math.round(avg * 1.05)),
                Math.max(1, Math.round(avg * 1.1)),
                Math.max(1, Math.round(avg * 1.15))
            ];
        }
        
        // Скользящее среднее за последние 3 месяца
        const lastValues = historicalData.slice(-3);
        const movingAvg = lastValues.reduce((sum, val) => sum + val, 0) / 3;
        
        // Расчет тренда (наклона кривой)
        const trend = (lastValues[2] - lastValues[0]) / 2;
        
        // Оценка сезонности (простой подход)
        const seasonalIndex = historicalData.length >= 12 ? 
            historicalData[historicalData.length - 12] / movingAvg : 1;
        
        // Прогноз с учетом тренда и сезонности, но не меньше 1
        return [
            Math.max(1, Math.round((movingAvg + trend) * Math.max(0.9, Math.min(1.1, seasonalIndex)))),
            Math.max(1, Math.round((movingAvg + trend * 2) * Math.max(0.85, Math.min(1.15, seasonalIndex)))),
            Math.max(1, Math.round((movingAvg + trend * 3) * Math.max(0.8, Math.min(1.2, seasonalIndex))))
        ];
    }
    
    const forecastUsers = calculateForecast(historicalUsers);
    const forecastDeals = calculateForecast(historicalDeals);
    
    // Объединяем исторические данные и прогноз
    const allMonths = [...historicalMonths, ...forecastMonths];
    const allUsers = [...historicalUsers, ...forecastUsers];
    const allDeals = [...historicalDeals, ...forecastDeals];
    
    // Индекс, разделяющий исторические данные и прогноз
    const separationIndex = historicalMonths.length - 1;
    
    // Создаем график прогноза с визуальным разделением исторических и прогнозных данных
    charts.forecastChart = new Chart(forecastCtx, {
        type: 'line',
        data: {
            labels: allMonths,
            datasets: [
                {
                    label: 'Пользователи',
                    data: allUsers,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    borderWidth: 2,
                    fill: false,
                    pointBackgroundColor: function(context) {
                        const index = context.dataIndex;
                        return index >= historicalMonths.length ? '#4e73df' : 'rgba(78, 115, 223, 0.8)';
                    },
                    pointBorderColor: '#fff',
                    pointRadius: function(context) {
                        const index = context.dataIndex;
                        return index >= historicalMonths.length ? 5 : 3;
                    },
                    pointStyle: function(context) {
                        const index = context.dataIndex;
                        return index >= historicalMonths.length ? 'rectRot' : 'circle';
                    },
                    segment: {
                        borderDash: ctx => ctx.p1DataIndex >= separationIndex ? [5, 5] : undefined
                    }
                },
                {
                    label: 'Сделки',
                    data: allDeals,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                    tension: 0.3,
                    borderWidth: 2,
                    fill: false,
                    pointBackgroundColor: function(context) {
                        const index = context.dataIndex;
                        return index >= historicalMonths.length ? '#1cc88a' : 'rgba(28, 200, 138, 0.8)';
                    },
                    pointBorderColor: '#fff',
                    pointRadius: function(context) {
                        const index = context.dataIndex;
                        return index >= historicalMonths.length ? 5 : 3;
                    },
                    pointStyle: function(context) {
                        const index = context.dataIndex;
                        return index >= historicalMonths.length ? 'rectRot' : 'circle';
                    },
                    segment: {
                        borderDash: ctx => ctx.p1DataIndex >= separationIndex ? [5, 5] : undefined
                    }
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#6e707e',
                    titleMarginBottom: 10,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: true,
                    callbacks: {
                        title: function(tooltipItems) {
                            const index = tooltipItems[0].dataIndex;
                            return index >= historicalMonths.length ? 
                                `${tooltipItems[0].label} (прогноз)` : tooltipItems[0].label;
                        }
                    }
                },
                annotation: {
                    annotations: {
                        line1: {
                            type: 'line',
                            xMin: separationIndex,
                            xMax: separationIndex,
                            borderColor: 'rgba(150, 150, 150, 0.5)',
                            borderWidth: 1,
                            borderDash: [5, 5],
                            label: {
                                content: 'Начало прогноза',
                                enabled: true,
                                position: 'top'
                            }
                        }
                    }
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

    // График статистики брифов
    const briefsCtx = document.getElementById('briefsChart').getContext('2d');
    charts.briefsChart = new Chart(briefsCtx, {
        type: 'pie',
        data: {
            labels: ['Общие брифы', 'Коммерческие брифы'],
            datasets: [{
                data: [{{ $commonsCount }}, {{ $commercialsCount }}],
                backgroundColor: ['#4e73df', '#1cc88a'],
                hoverBackgroundColor: ['#2e59d9', '#17a673'],
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const dataset = data.datasets[0];
                                    const value = dataset.data[i];
                                    const backgroundColor = dataset.backgroundColor[i];

                                    return {
                                        text: `${label}: ${value}`,
                                        fillStyle: backgroundColor,
                                        strokeStyle: '#fff',
                                        lineWidth: 2,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleColor: '#6e707e',
                    titleMarginBottom: 10,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false
                }
            }
        }
    });
    
    // Возвращаем объект со всеми графиками для дальнейшего обновления
    return charts;
}

// Функция для создания легенды для графиков
function createChartLegend(containerId, labels, colors) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '';
    
    labels.forEach((label, index) => {
        if (index >= colors.length) return; // Защита от ошибок индексации
        
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item';
        
        const colorBox = document.createElement('div');
        colorBox.className = 'legend-color';
        colorBox.style.backgroundColor = colors[index];
        
        const labelText = document.createElement('span');
        labelText.textContent = label;
        
        legendItem.appendChild(colorBox);
        legendItem.appendChild(labelText);
        container.appendChild(legendItem);
    });
}
</script>

<style>
/* Стили для легенды графиков */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 10px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-right: 15px;
    margin-bottom: 5px;
    font-size: 12px;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 4px;
    color: white;
    font-weight: 500;
    z-index: 1050;
    opacity: 1;
    transition: opacity 0.5s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.notification-error {
    background-color: #e74a3b;
}

.notification-success {
    background-color: #1cc88a;
}

/* Исправление высоты контейнера для графиков */
.chart-container {
    height: 250px;
    position: relative;
}

@media (max-width: 992px) {
    .chart-container {
        height: 200px;
    }
}
</style>