<?php
require('build.php');

use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

$portal->self(
  array(
    'uniq' => '93bae5c1ab5f8dee7d116bf6665be51f',
    'hash' => $sl->token('sl.forms'),
    'name' => 'Gelen Formlar',
    'link' => 'forms.crm.php'
  )
);

$array = array(
  array('mdi mdi-file-document-box', 'Bize Sorun', 'forms_messagetous', [2472,2642], 4),
  array('mdi mdi-file-document-box', 'Reklam Formları', 'ad_forms', 2644, 5),
);

$import_response = [];

if (isset($sl->post['action']) and $sl->post['action'] == 'import') {
  error_reporting(E_ALL);
# Create a new Xls Reader
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

// Tell the reader to only read the data. Ignore formatting etc.
// $reader->setReadDataOnly(true);

// Read the spreadsheet file.
$spreadsheet = $reader->load($_FILES['file']['tmp_name']);

$sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());

$data = $sheet->toArray();

$id = 2644;

for($i=1;$i<sizeof($data);$i++) {
  if(empty($data[$i][2]))
    continue;

  $json = json_encode([
    'fullname' => $data[$i][2],
    'email' => $data[$i][3],
    'phone' => $data[$i][4]
  ], JSON_UNESCAPED_UNICODE);

  $sl->db->Query("INSERT INTO forms
    (title,email,variables,`json`,`type`,`status`,modified,published,view) VALUES
    ('{$data[$i][2]}','{$data[$i][3]}','{$json}','{$json}',$id, 1, ".time().",".time().", 0)
  ");
}

  $import_response = ['status' => 0];
}

if (isset($sl->post['action']) and $sl->post['action'] == 'remove') {
  if (is_numeric($sl->post['id'])) {
    $time = time() + 60 * 60 * 24 * 30;

    $c = $sl->db->Query("UPDATE `forms` SET
    `status`='0',
    `expiry` = '" . $time . "'
    WHERE `id`='" . $sl->post['id'] . "'");
  }

  if ($c) {
    echo json_encode(array('status' => 'ok'));
  } else {
    echo json_encode(array('status' => 'fail'));
  }

  exit;
}

if (isset($sl->post['action']) and $sl->post['action'] == 'excel') {
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

  $skip = array('g-recaptcha-response', 'agree', 'g-recaptcha-responseV2', 'action', 'type');

  $replace = array(
    'name' => 'İsim',
    'tel' => 'Telefon',
    'surname' => 'Soyisim',
    'dogum_tarihi' => 'Doğum Tarihi',
    'programsecimi' => 'Program Seçimi',
    'ceptelefonu' => 'Cep Telefonu',
    'mezun_universite' => 'Mezun Üniversite',
    'yds_puani' => 'YDS Puanı',
    'yokdil_puani' => 'YÖK Dil Puanı',
    'uyruk' => 'Uyruk',
    'ales_puani' => 'ALES Puanı',
    'email' => 'E-posta',
    'tckimlik' => 'T.C. Kimlik',
    'fullname' => 'İsim Soyisim',
    'phone' => 'Telefon',
    'cinsiyeti' => 'Cinsiyet',
    'mezuniyet_yili' => 'Mezuniyet Yılı',
    'published' => 'Kayıt Tarihi',
  );

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('A1', 'ID Numarası');
  $sheet->setCellValue('B1', 'Kayıt Tarihi');
  $sheet->setCellValue('C1', 'İlgili Sayfa');

  /*
  $sheet->setCellValue('B1', 'İsim / Soyisim');
  $sheet->setCellValue('C1', 'E-posta');
  $sheet->setCellValue('D1', 'Telefon');
  $sheet->setCellValue('F1', 'Bölüm');
  $sheet->setCellValue('G1', 'Mesaj');
  $sheet->setCellValue('H1', 'Tarih');
  */


  $date = date('Y-m-d H-i-s');
  $query = array();
  // $query[] = "`status` = 1";

  if (@$sl->post['viewonly'] == 1)
    $query[] = "`view` = 0";

  $types = $sl->db->QueryArray("SELECT id,`name{tr}` AS name FROM contents WHERE id IN ({$sl->post['type']})", MYSQLI_ASSOC, 'id');

  if ($sl->post['type']) {
    $query[] = "`type` IN ({$sl->post['type']})";
  }

  if ($sl->post['datefilter']) {
    $sl->post['datefilter'] = str_replace('/', '-', $sl->post['datefilter']);
    $_date = explode(' - ', $sl->post['datefilter']);
    $sdate = strtotime($_date[0] . ' 00:00');
    $edate = strtotime($_date[1] . ' 23:59');
    $query[] = "`published` BETWEEN {$sdate} AND {$edate}";
  }

  $query = implode(" AND ", $query);
  $sql = "SELECT * FROM `forms` WHERE $query";

  $data = $sl->db->QueryArray($sql, MYSQLI_ASSOC);

  function array2csv(array &$array)
  {
    if (count($array) == 0) {
      return null;
    }
    ob_start();
    $df = fopen("php://output", 'w');
    fputcsv($df, array_keys(reset($array)));
    foreach ($array as $row) {
      fputcsv($df, $row);
    }
    fclose($df);
    return ob_get_clean();
  }


  function download_send_headers($filename)
  {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
  }

  $array = array_map(function ($e) {
    $e['json'] = json_decode($e['json'], true);

    return [$e['title'], $e['email'], $e['json']['phone'], $e['json']['message']];
  }, $data);

  download_send_headers("Formlar" . date("Y-m-d") . ".csv");
  echo array2csv($array);
  exit;

  if ($data) {
    $data_keys = array_keys(json_decode($data[0]['json'], true));
    $range = range('A', 'Z');

    $num_range = 0;
    $cell = 1;

    $sheet->setCellValue($range[$num_range] . $cell, 'ID Numarası');
    $sheet->setCellValue('B' . $cell, 'Kayıt Tarihi');
    $sheet->setCellValue('C' . $cell, 'İlgili Sayfa');
    $num_range++;
    $num_range++;
    $num_range++;

    for ($i = 0; $i < sizeof($data_keys); $i++) {
      //echo $range[$num_range] . $cell." = " . $data_keys[$i]."<br>";
      if (in_array($data_keys[$i], $skip))
        continue;

      if (isset($replace[$data_keys[$i]]))
        $data_keys[$i] = $replace[$data_keys[$i]];

      $sheet->setCellValue($range[$num_range] . $cell, $data_keys[$i]);
      $num_range++;
    }
    $writer = new Xlsx($spreadsheet);

    $cell++;

    //build an array we can re-use across several operations
    $badchar = array(
      // control characters
      chr(0),
      chr(1),
      chr(2),
      chr(3),
      chr(4),
      chr(5),
      chr(6),
      chr(7),
      chr(8),
      chr(9),
      chr(10),
      chr(11),
      chr(12),
      chr(13),
      chr(14),
      chr(15),
      chr(16),
      chr(17),
      chr(18),
      chr(19),
      chr(20),
      chr(21),
      chr(22),
      chr(23),
      chr(24),
      chr(25),
      chr(26),
      chr(27),
      chr(28),
      chr(29),
      chr(30),
      chr(31),
      // non-printing characters
      chr(127),
      "\r\n",
      "\r",
      "\n",
      '🤔',
      '😃',
      '_',
      '(',
      ')',
      '🙏',
      '🩺'
    );

    function remove_emoji_characters($text)
    {
      return $text;
    }

    for ($i = 0; $i < sizeof($data); $i++) {
      $data[$i]['json'] = $sl->decode(json_decode($data[$i]['json'], true));

      if (isset($data[$i]['json']['message']))
        $data[$i]['json']['message'] = str_replace(PHP_EOL, null, str_replace($badchar, '', remove_emoji_characters($data[$i]['json']['message'])));
    }

    for ($i = 0; $i < sizeof($data); $i++) {
      $sheet->setCellValue('A' . $cell, $data[$i]['id']);
      $sheet->setCellValue('B' . $cell, date('Y-m-d H:i:s', $data[$i]['published']));

      $type = isset($types[$data[$i]['type']]) ? $types[$data[$i]['type']]['name'] : '';

      $sheet->setCellValue('C' . $cell, $type);

      if (isset($data[$i]['json']) and !empty($data[$i]['json'])) {
        $keys = array_keys($data[$i]['json']);

        $num_range = 2;

        for ($s = 0; $s < sizeof($data_keys); $s++) {
          if (in_array($data_keys[$s], $skip))
            continue;

          $num_range++;

          //echo $range[$num_range] . $cell ." = ". $data[$i]['json'][$keys[$s]] . "<br>";
          $value = (isset($data[$i]['json'][$keys[$s]])) ? $data[$i]['json'][$keys[$s]] : '';
          $sheet->setCellValue($range[$num_range] . $cell, $value);
        }

        /*
         $sheet->setCellValue('B' . $cell, $data[$i]['title']);
         $sheet->setCellValue('C' . $cell, $data[$i]['email']);
         $sheet->setCellValue('D' . $cell, $data[$i]['json']['tel']);
         $sheet->setCellValue('F' . $cell, $data[$i]['json']['bolum']);
         $sheet->setCellValue('G' . $cell, ($data[$i]['json']['message']));
         $sheet->setCellValue('H' . $cell, date('Y-m-d H:i:s', $data[$i]['published']));
         */

        $cell++;
      }
    }
  }

  header('Cache-Control: max-age=0');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
  header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
  header('Pragma: public'); // HTTP/1.0

  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="' . $date . ' Formlar.xls"');

  $writer = IOFactory::createWriter($spreadsheet, 'Xls');
  $writer->save('php://output');

  //$data = $sl->db->QueryArray("SELECT * FROM `forms` WHERE $query");

  #$sl->db->QueryArray("UPDATE `forms` SET `view` = 1 WHERE $query");
  exit;
}

/*
$data = $sl->db->QueryArray("SELECT * FROM `forms`");
for($i=0;$i<sizeof($data);$i++) {
  $data[$i]['json'] = $sl->decode(json_decode($data[$i]['json'], true));

  $type = ($data[$i]['json']['readyfor'] == 1)?1:($data[$i]['json']['readyfor'] == 2?2:0);
  $type = ($data[$i]['json']['_incorporation_ready'] == 1)?1:$type;
  $type = ($data[$i]['json']['_incorporation_ready'] == 2)?2:$type;
  $sl->db->Query("UPDATE `forms` SET `type`='{$type}' WHERE `id`='{$data[$i]['id']}'");
}
*/

if (isset($sl->get['action']) and $sl->get['action'] == 'rpc') {
  //$sl->checkToken('sl.contents');
  error_reporting(0);

  $config = [
    'host' => $sl->settings['database']['host'],
    'port' => '3306',
    'username' => $sl->settings['database']['user'],
    'password' => $sl->settings['database']['password'],
    'database' => $sl->settings['database']['db']
  ];

  $dt = new Datatables(new MySQL($config));

  $where = '';
  $query = array();

  $status = explode(',', $sl->get['status']);
  $_status = array();

  for ($i = 0; $i < sizeof($status); $i++)
    $_status[] = "`status`='{$status[$i]}'";

  $query[] = "(" . implode(' OR ', $_status) . ")";

  global $types;

  $types = $sl->db->QueryArray("SELECT id,`name{tr}` AS name FROM contents WHERE id IN ({$sl->get['type']})", MYSQLI_ASSOC, 'id');

  if ($sl->get['id']) {
    $query[] = "`type` IN ({$sl->get['id']})";
  }

  $where = implode(" AND ", $query);
  $order = "{$sl->post['columns'][$sl->post['order'][0]['column']]['data']}";

  $sql = "SELECT
    `id`,
    `id` AS _id,
    `title`,
    `email`,
    `published`,
    `view`,
    `json`,
    `uniq`,
    `type`
  FROM `forms` WHERE {$where}
  ORDER BY {$order} {$sl->post['order'][0]['dir']}
  ";

  $dt->query($sql);

  //$dt->hide('cluster');

  $dt->edit('id', function ($data) {
    return '<label> <input data-status="' . $data['status'] . '" type="checkbox" name="rows[]" value="' . $data['id'] . '"> ' . $data['id'] . '</label>';
  });

  $dt->edit('_id', function ($data) use ($sl) {
    $data['json'] = json_decode($data['json'], true);
    $id = $sl->db->QuerySingleValue("SELECT id FROM guestbook WHERE phone LIKE '%{$data['json']['phone']}'");

    if ($id) {
      return "
      <a href=\"guestbook.php?id={$id}\">Kaydı Var</a>
      ";
    } else {
      return '';
    }
  });

  $dt->edit('title', function ($data) {
    global $types;
    return $data['title'] . "<br><small>{$types[$data['type']]['name']}</small>";
  });

  $dt->edit('email', function ($data) {
    $data['json'] = json_decode($data['json'], true);
    return "{$data['json']['phone']} <small class=\"d-block text-muted\">{$data['email']}</small>";
  });

  $dt->edit('published', function ($data) {
    return date('Y-m-d H:i:s', $data['published']);
  });

  $dt->edit('type', function ($data) {
    $status = $data['view'] == 1 ?
      '(Okunmuş)' :
      '<strong class="badge badge-danger"><i class="fas fa-envelope"></i> Yeni</strong>';

    $uniq = (empty($data['uniq'])) ? '<span class="badge badge-info ml-1">İşlenmemiş</span>' : '<span class="badge badge-primary ml-1">Aktarılmış</span>';
    $status = $status . $uniq;

    $value = '
    <a data-filter="#content-for-load" data-width="600" data-height="600" href="javascript:;"
data-src="forms.view.php?id=' . $data['id'] . '"
data-fancybox="wcallback" data-type="ajax">Görüntüle ' . $status . ' </a>
    ';

    return $value;
  });

  $dt->add('actions', function ($data) {
    global $sl, $portal;
    $json = json_encode($data);

    return '
    <div class="btn-group" role="group" aria-label="' . $sl->languages('İşlemler') . '">
      <a href="forms.view.php?id=' . $data['id'] . '" class="btn btn-info" title="' . $sl->languages('Görüntüle') . '"><i class="fas fa-edit"></i></a>
      <a href="#" class="btn btn-info" data-trash="' . $data['id'] . '" title="' . $sl->languages('Sil') . '"><i class="fas fa-trash-alt"></i></a>

      <a href="guestbook.form.php?fid=' . $data['id'] . '&type='.$sl->get['type'].'" data-type="ajax" class="btn btn-info"
        data-fancybox title="' . $sl->languages('Aktar') . '"><i class="fas fa-copy"></i></a>

      </div>
    ';
  });

  echo $dt->generate();
  exit;
}

$portal->file('header');


?>
<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper page-wrapper-small">
  <!-- ============================================================== -->
  <!-- Container fluid  -->
  <!-- ============================================================== -->
  <div class="container-fluid">
    <?php include('breadcrumb.php'); ?>
    <!-- ============================================================== -->
    <!-- Start Page Content -->
    <!-- ============================================================== -->

    <div class="row">
      <div class="col-md-6 order-2">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">
              Filtre ve İndirme
            </h4>
            <h6 class="card-subtitle">
              Mesajları filtreleyebilir veya excel çıktısını alabilirsiniz.
            </h6>

            <form action="forms.crm.php" method="post">
              <input type="hidden" name="action" value="excel">

              <div class="input-group mb-3">
                <input type="text" name="datefilter" class="form-control dateFilter" placeholder="Tarih Aralığı">
                <div class="input-group-append">
                  <span class="input-group-text">
                    <span class="ti-calendar"></span>
                  </span>
                </div>
              </div>
              <!--
              <input type="hidden" name="mark-as" value="read">

              <button type="submit" name="method" value="filter" class="btn btn-primary">Filtrele</button>
              <hr>
            -->
              <div class="col-md-4">
                <!--
                <div class="switch"> <label>Aday Öğrenci <input type="checkbox" checked><span class="lever"></span>Bize Sorun</label> </div>
                -->
                <select class="form-control" name="type">
                  <?php
                  for ($i = 0; $i < sizeof($array); $i++) {
                    $type = (is_array($array[$i][3])) ? implode(',', $array[$i][3]) : $array[$i][3];
                    echo '<option value="' . $type . '">' . $array[$i][1] . '</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-4">
                <div class="switch"> <label>Tümü <input type="checkbox" name="viewonly" value="1" checked><span
                      class="lever"></span>Sadece Yeni Gelenler</label> </div>
              </div>
              <button type="submit" name="method" value="excel" class="btn btn-secondary">Excel İndir</button>
            </form>

          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <?php
            if(!empty($import_response))
                  echo '<div class="alert bg-warning">İçeri aktarma başarıyla tamamlandı.</div>';
            ?>
            <h4 class="card-title">
              İçeri Aktarma
            </h4>
            <h6 class="card-subtitle">
              Zaman, Platform, Fullname, Email, Phone Number
              <br> * Bu kolon sırasına göre ayarlanmıştır.
            </h6>
            <form action="forms.crm.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="action" value="import">

              <div class="input-group mb-3">
                <input type="file" name="file" class="form-control" placeholder="">
                <div class="input-group-append">
                  <span class="input-group-text">
                    <span class="ti-file"></span>
                  </span>
                </div>
              </div>

              <button type="submit" name="method" value="excel" class="btn btn-secondary">İçeri Aktar</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <div class="card">
          <div class="card-body p-b-0">
            <h4 class="card-title">
              <?php echo $portal->module['name']; ?>
            </h4>
            <h6 class="card-subtitle">
              <?php echo $portal->module['desc']; ?>
            </h6>
          </div>
          <!-- Nav tabs -->
          <ul class="nav nav-tabs customtab" role="tablist">
            <?php
            for ($i = 0; $i < sizeof($array); $i++) {
              $ids = (is_array($array[$i][3])) ? implode(',', $array[$i][3]) : $array[$i][3];
              $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `forms` WHERE `view` = 0 AND `status` = 1 AND `type` IN ({$ids})");
              ?>
              <li class="nav-item">
                <a class="nav-link<?php echo $i == 0 ? ' active' : ''; ?>" data-toggle="tab"
                  href="#<?php echo $array[$i][2]; ?>" role="tab">
                  <span class="hidden-sm-up"><i class="<?php echo $array[$i][0]; ?>"></i></span> <span
                    class="hidden-xs-down">
                    <i class="<?php echo $array[$i][0]; ?>"></i>
                    <?php echo $array[$i][1]; ?>
                  </span>
                  <span class="badge badge-danger">
                    <?php echo $count ?>
                  </span> </a>
              </li>
            <?php } ?>
          </ul>
          <!-- Tab panes -->
          <div class="tab-content">
            <?php
            for ($i = 0; $i < sizeof($array); $i++) {
              ?>
              <div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>" id="<?php echo $array[$i][2]; ?>"
                role="tabpanel">
                <div class="p-20 table-responsive">
                  <table id="dt_<?php echo $array[$i][2]; ?>"
                    class="datatable display nowrap table table-hover table-striped table-bordered" style="width:100%">
                    <thead>
                      <tr>
                        <th>ID Numarası</th>
                        <th>Önceki Kayıt</th>
                        <th>İsim</th>
                        <th>E-posta</th>
                        <th>Kayıt Zamanı</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
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
    /* For Export Buttons available inside jquery-datatable "server side processing" - Start
    - due to "server side processing" jquery datatble doesn't support all data to be exported
    - below function makes the datatable to export all records when "server side processing" is on */

    function newexportaction(e, dt, button, config) {
      var self = this;
      var oldStart = dt.settings()[0]._iDisplayStart;
      dt.one('preXhr', function (e, s, data) {
        // Just this once, load all data from the server...
        data.start = 0;
        data.length = 2147483647;
        dt.one('preDraw', function (e, settings) {
          // Call the original action function
          if (button[0].className.indexOf('buttons-copy') >= 0) {
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
          } else if (button[0].className.indexOf('buttons-excel') >= 0) {
            $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
          } else if (button[0].className.indexOf('buttons-csv') >= 0) {
            $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
          } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
            $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
          } else if (button[0].className.indexOf('buttons-print') >= 0) {
            $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
          }
          dt.one('preXhr', function (e, s, data) {
            // DataTables thinks the first item displayed is index 0, but we're not drawing that.
            // Set the property to what it was before exporting.
            settings._iDisplayStart = oldStart;
            data.start = oldStart;
          });
          // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
          setTimeout(dt.ajax.reload, 0);
          // Prevent rendering of the full data to the DOM
          return false;
        });
      });
      // Requery the server with the new one-time export settings
      dt.ajax.reload();
    };
    //For Export Buttons available inside jquery-datatable "server side proce
    <?php
    for ($i = 0; $i < sizeof($array); $i++) {
      ?>
      var table_<?php echo $array[$i][2]; ?> = $('#dt_<?php echo $array[$i][2]; ?>').DataTable({
        'searching': true,
        'ordering': true,
        'responsive': true,
        "processing": true,
        'serverSide': true,
        'order': [[4, "desc"]],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        'stateSave': false,
        'buttons': false,
        /*
          "buttons": [
                                     {
                                         "extend": 'copy',
                                         "text": '<i class="fas fa-file"></i> Kopyala',
                                         "titleAttr": 'Copy',
                                         "action": newexportaction
                                     },
                                     {
                                         "extend": 'excel',
                                         "text": '<i class="fas fa-file-excel"></i> Excel',
                                         "titleAttr": 'Excel',
                                         "action": newexportaction
                                     },
                                     {
                                         "extend": 'csv',
                                         "text": '<i class="fas fa-file-text"></i> CSV',
                                         "titleAttr": 'CSV',
                                         "action": newexportaction
                                     },
                                     {
                                         "extend": 'pdf',
                                         "text": '<i class="fas fa-file-pdf"></i> PDF',
                                         "titleAttr": 'PDF',
                                         "action": newexportaction
                                     },
                                     {
                                          "extend": 'print',
                                          "text": '<i class="fas fa-print"></i> Yazdır',
                                          "titleAttr": 'Print',
                                          "action": newexportaction
                                     }
          ],
      */
        'length': 50,
        /*

            l - length changing input control
            f - filtering input
            t - The table!
            i - Table information summary
            p - pagination control
            r - processing display element

        */
        //"dom": 'Bfrt<lip>',
        "dom": "<'row'<'col-sm-12 col-md-8'B><'col-sm-12 col-md-4'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-12 col-md-5'li><'col-sm-12 col-md-7'p>>",
        "columns": [
          { "data": "id" },
          { "data": "_id" },
          { "data": "title" },
          { "data": "email" },
          { "data": "published" },
          { "data": "type" },
          { "data": "actions" }
        ],
        createdRow: function (row, data, dataIndex) {
          // Set the data-status attribute, and add a class
          $(row).find('td:eq(0)')
            .attr('data-status', data.status ? 'locked' : 'unlocked')
            .addClass('inactive');
        },
        "ajax": {
          "url": "<?php echo $portal->self()['link']; ?>?action=rpc&id=<?php echo (is_array($array[$i][3])) ? implode(',', $array[$i][3]) : $array[$i][3]; ?>&type=<?php echo $array[$i][4]; ?>&status=1",
          "type": "POST",
          "headers": {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        },
        "language": {
          "url": "lang/<?php echo $sl->languages('prefix'); ?>.datatables.json"
        }
      });
    <?php } ?>

    function refreshtables() {
      <?php
      for ($i = 0; $i < sizeof($array); $i++) {
        ?>
        table_<?php echo $array[$i][2]; ?>.ajax.reload();
      <?php } ?>
    }

    $(document).ready(function () {
      $('#city').bind('change', function () {
        var cityCode = $('#city').find('option:selected').val();

        $.ajax({
          url: '/tr/xhr/counties',
          type: 'POST',
          data: 'cityID=' + cityCode + '&action=counties',
          success: function (data) {
            var data = JSON.parse(data);
            var options = '';

            $.each(data, function (e, item) {
              options += '<option value="' + item.countyID + '">' + item.countyName + '</option>';
            });

            console.log(options);
            $('#counties').html(options);
            $('#counties').selectpicker('refresh');
          },
          error: function () {
          }
        });
      });

      $(document).on('submit', '#quickform', function (e) {
        $(this).attr('disabled', 'disabled');
        e.preventDefault();

        $.post('guestbook.php?ajax=true', $('#quickform').serialize(), function (e) {
          var json = JSON.parse(e);

          if (json.status == 1) {
            $.fancybox.close();
          } else {
            alert(json.msg);
          }

          table_forms_messagetous.ajax.reload(() => { }, false);
        });

      });

      // $(document).on('click', '*[data-fancybox]', function() {
      //   setTimeout(() => {
      //     $('#callback_time').datepicker({
      //       format: 'yyyy/mm/dd',
      //     });
      //   }, 500);
      // });

      $('.dateFilter').daterangepicker({
        locale: {
          format: 'DD-MM-YYYY'
        }
      });

      $('[data-fancybox="wcallback"]').fancybox({
        afterClose: function (instance, current) {
          console.log('closing...');
          refreshtables();
        }
      });

      $(document).on('click', 'button[value="esxcel"]', function () {
        swal({
          title: "<?php echo $sl->languages('Doğrulayınız'); ?>",
          text: "<?php echo $sl->languages('Excel olarak indirme işlemi başlatıyorsunuz, excel olarak indirdiğiniz mesajlar okundu olarak işaretlensin mi?'); ?>",
          icon: "warning",
          buttons: ["Hayır, sadece excel oluştur", "Evet, okundu olarak işaretle!"],
        }).then((boolean) => {
          if (boolean) {
            console.log('mark as read');
          } else {
            console.log('mark as unread');
          }

          //return false;
        });

        //return false;
      });

      $(document).on('click', 'a[data-trash]', function () {
        swal({
          title: "<?php echo $sl->languages('Emin misiniz?'); ?>",
          text: "<?php echo $sl->languages('Form geri dönüşüm sekmesine taşınacaktır, 3 ay içinde geri alınmayan kayıtlar tamamen silinir.'); ?>",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((boolean) => {
          if (boolean) {
            var id = $(this).attr('data-trash');
            var _this = $(this);

            $.post('<?php echo $portal->self()['link']; ?>', { action: 'remove', id: id }, function (e) {
              if (e.status == 'ok') {
                swal("<?php echo $sl->languages('Başarılı'); ?>", "<?php echo $sl->languages('Form başarıyla geri dönüşüm kutusuna taşındı.'); ?>", "success");
                refreshtables();
              } else {
                refreshtables();
                swal('<?php echo $sl->languages('Başarısız'); ?>', '<?php echo $sl->languages('Silme işlemi başarısız lütfen tekrar deneyiniz.'); ?>', 'error');
              }
            }, 'json');
          } else {
          }
        });

        return false;
      });

      $('.dt-buttons').remove();
    });
  </script>
  <?php $portal->file('footer'); ?>