import Vue from 'vue';
import Router from 'vue-router';

/**
 * Layzloading will create many files and slow on compiling, so best not to use lazyloading on devlopment.
 * The syntax is lazyloading, but we convert to proper require() with babel-plugin-syntax-dynamic-import
 * @see https://doc.laravue.dev/guide/advanced/lazy-loading.html
 */

Vue.use(Router);

/* Layout */
import Layout from '@/layout';
import adminRoutes from './modules/admin';
import permissionRoutes from './modules/permission';

/**
 * Sub-menu only appear when children.length>=1
 * @see https://doc.laravue.dev/guide/essentials/router-and-nav.html
 **/

/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    roles: ['admin', 'editor']   Visible for these roles only
    permissions: ['view menu zip', 'manage user'] Visible for these permissions only
    title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    noCache: true                if true, the page will no be cached(default is false)
    breadcrumb: false            if false, the item will hidden in breadcrumb (default is true)
    affix: true                  if true, the tag will affix in the tags-view
  }
**/

export const constantRoutes = [
  {
    path: '/login',
    component: () => import('@/views/login/index'),
    hidden: true,
  },
  {
    path: '/auth-redirect',
    component: () => import('@/views/login/AuthRedirect'),
    hidden: true,
  },
  {
    path: '/',
    component: Layout,
    redirect: 'dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index'),
        name: 'Dashboard',
        meta: { title: 'dashboard', icon: 'table', noCache: false, affix: true },
      },
    ],
  },
  {
    path: '/masterfile',
    component: Layout,
    redirect: '/masterfile/patients',
    name: 'Master File',
    meta: {
      title: 'Master File',
      icon: 'admin',
      // permissions: ['view menu nested routes'],
    },
    children: [
      {
        path: 'patients',
        name: 'Patients',
        component: () => import('@/views/patients/index'),
        meta: { title: 'Patients', icon: 'patients' },
      },
      /* {
        path: 'medicines',
        hidden: false,
        component: () => import('@/views/medicines/index'),
        meta: { title: 'Medicines' },
      },
      {
        path: 'diagnostics',
        hidden: false,
        component: () => import('@/views/diagnostics/index'),
        meta: { title: 'Diagnostics' },
      }, */
      {
        path: 'patient_form/:id',
        hidden: true,
        component: () => import('@/views/patients/form'),
        meta: { title: 'Patient Form' },
      },
      {
        path: 'profile/:id/:pid',
        hidden: true,
        component: () => import('@/views/patients/SelfProfile'),
        meta: { title: 'userProfile', icon: 'user', noCache: true },
      },
      {
        path: 'profile/:id/:pid/consultation/:appointmentId',
        hidden: true,
        component: () => import('@/views/patients/ConsultationRecordDetail'),
        meta: { title: 'Visit Summary', noCache: true },
      },
    ],
  },
  {
    path: '/appointments',
    component: Layout,
    redirect: '/appointments/appointments',
    alwaysShow: true,
    meta: {
      title: 'Appointments',
      icon: 'appointments',
      affix: false,
    },
    children: [
      {
        path: 'appointments',
        component: () => import('@/views/appointments/index'),
        name: 'Appointments',
        meta: { title: 'Appointments', icon: 'appointments', noCache: false, affix: false },
      },
      {
        path: 'report',
        component: () => import('@/views/appointments/report'),
        name: 'AppointmentReport',
        meta: { title: 'Reports', icon: 'chart', affix: false },
      },
      {
        path: 'form/:id',
        component: () => import('@/views/appointments/form'),
        name: 'Form',
        hidden: true,
        meta: { title: 'Diagnosis', icon: 'theme', affix: false },
      },
    ],
  },
  {
    path: '/profile',
    component: Layout,
    redirect: '/profile/edit',
    hidden: true,
    children: [
      {
        path: 'edit',
        component: () => import('@/views/users/SelfProfile'),
        name: 'SelfProfile',
        meta: { title: 'userProfile', icon: 'user', noCache: true },
      },
    ],
  },
];

export const asyncRoutes = [
  permissionRoutes,
  adminRoutes,
  /* {
    path: '/forms',
    component: Layout,
    redirect: 'blankPrescription',
    name: 'forms',
    alwaysShow: true,
    meta: {
      title: 'Forms',
      icon: 'edit',
      roles: ['admin, doctor'],
    },
    children: [
      {
        path: 'blankPrescription',
        component: () => import('@/views/appointments/blankPrescription'),
        name: 'BlankPrescription',
        meta: { title: 'Blank Prescription', icon: 'form',},
      },
    ],
  }, */
  {
    path: '/template',
    component: Layout,
    redirect: '/template/prescription-diagnosis-templates',
    name: 'Template',
    alwaysShow: true,
    meta: {
      title: 'Template',
      icon: 'documentation',
      roles: ['admin', 'doctor'],
    },
    children: [
      {
        path: 'prescription-diagnosis-templates',
        component: () => import('@/views/templates/prescription-diagnosis/index'),
        name: 'PrescriptionDiagnosisTemplates',
        meta: {
          title: 'Prescription Templates by Diagnosis',
          icon: 'clipboard',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'prescription-diagnosis-templates/create',
        component: () => import('@/views/templates/prescription-diagnosis/form'),
        name: 'PrescriptionDiagnosisTemplateCreate',
        hidden: true,
        meta: {
          title: 'New Prescription Template',
          activeMenu: '/template/prescription-diagnosis-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'prescription-diagnosis-templates/:id/edit',
        component: () => import('@/views/templates/prescription-diagnosis/form'),
        name: 'PrescriptionDiagnosisTemplateEdit',
        hidden: true,
        meta: {
          title: 'Edit Prescription Template',
          activeMenu: '/template/prescription-diagnosis-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'diagnostic-templates',
        component: () => import('@/views/templates/diagnostics/index'),
        name: 'DiagnosticTemplates',
        meta: {
          title: 'Diagnostics',
          icon: 'clipboard',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'diagnostic-templates/create',
        component: () => import('@/views/templates/diagnostics/form'),
        name: 'DiagnosticTemplateCreate',
        hidden: true,
        meta: {
          title: 'New Diagnostic Template',
          activeMenu: '/template/diagnostic-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'diagnostic-templates/:id/view',
        component: () => import('@/views/templates/diagnostics/view'),
        name: 'DiagnosticTemplateView',
        hidden: true,
        meta: {
          title: 'View Diagnostic Template',
          activeMenu: '/template/diagnostic-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'diagnostic-templates/:id/edit',
        component: () => import('@/views/templates/diagnostics/form'),
        name: 'DiagnosticTemplateEdit',
        hidden: true,
        meta: {
          title: 'Edit Diagnostic Template',
          activeMenu: '/template/diagnostic-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'form-templates',
        component: () => import('@/views/templates/form-templates/index'),
        name: 'FormTemplates',
        meta: {
          title: 'Form Templates',
          icon: 'form',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'form-templates/create',
        component: () => import('@/views/templates/form-templates/form'),
        name: 'FormTemplateCreate',
        hidden: true,
        meta: {
          title: 'New Form Template',
          activeMenu: '/template/form-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'form-templates/:id/view',
        component: () => import('@/views/templates/form-templates/view'),
        name: 'FormTemplateView',
        hidden: true,
        meta: {
          title: 'View Form Template',
          activeMenu: '/template/form-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'form-templates/:id/preview',
        component: () => import('@/views/templates/form-templates/preview'),
        name: 'FormTemplatePreview',
        hidden: true,
        meta: {
          title: 'Preview Form Template',
          activeMenu: '/template/form-templates',
          roles: ['admin', 'doctor'],
        },
      },
      {
        path: 'form-templates/:id/edit',
        component: () => import('@/views/templates/form-templates/form'),
        name: 'FormTemplateEdit',
        hidden: true,
        meta: {
          title: 'Edit Form Template',
          activeMenu: '/template/form-templates',
          roles: ['admin', 'doctor'],
        },
      },
    ],
  },
  {
    path: '/services',
    component: Layout,
    redirect: 'services',
    children: [
      {
        path: 'services',
        component: () => import('@/views/services/index'),
        name: 'Services',
        meta: { title: 'Services', icon: 'service', noCache: false, roles: ['admin', 'doctor'], affix: true },
      },
    ],
  },
  {
    path: '/medicines',
    component: Layout,
    redirect: 'medicines',
    children: [
      {
        path: 'medicines',
        component: () => import('@/views/medicines/index'),
        name: 'medicines',
        meta: { title: 'Medicines', icon: 'capsule', noCache: false, roles: ['admin', 'doctor'], affix: true },
      },
    ],
  },
  {
    path: '/user-profile',
    component: Layout,
    redirect: 'user-profile',
    children: [
      {
        path: 'user-profile',
        component: () => import('@/views/profile/index'),
        name: 'MyProfile',
        meta: { title: 'My Profile', icon: 'role', noCache: false, roles: ['admin', 'doctor'], affix: true },
      },
    ],
  },
];

const createRouter = () => new Router({
  // mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  base: process.env.MIX_LARAVUE_PATH,
  routes: constantRoutes,
});

const router = createRouter();

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter();
  router.matcher = newRouter.matcher; // reset router
}

export default router;
