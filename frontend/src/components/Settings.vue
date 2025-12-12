<template>
  <div class="settings-wrapper">
    <h1 class="settings-title">Settings</h1>

    <section class="security-card">
      <h2 class="security-heading">Account Security</h2>

      <form class="security-form" @submit.prevent="saveChanges">
        <div class="form-group">
          <label>Username</label>
          <input type="email" v-model="username" disabled />
        </div>

        <p class="section-label">Change Password</p>

        <div class="form-group">
          <label>Current Password</label>
          <input type="password" v-model="currentPassword" />
        </div>

        <div class="form-group">
          <label>New Password</label>
          <input type="password" v-model="newPassword" />
        </div>

        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" v-model="confirmPassword" />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-save">Save Changes</button>
        </div>
      </form>
    </section>
  </div>
</template>

<script>
export default {
  name: "SystemSettings",

  data() {
    return {
      username: "adminuser@gmail.com", // replace with real user email from auth
      currentPassword: "",
      newPassword: "",
      confirmPassword: ""
    };
  },

  methods: {
    async saveChanges() {
      if (this.newPassword !== this.confirmPassword) {
        alert("New password and confirm password do not match.");
        return;
      }
      // TODO: call your API endpoint to change password
      // await api.post('/change-password', { currentPassword, newPassword });
      alert("Password updated (wire this to backend).");
    }
  }
};
</script>

<style scoped>
.settings-wrapper {
  padding: 30px;
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  margin-left: 200px;
}

.settings-title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: -50px;
  margin-top: -95px;
  position: absolute;
}

.security-card {
  margin-top: 60px;
  background: #ffffff;
  padding: 30px 40px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  border: 1px solid #ddd;
  max-width: 900px;
}

.security-heading {
  font-size: 20px;
  font-weight: 700;
  color: #010253;
  margin-bottom: 20px;
}

.security-form {
  max-width: 700px;
}

.section-label {
  font-weight: 600;
  margin: 20px 0 10px;
  color: #010253;
}

.form-group {
  margin-bottom: 14px;
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 4px;
}

.form-group input {
  padding: 8px 10px;
  border-radius: 3px;
  border: 1px solid #ccc;
  font-size: 14px;
}

.form-group input[disabled] {
  background: #f5f5f5;
}

.form-actions {
  margin-top: 20px;
  text-align: right;
}

.btn-save {
  padding: 10px 20px;
  background: #010253;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.btn-save:hover {
  background: #2a3b8a;
}
</style>
