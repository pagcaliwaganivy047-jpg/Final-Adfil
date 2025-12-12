import axios from "axios";

const api = axios.create({
  baseURL: "http://localhost:8000/api",
});

// attach token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// REPORTS
export const fetchReportSummary = () => api.get("/reports/summary");
export const fetchReportPreview = (params = {}) => api.get("/reports/preview", { params });
export const exportReportCSV = (params = {}) =>
  api.get("/reports/export", { params, responseType: "blob" });


export default api;
