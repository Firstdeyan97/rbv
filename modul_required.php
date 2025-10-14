<?php
// modul_required.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Modul Belum Dipilih</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #fffbf0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
            margin: 0;
            overflow: hidden;
        }
        .card {
            background: white;
            padding: 35px 45px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 520px;
            animation: fadeInUp 0.7s ease-out;
        }
        h2 {
            color: #0056b3;
            margin-bottom: 15px;
        }
        a {
            color: #0056b3;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        .note {
            font-size: 13px;
            color: #777;
            margin-top: 20px;
        }
        #countdown {
            font-weight: bold;
            color: #d9534f;
            font-size: 16px;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        #countdown.pulse {
            animation: pulse 1s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Ops! Modul belum dipilih 📘</h2>
        <p>Silakan pilih modul terlebih dahulu melalui halaman berikut:</p>
        <p><a href="https://pustaka.ut.ac.id/lib/ruangbaca/" target="_blank">🔗 Daftar Modul RBV</a></p>
        <p class="note">Anda akan dialihkan otomatis dalam <span id="countdown" class="pulse">10</span> detik...</p>
    </div>

    <script>
        let seconds = 10;
        const countdownEl = document.getElementById('countdown');
        const redirectUrl = "https://pustaka.ut.ac.id/lib/ruangbaca/";

        const timer = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = redirectUrl;
            }
        }, 1000);
    </script>
</body>
</html>
