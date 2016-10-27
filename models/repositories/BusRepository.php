<?php

namespace app\models\repositories;

use app\models\bus\BusStop;
use app\models\bus\fullTimeBuses;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

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
    public static function getFullTimeBuses()
    {
        $request = Request::get("http://www.etsiinf.upm.es/apps/autobuses/v2/")->expects(Mime::JSON)->send();
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
            echo $availableLines['591']['periodos']['lectivo']['horarios']['Aluche >> ETSIINF']['listadoHoras']['12345']['horas'][0];
        }
        else
        {
            throw new Exception("Repository exception");
        }
        return $availableLines;
    }

    public static function getFullTimeBusesOpts($idLine,$origin)
    {
        $request = Request::get("http://www.etsiinf.upm.es/apps/autobuses/v2/")->expects(Mime::JSON)->send();
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
            echo $availableLines['591']['periodos']['lectivo']['horarios']['Aluche >> ETSIINF']['listadoHoras']['12345']['horas'][0];
        }
        else
        {
            throw new Exception("Repository exception");
        }
        return $availableLines;
    }


}
