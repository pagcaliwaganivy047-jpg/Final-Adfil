<template>
  <!--Sidebar Navigation-->
  <aside v-if="$route.name !== 'login'" class="sidebar">


    <!-- BRAND -->
    <div class="brand">
      <span style="color:#010253; font-weight: bold;">
        ADF<i>i</i>L <br> CORPORATION
      </span>
    </div>




    <!-- MAIN NAVIGATION -->
    <nav class="nav-menu">
      <ul>
        <li><router-link to="/dashboard" exact><i class="fa-solid fa-layer-group"></i>&nbsp; Dashboard</router-link></li>
        <li><router-link to="/inventory" exact><i class="fa-solid fa-boxes-stacked"></i>&nbsp; Inventory</router-link></li>
        <li><router-link to="/transactions" exact><i class="fa-solid fa-arrow-right-arrow-left"></i>&nbsp; Transactions</router-link></li>
        <li><router-link to="/suppliers" exact><i class="fa-solid fa-store"></i>&nbsp; Suppliers</router-link></li>
        <li><router-link to="/reports" exact><i class="fa-solid fa-chart-column"></i>&nbsp; Reports</router-link></li>
        <li><router-link to="/settings" exact><i class="fa-solid fa-gear"></i>&nbsp; Settings</router-link></li>
      </ul>
    </nav>




    <!-- LOGOUT BUTTON -->
<nav class="logout-nav">
  <ul>
    <li class="logout-item" @click="logout">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </li>
  </ul>
</nav>




  </aside>




  <div v-if="$route.name !== 'login'" class="rectangle-red"></div>


<div v-if="$route.name !== 'login'" class="top-rectangle">


    <div class="page-header">
      <div class="header-left"></div>




      <div class="header-right">




        <!-- 🔍 GLOBAL SEARCH BAR -->
        <div class="search-wrapper">
          <input
            type="text"
            class="global-search"
            placeholder="Search anything..."
            v-model="globalSearch"
            @input="emitGlobalSearch"
          />
          <i class="fa fa-search search-icon"></i>
        </div>




        <!-- 🔔 Notifications -->
        <button class="icon-btn notif-btn" @click="openNotifications" title="Notifications">
          <i class="fa fa-bell"></i>
          <p class="notif">Notifications</p>
          <span v-if="unreadCount" class="badge">{{ unreadCount }}</span>
        </button>




        <!-- 👤 Profile -->
        <div class="profile" @click="toggleProfile">
          <img class="avatar" :src="avatarUrl" alt="avatar" />




          <div v-if="profileOpen" class="profile-menu">
            <button @click="goToProfile">Profile</button>
          </div>
        </div>




      </div>
    </div>
  </div>




  <!-- ROUTER PAGES -->
  <div class="content">
    <router-view :search-query="globalSearch"></router-view>
  </div>
</template>




<script>




// ✅ Correct import for event bus
import emitter from "./eventBus";




export default {
  name: "App",




  data() {
    return {
      avatarUrl: "https://cdn-icons-png.flaticon.com/512/1946/1946429.png",
      userName: "Admin",
      profileOpen: false,




      notifications: [],
      unreadCount: 0,




      globalSearch: "", // 🔍 GLOBAL SEARCH VARIABLE
    };
  },




  mounted() {
    this.updateBadge();
    this.checkAlerts();




    // Listen for notification updates
    window.addEventListener("notif-update", () => {
      this.updateBadge();
    });
  },




  methods: {
    // Vue 3 FIX: use mitt instead of this.$root.$emit
    emitGlobalSearch() {
      emitter.emit("global-search", this.globalSearch);
    },




    toggleProfile() {
      this.profileOpen = !this.profileOpen;
    },




    goToProfile() {
      this.$router.push("/profile");
      this.profileOpen = false;
    },




    logout() {
      alert("Logged out successfully!");
      this.profileOpen = false;
      this.$router.push("/login");
    },




    openNotifications() {
      this.$router.push("/notifications");
      this.unreadCount = 0;
    },




    checkAlerts() {
      this.addNotification("Warning: Cement is below minimum stock!");
      this.addNotification("Emergency: Steel bars have reached zero stock!");
      this.addNotification("Alert: 5 gallons of paint will expire in 3 days.");
    },




    addNotification(message) {
      this.notifications.push(message);
      this.unreadCount++;
    },




    updateBadge() {
      const notifs = JSON.parse(localStorage.getItem("notifications")) || [];
      this.unreadCount = notifs.filter(n => !n.read).length;
    },
  },




    watch: {
    $route(to) {
      // Reset notifications when navigating to notifications page
      if (to.name === "notifications") {
        this.unreadCount = 0;
      }




      // Hide profile dropdown when going to login page
      if (to.path === "/login") {
        this.profileOpen = false;
      }
    }
  }
};
</script>












<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css";


/* 🔵 Active Link Style */
.nav-menu a.router-link-active,
.nav-menu a.router-link-exact-active {
  background-color: #010253;
  color: #ffffff !important;
  border-radius: 6px;
  padding: 10px 12px;
}
/* Ensure active link remains correct even on hover */
.nav-menu a.router-link-active:hover,
.nav-menu a.router-link-exact-active:hover {
  background-color: #010253 !important;
  color: #ffffff !important;
}
/* Active */
.nav-menu a.router-link-active,
.nav-menu a.router-link-exact-active {
  background-color: #010253;
  color: #ffffff !important;
  border-radius: 6px;
  padding: 10px 12px;
}
/* Base link style for ALL menu items */
.nav-menu li a {
  display: block;
  padding: 10px 12px; /* Set default size */
  border-radius: 6px;
  transition: 0.2s ease-in-out; /* Smooth hover */
}

/* Hover */
.nav-menu li:hover a {
  background-color: #010253;
  color: #ffffff !important;
}


/* Active + Hover (Priority Fix) */
.nav-menu a.router-link-active:hover,
.nav-menu a.router-link-exact-active:hover {
  background-color: #010253 !important;
  color: #ffffff !important;
}


/* Fix left alignment for sidebar menu */
.nav-menu ul {
  padding-left: 0 !important;
}




.nav-menu li {
  text-align: left !important;
  width: 100%;
  padding-left: 5px; /* for spacing of icons+text */
}
.logout-nav ul {
  list-style: none;
  padding-left: 0;
}




.logout-item {
  width: 100%;
  margin-top: auto;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  border-radius: 5px;
  color: #010253;
}




.logout-item:hover {
  background-color: #b71c1c;
  color: #ffffff;
}








.nav-menu a {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  text-align: left !important;
}




.global-search {
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid #010253;
  width: 220px;
  font-size: 14px;
 position: absolute;
  left: 1009px;
  top: 38px;
 
}




.fa-search{
  position: relative;
  top: -8px;
  left: 40px;
  font-size: medium;




}




.sidebar {
  width: 220px;
  background: #FFFFFF;
  color: #010253;
  height: 100vh;
  padding: 25px;
  position: fixed;
  left: 0;
  top: -8px;
  box-shadow: 1px 0 2px 2px rgba(0, 0, 0, 0.3);
}
#app-layout {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #f4f6f9;
  display: flex;
}
.nav-menu a {
  text-decoration: none !important;
  color: inherit;
}




.sidebar {
  width: 220px;
  background: #FFFFFF;
  color: #010253;
  height: 100vh;
  padding: 25px;
  position: fixed;
  left: 0;
  top: -8px;
  box-shadow: 1px 0 2px 2px rgba(0, 0, 0, 0.3);




}




.brand {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 60px;
  letter-spacing: 2px;
  text-align: left;
  line-height: 1.2;
 
}




.nav-menu ul {
  list-style: none;
  padding: 10;
  margin: 10;
  margin-right: 20px;
  text-decoration: none;
}
.nav-menu li {
  margin-bottom: 20px;
  padding: 10px 12px;
  color: #010253;
  text-decoration: none;
  font-size: 16px;
  border-radius: 5px;
  display: block;
  transition: 0.2s;
  width: 100%;
  text-decoration: none;
 
 
 




}





.brand i{
  color: #E02323;
}




i{
  margin-left: -1px;
}




.content{
  margin-left: 100px;
  padding: 20px;
  background-color:#EFF3F4 ;
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  padding-bottom: 40px;
}




.rectangle-red{
  max-width: 100vw;
  height: 20px;
  background-color: #E02323;
  margin-top: -10px;
  margin-left: 250px;
 
}




.top-rectangle{
  background-color:#FFFFFF ;
  max-width: 100vw;
  height: 80px;
  margin-top: -11px;
  margin-left: 250px;
}




.page-header { display:flex; justify-content:space-between; align-items:center; margin: 8px 20px; }
.header-left h2 { margin:0;margin-top: 27px; font-size: 30px; color:#0b2a66; }
.header-right { display:flex; margin-top: 27px; align-items:center; gap:12px; }
.icon-btn { position:relative;margin-left: 100px; border:none; background:transparent; cursor:pointer; font-size:18px; color:#222; }
.badge { position:absolute; top:-9px; left:-5px; background:#d0242d; color:#fff; border-radius:999px; padding:1px 5px; font-size:12px; }




.profile { display:flex; align-items:center; gap:8px; cursor:pointer; position:relative; user-select:none; left: -10px; top: -8px;}
.avatar { width:30px; height:30px; border-radius:50%; object-fit:cover; color: #010253;}
.profile-menu { position:absolute; right:0; top:44px; background:#fff; border:1px solid #eee; padding:8px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.08); display:flex; flex-direction:column; gap:6px; }




.title {
  font-size: 30px;
  color: #010253;
  font-weight: bold;
  margin-left: 50px;
  margin-top: 10px;
}




.fa-bell{
  margin-left: -155px;
  margin-top: -3px;
  font-size: 26px;
  color: #010253;
}




.notif {
  margin-left: 0px;
  margin-right: 25px;
  margin-top: -20px;
  font-size: 15px;
  color: gray;
  letter-spacing: 0.10em;
  font-family: Arial, sans-serif;




}




h2{
  margin-left: 20px;
  margin-top: -10px;
}




.filter-bar {
 position: relative;




  gap: 10px;
  margin-bottom: 20px;
}




.filter-bar input,
.filter-bar select {
  padding: 5px 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
}




.sidebar {
  display: flex;
  flex-direction: column;
  justify-content: space-between; /* Moves logout to bottom */
  height: 100vh;
  padding-bottom: 20px;
}




.logout-section {
  margin-top: auto;
  padding: 20px;
}




.logout-btn {
  width: 100%;
  padding: 10px 15px;
  background-color: #ffffff;
  color: #010253;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  text-align: left;
}




.logout-btn i {
  margin-right: 6px;
}




.logout-btn:hover {
  background-color: #b71c1c;
}




</style>









