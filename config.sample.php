<?php
/**
 * Arfhause - E-posta yapilandirma ornegi.
 *
 * KURULUM:
 *   1. Bu dosyayi sunucuda `config.php` adiyla kopyalayin:
 *        cp config.sample.php config.php
 *   2. Asagidaki SMTP_PASS degerini Natro panelinden olusturdugunuz
 *      YENI sifre ile degistirin.
 *   3. config.php git'e EKLENMEZ (.gitignore'da) ve deploy ile uzerine
 *      yazilmaz; sunucuda kalici durur.
 */

return [
    'SMTP_HOST'   => 'srvm04.kurumsaleposta.com',
    'SMTP_USER'   => 'hello@arfhause.com',
    'SMTP_PASS'   => 'BURAYA_YENI_SIFRE',   // <-- Natro'dan yeni sifreyi yazin
    'SMTP_SECURE' => 'ssl',
    'SMTP_PORT'   => 465,
    'MAIL_FROM'   => 'hello@arfhause.com',
    'MAIL_TO'     => 'hello@arfhause.com',
    // Formun gonderilebilecegi izinli kaynak (CORS). Kendi alan adiniz.
    'ALLOWED_ORIGIN' => 'https://arfhause.com',
];
