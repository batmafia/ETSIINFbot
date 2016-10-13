
<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesBusLines extends Model
{
    public $lines = []; // ints array


    /**
    * @return array the validation rules.
    */
    public function rules()
    {
        return [
            // lines is integer array
            ['lines' => ['integer']],
        ];
    }

}
