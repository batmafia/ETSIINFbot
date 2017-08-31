<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 08/31/17
 * Time: 01:11
 */

namespace app\commands\user;
use app\commands\base\Request;
use app\commands\base\BaseUserCommand;
use app\models\proyectoInicio\Enlace;
use app\models\repositories\ProyectoInicioRepository;
use app\models\repositories\TutorRepository;
use app\commands\user\TutorCommand;

/**
 * User "/ProyectoIncioRepository" command
 */
class ProyectoInicioCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'proyectoInicio';
    protected $description = 'Información acerca del Proyecto Incio';
    protected $usage = '/proyectoInicio';
    protected $version = '1.0.0';
    protected $need_mysql = true;

    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';
    const NUEVA_BUSQUEDA = 'Nueva Búsqueda';



    public function processProyectoInicio($text)
    {
        $cancel = [self::CANCELAR];
        $options = ['Calendario','Contenido','Equipo PI','Proyecto mentor','Tutor curricular'];

        $keyboard = array_chunk(($options), 2);
        $keyboard [] = [self::CANCELAR];

        $this->getConversation();

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (!(in_array($text, $options) || in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
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

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $text = $this->getConversation()->notes['text'];

        if ($text === 'Calendario')
        {
            $msg = "Aquí tienes el calendario del proyecto inicio para el curso " . $this->getCourse() . ":\n";
            $calendarioModel = \Yii::createObject([
                'class' => Enlace::className(),
                'link' => "https://www.fi.upm.es/docs/estudios/orientacion/995_2CalendarioProyectoInicio2017-2018.pdf",
                'caption' => $msg
            ]);

            if($calendarioModel->validate()) {
                // $result = $this->getRequest()->hideKeyboard()->caption($calendarioModel->caption)->sendDocument($calendarioModel->link);
                $msg = $calendarioModel->caption . $calendarioModel->link;
                $result = $this->getRequest()->hideKeyboard()->sendMessage($msg);
                $this->stopConversation();
                return $result;
            } else {
                $result = $this->getRequest()->sendMessage("Lo siento no he podido enviarte el calendario del proyecto inicio. Prueba de nuevo mas tarde.");
                $this->stopConversation();
                return $result;
            }

        }
        elseif ($text === 'Contenido')
        {
            $msg = "Aquí tienes el documento con la información del proyecto inicio (https://www.fi.upm.es/?id=proyectoinicio) para el curso " . $this->getCourse() . ":\n";
            $enlace = \Yii::createObject([
                'class' => Enlace::className(),
                'link' => "https://www.fi.upm.es/docs/estudios/orientacion/995_3ContenidoProyectoInicio2017.pdf",
                'caption' => $msg
            ]);
            if($enlace->validate()) {
                // $result = $this->getRequest()->hideKeyboard()->caption($link->caption)->sendDocument($link->link);
                $msg = $enlace->caption . $enlace->link;
                $result = $this->getRequest()->hideKeyboard()->sendMessage($msg);
                $this->stopConversation();
                return $result;
            } else {
                $result = $this->getRequest()->sendMessage("Lo siento no he podido enviarte el documento con la información del proyecto inicio. Prueba de nuevo mas tarde.");
                $this->stopConversation();
                return $result;
            }
        }
        elseif ($text === 'Tutor curricular')
        {
            $result = $this->getRequest()->hideKeyboard()->sendMessage("Para consultar tu tutor curricular, ejecuta este comando /tutor y sigue las instruciones.");
            $this->stopConversation();
            return $result;
        }
        elseif ($text === 'Equipo PI')
        {
            return $this->nextStep("EquipoPI");
        }
        elseif ($text === 'Proyecto mentor')
        {
            $msg = "Aquí tienes el documento con la información del proyecto mentor para el curso " . $this->getCourse() . ":\n";
            $msg .= "https://www.fi.upm.es/index.php?id=proyectomentor";
            $result = $this->getRequest()->hideKeyboard()->sendMessage($msg);
            $this->stopConversation();
            return $result;
        }
        elseif ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }


        $result = $this->stopConversation();

        return $result;

    }


    public function processEquipoPI($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $keyboard [] = [self::CANCELAR];

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("Introduce su numero de DNI (sin letra) para consultar su equipo y su turno:\n (No almacenamos ningun tipo de info en el servidor lo utilizamos para consultar al información)");
        }

        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['dni'] = $text;

        return $this->nextStep();

    }


    public function processEquipoPIDni($text)
    {
        $keyboard [] = [self::CANCELAR];

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

//        if ($this->isProcessed() || empty($text))
//        {
//            return $this->previousStep();
//        }

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $dni = $this->getConversation()->notes['dni'];

        $alumnoGrupoPI = ProyectoInicioRepository::getGrupoPI($dni);

        if ($alumnoGrupoPI == null || $alumnoGrupoPI->nombre == "")
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("DNI no válido. Por favor, vuelva a intentarlo. Cualquier duda vaya a la web oficial: https://www.fi.upm.es/index.php?id=piequipos.");
        }


        $mensaje = "";


        if ($alumnoGrupoPI !== null && $alumnoGrupoPI !== [] &&
            $alumnoGrupoPI->nombre !== null && $alumnoGrupoPI->nombre !== "" &&
            $alumnoGrupoPI->apellidos !== null && $alumnoGrupoPI->apellidos !== "")
        {
            $mensaje .= "Hola *$alumnoGrupoPI->nombre $alumnoGrupoPI->apellidos*.\n";
        } else {
            $mensaje .= "Hola *$dni*.\n";
        }

        if ($alumnoGrupoPI->nMat !== null && $alumnoGrupoPI->nMat !== "")
        {
            $mensaje .= " - Número matricula: $alumnoGrupoPI->nMat\n";
        }

        if ($alumnoGrupoPI->plan !== null && $alumnoGrupoPI->plan !== "")
        {
            $mensaje .= " - Plan de estudios: $alumnoGrupoPI->plan\n";
        }

        if ($alumnoGrupoPI->equipoPI !== null && $alumnoGrupoPI->equipoPI !== "")
        {
            $mensaje .= " - Equipo de P.I.: $alumnoGrupoPI->equipoPI\n";
        }

        if ($alumnoGrupoPI->nMat !== null && $alumnoGrupoPI->nMat !== "")
        {
            $mensaje .= " - nMatricula: $alumnoGrupoPI->nMat\n";
        }

        if ($alumnoGrupoPI->correoUPM !== null && $alumnoGrupoPI->correoUPM !== "")
        {
            $mensaje .= " - nMatricula: $alumnoGrupoPI->correoUPM\n";
        }

        if ($alumnoGrupoPI->turno !== null && $alumnoGrupoPI->turno !== "" &&
            $alumnoGrupoPI->turnoMsg !== null && $alumnoGrupoPI->turnoMsg !== "" &&
            $alumnoGrupoPI->horaTurno !== null && $alumnoGrupoPI->horaTurno !== "")
        {
//            $mensaje .= "Te ha tocado en el turno: \"$alumnoGrupoPI->turno\". Por lo tanto, tienes que presentarte en la "
//            $mensaje .= "Escuela Técnica Superior de Ingenieros Informáicos el día ";
//            $mensaje .= "4 de septiembre de 2017 "; // @TODO: @CHANGE
//            $mensaje .= "a las "
//            $mensaje .= "\n";
            $mensaje .= "$alumnoGrupoPI->turnoMsg\n";
        }




        $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
        $this->stopConversation();

    }






    private function getCourse()
    {
        $year = intval(date("y"));

        if (intval(date("m")) <= 7)
            $year--;

        $year2 = $year+1;

        $ret = "20" . $year . "/". "20" . $year2;
        return $ret;
    }


}