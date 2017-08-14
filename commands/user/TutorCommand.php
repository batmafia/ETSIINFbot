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

        return $this->nextStep();
    }


   public function processReturnInfo($text)
    {

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $textForSearch = $this->getConversation()->notes['text'];

        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $tutor = TutorRepository::getTutor(urlencode($textForSearch));

        if ($tutor == null)
        {
            $mensaje = "Este número de matrícula: *$textForSearch*, no es válido.\n";
            $mensaje .= "Vuelva a lanzar el comando con un número de matrícula válido.";
            $this->getRequest()->markdown()->hideKeyboard()->sendMessage($mensaje);
            return $this->stopConversation();
        }

        // @TODO: check if return a empty object with 140360
        if ($tutor->nombre == "" && $tutor->apellidos == "" && $tutor->departamento == "")
        {
            $mensaje = "Hola *$textForSearch*.\nParece que no tienes un tutor asignado. Contacta con subdirección de alumnos para mas información.";
            $this->getRequest()->markdown()->hideKeyboard()->sendMessage($mensaje);
            return $this->stopConversation();
        }

        // @TODO: check if return a field empty

        // @TODO: call DirectoryRepository::getDirectoryInfo(urlencode(nombre apellidos1 apellido2)); in repository to get more info


        $phoneIcon = "\xF0\x9F\x93\x9E";
        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x91\x94";

        $mensaje = "";
        $mensaje .= "Hola *$textForSearch*";
        //$mensaje .= " que empezó en el curso *$tutor->curso*";
        $mensaje .= " este es tu tutor:\n";

        $mensaje .= "*$tutor->nombre $tutor->apellidos* del *$tutor->departamento*\n";

        /*
        $mensaje = "Información sobre...\n*$nombre $apellidos [$tutor->departamento]*\n".
        "$mailIcon Email: $tutor->nombreEmail@$tutor->dominioEmail\n";
        */

        if ($tutor->despacho !== "" && $tutor->despacho !== null)
        {
            // $mensaje.="$departmentIcon Despacho: *$tutor->despacho*\n";
            $mensaje.="Puedes encontrarle en el depacho: *$tutor->despacho*.\n";

        }

        /*
        $mensaje = "Información sobre...\n*$nombre $apellidos [$tutor->departamento]*\n".
        "$mailIcon Email: $tutor->nombreEmail@$tutor->dominioEmail\n";

        $mensaje.="$phoneIcon Teléfono: *$tutor->telefono*\n\n";
        */

        if ($this->isProcessed() || empty($text))
        {
            $this->getRequest()->markdown()->hideKeyboard()->sendMessage($mensaje);
        }

        print("$mensaje\n");
        $this->stopConversation();


    }
}