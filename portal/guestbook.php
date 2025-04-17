<?php
require "build.php";

use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

$portal->self([
    "uniq" => "93bae5c1ab5f8dee7d116bf6665be51f",
    "hash" => $sl->token("sl.guestbook"),
    "name" => "Aday Kayıt CRM",
    "link" => "guestbook.php",
]);

$success = false;

if (isset($sl->post["action"]) and $sl->post["action"] == "do") {
  $json = json_encode($sl->encode($sl->post));
  $sl->post["branch_of_interest"] = implode(
      ",",
      $sl->post["branch_of_interest"]
  );

  $phone_without_country_code = str_replace('+90', '', $sl->post['phone']);

  if(
    $sl->post["type_action"] != "update" AND
    $sl->db->QuerySingleValue("SELECT COUNT(*) FROM guestbook WHERE
    `phone` = '{$sl->post['phone']}' OR `phone` = '{$phone_without_country_code}' ") > 0
  ) {
    $msg = 'Bu numara daha önce kayıt edilmiş, lütfen eski kayıtları kontrol ediniz.';

    if($sl->get['ajax'] == true) {
      $response = ['status' => 0, 'msg' => $msg];
      die(json_encode($response));
    }

    $sl->alert(
      array(
        $sl::ALERT_WARNING,
        $msg
      )
    );
  } else {
    $json = json_encode($sl->post, JSON_UNESCAPED_UNICODE);
    $sql = "SELECT * FROM guestbook WHERE id = {$sl->post["id"]}";

    $row = $sl->db->QuerySingleRowArray($sql);

    $row["updates"] = !empty($row["updates"])
        ? json_decode($row["updates"], true)
        : [];

	echo $sl->user_session["variables"]["username"] = $sl->user_session["variables"]["username"] ?? $sl->user["name"];

    if (empty($sl->user_session["variables"]["username"])) {
      $response = [
            "status" => 3,
            "msg" => "Operatör ismi girmeden kayıt yapamazsınız.",
        ];
		echo 'fasd';
    } else {
        if ($sl->post["type_action"] != "update") {
            $sql =
                "INSERT INTO `guestbook`
        (
          `json`,
          `status`,
          `modified`,
          `published`,
          `operator`
        ) VALUES
        (
          '{$json}',
          '1',
          " .
                time() .
                ",
          " .
                time() .
                ",
          '{$sl->user_session["variables"]["username"]}'
        )
      ";

            $data = $sl->db->Query($sql);
            $id = $sl->post["id"] = $sl->db->GetLastInsertId();
        } else {
            $id = $sl->post["id"];
        }

        if (!$id) {
            $response = [
                "status" => 4,
                "msg" => "Teknik hata oluştu, tekrar deneyiniz. ",
            ];
        } else {
        if(!empty($sl->post['text']))
            $row["updates"][] = [
                "name" => $sl->user_session["variables"]["username"],
                "date" => date("Y-m-d H:i:s"),
                "text" => $sl->post['text']
            ];

            $updates = json_encode($row["updates"], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $sl->post['callback_time'] = date('Y-m-d H:i:s', strtotime($sl->post['callback_time']));

            $columns = [
                "type",
                "fullname",
                "phone",
                "city",
                "county",
                "hearus",
                "campusvisit",
                "other",
                "talep",
                "callback_time",
                "negatif",
                "baddata",
            ];

            $variables = array_map(function ($e) use ($sl) {
                $sl->post[$e] = $sl->post[$e] ?? 0;
                return "`{$e}` = '{$sl->post[$e]}'\n";
            }, $columns);

            $variables = implode(",", $variables);

            $sql = "UPDATE `guestbook` SET {$variables}, `updates` = '{$updates}' WHERE id = {$sl->post["id"]}";

            $data = $sl->db->Query($sql);

            $msg =
                $sl->post["type_action"] == "update"
                    ? "Kayıt başarıyla güncellendi."
                    : "";

          if ($data) {
                $response = ["status" => 1, "msg" => $msg];
            } else {
                $response = [
                    "status" => 2,
                    "msg" => "Teknik hata oluştu, lütfen tekrar deneyiniz",
                ];
            }
        }
    }

    if($response['status'] == 1) {
      if(!empty($sl->post['fid'])) {
        $sl->db->Query("UPDATE forms SET uniq = '1' WHERE id = {$sl->post['fid']}");
      }
    }

	exit;
    if($sl->get['ajax'] == true) {
      die(json_encode($response));
    }

    header("Location: guestbook.list.php");
    exit;
  }
}

if ($sl->post) {
    $sl->post = array_merge([
        "id" => "",
        "type" => "1",
        "fullname" => "",
        "phone" => "",
        "city" => "40",
        "county" => "445",
        "hearus" => "",
        "campusvisit" => 1,
        "other" => 1,

        "json" => "",
        "status" => "",
        "modified" => "",
        "published" => "",
    ],$sl->post);
}

if (isset($sl->get["id"]) and !empty($sl->get["id"])) {
    $sl->post = $sl->db->QuerySingleRowArray(
        "SELECT * FROM guestbook WHERE id = {$sl->get["id"]}", MYSQLI_ASSOC
    );
    $sl->post["branch_of_interest"] = explode(
        ",",
        $sl->post["branch_of_interest"]
    );
}

/*
  $sl->post = [
    'id' => '',
    'type' => '1',
    'fullname' => 'Orkan Köylü',
    'phone' => '05323633011',
    'graduated_high_school' => 'Yok',
    'city' => '40',
    'county' => '445',
    'scoretype' => '1',
    'rank' => '10',
    'branch_of_interest' => [12,13],
    'hearus' => 49,
    'campusvisit' => 1,
    'preferencefair' => 1,
    'other' => 1,

    'json' => '',
    'status' => '',
    'modified' => '',
    'published' => ''
  ];
*/
$portal->file("header");
?>
<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper page-wrapper-small">
<!-- ============================================================== -->
<!-- Container fluid  -->
<!-- ============================================================== -->
<div class="container-fluid">
   <?php include "breadcrumb.php"; ?>
   <!-- ============================================================== -->
   <!-- Start Page Content -->
   <!-- ============================================================== -->
<?php $column = "col-12 col-lg-8"; include "guestbook.form.inc.php"; ?>
                        </div>
                      <div class="col-12 col-lg-4">
                      <h2>Güncellemeler</h2>
                      <hr/>
                      <div class="" style="overflow-y:scroll;max-height:400px;">
                        <?php
                          $updates = array_reverse(json_decode($sl->post['updates'], true));
                          // print_r($updates);
                          for($i=0;$i<sizeof($updates);$i++) {
                            echo '
                            <div class="bg-light mb-3 p-2 rounded">
                              <span class="p-2 d-block mb-2">İşlem Tarihi: '.$updates[$i]['date'].'</span>
                              <span class="p-2">Operatör: '.$updates[$i]['name'].'</span>
                              <div class="p-2">'.$updates[$i]['text'].'</div>
                            </div>
                            ';
                          }
                        ?>
                        </div>
                      </div>
                    </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
</div>
<!-- ============================================================== -->
<!-- End Container fluid  -->
<!-- ============================================================== -->
<?php $portal->file("scripts"); ?>
<script type="text/javascript">
   $(document).ready(function() {
     $('.dateFilter').daterangepicker({
         locale: {
             format: 'DD/MM/YYYY'
         }
     });
    //  $('#callback_time').datepicker({
    //   format: 'yyyy/mm/dd',
    // });
   $('input#phone').on('input propertychange paste', function (e) {
      var val = $(this).val()
      var reg = /^0/gi;

      if (val.match(reg)) {
      	val = val.replace(reg, '');
      }

      reg = /-/gi;

      if (val.match(reg)) {
      	val = val.replace(reg, '');
      }

      reg = / /gi;

      if (val.match(reg)) {
      	val = val.replace(reg, '');
      }

     	$(this).val(val);
   });

     $('[data-fancybox="wcallback"]').fancybox({
     	afterClose: function(instance, current) {
         console.log('closing...');
         refreshtables();
     	}
     });

     $('#city').bind('change',function(){
       var cityCode = $('#city').find('option:selected').val();

       $.ajax({
         url: '/tr/xhr/counties',
         type: 'POST',
         data: 'cityID='+cityCode+'&action=counties',
         success: function(data) {
           var data = JSON.parse(data);
           var options = '';

           $.each(data, function(e, item) {
             options += '<option value="'+item.countyID+'">'+item.countyName+'</option>';
           });

           console.log(options);
           $('#counties').html(options);
           $('#counties').selectpicker('refresh');
         },
         error: function() {
         }
       });
     });

   });
</script>
<?php $portal->file("footer"); ?>
