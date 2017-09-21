<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\commands\admin;

use app\commands\base\BaseAdminCommand;
use \DateTime;
use \DateTimeZone;

/**
 * Admin "/date" command
 */
class DateCommand extends BaseAdminCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'date';
    protected $description = 'Muestra la fecha del servidor.';
    protected $usage = '/date';
    protected $version = '1.0.2';
    protected $need_mysql = true;
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {


        $outText = "";

        $nowTime_STR = $this->myDateFormat("H:i:s", false, 'Europe/Madrid'); // false for timeestamp
        $outText .= "myDateFormat: " . $nowTime_STR . "\n";

        $nowTime = strtotime($nowTime_STR);
        $outText .= "nowTime: " . $nowTime . "\n";

        $serverTime_STR = date("H:i:s");
        $outText .= "serverTime_STR: " . $serverTime_STR . "\n";

        $serverTimeTIMESTAMP_STR = date("U");
        $outText .= "serverTimeTIMESTAMP_STR: " . $serverTimeTIMESTAMP_STR . "\n";


        $result = $this->getRequest()->sendMessage($outText);
        $this->stopConversation();
        return $result;

    }



    /**
     * http://php.net/manual/es/function.date.php
     * @param  string  $format    [description]
     * @param  boolean $timestamp [description]
     * @param  boolean $timezone  [description]
     * @return [type]             [description]
     */
    function myDateFormat($format="r", $timestamp=false, $timezone=false)
    {
        $userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
        $gmtTimezone = new DateTimeZone('GMT');
        $myDateTime = new DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
        $offset = $userTimezone->getOffset($myDateTime);
        return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
    }
}
