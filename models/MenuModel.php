<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 29/9/16
 * Time: 12:10
 */

namespace app\models;


use yii\base\Model;

class MenuModel extends Model
{

    public $link;
    public $validFrom;
    public $validTo;
    public $caption;

    function rules()
    {
        return [
            [['link', 'caption'], 'string'],
            [['validFrom','validTo'], 'integer']
        ];
    }

}