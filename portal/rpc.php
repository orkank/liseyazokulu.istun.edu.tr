<?php
require('build.php');

$sl->JSONHeaders();
$custom = '';


switch (($sl->post['action'])?$sl->post['action']:$sl->get['action']) {
  case 'images-sort':
    if(!empty($sl->post['data'])) {
      for($i=0;$i<sizeof($sl->post['data']);$i++)
        $sl->db->Query("UPDATE `images` SET `sort` = '{$i}'
          WHERE
            `id`='{$sl->post['data'][$i]}' AND (`rid`='".$sl->post['id']."' OR `rid` IS NULL)
            AND `group` = '{$sl->post['group']}'
          ");
          echo $sl->db->Error();

      $action = 1;
    } else {
      $action = 0;
    }
  break;

  case 'menu':
    $data = json_decode(stripslashes($sl->post['data']), true);

    $data = serialize($data);

    if($sl->db->Query("UPDATE `system` SET `value`='".$data."' WHERE `name`='MENU'")) {
      $action = 1;
    } else {
      $status = 0;
    }
  break;
  case 'image-update':
    if(is_numeric($sl->get['id'])) {
      if($sl->db->QuerySingleValue("SELECT COUNT(*) FROM `images` WHERE
        `id`='{$sl->get['id']}'") > 0) {

        $values = serialize(array_map('addslashes', $sl->post));

        $action = $sl->db->Query("UPDATE `images` SET
          `values` = '{$values}'
          WHERE `id`='{$sl->get['id']}'");
      }
    }
  break;
  case 'image-edit':
    if(is_numeric($sl->post['id'])) {
      if($sl->db->QuerySingleValue("SELECT COUNT(*) FROM `images` WHERE
        `id`='{$sl->post['id']}'") > 0) {
          $custom = $sl->db->QuerySingleValue("SELECT `values` FROM `images` WHERE `id`='{$sl->post['id']}'");

          if(!empty($custom))
            $custom = unserialize($custom);
          else
            $custom = '';

        $action = true;
      }
    }
  break;
  case 'image-remove':
    if(is_numeric($sl->post['image'])) {
      if($sl->db->QuerySingleValue("SELECT COUNT(*) FROM `images` WHERE
        `rid`='{$sl->post['rid']}' AND
        `group`='{$sl->post['group']}' AND
        `id`='{$sl->post['image']}'") > 0) {

          $rid = (empty($sl->post['id']))?'':"`rid` = '{$sl->post['rid']}' AND ";

        $images = unserialize(
          $sl->db->QuerySingleValue("SELECT `images` FROM `images`
          WHERE
          {$rid}
          `group` = '{$sl->post['group']}' AND
          `id`='{$sl->post['image']}'")
        );

        $keys = array_keys($images);

        for($i=0;$i<sizeof($images);$i++) {
          if(is_array($images[$keys[$i]])) {
            $_keys = array_keys($images[$keys[$i]]);

            for($j=0;$j<sizeof($images[$keys[$i]]);$j++) {
              if(!is_string($images[$keys[$i]][$_keys[$j]]))
                continue;

              $FILE = PB .DS. UP . $sl->clearUpload($images[$keys[$i]][$_keys[$j]]);

              if(is_file( $FILE ))
                unlink($FILE);
            }

            continue;
          }

          $FILE = PB .DS. UP . $sl->clearUpload($images[$keys[$i]]);

          if(is_file( $FILE ))
            unlink($FILE);
        }

        $action = $sl->db->Query("DELETE FROM `images`
        WHERE
        `rid` = '{$sl->post['rid']}' AND
        `group` = '{$sl->post['group']}' AND
        `id`='{$sl->post['image']}'");
      } else {
        $action = 0;
      }
    } else {
      $image = PB .DS. UP . $sl->clearUpload($sl->post['image']);

      if(is_file($image)) {
        $action = unlink($image);
      } else {
        $action = false;
      }

      if(!is_file($image))
        $status = 1;

      $action = true;
    }

  break;

  default:
    // code...
  break;
}

if($action) {
  $sl->alert(
    array(
      'success',
      $sl->languages('İşlem başarıyla gerçekleştirildi.'),
      $sl->languages('Başarılı')
    )
  );

  $status = 1;
} else {
  $sl->alert(
    array(
      'error',
      $sl->languages('İşlem gerçekleştirilemedi, lütfen tekrar deneyiniz.'),
      $sl->languages('Hata oluştu')
    )
  );

  $status = 0;
}

echo json_encode(
  array(
    'status' => $status,
    'alerts' => $sl->alert(false, 'get'),
    'custom' => $custom
  )
);

$sl->alert(false, 'kill');
//print_r($sl->post);
//print_r(json_decode($sl->post['data'], true));
