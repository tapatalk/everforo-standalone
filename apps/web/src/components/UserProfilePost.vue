<template>
    <div
        class='profile-post'
        v-on:click="goToPost"
    >
        <UserAvatar
            :avatar="post.user_avatar"
            :username="post.username"
            :profileId="post.user_id"
            :scale="2"
            :is-ban="$store.getters['BanUser/isBan'](post.user_id, post.is_ban)"
        />
        <div class="post-content">
            <div>
                <div 
                    v-html="activityString"
                >
                </div>
                <!-- <img
                    v-if="post.like_username"
                    src="/img/like.png"
                />
                <img
                    v-else
                    src="/img/reply.png"
                /> -->
            </div>
            <TimeString
                :time="post.created_at"
            />
        </div>
    </div>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {ProfilePostInterface} from '@/helpers/Interfaces';
    import {RawLocation} from "vue-router";
    import TimeString from '@/components/TimeString.vue';
    import UserAvatar from '@/components/UserAvatar.vue';

    @Component({
        components: {
            TimeString,
            UserAvatar,
        },
    })
    export default class UserProfilePost extends Vue {

        @Prop()
        public post!: ProfilePostInterface;

        get activityString() {

            if (this.post.like_username) {
                if (this.post.user_id == this.post.thread_poster_id) {
                    return `<strong>${this.post.like_username}</strong> liked <strong>${this.post.thread_poster_name}</strong>'s topic <strong>${this.post.thread_title}</strong>`;
                } else {
                    return `<strong>${this.post.like_username}</strong> liked <strong>${this.post.username}</strong>'s comment in <strong>${this.post.thread_title}</strong>`;
                }
            } else {
                if (this.post.user_id != this.post.thread_poster_id) {
                    if (this.post.parent_id == -1) {
                        return `<strong>${this.post.username}</strong> replied to <strong>${this.post.thread_poster_name}</strong>'s topic <strong>${this.post.thread_title}</strong>`;
                    } else {
                        return `<strong>${this.post.username}</strong> replied to <strong>${this.post.parent_poster_name}</strong>'s comment in <strong>${this.post.thread_title}</strong>`;
                    }
                } else {
                    return `<strong>${this.post.username}</strong> replied to topic <strong>${this.post.thread_title}</strong>`;
                }
            }

            return '';
        }

        protected goToPost() {
            try {
                this.$router.push({
                    name: 'thread', 
                    params: {group_name: this.post.group_name, thread_slug: this.post.thread_id, sort: 'all', page: 1, post_id: this.post.id}
                } as unknown as RawLocation);
            } catch(e) {
                console.info(e, this.post);
            }
        }
        
    }
</script>
<style lang="scss" scoped>
    .profile-post {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: flex-start;
        padding: var(--p3) var(--p4) 0 0;
        cursor: pointer;

        &:hover {
            background-color: var(--hover-bg);
        }

        .avatar-div {
            display: block;
            flex-shrink: 0;
            margin-right: var(--p4);
        }

        .post-content {
            flex-grow: 1;
            color: var(--font-color1);
            word-break: break-word;
            line-height: 1.2;
            padding-bottom: var(--p3);
            border-bottom: $border-width $border-style var(--border-color2);

            & > div {
                margin-bottom: var(--p2);
                display: flex;
                justify-content: flex-start;
                align-items: center;

                img {
                    width: 1em;
                    height: 1em;
                    margin-left: var(--p2); 
                }
            }

            p {
                margin-bottom: 0;
            }

            .time-string {
                font-size: $font-size0;
            }
        }
    }
</style>
