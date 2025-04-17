<?php

namespace master {
  // use Mews\Pos\PosInterface;  // Correct namespace
  // use Mews\Pos\Factory\AccountFactory;
  // use Mews\Pos\Factory\PosFactory;
  // use Mews\Pos\Factory\CreditCardFactory;
  // use Symfony\Component\EventDispatcher\EventDispatcher;
  // use Symfony\Component\HttpFoundation\Session\Session;
  // use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
  // use Mews\Pos\Event\RequestDataPreparedEvent;  // Add this
  // use Mews\Pos\Event\Before3DFormHashCalculatedEvent;  // Add this
  // use OmerKamcili\DenizBank\DenizbankPay3d;

  class checkout extends Core {
    // private
    // $paymentModel = PosInterface::MODEL_3D_PAY,
    // $transactionType = PosInterface::TX_TYPE_PAY_AUTH;

    function filterPrograms($programs, $program_ids, $tax_rate = 0.10) {
      $filtered = [];
      $total_price = 0;
      $total_tax = 0;
      $total_without_tax = 0;

      foreach ($programs as $program) {
        if (in_array($program['id'], $program_ids)) {
          $filtered[] = $program;

          // Convert price from "4.000" format to numeric
          $price = str_replace('.', '', $program['nodes']['price']); // Remove thousand separator
          $price = str_replace(',', '.', $price); // Convert decimal comma to point
          $price = floatval($price);

          // Calculate tax and price without tax
          $price_without_tax = $price / (1 + $tax_rate);
          $tax = $price - $price_without_tax;

          // Add to totals
          $total_price += $price;
          $total_tax += $tax;
          $total_without_tax += $price_without_tax;
        }
      }

      return [
          'programs' => $filtered,
          'total_price' => number_format($total_price, 2, ',', '.'),
          'total_price_raw' => $total_price,
          'total_tax' => number_format($total_tax, 2, ',', '.'),
          'total_tax_raw' => $total_tax,
          'total_without_tax' => number_format($total_without_tax, 2, ',', '.'),
          'total_without_tax_raw' => $total_without_tax,
          'count' => count($filtered)
      ];
    }
    public function payment($programs) {
      // $this->sessionHandler = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage([
      //   'cookie_samesite' => 'None',
      //   'cookie_secure'   => true,
      //   'cookie_httponly' => true, // Javascriptin session'a erişimini engelliyoruz.
      // ]);
      // $this->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
      // $this->session        = new \Symfony\Component\HttpFoundation\Session\Session($this->sessionHandler);

      $data = [
        'status' => false,
        'msg' => 'TCKN bulunamadı, lütfen tekrar deneyiniz.'
      ];

      if(isset($this->core->requests[3]) AND !empty($this->core->requests[3])) {
        $form = $this->core->QuerySingleRowArray("SELECT * FROM `forms` WHERE tckn = ? ORDER BY id DESC", [$this->core->requests[3]]);
        // echo $form['json'];
        $form['json'] = json_decode($form['json'], true);

        $sozlesme = $this->content->get(['where' => ['id' => 2676], 'nodes' => false, 'multiple' => false, 'meta' => false, 'columns' => 'slug{},name{},content{}']);
        $sozlesme['content'] = str_replace(
          ['{{FULLNAME}}','{{DATE}}','{{TCKN}}'],
          [$form['title'],date('Y-m-d', $form['published']),$form['tckn']],
          $sozlesme['content']
        );

        $data = [
          'status' => true,
          'form' => $form
        ];

        $this->core->view->append('sozlesme', $sozlesme);
      }

      $filtered_programs = $this->filterPrograms($programs, $form['json']['program_id']);
      $this->core->view->append('filtered_programs',$filtered_programs);

      $testCard = [
        "card_name" => "JON DOE", "card_number" => "4090700015897901", "card_cvv" => "991", "type" => "VI", "card_month" => "12", "card_year" => "14"
      ];
      $testCard = [
        'card_number' => '5570236460466000',
        'card_year' => '29',
        'card_month' => '12',
        'card_cvv' => '',
        'card_type' => 'master',

        'card_name' => 'ORKAN KÖYLÜ'
      ];


      if(isset($this->core->get['data']) AND !empty($this->core->get['data'])) {
        $this->core->view->append('card',$testCard);
      }

      if(isset($this->core->post['ShopCode'])) {
        $response = $this->core->post;

        if(
          isset($response['ErrorMessage'])
          AND !empty($response['ErrorMessage'])
          OR $response['Response'] != 'Approved'
        ) {
          $this->core->Query("UPDATE forms SET bank_response = ? WHERE tckn = ? AND id = ?",
          [json_encode($response), $form['tckn'], $form['id']]);

          $this->core->view->append('alert', ['msg' => 'Bankadan onay bilgisi alınamadı, lütfen tekrar deneyiniz.', 'small_msg' => $this->core->post['ErrorMessage']]);
        }

        // $response = array_merge($response, [
        //   'mdStatus' => 0,
        //   'ProcReturnCode' => 0
        // ]);

        if(
          $response['Response'] == 'Approved' AND
          $response['ProcReturnCode'] == "00"
        ) {
          if($form['status'] != 2)
            $this->core->Query("UPDATE forms SET response = ?, status = 2 WHERE tckn = ? AND id = ?",
            [json_encode($response), $form['tckn'], $form['id']]);

          // Method 1: Set expiration in the past
          setcookie("Programs", "", time() - 3600, "/");

          // Method 2: Use empty value and expiration
          // setcookie("Programs", "", 0, "/");

          // Method 3: Use unset
          // unset($_COOKIE['Programs']);
          // setcookie('Programs', null, -1, '/');

          $this->core->view->append('form',$form);
          $this->core->view->append('success',
          ['small_msg' => 'Programla ilgili daha sonra detaylı bilgilendirileceksiniz.', 'msg' => 'Ödemeniz başarıyla alınmıştır, ilginiz için teşekkür ederiz.', 'class' => 'success']);
        }
      }

      if(
        isset($this->core->post['fid']) AND
        isset($this->core->post['tckn'])
      ) {
        // $this->order = [
        //   'id'          => 'LISEKIS-' . $form['id'],
        //   'amount'      => $filtered_programs['total_price_raw'] . '.00',
        //   'currency'    => PosInterface::CURRENCY_TRY,
        //   'installment' => 0,
        //   'success_url' => 'https://dev.lisekisokulu.istun.edu.tr/tr/checkout/payment/' . $this->core->post['tckn'],
        //   'fail_url'    => 'https://dev.lisekisokulu.istun.edu.tr/tr/checkout/payment/' . $this->core->post['tckn'],
        //   'lang' => PosInterface::LANG_TR,
        //   'ip' => $_SERVER['REMOTE_ADDR'],  // Customer IP address
        //   'email' => $form['email'],   // Customer email

        //   'shipping_address' => [           // Add shipping address
        //       'address' => "{$form['json']['adres']} {$form['json']['ilce']}",
        //       'city' => $form['json']['il'],
        //       'country' => 'TR',
        //       'postcode' => '34000'
        //   ]
        // ];
        $month = strlen($this->core->post['card_month']) == 1 ? '0' . $this->core->post['card_month'] : $this->core->post['card_month'];

        $shopCode = "9488";  //Banka tarafindan verilen isyeri numarasi
        $purchaseAmount = $filtered_programs['total_price_raw'];         //Islem tutari
        $orderId = "LISEKIS-{$form['id']}" . uniqid();      //Siparis Numarasi
        $currency = "949"; // Kur Bilgisi - 949 TL

        $returnUrl = 'https://lisekisokulu.istun.edu.tr/tr/checkout/payment/' . $this->core->post['tckn'];

        $rnd = microtime();    //Tarih veya her seferinde degisen bir deger g�venlik ama�li
        $installmentCount = "";         //taksit sayisi
        $txnType ="Auth";     //Islem tipi
        $merchantPass = "9zbHT";  //isyeri 3D anahtari
        // hash hesabinda taksit ve islemtipi de kullanilir.

        $hashstr = $shopCode . $orderId . $purchaseAmount . $returnUrl . $returnUrl .$txnType. $installmentCount  .$rnd . $merchantPass;
        $hash = base64_encode(pack('H*',sha1($hashstr)));

        $formData['method'] = "POST";
        $formData['gateway'] = "https://inter-vpos.com.tr/mpi/Default.aspx";
        $formData['inputs'] = [
          'Pan' => $this->core->post['card_number'],
          'Cvv2' => $this->core->post['card_cvv'],
          'Expiry' => $month . $this->core->post['card_year'],

          "BonusAmount" => "",
          'CardType' => $this->detectCardType($this->core->post['card_number']),
          "ShopCode" => "9488",
          "PurchAmount" => $purchaseAmount,
          "Currency" => "949",
          'OrderId' => $orderId,
          "OkUrl" => $returnUrl,
          "FailUrl" => $returnUrl,
          'Rnd' => $rnd,
          'Hash' => $hash,
          "TxnType" => "Auth",
          "InstallmentCount" => "",
          "SecureType" => "3DPay",
          "Version3D" => "2.0",
          "Lang" => "tr"

          // // Visible inputs
          // 'Pan' => $this->core->post['card_number'],
          // 'Cvv2' => $this->core->post['card_cvv'],
          // 'Expiry' => $month . $this->core->post['card_year'],
          // 'BonusAmount' => '',
          // 'CardType' => $this->detectCardType($this->core->post['card_number']),
          // 'CardHolderBillingAdressLine1' => $form['json']['adres'],
          // 'CardHolderBillingAdressLine2' => '',
          // 'CardHolderBillingAdressLine3' => '',
          // 'CardHolderBillingCountry' => 'TR',
          // 'CardHolderBillingCity' => $form['json']['il'],
          // 'billAddrPostCode' => '34000',
          // 'billAddrState' => $form['json']['ilce'],
          // 'email' => $form['email'],
          // 'cardholderName' => $this->core->post['card_name'],
          // 'CardHolderShipingAdressLine1' => $form['json']['adres'],
          // 'CardHolderShipingAdressLine2' => '',
          // 'CardHolderShipingAdressLine3' => '',
          // 'CardHolderShipingCountry' => 'TR',
          // 'CardHolderShipingCity' => $form['json']['il'],
          // 'shipAddrPostCode' => '34000',
          // 'shipAddrState' => $form['json']['ilce'],
          // // 'homePhone' => $form['json']['veliTelefon'],
          // // 'mobilePhone' => $form['json']['ogrenciTelefon'],
          // // 'workPhone' => '',
          // // 'addrMatch' => 'Y',

          // // Hidden inputs
          // 'ShopCode' => $shopCode,
          // 'PurchAmount' => $purchaseAmount,
          // 'Currency' => $currency,
          // 'OrderId' => $orderId,
          // 'OkUrl' => $returnUrl,
          // 'FailUrl' => $returnUrl,
          // 'Rnd' => $rnd,
          // 'Hash' => $hash,
          // 'TxnType' => $txnType,
          // 'InstallmentCount' => $installmentCount,
          // 'SecureType' => '3DPay',
          // 'Version3D' => '2.0',
          // 'Lang' => 'tr'
        ];

        $this->core->view->append('formData',$formData);
        // $this->redirectToBank();
      }

      $this->core->view->append('data',$data);
      $this->core->view->output('payment.tpl');
    }

    public function init(int $id = 0): void {
      error_reporting(E_ALL);

      $page = $this->content->get(
        [
          'where' => ['id' => 2668],
          'images' => true,
          'nodes' => true,
          'multiple' => false, 'meta' => true, 'columns' => 'slug{},name{},content{},default,parent'
        ]
      );

      $programs = $this->content->get(['where' => ['sl_module' => 27], 'nodes' => true, 'multiple' => true, 'meta' => false, 'columns' => 'slug{},name{},content{}']);
      $cities = $this->core->QueryArray("SELECT * FROM address_cities WHERE countryID = 212");

      $this->core->view->append('cities', $cities);
      $this->core->view->append('meta', $page['meta']);
      $this->core->view->append('page', $page);
      $this->core->view->append('programs', $programs);

      if(isset($this->core->requests[2]) AND $this->core->requests[2] == 'payment') {
        $this->payment($programs);
        return;
      }

      $sampleData = [
        // Veli Bilgileri
        'veliAd' => '',
        'veliSoyad' => '',
        'veliEmail' => '',
        'veliTelefon' => '',

        // Öğrenci Bilgileri
        'ogrenciTcKimlikNo' => '',
        'ogrenciAd' => '',
        'ogrenciSoyad' => '',
        'ogrenciEmail' => '',
        'ogrenciTelefon' => '',

        // Doğum Tarihi
        'birthDay' => '',
        'birthMonth' => '',
        'birthYear' => '',

        // Okul Bilgileri
        'okul' => '',
        'devamEdilenSinif' => '',

        // Adres Bilgileri
        'il' => '',
        'ilce' => '',
        'adres' => ''
      ];

      if((isset($_GET['data']))) {
        $sampleData = [
          // Veli Bilgileri
          'veliAd' => 'Ahmet',
          'veliSoyad' => 'Yılmaz',
          'veliEmail' => 'ahmet.yilmaz@example.com',
          'veliTelefon' => '05321234567',

          // Öğrenci Bilgileri
          'ogrenciTcKimlikNo' => '12345678901',
          'ogrenciAd' => 'Mehmet',
          'ogrenciSoyad' => 'Yılmaz',
          'ogrenciEmail' => 'mehmet.yilmaz@example.com',
          'ogrenciTelefon' => '05351234567',

          // Doğum Tarihi
          'birthDay' => '15',
          'birthMonth' => '06',
          'birthYear' => '2008',

          // Okul Bilgileri
          'okul' => 'Atatürk Anadolu Lisesi',
          'devamEdilenSinif' => '10',

          // Adres Bilgileri
          'il' => 'İSTANBUL',
          'ilce' => 'Kadıköy',
          'adres' => 'Bağdat Caddesi No:123 D:4 Kadıköy'
        ];
      }

      $this->core->view->append('sampleData', $sampleData);

      $this->core->view->output('checkout.tpl');
      exit;
    }

    private function detectCardType($cardNumber) {
      $patterns = [
          '0' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
          '1' => '/^5[1-5][0-9]{14}$/',
          'amex' => '/^3[47][0-9]{13}$/',
      ];

      foreach ($patterns as $type => $pattern) {
          if (preg_match($pattern, $cardNumber)) {
              return $type;
          }
      }

      return null;
    }
    public function redirectToBank() {
      $this->account = \Mews\Pos\Factory\AccountFactory::createInterPosAccount(
        'denizbank', //pos config'deki ayarın index name'i
        '9488',
        'istunedu_adm',
        "'^z54%W&GpQzz?i",
        $this->paymentModel,
        '9zbHT',
        \Mews\Pos\PosInterface::LANG_TR
      );

      try {
        $config = require PB . '/pos.config.php';

        $this->pos =
        \Mews\Pos\Factory\PosFactory::createPosGateway
        ($this->account, $config, $this->eventDispatcher);

        // GarantiPos'u test ortamda test edebilmek için zorunlu.
        $this->pos->setTestMode(false);
      } catch (\Mews\Pos\Exceptions\BankNotFoundException | \Mews\Pos\Exceptions\BankClassNullException $e) {
        // var_dump($e);
        exit;
      }

      if(true) {
        // Sipariş bilgileri
        $this->session->set('order', $this->order);

        // Kredi kartı bilgileri
        $card = null;

        try {
          $card = CreditCardFactory::createForGateway(
      $this->pos,
      $this->core->post['card_number'],
      $this->core->post['card_year'],
      $this->core->post['card_month'],
      $this->core->post['card_cvv'],
      $this->core->post['card_name'],
      $this->detectCardType($this->core->post['card_number'])  // Replace manual card type with detection
          );
        } catch (\Mews\Pos\Exceptions\CardTypeRequiredException $e) {
        // bu gateway için kart tipi zorunlu
        } catch (\Mews\Pos\Exceptions\CardTypeNotSupportedException $e) {
        // sağlanan kart tipi bu gateway tarafından desteklenmiyor
        }

        $this->session->set('card', $this->core->post);

        try {
            $formData = $this->pos->get3DFormData(
                $this->order,
                $this->paymentModel,
                $this->transactionType,
                $card
            );

            $this->core->view->append('formData', $formData);
        } catch (\InvalidArgumentException $e) {
            // örneğin kart bilgisi sağlanmadığında bu exception'i alırsınız.
            // var_dump($e);
        } catch (\LogicException $e) {
            // ödeme modeli veya işlem tipi desteklenmiyorsa bu exception'i alırsınız.
            // var_dump($e);
        } catch (\Exception|\Error $e) {
            // var_dump($e);
            exit;
        }
      }
    }

    public function bankResponse() {
      $card  = null;

      $order = $this->session->get('order');
      $card = $this->session->get('card');

      $cardData = $this->card;

      // $session->remove('card');
      $card = \Mews\Pos\Factory\CreditCardFactory::createForGateway(
          $this->pos,
          $cardData['card_number'],
          $cardData['card_year'],
          $cardData['card_month'],
          $cardData['card_cvv'],
          $cardData['card_name'],
          $cardData['card_type']
    );
      // Ödeme tamamlanıyor,
    try  {
      $this->pos->payment($this->paymentModel,$this->order,$this->transactionType,$card);

      // Sonuç çıktısı
      $response = $this->pos->getResponse();
      // print_r($response);
      // response içeriği için /examples/template/_payment_response.php dosyaya bakınız.

      // Ödeme başarılı mı?
      if ($this->pos->isSuccess()) {
      // NOT: Ödeme durum sorgulama, iptal ve iade işlemleri yapacaksanız $response değerini saklayınız.
      }
      } catch (\Mews\Pos\Exceptions\HashMismatchException $e) {
      // Bankadan gelen verilerin bankaya ait olmadı����ında bu exception oluşur.
      // veya Banka API bilgileriniz hatalı ise de oluşur.
      } catch (\Error $e) {
        // print_r($e);
        exit;
      }

      return $response;
    }

    public function __construct() {
      $this->core = Core::getInstance();
      $this->content = new Contents;

      $this->content->statics();
      $this->prefix = $this->core->getLanguagePrefix();
      $this->request = $this->content->getRequest();

      $this->init();
    }
  }

  new checkout();
}