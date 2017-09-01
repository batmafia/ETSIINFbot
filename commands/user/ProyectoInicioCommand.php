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
    protected $description = 'Información acerca del Proyecto Inicio';
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
            $msg = "Introduce su numero de DNI (sin letra) para consultar su equipo y su turno.\n";
            $msg .= "(No almacenamos ningun tipo de info en el servidor lo utilizamos para consultar la información)";
            return $this->getRequest()->markdown()->keyboard($keyboard)->sendMessage($msg);
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

        if ($alumnoGrupoPI == null  || $alumnoGrupoPI == [] || $alumnoGrupoPI->nombre == "")
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("DNI no válido. Por favor, vuelva a intentarlo. Si el fallo persiste, vaya a la web oficial: https://www.fi.upm.es/index.php?id=piequipos.");
        }


        $mensaje = "";


        if ($alumnoGrupoPI->equipoPI == null || $alumnoGrupoPI->equipoPI == "")
        {
            $mensaje .= "Hola alumno con dni " . $dni . ".\n";
            $mensaje .= "Estoy viendo que este no es tu primer año, por lo que no puedes realizar el Proyecto Inicio ni tampoco tienes un equipo asignado.\n";
            $mensaje .= "Te recomiendo que sigas usando el resto de comandos que ofrecemos en el bot, ya que éste no te va a resultar de mucha utilidad.\n";
            $mensaje .= "Puedes consultar todos los comandos disponibles en /help.\n";
            $mensaje .= "Saludos ;)";

        }
        else
        {

            if ($alumnoGrupoPI !== null && $alumnoGrupoPI !== [] &&
                $alumnoGrupoPI->nombre !== null && $alumnoGrupoPI->nombre !== "" &&
                $alumnoGrupoPI->apellidos !== null && $alumnoGrupoPI->apellidos !== "") {
                $mensaje .= "Hola *$alumnoGrupoPI->nombre $alumnoGrupoPI->apellidos*.\n";
            } else {
                $mensaje .= "Hola *$dni*.\n";
            }

            if ($alumnoGrupoPI->nMat !== null && $alumnoGrupoPI->nMat !== "") {
                $mensaje .= " - Número matrícula: *$alumnoGrupoPI->nMat*\n";
            }

            if ($alumnoGrupoPI->correoUPM !== null && $alumnoGrupoPI->correoUPM !== "") {
                $mensaje .= " - Correo institucional: $alumnoGrupoPI->correoUPM\n";
            }

            if ($alumnoGrupoPI->plan !== null && $alumnoGrupoPI->plan !== "") {
                $mensaje .= " - Plan de estudios: *$alumnoGrupoPI->plan*\n";
            }


            $mensaje .= " - Equipo de P.I.: *$alumnoGrupoPI->equipoPI*\n";


            if ($alumnoGrupoPI->turno !== null && $alumnoGrupoPI->turno !== "") {
                $mensaje .= " - Turno: *$alumnoGrupoPI->turno*\n";
            }



            $phoneIcon = "\xF0\x9F\x93\x9E";
            $mailIcon = "\xF0\x9F\x93\xA7";
            $departmentIcon = "\xF0\x9F\x8F\xA2";

            $nMat = $alumnoGrupoPI->nMat;
            $tutoria = TutorRepository::getTutoria(urlencode($nMat));


            if ($tutoria !== null && $tutoria !== [] && $tutoria !== "") {

                $tutor = $tutoria[1];

                if ($tutor->nombre == "" && $tutor->apellidos == "" && $tutor->departamento == "")
                {
                    $mensaje .= "\nParece que no tienes un tutor asignado.\n";
                    $mensaje .= "Contacta con subdirección de alumnos para mas información.";
                    $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
                    return $this->stopConversation();
                }
                else
                {

                    //$mensaje .= " desde el curso *$tutor->curso*,";
                    $mensaje .= "\nA continuación te mostramos también quien es tu tutor curricular quien te guiará y ayudará en el camino por la escuela ([mas info aquí](https://www.fi.upm.es/?id=tutoriacurricular)):\n";


                    if ($tutor->enlace !== null && $tutor->enlace !== "") {
                        $mensaje .= "[$tutor->nombre $tutor->apellidos]($tutor->enlace)";
                    } else {
                        $mensaje .= "*$tutor->nombre $tutor->apellidos*";
                    }

                    if ($tutor->departamento !== null && $tutor->departamento !== "") {
                        $mensaje .= " del *$tutor->departamento*\n";
                    }

                    if ($tutor->nombreEmail !== null && $tutor->dominioEmail !== null &&
                        $tutor->nombreEmail !== "" && $tutor->dominioEmail !== "") {
                        $mensaje .= "$mailIcon Email: $tutor->nombreEmail@$tutor->dominioEmail\n";
                    }

                    if ($tutor->despacho !== null && $tutor->despacho !== "") {
                        $mensaje .= "$departmentIcon Despacho: *$tutor->despacho*\n";
                    }

                    if ($tutor->telefono !== null && $tutor->telefono !== "") {
                        $mensaje .= "$phoneIcon Teléfono: ";
                        if (strpos($tutor->telefono, "+34") === false) {
                            $mensaje .= "+34";
                        }
                        $mensaje .= "$tutor->telefono\n";

                    }
                }
            }





            if ($alumnoGrupoPI->turno !== null && $alumnoGrupoPI->turno !== "" &&
                $alumnoGrupoPI->turnoMsg !== null && $alumnoGrupoPI->turnoMsg !== "" &&
                $alumnoGrupoPI->horaTurno !== null && $alumnoGrupoPI->horaTurno !== "")
            {
                $mensaje .= "Te ha tocado en el turno: \"$alumnoGrupoPI->turno\". Por lo tanto, tienes que presentarte en la ";
                $mensaje .= "Escuela Técnica Superior de Ingenieros Informáicos el día ";
                $mensaje .= "4 de septiembre de 2017 "; // @TODO: @CHANGE
                $mensaje .= "a las ";
                $mensaje .= "$alumnoGrupoPI->horaTurno:00 ";
                $mensaje .= "en el hall del bloque 1 de la escuela. ";
                $mensaje .= "\n";
//            $mensaje .= "\n$alumnoGrupoPI->turnoMsg\n";
            }

        }


        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
        $this->stopConversation();
        return $result;


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