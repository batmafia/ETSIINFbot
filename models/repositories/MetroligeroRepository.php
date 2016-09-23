<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 21:44
 */

namespace app\models\repositories;
use app\models\MetroligeroStop;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;
class MetroligeroRepository
{
    /**
     * @param $busStopId
     * @return MetroligeroStpo
     */
    public static function getMetroligeroStop($origin,$destination)
    {
        $request = Request::get("http://www.metroligero-oeste.es/api/next-stop?origin=$origin&destination=$destination")
            ->expects(Mime::JSON)->send();
        if(!$request->hasErrors())
        {
            $metroligeroObj = new MetroligeroStop();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $metroligeroObj->setAttributes($data);
            if($metroligeroObj->validate())
            {
                return $metroligeroObj;
            }
            else
            {
                print_r($metroligeroObj->getErrors());
            }
        }
        else
        {
            throw new Exception("Repository exception");
        }
    }
}