<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles d-none">
  <div class="col-md-5 col-8 align-self-center">
    <h3 class="text-themecolor"><?=$portal->module['name'];?></h3>
    <ol class="breadcrumb">
      <?php
      for($i=0;$i<sizeof($portal->breadcrumbs);$i++) {
      ?>
      <li class="breadcrumb-item<?=($portal->breadcrumbs[$i]['link'] == $portal->module['link'])?' active':'';?>"><a href="<?=$portal->breadcrumbs[$i]['link'];?>"><?=$portal->breadcrumbs[$i]['name'];?></a></li>
      <?php } ?>
    </ol>
  </div>

</div>
<script>
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    </script>

<div class="row mt-2">
  <div class="col-md-12">

<?php
//$sl->alert(false, 'read');
if(!empty($sl->alert_variables)) {
  for($i=0;$i<sizeof($sl->alert_variables);$i++)
    echo '<div class="alert alert-'.$sl->alert_variables[$i][0].'">'.$sl->alert_variables[$i][1].'</div>';
}
$sl->alert(false, 'kill');
?>
  </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
