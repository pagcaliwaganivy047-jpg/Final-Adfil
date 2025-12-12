<template>
  <div class="content">
    <h2 class="title">Transactions</h2>

    <!-- FILTER BAR -->
    <div class="filters">
      <select class="filter-rad" v-model="filterType" @change="applyFilters">
        <option value="">All Types</option>
        <option value="stock_in">Stock In</option>
        <option value="stock_out">Stock Out</option>
        <option value="item_added">New Item Added</option>
        <option value="item_edited">Item Edited</option>
        <option value="item_deleted">Item Deleted</option>
      </select>

      <input
        class="filter-rad"
        type="date"
        v-model="filterDate"
        @change="applyFilters"
      />
    </div>

    <!-- TABLE -->
    <section class="table-container" style="margin-top: 24px;">
      <table>
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Date</th>
            <th>Item</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Handled By</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="trx in paginatedTransactions" :key="trx.id">
            <td>{{ trx.id }}</td>
            <td>{{ formatDate(trx.date) }}</td>
            <td>{{ trx.item_name }}</td>
            <td>{{ formatType(trx.type) }}</td>
            <td>{{ trx.quantity }}</td>
            <td>{{ trx.handled_by }}</td>
          </tr>

          <tr v-if="filteredTransactions.length === 0">
            <td colspan="6" style="text-align:center; color:#777;">
              No transactions found
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINATION -->
      <div class="table-footer" v-if="totalPages > 1">
        <div class="pagination-center">
          <button @click="prevPage" :disabled="page === 1">Prev</button>

          <button
            v-for="p in totalPages"
            :key="p"
            :class="['page-btn', { active: page === p }]"
            @click="page = p"
          >
            {{ p }}
          </button>

          <button @click="nextPage" :disabled="page === totalPages">
            Next
          </button>
        </div>

        <span class="page-info">
          Page {{ page }} / {{ totalPages }} — {{ filteredTransactions.length }} transactions
        </span>
      </div>
    </section>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "TransactionView",

  data() {
    return {
      transactions: [],
      filteredTransactions: [],
      filterType: "",
      filterDate: "",
      page: 1,
      itemsPerPage: 10
    };
  },

  created() {
    this.loadTransactions();
  },

  computed: {
    totalPages() {
      return (
        Math.ceil(this.filteredTransactions.length / this.itemsPerPage) || 1
      );
    },

    paginatedTransactions() {
      const start = (this.page - 1) * this.itemsPerPage;
      return this.filteredTransactions.slice(start, start + this.itemsPerPage);
    }
  },

  methods: {
    async loadTransactions() {
      try {
        const response = await axios.get(
          "http://localhost:8000/api/transactions",
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`
            }
          }
        );

        this.transactions = response.data;
        this.filteredTransactions = this.transactions;
      } catch (error) {
        console.error("Failed to load transactions:", error);
      }
    },

    applyFilters() {
      this.filteredTransactions = this.transactions.filter((trx) => {
        const matchType =
          this.filterType === "" || trx.type === this.filterType;

        const matchDate =
          this.filterDate === "" ||
          (trx.date && trx.date.startsWith(this.filterDate));

        return matchType && matchDate;
      });

      // reset to first page when filters change
      this.page = 1;
    },

    prevPage() {
      if (this.page > 1) this.page--;
    },

    nextPage() {
      if (this.page < this.totalPages) this.page++;
    },

    formatType(type) {
      const map = {
        stock_in: "Stock In",
        stock_out: "Stock Out",
        new_item: "New Item Added",
        edit_item: "Item Edited",
        delete_item: "Item Deleted"
      };
      return map[type] || type;
    },

    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleString();
    }
  }
};
</script>

<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css";

/* ============================= MAIN RIGHT CONTENT AREA ================================ */
.rectangle-red {
  width: 107%;
  height: 20px;
  background-color: #E02323;
  margin-top: -20px;
  margin-left: -37px;
}

.top-rectangle {
  background-color: #FFFFFF;
  box-shadow: 5px 5px 8px 2px rgba(0, 0, 0, 0.3);
  width: 168.5vh;
  height: 80px;
  margin-top: -24px;
  margin-left: -30px;
}

.filters {
  margin-left: 830px;
  margin-top: 10px;
  display: flex;
  gap: 15px;
}

.filter-rad {
  padding: 5px 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
  color: #777;
}

/* TITLE */
.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -40px;
  margin-top: -60px;
  position: absolute;
}

/* TABLE */
.table-container {
  background: white;
  padding: 20px;
  border-radius: 0px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  margin-top: 1000px;
  margin-bottom: -100px;
  border: 1px solid #010253;
  min-height: 350px;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
}

table {
  width: 100%;
  border-collapse: collapse;
  flex: 1 1 auto;
}

thead th {
  padding: 12px 8px;
  color: #010253;
  font-size: 14px;
  border-bottom: 2px solid #ddd;
  text-align: center;
}

tbody td {
  padding: 10px 10px;
  border-bottom: 1px solid #eee;
  text-align: center;
}

tbody tr:hover {
  background: #fafafa;
}

/* PAGINATION */
.table-footer {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 14px;
  padding-top: 10px;
}

.pagination-center {
  display: flex;
  align-items: center;
  gap: 8px;
}

.page-btn {
  min-width: 32px;
  padding: 6px 10px;
  border-radius: 4px;
  border: 1px solid #ccc;
  cursor: pointer;
  background: #fff;
}

.page-btn.active {
  background-color: #010253;
  color: #fff;
}

.page-info {
  font-size: 14px;
  color: #444;
}

/* CONTENT BACKGROUND */
.content {
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  margin-left: 200px;
  margin-top: -29px;
  padding-bottom: 40px;
}
</style>
