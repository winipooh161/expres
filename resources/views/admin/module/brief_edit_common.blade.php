<!-- Добавим ссылку на файл со стилями вкладок -->
<link href="{{ asset('css/admin-briefs-tabs.css') }}" rel="stylesheet">

<div class="admin-dashboard">
    <div class="admin-header">
        <h1 class="page-title">Редактирование общего брифа <span class="brief-id">#{{ $brief->id }}</span></h1>
        <div class="admin-actions">
            <a href="{{ route('user.briefs', $brief->user_id) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к брифам пользователя
            </a>
            <a href="{{ route('common.show', $brief->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fas fa-eye"></i> Просмотреть бриф
            </a>
            @if($brief->deal_id)
            <a href="{{ route('deal.edit', $brief->deal_id) }}" class="btn btn-sm btn-success">
                <i class="fas fa-handshake"></i> Перейти к сделке
            </a>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.brief.updateCommon', $brief->id) }}" method="POST" id="briefEditForm">
        @csrf
        @method('PUT')

        <!-- Система вкладок -->
        <div class="tab-navigation">
            <ul class="nav-tabs">
                <li class="tab-item active" data-tab="info">
                    <i class="fas fa-info-circle"></i> Основная информация
                </li>
                <li class="tab-item" data-tab="answers">
                    <i class="fas fa-question-circle"></i> Ответы на вопросы
                </li>
                <li class="tab-item" data-tab="files">
                    <i class="fas fa-file-alt"></i> Файлы и референсы
                </li>
                <li class="tab-item" data-tab="rooms">
                    <i class="fas fa-home"></i> Комнаты и помещения
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- Вкладка основной информации -->
            <div class="tab-pane active" id="info-tab">
                <div class="row">
                    <div class="wd100">
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
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status">Статус</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="Активный" {{ $brief->status == 'Активный' ? 'selected' : '' }}>Активный</option>
                                                <option value="Завершенный" {{ $brief->status == 'Завершенный' ? 'selected' : '' }}>Завершенный</option>
                                                <option value="completed" {{ $brief->status == 'completed' ? 'selected' : '' }}>Выполнен</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="current_page">Текущая страница</label>
                                            <input type="number" name="current_page" id="current_page" class="form-control" value="{{ $brief->current_page }}" min="1" max="5">
                                            <small class="form-text text-muted">Из 5 страниц</small>
                                        </div>
                                    </div>
                                </div>

                          
                                
                                <div class="form-group">
                                    <label for="article">ID брифа</label>
                                    <input type="text" name="article" id="article" class="form-control" value="{{ $brief->article }}" readonly>
                                    <small class="form-text text-muted">Уникальный идентификатор брифа, только для чтения</small>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>

            <!-- Вкладка ответов на вопросы -->
            <div class="tab-pane" id="answers-tab">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Ответы на вопросы</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="answers-accordion">
                            @php
                            // $answers уже должен содержать структурированные ответы из контроллера
                            $pageTitles = [
                                'page1' => 'Общая информация',
                                'page2' => 'Интерьер: стиль и предпочтения',
                                'page3' => 'Пожелания по помещениям',
                                'page4' => 'Пожелания по отделке помещений',
                                'page5' => 'Пожелания по оснащению помещений',
                            ];
                            
                            // Проверяем наличие ответов
                            $hasAnyAnswers = false;
                            foreach ($answers as $pageKey => $pageData) {
                                if (!empty($pageData)) {
                                    $hasAnyAnswers = true;
                                    break;
                                }
                            }
                            @endphp

                            @if($hasAnyAnswers)
                                @foreach($pageTitles as $pageKey => $pageTitle)
                                    @php
                                    $pageAnswers = $answers[$pageKey] ?? [];
                                    $pageNum = substr($pageKey, 4); // Получаем номер страницы из ключа
                                    $hasAnswersInPage = !empty($pageAnswers);
                                    // Добавляем подсчет количества заполненных ответов
                                    $answersCount = count($pageAnswers);
                                    @endphp
                                    
                                    <div class="accordion-item {{ $hasAnswersInPage ? 'completed' : 'not-completed' }}">
                                        <div class="accordion-header" id="heading{{ $pageNum }}">
                                            <button class="btn btn-link accordion-button {{ $pageNum == 1 ? '' : 'collapsed' }}" 
                                                    type="button" 
                                                    data-toggle="collapse" 
                                                    data-target="#collapse{{ $pageNum }}" 
                                                    aria-expanded="{{ $pageNum == 1 ? 'true' : 'false' }}" 
                                                    aria-controls="collapse{{ $pageNum }}">
                                                <div class="page-header-content">
                                                    <span class="page-number">{{ $pageNum }}</span>
                                                    <span class="page-title">{{ $pageTitle }}</span>
                                                </div>
                                                <span class="accordion-status">
                                                    @if($hasAnswersInPage)
                                                        <i class="fas fa-check-circle text-success"></i>
                                                        <span class="ml-2 text-success font-weight-bold">{{ $answersCount }} {{ trans_choice('ответ|ответа|ответов', $answersCount) }}</span>
                                                    @else
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        <span class="ml-2 text-muted">Нет ответов</span>
                                                    @endif
                                                </span>
                                            </button>
                                        </div>

                                        <div id="collapse{{ $pageNum }}" 
                                             class="collapse {{ $pageNum == 1 ? 'show' : '' }}" 
                                             aria-labelledby="heading{{ $pageNum }}" 
                                             data-parent=".answers-accordion">
                                            <div class="accordion-body">
                                                @if($hasAnswersInPage)
                                                    @foreach($pageAnswers as $question => $answer)
                                                        <div class="answer-item">
                                                            <div class="answer-question">{{ $question }}</div>
                                                            <div class="form-group">
                                                                @if(is_array($answer))
                                                                    <textarea class="form-control answer-field" 
                                                                              name="answers[{{ $pageKey }}][{{ $question }}]" 
                                                                              rows="3">{{ json_encode($answer, JSON_UNESCAPED_UNICODE) }}</textarea>
                                                                @else
                                                                    @if(strlen($answer) > 100)
                                                                        <textarea class="form-control answer-field" 
                                                                                name="answers[{{ $pageKey }}][{{ $question }}]" 
                                                                                rows="3">{{ $answer }}</textarea>
                                                                    @else
                                                                        <input type="text" class="form-control answer-field" 
                                                                               name="answers[{{ $pageKey }}][{{ $question }}]" 
                                                                               value="{{ $answer }}">
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="empty-answers">
                                                        <p>На вопросы этой страницы еще не были даны ответы</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-comments"></i>
                                    <p>Еще нет ответов на вопросы в этом брифе</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка файлов и референсов -->
            <div class="tab-pane" id="files-tab">
                <div class="row">
                    <!-- Референсы -->
                    <div class="col-md-6">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2>Референсы</h2>
                            </div>
                            <div class="admin-card-body">
                                <div class="file-section">
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
                                                                <img src="{{ $reference }}" alt="Референс {{ $index + 1 }}">
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
                            </div>
                        </div>
                    </div>

                    <!-- Документы -->
                    <div class="col-md-6">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2>Документы</h2>
                            </div>
                            <div class="admin-card-body">
                                <div class="file-section">
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

            <!-- Вкладка комнат и помещений -->
            <div class="tab-pane" id="rooms-tab">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Комнаты и помещения</h2>
                    </div>
                    <div class="admin-card-body">
                        @php
                        $rooms = json_decode($brief->rooms ?? '[]', true);
                        @endphp

                        @if(is_array($rooms) && count($rooms) > 0)
                            <div class="rooms-container">
                                <div class="row">
                                    @foreach($rooms as $roomKey => $roomName)
                                        <div class="wd100">
                                            <div class="room-card">
                                                <div class="room-header">
                                                    <h3>{{ $roomName }}</h3>
                                                </div>
                                                <div class="room-body">
                                                    <div class="form-group">
                                                        <label for="room-{{ $roomKey }}">Название комнаты</label>
                                                        <input type="text" id="room-{{ $roomKey }}" name="rooms[{{ $roomKey }}]" class="form-control" value="{{ $roomName }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary add-room mt-3">
                                    <i class="fas fa-plus"></i> Добавить комнату
                                </button>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-home"></i>
                                <p>Нет добавленных комнат</p>
                            </div>
                        @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Навигация по вкладкам
    document.querySelectorAll('.tab-item').forEach(item => {
        item.addEventListener('click', function() {
            // Убираем активный класс со всех вкладок
            document.querySelectorAll('.tab-item').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Скрываем все панели вкладок
            document.querySelectorAll('.tab-pane').forEach(panel => {
                panel.classList.remove('active');
            });
            
            // Добавляем активный класс к выбранной вкладке и показываем соответствующую панель
            this.classList.add('active');
            document.getElementById(this.dataset.tab + '-tab').classList.add('active');
        });
    });

    // Обработчики для удаления файлов
    document.querySelectorAll('.delete-document').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Вы действительно хотите удалить этот документ?')) {
                const url = this.dataset.url;
                const item = this.closest('.document-item');
                
                // AJAX запрос для удаления файла
                fetch('{{ route("common.delete-file", $brief->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        file_url: url,
                        type: 'document'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        
                        // Проверить, остались ли еще документы
                        const container = document.getElementById('documents-container');
                        if (container.querySelectorAll('.document-item').length === 0) {
                            container.innerHTML = `
                                <div class="empty-state">
                                    <i class="far fa-file-alt"></i>
                                    <p>Нет загруженных документов</p>
                                </div>
                            `;
                        }
                    } else {
                        alert('Произошла ошибка при удалении файла.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при удалении файла.');
                });
            }
        });
    });

    document.querySelectorAll('.delete-reference').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Вы действительно хотите удалить этот референс?')) {
                const url = this.dataset.url;
                const item = this.closest('.reference-item');
                
                // AJAX запрос для удаления файла
                fetch('{{ route("common.delete-file", $brief->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        file_url: url,
                        type: 'reference'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        
                        // Проверить, остались ли еще референсы
                        const container = document.getElementById('references-container');
                        if (container.querySelectorAll('.reference-item').length === 0) {
                            container.innerHTML = `
                                <div class="empty-state">
                                    <i class="far fa-images"></i>
                                    <p>Нет загруженных референсов</p>
                                </div>
                            `;
                        }
                    } else {
                        alert('Произошла ошибка при удалении файла.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при удалении файла.');
                });
            }
        });
    });

    // Добавление новой комнаты
    document.querySelectorAll('.add-room').forEach(btn => {
        btn.addEventListener('click', function() {
            const roomContainer = document.querySelector('.rooms-container .row') || document.createElement('div');
            
            if (!document.querySelector('.rooms-container')) {
                const container = document.createElement('div');
                container.className = 'rooms-container';
                roomContainer.className = 'row';
                container.appendChild(roomContainer);
                
                const emptyState = document.querySelector('#rooms-tab .empty-state');
                if (emptyState) {
                    emptyState.replaceWith(container);
                } else {
                    document.querySelector('#rooms-tab .admin-card-body').appendChild(container);
                }
                
                // Перемещаем кнопку после контейнера
                this.remove();
                container.parentNode.appendChild(this);
            }
            
            const roomKey = 'new_' + Date.now();
            const roomCard = document.createElement('div');
            roomCard.className = 'wd100';
            roomCard.innerHTML = `
                <div class="room-card">
                    <div class="room-header">
                        <h3>Новая комната</h3>
                    </div>
                    <div class="room-body">
                        <div class="form-group">
                            <label for="room-${roomKey}">Название комнаты</label>
                            <input type="text" id="room-${roomKey}" name="rooms[${roomKey}]" class="form-control" value="Новая комната">
                        </div>
                    </div>
                </div>
            `;
            
            roomContainer.appendChild(roomCard);
        });
    });
});
</script>

<!-- Убираем дублирующиеся стили, так как они теперь в отдельном файле -->
<style>
/* Уникальные стили для страницы общего брифа */

</style>
