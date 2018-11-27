<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 13.08.2018
 * Time: 15:29
 */

use Phalcon\Http\Response;

class SupportClass
{

    public static function checkInteger($var){
        return ((string)(int)$var == $var);
    }

    public static function checkPositiveInteger($var){
        return ((string)(int)$var == $var);
    }

    /*public static function checkDouble($var){
        return ((string)(double)$var == $var);
    }*/

    public static function pullRegions($filename, $db = null){
        if($db == null)
            $db = Phalcon\DI::getDefault()->getDb();

        $content = file_get_contents($filename);
        //$content = trim($content);
        $str = str_replace("\n", '', $content);
        $str = str_replace('osmId', '"osmId"', $str);
        $str = str_replace('name', '"name"', $str);
        $str = str_replace("'", '"', $str);
        $regions = json_decode($str,true);
        //$res = json_decode($str,true);

        $db->begin();
        foreach($regions as $region){
            $regionObj = Regions::findFirstByRegionid($region['osmId']);
            if(!$regionObj) {
                $regionObj = new Regions();
                $regionObj->setRegionId($region['osmId']);
            }
            $regionObj->setRegionName($region['name']);

            if (!$regionObj->save()) {
                $db->rollback();
                $errors = [];
                foreach ($regionObj->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                return ['result' => false,'errors' => $errors];
            }
        }
        $db->commit();
        return ['result' => true];
    }

    public static function transformControllerName($controllerName){
        $new_controller = array();
        for($i=0;$i<strlen($controllerName);$i++)
        {
            $lowercase = strtolower($controllerName[$i]);
            if(ord($controllerName[$i])<=90 && $i>0)
            {
                $new_controller[]='_';
            }
            $new_controller[]=$lowercase;
        }
        $str = implode('',$new_controller);
        return implode('',$new_controller);
    }

    public static function writeMessageInLogFile($message){
        $file = fopen(BASE_PATH.'/public/logs.txt', 'a');
        fwrite($file,'Дата: '.date('Y-m-d H:i:s').' - '.$message."\r\n");
        fflush($file);
        fclose($file);
    }

    /**
     * Optimized algorithm from http://www.codexworld.com
     *
     * @param float $latitudeFrom
     * @param float $longitudeFrom
     * @param float $latitudeTo
     * @param float $longitudeTo
     *
     * @return float [m]
     */
    public static function codexworldGetDistanceOpt($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $rad = M_PI / 180;
        //Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);

        return acos($dist) / $rad * 60 *  1853;
    }

    public static function translateInPhpArrFromPostgreArr($str){
        //$str = json_decode($str);
        $str[0] = '[';
        $str[strlen($str) - 1] = ']';

        $str = str_replace('"{', '{', $str);
        $str = str_replace('}"', '}', $str);
        $str = stripslashes($str);

        $str = json_decode($str, true);
        return $str;
    }

    public static function getResponseWithErrors($object){
        $response = new Response();
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        $response->setJsonContent(
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ]);

        return $response;
    }

    public static function getResponseWithErrorsFromArray($errors){
        $response = new Response();
        $response->setJsonContent(
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ]);

        return $response;
    }
}