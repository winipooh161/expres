<!-- Добавим ссылку на файл со стилями вкладок -->
<link href="{{ asset('css/admin-briefs-tabs.css') }}" rel="stylesheet">

<div class="admin-dashboard">
    <div class="admin-header">
        <h1 class="page-title">Редактирование коммерческого брифа <span class="brief-id">#{{ $brief->id }}</span></h1>
        <div class="admin-actions">
            <a href="{{ route('user.briefs', $brief->user_id) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к брифам пользователя
            </a>
            <a href="{{ route('commercial.show', $brief->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fas fa-eye"></i> Просмотреть бриф
            </a>
            @if($brief->deal_id)
            <a href="{{ route('deal.edit', $brief->deal_id) }}" class="btn btn-sm btn-success">
                <i class="fas fa-handshake"></i> Перейти к сделке
            </a>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.brief.updateCommercial', $brief->id) }}" method="POST" id="briefEditForm">
        @csrf
        @method('PUT')

        <!-- Система вкладок -->
        <div class="tab-navigation">
            <ul class="nav-tabs">
                <li class="tab-item active" data-tab="info">
                    <i class="fas fa-info-circle"></i> Основная информация
                </li>
                <li class="tab-item" data-tab="zones">
                    <i class="fas fa-cubes"></i> Зоны и помещения
                </li>
                <li class="tab-item" data-tab="files">
                    <i class="fas fa-file-alt"></i> Файлы и референсы
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div id="info-tab" class="tab-pane active">
                <div class="row">
                    <!-- Левая колонка: Основная информация -->
                    <div class="col-md-4">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2>Основная информация</h2>
                            </div>
                            <div class="admin-card-body">
                                <div class="form-group">
                                    <label for="title">Название брифа</label>
                                    <input type="text" name="title" id="title" class="form-control" value="{{ $brief->title }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Описание</label>
                                    <textarea name="description" id="description" class="form-control" rows="3">{{ $brief->description }}</textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="price">Общий бюджет (₽)</label>
                                    <input type="number" name="price" id="price" class="form-control" value="{{ $brief->price }}">
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Статус</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="Активный" {{ $brief->status == 'Активный' ? 'selected' : '' }}>Активный</option>
                                        <option value="Завершенный" {{ $brief->status == 'Завершенный' ? 'selected' : '' }}>Завершенный</option>
                                        <option value="completed" {{ $brief->status == 'completed' ? 'selected' : '' }}>Выполнен</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="total_area">Общая площадь (м²)</label>
                                    <input type="number" name="total_area" id="total_area" class="form-control" value="{{ $brief->total_area }}" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label for="projected_area">Проектируемая площадь (м²)</label>
                                    <input type="number" name="projected_area" id="projected_area" class="form-control" value="{{ $brief->projected_area }}" step="0.01">
                                </div>

                             
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="zones-tab" class="tab-pane">
                <div class="row">
                    <div class="col-md-12">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2><i class="fas fa-building mr-2"></i> Зоны и помещения</h2>
                                <div class="card-actions">
                                    <button type="button" class="btn btn-sm btn-primary" id="addZoneBtn">
                                        <i class="fas fa-plus"></i> Добавить зону
                                    </button>
                                </div>
                            </div>
                            <div class="admin-card-body">
                                <!-- Пояснение по вопросам для зон -->
                                @php
                                $titles = [
                                    1  => "Название зоны",
                                    2  => "Метраж зон",
                                    3  => "Зоны и их стиль оформления",
                                    4  => "Меблировка зон",
                                    5  => "Предпочтения отделочных материалов",
                                    6  => "Освещение зон",
                                    7  => "Кондиционирование зон",
                                    8  => "Напольное покрытие зон",
                                    9  => "Отделка стен зон",
                                    10 => "Отделка потолков зон",
                                    11 => "Категорически неприемлемо или нет",
                                    12 => "Бюджет на помещения",
                                    13 => "Пожелания и комментарии",
                                ];
                                $zones = json_decode($brief->zones ?? '[]', true);
                                $zoneBudgets = json_decode($brief->zone_budgets ?? '[]', true);
                                $preferences = json_decode($brief->preferences ?? '[]', true) ?: [];
                                @endphp
                                
                                <div class="zone-info-box alert alert-info mb-4">
                                    <h5><i class="fas fa-info-circle mr-2"></i> Вопросы для каждой зоны</h5>
                                    <p>Для каждой зоны пользователь отвечает на следующие вопросы:</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                @foreach(array_slice($titles, 0, 7, true) as $key => $title)
                                                    <li><strong>{{ $key }}.</strong> {{ $title }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                @foreach(array_slice($titles, 7, null, true) as $key => $title)
                                                    <li><strong>{{ $key }}.</strong> {{ $title }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div id="zonesContainer">
                                    @if(is_array($zones) && count($zones) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover zones-table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Название зоны</th>
                                                        <th>Описание</th>
                                                        <th>Ответов</th>
                                                        <th>Действия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($zones as $index => $zone)
                                                        @php
                                                        $answersCount = isset($preferences[$index]) ? count($preferences[$index]) : 0;
                                                        @endphp
                                                        <tr data-zone-index="{{ $index }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <input type="hidden" name="zones[{{ $index }}][name]" value="{{ $zone['name'] ?? '' }}">
                                                                <strong>{{ $zone['name'] ?? 'Без названия' }}</strong>
                                                            </td>
                                                            <td>
                                                                <input type="hidden" name="zones[{{ $index }}][description]" value="{{ $zone['description'] ?? '' }}">
                                                                <span class="text-muted">{{ Str::limit($zone['description'] ?? 'Нет описания', 50) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $answersCount > 0 ? 'success' : 'secondary' }}">
                                                                    {{ $answersCount }} {{ trans_choice('ответ|ответа|ответов', $answersCount) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-zone" data-zone-index="{{ $index }}">
                                                                        <i class="fas fa-edit"></i> Редактировать
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-zone" data-zone-index="{{ $index }}">
                                                                        <i class="fas fa-trash"></i> Удалить
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
                                            <i class="fas fa-cubes"></i>
                                            <p>Нет добавленных зон</p>
                                            <p class="text-muted">Нажмите "Добавить зону", чтобы создать первую зону.</p>
                                        @endif

                                    <!-- Скрытое поле для передачи данных о предпочтениях зон -->
                                    <input type="hidden" id="zone-preferences-data" name="zone_preferences_data" value="{{ json_encode($preferences) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="files-tab" class="tab-pane">
                <div class="row">
                    <!-- Секция для референсов и документов -->
                    <div class="wd100">
                        <div class="admin-card mt-4">
                            <div class="admin-card-header">
                                <h2>Файлы и референсы</h2>
                            </div>
                            <div class="admin-card-body">
                                <!-- Референсы -->
                                <div class="file-section">
                                    <h4>Референсы</h4>
                                    <div id="references-container">
                                        @php
                                        $references = json_decode($brief->references ?? '[]', true);
                                        @endphp
                                        
                                        @if(is_array($references) && count($references) > 0)
                                            <div class="references-gallery">
                                                @foreach($references as $index => $reference)
                                                    <div class="reference-item" data-url="{{ $reference }}">
                                                        <div class="reference-preview">
                                                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $reference))
                                                                <img src="{{ $reference }}" alt="Референс {{ (int)$index + 1 }}">
                                                            @else
                                                                <div class="file-icon">
                                                                    <i class="far fa-file"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="reference-actions">
                                                            <a href="{{ $reference }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-reference" data-url="{{ $reference }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="empty-state">
                                                <i class="far fa-images"></i>
                                                <p>Нет загруженных референсов</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Документы -->
                                <div class="file-section mt-4">
                                    <h4>Документы</h4>
                                    <div id="documents-container">
                                        @php
                                        $documents = json_decode($brief->documents ?? '[]', true);
                                        @endphp
                                        
                                        @if(is_array($documents) && count($documents) > 0)
                                            <div class="documents-list">
                                                @foreach($documents as $index => $document)
                                                    <div class="document-item" data-url="{{ $document }}">
                                                        <div class="document-info">
                                                            @php
                                                            $extension = pathinfo(parse_url($document, PHP_URL_PATH), PATHINFO_EXTENSION);
                                                            $filename = pathinfo(parse_url($document, PHP_URL_PATH), PATHINFO_BASENAME);
                                                            
                                                            // Определяем иконку на основе расширения
                                                            $iconClass = 'fa-file';
                                                            switch(strtolower($extension)) {
                                                                case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                                                case 'doc': case 'docx': $iconClass = 'fa-file-word'; break;
                                                                case 'xls': case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                                                case 'jpg': case 'jpeg': case 'png': case 'gif': $iconClass = 'fa-file-image'; break;
                                                                case 'zip': case 'rar': $iconClass = 'fa-file-archive'; break;
                                                            }
                                                            @endphp
                                                            
                                                            <div class="document-icon">
                                                                <i class="far {{ $iconClass }}"></i>
                                                            </div>
                                                            <div class="document-name">
                                                                {{ $filename }}
                                                            </div>
                                                        </div>
                                                        <div class="document-actions">
                                                            <a href="{{ $document }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-document" data-url="{{ $document }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="empty-state">
                                                <i class="far fa-file-alt"></i>
                                                <p>Нет загруженных документов</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-actions-bottom">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить изменения
            </button>
            <a href="{{ route('user.briefs', $brief->user_id) }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Отменить
            </a>
            @if(!$brief->deal_id)
                <button type="button" class="btn btn-success create-deal-btn">
                    <i class="fas fa-handshake"></i> Создать сделку
                </button>
            @endif
        </div>
    </form>
</div>

<!-- Модальное окно для добавления новой зоны -->
<div class="modal fade" id="addZoneModal" tabindex="-1" aria-labelledby="addZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addZoneModalLabel">Добавить новую зону</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new-zone-name">Название зоны</label>
                    <input type="text" class="form-control" id="new-zone-name" placeholder="Например: Гостиная, Кухня, Спальня...">
                </div>
                <div class="form-group">
                    <label for="new-zone-description">Описание зоны</label>
                    <textarea class="form-control" id="new-zone-description" rows="3" placeholder="Опишите особенности этой зоны..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveNewZoneBtn">Добавить зону</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования зоны и ответов на вопросы -->
<div class="modal fade" id="editZoneModal" tabindex="-1" aria-labelledby="editZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editZoneModalLabel">Редактирование зоны</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-zone-index">
                
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-basic-tab" data-toggle="tab" href="#nav-basic" role="tab" aria-controls="nav-basic" aria-selected="true">Основная информация</a>
                        <a class="nav-item nav-link" id="nav-questions-tab" data-toggle="tab" href="#nav-questions" role="tab" aria-controls="nav-questions" aria-selected="false">Ответы на вопросы</a>
                    </div>
                </nav>
                
                <div class="tab-content" id="nav-tabContent">
                    <!-- Вкладка с основной информацией о зоне -->
                    <div class="tab-pane fade show active" id="nav-basic" role="tabpanel" aria-labelledby="nav-basic-tab">
                        <div class="p-3">
                            <div class="form-group">
                                <label for="edit-zone-name">Название зоны</label>
                                <input type="text" class="form-control" id="edit-zone-name">
                            </div>
                            <div class="form-group">
                                <label for="edit-zone-description">Описание зоны</label>
                                <textarea class="form-control" id="edit-zone-description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка с ответами на вопросы для зоны -->
                    <div class="tab-pane fade" id="nav-questions" role="tabpanel" aria-labelledby="nav-questions-tab">
                        <div class="p-3">
                            <div class="answers-container">
                                <!-- Здесь будут выводиться поля для ответов на вопросы -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveZoneChangesBtn">Сохранить изменения</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Код для работы вкладок основного интерфейса
    // Исправление проблемы с вкладками - используем прямой подход с jQuery, если доступен
    if (typeof $ !== 'undefined') {
        // Вариант с jQuery
        $('.tab-item').on('click', function(e) {
            console.log('Tab clicked (jQuery):', $(this).data('tab'));
            e.preventDefault();
            
            // Удаляем активный класс со всех вкладок
            $('.tab-item').removeClass('active');
            
            // Добавляем активный класс на текущую вкладку
            $(this).addClass('active');
            
            // Скрываем все панели
            $('.tab-pane').removeClass('active');
            
            // Получаем id целевой панели
            var tabId = $(this).data('tab') + '-tab';
            
            // Показываем нужную панель
            $('#' + tabId).addClass('active');
        });
        
        // Функционал для просмотра зон
        $('.view-zone').on('click', function() {
            var zoneIndex = $(this).data('zone-index');
            showZoneDetails(zoneIndex);
        });
        
        // Функционал для редактирования зон
        $('.edit-zone').on('click', function() {
            var zoneIndex = $(this).data('zone-index');
            openZoneEditModal(zoneIndex);
        });
        
        // Удаление зоны
        $('.delete-zone').on('click', function() {
            var zoneIndex = $(this).data('zone-index');
            if (confirm('Вы действительно хотите удалить эту зону?')) {
                deleteZone(zoneIndex);
            }
        });
        
        // Кнопка добавления новой зоны
        $('#addZoneBtn').on('click', function() {
            $('#addZoneModal').modal('show');
        });
        
        // Сохранение новой зоны
        $('#saveNewZoneBtn').on('click', function() {
            saveNewZone();
        });
        
        // Сохранение изменений зоны
        $('#saveZoneChangesBtn').on('click', function() {
            saveZoneChanges();
        });
        
        // Кнопка "Редактировать" из модального окна просмотра
        $('.edit-from-view-btn').on('click', function() {
            var zoneIndex = $('#view-zone-index').val();
            $('#viewZoneModal').modal('hide');
            setTimeout(function() {
                openZoneEditModal(zoneIndex);
            }, 500);
        });
    } else {
        // Вариант с чистым JavaScript - переписываем полностью
        var tabs = document.querySelectorAll('.tab-item');
        
        tabs.forEach(function(tab) {
            // Привязываем обработчик напрямую к каждому элементу
            tab.onclick = function(e) {
                console.log('Tab clicked (JS):', this.getAttribute('data-tab'));
                
                // Предотвращаем стандартное действие
                e.preventDefault();
                
                // Обработка активного состояния вкладок
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');
                
                // Обработка панелей вкладок
                var targetId = this.getAttribute('data-tab') + '-tab';
                var panes = document.querySelectorAll('.tab-pane');
                
                panes.forEach(function(pane) {
                    pane.classList.remove('active');
                });
                
                document.getElementById(targetId).classList.add('active');
            };
        });
    }
    
    // Функция для показа деталей зоны
    function showZoneDetails(zoneIndex) {
        var zones = @json($zones);
        var zoneBudgets = @json($zoneBudgets);
        var preferences = @json($preferences);
        
        var zone = zones[zoneIndex];
        $('#view-zone-name').text(zone.name || 'Не указано');
        $('#view-zone-area').text(zone.area ? zone.area + ' м²' : 'Не указано');
        $('#view-zone-style').text(zone.style || 'Не указано');
        
        var budget = zoneBudgets[zoneIndex];
        $('#view-zone-budget').text(budget ? new Intl.NumberFormat('ru-RU').format(budget) + ' ₽' : 'Не указан');
        
        $('#view-zone-description').text(zone.description || 'Описание отсутствует');
        
        // Заполняем таблицу ответов на вопросы
        var preferencesTable = $('#view-preferences-table-body');
        preferencesTable.empty();
        
        if (preferences[zoneIndex] && preferences[zoneIndex].length > 0) {
            var titles = @json($titles);
            preferences[zoneIndex].forEach(function(pref) {
                var questionTitle = pref.category && titles[pref.category] ? titles[pref.category] : 'Вопрос не указан';
                var row = '<tr>' +
                          '<td><strong>' + (pref.question ? pref.question : questionTitle) + '</strong>' +
                          (pref.category ? '<div class="small text-muted">Вопрос №' + pref.category + ': ' + questionTitle + '</div>' : '') +
                          '</td>' +
                          '<td>' + (pref.answer || 'Ответ не указан') + '</td>' +
                          '</tr>';
                preferencesTable.append(row);
            });
        } else {
            preferencesTable.append('<tr><td colspan="2" class="text-center">Нет ответов на вопросы</td></tr>');
        }
        
        // Запоминаем индекс для возможного последующего редактирования
        $('#view-zone-index').val(zoneIndex);
        
        // Показываем модальное окно
        $('#viewZoneModal').modal('show');
    }
    
    // Функция для открытия модального окна редактирования зоны
    function openZoneEditModal(zoneIndex) {
        var zones = @json($zones);
        var zoneBudgets = @json($zoneBudgets);
        var preferences = @json($preferences);
        
        var zone = zones[zoneIndex];
        $('#edit-zone-index').val(zoneIndex);
        $('#edit-zone-name').val(zone.name || '');
        $('#edit-zone-area').val(zone.area || '');
        $('#edit-zone-style').val(zone.style || '');
        $('#edit-zone-budget').val(zoneBudgets[zoneIndex] || '');
        $('#edit-zone-description').val(zone.description || '');
        
        // Заполняем контейнер ответов на вопросы
        var preferencesContainer = $('#edit-zone-preferences-container');
        preferencesContainer.empty();
        
        if (preferences[zoneIndex] && preferences[zoneIndex].length > 0) {
            var titles = @json($titles);
            preferences[zoneIndex].forEach(function(pref, prefIndex) {
                var questionTitle = pref.category && titles[pref.category] ? titles[pref.category] : 'Вопрос не указан';
                var prefHtml = '<div class="preference-item mb-3 p-3 border rounded" data-pref-index="' + prefIndex + '">' +
                               '<div class="d-flex justify-content-between mb-2">' +
                               '<h6>' + (pref.question ? pref.question : questionTitle) + '</h6>' +
                               '<div class="btn-group">' +
                               '<button type="button" class="btn btn-sm btn-outline-primary edit-preference" data-pref-index="' + prefIndex + '">' +
                               '<i class="fas fa-pencil-alt"></i></button>' +
                               '<button type="button" class="btn btn-sm btn-outline-danger delete-preference" data-pref-index="' + prefIndex + '">' +
                               '<i class="fas fa-trash"></i></button>' +
                               '</div>' +
                               '</div>' +
                               '<div class="small text-muted mb-2">Вопрос №' + pref.category + ': ' + questionTitle + '</div>' +
                               '<div class="preference-answer border-top pt-2">' + (pref.answer || 'Ответ не указан') + '</div>' +
                               '</div>';
                preferencesContainer.append(prefHtml);
            });
            
            // Привязываем обработчики к кнопкам редактирования и удаления предпочтений
            $('.edit-preference').on('click', function() {
                var prefIndex = $(this).data('pref-index');
                editPreference(zoneIndex, prefIndex);
            });
            
            $('.delete-preference').on('click', function() {
                var prefIndex = $(this).data('pref-index');
                if (confirm('Удалить это предпочтение?')) {
                    deletePreference(zoneIndex, prefIndex);
                }
            });
        } else {
            preferencesContainer.html('<div class="alert alert-info">Нет ответов на вопросы. Нажмите кнопку "Добавить ответ на вопрос" для создания.</div>');
        }
        
        // Обработчик для кнопки добавления предпочтения
        $('.add-preference-btn').off('click').on('click', function() {
            addNewPreference(zoneIndex);
        });
        
        // Показываем модальное окно
        $('#editZoneModal').modal('show');
    }
    
    // Функция удаления зоны
    function deleteZone(zoneIndex) {
        // Здесь можно добавить AJAX-запрос для удаления зоны на сервере
        // Либо просто удалить строку из таблицы, если данные отправляются при сохранении формы
        var row = document.querySelector('tr[data-zone-index="' + zoneIndex + '"]');
        if (row) {
            row.remove();
            
            // Перенумеровать оставшиеся строки
            var rows = document.querySelectorAll('.zones-table tbody tr');
            rows.forEach(function(row, index) {
                row.querySelector('td:first-child').textContent = index + 1;
                row.setAttribute('data-zone-index', index);
                
                // Обновить data-zone-index в кнопках
                row.querySelectorAll('button').forEach(function(btn) {
                    btn.setAttribute('data-zone-index', index);
                });
            });
        }
    }
    
    // Функция добавления новой зоны
    function saveNewZone() {
        var zoneName = $('#new-zone-name').val();
        var zoneArea = $('#new-zone-area').val();
        var zoneStyle = $('#new-zone-style').val();
        var zoneBudget = $('#new-zone-budget').val();
        var zoneDescription = $('#new-zone-description').val();
        
        if (!zoneName) {
            alert('Пожалуйста, укажите название зоны');
            return;
        }
        
        // Получаем текущий максимальный индекс зон
        var zoneRows = document.querySelectorAll('.zones-table tbody tr');
        var nextIndex = zoneRows.length;
        
        // Создаем новую строку для таблицы
        var newRow = '<tr data-zone-index="' + nextIndex + '">' +
                     '<td>' + (nextIndex + 1) + '</td>' +
                     '<td>' +
                     '<input type="hidden" name="zones[' + nextIndex + '][name]" value="' + zoneName + '">' +
                     '<span class="zone-name">' + zoneName + '</span>' +
                     '</td>' +
                     '<td>' +
                     '<input type="hidden" name="zones[' + nextIndex + '][area]" value="' + zoneArea + '">' +
                     '<span class="zone-area">' + (zoneArea || '-') + '</span>' +
                     '</td>' +
                     '<td>' +
                     '<input type="hidden" name="zones[' + nextIndex + '][style]" value="' + zoneStyle + '">' +
                     '<span class="zone-style">' + (zoneStyle || '-') + '</span>' +
                     '</td>' +
                     '<td>' +
                     '<input type="hidden" name="zone_budgets[' + nextIndex + ']" value="' + zoneBudget + '">' +
                     '<span class="zone-budget">' + (zoneBudget ? new Intl.NumberFormat('ru-RU').format(zoneBudget) : '-') + '</span>' +
                     '</td>' +
                     '<td>' +
                     '<div class="btn-group">' +
                     '<button type="button" class="btn btn-sm btn-outline-primary view-zone" data-zone-index="' + nextIndex + '">' +
                     '<i class="fas fa-eye"></i> Просмотр</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-success edit-zone" data-zone-index="' + nextIndex + '">' +
                     '<i class="fas fa-pencil-alt"></i> Изменить</button>' +
                     '<button type="button" class="btn btn-sm btn-outline-danger delete-zone" data-zone-index="' + nextIndex + '">' +
                     '<i class="fas fa-trash"></i> Удалить</button>' +
                     '</div>' +
                     '</td>' +
                     '</tr>';
        
        // Если у нас первая зона, нужно заменить пустое состояние на таблицу
        if (zoneRows.length === 0) {
            var emptyState = document.querySelector('.empty-state');
            if (emptyState) {
                var tableHtml = '<div class="table-responsive">' +
                                '<table class="table table-striped table-hover zones-table">' +
                                '<thead>' +
                                '<tr>' +
                                '<th width="5%">#</th>' +
                                '<th width="25%">Название зоны</th>' +
                                '<th width="10%">Метраж (м²)</th>' +
                                '<th width="20%">Стиль</th>' +
                                '<th width="15%">Бюджет (₽)</th>' +
                                '<th width="25%">Действия</th>' +
                                '</tr>' +
                                '</thead>' +
                                '<tbody>' + newRow + '</tbody>' +
                                '</table>' +
                                '</div>';
                
                $('#zonesContainer').html(tableHtml);
            }
        } else {
            // Добавляем строку в существующую таблицу
            $('.zones-table tbody').append(newRow);
        }
        
        // Добавляем скрытое поле для описания
        if (zoneDescription) {
            var descField = '<input type="hidden" name="zones[' + nextIndex + '][description]" value="' + zoneDescription + '">';
            $('#zonesContainer').append(descField);
        }
        
        // Добавляем обработчики событий для новой строки
        $('.view-zone[data-zone-index="' + nextIndex + '"]').on('click', function() {
            showZoneDetails(nextIndex);
        });
        
        $('.edit-zone[data-zone-index="' + nextIndex + '"]').on('click', function() {
            openZoneEditModal(nextIndex);
        });
        
        $('.delete-zone[data-zone-index="' + nextIndex + '"]').on('click', function() {
            if (confirm('Вы действительно хотите удалить эту зону?')) {
                deleteZone(nextIndex);
            }
        });
        
        // Закрываем модальное окно и очищаем поля
        $('#addZoneModal').modal('hide');
        $('#new-zone-name').val('');
        $('#new-zone-area').val('');
        $('#new-zone-style').val('');
        $('#new-zone-budget').val('');
        $('#new-zone-description').val('');
    }
    
    // Функция сохранения изменений зоны
    function saveZoneChanges() {
        var zoneIndex = $('#edit-zone-index').val();
        var zoneName = $('#edit-zone-name').val();
        var zoneArea = $('#edit-zone-area').val();
        var zoneStyle = $('#edit-zone-style').val();
        var zoneBudget = $('#edit-zone-budget').val();
        var zoneDescription = $('#edit-zone-description').val();
        
        if (!zoneName) {
            alert('Пожалуйста, укажите название зоны');
            return;
        }
        
        // Обновляем видимые значения в таблице
        var row = document.querySelector('tr[data-zone-index="' + zoneIndex + '"]');
        if (row) {
            row.querySelector('.zone-name').textContent = zoneName;
            row.querySelector('.zone-area').textContent = zoneArea || '-';
            row.querySelector('.zone-style').textContent = zoneStyle || '-';
            row.querySelector('.zone-budget').textContent = zoneBudget ? new Intl.NumberFormat('ru-RU').format(zoneBudget) : '-';
            
            // Обновляем скрытые поля для формы
            row.querySelector('input[name="zones[' + zoneIndex + '][name]"]').value = zoneName;
            row.querySelector('input[name="zones[' + zoneIndex + '][area]"]').value = zoneArea;
            row.querySelector('input[name="zones[' + zoneIndex + '][style]"]').value = zoneStyle;
            row.querySelector('input[name="zone_budgets[' + zoneIndex + ']"]').value = zoneBudget;
            
            var descField = document.querySelector('input[name="zones[' + zoneIndex + '][description]"]');
            if (descField) {
                descField.value = zoneDescription;
            } else {
                var newDescField = document.createElement('input');
                newDescField.type = 'hidden';
                newDescField.name = 'zones[' + zoneIndex + '][description]';
                newDescField.value = zoneDescription;
                document.getElementById('zonesContainer').appendChild(newDescField);
            }
        }
        
        // Закрываем модальное окно
        $('#editZoneModal').modal('hide');
    }
    
    // Функция для добавления нового предпочтения
    function addNewPreference(zoneIndex) {
        $('#preference-zone-index').val(zoneIndex);
        $('#preference-pref-index').val('');
        $('#preference-category').val('1');
        $('#preference-question').val('');
        $('#preference-answer').val('');
        
        $('#addPreferenceModal').modal('show');
    }
    
    // Функция для редактирования предпочтения
    function editPreference(zoneIndex, prefIndex) {
        var preferences = @json($preferences);
        var pref = preferences[zoneIndex][prefIndex];
        
        $('#preference-zone-index').val(zoneIndex);
        $('#preference-pref-index').val(prefIndex);
        $('#preference-category').val(pref.category || '1');
        $('#preference-question').val(pref.question || '');
        $('#preference-answer').val(pref.answer || '');
        
        $('#addPreferenceModal').modal('show');
    }
    
    // Функция для удаления предпочтения
    function deletePreference(zoneIndex, prefIndex) {
        // Удаляем элемент из DOM
        var prefItem = document.querySelector('#edit-zone-preferences-container .preference-item[data-pref-index="' + prefIndex + '"]');
        if (prefItem) {
            prefItem.remove();
        }
        
        // Перенумеруем оставшиеся предпочтения
        var prefItems = document.querySelectorAll('#edit-zone-preferences-container .preference-item');
        prefItems.forEach(function(item, index) {
            item.setAttribute('data-pref-index', index);
            item.querySelector('.edit-preference').setAttribute('data-pref-index', index);
            item.querySelector('.delete-preference').setAttribute('data-pref-index', index);
        });
        
        // Если все предпочтения удалены, показываем сообщение
        if (prefItems.length === 0) {
            $('#edit-zone-preferences-container').html('<div class="alert alert-info">Нет данных о предпочтениях. Нажмите кнопку "Добавить предпочтение" для создания.</div>');
        }
    }
    
    // Обработчик сохранения предпочтения
    $('#savePreferenceBtn').on('click', function() {
        var zoneIndex = $('#preference-zone-index').val();
        var prefIndex = $('#preference-pref-index').val();
        var category = $('#preference-category').val();
        var question = $('#preference-question').val();
        var answer = $('#preference-answer').val();
        
        if (!question) {
            alert('Пожалуйста, укажите вопрос или название параметра');
            return;
        }
        
        // Получаем заголовок категории для отображения
        var titles = @json($titles);
        var categoryTitle = titles[category] || 'Категория не указана';
        
        // Если это новое предпочтение
        if (prefIndex === '') {
            var prefItems = document.querySelectorAll('#edit-zone-preferences-container .preference-item');
            prefIndex = prefItems.length;
            
            // Если это первое предпочтение, очищаем контейнер от сообщения
            if (prefItems.length === 0) {
                $('#edit-zone-preferences-container').empty();
            }
            
            // Создаем HTML для нового предпочтения
            var prefHtml = '<div class="preference-item mb-3 p-3 border rounded" data-pref-index="' + prefIndex + '">' +
                           '<div class="d-flex justify-content-between mb-2">' +
                           '<h6>' + question + '</h6>' +
                           '<div class="btn-group">' +
                           '<button type="button" class="btn btn-sm btn-outline-primary edit-preference" data-pref-index="' + prefIndex + '">' +
                           '<i class="fas fa-pencil-alt"></i></button>' +
                           '<button type="button" class="btn btn-sm btn-outline-danger delete-preference" data-pref-index="' + prefIndex + '">' +
                           '<i class="fas fa-trash"></i></button>' +
                           '</div>' +
                           '</div>' +
                           '<div class="small text-muted mb-2">Категория: ' + category + '. ' + categoryTitle + '</div>' +
                           '<div class="preference-answer border-top pt-2">' + (answer || 'Ответ не указан') + '</div>' +
                           '</div>';
            
            $('#edit-zone-preferences-container').append(prefHtml);
            
            // Добавляем скрытые поля для формы
            $('#zonesContainer').append(
                '<input type="hidden" name="preferences[' + zoneIndex + '][' + prefIndex + '][question]" value="' + question + '">' +
                '<input type="hidden" name="preferences[' + zoneIndex + '][' + prefIndex + '][category]" value="' + category + '">' +
                '<input type="hidden" name="preferences[' + zoneIndex + '][' + prefIndex + '][answer]" value="' + answer + '">'
            );
            
            // Добавляем обработчики для кнопок
            $('.edit-preference[data-pref-index="' + prefIndex + '"]').on('click', function() {
                editPreference(zoneIndex, prefIndex);
            });
            
            $('.delete-preference[data-pref-index="' + prefIndex + '"]').on('click', function() {
                if (confirm('Удалить это предпочтение?')) {
                    deletePreference(zoneIndex, prefIndex);
                }
            });
        } else {
            // Обновляем существующее предпочтение
            var prefItem = document.querySelector('#edit-zone-preferences-container .preference-item[data-pref-index="' + prefIndex + '"]');
            if (prefItem) {
                prefItem.querySelector('h6').textContent = question;
                prefItem.querySelector('.small.text-muted').textContent = 'Категория: ' + category + '. ' + categoryTitle;
                prefItem.querySelector('.preference-answer').textContent = answer || 'Ответ не указан';
            }
            
            // Обновляем скрытые поля для формы
            var questionField = document.querySelector('input[name="preferences[' + zoneIndex + '][' + prefIndex + '][question]"]');
            var categoryField = document.querySelector('input[name="preferences[' + zoneIndex + '][' + prefIndex + '][category]"]');
            var answerField = document.querySelector('input[name="preferences[' + zoneIndex + '][' + prefIndex + '][answer]"]');
            
            if (questionField) questionField.value = question;
            if (categoryField) categoryField.value = category;
            if (answerField) answerField.value = answer;
        }
        
        // Закрываем модальное окно
        $('#addPreferenceModal').modal('hide');
    });
});

// Глобальный обработчик для вкладок, который будет перехватывать все клики
document.addEventListener('click', function(e) {
    // Проверяем, был ли клик по вкладке или её дочернему элементу
    var tabItem = e.target.closest('.tab-item');
    if (tabItem) {
        console.log('Глобальный перехват клика по вкладке:', tabItem.getAttribute('data-tab'));
        
        // Отменяем стандартные действия
        e.preventDefault();
        e.stopPropagation();
        
        // Удаляем активный класс со всех вкладок
        document.querySelectorAll('.tab-item').forEach(function(tab) {
            tab.classList.remove('active');
        });
        
        // Добавляем активный класс выбранной вкладке
        tabItem.classList.add('active');
        
        // Скрываем все панели
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('active');
        });
        
        // Получаем ID целевой панели и активируем её
        var tabId = tabItem.getAttribute('data-tab') + '-tab';
        var targetPane = document.getElementById(tabId);
        
        if (targetPane) {
            targetPane.classList.add('active');
        } else {
            console.error('Целевая панель не найдена:', tabId);
        }
    }
});
</script>
