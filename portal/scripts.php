
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="assets/plugins/sweetalert/sweetalert.min.js"></script>

<script src="assets/plugins/moment/moment.js"></script>
<script src="assets/plugins/popper/popper.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="js/jquery.slimscroll.js"></script>
<!--Wave Effects -->
<script src="js/waves.js"></script>
<script src="js/autosize.min.js"></script>
<!--Menu sidebar -->
<script src="js/sidebarmenu.js"></script>
<!--stickey kit -->
<script src="assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>

<script src="js/custom.js"></script>
<!-- ============================================================== -->
<!-- This page plugins -->
<!-- ============================================================== -->
<script src="assets/plugins/bootstrap-treeview-master/dist/bootstrap-treeview.min.js"></script>

<!--c3 JavaScript -->
<script src="assets/plugins/d3/d3.min.js"></script>
<script src="assets/plugins/c3-master/c3.min.js"></script>

<!-- wysuhtml5 Plugin JavaScript -->
<script src="assets/plugins/html5-editor/wysihtml5-0.3.0.js"></script>
<script src="assets/plugins/html5-editor/bootstrap-wysihtml5.js"></script>

<link rel="stylesheet" href="assets/plugins/html5-editor/bootstrap-wysihtml5.css">

<script type="text/javascript" src="js/pdfmake.min.js"></script>
<script type="text/javascript" src="js/vfs_fonts.js"></script>
<script type="text/javascript" src="js/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/datatables.min.css">
<script src="assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>

<script src="js/tinymce.js"></script>
<script src="assets/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<!-- ============================================================== -->
<!-- General -->
<!-- ============================================================== -->
<script src="js/dashboard.js"></script>

<script src="assets/plugins/fancybox-master/dist/jquery.fancybox.min.js"></script>
<script src="assets/plugins/pluploader/plupload.full.min.js"></script>
<script src="assets/plugins/switchery/dist/switchery.min.js"></script>
<!--
<script type="text/javascript" src="//code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css">
-->
<script src="js/sl.nestable.js"></script>

<script src="assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>
<!-- Clock Plugin JavaScript -->
<script src="assets/plugins/clockpicker/dist/jquery-clockpicker.min.js"></script>
<!-- Date Picker Plugin JavaScript -->
<script src="assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<!-- Date range Plugin JavaScript -->
<script src="assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="js/html5sortable.min.js"></script>

<!-- Languages -->
<script src="lang/<?=$this->sl->languages('prefix');?>.bootstrap-wysihtml5.js"></script>
<script src="lang/<?=$this->sl->languages('prefix');?>.calendar.js"></script>
<script src="lang/<?=$this->sl->languages('prefix');?>.bootstrap-wysihtml5.js"></script>
<script src="lang/<?=$this->sl->languages('prefix');?>.tinymce.js"></script>
<script src="lang/<?=$this->sl->languages('prefix');?>.bootstrap-datepicker.min.js"></script>

<script src="js/sl.scripts.js" type="text/javascript"></script>

<!--
<div style="display: none;" id="html5editoradvanced">

  <div class="btn-group m-t-20" role="group" aria-label="Action buttons">
      <button type="button" data-advanced-editor-save="" class="btn btn-info"> <i class="fas fa-save"></i> Kaydet </button>
      <button type="button" data-advanced-editor-cancel class="btn btn-info"> <i class="fal fa-window-close"></i> Vazgeç </button>
  </div>
</div>
-->
<!-- sample modal content -->
<div id="html5editoradvanced" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="vcenter" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="vcenter"><?=$this->sl->languages('Gelişmiş Editör');?></h4>
            </div>
            <div class="modal-body">
              <textarea id="floateditor"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default waves-effect" data-advanced-editor-dismiss data-dismiss="modal"><?=$this->sl->languages('Vazgeç');?></button>
              <button type="button" data-advanced-editor-save class="btn btn-danger waves-effect waves-light"><?=$this->sl->languages('Kaydet');?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script type="text/jscript">
var conf = [];

conf["uniq"]        = "<?=$this->self()['uniq'];?>";
conf["prefix"]      = "<?=$this->sl->languages('prefix');?>";
conf["portal_url"]  = "<?=$this->sl->settings['portal_url'];?>";

let sl = new SL(conf);

$(document).ready(function() {
  sl.langs();
  var clipboard = new ClipboardJS('.copy');

  <?php
  if(isset($this->sl->scripts[1]))
    echo implode("\n", $this->sl->scripts[1]);
  ?>
  //$('.textarea_editor').wysihtml5({locale: "<?=$this->sl->languages('prefix');?>"});

  autosize(document.querySelectorAll('textarea'));
});
<?php
  if(isset($this->sl->scripts[0]))
  echo implode("\n", $this->sl->scripts[0]);
?>
</script>
