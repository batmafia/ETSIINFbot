<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 18:13
 */

namespace app\models\subjects;

use yii\base\Model;

class Activities extends Model
{
    # ID
    public $semana;
    public $denominacion;
    public $duracion;
    public $tecnica;
    public $peso;
    public $nota_minima;
    public $presencial;

/*
"281374": {
    "SEMANA": "5",
    "DENOMINACION": "Entrega práctica 1",
    "DURACION": "05:00",
    "TECNICA": "TG: Técnica del tipo Trabajo en Grupo",
    "PESO": "20",
    "NOTA_MINIMA": "4",
    "PRESENCIAL": "No Presencial"
},
"281389": {
    "SEMANA": "15",
    "DENOMINACION": "Entrega práctica 2",
    "DURACION": "15:00",
    "TECNICA": "TG: Técnica del tipo Trabajo en Grupo",
    "PESO": "20",
    "NOTA_MINIMA": "4",
    "PRESENCIAL": "Presencial"
},
*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['semana', 'peso', 'nota_minima'], 'integer'],
            [['denominacion', 'duracion', 'tecnica', 'presencial'], 'string'],
        ];
    }

}
