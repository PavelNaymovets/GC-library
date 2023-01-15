<?php
    /**
     * Класс описывает изменение статуса задачи пользователя на "завершенный" в БД.
     * 
     * Файл: api/v1/taskmanager/delete/task.php
     * 
     * Параметры: $queryExecutor - выполняет CRUD операции в БД.
     *            $tasksReturner - возвращает информацию о задачах из БД.
     *            $table - имя таблицы для запросов(формат: tasks_название_проекта).
    */
    class TaskDeleter {

        private $queryExecutor;
        private $tasksReturner;
        private $table;

        public function __construct(&$queryExecutor, $tasksReturner) {
            $this->queryExecutor = $queryExecutor;
            $this->tasksReturner = $tasksReturner;
            $this->table = $queryExecutor->getTable();
        }
        
        /** 
         * УДАЛЕНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция удаляет задачу из базы данных. Обновляет порядковый номер следующих за этой задач в БД.
         * 
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskIsDeleted($taskId, $uid, $week) {
            $data = $this->tasksReturner->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->tasksReturner->getCountActiveTasksWeek($uid, $week);
            $result = $this->queryExecutor->selectQuery("SELECT isActive FROM $this->table WHERE id = $taskId AND uid = $uid");
            $row = $result->fetch_assoc();
            $active = $row['isActive'];
            $result = $this->queryExecutor->deleteQuery("DELETE FROM $this->table WHERE id = $taskId AND uid = $uid");
            if($result == 1 && $active != 0){
                if($data["seqNumber"] != $activeTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $activeTasks; $i++) {
                        $this->queryExecutor->updateQuery("UPDATE $this->table SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1 AND week = $week");
                    }
                }
            } 
            
            return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $data["seqNumber"],
                    'week' => $week,
                    'taskText' => $data["taskText"]
                    ];
        }
    }
?>