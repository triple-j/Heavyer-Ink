<?php
/**
 * For some reason Google's App Engine wont allow POST to files ending with '.user.js'.  Until
 * we can find out why, we'll use this file to save the POST into the SESSION variable before
 * redirecting to the Userscript.
 */

require('includes/app_top.php');


#setcookie("HERI_REQUEST", json_encode($_REQUEST));
$_SESSION['HERI_REQUEST'] = json_encode($_REQUEST);


define('TIMEOUT', 2);


ob_start();
?>

	<meta http-equiv="Refresh" content="<?=TIMEOUT;?>; url=/heavyerink.user.js">
	<script type="text/javascript">
		window.onload = function() {
			var intervalID = window.setInterval(function(){
				var elm = document.getElementById('countdown');
				var countdown = parseInt( elm.textContent, 10 );
				if ( countdown > 0 ) {
					elm.textContent = countdown - 1;
				} /*else {
					window.location = "/";
				}*/
			}, 1000);
		}
	</script>

<?php
$tmpl_head = ob_get_clean();

ob_start();
?>

	<h1>Getting Your Custom Userscript</h1>
	<p>please wait...(<span id="countdown"><?=TIMEOUT+1;?></span>)</p>

<?php
$tmpl_body = ob_get_clean();


PX_Template::set_region('title', "Redirecting");
PX_Template::set_region('head', $tmpl_head);
PX_Template::set_region('body', $tmpl_body);
PX_Template::out();
