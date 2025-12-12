<template>
  
  <div class="reports-page">
    <h2 class="title">Reports</h2>
    <!-- FILTERS -->
    <div class="filters">
      

      <div class="filters">
        <label style="color: #010253; position: absolute; margin-left: -3px; margin-top: -18px;">Report Type</label>
        <select v-model="reportType" class="sort">
          <option value="transaction">Transaction</option>
          <option value="inventory">Inventory</option>
        </select>
      </div>
    </div>

    <!-- SIDE REPORT BOXES + OUTPUT PREVIEW -->
    <div class="content-row">
      <div class="summary-grid">
        <div class="box red" style="background-color: #ffffff; color: #010253;">
          <h2>Total Transactions</h2>
          <p>{{ totalTransactions }}</p>
        </div>

        <div class="box blue" style="background-color: #ffffff; color: #010253; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);">
          <h2>Total Items in Stock</h2>
          <p>{{ totalItemsInStock  }}</p>
        </div>

        <div class="top-red"></div>

        <div class="box white" style="margin-top: -40px;">
          <h2>Recent Reports</h2>
          <p>Last generated: {{ lastGenerated || 'N/A' }}</p>
        </div>
      </div>

      <div class="output-preview">
        <div class="input-group">
        <!-- replaced free text with two date inputs (from / to) -->
        <label style="color: #010253; position: absolute; margin-left: -8px; margin-top: -1px;">To</label>
        <input type="date" v-model="dateFrom" class="input-box" style="margin-left: 15px;" />
        <label style="color: #010253; position: absolute; margin-left: 150px; margin-top: -1px;">From</label>
        <input type="date" v-model="dateTo" class="input-box" style="margin-left:50px;" />
      </div>
        <h2>Output Preview</h2>

        <!-- Chart -->
        <div class="chart-wrap">
          <canvas ref="chartCanvas"></canvas>
        </div>

        <div v-if="previewCount === 0" class="empty">
          No results for selected date range.
        </div>

        <ul v-else class="preview-list">
          <li
            v-for="item in previewData"
            :key="item.id || item.sku || item.name"
            class="preview-item"
            @click="openNotificationFromActivity(item)"
            style="cursor:pointer;"
          >
            <div class="item-main">
              <strong>{{ primaryField(item) }}</strong>
              <span class="muted">{{ secondaryField(item) }}</span>
            </div>
            <div class="item-meta">{{ tertiaryField(item) }}</div>
          </li>
        </ul>

      </div>

      <!-- HIDDEN COMPONENT (SOURCE DATA PROVIDER) -->
      <component
        :is="currentComponent"
        ref="reportChild"
        v-if="currentComponent"
        style="display: none"
      />
    </div>

    <!-- Export placed below-right outside the output-preview/table -->
    <div class="export-wrap">
      <button class="generate-btn secondary" @click="exportCSV" :disabled="previewCount === 0">
        Export CSV
      </button>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";
import { Chart, registerables } from "chart.js";
import { upsertNotification } from "@/utils/notify";

Chart.register(...registerables);

export default {
  name: "ReportsView",

  data() {
    return {
      dateFrom: null,
      dateTo: null,
      reportType: "transaction",

      transactions: [],
      inventoryItems: [],

      lastGenerated: null,
      chart: null,

      totalTransactions: 0,
      totalItemsInStock: 0,
    };
  },

  computed: {
    previewData() {
      return this.reportType === "transaction"
        ? this.transactions
        : this.inventoryItems;
    },

    previewCount() {
      return this.previewData.length;
    },

    previewTitle() {
      return this.reportType === "transaction"
        ? "Transactions"
        : "Inventory";
    },
  },

  watch: {
    reportType() {
      this.generateReport();
    },
  },

  methods: {
    primaryField(item) {
      return this.reportType === "transaction" ? item.type : item.name;
    },

    secondaryField(item) {
      if (this.reportType === "transaction") {
        return `Qty: ${item.quantity}`;
      }
      return `Stock: ${item.quantity}`;
    },

    tertiaryField(item) {
      return item.created_at || "";
    },

    async generateReport() {
      try {
        const response = await api.get("/reports/preview", {
          params: {
            type: this.reportType === "transaction" ? "Transaction" : "Inventory",
            from: this.dateFrom,
            to: this.dateTo,
          },
        });

        const data = response.data;

        if (this.reportType === "transaction") {
          this.transactions = data;
        } else {
          this.inventoryItems = data;
        }

        this.lastGenerated = new Date().toLocaleString();
        this.renderChart();
      } catch (err) {
        console.error("Report fetch failed:", err);
      }
    },

    async loadSummary() {
      try {
        const res = await api.get("/reports/summary");
        this.totalTransactions = res.data.totalTransactions;
        this.totalItemsInStock = res.data.totalItemsInStock;
      } catch (err) {
        console.error("Summary load failed:", err);
      }
    },

    // ⭐ FIXED — NOW INSIDE methods {}
    renderChart() {
      const ctx = this.$refs.chartCanvas?.getContext("2d");
      if (!ctx) return;

      if (this.chart) this.chart.destroy();

      let labels = [];
      let values = [];
      let type = "bar";
      let options = { responsive: true, maintainAspectRatio: false };

      if (this.reportType === "transaction") {
        const count = {};

        this.transactions.forEach((t) => {
          const key = t.type || "Unknown";
          count[key] = (count[key] || 0) + 1;
        });

        labels = Object.keys(count);
        values = Object.values(count);
        type = "doughnut";
      }

      if (this.reportType === "inventory") {
        labels = this.inventoryItems.map((i) => i.name);
        values = this.inventoryItems.map((i) => Number(i.quantity));
        type = "bar";
        options = { indexAxis: "y", responsive: true };
      }

      this.chart = new Chart(ctx, {
        type,
        data: {
          labels,
          datasets: [
            {
              label: this.previewTitle,
              data: values,
              backgroundColor: values.map(() => "#2A3B8A"),
            },
          ],
        },
        options,
      });
    },

    exportCSV() {
      const url =
        api.defaults.baseURL +
        `/reports/export?type=${
          this.reportType === "transaction" ? "Transaction" : "Inventory"
        }&from=${this.dateFrom || ""}&to=${this.dateTo || ""}`;

      window.open(url, "_blank");
    },

    openNotificationFromActivity(item) {
      const time = item.created_at || "";
      const message = `${this.previewTitle}: ${time}`;

      const n = upsertNotification({
        title: message,
        message,
        level: "info",
        meta: { id: item.id },
        uniqueKey: "id",
      });

      this.$router.push({
        name: "notifications",
        query: { id: n.id },
      });
    },
  },

  mounted() {
    this.loadSummary();
    this.generateReport();
  },
};
</script>


<style scoped>
.reports-page {
  padding: 12px 20px;
  margin-left: 200px;
  background: #eff3f4;
  min-height: 100vh;
  margin-top: -20px;
  box-sizing: border-box;
}

/* title */
.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -40px;
  margin-top: -60px;
  position: absolute;
}

/* filters row */
.filters {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 8px;
}
.sort {
  padding: 4px 18px;
  font-size: 13px;
  border: 1px solid #ccc;
  border-radius: 4px;
  color: #777;
}

/* main 2-column row */
.content-row {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

/* left summary column */
.summary-grid {
  width: 230px;
  color: #010253;
}
.box {
  background: white;
  padding: 35px;
  border-radius: 8px;
  margin-bottom: 50px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.box h2 {
  font-size: 14px;
  margin: 0 0 4px;
}
.box p {
  font-size: 16px;
  margin: 0;
}
.top-red {
  display: none;
}

/* right panel: output preview */
.output-preview {
  flex: 1;
  background: white;
  padding: 12px 16px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
}

/* date inputs row */
.input-group {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}
.input-group label {
  font-size: 12px;
  color: #010253;
  position: static !important;
  margin: 0;
}
.input-box {
  padding: 4px 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 12px;
}

/* preview title */
.output-preview h2 {
  font-size: 16px;
  margin: 4px 0 6px;
}

/* chart: smaller fixed height */
.chart-wrap {
  height: 180px;
  margin-bottom: 6px;
}
.chart-wrap canvas {
  width: 100% !important;
  height: 100% !important;
}

/* list area: fixed max height, scroll inside only if needed */
.preview-list {
  list-style: none;
  padding: 0;
  margin: 0;
  max-height: 190px;     /* keep whole panel short */
  overflow-y: auto;
}
.preview-item {
  padding: 6px 4px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
}
.item-main strong {
  display: block;
}
.item-main .muted {
  color: #666;
  font-size: 11px;
}
.item-meta {
  font-size: 11px;
}
.empty {
  color: #666;
  padding: 8px 0;
  font-size: 12px;
}

/* export button row */
.export-wrap {
  display: flex;
  justify-content: flex-end;
  margin-top: 6px;
}
.generate-btn {
  padding: 6px 12px;
  background: #010253;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
}
.generate-btn.secondary {
  background: #010253;
  color: #ddd;
}

/* small screens */
@media (max-width: 1100px) {
  .content-row {
    flex-direction: column;
  }
  .summary-grid {
    width: 100%;
    display: flex;
    gap: 10px;
  }
  .summary-grid .box {
    flex: 1;
  }
}
</style>
