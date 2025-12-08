{* admin_menu.tpl *}
<span class="admin_title">HatShop Admin</span>
<span class="menu_text"> |
  <a href="{"admin.php"|prepare_link:"https"}">CATALOG ADMIN</a> |
{if $features.customer_orders}
  <a href="{"admin.php?Page=Orders"|prepare_link:"https"}">ORDERS ADMIN</a> |
{/if}
  <a href="{"index.php"|prepare_link:"http"}">STOREFRONT</a> |
  <a href="{"admin.php?Page=Logout"|prepare_link:"https"}">LOGOUT</a> |
</span>
<br />
