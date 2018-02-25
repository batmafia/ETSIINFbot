<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:07
 */

namespace app\models\tryit;


use yii\base\Model;

class Editions extends Model
{

    public $editions = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['edition', 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $editions_tmp = [];
        foreach($this->editions as $i=>$e)
        {
            $edition_i = new Tutorial();
            $edition_i->setAttributes($e);
            if($edition_i->validate())
            {
                $editions_tmp[] = $edition_i;
            }

        }
        $this->editions = $editions_tmp;

    }

}