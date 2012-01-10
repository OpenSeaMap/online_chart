<script src="javascript.js" type="text/javascript"></script>
<div style="text-align:center;border: 1px solid #C1C1C1; color: #AA0101 !important;background-color: #99CC66;font-size: 11px;-moz-border-radius: 10px;">
<form name="<?php echo $this->config['proxy_url_form_name'] ?>" method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="<?php echo $this->config['url_var_name'] ?>" value="" id="url_input" />
<input type="hidden" name="<?php echo $this->config['flags_var_name'] ?>" value="" />
</form>
<form name="<?php echo $this->config['proxy_settings_form_name'] ?>" action="" method="get" onsubmit="return submit_form();">
[<a href="<?php echo $this->script_url ?>">Main</a>] Current URL: <input type="text" size="66" name="url" value="<?php echo $this->url ?>" />
<input type="submit" name="browse" value="Browse" onclick="return submit_form();" />
<input type="checkbox" name="new_window" /> New Window [<a href="<?php echo $this->script_url ?>?<?php echo $this->config['url_var_name'] ?>=<?php echo encode_url($this->url_segments['prev_dir']) ?>">Up One Directory</a>]<br />
<?php echo $this->options_list() ?>
</form></div><hr style="color: #000066" />