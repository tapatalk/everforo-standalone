import Vue from 'vue';
import store from "@/store";
import {Route} from "vue-router";
import VueAmplitude from '@/helpers/VueAmplitude';
import VueAnalytics from 'vue-analytics';
import {isInApp} from "@/helpers/Utils";

Vue.use(VueAmplitude, {apiKey: '19fd4965e4e045e16b9d9574cb36c65e', userId : 0 , config: {includeReferrer: true, includeUtm: true}});
Vue.use(VueAnalytics, {id: 'UA-172351397-1'});

export default function beforeEachHook(to:Route, from:Route, next:any) {
     
    const match = to.fullPath.match(/^\/g\/([\w\d\-]+)\/?$/)
    // group url without any extra path, redirect to /g/group_name/all
    if (match && match[1]) {
        next({
            name: 'group',
            params: {group_name:  match[1]},
            replace: true,
        });

        return;
    }

    let proceed = true;

    if ((process.env.VUE_APP_DOMAIN === 'https://stage.everforo.com' || process.env.VUE_APP_DOMAIN === 'https://dev.everforo.com')
        && !localStorage.getItem('stagePassport'))
    {

        if (isInApp()) {
            // no need such guard for app
        } else {
            proceed = false;
            const answer = window.prompt('what\'s the password?');
            if (btoa(btoa(btoa(answer + ''))) === 'VFZSSmVrNUVVWHBOYWtVOQ==') {
                proceed = true;
                localStorage.setItem('stagePassport', 'everforo');
            }
        }
    }

    //amp track and ga track
    if(process.env.NODE_ENV === 'production' && from.fullPath != to.fullPath ){
        Vue.prototype.$amplitude.logEvent(to.fullPath);
        Vue.prototype.$ga.page(to.fullPath);
    }

    // dark mode
    const dark_cookie = document.cookie.match('(^|;) ?dm=([^;]*)(;|$)');

    const darkModeDisabled: string[] = ['privacy', 'eula', 'terms', 'homehome'];
    
    if (localStorage.getItem('bearer') && dark_cookie && dark_cookie[2] === '1' && darkModeDisabled.indexOf(to.name as string) === -1) {

        document.body.style.setProperty('--body-bg', '#1a1d20');
        document.body.style.setProperty('--navbar-bg', '#24272b');
        document.body.style.setProperty('--dropdown-bg', '#191B1D');
        document.body.style.setProperty('--input-bg', '#24272b');
        document.body.style.setProperty('--hover-bg', '#0a0b0c');
        document.body.style.setProperty('--home-bg', '#242526');
        document.body.style.setProperty('--group-bg', '#24272b');
        document.body.style.setProperty('--active-bg', '#232527');
        document.body.style.setProperty('--font-color1', '#DDDADA');
        document.body.style.setProperty('--font-color2', '#8C97AD');
        document.body.style.setProperty('--font-color3', '#DDDADA');
        document.body.style.setProperty('--font-color6', '#8c97ad');
        document.body.style.setProperty('--font-color7', '#FFF');
        document.body.style.setProperty('--desc-color', '#606878');
        document.body.style.setProperty('--not-online-color', '#606878');
        document.body.style.setProperty('--not-online-name-color', '#DDDADA');
        document.body.style.setProperty('--theme-color', '#6F9FFF');
        document.body.style.setProperty('--theme-backgroud-color', '#272F49');
        document.body.style.setProperty('--high-color', '#6F9FFF');
        document.body.style.setProperty('--daiload-color', '#6F9FFF');
        document.body.style.setProperty('--box-shadow-color', '#00000026');
        document.body.style.setProperty('--avatar-shadow-color', '#8c97ad');
        document.body.style.setProperty('--border-color1', '#24272b');
        document.body.style.setProperty('--border-color2', '#24272b');
        document.body.style.setProperty('--border-color3', '#24272b');
        document.body.style.setProperty('--border-color4', '#8c97ad');
        document.body.style.setProperty('--border-color5', '#484D53');
        document.body.style.setProperty('--border-color6', '#484D53');
        document.body.style.setProperty('--category-color', '#dddada');
        document.body.style.setProperty('--btn-disabled-bg', 'transparent');
        document.body.style.setProperty('--btn-disabled', '#8c8c8c');
        document.body.style.setProperty('--btn-disabled-border', '#8c8c8c');
        document.body.style.setProperty('--skeleton-color1', '#24272b');
        document.body.style.setProperty('--skeleton-color2', '#484D53');
        document.body.style.setProperty('--not-online-color', '#606878');
        document.body.style.setProperty('--not-online-name-color', '#DDDADA');
        document.body.style.setProperty('--profile-upper-bg', '#242526');
    } else {

        document.body.style.setProperty('--body-bg', '#ffffff');
        document.body.style.setProperty('--navbar-bg', '#ffffff');
        document.body.style.setProperty('--dropdown-bg', '#ffffff');
        document.body.style.setProperty('--input-bg', '#f8f8f8');
        document.body.style.setProperty('--hover-bg', '#fafafe');
        document.body.style.setProperty('--home-bg', '#fafafe');
        document.body.style.setProperty('--group-bg', '#fafafe');
        document.body.style.setProperty('--active-bg', '#EAF3FF');
        document.body.style.setProperty('--font-color1', '#333333');
        document.body.style.setProperty('--font-color2', '#606878');
        document.body.style.setProperty('--font-color3', '#8C97AD');
        document.body.style.setProperty('--font-color6', '#606878');
        document.body.style.setProperty('--font-color7', '#3d72de');
        document.body.style.setProperty('--desc-color', '#8C97AD');
        document.body.style.setProperty('--not-online-color', '#CCCCCC');
        document.body.style.setProperty('--not-online-name-color', '#CCCCCC');
        document.body.style.setProperty('--theme-color', '#3d72de');
        document.body.style.setProperty('--theme-backgroud-color', '#EAF3FF');
        document.body.style.setProperty('--high-color', 'rgba(119,150,222,.1)');
        document.body.style.setProperty('--daiload-color', '#f8f8f8');
        document.body.style.setProperty('--box-shadow-color', 'rgba(100, 100, 100, 0.15)');
        document.body.style.setProperty('--avatar-shadow-color', 'rgba(19, 19, 19, 0.2)');
        // border separate the main layouts
        document.body.style.setProperty('--border-color1', '#f2f2f2');
        // border separate the contents, for now it's the same as var(--border-color1).
        // but you should use them differently, in case we need to change them in the future
        document.body.style.setProperty('--border-color2', '#f2f2f2');
        // ant vue design input border color
        document.body.style.setProperty('--border-color3', '#f2f2f2');
        document.body.style.setProperty('--border-color4', '#f2f2f2');
        document.body.style.setProperty('--border-color5', '#f2f2f2');
        document.body.style.setProperty('--border-color6', '#606878');
        document.body.style.setProperty('--category-color', '#606878');
        document.body.style.setProperty('--btn-disabled-bg', '#f5f5f5');
        document.body.style.setProperty('--btn-disabled', 'rgba(0, 0, 0, 0.25)');
        document.body.style.setProperty('--btn-disabled-border', '#f2f2f2');
        document.body.style.setProperty('--skeleton-color1', '#f2f2f2');
        document.body.style.setProperty('--skeleton-color2', '#e6e6e6');
        document.body.style.setProperty('--not-online-color', '#CCCCCC');
        document.body.style.setProperty('--not-online-name-color', '#CCCCCC');
        document.body.style.setProperty('--profile-upper-bg', '#fafafe');
    }

    // if we are going to a  group info not fetched, we do it before go to this page
    if (to.name != '404') {

        store.commit('setShowProgressLine', true);

        // set group name
        store.commit('setGroupName', 'everforo-test');
        // clear some group spicified data
        store.commit('BanUser/clearBanList');
        
        store.dispatch('Group/load').then((response: any) => {
            store.commit('GroupExtensions/clearFeature');
            if (response && response.feature && response.feature.length) {
                for (let i in response.feature) {
                    store.commit('GroupExtensions/setFeature', response.feature[i]);
                }
            }

            // we also need category list.
            store.dispatch('Category/load').then(() => {
                store.commit('setShowProgressLine', false);
                next(proceed);
            }).catch(() => {
                store.commit('setErrorMessage', 'Fetching category list failed');
            });
        }).catch(() => {
            store.commit('setErrorMessage', 'The group you are trying to visit doesn\'t exist.');

            store.commit('setShowProgressLine', false);
            next({name: '404'});
        });
       
    } else {
        // move on to the next hook in the pipeline. If no hooks are left, the navigation is confirmed.
        // Make sure that the next function is called exactly once in any given pass through the navigation guard.
        // otherwise the hook will never be resolved or produce errors.
        next(proceed);
    }
}