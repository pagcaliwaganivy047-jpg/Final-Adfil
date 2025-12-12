// src/api/dashboard.js
import api from "./api";

export const getDashboard = () => api.get("/dashboard");
