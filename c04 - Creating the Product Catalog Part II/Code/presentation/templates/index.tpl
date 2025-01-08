{* smarty *}
{config_load file="site.conf"}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title>{#site_title#}</title>
        <link href="hatshop.css" type="text/css" rel="stylesheet" />
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  </head>
  <body>
    <div>
      {include file="departments_list.tpl"}
      {include file="$categoriesCell"}
      {include file="header.tpl"}
      <div id="content">
        {include file="$pageContentsCell"}
      </div>
    </div>
  </body>
</html>
