<?php

namespace app\models\repositories;

class InfoRepository
{

    private static $infoArray = [
        'Secretaria' => [
            'Info' => "*Secretaría*
La Sección de Gestión Administrativa gestiona los trámites relacionados con los expedientes de alumnos de Ingenieria, Grado, Máster y Doctorado, así como el Registro de la Escuela.
*Web*: https://www.etsiinf.upm.es/?id=servicios/secretaria
*Lugar*: Bloque 3, planta 1
*Horario de mañanas*: Lunes a Viernes: 9:00 - 14:00\n*Horario de Tardes*: Lunes y Miercoles 15:30 - 17:30
*Teléfono*: +34913367407
*Correo*: secretaria@fi.upm.es
*Trámites administrativos*: https://www.etsiinf.upm.es/?id=servicios/tramites
*Carta de servicios*: https://www.etsiinf.upm.es/docs/estructura/servicios/195\\_Triptico\\_Secretaria\\_Alumnos.pdf",  // to skip _ -> \_ -> to skip \ -> \\_
            'Trámites' => 'https://www.etsiinf.upm.es/?id=servicios/tramites'
            // 'Trámites' => [
            //      'Preguntas frecuentes' => 'https://www.etsiinf.upm.es/?pagina=1921',
            //      '...' => '...',
            //      '...' => '...',
            //      'Acreditación de nivel B2 de lengua' => 'https://www.etsiinf.upm.es/?pagina=1921',
            //      '...' => '...',
            //      'Otros trámites de Ingeniería Informática (Plan 96)' => '...'
            //  ],
        ],
        'Publicaciones' => [
            'Info' => "*Publicaciones*
El Servicio de Publicaciones de la Escuela ofrece los siguentes servicios de atención al alumnado, al profesorado y a los diferentes departamentos y servicios del centro y cualquier persona interesada.
*Web*: https://www.fi.upm.es/?pagina=219
*Lugar*: Bloque 6, planta baja
*Horario de mañanas*: Lunes a Viernes: 9:00 - 14:00\n*Horario de Tardes*: Lunes y Miercoles 15:00 - 18:00
*Teléfono*: +34913367367 y +34913364291
*Correo*: publicaciones@fi.upm.es
*Normativa del Servicio*: https://www.fi.upm.es/docs/estructura/servicios/219\\_NORMAS%20%20SERVICIO%20DE%20PUBLICACIONES.pdf
*Carta de servicios*: https://www.fi.upm.es/docs/estructura/1345\\_Triptico\\_Publicaciones\\_nuevo.pdf",  // to skip _ -> \_ -> to skip \ -> \\_
            'Servicios' => [
                 'Venta de Libros y Papelería' => '*Venta de Libros y Papelería*
En éste servicio encontrará disponible un amplio y diverso catálogo de libros publicados en la propia Escuela y de algunas editoriales externas que sirven para complementar los conocimientos sobre las distintas asignaturas que se imparten en la Escuela y además de un listado de papelería.
*Catálogo de Libros*: https://www.fi.upm.es/docs/servicios/papeleria/1457\\_CAT.%20NUEVO%20%202016-2017%20.pdf
*Listado de Papeleria*: https://www.fi.upm.es/docs/servicios/papeleria/1457\\_PAPELERIA.pdf
*AVISO*: Los precios son validos salvo error tipográfico.',
                 'Venta de Apuntes' => '*Venta de Apuntes*
El Servicio de Publicaciones ofrece un listado con los apuntes relativos a las asignaturas que se imparten en la Escuela.
*1º CURSO*: https://www.fi.upm.es/docs/servicios/Apuntes/1463\\_PRIMERO%202016-2017.pdf
*2º CURSO*: https://www.fi.upm.es/docs/servicios/Apuntes/1463\\_SEGUNDO%202016-2017.pdf
*Examenes cursos anteriores*: https://www.fi.upm.es/docs/servicios/Apuntes/1463\\_EXAMENES%20ANTERIORES%202016-17%20.pdf
*AVISO*: Los precios son validos salvo error tipográfico',
                 'Reprografía y Encuadernaciones' => 'https://www.fi.upm.es/?pagina=251',
                 'Fotocopias e Impresión Digital' => 'https://www.fi.upm.es/?pagina=250',
             ],
        ],
        'Biblioteca ETSIINF' => '
*Biblioteca ETSIINF*
*Lugar*: Bloque 3 planta 0
*Lugar futuro*: Bloque 1 planta 2
*Web*: http://www.fi.upm.es/?pagina=24
*Correo*: biblioteca.etsiinf@upm.es
*Twitter*: https://twitter.com/BibliotecaFIUPM',
        'Delegación de Alumnos' => '
*Delegación de Alumnos*
*Nombre*: Delegación de Alumnos.
*Web*: https://www.da.etsiinf.upm.es
*Web ETSIINF*: https://www.etsiinf.upm.es/?id=delegacion
*Correo*: delegacion@da.fi.upm.es
*Twitter*: https://twitter.com/daetsiinf',
        'Asociaciones' => [
            'Info asoc ETSIINF' => 'https://www.etsiinf.upm.es/?id=actividades/asociaciones',
            'Info asoc UPM' => 'http://www.upm.es/Estudiantes/Asociaciones',
            '' => '',
            'La Tuna' => '
*La Tuna*
*Nombre*: Tuna de Informática
*Descripción*: La Tuna es una experiencia que sólo puede vivirse en la universidad, es otra forma de sentir esta etapa complementando en lo social a lo intelectual.
La actividad principal es alegrar cantando a la gente allá donde estemos y sobre todo divertirnos. Nos podrás ver rondando a unas mozas bajo un balcón, de viaje en cualquier ciudad del planeta, representando a la ETSI Informáticos en cualquier certámen de tunas o simplemente cantando a tu compañera de clase en la cafetería.
Está abierta a todos los alumnos que tengan ganas de conocer gente nueva, aprender a tocar un instrumento, conocer mundo sin gastarse un duro y cantar sin tener que pasar por OT.
*Web*: http://tunadeinformatica.com/
*Correo*: tuna@tunadeinformatica.com
*Twitter*: https://twitter.com/tunainformatica',
            'ACM' => '
*ACM*
*Nombre*: ACM Capítulo de Estudiantes.
*Descripción*: ACM Capítulo de Estudiantes es una de las representaciones estudiantiles de ACM. Llevamos organizando actividades dentro y fuera de la facultad desde 1986, siendo uno de nuestros principales objetivos aplicar los propósitos culturales, educativos y científicos de ACM entre los estudiantes. Para ello, divulgamos de diferentes maneras el conocimiento informático, buscando siempre favorecer la formación complementaria del alumnado.
*Web*: http://acm.asoc.etsiinf.upm.es/
*Correo*: acm@fi.upm.es
*Twitter*: https://twitter.com/acmupm
*Facebook*: https://es-es.facebook.com/acmupm
*Telegram*: @acmupm
*Telegram Bot*: @acmupm\\_bot',  // to skip _ -> \_ -> to skip \ -> \\_
            'Histrión' => '
*Histrión*
*Nombre*: Agrupación de Teatro Histrión.
*Descripción*: Está dirigida a todos aquellos alumnos que deseen pasar un buen rato aprendiendo y disfrutando con el teatro. Las actividades que ofrece son muy diversas e interesantes: cursos de interpretación, festival de artistas, cursos de clown, improvisación, salidas a teatros profesionales y montajes de obras para el festival de teatro de la U.P.M.
*Web*: http://histrion.asoc.fi.upm.es/
*Correo*: histrion.fi.upm@gmail.com
*Twitter*: https://twitter.com/histrionupm',
            'ASCFI' => '
*ASCFI*
*Nombre*: Agrupación Socio-Cultural de la ETSI Informáticos (ASCFI).
*Descripción*: Bajo este epígrafe encontramos una asociación muy activa. Se mueve en ámbitos diversos como el mundo de la fotografía, el videoforum, acampadas y marchas, concursos de rol y simulación, radio, juegos de mesa.
*Web*: http://ascfi.asoc.fi.upm.es/
*Correo*: ascfi@alumnos.fi.upm.es
*Twitter*: https://twitter.com/ascfiupm',
            'Radio FI (ASCFI)' => '
*Radio FI (ASCFI)*
*Nombre*: Onda expansiva - Radio FI (ASCFI).
*Descripción*: Desde el curso 1999/2000 está funcionando una emisora de radio en la ETSI Informáticos. Está gestionada por alumnos de la agrupación socio-cultural de la Escuela (ASCFI).
Emite 24 horas al día, durante todo el año, desde la propia ETSI Informáticos para todo el suroeste de Madrid. Están en el 89.5 F.M.
*Web*: http://radio.asoc.fi.upm.es/
*Correo*: fifm.radio@gmail.com
*Twitter*: https://twitter.com/radiofifm y https://twitter.com/frikifilandia',
            'NERV' => '
*NERV*
*Nombre*: NERV.
*Descripción*: Somos una asociación dedicada a promocionar la cultura japonesa a través de todas sus facetas, como el manga, el anime, los videojuegos, etc. Nuestras actividades engloban proyecciones, torneos y cursos en la Escuela así como salidas a eventos culturales de interés. Disponemos de una amplia mangateca y consolas disponibles exclusivamente para nuestros socios. Búscanos en la zona de asociaciones (junto al bloque 1) y mantente al corriente de nuestras actividades a través de los siguientes enlaces.
*Web*: http://www.alumnos.fi.upm.es/~nerv/
*Correo*: asoc.nerv@gmail.com
*Twitter*: https://twitter.com/NervFI',
            'Alfa - Omega' => '
*Alfa - Omega*
*Nombre*: Alfa - Omega.
*Descripción*: Surge ante la necesidad de participar en una formación integral del alumno, objetivo último de la Universidad, que debe velar en igual medida por la formación académica, profesional y humana del estudiante.
Por eso desde la Asociación Alfa-Omega intentamos complementar la formación recibida mediante actividades: charlas, vídeos, visitas culturales...
La Asociación se inspira fuertemente en el humanismo cristiano, de ahí también las actividades religiosas que llevamos a cabo. No obstante la Asociación está abierta a todos los estudiantes, independientemente de sus creencias e ideologías.
*Web*: http://www.alumnos.fi.upm.es/~alfaomega/
*Correo*: alfaomega@alumnos.fi.upm.es
*Twitter*: https://twitter.com/AyO\\_FI', // to skip _ -> \_ -> to skip \ -> \\_
            'I.D.I.M.' => '
*I.D.I.M.*
*Nombre*: Investigación y Desarrollo de la Informática Musical (I.D.I.M.).
*Descripción*: Se centra sobre todo en la informática musical, aunque está abierta a todo tipo de música. Organizan diversos cursos de formación musical. Una de las principales metas es la formación de sus socios mediante cursos de muy diversos temas, suscripciones a importantes revistas de informática musical y concursos. Esta asociación dispone de un pequeño estudio de composición.
*Web*: http://idim.asoc.fi.upm.es/',
            'A.E.T.O' => '
*A.E.T.O*
*Nombre*: Asociación de Estudiantes en Tiempo de Ocio
*Correo*: aetoupm@gmail.com
*Twitter*: https://twitter.com/AETOupm'
        ],
        'Club Deportivo' => '
*Club Deportivo*
*Nombre*: Club Deportivo.
*Descripción*: Es uno de los diecinueve clubes de este tipo con que cuenta la U.P.M. y como tal participa en las competiciones organizadas por ésta. Participan en ligas por equipos de: baloncesto, voleibol, fútbol, fútbol sala, balonmano y rugby. Así mismo colabora con el área de deportes de la U.P.M. en la inscripción de participantes en competiciones individuales en deportes tan diversos como atletismo, natación, tenis, tiro con arco, squash, etc.
*Web*: http://www.cdfim.com/
*Correo*: cdfim@gmail.com
*Twitter*: https://twitter.com/cdfim',
        'Servicios' => [
            'Reserva salas' => 'https://www.fi.upm.es/aulas/view-schedule.php?sid=5',
            'WIFI' => [
                'FIWIFI' => '
La conexión al portal cautivo de la Facultad. Para realizar esta conexión sólo deberá realizar una petición de una página web en su navegador. Su solicitud será redirigida a una página web en la que se le pedirá que se autentique. Tras introducir correctamente el usuario y contraseña asignado por la Facultad (aquel que se usa para acceder a los ordenadores de las salas) será redirigido a una página web que le indicará que se ha conectado correctamente. La autentificación de este sistema es la misma que la del Acceso VPN y por tanto, si no se ha hecho ya, debe seguirse el mismo procedimiento de solicitud.
Una vez que vea esta página ya puede pedir a su navegador las páginas que desee. Conviene que no pierda la página anterior, para que cuando desee finalizar su sesión pulse Terminar Sesión y finalice.

*Más información*: https://www.fi.upm.es/?pagina=262',
                'WIFIUPM' => '
Poner tu correo institucional (...@alumnos.upm.es) y tu clave.

*Más información*: http://www.upm.es/UPM/InformaticaComunicaciones/wifi?id=7dc338900c392410VgnVCM10000009c7648a\\_\\_\\_\\_&fmt=detail&prefmt=articulo',
                'eduroam' => '
Poner tu correo institucional (...@alumnos.upm.es) y tu clave.

*Más información*: http://www.upm.es/UPM/InformaticaComunicaciones/wifi?id=e83fe60106778110VgnVCM10000009c7648a\\_\\_\\_\\_&fmt=detail&prefmt=articulo'
            ],
            'VPN' => [
                'INFO' => '*Más información*: https://www.fi.upm.es/?pagina=373',
                'Windows 10' => '*Guia*: https://www.fi.upm.es/docs/servicios/red-acceso/373\\_Guia\\_VPN-Windows\\_10.pdf',
                'Mac' => '*NOTA*: si tienes MacOS http://www.jpromero.com/2016/05/configuracion-vpn-etsiinf-upm.html',
                'Ubuntu' => '*Guia*: https://www.fi.upm.es/docs/servicios/red-acceso/373\\_Guia\\_Ubuntu\\_16.04.pdf',
                'Windows7' => '*Guia*: https://www.fi.upm.es/docs/servicios/red-acceso/373\\_Guia\\_VPN-Windows\\_7.pdf'
            ],
            'FTP' => '
*Acceso a directorios personales*
Los alumnos de la Facultad tienen la posibilidad de acceder a sus directorios de trabajo compartidos en Windows y Unix desde sus equipos personales. Este acceso se realizará mediante protocol SCP/SFTP con el servidor *www.alumnos.fi.upm.es con su usuario y contraseña habituales*. Linux dispone de comandos para scp y sftp mientras que en Windows se pueden instalar programas libres (WinSCP, Filezilla).

*Más información*: https://www.fi.upm.es/?pagina=372',
    'Seguridad informática' => 'https://www.fi.upm.es/?id=seguridad',
    'Quejas y sugerencias' => 'https://www.fi.upm.es/?id=politicacalidad/quejasysugerencias'
        ],
    ];

    /**
     * [getInfoArrat description]
     * @return Array infoArray with all the options and strings
     */
    public static function getInfoArray()
    {
        return self::$infoArray;
    }
}
