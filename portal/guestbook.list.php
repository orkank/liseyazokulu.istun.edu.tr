<?php
ob_start();

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
    'hash' => $sl->token('sl.guestbook'),
    'name' => 'Kayıt Defteri',
    'link' => 'guestbook.list.php'
  )
);

$success = false;

if (isset($sl->post['action']) and $sl->post['action'] == 'remove') {
  if (is_numeric($sl->post['id'])) {
    $time = time() + 60 * 60 * 24 * 30;

    $c = $sl->db->Query("UPDATE `guestbook` SET
    `status`='0'
    WHERE `id`='" . $sl->post['id'] . "'");
  }

  if ($c) {
    echo json_encode(array('status' => 'ok'));
  } else {
    echo json_encode(array('status' => 'fail'));
  }

  exit;
}

function postToUrlParamsEnhanced($data, $prefix = '') {
  $params = [];
  foreach ($data as $key => $value) {
    $key = urlencode($prefix . $key);
    if (is_array($value)) {
      if (!empty($value)) {
        $params[] = $key . '[]=' . implode('&' . $key . '[]=', array_map(function ($item) use ($prefix) {
          return postToUrlParamsEnhanced($item, $prefix . $key . '[');
        }, $value));
      } else {
        $params[] = $key . '[]='; // Add empty key with brackets for empty array
      }
    } else {
      $params[] = $key . '=' . urlencode($value);
    }
  }
  return implode('&', $params);
}

$filters = '';

if($sl->post['action'] == 'filter') {
  $filters = array_map(function($e) {
    return is_array($e)
      ?
      array_values(array_filter(
          array_map(function($e) { return !empty($e) ? $e : false; }, $e)
          ))
      :
        (!empty($e) ? $e : false);
  }, $sl->post);

  unset($filters['action']);
  // print_r($filters);
  $filters = http_build_query($filters, '', '&');
  // exit;
}

if(isset($sl->post['action']) AND $sl->post['action'] == 'excel') {
  error_reporting(E_ALL);

  // $spreadsheet = new Spreadsheet();
  // $sheet = $spreadsheet->getActiveSheet();
  // $sheet->setTitle('Liste');

  // $spreadsheet->createSheet();
  // $spreadsheet->setActiveSheetIndex(1)->setCellValue('A1', 'world!');

  // $spreadsheet->getActiveSheet()->setTitle('Rapor');
  // // Set active sheet index to the first sheet, so Excel opens this as the first sheet
  // $spreadsheet->setActiveSheetIndex(0);
  // $sheet = $spreadsheet->getActiveSheet();

  // $sheet->setCellValue('A1', 'Adı / Soyadı');
  // $sheet->setCellValue('B1', 'Telefon');
  // $sheet->setCellValue('C1', 'Mezun Olduğu Lise');
  // $sheet->setCellValue('D1', 'Hangi İlden Arıyor');
  // $sheet->setCellValue('F1', 'İlçe');
  // $sheet->setCellValue('G1', 'Puan türü');
  // $sheet->setCellValue('H1', 'Sıralaması');
  // $sheet->setCellValue('I1', 'İlgilendiği Bölüm');
  // $sheet->setCellValue('J1', 'Bizi Nereden Duydunuz');
  // $sheet->setCellValue('K1', 'Kampüsü Ziyaret Ettiniz mi?');
  // $sheet->setCellValue('L1', 'Tercih Fuarını Ziyaret Ettiniz mi?');
  // $sheet->setCellValue('M1', 'Diğer');
  // $sheet->setCellValue('N1', 'Kayıt Zamanı');
  // $sheet->setCellValue('O1', 'Operatör');

  // $writer = new Xlsx($spreadsheet);
  ob_end_clean();

  // CREATE A NEW SPREADSHEET + POPULATE DATA
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $spreadsheet->getActiveSheet()->setTitle('Liste');
  // Add some data
  $spreadsheet->createSheet();
  // Add some data
  $spreadsheet->setActiveSheetIndex(1);
  // Rename worksheet
  $spreadsheet->getActiveSheet()->setTitle('Rapor');
  // Set active sheet index to the first sheet, so Excel opens this as the first sheet
  $spreadsheet->setActiveSheetIndex(0);

  $sheet = $spreadsheet->getActiveSheet();

  $sheet->setCellValue('A1', 'Adı / Soyadı');
  $sheet->setCellValue('B1', 'Telefon');
  $sheet->setCellValue('C1', 'İl');
  $sheet->setCellValue('D1', 'İlçe');
  $sheet->setCellValue('F1', 'Talep');
  $sheet->setCellValue('G1', 'Bizi Nereden Duydunuz?');
  $sheet->setCellValue('H1', 'Aksiyon');
  $sheet->setCellValue('I1', 'Baddata');
  $sheet->setCellValue('J1', 'Negatif');
  $sheet->setCellValue('K1', 'Operatör');
  $sheet->setCellValue('L1', 'Tarih');
  $sheet->setCellValue('M1', 'Türü');
  // $sheet->setCellValue('N1', 'Kayıt Zamanı');
  // $sheet->setCellValue('O1', 'Operatör');

  $writer = new Xlsx($spreadsheet);

  $query = array();
  $query[] = "`status` = 1";

  $filters = array_filter(array_map(function($e) {
    return is_array($e)
      ?
      array_values(array_filter(
          array_map(function($e) { return !empty($e) ? $e : false; }, $e)
          ))
      :
        (!empty($e) ? $e : false);
  }, $sl->post));

  $keys = array_keys($filters);

  for($i=0;$i<sizeof($filters);$i++) {
    $allowed = ['other','talep','hearus','city','type'];

    if(!in_array($keys[$i], $allowed))
      continue;

    $key = $keys[$i];
    $query[] = "`{$key}` IN (".implode(',', $filters[$keys[$i]]).")";
  }

  // if($sl->post['type'] != 0)
	//   $query[] = "`type` = {$sl->post['type']}";

  if(!empty($sl->get['date'])) {
  	$date = explode(' - ', $sl->get['date']);
  	$sdate = strtotime($date[0] . ' 00:00');
  	$edate = strtotime($date[1] . ' 23:59');

  	$query[] = "published BETWEEN {$sdate} AND {$edate}";
  }

  $cell = 2;
  $query = implode(" AND ", $query);
  $date = date('Y-m-d H-i-s');
  // (SELECT GROUP_CONCAT(name ORDER BY name DESC SEPARATOR ',') FROM guestbook_types WHERE FIND_IN_SET(id, g.branch_of_interest)) AS branch_of_interest,
  $sql = "SELECT
  fullname,
  phone,
  (SELECT cityName FROM address_cities WHERE cityID = g.city LIMIT 1) AS city,
  (SELECT countyName FROM address_counties WHERE countyID = g.county LIMIT 1) AS county,
  (SELECT name FROM guestbook_types WHERE id = g.hearus) hearus,
  (SELECT name FROM guestbook_types WHERE id = g.talep) talep,
  type,
  other,
  (CASE WHEN (baddata = 1) THEN 'Evet' ELSE 'Hayır' END) AS baddata,
  (CASE WHEN (negatif = 1) THEN 'Evet' ELSE 'Hayır' END) AS negatif,
  operator,published
  FROM `guestbook` g WHERE $query";

  $data = $sl->db->QueryArray($sql, MYSQLI_ASSOC);

  $data = array_map(function($e) use($aksiyonlar) {
    if(isset($aksiyonlar[$e['other']]))
      $e['other'] = $aksiyonlar[$e['other']][1];
    else
      $e['other'] = '';

    return $e;
  }, $data);

  // print_r($data);
  // die('fadsdas');

  //build an array we can re-use across several operations
  $badchar=array(
      // control characters
      chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), chr(9), chr(10),
      chr(11), chr(12), chr(13), chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20),
      chr(21), chr(22), chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30),
      chr(31),
      // non-printing characters
      chr(127),
      "\r\n", "\r", "\n",
      '🤔','😃','_','(',')','🙏','🩺'
  );

  // $sheet->setCellValue('A1', 'Adı / Soyadı');
  // $sheet->setCellValue('B1', 'Telefon');
  // $sheet->setCellValue('C1', 'İl');
  // $sheet->setCellValue('D1', 'İlçe');
  // $sheet->setCellValue('F1', 'Talep');
  // $sheet->setCellValue('G1', 'Bizi Nereden Duydunuz?');
  // $sheet->setCellValue('H1', 'Aksiyon');
  // $sheet->setCellValue('I1', 'Baddata');
  // $sheet->setCellValue('J1', 'Negatif');
  // $sheet->setCellValue('K1', 'Operatör');
  // $sheet->setCellValue('L1', 'Tarih');

  $types = [
    1 => 'Callcenter',
    2 => 'Ziyaret',
    3 => 'WhatsApp',
    4 => 'Web Form',
    5 => 'Reklam Formları'
  ];

  for($i=0;$i<sizeof($data);$i++) {
    $sheet->setCellValue('A' . $cell, $data[$i]['fullname']);
    $sheet->setCellValue('B' . $cell, $data[$i]['phone']);
    $sheet->setCellValue('C' . $cell, $data[$i]['city']);
    $sheet->setCellValue('D' . $cell, $data[$i]['county']);
    $sheet->setCellValue('F' . $cell, $data[$i]['talep']);
    $sheet->setCellValue('G' . $cell, $data[$i]['hearus']);
    $sheet->setCellValue('H' . $cell, $data[$i]['other']);
    $sheet->setCellValue('I' . $cell, $data[$i]['baddata']);
    $sheet->setCellValue('J' . $cell, $data[$i]['negatif']);
    $sheet->setCellValue('K' . $cell, $data[$i]['operator']);
    $sheet->setCellValue('L' . $cell, date('Y-m-d H:i:s', $data[$i]['published']));
    $sheet->setCellValue('M' . $cell, $types[$data[$i]['type']]);

    // $sheet->setCellValue('M' . $cell, $others[$data[$i]['other']]);
    // $sheet->setCellValue('N' . $cell, date('Y-m-d H:i:s', $data[$i]['published']));
    // $sheet->setCellValue('O' . $cell, $data[$i]['operator']);

    $cell++;
  }

  // $sheet->setCellValue('I1', 'Baddata');
  // $sheet->setCellValue('J1', 'Negatif');
  // $sheet->setCellValue('K1', 'Operatör');
  // $sheet->setCellValue('L1', 'Tarih');
  // $sheet->setCellValue('M1', 'Diğer');
  // $sheet->setCellValue('N1', 'Kayıt Zamanı');
  // $sheet->setCellValue('O1', 'Operatör');

  header('Cache-Control: max-age=0');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
  header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
  header('Pragma: public'); // HTTP/1.0

  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="'.$date.' Rapor.xls"');

  $writer = IOFactory::createWriter($spreadsheet, 'Xls');
  $writer->save('php://output');

  // $data = $sl->db->QueryArray("SELECT * FROM `forms` WHERE $query");
  // $sl->db->QueryArray("UPDATE `forms` SET `view` = 1 WHERE $query");
  exit;
}

if (isset($sl->post['action']) and $sl->post['action'] == '_excel') {
  error_reporting(E_ALL);

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('A1', 'Adı / Soyadı');
  $sheet->setCellValue('B1', 'Telefon');
  $sheet->setCellValue('C1', 'Mezun Olduğu Lise');
  $sheet->setCellValue('D1', 'Hangi İlden Arıyor');
  $sheet->setCellValue('F1', 'İlçe');
  $sheet->setCellValue('G1', 'Puan türü');
  $sheet->setCellValue('H1', 'Sıralaması');
  $sheet->setCellValue('I1', 'İlgilendiği Bölüm');
  $sheet->setCellValue('J1', 'Bizi Nereden Duydunuz');
  $sheet->setCellValue('K1', 'Kampüsü Ziyaret Ettiniz mi?');
  $sheet->setCellValue('L1', 'Tercih Fuarını Ziyaret Ettiniz mi?');
  $sheet->setCellValue('M1', 'Diğer');
  $sheet->setCellValue('N1', 'Kayıt Zamanı');
  $sheet->setCellValue('O1', 'Operatör');

  $writer = new Xlsx($spreadsheet);

  $query = array();
  $query[] = "`status` = 1";

  if ($sl->post['type'] != 0)
    $query[] = "`type` = {$sl->post['type']}";

  if (!empty($sl->get['date'])) {
    $date = explode(' - ', $sl->get['date']);
    $sdate = strtotime($date[0] . ' 00:00');
    $edate = strtotime($date[1] . ' 23:59');

    $query[] = "published BETWEEN {$sdate} AND {$edate}";
  }

  $cell = 2;
  $query = implode(" AND ", $query);
  $date = date('Y-m-d H-i-s');

  $data = $sl->db->QueryArray("SELECT
	  fullname,
  phone,
  graduated_high_school,
  (SELECT cityName FROM address_cities WHERE cityID = g.city LIMIT 1) AS city,
  (SELECT countyName FROM address_counties WHERE countyID = g.county LIMIT 1) AS county,
  scoretype,
  rank,
  (SELECT GROUP_CONCAT(name ORDER BY name DESC SEPARATOR ',') FROM guestbook_types WHERE FIND_IN_SET(id, g.branch_of_interest)) AS branch_of_interest,
  (SELECT name FROM guestbook_types WHERE id = g.hearus) hearus,
  (CASE WHEN (campusvisit = 1) THEN 'Evet' ELSE 'Hayır' END) AS campusvisit,
  (CASE WHEN (preferencefair = 1) THEN 'Evet' ELSE 'Hayır' END) AS preferencefair,
  other,operator,published
	  FROM `guestbook` g WHERE $query", MYSQLI_ASSOC);

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
  /*
  for($i=0;$i<sizeof($data);$i++) {
    $data[$i]['json'] = $sl->decode(json_decode($data[$i]['json'], true));
    $data[$i]['json']['message'] = str_replace(PHP_EOL, null, str_replace($badchar, '', remove_emoji_characters($data[$i]['json']['message'])));
  }
  */
  $others = [
    1 => 'Çok İlgili',
    9 => 'Bilgi Verildi',
    2 => 'Yanlış Numara',
    3 => 'Ulaşılamadı',
    4 => 'Müsait değil, Tekrar aranacak',
    5 => 'İlgilendiği bölüm yok',
    6 => 'Yabancı uyruklu',
    7 => 'Yatay Geçiş'
  ];

  $puanturu = [
    1 => 'SAY',
    2 => 'EA',
    3 => 'SÖZ',
    4 => 'DİL',
  ];

  for ($i = 0; $i < sizeof($data); $i++) {
    $sheet->setCellValue('A' . $cell, $data[$i]['fullname']);
    $sheet->setCellValue('B' . $cell, $data[$i]['phone']);
    $sheet->setCellValue('C' . $cell, $data[$i]['graduated_high_school']);
    $sheet->setCellValue('D' . $cell, $data[$i]['city']);
    $sheet->setCellValue('F' . $cell, $data[$i]['county']);
    $sheet->setCellValue('G' . $cell, $puanturu[$data[$i]['scoretype']]);
    $sheet->setCellValue('H' . $cell, $data[$i]['rank']);
    $sheet->setCellValue('I' . $cell, $data[$i]['branch_of_interest']);
    $sheet->setCellValue('J' . $cell, $data[$i]['hearus']);
    $sheet->setCellValue('K' . $cell, $data[$i]['campusvisit']);
    $sheet->setCellValue('L' . $cell, $data[$i]['preferencefair']);
    $sheet->setCellValue('M' . $cell, $others[$data[$i]['other']]);

    $sheet->setCellValue('N' . $cell, date('Y-m-d H:i:s', $data[$i]['published']));
    $sheet->setCellValue('O' . $cell, $data[$i]['operator']);

    $cell++;
  }

  header('Cache-Control: max-age=0');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
  header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
  header('Pragma: public'); // HTTP/1.0

  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="' . $date . ' Formlar Yeni Gelenler.xls"');

  $writer = IOFactory::createWriter($spreadsheet, 'Xls');
  $writer->save('php://output');

  $data = $sl->db->QueryArray("SELECT * FROM `forms` WHERE $query");

  $sl->db->QueryArray("UPDATE `forms` SET `view` = 1 WHERE $query");
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
  error_reporting(0);
  //$sl->checkToken('sl.contents');

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

  if (!empty($sl->get['date'])) {
    $date = explode(' - ', $sl->get['date']);
    $sdate = strtotime($date[0] . ' 00:00');
    $edate = strtotime($date[1] . ' 23:59');

    $query[] = "published BETWEEN {$sdate} AND {$edate}";
  }

  if (!empty($sl->get['negatif'])) {
    $query[] = "negatif = 1";
  }
  if (!empty($sl->get['baddata'])) {
    $query[] = "baddata = 1";
  }

  if (isset($sl->get['tab']) and !empty($sl->get['tab'])) {
    switch ($sl->get['tab']) {
      case 21:
        $query[] = "`negatif`='1'";
        break;
      case 20:
        $query[] = "`baddata`='1'";
        break;
      case 22:
        $query[] = "
        STR_TO_DATE(callback_time, '%Y-%m-%d') > '" . date('Y-m-d 00:00:00') . "' AND
        STR_TO_DATE(callback_time, '%Y-%m-%d') < '" . date('Y-m-d 23:59:59') . "'
        ";
        break;
      default:
        $query[] = "`other`='{$sl->get['tab']}'";
    }
  }

  $keys = array_keys($sl->get);

  for($i=0;$i<sizeof($sl->get);$i++) {
    $allowed = ['other','talep','hearus','city','type'];

    if(!in_array($keys[$i], $allowed))
      continue;

    $key = $keys[$i];
    $query[] = "`{$key}` IN (".implode(',', $sl->get[$keys[$i]]).")";
  }

  $where = implode(" AND ", $query);
  $sql = "SELECT
  `modified`,
  `id`,
  `fullname`,
  `phone`,
  `published`,
  `updates`,
  negatif,
  baddata
  FROM `guestbook`
  WHERE {$where}

  -- ORDER BY
  -- CASE WHEN callback_time = CURDATE() THEN 0 ELSE 1 END,
  -- callback_time DESC, id DESC
  ";

  // echo $sql;

  $dt->query($sql);

  //$dt->hide('cluster');

  // $dt->edit('id', function ($data) {
  //   return '<label> <input type="checkbox" name="rows[]" value="' . $data['id'] . '"> ' . $data['id'] . '</label>';
  // });

  $dt->edit('id', function ($data) {
    $updates = array_reverse(json_decode($data['updates'], true));

    $update = $updates[0];
    $update['text'] = $update['text'] ?? '';

    if ($update)
      return "
        <small title=\"{$update['text']}\" style=\"overflow:hidden;max-width:190px;width:100%;display:block;\">
          {$update['text']}
        </small>
      ";

    return '';
  });

  // $dt->edit('callback_time', function ($data) {
  //   $dh = date('Ymd', strtotime($data['callback_time']));

  //   if($dh == date('Ymd')) {
  //     return "{$data['callback_time']} <span class='badge badge-danger'>BUGÜN</span>";
  //   }

  //   return $data['callback_time'];
  // });

  $dt->edit('published', function ($data) {
    return date('Y-m-d H:i:s', $data['published']);
  });
  $dt->edit('modified', function ($data) {
    return date('Y-m-d H:i:s', $data['modified']);
  });

  $dt->add('actions', function ($data) {
    global $sl, $portal;

    return '
    <div class="btn-group btn-group-sm" role="group" aria-label="' . $sl->languages('İşlemler') . '">
      <a href="guestbook.php?id=' . $data['id'] . '" class="btn btn-info" title="' . $sl->languages('Görüntüle') . '"><i class="fas fa-edit"></i></a>
      <a href="#" class="btn btn-info" data-trash="' . $data['id'] . '" title="' . $sl->languages('Sil') . '"><i class="fas fa-trash-alt"></i></a>
    </div>
    ';
  });

  echo $dt->generate();
  exit;
}

$portal->file('header');

$where = [];
$where[] = "status = 1";

if (!empty($sl->get['date'])) {
  $date = explode(' - ', $sl->get['date']);
  $sdate = strtotime($date[0] . ' 00:00');
  $edate = strtotime($date[1] . ' 23:59');

  $where[] = "published BETWEEN {$sdate} AND {$edate}";
}

$where = implode(" AND ", $where);

$totals = $sl->db->QuerySingleRowArray("SELECT
  COUNT(*),
  (SELECT COUNT(*) FROM guestbook WHERE type = 1 AND {$where}) AS callcenter,
  (SELECT COUNT(*) FROM guestbook WHERE type = 2 AND {$where}) AS campus,
  (SELECT COUNT(*) FROM guestbook WHERE type = 3 AND {$where}) AS fuar,
  (SELECT COUNT(*) FROM guestbook WHERE type = 4 AND {$where}) AS webform
  FROM guestbook WHERE status = 1
");

if (empty($sl->get['date'])) {
  $sl->get['date'] = date('01-01-2024 - d-m-Y');
}
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
      <div class="col-lg-12">
      </div>
    </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs customtab" role="tablist">
          <?php
          $array = array(
            array('mdi mdi-file-document-box', 'Tümü', 'forms_guestbook', 1),
            array('mdi mdi-file-document-box', 'Tekrar Aranacak', 'forms_guestbook7', 1, 4),
            array('mdi mdi-file-document-box', 'Randevu Verildi', 'forms_guestbook3', 1, 1),
            array('mdi mdi-file-document-box', 'Bilgi Verildi', 'forms_guestbook4', 1, 9),
            array('mdi mdi-file-document-box', 'Yanlış Numara', 'forms_guestbook5', 1, 2),
            array('mdi mdi-file-document-box', 'Ulaşılamadı', 'forms_guestbook6', 1, 3),
            array('mdi mdi-file-document-box', 'İlgilenmiyor', 'forms_guestbook8', 1, 5),
            array('mdi mdi-file-document-box', 'Baddata', 'forms_guestbook99', 1, 20),
            array('mdi mdi-file-document-box', 'Negatif', 'forms_guestbook91', 1, 21),
            array('fas fa-trash-alt', 'Silinen Kayıtlar', 'deleted_guestbook', 0),
          );
          for ($i = 0; $i < sizeof($array); $i++) {
            ?>
            <li class="nav-item">
              <a class="nav-link<?php echo $i == 0 ? ' active' : ''; ?>" data-toggle="tab"
                href="#<?php echo $array[$i][2]; ?>" role="tab">
                <span class="hidden-sm-up"><i class="<?php echo $array[$i][0]; ?>"></i></span> <span
                  class="hidden-xs-down">
                  <i class="<?php echo $array[$i][0]; ?>"></i>
                  <?php echo $array[$i][1]; ?>
                </span>
              </a>
            </li>
          <?php }
            ?>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
          <?php
          for ($i = 0; $i < sizeof($array); $i++) {
            ?>
            <div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>" id="<?php echo $array[$i][2]; ?>"
              role="tabpanel">
              <div class="pl-3 pt-0 pb-1 table-responsive">
                <table id="dt_<?php echo $array[$i][2]; ?>"
                  class="datatable display nowrap table table-hover table-striped table-bordered" style="width:100%">
                  <thead>
                    <tr>
                      <th>Son Güncelleme</th>
                      <th>Son Mesaj</th>
                      <th>İsim / Soyisim</th>
                      <th>Telefon</th>
                      <th>Kayıt Zamanı</th>
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
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <?php
          $sql = "SELECT * FROM
              guestbook WHERE

              STR_TO_DATE(callback_time, '%Y-%m-%d %H:%i:%s') > '" . date('Y-m-d 00:00:00') . "'
              -- AND STR_TO_DATE(callback_time, '%Y-%m-%d %H:%i:%s') < '" . date('Y-m-d 23:59:59') . "'
              AND status = 1
              LIMIT 10
              ";

          $data = $sl->db->QueryArray($sql);
          ?>
          <h3>Sıradaki Aranacaklar</h3>
          <hr>
          <?php
          for ($i = 0; $i < sizeof($data); $i++) {
            echo "<a href=\"guestbook.php?id={$data[$i]['id']}\">{$data[$i]['fullname']} ({$data[$i]['callback_time']})</a>";
            echo '<hr>';
          }
          ?>
        </div>
      </div>
      <form method="post" action="guestbook.list.php">
          <!-- <input type="hidden" name="action" value="excel"> -->
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-auto">
                  <input type="text" style="width:200px" value="<?php echo $sl->get['date']; ?>" class="form-control daterange" />
                </div>
                <div class="col-auto">
                  <div class="form-group" style="min-width:200px;">
                    <select multiple id="type" name="type[]" data-show-subtext="true" data-live-search="true"
                      data-select-picker class="form-control">
                      <option value="" selected>Türü</option>
                      <option value="1">Callcenter</option>
                      <option value="2">Ziyaret</option>
                      <option value="3">WhatsApp</option>
                      <option value="4">Web Form</option>
                      <option value="5">Reklam Formları</option>
                    </select>
                  </div>
                </div>
                <div class="col-auto">
                  <div class="form-group" style="min-width:200px;">
                    <select multiple id="hearus" name="hearus[]" data-show-subtext="true" data-live-search="true"
                      data-select-picker class="form-control">
                      <option value="" selected>Bizi Nereden Duydunuz?</option>
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
                <div class="col-auto">
                  <div class="form-group" style="min-width:200px;">
                    <select id="other" multiple required name="other[]" data-show-subtext="true" data-live-search="true"
                      data-select-picker class="form-control">
                      <option selected value="">Aksiyon</option>
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
                <div class="col-auto">
                  <div class="form-group" style="min-width:200px;">
                    <select id="talep" name="talep[]" multiple data-show-subtext="true" data-live-search="true" data-select-picker
                      class="form-control">
                      <option value="" selected>Başvuru Talebi</option>
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
              <div class="d-flex">
                <button name="action" value="filter" type="submit" value="filter" class="btn btn-primary mt-2 mr-2">Filtrele</button>
                <button name="action" value="excel" type="submit" class="btn btn-secondary mt-2">Excel İndir</button>
                <?php
                  $disabled = "disabled";
                  if (!empty($sl->get['date'])) {
                    $disabled = "";
                    echo '<strong class="d-inline-block align-self-center ml-2 mt-2 text-muted">Seçili Tarih: '.$sl->get['date'].'</strong>';
                  }
                ?>
              </div>
            </div>
          </div>
        </form>

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

<script type="text/javascript" src="js/fusioncharts-suite-xt/js/fusioncharts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/fusioncharts.charts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/fusioncharts.powercharts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/integrations/jquery/js/jquery-fusioncharts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/themes/fusioncharts.theme.gammel.js"></script>

<script type="text/javascript">

  <?php
  for ($i = 0; $i < sizeof($array); $i++) {
    ?>
    var table_<?php echo $array[$i][2]; ?> = $('#dt_<?php echo $array[$i][2]; ?>').DataTable({
      'searching': true,
      'ordering': true,
      'responsive': false,
      "processing": true,
      'serverSide': true,
      'order': [[4, "desc"]],
      'stateSave': false,
      'length': 100,
      'lengthMenu': [
        [100, 250, 500, -1],
        [100, 250, 500, 'Tümü']
      ],
      "columns": [
        { "data": "modified" },
        { "data": "id" },
        { "data": "fullname" },
        { "data": "phone" },
        { "data": "published" },
        { "data": "actions" }
      ],
      createdRow: function (row, data, dataIndex) {
        // Set the data-status attribute, and add a class
        if (data.negatif == 1)
          $(row).addClass('bg-warning');
        if (data.baddata == 1)
          $(row).addClass('bg-danger text-white');

        $(row).find('td:eq(0)')
          .attr('data-status', data.status ? 'locked' : 'unlocked')
          .addClass('inactive');
      },
      "ajax": {
      <?php
      if($i == 0) {
      ?>
        "url": "<?php echo $portal->self()['link']; ?>?action=rpc&<?php echo $filters; ?>&status=<?php echo $array[$i][3]; ?>&date=<?php echo $sl->get['date']; ?>",
      <?php } else { ?>
        "url": "<?php echo $portal->self()['link']; ?>?action=rpc&tab=<?php echo $array[$i][4]; ?>&status=<?php echo $array[$i][3]; ?>&date=<?php echo $sl->get['date']; ?>",
      <?php } ?>
        "type": "POST",
        "headers": {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      },
      "language": {
        "url": "lang/<?php echo $sl->languages('prefix'); ?>.datatables.json"
      }
    });
  <?php }
    ?>

  function refreshtables() {
    <?php
    for ($i = 0; $i < sizeof($array); $i++) {
      ?>
      table_<?php echo $array[$i][2]; ?>.ajax.reload();
    <?php } ?>
  }

  $(document).ready(function () {
    $('.dateFilter').daterangepicker({
      locale: {
        format: 'DD/MM/YYYY'
      }
    });

    $('[data-fancybox="wcallback"]').fancybox({
      afterClose: function (instance, current) {
        console.log('closing...');
        refreshtables();
      }
    });

    // $(document).on('click', 'button[value="excel"]', function () {
    //   swal({
    //     title: "<?php echo $sl->languages('Doğrulayınız'); ?>",
    //     text: "<?php echo $sl->languages('Excel olarak indirme işlemi başlatıyorsunuz, excel olarak indirdiğiniz mesajlar okundu olarak işaretlensin mi?'); ?>",
    //     icon: "warning",
    //     buttons: ["Hayır, sadece excel oluştur", "Evet, okundu olarak işaretle!"],
    //   }).then((boolean) => {
    //     if (boolean) {
    //       console.log('mark as read');
    //     } else {
    //       console.log('mark as unread');
    //     }

    //     //return false;
    //   });

    //   //return false;
    // });

    $(document).on('click', 'a[data-trash]', function () {
      swal({
        title: "<?php echo $sl->languages('Emin misiniz?'); ?>",
        text: "<?php echo $sl->languages('Kayıt geri dönüşüm sekmesine taşınacaktır, 3 ay içinde geri alınmayan kayıtlar tamamen silinir.'); ?>",
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

    $('#city').on('change', function () {
      var id = $(this).find('option:selected').val();


    });

    $(".daterange").daterangepicker();

    $('.daterange').on('apply.daterangepicker', function (ev, picker) {
      var date = $('.daterange').val();
      $('.daterange').attr('disabled', 'disabled');
      $('.daterange').val('Bekleyiniz...');
      location.href = 'guestbook.list.php?date=' + date;
    });

  });
</script>
<?php $portal->file('footer'); ?>