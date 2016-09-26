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
    protected $version = '0.1.0';
    protected $need_mysql = true;


    /**
     * [process_SelectMenu description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processMenu()
    {
        $link = MenuRepository::getLastPdfLink();
        $this->getRequest()->caption("Prueba")->sendDocument($link);
    }

}
