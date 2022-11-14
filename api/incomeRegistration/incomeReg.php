<?php
    
    //-----------------------------------------------------
    // РЕГИСТРАЦИЯ ДОХОДА ПОЛЬЗОВАТЕЛЯ
    //-----------------------------------------------------
    
    /** 
    * Endpoint для регистрации дохода пользователя в БД в таблице income:
    * 
    * https://hmns.in/hmnsgc/api/incomeregistration/incomeReg.php?uid=2147483647&income=1000
    * 
    * Параметры запроса: uid - уникальный номер пользователя;
    *                    income - доход пользователя.
    */
    
    /* ПОДКЛЮЧЕНИЕ КЛАССОВ РАБОТЫ С БАЗОЙ ДАННЫХ, GET/POST ЗАПРОСАМИ */   
    require_once '../../workWithDataBase/ConnectToDataBase.php';
    require_once '../../workWithDataBase/QueryToDataBase.php';
    require_once '../../getPostHandler/GetPostHandler.php';
    require_once '../../jsonHandler/jsonHandler.php';
    
    /* ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ GET ЗАПРОСА */
    $params = array('uid', 'income');
    $getHandler = new GetPostHandler("GET", $params);
    $getData = $getHandler->getDataFromQuery();
    
    /* ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ */
    $connect = new ConnectToDataBase("localhost","a0256806_pasha","e123456X","a0256806_pasha");
    $connect->openConnection();
    $mysql = $connect->getConnection();
    $queryToDataBase = new QueryToDataBase($mysql);
    
    /* РЕГИСТРАЦИЯ ДОХОДА И ПОЛУЧЕНИЕ ОБЩЕГО ДОХОДА ПОЛЬЗОВАТЕЛЯ ЗА ВСЕ ВРЕМЯ  */
    $result = $queryToDataBase->getTotalUserIncome($getData['uid'], $getData['income']);
    
    /* ВЫВОД ИНФОРМАЦИИ В ФОРМАТЕ JSON НА СТРАНИЦУ */
    JsonHandler::echoJSON($result);
    
    /* ЗАКРЫВАЮ СОЕДИНЕНИЕ С БД */
    $connect->closeConnection();
?>