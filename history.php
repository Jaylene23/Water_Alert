<?php
include 'config.php';
if(!isLoggedIn()) header("Location:login.php");
?>
<!DOCTYPE html>
<html>
<head><title>Leak History</title></head>
<body>
<h2>Leak History</h2>
<table border="1" width="100%">
<tr><th>ID</th><th>Status</th><th>Source</th><th>Date</th></tr>
<tbody id="logs"><tr><td colspan="4">Loading...</td></tr></tbody>
</table>
<script>
function loadLogs(){
  fetch("api.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=get_logs"})
  .then(r=>r.json()).then(d=>{
    let rows='';
    d.logs.forEach(l=>{
      rows+=`<tr><td>${l.id}</td><td>${l.status}</td><td>${l.source}</td><td>${l.created_at}</td></tr>`;
    });
    document.getElementById("logs").innerHTML=rows;
  });
}
loadLogs();
setInterval(loadLogs,5000);
</script>
</body>
</html>