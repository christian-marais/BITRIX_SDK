<?php
header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
</head>
<body>
    <script> window.addEventListener('b24:form:init', (event) => { let form = event.detail.object; code=new URLSearchParams(window.location.search).get("code"); if (code) {form.setProperty("param1", "1"); form.setProperty("city", "Lyon"); } }); </script> 
    <script data-b24-form="inline/10/42k5cs" data-skip-moving="true">(function(w,d,u){var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/180000|0);var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);})(window,document,'https://bitrix24demoec.ns2b.fr/upload/crm/form/loader_10_42k5cs.js');</script>
</body>
</html>