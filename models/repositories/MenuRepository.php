<?php

namespace app\models\repositories;


class MenuRepository
{

    public static function updateLastMenu()
    {
        $request = Request::get("http://www.fi.upm.es/?pagina=228")->send();
        if(!$request->hasErrors())
        {
            $dom = SimpleHtmlDom::str_get_html($request->raw_body);
            /** @var $dom \simple_html_dom */
            $elems = $dom->find("a.pdf");
            $last = end($elems);
            $url = "http://www.fi.upm.es/".$last->getAttribute("href");

            $request2 = Request::get($url)->send();
            $modified = strtotime($request2->headers->toArray()['last-modified']);
        }
    }

    public static function getLastMenu()
    {

    }

}