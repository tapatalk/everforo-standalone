<template>
    <section class="group-card">
        <div
            class="group-cover"
            v-if="groupCover"
        >
            <ImageHolder
                :src="groupCover"
            />
        </div>
        <div class="group-name">
            <router-link :to="{name: 'group', params: {group_name: groupName,sort:sort}}">
                <span>{{groupTitle}}</span>
            </router-link>
        </div>
        <div 
            class="group-desc"
            v-if="groupDesc"    
        >
            <span>{{groupDesc}}</span>
        </div>
        <div class="group-meta">
            <span v-if="!isFollow">
                <span class="meta-img">
                    <Icons
                            type="members.png"
                    />
                </span>
                <span class="num">{{groupMembersCount}}</span>
                <span class="text">{{$tc('members', groupMembersCount)}}</span>
            </span>
            <a v-else v-on:click="onShowMemberList(false)"><span class="meta-img">
                <Icons
                    type="members.png"
                />
            </span>
            <span class="num">{{groupMembersCount}}</span>
            <span class="text">{{$tc('members', groupMembersCount)}}</span></a>
            <a v-if="isFollow && canInvite && groupMembersCount == 1" v-on:click="onShowInviteMember" class="invite"><div> + {{$t('invite')}} </div></a>
        </div>
        <div 
            class="group-meta"
            v-on:click="onShowMemberList(true)"
            v-if="groupOnlineStatus"
        >
            <span class="meta-img"></span>
            <a v-if="isFollow"><span class="num">{{groupOnlineCount}}</span>
            <span class="text">{{$t('online')}}</span></a>
            <span v-else>
                <span class="num">{{groupOnlineCount}}</span>
                <span class="text">{{$t('online')}}</span>
            </span>
        
        </div>
        <div class="group-meta">
            <span class="meta-img">
                <Icons
                    type="group-chat.png"
                />
            </span>
            <span class="num">{{groupTopicsCount}}</span>
            <span class="text">{{$tc('threads', groupTopicsCount)}}</span>
        </div>
        <div
            v-if="$store.state.User.id && $store.state.User.activate"
        >
            <div v-if="canJoin">
                <div
                        v-if="canManage"
                        :class="['manage', {'bg': inManage}]"
                        v-on:click="showManageGroup"
                >
                    <a-button
                            type="primary"
                            :loading="joining"
                    >
                        {{$t('manage')}}
                    </a-button>
                </div>
                <div
                        v-else-if="groupJoining == 2 && !isFollow && joinStatus"
                        class="follow"
                >
                    <div
                            class="pending-admin-approval"
                    >
                        {{$t('pending_admin_approval')}}
                    </div>
                </div>
                <div v-else
                    style="height: 20px"
                >

                </div>
            </div>
            <div
                    v-else
                    class="can-not-join"
            >

            </div>
        </div>
        <div
            v-else
        >
            <div
                class="follow"
                v-if="groupJoining != 3 && groupJoining != 4"
                v-on:click="showLoginModal"
            >
                <a-button
                    type="primary" 
                    :loading="joining"
                >
                    {{$t('join_group')}}
                </a-button>
            </div>
            <div v-else
                 style="height: 20px"
            >

            </div>
        </div>
        <ConfirmModal
            v-if="showUnfollowConfirm"
            :reverse-button="true"
            :yes-text="$t('leave')"
            :no-text="$t('not_now')"
            v-on:confirm="onUnFollow"
            v-on:cancel="showUnfollowConfirm = false"
        >
            <div
                class="confirm-message"
            >{{$t('leave_group_confirm')}}</div>
        </ConfirmModal>
<!--        <MemberList-->
<!--            v-if="showMemberList"-->
<!--            :total-member="groupMembersCount"-->
<!--            v-on:close-member-list="onCloseMemberList"-->
<!--        />-->
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import ImageHolder from '@/components/ImageHolder.vue';
    import JoinRequest from '@/components/JoinRequest.vue';
    import { SORT_BY_GROUP } from '@/helpers/Utils';

    @Component({
        components: {
            ConfirmModal,
            ImageHolder,
            JoinRequest,
        },
    })
    export default class GroupCard extends Vue {

        protected joining: boolean = false;
        protected showUnfollowConfirm: boolean = false;
        protected showMemberList: boolean = false;
        protected showJoinFlag: boolean = false;


        get groupCover(): string {
            return this.$store.getters['Group/cover'];
        }

        get joinStatus(): boolean {
            return this.$store.state.User.joinStatus ? true : false;
        }

        get groupName(): string {
            return this.$store.state.Group.name;
        }

        get sort():string {
            return SORT_BY_GROUP[1];
        }

        get groupTitle(): string {
            return this.$store.state.Group.title;
        }

        get groupMembersCount(): number {
            return this.$store.state.Group.members || 0;
        }

        get isAdmin(): string {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus);
        }

        get canInvite(): boolean {
        var flag = true;
            if (this.groupJoining == 4 && !this.isAdmin) {
                flag = false;
            }
            return flag;
        }

        get groupOnlineCount(): number {
            return this.$store.state.Group.online_members || 0;
        }

        get groupOnlineStatus(): boolean {
            return this.groupOnlineCount > 0 ? true : false;
        }

        get groupTopicsCount(): number {
            return this.$store.state.Group.threads || 0;
        }

        get groupDesc(): string {
            return this.$store.state.Group.description;
        }

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get canManage(): boolean {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus);
        }

        //check user banned used check join button
        get canJoin(): boolean {
            return this.$store.getters['User/isBanned'](this.$store.state.Group.id);
        }

        get inManage(): boolean {
            return this.$route.name === 'manage';
        }

        get isFollow(): boolean {
            return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        get fetchedStatisticGroupName() : string {
            return this.$store.state.Group.fetchedStatisticGroupName;
        }

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

        @Watch('groupName', {immediate: true})
        protected onSwitchGroup(newGroup: string, oldGroup: string) {
            // fetch group statistic data when first land on a group or switch group
            // if ((newGroup && oldGroup === undefined && !this.groupMembersCount) 
                // || oldGroup !== undefined && newGroup !== oldGroup) 
            if (newGroup !== this.fetchedStatisticGroupName)
            {
                this.$store.dispatch('Group/getStat');
            }
        }

        protected onFollow() {
            if (this.groupJoining == 1) {
                this.joining = true;
                this.$store.dispatch('Group/follow')
                    .then((response: { success: number }) => {
                        this.joining = false;
                        if (response.success) {
                            if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission') 
                                && this.groupVisibility == 3) {
                                    location.reload();
                            } else {
                                this.$store.dispatch('User/getMe');
                                this.$store.dispatch('Group/getStat');
                            }
                        }
                    });
            }
            if (this.groupJoining == 2) {
                this.onShowJoinRequest();
            }
            
        }

        protected onUnFollowConfirm() {

            this.showUnfollowConfirm = true;
        }

        protected onUnFollow() {
            this.joining = true;
            this.$store.dispatch('Group/unfollow')
                .then((response: { success: number }) => {
                    this.joining = false;
                    if (response.success) {
                        if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission') 
                        && this.groupVisibility == 3) {
                            location.reload();
                        } else {
                            this.$store.dispatch('User/getMe');
                            this.showUnfollowConfirm = false;
                            this.$store.dispatch('Group/getStat');
                        }
                        
                    }
                });
        }

        protected showLoginModal() {
            this.$store.commit('setShowLoginModal', true);
        }

        // protected onShowMemberList() {
        //     this.showMemberList = true;
        // }

        protected onCloseMemberList() {
          this.showMemberList = false;
        }

        @Emit('show-manage-group')
        protected showManageGroup(){

        }

        @Emit('show-member-list')
        protected onShowMemberList(flag = false){
            return flag;
        }

        @Emit('show-join-request')
        protected onShowJoinRequest(){
        }

        @Emit('show-invite-member')
        protected onShowInviteMember(){

        }

    }
</script>
<style lang="scss" scoped>
    .group-card {
        overflow: hidden;

        .invite {
            padding: 1px 8px 1px 1px;
            margin-left: 15px;
            border: 1px solid var(--border-color6);
            border-radius:5px;
            color: var(--font-color2);
            div {
                display: inline;
            }
        }
        .group-cover {
            width: 100%;
            height: auto;
            text-align: center;

            img {
                width: 100%;
            }
        }

        .group-name {
            padding: var(--p5) var(--p6) var(--p2) 0;
            @include title_font;

            a {
                color: var(--font-color1);
            }
        }

        .group-desc {
            @include category_font;
            padding: 0 var(--p6) 0 0;
            line-height: $category-line-height;
            margin-bottom: 21px;

            &:last-child {
                margin-bottom: var(--p4);
            }

            & > span {
                word-break: break-word;
            }
        }

        .manage,
        .follow,
        .unfollow {

            button {
                height: $common-btn-height;
            }

            padding-right: var(--p6);

            &.bg, &:hover {

                .ico, a {
                    color: var(--theme-color);
                }
            }

            a {
                display: block;
                width: 100%;
                height: 100%;
                line-height: $content-line-height;
                padding: var(--p3) var(--p6) var(--p3) 0;
                color: var(--font-color3);
                @include capitalize;
            }

            span {
                padding-left: var(--p3);
            }

            .ant-btn-primary, .ant-btn-default {
                width: 100%;
                margin: 1.4rem 0;
                font-weight: 500;
                @include capitalize;
            }
        }

        .manage,
        .unfollow {
            .ant-btn-primary, .ant-btn-default {
                width: 100%;
                margin: 1.4rem 0;
                font-weight: 400;
                @include capitalize;
                background: none;
                color:var(--font-color2);
                border-color: var(--border-color6);
                box-shadow: none;
            }
        }

        .unfollow {
            .ant-btn-default {
                color: var(--font-color2);
                @include input;

                &:hover, &:focus {
                    border: 1px solid transparent;
                }
            }

            img {
                height: 16px;
                display: inline-block;
                vertical-align: middle;
                margin-top: -3px;
            }
        }

        .group-meta {
            margin-top: var(--p3);

            .meta-img {
                width: 24px;
            }

            .ico {
                width: 18px;
                height: 16px;
                vertical-align: top;
            }

            span {
                font-size: 14px;
                line-height: 18px;
                color: var(--desc-color);
                display: inline-block;

                &.num {
                    color: var(--font-color1);
                    margin-right: 6px;
                    min-width: 32px;
                    text-align: right;
                }

                &.text {
                    text-align: left;
                }
            }
        }
        .can-not-join {
            height: 30px;
        }
        .pending-admin-approval {
            height: 40px;
            width: 100%;
            margin: 1.4rem 0;
            font-weight: 400;
            display: inline-block;
            background: none;
            color: var(--font-color2);
            border-color: var(--border-color6);
            -webkit-box-shadow: none;
            box-shadow: none;
            border: 1px solid;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            line-height: 40px;
        }
    }
</style>
