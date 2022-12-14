<?php

    //-----------------------------------------------------
    // РЕГИСТРАЦИЯ ЗАДАЧИ В БД
    //-----------------------------------------------------
    
    /** 
    * Endpoint для регистрации задачи пользователя в БД в таблице tasks:
    * 
    * https://hmns.in/hmnsgc/api/taskmanager/addTask.php
    * 
    * Параметры POST запроса: uid - уникальный номер пользователя;
    *                         taskText - текст задачи. В запросе указывать в '' или в "" кавычках. Иначе не выполнится SQL запрос;
    *                         week - номер недели.
    */
    
    /* ПОДКЛЮЧЕНИЕ КЛАССОВ РАБОТЫ С БАЗОЙ ДАННЫХ, GET/POST ЗАПРОСАМИ */   
    require_once '../../workWithDataBase/ConnectToDataBase.php';
    require_once '../../workWithDataBase/QueryToDataBase.php';
    require_once '../../getPostHandler/GetPostHandler.php';
    require_once '../../jsonHandler/jsonHandler.php';
    
    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ GET ЗАПРОСА */
    $params = array('uid', 'taskText', 'week');
    $getHandler = new GetPostHandler("POST", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $queryToDataBase = new QueryToDataBase($mysql);
    
    /* РЕГИСТРАЦИЯ ЗАДАЧИ ПОЛЬЗОВАТЕЛЯ В БД */
    $uid = $getData['uid'];
    $taskText = $getData['taskText'];
    $week = $getData['week'];
    $result = $queryToDataBase->putTaskInDataBase($uid, $taskText, $week);
    
    /* ВЫВОД ИНФОРМАЦИИ В ФОРМАТЕ JSON НА СТРАНИЦУ */
    JsonHandler::echoJSON($result);
    
    /* ЗАКРЫВАЮ СОЕДИНЕНИЕ С БД */
    $connect->closeConnection();
?>