<div class="admin-dashboard">
    <div class="admin-header">
        <h1 class="page-title">Брифы пользователя: {{ $user->name }}</h1>
        <div class="admin-actions">
            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> К списку пользователей
            </a>
        </div>
    </div>
    
    <div class="admin-card user-profile-card">
        <div class="admin-card-header">
            <h2>Информация о пользователе</h2>
            <div class="card-actions">
                <a href="{{ route('profile.view', $user->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> Просмотр профиля
                </a>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                </div>
                <div class="user-details">
                    <div class="detail-row">
                        <span class="detail-label">Имя:</span>
                        <span class="detail-value">{{ $user->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $user->email }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Телефон:</span>
                        <span class="detail-value">{{ $user->phone ?: 'Не указан' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Статус:</span>
                        <span class="detail-value">
                            <span class="badge {{ $user->status == 'admin' ? 'bg-danger' : ($user->status == 'coordinator' ? 'bg-success' : 'bg-primary') }}">
                                {{ $user->status }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Дата регистрации:</span>
                        <span class="detail-value">{{ $user->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
                <div class="user-stats">
                    <div class="stat-card">
                        <span class="stat-value">{{ $commonBriefs->count() + $commercialBriefs->count() }}</span>
                        <span class="stat-label">Всего брифов</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $commonBriefs->where('status', 'Активный')->count() + $commercialBriefs->where('status', 'Активный')->count() }}</span>
                        <span class="stat-label">Активных</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Фильтр для брифов -->
    <div class="briefs-filter">
        <div class="filter-controls">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-layer-group"></i> Все брифы
            </button>
            <button class="filter-btn" data-filter="common">
                <i class="fas fa-file-alt"></i> Общие брифы
            </button>
            <button class="filter-btn" data-filter="commercial">
                <i class="fas fa-building"></i> Коммерческие брифы
            </button>
        </div>
        
        <div class="search-box">
            <input type="text" id="briefSearch" class="search-input" placeholder="Поиск брифов...">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
    
    <!-- Общие брифы -->
    <div class="briefs-section" id="common-briefs">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fas fa-file-alt"></i> Общие брифы</h2>
                <span class="badge bg-info">{{ $commonBriefs->count() }}</span>
            </div>
            <div class="admin-card-body">
                @if($commonBriefs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover briefs-table" id="commonBriefsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Дата создания</th>
                                    <th>Статус</th>
                                    <th>Прогресс</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commonBriefs as $brief)
                                    <tr data-id="{{ $brief->id }}" data-type="common" data-title="{{ $brief->title }}">
                                        <td><span class="id-badge">{{ $brief->id }}</span></td>
                                        <td>
                                            <div class="brief-title">{{ $brief->title }}</div>
                                            @if($brief->description)
                                                <div class="brief-description">{{ \Illuminate\Support\Str::limit($brief->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <i class="far fa-calendar-alt"></i> 
                                                {{ $brief->created_at->format('d.m.Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $brief->status == 'Активный' ? 'active' : 'completed' }}">
                                                <i class="fas {{ $brief->status == 'Активный' ? 'fa-spinner fa-spin' : 'fa-check' }}"></i>
                                                {{ $brief->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="brief-progress-wrapper" title="Заполнено страниц: {{ $brief->current_page }} из {{ $brief->total_pages ?? 10 }}">
                                                <div class="brief-progress">
                                                    <div class="progress-bar" style="width: {{ ($brief->current_page / ($brief->total_pages ?? 10)) * 100 }}%"></div>
                                                </div>
                                                <span class="progress-text">{{ $brief->current_page }}/{{ $brief->total_pages ?? 10 }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="actions-group">
                                                <a href="{{ route('admin.brief.editCommon', $brief->id) }}" class="action-btn edit-btn" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('common.show', $brief->id) }}" class="action-btn view-btn" title="Просмотр" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($brief->deal_id)
                                                <a href="{{ route('deal.edit', $brief->deal_id) }}" class="action-btn deal-btn" title="Перейти к сделке">
                                                    <i class="fas fa-handshake"></i>
                                                </a>
                                                @endif
                                                <button class="action-btn delete-btn delete-brief" data-id="{{ $brief->id }}" data-type="common" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>У пользователя нет общих брифов</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Коммерческие брифы -->
    <div class="briefs-section" id="commercial-briefs">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fas fa-building"></i> Коммерческие брифы</h2>
                <span class="badge bg-info">{{ $commercialBriefs->count() }}</span>
            </div>
            <div class="admin-card-body">
                @if($commercialBriefs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover briefs-table" id="commercialBriefsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Дата создания</th>
                                    <th>Статус</th>
                                    <th>Зоны</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commercialBriefs as $brief)
                                    <tr data-id="{{ $brief->id }}" data-type="commercial" data-title="{{ $brief->title }}">
                                        <td><span class="id-badge">{{ $brief->id }}</span></td>
                                        <td>
                                            <div class="brief-title">{{ $brief->title }}</div>
                                            @if($brief->description)
                                                <div class="brief-description">{{ \Illuminate\Support\Str::limit($brief->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <i class="far fa-calendar-alt"></i> 
                                                {{ $brief->created_at->format('d.m.Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $brief->status == 'Активный' ? 'active' : 'completed' }}">
                                                <i class="fas {{ $brief->status == 'Активный' ? 'fa-spinner fa-spin' : 'fa-check' }}"></i>
                                                {{ $brief->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $zones = json_decode($brief->zones ?? '[]', true);
                                                $zonesCount = is_array($zones) ? count($zones) : 0;
                                            @endphp
                                            <div class="zones-info" title="Количество зон: {{ $zonesCount }}">
                                                <div class="zones-badge">
                                                    <i class="fas fa-th"></i> 
                                                    <span class="zones-count">{{ $zonesCount }}</span>
                                                </div>
                                                @if($zonesCount > 0)
                                                    <div class="zones-names">
                                                        @foreach(array_slice($zones, 0, 3) as $zone)
                                                            <span class="zone-tag">{{ $zone['name'] ?? 'Без названия' }}</span>
                                                        @endforeach
                                                        @if($zonesCount > 3)
                                                            <span class="zone-more">+{{ $zonesCount - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="actions-group">
                                                <a href="{{ route('admin.brief.editCommercial', $brief->id) }}" class="action-btn edit-btn" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('commercial.show', $brief->id) }}" class="action-btn view-btn" title="Просмотр" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($brief->deal_id)
                                                <a href="{{ route('deal.edit', $brief->deal_id) }}" class="action-btn deal-btn" title="Перейти к сделке">
                                                    <i class="fas fa-handshake"></i>
                                                </a>
                                                @endif
                                                <button class="action-btn delete-btn delete-brief" data-id="{{ $brief->id }}" data-type="commercial" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>У пользователя нет коммерческих брифов</p>
                        <a href="{{ route('commercial.create') }}" class="btn btn-outline-primary mt-3">Создать новый бриф</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления брифа -->
<div class="modal fade" id="deleteBriefModal" tabindex="-1" aria-labelledby="deleteBriefModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBriefModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Вы уверены, что хотите удалить бриф "<span id="briefTitleToDelete"></span>"?</p>
                    <p class="text-danger mt-2"><strong>Внимание:</strong> Это действие нельзя отменить.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBrief">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация DataTables с улучшенными опциями
    const commonTable = $('#commonBriefsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json'
        },
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [5] },
            { width: "50px", targets: [0] },
            { width: "130px", targets: [3] },
            { width: "180px", targets: [5] }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        lengthMenu: [
            [5, 10, 25, -1],
            [5, 10, 25, "Все"]
        ]
    });
    
    const commercialTable = $('#commercialBriefsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json'
        },
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [5] },
            { width: "50px", targets: [0] },
            { width: "130px", targets: [3] },
            { width: "180px", targets: [5] }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        lengthMenu: [
            [5, 10, 25, -1],
            [5, 10, 25, "Все"]
        ]
    });

    // Фильтр по типам брифов с улучшенным UX
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            if (filter === 'all') {
                document.getElementById('common-briefs').style.display = 'block';
                document.getElementById('commercial-briefs').style.display = 'block';
            } else if (filter === 'common') {
                document.getElementById('common-briefs').style.display = 'block';
                document.getElementById('commercial-briefs').style.display = 'none';
            } else if (filter === 'commercial') {
                document.getElementById('common-briefs').style.display = 'none';
                document.getElementById('commercial-briefs').style.display = 'block';
            }
        });
    });
    
    // Улучшенный поиск по брифам
    document.getElementById('briefSearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        commonTable.search(searchValue).draw();
        commercialTable.search(searchValue).draw();
    });
    
    // Улучшенная обработка удаления брифов
    let briefToDeleteId = null;
    let briefToDeleteType = null;
    
    document.querySelectorAll('.delete-brief').forEach(button => {
        button.addEventListener('click', function() {
            briefToDeleteId = this.dataset.id;
            briefToDeleteType = this.dataset.type;
            const briefTitle = this.closest('tr').dataset.title;
            document.getElementById('briefTitleToDelete').textContent = briefTitle;
            
            // Показываем модальное окно с анимацией
            const modal = new bootstrap.Modal(document.getElementById('deleteBriefModal'));
            modal.show();
        });
    });
    
    // Обработчик подтверждения удаления с визуальной обратной связью
    document.getElementById('confirmDeleteBrief').addEventListener('click', function() {
        if (briefToDeleteId && briefToDeleteType) {
            // Изменение текста кнопки и добавление спиннера
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Удаление...';
            this.disabled = true;
            
            // Токен CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Определяем маршрут в зависимости от типа брифа
            const deleteUrl = briefToDeleteType === 'common' 
                ? `/admin/brief/common/${briefToDeleteId}` 
                : `/admin/brief/commercial/${briefToDeleteId}`;
            
            // Отправляем AJAX запрос
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Закрываем модальное окно
                    bootstrap.Modal.getInstance(document.getElementById('deleteBriefModal')).hide();
                    
                    // Добавляем анимацию удаления строки
                    const row = document.querySelector(`tr[data-id="${briefToDeleteId}"][data-type="${briefToDeleteType}"]`);
                    row.classList.add('fade-out-row');
                    
                    // Удаляем строку из таблицы после завершения анимации
                    setTimeout(() => {
                        if (briefToDeleteType === 'common') {
                            commonTable.row(row).remove().draw();
                        } else {
                            commercialTable.row(row).remove().draw();
                        }
                        
                        // Показываем красивое уведомление об успешном удалении
                        showNotification('success', 'Бриф успешно удален');
                        
                        // Обновляем счетчики
                        updateCounters(briefToDeleteType);
                    }, 300);
                } else {
                    showNotification('error', 'Ошибка удаления брифа');
                    resetDeleteButton();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Произошла ошибка при отправке запроса');
                resetDeleteButton();
            });
        }
    });
    
    // Функция для обновления счетчиков
    function updateCounters(type) {
        const commonCounter = document.querySelector('#common-briefs .badge');
        const commercialCounter = document.querySelector('#commercial-briefs .badge');
        const totalCounter = document.querySelector('.user-stats .stat-value:first-child');
        const activeCounter = document.querySelector('.user-stats .stat-value:last-child');
        
        if (type === 'common') {
            commonCounter.textContent = parseInt(commonCounter.textContent) - 1;
        } else {
            commercialCounter.textContent = parseInt(commercialCounter.textContent) - 1;
        }
        
        totalCounter.textContent = parseInt(totalCounter.textContent) - 1;
        
        // Проверяем, было ли активным удаленное
        const activeCount = parseInt(activeCounter.textContent);
        if (activeCount > 0) {
            activeCounter.textContent = activeCount - 1;
        }
        
        // Если таблица пуста, показываем сообщение
        checkEmptyTables(type);
    }
    
    // Функция для сброса состояния кнопки удаления
    function resetDeleteButton() {
        const deleteBtn = document.getElementById('confirmDeleteBrief');
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Удалить';
        deleteBtn.disabled = false;
    }
    
    // Проверка на пустые таблицы
    function checkEmptyTables(type) {
        if (type === 'common' && commonTable.rows().count() === 0) {
            document.querySelector('#common-briefs .admin-card-body').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>У пользователя нет общих брифов</p>
                </div>
            `;
        }
        
        if (type === 'commercial' && commercialTable.rows().count() === 0) {
            document.querySelector('#commercial-briefs .admin-card-body').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>У пользователя нет коммерческих брифов</p>
                    <a href="{{ route('commercial.create') }}" class="btn btn-outline-primary mt-3">Создать новый бриф</a>
                </div>
            `;
        }
    }
    
    // Функция для показа уведомлений
    function showNotification(type, message) {
        const notificationContainer = document.createElement('div');
        notificationContainer.className = `notification notification-${type}`;
        
        const icon = type === 'success' 
            ? '<i class="fas fa-check-circle"></i>' 
            : '<i class="fas fa-exclamation-circle"></i>';
            
        notificationContainer.innerHTML = `
            ${icon}
            <span>${message}</span>
        `;
        
        document.body.appendChild(notificationContainer);
        
        // Анимация появления
        setTimeout(() => {
            notificationContainer.classList.add('show');
        }, 10);
        
        // Удаление через 3 секунды
        setTimeout(() => {
            notificationContainer.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notificationContainer);
            }, 300);
        }, 3000);
    }
});
</script>
