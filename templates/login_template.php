<?php
function renderLogin($error='', $math='') {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ruang Baca Virtual - Login</title>
    <link rel="icon" type="image/webp" href="fav.webp">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.extensions.min.js"></script>
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
</head>
<body>
<div class="login-container">
    <img src="https://pustaka.ut.ac.id/lib/wp-content/uploads/2020/02/RBV-suaka-header1.jpg" alt="Ruang Baca Virtual Header" class="login-header-img">
    
    <div class="info-box">
        <p>
            Gunakan login Single Sign-On (SSO) Microsoft o365 UT <strong>(ecampus.ut.ac.id)</strong><br>
            atau<br>
            Mengisi username dan password <strong>UT-Online (elearning.ut.ac.id)</strong> serta <strong>Captcha</strong> untuk akses Ruang Baca Virtual (RBV).
        </p>
    </div>

    <?php if($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

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
                <span class="captcha-text"><?php echo $math; ?></span>
                <input type="text" name="ccaptcha" id="ccaptcha" maxlength="3" size="3" required>
            </div>
        </div>

        <button type="submit" name="submit">Login</button>
    </form>

    <div class="about-box">
        <h3>Tentang RBV V.2</h3>
        <p>Ruang Baca Virtual UT adalah fasilitas pembelajaran yang berisi Buku Materi Pokok (<em>full text</em>) bagi mahasiswa dan tutor dengan akses ke Tutorial Online UT.</p>
        <p>Seluruh isi dilindungi oleh HAKI. Dilarang menyimpan, menduplikasi, atau menyebarkan isi tanpa izin resmi dari Universitas Terbuka.</p>
    </div>
</div>

<script>
    // Kirim modul/subfolder ke SSO Microsoft
    const params = new URLSearchParams(window.location.search);
    const modul = params.get("modul") || params.get("subfolder") || "";
    if (modul) {
        const link = document.getElementById("microsoft-login");
        link.href = "oauth/login.php?modul=" + encodeURIComponent(modul);
    }
</script>
</body>
</html>
<?php
}
?>
