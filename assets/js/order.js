document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('orderForm');
  const tableBody = document.getElementById('orderTableBody');
  const totalBadge = document.getElementById('totalOrders');
  const averageBadge = document.getElementById('averageRating');

  if (!form || !tableBody || !totalBadge || !averageBadge) {
    return;
  }

  const updateTotal = () => {
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    totalBadge.textContent = rows.length.toString();

    const ratingTotal = rows.reduce((total, row) => {
      const value = Number((row.children[2] && row.children[2].textContent) || 0);
      return total + value;
    }, 0);

    const average = rows.length === 0 ? 0 : ratingTotal / rows.length;
    averageBadge.textContent = `Rata-rata: ${average.toFixed(1)}`;
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    const formData = new FormData(form);
    const customerName = String(formData.get('nama') || '').trim();
    const product = String(formData.get('produk') || '').trim();
    const quantity = String(formData.get('jumlah') || '').trim();
    const notes = String(formData.get('catatan') || '').trim();

    if (!customerName || !product || !quantity) {
      return;
    }

    const row = document.createElement('tr');

    [customerName, product, quantity, notes || '-'].forEach((value) => {
      const cell = document.createElement('td');
      cell.textContent = value;
      row.appendChild(cell);
    });

    tableBody.prepend(row);
    form.reset();
    updateTotal();
  });

  updateTotal();
});
