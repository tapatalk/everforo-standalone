<template>
    <section>
        <ThreadForm
            :post="post"
            :is-edit="isEdit"
            :thread-title="threadTitle"
            :category-id="categoryId"
            :editor-max-height="editorMaxHeight"
            :editor-min-height="editorMinHeight"
            v-on:edit-post="onEditPost"
            v-on:edit-thread-title="onEditThreadTitle"
            v-on:edit-thread-category="onEditThreadCategory"
        />
        <PageLoading :loading="loadingPost" />
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Watch, Prop, Vue} from 'vue-property-decorator';
    import {CategoryInterface, PostInterface} from '@/helpers/Interfaces';
    import ThreadForm from '@/components/ThreadForm.vue';
    import {windowHeight} from '@/helpers/Utils';
    import PageLoading from '@/components/PageLoading.vue';

    @Component({
        components: {
            ThreadForm,
            PageLoading,
        },
    })
    export default class ThreadEditMobile extends Vue {
        @Prop()
        public threadTitle!: string;
        @Prop()
        public categoryId!: number;

        protected post: PostInterface | null = null;

        protected editorMaxHeight: number = 0;

        protected editorMinHeight: number = 100;

        protected loadingPost: boolean = false;

        protected isEdit: boolean = false;

        get categories(): CategoryInterface[] {
            return this.$store.state.Category.categories;
        }

        @Watch('categories', {immediate: true, deep: true}) 
        protected onCategoryChange(){
            // 400 include the form padding, title, some others inputs.
            // also add 50 to make it safe, not exceed viewport
            const remainSpace: number = windowHeight() - 320;

            if (this.categories && this.categories.length) {

                this.editorMinHeight = remainSpace;
                this.editorMaxHeight = remainSpace;
            } else {
                const ch: number = 40;

                this.editorMinHeight = remainSpace + ch;
                this.editorMaxHeight = remainSpace + ch;
            }
        }

        @Watch('$route', {immediate: true})
        protected onRouteUpdate(): void {
            if (this.$route.params.post_id) {

                const postId: number = Number(this.$route.params.post_id);
                if (postId > 0) {
                    this.isEdit = true;
                    this.loadingPost = true;
                    this.$store.dispatch('Post/get', postId)
                        .then((postData: PostInterface | null) => {
                            this.loadingPost = false;
                            if (postData) {
                                this.post = postData;
                            } else {
                                this.$message.error(this.$t('network_error') as string);
                            }
                    }).catch((e) => {
                        this.$message.error(this.$t('network_error') as string);
                    });
                }
            }
        }

        /**
         * send edited post to thread tree
         */
        @Emit('edit-post')
        protected onEditPost(postData: PostInterface): PostInterface {
            return postData;
        }

        /**
         * send edited thread title to thread tree
         */
        @Emit('edit-thread-title')
        protected onEditThreadTitle(threadTitle: string): string {
            return threadTitle;
        }

        @Emit('edit-thread-category')
        protected onEditThreadCategory(category_index_id: number): number {
            return category_index_id;
        }
    }
</script>
<style lang="scss" scoped>
    section {
        padding: var(--p6);
    }
</style>
<style lang="scss">

</style>