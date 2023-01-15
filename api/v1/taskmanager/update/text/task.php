<?php

    //-----------------------------------------------------
    // ОБНОВЛЕНИЕ ТЕКСТА ЗАДАЧИ
    //-----------------------------------------------------
    
    /** 
    * Endpoint для обновления текста задачи пользователя в БД:
    * 
    * https://hmns.in/hmnsgc/projects/toTheYard/api/v1/taskmanager/update/text/task.php?taskId=31&uid=1&week=0
    * 
    * Параметры GET запроса: taskId - id задачи в базе данных;
    *                        uid - уникальный номер пользователя;
    *                        week - номер недели;
    */

    /* ПОДКЛЮЧЕНИЕ КЛАССОВ РАБОТЫ С БАЗОЙ ДАННЫХ, GET/POST ЗАПРОСАМИ */   
    require_once '../../../../../workWithDataBase/ConnectToDataBase.php';
    require_once '../../../../../workWithDataBase/QueryToDataBase.php';
    require_once '../../../../../workWithDataBase/TaskManagerDB/TaskTextUpdater.php';
    require_once '../../../../../workWithDataBase/TaskManagerDB/TaskReturner.php';
    require_once '../../../../../getPostHandler/GetPostHandler.php';
    require_once '../../../../../jsonHandler/jsonHandler.php';

    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ GET ЗАПРОСА */
    $params = array('taskId', 'uid', 'week', 'newText');
    $getHandler = new GetPostHandler("GET", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $executorQuery = new QueryToDataBase($mysql);
    $taskReturner = new TaskReturner($executorQuery);
    $taskTextUpdater = new TaskTextUpdater($executorQuery, $taskReturner);

    /* ЗАДАЧА АКТИВНА */
    $taskId = $getData['taskId']; 
    $uid = $getData['uid'];
    $week = $getData['week'];
    $newText = $getData['newText'];

    $result = $taskTextUpdater->taskUpdateText($taskId, $uid, $week, $newText);

    /* ВЫВОД ИНФОРМАЦИИ В ФОРМАТЕ JSON НА СТРАНИЦУ */
    JsonHandler::echoJSON($result);
    
    /* ЗАКРЫВАЮ СОЕДИНЕНИЕ С БД */
    $connect->closeConnection();
?>