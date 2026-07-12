<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>Scan Absensi</title>

<script src="https://unpkg.com/html5-qrcode"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --primary:#10b981;
    --primary-dark:#047857;
    --secondary:#0f766e;
    --success:#22c55e;
    --warning:#f59e0b;
    --danger:#ef4444;
    --white:#fff;
    --text:#fff;
    --muted:rgba(255,255,255,.72);
    --glass:rgba(255,255,255,.12);
    --glass-border:rgba(255,255,255,.18);
    --radius:20px;
    --shadow:0 20px 60px rgba(0,0,0,.22);
    --transition:.25s ease;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html{
    scroll-behavior:smooth;
}

body{
    font-family:'Plus Jakarta Sans',sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:28px;
    color:var(--text);
    background:
        radial-gradient(circle at top left,#10b981 0%,transparent 35%),
        radial-gradient(circle at bottom right,#065f46 0%,transparent 40%),
        linear-gradient(135deg,#065f46,#0f766e,#059669);
}

.container{
    width:100%;
    max-width:1450px;
    display:grid;
    grid-template-columns:520px 1fr;
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

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
}

.page-title{
    display:flex;
    align-items:center;
    gap:16px;
}

.page-icon{
    width:58px;
    height:58px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:16px;
    background:rgba(255,255,255,.18);
}

.page-icon svg{
    width:30px;
    height:30px;
}

.page-title h1{
    font-size:30px;
    font-weight:700;
    line-height:1.2;
}

.page-title p{
    margin-top:4px;
    font-size:14px;
    color:var(--muted);
}

.icon-xl{width:32px;height:32px;}
.icon-lg{width:26px;height:26px;}
.icon-md{width:22px;height:22px;}
.icon-sm{width:18px;height:18px;}

.scanner-status{
    display:flex;
    align-items:center;
    gap:8px;
    padding:10px 16px;
    border-radius:999px;
    font-size:13px;
    font-weight:600;
}

.scanner-online{
    background:rgba(34,197,94,.18);
    color:#bbf7d0;
}

.scanner-offline{
    background:rgba(239,68,68,.18);
    color:#fecaca;
}

.scanner-loading{
    background:rgba(245,158,11,.18);
    color:#fde68a;
}

/* =========================================================
   INFO CARD
========================================================= */

.info-card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:18px;
    padding:20px;
    margin-bottom:22px;
}

.info-label{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    font-weight:600;
    color:var(--muted);
    margin-bottom:12px;
}

.badge-kegiatan{
    display:inline-flex;
    align-items:center;
    padding:8px 14px;
    border-radius:999px;
    background:#facc15;
    color:#111827;
    font-size:14px;
    font-weight:700;
}

.clock-wrapper{
    margin:18px 0 24px;
}

.clock{
    font-size:42px;
    font-weight:800;
    letter-spacing:2px;
    line-height:1;
}

.clock-date{
    margin-top:10px;
    font-size:16px;
    color:rgba(255,255,255,.72);
    font-weight:500;
}

.next-card{
    margin-top:14px;
    padding:14px 16px;
    border-radius:14px;
    background:rgba(255,255,255,.08);
}

.next-title{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    color:var(--muted);
    margin-bottom:6px;
}

/* =========================================================
   MODE BUTTON
========================================================= */

.mode-group{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
    margin-bottom:22px;
}

.mode-button{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:8px;
    border:none;
    border-radius:16px;
    padding:16px;
    cursor:pointer;
    color:#fff;
    background:rgba(255,255,255,.10);
    transition:var(--transition);
}

.mode-button:hover{
    background:rgba(255,255,255,.18);
    transform:translateY(-2px);
}

.mode-button.active{
    background:#fff;
    color:#047857;
}

/* =========================================================
   SCAN AREA
========================================================= */

.camera-selector{
    margin-bottom:16px;
}

.camera-selector label{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    font-weight:600;
    margin-bottom:8px;
}

.camera-selector select{
    width:100%;
    padding:12px 14px;
    border:none;
    outline:none;
    border-radius:14px;
    font-size:14px;
    font-family:inherit;
    background:#fff;
    color:#111827;
}
.scan-area{
    position:relative;
    min-height:360px;
    background:rgba(255,255,255,.08);
    border:2px dashed rgba(255,255,255,.18);
    border-radius:18px;
    padding:18px;
}

#reader{
    width:100%;
}

#reader video{
    border-radius:14px;
}

#manualArea{
    display:flex;
    flex-direction:column;
    justify-content:center;
    height:100%;
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
    padding:15px 18px;
    font-size:17px;
    font-family:inherit;
}

#manualArea small{
    margin-top:10px;
    color:var(--muted);
    font-size:13px;
}

#loadingScan{
    position:absolute;
    inset:0;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    background:rgba(6,95,70,.82);
    backdrop-filter:blur(8px);
    border-radius:18px;
    z-index:20;
}

.loading-spinner{
    width:60px;
    height:60px;
    border:5px solid rgba(255,255,255,.25);
    border-top-color:#fff;
    border-radius:50%;
    animation:spin .8s linear infinite;
}

.loading-text{
    margin-top:18px;
    font-weight:600;
}

/* =========================================================
   RESULT CARD
========================================================= */

.result-card{
    text-align:center;
}

.photo-wrapper{
    width:180px;
    height:180px;
    margin:auto;
    border-radius:22px;
    padding:6px;
    background:rgba(255,255,255,.18);
}

.default-photo{

    width:100%;
    height:100%;

    border-radius:18px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:rgba(255,255,255,.08);

}

.default-photo-icon{

    width:110px;
    height:110px;

    color:rgba(255,255,255,.45);

}

.foto{
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:18px;

    display:none;
}

.nama{
    margin-top:18px;
    font-size:24px;
    font-weight:700;
}

.status{
    margin-top:8px;
    font-size:15px;
    color:var(--muted);
    min-height:22px;
}

.divider{
    height:1px;
    background:rgba(255,255,255,.12);
    margin:26px 0;
}

/* =========================================================
   RIWAYAT
========================================================= */

.riwayat-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
}

.riwayat-title{
    display:flex;
    align-items:center;
    gap:8px;
    font-weight:700;
}

.riwayat-count{
    padding:6px 12px;
    border-radius:999px;
    background:rgba(255,255,255,.10);
    font-size:13px;
}

.riwayat-table{
    width:100%;
    border-collapse:collapse;
}

.riwayat-table thead th{
    padding:12px;
    text-align:left;
    font-size:13px;
    font-weight:700;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.riwayat-table tbody td{
    padding:12px;
    border-bottom:1px solid rgba(255,255,255,.08);
    vertical-align:middle;
}

.riwayat-table tbody tr{
    transition:.2s;
}

.riwayat-table tbody tr:hover{
    background:rgba(255,255,255,.05);
}

.riwayat-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
}

.empty-state{
    text-align:center;
    padding:40px !important;
    color:var(--muted);
}

/* =========================================================
   ANIMATION
========================================================= */

@keyframes spin{
    to{
        transform:rotate(360deg);
    }
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1100px){

    .container{
        grid-template-columns:1fr;
    }

}

@media(max-width:768px){

    body{
        padding:16px;
    }

    .card{
        padding:20px;
    }

    .page-title h1{
        font-size:24px;
    }

    .clock{
        font-size:32px;
    }

    .photo-wrapper{
        width:150px;
        height:150px;
    }

    .mode-group{
        grid-template-columns:1fr;
    }

}

</style>

</head>

<div class="container">

    {{-- =========================================================
        PANEL KIRI
    ========================================================== --}}

    <div class="card">

        {{-- HEADER --}}

        <div class="page-header">

            <div class="page-title">

                <div class="page-icon">
                    <x-heroicon-o-qr-code class="icon-xl"/>
                </div>

                <div>

                    <h1>Scan Absensi</h1>

                    <p>
                        Sistem Absensi Digital Siswa & Guru
                    </p>

                </div>

            </div>

            <div
                id="scannerStatus"
                class="scanner-status scanner-offline">

                <x-heroicon-o-signal class="icon-sm"/>

                <span>
                    Offline
                </span>

            </div>

        </div>

        {{-- INFORMASI KEGIATAN --}}

        <div class="info-card">

            <div class="info-label">

                <x-heroicon-o-calendar-days class="icon-sm"/>

                <span>
                    Kegiatan Aktif
                </span>

            </div>

            <div class="badge-kegiatan">

                @if($kegiatan)

                    {{ $kegiatan->templateKegiatan->nama_kegiatan }}

                @else

                    Tidak Ada Kegiatan Aktif

                @endif

            </div>

            <div class="clock-wrapper">

            <div
                id="clock"
                class="clock">
        
                --:--:--
        
            </div>
        
            <div
                id="tanggal"
                class="clock-date">
        
                {{ now()->translatedFormat('l, d F Y') }}
        
            </div>

</div>

            @if(isset($next) && $next)

                <div class="next-card">

                    <div class="next-title">

                        <x-heroicon-o-clock class="icon-sm"/>

                        <span>
                            Kegiatan Berikutnya
                        </span>

                    </div>

                    <strong>

                        {{ $next->templateKegiatan->nama_kegiatan }}

                    </strong>

                    <br>

                    <span>

                        {{ \Carbon\Carbon::parse($next->jam_mulai)->format('H:i') }}

                    </span>

                </div>

            @endif

        </div>

        {{-- MODE SCAN --}}

        <div class="mode-group">

            <button
                id="btnCamera"
                class="mode-button active">

                <x-heroicon-o-camera class="icon-md"/>

                <span>
                    Kamera
                </span>

            </button>

            <button
                id="btnBarcode"
                class="mode-button">

                <x-heroicon-o-qr-code class="icon-md"/>

                <span>
                    Barcode
                </span>

            </button>

            <button
                id="btnRFID"
                class="mode-button">

                <x-heroicon-o-identification class="icon-md"/>

                <span>
                    RFID
                </span>

            </button>

        </div>

        {{-- AREA SCAN --}}

        <div class="scan-area">

            {{-- CAMERA --}}
            <div id="cameraSelector" class="camera-selector">
            
                <label for="cameraSelect">
            
                    <x-heroicon-o-camera class="icon-sm"/>
            
                    <span>Pilih Kamera</span>
            
                </label>
            
                <select id="cameraSelect">
            
                    <option value="">
                        Memuat daftar kamera...
                    </option>
            
                </select>
            
            </div>

            <div id="reader"></div>

            {{-- BARCODE / RFID --}}

            <div
                id="manualArea"
                style="display:none;">

                <div class="manual-header">

                    <x-heroicon-o-computer-desktop class="icon-md"/>

                    <span>

                        Scan Menggunakan Barcode / RFID

                    </span>

                </div>

                <input
                    id="inputScan"
                    type="text"
                    autocomplete="off"
                    spellcheck="false"
                    placeholder="Silakan scan Barcode atau RFID...">

                <small>

                    Fokus akan otomatis diarahkan ke kolom ini.

                </small>

            </div>

            {{-- LOADING --}}

            <div
                id="loadingScan"
                style="display:none;">

                <div class="loading-spinner"></div>

                <div class="loading-text">

                    Memproses Scan...

                </div>

            </div>

        </div>

    </div>
    
        {{-- =========================================================
        PANEL KANAN
    ========================================================== --}}

    <div class="card">

        {{-- HASIL SCAN --}}

        <div class="result-card">

            <div class="photo-wrapper">

                <div id="defaultPhoto" class="default-photo">
            
                    <x-heroicon-o-user-circle class="default-photo-icon"/>
            
                </div>
            
                <img
                    id="foto"
                    class="foto"
                    src=""
                    alt="Foto Siswa"
                    style="display:none;">
            
            </div>

            <div
                id="nama"
                class="nama">

                Menunggu Scan...

            </div>

            <div
                id="status"
                class="status">

                Silakan pilih mode scanner kemudian lakukan scan.

            </div>

        </div>

        <div class="divider"></div>

        {{-- RIWAYAT ABSENSI --}}

        <div class="riwayat">

            <div class="riwayat-header">

                <div class="riwayat-title">

                    <x-heroicon-o-clock class="icon-sm"/>

                    <span>
                        Riwayat Absensi
                    </span>

                </div>

                <div
                    id="totalRiwayat"
                    class="riwayat-count">

                    0 Data

                </div>

            </div>

            <table class="riwayat-table">

                <thead>

                    <tr>

                        <th width="60">
                            Foto
                        </th>

                        <th>
                            Nama
                        </th>

                        <th width="90">
                            Jam
                        </th>

                        <th width="120">
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody id="listRiwayat">

                    <tr>

                        <td
                            colspan="4"
                            class="empty-state">

                            <x-heroicon-o-inbox-stack class="icon-lg"/>

                            <br><br>

                            Belum ada data absensi.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

{{-- =========================================================
    AUDIO
========================================================== --}}

<audio id="beepSuccess" preload="auto">
    <source src="https://actions.google.com/sounds/v1/cartoon/concussive_drum_hit.ogg">
</audio>

<audio id="beepError" preload="auto">
    <source src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg">
</audio>

{{-- =========================================================
    JAVASCRIPT
========================================================== --}}

<script>
    
/*
|--------------------------------------------------------------------------
| Scan Absensi
|--------------------------------------------------------------------------
*/

let scanner = null;
let scanLock = false;
let scanTimeout = null;
let currentMode = 'camera';

let cameras=[];
let selectedCameraId=localStorage.getItem('selectedCamera');

/*
|--------------------------------------------------------------------------
| Element
|--------------------------------------------------------------------------
*/

const reader = document.getElementById('reader');
const input = document.getElementById('inputScan');
const cameraSelector = document.getElementById('cameraSelector');
const cameraSelect = document.getElementById('cameraSelect');

const btnCamera = document.getElementById('btnCamera');
const btnBarcode = document.getElementById('btnBarcode');
const btnRFID = document.getElementById('btnRFID');

const loading = document.getElementById('loadingScan');

const scannerStatus = document.getElementById('scannerStatus');

/*
|--------------------------------------------------------------------------
| Clock
|--------------------------------------------------------------------------
*/

function updateClock(){

    document.getElementById('clock').innerHTML =
        new Date().toLocaleTimeString('id-ID',{
            hour:'2-digit',
            minute:'2-digit',
            second:'2-digit',
            hour12:false
        });

}

updateClock();

setInterval(updateClock,1000);

/*
|--------------------------------------------------------------------------
| Scanner Status
|--------------------------------------------------------------------------
*/

function setScannerStatus(type,text){

    scannerStatus.className='scanner-status';

    switch(type){

        case 'online':
            scannerStatus.classList.add('scanner-online');
            break;

        case 'loading':
            scannerStatus.classList.add('scanner-loading');
            break;

        default:
            scannerStatus.classList.add('scanner-offline');

    }

    scannerStatus.querySelector('span').innerHTML=text;

}

/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

function showLoading(){

    loading.style.display='flex';

}

function hideLoading(){

    loading.style.display='none';

}

/*
|--------------------------------------------------------------------------
| Camera
|--------------------------------------------------------------------------
*/
async function loadCameraList(){
    try{

        cameras = await Html5Qrcode.getCameras();
        cameraSelect.innerHTML='';
        cameras.forEach(camera=>{
            const option=document.createElement('option');
            option.value=camera.id;
            option.textContent=camera.label || camera.id;
            cameraSelect.appendChild(option);
        });

        if(selectedCameraId){
            cameraSelect.value=selectedCameraId;
        }else{
            const back=cameras.find(c=>{
                const label=(c.label || '').toLowerCase();
                return label.includes('back') ||
                       label.includes('rear') ||
                       label.includes('environment');

            });

            if(back){

                cameraSelect.value=back.id;
                selectedCameraId=back.id;

            }else if(cameras.length){
                cameraSelect.value=cameras[0].id;
                selectedCameraId=cameras[0].id;
            }
        }
    }catch(e){
        console.error(e);
    }
}

async function startCamera(){

    try{

        setScannerStatus('loading','Menyalakan Kamera');
        scanner = new Html5Qrcode("reader");
        
        if(!cameras.length){
            await loadCameraList();
        
        }
        
        if(!selectedCameraId){
            setScannerStatus('offline','Kamera Tidak Ditemukan');
            return;
        
        }
        
        await scanner.start(
        selectedCameraId,

            {
                fps:10,
                qrbox:{
                    width:250,
                    height:250
                }
            },

            decoded=>{
                handleScan(decoded);
            },

            error=>{}

        );

        setScannerStatus('online','Kamera Aktif');

    }

    catch(e){

        console.log(e);

        setScannerStatus('offline','Kamera Tidak Aktif');

    }

}

/*
|--------------------------------------------------------------------------
| Stop Camera
|--------------------------------------------------------------------------
*/

async function stopCamera(){

    if(!scanner) return;

    try{

        const state=scanner.getState();

        if(
            state===Html5QrcodeScannerState.SCANNING ||
            state===Html5QrcodeScannerState.PAUSED
        ){

            await scanner.stop();

        }

    }

    catch(e){

        console.log(e);

    }

    try{

        await scanner.clear();

    }

    catch(e){}

    scanner=null;

}

/*
|--------------------------------------------------------------------------
| Switch Mode
|--------------------------------------------------------------------------
*/

async function switchMode(mode){

    currentMode=mode;

    await stopCamera();

    btnCamera.classList.remove('active');
    btnBarcode.classList.remove('active');
    btnRFID.classList.remove('active');

    reader.style.display='none';
    cameraSelector.style.display='none';
    
    document.getElementById('manualArea').style.display='none';

    if(mode==='camera'){
        btnCamera.classList.add('active');
    
        cameraSelector.style.display='block';
        reader.style.display='block';
    
        startCamera();
    
    }

    if(mode==='barcode'){

        btnBarcode.classList.add('active');
        document.getElementById('manualArea').style.display='block';
        input.focus();
        setScannerStatus('online','Mode Barcode');

    }

    if(mode==='rfid'){

        btnRFID.classList.add('active');
        document.getElementById('manualArea').style.display='block';
        input.focus();
        setScannerStatus('online','Mode RFID');

    }

}

/*
|--------------------------------------------------------------------------
| Handle Scan
|--------------------------------------------------------------------------
*/

function handleScan(code){

    if(scanLock) return;

    code = String(code)
        .replace(/[\r\n\t]/g,'')
        .trim();

    if(code.length < 3){
        return;
    }

    scanLock = true;

    showLoading();

    sendScan(code);

    setTimeout(()=>{
        scanLock = false;
    },1200);

}

/*
|--------------------------------------------------------------------------
| Barcode / RFID Input
|--------------------------------------------------------------------------
*/

input.addEventListener('input',function(){

    clearTimeout(scanTimeout);

    scanTimeout = setTimeout(()=>{

        const code = input.value.trim();

        if(code.length >= 3){

            handleScan(code);

            input.value='';

        }

    },60);

});

/*
|--------------------------------------------------------------------------
| Auto Focus
|--------------------------------------------------------------------------
*/

setInterval(()=>{

    if(
        currentMode !== 'camera' &&
        document.activeElement !== input
    ){
        input.focus();
    }

},500);

/*
|--------------------------------------------------------------------------
| Voice
|--------------------------------------------------------------------------
*/

function voice(text){

    if(!window.speechSynthesis) return;

    speechSynthesis.cancel();

    const speech = new SpeechSynthesisUtterance(text);

    speech.lang='id-ID';
    speech.rate=.9;
    speech.pitch=1;

    speechSynthesis.speak(speech);

}

/*
|--------------------------------------------------------------------------
| Update Result
|--------------------------------------------------------------------------
*/

function updateResult(data){

    document.getElementById('nama').innerHTML =
        data.nama ?? '-';

    document.getElementById('status').innerHTML =
        data.message ?? '-';

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

/*
|--------------------------------------------------------------------------
| Update Riwayat
|--------------------------------------------------------------------------
*/

function updateRiwayat(list){

    if(!Array.isArray(list)) return;

    document.getElementById('totalRiwayat').innerHTML =
        list.length+' Data';

    let html='';

    list.forEach(item=>{

        html+=`
        <tr>

            <td>

                <img
                    src="${item.foto}"
                    class="riwayat-avatar">

            </td>

            <td>

                ${item.nama}

            </td>

            <td>

                ${item.waktu}

            </td>

            <td>

                ${item.status}

            </td>

        </tr>
        `;

    });

    if(html===''){

        html=`
        <tr>

            <td colspan="4" class="empty-state">

                Belum ada data absensi.

            </td>

        </tr>
        `;

    }

    document.getElementById('listRiwayat').innerHTML=html;

}

/*
|--------------------------------------------------------------------------
| Kirim ke Laravel
|--------------------------------------------------------------------------
*/

async function sendScan(code){

    try{

        const response = await fetch(
            "{{ route('absensi.scan') }}",
            {
                method:'POST',

                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':
                        document
                        .querySelector('meta[name="csrf-token"]')
                        .content
                },

                body:JSON.stringify({
                    qr_code:code
                })
            }
        );

        const data = await response.json();

        updateResult(data);

        updateRiwayat(data.riwayat);

        if(data.status==='success'){

            document
                .getElementById('beepSuccess')
                .play();

            voice(
                `${data.nama}, ${data.message}`
            );

        }else{

            document
                .getElementById('beepError')
                .play();

            voice(data.message);

        }

    }

    catch(e){

        console.error(e);

        document
            .getElementById('beepError')
            .play();

        voice('Terjadi kesalahan sistem');

    }

    finally{

        hideLoading();

        if(currentMode!=='camera'){

            input.value='';

            input.focus();

        }

    }

}

/*
|--------------------------------------------------------------------------
| Button Event
|--------------------------------------------------------------------------
*/

btnCamera.addEventListener('click',()=>{

    switchMode('camera');

});

btnBarcode.addEventListener('click',()=>{

    switchMode('barcode');

});

btnRFID.addEventListener('click',()=>{
    switchMode('rfid');
});

cameraSelect.addEventListener('change',async function(){
    selectedCameraId=this.value;

    localStorage.setItem(
        'selectedCamera',
        selectedCameraId
    );

    if(currentMode==='camera'){
        await switchMode('camera');
    }

});

/*
|--------------------------------------------------------------------------
| Start
|--------------------------------------------------------------------------
*/

window.addEventListener('load',()=>{

    switchMode('camera');

});
</script>

</body>
</html>