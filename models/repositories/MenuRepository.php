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

        try
        {
            # Get menu from DA
            $menuDAJSON = self::getMenuFromDA();
            if ($menuDAJSON != null && sizeof($menuDAJSON) == 1)
                foreach($menuDAJSON as $mDAJSON)
                    if ($mDAJSON != null && sizeof($mDAJSON) >= 1)
                        foreach($mDAJSON as $mDA)
                            $menus[] = $mDA;
        }
        catch (\Exception $exception)
        {
            if ($exception->getMessage() == "Unable to parse response as JSON"
                || preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                // send mesage to Alvaro
                $msg = "Parece que el servicio del menu de la web de DAETSIINF esta caida";
                // $this->getRequest()->markdown()->sendMessage($msg."\n\n");
                print($msg);
            }
            else
            {
                throw $exception;
            }
        }

        try
        {
            # Get menus from FI
            $menusFI = self::getMenusFromFI();
            if ($menusFI != null && sizeof($menusFI) > 1)
                foreach($menusFI as $mFI)
                    $menus[] = $mFI;
        }
        catch (\Exception $exception)
        {
            if ($exception->getMessage() == "Unable to parse response as JSON"
                || preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                // send mesage to pmoso
                $msg = "Parece que la web de la facultad esta caida";
                // $this->getRequest()->markdown()->sendMessage($msg."\n\n");
                print($msg);
            }
            else
            {
                throw $exception;
            }
        }



        return $menus;
    }


    public static function getMenuFromDA()
    {
        $request = Request::get("http://da.etsiinf.upm.es/menu/menu.json")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $menuDAjson = new MenuDAjson();

            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $menuDAjson->setAttributes($data);

            return $menuDAjson;

//            if ($menuDAjson->validate()) {
//                return $menuDAjson;
//            } else {
//                print_r($menuDAjson->getErrors());
//                return null;
//            }

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