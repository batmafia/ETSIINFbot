<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesTermsSchedules extends Model
{
    public $idTerm; // lectivo, no_lectivo, agosto
    public $nameSPA;
    public $nameENG;
    public $valid;
    public $schedules = []; // ints array

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['idTerm', 'nameSPA', 'nameENG'], 'integer'],
            ['valid', 'binary'],
            // lines is integer array
            ['schedules' => ['integer']],
        ];
    }

}
