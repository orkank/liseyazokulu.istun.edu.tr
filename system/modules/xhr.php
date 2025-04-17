<?php

namespace master {
  use \PHPMailer\PHPMailer\PHPMailer;
  use \PHPMailer\PHPMailer\SMTP;
  use \PHPMailer\PHPMailer\Exception;

  class xhr extends Core {
    private function renderProgram($program) {
      $e = $program;

      $e['nodes']['description'] = !empty($e['nodes']['description']) ? $e['nodes']['description'] : $e['name'];

      $e['nodes']['tabs']['text'] = array_map(function($e) {
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $e, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Find all elements with style attributes and remove them
        $xpath = new \DOMXPath($doc);
        $elements = $xpath->query("//*[@style]");
        foreach($elements as $element) {
          $element->removeAttribute('style');
        }

        // Remove b, u, span elements but keep their contents
        $elementsToRemove = ['b', 'u', 'span'];
        foreach ($elementsToRemove as $tagName) {
            $elements = $xpath->query("//{$tagName}");
            foreach ($elements as $element) {
                $parent = $element->parentNode;
                while ($element->firstChild) {
                    $parent->insertBefore($element->firstChild, $element);
                }
                $parent->removeChild($element);
            }
        }

        // Process tables
        $tables = $doc->getElementsByTagName('table');
        foreach ($tables as $table) {
            // Create wrapper div
            $wrapper = $doc->createElement('div');
            $wrapper->setAttribute('class', 'table-responsive d-none d-md-block');

            // Create mobile cards wrapper
            $mobileWrapper = $doc->createElement('div');
            $mobileWrapper->setAttribute('class', 'mobile-table d-md-none');

            // Process table for mobile view
            $headers = [];
            $headerMap = []; // Store colspan information
            $rows = $table->getElementsByTagName('tr');

            // First try to get headers from thead
            $thead = $table->getElementsByTagName('thead');
            if ($thead->length > 0) {
                $headerRow = $thead->item(0)->getElementsByTagName('tr')->item(0);
                $colIndex = 0;
                foreach ($headerRow->getElementsByTagName('th') as $th) {
                    $colspan = $th->getAttribute('colspan') ? intval($th->getAttribute('colspan')) : 1;
                    for ($c = 0; $c < $colspan; $c++) {
                        $headers[$colIndex] = $th->textContent;
                        $headerMap[$colIndex] = [
                            'text' => $th->textContent,
                            'colspan' => $colspan,
                            'originalIndex' => count($headers) - $colspan
                        ];
                        $colIndex++;
                    }
                }
            }
            // If no thead, use first row as headers
            else if ($rows->length > 0) {
                $headerRow = $rows->item(0);
                $colIndex = 0;
                foreach ($headerRow->getElementsByTagName('td') as $td) {
                    $colspan = $td->getAttribute('colspan') ? intval($td->getAttribute('colspan')) : 1;
                    for ($c = 0; $c < $colspan; $c++) {
                        $headers[$colIndex] = $td->textContent;
                        $headerMap[$colIndex] = [
                            'text' => $td->textContent,
                            'colspan' => $colspan,
                            'originalIndex' => count($headers) - $colspan
                        ];
                        $colIndex++;
                    }
                }
            }

            // Process data rows
            $startIndex = ($thead->length > 0) ? 0 : 1;
            $rowspanTracker = array(); // Track active rowspans

            for ($i = $startIndex; $i < $rows->length; $i++) {
                $row = $rows->item($i);
                $card = $doc->createElement('div');
                $card->setAttribute('class', 'card mb-3 bg-soft-primary');

                $cardBody = $doc->createElement('div');
                $cardBody->setAttribute('class', 'card-body p-3');

                $cells = $row->getElementsByTagName('td');
                $cellIndex = 0;
                $actualCellIndex = 0;

                // Handle rowspan from previous rows
                foreach ($rowspanTracker as $idx => $span) {
                    if ($span['remainingRows'] > 0) {
                        // Add the spanning cell content
                        $item = $doc->createElement('div');
                        $item->setAttribute('class', 'd-flex align-items-start gap-2 mb-2');

                        $label = $doc->createElement('strong');
                        $label->setAttribute('class', 'table-label');
                        $label->textContent = $headers[$idx] . ': ';

                        $content = $doc->createElement('span');
                        $content->setAttribute('class', 'flex-grow-1');
                        $content->textContent = $span['content'];

                        $item->appendChild($label);
                        $item->appendChild($content);
                        $cardBody->appendChild($item);

                        $rowspanTracker[$idx]['remainingRows']--;
                        $cellIndex++;
                    }
                }

                foreach ($cells as $cell) {
                    $colspan = $cell->getAttribute('colspan') ? intval($cell->getAttribute('colspan')) : 1;
                    $rowspan = $cell->getAttribute('rowspan') ? intval($cell->getAttribute('rowspan')) : 1;

                    // Handle rowspan
                    if ($rowspan > 1) {
                        $rowspanTracker[$cellIndex] = [
                            'content' => $cell->textContent,
                            'remainingRows' => $rowspan - 1
                        ];
                    }

                    // Create mobile view elements
                    for ($c = 0; $c < $colspan; $c++) {
                        if (isset($headers[$cellIndex + $c])) {
                            $item = $doc->createElement('div');
                            $item->setAttribute('class', 'd-flex align-items-start gap-2 mb-2');

                            $label = $doc->createElement('strong');
                            $label->setAttribute('class', 'table-label');
                            $label->textContent = $headers[$cellIndex + $c] . ': ';

                            $content = $doc->createElement('span');
                            $content->setAttribute('class', 'flex-grow-1');
                            $content->textContent = $cell->textContent;

                            $item->appendChild($label);
                            $item->appendChild($content);
                            $cardBody->appendChild($item);
                        }
                    }

                    $cellIndex += $colspan;
                    $actualCellIndex++;
                }

                $card->appendChild($cardBody);
                $mobileWrapper->appendChild($card);
            }

            // Add table classes
            $currentClasses = $table->getAttribute('class');
            $requiredClasses = ['table', 'table-striped', 'table-hover'];
            $existingClasses = $currentClasses ? explode(' ', $currentClasses) : [];
            $newClasses = array_unique(array_merge($existingClasses, $requiredClasses));
            $table->setAttribute('class', implode(' ', $newClasses));

            // Create container and append both views
            $container = $doc->createElement('div');

            // Clone the table for the wrapper
            $tableClone = $table->cloneNode(true);
            $wrapper->appendChild($tableClone);

            $container->appendChild($wrapper);
            $container->appendChild($mobileWrapper);

            // Replace the original table with the container
            $table->parentNode->replaceChild($container, $table);
        }

        // Get the processed HTML
        $cleanHtml = '';
        $containers = $xpath->query('//div[contains(@class, "table-responsive")]/parent::div');
        foreach ($containers as $container) {
            $cleanHtml .= $doc->saveHTML($container);
        }

        return $cleanHtml;
      }, $e['nodes']['tabs']['text']);

      $this->core->view->append('program', $e);
      $e['program'] = $this->core->view->fetch('program.tpl');
      return $e;
    }

    private function ois_Create($data) {
      // API endpoint
      $url = 'https://ois.istun.edu.tr/api/sertifika';

      // Default credentials
      $defaultData = [
          'api_username' => 'sem.api',
          'api_password' => '5797jzY*',
          'method' => 'kayit'
      ];

      // Merge default data with provided data
      $postData = array_merge($defaultData, $data);

      // Initialize CURL
      $ch = curl_init();

      // Set CURL options
      curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_SSL_VERIFYPEER => false, // Only if needed for development
        CURLOPT_HTTPHEADER => [
          'Content-Type: application/x-www-form-urlencoded'
        ]
      ]);

      // Execute the request
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $error = curl_error($ch);

      // Close CURL
      curl_close($ch);

      // Handle response
      if ($error) {
        return [
          'success' => false,
          'error' => $error
        ];
      }

      // Decode JSON response
      $responseData = json_decode($response, true);

      return [
        'success' => $httpCode === 200,
        'data' => $responseData,
        'http_code' => $httpCode
      ];
    }

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

      return $filtered;
    }

    private function checkout() {
      $data = $this->core->post;

      $recaptcha = new \ReCaptcha\ReCaptcha($this->core->settings['gcaptchaV2']['secret']);
      $resp = $recaptcha
                        ->verify($this->core->post['g-recaptcha-response'], $this->core->IP()['IPADDR']);

      // $json = array('status' => 2, 'msg' => 'Bir hata oluştu, lütfen tekrar deneyiniz.');
      $uniqid = mt_rand(10000000, 99999999); // 8-digit number
      $q = false;

      $programs = $this->content->get(['where' => ['sl_module' => 27], 'nodes' => true, 'multiple' => true, 'meta' => false, 'columns' => 'slug{},name{}']);
      $this->core->post['program_id'] = $this->core->post['program_id'] ?? 0;

      $new_programs = [];
      $exist_record = false;

      if(!empty($this->core->post['program_id'])) {
        $new_programs = $this->filterPrograms($programs, $this->core->post['program_id']);
        $exist_record = $this->core->QueryArray("SELECT * FROM `forms` WHERE `tckn` = ? AND `status` = 2", [$this->core->post['ogrenciTcKimlikNo']]);

        // Extract and flatten program IDs from all records
        $exist_record = array_reduce(array_map(function($e) {
          return json_decode($e['json'], true)['program_id'];
        }, $exist_record), 'array_merge', []);
      }

      if(empty($new_programs)) {
        $json = array(
          'status' => 2,
          'msg' => 'Hiç program seçmediniz, lütfen listenize program ekleyiniz.'
        );

        die($this->core->jsonencode($json));
      }

      if($exist_record) {
        $exist_programs = $this->filterPrograms($programs, $exist_record);

        // Check for duplicate programs and date conflicts
        $has_conflict = false;
        $conflict_message = '';

        foreach ($new_programs as $new_program) {
          // First check if program is already registered
          foreach ($exist_programs as $exist_program) {
            if ($new_program['id'] == $exist_program['id']) {
              $has_conflict = true;
              $conflict_message = 'Bu programa daha önce kayıt yaptırmışsınız: ' . $new_program['name'];
              break 2;
            }

            // Check date conflicts if different programs
            $new_start = \DateTime::createFromFormat('d.m.Y', $new_program['nodes']['start_date']);
            $new_end = \DateTime::createFromFormat('d.m.Y', $new_program['nodes']['end_date']);
            $exist_start = \DateTime::createFromFormat('d.m.Y', $exist_program['nodes']['start_date']);
            $exist_end = \DateTime::createFromFormat('d.m.Y', $exist_program['nodes']['end_date']);

            if ($new_start <= $exist_end && $new_end >= $exist_start) {
              $has_conflict = true;
              $conflict_message = sprintf(
                'Seçtiğiniz "%s" programı, daha önce kayıt yaptırdığınız "%s" programı ile aynı tarihlerde çakışıyor.',
                $new_program['name'],
                $exist_program['name']
              );
              break 2;
            }
          }
        }

        if ($has_conflict) {
          $json = array(
            'status' => 2,
            'msg' => $conflict_message
          );
          die($this->core->jsonencode($json));
        }
      }

      if(!$resp->isSuccess()) {
        $json = array('status' => 2, 'msg' => 'Doğrulama yapılamadı, lütfen tekrar deneyiniz.');
      } else if (
        !empty($data['agreement']) and
        // !empty($data['agreement_ticari_iletisim']) and
        !empty($data['veliEmail']) and
        !empty($data['veliTelefon'])
      ) {
        $form = $this->core->jsonencode($data);
        $timestamp = time();
        $timestampplus = strtotime('+1 minutes');
        $data['published'] = $timestamp;
        $data['uniq'] = $uniqid;
        $tckn = $data['ogrenciTcKimlikNo'];

        $q = $this->core->Query(
          "INSERT INTO forms (`tckn`,`uniq`,`title`,`email`,`variables`,`json`,`ipaddress`,`status`,`type`,`published`)
          VALUES (?,?,?,?,?,?,?,?,?,?)",
          [$tckn, $uniqid, $data['veliAd'] .' '. $data['veliSoyad'], $data['veliEmail'], $form, $form, $this->core->IP()['IPLONG'], 1, 2668, $timestamp]
        );
      }

      if ($q) {
        $id = $this->core->lastInsertId();

      //   try {
      //     $result = $this->ois_Create(
      // [
      //       'ad' => $data['ogrenciAd'],
      //       'soyad' => $data['ogrenciSoyad'],
      //       'tc_kimlik_no' => $data['ogrenciTcKimlikNo'],
      //       'cinsiyet' => 'Erkek',
      //       'dogum_tarihi' => $data['ogrenciSoyad'],
      //       'dogum_yeri' => $data['il'],
      //       'eposta_adresi' => $data['ogrenciEmail'],
      //       'sertifika_kodu' => '70200001',
      //       'pesin_fiyat' => $price,
      //       'taksitli_fiyat' => $price,
      //       'taksit_adet' => '1',
      //       'external_id' => 'SEM' . $id
      //       ]
      //     );

      //     if ($result['success']) {
      //       echo "Registration successful!\n";
      //       print_r($result['data']);
      //     } else {
      //       echo "Registration failed!\n";
      //       print_r($result);
      //     }
      //   } catch (Exception $e) {
      //     echo "Error: " . $e->getMessage();
      //   }
      $data['published'] = date('Y-m-d H:i:s', $data['published']);

        $json = array(
          'id' => $id,
          'tckn' => $tckn,
          'status' => 0,
          'msg' => '',
          'post' => $data,
        );
      } else if(empty($json)) {
        $json = array(
          'status' => 1,
          'msg' => 'Hata oluştu, lütfen tekrar deneyiniz. Gerekli alanları doldurduğunuzdan emin olunuz.'
        );
      }

      die($this->core->jsonencode($json));
    }

    private function getPrograms() {
        if (isset($_COOKIE['Programs'])) {
            return explode(',', $_COOKIE['Programs']);
        }
        return [];
    }

    public function __construct() {
      $this->core = Core::getInstance();
      $this->content = new Contents;
      error_reporting(E_ALL);

      switch ($this->core->get['action'] ?? $this->core->post['action']) {
        case 'getProgramDetails':
          $program = $this->content->get(['where' => ['id' => $this->core->post['program'] ], '_nodes_columns' => ['date','price{tr}','quota'], 'nodes' => true, 'multiple' => false, 'meta' => false, 'columns' => 'slug{},name{}']);

          echo json_encode($this->renderProgram($program));
          exit;
        break;

        case 'checkout':
          $this->checkout();
          exit;
        break;

        case 'getProgram':
          $program = $this->content->get([
              'where' => ['id' => ['selector' => 'IN', 'value' => "({$this->core->post['program']})"]],
              '_nodes_columns' => ['date','price{tr}','quota'],
              'nodes' => true,
              'multiple' => true,
              'meta' => false,
              'columns' => 'slug{},name{}'
          ]);

          // Use the new function to get programs from cookie
          $cookiePrograms = $this->getPrograms();
          $programs = !empty($cookiePrograms) ? implode(',', $cookiePrograms) : '';

          if(!empty($programs)) {
              $existingPrograms = $this->content->get([
                  'where' => ['id' => ['selector' => 'IN', 'value' => "({$programs})"]],
                  '_nodes_columns' => ['date','price{tr}','quota'],
                  'nodes' => true,
                  'multiple' => true,
                  'meta' => false,
                  'columns' => 'slug{},name{}'
              ]);

              // Check date conflicts
              foreach ($program as $key => $newProg) {
                  $newStartDate = \DateTime::createFromFormat('d.m.Y', $newProg['nodes']['start_date']);
                  $newEndDate = \DateTime::createFromFormat('d.m.Y', $newProg['nodes']['end_date']);

                  foreach ($existingPrograms as $existingProg) {
                      $existingStartDate = \DateTime::createFromFormat('d.m.Y', $existingProg['nodes']['start_date']);
                      $existingEndDate = \DateTime::createFromFormat('d.m.Y', $existingProg['nodes']['end_date']);

                      // Check if date ranges overlap
                      if ($this->datesOverlap(
                          $newStartDate,
                          $newEndDate,
                          $existingStartDate,
                          $existingEndDate
                      )) {
                          $program[$key]['date_conflict'] = true;
                          $program[$key]['conflicting_program'] = $existingProg['name'];

                          break;
                      }
                  }
              }
          }

          echo json_encode($program);
          exit;
        break;

        case 'counties':
          if(!empty($this->core->post['cityID']) AND is_numeric($this->core->post['cityID'])) {
            $data = $this->core->QueryArray("SELECT * FROM `address_counties` WHERE `cityID` = ?", [$this->core->post['cityID']]);

            echo json_encode($data);
            exit;
          }
        break;

        case 'email':

          $recaptcha = new \ReCaptcha\ReCaptcha($this->core->settings['gcaptchaV2']['secret']);

          $resp = $recaptcha
                            ->verify($this->core->post['g-recaptcha-response'], $this->core->IP()['IPADDR']);

          if ($resp->isSuccess()) {
            $response = array('status' => 0, 'msg' => '');
            die($this->core->jsonencode($response));

            $mail = new PHPMailer(true);

            try {
                //Server settings
                //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                        //Enable verbose debug output
                $mail->isSMTP();                                                //Send using SMTP
                $mail->Host       = $this->core->settings['smtp']['server'];    //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                       //Enable SMTP authentication
                $mail->Username   = $this->core->settings['smtp']['username'];  //SMTP username
                $mail->Password   = $this->core->settings['smtp']['password'];  //SMTP password
                $mail->Port       = $this->core->settings['smtp']['port'];      //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;              //Enable implicit TLS encryption

                //Recipients
                $mail->setFrom($this->core->settings['smtp']['sender_email'], $this->core->settings['smtp']['sender_name']);

                $mail->addAddress('orkan@koylu.net', 'Orkan Köylü');     //Add a recipient
                $mail->addReplyTo($this->core->post['email'], $this->core->post['fullname']);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'İletişim Formu';
                $template = $this->core->view->fetch('email.template.html');

                $mail->Body    = $template;
                $mail->AltBody = '';

                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

          } else {
            $errors = $resp->getErrorCodes();
            $response = array('status' => 1, 'msg' => 'Doğrulama yapılamadı, lütfen tekrar deneyiniz.<br>' . implode('<br>',$errors) );
          }

          die($this->core->jsonencode($response));
        break;

        default:
          // code...
        break;
      }
    }

    // Modified helper method for date range overlap check
    private function datesOverlap($start1, $end1, $start2, $end2) {
        return $start1 <= $end2 && $end1 >= $start2;
    }
  }

  new xhr();
}
