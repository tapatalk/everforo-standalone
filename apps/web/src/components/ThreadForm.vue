<template>
    <div
        v-if="isMobile"
        class="form-mobile"
    >
        <div class="title-box">
            <Back/>
            <span class="title">{{isEdit ? $t('edit_topic') : $t('post_new_topic')}}</span>
        </div>
        <a-row
            v-if="categories.length > 0"
            class="item"
        >
            <EFDropdown
                :source-data="categories"
                :value-key="'name'"
                :default-value="category"
                v-on:select-item="onChooseCategory"
            />
        </a-row>
        <a-row :class="['subject', {'mobile': isMobile}]">
            <a-input
                :placeholder="$t('subject')"
                size="large"
                id="subject_input"
                v-model.lazy="subject"
            />
        </a-row>
        <Editor
            v-on:on-submit="onSubmit"
            :submit-complete="submitComplete"
            :default-post="post ? post : null"
            :max-height="editorMaxHeight"
            :min-height="editorMinHeight"
            :mixed-content="true"
            :is-thread-editor="true"
        />
    </div>
    <div
        v-else
        class="form"
    >
        <div class="title">{{isEdit ? $t('edit_topic') : $t('post_new_topic')}}</div>
        <div 
            class="label first" 
            v-if="categories.length > 0 && isEdit == false"
        >{{$t('post_to')}}</div>
        <a-row
            v-if="categories.length > 0"
            class="item"
        >
            <EFDropdown
                :source-data="categories"
                :value-key="'name'"
                :default-value="category"
                v-on:select-item="onChooseCategory"
            />
        </a-row>
        <div class="label">{{$t('subject')}}</div>
        <a-row class="item">
            <a-input
                :placeholder="$t('subject')"
                size="large"
                id="subject_input"
                v-model.lazy="subject"
            />
        </a-row>
        <div class="label">{{$t('content')}}</div>
        <Editor
            v-on:on-submit="onSubmit"
            :submit-complete="submitComplete"
            :default-post="post ? post : null"
            :max-height="editorMaxHeight"
            :min-height="editorMinHeight"
            :mixed-content="true"
            :is-thread-editor="true"
        />
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {IS_MOBILE, isGroupFollowed} from "@/helpers/Utils";
    import {CategoryInterface, PostInterface, ThreadInterface} from "@/helpers/Interfaces";
    import Back from "@/components/Back.vue";
    import EFDropdown from '@/components/EFDropdown.vue';
    import Editor from '@/components/Editor.vue';

    @Component({
        components: {
            Back,
            EFDropdown,
            Editor,
        }
    })
    export default class ThreadForm extends Vue {
        @Prop()
        public post!: PostInterface;
        @Prop()
        public threadTitle!: string;
        @Prop()
        public categoryId!: number;
        @Prop()
        public editorMaxHeight!: number;
        @Prop()
        public editorMinHeight!: number;
        @Prop()
        public formOpen!: boolean;

        protected isEdit: boolean = false;

        protected category: { id: number, name: string } = {id: 0, name: this.$t('choose_category') as string};

        protected subject: string = '';

        protected isMobile: boolean = IS_MOBILE;

        protected submitComplete: boolean = false;

        get categories(): CategoryInterface[] {

            const data: CategoryInterface[] = [];

            if (this.$store.state.Category.categories) {
                for (let i = 0; i < this.$store.state.Category.categories.length; i++) {
                    if (this.$store.state.Category.categories[i].id > 0) {
                        data.push(this.$store.state.Category.categories[i]);
                    }
                }
            }

            return data;
        }

        protected created() {
            if (this.threadTitle) {
                this.subject = this.threadTitle;
            }
            if (this.post) {
                this.isEdit = true;
            }
        }

        @Watch('formOpen', {immediate: true})
        protected onFormOpen(val: boolean){
            if (val) {
                if (!this.subject){
                    const subjectInput = document.getElementById('subject_input');

                    subjectInput && subjectInput.focus();
                }
            }
        }

        @Watch('$route', {immediate: true})
        protected onRouteUpdate(): void {
            if (this.categoryId || (this.$route.params && this.$route.params.category_id)) {
                if (this.categories.length) {
                    const id = this.categoryId ? this.categoryId : parseInt(this.$route.params.category_id);
                    for (let i = 0; i < this.categories.length; i++) {
                        if (id === this.categories[i].category_id) {
                            this.category.id = this.categories[i].category_id;
                            this.category.name = this.categories[i].name;
                        }
                    }
                }

            } else {
                this.category.id = 0;
                this.category.name = this.$t('choose_category') as string;
            }
        }

        protected onChooseCategory(category: CategoryInterface) {
            this.category.id = category.category_id;
            this.category.name = category.name;
        }

        protected onSubmit(data: FormData): void {

            if (!this.subject){
                this.$message.error(this.$t('subject_empty') as string);
                this.submitComplete = true;
                return;
            }

            if (this.subject.length < 3 || this.subject.length > 190) {
                this.$message.error(this.$t('subject_max_length', {min: 3, max: 190}) as string);
                this.submitComplete = true;
                return;
            }

            data.append('title', this.subject);

            if (this.categories.length && !this.category.id) {
                this.$message.info(this.$t('please_choose_category') as string);
                this.submitComplete = true;
                return;
            }

            if (this.category.id) {
                data.append('category_index_id', this.category.id + '');
                var categorys = this.$store.state.Category.categories;
                for (let i = 0; i < categorys.length; i++) {
                    if (categorys[i].category_id == this.category.id && categorys[i].new_topics) {
                        categorys[i].new_topics = 0;
                    }
                }
                this.$store.commit('Category/setCategories', categorys);
            }

            if (this.post) {
                data.append('thread_id', this.post.thread_id + '');
                data.append('post_id', this.post.id + '');
                // edit thread content
                this.$store.dispatch('Post/edit', data)
                    .then((data: { post: PostInterface, thread: ThreadInterface } | any) => {
                        if (data && data.response && data.response.data
                                && data.response.data.code == 401) {
                            this.$message.error(this.$t('ban_message') as string);
                        } else if (data && data.response && data.response.data
                                && data.response.data.code == 403) {
                            this.$message.error(this.$t('no_permission') as string);
                        } else {
                            this.onEditPost(data.post);

                            if (data.thread &&
                                    this.threadTitle !== data.thread.title) {
                                this.onEditThreadTitle(data.thread.title);
                            }

                            if (data.thread && data.thread.category_index_id &&
                                    this.categoryId !== data.thread.category_index_id) {
                                this.onEditThreadCategory(parseInt(data.thread.category_index_id as unknown as string));
                            }
                        }
                    })
                    .finally(() => {
                        this.submitComplete = true;
                        this.$nextTick(() => {
                            this.submitComplete = false;
                        });
                    });

            } else {
                // create a new topic
                this.$store.dispatch('Thread/submit', data)
                    .then((data: ThreadInterface | any) => {
                        if (data && data.id) {
                            // clear stored thread list
                            this.$store.commit('ThreadList/setThreadList', []);
                            this.$store.commit('ThreadList/setPreviousSort', '');
                            // add group to followed group list
                            this.$store.commit('User/addGroup', this.$store.state.Group);

                            this.submitComplete = true;

                            const groupName = this.$store.getters.getGroupName as string;
                            this.$router.push({
                                name: 'thread',
                                params: {group_name: groupName, thread_slug: data.slug, thread_id: data.id}
                            } as unknown as RawLocation);
                        } else {
                            if (data && data.response && data.response.data 
                                && data.response.data.code == 503) 
                            {
                                this.$message.info(this.$t('flood_check') as string);
                            }else if (data && data.response && data.response.data
                                    && data.response.data.code == 401) {
                                this.$message.error(this.$t('ban_message') as string);
                            }else if (data && data.response && data.response.data
                                && data.response.data.code == 40003){
                                this.$message.error(this.$t('join_error') as string);
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
            // why is this poece of code here? it might be redundant
            // if (!isGroupFollowed(this.$store.state.User.groups, this.$store.state.Group.id)) {
            //     this.$store.dispatch('Group/follow')
            //         .then((response: { success: number }) => {
            //             if (response.success) {
            //                 this.$store.dispatch('User/getMe');
            //             }
            //     });
            // }
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
    .form {

        .title {
            @include form_title;
        }

        .category-holder {
            width: 100%;
            line-height: $avatar-size2;
            text-indent: var(--p6);
            @include input;
        }

        .label {
            @include form_label;
        }

        .item {
            @include form_item;
        }
    }

    .form-mobile {
        .title-box {

            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            padding-bottom: var(--p6);

            .back {
                margin-right: var(--p6);
            }

            .title {
                @include title_font;
            }
        }

        .ant-input {
            border: 0;
        }

        .subject {
            border: $border-width $border-style var(--border-color1);
            border-radius: 8px;
            margin: var(--p8) 0;
        }

        .category-holder {
            width: 100%;
            line-height: $avatar-size2;
            text-indent: var(--p6);
            @include input;
        }
    }
</style>
