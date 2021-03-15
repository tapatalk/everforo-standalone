<template>
    <a-layout-sider class="left-sider">
        <div class="group-card">
            <div
                class="group-cover"
                v-if="groupCover"
            >
                <ImageHolder
                    :src="groupCover"
                />
            </div>
            <div class="group-name">
                <router-link :to="{name: 'group', params: {group_name: groupName}}">
                    <span>{{groupTitle}}</span>
                </router-link>
            </div>
            <div class="group-desc"> 
                <span>{{groupDesc}}</span>
            </div>
            <div class="group-stat">
                <div :class="['block',{'online':groupOnlineStatus}]">
                    <div
                            class="num"
                    >{{groupMembers}}</div>
                    <div class="text">{{$tc('members', groupMembers)}}</div>
                </div>
                <div v-if="groupOnlineStatus" :class="['block',{'online':groupOnlineStatus}]">
                    <a
                            v-on:click="onShowMemberList(true)"
                    >
                    <div
                            class="num"
                    >{{groupOnlineCount}}</div>
                    <div class="text">{{$tc('online', groupOnlineCount)}}</div></a>
                </div>
                <div :class="['block',{'online':groupOnlineStatus}]">
                    <div class="num">{{groupThreads}}</div>
                    <div class="text">{{$tc('threads', groupThreads)}}</div>
                </div>
            </div>

            <div
                v-if="$store.state.User.id && $store.state.User.activate"
                class="follow"
            > 
                <div v-if="canJoin && (groupJoining == 1 || groupJoining == 2)">
                        <div
                                v-if="groupJoining == 2 && !isFollow && joinStatus"
                                class="pending-admin-approval"
                        >
                            {{$t('pending_admin_approval')}}
                        </div>
                    <a-button
                            v-else
                            type="primary"
                            v-on:click="onFollow"
                            :loading="loading"
                    >
                        {{$t('join_group')}}
                    </a-button>
                </div>
            </div>

            <div
                v-else
                class="follow"
            >
                <a-button
                    type="primary"
                    v-if="groupJoining != 3 && groupJoining != 4"
                    v-on:click="showLoginModal"
                    :loading="loading"
                >
                    {{$t('join_group')}}
                </a-button>
            </div>

        </div>
        <MemberList
                v-if="showMemberList"
                :total-member="groupMembers"
                v-on:close-member-list="onCloseMemberList"
                :createSort = "memberListSort"
        />
        <JoinRequest
            v-if="showJoinFlag"
            v-on:close-invite-member="showJoinFlag=false"
         />
        <FooterSider/>
    </a-layout-sider>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import FooterSider from '@/components/FooterSider.vue';
    import ImageHolder from '@/components/ImageHolder.vue';
    import MemberList from '@/components/MemberList.vue';
    import JoinRequest from '@/components/JoinRequest.vue';
import { SORT_BY_MEMBER_ADMIN } from '@/helpers/Utils';

    @Component({
        components: {
            FooterSider,
            ImageHolder,
            MemberList,
            JoinRequest,
        }
    })
    export default class ThreadSider extends Vue {

        protected loading: boolean = false;
        protected showMemberList: boolean = false;
        protected showJoinFlag: boolean = false;
        protected memberListSort: string = '';

        get groupName(): string {
            return this.$store.state.Group.name;
        }

        get joinStatus(): boolean {
            return this.$store.state.User.joinStatus ? true : false;
        }

        get groupCover(): string {
            return this.$store.getters['Group/cover'];
        }

        get groupTitle(): string {
            return this.$store.state.Group.title;
        }

        get groupDesc(): string {
            return this.$store.state.Group.description;
        }

        get groupMembers(): number {
            return this.$store.state.Group.members;
        }

        get groupThreads(): number {
            return this.$store.state.Group.threads;
        }

        get groupOnlineCount(): number {
            return this.$store.state.Group.online_members || 0;
        }

        get isFollow(): boolean {
            return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        get groupOnlineStatus(): boolean {
            return this.groupOnlineCount > 0 ? true : false;
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

        //check user banned used check join button
        get canJoin(): boolean {
            return this.$store.getters['User/isBanned'](this.$store.state.Group.id);
        }

        protected created() {
            if (!this.groupMembers) {
                this.$store.dispatch('Group/getStat');
            }
        }

        protected onFollow() {
            if (this.$store.state.User.id && this.$store.state.User.activate) {
                if (this.groupJoining == 1) {
                    this.loading = true;
                    this.$store.dispatch('Group/follow')
                        .then((response: { success: number }) => {
                            if (response.success) {
                                if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission') 
                                && this.groupVisibility == 3) {
                                    location.reload();
                                } else {
                                    this.$store.dispatch('User/getMe');
                                }
                            }
                        });
                }
                if (this.groupJoining == 2) {
                    this.showJoinFlag = true;
                }
            } else {
                this.$store.commit('setShowLoginModal', true);
            }
        }

        protected showLoginModal() {
            this.$store.commit('setShowLoginModal', true);
        }

        protected onShowMemberList(flag = false) {
            if (this.isFollow || this.$store.getters['User/isSuperAdmin']()) {
                if (flag) {
                    this.memberListSort = SORT_BY_MEMBER_ADMIN[3];
                } else {
                    this.memberListSort = SORT_BY_MEMBER_ADMIN[0];
                }
                this.showMemberList = true;
            }
        }

        protected onCloseMemberList() {
            this.showMemberList = false;
        }
    }
</script>
<style lang="scss" scoped>

    .left-sider {
        border-right: none;
    }

    .group-card {
        background-color: var(--group-bg);
        border: $border-width $border-style var(--border-color1);
        margin: var(--p6);
        border-radius: 8px;
        overflow: hidden;

        .group-cover {
            // width: 100%;
            height: auto;
            text-align: center;

            img {
                width: 100%;
            }
        }

        .group-name {
            padding: 18px var(--p4) 8px;
            font-size: 18px;
            line-height: 23px;
            font-weight: 500;

            a {
                color: var(--font-color1);
            }
        }

        .group-desc {
            @include category_font;
            // font-size: category_font;
            // line-height: category_font-line-height;
            line-height: $category-line-height;
            padding: 0 var(--p4);
            margin-bottom: 22px;
            // color: var(--font-color1);
            overflow: hidden;
            word-break: break-word;

            &:last-child {
                margin-bottom: var(--p4);
            }
        }

        .group-stat {
            position: relative;
            width: 100%;
            padding: 0 var(--p4) var(--p4);
            @include clear_after();

            .block {
                width: 50%;
                float: left;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-content: center;

                .num {
                    font-size: 20px;
                    line-height: 25px;
                    font-weight: 500;
                    color: var(--font-color1);
                }

                .text {
                    font-size: 12px;
                    line-height: 15px;
                    color: #8C97AD;
                    @include capitalize();
                }
            }

            .online {
                width: 33.3333%;
            }

        }

        .follow {
            padding: 0 var(--p4) var(--p4);

            .ant-btn-primary, .ant-btn-default {
                width: 100%;
                margin-top: var(--p3);
                font-weight: 500;
                height: $common-btn-height;
                @include capitalize;
            }
        }
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

    .footer-sider {
        margin: var(--p6);
    }
</style>
