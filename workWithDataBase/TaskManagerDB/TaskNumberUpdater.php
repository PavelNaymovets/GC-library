<?php
    /**
     * Класс описывает изменение порядкового номера задач пользователя в БД.
     * 
     * Файл: api/v1/taskmanager/update/number/tasks.php
     * 
     * Параметры: $queryExecutor - выполняет CRUD операции в БД.
     *            $tasksReturner - возвращает информацию о задачах из БД.
     *            $table - имя таблицы для запросов(формат: tasks_название_проекта).
    */
    class TaskNumberUpdater {

        private $queryExecutor;
        private $tasksReturner;
        private $table;

        public function __construct(&$queryExecutor, $tasksReturner) {
            $this->queryExecutor = $queryExecutor;
            $this->tasksReturner = $tasksReturner;
            $this->table = $queryExecutor->getTable();
        }
        
        /**
         * ОБНОВЛЕНИЕ ПОРЯДКОВОГО НОМЕРА ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция обновляет порядковый номер задачи пользователя внтури недели.
         * 
         * return 1 - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
         
        public function updateSeqNumber($uid, $week, $data) {
            $dataSeparated = explode(" ", $data);
            foreach ($dataSeparated as $value) {
                $var = explode("_", $value);
                $taskId = $var[0];
                $seqNumber = $var[1];
                
                $activeTasks = $this->tasksReturner->getCountActiveTasksWeek($uid, $week);
                if(count($dataSeparated) == $activeTasks) {
                    $result = $this->queryExecutor->updateQuery("UPDATE $this->table SET seqNumber = $seqNumber WHERE id = $taskId AND week = $week");
                }
            }
            
            if($result == 1) {
                $result = $this->queryExecutor->selectQuery("SELECT id, seqNumber FROM $this->table WHERE uid = $uid AND week = $week AND isActive = 1");
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()) {
                        $newTasksNumbers[$row['id']] = $row['seqNumber'];
                    }
                            
                    return [
                            'success' => 'ok',
                            'uid' => $uid,
                            'week' => $week,
                            'newTasksNumbers' => $newTasksNumbers
                            ];
                }
            } else {
                
                return 0;
            }
            
        }
    }
?>