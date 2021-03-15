<template>
    <FullScreenModal
        v-on:close="onCancel"
        :content-max-width="isMobile?'95%':'520px'"
        :content-height="isMobile ? '60%' : '50%'"
        :max-height="isMobile ? '60%' : '50%'"
        v-on:buttom="loadMoreList"
        :top="isMobile ? '20%' : '25%'"
    >
        <template v-slot:header>
            <div
                    class="title"
            >
                <div>{{title}} <a v-if="isFollow && canInvite" v-on:click="showInviteAction" class="invite"><div> + {{$t('invite')}} </div></a></div>
                <div>
                    <Sort
                        :selected-sort="sortBy"
                        :sort-by-data="sortData"
                        :sort-by-type="1"
                        v-on:sort-change="onSortChange"
                    />
                </div>
            </div>
        </template>
        <div
                v-on:click="onCancel"
                :class="['modal-close-btn']"
        >
            <Icons
                    type="chacha"
            />
        </div>
        <div
            v-if="!sortOnlineStatus && !sorActiveStatus && !sorPendingStatus"
        >
            <div
                v-for="mem in memberList"
                :key="mem.user_id"
                class='main profile-list-item'
            >
                <UserAvatar
                    :username="mem.name"
                    :avatar="mem.photo_url"
                    :profile-id="mem.user_id"
                    :class="[{'mobile': isMobile}]"
                    :scale="2"
                    :is-ban="$store.getters['BanUser/isBan'](mem.user_id, mem.is_ban)"
                    :online="mem.online"
                />
                <div class="member-body">
                    <div :class="['member-info', {'mobile': isMobile}]">
                        <Username
                                :username="mem.name"
                                :profileId="mem.user_id"
                        />
                        <Dot v-if="mem.is_admin == 1 || (mem.is_admin > 1 && adminStatus)" />
                        <a-avatar
                                v-if="mem.is_admin == 1"
                                class="admin-avatar"
                                slot="avatar"
                                icon="user"
                                :size="16"
                                :src="admin_img"
                        />
                        <a-avatar
                                v-else-if="mem.is_admin == 2 && adminStatus"
                                class="admin-avatar"
                                slot="avatar"
                                icon="user"
                                :size="16"
                                :src="admin_img"
                        />
                        <a-avatar
                                v-else-if="mem.is_admin == 3 && adminStatus"
                                class="admin-avatar"
                                slot="avatar"
                                icon="user"
                                :size="16"
                                :src="moderator_img"
                        />
                    </div>
                    <span class="since-color">{{$t('member_since')}} </span> <TimeString :time="mem.created_at"/>
                    <span class="since-color" v-if="mem.likes_count && mem.likes_count > 0"
                          slot="content"
                          :class="['likes-count']"
                    >
                            <Dot />
                        <span class="since-color">{{mem.likes_count}} {{$tc('member_list_likes', mem.likes_count)}}</span>
                    </span>
                </div>
            </div>
        </div>
            
        <div
            v-else-if="sortOnlineStatus"
        >
            <div v-if="showOnlineFlag" >
                <div 
                    v-for="mem in memberList"
                    :key="mem.user_id"
                >
                    <div
                        v-if="mem.seven_days"
                        class='main profile-list-item'
                    >
                        <UserAvatar
                            :username="mem.name"
                            :avatar="mem.photo_url"
                            :profile-id="mem.user_id"
                            :class="[{'mobile': isMobile}]"
                            :scale="2"
                            :is-ban="$store.getters['BanUser/isBan'](mem.user_id, mem.is_ban)"
                            :online="mem.online"
                        />
                        <div class="member-body">
                            <div :class="['member-info', {'mobile': isMobile}]">
                                <Username
                                    :username="mem.name"
                                    :profileId="mem.user_id"
                                />
                                <Dot v-if="mem.is_admin == 1 || (mem.is_admin > 1 && adminStatus)" />
                                <a-avatar
                                        v-if="mem.is_admin == 1"
                                        class="admin-avatar"
                                        slot="avatar"
                                        icon="user"
                                        :size="16"
                                        :src="admin_img"
                                />
                                <a-avatar
                                        v-else-if="mem.is_admin == 2 && adminStatus"
                                        class="admin-avatar"
                                        slot="avatar"
                                        icon="user"
                                        :size="16"
                                        :src="admin_img"
                                />
                                <a-avatar
                                        v-else-if="mem.is_admin == 3 && adminStatus"
                                        class="admin-avatar"
                                        slot="avatar"
                                        icon="user"
                                        :size="16"
                                        :src="moderator_img"
                                />
                            </div>
                            <DayString :time="mem.updated_at"/>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showNotOnlineFlag" >
                <div class="noline-flag">
                    <div class="online-child-left"></div>
                    <div class="online-child-center">{{$t('days_ago')}}</div>
                    <div class="online-child-right"></div>
                </div>
                <div 
                    v-for="mem in memberList"
                    :key="mem.user_id"
                    class='main-notonline'
                >
                    <div
                        v-if="!mem.seven_days"
                        class='main profile-list-item'
                    >
                        <UserAvatar
                            :username="mem.name"
                            :avatar="mem.photo_url"
                            :profile-id="mem.user_id"
                            :class="[{'mobile': isMobile}]"
                            :scale="2"
                            :is-ban="$store.getters['BanUser/isBan'](mem.user_id, mem.is_ban)"
                            :notOnline="true"
                        />
                        <div class="member-body">
                            <div :class="['member-info', {'mobile': isMobile}]">
                                <Username
                                    :username="mem.name"
                                    :profileId="mem.user_id"
                                />
                                <Dot v-if="mem.is_admin == 1 || (mem.is_admin > 1 && adminStatus)" />
                                <a-avatar
                                        v-if="mem.is_admin == 1"
                                        class="admin-avatar"
                                        slot="avatar"
                                        icon="user"
                                        :size="16"
                                        :src="admin_img"
                                />
                                <a-avatar
                                        v-else-if="mem.is_admin == 2 && adminStatus"
                                        class="admin-avatar"
                                        slot="avatar"
                                        icon="user"
                                        :size="16"
                                        :src="admin_img"
                                />
                                <a-avatar
                                        v-else-if="mem.is_admin == 3 && adminStatus"
                                        class="admin-avatar"
                                        slot="avatar"
                                        icon="user"
                                        :size="16"
                                        :src="moderator_img"
                                />
                            </div>
                            <DayString :time="mem.updated_at" :online = "true"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="sorActiveStatus"
        >
            <div
                v-for="mem in memberList"
                :key="mem.user_id"
                class='main profile-list-item'
            >
                <UserAvatar
                    :username="mem.name"
                    :avatar="mem.photo_url"
                    :profile-id="mem.user_id"
                    :class="[{'mobile': isMobile}]"
                    :scale="2"
                    :is-ban="$store.getters['BanUser/isBan'](mem.user_id, mem.is_ban)"
                    :online="mem.online"
                />
                <div class="member-body">
                    <div :class="['member-info', {'mobile': isMobile}]">
                        <Username
                                :username="mem.name"
                                :profileId="mem.user_id"
                        />
                        <Dot v-if="mem.is_admin == 1 || (mem.is_admin > 1 && adminStatus)" />
                        <a-avatar
                                v-if="mem.is_admin == 1"
                                class="admin-avatar"
                                slot="avatar"
                                icon="user"
                                :size="16"
                                :src="admin_img"
                        />
                        <a-avatar
                                v-else-if="mem.is_admin == 2 && adminStatus"
                                class="admin-avatar"
                                slot="avatar"
                                icon="user"
                                :size="16"
                                :src="admin_img"
                        />
                        <a-avatar
                                v-else-if="mem.is_admin == 3 && adminStatus"
                                class="admin-avatar"
                                slot="avatar"
                                icon="user"
                                :size="16"
                                :src="moderator_img"
                        />
                    </div>
                    <span class="since-color">{{$t('member_active')}} </span> <TimeString :time="mem.updated_at"/>
                </div>
            </div>
        </div>
        <div
            v-if="sorPendingStatus"
        >
            <div
                v-for="mem in memberList"
                :key="mem.user_id"
                class='main profile-list-item'
            >
                <UserAvatar
                    :username="mem.name"
                    :avatar="mem.photo_url"
                    :profile-id="mem.user_id"
                    :class="[{'mobile': isMobile}]"
                    :scale="2"
                />
                <div class="member-body">
                    <div :class="['member-info', {'mobile': isMobile}]">
                        <Username
                            :username="mem.name"
                            :profileId="mem.user_id"
                        />
                        <a
                            class="approve"
                            v-on:click="ignoreJoin(mem.user_id)"
                        >
                            {{$t('ignore')}}
                        </a>
                        <a
                            class="approve approve-right"
                            v-on:click="approveJoin(mem.user_id)"
                        >
                            {{$t('approve')}}
                        </a>
                    </div>
                    <span class="since-color">{{mem.join_msg}}</span>
                </div>
            </div>
        </div>

        <div v-if="loadMore">
            <a-skeleton
                    active
                    avatar
                    :paragraph="{rows: 1}"
                    :loading="loadMore"
            />
        </div>
        <InviteMember
                v-if="showInviteMemberStatus"
                v-on:close-invite-member="showInviteMemberStatus = false"
        />
    </FullScreenModal>
</template>
<script lang="ts">
import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
import FullScreenModal from '@/components/FullScreenModal.vue';
import UserAvatar from '@/components/UserAvatar.vue';
import Dot from '@/components/Dot.vue';
import Username from '@/components/Username.vue';
import TimeString from '@/components/TimeString.vue';
import DayString from '@/components/DayString.vue';
import Sort from '@/components/Sort.vue';
import OnlineButton from '@/components/OnlineButton.vue';
import InviteMember from '@/components/InviteMember.vue';
import {MemberListInterface} from "@/helpers/Interfaces";
import {Response} from "@/http/Response";
import {SORT_BY_MEMBER_ADMIN, SORT_BY_MEMBER} from "@/helpers/Utils";

@Component({
  components: {
      FullScreenModal,
      UserAvatar,
      Dot,
      Username,
      TimeString,
      Sort,
      InviteMember,
      OnlineButton,
      DayString,
  },
})
export default class MemberList extends Vue {

    @Prop()
    protected createSort!:any;

    protected totalMember = 0;

    protected title = '';
    protected visible= false;
    protected isMobile = false;
    protected memberList: any = [];
    protected pageNum = 1;
    protected admin_img = '/img/member_list_admin.png';
    protected moderator_img = '/img/moderator.png';
    //Used to control whether to continue loading
    protected loadMore = true;
    protected sortByString:string = '';
    protected sortData:string[] = [this.$tc('all')];
    protected pageSize:number = 20;
    protected showInviteMemberStatus:boolean=false;

    protected created() {
        //if admin ,can select filter,if member ,can not see banned member
        if (this.createSort !== SORT_BY_MEMBER_ADMIN[3] && this.createSort !== SORT_BY_MEMBER_ADMIN[4]) {
            this.sortByString = SORT_BY_MEMBER_ADMIN[0];
        } else if (this.createSort === SORT_BY_MEMBER_ADMIN[3]) {
            this.sortByString = SORT_BY_MEMBER_ADMIN[3];
        } else if (this.createSort === SORT_BY_MEMBER_ADMIN[4]) {
            this.sortByString = SORT_BY_MEMBER_ADMIN[4];
        }

        //if group ban num is 0, unShow member list filter
        this.$store.dispatch('Members/getBanNum',  {})
                .then((response: Response) => {
                    const data: {active_count: number, ban_count: number, online_count: number, pending_count: number} = response.getData();
                    if (data && data.ban_count && data.ban_count > 0) {
                        if (this.isAdmin) {
                            this.sortData = this.sortData.concat([this.$tc('banned')]);
                        }
                    }

                    if (data && data.active_count && data.active_count > 0) {
                        this.sortData = this.sortData.concat([this.$tc('active')]);
                    }

                    if (data && data.online_count && data.online_count > 0) {
                        this.sortData = this.sortData.concat([this.$tc('online')]);
                    }
                    if ((this.$store.getters['User/isSuperAdmin']()
                            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus)
                            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus)
                            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus))
                            && data && data.pending_count && data.pending_count > 0
                            && this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission')
                        ) {
                            this.sortData = this.sortData.concat([this.$tc('pending')]);
                    }
                    
                })
        
        this.loadMoreList();
    }
    get sortBy(): string {
        return this.sortByString;
    }
    set sortBy(sortBy : string) {
        this.sortByString = sortBy;
    }

    get adminStatus(): number {
        return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
    }

    get isAdmin(): string {
        return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
        || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus) 
        || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus);
    }

    get isFollow(): boolean {
        return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
    }

    get canInvite(): boolean {
        var flag = true;
        if (this.groupJoining == 4 && !this.isAdmin) {
            flag = false;
        }
        return flag;
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

    get showOnlineFlag(): boolean {
        for (let index = 0; index < this.memberList.length; index++) {
            if (this.memberList[index].seven_days) {
                return true;
            }
        }
        return false;
    }

    get showNotOnlineFlag(): boolean {
        for (let index = 0; index < this.memberList.length; index++) {
            if (!this.memberList[index].seven_days) {
                return true;
            }
        }
        return false;
    }

    get sortOnlineStatus(): boolean {
        if (this.sortBy === SORT_BY_MEMBER_ADMIN[3]) {
            return true;
        } else {
            return false;
        }
    }

    get sorActiveStatus(): boolean {
        if (this.sortBy === SORT_BY_MEMBER_ADMIN[2]) {
            return true;
        } else {
            return false;
        }
    }

    get sorPendingStatus(): boolean {
        if (this.sortBy === SORT_BY_MEMBER_ADMIN[4]) {
            return true;
        } else {
            return false;
        }
    }

    // typescript decorator
    @Emit('close-member-list')
    protected onCancel() {
        this.pageNum = 1;
        this.loadMore = true;
    }

    //load more data when the scroll bar changes
    protected loadMoreList() {
        if (this.loadMore) {
            this.loadMore = false;
            this.$store.dispatch('Members/getMemberList',  { page : this.pageNum, filter : this.sortBy})
                    .then((response: Response) => {
                        this.loadMore = true;
                        const data = response.getData();
                        if (data && data.list && data.list.length) {
                            this.memberList = this.memberList.concat(data.list);
                            this.pageNum = this.pageNum + 1;
                            this.totalMember = data.count;
                            if (data.list.length < this.pageSize) {
                                this.loadMore = false;
                            }
                        } else {
                            this.loadMore = false;
                        }
                    })
                    .catch(() =>{
                        //error
                        this.$message.error(this.$t('member_list_error') as string);
                    })
                    .finally(() => {
                        //do
                        if (this.sortBy === SORT_BY_MEMBER_ADMIN[1]) {
                            this.title = this.totalMember + ' ' + this.$tc('ban_user_number', this.totalMember);
                        } else if (this.sortBy === SORT_BY_MEMBER_ADMIN[2]) {
                            this.title = this.totalMember + ' ' + this.$tc('active_number', this.totalMember);
                        } else if (this.sortBy === SORT_BY_MEMBER_ADMIN[3]) {
                            this.title = this.totalMember + ' ' + this.$tc('online_number', this.totalMember);
                        } else if (this.sortBy === SORT_BY_MEMBER_ADMIN[4]) {
                            this.title = this.totalMember + ' ' + this.$tc('pending_number', this.totalMember);
                        } else {
                            this.title = this.totalMember + ' ' + this.$tc('members', this.totalMember);
                        }
                    });
        }

    }


    public onSortChange(sortBy: string)
    {
        this.pageNum = 1;
        this.memberList = [];
        this.totalMember = 0;
        this.loadMore = true;
        this.sortBy = sortBy;
        this.loadMoreList();
    }

    protected approveJoin(user_id: any)
    {
        var formData = new FormData;
        formData.append('user_id', user_id);
        formData.append('group_id', this.$store.state.Group.id);
        this.$store.dispatch('GroupPrivacy/approveJoin', formData)
                .then((response: Response) => {
                    if (response && response.response && response.response.data
                            && response.response.data.code == 403) {
                        this.$message.error(this.$t('no_permission') as string);
                        return;
                    }
                    const data: {no_msg: number} = response.getData();

                    if ((!data || !data.no_msg) && this.$store.getters['User/isBanned'](this.$store.state.Group.id)) {
                        this.$message.info(this.$t('approve_success') as string);
                    }
                    var list = [];
                    for (let i = 0; i < this.memberList.length; i++) {
                        if (this.memberList[i].user_id !== user_id) {
                            list.push(this.memberList[i]);
                        }
                    }
                    this.totalMember = this.totalMember - 1;
                    this.title = this.totalMember + ' ' + this.$tc('pending_number', this.totalMember);
                    this.memberList = list;
                });
    }

    protected ignoreJoin(user_id: any)
    {
        var formData = new FormData;
        formData.append('user_id', user_id);
        formData.append('group_id', this.$store.state.Group.id);
        this.$store.dispatch('GroupPrivacy/ignoreJoin', formData)
                .then((response: any) => {
                    if (response && response.response && response.response.data
                            && response.response.data.code == 403) {
                        this.$message.error(this.$t('no_permission') as string);
                        return;
                    }
                    const data: {no_msg: number} = response.getData();
                    
                    if ((!data || !data.no_msg) && this.$store.getters['User/isBanned'](this.$store.state.Group.id)) {
                        this.$message.info(this.$t('ignore_success') as string);
                    }
                    var list = [];
                    for (let i = 0; i < this.memberList.length; i++) {
                        if (this.memberList[i].user_id !== user_id) {
                            list.push(this.memberList[i]);
                        }
                    }
                    this.totalMember = this.totalMember - 1;
                    this.title = this.totalMember + ' ' + this.$tc('pending_number', this.totalMember);
                    this.memberList = list;
                });
    }

    protected showInviteAction()
    {
        this.$store.dispatch('GroupPrivacy/getInviteStatus', {})
                .then((response: any) => {
                    if (response.success == 1) {
                        this.showInviteMemberStatus = true;
                    }
                });
    }

}
</script>
<style lang="scss" scoped>
    $avatar-size: $avatar-size1;
    $avatar-margin-right: var(--p4);
    $time-size: $member-list-time-font-size;
    $name-size: $member-list-name-font-size;
    .content {
        max-height: 40%;
    }
    .sort {
        line-height: $name-size;
        font-size: 0.9rem;
        .sort-order {
            font-size: 0.9rem;
        }
        .sort-by {
            font-size: 0.9rem;
        }
    }
    
    .noline-flag {
        display:flex;
        justify-content:center;
        margin-bottom: 32px;
        margin-top: 32px;
        .online-child-left {
            width: 100px;
            margin-top: auto;
            margin-bottom: auto;
            margin-right: 40px;
            border-bottom: 1px solid var(--not-online-color);
        }
        
        .online-child-right {
            width: 100px;
            margin-top: auto;
            margin-bottom: auto;
            margin-left: 40px;
            border-bottom: 1px solid var(--not-online-color);
        }

        .online-child-center {
            color: var(--not-online-color);
        }
    }

    .invite {
        padding: 1px 5px;
        margin-left: 15px;
        font-size: 0.9rem;
        font-weight: 500;
        border: 1px solid var(--border-color5);
        border-radius:5px;
        color: var(--font-color2);
        div {
            display: inline;
        }
    }
    .dot-dot {
        color: var(--desc-color);
    }
    .profile-list-item {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        
        
    }
    .main {
        display: flex;
        .avatar-div {
            flex-shrink: 0;
            margin-right: $avatar-margin-right;

            &.mobile {
                margin-right: var(--p4);
            }
        }

        .member-info {
            height: 20px;
            line-height: 20px;
            margin-bottom: var(--p2);
            position: relative;
            &.mobile {
                height: auto;
                line-height: 1.5rem;
            }

            .name {
                font-size: $name-size;
            }

            .admin-avatar {
                margin-bottom: 4px;
            }

            .approve {
                float: right;
                color: var(--font-color2);
                font-size: 0.9rem;
            }
            .approve-right {
                margin-right: 20px;
            }
            .admin-avatar {
                margin-bottom: 4px;
            }
        }

        .time-string {
            font-size: $time-size;
        }

        .member-body {
            flex: 1 1 auto;
            max-width: 100%;
            border-bottom: 1px solid var(--border-color5);
            padding-top: var(--p4);
            padding-bottom: var(--p4);
            .likes-count {
                @include content_font;
                @include wrap_words;
                line-height: $category-line-height;
                transition: all 2s ease-in-out 1s;

                &.new {
                    background-color: var(--theme-color)-transparent;
                }
            }
            .since-color {
                font-size: $time-size;
                color: var(--desc-color);
            }
        }
        // .member-body:last-child {
        //     border-bottom: none;
        // }
        &:last-child {
            .member-body:last-child {
                border-bottom: none;
            }
        }
    }
    .main-notonline {
        .main {
            .member-body {
                .member-info {
                    .name {
                        color: var(--not-online-name-color); 
                    }
                }
                
                .time-string {
                    color:  var(--not-online-color);
                }
            }
        }
    }
    .mask .content .title {
        margin-bottom: 0;
    }
</style>