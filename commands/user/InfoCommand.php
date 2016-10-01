<?php

namespace app\commands\user;
use app\commands\base\BaseUserCommand;

/**
 * User "/bus" command
 */
class InfoCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;

    protected $name = 'info';
    protected $description = 'Consulta información rápida sobre algunos temas de la facultad.';
    protected $usage = '/info';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    /**#@-*/

    const KEYBOARD_COLUMNS = 3;

    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processOptions($text)
    {
        //TODO: Use actual $infoArray from repository
        $infoArray = [
            'Asociaciones' => [
                'Información ETSIINF'=>'https://www.fi.upm.es/?id=actividades/asociaciones',
                'Información UPM'=>'http://www.upm.es/Estudiantes/Asociaciones',
                'ACM' => [
                    'Nombre'=>'ACM Capítulo de Estudiantes.',
                    'Descripción'=>'Es una organización internacional científica y educativa dedicada a hacer progresar el arte, la ciencia, la ingeniería y las aplicaciones de la tecnología de la información, fomentando un intercambio abierto de información. A.C.M. agrupa a la práctica totalidad de la comunidad investigadora en el ámbito informático. Entre los propósitos de A.C.M. se encuentra promover el conocimiento de la ciencia, el diseño, la construcción del lenguaje y las aplicaciones de los ordenadores, proporcionando así un medio de comunicación e intercambio.',
                    'Web'=>'http://acm.asoc.fi.upm.es/',
                    'Correo'=>'acm@fi.upm.es',
                    'Twitter'=>'@acmupm',
                    'Telegram'=>'@acmupm'
                ],
                'Histrión'=>'Info de Histrión',
                'ASCFI'=>'Info de ASCFI',
                'Alfa - Omega'=>'Info de Alfa - Omega',
                'CITFI'=>'Info de CITFI',
                'Clib Deportivo'=>'Info de Club Deportivo',
                'I.D.I.M.'=>'Info de I.D.I.M.',
                'NERV'=>'Info de NERV',
                'Tuna'=>'Tuna de Informática'
            ],
            'Secretaria' => [
                'Lugar'=>'Lugar',
                'Horario'=>'Horario',
                'Teléfono'=>'Telefono',
                'Correo'=>'Correo'
            ],
            'WIFI' => [
                'FIWIFI'=>'FIWIFI',
                'WIFIUPM'=>'WIFIUPM',
                'eduroam'=>'eduroam'
            ],
            'VPN' => 'Info de VPN',
            'FTP' => 'Info de FPT'
        ];

        $opts = $this->getCurrentOptions($infoArray);

        if(!is_array($opts))
        {
            //We reached last level, send the info.
            $this->stopConversation();
            return $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($opts);
        }
        else
        {
            $opts = array_keys($opts);
            $cancel = ['Cancelar'];
            $keyboard = array_chunk($opts, self::KEYBOARD_COLUMNS);
            $keyboard[] = $cancel;

            $this->getRequest()->keyboard($keyboard);

            if (empty($text)) {
                return $this->getRequest()->sendMessage('Selecciona una opción');
            }

            if (!(in_array($text, $opts) || in_array($text, $cancel))) {
                return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
            }

            if (in_array($text, $cancel)) {
                return $this->cancelConversation();
            }

            $this->getConversation()->notes['indexes'][] = $text;
            return $this->processOptions(null);
        }
    }

    private function getCurrentOptions($infoArray)
    {
        if(!isset($this->getConversation()->notes['indexes']))
            $this->getConversation()->notes['indexes'] = [];

        $opts = $infoArray;
        foreach ($this->getConversation()->notes['indexes'] as $index)
        {
            $opts = $opts[$index];
        }

        return $opts;
    }

    /**
     * [cancelConversation description]
     * @return [type] [description]
     */
    private function cancelConversation()
    {
        $msgCancel = "*Comando cancelado.*";
        $msgHelp = "Más comandos en /help.";

        $msgCancelConver = $msgCancel."\n".$msgHelp;

        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($msgCancelConver);
        $this->stopConversation();
        return $result;
    }

}
