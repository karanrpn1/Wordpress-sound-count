jQuery(document).ready(function($){
	$(".pay_now").click(function(){
		
		var ref = $(this);										 
		var postID = ref.parents("tr").attr("data-id");			
		var userID = ref.parents("tr").attr("data-user");
		var amount = ref.parents("tr").find('input[type="number"]').val();
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_object.ajaxurl,
			data: { 
				'action': 'pay_count_impression',
				'postID': postID,
				'userID': userID,
				'amount': amount
			},
			success: function(data){				
				/*if(data.status=="success") {
					ref.parents(".productAudio").removeClass("productAudio");	
				}
				else {
					console.log("Something went wrong");
				}*/
			}
		});  
	});						
});