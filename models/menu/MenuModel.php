<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 29/9/16
 * Time: 12:10
 */

namespace app\models\menu;


use yii\base\Model;

class MenuModel extends Model
{

    public $link;
    public $validFrom;
    public $validTo;
    public $name;

    function rules()
    {
        return [
            [['link', 'name'], 'string'],
            [['validFrom','validTo'], 'integer']
        ];
    }

}