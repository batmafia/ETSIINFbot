<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 4/2/16
 * Time: 12:56
 */

namespace app\models\subjects;

use yii\base\Model;

class MyCenter extends Model
{

    public $codigo;
    public $nombre;
    public $titulaciones = [];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['codigo', 'string'],
            ['nombre', 'filter', 'filter' => function($name)
            {
                return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
            }],
            ['titulaciones', 'each', 'rule' => ['string']],
        ];
    }
}