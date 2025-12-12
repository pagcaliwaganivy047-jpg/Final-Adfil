<template>
  <div class="content">
    <h2 class="title">Supplier Record</h2>

    <p v-if="supplierName" class="subtitle">
      Records for: <strong>{{ supplierName }}</strong>
    </p>

  
    <section class="table-container" style="margin-top: 16px;">
        <!-- DATE FILTERS -->
    <div class="filter-row">
      <label>
        From:
        <input type="date" v-model="dateFrom" />
      </label>
      <label style="margin-left:12px;">
        To:
        <input type="date" v-model="dateTo" />
      </label>
      <button class="clear-btn" @click="clearDateFilter">Clear</button>
    </div>

      <table>
        <thead>
          <tr>
            <th>Date Started</th>
            <th>Project</th>
            <th>Item Supplied</th>
            <th>Initial Qty</th>
            <th>Delivered Qty</th>
            <th>Remaining Qty</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date Ended</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rec in filteredRecords" :key="rec.id">
            <td>{{ formatDate(rec.date_started) }}</td>
            <td>{{ rec.project || "-" }}</td>
            <td>{{ rec.item_supplied || "-" }}</td>
            <td>{{ rec.quantity || 0 }}</td>
            <td>{{ rec.delivered_quantity || 0 }}</td>
            <td>{{ remainingQty(rec) }}</td>
            <td>{{ rec.amount || "-" }}</td>
            <td>
              <select v-model="rec.status" @change="onStatusChange(rec)">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </td>
            <td>{{ formatDate(rec.date_ended) }}</td>
            <td>
              <button class="delete" @click="deleteRecord(rec.id)">Delete</button>
            </td>
          </tr>
          <tr v-if="filteredRecords.length === 0">
            <td colspan="10" style="text-align:center; color:#777;">
              No records found for this supplier.
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- ADD RECORD MODAL -->
    <div class="overlay" v-if="showAddModal" @click.self="closeAddModal">
      <div class="modal add-modal-wide">
        <h2>Add Supplier Record</h2>

        <label>Project</label>
        <input v-model="newRecord.project" type="text" />

        <label>Item Supplied</label>
        <input v-model="newRecord.item_supplied" type="text" />

        <label>Initial Quantity</label>
        <input v-model.number="newRecord.quantity" type="number" min="0" />

        <label>Delivered Quantity (optional)</label>
        <input v-model.number="newRecord.delivered_quantity" type="number" min="0" />

        <label>Amount</label>
        <input v-model="newRecord.amount" type="text" />

        <label>Status</label>
        <select v-model="newRecord.status">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>

        <label>Date Started</label>
        <input v-model="newRecord.date_started" type="date" />

        <div class="modal-buttons">
          <button class="cancel" @click="closeAddModal">Cancel</button>
          <button class="confirm" @click="saveNewRecord">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";

export default {
  name: "SupplierRecordView",

  data() {
    return {
      supplierName: this.$route.params.name || "",
      records: [],
      dateFrom: "",
      dateTo: "",
      showAddModal: false,
      newRecord: {
        project: "",
        item_supplied: "",
        quantity: 0,
        delivered_quantity: 0,
        amount: "",
        status: "pending",
        date_started: ""
      }
    };
  },

  computed: {
    supplierId() {
      return Number(this.$route.params.id);
    },

    filteredRecords() {
      if (!this.dateFrom && !this.dateTo) return this.records;

      const fromTime = this.dateFrom
        ? new Date(this.dateFrom).getTime()
        : null;
      const toTime = this.dateTo ? new Date(this.dateTo).getTime() : null;

      return this.records.filter((rec) => {
        if (!rec.date_started) return false;
        const t = new Date(rec.date_started).getTime();
        if (fromTime && t < fromTime) return false;
        if (toTime && t > toTime) return false;
        return true;
      });
    }
  },

  methods: {
    clearDateFilter() {
      this.dateFrom = "";
      this.dateTo = "";
    },

    async loadRecords() {
      try {
        const res = await api.get(`/suppliers/${this.supplierId}/records`);
        this.records = res.data || [];
      } catch (err) {
        console.error(
          "Failed to load supplier records:",
          err.response?.data || err.message
        );
        alert("Error loading supplier records.");
      }
    },

    remainingQty(rec) {
      const q = Number(rec.quantity) || 0;
      const d = Number(rec.delivered_quantity) || 0;
      return Math.max(q - d, 0);
    },

    formatDate(value) {
      if (!value) return "";
      const d = new Date(value);
      if (Number.isNaN(d.getTime())) return value;
      return d.toLocaleDateString();
    },

    async onStatusChange(rec) {
      try {
        const payload = {
          status: rec.status
        };

        if (rec.status === "completed" && !rec.date_ended) {
          const today = new Date();
          const yyyy = today.getFullYear();
          const mm = String(today.getMonth() + 1).padStart(2, "0");
          const dd = String(today.getDate()).padStart(2, "0");
          const dateStr = `${yyyy}-${mm}-${dd}`;
          rec.date_ended = dateStr;
          payload.date_ended = dateStr;
        }

        await api.put(`/supplier-records/${rec.id}`, payload);
      } catch (err) {
        console.error(
          "Update status failed:",
          err.response?.data || err.message
        );
        alert("Error updating record status.");
      }
    },

    openAddModal() {
      this.showAddModal = true;
      this.newRecord = {
        project: "",
        item_supplied: "",
        quantity: 0,
        delivered_quantity: 0,
        amount: "",
        status: "pending",
        date_started: ""
      };
    },

    closeAddModal() {
      this.showAddModal = false;
    },

    async saveNewRecord() {
      try {
        if (!this.newRecord.project && !this.newRecord.item_supplied) {
          alert("Please enter at least Project or Item Supplied.");
          return;
        }

        const payload = {
          supplier_id: this.supplierId,
          project: this.newRecord.project || null,
          item_supplied: this.newRecord.item_supplied || null,
          quantity: Number(this.newRecord.quantity) || 0,
          delivered_quantity:
            Number(this.newRecord.delivered_quantity) || 0,
          amount: this.newRecord.amount || null,
          status: this.newRecord.status || "pending",
          date_started: this.newRecord.date_started || null
        };

        const res = await api.post("/supplier-records", payload);
        this.records.unshift(res.data);
        this.showAddModal = false;
      } catch (err) {
        console.error(
          "Add supplier record failed:",
          err.response?.data || err.message
        );
        alert(
          "Error adding supplier record: " +
            JSON.stringify(
              err.response?.data?.errors ||
                err.response?.data ||
                err.message
            )
        );
      }
    },

    async deleteRecord(id) {
      if (!confirm("Delete this record?")) return;
      try {
        await api.delete(`/supplier-records/${id}`);
        this.records = this.records.filter((r) => r.id !== id);
      } catch (err) {
        console.error(
          "Delete supplier record failed:",
          err.response?.data || err.message
        );
        alert("Error deleting supplier record.");
      }
    }
  },

  mounted() {
    this.loadRecords();
  }
};
</script>


<style scoped>
.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -40px;
  margin-top: -60px;
  position: absolute;
}

.content {
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  margin-left: 200px;
  margin-top: -29px;
  padding-bottom: 40px;
}

.subtitle {
  color: #555;
  margin-bottom: 12px;
}

/* date filter row */
.filter-row {
  margin-top: 40px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}
.filter-row input[type="date"] {
  padding: 4px 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.clear-btn {
  margin-left: 8px;
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid #ccc;
  background: #f5f5f5;
  cursor: pointer;
}

/* table */
.table-container {
  background: white;
  padding: 12px;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  min-height: 450px;
  display: flex;
  flex-direction: column;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 8px 10px;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

thead th {
  background: #f5f5f5;
  color: #010253;
}

.delete {
  background: #b81c1c;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}
.delete:hover {
  background: #e02323;
}

/* modal */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
  z-index: 3;
}
.modal {
  width: 380px;
  margin: 80px auto;
  padding: 24px 24px 32px;
  background: #eff3f4;
  border-radius: 8px;
}
.add-modal-wide {
  width: 420px;
}
.modal h2 {
  text-align: center;
  margin-bottom: 16px;
  color: #010253;
}
.modal label {
  display: block;
  margin-top: 8px;
  color: #010253;
  font-size: 14px;
}
.modal input,
.modal select {
  width: 100%;
  padding: 6px 8px;
  margin-top: 4px;
  border: 1px solid #aaa;
  border-radius: 4px;
  font-size: 14px;
}
.modal-buttons {
  margin-top: 18px;
  display: flex;
  justify-content: center;
  gap: 10px;
}
.cancel {
  background: #777;
  color: white;
  padding: 7px 15px;
  border-radius: 5px;
  border: none;
}
.confirm {
  background: #010253;
  color: white;
  padding: 7px 15px;
  border-radius: 5px;
  border: none;
}
</style>
