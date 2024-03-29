# Планировщик задач

Проект содержит код микросервиса по управлению задачами пользователя в планировщике задач. 

Планировщик задач содержит функции:
* Добавить задачу пользователю;
* Получить все задачи пользователя;
* Перевести задачу в статус: выполнено, удалено; 
* Вернуть задачу в активные из выполненных, обновить текст задачи;
* Обновление порядкового номера задачи внутри недели при drag and drop перетаскивании задач на фронте.

### Проект содержит:
* _REST_ веб-сервис для управления задачами пользователя;
* _MySQL_ базу данных для хранения задач пользователей;

### _REST_ веб-сервис
Веб-сервис поддерживает запросы по следующим _URL_:
* `.../api/v1/taskmanager/tasks.php`- GET. Переменные пути: uid
* `.../api/v1/taskmanager/add/task.php`- POST. Переменные в теле запроса: uid, taskText, week
* `.../api/v1/taskmanager/complete/task.php`- GET. Переменные пути: taskId, uid, week
* `.../api/v1/taskmanager/delete/task.php`- GET.  Переменные пути: taskId, uid, week
* `.../api/v1/taskmanager/active/task.php`- GET. Переменные пути: taskId, uid, week
* `.../api/v1/taskmanager/update/text/task.php`- GET. Переменные пути: taskId, uid, week, newText
* `.../api/v1/taskmanager/update/number/tasks.php`- GET. Переменные пути: numbers, uid, week

Переменные в запросах:

* `uid` - уникальный номер пользователя;
* `taskText` - текст задачи;
* `week` - номер недели;
* `taskId` - уникальный номер задачи в базе данных;
* `newText` - новый текст задачи;
* `numbers` - уникальный номер задачи_номер недели;

В ответ на запросы приходит объект с параметрами ответа в формате _JSON_. Пример ответа на запрос `.../api/v1/taskmanager/tasks.php`:
```
{
    "success":"ok",
    "uid":"1",
    "active":{"34_0_1":"Пять, шесть","36_0_2":"Пять,десять","39_0_3":"1010","50_0_4":"семь"},
    "done":0
}
```
Запрос вернул:
* `"success"` - статус запроса;
* `"uid"` - уникальный номер пользователя;
* `"active"` - список активных задач. Формат записи задачи `"id задачи_номер недели_порядковый номер задачи внутри недели":"текст задачи"`;
* `"done"` - список выполненных задач. Формат записи задач такой же, как и у `"active"`.
### База данных
Данные о пользователях и их задачах хранятся в базе данных и управляются СУБД MySQL. Формат хранения данных - таблица со столбцами: 
* `id` - уникальный номер задачи;
* `uid` - уникальный номер пользователя; 
* `taskText` - текст задачи; 
* `isActive` - активность задачи. 1 - активна, 0 - выполнена; 
* `seqNamber` - порядковый номер задачи внутри недели; 
* `week` - номер недели;
* `dataStamp` - дата последнего обновления записи.

### Ссылки по проекту:
* _[api](https://github.com/PavelNaymovets/GC-library/tree/master/api/v1/taskmanager)_ - код эндпоинтов;
* _[getPostHandler](https://github.com/PavelNaymovets/GC-library/tree/master/getPostHandler)_ - обработка GET, POST запросов;
* _[jsonHandler](https://github.com/PavelNaymovets/GC-library/tree/master/jsonHandler)_ - возврат данных на фронт в формате _JSON_;
* _[workWithDataBase](https://github.com/PavelNaymovets/GC-library/tree/master/workWithDataBase)_ - работа с БД.