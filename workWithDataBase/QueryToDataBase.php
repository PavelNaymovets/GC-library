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
         * $week - порядковый номер недели
         */
         
        /** 
         * ОГРАНИЧЕНИЯ ДЛЯ БД:
         * 
         * Таблица с задачами должна иметь название - tasks.
         * Таблица с задачами должна иметь следующие названия и порядок столбцов: 
         * 
         *  id, uid, taskText, isActive, seqNumber, week, dataStamp
         */
         
        /** 
         * РЕГИСТРАЦИЯ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ: 
         * 
         * Функция регистрирует задачу пользователя в БД.
         * 
         * Текст задачи ($taskText) должен передаваться в '' или в "" кавычках. Иначе не выполнится SQL запрос.
         * 
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function putTaskInDataBase($uid, $taskText, $week) {
            $seqNumber = $this->getCountActiveTasksWeek($uid, $week);
            $seqNumber += 1;
            $result = $this->insertIntoQuery("INSERT INTO tasks(uid, taskText, isActive, seqNumber, week) VALUES ($uid, $taskText,'1',$seqNumber, $week)");
            if($result == 1){
                $idQuery = $this->selectQuery("SELECT id FROM tasks WHERE uid = $uid AND seqNumber = $seqNumber AND week = $week");
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
        
        /* ВОЗВРАТ КОЛИЧЕСТВА ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ */
        public function getCountTasks($uid) {
            $result = $this->selectQuery("SELECT * FROM tasks WHERE uid = $uid");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ КОНКРЕТНОЙ НЕДЕЛИ */
        public function getCountActiveTasksWeek($uid, $week) {
            $result = $this->selectQuery("SELECT * FROM tasks WHERE uid = $uid AND isActive = 1 AND week = $week");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ ПО ВСЕМ НЕДЕЛЯМ */
        public function getCountActiveTasks($uid) {
            $result = $this->selectQuery("SELECT * FROM tasks WHERE uid = $uid AND isActive = 1");
            
            return $result->num_rows;
        }
        
        /* ВОЗВРАТ КОЛИЧЕСТВА НЕ АКТИВНЫХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ ПО ВСЕМ НЕДЕЛЯМ */
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
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskIsDone($taskId, $uid, $week) {
            $data = $this->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->getCountActiveTasksWeek($uid, $week);
            $result = $this->updateQuery("UPDATE tasks SET isActive = 0 WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                if($data["seqNumber"] != $activeTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $activeTasks; $i++) {
                        $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1 AND week = $week");
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
            } else {
                return 0;
            }
        }
        
        /** 
         * УДАЛЕНИЕ ЗАДАЧЕЙ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция удаляет задачу из базы данных. Обновляет порядковый номер следующих за этой задач в БД.
         * 
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskIsDeleted($taskId, $uid, $week) {
            $data = $this->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->getCountActiveTasksWeek($uid, $week);
            $result = $this->deleteQuery("DELETE FROM tasks WHERE id = $taskId AND uid = $uid");
            if($result == 1){
                if($data["seqNumber"] != $activeTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $activeTasks; $i++) {
                        $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1 AND week = $week");
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
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskReturnActive($taskId, $uid, $week) {
            $data = $this->getSeqNumberAndTaskText($taskId, $uid);
            $activeTasks = $this->getCountActiveTasksWeek($uid, $week);
            $i = $activeTasks + 2; // +2, чтобы при -2 попасть в нужную seqNumber, а при -1 получить seqNumber на 1 больше, чем было.
            if($activeTasks == 0 && $data['seqNumber'] > 1) {
                $result = $this->updateQuery("UPDATE tasks SET seqNumber = 1 WHERE id = $taskId AND uid = $uid");
                $data['seqNumber'] = 1;
            } else {
                for($i; $i >= $data["seqNumber"] + 2; $i--) { // Сделано увеличение порядкового номера через вычитание, иначе БД работает неправильно. Вставляет одно число во все позиции, а нужно + 1.
                    $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i - 2 AND uid = $uid AND isActive = 1 AND week = $week");
                }
            }
            $result = $this->updateQuery("UPDATE tasks SET isActive = 1 WHERE id = $taskId AND uid = $uid");
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
        
        /** 
         * ОБНОВЛЕНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция обновляет текст задачи.
         * 
         * return ['success','uid','id', 'seqNumber', 'week', 'taskText'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
        public function taskUpdateText($taskId, $uid, $week, $newText) {
            $selectQuery = $this->selectQuery("SELECT week FROM tasks WHERE id = $taskId");
            $row = $selectQuery->fetch_assoc();
            $oldWeek = $row['week'];
            
            if($week == $oldWeek) {
                $result = $this->updateQuery("UPDATE tasks SET taskText = $newText WHERE id = $taskId AND uid = $uid");
            } else {
                $data = $this->getSeqNumberAndTaskText($taskId, $uid); //получаю порядковый номер задачи в старой неделе
                $oldActiveTasks = $this->getCountActiveTasksWeek($uid, $oldWeek); //считаю количество активных задач в старой неделе
                if($data["seqNumber"] != $oldActiveTasks) { //не нужно обновлять порядковый номер следующих задач, если задача последняя в списке
                    $i = $data["seqNumber"] + 1;
                
                    for($i; $i <= $oldActiveTasks; $i++) {
                        $this->updateQuery("UPDATE tasks SET seqNumber = $i - 1 WHERE seqNumber = $i AND uid = $uid AND isActive = 1 AND week = $oldWeek");
                    }
                }
                $activeTasks = $this->getCountActiveTasksWeek($uid, $week);
                $result = $this->updateQuery("UPDATE tasks SET taskText = $newText, week = $week, seqNumber = $activeTasks + 1  WHERE id = $taskId AND uid = $uid");
            }
            
            if($result == 1){
                $data = $this->getSeqNumberAndTaskText($taskId, $uid);
                
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
        // ПОЛУЧЕНИЕ ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ
        //-----------------------------------------------------
        
        /* ФАЙЛ: getAllTasks.php */
        
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
            $result = $this->selectQuery("SELECT id, week, seqNumber, taskText FROM tasks WHERE uid = $uid AND isActive = 1");
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
            $result = $this->selectQuery("SELECT id, week, seqNumber, taskText FROM tasks WHERE uid = $uid AND isActive = 0");
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    $doneTasks[$row["id"] . '_' .$row["week"] . '_' .$row["seqNumber"]] = $row["taskText"];
                }
                
                return $doneTasks;
            } else {
                return 0;
            }
        }
        
        //=====================================================
        // РЕГИСТРАЦИЯ ДОХОДА ПОЛЬЗОВАТЕЛЯ
        //=====================================================
        
        /* ФАЙЛ: incomeReg.php */
        
        /**
         * ПАРАМЕТРЫ ФУНКЦИЙ:
         * 
         * $uid - уникальный номер пользователя;
         * $income - доход пользователя.
         */
         
        /** 
         * ОГРАНИЧЕНИЯ ДЛЯ БД:
         * 
         * Таблица с доходом должна иметь название - income.
         * Таблица с доходом должна иметь следующие названия и порядок столбцов: 
         * 
         *  id, uid, income, dataStamp
         */
         
        /** 
         * ПОЛУЧЕНИЕ ОБЩЕГО ДОХОДА ПОЛЬЗОВАТЕЛЯ: 
         * 
         * Функция возвращает доход зарегистрированный за все время с учетом нового зарегистрированного дохода.
         * 
         * return ['success','uid', 'income', 'totalIncome'] - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
         public function getTotalUserIncome($uid, $income) {
             $result = $this->putUserIncomeInDataBase($uid, $income);
             if($result == 1) {
                $selectQuery = $this->selectQuery("SELECT SUM(income) as totalIncome FROM income WHERE uid = $uid");
                $row = $selectQuery->fetch_assoc();
                $totalIncome = $row["totalIncome"];
                return [
                    'success' => 'ok',
                    'uid' => $uid,
                    'income' => $income,
                    'totalIncome' => $totalIncome
                    ];
             } else {
                 return 0;
             }
         }
         
         /** 
         * РЕГИСТРАЦИЯ ТЕКУЩЕГО ДОХОДА ПОЛЬЗОВАТЕЛЯ:
         * 
         * Функция регистрирует доход пользователя в БД.
         * 
         * return 1 - запрос выполнен.
         * return 0 - запрос не выполнен.
         */
         public function putUserIncomeInDataBase($uid, $income) {
             $result = $this->insertIntoQuery("INSERT INTO income(uid, income) VALUES ($uid, $income)");
             return $result;
         }
         
        //=====================================================
        // ОБНОВЛЕНИЕ ПОРЯДКОВОГО НОМЕРА ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ
        //=====================================================
        
        /* ФАЙЛ: newTasksNumbers.php */
        
        /**
         * ПАРАМЕТРЫ ФУНКЦИЙ:
         * 
         * $uid - уникальный номер пользователя;
         * $data - строка с данными формата: id_номер недели_порядковый номер задачи внутри этой недели.
         * 
         */
         
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
                
                $activeTasks = $this->getCountActiveTasksWeek($uid, $week);
                if(count($dataSeparated) == $activeTasks) {
                    $result = $this->updateQuery("UPDATE tasks SET seqNumber = $seqNumber WHERE id = $taskId AND week = $week");
                }
            }
            
            if($result == 1) {
                $result = $this->selectQuery("SELECT id, seqNumber FROM tasks WHERE uid = $uid AND week = $week AND isActive = 1");
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