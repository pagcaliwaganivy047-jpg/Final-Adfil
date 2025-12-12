<template>
  <div class="content">
    <h2 class="title">Supplier</h2>

    <div>
      <i class="fa fa-filter" aria-hidden="true"></i>
      <select class="sort" v-model="filterSort">
        <option value="">All</option>
        <option value="name">Supplier Name</option>
        <option value="contact">Contact</option>
      </select>
    </div>

    <section class="table-container">
      <table>
        <thead>
          <tr>
            <th>Supplier Name</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Record</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="supplier in paginatedSuppliers" :key="supplier.id">
            <td>{{ supplier.name }}</td>
            <td>{{ supplier.contact }}</td>
            <td>{{ supplier.email }}</td>
            <td>
              <router-link
                class="clickhere"
                :to="{ name: 'SupplierRecord', params: { id: supplier.id } }"
              >
                Click Here
              </router-link>
            </td>
            <td>
              <button class="edit" @click="editSupplier(supplier)">Edit</button>
              <button class="delete" @click="openDeleteModal(supplier.id)">Delete</button>
            </td>
          </tr>

          <tr v-if="filteredSuppliers.length === 0">
            <td colspan="5" style="text-align:center; color:#777;">No suppliers found</td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- Pagination -->
    <div
      class="pagination"
      style="margin-top:16px; display:flex; gap:8px; align-items:center;"
    >
      <button @click="prevPage" :disabled="page === 1">Previous</button>

      <button
        v-for="p in totalPages"
        :key="p"
        :class="{ active: page === p }"
        @click="page = p"
        style="min-width:32px;"
      >
        {{ p }}
      </button>

      <button @click="nextPage" :disabled="page === totalPages">Next</button>

      <div style="margin-left:12px; color:#666;">
        Page {{ page }} / {{ totalPages }} — {{ filteredSuppliers.length }} items
      </div>
    </div>

    <!-- ADD MODAL -->
    <div class="overlay" v-if="showAddModal">
      <div class="header-add"></div>
      <div class="modal add-modal-wide">
        <h2>Add Supplier</h2>

        <table class="add-table">
          <thead>
            <tr>
              <th>Supplier</th>
              <th>Contact</th>
              <th>Email</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in addRows" :key="index">
              <td class="modal-add">
                <input v-model="row.name" type="text" placeholder="Supplier" />
              </td>
              <td class="modal-add">
                <input v-model="row.contact" type="text" placeholder="Contact" />
              </td>
              <td class="modal-add">
                <input v-model="row.email" type="email" placeholder="Email" />
              </td>
              <td>
                <button class="delete" @click="removeAddRow(index)" title="Remove row">
                  Remove
                </button>
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

    <!-- EDIT MODAL -->
    <div class="overlay" v-if="showEditModal">
      <div class="header"></div>
      <div class="modal">
        <h2>Edit Supplier</h2>
        <form @submit.prevent="saveEdit">
          <label>Name:</label>
          <input v-model="editData.name" required />

          <label>Contact:</label>
          <input v-model="editData.contact" required />

          <label>Email:</label>
          <input v-model="editData.email" required />

          <div class="modal-buttons">
            <button class="cancel" type="button" @click="closeEditModal">Cancel</button>
            <button class="confirm" type="submit">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="overlay" v-if="showDeleteModal">
      <div class="header-delete"></div>
      <div class="modal">
        <h2 style="color: #b81c1c;">Delete Supplier</h2>
        <p>This action cannot be undone. Enter password to confirm deletion.</p>
        <input type="password" v-model="deletePassword" placeholder="Enter password" />
        <div class="modal-buttons">
          <button class="cancel" @click="closeDeleteModal">Cancel</button>
          <button class="confirm-del" @click="confirmDelete">Confirm</button>
        </div>
      </div>
    </div>

    <button class="add-btn" @click="showAddModal = true">Add Supplier</button>

    <!-- STATUS MODAL FOR ADD / EDIT / DELETE -->
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
  name: "SupplierView",

  data() {
    return {
      suppliers: [],
      searchQuery: "",
      filterSort: "",

      showAddModal: false,
      showEditModal: false,
      showDeleteModal: false,

      addRows: [{ name: "", contact: "", email: "" }],

      editData: null,
      deleteId: null,
      deletePassword: "",

      page: 1,
      perPage: 15,

      statusModal: {
        visible: false,
        title: "",
        message: "",
        type: "info" // 'success' | 'info' | 'error'
      }
    };
  },

  computed: {
    filteredSuppliers() {
      let s = this.suppliers.slice();

      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase();
        s = s.filter(
          (sup) =>
            sup.name.toLowerCase().includes(q) ||
            (sup.contact || "").toLowerCase().includes(q) ||
            (sup.email || "").toLowerCase().includes(q)
        );
      }

      if (this.filterSort === "name") {
        s.sort((a, b) => a.name.localeCompare(b.name));
      } else if (this.filterSort === "contact") {
        s.sort((a, b) => (a.contact || "").localeCompare(b.contact || ""));
      }

      return s;
    },

    paginatedSuppliers() {
      const start = (this.page - 1) * this.perPage;
      return this.filteredSuppliers.slice(start, start + this.perPage);
    },

    totalPages() {
      return Math.max(1, Math.ceil(this.filteredSuppliers.length / this.perPage));
    }
  },

  watch: {
    searchQuery() {
      this.page = 1;
    },
    filterSort() {
      this.page = 1;
    }
  },

  methods: {
    showStatusModal({ title, message, type = "info" }) {
      this.statusModal.title = title;
      this.statusModal.message = message;
      this.statusModal.type = type;
      this.statusModal.visible = true;
    },

    closeStatusModal() {
      this.statusModal.visible = false;
    },

    // LOAD SUPPLIERS
    async loadSuppliers() {
      try {
        const res = await api.get("/suppliers");
        this.suppliers = res.data;
      } catch (err) {
        console.error("Failed to load suppliers:", err);
      }
    },

    // ADD SUPPLIERS (MULTI-ROW)
    async confirmAddRows() {
      try {
        for (const row of this.addRows) {
          if (!row.name) continue;

          await api.post("/suppliers", {
            supplier_name: row.name,
            contact: row.contact,
            email: row.email
          });
        }

        this.showAddModal = false;
        this.addRows = [{ name: "", contact: "", email: "" }];
        await this.loadSuppliers();

        this.showStatusModal({
          title: "Add Supplier",
          message: "The supplier has been added successfully.",
          type: "success"
        });
      } catch (err) {
        console.error("Add supplier failed:", err.response?.data || err.message);
        this.showStatusModal({
          title: "Add Supplier",
          message: "Error adding supplier. Please check the form or try again.",
          type: "error"
        });
      }
    },

    addRow() {
      this.addRows.push({ name: "", contact: "", email: "" });
    },

    removeAddRow(index) {
      if (this.addRows.length > 1) this.addRows.splice(index, 1);
    },

    closeAddModal() {
      this.addRows = [{ name: "", contact: "", email: "" }];
      this.showAddModal = false;
    },

    // EDIT SUPPLIER
    editSupplier(supplier) {
      this.editData = { ...supplier };
      this.showEditModal = true;
    },

    async saveEdit() {
      try {
        await api.put(`/suppliers/${this.editData.id}`, {
          supplier_name: this.editData.name,
          contact: this.editData.contact,
          email: this.editData.email
        });

        this.showEditModal = false;
        this.editData = null;
        await this.loadSuppliers();

        this.showStatusModal({
          title: "Edit Supplier",
          message: "The supplier was updated successfully.",
          type: "info"
        });
      } catch (err) {
        console.error("Update supplier failed:", err);
        this.showStatusModal({
          title: "Edit Supplier",
          message: "Error updating supplier.",
          type: "error"
        });
      }
    },

    closeEditModal() {
      this.showEditModal = false;
      this.editData = null;
    },

    // DELETE SUPPLIER
    openDeleteModal(id) {
      this.deleteId = id;
      this.deletePassword = "";
      this.showDeleteModal = true;
    },

    closeDeleteModal() {
      this.showDeleteModal = false;
      this.deleteId = null;
      this.deletePassword = "";
    },

    async confirmDelete() {
      const adminPass = "admin123";

      if (this.deletePassword !== adminPass) {
        this.showStatusModal({
          title: "Delete Supplier",
          message: "Incorrect password.",
          type: "error"
        });
        return;
      }

      try {
        await api.delete(`/suppliers/${this.deleteId}`);
        this.closeDeleteModal();
        await this.loadSuppliers();

        this.showStatusModal({
          title: "Delete Supplier",
          message: "The supplier was deleted successfully.",
          type: "error"
        });
      } catch (err) {
        console.error("Delete supplier failed:", err);
        this.showStatusModal({
          title: "Delete Supplier",
          message: "Error deleting supplier.",
          type: "error"
        });
      }
    },

    // Pagination
    prevPage() {
      if (this.page > 1) this.page--;
    },

    nextPage() {
      if (this.page < this.totalPages) this.page++;
    }
  },

  mounted() {
    this.loadSuppliers();
  }
};
</script>





<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css";

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
    width: 170.5vh;
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
  top: -204px;
  left: 750px;
  gap: 10px;
  margin-bottom: 20px;
}

.fa-filter{
  position: relative;
  top: 32px;
  left: 1147px;
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
  left:960px;
  top: 30px;
  color: #777;
}

.add-btn {
  position: relative;
  left: 1015px;
  top: 10px;
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
}

table {
  width: 100%;
  border-collapse: collapse;
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
    
}

.header-add{
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

/* small active page style */
.pagination button.active {
  background: #010253;
  color: #fff;
  border-radius: 4px;
  padding: 4px 8px;
}

.clickhere{
  color: gray;
  text-decoration: none;
}

.status-modal {
  max-width: 360px;
  margin: 100px auto;
  background: #f7f9ff;
  border-radius: 8px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
  padding: 16px 20px 24px;
  text-align: center;
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

/* default header style, overridden by type classes */
.status-header h2 {
  margin: 0;
  font-size: 18px;
  color: #ffffff;
}

/* type colors */
.status-modal.success .status-header {
  background-color: #178a0a;
  border-radius: 6px 6px 0 0;
  margin: -16px -20px 16px -20px;
  padding: 10px 16px;
}

.status-modal.info .status-header {
  background-color: #555a80;
  border-radius: 6px 6px 0 0;
  margin: -16px -20px 16px -20px;
  padding: 10px 16px;
}

.status-modal.error .status-header {
  background-color: #c81e1e;
  border-radius: 6px 6px 0 0;
  margin: -16px -20px 16px -20px;
  padding: 10px 16px;
}

.status-message {
  margin: 0;
  font-weight: 600;
  color: #0a0a3a;
}

.close-btn {
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 20px;
  cursor: pointer;
}
</style>