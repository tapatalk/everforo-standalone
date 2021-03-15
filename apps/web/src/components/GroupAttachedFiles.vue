<template>
    <FullScreenModal
        v-on:close="onCancel"
        :content-max-width="isMobile? null:'660px'"
        :content-height="'auto'"
        :max-height="'auto'"
        :top="'20%'"
    >
        <template v-slot:header>
            <div
                    :class="['title-icon', {'mobile-title-icon': isMobile}]"
            >
                <a-icon 
                v-on:click="onCancel"
                type="left"/>
            </div>
            <div
                v-if="title"
                class="title"
            >{{title}}</div>
        </template>
        <div
            v-on:click="onCancel"
            :class="['modal-close-btn']"
        >
            <Icons
                type="chacha"
            />
        </div>
        <div class="options">
            <div class="label">
                {{$t('who_attach_files')}}
            </div>
            <a-radio-group 
                name="radioGroup" 
                :default-value="1"
                :value="parseInt(groupAttachedFilesSetting.allow_everyone)"
                v-on:change="onAllowEveryoneChange"
                class="radio-style"
            >
                <a-radio class="redio-style" :value="1">{{$t('everyone')}}</a-radio>
                <a-radio class="redio-style" :value="0">{{$t('admins_only')}}</a-radio>
            </a-radio-group>
        </div>
        <div class="options last-row">
            <div class="label">
                {{$t('where_attach')}}
            </div>
            <a-radio-group 
                name="radioGroup" 
                class="radio-style"
                :default-value="1"
                :value="parseInt(groupAttachedFilesSetting.allow_post)"
                v-on:change="onAllowPostChange"
            >
                <a-radio class="redio-style" :value="1">{{$t('both_topics_replies')}}</a-radio>
                <a-radio class="redio-style" :value="0">{{$t('only_topic')}}</a-radio>
            </a-radio-group>
        </div>
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import { trim } from 'lodash';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            FullScreenModal,
        },
    })
    export default class GroupAttachedFiles extends Vue {

        protected title: string = this.$t('attached_files') as string;
        protected isMobile: boolean = IS_MOBILE;

        get groupAttachedFilesSetting(): any {
            return this.$store.state.Group.attached_files;
        }

        protected created(val: number) {

            this.$store.dispatch('Group/getAttachedFilesSetting')
            .then((response: Response) => {
                const data = response.getData();

                if (data) {
                    this.$store.commit('Group/setAttachFilesSetting', data);
                }
            });
        }

        @Emit('close-popup')
        protected onCancel() {

        }

        protected onAllowEveryoneChange(val: {target: {value: number}}) {
            this.$store.state.Group.attached_files.allow_everyone = val.target.value;
            this.saveSettings();
        }

        protected onAllowPostChange(val: {target: {value: number}}) {
            this.$store.state.Group.attached_files.allow_post = val.target.value;
            this.saveSettings();
        }

        protected saveSettings() {

            const data = new FormData;

            data.append('allow_everyone', this.$store.state.Group.attached_files.allow_everyone);
            data.append('allow_post', this.$store.state.Group.attached_files.allow_post);

            this.$store.dispatch('Group/updateAttachedFilesSetting', data)
            .then((response: any) => {
                if (response && response.response && response.response.data
                        && response.response.data.code == 403) {
                    this.$message.error(this.$t('no_permission') as string);
                } else {
                    this.$message.success(this.$t('group_info_updated') as string);
                }
            });
        }
    }
</script>
<style lang="scss" scoped>
    .options {
        .label {
            @include form_label;
            padding-top: var(--p6);
            padding-bottom: var(--p2);
            button {
                width: 100px;
                border-radius: 4px;
            }
        }
    }
    // .mask .content .title {
    //     text-align: center;
    //             padding: var(--p4) 0;
    //             margin: 0 var(--p8) 0 0;
    //     padding-left: 24px;
    // }
    // .title-icon {
    //     position: absolute;
    //     top: 40px;
    //     color: var(--font-color1);
    // }
    
    .redio-style {
        display: block;
        margin-top: 16px;
        margin-bottom: 0px;
        &:first-child {
            margin-top: 10px;
        }
    }
    .ant-radio-group {
        display: block;
    }
    .title-icon {
        position: absolute;
        top: 40px;
        color: var(--font-color1);
    }
    .mobile-title-icon {
        top: 28px;
    }
    .mask .content .title {
        // text-align: center;
        padding: var(--p4) 0;
        margin: 0 var(--p8) 0 0;
        padding-left: 24px;
    }
    .last-row {
        margin-bottom: 24px;
    }
</style>
