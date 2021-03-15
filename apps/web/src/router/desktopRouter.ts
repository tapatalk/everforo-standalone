import Vue from 'vue';
import VueRouter from 'vue-router';
import beforeEachHook from "@/router/beforeEachHook";

Vue.use(VueRouter);

function loadView(view: string) {
    return () => import(/* webpackChunkName: "view-[request]" */ `@/views/${view}.vue`)
}

const routes = [
    {
        path: '/thread/:thread_slug/:sort?/:page?/:post_id?/:unsubscribe?/:user_id?',
        name: 'thread',
        component: loadView('Thread'),
    },
    {
        path: '/password/reset',
        name: 'passwordreset',
        component: loadView('Group'),
    },
    {
        path: '/email/register',
        name: 'register',
        component: loadView('Group'),
    },
    {
        path: '/:type',
        name: 'groups',
        component: loadView('Group'),
    },
    {
        path: '/:sort?/:page?/:category_id?',
        name: 'group',
        component: loadView('Group'),
    },

    // {
    //     path: '/g/:group_name/category',
    //     name: 'ghome',
    //     component: Group,
    //     alias: '/g/:group_name/all',
    // },
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
        path: '/eula',
        name: 'eula',
        component: loadView('EULA'),
    },
    {
        path: '/term-of-use',
        name: 'terms',
        component: loadView('TermUse'),
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

const desktopRouter = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes,
    // scrollBehavior (to) { // mimic the html anchor behavior, but it doesn't work well, since we have a fixed top navbar
    //     if (to.hash) {
    //         return {
    //             selector: to.hash,
    //             // offset: {x: 100, y: 100}
    //         }
    //     }
    // },
});

/**
 *  global before guards
 *  we can do auth and redirect in here
 */
desktopRouter.beforeEach(beforeEachHook);

export default desktopRouter;
