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

function appendBarcode(scans){
    
    $("#ticker").html('<div class="row center">' +
		'   <h3>Scan Ticker</h3> ' +
		'			</div>' +
		'<div class="list">' +
		'</div>');
		
	
if(scans.length > 0)
{
	
	$.each(scans, function(index, value)
	{
	    $(".list").append(
			'<div class="item" id="row'+index+'">' +
			'	<div class="row">' +
			'		<label class="barcode">Barcode: </span> #' + value.barcode+ '</label>' +
			'		<span class="right">'+ value.name +'</span>' +
			'		<span class="date">'+ value.date +'</span>' +
			'		<span class="time">' + value.time +'</span>' +
			'	</div> ' +
			'</div>' +
			'<div class="event_row">'+
			'	<div class="dropdown" id="drop'+index+'" style="display:none;">'+
			'		<h5><strong>UCInetID:</strong> 	'+ value.ucinetid +'</h5>'+
			'		<h5><strong>Name:</strong> 		'+value.name+'</h5>'+
			'		<h5><strong>Major:</strong> 	'+value.major+'</h5>'+
			'		<h5><strong>Level:</strong> 	'+value.level+'</h5>'+
			'		<h5><strong>Time:</strong>		'+ value.date+' '+value.time+'</h5>'+
			'	</div>' +
			'</div>'+
			'<script>' +
			'$(row'+index+').click(function () {'+
			'	    	$("#drop'+index+'").slideToggle("fast");'+
			'	    });' +
			'<script>'
			);
	});
}
else {
	$(".list").append('<div class="list">'+
	'<div class="item">'+
	'	<label>No Scans Yet</label>'+
	'</div>'+
	'</div>');
}
}