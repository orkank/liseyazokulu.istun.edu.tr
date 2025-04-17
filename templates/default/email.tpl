<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>İstanbul Sağlık ve Teknoloji Üniversitesi - Kayıt Bilgilendirme</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f6f6f6;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 40px 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="text-align: center; padding-bottom: 30px;">
                            <img src="https://www.istun.edu.tr/templates/default/assets/images/istun.logo.red.png" alt="İstanbul Sağlık ve Teknoloji Üniversitesi" style="max-width: 200px; height: auto;"/>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0; color: #333333; font-size: 16px; line-height: 24px;">
                            <p style="margin: 0 0 20px 0;">Sayın, {%$details.ogrenciAd%} {%$details.ogrenciSoyad%}</p>

                            <p style="margin: 0 0 20px 0;">Kaydınız başarıyla oluşturulmuştur.</p>

                            <div style="margin: 0 0 20px 0;">
                                <p style="margin: 0 0 10px 0;"><strong>Öğrenci Bilgileri:</strong></p>
                                <p style="margin: 0 0 5px 0;">T.C. Kimlik No: {%$details.ogrenciTcKimlikNo%}</p>
                                <p style="margin: 0 0 5px 0;">E-posta: {%$details.ogrenciEmail%}</p>
                                <p style="margin: 0 0 5px 0;">Telefon: {%$details.ogrenciTelefon%}</p>
                                <p style="margin: 0 0 5px 0;">Doğum Tarihi: {%$details.birthDay%}/{%$details.birthMonth%}/{%$details.birthYear%}</p>
                                <p style="margin: 0 0 5px 0;">Okul: {%$details.okul%}</p>
                                <p style="margin: 0 0 5px 0;">Sınıf: {%$details.devamEdilenSinif%}</p>
                            </div>

                            <div style="margin: 0 0 20px 0;">
                                <p style="margin: 0 0 10px 0;"><strong>Veli Bilgileri:</strong></p>
                                <p style="margin: 0 0 5px 0;">Ad Soyad: {%$details.veliAd%} {%$details.veliSoyad%}</p>
                                <p style="margin: 0 0 5px 0;">E-posta: {%$details.veliEmail%}</p>
                                <p style="margin: 0 0 5px 0;">Telefon: {%$details.veliTelefon%}</p>
                            </div>

                            <div style="margin: 0 0 20px 0;">
                                <p style="margin: 0 0 10px 0;"><strong>Adres Bilgileri:</strong></p>
                                <p style="margin: 0 0 5px 0;">{%$details.adres%}</p>
                                <p style="margin: 0 0 5px 0;">{%$details.ilce%} / {%$details.il%}</p>
                            </div>

                            <p style="margin: 0 0 10px 0;">Kayıt olduğunuz programlar:</p>
                            <ul style="margin: 0 0 20px 0; padding-left: 20px;">
                                {%foreach from=$details.programs item=program%}
                                    <li style="margin-bottom: 5px;">{%$program.name%} - {%$program.nodes.start_date%}{%if $program.nodes.end_date%} - {%$program.nodes.end_date%}{%/if%}</li>
                                {%/foreach%}
                            </ul>

                            <p style="margin: 0 0 20px 0;">İlginiz için teşekkür ederiz</p>

                            <p style="margin: 0; font-weight: bold;">İstanbul Sağlık ve Teknoloji Üniversitesi</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8f8f8; padding: 20px 30px; text-align: center; font-size: 12px; color: #666666; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <p style="margin: 0;">© 2024 İstanbul Sağlık ve Teknoloji Üniversitesi. Tüm hakları saklıdır.</p>
            </td>
        </tr>
    </table>
</body>
</html>
