jQuery(document).ready(function($){

	$('.swp-autocomplete__search input#s').autoComplete({
		source: function(name, response) {
			$.ajax({
	            url: '/wp-admin/admin-ajax.php',
	            dataType: 'json',
	            data: {
	                'action': 'swp/autocomplete/get_terms',
	                'name':	name,
	            },
	            type: 'post',
	            success: function(data){
	                response(data);
	            }
	        });
		}
	});

});
