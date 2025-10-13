<h2>Login Ruang Baca Virtual</h2>

<div class="info-box">
    <p>
        Gunakan login <b>Single Sign-On (SSO) Microsoft O365 UT</b> (<code>@ecampus.ut.ac.id</code>)<br>
        atau<br>
        Masukkan <b>username dan password UT-Online</b> (<code>elearning.ut.ac.id</code>) beserta Captcha untuk mengakses <b>Ruang Baca Virtual (RBV)</b>.
    </p>
</div>

<div class="login-options">
    <a href="oauth/login.php" class="sso-btn">
        <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" width="18" alt="Microsoft">
        Login dengan Microsoft 365
    </a>

    <hr>

    <form method="post" action="login.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Captcha</label>
        <input type="text" name="captcha" required placeholder="Jawab hasil 3+4">

        <button type="submit">Login</button>
    </form>
</div>
