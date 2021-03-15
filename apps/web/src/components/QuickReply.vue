<template>
    <a-row
        type="flex"
        justify="center"
        align="top"
        :class="['quick-reply', {'mobile': isMobile}]"
    >
        <UserAvatar
            v-if="!isMobile"
            :scale="2"
            :profileId="$store.state.User.id"
        />
        <div class="reply-editor">
            <Editor
                :clear-content="clearReply"
                :set-focus="setEditorFocus"
                :submit-complete="submitComplete"
                :placeholder="editorPlaceholder"
                v-on:on-submit="onSubmit"
                v-on:close-editor="onCloseEditor"
            />
        </div>
    </a-row>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {PostInterface} from '@/helpers/Interfaces';
    import {IS_MOBILE, isGroupFollowed} from '@/helpers/Utils';
    import Editor from '@/components/Editor.vue';
    import UserAvatar from '@/components/UserAvatar.vue';

    @Component({
        components: {
            Editor,
            UserAvatar,
        },
    })
    export default class QuickReply extends Vue {

        @Prop()
        public post!: PostInterface;
        @Prop()
        public isFirstPost!: boolean;
        @Prop()
        public clearReply!: boolean;
        @Prop()
        public setEditorFocus!: boolean;
        @Prop()
        public placeholder!: string;

        protected isMobile: boolean = IS_MOBILE;
        protected submitComplete: boolean = false;
        protected editorPlaceholder: string = '';

        protected created() {
            this.editorPlaceholder = this.placeholder ? this.placeholder : this.$t('share_thoughts') as string;
        }

        protected beforeDestroy() {
            delete this.post;
        }

        // life cycle hook for keep-alive components
        // protected deactivated(){
        //
        // }

        /**
         * on submit
         */
        protected onSubmit(data: FormData): void {

            data.append('thread_id', this.post.thread_id + '');

            if (!this.isFirstPost) {
                data.append('reply_id', this.post.id + '');
            }

            this.$store.dispatch('Post/submit', data)
                .then((data: PostInterface | any) => {
                    
                    if (data && data.id) {
                        data.is_new = true;
                        data.online = true;
                        //if user subscribe thread,change subscribe status
                        if (data.is_subscribe) {
                            this.$store.commit('Subscribe/setSubscribe', data.thread_id);
                        }
                        this.onNewPost(data);

                        if (!isGroupFollowed(this.$store.state.User.groups, this.$store.state.Group.id)) {
                            this.$store.dispatch('Group/follow')
                                .then((response: { success: number }) => {
                                    if (response.success) {
                                        this.$store.dispatch('User/getMe');
                                    }
                                });
                        }
                    } else {
                        if (data && data.response && data.response.data
                            && data.response.data.code == 503)
                        {
                            this.$message.info(this.$t('flood_check') as string);
                        }else if (data && data.response && data.response.data
                                && data.response.data.code == 401){
                            this.$message.error(this.$t('ban_message') as string);
                        }else if (data && data.response && data.response.data
                                && data.response.data.code == 40003){
                            this.$message.error(this.$t('join_error') as string);
                        }else if (data && data.response && data.response.data
                                && data.response.data.code == 403){
                            this.$message.error(this.$t('no_permission') as string);
                        } else {
                            this.$message.error(this.$t('network_error') as string);
                        }
                    }

                })
                .finally(() => {
                    this.submitComplete = true;
                    this.$nextTick(() => {
                        this.submitComplete = false;
                    });
                });
        }

        /**
         * add new post to thread tree
         */
        @Emit('new-post')
        protected onNewPost(postData: PostInterface): PostInterface {
            return postData;
        }

        @Emit('close-editor')
        protected onCloseEditor(): boolean {
            /**
             * close editor if it's a post reply editor, we never close a thread reply editor 
             */
            return !this.isFirstPost;
        }
    }
</script>
<style lang="scss" scoped>

    .quick-reply {
        padding: var(--p6) 0 0;
        flex-wrap: nowrap;

        .reply-editor {
            order: 1;
            flex: 1 1 auto;
            padding: 0 0 0 var(--p2);
            max-width: calc(100% - 40px);
        }

        &.mobile {
            .reply-editor {
                max-width: 100%;
            }
        }
    }
</style>