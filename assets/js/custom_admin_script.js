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
				
				if(data.status=="success") {
					var paidCount = ref.parents("tr").find('td.paid-count').html();
					paidTotal = parseInt(paidCount)+parseInt(amount);
					ref.parents("tr").find('td.paid-count').html(paidTotal);
					
					var unpaidCount = ref.parents("tr").find('td.unpaid-count').html();
					unpaidTotal = parseInt(unpaidCount)-parseInt(amount);
					ref.parents("tr").find('td.unpaid-count').html(unpaidTotal);
					
					ref.parents("tr").find('input[type="number"]').val("");
					
					if(unpaidTotal<=0) {
						ref.parents("tr").find('input[type="number"]').attr("readonly","readonly");
						ref.parents("td").addClass("disableRow");
					}
				}
				else if(data.status=="error") {
					alert(data.message);	
				}
				else {
					console.log("Something went wrong");
				}
			}
		});  
	});						
});