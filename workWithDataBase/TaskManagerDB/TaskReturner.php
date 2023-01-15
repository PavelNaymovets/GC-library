<?php
    /**
     * Класс описывает возвращение задач пользователя из БД.
     * 
     * Файл: api/v1/taskmanager/tasks.php
     * 
     * Параметры: $queryExecutor - выполняет CRUD операции в БД.
     *            $table - имя таблицы для запросов(формат: tasks_название_проекта).
    */
    class TaskReturner {

        private $queryExecutor;
        private $table;

        public function __construct(&$queryExecutor) {
            $this->queryExecutor = $queryExecutor;
            $this->table = $queryExecutor->getTable();
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ */
        public function getCountTasks($uid) {
            $result = $this->queryExecutor->selectQuery("SELECT * FROM $this->table WHERE uid = $uid");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ КОНКРЕТНОЙ НЕДЕЛИ */
        public function getCountActiveTasksWeek($uid, $week) {
            $result = $this->queryExecutor->selectQuery("SELECT * FROM $this->table WHERE uid = $uid AND isActive = 1 AND week = $week");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ ПО ВСЕМ НЕДЕЛЯМ */
        public function getCountActiveTasks($uid) {
            $result = $this->queryExecutor->selectQuery("SELECT * FROM $this->table WHERE uid = $uid AND isActive = 1");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА НЕ АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ ПО ВСЕМ НЕДЕЛЯМ */
        public function getCountNotActiveTasks($uid) {
            $result = $this->queryExecutor->selectQuery("SELECT * FROM $this->table WHERE uid = $uid AND isActive = 0");
            
            return $result->num_rows;
        }
        
        /** 
         * ВОЗВРАТ seqNumber И taskText ЗАДАЧИ КОНКРЕТНОГО ПОЛЬЗОВАТЕЛЯ:
         * 
         * return ["seqNumber","taskText"] - запрос выполнен.
         * return ' ' - запрос не выполнен.
         */
        public function getSeqNumberAndTaskText($taskId, $uid){
            $selectQuery = $this->queryExecutor->selectQuery("SELECT seqNumber, taskText FROM $this->table WHERE id = $taskId AND uid = $uid");
            $row = $selectQuery->fetch_assoc();
            $data["seqNumber"] = $row["seqNumber"];
            $data["taskText"] = $row["taskText"];
            
            return $data;
        }
        
        /** 
         * ВОЗВРАТ ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция возвращает активные и выполненные задачи пользователя.
         * 
         * return ['success','uid', active['id', 'week', 'seqNumber', 'taskText'], done['id', 'week', 'seqNumber', 'taskText']] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function getAllTasks($uid) {
            $activeTasks = $this->getActiveTasks($uid);
            $doneTasks = $this->getDoneTasks($uid);
            
            return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'active' => $activeTasks,
                    'done' => $doneTasks
                    ];
        }
        
        /** 
         * ВОЗВРАТ ВСЕХ АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ:
         * 
         * return ['id_week_seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function getActiveTasks($uid){
            $result = $this->queryExecutor->selectQuery("SELECT id, week, seqNumber, taskText FROM $this->table WHERE uid = $uid AND isActive = 1");
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    $activeTasks[$row["id"] . '_' .$row["week"] . '_' .$row["seqNumber"]] = $row["taskText"]; //из-за коллизий применил такой способ добавления элементов в ассоциативный массив
                }
                
                return $activeTasks;
            } else {
                return 0;
            }
        }
        
        /** 
         * ВОЗВРАТ ВСЕХ ВЫПОЛНЕННЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ:
         * 
         * return ['id_week_seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function getDoneTasks($uid){
            $result = $this->queryExecutor->selectQuery("SELECT id, week, seqNumber, taskText FROM $this->table WHERE uid = $uid AND isActive = 0");
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    $doneTasks[$row["id"] . '_' .$row["week"] . '_' .$row["seqNumber"]] = $row["taskText"];
                }
                
                return $doneTasks;
            } else {
                return 0;
            }
        }
        
    }
?>