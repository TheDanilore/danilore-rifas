import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import Home from '@/views/Home.vue'
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import RifaDetail from '@/views/RifaDetail.vue'
import PremioDetail from '@/views/PremioDetail.vue'
import Ganadores from '@/views/Ganadores.vue'
import ComoFunciona from '@/views/ComoFunciona.vue'
import TerminosCondiciones from '@/views/TerminosCondiciones.vue'
import Dashboard from '@/views/Dashboard.vue'
import AdminLogin from '@/views/admin/AdminLogin.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import AdminRifas from '@/views/admin/AdminRifas.vue'
import AdminUsuarios from '@/views/admin/AdminUsuarios.vue'
import AdminVentas from '@/views/admin/AdminVentas.vue'
import NotFound from '@/views/NotFound.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresGuest: true }
  },
  {
    path: '/rifa/:id',
    name: 'RifaDetail',
    component: RifaDetail,
    props: true
  },
  {
    path: '/premio/:codigoUnico/:codigoPremio',
    name: 'PremioDetail',
    component: PremioDetail,
    props: true
  },
  {
    path: '/ganadores',
    name: 'Ganadores',
    component: Ganadores
  },
  {
    path: '/como-funciona',
    name: 'ComoFunciona',
    component: ComoFunciona
  },
  {
    path: '/terminos-condiciones',
    name: 'TerminosCondiciones',  
    component: TerminosCondiciones
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'AdminLogin',
    component: AdminLogin,
    meta: { requiresAdminGuest: true }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAdmin: true }
  },
  {
    path: '/admin/rifas',
    name: 'AdminRifas',
    component: AdminRifas,
    meta: { requiresAdmin: true }
  },
  {
    path: '/admin/usuarios',
    name: 'AdminUsuarios',
    component: AdminUsuarios,
    meta: { requiresAdmin: true }
  },
  {
    path: '/admin/ventas',
    name: 'AdminVentas',
    component: AdminVentas,
    meta: { requiresAdmin: true }
  },
  {
    path: '/perfil',
    redirect: '/dashboard'
  },
  {
    path: '/mis-rifas',
    redirect: '/dashboard'
  },
  {
    path: '/historial',
    redirect: '/dashboard'
  },
  // Ruta 404 - debe ir al final
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Navigation Guards
router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin } = useAuthStore()
  
  // Rutas que requieren autenticación de admin
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    if (!isAuthenticated.value || !isAdmin.value) {
      next('/admin')
      return
    }
  }
  
  // Rutas de admin que solo pueden acceder usuarios no autenticados como admin
  if (to.matched.some(record => record.meta.requiresAdminGuest)) {
    if (isAuthenticated.value && isAdmin.value) {
      next('/admin/dashboard')
      return
    }
  }
  
  // Rutas que requieren autenticación
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated.value) {
      next('/login')
      return
    }
  }
  
  // Rutas que solo pueden acceder usuarios no autenticados
  if (to.matched.some(record => record.meta.requiresGuest)) {
    if (isAuthenticated.value) {
      next('/dashboard')
      return
    }
  }
  
  next()
})

export default router
