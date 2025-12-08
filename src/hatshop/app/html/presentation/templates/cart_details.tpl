{* cart_details.tpl - Full shopping cart page *}
{load_cart_details assign="cart"}
<h1>Your Shopping Cart</h1>

{if $cart->mIsCartNowEmpty}
  <p>Your shopping cart is empty!</p>
{else}
  <form method="post" action="{$cart->mCartDetailsTarget|prepare_link:'https'}">
    <table class="cart_content" cellspacing="0">
      <tr>
        <th>Product Name</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Subtotal</th>
        <th>&nbsp;</th>
      </tr>
      {section name=i loop=$cart->mCartProducts}
        <tr>
          <td>{$cart->mCartProducts[i].name}</td>
          <td>${$cart->mCartProducts[i].price}</td>
          <td>
            <input type="text" name="itemQty_{$cart->mCartProducts[i].product_id}"
             value="{$cart->mCartProducts[i].quantity}" size="5" />
          </td>
          <td>${$cart->mCartProducts[i].subtotal}</td>
          <td>
            <a href="{$cart->mCartProducts[i].save_for_later_link|prepare_link:'http'}">
              Save for later
            </a>
            |
            <a href="{$cart->mCartProducts[i].remove_link|prepare_link:'http'}">
              Remove
            </a>
          </td>
        </tr>
      {/section}
      <tr>
        <td class="cart_subtotal" colspan="3">Total amount:</td>
        <td class="cart_subtotal">${$cart->mTotalAmount}</td>
        <td>&nbsp;</td>
      </tr>
    </table>
    <p>
      <input type="submit" name="update" value="Update" />
      {if $features.order_storage}
      <input type="button" name="Checkout" value="Checkout"
        {if $cart->mTotalAmount eq 0}disabled="disabled"{/if}
        onclick="window.location='{$cart->mCheckoutLink|prepare_link:"https"}';" />
      {/if}
    </p>
  </form>
{/if}

{if !$cart->mIsCartLaterEmpty}
  <h2>Saved Products:</h2>
  <p>These are the products in your "Save for later" list.
     They will be stored for 90 days.</p>
  <table class="cart_content" cellspacing="0">
    <tr>
      <th>Product Name</th>
      <th>Price</th>
      <th>&nbsp;</th>
    </tr>
    {section name=i loop=$cart->mSavedCartProducts}
      <tr>
        <td>{$cart->mSavedCartProducts[i].name}</td>
        <td>${$cart->mSavedCartProducts[i].price}</td>
        <td>
          <a href="{$cart->mSavedCartProducts[i].move_to_cart_link|prepare_link:'http'}">
            Move to cart
          </a>
          |
          <a href="{$cart->mSavedCartProducts[i].remove_link|prepare_link:'http'}">
            Remove
          </a>
        </td>
      </tr>
    {/section}
  </table>
{/if}

<p>
  <a href="{$cart->mContinueShoppingLink|prepare_link:'http'}">Continue Shopping</a>
</p>

{if $features.product_recommendations && $cart->mRecommendations}
<h2>Customers who bought this also bought:</h2>
  {section name=m loop=$cart->mRecommendations}
    <p>
      <a class="product_recommendation"
         href="{$cart->mRecommendations[m].link|prepare_link:"http"}">
        {$cart->mRecommendations[m].name}
      </a>
      - {$cart->mRecommendations[m].description}
    </p>
  {/section}
{/if}
