<?php

namespace app\models\repositories;


use app\models\menu\MenuDAjson;
use app\models\menu\MenuModel;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;
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
        $menus = [];

        # Get menu from DA
        $menuDA = self::getMenuFromDA();
        if ($menuDA != null && sizeof($menuDA) == 1)
            foreach($menuDA as $mDA)
                $menus[] = $mDA;

        # Get menus from FI
        $menusFI = self::getMenusFromFI();
        if ($menusFI != null && sizeof($menusFI) > 1)
            foreach($menusFI as $mFI)
                $menus[] = $mFI;

        return $menus;
    }


    public static function getMenuFromDA()
    {
        $menus = [];
        $request = Request::get("http://da.etsiinf.upm.es/menu/menu.json")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $menuDAjson = new MenuDAjson();
            $menuDAjson->setAttributes($data);

            if ($menuDAjson->validate()) {

                $title = $menuDAjson->title;
                $text = explode("_", $title);
                $day1 = $text[1];
                $endDate_temp = explode("-", $text[3]);
                $day2 = $endDate_temp[0];
                $month = $endDate_temp[1];
                $year = "20".$endDate_temp[2];

                $menu = new MenuModel();
                $menu->setAttributes([
                    'link'=>"http://da.etsiinf.upm.es/menu/menu.jpg",
                    'caption'=>html_entity_decode($title),
                    'validFrom'=>strtotime($day1."-".$month."-".$year),
                    'validTo'=>strtotime($day2."-".$month."-".$year),
                ]);

                if($menu->validate())
                    $menus[] = $menu;

                return $menus;

            } else {
                print_r($menuDAjson->getErrors());
                return null;
            }

        } else {
            throw new Exception("Repository exception");
        }

    }



    public static function getMenusFromFI()
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

        } else {
            throw new Exception("Repository exception");
        }
    }




}