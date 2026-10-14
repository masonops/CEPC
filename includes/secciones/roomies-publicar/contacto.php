<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/contacto.php — Formas extra de
 * contacto (opcionales). Por defecto los mensajes llegan al correo.
 */

declare(strict_types=1);
?>
<fieldset class="parte" id="parte-contacto" data-parte="contacto">
  <legend><span class="parte__numero">8</span>Contacto</legend>
  <p class="parte__ayuda">Los interesados te escriben desde el aviso y te llega a tu correo. Si quieres, suma otras formas.</p>
  <div class="rejilla rejilla--2">
    <label class="campo">
      <span>WhatsApp <span class="campo__ayuda">· opcional</span></span>
      <span class="campo-con-prefijo"><span>+56</span><input type="tel" name="whatsapp" maxlength="12" inputmode="tel" autocomplete="tel-national" placeholder="9 1234 5678" value="<?= $valor('whatsapp') ?>"></span>
    </label>
    <label class="campo">
      <span>Instagram <span class="campo__ayuda">· opcional</span></span>
      <span class="campo-con-prefijo"><span>@</span><input type="text" name="instagram" maxlength="31" autocapitalize="off" spellcheck="false" value="<?= $valor('instagram') ?>"></span>
    </label>
  </div>
  <label class="interruptor"><input type="checkbox" name="mostrar_whatsapp" value="1"<?= $marcado('mostrar_whatsapp', '1') ?>><span>Mostrar un botón de WhatsApp en el aviso</span></label>
</fieldset>
