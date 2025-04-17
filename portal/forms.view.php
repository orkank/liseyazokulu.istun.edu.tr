<?php
require('build.php');

use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;
use Dompdf\Dompdf;

$portal->self(
  array(
    'uniq' => '93bae5c1ab5f8dee7d116bf6665be51f',
    'hash' => $sl->token('sl.forms'),
    'name' => 'Form Görüntüle',
    'link' => 'companies.forms.view.php'
  )
);

if(!empty($sl->get['id'])) {
  $form = $sl->db->QuerySingleRowArray("SELECT * FROM `forms` WHERE `id`='{$sl->get['id']}'", MYSQLI_ASSOC);
  $form['variables'] = (json_decode($form['variables'], true));
  $form['json'] = (json_decode($form['json'], true));
  $options['cities'] = $sl->db->QueryArray("SELECT * FROM `address_cities` WHERE `countryID`='212'", MYSQLI_ASSOC, 'cityID');
  $sl->db->Query("UPDATE `forms` SET `view` = 1 WHERE `id` = {$sl->get['id']}");
} else {
}
if(isset($sl->get['file']) AND !empty($sl->get['file'])) {
  $file = $sl->get['file'];
  $folder = explode(',', $file);
  $name = base64_decode(explode('__', explode(',', $file)[1])[0]);

  header('Content-Disposition: attachment; filename="'.$name.'"');

  #echo $_SERVER['DOCUMENT_ROOT'] . '/userFiles/' . $folder[0] . '/' . $folder[1];

  readfile($_SERVER['DOCUMENT_ROOT'] . '/userFiles/' . $folder[0] . '/' . $folder[1]);

  exit;
}

if(isset($sl->get['action']) AND $sl->get['action'] == 'pdf') {
  $sl->smarty->assign('form', $form);

  $logo = PB .DS. 'templates/default/assets/images/logotype.jpg';
  $type = pathinfo($logo, PATHINFO_EXTENSION);
  $data = file_get_contents($logo);
  $logotype = 'data:image/' . $type . ';base64,' . base64_encode($data);
  $sl->smarty->assign('logotype', $logotype);

  switch ($form['cluster']) {
    case 1:
      $file = 'kulucka.html';
    break;
    case 2:
      $file = 'boostup.html';
    break;
  }
  $html = $sl->smarty->fetch(PB .DS. 'templates/default/pdf.templates/' . $file);
  //$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
  if(false) {
    echo $html;
    exit;
  }

  $dompdf = new DOMPDF();

  $dompdf->set_option('defaultMediaType', 'all');
  $dompdf->set_option('isFontSubsettingEnabled', true);

  $dompdf->loadHtml($html, 'UTF-8');
  $dompdf->set_paper('A4');
  $dompdf->render();
  $dompdf->stream("sample.pdf", array("Attachment"=>0));

  exit;
}

if(isset($sl->post['action']) AND $sl->post['action'] == 'status') {
 if(is_numeric($sl->post['id'])) {
   $c = $sl->db->Query("UPDATE `forms` SET
   `status`='{$sl->post['status']}',
   `modified`='".time()."'
   WHERE `id`='".$sl->post['id']."'");
 }

 if($c) {
   echo json_encode(array('status' => 'ok'));
 } else {
   echo json_encode(array('status' => 'fail'));
 }

 exit;
}
$skip = array('g-recaptcha-response','agree','g-recaptcha-responseV2','action','send_to','type');

$replace = array(
  'name' => 'İsim',
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
  'cinsiyeti' => 'Cinsiyet',
  'mezuniyet_yili' => 'Mezuniyet Yılı',
  'Bu00f6lu00fcm' => 'Bölüm',
  'Faku00fclte' => 'Fakülte',
  'Adu0131nu0131z_Soyadu0131nu0131z' => 'Adınız Soyadınız',
  'Eser_Tu00fcru00fc' => 'Eser Türü',
  'Eser_Adu0131' => 'Eser Adı',
  'Yayu0131nevi' => 'Yayın Evi',
  'Yer_Numarasu0131' => 'Yer Numarası',
  'Ders_Adu0131' => 'Ders Adı',
  'Rezerv_Bau015flangu0131u00e7_Tarihi' => 'Rezerv Başlangıç Tarihi',
  'Rezerv_Bitiu015f_Tarihi' => 'Rezerv Bitiş Tarihi',
  'E-posta Adresi' => 'E-posta Adresi',
  'okulu' => 'Okulu',
  'message' => 'Mesaj',
  'fullname' => 'Tam Adı',
  'phone' => 'Telefon',
  'unvani' => 'Ünvanı',
  'veliAd' => 'Veli Adı',
  'veliSoyad' => 'Veli Soyadı',
  'veliTelefon' => 'Veli Telefon',
  'veliEmail' => 'Veli E-posta',
  'veliTc' => 'Veli T.C.',
  'ogrenciTcKimlikNo' => 'Öğrenci T.C.',
  'ogrenciAd' => 'Öğrenci Adı',
  'ogrenciSoyad' => 'Öğrenci Soyadı',
  'ogrenciTelefon' => 'Öğrenci Telefon',
  'ogrenciEmail' => 'Öğrenci E-posta',
  'devamEdilenSinif' => 'Devam Edilen Sınıf',
  'il' => 'İl',
  'ilce' => 'İlçe',
  'adres' => 'Adres',
  'bank_response' => 'Cevap',
  'agreement' => 'Sözleşme Kabulü',
  'birthMonth' => 'Doğum Ayı',
  'birthYear' => 'Doğum Yılı',
  'birthDay' => 'Doğum Günü',
  'agreement_ticari_iletisim' => 'Ticari İletişim İzni',
);

$portal->file('header');
?>
<style>
.subject {
  background: #f0f0f0;
  padding: 10px;
  display: block;
}
textarea {
  line-height: 40px;
}
</style>
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

    <?php

    ?>
    <div class="row" style="max-width: 1000px;">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body" id="content-for-load">
            <h4 class="card-title">
            </h4>

            <div class="row">
              <?php
                $name = 'Banka Cevap';
                $response = (!empty($form['bank_response'])) ? $form['bank_response'] : $form['response'];
                $values = json_decode($response, true);
                if(!empty($values)) {
              ?>
              <div class="col-12 col-md-12">
                <div class="bg-light-success p-2 rounded m-2 d-flex">
                  <div class="p-3">
                    <strong class="d-block mb-2">Sipariş Numarası</strong>
                    <p><?php echo $values['OrderId']; ?></p>
                  </div>
                  <div class="p-3">
                    <strong class="d-block mb-2">Toplam Tutar</strong>
                    <p>₺ <?php echo $values['PurchAmount']; ?></p>
                  </div>
                  <div class="p-3">
                    <strong class="d-block mb-2">Tarih</strong>
                    <p><?php echo date('Y-m-d H:i:s', ($form['published'])); ?></p>
                  </div>
                </div>
              </div>
              <?php } ?>
              <?php
              if(!empty($form['json']['files']) OR !empty($form['json']['file'])) {
              ?>
              <div class="col-12">
                <hr>

                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Dosyalar</h4>
                    <div class="row">
                    <?php
                    if(!empty($form['json']['file'])) {
					    $name = base64_decode(explode('__', explode(',', $form['json']['file'])[1])[0]);

                      echo "
                      <div class=\"col-12 col-md-6\">
                        <p><strong>Dosya {$i}:</strong> <a href=\"forms.view.php?file={$form['json']['file']}\">{$name}</a></p>
                      </div>
                      ";
                    }
                    ?>

                    <?php
                    for($i=0;$i<sizeof($form['json']['files']);$i++) {
					    $name = base64_decode(explode('__', explode(',', $form['json']['files'][$i])[1])[0]);

                      echo "
                      <div class=\"col-12 col-md-6\">
                        <p><strong>Dosya {$i}:</strong> <a href=\"forms.view.php?file={$form['json']['files'][$i]}\">{$name}</a></p>
                      </div>
                      ";
                    }
                    ?>
                  </div>
                  </div>
                </div>

              </div>
              <?php } ?>
              <?php
              if(!empty($form['json'])) {
                $keys = array_keys($form['json']);
                for($i=0;$i<sizeof($form['json']);$i++) {
                  if(in_array($keys[$i], $skip) OR is_array($form['json'][$keys[$i]])) {
                    if($keys[$i] == 'program_id') {
                      $name = 'Program';
                      $values = implode(',', $form['json'][$keys[$i]]);
                      $values = $sl->db->QueryArray("SELECT id,`name{tr}` AS `name` FROM contents WHERE id IN ({$values}) ");

                      foreach($values as $key =>  $value) {
                        $values[$key]['price'] = $sl->db->QuerySingleValue("SELECT value AS price FROM content_nodes WHERE `key` = 'price{tr}' AND cid = {$value['id']} ");
                      }
                      $prices = array_column($values, 'price');

                      $values = array_map(function($e) {
                        return $e['name'];
                      },$values);
                      $values = implode('<br>', $values);
                      for($j=0;$j<sizeof($form['json'][$keys[$i]]);$j++) {
              ?>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="bg-light-warning p-2 rounded m-2">
                  <strong class="d-block mb-2"><?php echo $name; ?></strong>
                  <p><?php echo $values; ?></p>
                </div>
              </div>
              <?php
                      }
                    }

          	  		  continue;
                  }

                  $name = (isset($replace[$keys[$i]])) ? $replace[$keys[$i]] : $keys[$i];
              ?>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="bg-light-warning p-2 rounded m-2">
                  <strong class="d-block mb-2"><?php echo $name; ?></strong>
                  <p><?php echo $form['json'][$keys[$i]]; ?></p>
                </div>
              </div>
              <?php
                }
              }
              ?>
              <?php
                $name = 'Banka Cevap';
                $response = (!empty($form['bank_response'])) ? $form['bank_response'] : $form['response'];
                $_values = json_decode($response, true);

              ?>
                <div class="col-12 col-md-12">
                <div class="bg-light-danger p-2 rounded m-2 d-flex">
                  <div class="p-3">
                    <strong class="d-block mb-2">Toplam Tutar (Ödenmesi Gereken)</strong>
                    <p>₺ <?php echo array_sum(str_replace([',','.'], '', $prices)); ?></p>
                  </div>
                </div>
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
  <!-- ============================================================== -->
  <!-- End Container fluid  -->
  <!-- ============================================================== -->
  <?php $portal->file('scripts'); ?>

  <script type="text/javascript">
    $(document).ready(function() {

    });
  </script>
  <?php $portal->file('footer'); ?>
