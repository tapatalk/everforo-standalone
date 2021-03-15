<template>
    <section> 
        <a-layout-header 
            :class="['nav-bar', {'mobile': isMobile}]"
        >
            <div class="fl">
                <Logo
                    :no-name="isMobile ? true : false"
                />
            </div>
            <div
                class="fr"
            >
                <Notifications
                    v-if="$store.state.User.id"
                />
                <UserDropDown/>
            </div>
        </a-layout-header>
        <LoginModal/>
    </section>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';
    import GroupSwitch from '@/components/GroupSwitch.vue';
    import Logo from '@/components/Logo.vue';
    import Notifications from '@/components/Notifications.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import UserDropDown from '@/components/UserDropDown.vue';
    import LoginModal from '@/components/LoginModal.vue';

    @Component({
        components: {
            Logo,
            Notifications,
            UserAvatar,
            UserDropDown,
            GroupSwitch,
            LoginModal,
        },
    })
    export default class GroupNavBar extends Vue {
        protected isMobile: boolean = IS_MOBILE;

    }
</script>
<style lang="scss" scoped>
    section{
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
            user-select: none;

            .fl, .fr {
                display: flex;
                flex-direction: row;
                height: 100%;
                justify-content: center;
                align-items: center;
            }

            &.mobile {
                padding: 0 var(--p6);
                display: flex;
                flex-direction: row;
                justify-content: space-between;

                .fl {
                    order: 1;
                    // max-width: 100%;
                    // margin-left: 16px;
                    // margin-right: 16px;
                    // padding-left: ;
                }

                .fr {
                    order: 2;
                    // flex-basis: 30px;
                    flex-shrink: 0;
                    margin-left: var(--p6);
                }
            }
        }
    }

    .notification-list {
        background-color: #ffffff;
        box-shadow: $box-shadow-popup;
    }

    @media (min-width: 1600px) {
        section .nav-bar {
            padding: 0 12%;
        }
    }
</style>
<style lang="scss">
    .mobile a section {
        padding-bottom: 0;
    }
</style>