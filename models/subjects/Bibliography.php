<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 20:03
 */

namespace app\models\subjects;


use yii\base\Model;

class Bibliography extends Model
{

    public $titulo;
    # @FUTURE: not implemented in UPM API for now
    # public $autores = [];
    # public $isbn;
    # public $descripcion;

    function rules()
    {
        return [
            [['titulo'], 'string'],
        ];
    }

}