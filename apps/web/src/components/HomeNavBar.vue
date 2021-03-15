<template>
    <section>
        <a-layout-header 
            v-if="isMobile"
            class="mobile-nav-bar"
        >
            <div class="left">
                <GroupSwitch 
                    :showDefault="true"
                />
            </div>
            <div :class="['right', 'mobile']">
                <div class="drawer-btn" 
                    v-on:click="drawerVisible = true"
                >
                    <Icons
                        type="drawer"
                    />
                </div>
                <Notifications v-if="$store.state.User.id" />
                <div class="avatar-box">
                    <UserDropDown/>
                </div>
                <a-drawer
                    placement="right"
                    :closable="false"
                    @close="drawerVisible = false"
                    :visible="drawerVisible"
                    :bodyStyle="{padding: 0}"
                    >
                    <p v-on:click="drawerVisible = false">
                        <router-link :to="{name: 'homegroups',}">{{$t('what_happen')}}</router-link>
                    </p>
                    <p v-on:click="drawerVisible = false">
                        <router-link :to="{name: 'homehome',}">{{$t('why_everforo')}}</router-link>
                    </p>
                    <p v-if="$store.state.User.id == 0">
                        <a 
                            href="#"
                            v-on:click="loginClicked"
                        >{{$t('login')}}</a>
                    </p>
                </a-drawer>
            </div>
            <div
                class="box"
            >
                <LoginModal/>
            </div>
        </a-layout-header>


        <a-layout-header 
            v-else
            class="nav-bar"
        >
            <div class="fl">
                <Logo
                    :no-name="false"
                />
            </div>
            <div 
                class="home-tabs"    
            >
                <div
                    class="inner"
                    v-if="activeTab"
                >
                    <div :class="['tab', {'active': activeTab === 'homegroups' || activeTab === 'register' || activeTab === 'passwordreset'}]">
                        <router-link :to="{name: 'homegroups',}">{{$t('what_happen')}}</router-link>
                    </div>
                    <div :class="['tab', {'active': activeTab === 'home' || activeTab === 'homehome' || activeTab === 'hometech'}]">
                        <router-link :to="{name: 'homehome',}">{{$t('why_everforo')}}</router-link>
                    </div>
                </div>
            </div>
            <div
                class="fr"
            >
                <Notifications v-if="$store.state.User.id"/>
                <UserDropDown/>
            </div>
            <LoginModal
                v-if="!$store.state.User.id"
            />
        </a-layout-header>

    </section>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';
    import Logo from '@/components/Logo.vue';
    import Notifications from '@/components/Notifications.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import UserDropDown from '@/components/UserDropDown.vue';
    import LoginModal from '@/components/LoginModal.vue';
    import GroupNavBar from '@/components/GroupNavBar.vue';
    import GroupSwitch from '@/components/GroupSwitch.vue';

    @Component({
        components: {
            Logo,
            Notifications,
            UserAvatar,
            UserDropDown,
            LoginModal,
            GroupNavBar,
            GroupSwitch
        }
    })
    export default class HomeNavBar extends Vue {
        @Prop()
        public activeTab!:string;

        protected isMobile: boolean = IS_MOBILE;
        protected drawerVisible: boolean = false;

        protected loginClicked(e: Event) {
            e.preventDefault();
            this.drawerVisible = false;
            this.$store.commit('setShowLoginModal', true);
        }
    }
</script>
<style lang="scss" scoped>
    section {
        position: relative;
        width: 100%;
        height: $nav-bar-height;
        line-height: $nav-bar-height;
    
        .nav-bar {
            background-color: var(--navbar-bg);
            //box-shadow: $box-shadow;
            height: $nav-bar-height;
            z-index: $nav-z-index;
            position: fixed;
            width: 100%;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 100%;

            .home-tabs {
                width: auto;
                height: 100%;
                margin: 0 auto;

                .inner {
                    height: 100%;
                    display: flex;
                    flex-direction: row;
                    justify-content: center;
                    align-items: center;

                    .tab {
                        height: 100%;
                        line-height: $nav-bar-height;
                        position: relative;
                        padding: 0 var(--p5);
                        
                        a {
                            @include title_font();
                            @include capitalize();
                            text-decoration: none;
                            line-height: $nav-bar-height;
                            font-weight: 500;
                            color: var(--font-color6);
                            font-size: 18px;
                        }

                        &.active{
                            &::after {
                                $w: 30px;
                                content: "";
                                position: absolute;
                                bottom: 0;
                                left: 50%;
                                width: $w;
                                height: 2px;
                                margin: 0 0 0 $w / -2;
                                background-color: var(--theme-color);
                            }

                            a {
                                color: var(--theme-color);
                            }
                        }
                    }
                }
            }

            .fl, .fr {
                display: flex;
                flex-direction: row;
                height: 100%;
                justify-content: center;
                align-items: center;
            }
        }

        .mobile-nav-bar {
            background-color: var(--body-bg);
            box-shadow: $box-shadow;
            height: $nav-bar-height;
            z-index: $nav-z-index;
            position: fixed;
            width: 100%;
            padding-left: 0px;
            padding-right: 0px;
            // padding: 12px 16px;

            .left {
                float: left;
                padding-left: 8px;
            }

            .right {
                float: right;
                padding: 0 16px;
                height: 100%;

                .drawer-btn {
                    float: right;
                    padding-left: var(--p4);
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;                

                    .ico {
                        font-size: $font-size4;
                    }
                }

                .avatar-box {
                    float: right;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center; 
                }

                .notification-box {
                    display:inline-block;
                }
            }
        }
    }

    .ant-drawer-body {
        padding: 0;

        p {
            margin-bottom: 0;
        }

        a {
            font-size: 16px;
            line-height: 20px;
            color: #606878;
            padding: 15px 24px;
            border-bottom: 1px solid #F3F3F3;
            display: block;
            text-transform: capitalize;
        }
    }

    @media (min-width: 1600px) {
        section .nav-bar {
            padding: 0 12%;
        }
    }
</style>