export function applyTransactionToInventory(transaction) {
  let inventory = JSON.parse(localStorage.getItem("inventory")) || [];

  // Check if item exists in inventory
  let item = inventory.find(i => i.name.toLowerCase() === transaction.name.toLowerCase());

  // If item does not exist, create new (optional)
  if (!item) {
    item = {
      id: inventory.length ? inventory[inventory.length - 1].id + 1 : 1,
      name: transaction.name,
      category: transaction.category || "Uncategorized",
      quantity: 0,
      location: transaction.location || "Warehouse"
    };
    inventory.push(item);
  }

  // Apply the transaction change
  if (transaction.type === "IN") {
    item.quantity += Number(transaction.quantity);
  } else if (transaction.type === "OUT") {
    item.quantity -= Number(transaction.quantity);
    if (item.quantity < 0) item.quantity = 0; // prevents negative stock
  }

  // Save updated inventory
  localStorage.setItem("inventory", JSON.stringify(inventory));
}
