import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import Home from '@/views/public/HomeView.vue'
import Login from '@/views/public/LoginView.vue'
import Register from '@/views/public/RegisterView.vue'
import RifaDetail from '@/views/public/RifaDetailView.vue'
import PremioDetail from '@/views/public/PremioDetailView.vue'
import Ganadores from '@/views/public/GanadoresView.vue'
import ComoFunciona from '@/views/public/ComoFuncionaView.vue'
import TerminosCondiciones from '@/views/public/TerminosView.vue'
import Dashboard from '@/views/user/DashboardView.vue'
import Perfil from '@/views/user/PerfilView.vue'
import MisRifas from '@/views/user/MisRifasView.vue'
import Historial from '@/views/user/HistorialView.vue'
import AdminLogin from '@/views/admin/AdminLoginView.vue'
import AdminDashboard from '@/views/admin/AdminDashboardView.vue'
import AdminRifas from '@/views/admin/AdminRifasView.vue'
import AdminUsuarios from '@/views/admin/AdminUsuariosView.vue'
import AdminVentas from '@/views/admin/AdminVentasView.vue'
import NotFound from '@/views/errors/NotFoundView.vue'

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
    name: 'Perfil',
    component: Perfil,
    meta: { requiresAuth: true }
  },
  {
    path: '/mis-rifas',
    name: 'MisRifas',
    component: MisRifas,
    meta: { requiresAuth: true }
  },
  {
    path: '/historial',
    name: 'Historial',
    component: Historial,
    meta: { requiresAuth: true }
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
