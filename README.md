# Torqace

Torqace adalah Web interface untuk PBS/Torque. Aplikasi ini dibangun dari PBSWeb-Lite yang dikembangkan oleh Yuan-Chung Cheng. PBSWeb-Lite sendiri merupakan modifikasi dari PBSWeb yang dikembangkan oleh Paul Lu. Histori dari proyek ini dapat dilihat pada berkas HISTORY (dalam bahasa inggris).

Torque is free software, you can redistribute it and/or modify it under the terms of the GNU General Public License. The GNU General Public License does not permit this software to be redistributed in proprietary programs. :)

# Installation

*** Yuan-Chung Cheng telah mengetes ini pada Debian GNU/Linux 3.0 ***

*** Penulis mengetes ini pada Rocks Cluster 6.1 (Centos 6\. ***

Dalam Instalasi harus memiliki akses ke dalam root.

Requirements:&nbsp;

&nbsp;1\. PBS atau Torque
U don't say...

<span style="font-size: 16pt; line-height: 21pt; text-indent: 2em;">2\. &nbsp;Apache HTTP Server.
Penulis menggunakan PHP5 saat mengubah kode ini. Kalau membutuhkan SSL, mungkin perlu ada openssl dan apache modeSSL juga.</span>

<span style="font-size: 16pt; line-height: 21pt; text-indent: 2em;">3\. PHP5 scripting language.
Jangan lupa untuk mengaktifkan modul php di konfigurasi server anda.</span>

4\. SSH. Ini penting, Torqace dan PBSWeb-Lite menggunakan 'hampir' semua perintah dengan SSH. Jadi pastikan dapat berjalan dengan baik. Termasuk akses ke pbs_server dan kebutuhan lainnya.

User dalam menjalankan web-server berbeda-beda, kalau di debian mungkin 'www-data', kalau di sistem yang penulis pasang adalah 'apache', silakan disesuaikan.

Anyway, ini cara memasangnya
a. Compile spasswd dan install.

     1\. Edit Makefile, ganti sesuai kebutuhan
    2\. 'Make' spasswd, dan install semua.
    make;
    make install;
    `</pre>

    b. Setting PHP dan Apache.

    <pre>` 1\. Edit PHP settings (php.ini), pastikan :
    session.use_cookies = 1
    file_uploads = On
    upload_max_filesize = 50M
    session.cache_limiter =
    2\. Edit apache http.conf file, pastikan ini tidak dikomen
    LoadModule php5_module modules/php5.so
    AddType application/x-httpd-php .php
    AddType application/x-httpd-php-source .phps
    DirectoryIndex index.html index.htm index.shtml index.cgi index.php
    3\. Restart apache (or apache-ssl) di setiap perubahan yang dilakukan
    `</pre>

    c. Setting PBSWeb-Lite.

    <pre>` 1\. Masuk ke direktori tempat menginstall torqace, cari config.php
    2\. Ganti komponen di dalamnya sesuai dengan kebutuhan (misal lokasi qsub, qdel, dll)
    3\. Buat direktori upload dan download, set chmod ke 700\. pastikan apache memiliki
    akses baca/tulis kesana
    `</pre>

    d. Membuat key untuk user 'apache'.

    <pre>` Intinya, kita ingin agar user apache dapat melakukan ssh ke user-user lain tanpa harus
    memasukkan password, silakan cari tutorialnya di internet, tapi kurang lebih caranya adalah :
    su - apache
    cd ~apache
    mkdir .ssh
    chmod 70\. .ssh
    ssh-keygen -t dsa -f .ssh/id_dsa
    saat diminta passphrase, langsung enter saja. Setelah proses selesai, logout.

e. Untuk setiap user, buat folder "pbsweb" di dalam $HOME-nya. Dan copy/append key yang telah
ter-generate tadi (id_dsa.pub) ke dalam berkas .ssh/authorized_keys2 atau .ssh/authorized_keys
(sesuai dengan versi ssh anda). LAKUKAN INI UNTUK SEMUA USER.

f. Enjoy!

# Contact

Ardhi Putra Pratama [ardhipoetra@idmail.or.id](mailto:ardhipoetra@idmail.or.id)
