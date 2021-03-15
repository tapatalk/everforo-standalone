<template>
    <span>
        <div
            :class="['like-list', {'mobile':isMobile}]"
        >
            <div
                v-for="(profile, index) in likeList"
                :key="index"
                class="profile"
                :style= "{'z-index': likeList.length - index}"
                v-on:click="showLikeListModal"
            >
                <UserAvatar
                    :avatar="profile.photo_url" 
                    :username="profile.name"
                    :scale="1"
                    :disable-login-popup="true"
                    :is-ban="$store.getters['BanUser/isBan'](profile.user_id, profile.is_ban)"
                    :online="profile.online"
                />
            </div>
            <div
                v-if="desc"
                class="desc"
                v-on:click="showLikeListModal"
            >{{desc}}</div>
        </div>
    </span>
</template>
<script lang="ts">
    import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
    import {LikeInterface, ProfileInterface} from '@/helpers/Interfaces';
    import UserAvatar from '@/components/UserAvatar.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            UserAvatar,
        },
    })
    export default class LikePreview extends Vue {
        @Prop()
        public likes!: LikeInterface[];

        protected likeList: ProfileInterface[] = [];

        protected postId: number = 0;

        protected showDescThreshold = IS_MOBILE?2:5;

        protected isMobile: boolean = IS_MOBILE;

        protected desc: string = '';

        get userProfiles():ProfileInterface[]  {
            return this.$store.state.ProfileList.profiles;
        }

        @Watch('likes', {immediate: true})
        protected onLikes(): void {
            if (!this.likes.length) {
                this.likeList = [];
                return;
            }

            const user_id_list: number[] = [];

            for (let i = 0; i < Math.min(this.showDescThreshold, this.likes.length); i++) {

                if (!this.postId) {
                    this.postId = this.likes[i].post_id;
                }

                // if the profile is not fetched, add it to the list
                if (!this.userProfiles[this.likes[i].user_id]) {
                    user_id_list.push(this.likes[i].user_id);
                }
            }

            // if there is profile not fetched, send request
            if (user_id_list.length) {
                this.$store.dispatch('ProfileList/getProfileList', user_id_list)
                .then(() => {
                    this.generateList();
                });
            } else {
                this.generateList();
            }
        }

        protected generateList() {
            this.likeList = [];

            for (let i = 0; i < Math.min(this.showDescThreshold, this.likes.length); i++) {

                if (this.likes[i] && this.userProfiles[this.likes[i].user_id]) {
                    this.userProfiles[this.likes[i].user_id].is_ban = this.likes[i].is_ban;
                    this.likeList.push(this.userProfiles[this.likes[i].user_id]);
                }
            }

            if (this.likes.length > this.likeList.length) {
                this.desc = this.isMobile 
                            ? this.$tc('like_list_desc_mobile', this.likes.length - this.likeList.length) as string
                            : this.$tc('like_list_desc', this.likes.length - this.likeList.length) as string;
            }
        }

        protected showLikeListModal(): void{
            this.$store.commit('Like/setLikeListPostId', this.postId);
        }

    }
</script>
<style lang="scss" scoped>
    .like-list {
        $h: $avatar-size1;
        display: flex;
        flex-direction: row;
        height: $h;

        &.mobile {
            height:30px;

             .profile {
            width: 30px;
            height: 30px;
            margin-left: -6px;

                img {
                    width: 100%;
                    height: 100%;
                    border-radius: 50%;
                }
            }
        }

        .profile {
            width: $h;
            height: $h;
            margin-left: $avatar-size1 / -3;

            &:first-of-type {
                margin-left: 0;
            }
        }

        .desc {
            margin-left: -32px;
            padding-left: calc(var(--p4) + 24px);
            padding-right: var(--p4);
            line-height: $h;
            font-size: $font-size1;
            color: var(--font-color2);
            font-weight:500;
            background:var(--home-bg);
            border-radius:32px;
            border:1px solid var(--home-bg);
        }
    }
</style>
