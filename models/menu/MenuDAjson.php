<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 06/10/17
 * Time: 12:10
 */

namespace app\models\menu;


use yii\base\Model;

class MenuDAjson extends Model
{

    public $menus = [];

    /**
     * @return array
     */
    function rules()
    {
        return [
            ['menus', 'each', 'rule'=>['each', 'rule'=>['validateModels']]]
        ];
    }

    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    function setAttributes($values, $safeOnly = true)
    {
        $menus = [];

//        parent::setAttributes($values, $safeOnly);
//        $menus_iter = $this->menus;
        $menus_iter = $values;

        foreach($menus_iter as $i=>$m)
        {
//            $link = $m->link;
//            $title = $m->title;
//            $vF = $m->validFrom;
//            $vT = $m->validTo;

            $link = $m['link'];
            $title = $m['name'];
            $vF = $m['validFrom'];
            $vT = $m['validTo'];

            $vF_array = explode("-", $vF);
            $vT_array = explode("-", $vT);

            $menu = new MenuModel();
            $menu->setAttributes([
                'link'=>$link,
                'caption'=>html_entity_decode($title),
                'validFrom'=>strtotime($vF_array[0]."-".$vF_array[1]."-".$vF_array[2]),
                'validTo'=>strtotime($vT_array[0]."-".$vT_array[1]."-".$vT_array[2]),
            ]);

            if($menu->validate())
                $menus[] = $menu;
        }
        $this->menus = $menus;

    }


}