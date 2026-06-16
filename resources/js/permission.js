import router from './router';
import store from './store';
import { Message } from 'element-ui';
import NProgress from 'nprogress'; // progress bar
import 'nprogress/nprogress.css'; // progress bar style
import { isLogged, setLogged } from '@/utils/auth';
import getPageTitle from '@/utils/get-page-title';

NProgress.configure({ showSpinner: false }); // NProgress Configuration

const whiteList = ['/login', '/auth-redirect']; // no redirect whitelist
const MIN_LOADING_MS = 250;
let loadingStartedAt = 0;
let sessionRestoreAttempted = false;

async function tryRestoreSession() {
  if (sessionRestoreAttempted || isLogged()) {
    return isLogged();
  }
  sessionRestoreAttempted = true;
  try {
    await store.dispatch('user/getInfo');
    setLogged('1');
    return true;
  } catch (e) {
    return false;
  }
}

function startPageLoading() {
  loadingStartedAt = Date.now();
  store.commit('app/SET_PAGE_LOADING', true);
  NProgress.start();
}

function finishPageLoading() {
  const elapsed = Date.now() - loadingStartedAt;
  const delay = Math.max(0, MIN_LOADING_MS - elapsed);
  setTimeout(() => {
    store.commit('app/SET_PAGE_LOADING', false);
    NProgress.done();
  }, delay);
}

router.beforeEach(async(to, from, next) => {
  startPageLoading();
  // set page title
  document.title = getPageTitle(to.meta.title);

  // determine whether the user has logged in (cookie or active Laravel session)
  let isUserLogged = isLogged();
  const isWhitelisted = whiteList.indexOf(to.matched[0] ? to.matched[0].path : '') !== -1;
  if (!isUserLogged && !isWhitelisted) {
    isUserLogged = await tryRestoreSession();
  }

  if (isUserLogged) {
    if (to.path === '/login') {
      // if is logged in, redirect to the home page
      next({ path: '/' });
      finishPageLoading();
    } else {
      // determine whether the user has obtained his permission roles through getInfo
      const hasRoles = store.getters.roles && store.getters.roles.length > 0;
      if (hasRoles) {
        next();
      } else {
        try {
          // get user info
          // note: roles must be a object array! such as: ['admin'] or ,['manager','editor']
          const { roles, permissions } = await store.dispatch('user/getInfo');

          // generate accessible routes map based on roles
          const accessRoutes = await store.dispatch('permission/generateRoutes', { roles, permissions });
          router.addRoutes(accessRoutes);
          next({ ...to, replace: true });
        } catch (error) {
          // remove token and go to login page to re-login
          await store.dispatch('user/resetToken');
          Message.error(error.message || 'Has Error');
          next(`/login?redirect=${to.path}`);
          finishPageLoading();
        }
      }
    }
  } else {
    /* has no token*/

    if (whiteList.indexOf(to.matched[0] ? to.matched[0].path : '') !== -1) {
      // in the free login whitelist, go directly
      next();
    } else {
      // other pages that do not have permission to access are redirected to the login page.
      next(`/login?redirect=${to.path}`);
      finishPageLoading();
    }
  }
});

router.afterEach(() => {
  finishPageLoading();
});

router.onError(() => {
  finishPageLoading();
});
