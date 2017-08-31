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
    protected $version = '1.0.0';
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
                ->sendMessage("Introduce el número de matrícula (sin letra al principio):");
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

        $tutoria = TutorRepository::getTutoria(urlencode($textForSearch));

        if ($tutoria == null)
        {
            $mensaje = "Este número de matrícula: *$textForSearch*, no es válido.\n";
            $mensaje .= "Escriba un número de matrícula válido.";
            $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
            $this->stopConversation();
            return $this->resetCommand();
        }

        $alumno = $tutoria[0];
        $tutor = $tutoria[1];

        // check if return a empty object with mat without teacher
        if ($tutor->nombre == "" && $tutor->apellidos == "" && $tutor->departamento == "")
        {
            $mensaje = "";
            if ($alumno !== null && $alumno !== [] &&
                $alumno->nombre !== null && $alumno->apellidos !== null &&
                $alumno->nombre !== "" && $alumno->apellidos !== "")
            {
                $mensaje .= "Hola *$alumno->nombre $alumno->apellidos*.\n";
            } else {
                $mensaje .= "Hola *$textForSearch*.\n";
            }
            $mensaje .= "Parece que no tienes un tutor asignado.\n";
            $mensaje .= "Contacta con subdirección de alumnos para mas información.";
            $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
            return $this->stopConversation();
        }




        //
        // Msg
        //

        $phoneIcon = "\xF0\x9F\x93\x9E";
        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x8F\xA2";


        $mensaje = "";

        if ($alumno !== null && $alumno !== [] &&
            $alumno->nombre !== null && $alumno->apellidos !== null  &&
            $alumno->nombre !== "" && $alumno->apellidos !== "")
        {
            $mensaje .= "Hola *$alumno->nombre $alumno->apellidos*";
        } else {
            $mensaje .= "Hola *$textForSearch*";
        }

        //$mensaje .= " desde el curso *$tutor->curso*,";
        $mensaje .= " tu tutor es:\n";


        if ($tutor->enlace !== null && $tutor->enlace !== "")
        {
            $mensaje .= "[$tutor->nombre $tutor->apellidos]($tutor->enlace)";
        } else {
            $mensaje .= "*$tutor->nombre $tutor->apellidos*";
        }
        if ($tutor->departamento !== null && $tutor->departamento !== "")
        {
            $mensaje .= " del *$tutor->departamento*\n";
        }

        if ($tutor->nombreEmail !== null && $tutor->dominioEmail !== null  &&
            $tutor->nombreEmail !== "" && $tutor->dominioEmail !== "")
        {
            $mensaje .= "$mailIcon Email: $tutor->nombreEmail@$tutor->dominioEmail\n";
        }

        if ($tutor->despacho !== null && $tutor->despacho !== "")
        {
            $mensaje .= "$departmentIcon Despacho: *$tutor->despacho*\n";
        }

        if ($tutor->telefono !== null && $tutor->telefono !== "")
        {
            $mensaje .= "$phoneIcon Teléfono: ";
            if(strpos($tutor->telefono, "+34") === false) {
                $mensaje .= "+34";
            }
            $mensaje .= "$tutor->telefono\n";

        }


        if ($this->isProcessed() || empty($text))
        {
            $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
        }

        $this->stopConversation();


    }
}