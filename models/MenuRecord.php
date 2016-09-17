<?php

namespace app\models;

use yii\db\ActiveRecord;

class MenuRecord extends ActiveRecord
{

    public static function buildModel($pdfContent, $modified)
    {
        file_put_contents("cafeta.pdf", $pdfContent);
    }



}