<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\commands\user;

use app\commands\base\BaseUserCommand;
use app\commands\base\Request;
use app\models\repositories\MenuRepository;

/**
 * User "/menu" command
 */
class MenuCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;

    protected $name = 'menu';
    protected $description = 'Consulta el menú de la cafetería de la ETSIINF.';
    protected $usage = '/menu';
    protected $version = '0.2.0';
    protected $need_mysql = true;


    /*
     * public function processMenu()
    {
        $mensaje = "*Debido al cambio de la empresa de cafetería, por ahora no podemos enviar el menú.\nEstamos trabajando para solucionarlo.\nPerdonen las molestias.*";
        $results = $this->getRequest()->markdown()->sendMessage($mensaje);
        return $results;
    }
    */


    /**
     * [process_SelectMenu description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processMenu()
    {
        date_default_timezone_set('Europe/Madrid');
        
        $this->getConversation();
        
        $menus = MenuRepository::getMenus();

        if ($menus == null){
            $result = $this->getRequest()->markdown()->sendMessage("⚠️ Ha habido un problema al consultar el menu de la cafeteria. Prueba más tarde.");
            return $result;
        }

        $selectedMenu = null;
        foreach ($menus as $key => $weekMenu)
        {
            if (time() < strtotime("+1 day", $weekMenu->validTo))
            {
                $selectedMenu = $key;
                break;
            }

        }

        if($selectedMenu !== null)
        {
            $hbIcon = "\xF0\x9F\x8D\x94";
            $dateTo = date("d/m/Y", $menus[$selectedMenu]->validTo);
            $cap = "Aquí tienes el menú hasta el $dateTo";
            $linkMenu = $menus[$selectedMenu]->link;

            if (strpos($linkMenu, ".jpg") !== false) {
                $this->getRequest()->sendAction(Request::ACTION_UPLOADING_PHOTO);
                $result = $this->getRequest()->caption("$hbIcon $cap")->sendPhoto($linkMenu);
            } elseif ((strpos($linkMenu, ".pdf") !== false)) {
                $this->getRequest()->sendAction(Request::ACTION_UPLOADING_DOCUMENT);
                $result = $this->getRequest()->caption("$hbIcon $cap")->sendDocument($linkMenu);
            } else {
                $this->getRequest()->sendAction(Request::ACTION_TYPING);
                $result = $this->getRequest()->caption("$hbIcon $cap")->sendMessage($linkMenu);
            }
        }
        else
        {
            if(!empty($menus))
            {
                $menuDA = $menus[0];
                $dateFrom = date("d/m/Y", $menuDA->validFrom);
                $dateTo = date("d/m/Y", $menuDA->validTo);
                $result = $this->getRequest()->markdown()->sendMessage("⚠️ El menú disponible de la cafetería es antiguo (*$dateFrom - $dateTo*). Prueba más tarde.");
            }
            else
            {
                $result = $this->getRequest()->markdown()->sendMessage("⚠️ *No se ha encontrado ningún menú* de la cafetería. Prueba más tarde.");
            }
        }

        return $result;
    }

}
