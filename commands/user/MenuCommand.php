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


    /**
     * [process_SelectMenu description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processMenu()
    {
        date_default_timezone_set('Europe/Madrid');
        $menus = MenuRepository::getMenus();
        $selectedMenu = null;

        foreach ($menus as $key => $weekMenu)
        {
            if (time() < strtotime("+1 day",$weekMenu->validTo))
            {
                $selectedMenu=$key;
            }
        }

        if($selectedMenu !== null)
        {
            $hbIcon = "\xF0\x9F\x8D\x94";
            $cap = "Aquí tienes el menú para esta semana";
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
                $cap = $menus[0]->caption;
                $result = $this->getRequest()->markdown()->sendMessage("⚠️ El menú disponible en la web de la cafetería es antiguo (*$cap*). Prueba más tarde.");
            }
            else
            {
                $result = $this->getRequest()->markdown()->sendMessage("⚠️ *No se ha encontrado ningún menú* en la web de la cafetería. Prueba más tarde.");
            }
        }

        return $result;
    }

}
