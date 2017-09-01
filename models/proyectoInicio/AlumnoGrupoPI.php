<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 31/08/17
 * Time: 19:19
 */

namespace app\models\proyectoInicio;


use yii\base\Model;

class AlumnoGrupoPI extends Model
{

    public $nombre;
    public $apellidos;
    public $nMat;
    public $cursoEmpieze;
    public $correoUPM;
    public $plan;
    public $equipoPI;
    public $turno;
    public $turnoMsg;
    public $horaTurno;

    function rules()
    {
        return [
            [['nombre', 'apellidos', 'nMat', 'cursoEmpieze', 'correoUPM', 'plan', 'equipoPI', 'turno', 'turnoMsg', 'horaTurno'], 'string'],
            [['nombre', 'apellidos', 'nMat', 'cursoEmpieze', 'correoUPM', 'plan', 'equipoPI', 'turno', 'turnoMsg', 'horaTurno'], 'safe'],
        ];
    }

}