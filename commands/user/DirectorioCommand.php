<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 17/10/16
 * Time: 9:36
 */

namespace app\commands\user;
use app\commands\base\Request;
use app\commands\base\BaseUserCommand;
use app\models\repositories\DirectoryRepository;

/**
 * User "/directorio" command
 */
class DirectorioCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'directorio';
    protected $description = 'Busca información básica sobre el personal de la universidad.';
    protected $usage = '/directorio';
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
            $des = "Introduce el nombre del personal / profesor (sensible a tildes) del que deseas buscar información." . "\n" ;
            $des .= "\n";
            $des .= "*Para una busqueda óptima escribe el nombre y primer apellido de la persona.*" . "\n";
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage($des);
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['text'] = $text;
        return $this->nextStep();
    }

    public function processReturnSearch($text)
    {

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        else if ($text === self::NUEVA_BUSQUEDA)
        {
            return $this->previousStep();
        }

        $textForSearch = $this->getConversation()->notes['text'];

        $this->getRequest()->sendAction(Request::ACTION_TYPING);


        $directory = [];

        try
        {
            $directory = DirectoryRepository::getDirectoryInfo(urlencode($textForSearch));
        }
        catch (\Exception $exception)
        {
            if (preg_match('/json_decode error: Syntax error/', $exception->getMessage())) {
                print("La petición se queda pillada");
                print($exception->getMessage());
                print($exception->getTraceAsString());
                $msge = "Parece que la API tiene problemas con ese patrón de nombre." . "\n\n";
                $msge .= "Prueba a alternar el nombre o el apellido de la persona." . "\n\n";
                $msge = "*" . "$msge" . "*";
                $msge .= "Si el problema persiste, escribe a /contacta." . "\n";
                $msge .= "\n";
                $this->getRequest()->markdown()->hideKeyboard()->sendMessage($msge . "\n\n");
                return $this->stopConversation();
            } else {
                if (preg_match('/Unable to connect to /', $exception->getMessage())) {
                    $msge = "Parece que la API de la UPM esta caida.";
                } elseif ($exception->getMessage() == "Unable to parse response as JSON") {
                    $msge = "Parece que la API de la UPM esta caida.";
                    print("No se ha interpretado el JSON de la petición.");
                    print($exception->getMessage());
                    print($exception->getTraceAsString());
                } else {
                    $msge = "Ocurrió un error inesperado.";
                    print($msge);
                    throw $exception;
                }
                $msge .= "Prueba a realizar la consulta más tarde." . "\n";
                $msge .= "Si el problema persiste, escribe a /contacta." . "\n";
                $msge .= "\n";
                $this->getRequest()->markdown()->sendMessage($msge . "\n\n");
                return $this->cancelConversation();
            }
        }

        if(count($directory)!==0)
        {
            $cancel = [self::CANCELAR, self::NUEVA_BUSQUEDA];
            $personalKB = [];
            $mensaje = "Selecciona de quién deseas obtener más información:\n\n";

            foreach ($directory as $person)
            {
                if ($person->nombre === "" || $person->nombre === null)
                {
                    $mensaje .= "*- $person->apellidos [$person->departamento]\n*";
                    $personalKB [] = "$person->apellidos [$person->departamento]";
                }
                else
                {
                    $mensaje .= "*- $person->nombre $person->apellidos [$person->departamento]\n*";
                    $personalKB [] = "$person->nombre $person->apellidos [$person->departamento]";
                }

            }
            $keyboard = array_chunk($personalKB, 2);
        }
        else
        {
            $mensaje = "*No se han encontrado resultados para tu búsqueda. Prueba a buscar con otros términos. Recuerda poner acentos si el nombre los tiene.*";
            $this->getRequest()->markdown()->sendMessage($mensaje);
            $this->stopConversation();
            return $this->resetCommand();
        }

        $keyboard [] = $cancel;
        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($mensaje);
        }

        if(count($directory)!==0){
            if (!(in_array($text, $personalKB) || in_array($text, $cancel)))
            {
                return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
            }
        }

        $this->getConversation()->notes['personal'] = array_search($text,$personalKB);
        return $this->nextStep();
    }

    public function processReturnInfo($text)
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
        $selectedIndexPersonal = $this->getConversation()->notes['personal'];

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $directory = [];

        try
        {
            $directory = DirectoryRepository::getDirectoryInfo(urlencode($textForSearch));
        }
        catch (\Exception $exception)
        {
            if (preg_match('json_decode error: Syntax error', $exception->getMessage())) {
                print("La petición se queda pillada");
                print($exception->getMessage());
                print($exception->getTraceAsString());
                $msge = "Parece que la API tiene problemas con ese patrón de nombre." . "\n";
                $msge .= "Prueba a alternar el nombre o el apellido de la persona." . "\n";
                $msge = "*" . "$msge" . "*";
                $msge .= "Si el problema persiste, escribe a /contacta." . "\n";
                $msge .= "\n";
                $this->getRequest()->markdown()->hideKeyboard()->sendMessage($msge . "\n\n");
                return $this->stopConversation();
            } else {
                if (preg_match('/Unable to connect to /', $exception->getMessage())) {
                    $msge = "Parece que la API de la UPM esta caida.";
                } elseif ($exception->getMessage() == "Unable to parse response as JSON") {
                    $msge = "Parece que la API de la UPM esta caida.";
                    print("No se ha interpretado el JSON de la petición.");
                    print($exception->getMessage());
                    print($exception->getTraceAsString());
                } else {
                    $msge = "Ocurrió un error inesperado.";
                    print($msge);
                    throw $exception;
                }
                $msge .= "Prueba a realizar la consulta más tarde." . "\n";
                $msge .= "Si el problema persiste, escribe a /contacta." . "\n";
                $msge .= "\n";
                $this->getRequest()->markdown()->sendMessage($msge . "\n\n");
                return $this->cancelConversation();
            }
        }

        $phoneIcon = "\xF0\x9F\x93\x9E";
        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x8F\xA2";


        $person = $directory[$selectedIndexPersonal];



        $mensaje = "Información sobre:\n";
        $this->getRequest()->markdown()->sendMessage($mensaje);


        $mensaje = "";

        if ($person->enlace !== null && $person->enlace !== "")
        {
            $mensaje .= "[$person->nombre $person->apellidos]($person->enlace)";
        } else {
            $mensaje .= "*$person->nombre $person->apellidos*";
        }
        if ($person->departamento !== null && $person->departamento !== "")
        {
            $mensaje .= " del *$person->departamento*\n";
        }

        if ($person->nombreEmail !== null && $person->dominioEmail !== null &&
            $person->nombreEmail !== "" && $person->dominioEmail !== "")
        {
            $mensaje .= "$mailIcon Email: $person->nombreEmail@$person->dominioEmail\n";
        }

        if ($person->despacho !== null && $person->despacho !== "")
        {
            $mensaje .= "$departmentIcon Despacho: *$person->despacho*\n";
        }

        if ($person->telefono !== null && $person->telefono !== "")
        {
            $mensaje .= "$phoneIcon Teléfono: +34$person->telefono\n";
        }


        $this->getRequest()->markdown()->sendMessage($mensaje);


        $mensaje = "Selecciona una opción del teclado por favor:";

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

    }
}