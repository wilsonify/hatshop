{* smarty *}
{config_load file="site.conf"}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
 "https://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title>{#site_title#} - Admin</title>
    <link href={"hatshop.css"|asset_url} type="text/css" rel="stylesheet" />
    <link rel="icon" href={"images/favicon.ico"|asset_url} type="image/x-icon">
  </head>
  <body>
    <div>
      <br />
      {include file="$pageMenuCell"}
    </div>
    <div>
      {include file="$pageContentsCell"}
    </div>
  </body>
</html>
