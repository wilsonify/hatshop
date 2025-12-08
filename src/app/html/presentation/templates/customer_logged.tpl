{* customer_logged.tpl *}
{load_customer_logged assign="customer_logged"}
{if $features.customer_details}
<div class="left_box" id="login_box">
  <p>Welcome, {$customer_logged->mCustomerName}</p>
  <ol>
    <li>
      <a href="{$customer_logged->mUpdateAccount|prepare_link:"https"}">
        &raquo; Change Account Details
      </a>
    </li>
    {if $features.order_storage}
    <li>
      <a href="{$customer_logged->mUpdateCreditCard|prepare_link:"https"}">
        &raquo; {$customer_logged->mCreditCardAction} CC Details
      </a>
    </li>
    {/if}
    <li>
      <a href="{$customer_logged->mUpdateAddress|prepare_link:"https"}">
        &raquo; {$customer_logged->mAddressAction} Address Details
      </a>
    </li>
    <li>
      <a href="{$customer_logged->mLogout|prepare_link}">
        &raquo; Log Out
      </a>
    </li>
  </ol>
</div>
{/if}
