<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 17/10/16
 * Time: 9:22
 */

namespace app\models\directory;

use yii\base\Model;

class DirectoryResponse extends Model
{

    public $nombre;
    public $apellidos;
    public $enlace;
    public $departamento;
    public $despacho;
    public $telefono;
    public $nombreEmail;
    public $dominioEmail;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre','apellidos'], 'filter', 'filter' => function($name)
            {
                return ltrim($name);
            }],
            [['nombre', 'apellidos', 'departamento', 'despacho','telefono','nombreEmail','dominioEmail'], 'string'],
            [['enlace','despacho'], 'safe'],
        ];
    }
}