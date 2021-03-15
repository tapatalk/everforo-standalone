<template>
    <div
        class='profile-list-item'
    >
        <UserAvatar
            :username="profile.name"
            :avatar="profile.photo_url"
            :profile-id="profile.id"
            :scale="2"
            :is-ban="isBan"
            :online="online"
        />
        <div
            v-if="content"
            :class="['name']"
            v-html="content"
        ></div>
        <slot 
            v-else
            name="content"
        ></slot>
    </div>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {ProfileInterface} from '@/helpers/Interfaces';
    import UserAvatar from '@/components/UserAvatar.vue';

    @Component({
        components: {
            UserAvatar,
        },
    })
    export default class ProfileListItem extends Vue {
        @Prop()
        public profile!: ProfileInterface;

        @Prop()
        public content!: string;

        @Prop()
        public isBan!: number;

        @Prop()
        public online!: number;

    }
</script>
<style lang="scss" scoped>
    .profile-list-item {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        margin-bottom: var(--p6);

        .avatar-div {
            margin-right: var(--p4);
        }

        .name {
            font-size: $font-size1;
            line-height: $font-size1-line-height;
        }
    }
</style>
