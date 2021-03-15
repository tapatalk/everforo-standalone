<template>
<div v-if="notShowProfileFlag" class="avatar-div" :style="defaultStyle">
    <a-avatar
            v-if="isBan > 0"
            :class="['avatar', {'not-online': notOnline}]"
            slot="avatar"
            icon="user"
            :size="sizePx"
            :src="banAvatar"
            :alt="username"
    />
    <a-avatar
        v-else-if="avatar !== undefined"
        :class="['avatar', {'not-online': notOnline}]"
        slot="avatar"
        icon="user"
        :size="sizePx"
        :src="avatar"
        :alt="username"
    />
    <a-avatar
        v-else
        :class="['avatar', {'not-online': notOnline}]"
        slot="avatar"
        icon="user"
        :size="sizePx"
        :src="$store.state.User.photo_url"
        :alt="$store.state.User.name"
    />
    <OnlineButton
        v-if="online"
        :button-radius="buttonRadius"
        :button-size="buttonSize"
    />
</div>
<div v-else class="avatar-div" :style="defaultStyle">
    <a-avatar
            v-if="isBan > 0"
            :class="['avatar', {'not-online': notOnline}]"
            slot="avatar"
            icon="user"
            :size="sizePx"
            :src="banAvatar"
            :alt="username"
            v-on:click="showProfile"
    />
    <a-avatar
        v-else-if="avatar !== undefined"
        :class="['avatar', {'not-online': notOnline}]"
        slot="avatar"
        icon="user"
        :size="sizePx"
        :src="avatar"
        :alt="username"
        v-on:click="showProfile"
    />
    <a-avatar
        v-else
        :class="['avatar', {'not-online': notOnline}]"
        slot="avatar"
        icon="user"
        :size="sizePx"
        :src="$store.state.User.photo_url"
        :alt="$store.state.User.name"
        v-on:click="showProfile"
    />
    <OnlineButton
        v-if="online"
        :button-radius="buttonRadius"
        :button-size="buttonSize"
    />
</div>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import OnlineButton from '@/components/OnlineButton.vue';
    import {IS_MOBILE} from '@/helpers/Utils';

    @Component({
        components: {
            OnlineButton,
        }
    })
    export default class UserAvatar extends Vue {
        @Prop()
        public username!: string;
        @Prop()
        public avatar!: string;
        @Prop()
        public profileId!: number;
        @Prop()
        public scale!: number;
        @Prop()
        public disableLoginPopup!: boolean;
        @Prop()
        public isBan!: number;
        @Prop()
        public online!: number;
        @Prop()
        public notOnline!: boolean;
        @Prop()
        public notShowProfileFlag!: boolean;


        protected sizePx!: number;
        protected sizeEnum: Record<number, number> = {1: 30, 2: 40, 3: 50, 4: 60, 5: 80, 6: 100};
        protected banAvatar:string = '/img/ban.png';
        protected defaultStyle = {height: '30px', width: '30px'};
        protected flagSize: Record<number, number> = {30: 6, 40: 8, 50: 10, 60: 12, 80: 16, 100: 20};
        protected flagRadius: Record<number, number> = {30: 3, 40: 4, 50: 5, 60: 6, 80: 8, 100: 10};
        protected buttonSize = 10;
        protected buttonRadius = 5;

        protected created() {
            this.sizePx = this.sizeEnum[this.scale ? this.scale : 1];
            this.buttonSize = this.flagSize[this.sizePx];
            this.buttonRadius = this.flagRadius[this.sizePx];
            this.defaultStyle = {height: this.sizePx + 'px', width: this.sizePx + 'px'};
        }

        protected showProfile(e: Event){
            
            e.preventDefault();
            if (this.profileId){
                this.$store.commit('Profile/showProfile', true);
                this.$store.commit('Profile/setProfileId', this.profileId);
            } else if (!this.disableLoginPopup && !this.$store.state.User.id) {
                this.$store.commit('setShowLoginModal', true);
            }
        }
    }
</script>
<style lang="scss" scoped>
    .avatar-div {
        flex-shrink: 0;
        cursor: pointer;
    }
    .avatar-div {
        position: relative;
        display: inline-block;
    }
    .not-online {
        filter:grayscale(100%);
    }

</style>