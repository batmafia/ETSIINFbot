<?php

function getCafetaPDF($chat_id,$date){

	sendChatAction('upload_photo',$chat_id);

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, 'http://www.fi.upm.es/?pagina=228');
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$cafeta = curl_exec($ch);

	if (!curl_errno($ch)) {
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200:
				break;
            default:
               	sendChatAction('typing',$chat_id);
               	sendMessage("⚠️ *Error al gestionar la petición con el servidor HTTP. No ha sido posible conectar con la cafetería. Código HTTP: $http_code*\n",$chat_id,"true");
                break;
        }
    }else{
       	if (curl_errno($ch) === 7){
       		sendMessage("⚠️ *No se ha podido conectar con la cafetería. Error (". curl_errno($ch).") COULDNT CONNECT.*\n",$chat_id,"true");
       		return "ERR";
       	}else{
       		sendMessage("⚠️ *No se ha podido conectar con la cafetería. Error (". curl_errno($ch).") OTHER.*\n",$chat_id,"true");
       		return "ERR";
       	}
	}

	curl_close($ch);

	$cafeta2 = substr($cafeta, strpos($cafeta, '<a class="pdf" href="') + strlen('<a class="pdf" href="') ); 
	$cafeta3 = substr($cafeta2, 0 ,strpos($cafeta2, '"'));

	$menutitle2 = substr($cafeta, strpos($cafeta, 'Men&uacute;') + strlen('Men&uacute;') ); 
	$menutitle3 = substr($menutitle2, 0 ,strpos($menutitle2, '<'));

	echo $menutitle3;

	$parts = explode(" ", $menutitle3);
	$months = array(
		"Enero",
		"Febrero",
		"Marzo",
		"Abril",
		"Mayo",
		"Junio",
		"Julio",
		"Agosto",
		"Septiembre",
		"Octubre",
		"Noviembre",
		"Diciembre"
	);


	$old = strtotime("today") > strtotime( $parts[count($parts)-3]."-".(array_search(ucfirst($parts[count($parts)-1]), $months)+1)."-".date("Y") );

	if ($old){
		sendMessage("⚠️ El menú subido en la web de la cafetería es antiguo *(Menú$menutitle3)*, por lo que no se enviará. Pruebe más tarde.",$chat_id,"true");
		return;
	}

	$name1 = date("YmdHis",time());

	$content = file_get_contents('http://www.fi.upm.es/'.$cafeta3);
	$nameoffile = 'cafeta'.$name1.'.pdf';
	$directfile = '/usr/share/nginx/www/batmafiabot/'.$nameoffile;
	file_put_contents($directfile, $content);

	sendChatAction('upload_photo',$chat_id);

	exec("convert -density 200 ".$directfile." cafeta".$name1."crop.jpg");
	sendChatAction('upload_photo',$chat_id);
	unlink(realpath($directfile));

	sendMessage("🍔 *Último menú disponible:\n* Menú".$menutitle3,$chat_id,"true");
	$caption = "Menú de la cafeta de";
	sendChatAction('upload_photo',$chat_id);

	if ($date == 'lunes'){
	 	exec("convert cafeta".$name1."crop.jpg -crop 330x775+320+330 cafeta".$name1."out.jpg");
	 	$caption = $caption."l lunes";

	 }else if ($date == 'martes'){
	 	exec("convert cafeta".$name1."crop.jpg -crop 330x775+670+330 cafeta".$name1."out.jpg");
	 	$caption = $caption."l martes";

	 }else if ($date == 'miercoles'){
		exec("convert cafeta".$name1."crop.jpg -crop 330x775+1050+330 cafeta".$name1."out.jpg");
	 	$caption = $caption."l miercoles";

	}else if ($date == 'jueves'){
		exec("convert cafeta".$name1."crop.jpg -crop 330x775+1400+330 cafeta".$name1."out.jpg");
		$caption = $caption."l jueves";

	}else if ($date == 'viernes'){
		exec("convert cafeta".$name1."crop.jpg -crop 340x775+1760+330 cafeta".$name1."out.jpg");
		$caption = $caption."l viernes";

	}else{
		exec("mv cafeta".$name1."crop.jpg cafeta".$name1."out.jpg");
		$caption = $caption." la semana";	
		
	}

	//  											Ancho x Alto + X + Y
	// 	exec("convert cafeta".$name1."crop.jpg -crop 320x720+200+300 ".$name1."out.jpg");

	if ($date!=="semana"){
		// OCR MENU CAFETA
		$post_fields2 = array('apikey'     => 'helloworld',
							'url'  => 'http://www.diegofpb.com/batmafiabot/cafeta'.$name1.'out.jpg',
							'language'     => 'spa'
							);

		$ch2 = curl_init(); 
		curl_setopt($ch2, CURLOPT_URL, 'https://api.ocr.space/Parse/Image');
		curl_setopt($ch2, CURLOPT_HEADER, 0);
		curl_setopt($ch2, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); //aqui curl
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_fields2); 
		$output2 = curl_exec($ch2);
		curl_close($ch2);
		
		$myOCRjason = json_decode($output2, true);

		$myOCR= $myOCRjason['ParsedResults'][0]['ParsedText'];
		sendMessage("⚠️ *Sistema OCR en pruebas:*\n$myOCR",$chat_id,"true");

	}

	unlink(realpath("cafeta".$name1."crop.jpg"));
	
	$namefinal = "cafeta".$name1."out.jpg";
	sendChatAction('upload_photo',$chat_id);
	sendPhoto($namefinal,$chat_id,$caption);
}


function getBusTimes($chat_id,$busline,$where){

	$flag = 0;

	if (strtoupper($where) === "FI"){
		if ($busline === '591'){
			$stopid = "08411";
		}else if ($busline === '865') {
			$stopid = "17573";
		}else if ($busline === '571' || $busline === '573'){
			$stopid = "08771";
		}else{
			$flag = 1;
			sendMessage("No has indicado una línea válida.",$chat_id,"true");
		}
	}elseif (strtoupper($where) === "MADRID"){
		if ($busline === '591'){
			$stopid = "08380";
		}else if ($busline === '865') {
			$stopid = "17556";
		}else if ($busline === '571'){
			$stopid = "15782";
		}else if ($busline === '573'){
			$stopid = "18248";
		}else{
			$flag = 1;
			sendMessage("No has indicado una línea válida.",$chat_id,"true");
		}
	}else{
		$flag = 1;
		sendMessage("No has indicado un lugar válido.",$chat_id,"true");
	}

	if ($flag === 0){
		$FIBUS = "http://api.interurbanos.welbits.com/v1/stop/$stopid";
		$array = file_get_contents($FIBUS);
		$myjson = json_decode($array, true);

		$narrives = count($myjson['lines']);

		$msgtotal = "Próximos autobuses de la línea $busline desde $where:\n";
		$cont = 0;

		for ($i=0;$i<$narrives;$i++){
			echo $myjson['lines'][$i]['lineNumber']."\n";
			if($myjson['lines'][$i]['lineNumber'] === $busline){
				$cont++;
				echo substr($myjson['lines'][$i]['waitTime'],-3)."\n";
				if (substr($myjson['lines'][$i]['waitTime'],-3)==="min"){
					$msgtotal .= "Saldrá en ".$myjson['lines'][$i]['waitTime']."\n";
				}else{
					$msgtotal .= "Saldrá a las ".$myjson['lines'][$i]['waitTime']."\n";
				}	
			}
		}

		if ($cont !== 0){
			sendMessage($msgtotal,$chat_id,"true");
		}else{
			sendMessage("No hay próximos autobuses ahora mismo",$chat_id,"true");
		}
	}
}


?>