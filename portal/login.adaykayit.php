<?php
$checkAuth = false;
require('./build.php');

if($sl->auth()) {
  header("Location: index.php");
  exit;
}
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
  <link rel="icon" type="image/png" sizes="16x16" href="<?=$sl->settings['portal_url'];?>assets/images/favicon.png">
  <title><?=$sl->portal_title;?></title>
  <!-- Bootstrap Core CSS -->
  <link href="<?=$sl->settings['portal_url'];?>assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?=$sl->settings['portal_url'];?>css/style.css" rel="stylesheet">
  <!-- You can change the theme colors from here -->
  <link href="<?=$sl->settings['portal_url'];?>css/colors/blue.css" id="theme" rel="stylesheet">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
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
  <section id="wrapper">
    <div class="login-register" style="padding:0;padding-top:100px;background-image:url(<?=$sl->settings['portal_url'];?>assets/images/background/logisn-register.jpg);">
      <div class="text-center m-b-40"> <img src="https://www.istun.edu.tr/templates/default/assets/images/istun.logo.red.gray.png" alt="<?=$sl->portal_title;?>"> </div>

      <div class="login-box card">
        <div class="card-body">
          <form class="form-horizontal form-material" id="loginform" action="/portal/?auth" method="post">
            <input type="hidden" name="redirectURI" value="<?=rawurlencode($sl->settings['url'].$sl->settings['portal_url']);?>">

            <h3 class="box-title m-b-20">Giriş</h3>
            <?php
            if(!empty($sl->alert_variables)) {
              for($i=0;$i<sizeof($sl->alert_variables);$i++)
                echo '<div class="alert '.$sl->alert_variables[$i][0].'">'.$sl->alert_variables[$i][1].'</div>';
            }
            $sl->alert(false,'kill');
            ?>

            <div class="form-group">
              <div class="col-xs-12">
             <input class="form-control" type="hidden" name="email" required="" value="crm@crm" placeholder=""> </div>
                <input class="form-control" type="text" name="username" required="" placeholder="Operatör Adı"> </div>

            <div class="form-group">
              <div class="col-xs-12">
                <input class="form-control" type="password" name="password" required="" placeholder="Şifreniz"> </div>
            </div>
            <div id="captcha"></div>

            <div class="form-group text-center m-t-20">
              <div class="col-xs-12">
                <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit" name="action" value="do">Giriş</button>
              </div>
            </div>
            <div class="row" style="display:none;">
              <div class="col-xs-12 col-sm-12 col-md-12 m-t-10 text-center">
                <div class="social">
                  <button class="btn btn-facebook" data-toggle="tooltip" title="Login with Facebook"> <i aria-hidden="true" class="fab fa-facebook-f"></i> </button>
                  <button class="btn btn-googleplus" data-toggle="tooltip" title="Login with Google"> <i aria-hidden="true" class="fab fa-google-plus-g"></i> </button>
                </div>
              </div>
            </div>

          </form>
          <form class="form-horizontal" id="recoverform" action="index.html">
            <div class="form-group ">
              <div class="col-xs-12">
                <h3>Şifremi Unuttum</h3>
                <p class="text-muted">Lütfen kayıtlı e-posta adresinizi giriniz şifre sıfırlama bilgileri e-posta adresinize gönderilecektir! </p>
              </div>
            </div>
            <div class="form-group ">
              <div class="col-xs-12">
                <input class="form-control" type="text" required="" placeholder="E-posta adresiniz"> </div>
            </div>

            <div class="form-group text-center m-t-20">
              <div class="col-xs-12">
                <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Sıfırla</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
  <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer>
  </script>
  <script>
    var onloadCallback = function() {
      grecaptcha.render('captcha', {
        'sitekey' : '<?php echo $sl->settings['gcaptchaV2']['key'] ?>'
      });
    };
  </script>

  <!-- ============================================================== -->
  <!-- End Wrapper -->
  <!-- ============================================================== -->
  <!-- ============================================================== -->
  <!-- All Jquery -->
  <!-- ============================================================== -->
  <script src="<?=$sl->settings['portal_url'];?>assets/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap tether Core JavaScript -->
  <script src="<?=$sl->settings['portal_url'];?>assets/plugins/popper/popper.min.js"></script>
  <script src="<?=$sl->settings['portal_url'];?>assets/plugins/bootstrap/js/bootstrap.min.js"></script>
  <!-- slimscrollbar scrollbar JavaScript -->
  <script src="<?=$sl->settings['portal_url'];?>js/jquery.slimscroll.js"></script>
  <!--Wave Effects -->
  <script src="<?=$sl->settings['portal_url'];?>js/waves.js"></script>
  <!--Menu sidebar -->
  <script src="js/sidebarmenu.js"></script>
  <!--stickey kit -->
  <script src="<?=$sl->settings['portal_url'];?>assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
  <script src="<?=$sl->settings['portal_url'];?>assets/plugins/sparkline/jquery.sparkline.min.js"></script>
  <!--Custom JavaScript -->
  <script src="js/custom.min.js"></script>
  <!-- ============================================================== -->
  <!-- Style switcher -->
  <!-- ============================================================== -->
  <script src="<?=$sl->settings['portal_url'];?>assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>

</body>

</html>
