<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesLine extends Model
{
    public $idLine; // ints 591, 865, 571, 573
    public $term = []; // ints array


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['idLine', 'integer'],
            // lines is integer array
            ['term' => ['integer']],
        ];
    }

}
