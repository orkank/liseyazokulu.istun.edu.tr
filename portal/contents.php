<?php
require('build.php');

use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;

$portal->self(
  array(
    'uniq' => 'ff89f59e56dddc2b2a8a28a72fb3f420',
    'hash' => $sl->token('sl.contents')
  )
);

if(isset($sl->post['action']) AND $sl->post['action'] == 'reorder') {
  if(isset($sl->post['rows'])) {
    for($i=0;$i<sizeof($sl->post['rows']);$i++) {
      $sl->db->Query("UPDATE contents SET `sorder` = {$i} WHERE id = {$sl->post['rows'][$i]}");
    }
  }
  exit;
}
/*

$str = 'Süt Ürünleri
Sıvı Gıdalar
Kuru Gıdalar
Çikolata/Şekerleme
Fırın Ürünleri
Pet Food
Tarımsal Ürünler
Kimyasal Ürünler
Temizlik ve Bakım Ürünleri
Dondurulmuş Gıdalar
Meyve, Sebze
Hazır Yemek';

$str = explode("\n", $str);
for($i=0;$i<sizeof($str);$i++) {
  $slug = $portal->slug($str[$i]);
  $sql = "INSERT INTO `contents` (`id`, `status`, `parent`, `lang`, `breadcrumb`, `sl_module`, `sl_template`, `orders`, `sorder`, `default`, `children`, `modified`, `published`, `editor_note`, `draft`, `expiry`, `link_access`, `name{tr}`, `name{en}`, `content{tr}`, `content{en}`, `keywords{tr}`, `keywords{en}`, `title{tr}`, `title{en}`, `desc{tr}`, `desc{en}`, `link{tr}`, `link{en}`, `slug{tr}`, `slug{en}`) VALUES
  (NULL, 1, 2327, NULL, NULL, 3, '', NULL, 0, 0, 0, 1651567596, NULL, '', NULL, NULL, NULL, '{$str[$i]}', '', '', '', NULL, NULL, '', '', '', '', '', '', '{$slug}', '');";
  $sl->db->Query($sql);
}

exit;
*/

if(isset($sl->post['action']) AND $sl->post['action'] == 'regenerate-menu') {
  $menu = $sl->getmenu();
  $menu_current = json_encode($sl->get_menu_static());
  $restore = false;

  if($menu) {
    if(!file_put_contents(PB . DS . 'menu.json', json_encode($menu))) {
      $restore = true;
    }
  } else {
    $restore = true;
  }

  if($restore) {
    file_put_contents(PB . DS . 'menu.json', $menu_current);
    echo json_encode(array('status' => 'fail'));
  } else {
    echo json_encode(array('status' => 'ok'));
  }
  exit;
}


if(isset($sl->post['action']) AND $sl->post['action'] == 'remove') {
  if(is_numeric($sl->post['id'])) {
    $time = time() + 60 * 60 * 24 * 30;

    $c = $sl->db->Query("DELETE FROM `".$portal->self()['table']."` WHERE `id`='".$sl->post['id']."'");
    $sl->db->Query("DELETE FROM `content_nodes` WHERE `cid`='".$sl->post['id']."'");


    /*
    $c = $sl->db->Query("UPDATE `".$portal->self()['table']."` SET
    `status`='0',
    `expiry` = '".$time."'
    WHERE `id`='".$sl->post['id']."'");
    */
  }

  if($c) {
    echo json_encode(array('status' => 'ok'));
  } else {
    echo json_encode(array('status' => 'fail'));
  }

  exit;
}

if(isset($sl->get['action']) AND $sl->get['action'] == 'rpc') {
  //$sl->checkToken('sl.contents');

  $config = [ 'host'     => $sl->settings['database']['host'],
              'port'     => '3306',
              'username' => $sl->settings['database']['user'],
              'password' => $sl->settings['database']['password'],
              'database' => $sl->settings['database']['db'] ];

  $dt = new Datatables( new MySQL($config) );

  $where = '';
  $query = array();
  $order = '';
  $extracolumn = '';

  /*
  $status = explode(',', $sl->get['status']);
  $_status = array();

  for($i=0;$i<sizeof($status);$i++)
    $_status[] = "`status` = {$status[$i]}";

  $query[] = "(".implode(' OR ',$_status).")";
  */

  if(!empty($sl->get['sl_module'])) {
    $sl_module = explode(',', $sl->get['sl_module']);

  }
  $_sl_modules = array();

  if(isset($sl_module) AND is_array($sl_module))
  for($i=0;$i<sizeof($sl_module);$i++)
    $query[] = "sl_module={$sl_module[$i]}";

  $parent = explode(',', $sl->get['parent']);

  $_parent = array();

  for($i=0;$i<sizeof($parent);$i++) {
    if($parent[$i] > 0)
      $_parent[] = "`parent`={$parent[$i]}";
  }

  if(empty($sl->post['search']['value']) AND !empty($_parent))
    $query[] = "(".implode(' OR ',$_parent).")";

  $where = implode(" AND ", $query);

  if(isset($sl->get['extracolumn']) AND !empty($sl->get['extracolumn'])) {
    if(strpos($sl->get['extracolumn'], ',') > 0) {
      $extracolumn = array_map(function($e){
        return "`{$e}`";
      }, $sl->get['extracolumn']);
      $extracolumn = implode(',', $extracolumn);
    } else {
      $extracolumn = "`{$sl->get['extracolumn']}`,";
    }
  }

  $query = "SELECT
    `id`,
  	{$extracolumn}
    `name{{$sl->languages('prefix')}}` AS `name`,
    `modified`,
    `sl_module` AS `module`,
    `status`,
    `parent`
  FROM `contents` WHERE {$where} {$order}";

  $dt->query($query);

  switch($sl->get['sl_module']) {
    case 21:
      $dt->edit('name', function($data) {
        global $sl;

        $return = array();
        $path = $sl->db->QuerySingleValue("SELECT `value` FROM `content_nodes` WHERE `cid` = {$data['id']} AND `key` = 'desktop{{$sl->languages('prefix')}}'");
        $path = unserialize($path)[0];
        $return[] = "<a href=\"{$path}\" data-fancybox><span class=\"d-block mb-2 badge\">{$data['name']}</span><br><img src=\"{$path}\" style=\"max-width:120px;\"></a> ";
        #print_r(unserialize($path));

        return implode("\n", $return);
      });
    break;
    default:
      $dt->edit('name', function($data) {
          return '<a href="content.sl.php?id='.$data['id'].'" class="" title="Düzenle">'.$data['name'].'</a><br><a href="content.sl.php?config=3&parent='.$data['id'].'"><small class="text-muted">Alt Sayfa Ekle</small></a>';
      });
  }

  $dt->hide('status');

  $dt->edit('id', function($data){
      return '<label> <input data-status="'.$data['status'].'" type="checkbox" name="rows[]" value="'.$data['id'].'"> '.$data['id'].'</label>';
  });

  $dt->edit('module', function($data){
    global $portal, $sl;

    return isset($portal->module['modules'][$data['module']])?$portal->module['modules'][$data['module']]['desc']:'<small classs="text-muted">'.$sl->languages('Modül Seçili Değil').'</small>';
  });

  $dt->edit('modified', function($data){
    return date('Y-m-d H:i:s',$data['modified']);
  });

  $dt->add('subpages', function($data) {
    global $sl, $portal;

    $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `{$portal->self()['table']}` WHERE `parent`='{$data['id']}'");
    $parent = $sl->db->QuerySingleRowArray("SELECT `name{{$sl->languages('prefix')}}` AS name,`parent` FROM `{$portal->self()['table']}` WHERE `id`='{$data['parent']}'");

    $_parent = '';

    if(isset($parent['parent']))
      $_parent = $sl->db->QuerySingleRowArray("SELECT `name{{$sl->languages('prefix')}}` AS name,`parent` FROM `{$portal->self()['table']}` WHERE `id`='{$parent['parent']}'");

    $return = array();

    $return[] = ($count > 0)?
    '<button href="#" data-target="#subpages" data-toggle="modal" data-id="'.$data['id'].'"
      class="btn btn-outline-primary waves-effect waves-light btn-sm">
        <span class="btn-label"><i class="fas fa-align-right"></i></span> '.$sl->languages('Alt Sayfalar').' ('.$count.')</button>
        ':
    '';

    if(isset($_parent['name']) OR isset($parent['name']))
      $return[] = "<small class=\"text-muted d-block mt-2\">{$_parent['name']} > {$parent['name']}</small>";

    return implode("\n", $return);
  });

  $dt->add('actions', function($data) {
    global $sl, $portal;

    $return = array();
    $return[] = '
    <div class="btn-group" role="group" aria-label="'.$sl->languages('İşlemler').'">
      <a href="content.sl.php?id='.$data['id'].'" class="btn btn-info" title="'.$sl->languages('Düzenle').'"><i class="fas fa-edit"></i></a>
      <a href="#" class="btn btn-info" data-trash="'.$data['id'].'" title="'.$sl->languages('Sil').'"><i class="fas fa-trash-alt"></i></a>
      <!--
      <a href="#" class="btn btn-info" data-copy="'.$data['id'].'" title="'.$sl->languages('Kopyala').'"><i class="fas fa-copy"></i></a>
      -->
    </div>
    ';
    $return[] = ($data['status'] != 1) ? '<span class="badge badge-danger d-block mt-1">Kapalı</span>' : '<span class="badge badge-success d-block mt-1">Açık</span>';

    return implode("\n", $return);
  });

  echo $dt->generate();
  exit;
  /*
  // DB table to use
  $table = $portal->self()['table'];

  // Table's primary key
  $primaryKey = 'id';

  // Array of database columns which should be read and sent back to DataTables.
  // The `db` parameter represents the column name in the database, while the `dt`
  // parameter represents the DataTables column identifier. In this case simple
  // indexes
  $columns = array(
    array( 'db' => 'id', 'dt' => 0, 'formatter' => function($d) {
      return '<span data-status=""></span>';
    }),
    array( 'db' => 'name{'.$sl->languages('prefix').'}', 'dt' => 1 ),

      array( 'db' => 'id', 'dt' => 2, 'formatter' => function($d) {
        global $sl, $portal;

        $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `{$portal->self()['table']}` WHERE `parent`='{$d}'");
        $button = ($count > 0)?
        '<a href="#" data-subs="'.$d.'" class="btn btn-outline-primary waves-effect waves-light btn-sm"><span class="btn-label"><i class="fas fa-align-right"></i></span> '.$sl->languages('Alt Sayfalar').' ('.$count.')</button>':
        '';

        return $button;
      }),
      array( 'db' => 'modified', 'dt' => 3, 'formatter' => function($d) {
        return date('Y-m-d H:i:s',$d);
      }),
      array(
          'db'        => 'sl_module',
          'dt'        => 4,
          'formatter' => function($d, $row) {
            global $portal;
            return $portal->module['modules'][$d]['desc'];
          }
      ),
      array(
          'db'        => 'id',
          'dt'        => 5,
          'formatter' => function( $d, $row ) {
            global $sl;

            return '
            <div class="btn-group" role="group" aria-label="'.$sl->languages('İşlemler').'">
              <a href="content.sl.php?id='.$d.'" class="btn btn-info" title="'.$sl->languages('Düzenle').'"><i class="fas fa-edit"></i></a>
              <a href="#" class="btn btn-info" data-trash="'.$d.'" title="'.$sl->languages('Sil').'"><i class="fas fa-trash-alt"></i></a>
              <a href="#" class="btn btn-info" data-copy="'.$d.'" title="'.$sl->languages('Kopyala').'"><i class="fas fa-copy"></i></a>
            </div>
            ';
          }
      )
  );

  // SQL server connection information

  $sql = array(
      'user' => $sl->settings['database']['user'],
      'pass' => $sl->settings['database']['password'],
      'db'   => $sl->settings['database']['db'],
      'host' => $sl->settings['database']['host']
  );

  $where = '';
  $query = array();

  $status = explode(',', $sl->get['status']);
  $_status = array();

  for($i=0;$i<sizeof($status);$i++)
    $_status[] = "`status`='{$status[$i]}'";

  $query[] = "(".implode(' OR ',$_status).")";

  $query[] = "`parent`='{$sl->get['parent']}'";

  $where = implode(" AND ", $query);

  echo json_encode(
      SSP::complex( $_GET, $sql, $table, $primaryKey, $columns, $where )
  );

  exit;
  */
}

$portal->file('header');
?>
<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
  <!-- ============================================================== -->
  <!-- Container fluid  -->
  <!-- ============================================================== -->
  <div class="container-fluid">
    <?php include('breadcrumb.php'); ?>
    <!-- ============================================================== -->
    <!-- Start Page Content -->
    <!-- ============================================================== -->

    <div class="row">

      <div class="col-md-6">
        <div class="card">
          <div class="card-body p-0">
            <div class="p-20">
              <?php
              $keys = array_keys($portal->self()['modules']);
              for($i=0;$i<sizeof($portal->self()['modules']);$i++) {
                if(
                  $portal->self()['modules'][$keys[$i]]['hidden'] OR
                  $portal->self()['modules'][$keys[$i]]['readonly']
                  )
                  continue;
              ?>
              <a href="content.sl.php?config=<?php echo $portal->self()['modules'][$keys[$i]]['id'];?>"
                class="btn btn-outline-primary waves-effect waves-light">
                <?php echo (!empty($portal->self()['modules'][$keys[$i]]['icon'])?'<span class="btn-label"><i class="'.$portal->self()['modules'][$keys[$i]]['icon'].'"></i></span>':'');?>
                <?php echo $portal->self()['modules'][$keys[$i]]['name'];?> <?php echo $sl->languages('Ekle');?></a>
              <?php } ?>

            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-body p-0">
            <div class="p-20">

              <!-- modal content -->
              <div class="modal fade bs-example-modal-lg" id="menu_settings" tabindex="-1" role="dialog" aria-labelledby="menu_settings" aria-hidden="true" style="display: none;">
                  <div class="modal-dialog modal-xl">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h4 class="modal-title"><?php echo $sl->languages('Menü Yönetimi');?></h4>
                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          </div>
                          <div class="modal-body">
                              <h4><?php echo $sl->languages('Sayfaları sıralamak için sürükle bırak yönetimini kullanabilirsiniz');?></h4>
                              <p><?php echo $sl->languages('Aktif menülere içerikler bölümünden istediğiniz içeriği taşıyabilirsiniz.');?></p>

                              <div class="card-body p-b-0">
                                <div class="row">

                                  <div class="col-md-5 m-r-40">
                                    <div class="card-title"><?php echo $sl->languages('İçerikler');?></div>
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
                                    <div class="card-title"><?php echo $sl->languages('Geçerli Menü');?></div>
                                    <div class="myadmin-dd-empty dd" id="menu_current">
                                      <ol class="dd-list" style="min-height:200px;">

                                        <?php
                                        if($sl->getmenu()) {
                                          for($i=0;$i<sizeof($sl->menu);$i++) {
                                            if(empty($sl->menu[$i]['name']))
                                              continue;

                                            $checked = (isset($sl->menu[$i]['menu']['sub']) AND $sl->menu[$i]['menu']['sub'] == '1')?' checked':'';
                                            $sl->menu[$i]['menu']['sub']  = $sl->menu[$i]['menu']['sub'] ?? '';
                                            $sl->menu[$i]['id'] = $sl->menu[$i]['id'] ?? '';
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
                            <button type="button" class="btn btn-primary waves-effect text-left" data-menu="save"> <i class="mdi mdi-content-save-all"></i> <?php echo $sl->languages('Kaydet');?></button>
                            <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal"><?php echo $sl->languages('Kapat');?></button>
                          </div>
                      </div>
                      <!-- /.modal-content -->
                  </div>
                  <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <a href="#" data-target="#menu_settings" data-toggle="modal" class="btn btn-outline-primary waves-effect waves-light"><span class="btn-label"><i class="fas fa-bars"></i></span> <?php echo $sl->languages('Menü Ayarları');?></a>
              <a href="includes/filemanager/dialog.php?type=0" data-filemanager data-type="iframe"
                class="btn btn-outline-primary waves-effect waves-light"><span class="btn-label"><i class="fas fa-file-search"></i></span> <?php echo $sl->languages('Dosya Yöneticisi');?></a>
              <a href="#" data-regenerate-menu class="btn btn-outline-primary waves-effect waves-light"><span class="btn-label"><i class="fas fa-bars"></i></span> <?php echo $sl->languages('Menüyü Tekrar Oluştur');?></a>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- modal content -->
    <div class="modal fade bs-example-modal-lg" id="subpages" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo $sl->languages('Alt Sayfalar');?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <h4 id="subpages_title"></h4>
                    <p><?php echo $sl->languages('Alt sayfaları bu alandan kontrol edebilir düzenleme yapabilirsiniz.');?></p>

                    <div class="card-body p-b-0">
                      <table id="subpages_table" class="datatable display nowrap table table-hover table-striped table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <th>ID Numarası</th>
                            <th>Başlık</th>
                            <th>Bağlı Sayfalar</th>
                            <th>Son Güncelleme</th>
                            <th>Modül</th>
                            <th>İşlemler</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                          <tr>
                            <th>ID Numarası</th>
                            <th>Başlık</th>
                            <th>Bağlı Sayfalar</th>
                            <th>Son Güncelleme</th>
                            <th>Modül</th>
                            <th>İşlemler</th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal"><?php echo $sl->languages('Kapat');?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

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
            <!--
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#contents" role="tab">
                <span class="hidden-sm-up"><i class="mdi mdi-file-document-box"></i></span> <span class="hidden-xs-down"> <i class="mdi mdi-file-document-box"></i> İçerikler </span></a> </li>
              -->
            <?php
            $setactive = true;
            $modules = $portal->self()['modules'];
            /*
            $modules[] = [
              'id' => 0,
              'name' => 'Geri Dönüşüm',
              'desc' => 'Geri dönüşüm kutusu',
              'config' => '',
              'type' => 0,
              'parent' => 0,
              'default' => 0,
              'link' => '',
              'icon' => 'mdi mdi-delete',
              'hidden' => 0,
              'group' => 0,
              'config_id' => 0,
              'sortable' => 0,
              'readonly' => 1
            ];
            */
            $keys = array_keys($modules);

            for($i=0;$i<sizeof($modules);$i++) {
              if($modules[$keys[$i]]['hidden'])
                continue;
            ?>
            <li class="nav-item">
              <a class="nav-link<?php if($setactive) {echo ' active show'; $setactive=false;} ?>" data-toggle="tab" href="#contents_<?php echo $modules[$keys[$i]]['id'];?>" role="tab">
                <span class="hidden-sm-up"><i class="<?php echo $modules[$keys[$i]]['icon']; ?>"></i></span> <span class="hidden-xs-down">
                  <i class="<?php echo $modules[$keys[$i]]['icon']; ?>"></i> <?php echo $modules[$keys[$i]]['desc']; ?> </span></a> </li>
            <?php } ?>
          </ul>
          <!-- Tab panes -->
          <div class="tab-content">
            <?php
            $keys = array_keys($modules);
            $setactive = true;

            for($i=0;$i<sizeof($modules);$i++) {
              if($modules[$keys[$i]]['hidden'])
                continue;
            ?>
            <div class="tab-pane<?php if($setactive) {echo ' active'; $setactive=false;} ?>" id="contents_<?php echo $modules[$keys[$i]]['id'];?>" role="tabpanel">
              <div class="p-20 table-responsive">
                <table id="dt_contents_<?php echo $modules[$keys[$i]]['id'];?>" class="datatable display nowrap table table-hover table-striped table-bordered" style="width:100%">
                  <thead>
                    <tr>
                      <th>ID Numarası</th>
					  <?php if($modules[$keys[$i]]['sortable'] == 1) { ?>
                      <th>Sıra Numarası</th>
					  <?php } ?>
                      <th>Başlık</th>
                      <th>Bağlı Sayfalar</th>
                      <th>Son Güncelleme</th>
                      <th>Modül</th>
                      <th>İşlemler</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>ID Numarası</th>
					  <?php if($modules[$keys[$i]]['sortable'] == 1) { ?>
                      <th>Sıra Numarası</th>
					  <?php } ?>
                      <th>Başlık</th>
                      <th>Bağlı Sayfalar</th>
                      <th>Son Güncelleme</th>
                      <th>Modül</th>
                      <th>İşlemler</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <?php } ?>

          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- End PAge Content -->
    <!-- ============================================================== -->
  </div>
  <!-- ============================================================== -->
  <!-- End Container fluid  -->
  <!-- ============================================================== -->
  <?php $portal->file('scripts'); ?>

  <script type="text/javascript">
    var subtable = null;

    $(document).ready(function() {
      $(document).on('click', '*[data-regenerate-menu]', function() {
        $.post('<?php echo $portal->self()['link'];?>',{action:'regenerate-menu'},function(e){
          if(e.status == 'ok') {
            swal("<?php echo $sl->languages('Başarılı');?>", "<?php echo $sl->languages('Menü başarıyla tekrar oluşturuldu.');?>", "success");
            $(_this).parents('tr').remove();
          } else {
            swal('<?php echo $sl->languages('Başarısız');?>','<?php echo $sl->languages('Menü oluşturma başarısız, tekrar deneyinzi.');?>','error');
          }
        },'json');

      });

      $(document).on('click', '*[data-target="#subpages"]', function() {
        var id = $(this).data('id');

        if(subtable != undefined || subtable != null) {
          console.log('destroy subtable');
          subtable.destroy();
        }

        subtable = $('#subpages_table').DataTable({
          'searching'       : true,
          'ordering'        : false,
          'responsive'      : true,
          "processing"      : true,
          'serverSide'      : true,
    			'stateSave'       : true,
    			'length'          : 50,
          "columns": [
              {"data": "id"},
              {"data": "name"},
              {"data": "subpages"},
              {"data": "modified"},
              {"data": "module"},
              {"data": "actions"}
          ],
          createdRow: function( row, data, dataIndex ) {
            // Set the data-status attribute, and add a class
            $(row).find('td:eq(0)')
              .attr('data-status', data.status ? 'locked' : 'unlocked')
              .addClass('inactive');
          },
          "ajax": {
              "url": "<?php echo $portal->self()['link'];?>?action=rpc&status=1,2&parent=" + id,
              "type": "POST",
              "headers": {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          },
          "language": {
            "url": "lang/<?php echo $sl->languages('prefix');?>.datatables.json"
           }
        });

      });

      var updateMenu = function(e) {
        var list = e.length ? e : $(e.target);
        var autosave = $('input[data-menu="autosave"]:checked').length > 0;

        if(autosave) {
          var data = window.JSON.stringify($('#menu_current').nestable('serialize'));
        }

        if (!window.JSON) {
          console.log('JSON browser support required for this function.');
        }
      };

      $('input[data-menu-subs]').on('change',function(){
        var id = $(this).attr('data-menu-subs');
        $('li[data-id="'+id+'"]').attr('data-sub',($(this).is(':checked')?1:0));
      });

      $('button[data-menu-remove]').on('click',function(){
        var id = $(this).attr('data-menu-remove');

        $('#menu_current li[data-id="'+id+'"]').remove();
      });

      $('button[data-menu="save"]').on('click',function(){
        var data = window.JSON.stringify($('#menu_current').nestable('serialize')),
            button = $(this);
            //data = $('#menu_current').nestable('serialize');


        //var data = $('#menu_current :input').serialize();
        var saving = '<span class="fa-1x"><i class="fas fa-sync fa-spin"></i></span> <?php echo $sl->languages('Kayıt Ediliyor');?>...';
        var idle = '<i class="mdi mdi-content-save-all"></i> <?php echo $sl->languages('Kaydet');?>';

        $(this).html(saving);

        $.post('rpc.php', {"data": data, "uniq": conf["uniq"], "action": "menu"}, function(response) {
          if(response.status) {
            $(button).html(idle);
          }
          if(response.alerts) {
            sl.alert(response.alerts);
          }
        },'json');

        return false;
      });

      $('#menu, #menu_current').nestable({
          group: 1
      }).on('change', updateMenu);

      $(document).on('click', 'a[data-trash]', function(){
        swal({
          title: "<?php echo $sl->languages('Emin misiniz?');?>",
          text: "<?php echo $sl->languages('Sayfayı ve alt sayfalarını geri dönüşüm kutusuna taşıyorsunuz, geri dönüşüm kutusuna taşınan içerikler 30 gün içinde silinir.');?>",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((boolean) => {
          if (boolean) {
            var id = $(this).attr('data-trash');
            var _this = $(this);

            $.post('<?php echo $portal->self()['link'];?>',{action:'remove', id:id},function(e){
              if(e.status == 'ok') {
                swal("<?php echo $sl->languages('Başarılı');?>", "<?php echo $sl->languages('İçerik başarıyla geri dönüşüm kutusuna taşındı.');?>", "success");
                $(_this).parents('tr').remove();
              } else {
                swal('<?php echo $sl->languages('Başarısız');?>','<?php echo $sl->languages('Silme işlemi başarısız lütfen tekrar deneyiniz.');?>','error');
              }
            },'json');
          } else {
          }
        });

        return false;
      });

      <?php
      $keys = array_keys($modules);
      for($i=0;$i<sizeof($modules);$i++) {
        if($modules[$keys[$i]]['hidden'])
          continue;
      ?>
      var dt_contents_<?php echo $modules[$keys[$i]]['id'];?> = $('#dt_contents_<?php echo $modules[$keys[$i]]['id'];?>').DataTable({
        'searching'       : true,
        'ordering'        : true,
        'responsive'      : true,
        "processing"      : true,
        'serverSide'      : true,
  		'stateSave'       : false,
  		'length'          : 50,
        <?php if($modules[$keys[$i]]['sortable'] == 1) { ?>
	        "columns": [
	            {"data": "id"},
	            {"data": "sorder"},
	            {"data": "name"},
	            {"data": "subpages"},
	            {"data": "modified"},
	            {"data": "module"},
	            {"data": "actions"}
	        ],
		"order": [[ 1, "asc" ]],
        rowReorder: {
          dataSrc: 'id'
        },
        <?php } else { ?>
	        "columns": [
	            {"data": "id"},
	            {"data": "name"},
	            {"data": "subpages"},
	            {"data": "modified"},
	            {"data": "module"},
	            {"data": "actions"}
	        ],
			<?php } ?>
        createdRow: function( row, data, dataIndex ) {
          // Set the data-status attribute, and add a class
          $(row).find('td:eq(0)')
            .attr('data-status', data.status ? 'locked' : 'unlocked')
            .addClass('inactive');
        },
        "ajax": {
            <?php if($modules[$keys[$i]]['sortable'] == 1) { ?>
              "url": "<?php echo $portal->self()['link'];?>?action=rpc&status=1,2&parent=<?php echo $modules[$keys[$i]]['parent'];?>&sl_module=<?php echo $modules[$keys[$i]]['id'];?>&extracolumn=sorder",
            <?php } else { ?>
              "url": "<?php echo $portal->self()['link'];?>?action=rpc&status=1,2&parent=<?php echo $modules[$keys[$i]]['parent'];?>&sl_module=<?php echo $modules[$keys[$i]]['id'];?>",
            <?php } ?>
            "type": "POST",
            "headers": {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
        "language": {
          "url": "lang/<?php echo $sl->languages('prefix');?>.datatables.json"
         }
      });
      <?php if($modules[$keys[$i]]['sortable'] == 1) { ?>
        dt_contents_<?php echo $modules[$keys[$i]]['id'];?>.on( 'row-reorder', function ( e, diff, edit ) {
          var items = $('#' + e.currentTarget.id).find('input[name="rows[]"]');
          var data = $('#' + e.currentTarget.id).serializeAny();

          data += '&action=reorder';

          request = $.ajax({
              url: "contents.php",
              type: "post",
              data: data
          });

          request.done(function (response, textStatus, jqXHR){
              // Log a message to the console
              console.log("Items re-ordered!");
            });

          // Callback handler that will be called on failure
          request.fail(function (jqXHR, textStatus, errorThrown){
            swal({
              title: "İşlem tamamlanamadı!",
              text: "Sıralama işlemi yapılamadı lütfen sayfayı yenileyin ve tekrar deneyin.",
              icon: "warning",
              buttons: true,
              dangerMode: true,
            });

              // Log the error to the console
              console.error(
                  "The following error occurred: "+
                  textStatus, errorThrown
              );
          });

        } );
      <?php } ?>
      <?php } ?>

    });
  </script>
  <?php $portal->file('footer'); ?>
