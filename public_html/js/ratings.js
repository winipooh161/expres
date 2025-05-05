/**
 * Система рейтингов для оценки исполнителей в сделках
 */
(function() {
    // Инициализация при загрузке DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[Рейтинги] Инициализация системы рейтингов');
        
        // Элементы интерфейса
        const ratingModal = document.getElementById('rating-modal');
        if (!ratingModal) {
            console.error('[Рейтинги] Не найден элемент модального окна #rating-modal');
            return;
        }
        
        const stars = ratingModal.querySelectorAll('.rating-stars .star');
        const submitBtn = document.getElementById('submit-rating');
        
        let currentRating = 0;
        let pendingRatings = [];
        let currentRatingIndex = 0;
        let currentDealId = null;
        
        // Проверка необходимости оценок при загрузке
        function checkPendingRatingsOnLoad() {
            console.log('[Рейтинги] Проверка необходимости оценки при загрузке страницы');
            
            // Проверяем, есть ли ID завершенной сделки в localStorage
            const completedDealId = localStorage.getItem('completed_deal_id');
            console.log('[Рейтинги] ID завершенной сделки из localStorage:', completedDealId);
            
            if (completedDealId) {
                // Перед запуском проверки оценок, проверяем существование сделки
                verifyDealExists(completedDealId).then(exists => {
                    console.log('[Рейтинги] Проверка существования сделки:', completedDealId, 'Результат:', exists);
                    
                    if (exists) {
                        console.log('[Рейтинги] Запуск проверки оценок для сделки:', completedDealId);
                        if (typeof window.checkPendingRatings === 'function') {
                            window.checkPendingRatings(completedDealId);
                        } else {
                            console.warn('[Рейтинги] Функция checkPendingRatings не найдена, попытка инициализации через таймаут');
                            // Пробуем инициализировать через таймаут
                            setTimeout(() => {
                                if (typeof window.checkPendingRatings === 'function') {
                                    console.log('[Рейтинги] Функция найдена после таймаута, запуск');
                                    window.checkPendingRatings(completedDealId);
                                } else {
                                    console.error('[Рейтинги] Функция checkPendingRatings не определена при загрузке после таймаута');
                                }
                            }, 1000);
                        }
                    } else {
                        console.warn('[Рейтинги] Сделка не существует, очистка данных из хранилища:', completedDealId);
                        localStorage.removeItem('completed_deal_id');
                        localStorage.removeItem(`pending_ratings_${completedDealId}`);
                    }
                });
            } else {
                console.log('[Рейтинги] Нет сохраненных ID завершенных сделок');
            }
        }
        
        // Блокировка страницы до завершения оценки
        function blockPageUntilRated() {
            document.body.classList.add('rating-in-progress');
            
            // Блокируем нажатие клавиш Escape и Tab
            document.addEventListener('keydown', preventKeyboardNavigation);
            
            // Предотвращаем закрытие вкладки/браузера
            window.onbeforeunload = function() {
                return "Пожалуйста, оцените всех специалистов перед закрытием страницы.";
            };
        }
        
        // Предотвращение навигации клавиатурой
        function preventKeyboardNavigation(e) {
            if (e.key === 'Escape' || e.key === 'Tab') {
                e.preventDefault();
                // Показываем предупреждение при попытке закрыть модальное окно
                const alert = document.querySelector('.rating-alert');
                if (alert) {
                    alert.style.animation = 'none';
                    setTimeout(() => {
                        alert.style.animation = 'rating-alert-flash 0.5s ease-in-out';
                    }, 10);
                }
            }
        }
        
        // Разблокировка страницы после оценки
        function unblockPage() {
            document.body.classList.remove('rating-in-progress');
            document.removeEventListener('keydown', preventKeyboardNavigation);
            window.onbeforeunload = null;
            localStorage.removeItem('pendingRatingsState');
        }
        
        // Сохранение текущего состояния оценок
        function savePendingRatingsState() {
            localStorage.setItem('pendingRatingsState', JSON.stringify({
                pendingRatings: pendingRatings,
                currentIndex: currentRatingIndex,
                dealId: currentDealId
            }));
         
        }
        
        // Инициализация звездочек
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.dataset.value);
                highlightStars(value);
            });
            
            star.addEventListener('mouseout', function() {
                highlightStars(currentRating);
            });
            
            star.addEventListener('click', function() {
                currentRating = parseInt(this.dataset.value);
                highlightStars(currentRating);
            });
        });
        
        // Функция подсветки звезд
        function highlightStars(count) {
            stars.forEach(star => {
                const value = parseInt(star.dataset.value);
                if (value <= count) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        // Обработчик отправки оценки
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                if (currentRating === 0) {
                    const alert = document.querySelector('.rating-alert');
                    alert.textContent = "Пожалуйста, выберите оценку от 1 до 5 звезд!";
                    alert.style.backgroundColor = "#f8d7da";
                    alert.style.color = "#721c24";
                    alert.style.borderColor = "#f5c6cb";
                    alert.style.animation = 'none';
                    setTimeout(() => {
                        alert.style.animation = 'rating-alert-flash 0.5s ease-in-out';
                    }, 10);
                    return;
                }
                
                const userToRate = pendingRatings[currentRatingIndex];
                const comment = document.getElementById('rating-comment').value;
                
                // Получаем CSRF-токен из meta-тега
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Отправка оценки на сервер
                fetch('/ratings/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        deal_id: currentDealId,
                        rated_user_id: userToRate.user_id,
                        score: currentRating,
                        comment: comment,
                        role: userToRate.role
                    })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        // Переход к следующему исполнителю или закрытие модального окна
                        currentRatingIndex++;
                        
                        // Сохраняем текущее состояние
                        savePendingRatingsState();
                        
                        if (currentRatingIndex < pendingRatings.length) {
                            showNextRating();
                        } else {
                            ratingModal.style.display = 'none';
                            resetRatingModal();
                            unblockPage(); // Разблокируем страницу после выставления всех оценок
                            
                            // Показываем уведомление об успешном завершении оценок
                            const successMessage = document.createElement('div');
                            successMessage.className = 'success-message';
                            successMessage.innerHTML = 'Спасибо за оценку всех специалистов!';
                            document.body.appendChild(successMessage);
                            
                            setTimeout(() => {
                                successMessage.style.opacity = '0';
                                setTimeout(() => {
                                    document.body.removeChild(successMessage);
                                }, 500);
                            }, 3000);
                        }
                    } else {
                        alert(response.message || 'Произошла ошибка при сохранении оценки.');
                    }
                })
                .catch(error => {
                    console.error('[Рейтинги] Ошибка при отправке оценки:', error);
                    alert('Произошла ошибка при сохранении оценки.');
                });
            });
        }
        
        // Показ информации о следующем исполнителе для оценки
        function showNextRating() {
            if (currentRatingIndex >= pendingRatings.length) return;
            
            const userToRate = pendingRatings[currentRatingIndex];
            document.getElementById('rating-user-name').textContent = userToRate.name;
            document.getElementById('rating-user-role').textContent = formatRole(userToRate.role);
            document.getElementById('rating-user-avatar').src = userToRate.avatar_url || '/storage/icon/profile.svg';
            document.getElementById('current-rating-index').textContent = currentRatingIndex + 1;
            document.getElementById('total-ratings').textContent = pendingRatings.length;
            
            // Адаптируем заголовок и инструкцию в зависимости от роли оцениваемого
            const modalTitle = document.querySelector('#rating-modal h2');
            const ratingAlert = document.querySelector('.rating-alert');
            
            if (userToRate.role === 'coordinator') {
                modalTitle.textContent = 'Оцените качество планировочных координатора';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество координации проекта от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваша оценка позволит улучшить работу координаторов';
            } else if (userToRate.role === 'architect') {
                modalTitle.textContent = 'Оценка работы архитектора';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество планировочных решений от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваше мнение очень важно для нас и поможет улучшить качество работы архитекторов';
            } else if (userToRate.role === 'designer') {
                modalTitle.textContent = 'Оценка работы дизайнера';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество дизайнерских решений от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваше мнение очень важно для нас и поможет улучшить качество работы дизайнеров';
            } else if (userToRate.role === 'visualizer') {
                modalTitle.textContent = 'Оценка работы визуализатора';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество визуализаций от 1 до 5 звезд';
                ratingAlert.textContent = 'Ваше мнение очень важно для нас и поможет улучшить качество работы визуализаторов';
            } else {
                modalTitle.textContent = 'Оценка работы специалиста';
                document.querySelector('.rating-instruction').textContent = 'Оцените качество работы специалиста от 1 до 5 звезд';
                ratingAlert.textContent = 'Для продолжения работы необходимо оценить всех специалистов по данной сделке';
            }
            
            // Сброс текущей оценки
            currentRating = 0;
            highlightStars(0);
            document.getElementById('rating-comment').value = '';
        }
        
        // Форматирование роли для отображения
        function formatRole(role) {
            const roles = {
                'architect': 'Архитектор',
                'designer': 'Дизайнер',
                'visualizer': 'Визуализатор',
                'coordinator': 'Координатор',
                'partner': 'Партнер'
            };
            return roles[role] || role;
        }
        
        // Сброс модального окна
        function resetRatingModal() {
            currentRating = 0;
            pendingRatings = [];
            currentRatingIndex = 0;
            currentDealId = null;
            highlightStars(0);
            document.getElementById('rating-comment').value = '';
        }
        
        // Проверка наличия Laravel и текущего пользователя
        if (typeof window.Laravel === 'undefined' || !window.Laravel.user) {
            console.error('[Рейтинги] Отсутствует объект window.Laravel или информация о пользователе');
            return;
        }

        // Убеждаемся, что у пользователя есть статус и ID
        if (!window.Laravel.user.status || !window.Laravel.user.id) {
            console.error('[Рейтинги] У пользователя отсутствует статус или ID');
            return;
        }
        
        // Проверяем, может ли текущий пользователь оценивать других
        const userCanRate = ['coordinator', 'partner', 'client', 'user'].includes(window.Laravel.user.status);
        console.log('[Рейтинги] Пользователь может оценивать:', userCanRate, 'Статус:', window.Laravel.user.status);
        
        if (!userCanRate) {
            console.log('[Рейтинги] Пользователь не может оценивать других по его статусу');
            return;
        }
        
        // Проверка необходимости выставления оценок
        window.checkPendingRatings = function(dealId) {
            if (!dealId) {
                console.warn('[Рейтинги] Вызов checkPendingRatings без dealId');
                return;
            }
            
            console.log('[Рейтинги] Проверка ожидающих оценок для сделки:', dealId);

            // Сначала проверяем существование сделки
            verifyDealExists(dealId).then(exists => {
                console.log('[Рейтинги] Проверка существования сделки перед запросом:', dealId, 'Результат:', exists);
                
                if (!exists) {
                    console.warn('[Рейтинги] Сделка не существует, очистка данных из хранилища:', dealId);
                    localStorage.removeItem('completed_deal_id');
                    localStorage.removeItem(`pending_ratings_${dealId}`);
                    return;
                }

                // Если сделка существует, продолжаем проверку ожидающих рейтингов
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                console.log('[Рейтинги] CSRF-токен для запроса:', csrfToken ? 'Получен' : 'Отсутствует');
                
                fetch(`/ratings/check-pending?deal_id=${dealId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('[Рейтинги] Статус ответа API:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('[Рейтинги] Получены данные о необходимых оценках:', data);
                    
                    // Проверяем наличие пользователей для оценки
                    if (data.pending_ratings && data.pending_ratings.length > 0) {
                        console.log('[Рейтинги] Найдены пользователи для оценки:', data.pending_ratings.length);
                        
                        // Сохраняем ID сделки для текущей оценки
                        currentDealId = dealId;
                        
                        // Сохраняем список пользователей для оценки
                        pendingRatings = data.pending_ratings;
                        currentRatingIndex = 0;
                        
                        // Сохраняем состояние в localStorage для возможности восстановления
                        localStorage.setItem(`pending_ratings_${dealId}`, JSON.stringify(pendingRatings));
                        localStorage.setItem('completed_deal_id', dealId);
                        
                        // Показываем модальное окно
                        const ratingModal = document.getElementById('rating-modal');
                        if (ratingModal) {
                            console.log('[Рейтинги] Отображаем модальное окно для оценок');
                            
                            // Блокируем страницу до завершения оценок
                            blockPageUntilRated();
                            
                            // Отображаем первого пользователя для оценки
                            showNextRating();
                            
                            // Показываем модальное окно
                            ratingModal.style.display = 'flex';
                            
                            // Принудительно перерисовываем DOM для улучшения отображения
                            setTimeout(() => {
                                ratingModal.classList.add('show-rating-modal');
                            }, 10);
                        } else {
                            console.error('[Рейтинги] Не найден элемент #rating-modal');
                        }
                    } else {
                        console.log('[Рейтинги] Нет пользователей для оценки или все уже оценены');
                        // Если нет пользователей для оценки - очищаем ID сделки из localStorage
                        localStorage.removeItem('completed_deal_id');
                    }
                })
                .catch(error => {
                    console.error('[Рейтинги] Ошибка при проверке ожидающих оценок:', error);
                    // Очищаем данные в случае ошибки чтобы не застрять в цикле ошибок
                    localStorage.removeItem('completed_deal_id');
                });
            });
        };

        // Добавляем анимацию мигания для предупреждения
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            @keyframes rating-alert-flash {
                0% { transform: scale(1); }
                50% { transform: scale(1.03); background-color: #ffeeba; }
                100% { transform: scale(1); }
            }
            
            /* Стили для модального окна оценки */
            .rating-in-progress {
                overflow: hidden !important;
            }
            
            .rating-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .rating-modal-content {
                background: #fff;
                border-radius: 10px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }
            
            .rating-user-info {
                display: flex;
                align-items: center;
                margin: 20px 0;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 8px;
            }
            
            .rating-avatar {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                margin-right: 15px;
                object-fit: cover;
            }
            
            .rating-stars {
                display: flex;
                justify-content: center;
                font-size: 30px;
                margin: 20px 0;
            }
            
            .star {
                cursor: pointer;
                color: #ddd;
                margin: 0 5px;
                transition: transform 0.2s;
            }
            
            .star:hover {
                transform: scale(1.2);
            }
            
            .star.active {
                color: #ffbf00;
            }
            
            .rating-comment textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                min-height: 100px;
                margin-top: 10px;
            }
            
            /* Информационные сообщения */
            .info-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: #e9f5ff;
                color: #0069d9;
                border: 1px solid #b8daff;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 9999;
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(styleElement);

        // Проверяем localStorage для поиска ID завершенной сделки
        const completedDealIdFromStorage = localStorage.getItem('completed_deal_id');
        if (completedDealIdFromStorage) {
            console.log('[Рейтинги] Найден ID завершенной сделки в localStorage:', completedDealIdFromStorage);
            
            setTimeout(() => {
                console.log('[Рейтинги] Запуск проверки оценок для сделки из localStorage после таймаута');
                window.checkPendingRatings(completedDealIdFromStorage);
                localStorage.removeItem('completed_deal_id');
            }, 1500);
        } else {
            console.log('[Рейтинги] Нет ID завершенной сделки в localStorage');
        }
        
        // Проверяем наличие уже сохранённых оценок
        checkPendingRatingsOnLoad();

        // Добавляем очистку устаревших данных
        cleanupRatingsData();


    });
    
    // Функция для непосредственного запуска проверки оценок из других скриптов
    window.runRatingCheck = function(dealId) {
        console.log('[Рейтинги] Вызов runRatingCheck для сделки:', dealId);
        
        if (!dealId) {
            console.error('[Рейтинги] Вызов runRatingCheck без ID сделки');
            return;
        }
        
        // Сохраняем ID сделки в localStorage для последующей обработки
        localStorage.setItem('completed_deal_id', dealId);
        
        if (typeof window.checkPendingRatings === 'function') {
            console.log('[Рейтинги] Запуск checkPendingRatings из runRatingCheck');
            window.checkPendingRatings(dealId);
        } else {
            console.error('[Рейтинги] Функция checkPendingRatings не определена');
            // Пробуем инициализировать через таймаут
            setTimeout(() => {
                if (typeof window.checkPendingRatings === 'function') {
                    console.log('[Рейтинги] Функция найдена после таймаута, запуск');
                    window.checkPendingRatings(dealId);
                } else {
                    console.error('[Рейтинги] Функция checkPendingRatings все еще не определена после таймаута');
                }
            }, 2000);
        }
    };

    // Функция для очистки устаревших данных о рейтингах в localStorage
    function cleanupRatingsData() {
        // Находим все ключи в localStorage, связанные с рейтингами
        const keysToCheck = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && (key.startsWith('pending_ratings_') || key === 'completed_deal_id')) {
                keysToCheck.push(key);
            }
        }

        // Проверяем каждый ключ
        keysToCheck.forEach(key => {
            if (key === 'completed_deal_id') {
                const dealId = localStorage.getItem(key);
                if (dealId) {
                    verifyDealExists(dealId).then(exists => {
                        if (!exists) {
                            console.warn('[Рейтинги] Сделка не существует, очистка данных из хранилища:', dealId);
                            localStorage.removeItem(key);
                        }
                    });
                }
            } else if (key.startsWith('pending_ratings_')) {
                const dealId = key.replace('pending_ratings_', '');
                verifyDealExists(dealId).then(exists => {
                    if (!exists) {
                        console.warn('[Рейтинги] Сделка не существует, очистка данных из хранилища:', dealId);
                        localStorage.removeItem(key);
                    }
                });
            }
        });
    }

    // Функция для проверки существования сделки
    function verifyDealExists(dealId) {
        console.log('[Рейтинги] Проверка существования сделки:', dealId);
        
        if (!dealId) {
            console.error('[Рейтинги] Вызов verifyDealExists без ID сделки');
            return Promise.resolve(false);
        }
        
        return new Promise((resolve) => {
            // Отправляем запрос на сервер для проверки существования сделки
            fetch(`/deal/${dealId}/exists`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    // Если HTTP-статус не в диапазоне 200-299, предполагаем, что сделка не существует
                    console.warn('[Рейтинги] Ошибка проверки сделки, HTTP-статус:', response.status);
                    return { exists: false };
                }
            })
            .then(data => {
                console.log('[Рейтинги] Результат проверки сделки:', data);
                resolve(data.exists === true);
            })
            .catch(error => {
                console.error('[Рейтинги] Ошибка при проверке сделки:', error);
                resolve(false); // В случае ошибки, считаем что сделка не существует
            });
        });
    }
})();
