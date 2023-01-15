<?php
    /**
     * Класс описывает регистрацию задачи пользователя в БД.
     * 
     * Файл: api/v1/taskmanager/add/task.php
     * 
     * Параметры: $queryExecutor - выполняет CRUD операции в БД.
     *            $tasksReturner - возвращает информацию о задачах из БД.
     *            $table - имя таблицы для запросов(формат: tasks_название_проекта).
    */
    class TaskRegistrator {

        private $queryExecutor;
        private $tasksreturner;
        private $table;

        public function __construct(&$queryExecutor, $tasksreturner) {
            $this->queryExecutor = $queryExecutor;
            $this->tasksreturner = $tasksreturner;
            $this->table = $queryExecutor->getTable();
        }

        public function putTaskInDataBase($uid, $taskText, $week) {
            $seqNumber = $this->tasksreturner->getCountActiveTasksWeek($uid, $week);
            $seqNumber += 1;
            $result = $this->queryExecutor->insertIntoQuery("INSERT INTO $this->table(uid, taskText, isActive, seqNumber, week) VALUES ($uid, $taskText,'1',$seqNumber, $week)");
            if($result == 1){
                $idQuery = $this->queryExecutor->selectQuery("SELECT id FROM $this->table WHERE uid = $uid AND seqNumber = $seqNumber AND week = $week");
                $row = $idQuery->fetch_assoc();
                $taskId = $row["id"];
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $seqNumber,
                    'week' => $week,
                    'taskText' => $taskText
                    ];
            } else {
                return 0;
            }
        }
    }
?>