<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:18
 */

namespace app\models\tryit;


class yearsessions extends Model
{

    public $sessions = [];

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
        foreach($this->sessions as $i=>$s)
        {
            $session_i = new Tutorial();
            $session_i->setAttributes($s);
            if($session_i->validate())
            {
                $sessions_tmp[] = $session_i;
            }

        }
        $this->sessions = $sessions_tmp;

    }

}