{%include file='_partials/header.tpl' %}
<div class="content-wrapper">
  {%if isset($formData)%}
  <section class="wrapper bg-light">
    <div class="container pt-12 pt-md-14 pb-14 pb-md-16">
      <form method="{%$formData.method%}" action="{%$formData.gateway%}" class="redirect-form" role="form">

        {%foreach from=$formData.inputs key=key item=value%}
          <input type="hidden" name="{%$key%}" value="{%$value%}">
        {%/foreach%}

        <div class="alert alert-info" role="alert">Lütfen bekleyiniz banka doğrulama sayfasına yönlendiriliyorsunuz.</div>
        <hr class="my-4">
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Otomatik yönlendirme gerçekleşmez ise tıklayınız</button>
        </div>
      </form>
    </div>
  </section>

  <script>
    // Formu JS ile otomatik submit ederek kullaniciyi banka gatewayine yonlendiriyoruz.
    let redirectForm = document.querySelector('form.redirect-form');
    if (redirectForm) {
      redirectForm.submit();
    }
  </script>
  {%/if%}
  {%if isset($success)%}
    <section class="wrapper bg-light">
      <div class="container pt-12 pt-md-14 pb-14 pb-md-16">
        <h1>Sn. {%$form.title%},</h1>
        <div class="alert alert-success" role="alert">Ödemeniz başarıyla alınmıştır, ilginiz için teşekkür ederiz.</div>
      </div>
    </section>
  {%else%}

  <form action="/{%$prefix%}/checkout/payment/{%$data.form.tckn%}" method="post">
    <input type="hidden" name="fid" value="{%$data.form.id%}">
    <input type="hidden" name="tckn" value="{%$data.form.tckn%}">

    <section class="wrapper bg-light">
      <div class="container pt-12 pt-md-14 pb-14 pb-md-16">

        {%if !$data.status AND $data.status != 3%}
        <div class="row gx-md-8 gx-xl-12 gy-12">
          <div class="col-12 col-lg-8 m-auto mt-5">
            <div class="alert alert-warning alert-icon" role="alert">
              {%$data.msg%}
            </div>
          </div>
        </div>
        {%/if%}

        {%if isset($alert)%}
        <div class="row gx-md-8 gx-xl-12 gy-12">
          <div class="col-12 col-lg-8">
            <div class="alert alert-{%if !empty($alert.class)%}{%$alert.class%}{%else%}warning{%/if%} alert-icon" role="alert">
              {%$alert.msg%}
              <small class="text-muted mt-3 d-block">{%$alert.small_msg%}</small>
            </div>
          </div>
        </div>
        {%/if%}

        <div class="row gx-md-8 gx-xl-12 gy-12{%if $data.status == false OR isset($formData) OR $data.status != 3%} d-none{%/if%}">
          <div class="col-lg-8">
            <h3 class="mb-4">Ödeme Formu</h3>

            <div class="row g-3">
              <div class="mt-3 mb-6">
                <div class="form-check">
                  <input id="credit" name="paymentMethod" type="radio" class="form-check-input" checked="" required="">
                  <label class="form-check-label" for="credit">Kredi Kartı</label>
                </div>
                <!-- <div class="form-check">
                  <input id="debit" name="paymentMethod" type="radio" class="form-check-input" required="">
                  <label class="form-check-label" for="debit">Havale/EFT</label>
                </div> -->
              </div>
              <div class="card shadow-none bg-pale-green">
                <div class="card-body">
                  <div class="row">
                    <div class="col-xl-8">
                      <div class="row gy-3 gx-3">
                        <div class="col-md-12">
                          <div class="form-floating">
                            <input type="text" class="form-control" value="{%$card.card_number%}" name="card_number" id="cc-number" placeholder="Kredi Kartı Numarası"
                              required="">
                            <label for="cc-number" class="form-label">Kredi Kartı Numarası</label>
                            <div class="invalid-feedback"> Kredi Kartı Numarası gerekli </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-floating">
                            <input type="text" class="form-control" value="{%$card.card_name%}" name="card_name" id="cc-name" placeholder="Kart Üzerindeki İsim"
                              required="">
                            <label for="cc-name" class="form-label">Kart Üzerindeki İsim</label>
                            <div class="invalid-feedback"> Kart Üzerindeki İsim gerekli </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-floating">
                            <input type="text" class="form-control" value="{%$card.card_year%}" name="card_year" id="cc-expiration" maxlength="2" placeholder="Son Kullanma Tarihi"
                              required="">
                            <label for="cc-expiration" class="form-label">Yıl</label>
                            <div class="invalid-feedback"> Son Kullanma Tarihi gerekli </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-floating">
                            <input type="text" class="form-control" name="card_month" value="{%$card.card_month%}" id="cc-expiration" maxlength="2" placeholder="Son Kullanma Tarihi"
                              required="">
                            <label for="cc-expiration" class="form-label">Ay</label>
                            <div class="invalid-feedback"> Son Kullanma Tarihi gerekli </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-floating">
                            <input type="text" class="form-control" id="cc-cvv" name="card_cvv" value="{%$card.card_cvv%}" placeholder="CVV" required="">
                            <label for="cc-cvv" class="form-label">CVV</label>
                            <div class="invalid-feedback"> CVV kodu gereklidir </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-soft-gray rounded p-3">
                <div style="width:100%;height:350px;overflow: scroll;font-size:13px;">
                  {%$sozlesme.content%}
                </div>
              </div>
            </div>

          </div>
          <!-- /column -->
          <div class="col-lg-4">
            <h3 class="mb-4">Başvuru Detayı</h3>
            <div class="d-none shopping-cart mb-7" id="checkoutContent">
            </div>

            <div class="shopping-cart mb-7">
              {%foreach from=$filtered_programs.programs item=i%}
              <div class="shopping-cart-item bg-soft-primary p-2 rounded d-flex justify-content-between mb-3">
                <div class="d-flex flex-row d-flex align-items-center">
                  <div class="w-100">
                    <strong class="fs-14 mb-1 ellipsis d-block">{%$i.name%}</strong>
                    <div class="small">{%$i.nodes.date%}</div>
                  </div>
                </div>
                <div class="ms-2 d-flex align-items-center">
                  <p class="price fs-sm"><span class="amount">₺{%$i.nodes.price%}</span></p>
                </div>
              </div>
              {%/foreach%}
            </div>
            <!-- /.shopping-cart-->
            <hr class="my-4">
            <!-- <h3 class="mb-2">Sözleşmeler</h3> -->
            <div class="mb-5">
              <img src="{%$settings.asset%}img/denizsanalpos.png" class="img-fluid" style="max-width:200px;" alt="Deniz Sanalpos">
              <small class="text-muted d-block mt-2">* İşlemleriniz Denizbank Sanalpos üzerinden gerçekleştirilecektir.</small>
            </div>
            <div class="table-responsive">
              <table class="table table-order">
                <tbody>
                  <tr>
                    <td class="ps-0"><strong class="text-dark">Ara Toplam</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price" id="">₺ {%$filtered_programs.total_without_tax%}</p>
                    </td>
                  </tr>
                  <!-- <tr>
                    <td class="ps-0"><strong class="text-dark">Discount (5%)</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price text-red">-$6.8</p>
                    </td>
                  </tr> -->
                  <tr>
                    <td class="ps-0"><strong class="text-dark">KDV</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price" id="">₺ {%$filtered_programs.total_tax%}</p>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-0"><strong class="text-dark">Toplam</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price text-dark fw-bold" id="">₺ {%$filtered_programs.total_price%}</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="table-responsive d-none">
              <table class="table table-order">
                <tbody>
                  <tr>
                    <td class="ps-0"><strong class="text-dark">Ara Toplam</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price" id="checkout_subtotal">₺0</p>
                    </td>
                  </tr>
                  <!-- <tr>
                    <td class="ps-0"><strong class="text-dark">Discount (5%)</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price text-red">-$6.8</p>
                    </td>
                  </tr> -->
                  <tr>
                    <td class="ps-0"><strong class="text-dark">KDV</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price" id="checkout_tax">₺0</p>
                    </td>
                  </tr>
                  <tr>
                    <td class="ps-0"><strong class="text-dark">Toplam</strong></td>
                    <td class="pe-0 text-end">
                      <p class="price text-dark fw-bold" id="checkout_total">₺0</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="captcha-wrapper">
              <div id="offer-g-recaptcha" style="width:100%;overflow:hidden;"
                data-sitekey="{%$settings.gcaptchaV2.key%}"></div>
            </div>

            <button class="btn btn-primary rounded w-100 mt-4" type="submit" id="checkoutSubmit">Ödemeyi Tamamla</button>
          </div>
          <!-- /column -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container -->
  </form>
  {%/if%}
  </section>

  {%include file='_partials/footer.tpl' %}