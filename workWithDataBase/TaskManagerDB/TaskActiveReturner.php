<?php
    /**
     * Класс описывает изменение статуса задачи пользователя на "активный" в БД.
     * 
     * Файл: api/v1/taskmanager/active/task.php
     * 
     * Параметры: $queryExecutor - выполняет CRUD операции в БД.
     *            $tasksReturner - возвращает информацию о задачах из БД.
     *            $table - имя таблицы для запросов(формат: tasks_название_проекта).
    */
    class TaskActiveReturner {

        private $queryExecutor;
        private $tasksReturner;
        private $table;

        public function __construct(&$queryExecutor, $tasksReturner) {
            $this->queryExecutor = $queryExecutor;
            $this->tasksReturner = $tasksReturner;
            $this->table = $queryExecutor->getTable();
        }
        
        
        /** 
         * ВОЗВРАЩЕНИЕ АКТИВНОСТИ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция делает завершенную задачу активной. isActive = 1 в БД. 
         * Обновляет порядковый номер следующих за этой задач в БД.
         * 
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskReturnActive($taskId, $uid, $week) {
            $data = $this->tasksReturner->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->tasksReturner->getCountActiveTasksWeek($uid, $week);
            $i = $activeTasks + 2; // +2, чтобы при -2 попасть в нужную seqNumber, а при -1 получить seqNumber на 1 больше, чем было.
            if($activeTasks == 0 && $data['seqNumber'] > 1) {
                $result = $this->queryExecutor->updateQuery("UPDATE $this->table SET seqNumber = 1 WHERE id = $taskId AND uid = $uid");
                $data['seqNumber'] = 1;
            } else {
                for($i; $i >= $data["seqNumber"] + 2; $i--) { // Сделано увеличение порядкового номера через вычитание, иначе БД работает неправильно. Вставляет одно число во все позиции, а нужно + 1.
                    $this->queryExecutor->updateQuery("UPDATE $this->table SET seqNumber = $i - 1 WHERE seqNumber = $i - 2 AND uid = $uid AND isActive = 1 AND week = $week");
                }
            }
            $result = $this->queryExecutor->updateQuery("UPDATE $this->table SET isActive = 1 WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $data["seqNumber"],
                    'week' => $week,
                    'taskText' => $data["taskText"]
                    ];
            } else {
                return 0;
            }
        }
    }
?>