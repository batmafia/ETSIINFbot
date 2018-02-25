<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:13
 */

namespace app\models\tryit;


class Edition extends Model
{

    public $url;
    public $year;
    public $title;
    public $slogan;
    public $description;
    public $start_date;
    public $end_date;

    function rules()
    {
        return [
            [['year', 'title', 'slogan', 'description', 'start_date', 'end_date'], 'string'],
            [['url'], 'url'],
        ];
    }

}