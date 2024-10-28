jQuery(document).ready(function($){
	
	$('.olt-add-link-chec').live('click',function(){
		id = $(this).attr('id');
		if($(this).attr('checked'))
		{
			$(this).siblings('span').show();
			$(this).siblings('label').hide();
			
			if(id.indexOf('password'))
			{
				$(this).parent().siblings('#add-link-password').show();
			}
					
		}else{
			$(this).siblings('span').hide();
			$(this).siblings('label').show();
			if(id.indexOf('password'))
			{	
				$(this).parent().siblings('#add-link-password').hide();
			}
		}
		
	});
	
	$('.add-link-select-input').live('click',function(){
		//console.log(this.value);
		if(this.value == 'everyone')
		{
			$('.add-link-user-info').hide();
		}
		else{
			$('.add-link-user-info').show();
		} 
		
		});
	
	
	$('.add-link-owner-check').live('click',function(){
		if($(this).attr('checked'))
		{
			$('.add-link-user-info-indent').show();
		}else{
			$('.add-link-user-info-indent').hide();
		}
		
	})
	
	
});