<?php
function renderViewer($tab, $navTitle, $doc, $pdfFilePath, $licenseKey, $renderingOrder, $subfolder) {

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
    } else {
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
    <title>Ruang Baca Virtual - Viewer</title>
    <link rel="icon" type="image/webp" href="fav.webp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1,user-scalable=no,maximum-scale=1,width=device-width" />
    <style type="text/css" media="screen">
        html, body { height:100%; }
        body { margin:0; padding:0; overflow:auto; }
        #flashContent { display:none; }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script type="text/javascript" src="js/simpletabs_1.3.js"></script>
    <style type="text/css" media="screen">@import "css/simpletabs.css";</style>
    <link rel="stylesheet" type="text/css" href="css/flowpaper.css" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.extensions.min.js"></script>
    <!--[if gte IE 10 | !IE ]><!-->
    <script type="text/javascript" src="js/three.min.js"></script>
    <!--<![endif]-->
    <script type="text/javascript" src="js/flowpaper.js"></script>
    <script type="text/javascript" src="js/flowpaper_handlers.js"></script>
    <?php echo $analytic ?>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Roboto, sans-serif;
            background: #000000;
            color: #fff;
        }
        .viewer-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }
        /* NAVBAR */
        .viewer-header { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(90deg, #002daaff, #0073e6); padding: 8px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 1001; position: sticky; top: 0; }
        .nav-left { display: flex; align-items: center; gap: 10px; }
        .menu-btn { background: linear-gradient(90deg, #002daaff, #0073e6); border: none; color: #fff; font-size: 22px; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.3s ease; }
        .menu-btn:hover { transform: rotate(90deg); box-shadow: 0 4px 12px rgba(0,0,0,0.25); background: linear-gradient(90deg, #0073e6, #002daaff); }
        .nav-title { font-weight: 600; font-size: 15px; }
        .nav-right { display: flex; align-items: center; }
        .logout-icon { background: #d32f2f; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }
        .logout-icon:hover { background: #b71c1c; }
        /* SIDEBAR */
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 180px; background: #0c1c3a; padding-top: 60px; box-shadow: 2px 0 8px rgba(0,0,0,0.4); transform: translateX(-100%); transition: transform 0.3s ease; overflow-y: auto; overflow-x: hidden; z-index: 1000; }
        .sidebar.active { transform: translateX(0); }
        .sidebar-nav { display: flex; flex-direction: column; }
        .sidebar-nav a { color: #e2eaff; padding: 10px 15px; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        .sidebar-nav a:hover { background: linear-gradient(90deg, #0073e6, #005baa); transform: translateX(5px); }
        /* VIEWER */
        #documentViewer { flex: 1; width: 100%; background: #000; min-height: calc(100vh - 50px); }
    </style>

    <!-- FLOATING ACTION BUTTON -->
    <style>
        #fab {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(90deg,#0073e6,#005baa);
            color: #fff;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #fab:hover { transform: scale(1.2) rotate(90deg); }

        #fab-menu {
            position: fixed;
            bottom: 85px;
            right: 25px;
            display: none;
            flex-direction: column;
            gap: 10px;
            z-index: 1999;
            opacity: 0;
            transform: translateY(20px) scale(0.8);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }
        #fab-menu.show {
            display: flex;
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        .fab-item { background: #fff; color: #005baa; padding: 10px 14px; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.2s; }
        .fab-item:hover { background: #0073e6; color: #fff; }

        /* Modal common */
        .modal-overlay {
            position: fixed;
            top:0; left:0; right:0; bottom:0;
            background: rgba(0,0,0,0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
        }
        .modal {
            background: #fff;
            color: #000;
            border-radius: 8px;
            max-width: 600px; /* lebih besar untuk notes */
            width: 95%;
            max-height: 80%;
            overflow-y: auto;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            position: relative;
        }
        .modal h3 { margin-top:0; color:#005baa; }
        .modal .close-btn { position: absolute; top:10px; right:10px; cursor: pointer; font-size: 18px; font-weight: bold; color:#333; }
        .modal input, .modal textarea { width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 6px; border:1px solid #ccc; }
        .modal button { background: linear-gradient(90deg,#0073e6,#005baa); color: #fff; padding: 8px 12px; border:none; border-radius:6px; cursor:pointer; margin-right:5px; }
        .modal button:hover { opacity:0.9; }
        .modal ul { list-style:none; padding:0; }
        .modal li { padding:6px 0; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center; }
        .modal li span { flex:1; }
        .note-title { font-weight:600; }
        .local-only { font-size:11px; color:#999; margin-top:5px; }

       /* Modal notes list */
#notes-modal .modal {
    max-width: 500px;   
    width: 90%;
    max-height: 80vh;   
    overflow-y: auto;   
    padding: 20px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 6px 20px rgba(0,0,0,0.35);
}

/* Modal view note individual */
#note-view-modal {
    position: fixed;
    top: 50%;
    left: 60%;            /* sejajar modal list */
    transform: translateY(-50%);
    max-width: 500px;     
    width: 90%;
    max-height: 80vh;     
    overflow-y: auto;
    padding: 20px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 6px 20px rgba(0,0,0,0.35);
    z-index: 3500;
    display: none;

    /* Animasi masuk */
    opacity: 0;
    transition: all 0.3s ease;
}
#note-view-modal.show {
    opacity: 1;
}

#note-view-modal h4 { margin-top: 0; color: #005baa; }
#note-view-modal .close-btn { position: absolute; top:5px; right:10px; cursor:pointer; font-weight:bold; }


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

    <div id="fab"><i class="bi bi-three-dots"></i></div>
    <div id="fab-menu">
        <!-- <div class="fab-item" id="fab-notes-btn"><i class="bi bi-journal-text"></i> Catatan</div> -->
        <div class="fab-item" id="fab-bookmarks-btn"><i class="bi bi-bookmark"></i> Bookmarks</div>
    </div>

<!-- NOTES MODAL -->
<div class="modal-overlay" id="notes-modal">
  <div class="modal">
      <div class="close-btn" id="close-notes">×</div>
      <h3>Notes <span class="local-only">(Hanya tersimpan di device dan browser ini)</span></h3>
      <ul id="notes-list"></ul>
      <input type="text" id="note-title" placeholder="Judul catatan">
      <textarea id="note-content" placeholder="Isi catatan"></textarea>
      <button id="add-note-btn">Tambah / Update</button>
  </div>
</div>

<!-- VIEW NOTE MODAL -->
<div class="modal-overlay" id="note-view-modal">
  <div class="modal modal-view">
      <div class="close-btn" id="close-note-view">×</div>
      <h3 id="note-view-title"></h3>
      <div id="note-view-content" style="white-space: pre-wrap;"></div>
  </div>
</div>



<!-- BOOKMARKS MODAL -->
<div class="modal-overlay" id="bookmarks-modal">
  <div class="modal">
      <div class="close-btn" id="close-bookmarks">×</div>
      <h3>Bookmarks <span class="local-only">(Hanya tersimpan di device dan browser ini)</span></h3>
      <ul id="bookmarks-list"></ul>
      <button id="add-bookmark-btn">Tambah Modul Saat Ini</button>
  </div>
</div>

<script>
    function getDocumentUrl(document){
        var numPages = <?php echo getTotalPages($pdfFilePath . $doc . ".pdf"); ?>;
        var url = "{/reader/services/view.php?doc={doc}&format={format}&subfolder=<?php echo $subfolder; ?>&page=[*,0],{numPages}}";
        url = url.replace("{doc}", document);
        url = url.replace("{numPages}", numPages);
        return url;
    }
    var searchServiceUrl = escape("/reader/services/containstext.php?doc=<?php echo $doc; ?>&page=[page]&searchterm=[searchterm]");

    $(document).ready(function() {
        $("#documentViewer").FlowPaperViewer({
            config : {
                DOC : escape(getDocumentUrl("<?php echo $doc; ?>")),
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
                RenderingOrder : "<?php echo $renderingOrder; ?>",
                key : "<?php echo $licenseKey; ?>"
            }
        });

        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleSidebar");
        toggleBtn.addEventListener("click", () => { sidebar.classList.toggle("active"); });
        function setSidebarState() {
            if (window.innerWidth < 768) sidebar.classList.remove("active");
            else sidebar.classList.add("active");
        }
        window.addEventListener("load", setSidebarState);
        window.addEventListener("resize", setSidebarState);
    });

    // ---- Floating button toggle ----
    const fab = document.getElementById("fab");
    const fabMenu = document.getElementById("fab-menu");
    fab.addEventListener("click", () => {
        fabMenu.classList.toggle("show");
    });

    // ----- NOTES MODAL -----
// const notesModal = document.getElementById("notes-modal");
// const notesList = document.getElementById("notes-list");
// const noteTitleInput = document.getElementById("note-title");
// const noteContentInput = document.getElementById("note-content");
// let editingNoteId = null;

// // Modal view note (terpisah dari list modal)
// const noteViewModal = document.getElementById("note-view-modal");
// const noteViewTitle = document.getElementById("note-view-title");
// const noteViewContent = document.getElementById("note-view-content");

// document.getElementById("close-note-view").addEventListener("click", ()=>{
//     noteViewModal.classList.remove("show");

//     // delay 300ms supaya fade-out selesai baru hide
//     setTimeout(()=>{ noteViewModal.style.display = "none"; }, 300);
// });



// // Fungsi ambil / simpan notes dari localStorage
// function getNotes(){ return JSON.parse(localStorage.getItem("notes")||"[]"); }
// function saveNotes(notes){ localStorage.setItem("notes", JSON.stringify(notes)); }

// // Render list notes di modal utama
// function renderNotes(){
//     const notes = getNotes();
//     notesList.innerHTML = "";
//     notes.forEach(n=>{
//         const li = document.createElement("li");
//         const span = document.createElement("span");
//         span.textContent = n.title; // hanya judul
//         li.appendChild(span);

//         // Tombol view
//         const viewBtn = document.createElement("button");
//         viewBtn.textContent = "View";
//        viewBtn.onclick = ()=>{
//             noteViewTitle.textContent = n.title;
//             noteViewContent.textContent = n.content;
//             noteViewModal.style.display = "flex";

//             // Tambahkan class show untuk animasi
//             noteViewModal.classList.add("show");
//         };

//         li.appendChild(viewBtn);

//         // Tombol edit
//         const editBtn = document.createElement("button");
//         editBtn.textContent = "Edit";
//         editBtn.onclick = ()=>{
//             noteTitleInput.value = n.title;
//             noteContentInput.value = n.content;
//             editingNoteId = n.id;
//         };
//         li.appendChild(editBtn);

//         // Tombol hapus
//         const delBtn = document.createElement("button");
//         delBtn.textContent = "Hapus";
//         delBtn.onclick = ()=>{
//             const filtered = notes.filter(x => x.id !== n.id);
//             saveNotes(filtered);
//             renderNotes();
//             if(noteViewModal.style.display==="block" && noteViewTitle.textContent===n.title){
//                 noteViewModal.style.display="none";
//             }
//         };
//         li.appendChild(delBtn);

//         notesList.appendChild(li);
//     });
// }

// // Tombol buka modal notes dari floating button
// fabMenu.querySelector("#fab-notes-btn").addEventListener("click", ()=>{
//     fabMenu.classList.remove("show");
//     notesModal.style.display = "flex";
//     renderNotes();
// });

// // Tutup modal notes
// document.getElementById("close-notes").addEventListener("click", ()=>{
//     notesModal.style.display = "none";
// });

// // Tombol tambah / update note
// document.getElementById("add-note-btn").addEventListener("click", ()=>{
//     const title = noteTitleInput.value.trim();
//     const content = noteContentInput.value.trim();
//     if(!title && !content){ alert("Isi catatan dulu!"); return; }

//     const notes = getNotes();
//     if(editingNoteId){
//         // update note
//         const idx = notes.findIndex(x => x.id === editingNoteId);
//         if(idx >= 0){
//             notes[idx].title = title;
//             notes[idx].content = content;
//         }
//     } else {
//         // tambah note baru
//         notes.push({id: Date.now(), title, content, createdAt: new Date().toISOString()});
//     }

//     saveNotes(notes);
//     noteTitleInput.value = "";
//     noteContentInput.value = "";
//     editingNoteId = null;
//     renderNotes();
// });


    // ---- Bookmarks modal ----
    const bookmarksModal = document.getElementById("bookmarks-modal");
    const bookmarksList = document.getElementById("bookmarks-list");

    fabMenu.querySelector("#fab-bookmarks-btn").addEventListener("click", ()=>{
        fabMenu.classList.remove("show");
        bookmarksModal.style.display="flex";
        renderBookmarks();
    });
    document.getElementById("close-bookmarks").addEventListener("click",()=>{ bookmarksModal.style.display="none"; });

    function getBookmarks(){ return JSON.parse(localStorage.getItem("bookmarks")||"[]"); }
    function saveBookmarks(b){ localStorage.setItem("bookmarks",JSON.stringify(b)); }
    function renderBookmarks(){
        const b = getBookmarks();
        bookmarksList.innerHTML="";
        b.forEach(item=>{
            const li = document.createElement("li");
            const span = document.createElement("span");
            span.textContent = item.title + " ("+new Date(item.createdAt).toLocaleDateString()+")";
            li.appendChild(span);

            const goBtn = document.createElement("button");
            goBtn.textContent="Buka";
            goBtn.onclick=()=>{ window.location.href=item.url; };
            li.appendChild(goBtn);

            const delBtn = document.createElement("button");
            delBtn.textContent="Hapus";
            delBtn.onclick=()=>{ const filtered = b.filter(x=>x.id!==item.id); saveBookmarks(filtered); renderBookmarks(); };
            li.appendChild(delBtn);

            bookmarksList.appendChild(li);
        });
    }

    document.getElementById("add-bookmark-btn").addEventListener("click", ()=>{
        const title = prompt("Judul Modul / Bookmark:", document.title || "Modul Saat Ini");
        if(!title) return;
        const url = window.location.href;
        const b = getBookmarks();
        b.push({id:Date.now(),title,url,createdAt:new Date().toISOString()});
        saveBookmarks(b);
        renderBookmarks();
    });
</script>

</body>
</html>
<?php
}
?>
