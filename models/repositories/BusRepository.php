<?php

namespace app\models\repositories;

use app\models\BusStop;
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
        if(!$request->hasErrors())
        {
            $busObj = new BusStop();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $busObj->setAttributes($data);

            if($busObj->validate())
            {
                return $busObj;
            }
            else
            {
                print_r($busObj->getErrors());
            }
        }
        else
        {
            throw new Exception("Repository exception");
        }

    }

}
