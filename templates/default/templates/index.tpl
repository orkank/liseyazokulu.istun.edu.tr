{%include file='_partials/header.tpl' %}
<section class="wrapper bg-dark">
  <div class="swiper-container swiper-thumbs-container swiper-fullscreen nav-dark" data-margin="0"
    data-autoplay="true" data-autoplaytime="7000" data-nav="true" data-dots="false" data-items="1" data-thumbs="true">
    <div class="swiper">
      <div class="swiper-wrapper">
        <div class="swiper-slide bg-image" data-image-src="/uploads/sliders/slide01.jpg"></div>
      </div>
      <!--/.swiper-wrapper -->
    </div>
    <!-- /.swiper -->

  </div>
  <!-- /.swiper-container -->
</section>
<!-- /section -->

  <div class="content-wrapper">
    <!--
    Ders Programları
    -->
    <section class="wrapper bg-light pt-20" id="programlar">
      <div class="container py-14 py-md-16 pb-md-10">
        <div class="row align-items-stretch justify-content-center gx-md-5 gy-5 mt-n18 mt-md-n21 mb-14 mb-md-17">
          {%foreach from=$programs item=program%}
            <div class="col-md-6 col-xl-3">
              <div class="card card-custom shadow-lg card-border-bottom overflow-hidden border-soft-primary position-relative h-100">
                {%if isset($program['nodes']['page_video']) || isset($program['page_video']['image']) %}
                  <div class="card-media position-relative">
                    {%if !empty($program['nodes']['page_image'][0]) && !empty($program['nodes']['page_video'][0])%}
                      <img src="{%$program['nodes']['page_image'][0]%}" class="w-100" style="object-fit: cover;height:230px;" alt="{%$program['name']%}">
                      <video class="w-100 position-absolute top-0 start-0 transition-opacity"
                             style="object-fit: cover;height:230px;opacity: 0;transition: opacity 0.5s ease-in-out;"
                             autoplay muted playsinline loop
                             onmouseover="this.style.opacity=1"
                             onmouseout="this.style.opacity=0">
                        <source src="{%$program['nodes']['page_video'][0]%}" type="video/mp4">
                      </video>
                    {%elseif !empty($program['nodes']['page_video'][0])%}
                      <video class="w-100" style="object-fit: cover;height:230px;" autoplay muted playsinline loop>
                        <source src="{%$program['nodes']['page_video'][0]%}" type="video/mp4">
                      </video>
                    {%elseif !empty($program['nodes']['page_image'][0])%}
                      <img src="{%$program['nodes']['page_image'][0]%}" class="w-100" style="object-fit: cover;height:230px;" alt="{%$program['name']%}">
                    {%/if%}
                    <div class="position-absolute w-100" style="height: 150px !important;bottom: 0;pointer-events: none;background: linear-gradient(to bottom, transparent 0%, white 100%);"></div>
                  </div>
                {%/if%}
                <div class="card-body p-4 d-flex flex-column">
                  <h4 class="flex-grow-0" style="height: 87px;">{%$program['name']%}</h4>
                  {%if isset($program['nodes']['faculty']) %}
                  <span class="d-block text-pink fw-bold fs-14"><i class="uil uil-university"></i> {%$program['nodes']['faculty']%}</span>
                  {%/if%}
                  <p class="mb-4 flex-grow-1 content">
                    <!-- {%$program['content']%} -->
                    <div class="d-flex flex-row flex-wrap gap-2">
                      {%if isset($program['nodes']['date'])%}
                        <div class="flex-grow-1 w-100">
                          <span class="badge text-primary bg-soft-primary"><i class="uil uil-calendar-alt"></i> {%$program['nodes']['date']%}</span>
                        </div>
                      {%/if%}

                      {%if isset($program['nodes']['price'])%}
                        <div>
                          <span class="badge bg-pale-pink text-pink"><i class="uil uil-money-bill"></i> {%$program['nodes']['price']%}</span>
                        </div>
                      {%/if%}
                      {%if isset($program['nodes']['quota'])%}
                        <div>
                          <span class="badge text-dark bg-soft-gray"><i class="uil uil-users-alt"></i> {%$program['nodes']['quota']%} Kontenjan</span>
                        </div>
                      {%/if%}
                    </div>
                  </p>

                  <div class="d-flex flex-row justify-content-between align-items-center">
                    <!-- <a href="javascript:void(0);" data-program="{%$program['id']%}" class="more hover link-primary align-self-center">Detaylı Bilgi</a> -->

                    {%if $options.basvuru%}
                      <button class="btn btn-sm btn-outline-primary rounded-pill align-self-center"
                        data-date-start="{%$program['nodes']['start_date']%}"
                        data-date-end="{%$program['nodes']['end_date']%}"
                        data-add-program="{%$program['id']%}"
                        ><i class="uil uil-plus"></i> Listeme Ekle</button>
                    {%/if%}
                  </div>
                </div>
              </div>
            </div>
            <!--/column -->
          {%/foreach%}
        </div>
        <!--/.row -->

        <div class="row gx-lg-8 gx-xl-12 gy-10 mb-14 mb-md-5 align-items-center">
          <div class="col-lg-7">
            <figure><img class="w-auto" src="{%$settings.asset%}img/illustrations/i8.png" srcset="{%$settings.asset%}img/illustrations/i8@2x.png 2x" alt="" /></figure>
          </div>
          <!--/column -->
          <div class="col-lg-5">
            <h3 class="display-4 mb-7">Lise Kış Okulu: Geleceğinize Giden Üç Adım</h3>
            <div class="d-flex flex-row mb-6">
              <div>
                <span class="icon btn btn-circle btn-soft-primary pe-none me-5"><span class="number fs-18">1</span></span>
              </div>
              <div>
                <h4 class="mb-1">İlham Veren Fikirler</h4>
                <p class="mb-0">Bilim, teknoloji ve sanatla dolu etkinlikler sayesinde yeni fikirler keşfedin.</p>
              </div>
            </div>
            <div class="d-flex flex-row mb-6">
              <div>
                <span class="icon btn btn-circle btn-soft-primary pe-none me-5"><span class="number fs-18">2</span></span>
              </div>
              <div>
                <h4 class="mb-1">Deneyim ve Keşif</h4>
                <p class="mb-0">Laboratuvar çalışmaları, uygulamalı atölyeler ve akademik deneyimlerle kendinizi geliştirin.</p>
              </div>
            </div>
            <div class="d-flex flex-row">
              <div>
                <span class="icon btn btn-circle btn-soft-primary pe-none me-5"><span class="number fs-18">3</span></span>
              </div>
              <div>
                <h4 class="mb-1">Başarıya İlk Adım</h4>
                <p class="mb-0">Kazandığınız bilgi ve becerilerle geleceğinizde fark yaratacak bir başlangıç yapın.</p>
              </div>
            </div>
          </div>
          <!--/column -->
        </div>
        <!--/.row -->
      </div>
      <!-- /.container -->
    </section>
    <!-- /section -->
<section class="wrapper bg-light d-none">
  <div class="container-card pt-5 pt-md-10">
    <div class="card image-wrapper bg-full bg-image bg-overlay bg-overlay-light-500 pb-15" data-image-src="{%$settings.asset%}img/bg22.png">
      <div class="card-body py-14 px-0">
        <div class="container">
          <div class="row gx-lg-8 gx-xl-12 gy-10 gy-lg-0">
            <div class="col-lg-4 text-center text-lg-start">
              <h3 class="display-4 mb-3 pe-xxl-15">Lise Kış Okulu Programları</h3>
              <p class="lead fs-lg mb-0 pe-xxl-10">Ders programları takvimini aşağıdan takip edebilirsiniz.</p>
            </div>
            <!-- /column -->
            <div class="col-lg-8 mt-lg-2">
              <div class="row align-items-center counter-wrapper gy-6 text-center">
                <div class="col-md-4">
                  <img src="{%$settings.asset%}img/icons/solid/target.svg" class="svg-inject icon-svg icon-svg-sm solid-duo text-grape-fuchsia mb-3" alt="" />
                  <h3 class="counter">10+</h3>
                  <p class="mb-0">Gerçekleştirilen Programlar</p>
                </div>
                <!--/column -->
                <div class="col-md-4">
                  <img src="{%$settings.asset%}img/icons/solid/bar-chart.svg" class="svg-inject icon-svg icon-svg-sm solid-duo text-grape-fuchsia mb-3" alt="" />
                  <h3 class="counter">500+</h3>
                  <p class="mb-0">Katılımcı</p>
                </div>
                <!--/column -->
                <div class="col-md-4">
                  <img src="{%$settings.asset%}img/icons/solid/employees.svg" class="svg-inject icon-svg icon-svg-sm solid-duo text-grape-fuchsia mb-3" alt="" />
                  <h3 class="counter">30+</h3>
                  <p class="mb-0">Eğitmenler</p>
                </div>
                <!--/column -->
              </div>
              <!--/.row -->
            </div>
            <!-- /column -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container -->
      </div>
      <!--/.card-body -->
    </div>
    <!--/.card -->
  </div>
  <!-- /.container-card -->

  <div class="container">
    <div class="mb-16">
      <div class="row gy-6 mt-n18" style="display: flex; flex-wrap: wrap; align-items: stretch;">

        <?php foreach ($programs as $program) : ?
        <div class="item col-md-6 col-xl-3 d-flex">
          <div class="card shadow-lg card-border-bottom border-soft-primary flex-grow-1">
            <div class="card-body p-4">
              <blockquote class="icon mb-0">
                <h5 class="mb-1"><?= $program['date'] ?></h5>
                <div class="blockquote-details">
                  <div class="info ps-0">
                  <p><?= $program['title'] ?></p>
                  <p class="mb-0">
                      <?php foreach ($program['events'][0] as $time => $event) : ?>
                        <small class="fw-bold">
                        <span class="badge text-primary bg-soft-primary"><?= $time ?></span> <?= $event ?><br>
                        </small>
                      <?php endforeach; ?>
                    </p>
                  </div>
                </div>
              </blockquote>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
          <!--/column -->
        <?php endforeach; ?>
      </div>
      <!-- /.row -->
    </div>
    <!-- /.grid-view -->
  </div>
  <!-- /.container -->
</section>
<!-- /section -->


  <section class="wrapper">
    <div class="container py-5 py-md-10">
      <div class="row gx-lg-8 gx-xl-12 gy-10 align-items-center">
        <div class="col-md-10 col-lg-8 col-xl-7 col-xxl-6 mx-auto text-center">
          <h2 class="fs-15 text-uppercase text-muted mb-1">Lise Kış Okulu</h2>
          <h3 class="display-4 mb-5">Sıkça Sorulan Sorular</h3>
        </div>
        <div class="col-12">

          <div class="accordion accordion-wrapper" id="accordionExample">
            {%foreach from=$faq['nodes']['accordions']['name'] item=item key=key%}
            <div class="card accordion-item">
              <div class="card-header" id="heading_{%$key%}">
                <button class="accordion-button {%if $key !== 0%}collapsed{%/if%}"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse_{%$key%}"
                        aria-expanded="{%if $key === 0%}true{%else%}false{%/if%}"
                        aria-controls="collapse_{%$key%}">
                  {%$item%}
                </button>
              </div>
              <div id="collapse_{%$key%}"
                  class="accordion-collapse collapse {%if $key === 0%}show{%/if%}"
                  aria-labelledby="heading_{%$key%}"
                  data-bs-parent="#accordionExample">
                <div class="card-body">
                  {%$faq['nodes']['accordions']['text'][$key]%}
                </div>
              </div>
            </div>
            {%/foreach%}
          </div>          <!--/.accordion -->
        </div>
      </div>
    </div>
  </section>


    <section class="wrapper bg-soft-primary">
      <div class="container py-14 py-md-17">
        <div class="row gx-lg-8 gx-xl-12 gy-10 align-items-center">
          <div class="col-lg-7">
            <figure><img class="w-auto" src="{%$settings.asset%}img/illustrations/i5.png" srcset="{%$settings.asset%}img/illustrations/i5@2x.png 2x" alt="" /></figure>
          </div>
          <!--/column -->
          <div class="col-lg-5">
            <h3 class="display-4 mb-7">Sorularınız mı var? Bize ulaşmaktan çekinmeyin</h3>
            <div class="d-flex flex-row">
              <div>
                <div class="icon text-primary fs-28 me-4 mt-n1"> <i class="uil uil-location-pin-alt"></i> </div>
              </div>
              <div>
                <h5 class="mb-1">Sütlüce Kampüsü</h5>
                <address>Sütlüce Mah. İmrahor Cad. No: 82 Beyoğlu – İstanbul</address>
              </div>
            </div>
            <div class="d-flex flex-row">
              <div>
                <div class="icon text-primary fs-28 me-4 mt-n1"> <i class="uil uil-phone-volume"></i> </div>
              </div>
              <div>
                <h5 class="mb-1">Telefon</h5>
                <p>444 3 788</p>
              </div>
            </div>
            <div class="d-flex flex-row">
              <div>
                <div class="icon text-primary fs-28 me-4 mt-n1"> <i class="uil uil-envelope"></i> </div>
              </div>
              <div>
                <h5 class="mb-1">E-mail</h5>
                <p class="mb-0"><a href="mailto:lisekisokulu@istun.edu.tr" class="link-body">lisekisokulu@istun.edu.tr</a></p>
              </div>
            </div>
          </div>
          <!--/column -->
        </div>
        <!--/.row -->
      </div>
      <!-- /.container -->
    </section>
    <!-- /section -->

{%include file='_partials/footer.tpl' %}