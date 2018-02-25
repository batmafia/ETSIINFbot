<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:21
 */

namespace app\models\tryit;


class Yearsession extends Model
{

    public $code;
    public $title;
    public $start_date;

    function rules()
    {
        return [
            [['url', 'title'], 'string'],
            [['start_date'], 'date'],
        ];
    }

}