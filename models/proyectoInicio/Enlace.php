<?php
/**
 * Created by PhpStorm.
 * User: sg153
 * Date: 08/31/16
 * Time: 01:55
 */

namespace app\models\proyectoInicio;


use yii\base\Model;

class Enlace extends Model
{

    public $caption;
    public $link;

    function rules()
    {
        return [
            [['caption', 'link'], 'required'],
            ['caption', 'string'],
            ['link', 'url']
        ];
    }

}