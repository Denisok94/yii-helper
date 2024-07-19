<?php

namespace denisok94\helper\yii2\traits;

use Yii;
use yii\helpers\FileHelper;
use denisok94\helper\yii2\Helper;

/**
 * Other trait
 */
trait Other
{
    /**
     * консольная команда
     * @param string $path commands Controller/action,
     * @param array $params параметры команды,
     * @param boolean $sync фоновая задача или нет,
     * @param string $out путь лог файла с результатом команды,
     * @param string $error путь лог файла с ошибками,
     */
    public static function exec(string $path, array $params, bool $sync = true, string $out = 'runtime/logs/consoleOut', string $error = 'runtime/logs/consoleError')
    {
        $dir = Yii::$app->getBasePath();
        $dt = Helper::currentDate("d.m.Y");
        $syncStr = $sync ? '&' : '';
        $json = addcslashes(Helper::toJson($params), '"');
        $command = "php $dir/yii $path --json=\"$json\" >> $dir/$out.$dt.log 2>> $dir/$error.$dt.log $syncStr";
        // exec($command);
        exec($command, $output, $return_val);
        if ($return_val != 0) {
            $id = microtime(true);
            Other::log('execError', "$id | error code: $return_val, command: $command, out: " . Helper::toJson($output));
            return "ERROR in exec '$path', code: $return_val, id: $id\n. learn more in '/runtime/logs/execError.$dt.log'";
        }
    }

    /**
     * Создать/Добавить/обновить кэш
     * @param string $name имя кэша,
     * @param array $array данные, которые надо за кэшировать,
     * @return bool успех записи
     */
    public static function setCache(string $name, array $array)
    {
        $fileCache = Yii::$app->getBasePath() . "/cache/$name.json";
        if ((file_exists($fileCache))) {
            $oldArray = Helper::toArray(file_get_contents($fileCache));
            $newArray = array_merge($oldArray, $array);
        } else {
            $dirCache = pathinfo($fileCache, PATHINFO_DIRNAME);
            if (!is_dir($dirCache)) self::createDirCache($dirCache);
            $newArray = $array;
        }
        $fpc = file_put_contents($fileCache, Helper::toJson($newArray), LOCK_EX);
        return ($fpc === false) ? false : true;
    }

    /**
     * Получить кэш
     * @param string $name имя кэша,
     * @return array|bool
     */
    public static function getCache(string $name)
    {
        $fileCache = Yii::$app->getBasePath() . "/cache/$name.json";
        if (file_exists($fileCache)) {
            return Helper::toArray(file_get_contents($fileCache));
        } else {
            return false;
        }
    }

    /**
     * Удалить кэш
     * @param string $name имя кэша,
     * @return bool
     */
    public static function deleteCache(string $name)
    {
        $fileCache = Yii::$app->getBasePath() . "/cache/$name.json";
        return FileHelper::unlink($fileCache);
    }

    /**
     * Очистить кэш
     * @return bool
     */
    public static function clearCache()
    {
        return false;
    }

    /**
     * @param string $dirCache
     */
    private static function createDirCache(string $dirCache)
    {
        FileHelper::createDirectory($dirCache);
        file_put_contents($dirCache . "/.gitignore", "*\n!.gitignore\n");
    }

    /**
     * Записать логи
     * @param string $name имя файла
     * @param string $value текст лога
     */
    public static function log(string $name, string $value)
    {
        $dir = Yii::$app->getBasePath();
        $dt = Helper::currentDate("d.m.Y");
        file_put_contents("$dir/runtime/logs/$name.$dt.log", Helper::currentDt() . ' | ' . $value . "\n", FILE_APPEND);
    }
}
