<?php

    //-----------------------------------------------------
    // ОБНОВЛЕНИЕ ПОРЯДКОВОГО НОМЕРА ЗАДАЧ
    //-----------------------------------------------------
    
    /** 
    * Endpoint для обновления порядкового номера задач пользователя в БД:
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
    require_once '../../../../../workWithDataBase/TaskManagerDB/TaskNumberUpdater.php';
    require_once '../../../../../workWithDataBase/TaskManagerDB/TaskReturner.php';
    require_once '../../../../../getPostHandler/GetPostHandler.php';
    require_once '../../../../../jsonHandler/jsonHandler.php';

    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ GET ЗАПРОСА */
    $params = array('uid', 'week', 'numbers');
    $getHandler = new GetPostHandler("GET", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $executorQuery = new QueryToDataBase($mysql);
    $taskReturner = new TaskReturner($executorQuery);
    $taskNumberUpdater = new TaskNumberUpdater($executorQuery, $taskReturner);

    /* ОБНОВЛЕНИЕ ПОРЯДКОВОГО НОМЕРА ЗАДАЧ */
    $uid = $getData['uid'];
    $week = $getData['week'];
    $data = $getData['numbers'];
    
    $result = $taskNumberUpdater->updateSeqNumber($uid, $week, $data);

    /* ВЫВОД ИНФОРМАЦИИ В ФОРМАТЕ JSON НА СТРАНИЦУ */
    JsonHandler::echoJSON($result);
    
    /* ЗАКРЫВАЮ СОЕДИНЕНИЕ С БД */
    $connect->closeConnection();
?>