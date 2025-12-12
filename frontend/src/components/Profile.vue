<template>
  <div class="profile-container">
    <h2 class="title">User Profile</h2>

    <div class="profile-card">
      <div class="profile-left">
        <img :src="avatarUrl" class="avatar-large" />

        <label class="upload-btn">
          Change Avatar
          <input type="file" @change="changeAvatar" />
        </label>

        <h3>{{ user.name }}</h3>
        <p class="role">{{ user.role }}</p>
      </div>

      <div class="profile-right">
        <h3>Account Information</h3>

        <div class="form-row">
          <label>Full Name</label>
          <input v-model="user.name" type="text" />
        </div>

        <div class="form-row">
          <label>Email</label>
          <input v-model="user.email" type="email" />
        </div>

        <div class="form-row">
          <label>Username</label>
          <input v-model="user.username" type="text" />
        </div>

        <h3>Change Password</h3>

        <div class="form-row">
          <label>Current Password</label>
          <input type="password" v-model="password.current" />
        </div>

        <div class="form-row">
          <label>New Password</label>
          <input type="password" v-model="password.newPass" />
        </div>

        <div class="form-row">
          <label>Confirm New Password</label>
          <input type="password" v-model="password.confirmPass" />
        </div>

        <button class="save-btn" @click="saveProfile">Save Changes</button>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";

export default {
  name: "ProfilePage",

  data() {
    return {
      avatarUrl: "",
      user: {
        name: "",
        username: "",
        email: "",
        role: "",
      },
      password: {
        current: "",
        newPass: "",
        confirmPass: "",
      },
    };
  },

  async mounted() {
    const res = await api.get("/user");
    this.user = res.data.user;
    this.avatarUrl = res.data.user.avatar;
  },

  methods: {
    async changeAvatar(e) {
      const file = e.target.files[0];
      const form = new FormData();
      form.append("avatar", file);

      const res = await api.post("/user/avatar", form);

      this.avatarUrl = res.data.avatar_url;
      alert("Avatar updated!");
    },

    async saveProfile() {
      if (this.password.newPass !== this.password.confirmPass) {
        alert("❌ Passwords do not match!");
        return;
      }

      await api.post("/user/update", {
        name: this.user.name,
        username: this.user.username,
        email: this.user.email,
        password: this.password.newPass,
      });

      alert("Profile saved successfully!");
    },
  },
};
</script>


<style scoped>
.profile-container {
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

.profile-card {
  display: flex;
  background: #fff;
  padding: 25px;
  border-radius: 0px;
  border: 1px solid #010253;
  gap: 40px;
  box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
}

.profile-left {
  text-align: center;
  width: 250px;
}

.avatar-large {
  width: 150px;
  border-radius: 50%;
  margin-bottom: 15px;
}

.upload-btn {
  display: inline-block;
  background: #ececec;
  padding: 10px;
  margin-top: 10px;
  border-radius: 8px;
  cursor: pointer;
}

.upload-btn input {
  display: none;
}

.role {
  font-size: 14px;
  color: gray;
}

.profile-right {
  flex-grow: 1;
}

h3 {
  margin-top: 20px;
}

.form-row {
  margin-bottom: 15px;
}

label {
  font-weight: bold;
}

input {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.save-btn {
  padding: 12px 20px;
  background: #010253;
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  margin-top: 20px;
}
</style>
