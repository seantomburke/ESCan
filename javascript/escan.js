function setMessage(message, alert){
    
    $("#banner").hide("fast", function()
    {
            $("#banner").html('<div id="delay">' +
                '<div id="message" class="message2 '
                + alert +
                '"><span id="x" class="' 
                + alert + 
                '">X</span> '
                + message +
                '</div></div>'
            );
        	$("#x").click(function(){
        		$("#message").fadeOut("slow");
        	});
        	$("#delay").delay(20000).fadeOut("slow");
        	$("#banner").show();
    });
    
}

function loadTicker(eid){
    $("#ticker").html('<div class="row center">' +
		'   <h3>Scan Ticker</h3> ' +
		'			</div>' +
		'<div class="list">' +
		'</div>');
    
    $.ajax({
              type: "GET",
              url: "ticker.php",
              dataType: "json",
              data: { 
                eid: eid},
              success: function(data) {
                console.log(data); 
                	
                if(data.scans.length > 0)
                {
                	for(var i=0;i<data.scans.length; i++)
                	{
                	    appendBarcode(data.scans[i]);
                	};
                }
                else {
                	$("#ticker .list").append(
                	'<div class="item">'+
                	'	<label>No Scans Yet</label>'+
                	'</div>');
                }
              }
    	    });
    
}

function appendBarcode(scan){
    
    $("#ticker .list").prepend(
			'<div class="item row">' +
			'	<div class="row">' +
			'		<label class="barcode">Barcode: </span> #' + scan.barcode+ '</label>' +
			'		<span class="right">'+ scan.name +'</span>' +
			'		<span class="date">'+ scan.date +'</span>' +
			'		<span class="time">' + scan.time +'</span>' +
			'	</div> ' +
			'</div>' +
			'<div class="event_row">'+
			'	<div class="dropdown drop" style="display:none;">'+
			'		<h5><strong>UCInetID:</strong> 	'+ scan.ucinetid +'</h5>'+
			'		<h5><strong>Name:</strong> 		'+scan.name+'</h5>'+
			'		<h5><strong>Major:</strong> 	'+scan.major+'</h5>'+
			'		<h5><strong>Level:</strong> 	'+scan.level+'</h5>'+
			'		<h5><strong>Time:</strong>		'+ scan.date+' '+scan.time+'</h5>'+
			'	</div>' +
			'</div>'
			);
			
	
    
}