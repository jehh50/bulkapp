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
                  <input type="text" class="form-control" placeholder="12624422" value='12345678' aria-describedby="dni" name="dni" id="dni" maxlength="11" autofocus style="margin-bottom: 5px;" />
                  <input type="hidden" name="servicioId" id="servicioId" value="<?php echo $_SESSION['servicio_id']; ?>" />
                  <input type="submit" class="btn btn-success" value="Buscar" id="btn-buscar">
                </div>
              </div>
            </form>
          </div>

          <!-- FIN DE DATOS DEL CLIENTE -->
          <!-- INICIO DEL FORMULARIO -->
          <?php if (empty($cliente)) { echo $msg;if($msg){?>
            <div class="alert alert-warning" role="alert">
              No se encontraron datos del cliente.
            </div>
          <?php
          }} else {?>
            <div class="row" id="formularioCliente">
              <!-- Columna Izquierda: Tabla de compras -->
              <div class="col-lg-7 col-md-7 col-sm-12" style="margin-bottom:20px;">
                <div class="panel panel-default">
                  <div class="panel-body">
                    <div class="alert" style="background:#e9e9e9;" role="alert" id="customerData">
                      <strong>Datos del cliente:</strong> <span id="customer">
                        <?php
                        $ultimoNombre = $ultimoEmail = $ultimoTelefono = $ultimoSegmento ='';
                        foreach ($cliente as $c) {
                          if ($c['nombre'] !== $ultimoNombre) {
                            echo strtoupper($c['nombre']) . ' - ';
                            $ultimoNombre = $c['nombre'];
                          }
                          if ($c['email'] !== $ultimoEmail) {
                            echo strtoupper($c['email']) . ' - ';
                            $ultimoEmail = $c['email'];
                          }
                          if ($c['telefono'] !== $ultimoTelefono) {
                            echo $c['telefono'].' - ';
                            $ultimoTelefono = $c['telefono'];
                          }
                          if ($c['segmento'] !== $ultimoSegmento) {
                            echo strtoupper($c['segmento']);
                            $ultimoSegmento = $c['segmento'];
                          }
                          
                        }
                        ?>
                      </span>
                    </div>
                    <div class="nav nav-pills" role="navigation" aria-label="store" id="store" style="margin-bottom:5px;">
                      <ul class="nav nav-pills">
                        <?php
                        $locales = [];
                        $firstTab = true;
                        foreach ($cliente as $c) {
                          if (!in_array($c['nombre_local'], $locales)) {
                            $locales[] = $c['nombre_local'];
                            $tabId = str_replace(' ', '', $c['nombre_local']);
                            echo '<li class="' . ($firstTab ? 'active' : '') . '"><a href="#' . $tabId . '" data-toggle="tab">' . $c['nombre_local'] . '</a></li>';
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
                        if (!in_array($c['nombre_local'], $locales)) {
                          $locales[] = $c['nombre_local'];
                          $tabId = str_replace(' ', '', $c['nombre_local']);
                          echo '<div class="tab-pane fade' . ($firstPane ? ' in active' : '') . '" id="' . $tabId . '">';
                          echo '<table class="table table-responsive table-striped table-condensed">';
                          echo '<thead>
                          <tr>
                          <th style="text-align: center;"><h5><strong>#</strong></h5></th>
                          <th style="text-align: center;"><h5><strong>Fecha de compra</strong></h5></th>
                                    <th style="text-align: center;"><h5><strong>Monto de cuota</strong></h5></th>
                                    <th style="text-align: center;"><h5><strong>Número de cuota</strong></h5></th>
                                    <th style="text-align: center;"><h5><strong>Tramo</strong></h5></th>
                                    </tr>
                                </thead>
                                <tbody>';
                          $i = 1;
                          $suma_monto = 0;
                          foreach ($cliente as $row) {
                            if ($row['nombre_local'] == $c['nombre_local']) {
                              echo '<tr>
                                        <th style="text-align: center;" scope="row">' . $i . '</th>
                                        <td style="text-align: center;">' . $row['fecha'] . '</td>
                                        <td style="text-align: center;">' . $row['monto'] . '</td>
                                        <td style="text-align: center;">' . $row['numero_cuota'] . '</td>
                                        <td style="text-align: center;">' . $row['tramo_inicial'] . '</td>
                                      </tr>';
                              $suma_monto += floatval($row['monto']);
                              $i++;
                            }
                          }
                          echo '<tr>
                                    <td colspan="2" style="text-align: right;"><strong>Total</strong></td>
                                    <td style="text-align: center;"><strong>' . number_format($suma_monto, 2, ',', '.') . '</strong></td>
                                    <td></td>
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
              <div class="col-lg-5 col-md-5 col-sm-12">
                <div class="panel panel-default">
                  <div class="panel-body">
                    <form name="form2" enctype="multipart/form-data" method="POST" onsubmit="return validateForm2();" action="?view=formulario&mode=registro" autocomplete="off">
                      <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo $cliente[0]['cedula']; ?>">
                      <input type="hidden" id="usuario" name="usuario" value="<?php echo $_SESSION['id']; ?>">
                      <input type="hidden" id="servicio" name="servicio" value="<?php echo isset($_SESSION['servicio_id']) ? $_SESSION['servicio_id'] : ''; ?>">

                      <h2><span class="form-group"><strong>Datos de contacto</strong></span></h2>
                      <div class="form-group">
                        <span class="form-group">Contacto Efectivo</span>
                        <select class='selectpicker show-menu-arrow show-tick form-control' name="contacto" id="contacto">
                          <option value='' disabled selected style='display:none;'>Seleccione...</option>
                          <option value='1'>Si</option>
                          <option value='0'>No</option>
                        </select>
                      </div>

                      <div class="form-group" id="d_efectivo">
                        <span class="form-group">Motivo de contacto</span>
                        <select class="form-control" name="efectivo" id="efectivo">
                          <?php foreach ($efectivo as $e) { ?>
                            <option value='' disabled selected style='display:none;'>Seleccione...</option>
                            <option value='<?php echo $e['id']; ?>'><?php echo $e['descripcion']; ?></option>
                          <?php } ?>
                        </select>
                      </div>

                      <div class="form-group" id="d_noefectivo">
                        <span class="form-group">Motivo de NO contacto</span>
                        <select class="form-control" name="noefectivo" id="noefectivo">
                          <?php foreach ($noefectivo as $ne) { ?>
                            <option value='' disabled selected style='display:none;'>Seleccione...</option>
                            <option value='<?php echo $ne['id']; ?>' style='display:enable;'><?php echo $ne['descripcion']; ?></option>
                          <?php } ?>
                        </select>
                      </div>

                      <div id="formulario">
                        <section>
                          <h2><span class="form-group" id="formTitle"><strong>Gestión de Cobranza</strong></span></h2>
                        </section>

                        <div class="form-group" id="dPaymentPlan">
                          <span class="form-group">Plan de pago</span>
                          <select class="form-control" name="paymentPlan" id="paymentPlan">
                            <option value=''>Seleccione...</option>
                            <option value='1'>Monto total</option>
                            <option value='2'>Cuota</option>
                            <option value='3'>Pago personalizado</option>
                          </select>
                        </div>

                        <div class="form-group" id="dAmount">
                          <section>
                            <span class="form-group">Monto a abonar</span>
                          </section>
                          <input type="text" class="form-control" placeholder="545" aria-describedby="amount" name="amount" id="amount" oninput="onlyNumbers(this)" />
                        </div>

                        <div class="form-group" id="dPaymentDate">
                          <span class="form-group">Fecha de compromiso</span>
                          <ul style="list-style: none; padding-left:0">
                            <?php
                            for ($i = 0; $i < 3; $i++) {
                              $fecha = date('Y-m-d', strtotime("+$i day"));
                              $label = date('d/m/Y', strtotime("+$i day"));
                              echo "<li style='margin-right:8px; margin-top:5px;'>
                                      <input type='radio' name='paymentDate' value='$fecha' onclick=\"document.getElementById('paymentDate').value=this.value;\"> $label
                                    </li>";
                            }
                            ?>
                          </ul>
                        </div>

                        <div class="form-group" id="dFullName">
                          <span class="form-group">Nombre y apellido</span>
                          <input type="text" class="form-control" placeholder="Simón Díaz" aria-describedby="fullName" name="fullName" id="fullName" oninput="onlyLetters(this)" />
                        </div>

                        <div class="form-group" id="dRelationship">
                          <span class="form-group">Parentesco</span>
                          <select class="form-control" name="relationship" id="relationship">
                            <option value=''>Seleccione...</option>
                            <option value='1'>Padre</option>
                            <option value='2'>Madre</option>
                            <option value='3'>Hijo/Hija</option>
                            <option value='4'>Hermano/Hermana</option>
                            <option value='5'>Esposo/Esposa</option>
                            <option value='6'>Otro</option>
                          </select>
                        </div>

                        <div class="form-group" id="dObservaciones">
                          <span class="form-group" id="">Observaciones</span>
                          <textarea class="form-control" rows=3 maxlength="250" name="observaciones" id="observaciones" oninput="upperCase(this);"></textarea>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-success btn-md">Guardar</button>
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
<script src="public/js/fieldsValidation.js"></script>