<?php
	$executeXML = curl_init();
	curl_setopt($executeXML, CURLOPT_URL, "http://localhost/proyectos/bulksales/?view=xml&mode=xmlBancaribe");
	curl_setopt($executeXML, CURLOPT_HEADER, 0);
	curl_exec($executeXML);

	curl_setopt($executeXML, CURLOPT_URL, "http://localhost/proyectos/bulksales/?view=xml&mode=xml");
	curl_setopt($executeXML, CURLOPT_HEADER, 0);
	curl_exec($executeXML);

	curl_setopt($executeXML, CURLOPT_URL, "http://localhost/proyectos/bulksales/?view=xml&mode=xmlVenezuela");
	curl_setopt($executeXML, CURLOPT_HEADER, 0);
	curl_exec($executeXML);

	curl_setopt($executeXML, CURLOPT_URL, "http://localhost/proyectos/bulksales/?view=xml&mode=xmlPrevisegura");
	curl_setopt($executeXML, CURLOPT_HEADER, 0);
	curl_exec($executeXML);

	curl_close($executeXML);
?>
