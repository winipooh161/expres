<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'common_id',
        'commercial_id',
        'client_name',
        'name',
        'client_phone',
        'total_sum',
        'measuring_cost',
        'project_budget',
        'status',
        'registration_link',
        'registration_link_expiry',
        'user_id',
        'coordinator_id',
        'registration_token',
        'registration_token_expiry',
        'avatar_path',
        'link',
        'created_date',
        'client_city',
        'client_email',
        'client_info',
        'execution_comment',
        'comment',
        'project_number',
        'order_stage',
        'price_service_option',
        'rooms_count_pricing',
        'execution_order_comment',
        'execution_order_file',
        'client_timezone',
        'office_partner_id',
        'client_account_link',
        'measurement_comments',
        'measurements_file',
        'brief',
        'start_date',
        'project_duration',
        'project_end_date',
        'architect_id',
        'final_floorplan',
        'designer_id',
        'final_collage',
        'visualizer_id',
        'visualization_link',
        'final_project_file',
        'work_act',
        'archicad_file',
        'contract_number',
        'contract_attachment',
        'deal_note',
        'object_type',
        'package',
        'completion_responsible',
        'office_equipment',
        'stage',
        'coordinator_score',
        'has_animals',
        'has_plants',
        'object_style',
        'object_measurements',
        'rooms_count',
        'deal_end_date',
        'payment_date',
        'chat_group_id',
        // Добавляем новые поля для Яндекс Диска
        'yandex_url_execution_order_file',
        'yandex_url_measurements_file',
        'yandex_url_final_floorplan',
        'yandex_url_final_collage',
        'yandex_url_final_project_file',
        'yandex_url_work_act',
        'yandex_url_archicad_file',
        'yandex_url_contract_attachment',
        'yandex_disk_path_execution_order_file',
        'yandex_disk_path_measurements_file',
        'yandex_disk_path_final_floorplan',
        'yandex_disk_path_final_collage',
        'yandex_disk_path_final_project_file',
        'yandex_disk_path_work_act',
        'yandex_disk_path_archicad_file',
        'yandex_disk_path_contract_attachment',
        'original_name_execution_order_file',
        'original_name_measurements_file',
        'original_name_final_floorplan',
        'original_name_final_collage',
        'original_name_final_project_file',
        'original_name_work_act',
        'original_name_archicad_file',
        'original_name_contract_attachment',
        // Добавляем поля для новых типов файлов
        'yandex_url_plan_final',
        'yandex_disk_path_plan_final',
        'original_name_plan_final',
        'yandex_url_chat_screenshot',
        'yandex_disk_path_chat_screenshot',
        'original_name_chat_screenshot',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }

    public function brief()
    {
        return $this->belongsTo(Common::class, 'common_id');
    }

    public function briefs()
    {
        return $this->hasMany(Common::class, 'deal_id');
    }

    public function commercials()
    {
        return $this->hasMany(Commercial::class, 'deal_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'deal_user', 'deal_id', 'user_id');
    }

    public function coordinators()
    {
        return $this->users()->wherePivot('role', 'coordinator');
    }

    public function responsibles()
    {
        return $this->belongsToMany(User::class, 'deal_user');
    }

    public function allUsers()
    {
        return $this->users()->wherePivotIn('role', ['responsible', 'coordinator']);
    }

    public function dealFeeds()
    {
        return $this->hasMany(DealFeed::class);
    }

    public function changeLogs()
    {
        return $this->hasMany(DealChangeLog::class);
    }

    /**
     * Связь с групповым чатом
     */
    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    /**
     * Альтернативное отношение для прямого доступа к pivot-таблице.
     */
    public function usersPivot()
    {
        return $this->hasMany(DealUser::class);
    }

    /**
     * Рейтинги, связанные со сделкой
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Проверяет, поставил ли пользователь оценку другому пользователю в этой сделке
     * 
     * @param int $raterUserId ID оценивающего пользователя
     * @param int $ratedUserId ID оцениваемого пользователя
     * @param string|null $role Роль, в которой оценивается пользователь
     * @return bool
     */
    public function hasRatingFrom($raterUserId, $ratedUserId, $role = null)
    {
        $query = Rating::where('deal_id', $this->id)
            ->where('rater_user_id', $raterUserId)
            ->where('rated_user_id', $ratedUserId);
        
        // Если указана роль, добавляем её в условие запроса
        if ($role) {
            $query->where('role', $role);
        }
        
        return $query->exists();
    }

    /**
     * Получить среднюю оценку сделки ТОЛЬКО от клиента по всем исполнителям
     *
     * @return float|null
     */
    public function getClientAverageRatingAttribute()
    {
        if ($this->status !== 'Проект завершен') {
            return null;
        }
        
        // Получаем все оценки по сделке, где оценивающий - клиент (user)
        $ratings = \App\Models\Rating::where('deal_id', $this->id)
                                    ->whereHas('raterUser', function($query) {
                                        $query->where('status', 'user');
                                    })
                                    ->get();
        
        if ($ratings->isEmpty()) {
            return null;
        }
        
        return number_format($ratings->avg('score'), 1);
    }

    /**
     * Получить количество оценок сделки от клиента
     *
     * @return int
     */
    public function getClientRatingsCountAttribute()
    {
        return \App\Models\Rating::where('deal_id', $this->id)
                               ->whereHas('raterUser', function($query) {
                                   $query->where('status', 'user');
                               })
                               ->count();
    }

    /**
     * Проверяет наличие загруженного файла на Яндекс Диске
     *
     * @param string $fieldName Имя поля файла
     * @return bool
     */
    public function hasYandexFile($fieldName)
    {
        $yandexPathField = "yandex_disk_path_{$fieldName}";
        return !empty($this->$yandexPathField);
    }
    
    /**
     * Получает URL файла с Яндекс Диска
     *
     * @param string $fieldName Имя поля файла
     * @return string|null
     */
    public function getYandexFileUrl($fieldName)
    {
        $yandexUrlField = "yandex_url_{$fieldName}";
        return $this->$yandexUrlField;
    }
    
    /**
     * Получает оригинальное имя файла
     *
     * @param string $fieldName Имя поля файла
     * @return string|null
     */
    public function getYandexFileOriginalName($fieldName)
    {
        $originalNameField = "original_name_{$fieldName}";
        return $this->$originalNameField;
    }
}
