<template>
  <div class="content">
    <h2 class="title">Notifications</h2>

    <div class="profile-card">
      <div class="notif-container">
        <div
          v-for="n in notifications"
          :key="n.id"
          class="notif-card"
          :class="n.type"
        >
          <div class="left">
            <span class="dot" :class="n.type"></span>
            <p class="msg">{{ n.message }}</p>
            <p class="time">{{ formatDate(n.date) }}</p>
          </div>

          <router-link :to="n.link" class="view-link" @click="markRead(n.id)">
            View
          </router-link>
        </div>

        <div v-if="notifications.length === 0" class="empty">
          No notifications available
        </div>
      </div>
    </div>

    <div class="actions">
      <button @click="markAllRead">Mark All Read</button>
      <button @click="clearAll">Clear Notifications</button>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";

export default {
  name: "NotificationsPage",

  data() {
    return {
      notifications: []
    };
  },

  created() {
    this.loadNotifications();
  },

  methods: {
    async loadNotifications() {
      try {
        const res = await api.get("/notifications");
        this.notifications = res.data;
      } catch (err) {
        console.error("Load notifications failed:", err.response?.data || err.message);
      }
    },

    async markRead(id) {
      try {
        await api.post(`/notifications/${id}/read`);
        await this.loadNotifications();
      } catch (err) {
        console.error("Mark read failed:", err.response?.data || err.message);
      }
    },

    async markAllRead() {
      try {
        await api.post("/notifications/read-all");
        await this.loadNotifications();
      } catch (err) {
        console.error("Mark all read failed:", err.response?.data || err.message);
      }
    },

    async clearAll() {
      try {
        await api.delete("/notifications");
        await this.loadNotifications();
      } catch (err) {
        console.error("Clear notifications failed:", err.response?.data || err.message);
      }
    },

    formatDate(date) {
      return date ? new Date(date).toLocaleString() : "";
    }
  }
};
</script>

<style scoped>
.content {
  margin-left: 200px;
  padding: 20px;
}

.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -40px;
  margin-top: -90px;
  position: absolute;
}

.notif-container {
  margin-top: 20px;
}

.profile-card {
  background: white;
  padding: 20px;
  border-radius: 0px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  margin-top: 60px;
  border: 1px solid #010253;
}

.notif-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-left: 5px solid;
  background: white;
  padding: 15px;
  margin-bottom: 12px;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.notif-card.info { border-color: #28c76f; }
.notif-card.warning { border-color: #ff9f43; }
.notif-card.error { border-color: #ea5455; }

.dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 10px;
}

.dot.info { background: #28c76f; }
.dot.warning { background: #ff9f43; }
.dot.error { background: #ea5455; }

.msg {
  font-weight: bold;
}

.time {
  font-size: 12px;
  color: #666;
}

.view-link {
  color: #010253;
  font-weight: bold;
  text-decoration: underline;
}

.actions {
  margin-top: 20px;
}

.actions button {
  background: #010253;
  color: white;
  border: none;
  padding: 8px 16px;
  margin-right: 10px;
  border-radius: 5px;
}
</style>
