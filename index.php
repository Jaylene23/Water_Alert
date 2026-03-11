<?php
include 'config.php';
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Water Tank Leak Alert System</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Keep all previous styles from your last code */
:root {
    --primary: #2c3e50;
    --accent: #3498db;
    --danger: #e74c3c;
    --success: #27ae60;
    --warning: #f39c12;
    --bg: #f4f7f6;
    --card-bg: #ffffff;
}
body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin:0; padding:0; color:#333; }
header { background: var(--primary); color:white; padding:15px 20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; }
.tank-info h1 { margin:0; font-size:1.2rem; font-weight:600; }
.tank-info p { margin:0; font-size:0.85rem; opacity:0.8; }
.header-status { font-size:0.8rem; background: rgba(255,255,255,0.2); padding:5px 10px; border-radius:15px; }
.container { max-width: 1200px; margin:20px auto; padding:0 15px; display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:20px; }
.card { background: var(--card-bg); padding:20px; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); transition:transform 0.2s; }
.card:hover { transform: translateY(-2px); }
.card h3 { margin-top:0; color: var(--primary); border-bottom:2px solid #eee; padding-bottom:10px; font-size:1.1rem; }
.status-card { text-align:center; padding:20px; border-radius:10px; margin-bottom:0; transition: all 0.3s ease; border:2px solid transparent; }
.status-safe { background:#d4edda; color:#155724; border-color:#c3e6cb; }
.status-danger { background:#f8d7da; color:#721c24; border-color:#f5c6cb; animation:pulse 1s infinite; }
@keyframes pulse { 0% { transform:scale(1); } 50% { transform:scale(1.02); } 100% { transform:scale(1); } }
.level-container { margin-top:15px; }
.level-label { display:flex; justify-content:space-between; font-size:0.9rem; font-weight:bold; }
.progress-bar { height:20px; background:#e0e0e0; border-radius:10px; overflow:hidden; margin-top:5px; }
.progress-fill { height:100%; background:var(--accent); width:75%; transition:width 0.5s ease,background 0.5s; }
.progress-fill.low { background: var(--warning); }
.progress-fill.critical { background: var(--danger); }
.control-group { display:flex; flex-direction:column; gap:10px; }
button { padding:12px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; font-size:0.95rem; transition:opacity 0.2s; }
button:hover { opacity:0.9; }
.btn-leak { background: var(--danger); color:white; }
.btn-safe { background: var(--success); color:white; }
.btn-valve { background: var(--primary); color:white; }
.btn-valve.closed { background:#555; cursor:not-allowed; }
.health-item { display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.9rem; }
.health-value { font-weight:bold; }
.health-ok { color: var(--success); }
.health-warn { color: var(--warning); }
.table-responsive { overflow-x:auto; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); }
table { width:100%; border-collapse:collapse; min-width:500px; }
th,td { padding:12px; text-align:left; border-bottom:1px solid #000305; }
th { background-color: var(--primary); color:white; }
.badge { padding:4px 8px; border-radius:4px; font-size:11px; font-weight:bold; text-transform:uppercase; }
.badge-leak { background: var(--danger); color:white; }
.badge-safe { background: var(--success); color:white; }
@media(max-width:768px){.container{grid-template-columns:1fr;} header{flex-direction:column;text-align:center;gap:10px;} .header-status{align-self:flex-end;} .container{margin-top:10px;} .card{padding:15px;} h1{font-size:1.1rem;}}
</style>
</head>
<body>

<header>
    <div class="tank-info">
        <h1>🏭 Main Storage Tank</h1>
        <p>Location: NBSC SCHOOL • Capacity: 5000 Liters</p>
    
    </div>
    <div class="header-status">
    System: <span id="headerStatus">Online</span> 
    • <a href="logout.php" style="color:white; text-decoration:underline; font-size:0.8rem;">Logout</a>
</div>
</header>

<div class="container">
    <!-- Status Card -->
    <div class="card">
        <h3>🚨 Live Status</h3>
        <div id="statusDisplay" class="status-card status-safe">
            <h2 id="statusText">System Normal</h2>
            <p id="lastUpdate" style="font-size:0.85em; opacity:0.8;">Waiting for data...</p>
        </div>
    </div>

    <!-- Water Level Card -->
    <div class="card">
        <h3>💧 Water Level</h3>
        <canvas id="waterChart" height="200"></canvas>
        <div class="level-label">
            <span>Current Level</span>
            <span id="levelText">75%</span>
        </div>
        <div class="progress-bar">
            <div id="levelBar" class="progress-fill" style="width:75%;"></div>
        </div>
        <p style="font-size:0.8rem; color:#666; margin-top:5px;">Threshold: <20% triggers alert</p>
    </div>

    <!-- Controls Card -->
    <div class="card">
        <h3>🛠️ Controls</h3>
        <div class="control-group">
            <button class="btn-leak" onclick="simulateStatus('LEAK')">Simulate Leak 🚨</button>
            <button class="btn-safe" onclick="simulateStatus('SAFE')">Simulate Normal ✅</button>
            <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
            <button id="valveBtn" class="btn-valve" onclick="toggleValve()">Close Valve (Open)</button>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card" style="grid-column:1/-1;">
        <h3>📜 Recent Logs</h3>
        <div class="table-responsive">
            <table>
                <thead><tr><th>ID</th><th>Status</th><th>Source</th><th>Time</th></tr></thead>
                <tbody id="logTable"><tr><td colspan="4" style="text-align:center;">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<script>
let waterChart;
let chartLabels = [];
let chartData = [];

// Map water level to color: SAFE / LOW / CRITICAL
function getColor(value){
    if(value < 20) return 'rgba(231,76,60,0.6)';    // RED → CRITICAL
    if(value < 50) return 'rgba(243,156,18,0.6)';   // YELLOW → LOW
    return 'rgba(46,204,113,0.6)';                 // GREEN → SAFE
}

// Initialize Chart.js chart
function initChart(){
    const ctx = document.getElementById('waterChart').getContext('2d');
    waterChart = new Chart(ctx,{
        type:'line',
        data:{
            labels: chartLabels,
            datasets:[{
                label:'Water Level (%)',
                data: chartData,
                backgroundColor: chartData.map(getColor),
                borderColor: chartData.map(getColor),
                fill:true,
                tension:0.2
            }]
        },
        options:{
            responsive:true,
            scales:{ y:{ beginAtZero:true, max:100 } }
        }
    });
}

// Fetch latest tank status from API
function fetchStatus(){
    fetch('api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=get_status'
    }).then(r=>r.json()).then(d=>{
        if(d.success){
            const s = d.data;

            // Update status card
            document.getElementById('statusText').innerText = s.status;
            document.getElementById('lastUpdate').innerText = new Date(s.last_updated).toLocaleString();
            document.getElementById('levelText').innerText = s.water_level+'%';
            document.getElementById('levelBar').style.width = s.water_level+'%';

            const card = document.getElementById('statusDisplay');
            card.className = 'status-card '+(s.water_level<20?'status-danger':'status-safe');

            document.getElementById('valveBtn').innerText = (s.valve_state=='OPEN'?'Close Valve (Open)':'Open Valve (Closed)');

            // Update Chart dynamically
            const now = new Date().toLocaleTimeString();
            if(chartLabels.length>20){ chartLabels.shift(); chartData.shift(); }
            chartLabels.push(now);
            chartData.push(s.water_level);

            waterChart.data.datasets[0].data = chartData;
            waterChart.data.datasets[0].backgroundColor = chartData.map(getColor);
            waterChart.data.datasets[0].borderColor = chartData.map(getColor);
            waterChart.update();
        }
    });
}

// Fetch recent logs
function fetchLogs(){
    fetch('api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=get_logs'
    }).then(r=>r.json()).then(d=>{
        if(d.success){
            const tbody = document.getElementById('logTable');
            tbody.innerHTML='';
            d.logs.forEach(l=>{
                tbody.innerHTML+=`<tr>
                    <td>${l.id}</td>
                    <td>${l.status}</td>
                    <td>${l.source}</td>
                    <td>${new Date(l.created_at).toLocaleString()}</td>
                </tr>`;
            });
        }
    });
}

// Simulate leak or safe status
function simulateStatus(type){
    fetch('api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=simulate_leak&status='+type
    }).then(()=>{ fetchStatus(); fetchLogs(); });
}

// Toggle valve open/close
function toggleValve(){
    const current = document.getElementById('valveBtn').innerText.includes('Open')?'OPEN':'CLOSED';
    fetch('api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=toggle_valve&current_state='+current
    }).then(()=>{ fetchStatus(); });
}

// Initialize everything
initChart();
setInterval(()=>{ fetchStatus(); fetchLogs(); },3000);
fetchStatus();
fetchLogs();
</script>