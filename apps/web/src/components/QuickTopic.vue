<template>
    <div
        class="quick-topic"
    >
        <EditorTrigger
            :trigger-func="onShowEditor"
            :placeholder="$t('post_new_topic')"
        />
        <keep-alive>
            <ThreadEdit
                v-if="!isMobile && showEditor"
                :form-open="showEditor"
                v-on:close="showEditor = false"
            />
        </keep-alive>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {IS_MOBILE} from "@/helpers/Utils";
    import EditorTrigger from '@/components/EditorTrigger.vue';
    import ThreadEdit from '@/components/ThreadEdit.vue';

    @Component({
        components: {
            EditorTrigger,
            ThreadEdit,
        },
    })
    export default class QuickTopic extends Vue {

        protected isMobile:boolean = IS_MOBILE;
        protected showEditor: boolean = false;

        get groupJoining(): number {
            if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission')) {
                return this.$store.state.Group.joining;
            } else {
                return 1;
            }
        }

        get groupVisibility(): number {
            if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission')) {
                return this.$store.state.Group.visibility;
            } else {
                return 1;
            }
        }

        protected onShowEditor(): void {
            if (this.groupJoining != 1 && !this.$store.getters['User/isFollow'](this.$store.state.Group.id) && !this.$store.getters['User/isSuperAdmin']()) {
                this.$message.error(this.$t('join_error') as string);
                return;
            }
            if (this.groupVisibility == 3 && !this.$store.getters['User/isFollow'](this.$store.state.Group.id) && !this.$store.getters['User/isSuperAdmin']()) {
                this.$message.error(this.$t('join_error') as string);
                return;
            }
            if (IS_MOBILE){

                this.$router.push({
                    name: 'threadedit',
                    params: {group_name: this.$store.getters.getGroupName},
                } as unknown as RawLocation);

            }else{
                this.showEditor = true;
            }
        }
    }
</script>
<style lang="scss" scoped>

    .quick-topic {
        position: relative;
        min-height: $quick-topic-height;
        padding: var(--p3) var(--p6) var(--p6);
        border-bottom: $border-width $border-style var(--border-color5);

        // &:before {
        //     content: '';
        //     position: absolute;
        //     top: 0;
        //     left: 0;
        //     width: 100%;
        //     height: $quick-topic-height;
        //     background: linear-gradient(rgba(255, 255, 255, 1) 60%, rgba(255, 255, 255, .5) 90%, rgba(255, 255, 255, .0));
        //     z-index: -1;
        // }
    }
</style>