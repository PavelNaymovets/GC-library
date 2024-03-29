<?php
        
    //-----------------------------------------------------
    // ПОЛУЧЕНИЕ ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ
    //-----------------------------------------------------
    
    /** 
    * Endpoint для получения задач пользователя из БД из таблицы tasks:
    * 
    * https://hmns.in/hmnsgc/projects/toTheYard/api/v1/taskmanager/tasks.php?uid=1
    * 
    * Параметры запроса: uid - уникальный номер пользователя.
    */
    
    /* ПОДКЛЮЧЕНИЕ КЛАССОВ РАБОТЫ С БАЗОЙ ДАННЫХ, GET/POST ЗАПРОСАМИ */

    require_once '../../../workWithDataBase/ConnectToDataBase.php';
    require_once '../../../workWithDataBase/QueryToDataBase.php';
    require_once '../../../workWithDataBase/TaskManagerDB/TaskReturner.php';
    require_once '../../../getPostHandler/GetPostHandler.php';
    require_once '../../../jsonHandler/jsonHandler.php';
    
    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ GET ЗАПРОСА */
    $params = array('uid');
    $getHandler = new GetPostHandler("GET", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $executorQuery = new QueryToDataBase($mysql);
    $taskReturner = new TaskReturner($executorQuery);
    
    /* ПОЛУЧЕНИЕ ВСЕХ ЗАДАЧ ПОЛЬЗОВАТЕЛЯ */
    $result = $taskReturner->getAllTasks($getData['uid']);
    
    /* ВЫВОД ИНФОРМАЦИИ В ФОРМАТЕ JSON НА СТРАНИЦУ */
    JsonHandler::echoJSON($result);
?>