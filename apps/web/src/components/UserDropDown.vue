<template>
    <div
        v-if="bearToken && !userId"
        class="box"
    >
        <UserAvatar 
            :scale="1"
            :profile-id="$store.state.User.id"
        />
        <Username
             v-if="!isMobile"
            :username="$t('default_username')"
            :primary="true"
            :profile-id="$store.state.User.id"
        />
    </div>
    <div
        v-else-if="userId"
        class='box'
    >
        <a-dropdown
            class='dropdown-trigger'
            :trigger="['click']"
        >
            <div
                class="ant-dropdown-link"
                v-on:click="e => e.preventDefault()"
            >
                <UserAvatar
                    :scale="1"
                />
                <Username
                    v-if="!isMobile"
                    :primary="true"
                />
                <a>
                    <Icons
                        type="xiala"
                    />
                </a>
            </div>
            <div
                slot="overlay"
                class="user-dropdown"
            >
                <ul>
                    <li v-if="false">
                        <span>
                            <Icons
                                type='coin'
                            />{{$t('my_coin')}}
                        </span>
                    </li>
                    <li>
                        <span class="icon-font">
                            <Icons
                                type='moon'
                            /><span class="icon-font-span">{{$t('dark_mode')}}</span>
                        </span>
                        <a-switch
                            size="small"
                            :checked="$store.getters['User/darkMode']"
                            v-on:change="onDarkMode" 
                        />
                    </li>
                    <li
                        class='show-profile'
                        v-on:click="showProfile"
                    >
                        <span class="icon-font">
                            <Icons
                                type='myprofile'
                            /><span class="icon-font-span">{{$t('my_profile')}}</span>
                        </span>
                    </li>
                    <li
                        class='sign-out'
                        v-on:click="signOut"
                    >
                        <span class="icon-font">
                            <Icons
                                type='signout'
                            /><span class="icon-font-span">{{$t('sign_out')}}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </a-dropdown>
        <component :is="loginComponent"></component>
    </div>
    <div
        v-else
        class="box"
    >
        <span
            class="login"
            v-on:click="showLoginModal"
        >{{$t('login')}}</span>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {IS_MOBILE, StorageLocal} from "@/helpers/Utils";
    import {Response} from '@/http/Response';
    import LoginModal from '@/components/LoginModal.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';

    @Component({
        components: {
            LoginModal,
            UserAvatar,
            Username,
        },
    })
    export default class UserDropDown extends Vue {

        protected loginComponent!: string;

        protected isMobile:boolean = IS_MOBILE;

        protected showUserDropdown: boolean = false;

        get bearToken() {
            return localStorage.getItem('bearer');
        }

        get userId() {
            return this.$store.state.User.id;
        }

        protected created(){
            this.loginComponent = 'LoginModal';
        }

        protected showLoginModal() {
            this.$store.commit('setShowLoginModal', true);
        }

        protected onDarkMode(flag: boolean) {

            const data = new FormData;

            data.append('dark_mode', flag ? '1' : '0');

            this.$store.dispatch('User/switchDarkMode', data)
            .then((response: Response) => {
                const data: {users_settings: {user_id: number, dark_mode: string}} = response.getData();

                if (data.users_settings && parseInt(data.users_settings.dark_mode)) {
                    
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

                this.$store.commit('User/setDarkMode', data.users_settings.dark_mode);
            })
        }

        protected showProfile(): void {
            if (this.userId) {
                setTimeout(() => {
                    this.$store.commit('Profile/showProfile', true);
                    this.$store.commit('Profile/setProfileId', this.userId);
                }, 500);
            }
        }

        protected signOut(): void {
            StorageLocal.removeItem('bearer');
            document.cookie = "dm=0;path=/";
            window.location.reload(true);
        }
    }
</script>
<style lang="scss" scoped>
    .box {
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        height: $logo-size;
        line-height: 23px;
        cursor: pointer;

        .name {
            margin-left: var(--p2);
            font-size: $font-size3;
        }

        .login {
            @include title_font;
            @include capitalize;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .ico {
            font-size: $font-size0;
            margin-left: var(--p2);
            color: var(--category-color);
        }
    }

    .user-dropdown {
        position: absolute;
        background-color: var(--navbar-bg);
        // top: $nav-bar-height - 10;
        right: 0;
        color: var(--font-color1);
        width: 250px;
        top:8px;
        padding: var(--p2) var(--p4);
        box-shadow: $box-shadow;

        ul {
            li {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                line-height: 40px;

                &.sign-out, &.show-profile {
                    cursor: pointer;
                }

                .ico {
                    margin-right: var(--p2);
                }
            }
        }
        .icon-font {
            color: var(--category-color);
            font-size: $font-size2;
            display: flex;
            align-items: center;

            .ico {
                font-size: $font-size4;
                color: var(--category-color)
            }
            .icon-font-span {
                margin-left: 4px;
                font-weight: 450;
            }
        }

    }
</style>