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
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getConversation();

        $keyboard [] = [self::CANCELAR];
        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage("Introduce el nombre del personal/profesor del que deseas buscar información:");
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

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $textForSearch = $this->getConversation()->notes['text'];
        $directory = DirectoryRepository::getDirectoryInfo(urlencode($textForSearch));

        if(count($directory)!==0)
        {
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
            $mensaje = "No se han encontrado resultados para tu búsqueda. Selecciona una opción del teclado.";
        }

        $cancel = [self::CANCELAR, self::NUEVA_BUSQUEDA];
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
        }else{
            if (!(in_array($text, $cancel)))
            {
                return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
            }
        }

        if (in_array($text, $cancel))
        {
            if ($text === self::CANCELAR)
            {
                return $this->cancelConversation();
            }
            else if ($text === self::NUEVA_BUSQUEDA)
            {
                return $this->previousStep();
            }
        }

        $this->getConversation()->notes['personal'] = array_search($text,$personalKB);
        return $this->nextStep();
    }

    public function processReturnInfo($text)
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $textForSearch = $this->getConversation()->notes['text'];
        $selectedIndexPersonal = $this->getConversation()->notes['personal'];
        $directory = DirectoryRepository::getDirectoryInfo(urlencode($textForSearch));
        $phoneIcon = "\xF0\x9F\x93\x9E";
        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x91\x94";

        $person = $directory[$selectedIndexPersonal];

        $mensaje = "Información sobre *$person->nombre $person->apellidos [$person->departamento]*\n".
        "$mailIcon Email: $person->nombreEmail@$person->dominioEmail\n";

        if ($person->despacho !== "" && $person->despacho !== null)
        {
            $mensaje.="$departmentIcon Despacho: *$person->despacho*\n";
        }

        $mensaje.="$phoneIcon Teléfono: *$person->telefono*\n\n".
        "Selecciona una opción del teclado por favor:";

        $cancel = [self::CANCELAR, self::ATRAS ,self::NUEVA_BUSQUEDA];
        $keyboard [] = $cancel;
        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($mensaje);
        }

        if (!(in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }

        if (in_array($text, $cancel))
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
        }
    }
}