<script src="public/js/fieldsValidation.js"></script>

<div class="container" style="margin-top:20px;">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <form name="form1" id="form1" method="POST" action="?view=formulario&mode=buscarCliente" autocomplete="off">
              <div class="row ">
                <div class="col-sm-6 col-md-4 col-lg-3">
                  <h5>Número de cédula</h5>
                  <input type="text" class="form-control" placeholder="12624422" aria-describedby="dni" name="dni" id="dni" maxlength="11" autofocus style="margin-bottom: 5px;" required />
                  <input type="hidden" name="servicioId" id="servicioId" value="<?php echo $_SESSION['servicio_id']; ?>" />
                  <input type="submit" class="btn btn-success" value="Buscar" id="btn-buscar">
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- FIN DE DATOS DEL CLIENTE -->
        <!-- INICIO DEL FORMULARIO -->

        <?php
        if (empty($cliente) && empty($msg)){ } elseif($msg) {?>
          <div class="panel-body">
              <div class="form-group">
                <div class="row">
                  <div class="col-xs-10 col-sm-11 col-md-12 col-lg-12">
                    <div class='alert alert-success' role='alert'><?= $msg; ?></div>
                  </div>
                </div>
              </div>
            </div>
        <?php } else {
          if ($msg) { ?>
            <div class="panel-body">
              <div class="form-group">
                <div class="row">
                  <div class="col-xs-10 col-sm-11 col-md-12 col-lg-12">
                    <div class='alert alert-danger' role='alert'><?= $msg; ?></div>
                  </div>
                </div>
              </div>
            </div>

          <?php exit;
          } ?>
          <div class="row text-center" id="formularioCliente">
            <!-- Columna Izquierda: Tabla de compras -->
            <div class="col-lg-7 col-md-7 col-sm-11" style="float:none; display:inline-block; vertical-align:top;">
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="alert" style="background:#e9e9e9;" role="alert" id="customerData">
                    <strong>Datos del cliente:</strong>
                    <span id="customer">
                      <?php
                      $ultimoNombre = $ultimoEmail = $ultimoTelefono = $ultimoSegmento = $ultimoCedula = '';
                      foreach ($cliente as $c) {
                        if ($c['nombre_usuario'] !== $ultimoNombre) {
                          echo strtoupper($c['nombre_usuario']) . ' - ';
                          $ultimoNombre = $c['nombre_usuario'];
                        }
                        if ($c['email'] !== $ultimoEmail) {
                          echo strtolower($c['email']) . ' - ';
                          $ultimoEmail = $c['email'];
                        }
                        if ($c['telefono'] !== $ultimoTelefono) {
                          echo $c['telefono'] . ' - ';
                          $ultimoTelefono = $c['telefono'];
                        }
                        if ($c['cedula'] !== $ultimoCedula) {
                          echo $c['cedula'];
                          $ultimoCedula = $c['cedula'];
                        }
                      }
                      ?>
                    </span>
                  </div>
                  <div class="nav nav-pills" style="margin-bottom:5px;" role="navigation" aria-label="store" id="store">
                    <ul class="nav nav-pills">
                      <?php
                      $locales = [];
                      $firstTab = true;
                      foreach ($cliente as $c) {
                        if (!in_array($c['local_origen'], $locales)) {
                          $locales[] = $c['local_origen'];
                          $tabId = str_replace(' ', '', $c['local_origen']);
                          echo '<li class="' . ($firstTab ? 'active' : '') . '"><a href="#' . $tabId . '" data-toggle="tab">' . $c['local_origen'] . '</a></li>';
                          $firstTab = false;
                        }
                      }
                      ?>
                    </ul>
                  </div>
                  <div class="tab-content">
                    <?php
                    $locales = [];
                    $firstPane = true;
                    foreach ($cliente as $c) {
                      if (!in_array($c['local_origen'], $locales)) {
                        $locales[] = $c['local_origen'];
                        $tabId = str_replace(' ', '', $c['local_origen']);
                        echo '<div class="tab-pane fade' . ($firstPane ? ' in active' : '') . '" id="' . $tabId . '">';
                        echo '<table class="table table-responsive table-striped table-condensed">';
                        echo '<thead>
                          <tr>
                            <th class="text-center"><h5><strong>ID Cuota</strong></h5></th>
                            <th class="text-center"><h5><strong>Fecha de pago</strong></h5></th>
                            <th class="text-center"><h5><strong>Monto</strong></h5></th>
                            <th class="text-center"><h5><strong>Cuota</strong></h5></th>
                            <th class="text-center"><h5><strong>Tramo</strong></h5></th>
                            <th class="text-center"><h5><strong>Segmento</strong></h5></th>
                            <th class="text-center"><h5><strong>Estado</strong></h5></th>
                          </tr>
                          </thead>
                          <tbody>';
                        $i = 1;
                        $suma_monto = 0;
                        foreach ($cliente as $row) {
                          if ($row['local_origen'] == $c['local_origen']) {
                          $quote = round((float)(str_replace(",",".",$row['plata_por_cobrar'])),2);
                          $fechaFormateada = DateTime::createFromFormat('d/m/Y H:i', $row['fecha_pagar'])->format('d/m/Y');
                            echo '<tr>
                                      <th class="text-center" scope="row">' . $row['id_cuota'] . '</th>
                                      <td class="text-center">' . $fechaFormateada. '</td>
                                      <td class="text-center">' . $quote . '</td>
                                      <td class="text-center">' . $row['numero_cuota'] . '</td>
                                      <td class="text-center">' . $row['tramo_inicial'] . '</td>
                                      <td class="text-center">' . $row['segmento'] . '</td>
                                      <td class="text-center">' . ($row['status'] == 'Efectiva' ? $row['status'].' ✅' : ($row['status'] == 'No pago' ? $row['status'] .' ❌' : $row['status']) ) .  '</td>
                                    </tr>';
                            $suma_monto += $quote;
                            $i++;
                          }
                        }
                        echo '<tr>
                                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                                    <td class="text-center"><strong>' .$suma_monto . '</strong></td>
                                    <td colspan="4"></td>
                                  </tr>';
                        echo '</tbody></table></div>';
                        $firstPane = false;
                      }
                    }
                    ?>
                  </div>
                </div>
              </div>
            </div>
            <!-- Columna Derecha: Formulario -->
            <div class="col-lg-4 col-md-4 col-sm-11 col-xs-11" style="float:none; display:inline-block; vertical-align:top;">
              <div class="panel panel-default">
                <div class="panel-body">
                  <form name="form2" enctype="multipart/form-data" method="POST" onsubmit="return validateForm2();" action="?view=formulario&mode=registro" autocomplete="off">
                    <input type="hidden" id="dni_cliente" name="dni_cliente" value="<?php echo $cliente[0]['cedula']; ?>">
                    <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo $cliente[0]['id']; ?>">
                    <input type="hidden" id="usuario" name="usuario" value="<?php echo $_SESSION['id']; ?>">
                    <input type="hidden" id="servicio" name="servicio" value="<?php echo isset($_SESSION['servicio_id']) ? $_SESSION['servicio_id'] : ''; ?>">

                    <h2 class="mb-3"><span class="form-group"><strong>Datos de contacto</strong></span></h2>
                    <div class="form-group mb-3">
                      <label class="form-label">Contacto Efectivo</label>
                      <select class="form-control" name="contacto" id="contacto" required>
                        <option value='' disabled selected style='display:none;'>Seleccione...</option>
                        <option value='1'>Si</option>
                        <option value='0'>No</option>
                      </select>
                    </div>

                    <div class="form-group mb-3" id="d_efectivo">
                      <label class="form-label">Tipo de contacto</label>
                      <select class="form-control" name="efectivo" id="efectivo">
                        <?php foreach ($efectivo as $e) { ?>
                          <option value='' disabled selected style='display:none;'>Seleccione...</option>
                          <option value='<?php echo $e['id']; ?>'><?php echo $e['descripcion']; ?></option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="form-group mb-3" id="d_subContacto">
                      <label class="form-label">Motivo de contacto</label>
                      <select class="form-control" name="subContacto" id="subContacto">
                        <?php foreach ($subContacto as $s) { ?>
                          <option value='' disabled selected style='display:none;'>Seleccione...</option>
                          <option value='<?php echo $s['id']; ?>'><?php echo $s['descripcion']; ?></option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="form-group mb-3" id="d_noefectivo">
                      <label class="form-label">Motivo de NO contacto</label>
                      <select class="form-control" name="noefectivo" id="noefectivo">
                        <?php foreach ($noefectivo as $ne) { ?>
                          <option value='' disabled selected style='display:none;'>Seleccione...</option>
                          <option value='<?php echo $ne['id']; ?>'><?php echo $ne['descripcion']; ?></option>
                        <?php } ?>
                      </select>
                    </div>

                    <div id="formulario">
                      <section>
                        <h2 class="mb-3"><span class="form-group" id="formTitle"><strong>Gestión de Cobranza</strong></span></h2>
                      </section>

                      <div class="form-group mb-3" id="dPaymentPlan">
                        <label class="form-label">Plan de pago</label>
                        <select class="form-control" name="paymentPlan" id="paymentPlan">
                          <option value=''>Seleccione...</option>
                          <option value='1'>Monto total</option>
                          <option value='2'>Cuota</option>
                          <option value='3'>Pago personalizado</option>
                        </select>
                      </div>

                      <div class="form-group mb-3" id="dQuote">
                        <label class="form-label">ID de la cuota</label>
                        <input type="text" class="form-control" placeholder="5511223" aria-describedby="idQuote" name="idQuote" id="idQuote" />
                      </div>

                      <div class="form-group mb-3" id="dAmount">
                        <label class="form-label">Monto a abonar</label>
                        <input type="text" class="form-control" placeholder="545" aria-describedby="amount" name="amount" id="amount" oninput="onlyNumbers(this)" />
                      </div>

                      <div class="form-group mb-3" id="dPaymentDate">
                        <label class="form-label">Fecha de compromiso</label>
                        <ul class="list-unstyled">
                          <?php
                          for ($i = 0; $i < 3; $i++) {
                            $fecha = date('Y-m-d', strtotime("+$i day"));
                            $label = date('d/m/Y', strtotime("+$i day"));
                            echo "<li class='mb-2'>
                                      <input type='radio' id='paymentDate' name='paymentDate' value='$fecha' onclick=\"document.getElementById('paymentDate').value=this.value; required\"> $label
                                    </li>";
                          }
                          ?>
                        </ul>
                      </div>

                      <div class="form-group mb-3" id="dFullName">
                        <label class="form-label">Nombre y apellido</label>
                        <input type="text" class="form-control" placeholder="Simón Díaz" aria-describedby="fullName" name="fullName" id="fullName" oninput="onlyLetters(this)" />
                      </div>

                      <div class="form-group mb-3" id="dRelationship">
                        <label class="form-label">Parentesco</label>
                        <select class="form-control" name="relationship" id="relationship">
                          <option value=''>Seleccione...</option>
                          <option value='PADRE'>Padre</option>
                          <option value='MADRE'>Madre</option>
                          <option value='HIJO'>Hijo/Hija</option>
                          <option value='HERMANO'>Hermano/Hermana</option>
                          <option value='ESPOSA'>Esposo/Esposa</option>
                          <option value='OTRO'>Otro</option>
                        </select>
                      </div>

                      <div class="form-group mb-3" id="dObservaciones">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" rows=3 maxlength="250" name="observaciones" id="observaciones" oninput="upperCase(this);"></textarea>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-md btn-block">Guardar</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
        <!-- FIN DEL FORMULARIO -->
      </div>
    </div>
  </div>
</div>
</div>

<script src="public/js/formularioC.js"></script>