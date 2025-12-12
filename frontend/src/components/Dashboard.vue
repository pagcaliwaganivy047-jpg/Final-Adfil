<template>

  
  <div class="dashboard-wrapper">
    <h2 class="title">Dashboard</h2>
    <!-- Content area (inside card like in design) -->
    <div class="content-frame">
      <!-- Summary cards row -->
      <div class="summary-row">
        <div class="summary-card">
          <div class="summary-label">Total Items</div>
          <div class="summary-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
          <div class="summary-value">{{ totalItems }}</div>
        </div>

        <div class="summary-card">
          <div class="summary-label">Low Stock Alerts</div>
          <div class="summary-icon"><i class="fa-solid fa-chart-line"></i></div>
          <div class="summary-value">{{ lowStockCount }}</div>
        </div>

        <div class="summary-card">
          <div class="summary-label">Total Suppliers</div>
          <div class="summary-icon"><i class="fa-solid fa-store"></i></div>
          <div class="summary-value">{{ totalSuppliers }}</div>
        </div>

        <div class="summary-card">
          <div class="summary-label">Today's Transactions</div>
          
           <div class="summary-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></div>
          <div class="summary-value">{{ todaysTransactions }}</div>
        </div>
      </div>

      <!-- Chart + calendar -->
      <div class="middle-row">
        <div class="chart-card">
          <div class="chart-placeholder">
            <div class="chart-line"></div>
          </div>
        </div>

        <div class="calendar-card">
          <div class="calendar-header">
            <span>{{ currentMonthLabel }}</span>
          </div>
          <table class="calendar-table">
            <thead>
              <tr>
                <th v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d">{{ d }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(week, wIdx) in calendarWeeks" :key="wIdx">
                <td
                  v-for="(day, dIdx) in week"
                  :key="dIdx"
                  :class="{ today: day.isToday, empty: !day.day }"
                >
                  <span v-if="day.day">{{ day.day }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent transactions + low stock items -->
      <div class="bottom-row">
        <div class="recent-card">
          <h3>Recent Transaction</h3>
          <table class="recent-table">
            <thead>
              <tr>
                <th>Ref No</th>
                <th>Date</th>
                <th>Item</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Handled by</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="trx in recentTransactions" :key="trx.id">
                <td>{{ trx.ref_no }}</td>
                <td>{{ formatDate(trx.date) }}</td>
                <td>{{ trx.item_name }}</td>
                <td>{{ formatType(trx.type) }}</td>
                <td>{{ trx.quantity }}</td>
                <td>{{ trx.handled_by }}</td>
              </tr>
              <tr v-if="recentTransactions.length === 0">
                <td colspan="6" class="no-data">No transactions yet</td>
              </tr>
            </tbody>
          </table>

          <div class="bottom-buttons">
            <button @click="goToTransactions">View all Transactions</button>
            <button @click="goToInventory">Manage Inventory</button>
            <button @click="refresh">Refresh</button>
          </div>
        </div>

        <div class="lowstock-card">
          <h3>Low Stock Items</h3>
          <ul>
            <li v-for="item in lowStockItems" :key="item.item_name">
              <strong>{{ item.item_name }}</strong>
              <div class="meta">
                {{ item.quantity }} — {{ item.location || "No location" }}
              </div>
            </li>
            <li v-if="lowStockItems.length === 0" class="no-data">
              No low stock items
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";

export default {
  name: "DashboardView",

  data() {
    return {
      lowStockThreshold: 10,
      inventory: [],
      transactions: [],
      totalItems: 0,
      lowStockCount: 0,
      totalSuppliers: 0,
      todaysTransactions: 0
    };
  },

  mounted() {
    this.loadData();
  },

  computed: {
    lowStockItems() {
      return this.inventory
        .filter((i) => (Number(i.quantity) || 0) <= this.lowStockThreshold)
        .slice(0, 5);
    },

    recentTransactions() {
      return (this.transactions || []).slice().reverse().slice(0, 5);
    },

    currentMonthLabel() {
      const d = new Date();
      return d.toLocaleString("default", { month: "long", year: "numeric" });
    },

    calendarWeeks() {
      const result = [];
      const today = new Date();
      const year = today.getFullYear();
      const month = today.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startWeekday = firstDay.getDay();
      const totalDays = lastDay.getDate();

      let currentDay = 1;
      for (let w = 0; w < 6; w++) {
        const week = [];
        for (let d = 0; d < 7; d++) {
          if (w === 0 && d < startWeekday) {
            week.push({ day: null, isToday: false });
          } else if (currentDay > totalDays) {
            week.push({ day: null, isToday: false });
          } else {
            const isToday =
              currentDay === today.getDate() &&
              month === today.getMonth() &&
              year === today.getFullYear();
            week.push({ day: currentDay, isToday });
            currentDay++;
          }
        }
        result.push(week);
      }
      return result;
    }
  },

  methods: {
    async loadData() {
      try {
        const res = await api.get("/dashboard");

        this.transactions = res.data.recent_activity || [];
        this.inventory = res.data.low_stock_items || [];

        this.totalItems = res.data.summary.total_items;
        this.lowStockCount = res.data.summary.low_stock_alerts;
        this.totalSuppliers = res.data.summary.total_suppliers;
        this.todaysTransactions = res.data.summary.today_transactions;
      } catch (error) {
        console.error("Dashboard Load Error:", error.response || error);
        if (error.response?.status === 401) {
          alert("Session expired. Please log in again.");
          this.$router.push("/login");
        }
      }
    },

    formatType(type) {
      if (!type) return "";
      if (type === "IN") return "Stock In";
      if (type === "OUT") return "Stock Out";
      return type;
    },

    formatDate(dateString) {
      if (!dateString) return "";
      const d = new Date(dateString);
      return d.toLocaleDateString();
    },

    goToTransactions() {
      this.$router.push({ name: "transactions" }).catch(() => {});
    },

    goToInventory() {
      this.$router.push({ name: "inventory" }).catch(() => {});
    },

    refresh() {
      this.loadData();
    }
  }
};
</script>

<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css";

html,
body {
  overflow: auto !important;
  height: auto !important;
  overflow-x: hidden;
}
.dashboard-wrapper {
  max-width: 100vw;
  min-height: 100vh;
  margin-left: 200px; /* adjust for sidebar */
  background: #f3f4f7;
  padding-top: 12px;
  padding: 12px 0 40px;
}

.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -20px;
  margin-top: -80px;
  position: absolute;
}

.content-frame {
  margin: 0 24px 24px;
}

/* summary cards */
.summary-row {
  display: flex;
  gap: 18px;
  margin-bottom: 16px;
}
.summary-card {
  flex: 1;
  background: #ffffff;
  border-radius: 6px;
  padding: 10px 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.summary-icon {
  font-size: 20px;
  color: #010253;
  margin-bottom: 6px;
}
.summary-label {
  font-size: 14px;
  color: #29304d;
}
.summary-value {
  font-size: 22px;
  font-weight: 700;
  color: #010253;
}

/* middle row */
.middle-row {
  display: flex;
  gap: 18px;
  margin-bottom: 16px;
}
.chart-card {
  flex: 1;
  background: #ffffff;
  border-radius: 6px;
  padding: 18px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}
.chart-placeholder {
  border-radius: 4px;
  height: 180px;
  background: #f6f7fb;
  position: relative;
  overflow: hidden;
}
.chart-line {
  position: absolute;
  left: 10px;
  right: 10px;
  top: 60%;
  height: 2px;
  background: #8890c0;
}
.calendar-card {
  width: 240px;
  background: #ffffff;
  border-radius: 6px;
  padding: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}
.calendar-header {
  font-weight: 600;
  color: #010253;
  margin-bottom: 8px;
}
.calendar-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
}
.calendar-table th,
.calendar-table td {
  text-align: center;
  padding: 4px;
}
.calendar-table td.empty {
  background: transparent;
}
.calendar-table td.today {
  background: #010253;
  color: #ffffff;
  border-radius: 50%;
}


.bottom-row {
  display: flex;
  gap: 18px;
  padding: -2px 1px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  align-items: stretch; 
}
.recent-card {
  flex: 1;
  background: #ffffff;
  border-radius: 6px;
  padding: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  height: 200px;           
  display: flex;
  flex-direction: column;
}
.recent-card h3 {
  margin: 0 0 8px;
  color: #010253;
}

.recent-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  flex: 1;       
}
.recent-table th,
.recent-table td {
  padding: 6px 8px;
  border-bottom: 1px solid #eef0f5;
  text-align: left;
}
.recent-table th {
  color: #29304d;
  font-weight: 600;
}

.bottom-buttons {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}
.bottom-buttons button {
  background: #010253;
  color: #ffffff;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
}

.lowstock-card {
  width: 260px;
  background: #ffffff;
  border-radius: 6px;
  padding: 14px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  height: 195px;        
  display: flex;
  flex-direction: column;
}
.lowstock-card h3 {
  margin: 0 0 8px;
  color: #010253;
}
.lowstock-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.lowstock-card li {
  padding: 6px 4px;
  border-bottom: 1px solid #eef0f5;
}
.lowstock-card .meta {
  font-size: 11px;
  color: #666;
}
.no-data {
  text-align: center;
  color: #888;
  padding: 12px 0;
}

@media (max-width: 1100px) {
  .summary-row {
    flex-wrap: wrap;
  }
  .middle-row,
  .bottom-row {
    flex-direction: column;
  }
  .calendar-card,
  .lowstock-card {
    width: 100%;
  }
}
</style>
