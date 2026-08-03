<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>Absensi Masuk & Pulang</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --primary:#00A39D;
    --primary-dark:#00857f;
    --success:#22c55e;
    --warning:#f59e0b;
    --danger:#ef4444;
    --text:#fff;
    --muted:rgba(255,255,255,.72);
    --glass:rgba(255,255,255,.12);
    --glass-border:rgba(255,255,255,.18);
    --radius:20px;
    --shadow:0 20px 60px rgba(0,0,0,.22);
    --transition:.25s ease;
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Plus Jakarta Sans',sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:28px;
    color:var(--text);
    background:
        radial-gradient(circle at top left,#14C8C0 0%,transparent 35%),
        radial-gradient(circle at bottom right,#00595a 0%,transparent 40%),
        linear-gradient(135deg,#00595a,#00857f,#00A39D);
}

.container{
    width:100%;
    max-width:1200px;
    display:grid;
    grid-template-columns:480px 1fr;
    gap:28px;
}

.card{
    background:var(--glass);
    backdrop-filter:blur(18px);
    border:1px solid var(--glass-border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:28px;
}

.page-title{text-align:center;}
.page-title h1{font-size:26px;font-weight:700;line-height:1.2;}
.page-title p{margin-top:4px;font-size:14px;color:var(--muted);}
.icon-md{width:22px;height:22px;}
.icon-sm{width:18px;height:18px;}

.clock-wrapper{margin:18px 0 22px;text-align:center;}
.clock{font-size:40px;font-weight:800;letter-spacing:2px;}
.clock-date{margin-top:8px;font-size:15px;color:var(--muted);font-weight:500;}

.jenis-group{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    margin-bottom:22px;
}

.jenis-button{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    border:none;
    border-radius:16px;
    padding:20px;
    cursor:pointer;
    font-size:16px;
    font-weight:700;
    color:#fff;
    background:rgba(255,255,255,.10);
    transition:var(--transition);
}

.jenis-button:hover{background:rgba(255,255,255,.18);}

.jenis-button.active-masuk{background:var(--success);color:#fff;}
.jenis-button.active-pulang{background:var(--warning);color:#111827;}

#manualArea{
    background:rgba(255,255,255,.08);
    border:2px dashed rgba(255,255,255,.18);
    border-radius:18px;
    padding:22px;
}

.manual-header{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:15px;
    font-weight:700;
    margin-bottom:14px;
}

#inputScan{
    width:100%;
    border:none;
    outline:none;
    border-radius:14px;
    padding:16px 18px;
    font-size:18px;
    font-family:inherit;
}

#manualArea small{display:block;margin-top:10px;color:var(--muted);font-size:13px;}

#loadingScan{
    position:fixed;
    inset:0;
    display:none;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    background:rgba(3,74,110,.85);
    backdrop-filter:blur(8px);
    z-index:50;
}

.loading-spinner{
    width:60px;height:60px;
    border:5px solid rgba(255,255,255,.25);
    border-top-color:#fff;
    border-radius:50%;
    animation:spin .8s linear infinite;
}

.loading-text{margin-top:18px;font-weight:600;}

.result-card{text-align:center;}

.photo-wrapper{
    width:170px;height:170px;margin:auto;
    border-radius:22px;padding:6px;
    background:rgba(255,255,255,.18);
}

.default-photo{
    width:100%;height:100%;border-radius:18px;
    display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.08);
}

.default-photo-icon{width:100px;height:100px;color:rgba(255,255,255,.45);}

.foto{width:100%;height:100%;object-fit:cover;border-radius:18px;display:none;}

.nama{margin-top:16px;font-size:22px;font-weight:700;}
.status{margin-top:8px;font-size:15px;color:var(--muted);min-height:22px;}

.divider{height:1px;background:rgba(255,255,255,.12);margin:24px 0;}

.riwayat-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
.riwayat-title{display:flex;align-items:center;gap:8px;font-weight:700;}
.riwayat-count{padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.10);font-size:13px;}

.riwayat-table{width:100%;border-collapse:collapse;}
.riwayat-table thead th{padding:12px;text-align:left;font-size:13px;font-weight:700;border-bottom:1px solid rgba(255,255,255,.15);}
.riwayat-table tbody td{padding:12px;border-bottom:1px solid rgba(255,255,255,.08);vertical-align:middle;}
.riwayat-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;}

.empty-state{text-align:center;padding:40px !important;color:var(--muted);}

@keyframes spin{to{transform:rotate(360deg);}}

@media(max-width:1000px){.container{grid-template-columns:1fr;}}
@media(max-width:600px){.jenis-group{grid-template-columns:1fr;}}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <div class="page-title">
            <h1>Absensi Masuk & Pulang</h1>
            <p>Sistem Absensi Harian Siswa & Guru/Pegawai</p>
        </div>

        <div class="clock-wrapper">
            <div id="clock" class="clock">--:--:--</div>
            <div id="tanggal" class="clock-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        </div>

        <div class="jenis-group">
            <button id="btnMasuk" class="jenis-button active-masuk" type="button">
                <x-heroicon-o-arrow-right-on-rectangle class="icon-md"/>
                <span>Absen Masuk</span>
            </button>
            <button id="btnPulang" class="jenis-button" type="button">
                <x-heroicon-o-arrow-left-on-rectangle class="icon-md"/>
                <span>Absen Pulang</span>
            </button>
        </div>

        <div id="manualArea">
            <div class="manual-header">
                <x-heroicon-o-qr-code class="icon-md"/>
                <span>Scan Barcode / RFID / Ketik Manual</span>
            </div>

            <input
                id="inputScan"
                type="text"
                placeholder="Tempatkan kursor di sini lalu scan..."
                autofocus
                autocomplete="off">

            <small>NIS / NISN / RFID / QR Code siswa, atau NIY / RFID / QR Code guru & pegawai.</small>
        </div>

    </div>

    <div class="card">

        <div class="result-card">

            <div class="photo-wrapper">
                <div id="defaultPhoto" class="default-photo">
                    <x-heroicon-o-user-circle class="default-photo-icon"/>
                </div>
                <img id="foto" class="foto" alt="">
            </div>

            <div id="nama" class="nama">Menunggu Scan...</div>
            <div id="status" class="status"></div>

        </div>

        <div class="divider"></div>

        <div class="riwayat-header">
            <div class="riwayat-title">
                <x-heroicon-o-clock class="icon-sm"/>
                <span>Riwayat Hari Ini</span>
            </div>
            <div id="totalRiwayat" class="riwayat-count">0 Data</div>
        </div>

        <table class="riwayat-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nama</th>
                    <th>Jam</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="listRiwayat">
                <tr>
                    <td colspan="4" class="empty-state">Belum ada data absensi.</td>
                </tr>
            </tbody>
        </table>

    </div>

</div>

<div id="loadingScan">
    <div class="loading-spinner"></div>
    <div class="loading-text">Memproses...</div>
</div>

<audio id="beepSuccess" src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" preload="auto"></audio>
<audio id="beepError" src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" preload="auto"></audio>

<script>

const input = document.getElementById('inputScan');
const btnMasuk = document.getElementById('btnMasuk');
const btnPulang = document.getElementById('btnPulang');
const loading = document.getElementById('loadingScan');

let currentJenis = 'masuk';
let scanLock = false;
let scanTimeout = null;

/* Clock */
function updateClock(){
    const now = new Date();
    document.getElementById('clock').innerText =
        now.getHours().toString().padStart(2,'0')+':'+
        now.getMinutes().toString().padStart(2,'0')+':'+
        now.getSeconds().toString().padStart(2,'0');
}
setInterval(updateClock,1000);
updateClock();

/* Toggle Jenis */
function setJenis(jenis){
    currentJenis = jenis;

    btnMasuk.classList.remove('active-masuk');
    btnPulang.classList.remove('active-pulang');

    if(jenis==='masuk'){
        btnMasuk.classList.add('active-masuk');
    }else{
        btnPulang.classList.add('active-pulang');
    }

    input.focus();
}

btnMasuk.addEventListener('click', ()=>setJenis('masuk'));
btnPulang.addEventListener('click', ()=>setJenis('pulang'));

/* Auto focus input */
setInterval(()=>{
    if(document.activeElement !== input){
        input.focus();
    }
},500);

/* Handle Scan Input (debounced, cocok untuk scanner RFID/Barcode) */
input.addEventListener('input', function(){
    clearTimeout(scanTimeout);

    scanTimeout = setTimeout(()=>{
        const code = input.value.trim();

        if(code.length >= 3){
            handleScan(code);
            input.value = '';
        }
    },60);
});

function handleScan(code){
    if(scanLock) return;

    scanLock = true;
    loading.style.display = 'flex';

    sendScan(code);

    setTimeout(()=>{ scanLock = false; },1200);
}

function voice(text){
    if(!window.speechSynthesis) return;
    speechSynthesis.cancel();
    const speech = new SpeechSynthesisUtterance(text);
    speech.lang = 'id-ID';
    speech.rate = .9;
    speechSynthesis.speak(speech);
}

function updateResult(data){
    document.getElementById('nama').innerHTML = data.nama ?? '-';
    document.getElementById('status').innerHTML = data.message ?? '-';

    const foto = document.getElementById('foto');
    const defaultPhoto = document.getElementById('defaultPhoto');

    if(data.foto){
        foto.src = data.foto;
        foto.style.display = 'block';
        defaultPhoto.style.display = 'none';
    }else{
        foto.removeAttribute('src');
        foto.style.display = 'none';
        defaultPhoto.style.display = 'flex';
    }
}

function updateRiwayat(list){
    if(!Array.isArray(list)) return;

    document.getElementById('totalRiwayat').innerHTML = list.length+' Data';

    let html = '';

    list.forEach(item=>{
        html += `
        <tr>
            <td><img src="${item.foto}" class="riwayat-avatar"></td>
            <td>${item.nama} <br><small>${item.tipe}</small></td>
            <td>${item.waktu}</td>
            <td>${item.status}</td>
        </tr>`;
    });

    if(html===''){
        html = `<tr><td colspan="4" class="empty-state">Belum ada data absensi.</td></tr>`;
    }

    document.getElementById('listRiwayat').innerHTML = html;
}

async function sendScan(code){
    try{
        const response = await fetch("{{ route('absensi-harian.scan') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ qr_code: code, jenis: currentJenis })
        });

        const data = await response.json();

        updateResult(data);
        updateRiwayat(data.riwayat);

        if(data.status === 'success'){
            document.getElementById('beepSuccess').play();
            voice(`${data.nama}, ${data.message}`);
        }else{
            document.getElementById('beepError').play();
            voice(data.message);
        }

    }catch(e){
        console.error(e);
        document.getElementById('beepError').play();
        voice('Terjadi kesalahan sistem');
    }finally{
        loading.style.display = 'none';
        input.value = '';
        input.focus();
    }
}

window.addEventListener('load', ()=>{ input.focus(); });

</script>

</body>
</html>
