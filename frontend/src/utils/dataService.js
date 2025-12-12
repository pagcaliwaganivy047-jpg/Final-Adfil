export const KEY_INV = "inventory";
export const KEY_TX = "transactions";

import { upsertNotification } from "@/utils/notify";

/* Storage helpers */
function read(key) {
  try { return JSON.parse(localStorage.getItem(key) || "[]"); } catch { return []; }
}
function write(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

/* Inventory helpers */
export function getInventory() {
  return read(KEY_INV);
}
export function saveInventory(list) {
  write(KEY_INV, list);
  return list;
}
export function findInventoryItem(idOrSku) {
  const list = getInventory();
  return list.find(i => i.id == idOrSku || i.sku == idOrSku) || null;
}
export function updateInventoryItem(idOrSku, delta = 0, opts = {}) {
  // delta positive => add, negative => remove
  const items = getInventory();
  const idx = items.findIndex(i => i.id == idOrSku || i.sku == idOrSku);
  if (idx === -1) throw new Error("Item not found");
  const item = { ...items[idx] };
  const newQty = (Number(item.quantity) || 0) + Number(delta);
  if (!opts.allowNegative && newQty < 0) throw new Error("Insufficient stock");
  item.quantity = newQty;
  items.splice(idx, 1, item);
  saveInventory(items);
  // optionally upsert notification about stock change
  if (opts.notify !== false) {
    upsertNotification({
      title: `Inventory updated: ${item.name || item.sku}`,
      message: `Quantity is now ${item.quantity}`,
      level: item.quantity <= (opts.lowThreshold ?? 10) ? "warn" : "info",
      meta: { source: "inventory", sourceId: item.id || item.sku },
      uniqueKey: "sourceId"
    });
  }
  return item;
}

/* Transactions helpers */
export function getTransactions() {
  return read(KEY_TX);
}
export function saveTransactions(list) {
  write(KEY_TX, list);
  return list;
}

/**
 * addTransaction(transaction)
 * - transaction = { type: "add"|"withdraw"|"adjust", itemId, qty, date, note, user }
 * - automatically updates inventory quantities based on type
 * - returns saved transaction object
 */
export function addTransaction(tx) {
  if (!tx || !tx.type) throw new Error("Transaction.type required");
  if (!tx.itemId) throw new Error("Transaction.itemId required");
  const qty = Number(tx.qty || 0);
  if (isNaN(qty)) throw new Error("Invalid qty");

  // find item
  const item = findInventoryItem(tx.itemId);
  if (!item) throw new Error("Inventory item not found for transaction");

  // compute delta
  let delta = 0;
  if (tx.type === "add") delta = qty;
  else if (tx.type === "withdraw") delta = -qty;
  else if (tx.type === "adjust") delta = (tx.newQuantity !== undefined) ? (Number(tx.newQuantity) - Number(item.quantity || 0)) : qty;
  else throw new Error("Unsupported transaction type");

  // apply inventory update (throws if not enough stock)
  const updated = updateInventoryItem(tx.itemId, delta, { allowNegative: !!tx.allowNegative, notify: tx.notify, lowThreshold: tx.lowThreshold });

  // persist transaction
  const list = getTransactions();
  const now = new Date().toISOString();
  const id = tx.id || `${Date.now()}-${Math.floor(Math.random()*10000)}`;
  const saved = {
    id,
    type: tx.type,
    itemId: tx.itemId,
    itemName: item.name || item.sku || item.id,
    qty,
    date: tx.date || now,
    note: tx.note || "",
    user: tx.user || localStorage.getItem("userName") || "system",
    createdAt: now
  };
  list.unshift(saved);
  saveTransactions(list);

  // notification about the transaction
  upsertNotification({
    title: `${saved.type === "withdraw" ? "Withdrawal" : saved.type === "add" ? "Addition" : "Adjustment"}: ${saved.itemName}`,
    message: `${saved.user} ${saved.type === "withdraw" ? "removed" : "added"} ${saved.qty} — ${saved.note || ""}`.trim(),
    level: saved.type === "withdraw" && updated.quantity <= (tx.lowThreshold ?? 10) ? "warn" : "info",
    meta: { source: "transaction", sourceId: saved.id },
    uniqueKey: "sourceId"
  });

  return { transaction: saved, updatedItem: updated };
}