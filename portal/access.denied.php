<?php
include('construction.php');

$page = array(
  'name' => 'Erişim engellendi',
  'file' => 'access.denied.php'
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- Favicon icon -->
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
  <title>Yıldız Teknik Üniversitesi BAP Takip Sistemi</title>
  <!-- Bootstrap Core CSS -->
  <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- chartist CSS -->
  <link href="assets/plugins/chartist-js/dist/chartist.min.css" rel="stylesheet">
  <link href="assets/plugins/chartist-js/dist/chartist-init.css" rel="stylesheet">
  <link href="assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css" rel="stylesheet">
  <link href="assets/plugins/css-chart/css-chart.css" rel="stylesheet">
  <link href="assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet">
  <link href="assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
  <link href="assets/plugins/datatables/media/css/dataTables.bootstrap4.css" rel="stylesheet">
  <link href="assets/plugins/sweetalert/sweetalert.css" rel="stylesheet" type="text/css">
  <link href="assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
  <link href="assets/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">

  <!--This page css - Morris CSS -->
  <link href="assets/plugins/c3-master/c3.min.css" rel="stylesheet">
  <!-- Vector CSS -->
  <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
  <!-- Custom CSS -->
  <link href="css/style.css" rel="stylesheet">
  <link href="css/custom.css" rel="stylesheet">
  <!-- You can change the theme colors from here -->
  <link href="css/colors/ytu.colors.css" id="theme" rel="stylesheet">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="fix-header fix-sidebar card-no-border">
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <section id="wrapper" class="error-page">
        <div class="error-box">
            <div class="error-body text-center">
                <h1 class="text-info">Hata</h1>
                <h3 class="text-uppercase">Erişim engellendi!</h3>
                <p class="text-muted m-t-30 m-b-30">DAHA FAZLA BİLGİ İÇİN LÜTFEN YÖNETİCİNİZE BAŞVURUNUZ</p>
                <a href="index.php" class="btn btn-info btn-rounded waves-effect waves-light m-b-40">Panele geri dön</a> </div>
            <footer class="footer text-center"> © 2018 YTÜ Rektörlük.</footer>
        </div>
    </section>

    <?php
    include('scripts.php');
    ?>
</body>

</html>
