document.querySelectorAll('[data-confirm]').forEach((button) => {
  button.addEventListener('click', (event) => {
    if (!confirm(button.dataset.confirm || 'Are you sure?')) event.preventDefault();
  });
});

setTimeout(() => {
  document.querySelectorAll('[data-flash]').forEach((el) => el.classList.add('fade'));
}, 3500);

const wage = document.querySelector('[data-wage]');
const days = document.querySelector('[data-days]');
const amount = document.querySelector('[data-amount]');
function updateAmount() {
  if (!wage || !days || !amount) return;
  const w = Number(wage.value || 0);
  const d = Number(days.value || 0);
  if (w > 0 && d > 0) amount.value = (w * d).toFixed(2);
}
[wage, days].forEach((el) => el && el.addEventListener('input', updateAmount));
