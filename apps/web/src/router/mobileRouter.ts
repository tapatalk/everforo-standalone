import Vue from 'vue';
import VueRouter from 'vue-router';
import beforeEachHook from "@/router/beforeEachHook";

Vue.use(VueRouter);

function loadView(view: string) {
    return () => import(/* webpackChunkName: "view-[request]" */ `@/views/${view}.vue`);
}

function loadMobileView(view: string) {
    return () => import(/* webpackChunkName: "view-[request]" */ `@/views-mobile/${view}.vue`);
}

const routes = [
    
    {
        path: '/thread/:thread_slug/:sort?/:page?/:post_id?/:unsubscribe?/:user_id?',
        name: 'thread',
        component: loadView('Thread'),
    },
    {
        path: '/:sort?/:page?/:category_id?',
        name: 'group',
        component: loadMobileView('GroupMobile'),
    },
    {
        path: '/:type',
        name: 'groups',
        component: loadView('GroupMobile'),
    },
    {
        path: '/threadedit/:thread_id?',
        name: 'threadedit',
        component: loadMobileView('ThreadEditMobile'),
    },
    {
        path: '/eula',
        name: 'eula',
        component: loadView('EULA'),
    },
    {
        path: '/privacy',
        name: 'privacy',
        component: loadView('PrivacyPolicy'),
    },
    { // be compatible with app, remove in the future
        path: '/privacy-policy',
        redirect: {name: 'privacy'},
    },
    {
        path: '/term-of-use',
        name: 'terms',
        component: loadMobileView('TermUseMobile'),
    },
    {
        path: '/nsod',
        name: 'nsod',
        component: loadView('NSOD'),
    },
    {
        path: '*',
        name: '404',
        component: loadView('NotFound'),
    },
];

const mobileRouter = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes,
});

mobileRouter.beforeEach(beforeEachHook);

export default mobileRouter;
