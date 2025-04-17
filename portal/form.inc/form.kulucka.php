
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
                <span class="hidden-sm-up"><i class="mdi mdi-file-document-box"></i></span> <span class="hidden-xs-down"> <i class="fas fa-file-invoice"></i> Girişim Bilgileri </span></a> </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#tab_team" role="tab">
                <span class="hidden-sm-up"><i class="mdi mdi-delete"></i></span> <span class="hidden-xs-down"> <i class="fas fa-users-crown"></i> Ekip Bilgileri </span></a> </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#tab_files" role="tab">
                <span class="hidden-sm-up"><i class="mdi mdi-delete"></i></span> <span class="hidden-xs-down"> <i class="fas fa-folder-tree"></i> Dosyalar </span></a> </li>
          </ul>
          <!-- Tab panes -->
          <div class="tab-content">
            <div class="tab-pane active" id="tab_form" role="tabpanel">
              <div class="p-20 form-material mt-4">

                <?php if($form['json']['_incorporation_ready']) { ?>
                <input <?php echo ($form['json']['_incorporation_ready'] == 1)? 'checked':'';?> type="radio" id="<?php echo $options['values']['_incorporation_ready'][0]; ?>_1" name="<?php echo $options['values']['_incorporation_ready'][0]; ?>" value="1" class="filled-in" />
                <label for="<?php echo $options['values']['_incorporation_ready'][0]; ?>_1"><?php echo $options['values']['_incorporation_ready'][1][0]; ?></label>

                <input <?php echo ($form['json']['_incorporation_ready'] == 2)? 'checked':'';?> type="radio" id="<?php echo $options['values']['_incorporation_ready'][0]; ?>_0" name="<?php echo $options['values']['_incorporation_ready'][0]; ?>" value="2" class="filled-in" />
                <label for="<?php echo $options['values']['_incorporation_ready'][0]; ?>_0"><?php echo $options['values']['_incorporation_ready'][1][1]; ?></label>
                <?php } else { ?>
                  <input type="checkbox" id="incorporation_ready" class="filled-in" checked disabled />
                  <label for="incorporation_ready"><?php echo $options['values']['incorporation_ready'][1]; ?></label>
                  <hr>
                  <input <?php echo ($form['json']['readyfor'] == 1)? 'checked':'';?> type="radio" id="<?php echo $options['values']['readyfor'][0]; ?>_0" name="<?php echo $options['values']['readyfor'][0]; ?>" class="filled-in" />
                  <label for="<?php echo $options['values']['readyfor'][0]; ?>_0"><?php echo $options['values']['readyfor'][1][1]; ?></label>
                  <input <?php echo ($form['json']['readyfor'] == 2)? 'checked':'';?> type="radio" id="<?php echo $options['values']['readyfor'][0]; ?>_1" name="<?php echo $options['values']['readyfor'][0]; ?>" class="filled-in" />
                  <label for="<?php echo $options['values']['readyfor'][0]; ?>_1"><?php echo $options['values']['readyfor'][1][0]; ?></label>

                <?php } ?>

                <hr>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['title'][1]; ?> <?php echo ($options['values']['title'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <input type="text" class="form-control form-control-line" name="<?php echo $options['values']['title'][0]; ?>"
                  value="<?php echo $form['json'][$options['values']['title'][0]]; ?>"> </div>

                <label class="d-block subject"><?php echo $options['values']['type1'][1]; ?> <?php echo ($options['values']['type1'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <select class="col-10 nopadding selectpicker mb-3 mr-2 d-block" multiple name="<?php echo $options['values']['type1'][0]; ?>" data-style="btn-primary">
                  <?php
                  for($i=1;$i<sizeof($options['type1']);$i++){
                  ?>
                    <option data-tokens=""<?php echo in_array($i, $form['json']['type1']) !== FALSE?' selected':'';?> value="<?php echo $i; ?>"><?php echo $options['type1'][$i]; ?></option>
                  <?php } ?>
                </select>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_1'][1]; ?> <?php echo ($options['values']['input_1'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <input type="text" class="form-control form-control-line" name="<?php echo $options['values']['input_1'][0]; ?>"
                  value="<?php echo $form['json'][$options['values']['input_1'][0]]; ?>"> </div>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_2'][1]; ?> <?php echo ($options['values']['input_2'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_2'][0]; ?>"><?php echo $form['json'][$options['values']['input_2'][0]]; ?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_3'][1]; ?> <?php echo ($options['values']['input_3'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_3'][0]; ?>"><?php echo $form['json']['input_3']; ?></textarea>
                </div>

                <label class="d-block subject"><?php echo $options['values']['type2'][1]; ?> <?php echo ($options['values']['type2'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <select class="col-10 nopadding selectpicker mb-3 mr-2 d-block" name="<?php echo $options['values']['type2'][0]; ?>" data-style="btn-primary">
                  <?php
                  for($i=1;$i<sizeof($options['type2']);$i++) {
                  ?>
                    <option data-tokens=""<?php echo ($form['json']['type2'] == $options['type2'][$i][0])?' selected':'';?>
                      value="<?php echo $options['type2'][$i][0]; ?>"><?php echo $options['type2'][$i][1]; ?></option>
                  <?php } ?>
                </select>

                <label class="d-block subject"><?php echo $options['values']['input_4'][1]; ?> <?php echo ($options['values']['input_4'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <input type="radio" id="<?php echo $options['values']['input_4'][0]; ?>_0" name="<?php echo $options['values']['input_4'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_4'] == 1)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_4'][0]; ?>_0">Evet</label>
                <input type="radio" id="<?php echo $options['values']['input_4'][0]; ?>_1" name="<?php echo $options['values']['input_4'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_4'] == 2)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_4'][0]; ?>_1">Hayır</label>
                <?php
                if($form['json']['input_4'] == 1) {
                ?>
                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_5'][1]; ?> <?php echo ($options['values']['input_5'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_5'][0]; ?>"><?php echo $form['json']['input_5']; ?></textarea>
                </div>
                <?php } ?>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_6'][1]; ?> <?php echo ($options['values']['input_6'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_6'][0]; ?>"><?php echo $form['json'][$options['values']['input_6'][0]]; ?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_7'][1]; ?> <?php echo ($options['values']['input_7'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_7'][0]; ?>"><?php echo $form['json'][$options['values']['input_7'][0]]; ?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_8'][1]; ?> <?php echo ($options['values']['input_8'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_8'][0]; ?>"><?php echo $form['json'][$options['values']['input_8'][0]]; ?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_9'][1]; ?> <?php echo ($options['values']['input_9'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_9'][0]; ?>"><?php echo $form['json'][$options['values']['input_9'][0]]; ?></textarea>
                </div>

                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_10'][1]; ?> <?php echo ($options['values']['input_10'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_10'][0]; ?>"><?php echo $form['json'][$options['values']['input_10'][0]]; ?></textarea>
                </div>

                <label class="d-block subject"><?php echo $options['values']['input_11'][1]; ?> <?php echo ($options['values']['input_11'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <input type="radio" id="<?php echo $options['values']['input_11'][0]; ?>_0" name="<?php echo $options['values']['input_11'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_11'] == 1)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_11'][0]; ?>_0">Evet</label>
                <input type="radio" id="<?php echo $options['values']['input_11'][0]; ?>_1" name="<?php echo $options['values']['input_11'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_11'] == 2)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_11'][0]; ?>_1">Hayır</label>
                <?php
                if($form['json']['input_11'] == 1) {
                ?>
                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_12'][1]; ?> <?php echo ($options['values']['input_12'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_12'][0]; ?>"><?php echo $form['json']['input_12']; ?></textarea>
                </div>
                <?php } ?>

                <label class="d-block subject"><?php echo $options['values']['input_13'][1]; ?> <?php echo ($options['values']['input_13'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <input type="radio" id="<?php echo $options['values']['input_13'][0]; ?>_0" name="<?php echo $options['values']['input_13'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_13'] == 1)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_13'][0]; ?>_0">Evet</label>
                <input type="radio" id="<?php echo $options['values']['input_13'][0]; ?>_1" name="<?php echo $options['values']['input_13'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_13'] == 2)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_13'][0]; ?>_1">Hayır</label>
                <?php
                if($form['json']['input_13'] == 1) {
                ?>
                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_14'][1]; ?> <?php echo ($options['values']['input_14'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_14'][0]; ?>"><?php echo $form['json']['input_14']; ?></textarea>
                </div>
                <?php } ?>

                <label class="d-block subject"><?php echo $options['values']['input_15'][1]; ?> <?php echo ($options['values']['input_15'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <input type="radio" id="<?php echo $options['values']['input_15'][0]; ?>_0" name="<?php echo $options['values']['input_15'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_15'] == 1)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_15'][0]; ?>_0">Evet</label>
                <input type="radio" id="<?php echo $options['values']['input_15'][0]; ?>_1" name="<?php echo $options['values']['input_15'][0]; ?>" class="filled-in"<?php echo ($form['json']['input_15'] == 2)? 'checked':'';?> />
                <label for="<?php echo $options['values']['input_15'][0]; ?>_1">Hayır</label>
                <?php
                if($form['json']['input_15'] == 1) {
                ?>
                <div class="form-group">
                  <label class="subject"><?php echo $options['values']['input_16'][1]; ?> <?php echo ($options['values']['input_16'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                  <textarea class="form-control form-control-line" name="<?php echo $options['values']['input_16'][0]; ?>"><?php echo $form['json']['input_16']; ?></textarea>
                </div>
                <?php } ?>

                <div class="row">
                  <div class="col-12 col-lg-6">
                    <label class="d-block subject"><?php echo $options['values']['input_17'][1]; ?> <?php echo ($options['values']['input_17'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                    <select class="col-12 nopadding selectpicker mb-3 mr-2 d-block" name="<?php echo $options['values']['input_17'][0]; ?>" data-style="btn-primary">
                      <?php
                      for($i=1;$i<sizeof($options['type6']);$i++){
                      ?>
                        <option data-tokens=""<?php echo $options['type6'][$i][0] == $form['json']['input_17']?' selected':'';?> value="<?php echo $i; ?>"><?php echo $options['type6'][$i][1]; ?></option>
                      <?php } ?>
                    </select>
                  </div>

                  <div class="col-12 col-lg-6">
                    <label class="d-block subject"><?php echo $options['values']['input_18'][1]; ?> <?php echo ($options['values']['input_18'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                    <select class="col-12 nopadding selectpicker mb-3 mr-2 d-block" name="<?php echo $options['values']['input_18'][0]; ?>" data-style="btn-primary">
                      <?php
                      for($i=1;$i<sizeof($options['type3']);$i++){
                      ?>
                        <option data-tokens=""<?php echo $options['type3'][$i][0] == $form['json']['input_18']?' selected':'';?> value="<?php echo $i; ?>"><?php echo $options['type3'][$i][1]; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <label class="d-block subject"><?php echo $options['values']['input_19'][1]; ?> <?php echo ($options['values']['input_19'][3] == 1?'<span class="help"> * </span>':''); ?></label>
                <select class="col-12 nopadding selectpicker mb-3 mr-2 d-block" name="<?php echo $options['values']['input_19'][0]; ?>" data-style="btn-primary">
                  <?php
                  for($i=1;$i<sizeof($options['type4']);$i++){
                  ?>
                    <option data-tokens=""<?php echo $options['type4'][$i][0] == $form['json']['input_19']?' selected':'';?> value="<?php echo $i; ?>"><?php echo $options['type4'][$i][1]; ?></option>
                  <?php } ?>
                </select>

              </div>
            </div>

            <div class="tab-pane  p-20" id="tab_team" role="tabpanel">
              <div class="p-20 form-material mt-4">
                <div class="row">
                <?php
                $keys = array_keys($form['json']['team']);

                for($i=0;$i<sizeof($form['json']['team']);$i++) {
                ?>
                <div style="width:100%;margin:10px;max-width:350px;">
                  <div class="card">
                    <div class="card-img-top" style="height:300px;background-image:url('<?php echo $form['json']['team'][$keys[$i]][11];?>');background-position:center;background-size:cover;"></div>
                    <div class="card-body">
                      <h4 class="card-title"><?php echo $form['json']['team'][$keys[$i]][0];?></h4>
                      <p class="card-text">
                        <span class="d-block text-muted">T.C. <?php echo $form['json']['team'][$keys[$i]][1];?></span>
                        <span class="d-block text-muted">Görevi: <?php echo $form['json']['team'][$keys[$i]][2];?></span>
                        <span class="d-block text-muted">Telefon: <?php echo $form['json']['team'][$keys[$i]][3];?></span>
                        <span class="d-block text-muted">Eğitim: <?php echo $options['type5'][$form['json']['team'][$keys[$i]][4]][1];?></span>
                        <span class="d-block text-muted">Üniversite: <?php echo $form['json']['team'][$keys[$i]][6];?></span>
                        <span class="d-block text-muted">Bölüm: <?php echo $form['json']['team'][$keys[$i]][5];?></span>
                        <span class="d-block text-muted">Şehir: <?php echo $options['cities'][$form['json']['team'][$keys[$i]][7]]['cityName'];?></span>
                        <span class="d-block text-muted">E-posta: <?php echo $form['json']['team'][$keys[$i]][9];?></span>
                        <span class="d-block text-muted">Cinsiyet: <?php echo $options['sex'][$form['json']['team'][$keys[$i]][10]][1];?></span>
                      </p>
                      <!--
                      <a href="#" class="btn btn-primary">Detaylar</a>
                      -->
                    </div>
                  </div>
                </div>
                <?php } ?>
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
