<?php

namespace app\models\bus;

use yii\base\Model;

class BusConnections extends Model
{
    public $ids = []; // ints array
    public $verified; // true || false


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // ids is integer array
            ['ids', 'each', 'rule' => ['integer']],
            // verified must be a boolean value
            ['verified', 'boolean'],
        ];
    }

}
