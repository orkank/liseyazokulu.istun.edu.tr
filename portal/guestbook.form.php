<?php
$success = false;
require('build.php');

// if(isset($sl->post['action']) AND $sl->post['action'] == 'do') {
//   $json = json_encode($sl->encode($sl->post));
//   $sl->post['branch_of_interest'] = implode(',', $sl->post['branch_of_interest']);

//   $json = json_encode($sl->post, JSON_UNESCAPED_UNICODE);
//   $sql = "SELECT * FROM guestbook WHERE id = {$sl->post['id']}";

//   $row = $sl->db->QuerySingleRowArray($sql);

//   $row['updates'] = (!empty($row['updates']))?json_decode($row['updates'],true):[];

//   if(empty($sl->user_session['variables']['username']) and false)
//     $response = ['status' => 3, 'msg' => 'Operatör ismi girmeden kayıt yapamazsınız.'];
//   else {
// 	  if($sl->post['type_action'] != 'update') {
// 	      $sql =
// 	      "INSERT INTO `guestbook`
// 	        (
// 	          `json`,
// 	          `status`,
// 	          `modified`,
// 	          `published`,
// 	          `operator`
// 	        ) VALUES
// 	        (
// 	          '{$json}',
// 	          '1',
// 	          ".time().",
// 	          ".time().",
// 	          '{$sl->user_session['variables']['username']}'
// 	        )
// 	      ";

// 	      $data = $sl->db->Query($sql);
//         echo $sl->db->Error();

// 	      $id =
// 		  $sl->post['id'] = $sl->db->GetLastInsertId();
// 	  } else {
// 	  	$id = $sl->post['id'];
// 	  }

//     if(!$id)
// 	    $response = ['status' => 4, 'msg' => 'Teknik hata oluştu, tekrar deneyiniz. '];
// 	  else {
//       $row['updates'][] = ['name' => $sl->user_session['variables']['username'], 'date' => date('Y-m-d H:i:s')];
// 		  $updates = json_encode($row['updates']);

//       $columns = [
//         'type',
//         'fullname',
//         'phone',
//         'city',
//         'county',
//         'hearus',
//         'campusvisit',
//         'other'
//       ];

//       $variables = array_map(function($e) use($sl) {
//         $sl->post[$e] = $sl->post[$e] ?? 0;
//         return "`{$e}` = '{$sl->post[$e]}'\n";
//       }, $columns);

//       $variables = implode(',', $variables);

//       $sql = "UPDATE `guestbook` SET {$variables}, `updates` = '{$updates}' WHERE id = {$sl->post['id']}";

//       $data = $sl->db->Query($sql);

//       $msg = ($sl->post['type_action'] == 'update')?'Kayıt başarıyla güncellendi.': '';

//       if($data)
//         $response = ['status' => 1, 'msg' => $msg];
//       else
//         $response = ['status' => 2, 'msg' => 'Teknik hata oluştu, lütfen tekrar deneyiniz'];
//   	}
//   }

//   if($response['status'] == 1) {
//     if(!empty($sl->post['fid'])) {
//       $sl->db->Query("UPDATE forms SET uniq = '1' WHERE id = {$sl->post['fid']}");
//     }
//   }

//   die(json_encode($response));
// }

if(!empty($sl->get['fid'])) {
  $data = $sl->db->QuerySingleRowArray("SELECT * FROM forms WHERE id = {$sl->get['fid']}");
  $data = json_decode($data['json'], true);
  $sl->post = [
    'phone' => $data['phone'],
    'email' => $data['email'],
    'message' => $data['message'],
    'fullname' => $data['fullname'],
    'type' => 4
  ];
}

if($sl->post)
  $sl->post = array_merge(
    [
      'id' => '',
      'type' => '1',
      'fullname' => '',
      'phone' => '',
      'city' => '40',
      'county' => '445',
      'hearus' => '',
      'campusvisit' => 0,
      'other' => 0,

      'json' => '',
      'status' => '',
      'modified' => '',
      'published' => ''
    ],
    $sl->post
  );

if(isset($sl->get['id']) AND !empty($sl->get['id'])) {
	$sl->post = $sl->db->QuerySingleRowArray("SELECT * FROM guestbook WHERE id = {$sl->get['id']}");
	$sl->post['branch_of_interest'] = explode(',',$sl->post['branch_of_interest']);
}
?>
<div style="max-width:800px;  background-color: transparent !important;
  box-shadow: none;">
  <?php $column = "col-12"; include "guestbook.form.inc.php"; ?>
</div>