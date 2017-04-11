<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 10/04/17
 * Time: 21:18
 */

namespace app\commands\user;
use app\commands\base\Request;
use app\commands\base\BaseUserCommand;
use app\models\repositories\TutorRepository;

/**
 * User "/tutor" command
 */
class TutorCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'tutor';
    protected $description = 'Devuelve el tutor de un alumno dada una matricula.';
    protected $usage = '/tutor';
    protected $version = '0.1.0';
    protected $need_mysql = true;

    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';
    const NUEVA_BUSQUEDA = 'Nueva Búsqueda';


    public function processGetTextForSearch($text)
    {
        $this->getConversation();

        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $keyboard [] = [self::CANCELAR];

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("Introduce el número de matrícula (sin letra):");
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['text'] = $text;
        $data = TutorRepository::getTutor(urlencode($text));

        return $this->stopConversation();
    }


   /* public function processReturnInfo($text)
    {

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        else if ($text === self::NUEVA_BUSQUEDA)
        {
            $this->stopConversation();
            return $this->resetCommand();
        }
        else if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        $textForSearch = $this->getConversation()->notes['text'];

        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $directory = TutorRepository::getTutor(urlencode($textForSearch));

        $phoneIcon = "\xF0\x9F\x93\x9E";
        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x91\x94";

        $person = $directory[$selectedIndexPersonal];

        $mensaje = "Información sobre...\n*$person->nombre $person->apellidos [$person->departamento]*\n".
        "$mailIcon Email: $person->nombreEmail@$person->dominioEmail\n";

        if ($person->despacho !== "" && $person->despacho !== null)
        {
            $mensaje.="$departmentIcon Despacho: *$person->despacho*\n";
        }

        $mensaje.="$phoneIcon Teléfono: *$person->telefono*\n\n".
        "Selecciona una opción del teclado por favor:";

        $newSearch = [self::NUEVA_BUSQUEDA];
        $cancel = [self::CANCELAR,self::ATRAS];
        $keyboard [] = $newSearch;
        $keyboard [] = $cancel;
        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($mensaje);
        }

        if (!(in_array($text, $cancel) || in_array($text,$newSearch)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }

    } */
}