require(['jquery','mage/url'],function($,url){
	url.setBaseUrl(BASE_URL);
	var baseurl = url.build('');
	jQuery(document).ready(function() {
	    jQuery("a.login_btn").click(function(){
	    	var sales_id =(jQuery('#sales_id').val());
	    	if(sales_id != ''){
		    	var url = baseurl+"zoho_login.php?sales_id="+sales_id;
		    	jQuery.ajax({
		            url: url,
		            type: "POST",
		            contentType: "application/json",
		            dataType: 'json',
		        	showLoader: true,
		        	cache: false,
		            success: function(response){
		                var valid = response.success;
		                if(valid == 1){
							location.href = baseurl+'track_orders/';
						}else{
		                    jQuery('#login_error').css('display','block');
		                    jQuery('#login_error').html('<div class="div_incorrect invalid" style=""><img src="'+baseurl+'/pub/media/logo/in.png"><p>Purchase Order Number Not Exists..</p></div><br/><br/>');
						}
		            }
		        });
		    }else{
		        jQuery('#login_error').css('display','block');
		        jQuery('#login_error').html('<div class="div_incorrect invalid" style=""><img src="'+baseurl+'pub/media/logo/in.png"><p>Please Enter Purchase Order Number.</p></div><br/><br/>');
	        }                    
	    	return false;
	   	});
	});
});