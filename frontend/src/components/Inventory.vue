<template>
  <div class="content">
    <h2 class="title">Inventory</h2>

    <!-- FILTERS -->
    <div class="filter-bar">
     
      <select class="sort" v-model="filterSort" style="margin-left:8px;">
      <option value="">Date</option>
      <option value="date_asc">Date ↑ (oldest first)</option>
      <option value="date_desc">Date ↓ (newest first)</option>
      </select>

      <!-- Category filter (still simple text for now) -->
      <select class="sort" v-model="filterCategory">
        <option value="">All Categories</option>
        <option value="Concrete">Concrete</option>
        <option value="Steel">Steel</option>
        <option value="Tools">Tools</option>
        <option value="PPE">PPE</option>
      </select>

      <!-- Location filter (still simple text for now) -->
      <select class="sort" v-model="filterLocation" style="margin-left:8px;">
        <option value="">All Locations</option>
        <option value="Warehouse A">Warehouse A</option>
        <option value="Warehouse B">Warehouse B</option>
        <option value="Site 1">Site 1</option>
        <option value="Site 2">Site 2</option>
      </select>
    </div>

    <!-- SEARCH BAR -->
    <div class="filter-bar">
      <input type="text" v-model="searchQuery" placeholder="Search item..." />
    </div>

    <!-- TOP ACTIONS -->
    <div class="top-actions">
      <div>
        <button class="add-btn" @click="openAddModal">Add New Item</button>
      </div>

      <div class="right-actions">
        <button class="stock-in" @click="openAdjustModal('stock_in')">Stock In</button>
        <button class="stock-out" style="margin-left:6px;" @click="openAdjustModal('stock_out')">
          Stock Out
        </button>
      </div>
    </div>

    <!-- INVENTORY TABLE -->
    <section class="table-container" style="margin-top:8px;">
      <table>
        <thead>
          <tr>
            <th>Item ID</th>
            <th @click="sortByField('name')" style="cursor:pointer;">Item</th>
            <th @click="sortByField('category')" style="cursor:pointer;">Category</th>
            <th>Qty</th>
            <th @click="sortByField('location')" style="cursor:pointer;">Location</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="item in paginatedItems" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.item_name }}</td>
            <td>{{ item.category?.name || "-" }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ item.location?.name || "-" }}</td>
            <td>
              <button class="edit" @click="openEditModal(item)">Edit</button>
              <button class="delete" @click="requestDelete(item)">Delete</button>
            </td>
          </tr>

          <tr v-if="filteredInventory.length === 0">
            <td colspan="6" style="text-align:center; color:#777;">No items found</td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINATION CENTERED INSIDE TABLE -->
      <div class="table-footer">
        <div class="pagination-center">
          <button @click="prevPage" :disabled="page === 1">Prev</button>

          <button
            v-for="p in totalPages"
            :key="p"
            :class="{ active: page === p }"
            @click="page = p"
            class="page-btn"
          >
            {{ p }}
          </button>

          <button @click="nextPage" :disabled="page === totalPages">Next</button>
        </div>

        <span class="page-info">
          Page {{ page }} / {{ totalPages }} — {{ filteredInventory.length }} items
        </span>
      </div>
    </section>

    <!-- ADD MODAL -->
    <div class="overlay" v-if="showAddModal" @click.self="closeAddModal">
      <div class="modal add-modal-wide">
        <h2>Add Items</h2>

        <table class="add-table">
          <thead>
            <tr>
              <th>Item Name</th>
              <th>Type</th>
              <th>Category</th>
              <th>Initial Stock</th>
              <th>Location</th>
              <th style="width:40px"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in addRows" :key="index">
              <td class="modal-add">
                <input v-model="row.name" type="text" />
              </td>

              <!-- Item type -->
              <td class="modal-add">
                <select v-model="row.item_type_id">
                  <option disabled value="">Select type</option>
                  <option v-for="t in itemTypes" :key="t.id" :value="t.id">
                    {{ t.name }}
                  </option>
                </select>
              </td>

              <!-- Category -->
              <td class="modal-add">
                <select v-model="row.category_id">
                  <option disabled value="">Select category</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">
                    {{ c.name }}
                  </option>
                </select>
              </td>

              <!-- Initial stock -->
              <td class="modal-add">
                <input v-model.number="row.quantity" type="number" min="0" />
              </td>

              <!-- Location -->
              <td class="modal-add">
                <select v-model="row.location_id">
                  <option disabled value="">Select location</option>
                  <option v-for="l in locations" :key="l.id" :value="l.id">
                    {{ l.name }}
                  </option>
                </select>
              </td>

              <td>
                <button class="delete" @click.stop="removeAddRow(index)">Remove</button>
              </td>
            </tr>
          </tbody>
        </table>

        <p class="add-row-btn" @click="addRow">+ Add Row</p>

        <div class="modal-buttons">
          <button class="cancel" @click="closeAddModal">Cancel</button>
          <button class="confirm" @click="confirmAddRows">Confirm</button>
        </div>
      </div>
    </div>

    <!-- STOCK IN / OUT MODAL WITH SEARCH -->
    <div v-if="showAdjustModal" class="overlay" @click.self="closeAdjustModal">
      <div class="header-add"></div>
      <div class="modal">
        <h2 v-if="adjustAction === 'stock_in'">Stock In</h2>
        <h2 v-else>Stock Out</h2>

        <label>Search Item</label>
        <input
          type="text"
          v-model="adjustSearch"
          placeholder="Search item by name..."
        />

        <div style="margin-top:8px;">
          <p v-if="!adjustItem.id" style="font-size:13px; color:#555;">Select item:</p>
          <ul
            v-if="filteredAdjustItems.length"
            style="max-height:150px; overflow-y:auto; padding-left:16px;"
          >
            <li
              v-for="it in filteredAdjustItems"
              :key="it.id"
              style="cursor:pointer; margin-bottom:4px;"
              @click="selectAdjustItem(it)"
            >
              {{ it.item_name }} ({{ it.quantity }} @ {{ it.location?.name || "N/A" }})
            </li>
          </ul>
          <p
            v-if="adjustSearch && !filteredAdjustItems.length"
            style="font-size:13px; color:#777;"
          >
            No matching items.
          </p>
        </div>

        <div v-if="adjustItem.id" style="margin-top:10px;">
          <p><strong>Selected Item:</strong> {{ adjustItem.item_name }}</p>
          <p><strong>Current Qty:</strong> {{ adjustItem.quantity }}</p>
        </div>

        <label>Quantity</label>
        <input v-model.number="adjustQtyInput" type="number" min="1" />

        <label>Handled By</label>
        <input v-model="handledBy" />

        <div v-if="adjustAction === 'stock_out'">
          <label>Purpose</label>
          <input v-model="adjustPurpose" />
        </div>

        <div class="modal-buttons">
          <button class="cancel" @click="closeAdjustModal">Cancel</button>
          <button class="confirm" @click="confirmAdjust">Confirm</button>
        </div>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="showEditModal" class="overlay" @click.self="closeEditModal">
      <div class="modal modal-add">
        <h2>Edit Item</h2>

        <label>Name</label>
        <input v-model="editClone.name" />

        <label>Type</label>
        <select v-model="editClone.item_type_id">
          <option disabled value="">Select type</option>
          <option v-for="t in itemTypes" :key="t.id" :value="t.id">
            {{ t.name }}
          </option>
        </select>

        <label>Category</label>
        <select v-model="editClone.category_id">
          <option disabled value="">Select category</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>

        <label>Location</label>
        <select v-model="editClone.location_id">
          <option disabled value="">Select location</option>
          <option v-for="l in locations" :key="l.id" :value="l.id">
            {{ l.name }}
          </option>
        </select>

        <label>Quantity</label>
        <input v-model.number="editClone.quantity" type="number" min="0" />

        <label>Low Stock Threshold</label>
        <input v-model.number="editClone.low_stock_threshold" type="number" min="0" />

        <div class="modal-buttons">
          <button class="cancel" @click="closeEditModal">Cancel</button>
          <button class="confirm" @click="saveEdit">Save</button>
        </div>
      </div>
    </div>

    <!-- DELETE PASSWORD MODAL -->
    <div
      v-if="showDeletePasswordModal"
      class="overlay"
      @click.self="closeDeletePasswordModal"
    >
      <div class="modal">
        <h2>Confirm Deletion</h2>
        <p>
          Enter admin password to delete
          "<strong>{{ deleteCandidateItem?.item_name }}</strong>"
        </p>

        <label>Password</label>
        <input v-model="deletePasswordInput" type="password" />

        <div class="modal-buttons">
          <button class="cancel" @click="closeDeletePasswordModal">Cancel</button>
          <button class="confirm" @click="confirmDeleteWithPassword">Delete</button>
        </div>
      </div>
    </div>

    <!-- STATUS MODAL -->
    <div v-if="statusModal.visible" class="overlay" @click.self="closeStatusModal">
      <div class="modal status-modal" :class="statusModal.type">
        <div class="status-header">
          <h2>{{ statusModal.title }}</h2>
          <button class="close-btn" @click="closeStatusModal">×</button>
        </div>
        <p class="status-message">{{ statusModal.message }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";

export default {
  name: "InventoryView",

  data() {
    return {
      items: [],
      loading: false,
      error: "",
      searchQuery: "",
      filterSort: "",
      filterCategory: "",
      filterLocation: "",
      page: 1,
      itemsPerPage: 5,

      // dynamic dropdowns from backend
      itemTypes: [],
      categories: [],
      locations: [],

      showAddModal: false,
      addRows: [
        { name: "", item_type_id: "", category_id: "", location_id: "", quantity: 0 }
      ],

      showAdjustModal: false,
      adjustAction: "stock_in",
      adjustItem: {},
      adjustSearch: "",
      adjustQtyInput: 1,
      handledBy: "",
      adjustPurpose: "",

      showEditModal: false,
      editClone: {},

      showDeletePasswordModal: false,
      deleteCandidateItem: null,
      deletePasswordInput: "",

      statusModal: {
        visible: false,
        title: "",
        message: "",
        type: "info" // 'success' | 'info' | 'error'
      }
    };
  },

  computed: {
  filteredInventory() {
    let items = this.items || [];

    // search
    if (this.searchQuery) {
      const q = this.searchQuery.toLowerCase();
      items = items.filter(
        (i) =>
          i.item_name?.toLowerCase().includes(q) ||
          i.category?.name?.toLowerCase().includes(q) ||
          i.location?.name?.toLowerCase().includes(q)
      );
    }

    // category filter
    if (this.filterCategory) {
      const qc = this.filterCategory.toLowerCase();
      items = items.filter(
        (i) => i.category?.name?.toLowerCase() === qc
      );
    }

    // location filter
    if (this.filterLocation) {
      const ql = this.filterLocation.toLowerCase();
      items = items.filter(
        (i) => i.location?.name?.toLowerCase() === ql
      );
    }

    // DATE SORT (ascending / descending)
    if (this.filterSort === "date_asc" || this.filterSort === "date_desc") {
      const dir = this.filterSort === "date_asc" ? 1 : -1;
      return items.slice().sort((a, b) => {
        // use your actual date field here:
        const da = new Date(a.created_at || a.updated_at || 0).getTime();
        const db = new Date(b.created_at || b.updated_at || 0).getTime();
        return (da - db) * dir;
      });
    }

    // no date sort
    return items;
  },

  totalPages() {
    return Math.ceil(this.filteredInventory.length / this.itemsPerPage) || 1;
  },

  paginatedItems() {
    const start = (this.page - 1) * this.itemsPerPage;
    return this.filteredInventory.slice(start, start + this.itemsPerPage);
  },

  filteredAdjustItems() {
    const q = this.adjustSearch.toLowerCase();
    if (!q) return [];
    return this.items.filter((i) =>
      i.item_name?.toLowerCase().includes(q)
    );
  }
},

  watch: {
    searchQuery() {
      this.page = 1;
    },
    filterCategory() {
      this.page = 1;
    },
    filterLocation() {
      this.page = 1;
    }
  },

  methods: {
    async loadItems() {
      try {
        this.loading = true;
        const res = await api.get("/items");
        this.items = res.data;
      } catch (err) {
        console.error(err);
        this.error = "Failed to load items.";
      } finally {
        this.loading = false;
      }
    },

    async loadDropdowns() {
      try {
        const [typesRes, catRes, locRes] = await Promise.all([
          api.get("/item-types"),
          api.get("/categories"),
          api.get("/locations")
        ]);
        this.itemTypes = typesRes.data;
        this.categories = catRes.data;
        this.locations = locRes.data;
      } catch (err) {
        console.error("Load dropdowns failed:", err.response?.data || err.message);
      }
    },

    async addItem(newItem) {
      try {
        newItem.quantity = parseInt(newItem.quantity ?? 0, 10);
        const res = await api.post("/items", newItem);
        console.log("Item created:", res.data);
        await this.loadItems();
        this.showStatusModal({
          title: "Add Item",
          message: "The item has been added successfully.",
          type: "success"
        });
      } catch (err) {
        console.error("Add item failed:", err.response?.data || err.message);
        this.showStatusModal({
          title: "Add Item",
          message: "Error adding item.",
          type: "error"
        });
      }
    },

    async updateItem(id, updatedData) {
      try {
        const res = await api.put(`/items/${id}`, updatedData);
        console.log("Item updated:", res.data);
        await this.loadItems();
        this.showStatusModal({
          title: "Edit Item",
          message: "The item was updated successfully.",
          type: "info"
        });
      } catch (err) {
        console.error("Update item failed:", err.response?.data || err.message);
        this.showStatusModal({
          title: "Edit Item",
          message: "Error updating item.",
          type: "error"
        });
      }
    },

    async deleteItem(id) {
      if (!confirm("Delete this item?")) return;
      try {
        await api.delete(`/items/${id}`);
        await this.loadItems();
        this.showStatusModal({
          title: "Delete Item",
          message: "The item was deleted successfully.",
          type: "error"
        });
      } catch (err) {
        console.error("Delete item failed:", err.response?.data || err.message);
        this.showStatusModal({
          title: "Delete Item",
          message: "Error deleting item.",
          type: "error"
        });
      }
    },

    // status modal helpers
    showStatusModal({ title, message, type = "info" }) {
      this.statusModal.title = title;
      this.statusModal.message = message;
      this.statusModal.type = type;
      this.statusModal.visible = true;
    },

    closeStatusModal() {
      this.statusModal.visible = false;
    },

    // ADD MODAL
    openAddModal() {
      this.showAddModal = true;
      this.addRows = [
        { name: "", item_type_id: "", category_id: "", location_id: "", quantity: 0 }
      ];
    },

    closeAddModal() {
      this.showAddModal = false;
    },

    addRow() {
      this.addRows.push({
        name: "",
        item_type_id: "",
        category_id: "",
        location_id: "",
        quantity: 0
      });
    },

    removeAddRow(index) {
      this.addRows.splice(index, 1);
      if (this.addRows.length === 0) {
        this.addRows.push({
          name: "",
          item_type_id: "",
          category_id: "",
          location_id: "",
          quantity: 0
        });
      }
    },

    async confirmAddRows() {
      for (const row of this.addRows) {
        if (
          !row.name ||
          !row.item_type_id ||
          !row.category_id ||
          !row.location_id ||
          row.quantity === null ||
          row.quantity === ""
        ) {
          continue;
        }

        const payload = {
          item_code: null,
          item_name: row.name,
          item_type_id: row.item_type_id,
          category_id: row.category_id,
          location_id: row.location_id,
          quantity: Number(row.quantity) || 0,
          low_stock_threshold: 10
        };

        await this.addItem(payload);
      }
      this.closeAddModal();
    },

    // SORTING & PAGINATION
    sortByField(field) {
      this.filterSort = field;
      this.page = 1;
    },

    prevPage() {
      if (this.page > 1) this.page--;
    },

    nextPage() {
      if (this.page < this.totalPages) this.page++;
    },

    // STOCK IN / OUT
    openAdjustModal(action) {
      this.adjustAction = action;
      this.showAdjustModal = true;
      this.adjustItem = {};
      this.adjustSearch = "";
      this.adjustQtyInput = 1;
      this.handledBy = "";
      this.adjustPurpose = "";
    },

    selectAdjustItem(item) {
      this.adjustItem = { ...item };
    },

    closeAdjustModal() {
      this.showAdjustModal = false;
      this.adjustItem = {};
    },

    async confirmAdjust() {
  const qty = Number(this.adjustQtyInput) || 0;
  if (qty <= 0) {
    alert("Quantity must be greater than 0");
    return;
  }
  if (!this.adjustItem.id) {
    alert("Please select an item.");
    return;
  }

  const payload = {
    quantity: qty,
    purpose: this.adjustAction === "stock_out" ? this.adjustPurpose || "" : undefined,
  };

  try {
    if (this.adjustAction === "stock_in") {
      await api.post(`/items/${this.adjustItem.id}/stock-in`, payload);
    } else {
      await api.post(`/items/${this.adjustItem.id}/stock-out`, payload);
    }
    await this.loadItems();
    this.closeAdjustModal();
  } catch (err) {
    console.error("Stock adjust failed:", err.response?.data || err.message);
    alert("Error adjusting stock");
  }
},

    // EDIT MODAL
    openEditModal(item) {
      this.editClone = {
        id: item.id,
        item_code: item.item_code || null,
        name: item.item_name,
        item_type_id: item.item_type_id,
        category_id: item.category_id,
        location_id: item.location_id,
        quantity: item.quantity,
        low_stock_threshold: item.low_stock_threshold ?? 10
      };
      this.showEditModal = true;
    },

    closeEditModal() {
      this.showEditModal = false;
    },

    async saveEdit() {
      const payload = {
        item_code: this.editClone.item_code || null,
        item_type_id: this.editClone.item_type_id,
        category_id: this.editClone.category_id,
        location_id: this.editClone.location_id,
        quantity: this.editClone.quantity ?? 0,
        low_stock_threshold: this.editClone.low_stock_threshold ?? 10
      };

      await this.updateItem(this.editClone.id, payload);
      this.closeEditModal();
    },

    // DELETE MODAL
    requestDelete(item) {
      this.deleteCandidateItem = item;
      this.deletePasswordInput = "";
      this.showDeletePasswordModal = true;
    },

    closeDeletePasswordModal() {
      this.showDeletePasswordModal = false;
    },

    async confirmDeleteWithPassword() {
      await this.deleteItem(this.deleteCandidateItem.id);
      this.closeDeletePasswordModal();
    }
  },

  mounted() {
    this.loadItems();
    this.loadDropdowns();
  }
};
</script>




<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css";


.top-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 12px 0;
  width: 100%;
}


.right-actions {
  display: flex;
  gap: 8px;
}


/* Remove absolute positioning from old button */
.add-btn {
  background: #010253;
  color: white;
  padding: 9px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  position: static !important;
}
.add-btn:hover {
  background: #2A3B8A;
}


/* =============================
   MAIN RIGHT CONTENT AREA
================================ */
.rectangle-red{
    width: 107%;
    height: 20px;
    background-color: #E02323;
    margin-top: -20px;
    margin-left: -37px;
}


.top-rectangle{
    background-color:#FFFFFF ;
    box-shadow: 5px 5px 8px 2px rgba(0, 0, 0, 0.3);
    width: 168.5vh;
    height: 80px;
    margin-top: -24px;
    margin-left: -30px;
}


/* TITLE */
.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -40px;
  margin-top: 20px;
  position: absolute;


}


h2{
    position: relative;
    top: 25px;
}

/* FILTER BAR */
.filter-bar {
  position: relative;
  top: 1px;
  left: -300px;
  gap: 10px;
  margin-bottom: 20px;
 
}


.fa-filter{
  position: relative;
  top: 32px;
  left: 1100px;
  font-size: medium;
  z-index: 1;
}


.filter-bar input,
.filter-bar select {
  padding: 5px 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;


}


.sort{
  padding: 5px 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
  position: relative;
  left:985px;
  top: 30px;
  color: #777;
}


.add-btn {
  position: absolute;
  left:1350px;
  top: 700px;
  background: #010253;
  color: white;
  padding: 9px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.add-btn:hover {
  background: #2A3B8A;
}


/* TABLE */
.table-container {
  background: white;
  padding: 20px;
  border-radius: 0px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  margin-top: 100px;
  border: 1px solid #010253;
  margin-top: 60px;
  min-height: 370px;        
  display: flex;
  flex-direction: column;
}



.table-container a {
  text-decoration: none;    
  color: #777;                
  font-weight: 500;          
}


.table-container a:hover {
  color: #2A3B8A;            
 
}




table {
  width: 100%;
  border-collapse: collapse;
 
}



thead th {
  padding: 12px 10px;
 
  color: #010253;
  font-size: 14px;
  border-bottom: 2px solid #ddd;
  text-align: justify;
 
}


tbody td {
  padding: 12px 10px;
  border-bottom: 1px solid #eee;
}


tbody tr:hover {
  background: #fafafa;
}


/* ACTION BUTTONS */
.edit {
  background: #010253;
  color: white;
  padding: 3px 10px;
  border: none;
  border-radius: 5px;
  box-shadow: black;
}

.stock-in {
  background: #010253;
  color: white;
  padding: 9px 18px;
  border: none;
  border-radius: 5px;
  box-shadow: black;
}

.stock-out {
  background: #b81c1c;
  color: white;
  padding: 9px 18px;
  border: none;
  border-radius: 5px;
  box-shadow: black;
}


.edit:hover {
  background:  #2A3B8A;
}


.delete {
  background: #b81c1c;
  color: white;
  padding: 3px 10px;
  border: none;
  border-radius: 5px;
  box-shadow: black;
}
.delete:hover {
  background: #E02323;
}






/* MODALS */
.overlay {
   position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.100); /* black with transparency */
    backdrop-filter: blur(2px); /* blur the background */
    -webkit-backdrop-filter: blur(5px); /* Safari */
    z-index: 3; /* on top of everything */
   /* hide by default */
}


.modal {
    width: 340px;
    margin: 64px auto;
    padding: 32px 115px;
    border: 1px solid none;
    background: #EFF3F4;
    box-shadow: 0 0px 0px rgba(0, 0, 0, 0.6);
    margin-top: 140px ;
    margin-right: 390px;
    border-radius: 30px;

}


.header{
    text-align: center;
    font-size: 2.2em;
    letter-spacing: 2px;
    background-color: #010253;
    padding: 20px 285px;
    border-radius: 30px;
    position: absolute;
    left: 561px;
    top: 120px;
    border-bottom-left-radius: 0%;
    border-bottom-right-radius: 0%;
}


.header-add{
   width: 340px;
  text-align: center;
    font-size: 2.2em;
    letter-spacing: 2px;
    background-color: #010253;
    padding: 20px 430px;
    border-radius: 30px;
    position: absolute;
    left: 461px;
    top: 110px;
    border-bottom-left-radius: 0%;
    border-bottom-right-radius: 0%;
}


.header-delete{
  text-align: center;
    font-size: 2.2em;
    letter-spacing: 2px;
    background-color: #b81c1c;
    padding: 20px 285px;
    border-radius: 30px;
    position: absolute;
    left: 561px;
    top: 120px;
    border-bottom-left-radius: 0%;
    border-bottom-right-radius: 0%;
}


.header-delete h2{
  color: #b81c1c;
}




.modal-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: left;
  gap: 10px;
  text-align: center;
}


.modal label{
    display: block;
    margin-top: 10px;
    color: #010253;
    margin-left: 40px;
    text-align: left;


}


.modal p{
  text-align: center;
}
.modal input{
    width: 100px;
    padding: 5px 90px;
    margin-top: 5px;
    border: 1px solid #aaa;
    border-radius: 4px;
    margin-left: 40px;


}


.modal-add input{
  width: 100px;
    padding: 5px 90px;
    margin-top: 5px;
    border: 1px solid #aaa;
    border-radius: 4px;
    margin-left: -7px;
}


.modal h2{
    justify-content: center;
    text-align: center;
    margin-bottom: 50px;
    color:  #010253;
   
}


.cancel {
  background: #777;
  color: white;
  padding: 7px 15px;
  border-radius: 5px;
  justify-content: center;
}


.confirm {
  background: #010253;
  color: white;
  padding: 7px 15px;
  border-radius: 5px;
  justify-content: center;
}


.confirm-del {
  background: #b81c1c;
  color: white;
  padding: 7px 15px;
  border-radius: 5px;
  justify-content: center;
}


i{
    color:#010253;
    margin-left: 15px;
    font-size: x-large;
}


i:hover{
    color: #2A3B8A;
   
}


.notif{
    position: relative;
    top: -40px;
    left: 260px;
    color: #a29a9a;
    letter-spacing: 0.15em;
    font-family: Arial, sans-serif;
}


.fa-user-circle{
    position: relative;
    top: -83px;
    left: 400px;
    font-size: xx-large;


}


.fa-search{
  position: relative;
  top: 0px;
  left: -45px;
  font-size: smaller;


}


.content{
 
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  margin-left: 200px;
  margin-top: -29px;
  padding-bottom: 40px;
 
}


/* ADD MODAL (WIDE) */
.add-modal-wide {
  width: 780px;
  padding: 40px;
  margin-right: 200px;
}


/* TABLE INSIDE ADD MODAL */
.add-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}


.add-table th {
  background: #f5f5f5;
  padding: 10px;
  border: 1px solid #ddd;
}


.add-table td {
  padding: 8px;
  border: 1px solid #ddd;
}


.add-table input {
  width: 100%;
  padding: 6px;
  border: none;
  outline: none;
 
}


/* ADD ROW LINK */
.add-row-btn {
  margin-top: 10px;
  color: #010253;
  font-weight: bold;
  cursor: pointer;
}
.add-row-btn:hover {
  text-decoration: underline;
}


/* MODAL BUTTON ROW */
.modal-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-left: 50px;
}


.pagination{
    margin-top: 20px;
}
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
}

.page-btn.active {
  background-color: #010253;
  color: #fff;
}

.page-info {
  font-size: 14px;
  color: #444;
}


/* small active page style */
.pagination button.active {
  background: #010253;
  color: #fff;
  border-radius: 4px;
  padding: 4px 8px;
}

html, body {
  overflow: hidden !important;
  height: 100% !important;
}

.status-header{
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>



