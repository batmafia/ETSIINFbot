<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 19:42
 */

namespace app\models\subjects;

use yii\base\Model;

class DidacticResources extends Model
{
    public $recursos_web = [];
    public $bibliografia = [];

/*
"recursos_didacticos": {
    "Recursos web": [
        "Moodle",
        "Unstructured Information Management Architecture (UIMA)"
    ],
    "bibliografia": [
        "Ian Witten, Eibe Frank, Mark Hall, Data Mining: Practical Machine Learning Tools and Techniques, 3nd Edition, Morgan Kaufmann, ISBN 978-0-12-374856-0, 2011.",
        "\"MySQL Administrator´s Bible\". Sheeri K. Cabral and Keith Murphy. Wiley",
        "Pang-Ning Tan, Michael Steinbach, Vipin Kumar, Introduction to Data Mining, Pearson Addison Wesley (May, 2005). ",
        "Smart Machines book",
        "Database Systems: The Complete Book (DS:CB), by Hector Garcia-Molina, Jeff Ullman, and Jennifer Widom",
        "Data Mining book"
    ]
},
*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['recursos_web', 'bibliografia'], 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $recursos_web = [];
        foreach($this->recursos_web as $k=>$v)
        {
            $name = $v;

            $webResources = new WebResources();
            # $webResources->setAttributes($v);
            $webResources->setAttributes([
                'nombre'=>$name,
            ]);
            if($webResources->validate())
            {
                $recursos_web[] = $webResources;
            }

        }
        $this->recursos_web = $recursos_web;

        $bibliografia = [];
        foreach($this->bibliografia as $k=>$v)
        {
            $title = $v;

            $bibliography = new Bibliography();
            $bibliography->setAttributes([
                'titulo'=>$title,
            ]);
            if($bibliography->validate())
            {
                $bibliografia[] = $bibliography;
            }

        }
        $this->bibliografia = $bibliografia;

    }
}
