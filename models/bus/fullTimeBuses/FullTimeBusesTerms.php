<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesTerms extends Model
{
    public $idPeriodo; // lectivo, no_lectivo, agosto
    public $nombre;
    public $nombre_ingles;
    public $validez;
    public $horarios = []; // ints array

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['idPeriodo', 'nombre', 'nombre_ingles'], 'string'],
            ['validez', 'binary'],
        ];
    }

}
