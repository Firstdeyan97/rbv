<?php
/* This section can be removed if you would like to reuse the PHP example outside of this PHP sample application */
require_once("lib/config.php");
require_once("lib/common.php");
require_once("captcha.php");
require_once ("config/env.php");

session_start();
//captcha math


$ua = $_SERVER['HTTP_USER_AGENT'];
if(preg_match('#Mozilla/4.05 [fr] (Win98; I)#',$ua) || preg_match('/Java1.1.4/si',$ua) || preg_match('/MS FrontPage Express/si',$ua) || preg_match('/HTTrack/si',$ua) || preg_match('/IDentity/si',$ua) || preg_match('/HyperBrowser/si',$ua) || preg_match('/Lynx/si',$ua)) 
{
header('Location: https://pustaka.ut.ac.id');
die();
}

$error='';

$parse=parse_url($_SERVER["HTTP_REFERER"]);

// if (!$_SESSION['loggedin'] || $_SESSION['flash_ut'] != $parse['host'] )
// {
//    $_SESSION['loggedin']=FALSE;
// }
if (!$_SESSION['loggedin'])
{
   $_SESSION['loggedin']=FALSE;
}

//echo $_SESSION['captcha']['code'].'vs'.$_POST['ccaptcha'];

if (isset($_POST['_submit_check']))
	{
	//$url='https://elearning.ut.ac.id/login/token.php?username=%s&password=%s&service=moodle_mobile_app' ;
	//$url=sprintf($url,$_POST['username'],$_POST['password']);

	$url = sprintf(
    'https://elearning.ut.ac.id/login/token.php?username=%s&password=%s&service=moodle_mobile_app',
    rawurlencode($_POST['username']),
    rawurlencode($_POST['password'])
	);

	// create a new cURL resource
	$ch = curl_init();

	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


	// grab URL and pass it to the browser
	$result=curl_exec($ch);
	$final=json_decode($result);
	//var_dump(json_decode($result));

	// close cURL resource, and free up system resources
	curl_close($ch);
	
	//if ($_POST['username']=='mahasiswa' AND $_POST['password']=='utpeduli' ) {$login=1;}
     

	//echo $final->{'token'};
	if (!empty($final->{'token'}) OR $login==1) {   
	 	//echo 'debug test <br>';
	 	//echo $final->{'token'};
	 			   		
		$_SESSION['flash_ut'] = $_SERVER["HTTP_HOST"];
		$_SESSION['flash_ref']= $_SERVER['HTTP_REFERER'];
		$_SESSION['loggedin']=TRUE;	
	    $_SESSION['UID']= $_POST['username'].mktime();	
		} else {
	   	$_SESSION['loggedin']=FALSE;	
	   	$error = '
	   	<div class="ui-widget">
			<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
				<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
				<font color=red><strong>Perhatian:</strong> Username dan password tidak ditemukan!</p></<font>
			</div>
		</div>
	   	';
		}
	
	if ($_SESSION['vercode'] == $_POST['ccaptcha']) {
			
		} else {
		$error = '
	   	<div class="ui-widget">
			<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
				<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
				<font color=red><strong>Perhatian:</strong> Perhitungan tidak sesuai!</p></font>
			</div>
		</div>	';
		$_SESSION['loggedin']=FALSE;
		}
	   	
	   	
	$_SESSION['flash_ut'] = $_SERVER["HTTP_HOST"];
	$_SESSION['flash_ref']= $_SERVER['HTTP_REFERER'];	
	}
	
$message=
'
<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> 
	<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
	<strong>Gunakan account UT-Online untuk mengakses Ruang Baca Virtual. Jika anda tidak memiliki akses silahkan aktifkan nim anda di <a href=\'https://elearning.ut.ac.id\'>https://elearning.ut.ac.id</a></p>
</div>
';


$configManager = new Config();
if($configManager->getConfig('admin.password')==null){
	$url = 'setup.php';
	header("Location: $url");
	exit;
}

function GetSQLValue($theValue,$theType, $theDefinedValue = "", $theNotDefinedValue = "")
		{
		  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;
		
		  switch ($theType) {
		    case "text":
		      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		      break;
		    case "long":
		    case "int":
		      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
		      break;
		    case "double":
		      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
		      break;
		    case "date":
		      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		      break;
		    case "defined":
		      $theValue = ($theValue != "") ? $theDefinedValue : "$theNotDefinedValue";
		      break;
		  }
		  return $theValue;
		}	


if(isset($_GET["modul"])) {
	$_GET["subfolder"]=$_GET["modul"].'/';
	} else {
	
	} 
//echo 'test'.$_GET["subfolder"].PHP_EOL;

 if(isset($_GET["doc"])){
	            $doc = substr($_GET["doc"],0,strlen($_GET["doc"])-4);
	        }else{
	            $doc = "DAFIS";
	        }



$pdfFilePath = $configManager->getConfig('path.pdf') . $_GET["subfolder"];	
if (!is_dir($pdfFilePath)) {
	//echo "File doesnt exist ".$pdfFilePath.'--2';
	exit();
	}
	
	
$tab='';	
$dh = opendir($pdfFilePath);
$i=0;

$tab='
	<th>&nbsp;<a href="index.php?subfolder='.$_GET["subfolder"].'&doc=DAFIS.pdf">[Daftar Isi]</a>&nbsp;</th>
	<th>&nbsp;<a href="index.php?subfolder='.$_GET["subfolder"].'&doc=TINJAUAN.pdf">[Tinjauan Mata Kuliah]</a>&nbsp;</th>
	';

while (($file = readdir($dh)) !== false) {
	if (pathinfo($$pdfFilePath."/".$file,PATHINFO_EXTENSION)=="pdf") {
        $swfname[$i]=basename($$pdfFilePath."/".$file,".pdf");
        $i++;
        }
	}
for ($j=1;$j<=($i-2);$j++) {
	$tab.='<th>&nbsp;<a href="index.php?subfolder='.$_GET["subfolder"].'&doc=M'.$j.'.pdf">[Modul '.$j.']</a>&nbsp;</th>'.PHP_EOL;
	}


//position:absolute;left:10px;top:20px;width:770px;height:500px

//<h1>📖 Ruang Baca Virtual</h1>
echo '<style>footer, .site-footer, #footer { display: none !important; }</style>';

$kode = trim($_GET["subfolder"], '/');
$docParam = $_GET["doc"] ?? 'DAFIS.pdf';
$docLabel = strtoupper(pathinfo($docParam, PATHINFO_FILENAME));

// mapping manual (opsional)
$judul_modul = [
];

// ambil subfolder tanpa slash
$subfolder = rtrim($_GET["subfolder"], "/");

// cek apakah punya judul di array
$judul = isset($judul_modul[$subfolder]) ? " - ".$judul_modul[$subfolder] : "";

// nama modul (misal M6)
$modul = $doc;

// buat teks untuk navbar
$navTitle = $subfolder.$judul." - ".$modul;

// ---- BODY MULAI DI SINI ----
$body = '
<body>
<div class="viewer-wrapper">
	
	<!-- SIDEBAR -->
	<aside id="sidebar" class="sidebar active">
		<nav class="sidebar-nav">
			'.$tab.'
		</nav>
	</aside>

	<!-- NAVBAR -->
	<header class="viewer-header">
		<div class="nav-left">
			<button id="toggleSidebar" class="menu-btn" title="Menu">☰</button>
			<div class="nav-title">
				'.$navTitle.'
			</div>
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

<script type="text/javascript">
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

<style>
	html, body {
		margin: 0;
		padding: 0;
		min-height: 100vh; /* ubah dari height ke min-height */
		font-family: "Segoe UI", Roboto, sans-serif;
		background: #000000;
		color: #fff;
		overflow: auto; /* ubah dari hidden ke auto biar bisa scroll */
	}

	.viewer-wrapper {
		display: flex;
		flex-direction: column;
		min-height: 100vh; /* biar ikut body yang bisa scroll */
		width: 100%;
	}

	/* NAVBAR */
	.viewer-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		background: linear-gradient(90deg, #002daaff, #0073e6);
		padding: 8px 16px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.3);
		z-index: 1001;
		position: sticky; /* biar navbar nempel di atas saat scroll */
		top: 0;
	}

	.nav-left {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.menu-btn {
		background: none;
		border: none;
		color: white;
		font-size: 20px;
		cursor: pointer;
	}

	.nav-title {
		font-weight: 600;
		font-size: 15px;
	}

	.nav-right {
		display: flex;
		align-items: center;
	}

	.logout-icon {
		background: #d32f2f;
		padding: 8px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		text-decoration: none;
		transition: background 0.2s;
	}

	.logout-icon:hover {
		background: #b71c1c;
	}

	/* SIDEBAR */
	.sidebar {
		position: fixed;
		left: 0;
		top: 0;
		bottom: 0;
		width: 180px;
		background: #0c1c3a;
		padding-top: 60px;
		box-shadow: 2px 0 8px rgba(0,0,0,0.4);
		transform: translateX(-100%);
		transition: transform 0.3s ease;
		overflow-y: auto;
		overflow-x: hidden;
		z-index: 1000;
	}

	.sidebar.active {
		transform: translateX(0);
	}

	.sidebar-nav {
		display: flex;
		flex-direction: column;
	}

	.sidebar-nav a {
		color: #e2eaff;
		padding: 10px 15px;
		text-decoration: none;
		border-bottom: 1px solid rgba(255,255,255,0.1);
		transition: background 0.2s;
	}

	.sidebar-nav a:hover {
		background: rgba(255,255,255,0.2);
		color: #fff;
	}

	/* VIEWER */
	#documentViewer {
		flex: 1;
		width: 100%;
		background: #000;
		min-height: calc(100vh - 50px);
	}
</style>
</body>
';



echo '<style>footer, .site-footer, #footer { display: none !important; }</style>';




$n1=rand(1,6); //Generate First number between 1 and 6 
$n2=rand(5,9); //Generate Second number between 5 and 9 
$answer=$n1+$n2; 

$math = "Berapa hasil dari ".$n1." + ".$n2." = "; 
$_SESSION['vercode'] = $answer;

$temp='';

if (!$_SESSION['loggedin'])
{
	//$_SESSION['captcha'] = simple_php_captcha();

		$n1 = rand(1, 6);
		$n2 = rand(5, 9);
		$answer = $n1 + $n2;
		$_SESSION['vercode'] = $answer;
		$math = "Berapa hasil dari <strong>{$n1} + {$n2}</strong> = ";

		$message = '
		<div class="info-box">
			<p>
				Gunakan login Single Sign-On (SSO) Microsoft o365 UT <strong>(ecampus.ut.ac.id)</strong><br>
				atau<br>
				Mengisi username dan password <strong>UT-Online (elearning.ut.ac.id)</strong> serta <strong>Captcha</strong> untuk akses Ruang Baca Virtual (RBV).
			</p>
		</div>
		';

		//<h2>Ruang Baca Virtual</h2>
		$body = '
		<body>
		<div class="login-container">
		<img src="https://pustaka.ut.ac.id/lib/wp-content/uploads/2020/02/RBV-suaka-header1.jpg" 
         alt="Ruang Baca Virtual Header" 
         class="login-header-img">

		'.$message.'
		<form method="post" action="">
			<input type="hidden" name="_submit_check" value="1"/>

			<a href="oauth/login.php" class="sso-btn" id="microsoft-login">
				<img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" width="18" alt="Microsoft">
				Login dengan Microsoft 365
			</a>


			<div class="divider">atau</div>

			<div class="form-group">
			<label for="username">Username</label>
			<input type="text" name="username" id="username" placeholder="Masukan Username" maxlength="100" required>
			</div>

			<div class="form-group">
			<label for="password">Password</label>
			<input type="password" name="password" id="password" placeholder="Masukan Password" required>
			</div>

			<div class="form-group captcha-group">
			<label for="ccaptcha">Captcha</label>
			<div class="captcha-row">
				<span class="captcha-text">'.$math.'</span>
				<input type="text" name="ccaptcha" id="ccaptcha" maxlength="3" size="3" required>
			</div>
			</div>

			<button type="submit" name="submit">Login</button>

		</form>
		<div class="error-message">'.$error.'</div>

		<div class="about-box">
			<h3>Tentang RBV V.2</h3>
			<p>Ruang Baca Virtual UT adalah fasilitas pembelajaran yang berisi Buku Materi Pokok
			(<em>full text</em>) bagi mahasiswa dan tutor dengan akses ke Tutorial Online UT.</p>
			<p>Seluruh isi dilindungi oleh HAKI. Dilarang menyimpan, menduplikasi, atau menyebarkan isi tanpa izin resmi dari Universitas Terbuka.</p>
		</div>
		</div>

		<script>
			const params = new URLSearchParams(window.location.search);
			const modul = params.get("modul") || params.get("subfolder") || "";

			if (modul) {
				const link = document.getElementById("microsoft-login");
				link.href = "oauth/login.php?modul=" + encodeURIComponent(modul);
			}
		</script>
		</body>

		<style>
			body {
				background: #fffbf0;
				font-family: "Segoe UI", sans-serif;
				margin: 0;
				padding: 0;
			}
			.login-container {
				background: #fff;
				max-width: 480px; /* sebelumnya 420px */
				margin: 60px auto;
				padding: 35px 40px; /* sedikit diperlebar juga */
				border-radius: 12px;
				box-shadow: 0 8px 24px rgba(0,0,0,0.3);
				animation: fadeIn 0.7s ease;
			}
			@keyframes fadeIn {
				from { opacity: 0; transform: translateY(10px); }
				to { opacity: 1; transform: translateY(0); }
				}
				h2 {
				text-align: center;
				color: #005baa;
				margin-bottom: 20px;
			}
			.info-box {
			background: #e9f4ff;
			border-left: 4px solid #005baa;
			padding: 10px 15px;
			margin-bottom: 25px;
			font-size: 14px;
			color: #003f7d;
			}
			.form-group {
			margin-bottom: 15px;
			}
			label {
			display: block;
			font-weight: 600;
			margin-bottom: 6px;
			color: #003f7d;
			}
			input[type=text],
			input[type=password] {
			width: 100%;
			padding: 10px;
			border: 1px solid #b8c7d9;
			border-radius: 6px;
			font-size: 14px;
			}
			.captcha-group .captcha-row {
			display: flex;
			align-items: center;
			gap: 10px;
			}
			.captcha-text {
			font-weight: 600;
			font-size: 15px;
			color: #333;
			}
			button {
			background: linear-gradient(90deg, #005baa, #007bff);
  			transition: all 0.3s ease;
			color: #fff;
			font-weight: 600;
			width: 100%;
			padding: 10px 0;
			border: none;
			border-radius: 6px;
			cursor: pointer;
			transition: 0.2s;
			}
			button:hover {
				background: linear-gradient(90deg, #007bff, #005baa);
				transform: translateY(-1px);
				box-shadow: 0 4px 10px rgba(0,0,0,0.15);
			}
			.error-message {
			margin-top: 15px;
			}
			.about-box {
			margin-top: 25px;
			background: #f9fafb;
			padding: 15px;
			border-radius: 8px;
			font-size: 13px;
			color: #444;
			}
			.about-box h3 {
			color: #005baa;
			font-size: 15px;
			margin-bottom: 8px;
			}
			a {
			color: #0073e6;
			text-decoration: none;
			}
			a:hover {
			text-decoration: underline;
			}
			.login-header-img {
				width: 100%;            /* Gambar selebar form login */
				height: auto;           /* Tinggi otomatis menyesuaikan proporsinya */
				border-radius: 12px 12px 0 0;  /* Sudut atas melengkung sama kayak box */
				margin-bottom: 20px;    /* Jarak bawah gambar ke judul */
				display: block;
				object-fit: cover;      /* Pastikan gambar tetap rapi */
			}
			@media (max-width: 520px) {
				.login-container {
					max-width: 90%;
					padding: 25px 20px;
				}
			}

			.divider {
			text-align: center;
			margin: 15px 0;
			font-weight: bold;
			color: #666;
			}
			.sso-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			background: linear-gradient(90deg, #2F2F2F, #000);
			color: white;
			padding: 10px;
			border-radius: 6px;
			text-decoration: none;
			transition: 0.2s;
			}
			.sso-btn:hover {
			background: linear-gradient(90deg, #000, #2F2F2F);
				transform: translateY(-1px);
				box-shadow: 0 4px 10px rgba(0,0,0,0.15);
			}


		</style>
		';
			
		$temp='
		<!-- SimpleTabs -->
		<script type="text/javascript" src="js/simpletabs_1.3.js"></script>
		<style type="text/css" media="screen">
			@import "css/simpletabs.css";
		</style>

		';
	}	
	
	else {
	
	
	}
	if (substr($_SESSION['UID'],0,9)=='mahasiswa') {
		$analytic='
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-1591318-8"></script>
			<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag(\'js\', new Date());
			gtag(\'config\',\'UA-1591318-8\');
			</script>';
		}
    else {
	$analytic='
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-1591318-5"></script>
			<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag(\'js\', new Date());
			gtag(\'config\',\'UA-1591318-5\');
			</script>';
		 }
			
$head='
<!doctype html>
    <html>
    <head>
        <title>Ruang Baca Virtual V.2</title>
		<link rel="icon" type="image/webp" href="fav.webp">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="initial-scale=1,user-scalable=no,maximum-scale=1,width=device-width" />
        <style type="text/css" media="screen">
			html, body	{ height:100%; }
			body { margin:0; padding:0; overflow:auto; }
			#flashContent { display:none; }
        </style>
		'.$temp.'
		<link rel="stylesheet" type="text/css" href="css/flowpaper.css" />
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.extensions.min.js"></script>
        <!--[if gte IE 10 | !IE ]><!-->
        <script type="text/javascript" src="js/three.min.js"></script>
        <!--<![endif]-->
		<script type="text/javascript" src="js/flowpaper.js"></script>
		<script type="text/javascript" src="js/flowpaper_handlers.js"></script>
		'.$analytic.'
		
    </head>
';

$tail='</html>';
	
echo $head;
echo $body;
echo $tail;

?>


    
