// Progressive enhancement only: every form works with JavaScript disabled.
// This script makes "add to cart" update the cart link without a full reload.

const cartLink = document.querySelector('[data-cart-link]');

function csrfToken() {
  const match = document.cookie.match(/(?:^|;\s*)hatshop_csrf=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : '';
}

function updateCartLink(summary) {
  if (!cartLink || !summary) return;
  const count = Number(summary.items ?? 0);
  const total = Number(summary.total ?? 0).toFixed(2);
  cartLink.textContent = count > 0 ? `Cart (${count}) · $${total}` : 'Cart';
}

async function postForm(form) {
  const response = await fetch(form.action, {
    method: 'POST',
    headers: { 'x-csrf-token': csrfToken(), accept: 'application/json' },
    body: new FormData(form),
    credentials: 'same-origin',
    redirect: 'follow',
  });
  return response;
}

document.addEventListener('submit', async (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;
  if (!form.matches('[data-cart-add]')) return;

  event.preventDefault();
  const button = form.querySelector('button[type="submit"]');
  if (button) button.disabled = true;

  try {
    await postForm(form);
    const summaryResponse = await fetch('/cart', {
      headers: { 'x-summary-only': '1' },
      credentials: 'same-origin',
    });
    if (summaryResponse.ok) {
      const type = summaryResponse.headers.get('content-type') || '';
      if (type.includes('application/json')) updateCartLink(await summaryResponse.json());
    }
    if (button) button.textContent = 'Added';
  } catch {
    // Fall back to a normal submission so the action is never silently lost.
    form.submit();
  } finally {
    if (button) {
      button.disabled = false;
      window.setTimeout(() => {
        if (button.textContent === 'Added') button.textContent = 'Add to cart';
      }, 1500);
    }
  }
});
