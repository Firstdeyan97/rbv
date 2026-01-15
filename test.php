<?php
$file = "/home/nginx/domains/pustaka.ut.ac.id/private/pdfbmpdig/ADBI4130/DAFIS.pdf";

echo "realpath: " . var_export(realpath($file), true) . "<br>";
echo "file_exists: " . (file_exists($file) ? "YES" : "NO") . "<br>";
echo "is_readable: " . (is_readable($file) ? "YES" : "NO") . "<br>";

if (file_exists($file)) {
    echo "✅ File ada dan bisa diakses webserver";
} else {
    echo "❌ File tidak ditemukan atau tidak bisa diakses";
}
?>
