let waterChart=null;
function loadStatus(){
  fetch("api.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=get_status"})
  .then(r=>r.json())
  .then(d=>{
    if(!d.success) return;
    let s=d.data;
    document.getElementById("status").innerText=s.status;
    document.getElementById("leveltext").innerText=s.water_level+"%";
    document.getElementById("levelbar").style.width=s.water_level+"%";
    document.getElementById("battery").innerText=s.battery+"%";
    document.getElementById("signal").innerText=s.signal;
    document.getElementById("valve").innerText=s.valve_state;

    // Real-time chart
    if(!waterChart){
      const ctx=document.getElementById('waterChart').getContext('2d');
      waterChart=new Chart(ctx,{
        type:'line',
        data:{
          labels:[new Date().toLocaleTimeString()],
          datasets:[{label:'Water Level',data:[s.water_level],borderColor:'blue',fill:false}]
        },
        options:{responsive:true,scales:{y:{min:0,max:100}}}
      });
    }else{
      waterChart.data.labels.push(new Date().toLocaleTimeString());
      waterChart.data.datasets[0].data.push(s.water_level);
      if(waterChart.data.labels.length>20){waterChart.data.labels.shift();waterChart.data.datasets[0].data.shift();}
      waterChart.update();
    }

    // Alert
    if(s.water_level<20){
      document.getElementById("alerts").innerText="⚠️ LEAK ALERT! Water below 20%";
      // You can call server API to send SMS/email here
    }else{
      document.getElementById("alerts").innerText="";
    }

    // Auto valve close
    if(s.water_level<10 && s.valve_state=='OPEN'){
      toggleValve();
    }
  });
}

function simulate(type){
  fetch("api.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=simulate_leak&status="+type})
  .then(()=>loadStatus());
}

function toggleValve(){
  let current=document.getElementById("valve").innerText;
  fetch("api.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=toggle_valve&current="+current})
  .then(()=>loadStatus());
}

setInterval(loadStatus,3000);
loadStatus();