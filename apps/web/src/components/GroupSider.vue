<template>
    <a-layout-sider class="left-sider">
        <div 
            id="sticky" 
            class="sticky"
            :style="{minHeight: stickyMinHeight + 'px'}"
        >
            <div>
                <GroupCard
                    v-on:show-manage-group="showManageGroup"
                    v-on:show-member-list="showMemberList"
                    v-on:show-invite-member="showInviteAction()"
                    v-on:show-join-request="showJoinRequest"
                />
                <CategoryTree/>
            </div>
            <FooterSider/>
        </div>
        <MemberList
                v-if="showMemberListStatus"
                v-on:close-member-list="showMemberListStatus = false"
                :createSort = "memberListSort"
        />
        <InviteMember
                v-if="showInviteMemberStatus"
                v-on:close-invite-member="showInviteMemberStatus = false"
        />
        <JoinRequest
            v-if="showJoinFlag"
            v-on:close-invite-member="showJoinFlag=false"
         />
    </a-layout-sider>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {NAV_BAR_HEIGHT, SORT_BY_MEMBER_ADMIN, windowHeight} from '@/helpers/Utils';
    import CategoryTree from '@/components/CategoryTree.vue';
    import FooterSider from '@/components/FooterSider.vue';
    import GroupCard from '@/components/GroupCard.vue';
    import MemberList from '@/components/MemberList.vue';
    import InviteMember from '@/components/InviteMember.vue';
    import JoinRequest from '@/components/JoinRequest.vue';

    @Component({
        components: {
            FooterSider,
            CategoryTree,
            GroupCard,
            MemberList,
            InviteMember,
            JoinRequest,
        }
    })
    export default class GroupSider extends Vue {

        protected showMemberListStatus: boolean = false;
        protected showInviteMemberStatus: boolean = false;
        protected memberListSort: string = '';
        protected showJoinFlag: boolean = false;

        get stickyMinHeight(): number {
            return windowHeight() - NAV_BAR_HEIGHT;
        }

        get isFollow(): boolean {
            return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        protected showMemberList(flag : boolean){
            if (this.isFollow || this.$store.getters['User/isSuperAdmin']()) {
                if (flag) {
                    this.memberListSort = SORT_BY_MEMBER_ADMIN[3];
                } else {
                    this.memberListSort = SORT_BY_MEMBER_ADMIN[0];
                }
                this.showMemberListStatus = true;
            }
        }

        @Emit('show-manage-group')
        protected showManageGroup(){

        }

        protected showJoinRequest() {
            this.showJoinFlag = true;
        }

        protected showInviteAction()
        {
            this.$store.dispatch('GroupPrivacy/getInviteStatus', {})
                    .then((response: any) => {
                        if (response.success == 1) {
                            this.showInviteMemberStatus = true;
                        } else {
                            this.$message.info(this.$t('save_success') as string);
                        }
                    });
        }

    }
</script>
<style lang="scss" scoped>
    .sticky {
        @include sticky();
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>