{* cart_summary.tpl - Shopping cart sidebar summary *}
{load_cart_summary assign="cart_summary"}
<div class="left_box" id="cart">
  <p>Your shopping cart:</p>
  {if $cart_summary->mEmptyCart}
    <span class="cart_text">Your cart is empty</span>
  {else}
    <table id="cart_summary_table">
      <tr>
        <td><span class="cart_text">Items:</span></td>
        <td class="cart_cell">
          <span class="cart_count">{$cart_summary->mItems|count}</span>
        </td>
      </tr>
      <tr>
        <td><span class="cart_text">Total:</span></td>
        <td class="cart_cell">
          <span class="cart_price">${$cart_summary->mTotalAmount}</span>
        </td>
      </tr>
    </table>
    <a href="index.php?CartAction={$smarty.const.ADD_PRODUCT|default:1}" class="cart_link">
      View details
    </a>
  {/if}
</div>
