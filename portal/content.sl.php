<?php
require('build.php');

$portal->self(
  array(
    'uniq' => 'ff89f59e56dddc2b2a8a28a72fb3f420',
    'module' => $sl->get['config'],
    'params' => (!empty($sl->get['id']) ? 'id='.$sl->get['id'] : ''),
    'id_content' => (!empty($sl->get['id']) ? $sl->get['id'] : 0)
  )
);

if(isset($sl->get['q']) OR isset($sl->get['parentV2'])) {
  /*
  {
    "total_count": 1,
    "incomplete_results": false,
    "items": [{
      "id": 156,
      "name": "Vibratörler",
      "status": 1,
      "parent": 0,
      "modified": 1622388048,
      "path": null
    }]
  }
  */
  $where = [];

  if(!empty($sl->get['q']))
    $where[] = "`name{tr}` LIKE '%{$sl->get['q']}%'";

  if($sl->get['parent'])
    $where[] = "`parent` = '{$sl->get['parent']}'";

  if($sl->get['id']) {
    $id = array_map(function($e){ return "'{$e}'"; }, explode(',', $sl->get['id']));
    $id = implode(',',$id);
    $where[] = "`id` IN ({$id})";
  }

  if(isset($sl->get['page']) AND !empty($sl->get['page'])) {
    $value = $sl->get['page'] * $sl->get['total'];
    $page = "OFFSET {$value}";
  }

  if(isset($sl->get['limit']) AND !empty($sl->get['limit'])) {
    $limit = "LIMIT {$sl->get['limit']}";
  }

  $where = implode(" AND ", $where);
  $sql = "SELECT `name{tr}` AS name,id,parent FROM contents
    WHERE {$where}
    {$page} {$limit}
    ";
  $items = $sl->db->QueryArray($sql);

  for($i=0;$i<sizeof($items);$i++) {
    $parent = $items[$i]['parent'];

    for(;;) {
      if($parent == 0)
        break;

      $data = $sl->db->QuerySingleRowArray("SELECT `name{tr}` as name,parent FROM contents WHERE id = {$parent}");
      $parent = $data['parent'];
      $items[$i]['name'] = "{$data['name']} > {$items[$i]['name']}";
    }
  }

  echo json_encode(array(
    "total_count" => count($items),
    "incomplete_results" => true,
    "items" => $items
  ));
  exit;
}
if(is_file( __DIR__ .DS. 'config' .DS. $portal->self()['config'])) {
  $config = $portal->config();

  if(isset($portal->self()['table']) AND isset($config['table']['columns']))
    $portal->table($portal->self()['table'],$config['table']['columns']);
} else {
  if(!$sl->get['id'])
    $portal->break();
}

if(isset($sl->post['action']) AND $sl->post['action'] == 'do') {
  $existing_id = (int)$sl->get['id'];

  if($sl->post['button'] == 'new' OR !$existing_id) {
    $existing_id = 0;
  }

  if(!isset($sl->post['default']))
    $sl->post['default'] = 0;

  $portal->data('_clear');
  $portal->data('_merge_config', $sl->post);
  $sl->columns($portal->self()['table']);
  $existing = 0;

  if(is_numeric($existing_id) AND $existing_id > 0) {
    $existing = $sl->db->QuerySingleValue("SELECT `id` FROM `".$portal->self()['table']."` WHERE `id`='".$existing_id."'");
  }

  $portal->readySQL(true);
  $portal->readySQL['columns']['modified'] = time();

  if(!is_numeric($existing_id) AND empty($sl->post['published'])) {
    $portal->readySQL['columns']['published'] = time();
  } elseif(!empty($sl->post['published'])) {
    $portal->readySQL['columns']['published'] = (is_numeric($sl->post['published'])) ? ($sl->post['published']) : strtotime($sl->post['published']);
  }

  if(!isset($sl->post['sl_module']))
    $portal->readySQL['columns']['sl_module'] = $portal->self()['module'];

  //$portal->readySQL['columns']['config'] = $sl->post['sl_module'];

  if($sl->db->AutoInsertUpdate(
    $portal->self()['table'],
    $portal->readySQL['columns'],
    array(
      'id' => $existing
      )
    )
  ) {
    if(!$existing)
      $existing = $sl->db->GetLastInsertID();

    $sl->db->Query("UPDATE `images` SET `rid` = {$existing}, `_rid` = 0 WHERE `_rid` = {$existing} AND `group`='{$portal->self()['group']}'");

    $sl->alert(
      array(
        $sl::ALERT_SUCCESS,
        sprintf($sl->languages('%s ID numarasıyla işlem başarıyla gerçekleştirildi.'), $existing)
      )
    );

    $nodes = &$portal->readySQL['nodes'];
    $keys = array_keys($nodes);
    //print_r($nodes);

    for($i=0;$i<sizeof($nodes);$i++) {
      $sl->db->Query("DELETE FROM content_nodes WHERE cid = {$existing} AND `key` = '{$keys[$i]}'");

      if(is_array($nodes[$keys[$i]])) {
        for($s=0;$s<sizeof($nodes[$keys[$i]]);$s++) {
          if(!empty($nodes[$keys[$i]][$s]))
          if(!$sl->db->AutoInsertUpdate(
            'content_nodes',
            array(
              'cid' => $existing,
              'key' => "'".$keys[$i]."'",
              'value' => $nodes[$keys[$i]][$s],
              'onload' => 1
            ),
            array(
              'key' => "'".$keys[$i]."'",
              'value' => "'".$nodes[$keys[$i]][$s]."'",
              'cid' => $existing
              )
            )
          ) {
            $sl->alert(
              array(
                $sl::ALERT_ERROR,
                sprintf($sl->languages("%s, Bazı işlemler gerçekleştirilemedi, ${$nodes[$keys[$i]][$s]}, ". $sl->db->Error()), $existing)
              )
            );
          }
        }
      } else {
        if(!empty($nodes[$keys[$i]][$s]))
        if(!$sl->db->AutoInsertUpdate(
          'content_nodes',
          array(
            'cid' => $existing,
            'key' => "'".$keys[$i]."'",
            'value' => $nodes[$keys[$i]],
            'onload' => 1
          ),
          array(
            'key' => "'".$keys[$i]."'",
            'cid' => $existing
            )
          )
        ) {
          $sl->alert(
            array(
              $sl::ALERT_ERROR,
              sprintf($sl->languages("%s, Bazı işlemler gerçekleştirilemedi, ${$nodes[$keys[$i]][$s]}, ". $sl->db->Error()), $existing)
            )
          );
        }
      }
    }

    /*
    $parent_id = $sl->post['parent'];
    $slugs = array();
    //$slugs[] = 'tr';
    $i = 0;
    for(;;) {
      $i = $i+1;
      $parent = $sl->db->QuerySingleRowArray("SELECT `id`,`slug{tr}` AS `slug`,`parent` FROM `contents` WHERE `id`='{$parent_id}'");
      $node = $sl->db->QuerySingleValue("SELECT `value` FROM `content_nodes` WHERE `cid` = {$parent['id']} AND `key` = 'headerlink'");

      if($i > 8) {
        echo 'huu break';
        break;
      }

      if(($parent AND $node == 2)) {
        $parent_id = $parent['parent'];
        $slugs[] = $parent['slug'];
      } else {
        break;
      }
    }
    unset($slugs[0]);
    $slugs = array_reverse($slugs);
    $slugs[] = $sl->post['slug{tr}'];
    $slugs = implode(',', $slugs);
    echo $slugs;
    $sl->smarty->clearCache('content.html', md5($sl->post['slug{tr}']));
    */

    if($existing) {
      $portal->self(
        array(
          'params' => 'id=' . $existing
        )
      );
      $portal->data($sl->post,false,true);

      //header("Location: {$portal->self()['link']}");
      //exit;
    }

    $portal->data('_clear');
    $portal->data($sl->post,false,true);

  } else {
    $portal->data('_clear');
    $portal->data($sl->post,false,true);

    $sl->alert(
      array(
        $sl::ALERT_ERROR,
        $sl->languages('İşlem gerçekleştirilemedi, lütfen tekrar deneyiniz.') ."<br><br>". $sl->db->Error() . "<br><br>" .
          $sl->db->GetLastSQL()
      )
    );
  }
}

if(!empty($sl->get['id']))
  $portal->getcontent();

//print_r($portal->data);
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

  <form action="<?php echo $portal->self()['link'];?>" method="post" enctype="multipart/form-data" style="max-width:1920px;">
    <input type="hidden" name="action" value="do">

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body p-b-0">
            <div class="float-left">
              <h4 class="card-title d-inline"><?php echo $portal->self()['name'];?></h4><br>
              <h6 class="card-subtitle d-inline"><?=$portal->module['desc'];?></h6>
            </div>
            <div class="float-right text-right">
              <!-- Example single danger button -->
              <?php if(count($sl->languages('langs')) > 1) { ?>
              <div class="btn-group">
                <button type="button" class="btn btn-primary" data-select-scheme aria-haspopup="true" aria-expanded="false">Şablon Yükle</button>
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?=$sl->languages('Dil Değiştir');?>
                </button>
                <div class="dropdown-menu">
                  <?php
                  for($i=0;$i<sizeof($sl->languages('langs'));$i++) {
                  ?>
                    <a class="dropdown-item" data-change-lang="<?=$sl->languages('langs')[$i]['prefix'];?>" href="#"><?=$sl->languages('langs')[$i]['name'];?></a>
                  <?php } ?>
                </div>
              </div>
              <hr>
              <?php } ?>
              <small><?=(!empty($portal->data['modified'])?$sl->languages('Son güncelleme: ').date('Y-m-d H:i:s',$portal->data['modified']):'');?></small>
            </div>
          </div>
          <!-- Nav tabs -->
          <ul class="nav nav-tabs customtab" role="tablist">
            <?php
            for($i=0;$i<sizeof($config['tabs']);$i++) {
            ?>
            <li class="nav-item"> <a class="nav-link<?=($i==0?' active':'');?>" data-toggle="tab" href="#tab_<?=md5($config['tabs'][$i]['name']);?>" role="tab"><?=$config['tabs'][$i]['name'];?></a> </li>
            <?php } ?>
          </ul>
          <!-- Tab panes -->
          <div class="tab-content">

            <?php
            for($i=0;$i<sizeof($config['tabs']);$i++) {
            ?>
            <div class="tab-pane<?=($i==0?' active':'');?>" id="tab_<?=md5($config['tabs'][$i]['name']);?>" role="tabpanel">
              <div class="p-20 table-responsive">
                <div class="row">
                  <?php
                  if(isset($config['tabs'][$i]['inputs']) AND is_array($config['tabs'][$i]['inputs'])) {
                    echo $portal->FormGenerator($config['tabs'][$i]['inputs']);
                  }
                  ?>
                </div>
              </div>
            </div>
            <?php } ?>

          </div>
          <div class="card-body p-b-20">
            <div class="btn-group float-left" role="group" aria-label="">
              <!-- <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> -->
              <button data-publish type="submit" name="button" value="publish" class="btn btn-info"> <i class="fas fa-upload"></i> <?=$sl->languages('Kaydet');?> </button>
              <button data-new type="submit" name="button" value="new" class="btn btn-secondary"> <i class="far fa-save"></i> <?=$sl->languages('Yeni Saytfa Oluştur');?> </button>
              <!--
              <button data-save type="button" name="button" value="save" class="btn btn-secondary"> <i class="far fa-save"></i> <?=$sl->languages('Taslak Olarak Kaydet');?> </button>
              -->
            </div>
          </div>

        </div>
      </div>
    </div>
  </form>

    <!-- ============================================================== -->
    <!-- End PAge Content -->
    <!-- ============================================================== -->
  </div>
  <!-- ============================================================== -->
  <!-- End Container fluid  -->
  <!-- ============================================================== -->
  <?php $portal->file('scripts'); ?>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script type="text/javascript">
  var formatRepo = function (repo) {
    if (repo.loading) return repo.text;

    var markup = "<div class='select2-result-repository clearfix'>" +
    "<div class='select2-result-repository__meta'>" +
    "<div class='select2-result-repository__title'>" + repo.name + "</div>";

    markup += "</div></div>";

    return markup;
  }

  var formatRepoSelection = function(repo) {
    var name = (repo.name) ? repo.name : repo.text;
    return name + " - (ID "+repo.id+")";
  }

    $(document).ready(function() {
      sl.dataLang();
      sl.alphaLength();
      sl.slugs();
      sl.Switchery();
      sl.removeImage();
      sl.changeLang();
      sl.editImages();
      sl.datePicker();

      $('.subs_sortable').sortable().bind('sortupdate', function(e, ui) {
          //ui.item contains the current dragged element.
          //Triggered when the user stopped sorting and the DOM position has changed.
      });

      $(document).on('keyup, change', 'input[data-function="slug"]', function(){
      });

    });
  </script>
  <?php $portal->file('footer'); ?>
