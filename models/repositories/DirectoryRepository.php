<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:16
 */
namespace app\models\repositories;

use app\models\directory\DirectoryResponse;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

class DirectoryRepository
{

    public static function getDirectoryInfo($text)
    {
        $request = Request::get("http://www.etsiinf.upm.es/apps/personal/v2/?texto=$text")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            print_r($request);

            $dirObj = new DirectoryResponse();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $dirObj->setAttributes($data);

            if ($dirObj->validate()) {
                return $dirObj;
            } else {
                print_r($dirObj->getErrors());
            }
        } else {
            throw new Exception("Repository exception");
        }
    }

}