  </div>
  <!-- /.content-wrapper -->
  {%include 'checkout.modal.tpl'%}

  <footer class="bg-light">
    <div class="container py-13 py-md-15">
      <div class="row gy-6 gy-lg-0">
        <div class="col-md-4 col-lg-3">
          <div class="widget">
            <!-- <img class="mb-4" src="{%$settings.asset%}img/logo.png?v=.02" style="max-width: 200px;" srcset="{%$settings.asset%}img/logo@2x?v=.02.png 2x" alt="" /> -->
            <p class="mb-4">© <script> document.write(new Date().getUTCFullYear()); </script> İstanbul Sağlık ve Teknoloji Üniversitesi. <br class="d-none d-lg-block" />Tüm hakları saklıdır.</p>
            <nav class="nav social">
              <a href="#"><i class="uil uil-twitter"></i></a>
              <a href="#"><i class="uil uil-facebook-f"></i></a>
              <a href="#"><i class="uil uil-dribbble"></i></a>
              <a href="#"><i class="uil uil-instagram"></i></a>
              <a href="#"><i class="uil uil-youtube"></i></a>
            </nav>
            <!-- /.social -->
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
        <div class="col-md-4 col-lg-3">
          <div class="widget">
            <h4 class="widget-title  mb-3">Bize Ulaşın</h4>
            <address class="pe-xl-15 pe-xxl-17">Sütlüce Mah. İmrahor Cad. No: 82 Beyoğlu – İstanbul</address>
            <a href="mailto:liseyazokulu@istun.edu.tr" class="link-body">liseyazokulu@istun.edu.tr</a><br /> 444 3 788
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
        <div class="col-md-4 col-lg-3">
          <div class="widget">
            <h4 class="widget-title  mb-3">Daha Fazla Bilgi</h4>
            <ul class="list-unstyled text-reset mb-0">
              <li><a href="https://www.istun.edu.tr">İSTÜN</a></li>
              <li><a href="https://liseyazokulu.istun.edu.tr/tr/ticari-elektronik-ileti-onay-formu-2670"></a></li>
              <li><a href="https://liseyazokulu.istun.edu.tr/tr/aydinlatma-metni-2669"></a></li>
            </ul>
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
        <div class="col-md-12 col-lg-3 d-none">
          <div class="widget">
            <h4 class="widget-title mb-3">Bültenimize Kayıt Olun</h4>
            <p class="mb-5">Bültenimize kayıt olarak güncellemelerimizi ve fırsatlarımızı alın.</p>
            <div class="newsletter-wrapper">
              <!-- Begin Mailchimp Signup Form -->
              <div id="mc_embed_signup2">
                <form action="#" method="post" id="mc-embedded-subscribe-form2" name="mc-embedded-subscribe-form" class="validate " target="_blank" novalidate>
                  <div id="mc_embed_signup_scroll2">
                    <div class="mc-field-group input-group form-floating">
                      <input type="email" value="" name="EMAIL" class="required email form-control" placeholder="Email Adresinizi Giriniz" id="mce-EMAIL2">
                      <label for="mce-EMAIL2">Email Adresiniz</label>
                      <input type="submit" value="Kayıt Ol" name="subscribe" id="mc-embedded-subscribe2" class="btn btn-primary ">
                    </div>
                    <div id="mce-responses2" class="clear">
                      <div class="response" id="mce-error-response2" style="display:none"></div>
                      <div class="response" id="mce-success-response2" style="display:none"></div>
                    </div> <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_ddc180777a163e0f9f66ee014_4b1bcfa0bc" tabindex="-1" value=""></div>
                    <div class="clear"></div>
                  </div>
                </form>
              </div>
              <!--End mc_embed_signup-->
            </div>
            <!-- /.newsletter-wrapper -->
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
      </div>
      <!--/.row -->
    </div>
    <!-- /.container -->
  </footer>
  <div class="progress-wrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
      <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
    </svg>
  </div>
  <script src="{%$settings.asset%}js/plugins.js?v=0.0.2"></script>
  <script src="{%$settings.asset%}js/theme.js?v=0.0.9"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      theme.checkExistingPrograms({%$programs_ids%});
      Fancybox.bind('[data-fancybox]');
    });
  </script>

</body>

</html>
