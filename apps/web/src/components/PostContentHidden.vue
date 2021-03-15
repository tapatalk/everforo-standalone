<template>
    <div
        v-if="post.deleted == 1 || post.deleted == 3"
        class="deleted"
    >
        <div class="deleted-wrapper">
            <Icons type="shanchu" />
            <span>
                {{$t('post_deleted', {author: post.deleted_by ? post.deleted_by.name : (post.user ? post.user.name : '') })}}
            </span>
            <span v-if="post.deleted == 3 && isAdmin">
                ,<a v-on:click="undeletePost">{{$t('undelete')}}</a>
            </span>
        </div>
    </div>
    <div
            v-else-if="$store.getters['BanUser/isBan'](post.user_id, post.is_ban)"
            class="deleted"
    >
        <div class="deleted-wrapper">
            <Icons type="ban" />
            <span>{{$t('is_ban_content')}}</span>
        </div>
    </div>
    <div
        v-else-if="$store.getters['User/isBlocked'](post.user_id)"
        class="deleted"
    >
        <div class="deleted-wrapper">
            <Icons type="block" />
            <span>{{$t('post_blocked_long')}}</span>
        </div>
    </div>
    <div
        v-else-if="post.nsfw == 1"
        class="deleted"
    >
        <div class="deleted-wrapper">
            <Icons type="biyan" />
            {{$t('nsfw_cotent')}}
            <span
                class="view-nsfw"
                v-on:click="showNSFW(post, $event)"
            >{{$t('view')}}</span>
        </div>
    </div>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {PostInterface} from '@/helpers/Interfaces';
    import {RawLocation} from "vue-router";

    @Component({
        components: {

        },
    })
    export default class PostContentHidden extends Vue {
        @Prop()
        protected post!: PostInterface;

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin():boolean {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus)
                    || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus);
        }

        protected showNSFW(post: PostInterface, event: MouseEvent) {
            event.preventDefault();
            if (this.$store.state.User.id == 0){
                this.$store.commit('setShowLoginModal', true);
            } else {
                post.nsfw = 0;
            }
        }

        protected undeletePost()
        {
            if (!this.post || !this.post.id) {
                return;
            }

            const data = new FormData();

            data.append('post_id', this.post.id + '');

            this.$store.dispatch('Post/undelete', data)
                    .then((postData: PostInterface | any) => {
                        location.reload();
                    });
        }
        
    }
</script>
<style lang="scss" scoped>
    // deleted post, blocked post and nsfw post
    .deleted {
        @include deleted_font;
        @include capitalize();
        // padding: var(--p1) var(--p6) 0 0;
        display: inline-block;

        .ico {
            margin-right: var(--p3);
        }

        .deleted-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: var(--p3);
            border: $border-width $border-style var(--border-color2);
            border-radius: $border-radius1;
            margin-top: var(--p2);
        }

        .view-nsfw {
            @include capitalize;
            margin-left: var(--p2);
            color: var(--theme-color);
            cursor: pointer;
        }
    }
</style>
