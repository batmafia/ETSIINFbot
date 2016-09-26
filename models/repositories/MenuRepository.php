<?php

namespace app\models\repositories;


use Httpful\Request;
use Sunra\PhpSimple\HtmlDomParser;

class MenuRepository
{

    public static function getLastPdfLink()
    {
        $request = Request::get("http://www.fi.upm.es/?pagina=228")->send();
        if(!$request->hasErrors())
        {
            $dom = HtmlDomParser::str_get_html($request->raw_body);
            /** @var $dom \simple_html_dom */
            $elems = $dom->find("a.pdf");
            $last = $elems[0];

            return "http://www.fi.upm.es/".$last->getAttribute("href");
        }
    }

}