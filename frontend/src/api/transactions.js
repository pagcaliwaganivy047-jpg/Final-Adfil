// src/api/transactions.js
import api from "./api";

export const getTransactions = () => api.get("/transactions");
export const getTransaction = id => api.get(`/transactions/${id}`);
export const createTransaction = data => api.post("/transactions", data);
export const deleteTransaction = id => api.delete(`/transactions/${id}`);
