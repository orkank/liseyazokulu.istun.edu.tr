<!DOCTYPE html>
<html lang="tr" class="h-100">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{%$meta.title%}</title>
  <meta name="description" content="{%$meta.desc%}" />

  <link rel="dns-prefetch" href="{%$settings.domain.static%}" />
  {%if empty($css)%}
  <link rel="shortcut icon" href="{%$settings.asset%}img/favicon.png">
  <link rel="stylesheet" href="{%$settings.asset%}css/plugins.css">
  <link rel="stylesheet" href="{%$settings.asset%}css/style.css?v=1.0.0">
  <link rel="preload" href="{%$settings.asset%}css/fonts/dm.css" as="style" onload="this.rel='stylesheet'">

  {%else%}
  <style media="all" type="text/css">
    {%$css%}
  </style>
  {%/if%}

</head>

  <body>
    <div class="content-wrapper">
      <header class="wrapper bg-soft-primary">
        <nav class="navbar navbar-expand-lg center-nav transparent position-absolute navbar-dark caret-none">
          <div class="container flex-lg-row flex-nowrap align-items-center">
            <div class="navbar-brand w-100" style="">
              <a href="{%$settings.domain.url%}" style="display:block;max-width: 450px;">
                <img class="logo-dark img-fluid" src="{%$settings.asset%}img/logo-dark.png" srcset="{%$settings.asset%}img/logo-dark@2x.png 2x" alt="" />
                <img class="logo-light img-fluid" src="{%$settings.asset%}img/logo-light.png" srcset="{%$settings.asset%}img/logo-light@2x.png 2x" alt="" />
              </a>
            </div>
            <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start">
              <div class="offcanvas-header d-lg-none">
                <h3 class="text-white fs-30 mb-0">Lise Yaz Okulu</h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body d-flex flex-column h-100">
                <ul class="navbar-nav">
                  <li class="nav-item"><a class="nav-link" href="#">Anasayfa</a></li>
                  <li class="nav-item"><a class="nav-link" href="#">Programlar</a></li>
                  <li class="nav-item"><a class="nav-link" href="#">Sıkça Sorulan Sorular</a></li>
                  <li class="nav-item"><a class="nav-link" href="#">İletişim</a></li>
                  <li class="nav-item d-flex align-items-center"><a class="btn btn-primary rounded-pill" href="/#programlar">Başvuru Formu</a></li>
                </ul>
                <!-- /.navbar-nav -->
                <div class="offcanvas-footer d-lg-none">
                  <div>
                    <a href="mailto:liseyazokulu@istun.edu.tr" class="link-inverse">liseyazokulu@istun.edu.tr</a>
                    <br /> 444 3 788 <br />
                    <nav class="nav social social-white mt-4">
                      <a href="#"><i class="uil uil-twitter"></i></a>
                      <a href="#"><i class="uil uil-facebook-f"></i></a>
                      <a href="#"><i class="uil uil-dribbble"></i></a>
                      <a href="#"><i class="uil uil-instagram"></i></a>
                      <a href="#"><i class="uil uil-youtube"></i></a>
                    </nav>
                    <!-- /.social -->
                  </div>
                </div>
            <!-- /.offcanvas-footer -->
              </div>
              <!-- /.offcanvas-body -->
            </div>
            <!-- /.navbar-collapse -->
            <div class="navbar-other w-100 d-flex ms-auto d-lg-none">
              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- <li class="nav-item"><a class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-info"><i class="uil uil-info-circle"></i></a></li> -->
                <li class="nav-item d-lg-none">
                  <button class="hamburger offcanvas-nav-btn"><span></span></button>
                </li>
              </ul>
              <!-- /.navbar-nav -->
            </div>
            <!-- /.navbar-other -->
          </div>
          <!-- /.container -->
        </nav>
        <!-- /.navbar -->
        <div class="offcanvas offcanvas-end text-inverse" id="offcanvas-info" data-bs-scroll="true">
          <div class="offcanvas-header">
            <h3 class="text-white fs-30 mb-0">İSTÜN</h3>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body pb-6">
            <div class="widget mb-8">
              <p>İstanbul Sağlık ve Teknoloji Üniversitesi Lise Yaz Okulu 2024-2025</p>
            </div>
            <!-- /.widget -->
            <div class="widget mb-8">
              <h4 class="widget-title text-white mb-3">Sütlüce Kampüsü</h4>
              <address> Sütlüce Mah. İmrahor Cad. No: 82 Beyoğlu – İstanbul  </address>
              <a href="mailto:liseyazokulu@istun.edu.tr">liseyazokulu@istun.edu.tr</a><br /> 444 3 788
            </div>
            <!-- /.widget -->
            <div class="widget mb-8">
              <h4 class="widget-title text-white mb-3">Daha Fazla Bilgi</h4>
              <ul class="list-unstyled">
                <li><a href="#">Ders Programları</a></li>
                <!-- <li><a href="#">Başvuru Formu</a></li> -->
                <li><a href="#">İletişim</a></li>
              </ul>
            </div>
            <!-- /.widget -->
            <div class="widget d-none">
              <h4 class="widget-title text-white mb-3">Takip Et</h4>
              <nav class="nav social social-white">
                <a href="https://www.instagram.com/istunedu/"><i class="uil uil-instagram"></i></a>
                <a href="https://www.facebook.com/istunliseyazokulu"><i class="uil uil-facebook-f"></i></a>
                <a href="https://www.youtube.com/@istunliseyazokulu"><i class="uil uil-youtube"></i></a>
              </nav>
              <!-- /.social -->
            </div>
            <!-- /.widget -->
          </div>
          <!-- /.offcanvas-body -->
      </div>
        <!-- /.offcanvas -->
        <div class="offcanvas offcanvas-top bg-light" id="offcanvas-search" data-bs-scroll="true">
          <div class="container d-flex flex-row py-6">
            <form class="search-form w-100">
              <input id="search-form" type="text" class="form-control" placeholder="Type keyword and hit enter">
            </form>
            <!-- /.search-form -->
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <!-- /.container -->
        </div>
        <!-- /.offcanvas -->
      </header>
      <!-- /header -->
