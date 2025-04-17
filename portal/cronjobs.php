<?php
namespace master;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use master\content;

error_reporting(0);

require(dirname(__FILE__). '/../load.php');

function generatePlainTextEmail($details) {
    $text = <<<EOT
Sayın, {$details['ogrenciAd']} {$details['ogrenciSoyad']}

Kaydınız başarıyla oluşturulmuştur.

ÖĞRENCİ BİLGİLERİ:
-----------------
T.C. Kimlik No: {$details['ogrenciTcKimlikNo']}
E-posta: {$details['ogrenciEmail']}
Telefon: {$details['ogrenciTelefon']}
Doğum Tarihi: {$details['birthDay']}/{$details['birthMonth']}/{$details['birthYear']}
Okul: {$details['okul']}
Sınıf: {$details['devamEdilenSinif']}

VELİ BİLGİLERİ:
-------------
Ad Soyad: {$details['veliAd']} {$details['veliSoyad']}
E-posta: {$details['veliEmail']}
Telefon: {$details['veliTelefon']}

ADRES BİLGİLERİ:
--------------
{$details['adres']}
{$details['ilce']} / {$details['il']}

KAYIT OLDUĞUNUZ PROGRAMLAR:
------------------------
EOT;

    foreach ($details['programs'] as $program) {
        $text .= "- " . $program['name'] . "\n";
    }

    $text .= <<<EOT

İlginiz için teşekkür ederiz

İstanbul Sağlık ve Teknoloji Üniversitesi
----------------------------------------
© 2024 İstanbul Sağlık ve Teknoloji Üniversitesi. Tüm hakları saklıdır.
EOT;

    return $text;
}

if(empty($_REQUEST)) {
    $ois = $core->QueryArray("SELECT * FROM `forms` WHERE `ois`='0' AND `status` = 2 ORDER BY `id` DESC LIMIT 5");
    $data = $core->QueryArray("SELECT * FROM `forms` WHERE `cronjob`='0' AND `status` = 2 ORDER BY `id` DESC LIMIT 5");

    if(!$data) {
        $data = [];
    }

    if(empty($data) && empty($ois)) {
        die('ALL OK - '.date('Y-m-d H:i:s'));
    }

    $content = new Contents;

    for($i=0; $i<sizeof($ois); $i++) {
        $ois[$i]['details'] = json_decode($ois[$i]['json'], true);
        $ois[$i]['response'] = json_decode($ois[$i]['response'], true);
        $programs = implode(',', $ois[$i]['details']['program_id']);

        $programs = $content->get([
            'where' => ['id' => ['selector' => 'IN', 'value' => "({$programs})"]],
            'nodes' => true,
            'multiple' => true,
            'meta' => false,
            'columns' => 'slug{},name{}'
        ]);

        // print_r($programs);

        // Format birth date
        $birthDate = sprintf(
            '%04d-%02d-%02d',
            $ois[$i]['details']['birthYear'],
            $ois[$i]['details']['birthMonth'],
            $ois[$i]['details']['birthDay']
        );

        $results = [];

        // Process each program separately
        foreach ($programs as $program) {
            // Get price from program data
            $price = str_replace('.', '', $program['nodes']['price']); // Remove thousand separator
            $price = str_replace(',', '.', $price); // Convert decimal comma to point
            $price = floatval($price);

            if(empty($program['nodes']['sertifika_kodu']))
              continue;

            // First API call - Student Registration
            $registrationData = [
              'api_username' => 'sem.api',
              'api_password' => '5797jzY*',
              'method' => 'kayit',
              'ad' => $ois[$i]['details']['ogrenciAd'],
              'soyad' => $ois[$i]['details']['ogrenciSoyad'],
              'tc_kimlik_no' => $ois[$i]['details']['ogrenciTcKimlikNo'],
              'cinsiyet' => 'Erkek',
              'adres' => "{$ois[$i]['details']['adres']} {$ois[$i]['details']['ilce']} / {$ois[$i]['details']['il']}",
              'dogum_tarihi' => $birthDate,
              'dogum_yeri' => $ois[$i]['details']['il'],
              'eposta_adresi' => $ois[$i]['details']['ogrenciEmail'],
              'sertifika_kodu' => $program['nodes']['sertifika_kodu'] ?? '',
              'pesin_fiyat' => $price,
              'taksitli_fiyat' => $price,
              'taksit_adet' => '1',
              'external_id' => $ois[$i]['uniq']
            ];

            // Make registration API call
            if(!empty($ois[$i]['ois_response'])) {
              $ois[$i]['ois_response'] = json_decode($ois[$i]['ois_response'], true);
            }

            if(
              !isset($ois[$i]['ois_response']) &&
              isset($ois[$i]['ois_response']['err']) &&
              $ois[$i]['ois_response']['err'] != 0 OR
              !isset($ois[$i]['ois_response']['par']['ogrenci_no'])
            ) {
              $registrationResponse = makeApiCall($registrationData);
              $registrationResponse = $registrationResponse['data'];
            } else {
              $registrationResponse = $ois[$i]['ois_response'];
            }

            if(empty($ois[$i]['ois_response']))
              $core->Query(
                "UPDATE forms SET
                `ois_response` = ?
                WHERE `id` = ?",
                [
                  json_encode($registrationResponse),
                  $ois[$i]['id']
                ]
              );

              // Check if registration was successful and has required data
            if (
              isset($registrationResponse['err']) &&
              $registrationResponse['err'] == 0 &&
              isset($registrationResponse['par']['ogrenci_no'])
              ) {

                // Store student number for future reference
                $studentNo = $registrationResponse['data']['par']['ogrenci_no'];

                // If registration successful, make payment confirmation API call
                $paymentData = [
                  'api_username' => 'sem.api',
                  'api_password' => '5797jzY*',
                  'method' => 'odeme',
                  'tc_kimlik_no' => $ois[$i]['details']['ogrenciTcKimlikNo'],
                  'odeme_tarihi' => date('Y-m-d', $ois[$i]['published']), // Current date for payment
                  'sertifika_kodu' => $program['nodes']['sertifika_kodu'] ?? '',
                  'odeme_turu' => 'T-S6',
                  'odeme_miktari' => $price,
                  'spos_onay_kodu' => $ois[$i]['response']['AuthCode'],
                  'order_id' => $ois[$i]['uniq']
                ];

                $paymentResponse = makeApiCall($paymentData);

                $results[$program['id']] = [
                    'success' => $paymentResponse['success'],
                    // 'registration_response' => $registrationResponse['data'],
                    'payment_response' => $paymentResponse['data'],
                    'program_name' => $program['name'],
                    'student_no' => $studentNo,
                    'sent_data' => [
                      'registration' => $registrationData,
                      'payment' => $paymentData
                    ]
                ];
            } else {
              // Registration failed or missing required data
              $error = 'Registration failed';

              if (isset($registrationResponse['err']) && $registrationResponse['err'] !== 0) {
                $error = 'API Error: ' . ($registrationResponse['msg'] ?? 'Unknown error');
              } elseif (!isset($registrationResponse['par']['ogrenci_no'])) {
                $error = 'Missing student number in response';
              }

              $results[$program['id']] = [
                'success' => false,
                'registration_response' => $registrationResponse['data'],
                'error' => $error,
                'program_name' => $program['name'],
                'sent_data' => [
                  'registration' => $registrationData
                ]
              ];
            }
        }

        // Update database with results
        $allSuccess = !in_array(false, array_column($results, 'success'));

        $core->Query(
            "UPDATE forms SET
            `ois_response` = ?,
            `ois` = ?
            WHERE `id` = ?",
            [
                json_encode(['registration' => $registrationResponse, 'payment' => $results]),
                $allSuccess ? 1 : 2,
                $ois[$i]['id']
            ]
        );

        // Log errors if any
        if (!$allSuccess) {
            foreach ($results as $program_id => $result) {
                if (!$result['success']) {
                    error_log(sprintf(
                        "OIS API Error for form ID %d, Program '%s': %s",
                        $ois[$i]['id'],
                        $result['program_name'],
                        json_encode($result)
                    ));
                }
            }
        }
    }

    if(!empty($data))
        for($i=0; $i<sizeof($data); $i++) {
            $file = 'email';

            if(!empty($file)) {
                $details = json_decode($data[$i]['json'], true);

                $programs = implode(',', $details['program_id']);
                // $details['programs'] = $core->QueryArray("SELECT `name{tr}` AS name
                //   FROM `contents`
                //   WHERE `id` IN (".$programs.")
                // ");
                $programs = $content->get([
                  'where' => ['id' => ['selector' => 'IN', 'value' => "({$programs})"]],
                  'nodes' => true,
                  'multiple' => true,
                  'meta' => false,
                  'columns' => 'slug{},name{}'
                ]);

                $details['programs'] = $programs;

                $core->view->append('details', $details);
                $html = $core->view->fetch($file);
                $plainText = generatePlainTextEmail($details);

                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->SMTPDebug = $core->settings['smtp']['debug'];
                    $mail->isSMTP();
                    $mail->Host = $core->settings['smtp']['server'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $core->settings['smtp']['username'];
                    $mail->Password = $core->settings['smtp']['password'];
                    $mail->CharSet = $core->settings['smtp']['charset'];
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->Encoding = 'base64';

                    if(!empty($core->settings['smtp']['secure'])) {
                        $mail->SMTPSecure = $core->settings['smtp']['secure'];
                    }
                    $mail->Port = $core->settings['smtp']['port'];

                    // Recipients
                    $mail->setFrom($core->settings['smtp']['sender_email'], $core->settings['smtp']['sender_name']);
                    $mail->addReplyTo($core->settings['smtp']['reply_email'], $core->settings['smtp']['reply_name']);
                    $mail->ClearAllRecipients();

                    // $mail->addAddress('orkan.koylu@gmail.com', 'Orkan Köylü');
                    $mail->addAddress('lisekisokulu@istun.edu.tr', 'Lise Kış Okulu');
                    // Add student and parent as recipients
                    $mail->addAddress($details['ogrenciEmail'], $details['ogrenciAd'] . ' ' . $details['ogrenciSoyad']);
                    $mail->addAddress($details['veliEmail'], $details['veliAd'] . ' ' . $details['veliSoyad']);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'İstanbul Sağlık ve Teknoloji Üniversitesi - Kayıt Bilgilendirme';
                    $mail->Body = $html;
                    $mail->AltBody = $plainText;

                    $mail->send();

                    // Update cronjob status to sent (1)
                    $core->Query("UPDATE `forms` SET `cronjob`='1' WHERE `id`='".$data[$i]['id']."'");

                } catch (Exception $e) {
                    // echo $e->getMessage();
                    // exit;
                    // Update cronjob status to failed (2)
                    $core->Query("UPDATE `forms` SET `cronjob`='2' WHERE `id`='".$data[$i]['id']."'");
                }
            }
        }

    die("All cronjobs are done.");
}

// Helper function to make API calls
function makeApiCall($postData) {
    $ch = curl_init('https://ois.istun.edu.tr/api/sertifika');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
      return [
        'success' => false,
        'error' => $error,
        'data' => null
      ];
    }

    $responseData = json_decode($response, true);
    return [
      'success' => $httpCode === 200,
      'data' => $responseData,
      'http_code' => $httpCode
    ];
}

exit;
