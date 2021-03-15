<template>
    <a-modal
        :visible="true"
        :centered="true"
        :width="isMobile? '90%' : 660"
        :closable="false"
        :max-width="660"
        :maskStyle="{backgroundColor: 'rgba(0,0,0,0.7)'}"
        :footer="null"
        v-on:cancel="onClose"
    >
        <div
            :class="['modal-close-btn', {'mobile': isMobile}]"
            v-on:click="onClose"
        >
            <Icons
                type="chacha"
            />
        </div>

        <a-row
            v-if="profileId === userId && tabIndex == 2"
            :class="['profile-header','tab2', {'mobile': isMobile}]"
        >

            <div
                class="login-back-btn"
                v-on:click="() => goBack()"
            >
                <Icons type="fanhui"/>
            </div>

            <a-col
                :span="18"
            >
                <div class=" "
                    :class="['header-text','has-goback-icon-header', {'mobile': isMobile}]"
                >{{$t('upload_avatar')}}</div>
            </a-col>

            <a-col
                :span="6"
                class="header-action"
            >
                <a-button
                    v-if="$store.state.Profile.id == $store.state.User.id "
                    :disabled="avatarCanSave == false"
                    type="link"
                    v-on:click="() => setSaveAvatar()"
                    :class="{'mobile': isMobile}"
                >
                    {{$t('save')}}
                </a-button>
            </a-col>
        </a-row>

        <ChangeAvatar
            v-if="tabIndex == 2"
            :save-avatar="this.submitSaveAvatar"
            v-on:avatar-can-save="setAvatarCanSave"
            v-on:avatar-saved="setAvatarSaved"
        >
        </ChangeAvatar>

        <VueScrollBar
            v-if="tabIndex == 1"
            :max-height="500"
            :min-height="0"
            :scroll-to="0"
            v-on:reach-bottom="onProfilePostReachBottom"
        >
            <a-row
                :class="['profile', {'mobile': isMobile}]"
            >
                <a-col
                    :span="5"
                    class="profile-left-col"
                    :class="[{'mobile': isMobile}]"
                >
                    <div
                        style="display: inline-block;"
                        v-on:click="changeAvatar"
                    >
                        <UserAvatar
                            :scale="isMobile ? 3 : 5"
                            :username="$store.state.Profile.name"
                            :avatar="avatar"
                            :disableLoginPopup=true
                            :is-ban="$store.state.Profile.is_ban"
                            :online="$store.state.Profile.online"
                        />
                        <div
                            v-if="profileId === userId"
                            class="change-avatar-btn"
                        >
                            <img src="/img/camera.png" />
                            <input
                                type="file"
                                ref="avatar_input"
                                v-on:change="selectAvatar"
                                accept="image/*"
                            >
                        </div>
                    </div>
                </a-col>

                <a-col
                    :span="19"
                    class="profile-right-col"
                    :class="[{'mobile': isMobile}]"
                >
                    <div>
                        <div class="username-statistic">
                            <div
                                v-if="editUsername"
                                class="username"
                            >
                                <a-input
                                    v-model="nameEdited"
                                    id="username"
                                    autofocus="autofocus"
                                    v-on:pressEnter="() => updateProfile()"
                                />
                                <div class="join-date">
                                    <span>{{$t('since')}}: </span>
                                    <span>
                                        <TimeString
                                            v-if="$store.state.Profile.created_at"
                                            :time="$store.state.Profile.created_at"
                                        />
                                    </span>
                                </div>
                                <div class="join-date">
                                    <span>{{$t('last_seen')}}: </span>
                                    <span>
                                        <DayString
                                            v-if="$store.state.Profile.last_seen"
                                            :time="$store.state.Profile.last_seen"
                                        />
                                    </span>
                                </div>
                            </div>
                            <div
                                v-else
                                class="username"
                            >
                                <div>
                                    {{$store.state.Profile.name}}
                                    <Icons
                                        v-if="$store.getters['User/isBlocked'](profileId)"
                                        type="block"
                                    />
                                </div>
                                <div v-if="$route.params.group_name && $store.state.Profile.is_ban" class="join-date">
                                    <span>{{$t('is_ban_profile')}}</span>
                                </div>
                                <div v-else class="join-date">
                                    <span>{{$t('since')}}: </span>
                                    <span>
                                        <TimeString
                                            v-if="$store.state.Profile.created_at"
                                            :time="$store.state.Profile.created_at"
                                        />
                                    </span>
                                </div>
                                <div class="join-date">
                                    <span>{{$t('last_seen')}}: </span>
                                    <span>
                                        <DayString
                                            v-if="$store.state.Profile.last_seen"
                                            :time="$store.state.Profile.last_seen"
                                        />
                                    </span>
                                </div>
                            </div>
                            <div class="statistic">
                                <div>
                                    <span class="num">{{$store.state.Profile.posts}}</span>
                                    <span class="text">{{$tc('posts', $store.state.Profile.posts)}}</span>
                                </div>
                                <div>
                                    <span class="num">{{$store.state.Profile.likes}}</span>
                                    <span class="text">{{$tc('likes', $store.state.Profile.likes)}}</span>
                                    <span class="text">{{$t('received')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a-row
                        v-if="profileId === userId"
                        class="actions-row"
                        :class="{'mobile': isMobile}"
                    >
                        <div class="other-actions">
                            <a
                                v-if="editUsername"
                                v-on:click="() => updateProfile()"
                            >{{$t('save')}}</a>
                            <a
                                v-else
                                v-on:click="changeUsername"
                            >{{$t('change_username')}}</a>
                            <a
                                v-if="false"
                                v-on:click="viewAssets"
                            >{{$t('view_my_assets')}}</a>
                            <a
                                v-if="false"
                                v-on:click="signOut"
                            >{{$t('sign_out')}}</a>
                        </div>
                    </a-row>
                    <a-row
                        v-else-if="userId"
                        class="actions-row"
                        :class="{'mobile': isMobile}"
                    >
                        <span
                            v-if="$store.getters['User/isBlocked'](profileId)"
                            class="other-actions"
                        >
                            <a
                                v-on:click="unblockUser"
                            >{{$t('unblock')}}</a>
                        </span>
                        <span
                            v-else
                            class="other-actions"
                        >
                            <a
                                v-on:click="blockUser"
                            >{{$t('block')}}</a>
                        </span>
                        <a-dropdown
                            :trigger="['click']"
                            v-on:visibleChange="menuVisibleHandler"
                            v-if="banUserFlag"
                        >
                            <a
                                :class="['show-more-action', {'menu-showed': showActionMenu}]"
                            >
                                <Icons
                                    type="more"
                                />
                            </a>
                            <a-menu slot="overlay" class="comment-action-menu">
                                <a-menu-item
                                >
                                    <span
                                        v-if="!$store.state.Profile.is_ban"
                                        class="other-actions select-a"
                                    >
                                        <a
                                            class="item-menu-text"
                                            v-on:click="showBanConfirm = true"
                                        >{{$t('ban')}}</a>
                                    </span>
                                    <span
                                        v-else
                                        class="other-actions select-a"
                                    >
                                        <a
                                            v-on:click="unBanUser"
                                        >{{$t('unban')}}</a>
                                    </span>
                                </a-menu-item>
                            </a-menu>
                        </a-dropdown>
                    </a-row>
                </a-col>
            </a-row>

            <div 
                v-if="!$store.state.Profile.is_ban"
                class="post-filter"
            >
                <div>{{$t('activities')}}</div>
                <a-row
                    type="flex"
                    justify="start"
                    align="middle"
                    class="sort"
                >
                    <span class="sort-by">{{$t('filters')}}</span>
                    <a-dropdown
                        placement="bottomRight"
                        :trigger="['click']"
                    >
                        <a
                            class="sort-order"
                        >
                            <span>{{userPostsFilter}}</span>
                            <Icons
                                type="xiala"
                                :class="[{'mobile': isMobile}]"
                            />
                        </a>
                        <a-menu slot="overlay">
                            <a-menu-item
                                v-for="(sort, index) in profilePostFilter"
                                :key="index"
                                v-on:click="switchProfilePosts(sort)"
                            >
                                <span
                                    class="sort-order"
                                >{{sort}}</span>
                            </a-menu-item>
                        </a-menu>
                    </a-dropdown>
                </a-row>
            </div>


            <section
                v-if="!$store.state.Profile.is_ban && (userPosts.length || loadMore)"
                class='profile-posts'
            >
                <div
                    v-for="post in userPosts"
                >
                    <UserProfilePost
                        :post="post"
                    />
                </div>
                <div v-if="loadMore">
                    <a-skeleton
                        active
                        :paragraph="{rows: 1}"
                        :loading="loadMore"
                    />
                </div>
            </section>
        </VueScrollBar>

        <TokenAssetsList
            v-if="showAssetsList"
            v-on:close-assets-list="showAssetsList = false"
        />

        <ConfirmModal
                v-if="showBanConfirm"
                :reverse-button="true"
                :yes-text="$t('Yes')"
                :no-text="$t('cancel')"
                v-on:confirm="banUserAction"
                v-on:cancel="showBanConfirm = false"
        >
            <div class="ant-ban-image"><QuestionMark/></div>

            <div
                class="confirm-message"
            >{{$t('ban_user')}}</div>
        </ConfirmModal>
        <NoMoreData
                v-if="noMoreDataStatus"
        />

    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {ProfilePostInterface, UploadedImageUrl, UserInterface} from '@/helpers/Interfaces';
    import {IS_MOBILE, MAX_AVATAR_SIZE, SUPPORTED_IMAGE_TYPE, bindEvent, StorageLocal} from "@/helpers/Utils";
    import {Response} from '@/http/Response';
    import ChangeAvatar from '@/components/ChangeAvatar.vue';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import QuestionMark from '@/components/QuestionMark.vue';
    import TimeString from '@/components/TimeString.vue';
    import DayString from '@/components/DayString.vue';
    import TokenAssetsList from '@/components/TokenAssetsList.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';
    import NoMoreData from '@/components/NoMoreData.vue';
    import UserProfilePost from '@/components/UserProfilePost.vue';
    import VueScrollBar from '@/components/VueScrollBar.vue';

    @Component({
        components: {
            ChangeAvatar,
            ConfirmModal,
            FullScreenModal,
            QuestionMark,
            TimeString,
            TokenAssetsList,
            UserAvatar,
            Username,
            UserProfilePost,
            VueScrollBar,
            DayString,
            NoMoreData,
        }
    })
    export default class UserProfile extends Vue {

        @Ref('avatar_input')
        readonly avatar_input!: HTMLInputElement;

        protected loading: boolean = true;
        protected editUsername: boolean = false;
        protected canSave: boolean = false;
        protected avatarCanSave: boolean = false;
        protected maxAllowedSize: number = MAX_AVATAR_SIZE;
        protected isMobile: boolean = IS_MOBILE;

        protected initName: string = '';
        protected initAvatar: string = '';

        protected showAssetsList = false;
        protected showBanConfirm = false;
        protected pageSize = 10;

        protected submitSaveAvatar = false;

        protected tabIndex = 1;

        protected userPosts: ProfilePostInterface[] = [];
        protected userPostsFilter: string = '';
        protected userPostsPage: number = 1;
        protected showActionMenu: boolean = false;
        protected loadMore: boolean = false;
        protected noMoreDataStatus:boolean = false;

        get showProfile(): boolean {
            return this.$store.state.Profile.show;
        }

        get nameEdited(): string {
            return this.$store.state.Profile.name;
        }

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin(): boolean {
            return this.$store.getters['User/isSuperAdmin']() 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus);
        }

        get banUserFlag(): boolean {
            if (this.$store.state.Profile.is_ban) {
                return this.isAdmin && this.$store.state.Profile.is_admin == 0 ? true : false;
            } else {
                return this.isAdmin && this.$store.state.Profile.is_follow > 0 && this.$store.state.Profile.is_admin == 0 ? true : false;
            }

        }

        set nameEdited(name: string) {
            this.$store.commit('Profile/updateName', name);
        }

        get avatar(): string {
            return this.$store.state.Profile.photo_url;
        }

        set avatar(url) {
            this.$store.commit('Profile/updateAvatar', url);
        }

        get profileId(): number {
            return this.$store.state.Profile.id;
        }

        get userId(): number {
            return this.$store.state.User.id;
        }

        get myToken(): string {
            return '0.123456';
        }

        get profilePostFilter() {
            return [
                this.$t('all'),
                this.$tc('posts', 0),
                this.$tc('likes', 0),
            ];
        }

        protected beforeDestroy() {
            if (this.nameEdited !== this.initName) {
                this.nameEdited = this.initName;
            }
        }

        // immediate becasue this component got destroied each time close profile popup 
        @Watch('profileId', {immediate: true})
        protected onProfileIdChange(newId: number) {
            if (newId && newId !== this.$store.state.Profile.old_id) {
                this.loading = true;
                let params = {id: newId, group_name: this.$store.state.Group.name ? this.$store.state.Group.name : ''}
                this.$store.dispatch('Profile/getProfile', params)
                .then(() => {
                    this.loading = false;
                    this.initName = this.$store.state.Profile.name;
                    this.initAvatar = this.$store.state.Profile.photo_url;

                    this.userPostsFilter = this.$t('all') as string;
                    this.userPostsPage = 1;

                    this.loadMore = true;
                    
                    // get user posts
                    this.$store.dispatch('Profile/getUserPosts', {profile_id: newId, filter: this.userPostsFilter, page: this.userPostsPage})
                    .then((response: Response) => {
                        const data: {user_posts: ProfilePostInterface[]} = response.getData();

                        if (data && data.user_posts && data.user_posts.length) {
                            // if (data.user_posts.length < this.pageSize) {
                            //     this.loadMore = false;
                            // }
                            this.userPosts = data.user_posts;
                        } else {
                            this.noMoreDataStatus = true;
                        }
                        
                    })
                    .finally(() => {
                        this.loadMore = false;
                    });
                });
            }
        }

        @Watch('nameEdited')
        protected onNameEdited(name: string) {
            if (this.initName && name !== this.initName) {
                if (name.length < 3 || name.length > 32) {
                    this.canSave = false;
                } else {
                    this.canSave = true;
                }
            } else {
                if (this.avatar === this.initAvatar) {
                    // this.canSave = false;
                }
            }
        }

        @Watch('avatar')
        protected onAvatarChange(avatar: string) {
            if (this.initAvatar && avatar !== this.initAvatar) {
                // this.canSave = true;
            } else {
                if (this.nameEdited === this.initName) {
                    this.canSave = false;
                }
            }
        }

        @Watch('loading')
        protected onLoading(flag: boolean) {
            this.$store.commit('setShowProgressLine', flag);
        }

        @Emit('close')
        protected onClose(): void {
            this.editUsername = false;
        }

        /**
         * when click camera icon, trigger input select file
         */
        protected changeAvatar(): void {
            if(this.userId) {
                if (this.profileId === this.userId) {

                    this.tabIndex = 2;

                    // this.avatar_input.click();
                }
            }
        }

        protected menuVisibleHandler(visible: boolean) {
            this.showActionMenu = visible;
        }

        /**
         * when click on "change name", show input
         */
        protected changeUsername(): void {
            this.editUsername = !this.editUsername;
        }

        protected selectAvatar(e: Event): void {

            const target = e.target as HTMLInputElement;

            if (target.files && target.files.length) {
                this.readFiles(target.files[0] as File);
            }
        }

        protected readFiles(file: File): void {

            if (!file.size) {
                this.$message.error(this.$t('uploaded_file_empty') as string);
                return;
            }

            if (file.size > (this.maxAllowedSize * 1048576)) {
                this.$message.error(this.$t('uploaded_file_exceed_max_size', [this.maxAllowedSize, (file.size / 1048576).toFixed(2)]) as string);
                return;
            }

            if (file.type.match(SUPPORTED_IMAGE_TYPE)) {
                const reader = new FileReader();

                bindEvent(reader, 'error', this.loadingError as EventListener);
                bindEvent(reader, 'load', this.displayImage as EventListener);

                reader.readAsDataURL(file);
            }
            else {
                this.$message.error(this.$t('uploaded_file_type_not_allowed', [file.type]) as string);
                return;
            }
        }

        /**
         * show load failed error, can't simulate this case, just leave it here
         * @param e
         */
        protected loadingError(e: ProgressEvent): void {
            this.$message.error(this.$t('file_loading_error') as string);
        }

        protected displayImage(e: ProgressEvent) {
            // base64 encoded file data
            const dataUrl = (e.target as FileReader).result as string;

            this.avatar = dataUrl;
        }

        protected viewAssets() {
            this.showAssetsList = true;
        }

        protected signOut(): void {
            StorageLocal.removeItem('bearer');
            window.location.reload(true);
        }

        /**
         * update user profile
         */
        protected async updateProfile() {

            const data = new FormData();
            
            // if (this.avatar.match(/^data:/)) {
            //     const uploadedAvatarUrl: UploadedImageUrl = await this.$store.dispatch('Profile/uploadAvatar',
            //                                                 {dataUrl: this.avatar});
            //
            //     data.append('photo_url', uploadedAvatarUrl.thumb_url);
            // }

            data.append('name', this.nameEdited);
            this.loading = true;

            try {
                const response = await this.$store.dispatch('Profile/update', data);
                const profileData: UserInterface = response.getData();

                this.canSave = false;
                this.loading = false;

                if ( response.getCode() != 20000 ){
                    if(response.getDescription()){
                        this.$message.error(response.getDescription() as string);
                    } else {
                        this.$message.error(this.$t('network_error') as string);
                    }
                }

                if (profileData && profileData.id == this.$store.state.User.id){
                    this.$store.commit('User/setCurrentUser', profileData);
                    this.initName = this.$store.state.Profile.name;
                    this.initAvatar = this.$store.state.Profile.photo_url;
                    this.$message.success(this.$t('profile_updated') as string);
                    this.editUsername = false;
                }
            }catch(e) {
                this.loading = false;
                if (e.response) {
                    const data = e.response.response && e.response.response.data;
                    if (data.errors) {
                        for (const error in data.errors) {
                            this.$message.error(data.errors[error]);
                        }
                    }
                }
            }
        }

        @Watch('editUsername')
        protected onEditUsernameChange() {
            this.$nextTick (() => {
                    var input = document.getElementById('username') as HTMLDivElement;
                    if (this.editUsername && input)
                    input.focus();
                });
        }

        protected blockUser() {
            const data = new FormData();

            data.append('block_user_id', this.profileId + '');
            
            this.$store.dispatch('Flag/blockUser', data)
                        .then((blocked_users: number[]) => {
                            // todo, show block user popup
                            this.$store.commit('User/updateBlockedUser', blocked_users);
                        });
        }

        protected unblockUser() {
            const data = new FormData();

            data.append('block_user_id', this.profileId + '');
            
            this.$store.dispatch('Flag/unblockUser', data)
                        .then((blocked_users: number[]) => {
                            // todo, show block user popup
                            this.$store.commit('User/updateBlockedUser', blocked_users);
                        });
        }

        protected banUserAction(): void {
            this.$store.dispatch('BanUser/banUser',  { user_id: this.profileId})
                    .then((response: Response) => {
                        const data = response.getData();
                        // console.log(response, data);
                        if (data && data.ban_status) {
                            //update user ban status
                            this.$store.commit('Profile/setIsBan', 1);
                            this.$store.commit('BanUser/setBanList', this.profileId);
                            this.showBanConfirm = false;
                        } else if (response && response.response && response.response.data && response.response.data.code
                                && response.response.data.code == 403) {
                            this.$message.error(this.$t('no_permission') as string);
                        } else if (response && response.response && response.response.data && response.response.data.code
                                && response.response.data.code == 40002) {
                            this.$message.error(this.$t('ban_error') as string);
                        } else if (response && response.response && response.response.data && response.response.data.code
                                && response.response.data.code == 40003) {
                            this.$message.error(this.$t('ban_admin_error') as string);
                        }
                    })
                    .catch(() =>{
                        //error
                    });
        }

        protected unBanUser() {
            this.$store.dispatch('BanUser/unBanUser',  { user_id: this.profileId})
                    .then((response: Response) => {
                        const data = response.getData();
                        if (data) {
                            //update user ban status
                            this.$store.commit('Profile/setIsBan', 0);
                            this.$store.commit('BanUser/setUnBanList', this.profileId);
                            this.$store.commit('Profile/setIsFollow', 1);
                        } else if (response && response.response && response.response.data && response.response.data.code
                                && response.response.data.code == 403) {
                            this.$message.error(this.$t('no_permission') as string);
                        }
                    })
                    .catch(() =>{
                        //error
                    });
        }

        protected goBack(){
            this.tabIndex = 1;
        }

        protected setAvatarCanSave(flag : boolean){
            this.avatarCanSave = flag ? true : false;
        }

        protected setAvatarSaved(){
            this.avatar = this.$store.state.Profile.photo_url;
            this.tabIndex = 1;
            this.submitSaveAvatar = false;
        }

        protected setSaveAvatar(){
            this.setAvatarCanSave(false);
            this.submitSaveAvatar = true;
        }

        protected switchProfilePosts(filter: string) {
            this.loadMore = true;
            this.userPostsFilter = filter;
            this.userPostsPage = 1;

            this.$store.dispatch('Profile/getUserPosts', {profile_id: this.profileId, filter: filter, page: this.userPostsPage})
            .then((response: Response) => {
                const data: {user_posts: ProfilePostInterface[]} = response.getData();
                if (data && data.user_posts) {
                    // if (data.user_posts.length < this.pageSize) {
                    //     this.loadMore = false;
                    // }
                    this.userPosts = data.user_posts;
                }
            })
            .finally(() => {
                this.loadMore = false;
            });
        }

        protected onProfilePostReachBottom() {
            this.loadMore = true;
            this.userPostsPage = this.userPostsPage + 1;
            
            this.$store.dispatch('Profile/getUserPosts', {profile_id: this.profileId, filter: this.userPostsFilter, page: this.userPostsPage})
            .then((response: Response) => {
                const data: {user_posts: ProfilePostInterface[]} = response.getData();
                if (data && data.user_posts) {
                    // if (data.user_posts.length < this.pageSize) {
                    //     this.loadMore = false;
                    // }
                    this.userPosts = this.userPosts.concat(data.user_posts);
                }
            })
            .finally(() => {
                this.loadMore = false;
            });
        }

    }
</script>
<style lang="scss" scoped>
    .profile {
        .change-avatar-btn {
            position: absolute;
            top: 52px;
            right: 30px;
            width: 30px;
            height: 30px;
            cursor: pointer;

            img {
                width: 100%;
                height: 100%;
            }

            input {
                display: none;
            }
        }

        &.mobile {
            .change-avatar-btn {
                top: 29px;
                left: 30px;
                width: 24px;
                height: 24px;
            }

            .avatar-div {
                margin-bottom: 15px;
            }

            .profile-right-col { 
                padding-top: 0;

                .username {
                    font-size: 18px;
                    line-height: 25px;
                    margin-bottom: 0;
                }
            }
        }
    }

    .profile-header.tab2{
        margin-bottom: 0;
    }

    .profile-header {
        padding: var(--p6) 0;
        border-bottom: 1px solid var(--border-color2);

        .has-goback-icon-header{
            padding-left:var(--p5);
            color: var(--font-color1);
            line-height: 1.2;
        }

        .has-goback-icon-header.mobile{
            padding-left:var(--p9);
        }

        &.mobile {
            padding: 15px 0;

            .header-text {
                font-size: 18px;
            }
        }
    }

    .ant-row.profile {
        padding: var(--p6) var(--p6) var(--p6);
        //background-color: var(--profile-upper-bg);
        padding-left: 14px;
        border-bottom: 1px solid var(--border-color2);
        padding-left: 0;
    }

    .profile-left-col {
        padding-left: 10px;
        margin-right: -8px;
        
        &.mobile {
            padding-left: 0;
            margin-right: 0;
        }
    }

    .profile-right-col {
        padding-left: 1px;
        padding-top: 8px;
        color: var(--font-color1);

        &.mobile {
            padding-left: var(--p4);
        }

        .username-statistic {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--p2);

            .username {
                @include title_font;
                font-size: $font-size5;
                line-height: 21px;
                div:first-child {
                    margin-bottom: var(--p2);
                }
            }

            .statistic {
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: flex-start;
                line-height: 24px;

                & > div {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: flex-start;
                }

                & > div:first-child {
                    margin-right: var(--p10);
                }
                
                span {
                    display: inline-block;
                }

                .num {
                    color: var(--font-color1);
                    font-size: $font-size5;
                    font-weight: 500;
                }

                .text {
                    color: var(--desc-color);
                    font-size: $upload-desc-font-size;
                    line-height: 12px;
                    margin-top: 8px;
                }
            }

        }

        .join-date {
            margin-bottom: 0;
            color: var(--desc-color);
            font-size: $upload-desc-font-size;
            font-weight: normal;

            .time-string {
                font-size: inherit;
                color: inherit;
            }
        }

        .actions-row {

            margin-top: var(--p4);

            .other-actions {

                display: inline-block;    
                padding: var(--p2) var(--p4);
                border: $border-width $border-style var(--font-color2);
                border-radius: $border-radius1;

                a {
                    font-size: $font-size1;
                    color: var(--font-color2);
                    display: inline-block;
                    margin-right: var(--p6);

                    &::first-letter {
                        text-transform: uppercase;
                    }

                    &:last-child {
                        margin-right: 0;
                    }
                }
            }

            &.mobile {
                margin-top: 40px;
            }
        }
    }

    .ant-btn {
        @include capitalize();
        float: right;
        font-size: 16px;
        line-height: 20px;
        height: 25px;
        color: var(--theme-color);
        font-weight: 500;

        &.ant-btn-link[disabled] {
            color: #606878;

            &.mobile {
                padding-right: 0;
            }
        }
        
    }
    .ban-status {
        margin-left: var(--p4);
    }
    .question-mark {
        margin: 0 auto;
    }

    .ant-ban-image {
        text-align: center;
    }

    .login-back-btn {
        position: absolute;
        z-index: 9;
        cursor: pointer;
        // padding-top: var(--p1-5);
    }

    .post-filter {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: var(--p3) var(--p6);
        margin: var(--p3) 0 0;
        padding-left: 0;
        padding-right: 0;
        // border: $border-width $border-style var(--border-color2);

        div:first-child {
            @include title_font;
        }

        .sort {
            margin-right: var(--p4);
            .sort-by {
                margin-right: var(--p1);
                color: var(--font-color2);
            }

            .sort-order {
                padding: 0 0 0 var(--p2);
                color: var(--theme-color);
                font-weight: $title-weight;
                display: flex;

                .ico {
                    padding-left: var(--p2);
                    color: var(--category-color);
                    font-size: $font-size0;
                    &.mobile {
                        font-size: 0.9rem;
                    }
                }
            }
        }
    }

    //section.profile-posts {
    //    padding: 0 var(--p6);
    //}

    .show-more-action {
        margin-left: 20px;
        .ico {
            font-size: 1.3rem;
        }
    }
    .item-menu-text {
        font-size: 1.1rem;
        padding-left: 4px;
        padding-right: 40px;
        color: var(--font-color2);
    }
    .ant-dropdown-menu-item-active {
        a {
             color: #3d72de;
        }
    }
    .header-action {
        position: absolute;
        top: 20px;
        right: 0;
    }
</style>
<style>
    /* special css for user profile popup */
    /*.ant-modal-body {
        padding: 0 0 var(--p6);
    }*/
    /* for border radius */
    /*.ant-modal-content {
        overflow: hidden;
    }*/
</style>