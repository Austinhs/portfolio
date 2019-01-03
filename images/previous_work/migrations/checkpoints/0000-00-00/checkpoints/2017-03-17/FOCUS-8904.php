<?php
$wkhtmltopdfPath = "./assets/wkhtmltopdf/wkhtmltopdf-amd64";
if (file_exists($wkhtmltopdfPath)) {
  chmod($wkhtmltopdfPath, 0744);
}
?>
