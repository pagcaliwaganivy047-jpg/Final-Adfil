export function addNotification({ type, message, link }) {
  const stored = JSON.parse(localStorage.getItem("notifications")) || [];

  const newNotif = {
    id: Date.now(),
    type,           // "info", "warning", "error"
    message,
    link,           // route to open
    date: new Date().toISOString(),
    read: false
  };

  stored.unshift(newNotif);
  localStorage.setItem("notifications", JSON.stringify(stored));

  // update badge in App.vue using event bus
  window.dispatchEvent(new CustomEvent("notif-update"));
}
