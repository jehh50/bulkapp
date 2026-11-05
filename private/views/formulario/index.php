<style>
  /* ---------- Formulario de gestión (BS5) ---------- */
  .fg-grid{display:grid;grid-template-columns:1fr;gap:16px;align-items:start}
  @media (min-width:1000px){.fg-grid{grid-template-columns:400px minmax(0,1fr)}}

  .fg-card{background:#fff;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);padding:24px;display:flex;flex-direction:column;gap:20px}
  .fg-card--gold{border-top:5px solid var(--gold)}
  .fg-card--blue{border-top:5px solid var(--blue-700)}
  .fg-card--green{border-top:5px solid var(--bs-green)}

  .fg-card .form-control,.fg-card .form-select{height:44px;border-radius:10px;font-size:14px;color:var(--ink)}
  .fg-card .form-control:focus,.fg-card .form-select:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}
  .fg-card .form-control[readonly],.fg-card .form-control:disabled{background:#fff}
  .fg-card textarea.form-control{height:auto;min-height:96px}

  .fg-lbl{display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:var(--ink);margin:0}

  .fg-search .form-control{flex:1 1 220px;min-width:0;height:46px}
  .fg-btn{height:46px;border-radius:10px;font-weight:600}
  .fg-btn-sec{height:46px;padding:0 22px;border:1px solid #d1d5db;border-radius:10px;background:#fff;color:var(--ink);font-size:15px;font-weight:500}
  .fg-btn-sec:hover{background:#f4f6fa}

  .fg-search-row{display:flex;gap:10px;flex-wrap:wrap}
  .fg-error{margin-top:6px;padding:12px 16px;border-radius:10px;background:#fdecec;border:1px solid #f3c2c2;font-size:14px;color:#8a3f3f}

  .fg-h2{margin:0;font-size:19px;font-weight:600;color:var(--ink)}
  .fg-headrow{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}

  .fg-profile{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:14px;background:var(--blue-900);color:#fff}
  .fg-profile__avatar{flex:0 0 52px;width:52px;height:52px;border-radius:50%;background:#c9d6f0;color:var(--ink);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700}
  .fg-profile--found .fg-profile__avatar{background:var(--gold)}
  .fg-profile__name{font-size:16px;font-weight:600;line-height:1.25;overflow-wrap:anywhere}
  .fg-profile__sub{font-size:13px;color:var(--muted);margin-top:2px}

  .fg-fieldList{display:flex;flex-direction:column;border:1px solid #e3e9f5;border-radius:14px;overflow:hidden}
  .fg-field{display:flex;flex-direction:column;gap:2px;padding:10px 16px;background:#fff}
  .fg-field + .fg-field{border-top:1px solid #eef0f4}
  .fg-field:nth-child(even){background:#fafbfe}
  .fg-field__label{font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#5b6475}
  .fg-field__value{font-size:14px;font-weight:600;color:var(--ink);overflow-wrap:anywhere;min-height:21px}

  .fg-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:8px;min-height:220px;padding:20px}
  .fg-empty__icon{width:48px;height:48px;border-radius:50%;background:#e8eefb;color:var(--blue-700);display:flex;align-items:center;justify-content:center;font-size:22px}
  .fg-empty__title{font-size:17px;font-weight:600;color:var(--ink)}
  .fg-empty__text{font-size:14px;max-width:340px;color:var(--ink)}

  .fg-contactBox{display:grid;grid-template-columns:1fr 1fr;gap:14px 16px;background:#fdf6e4;border:1px solid #f3dfae;border-radius:14px;padding:18px}
  .fg-contactBox .form-select{background:#fff}
  @media (max-width:767px){.fg-contactBox{grid-template-columns:1fr}}

  .fg-sections{display:flex;flex-direction:column;gap:24px}
  .fg-box{border:1px solid #e3e9f5;border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:14px;background:#ececec}
  .fg-box__head{display:flex;align-items:center;gap:10px}
  .fg-box__n{width:28px;height:28px;border-radius:8px;background:var(--blue-700);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700}
  .fg-box__head h3{margin:0;font-size:16px;font-weight:600;color:var(--ink)}
  .fg-box__grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 16px}
  @media (max-width:767px){.fg-box__grid{grid-template-columns:1fr}}
  .fg-box__grid .fg-span2{grid-column:1 / -1}
  .fg-box .form-control,.fg-box .form-select{background:#fff}

  .fg-actions{display:flex;gap:10px;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:20px}
  .fg-actions .btn-primary{height:46px;padding:0 26px}
</style>

<div class="fg-grid">

  <!-- ========== 1 · CLIENTE ========== -->
  <section class="fg-card fg-card--gold">
    <form id="formBuscar" class="fg-search d-flex flex-column gap-3" autocomplete="off">
      <label class="fg-label" for="b_telf">Número de teléfono</label>
      <div class="fg-search-row">
        <input type="text" class="form-control" placeholder="04242848747" aria-describedby="b_telf"
               name="b_telf" id="b_telf" maxlength="11" inputmode="numeric" autofocus />
        <input type="hidden" name="idUser" id="idUser" value="<?php echo $_SESSION['servicio_id']; ?>" />
        <button type="button" class="btn btn-primary fg-btn px-4" id="btn-buscar">Buscar</button>
        <button type="button" class="fg-btn-sec fg-reset">Nueva búsqueda</button>
      </div>
      <div id="searchError" class="fg-error" style="display:none"></div>
    </form>

    <div class="fg-headrow">
      <h2 class="fg-h2">1 · Cliente</h2>
    </div>

    <div class="fg-profile" id="fgProfile">
      <div class="fg-profile__avatar" id="initials">?</div>
      <div class="min-w-0">
        <div class="fg-profile__name" id="profileName">Sin cliente</div>
        <div class="fg-profile__sub" id="profileSub">Busca un número de teléfono</div>
      </div>
    </div>

    <div class="fg-fieldList">
      <!-- <div class="fg-field"><span class="fg-field__label">Nombre del cliente</span><span class="fg-field__value" id="name">—</span></div> -->
      <div class="fg-field"><span class="fg-field__label">Cédula</span><span class="fg-field__value" id="dni">—</span></div>
      <div class="fg-field"><span class="fg-field__label">Fecha de nacimiento</span><span class="fg-field__value" id="phone1">—</span></div>
      <div class="fg-field"><span class="fg-field__label">Genero</span><span class="fg-field__value" id="phone2">—</span></div>
      <!-- <div class="fg-field"><span class="fg-field__label">Teléfono</span><span class="fg-field__value" id="phone3">—</span></div> -->
      <div class="fg-field"><span class="fg-field__label">Ubicación</span><span class="fg-field__value" id="birthday">—</span></div>
      <div class="fg-field"><span class="fg-field__label">Número de cuenta</span><span class="fg-field__value" id="account">—</span></div>
      <div class="fg-field"><span class="fg-field__label">Correo electrónico</span><span class="fg-field__value" id="email">—</span></div>
    </div>
  </section>

  <!-- ========== 2 · CONTACTO / GESTIÓN ========== -->
  <section class="fg-card fg-card--green">

    <div id="emptyState" class="fg-empty">
      <div class="fg-empty__icon">☎</div>
      <div class="fg-empty__title">Formulario de contacto</div>
      <div class="fg-empty__text">Busca al cliente por su número de teléfono para registrar el resultado de la llamada.</div>
    </div>

    <form name="form1" id="formGestion" enctype="multipart/form-data" method="POST"
          onsubmit="return validateForm(this);" action="?view=formulario&mode=registro" autocomplete="off"
          style="display:none">
      <input type="hidden" name="id_cliente" id="id_cliente">
      <input type="hidden" name="usuario" id="usuario" value="<?php echo $_SESSION['id']; ?>">
      <input type="hidden" name="servicio" id="servicio" value="<?php echo $_SESSION['servicio_id']; ?>">
      <input type="hidden" name="hora_actual" id="hora_actuall">

      <div class="fg-headrow mb-2"><h2 class="fg-h2">2 · Contacto</h2></div>

      <div class="fg-contactBox">
        <label class="fg-lbl">Tipo de contacto
          <select class="form-select" name="contacto" id="contacto">
            <option value="" selected disabled>Seleccione…</option>
            <option value="1">Efectivo</option>
            <option value="0">No efectivo</option>
          </select>
        </label>

        <label class="fg-lbl" id="d_efectivo">Resultado del contacto
          <select class="form-select" name="efectivo" id="efectivo">
            <option value="" selected disabled>Seleccione…</option>
            <?php foreach ($efectivo as $e) { ?>
              <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['descripcion']); ?></option>
            <?php } ?>
          </select>
        </label>

        <label class="fg-lbl" id="d_noefectivo">Motivo de NO contacto
          <select class="form-select" name="noefectivo" id="noefectivo">
            <option value="" selected disabled>Seleccione…</option>
            <?php foreach ($noefectivo as $ne) { ?>
              <option value="<?php echo $ne['id']; ?>"><?php echo htmlspecialchars($ne['descripcion']); ?></option>
            <?php } ?>
          </select>
        </label>

        <label class="fg-lbl" id="d_venta">Tipo de producto
          <select class="form-select" name="venta" id="venta">
            <option value="0" selected disabled>Seleccione…</option>
            <?php foreach ($producto as $p) { ?>
              <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['descripcion']); ?></option>
            <?php } ?>
          </select>
        </label>
      </div>

      <!-- Secciones de la venta -->
      <div id="formulario" class="fg-sections">

        <div class="fg-box">
          <div class="fg-box__head">
            <span class="fg-box__n">1</span><h3>Datos del cliente</h3>
          </div>
          <div class="fg-box__grid">
            <label class="fg-lbl">Nombre del cliente
              <input type="text" class="form-control" placeholder="Carolina" aria-describedby="nombre2"
                     name="nombre2" id="nombre2" oninput="onlyLetters(this);" minlength="3" maxlength="60" required>
            </label>
            <label class="fg-lbl">Apellido del cliente
              <input type="text" class="form-control" placeholder="Perez" aria-describedby="apellido2"
                     name="apellido2" id="apellido2" oninput="onlyLetters(this);" minlength="3" maxlength="60" required>
            </label>
            <label class="fg-lbl">Sexo
              <select class="form-select" name="genero" id="genero" required>
                <option value="">Seleccione…</option>
                <option value="F">FEMENINO</option>
                <option value="M">MASCULINO</option>
              </select>
            </label>
            <label class="fg-lbl">Fecha de nacimiento
              <input type="text" class="form-control" aria-describedby="fecha_nac" name="fecha_nac" id="fecha_nac"
                     placeholder="01-01-2024" oninput="formatDate(this);" minlength="10" maxlength="10" required />
            </label>
            <label class="fg-lbl">Nacionalidad
              <select class="form-select" name="nacionalidad" id="nacionalidad" required>
                <option value="">Seleccione…</option>
                <option value="1">VENEZOLANA</option>
                <option value="2">EXTRANJERA</option>
                <option value="3">JURIDICO</option>
              </select>
            </label>
            <label class="fg-lbl">Cédula del cliente
              <input type="text" class="form-control" placeholder="12456345" aria-describedby="cedula2"
                     name="cedula2" id="cedula2" maxlength="8" minlength="6" oninput="onlyNumbers(this)" required />
            </label>
            <label class="fg-lbl">Teléfono Habitación
              <input type="text" class="form-control telefono" placeholder="(0212)345.67.89" aria-describedby="telf_hab"
                     name="telf_hab" id="telf_hab" onchange="validaTelf(this);" maxlength="15" required />
            </label>
            <label class="fg-lbl">Teléfono Celular
              <input type="text" class="form-control telefono" placeholder="(0424)234.56.78" aria-describedby="telf_cel"
                     name="telf_cel" id="telf_cel" onchange="validaTelf(this);" maxlength="15" required />
            </label>
            <label class="fg-lbl fg-span2">Correo electrónico
              <input type="email" class="form-control" placeholder="usuario@dominio.com" aria-describedby="correo2"
                     name="correo2" id="correo2" oninput="upperCase(this);" onchange="validateEmail(this);" maxlength="60" required />
            </label>
          </div>
        </div>

        <div class="fg-box">
          <div class="fg-box__head">
            <span class="fg-box__n">2</span><h3>Dirección</h3>
          </div>
          <div class="fg-box__grid">
            <label class="fg-lbl">Estado
              <select class="form-select" name="estado" id="estado" required>
                <option value="">Seleccione…</option>
                <?php foreach ($estado as $es) { ?>
                  <option value="<?php echo $es['id']; ?>"><?php echo htmlspecialchars($es['estado']); ?></option>
                <?php } ?>
              </select>
            </label>
            <label class="fg-lbl">Ciudad
              <select class="form-select" name="ciudad" id="ciudad" required></select>
            </label>
            <label class="fg-lbl fg-span2">Municipio
              <select class="form-select" name="municipio" id="municipio" required></select>
            </label>
          </div>
        </div>

        <div class="fg-box">
          <div class="fg-box__head">
            <span class="fg-box__n">3</span><h3>Datos bancarios</h3>
          </div>
          <div class="fg-box__grid">
            <label class="fg-lbl">Tipo de cuenta
              <select class="form-select" name="tipo_cuenta" id="tipo_cuenta" required>
                <option value="">Seleccione…</option>
                <?php foreach ($cuentas as $cuenta) { ?>
                  <option value="<?php echo $cuenta['id']; ?>"><?php echo htmlspecialchars($cuenta['nombre']); ?></option>
                <?php } ?>
              </select>
            </label>
            <label class="fg-lbl">Número de cuenta
              <input type="text" class="form-control" placeholder="123456********7890" aria-describedby="pan"
                     name="pan" id="pan" oninput="onlyPanNumber(this);" maxlength="20" />
            </label>
            <label class="fg-lbl fg-span2">Observaciones
              <textarea class="form-control" rows="3" maxlength="250" name="observaciones" id="observaciones"
                        oninput="upperCase(this);"></textarea>
            </label>
          </div>
        </div>
      </div>

      <div class="fg-actions">
        <button type="button" class="fg-btn-sec fg-reset">Cancelar</button>
        <button type="submit" class="btn btn-primary fg-btn px-4">Guardar</button>
      </div>
    </form>
  </section>
</div>

<script src="<?php echo asset('public/js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo asset('public/js/formulario.js'); ?>"></script>
<script src="<?php echo asset('public/js/fieldsValidation.js'); ?>"></script>
