<?php if(isset($_POST['saveSettings'])) { 
	update_option('sound_plugin_email_template',htmlentities(wpautop($_POST['sound_plugin_email_template'])));
	update_option('sound_plugin_range_constant',$_POST['sound_plugin_range_constant']);
	$_SESSION['SAVE']="Settings saved successfully.";
}
?>
<div class="wrap">
	<?php if(isset($_SESSION['SAVE'])) { ?>
    	<div class="success-div">
        	<?=$_SESSION['SAVE']?>
        </div>
    <?php unset($_SESSION['SAVE']); } ?>
	<h2>Sound Settings</h2>
    <div class="form-widget">
    	<form method="post">
        
        <div class="form-group">
        	<label><strong>Send Email Counter:</strong></label>
        	<input type="number" class="form-control" name="sound_plugin_range_constant" value="<?=get_option('sound_plugin_range_constant')?>" placeholder="Enter default email range constant."/>
        </div>
    	
        <div class="form-group mb15">
        	<label><strong>Email Content:</strong></label>
			<?php
			$default_content=html_entity_decode(get_option('sound_plugin_email_template'));
			$default_content=stripslashes($default_content);
			wp_editor($default_content,'sound_plugin_email_template',array('textarea_rows'=>15)); ?>
            
            <small> <strong>Note: Use these costant to get dynamic value on email </strong> ( For Username use {{USERNAME}} , For total view of mp3 use {{TOTALVIEWS}}, For post title use {{POSTTITLE}} )  </small>
        </div>
        
        <input type="submit" name="saveSettings" value="SAVE"/>
        </form>
    </div>
</div>