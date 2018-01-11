<?php

namespace app\models\repositories;

use app\models\bus\BusStop;
use app\models\bus\fullTimeBuses;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;
use \DateTime;
use \DateTimeZone;


class BusRepository
{

    /**
     * @param $busStopId
     * @return BusStop
     */
    public static function getBusStop($busStopId)
    {
        $request = Request::get("http://api.interurbanos.welbits.com/v1/stop/$busStopId")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $busObj = new BusStop();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $busObj->setAttributes($data);

            if ($busObj->validate()) {
                return $busObj;
            } else {
                print_r($busObj->getErrors());
            }
        } else {
            throw new Exception("Repository exception");
        }

    }

    /**
     * @return
     */
    private static function getFullTimeBuses()
    {
//        $request = Request::get("http://www.etsiinf.upm.es/apps/autobuses/v2/")->expects(Mime::JSON)->send();
        $request = Request::get("https://gist.githubusercontent.com/svg153/33620db198756fd5a680a8d414674949/raw/d95cf16fa7b5686b3474490cb7b44138d8127a2d/buses.json")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $availableLines=[];
            foreach ($data as $key => $line)
            {
                foreach ($line as $k => $lineObject){
                    $myLine = new fullTimeBuses\FullTimeBusesLine();
                    $myLine->setAttributes($lineObject);

                    if ($myLine->validate()) {
                        $availableLines[$lineObject['idLinea']]=$myLine;
                    } else {
                        print_r($myLine->getErrors());
                    }
                }

            }

        }
        else
        {
            throw new Exception("Repository exception");
        }
        return $availableLines;
    }


    public static function getFullTimeBusesOpts($idLine, $origin, $timestamp=false)
    {

        try
        {
            $availableLines = self::getFullTimeBuses();
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }

        $dayType = "";
        if ($idLine==='591' || $idLine==='865') {
            $dayType = 'lectivo';
        }
        if ($idLine==='571') {
            $dayType = 'laboral';
        }
        if ($idLine==='573') {
            $dayType = 'laborsabado';
        }


        $dest = "";
        if ($idLine==='591' && $origin==='Madrid') {
            $dest = 'Aluche >> ETSIINF';
        }
        if ($idLine==='591' && $origin==='ETSIINF') {
            $dest = 'ETSIINF >> Aluche';
        }
        if ($idLine==='865' && $origin==='Madrid') {
            $dest = 'Moncloa >> ETSIINF';
        }
        if ($idLine==='865' && $origin==='ETSIINF') {
            $dest = 'ETSIINF >> Moncloa';
        }
        if ($idLine==='571' && $origin==='Madrid') {
            $dest = 'Aluche >> Boadilla';
        }
        if ($idLine==='571' && $origin==='ETSIINF') {
            $dest = 'Boadilla >> Aluche';
        }
        if ($idLine==='573' && $origin==='Madrid') {
            $dest = 'Moncloa >> Boadilla';
        }
        if ($idLine==='573' && $origin==='ETSIINF') {
            $dest = 'Boadilla >> Moncloa';
        }

        $myDateFormatTimestamp = (($timestamp!=false)?$timestamp:false);
        $nowTimeSTR = self::myDateFormat("D M j G:i:s T Y", $myDateFormatTimestamp, 'Europe/Madrid'); // false for timeestamp
        $nowTime = strtotime($nowTimeSTR);
        $dw = idate("w", $nowTime);
        $dayWeekNumber = ($dw===0)?7:$dw;

        $hours = [];
        foreach ($availableLines[$idLine]['periodos'][$dayType]['horarios'][$dest]['listadoHoras'] as $key => $value)
        {
            if (strpos($key, "$dayWeekNumber") !== FALSE)
            {
                $hours = array_merge($hours, $value['horas']);
            }
        }
        //echo $availableLines[$idLine]['periodos'][$dayType]['horarios'][$dest]['listadoHoras']['12345']['horas'][0];
        return $hours;
    }


    /**
     * http://php.net/manual/es/function.date.php
     * @param  string  $format    [description]
     * @param  boolean $timestamp [description]
     * @param  boolean $timezone  [description]
     * @return [type]             [description]
     */
    public static function myDateFormat($format="r", $timestamp=false, $timezone=false)
    {
        $userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
        $gmtTimezone = new DateTimeZone('GMT');
        $myDateTime = new DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
        $offset = $userTimezone->getOffset($myDateTime);
        return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
    }


}
