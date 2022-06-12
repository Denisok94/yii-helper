<h1 align = "center"> Yii Helper Class </h1>

# Installation

Run:

```bash
composer require --prefer-dist denisok94/yii-helper
# or
php composer.phar require --prefer-dist denisok94/yii-helper
```

or add to the `require` section of your `composer.json` file:

```json
"denisok94/yii-helper": "*"
```

```bash
composer update
# or
php composer.phar update
```

# Using

- [StatusController](#StatusController)
- [ConsoleController](#ConsoleController)
- [Helper](#Helper)

# **StatusController**

For communication in the json format.

| Method | Parameters | default code | Description |
|----------------|:---------:|:---------:|:----------------|
| send | $data | 200 | custom responses |
| sendSuccess | $data | 200 | Report Success |
| sendResponse | $data, $message, $status, $code | 200 | custom responses |
| sendError | $message, $status, $code | 400 | Report an error |
| sendBadRequest | $message | 400 | |
| sendUnauthorized | $message | 401 | |
| sendForbidden | $message | 403 | |
| sendNotFound | $message | 404 | |
| sendInternalServerError | $message | 500 | |
| getPost | $name | -- | Получить параметр из сообщения |
| success | $data | -- | outdated |
| error | $error, $text, $data | -- | outdated |

```php
namespace app\controllers;
use \denisok94\helper\yii2\StatusController;

class MyController extends StatusController
{
    // code
}
```

```php
// получить все данные
$message = $this->post; // array
// получить параметр из данных
$phone = $this->getPost('phone'); // phone or null
```

Report Success
```php
// Сообщить об успешной обработки
return $this->sendSuccess(); // http status code 200
// ['code' => '200', 'status' => 'OK', 'data' => []];
// Вернуть результат работы
return $this->sendSuccess($data);  // http status code 200
// ['code' => '200', 'status' => 'OK', 'data' => $data];
```

Report an error
```php
return $this->sendError(); // http status code 400
// ['code' => '400', 'status' => 'FAIL', 'message' => 'Error', 'data' => []]
return $this->sendError($message); // http status code 400
// ['code' => '400', 'status' => 'FAIL', 'message' => $message, 'data' => []]
return $this->sendError($message, $data); // http status code 400
// ['code' => '400', 'status' => 'FAIL', 'message' => $message, 'data' => $data]
return $this->sendError($message, $data, 401); // http status code 401
// ['code' => '401', ...]

// return ['code', 'status', 'message'];
return $this->sendBadRequest(); // http status code 400
return $this->sendUnauthorized(); // http status code 401
return $this->sendForbidden(); // http status code 403
return $this->sendNotFound(); // http status code 404
return $this->sendInternalServerError(); // http status code 500

if (!$this->post) {
    return $this->sendBadRequest("Request is null"); // http status code 400
    // ['code' => '400', 'status' => 'FAIL', 'message' => 'Request is null']
}

try {
    //code...
} catch (\Exception $e) {
    return $this->sendInternalServerError($e->getMessage()); // http status code 500
    // ['code' => '500', 'status' => 'FAIL', 'message' => '...']
}
```

Custom response format
```php
return $this->send([...]); // http status code 200
// [...];
return $this->send(['code' => 204]); // http status code 204
// ['code' => '204'];
return $this->send(['code' => 201, 'data' => $data]); // http status code 201
// ['code' => '201', 'data' => $data];

return $this->sendResponse($data); // http status code 200
// ['code' => '200', 'status' => 'OK', 'message' => '', 'data' => $data]
return $this->sendResponse($data, $message); // http status code 200
// ['code' => '200', 'status' => 'OK', 'message' => $message, 'data' => $data]
return $this->sendResponse($data, $message, $status, 999); // http status code 999
// ['code' => '999', 'status' => $status, 'message' => $message, 'data' => $data]
```

# **ConsoleController**

```php
namespace app\commands;
use \denisok94\helper\yii2\ConsoleController;

class MyController extends ConsoleController
{
    // code
}
```

Вызвать `action` консольного контроллера:
```php
use \denisok94\helper\yii2\Helper as H;
H::exec('controller/action', [params]);
```
> Консольный контроллер, не подразумевает ответ. 
Вся выводящая информация (echo, print и тд) будет записана в лог файл. 
При вызове через `H::exec()`, по умолчанию логи находятся в `/runtime/logs/consoleOut.XXX.log` (можно переопределить)

Получить переданные параметры
```php
$init = $this->params;
```

Пример:
```php
namespace app\commands;
use \denisok94\helper\yii2\ConsoleController;

class MyController extends ConsoleController
{
	public function actionTest()
	{
		$init = $this->params; // ['test' => 'test1']
		$test = $this->params['test']; // 'test1'
	}
}
//
use \denisok94\helper\yii2\Helper as H;
H::exec('my/test', ['test' => 'test1']);
```
# **Helper**

```php
use \denisok94\helper\yii2\Helper as H;
H::methodName($arg);
```

`yii2\Helper` наследует все от [Helpers](https://github.com/Denisok94/helper)

| Method | Description |
|----------------|:----------------|
| exec | Выполнить консольную команду |
| log | Записать данные в лог файл. Файлы хранятся в `runtime/logs/` |
| setCache | Запомнить массив в кэш |
| getCache | Взять массив из кэша |
| deleteCache | Удалить кэш |
| ~~clearCache~~ | | |


___
> `setCache`/`getCache`.
В кэш можно сохранить результат запроса из бд, который часто запрашивается, например для фильтра.
К тому же, этот фильтр, может быть, использоваться несколько раз на странице или сама страница с ним, может, многократно обновляться/перезагружаться.

```php
namespace app\components;
use app\components\H;

class Filter
{
    //.....
    /**
     * @return array
     */
    public static function getTypes()
    {
        $types = H::getCache('types'); // dir: app/cache/types.json
        if ($types) {
            return $types;
        } else {
            $types = \app\models\Types::find()
                ->select(['id', 'name'])->all();
            $array = [];
            foreach ($types as $key => $value) {
                $array[$value->id] = ucfirst($value->name);
            }
            H::setCache('types', $array);
            return $array;
        }
    }
}
```
