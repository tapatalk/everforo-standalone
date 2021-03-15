<template>
    <div class="group-switch-box">
        <a-dropdown
            :trigger="['click']"
            placement='bottomLeft'
        >
            <a class="ant-dropdown-link group-switch">
                <div class="box">
                    <Logo
                        v-if="showEverforoLogoAndName"
                    />
                    <div
                        v-else-if="groupLogo"
                        class="logo" 
                        :style="{width: logoSize,
                        height: logoSize,
                        'border-radius' : logoBorderRadius,
                        'backgroundImage': 'url(' + groupLogo + ')'
                         }"
                    ></div>
                    <div
                        v-else-if="groupTitle"
                        class="logo"
                        :style="{width: logoSize,
                        height: logoSize,
                        'border-radius' : logoBorderRadius,
                        'backgroundImage': 'url(' + defaultGroupIconPath + ')'
                         }"
                    >
                    </div>
                    <pre v-if= "showEverforoLogoAndName">{{'                                                     '.substring(0, MOBILE_TEXT_LENGTH - 14)}}</pre>
                    <span
                        v-else-if="isMobile"
                    >{{groupTitle.length > MOBILE_TEXT_LENGTH ? groupTitle.substring(0, MOBILE_TEXT_LENGTH) + '...' : groupTitle}}</span>
                    <span
                        v-else
                    >{{groupTitle}}</span>
                    <Icons
                        type="xiala"
                    />
                </div>
            </a>
            <a-menu
                slot="overlay"
                class="group-list-menu"
                :style="{marginTop: '-10px'}"
            >
                <a-menu-item
                    v-if="followedGroups.length"
                    class="menu-title"
                    disabled
                >
                    {{$t('my_groups')}}
                </a-menu-item>
                <a-menu-item 
                    v-for="group in followedGroups"
                    v-on:click="goGroup(group)"
                    :key="group.id"
                    :class="[{'active-group': !showEverforoLogoAndName && group.id == $store.state.Group.id}]"
                >
                    <a
                        class="group-list-item"
                    >
                        <div
                            v-if="group.logo"
                            class="logo"
                            :style="{width: logoSize, height: logoSize, 'border-radius' : logoBorderRadius, 'backgroundImage': 'url(' + group.logo + ')'}"
                        ></div>
                        <div
                            v-else
                            class="logo"
                            :style="{width: logoSize, height: logoSize, 'border-radius' : logoBorderRadius, 'backgroundImage': 'url(' + defaultGroupIconPath + ')'}"
                        >
                        </div>
                        <span
                            v-if="isMobile"
                        >{{group.title.length > MOBILE_TEXT_LENGTH ? group.title.substring(0, MOBILE_TEXT_LENGTH) + '...' : group.title}}</span>
                        <span
                            v-else
                        >{{group.title}}</span>
                    </a>
                </a-menu-item>

                <a-menu-item class="create-group-item">
                    <a-button
                        class="new-group-btn"
                        v-on:click="showCreateGroupModal"
                    >
                        {{$t('create_a_group')}}
                    </a-button>
                </a-menu-item>
            </a-menu>
        </a-dropdown>
        <keep-alive>
            <GroupCreate
                v-if="showCreateGroup"
                v-on:close-create-group="showCreateGroup = false"
            />
        </keep-alive>
    </div>
</template>
<script lang="ts">
    import {Component, Vue, Watch, Prop} from 'vue-property-decorator';
    import {IS_MOBILE, SORT_BY_GROUP} from '@/helpers/Utils';
    import {RawLocation} from "vue-router";
    import {LOGO_SIZE} from '@/helpers/Utils';
    import {LOGO_BORDER_RADIUS} from '@/helpers/Utils';
    import {GroupInterface} from '@/helpers/Interfaces';
    import GroupCreate from '@/components/GroupCreate.vue';
    import Logo from '@/components/Logo.vue';
    import {windowWidth, DEFAULT_GROUP_ICON_PATH} from '@/helpers/Utils';

    @Component({
        components: {
            GroupCreate,
            Logo,
        },
    })
    export default class GroupSwitch extends Vue {
        
        protected MOBILE_TEXT_LENGTH: number = 15;

        protected isMobile: boolean = IS_MOBILE;
        protected followedGroups: GroupInterface[] = [];
        protected logoSize: string = LOGO_SIZE + 'px';
        protected logoBorderRadius: string = LOGO_BORDER_RADIUS + 'px';
        protected showCreateGroup: boolean = false;

        protected defaultGroupIconPath: string = DEFAULT_GROUP_ICON_PATH;

        protected created(){
            if(windowWidth() <= 340){
                this.MOBILE_TEXT_LENGTH = 10;
            } 
        }

        @Prop()
        public showDefault!: boolean;

        get groupName(): string {
            return this.$store.state.Group.name;
        }

        get groupTitle(): string {
            return this.$store.state.Group.title;
        }

        get groupLogo(): string {
            return this.$store.state.Group.logo;
        }

        get showEverforoLogoAndName(): boolean {
            return this.showDefault && (this.groupTitle == null || this.groupTitle.length == 0 || this.$router.currentRoute.name == 'homegroups');
        }

        get userId(): number {
            return this.$store.state.User.id;
        }

        protected showCreateGroupModal(e: Event) {
            e.preventDefault();
            if (this.$store.state.User.id && this.$store.state.User.activate) {
                this.showCreateGroup = true;
            } else {
                this.$store.commit('setShowLoginModal', true);
            }
        }

        @Watch('groupName', {immediate: true})
        protected onGroupNameChange(val: string, old: string) {
            // when switch group or first come to a group
            if (val !== old) {
                this.updateFollowList();
            }
        }

        @Watch('userId')
        protected onUserChange(val: number, old: number) {
            if (val != old) {
                this.updateFollowList();
            }
        }

        protected updateFollowList() {
            this.followedGroups = [];

            if(this.$store.state.User.groups && this.$store.state.User.groups.length) {
                for (let i = 0; i < this.$store.state.User.groups.length; i++) {
                    // add followed groups to list, when it's current group. dd to the top
                    if (this.$store.state.User.groups[i].id) {
                        if (this.$store.state.User.groups[i].id != this.$store.state.Group.id) {
                            this.followedGroups.push(this.$store.state.User.groups[i]);
                        } else {
                            this.followedGroups.unshift(this.$store.state.User.groups[i]);
                        }
                    }
                }
            }
        }

        protected goGroup(group: GroupInterface) {
            
            if (group.id == this.$store.state.Group.id) {
                // no need to redirect to current group, the url is the same
            } else {
                this.$router.push({
                    name: 'group',
                    params: {group_name: group.name, sort: this.$store.getters['User/getSort'](group.name)},
                } as unknown as RawLocation);
            }
        }
    }
</script>
<style lang="scss" scoped>

    .ant-dropdown-trigger {
        margin-left: var(--p4);
    }

    .group-switch-box {
        display: flex;
    }

    @media (max-width: 360px) {
        .group-switch-box{
            max-width: 200px;
        }

        .group-switch {
            max-width: 200px;
        }
    }

    .group-switch {
        height: 100%;

        .box {
            height: $nav-bar-height - 12;
            padding: 5px var(--p3);
            margin: 6px 0;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            color: var(--font-color1);
            font-weight: $title-weight;
            border: $border-width $border-style var(--border-color5);
            border-radius: $border-radius1;
            overflow: hidden;

            .ico {
                font-size: 0.9rem;
                margin-left: var(--p3);
            }
        }
    }

    .create-group-item:hover {
        background: none;
    }

    .group-list-item {
        color: var(--font-color1);
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
    }

    .group-list-menu { 
        background-color: var(--dropdown-bg);
        padding-left: 12px;
        padding-right: 12px;

        .ant-dropdown-menu-item {
            padding-left: 0;
            padding-right: 0;

            &.active-group a {
                background-color: var(--active-bg);
            }

            &:hover a {
                background-color: var(--hover-bg);
            }
        }

        .ant-dropdown-menu-item-disabled:hover {
            cursor: default;
        }

        .menu-title {
            @include secondary_title_font();
        }

        .menu-title.ant-dropdown-menu-item-disabled {
            background: none;
            color: var(--font-color1);
            line-height: 19px;
            padding: 12px 0;
            font-weight: 500;
        }
    }

    .new-group-btn {
        margin: 16px 0;
        width: 100%;
        height: 36px;
        font-weight: 500;
        color: var(--theme-color);
        border: 1px solid var(--theme-color);
        background: none;
        font-size: 14px;
        line-height: 20px;
    }



    .logo, .logo-placeholder {
        margin-right: var(--p3);
        background-size: cover;
    }

    .logo-placeholder {
        @include logo_placeholder;
    }

    .ant-dropdown {
        margin-top: -10px;
    }

    .ant-dropdown-menu {
        max-width: 600px;
    }
</style>
