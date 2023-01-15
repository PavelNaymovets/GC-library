<?php
    /**
     * Класс описывает изменение текста задачи пользователя в БД.
     * 
     * Файл: api/v1/taskmanager/update/text/task.php
     * 
     * Параметры: $queryExecutor - выполняет CRUD операции в БД.
     *            $tasksReturner - возвращает информацию о задачах из БД.
     *            $table - имя таблицы для запросов(формат: tasks_название_проекта).
    */
    class TaskTextUpdater {

        private $queryExecutor;
        private $tasksReturner;
        private $table;

        public function __construct(&$queryExecutor, $tasksReturner) {
            $this->queryExecutor = $queryExecutor;
            $this->tasksReturner = $tasksReturner;
            $this->table = $queryExecutor->getTable();
        }
        
        /** 
         * ОБНОВЛЕНИЕ ТЕКСТА ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция обновляет текст задачи.
         * 
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskUpdateText($taskId, $uid, $week, $newText) {
            $newText = "'{$newText}'";
            $selectQuery = $this->queryExecutor->selectQuery("SELECT week FROM $this->table WHERE id = $taskId");
            $row = $selectQuery->fetch_assoc();
            $oldWeek = $row['week'];
            
            if($week == $oldWeek) {
                $result = $this->queryExecutor->updateQuery("UPDATE $this->table SET taskText = $newText WHERE id = $taskId AND uid = $uid");
            } else {
                $data = $this->tasksReturner->getSeqNumberAndTaskText($taskId, $uid); //получаю порядковый номер задачи в старой неделе
                $oldActiveTasks = $this->tasksReturner->getCountActiveTasksWeek($uid, $oldWeek); //считаю количество активных задач в старой неделе
                if($data["seqNumber"] != $oldActiveTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $oldActiveTasks; $i++) {
                        $this->queryExecutor->updateQuery("UPDATE $this->table SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1 AND week = $oldWeek");
                    }
                }
                $activeTasks = $this->tasksReturner->getCountActiveTasksWeek($uid, $week);
                $result = $this->queryExecutor->updateQuery("UPDATE $this->table SET taskText = $newText, week = $week, seqNumber = $activeTasks + 1  WHERE id = $taskId AND uid = $uid");
            }
            
            if($result == 1){
                $data = $this->tasksReturner->getSeqNumberAndTaskText($taskId, $uid);
                
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