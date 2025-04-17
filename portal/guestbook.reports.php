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
    'name' => 'Ziyaretçi Defteri',
    'link' => 'guestbook.list.php'
  )
);

$success = false;

if(isset($sl->post['action']) AND $sl->post['action'] == 'remove') {
  if(is_numeric($sl->post['id'])) {
    $time = time() + 60 * 60 * 24 * 30;

    $c = $sl->db->Query("UPDATE `guestbook` SET
    `status`='0'
    WHERE `id`='".$sl->post['id']."'");
  }

  if($c) {
    echo json_encode(array('status' => 'ok'));
  } else {
    echo json_encode(array('status' => 'fail'));
  }

  exit;
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
  // $sheet->setCellValue('M1', 'Diğer');
  // $sheet->setCellValue('N1', 'Kayıt Zamanı');
  // $sheet->setCellValue('O1', 'Operatör');

  $writer = new Xlsx($spreadsheet);

  $query = array();
  $query[] = "`status` = 1";

  if($sl->post['type'] != 0)
	  $query[] = "`type` = {$sl->post['type']}";

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

  $data = $sl->db->QueryArray("SELECT
	  fullname,
  phone,
  (SELECT cityName FROM address_cities WHERE cityID = g.city LIMIT 1) AS city,
  (SELECT countyName FROM address_counties WHERE countyID = g.county LIMIT 1) AS county,
  (SELECT name FROM guestbook_types WHERE id = g.hearus) hearus,
  (SELECT name FROM guestbook_types WHERE id = g.talep) talep,
  other,
  (CASE WHEN (baddata = 1) THEN 'Evet' ELSE 'Hayır' END) AS baddata,
  (CASE WHEN (negatif = 1) THEN 'Evet' ELSE 'Hayır' END) AS negatif,
    operator,published
	  FROM `guestbook` g WHERE $query", MYSQLI_ASSOC);

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

    // $sheet->setCellValue('M' . $cell, $others[$data[$i]['other']]);
    // $sheet->setCellValue('N' . $cell, date('Y-m-d H:i:s', $data[$i]['published']));
    // $sheet->setCellValue('O' . $cell, $data[$i]['operator']);

    $cell++;
  }

  $spreadsheet->setActiveSheetIndex(1);

  $sheet = $spreadsheet->getActiveSheet();

  $cell = 0;
  $cellB = 0;

  $arr = [
    [4, 'Webform'],
    [5, 'Reklam Formları'],
    [1, 'Callcenter'],
    [2, 'Ziyaret'],
    [3, 'WhatsApp']
  ];

  for($i=0;$i<sizeof($arr);$i++) {
    $cell++;

    $sheet->setCellValue("A{$cell}", $arr[$i][1]);

    $spreadsheet->getActiveSheet()->getStyle("A{$cell}:A{$cell}")->getFont()->setBold(true);

    $cell++;
    $sheet->setCellValue("A{$cell}", 'Randevu Verildi');
    $cell++;
    $sheet->setCellValue("A{$cell}", 'Bilgi Verildi');
    $cell++;
    $sheet->setCellValue("A{$cell}", 'Yanlış Numara');
    $cell++;
    $sheet->setCellValue("A{$cell}", 'Ulaşılamadı');
    $cell++;
    $sheet->setCellValue("A{$cell}", 'Müsait Değil, Tekrar Aranacak');
    $cell++;
    $sheet->setCellValue("A{$cell}", 'İlgilenmiyor');

    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND {$query}"));

    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND other = 1 AND {$query}"));
    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND other = 9 AND {$query}"));
    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND other = 2 AND {$query}"));
    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND other = 3 AND {$query}"));
    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND other = 4 AND {$query}"));
    $cellB++;
    $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND other = 5 AND {$query}"));

    $hearus = $sl->db->QueryArray(
      "SELECT * FROM `guestbook_types` WHERE `type` = 1"
    );

    for($s=0;$s<sizeof($hearus);$s++) {
      $cell++;
      $sheet->setCellValue("A{$cell}", $hearus[$s]['name']);
    }

    for($s=0;$s<sizeof($hearus);$s++) {
      $cellB++;
      $sheet->setCellValue("B{$cellB}", $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE type = {$arr[$i][0]} AND hearus = {$hearus[$s]['id']} AND {$query}"));
    }

    $cellB++;
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

if(isset($sl->get['action']) AND $sl->get['action'] == 'rpc') {
  error_reporting(E_ALL);
  //$sl->checkToken('sl.contents');

  $config = [ 'host'     => $sl->settings['database']['host'],
              'port'     => '3306',
              'username' => $sl->settings['database']['user'],
              'password' => $sl->settings['database']['password'],
              'database' => $sl->settings['database']['db'] ];

  $dt = new Datatables(new MySQL($config));

  $where = '';
  $query = array();

  $status = explode(',', $sl->get['status']);
  $_status = array();

  for($i=0;$i<sizeof($status);$i++)
    $_status[] = "`status`='{$status[$i]}'";

  $query[] = "(".implode(' OR ',$_status).")";

  if(!empty($sl->get['date'])) {
  	$date = explode(' - ', $sl->get['date']);
  	$sdate = strtotime($date[0] . ' 00:00');
  	$edate = strtotime($date[1] . ' 23:59');

  	$query[] = "published BETWEEN {$sdate} AND {$edate}";
  }

  $where = implode(" AND ", $query);

  $dt->query("SELECT
    `id`,
    `fullname`,
    `phone`,
    `operator`,
    `published`
  FROM `guestbook` WHERE {$where}
  ORDER BY `published` DESC
  ");

  //$dt->hide('cluster');

  $dt->edit('id', function($data) {
      return '<label> <input type="checkbox" name="rows[]" value="'.$data['id'].'"> '.$data['id'].'</label>';
  });

  $dt->edit('published', function($data) {
    return date('Y-m-d H:i:s',$data['published']);
  });

  $dt->add('actions', function($data) {
    global $sl, $portal;

    return '
    <div class="btn-group" role="group" aria-label="'.$sl->languages('İşlemler').'">
      <a href="guestbook.php?id='.$data['id'].'" class="btn btn-info" title="'.$sl->languages('Görüntüle').'"><i class="fas fa-edit"></i></a>
      <a href="#" class="btn btn-info" data-trash="'.$data['id'].'" title="'.$sl->languages('Sil').'"><i class="fas fa-trash-alt"></i></a>
    </div>
    ';
  });

  echo $dt->generate();
  exit;
}

$portal->file('header');

$where = [];
$where[] = "status = 1";

if(!empty($sl->get['date'])) {
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
  (SELECT COUNT(*) FROM guestbook WHERE type = 4 AND {$where}) AS webform,
  (SELECT COUNT(*) FROM guestbook WHERE type = 5 AND {$where}) AS reklam
  FROM guestbook WHERE status = 1
");

if(empty($sl->get['date'])) {
	$sl->get['date'] = date('01-m-Y - d-m-Y');
}
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
                <div class="col-lg-12">
					<form method="post" action="guestbook.reports.php?date=<?php echo $sl->get['date'];?>">
						<input type="hidden" name="action" value="excel">
                  <div class="card">
                    <div class="card-body">
                    <input type="text" value="<?php echo $sl->get['date']; ?>" class="form-control daterange" />
					<?php
					$disabled = "disabled";
					if(!empty($sl->get['date'])) {
						$disabled = "";
						echo '<strong class="d-block mt-2 text-muted">Seçili Tarih:</strong>' . $sl->get['date'];
					}
					?>
				<div class="col-12 d-none">
                  <div class="form-group mb-3 row pb-3">
                    <div class="col-12">
                      <label for="inputEmail3" class="col-12 text-end control-label col-form-label">Kayıt Türü <small class="text-muted text-red">* Gerekli</small></label>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" required="" class="form-check-input" checked id="all" value="0" name="type">
                        <label class="form-check-label" for="all">Tümü</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" required="" class="form-check-input" id="callcenter" value="1" name="type">
                        <label class="form-check-label" for="callcenter">Call Center</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" required="" class="form-check-input" id="kampusziyareti" value="2" name="type">
                        <label class="form-check-label" for="kampusziyareti">Ziyaret</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" required="" class="form-check-input" id="fuarziyareti" value="3" name="type">
                        <label class="form-check-label" for="fuarziyareti">WhatsApp</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" required="" class="form-check-input" id="webform" value="4" name="type">
                        <label class="form-check-label" for="webform">Web Form</label>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="form-check">
                        <input type="radio" required="" class="form-check-input" id="reklam" value="5" name="type">
                        <label class="form-check-label" for="reklam">Reklam Formları</label>
                      </div>
                    </div>

                  </div>
                </div>					<hr/>
					<button <?php echo $disabled ?>  type="submit" class="btn btn-primary mt-2">Excel İndir</button>
					</div>
					</div>
				</form>
				</div>

                <!-- Column -->
                <div class="col-lg-auto col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex flex-row">
                        <div class="
                            round round-lg
                            text-white
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                            bg-info
                            mr-2
                          ">
                          <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
                          </svg>
                        </div>
                        <div class="ms-2 align-self-center">
                          <h3 class="mb-0"><?php echo $totals['callcenter']; ?></h3>
                          <h6 class="text-muted mb-0">Call Center</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Column -->
                <!-- Column -->
                <div class="col-lg-auto col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex flex-row">
                        <div class="
                            round round-lg
                            text-white
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                            bg-warning
                            mr-2
                          ">
                          <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M12,3L1,9L12,15L21,10.09V17H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z" />
                          </svg>
                        </div>
                        <div class="ms-2 align-self-center">
                          <h3 class="mb-0"><?php echo $totals['campus']; ?></h3>
                          <h6 class="text-muted mb-0">Ziyaret</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Column -->
                <!-- Column -->
                <div class="col-lg-auto col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex flex-row">
                        <div class="
                            round round-lg
                            text-white
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                            bg-primary
                            mr-2
                          ">
                          <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M20 6H4V4H20V6M21 12V14H20V20H18V14H14V20H4V14H3V12L4 7H20L21 12M12 14H6V18H12V14M18.96 12L18.36 9H5.64L5.04 12H18.96M7 24H9V22H7V24M11 24H13V22H11V24M15 24H17V22H15V24Z" />
                          </svg>
                        </div>
                        <div class="ms-2 align-self-center">
                          <h3 class="mb-0"><?php echo $totals['fuar']; ?></h3>
                          <h6 class="text-muted mb-0">WhatsApp</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Column -->
                <!-- Column -->
                <div class="col-lg-auto col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex flex-row">
                        <div class="
                            round round-lg
                            text-white
                            d-flex
                            justify-content-center
                            align-items-center
                            rounded-circle
                            bg-danger
                            mr-2
                          ">
                          <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M16.36,14C16.44,13.34 16.5,12.68 16.5,12C16.5,11.32 16.44,10.66 16.36,10H19.74C19.9,10.64 20,11.31 20,12C20,12.69 19.9,13.36 19.74,14M14.59,19.56C15.19,18.45 15.65,17.25 15.97,16H18.92C17.96,17.65 16.43,18.93 14.59,19.56M14.34,14H9.66C9.56,13.34 9.5,12.68 9.5,12C9.5,11.32 9.56,10.65 9.66,10H14.34C14.43,10.65 14.5,11.32 14.5,12C14.5,12.68 14.43,13.34 14.34,14M12,19.96C11.17,18.76 10.5,17.43 10.09,16H13.91C13.5,17.43 12.83,18.76 12,19.96M8,8H5.08C6.03,6.34 7.57,5.06 9.4,4.44C8.8,5.55 8.35,6.75 8,8M5.08,16H8C8.35,17.25 8.8,18.45 9.4,19.56C7.57,18.93 6.03,17.65 5.08,16M4.26,14C4.1,13.36 4,12.69 4,12C4,11.31 4.1,10.64 4.26,10H7.64C7.56,10.66 7.5,11.32 7.5,12C7.5,12.68 7.56,13.34 7.64,14M12,4.03C12.83,5.23 13.5,6.57 13.91,8H10.09C10.5,6.57 11.17,5.23 12,4.03M18.92,8H15.97C15.65,6.75 15.19,5.55 14.59,4.44C16.43,5.07 17.96,6.34 18.92,8M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                          </svg>
                        </div>
                        <div class="ms-2 align-self-center">
                          <h3 class="mb-0"><?php echo $totals['webform']; ?></h3>
                          <h6 class="text-muted mb-0">Web Form</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Column -->
                <div class="col-lg-auto col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex flex-row">
                        <div class="
                            round round-lg
                            text-white
                            d-flex
                            justify-content-center
                            align-items-center
                            rounded-circle
                            bg-danger
                            mr-2
                          ">
                          <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M16.36,14C16.44,13.34 16.5,12.68 16.5,12C16.5,11.32 16.44,10.66 16.36,10H19.74C19.9,10.64 20,11.31 20,12C20,12.69 19.9,13.36 19.74,14M14.59,19.56C15.19,18.45 15.65,17.25 15.97,16H18.92C17.96,17.65 16.43,18.93 14.59,19.56M14.34,14H9.66C9.56,13.34 9.5,12.68 9.5,12C9.5,11.32 9.56,10.65 9.66,10H14.34C14.43,10.65 14.5,11.32 14.5,12C14.5,12.68 14.43,13.34 14.34,14M12,19.96C11.17,18.76 10.5,17.43 10.09,16H13.91C13.5,17.43 12.83,18.76 12,19.96M8,8H5.08C6.03,6.34 7.57,5.06 9.4,4.44C8.8,5.55 8.35,6.75 8,8M5.08,16H8C8.35,17.25 8.8,18.45 9.4,19.56C7.57,18.93 6.03,17.65 5.08,16M4.26,14C4.1,13.36 4,12.69 4,12C4,11.31 4.1,10.64 4.26,10H7.64C7.56,10.66 7.5,11.32 7.5,12C7.5,12.68 7.56,13.34 7.64,14M12,4.03C12.83,5.23 13.5,6.57 13.91,8H10.09C10.5,6.57 11.17,5.23 12,4.03M18.92,8H15.97C15.65,6.75 15.19,5.55 14.59,4.44C16.43,5.07 17.96,6.34 18.92,8M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                          </svg>
                        </div>
                        <div class="ms-2 align-self-center">
                          <h3 class="mb-0"><?php echo $totals['reklam']; ?></h3>
                          <h6 class="text-muted mb-0">Reklam Formları</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Column -->
              </div>
              <div class="row">
                <div class="col-lg-6 col-md-12">
                  <div class="card">
                    <div class="card-body">
                      <div id="kayitturu" style="min-width:100%;min-height:400px;"></div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-md-12">
                  <div class="card">
                    <div class="card-body">
                      <div id="hearus" style="min-width:100%;min-height:400px;"></div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6 col-md-12">
                  <div class="card">
                    <div class="card-body">
                      <div id="others" style="min-width:100%;height:400px;"></div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6 col-md-12">
                  <div class="card">
                    <div class="card-body">
                      <div id="city" style="min-width:100%;height:400px;"></div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6 col-md-12">
                  <div class="card">
                    <div class="card-body">
                      <div id="talep" style="min-width:100%;height:400px;"></div>
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

<script type="text/javascript" src="js/fusioncharts-suite-xt/js/fusioncharts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/fusioncharts.charts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/fusioncharts.powercharts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/integrations/jquery/js/jquery-fusioncharts.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>
<script type="text/javascript" src="js/fusioncharts-suite-xt/js/themes/fusioncharts.theme.gammel.js"></script>

  <script>
  $("#others").insertFusionCharts({
    type: "bar2d",
    width: "100%",
    height: "100%",
    dataFormat: "json",
    dataSource: {
      chart: {
        formatNumber: "0",
        exportEnabled: "1",
        exportMode: "client",
        labelDisplay: "Auto",
        showPercentValues:'0',
        showPercentInToolTip: '1',
        caption: "Aksiyonlar",
        subcaption: "Kayıt türüne göre dağılımı gösterir",
        showvalues: "1",
        showpercentintooltip: "0",
        numbersuffix: " - Kişi",
        enablemultislicing: "1",
        theme: "fusion",
      },
      data: [
        {
          label: "Baddata",
          value: "<?php echo $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE baddata = 1 AND {$where}") ?>"
        },
        {
          label: "Negatif",
          value: "<?php echo $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE negatif = 1 AND {$where}"); ?>"
        },
        <?php
        for($i=0;$i<sizeof($aksiyonlar);$i++) {
          $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE other = {$aksiyonlar[$i][0]} AND
          {$where}");
          // if($count < 1)
          //   continue;
        ?>
        {
          label: "<?php echo $aksiyonlar[$i][1]; ?>",
          value: "<?php echo $count; ?>"
        },
        <?php } ?>
      ]
    }
  });

$("#talep").insertFusionCharts({
    type: "bar2d",
    width: "100%",
    height: "100%",
    dataFormat: "json",
    dataSource: {
      chart: {
        formatNumber: "0",
        exportEnabled: "1",
        exportMode: "client",
        labelDisplay: "Auto",
        showPercentValues:'0',
        showPercentInToolTip: '1',
        caption: "Başvuru Talebi",
        subcaption: "Kayıt türüne göre dağılımı gösterir",
        showvalues: "1",
        showpercentintooltip: "0",
        numbersuffix: " - Kişi",
        enablemultislicing: "1",
        theme: "fusion",
      },
      data: [
        <?php
        $types = $sl->db->QueryArray("SELECT * FROM guestbook_types WHERE type = 4 AND parent = 0");

        for($i=0;$i<sizeof($types);$i++) {
          $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE talep = {$types[$i]['id']} AND {$where}");
          // if($count < 1)
          //   continue;
        ?>
        {
          label: "<?php echo $types[$i]['name']; ?>",
          value: "<?php echo $count; ?>"
        },
        <?php } ?>
      ]
    }
  });

  $("#hearus").insertFusionCharts({
    type: "bar2d",
    width: "100%",
    height: "100%",
    dataFormat: "json",
    dataSource: {
      chart: {
    formatNumber: "0",
    exportEnabled: "1",
    exportMode: "client",

        labelDisplay: "Auto",
		showPercentValues:'0',
		showPercentInToolTip: '1',
        caption: "Bizi Nereden Duydunuz",
        subcaption: "Kayıt türüne göre dağılımı gösterir",
        showvalues: "1",
        showpercentintooltip: "0",
        numbersuffix: " - Kişi",
        enablemultislicing: "1",
        theme: "fusion",
      },
      data: [
        <?php
        $types = $sl->db->QueryArray("SELECT * FROM guestbook_types WHERE type = 1 AND parent = 0");

        for($i=0;$i<sizeof($types);$i++) {
          $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE hearus = {$types[$i]['id']} AND {$where}");
          if($count < 1)
            continue;
        ?>
        {
          label: "<?php echo $types[$i]['name']; ?>",
          value: "<?php echo $count; ?>"
        },
        <?php } ?>
      ]
    }
  });

  $("#kayitturu").insertFusionCharts({
    type: "bar2d",
    width: "100%",
    height: "100%",
    dataFormat: "json",
    dataSource: {
      chart: {
    exportEnabled: "1",
    exportMode: "client",
    formatNumber: "0",

        labelDisplay: "Auto",
		showPercentValues:'0',
		showPercentInToolTip: '1',

        caption: "Kayıt Türü",
        subcaption: "Kayıt türüne göre dağılımı gösterir",
        showvalues: "1",
        showpercentintooltip: "0",
        numbersuffix: " - Kişi",
        enablemultislicing: "1",
        theme: "fusion"
      },
      data: [
        {
          label: "Callcenter",
          value: "<?php echo $totals['callcenter']; ?>"
        },
        {
          label: "Ziyaret",
          value: "<?php echo $totals['campus']; ?>"
        },
        {
          label: "WhatsApp",
          value: "<?php echo $totals['fuar']; ?>"
        },
        {
          label: "Web Form",
          value: "<?php echo $totals['webform']; ?>"
        },
        {
          label: "Reklam Formları",
          value: "<?php echo $totals['reklam']; ?>"
        }
      ]
    }
  });

  $("#city").insertFusionCharts({
    type: "pie2d",
    width: "100%",
    height: "100%",
    dataFormat: "json",
    dataSource: {
      chart: {
    exportEnabled: "1",
    exportMode: "client",
    yAxisValuesPadding: "0",
	valuePadding: "0",
    formatNumber: "0",

        caption: "Şehirlere Göre",
        subcaption: "0'dan fazla kaydı olan şehirler listelenir",
        yaxisname: "Toplam Kayıt Edilen Kişi Sayısı",
        decimals: "1",
        theme: "fusion",

        labelDisplay: "Auto",
		showPercentValues:'0',
		showPercentInToolTip: '1',
        showvalues: "1",

      },
      data: [
        <?php
        $cities = $sl->db->QueryArray("SELECT * FROM address_cities");
        for($i=0;$i<sizeof($cities);$i++) {
          $count = $sl->db->QuerySingleValue("SELECT COUNT(*) FROM `guestbook` WHERE city = {$cities[$i]['cityID']} AND {$where}");
          if($count < 1)
            continue;
        ?>
        {
          label: "<?php echo trim($cities[$i]['cityName']);?>",
          value: "<?php echo $count; ?>"
        },
        <?php } ?>

      ]
    }
  });
  </script>

  <script type="text/javascript">
  <?php
  for($i=0;$i<sizeof($array);$i++) {
  ?>
  var table_<?php echo $array[$i][2];?> = $('#dt_<?php echo $array[$i][2];?>').DataTable({
    'searching'       : true,
    'ordering'        : true,
    'responsive'      : true,
    "processing"      : true,
    'serverSide'      : true,
    'order'           : [[ 3, "desc" ]],
    'stateSave'       : false,
    'length'          : 50,
     "columns": [
        {"data": "id"},
        {"data": "fullname"},
        {"data": "phone"},
        {"data": "operator"},
        {"data": "published"},
        {"data": "actions"}
    ],
    createdRow: function( row, data, dataIndex ) {
      // Set the data-status attribute, and add a class
      $(row).find('td:eq(0)')
        .attr('data-status', data.status ? 'locked' : 'unlocked')
        .addClass('inactive');
    },
    "ajax": {
        "url": "<?php echo $portal->self()['link'];?>?action=rpc&status=<?php echo $array[$i][3];?>&date=<?php echo $sl->get['date'];?>",
        "type": "POST",
        "headers": {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    },
    "language": {
      "url": "lang/<?php echo $sl->languages('prefix');?>.datatables.json"
     }
  });
  <?php } ?>

    function refreshtables() {
      <?php
      for($i=0;$i<sizeof($array);$i++) {
      ?>
      table_<?php echo $array[$i][2];?>.ajax.reload();
      <?php } ?>
    }

    $(document).ready(function() {
      $('.dateFilter').daterangepicker({
          locale: {
              format: 'DD/MM/YYYY'
          }
      });

      $('[data-fancybox="wcallback"]').fancybox({
      	afterClose: function(instance, current) {
          console.log('closing...');
          refreshtables();
      	}
      });

      $(document).on('click', 'button[value="excel"]', function(){
        swal({
          title: "<?php echo $sl->languages('Doğrulayınız');?>",
          text: "<?php echo $sl->languages('Excel olarak indirme işlemi başlatıyorsunuz, excel olarak indirdiğiniz mesajlar okundu olarak işaretlensin mi?');?>",
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

      $(document).on('click', 'a[data-trash]', function(){
        swal({
          title: "<?php echo $sl->languages('Emin misiniz?');?>",
          text: "<?php echo $sl->languages('Kayıt geri dönüşüm sekmesine taşınacaktır, 3 ay içinde geri alınmayan kayıtlar tamamen silinir.');?>",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((boolean) => {
          if (boolean) {
            var id = $(this).attr('data-trash');
            var _this = $(this);

            $.post('<?php echo $portal->self()['link'];?>',{action:'remove', id:id},function(e){
              if(e.status == 'ok') {
                swal("<?php echo $sl->languages('Başarılı');?>", "<?php echo $sl->languages('Form başarıyla geri dönüşüm kutusuna taşındı.');?>", "success");
                refreshtables();
              } else {
                refreshtables();
                swal('<?php echo $sl->languages('Başarısız');?>','<?php echo $sl->languages('Silme işlemi başarısız lütfen tekrar deneyiniz.');?>','error');
              }
            },'json');
          } else {
          }
        });

        return false;
      });

      $('#city').on('change', function(){
        var id = $(this).find('option:selected').val();


      });

      $(".daterange").daterangepicker();

	  $('.daterange').on('apply.daterangepicker', function(ev, picker) {
	    var date = $('.daterange').val();
		$('.daterange').attr('disabled','disabled');
		$('.daterange').val('Bekleyiniz...');
		location.href = 'guestbook.reports.php?date=' + date;
	  });

    });
  </script>
  <?php $portal->file('footer'); ?>
