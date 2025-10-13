<?php
function renderViewer($tab, $navTitle, $doc, $pdfFilePath, $licenseKey, $renderingOrder) {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ruang Baca Virtual - Viewer</title>
    <link rel="icon" type="image/webp" href="fav.webp">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/flowpaper.css" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.extensions.min.js"></script>
    <script src="js/flowpaper.js"></script>
    <script src="js/flowpaper_handlers.js"></script>

    <style>
    html, body { margin:0; padding:0; min-height:100vh; font-family:"Segoe UI", Roboto, sans-serif; background:#000; color:#fff; overflow:auto;}
    .viewer-wrapper { display:flex; flex-direction:column; min-height:100vh; width:100%; }
    .viewer-header { display:flex; align-items:center; justify-content:space-between; background:linear-gradient(90deg,#002daaff,#0073e6); padding:8px 16px; box-shadow:0 2px 8px rgba(0,0,0,0.3); z-index:1001; position:sticky; top:0; }
    .nav-left { display:flex; align-items:center; gap:10px; }
    .menu-btn { background:none; border:none; color:white; font-size:20px; cursor:pointer; }
    .nav-title { font-weight:600; font-size:15px; }
    .nav-right { display:flex; align-items:center; }
    .logout-icon { background:#d32f2f; padding:8px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:0.2s; }
    .logout-icon:hover { background:#b71c1c; }
    .sidebar { position:fixed; left:0; top:0; bottom:0; width:180px; background:#0c1c3a; padding-top:60px; box-shadow:2px 0 8px rgba(0,0,0,0.4); transform:translateX(-100%); transition:transform 0.3s ease; overflow-y:auto; overflow-x:hidden; z-index:1000; }
    .sidebar.active { transform:translateX(0); }
    .sidebar-nav { display:flex; flex-direction:column; }
    .sidebar-nav a { color:#e2eaff; padding:10px 15px; text-decoration:none; border-bottom:1px solid rgba(255,255,255,0.1); transition:0.2s; }
    .sidebar-nav a:hover { background:rgba(255,255,255,0.2); color:#fff; }
    #documentViewer { flex:1; width:100%; background:#000; min-height:calc(100vh - 50px); }
    </style>
</head>
<body>
<div class="viewer-wrapper">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar active">
        <nav class="sidebar-nav">
            <?php echo $tab; ?>
        </nav>
    </aside>

    <!-- NAVBAR -->
    <header class="viewer-header">
        <div class="nav-left">
            <button id="toggleSidebar" class="menu-btn" title="Menu">☰</button>
            <div class="nav-title"><?php echo $navTitle; ?></div>
        </div>
        <div class="nav-right">
            <a href="logout.php" class="logout-icon" title="Keluar">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="white" viewBox="0 0 24 24">
                    <path d="M10 17v-3h4v-4h-4V7l-5 5 5 5zm9-12H5c-1.1 0-2 .9-2 2v3h2V7h14v10H5v-3H3v3
                    c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2z"/>
                </svg>
            </a>
        </div>
    </header>

    <!-- VIEWER -->
    <main id="documentViewer" class="flowpaper_viewer"></main>

</div>

<script>
    function getDocumentUrl(document){
		var numPages = '.getTotalPages($pdfFilePath . $doc . ".pdf").';
		var url = "{services/view.php?doc={doc}&format={format}&subfolder='.$_GET["subfolder"].'&page=[*,0],{numPages}}";
		url = url.replace("{doc}", document);
		url = url.replace("{numPages}", numPages);
		return url;
	}

	var searchServiceUrl = escape("services/containstext.php?doc='.$doc.'&page=[page]&searchterm=[searchterm]");
	$("#documentViewer").FlowPaperViewer({
		config : {
			DOC : escape(getDocumentUrl("'.$doc.'")),
			Scale : 1.0,
			FitWidthOnLoad : true,
			FitPageOnLoad : true,
			FullScreenAsMaxWindow : true,
			ProgressiveLoading : true,
			ZoomToolsVisible: true,
			NavToolsVisible: true,
			CursorToolsVisible: true,
			ViewModeToolsVisible: true,
			SearchToolsVisible: true,
			PrintToolsVisible: true,
			DownloadToolsVisible: true,
			FullScreenVisible: true,
			MinimapVisible: true,
			TextSelectionEnabled: true,
			AnnotationsEnabled: true,
			LocaleChain : "id_ID",
			RenderingOrder : "'.($configManager->getConfig('renderingorder.primary').','.$configManager->getConfig('renderingorder.secondary')).'",
			key : "'.$configManager->getConfig('licensekey').'"
		}
	});

	// Toggle sidebar
	const sidebar = document.getElementById("sidebar");
	const toggleBtn = document.getElementById("toggleSidebar");
	toggleBtn.addEventListener("click", () => {
		sidebar.classList.toggle("active");
	});

	// Default behavior: show on desktop, hide on mobile
	function setSidebarState() {
		if (window.innerWidth < 768) {
			sidebar.classList.remove("active"); // mobile: hide
		} else {
			sidebar.classList.add("active"); // desktop: show
		}
	}
	window.addEventListener("load", setSidebarState);
	window.addEventListener("resize", setSidebarState);
</script>

</body>
</html>
<?php
}
?>
