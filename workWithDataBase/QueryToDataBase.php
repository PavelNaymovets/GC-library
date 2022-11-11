<?php
    /**
     * Класс выполняет стандартные(select, insert_into, update, delete) и специальные операции в БД.
     * 
     * Параметры: $connection - соединение с БД.
    */
    class QueryToDataBase {

        private $connection;

        public function __construct(&$connection) {
            $this->connection = $connection;
        }
        
        //=====================================================
        // СТАНДАРТНЫЕ ОПЕРАЦИИ
        //=====================================================

        /** 
         * SELECT запрос в БД:
         * 
         * return $result - запрос выполнен и количество выбранных из базы строк > 0.
         * return 0 - запрос не выполнен.
         */
        public function selectQuery(string $query) {
            $sqlSelect = $query;
            $result = $this->connection->query($sqlSelect);
            if($result->num_rows > 0){
                return $result;
            } else {
                return 0;
            }
        }

        /** 
         * INSERT INTO запрос в БД:
         * 
         * return 1 - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function insertIntoQuery(string $query) {
            $sqlInsertInto = $query;
            $result = $this->connection->query($sqlInsertInto);
            if($result == true){
                return 1;
            } else {
                return 0;
            }
        }

        /** 
         * UPDATE запрос в БД:
         * 
         * return 1 - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function updateQuery(string $query) {
            $sqlUpdate = $query;
            $result = $this->connection->query($sqlUpdate);
            if($result == true){
                return 1;
            } else {
                return 0;
            }
        }

        /** 
         * DELETE запрос в БД: 
         * 
         * return 1 - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function deleteQuery(string $query) {
            $sqlDelete = $query;
            $result = $this->connection->query($sqlDelete);
            if($result == true){
                return 1;
            } else {
                return 0;
            }
        }
        
        //=====================================================
        // СПЕЦИАЛЬНЫЕ ОПЕРАЦИИ
        //=====================================================
        
        /* ВОЗВРАТ КОЛИЧЕСТВА СТРОК В ТАБЛИЦЕ В БД */
        public function getCountRow(string $tableName) {
            $result = $this->selectQuery("SELECT * FROM $tableName");
            
            return $result->num_rows;
       }
       
        //=====================================================
        // ТАСК-МЕНЕДЖЕР
        //=====================================================
               
        //-----------------------------------------------------
        // РЕГИСТРАЦИЯ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ
        //-----------------------------------------------------
        
        /* ФАЙЛ: addTask.php */
        
        /**
         * ПАРАМЕТРЫ ФУНКЦИЙ:
         * 
         * $uid - уникальный номер пользователя;
         * $taskText - текст задачи; 
         * $taskId - id задачи;
         * $newText - новый текст задачи;
         * $seqNumber - порядковый номер задачи у конкретного пользователя;
         */
         
        /** 
         * ОГРАНИЧЕНИЯ ДЛЯ БД:
         * 
         * Таблица с задачами должна иметь название - tasks.
         * Таблица с задачами должна иметь следующие названия и порядок столбцов: 
         * 
         *  id, uid, taskText, isActive, seqNumber, dataStamp
         */
         
        /** 
         * РЕГИСТРАЦИЯ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ: 
         * 
         * Функция регистрирует задачу пользователя в БД.
         * 
         * Текст задачи ($taskText) должен передаваться в '' или в "" кавычках. Иначе не выполнится SQL запрос.
         * 
         * return ['success','uid','id', 'seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function putTaskInDataBase($uid, $taskText) {
            $seqNumber = $this->getCountActiveTasks($uid);
            $seqNumber += 1;
            $result = $this->insertIntoQuery("INSERT INTO tasks(uid, taskText, isActive, seqNumber) VALUES ($uid, $taskText,'1',$seqNumber)");
            if($result == 1){
                $idQuery = $this->selectQuery("SELECT id FROM tasks WHERE uid = $uid AND seqNumber = $seqNumber");
                $row = $idQuery->fetch_assoc();
                $taskId = $row["id"];
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $seqNumber,
                    'taskText' => $taskText
                    ];
            } else {
                return 0;
            }
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ */
        public function getCountTasks($uid) {
            $result = $this->selectQuery("SELECT * FROM tasks WHERE uid = $uid");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ */
        public function getCountActiveTasks($uid) {
            $result = $this->selectQuery("SELECT * FROM tasks WHERE uid = $uid AND isActive = 1");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА НЕ АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ */
        public function getCountNotActiveTasks($uid) {
            $result = $this->selectQuery("SELECT * FROM tasks WHERE uid = $uid AND isActive = 0");
            
            return $result->num_rows;
        }
               
        //-----------------------------------------------------
        // УПРАВЛЕНИЕ ЗАДАЧАМИ ПОЛЬЗОВАТЕЛЯ
        //-----------------------------------------------------
        
        /* ФАЙЛ: manageTask.php */
        
        /** 
         * 
         * ЗАВЕРШЕНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЕМ:
         * 
         * Функция делает задачу не активной. isActive = 0 в БД. 
         * Обновляет порядковый номер следующих за этой задач в БД.
         * 
         * return ['success','uid','id', 'seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskIsDone($taskId, $uid) {
            $data = $this->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->getCountActiveTasks($uid);
            $result = $this->updateQuery("UPDATE tasks SET isActive = 0 WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                if($data["seqNumber"] != $activeTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $activeTasks; $i++) {
                        $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1");
                    }
                }
                
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $data["seqNumber"],
                    'taskText' => $data["taskText"]
                    ];
            } else {
                return 0;
            }
        }
        
        /** 
         * УДАЛЕНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция удаляет задачу из базы данных. Обновляет порядковый номер следующих за этой задач в БД.
         * 
         * return ['success','uid','id', 'seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskIsDeleted($taskId, $uid) {
            $data = $this->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->getCountActiveTasks($uid);
            $result = $this->deleteQuery("DELETE FROM tasks WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                if($data["seqNumber"] != $activeTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $activeTasks; $i++) {
                        $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1");
                    }
                }
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $data["seqNumber"],
                    'taskText' => $data["taskText"]
                    ];
            } else {
                return 0;
            }
        }
        
        /** 
         * ВОЗВРАЩЕНИЕ АКТИВНОСТИ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция делает завершенную задачу активной. isActive = 1 в БД. 
         * Обновляет порядковый номер следующих за этой задач в БД.
         * 
         * return ['success','uid','id', 'seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskReturnActive($taskId, $uid) {
            $data = $this->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->getCountActiveTasks($uid);
            $i = $activeTasks + 2; // +2, чтобы при -2 попасть в нужную seqNumber, а при -1 получить seqNumber на 1 больше, чем было.
            for($i; $i >= $data["seqNumber"] + 2; $i--) { // Сделано увеличение порядкового номера через вычитание, иначе БД работает неправильно. Вставляет одно число во все позиции, а нужно + 1.
                $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i - 2 AND uid = $uid AND isActive = 1");
            }
            $result = $this->updateQuery("UPDATE tasks SET isActive = 1 WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $data["seqNumber"],
                    'taskText' => $data["taskText"]
                    ];
            } else {
                return 0;
            }
        }
        
        /** 
         * ОБНОВЛЕНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция обновляет текст задачи.
         * 
         * return ['success','uid','id', 'seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskUpdateText($taskId, $uid, $newText) {
            $result = $this->updateQuery("UPDATE tasks SET taskText = $newText WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                $data = $this->getSeqNumberAndTaskText($taskId, $uid);
                
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'id' => $taskId,
                    'seqNumber' => $data["seqNumber"],
                    'taskText' => $data["taskText"]
                    ];
            } else {
                return 0;
            }
        }
        
        /** 
         * ВОЗВРАЩАЕТ seqNumber И taskText КОНКРЕТНОГО ПОЛЬЗОВАТЕЛЯ:
         * 
         * return ["seqNumber","taskText"] - запрос выполнен.
         * return ' ' - запрос не выполнен.
         */
        public function getSeqNumberAndTaskText($taskId, $uid){
            $selectQuery = $this->selectQuery("SELECT seqNumber, taskText FROM tasks WHERE id = $taskId AND uid = $uid");
            $row = $selectQuery->fetch_assoc();
            $data["seqNumber"] = $row["seqNumber"];
            $data["taskText"] = $row["taskText"];
            
            return $data;
        }
        
        //-----------------------------------------------------
        // УПРАВЛЕНИЕ ЗАДАЧАМИ ПОЛЬЗОВАТЕЛЯ
        //-----------------------------------------------------
        
        /* ФАЙЛ: getAllTasks.php */
        
        /** 
         * ВОЗВРАТ ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция возвращает активные и выполненные задачи пользователя.
         * 
         * return ['success','uid', active['id', 'seqNumber', 'taskText'], done['id', 'seqNumber', 'taskText']] - запрос выполнен.
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
         * return ['id_seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        function getActiveTasks($uid){
            $result = $this->selectQuery("SELECT id, seqNumber, taskText FROM tasks WHERE uid = $uid AND isActive = 1");
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    $activeTasks[$row["id"] . '_' .$row["seqNumber"]] = $row["taskText"]; //из-за коллизий применил такой способ добавления элементов в ассоциативный массив
                }
                
                return $activeTasks;
            } else {
                return 0;
            }
        }
        
        /** 
         * ВОЗВРАТ ВСЕХ ВЫПОЛНЕННЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ:
         * 
         * return ['id_seqNumber', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        function getDoneTasks($uid){
            $result = $this->selectQuery("SELECT id, seqNumber, taskText FROM tasks WHERE uid = $uid AND isActive = 0");
            if($result->num_rows > 0){
                $doneTasks = [];
                while($row = $result->fetch_assoc()) {
                    $doneTasks[$row["id"] . '_' .$row["seqNumber"]] = $row["taskText"];
                }
                
                return $doneTasks;
            } else {
                return 0;
            }
        }
    }
?>