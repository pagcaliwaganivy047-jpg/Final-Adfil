import { createRouter, createWebHistory } from 'vue-router';


// Import your pages
import Supplier from '@/components/Supplier.vue';
import LoginView from '@/components/LogIn.vue';
import AppInventory from '@/components/Inventory.vue';
import Transactions from '@/components/Transaction.vue';
import Reports from '@/components/Reports.vue';
import Settings from '@/components/Settings.vue';
import Dashboard from '@/components/Dashboard.vue';
import NotificationsPage from '@/components/Notifications.vue';
import ProfilePage from '@/components/Profile.vue';






const routes = [
  {
    path: '/',
    redirect: '/dashboard'   // default page
  },


 
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard
  },
  {
    path: '/inventory',
    name: 'inventory',
    component: AppInventory
  },
  {
    path: '/transactions',
    name: 'transactions',
    component: Transactions
  },
  {
    path: '/suppliers',
    name: 'suppliers',
    component: Supplier
  },
  {
    path: '/reports',
    name: 'reports',
    component: Reports
  },
  {
    path: '/settings',
    name: 'settings',
    component: Settings
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: {hideLayout: true}
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: NotificationsPage
  },
  {
  path: '/profile',
  name: 'profile',
  component: ProfilePage  
  },


{
  path: "/supplier-record/:id",
  name: "SupplierRecord",
  component: () => import("@/components/SupplierRecord.vue")
}


];




const router = createRouter({
  history: createWebHistory(),
  routes
});


export default router;









