<?php
function renderLogin($error='', $math='') {

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ruang Baca Virtual - Login</title>
    <link rel="icon" type="image/webp" href="fav.webp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script type="text/javascript" src="js/simpletabs_1.3.js"></script>
	<style type="text/css" media="screen">@import "css/simpletabs.css";</style>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.extensions.min.js"></script>
    <?php echo $analytic ?>
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
				padding: 35px 40px; /* sedikit diFlebar juga */
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
            color: #f80303ff;
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
            Gunakan login <strong>Single Sign-On (SSO) Microsoft o365 UT (ecampus.ut.ac.id)</strong>
        </p>
    </div>

    <form method="post" action="">
        <input type="hidden" name="_submit_check" value="1"/>

        <a href="oauth/login.php" class="sso-btn" id="microsoft-login">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" width="18" alt="Microsoft">
            Login dengan SSO O365-UT (ecampus.ut.ac.id)
        </a>

        <div class="divider">atau</div>

        <div class="info-box">
        <p>
            Masukan Username dan Password <strong>UT-Online (elearning.ut.ac.id)</strong> serta <strong>Captcha</strong>.
        </p>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Masukan Username" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Masukan Password" required>
             <!-- Password akan diencrypt di server menggunakan public.pem -->
        </div>

        <div class="form-group captcha-group">
            <label for="ccaptcha">Captcha</label>
            <div class="captcha-row">
                <span class="captcha-text"><?php echo $math; ?></span>
                <input type="text" name="ccaptcha" id="ccaptcha" maxlength="3" size="3" required>
            </div>
        </div>

        <button type="submit">Login</button>
    </form>

     <?php if($error): ?>
        <div class="error-message"><strong>Perhatian: </strong><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="about-box">
        <h3>Tentang RBV</h3>
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
<!-- START: Client-side RSA-OAEP encryption (Web Crypto) -->
<script>
    /* PEM -> ArrayBuffer */
    function pemToArrayBuffer(pem) {
    const b64 = pem.replace(/-----[^-]+-----/g, '').replace(/\s+/g, '');
    const bin = atob(b64);
    const bytes = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
    return bytes.buffer;
    }

    /* import SPKI public key */
    async function importPublicKey(pemText) {
    const ab = await pemToArrayBuffer(pemText);
    return await crypto.subtle.importKey(
        'spki',
        ab,
        { name: 'RSA-OAEP', hash: { name: 'SHA-1' } }, // OAEP+SHA1 compatible with OpenSSL default
        false,
        ['encrypt']
    );
    }

    /* encrypt text -> base64 */
    async function encryptWithPublicKey(pubKey, text) {
    const encoded = new TextEncoder().encode(text);
    const cipher = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, pubKey, encoded);
    const u8 = new Uint8Array(cipher);
    let s = '';
    for (let i = 0; i < u8.length; i++) s += String.fromCharCode(u8[i]);
    return btoa(s);
    }

    /* main: intercept submit */
    document.addEventListener('DOMContentLoaded', function(){
    const form = document.querySelector('form[method="post"]');
    if (!form) return;

    // load public key once
const pubKeyPromise = fetch('pubkey.php', { cache: 'no-store' })
    .then(r => r.ok ? r.text() : null)   // kalau gagal fetch, return null
    .then(pem => pem ? importPublicKey(pem) : null)
    .catch(e => null);  // silent fail, tidak munculin error



    form.addEventListener('submit', async function(e){
        e.preventDefault();

        const pwdInput = form.querySelector('input[name="password"]');
        if (!pwdInput) return form.submit();

        const pwd = (pwdInput.value || '').trim();
        if (!pwd) return form.submit();

        try {
        const pubKey = await pubKeyPromise;
        if (pubKey) {
            const payload = JSON.stringify({ pw: pwd, t: Date.now() });
            const ct = await encryptWithPublicKey(pubKey, payload);

            // ensure hidden field exists
            let hf = form.querySelector('input[name="password_enc"]');
            if (!hf) {
                hf = document.createElement('input');
                hf.type = 'hidden';
                hf.name = 'password_enc';
                form.appendChild(hf);
            }
            hf.value = ct;

            // clear plaintext asap
            pwdInput.value = '';
            pwdInput.setAttribute('autocomplete','new-password');
        }

        // submit anyway
        form.submit();


        // ensure hidden field exists
        let hf = form.querySelector('input[name="password_enc"]');
        if (!hf) {
            hf = document.createElement('input');
            hf.type = 'hidden';
            hf.name = 'password_enc';
            form.appendChild(hf);
        }
        hf.value = ct;

        // clear plaintext asap
        pwdInput.value = '';
        pwdInput.setAttribute('autocomplete','new-password');

        // submit
        form.submit();
        } catch (err) {
        console.error('Encrypt failed, submitting plaintext as fallback', err);
        form.submit();
        }
    });
    });
</script>
<!-- END: Client-side RSA-OAEP encryption -->
</body>
</html>

<?php
}
?>
