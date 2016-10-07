<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:31
 */

namespace app\models;

use yii\base\Model;

class Subject extends Model
{

    public $codigo;
    public $nombre;
    public $ects;
    public $anio;
    public $semestre;
    public $guia;
    public $depto;
    public $plan;
    public $caracter;
    public $profesores = [];
    public $fecha_actualizacion;

// "codigo":"105000004",
// "nombre":"Matematica discreta II",
// "ects":"3",
// "anio":"2016-17",
// "semestre":"1S",
// "guia":"https:\/\/www.upm.es\/comun_gauss\/publico\/guias\/2016-17\/1S\/GA_10II_105000004_1S_2016-17.pdf",
// "depto":"Matem\u00e1tica Aplicada a las Tecnolog\u00edas D la Informaci\u00f3n y las Comunicaciones",
// "plan":"Grado en Ingenieria Informatica",
// "caracter":"Obligatoria",
// "profesores":[
    // {
    // "nombre":"Maria Gloria",
    // "apellidos":"Sanchez Torrubia",
    // "email":"mariagloria.sanchez@upm.es",
    // "despacho":"1318",
    // "coordinador":false,
    // "tutorias":[
    // {
    // "dia":null,
    // "hora_inicio":null,
    // "hora_fin":null,
    // "observaciones":null
    // }
    // ]
// },
// {
// "nombre":"Blanca Nieves",
// "apellidos":"Castro Gonzalez",
// "email":"nieves.castro.gonzalez@upm.es",
// "despacho":"1319",
// "coordinador":false,
// "tutorias":[
// {
// "dia":null,
// "hora_inicio":null,
// "hora_fin":null,
// "observaciones":null
// }
// ]
// },
// {
// "nombre":"M. del Carmen",
// "apellidos":"Escribano Iglesias",
// "email":"mariadelcarmen.escribano@upm.es",
// "despacho":"1303",
// "coordinador":false,
// "tutorias":[
// {
// "dia":null,
// "hora_inicio":null,
// "hora_fin":null,
// "observaciones":null
// }
// ]
// },
// {
// "nombre":"Victoria",
// "apellidos":"Zarzosa Rodriguez",
// "email":"victoria.zarzosa@upm.es",
// "despacho":"1313",
// "coordinador":false,
// "tutorias":[
// {
// "dia":null,
// "hora_inicio":null,
// "hora_fin":null,
// "observaciones":null
// }
// ]
// }
// ],
// "fecha_actualizacion":"June 27, 2016, 9:08 pm"
// }


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre','anio','semestre','guia','depto','plan','caracter','fecha_actualizacion'], 'string'],
            ['profesores', 'safe'],
            [['codigo','ects'], 'integer'],
        ];
    }

    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $profesores = [];
        foreach($this->profesores as $i=>$p)
        {
            $profesor = new Teacher();
            $profesor->setAttributes($p);
            if($profesor->validate())
            {
                $profesores[] = $profesor;
            }

        }
        $this->profesores = $profesores;

    }


}
