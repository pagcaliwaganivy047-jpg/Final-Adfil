<template>
 <form @submit.prevent="login" class="login-form">
    <h2 class="header">
        <span style="color:#EFF3F4; font-weight: bold;">ADF<i>i</i>L  CORPORATION</span>
    </h2>

    <div class="form-group">
        <input  
          id="email" 
          v-model="email" 
          placeholder="Email" 
          type="email" 
          required 
        />
    </div>

    <div class="form-group">
        <input  
          id="password" 
          v-model="password" 
          placeholder="Enter Password" 
          type="password" 
          required 
        />
    </div>

    <button type="submit">Log in</button>

    <p v-if="error" class="error">{{ error }}</p>
 </form>
</template>

<script>
import api from "@/api/api";

export default {
  name: 'LoginView',
  data() {
    return {
      email: "",
      password: "",
      error: ""
    };
  },

  methods: {
    async login() {
      try {
        this.error = "";

        const res = await api.post("/login", {
          email: this.email,
          password: this.password,
        });

        // Save correct token
        localStorage.setItem("token", res.data.access_token);

        // Save user info
        localStorage.setItem("user", JSON.stringify(res.data.user));

        // Redirect
        this.$router.push("/dashboard");

      } catch (err) {
        this.error = "Invalid email or password.";
      }
    }
  }
};
</script>

<style scoped>
.login-form{
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
    padding: 20px 81px;
    border-radius: 30px;
    position: absolute;
    left: 568px;
    top: 30px;
    border-bottom-left-radius: 0%;
    border-bottom-right-radius: 0%;
}

.form-group{
    margin-bottom: 16px;
}

i{
    color: #D0242D;
}

input{
    width: 100%;
    padding: 6px;
    margin-bottom: 2px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-shadow: 0 1px 0px rgba(0, 0, 0, 0.3);
}

button{
    width: 100px;
    padding: 9px 0;
    background: #010253;
    color: white;
    border: white;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1em;
    box-shadow: 0 0px 6px rgb(0, 0, 0, 0.6);
}

button:hover{
    background-color: #f1f1f1;
}

.error {
  color: red;
  margin-top: 10px;
  text-align: center;
}
</style>
<style>
html, body {
  overflow: hidden !important;
  height: 100% !important;
}
</style>
