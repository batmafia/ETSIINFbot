<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 11:19
 */

namespace app\models;

use yii\base\Model;
ini_set('default_charset', 'utf-8');
class PlanSubject extends Model
{

    public $codigo;
    public $nombre;
    public $nombre_ingles;
    public $curso;
    public $codigo_tipo_asignatura;
    public $nombre_tipo_asignatura;
    public $credects;
    public $departamentos = [];
    public $idiomas = [];
    public $imparticion = [];


    /**
     * @return array the validation rules.
     */


    public function strToLowerAndUCWord($attribute, $value)
    {
        $arrayChars = str_split($this->nombre);
        $strLowered ="";

        foreach ($arrayChars as $char)
        {
            switch ($char)
            {
                case "Á":
                    $strLowered.="á";
                    break;
                case "É":
                    $strLowered.="é";
                    break;
                case "Í":
                    $strLowered.="í";
                    break;
                case "Ó":
                    $strLowered.="ó";
                    break;
                case "Ú":
                    $strLowered.="ú";
                    break;
                case "Ä":
                    $strLowered.="ä";
                    break;
                case "Ë":
                    $strLowered.="ë";
                    break;
                case "Ï":
                    $strLowered.="ï";
                    break;
                case "Ö":
                    $strLowered.="ö";
                    break;
                case "Ü":
                    $strLowered.="ü";
                    break;
                case "Ñ":
                    $strLowered.="ñ";
                    break;
                default:
                    $strLowered.=strtolower($char);
            }
        }

        $arrayWords = explode(" ", $strLowered);

        $myWord = [];

        foreach ($arrayWords as $word)
        {
            $wordSplitted=str_split($word);
            switch ($wordSplitted[0])
            {
                case "á":
                    $wordSplitted[0]="Á";
                    break;
                case "é":
                    $wordSplitted[0]="É";
                    break;
                case "í":
                    $wordSplitted[0]="Í";
                    break;
                case "ó":
                    $wordSplitted[0]="Ó";
                    break;
                case "ú":
                    $wordSplitted[0]="Ú";
                    break;
                case "ä":
                    $wordSplitted[0]="Ä";
                    break;
                case "ë":
                    $wordSplitted[0]="Ë";
                    break;
                case "ï":
                    $wordSplitted[0]="Ï";
                    break;
                case "ö":
                    $wordSplitted[0]="Ö";
                    break;
                case "ü":
                    $wordSplitted[0]="Ü";
                    break;
                case "ñ":
                    $wordSplitted[0]="Ñ";
                    break;
                default:
                    $wordSplitted[0]=strtoupper($wordSplitted[0]);
            }
            $myWord []= implode($wordSplitted);
        }

        $this->{$attribute} = implode(" ",$myWord);
        return true;
    }

    public function rules()
    {
        return [
            [['codigo','nombre','nombre_ingles','curso','codigo_tipo_asignatura','nombre_tipo_asignatura','credects'], 'string'],
            ['nombre', 'strToLowerAndUCWord'],
            ['idiomas', 'each', 'rule'=>['string']],
            [['imparticion','departamentos'], 'each', 'rule'=>['validateModels']],
        ];
    }


    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $departments = [];
        foreach($this->departamentos as $i=>$d)
        {
            $department = new PlanDepartment();
            $department->setAttributes($d);
            $departments[]=$department;
        }
        $this->departamentos = $departments;

        $imparticiones = [];
        foreach($this->imparticion as $i=>$im)
        {
            $impartition = new PlanImpartition();
            $impartition->setAttributes($im);
            $imparticiones[]=$impartition;
        }
        $this->imparticion = $imparticiones;

    }

}