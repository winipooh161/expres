<!-- Подключение CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<div class="admin-dashboard">
    <h1 class="page-title">Управление пользователями</h1>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Список пользователей</h2>
           
        </div>
        
        <div class="admin-card-body">
            <table id="userTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Телефон</th>
                      
                        <th>Пароль</th>
                        <th>Статус</th>
                        <th>Брифы</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-id="{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td><input type="text" class="form-control form-control-sm" value="{{ $user->name }}" data-id="{{ $user->id }}" data-field="name" onchange="updateUser(this)" /></td>
                            <td><input type="text" class="form-control form-control-sm" value="{{ $user->phone }}" data-id="{{ $user->id }}" data-field="phone" onchange="updateUser(this)" /></td>
                           
                            <td>
                                <input type="password" class="form-control form-control-sm" value="******" data-id="{{ $user->id }}" data-field="password" onclick="this.value=''" onchange="updateUser(this)" />
                            </td>
                            <td>
                                <select class="form-control form-control-sm" data-id="{{ $user->id }}" data-field="status" onchange="updateUser(this)">
                                    <option value="user" {{ $user->status == 'user' ? 'selected' : '' }}>Клиент</option>
                                    <option value="admin" {{ $user->status == 'admin' ? 'selected' : '' }}>Администратор</option>
                                    <option value="coordinator" {{ $user->status == 'coordinator' ? 'selected' : '' }}>Координатор</option>
                                    <option value="partner" {{ $user->status == 'partner' ? 'selected' : '' }}>Партнер</option>
                                    <option value="architect" {{ $user->status == 'architect' ? 'selected' : '' }}>Архитектор</option>
                                    <option value="designer" {{ $user->status == 'designer' ? 'selected' : '' }}>Дизайнер</option>
                                    <option value="visualizer" {{ $user->status == 'visualizer' ? 'selected' : '' }}>Визуализатор</option>
                                </select>
                            </td>
                            <td>
                                <a href="{{ route('user.briefs', $user->id) }}" class="btn btn-sm btn-info">Брифы</a>
                            </td>
                            <td class="actions">
                              
                                <button class="btn btn-sm btn-outline-danger delete-user" onclick="deleteUser({{ $user->id }})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Подключение JS DataTables и jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    const table = $('#userTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json"
        },
        "pageLength": 25,
        "order": [[0, 'desc']]
    });

    // Пользовательский поиск с учетом значений input
    $.fn.dataTable.ext.search.push(function(settings, searchData, index, rowData, counter) {
        const filterValue = $('#userTable_filter input').val().toLowerCase(); // Значение из поля поиска DataTables
        const rowInputs = $(`#userTable tbody tr:eq(${index}) input`).map(function() {
            return $(this).val().toLowerCase(); // Собираем значения всех input в строке
        }).get();

        // Проверка: хотя бы одно значение в input содержит поисковую строку
        return rowInputs.some(value => value.includes(filterValue));
    });

    // Обновление поиска при вводе текста
    $('#userTable_filter input').off('input').on('input', function() {
        table.draw(); // Перерисовка таблицы
    });
});

function updateUser(input) {
    const userId = input.dataset.id;
    const field = input.dataset.field;
    const value = input.value;

    fetch(`/admin/users/${userId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            [field]: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification("Данные пользователя обновлены", "success");
            if (field === "password") {
                input.value = "******"; // Возвращаем маску после обновления
            }
        } else {
            showNotification("Ошибка при обновлении данных", "error");
        }
    });
}

function deleteUser(userId) {
    if (!confirm("Вы уверены, что хотите удалить пользователя?")) return;

    fetch(`/admin/users/${userId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $(`#userTable tr[data-id="${userId}"]`).remove();
                showNotification("Пользователь удален", "success");
            } else {
                showNotification("Ошибка при удалении пользователя", "error");
            }
        });
}

function showNotification(message, type) {
    // Простая функция для отображения уведомлений
    const notificationClass = type === 'success' ? 'success-message' : 'error-message';
    const notification = $(`<div class="${notificationClass}">${message}</div>`).hide();
    $('body').append(notification);
    notification.fadeIn(300).delay(2000).fadeOut(300, function() {
        $(this).remove();
    });
}
</script>

<style>
.admin-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.admin-card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #EBE9F1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-card-header h2 {
    margin: 0;
  
    font-weight: 600;
}

.admin-card-body {
    padding: 20px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

table.dataTable {
    border-collapse: collapse !important;
}

.actions {
    white-space: nowrap;
    width: 100px;
}

.page-title {
    margin-bottom: 20px;
    font-weight: 600;
}

.form-control-sm {
    height: 30px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>