<?php
    
    //-----------------------------------------------------
    // УПРАВЛЕНИЕ ЗАДАЧЕЙ
    //-----------------------------------------------------
    
    /** 
    * Endpoint для управления задачей пользователя в БД в таблице tasks:
    * 
    * https://hmns.in/hmnsgc/api/taskmanager/manageTask.php
    * 
    * Параметры POST запроса: action - действие над задачей (done, delete, returnActive, update). Слово действия в запросе указывать без '' или "" кавычек.
    *                    Иначе не будет работать логика скрипта в блоке if else. Слово действия не чувствительно к регистру (например можно писать DONE, dOne, doNE и пр.)
    *                    taskId - id задачи в базе данных;
    *                    uid - уникальный номер пользователя;
    *                    week - номер недели;
    *                    newText - новый текст задачи. текст задачи. В запросе указывать в '' или в "" кавычках. Иначе не выполнится SQL запрос.
    */
    
    /** ОПЕРАЦИИ НАД ЗАДАЧЕЙ:
    * 
    * Параметры операции: done - задача выполнена;
    *                     delete - задача удалена;
    *                     returnActive - вернуть задачу из выполненных в активные;
    *                     update - обновить текст задачи.
    */
    
    /* ПОДКЛЮЧЕНИЕ КЛАССОВ РАБОТЫ С БАЗОЙ ДАННЫХ, GET/POST ЗАПРОСАМИ */   
    require_once '../../workWithDataBase/ConnectToDataBase.php';
    require_once '../../workWithDataBase/QueryToDataBase.php';
    require_once '../../getPostHandler/GetPostHandler.php';
    require_once '../../jsonHandler/jsonHandler.php';
    
    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ POST ЗАПРОСА */
    $params = array('action', 'taskId', 'uid', 'week', 'newText');
    $getHandler = new GetPostHandler("POST", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $queryToDataBase = new QueryToDataBase($mysql);
    
    /* ОПЕРАЦИИ НАД ЗАДАЧЕЙ */
    $action = mb_strtolower($getData['action']);
    $taskId = $getData['taskId']; 
    $uid = $getData['uid'];
    $week = $getData['week'];
    $newText = $getData['newText'];
    
    if($action == 'done') { //задача завершена.
        $result = $queryToDataBase->taskIsDone($taskId, $uid, $week);
        JsonHandler::echoJSON($result);//вывод информации в формате json на страницу.
    } else if($action == 'delete') { //задача удалена.
        $result = $queryToDataBase->taskIsDeleted($taskId, $uid, $week);
        JsonHandler::echoJSON($result);
    } else if($action == 'returnactive') { //активность задачи возвращена.
        $result = $queryToDataBase->taskReturnActive($taskId, $uid, $week);
        JsonHandler::echoJSON($result);
    } else if($action == 'update') { //обновлен текст задачи
        $result = $queryToDataBase->taskUpdateText($taskId, $uid, $week, $newText);
        JsonHandler::echoJSON($result);
    }
    
    /* ЗАКРЫВАЮ СОЕДИНЕНИЕ С БД */
    $connect->closeConnection();
?>