<?php

$gauss_ep="https://www.upm.es/comun_gauss/publico/api";
$upmapi_ep="https://www.upm.es/wapi_upm/academico/comun/index.upm/v2";

function getAsignaturas($plan,$anio){
	// @TODO: SIN TERMINAR
	// Ejemplo datos: $plan=10II 	$anio=201617
	global $upmapi_ep;

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "$upmapi_ep/plan.json/$plan/asignaturas?anio=$anio");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$dataCurl = curl_exec($ch);
	curl_close($ch);

	$json = json_decode($dataCurl, true);
}

function getInfoAsignatura($plan,$codAsignatura,$semestre,$anio){

	// Ejemplo datos: $plan=10II 	$codAsignatura=105000004 	$semestre=1S	$anio=2016-17
	global $gauss_ep;

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "$gauss_ep/$anio/$semestre/$plan"."_"."$codAsignatura.json");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$dataCurl = curl_exec($ch);

	if (!curl_errno($ch)) {
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200:
				break;
            default:
               	$mensaje="⚠️ *Error al gestionar la petición con el servidor HTTP. No ha sido posible conectar con la API. Código HTTP: $http_code*\n";
                break;
        }
    }else{
       	if (curl_errno($ch) === 7){
       		$mensaje="⚠️ *No se ha podido conectar con la API. Error (". curl_errno($ch).") COULDNT CONNECT.*\n";
       		return;
       	}else{
       		$mensaje="⚠️ *No se ha podido conectar con la API. Error (". curl_errno($ch).") OTHER.*\n";
       		return;
       	}
	}



	curl_close($ch);

	$json = json_decode($dataCurl, true);

	$nombre = $json['nombre'];
	$ects =	$json['ects'];
	$guiaPDF = $json['guia'];
	$departamento = $json['depto'];
	$fechaUpdate = $json['fecha_actualizacion'];
	$numeroProfesores = count($json['profesores']);

	$mensaje = "La asignatura *$nombre* pertenece al departamento de *$departamento*, tiene un peso de *$ects ects* y tienes a *$numeroProfesores profesores* dispuestos a ayudarte. A continuación puedes descargarte la guía de aprendizaje de este curso.\n
		(Información actualizada en $fechaUpdate)\n";
	
	echo $mensaje;
}

function getInfoProfesores($plan,$codAsignatura,$semestre,$anio){

	// Ejemplo datos: $plan=10II 	$codAsignatura=105000004 	$semestre=1S	$anio=2016-17
	global $gauss_ep;

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "$gauss_ep/$anio/$semestre/$plan"."_"."$codAsignatura.json");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$dataCurl = curl_exec($ch);

	if (!curl_errno($ch)) {
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200:
				break;
            default:
               	$mensaje="⚠️ *Error al gestionar la petición con el servidor HTTP. No ha sido posible conectar con la API. Código HTTP: $http_code*\n";
                break;
        }
    }else{
       	if (curl_errno($ch) === 7){
       		$mensaje="⚠️ *No se ha podido conectar con la API. Error (". curl_errno($ch).") COULDNT CONNECT.*\n";
       		return;
       	}else{
       		$mensaje="⚠️ *No se ha podido conectar con la API. Error (". curl_errno($ch).") OTHER.*\n";
       		return;
       	}
	}



	curl_close($ch);

	$json = json_decode($dataCurl, true);

	$nombre = $json['nombre'];
	$fechaUpdate = $json['fecha_actualizacion'];
	$numeroProfesores = count($json['profesores']);

	$mensaje = "A continuación, podrás pedir ayuda a los siguientes profesores que imparten *$nombre*:\n\n";

	for ($i=0;$i<$numeroProfesores;$i++){
		$coordinadorProfesor = false;
		$nombreProfesor = $json['profesores'][$i]['nombre'];
		$apellidosProfesor = $json['profesores'][$i]['apellidos'];
		$emailProfesor = $json['profesores'][$i]['email'];
		$despachoProfesor = $json['profesores'][$i]['despacho'];
		$coordinadorProfesor = $json['profesores'][$i]['coordinador'];
		$numeroTutorias = count($json['profesores'][$i]['tutorias']);

		$mensaje.="$nombreProfesor $apellidosProfesor";

		if($coordinadorProfesor==="true"){
			$mensaje.=" (_Coordinador_)";
		}

		$mensaje.="\nDespacho: $despachoProfesor\nCorreo: $emailProfesor\nTutorias:\n";

		for ($z=0;$z<$numeroTutorias;$z++){
			$diaTutoria = $json['profesores'][$i]['tutorias'][$z]['dia'];
			$horaIniTutoria = $json['profesores'][$i]['tutorias'][$z]['hora_inicio'];
			$horaFinTutoria = $json['profesores'][$i]['tutorias'][$z]['hora_fin'];
			$observacionesTutoria = $json['profesores'][$i]['tutorias'][$z]['observaciones'];

			if($diaTutoria!==null){
				switch ($diaTutoria){
					case 1:
					$mensaje.="Lun de $horaIniTutoria a $horaFinTutoria\n";
					break;
					case 2:
					$mensaje.="Mar de $horaIniTutoria a $horaFinTutoria\n";
					break;
					case 3:
					$mensaje.="Mie de $horaIniTutoria a $horaFinTutoria\n";
					break;
					case 4:
					$mensaje.="Jue de $horaIniTutoria a $horaFinTutoria\n";
					break;
					case 5:
					$mensaje.="Vie de $horaIniTutoria a $horaFinTutoria\n";
					break;
				}
			}else{
				$mensaje.="No ha especificado días de tutorias.\n";
			}
		}
		$mensaje.="\n";
	}

	echo $mensaje;
}


?>