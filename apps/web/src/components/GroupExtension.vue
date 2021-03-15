<template>
    <section>
        <div
            class="flags"
            v-for="(ext, index) in extensions"
            :key="index"
        >
            <div class="main-class">
                <div class="title">{{$t(ext.feature_name)}}</div>
                <div class="desc">{{$t(ext.feature_name + '_desc')}}</div>
            </div>
            <div>
                <a
                    class="switch-a"
                    v-if="$store.getters['GroupExtensions/getFeatureStatus'](ext.feature_name) && (ext.feature_name == 'adminsAndModerators' || ext.feature_name == 'GroupLevelPermission' || ext.feature_name == 'attached_files')"
                    v-on:click="extensionSetting(ext)"
                >
                    {{$t('setting')}}
                </a>
                <a-switch 
                    size="small"
                    :disabled="ext.feature_name == 'adminsAndModerators' && !isAdmin"
                    :checked="ext.status == '1'"
                    v-on:change="changeEnabled(ext)" 
                />  
                <!-- <a-button
                    v-if="ext.status"
                    type="link"
                    class="enabled-button"
                    v-on:click="onUnEnabled(ext)"
                >
                    {{$t('enabled')}}
                </a-button>
                <a-button
                    v-else
                    type="primary"
                    v-on:click="onEnableAirDrop(ext)"
                >
                    {{$t('enable')}}
                </a-button> -->
            </div>
        </div>
        <AdminSetting
                v-if="showAdminSetting"
                v-on:close-invite-member="showAdminSetting = false"
        />
        <GroupLevelSetting
                v-if="showGroupLevel"
                v-on:close-invite-member="showGroupLevel = false"
        />
        <GroupAttachedFiles
                v-if="showAttachedFiles"
                v-on:close-popup="showAttachedFiles = false"
        />
    </section>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {GroupFeatureInterface, ERC20TokenInterface} from '@/helpers/Interfaces';
    import AdminSetting from '@/components/AdminSetting.vue';
    import GroupLevelSetting from '@/components/GroupLevelSetting.vue';
    import GroupAttachedFiles from '@/components/GroupAttachedFiles.vue';

    @Component({
        components: {
            AdminSetting,
            GroupLevelSetting,
            GroupAttachedFiles,
        },
    })
    export default class GroupExtension extends Vue {

        protected showAdminSetting: boolean = false;
        protected showGroupLevel: boolean = false;
        protected showAttachedFiles: boolean = false;

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin(): string {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus);
        }

        get extensions(): GroupFeatureInterface[] {
            return this.$store.state.GroupExtensions.features;
        }

        protected changeEnabled(ext: GroupFeatureInterface)
        {   
            let status = '1';
            if (ext.status == '1') {
                status = '0';
            }
            const data = new FormData;
            data.append('feature_id', ext.id + '');
            data.append('status', status);
            this.$store.dispatch('GroupExtensions/enableERC20Token', data)
                .then((response: Response) => {
                    const data: {feature: {feature_id: number, status: string}} = response.getData();
                    if (data && data.feature) {
                        this.$store.commit('GroupExtensions/setFeatureStatus', {status: data.feature.status, id: ext.id});
                    } else if (response && response.response && response.response.data
                            && response.response.data.code == 403) {
                        this.$message.error(this.$t('no_permission') as string);
                    }
                });
        }

        protected extensionSetting(ext: GroupFeatureInterface) {
            switch (ext.id) {
                case 4:
                    this.showAdminSetting = true;
                    break;
                case 5:
                    this.showGroupLevel = true;
                    break;
                case 6:
                    this.showAttachedFiles = true;
                    break;
            }
        }

        // get activatedGroupTab(): number {
        //     return this.$store.state.activatedGroupTab;
        // }

        // @Watch('activatedGroupTab', {immediate: true})
        // protected onTabChange(val: number) {

        //     this.$store.dispatch('GroupExtensions/fetchExtensionList')
        //     .then((response: Response) => {
        //         const data: {features: GroupFeatureInterface[]} = response.getData();
                
        //         if (data && data.features && data.features.length) {

        //             this.extensions = [];

        //             for (let i in data.features) {
        //                 this.extensions.push(data.features[i]);
        //             }

        //         }
        //     });

        // }

        // protected onEnableAirDrop(ext: GroupFeatureInterface) {

        //     const data = new FormData;

        //     data.append('feature_id', ext.id + '');
        //     data.append('status', '1');

        //     this.$store.dispatch('GroupExtensions/enableERC20Token', data)
        //         .then((response: Response) => {

        //             const data: {feature: {feature_id: number, status: string}} = response.getData();
        //             if (data && data.feature) {
        //                 // to make getter and setter work
        //                 // if (!data.token.name) {
        //                 //     data.token.name = '';
        //                 // }

        //                 // if (!data.token.symbol) {
        //                 //     data.token.symbol = '';
        //                 // }

        //                 // if (!data.token.balance) {
        //                 //     data.token.balance = '';
        //                 // }

        //                 // if (!data.token.decimal) {
        //                 //     data.token.decimal = '';
        //                 // }

        //                 // this.$store.commit('Group/setToken', data.token);

        //                 //this.$store.commit('GroupExtensions/setAirdropStatus', data.feature.status);
        //                 this.$store.commit('GroupExtensions/setFeatureStatus', {status: data.feature.status, id: ext.id});
        //             }
        //         });
        // }
        // //Since the original logic is not deleted, airdrop will not be processed here
        // protected onUnEnabled(ext: GroupFeatureInterface) {
        //     if (ext.feature_name != 'airdrop') {
        //         const data = new FormData;
        //         data.append('feature_id', ext.id + '');
        //         data.append('status', '0');
        //         this.$store.dispatch('GroupExtensions/enableERC20Token', data)
        //             .then((response: Response) => {
        //                 const data: {feature: {feature_id: number, status: string}} = response.getData();
        //                 if (data && data.feature) {
        //                     this.$store.commit('GroupExtensions/setFeatureStatus', {status: data.feature.status, id: ext.id});
        //                 }
        //             });
        //     }
        // }
    }
</script>
<style lang="scss" scoped>
    .flags {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: var(--p6) 0;
        border-bottom: $border-width $border-style var(--border-color5);
        // &:first-of-type {
        //     padding-top: var(--p2);
        // }

        .title {
            @include secondary_title_font;
        }

        .desc {
            @include description_font;
            font-size: $upload-desc-font-size;
            line-height: 1.3rem;
        }

        .enabled-button {
            border: $border-width $border-style var(--font-color2);
            padding-left: 11px;
            padding-right: 11px;
            color: var(--font-color2);
        }

        .ant-btn {
            @include capitalize;
        }
    }
    .flags:last-child {
        border-bottom:0;
    }
    .main-class {
        width: 65%;
    }
    .switch-a {
        margin-right: var(--p2);
        font-size: 0.9rem;
    }
</style>
