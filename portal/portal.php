<?php
require('build.php');

$portal->self(
  array(
    'name' => 'Portal',
    'file' => 'portal.php'
  )
);

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


        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
      </div>
      <!-- ============================================================== -->
      <!-- End Container fluid  -->
      <!-- ============================================================== -->

<?php $portal->file('scripts'); ?>
<script>
  $(document).ready(function() {
    $('*[data-expand]').on('click',function(){
      var data = $(this).data();

      if(data['expanded'] == 'true') {

        $('#'+data['id']).attr('class',$(this).data('previous'));
        $(this).data('status','0');
        $(this).data('expanded','false');
        $(this).find('i').attr('class','mdi mdi-arrow-expand');
      } else {
        $(this).data('previous',$('#'+data['id']).attr('class'));
        $(this).data('expanded','true');
        $(this).find('i').attr('class','mdi mdi-arrow-compress');
        console.log($(this).find('i'));

        $('#'+data['id']).removeClass(function(index, className) {
            return (className.match (/(^|\s)col-\S+/g) || []).join(' ');
        });
        $('#'+data['id']).addClass('col-md-12');
      }

      return false;
    });
    /*
    $.toast({
     heading: 'Hoşgeldin Yönetici',
     text: '2 yeni bildirimiz var.',
     position: 'top-right',
     loaderBg:'#26569A',
     icon: 'info',
     hideAfter: 3000,
     stack: 6
   });
   $.toast({
    heading: 'Firma Bildirimleri',
    text: '1 Yeni başvuru var!',
    position: 'top-right',
    loaderBg:'#26569A',
    icon: 'warning',
    hideAfter: 3000,
    stack: 6
  });
  */

    /*<!-- ============================================================== -->*/
    /*<!-- Bar Chart -->*/
    /*<!-- ============================================================== -->*/
    /*
    new Chart(document.getElementById("companies"),
        {
            "type":"bar",
            "data":{"labels":["Akitf Ön Kuluçka","Mezun Ön Kuluçka","Kuluçka","Post Kuluçka","Mezun Kuluçka","Ayrılan Kuluçka"],
            "datasets":[{
                            "label":"Firma durumları dataset",
                            "data":[65,59,80,81,56,55],
                            "fill":false,
                            "backgroundColor":["rgba(255, 99, 132, 0.2)","rgba(255, 159, 64, 0.2)","rgba(255, 205, 86, 0.2)","rgba(75, 192, 192, 0.2)","rgba(54, 162, 235, 0.2)","rgba(153, 102, 255, 0.2)","rgba(201, 203, 207, 0.2)"],
                            "borderColor":["rgb(252, 75, 108)","rgb(255, 159, 64)","rgb(255, 178, 43)","rgb(38, 198, 218)","rgb(54, 162, 235)","rgb(153, 102, 255)","rgb(201, 203, 207)"],
                            "borderWidth":1}
                        ]},
            "options":{
                "scales":{"yAxes":[{"ticks":{"beginAtZero":true}}]}
            }
        });
        */

    var initialLocaleCode = '<?=$sl->languages('prefix');?>';

    $('#calendar').fullCalendar({
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay,listMonth'
      },
      locale: initialLocaleCode,
      lang: initialLocaleCode,
      defaultDate: '2019-03-02',
      buttonIcons: false, // show the prev/next text
      weekNumbers: true,
      navLinks: true, // can click day/week names to navigate views
      editable: true,
      eventLimit: true, // allow "more" link when too many events
      events: [{
          id: 9799,
          title: 'Konferans',
          start: '2019-03-11',
          url: 'http://google.com/',
          end: '2019-03-13'
        },
        {
          id: 9919,
          title: 'Firma Sözleşme Yenilemesi',
          start: '2019-03-23 10:00',
          className: 'alert-danger'
        },
        {
          id: 9939,
          title: 'Hakem oylaması',
          start: '2019-03-02 10:00',
          className: 'alert-warning'
        },

      ]
    });

  });
</script>
<?php $portal->file('footer'); ?>
