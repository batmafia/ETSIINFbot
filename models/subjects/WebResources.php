<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 20:03
 */

namespace app\models\subjects;


use yii\base\Model;

class WebResources extends Model
{

    public $nombre;
    # @FUTURE: not implemented in UPM API for now
    # public $enlace;
    # public $descripcion;

    function rules()
    {
        return [
            [['nombre'], 'string'],
        ];
    }

}