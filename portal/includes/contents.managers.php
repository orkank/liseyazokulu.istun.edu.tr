<div class="col-md-6">
  <div class="card">
    <div class="card-body p-0">
      <div class="p-20">

        <!-- modal content -->
        <div class="modal fade bs-example-modal-lg" id="menu_settings" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel"><?=$sl->languages('Menü Yönetimi');?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <h4><?=$sl->languages('Sayfaları sıralamak için sürükle bırak yönetimini kullanabilirsiniz');?></h4>
                        <p><?=$sl->languages('Aktif menülere içerikler bölümünden istediğiniz içeriği taşıyabilirsiniz.');?></p>

                        <div class="card-body p-b-0">
                          <div class="row">

                            <div class="col-md-5 m-r-40">
                              <div class="card-title"><?=$sl->languages('İçerikler');?></div>
                              <div class="myadmin-dd-empty dd" id="menu">
                                <ol class="dd-list">
                                  <?php
                                    echo $sl->tree_build($sl->tree(0),
                                    array(
                                      '<ol class="dd-list" data-label="%s">',
                                      '</ol>'
                                    ),
                                    array(
                                      '<li class="dd-item dd3-item" data-id="%1$s" data-sub="0">
                                        <div class="dd-handle dd3-handle"></div>
                                        <div class="dd3-content"> %3$s </div>',
                                      '</li>'),
                                      false
                                    );
                                  ?>
                                </ol>
                              </div>
                            </div>

                            <div class="col-md-5">
                              <div class="card-title"><?=$sl->languages('Geçerli Menü');?></div>
                              <div class="myadmin-dd-empty dd" id="menu_current">
                                <ol class="dd-list" style="min-height:200px;">

                                  <?php
                                  if($sl->getmenu()) {
                                    for($i=0;$i<sizeof($sl->menu);$i++) {
                                      $checked = ($sl->menu[$i]['menu']['sub'] == '1')?' checked':'';
                                      echo
                                      '<li class="dd-item dd3-item" data-sub="'.$sl->menu[$i]['menu']['sub'].'" data-id="'.$sl->menu[$i]['id'].'">
                                        <div class="dd-handle dd3-handle"></div>
                                        <div class="dd3-content"> <span>'.$sl->menu[$i]['name'].'</span>
                                          <button class="btn btn-danger waves-effect waves-light float-right btn-sm" type="button" data-menu-remove="'.$sl->menu[$i]['id'].'"><i class="fa fa-do-not-enter"></i></button>
                                          <div class="switch">
                                            <label>
                                              '.$sl->languages('Alt Menüler').'
                                              <input type="checkbox" data-menu-subs="'.$sl->menu[$i]['id'].'" name="data['.$sl->menu[$i]['id'].'][submenu]"'.$checked.' value="1"><span class="lever"></span>
                                            </label>
                                          </div>

                                        </div>
                                      </li>';
                                    }
                                  }
                                  ?>
                                </ol>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary waves-effect text-left" data-menu="save"> <i class="mdi mdi-content-save-all"></i> <?=$sl->languages('Kaydet');?></button>
                      <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal"><?=$sl->languages('Kapat');?></button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <a href="#" data-target="#menu_settings" data-toggle="modal" class="btn btn-outline-primary waves-effect waves-light"><span class="btn-label"><i class="fas fa-bars"></i></span> <?=$sl->languages('Menü Ayarları');?></a>
        <a href="includes/filemanager/dialog.php?type=0" data-filemanager data-type="iframe"
          class="btn btn-outline-primary waves-effect waves-light"><span class="btn-label"><i class="fas fa-file-search"></i></span> <?=$sl->languages('Dosya Yöneticisi');?></a>

      </div>
    </div>
  </div>
</div>
