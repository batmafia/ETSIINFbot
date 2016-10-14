<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 13/10/16
 * Time: 19:04
 */

namespace app\models\repositories;

use app\models\calendar\Calendar;
use Httpful\Request;
use Httpful\Mime;
use simplehtmldom_1_5\simple_html_dom;
use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;

class CalendarRepository
{

    public static function getBusinessCalendars()
    {
        $request = Request::get("https://www.fi.upm.es/?pagina=55")->expects(Mime::HTML)->send();
        if(!$request->hasErrors())
        {
            $dom = HtmlDomParser::str_get_html($request->raw_body);
            /** @var $dom simple_html_dom */
            $titles = $dom->find(".contenido h3");
            $links = $dom->find(".contenido ul li a");

            $calendars = [];

            foreach ($titles as $i=>$t)
            {
                /** @var $t simple_html_dom_node */
                $caption = $links[$i]->innerText();
                if($links[$i]->has_child())
                    $caption = $links[$i]->first_child()->innerText();

                $businessCalendar = \Yii::createObject([
                    'class' => Calendar::className(),
                    'link' => "https://www.fi.upm.es/".$links[$i]->href,
                    'caption' => $caption
                ]);

                if($businessCalendar->validate())
                {
                    $calendars[html_entity_decode($t->innertext())] = $businessCalendar;
                }
            }

            return $calendars;
        }
        else
        {
            throw new Exception("Repository exception");
        }

    }


    public static function getDegrees()
    {
        $request = Request::get("https://www.fi.upm.es/?id=estudios")->expects(Mime::HTML)->send();
        if(!$request->hasErrors())
        {
            $degrees = [];

            $dom = HtmlDomParser::str_get_html($request->raw_body);
            $headers = $dom->find(".contenido h2");
            foreach($headers as $h)
            {
                if($h->innerText() === "Grado")
                {
                    /** @var $h simple_html_dom_node */
                    $ul = $h->nextSibling();
                    foreach($ul->find("a") as $a)
                    {
                        $degrees[html_entity_decode($a->innertext())] = $a->href;
                    }

                    break;
                }
            }

            //Remove last element (Grupo++)
            array_pop($degrees);

            return $degrees;
        }
        else
        {
            throw new Exception("Repository exception");
        }
    }

    public static function getExamCalendars($degree)
    {
        $request = Request::get("https://www.fi.upm.es/$degree")->followRedirects(true)->expects(Mime::HTML)->send();
        if(!$request->hasErrors())
        {
            $calendars = [];

            $dom = HtmlDomParser::str_get_html($request->raw_body);
            $headers = $dom->find(".contenido h2");

            foreach($headers as $h)
            {
                if(html_entity_decode($h->innerText()) === "Evaluación")
                {
                    /** @var $h simple_html_dom_node */
                    $ul = $h->nextSibling();
                    foreach($ul->find("a") as $a)
                    {
                        if(!isset($a->href))
                            continue;

                        $caption = $a->innerText();
                        if($a->has_child())
                            $caption = $a->first_child()->innerText();

                        $caption = html_entity_decode($caption);

                        $examCalendar = \Yii::createObject([
                            'class' => Calendar::className(),
                            'link' => "https://www.fi.upm.es/".$a->href,
                            'caption' => $caption
                        ]);

                        if($examCalendar->validate())
                        {
                            $calendars[$examCalendar->caption] = $examCalendar;
                        }
                    }

                    break;
                }
            }

            return $calendars;
        }
        else
        {
            throw new Exception("Repository exception");
        }
    }

    public static function getTimetables($degree)
    {
        $request = Request::get("https://www.fi.upm.es/$degree")->followRedirects(true)->expects(Mime::HTML)->send();
        if(!$request->hasErrors())
        {
            $timetables = [];

            $dom = HtmlDomParser::str_get_html($request->raw_body);
            $headers = $dom->find(".contenido h2");

            foreach($headers as $h)
            {
                if(substr($h->innerText(), 0, strlen("Horarios curso ")) === "Horarios curso ")
                {
                    /** @var $h simple_html_dom_node */
                    while(($h = $h->nextSibling())->tag === "h3")
                    {
                        $semester = $h->innerText();
                        foreach ($h->nextSibling()->find("ul a") as $a) {
                            if (!isset($a->href))
                                continue;

                            $caption = $a->innerText();
                            if ($a->has_child())
                                $caption = $a->first_child()->innerText();

                            $caption = html_entity_decode($caption);

                            $timetable = \Yii::createObject([
                                'class' => Calendar::className(),
                                'link' => "https://www.fi.upm.es/" . $a->href,
                                'caption' => $caption
                            ]);

                            if ($timetable->validate()) {
                                $timetables[$semester][$timetable->caption] = $timetable;
                            }
                        }

                        $h = $h->nextSibling();
                    }

                    break;
                }
            }

            return $timetables;
        }
        else
        {
            throw new Exception("Repository exception");
        }
    }


}