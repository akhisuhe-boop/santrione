<!DOCTYPE html>
<html>
<head>
<title>Scan Absensi</title>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://unpkg.com/html5-qrcode"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>

*{box-sizing:border-box;}

body{
font-family:'Inter',sans-serif;
margin:0;
min-height:100vh;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#059669,#0f766e,#065f46);
color:white;
padding:20px;
}

.container{
width:100%;
max-width:1100px;
display:grid;
grid-template-columns:1fr 1fr;
gap:30px;
}

.card{
background:rgba(255,255,255,0.15);
padding:30px;
border-radius:16px;
backdrop-filter:blur(15px);
}

.title{
font-size:28px;
font-weight:700;
}

.subtitle{
opacity:0.9;
margin-bottom:10px;
}

.badge{
background:#facc15;
color:black;
padding:6px 12px;
border-radius:8px;
font-weight:600;
display:inline-block;
}

.clock{
font-size:36px;
font-weight:700;
margin-top:10px;
}

.tabs button{
border:none;
padding:10px 16px;
margin-right:8px;
border-radius:8px;
cursor:pointer;
background:rgba(255,255,255,0.2);
color:white;
font-weight:600;
}

.tabs button.active{
background:white;
color:#065f46;
}

.scan-box{
margin-top:15px;
background:rgba(255,255,255,0.2);
padding:20px;
border-radius:12px;
text-align:center;
}

input{
width:100%;
padding:12px;
border:none;
border-radius:6px;
font-size:16px;
}

.foto{
width:160px;
height:160px;
object-fit:cover;
border-radius:12px;
border:4px solid white;
display:block;
margin:auto;
}

.nama{
text-align:center;
font-weight:700;
margin-top:10px;
font-size:18px;
}

.status{
text-align:center;
opacity:0.9;
}

.next{
margin-top:8px;
font-size:14px;
}

.divider{
height:1px;
background:rgba(255,255,255,0.4);
margin:10px auto 15px auto;
width:100%;
}

/* ================= TABLE ================= */

.riwayat{
margin-top:20px;
}

.riwayat-table{
width:100%;
border-collapse:collapse;
margin-top:10px;
font-size:14px;
}

.riwayat-table thead tr{
border-bottom:1px solid rgba(255,255,255,0.4);
}

.riwayat-table th{
text-align:left;
padding:8px 6px;
font-weight:600;
}

.riwayat-table td{
padding:8px 6px;
border-bottom:1px solid rgba(255,255,255,0.1);
vertical-align:middle;
}

/* ukuran kolom */

.riwayat-table th:nth-child(1),
.riwayat-table td:nth-child(1){
width:50px;
}

.riwayat-table th:nth-child(3),
.riwayat-table td:nth-child(3){
width:90px;
}

.riwayat-table th:nth-child(4),
.riwayat-table td:nth-child(4){
width:90px;
}

/* avatar */

.riwayat-avatar{
width:32px;
height:32px;
border-radius:50%;
object-fit:cover;
border:2px solid white;
display:block;
}

/* hover */

.riwayat-table tbody tr:hover{
background:rgba(255,255,255,0.05);
}

/* ================= RESPONSIVE ================= */

@media(max-width:900px){

.container{
grid-template-columns:1fr;
}

.card{
padding:20px;
}

.clock{
font-size:28px;
}

.foto{
width:130px;
height:130px;
}

.tabs button{
margin-bottom:8px;
}

}

</style>
</head>

<body>

<div class="container">

<div class="card">

<div class="title">Scan Absensi Siswa & Guru</div>
<div class="subtitle">Kamera / Mesin Barcode / RFID</div>

<div class="badge">
@if($kegiatan)
{{ $kegiatan->templateKegiatan->nama_kegiatan }}
@else
Tidak ada kegiatan aktif
@endif
</div>

<div class="clock" id="clock"></div>

@if(isset($next) && $next)
<div class="next">
Kegiatan berikutnya :
<b>{{ $next->templateKegiatan->nama_kegiatan }}</b>
({{ \Carbon\Carbon::parse($next->jam_mulai)->format('H:i') }})
</div>
@endif

<hr>

<div class="tabs">
<button id="btnCamera" class="active">Kamera</button>
<button id="btnBarcode">Mesin Barcode</button>
<button id="btnRFID">RFID</button>
</div>

<div class="scan-box">
<div id="reader"></div>
<input type="text" id="inputScan" style="display:none">
</div>

</div>

<div class="card">

<img id="foto" class="foto" src="">

<div id="nama" class="nama"></div>
<div id="status" class="status"></div>

<div class="divider"></div>

<div class="riwayat">

<b>Riwayat Absensi</b>

<table class="riwayat-table">

<thead>
<tr>
<th>Foto</th>
<th>Nama</th>
<th>Jam</th>
<th>Status</th>
</tr>
</thead>

<tbody id="listRiwayat"></tbody>

</table>

</div>

</div>

</div>


<audio id="beepSuccess">
<source src="https://actions.google.com/sounds/v1/cartoon/concussive_drum_hit.ogg">
</audio>

<audio id="beepError">
<source src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg">
</audio>


<script>

let scanner=null
let scanLock=false

const input=document.getElementById("inputScan")

const btnCamera=document.getElementById("btnCamera")
const btnBarcode=document.getElementById("btnBarcode")
const btnRFID=document.getElementById("btnRFID")

const reader=document.getElementById("reader")

// JAM DIGITAL
setInterval(()=>{
document.getElementById("clock").innerText =
new Date().toLocaleTimeString('id-ID',{
hour:'2-digit',
minute:'2-digit',
second:'2-digit',
hour12:false
})
},1000)


// SWITCH MODE
function switchMode(mode){

stopCamera()

reader.style.display="none"
input.style.display="none"

btnCamera.classList.remove("active")
btnBarcode.classList.remove("active")
btnRFID.classList.remove("active")

if(mode==="camera"){
btnCamera.classList.add("active")
reader.style.display="block"
startCamera()
}

if(mode==="barcode"){
btnBarcode.classList.add("active")
input.style.display="block"
input.focus()
}

if(mode==="rfid"){
btnRFID.classList.add("active")
input.style.display="block"
input.focus()
}

}


// CAMERA
function startCamera(){

scanner=new Html5Qrcode("reader")

Html5Qrcode.getCameras().then(devices=>{

if(devices.length){

scanner.start(
devices[0].id,
{fps:10,qrbox:250},
(decoded)=>handleScan(decoded)
)

}

})

}

function stopCamera(){

if(scanner){

scanner.stop().then(()=>{
scanner.clear()
scanner=null
})

}

}


// HANDLE SCAN
function handleScan(code){

if(scanLock) return

scanLock=true

code = code.replace(/[\r\n\t]/g,'').trim()

sendScan(code)

setTimeout(()=>{
scanLock=false
},1200)

}


// BARCODE / RFID
let scanTimeout=null;

input.addEventListener("input",function(){

clearTimeout(scanTimeout);

scanTimeout=setTimeout(()=>{

let code=input.value.trim();

if(code.length>=6){

handleScan(code);

input.value="";

}

},80);

});


// SERVER
function sendScan(code){

fetch("{{ route('absensi.scan') }}",{

method:"POST",

headers:{
"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
"Content-Type":"application/json"
},

body:JSON.stringify({
qr_code:code
})

})

.then(r=>r.json())

.then(data=>{

document.getElementById("nama").innerText=data.nama ?? ""
document.getElementById("status").innerText=data.message ?? ""

if(data.foto){
document.getElementById("foto").src=data.foto
}

if(data.status==="success"){
document.getElementById("beepSuccess").play()
voice(data.nama+" "+data.message)
}else{
document.getElementById("beepError").play()
voice(data.message)
}

if(data.riwayat){

let html=""

data.riwayat.forEach(r=>{

html+=`
<tr>
<td><img src="${r.foto}" class="riwayat-avatar"></td>
<td>${r.nama}</td>
<td>${r.waktu}</td>
<td>${r.status}</td>
</tr>
`

})

document.getElementById("listRiwayat").innerHTML=html

}

})

}


// AI VOICE
function voice(text){

let speech=new SpeechSynthesisUtterance(text)

speech.lang="id-ID"
speech.rate=0.9

speechSynthesis.cancel()
speechSynthesis.speak(speech)

}


// BUTTON EVENT
btnCamera.onclick=()=>switchMode("camera")
btnBarcode.onclick=()=>switchMode("barcode")
btnRFID.onclick=()=>switchMode("rfid")


// DEFAULT
switchMode("camera")

</script>

</body>
</html>
