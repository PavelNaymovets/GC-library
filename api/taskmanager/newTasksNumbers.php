<?php
    
    //-----------------------------------------------------
    // УПРАВЛЕНИЕ ЗАДАЧЕЙ
    //-----------------------------------------------------
    
    /** 
    * Endpoint для управления задачей пользователя в БД в таблице tasks:
    * 
    * https://hmns.in/hmnsgc/api/taskmanager/newTasksNumbers.php
    * 
    * Параметры POST запроса: uid - уникальный номер пользователя;
    *                         newTasksNumbers - содержит id_номер недели_пордяковый номер задачи.
    *                         Порядковый номер задачи содержит новый порядковый номер задачи для обновления в базе данных.
    */
    
    /* ПОДКЛЮЧЕНИЕ КЛАССОВ РАБОТЫ С БАЗОЙ ДАННЫХ, GET/POST ЗАПРОСАМИ */   
    require_once '../../workWithDataBase/ConnectToDataBase.php';
    require_once '../../workWithDataBase/QueryToDataBase.php';
    require_once '../../getPostHandler/GetPostHandler.php';
    require_once '../../jsonHandler/jsonHandler.php';
    
    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ POST ЗАПРОСА */
    $params = array('uid', 'week', 'newTasksNumbers');
    $getHandler = new GetPostHandler("POST", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $queryToDataBase = new QueryToDataBase($mysql);
    
    /* ОБНОВЛЕНИЕ ДАННЫХ О ЗАДАЧЕ */
    $uid = $getData['uid'];
    $week = $getData['week'];
    $data = $getData['newTasksNumbers'];
    $result = $queryToDataBase->updateSeqNumber($uid, $week, $data);
    
    JsonHandler::echoJSON($result);//вывод информации в формате json на страницу.
    
    /* ЗАКРЫВАЮ СОЕДИНЕНИЕ С БД */
    $connect->closeConnection();
?>