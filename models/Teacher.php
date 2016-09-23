<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:32
 */

namespace app\models;

use yii\base\Model;

class Teacher extends Model
{
    public $nombre;
    public $apellidos;
    public $email;
    public $despacho;
    public $coordinador;
    public $tutorias = [];

/*{
    "nombre":"Victor",
    "apellidos":"Gimenez Martinez",
    "email":"victor.gimenez@upm.es",
    "despacho":"1307",
    "coordinador":false,
    "tutorias":[
        {
            "dia":"1",
            "hora_inicio":"13:00",
            "hora_fin":"15:00",
            "observaciones":null
        },
        {
            "dia":"3",
            "hora_inicio":"13:00",
            "hora_fin":"15:00",
            "observaciones":null
        },
        {
            "dia":"4",
            "hora_inicio":"13:00",
            "hora_fin":"15:00",
            "observaciones":null
        }
    ]
},*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre','apellidos','email','despacho'], 'string'],
            ['coordinador', 'boolean'],
            ['tutorias', 'safe'],
        ];
    }


    public function getInfo(){

        if($this->coordinador){
            $message="El profesor $this->nombre $this->apellidos (coordinador de la asignatura) puedes encontrarle en el despacho $this->despacho o puedes contactar
            con el mediante su correo electrónico $this->email\n";
        }else{
            $message="El profesor $this->nombre $this->apellidos puedes encontrarle en el despacho $this->despacho o puedes contactar
            con el mediante su correo electrónico $this->email\n";
        }

        if (!empty($this->tutorias))
        {
            $message.="Las tutorias programadas son:\n";
            foreach ($this->tutorias as $tutoria)
            {
                $message.= $tutoria->getMessage()."\n";
            }
        }

        return $message;

    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $tutorias = [];
        foreach($this->tutorias as $i=>$t)
        {
            $tutorial = new Tutorial();
            $tutorial->setAttributes($t);
            if($tutorial->validate())
            {
                $tutorias[] = $tutorial;
            }

        }
        $this->tutorias = $tutorias;

    }
}
