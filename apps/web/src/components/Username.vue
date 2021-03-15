<template>
    <span 
        :class="['name', {primary: primary}, {'mobile': isMobile}]"
        v-if="notShowProfileFlag"
    >
        <a 
            v-if="username"
        >{{username}}</a>
        <a 
            v-else
        >{{$store.state.User.name}}</a>
    </span>
    <span 
        :class="['name', {primary: primary}, {'mobile': isMobile}]"
        v-on:click="showProfile"
        v-else
    >
        <a 
            v-if="username"
        >{{username}}</a>
        <a 
            v-else
        >{{$store.state.User.name}}</a>
    </span>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';

    @Component
    export default class Username extends Vue {
        @Prop()
        public username!: string;
        @Prop()
        public profileId!: number;
        @Prop()
        public primary!: boolean;
        @Prop()
        public notShowProfileFlag!: boolean;

        protected isMobile: boolean = IS_MOBILE;

        protected showProfile(e: Event){
            e.preventDefault();
            if (this.profileId){
                this.$store.commit('Profile/showProfile', true);
                this.$store.commit('Profile/setProfileId', this.profileId);
            } else if(!this.$store.state.User.id) {
                this.$store.commit('setShowLoginModal', true);
            }
        }
    }
</script>
<style lang="scss" scoped>

    .name {
        @include username_font;

        a {
            color: inherit;
        }

        &.primary a {
            font-weight: $title-weight;
        }

        &.mobile {
            @include mobile_thread_username_font;
        }
    }
</style>