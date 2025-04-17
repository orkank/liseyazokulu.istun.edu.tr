{%include file='_partials/header.tpl' %}
  <section class="wrapper bg-light" id="success" style="display: none;">
    <div class="container pt-12 pt-md-14 pb-14 pb-md-16">
      <div class="row gx-md-8 gx-xl-12 gy-12">
        <div class="col-lg-8">
          <h3 class="mb-4">Tebrikler, başarıyla başvurunuz alındı.</h3>
          <p>Başvurunuzun detayını aşağıda bulabilirsiniz.</p>

          <div class="alert alert-success">
            <strong class="d-block">Kış Okulu Kayıt No: <em id="form_uniq" class="code-wrapper-inner"></em></strong>
            <strong class="d-block">Başvuru Tarihi: <em id="form_published"></em></strong>
            <!-- <strong class="d-block">Başvuru Durumu: <em id="form_status"></em></strong> -->
            <br>
            <a data-payment href="/{%$prefix%}/checkout/payment" class="btn btn-primary mt-2">Ödeme Yapmak İçin Tıklayınız</a>
          </div>

          <!-- <p>* Havale/EFT ile ödemeyi tercih edenler için ödeme bilgilerini aşağıda bulabilirsiniz.</p>
          <hr class="my-4">

          <div class="bg-soft-primary p-3 rounded">
            <ul class="list-icons">
              <li>Banka Adı: DENİZBANK A.Ş.</li>
              <li>Şube Kodu: 9840</li>
              <li>Hesap Adı: İSTÜN SÜREKLİ EĞİTİM MERKEZİ İKTİSADİ İŞLETMESİ</li>
              <li>Hesap No: 24288019-351</li>
              <li>
                IBAN No:
                <span class="code-wrapper-inner">TR10 0013 4000 0242 8801 9000 01</span>
              </li>
            </ul>
            <p><strong>Not: Ödeme açıklamasına, "Kış Okulu Kayıt No" yazılması gerekmektedir.</strong></p>
          </div> -->
        </div>
      </div>
    </div>
  </section>

    <section class="wrapper bg-light">
      <form action="/{%$prefix%}/checkout" method="post" class="needs-validation" novalidate="">
        <div class="container pt-12 pt-md-14 pb-14 pb-md-16">
        <div class="row gx-md-8 gx-xl-12 gy-12">
          <div class="col-lg-8">
            <div class="messages"></div>
            <h3 class="mb-4">Başvuru Formu</h3>
            <div class="p-3 bg-soft-primary rounded">
              <div class="row g-3">
                <div class="col-12">
                  <h4>Veli Bilgileri</h4>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="veliAd" placeholder="Veli Adı" name="veliAd" value="{%$sampleData.veliAd%}" required>
                    <label for="veliAd" class="form-label">Veli Adı</label>
                    <div class="invalid-feedback">Geçerli bir ad giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="veliSoyad" placeholder="Veli Soyadı" name="veliSoyad" value="{%$sampleData.veliSoyad%}" required>
                    <label for="veliSoyad" class="form-label">Veli Soyadı</label>
                    <div class="invalid-feedback">Geçerli bir soyad giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" id="veliEmail" placeholder="Veli E-Posta" name="veliEmail" value="{%$sampleData.veliEmail%}" required>
                    <label for="veliEmail" class="form-label">Veli E-Posta</label>
                    <div class="invalid-feedback">Geçerli bir e-posta adresi giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="tel" class="form-control" id="veliTelefon" placeholder="Veli Telefon" name="veliTelefon" value="{%$sampleData.veliTelefon%}" required>
                    <label for="veliTelefon" class="form-label">Veli Telefon</label>
                    <div class="invalid-feedback">Geçerli bir telefon numarası giriniz.</div>
                  </div>
                </div>
              </div>
            </div>
            <hr class="my-4">
            <div class="p-3 bg-soft-green rounded">
              <div class="row g-3">
                <div class="col-12">
                  <h4>Öğrenci Bilgileri</h4>
                </div>
                <div class="col-sm-12">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="ogrenciTcKimlikNo" placeholder="Öğrenci T.C. Kimlik No" name="ogrenciTcKimlikNo" value="{%$sampleData.ogrenciTcKimlikNo%}" required>
                    <label for="ogrenciTcKimlikNo" class="form-label">Öğrenci T.C. Kimlik No</label>
                    <div class="invalid-feedback">Geçerli bir T.C. kimlik no giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="ogrenciAd" placeholder="Öğrenci Adı" name="ogrenciAd" value="{%$sampleData.ogrenciAd%}" required>
                    <label for="ogrenciAd" class="form-label">Öğrenci Adı</label>
                    <div class="invalid-feedback">Geçerli bir ad giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="ogrenciSoyad" placeholder="Öğrenci Soyadı" name="ogrenciSoyad" value="{%$sampleData.ogrenciSoyad%}" required>
                    <label for="ogrenciSoyad" class="form-label">Öğrenci Soyadı</label>
                    <div class="invalid-feedback">Geçerli bir soyad giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" id="ogrenciEmail" placeholder="Öğrenci E-Posta" name="ogrenciEmail" value="{%$sampleData.ogrenciEmail%}" required>
                    <label for="ogrenciEmail" class="form-label">Öğrenci E-Posta</label>
                    <div class="invalid-feedback">Geçerli bir e-posta adresi giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="tel" class="form-control" id="ogrenciTelefon" placeholder="Öğrenci Telefon" name="ogrenciTelefon" value="{%$sampleData.ogrenciTelefon%}" required>
                    <label for="ogrenciTelefon" class="form-label">Öğrenci Telefon</label>
                    <div class="invalid-feedback">Geçerli bir telefon numarası giriniz.</div>
                  </div>
                </div>
                <div class="col-12">
                  <strong class="d-block mb-2">Öğrenci Doğum Tarihi (Gün/Ay/Yıl)</strong>
                  <div class="row g-3">
                    <div class="col-sm-4">
                      <div class="form-floating">
                        <select class="form-select" id="birthDay" name="birthDay" required>
                          <option value="">Gün</option>
                        </select>
                        <label for="birthDay">Gün</label>
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <div class="form-floating">
                        <select class="form-select" id="birthMonth" name="birthMonth" required>
                          <option value="">Ay</option>
                        </select>
                        <label for="birthMonth">Ay</label>
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <div class="form-floating">
                        <select class="form-select" id="birthYear" name="birthYear" required>
                          <option value="">Yıl</option>
                          {%for $i=2014 to 2004 step=-1%}
                            <option value="{%$i%}" {%if $sampleData.birthYear == $i%}selected{%/if%}>{%$i%}</option>
                          {%/for%}
                        </select>
                        <label for="birthYear">Yıl</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="okul" placeholder="Okul" name="okul" value="{%$sampleData.okul%}" required>
                    <label for="okul" class="form-label">Okul</label>
                    <div class="invalid-feedback">Geçerli bir okul adı giriniz.</div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="devamEdilenSinif" placeholder="Devam Edilen Sınıf" name="devamEdilenSinif" value="{%$sampleData.devamEdilenSinif%}" required>
                    <label for="devamEdilenSinif" class="form-label">Devam Edilen Sınıf</label>
                    <div class="invalid-feedback">Geçerli bir sınıf giriniz.</div>
                  </div>
                </div>
              </div>
            </div>
            <hr class="my-4">
            <div class="p-3 bg-soft-primary rounded">
              <div class="row g-3">
                <div class="col-12">
                  <h4>Adres Bilgileri</h4>
                </div>
                <div class="col-sm-6">
                  <div class="form-select-wrapper mb-4">
                    <select class="form-select" name="il" aria-label="">
                      <option value="">İl seçiniz</option>
                      {%foreach from=$cities item=i%}
                        <option value="{%$i.cityName%}" {%if $sampleData.il == $i.cityName%}selected{%/if%}>{%$i.cityName%}</option>
                      {%/foreach%}
                    </select>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="ilce" placeholder="İlçe" name="ilce" value="{%$sampleData.ilce%}" required>
                    <label for="ilce" class="form-label">İlçe</label>
                    <div class="invalid-feedback">Geçerli bir ilçe giriniz.</div>
                  </div>
                </div>
                <div class="col-sm-12">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="adres" placeholder="Adres" name="adres" value="{%$sampleData.adres%}" required>
                    <label for="adres" class="form-label">Adres</label>
                    <div class="invalid-feedback">Geçerli bir adres giriniz.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /column -->
          <div class="col-lg-4">
            <h3 class="mb-4">Başvuru Detayı</h3>
            <div class="shopping-cart mb-7" id="checkoutContent">
            </div>
            <!-- /.shopping-cart-->
            <hr class="my-4">
            <!-- <h3 class="mb-2">Sözleşmeler</h3> -->
            <div class="mb-5">
              <div class="form-check mb-2">
                <input id="standart" name="agreement" type="checkbox" class="form-check-input" required="">
                <label class="form-check-label" for="standart"><a href="/{%$prefix%}/aydinlatma-metni-2669" data-type="ajax" data-width="800" data-height="600" data-filter="#content-wrapper" data-fancybox="">Aydınlatma Metni</a></label>
                <small class="text-muted d-block">Kişisel Verilerin İşlenmesi Aydınlatma Metni</small>
              </div>
              <div class="form-check">
                <input id="express" name="agreement_ticari_iletisim" type="checkbox" class="form-check-input">
                <label class="form-check-label" for="express"><a href="/{%$prefix%}/ticari-elektronik-ileti-onay-formu-2670" data-type="ajax" data-width="800" data-height="600" data-filter="#content-wrapper" data-fancybox="">Ticari Elektronik İletişim Onay Formu</a></label>
                <small class="text-muted d-block">* Opsiyonel</small>
              </div>
            </div>

            <div class="table-responsive">
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
              <div id="offer-g-recaptcha" style="width:100%;overflow:hidden;" data-sitekey="{%$settings.gcaptchaV2.key%}"></div>
            </div>

            <button class="btn btn-primary rounded w-100 mt-4" type="submit" id="checkoutSubmit">Başvuru Yap</button>
          </div>
          <!-- /column -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container -->
    </form>
    </section>
  </div>
  <!-- /.content-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get current date
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();

    // Set minimum age (10 years)
    const minAge = 10;
    const startYear = currentYear - minAge;
    const endYear = currentYear - 20; // Assuming maximum age of 100

    // Get select elements
    const daySelect = document.getElementById('birthDay');
    const monthSelect = document.getElementById('birthMonth');
    const yearSelect = document.getElementById('birthYear');

    // Populate days (1-31)
    for(let i = 1; i <= 31; i++) {
        const option = document.createElement('option');
        option.value = i.toString().padStart(2, '0');
        option.textContent = i;
        if (option.value === '{%$sampleData.birthDay%}') {
            option.selected = true;
        }
        daySelect.appendChild(option);
    }

    // Populate months (1-12)
    const months = [
        'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
        'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
    ];

    months.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = (index + 1).toString().padStart(2, '0');
        option.textContent = month;
        if (option.value === '{%$sampleData.birthMonth%}') {
            option.selected = true;
        }
        monthSelect.appendChild(option);
    });

    // Populate years (current year - minAge down to endYear)
    for(let year = startYear; year >= endYear; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }

    // Function to update days based on selected month and year
    function updateDays() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);

        if(year && month) {
            const daysInMonth = new Date(year, month, 0).getDate();
            const currentDay = parseInt(daySelect.value);

            // Store current selection
            const currentSelection = daySelect.value;

            // Clear existing days
            while(daySelect.options.length > 1) {
                daySelect.remove(1);
            }

            // Add correct number of days
            for(let i = 1; i <= daysInMonth; i++) {
                const option = document.createElement('option');
                option.value = i.toString().padStart(2, '0');
                option.textContent = i;
                daySelect.appendChild(option);
            }

            // Restore selection if valid
            if(currentSelection && currentSelection <= daysInMonth) {
                daySelect.value = currentSelection;
            }
        }
    }

    // Add event listeners to update days when month or year changes
    monthSelect.addEventListener('change', updateDays);
    yearSelect.addEventListener('change', updateDays);

    const tcInput = document.getElementById('ogrenciTcKimlikNo');

    var checkTcNum = function(value) {
      value = value.toString();
      var isEleven = /^[0-9]{11}$/.test(value);
      var totalX = 0;
      for (var i = 0; i < 10; i++) {
      totalX += Number(value.substr(i, 1));
      }
      var isRuleX = totalX % 10 == value.substr(10,1);
      var totalY1 = 0;
      var totalY2 = 0;
      for (var i = 0; i < 10; i+=2) {
      totalY1 += Number(value.substr(i, 1));
      }
      for (var i = 1; i < 10; i+=2) {
      totalY2 += Number(value.substr(i, 1));
      }
      var isRuleY = ((totalY1 * 7) - totalY2) % 10 == value.substr(9,0);
      return isEleven && isRuleX && isRuleY;
    };
    // Add input event listener for real-time validation
    tcInput.addEventListener('input', function(e) {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        console.log(this.value);

        // Limit to 11 digits
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }

        // Clear validation state if empty
        if (this.value.length === 0) {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
            this.classList.remove('is-valid');
            return;
        }

        // Validate when length is 11
        if (this.value.length === 11) {
          console.log(checkTcNum(this.value));
            if (!checkTcNum(this.value)) {
                this.setCustomValidity('Geçersiz T.C. Kimlik Numarası');
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        } else {
            this.setCustomValidity('T.C. Kimlik Numarası 11 haneli olmalıdır');
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });

    // Add blur event listener for validation when leaving the field
    tcInput.addEventListener('blur', function(e) {
        if (this.value.length > 0 && this.value.length < 11) {
            this.setCustomValidity('T.C. Kimlik Numarası 11 haneli olmalıdır');
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
});
</script>

<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"></script>
<script>
  function onloadCallback() {
    grecaptcha.render('offer-g-recaptcha', {
      'sitekey' : '{%$settings.gcaptchaV2.key%}'
    });
  }

  document.getElementById('checkoutSubmit').addEventListener('click', function() {
    theme.checkoutSubmit();
  });
</script>

{%include file='_partials/footer.tpl' %}
