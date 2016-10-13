<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 13/10/16
 * Time: 19:19
 */

namespace app\models\calendar;


use yii\base\Model;

class Calendar extends Model
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