{* smarty *}
{config_load file="site.conf"}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title>{#site_title#}</title>
        <link href="hatshop.css" type="text/css" rel="stylesheet" />
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    {if $features.paypal}
    {literal}
    <script language="JavaScript" type="text/javascript">
    <!--
      var PayPalWindow = null;
      function OpenPayPalWindow(url)
      {
        if ((!PayPalWindow) || PayPalWindow.closed)
          // If the PayPal window doesn't exist, we open it
          PayPalWindow = window.open(url, "cart", "height=300, width=500");
        else
        {
          // If the PayPal window exists, we make it show
          PayPalWindow.location.href = url;
          PayPalWindow.focus();
        }
      }
    // -->
    </script>
    {/literal}
    {/if}
  </head>
  <body>
    <div>
      {include file="departments_list.tpl"}
      {include file="$categoriesCell"}
      {if $features.search}
        {include file="search_box.tpl"}
      {/if}
      {if $features.shopping_cart}
        {include file="$cartSummaryCell"}
      {/if}
      {if $features.paypal}
      <div class="left_box" id="view_cart">
        <input type="button" name="view_cart" value="View Cart"
         onclick="JavaScript:OpenPayPalWindow(&quot;{$paypal_url}?cmd=_cart&amp;business={$paypal_email|escape:'url'}&amp;display=1&amp;return={$paypal_return_url|escape:'url'}&amp;cancel_return={$paypal_cancel_url|escape:'url'}&quot;)" />
      </div>
      {/if}
      {include file="header.tpl"}
      <div id="content">
        {include file="$pageContentsCell"}
      </div>
    </div>
  </body>
</html>
