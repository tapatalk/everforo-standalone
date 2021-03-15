<template>
    <a-layout>
        <GroupNavBar />
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
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {NAV_BAR_HEIGHT, bindEvent, removeEvent, windowHeight} from "@/helpers/Utils";
    import UserProfile from '@/components/UserProfile.vue';
    import GroupNavBar from '@/components/GroupNavBar.vue';
    import Messenger from '@/components/Messenger.vue';
    import ProgressLine from '@/components/ProgressLine.vue';

    @Component({
        components: {
            UserProfile,
            GroupNavBar,
            Messenger,
            ProgressLine,
        },
    })
    export default class MainLayout extends Vue {

        get showProgress(): boolean {
            return this.$store.state.showProgressLine;
        }

        get showProfile(): boolean {
            return this.$store.state.Profile.show;
        }

        set showProfile(flag: boolean) {
            this.$store.commit('Profile/showProfile', flag);
        }

        protected mounted() {
            bindEvent(window, 'scroll', this.scroll, {passive: true});
        }

        protected beforeDestroy() {
            removeEvent(window, 'scroll', this.scroll, {passive: true});
        }

        protected scroll(){

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