<template>
    <div 
        id="app"
    >
        <transition name="page" mode="out-in">
            <component v-bind:is="layout"></component>
        </transition>
    </div>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import VueCookies from 'vue-cookies';
    import Vue2TouchEvents from 'vue2-touch-events';
    import {
        AutoComplete,
        Avatar,
        Breadcrumb,
        Button,
        Card,
        Checkbox,
        Col,
        Dropdown,
        Empty,
        Icon,
        Input,
        Layout,
        List,
        Menu,
        message,
        Modal,
        notification,
        Radio,
        Row,
        Skeleton,
        Switch,
        Table,
        Tabs,
        Drawer,
    } from 'ant-design-vue';
    import {IS_MOBILE, LANGUAGE_LIST, StorageLocal} from "@/helpers/Utils";
    import {ResponseError} from '@/http/ResponseError';
    import MainLayout from '@/layouts/MainLayout.vue';
    import MobileLayout from '@/layouts/MobileLayout.vue';
    import VueMeta from 'vue-meta';

    Vue.use(AutoComplete);
    Vue.use(Avatar);
    Vue.use(Breadcrumb);
    Vue.use(Button);
    Vue.use(Card);
    Vue.use(Checkbox);
    Vue.use(Col);
    Vue.use(Drawer);
    Vue.use(Dropdown);
    Vue.use(Empty);
    Vue.use(Icon);
    Vue.use(Input);
    Vue.use(Layout);
    Vue.use(List);
    Vue.use(Menu);
    Vue.use(Modal);
    Vue.use(Radio);
    Vue.use(Row);
    Vue.use(Skeleton);
    Vue.use(Switch);
    Vue.use(Table);
    Vue.use(Tabs);
    Vue.use(Vue2TouchEvents);
    Vue.use(VueCookies);
    Vue.use(VueMeta, {keyName: 'metaInfo', refreshOnceOnNavigation: true});

    Vue.prototype.$message = message;
    Vue.prototype.$notification = notification;

    @Component({
        components: {
            MainLayout,
            MobileLayout,
        }
    })
    export default class App extends Vue {

        protected layout!: string;

        get isDarkMode(): boolean {
            return this.$store.getters['User/darkMode'];
        }

        // created() is a Lifecycle Hooks Called synchronously after the instance is created.
        // At this stage, the instance has finished processing the options which means the following have been set up:
        // data observation, computed properties, methods, watch/event callbacks.
        // However, the mounting phase has not been started, and the $el property will not be available yet.
        protected created(): void {
            // if (process.env.NODE_ENV === 'development'){
            //     const t = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xOTIuMTY4LjEuNDE6ODEwMVwvYXBpXC9xcl9jb2RlX3NjYW5fbG9jYWwiLCJpYXQiOjE1ODkyNDkzOTksImV4cCI6MTU5NDQzMzM5OSwibmJmIjoxNTg5MjQ5Mzk5LCJqdGkiOiJyZkV3ZHplRklITUJjQm9LIiwic3ViIjo0NiwicHJ2IjoiODdlMGFmMWVmOWZkMTU4MTJmZGVjOTcxNTNhMTRlMGIwNDc1NDZhYSJ9.Cw0oSbuKqMU8HKVg4z_3mi23_xylTlwe3dP2kcZBMpY";
            //     if (!localStorage.getItem('bearer')){
            //         localStorage.setItem('bearer', t);
            //     }
            // }

            if (!StorageLocal.getItem('bearer')) {

                if (this.$cookies.get('bearer')) {
                    StorageLocal.setItem('bearer', this.$cookies.get('bearer'));
                }
            }

            this.$message.config({
                duration: 3,
            });

            const uri = window.location.search.substring(1); 
            const params = new URLSearchParams(uri);
            // login for app -> mobile web browser
            if (params.get('bearer')) {
                StorageLocal.removeItem('bearer'); // this line probably unnecessary
                StorageLocal.setItem('bearer', params.get('bearer') as string);
            }

            if (StorageLocal.getItem('bearer')) {
                this.$store.dispatch('User/getMe')
                            .then(() => {
                                // if we updated version, force reload
                                if (this.$store.state.User.api_version){
                                    if (StorageLocal.getItem('api_version')){
                                        if (parseInt(StorageLocal.getItem('api_version') as string) != parseInt(this.$store.state.User.api_version)){
                                            StorageLocal.setItem('api_version', this.$store.state.User.api_version);
                                            window.location.reload(true);
                                        }
                                    } else {
                                        StorageLocal.setItem('api_version', this.$store.state.User.api_version);
                                    }
                                }

                                if (this.$store.state.User.settings) {

                                    if (this.$store.state.User.settings.language) {
                                        const language :any = this.$store.state.User.settings.language;
                                        this.$root.$i18n.locale = language;
                                        StorageLocal.setItem('language', language as string);
                                    }

                                    if (this.$store.state.User.settings.dark_mode) {
                                        this.$store.commit('User/setDarkMode', this.$store.state.User.settings.dark_mode);
                                    }
                                }
                            })
                            .catch((error: ResponseError) => {
                                StorageLocal.removeItem('bearer');
                                // this.$store.dispatch('User/refresh');
                            });
            } else {
                if (StorageLocal.getItem('language')) {
                    var language :any = StorageLocal.getItem('language');
                } else {
                    var language :any = ((navigator.languages && navigator.languages[0]) || navigator.language).replace('-', '_');
                    var flag = false;
                    for (let i = 0; i < LANGUAGE_LIST.length; i++) {
                        if (LANGUAGE_LIST[i].value === language) {
                            flag = true;
                        }
                    }
                    if (!flag) {
                        language = 'en';
                    }
                    StorageLocal.setItem('language', language as string);
                }
                this.$root.$i18n.locale = language;
            }

            if (IS_MOBILE) {
                this.layout = 'MobileLayout';
            } else {
                this.layout = 'MainLayout';
            }
        }

        @Watch('isDarkMode', {immediate: true})
        protected onDarkMode(val: boolean) {
            if (val) {

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
                document.body.style.setProperty('--theme-color', '#6F9FFF');
                document.body.style.setProperty('--high-color', '#6F9FFF');
                document.body.style.setProperty('--daiload-color', '#6F9FFF');
                document.body.style.setProperty('--box-shadow-color', '#00000026');
                document.body.style.setProperty('--border-color1', '#24272b');
                document.body.style.setProperty('--border-color2', '#24272b');
                document.body.style.setProperty('--border-color3', '#24272b');
                document.body.style.setProperty('--border-color4', '#8c97ad');
                document.body.style.setProperty('--border-color5', '#484D53');
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
                document.body.style.setProperty('--theme-color', '#3d72de');
                document.body.style.setProperty('--high-color', 'rgba(119,150,222,.1)');
                document.body.style.setProperty('--daiload-color', '#f8f8f8');
                document.body.style.setProperty('--box-shadow-color', 'rgba(100, 100, 100, 0.15)');
                // border separate the main layouts
                document.body.style.setProperty('--border-color1', '#f2f2f2');
                // border separate the contents, for now it's the same as var(--border-color1).
                // but you should use them differently, in case we need to change them in the future
                document.body.style.setProperty('--border-color2', '#f2f2f2');
                // ant vue design input border color
                document.body.style.setProperty('--border-color3', '#f2f2f2');
                document.body.style.setProperty('--border-color4', '#f2f2f2');
                document.body.style.setProperty('--border-color5', '#f2f2f2');
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
        }

    }
</script>
<style lang="scss">
    /* some global stuff */
    :root {
        --p1: 4px;
        --p1-5: 6px;
        --p2: 8px;
        --p3: 12px;
        --p4: 16px;
        --p5: 20px;
        --p6: 24px;
        --p7: 28px;
        --p8: 32px;
        --p9: 36px;
        --p10:40px;
        --p11:44px;
        --p12:48px;
        --fold-line-top: 70px;
        --nested-margin-left: 42px;
    }

    @media #{$mobile-device-break-point} {
        :root {
            --p1: 2px;
            --p1-5: 3px;
            --p2: 4px;
            --p3: 6px;
            --p4: 8px;
            --p5: 10px;
            --p6: 12px;
            --p7: 14px;
            --p8: 16px;
            --p9: 18px;
            --p10:20px;
            --p11:22px;
            --p12:24px;
            --fold-line-top: 50px;
            --nested-margin-left: 42px;
        }
    }

    html, body {
        margin: 0;
        padding: 0;
    }

    html {
        font-size: $root-size;
    }

    body {
        line-height: 1;
        font-size: $root-size;
        font-family: 'Google Sans', sans-serif, Roboto;
        background-color: var(--body-bg);
        color: var(--font-color1);
        // disable select for everything, enable it for post content and some text later
        // user-select: none;
        &.no-scroll {
            overflow: hidden;
        }
    }

    ul {
        list-style: none;
        margin-bottom: 0 !important;
        padding-inline-start: 0; /* override user agent */
    }

    a:focus {
        text-decoration: none;
    }

    .fl {
        float: left;
    }

    .fr {
        float: right;
    }

    .ant-layout {
        background-color: var(--body-bg);
    }

    /* override some ant vue design styles, which can not be alter by passing modifyVars to less loader */
    .ant-menu-inline, .ant-menu-vertical, .ant-menu-vertical-left {
        border-right-width: 0 !important;
    }

    .ant-modal-content {
        background-color: var(--body-bg);
    }
    .ant-dropdown-menu {
        background-color: var(--navbar-bg);
        box-shadow: $box-shadow;

    }
    .ant-dropdown-menu-item {
        color: var(--font-color2);
    }

    .ant-dropdown-menu-item-active {
        color: var(--theme-color);
        
    }
    .ant-dropdown-menu-item:hover {
        background-color: var(--hover-bg);
    }

    
    .ant-btn-primary {
        background-color: var(--theme-color);
        border-color: var(--theme-color);
    }

    .ant-breadcrumb > span:last-child a {
        color: var(--font-color1);
    }

    .ant-card-bordered, .ant-menu {
        border: 0;
    }

    .ant-card-body {
        padding: var(--p6);
    }

    .ant-card-meta-title {
        color: var(--font-color1);
        font-weight: $title-weight;
    }

    .ant-skeleton-content .ant-skeleton-title,
    .ant-skeleton-content .ant-skeleton-paragraph > li,
    .ant-skeleton.ant-skeleton-active .ant-skeleton-content .ant-skeleton-title, 
    .ant-skeleton.ant-skeleton-active .ant-skeleton-content .ant-skeleton-paragraph > li {
        background: linear-gradient(90deg, var(--skeleton-color1) 25%, var(--skeleton-color2) 37%, var(--skeleton-color1) 63%);
    }

    .ant-skeleton.ant-skeleton-active .ant-skeleton-avatar,
    .ant-skeleton-header .ant-skeleton-avatar {
        background:  var(--skeleton-color2);
    }

    .ant-menu-inline .ant-menu-submenu-title {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .ant-list-split .ant-list-item {
        padding: var(--p9) var(--p6);
        border: 0;
    }

    .ant-list-vertical .ant-list-item-content {
        margin-bottom: 0 !important;
    }

    .ant-list-item {
        position: relative;
    }

    .no-sub .ant-menu-submenu-arrow {
        display: none;
    }

    .ant-input {
        @include placeholder_font;
        opacity: 1;
        background-color: var(--navbar-bg);
        border-color: var(--border-color2);
        color: var(--font-color1);
    }

    // these 4 lines are not necessary
    // .ant-input::-webkit-input-placeholder { /* WebKit, Blink, Edge */    color:    var(--desc-color); }
    // .ant-input:-moz-placeholder { /* Mozilla Firefox 4 to 18 */   color:   var(--desc-color); }
    // .ant-input::-moz-placeholder { /* Mozilla Firefox 19+ */   color:    var(--desc-color); }
    // .ant-input:-ms-input-placeholder { /* Internet Explorer 10-11 */   color:    var(--desc-color); }

    .ant-checkbox-inner {
        background-color: var(--border-color2);
    }

    .ant-input-group-addon {
        background-color: var(--home-bg);
        border-color: var(--home-bg);
    }
    
    .ant-btn-disabled.ant-btn,
    .ant-btn-primary-disabled.ant-btn, 
    .ant-btn-primary.ant-btn.disabled, 
    .ant-btn-primary.ant-btn[disabled] {
        color: var(--btn-disabled);
        background-color: var(--btn-disabled-bg);
        border-color: var(--btn-disabled-border);
    }
    .ant-radio-wrapper {
        color: var(--font-color1);
    }

    .ant-input-lg {
        font-size: $font-size1;
    }
    
    .ant-checkbox-inner {
        border-color: #606878
    }

    .ant-layout-header {
        line-height: initial;
        box-shadow: 0 2px 4px 0 var(--box-shadow-color);
    }

    // self-defined global style
    .main-layout {
        max-width: 100%;
        margin: 0 auto;
    }

    .ant-tabs-bar {
        border-bottom-color: var(--border-color2);
        margin-bottom: 0;
    }

    //change table font-size
    .ant-table thead {
        font-size: $font-size2
    }

    //change tabs font-size and font weight
    .ant-tabs-nav-wrap {
        font-size: $font-size3;
        font-weight: 500;
        color: var(--font-color6);
        .ant-tabs-tab-active {
            font-weight: 500;
        }
        .ant-tabs-tab {
            padding-left: 0;
            padding-right: 0;
        }
    }
  
    .ant-modal-header {
        background-color: var(--body-bg);
        border-color: var(--border-color5);
        .ant-modal-title {
            color: var(--font-color1);
        }
            
    }

    .ant-table-thead > tr > th {
        background-color: var(--body-bg);
          color: var(--font-color1);
    }

    .ant-table-placeholder {
        background-color: var(--body-bg);
        color: var(--font-color1);
        border-color: var(--border-color5);
        border-top: 0;
    }

    .ant-empty-normal {
        color: var(--font-color1);
    }

    .ant-switch:not(.ant-switch-checked) {
        background-color: #cccccc;
    }

    .main-content {
        margin: 0 auto;
        position: relative;
        max-width: 100%;
    }

    .main-content,
    .left-sider {
        border-right: $border-width $border-style var(--border-color5);
    }

    svg:not(:root) {
        overflow: visible;
    }

    /* globally used elements start */
    .modal-close-btn {
        position: absolute;
        right: -28px;
        width: 18px;
        top: 2px;
        cursor: pointer;

        .ico {
            color: #ffffff !important;
        }

        &.mobile {
            right: 0px;
            top: -28px;
        }
    }

    .confirm-message {
        @include title_font;
        text-align: center;
        padding: var(--p6);
    }

    /* globally used elements end */

    @media (min-width: 1400px) {

        .main-layout {
            width: $total-width1;

            .main-content {
                width: $mid-width1;

                &.no-left {
                    width: $mid-width1 + $left-width1;
                }
            }

            &.wide {
                width: $total-width1 + $wide-diff;

                .main-content {
                    width: $mid-width1 + $wide-diff;
                }
            }
        }

        .left-sider {
            width: $left-width1 !important;
            max-width: $left-width1 !important;
            min-width: $left-width1 !important;
        }
    }

    @media (min-width: 1000px) and (max-width: 1400px) {
        .main-layout {
            width: $total-width2;

            .main-content {
                width: $mid-width2;

                &.no-left {
                    width: $mid-width2 + $left-width2;
                }
            }

            &.wide {
                width: $total-width2 + $wide-diff;

                .main-content {
                    width: $mid-width2 + $wide-diff;
                }
            }
        }

        .left-sider {
            width: $left-width2 !important;
            max-width: $left-width2 !important;
            min-width: $left-width2 !important;
        }
    }

    @media (max-width: 1000px) {

        .main-content {
            width: $mid-width2;
            flex: auto;

            &.no-left {
                width: $mid-width2 + $left-width2;
            }

            &.wide {
                width: $total-width2 + $wide-diff;

                .main-content {
                    width: $mid-width2 + $wide-diff;
                }
            }
        }

        .left-sider {
            display: none;
        }
    }

    //.height-unset-i{
    //    height: unset!important;
    //}
</style>