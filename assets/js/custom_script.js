jQuery(document).ready(function($){
	$('.productAudio audio').on('play', function(){ 
												 
		var ref = $(this);										 
		var postID = ref.parents(".productAudio").attr("data-id");								 		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_object.ajaxurl,
			data: { 
				'action': 'count_impression',
				'postID': postID
			},
			success: function(data){				
				if(data.status=="success") {
					ref.parents(".productAudio").removeClass("productAudio");	
				}				
				else {
					console.log("Something went wrong");
				}
			}
		});                                               
	}); 						
});