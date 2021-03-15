<template>
    <FullScreenModal
        v-on:close="onCancel"
        :content-height="'auto'"
        :max-height="'auto'"
        :top="'15%'"
        :contentMaxWidth="isMobile? null:'660px'"
    >
        <template 
            v-slot:header
        >
            <div
                :class="['title-icon', {'mobile-title-icon': isMobile}]"
            >
                <a-icon 
                v-on:click="onCancel"
                type="left"
            />
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

        <a-row>
            <div class="label">{{$t('admins')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <div
                class="desc"
            >
                {{$t('admins_desc')}}
            </div>
        </a-row>

        <a-row
            class="item categories"
        >
            <template>
                <span
                >
                    <a-input
                        size="large"
                        :class="['add-admin-input', {'mobile-add-admin-input': isMobile}]"
                        v-model="admin.name"
                        :disabled="true"
                    >
                        <div
                            slot="prefix"
                        >
                            <UserAvatar
                                :username="admin.name"
                                :avatar="admin.photo_url"
                                :profile-id="admin.user_id"
                                :not-show-profile-flag="true"
                                :scale="1"
                            />
                        </div>
                    </a-input>
                </span>
                <span
                    v-for="(mem, index) in adminList"
                    :key="index"
                >
                    <span
                        v-if="!!mem.user_id"
                    >
                        <a-input
                            size="large"
                            :class="['add-admin-input', {'mobile-add-admin-input': isMobile}]"
                            v-model="mem.name"
                            :disabled="true"
                        >
                            <div
                                slot="prefix"
                                v-if="!!mem.user_id"
                            >
                                <UserAvatar
                                    :username="mem.name"
                                    :avatar="mem.photo_url"
                                    :profile-id="mem.user_id"
                                    :not-show-profile-flag="true"
                                    :scale="1"
                                />
                            </div>
                            <div
                                class="delete-category"
                                slot="addonAfter"
                                v-if="adminStatus"
                                v-on:click="deleteAdmin(index, true)"
                            >
                                <Icons
                                    type="guanbi"
                                />
                            </div>
                        </a-input>
                    </span>
                <a-dropdown
                    v-if="!mem.user_id"
                    :trigger="['click']"
                >
                    <a-input
                        size="large"
                        :class="['add-admin-input', {'mobile-add-admin-input': isMobile}]"
                        v-on:change="onAdminChange"
                    >
                    </a-input>
                    <a-menu 
                        slot="overlay"
                        class="add-style"
                    >
                        <a-menu-item 
                            v-for="mem in selectAdminList"
                            :key="mem.user_id"
                            v-on:click="add(mem, true)"
                        >
                            <div
                                class="avatar-main-class avatar-select-block-class"
                            ></div>
                            <UserAvatar
                                :username="mem.name"
                                :avatar="mem.photo_url"
                                :profile-id="mem.user_id"
                                :not-show-profile-flag="true"
                                :scale="1"
                            />
                            <div
                                class="avatar-main-class avatar-select-block-class"
                            ></div>
                            <Username
                                :username="mem.name"
                                :profileId="mem.user_id"
                                :not-show-profile-flag="true"
                            />
                        </a-menu-item>
                        <a-menu-item
                                class="not-found-user"
                                v-if="!selectAdminList.length && !noAdmin"
                        >
                            {{adminTips}}
                        </a-menu-item>
                    </a-menu>

                </a-dropdown>
                </span>
            </template>
            <div
                class="add-category"
                v-on:click="addSearchInput(true)"
                v-if="getAddAdminStatus"
            >
                <div class="inner">
                    <Icons
                        type="tianjia"
                    />
                </div>
            </div>
        </a-row>

        <a-row>
            <div class="label">{{$t('moderators')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <div
                class="desc"
            >
                {{$t('moderators_desc')}}
            </div>
        </a-row>

        <a-row
            class="item categories"
        >
            <template>
                <span
                    v-for="(mem, index) in moderatorList"
                    :key="index"
                >
                    <span
                        v-if="!!mem.user_id"
                    >
                        <a-input
                            size="large"
                            :class="['add-admin-input', {'mobile-add-admin-input': isMobile}]"
                            v-model="mem.name"
                            :disabled="true"
                        >
                            <div
                                slot="prefix"
                                v-if="!!mem.user_id"
                            >
                                <UserAvatar
                                    :username="mem.name"
                                    :avatar="mem.photo_url"
                                    :profile-id="mem.user_id"
                                    :not-show-profile-flag="true"
                                    :scale="1"
                                />
                            </div>
                            <div
                                class="delete-category"
                                slot="addonAfter"
                                v-on:click="deleteAdmin(index, false)"
                            >
                                <Icons
                                    type="guanbi"
                                />
                            </div>
                        </a-input>
                    </span>
                <a-dropdown
                    v-if="!mem.user_id"
                    :trigger="['click']"
                >
                    <a-input
                        size="large"
                        :class="['add-admin-input', {'mobile-add-admin-input': isMobile}]"
                        v-on:change="onModeratorChange"
                    >
                    </a-input>
                    <a-menu 
                        slot="overlay"
                        class="add-style"
                    >
                        <a-menu-item 
                            v-for="mem in selectModeratorList"
                            :key="mem.user_id"
                            v-on:click="add(mem, false)"
                        >
                            <div
                                class="avatar-main-class avatar-select-block-class"
                            ></div>
                            <UserAvatar
                                :username="mem.name"
                                :avatar="mem.photo_url"
                                :profile-id="mem.user_id"
                                :not-show-profile-flag="true"
                                :scale="1"
                            />
                            <div
                                class="avatar-main-class avatar-select-block-class"
                            ></div>
                            <Username
                                :username="mem.name"
                                :profileId="mem.user_id"
                                :not-show-profile-flag="true"
                            />
                        </a-menu-item>
                        <a-menu-item
                                class="not-found-user"
                                v-if="!selectModeratorList.length && !noMoremoderator"
                        >
                            {{moderatorTips}}
                        </a-menu-item>
                    </a-menu>

                </a-dropdown>
                </span>
            </template>
            <div
                class="add-category"
                v-on:click="addSearchInput(false)"
                v-if="getAddModeratorStatus"
            >
                <div class="inner">
                    <Icons
                        type="tianjia"
                    />
                </div>
            </div>
        </a-row>

        <a-row>
            <div class="label">{{$t('group_transfer')}}</div>
        </a-row>

        <a-row
            class="item"
        >
            <div
                class="desc"
                v-if="!adminStatus"
            >
                {{$t('group_transfer_not_admin')}}
            </div>
            <div
                class="desc"
                v-else-if="getTransferStatus"
            >
                {{$t('group_transfer_not_admin_list')}}
            </div>
            <div
                class="desc"
                v-else
            >
                {{$t('group_transfer_desc')}}
            </div>
        </a-row>

        <a-row
            class="transfer-main"
        >
                <span
                    v-if="!!changeAdminInfo"
                >
                    <a-input
                        size="large"
                        :class="['transfer-admin-input', {'mobile-transfer-admin-input': isMobile}]"
                        v-model="changeAdminInfo.name"
                        :disabled="true"
                    >
                        <div
                            slot="prefix"
                            v-if="!!changeAdminInfo.user_id"
                        >
                            <UserAvatar
                                :username="changeAdminInfo.name"
                                :avatar="changeAdminInfo.photo_url"
                                :profile-id="changeAdminInfo.user_id"
                                :not-show-profile-flag="true"
                                :scale="1"
                            />
                        </div>
                        <div
                            class="delete-category"
                            slot="addonAfter"
                            v-on:click="deleteTransfer"
                        >
                            <Icons
                                type="guanbi"
                            />
                        </div>
                    </a-input>
                </span>
                <a-dropdown
                    :trigger="['click']"
                    :disabled="getTransferStatus"
                    v-else
                >
                    <a-input
                        size="large"
                        :class="['transfer-admin-input', {'mobile-transfer-admin-input': isMobile}]"
                        v-on:change="onTransferChange"
                    >
                    </a-input>
                    <a-menu 
                        slot="overlay"
                        class="add-style"
                    >
                        <a-menu-item 
                            v-for="mem in selectTransferList"
                            :key="mem.user_id"
                            v-on:click="changeAdmin(mem,false)"
                        >
                            <div
                                class="avatar-main-class avatar-select-block-class"
                            ></div>
                            <UserAvatar
                                :username="mem.name"
                                :avatar="mem.photo_url"
                                :profile-id="mem.user_id"
                                :not-show-profile-flag="true"
                                :scale="1"
                            />
                            <div
                                class="avatar-main-class avatar-select-block-class"
                            ></div>
                            <Username
                                :username="mem.name"
                                :profileId="mem.user_id"
                                :not-show-profile-flag="true"
                            />
                        </a-menu-item>
                        <a-menu-item
                                class="not-found-user"
                            v-if="!selectTransferList.length && !noTransfer"
                        >
                            {{TransferTips}}
                        </a-menu-item>
                    </a-menu>
                </a-dropdown>

                <a-button
                    v-on:click="transfer"
                    type="primary"
                    class="button-style"
                    :disabled="getTransferStatus || !this.changeAdminInfo"
                >
                    {{$t('transfer')}}
                </a-button>
                <div 
                    class="transfer-to-admin"
                    v-if="changeAdminInfo"
                >
                    {{$t('transfer_other_admin')}}<span class="transfer-to-admin-name">{{changeAdminInfo.name}}</span>.
                </div>
        </a-row>
        <TransferAdmin
            v-if="showTransferConfirm"
            v-on:confirm="handleOk"
            v-on:cancel="handleChange"
            :transfer-message = "sendTransferMessage"
            :admin-id="adminId"
        />
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {FlagInterface} from '@/helpers/Interfaces';
    import TransferAdmin from '@/components/TransferAdmin.vue';
    import QuestionMark from '@/components/QuestionMark.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            TransferAdmin,
            QuestionMark,
            FullScreenModal,
            UserAvatar,
            Username,
        },
    })
    export default class AdminSetting extends Vue {

        protected isMobile: boolean = IS_MOBILE;
        protected title: string = this.$t('admin_moderators') as string;
        protected showTransferConfirm: boolean = false;
        protected admin = '';
        protected adminList: any[] = [];
        protected moderatorList: any[] = [];
        protected selectAdminList: [] = [];
        protected selectModeratorList: [] = [];
        protected selectTransferList: [] = [];
        protected changeAdminInfo: any = '';
        protected sendTransferMessage: string = '';
        protected adminId: number = 0;
        protected moderatorTips: string = this.$t('user_not_enter') as string;
        protected adminTips: string = this.$t('user_not_enter') as string;
        protected TransferTips: string = this.$t('user_not_enter') as string;
        protected noMoremoderator: boolean = false;
        protected noAdmin: boolean = false;
        protected noTransfer: boolean = false;

        get adminStatus(): number {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, 1);
        }

        get getTransferStatus(): boolean
        {
            return this.adminList.length < 1 || !this.adminStatus || !this.adminList[0].name ? true : false;
        }

        get getAddAdminStatus(): boolean
        {
            return this.adminList.length < 3 && this.adminStatus ? true : false;
        }

        get getAddModeratorStatus(): boolean
        {
            return this.moderatorList.length < 5 ? true : false;
        }

        protected created()
        {
            this.getAdminList();
        }

        protected getAdminList()
        {
            this.$store.dispatch('GroupAdmin/getGroupMember',  {})
                    .then((response: any) => {
                        this.admin = response.response.data.data.admin;
                        this.adminList = response.response.data.data.adminData;
                        this.moderatorList = response.response.data.data.moderatorData;
                    });
        }

        protected handleOk() {
            this.showTransferConfirm = false;
            this.onCancel();
        }

        protected handleChange() {
            this.showTransferConfirm = false;
        }

        protected add(e: any, type: boolean) {
            if (type) {
                this.$store.dispatch('GroupAdmin/addGroupMember',  {admin_id: e.user_id})
                    .then((response: Response) => {
                        if (response && response.response && response.response.data
                                && response.response.data.code == 403) {
                            this.$message.error(this.$t('no_permission') as string);
                        } else {
                            this.getAdminList();
                            this.selectModeratorList = [];
                            this.selectAdminList = [];
                            this.selectTransferList = [];
                        }
                    });
            } else {
                this.$store.dispatch('GroupAdmin/addGroupModerator',  {admin_id: e.user_id})
                    .then((response: Response) => {
                        if (response && response.response && response.response.data
                                && response.response.data.code == 403) {
                            this.$message.error(this.$t('no_permission') as string);
                        } else {
                            this.getAdminList();
                            this.selectModeratorList = [];
                            this.selectAdminList = [];
                            this.selectTransferList = [];
                        }
                    });
            }
            
        }

        protected changeAdmin(e: any) {
            this.changeAdminInfo = e;
        }

        protected transfer() {
            this.sendTransferMessage = this.$t("transfer_admin", {username : this.changeAdminInfo.name}) as string;
            this.adminId = this.changeAdminInfo.user_id;
            this.showTransferConfirm = true;
        }

        @Emit('close-invite-member')
        protected onCancel() {

        }

        //add input 
        protected addSearchInput(type: boolean): void {
                if (type) {
                    var flag = true;
                    if (this.adminList.length >= 3) {
                        flag = false;
                        return;
                    }
                    for (let i = 0; i < this.adminList.length; i++) {
                        if (this.adminList[i].name === '') {
                            flag = false;
                            return;
                        }
                    }
                    if (flag) {
                        this.adminList.push({name: ''});
                    }
                    this.adminTips = this.$t('user_not_enter') as string;
                } else {
                    var flag = true;
                    if (this.moderatorList.length >= 5) {
                        flag = false;
                        return;
                    }
                    for (let i = 0; i < this.moderatorList.length; i++) {
                        if (this.moderatorList[i].name === '') {
                            flag = false;
                            return;
                        }
                    }
                    if (flag) {
                        this.moderatorList.push({name: ''});
                    }
                    this.moderatorTips = this.$t('user_not_enter') as string;
                }
                
        }
        
        /**
         * delete admin
         */
        protected deleteAdmin(index: number, type: boolean): void {
            if (type) {
                if (this.adminStatus) {
                    if (this.adminList[index].user_id == this.changeAdminInfo.user_id) {
                        this.changeAdminInfo = '';
                    }
                    this.$store.dispatch('GroupAdmin/delGroupMember',  {admin_id: this.adminList[index].user_id})
                        .then((response: Response) => {
                            if (response && response.response && response.response.data
                                    && response.response.data.code == 403) {
                                this.$message.error(this.$t('no_permission') as string);
                            } else {
                                this.getAdminList();
                                this.selectTransferList = [];
                            }
                        });
                }
            } else {
                this.$store.dispatch('GroupAdmin/delGroupModerator',  {admin_id: this.moderatorList[index].user_id})
                    .then((response: Response) => {
                        if (response && response.response && response.response.data
                                && response.response.data.code == 403) {
                            this.$message.error(this.$t('no_permission') as string);
                        } else {
                            this.getAdminList();
                        }
                    });
            }
            
        }

        protected onAdminChange(e: any) {
            if (e.target.value) {
                // console.log(e.target.value);
                this.noAdmin = true;
                this.adminTips = this.$t('user_not_fount') as string;
            } else {
                this.adminTips = this.$t('user_not_enter') as string;
            }
            this.selectAdminList = [];
            if (e.target.value) {
                this.$store.dispatch('GroupAdmin/selectGroupMember', {name: e.target.value})
                    .then((response: any) => {
                        this.selectAdminList = response.response.data.data.list;
                        this.noAdmin = false;
                    });
            }
            
        }

        protected onModeratorChange(e: any) {
            if (e.target.value) {
                this.noMoremoderator = true;
                this.moderatorTips = this.$t('user_not_fount') as string;
            } else {
                this.moderatorTips = this.$t('user_not_enter') as string;
            }

            this.selectModeratorList = [];
            if (e.target.value) {
                this.$store.dispatch('GroupAdmin/selectGroupMember', {name: e.target.value})
                    .then((response: any) => {
                        this.selectModeratorList = response.response.data.data.list;
                        this.noMoremoderator = false;
                    });
            }
            
        }

        protected onTransferChange(e: any) {
            if (e.target.value) {
                this.noTransfer = true;
                this.TransferTips = this.$t('user_not_fount') as string;
            } else {
                this.TransferTips = this.$t('user_not_enter') as string;
            }
            this.selectTransferList = [];
            if (e.target.value) {
                this.$store.dispatch('GroupAdmin/selectGroupAdmin', {name: e.target.value})
                    .then((response: any) => {
                        this.selectTransferList = response.response.data.data.list;
                        this.noTransfer = false;
                    });
            }
        }

        protected deleteTransfer()
        {
            this.changeAdminInfo = '';
            this.selectTransferList = [];
        }
    }
</script>
<style lang="scss" scoped>
    .mask {
        .content {
            .title {
                text-align: center;
                padding: var(--p4) 0;
                margin: 0 var(--p8) 0 0;
                padding-left: 24px;
            }
        }
    }
    

    .label {
        @include form_label;
        padding-top: var(--p6);
        padding-bottom: var(--p1);
        button {
            width: 100px;
            border-radius: 8px;
        }

    }

    .item {
        textarea {
            line-height: 25px;
        }
        .desc {
            font-size: 0.9rem;
            color: var(--desc-color);
            line-height: 1.3rem;
        }
    }

    .confirm-footer {
        button {
            height: 40px;
        }
    }
    .avatar-class {
        display: inline-block;
        padding-right: 28px;
        margin-right: 10px;
        border-radius: 30px;
        border: 1px solid var(--font-color6);

        border-left: none;
        .avatar-div-class {
            display: inline-block;
            width: 10px;
        }
        .ico {
            font-size: 1.3rem;
        }
        .avatar-delete-ico {
            position: relative;
            right: -18px;
        }

    }
    .not-found-user {
        color: var(--desc-color);
    }
    .avatar-main-class {
        display: inline-block;
        .avatar-main-add {
            padding-left: 24px;
            color: var(--font-color6);
        }
        .dropdown-jiahao-class {
            .ico {
                font-size: 1.55rem;
            }
            position: relative;
            top: -8px;
        }
    }

    .avatar-option-class {
        width: 240px;
        height: 50px;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .avatar-select-class {
        width: 240px;
        margin-top: 20px;
    }

    .avatar-select-block-class {
        width: 10px;
    }

    .dropdown-main-class {
        height: 40px;
        border: 1px solid var(--font-color6);
        border-left: none;
        padding-right: 20px;
        border-radius: 30px;
        line-height: 40px;
    }

    .transfer-main {
        height: 40px;
        margin-top: 12px;
        margin-bottom: 24px;
    }

    .add-admin {
        margin-top: 16px;
    }
    .transfer-admin {
        padding-top: 16px;
    }
    .button-style {
        height: 40px;
        margin-left: 24px;
        border-radius: 8px;
    }
    
    .title-icon {
        position: absolute;
        top: 40px;
        color: var(--font-color1);
    }
    .mobile-title-icon {
        top: 28px;
    }
    .add-style {
        max-height: 350px;
        overflow: auto;
    }


    .categories {
                margin-top: 12px;
                flex-wrap: wrap;

                & > span {
                    width: auto;
                    margin: 0 var(--p6) var(--p6) 0;

                    &:last-of-type {
                        margin-bottom: 0;
                    }
                }

                .delete-category {
                    cursor: pointer;
                    display: block;
                    margin: auto;
                    width: 1em;
                    height: 1em;

                    .ico {
                        vertical-align: initial;
                    }
                }

                .add-category {
                    $s: 40px;
                    display: inline-block;
                    width: $s;
                    height: $s;
                    background-color: var(--input-bg);
                    border: $border-width $border-style var(--border-color3);
                    border-radius: 50%;
                    cursor: pointer;

                    .inner {
                        width: 100%;
                        height: 100%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    
                        .ico {
                            cursor: pointer;
                            width: 1em;
                            height: 1em;
                        }
                    }
                }
            }
    .add-admin-input {
        width: 45%;
        margin-bottom: 12px;
    }

    .ant-input-group-wrapper {
        width: 45%;
    }
    .transfer-admin-input {
        width: 45%;
    }
    .mobile-transfer-admin-input {
        width: 90%;
        margin-bottom: 12px;
    }
    .mobile-add-admin-input {
        width: 90%;
    }

    .transfer-to-admin {
        margin-top: 12px;
        font-size: 0.9rem;
        color: var(--desc-color);
        line-height: 1.3rem;
        .transfer-to-admin-name {
            font-weight: 600;
            color: var(--font-color1);
        }
    }
</style>
<style>
    .ant-input[disabled] {
        background-color: var(--navbar-bg);
        border-color: var(--border-color2);
        color: var(--font-color1);
        cursor: not-allowed;
        opacity: 1;
    }
    .ant-input-affix-wrapper .ant-input:not(:first-child) {
        padding-left: 50px;
    }
</style>
