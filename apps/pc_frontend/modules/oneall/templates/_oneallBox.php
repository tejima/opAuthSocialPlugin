<script type="text/javascript">
 var oneall_js_protocol = (("https:" == document.location.protocol) ? "https" : "http");
 document.write(unescape("%3Cscript src='" + oneall_js_protocol + "://<?php echo opConfig::get('cqc.jp.oneall_subdomain'); ?>.api.oneall.com/socialize/library.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<!-- The plugin will be embedded into this div //-->
<div id="social_login_container"></div>

<script type="text/javascript">
 oneall.api.plugins.social_login.build("social_login_container", {
  'providers' :  ['facebook', 'google', 'twitter'], 
  'css_theme_uri': 'https://oneallcdn.com/css/api/socialize/themes/buildin/signin/large-v1.css',
  'grid_size_x': '1',
  'callback_uri': '<?php echo sfConfig::get('op_base_url'); ?>/member/login/authMode/Social'
 });
</script>