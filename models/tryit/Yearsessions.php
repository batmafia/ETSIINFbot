<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:18
 */

namespace app\models\tryit;


class Yearsessions extends Model
{

    public $yearsessions = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['session', 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $sessions_tmp = [];
        foreach($this->yearsessions as $i=> $s)
        {
            $session_i = new Yearsession();
            $session_i->setAttributes($s);
            if($session_i->validate())
            {
                $sessions_tmp[] = $session_i;
            }

        }
        $this->yearsessions = $sessions_tmp;

    }

}