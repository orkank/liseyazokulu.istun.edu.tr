<!DOCTYPE html>
<html lang="<?php echo $this->sl->languages('prefix'); ?>">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- Favicon icon -->
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
  <title><?php echo $this->sl->portal_title;?></title>
  <!-- Bootstrap Core CSS -->
  <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!--This page css - Morris CSS -->
  <link href="assets/plugins/c3-master/c3.min.css" rel="stylesheet">
  <link href="assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="css/style.css" rel="stylesheet">
  <link href="css/sl.styles.css?v=.02" rel="stylesheet">
  <?php if(isset($this->self()['hash'])): ?>
  <meta name="csrf-token" content="<?php echo $this->self()['hash']; ?>">
  <?php endif; ?>
  <!-- You can change the theme colors from here -->
  <link href="css/colors/purple.css" id="theme" rel="stylesheet">
  <!-- toast CSS -->
  <link href="assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
  <!-- Daterange picker plugins css -->
  <link href="assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
  <link href="assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

  <link href="assets/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
  <link href="assets/plugins/fancybox-master/dist/jquery.fancybox.min.css" rel="stylesheet">
  <link href="assets/plugins/switchery/dist/switchery.min.css" rel="stylesheet" />
  <link href="assets/plugins/nestable/nestable.css" rel="stylesheet" type="text/css" />

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script>
    var lang_prefix = '<?php echo $this->sl->languages('prefix'); ?>';
  </script>
</head>

<body class="fix-header fix-sidebar card-no-border">
  <!-- ============================================================== -->
  <!-- Preloader - style you can find in spinners.css -->
  <!-- ============================================================== -->
  <div class="preloader">
    <svg class="circular" viewBox="25 25 50 50">
      <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
  </div>
  <!-- ============================================================== -->
  <!-- Main wrapper - style you can find in pages.scss -->
  <!-- ============================================================== -->
  <div id="main-wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar">
      <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <!-- ============================================================== -->
        <!-- Logo -->
        <!-- ============================================================== -->
        <div class="navbar-header">
          <a class="navbar-brand" href="<?php echo $this->sl->settings['portal_url'];?>">
            <!-- Logo icon -->
            <b>
              <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
              <!-- Dark Logo icon -->
              <!-- Light Logo icon -->
            </b>
            <!--End Logo icon -->
            <!-- Logo text -->
            <span>
              <!-- dark Logo text -->
              <img src="/templates/default/assets/entertech/kulucka.white.png" width="100" alt="homepage" class="dark-logo" />
              <!-- Light Logo text -->
              <img src="/templates/default/assets/entertech/kulucka.white.png" width="100" class="img-fluid light-logo" alt="" /></span> </a>
        </div>
        <!-- ============================================================== -->
        <!-- End Logo -->
        <!-- ============================================================== -->
        <div class="navbar-collapse">
          <!-- ============================================================== -->
          <!-- toggle and nav items -->
          <!-- ============================================================== -->
          <ul class="navbar-nav mr-auto mt-md-0">
            <!-- This is  -->
            <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
            <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
            <!-- ============================================================== -->
            <!-- Search -->
            <!-- ============================================================== -->
            <li class="nav-item hidden-sm-down search-box">
              <a class="nav-link hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
              <form class="app-search">
                <input type="text" class="form-control" placeholder="Anahtar kelime"> <a class="srh-btn"><i class="ti-close"></i></a> </form>
            </li>
            <!-- ====== ======================================================== -->
            <!-- Messages -->
            <!-- ============================================================== -->
            <li class="nav-item dropdown mega-dropdown"> <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-help-circle"></i></a>
              <div class="dropdown-menu scale-up-left">
                <ul class="mega-dropdown-menu row">
                  <li class="col-lg-12  m-b-30">
                    <h4 class="m-b-0">Destek Merkezi</h4>
                    <small class="text-muted m-b-20">Sorun yaşıyorsanız lütfen aşağıdaki formu kullanarak bize mesaj ile iletiniz.</small>
                    <div class="clearfix"></div>
                    <!-- Contact -->
                    <form>
                      <div class="form-group">
                        <input type="text" class="form-control" id="exampleInputname1" placeholder="İsminiz"> </div>
                      <div class="form-group">
                        <input type="email" class="form-control" placeholder="E-posta adresiniz"> </div>
                      <div class="form-group">
                        <input type="email" class="form-control" placeholder="Telefon Numaranız"> </div>
                      <div class="form-group">
                        <textarea class="form-control" id="exampleTextarea" rows="3" placeholder="Mesajınız"></textarea>
                      </div>
                      <button type="submit" class="btn btn-info">Gönder</button>
                    </form>
                  </li>

                </ul>
              </div>
            </li>
            <!-- ============================================================== -->
            <!-- End Messages -->
            <!-- ============================================================== -->
          </ul>
          <!-- ============================================================== -->
          <!-- User profile and search -->
          <!-- ============================================================== -->
          <ul class="navbar-nav my-lg-0">

            <!-- ============================================================== -->
            <!-- Profile -->
            <!-- ============================================================== -->
            <?php if($this->sl->settings['user'] !== false) { ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="assets/images/users/0.jpg" alt="" class="profile-pic" /></a>
              <div class="dropdown-menu dropdown-menu-right scale-up">
                <ul class="dropdown-user">
                  <li>
                    <div class="dw-user-box">
                      <div class="u-img">
                        <img src="assets/images/users/0.jpg" alt="user">
                      </div>
                      <div class="u-text">
                        <h4><?php echo $this->sl->user['name'];?></h4>
                        <p class="text-muted"><?php echo $this->sl->user['email'];?></p><a href="<?php echo $this->settings['portal_url'];?>user.profile.php" class="btn btn-rounded btn-danger btn-sm">Profilim</a>
                      </div>
                    </div>
                  </li>
                  <li role="separator" class="divider"></li>
                  <li><a href="<?php echo $this->sl->settings['portal_url'];?>user.profile.php"><i class="ti-user"></i> Profilim</a></li>
                  <li><a href="<?php echo $this->sl->settings['portal_url'];?>user.message.php"><i class="ti-email"></i> Mesajlarım</a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="<?php echo $this->sl->settings['portal_url'];?>user.profile.php"><i class="ti-settings"></i> Hesap Ayarlarım</a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="/portal/?logout"><i class="fa fa-power-off"></i> Çıkış</a></li>
                </ul>
              </div>
            </li>
            <?php } ?>
          </ul>
        </div>
      </nav>
    </header>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
          <ul id="sidebarnav">
            <li class="nav-small-cap">NAVIGASYON</li>

            <?php
            $modules = $this->modules();
            for($i=0;$i<sizeof($modules);$i++) {
              if(!$modules[$i]['show'])
                continue;
            ?>
            <li>
              <?php
              if(isset($modules[$i]['subs'])) {
              ?>
              <a class="has-arrow waves-effect waves-dark" href="<?php echo $modules[$i]['file'];?>" aria-expanded="false">
                <i class="<?php echo $modules[$i]['icon'];?>"></i><span class="hide-menu"><?php echo $modules[$i]['name'];?></span></a>
              <ul aria-expanded="false" class="collapse">
                <?php
                for($s=0;$s<sizeof($modules[$i]['subs']);$s++) {
                ?>
                  <li><a href="<?php echo $modules[$i]['subs'][$s][1];?>"><?php echo $modules[$i]['subs'][$s][0];?></a></li>
                <?php
                  }
                ?>
              </ul>
            </li>
            <?php } else { ?>
              <a class="waves-effect waves-dark" href="<?php echo $modules[$i]['file'];?>">
                <i class="<?php echo $modules[$i]['icon'];?>"></i><span class="hide-menu"><?php echo $modules[$i]['name'];?></span></a>
              </li>
              <?php
                }
              }
              ?>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
      <!-- Bottom points-->
      <div class="sidebar-footer">
        <!-- item--><a href="" class="link" data-toggle="tooltip" title="Ayarlar"><i class="ti-settings"></i></a>
        <!-- item--><a href="/<?php echo $this->sl->languages('prefix');?>/auth/logout" class="link" data-toggle="tooltip" title="Çıkış"><i class="mdi mdi-power"></i></a> </div>
      <!-- End Bottom points-->
    </aside>
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
