<form method="post" action="guestbook.php" id="quickform">
  <input type="hidden" name="action" value="do">

  <?php if (isset($sl->get["fid"])) { ?>
    <input type="hidden" name="fid" value="<?php echo $sl->get["fid"]; ?>">
  <?php } ?>

  <?php if (isset($sl->get["id"])) { ?>
    <input type="hidden" name="type_action" value="update">
    <input type="hidden" name="id" value="<?php echo $sl->get["id"]; ?>">
  <?php } ?>
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-outline-info">
        <div class="card-header">
          <h4 class="m-b-0 text-white">
            <?php if (isset($sl->get["id"])) { ?>
              Kayıt Güncelle
            <?php } else { ?>
              Yeni Kayıt
            <?php } ?>
          </h4>
        </div>
        <div class="card-body">
          <?php
          if ($response["status"] == 1) {
            $msg = !empty($response["msg"])
              ? $response["msg"]
              : "" . $id . " id numarasıyla kayıt başarıyla oluşturuldu.";
            echo '<div class="alert alert-success">' . $msg . "</div>";
          }
          if ($response["status"] > 1) {
            echo '<div class="alert alert-warning">' .
              $response["msg"] .
              '<br>
                         <small class="text-muted"><strong>Hata Kodu:</strong>' .
              $response["status"] .
              "</small></div>";
          }
          ?>
          <!-- <div class="row form-horizontal"> -->
          <div class="col-12 d-none">
            <div class="form-group mb-3 row pb-3">
              <div class="col-12">
                <div class="alert alert-info d-inline-block">
                  <strong>Operatör İsmi:</strong>
                  <?php
                  echo $sl->user_session["variables"]["username"];

                  if (empty($sl->user_session["variables"]["username"])) {
                    echo "Operatör ismi yazılmadan işlem yapılamaz.";
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
          <div class="row m-0">
            <div class="<?php echo $column; ?>">

              <div class="row">
                <div class="col-12">
                  <div class="form-group mb-3 row pb-3">
                    <div class="col-12">
                      <label for="inputEmail3" class="col-12 text-end control-label col-form-label">Kayıt Türü <small
                          class="text-muted text-red">* Gerekli</small></label>
                    </div>
                    <?php
                    $sl->post["type"] = $sl->get["type"] ?? $sl->post["type"];
                    ?>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" <?php echo $sl->post["type"] == 1 ? " checked" : ""; ?> required
                          class="form-check-input" id="callcenter" value="1" name="type">
                        <label class="form-check-label" for="callcenter">Call Center</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" <?php echo $sl->post["type"] == 2 ? " checked" : ""; ?> required
                          class="form-check-input" id="kampusziyareti" value="2" name="type">
                        <label class="form-check-label" for="kampusziyareti">Ziyaret</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" <?php echo $sl->post["type"] == 3 ? " checked" : ""; ?> required
                          class="form-check-input" id="whatsapp" value="3" name="type">
                        <label class="form-check-label" for="whatsapp">WhatsApp</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" <?php echo $sl->post["type"] == 4 ? " checked" : ""; ?> required
                          class="form-check-input" id="webform" value="4" name="type">
                        <label class="form-check-label" for="webform">Web Form</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" <?php echo $sl->post["type"] == 5 ? " checked" : ""; ?> required
                          class="form-check-input" id="webform" value="5" name="type">
                        <label class="form-check-label" for="webform">Reklam Formları</label>
                      </div>
                    </div>
                    <div class="col-auto align-self-right">
                      <div class="bg-light p-2">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" <?php echo ($sl->post["negatif"] == 1) ? 'checked' : ''; ?>
                            name="negatif" type="checkbox" id="Negatif" value="1">
                          <label class="form-check-label" for="Negatif">Negatif</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" <?php echo ($sl->post["baddata"] == 1) ? 'checked' : ''; ?>
                            name="baddata" type="checkbox" id="baddata" value="1">
                          <label class="form-check-label" for="baddata">Bad Data</label>
                        </div>
                      </div>
                    </div>
                    <script>
                      var negatif = document.querySelector("#Negatif");
                      var baddata = document.querySelector("#baddata");

                      negatif.addEventListener('change', () => {
                        baddata.checked = false;
                      });

                      baddata.addEventListener('change', () => {
                        negatif.checked = false;
                      });
                    </script>

                  </div>
                </div>

                <div class="col-12 col-lg-4">
                  <label for="fullname">Adı / Soyadı <small class="text-muted text-red">* Gerekli</small></label>
                  <div class="form-group ">
                    <input type="text" required class="form-control"
                      value="<?php echo mb_strtoupper($sl->post["fullname"]); ?>" id="fullname" name="fullname">
                  </div>
                </div>
                <script>
                  document.getElementById('fullname').addEventListener('input', function () {
                    this.value = this.value.toUpperCase();
                  });
                </script>
                <div class="col-12 col-lg-4">
                  <label for="tel">Telefon</label>
                  <div class="form-group">
                    <input type="tel" class="form-control" required value="<?php echo $sl->post["phone"]; ?>"
                      minlength="10" maxlength="10" id="phone" name="phone">
                    <small class="text-muted">* Başında 0 olmadan 10 hane olarak giriniz</small>
                  </div>
                </div>

                <div class="col-4">
                  <div class="form-group ">
                    <label for="city">Hangi İlden Arıyor</label>
                    <select id="city" name="city" data-show-subtext="true" data-live-search="true" data-select-picker
                      class="form-control">
                      <option value="0"></option>
                      <?php
                      $data = $sl->db->QueryArray(
                        "SELECT * FROM `address_cities` WHERE `countryID` = 212"
                      );
                      for ($i = 0; $i < sizeof($data); $i++) {
                        $s = "";

                        if (!isset($sl->post["city"])) {
                          $s = $data[$i]["cityID"] == 40 ? " selected" : "";
                        } else {
                          $s =
                            $data[$i]["cityID"] == $sl->post["city"]
                            ? " selected"
                            : "";
                        }

                        echo '<option value="' .
                          $data[$i]["cityID"] .
                          '"' .
                          $s .
                          ">" .
                          $data[$i]["cityName"] .
                          "</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-4">
                  <div class="form-group ">
                    <label for="county">İlçe</label>
                    <select id="counties" name="county" data-show-subtext="true" data-live-search="true"
                      data-select-picker class="form-control">
                      <option value="0"></option>
                      <?php
                      $city = !isset($sl->post["city"])
                        ? 40
                        : $sl->post["city"];

                      $data = $sl->db->QueryArray(
                        "SELECT * FROM `address_counties` WHERE `cityID` = {$city}"
                      );
                      for ($i = 0; $i < sizeof($data); $i++) {
                        $s = "";
                        if (isset($sl->post["county"])) {
                          $s =
                            $data[$i]["countyID"] == $sl->post["county"]
                            ? " selected"
                            : "";
                        }

                        echo '<option value="' .
                          $data[$i]["countyID"] .
                          '"' .
                          $s .
                          ">" .
                          $data[$i]["countyName"] .
                          "</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-4">
                  <label for="hearus">Bizi Nereden Duydunuz</label>
                  <div class="form-group ">
                    <select id="hearus" name="hearus" data-show-subtext="true" data-live-search="true"
                      data-select-picker class="form-control">
                      <option value="0"></option>
                      <?php
                      $data = $sl->db->QueryArray(
                        "SELECT * FROM `guestbook_types` WHERE `type` = 1"
                      );
                      for ($i = 0; $i < sizeof($data); $i++) {
                        $s =
                          $data[$i]["id"] == $sl->post["hearus"]
                          ? " selected"
                          : "";
                        echo '<option value="' .
                          $data[$i]["id"] .
                          '"' .
                          $s .
                          ">" .
                          $data[$i]["name"] .
                          "</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-4">
                  <label for="hearus">Başvuru Talebi</label>
                  <div class="form-group ">
                    <select id="talep" name="talep" data-show-subtext="true" data-live-search="true" data-select-picker
                      class="form-control">
                      <option value="0"></option>
                      <?php
                      $data = $sl->db->QueryArray(
                        "SELECT * FROM `guestbook_types` WHERE `type` = 4"
                      );
                      for ($i = 0; $i < sizeof($data); $i++) {
                        $s =
                          $data[$i]["id"] == $sl->post["talep"]
                          ? " selected"
                          : "";
                        echo '<option value="' .
                          $data[$i]["id"] .
                          '"' .
                          $s .
                          "> " . $data[$i]["name"] .
                          "</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-4">
                  <label for="hearus">Aksiyon</label>
                  <div class="form-group ">
                    <select id="other" required name="other" data-show-subtext="true" data-live-search="true"
                      data-select-picker class="form-control">
                      <option selected value="0"></option>
                      <?php
                      $data = $aksiyonlar;
                      $keys = array_keys($aksiyonlar);
                      for ($i = 0; $i < sizeof($data); $i++) {
                        $s =
                          ($data[$keys[$i]][0] == $sl->post["other"])
                          ? " selected"
                          : "";

                        echo '<option value="' .
                          $data[$keys[$i]][0] .
                          '"' .
                          $s .
                          "> " . $data[$keys[$i]][1] .
                          "</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-4">
                  <label for="fullname">Varsa notunuz</label>
                  <div class="form-group ">
                    <textarea <?php if (empty($sl->post['updates']))
                      echo 'required'; ?> class="form-control" id="text"
                      name="text"><?php echo $sl->post["text"]; ?></textarea>
                  </div>
                  <small class="text-muted mb-2">* Güncelleme tarih ve saati otomatik olarak kayıt
                    edilecektir.</small>
                </div>

                <div class="col-4">
                  <label for="callback_time">Tekrar Arama Tarihi</label>
                  <div class="form-group ">
                    <input type="datetime-local" name="callback_time" id="callback_time"
                      value="<?php echo $sl->post['callback_time']; ?>" class="form-control">
                    <button type="button" onclick="javascript:document.querySelector('#callback_time').value = '';"
                      id="removeCallback" class="btn mt-1 btn-sm btn-primary">Tekrar Arama Tarihini Sil</button>
                  </div>
                </div>

              </div>
              <!-- //row -->

              <div class="form-actions mt-40">
                <button type="submit" data-submit="form" class="btn btn-success"> <i class="fa fa-check"></i>
                  Kaydet</button>
              </div>

</form>