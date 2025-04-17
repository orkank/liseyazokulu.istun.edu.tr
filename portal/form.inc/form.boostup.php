
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body p-b-0">
            <h4 class="card-title">
              <?php echo $portal->module['name'];?>
            </h4>
            <h6 class="card-subtitle">
              <?php echo $portal->module['desc'];?>
            </h6>
          </div>
          <!-- Nav tabs -->
          <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#tab_form" role="tab">
                <span class="hidden-sm-up"><i class="mdi mdi-file-document-box"></i></span> <span class="hidden-xs-down"> <i class="fas fa-file-invoice"></i> Başvuru Bilgileri </span></a> </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#tab_files" role="tab">
                <span class="hidden-sm-up"><i class="mdi mdi-delete"></i></span> <span class="hidden-xs-down"> <i class="fas fa-folder-tree"></i> Dosyalar </span></a> </li>
          </ul>
          <!-- Tab panes -->
          <div class="tab-content">
            <div class="tab-pane active" id="tab_form" role="tabpanel">
              <div class="p-20 form-material mt-4">

                <input checked type="radio" id="legal" name="legal" value="1" class="filled-in" checked />
                <label for="legal"> Programda belirtilen başvuru kriterlere uygunum.</label>

                <div class="form-group">
                  <label class="subject">Şirketin Tam Adı</label>
                  <input type="text" class="form-control form-control-line" name="company" value="<?php echo $form['json']['company'];?>">
                </div>

                <div class="form-group">
                  <label class="subject">E-mail Adresi</label>
                  <input type="text" class="form-control form-control-line" name="email" value="<?php echo $form['json']['email'];?>">
                </div>

                <div class="form-group">
                  <label class="subject">Telefon Numarası</label>
                  <input type="text" class="form-control form-control-line" name="mobile" value="<?php echo $form['json']['mobile'];?>">
                </div>

                <div class="form-group">
                  <label class="subject">Şirketin Websitesi</label>
                  <input type="text" class="form-control form-control-line" name="web" value="<?php echo $form['json']['web'];?>">
                </div>

                <div class="form-group">
                  <label class="subject">Şirket Kurucu/Kurucuların Adı Soyadı</label>
                  <input type="text" class="form-control form-control-line" name="fullname" value="<?php echo $form['json']['fullname'];?>">
                </div>

                <div class="form-group">
                  <label class="subject">Firmanızın faaliyet alan/alanları ve firmanız hakkında özet bilgi yazınız </label>
                  <textarea class="form-control form-control-line" name="faaliyet_alanlari"><?php echo $form['json']['faaliyet_alanlari'];?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject">Gelir elde ediliyor mu? Evet ise tutarı nedir? </label>
                  <textarea class="form-control form-control-line" name="gelir_elde_ediliyormu"><?php echo $form['json']['gelir_elde_ediliyormu'];?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject">Geliriniz yalnızca ulusal pazardan mı yoksa uluslarası pazardan mı elde ediliyor? </label>
                  <textarea class="form-control form-control-line" name="gelir_ulusal"><?php echo $form['json']['gelir_ulusal'];?></textarea>
                </div>


              </div>
            </div>

            <div class="tab-pane  p-20" id="tab_files" role="tabpanel">
              <div class="p-20 form-material mt-4">
                <div class="row">
                <?php
                if(!empty($form['json']['files'])) {

                  for($i=0;$i<sizeof($form['json']['files']);$i++) {
                ?>
                <div class="col-12" style="max-width:250px;">
                  <div class="card">
                    <div class="card-body">
                      <p class="card-text">
                        <?php echo basename($form['json']['files'][$i]); ?>
                      </p>
                      <a href="<?php echo $form['json']['files'][$i];?>" target="_blank" class="btn btn-primary">Görüntüle</a>
                    </div>
                  </div>
                </div>
                <?php
                  }
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- End PAge Content -->
    <!-- ============================================================== -->
  </div>
