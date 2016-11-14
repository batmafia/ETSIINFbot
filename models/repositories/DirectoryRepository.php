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

            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $availablePersonal = [];

            foreach ($data as $directorioFI){
                if ($directorioFI !== null){

                    foreach ($directorioFI as $personal){

                        $person = new DirectoryResponse();
                        $person->setAttributes($personal);

                        if ($person->validate()) {
                            $availablePersonal[]=$person;
                        } else {
                            print_r($personal->getErrors());
                        }
                    }
                }

                return $availablePersonal;
            }


        } else {
            throw new Exception("Repository exception");
        }
    }

}