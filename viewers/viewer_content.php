<?php
$config = new Config();
$doc = htmlspecialchars($modul);
$pdfPath = $config->getConfig('path.pdf') . $doc . ".pdf";
$totalPages = getTotalPages($pdfPath);
?>

<div id="viewerContainer">
    <div id="documentViewer" class="flowpaper_viewer"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Viewer loaded for modul: <?php echo $doc; ?>');
    // Integrasi FlowPaper bisa ditambahkan di sini jika kamu sudah punya script-nya
});
</script>
