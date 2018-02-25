<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:44
 */

namespace app\models\tryit;


class Session extends Model
{
    public $title;
    public $start_date;
    public $end_date;
    public $description;
    public $url;
    public $video;
    public $company;
    public $logo;
    public $speakers = [];
    public $track = [];

    function rules()
    {
        return [
            [['title', 'description', 'company'], 'string'],
            [['start_date', 'end_date'], 'date'],
            [['url', 'video', 'logo'], 'url'],
            [['speakers', 'track'], 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);


        $speakers = [];
        foreach($this->speakers as $i=>$s)
        {
            $speakers_i = new Yearsession();
            $speakers_i->setAttributes($s);
            if($speakers_i->validate())
            {
                $speakers[] = $speakers_i;
            }

        }
        $this->speakers = $speakers;


        $track_tmp = [];
        foreach($this->track as $i=>$t)
        {
            $track_i = new Yearsession();
            $track_i->setAttributes($t);
            if($track_i->validate())
            {
                $track_tmp[] = $track_i;
            }

        }
        $this->track = $track_tmp;

    }

}