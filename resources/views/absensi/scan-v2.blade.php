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

/*==================================================
ROOT
==================================================*/

:root{
    --primary:#18b394;
    --primary-dark:#0e7f68;

    --background:#0a4c47;
    --background-2:#0f6058;

    --card:#0d4f49;
    --card-hover:#12655d;

    --border:rgba(255,255,255,.08);

    --white:#ffffff;
    --text:#ffffff;
    --muted:rgba(255,255,255,.72);

    --success:#22c55e;
    --warning:#f7c948;
    --danger:#ef4444;

    --radius:18px;

    --shadow:0 12px 35px rgba(0,0,0,.28);

    --transition:.25s ease;
}

/*==================================================
RESET
==================================================*/

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
    color:var(--text);

    min-height:100vh;

    background:
        radial-gradient(circle at top left,#167a6d 0%,transparent 30%),
        radial-gradient(circle at bottom right,#084843 0%,transparent 35%),
        linear-gradient(135deg,#0a4c47,#0d5b55,#0c514b);

    padding:22px;
}

/*==================================================
LAYOUT
==================================================*/

.container{
    width:100%;
    max-width:1550px;
    margin:auto;
}

.card{
    background:rgba(255,255,255,.04);
    border:1px solid var(--border);
    border-radius:20px;
    box-shadow:var(--shadow);
}

.section{
    padding:26px;
}

.section-title{
    font-size:24px;
    font-weight:700;
    margin-bottom:22px;
}

.divider{
    height:1px;
    background:rgba(255,255,255,.08);
    margin:22px 0;
}

/*==================================================
HEADER
==================================================*/

.header{
    display:grid;
    grid-template-columns:1fr 640px;
    gap:22px;
    margin-bottom:22px;
}

.brand{
    display:flex;
    align-items:center;
}

.logo{
    width:92px;
    height:92px;
    border-radius:22px;
    background:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:var(--shadow);
}

.logo svg{
    width:52px;
    height:52px;
}

.brand-text{
    margin-left:22px;
}

.brand-text h1{
    font-size:56px;
    font-weight:800;
    line-height:1;
}

.brand-text p{
    margin-top:10px;
    font-size:22px;
    color:var(--muted);
}

.scanner-status{
    margin-left:auto;
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 24px;
    border-radius:16px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.10);
}

.status-dot{
    width:12px;
    height:12px;
    border-radius:50%;
    background:var(--success);
    box-shadow:0 0 14px var(--success);
}

.scanner-status span{
    font-size:24px;
    font-weight:700;
}

/*==================================================
TOP INFO
==================================================*/

.top-info{
    display:grid;
    grid-template-columns:1fr 280px;
    overflow:hidden;
}

.info-box{
    padding:26px;
}

.info-title{
    font-size:17px;
    color:var(--muted);
    margin-bottom:16px;
}

.badge{
    display:inline-flex;
    align-items:center;
    padding:13px 18px;
    border-radius:12px;
    background:var(--warning);
    color:#111827;
    font-size:20px;
    font-weight:700;
}

.server-box{
    padding:26px;
    border-left:1px solid rgba(255,255,255,.08);
}

.server-title{
    font-size:17px;
    color:var(--muted);
}

.server-clock{
    margin-top:14px;
    display:flex;
    align-items:center;
    gap:12px;
}

.server-clock svg{
    width:32px;
    height:32px;
}

.server-time{
    font-size:48px;
    font-weight:800;
    line-height:1;
}

.server-date{
    margin-top:10px;
    font-size:18px;
    color:var(--muted);
}

/*==================================================
MAIN GRID
==================================================*/

.main{
    display:grid;
    grid-template-columns:520px 1fr;
    gap:22px;
    margin-top:22px;
}

/*==================================================
BUTTON
==================================================*/

.mode-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.mode-button{
    height:68px;
    border:none;
    border-radius:14px;
    background:#103d3d;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    cursor:pointer;
    font-family:inherit;
    font-size:20px;
    font-weight:600;
    transition:var(--transition);
}

.mode-button:hover{
    background:#145952;
}

.mode-button.active{
    background:var(--primary);
}

.mode-button svg{
    width:24px;
    height:24px;
}

/*==================================================
FORM
==================================================*/

.form-group{
    margin-bottom:18px;
}

.form-label{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:10px;
    font-size:15px;
    font-weight:600;
}

.form-label svg{
    width:18px;
    height:18px;
}

.form-control{
    width:100%;
    height:56px;
    border:none;
    outline:none;
    border-radius:14px;
    background:#103d3d;
    color:white;
    padding:0 18px;
    font-family:inherit;
    font-size:17px;
    border:1px solid rgba(255,255,255,.08);
}

/*==================================================
SCANNER
==================================================*/

.scanner-box{
    margin-top:18px;
    height:470px;
    background:#000;
    border-radius:18px;
    overflow:hidden;
    border:2px solid rgba(255,255,255,.08);
    position:relative;
}

#reader{
    width:100%;
    height:100%;
}

#reader video{
    width:100%!important;
    height:100%!important;
    object-fit:cover!important;
}

/*==================================================
RESULT
==================================================*/

.result-grid{
    display:grid;
    grid-template-columns:320px 1fr;
    gap:28px;
}

.photo-card{
    padding:24px;
    border-radius:18px;
    background:rgba(255,255,255,.04);
    text-align:center;
}

.photo{
    width:220px;
    height:220px;
    margin:auto;
    border-radius:50%;
    background:rgba(255,255,255,.08);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

.photo img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.photo svg{
    width:120px;
    height:120px;
    opacity:.35;
}

.photo-title{
    margin-top:18px;
    font-size:28px;
    font-weight:700;
}

.photo-subtitle{
    margin-top:8px;
    color:var(--muted);
    font-size:17px;
}

.result-info{
    border-left:1px solid rgba(255,255,255,.08);
    padding-left:28px;
}

.info-item{
    margin-bottom:24px;
}

.info-item label{
    display:block;
    margin-bottom:8px;
    color:var(--muted);
    font-size:15px;
}

.info-item .value{
    font-size:30px;
    font-weight:700;
}

.badge-status{
    display:inline-flex;
    align-items:center;
    padding:10px 16px;
    border-radius:10px;
    background:rgba(255,255,255,.08);
    font-weight:700;
}

/*==================================================
TABLE
==================================================*/

.table-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:24px 26px;
}

.table-header h3{
    font-size:24px;
}

.table-count{
    padding:10px 18px;
    border-radius:12px;
    background:rgba(255,255,255,.05);
    font-weight:700;
}

.table-wrapper{
    padding:0 20px 20px;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table thead{
    background:rgba(255,255,255,.08);
}

.table th{
    padding:16px;
    text-align:left;
    font-size:16px;
}

.table td{
    padding:16px;
    border-top:1px solid rgba(255,255,255,.05);
}

.empty{
    text-align:center;
    padding:70px 0;
    color:var(--muted);
}

.empty svg{
    width:64px;
    height:64px;
    opacity:.35;
    margin-bottom:12px;
}

/*==================================================
FOOTER
==================================================*/

.footer{
    margin-top:22px;
    padding:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    border-radius:18px;
    border:1px solid var(--border);
    background:rgba(255,255,255,.04);
    color:var(--muted);
    font-size:17px;
}

.footer svg{
    width:20px;
    height:20px;
}

/*==================================================
RESPONSIVE
==================================================*/

@media(max-width:1280px){

    .header{
        grid-template-columns:1fr;
    }

    .main{
        grid-template-columns:1fr;
    }

    .result-grid{
        grid-template-columns:1fr;
    }

    .result-info{
        border:none;
        padding:0;
    }

}

</style>

</head>

<body>

<div class="container">

    {{-- =====================================================
        HEADER
    ====================================================== --}}

    <div class="header">
        <div class="brand">
            <div class="logo">
                <x-heroicon-o-qr-code class="icon-logo"/>
            </div>

            <div class="brand-text">
                <h1>Scan Absensi</h1>
                <p>
                    Sistem Absensi Digital Siswa & Guru
                </p>
            </div>
        </div>
        <div class="scanner-status" id="scannerStatus">
            <div class="status-dot"></div>
            <span>
                Scanner Aktif
            </span>
        </div>
    </div>

    {{-- =====================================================
        TOP INFO
    ====================================================== --}}

    <div class="card top-info">
        <div class="info-box">
            <div class="info-title">
                <x-heroicon-o-calendar-days class="icon-sm"/>
                Kegiatan Aktif
            </div>
            <div class="badge">
                @if($kegiatan)
                    {{ $kegiatan->templateKegiatan->nama_kegiatan }}
                @else
                    Tidak Ada Kegiatan

                @endif
            </div>
        </div>        <div class="server-box">

            <div class="server-title">
                Waktu Server
            </div>
            <div class="server-clock">
                <x-heroicon-o-clock class="icon-md"/>
                <div id="clock" class="server-time">
                    --:--:--
                </div>

            </div>

            <div
                class="server-date"
                id="tanggal">

                {{ now()->translatedFormat('l, d F Y') }}

            </div>

        </div>

    </div>

    {{-- =====================================================
        MAIN
    ====================================================== --}}

    <div class="main">

        {{-- ===============================================
            LEFT PANEL
        ================================================ --}}

        <div class="card section">
            <div class="section-title">
                Pilih Mode Scan
            </div>
            <div class="mode-grid">

                <button
                    id="btnCamera"
                    class="mode-button active">
                    <x-heroicon-o-camera/>
                    Kamera
                </button>

                <button
                    id="btnBarcode"
                    class="mode-button">
                    <x-heroicon-o-qr-code/>
                    Barcode
                </button>
                <button
                    id="btnRFID"
                    class="mode-button">
                    <x-heroicon-o-identification/>

                    RFID
                </button>

            </div>

            <div class="divider"></div>

            {{-- CAMERA --}}

            <div
                id="cameraSelector"
                class="form-group">

                <label class="form-label">
                    <x-heroicon-o-camera/>

                    Pilih Kamera

                </label>
                <select
                    id="cameraSelect"
                    class="form-control">
                    <option>
                        Memuat Kamera...
                    </option>
                </select>
            </div>

            {{-- BARCODE RFID --}}

            <div
                id="manualArea"
                style="display:none;">
                <div class="form-group">
                    <label class="form-label">
                        <x-heroicon-o-computer-desktop/>
                        Scan Barcode / RFID
                    </label>
                    <input
                        id="inputScan"
                        class="form-control"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="Silakan scan barcode atau RFID">
                </div>
            </div>

            {{-- PREVIEW --}}

            <div class="scanner-box">
                <div id="reader"></div>
                <div
                    id="loadingScan"
                    style="display:none;">
                </div>
            </div>
        </div>

        {{-- ===============================================
            RIGHT PANEL
        ================================================ --}}

        <div>
            <div class="card result-card">
                <div class="result-grid">
                    {{-- FOTO --}}
                    <div class="photo-card">
                        <div class="photo">

                            <img
                                id="foto"
                                src=""
                                style="display:none;">
                            <div id="defaultPhoto">
                                <x-heroicon-o-user-circle/>
                            </div>
                        </div>
                        <div
                            id="nama"
                            class="photo-title">

                            Belum Ada Scan

                        </div>
                        <div
                            id="status"
                            class="photo-subtitle">

                            Foto siswa akan tampil setelah berhasil scan

                        </div>
                    </div>
                    {{-- INFORMASI --}}
                    <div class="result-info">
                        <div class="info-item">
                            <label>

                                Nama

                            </label>
                            <div
                                class="value"
                                id="infoNama">
                                -
                            </div>
                        </div>
                        <div class="info-item">
                            <label>
                                Status
                            </label>
                            <div
                                id="badgeStatus"
                                class="badge-status">
                                Menunggu Scan
                            </div>
                        </div>
                        <div class="info-item">
                            <label>
                                Waktu Scan
                            </label>
                            <div
                                class="value"
                                id="infoJam">
                                -
                            </div>
                        </div>
                        <div class="info-item">
                            <label>
                                Keterangan
                            </label>
                            <div
                                class="value"
                                id="infoKeterangan">

                                Silakan lakukan scan.

                            </div>
                        </div>
                    </div>
                </div>
            </div>
{{-- =====================================================
    RIWAYAT SCAN
===================================================== --}}

<div class="card history-card">
    <div class="table-header">
        <h3>Riwayat Scan Hari Ini</h3>

        <div id="totalRiwayat" class="table-count">
            0 Data
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table">

            <thead>
                <tr>
                    <th width="60">No</th>
                    <th width="80">Foto</th>
                    <th>Nama</th>
                    <th width="120">Waktu</th>
                    <th width="140">Status</th>
                    <th width="120">Metode</th>
                </tr>
            </thead>

            <tbody id="listRiwayat">
                <tr>
                    <td colspan="6" class="empty">
                        <x-heroicon-o-inbox-stack class="icon-lg"/>
                        <br><br>
                        Belum ada data scan hari ini.
                    </td>
                </tr>
            </tbody>

        </table>
    </div>
</div>

</div>

</div>

{{-- =====================================================
    FOOTER
===================================================== --}}

<div class="footer">
    <x-heroicon-o-information-circle class="icon-sm"/>
    <span>Pastikan QR Code, Barcode, atau RFID berada pada posisi yang benar agar proses scan berjalan dengan optimal.</span>
</div>

</div>

{{-- =====================================================
    AUDIO
===================================================== --}}

<audio id="beepSuccess" preload="auto">
    <source src="https://actions.google.com/sounds/v1/cartoon/concussive_drum_hit.ogg">
</audio>

<audio id="beepError" preload="auto">
    <source src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg">
</audio>

{{-- =====================================================
    JAVASCRIPT
===================================================== --}}

<script>
/*==================================================
| GLOBAL
==================================================*/

let scanner=null;
let cameras=[];
let currentMode='camera';
let selectedCameraId=localStorage.getItem('selectedCamera');
let scanLock=false;
let scanTimeout=null;

/*==================================================
| ELEMENT
==================================================*/

const reader=document.getElementById('reader');
const input=document.getElementById('inputScan');

const cameraSelector=document.getElementById('cameraSelector');
const cameraSelect=document.getElementById('cameraSelect');

const btnCamera=document.getElementById('btnCamera');
const btnBarcode=document.getElementById('btnBarcode');
const btnRFID=document.getElementById('btnRFID');

const loading=document.getElementById('loadingScan');
const scannerStatus=document.getElementById('scannerStatus');

const foto=document.getElementById('foto');
const defaultPhoto=document.getElementById('defaultPhoto');

const nama=document.getElementById('nama');
const status=document.getElementById('status');

const infoNama=document.getElementById('infoNama');
const infoJam=document.getElementById('infoJam');
const infoKeterangan=document.getElementById('infoKeterangan');

const badgeStatus=document.getElementById('badgeStatus');

/*==================================================
| CLOCK
==================================================*/

function updateClock(){
    document.getElementById('clock').innerHTML=new Date().toLocaleTimeString('id-ID',{
        hour:'2-digit',
        minute:'2-digit',
        second:'2-digit',
        hour12:false
    });
}

updateClock();
setInterval(updateClock,1000);

/*==================================================
| STATUS SCANNER
==================================================*/

function setScannerStatus(type,text){

    scannerStatus.querySelector('span').textContent=text;

    const dot=scannerStatus.querySelector('.status-dot');

    dot.style.background='#22c55e';
    dot.style.boxShadow='0 0 12px #22c55e';

    if(type==='loading'){
        dot.style.background='#f59e0b';
        dot.style.boxShadow='0 0 12px #f59e0b';
    }

    if(type==='offline'){
        dot.style.background='#ef4444';
        dot.style.boxShadow='0 0 12px #ef4444';
    }

}

/*==================================================
| LOADING
==================================================*/

function showLoading(){
    loading.style.display='flex';
}

function hideLoading(){
    loading.style.display='none';
}

/*==================================================
| LOAD CAMERA
==================================================*/

async function loadCameraList(){

    try{

        cameras=await Html5Qrcode.getCameras();

        cameraSelect.innerHTML='';

        cameras.forEach(camera=>{

            const option=document.createElement('option');

            option.value=camera.id;

            let label=camera.label||'Camera';

            const lower=label.toLowerCase();

            if(lower.includes('back')||lower.includes('rear')){
                label='📷 Kamera Belakang';
            }else if(lower.includes('front')){
                label='🤳 Kamera Depan';
            }else if(lower.includes('integrated')){
                label='💻 Webcam Internal';
            }

            option.textContent=label;

            cameraSelect.appendChild(option);

        });

        if(selectedCameraId){
            cameraSelect.value=selectedCameraId;
        }else if(cameras.length){
            selectedCameraId=cameras[0].id;
            cameraSelect.value=selectedCameraId;
        }

    }catch(e){
        console.error(e);
    }

}

/*==================================================
| CAMERA CHANGE
==================================================*/

cameraSelect.addEventListener('change',async()=>{

    selectedCameraId=cameraSelect.value;

    localStorage.setItem('selectedCamera',selectedCameraId);

    if(currentMode==='camera'){
        await switchMode('camera');
    }

});

/*==================================================
| START CAMERA
==================================================*/

async function startCamera(){

    try{

        setScannerStatus('loading','Menyalakan Kamera');

        if(!cameras.length){
            await loadCameraList();
        }

        if(!selectedCameraId){
            setScannerStatus('offline','Kamera Tidak Ditemukan');
            return;
        }

        scanner=new Html5Qrcode("reader");

        await scanner.start(
            selectedCameraId,
            {
                fps:10,
                qrbox:{
                    width:280,
                    height:280
                },
                aspectRatio:1.7778
            },
            decoded=>{
                handleScan(decoded);
            },
            error=>{}
        );

        setScannerStatus('online','Scanner Aktif');

    }catch(e){

        console.error(e);

        setScannerStatus('offline','Kamera Tidak Aktif');

    }

}

/*==================================================
| STOP CAMERA
==================================================*/

async function stopCamera(){

    if(!scanner){
        return;
    }

    try{

        const state=scanner.getState();

        if(
            state===Html5QrcodeScannerState.SCANNING ||
            state===Html5QrcodeScannerState.PAUSED
        ){
            await scanner.stop();
        }

    }catch(e){
        console.log(e);
    }

    try{
        await scanner.clear();
    }catch(e){}

    scanner=null;

}

/*==================================================
| SWITCH MODE
==================================================*/

async function switchMode(mode){

    currentMode=mode;

    await stopCamera();

    btnCamera.classList.remove('active');
    btnBarcode.classList.remove('active');
    btnRFID.classList.remove('active');

    reader.style.display='none';
    cameraSelector.style.display='none';
    manualArea.style.display='none';

    hideLoading();

    switch(mode){

        case 'camera':

            btnCamera.classList.add('active');

            cameraSelector.style.display='block';
            reader.style.display='block';

            await startCamera();

        break;

        case 'barcode':

            btnBarcode.classList.add('active');

            manualArea.style.display='block';

            input.value='';

            input.focus();

            setScannerStatus('online','Mode Barcode');

        break;

        case 'rfid':

            btnRFID.classList.add('active');

            manualArea.style.display='block';

            input.value='';

            input.focus();

            setScannerStatus('online','Mode RFID');

        break;

    }

}

/*==================================================
| BUTTON EVENT
==================================================*/

btnCamera.addEventListener('click',()=>{
    switchMode('camera');
});

btnBarcode.addEventListener('click',()=>{
    switchMode('barcode');
});

btnRFID.addEventListener('click',()=>{
    switchMode('rfid');
});
/*==================================================
| HANDLE SCAN
==================================================*/

function handleScan(code){

    if(scanLock){
        return;
    }

    code=String(code)
        .replace(/[\r\n\t]/g,'')
        .trim();

    if(code===''){
        return;
    }

    scanLock=true;

    showLoading();

    sendScan(code);

    setTimeout(()=>{
        scanLock=false;
    },1200);

}

/*==================================================
| BARCODE / RFID
==================================================*/

input.addEventListener('input',()=>{

    clearTimeout(scanTimeout);

    scanTimeout=setTimeout(()=>{

        const code=input.value.trim();

        if(code.length<3){
            return;
        }

        input.value='';

        handleScan(code);

    },50);

});

/*==================================================
| AUTO FOCUS
==================================================*/

setInterval(()=>{

    if(currentMode==='camera'){
        return;
    }

    if(document.activeElement!==input){

        input.focus();

    }

},500);

/*==================================================
| SEND SCAN
==================================================*/

async function sendScan(code){

    try{

        const response=await fetch(
            "{{ route('absensi.scan') }}",
            {
                method:'POST',

                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':document
                        .querySelector('meta[name="csrf-token"]')
                        .content
                },

                body:JSON.stringify({
                    qr_code:code,
                    metode:currentMode
                })

            }
        );

        if(!response.ok){
            throw new Error('HTTP '+response.status);
        }

        const data=await response.json();

        updateResult(data);

        updateHistory(data.riwayat);

    }catch(error){

        console.error(error);

        document.getElementById('beepError').play();

        voice('Terjadi kesalahan sistem.');

        badgeStatus.innerHTML='Error';

        infoKeterangan.innerHTML='Gagal menghubungi server.';

    }finally{

        hideLoading();

        if(currentMode!=='camera'){

            input.value='';

            input.focus();

        }

    }

}

/*==================================================
| UPDATE RESULT
==================================================*/

function updateResult(data){

    nama.textContent=data.nama??'Belum Ada Scan';
    status.textContent=data.message??'';

    infoNama.textContent=data.nama??'-';
    infoJam.textContent=data.waktu??'-';
    infoKeterangan.textContent=data.message??'-';

    badgeStatus.textContent=data.status_label??'Menunggu';

    badgeStatus.style.background='rgba(255,255,255,.08)';
    badgeStatus.style.color='#ffffff';

    if(data.status==='success'){
        badgeStatus.style.background='#16a34a';
    }

    if(data.status==='warning'){
        badgeStatus.style.background='#f59e0b';
    }

    if(data.status==='error'){
        badgeStatus.style.background='#dc2626';
    }

    if(data.foto){

        foto.src=data.foto;
        foto.style.display='block';
        defaultPhoto.style.display='none';

    }else{

        foto.removeAttribute('src');
        foto.style.display='none';
        defaultPhoto.style.display='flex';

    }

    if(data.status==='success'){

        document.getElementById('beepSuccess').play();

        voice(`${data.nama}. ${data.message}`);

    }else{

        document.getElementById('beepError').play();

        voice(data.message);

    }

}

/*==================================================
| UPDATE HISTORY
==================================================*/

function updateHistory(rows){

    if(!Array.isArray(rows)){
        return;
    }

    totalRiwayat.textContent=`${rows.length} Data`;

    let html='';

    rows.forEach((item,index)=>{

        let badgeColor='#6b7280';

        if(item.status==='Hadir'){
            badgeColor='#16a34a';
        }

        if(item.status==='Terlambat'){
            badgeColor='#f59e0b';
        }

        if(item.status==='Izin'){
            badgeColor='#2563eb';
        }

        if(item.status==='Alpa'){
            badgeColor='#dc2626';
        }

        html+=`
        <tr>

            <td>${index+1}</td>

            <td>
                <img
                    src="${item.foto}"
                    class="history-avatar">
            </td>

            <td>${item.nama}</td>

            <td>${item.waktu}</td>

            <td>
                <span
                    style="
                        background:${badgeColor};
                        color:#fff;
                        padding:6px 12px;
                        border-radius:8px;
                        font-size:13px;
                        font-weight:600;
                    ">
                    ${item.status}
                </span>
            </td>

            <td>${item.metode??'-'}</td>

        </tr>
        `;

    });

    if(rows.length===0){

        html=`
        <tr>
            <td colspan="6" class="empty">
                <x-heroicon-o-inbox-stack class="icon-lg"/>
                <br><br>
                Belum ada data scan hari ini.
            </td>
        `;

    }

    listRiwayat.innerHTML=html;

}

/*==================================================
| VOICE
==================================================*/

function voice(text){

    if(!window.speechSynthesis){
        return;
    }

    speechSynthesis.cancel();

    const speech=new SpeechSynthesisUtterance(text);

    speech.lang='id-ID';
    speech.rate=.9;
    speech.pitch=1;

    speechSynthesis.speak(speech);

}

/*==================================================
| START
==================================================*/

window.addEventListener('load',()=>{

    switchMode('camera');

});

</script>

</body>
</html>