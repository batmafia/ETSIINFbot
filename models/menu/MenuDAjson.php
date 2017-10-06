<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 06/10/17
 * Time: 12:10
 */

namespace app\models\menu;


use yii\base\Model;

class MenuDAjson extends Model
{

    public $title;

    function rules()
    {
        return [
            [['title'], 'string']
        ];
    }

}