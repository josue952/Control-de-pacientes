import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import UsuariosView from '@/views/UsuariosView.vue'
import PacientesView from '@/views/PacientesView.vue'
import DoctoresView from '@/views/DoctoresView.vue'
import CitasView from '@/views/CitasView.vue'
import ExamenesView from '@/views/ExamenesView.vue'
import PagosView from '@/views/PagosView.vue'
import ConsultasView from '@/views/ConsultasView.vue'
import MedicamentosView from '@/views/MedicamentosView.vue'
import RecetasView from '@/views/RecetasView.vue'
// Importar las vistas de los reportes
import ReportePaciente from '@/views/Reportes/ReportePaciente.vue'
import ReporteReceta from '@/views/Reportes/ReporteReceta.vue'
import ReporteExamenes from '@/views/Reportes/ReporteExamenes.vue'
import ReporteConsultas from '@/views/Reportes/ReporteConsultas.vue'

const routes = [
  {
    path: '/',
    name: 'Login',
    component: LoginView
  },
  {
    path: '/usuarios',
    name: 'Usuarios',
    component: UsuariosView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/pacientes',
    name: 'Pacientes',
    component: PacientesView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/doctores',
    name: 'Doctores',
    component: DoctoresView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/citas',
    name: 'Citas',
    component: CitasView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/examenes',
    name: 'Examenes',
    component: ExamenesView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/pagos',
    name: 'Pagos',
    component: PagosView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/consultas',
    name: 'Consultas',
    component: ConsultasView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/medicamentos',
    name: 'Medicamentos',
    component: MedicamentosView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/recetas',
    name: 'Recetas',
    component: RecetasView,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  //Rutas para los reportes
  {
    path: '/reporte-paciente',
    name: 'ReportePaciente',
    component: ReportePaciente,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/reporte-receta',
    name: 'ReporteReceta',
    component: ReporteReceta,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/reporte-examenes',
    name: 'ReporteExamenes',
    component: ReporteExamenes,
    meta: { requiresAuth: true } // Requiere autenticación
  },
  {
    path: '/reporte-consultas',
    name: 'ReporteConsultas',
    component: ReporteConsultas,
    meta: { requiresAuth: true } // Requiere autenticación
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Guardia de navegación global
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token'); // Verifica si hay token en el localStorage

  if (to.meta.requiresAuth && !isAuthenticated) {
    // Si la ruta requiere autenticación y no está autenticado, redirige al Login
    next({ name: 'Login' });
  } else if (to.name === 'Login' && isAuthenticated) {
    // Si intenta acceder al Login estando autenticado, redirige al Dashboard
    next({ name: 'Dashboard' });
  } else {
    // Permite el acceso a la ruta
    next();
  }
});

export default router
