<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 21:44
 */

namespace app\models\repositories;
use app\models\metroligero\MetroligeroApiResponse;
use app\models\metroligero\MetroligeroStop;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;
class MetroligeroRepository
{
    /**
     * @param $origin, destination
     * @return MetroligeroStop
     */
    public static function getMetroligeroStop($origin,$destination)
    {
        $request = Request::get("https://www.metroligero-oeste.es/api/next-stop?origin=$origin&destination=$destination")
            ->expects(Mime::JSON)->send();
        if(!$request->hasErrors())
        {

            $metroligeroObj = new MetroligeroApiResponse(["dataClass"=>MetroligeroStop::className()]);
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $metroligeroObj->setAttributes($data);
            if($metroligeroObj->validate())
            {
                return $metroligeroObj->data;
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