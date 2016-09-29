<?php

namespace app\models\repositories;


use app\models\MenuModel;
use Httpful\Request;
use simplehtmldom_1_5\simple_html_dom;
use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;

class MenuRepository
{

    private static $months = [
        "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "octubre",
        "noviembre",
        "diciembre"
    ];


    public static function getMenus()
    {
        $request = Request::get("http://www.fi.upm.es/?pagina=228")->send();
        if(!$request->hasErrors())
        {
            $dom = HtmlDomParser::str_get_html($request->raw_body);
            /** @var $dom simple_html_dom */
            $elems = $dom->find("a.pdf");

            $menus = [];
            foreach($elems as $elem)
            {
                /** @var $elem simple_html_dom_node */
                $text = explode(" ", preg_replace('/[^ \w]+/', '', $elem->innertext()));
                $month = array_search(strtolower(end($text)), self::$months)+1;
                $year = date('Y');

                $days = array_values(array_filter($text, function($e){
                    return intval($e) > 0;
                }));

                $menu = new MenuModel();
                $menu->setAttributes([
                    'link'=>"http://www.fi.upm.es/".$elem->getAttribute("href"),
                    'caption'=>html_entity_decode($elem->innertext()),
                    'validFrom'=>strtotime($days[0]."-".$month."-".$year),
                    'validTo'=>strtotime($days[1]."-".$month."-".$year),
                ]);

                if($menu->validate())
                    $menus[] = $menu;
            }

            return $menus;
        }
    }

}