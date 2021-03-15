<template>
    <a-layout
        v-touch:moving="onMoving"
    >
        <GroupNavBar v-if="inApp == false && ($route.params.group_name || $route.name == '404')"/>
        <transition name="fade">
            <router-view/>
        </transition>
        <UserProfile
            v-if="showProfile"
            v-on:close="showProfile = false"
        />
        <Messenger/>
        <ProgressLine v-if="showProgress"/>
    </a-layout>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import { isInApp } from '@/helpers/Utils';
    import Messenger from '@/components/Messenger.vue';
    import GroupNavBar from '@/components/GroupNavBar.vue';
    import ProgressLine from '@/components/ProgressLine.vue';
    import UserProfile from '@/components/UserProfile.vue';

    @Component({
        components: {
            Messenger,
            GroupNavBar,
            ProgressLine,
            UserProfile,
        },
    })
    export default class MobileLayout extends Vue {

        protected inApp: boolean = isInApp();

        get showProgress(): boolean {
            return this.$store.state.showProgressLine;
        }

        /**
         * disable for mobile for now
         */
        get showProfile(): boolean {
            return this.$store.state.Profile.show;
        }

        set showProfile(flag: boolean) {
            this.$store.commit('Profile/showProfile', flag);
        }

        // touch slide event
        protected onMoving(e: TouchEvent) {
            const pageHeight = document.documentElement.scrollHeight;
            const windowHeight = window.innerHeight;
            const scrollPosition = window.scrollY || window.pageYOffset 
            || document.body.scrollTop + (document.documentElement && document.documentElement.scrollTop || 0);

            if (pageHeight <= windowHeight+scrollPosition) {
                this.$store.commit('setScrollReachBottom', true);

                this.$nextTick(() => {
                    this.$store.commit('setScrollReachBottom', false);
                });
            }
        }

    }
</script>