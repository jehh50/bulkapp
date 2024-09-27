<?php
include (PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
  header('location:index.php');
} else {
  include_once (MODEL_DIR . 'xmlModel.php');
  $conexion = new database();
  if (isset($_GET['mode'])) {

    switch ($_GET['mode']) {
      case 'index':
        include (PUBLIC_DIR . 'general/header.php');
        include (PUBLIC_DIR . 'general/navbar.php');
        $servicio = $conexion->servicio();
        include (HTML_DIR . 'xml/index.php');
        include (PUBLIC_DIR . 'general/footer.php');

        break;
      #-------------------------------------------------------------------------------------------
      case 'xml':
        if ($_POST['fecha_']) { //Generado desde la web
          $fecha_ = str_replace('-', '', $_POST['fecha_']);
          $date = $fecha_;
          $json['response'] = 'true';
        } else {    //Generado automaticamente
          $fecha_ = date('Ymd');
          $date = date('Ymd');
          $json['response'] = 'true';
        }
        //ajustar el codigo del servicio
        $datosXML = $conexion->datosXML($fecha_, 1);

        $encabezado = '<bulk_sales xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="bulk_sales.xsd">
  <company code="7">
    <company_name>SEGUROS CAPITAL C.A.</company_name>
      <agencies>
        <agency code="805">
          <agency_name>CARACAS</agency_name>
            <transactions>';
        if (!empty($datosXML)) {
          $maxidTransaccion = $conexion->maxidTransaccion();
          $k = $maxidTransaccion['max_id'] == null ? 0 : $maxidTransaccion['max_id'];

          foreach ($datosXML as $xml) {
            $payment_type = 'AC';
            $fecha = new Datetime($xml['fecha_venta']);
            $fecha->modify('+1 month');
            $proximoMes = $fecha->format('Ymd');
            $fecha = explode("-",$xml['fecha_nacimiento']);
            $birth = $fecha[0].$fecha[1].$fecha[2];

            #PARAMETROS RELLENAR CON CERO  $xml['id_resultado']
            $long = strlen($k);
            $ceros = 19 - $long;
            $certificate_number = str_pad($k, $ceros, "0", STR_PAD_LEFT);

            $telfOficina = $xml['telf_ofi'] == '' ? '(0000)000.00.00' : $xml['telf_ofi'];

            $contenido[] = '
              <transaction id="' . $k . '">
                <type_code>E</type_code>
                <policy>
                  <call_id>' . $k . '</call_id>
                  <certificate_number>' . $certificate_number . '</certificate_number>
                  <product_code>'.$xml['codigo_producto'].'</product_code>
                  <plan_code>' . $xml['codplan'] . '</plan_code>
                  <issue_date>' . $xml['fecha_venta'] . '</issue_date>
                  <channel_code>20</channel_code>
                  <company_user/>
                  <simm_user/>
                  <currency_type>0</currency_type>
                  <frequency_code>2</frequency_code>
                  <policy_holder>
                    <person>
                      <name>' . $xml['nombre'] . '</name>
                      <document_type_code>' . $xml['nacionalidad'] . '</document_type_code>
                      <document_number>' . $xml['cedula'] . '</document_number>
                      <address>
                        <country_code>1</country_code>
                        <country_name>VENEZUELA</country_name>
                        <state_code>' . $xml['codigo_estado'] . '</state_code>
                        <state_name>' . rtrim($xml['estado']) . '</state_name>
                        <city_code>' . $xml['codigo_ciudad'] . '</city_code>
                        <city_name>' . rtrim($xml['ciudad']) . '</city_name>
                        <municipality_code>' . $xml['codigo_municipio'] . '</municipality_code>
                        <municipality_name>' . rtrim($xml['municipio']) . '</municipality_name>
                        <parish></parish>
                        <zip_code></zip_code>
                        <street></street>
                        <estate></estate>
                        <building></building>
                        <floor></floor>
                        <number></number>
                      </address>
                      <issue_date>' . $xml['fecha_venta'] . '</issue_date>
                      <type_person type="N">
                        <natural_person>
                          <last_name>' . $xml['apellido'] . '</last_name>
                          <last_update_date>' . $date . '</last_update_date>
                          <education_degree_code/>
                          <education_degree_name/>
                          <occuppation_code>125</occuppation_code>
                          <occuppation_name>URBANISTA</occuppation_name>
                          <birthdate>' . $birth . '</birthdate>
                          <gender>' . $xml['genero'] . '</gender>
                          <marital_status>S</marital_status>
                          <children_number>0</children_number>
                          <email>' . $xml['correo'] . '</email>
                          <home_telephone1>' . $xml['telf_hab'] . '</home_telephone1>
                          <cellular_phone>' . $xml['telf_celular'] . '</cellular_phone>
                          <company_name/>
                          <position/>
                          <income_amount>0</income_amount>
                          <office_telephone1>' . $telfOficina . '</office_telephone1>
                        </natural_person>
                      </type_person>
                    </person>
                  </policy_holder>
                  <policy_taker>
                    <person>
                      <name>' . $xml['nombre'] . '</name>
                      <document_type_code>' . $xml['nacionalidad'] . '</document_type_code>
                      <document_number>' . $xml['cedula'] . '</document_number>
                      <address>
                        <country_code>1</country_code>
                        <country_name>VENEZUELA</country_name>
                        <state_code>' . $xml['codigo_estado'] . '</state_code>
                        <state_name>' . rtrim($xml['estado']) . '</state_name>
                        <city_code>' . $xml['codigo_ciudad'] . '</city_code>
                        <city_name>' . rtrim($xml['ciudad']) . '</city_name>
                        <municipality_code>' . $xml['codigo_municipio'] . '</municipality_code>
                        <municipality_name>' . rtrim($xml['municipio']) . '</municipality_name>
                        <parish></parish>
                        <zip_code></zip_code>
                        <street></street>
                        <estate></estate>
                        <building></building>
                        <floor></floor>
                        <number></number>
                      </address>
                      <issue_date>' . $xml['fecha_venta'] . '</issue_date>
                      <type_person type="N">
                        <natural_person>
                          <last_name>' . $xml['apellido'] . '</last_name>
                          <last_update_date>' . $date . '</last_update_date>
                          <education_degree_code/>
                          <education_degree_name/>
                          <occuppation_code>125</occuppation_code>
                          <occuppation_name>URBANISTA</occuppation_name>
                          <birthdate>' . $birth . '</birthdate>
                          <gender>' . $xml['genero'] . '</gender>
                          <marital_status>S</marital_status>
                          <children_number>0</children_number>
                          <email>' . $xml['correo'] . '</email>
                          <home_telephone1>' . $xml['telf_hab'] . '</home_telephone1>
                          <cellular_phone>' . $xml['telf_celular'] . '</cellular_phone>
                          <company_name/>
                          <position/>
                          <income_amount>0</income_amount>
                          <office_telephone1>' . $telfOficina . '</office_telephone1>
                        </natural_person>
                      </type_person>
                    </person>
                  </policy_taker>
                  <policy_payer>
                    <person>
                      <name>' . $xml['nombre'] . '</name>
                      <document_type_code>1</document_type_code>
                      <document_number>' . $xml['cedula'] . '</document_number>
                      <address>
                        <country_code>1</country_code>
                        <country_name>VENEZUELA</country_name>
                        <state_code>' . $xml['codigo_estado'] . '</state_code>
                        <state_name>' . rtrim($xml['estado']) . '</state_name>
                        <city_code>' . $xml['codigo_ciudad'] . '</city_code>
                        <city_name>' . $xml['ciudad'] . '</city_name>
                        <municipality_code>' . $xml['codigo_municipio'] . '</municipality_code>
                        <municipality_name>' . $xml['municipio'] . '</municipality_name>
                        <parish></parish>
                        <zip_code></zip_code>
                        <street></street>
                        <estate></estate>
                        <building></building>
                        <floor></floor>
                        <number></number>
                      </address>
                      <issue_date>' . $xml['fecha_venta'] . '</issue_date>
                      <type_person type="N">
                        <natural_person>
                          <last_name>' . $xml['apellido'] . '</last_name>
                          <last_update_date>' . $date . '</last_update_date>
                          <education_degree_code/>
                          <education_degree_name/>
                          <occuppation_code>125</occuppation_code>
                          <occuppation_name>URBANISTA</occuppation_name>
                          <birthdate>' . $birth . '</birthdate>
                          <gender>' . $xml['genero'] . '</gender>
                          <marital_status>S</marital_status>
                          <children_number>0</children_number>
                          <email>' . $xml['correo'] . '</email>
                          <home_telephone1>' . $xml['telf_hab'] . '</home_telephone1>
                          <cellular_phone>' . $xml['telf_celular'] . '</cellular_phone>
                          <company_name/>
                          <position/>
                          <income_amount>0</income_amount>
                          <office_telephone1>' . $telfOficina . '</office_telephone1>
                        </natural_person>
                      </type_person>
                    </person>
                    <payment_mean>
                      <payment_mean_code>' . $xml['cod_cuenta'] . '</payment_mean_code>
                      <payment_mean_name>' . $xml['nombre_cuenta'] . '</payment_mean_name>
                      <payment_type type="' . $payment_type . '">
                      <account>
                      <number>' . $xml['cuenta'] . '</number>
                      </account>
                      </payment_type>
                    </payment_mean>
                  </policy_payer>
                  <beneficiaries/>
                  <policy_sellers>
                    <policy_seller type="V">
                      <document_type_code>1</document_type_code>
                      <document_number>505658514</document_number>
                      <name>CALL</name>
                      <last_name>CENTER</last_name>
                      <position_code>40</position_code>
                      <position>OPERADOR DE TLMK</position>
                    </policy_seller>
                  </policy_sellers>
                  <receipt>
                    <external_id>' . $k . '</external_id>
                    <begin_date>' . $xml['fecha_venta'] . '</begin_date>
                    <end_date>' . $proximoMes . '</end_date>
                    <total_amount>' . $xml['costo_prod'] . '</total_amount>
                    <payment>
                      <payment_mean_number>' . $xml['cuenta'] . '</payment_mean_number>
                      <insurance_account_number/>
                      <insurance_comission>0</insurance_comission>
                      <insurance_amount>0</insurance_amount>
                      <insurance_debit_transaction>0</insurance_debit_transaction>
                      <insurance_credit_transaction>0</insurance_credit_transaction>
                      <bank_account_number>0</bank_account_number>
                      <bank_comission>0</bank_comission>
                      <bank_amount>0</bank_amount>
                      <bank_ins_debit_transaction>0</bank_ins_debit_transaction>
                      <bank_credit_transaction>0</bank_credit_transaction>
                      <reinsurance_account_number>0</reinsurance_account_number>
                      <reinsurance_comission>0</reinsurance_comission>
                      <reinsurance_amount>0</reinsurance_amount>
                      <reinsurance_ins_debit_transaction>0</reinsurance_ins_debit_transaction>
                      <reinsurance_credit_transaction>0</reinsurance_credit_transaction>
                      <simm_account_number>0</simm_account_number>
                      <simm_comission>0</simm_comission>
                      <simm_amount>0</simm_amount>
                      <simm_ins_debit_transaction>0</simm_ins_debit_transaction>
                      <simm_credit_transaction>0</simm_credit_transaction>
                    </payment>
                  </receipt>
                </policy>
              </transaction>';

            $k++;

            $insertTransaccion = $conexion->insertTransaccion($k, $fecha_, $xml['id_resultado']);

          }
        }
        $fin = '      
          </transactions>
        </agency>
      </agencies>
    </company>
  <total_operations>' . count($datosXML) . '</total_operations>
</bulk_sales>';


        /* #ARCHIVO XML
         *
         * Corregir que la carpeta se cree sola en caso de no existir, y pasar el nombre de la carpeta
         * como variable
         * 
         */

        $hora = new DateTime("now", new DateTimeZone('America/Caracas'));
        $min = new DateTime("now", new DateTimeZone('America/Caracas'));
        $hora = $hora->format('G');
        $min = $min->format('i');

        $fileXml = $encabezado;
        $nombreXML = "{$fecha_}{$hora}{$min}_bulk_sales.xml";
        $handler = fopen("public/archivos/bancamiga/$nombreXML", "w+");
        fwrite($handler, $encabezado);

        // for ($i = 0; $i < $count; $i++) {
        //   $fileXml .= $contenido[$i];
        //   fwrite($handler, $contenido[$i]);
        // }

        foreach ($contenido as $item) {
          $fileXml .= $item;
          fwrite($handler, $item);
        }
        

        fwrite($handler, $fin);
        fclose($handler);

        #ARCHIVO MD5
        $fileXml .= $fin;
        $fileMd5 = md5($fileXml) . "  " . $nombreXML;
        $nameMd5 = "MD5SUM";
        $handler = fopen("public/archivos/bancamiga/$nameMd5", "w+");
        fwrite($handler, $fileMd5);
        fclose($handler);
        $nameMd5 = "{$fecha_}{$hora}{$min}_MD5SUM";
        $handler = fopen("public/archivos/bancamiga/$nameMd5", "w+");
        fwrite($handler, $fileMd5);
        fclose($handler);

        echo json_encode($json);

        break;


      default:
        header('location:' . HTML_DIR . 'error.html');
        break;
    }
  }
}