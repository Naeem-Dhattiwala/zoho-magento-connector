require(['jquery','mage/url'],function($,url){
	url.setBaseUrl(BASE_URL);
	var baseurl = url.build('');
	jQuery(document).ready(function() {
	    jQuery("#po_login_btn").click(function(){
		    	var sales_id =(jQuery('#sales_id').val());
		    	if(sales_id != ''){
			    	var url = baseurl+"nvspromo_zohocustomerlogin/index/CheckPoNum?sales_id="+sales_id;
			    	//console.log(url);
			    	jQuery.ajax({
			            url: url,
			            type: "POST",
			        	showLoader: false,
			        	cache: false,
			            success: function(response){
							if (response != '') {
								location.href = baseurl+'order_tracker_view/'+response;
							}
							else{
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