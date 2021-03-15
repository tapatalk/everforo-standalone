<template>
    <a-row
        type="flex"
        justify="center"
        align="top"
        class="edit-post"
    >
        <UserAvatar 
            :scale="1"
            :profileId="post.user.id"
            :online="post.user.id"
        />
        <div class="post-editor">
            <Editor
                :default-post="post"
                :set-focus="setEditorFocus"
                :submit-complete="submitComplete"
                v-on:on-submit="onSubmit"
            />
        </div>
    </a-row>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import Editor from '@/components/Editor.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import {PostInterface, ThreadInterface} from '@/helpers/Interfaces';

    @Component({
        components: {
            Editor,
            UserAvatar,
        },
    })
    export default class PostEdit extends Vue {
        /**
         * edit reply content
         */
        @Prop()
        public post!: PostInterface;
        @Prop()
        public setEditorFocus!: boolean;

        protected submitComplete: boolean = false;

        protected onSubmit(data: FormData): void {
            data.append('post_id', this.post.id + '');

            this.$store.dispatch('Post/edit', data)
                .then((data: { post: PostInterface, thread?: ThreadInterface } | any) => {
                    //code is 401 ,user be banned
                    if (data && data.response && data.response.data
                            && data.response.data.code == 401) {
                        this.$message.error(this.$t('ban_message') as string);
                    } else if (data && data.response && data.response.data
                            && data.response.data.code == 403) {
                        this.$message.error(this.$t('no_permission') as string);
                    } else {
                        this.onEditPost(data.post);
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
         * send edited post to thread tree
         */
        @Emit('edit-post')
        protected onEditPost(postData: PostInterface): PostInterface {
            return postData;
        }
    }
</script>
<style lang="scss" scoped>
    .edit-post {
        padding: var(--p6) 0 0;
        flex-wrap: unset;

        .post-editor {
            order: 1;
            flex: 1 1 auto;
            padding: 0 0 0 var(--p4);
        }
    }
</style>