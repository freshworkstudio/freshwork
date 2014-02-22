<?php
$page->template = false; ?>
<html>
<head></head>
<body>
	<a href="#" style="font-size:20px; color:blue; text-decoration:underline; text-align:center; display:block; margin-top:200px;" class="agregar"><?php ___("Si no aparece el cuadro de diálogo, haz click aquí"); ?></a>
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script>
	$(document).bind("app.fbinit",function(){
		$(".agregar").click(function(){
			FB.ui({
			  method: 'pagetab',
			  redirect_uri: '<?php echo ABS_URL; ?>'
			}, function(response){
				if(response.tabs_added.length > 0)alert("Aplicación agregada a tu página de facebook");	
			});
		});
		FB.ui({
			  method: 'pagetab',
			  redirect_uri: '<?php echo ABS_URL; ?>'
			}, function(response){
				if(response.tabs_added.length > 0)alert("Aplicación agregada a tu página de facebook");	
			});
	});
	</script>
	<?php echo get_facebook_script(); ?>
</body></html>