{load_product assign="product"}
<span class="description">{$product->mProduct.name}</span>
<br /><br />
<img src="product_images/{$product->mProduct.image}"
     alt="Product image" width="190" border="0" />
<br /><br />
<span>
  {$product->mProduct.description}
  <br /><br />Price:
</span>
{if $product->mProduct.discounted_price == 0}
<span class="price">
{else}
<span class="old_price">
{/if}
  ${$product->mProduct.price}
</span>
{if $product->mProduct.discounted_price != 0}
<span class="price">
  &nbsp;${$product->mProduct.discounted_price}
</span>
{/if}
<br /><br />
{if $features.paypal}
{* PayPal Add to Cart Button using JavaScript popup *}
<input type="button" name="add_to_cart" value="Add to Cart"
 onclick="JavaScript:OpenPayPalWindow(&quot;{$paypal_url}?cmd=_cart&amp;business={$paypal_email|escape:'url'}&amp;item_name={$product->mProduct.name|escape:'url'}&amp;amount={if $product->mProduct.discounted_price != 0}{$product->mProduct.discounted_price}{else}{$product->mProduct.price}{/if}&amp;currency={$paypal_currency_code}&amp;add=1&amp;return={$paypal_return_url|escape:'url'}&amp;cancel_return={$paypal_cancel_url|escape:'url'}&quot;)" />
&nbsp;
{elseif $features.shopping_cart}
{* Shopping Cart Add to Cart Button *}
<input type="button" name="add_to_cart" value="Add to Cart"
 onclick="window.location='{$product->mAddToCartLink|prepare_link:'http'}';" />
&nbsp;
{/if}
<input type="button" value="Continue Shopping"
 onclick="window.location='{$product->mPageLink|prepare_link:"http"}';" />
