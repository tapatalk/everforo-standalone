<template>
    <FullScreenModal
        v-on:close="onClose"
        v-on:full-screen="onFullScreen"
        v-on:full-screen-exit="onFullScreenExit"
        :height="popupHeight"
    >
        <ThreadForm
            :post="post"
            :thread-title="threadTitle"
            :category-id="categoryId"
            :editor-max-height="editorMaxHeight"
            :editor-min-height="editorMinHeight"
            :form-open="formOpen"
            v-on:edit-post="onEditPost"
            v-on:edit-thread-title="onEditThreadTitle"
            v-on:edit-thread-category="onEditThreadCategory"
        />
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {MODAL_POPUP_HEIGHT_RATIO} from '@/helpers/Utils';
    import {CategoryInterface, PostInterface,} from "@/helpers/Interfaces";
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import ThreadForm from "@/components/ThreadForm.vue";
    import {windowHeight} from "@/helpers/Utils";

    @Component({
        components: {
            FullScreenModal,
            ThreadForm,
        }
    })
    export default class ThreadEdit extends Vue {
        @Prop()
        public post!: PostInterface;
        @Prop()
        public threadTitle!: string;
        @Prop()
        public categoryId!: number;
        @Prop()
        public formOpen!: boolean;

        protected remainSpace: number = 0;
        protected editorMaxHeight: number = 0;
        protected editorMinHeight: number = 100;
        protected popupHeight:number = 0;
        // 460 include the form padding, title, some others inputs.
        // also add 100 to make it safe, not exceed viewport
        protected otherDeduct: number = 440;
        protected categoryHeight: number = 100;

        get categories(): CategoryInterface[] {
            return this.$store.state.Category.categories;
        }

        @Watch('categories', {immediate: true, deep: true}) 
        protected onCategoryChange(){

            this.popupHeight = Math.floor(windowHeight() * MODAL_POPUP_HEIGHT_RATIO);

            if( this.popupHeight > 950 ){
                this.popupHeight = 950
            }

            if (this.categories && this.categories.length) {

                this.editorMinHeight = this.popupHeight - this.otherDeduct;
                this.editorMaxHeight = this.popupHeight - this.otherDeduct;
            } else {
                
                this.editorMinHeight = this.popupHeight - this.otherDeduct + this.categoryHeight;
                this.editorMaxHeight = this.popupHeight - this.otherDeduct + this.categoryHeight;
            }
        }

        protected onFullScreen() {
            this.remainSpace = windowHeight() - this.otherDeduct;

            this.editorMinHeight = this.remainSpace;
            this.editorMaxHeight = this.remainSpace;
        }

        protected onFullScreenExit() {
            this.editorMinHeight = this.popupHeight - this.otherDeduct;
            this.editorMaxHeight = this.popupHeight - this.otherDeduct;
        }

        @Emit('close')
        protected onClose(): void {

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
