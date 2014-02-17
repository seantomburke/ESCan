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

function loadBarcodes(){
    $("#ticker").html('<div class="row center">' +
		'   <h3>Scan Ticker</h3> ' +
		'			</div>' +
		'<div class="list">' +
		'</div>');
    
    $.ajax({
              type: "GET",
              url: "barcodes.php",
              dataType: "json",
              success: function(data) {
                console.log(data); 
                	
                if(data.scans.length > 0)
                {
                    //reverse order
                	for(var i=data.scans.length-1;i>=0; i--)
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
                	//reverse order
                	for(var i=data.scans.length-1;i>=0; i--)
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
    
    //remove the 'null' in the ticker
    if(scan.name == null)
        scan.name = '';
    if(scan.ucinetid == null)
        scan.ucinetid = '';
    if(scan.major == null)
        scan.major = '';
    if(scan.level == null)
        scan.level = '';
        
    var item = $('<div class="item row-tick">' +
			'	<div class="row">' +
			'		<label class="barcode">Barcode: </span> #' + scan.barcode+ '</label>' +
			'		<span class="right">'+ scan.name +'</span>' +
			'		<span class="date">'+ scan.date +'</span>' +
			'		<span class="time">' + scan.time +'</span>' +
			'	</div> ' +
			'</div>' +
			'<div class="event_row">'+
			'	<div class="dropdown drop-tick" style="display:none;">'+
			'		<h5><strong>UCInetID:</strong> 	'+ scan.ucinetid +'</h5>'+
			'		<h5><strong>Name:</strong> 		'+scan.name+'</h5>'+
			'		<h5><strong>Major:</strong> 	'+scan.major+'</h5>'+
			'		<h5><strong>Level:</strong> 	'+scan.level+'</h5>'+
			'		<h5><strong>Time:</strong>		'+ scan.date+' '+scan.time+'</h5>'+
			'	</div>' +
			'</div>');
    
    item.prependTo("#ticker .list")
        .hide()
        .slideDown();
    
    //allows the click toggle switch for each ticker item
    item.click(function(){
            	    $(this).next("div").find("div").slideToggle();
            	});
    
}