<template>
    <FullScreenModal
        v-on:close="onClose"
        :contentMaxWidth="isMobile? null:'660px'"
        :height="undefined"
        :top="undefined"
        :content-height="undefined"
    >
        <template v-slot:header>
            <div
                class="title"
            >
                <span>{{$t('manage_setting')}}</span>
                <a-button
                    v-if="($store.state.activatedGroupTab == 1 && infoCanSave) || ($store.state.activatedGroupTab == 4 && attachedFilesCanSave)"
                    class="save-btn can-save"
                    type="link"
                    ref="save"
                    v-on:click="onSubmit"
                >{{$t('save')}}
                </a-button>
                <a-button
                    v-else-if="($store.state.activatedGroupTab == 1 || $store.state.activatedGroupTab == 4)"
                    :disabled="true"
                    class="save-btn"
                    type="link"
                >{{$t('save')}}
                </a-button>
            </div>
        </template>
        <GroupTabs
            :default-key="defaultKey"
        >
            <template v-slot:tab1>
                <GroupForm
                    v-on:can-save="onInfoCanSave"
                    v-on:submit-status="onInfoSubmitStatus"
                    v-on:save="(data) => onInfoSave(data)"
                    :default-group="groupData"
                    :default-categories="categoryData"
                    :reset-button="resetButton"
                    :submit="infoSubmit"
                />
            </template>
            <template v-slot:tab2>
                <GroupExtension/>
            </template>
            <template v-slot:tab3>
                <GroupAirdrop/>
            </template>
        </GroupTabs>
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {CategoryInterface, GroupInterface} from "@/helpers/Interfaces";
    import {IS_MOBILE} from '@/helpers/Utils';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import GroupExtension from '@/components/GroupExtension.vue';
    import GroupForm from '@/components/GroupForm.vue';
    import GroupTabs from '@/components/GroupTabs.vue';
    import GroupAirdrop from '@/components/GroupAirdrop.vue';
    import GroupAttachedFiles from '@/components/GroupAttachedFiles.vue';

    @Component({
        components: {
            FullScreenModal,
            GroupExtension,
            GroupForm,
            GroupTabs,
            GroupAirdrop,
            GroupAttachedFiles,
        },
    })
    export default class GroupManage extends Vue {
        
        protected isMobile: boolean = IS_MOBILE;

        protected infoCanSave: boolean = false;
        protected infoSubmit: boolean = false;
        protected resetButton: boolean = false;
        

        @Prop()
        public defaultKey!: string;

        get groupData(): GroupInterface {
            return this.$store.state.Group;
        }

        protected created()
        {
            // console.log(this.defaultKey);
            this.$store.commit('setActivatedGroupTab', this.defaultKey);
        }

        get categoryData(): CategoryInterface[]{
            return this.$store.state.Category.categories;
        }

        get activatedGroupTab(): number {
            return this.$store.state.activatedGroupTab;
        }

        @Emit('close-create-group')
        protected onClose() {

        }

        protected onSubmit() {
            this.onInfoSubmit();
        }

        protected onInfoCanSave(flag: boolean) {
            this.infoCanSave = flag;
        }

        protected onInfoSubmit() {
            this.infoSubmit = true;
        }

        protected onInfoSubmitStatus() {
            this.infoSubmit = false;
        }

        /**
         *
         */
        protected async onInfoSave(data: FormData) {

            // this.saveBtnText = this.$t('saving') as string;

            this.infoCanSave = false;
            this.resetButton = false;

            try{

                const response = await this.$store.dispatch('Group/update', data);

                const saved = response.response.data;

                if(saved.code != '20000'){

                    if(response.getDescription()){
                        this.$message.error(response.getDescription() as string);
                    } else {
                        this.$message.info(this.$t('group_info_update_failed') as string);
                    }

                } else if (saved){

                    if (saved.data.group && saved.data.group.id) {
                        this.$store.commit('Group/setCurrentGroup', saved.data.group);
                        this.$store.commit('User/updateGroup', saved.data.group); // I don't know, maybe we can delete this line
                    }
                    // categories can be empty, eg. deleted all categories
                    if (saved.data.categories) {
                        this.$store.commit('Category/setCategories', saved.data.categories);
                    }

                    this.$message.success(this.$t('group_info_updated') as string);

                    this.onClose();
                }

            } catch (e){
                this.$message.error(this.$t('group_info_update_failed') as string);
            
            } finally {
                this.infoCanSave = true;
                this.resetButton = true;
                this.infoSubmit = false;
            }
        }


    }
</script>
<style lang="scss" scoped>
    // .settings {
    //     padding: var(--p4) 0 0;
    //     @include clear_after;

    //     .label {
    //         @include form_label;
    //     }

    //     .item {
    //         @include form_item;

    //         input {
    //             @include input;
    //         }

    //         &.categories {
    //             input {
    //                 width: auto;
    //             }
    //         }
    //     }

    //     .file-upload-control {
    //         display: flex;
    //         flex-direction: column;
    //         align-items: flex-start;
    //         justify-content: space-between;

    //         .description {
    //             @include description_font;
    //             padding: 0 0 0 var(--p6);
    //         }

    //         .ant-btn {
    //             @include capitalize;
    //         }

    //         .notice-message {
    //             @include notice_font;
    //             padding: 0 0 0 var(--p6);
    //         }
    //     }
    // }
    // div.content{
    //     max-width: 660px;
    // }
    .scroll-content {
        padding-bottom: var(--p6);
    }
    .mask {
        .content {
            .title {
                margin-bottom:  0;
                border-bottom: 0;
                font-size: $font-size3;
            }
        }
    }
</style>